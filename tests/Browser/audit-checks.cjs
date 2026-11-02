const assert = require('node:assert/strict');

module.exports = async ({ evaluate, cdp, width }) => {
  const key = async (name, modifiers=0) => {
    const codes={Enter:13,Escape:27,Tab:9,ArrowDown:40,ArrowUp:38,Home:36,End:35};
    await cdp('Input.dispatchKeyEvent', { type: 'keyDown', key: name, code: name, modifiers, windowsVirtualKeyCode: codes[name], ...(name === 'Enter' ? {text:'\r',unmodifiedText:'\r'} : {}) });
    await cdp('Input.dispatchKeyEvent', { type: 'keyUp', key: name, code: name });
  };
  const geometry = async () => evaluate(`(() => {
    const problems=[];
    const root=document.querySelector('.cms-audit-page');
    const table=root.querySelector('.audit-table');
    const cards=innerWidth<=1024;
    const rows=[...document.querySelectorAll('#logBody tr[data-log-id]')];
    if(getComputedStyle(table).display!==(cards?'block':'table'))problems.push('Incorrect table/card layout');
    for(const row of rows) {
      if(getComputedStyle(row).display!==(cards?'grid':'table-row'))problems.push('Incorrect row layout');
      if(row.cells.length!==8)problems.push('Missing audit field');
      for(const cell of row.cells) {
        if(!cell.headers||!document.getElementById(cell.headers))problems.push('Missing accessible column header');
        if(!cell.getBoundingClientRect().height)problems.push('Hidden field');
      }
      for(const el of row.querySelectorAll('.uname,.urole,.desc-text,.ip-text,.mod-badge,.action-badge,.time-text,.time-ago')) {
        if(el.scrollWidth>el.clientWidth+1||el.scrollHeight>el.clientHeight+1)problems.push('Truncated audit field: '+el.className);
      }
      if(cards) {
        const button=row.querySelector('.btn-view-sm').getBoundingClientRect();
        if(button.width<44||button.height<44)problems.push('View Details touch target too small');
        if([...row.querySelectorAll('.audit-field-label')].some(label=>!label.getClientRects().length))problems.push('Missing card label');
      }
    }
    for(const el of root.querySelectorAll('*')) {
      if(el.closest('thead,colgroup')||['SCRIPT','STYLE'].includes(el.tagName))continue;
      const r=el.getBoundingClientRect(),s=getComputedStyle(el);
      if(!r.width||!r.height||s.visibility==='hidden'||s.opacity==='0')continue;
      if(r.left< -1||r.right>document.documentElement.clientWidth+1)problems.push('Outside viewport: '+el.className);
    }
    const records=root.querySelector('.audit-records');
    if(records.scrollWidth>records.clientWidth+1||document.documentElement.scrollWidth>innerWidth+1)problems.push('Horizontal scrolling required');
    const fields=[...root.querySelector('.filter-bar').children].map(el=>el.getBoundingClientRect());
    if(innerWidth>=1025&&fields.some(field=>Math.abs(field.top-fields[0].top)>1))problems.push('Desktop controls must remain horizontal');
    if(innerWidth<=767&&fields.some(field=>Math.abs(field.width-fields[0].width)>1))problems.push('Mobile controls must use the full column');
    return problems;
  })()`);
  const assertPage = async () => {
    assert.equal(await evaluate(`JSON.stringify([...document.querySelectorAll('#logBody tr[data-log-id]')].map(row=>Number(row.dataset.logId)))===JSON.stringify(filtered().slice((pg-1)*PP,pg*PP).map(log=>log.id))`), true, 'Rendered records must match the filtered page');
  };

  await evaluate(`(() => {
    window.__auditOriginalRecords=NORMALIZED_LOGS.slice();
    const fixtures=Array.from({length:46},(_,i)=>normalizeLog({id:900000+i,user:'Responsive Reviewer '+i,role:'SUPERADMIN',action:i%2?'LOGIN':'UPDATED',module:i%2?'ACCOUNTS':'CONTENT',desc:'Audit responsive fixture '+i,ip:'127.0.0.1',ts:(i<15?'2026-10-':'2026-09-')+String(27-i%15).padStart(2,'0')+'T12:00:00'}));
    Object.assign(fixtures[0],{user:'LongUsername'.repeat(12),role:'LONG_ROLE_'.repeat(10),module:'AcademicModule'.repeat(12),desc:'Full audit description: '+('LongUnbrokenToken'.repeat(40))+' <script>this stays text</script> '+('A complete description must remain readable. '.repeat(30)),ip:'2001:0db8:1234:5678:abcd:ef01:2345:6789'});
    NORMALIZED_LOGS.splice(0,NORMALIZED_LOGS.length,...fixtures);
    switchType('all');clearFilters();
  })()`);
  try {
    assert.deepEqual(await geometry(), [], 'Audit fields and controls must fit at '+width);
    assert.equal(await evaluate(`document.querySelector('#logBody .desc-text').textContent===NORMALIZED_LOGS[0].desc`), true, 'Full description must be escaped and retained');
    await assertPage();

    await evaluate(`document.getElementById('nextBtn').click()`);
    await assertPage();
    assert.equal(await evaluate('pg'), 2, 'Next page');
    assert.equal(await evaluate('document.activeElement.id'), 'nextBtn', 'Pagination retains keyboard focus');
    await evaluate(`document.getElementById('prevBtn').click()`);
    assert.equal(await evaluate('pg'), 1, 'Previous page');
    await evaluate('goToPg(4)');
    assert.equal(await evaluate(`document.getElementById('pgInfo').textContent`), 'Showing 46-46 of 46 logs');
    assert.equal(await evaluate(`document.getElementById('nextBtn').disabled`), true);
    await evaluate('goToPg(1)');

    await evaluate(`document.getElementById('srch').value='fixture 21';document.getElementById('srch').dispatchEvent(new Event('input',{bubbles:true}))`);
    await assertPage();
    assert.equal(await evaluate('filtered().length'), 1, 'Search uses the original audit filtering logic');
    await evaluate(`clearFilters();document.querySelector('#auditActionDropdown .cms-dropdown-trigger').focus()`);
    await key('ArrowDown');
    assert.equal(await evaluate(`document.activeElement.dataset.value`), '', 'Arrow opens the action options');
    await key('ArrowDown');
    assert.equal(await evaluate(`document.activeElement.dataset.value`), 'LOGIN', 'Arrow selects the next action');
    await key('Enter');
    await assertPage();
    assert.equal(await evaluate(`filtered().length>0&&filtered().every(log=>log.action==='LOGIN')`), true, 'Action filter');
    await evaluate(`clearFilters();document.querySelector('[data-type="CONTENT"]').click()`);
    assert.equal(await evaluate('filtered().length>0&&filtered().every(isContentLog)'), true, 'Content tab filter');
    await evaluate(`document.querySelector('[data-type="ACCOUNT"]').click()`);
    assert.equal(await evaluate('filtered().length>0&&filtered().every(isAccountLog)'), true, 'Account tab filter');
    await evaluate(`switchType('all');clearFilters();document.querySelector('#auditRangeDropdown .cms-dropdown-trigger').click();document.querySelector('#auditRangeDropdown [data-value="CUSTOM"]').click();for(const id of ['auditRangeCustomStart','auditRangeCustomEnd']){const el=document.getElementById(id);el.value='2026-09';el.dispatchEvent(new Event('change',{bubbles:true}));}`);
    await assertPage();
    assert.equal(await evaluate(`filtered().length>0&&filtered().every(log=>log.ts.startsWith('2026-09'))`), true, 'Custom date range');
    assert.deepEqual(await geometry(), [], 'Custom date controls must fit at '+width);

    await evaluate(`clearFilters();document.querySelector('#logBody .btn-view-sm').focus()`);
    await key('Enter');
    assert.equal(await evaluate(`document.getElementById('logModal').classList.contains('active')`), true, 'Keyboard opens View Details');
    assert.equal(await evaluate(`document.getElementById('logDetail').textContent.includes(NORMALIZED_LOGS[0].desc)`), true, 'Details use the same record');
    assert.equal(await evaluate(`document.activeElement.matches('#logModal .cbtn')`), true, 'Dialog receives focus');
    await key('Tab',8);
    assert.equal(await evaluate(`document.activeElement.matches('#logModal .mfoot button')`), true, 'Shift+Tab stays inside the dialog');
    await key('Tab');
    assert.equal(await evaluate(`document.activeElement.matches('#logModal .cbtn')`), true, 'Tab stays inside the dialog');
    assert.equal(await evaluate(`(() => {const el=document.querySelector('#logModal .mbox');return el.scrollWidth<=el.clientWidth+1})()`), true, 'Long modal content wraps');
    await key('Escape');
    assert.equal(await evaluate(`document.activeElement.matches('#logBody .btn-view-sm')`), true, 'Dialog returns focus to its trigger');

    await evaluate(`document.getElementById('srch').value='__no_audit_results__';applyFilters()`);
    assert.equal(await evaluate(`document.querySelector('.audit-empty-row')!==null`), true, 'Empty state');
    assert.equal(await evaluate(`document.getElementById('pgInfo').textContent`), 'Showing 0 of 0 logs');
    await evaluate('clearFilters()');
    const ax=await cdp('Accessibility.getFullAXTree');
    assert.ok(ax.nodes.some(node=>!node.ignored&&node.role?.value==='table'&&node.name?.value==='Audit trail'), 'Accessible audit table');
    assert.ok(ax.nodes.some(node=>!node.ignored&&node.role?.value==='columnheader'&&node.name?.value==='Description'), 'Accessible column headers survive card layout');
    assert.ok(ax.nodes.some(node=>!node.ignored&&node.role?.value==='cell'), 'Accessible cells survive card layout');
  } finally {
    await evaluate(`NORMALIZED_LOGS.splice(0,NORMALIZED_LOGS.length,...window.__auditOriginalRecords);delete window.__auditOriginalRecords;switchType('all');clearFilters();document.getElementById('cmsToastWrap')?.replaceChildren()`);
  }
};
