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
            <img src="{{ asset('assets/static_img/pupt_cms_logo.png') }}" alt="PUPT CMS Logo" class="logo">
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
                    @if(($pendingApprovalCount ?? 0) > 0)
                        <span style="margin-left:auto;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:#f0c85a;color:#5c0000;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;line-height:1;">{{ ($pendingApprovalCount ?? 0) > 99 ? '99+' : $pendingApprovalCount }}</span>
                    @endif
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
                <a href="{{ route('superadmin.content') ?? '#' }}" class="nav-link" onclick="try{sessionStorage.setItem('cms-content-entry-loading','1');}catch(e){}">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.downloadables') ?? '#' }}" class="nav-link">
                    <i class="fas fa-download"></i>
                    <span>Downloadables</span>
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
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Audit Trails</span>
                </a>
            </li>

        </ul>
    </nav>

    <x-app.topbar :logout-route="route('superadmin.logout')" default-role="ADMIN" />

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

            <div class="filter-bar">
    <div class="filter-field">
    <div class="searchable-select" id="searchWrap">
        <div class="voice-input-row">
            <input
                type="text"
                id="srch"
                placeholder="Search name, email, or ID..."
                autocomplete="off"
                oninput="applyFilters()"
            >
        </div>
    </div>
</div>

    <div class="filter-field">
    <div class="searchable-select" id="roleFilterWrap">
        <div class="voice-input-row">
            <input
                type="text"
                id="roleFilterSearch"
                placeholder="All Roles"
                autocomplete="off"
            >
        </div>
        <input type="hidden" id="roleFil">
        <div class="searchable-dropdown" id="roleFilterDropdown"></div>
    </div>
</div>

    <div class="filter-field filter-select">
        <i class="fas fa-circle-check"></i>
        <select id="stFil" onchange="applyFilters()">
            <option value="">All Status</option>
            <option>Active</option>
            <option>Inactive</option>
            <option>Suspended</option>
        </select>
    </div>
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
    <div class="frow frow-single" id="facultyPickerRow" style="display:grid;">
      <div class="fg">
        <label>Select User <span class="req">*</span></label>
        <div class="searchable-select" id="facultySearchWrap">
    <div class="voice-input-row">
        <input
            type="text"
            id="facultySearch"
            placeholder="Search faculty by name or email"
            autocomplete="off"
        >
    </div>
    <input type="hidden" id="facultySelect">
    <div class="searchable-dropdown" id="facultyDropdown"></div>
</div>
        <small class="role-help-text">Type a name or email, then choose a faculty member from the list.</small>
      </div>
    </div>
    <div class="frow">
        <div class="fg">
            <label>First Name <span class="req">*</span></label>
            <input id="f-fn" placeholder="Auto-filled from faculty record" readOnly>
        </div>
        <div class="fg">
            <label>Last Name <span class="req">*</span></label>
            <input id="f-ln" placeholder="Auto-filled from faculty record" readOnly>
        </div>
    </div>

    <div class="frow">
    <div class="fg">
        <label>Email <span class="req">*</span></label>
        <input type="email" id="f-em" placeholder="Auto-filled from faculty record" readOnly>
    </div>

    <div class="fg">
      <label>CMS Roles <span class="req">*</span></label>
      <div class="searchable-select" id="roleSearchWrap">
    <div class="voice-input-row">
        <input
            type="text"
            id="roleSearch"
            placeholder="Search and select CMS role"
            autocomplete="off"
        >
    </div>
    <input type="hidden" id="rolePicker">
    <div class="searchable-dropdown" id="roleDropdown"></div>
