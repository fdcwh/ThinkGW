<?php
declare (strict_types=1);

namespace kernel\librarys;

use kernel\exception\ApiException;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use think\facade\Request;

/**
 * Class SysToken
 * @package kernel\librarys
 */
class SysToken {

	/**
	 * @var string
	 */
	public $alg = 'HS256';

	/**
	 * @var string
	 */
	private $key = '__YGL-CRM__';

	/**
	 * @var string
	 */
	private $storageTag = '_crm_token';

	/**
	 * @var null
	 */
	protected static $instance = null;

	private function __construct() {

	}

	/**
	 * @title __clone
	 */
	private function __clone() {
	}

	/**
	 * @title  getInstance
	 * @return SysToken|null
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @title  getParse token
	 * @return mixed|string
	 */
	public function getParse() {
		$header = Request::instance()->server('HTTP_AUTHORIZATION') ?: Request::instance()->server('REDIRECT_HTTP_AUTHORIZATION');
		if ($header && preg_match('/Bearer\s*(\S+)\b/i', $header, $matches)) {
			return $matches['1'];
		}
		return $header;
	}

	/**
	 * @title  getJwtToken 签发token
	 *
	 * iss: jwt签发者
	 * sub: jwt所面向的用户
	 * aud: 接收jwt的一方
	 * exp: jwt的过期时间，这个过期时间必须要大于签发时间
	 * nbf: 定义在什么时间之前，该jwt都是不可用的.
	 * iat: jwt的签发时间
	 * jti: jwt的唯一身份标识，主要用来作为一次性token,从而回避重放攻击。
	 *
	 * @param array  $data
	 * @param string $key
	 * @param string $aud
	 *
	 * @return string
	 */
	public function getToken(array $data = [], string $key = '', $aud = '') {
		$time    = time();
		$payload = [
			'iss'  => 'YGL',
			'aud'  => $aud,
			'iat'  => $time, //签发时间
			'nbf'  => $time, //在什么时间之后该jwt才可用
			// 'exp'  => $time + (3600 * 24 * 7), //过期时间
			'data' => $data
		];
		return JWT::encode($payload, $key ?: $this->key, $this->alg);
	}

	/**
	 * @title  decodeToken
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function decodeToken($token, $key = '') {
		if (empty($token)) {
			throw new ApiException(401, '未授权');
		}
		try {
			//
			// 当前时间减去60，把时间留点余地
			JWT::$leeway = 60;
			$decoded     = JWT::decode($token, $key ?: $this->key, [$this->alg]);
			$data        = (array)$decoded;
			$data        = (array)$data['data'];
			if (empty($data['uid']) || empty($data['company_id'])) {
				throw new ApiException(401, '登陆信息异常！');
			}
			return $data;
		} catch (SignatureInvalidException $e) {  //签名不正确
			throw new ApiException(401, $e->getMessage());
		} catch (BeforeValidException $e) {
			// 签名在某个时间点之后才能用
			// apiAbort($e->getMessage(), 1002);
		} catch (ExpiredException $e) {
			// token过期
			throw new ApiException(401, $e->getMessage());

		} catch (Exception $e) {
			//其他错误
			throw new ApiException(401, $e->getMessage());
		}
		throw new ApiException(401, 'token 异常');
	}

	/**
	 * @title  getTokenInfo
	 * @return array|string
	 * @throws ApiException
	 */
	public function getTokenInfo($f) {
		$token = $this->getParse();
		//
		if (empty($token)) {
			return '';
		}
		try {
			// 当前时间减去60，把时间留点余地
			JWT::$leeway = 60;
			$decoded     = JWT::decode($token, $this->key, [$this->alg]);
			$data        = (array)$decoded;
			$data        = (array)$data['data'];

		} catch (SignatureInvalidException $e) {  //签名不正确
		} catch (BeforeValidException|Exception|ExpiredException $e) {
		}

		return $data[$f] ?? '';
	}
}