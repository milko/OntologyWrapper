<?php

/**
 * {@link Service} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link Service} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 13/01/2014
 */

/*=======================================================================================
 *																						*
 *									test_Service.php									*
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

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

//
// Domain definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );

//
// Operators.
//
require_once( kPATH_DEFINITIONS_ROOT."/Operators.inc.php" );

//
// API.
//
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );


/*=======================================================================================
 *	RUNTIME SETTINGS																	*
 *======================================================================================*/
 
//
// Debug switches.
//
define( 'kDEBUG_PARENT', FALSE );


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

session_start();

//
// Init local storage.
//
$base_url = 'http://localhost/weblib/OntologyWrapper/Library/service/Service.php';
 
//
// Test class.
//
try
{
	//
	// Instantiate data dictionary.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( 'localhost', 11211 ) ) );

	//
	// Set databases.
	//
	$meta = $wrapper->Metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->Entities(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
/*	
	//
	// Drop database.
	//
	$meta->drop();
	
	//
	// Load database.
	//
	$command = 'unzip '
			  .'/Library/WebServer/Library/OntologyWrapper/Library/backup/data/TEST.zip '
			  .'-d /Library/WebServer/Library/OntologyWrapper/Library/backup/data';
	exec( $command );
	$command = 'mongorestore --directoryperdb '
			  .'/Library/WebServer/Library/OntologyWrapper/Library/backup/data/';
	exec( $command );
	$command = 'rm -r /Library/WebServer/Library/OntologyWrapper/Library/backup/data/TEST';
	exec( $command );
*/	
	
	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();

	//
	// Test parent class.
	//
	if( kDEBUG_PARENT )
	{
		echo( "<h3>Parent class test</h3>" );
	} echo( '<hr>' );
	
	//
	// Header.
	//
	if( kDEBUG_PARENT )
		echo( "<h3>Current class test</h3>" );

	//
	// Try empty URL.
	//
	echo( '<h4>Try empty URL</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$request = $base_url;
	echo( $request );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Try wrong command.
	//
	echo( '<h4>Try wrong command</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$request = "$base_url?op=unknown";
	echo( $request );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Try ping.
	//
	echo( '<h4>Try ping</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$request = "$base_url?op=".kAPI_OP_PING;
	echo( $request );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Try list parameters.
	//
	echo( '<h4>Try list parameters</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$request = "$base_url?op=".kAPI_OP_LIST_CONSTANTS;
	echo( $request );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Try list operators.
	//
	echo( '<h4>Try list operators</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$request = "$base_url?op=".kAPI_OP_LIST_OPERATORS;
	echo( $request );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Try list reference counts.
	//
	echo( '<h4>Try list reference counts</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$request = "$base_url?op=".kAPI_OP_LIST_REF_COUNTS;
	echo( $request );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Try matchTagLabels containing "count".
	//
	echo( '<h4>Try matchTagLabels containing "count"</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PAGING_LIMIT => 50,
		kAPI_PARAM_PATTERN => 'count',
		kAPI_PARAM_OPERATOR => array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE )
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_TAG_LABELS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Try matchTagLabels containing "count" with unit reference count.
	//
	echo( '<h4>Try matchTagLabels containing "count" with unit reference count</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PAGING_LIMIT => 50,
		kAPI_PARAM_PATTERN => 'count',
		kAPI_PARAM_OPERATOR => array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE ),
		kAPI_PARAM_REF_COUNT => kAPI_PARAM_COLLECTION_UNIT
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_TAG_LABELS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Try matchTagLabels ending with "name" with term and node reference count.
	//
	echo( '<h4>Try matchTagLabels ending with "name" with term and node reference count</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PAGING_LIMIT => 50,
		kAPI_PARAM_PATTERN => 'name',
		kAPI_PARAM_OPERATOR => array( kOPERATOR_SUFFIX, kOPERATOR_NOCASE ),
		kAPI_PARAM_REF_COUNT => array( kAPI_PARAM_COLLECTION_TERM,
									   kAPI_PARAM_COLLECTION_NODE )
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_TAG_LABELS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Try matchTermLabels containing "italia".
	//
	echo( '<h4>Try matchTermLabels containing "italia"</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PAGING_LIMIT => 6,
		kAPI_PARAM_PATTERN => 'italia',
		kAPI_PARAM_OPERATOR => array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE )
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_TERM_LABELS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Try matchTagByLabel containing "count".
	//
	echo( '<h4>Try matchTagByLabel containing "count"</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PAGING_LIMIT => 50,
		kAPI_PARAM_PATTERN => 'count',
		kAPI_PARAM_OPERATOR => array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE )
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_TAG_BY_LABEL;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Try matchTagByLabel containing "count" with unit reference count.
	//
	echo( '<h4>Try matchTagByLabel containing "count" with unit reference count</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PAGING_LIMIT => 50,
		kAPI_PARAM_PATTERN => 'count',
		kAPI_PARAM_OPERATOR => array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE ),
		kAPI_PARAM_REF_COUNT => kAPI_PARAM_COLLECTION_UNIT
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_TAG_BY_LABEL;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Try matchTagByLabel ending with "name" with term and node reference count.
	//
	echo( '<h4>Try matchTagByLabel ending with "name" with term and node reference count</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PAGING_LIMIT => 50,
		kAPI_PARAM_PATTERN => 'name',
		kAPI_PARAM_OPERATOR => array( kOPERATOR_SUFFIX, kOPERATOR_NOCASE ),
		kAPI_PARAM_REF_COUNT => array( kAPI_PARAM_COLLECTION_TERM,
									   kAPI_PARAM_COLLECTION_NODE )
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_TAG_BY_LABEL;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Try matchTermByLabel containing "italia".
	//
	echo( '<h4>Try matchTermByLabel containing "italia"</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PAGING_LIMIT => 6,
		kAPI_PARAM_PATTERN => 'italia',
		kAPI_PARAM_OPERATOR => array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE )
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_TERM_BY_LABEL;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Try getTagEnumerations for ":type:entity".
	//
	echo( '<h4>Try getTagEnumerations for ":type:entity"</h4>' );
	$term = ':type:entity';
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PAGING_LIMIT => 300,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_TAG => $term
	);
	$request = "$base_url?op=".kAPI_OP_GET_TAG_ENUMERATIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Try getTagEnumerations for ":type:entity" recursed.
	//
	echo( '<h4>Try getTagEnumerations for ":type:entity" recursed</h4>' );
	$term = ':type:entity';
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_RECURSE => TRUE,
		kAPI_PARAM_TAG => $term
	);
	$request = "$base_url?op=".kAPI_OP_GET_TAG_ENUMERATIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Try getNodeEnumerations for ":type:entity:100".
	//
	echo( '<h4>Try getTagEnumerations for ":type:entity:100"</h4>' );
	$node = $result[ kAPI_RESPONSE_RESULTS ][ 0 ][ kAPI_RESULT_ENUM_NODE ];
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PAGING_LIMIT => 300,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_NODE => $node
	);
	$request = "$base_url?op=".kAPI_OP_GET_NODE_ENUMERATIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Try getNodeEnumerations for ":type:entity:120".
	//
	echo( '<h4>Try getTagEnumerations for ":type:entity:120"</h4>' );
	$node = $result[ kAPI_RESPONSE_RESULTS ][ 1 ][ kAPI_RESULT_ENUM_NODE ];
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PAGING_LIMIT => 300,
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_NODE => $node
	);
	$request = "$base_url?op=".kAPI_OP_GET_NODE_ENUMERATIONS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

//
// TEST.
//
echo( '<h4>TEST</h4>' );
echo( kSTYLE_TABLE_PRE );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_HEAD_PRE );
echo( 'Request:' );
echo( kSTYLE_HEAD_POS );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_HEAD_PRE );
$param = array
(
//	kAPI_PAGING_LIMIT => 10,
	kAPI_PARAM_LOG_REQUEST => TRUE,
	kAPI_PARAM_CRITERIA => array
	(
		':test:feature1' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING
		),
		':test:feature2' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_RANGE,
			kAPI_PARAM_RANGE_MIN => 10,
			kAPI_PARAM_RANGE_MAX => 20,
			kAPI_PARAM_OPERATOR => array
			(
				kOPERATOR_IRANGE
			)
		),
		':test:feature1/:predicate:SCALE-OF/:test:scale1' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
			kAPI_PARAM_PATTERN => 'two',
			kAPI_PARAM_OPERATOR => array
			(
				kOPERATOR_CONTAINS,
				kOPERATOR_NOCASE
			)
		),
		':test:feature1/:predicate:SCALE-OF/:test:scale2' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
			kAPI_PARAM_PATTERN => 21
		),
		':test:feature2/:predicate:SCALE-OF/:test:scale1' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
			kAPI_PARAM_PATTERN => 'three',
			kAPI_PARAM_OPERATOR => array
			(
				kOPERATOR_CONTAINS,
				kOPERATOR_NOCASE
			)
		),
		':test:feature2/:predicate:SCALE-OF/:test:scale2' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
			kAPI_PARAM_PATTERN => 'FOUR',
			kAPI_PARAM_OPERATOR => array
			(
				kOPERATOR_CONTAINS,
				kOPERATOR_NOCASE
			)
		),
		':test:feature2/:predicate:SCALE-OF/:test:scale3' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
			kAPI_RESULT_ENUM_TERM => array( ':test:enumeration:2' )
		),
		':test:feature4' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
			kAPI_PARAM_PATTERN => 'test string five',
			kAPI_PARAM_OPERATOR => array
			(
				kOPERATOR_EQUAL
			)
		),
		':test:feature5' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
			kAPI_PARAM_PATTERN => 'test string six',
			kAPI_PARAM_OPERATOR => array
			(
				kOPERATOR_EQUAL
			)
		),
		':unit:version' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_RANGE,
			kAPI_PARAM_RANGE_MIN => '19770101',
			kAPI_PARAM_RANGE_MAX => '20140527',
			kAPI_PARAM_OPERATOR => array
			(
				kOPERATOR_IRANGE
			)
		),
		':type:entity' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
			kAPI_RESULT_ENUM_TERM => array( ':type:entity:123', ':type:entity:125' )
		),
		':unit:domain' => array
		(
			kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM
		)
	),
