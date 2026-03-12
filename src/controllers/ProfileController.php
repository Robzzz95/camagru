<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/GalleryService.php';

class ProfileController
{
	private User $user;
	private GalleryService $gallery;

	public function __construct()
	{
		$this->user	= new User;
		$this->gallery = new GalleryService;
	}

	public function index(?int $id = null): void
	{
		$userId = $id ?? Auth::id();
		$user = $this->user->getById($userId);
		if (!$user) {
			http_response_code(404);
			echo "User not found";
			exit;
		}

		$result = $this->gallery->byUser($userId);
		(new View('profile'))->render(['user' => $user,
			'posts' => $result['posts'],
			'hasMore' => $result['hasMore'],
			'totalPosts'=> $this->gallery->countByUser($userId),
			'isOwner' => Auth::id() === $userId,]);
	}

	public function morePosts(?int $id = null): void
	{
		header('Content-Type: application/json');
		$userId = $id ?? Auth::id();
		$offset = (int)($_GET['offset'] ?? 0);
		echo json_encode($this->gallery->byUser($userId, 12, $offset));
		exit;
	}
}
