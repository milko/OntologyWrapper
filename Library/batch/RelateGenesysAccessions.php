<?php

/**
 * Accession mapping script.
 *
 * This file contains routines to map accession records in the Genesys Bioversity SQL
 * database with the accessions in the PGRDG MongoDB database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 31/03/2015
 */

/*=======================================================================================
 *																						*
 *								RelateGenesysAccessions.php								*
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

/**
 * Settings.
 */
define( 'kDO_CLIMATE', TRUE );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 3 )
	exit( "Usage: <script.php> "
	// MySQLi://root:Bogomil@localhost/bioversity_genesys?socket=/tmp/mysql.sock&persist
				."<Input SQL database DSN> "
	// mongodb://localhost:27017/PGRDG
				."<mongo database DSN>\n" );										// ==>

//
// Init local storage.
//
$start = 0;
$limit = 1000;

//
// Init base query.
//
$base_query = "SELECT * FROM `accessions` LIMIT $start,$limit";

//
// Load arguments.
//
$db_sql = $argv[ 1 ];
$db_mongo = $argv[ 2 ];

//
// Init cursors.
//
$rs_sql = $rs_mongo = NULL;

//
// Inform.
//
echo( "\n==> Relating Genesys accessions with PGRDG accessions.\n" );

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
	// Instantiate database.
	//
	$mongo
		= new OntologyWrapper\MongoDatabase(
			"$db_mongo?connect=1" );
	
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
	// Load data dictionary.
	//
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
	// Connect to SQL database.
	//
	echo( "  • Connecting to SQL database\n" );
	echo( "    - $db_sql\n" );
	$db = NewADOConnection( $db_sql );
	$db->Execute( "SET CHARACTER SET 'utf8'" );
	$db->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Iterate accessions.
	//
	$rs_sql = $db->execute( "SELECT * FROM `accessions` LIMIT $start,$limit" );
	while( $rs_sql->RecordCount() )
	{
		//
		// Iterate page.
		//
		foreach( $rs_sql as $record )
		{
			//
			// Build Mongo query.
			//
			$criteria
				= array(
					$wrapper->getSerial( ":inventory:GENESYS", TRUE )
						=> (string) $record[ 'ID' ] );
			
			//
			// Update accession.
			//
			$accession = $collection->matchOne( $criteria, kQUERY_OBJECT );
			if( $accession!== NULL )
				$ok = $db->execute( "UPDATE `accessions` SET `unit` = "
								   .'0x'.bin2hex( $accession->offsetGet( kTAG_NID ) )
								   ." WHERE( `ID` = "
								   .$record[ 'ID' ]
								   ." )" );
		
		} // Iterating page.
		
		//
		// Show progress.
		//
		echo( "." );
		
		//
		// Close recordset.
		//
		$rs_sql->Close();
		$rs_sql = NULL;
			
		//
		// Next page.
		//
		$start += $limit;
		$rs_sql = $db->execute( "SELECT * FROM `accessions` LIMIT $start,$limit" );
	
	} // Records left.

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

//
// FINAL BLOCK.
//
finally
{
	if( $rs_sql instanceof ADORecordSet )
		$rs_sql->Close();
	if( $db instanceof ADOConnection )
		$db->Close();

} // FINALLY BLOCK.

?>
