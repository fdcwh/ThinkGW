<?php

declare (strict_types=1);

namespace kernel\exception;

use Exception;

/**
 * Class SysException
 * @package kernel\exception
 */
class SysException extends \RuntimeException {

	/**
	 * @param int            $code
	 * @param string         $message
	 * @param Exception|null $previous
	 * @param array          $headers
	 */
	public function __construct(int $code = 500, string $message = '', Exception $previous = null, array $headers = []) {
		parent::__construct($message, $code, $previous);
	}
}
