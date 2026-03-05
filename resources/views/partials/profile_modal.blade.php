@php
    $profileUserId = (int) session('user_id', 0);
    $profileFirst = (string) session('user_first_name', '');
    $profileMiddle = (string) session('user_middle_name', '');
    $profileLast = (string) session('user_last_name', '');
    $profilePicture = (string) session('user_profile_picture', '');

    if ($profileUserId > 0) {
        $profileIdColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $profileUser = \Illuminate\Support\Facades\DB::table('users')
            ->where($profileIdColumn, $profileUserId)
            ->first();

        if ($profileUser) {
            $profileFirst = (string) (data_get($profileUser, 'first_name') ?: $profileFirst);
            $profileMiddle = (string) (data_get($profileUser, 'middle_name') ?: $profileMiddle);
            $profileLast = (string) (data_get($profileUser, 'last_name') ?: $profileLast);
            $profilePicture = (string) (data_get($profileUser, 'profile_picture') ?: $profilePicture);
        }
    }

    if (!$profileFirst && !$profileLast) {
        $nameFromSession = trim((string) session('user_name', ''));
        if ($nameFromSession !== '') {
            $parts = preg_split('/\s+/', $nameFromSession);
            $profileFirst = (string) ($parts[0] ?? '');
            $profileLast = (string) (count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '');
        }
    }

    $profileInitial = strtoupper(substr($profileFirst ?: ($profileLast ?: 'U'), 0, 1));
@endphp

