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
		if ($file['error'] !== UPLOAD_ERR_OK)
			throw new Exception("Upload failed");

		$allowed = ['jpg','jpeg','png','gif'];
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if (!in_array($ext, $allowed))
			throw new Exception("Invalid file type");

		$filename = bin2hex(random_bytes(16)) . '.' . $ext;
		$destination = __DIR__ . "/../uploads/" . $filename;
		if (!move_uploaded_file($file['tmp_name'], $destination))
			throw new Exception("Failed to move file");

		self::create($userId, $filename);
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
		$file = __DIR__ . '/../uploads/' . $image['path'];
		if (file_exists($file)) {
			unlink($file);
		}
		// $db->prepare("DELETE FROM images WHERE id = ?")->execute([$imageId]);
		// $db->prepare("DELETE FROM likes WHERE image_id = ?")->execute([$imageId]);
	}
}
