<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';

abstract class Model
{
	private	static ?PDO $db = null;

	private function getDb()
	{
		if (self::$db !== null)
			return self::$db;

		//maybe add an error handling if no variables found
		$host = $_ENV['DB_HOST'] ?? 'mysql';
		$db   = $_ENV['DB_NAME'] ?? 'camagru';
		$user = $_ENV['DB_USER'] ?? 'camagru';
		$pass = $_ENV['DB_PASS'] ?? 'camagru_password';

		self::$db = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass,
			[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			 PDO::ATTR_EMULATE_PREPARES => false]);

		return (self::$db);
	}

	protected function executeRequest($sql, ?array $params = null)
	{
		if ($params === null)
			$stmt = $this->getDb()->query($sql);
		else
			$stmt = $this->getDb()->prepare($sql);
		if ($params !== null)
		{
			foreach($params as $i => $value)
			{
				$type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmt->bindValue($i + 1, $value, $type);
			}	
			$stmt->execute();
		}
		return ($stmt);
	}

	protected function getLastInsertId(): string
	{
		return $this->getDb()->lastInsertId();
	}
}
