<div class="auth-wrapper">
	<div class="auth-card">
		<h2>Reset Password</h2>
		<form method="POST" action="/reset-password">
			<input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
			<input type="password" name="password" placeholder="New password (min 8 chars)" required>
			<button>Reset Password</button>
		</form>
		<p><a href="/login">Back to login</a></p>
	</div>
</div>