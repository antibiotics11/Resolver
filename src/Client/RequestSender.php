<?php

namespace Resolver\Client;
use Resolver\Message\{Header, Query};
use Resolver\Message\{Classes, OPcodes, Types};
use Resolver\Network\{InetAddress, Packet};

class RequestSender {

	public const EXTERNAL_SERVERS = [
		[ "1.1.1.1", 53, "Cloudflare" ],
		[ "8.8.8.8", 53, "Google Public DNS" ],
		[ "9.9.9.9", 53, "IBM Quad9" ],
		[ "208.67.222.222", 53, "Cisco OpenDNS" ],
	];

	private InetAddress $targetAddress;
	private int         $targetPort;
	private Header      $header;
	private String      $packet;
	
	public function createHeader(): void {
	
		$this->header = new Header();
		
		$this->header->id          = rand(1, 65535);
		$this->header->flag_qr     = 0;
		$this->header->flag_opcode = OPcodes::QUERY->value;
		$this->header->flag_aa     = 0;
		$this->header->flag_tc     = 0;
		$this->header->flag_rd     = 1;
		$this->header->flag_ra     = 0;
		$this->header->flag_z      = 0;
		$this->header->flag_rcode  = 0;
		
		$this->header->qdcount     = 0;
		$this->header->ancount     = 0;
		$this->header->nscount     = 0;
		$this->header->arcount     = 0;
		
		$this->header->queries     = [];
		$this->header->answers     = [];
	
	}
	
	public function setTargetInfo(String $address, int $port): void {
		
		$this->targetAddress = new InetAddress($address);
		$this->targetPort    = $port;
		
	}
	
	public function setTransactionId(int $id): int {
	
		if ($id > 1 && $id < 65536) {
			$this->header->id = $id;
		} else {
			$id = -1;
		}
		
		return $id;
	
	}
	
	public function setFlagOPcode(int $type): void {
		
		$this->header->flag_opcode = OPcodes::from($type)->value;
		
	}

	public function setFlagRD(bool $desired): void {
		
		$this->header->rd = ($desired) ? 1 : 0;
		
	}
	
	public function setQuery(String $name, int $type = 1, int $class = 1): Query {
	
		$query = new Query();
		$query->name  = $name;
		$query->type  = $type;
		$query->class = $class;
		
		$this->header->queries[] = $query;
		
		return $query;
	
	}
	
	public function pack(): String {
	
		$this->header->qdcount = count($this->header->queries);
		$this->packet = Packet::packHeader($this->header);
		
		return $this->packet;
	
	}
	
	public function send(): String {
		
		$version = $this->targetAddress->getFamily();
		$address = $this->targetAddress->getAddress();
		$port    = $this->targetPort;
		
		$socket = socket_create($version, SOCK_DGRAM, SOL_UDP);
		socket_sendto($socket, $this->packet, strlen($this->packet), 0, $address, $port);
		socket_recv($socket, $buffer, 512, MSG_WAITALL);
		socket_close($socket);
		
		return $buffer;
		
	}
	
};
