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
 *									GenerateChecklistCSV.php							*
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
// Domain definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

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
// Init local storage.
//
$enums = array( ":taxon:crop", ":taxon:crop:group",
				":taxon:crop:category", ":inventory:admin" );
$query1 = array( kTAG_DOMAIN => kDOMAIN_INVENTORY );
$header1
	= array(
		kTAG_AUTHORITY => "Authority",
		kTAG_COLLECTION => "Collection",
		kTAG_IDENTIFIER => "Identifier",
		kTAG_VERSION => "Version",
		":taxon:epithet" => "Taxon",
		":taxon:genus" => "Genus",
		":taxon:species" => "Species",
		":taxon:infraspecies" => "Infraspecies",
		":taxon:crop" => "Crop",
		":taxon:crop:group" => "Group",
		":taxon:crop:category" => "Category",
		":inventory:admin" => "Administrative unit" );
$query2 = array( kTAG_DOMAIN => kDOMAIN_CHECKLIST );
$header2
	= array(
		kTAG_AUTHORITY => "Authority",
		kTAG_COLLECTION => "Collection",
		kTAG_IDENTIFIER => "Identifier",
		kTAG_VERSION => "Version",
		":taxon:epithet" => "Taxon",
		":taxon:genus" => "Genus",
		":taxon:species" => "Species",
		":taxon:infraspecies" => "Infraspecies",
		":taxon:crop" => "Crop",
		":taxon:crop:group" => "Group",
		":taxon:crop:category" => "Category",
		":inventory:admin" => "Administrative unit" );

//
// Load arguments.
//
$mongo = $argv[ 1 ];
$graph = ( ($argc > 2) && strlen( $argv[ 2 ] ) ) ? $argv[ 2 ] : NULL;

//
// Inform.
//
echo( "\n==> Generating CWR inventory and checklist CSVs.\n" );

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
	$wrapper->metadata( $mongo );
	
	//
	// Set units.
	//
	echo( "  • Setting units.\n" );
	$wrapper->units( $mongo );
	
	//
	// Set entities.
	//
	echo( "  • Setting users.\n" );
	$wrapper->users( $mongo );
	
	//
	// Check graph database.
	//
	if( $graph !== NULL )
	{
		//
		// Set graph database.
		//
		echo( "  • Setting graph.\n" );
		$wrapper->graph(
			new OntologyWrapper\Neo4jGraph(
				$graph ) );
	
	} // Use graph database.
	
	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Resolve terms collection.
	//
	$collection_terms
		= OntologyWrapper\Term::ResolveCollection(
			OntologyWrapper\Term::ResolveDatabase(
				$wrapper, TRUE ) );
	
	//
	// Resolve units collection.
	//
	$collection_units
		= OntologyWrapper\UnitObject::ResolveCollection(
			OntologyWrapper\UnitObject::ResolveDatabase(
				$wrapper, TRUE ) );
	
	//
	// Generate inventories.
	//
	echo( "  • Generate inventories\n" );
	$file = new SplFileObject( kPATH_LIBRARY_ROOT."/data/cwr_inventory.csv", "w" );
	$file->fputcsv( array_values( $header1 ) );
	$rs = $collection_units->matchAll( $query1, kQUERY_OBJECT );
	foreach( $rs as $object )
	{
		//
		// Load object.
		//
		$record = Array();
		foreach( $header1 as $tag => $label )
		{
			//
			// Handle data.
			//
			if( $object->offsetExists( $tag ) )
			{
				//
				// Resolve enumerations.
				//
				if( in_array( $tag, $enums ) )
				{
					//
					// Load term.
					//
					$term
						= $collection_terms->matchOne(
							array( kTAG_NID => $object->offsetGet( $tag ) ),
							kQUERY_OBJECT | kQUERY_ASSERT );
					
					//
					// Resolve language.
					//
					$value
						= OntologyWrapper\OntologyObject::SelectLanguageString(
							$term->offsetGet( kTAG_LABEL ), kSTANDARDS_LANGUAGE );
				}
				
				else
					$value = $object->offsetGet( $tag );
				
				//
				// Set value.
				//
				$record[] = $value;
			}
			else
				$record[] = NULL;
		}
		
		//
		// Write record.
		//
		$file->fputcsv( $record );
	}
	
	//
	// Generate checklists.
	//
	echo( "  • Generate checklists\n" );
	$file = new SplFileObject( kPATH_LIBRARY_ROOT."/data/cwr_checklist.csv", "w" );
	$file->fputcsv( array_values( $header2 ) );
	$rs = $collection_units->matchAll( $query2, kQUERY_OBJECT );
	foreach( $rs as $object )
	{
		//
		// Load object.
		//
		$record = Array();
		foreach( $header2 as $tag => $label )
		{
			//
			// Handle data.
			//
			if( $object->offsetExists( $tag ) )
			{
				//
				// Resolve enumerations.
				//
				if( in_array( $tag, $enums ) )
				{
					//
					// Load term.
					//
					$term
						= $collection_terms->matchOne(
							array( kTAG_NID => $object->offsetGet( $tag ) ),
							kQUERY_OBJECT | kQUERY_ASSERT );
					
					//
					// Resolve language.
					//
					$value
						= OntologyWrapper\OntologyObject::SelectLanguageString(
							$term->offsetGet( kTAG_LABEL ), kSTANDARDS_LANGUAGE );
				}
				
				else
					$value = $object->offsetGet( $tag );
				
				//
				// Set value.
				//
				$record[] = $value;
			}
			else
				$record[] = NULL;
		}
		
		//
		// Write record.
		//
		$file->fputcsv( $record );
	}

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
