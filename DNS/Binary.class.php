<?php

namespace Resolver;

class Binary {


	public static function parse(String $packet): Header {
	
		$id       = bin2hex($packet[0]).bin2hex($packet[1]);
		$flags    = bin2hex($packet[2]).bin2hex($packet[3]);
		$flags    = sprintf("%016d", decbin(hexdec($flags)));
		$opcode   = bindec($flags[1].$flags[2].$flags[3].$flags[4]);
		$qdcount  = bin2hex($packet[4]).bin2hex($packet[5]);
		$ancount  = bin2hex($packet[6]).bin2hex($packet[7]);
		$nscount  = bin2hex($packet[8]).bin2hex($packet[9]);
		$arcount  = bin2hex($packet[10]).bin2hex($packet[11]);

		$query = ((int)$flags[0]) ? new Answer() : new Query();
		
		$query->id           = hexdec($id);
		
		$query->flag_qr      = (int)$flags[0];
		$query->flag_opcode  = (int)$opcode;
		$query->flag_aa      = (int)$flags[5];
		$query->flag_tc      = (int)$flags[6];
		$query->flag_rd      = (int)$flags[7];
		$query->flag_ra      = (int)$flags[8];

		$query->qdcount      = hexdec($qdcount);
		$query->ancount      = hexdec($ancount);
		$query->nscount      = hexdec($nscount);
		$query->arcount      = hexdec($arcount);

		// Search name from query
		$name        = "";
		$name_found  = false;
		$name_end    = 0;
		for ($i = 13; ; $i++) {
			$j = hexdec(bin2hex($packet[$i]));
			if ($j > 0 && $j <= 9) {
				$name .= chr(0x2e);
			} else if ($j === 0) {
				$name_end = $i;
				break;
			} else {
				$name .= chr($j);
			}
		}
		$query->name   = $name;
		
		$type   = bin2hex($packet[++$name_end])
			.bin2hex($packet[++$name_end]);
		$class  = bin2hex($packet[++$name_end])
			.bin2hex($packet[++$name_end]);
		$query->type   = hexdec($type);
		$query->class  = hexdec($class);

		return $query;
	
	}
	
	public static function complete(Header $header): String {
	
		$packet = "";
	
		$id     = sprintf("%016d", decbin($header->id));
		$id     = chr(bindec(substr($id, 0, 8)))
			 .chr(bindec(substr($id, 8)));
		
		$opcode = sprintf("%04d", decbin($header->flag_opcode));
		$rcode  = sprintf("%04d", decbin($header->flag_rcode));
		$flags  = $header->flag_qr
			 .$opcode
			 .$header->flag_aa
			 .$header->flag_tc
			 .$header->flag_rd
			 .$header->flag_ra
			 .$header->flag_z
			 ."00"
			 .$rcode
		;
		$flags = chr(bindec(substr($flags, 0, 8)))
			.chr(bindec(substr($flags, 8)));
		
		$qdcount = sprintf("%016d", decbin($header->qdcount));
		$qdcount = chr(bindec(substr($qdcount, 0, 8)))
			  .chr(bindec(substr($qdcount, 8)));
		$ancount = sprintf("%016d", decbin($header->ancount));
		$ancount = chr(bindec(substr($ancount, 0, 8)))
			  .chr(bindec(substr($ancount, 8)));
		$nscount = sprintf("%016d", decbin($header->nscount));
		$nscount = chr(bindec(substr($nscount, 0, 8)))
			  .chr(bindec(substr($nscount, 8)));
		$arcount = sprintf("%016d", decbin($header->arcount));
		$arcount = chr(bindec(substr($arcount, 0, 8)))
			  .chr(bindec(substr($arcount, 8)));
		
		$packet .= $id.$flags.$qdcount.$ancount.$nscount.$arcount;
		
		if ($header instanceof Query) {
			
			$name = "";
			$level = explode(".", $header->name);
			for ($n = 0; $n < count($level); $n++) {
				$name .= chr(strlen($level[$n]));
				$name .= $level[$n];
			}
			$name .= chr(0x00);
			
			$type  = sprintf("%016d", decbin($header->type));
			$type  = chr(bindec(substr($type, 0, 8)))
				.chr(bindec(substr($type, 8)));
			
			$class = sprintf("%016d", decbin($header->class));
			$class = chr(bindec(substr($class, 0, 8)))
				.chr(bindec(substr($class, 8)));
		
			$packet .= $name.$type.$class;
		
		}
		
		if ($header instanceof Answer && $header->ancount) {
		
			$name = "";
			$level = explode(".", $header->name);
			for ($n = 0; $n < count($level); $n++) {
				$name .= chr(strlen($level[$n]));
				$name .= $level[$n];
			}
			$name .= chr(0x00);
			
			$type  = sprintf("%016d", decbin($header->type));
			$type  = chr(bindec(substr($type, 0, 8)))
				.chr(bindec(substr($type, 8)));
			
			$class = sprintf("%016d", decbin($header->class));
			$class = chr(bindec(substr($class, 0, 8)))
				.chr(bindec(substr($class, 8)));
				
			$ttl   = sprintf("%032d", decbin($header->ttl));
			$ttl   = chr(bindec(substr($ttl, 0, 8)))
				.chr(bindec(substr($ttl, 8, 8)))
				.chr(bindec(substr($ttl, 16, 8)))
				.chr(bindec(substr($ttl, 24)));
			
			$rdata    = Binary::rdata_to_bin($header->type, $header->rdata);
			
			$rdlength = sprintf("%016d", decbin(strlen($rdata)));
			$rdlength = chr(bindec(substr($rdlength, 0, 8)))
				   .chr(bindec(substr($rdlength, 8)));

			$packet .= $name.$type.$class.$ttl.$rdlength.$rdata;
		
		}
		
		return $packet;
	
	}
	
	public static function rdata_to_bin(int $type, String $rdata): String {
		
		$rdata  = trim($rdata);
		$binary = "";
		
		switch ($type) {
		
		case NameResolver::TYPE_A      :
			$field = explode(".", $rdata);
			for ($r = 0; $r < count($field); $r++) {
				$binary .= chr((int)$field[$r]);
			}
			
			break;
		
		case NameResolver::TYPE_AAAA   :
			
			break;
			
		case NameResolver::TYPE_NS     : 
			$part = explode(".", $rdata);
			for ($r = 0; $r < count($part); $r++) {
				$part[$r] = trim($part[$r]);
				$binary .= chr(strlen($part[$r]));
				$binary .= $part[$r];
			}
			$binary .= chr(0x00);
			
			break;
			
		case NameResolver::TYPE_MX     :
			$binary = chr(0x00).chr(0x0a);
			$part   = explode(".", $rdata);
			for ($r = 0; $r < count($part); $r++) {
				$part[$r] = trim($part[$r]);
				$binary .= chr(strlen($part[$r]));
				$binary .= $part[$r];
			}
			$binary .= chr(0x00);
			
			break;
			
		case NameResolver::TYPE_TXT    :
			$binary = chr(strlen($rdata)).$rdata;
				
			break;
		
		};
		
		return $binary;
	
	}


};
