<?php

namespace kernel\librarys;

/**
 * Class KeywordFilter 敏感词过滤
 * @package tools
 */
class KeywordFilter {

	/**
	 * @var array 敏感词树
	 */
	protected $tree;

	/**
	 * @var array 干扰因子
	 */
	protected $disturbance = [' ', ',', "'", '-', '/', '\\'];

	/**
	 * @var object 单利模式的实例
	 */
	protected static $instance;

	/**
	 * const 搜索时的操作类型
	 */
	const DFA_REPLACE = 1;
	const DFA_MARK    = 2;

	/**
	 * const 匹配模式
	 */
	const DFA_MIN_MATCH = 0;
	const DFA_MAX_MATCH = 1;

	/**
	 * DfaFilter constructor
	 */
	protected function __construct() {

	}

	/**
	 * @title __clone
	 */
	private function __clone() {

	}

	/**
	 * @title  build 创建过滤实例
	 *
	 * @param array $words       敏感词
	 * @param array $disturbance 干扰因子
	 *
	 * @return KeywordFilter|object
	 */
	public static function getInstance(array $words = [], $disturbance = []) {
		if (!self::$instance) {
			self::$instance = new self();
		}

		self::$instance->addSensitives($words);
		self::$instance->addDisturbance($disturbance);
		return self::$instance;
	}

	/**
	 * @title addDisturbance 添加干扰因子
	 *
	 * @param array|string $disturbance 干扰因子
	 */
	public function addDisturbance($disturbance) {
		if (!is_array($disturbance)) {
			$disturbance = [$disturbance];
		}
		$this->disturbance = array_merge($this->disturbance, $disturbance);
	}

	/**
	 * @title importSensitiveFile 导入敏感词文件
	 *
	 * @param string $filename  文件名
	 * @param string $delimiter 内容分割符
	 */
	public function importSensitiveFile(string $filename, $delimiter = PHP_EOL) {
		if (!$filename || !file_exists($filename)) {
			return false;
		}

		if ($delimiter !== PHP_EOL) {
			$words = explode($delimiter, file_get_contents($filename));
		} else {
			//一行一个敏感词
			foreach ($this->readLineFromFile($filename) as $word) {
				$this->add($word);
			}
			return false;
		}

		$this->addSensitives($words);
	}

	/**
	 * @title  readLineFromFile 从文件中一行一行的读
	 *
	 * @param $filename
	 *
	 * @return \Generator
	 */
	protected function readLineFromFile($filename) {
		$f = fopen($filename, 'r');
		while (!feof($f)) {
			yield fgets($f);
		}
		fclose($f);
	}

	/**
	 * @title addSensitives 批量添加敏感词数组
	 *
	 * @param array $words
	 */
	public function addSensitives(array $words) {
		if (count($words) == 0) {
			return;
		}

		foreach ($words as $word) {
			$this->add($word);
		}
	}

	/**
	 * @title add 添加单个敏感词
	 *
	 * @param string $word
	 */
	protected function add(string $word) {
		$len = mb_strlen($word, 'utf-8');
		if ($len == 0) {
			return;
		}

		$tree = &$this->tree;
		for ($i = 0; $i < $len; $i++) {
			$char = mb_substr($word, $i, 1, 'utf-8');
			if (!isset($tree['map'][$char])) {
				$tree['map'][$char] = ['map' => []];
			}
			$tree = &$tree['map'][$char];
		}
		//最后一个字符节点标记状态为结束
		$tree['end'] = true;
	}

	/**
	 * @title  getKeywordTree 获取敏感词树
	 * @return array
	 */
	public function getKeywordTree() {
		return $this->tree;
	}

	/**
	 * @title  getDisturbance 获取干扰因子
	 * @return array
	 */
	public function getDisturbance() {
		return array_flip($this->disturbance);
	}

	/**
	 * @title  isKey 是否是完整敏感词
	 *
	 * @param $word
	 *
	 * @return bool
	 */
	public function isKey($word) {
		$len = mb_strlen($word, 'utf-8');
		if ($len == 0) {
			return false;
		}
		$disturbance = $this->getDisturbance();
		$tree        = $this->getKeywordTree();
		for ($i = 0; $i < $len; $i++) {
			$char = mb_substr($word, $i, 1, 'utf-8');
			//跳过干扰因子
			if (isset($disturbance[$char])) {
				continue;
			}
			if (!isset($tree['map'][$char])) {
				return false;
			}
			$tree = $tree['map'][$char];
		}
		//如果遍历匹配完后，最后一个字符是最后节点，则匹配成功
		if (isset($tree['end']) && ($tree['end'] === true)) {
			return true;
		}
		return false;
	}

