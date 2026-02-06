<?php

class Auth 
{
	public static function check() {
		if (!isset($_SESSION['user_id'])) {
			header('Location: /');
			exit;
		}
	}

	public static function guest() {
		if (isset($_SESSION['user_id'])) {
			header('Location: /profile');
			exit;
		}
	}

	public static function requireLogin() {
		if (!isset($_SESSION['user_id'])) {
			header('Location: /');
			exit;
		}
	}
}
