<?php require __DIR__ . '/layout/header.php'; ?>

<div class="auth-wrapper">
	<div class="auth-card">
		<h2>Login</h2>
		<form method="POST" action="/login">
			<input type="email" name="email" placeholder="Email" required>
			<input type="password" name="password" placeholder="Password" required>
			<button>Login</button>
			<?php if (!empty($_GET['error'])): ?>
				<p class="error">Invalid credentials</p>
			<?php endif; ?>
		</form>
	</div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
