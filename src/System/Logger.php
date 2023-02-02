<?php

namespace Resolver\System;

class Logger {

	private const MAX_BUFFER_SIZE = 1000;
	
	public Array  $buffer;
	public int    $bufferSize;

	
	public function __construct(int $bufferSize = -1) {
	
		$this->resetBuffer();
		$this->setBufferSize(
			($bufferSize == -1) ? self::MAX_BUFFER_SIZE : $bufferSize
		);
	
	}
	
	public function write(String $expression): void {
	
		$this->buffer[] = $expression;
	
	}
	
	public function autoWrite(String $expression, String $file): void {
	
		$this->write($expression);
		
		if (count($this->buffer) >= $this->bufferSize) {
			$result = $this->writeBufferToFile($file);
			
			if ($result) {
				$this->resetBuffer();
			} else {
				throw new \Exception("Failed to write to file");
			}
			
		} 
	
	}
	
	public function writeBufferToFile(String $file): bool {
	
		$contents = implode("\r\n", $this->buffer)."\r\n";
		$bytes = file_put_contents($file, $contents, FILE_APPEND);
		
		return ($bytes) ? true : false;
	
	}
	
	public function resetBuffer(): void {
	
		$this->buffer = [];
	
	}
	
	public function setBufferSize(int $size): void {
	
		if ($size < 1 || $size > self::MAX_BUFFER_SIZE) {
			$description = sprintf("Buffer size must be between 1 and %d", self::MAX_BUFFER_SIZE);
			throw new \Exception($description);
		}
		$this->bufferSize = $size;
	
	}
	
	public function getBufferSize(): int {
	
		return $this->bufferSize;
	
	}
	
	public static function print(String $expression, bool $error = false): void {
	
		$color = ($error) ? 31 : 34;
		printf("\033[0;%sm%s\033[0m\r\n", $color, $expression);
	
	}

};
