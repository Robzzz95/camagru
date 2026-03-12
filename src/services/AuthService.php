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
}