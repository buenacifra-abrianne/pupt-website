<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PUP Taguig - Manage Accounts</title>

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
                <a href="{{ route('faculty.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('faculty.accounts') }}" class="nav-link active">
                    <i class="fas fa-users-gear"></i>
                    <span>Manage Accounts</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('faculty.announcements') }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('faculty.content') }}" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('faculty.notifications') }}" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>

            {{-- optional: add audit trail route if meron ka --}}
            {{-- <li class="nav-item">
                <a href="{{ route('faculty.audit') }}" class="nav-link">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Audit Trail</span>
                </a>
            </li> --}}

            <li class="nav-item">
                <form method="POST" action="{{ route('faculty.logout') }}">
                    @csrf
                    <button type="submit" class="nav-link" style="background:none;border:none;width:100%;text-align:left;cursor:pointer;">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>

            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search across all systems...">
            </div>
        </div>

        <div class="topbar-right">
            <button class="icon-btn" type="button">
                <i class="fas fa-bell"></i>
                <span class="badge">0</span>
            </button>

            <button class="icon-btn" type="button">
                <i class="fas fa-envelope"></i>
                <span class="badge">0</span>
            </button>

            <details class="user-menu">
                <summary class="user-profile">
                    <div class="user-avatar">
                        @php
                            $fn = (string) session('user_first_name');
                            $ln = (string) session('user_last_name');
                            $ini = ($fn ? strtoupper(substr($fn,0,1)) : 'A') . ($ln ? strtoupper(substr($ln,0,1)) : '');
                            echo $ini ?: 'AD';
                        @endphp
                    </div>

                    <div class="user-info">
                        <div class="user-name">
                            {{ session('user_first_name') ? e(session('user_first_name').' '.session('user_last_name')) : 'Admin User' }}
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
                    <form method="POST" action="{{ route('faculty.logout') }}">
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
            <h1 class="page-title">Manage Accounts</h1>
            <p class="page-subtitle">View, add, and manage all user accounts and their roles.</p>
        </div>

        <div class="stats-row">
            <div class="stat-card"><div class="stat-icon si-total"><i class="fas fa-users"></i></div><div><div class="stat-val" id="cnt-total">0</div><div class="stat-lbl">Total Users</div></div></div>
            <div class="stat-card"><div class="stat-icon si-active"><i class="fas fa-circle-check"></i></div><div><div class="stat-val" id="cnt-active">0</div><div class="stat-lbl">Active</div></div></div>
            <div class="stat-card"><div class="stat-icon si-inactive"><i class="fas fa-circle-minus"></i></div><div><div class="stat-val" id="cnt-inactive">0</div><div class="stat-lbl">Inactive</div></div></div>
            <div class="stat-card"><div class="stat-icon si-pending"><i class="fas fa-clock"></i></div><div><div class="stat-val" id="cnt-pending">0</div><div class="stat-lbl">Pending</div></div></div>
            <div class="stat-card"><div class="stat-icon si-suspended"><i class="fas fa-ban"></i></div><div><div class="stat-val" id="cnt-suspended">0</div><div class="stat-lbl">Suspended</div></div></div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-users-gear"></i> User Accounts</div>
                <button class="action-btn" type="button" onclick="openAdd()"><i class="fas fa-user-plus"></i> Add New User</button>
            </div>

            <div class="tab-nav">
                <button class="tab-btn active" data-role="all" onclick="switchRole('all')"><i class="fas fa-th-list"></i> All <span class="count-pill" id="pill-all">0</span></button>
                <button class="tab-btn" data-role="Administrator" onclick="switchRole('Administrator')"><i class="fas fa-shield-halved"></i> Administrator <span class="count-pill" id="pill-Administrator">0</span></button>
                <button class="tab-btn" data-role="Registrar" onclick="switchRole('Registrar')"><i class="fas fa-id-card"></i> Registrar <span class="count-pill" id="pill-Registrar">0</span></button>
                <button class="tab-btn" data-role="HAP" onclick="switchRole('HAP')"><i class="fas fa-building-user"></i> HAP <span class="count-pill" id="pill-HAP">0</span></button>
                <button class="tab-btn" data-role="Faculty" onclick="switchRole('Faculty')"><i class="fas fa-chalkboard-user"></i> Faculty <span class="count-pill" id="pill-Faculty">0</span></button>
                <button class="tab-btn" data-role="Student" onclick="switchRole('Student')"><i class="fas fa-user-graduate"></i> Student <span class="count-pill" id="pill-Student">0</span></button>
                <button class="tab-btn" data-role="IT" onclick="switchRole('IT')"><i class="fas fa-laptop-code"></i> IT <span class="count-pill" id="pill-IT">0</span></button>
            </div>

            <div class="filter-bar">
                <input type="text" id="srch" placeholder="Search by name, email or ID..." oninput="applyFilters()">
                <select id="stFil" onchange="applyFilters()">
                    <option value="">All Status</option>
                    <option>Active</option><option>Inactive</option><option>Pending</option><option>Suspended</option>
                </select>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>User</th><th>User ID</th><th>Role</th><th>Department</th><th>Status</th><th>Last Login</th><th>Actions</th>
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
                <h2 id="mTitle"><i class="fas fa-user-plus"></i> Add New User</h2>
                <button class="cbtn" type="button" onclick="closeM('userModal')"><i class="fas fa-times"></i></button>
            </div>

            <div class="mbody">
                <div class="frow">
                    <div class="fg"><label>First Name <span class="req">*</span></label><input id="f-fn" placeholder="First name"></div>
                    <div class="fg"><label>Last Name <span class="req">*</span></label><input id="f-ln" placeholder="Last name"></div>
                </div>

                <div class="frow">
                    <div class="fg"><label>Email <span class="req">*</span></label><input type="email" id="f-em" placeholder="user@pup.edu.ph"></div>
                    <div class="fg"><label>Phone</label><input id="f-ph" placeholder="+63 XXX XXX XXXX"></div>
                </div>

                <div class="frow">
                    <div class="fg"><label>User ID / Student No. <span class="req">*</span></label><input id="f-uid" placeholder="e.g. 2021-00001"></div>
                    <div class="fg"><label>Role <span class="req">*</span></label>
                        <select id="f-rl">
                            <option value="">Select Role</option>
                            <option>Administrator</option><option>Registrar</option><option>HAP</option><option>Faculty</option><option>Student</option><option>Library</option><option>IT</option>
                        </select>
                    </div>
                </div>

                <div class="frow">
                    <div class="fg"><label>Department <span class="req">*</span></label>
                        <select id="f-dp">
                            <option value="">Select Department</option>
                            <option>Administration</option><option>IT Department</option><option>College of Engineering</option><option>College of Science</option><option>College of Computer Science</option><option>Registrar's Office</option><option>Library</option>
                        </select>
                    </div>
                    <div class="fg"><label>Position / Year Level</label><input id="f-pos" placeholder="e.g. Professor, 3rd Year"></div>
                </div>

                <div class="frow">
                    <div class="fg"><label>Username <span class="req">*</span></label><input id="f-un" placeholder="Username"></div>
                    <div class="fg" id="pwWrap"><label>Temporary Password <span class="req">*</span></label><input type="password" id="f-pw" placeholder="Temporary password"></div>
                </div>

                <div class="frow">
                    <div class="fg"><label>Account Status <span class="req">*</span></label>
                        <select id="f-st">
                            <option value="">Select Status</option>
                            <option>Active</option><option>Inactive</option><option>Pending</option><option>Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="fg">
                    <div class="chkrow">
                        <input type="checkbox" id="f-fc" checked>
                        <label for="f-fc" style="margin:0;font-weight:500;font-size:13px;">Force password change on first login</label>
                    </div>
                </div>

                <div class="fg"><label>Notes</label><textarea id="f-nt" placeholder="Optional notes..." rows="2"></textarea></div>
            </div>

            <div class="mfoot">
                <button class="btn-outline" type="button" onclick="closeM('userModal')"><i class="fas fa-times"></i> Cancel</button>
                <button class="action-btn" type="button" onclick="saveUser()">
                    <i class="fas fa-user-check"></i> <span id="saveLbl">Create User</span>
                </button>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal" id="viewModal">
        <div class="mbox">
            <div class="mhead">
                <h2><i class="fas fa-eye"></i> User Details</h2>
                <button class="cbtn" type="button" onclick="closeM('viewModal')"><i class="fas fa-times"></i></button>
            </div>
            <div id="viewBody"></div>
            <div class="mfoot">
                <button class="btn-outline" type="button" onclick="closeM('viewModal')">Close</button>
                <button class="action-btn" type="button" onclick="editFromView()"><i class="fas fa-pen"></i> Edit User</button>
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
const RC = {Administrator:'r-admin',Registrar:'r-registrar',HAP:'r-hap',Faculty:'r-faculty',Student:'r-student',Library:'r-library',IT:'r-it'};
const SC = {Active:'sb-active',Inactive:'sb-inactive',Pending:'sb-pending',Suspended:'sb-suspended'};
const SI = {Active:'fa-circle-check',Inactive:'fa-circle-minus',Pending:'fa-clock',Suspended:'fa-ban'};
const AV = ['av-0','av-1','av-2','av-3','av-4','av-5'];

