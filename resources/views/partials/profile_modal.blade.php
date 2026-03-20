@php
    $profileUserId = (int) session('user_id', 0);
    $profileFirst = (string) session('user_first_name', '');
    $profileMiddle = (string) session('user_middle_name', '');
    $profileLast = (string) session('user_last_name', '');
    $profileSuffix = (string) session('user_suffix', '');
    $profilePicture = (string) session('user_profile_picture', '');
    $profileName = trim((string) session('user_name', ''));
    $profileEmail = (string) session('user_email', '');
    $profileRole = (string) session('user_role', '');

    if ($profileUserId > 0) {
        $profileIdColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $profileUser = \Illuminate\Support\Facades\DB::table('users')
            ->where($profileIdColumn, $profileUserId)
            ->first();

        if ($profileUser) {
            $profileFirst = (string) (data_get($profileUser, 'first_name') ?: $profileFirst);
            $profileMiddle = (string) (data_get($profileUser, 'middle_name') ?: $profileMiddle);
            $profileLast = (string) (data_get($profileUser, 'last_name') ?: $profileLast);
            $profileSuffix = (string) (data_get($profileUser, 'suffix') ?: $profileSuffix);
            $profilePicture = (string) (data_get($profileUser, 'profile_picture') ?: $profilePicture);
            $profileName = trim((string) (data_get($profileUser, 'name') ?: $profileName));
            $profileEmail = (string) (data_get($profileUser, 'email') ?: $profileEmail);
        }
    }

    if ($profileName === '') {
        $profileName = trim(implode(' ', array_filter([$profileFirst, $profileMiddle, $profileLast, $profileSuffix], fn ($part) => trim((string) $part) !== '')));
    }

    $profileDetails = [
        'First Name' => $profileFirst,
        'Middle Name' => $profileMiddle,
        'Last Name' => $profileLast,
        'Suffix' => $profileSuffix,
        'Email' => $profileEmail,
    ];
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
    padding: 22px;
  }

  .profile-edit-modal.active {
    display: flex;
  }

  .profile-edit-dialog {
    width: 100%;
    max-width: 920px;
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 28px 60px rgba(0, 0, 0, 0.28);
  }

  .profile-edit-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #800000;
    color: #fff;
    padding: 16px 22px;
  }

  .profile-edit-title {
    font-size: 20px;
    font-weight: 700;
    letter-spacing: 0.01em;
  }

  .profile-edit-close {
    border: none;
    background: transparent;
    color: #fff;
    width: 40px;
    height: 40px;
    border-radius: 999px;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
    transition: background-color 0.18s ease, transform 0.18s ease;
  }

  .profile-edit-close:hover {
    background: rgba(255, 255, 255, 0.14);
    transform: scale(1.04);
  }

  .profile-edit-body {
    padding: 20px;
    max-height: 85vh;
    overflow-y: auto;
  }

  .profile-card {
    border: 1px solid #eadfdf;
    border-radius: 18px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 10px 30px rgba(87, 25, 25, 0.08);
  }

  .profile-card-body {
    padding: 18px;
    display: grid;
    grid-template-columns: 260px minmax(0, 1fr);
    align-items: start;
    gap: 20px;
  }

  .profile-photo-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;
    padding: 18px 14px;
    border-radius: 16px;
    background: linear-gradient(180deg, #fcf5f5 0%, #f9f2f2 100%);
    border: 1px solid #f0e2e2;
  }

  .profile-photo-ring {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    padding: 5px;
    background: linear-gradient(145deg, #d4af37, #c5a028);
    box-shadow: 0 10px 24px rgba(128, 0, 0, 0.18);
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
    background: #8e2f2f;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 48px;
    font-weight: 700;
    letter-spacing: 0.03em;
  }

  .profile-photo-label {
    font-size: 13px;
    font-weight: 600;
    color: #5f2323;
  }

  .profile-name {
    text-align: center;
    display: grid;
    gap: 4px;
    width: 100%;
  }

  .profile-name strong {
    color: #4f1717;
    font-size: 18px;
    line-height: 1.25;
  }

  .profile-name span {
    justify-self: center;
    min-height: 30px;
    padding: 6px 14px;
    border-radius: 999px;
    background: rgba(128, 0, 0, 0.08);
    color: #7a4949;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
  }

  .profile-details {
    display: grid;
    gap: 12px;
    align-content: start;
    min-width: 0;
    align-self: start;
  }

  .profile-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: start;
    gap: 16px 18px;
  }

  .profile-form-group.full {
    grid-column: 1 / -1;
  }

  .profile-form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
  }

  .profile-form-group label {
    font-size: 12px;
    color: #815555;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding-left: 2px;
  }

  .profile-static-value {
    min-height: 42px;
    border: 1px solid #e3d3d3;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 14px;
    color: #2f2f2f;
    background: linear-gradient(180deg, #ffffff 0%, #fbf8f8 100%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    display: flex;
    align-items: center;
    width: 100%;
    overflow-wrap: anywhere;
  }

  .profile-static-value.empty {
    color: #8e7777;
    font-style: italic;
  }

  .profile-edit-foot {
    display: flex;
    justify-content: flex-end;
    margin-top: 0;
    padding-top: 10px;
    border-top: 1px solid #f1e7e7;
  }

  .profile-btn {
    border: none;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.18s ease, transform 0.18s ease;
  }

  .profile-btn.cancel {
    background: #f4eeee;
    color: #6b2d2d;
    border: 1px solid #e4d2d2;
  }

  .profile-btn.cancel:hover {
    background: #efe4e4;
    transform: translateY(-1px);
  }

  @media (max-width: 860px) {
    .profile-edit-dialog {
      max-width: 680px;
    }

    .profile-card-body {
      grid-template-columns: 1fr;
      gap: 14px;
    }

    .profile-photo-center {
      padding: 16px 14px;
    }
  }

  @media (max-width: 640px) {
    .profile-form-grid {
      grid-template-columns: 1fr;
    }

    .profile-photo-ring {
      width: 132px;
      height: 132px;
    }

    .profile-edit-body {
      padding: 14px;
    }

    .profile-card-body {
      padding: 16px 14px;
    }
  }
</style>

<div id="profileEditModal" class="profile-edit-modal" role="dialog" aria-modal="true" aria-labelledby="profileEditTitle">
  <div class="profile-edit-dialog">
    <div class="profile-edit-head">
      <h3 id="profileEditTitle" class="profile-edit-title">Profile Information</h3>
      <button type="button" class="profile-edit-close" onclick="closeProfileModal()">&times;</button>
    </div>

    <div class="profile-edit-body">
      <div class="profile-card">
        <div class="profile-card-body">
          <div class="profile-photo-center">
            <div class="profile-photo-ring">
              <x-app.avatar
                :name="$profileName"
                :first-name="$profileFirst"
                :last-name="$profileLast"
                :src="$profilePicture"
                alt="Profile Picture"
                image-id="profilePicturePreview"
                fallback-id="profilePictureFallback"
                image-class="profile-photo-preview"
                fallback-class="profile-photo-fallback"
              />
            </div>

            <div class="profile-name">
              <strong>{{ $profileName !== '' ? $profileName : 'No profile name available' }}</strong>
              <span>{{ $profileRole !== '' ? $profileRole : 'No role assigned' }}</span>
            </div>
          </div>

          <div class="profile-details">
            <div class="profile-form-grid">
              @foreach($profileDetails as $label => $value)
                <div class="profile-form-group{{ in_array($label, ['Email'], true) ? ' full' : '' }}">
                  <label>{{ $label }}</label>
                  <div class="profile-static-value{{ trim((string) $value) === '' ? ' empty' : '' }}">
                    {{ trim((string) $value) !== '' ? $value : 'Not provided' }}
                  </div>
                </div>
              @endforeach
            </div>

            <div class="profile-edit-foot">
              <button type="button" class="profile-btn cancel" onclick="closeProfileModal()">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>
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
</script>
