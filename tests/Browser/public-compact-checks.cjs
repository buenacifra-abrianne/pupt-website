const assert = require('node:assert/strict');

module.exports = async ({ evaluate, cdp, width, page }) => {
  const settle = () => evaluate('new Promise(resolve=>setTimeout(resolve,450))');
  const desktopUnchanged = async () => {
    assert.equal(await evaluate(`(() => {
      const link=document.querySelector('[data-public-compact]');
      const snapshot=()=>JSON.stringify([...document.querySelectorAll('main,main *,[role="dialog"],[role="dialog"] *')].filter(el=>el.getClientRects().length).map(el=>{
        const s=getComputedStyle(el),r=el.getBoundingClientRect();
        return [s.display,s.fontSize,s.padding,s.margin,s.gridTemplateColumns,r.width,r.height];
      }));
      link.disabled=true;const before=snapshot();link.disabled=false;return before===snapshot();
    })()`), true, page+': desktop appearance');
  };
  if(width>1024) await desktopUnchanged();
  else {
    const problems = await evaluate(`(() => {
      const errors=[];
      for(const el of document.querySelectorAll('main .academic-feature-copy p'))if(parseFloat(getComputedStyle(el).fontSize)>11)errors.push('Feature text is oversized');
      for(const el of document.querySelectorAll('main .ne-page-title,main .research-story-card h1'))if(parseFloat(getComputedStyle(el).fontSize)>18)errors.push('Public section heading is oversized');
      for(const el of document.querySelectorAll('main .ne-page-copy p,main .research-story-description p'))if(parseFloat(getComputedStyle(el).fontSize)>12)errors.push('Public introduction text is oversized');
      const filters=[...document.querySelectorAll('.ne-filters .ne-filter')];
      if(filters.length){
        const top=filters[0].getBoundingClientRect().top;
        for(const button of filters)if(Math.abs(button.getBoundingClientRect().top-top)>1||button.scrollWidth>button.clientWidth+1)errors.push('Event filters must fit one line');
        if(filters[0].parentElement.scrollWidth>filters[0].parentElement.clientWidth+1)errors.push('All event filters must fit inside their row ('+filters[0].parentElement.scrollWidth+'/'+filters[0].parentElement.clientWidth+'px)');
      }
      const columns=[...document.querySelectorAll('.ne-col-title')];
      if(columns.length===2&&Math.abs(columns[0].getBoundingClientRect().top-columns[1].getBoundingClientRect().top)>1)errors.push('Ongoing and Upcoming must share one row');
      for(const grid of document.querySelectorAll('main :is(.contents-cards,.students-cards,.academic-features-grid,.alphabetical-card-pages,.charter-grid-stack,.officials-grid)')) {
        if(!grid.getClientRects().length)continue;
        const gr=grid.getBoundingClientRect();
        const scrollable=grid.matches('.contents-cards,.students-cards,.academic-features-grid,.alphabetical-card-pages,.charter-grid-stack');
        if(scrollable){
          if(getComputedStyle(grid).overflowX!=='auto'||!getComputedStyle(grid).gridAutoFlow.includes('column'))errors.push('Card row must scroll horizontally');
          if(grid.scrollWidth>grid.clientWidth+1){
            grid.scrollTo({left:grid.scrollWidth,behavior:'instant'});
            if(grid.scrollLeft<1)errors.push('Later cards cannot be reached');
            grid.scrollTo({left:0,behavior:'instant'});
          }
        }else if(grid.scrollWidth>grid.clientWidth+1)errors.push('Static card grid overflows');
        for(const card of grid.children){
          if(card.hidden||!card.getClientRects().length)continue;
          const r=card.getBoundingClientRect();
          if(!scrollable&&(r.left<gr.left-1||r.right>gr.right+1))errors.push('A card is outside its grid');
          if(scrollable&&r.width>240)errors.push('Scrollable card is too wide');
          if(card.matches('.contents-card,.students-card,.official-card,.charter-card,.academic-feature-card')&&(r.height<160||r.height>250))errors.push(card.className+': collapsed or oversized ('+r.height+'px)');
          if(card.matches('.academic-feature-card')&&card.scrollHeight>card.clientHeight+1){
            if(getComputedStyle(card).overflowY!=='auto'||card.tabIndex<0)errors.push('Long feature text must remain scrollable by keyboard');
          }
        }
      }
      return errors;
    })()`);
    assert.deepEqual(problems, [], page+': compact cards at '+width);
  }
  if(page==='/students'&&width<=1024){
    await evaluate(`document.querySelector('.academic-breadcrumb').scrollIntoView({block:'center',behavior:'instant'})`);
    await settle();
    assert.equal(await evaluate(`document.querySelector('.students-contents-strip').getBoundingClientRect().top>=document.querySelector('.academic-breadcrumb').getBoundingClientRect().bottom-1`),true,'Students breadcrumb must not be covered by the contents section');
  }
  const capture = async (selector, name) => {
    if(process.env.CAPTURE!=='1'||![375,820].includes(width))return;
    if(!await evaluate(`!!document.querySelector('${selector}')`))return;
    await evaluate(`document.querySelector('${selector}').scrollIntoView({block:'center',behavior:'instant'})`);
    await settle();
    const shot=await cdp('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});
    require('node:fs').writeFileSync(require('node:path').resolve(__dirname,'../../storage/app/responsive-preview/'+page.replace(/[^a-z0-9-]/gi,'-')+'-'+name+'-'+width+'.png'),Buffer.from(shot.data,'base64'));
  };
  await capture('main .contents-cards,main .students-cards','compact-cards');
  await capture('main .academic-features-grid','compact-features');
  await capture('main .ne-events-section','compact-schedule');
  await capture('main .ne-filter-bar','compact-filters');
  await capture('main .research-story-card','compact-intro');
  if(process.env.MOBILE==='1'&&width===375) {
    const swipe=await evaluate(`(() => {
      const row=[...document.querySelectorAll('main .contents-cards,main .students-cards')].find(el=>el.scrollWidth>el.clientWidth+80);
      if(!row)return null;
      row.dataset.swipeTest='true';row.scrollIntoView({block:'center',behavior:'instant'});
      const r=row.getBoundingClientRect();return {x:r.left+Math.min(180,r.width-20),y:r.top+r.height/2};
    })()`);
    if(swipe){
      await cdp('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[swipe]});
      for(let step=1;step<=8;step++)await cdp('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x:swipe.x-step*15,y:swipe.y}]});
      await cdp('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});
      await settle();
      assert.equal(await evaluate(`document.querySelector('[data-swipe-test]').scrollLeft>20`),true,'Card row responds to a touch swipe');
      await evaluate(`const row=document.querySelector('[data-swipe-test]');row.scrollTo({left:0,behavior:'instant'});delete row.dataset.swipeTest;`);
    }
  }
  if(await evaluate(`!!document.querySelector('.alphabetical-card-selector button:not(.alphabetical-card-selector__all)')`)) {
    await evaluate(`document.querySelector('.alphabetical-card-selector button:not(.alphabetical-card-selector__all)').click()`);
    assert.equal(await evaluate(`document.querySelector('.alphabetical-card-selector [aria-current="page"]').classList.contains('alphabetical-card-selector__all')`),false,'Alphabetical group can be selected');
    await evaluate(`document.querySelector('.alphabetical-card-selector__all').click()`);
    assert.equal(await evaluate(`!!document.querySelector('.alphabetical-card-pages > [hidden]')`),false,'All cards can be restored');
  }
  const hasProgram=await evaluate(`!!document.querySelector('.dp-program-card-trigger')`);
  if(hasProgram) {
    await evaluate(`document.querySelector('.dp-program-card-trigger').click()`);
    await settle();
    assert.equal(await evaluate(`!document.querySelector('.dp-program-modal').hidden`),true,'Program details open');
    if(width>1024)await desktopUnchanged();
    else assert.deepEqual(await evaluate(`(() => {
      const dialog=document.querySelector('.dp-program-modal-dialog'),r=dialog.getBoundingClientRect(),errors=[];
      if(r.left<15||r.right>document.documentElement.clientWidth-15||r.top<0||r.bottom>innerHeight+1)errors.push('Program modal does not fit');
      if(dialog.scrollWidth>dialog.clientWidth+1)errors.push('Program modal scrolls horizontally');
      if(!dialog.querySelector('[data-program-modal-name]').textContent.trim())errors.push('Program information is missing');
      return errors;
    })()`),[],'Program modal at '+width);
    await capture('.dp-program-modal-dialog','compact-program-modal');
    await evaluate(`document.querySelector('.dp-program-modal-close').click()`);
    await settle();
    assert.equal(await evaluate(`document.querySelector('.dp-program-modal').hidden`),true,'Program details close');
  }
  for(const [trigger,dialog,close] of [
    ['[data-ne-modal-trigger]','.ne-modal-card','.ne-modal-close'],
    ['.student-qr-trigger','.student-qr-modal-panel','.student-qr-modal-close'],
    ['[data-student-admissions-person-trigger]','.student-admissions-person-modal-panel','.student-admissions-person-modal-close']
  ]) {
    if(!await evaluate(`!!document.querySelector('${trigger}')`))continue;
    await evaluate(`document.querySelector('${trigger}').click()`);
    await settle();
    if(width>1024)await desktopUnchanged();
    else assert.deepEqual(await evaluate(`(() => {
      const el=document.querySelector('${dialog}'),r=el.getBoundingClientRect(),errors=[];
      if(!el.getClientRects().length||r.left<0||r.right>document.documentElement.clientWidth+1||r.top<0||r.bottom>innerHeight+1)errors.push('Dialog must stay inside the viewport');
      if(el.scrollWidth>el.clientWidth+1)errors.push('Dialog scrolls horizontally');
      return errors;
    })()`),[],'Responsive dialog '+dialog+' at '+width);
    await capture(dialog,'compact-details');
    await evaluate(`document.querySelector('${close}').click()`);
    await settle();
  }
};
