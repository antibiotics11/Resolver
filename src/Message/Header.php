<?php

namespace Resolver\Message;

class Header {

	public int   $id;                    // Transaction ID

	public int   $flag_qr;               // Query/Response
	public int   $flag_opcode;           // Query Type
	public int   $flag_aa;               // Authoritative
	public int   $flag_tc;               // Truncated
	public int   $flag_rd;               // Recursion Desired
	public int   $flag_ra;               // Recursion Available
	public int   $flag_z        = 0;     // Reserved
	public int   $flag_rcode    = 0;     // Response Code

	public int   $qdcount       = 0;     // Questions
	public int   $ancount       = 0;     // Answer Resource Record
	public int   $nscount       = 0;     // Authority Resource Record
	public int   $arcount       = 0;     // Additional Resource Record
	
	public Array $queries       = [];
	public Array $answers       = [];
	/*
	public Array $additional    = [];
	*/
};
