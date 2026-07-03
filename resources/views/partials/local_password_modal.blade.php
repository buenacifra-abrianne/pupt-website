<div id="local-password-modal" class="profile-edit-modal">
    <div class="profile-edit-dialog" style="max-width: 500px;">
        <div class="profile-edit-head">
            <h2 class="profile-edit-title"><i class="fas fa-lock" style="margin-right: 8px;"></i> Set Local Password</h2>
            <button type="button" class="profile-edit-close" onclick="closeLocalPasswordModal()" aria-label="Close modal">&times;</button>
        </div>
        
        <div class="profile-edit-body">
            <div style="background-color: #fff3cd; color: #856404; border-left: 4px solid #856404; padding: 12px; margin-bottom: 20px; font-size: 14px; border-radius: 4px;">
                <strong>Notice:</strong> This password serves as an emergency fallback credential if the primary central login system is ever offline.
            </div>

            <form action="{{ route('profile.local-password.update') }}" method="POST" id="localPasswordForm">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label for="new_password" style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">New Password</label>
                    <input type="password" name="new_password" id="new_password" required 
                           style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;"
                           placeholder="Enter new local password">
                    <small style="color: #666; display: block; margin-top: 6px; font-size: 12px;">
                        Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.
                    </small>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label for="new_password_confirmation" style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" required 
                           style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;"
                           placeholder="Confirm new local password">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn btn-secondary" onclick="closeLocalPasswordModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openLocalPasswordModal() {
        document.getElementById('local-password-modal').classList.add('active');
    }

    function closeLocalPasswordModal() {
        document.getElementById('local-password-modal').classList.remove('active');
    }

    // Close when clicking outside
    document.getElementById('local-password-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLocalPasswordModal();
        }
    });

    // Handle form submission via AJAX (optional) or let standard POST handle with redirect
</script>
