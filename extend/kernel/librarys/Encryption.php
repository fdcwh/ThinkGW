<?php
declare (strict_types=1);

namespace kernel\librarys;

/**
 * Class Encryption
 * @package kernel\librarys
 */
class Encryption {

	/**
	 * @var Encryption
	 */
	private static $instance;

	/**
	 * @var string
	 */
	private $key = '_PWD__';

	/**
	 *
	 */
	private function __construct() {

	}

	/**
	 * @title __clone
	 */
	private function __clone() {

	}

	/**
	 * @title  getInstance
	 * @return Encryption
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @title  generatePassword
	 *
	 * @param string|int $str
	 *
	 * @return string
	 */
	public function generatePassword($str = ''): string {
		return hash('sha256', md5(mb_substr(md5($str), 8, 16) . $this->key));
	}

	/**
	 * @title  verifyPassword
	 *
	 * @param string|int $str
	 * @param string     $password
	 *
	 * @return bool
	 */
	public function verifyPassword($str = '', string $password = ''): bool {
		if ($this->generatePassword($str) === $password) {
			return true;
		}
		return false;
	}

	/**
	 * @title  strEncode
	 *
	 * @param        $string  明文
	 * @param string $key     密匙
	 *
	 * @return false|string
	 */
	public function strEncode($string, string $key = '') {
		return $this->authcode($string, 'ENCODE', $key);
	}

	/**
	 * @title  strDecode
	 *
	 * @param        $string  密文
	 * @param string $key     密匙
	 *
	 * @return false|string
	 */
	public function strDecode($string, string $key = '') {
		return $this->authcode($string, 'DECODE', $key);
	}

	/**
	 * @title  authcode 加密/解密操作
	 *
	 * @param        $string    字符串，明文或密文
	 * @param string $operation DECODE表示解密，其它表示加密
	 * @param string $key       密匙
	 * @param int    $expiry    密文有效期
	 *
	 * @return false|string
	 */
	private function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		// 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
		$ckey_length = 4;
		$key         = md5($key ?: $this->key);
		// 密匙a会参与加解密
		$keya = md5(substr($key, 0, 16));
		// 密匙b会用来做数据完整性验证
		$keyb = md5(substr($key, 16, 16));
		// 密匙c用于变化生成的密文
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr((string)$string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
		// 参与运算的密匙
		$cryptkey   = $keya . md5($keya . $keyc);
		$key_length = strlen($cryptkey);
		// 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
		// 解密时会通过这个密匙验证数据完整性
		// 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
		$string        = $operation == 'DECODE' ? base64_decode(substr((string)$string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
		$string_length = strlen($string);
		$result        = '';
		$box           = range(0, 255);
		$rndkey        = [];
		// 产生密匙簿
		for ($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		// 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
		for ($j = $i = 0; $i < 256; $i++) {
			$j       = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp     = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		// 核心加解密部分
		for ($a = $j = $i = 0; $i < $string_length; $i++) {
			$a       = ($a + 1) % 256;
			$j       = ($j + $box[$a]) % 256;
			$tmp     = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			// 从密匙簿得出密匙进行异或，再转成字符
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if ($operation == 'DECODE') {
			// 验证数据有效性，请看未加密明文的格式
			if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			// 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
			// 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
			return $keyc . str_replace('=', '', base64_encode($result));
		}
	}

}
