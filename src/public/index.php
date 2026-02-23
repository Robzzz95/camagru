<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../config/env.php';

$router = new Router();
$router->dispatch();
