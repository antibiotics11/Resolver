<?php

namespace Resolver;

class NameResolver {


	/**  DNS parameter constants
	 *   Refer to IANA Domain Name System (DNS) Parameters
	 *   https://www.iana.org/assignments/dns-parameters/dns-parameters.xhtml
	 */
	
	const CLASS_RESERVED  = 0;
	const CLASS_INTERNET  = 1;
	const CLASS_CHAOS     = 3;
	const CLASS_HESIOD    = 4;

	const TYPE_A          = 1;           // Address Record
	const TYPE_AAAA       = 28;          // IPv6 Address Record
	const TYPE_NS         = 2;           // Name Server Record
	const TYPE_CNAME      = 5;           // Canonical Name Record
	const TYPE_MX         = 15;          // Mail Exchange Record
	const TYPE_TXT        = 16;          // Text Record

	const OPCODE_QUERY    = 0;
	const OPCODE_INVERSE  = 1;
	const OPCODE_STATUS   = 2;
	const OPCODE_NOTIFY   = 4;
	const OPCODE_UPDATE   = 5;

	const RCODE_NOERROR   = 0;           // No Error
	const RCODE_FORMERR   = 1;           // Format Error
	const RCODE_SERVFAI   = 2;           // Server Failure
	const RCODE_NXDOMAIN  = 3;           // Non-Existent Domain
	const RCODE_NOTIMP    = 4;           // Not Implemented
	const RCODE_REFUSED   = 5;           // Query Refused
	
	
	public static function strtype(int $type): String {
	
		$strtype = "";
		
		switch ($type) {
		
		case NameResolver::TYPE_A      : $strtype = "A";     break;
		case NameResolver::TYPE_AAAA   : $strtype = "AAAA";  break;
		case NameResolver::TYPE_NS     : $strtype = "NS";    break;
		//case NameResolver::TYPE_CNAME  : $strtype = "CNAME"; break;
		case NameResolver::TYPE_MX     : $strtype = "MX";    break;
		case NameResolver::TYPE_TXT    : $strtype = "TXT";   break;
		
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
	
	public static function resolve(Query $query, String $zone_files_dir): Answer {
		
		$zone_file = realpath($zone_files_dir."/".$query->name);
		$record = ($zone_file) ? NameResolver::parse_zone_file($zone_file) : "";
		$rdata = "";
		if (isset($record[NameResolver::strtype($query->type)])) {
			$rdata = $record[NameResolver::strtype($query->type)];
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
		$response->flag_rcode   = NameResolver::RCODE_NOERROR;
		
		if (!$zone_file) {
			$response->flag_rcode = NameResolver::RCODE_NXDOMAIN;	
		}
		if (strlen(NameResolver::strtype($query->type)) === 0) {
			$response->flag_rcode = NameResolver::RCODE_NOTIMP;	
		}
		if ($query->class !== NameResolver::CLASS_INTERNET) {
			$response->flag_rcode = NameResolver::RCODE_NOTIMP;	
		}
		if ($response->flag_rcode === 0 && strlen($rdata) === 0) {
			$response->flag_rcode = NameResolver::RCODE_REFUSED;
		}
		
		$response->qdcount      = 1;
		$response->ancount      = (!$response->flag_rcode) ? 1 : 0;
		$response->nscount      = 0;
		$response->arcount      = 0;
		
		$response->type         = $query->type;
		$response->class        = $query->class;
		$response->name         = $query->name;
		
		if (!$response->flag_rcode) {
			$response->ttl          = $record["TTL"];
			$response->rdata        = trim($rdata);
			$response->rdlength     = 0;
		}

		return $response;
	
	}


};
