<?php

session_set_cookie_params([
	'httponly' => true,
	'samesite' => 'strict'
]);

function session_exists(): bool {
	return isset($_COOKIE[session_name()]);
}