<style>
  .profile-edit-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.52);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2500;
    padding: 16px;
  }

  .profile-edit-modal.active {
    display: flex;
  }

  .profile-edit-dialog {
    width: 100%;
    max-width: 580px;
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
  }

  .profile-edit-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #800000;
    color: #fff;
    padding: 14px 18px;
  }

  .profile-edit-title {
    font-size: 18px;
    font-weight: 600;
  }

  .profile-edit-close {
    border: none;
    background: transparent;
    color: #fff;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
  }

  .profile-edit-body {
    padding: 22px 20px 18px;
    max-height: 70vh;
    overflow-y: auto;
  }

  .profile-photo-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 18px;
  }

  .profile-photo-input {
    display: none;
  }

  .profile-photo-click {
    cursor: pointer;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    user-select: none;
  }

  .profile-photo-ring {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    padding: 5px;
    background: linear-gradient(145deg, #d4af37, #800000);
    box-shadow: 0 10px 24px rgba(128, 0, 0, 0.25);
    position: relative;
  }

  .profile-photo-preview,
  .profile-photo-fallback {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
  }

  .profile-photo-fallback {
    background: linear-gradient(135deg, #d4af37 0%, #c5a028 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 56px;
    font-weight: 700;
  }

  .profile-photo-badge {
    position: absolute;
    right: 8px;
    bottom: 8px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #800000;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    font-size: 14px;
  }

  .profile-photo-label {
    font-size: 13px;
    font-weight: 600;
    color: #5f2323;
  }

  .profile-photo-note {
    color: #666;
    font-size: 12px;
  }

  .profile-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .profile-form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .profile-form-group.full {
    grid-column: 1 / -1;
  }

  .profile-form-group label {
    font-size: 13px;
    color: #6a3b3b;
    font-weight: 600;
  }

  .profile-form-group input {
    border: 1px solid #d8cccc;
    border-radius: 10px;
    padding: 11px 12px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.15s ease;
  }

  .profile-form-group input:focus {
    border-color: #b06b6b;
  }

  .profile-form-group input[readonly] {
    background: #f7f7f7;
    color: #555;
  }

  .profile-edit-foot {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 18px;
    border-top: 1px solid #f0eaea;
  }

  .profile-btn {
    border: none;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
  }

  .profile-btn.cancel {
    background: #f2f2f2;
    color: #444;
  }

  .profile-btn.save {
    background: #800000;
    color: #fff;
  }

  .profile-edit-alerts {
    border-top: 1px solid #f0eaea;
    padding: 12px 18px 14px;
    display: grid;
    gap: 8px;
    background: #fff;
  }

  .profile-form-alert {
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 13px;
  }

  .profile-form-alert.success {
    background: #edf8ed;
    color: #1e6a1e;
    border: 1px solid #b9deb9;
  }

  .profile-form-alert.error {
    background: #fff1f1;
    color: #8f1f1f;
    border: 1px solid #f0b4b4;
  }

  @media (max-width: 640px) {
    .profile-form-grid {
      grid-template-columns: 1fr;
    }

    .profile-photo-ring {
      width: 132px;
      height: 132px;
    }
  }
</style>

<div id="profileEditModal" class="profile-edit-modal" role="dialog" aria-modal="true" aria-labelledby="profileEditTitle">
  <div class="profile-edit-dialog">
    <div class="profile-edit-head">
      <h3 id="profileEditTitle" class="profile-edit-title">Edit Profile</h3>
      <button type="button" class="profile-edit-close" onclick="closeProfileModal()">&times;</button>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
      @csrf
      <div class="profile-edit-body">
        <div class="profile-photo-center">
          <input id="profile_picture" class="profile-photo-input" name="profile_picture" type="file" accept=".jpg,.jpeg,.png,.webp" onchange="previewProfilePicture(this)">

          <label for="profile_picture" class="profile-photo-click">
            <div class="profile-photo-ring">
              @if($profilePicture)
                <img id="profilePicturePreview" class="profile-photo-preview" src="{{ asset($profilePicture) }}" alt="Profile Picture">
                <div id="profilePictureFallback" class="profile-photo-fallback" style="display:none;">{{ $profileInitial }}</div>
              @else
                <img id="profilePicturePreview" class="profile-photo-preview" src="" alt="Profile Picture" style="display:none;">
                <div id="profilePictureFallback" class="profile-photo-fallback">{{ $profileInitial }}</div>
              @endif
              <span class="profile-photo-badge"><i class="fas fa-camera"></i></span>
            </div>
            <span class="profile-photo-label">Click profile picture to change</span>
            <span class="profile-photo-note">JPG, PNG, WEBP (max 2MB)</span>
          </label>
        </div>

        <div class="profile-form-grid">
          <div class="profile-form-group">
            <label>Last Name</label>
            <input type="text" value="{{ $profileLast }}" readonly>
          </div>

          <div class="profile-form-group">
            <label>First Name</label>
            <input type="text" value="{{ $profileFirst }}" readonly>
          </div>

          <div class="profile-form-group full">
            <label>Middle Name</label>
            <input type="text" value="{{ $profileMiddle }}" readonly>
          </div>

          <div class="profile-form-group full">
            <label for="current_password">Current Password</label>
            <input id="current_password" name="current_password" type="password" placeholder="Enter your current login password" required>
          </div>

          <div class="profile-form-group">
            <label for="new_password">Change Password</label>
            <input id="new_password" name="new_password" type="password" placeholder="Enter new password" required>
          </div>

          <div class="profile-form-group">
            <label for="confirm_password">Confirm Password</label>
            <input id="confirm_password" name="confirm_password" type="password" placeholder="Re-enter new password" required>
          </div>
        </div>
      </div>

      <div class="profile-edit-foot">
        <button type="button" class="profile-btn cancel" onclick="closeProfileModal()">Cancel</button>
        <button type="submit" class="profile-btn save">Save Changes</button>
      </div>

      @if(session('profile_success') || $errors->profile->any())
        <div class="profile-edit-alerts">
          @if(session('profile_success'))
            <div class="profile-form-alert success">{{ session('profile_success') }}</div>
          @endif

          @if($errors->profile->any())
            <div class="profile-form-alert error">
              @foreach($errors->profile->all() as $error)
                <div>{{ $error }}</div>
              @endforeach
            </div>
          @endif
        </div>
      @endif
    </form>
  </div>
</div>

<script>
  function openProfileModal(trigger) {
    if (trigger) {
      const menu = trigger.closest('details');
      if (menu) menu.removeAttribute('open');
    }
    const modal = document.getElementById('profileEditModal');
    if (modal) modal.classList.add('active');
  }

  function closeProfileModal() {
    const modal = document.getElementById('profileEditModal');
    if (modal) modal.classList.remove('active');
  }

  function previewProfilePicture(input) {
    if (!input || !input.files || !input.files[0]) return;
    const preview = document.getElementById('profilePicturePreview');
    const fallback = document.getElementById('profilePictureFallback');
    const url = URL.createObjectURL(input.files[0]);

    if (preview) {
      preview.src = url;
      preview.style.display = 'block';
    }
    if (fallback) fallback.style.display = 'none';
  }

  document.addEventListener('click', function (e) {
    const modal = document.getElementById('profileEditModal');
    if (modal && e.target === modal) {
      closeProfileModal();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeProfileModal();
    }
  });

  @if($errors->profile->any() || session('profile_success'))
    document.addEventListener('DOMContentLoaded', function () {
      openProfileModal();
    });
  @endif
</script>
