<?php
declare(strict_types=1);

function requireLogin(): void {

	error_log('requireLogin() invoked');

	if (!isset($_SESSION['user_id'])) {
		header('Location: /login');
		exit;
	}
}

function requireApiLogin(): void {
	if (!isset($_SESSION['user_id'])) {
		http_response_code(401);
		echo json_encode(['success' => false, 'message' => 'Authentication required']);
		exit;
	}
}

function requireGuest(): void {
	if (isset($_SESSION['user_id'])) {
		header('Location: /profile');
		exit;
	}
}
