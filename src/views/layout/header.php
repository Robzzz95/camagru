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

<nav class="topbar">
	<a href="/" class="logo">
		<img src="/assets/favicon.ico" class="logo-icon" alt="logo">
		CAMAGRU
	</a>

	<div class="nav-right">
	<?php if (!empty($_SESSION['user_id'])): ?>
		<a href="/profile">Profile</a>
		<form method="POST" action="/logout" style="display:inline">
			<button type="submit">Logout</button>
		</form>

	<?php else: ?>
		<a href="/login">Login</a>
		<a href="/signup">Signup</a>
	<?php endif; ?>
	</div>
</nav>
