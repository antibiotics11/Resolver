<?php

namespace Resolver;

class Server {

	private ?InetAddress $address = null;
	private int          $port    = -1;

	private String       $zone_files = "";
	
	private String       $log_dir = __DIR__;

	public function __construct(String $address, int $port, String $zone_files, String $log_dir = "") {

		try {
			$this->address = new InetAddress($address);
		} catch (\Throwable $e) {
			throw new \Exception($e);
		}

		if (Socket::is_valid_port($port)) {
			$this->port = $port;
		} else {
			throw new \Exception("Invalid port number \"".$port."\"");
		}

	
		$path = realpath($zone_files);
		if ($path == false || !is_dir($path) || !is_readable($path)) {
			throw new \Exception("Invalid directory \"".$path."\"");
		}
		$this->zone_files = $path;
		
		$path = "";
		if (strlen($log_dir) < 0) {
			$this->log_dir = __DIR__;
		} else {
			$path = realpath($log_dir);
			if ($path && is_writeable($path)) {
				$this->log_dir = $path;
			}
		}

	}

	public function run(): void {
		
		$socket = new Socket();

		$socket->create($this->address, $this->port);
		
		$socket->listen(function($data, $ip, $port) {

			$question = Binary::parse($data);
			
			Logger::write_log(
				$this->log_dir,
				"[".$question->id."] "
				."Query "
				.NameResolver::strtype($question->type)
				." ".$question->name
				." from ".$ip.":".$port
			);
			
			$answer = NameResolver::resolve(
				$question, 
				$this->zone_files
			);
			
			$packet = Binary::complete($answer);

			Logger::write_log(
				$this->log_dir,
				"[".$answer->id."] "
				."Response "
				.NameResolver::strtype($answer->type)
				." ".$answer->name
				." to ".$ip.":".$port
			);

			return $packet;

		});
	
	}

};