let users = [
    {id:1,fn:'Maria',ln:'Santos',em:'m.santos@pup.edu.ph',ph:'+63 912 345 6789',uid:'ADM-2021-001',rl:'Administrator',dp:'Administration',pos:'System Admin',un:'m.santos',st:'Active',ll:'Jan 10, 2025',nt:'',av:'av-0'},
    {id:2,fn:'Juan',ln:'Dela Cruz',em:'j.delacruz@pup.edu.ph',ph:'+63 917 123 4567',uid:'ADM-2021-002',rl:'Administrator',dp:'Administration',pos:'Deputy Admin',un:'j.delacruz',st:'Active',ll:'Jan 9, 2025',nt:'',av:'av-1'},
    {id:3,fn:'Ana',ln:'Reyes',em:'a.reyes@pup.edu.ph',ph:'+63 918 234 5678',uid:'REG-2020-001',rl:'Registrar',dp:"Registrar's Office",pos:'Senior Registrar',un:'a.reyes',st:'Active',ll:'Jan 8, 2025',nt:'',av:'av-2'},
];

let nid=4, curRole='all', editId=null, viewId=null, pg=1;
const PP=10;

function filtered(){
    const q=(document.getElementById('srch').value||'').toLowerCase();
    const st=document.getElementById('stFil').value;
    return users.filter(u=>{
        const mr=curRole==='all'||u.rl===curRole;
        const mq=!q||`${u.fn} ${u.ln} ${u.em} ${u.uid}`.toLowerCase().includes(q);
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
            const ini=u.fn[0]+u.ln[0];
            const rc=RC[u.rl]||'r-student', sc=SC[u.st]||'sb-inactive', si=SI[u.st]||'fa-circle';
            const susp=u.st==='Active'||u.st==='Pending';
            const tb2=susp
                ?`<button class="bico bi-suspend" title="Suspend" onclick="doConfirm(${u.id},'suspend')"><i class="fas fa-ban"></i></button>`
                :`<button class="bico bi-activate" title="Activate" onclick="doConfirm(${u.id},'activate')"><i class="fas fa-circle-check"></i></button>`;
            return `<tr>
                <td style="color:#bbb;font-size:12px">${s+i+1}</td>
                <td><div class="user-cell"><div class="avatar ${u.av}">${ini}</div><div><div class="uname">${u.fn} ${u.ln}</div><div class="uemail">${u.em}</div></div></div></td>
                <td style="color:#666;font-size:12px">${u.uid}</td>
                <td><span class="role-badge ${rc}">${u.rl}</span></td>
                <td style="color:#666;font-size:12px">${u.dp}</td>
                <td><span class="sbadge ${sc}"><i class="fas ${si}" style="font-size:9px"></i> ${u.st}</span></td>
                <td style="color:#888;font-size:12px">${u.ll}</td>
                <td><div class="actions">
                    <button class="bico bi-view" title="View" onclick="viewUser(${u.id})"><i class="fas fa-eye"></i></button>
                    <button class="bico bi-edit" title="Edit" onclick="openEdit(${u.id})"><i class="fas fa-pen"></i></button>
                    ${tb2}
                </div></td>
            </tr>`;
        }).join('');
    }

    document.getElementById('pgInfo').textContent=tot?`Showing ${s+1}–${Math.min(s+PP,tot)} of ${tot} user${tot!==1?'s':''}`:'No users found';
    document.getElementById('pgNum').textContent=pg;
    updateCounts();
}

