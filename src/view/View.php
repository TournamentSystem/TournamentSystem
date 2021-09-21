<?php

namespace view;

use config\Config;
use Latte\Engine;
use Latte\Runtime\Filters;

abstract class View {
	public static Engine $latte;
	public static Config $config;
	
	protected string $title;
	protected string $type;
	
	public function __construct(string $title, string $type) {
		$this->title = $title;
		$this->type = $type;
	}
	
	protected final function renderView(?string $body = ''): void {
		self::$latte->render('templates/base.latte', [
			'title' => $this->title,
			'head' => '',
			'logo' => $this->getLogo(),
			'type' => $this->type,
			'body' => $body
		]);
	}
	
	/**
	 * @param object|array $params
	 */
	protected final function template(string $name, $params = []): string {
		return self::$latte->renderToString("templates/$name", $params);
	}
	
	public abstract function render(): void;
	
	private function getLogo(): string {
		$logo = self::$config->general->logo;
		
		if(str_ends_with($logo, '.svg') && !str_starts_with($logo, 'http')) {
			return file_get_contents(__ROOT__ . $logo);
		}
		
		return "<img src='$logo' alt='Logo'/>";
	}
}


View::$latte = new Engine();
View::$latte->addFilter('time', fn($time, $format = null) => Filters::date($time, $format ?? $GLOBALS['CONFIG']->general->time_format));
View::$latte->addFilter('date', fn($date, $format = null) => Filters::date($date, $format ?? $GLOBALS['CONFIG']->general->date_format));
View::$latte->addFilter('datetime', fn($datetime, $format = null) => Filters::date($datetime, $format ?? $GLOBALS['CONFIG']->general->datetime_format()));
