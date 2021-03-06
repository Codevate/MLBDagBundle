<?php

namespace Mlb\DagBundle\Tests;

require_once __DIR__.'/bootstrap.php';

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel {

	private $config;

	public function __construct($environment, $config) {
		parent::__construct($environment, true);

		$fs = new Filesystem();
		if (!$fs->isAbsolutePath($config)) {
			$config = __DIR__.'/config/'.$config;
		}

		if (!file_exists($config)) {
			throw new \RuntimeException(sprintf('The config file "%s" does not exist.', $config));
		}

		$this->config = $config;
	}

	public function registerBundles() {
		return array(
			new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
			new \Mlb\DagBundle\MlbDagBundle(),
		);
	}

	public function registerContainerConfiguration(LoaderInterface $loader) {
		$loader->load($this->config);
	}

	public function serialize() {
		return $this->config;
	}

	public function unserialize($config) {
		$this->__construct($config);
	}

}
