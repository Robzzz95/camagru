<?php

require_once __DIR__ . '/../models/Comment.php';

class CommentService
{
	public function add($userId, $imageId, $content) {
		if (!content)
				return (false);
		return (Comment::create($userId, $imageId, $content));
	}
}
