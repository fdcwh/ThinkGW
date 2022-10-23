<?php
declare (strict_types=1);

namespace app\admin\controller;


/**
 * Class Chats
 * @package app\admin\controller
 */
class Chats extends Admin {

	protected $currentAct = 2;

	/**
	 * @title  index
	 * @return string
	 */
	public function index() {
		$data['list'] = [];
		return $this->fetch('index', $data);
	}

}