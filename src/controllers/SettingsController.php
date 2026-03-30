<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/User.php';

class SettingsController
{
	private User $user;

	public function __construct()
	{
		$this->user = new User;
	}

	public function index(): void
	{
		$user = $this->user->getById(Auth::id());
		(new View('settings'))->render(['user' => $user]);
	}

	public function updateUsername(): void
	{
		header('Content-Type: application/json');
		try {
			$username = trim($_POST['username'] ?? '');
			if (!$username){
				http_response_code(400);
				echo json_encode(['success' => false, 'error' => 'Username required']);
				exit ;
			}
			if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
				http_response_code(422);
				echo json_encode(['success' => false, 'error' => 'Username must be 3-20 characters, letters/numbers/underscores only']);
				exit ;
			}
			if ($this->user->getByUsername($username)) {
				http_response_code(409);
				echo json_encode(['success' => false, 'error' => 'Username already taken']);
				exit ;
			}
			$this->user->updateUsername(Auth::id(), $username);
			http_response_code(200);
			echo json_encode(['success' => true, 'message' => 'Username updated']);

		} catch (Throwable $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}

	public function updateEmail(): void
	{
		header('Content-Type: application/json');
		try {
			$email = strtolower(trim($_POST['email'] ?? ''));
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				http_response_code(422);
				echo json_encode(['success' => false, 'error' => 'Invalid email']);
				exit ;
			}
			if ($this->user->getByEmail($email)) {
				http_response_code(409);
				echo json_encode(['success' => false, 'error' => 'Email already in use']);
				exit ;
			}
			$this->user->updateEmail(Auth::id(), $email);
			http_response_code(200);
			echo json_encode(['success' => true, 'message' => 'Email updated, please confirm your new address']);

		} catch (Throwable $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}

	public function updatePassword(): void
	{
		header('Content-Type: application/json');
		try {
			$current = $_POST['current_password'] ?? '';
			$new	 = $_POST['new_password']	 ?? '';
			$confirm = $_POST['confirm_password'] ?? '';

			if (!$current || !$new || !$confirm) {
				http_response_code(400);
				echo json_encode(['success' => false, 'error' => 'All fields required']);
				exit ;
			}
			if (strlen($new) < 8) {
				http_response_code(422);
				echo json_encode(['success' => false, 'error' => 'New password must be at least 8 characters']);
				exit ;
			}
			if ($new !== $confirm) {
				http_response_code(422);
				echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
				exit ;
			}

			$user = $this->user->getById(Auth::id());
			if (!password_verify($current, $user['password_hash'])) {
				http_response_code(401);
				echo json_encode(['success' => false, 'error' => 'Current password incorrect']);
				exit ;
			}

			$this->user->updatePassword(Auth::id(), password_hash($new, PASSWORD_DEFAULT));
			echo json_encode(['success' => true, 'message' => 'Password updated']);

		} catch (Throwable $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}

	public function deleteAccount(): void
	{
		header('Content-Type: application/json');
		try {
			$password = $_POST['password'] ?? '';
			$user	 = $this->user->getById(Auth::id());

			if (!password_verify($password, $user['password_hash'])) {
				http_response_code(401);
				echo json_encode(['success' => false, 'error' => 'Incorrect password.']);
				exit ;
			}

			$this->user->delete(Auth::id());
			Auth::logout();
			echo json_encode(['success' => true]);

		} catch (Throwable $e) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}

	public function updateAvatar(): void
	{
		header('Content-Type: application/json');
		try {
			$userId = Auth::id();

			if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
				http_response_code(400);
				echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error.']);
				exit;
			}

			$file = $_FILES['avatar'];
			$maxSize = 5 * 1024 * 1024; // 5 MB

			if ($file['size'] > $maxSize) {
				http_response_code(400);
				echo json_encode(['success' => false, 'error' => 'File too large. Max 5 MB.']);
				exit;
			}

			// Validate real MIME type, never trust the extension
			$finfo = new \finfo(FILEINFO_MIME_TYPE);
			$mime = $finfo->file($file['tmp_name']);
			$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

			if (!in_array($mime, $allowed, true)) {
				http_response_code(422);
				echo json_encode(['success' => false, 'error' => 'Invalid file type. Use JPG, PNG, WebP or GIF.']);
				exit;
			}

			$ext = match($mime) {'image/jpeg' => 'jpg', 'image/png'  => 'png',
				'image/webp' => 'webp','image/gif'  => 'gif',};

			$uploadDir = __DIR__ . '/../public/uploads/avatars/';
			// Delete old avatar file if one exists
			$oldAvatar = $this->user->getAvatarById($userId);
			if ($oldAvatar && file_exists($uploadDir . $oldAvatar)) {
				unlink($uploadDir . $oldAvatar);
			}

			$filename = $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
			$dest = $uploadDir . $filename;

			if (!move_uploaded_file($file['tmp_name'], $dest)) {
				http_response_code(500);
				echo json_encode(['success' => false, 'error' => 'Failed to save file.']);
				exit;
			}

			$this->user->updateAvatar($userId, $filename);

			echo json_encode(['success' => true,
				'avatar'  => '/uploads/avatars/' . $filename,
				'message' => 'Avatar updated!']);
		} catch (Throwable $e) {
			http_response_code(500);
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
		exit;
	}

}
