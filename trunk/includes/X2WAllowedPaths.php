<?php
/**
 * @file X2WAllowedPaths.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 *
 * @copyright 2010 Alejandro Darío Simi
 * @license GPL
 * @author Alejandro Darío Simi
 * @date 2010-07-11
 */

/**
 * @class X2WAllowedPaths
 * @todo doc
 */
class X2WAllowedPaths {
	/**
	 * @var boolean
	 */
	protected	$_isRecursive;
	/**
	 * @var array
	 */
	protected	$_paths;

	/**
	 * Constructor
	 * @param $list @todo doc
	 * @param $isRecursive @todo doc
	 */
	public function __construct(&$list=array(), $isRecursive=false) {
		$this->_isRecursive = $isRecursive;
		$this->_paths       = array(
					'files'		=> array(),
					'directories'	=> array(),
					'noaccess'	=> array(),
					'unknown'	=> array());

		$this->addPaths($list);
	}

	/*
	 * Public Methods
	 */
	/**
	 * @todo doc
	 * @param $list @todo doc
	 */
	public function addPaths(&$list) {
		if(is_array($list)) {
			foreach($list as $path) {
				$path = X2WAllowedPaths::cleanPath($path);
				if(file_exists($path)) {
					if(is_readable($path)) {
						if(is_dir($path)) {
							$this->addPathTo($path, $this->_paths['directories']);
						} else {
							$this->addPathTo($path, $this->_paths['files']);
						}
					} else {
						$this->addPathTo($path, $this->_paths['noaccess']);
					}
				} else {
					$this->addPathTo($path, $this->_paths['unknown']);
				}
			}
		}
		sort($this->_paths['directories']);
		sort($this->_paths['files']);
		sort($this->_paths['noaccess']);
		sort($this->_paths['unknown']);
	}
	/**
	 * @todo doc
	 * @param $path @todo doc
	 * @return @todo doc
	 */
	public function check($path) {
		$out = false;

		$path = X2WAllowedPaths::cleanPath($path);
		/*
		 * Checking if its a known file.
		 */
		$out = in_array($path, $this->_paths['files']);
		/*
		 * Checking if its a known directory.
		 */
		if(!$out) {
			$auxPath = dirname($path);
			if($this->_isRecursive) {
				foreach($this->_paths['directories'] as $dir) {
					if(strpos($auxPath, $dir) === 0) {
						$out = true;
						break;
					}
				}
			} else {
				$out = in_array($auxPath, $this->_paths['directories']);
			}
		}

		return $out;
	}
	/**
	 * @return @todo doc
	 */
	public function directories() {
		return $this->_paths['directories'];
	}
	/**
	 * @return @todo doc
	 */
	public function files() {
		return $this->_paths['files'];
	}
	/**
	 * @return @todo doc
	 */
	public function noAccess() {
		return $this->_paths['noaccess'];
	}
	/**
	 * @return @todo doc
	 */
	public function unknown() {
		return $this->_paths['unknown'];
	}

	/*
	 * Protected Methods
	 */
	/**
	 * @todo doc
	 * @param $path @todo doc
	 * @param $list @todo doc
	 */
	protected function addPathTo($path, &$list) {
		if(!in_array($path, $list)) {
			$list[] = $path;
		}
	}

	/*
	 * Public Class Method
	 */
	/**
	 * @todo doc
	 * @param $path @todo doc
	 * @return @todo doc
	 */
	public static function cleanPath($path) {
		$path = str_replace('/',  DIRECTORY_SEPARATOR, $path);
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

		while(strpos($path, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) !== false) {
			$path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
		}
		if(substr($path,strlen($path)-1,1) == DIRECTORY_SEPARATOR) {
			$path = substr($path,0, strlen($path)-1);
		}
		return $path;
	}
}
