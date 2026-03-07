@php
    $profileUserId = (int) session('user_id', 0);
    $profileFirst = (string) session('user_first_name', '');
    $profileMiddle = (string) session('user_middle_name', '');
    $profileLast = (string) session('user_last_name', '');
    $profileSuffix = (string) session('user_suffix', '');
    $profilePicture = (string) session('user_profile_picture', '');
    $profileName = trim((string) session('user_name', ''));

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
        }
    }

    if ($profileName === '') {
        $profileName = trim(implode(' ', array_filter([$profileFirst, $profileMiddle, $profileLast, $profileSuffix], fn ($part) => trim((string) $part) !== '')));
    }

    $profileInitials = '';

    // Keep initials consistent with audit trail: prioritize first_name + last_name.
    $profileInitialSource = trim(implode(' ', array_filter([
        $profileFirst,
        $profileLast,
    ], fn ($part) => trim((string) $part) !== '')));

    if ($profileInitialSource === '') {
        $profileInitialSource = $profileName;
    }

    $parts = preg_split('/\s+/', $profileInitialSource) ?: [];
    foreach ($parts as $part) {
        $trimmed = trim((string) $part);
        if ($trimmed === '') {
            continue;
        }
        $profileInitials .= strtoupper(substr($trimmed, 0, 1));
        if (strlen($profileInitials) >= 2) {
            break;
        }
    }

    if ($profileInitials === '') {
        $base = $profileFirst ?: ($profileLast ?: 'U');
        $profileInitials = strtoupper(substr($base, 0, 1));
    }
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
    max-width: 620px;
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
    display: grid;
    gap: 20px;
  }

  .profile-card {
    border: 1px solid #efe6e6;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
  }

  .profile-card-title {
    margin: 0;
    padding: 10px 14px;
    background: #faf6f6;
    border-bottom: 1px solid #efe6e6;
    font-size: 13px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #7a4949;
    font-weight: 700;
  }

  .profile-card-body {
    padding: 14px;
    display: grid;
    gap: 14px;
  }

  .profile-photo-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
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
    background: linear-gradient(145deg, #d4af37, #c5a028);
    box-shadow: 0 10px 24px rgba(128, 0, 0, 0.18);
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
    background: #8e2f2f;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 48px;
    font-weight: 700;
    letter-spacing: 0.03em;
  }

  .profile-photo-badge {
    position: absolute;
    right: 8px;
    bottom: 8px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #8e2f2f;
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

  .profile-photo-actions {
    margin-top: 6px;
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

  .profile-edit-foot {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
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
    display: grid;
    gap: 8px;
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

  .avatar-crop-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 2600;
  }

  .avatar-crop-overlay.active {
    display: flex;
  }

  .avatar-crop-dialog {
    width: 100%;
    max-width: 360px;
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.25);
  }

  .avatar-crop-head {
    padding: 12px 14px;
    background: #faf6f6;
    border-bottom: 1px solid #efe6e6;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #7a4949;
  }

  .avatar-crop-body {
    padding: 14px;
    display: grid;
    gap: 12px;
  }

  .avatar-crop-canvas-wrap {
    width: 100%;
    display: flex;
    justify-content: center;
  }

  .avatar-crop-canvas {
    width: 280px;
    height: 280px;
    border-radius: 12px;
    background: #f4f4f4;
    border: 1px solid #e6e0e0;
    touch-action: none;
    cursor: grab;
  }

  .avatar-crop-canvas.dragging {
    cursor: grabbing;
  }

  .avatar-crop-foot {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
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

    <div class="profile-edit-body">
      <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-card">
        @csrf
        <h4 class="profile-card-title">Profile Information</h4>
        <div class="profile-card-body">
          <div class="profile-photo-center">
            <input id="profile_picture" class="profile-photo-input" name="profile_picture" type="file" accept=".jpg,.jpeg,.png,.webp">
            <input id="avatar_image_data" name="avatar_image_data" type="hidden" value="">
            <input id="reset_avatar" name="reset_avatar" type="hidden" value="0">

            <label for="profile_picture" class="profile-photo-click">
              <div class="profile-photo-ring">
                @if($profilePicture)
                  <img id="profilePicturePreview" class="profile-photo-preview" src="{{ asset($profilePicture) }}" alt="Profile Picture">
                  <div id="profilePictureFallback" class="profile-photo-fallback" style="display:none;">{{ $profileInitials }}</div>
                @else
                  <img id="profilePicturePreview" class="profile-photo-preview" src="" alt="Profile Picture" style="display:none;">
                  <div id="profilePictureFallback" class="profile-photo-fallback">{{ $profileInitials }}</div>
                @endif
                <span class="profile-photo-badge"><i class="fas fa-camera"></i></span>
              </div>
              <span class="profile-photo-label">Click profile picture to change</span>
              <span class="profile-photo-note">JPG, PNG, WEBP (max 3MB)</span>
            </label>
            <div class="profile-photo-actions">
              <button id="resetAvatarButton" type="button" class="profile-btn cancel">Reset Avatar</button>
            </div>
          </div>

          <div class="profile-form-grid">
            <div class="profile-form-group">
              <label for="last_name">Last Name</label>
              <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $profileLast) }}" maxlength="100">
            </div>

            <div class="profile-form-group">
              <label for="first_name">First Name</label>
              <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $profileFirst) }}" maxlength="100">
            </div>

            <div class="profile-form-group">
              <label for="middle_name">Middle Name (Optional)</label>
              <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name', $profileMiddle) }}" maxlength="100">
            </div>

            <div class="profile-form-group">
              <label for="suffix">Suffix (Optional)</label>
              <input id="suffix" name="suffix" type="text" value="{{ old('suffix', $profileSuffix) }}" maxlength="30" placeholder="Jr., Sr., III">
            </div>
          </div>

          <div class="profile-edit-foot">
            <button type="button" class="profile-btn cancel" onclick="closeProfileModal()">Cancel</button>
            <button type="submit" class="profile-btn save">Save Profile</button>
          </div>

          @if(session('profile_info_success') || session('profile_info_notice') || $errors->profileInfo->any())
            <div class="profile-edit-alerts">
              @if(session('profile_info_success'))
                <div class="profile-form-alert success">{{ session('profile_info_success') }}</div>
              @endif

              @if(session('profile_info_notice'))
                <div class="profile-form-alert error">{{ session('profile_info_notice') }}</div>
              @endif

              @if($errors->profileInfo->any())
                <div class="profile-form-alert error">
                  @foreach($errors->profileInfo->all() as $error)
                    <div>{{ $error }}</div>
                  @endforeach
                </div>
              @endif
            </div>
          @endif
        </div>
      </form>

      <form method="POST" action="{{ route('profile.password.update') }}" class="profile-card">
        @csrf
        <h4 class="profile-card-title">Change Password</h4>
        <div class="profile-card-body">
          <div class="profile-form-grid">
            <div class="profile-form-group full">
              <label for="current_password">Current Password</label>
              <input id="current_password" name="current_password" type="password" placeholder="Enter your current login password" required>
            </div>

            <div class="profile-form-group">
              <label for="new_password">New Password</label>
              <input id="new_password" name="new_password" type="password" placeholder="Enter new password" required>
            </div>

            <div class="profile-form-group">
              <label for="confirm_password">Confirm Password</label>
              <input id="confirm_password" name="confirm_password" type="password" placeholder="Re-enter new password" required>
            </div>
          </div>

          <div class="profile-edit-foot">
            <button type="submit" class="profile-btn save">Change Password</button>
          </div>

          @if(session('profile_password_success') || $errors->profilePassword->any())
            <div class="profile-edit-alerts">
              @if(session('profile_password_success'))
                <div class="profile-form-alert success">{{ session('profile_password_success') }}</div>
              @endif

              @if($errors->profilePassword->any())
                <div class="profile-form-alert error">
                  @foreach($errors->profilePassword->all() as $error)
                    <div>{{ $error }}</div>
                  @endforeach
                </div>
              @endif
            </div>
          @endif
        </div>
      </form>
    </div>
  </div>
