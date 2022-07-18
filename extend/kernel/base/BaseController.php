<?php
declare (strict_types=1);

namespace kernel\base;

use think\App;
use think\Exception;
use think\exception\ValidateException;
use think\facade\View;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController {

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
	protected $middleware = [];

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
	 * @title  fetch    解析和获取模板内容 用于输出
	 *
	 * @param string $template
	 * @param array  $vars
	 *
	 * @return string
	 */
	protected function fetch(string $template = '', array $vars = []): string {
		return View::fetch($template, $vars);
	}

	/**
	 * @title  assign
	 *
	 * @param      $name
	 * @param null $value
	 *
	 * @return View
	 */
	protected function assign($name, $value = null) {
		return View::assign($name, $value);
	}

	/**
	 * @title  __call 魔法函数
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return void
	 * @throws Exception
	 */
	public function __call($name, $arguments) {
		throw new Exception((string)$name . ' 不存在！');
	}
}
