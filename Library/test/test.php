<?php
	
	//
	// Test parse_url.
	//
	$url = 'protocol://user:pass@host/name?parameter1=value1&parameter2=value2&parameter3&parameter4#fragment';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Reconstitute URL.
	//
	$url = http_build_url( $parts );
	var_dump( $url );

?>