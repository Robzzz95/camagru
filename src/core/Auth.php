<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/User.php';

class Auth
{
	public static function requireLogin(): void
	{
		if (!self::check()) {
			header('Location: /login');
			exit;
		}
	}

	public static function guest(): void
	{
		if (self::check()) {
			header('Location: /');
			exit;
		}
	}

	public static function login(int $userId): void
	{
		session_regenerate_id(true);
		$_SESSION['user_id'] = $userId;
	}

	public static function logout(): void
	{
		$_SESSION = [];
		session_destroy();
	}

	public static function check(): bool
	{
		return (isset($_SESSION['user_id']));
	}

	public static function id(): ?int
	{
		if (!self::check())
			return (null);
		return ((int)$_SESSION['user_id']);
	}

	public static function user(): ?array
	{
		if (!self::check())
			return (null);
		return ((new User)->getById(self::id()));
	}
}
