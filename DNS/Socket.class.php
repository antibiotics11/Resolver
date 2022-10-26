<?php

namespace Resolver;

class Socket {


	private ?\Socket $dns_socket = null;

	private ?InetAddress $address = null;
	private int $port = -1;
	
	
	public function create(InetAddress $address, int $port): void {
	
		$this->address = $address;
		if (Socket::is_valid_port($port)) {
			$this->port = $port;
		} else {
			throw new \Exception("Invalid port number \"".$port."\"");
		}
		
		$domain = $this->address->get_version();
		$type = \SOCK_DGRAM;
		$protocol = \SOL_UDP;
		
		$this->dns_socket = socket_create($domain, $type, $protocol);
		if ($e = socket_last_error()) {
			throw new \Exception(
				"Failed to create socket: "
				.socket_strerror($e)
			);
		}
		
		$address = $this->address->get_address();
		$port = $this->port;
		
		socket_bind($this->dns_socket, $address, $port);
		if ($e = socket_last_error()) {
			throw new \Exception(
				"Failed to bind to socket: "
				.socket_strerror($e)
			);
		}
	
	}
	
	public function listen(\Closure $process): void {
	
		if ($this->dns_socket == null) {
			throw new \Exception("Socket is not set.");
		}
		
		while (socket_recvfrom($this->dns_socket, $data, 512, 0, $ip, $port)) {
		
			$data = $process($data, $ip, $port);
			socket_sendto($this->dns_socket, $data, 512, 0, $ip, $port);
			
		}
	
	}
	
	public function get_address(): ?InetAddress {
		return $this->address;
	}
	
	public function get_port(): int {
		return $this->port;
	}
	
	public static function is_valid_port(int $port): bool {
		return ($port >= 1 && $port <= 65535);
	}
	
	
};
