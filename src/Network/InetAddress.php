<?php

namespace Resolver\Network;

class InetAddress {

	private String $address;
	private int    $family;

	public function __construct(String $address = "") {
	
		if (strlen($address) == 0) {
			$this->address = "";
			$this->family  = \AF_INET;
		} else {
			$this->setNewAddress($address);
		}
	
	}

	public function setNewAddress(String $address): int {

		$address = trim($address);
		if (InetAddress::isIpv4($address)) {
			$this->family = \AF_INET;
		} else if (InetAddress::isIpv6($address)) {
			$this->family = \AF_INET6;
		} else {
			return -1;
		}
		$this->address = $address;

		return $this->family;
	
	}


	public function getAddress(): String {
	
		return $this->address;
	
	}

	public function getFamily(): int {
	
		return $this->family;
	
	}

	public static function isIpv4(String $address): bool {

		return (
			filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
		) ? true : false;
	
	}

	public static function isIpv6(String $address): bool {
	
		return (
			filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
		) ? true : false;
	
	}

};
