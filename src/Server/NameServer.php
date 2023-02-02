<?php

namespace Resolver\Server;
use Resolver\Network\{InetAddress, Packet};
use Resolver\System\{Logger, Time};
use Resolver\Message\{Rcodes, Types};
use Resolver\Record\Record;

class NameServer {
	
	public const ENABLE_RECURSION = false;
	public const ENABLE_FORWARDER = false;

	public const LOG_DIRECTORY = __DIR__."/../../log/";

	private DatagramServer $datagram;
	private Logger         $logger;

	public function __construct(DatagramServer $datagram) {
	
		$this->logger = new Logger();
		
		$this->datagram = $datagram;
		$this->datagram->nonblock();
		
		Record::loadFiles(__DIR__."/../../record/");
		
		Time::setTimezone("UTC");
		register_shutdown_function([$this, "shutdown"]);
		
	}

	public function run(): void {
		
		$log = sprintf(
			"[%s]\tStarting server at %s:%d",
			Time::DateRFC2822(), 
			$this->datagram->getAddress()->getAddress(),
			$this->datagram->getPort()
		);
		$this->logger->write($log);
		Logger::print($log);
		
		$this->datagram->listen(
			function ($buffer, $remoteIp, $remotePort) {
			
			$request  = Packet::parseQuery($buffer);
			$response = (new RequestHandler())->handleRequest($request);
			$packet   = Packet::packHeader($response);
			
			$log = sprintf(
				"[%s]\t%s#%d Query %s for %s %s",
				Time::DateRFC2822(),
				$remoteIp, $request->id,
				Types::from($request->queries[0]->type)->name,
				$request->queries[0]->name,
				Rcodes::rcodeInString($response->flag_rcode)
			);
			$this->logger->write($log);
			Logger::print($log);
			
			return $packet;
			
		});
	
	}
	
	public function shutdown() {
		
		Logger::print("Shutting down server...");
		$file = sprintf("%s/%s.log", self::LOG_DIRECTORY, Time::DateYMD());
		$this->logger->writeBufferToFile($file);
	
	}

};
