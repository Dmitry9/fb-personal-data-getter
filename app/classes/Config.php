<?php
namespace App;

/**
 * Class Config is managing configuration files
 * @package App
 */
class Config {

	private $configs = [
		'app' => [],
	];

	/**
	 * Config constructor.
	 *
	 * @param array $ids
	 *
	 * @throws \Exception
	 */
	public function __construct(array $ids = ['app', 'db', 'fb']) {
		$appRoot = dirname(dirname(dirname(__FILE__)));

		$configFolder = $appRoot . '/app/config/';
		foreach ( $ids as $configId ) {
			$file = $configFolder.$configId.'-config.php';
			if ( ! file_exists( $file ) ) {
				throw new \Exception("Can't open configuration file `".$configId.'-config.php'."`");
			}
			$this->configs[$configId] = require_once ($file);
		}
		$this->configs['app']['root'] = $appRoot;
	}

	/**
	 * Get config by Id
	 * @param $configId
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function get($configId) {
		if ( empty( $this->configs[ $configId ] ) ) {
			throw new \Exception("Undefined config Id `{$configId}`");
		}
		return $this->configs[ $configId ];
	}
}