/*	kAPI_PARAM_SHAPE => array( kTAG_SHAPE_TYPE => 'Point',
							   kTAG_SHAPE_GEOMETRY => array( array( 10, 20 ), 10000 ) ),*/
/*	kAPI_PARAM_SHAPE => array( kTAG_SHAPE_TYPE => 'Circle',
							   kTAG_SHAPE_GEOMETRY => array( array( 10, 20 ), 10 / 3959 ) ),*/
/*	kAPI_PARAM_SHAPE => array( kTAG_SHAPE_TYPE => 'Rect',
							   kTAG_SHAPE_GEOMETRY => array( array( -10, 30 ),
							   								 array( -11, 29 ) ) ),*/
	kAPI_PARAM_SHAPE
		=> array( kTAG_SHAPE_TYPE => 'Polygon',
				  kTAG_SHAPE_GEOMETRY
				  	=> array( array( array( 12.8199,42.8422 ),
				  					 array( 12.8207,42.8158 ),
				  					 array( 12.8699,42.8166 ),
				  					 array( 12.8678,42.8398 ),
				  					 array( 12.8199,42.8422 ) ),
				  			  array( array( 12.8344,42.8347 ),
				  					 array( 12.8348,42.8225 ),
				  					 array( 12.857,42.8223 ),
				  					 array( 12.8566,42.8332 ),
				  					 array( 12.8344,42.8347 ) ) ) ),
	kAPI_PARAM_SHAPE_OFFSET => ':geo',
	kAPI_PARAM_GROUP => kTAG_DOMAIN
);
$request = "$base_url?op=".kAPI_OP_MATCH_UNITS;
$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
echo( htmlspecialchars($request) );
echo( kSTYLE_HEAD_POS );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_DATA_PRE );
$response = file_get_contents( $request );
$result = json_decode( $response, TRUE );
echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
echo( kSTYLE_DATA_POS );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_TABLE_POS );
echo( '<hr>' );
echo( '<hr>' );
exit;

	//
	// Try matchUnits with string search on ":name" contains "olive".
	//
	echo( '<h4>Try matchUnits with string search on ":name" contains "olive"</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$param = array
	(
		kAPI_PAGING_LIMIT => 10,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_COLLECTION => kAPI_PARAM_COLLECTION_ENTITY,
		kAPI_PARAM_CRITERIA => array
		(
			':name' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'olive',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			)
		)
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_UNITS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( '<pre>'.$error->xdebug_message.'</pre>' );
}

echo( "\nDone!\n" );

?>
