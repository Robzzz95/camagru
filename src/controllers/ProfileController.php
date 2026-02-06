<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/user.php';
require __DIR__ . '/../views/layout/header.php';
require __DIR__ . '/../views/profile.php';
require __DIR__ . '/../views/layout/footer.php';

class ProfileController
{
	public function show(): void
	{
		requireLogin();

		$user = currentUser();
		if (!$user) {
			session_destroy();
			header('Location: /login');
			exit;
		}

		require __DIR__ . '/../views/profile.php';
	}
}
