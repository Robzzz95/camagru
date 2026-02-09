<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';

class Database {
	private static ?PDO $pdo = null;

	public static function get(): PDO {
		if (self::$pdo === null) {
			$host = $_ENV['DB_HOST'];
			$db	  = $_ENV['DB_NAME'];
			$user = $_ENV['DB_USER'];
			$pass = $_ENV['DB_PASS'];
		}

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