<?php

namespace Resolver;
use Resolver\Packet\{DnsHeader, DnsQuery, DnsAnswer, DnsPacket};

class NameResolver {

	const TYPE_A          = 1;
	const TYPE_AAAA       = 28;
	//const TYPE_NS         = 2;
	//const TYPE_CNAME      = 5;
	//const TYPE_MX         = 15;
	//const TYPE_TXT        = 16;

	const OPCODE_QUERY    = 0;
	//const OPCODE_INVERSE  = 1;
	//const OPCODE_STATUS   = 2;
	//const OPCODE_NOTIFY   = 4;
	//const OPCODE_UPDATE   = 5;

	const RCODE_NOERROR   = 0;
	//const RCODE_FORMERR   = 1;
	//const RCODE_SERVFAI   = 2;
	//const RCODE_NXDOMAIN  = 3;
	const RCODE_NOTIMP    = 4;
	//const RCODE_REFUSED   = 5;

	public static Array $cache = Array();

	public static function question(String $packet): String {
		
		$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_connect($socket, "8.8.8.8", 53);
		socket_sendto($socket, $packet, 512, 0, "8.8.8.8", 53);
		socket_recv($socket, $answer, 512, MSG_WAITALL);

		return $answer;

	}

	public static function parse_zone_file(String $zone_file): Array {

		$record = Array();
		$contents = file($zone_file);
		
		for ($i = 0; $i < count($contents); $i++) {

			if (strlen($contents[$i] <= 1)) {
				continue;
			}

			$r = explode("\t", $contents[$i]);
			if (count($r) !== 2) {
				continue;
			}

			$r[0] = strtoupper(trim($r[0]));
			$r[1] = trim($r[1]);

			if (!isset($record[$r[0]])) {
				$record[$r[0]] = Array();
			}
			array_push($record[$r[0]], $r[1]);

		}

		return $record;
	
	}

	public static function strtype(int $type): String {

		$strtype = "";
		switch ($type) {
		case 1  : $strtype = "A"; break;
		case 28 : $strtype = "AAAA"; break;
		default : $strtype = "A";
		};

		return $strtype;
	
	}

	public static function resolve(DnsHeader $query, String $zone_files): DnsHeader {

		$answer = null;
		$packet = "";
		$zone_file = $zone_files.DIRECTORY_SEPARATOR.$query->name;

		if (is_file($zone_file) && is_readable($zone_file)) {

			$record = &NameResolver::$cache[$query->name];
			if (!isset($record)) {
				$record = NameResolver::parse_zone_file($zone_file);
			}
			
			$answer = new DnsAnswer();

			$answer->id           = $query->id;
			$answer->flag_qr      = 1;                           // is Answer
			$answer->flag_opcode  = NameResolver::OPCODE_QUERY;  // is standard query
			$answer->flag_aa      = 1;                           // is nameserver
			$answer->flag_tc      = 0;                           // is not truncated
			$answer->flag_rd      = $query->flag_rd;
			$answer->flag_ra      = 0;                           // recursion not allowed
			$answer->flag_z       = 0;
			$answer->flag_rcode   = NameResolver::RCODE_NOERROR; // no error

			$answer->qdcount      = $query->qdcount;
			$answer->ancount      = $query->ancount;
			$answer->nscount      = $query->nscount;
			$answer->arcount      = $query->arcount;

			$answer->type         = $query->type;
			$answer->class        = $query->class;
			$answer->name         = $query->name;

			$answer->ttl          = $record["TTL"][0];

			$rdata = $record[NameResolver::strtype($answer->type)][0];
			$rdata = explode(
				(($answer->type === 1) ? "." : ":"),
				$rdata
			);
			for ($r = 0; $r < count($rdata); $r++) {
				$answer->rdata .= chr((int)$rdata[$r]);
			}
			$answer->rdlength     = strlen($answer->rdata);

		} else {

			$packet = DnsPacket::complete($query);
			$packet = NameResolver::question($packet);
			$answer = DnsPacket::parse($packet);

		}

		return $answer;

	}

};