</div>

<div id="avatarCropModal" class="avatar-crop-overlay" role="dialog" aria-modal="true" aria-label="Crop profile picture">
  <div class="avatar-crop-dialog">
    <div class="avatar-crop-head">Crop Profile Picture</div>
    <div class="avatar-crop-body">
      <div class="avatar-crop-canvas-wrap">
        <canvas id="avatarCropCanvas" class="avatar-crop-canvas" width="280" height="280"></canvas>
      </div>
      <input id="avatarCropZoom" type="range" min="1" max="3" step="0.01" value="1">
      <div class="avatar-crop-foot">
        <button id="avatarCropCancel" type="button" class="profile-btn cancel">Cancel</button>
        <button id="avatarCropApply" type="button" class="profile-btn save">Apply Crop</button>
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

  (function initAvatarCropper() {
    const fileInput = document.getElementById('profile_picture');
    const hiddenInput = document.getElementById('avatar_image_data');
    const resetAvatarInput = document.getElementById('reset_avatar');
    const resetAvatarButton = document.getElementById('resetAvatarButton');
    const preview = document.getElementById('profilePicturePreview');
    const fallback = document.getElementById('profilePictureFallback');
    const cropModal = document.getElementById('avatarCropModal');
    const canvas = document.getElementById('avatarCropCanvas');
    const zoomInput = document.getElementById('avatarCropZoom');
    const applyBtn = document.getElementById('avatarCropApply');
    const cancelBtn = document.getElementById('avatarCropCancel');

    if (!fileInput || !hiddenInput || !resetAvatarInput || !resetAvatarButton || !preview || !fallback || !cropModal || !canvas || !zoomInput || !applyBtn || !cancelBtn) {
      return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
      return;
    }

    const cropState = {
      image: null,
      baseScale: 1,
      scale: 1,
      x: 0,
      y: 0,
      dragging: false,
      pointerId: null,
      startX: 0,
      startY: 0,
      startOffsetX: 0,
      startOffsetY: 0
    };

    function openCropper() {
      cropModal.classList.add('active');
    }

    function closeCropper() {
      cropModal.classList.remove('active');
      cropState.dragging = false;
      cropState.pointerId = null;
      canvas.classList.remove('dragging');
      if (fileInput) {
        fileInput.value = '';
      }
    }
    window.__closeAvatarCropper = closeCropper;

    function clampPosition() {
      if (!cropState.image) return;
      const renderedWidth = cropState.image.width * cropState.scale;
      const renderedHeight = cropState.image.height * cropState.scale;

      const minX = Math.min(0, canvas.width - renderedWidth);
      const minY = Math.min(0, canvas.height - renderedHeight);
      const maxX = 0;
      const maxY = 0;

      cropState.x = Math.max(minX, Math.min(maxX, cropState.x));
      cropState.y = Math.max(minY, Math.min(maxY, cropState.y));
    }

    function drawCropper() {
      if (!cropState.image) return;

      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(
        cropState.image,
        cropState.x,
        cropState.y,
        cropState.image.width * cropState.scale,
        cropState.image.height * cropState.scale
      );

      const radius = Math.min(canvas.width, canvas.height) / 2 - 8;
      const centerX = canvas.width / 2;
      const centerY = canvas.height / 2;

      ctx.save();
      ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.globalCompositeOperation = 'destination-out';
      ctx.beginPath();
      ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();

      ctx.save();
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 2;
      ctx.beginPath();
      ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
      ctx.stroke();
      ctx.restore();
    }

    function initCrop(image) {
      cropState.image = image;
      cropState.baseScale = Math.max(canvas.width / image.width, canvas.height / image.height);
      cropState.scale = cropState.baseScale;
      zoomInput.value = '1';
      cropState.x = (canvas.width - image.width * cropState.scale) / 2;
      cropState.y = (canvas.height - image.height * cropState.scale) / 2;
      clampPosition();
      drawCropper();
      openCropper();
    }

    fileInput.addEventListener('change', function () {
      const file = fileInput.files && fileInput.files[0];
      if (!file) return;
      resetAvatarInput.value = '0';

      if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
        alert('Please select a JPG, PNG, or WEBP image.');
        fileInput.value = '';
        return;
      }

      if (file.size > 3 * 1024 * 1024) {
        alert('Selected image is too large. Maximum is 3MB.');
        fileInput.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = function (event) {
        const img = new Image();
        img.onload = function () {
          initCrop(img);
        };
        img.src = event.target && event.target.result ? String(event.target.result) : '';
      };
      reader.readAsDataURL(file);
    });

    zoomInput.addEventListener('input', function () {
      if (!cropState.image) return;
      const zoomMultiplier = Number(zoomInput.value || '1');
      const previousScale = cropState.scale;
      const nextScale = cropState.baseScale * zoomMultiplier;

      const centerX = canvas.width / 2;
      const centerY = canvas.height / 2;
      const imageXAtCenter = (centerX - cropState.x) / previousScale;
      const imageYAtCenter = (centerY - cropState.y) / previousScale;

      cropState.scale = nextScale;
      cropState.x = centerX - imageXAtCenter * nextScale;
      cropState.y = centerY - imageYAtCenter * nextScale;
      clampPosition();
      drawCropper();
    });

    canvas.addEventListener('pointerdown', function (event) {
      if (!cropState.image) return;
      cropState.dragging = true;
      cropState.pointerId = event.pointerId;
      cropState.startX = event.clientX;
      cropState.startY = event.clientY;
      cropState.startOffsetX = cropState.x;
      cropState.startOffsetY = cropState.y;
      canvas.classList.add('dragging');
      canvas.setPointerCapture(event.pointerId);
    });

    canvas.addEventListener('pointermove', function (event) {
      if (!cropState.dragging || cropState.pointerId !== event.pointerId) return;
      const deltaX = event.clientX - cropState.startX;
      const deltaY = event.clientY - cropState.startY;
      cropState.x = cropState.startOffsetX + deltaX;
      cropState.y = cropState.startOffsetY + deltaY;
      clampPosition();
      drawCropper();
    });

    function stopDrag(event) {
      if (!cropState.dragging) return;
      if (event && cropState.pointerId !== null && cropState.pointerId !== event.pointerId) return;
      cropState.dragging = false;
      cropState.pointerId = null;
      canvas.classList.remove('dragging');
    }

    canvas.addEventListener('pointerup', stopDrag);
    canvas.addEventListener('pointercancel', stopDrag);
    canvas.addEventListener('lostpointercapture', stopDrag);

    cancelBtn.addEventListener('click', closeCropper);
    cropModal.addEventListener('click', function (event) {
      if (event.target === cropModal) {
        closeCropper();
      }
    });

    applyBtn.addEventListener('click', function () {
      if (!cropState.image) return;
      const output = document.createElement('canvas');
      output.width = 320;
      output.height = 320;
      const outputCtx = output.getContext('2d');
      if (!outputCtx) return;

      const ratio = output.width / canvas.width;

      outputCtx.save();
      outputCtx.beginPath();
      outputCtx.arc(output.width / 2, output.height / 2, output.width / 2 - 2, 0, Math.PI * 2);
      outputCtx.closePath();
      outputCtx.clip();
      outputCtx.drawImage(
        cropState.image,
        cropState.x * ratio,
        cropState.y * ratio,
        cropState.image.width * cropState.scale * ratio,
        cropState.image.height * cropState.scale * ratio
      );
      outputCtx.restore();

      const dataUrl = output.toDataURL('image/png', 0.95);
      hiddenInput.value = dataUrl;
      resetAvatarInput.value = '0';
      preview.src = dataUrl;
      preview.style.display = 'block';
      fallback.style.display = 'none';
      closeCropper();
    });

    resetAvatarButton.addEventListener('click', function () {
      hiddenInput.value = '';
      resetAvatarInput.value = '1';
      fileInput.value = '';
      preview.removeAttribute('src');
      preview.style.display = 'none';
      fallback.style.display = 'flex';
    });
  })();

  document.addEventListener('click', function (e) {
    const modal = document.getElementById('profileEditModal');
    if (modal && e.target === modal) {
      closeProfileModal();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      const cropModal = document.getElementById('avatarCropModal');
      if (cropModal && cropModal.classList.contains('active')) {
        if (typeof window.__closeAvatarCropper === 'function') {
          window.__closeAvatarCropper();
        } else {
          cropModal.classList.remove('active');
        }
        return;
      }
      closeProfileModal();
    }
  });

  @if($errors->profileInfo->any() || $errors->profilePassword->any())
    document.addEventListener('DOMContentLoaded', function () {
      openProfileModal();
    });
  @endif

  @if(session('profile_info_success') || session('profile_info_notice') || session('profile_password_success'))
    document.addEventListener('DOMContentLoaded', function () {
      @if(session('profile_info_success'))
        if (typeof window.showToast === 'function') {
          window.showToast(@json(session('profile_info_success')), 'success', 'Profile Updated');
        }
      @endif

      @if(session('profile_info_notice'))
        if (typeof window.showToast === 'function') {
          window.showToast(@json(session('profile_info_notice')), 'warning', 'No Profile Changes');
        }
      @endif

      @if(session('profile_password_success'))
        if (typeof window.showToast === 'function') {
          window.showToast(@json(session('profile_password_success')), 'success', 'Password Updated');
        }
      @endif
    });
  @endif
</script>
