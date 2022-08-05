<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\admin\model\AdminUser as UserModel;
use think\facade\Console;

/**
 * Class Index
 * @package app\admin\controller
 */
class Index extends Admin {

	/**
	 * @title  index
	 * @return string
	 */
	public function index() {
		$data['list'] = [];
		return $this->fetch('console', $data);
	}


	/**
	 * @title  profile
	 * @return mixed
	 */
	public function profile() {
		// 保存数据
		if ($this->request->isPost()) {
			$data = $this->request->post();

			// 如果没有填写密码，则不更新密码
			if ($data['password'] == '') {
				unset($data['password']);
			}

			$data['update_time'] = time();
			if (UserModel::update($data, ['id' => getAdmin('uid')])) {
				\think\facade\Session::clear();
				\think\facade\Cache::clear();
				$this->success('编辑成功！', 'close.reload');
			}
			$this->error('ERROR:编辑失败');
		}

		// 获取数据
		$info = UserModel::where('id', getAdmin('uid'))->field('password', true)->find()->toArray();
		$data = $info;
		return $this->fetch('user_form', $data);

	}

	/**
	 * @title clear
	 */
	public function clear() {
		Console::call('clear', ['admin']);
		return $this->success('清除缓存成功', (string)'reload');
	}
}