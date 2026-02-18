<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';

class Database
{
	private static ?PDO $pdo = null;

	public static function get(): PDO
	{
		if (self::$pdo !== null) {
			return self::$pdo;
		}

		$host = $_ENV['DB_HOST'] ?? 'mysql';
		$db   = $_ENV['DB_NAME'] ?? 'camagru';
		$user = $_ENV['DB_USER'] ?? 'camagru';
		$pass = $_ENV['DB_PASS'] ?? 'camagru_password';

		self::$pdo = new PDO(
			"mysql:host=$host;dbname=$db;charset=utf8mb4",
			$user,
			$pass,
			[
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			]
		);

		return self::$pdo;
	}
}
