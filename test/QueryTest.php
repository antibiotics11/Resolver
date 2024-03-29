#!/usr/bin/php
<?php

include_once __DIR__."/../src/Client/RequestSender.php";
include_once __DIR__."/../src/Message/Header.php";
include_once __DIR__."/../src/Message/Query.php";
include_once __DIR__."/../src/Message/Classes.php";
include_once __DIR__."/../src/Message/OPcodes.php";
include_once __DIR__."/../src/Message/Types.php";
include_once __DIR__."/../src/Network/InetAddress.php";
include_once __DIR__."/../src/Network/Packet.php";

use Resolver\Client\RequestSender as Request;
use Resolver\Message as Message;

$id = 12345;
$targets = Request::EXTERNAL_SERVERS;
$types = [ 
	Message\Types::A->value, 
	Message\Types::NS->value,
	Message\Types::CNAME->value,
	Message\Types::MX->value,
	Message\Types::TXT->value,
	Message\Types::AAAA->value,
	Message\Types::ALL->value,
];

foreach ($targets as $ns) {
	for ($i = 0; $i < count($types); $i++) {
	
		$id += 100;
		$request = new Resolver\Client\RequestSender();
		$request->createHeader();
		$request->setTargetInfo(address: $ns[0], port: $ns[1]);
		$request->setTransactionId($id);
		$request->setFlagRD(0);
		$request->setQuery(
			name:  "google.com", 
			type:  $types[$i],
			class: Message\Classes::INTERNET->value
		);
		
		printf("### Sending Query %s to %s...\r\n", Message\Types::from($types[$i])->name, $ns[2]);
		
		$packet = $request->pack();
		printf("### Query =>\t%s\r\n", bin2hex($packet));
		
		$buffer = $request->send();
		printf("### Answer =>\t%s\r\n", bin2hex($buffer));
		printf("\r\n");
		
	}
}
