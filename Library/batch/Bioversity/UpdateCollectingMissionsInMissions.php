<?php

/**
 * SQL archive load procedure.
 *
 * This file contains routines to load ovjects from the XML SQL archive.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 29/08/2014
 */

/*=======================================================================================
 *																						*
 *						UpdateCollectingMissionsInMissions.php							*
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
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

//
// Predicate definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Predicates.inc.php" );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );

//
// Functions.
//
require_once( kPATH_LIBRARY_ROOT."/Functions.php" );

/**
 * ADODB library.
 *
 * This include file contains the ADODB library definitions.
 */
require_once( "/Library/WebServer/Library/adodb/adodb.inc.php" );

/**
 * ADODB iterators.
 *
 * This include file contains the ADODB library iterators.
 */
require_once( "/Library/WebServer/Library/adodb/adodb-iterator.inc.php" );

/**
 * ADODB exceptions.
 *
 * This include file contains the ADODB library exceptions.
 */
require_once( "/Library/WebServer/Library/adodb/adodb-exceptions.inc.php" );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 2 )
	exit( "Usage: <script.php> "
	// mongodb://localhost:27017/BIOVERSITY
				."<mongo database DSN> "
	// neo4j://localhost:7474 or ""
				."[graph DSN]\n" );													// ==>

//
// Load arguments.
//
$mongo = $argv[ 1 ];
$graph = ( ($argc > 2) && strlen( $argv[ 2 ] ) ) ? $argv[ 2 ] : NULL;

//
// Init local storage.
//
$start = 0;
$limit = 100;

//
// Inform.
//
echo( "\n==> Add collecting missions to missions.\n" );

//
// Try.
//
try
{
	//
	// Inform.
	//
	echo( "  • Creating wrapper.\n" );
	
	//
	// Instantiate data dictionary.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( 'localhost', 11211 ) ) );
	
	//
	// Inform.
	//
	echo( "  • Creating databases.\n" );
	
	//
	// Instantiate database.
	//
	$mongo
		= new OntologyWrapper\MongoDatabase(
			"$mongo?connect=1" );
	
	//
	// Set metadata.
	//
	echo( "  • Setting metadata.\n" );
	$wrapper->Metadata( $mongo );
	
	//
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$wrapper->Units( $mongo );
	
	//
	// Set entities.
	//
	echo( "  • Setting entities.\n" );
	$wrapper->Entities( $mongo );
	
	//
	// Check graph database.
	//
	if( $graph !== NULL )
	{
		//
		// Set graph database.
		//
		echo( "  • Setting graph.\n" );
		$wrapper->Graph(
			new OntologyWrapper\Neo4jGraph(
				$graph ) );
	
	} // Use graph database.
	
	//
	// Load data dictionary.
	//
	echo( "  • Loading data dictionary.\n" );
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Resolve collection.
	//
	$collection
		= OntologyWrapper\UnitObject::ResolveCollection(
			OntologyWrapper\UnitObject::ResolveDatabase(
				$wrapper ) );
	
	//
	// Iterate missions.
	//
	$missions
		= $collection->matchAll(
			array( kTAG_DOMAIN => OntologyWrapper\Mission::kDEFAULT_DOMAIN ),
			kQUERY_OBJECT );
	echo( "  • Executing...\n" );
	foreach( $missions as $mission )
	{
		//
		// Init local storage.
		//
		$list = Array();
		
		//
		// Select collecting missions.
		//
		$query = array( kTAG_DOMAIN => OntologyWrapper\CollectingMission::kDEFAULT_DOMAIN,
						$wrapper->getSerial( ':mission:identifier' )
							=> $mission->offsetGet( ':mission:identifier' ) );
		$collecting = $collection->matchAll( $query, kQUERY_OBJECT );
		
		//
		// Sort by collecting date.
		//
		$collecting->sort(
			array( $wrapper->getSerial( ':mission:collecting:start' ) => 1 ) );
		
		//
		// Iterate collecting missions.
		//
		foreach( $collecting as $object )
		{
			//
			// Init local storage.
			//
			$element = Array();
			
			//
			// Set label.
			//
			if( $object->offsetExists( ':mission:collecting:identifier' ) )
				$element[ $wrapper->getSerial( ':struct-label' ) ]
					= $object->offsetGet( ':mission:collecting:identifier' );
			
			//
			// Set mission.
			//
			$element[ $wrapper->getSerial( ':mission' ) ]
				= $mission->offsetGet( kTAG_NID );
			
			//
			// Set start date.
			//
			if( $object->offsetExists( ':mission:collecting:start' ) )
				$element[ $wrapper->getSerial( ':mission:collecting:start' ) ]
					= $object->offsetGet( ':mission:collecting:start' );
		
			//
			// Set end date.
			//
			if( $object->offsetExists( ':mission:collecting:end' ) )
				$element[ $wrapper->getSerial( ':mission:collecting:end' ) ]
					= $object->offsetGet( ':mission:collecting:end' );
		
			//
			// Set region.
			//
			if( $object->offsetExists( ':location:region' ) )
				$element[ $wrapper->getSerial( ':location:region' ) ]
					= $object->offsetGet( ':location:region' );
		
			//
			// Set administrative unit.
			//
			if( $object->offsetExists( ':location:admin' ) )
				$element[ $wrapper->getSerial( ':location:admin' ) ]
					= $object->offsetGet( ':location:admin' );
		
			//
			// Set country.
			//
			if( $object->offsetExists( ':location:country' ) )
				$element[ $wrapper->getSerial( ':location:country' ) ]
					= $object->offsetGet( ':location:country' );
			
			//
			// Add element.
			//
			if( count( $element ) )
				$list[] = $element;
		
		} // Iterating collecting missions.
		
		//
		// Update mission.
		//
		if( count( $list ) )
		{
			$mission->offsetSet( ':collecting:missions', $list );
			$mission->commit();
		
		} // Has collecting missions.
		
		//
		// Inform.
		//
		echo( '.' );
	
	} // Iterating missions.

	echo( "\nDone!\n" );

} // TRY BLOCK.

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
	print_r( $error->getTrace() );

} // CATCH BLOCK.

?>
