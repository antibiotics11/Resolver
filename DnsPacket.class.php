<?php

namespace Resolver\Packet;

class DnsPacket {

	/** Parse packet and return DnsHeader object */
	public static function parse(String $packet): DnsHeader {

		$id       = bin2hex($packet[0]).bin2hex($packet[1]);
		$flags    = bin2hex($packet[2]).bin2hex($packet[3]);
		$flags    = sprintf("%016d", decbin(hexdec($flags)));
		$opcode   = bindec($flags[1].$flags[2].$flags[3].$flags[4]);
		$qdcount  = bin2hex($packet[4]).bin2hex($packet[5]);
		$ancount  = bin2hex($packet[6]).bin2hex($packet[7]);
		$nscount  = bin2hex($packet[8]).bin2hex($packet[9]);
		$arcount  = bin2hex($packet[10]).bin2hex($packet[11]);

		// Packet is Response if QR is 1, Request if 0
		$query = ((int)$flags[0]) ? new DnsAnswer() : new DnsQuery();
		
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

	/** Complete UDP Message from DnsHeader object */
	public static function complete(DnsHeader $header): String {

		$id     = sprintf("%04d", dechex($header->id));
		$flags  = (String)$answer->flag_qr
			 .(String)$answer->flag_opcode
			 .(String)$answer->flag_aa
			 .(String)$answer->flag_tc
			 .(String)$answer->flag_rd
			 .(String)$answer->flag_ra
			 .(String)$answer->flag_z
			 .(String)$answer->flag_rcode
			 ;

		$packet;
	
		return $packet;
	}

};
