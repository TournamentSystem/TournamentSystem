<?php

namespace TournamentSystem\View;

use Latte\Engine;
use TournamentSystem\Config\Config;

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
