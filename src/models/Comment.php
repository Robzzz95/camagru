<?php

require_once __DIR__ . '/../models/Model.php';

class Comment extends Model
{
	public function getComments(int $imageId, int $limit = 10, int $offset = 0): array
	{
		$sql = "SELECT c.id, c.content, c.created_at, u.id AS user_id, u.username FROM comments c 
			INNER JOIN users u ON u.id = c.user_id WHERE c.image_id = ? ORDER BY c.created_at DESC
			LIMIT  ? OFFSET ?";
		$rows = $this->executeRequest($sql, [$imageId, $limit + 1, $offset])->fetchAll();
		$hasMore = count($rows) > $limit;
		if ($hasMore)
			array_pop($rows);
	return (['comments' => $rows, 'hasMore' => $hasMore]);
	}

	public function create($userId, $imageId, $content) {
		$sql = "INSERT INTO comments (user_id, image_id, content) VALUES (?, ?, ?)";
		return ($this->executeRequest($sql, [$userId, $imageId, $content]));
	}

	public function delete($commentId, $userId): bool {
		$sql = "DELETE FROM comments WHERE id = ? AND (user_id = ? OR image_id IN
				(SELECT id FROM images WHERE user_id = ?))";
		return ($this->executeRequest($sql, [$commentId, $userId, $userId]) !== false);
	}

	public function lastInsertId(): int
	{
	    return (int)$this->getLastInsertId();
	}
}
