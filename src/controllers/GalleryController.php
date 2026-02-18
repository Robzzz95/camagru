<?php

require_once __DIR__ . '/../services/GalleryService.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../models/Comment.php';


class GalleryController
{
	public function index(): void
	{
		$images = GalleryService::all();
		foreach ($images as &$img) {
			$img['comments'] = Comment::forImage($img['id']);
		}
		require __DIR__ . '/../views/gallery.php';
	}

	public function upload() {
		Auth::requireLogin();
		if (empty($_FILES['image'])) {
			header('Location: /?error=no_file');
			exit;
		}
		GalleryService::upload((int)$_SESSION['user_id'], $_FILES['image']);
		header('Location: /');
		exit;
	}

	public function like() {
		Auth::requireLogin();
		$imageId = (int)($_POST['image_id'] ?? 0);
		GalleryService::toggleLike((int)$_SESSION['user_id'], $imageId);
		header('Location: /');
		exit;
	}

	public function delete() {
		Auth::requireLogin();
		$imageId = (int)($_POST['image_id'] ?? 0);
		GalleryService::delete((int)$_SESSION['user_id'], $imageId);
		header('Location: /');
		exit;
	}
}

