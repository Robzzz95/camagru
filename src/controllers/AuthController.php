<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../middleware/Auth.php';

class AuthController {

	public function login() {
		Auth::guest();

		if (AuthService::login($_POST['email'], $_POST['password'])) {
			header('Location: /profile');
		} else {
			header('Location: /?error=login');
		}
	}

	public function signup() {
		Auth::guest();
		AuthService::signup($_POST);
		header('Location: /');
	}

	public function logout() {
		Auth::check();
		AuthService::logout();
		header('Location: /');
	}
}




















// declare(strict_types=1);

// require_once __DIR__ . '/../config/env.php';
// require_once __DIR__ . '/../helpers/auth.php';
// require_once __DIR__ . '/../config/Database.php';

// class AuthController {

// 	private PDO $pdo;

// 	public function __construct() {
// 		$this->pdo = Database::getConnection();
// 	}

// 	// ------------------------------
// 	// Registration
// 	// ------------------------------
// 	public function signup(array $data): array {
// 		$username = trim($data['username'] ?? '');
// 		$email	= strtolower(trim($data['email'] ?? ''));
// 		$password = $data['password'] ?? '';

// 		if (!$username || !$email || !$password) {
// 			return ['success' => false, 'message' => 'All fields are required.'];
// 		}
// 		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
// 			return ['success' => false, 'message' => 'Invalid email address.'];
// 		}
// 		if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
// 			return ['success' => false, 'message' => 'Invalid username format.'];
// 		}
// 		if (strlen($password) < 8) {
// 			return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
// 		}

// 		// Check if user/email exists
// 		$stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
// 		$stmt->execute([$username, $email]);
// 		if ($stmt->fetch()) {
// 			return ['success' => false, 'message' => 'Username or email already exists.'];
// 		}

// 		$passwordHash = password_hash($password, PASSWORD_DEFAULT);
// 		$token = bin2hex(random_bytes(16));
// 		$stmt = $this->pdo->prepare("
// 			INSERT INTO users (username, email, password_hash, email_confirmed, confirmation_token, created_at)
// 			VALUES (?, ?, ?, 0, ?, NOW())
// 		");
// 		try {
// 			$stmt->execute([$username, $email, $passwordHash, $token]);
// 		} catch (PDOException $e) {
// 			return ['success' => false, 'message' => 'Registration failed. Please try again.'];
// 		}
// 		$subject = "Confirm your account";
// 		$headers = <<<HEADERS
// From: Camagru <no-reply@camagru.local>
// Content-Type: text/plain; charset=UTF-8
// HEADERS;

// 		$confirmationLink = $_ENV['APP_URL'] . "/confirm.php?token=$token";
// 		//no indentation for mailhog formatting
// 		$body = <<<MAIL
// Hello,

// You created an account on Camagru.

// Please confirm your email by clicking the link below:

// $confirmationLink

// If you did not request this, you can safely ignore this email.

// – Camagru Team
// MAIL;

