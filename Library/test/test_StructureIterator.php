<?php

/**
 * {@link StructureIterator} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link StructureIterator} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2015
 */

/*=======================================================================================
 *																						*
 *								test_StructureIterator.php								*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

//
// Local includes.
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
$base_url = 'http://localhost/gateway/Service.php';
 
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
	
	//
	// Instantiate root.
	//
	$pid = "template::checklist:cwr";
	$root = OntologyWrapper\Node::GetPidNode(
				$wrapper, "template::checklist:cwr", kQUERY_OBJECT );
	if( $root === NULL )
		exit( "Root [$pid] not found.<br>" );
	
	//
	// Instantiate iterator.
	//
	$i = new OntologyWrapper\StructureIterator( $wrapper, $root );
	
	//
	// Load all root children.
	//
	$c = $i->getChildren( $root );
	
	echo( 'Result<pre>' ); print_r( $c ); echo( '</pre>' ); echo( '<hr>' );
	echo( 'Graph<pre>' ); print_r( $i->getGraph() ); echo( '</pre>' ); echo( '<hr>' ); echo( '<hr>' );
	
	//
	// Load all first grandchildren.
	//
	$c = $i->getChildren( $c[ ":predicate:COLLECTION-OF" ][ 0 ] );
	
	echo( 'Result<pre>' ); print_r( $c ); echo( '</pre>' ); echo( '<hr>' );
	echo( 'Graph<pre>' ); print_r( $i->getGraph() ); echo( '</pre>' ); echo( '<hr>' ); echo( '<hr>' );
exit;


	//
	// Test CWR checklist all children.
	//
	echo( '<h4>Test CWR checklist all children</h4>' );
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
		kAPI_PARAM_NODE => 'struct::domain:trial',
		kAPI_PARAM_REF_COUNT => kAPI_PARAM_COLLECTION_UNIT
	);
	$request = "$base_url?op=".kAPI_OP_GET_NODE_STRUCT;
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