	/**
	 * @title  check 是否包含敏感词
	 *
	 * @param string $content 文本内容
	 *
	 * @return bool 包含敏感词返回 true
	 */
	public function check($content) {
		$len = mb_strlen($content, 'utf-8');
		if ($len == 0) {
			return false;
		}
		$disturbance = $this->getDisturbance();
		$tree        = $this->getKeywordTree();

		for ($i = 0; $i < $len; $i++) {
			//跳过干扰因子
			$tmpChar = mb_substr($content, $i, 1, 'utf-8');
			if (isset($disturbance[$tmpChar])) {
				continue;
			}
			for ($j = $i; $j < $len; $j++) {
				$char = mb_substr($content, $j, 1, 'utf-8');
				//跳过干扰因子
				if (isset($disturbance[$char])) {
					continue;
				}

				if (!isset($tree['map'][$char])) {
					break;
				}
				$tree = $tree['map'][$char];
				//是否匹配某个词完成
				if (isset($tree['end']) && ($tree['end'] === true)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @title  filter 过滤敏感词
	 *
	 * @param string $content   文本内容
	 * @param int    $matchMode 是否是最小匹配原则
	 * @param string $replace   替换文本
	 *
	 * @return string
	 */
	public function filter($content, $replace = '*', $matchMode = self::DFA_MIN_MATCH) {
		$option = [
			'action'    => self::DFA_REPLACE,
			'repalce'   => $replace,
			'matchMode' => $matchMode
		];

		return $this->search($content, $option);
	}

	/**
	 * @title  mark 自定义符号标记敏感词
	 *
	 * @param string $content   文本内容
	 * @param array  $mark      标记符
	 * @param int    $matchMode 是否为最小匹配模式
	 *
	 * @return string
	 */
	public function mark($content, $mark = ['<b>', '</b>'], $matchMode = self::DFA_MIN_MATCH) {
		$option = [
			'action'    => self::DFA_MARK,
			'mark'      => $mark,
			'matchMode' => $matchMode
		];

		return $this->search($content, $option);
	}

	/**
	 * @title  search 查找敏感词，并作相应操作
	 *
	 * @param $content
	 * @param $option
	 *
	 * @return string
	 */
	protected function search($content, $option) {
		$len = mb_strlen($content, 'utf-8');
		if ($len == 0) {
			return '';
		}
		$result      = $content;
		$disturbance = $this->getDisturbance();
		$tree        = $this->getKeywordTree();
		$matchCount  = 0;
		for ($i = 0; $i < $len; $i++) {
			//跳过干扰因子
			$tmpChar = mb_substr($content, $i, 1, 'utf-8');
			if (isset($disturbance[$tmpChar])) {
				continue;
			}

			$matchLen = 0;
			$isMatch  = false;
			$endIndex = $i;

			for ($j = $i; $j < $len; $j++) {
				$char = mb_substr($content, $j, 1, 'utf-8');
				//跳过干扰因子
				if (isset($disturbance[$char])) {
					//如果已经进入某个词的匹配验证，干扰因子占位长度也算进去
					if ($matchLen > 0) {
						$matchLen++;
					}
					continue;
				}

				if (!isset($tree['map'][$char])) {
					break;
				}

				$tree = $tree['map'][$char];
				$matchLen++;

				//是否匹配某个词完成
				if (isset($tree['end']) && ($tree['end'] === true)) {
					$endIndex = $j;
					//最小匹配，直接下一轮匹配
					if ($option['matchMode'] == self::DFA_MIN_MATCH) {
						$isMatch = true;
						$matchCount++;
						//后续遍历跳过已匹配的索引
						$i = $j;
						break;
					}

					//最大匹配时，在本轮第一次有词语匹配到时匹配次数才增加，避免“中国人”算两次(敏感词有“中国”,“中国人”)
					if ($isMatch === false) {
						$matchCount++;
						//后续遍历跳过已匹配的索引
						$i = $j;
					}
					$isMatch = true;
				}
			}

			if ($isMatch && $matchLen > 0) {
				//执行的操作
				$result = $this->action($result, $endIndex, $matchLen, $matchCount, $option);
			}
		}
		return $result;
	}

	/**
	 * @title action 执行匹配后的操作
	 *
	 * @param string $content    文本内容
	 * @param int    $endIndex   匹配到的结束位置
	 * @param int    $len        匹配到的长度
	 * @param int    $matchCount 匹配到的次数
	 * @param array  $option     操作参数
	 *
	 * @return string 操作后的文本内容
	 */
	protected function action($content, $endIndex, $len, $matchCount, $option) {
		switch ($option['action']) {
			case self::DFA_MARK:
				$realEndIndex = $endIndex;
				if ($matchCount > 1) {
					//计算过滤后当前匹配真正的索引位置
					$realEndIndex = (mb_strlen($option['mark'][0], 'utf-8') + mb_strlen($option['mark'][1], 'utf-8')) * ($matchCount - 1) + $endIndex;
				}
				$content = mb_substr($content, 0, $realEndIndex + 1 - $len, 'utf-8') . $option['mark'][0] . mb_substr($content, $realEndIndex + 1 - $len, $len, 'utf-8') . $option['mark'][1] . mb_substr($content, $realEndIndex + 1, null, 'utf-8');
				break;

			case self::DFA_REPLACE:
			default:
				$content = mb_substr($content, 0, $endIndex + 1 - $len, 'utf-8') . str_pad('', $len, $option['repalce']) . mb_substr($content, $endIndex + 1, null, 'utf-8');
		}
		return $content;
	}

}
