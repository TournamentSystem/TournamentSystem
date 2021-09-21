<?php

namespace config;

class Config {
	public GeneralConfig $general;
	public DatabaseConfig $database;
	
	public function __construct(string $filename) {
		$config = parse_ini_file($filename, true, INI_SCANNER_TYPED);
		
		$this->general = new GeneralConfig();
		self::set($config, 'General', 'debug', $this->general->debug);
		self::set($config, 'General', 'logo', $this->general->logo);
		self::set($config, 'General', 'time_format', $this->general->time_format);
		self::set($config, 'General', 'date_format', $this->general->date_format);
		
		$this->database = new DatabaseConfig();
		self::set($config, 'Database', 'hostname', $this->database->hostname);
		self::set($config, 'Database', 'username', $this->database->username);
		self::set($config, 'Database', 'password', $this->database->password);
		self::set($config, 'Database', 'database', $this->database->database);
		self::set($config, 'Database', 'port', $this->database->port);
		self::set($config, 'Database', 'socket', $this->database->socket);
	}
	
	private static function set($ini, string $section, string $key, &$field) {
		$value = $ini[$section][$key];
		
		if(is_string($value)) {
			if(!strlen($value)) {
				return;
			}
		}
		
		$field = $value;
	}
}
