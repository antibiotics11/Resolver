<?php

namespace Resolver\Packet;

class DnsHeader {

	public int $id;                     // Transaction Id
	
	public int $flag_qr;                // Query/Response
	public int $flag_opcode;            // Query Type
	public int $flag_aa;                // Authoritative
	public int $flag_tc;                // Truncated
	public int $flag_rd;                // Recursion Desired
	public int $flag_ra;                // Recursion Available
	public int $flag_z       = 0x00;    // Reserved
	public int $flag_rcode   = 0x00;    // Response Code

	public int $qdcount;                // Questions
	public int $ancount;                // Answer Resource Record
	public int $nscount;                // Authority Resource Record
	public int $arcount;                // Additional Resource Record

};
