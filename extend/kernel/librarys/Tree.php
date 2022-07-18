<?php
declare (strict_types=1);

namespace kernel\librarys;

/**
 * Class Tree
 * @package kernel\librarys
 */
class Tree {

	/**
	 * @var array 配置参数
	 */
	protected static $config = [
		'id'    => 'id',    // id名称
		'pid'   => 'pid',   // pid名称
		'title' => 'title', // 标题名称
		'child' => 'child', // 子元素键名
		'html'  => '┝ ',   // 层级标记
		'step'  => 4,       // 层级步进数量
	];
	/**
	 * @var Tree
	 */
	private static $instance;

	/**
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		self::$config = array_merge(self::$config, $config);
	}

	/**
	 * @title __clone
	 */
	private function __clone() {

	}

	/**
	 * @title  config   配置参数
	 *
	 * @param array $config
	 *
	 * @return Tree|object
	 */
	public static function config(array $config = []) {
		if (!empty($config)) {
			$config = array_merge(self::$config, $config);
		}
		if (is_null(self::$instance)) {
			self::$instance = new static($config);
		}
		return self::$instance;
	}

	/**
	 * 将数据集格式化成层次结构
	 *
	 * @param array/object $lists 要格式化的数据集，可以是数组，也可以是对象
	 * @param int $pid        父级id
	 * @param int $max_level  最多返回多少层，0为不限制
	 * @param int $curr_level 当前层数
	 *
	 * @author 6
	 * @return array
	 */
	public static function toLayer($lists = [], int $pid = 0, int $max_level = 0, int $curr_level = 0) {
		$trees = [];
		$lists = array_values($lists);
		foreach ($lists as $key => $value) {
			if ($value[self::$config['pid']] == $pid) {
				if ($max_level > 0 && $curr_level == $max_level) {
					return $trees;
				}
				unset($lists[$key]);
				$child = self::toLayer($lists, $value[self::$config['id']], $max_level, $curr_level + 1);
				if (!empty($child)) {
					$value[self::$config['child']] = $child;
				}
				$trees[] = $value;
			}
		}
		return $trees;
	}

	/**
	 * 将数据集格式化成列表结构
	 *
	 * @param array|object $lists 要格式化的数据集，可以是数组，也可以是对象
	 * @param integer      $pid   父级id
	 * @param integer      $level 级别
	 *
	 * @return array 列表结构(一维数组)
	 */
	public static function toList($lists = [], $pid = 0, $level = 0) {
		if (is_array($lists)) {
			$trees = [];
			foreach ($lists as $key => $value) {
				if ($value[self::$config['pid']] == $pid) {
					$title_prefix           = str_repeat("&nbsp;", $level * self::$config['step']) . self::$config['html'];
					$value['level']         = $level + 1;
					$value['title_prefix']  = $level == 0 ? '' : $title_prefix;
					$value['title_display'] = $level == 0 ? $value[self::$config['title']] : $title_prefix . $value[self::$config['title']];
					$trees[]                = $value;
					unset($lists[$key]);
					$trees = array_merge((array)$trees, self::toList($lists, $value[self::$config['id']], $level + 1));
				}
			}
			return $trees;
		} else {
			foreach ($lists as $key => $value) {
				if ($value[self::$config['pid']] == $pid && is_object($value)) {
					$title_prefix           = str_repeat("&nbsp;", $level * self::$config['step']) . self::$config['html'];
					$value['level']         = $level + 1;
					$value['title_prefix']  = $level == 0 ? '' : $title_prefix;
					$value['title_display'] = $level == 0 ? $value[self::$config['title']] : $title_prefix . $value[self::$config['title']];
					$lists->offsetUnset($key);
					$lists[] = $value;
					self::toList($lists, $value[self::$config['id']], $level + 1);
				}
			}
			return $lists;
		}
	}

	/**
	 * 根据子节点返回所有父节点
	 *
	 * @param array  $lists 数据集
	 * @param string $id    子节点id
	 *
	 * @return array
	 */
	public static function getParents($lists = [], $id = '') {
		$trees = [];
		foreach ($lists as $value) {
			if ($value[self::$config['id']] == $id) {
				$trees[] = $value;
				$trees   = array_merge(self::getParents($lists, $value[self::$config['pid']]), (array)$trees);
			}
		}
		return $trees;
	}

	/**
	 * 获取所有子节点id
	 *
	 * @param array  $lists 数据集
	 * @param string $pid   父级id
	 *
	 * @return array
	 */
	public static function getChildsId(array $lists = [], $pid = 0): array {
		$result = [];
		foreach ($lists as $value) {
			if ($value[self::$config['pid']] == $pid) {
				$result[] = $value[self::$config['id']];
				$result   = array_merge((array)$result, self::getChildsId($lists, $value[self::$config['id']]));
			}
		}
		return $result;
	}

	/**
	 * 获取所有子节点
	 *
	 * @param array  $lists 数据集
	 * @param string $pid   父级id
	 *
	 * @return array
	 */
	public static function getChilds($lists = [], $pid = '') {
		$result = [];
		foreach ($lists as $value) {
			if ($value[self::$config['pid']] == $pid) {
				$result[] = $value;
				$result   = array_merge((array)$result, self::getChilds($lists, $value[self::$config['id']]));
			}
		}
		return $result;
	}
}