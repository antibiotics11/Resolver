<?php

namespace Resolver\System;

class Time {

  public static function setTimeZone(String $timezone = "GMT"): void {
    date_default_timezone_set($timezone);
  }

  public static function getTimeZone(): String {
    return date_default_timezone_get();
  }

  public static function DateYMD(String $separator = "-", ?int $timestamp = null): String {
    return date(sprintf("Y%sm%sd", $separator, $separator), $timestamp ?? time());
  }

  // Formats given timestamp as a date string in RFC2822 format. (current time in default)
  public static function DateRFC2822(?int $timestamp = null): String {
    return date(DATE_RFC2822, $timestamp ?? time());
  }

  // Formats given timestamp as a date string in RFC7231 format. (current time in default)
  public static function DateRFC7231(?int $timestamp = null): String {
    return date(DATE_RFC7231, $timestamp ?? time());
  }

};