function updateCounts(){
    const rc={},sc={Active:0,Inactive:0,Pending:0,Suspended:0};
    users.forEach(u=>{ rc[u.rl]=(rc[u.rl]||0)+1; sc[u.st]=(sc[u.st]||0)+1; });
    document.getElementById('cnt-total').textContent=users.length;
    document.getElementById('cnt-active').textContent=sc.Active;
    document.getElementById('cnt-inactive').textContent=sc.Inactive;
    document.getElementById('cnt-pending').textContent=sc.Pending;
    document.getElementById('cnt-suspended').textContent=sc.Suspended;
    document.getElementById('pill-all').textContent=users.length;
    ['Administrator','Registrar','HAP','Faculty','Student','IT'].forEach(r=>{
        const el=document.getElementById('pill-'+r); if(el) el.textContent=rc[r]||0;
    });
}

function applyFilters(){ pg=1; render(); }
function switchRole(r){ curRole=r; pg=1; document.querySelectorAll('[data-role]').forEach(b=>b.classList.toggle('active',b.dataset.role===r)); render(); }
function changePg(d){ const t=filtered().length, m=Math.ceil(t/PP); pg=Math.max(1,Math.min(pg+d,m)); render(); }

function clrForm(){
    ['f-fn','f-ln','f-em','f-ph','f-uid','f-un','f-pw','f-pos','f-nt'].forEach(x=>document.getElementById(x).value='');
    document.getElementById('f-rl').value=''; document.getElementById('f-dp').value='';
    document.getElementById('f-st').value='Active'; document.getElementById('f-fc').checked=true;
}
function openAdd(){ editId=null; clrForm(); document.getElementById('mTitle').innerHTML='<i class="fas fa-user-plus"></i> Add New User'; document.getElementById('saveLbl').textContent='Create User'; document.getElementById('pwWrap').style.display=''; openM('userModal'); }
function openEdit(id){ editId=id; const u=users.find(x=>x.id===id); if(!u)return;
    document.getElementById('f-fn').value=u.fn; document.getElementById('f-ln').value=u.ln;
    document.getElementById('f-em').value=u.em; document.getElementById('f-ph').value=u.ph;
    document.getElementById('f-uid').value=u.uid; document.getElementById('f-rl').value=u.rl;
    document.getElementById('f-dp').value=u.dp; document.getElementById('f-pos').value=u.pos;
    document.getElementById('f-un').value=u.un; document.getElementById('f-st').value=u.st;
    document.getElementById('f-nt').value=u.nt; document.getElementById('f-pw').value='';
    document.getElementById('pwWrap').style.display='none';
    document.getElementById('mTitle').innerHTML='<i class="fas fa-pen"></i> Edit User';
    document.getElementById('saveLbl').textContent='Save Changes'; openM('userModal');
}
function saveUser(){
    const fn=document.getElementById('f-fn').value.trim(), ln=document.getElementById('f-ln').value.trim(),
          em=document.getElementById('f-em').value.trim(), uid=document.getElementById('f-uid').value.trim(),
          rl=document.getElementById('f-rl').value, dp=document.getElementById('f-dp').value,
          un=document.getElementById('f-un').value.trim(), st=document.getElementById('f-st').value;
    if(!fn||!ln||!em||!uid||!rl||!dp||!un||!st){alert('Please fill in all required fields.');return;}
    if(editId){
        const u=users.find(x=>x.id===editId);
        Object.assign(u,{fn,ln,em,ph:document.getElementById('f-ph').value,uid,rl,dp,pos:document.getElementById('f-pos').value,un,st,nt:document.getElementById('f-nt').value});
    } else {
        users.push({id:nid++,fn,ln,em,ph:document.getElementById('f-ph').value,uid,rl,dp,pos:document.getElementById('f-pos').value,un,st,ll:'Never',nt:document.getElementById('f-nt').value,av:AV[(nid-2)%6]});
    }
    closeM('userModal'); render();
}

