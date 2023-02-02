<?php

namespace Resolver\Message;

enum Rcodes: int {

	case NOERROR   = 0;   // No Error
	case FORMERR   = 1;   // Format Error  
	case SERVFAI   = 2;   // Server Failure
	case NXDOMAIN  = 3;   // Non-Existent Domain
	case NOTIMP    = 4;   // Not Implemented
	case REFUSED   = 5;   // Query Refused
	case YXDOMAIN  = 6;   // Name Exists when it should not
	case NOTAUTH   = 9;   // Not Authorized
	case NOTZONE   = 10;  // Name not contained in zone
	
	public static function rcodeInString(int $rcode): String {
	
		return match ($rcode) {
			self::NOERROR->value  => "No Error",
			self::FORMERR->value  => "Format Error",
			self::SERVFAI->value  => "Server Failure",
			self::NXDOMAIN->value => "Non-Existent Domain",
			self::NOTIMP->value   => "Not Implemented",
			self::REFUSED->value  => "Query Refused",
			self::YXDOMAIN->value => "Name Exists when it shoult not",
			self::NOTAUTH->value  => "Not Authorized",
			self::NOTZONE->value  => "Name not contained in zone",
			default               => ""
		};
		
	}
	
};
