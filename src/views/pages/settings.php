<div class="settings-wrapper">
    <h2>Settings</h2>

    <!-- USERNAME -->
    <div class="settings-card">
        <h3>Username</h3>
        <form class="ajax-form" data-action="/settings/username">
            <input type="text" name="username"
                   value="<?= htmlspecialchars($user['username']) ?>"
                   placeholder="Username" required>
            <button type="submit">Save</button>
            <span class="settings-msg"></span>
        </form>
    </div>

    <!-- EMAIL -->
    <div class="settings-card">
        <h3>Email</h3>
        <form class="ajax-form" data-action="/settings/email">
            <input type="email" name="email"
                   value="<?= htmlspecialchars($user['email']) ?>"
                   placeholder="Email" required>
            <button type="submit">Save</button>
            <span class="settings-msg"></span>
        </form>
    </div>

    <!-- PASSWORD -->
    <div class="settings-card">
        <h3>Change Password</h3>
        <form class="ajax-form" data-action="/settings/password">
            <input type="password" name="current_password" placeholder="Current password" required>
            <input type="password" name="new_password"     placeholder="New password (min 8)" required>
            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            <button type="submit">Update Password</button>
            <span class="settings-msg"></span>
        </form>
    </div>

    <!-- DELETE ACCOUNT -->
    <div class="settings-card settings-card--danger">
        <h3>Delete Account</h3>
        <p>This will permanently delete your account and all your posts.</p>
        <form class="ajax-form" data-action="/settings/delete" data-confirm="Are you sure? This cannot be undone.">
            <input type="password" name="password" placeholder="Confirm your password" required>
            <button type="submit" class="btn-danger">Delete My Account</button>
            <span class="settings-msg"></span>
        </form>
    </div>
</div>

<script src="/js/settings.js"></script>