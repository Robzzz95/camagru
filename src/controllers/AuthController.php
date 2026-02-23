<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../middleware/Auth.php';

class AuthController {

	private AuthService $service;

	public function __construct()
	{
		$this->service = new AuthService;
	}

	public function login()
	{
		Auth::guest();

		$email = $_POST['email'] ?? '';
		$password = $_POST['password'] ?? '';
		if ($this->service->login($email, $password)) {
			header('Location: /');
		}
		else {
			header('Location: /?error=login=1');
		}
		exit;
	}

	public function signup()
	{
		Auth::guest();
		$result = $this->service->signup($_POST);
		if ($result['success'])
			header('Location: /');
		else
			header('Location: /?error=' . urlencode($result['message']));
		exit;
	}

	public function logout(): void
	{
		Auth::check();
		$this->service->logout();
		header('Location: /');
		exit;
	}

	public function confirm()
	{
		$token = $_GET['token'] ?? '';
		if (!$token)
		{
			echo "Invalid token";
			return;
		}
		if ($this->service->confirmEmail($token))
			echo "Email confirmed. You can now login";
		else
			echo "Invalid or expired token.";
	}

	public function showLogin(): void
	{
		if (isset($_SESSION['user_id'])) {
			header('Location: /');
			exit;
		}

		require __DIR__ . '/../views/login.php';
	}

	public function showSignup(): void
	{
		if (isset($_SESSION['user_id'])) {
			header('Location: /');
			exit;
		}

		require __DIR__ . '/../views/signup.php';
	}

	public function show(int $id): void
	{
		$image = GalleryService::find($id);
		if (!$image) {
			http_response_code(404);
			echo "Post not found";
			return;
		}
		$image['comments'] = Comment::forImage($id);
		require __DIR__ . '/../views/post.php';
	}

	public function create(): void
	{
		require __DIR__ . '/../views/create.php';
	}
}


