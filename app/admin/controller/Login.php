<?php

declare (strict_types=1);

namespace app\admin\controller;

use app\admin\model\AdminUser as AdminUserModel;
use think\captcha\facade\Captcha;

/**
 * Class Login  用户登录控制器
 * @package app\admin\controller
 */
class Login extends Admin {

	/**
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * @title  index    登录页面/登录操作
	 * @return string|void
	 */
	public function index() {
		if (getAdmin()) {
			return $this->redirect((string)url('Index/index'));
		}
		//
		if ($this->request->isPost()) {
			$data = $this->request->post();
			// 数据验证
			$this->validate($data,
				[
					'username' => 'require',
					'password' => 'require',
					'captcha'  => 'require',
				],
				[
					'username.require' => '用户名不能为空！',
					'password.require' => '密码不能为空！',
					'captcha.require'  => '验证码不能为空！',
				]
			);

			if (!captcha_check($data['captcha'])) {
				$this->error('图形验证码异常！');
			}

			$uinfo = AdminUserModel::login($data['username'], $data['password']);
			if ($uinfo) {
				$this->success('登录成功！页面即将跳转~~', (string)url("index/index"));
			}
			$this->error('登录失败！');
		}
		return $this->fetch('login');
	}

	/**
	 * @title  getCaptchaSrc
	 * @return \think\Response
	 */
	public function getCaptchaSrc() {
		return Captcha::create('admin_login');
	}

	/**
	 * @title  logout
	 * @return \think\response\Redirect
	 */
	public function logout() {
		\think\facade\Session::clear();
		\think\facade\Cache::clear();
		return $this->redirect((string)url('login/index'));
	}
}