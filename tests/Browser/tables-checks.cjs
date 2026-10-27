const assert=require('node:assert/strict');

module.exports=async ({evaluate,cdp,width,page})=>{
  const settle=()=>evaluate('new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))');
  const geometry=()=>evaluate(`(() => {
    const problems=[];
    const analytics=document.querySelector('#analyticsTab.active');
    if(analytics&&innerWidth<=1024) {
      const grid=analytics.querySelector('.analytics-split-grid');
      if(getComputedStyle(grid).gridTemplateColumns.split(' ').filter(Boolean).length!==1)problems.push('Analytics has an unintended extra grid column');
      let previousBottom=null;
      for(const panel of grid.children) {
        if(!panel.matches('.analytics-panel'))continue;
        const r=panel.getBoundingClientRect();
        if(Math.abs(r.width-grid.clientWidth)>1)problems.push('Analytics panel does not fill the available column: '+panel.className+' width='+Math.round(r.width)+' available='+grid.clientWidth);
        if(previousBottom!==null&&r.top<previousBottom-1)problems.push('Analytics panels overlap vertically');
        previousBottom=r.bottom;
      }
      for(const label of analytics.querySelectorAll('.chart-label')) {
        const r=label.getBoundingClientRect(),style=getComputedStyle(label);
        if(r.height>parseFloat(style.lineHeight)*3+1)problems.push('Analytics chart label is squeezed into vertical text');
      }
    }
    for(const table of document.querySelectorAll('table[data-responsive-cards]')) {
      if(!table.getBoundingClientRect().height)continue;
      if(getComputedStyle(table).display!==(innerWidth<=1024?'block':'table'))problems.push('Wrong table layout: '+table.className);
      if(innerWidth>1024)continue;
      if(table.scrollWidth>table.clientWidth+1)problems.push('Table requires horizontal scrolling');
      for(const row of table.querySelectorAll('tbody > tr:not(.cms-card-empty)')) {
        if(getComputedStyle(row).display!=='grid')problems.push('Row is not a card');
        for(const cell of row.cells) {
          if(!cell.headers||!document.getElementById(cell.headers))problems.push('Missing semantic header');
          if(!cell.querySelector('.cms-card-label')?.getClientRects().length)problems.push('Missing card label');
          const value=cell.querySelector('.cms-cell-value');
          if(!value||value.scrollWidth>value.clientWidth+1)problems.push('Cell value overflows');
          if(parseFloat(getComputedStyle(value).fontSize)>13)problems.push('Cell font is too large');
          for(const button of cell.querySelectorAll('button,a.btn')) {
            const r=button.getBoundingClientRect();
            if(r.width<43.9||r.height<43.9)problems.push('Touch target is too small: '+button.className+' '+r.width+'x'+r.height);
            if(button.scrollWidth>button.clientWidth+1)problems.push('Button label overflows');
            if(!button.textContent.trim()&&!button.getAttribute('aria-label'))problems.push('Unlabelled icon button');
          }
        }
      }
    }
    for(const root of document.querySelectorAll('[data-compact-controls]')) {
      if(!root.getBoundingClientRect().height)continue;
      for(const el of root.querySelectorAll('*')) {
        if(innerWidth>1024&&el.closest('table.cms-adaptive-table'))continue;
        if(el.closest('thead,colgroup')||['SCRIPT','STYLE'].includes(el.tagName))continue;
        const r=el.getBoundingClientRect(),s=getComputedStyle(el);
        if(!r.width||!r.height||s.visibility==='hidden'||s.opacity==='0'||s.position==='fixed')continue;
        if(r.left< -1||r.right>document.documentElement.clientWidth+1)problems.push('Control outside viewport: '+el.tagName+'.'+el.className+' left='+Math.round(r.left)+' right='+Math.round(r.right)+' parent='+el.parentElement.className);
      }
    }
    for(const arrow of document.querySelectorAll('[data-compact-controls] .cms-pagination-arrow')) {
      if(!arrow.closest('.top-tab-content')||arrow.closest('.top-tab-content').classList.contains('active')) {
        const r=arrow.getBoundingClientRect();if(r.width&&(!r.height||!arrow.querySelector('path')))problems.push('Broken pagination arrow');
      }
    }
    return problems;
  })()`);
  await settle();
  assert.deepEqual(await geometry(),[],page+': adaptive cards at '+width);
  const menus=await evaluate(`(() => {
    const problems=[];
    for(const dropdown of document.querySelectorAll('.main-content .cms-dropdown')) {
      const trigger=dropdown.querySelector('.cms-dropdown-trigger'),menu=dropdown.querySelector('.cms-dropdown-menu');
      if(!trigger?.getBoundingClientRect().width||!trigger.getBoundingClientRect().height||!menu)continue;
      trigger.click();
      const r=menu.getBoundingClientRect();
      if(r.width<Math.min(220,document.documentElement.clientWidth-32)-1)problems.push('Dropdown is squeezed: '+dropdown.id+' width='+Math.round(r.width));
      if(r.left<0||r.right>document.documentElement.clientWidth+1)problems.push('Dropdown extends outside viewport: '+dropdown.id);
      for(const option of menu.querySelectorAll('.cms-dropdown-option')) {
        if(option.textContent.trim().length>22)continue;
        const s=getComputedStyle(option),height=option.getBoundingClientRect().height;
        if(height>parseFloat(s.fontSize)*1.5+parseFloat(s.paddingTop)+parseFloat(s.paddingBottom)+2)problems.push('Short dropdown option wraps: '+option.textContent.trim());
      }
      trigger.click();
    }
    return problems;
  })()`);
  assert.deepEqual(menus,[],page+': readable dropdown menus at '+width);
  if(process.env.CAPTURE==='1'&&page.includes('tab=analytics')&&width>=1280) {
    await evaluate(`document.querySelector('#analyticsPresetDropdown .cms-dropdown-trigger').click()`);
    const clip=await evaluate(`(() => {const r=document.querySelector('#analyticsPresetDropdown .cms-dropdown-menu').getBoundingClientRect();return {x:Math.max(0,r.left-120),y:Math.max(0,r.top-90),width:Math.min(500,innerWidth-r.left+120),height:Math.min(410,innerHeight-r.top+90),scale:1}})()`);
    const shot=await cdp('Page.captureScreenshot',{format:'png',clip});
    require('node:fs').writeFileSync(require('node:path').resolve(__dirname,'../../storage/app/cms-responsive-preview/analytics-dropdown-'+width+'.png'),Buffer.from(shot.data,'base64'));
    await evaluate(`document.querySelector('#analyticsPresetDropdown .cms-dropdown-trigger').click()`);
  }
  if(page.includes('/accounts')) {
    await evaluate(`window.__tableUsers=users.slice();users=Array.from({length:35},(_,i)=>({...window.__tableUsers[0],id:800000+i,fn:'Responsive User '+i,ln:'Preview',em:'responsive'+i+'@example.test',rl:'REGISTRAR',st:'Active'}));applyFilters()`);
    try {
      await settle();
      await evaluate('changePg(1)');await settle();
      assert.equal(await evaluate('pg'),2,'Access pagination');
      assert.ok((await evaluate(`document.getElementById('cmsAccessPageStatus').textContent`)).startsWith('Page 2 of'));
      await evaluate(`document.getElementById('srch').value='responsive21@example.test';document.getElementById('srch').dispatchEvent(new Event('input',{bubbles:true}))`);await settle();
      assert.equal(await evaluate('filtered().length'),1,'Access search');
      assert.deepEqual(await geometry(),[],'Newly rendered access rows keep their card layout');
      await evaluate(`document.querySelector('#tbody .bi-view').click()`);
      assert.equal(await evaluate(`document.getElementById('viewModal').classList.contains('active')`),true,'Access View action');
      await evaluate(`closeM('viewModal')`);
    } finally {
      await evaluate(`users=window.__tableUsers;delete window.__tableUsers;document.getElementById('srch').value='';applyFilters()`);await settle();
    }
  }
  if(page.includes('approvals/pending')) {
    await evaluate(`document.querySelector('#pendingRows button[onclick*="openDetails"]')?.click()`);
    assert.equal(await evaluate(`document.getElementById('detailsModal').classList.contains('active')`),true,'Request View Details');
    await evaluate('closeDetails()');
    assert.equal(await evaluate(`(() => {const link=[...document.querySelectorAll('.cms-responsive-pagination a[aria-label="Next page"]')].at(-1);return !link||new URL(link.href).searchParams.has('history_page')})()`),true,'Processed request links preserve their own page parameter');
  }
  if(page.includes('database-backups')) {
    await evaluate(`(() => {const body=document.querySelector('.backup-table tbody');window.__backupRows=body.innerHTML;body.innerHTML='<tr><td>backup-responsive-preview-2026.sql</td><td>12 MB</td><td>Private storage</td><td>Responsive Preview</td><td>Oct 27, 2026 12:00 PM</td><td><div class="backup-row-actions"><a class="btn" href="#" title="Download">Download</a><button class="btn" type="button" data-delete-url="/preview-only" data-backup-name="Responsive preview" onclick="openBackupDeleteModal(this)">Delete</button></div></td></tr>'})()`);
    try {
      await settle();assert.deepEqual(await geometry(),[],'Populated backup history');
      await evaluate(`document.querySelector('.backup-table button').click()`);
      assert.equal(await evaluate(`document.getElementById('backupDeleteModal').classList.contains('active')`),true,'Backup confirmation still opens');
      await evaluate('closeBackupDeleteModal()');
    } finally {await evaluate(`document.querySelector('.backup-table tbody').innerHTML=window.__backupRows;delete window.__backupRows`);await settle();}
  }
  if(page.includes('tab=analytics')) {
    await evaluate(`updateUploadAnalyticsChart({total_uploads:105,roles:[{role:'Superadmin',count:71,percentage:68},{role:'Registrar',count:34,percentage:32}],sources:[]})`);
    const checkUploadRoleLayout=async()=>assert.deepEqual(await evaluate(`Array.from(document.querySelectorAll('#analyticsTab .upload-role-row')).flatMap(row=>{
      const name=row.querySelector('.upload-role-name').getBoundingClientRect();
      const value=row.querySelector('.upload-role-value');
      const bounds=value.getBoundingClientRect();
      const bar=row.querySelector('.upload-role-bar').getBoundingClientRect();
      const fontSize=parseFloat(getComputedStyle(value).fontSize);
      return [bounds.height>fontSize*1.8?'Count and percentage must stay on one line':null,
        bounds.left<name.right-1?'Role and percentage overlap':null,
        bar.top<Math.max(name.bottom,bounds.bottom)-1?'Progress bar overlaps role details':null].filter(Boolean);
    })`),[],'Upload role counts and percentages');
    await checkUploadRoleLayout();
    await evaluate(`setTextSafe('kpiVisitors','17,515');setTextSafe('kpiAvgDuration','0m 40s');setTextSafe('kpiBounce','94.26%');setTextSafe('engSessions','18,250');setTextSafe('engPageviews','28,915');setTextSafe('engPagesPerSession','1.58');`);
    await evaluate(`updateUploadAnalyticsChart({total_uploads:12345,roles:[{role:'Research and Extension Department',count:12345,percentage:100}],sources:[{source:'Campus announcements',count:12345}]});updateFeedbackResultsChart({question_1_avg:3.8,overall_average:3.8,final_rating:'Outstanding',total_responses:12345,outstanding:12345});renderServerHealth({status:'Healthy',cpu_usage:'42%',memory_usage:'63%',last_updated:'Oct 27, 2026 12:00 PM'});`);
    await settle();assert.deepEqual(await geometry(),[],'Populated analytics dashboard');
    await checkUploadRoleLayout();
    if(process.env.CAPTURE==='1'&&[375,768,1024,1280,1440,1920].includes(width)) {
      const fs=require('node:fs'),path=require('node:path');
      for(const section of ['analytics-engagement-panel','upload-analytics-panel','analytics-feedback-panel']) {
        await evaluate(`window.scrollTo({top:document.querySelector('#analyticsTab .${section}').getBoundingClientRect().top+scrollY-90,behavior:'instant'})`);
        const shot=await cdp('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});
        fs.writeFileSync(path.resolve(__dirname,'../../storage/app/cms-responsive-preview/analytics-'+section+'-'+width+'.png'),Buffer.from(shot.data,'base64'));
      }
    }
    await evaluate(`document.getElementById('feedbackQuestionToggle').click()`);
    assert.deepEqual(await geometry(),[],'Expanded feedback questions');
    await evaluate(`document.getElementById('feedbackQuestionToggle').click();document.querySelector('#analyticsPresetDropdown .cms-dropdown-trigger').click();document.querySelector('#analyticsPresetDropdown [data-value="CUSTOM"]').click()`);
    await settle();assert.deepEqual(await geometry(),[],'Custom analytics dates');
    await evaluate(`document.querySelector('#analyticsPresetDropdown [data-value="ALL"]').click()`);
  }
  if(width<=1024) {
    await evaluate(`window.__longCell=document.querySelector('table.cms-adaptive-table tbody tr:not(.cms-card-empty) .cms-cell-value');if(window.__longCell){window.__longText=document.createElement('span');window.__longText.textContent='LongUnbrokenValue'.repeat(30);window.__longCell.append(window.__longText)}`);
    assert.deepEqual(await geometry(),[],'Long values must wrap in cards');
    await evaluate('window.__longText?.remove();delete window.__longText;delete window.__longCell');
  }
  const ax=await cdp('Accessibility.getFullAXTree');
  const hasVisibleTable=await evaluate(`[...document.querySelectorAll('table')].some(table=>table.getBoundingClientRect().width&&table.getBoundingClientRect().height)`);
  if(hasVisibleTable) assert.ok(ax.nodes.some(node=>!node.ignored&&node.role?.value==='table'),'Semantic tables survive card presentation');
  if(page.includes('/analytics/export/pdf')) {
    await cdp('Emulation.setEmulatedMedia',{media:'print'});
    try {
      assert.equal(await evaluate(`getComputedStyle(document.querySelector('table')).display`),'table','Printed analytics keeps its table layout');
      assert.equal(await evaluate(`getComputedStyle(document.querySelector('.cms-card-label')).display`),'none','Card labels stay out of printed reports');
    } finally {await cdp('Emulation.setEmulatedMedia',{media:''});}
  }
};
