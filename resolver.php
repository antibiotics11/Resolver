#!/usr/bin/env php
<?php

include_once __DIR__ . "/autoloader.php";

cli_set_process_title("Resolver");

const DNS_SERVER_ADDRESS = "127.0.0.1";
const DNS_SERVER_PORT    = 53;

$datagram = new Resolver\Server\DatagramServer();
$datagram->create(
	new Resolver\Network\InetAddress(DNS_SERVER_ADDRESS),
	DNS_SERVER_PORT
);

$server = new Resolver\Server\NameServer($datagram);
$server->run();
