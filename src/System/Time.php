<?php

namespace Resolver\System;

class Time {

	public static function setTimezone(String $timezone): String {
		
		date_default_timezone_set($timezone);
		return date_default_timezone_get();
		
	}

	public static function DateYMD(String $separator = "-"): String {

		return date("Y".$separator."m".$separator."d");
	
	}

	public static function DateRFC2822(): String {

		return substr(date(DATE_RFC2822), 0, -5).date("T");
	
	}

};
