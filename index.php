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

session_set_cookie_params([
	'httponly' => true,
	'samesite' => 'strict'
]);

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
		
		$page = new \view\TournamentListView($tournaments, $_REQUEST['year'] ?? null);
		$result->free();
	}
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
				
				$result->free();
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
				
				$result->free();
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
				
				$result->free();
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
				
				$result->free();
			}
			break;
		
		case 'admin':
			switch($_REQUEST['action']) {
				case 'login':
					$invalidData = false;
					
					if($_SERVER['REQUEST_METHOD'] === 'POST') {
						$username = $_REQUEST['username'];
						
						$stmt = $DB->prepare('SELECT password FROM tournament_user WHERE name=?');
						$stmt->bind_param('s', $username);
						$stmt->execute();
						
						if($result = $stmt->get_result()) {
							if($hash = $result->fetch_row()) {
								$hash = $hash[0];
								
								if(password_verify($_REQUEST['password'], $hash)) {
									$result->free();
									
									session_start();
									$_SESSION['user'] = $username;
									
									header('Location: /admin/dashboard/', true, 303);
									die();
								}else {
									$invalidData = true;
								}
							}
							
							$result->free();
						}
					}
					
					$page = new \view\admin\LoginView($invalidData);
					break;
				case 'dashboard':
					if(!isset($_COOKIE['PHPSESSID'])) {
						header('WWW-Authenticate: Cookie realm="TournamentSystem" form-action="/admin/login/" cookie-name="' . session_name() . '"', true, 401);
						$page = new \view\admin\LoginView();
					}else {
						session_start();
					}
					break;
			}
			break;
	}
}

if(!$page) {
	$page = new \view\DebugView();
}
$page->render();
