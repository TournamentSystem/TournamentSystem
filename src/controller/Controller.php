<?php

namespace controller;

use Database;
use mysqli_stmt;
use view\View;

abstract class Controller {
	/**
	 * @var string[]
	 */
	public const ALLOWED_METHODS = ['GET', 'POST', 'PUT', 'DELETE'];
	public static string $allowedMethodsString = '';
	
	protected const OK = 200;
	protected const NO_CONTENT = 204;
	protected const SEE_OTHER = 303;
	protected const TEMPORARY_REDIRECT = 307;
	protected const PERMANENT_REDIRECT = 308;
	protected const UNAUTHORIZED = 401;
	protected const FORBIDDEN = 403;
	protected const NOT_FOUND = 404;
	protected const METHOD_NOT_ALLOWED = 405;
	protected const NOT_IMPLEMENTED = 501;
	
	public static Database $db;
	
	protected mysqli_stmt $stmt;
	
	public function __construct(?string $query = null) {
		if($query !== null) {
			$this->stmt = self::$db->prepare($query);
		}
	}
	
	protected final function render(View $view): void {
		$view->render();
	}
	
	protected function get(): int {
		return self::NOT_IMPLEMENTED;
	}
	
	protected function post(): int {
		return self::NOT_IMPLEMENTED;
	}
	
	protected function put(): int {
		return self::NOT_IMPLEMENTED;
	}
	
	protected function delete(): int {
		return self::NOT_IMPLEMENTED;
	}
	
	public final function handleRequest(): void {
		$methodFunc = $_SERVER['REQUEST_METHOD'];
		
		if(!in_array($methodFunc, self::ALLOWED_METHODS)) {
			header('Allow: ' . self::$allowedMethodsString, true, self::METHOD_NOT_ALLOWED);
			exit;
		}
		
		$methodFunc = strtolower($methodFunc);
		http_response_code($this->$methodFunc());
	}
}


for($i = 0; $i < count(Controller::ALLOWED_METHODS); $i++) {
	if($i !== 0) {
		Controller::$allowedMethodsString .= ', ';
	}
	
	Controller::$allowedMethodsString .= Controller::ALLOWED_METHODS[$i];
}
