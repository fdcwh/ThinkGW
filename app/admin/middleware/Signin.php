<?php
declare (strict_types=1);

namespace app\admin\middleware;

use app\admin\model\auth\AdminMenu as AdminMenuModel;
use think\facade\Cookie;
use think\facade\View;

/**
 * Class Signin
 * @package app\backend\middleware
 */
class Signin {

	/**
	 * handle
	 *
	 * @param          $request
	 * @param \Closure $next
	 */
	public function handle($request, \Closure $next) {
		if (!getAdmin()) {
			return redirect((string)url('login/index'));
		}
		// 菜单
		$response = $next($request);
		// 添加操作记录
		// AdminLog::addAutohLog();
		return $response;
	}

}