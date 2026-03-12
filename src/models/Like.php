<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Model.php';

class Like extends Model
{
	public function exists(int $userId, int $imageId): bool
	{
		$row = $this->executeRequest("SELECT id FROM likes WHERE user_id = ? AND image_id = ?",
			[$userId, $imageId])->fetch();
		return ((bool)$row);
	}

	public function add(int $userId, int $imageId): void
	{
		$this->executeRequest("INSERT INTO likes (user_id, image_id) VALUES (?, ?)",
			[$userId, $imageId]);
	}

	public function remove(int $userId, int $imageId): void
	{
		$this->executeRequest("DELETE FROM likes WHERE user_id = ? AND image_id = ?",
			[$userId, $imageId]);
	}

	public function toggle(int $userId, int $imageId): void
	{
		if ($this->exists($userId, $imageId))
			$this->remove($userId, $imageId);
		else
			$this->add($userId, $imageId);
	}
}
