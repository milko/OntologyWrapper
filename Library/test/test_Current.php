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
	
	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();

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
var_dump( json_encode( $result[ 'results' ] ) );
exit;
*/

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
	echo( kSTYLE_HEAD_PRE );
	echo( 'Criteria:' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $param[ kAPI_PARAM_CRITERIA ] ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( json_encode( $param ) );
	echo( kSTYLE_DATA_POS );
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
