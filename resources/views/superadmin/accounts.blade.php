<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PUP Taguig - Manage CMS Access</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- move your css to public/assets/css/manageaccounts.css --}}
    <link rel="stylesheet" href="{{ asset('assets/css/manageaccounts.css') }}">
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="logo-section">
            <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Logo" class="logo">
            <div class="logo-text">
                Hello,<br>
                {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}!
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.approvals.pending') }}" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Approvals</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.accounts') }}" class="nav-link active">
                    <i class="fas fa-users-gear"></i>
                    <span>Manage CMS Access</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.announcements') ?? '#' }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.content') ?? '#' }}" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.notifications') ?? '#' }}" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.audit') ?? '#' }}" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Audit Trails</span>
                </a>
            </li>

        </ul>
    </nav>

    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="topbar-right">
            <details class="user-menu">
                <summary class="user-profile">
                    <div class="user-avatar">
                        @php
                            $fn = (string) session('user_first_name');
                            $ini = ($fn ? strtoupper(substr($fn,0,1)) : 'A');
                            echo $ini ?: 'AD';
                        @endphp
                    </div>

                    <div class="user-info">
                        <div class="user-name">
                            {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}
                        </div>
                        <div class="user-role">
                            {{ session('user_role') ? e(session('user_role')) : 'ADMIN' }}
                        </div>
                    </div>

                    <i class="fas fa-chevron-down profile-chevron" style="color: #D4AF37;"></i>
                </summary>
                <div class="profile-dropdown">
                    <button type="button" class="profile-dropdown-item" onclick="openProfileModal(this)">
                        <i class="fas fa-user-pen"></i>
                        <span>Edit Profile</span>
                    </button>
                    <form method="POST" action="{{ route('superadmin.logout') }}">
                        @csrf
                        <button type="submit" class="profile-dropdown-item">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </header>

    @include('partials.profile_modal')

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Manage CMS Access</h1>
            <p class="page-subtitle">View faculty access, assign CMS roles, and manage authorized users.</p>
        </div>

        <div class="stats-row">
            <div class="stat-card"><div class="stat-icon si-total"><i class="fas fa-users"></i></div><div><div class="stat-val" id="cnt-total">0</div><div class="stat-lbl">Total Users</div></div></div>
            <div class="stat-card"><div class="stat-icon si-active"><i class="fas fa-circle-check"></i></div><div><div class="stat-val" id="cnt-active">0</div><div class="stat-lbl">Active</div></div></div>
            <div class="stat-card"><div class="stat-icon si-inactive"><i class="fas fa-circle-minus"></i></div><div><div class="stat-val" id="cnt-inactive">0</div><div class="stat-lbl">Inactive</div></div></div>
            <div class="stat-card"><div class="stat-icon si-suspended"><i class="fas fa-ban"></i></div><div><div class="stat-val" id="cnt-suspended">0</div><div class="stat-lbl">Suspended</div></div></div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-users-gear"></i> CMS Access List</div>
                <button class="action-btn" type="button" onclick="openAdd()"><i class="fas fa-user-plus"></i> Assign Access</button>
            </div>

            <div class="tab-nav">
                <button class="tab-btn active" data-role="all" onclick="switchRole('all')">
                    <i class="fas fa-th-list"></i> All <span class="count-pill" id="pill-all">0</span>
                </button>

                <button class="tab-btn" data-role="Admin" onclick="switchRole('Admin')">
                    <i class="fas fa-shield-halved"></i> Admin <span class="count-pill" id="pill-Admin">0</span>
                </button>

                <button class="tab-btn" data-role="Registrar" onclick="switchRole('Registrar')">
                    <i class="fas fa-id-card"></i> Registrar <span class="count-pill" id="pill-Registrar">0</span>
                </button>

                <button class="tab-btn" data-role="HAP" onclick="switchRole('HAP')">
                    <i class="fas fa-building-user"></i> HAP <span class="count-pill" id="pill-HAP">0</span>
                </button>

                <button class="tab-btn" data-role="Student Services" onclick="switchRole('Student Services')">
                    <i class="fas fa-handshake-angle"></i> Student Services
                    <span class="count-pill" id="pill-StudentServices">0</span>
                </button>

                <button class="tab-btn" data-role="Research and Extension" onclick="switchRole('Research and Extension')">
                    <i class="fas fa-flask"></i> Research & Extension
                    <span class="count-pill" id="pill-ResearchExtension">0</span>
                </button>

                <button class="tab-btn" data-role="Faculty" onclick="switchRole('Faculty')">
                    <i class="fas fa-chalkboard-user"></i> Faculty <span class="count-pill" id="pill-Faculty">0</span>
                </button>
            </div>

            <div class="filter-bar">
                <input type="text" id="srch" placeholder="Search by name, email or ID..." oninput="applyFilters()">
                <select id="stFil" onchange="applyFilters()">
                    <option value="">All Status</option>
                    <option>Active</option><option>Inactive</option><option>Suspended</option>
                </select>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>User</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tbody"></tbody>
                </table>
            </div>

            <div class="pagination">
                <div class="page-info" id="pgInfo">Showing 0</div>
                <div class="page-btns">
                    <button class="pbtn" type="button" onclick="changePg(-1)"><i class="fas fa-chevron-left"></i></button>
                    <button class="pbtn active" type="button" id="pgNum">1</button>
                    <button class="pbtn" type="button" onclick="changePg(1)"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div class="modal" id="userModal">
        <div class="mbox">
            <div class="mhead">
                <h2 id="mTitle"><i class="fas fa-user-plus"></i> Assign CMS Access</h2>
                <button class="cbtn" type="button" onclick="closeM('userModal')"><i class="fas fa-times"></i></button>
            </div>

            <div class="mbody">
    <div class="frow">
        <div class="fg">
            <label>First Name <span class="req">*</span></label>
            <input id="f-fn" placeholder="Auto-filled from faculty record">
        </div>
        <div class="fg">
            <label>Last Name <span class="req">*</span></label>
            <input id="f-ln" placeholder="Auto-filled from faculty record">
        </div>
    </div>

    <div class="frow">
    <div class="fg">
        <label>Email <span class="req">*</span></label>
        <input type="email" id="f-em" placeholder="Auto-filled from faculty record">
    </div>

    <div class="fg">
        <label>CMS Roles <span class="req">*</span></label>
        <select id="rolePicker">
            <option value="">Select Role</option>
        </select>
    </div>
