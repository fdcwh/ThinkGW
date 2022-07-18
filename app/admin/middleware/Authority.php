<?php

declare (strict_types=1);

namespace app\admin\middleware;


/**
 * Class Authority
 * @package app\admin\middleware
 */
class Authority {

	/**
	 * @title  handle
	 *
	 * @param          $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 * @throws \think\Exception
	 */
	public function handle($request, \Closure $next) {
		return $next($request);
	}


}