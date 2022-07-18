<?php
declare (strict_types=1);

namespace app\admin\middleware;

use think\facade\Cookie;
use think\facade\View;

/**
 * Class Init
 * @package app\admin\middleware
 */
class Init {


	/**
	 * @title  handle
	 *
	 * @param          $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 */
	public function handle($request, \Closure $next) {
		return $next($request);
	}

}