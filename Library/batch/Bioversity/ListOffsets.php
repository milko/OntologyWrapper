<?php

/**
 * List unit offsets.
 *
 * This file contains a script that will return all unit offsets of tags which have a count
 * greater than the provided number.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 02/12/2014
 */

/*=======================================================================================
 *																						*
 *									ListOffsets.php										*
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
		exit( "usage: php -f ListIndexes.php "
			 ."<database> "						// mongodb://localhost:27017/BIOVERSITY
			 ."<minimum count>\n" );												// ==>
	
	//
	// Get arguments.
	//
	$database = $argv[ 1 ];
	$count = (int) $argv[ 2 ];
	
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
		new OntologyWrapper\MongoDatabase( $database ) );
	$wrapper->Entities(
		new OntologyWrapper\MongoDatabase( $database ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase( $database ) );

	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Resolve collection.
	//
	$coll_tags = $wrapper->resolveCollection( OntologyWrapper\Tag::kSEQ_NAME );
	$coll_units = $wrapper->resolveCollection( OntologyWrapper\UnitObject::kSEQ_NAME );
	
	//
	// Select tags.
	//
	$query = array( kTAG_UNIT_COUNT => array( '$gt' => $count ) );
	$sort = array( kTAG_UNIT_COUNT => -1 );
	$rs = $coll_tags->matchAll( $query );
	$rs->sort( $sort );
	
	//
	// Iterate tags.
	//
	foreach( $rs as $tag )
	{
		//
		// Show offsets.
		//
		$offsets = $tag[ kTAG_UNIT_OFFSETS ];
		if( is_array( $offsets ) )
		{
			//
			// Show tag.
			//
			echo( "\n// \"".$tag[ kTAG_NID ]."\" (".$tag[ kTAG_UNIT_COUNT ].")\n" );
			
			//
			// Iterate offsets.
			//
			foreach( $offsets as $offset )
			{
				//
				// Get records count.
				//
				$count = $coll_units->matchOne( array( kTAG_OBJECT_OFFSETS => $offset ),
												kQUERY_COUNT );
				
				//
				// Display count.
				//
				echo( "// [$count]\n" );
				
				//
				// Display command.
				//
				echo( "db.getCollection(\"_units\").ensureIndex( "
					 ."{ \"$offset\" : 1 }, "
					 ."{ name : \"ix_$offset\", "
					 ."sparse : true } )\n" );
			
			} // Iterating offsets.
		
		} // Has offsets.
	
	} // Iterating tags.

	echo( "\nDone!\n" );
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
}

?>
