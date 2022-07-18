<?php
declare (strict_types=1);

namespace app\admin\model;

use kernel\librarys\Encryption;
use think\facade\Session;
use think\Model;

/**
 * Class AuthUser
 *
 * @package kernel\model
 */
class AdminUser extends Model {

	/**
	 * @var bool
	 */
	protected $autoWriteTimestamp = true;

	/**
	 * setPasswordAttr 对密码进行加密
	 *
	 * @author HUA (1291713293@qq.com)
	 * @date   2018/7/27
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setPasswordAttr($value) {
		return Encryption::getInstance()->generatePassword($value);
	}

	/**
	 * login 用户登录操作
	 *
	 * @param string $phone
	 * @param string $password
	 * @param bool   $rememberme
	 */
	public static function login($phone = '', $password = '', $rememberme = false) {
		$password = trim($password);
		$map      = [
			'username' => trim($phone),
			'status'   => 1
		];
		// 查找用户
		$user = self::where($map)->find();

		if (!$user) {
			abort(422, '用户不存在或被禁用');
		} else {
			if (!Encryption::getInstance()->verifyPassword((string)$password, $user['password'])) {
				abort(422, '账号或者密码错误');
			} else {
				$uid = $user['id'];
				// 更新登录信息
				$upData = [
					'last_login_time' => time(),
					'last_login_ip'   => request()->ip(),
					'update_time'     => time(),
					'id'              => $uid
				];
				if (self::update($upData)) {
					// 自动登录
					return self::autoLogin($uid, $rememberme);
				}
			}
		}
		abort(422, '登录信息更新失败，请重新登录！');
	}


	/**
	 * autoLogin 登录
	 *
	 * @author HUA (1291713293@qq.com)
	 * @date   2018/8/10
	 *
	 * @param      $uid
	 * @param bool $rememberme
	 *
	 * @return mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	private static function autoLogin($uid, $rememberme = false) {
		$user = self::where('id', $uid)->find();
		// 记录登录SESSION和COOKIES
		$admin = [
			'uid'             => $user->id,
			'username'        => $user->username,
			'nickname'        => $user->nickname,
			// 'role_name'       => $role['name'],
			'role'            => $user->role,
			'avatar'          => $user->avatar,
			'email'           => $user->email,
			'mobile'          => $user->mobile,
			'status'          => $user->status,
			'create_time'     => $user->create_time,
			'last_login_time' => $user->last_login_time,
		];
		Session::set('_userInfo', $admin);
		return $uid;
	}

	public static function getAdmin(string $field = '') {
		$uInfo = Session::get('_userInfo');
		return $uInfo[$field] ?? $uInfo;
	}
}