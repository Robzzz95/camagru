<?php
declare(strict_types=1);

class Router
{
	private array $getRoutes = [];
	private array $postRoutes = [];

	public function __construct()
	{
		$this->registerRoutes();
	}

	private function registerRoutes(): void
	{
		// ---------- GET ----------
		$this->get('/', 'GalleryController@index');
		$this->get('/gallery', 'GalleryController@index');

		$this->get('/profile', 'ProfileController@index', true);
		$this->get('/settings', 'SettingsController@index', true);

		$this->get('/login', fn() => require __DIR__ . '/../views/login.php');
		$this->get('/signup', fn() => require __DIR__ . '/../views/signup.php');

		$this->get('/confirm', 'AuthController@confirm');
		$this->get('/logout', 'AuthController@logout', true);
		$this->get('/auth/status', 'AuthController@status');

		// ---------- POST ----------
		$this->post('/signup', 'AuthController@signup');
		$this->post('/login', 'AuthController@login');

		$this->post('/upload', 'GalleryController@upload', true);
		$this->post('/like', 'GalleryController@like', true);
		$this->post('/delete', 'GalleryController@delete', true);
	}

	private function get(string $uri, $action, bool $auth = false): void
	{
		$this->getRoutes[$uri] = compact('action', 'auth');
	}

	private function post(string $uri, $action, bool $auth = false): void
	{
		$this->postRoutes[$uri] = compact('action', 'auth');
	}

	public function dispatch(): void
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$uri = rtrim($uri, '/') ?: '/';

		$routes = $method === 'POST' ? $this->postRoutes : $this->getRoutes;
		if (!isset($routes[$uri])) {
			http_response_code(404);
			echo 'Not found';
			return;
		}

		$route = $routes[$uri];
		if ($route['auth']) {
			require_once __DIR__ . '/../middleware/Auth.php';
			Auth::requireLogin();
		}
		$this->runAction($route['action']);
	}

	private function runAction($action): void
	{
		if (is_callable($action)) {
			$action();
			return;
		}
		[$controller, $method] = explode('@', $action);
		require_once __DIR__ . '/../controllers/' . $controller . '.php';
		(new $controller())->$method();
	}
}
