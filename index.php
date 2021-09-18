<?php

const __ROOT__ = __DIR__;

require_once 'vendor/autoload.php';
require_once 'src/autoload.php';

$CONFIG = new \config\Config('config.ini');
$DB = new Database($CONFIG);

\controller\Controller::$db = $DB;
\view\View::$config = $CONFIG;

if($CONFIG->general->debug) {
	print('GLOBALS : ');
	
	if($_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
		var_dump($GLOBALS);
	}else {
		var_dump([
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_FILES' => $_FILES,
			'_REQUEST' => $_REQUEST,
			'_SERVER' => $_SERVER,
		]);
	}
}

$controller = null;
if(!array_key_exists('type', $_REQUEST)) {
	$controller = new \controller\TournamentListController();
}else {
	switch($_REQUEST['type']) {
		case 'player':
			$controller = new \controller\PlayerController();
			break;
		case 'coach':
			$controller = new \controller\CoachController();
			break;
		case 'club':
			$controller = new \controller\ClubController();
			break;
		case 'tournament':
			$controller = new \controller\TournamentController();
			break;
		
		case 'admin':
			switch($_REQUEST['action']) {
				case 'login':
					$controller = new \controller\admin\LoginController();
					break;
				case 'dashboard':
					$controller = new \controller\admin\DashboardController();
					break;
			}
			break;
	}
}

if($controller) {
	$controller->handleRequest();
}else {
	$page = new \view\DebugView();
	$page->render();
}