</div>

<div class="frow">
    <div class="fg">
        <label>CMS Access Status <span class="req">*</span></label>
        <select id="f-st">
            <option value="">Select Status</option>
            <option>Active</option>
            <option>Inactive</option>
            <option>Suspended</option>
        </select>
    </div>

    <div class="fg">
        <div id="roleChips" class="role-chips-wrap"></div>

        <small class="role-help-text">
            Select one or more roles. The first selected role will be the primary role.
        </small>
    </div>
</div>
</div>

            <div class="mfoot">
                <button class="btn-outline" type="button" onclick="closeM('userModal')"><i class="fas fa-times"></i> Cancel</button>
                <button class="action-btn" type="button" onclick="saveUser()">
                    <i class="fas fa-user-check"></i> <span id="saveLbl">Save Access</span>
                </button>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal" id="viewModal">
        <div class="mbox">
            <div class="mhead">
                <h2><i class="fas fa-eye"></i> Access Details</h2>
                <button class="cbtn" type="button" onclick="closeM('viewModal')"><i class="fas fa-times"></i></button>
            </div>
            <div id="viewBody"></div>
            <div class="mfoot">
                <button class="btn-outline" type="button" onclick="closeM('viewModal')">Close</button>
                <button class="action-btn" type="button" onclick="editFromView()"><i class="fas fa-pen"></i> Edit Access</button>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal" id="confirmModal">
        <div class="mbox sm">
            <div class="mhead">
                <h2 id="cfTitle"><i class="fas fa-exclamation-triangle"></i> Confirm</h2>
                <button class="cbtn" type="button" onclick="closeM('confirmModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="mbody" style="text-align:center;padding:30px 25px;">
                <div id="cfIcon" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;"></div>
                <p id="cfMsg" style="font-size:15px;color:#555;line-height:1.5;"></p>
            </div>
            <div class="mfoot" style="justify-content:center;gap:12px;">
                <button class="btn-outline" type="button" onclick="closeM('confirmModal')">Cancel</button>
                <button id="cfOk" class="action-btn" type="button">Confirm</button>
            </div>
        </div>
    </div>

