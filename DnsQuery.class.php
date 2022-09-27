<?php

namespace Resolver\Packet;

class DnsQuery extends DnsHeader {

	public int $type;
	public int $class = 0x0001;
	public String $name = "";         	

};
