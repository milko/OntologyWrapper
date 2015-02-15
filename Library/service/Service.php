<?php
	
/**
 * On server.
 *
 * This file contains a service implementing the {@link ServiceObject} class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/05/2014
 */

/*=======================================================================================
 *																						*
 *										Service.php										*
 *																						*
 *======================================================================================*/

/**
 * Global includes.
 *
 * This include file contains default path definitions and an
 * {@link __autoload() autoloader} used to automatically include referenced classes in this
 * library.
 */
require_once( 'includes.inc.php' );

/**
 * Local includes.
 *
 * This include file contains local definitions.
 */
require_once( 'local.inc.php' );

/**
 * API definition.
 *
 * This include file contains API definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );

/**
 * Session definition.
 *
 * This include file contains session definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );

/**
 * Functions definition.
 *
 * This include file contains functions definitions.
 */
require_once( kPATH_LIBRARY_ROOT."/Functions.php" );


/*=======================================================================================
 *	WRAPPER DEFINITION																	*
 *======================================================================================*/

try
{
	//
	// Instantiate data dictionary.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( kSTANDARDS_DDICT_HOST,
						  kSTANDARDS_DDICT_PORT ) ) );

	//
	// Set metadata database.
	//
	$wrapper->metadata(
		new OntologyWrapper\MongoDatabase(
			kSTANDARDS_METADATA_DB ) );

	//
	// Set entities database.
	//
	$wrapper->users(
		new OntologyWrapper\MongoDatabase(
			kSTANDARDS_ENTITIES_DB ) );

	//
	// Set units database.
	//
	$wrapper->units(
		new OntologyWrapper\MongoDatabase(
			kSTANDARDS_UNITS_DB ) );
	
	//
	// Set graph database.
	//
	if( kGRAPH_DO )
		$wrapper->graph(
			new OntologyWrapper\Neo4jGraph(
				kSTANDARDS_GRAPH_DB ) );

	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
}
catch( Exception $error )
{
	//
	// Init response.
	//
	$response = array( kAPI_RESPONSE_STATUS
					=> array( kAPI_STATUS_STATE
						=> kAPI_STATE_ERROR ) );
	
	//
	// Set status code.
	//
	if( ($tmp = $error->getCode()) !== NULL )
		$response[ kAPI_RESPONSE_STATUS ][ kAPI_STATUS_CODE ] = $tmp;
	
	//
	// Set status message.
	//
	if( ($tmp = $error->getMessage()) !== NULL )
		$response[ kAPI_RESPONSE_STATUS ][ kAPI_STATUS_MESSAGE ] = $tmp;
	
	//
	// Send header.
	//
	header( 'Content-type: application/json' );
	
	exit( JsonEncode( $response ) );												// ==>
}

/*=======================================================================================
 *	SERVICE EXECUTION																	*
 *======================================================================================*/

//
// Instantiate service.
//
$service = new OntologyWrapper\Service( $wrapper );

//
// Execute service.
//
$service->handleRequest();

?>
