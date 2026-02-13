<?php require __DIR__ . '/layout/header.php'; ?>

<div class="auth-box">
	<h2>Signup</h2>

	<form method="POST" action="/signup">
		<input name="username" placeholder="Username" required><br>
		<input type="email" name="email" placeholder="Email" required><br>
		<input type="password" name="password" placeholder="Password" required><br>
		<button>Create Account</button>
	</form>

	<?php if (!empty($_GET['error'])): ?>
		<p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
	<?php endif; ?>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
