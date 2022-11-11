<?php

$name = "test";

$a     = dns_get_record($name, DNS_A);
$aaaa  = dns_get_record($name, DNS_AAAA);
$ns    = dns_get_record($name, DNS_NS);
$mx    = dns_get_record($name, DNS_MX);
$txt   = dns_get_record($name, DNS_TXT);

printf("A :\t%s\n", $a[0]["ip"]);
printf("AAAA:\t%s\n", $aaaa[0]["ip"]);
printf("NS:\t%s\n", $ns[0]["target"]);
printf("MX:\t%s\n", $mx[0]["target"]);
printf("TXT:\t%s\n", $txt[0]["txt"]);
