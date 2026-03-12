<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../config/env.php';

$router = new Router();
$router->dispatch();


//todo
/*
settings
seeing likes as a guest but not be able to like a picture
notifications on comments
add forgot the password thingy
check the vulnerability
render gifs
maybe redis
possibility to share on social networks
*/