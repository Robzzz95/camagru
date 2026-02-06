<?php
declare(strict_types=1);

$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    http_response_code(500);
    exit('.env file not found');
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
	if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) 
		continue;

    [$key, $value] = array_map('trim', explode('=', $line, 2));

	$key = trim($key);
	$value = trim($value);
    $value = trim($value, "\"'");

    $_ENV[$key] = $value;
}