<?php

/**
 * MCPD (CWR) load procedure.
 *
 * This file contains routines to update the objects of the database, this script will scan
 * all objects that do not feature the {@link kTAG_ENUM_FULL_TEXT} property, load them and
 * update them.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/08/2014
 */

/*=======================================================================================
 *																						*
 *								UpdateForestGenusSpecies.php							*
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


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 2 )
	exit( "Usage: "
		 ."script.php "
		 ."[mongo database DSN] "	// mongodb://localhost:27017/PGRDG
		 ."[graph DSN].\n" );		// neo4j://localhost:7474						// ==>

//
// Load arguments.
//
$mongo = $argv[ 1 ];
$graph = ( $argc > 2 ) ? $argv[ 2 ] : NULL;
 
//
// Inform.
//
echo( "\n==> Loading genus and species for all forests.\n" );

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
	echo( "  • Creating database.\n" );
	
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
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Resolve collection.
	//
	$collection
		= OntologyWrapper\Tag::ResolveCollection(
			OntologyWrapper\Tag::ResolveDatabase( $wrapper ) );
	
	//
	// Collect tags.
	//
	$criteria
		= array( kTAG_NID => array(
			'$in' => array(
				':taxon:genus', ':taxon:species', ':taxon:epithet' ) ) );
	$rs = $collection->matchAll( $criteria, kQUERY_ARRAY );
	foreach( $rs as $record )
		$tags[ $record[ kTAG_NID ] ] = $record[ kTAG_ID_SEQUENCE ];
	
	//
	// Resolve collection.
	//
	$collection
		= OntologyWrapper\UnitObject::ResolveCollection(
			OntologyWrapper\UnitObject::ResolveDatabase( $wrapper ) );
	
	//
	// Read.
	//
	echo( "  • Scanning...\n" );
	$criteria = array( kTAG_DOMAIN => kDOMAIN_FOREST );
	$rs = $collection->matchAll( $criteria, kQUERY_OBJECT );
	
	//
	// Inform.
	//
	echo( "    ".$rs->count()." records.\n" );
	
	//
	// Iterate.
	//
	$lists = array( 'fcu:population', 'fcu:unit:species' );
	foreach( $rs as $object )
	{
		//
		// Iterate structures.
		//
		foreach( $lists as $list )
		{
			//
			// Get property.
			//
			$struct = $object[ $list ];
			if( is_array( $struct ) )
			{
				foreach( $struct as $key => $value )
				{
					//
					// Get elements.
					//
					$taxon = $value[ $tags[ ':taxon:epithet' ] ];
				
					//
					// Get genus and species.
					//
					$pos = strpos( $taxon, ' ' );
					if( $pos !== FALSE )
					{
						$genus = substr( $taxon, 0, $pos );
						$species = substr( $taxon, $pos + 1 );
					}
					else
					{
						$genus = $taxon;
						$species = NULL;
					}
				
					//
					// Set genus and species.
					//
					if( strlen( $genus ) )
						$struct[ $key ][ $tags[ ':taxon:genus' ] ] = $genus;
					if( strlen( $species ) )
						$struct[ $key ][ $tags[ ':taxon:species' ] ] = $species;
				}
			
				//
				// Update object.
				//
				$object[ $list ] = $struct;
			}
		}
		
		//
		// Save object.
		//
		$object->commit();
	
	} // Iterated.

	echo( "\nDone!\n" );
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
	print_r( $error->getTrace() );
}

//
// FINAL BLOCK.
//
finally
{
}

?>
