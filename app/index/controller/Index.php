<?php

namespace app\index\controller;

use app\BaseController;

class Index extends BaseController {
	public function index() {
		return 'ThinkIM';
	}

	public function hello($name = 'ThinkPHP6') {
		return 'hello,' . $name;
	}
}