function viewUser(id){
    viewId=id;
    const u=users.find(x=>x.id===id); if(!u) return;
    const rc=RC[u.rl]||'r-student', sc=SC[u.st]||'sb-inactive';
    document.getElementById('viewBody').innerHTML=`
    <div class="vhead"><div class="vav ${u.av}">${u.fn[0]}${u.ln[0]}</div><div>
        <div class="vname">${u.fn} ${u.ln}</div>
        <div class="vsub"><span class="role-badge ${rc}">${u.rl}</span><span class="sbadge ${sc}">${u.st}</span></div>
    </div></div>
    <div class="mbody"><div class="vgrid">
        <div class="vf"><div class="vfl">Full Name</div><div class="vfv">${u.fn} ${u.ln}</div></div>
        <div class="vf"><div class="vfl">User ID</div><div class="vfv">${u.uid}</div></div>
        <div class="vf"><div class="vfl">Email</div><div class="vfv">${u.em}</div></div>
        <div class="vf"><div class="vfl">Phone</div><div class="vfv">${u.ph||'—'}</div></div>
        <div class="vf"><div class="vfl">Username</div><div class="vfv">${u.un}</div></div>
        <div class="vf"><div class="vfl">Role</div><div class="vfv">${u.rl}</div></div>
        <div class="vf"><div class="vfl">Department</div><div class="vfv">${u.dp}</div></div>
        <div class="vf"><div class="vfl">Position / Year</div><div class="vfv">${u.pos||'—'}</div></div>
        <div class="vf"><div class="vfl">Status</div><div class="vfv">${u.st}</div></div>
        <div class="vf"><div class="vfl">Last Login</div><div class="vfv">${u.ll}</div></div>
        ${u.nt?`<div class="vf" style="grid-column:1/-1"><div class="vfl">Notes</div><div class="vfv">${u.nt}</div></div>`:''}
    </div></div>`;
    openM('viewModal');
}
function editFromView(){ closeM('viewModal'); openEdit(viewId); }

