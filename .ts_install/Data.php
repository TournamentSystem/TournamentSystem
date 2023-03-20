<?php

namespace TournamentSystem;

use Composer\Package\PackageInterface;

class Data {
	public readonly string $type;
	/**
	 * @var class-string|null
	 */
	public readonly ?string $class;
	/**
	 * @var string[]
	 */
	public readonly array $files;
	
	public function __construct(PackageInterface $package) {
		$data = $package->getExtra()['tournament_system'] ?? [];
		
		$this->type = $data['type'] ?? '';
		$this->class = $data['class'] ?? null;
		$this->files = $data['files'] ?? [];
	}
}
