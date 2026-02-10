<?php

require_once __DIR__ . '/../core/Database.php';

class AuthService {

	// ----------------
	// LOGIN
	// ----------------
	public function login(string $email, string $password): bool {
		$db = Database::get();
		$stmt = $db->prepare("SELECT id, password_hash FROM users WHERE email = ?");
		$stmt->execute([$email]);
		$user = $stmt->fetch();

		if (!$user)
			return (false);
		if (!password_verify($password, $user['password_hash']))
			return (false);
		if (!$user['email_confirmed'])
			return ['success' => false, 'message' => 'Email not confirmed'];

		$_SESSION['user_id'] = $user['id'];
		return (true);
	}

	// ----------------
	// LOGOUT
	// ----------------
	public function logout(): void {
		session_destroy();
	}

	// ----------------
	// SIGNUP
	// ----------------
	public function signup(array $data): array {
		$db = Database::get();
		$username = trim($data['username'] ?? '');
		$email = strtolower(trim($data['email'] ?? ''));
		$password = $data['password'] ?? '';

		if (!$username || !$email || !$password)
			return ['success' => false, 'message' => 'All fields required'];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			return ['success' => false, 'message' => 'Invalid email'];
		if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username))
			return ['success' => false, 'message' => 'Invalid username'];
		if (strlen($password) < 8)
			return ['success' => false, 'message' => 'Password too short'];

		// existing user check
		$stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
		$stmt->execute([$username, $email]);
		if ($stmt->fetch())
			return ['success' => false, 'message' => 'User already exists'];

		$hash = password_hash($password, PASSWORD_DEFAULT);
		$token = bin2hex(random_bytes(16));
		$stmt = $db->prepare("
			INSERT INTO users (username,email,password_hash,email_confirmed,confirmation_token)
			VALUES (?,?,?,0,?)
		");
		$stmt->execute([$username,$email,$hash,$token]);

		// mailhog will catch this
		$link = $_ENV['APP_URL'] . "/confirm?token=$token";
		mail($email, "Confirm account", "Confirm here:\n$link",	"From: camagru@local");
		return ['success' => true];
	}

	//-------------
	//confirm email
	//-------------
	public function confirmEmail(string $token): bool
	{
		$pdo = Database::get();
		$stmt = $pdo->prepare("UPDATE users SER email_confirmed = 1, confirmation_token = NULL WHERE confirmation_token = ?");
		$stmt->execute([$token]);
		return $stmt->rowCount() === 1;
	}
}



// class AuthController {

// 	private PDO $pdo;

// 	public function __construct() {
// 		$this->pdo = Database::getConnection();
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