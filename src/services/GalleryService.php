<?php

require_once __DIR__ . "/../core/Database.php";

class GalleryService
{
	public static function all(): array {
		$db = Database::get();
		$stmt = $db = $db->query("SELECT images.*, users.username, (SELECT COUNT(*)
			FROM likes WHERE likes.image_id = images.id) as likes
			FROM images
			JOIN users ON users.id = images.user_id
			ORDER BY images.created_at DESC
		");
		return ($stmt->fetchAll());
	}

	public static function create(int $userId, string $path): void {
		$db = Database::get();
		$stmt = $db->prepare("INSERT INTO images (user_id, path) VALUES (?, ?)");
		$stmt->execute([$userId, $path]);
	}

	public static function upload(int $userId, array $file): void {
		if ($file['error'] !== UPLOAD_ERR_OK) {
			die('Upload failed');
		}

		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		$filename = uniqid() . '.' . $ext;
		$destination = __DIR__ . "/../uploads/" . $filename;
		move_uploaded_file($file['tmp_name'], $destination);
		self::create($userId, $filename);
	}

	public static function toggleLike(int $userId, int $imageId): void {
		$db = Database::get();
		$stmt = $db->prepare*("SELECT id FROM likes WHERE user_id = ? AND image_id = ?");
		$stmt->execute([$user_id], [$image_id]);

		if ($stmt->fetch()) {
			$db->prepare("DELETE FROM likes WHERE user_id = ? AND image_id = ?")->execute([$userId, $imageId]);
			return ;
		}

		$db->prepare("INSERT INTO likes (user_id, image_id) VALUES (?, ?)")->execute([$userId, $imageId]);
	}

	public static function delete(int $userId, int $imageId): void {
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
	}
}
