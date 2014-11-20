<?php

/**
 * SQL collecting mission archive procedure.
 *
 * This file contains routines to load missions from an SQL database and archive it as
 * XML the archive database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/09/2014
 */

/*=======================================================================================
 *																						*
 *									FixTrialDates.php									*
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
	// MySQLi://user:pass@localhost/bioversity?socket=/tmp/mysql.sock&persist
				."<Input SQL database DSN>\n" );									// ==>

//
// Init local storage.
//
$dc_in = $dc_out = $rs = NULL;

//
// Init base query.
//
$base_query = "SELECT `ID`, `:trial:start`, `:trial:end` from `trials` "
			 ."WHERE( (`:trial:start` IS NOT NULL) OR (`:trial:end` IS NOT NULL) )";

//
// Load arguments.
//
$db_in = $argv[ 1 ];

//
// Inform.
//
echo( "\n==> Fixing dates in trials.\n" );

//
// Try.
//
try
{
	//
	// Connect to input database.
	//
	echo( "  • Connecting to input SQL\n" );
	echo( "    - $db_in\n" );
	$dc_in = NewADOConnection( $db_in );
	$dc_in->Execute( "SET CHARACTER SET 'utf8'" );
	$dc_in->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Scan trials.
	//
	echo( "  • Scanning\n" );
	$rs = $dc_in->execute( $base_query );
	foreach( $rs as $record )
	{
		//
		// Init local storage.
		//
		$old_dates = array( $record[ ':trial:start' ], $record[ ':trial:end' ] );
		$new_dates = array( NULL, NULL );
		
		//
		// Scan dates.
		//
		foreach( array_keys( $old_dates ) as $key )
		{
			//
			// Check date.
			//
			if( $old_dates[ $key ] !== NULL )
			{
				//
				// Intercept dashes.
				//
				if( count( $items = explode( '-', $old_dates[ $key ] ) ) > 1 )
				{
					//
					// Init loop storage-
					//
					$y = $m = $d = NULL;
					
					//
					// Handle day.
					//
					if( count( $items ) == 3 )
					{
						$i = 0;
						$d = $items[ $i++ ];
						$m = $items[ $i++ ];
						$y = $items[ $i++ ];
					}
					
					//
					// Handle month.
					//
					elseif( count( $items ) == 2 )
					{
						$i = 0;
						$m = $items[ $i++ ];
						$y = $items[ $i++ ];
					}
					
					//
					// Normalise month.
					//
					if( ! ctype_digit( $m ) )
						$d = $m = NULL;
					
					//
					// Format date.
					//
					if( $d !== NULL )
						$d = sprintf( "%02d", (int) $d );
					if( $m !== NULL )
						$m = sprintf( "%02d", (int) $m );
					
					//
					// Set date.
					//
					$new_dates[ $key ] = "$y$m$d";
			
				} // Is dashed.
				
				else
					$new_dates[ $key ] = $old_dates[ $key ];
			
			} // Has date.
			
			else
				$new_dates[ $key ] = $old_dates[ $key ];
		
		} // Iterating dates.
		
		//
		// Normalise dates.
		//
		if( (strlen( $new_dates[ 1 ] ) == 4)
		 && (strlen( $new_dates[ 0 ] ) > strlen( $new_dates[ 1 ] ))
		 && (substr( $new_dates[ 0 ], 0, 4 ) == substr( $new_dates[ 1 ], 0, 4 )) )
			$new_dates[ 1 ] .= '12';
		
		//
		// Update record.
		//
		$query = "UPDATE `trials` SET ";
		$query = Array();
		if( $new_dates[ 0 ] !== NULL )
			$query[] = "`:trial:start` = '".$new_dates[ 0 ]."'";
		if( $new_dates[ 1 ] !== NULL )
			$query[] = "`:trial:end` = '".$new_dates[ 1 ]."'";
		$query = "UPDATE `trials` SET ".implode( ', ', $query );
		$query .= (" WHERE `ID` = ".$record[ 'ID' ]);
		$ws = $dc_in->execute( $query );
		$ws->Close();
		$ws = NULL;
	
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
	if( $ws instanceof ADORecordSet )
		$ws->Close();
	if( $dc_in instanceof ADOConnection )
		$dc_in->Close();

} // FINALLY BLOCK.

?>
