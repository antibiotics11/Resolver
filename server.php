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
		printf("The %s extension not loaded.\n", $e);
		exit(0); 
	}

	$pid = pcntl_fork();

	if ($pid == -1) {

		printf("Failed to fork child process.\n");
		exit(0);

	} else if ($pid == 0) {
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
			printf("Server Error: %s", $e->getMessage());
		}
	*/
	} else if ($pid > 0) {

		try {
			$server = new \Resolver\Server(
				DNS_SERVER_ADDR,
				DNS_SERVER_PORT,
				DNS_ZONE_FILES,
				DNS_LOG_DIR
			);
			$server->run();
		} catch (Throwable $e) {
			printf("Server Error: %s", $e->getMessage());
		}

	}

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

main($_SERVER["argv"]);
