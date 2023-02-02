<?php

namespace Resolver\Record;
use Resolver\Message\Types;

class Record {

	public String $ZONE  = "";
	public int    $TTL   = 0;
	
	public Array  $A     = [];
	public Array  $NS    = [];
	public Array  $CNAME = [];
	public Array  $MX    = [];
	public Array  $TXT   = [];
	public Array  $AAAA  = [];
	
	
	public static Array $zones = [];
	
	public static function loadFiles(String $directory): void {
	
		$files = scandir($directory);
		
		foreach ($files as $file) {	
			if (strcmp($file, ".") === 0 || strcmp($file, "..") === 0) {
				continue;
			}
			$contents = self::parseFile($directory."/".$file);
			self::$zones[$contents->ZONE] = $contents;
		}
	
	}
	
	public static function parseFile(String $file): Record {
	
		$record = new Record();
	
		$contents = \file($file);
		for ($i = 0; $i < count($contents); $i++) {
			$line = $contents[$i];
			
			if (strlen($line) < 2) {
				continue;
			}
			
			$p = explode("\t", $line);
			if (count($p) != 2 && count($p) != 3) {
				continue;
			}
				
			$type = trim(strtoupper($p[0]));
			$value = trim(strtolower($p[1]));
				
			switch ($type) {
			case "#" :
				break;
				
			case "ZONE" :
				$record->ZONE = trim(strtolower($value));
				break;
				
			case "TTL" :
				$ttl = (int)$value;
				$record->TTL = ($ttl) ? $ttl : 0;
				break;
				
			case Types::A->name :
			case Types::NS->name :
			case Types::CNAME->name :
			case Types::MX->name :
			case Types::TXT->name :
			case Types::AAAA->name :
				$name = $value;
				$value = trim(strtolower($p[2]));
				if (strcmp($name, "@") === 0 || strcmp($name, $record->ZONE) === 0) {
					$name = $record->ZONE;
				} else {
					$name = sprintf("%s.%s", $name, $record->ZONE);
				}
				
				$record->{$type}[$name][] = $value;
				break;
			
			};
			
		}
		
		return $record;
	
	}

};
