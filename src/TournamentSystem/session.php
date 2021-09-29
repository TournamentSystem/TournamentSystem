<?php

session_set_cookie_params([
	'httponly' => true,
	'samesite' => 'strict'
]);

function session_exists(): bool {
	return isset($_COOKIE[session_name()]) && session_start() && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']);
}

function session_stop(): void {
	session_destroy();
	
	$options = session_get_cookie_params();
	$options['expires'] = 1;
	
	unset($options['lifetime']);
	
	setcookie(session_name(), '', $options);
}
