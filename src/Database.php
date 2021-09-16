<?php

use config\Config;

class Database extends mysqli {
	
	public function __construct(Config $config) {
		parent::__construct($config->database->hostname, $config->database->username, $config->database->password, $config->database->database, $config->database->port, $config->database->socket);
	}
}
