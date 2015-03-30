<?php

/**
 * Count offsets.
 *
 * This file contains a script to count the unit offset occurrences.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 19/01/2015
 */

/*=======================================================================================
 *																						*
 *									CountOffsets.php									*
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
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

//
// MAIN.
//
try
{
	//
	// Check arguments.
	//
	if( $argc < 2 )
		exit( "usage: php -f CountOffsets.php "
			 ."<database>\n" );														// ==>
	
	//
	// Get arguments.
	//
	$database = $argv[ 1 ];
	
	//
	// Parse database.
	//
	$parts = parse_url( $database );
	
	//
	// Inform.
	//
	echo( "\n"
		 ."Host:             ".$parts[ 'host' ]."\n"
		 ."Port:             ".$parts[ 'port' ]."\n"
		 ."Database:         ".substr( $parts[ 'path' ], 1 )."\n"
		 ."\n" );
	
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
		new OntologyWrapper\MongoDatabase( $database ) );
	$wrapper->users(
		new OntologyWrapper\MongoDatabase( $database ) );
	$wrapper->units(
		new OntologyWrapper\MongoDatabase( $database ) );

	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Resolve collection.
	//
	$collection
		= $wrapper->resolveCollection(
			OntologyWrapper\UnitObject::kSEQ_NAME )
				->connection();
	
	//
	// Set pipeline.
	//
	$pipeline = [];
	$pipeline[] = [ '$project' => [ kTAG_OBJECT_OFFSETS => 1 ] ];
	$pipeline[] = [ '$unwind' => '$'.kTAG_OBJECT_OFFSETS ];
	$pipeline[] = [ '$group' => [ kTAG_NID => '$'.kTAG_OBJECT_OFFSETS ] ];
	$pipeline[] = [ '$sort' => [ kTAG_NID => 1 ] ];
var_dump( json_encode( $pipeline ) );
exit;
	$cursor = $collection->aggregateCursor( $pipeline,
											array( 'allowDiskUse' => TRUE,
												   'maxTimeMS' => 360000 ) );
	
	//
	// Display result.
	//
	$offsets = Array();
	foreach( $cursor as $record )
		$offsets[ $record[ kTAG_NID ] ] = 0;
	
	//
	// Get counts.
	//
	foreach( array_keys( $offsets ) as $offset )
		$offsets[ $offset ]
			= $collection->find( array( kTAG_OBJECT_OFFSETS => $offset ) )
				->count();
	
	//
	// Sort results.
	//
	arsort( $offsets );
print_r( $offsets );
exit;
	
/*
	//
	// Resolve collection.
	//
	$collection
		= $wrapper->resolveCollection(
			OntologyWrapper\UnitObject::kSEQ_NAME );
	
	//
	// Iterate offsets.
	//
	$offsets = Array();
	$rs
		= $collection->matchAll(
			[],
			kQUERY_ARRAY,
			[ kTAG_OBJECT_TAGS => TRUE ] );
	while( $rs->count() )
	{
		//
		// Collect offsets.
		//
		foreach( $rs as $record )
		{
			if( array_key_exists( kTAG_OBJECT_TAGS, $record ) )
			{
				foreach( $record[ kTAG_OBJECT_TAGS ] as $offset )
				{
					if( array_key_exists( $offset, $offsets ) )
						$offsets[ $offset ]++;
					else
						$offsets[ $offset ] = 1;
				}
			}
		}
	}
	
	//
	// Sort offsets.
	//
	arsort( $offsets );
var_dump( $offsets );
exit;
*/
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
}

echo( "\nDone!\n" );

?>
