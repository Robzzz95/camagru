<?php

require_once __DIR__ . '/../middleware/Auth.php';

class SettingsController
{
	public function index(): void
	{
		Auth::requireLogin();
		require __DIR__ . '/../views/settings.php';
	}
}
