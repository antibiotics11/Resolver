<?php

namespace Resolver\Network;
use Resolver\Message\{Header, Query, Answer, Types};

class Packet {

	public static function parseQuery(String $packet): Header {
		
		$id       = bin2hex($packet[0]).bin2hex($packet[1]);
		$flags    = bin2hex($packet[2]).bin2hex($packet[3]);
		$flags    = sprintf("%016d", decbin(hexdec($flags)));
		$opcode   = bindec($flags[1].$flags[2].$flags[3].$flags[4]);
		$qdcount  = bin2hex($packet[4]).bin2hex($packet[5]);
		$ancount  = bin2hex($packet[6]).bin2hex($packet[7]);
		$nscount  = bin2hex($packet[8]).bin2hex($packet[9]);
		$arcount  = bin2hex($packet[10]).bin2hex($packet[11]);

		$header = new Header();

		$header->id           = hexdec($id);
		$header->flag_qr      = (int)$flags[0];
		$header->flag_opcode  = (int)$opcode;
		$header->flag_aa      = (int)$flags[5];
		$header->flag_tc      = (int)$flags[6];
		$header->flag_rd      = (int)$flags[7];
		$header->flag_ra      = (int)$flags[8];

		$header->qdcount      = hexdec($qdcount);
		$header->ancount      = hexdec($ancount);
		$header->nscount      = hexdec($nscount);
		$header->arcount      = hexdec($arcount);
		
		$s = 13;
		for ($q = 0; $q < $header->qdcount; $q++) {
		
			$name = "";
			$end  = 0;
			for ($n = $s; ; $n++) {
				$p = hexdec(bin2hex($packet[$n]));
				if ($p > 0 && $p <= 9) {
					$name .= chr(46);
				} else if ($p == 0) {
					$end = $n;
					break;
				} else {
					$name .= chr($p);
				}
			}
			
			$type  = bin2hex($packet[++$end]).bin2hex($packet[++$end]);
			$class = bin2hex($packet[++$end]).bin2hex($packet[++$end]);
			
			$s = $end;
			
			$header->queries[$q] = new Query();
			$header->queries[$q]->name  = $name;
			$header->queries[$q]->type  = hexdec($type);
			$header->queries[$q]->class = hexdec($class);
			
		}
		
		return $header;
	
	}

	/** 
	 * Compress Header object into binary packet.
	 *
	 * @param  Resolver\Message\Header $header  Header object
	 * @return String binary packet
	 */
	public static function packHeader(Header $header): String {

		$id = sprintf("%016d", decbin($header->id));
		$id = sprintf(
			"%s%s",
			chr(bindec(substr($id, 0, 8))),
			chr(bindec(substr($id, 8)))
		);
		
		$opcode = sprintf("%04d", decbin($header->flag_opcode));
		$rcode  = sprintf("%04d", decbin($header->flag_rcode));
		$flags  = sprintf(
			"%s%s%s%s%s%s%s00%s",
			$header->flag_qr,
			$opcode,
			$header->flag_aa,
			$header->flag_tc,
			$header->flag_rd,
			$header->flag_ra,
			$header->flag_z,
			$rcode
		);
		$flags  = chr(bindec(substr($flags, 0, 8))).chr(bindec(substr($flags, 8)));
		
		$qdcount = sprintf("%016d", decbin($header->qdcount));
		$qdcount = sprintf(
			"%s%s",
			chr(bindec(substr($qdcount, 0, 8))),
			chr(bindec(substr($qdcount, 8)))
		);
		$ancount = sprintf("%016d", decbin($header->ancount));
		$ancount = sprintf(
			"%s%s",
			chr(bindec(substr($ancount, 0, 8))),
			chr(bindec(substr($ancount, 8)))
		);
		$nscount = sprintf("%016d", decbin($header->nscount));
		$nscount = sprintf(
			"%s%s",
			chr(bindec(substr($nscount, 0, 8))),
			chr(bindec(substr($nscount, 8)))
		);
		$arcount = sprintf("%016d", decbin($header->arcount));
		$arcount = sprintf(
			"%s%s",
			chr(bindec(substr($arcount, 0, 8))),
			chr(bindec(substr($arcount, 8)))
		);
		
		$packet  = $id.$flags.$qdcount.$ancount.$nscount.$arcount;
		
		for ($q = 0; $q < count($header->queries); $q++) {
			$packet .= self::packQuery($header->queries[$q]);
		}
		
		for ($a = 0; $a < count($header->answers); $a++) {
			$packet .= self::packAnswer($header->queries[0], $header->answers[$a]);
		}
		
		return $packet;
	
	}
	
	public static function packQuery(Query $query): String {
		
		$name = "";
		$level = explode(".", $query->name);
		for ($l = 0; $l < count($level); $l++) {
			$name .= chr(strlen($level[$l]));
			$name .= $level[$l];
		}
		$name .= chr(0);
		
		$type = sprintf("%016d", decbin($query->type));
		$type = chr(bindec(substr($type, 0, 8))).chr(bindec(substr($type, 8)));
		
		$class = sprintf("%016d", decbin($query->class));
		$class = chr(bindec(substr($class, 0, 8))).chr(bindec(substr($class, 8)));
		
		return sprintf("%s%s%s", $name, $type, $class);
		
	}
	
	public static function packAnswer(Query $query, Answer $answer): String {
	
		$ttl = sprintf("%032d", decbin($answer->ttl));
		$ttl = sprintf(
			"%s%s%s%s",
			chr(bindec(substr($ttl, 0, 8))),
			chr(bindec(substr($ttl, 8, 8))),
			chr(bindec(substr($ttl, 16, 8))),
			chr(bindec(substr($ttl, 24)))
		);
		$rdata = self::rdataToBinary($query->type, $answer->rdata);
		$rdlength = sprintf("%016d", decbin(strlen($rdata)));
		$rdlength = sprintf(
			"%s%s",
			chr(bindec(substr($rdlength, 0, 8))),
			chr(bindec(substr($rdlength,8)))
		);
		
		$query = self::packQuery($query);
		
		return sprintf("%s%s%s%s", $query, $ttl, $rdlength, $rdata);
	
	}

	public static function rdataToBinary(int $type, String $rdata): String {
	
		$rdata  = trim($rdata);
		$binary = "";

		switch ($type) {
		
		case Types::A->value :
		case Types::AAAA->value :
			$binary = inet_pton($rdata);
			break;

		case Types::TXT->value : 
			$binary = chr(strlen($rdata)).$rdata;
			break;

		case Types::MX->value :
			$binary .= chr(0).chr(10);
		case Types::NS->value : 
			$part = explode(".", $rdata);
			for ($r = 0; $r < count($part); $r++) {
				$part[$r] = trim($part[$r]);
				$binary .= chr(strlen($part[$r]));
				$binary .= $part[$r];
			}
			$binary .= chr(0);
			break;
			
		case Types::CNAME->value :
			break;

		default :
			$binary = "";
		
		};

		return $binary;
	
	}

};
