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
 *								LoadFromSQLArchive.php									*
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
if( $argc < 4 )
	exit( "Usage: <script.php> "
	// MySQLi://user:pass@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist
				."<Input SQL database DSN> "
	// eufgis
				."<Output SQL database table> "
	// mongodb://localhost:27017/BIOVERSITY
				."<mongo database DSN> "
	// neo4j://localhost:7474 or ""
				."[graph DSN] "
	// last identifier
				."[last ID (will select all those greater than)]\n" );				// ==>

//
// Init local storage.
//
$start = 0;
$limit = 600;
$backup = 24000;
$dc = $dc_out = $rs = NULL;

//
// Load arguments.
//
$db = $argv[ 1 ];
$table = $argv[ 2 ];
$mongo = $argv[ 3 ];
$graph = ( ($argc > 4) && strlen( $argv[ 4 ] ) ) ? $argv[ 4 ] : NULL;
$last = ( $argc > 5 ) ? $argv[ 5 ] : NULL;

//
// Inform.
//
echo( "\n==> Loading from archive ($table).\n" );

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
	echo( "  • Setting users.\n" );
	$wrapper->Users( $mongo );
	
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
	// Connect to input database.
	//
	echo( "  • Connecting to input SQL\n" );
	echo( "    - $db\n" );
	$dc = NewADOConnection( $db );
	$dc->Execute( "SET CHARACTER SET 'utf8'" );
	$dc->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Get records count.
	//
	$rec_count = $dc->GetOne( "SELECT COUNT(*) FROM `$table`" );
	$page = (int) ($rec_count / 100);
	$cur = $page;
	$do_backup = $backup;
	
	//
	// Import.
	//
	echo( "  • Importing\n" );
	$query = "SELECT * FROM `$table`";
	if( $last !== NULL )
		$query .= " WHERE `id` > $last";
	$query .= " ORDER BY `id` ASC LIMIT $start,$limit";
	$rs = $dc->execute( $query );
	while( $rs->RecordCount() )
	{
		//
		// Iterate page.
		//
		foreach( $rs as $record )
		{
			//
			// Import XML.
			//
			$class = $record[ 'class' ];
			$list = $class::Import( $wrapper, new SimpleXMLElement( $record[ 'xml' ] ) );
			
			//
			// Save records.
			// Note that we prevent the object from updating its related.
			//
			foreach( $list as $object )
				$object->commit( NULL, kFLAG_DEFAULT );
			
			//
			// Inform.
			//
			if( $cur-- <= 0 )
			{
				echo( '.' );
				$cur = $page;
			}
			
			//
			// Backup.
			//
			if( $do_backup-- <= 0 )
			{
				//
				// Backup database.
				//
				exec( 'rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"' );
				exec( 'mongodump --directoryperdb '
					.'--db "BIOVERSITY" '
					.'--out "/Library/WebServer/Library/OntologyWrapper/Library/backup/data"' );
				exec( 'rm "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.zip"' );
				exec( 'ditto -c -k --sequesterRsrc --keepParent '
					 .'"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"  '
					 .'"/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY.zip"' );
				exec( 'rm -R "/Library/WebServer/Library/OntologyWrapper/Library/backup/data/BIOVERSITY"' );
				
				//
				// Reset counter.
				//
				$do_backup = $backup;
			}
			
		} // Iterating page.
		
		//
		// Close recordset.
		//
		$rs->Close();
		$rs = NULL;
		
		//
		// Read next.
		//
		$start += $limit;
		$query = "SELECT * FROM `$table`";
		if( $last !== NULL )
			$query .= " WHERE `id` > $last";
		$query .= " ORDER BY `id` ASC LIMIT $start,$limit";
		$rs = $dc->execute( $query );
	
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
	if( $rs instanceof ADORecordSet )
		$rs->Close();
	if( $dc instanceof ADOConnection )
		$dc->Close();

} // FINALLY BLOCK.

?>
