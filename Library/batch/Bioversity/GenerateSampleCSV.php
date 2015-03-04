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
 *									GenerateSampleCSV.php								*
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
				":taxon:crop:category", ":taxon:annex-1", ":location:country" );
$query = array( kTAG_DOMAIN => kDOMAIN_SAMPLE_COLLECTED );
$header
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
		":taxon:annex-1" => "Annex-1",
		":location:country" => "Country",
		":location:admin-1" => "Administrative unit 1",
		":location:admin-2" => "Administrative unit 2",
		":location:admin-3" => "Administrative unit 3",
		":location:locality" => "Locality",
		":location:site:latitude" => "Latitude",
		":location:site:longitude" => "Longitude" );

//
// Load arguments.
//
$mongo = $argv[ 1 ];
$graph = ( ($argc > 2) && strlen( $argv[ 2 ] ) ) ? $argv[ 2 ] : NULL;

//
// Inform.
//
echo( "\n==> Generating collected sample CSVs.\n" );

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
	echo( "  • Generate samples\n" );
	$file = new SplFileObject( kPATH_LIBRARY_ROOT."/data/samples.csv", "w" );
	$file->fputcsv( array_values( $header ) );
	$rs = $collection_units->matchAll( $query, kQUERY_OBJECT );
	foreach( $rs as $object )
	{
		//
		// Load object.
		//
		$record = Array();
		foreach( $header as $tag => $label )
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
