<?php

namespace config;

class GeneralConfig {
	public bool $debug = false;
	public string $logo = '/resources/svg/logo.svg';
	public string $time_format = '%H:%M:%S';
	public string $date_format = '%d.%m.%Y';
	
	public function datetime_format(): string {
		return $this->date_format . ' ' . $this->time_format;
	}
}
