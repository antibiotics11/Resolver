<?php

namespace Resolver;

class Server {

	private ?InetAddress $address = null;
	private int          $port    = -1;

	private String       $zone_files = "";

	public function __construct(String $address, int $port, String $zone_files) {

		try {
			$this->address = new InetAddress($address);
		} catch (\Exception $e) {
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
	
	}

	public function run(): void {
		
		$socket = new Socket();

		$socket->create($this->address, $this->port);
		$socket->listen(function($data, $ip, $port) {

			$question = Packet::parse($data);
			var_dump($question);
			$answer = NameResolver::resolve($question, $this->zone_files);
			$data = Packet::complete($answer);

			return $data;

		});
	
	}

};
