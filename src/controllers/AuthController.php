<?php
declare(strict_types=1);
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';

class AuthController
{
	private AuthService $service;

	public function __construct()
	{
		$this->service = new AuthService;
	}

	public function login(): void
	{
		Auth::guest();
		$email = $_POST['email'] ?? '';
		$password = $_POST['password'] ?? '';

		if ($this->service->login($email, $password))
			header('Location: /');
		else
			header('Location: /login?error=' . urlencode('Invalid credentials'));
		exit;
	}

	public function signup(): void
	{
		Auth::guest();
		$result = $this->service->signup($_POST);

		if ($result['success'])
			header('Location: /login?success=' . urlencode('Account created, please confirm your email'));
		else
			header('Location: /signup?error=' . urlencode($result['message']));
		exit;
	}

	public function logout(): void
	{
		Auth::requireLogin();
		Auth::logout();
		header('Location: /');
		exit;
	}

	public function confirm(): void
	{
		$token = $_GET['token'] ?? '';
		if (!$token) {
			header('Location: /login?error=' . urlencode('Invalid token'));
			exit;
		}

		if ($this->service->confirmEmail($token))
			header('Location: /login?success=' . urlencode('Email confirmed, you can now log in'));
		else
			header('Location: /login?error=' . urlencode('Invalid or expired token'));
		exit;
	}

	public function showLogin(): void
	{
		Auth::guest();
		(new View('login'))->render(); 
	}

	public function showSignup(): void
	{
		Auth::guest();
		(new View('signup'))->render(); 
	}
}
