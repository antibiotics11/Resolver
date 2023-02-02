<?php

namespace Resolver\Server;
use Resolver\Network\InetAddress;
use Resolver\Exception\{SocketException, SocketIOException};

declare(ticks = 1);

class DatagramServer {

	public const SOCKET_BUFFER_SIZE = 512;

	private ?\Socket     $socket;
	private ?InetAddress $address;
	private int          $port;
	
	public function __construct() {
	
		$this->socket  = null;
		$this->address = null;
		$this->port    = -1;
		
		pcntl_signal(SIGINT,  [$this, "terminate"]);
		pcntl_signal(SIGTSTP, [$this, "terminate"]);
	
	}
	
	public function getAddress(): ?InetAddress {
	
		return $this?->address;
		
	}

	public function getPort(): int {
	
		return $this->port;
		
	}

	public function create(InetAddress $address, int $port): void {
	
		if (DatagramServer::isValidPortNumber($port)) {
			$this->port = $port;
		} else {
			throw new \Exception(
				sprintf("Invalid port number %d", $port)
			);
		}

		$this->address = $address;

		$this->socket = socket_create(
			$this->address->getFamily(), \SOCK_DGRAM, \SOL_UDP
		);
		if ($e = socket_last_error()) {
			throw new SocketException(socket_strerror($e));
		}

		socket_bind(
			$this->socket, 
			$this->address->getAddress(), 
			$this->port
		);
		if ($e = socket_last_error()) {
			throw new SocketException(socket_strerror($e));
		}
		
	}
	
	public function block(): bool {
	
		return socket_set_block($this->socket);
		
	}
	
	public function nonblock(): bool {
	
		return socket_set_nonblock($this->socket);
		
	}

	public function listen(?\Closure $process = null): void {

		if ($this->socket == null) {
			throw new SocketException("Socket is not set.");
		}
		
		if ($process == null) {
			$process = fn($buffer, $ip, $port) => $buffer;
		}

		while (true) {
		
			$bytes = socket_recvfrom(
				$this->socket, 
				$buffer, 
				self::SOCKET_BUFFER_SIZE, 
				0,
				$remoteIp, $remotePort
			);
			
			if ($bytes) {

				$buffer = $process($buffer, $remoteIp, $remotePort);
				$bytes = socket_sendto(
					$this->socket, 
					$buffer,
					self::SOCKET_BUFFER_SIZE,
					0,
					$remoteIp, $remotePort
				);
			
			}
		
		}
	
	}
	
	public function close(): void {
	
		socket_close($this->socket);
		
	}
	
	public function terminate(): void {
	
		exit(0);
		
	}

	public static function isValidPortNumber(int $port): bool {
	
		return ($port >= 1 && $port <= 65535);
		
	}

};
