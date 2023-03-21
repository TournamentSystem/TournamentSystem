<?php

namespace TournamentSystem;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Script\Event;

class Installer {
	public const BANNER = <<<'EOT'
 _____                                                 _   ____            _
|_   _|__  _   _ _ __ _ __   __ _ _ __ ___   ___ _ __ | |_/ ___| _   _ ___| |_ ___ _ __ ___
  | |/ _ \| | | | '__| '_ \ / _` | '_ ` _ \ / _ \ '_ \| __\___ \| | | / __| __/ _ \ '_ ` _ \
  | | (_) | |_| | |  | | | | (_| | | | | | |  __/ | | | |_ ___) | |_| \__ \ ||  __/ | | | | |
  |_|\___/ \__,_|_|  |_| |_|\__,_|_| |_| |_|\___|_| |_|\__|____/ \__, |___/\__\___|_| |_| |_|
                                                                 |___/

EOT;
	
	public const CONFIG_FOLDER = 'config';
	
	public const MODULES_LIST = self::CONFIG_FOLDER . '/modules.json';
	
	public const DATABASE_CONFIG = self::CONFIG_FOLDER . '/database.json';
	public const DATABASE_CONFIG_DEFAULTS = [
		'host' => 'localhost',
		'port' => 3306,
		'user' => null,
		'password' => null,
		'database' => 'TournamentSystem',
		'prefix' => null,
	];
	
	/**
	 * @var PackageInterface[]
	 */
	private static array $installedPackages;
	/**
	 * @var array<string, Data>
	 */
	private static array $extraData = [];
	
	public static function postUpdate(Event $event) {
		$composer = $event->getComposer();
		$IO = $event->getIO();
		
		$IO->write(self::BANNER);
		
		/*
		 * Core & Modules
		 */
		$IO->write('<info>Looking for TournamentSystem components</info>');
		
		$corePackages = self::getCorePackages($composer);
		if(count($corePackages) === 0) {
			$IO->write('<error>No TournamentSystem core found!</error>');
			$IO->write('<error>Please install a TournamentSystem core.</error>');
			return;
		}
		if(count($corePackages) > 1) {
			$IO->write('<error>More than one TournamentSystem core found!</error>');
			$IO->write('<error>Please remove one of the following packages:</error>');
			foreach($corePackages as $package) {
				$IO->write('  - ' . self::packageToString($package));
			}
			return;
		}
		$corePackage = $corePackages[0];
		$IO->write('  - Found core: ' . self::packageToString($corePackage));
		
		$modulePackages = self::getModulePackages($composer);
		if(count($modulePackages) === 0) {
			$IO->write('  - Found <comment>0</comment> modules');
		}elseif(count($modulePackages) === 1) {
			$IO->write('  - Found <comment>1</comment> module: ' . self::packageToString($modulePackages[0]));
		}else {
			$IO->write('  - Found <comment>' . count($modulePackages) . '</comment> modules:');
			foreach($modulePackages as $package) {
				$IO->write('    - ' . self::packageToString($package));
			}
		}
		
		$packages = [$corePackage->getName() => $corePackage];
		foreach($modulePackages as $package) {
			$packages[$package->getName()] = $package;
		}
		
		/*
		 * Duplicate files
		 */
		$files = [];
		foreach($packages as $packageName => $package) {
			$files[$packageName] = self::getPackageFiles($composer, $package);
		}
		$duplicates = self::getDuplicateFiles($files);
		$duplicatesCount = count($duplicates);
		if($duplicatesCount > 0) {
			if($duplicatesCount === 1) {
				$IO->write('<warning>Duplicate file found!</warning>');
			}else {
				$IO->write('<warning>Found ' . $duplicatesCount . ' duplicate files!</warning>');
			}
			$IO->write('<warning>Please select which version of the file should be used:</warning>');
			
			foreach($duplicates as $file => $dupPackages) {
				$choices = array_map(fn($package) => self::packageToString($packages[$package]), $dupPackages);
				$choice = $dupPackages[$IO->select("- <comment>$file</comment>", $choices, null)];
				
				if($choice === null) {
					return;
				}
				
				foreach($dupPackages as $package) {
					if($package === $choice) {
						continue;
					}
					
					$files[$package] = array_diff($files[$package], [$file]);
				}
			}
		}
		
		/*
		 * Folder structure
		 */
		$IO->write('<info>Checking folder structure</info>');
		
		$IO->write('  - Checking <comment>config</comment>: ', false);
		if(!is_dir(self::CONFIG_FOLDER)) {
			if(mkdir(self::CONFIG_FOLDER)) {
				$IO->write('<info>created</info>');
			}else {
				$IO->write('<error>failed</error>');
				return;
			}
		}else {
			$IO->write('<info>OK</info>');
		}
		
		/*
		 * Installation
		 */
		$IO->write('<info>Installing TournamentSystem components</info>');
		$moduleList = [];
		foreach($packages as $packageName => $package) {
			$IO->write('  - Installing ' . self::packageToString($package));
			
			$packageDir = self::getPackageInstallPath($composer, $package);
			
			$copyFailed = false;
			foreach($files[$packageName] as $file) {
				$path = $packageDir . '/' . $file;
				$dir = dirname($file);
				
				if(!is_dir($dir)) {
					if(!mkdir($dir, recursive: true)) {
						$IO->write("<error>Failed to create directory </error><comment>$dir</comment>");
						return;
					}
				}
				
				if(!copy($path, $file)) {
					$IO->write("    - <comment>$file</comment>: <error>failed</error>");
					$copyFailed = true;
				}
			}
			
			if($copyFailed) {
				return;
			}
			
			if(self::getPackageType($package) === 'module') {
				$moduleList[$packageName] = self::getPackageData($package)->class;
			}
		}
		file_put_contents(self::MODULES_LIST, json_encode($moduleList, JSON_PRETTY_PRINT));
		
		/*
		 * Configuration
		 */
		$IO->write('<info>Configuring database connection</info>');
		
		$config = self::DATABASE_CONFIG_DEFAULTS;
		$configExists = file_exists(self::DATABASE_CONFIG);
		if($configExists) {
			$IO->write('  <info>Found existing database configuration</info>');
			
			$config = array_merge($config, json_decode(file_get_contents(self::DATABASE_CONFIG), true));
			
			$IO->write("    - Host: <comment>${config['host']}</comment>");
			$IO->write("    - Port: <comment>${config['port']}</comment>");
			$IO->write("    - User: <comment>${config['user']}</comment>");
			$IO->write("    - Password: <comment>${config['password']}</comment>");
			$IO->write("    - Database: <comment>${config['database']}</comment>");
			$IO->write("    - Prefix: <comment>${config['prefix']}</comment>");
		}
		
		$question = '  <info>Do you want to configure the database connection</info> [<comment>';
		$question .= $configExists ? 'no' : 'yes';
		$question .= '</comment>]? ';
		if($IO->askConfirmation($question, !$configExists)) {
			$askDbConfig = self::askConfig($config);
			
			$askDbConfig($IO, 'host', '    - Host');
			$askDbConfig($IO, 'port', '    - Port');
			$askDbConfig($IO, 'user', '    - User');
			$askDbConfig($IO, 'password', '    - Password');
			$askDbConfig($IO, 'database', '    - Database');
			$askDbConfig($IO, 'prefix', '    - Prefix');
			
			$IO->write('  <info>Writing configuration</info>');
			
			file_put_contents(self::DATABASE_CONFIG, json_encode($config, JSON_PRETTY_PRINT));
		}
	}
	
	/**
	 * @return PackageInterface[]
	 */
	public static function getInstalledPackages(Composer $composer): array {
		if(!isset(self::$installedPackages)) {
			self::$installedPackages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();
			
			foreach(self::$installedPackages as $package) {
				self::$extraData[$package->getName()] = new Data($package);
			}
		}
		
		return self::$installedPackages;
	}
	
	/**
	 * @return PackageInterface[]
	 */
	public static function getCorePackages(Composer $composer): array {
		return self::getPackagesByType($composer, 'core');
	}
	
	/**
	 * @return PackageInterface[]
	 */
	public static function getModulePackages(Composer $composer): array {
		return self::getPackagesByType($composer, 'module');
	}
	
	/**
	 * @return PackageInterface[]
	 */
	private static function getPackagesByType(Composer $composer, string $type): array {
		$packages = [];
		
		foreach(self::getInstalledPackages($composer) as $package) {
			if(self::getPackageType($package) === $type) {
				$packages[] = $package;
			}
		}
		
		return $packages;
	}
	
	public static function getPackageInstallPath(Composer $composer, PackageInterface $package): string {
		return realpath($composer->getInstallationManager()->getInstallPath($package));
	}
	
	public static function getPackageData(PackageInterface $package): Data {
		return self::$extraData[$package->getName()];
	}
	
	public static function getPackageType(PackageInterface $package): ?string {
		return self::getPackageData($package)->type ?? null;
	}
	
	/**
	 * @return string[]
	 */
	public static function getPackageFiles(Composer $composer, PackageInterface $package): array {
		$extraFiles = self::getPackageData($package)->files;
		
		if(count($extraFiles) === 0) {
			return [];
		}
		
		$packageDir = self::getPackageInstallPath($composer, $package);
		$files = [];
		
		foreach($extraFiles as $file) {
			$path = $packageDir . '/' . $file;
			
			if(is_dir($path)) {
				$files = array_merge($files, self::getDirectoryFiles($path));
			}elseif(is_file($path)) {
				$files[] = $path;
			}
		}
		
		return array_map(function(string $file) use ($packageDir) {
			return substr(realpath($file), strlen($packageDir) + 1);
		}, $files);
	}
	
	/**
	 * @return string[]
	 */
	private static function getDirectoryFiles(string $dir): array {
		$files = [];
		
		foreach(scandir($dir) as $file) {
			if($file === '.' || $file === '..') {
				continue;
			}
			
			$path = $dir . '/' . $file;
			if(is_dir($path)) {
				$files = array_merge($files, self::getDirectoryFiles($path));
			}elseif(is_file($path)) {
				$files[] = $path;
			}
		}
		
		return $files;
	}
	
	/**
	 * @param array<string, string[]> $files
	 * @return array<string, string[]>
	 */
	private static function getDuplicateFiles(array $files): array {
		$duplicates = [];
		
		foreach($files as $package => $packageFiles) {
			foreach($packageFiles as $file) {
				if(isset($duplicates[$file])) {
					$duplicates[$file][] = $package;
				}else {
					$duplicates[$file] = [$package];
				}
			}
		}
		
		return array_filter($duplicates, function(array $packages) {
			return count($packages) > 1;
		});
	}
	
	/**
	 * @param array<string, mixed> $config
	 * @return callable(IOInterface, string, string): void
	 */
	private static function askConfig(array &$config): callable {
		return function(IOInterface $IO, string $key, string $question) use (&$config) {
			$default = $config[$key] ?? null;
			
			if($default !== null) {
				$question .= " [<comment>$default</comment>]";
			}
			
			$config[$key] = $IO->ask("$question: ") ?? $default;
		};
	}
	
	private static function packageToString(PackageInterface $package): string {
		return '<info>' . $package->getName() . '</info> (<comment>' . $package->getVersion() . '</comment>)';
	}
}
