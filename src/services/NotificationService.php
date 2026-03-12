<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Post.php';

class NotificationService
{
	private User $user;
	private Post $post;

	public function __construct()
	{
		$this->user = new User;
		$this->post = new Post;
	}

	public function notifyNewComment(int $imageId, string $commenterUsername, string $content): void
	{
		$post = $this->post->getById($imageId);
		if (!$post)
			return;

		$owner = $this->user->getById((int)$post['user_id']);
		if (!$owner)
			return;

		$to = $owner['email'];
		$subject = "New comment on your post";
		$appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';

		$body = implode("\n",
		["Hi {$owner['username']},",
			"",
			"@{$commenterUsername} commented on your post:",
			"\"{$content}\"",
			"",
			"View post: {$appUrl}/post/{$imageId}",]);

		$headers = implode("\r\n", ["From: Camagru <no-reply@camagru.local>",
			"Content-Type: text/plain; charset=UTF-8",]);
		mail($to, $subject, $body, $headers);
	}
}
