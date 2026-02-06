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
		if (!isset($_FILES['image'])) {
			die('No image');
		}

		GalleryService::upload($_SESSION['user_id'], $_FILES['image']);
		header('Location: /');
		exit;
	}

	public function like() {
		Auth::requireLogin();
		$imageId = (int)$_POST['image_id'];
		GalleryService::toggleLike($_SESSION['user_id'], $imageId);
		header('Location: /');
		exit;
	}

	public function delete() {
		Auth::requireLogin();
		$imageId = (int)$_POST['image_id'];
		GalleryService::delete($_SESSION['user_id'], $imageId);
		header('Location: /');
		exit;
	}
}

