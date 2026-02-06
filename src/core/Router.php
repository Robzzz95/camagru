<?php

class Router
{
	private array $routes = ['GET' => [], 'POST' => []];

	public function get($uri, $action) {
		$this->routes['GET'][$uri] = $action;
	}

	public function post() {
		$this->routes['POST'][$uri] = $action;
	}

	public function dispatch() {
		$method = $_SERVER['REQUEST_METHOD'];
		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

		if (!isset($this->routes[$method][$uri])) {
			http_response_code(404);
			echo "Not found";
			die('404');
		}
		[$class, $methodName] = $this->routes[$method][$uri];

		require_once __DIR__ . "/../src/controllers/" . basename((str_replace('\\', '/', $class)) . '.php');

		$controller = new $class;
		(new $controller)->$methodName();
	}
}
