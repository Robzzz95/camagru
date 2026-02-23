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
		$this->get('/login', 'AuthController@showLogin');
		$this->get('/signup', 'AuthController@showSignup');
		$this->get('/confirm', 'AuthController@confirm');
		$this->get('/logout', 'AuthController@logout', true);
		$this->get('/post/{id}', 'GalleryController@show');
		$this->get('/create', 'GalleryController@create', true);

		// ---------- POST ----------
		$this->post('/signup', 'AuthController@signup');
		$this->post('/login', 'AuthController@login');

		// $this->post('/upload', 'GalleryController@upload', true);
		$this->post('/gallery/store', 'GalleryController@store', true);
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
		foreach ($routes as $routeUri => $route)
		{
			$pattern = preg_replace('#\{[a-zA-Z]+\}#', '([0-9]+)', $routeUri);
			$pattern = '#^' . $pattern . '$#';

			if (preg_match($pattern, $uri, $matches))
			{
				array_shift($matches);

				if ($route['auth'])
				{
					require_once __DIR__ . '/../middleware/Auth.php';
					Auth::requireLogin();
				}
				$this->runActionWithParams($route['action'], $matches);
				return;
			}
		}
		http_response_code(404);;
	}

	private function runActionWithParams(string $action, array $params = [])
	{
		[$controllerName, $method] = explode('@', $action);
		$controllerPath = __DIR__ . '/../controllers/' . $controllerName . '.php';
		if (!file_exists($controllerPath)) {
			throw new Exception("Controller file not found: $controllerPath");
		}
		require_once $controllerPath;
		if (!class_exists($controllerName)) {
			throw new Exception("Controller class not found: $controllerName");
		}
		$controller = new $controllerName();
		$params = array_map(function ($param) {
			return is_numeric($param) ? (int)$param : $param;
		}, $params);

		return call_user_func_array([$controller, $method], $params);
	}
}
