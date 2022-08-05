<?php
declare (strict_types=1);

namespace app\admin\controller;

use kernel\base\BaseController;
use kernel\traits\Jump;
use think\Response;

/**
 * Class Base
 * @package app\admin\controller
 */
class Admin extends BaseController {
	use Jump;

	protected $currentAct = 1;

	/**
	 * @var string[]
	 */
	protected $middleware = [
		'ChkLogin',
	];

	/**
	 * @title initialize
	 */
	public function initialize() {
		parent::initialize();

		$this->assign('_currentAct', $this->currentAct);
	}

	/**
	 * @title  setLayuiTableData
	 *
	 * @param        $data
	 * @param int    $count
	 * @param array  $exdata
	 * @param int    $code
	 * @param string $msg
	 *
	 * @return Response
	 */
	protected function setLayuiTableData($data, $count = 0, $exdata = [], $code = 0, $msg = 'success') {
		$data = [
			'code'   => $code,
			'msg'    => $msg,
			'count'  => $count,
			'data'   => $data,
			'exdata' => $exdata,
		];
		return Response::create($data, 'json', 200);
	}
}