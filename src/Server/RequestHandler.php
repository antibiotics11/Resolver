<?php

namespace Resolver\Server;
use Resolver\Message\{Header, Query, Answer};
use Resolver\Message\{OPcodes, Rcodes, Types, Classes};
use Resolver\Record\Record;

class RequestHandler {

	private Header  $request;
	private Header  $response;
	
	private function createDefaultResponse(): void {
		
		$this->response = new Header();
		$this->response->id          = $this->request->id;
		$this->response->flag_qr     = 1;
		$this->response->flag_opcode = OPcodes::QUERY->value;
		$this->response->flag_aa     = 1;
		$this->response->flag_tc     = 0;
		$this->response->flag_rd     = $this->request->flag_rd;
		$this->response->flag_ra     = (NameServer::ENABLE_RECURSION) ? 1 : 0;
		$this->response->flag_z      = 0;
		$this->response->flag_rcode  = Rcodes::NOERROR->value;
		
		$this->response->qdcount     = $this->request->qdcount;
		$this->response->ancount     = 0;
		$this->response->nscount     = 0;
		$this->response->arcount     = 0;
	
	}
	
	private function validateHeader(): void {

		// If query is not a question
		if ($this->request->flag_qr) {           
			$this->response->flag_rcode = Rcodes::FORMERR->value;
		}
		
		// If query is not a standard query
		if ($this->request->flag_opcode != OPcodes::QUERY->value) {
			$this->response->flag_rcode = Rcodes::NOTIMP->value;
		}
		
		// If there is no query in header
		if (!$this->request->qdcount || !count($this->request->queries)) {
			$this->response->flag_rcode = Rcodes::FORMERR->value;
		}
		
	}
	
	private function validateQuery(): void {
	
		$query = $this->request->queries[0];
		
		// If query is not about INTERNET (IN) class
		if ($query->class != Classes::INTERNET->value) {
			$this->response->flag_rcode = Rcodes::SERVFAI->value;
		}
		
		// If query type is not supported
		if (!Types::isSupportedType($query->type)) {
			$this->response->flag_rcode = Rcodes::NOTIMP->value;
		}
	
	}
	
	private function createAnswerForQuery(): void {
		
		$query = $this->request->queries[0];
		$this->response->answers = [];
		
		$name  = $this->request->queries[0]->name;
		$tmp   = $name;
		$level = explode(".", $name);
		$ttl   = 0;
		$data  = [];
		
		for ($l = 0; $l < count($level); $l++) {
			if (isset(Record::$zones[$tmp])) {
				$type = Types::from($this->request->queries[0]->type);
				$ttl  = Record::$zones[$tmp]->TTL;
				$data = Record::$zones[$tmp]->{$type->name}[$tmp];
				break;
			}
			if ($l == count($level) - 1) {
				$this->response->flag_rcode = Rcodes::NXDOMAIN->value;
				return;
			}
			$tmp = substr($tmp, strlen($level[$l]) + 1);
		}
		
		for ($a = 0; $a < count($data); $a++) {
			$this->response->answers[$a] = new Answer();
			$this->response->answers[$a]->ttl = $ttl;
			$this->response->answers[$a]->rdata = $data[$a];
		}
		
		$this->response->ancount = count($this->response->answers);
	
	}
	
	public function handleRequest(Header $header): Header {
		
		$this->request = $header;
		$this->createDefaultResponse();
		
		$this->validateHeader();
		if ($this->response->flag_rcode != Rcodes::NOERROR->value) {
			return $this->response;
		}
		
		/**
		 * Multiple queries in a single packet can be accepted,
		 * but the Resolver only replies to the first query.
		 */
		$this->response->queries = $this->request->queries;
		 
		$this->validateQuery();
		if ($this->response->flag_rcode != Rcodes::NOERROR->value) {
			return $this->response;
		}
		
		$this->createAnswerForQuery();
		
		return $this->response;
		
	}

};
