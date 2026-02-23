<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">
	<link rel="icon" href="/assets/favicon.ico">
	<title>Camagru</title>
	<link rel="stylesheet" href="/css/styles.css">
</head>
<body>

<header class="topbar">
	<div class="topbar-content">
		<a href="/" class="logo">
			<img src="/assets/favicon.ico" class="logo-icon" alt="logo">
			Camagru
		</a>
		<div>
			<?php if (isset($_SESSION['user_id'])): ?>
				<a href="/profile">Profile</a>
				<a href="/create">Post</a>
				<a href="/logout">Logout</a>
			<?php else: ?>
				<a href="/login">Login</a>
				<a href="/signup">Sign up</a>
			<?php endif; ?>
		</div>
	</div>
</header>