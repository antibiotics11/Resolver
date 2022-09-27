<?php

namespace Resolver;

class InetAddress {

	private String $address;
	private int $version = \AF_INET;                       // Address version: 2 => IPv4, 10 => IPv6

	public function __construct(String $address) {

		$version = -1;
		if (InetAddress::is_ipv4($address)) {
			$version = \AF_INET;
		} else if (InetAddress::is_ipv6($address)) {
			$version = \AF_INET6;
		} else {
			throw new \Exception($address." is not a IP Address.");
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

		$address = explode(".", trim($address));

		if (count($address) != 4) {
			return false;
		}
		foreach ($address as $part) {
			if (!is_numeric($part) || (int)$part > 255 || (int)$part < 0) {
				return false;
			}
		}

		return true;

	}


	public static function is_ipv6(String $address = "::1"): bool {

		$address = explode(":", trim($address));
	
		if (count($address) > 8 || count($address) < 2) {
			return false;
		}
		foreach ($address as $part) {
			if (empty($part)) {
				$part = "00";
				continue;
			}
			if (!ctype_xdigit($part) || (int)hexdec($part) > 65535 || (int)hexdec($part) < 0) {
				return false;
			}
		}

		return true;
			
	}

};
