<?php
declare (strict_types=1);

namespace app\admin\controller;


/**
 * Class Setup
 * @package app\admin\controller
 */
class Setup extends Admin {

	protected $currentAct = 3;

	/**
	 * @title  index
	 * @return string
	 */
	public function index() {
		$data['list'] = [];
		return $this->fetch('index', $data);
	}

}