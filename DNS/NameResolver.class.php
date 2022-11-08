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
	
	
	public static Array  $cache = [];
	
	public static function strtype(int $type): String {
	
		$strtype = "";
		
		switch ($type) {
		
		case NameResolver::TYPE_A      : $strtype = "A";     break;
		case NameResolver::TYPE_AAAA   : $strtype = "AAAA";  break;
		case NameResolver::TYPE_NS     : $strtype = "NS";    break;
		case NameResolver::TYPE_CNAME  : $strtype = "CNAME"; break;
		case NameResolver::TYPE_MX     : $strtype = "MX";    break;
		case NameResolver::TYPE_TXT    : $strtype = "TXT";   break;
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
			$record[$type] = $r[1];
						
		}
		
		if (!isset($record["TTL"])) {
			$record["TTL"] = 3600;
		}
	
		return $record;
	
	}
	
	public static function set_cache(String $name, Array $record): void {
	
		$record["SETTIME"] = time();
		
		NameResolver::$cache[$name] = $record;
	
	}
	
	public static function search_cache(String $name): Array {
		
		$record = &NameResolver::$cache[$name];
		
		if ($record == null || !isset($record)) {
			return [];
		}
		
		if ((time() - (int)$record["SETTIME"]) > $record["TTL"]) {
			unset($record);
			return [];
		}
		
		return $record;
	
	}
	
	public static function set_rdata(int $type, Array $record): String {
	
		$rdata = "";
		
		switch ($type) {
		
		case NameResolver::TYPE_A      : 
			$ip = $record["A"];
			$part = explode(".", $ip);
			for ($r = 0; $r < count($part); $r++) {
				$rdata .= chr((int)$part[$r]);
			}
			
			break;
		
		case NameResolver::TYPE_AAAA   : 
			$ip = $record["AAAA"];
			$part = explode(":", $ip);
			for ($r = 0; $r < count($part); $r++) {
				$rdata .= chr((int)$part[$r]);
			}
			
			break;
			
		case NameResolver::TYPE_NS     : 
			$part = explode(".", $record["NS"]);
			for ($r = 0; $r < count($part); $r++) {
				$part[$r] = trim($part[$r]);
				$rdata .= chr(strlen($part[$r]));
				$rdata .= $part[$r];
			}
			$rdata .= chr(0x00);
			
			break;
			
		case NameResolver::TYPE_MX     :
			$rdata = chr(0x00).chr(0x0a);
			$part = explode(".", $record["MX"]);
			for ($r = 0; $r < count($part); $r++) {
				$part[$r] = trim($part[$r]);
				$rdata .= chr(strlen($part[$r]));
				$rdata .= $part[$r];
			}
			$rdata .= chr(0x00);
			
			break;
			
		case NameResolver::TYPE_TXT    :
			$record["TXT"] = trim($record["TXT"]);
			$rdata = chr(strlen($record["TXT"])).$record["TXT"];
				
			break;
		
		};
		
		return $rdata;
	
	}
	
	public static function resolve(Header $query, String $zone_files): Header {
	
		$response = null;
		$packet = "";
		
		$record = NameResolver::search_cache($query->name);
		if (count($record) === 0) {
			$file = $zone_files.DIRECTORY_SEPARATOR.$query->name;
			
			if (file_exists($file)) {
				$record = NameResolver::parse_zone_file($file);
				NameResolver::set_cache($query->name, $record);
			} else {
				
			}
			
		}
		
		$response = new Answer();
		
		$response->id           = $query->id;
		$response->flag_qr      = 1;                           // is answer
		$response->flag_opcode  = NameResolver::OPCODE_QUERY;  // is standard query
		$response->flag_aa      = 1;                           // is nameserver
		$response->flag_tc      = 0;                           // is not truncated
		$response->flag_rd      = $query->flag_rd;
		$response->flag_ra      = 0;                           // recursion not allowed
		$response->flag_z       = 0;
		$response->flag_rcode   = NameResolver::RCODE_NOERROR; // no error
		
		$response->qdcount      = 1;
		$response->ancount      = 1;
		$response->nscount      = 0;
		$response->arcount      = 0;
		
		$response->type         = $query->type;
		$response->class        = $query->class;
		$response->name         = $query->name;
		
		$response->ttl          = $record["TTL"];
		$response->rdata        = NameResolver::set_rdata($query->type, $record);
		$response->rdlength     = strlen($response->rdata);
	
		return $response;
	
	}


};
