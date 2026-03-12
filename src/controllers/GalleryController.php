<?php
declare(strict_types=1);
require_once __DIR__ . '/../services/GalleryService.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Like.php';

class GalleryController
{
	private GalleryService $service;
	private Comment $comment;

	public function __construct()
	{
		$this->service = new GalleryService;
		$this->comment = new Comment;
	}

	public function index(): void
	{
		$result = $this->service->all(5);
		(new View('gallery'))->render(['images'  => $result['posts'],
			'hasMore' => $result['hasMore'],]);
	}

	public function show(int $id): void
	{
		$image = $this->service->find($id);
		if (!$image) {
			//add a proper 404
			http_response_code(404);
			echo "Post not found";
			exit;
		}
		// AJAX -> return modal partial, direct access -> back to gallery
		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']))
		{
			View::partial('post', ['image' => $image]);
			exit;
		}
		$result = $this->service->all(5);
		(new View('gallery'))->render(['images'	=> $result['posts'],
			'hasMore'   => $result['hasMore'], 'openPostId' => $id,]);
	}

	public function create(): void
	{
		$myImages = $this->service->byUser(Auth::id())['posts'];
		$stickers = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/assets/*.png') ?: []);
		(new View('create'))->render(['myImages' => $myImages, 'stickers' => $stickers]);
	}

	public function upload(): void
	{
		try {
			$this->service->upload(Auth::id(), $_FILES['image'] ?? []);
			$_SESSION['flash_success'] = "Image uploaded successfully.";
		} catch (Exception $e) {
			$_SESSION['flash_error'] = $e->getMessage();
		}
		header('Location: /');
		exit;
	}

	public function store(): void
	{
		header('Content-Type: application/json');
		try {
			$data = json_decode(file_get_contents("php://input"), true);
			if (!isset($data['image']))
				throw new Exception("No image provided");

			$this->service->storeBase64(Auth::id(), $data['image']);
			echo json_encode(['success' => true]);

		} catch (Throwable $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}

	public function like(): void
	{
		header('Content-Type: application/json');
		try {
			$imageId = (int)($_POST['image_id'] ?? 0);
			if (!$imageId)
				throw new Exception("Invalid image");

			$this->service->toggleLike(Auth::id(), $imageId);
			$liked = (new Like)->exists(Auth::id(), $imageId);
			echo json_encode(['success' => true, 'liked' => $liked]);

		} catch (Throwable $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}

	public function delete(): void
	{
		try {
			$imageId = (int)($_POST['image_id'] ?? 0);
			if (!$imageId)
				throw new Exception("Invalid image");

			$this->service->delete(Auth::id(), $imageId);
		} catch (Throwable $e) {
			$_SESSION['flash_error'] = $e->getMessage();
		}
		header('Location: /');
		exit;
	}

	public function morePosts(): void
	{
		header('Content-Type: application/json');
		$offset = (int)($_GET['offset'] ?? 0);
		echo json_encode($this->service->all(5, $offset));
		exit;
	}

}