function doConfirm(id, action){
    const u=users.find(x=>x.id===id); if(!u)return;
    const cfg={
        suspend:{title:'Suspend User',msg:`Suspend <strong>${u.fn} ${u.ln}</strong>?<br><small style="color:#888">They will lose access until reactivated.</small>`,icon:'fa-ban',bg:'rgba(220,53,69,0.1)',col:'#dc3545',bs:'background:linear-gradient(135deg,#dc3545,#b02a37)',lbl:'Suspend'},
        activate:{title:'Activate User',msg:`Activate <strong>${u.fn} ${u.ln}</strong>?<br><small style="color:#888">They will regain system access.</small>`,icon:'fa-circle-check',bg:'rgba(40,167,69,0.1)',col:'#28a745',bs:'background:linear-gradient(135deg,#28a745,#1e7e34)',lbl:'Activate'},
    };
    const c=cfg[action];
    document.getElementById('cfTitle').innerHTML=`<i class="fas ${c.icon}" style="color:${c.col}"></i> ${c.title}`;
    document.getElementById('cfIcon').style.cssText=`width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;background:${c.bg};color:${c.col}`;
    document.getElementById('cfIcon').innerHTML=`<i class="fas ${c.icon}"></i>`;
    document.getElementById('cfMsg').innerHTML=c.msg;

    const btn=document.getElementById('cfOk');
    btn.style.cssText=c.bs+';color:#fff;border:none;padding:10px 22px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;gap:8px;transition:0.3s;';
    btn.innerHTML=`<i class="fas ${c.icon}"></i> ${c.lbl}`;
    btn.onclick=()=>{ u.st=action==='suspend'?'Suspended':'Active'; closeM('confirmModal'); render(); };
    openM('confirmModal');
}

function openM(id){ const m=document.getElementById(id); if(m)m.classList.add('active'); }
function closeM(id){ const m=document.getElementById(id); if(m)m.classList.remove('active'); }
window.addEventListener('click',e=>{ document.querySelectorAll('.modal').forEach(m=>{ if(e.target===m)m.classList.remove('active'); }); });
function toggleSidebar(){ document.getElementById('sidebar').classList.toggle('collapsed'); }

render();
</script>

</body>
</html>


