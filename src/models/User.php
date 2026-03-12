<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Model.php';

class User extends Model
{
	public function getById(int $userId): ?array
	{
		$sql = "SELECT * FROM users WHERE id = ?";
		$row = $this->executeRequest($sql, [$userId])->fetch();
		return ($row ?: null);
	}

	public function getByEmail(string $email): ?array
	{
		$sql = "SELECT * FROM users WHERE email = ?";
		$row = $this->executeRequest($sql, [$email])->fetch();
		return ($row ?: null);
	}

	public function getByUsername(string $username): ?array
	{
		$sql = "SELECT * FROM users WHERE username = ?";
		$row = $this->executeRequest($sql, [$username])->fetch();
		return ($row ?: null);
	}

	public function create(string $username, string $email, string $passwordHash, string $confirmationToken): int
	{
		$sql = "INSERT INTO users (username, email, password_hash, confirmation_token) VALUES (?, ?, ?, ?)";
		$this->executeRequest($sql, [$username, $email, $passwordHash, $confirmationToken]);
		return ((int)$this->getLastInsertId());
	}

	public function confirmEmail(string $token): bool
	{
		$sql = "UPDATE users SET email_confirmed = 1, confirmation_token = NULL WHERE confirmation_token = ?";
		return ($this->executeRequest($sql, [$token]) !== false);
	}

	public function setResetToken(int $userId, string $token, string $expires): bool
	{
		$sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?";
		return ($this->executeRequest($sql, [$token, $expires, $userId]) !== false);
	}

	public function resetPassword(string $token, string $newPasswordHash): bool
	{
		$sql = "UPDATE users SET password_hash = ?, reset_token = NULL,
			reset_expires = NULL WHERE reset_token = ? AND reset_expires > NOW()";
		return ($this->executeRequest($sql, [$newPasswordHash, $token]) !== false);
	}

	public function updateUsername(int $userId, string $username): bool
	{
		$sql = "UPDATE users SET username = ? WHERE id = ?";
		return ($this->executeRequest($sql, [$username, $userId]) !== false);
	}

	public function updateEmail(int $userId, string $email): bool
	{
		$sql = "UPDATE users SET email = ?, email_confirmed = 0 WHERE id = ?";
		return ($this->executeRequest($sql, [$email, $userId]) !== false);
	}

	public function updatePassword(int $userId, string $newPasswordHash): bool
	{
		$sql = "UPDATE users SET password_hash = ? WHERE id = ?";
		return ($this->executeRequest($sql, [$newPasswordHash, $userId]) !== false);
	}

	public function delete(int $userId): bool
	{
		$sql = "DELETE FROM users WHERE id = ?";
		return ($this->executeRequest($sql, [$userId]) !== false);
	}
}
