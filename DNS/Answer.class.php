<?php

namespace Resolver;

class Answer extends Query {

	public int     $ttl       = 3600;   // 3600s in default
	
	public int     $rdlength  = 0;
	public String  $rdata     = "";

};
