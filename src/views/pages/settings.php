<div class="settings-wrapper">
	<h2>Settings</h2>

	<!-- AVATAR -->
	<div class="settings-card settings-card--avatar">
		<h3>Profile Picture</h3>
		<div class="avatar-preview-wrap">
			<img
				id="avatarPreview"
				class="avatar avatar--lg"
				src="<?= $user['avatar'] ? '/uploads/avatars/' . htmlspecialchars($user['avatar']) : '/assets/avatars/default-avatar.svg' ?>"
				alt="Your avatar"
			>
			<label class="avatar-upload-btn" for="avatarInput">
				<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
					<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
					<polyline points="17 8 12 3 7 8"/>
					<line x1="12" y1="3" x2="12" y2="15"/>
				</svg>
				Change
			</label>
			<input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none">
		</div>
		<span class="settings-msg" id="avatarMsg"></span>
	</div>

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
			<input type="password" name="new_password"	 placeholder="New password (min 8)" required>
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