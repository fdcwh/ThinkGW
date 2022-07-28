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
		$this->setMenuData($request);
		return $next($request);
	}

	/**
	 * @title setMenuData
	 *
	 * @param $request
	 */
	private function setMenuData($request) {
		$controller             = $request->controller();
		$initData['top_middle'] = '';
		//
		$initData['_dialog']        = $request->param('_dialog', 0);
		$initData['_staticVersion'] = date('Ymd');
		// 读取菜单信息 ajax 以及部分控制器不需要执行
		if (!$initData['_dialog'] && !$request->isAjax()) {
			$initData['_leftMenu']    = self::getMenu();
			$initData['_getLocation'] = [];
			// 选中当前
			$currentIds             = array_values(array_column($initData['_getLocation'], 'id', 'id'));
			$initData['_current_0'] = $currentIds['0'] ?? 0;
			$initData['_current_1'] = $currentIds['1'] ?? 0;
		}
		View::assign('_initData', $initData);
	}

	/**
	 * @title  getMenu
	 * @return
	 */
	private static function getMenu() {
		return [
			[
				'id'        => 1,
				'title'     => '首页',
				'url_value' => '/admin/index/index',
				'icon'      => '',
				'url_type'  => 0,
			],
			[
				'id'        => 2,
				'title'     => '对话',
				'url_value' => '/admin/index/index',
				'icon'      => '',
				'url_type'  => 0,
			],
			[
				'id'        => 3,
				'title'     => '设置',
				'url_value' => '/admin/Setup/index',
				'icon'      => '',
				'url_type'  => 0,
			]
		];
	}
}