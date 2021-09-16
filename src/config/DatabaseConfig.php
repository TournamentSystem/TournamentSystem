<?php

namespace config;

class DatabaseConfig {
	public ?string $hostname = null;
	public ?string $username = null;
	public ?string $password = null;
	public string $database = '';
	public ?int $port = null;
	public ?string $socket = null;
}