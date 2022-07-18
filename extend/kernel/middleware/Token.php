<?php
declare (strict_types=1);

namespace kernel\middleware;

use kernel\librarys\SysToken;

/**
 * Class Token
 * @package kernel\middleware
 */
class Token {

	/**
	 * @title  handle
	 *
	 * @param          $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function handle($request, \Closure $next) {
		$info               = SysToken::getInstance()->decodeToken(SysToken::getInstance()->getParse());
		$request->userId    = $info['uid'];
		$request->companyId = $info['company_id'];
		return $next($request);
	}
}