<?php

if(!function_exists('str_starts_with')) {
	function str_starts_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		
		return substr($haystack, 0, $length) === $needle;
	}
}
if(!function_exists('str_ends_with')) {
	function str_ends_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		
		if(!$length) {
			return true;
		}
		
		return substr($haystack, -$length) === $needle;
	}
}
