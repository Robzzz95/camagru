<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../controllers/AuthController.php';
$auth = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$result = $auth->changePassword(
		$_SESSION['user_id'],
		$_POST['current_password'] ?? '',
		$_POST['new_password'] ?? ''
	);

	header('Content-Type: application/json');
	echo json_encode($result);
	exit;
}

// Later: load settings view
require __DIR__ . '/../views/settings.php';