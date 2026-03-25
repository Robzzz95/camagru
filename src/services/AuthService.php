<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Auth.php';

class AuthService
{
	private User $user;

	public function __construct()
	{
		$this->user = new User;
	}

	public function login(string $email, string $password): bool
	{
		$user = $this->user->getByEmail($email);
		if (!$user)
			return false;
		if (!password_verify($password, $user['password_hash']))
			return false;
		if (!$user['email_confirmed'])
			 return false;

		Auth::login($user['id']);
		return (true);
	}

	public function signup(array $data): array
	{
		$username = trim($data['username'] ?? '');
		$email	= strtolower(trim($data['email'] ?? ''));
		$password = $data['password'] ?? '';

		if (!$username || !$email || !$password)
			return ['success' => false, 'message' => 'All fields required'];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			return ['success' => false, 'message' => 'Invalid email'];
		if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username))
			return ['success' => false, 'message' => 'Invalid username'];
		if (strlen($password) < 8)
			return ['success' => false, 'message' => 'Password too short'];
		if ($this->user->getByUsername($username) || $this->user->getByEmail($email))
			return ['success' => false, 'message' => 'User already exists'];

		$hash  = password_hash($password, PASSWORD_DEFAULT);
		$token = bin2hex(random_bytes(16));
		$this->user->create($username, $email, $hash, $token);
		$link = ($_ENV['APP_URL'] ?? '') . "/confirm?token=$token";
		mail($email, "Confirm account", "Confirm here:\n$link", "From: camagru@local");
		return (['success' => true]);
	}

	public function confirmEmail(string $token): bool
	{
		return ($this->user->confirmEmail($token));
	}

	public function forgotPassword(string $email): void
	{
		// Always return void — never reveal if email exists
		$user = $this->user->getByEmail($email);
		if (!$user)
			return;

		$token = bin2hex(random_bytes(32));
		$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

		$this->user->setResetToken((int)$user['id'], $token, $expires);

		$link = ($_ENV['APP_URL'] ?? '') . "/reset-password?token=$token";
		$headers = "From: Camagru <no-reply@camagru.local>\r\nContent-Type: text/plain; charset=UTF-8";
		mail($user['email'], "Password reset", "Reset your password here:\n$link\n\nThis link expires in 1 hour.", $headers);
	}

	public function resetPassword(string $token, string $newPassword): array
	{
		if (strlen($newPassword) < 8)
			return ['success' => false, 'message' => 'Password must be at least 8 characters'];

		$hash = password_hash($newPassword, PASSWORD_DEFAULT);
		$ok = $this->user->resetPassword($token, $hash);

		if (!$ok)
			return ['success' => false, 'message' => 'Invalid or expired token'];

		return ['success' => true];
	}
}