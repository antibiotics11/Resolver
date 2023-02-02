<?php

namespace Resolver\Message;

enum Types: int {

	case A     = 1;   // Address
	case NS    = 2;   // Name Server
	case CNAME = 5;   // Canonical Name
	case SOA   = 6;   // Start of Authority
	case PTR   = 12;  // PTR Resource
	case MX    = 15;  // Mail Exchange
	case TXT   = 16;  // Text
	case AAAA  = 28;  // IPv6 Address
	case SRV   = 33;  // Service Locator 
	case ALL   = 255;
	
	public static function isSupportedType(int $type): bool {
	
		switch ($type) {
			case self::A->value :
			case self::NS->value :
			case self::CNAME->value :
			case self::MX->value :
			case self::TXT->value :
			case self::AAAA->value :
				return true;
			default :
				return false;
		};
		return false;
	
	}

};
