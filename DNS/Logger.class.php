<?php

namespace Resolver;

class Logger {

	public static function systime(): String {
		return substr(date(DATE_RFC2822), 0, -5).date("T");
	}
	
	public static function write_log(String $dir, String $message): void {
	
		$time = Logger::systime();
		$filename = $dir.DIRECTORY_SEPARATOR.date("Y-m-d").".log";
		$message = $time."\t".$message."\r\n";
		
		$fh = fopen($filename, "a");
		fwrite($fh, $message);
		fclose($fh);
	
	}


};
