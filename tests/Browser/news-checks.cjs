const assert=require('node:assert/strict');
module.exports=async ({evaluate,cdp,width,page})=>{
  const settle=()=>evaluate('new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))');
  // The third-party accessibility script is blocked in the offline renderer.
  // Supply its container so the shared modal guard is exercised for both FABs.
  await evaluate(`(() => {if(!document.querySelector('.acc-container')){const widget=document.createElement('div');widget.className='acc-container';widget.dataset.testWidget='true';widget.innerHTML='<button class="acc-toggle-btn" type="button">Accessibility</button>';document.body.append(widget);}})()`);
  const widgetsBlocked=async()=>{
    await settle();
    assert.deepEqual(await evaluate(`(() => {
      const errors=[];
      if(!document.body.classList.contains('cms-modal-open'))errors.push('Missing shared modal state');
      for(const widget of document.querySelectorAll('.widget-dock,.floating-voice-btn,.acc-container,.chatbot-widget-shell')){
        if(!widget.inert)errors.push('Widget remains keyboard accessible');
        for(const control of [widget,...widget.querySelectorAll('button,a,iframe')]){
          const style=getComputedStyle(control);
          if(style.visibility!=='hidden'||style.pointerEvents!=='none')errors.push('Widget remains visible or clickable');
        }
      }
      return errors;
    })()`),[],'Widgets must be blocked behind modals at '+width);
  };
  const widgetsRestored=async()=>{
    await settle();
    assert.equal(await evaluate(`!document.body.classList.contains('cms-modal-open')&&[...document.querySelectorAll('.widget-dock,.floating-voice-btn,.acc-container,.chatbot-widget-shell')].every(el=>!el.inert)`),true,'Widgets must be restored after modal closes');
  };
  const desktopUnchanged=async()=>{
    const difference=await evaluate(`(() => {
      const link=document.querySelector('[data-news-responsive]');
      const snapshot=()=>JSON.stringify([...document.querySelectorAll('.main-content,.main-content *,.modal.active,.modal.active *')].filter(el=>el.getClientRects().length&&!el.matches('script,style')).map(el=>{
        const s=getComputedStyle(el),r=el.getBoundingClientRect();
        return [el.tagName,el.className,s.display,s.fontSize,s.padding,s.margin,s.borderRadius,s.gridTemplateColumns,s.flexWrap,s.color,s.backgroundColor,r.width,r.height];
      }));
      link.disabled=true;const before=snapshot();link.disabled=false;return before!==snapshot();
    })()`);
    assert.equal(difference,false,'Desktop appearance must not change at '+width);
  };
  const cards=()=>evaluate(`(() => {
    const problems=[];
    for(const el of document.querySelectorAll('.tab-content.active .news-bulk-count')){
      const r=el.getBoundingClientRect();
      if(r.height>30||el.scrollWidth>el.clientWidth+1)problems.push('Selection count must stay a compact pill');
    }
    for(const el of document.querySelectorAll('.tab-content.active .announcement-title,.tab-content.active .news-title'))if(parseFloat(getComputedStyle(el).fontSize)>14)problems.push('Oversized card title');
    for(const el of document.querySelectorAll('.tab-content.active .announcement-description,.tab-content.active .news-description'))if(parseFloat(getComputedStyle(el).fontSize)>12)problems.push('Oversized card text');
    for(const el of document.querySelectorAll('.tab-content.active .announcement-card-create,.tab-content.active .news-card-create'))if(el.getBoundingClientRect().height>150)problems.push('Create card is too tall');
    for(const el of document.querySelectorAll('.tab-content.active .card'))if(parseFloat(getComputedStyle(el).paddingLeft)>12)problems.push('Oversized section padding');
    for(const el of document.querySelectorAll('.tab-content.active .announcement-actions .btn,.tab-content.active .news-actions .btn')) {
      if(!el.getClientRects().length)continue;
      const s=getComputedStyle(el),r=el.getBoundingClientRect();
      if(parseFloat(s.fontSize)>11||r.height>44.1)problems.push('Oversized card action');
      if(el.scrollWidth>el.clientWidth+1)problems.push('Action text overflows');
    }
    return problems;
  })()`);
  const modal=async id=>{
    await settle();
    assert.equal(await evaluate(`document.getElementById('${id}').classList.contains('active')`),true,'Modal must open');
    await widgetsBlocked();
    if(width>1024){await desktopUnchanged();return;}
    const problems=await evaluate(`(() => {
      const problems=[],overlay=document.getElementById('${id}'),dialog=overlay.querySelector('.modal-content'),r=dialog.getBoundingClientRect();
      if(r.left<15||r.right>document.documentElement.clientWidth-15||r.top<0||r.bottom>innerHeight+1)problems.push('Dialog must fit the viewport');
      if(dialog.scrollWidth>dialog.clientWidth+1)problems.push('Dialog scrolls horizontally');
      if(parseFloat(getComputedStyle(dialog.querySelector('.modal-title')).fontSize)>16)problems.push('Oversized dialog heading');
      for(const footer of dialog.querySelectorAll('.cms-news-modal-actions')) {
        dialog.scrollTop=dialog.scrollHeight;
        for(const button of footer.querySelectorAll('button')) {
          const br=button.getBoundingClientRect(),s=getComputedStyle(button);
          if(br.height<39.9||br.height>44.1||parseFloat(s.fontSize)>11)problems.push('Footer action must stay compact');
          if(button.scrollWidth>button.clientWidth+1)problems.push('Footer action overflows');
          const walker=document.createTreeWalker(button,NodeFilter.SHOW_TEXT);
          while(walker.nextNode())if(walker.currentNode.textContent.trim()){
            const range=document.createRange();range.selectNodeContents(walker.currentNode);
            if([...range.getClientRects()].filter(rect=>rect.width>1).length>1)problems.push('Footer label breaks across lines');
          }
          if(br.bottom>dialog.getBoundingClientRect().bottom+1)problems.push('Footer action is unreachable');
        }
      }
      dialog.scrollTop=0;
      return problems;
    })()`);
    assert.deepEqual(problems,[],page+': '+id+' at '+width);
  };
  const capture=async label=>{
    if(process.env.CAPTURE!=='1'||![375,768].includes(width))return;
    const shot=await cdp('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});
    require('node:fs').writeFileSync(require('node:path').resolve(__dirname,'../../storage/app/cms-responsive-preview/'+page.replace(/[^a-z0-9-]/gi,'-')+'-'+label+'-'+width+'.png'),Buffer.from(shot.data,'base64'));
  };
  for(const tab of ['announcements','news']) {
    await evaluate(`switchTab('${tab}',document.querySelector('.tab-navigation [onclick*="${tab}"]'))`);await settle();
    if(width<=1024)assert.deepEqual(await cards(),[],page+': compact '+tab+' at '+width);
    else await desktopUnchanged();
    await evaluate(`document.querySelector('.tab-content.active .announcement-grid,.tab-content.active .news-grid')?.scrollIntoView({block:'start'});window.scrollBy(0,-90)`);
    await capture(tab+'-cards');
  }
  for(const type of ['Announcement','News']) {
    await evaluate(`open${type}Modal(true)`);
    const id=type.toLowerCase()+'Modal';
    await modal(id);
    if(width<=1024)for(const kind of ['font-size','text-color']) {
      await evaluate(`document.querySelector('#${id} .js-${kind}-trigger').click()`);
      const errors=await evaluate(`(() => {const problems=[],popover=document.querySelector('#${id} .js-${kind}-popover'),r=popover.getBoundingClientRect(),dr=document.querySelector('#${id} .modal-content').getBoundingClientRect();if(popover.hidden||r.width<${kind==='font-size'?100:180})problems.push('Editor menu is squeezed');if(r.left<dr.left||r.right>dr.right||popover.scrollWidth>popover.clientWidth+1)problems.push('Editor menu overflows the dialog');return problems;})()`);
      assert.deepEqual(errors,[],id+': '+kind+' menu at '+width);
      await evaluate(`document.querySelector('#${id} .js-${kind}-trigger').click()`);
    }
    await evaluate(`(() => {const surface=document.querySelector('#${id} .rich-editor-surface');surface.innerHTML='<p>Responsive editor preview.</p><ul><li>First requirement</li><li>Second requirement</li></ul>';surface.dispatchEvent(new Event('input',{bubbles:true}));})()`);
    await modal(id);await capture(type.toLowerCase()+'-modal');
    await evaluate(`document.querySelector('#${id} .modal-content').scrollTop=100000`);await capture(type.toLowerCase()+'-footer');
    await evaluate(`close${type}Modal()`);
    await widgetsRestored();
  }
  await evaluate(`document.querySelector('.announcement-actions button[onclick*="editAnnouncement"]')?.click()`);
  if(await evaluate(`document.getElementById('announcementModal').classList.contains('active')`)) {await modal('announcementModal');await evaluate('closeAnnouncementModal()');}
  await evaluate(`document.querySelector('.edit-news-btn')?.click()`);
  if(await evaluate(`document.getElementById('newsModal').classList.contains('active')`)) {await modal('newsModal');await evaluate('closeNewsModal()');}
  for(const image of [false,true]) {
    await evaluate(`openReadMoreModal('Responsive preview','<p>'+('Full preview text wraps naturally. '.repeat(40))+'</p><ul><li>First item</li><li>Second item</li></ul>','https://example.test',${image?"'/assets/static_img/pupillar.jpeg'":'null'},'[]')`);
    await modal('readMoreModal');await capture(image?'preview-image':'preview-text');
    await evaluate('closeReadMoreModal()');
    await widgetsRestored();
  }
  // Closing a preview must not re-enable widgets while its editor remains open.
  await evaluate(`openAnnouncementModal(true);openReadMoreModal('Nested preview','<p>Preview</p>','','','[]')`);
  await widgetsBlocked();
  await evaluate('closeReadMoreModal()');
  await widgetsBlocked();
  await evaluate('closeAnnouncementModal()');
  await widgetsRestored();
  if(width<=1024) {
    await evaluate(`(async()=>{const canvas=document.createElement('canvas');canvas.width=320;canvas.height=180;const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/png'));window.__newsCrop=window.CmsImageEditor.editFile(new File([blob],'responsive-preview.png',{type:'image/png'}));})()`);
    for(let i=0;i<20;i++) {await settle();if(await evaluate(`!!document.querySelector('.cms-image-editor:not([hidden])')`))break;}
    const crop=await evaluate(`(() => {const el=document.querySelector('.cms-image-editor:not([hidden]) .cms-image-editor-dialog');if(!el)return ['Image editor did not open'];const r=el.getBoundingClientRect(),problems=[];if(r.left<15||r.right>document.documentElement.clientWidth-15||el.scrollWidth>el.clientWidth+1)problems.push('Image editor overflows');el.scrollTop=el.scrollHeight;for(const button of el.querySelectorAll('.cms-image-editor-actions button'))if(button.scrollWidth>button.clientWidth+1||button.getBoundingClientRect().bottom>el.getBoundingClientRect().bottom+1)problems.push('Image editor action is unreachable');return problems;})()`);
    assert.deepEqual(crop,[],'Compact image editor at '+width);
    await widgetsBlocked();
    await evaluate(`document.querySelector('.cms-image-editor [data-cms-image-editor-cancel]').click();window.__newsCrop.then(()=>{delete window.__newsCrop})`);
    await widgetsRestored();
  }
  await evaluate(`switchTab('news',document.querySelector('.tab-navigation [onclick*="news"]'));window.scrollTo(0,0)`);
};
