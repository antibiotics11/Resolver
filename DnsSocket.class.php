<?php

namespace Resolver;

use \Resolver\InetAddress;

class DnsSocket {

	private ?InetAddress $address = null;
	private int $port = -1;

	private ?\Socket $dns_socket = null;


	public function create(InetAddress $address, int $port) {

		$this->address = $address;
		$this->port = $port;

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

		socket_bind($this->dns_socket, $address->get_address(), $this->port);
		if ($e = socket_last_error()) {
			throw new \Exception(
				"Failed to bind to socket: "
				.socket_strerror($e)
			);
		}

	}


	public function listen(\Closure $process): void {
		
		if ($this->dns_socket == null) {
			throw new \Exception(
				"Socket is not set."
			);
		}

		while (socket_recvfrom($this->dns_socket, $question, 512, 0, $ip, $port)) {
			
			$pid = pcntl_fork();
			if ($pid == 0) {

				$answer = $process($question, $ip, $port);
				socket_sendto($this->dns_socket, $answer, 512, 0, $ip, $port);
			
			} else if ($pid == -1) {
				throw new \Exception(
					"Failed to fork process"
				);
			}
		
		}

	}

	public function get_address(): ?InetAddress {
		return $this->address;
	}

	public function get_port(): int {
		return $this->port;
	}

	public function get_dns_socket(): ?\Socket {
		return $this->dns_socket;
	}

};
