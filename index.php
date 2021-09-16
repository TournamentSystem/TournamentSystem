<?php

const __ROOT__ = __DIR__;

require_once 'vendor/autoload.php';
require_once 'src/autoload.php';

$CONFIG = new \config\Config('config.ini');
$DB = new Database($CONFIG);

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

class TestView extends \view\View {
	
	public function __construct() {
		parent::__construct('Test View', 'test_page');
	}
	
	public function render(): void {
		parent::renderPage(var_export([
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_FILES' => $_FILES,
			'_REQUEST' => $_REQUEST,
			'_SERVER' => $_SERVER,
		], true));
	}
}

$page = null;
if(!array_key_exists('type', $_REQUEST)) {
	$tournaments = [];
	
	$result = $DB->query('SELECT * FROM tournament_tournament');
	if($result) {
		foreach($result->fetch_all(MYSQLI_ASSOC) as $tournament) {
			array_push($tournaments, new \model\Tournament(
				$tournament['id'],
				$tournament['name'],
				new DateTime($tournament['start']),
				new DateTime($tournament['end']),
				$tournament['owner']
			));
		}
		
		$result->free();
	}
	
	$page = new \view\TournamentListView($tournaments, $_REQUEST['year'] ?? null);
}else {
	switch($_REQUEST['type']) {
		case 'player':
			$stmt = $DB->prepare('SELECT * FROM tournament_player NATURAL JOIN tournament_person WHERE id=?');
			$stmt->bind_param('i', $_REQUEST['id']);
			$stmt->execute();
			
			if($result = $stmt->get_result()) {
				if($player = $result->fetch_assoc()) {
					$player = new \model\Player(
						$player['id'],
						$player['firstname'],
						$player['name'],
						new DateTime($player['birthday'])
					);
					
					$page = new \view\PlayerView($player);
				}
			}
			break;
		case 'coach':
			$stmt = $DB->prepare('SELECT * FROM tournament_coach NATURAL JOIN tournament_person WHERE id=?');
			$stmt->bind_param('i', $_REQUEST['id']);
			$stmt->execute();
			
			if($result = $stmt->get_result()) {
				if($coach = $result->fetch_assoc()) {
					$coach = new \model\Coach(
						$coach['id'],
						$coach['firstname'],
						$coach['name'],
						new DateTime($coach['birthday'])
					);
					
					$page = new \view\CoachView($coach);
				}
			}
			break;
		case 'club':
			$stmt = $DB->prepare('SELECT * FROM tournament_club WHERE id=?');
			$stmt->bind_param('i', $_REQUEST['id']);
			$stmt->execute();
			
			if($result = $stmt->get_result()) {
				if($club = $result->fetch_assoc()) {
					$club = new \model\Club(
						$club['id'],
						$club['name']
					);
					
					$page = new \view\ClubView($club);
				}
			}
			break;
		case 'tournament':
			$stmt = $DB->prepare('SELECT * FROM tournament_tournament WHERE id=?');
			$stmt->bind_param('i', $_REQUEST['id']);
			$stmt->execute();
			
			if($result = $stmt->get_result()) {
				if($tournament = $result->fetch_assoc()) {
					$tournament = new \model\Tournament(
						$tournament['id'],
						$tournament['name'],
						new DateTime($tournament['start']),
						new DateTime($tournament['end']),
						$tournament['owner']
					);
					
					$page = new \view\TournamentView($tournament);
				}
			}
			break;
	}
}

if(!$page) {
	$page = new TestView();
}
$page->render();
