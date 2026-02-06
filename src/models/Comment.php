<?php

class Comment
{
	public static function create($userId, $imageId, $content) {
		$db = Database::get();
		$stmt = $db->prepare("INSERT INTO comments (user_id, image_id, content) VALUES (?, ?, ?)");
		return ($stmt->execute([$userId, $imageId, $content]));
	}

	public static function forImage($imageId) {
		$db = Database::get();
		$stmt = db->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON u.id = c.user_id
			WHERE image_id = ? ORDER BY c.created_at ASC");
		$stmt->execute([$imageId]);
		return ($stmt->fetchAll());
	}

}