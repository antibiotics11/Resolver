#!/usr/bin/php
<?php

include_once __DIR__."/config.php";
include_once __DIR__."/InetAddress.class.php";
include_once __DIR__."/DnsSocket.class.php";
include_once __DIR__."/NameResolver.class.php";
include_once __DIR__."/DnsPacket.class.php";
include_once __DIR__."/DnsHeader.class.php";
include_once __DIR__."/DnsQuery.class.php";
include_once __DIR__."/DnsAnswer.class.php";

use \Resolver\{ServerConfig, InetAddress, DnsSocket, NameResolver};
use \Resolver\Packet\{DnsPacket, DnsHeader, DnsQuery};

cli_set_process_title("Resolver/php");
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

function main(Array $argv): void {

	if (strlen($e = extensions_loaded()) !== 0) {
		console_log("The ".$e." extension not loaded.", true);
		terminate(true);
	}

	$pid = pcntl_fork();
	if ($pid == -1) {

		console_log("Failed to fork process", true);
		terminate(true);
	
	} else if ($pid == 0) {        // Run IPv6 Server in child process
	/*	
		server(
			ServerConfig::SERVER_ADDR6,
			ServerConfig::SERVER_PORT
		);
	 */
	} else if ($pid > 0) {         // Run IPv4 Server
		
		server(
			ServerConfig::SERVER_ADDR,
			ServerConfig::SERVER_PORT
		);

	}

}

function server(String $address, int $port): void {

	console_log("Listening on [".$address."]:".$port);

	$address = new InetAddress($address);	
	$socket = new DnsSocket();

	$handler = function(String $packet, String $ip, int $port) {

		$question = DnsPacket::parse($packet);

		console_log(
			"[ ID ".$question->id." ] => "
			."Request "
			.(($question->type === 1) ? "IPv4" : "IPv6")
			." record for ".$question->name
			." from [".$ip."]:".$port
		);

		$answer = NameResolver::resolve(
			$question, 
			ServerConfig::ZONE_FILES
		);

		$packet = DnsPacket::complete($answer);

		return $packet;

	};

	$socket->create($address, $port);
	$socket->listen($handler);

}

function get_system_time($zone = "UTC"): String {

	date_default_timezone_set("Asia/Seoul");
	$time = "[ ".date("D M j G:i:s T")." ]";

	return $time;

}

function console_log(String $log, bool $is_error = false): void {

	global $server_options;
	$color = ($is_error) ? 31 : 32;
	printf(
		"\033[1;%dm%s\t%s\033[0m%s", 
		$color, 
		get_system_time(), 
		$log, 
		PHP_EOL
	);

}

function extensions_loaded(): String {

	$extensions = Array("pcntl", "sockets");
	foreach ($extensions as $e) {
		if (!extension_loaded($e)) {
			return $e;
		}
	}

	return "";

}

function terminate(bool $by_error = false): void {

	console_log("Terminating execution...", $by_error);
	exit(0);

}

main($_SERVER["argv"]);
