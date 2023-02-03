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
$targets[] = [ "127.0.0.53", 53, "Stub Resolver" ];

foreach ($targets as $ns) {
	
		$id += 100;
		$request = new Resolver\Client\RequestSender();
		$request->createHeader();
		$request->setTargetInfo(address: $ns[0], port: $ns[1]);
		$request->setTransactionId($id);
		$request->setFlagRD(0);
		
		// Requesting multiple types of records in single message.
		$request->setQuery(
			name:  "google.com", 
			type:  Message\Types::A->value,
			class: Message\Classes::INTERNET->value
		);
		$request->setQuery(
			name:  "google.com", 
			type:  Message\Types::NS->value,
			class: Message\Classes::INTERNET->value
		);
		$request->setQuery(
			name:  "google.com", 
			type:  Message\Types::CNAME->value,
			class: Message\Classes::INTERNET->value
		);
		$request->setQuery(
			name:  "google.com", 
			type:  Message\Types::MX->value,
			class: Message\Classes::INTERNET->value
		);
		$request->setQuery(
			name:  "google.com", 
			type:  Message\Types::TXT->value,
			class: Message\Classes::INTERNET->value
		);
		$request->setQuery(
			name:  "google.com", 
			type:  Message\Types::AAAA->value,
			class: Message\Classes::INTERNET->value
		);
		
		printf("### Sending Query to %s...\r\n", $ns[2]);
		
		$packet = $request->pack();
		printf("### Query =>\t%s\r\n", bin2hex($packet));
		
		$buffer = $request->send();
		printf("### Answer =>\t%s\r\n", bin2hex($buffer));
		printf("\r\n");

}
