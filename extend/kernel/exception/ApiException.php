<?php

declare (strict_types=1);

namespace kernel\exception;

use Exception;

/**
 * Class ApiException
 * @package app\api\exception
 */
class ApiException extends \RuntimeException {

	public function __construct(int $code = 500, string $message = '', Exception $previous = null, array $headers = []) {
		parent::__construct($message, $code, $previous);
	}
}
