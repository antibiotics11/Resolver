<?php

namespace Resolver;

class NameResolver {

	const TYPE_A          = 1;
	const TYPE_AAAA       = 28;
	const TYPE_NS         = 2;
	const TYPE_CNAME      = 5;
	const TYPE_MX         = 15;
	const TYPE_TXT        = 16;

	const OPCODE_QUERY    = 0;
	const OPCODE_INVERSE  = 1;
	const OPCODE_STATUS   = 2;
	const OPCODE_NOTIFY   = 4;
	const OPCODE_UPDATE   = 5;

	const RCODE_NOERROR   = 0;
	const RCODE_FORMERR   = 1;
	const RCODE_SERVFAI   = 2;
	const RCODE_NXDOMAIN  = 3;
	const RCODE_NOTIMP    = 4;
	const RCODE_REFUSED   = 5;
	
	
	public static function strtype(int $type): String {
	
		$strtype = "";
		
		switch ($type) {
		
		case 1  : $strtype = "A";     break;
		case 28 : $strtype = "AAAA";  break;
		case 2  : $strtype = "NS";    break;
		case 5  : $strtype = "CNAME"; break;
		case 15 : $strtype = "MX";    break;
		case 16 : $strtype = "TXT";   break;
		default : throw new \Exception("Unknown type");
		
		};
		
		return $strtype;
	
	}

	public static function parse_zone_file(String $file): Array {
	
		$fcontents = file($file);
		$record = [];
		
		for ($i = 0; $i < count($fcontents); $i++) {
			
			if (strlen($fcontents[$i] <= 1)) {
				continue;
			}
			
			$r = explode("\t", $fcontents[$i]);
			$type = strtoupper(trim($r[0]));
			
			if (strcmp($type, "#") === 0) {
				continue;
			}
			
			if (strcmp($type, "TTL") === 0) {
				$record["TTL"] = (int)$r[1];
				continue;
			}
			
			if (!isset($record[$type])) {
				$record[$type] = [];
			}
			$record[$type][$r[1]] = $r[2];
						
		}
	
		return $record;
	
	}


};
