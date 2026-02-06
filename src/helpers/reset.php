<?php
declare(strict_types=1);

$pdo = Database::getConnection();

$token   = $_GET['token'] ?? $_POST['token'] ?? '';
$error   = '';
$success = '';

if (!$token) {
	http_response_code(400);
	exit('Invalid reset link.');
}

/*
|--------------------------------------------------------------------------
| POST — Reset password
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$password = $_POST['password'] ?? '';
	$confirm  = $_POST['confirm_password'] ?? '';

	if (!$password || !$confirm) {
		$error = "Both fields are required.";
	} elseif ($password !== $confirm) {
		$error = "Passwords do not match.";
	} elseif (strlen($password) < 8) {
		$error = "Password must be at least 8 characters.";
	} else {
		$stmt = $pdo->prepare(
			"SELECT id FROM users
			 WHERE reset_token = ?
			 AND reset_expires > NOW()"
		);
		$stmt->execute([$token]);
		$user = $stmt->fetch();

		if (!$user) {
			$error = "Invalid or expired token.";
		} else {
			$hash = password_hash($password, PASSWORD_DEFAULT);
			$stmt = $pdo->prepare(
				"UPDATE users
				 SET password_hash = ?, reset_token = NULL, reset_expires = NULL
				 WHERE id = ?"
			);
			$stmt->execute([$hash, $user['id']]);

			$success = "Password updated successfully. You can now log in.";
		}
	}
}

/*
|--------------------------------------------------------------------------
| GET — Validate token (just to show form)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$stmt = $pdo->prepare(
		"SELECT id FROM users
		 WHERE reset_token = ?
		 AND reset_expires > NOW()"
	);
	$stmt->execute([$token]);

	if (!$stmt->fetch()) {
		$error = "Invalid or expired token.";
	}
}

require __DIR__ . '/../views/layout/header.php';
?>

<div class="container">
	<h2>Reset Password</h2>

	<?php if ($error): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php if ($success): ?>
		<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
		<a href="/login">Go to login</a>
	<?php else: ?>
		<form method="post">
			<input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
			<label>New Password</label>
			<input type="password" name="password" required>
			<label>Confirm Password</label>
			<input type="password" name="confirm_password" required>
			<button type="submit">Reset Password</button>
		</form>
	<?php endif; ?>
</div>

<?php require __DIR__ . '/../views/layout/footer.php'; ?>