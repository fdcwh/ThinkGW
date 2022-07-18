<?php
declare (strict_types=1);

namespace kernel\base;

use kernel\exception\ApiException;
use think\App;
use think\exception\ValidateException;
use think\Response;
use think\Validate;

/**
 * Class ApiBase API 基类
 * @package kernel\controller
 */
abstract class ApiBase {

	/**
	 * Request实例
	 * @var \think\Request
	 */
	protected $request;

	/**
	 * 应用实例
	 * @var App
	 */
	protected $app;

	/**
	 * 是否批量验证
	 * @var bool
	 */
	protected $batchValidate = false;

	/**
	 * 控制器中间件
	 * @var array
	 */
	protected $middleware = [
		'Token',
	];

	/**
	 * 构造方法
	 * @access public
	 *
	 * @param App $app 应用对象
	 */
	public function __construct(App $app) {
		$this->app     = $app;
		$this->request = $this->app->request;
		// 控制器初始化
		$this->initialize();
	}

	// 初始化
	protected function initialize() {
	}

	/**
	 * 验证数据
	 * @access protected
	 *
	 * @param array        $data     数据
	 * @param string|array $validate 验证器名或者验证规则数组
	 * @param array        $message  提示信息
	 * @param bool         $batch    是否批量验证
	 *
	 * @return array|string|true
	 * @throws ValidateException
	 */
	protected function validate(array $data, $validate, array $message = [], bool $batch = false) {
		if (is_array($validate)) {
			$v = new Validate();
			$v->rule($validate);
		} else {
			if (strpos($validate, '.')) {
				// 支持场景
				[$validate, $scene] = explode('.', $validate);
			}
			$class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
			$v     = new $class();
			if (!empty($scene)) {
				$v->scene($scene);
			}
		}

		$v->message($message);
		// 是否批量验证
		if ($batch || $this->batchValidate) {
			$v->batch(true);
		}
		return $v->failException(true)->check($data);
	}


	/**
	 * @title  __call 魔法函数
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return void
	 */
	public function __call($name, $arguments) {
		throw new ApiException(500);
	}

	/**
	 * @title success 成功返回操作
	 *
	 * @param array|string $data   响应数据
	 * @param int          $code   状态码
	 * @param string       $msg    提示信息
	 * @param array        $header 响应头信息
	 *
	 * @return Response
	 */
	protected static function success($data = null, string $msg = 'success', $code = 200, array $header = []): Response {
		return self::result($data, $code, $msg, $header);
	}

	/**
	 * @title error 错误返回操作
	 *
	 * @param string $msg    错误信息
	 * @param int    $code   状态码
	 * @param array  $data   响应数据
	 * @param array  $header 响应头信息
	 *
	 * @return Response
	 */
	protected static function error(string $msg = 'error', int $code = 500, $data = null, array $header = []): Response {
		return self::result($data, $code, $msg, $header);
	}

	/**
	 * @title  apiResult 统一返回
	 *
	 * @param array|string $data   响应数据
	 * @param int          $code   状态码
	 * @param string       $msg    响应提示信息
	 * @param array        $header 响应头信息
	 * @param string       $type   响应头类型
	 *
	 * @return Response
	 */
	protected static function result($data, int $code = 200, string $msg = '', array $header = [], string $type = 'json'): Response {
		$result = [
			'code' => $code,
			'msg'  => $msg,
			'data' => $data
		];
		return Response::create($result, $type, 200)->header($header);
	}
}