</div>
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
  SUPERADMIN: 'r-superadmin',
  ADMIN: 'r-admin',
  REGISTRAR: 'r-registrar',
  HAP: 'r-hap',
  STUDENT_SERVICES: 'r-studentservices',
  RESEARCH: 'r-research',
  RESEARCH_EXTENSION: 'r-research',
  FACULTY: 'r-faculty',
  'pupt:faculty': 'r-faculty',
  'pupt:student': 'r-student',
  FACULTY_PRO: 'r-faculty_pro',
  DENTAL: 'r-dental',
  CLINIC: 'r-clinic',
  GUIDANCE: 'r-guidance',
  ACCREDITATION: 'r-accreditation',
  ADMISSIONS: 'r-admissions',
  LIBRARY: 'r-library',
  OJT: 'r-ojt',
  CWTS: 'r-cwts',
  DIRECTOR_OFFICE: 'r-director_office',
  ADMINISTRATION: 'r-administration',
};
const SC = { Active:'sb-active', Inactive:'sb-inactive', Suspended:'sb-suspended' };
const SI = { Active:'fa-circle-check', Inactive:'fa-circle-minus', Suspended:'fa-ban' };
const AV = ['av-0'];
const CURRENT_ROLE = "{{ strtoupper(trim((string) session('user_role'))) }}";

