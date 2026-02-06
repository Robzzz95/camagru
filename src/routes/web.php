<?php

use App\Core\Router;
use App\Controllers\GalleryController;
use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\SettingsController;

$router->get('/', [GalleryController::class, 'index']);

$router->post('/upload', [GalleryController::class, 'upload']);
$router->post('/like', [GalleryController::class, 'like']);
$router->post('/delete', [GalleryController::class, 'delete']);

$router->post('/login', [AuthController::class, 'login']);
$router->post('/signup', [AuthController::class, 'signup']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/profile', [ProfileController::class, 'index']);
$router->get('/settings', [SettingsController::class, 'index']);
