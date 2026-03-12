<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Comment.php';

class CommentService
{
	private Comment $comment;

	public function __construct()
	{
		$this->comment = new Comment;
	}

	public function add(int $userId, int $imageId, string $content): bool
	{
		$content = trim($content);
		if (!$content)
			return (false);

		return ($this->comment->create($userId, $imageId, $content) !== false);
	}

	public function delete(int $commentId, int $requestingUserId): bool
	{
		return ($this->comment->delete($commentId, $requestingUserId));
	}

	public function getComments(int $imageId, int $limit = 15, int $offset = 0): array
	{
		return ($this->comment->getComments($imageId, $limit, $offset));
	}

	public function lastId(): int
	{
	    return $this->comment->lastInsertId();
	}
}
