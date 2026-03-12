<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Model.php';

class Post extends Model
{
	public function getFeed(int $limit = 10, int $offset = 0, ?int $userId = null): array
	{
		$sql = "SELECT i.*, u.username, COUNT(DISTINCT l.id) AS like_count,
			COUNT(DISTINCT c.id) AS comment_count,
			MAX(CASE WHEN l.user_id = ? THEN 1 ELSE 0 END) AS liked_by_me
			FROM images i INNER JOIN users u ON u.id = i.user_id
			LEFT JOIN  likes	l ON l.image_id = i.id
			LEFT JOIN  comments c ON c.image_id = i.id
			GROUP BY   i.id, u.id ORDER BY   i.created_at DESC LIMIT ? OFFSET ?";

		$rows = $this->executeRequest($sql, [$userId ?? 0, $limit + 1, $offset])->fetchAll();
		$hasMore = count($rows) > $limit;
		if ($hasMore)
			array_pop($rows);

		return (['posts' => $rows, 'hasMore' => $hasMore]);
	}

	public function getById(int $postId, ?int $requestingUserId = null): ?array
	{
		$sql = "SELECT i.*, u.username, COUNT(DISTINCT l.id) AS like_count,
			COUNT(DISTINCT c.id) AS comment_count,
			MAX(CASE WHEN l.user_id = ? THEN 1 ELSE 0 END) AS liked_by_me
			FROM images i INNER JOIN users u ON u.id = i.user_id
			LEFT JOIN likes l ON l.image_id = i.id LEFT JOIN comments c ON c.image_id = i.id
			WHERE i.id = ? GROUP BY i.id, u.id";

		$row = $this->executeRequest($sql, [$requestingUserId ?? 0, $postId])->fetch();
		return ($row ?: null);
	}

	public function getByUser(int $userId, int $limit = 12, int $offset = 0): array
	{
		$sql = "SELECT i.*, u.username, COUNT(DISTINCT l.id) AS like_count,
			COUNT(DISTINCT c.id) AS comment_count
			FROM images i INNER JOIN users u ON u.id = i.user_id
			LEFT JOIN likes l ON l.image_id = i.id LEFT JOIN  comments c ON c.image_id = i.id
			WHERE i.user_id = ? GROUP BY i.id, u.id ORDER BY i.created_at DESC LIMIT ? OFFSET ?";

		$rows = $this->executeRequest($sql, [$userId, $limit + 1, $offset])->fetchAll();
		$hasMore = count($rows) > $limit;
		if ($hasMore)
			array_pop($rows);
		return (['posts' => $rows, 'hasMore' => $hasMore]);
	}

	public function countByUser(int $userId): int
	{
		$row = $this->executeRequest("SELECT COUNT(*) AS total
			FROM images WHERE user_id = ?", [$userId])->fetch();
			return ((int)$row['total']);
	}

	public function create(int $userId, string $path): int
	{
		$sql = "INSERT INTO images (user_id, path) VALUES (?, ?)";
		$this->executeRequest($sql, [$userId, $path]);
		return (int)$this->getLastInsertId();
	}

	public function delete(int $postId, int $requestingUserId): bool
	{
		$sql = "DELETE FROM images WHERE id = ? AND user_id = ?";
		return $this->executeRequest($sql, [$postId, $requestingUserId]) !== false;
	}
}
