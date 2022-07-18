<?php

namespace kernel\traits;

use think\exception\HttpResponseException;
use think\Response;

/**
 * Trait Jump
 * @package kernel\traits
 */
trait Jump {

	/**
	 *
	 * @title success   操作成功跳转的快捷方法
	 *
	 * @param mixed   $msg    提示信息
	 * @param string  $url    跳转的URL地址
	 * @param mixed   $data   返回的数据
	 * @param integer $wait   跳转等待时间
	 * @param array   $header 发送的Header信息
	 *
	 * @return void
	 */
	protected function success($msg = '', string $url = '', $data = '', int $wait = 3, array $header = []) {
		if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
			$url = $_SERVER["HTTP_REFERER"];
		} elseif ($url && !in_array($url, ['reload', 'close.reload', 'tip'])) {
			$url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
		}
		$result = [
			'code' => 200,
			'msg'  => $msg,
			'data' => $data,
			'url'  => $url ?: 'tip',
			'wait' => $wait,
		];

		$type = $this->getResponseType();
		// 把跳转模板的渲染下沉，这样在 response_send 行为里通过getData()获得的数据是一致性的格式
		if ('html' == strtolower($type)) {
			$tplArray = $this->app->config->get('jump');
			$response = Response::create(isset($tplArray[app('http')->getName()]) ? $tplArray[app('http')->getName()] : app()->getRootPath() . 'kernel/tpl/dispatch_jump.tpl', 'view')->assign($result)->header($header);
		} else {
			$response = Response::create($result, $type)->header($header);
		}
		throw new HttpResponseException($response);
	}

	/**
	 * @title error    操作错误跳转的快捷方法
	 *
	 * @param mixed   $msg    提示信息
	 * @param string  $url    跳转的URL地址
	 * @param mixed   $data   返回的数据
	 * @param integer $wait   跳转等待时间
	 * @param array   $header 发送的Header信息
	 *
	 * @return void
	 */
	protected function error($msg = '', string $url = '', $data = '', int $wait = 3, array $header = []) {
		if (is_null($url)) {
			$url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
		} elseif ($url) {
			$url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
		}
		$result = [
			'code' => 500,
			'msg'  => $msg,
			'data' => $data,
			'url'  => $url,
			'wait' => $wait,
		];
		$type   = $this->getResponseType();
		if ('html' == strtolower($type)) {
			$tplArray = $this->app->config->get('jump');
			$response = Response::create(isset($tplArray[app('http')->getName()]) ? $tplArray[app('http')->getName()] : app()->getRootPath() . 'kernel/tpl/dispatch_jump.tpl', 'view')->assign($result)->header($header);
		} else {
			$response = Response::create($result, $type)->header($header);
		}
		throw new HttpResponseException($response);
	}

	/**
	 * @title  result 返回封装后的API数据到客户端
	 *
	 * @param mixed   $data   要返回的数据
	 * @param integer $code   返回的code
	 * @param mixed   $msg    提示信息
	 * @param string  $type   返回数据格式
	 * @param array   $header 发送的Header信息
	 *
	 * @return void
	 */
	protected function result($data, $code = 200, $msg = '', $type = '', array $header = []) {
		$result   = [
			'code' => $code,
			'msg'  => $msg,
			'data' => $data,
		];
		$type     = $type ?: $this->getResponseType();
		$response = Response::create($result, $type)->header($header);
		throw new HttpResponseException($response);
	}

	/**
	 * @title redirect URL重定向
	 *
	 * @param mixed   $url  跳转的URL表达式
	 * @param integer $code http code
	 * @param array   $with 隐式传参
	 *
	 * @return void
	 */
	protected function redirect($url, int $code = 302, array $with = []) {
		$response = Response::create($url, 'redirect')->code($code)->with($with);
		throw new HttpResponseException($response);
	}

	/**
	 * @title  getResponseType 获取当前的response 输出类型
	 * @access protected
	 * @return string
	 */
	protected function getResponseType() {
		return ($this->app->request->isJson() || $this->app->request->isAjax()) ? 'json' : 'html';
	}
}