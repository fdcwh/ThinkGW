<?php
// 应用公共文件

use think\facade\Session;

if (!function_exists('getAdmin')) {
	/**
	 * @title  getAdmin
	 *
	 * @param string $field
	 *
	 * @return mixed
	 */
	function getAdmin(string $field = '') {
		$uInfo = Session::get('_userInfo');
		return $uInfo[$field] ?? $uInfo;
	}
}

if (!function_exists('checkAuth')) {
	/**
	 * @title  checkAuth
	 *
	 * @param string $current
	 *
	 * @return bool
	 * @throws \think\Exception
	 */
	function checkAuth($current = '') {
		return \app\admin\model\auth\AdminRole::checkAuth(strtolower($current), true);
	}
}