<?php

require_once __DIR__ . '/../services/CommentService.php';

class CommentController
{
	private CommentService $service;

	public function _construct() {
		$this->service = new CommentService();
	}

	public function store() {
		$this->service->add($_SESSION['user_id'], $_POST['image_id'], $_POST['content']);
		header('Location: /');
		exit;
	}
}
