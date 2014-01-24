<?php

/**
 * {@link DatabaseObject} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link DatabaseObject} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 24/01/2014
 */

/*=======================================================================================
 *																						*
 *								test_DatabaseObject.php									*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

//
// Style includes.
//
require_once( 'styles.inc.php' );


/*=======================================================================================
 *	RUNTIME SETTINGS																	*
 *======================================================================================*/
 
//
// Debug switches.
//
define( 'kDEBUG_PARENT', TRUE );


/*=======================================================================================
 *	CLASS SETTINGS																		*
 *======================================================================================*/
 
//
// Cast current class.
//
class MyClass extends OntologyWrapper\DatabaseObject
{
	protected function newCollection( $theOffsets ){ return $theOffsets; }
	protected function connectionOpen(){}
	protected function connectionClose(){}
}


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/
 
//
// Test class.
//
try
{
	//
	// Set data dictionary.
	//
	$_SESSION[ kSESSION_DDICT ]
		= new OntologyWrapper\connection\MemcachedCache(
			'memcached://localhost:11211' );
	$_SESSION[ kSESSION_DDICT ]->openConnection();
	$_SESSION[ kSESSION_DDICT ]->set( ':connection:protocol', kTAG_CONN_PROTOCOL );
	$_SESSION[ kSESSION_DDICT ]->set( ':connection:host', kTAG_CONN_HOST );
	$_SESSION[ kSESSION_DDICT ]->set( ':connection:port', kTAG_CONN_PORT );
	$_SESSION[ kSESSION_DDICT ]->set( ':connection:socket', kTAG_CONN_SOCKET );
	$_SESSION[ kSESSION_DDICT ]->set( ':connection:user', kTAG_CONN_USER );
	$_SESSION[ kSESSION_DDICT ]->set( ':connection:pass', kTAG_CONN_PASS );
	$_SESSION[ kSESSION_DDICT ]->set( ':connection:pid', kTAG_CONN_PID );
	$_SESSION[ kSESSION_DDICT ]->set( ':connection:name', kTAG_CONN_NAME );
	$_SESSION[ kSESSION_DDICT ]->set( ':connection:options', kTAG_CONN_OPTS );
	
	//
	// Test parent class.
	//
	if( kDEBUG_PARENT )
	{
		echo( "<h3>Parent class test</h3>" );
		//
		// Instantiate empty object.
		//
		echo( '<h4>Instantiate empty object</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		var_dump( $test );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
	
		//
		// Test set property.
		//
		echo( '<h4>Test set property<br /><i>should set the "$property" to "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->manageProperty( $test->property, "value" );'.kSTYLE_HEAD_POS );
		$test->manageProperty( $test->property, "value" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
	
		//
		// Test retrieve property.
		//
		echo( '<h4>Test retrieve property<br /><i>should return "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->manageProperty( $test->property );'.kSTYLE_HEAD_POS );
		$value = $test->manageProperty( $test->property );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
	
		//
		// Test modify property returning new value.
		//
		echo( '<h4>Test modify property returning new value<br /><i>should return "new"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->manageProperty( $test->property, "new" );'.kSTYLE_HEAD_POS );
		$value = $test->manageProperty( $test->property, "new" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
	
		//
		// Test modify property returning old value.
		//
		echo( '<h4>Test modify property returning old value<br /><i>should return "new"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->manageProperty( $test->property, "modified", TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->manageProperty( $test->property, "modified", TRUE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
	
		//
		// Test reset property returning old value.
		//
		echo( '<h4>Test reset property returning old value<br /><i>should return "modified"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->manageProperty( $test->property, FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->manageProperty( $test->property, FALSE, TRUE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
	
		//
		// Test reset property returning new value.
		//
		echo( '<h4>Test reset property returning new value<br /><i>should return NULL</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->manageProperty( $test->property, "new" );'.kSTYLE_HEAD_POS );
		$value = $test->manageProperty( $test->property, "new" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->manageProperty( $test->property, FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->manageProperty( $test->property, FALSE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Set offset by global identifier.
		//
		echo( '<h4>Set offset by global identifier<br /><i>should use kTAG_CONN_PROTOCOL</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[ ":connection:protocol" ] = "protocol";'.kSTYLE_HEAD_POS );
		$test[ ":connection:protocol" ] = "protocol";
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Set offset by native identifier.
		//
		echo( '<h4>Set offset by global native<br /><i>should use kTAG_CONN_PORT</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[ kTAG_CONN_PORT ] = 80;'.kSTYLE_HEAD_POS );
		$test[ kTAG_CONN_PORT ] = 80;
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test instantiate empty object.
		//
		echo( '<h4>Test instantiate empty object</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test instantiate with full DSN.
		//
		echo( '<h4>Test instantiate with full DSN</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		$dsn = "protocol://user:pass@host:80/path?opt1=val1&opt2=val2&opt3&opt4#fragment";
		echo( kSTYLE_HEAD_PRE );
		var_dump( $dsn );
		echo( kSTYLE_HEAD_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass($dsn);'.kSTYLE_HEAD_POS );
		$test = new MyClass($dsn);
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test instantiate with full parameters.
		//
		echo( '<h4>Test instantiate with full parameters<br /><i>path and fragment are not mapped to parameters in this class</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		$params = array( kTAG_CONN_PROTOCOL => "protocol",
						 kTAG_CONN_USER => "user",
						 kTAG_CONN_PASS => "pass",
						 kTAG_CONN_HOST => "host",
						 kTAG_CONN_PORT => 80,
						 kTAG_CONN_OPTS => array( 'opt1' => 'val1',
												  'opt2' => 'val2',
												  'opt3' => NULL,
												  'opt4' => NULL ) );
		echo( kSTYLE_HEAD_PRE );
		echo( '<pre>' );
		print_r( $params );
		echo( '</pre>' );
		echo( kSTYLE_HEAD_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass($params);'.kSTYLE_HEAD_POS );
		$test = new MyClass($params);
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
exit;

		//
		// Open connection.
		//
		echo( '<h4>Open connection<br /><i>DSN should be updated</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->openConnection();'.kSTYLE_HEAD_POS );
		$test->openConnection();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Change host.
		//
		echo( '<h4>Change host</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[ kTAG_CONN_HOST ] = ":_don\'t try this";'.kSTYLE_HEAD_POS );
		$test[ kTAG_CONN_HOST ] = ":_don\'t try this";
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Open connection.
		//
		echo( '<h4>Open connection<br /><i>DSN should be updated</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->openConnection();'.kSTYLE_HEAD_POS );
		$test->openConnection();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
	} echo( '<hr>' );
	
	//
	// Header.
	//
	if( kDEBUG_PARENT )
		echo( "<h3>Current class test</h3>" );

	//
	// Test instantiate object with DSN.
	//
	echo( '<h4>Test instantiate with full DSN</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	$dsn = "server-driver://server-user:server-pass@host:80?server-opt1=val1&server-opt2=val2&server-opt3&server-opt4";
	echo( kSTYLE_HEAD_PRE );
	var_dump( $dsn );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass($dsn);'.kSTYLE_HEAD_POS );
	$test = new MyClass($dsn);
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test database with DSN.
	//
	echo( '<h4>Test database with DSN<br /><i>should add server host and port</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	$dsn_db = "db-driver://db-user:db-pass@database-name?db-opt1=val1&db-opt2=val2&db-opt3&db-opt4";
	echo( kSTYLE_HEAD_PRE );
	var_dump( $dsn_db );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$db = $test->Database( $dsn_db );'.kSTYLE_HEAD_POS );
	$db = $test->Database( $dsn_db );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $db ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( '<pre>' );
	echo( (string) $error );
	echo( '</pre>' );
}

echo( "\nDone!\n" );

?>
