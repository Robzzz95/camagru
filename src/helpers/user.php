<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

function currentUser(): ?array
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	if (empty($_SESSION['user_id'])) {
		return null;
	}
	$pdo = Database::getConnection();
	$stmt = $pdo->prepare("
		SELECT id, username, email, created_at
		FROM users
		WHERE id = ?
		LIMIT 1
	");
	$stmt->execute([$_SESSION['user_id']]);
	$user = $stmt->fetch();
	return $user ?: null;
}
