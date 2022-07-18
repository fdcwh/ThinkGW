<?php
declare (strict_types=1);

namespace kernel\middleware;

use Closure;

/**
 * Class AllowCrossDomain   跨域请求中间件
 * @package kernel\middleware
 */
class AllowCrossDomain {

	/**
	 * @title  handle
	 *
	 * @param         $request
	 * @param Closure $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$header = [
			'Access-Control-Allow-Origin'      => '*',
			'Access-Control-Allow-Credentials' => 'true',
			'Access-Control-Max-Age'           => 1800,
			'Content-Type'                     => 'application/json;charset=utf-8',
			'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
			'Access-Control-Allow-Headers'     => 'Authorization,GAME-ID, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
		];
		// 预检
		if ($request->isOptions()) {
			return $next($request)::create((object)[], 'json')->header($header);
		}
		return $next($request)->header($header);
	}

}