// 		try {
// 		    if (!mail($email, $subject, $body, $headers)) {
//     	    error_log("Signup email failed for user: {$email}");
// 	   		}
// 		} catch (Throwable $e) {
// 		    error_log("Signup email exception: " . $e->getMessage());
// 		}
// 		return ['success' => true, 'message' => 'Registration successful. If the email is valid, a confirmation link has been sent.'];
// 	}

	// // ------------------------------
	// // Login
	// // ------------------------------
	// public function login(array $data): array {
	// 	$username = trim($data['username'] ?? '');
	// 	$password = $data['password'] ?? '';

	// 	if (!$username || !$password) {
	// 		return ['success' => false, 'message' => 'Username and password are required.'];
	// 	}
	// 	$stmt = $this->pdo->prepare(
	// 		"SELECT id, username, password_hash, email_confirmed
	//  		FROM users WHERE username = ? OR email = ?");
	// 	$stmt->execute([$username, $username]);
	// 	$user = $stmt->fetch();
	// 	if (!$user || !password_verify($password, $user['password_hash'])) {
	// 		return ['success' => false, 'message' => 'Invalid credentials.'];
	// 	}
	// 	if (!$user['email_confirmed']) {
	// 		return ['success' => false, 'message' => 'Please confirm your email first.'];
	// 	}

	// 	// Login success, store session
	// 	session_regenerate_id(true);//need to see if i need to destroy previous session or not, mmight have race issues!
	// 	$_SESSION['logged_in'] = true;
	// 	$_SESSION['user_id'] = $user['id'];
	// 	$_SESSION['username'] = $user['username'];
	// 	return ['success' => true, 'message' => 'Login successful.'];
	// }

	// // ------------------------------
	// // Logout
	// // ------------------------------
	// public function logout(): array {
	// 	$_SESSION = [];
	// 	if (ini_get("session.use_cookies")) {
	// 		$params = session_get_cookie_params();
	// 		setcookie(session_name(), '', time() - 42000,
	// 			$params["path"], $params["domain"],
	// 			$params["secure"], $params["httponly"]
	// 		);
	// 	}
	// 	session_destroy();
	// 	return ['success' => true, 'message' => 'Logged out successfully.'];
	// }

	// // ------------------------------
	// // Email confirmation
	// // ------------------------------
	// public function confirmEmail(string $token): array {
	// 	$stmt = $this->pdo->prepare("SELECT id, email_confirmed FROM users WHERE confirmation_token = ?");
	// 	$stmt->execute([$token]);
	// 	$user = $stmt->fetch();

	// 	if (!$user) {
	// 		return ['success' => false, 'message' => 'Invalid confirmation token.'];
	// 	}
	// 	if ($user['email_confirmed']) {
	// 		return ['success' => false, 'message' => 'Email already confirmed.'];
	// 	}
	// 	$stmt = $this->pdo->prepare("
	// 		UPDATE users SET email_confirmed = 1, confirmation_token = NULL WHERE id = ?
	// 	");
	// 	$stmt->execute([$user['id']]);
	// 	// header('Location: /login');
	// 	// exit;
	// 	return ['success' => true, 'message' => 'Email confirmed. You can now log in.'];
	// }

	// // ------------------------------
	// // Password reset request
	// // ------------------------------
	// public function requestPasswordReset(string $email): array {
	// 	$email = strtolower(trim($email));
	// 	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	// 		return ['success' => true, 'message' => 'If the email exists, a reset link was sent.'];
	// 	}
	// 	$stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
	// 	$stmt->execute([$email]);
	// 	$user = $stmt->fetch();
	// 	if (!$user) {
	// 		return ['success' => true, 'message' => 'If the email exists, a reset link was sent.'];
	// 	}
	// 	$token = bin2hex(random_bytes(32));
	// 	$stmt = $this->pdo->prepare(
	// 		"UPDATE users
	// 		 SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
	// 		 WHERE id = ?"
	// 	);
	// 	$stmt->execute([$token, $user['id']]);
	// 	$resetLink = $_ENV['APP_URL'] . "/reset?token=$token";
	// 	$headers = implode("\r\n", [
	// 		'From: Camagru <no-reply@camagru.local>',
	// 		'Reply-To: no-reply@camagru.local',
	// 		'MIME-Version: 1.0',
	// 		'Content-Type: text/plain; charset=UTF-8',
	// 		'X-Mailer: PHP/' . phpversion(),
	// 	]);
	// 	$subject = "Password reset";
	// 	mail($email, $subject, "Reset here: $resetLink", $headers);
	// 	return ['success' => true, 'message' => 'If the email exists, a reset link was sent.'];
	// }

// 	// ------------------------------
// 	// Password reset
// 	// ------------------------------
// 	public function resetPassword(string $token, string $newPassword): array
// 	{
// 		if (strlen($newPassword) < 8) {
// 			return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
// 		}
// 		$stmt = $this->pdo->prepare(
// 			"SELECT id FROM users
// 			 WHERE reset_token = ? AND reset_expires > NOW()"
// 		);
// 		$stmt->execute([$token]);
// 		$user = $stmt->fetch();
// 		if (!$user) {
// 			return ['success' => false, 'message' => 'Invalid or expired reset token.'];
// 		}
// 		$hash = password_hash($newPassword, PASSWORD_DEFAULT);
// 		$stmt = $this->pdo->prepare(
// 			"UPDATE users
// 			 SET password_hash = ?, reset_token = NULL, reset_expires = NULL
// 			 WHERE id = ?"
// 		);
// 		$stmt->execute([$hash, $user['id']]);
// 		session_regenerate_id(true);
// 		return ['success' => true, 'message' => 'Password reset successful.'];
// 	}

// 	public function changePassword(int $userId, string $current, string $new): array
// 	{
// 		if (!$current || !$new) {
// 			return ['success' => false, 'message' => 'All fields are required.'];
// 		}
// 		if (strlen($new) < 8) {
// 			return ['success' => false, 'message' => 'New password too short.'];
// 		}
// 		$stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
// 		$stmt->execute([$userId]);
// 		$user = $stmt->fetch();
// 		if (!$user || !password_verify($current, $user['password_hash'])) {
// 			return ['success' => false, 'message' => 'Current password incorrect.'];
// 		}
// 		$newHash = password_hash($new, PASSWORD_DEFAULT);
// 		$stmt = $this->pdo->prepare(
// 			"UPDATE users SET password_hash = ? WHERE id = ?"
// 		);
// 		$stmt->execute([$newHash, $userId]);
// 		return ['success' => true, 'message' => 'Password updated successfully.'];
// 	}
// }