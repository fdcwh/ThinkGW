<?php

namespace kernel\librarys;

/**
 * Class Auth
 * @package kernel\librarys
 */
class Auth {

	/**
	 * var object 对象实例
	 */
	protected static object $instance;

	/**
	 *
	 */
	public function __construct() {
	}

	/**
	 * @title  instance
	 *
	 * @param array $options
	 *
	 * @return Auth
	 */
	public static function instance(array $options = []) {
		if (is_null(self::$instance)) {
			self::$instance = new static($options);
		}
		return self::$instance;
	}


}