<?php

namespace Resolver\Packet;

class DnsAnswer extends DnsQuery {

	public int $ttl         = 0x0e10;   // 3600s in default

	public int $rdlength    = 0x0000;
	public String $rdata    = "";

};
