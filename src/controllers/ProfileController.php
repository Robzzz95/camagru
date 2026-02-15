<?php

require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../core/Database.php';

class ProfileController
{
	public function index(): void
	{
		Auth::requireLogin();

		$db = Database::get();
		$stmt = $db->prepare("SELECT id, username, email FROM users WHERE id = ?");
		$stmt->execute([$_SESSION['user_id']]);
		$user = $stmt->fetch();

		if (!$user) {
			session_destroy();
			header('Location: /');
			exit;
		}

		require __DIR__ . '/../views/profile.php';
	}
}
