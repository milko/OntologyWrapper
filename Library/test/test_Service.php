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
	
///*
	//
	// Map.
	//
	echo( '<h4>Map</h4>' );
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
		kAPI_PAGING_LIMIT => 5,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_CRITERIA => array
		(
			'mcpd:ACCENUMB' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'IG 20056',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			)
		),
		kAPI_PARAM_DOMAIN => ':domain:accession',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_MARKER,
		kAPI_PARAM_SHAPE_OFFSET => kTAG_GEO_SHAPE,
		kAPI_PARAM_SHAPE => array( kTAG_TYPE => 'Polygon',
								   kTAG_GEOMETRY => array(
								   	array(
								   		array( -31, 72 ), array( 65, 72 ),
								   		array( 65, 30 ), array( -31, 30 ),
								   		array( -31, 72 ) ) ) )
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
//*/
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
	echo( '<h4>Try getNodeEnumerations for ":type:entity:100"</h4>' );
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
	echo( '<h4>Try getNodeEnumerations for ":type:entity:120"</h4>' );
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
	// Test single field no data (group).
	//
	echo( '<h4>Test single field no data (group)</h4>' );
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
			)
		),
		kAPI_PARAM_GROUP => Array()
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

	//
	// Test single field with data (group).
	//
	echo( '<h4>Test single field with data (group)</h4>' );
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
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'one',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			)
		),
		kAPI_PARAM_GROUP => Array()
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

	//
	// Test single field with data indexed (group).
	//
	echo( '<h4>Test single field with data indexed (group)</h4>' );
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
			':test:feature2' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_RANGE,
				kAPI_PARAM_RANGE_MIN => 10,
				kAPI_PARAM_RANGE_MAX => 20,
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_ERANGE
				)
			)
		),
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

	//
	// Test single field with multiple offsets (group).
	//
	echo( '<h4>Test single field with multiple offsets (group)</h4>' );
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
			':test:feature4' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'FIVE',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			)
		),
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

	//
	// Test single field with multiple offsets indexed (group).
	//
	echo( '<h4>Test single field with multiple offsets indexed (group)</h4>' );
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
			':test:feature5' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'six',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			)
		),
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

	//
	// Test multiple fields with multiple offsets indexed (group).
	//
	echo( '<h4>Test multiple fields field with multiple offsets indexed (group)</h4>' );
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
			':test:feature4' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'FIVE',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			),
			':test:feature5' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'six',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			)
		),
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

	//
	// Test multiple fields with multiple offsets and no value (group).
	//
	echo( '<h4>Test multiple fields field with multiple offsets and no value (group)</h4>' );
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
			':test:feature4' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING
			),
			':test:feature5' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING
			)
		),
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

	//
	// Test two fields (group).
	//
	echo( '<h4>Test two fields (group)</h4>' );
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
			':test:feature4' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'FIVE',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			)
		),
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

	//
	// Test two fields with values (group).
	//
	echo( '<h4>Test two fields with values (group)</h4>' );
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
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'one',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			),
			':test:feature4' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'FIVE',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			)
		),
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

	//
	// Test two levels group.
	//
	echo( '<h4>Test two levels group</h4>' );
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
			':type:entity' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM
			)
		),
		kAPI_PARAM_GROUP => ':type:entity'
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

	//
	// Test three levels group.
	//
	echo( '<h4>Test three levels group</h4>' );
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
		kAPI_PARAM_GROUP => array( ':kind:entity', ':type:entity' )
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

	//
	// Map.
	//
	echo( '<h4>Map</h4>' );
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
		kAPI_PAGING_LIMIT => 5,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_CRITERIA => array
		(
			':location:country' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM
			)
		),
		kAPI_PARAM_DOMAIN => ':domain:forest',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_MARKER,
		kAPI_PARAM_SHAPE_OFFSET => kTAG_GEO_SHAPE,
		kAPI_PARAM_SHAPE => array( kTAG_TYPE => 'Rect',
								   kTAG_GEOMETRY => array( array( 9, 45 ),
														   array( 18, 50 ) ) )
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

	//
	// Many fields.
	//
	echo( '<h4>Many fields</h4>' );
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
	/*	kAPI_PARAM_SHAPE => array( kTAG_TYPE => 'Point',
								   kTAG_GEOMETRY => array( array( 10, 20 ), 10000 ) ),*/
	/*	kAPI_PARAM_SHAPE => array( kTAG_TYPE => 'Circle',
								   kTAG_GEOMETRY => array( array( 10, 20 ), 10 / 3959 ) ),*/
	/*	kAPI_PARAM_SHAPE => array( kTAG_TYPE => 'Rect',
								   kTAG_GEOMETRY => array( array( -10, 30 ),
																 array( -11, 29 ) ) ),*/
		kAPI_PARAM_SHAPE
			=> array( kTAG_TYPE => 'Polygon',
					  kTAG_GEOMETRY
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
		kAPI_PARAM_SHAPE_OFFSET => ':shape',
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
		kAPI_PAGING_LIMIT => 3,
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_LOG_TRACE => TRUE,
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
		),
		kAPI_PARAM_DOMAIN => ':domain:organisation',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_RECORD
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
	
	//
	// Try getUnit.
	//
	echo( '<h4>Try getUnit</h4>' );
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
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_ID => ':domain:forest://AUT/00023/1990;'
	);
	$request = "$base_url?op=".kAPI_OP_GET_UNIT;
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
	// Map.
	//
	echo( '<h4>Map</h4>' );
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
		kAPI_PARAM_CRITERIA => array
		(
			'fcu:unit:ownership/:predicate:SCALE-OF/eufgis:UnitOwnership' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
				kAPI_RESULT_ENUM_TERM => array( 'eufgis:UnitOwnership:Private' )
			)
		),
		kAPI_PARAM_DOMAIN => ':domain:forest',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_MARKER,
		kAPI_PARAM_SHAPE_OFFSET => kTAG_GEO_SHAPE,
		kAPI_PARAM_SHAPE
				=> array( kTAG_TYPE => 'Polygon',
						  kTAG_GEOMETRY
							=> array( array( array( 13, 48.5 ),
											 array( 13, 45.5 ),
											 array( 17, 45.5 ),
											 array( 17, 48.5 ),
											 array( 13, 48.5 ) ) ) )
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_UNITS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
	$request .= ('&'.kAPI_REQUEST_PARAMETERS.'='.urlencode( json_encode( $param ) ));
	echo( htmlspecialchars($request) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' );
	print_r( array( kAPI_REQUEST_OPERATION => kAPI_OP_MATCH_UNITS,
					kAPI_REQUEST_LANGUAGE => 'en',
					kAPI_REQUEST_PARAMETERS => $param ) );
	echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$response = file_get_contents( $request );
	$result = json_decode( $response, TRUE );
	echo( '<pre>' ); print_r( $result ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( 'Coordinates: ' );
	$min_lon = $min_lat = 200;
	$max_lon = $max_lat = -200;
	foreach( $result[ kAPI_RESPONSE_RESULTS ] as $item )
	{
		if( $item[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ][ 0 ] < $min_lon )
			$min_lon = $item[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ][ 0 ];
		if( $item[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ][ 0 ] > $max_lon )
			$max_lon = $item[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ][ 0 ];
		if( $item[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ][ 1 ] < $min_lat )
			$min_lat = $item[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ][ 1 ];
		if( $item[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ][ 1 ] > $max_lat )
			$max_lat = $item[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ][ 1 ];
	}
	echo( "[$min_lon] [$min_lat] [$max_lon $max_lat]" );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
	
	//
	// Get FCU structure offsets flattened.
	//
	echo( '<h4>Get FCU structure offsets flattened</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$offsets = $wrapper->collectStructureOffsets( 'struct:fcu:unit', 0 );
	echo( '<pre>' ); print_r( $offsets ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Get FCU structure offsets structured.
	//
	echo( '<h4>Get FCU structure offsets structured</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$offsets = $wrapper->collectStructureOffsets( 'struct:fcu', 1 );
	echo( '<pre>' ); print_r( $offsets ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Get FCU structure tags structured.
	//
	echo( '<h4>Get FCU structure tags structured</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	$offsets = $wrapper->collectStructureOffsets( 'struct:fcu', 2 );
	echo( '<pre>' ); print_r( $offsets ); echo( '</pre>' );
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
