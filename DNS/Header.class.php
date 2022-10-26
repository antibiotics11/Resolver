<?php

namespace Resolver;

class Header {

	public int $id;                     // Transaction ID
	
	public int $flag_qr;                // Query/Response
	public int $flag_opcode;            // Query Type
	public int $flag_aa;                // Authoritative
	public int $flag_tc;                // Truncated
	public int $flag_rd;                // Recursion Desired
	public int $flag_ra;                // Recursion Available
	public int $flag_z       = 0;       // Reserved
	public int $flag_rcode   = 0;       // Response Code

	public int $qdcount;                // Questions
	public int $ancount;                // Answer Resource Record
	public int $nscount;                // Authority Resource Record
	public int $arcount;                // Additional Resource Record

};
