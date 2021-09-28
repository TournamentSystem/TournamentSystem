<?php

namespace TournamentSystem;

define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);

require_once 'utility.php';
require_once 'session.php';

use Latte\Engine;
use Latte\Runtime\Filters;
use TournamentSystem\Config\Config;
use TournamentSystem\Controller\Admin\DashboardController;
use TournamentSystem\Controller\Admin\LoginController;
use TournamentSystem\Controller\Admin\LogoutController;
use TournamentSystem\Controller\ClubController;
use TournamentSystem\Controller\CoachController;
use TournamentSystem\Controller\Controller;
use TournamentSystem\Controller\PlayerController;
use TournamentSystem\Controller\TournamentController;
use TournamentSystem\Controller\TournamentListController;
use TournamentSystem\View\View;

class TournamentSystem {
	private static TournamentSystem $INSTANCE;
	
	private Config $CONFIG;
	private Database $DB;
	
	private function __construct() {
		$this->CONFIG = new Config('config.ini');
		$this->DB = new Database($this->CONFIG);
		
		$this->initStaticVars();
	}
	
	private function initStaticVars(): void {
		Controller::$db = $this->DB;
		
		View::$config = $this->CONFIG;
		
		View::$latte = new Engine();
		View::$latte->addFilter('time', fn($time, $format = null) => Filters::date($time, $format ?? $this->CONFIG->general->time_format));
		View::$latte->addFilter('date', fn($date, $format = null) => Filters::date($date, $format ?? $this->CONFIG->general->date_format));
		View::$latte->addFilter('datetime', fn($datetime, $format = null) => Filters::date($datetime, $format ?? $this->CONFIG->general->datetime_format()));
	}
	
	public function handle(): void {
		$controller = null;
		if(!array_key_exists('type', $_REQUEST)) {
			$controller = new TournamentListController();
		}else {
			switch($_REQUEST['type']) {
				case 'player':
					$controller = new PlayerController();
					break;
				case 'coach':
					$controller = new CoachController();
					break;
				case 'club':
					$controller = new ClubController();
					break;
				case 'tournament':
					$controller = new TournamentController();
					break;
				
				case 'admin':
					switch($_REQUEST['action']) {
						case 'login':
							$controller = new LoginController();
							break;
						case 'logout':
							$controller = new LogoutController();
							break;
						case 'dashboard':
							$controller = new DashboardController();
							break;
					}
					break;
			}
		}
		
		if($controller) {
			$controller->handleRequest();
		}else {
			$page = new \TournamentSystem\View\DebugView();
			$page->render();
		}
	}
	
	public static function instance(): TournamentSystem {
		if(!isset(self::$INSTANCE)) {
			self::$INSTANCE = new TournamentSystem();
		}
		
		return self::$INSTANCE;
	}
}