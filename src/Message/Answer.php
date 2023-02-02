<?php

namespace Resolver\Message;

class Answer {

	public int    $ttl       = 3600;        // 3600s in default
	public int    $rdlength  = 0;
	public String $rdata     = "";

};
