<?php
declare(strict_types=1);

class Router
{
	public function dispatch(): void
	{
		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$method = $_SERVER['REQUEST_METHOD'];
		$uri = rtrim($uri, '/') ?: '/';

		if ($method === 'GET') {
			$this->handleGet($uri);
			return;
		}
		if ($method === 'POST') {
			$this->handlePost($uri);
			return;
		}
		http_response_code(405);
		echo 'Method Not Allowed';
	}

	private function handleGet(string $uri): void
	{
		switch ($uri) {
			case '/':

			case '/gallery':
				require_once __DIR__ . '/../controllers/GalleryController.php';
				(new GalleryController())->index();
				break;

			case '/profile':
				require_once __DIR__ . '/../middleware/Auth.php';
				Auth::requireLogin();
				require_once __DIR__ . '/../controllers/ProfileController.php';
				(new ProfileController())->index();
				break;

			case '/settings':
				require_once __DIR__ . '/../middleware/Auth.php';
				Auth::requireLogin();
				require_once __DIR__ . '/../controllers/SettingsController.php';
				(new SettingsController())->index();
				break;
			
			case '/confirm':
				require_once __DIR__ . '/../controllers/AuthController.php';
				(new AuthController())->confirm();
				break;

			case '/login':
				require_once __DIR__ . '/../views/login.php';
				break;

			case '/signup':
				require_once __DIR__ . '/../views/signup.php';
				break;

			default:
				http_response_code(404);
				echo 'Not found';
		}
	}

	private function handlePost(string $uri): void
	{
		require_once __DIR__ . '/../controllers/AuthController.php';
		require_once __DIR__ . '/../controllers/GalleryController.php';

		switch ($uri) {
			case '/signup':
				(new AuthController())->signup();
				break;

			case '/login':
				(new AuthController())->login();
				break;

			case '/logout':
				(new AuthController())->logout();
				break;

			case '/upload':
				(new GalleryController())->upload();
				break;

			case '/like':
				(new GalleryController())->like();
				break;

			case '/delete':
				(new GalleryController())->delete();
				break;

			default:
				http_response_code(404);
				echo 'Not found';
		}
	}
}
