<?php

namespace Resolver\Exception;

class SocketException extends \Exception {

	public function __construct(String $description, $exceptionCode = 0) {

		$description = sprintf("Socket Error: %s", $description);
		parent::__construct($description, $exceptionCode);
	
	}

};