<script>
const RC = {
  GLOBAL_SUPERADMIN: 'r-admin',
  SYSTEM_SUPERADMIN: 'r-admin',

  REGISTRAR: 'r-registrar',
  HAP: 'r-hap',
  STUDENT_SERVICES: 'r-studentservices',
  RESEARCH_EXTENSION: 'r-research',
  FACULTY: 'r-faculty',
  'pupt:faculty': 'r-faculty',
  'pupt:student': 'r-student'
};
const SC = { Active:'sb-active', Inactive:'sb-inactive', Suspended:'sb-suspended' };
const SI = { Active:'fa-circle-check', Inactive:'fa-circle-minus', Suspended:'fa-ban' };
const AV = ['av-0','av-1','av-2','av-3','av-4','av-5'];
const CURRENT_ROLE = "{{ strtoupper(trim((string) session('user_role'))) }}";

const TAB_GROUPS = {
  'Admin': ['GLOBAL_SUPERADMIN', 'SYSTEM_SUPERADMIN'],
  'Registrar': ['REGISTRAR'],
  'HAP': ['HAP'],
  'Student Services': ['STUDENT_SERVICES'],
  'Research and Extension': ['RESEARCH_EXTENSION'],
  'Faculty': ['pupt:faculty'], // include base role pero label stays "Faculty"
};

function roleMatchesTab(roleCode, tabLabel){
  const list = TAB_GROUPS[tabLabel];
  if (!list) return roleCode === tabLabel; // fallback
  return list.includes(roleCode);
}

function normalizeRole(role){
  let r = String(role || '').trim();

  // keep base roles exactly (they are case-sensitive-ish)
  if (r.includes(':')) return r; // e.g., pupt:faculty, pupt:student

  // normalize everything else to CODE style
  r = r.toUpperCase().replace(/\s+/g, '_'); // "Global Superadmin" -> "GLOBAL_SUPERADMIN"
  return r;
}

function isGlobalSuperadmin(roleCode){
  return normalizeRole(roleCode) === 'GLOBAL_SUPERADMIN';
}

function isSystemSuperadmin(roleCode){
  return normalizeRole(roleCode) === 'SYSTEM_SUPERADMIN';
}

function canEditUser(targetRole){
  // SYSTEM_SUPERADMIN cannot edit GLOBAL_SUPERADMIN
  if (CURRENT_ROLE === 'SYSTEM_SUPERADMIN' && isGlobalSuperadmin(targetRole)) return false;
  return true;
}

function canSuspendUser(targetRole){
  // SYSTEM_SUPERADMIN cannot suspend GLOBAL_SUPERADMIN
  if (CURRENT_ROLE === 'SYSTEM_SUPERADMIN' && isGlobalSuperadmin(targetRole)) return false;
  return true;
}

/**
 * Fix: normalize/shape user object so UI always uses:
 * {id, fn, ln, em, rl, st, ll, nt, av}
 */
let nid = 4;
function shapeUser(raw = {}) {
  const fn = (raw.fn ?? raw.first_name ?? raw.user_first_name ?? '').toString();
  const ln = (raw.ln ?? raw.last_name ?? raw.user_last_name ?? '').toString();

    return {
    id: raw.id ?? raw.user_id ?? raw.userid ?? raw.userId ?? 0,
    fn,
    ln,
    em: (raw.em ?? raw.email ?? '').toString(),
    rl: normalizeRole(raw.rl ?? raw.role ?? ''),
    roles: Array.isArray(raw.roles)
      ? raw.roles.map(normalizeRole)
      : ((raw.rl ?? raw.role) ? [normalizeRole(raw.rl ?? raw.role)] : []),
    st: (raw.st ?? raw.status ?? 'Active').toString(),
    ll: (raw.ll ?? raw.last_login_at ?? raw.lastLoginAt ?? '—') || '—',
    nt: raw.nt ?? raw.notes ?? '',
    av: raw.av ?? AV[(nid++) % AV.length],
  };
}

