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
 *									test_Current.php									*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

//
// local includes.
//
require_once( 'local.inc.php' );

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
 *	TEST																				*
 *======================================================================================*/

//
// Init local storage.
//
$base_url = 'http://localhost/weblib/OntologyWrapper/Library/service/Service.php';
$base_url = 'http://localhost/services/Bioversity/Service.php';
 
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
	$meta = $wrapper->metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );

	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
/*
	//
	// Set offsets.
	//
	$collection = OntologyWrapper\UnitObject::ResolveCollectionByName( $wrapper, OntologyWrapper\UnitObject::kSEQ_NAME );
	$collection->createIndex( array( kTAG_OBJECT_OFFSETS => 1 ),
							  array( "name" => "OFFSETS" ) );
	
	exit;
*/

/*
	//
	// Perform query.
	//
	$criteria = array( 10 => '00001' );
//	$criteria = array( kTAG_NID => ':domain:forest://AUT/00001/1996;' );
//	$criteria = array( kTAG_NID => ':domain:organisation://http://fao.org/wiews:ITA303;' );
	$rs
		= OntologyWrapper\UnitObject::ResolveCollection(
			OntologyWrapper\UnitObject::ResolveDatabase(
				$wrapper ) )
					->matchAll( $criteria,
								kQUERY_OBJECT );
	
	//
	// Create serialiser.
	//
	$formatter
		= new OntologyWrapper\IteratorSerialiser(
			$rs,
			kAPI_RESULT_ENUM_DATA_MARKER,
			'en',
			kDOMAIN_FOREST,
			57 );
	
	//
	// Format.
	//
	$formatter->serialise();
	
	//
	// Show.
	//
	var_dump( $formatter->paging() );
	echo( '<hr />' );
	var_dump( $formatter->dictionary() );
	echo( '<hr />' );
	var_dump( $formatter->data() );
*/

/*
	//
	// Test getTagByIdentifier.
	//
	echo( '<h4>Test getTagByIdentifier</h4>' );
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
		kAPI_REQUEST_LANGUAGE => 'en',
		kAPI_PARAM_TAG => 'mcpd:ACCENUMB'
	//	kAPI_PARAM_TAG => array( 'mcpd:ACCENUMB', 'mcpd:ACCENAME' )
	//	kAPI_PARAM_TAG => 'pippo'
	);
	$request = "$base_url?op=".kAPI_OP_MATCH_TAG_BY_IDENTIFIER;
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
	// Try matchTagByLabel.
	//
	echo( '<h4>Try matchTagByLabel</h4>' );
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
		kAPI_PARAM_PATTERN => 'adm',
		kAPI_PARAM_OPERATOR => array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE ),
	//	kAPI_PARAM_REF_COUNT => array( kAPI_PARAM_COLLECTION_TERM,
	//								   kAPI_PARAM_COLLECTION_NODE )
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
	// Try matchUnits with full text search on "wild".
	//
	echo( '<h4>Try matchUnits with full text search on "wild"</h4>' );
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
			kAPI_PARAM_FULL_TEXT_OFFSET => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_TEXT,
				kAPI_PARAM_PATTERN => 'wild'
			)
		),
		kAPI_PARAM_DOMAIN => ':domain:organisation',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_COLUMN
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
	// Try matchUnits statistics with full text search on "wild".
	//
	echo( '<h4>Try matchUnits statistics with full text search on "wild"</h4>' );
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
			kAPI_PARAM_FULL_TEXT_OFFSET => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_TEXT,
				kAPI_PARAM_PATTERN => 'wild'
			)
		),
		kAPI_PARAM_DOMAIN => ':domain:organisation',
		kAPI_PARAM_STAT => 's1',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_STAT
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
	// Test getNodeForm.
	//
	echo( '<h4>Test getNodeForm</h4>' );
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
	//	kAPI_PARAM_NODE => 'form::taxon',
		kAPI_PARAM_NODE => 'form::forms',
		kAPI_PARAM_REF_COUNT => kAPI_PARAM_COLLECTION_UNIT
	);
	$request = "$base_url?op=".kAPI_OP_GET_NODE_FORM;
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
*/
	
