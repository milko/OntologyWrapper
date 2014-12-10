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
 *									test_MatchUnits.php									*
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
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->Users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	
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

	//
	// Try matchUnits with genus pinus.
	//
	echo( '<h4>Try matchUnits with genus pinus</h4>' );
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
		kAPI_PARAM_LOG_TRACE => TRUE,
		kAPI_PARAM_CRITERIA => array
		(
			':taxon:genus' => array
			(
				kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_STRING,
				kAPI_PARAM_PATTERN => 'pinus',
				kAPI_PARAM_OPERATOR => array
				(
					kOPERATOR_CONTAINS,
					kOPERATOR_NOCASE
				)
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