const ROLES = @json(json_decode($rolesJson ?? '[]', true));
let users = @json(json_decode($usersJson ?? '[]', true));

const ROLE_NAME_BY_CODE = (ROLES || []).reduce((acc, r) => {
  acc[String(r.code)] = String(r.name);
  return acc;
}, {});

function roleLabel(code){
  const c = String(code || '');
  if (c === 'pupt:faculty') return 'Faculty';
  if (c === 'pupt:student') return 'Student';
  return ROLE_NAME_BY_CODE[c] || c;
}

function fillRoleOptions() {
  const sel = document.getElementById('rolePicker');
  if (!sel) return;

  const opts = (ROLES || [])
    .filter(r => String(r.code) !== 'LIBRARY')
    .filter(r => String(r.code) !== 'GLOBAL_SUPERADMIN')
    .filter(r => !(CURRENT_ROLE === 'SYSTEM_SUPERADMIN' && String(r.code) === 'SYSTEM_SUPERADMIN'))
    .filter(r => !String(r.code).includes(':'))
    .map(r => {
      const code = String(r.code);
      const name = String(r.name);

      if (code === 'FACULTY') {
        return `<option value="pupt:faculty">${name}</option>`;
      }

      return `<option value="${code}">${name}</option>`;
    })
    .join('');

  sel.innerHTML = `<option value="">Select Role</option>` + opts;
}
fillRoleOptions();

function renderRoleChips() {
  const box = document.getElementById('roleChips');
  if (!box) return;

  if (!selectedRoles.length) {
    box.innerHTML = '';
    return;
  }

  box.innerHTML = selectedRoles.map((code, idx) => `
    <span class="role-badge ${RC[code] || 'r-student'}" style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;margin:6px;">
      ${roleLabel(code)}
      ${idx === 0 ? '<small style="opacity:.8">(Primary)</small>' : ''}
      <button
        type="button"
        onclick="removeRoleChip('${String(code).replace(/'/g, "\\'")}')"
        style="border:none;background:transparent;color:inherit;cursor:pointer;font-size:14px;line-height:1;"
      >&times;</button>
    </span>
  `).join('');
}

function addRoleChip(code) {
  code = normalizeRole(code);
  if (!code) return;
  if (selectedRoles.includes(code)) return;

  selectedRoles.push(code);
  renderRoleChips();
}

function removeRoleChip(code) {
  selectedRoles = selectedRoles.filter(r => r !== code);
  renderRoleChips();
}

document.addEventListener('change', function(e){
  if (e.target && e.target.id === 'rolePicker') {
    const val = e.target.value;
    if (val) addRoleChip(val);
    e.target.value = '';
  }
});
users = (Array.isArray(users) ? users : []).map(shapeUser);

let curRole='all', editId=null, viewId=null, pg=1;
let selectedRoles = [];
const PP=10;

function filtered(){
  const q=(document.getElementById('srch').value||'').toLowerCase();
  const st=document.getElementById('stFil').value;
  return users.filter(u=>{
    const mr = (curRole === 'all') || roleMatchesTab(u.rl, curRole);
    const mq=!q||`${u.fn} ${u.ln} ${u.em} ${u.id}`.toLowerCase().includes(q);
    const ms=!st||u.st===st;
    return mr&&mq&&ms;
  });
}

