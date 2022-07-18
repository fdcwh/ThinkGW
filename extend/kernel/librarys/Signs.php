<?php
declare (strict_types=1);

namespace kernel\librarys;

use think\facade\Config;

/**
 * Class Signs 接口签名
 * @package libs
 */
class Signs {

	/**
	 * @var string  签名  key
	 */
	protected static $key = '__HYJK__';

	/**
	 * @title  getSign
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public static function getSign($data) {
		return self::ruleData($data);
	}

	/**
	 * @title  verifySign
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public static function verifySign($data) {

		// 开关
		if (Config::get('system.signs') == false) {
			return true;
		}

		// 验证参数中是否有签名
		if (!isset($data['sign']) || !$data['sign']) {
			abort(2001, '发送的数据签名不存在');
		}

		/*if (!isset($data['timestamp']) || !$data['timestamp']) {
			abort(2002, '发送的数据参数不合法');
		}*/

		// 验证请求， 10分钟失效
		/*if (time() - $data['timestamp'] > 600) {
			abort(2002, '验证失效， 请重新发送请求');
		}*/
		$sign = $data['sign'];
		unset($data['sign']);
		if ($sign == self::ruleData($data)) {
			return true;
		}
		abort(2003, '请求不合法');
	}

	/**
	 * @title  ruleData 生成规则
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private static function ruleData($data) {
		$data = array_filter($data);
		// 对数组的值按key排序
		ksort($data);
		// 生成url的形式 urldecode 转码
		$params = urldecode(http_build_query($data));
		// 生成 sign 转大写
		return strtoupper(md5($params . '&key=' . self::$key));
	}

}