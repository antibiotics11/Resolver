<?php

namespace Resolver\Exception;

class SocketIOException extends \Exception {

	public int    $socketErrorCode;
	public String $description;

	public function __construct(int $socketErrorCode, int $exceptionCode = 0) {
		
		$this->socketErrorCode = $socketErrorCode;
		$description = socket_strerror($socketErrorCode);
		$this->description = sprintf("Socket I/O Error: %s", $description);
		parent::__construct($this->description, $exceptionCode);
	
	}

};
