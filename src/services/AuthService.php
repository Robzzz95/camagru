<?php

require_once __DIR__ . '/../core/Database.php';

class AuthService {

	public static function login(string $email, string $password): bool {
		$db = Database::get();

		$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
		$stmt->execute([$email]);
		$user = $stmt->fetch();

		if (!$user) return false;

		if (!password_verify($password, $user['password'])) return false;

		$_SESSION['user_id'] = $user['id'];

		return true;
	}

	public static function logout(): void {
		session_destroy();
	}

	public static function signup(array $data): bool {
		$db = Database::get();

		$stmt = $db->prepare("
			INSERT INTO users (username,email,password)
			VALUES (?,?,?)
		");

		return $stmt->execute([
			$data['username'],
			$data['email'],
			password_hash($data['password'], PASSWORD_DEFAULT)
		]);
	}
}