function render(){
  const f=filtered(), tot=f.length, s=(pg-1)*PP, sl=f.slice(s,s+PP);
  const tb=document.getElementById('tbody');

  if(!sl.length){
    tb.innerHTML=`<tr><td colspan="8"><div class="empty"><i class="fas fa-users-slash"></i><p>No users found.</p></div></td></tr>`;
  } else {
    tb.innerHTML=sl.map((u,i)=>{
      const ini = `${(u.fn || 'U').charAt(0)}${(u.ln || 'N').charAt(0)}`;
      const rc=RC[u.rl]||'r-student', sc=SC[u.st]||'sb-inactive', si=SI[u.st]||'fa-circle';
      const susp = (u.st === 'Active');

const allowEdit = canEditUser(u.rl);
const allowSuspend = canSuspendUser(u.rl);

const editBtn = allowEdit
  ? `<button class="bico bi-edit" title="Edit" onclick="openEdit(${u.id})"><i class="fas fa-pen"></i></button>`
  : ``; // hide completely

let statusBtn = '';
if (allowSuspend) {
  statusBtn = susp
    ? `<button class="bico bi-suspend" title="Suspend" onclick="doConfirm(${u.id},'suspend')"><i class="fas fa-ban"></i></button>`
    : `<button class="bico bi-activate" title="Activate" onclick="doConfirm(${u.id},'activate')"><i class="fas fa-circle-check"></i></button>`;
} else {
  statusBtn = ``; // hide completely
}

      return `<tr>
        <td style="color:#bbb;font-size:12px">${s+i+1}</td>
        <td>
          <div class="user-cell">
            <div class="avatar ${u.av}">${ini}</div>
            <div>
              <div class="uname">${u.fn} ${u.ln}</div>
              <div class="uemail">${u.em}</div>
            </div>
          </div>
        </td>
        <td><span class="role-badge ${rc}">${roleLabel(u.rl)}</span></td>
        <td><span class="sbadge ${sc}"><i class="fas ${si}" style="font-size:9px"></i> ${u.st}</span></td>
        <td style="color:#888;font-size:12px">${u.ll}</td>
        <td>
          <div class="actions">
            <button class="bico bi-view" title="View" onclick="viewUser(${u.id})"><i class="fas fa-eye"></i></button>
${editBtn}
${statusBtn}
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  document.getElementById('pgInfo').textContent =
    tot ? `Showing ${s+1}–${Math.min(s+PP,tot)} of ${tot} user${tot!==1?'s':''}` : 'No users found';

  document.getElementById('pgNum').textContent=pg;
  updateCounts();
}

function updateCounts(){
  const rc = {};
  const sc = {Active:0,Inactive:0,Suspended:0};

  users.forEach(u=>{
    rc[u.rl] = (rc[u.rl] || 0) + 1;
    sc[u.st] = (sc[u.st] || 0) + 1;
  });

  document.getElementById('cnt-total').textContent = users.length;
  document.getElementById('cnt-active').textContent = sc.Active;
  document.getElementById('cnt-inactive').textContent = sc.Inactive;
  document.getElementById('cnt-suspended').textContent = sc.Suspended;

  document.getElementById('pill-all').textContent = users.length;

const countByTab = (tabLabel) => {
  return users.reduce((n,u)=> n + (roleMatchesTab(u.rl, tabLabel) ? 1 : 0), 0);
};

const setPill = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

setPill('pill-Admin', countByTab('Admin'));
setPill('pill-Registrar', countByTab('Registrar'));
setPill('pill-HAP', countByTab('HAP'));
setPill('pill-StudentServices', countByTab('Student Services'));
setPill('pill-ResearchExtension', countByTab('Research and Extension'));
setPill('pill-Faculty', countByTab('Faculty'));
}

function applyFilters(){ pg=1; render(); }
function switchRole(r){
  curRole=r; pg=1;
  document.querySelectorAll('[data-role]').forEach(b=>b.classList.toggle('active',b.dataset.role===r));
  render();
}
function changePg(d){
  const t=filtered().length, m=Math.ceil(t/PP)||1;
  pg=Math.max(1,Math.min(pg+d,m));
  render();
}

function clrForm(){
  ['f-fn','f-ln','f-em'].forEach(x => document.getElementById(x).value='');
  document.getElementById('f-st').value='Active';
  selectedRoles = [];
  renderRoleChips();

  const picker = document.getElementById('rolePicker');
  if (picker) picker.value = '';
}

function openAdd(){
  editId = null;
  clrForm();
  document.getElementById('mTitle').innerHTML = '<i class="fas fa-user-plus"></i> Assign CMS Access';
  document.getElementById('saveLbl').textContent = 'Save Access';
  openM('userModal');
}
function openEdit(id){
  editId = id;
  const u = users.find(x => x.id === id);
  if(!u) return;

  document.getElementById('f-fn').value = u.fn || '';
  document.getElementById('f-ln').value = u.ln || '';
  document.getElementById('f-em').value = u.em || '';
  document.getElementById('f-st').value = u.st || 'Active';

  selectedRoles = Array.isArray(u.roles) && u.roles.length
    ? [...u.roles]
    : (u.rl ? [u.rl] : []);
  renderRoleChips();

  const picker = document.getElementById('rolePicker');
  if (picker) picker.value = '';

  document.getElementById('mTitle').innerHTML = '<i class="fas fa-pen"></i> Edit CMS Access';
  document.getElementById('saveLbl').textContent = 'Update Access';

  openM('userModal');
}

const STORE_URL = "{{ route('superadmin.accounts.store') }}";
const UPDATE_URL_TPL = "{{ route('superadmin.accounts.update', ['id' => '__ID__']) }}";
const STATUS_URL_TPL = "{{ route('superadmin.accounts.status', ['id' => '__ID__']) }}";

const urlWithId = (tpl, id) => tpl.replace('__ID__', String(id));
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

async function saveUser(){
  const fn=document.getElementById('f-fn').value.trim();
  const ln=document.getElementById('f-ln').value.trim();
  const em=document.getElementById('f-em').value.trim();
  const roles = [...selectedRoles];
  const st=document.getElementById('f-st').value;

  if(!fn||!ln||!em||!roles.length||!st){
    alert('Please fill in all required fields.');
    return;
  }

  const isEdit = !!editId;
  const url = isEdit ? urlWithId(UPDATE_URL_TPL, editId) : STORE_URL;
  const method = isEdit ? 'PUT' : 'POST';

  try {
    const res = await fetch(url, {
      method,
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': CSRF
      },
      body: JSON.stringify({
  first_name: fn,
  last_name: ln,
  email: em,
  roles: roles,
  status: st
})
    });

    const text = await res.text();
    let data = {};
    try { data = JSON.parse(text); } catch(e) {}

    if(!res.ok){
      alert(`Request failed (${res.status}).\n${data.message || 'Check Network tab + server logs.'}`);
      return;
    }

    if(!data.ok){
      alert(data.message || 'Failed.');
      return;
    }

    if(isEdit){
      // update local list
      const idx = users.findIndex(x => x.id === editId);
      if(idx >= 0){
        users[idx] = shapeUser({ ...users[idx], ...data.user });
      }
      editId = null;
      closeM('userModal');
      render();
      alert('CMS access updated successfully.');
    } else {
      const newUser = shapeUser(data.user);
      users.unshift(newUser);
      closeM('userModal');
      render();
      alert('CMS access assigned successfully.');
    }

  } catch (err){
    console.error(err);
    alert('Network/JS error. Check console.');
  }
}

function viewUser(id){
  viewId=id;
  const u=users.find(x=>x.id===id); if(!u) return;
  const rc=RC[u.rl]||'r-student', sc=SC[u.st]||'sb-inactive';

  document.getElementById('viewBody').innerHTML=`
    <div class="vhead">
      <div class="vav ${u.av}">${(u.fn||'U').charAt(0)}${(u.ln||'N').charAt(0)}</div>
      <div>
        <div class="vname">${u.fn} ${u.ln}</div>
        <div class="vsub">
          <span class="role-badge ${rc}">${roleLabel(u.rl)}</span>
          <span class="sbadge ${sc}">${u.st}</span>
        </div>
      </div>
    </div>
    <div class="mbody">
      <div class="vgrid">
        <div class="vf"><div class="vfl">Full Name</div><div class="vfv">${u.fn} ${u.ln}</div></div>
        <div class="vf"><div class="vfl">Email</div><div class="vfv">${u.em}</div></div>
        <div class="vf"><div class="vfl">CMS Roles</div><div class="vfv">${(u.roles || [u.rl]).map(roleLabel).join(', ')}</div></div>
        <div class="vf"><div class="vfl">CMS Access Status</div><div class="vfv">${u.st}</div></div>
        <div class="vf"><div class="vfl">Last Login</div><div class="vfv">${u.ll}</div></div>
        ${u.nt?`<div class="vf" style="grid-column:1/-1"><div class="vfl">Notes</div><div class="vfv">${u.nt}</div></div>`:''}
      </div>
    </div>`;

    const editActionBtn = document.querySelector('#viewModal .mfoot .action-btn');
if (editActionBtn) {
  editActionBtn.style.display = canEditUser(u.rl) ? '' : 'none';
}

  openM('viewModal');
}
function editFromView(){ closeM('viewModal'); openEdit(viewId); }

function doConfirm(id, action){
  const u=users.find(x=>x.id===id); if(!u)return;
  const cfg={
    suspend:{
      title:'Suspend User',
      msg:`Suspend <strong>${u.fn} ${u.ln}</strong>?<br><small style="color:#888">They will lose access until reactivated.</small>`,
      icon:'fa-ban',bg:'rgba(220,53,69,0.1)',col:'#dc3545',
      bs:'background:linear-gradient(135deg,#dc3545,#b02a37)',lbl:'Suspend'
    },
    activate:{
      title:'Activate User',
      msg:`Activate <strong>${u.fn} ${u.ln}</strong>?<br><small style="color:#888">They will regain system access.</small>`,
      icon:'fa-circle-check',bg:'rgba(40,167,69,0.1)',col:'#28a745',
      bs:'background:linear-gradient(135deg,#28a745,#1e7e34)',lbl:'Activate'
    },
  };
  const c=cfg[action];

  document.getElementById('cfTitle').innerHTML=`<i class="fas ${c.icon}" style="color:${c.col}"></i> ${c.title}`;
  document.getElementById('cfIcon').style.cssText=
    `width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;background:${c.bg};color:${c.col}`;
  document.getElementById('cfIcon').innerHTML=`<i class="fas ${c.icon}"></i>`;
  document.getElementById('cfMsg').innerHTML=c.msg;

  const btn=document.getElementById('cfOk');
  btn.style.cssText=c.bs+';color:#fff;border:none;padding:10px 22px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;gap:8px;transition:0.3s;';
  btn.innerHTML=`<i class="fas ${c.icon}"></i> ${c.lbl}`;
  btn.onclick = async () => {
  const nextStatus = (action === 'suspend') ? 'Suspended' : 'Active';
  const url = urlWithId(STATUS_URL_TPL, id);

  try {
    const res = await fetch(url, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': CSRF
      },
      body: JSON.stringify({ status: nextStatus })
    });

    const text = await res.text();
    let data = {};
    try { data = JSON.parse(text); } catch(e) {}

    if(!res.ok){
      alert(`Request failed (${res.status}).\n${data.message || 'Check Network tab + server logs.'}`);
      return;
    }

    if(!data.ok){
      alert(data.message || 'Failed.');
      return;
    }

    // update UI
    u.st = data.status || nextStatus;
    closeM('confirmModal');
    render();

  } catch (e) {
    console.error(e);
    alert('Network/JS error. Check console.');
  }
};

  openM('confirmModal');
}

function openM(id){ const m=document.getElementById(id); if(m)m.classList.add('active'); }
function closeM(id){ const m=document.getElementById(id); if(m)m.classList.remove('active'); }

window.addEventListener('click',e=>{
  document.querySelectorAll('.modal').forEach(m=>{
    if(e.target===m) m.classList.remove('active');
  });
});

function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('collapsed');
}

render();
</script>

</body>
</html>

