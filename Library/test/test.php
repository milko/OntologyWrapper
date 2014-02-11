<?php
	
	//
	// Test stuff.
	//
	echo( (0 % 2)."\n" );
	echo( (1 % 2)."\n" );
	echo( (2 % 2)."\n" );
	echo( (3 % 2)."\n" );
	echo( (4 % 2)."\n" );
exit;
	
	//
	// Test parse_url.
	//
	$url = 'protocol://user:pass@host:80/name?parameter1=value1&parameter2=value2&parameter3&parameter4#fragment';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'protocol://`user`:`pass`@`host`:80/`name`?parameter1=value1&parameter2=value2&parameter3&parameter4#fragment';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'user:pass@host:80/name?parameter1=value1&parameter2=value2&parameter3&parameter4#fragment';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'driver://user:pass@server:3206/database?opt1=val1&opt2=val2#table';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'driver://user:pass@database?opt1=val1&opt2=val2';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'driver://database?opt1=val1&opt2=val2';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'driver://database';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'mongodb://user:pass@host1:27017/database';
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
	
	//
	// Test parse_url.
	//
	$url = 'scheme://user:pass@name';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );
	
	//
	// Test parse_url.
	//
	$url = 'scheme://name';
	var_dump( $url );
	$parts = parse_url( $url );
	var_dump( $parts );

?>