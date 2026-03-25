<div class="auth-wrapper">
	<div class="auth-card">
		<h2>Forgot Password</h2>
		<p style="text-align:center; color:var(--ig-muted); font-size:13px; margin-bottom:8px;">
			Enter your email and we'll send you a reset link.
		</p>
		<form method="POST" action="/forgot-password">
			<input type="email" name="email" placeholder="Email" required>
			<button>Send Reset Link</button>
		</form>
		<p><a href="/login">Back to login</a></p>
	</div>
</div>