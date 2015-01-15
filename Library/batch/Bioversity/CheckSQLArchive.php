<?php

/**
 * SQL archive check procedure.
 *
 * This file contains routines to check content of the XML SQL archive.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/01/2015
 */

/*=======================================================================================
 *																						*
 *									CheckSQLArchive.php									*
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
if( $argc < 3 )
	exit( "Usage: <script.php> "
	// MySQLi://user:pass@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist
				."<Input SQL database DSN> "
	// eufgis
				."<Output SQL database table>\n" );									// ==>

//
// Init local storage.
//
$start = 0;
$limit = 1000;
$dc = $dc_out = $rs = NULL;

//
// Load arguments.
//
$db = $argv[ 1 ];
$table = $argv[ 2 ];

//
// Inform.
//
echo( "\n==> Checking archive ($table).\n" );

//
// Try.
//
try
{
	//
	// Set environment.
	//
	libxml_use_internal_errors( TRUE );
	
	//
	// Connect to input database.
	//
	echo( "  • Connecting to input SQL\n" );
	echo( "    - $db\n" );
	$dc = NewADOConnection( $db );
	$dc->Execute( "SET CHARACTER SET 'utf8'" );
	$dc->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Check.
	//
	echo( "  • Checking\n" );
	$query = "SELECT * FROM `$table` LIMIT $start,$limit";
	$rs = $dc->execute( $query );
	while( $rs->RecordCount() )
	{
		//
		// Iterate page.
		//
		foreach( $rs as $record )
		{
			//
			// Validate XML.
			//
			$xml = simplexml_load_string( $record[ 'xml' ] );
			if( ! $xml )
			{
				echo( "\n=======================================================\n" );
				echo( $record[ 'id' ]."\n" );
				echo( "\n=======================================================\n" );
				foreach( libxml_get_errors() as $error )
					echo "\t", $error->message;
				libxml_clear_errors();
			
			} // Found error.
			
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
		$query = "SELECT * FROM `$table` LIMIT $start,$limit";
		$rs = $dc->execute( $query );
	
	} // Records left.

	echo( "\nDone!\n" );

} // TRY BLOCK.

//
// Catch exceptions.
//
catch( Exception $error )
{
	echo( "\n" );
	echo( $error->getMessage() );
	print_r( $error->getTrace() );

} // CATCH BLOCK.

//
// FINAL BLOCK.
//
finally
{
	libxml_use_internal_errors( FALSE );
	if( $rs instanceof ADORecordSet )
		$rs->Close();
	if( $dc instanceof ADOConnection )
		$dc->Close();

} // FINALLY BLOCK.

?>
