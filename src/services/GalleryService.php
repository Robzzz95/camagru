<?php

require_once __DIR__ . "/../core/Database.php";

class GalleryService
{
	public static function all(): array {
		$db = Database::get();
		$stmt = $db->query("
			SELECT images.*, users.username,
			(SELECT COUNT(*) FROM likes WHERE likes.image_id = images.id) AS likes
			FROM images
			JOIN users ON users.id = images.user_id
			ORDER BY images.created_at DESC
		");
		return $stmt->fetchAll();
	}

	public static function create(int $userId, string $path): void {
		$db = Database::get();
		$stmt = $db->prepare("INSERT INTO images (user_id, path) VALUES (?, ?)");
		$stmt->execute([$userId, $path]);
	}

	public static function upload(int $userId, array $file): void
	{
		if (!isset($file) || !isset($file['error'])) {
			throw new Exception("No file uploaded");
		}
		if ($file['error'] !== UPLOAD_ERR_OK) {
			throw new Exception("Upload failed");
		}
		// Limit file size to 5MB
		$maxSize = 5 * 1024 * 1024;
		if ($file['size'] > $maxSize)
			throw new Exception("File too large (max 5MB)");
		$imageInfo = @getimagesize($file['tmp_name']);
		if ($imageInfo === false)
			throw new Exception("Invalid image file");
		$mime = $imageInfo['mime'];
		$allowedMime = [
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/gif'  => 'gif'
		];

		if (!isset($allowedMime[$mime]))
			throw new Exception("Unsupported image type");
		$extension = $allowedMime[$mime];
		$filename = bin2hex(random_bytes(16)) . '.' . $extension;
		$uploadDir = __DIR__ . '/../public/uploads/';
		$destination = $uploadDir . $filename;
		if (!move_uploaded_file($file['tmp_name'], $destination)) {
			throw new Exception("Failed to save file");
		}
		self::reencodeImage($destination, $mime);
		self::create($userId, $filename);
	}

	private static function reencodeImage(string $path, string $mime): void
	{
		switch ($mime) {
			case 'image/jpeg':
				$image = imagecreatefromjpeg($path);
				imagejpeg($image, $path, 90);
				break;

			case 'image/png':
				$image = imagecreatefrompng($path);
				imagepng($image, $path, 6);
				break;

			case 'image/gif':
				$image = imagecreatefromgif($path);
				imagegif($image, $path);
				break;

			default:
				return;
		}
		imagedestroy($image);
	}

	public static function toggleLike(int $userId, int $imageId): void
	{
		$db = Database::get();
		$stmt = $db->prepare("SELECT id FROM likes WHERE user_id = ? AND image_id = ?");
		$stmt->execute([$userId, $imageId]);
		if ($stmt->fetch()) {
			$db->prepare("DELETE FROM likes WHERE user_id = ? AND image_id = ?")
			   ->execute([$userId, $imageId]);
			return;
		}
		$db->prepare("INSERT INTO likes (user_id, image_id) VALUES (?, ?)")
		   ->execute([$userId, $imageId]);
	}

	public static function delete(int $userId, int $imageId): void
	{
		$db = Database::get();
		$stmt = $db->prepare("SELECT path, user_id FROM images WHERE id = ?");
		$stmt->execute([$imageId]);
		$image = $stmt->fetch();
		if (!$image) {
			throw new Exception("Image not found");
		}
		if ((int)$image['user_id'] !== $userId) {
			throw new Exception("Unauthorized");
		}
		$file = __DIR__ . '/../public/uploads/' . $image['path'];
		if (file_exists($file)) {
			unlink($file);
		}
		$db->prepare("DELETE FROM images WHERE id = ?")->execute([$imageId]);
		$db->prepare("DELETE FROM likes WHERE image_id = ?")->execute([$imageId]);
	}
}
