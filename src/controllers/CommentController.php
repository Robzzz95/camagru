<?php
declare(strict_types=1);
require_once __DIR__ . '/../services/CommentService.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../models/User.php';


class CommentController
{
	private CommentService $service;
	private NotificationService $notification;

	public function __construct()
	{
		$this->service = new CommentService;
		$this->notification = new NotificationService;
	}

	public function store(): void
	{
		header('Content-Type: application/json');
		try {
			$imageId = (int)($_POST['image_id'] ?? 0);
			$content = trim($_POST['content'] ?? '');

			if (!$imageId || !$content)
				throw new Exception("Invalid comment");

			$this->service->add(Auth::id(), $imageId, $content);
			$user = Auth::user();
			try {
				$this->notification->notifyNewComment($imageId, $user['username'], $content);
			}
			catch (Throwable) {

			}

			echo json_encode(['success' => true, 
				'comment' => ['id' => $this->service->lastId(),
						'content' => htmlspecialchars($content),
						'user_id' => Auth::id(),
						'username' => $user['username'],
						'user_avatar' => $user['avatar'] ?? null,
						'can_delete' => true ]]);

		} catch (Throwable $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}

	public function delete(): void
	{
		try {
			$commentId = (int)($_POST['comment_id'] ?? 0);
			if (!$commentId)
				throw new Exception("Invalid comment");

			$this->service->delete($commentId, Auth::id());

		} catch (Throwable $e) {
			$_SESSION['flash_error'] = $e->getMessage();
		}
		header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
		exit;
	}

	public function index(): void
	{
		header('Content-Type: application/json');
		try {
			$imageId = (int)($_GET['image_id'] ?? 0);
			$offset  = (int)($_GET['offset']   ?? 0);

			if (!$imageId)
				throw new Exception("Invalid image");

			$data = $this->service->getComments($imageId, 10, $offset);
			$userId = Auth::id();

			$userModel = new User();
			$imageOwnerId = $userModel->getImageOwnerId($imageId);

			foreach ($data['comments'] as &$comment) {
				$comment['can_delete'] = $userId && (
					$userId === (int)$comment['user_id'] ||
					$userId === (int)$imageOwnerId
				);
			}

			echo json_encode($data);

		} catch (Throwable $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}
}