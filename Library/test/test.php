<?php
	
	//
	// Test parse_url.
	//
	$url = 'protocol://user:pass@host/name?parameter1=value1&parameter2=value2&parameter3&parameter4#fragment';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'user:pass@host/name?parameter1=value1&parameter2=value2&parameter3&parameter4#fragment';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'mongodb://sf2.example.com,ny1.example.com';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'memcached://user:pass@host#persistent_id';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = '#persistent_id';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = '/path/to/socket#persistent_id';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = '/path/to/socket?option=value';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );

?>