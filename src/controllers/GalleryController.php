<?php

require_once __DIR__ . '/../services/GalleryService.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../models/Comment.php';


class GalleryController
{
	public function index(): void
	{
		$images = GalleryService::all();
		require __DIR__ . '/../views/gallery.php';
	}

	public function upload(): void
	{
		Auth::requireLogin();
		try {
			GalleryService::upload(
				(int)$_SESSION['user_id'],
				$_FILES['image'] ?? []
			);
			$_SESSION['flash_success'] = "Image uploaded successfully.";
		} catch (Exception $e) {
			$_SESSION['flash_error'] = $e->getMessage();
		}
		header('Location: /');
		exit;
	}

	public function create(): void
	{
		Auth::requireLogin();
		$myImages = GalleryService::byUser((int)$_SESSION['user_id']);
		require __DIR__ . '/../views/create.php';
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

	public function show(int $id): void {
		$image = GalleryService::find($id);
		if (!$image) {
			http_response_code(404);
			echo "Post not found";
			return;
		}
		require __DIR__ . '/../views/post.php';
	}

	public function store(): void
	{
		header('Content-Type: application/json');
		try {
			if (!isset($_SESSION['user_id']))
				throw new Exception("Unauthorized");
		
			$raw = file_get_contents("php://input");
			if (!$raw)
				throw new Exception("Empty request body");
			$data = json_decode($raw, true);
			if (!isset($data['image']))
				throw new Exception("No image provided");
			$base64 = $data['image'];
			if (!preg_match('#^data:image/(png|jpeg);base64,#', $base64))
				throw new Exception("Invalid image format");
			$base64 = preg_replace('#^data:image/\w+;base64,#', '', $base64);
			$decoded = base64_decode($base64, true);
			if ($decoded === false)
				throw new Exception("Base64 decode failed");
			if (strlen($decoded) < 1000)
				throw new Exception("Decoded image too small");
			$uploadDir = __DIR__ . '/../public/uploads/';
			if (!is_dir($uploadDir))
				throw new Exception("Upload directory missing");
			if (!is_writable($uploadDir))
				throw new Exception("Upload directory not writable");
			$filename = bin2hex(random_bytes(16)) . '.jpg';
			$path = $uploadDir . $filename;
			$written = file_put_contents($path, $decoded);
			if ($written === false)
				throw new Exception("Failed writing file");
			GalleryService::create((int)$_SESSION['user_id'], $filename);
			echo json_encode(['success' => true]);
			exit;
		} catch (Throwable $e) {
			http_response_code(500);
			echo json_encode([
				'success' => false,
				'error' => $e->getMessage()
			]);
			exit;
		}
	}
}

