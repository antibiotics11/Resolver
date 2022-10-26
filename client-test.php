#!/usr/bin/php
<?php

const DNS_ADDR = "127.100.100.53";
const DNS_PORT = 53;

$name = readline("host ==> ");
$name = explode(".", $name);

try {

	$socket_fd = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

	$packet = "";
	$dns_header = "";
	$dns_query = "";

	$dns_header .= chr(rand(0, 255)).chr(rand(0, 255));            // Transaction ID
	$dns_header .= chr(0x01).chr(0x00);                            // Flags 
	$dns_header .= chr(0x00).chr(0x01);                            // Questions = 1
	$dns_header .= chr(0x00).chr(0x00);                            // Answer RRs = 0
	$dns_header .= chr(0x00).chr(0x00);                            // Authority RRs = 0
	$dns_header .= chr(0x00).chr(0x00);                            // Additional RRs = 0

	for ($i = 0; $i < count($name); $i++) {                        // Name
		$dns_query .= chr(strlen($name[$i]));
		$dns_query .= $name[$i];
	}
	$dns_query .= chr(0x00);

	$dns_query .= chr(0x00).chr(0x01);                             // Type = A
	$dns_query .= chr(0x00).chr(0x01);                             // Class = IN

	$packet = $dns_header.$dns_query;                              // Complete packet

	socket_connect($socket_fd, DNS_ADDR, DNS_PORT);
	socket_sendto($socket_fd, $packet, strlen($packet), 0, DNS_ADDR, DNS_PORT);

	socket_recv($socket_fd, $data, 512, MSG_WAITALL);

	$address = array();
	$c = 0;
	$tmp = 0;

	for ($i = 0, $j; $i < strlen($data); $i++) {
		if (ord($data[$i]) == 4 && ord($data[$i - 1]) == 0) {

			$address[$c] = array();
			for ($j = $i + 1; $j < $i + 5; $j++) {
				$tmp = ord($data[$j]);
				array_push($address[$c], $tmp);
			}
			$c++;

		}
	}

	for ($i = 0; $i < count($address); $i++) {
		printf("address ==> %s\n", implode(".", $address[$i]));
	}
	

} catch (Exception $e) {

	printf("Socket Error: %s", $e);

} finally {
	
	socket_close($socket_fd);

}
