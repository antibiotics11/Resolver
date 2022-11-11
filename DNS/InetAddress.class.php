<?php

namespace Resolver;

class InetAddress {

	
	private String  $address;
	private int     $version = \AF_INET;
	

	public function __construct(String $address) {

		$version = -1;
		if (InetAddress::is_ipv4($address)) {
			$version = \AF_INET;
		} else if (InetAddress::is_ipv6($address)) {
			$version = \AF_INET6;
		} else {
			throw new \Exception("Invalid IP Address \"".$address."\"");
		}

		$this->address = $address;
		$this->version = $version;
	
	}

	public function get_address(): String {
		return $this->address;
	}

	public function get_version(): int {
		return $this->version;
	}

	
	public static function is_ipv4(String $address = "127.0.0.1"): bool {

		return (filter_var($address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) ? true : false;

	}


	public static function is_ipv6(String $address = "::1"): bool {

		return (filter_var($address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) ? true : false;
			
	}
	

};
