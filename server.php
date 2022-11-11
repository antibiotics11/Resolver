#!/usr/bin/php
<?php

include_once __DIR__."/config.php";
include_once __DIR__."/DNS/InetAddress.class.php";
include_once __DIR__."/DNS/Server.class.php";
include_once __DIR__."/DNS/Socket.class.php";
include_once __DIR__."/DNS/Binary.class.php";
include_once __DIR__."/DNS/Header.class.php";
include_once __DIR__."/DNS/Query.class.php";
include_once __DIR__."/DNS/Answer.class.php";
include_once __DIR__."/DNS/NameResolver.class.php";
include_once __DIR__."/DNS/Logger.class.php";

cli_set_process_title("Resolver");
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

function main(Array $argv = []): void {

	if (strlen($e = extensions_loaded()) !== 0) {
		console_log("The ".$e." extension not loaded.", true);
		terminate(true);
	}

	$pid = pcntl_fork();

	if ($pid == -1) {

		console_log("Failed to fork child process.", true);
		terminate(true);

	} else if ($pid == 0) {      // IPv6 Server
	
	// disabled to avoid conflicts with the systemd-resolved.
	/*
		try {
		
			$server6 = new \Resolver\Server(
				DNS_SERVER_ADDR6,
				DNS_SERVER_PORT,
				DNS_ZONE_FILES,
				DNS_LOG_DIR
			);
			$server6->run();
			
		} catch (Throwable $e) {
			console_log("Server Error: ".$e->getMessage(), true);
		}
	*/
	
	} else if ($pid > 0) {       // IPv4 Server

		$log = "Starting server at ".DNS_SERVER_ADDR.":".DNS_SERVER_PORT;
		console_log($log);
			
		\Resolver\Logger::write_log(DNS_LOG_DIR, $log);

		try {
		
			$server = new \Resolver\Server(
				DNS_SERVER_ADDR,
				DNS_SERVER_PORT,
				DNS_ZONE_FILES,
				DNS_LOG_DIR
			);
			$server->run();
			
		} catch (Throwable $e) {
			console_log("Server Error: ".$e->getMessage(), true);
		}

	}

}

function console_log(String $expression, bool $with_error = false): void {
	
	$color = ($with_error) ? 31 : 32;
	printf("\033[1;%dm%s\033[0m\r\n", $color, $expression);

}

function extensions_loaded(): String {

	$extensions = [ "pcntl", "sockets" ];

	foreach ($extensions as $e) {
		if (!extension_loaded($e)) {
			return $e;
		}
	}

	return "";

}

function terminate(bool $with_error = false): void {

	console_log("Terminating execution...", $with_error);
	exit(0);

}

main($_SERVER["argv"]);
