<?php

$name = "test";

$a     = dns_get_record($name, DNS_A);
$aaaa  = dns_get_record($name, DNS_AAAA);
$ns    = dns_get_record($name, DNS_NS);
$mx    = dns_get_record($name, DNS_MX);
$txt   = dns_get_record($name, DNS_TXT);

printf("Query Results ==> \n");

for ($i = 0; $i < count($a); $i++) {
	printf("A:\t%s\n", $a[$i]["ip"]);
}
for ($i = 0; $i < count($aaaa); $i++) {
	printf("AAAA:\t%s\n", $aaaa[$i]["ipv6"]);
}
for ($i = 0; $i < count($ns); $i++) {
	printf("NS:\t%s\n", $ns[$i]["target"]);
}
for ($i = 0; $i < count($mx); $i++) {
	printf("MX:\t%s\n", $mx[$i]["target"]);
}
for ($i = 0; $i < count($txt); $i++) {
	printf("TXT:\t%s\n", $txt[$i]["txt"]);
}
