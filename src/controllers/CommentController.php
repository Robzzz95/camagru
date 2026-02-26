<?php

require_once __DIR__ . '/../services/CommentService.php';

class CommentController
{
	private CommentService $service;

	public function __construct() {
		$this->service = new CommentService();
	}

	public function store() {
		if (!isset($_SESSION['user_id'])) {
			http_response_code(401);
			echo json_encode(['success' => false]);
			return;
		}
		$success = $this->service->add(
			$_SESSION['user_id'],
			$_POST['image_id'],
			$_POST['content']
		);
		echo json_encode(['success' => $success]);
	}

	public function delete() {
		if (!isset($_SESSION['user_id'])) {
			header('Location: /');
			exit;
		}

		Comment::delete($_POST['comment_id'], $_SESSION['user_id']);
		header('Location: ' . $_SERVER[HTTP_REFERER]);
	}
}