const TAB_GROUPS = {
  'Superadmin': ['SUPERADMIN'],
  'Admin': ['ADMIN'],
  'Registrar': ['REGISTRAR'],
  'HAP': ['HAP'],
  'Student Services': ['STUDENT_SERVICES'],
  'Research and Extension': ['RESEARCH_EXTENSION', 'RESEARCH'],
  'Faculty': ['pupt:faculty', 'FACULTY'], // support both legacy and base faculty codes
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

function isSuperadmin(roleCode){
  return normalizeRole(roleCode) === 'SUPERADMIN';
}

function canEditUser(targetRole){
  return true;
}

function canSuspendUser(targetRole){
  return true;
}

function escapeHtml(v) {
  return String(v ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

function initialsFromName(firstName, lastName, fallback = 'U') {
  const words = `${String(firstName || '').trim()} ${String(lastName || '').trim()}`.trim().split(/\s+/).filter(Boolean);
  if (words.length > 0) {
    return words.map((w) => w[0]).join('').slice(0, 2).toUpperCase();
  }

  const fb = String(fallback || '').trim();
  return (fb ? fb.charAt(0) : 'U').toUpperCase();
}

function renderUserAvatar(u, size = 'sm') {
  const sizeClass = size === 'lg' ? 'avatar-lg' : '';
  const colorClass = escapeHtml(String(u.av || 'av-0'));
  const avatarInitials = escapeHtml(String(u.avatarInitials || initialsFromName(u.fn, u.ln, u.em)));
  const avatarUrl = escapeHtml(String(u.avatarUrl || ''));
  const safeAlt = escapeHtml(`${String(u.fn || '').trim()} ${String(u.ln || '').trim()}`.trim() || 'User');

  if (!avatarUrl) {
    return `<div class="avatar ${colorClass} ${sizeClass}">${avatarInitials}</div>`;
  }

  return `<div class="avatar ${colorClass} ${sizeClass} avatar-photo">
    <img src="${avatarUrl}" alt="${safeAlt}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
    <span class="avatar-fallback">${avatarInitials}</span>
  </div>`;
}

/**
 * Fix: normalize/shape user object so UI always uses:
 * {id, fn, ln, em, rl, st, ll, nt, av}
 */
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
    ll: (raw.ll ?? raw.last_login_at ?? raw.lastLoginAt ?? 'Never') || 'Never',
    nt: raw.nt ?? raw.notes ?? '',
    av: raw.av ?? AV[0],
    avatarUrl: (raw.avatar_url ?? raw.avatarUrl ?? '').toString(),
    avatarInitials: (raw.avatar_initials ?? raw.avatarInitials ?? initialsFromName(fn, ln, raw.email ?? raw.em ?? 'U')).toString(),
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
  if (c === 'RESEARCH' || c === 'RESEARCH_EXTENSION') return 'Research & Extension';
  return ROLE_NAME_BY_CODE[c] || c;
}

function availableRoleOptions() {
  return (ROLES || [])
    .filter(r => {
      const code = String(r.code);
      if (code === 'SUPERADMIN' && CURRENT_ROLE !== 'SUPERADMIN') {
        return false;
      }
      return !code.includes(':');
    })
    .map(r => {
      const code = String(r.code);
      const normalizedCode = code === 'FACULTY' ? 'pupt:faculty' : code;
      const name = (code === 'RESEARCH' || code === 'RESEARCH_EXTENSION')
        ? 'Research & Extension'
        : String(r.name);

      return {
        code: normalizedCode,
        name: name
      };
    })
    .filter((role, index, arr) => arr.findIndex(x => x.code === role.code) === index);
}

function fillRoleOptions() {
  renderRoleDropdown('');
}

function closeRoleDropdown() {
  const dropdown = document.getElementById('roleDropdown');
  if (dropdown) dropdown.classList.remove('active');
}

function renderRoleDropdown(query = '') {
  const dropdown = document.getElementById('roleDropdown');
  if (!dropdown) return;

  const q = normalizeText(query);

  const filteredRoles = availableRoleOptions().filter(role => {
    const haystack = `${role.name} ${role.code}`.toLowerCase();
    return !q || haystack.includes(q);
  });

  if (!filteredRoles.length) {
    dropdown.innerHTML = `<div class="searchable-empty">No matching roles found.</div>`;
    dropdown.classList.add('active');
    return;
  }

  dropdown.innerHTML = filteredRoles.map(role => {
    const alreadySelected = selectedRoles.includes(normalizeRole(role.code));

    return `
      <div class="searchable-option ${alreadySelected ? 'disabled-option' : ''}" data-code="${escapeHtml(role.code)}" data-disabled="${alreadySelected ? '1' : '0'}">
        <div class="opt-name">${escapeHtml(role.name)}</div>
        <div class="opt-meta">${alreadySelected ? 'Already selected' : escapeHtml(role.code)}</div>
      </div>
    `;
  }).join('');

  dropdown.classList.add('active');

  dropdown.querySelectorAll('.searchable-option').forEach(opt => {
    opt.addEventListener('click', function () {
      if (this.dataset.disabled === '1') {
        showToast('This role is already selected.', 'warning');
        return;
      }

      const code = this.dataset.code;
      addRoleChip(code);

      const roleSearch = document.getElementById('roleSearch');
      const picker = document.getElementById('rolePicker');

      if (roleSearch) roleSearch.value = '';
      if (picker) picker.value = '';

      renderRoleDropdown('');
    });
  });
}

function closeRoleFilterDropdown() {
  const dropdown = document.getElementById('roleFilterDropdown');
  if (dropdown) dropdown.classList.remove('active');
}

function setRoleFilter(code = '') {
  const hidden = document.getElementById('roleFil');
  const input = document.getElementById('roleFilterSearch');

  if (hidden) hidden.value = code;
  if (input) input.value = code ? roleLabel(code) : '';

  applyFilters();
}

function renderRoleFilterDropdown(query = '') {
  const dropdown = document.getElementById('roleFilterDropdown');
  if (!dropdown) return;

  const q = normalizeText(query);
  const selectedCode = document.getElementById('roleFil')?.value || '';

  const filteredRoles = availableRoleOptions().filter(role => {
    const haystack = `${role.name} ${role.code}`.toLowerCase();
    return !q || haystack.includes(q);
  });

  let html = `
    <div class="searchable-option ${selectedCode === '' ? 'selected-option' : ''}" data-code="">
      <div class="opt-name">All Roles</div>
      <div class="opt-meta">Show all roles</div>
    </div>
  `;

  if (!filteredRoles.length) {
    html += `<div class="searchable-empty">No matching roles found.</div>`;
  } else {
    html += filteredRoles.map(role => `
      <div class="searchable-option ${selectedCode === role.code ? 'selected-option' : ''}" data-code="${escapeHtml(role.code)}">
        <div class="opt-name">${escapeHtml(role.name)}</div>
        <div class="opt-meta">${escapeHtml(role.code)}</div>
      </div>
    `).join('');
  }

  dropdown.innerHTML = html;
  dropdown.classList.add('active');

  dropdown.querySelectorAll('.searchable-option').forEach(opt => {
    opt.addEventListener('click', function () {
      const code = this.dataset.code || '';
      setRoleFilter(code);
      closeRoleFilterDropdown();
    });
  });
}

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

users = (Array.isArray(users) ? users : []).map(shapeUser);

let editId=null, viewId=null, pg=1;
let selectedRoles = [];
const PP=10;
fillRoleOptions();
function filtered(){
  const q = (document.getElementById('srch').value || '').toLowerCase();
  const role = document.getElementById('roleFil').value;
  const st = document.getElementById('stFil').value;

  return users.filter(u => {
    const userRoles = Array.isArray(u.roles) && u.roles.length ? u.roles : [u.rl];

    const mq = !q || `${u.fn} ${u.ln} ${u.em} ${u.id}`.toLowerCase().includes(q);
    const mr = !role || userRoles.includes(role);
    const ms = !st || u.st === st;

    return mq && mr && ms;
  });
}

function render(){
  const f=filtered(), tot=f.length, s=(pg-1)*PP, sl=f.slice(s,s+PP);
  const tb=document.getElementById('tbody');

  if(!sl.length){
    tb.innerHTML=`<tr><td colspan="8"><div class="empty"><i class="fas fa-users-slash"></i><p>No users found.</p></div></td></tr>`;
  } else {
    tb.innerHTML=sl.map((u,i)=>{
      const rc=RC[u.rl]||'r-student', sc=SC[u.st]||'sb-inactive', si=SI[u.st]||'fa-circle';
      const susp = (u.st === 'Active');
      const safeName = escapeHtml(`${u.fn} ${u.ln}`.trim());
      const safeEmail = escapeHtml(u.em);
      const safeRoleLabel = escapeHtml(roleLabel(u.rl));
      const safeStatus = escapeHtml(u.st);
      const safeLastLogin = escapeHtml(u.ll);

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
            ${renderUserAvatar(u)}
            <div>
              <div class="uname">${safeName}</div>
              <div class="uemail">${safeEmail}</div>
            </div>
          </div>
        </td>
        <td><span class="role-badge ${rc}">${safeRoleLabel}</span></td>
        <td><span class="sbadge ${sc}"><i class="fas ${si}" style="font-size:9px"></i> ${safeStatus}</span></td>
        <td style="color:#888;font-size:12px">${safeLastLogin}</td>
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
  const sc = {Active:0,Inactive:0,Suspended:0};

  users.forEach(u => {
    sc[u.st] = (sc[u.st] || 0) + 1;
  });

  document.getElementById('cnt-total').textContent = users.length;
  document.getElementById('cnt-active').textContent = sc.Active;
  document.getElementById('cnt-inactive').textContent = sc.Inactive;
  document.getElementById('cnt-suspended').textContent = sc.Suspended;
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
  document.getElementById('facultySelect').value = '';
  document.getElementById('facultySearch').value = '';
  ['f-fn','f-ln','f-em'].forEach(x => document.getElementById(x).value='');
  document.getElementById('f-st').value='Active';
  selectedRoles = [];
  renderRoleChips();

  const picker = document.getElementById('rolePicker');
  if (picker) picker.value = '';

  const roleSearch = document.getElementById('roleSearch');
  if (roleSearch) roleSearch.value = '';

  closeFacultyDropdown();
  closeRoleDropdown();
}

function toggleFacultyPicker(show) {
  const pickerRow = document.getElementById('facultyPickerRow');
  if (pickerRow) {
    pickerRow.hidden = !show;
    pickerRow.style.display = show ? 'grid' : 'none';
  }

  if (!show) {
    closeFacultyDropdown();
  }
}

function openAdd(){
  editId = null;
  clrForm();
  toggleFacultyPicker(true);
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

  const roleSearch = document.getElementById('roleSearch');
  if (roleSearch) roleSearch.value = '';

  toggleFacultyPicker(false);
  document.getElementById('mTitle').innerHTML = '<i class="fas fa-pen"></i> Edit CMS Access';
  document.getElementById('saveLbl').textContent = 'Update Access';

  openM('userModal');
}

const STORE_URL = "{{ route('superadmin.accounts.store') }}";
const UPDATE_URL_TPL = "{{ route('superadmin.accounts.update', ['id' => '__ID__']) }}";
const STATUS_URL_TPL = "{{ route('superadmin.accounts.status', ['id' => '__ID__']) }}";

const urlWithId = (tpl, id) => tpl.replace('__ID__', String(id));
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
function showToast(message, type = 'success', title = '') {
  if (typeof window.showToast === 'function' && window.showToast !== showToast) {
    window.showToast(message, type, title);
    return;
  }

  if (typeof window.cmsToast === 'function') {
    window.cmsToast(message, type, title);
    return;
  }

  if (typeof window.__cmsNativeAlert === 'function') {
    window.__cmsNativeAlert(message);
    return;
  }

  console.warn(message);
}

async function saveUser(){
  const fn=document.getElementById('f-fn').value.trim();
  const ln=document.getElementById('f-ln').value.trim();
  const em=document.getElementById('f-em').value.trim();
  const roles = [...selectedRoles];
  const st=document.getElementById('f-st').value;

  if(!fn||!ln||!em||!roles.length||!st){
    showToast('Please fill in all required fields.', 'warning', 'Incomplete Form');
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
      showToast(`(${res.status}) ${data.message || 'Check Network tab + server logs.'}`, 'error');
      return;
    }

    if(!data.ok){
      showToast(data.message || 'Failed.', 'error');
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
      showToast('CMS access updated successfully.', 'success');
    } else {
      const newUser = shapeUser(data.user);
      users.unshift(newUser);
      closeM('userModal');
      render();
      showToast('CMS access assigned successfully.', 'success');
    }

  } catch (err){
    console.error(err);
    showToast('Network/JS error. Check console.', 'error');
  }
}

function viewUser(id){
  viewId=id;
  const u=users.find(x=>x.id===id); if(!u) return;
  const rc=RC[u.rl]||'r-student', sc=SC[u.st]||'sb-inactive';
  const safeName = escapeHtml(`${u.fn} ${u.ln}`.trim());
  const safeEmail = escapeHtml(u.em);
  const safeStatus = escapeHtml(u.st);
  const safeLastLogin = escapeHtml(u.ll);
  const safeRoles = escapeHtml((u.roles || [u.rl]).map(roleLabel).join(', '));

  document.getElementById('viewBody').innerHTML=`
    <div class="vhead">
      ${renderUserAvatar(u, 'lg')}
      <div>
        <div class="vname">${safeName}</div>
        <div class="vsub">
          <span class="role-badge ${rc}">${escapeHtml(roleLabel(u.rl))}</span>
          <span class="sbadge ${sc}">${safeStatus}</span>
        </div>
      </div>
    </div>
    <div class="mbody">
      <div class="vgrid">
        <div class="vf"><div class="vfl">Full Name</div><div class="vfv">${safeName}</div></div>
        <div class="vf"><div class="vfl">Email</div><div class="vfv">${safeEmail}</div></div>
        <div class="vf"><div class="vfl">CMS Roles</div><div class="vfv">${safeRoles}</div></div>
        <div class="vf"><div class="vfl">CMS Access Status</div><div class="vfv">${safeStatus}</div></div>
        <div class="vf"><div class="vfl">Last Login</div><div class="vfv">${safeLastLogin}</div></div>
        ${u.nt?`<div class="vf" style="grid-column:1/-1"><div class="vfl">Notes</div><div class="vfv">${escapeHtml(u.nt)}</div></div>`:''}
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
      showToast(`(${res.status}) ${data.message || 'Check Network tab + server logs.'}`, 'error');
      return;
    }

    if(!data.ok){
      showToast(data.message || 'Failed.', 'error');
      return;
    }

    // update UI
    u.st = data.status || nextStatus;
    closeM('confirmModal');
    render();
    showToast(
      action === 'suspend'
        ? 'CMS access suspended successfully.'
        : 'CMS access activated successfully.',
      'success'
    );

  } catch (e) {
    console.error(e);
    showToast('Network/JS error. Check console.', 'error');
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

const facultyDirectory = @json(json_decode($facultyDirectoryJson ?? '[]', true));

function normalizeText(v) {
  return String(v || '').toLowerCase().trim();
}

function existingEmailsSet() {
  return new Set(
    users
      .map(u => normalizeText(u.em))
      .filter(Boolean)
  );
}

function clearFacultySelection() {
  document.getElementById('facultySelect').value = '';
  document.getElementById('facultySearch').value = '';
  document.getElementById('f-fn').value = '';
  document.getElementById('f-ln').value = '';
  document.getElementById('f-em').value = '';
}

function selectFaculty(faculty) {
  document.getElementById('facultySelect').value = String(faculty.id || '');
  document.getElementById('facultySearch').value = faculty.label || [faculty.first_name, faculty.last_name].filter(Boolean).join(' ');
  document.getElementById('f-fn').value = faculty.first_name || '';
  document.getElementById('f-ln').value = faculty.last_name || '';
  document.getElementById('f-em').value = faculty.email || '';
  closeFacultyDropdown();
}

function closeFacultyDropdown() {
  const dropdown = document.getElementById('facultyDropdown');
  if (dropdown) dropdown.classList.remove('active');
}

function renderFacultyDropdown(query = '') {
  const dropdown = document.getElementById('facultyDropdown');
  if (!dropdown) return;

  const q = normalizeText(query);
  const existingEmails = existingEmailsSet();

  const filteredFaculty = facultyDirectory.filter(f => {
    const haystack = [
      f.label,
      f.first_name,
      f.middle_name,
      f.last_name,
      f.email
    ].map(normalizeText).join(' ');

    return !q || haystack.includes(q);
  });

  if (!filteredFaculty.length) {
    dropdown.innerHTML = `<div class="searchable-empty">No matching faculty found.</div>`;
    dropdown.classList.add('active');
    return;
  }

  dropdown.innerHTML = filteredFaculty.map(f => {
    const alreadyExists = existingEmails.has(normalizeText(f.email));
    const safeId = String(f.id || '').replace(/"/g, '&quot;');
    const safeName = escapeHtml(f.label || [f.first_name, f.last_name].filter(Boolean).join(' '));
    const safeEmail = escapeHtml(f.email || '');
    const meta = alreadyExists ? 'Already has CMS access' : safeEmail;

    return `
      <div class="searchable-option ${alreadyExists ? 'disabled-option' : ''}" data-id="${safeId}" data-disabled="${alreadyExists ? '1' : '0'}">
        <div class="opt-name">${safeName}</div>
        <div class="opt-meta">${escapeHtml(meta)}</div>
      </div>
    `;
  }).join('');

  dropdown.classList.add('active');

  dropdown.querySelectorAll('.searchable-option').forEach(opt => {
    opt.addEventListener('click', function () {
      if (this.dataset.disabled === '1') {
        showToast('This faculty member already has CMS access.', 'warning');
        return;
      }

      const selected = facultyDirectory.find(x => String(x.id) === String(this.dataset.id));
      if (selected) selectFaculty(selected);
    });
  });
}

document.getElementById('facultySearch').addEventListener('focus', function () {
  renderFacultyDropdown(this.value);
});

document.getElementById('facultySearch').addEventListener('input', function () {
  document.getElementById('facultySelect').value = '';
  document.getElementById('f-fn').value = '';
  document.getElementById('f-ln').value = '';
  document.getElementById('f-em').value = '';
  renderFacultyDropdown(this.value);
});

document.addEventListener('click', function (e) {
  const wrap = document.getElementById('facultySearchWrap');
  if (wrap && !wrap.contains(e.target)) {
    closeFacultyDropdown();
  }
});

document.getElementById('roleSearch').addEventListener('focus', function () {
  renderRoleDropdown(this.value);
});

document.getElementById('roleSearch').addEventListener('input', function () {
  document.getElementById('rolePicker').value = '';
  renderRoleDropdown(this.value);
});

document.addEventListener('click', function (e) {
  const wrap = document.getElementById('roleSearchWrap');
  if (wrap && !wrap.contains(e.target)) {
    closeRoleDropdown();
  }
});

document.getElementById('roleFilterSearch').addEventListener('focus', function () {
  renderRoleFilterDropdown(this.value);
});

document.getElementById('roleFilterSearch').addEventListener('input', function () {
  renderRoleFilterDropdown(this.value);
});

document.addEventListener('click', function (e) {
  const wrap = document.getElementById('roleFilterWrap');
  if (wrap && !wrap.contains(e.target)) {
    closeRoleFilterDropdown();
  }
});

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

let activeRecognition = null;
let activeEditableField = null;
let activeFloatingButton = null;

function appendSpeechText(currentValue, spokenText) {
    const current = String(currentValue || '').trim();
    const spoken = String(spokenText || '').trim();
    if (!current) return spoken;
    if (!spoken) return current;
    return `${current} ${spoken}`.trim();
}

function stopFloatingVoiceRecognition() {
    if (activeRecognition) {
        activeRecognition.stop();
        activeRecognition = null;
    }

    if (activeFloatingButton) {
        activeFloatingButton.classList.remove('listening');
        activeFloatingButton = null;
    }
}

function setActiveEditableField(el) {
    activeEditableField = el;
}

function getRichTextHiddenFieldFromEditor(target) {
    const form = target.closest('form');
    if (!form) return null;
    return form.querySelector('[name="content"]');
}

function insertSpeechIntoActiveField(text) {
    if (!activeEditableField) {
        alert('Click a field first, then use speech-to-text.');
        return;
    }

    const target = activeEditableField;

    if (target.matches('input[type="text"], textarea')) {
        target.value = appendSpeechText(target.value, text);
        target.dispatchEvent(new Event('input', { bubbles: true }));

        if (target.id === 'globalSearch') {
            runSearch();
        }

        target.focus();
        return;
    }

    const richTextField = getRichTextHiddenFieldFromEditor(target);
    if (richTextField) {
        const nextValue = appendSpeechText(richTextField.value, text);
        setRichTextEditorValue(richTextField, nextValue);
        richTextField.dispatchEvent(new Event('input', { bubbles: true }));
        return;
    }

    alert('Selected field is not supported for speech input.');
}

function startFloatingVoiceRecognition(buttonEl) {
    if (!SpeechRecognition) {
        alert('Voice input is not supported in this browser. Please use Edge or Chrome.');
        return;
    }

    if (activeRecognition && activeFloatingButton === buttonEl) {
        stopFloatingVoiceRecognition();
        return;
    }

    if (activeRecognition) {
        stopFloatingVoiceRecognition();
    }

    const recognition = new SpeechRecognition();
    activeRecognition = recognition;
    activeFloatingButton = buttonEl;

    recognition.lang = 'en-US';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = function () {
        buttonEl.classList.add('listening');
        console.log('floating recognition started');
    };

    recognition.onerror = function (event) {
        console.error('Speech recognition error:', event.error);
        stopFloatingVoiceRecognition();
    };

    recognition.onend = function () {
        if (activeRecognition === recognition) {
            stopFloatingVoiceRecognition();
        }
    };

    recognition.onresult = function (event) {
        const transcript = (event.results?.[0]?.[0]?.transcript || '')
            .trim()
            .replace(/[^a-zA-Z0-9\s@_-]/g, '')
            .replace(/\s+/g, ' ');

        console.log('floating transcript:', transcript);

        if (!transcript) return;
        insertSpeechIntoActiveField(transcript);
    };

    try {
        recognition.start();
    } catch (err) {
        console.error('recognition.start failed:', err);
        stopFloatingVoiceRecognition();
    }
}

document.addEventListener('focusin', function (e) {
    const target = e.target;

    if (target.matches('input[type="text"], textarea')) {
        setActiveEditableField(target);
        return;
    }

    if (
        target.closest('.tox, .ck-editor, [contenteditable="true"], .rich-text-editor') ||
        target.isContentEditable
    ) {
        setActiveEditableField(target);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const floatingVoiceBtn = document.getElementById('floatingVoiceBtn');
    if (floatingVoiceBtn) {
        floatingVoiceBtn.addEventListener('click', function () {
            console.log('floating mic clicked');
            console.log('activeEditableField:', activeEditableField);
            startFloatingVoiceRecognition(floatingVoiceBtn);
        });
    }
});
</script>
<button type="button" id="floatingVoiceBtn" class="floating-voice-btn" title="Speech to text">
    <i class="fas fa-microphone"></i>
</button>
</body>
</html>