/*
	//
	// Try getUnit formatted.
	//
	echo( '<h4>Try getUnit formatted</h4>' );
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
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_FORMAT,
	//	kAPI_PARAM_ID => ':domain:forest://AUT/00008/1996;'
		kAPI_PARAM_ID => ':domain:hh-assessment://RAJASTHAN,BADMER,CHOHTAN,DHIRASAR:BA0201/2012;'
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
*/
	
/*
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
		kAPI_PARAM_LOG_REQUEST => TRUE,
		kAPI_PARAM_CRITERIA => array
		(
			':taxon:genus' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'brassica',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				),
				kAPI_PARAM_OFFSETS => array( '#256.#fc' )
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
		kAPI_PARAM_LOG_REQUEST => TRUE,
	//	kAPI_PARAM_CRITERIA => Array(),
		kAPI_PARAM_CRITERIA => array
		(
			':shape-disp' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_SHAPE,
				kAPI_PARAM_SHAPE => array(
					kTAG_TYPE => 'Rect',
					kTAG_GEOMETRY => array( array( 12, 45 ),
										    array( 18, 50 ) ) )
			)
		),
		kAPI_PARAM_GROUP => Array(),
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_FORMAT,
	//	kAPI_PARAM_SHAPE => array( kTAG_TYPE => 'Rect',
	//							   kTAG_GEOMETRY => array( array( 12, 45 ),
	//													   array( 18, 50 ) ) ),
		kAPI_PARAM_SHAPE_OFFSET => kTAG_GEO_SHAPE_DISP
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
	// Resolve taxon groups.
	//
	$genus = 'Triticum';
	$species = 'durum';
	$list = OntologyWrapper\Term::ResolveTaxonGroup( $wrapper, $genus, $species );
	var_dump( $list );
	echo( '<hr>' );
	echo( '<hr>' );
*/

/*
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
		kAPI_PARAM_CRITERIA => Array(),
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
var_dump( 'http://localhost/services/Bioversity/Service.php?op=matchUnits&ln=en&pr=%7B%22log-request%22:true,%22criteria%22:%5B%5D,%22grouping%22:%22#9%22%7D' );
var_dump( $request );
var_dump( urldecode( '%7B%22log-request%22:true,%22criteria%22:%5B%5D,%22grouping%22:%22#9%22%7D' ) );
var_dump( json_encode( $param ) );
exit;
*/

/*
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
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_CRITERIA => array
		(
			':location:country' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
				kAPI_PARAM_TERM => 'iso:3166:1:alpha-3:ITA'
			)
		),
		kAPI_PARAM_GROUP => array( '@255.@fc', '@255.@10c' )
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
*/

/*
	//
	// Test domains list and count.
	//
	echo( '<h4>Test domains list and count</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Request:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	$request = "$base_url?op=".kAPI_OP_LIST_DOMAINS;
	$request .= ('&'.kAPI_REQUEST_LANGUAGE.'=en');
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
*/

/*
	//
	// Test getUser.
	//
	echo( '<h4>Test getUser</h4>' );
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
	//	kAPI_PARAM_ID => array( 'gatewayadmin', 'gatewayadmin' ),
		kAPI_PARAM_ID => ':domain:individual://ITA406/pgrdiversity.bioversityinternational.org:milko;',
	//	kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_RECORD
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_FORMAT
	//	kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_MARKER
	);
	$request = "$base_url?op=".kAPI_OP_GET_USER;
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
*/

/*
	//
	// Try matchUnits record.
	//
	echo( '<h4>Try matchUnits record</h4>' );
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
		kAPI_PARAM_CRITERIA => array
		(
			':taxon:genus' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'brassica',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
			)
		),
		kAPI_PAGING_LIMIT => 2,
		kAPI_PARAM_DOMAIN => ':domain:hh-assessment',
		kAPI_PARAM_SHAPE_OFFSET => kTAG_GEO_SHAPE_DISP,
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_MARKER
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
*/

/*
	//
	// Try matchUnits statistics.
	//
	echo( '<h4>Try matchUnits statistics</h4>' );
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
		kAPI_PARAM_CRITERIA => Array(),
		kAPI_PARAM_DOMAIN => kDOMAIN_HH_ASSESSMENT,
		kAPI_PARAM_STAT => 'abdh-species-07',
		kAPI_PARAM_DATA => kAPI_RESULT_ENUM_DATA_STAT
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
*/

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
