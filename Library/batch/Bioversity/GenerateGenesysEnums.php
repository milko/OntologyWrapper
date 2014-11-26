<?php

/**
 * Generate Genesys enumerations.
 *
 * This file contains routines to generate the Genesys enumeration SQL records.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Utilities
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 26/11/2014
 */

/*=======================================================================================
 *																						*
 *								GenerateGenesysEnums.php								*
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
// Inform.
//
echo( "\n==> Generating Genesys enumerations.\n" );

//
// Parse arguments.
//
if( $argc < 2 )
	exit( "Usage: <script.php> "
	// MySQLi://user:pass@localhost/bioversity_genesys?socket=/tmp/mysql.sock&persist
				."<SQL database DSN>\n" );											// ==>

//
// Load arguments.
//
$db_in = $argv[ 1 ];

//
// Set tables.
//
$table_in = 'types';
$table_out = 'enums';

/*=======================================================================================
 *	TRY																					*
 *======================================================================================*/

//
// Try.
//
try
{
	//
	// Init local storage.
	//
	$rs = $rs_out = $dc = NULL;
	
	//
	// Connect to database.
	//
	echo( "  • Connecting to SQL\n" );
	echo( "    - $db_in\n" );
	$dc = NewADOConnection( $db_in );
	$dc->Execute( "SET CHARACTER SET 'utf8'" );
	$dc->SetFetchMode( ADODB_FETCH_ASSOC );
	$rs = $dc->Execute( "TRUNCATE TABLE `$table_out`" );
	$rs->Close();
	$rs = NULL;

	//
	// Inform.
	//
	echo( "\n==> Loading enumerations.\n" );
	
	//
	// Iterate types.
	//
	$query = "SELECT * FROM `$table_in`";
	$rs = $dc->execute( $query );
	foreach( $rs as $record )
	{
		//
		// Set type.
		//
		$type = $record[ 'ID' ];
		
		//
		// Load options.
		//
		$options = parseOptions( $record[ 'Options' ] );
		foreach( $options as $key => $value )
		{
			//
			// Build query.
			//
			$query = "INSERT INTO `$table_out` "
					."VALUES( $type, "
					.'0x'.bin2hex( $key ).', '
					.'0x'.bin2hex( $value ).' )';
			
			//
			// Insert record.
			//
			$rs_out = $dc->Execute( $query );
			$rs_out->Close();
			$rs_out = NULL;
		
		} // Iterating options.
	
	} // Scanning input table.

	echo( "\nDone!\n" );

} // TRY BLOCK.

/*=======================================================================================
 *	CATCH																				*
 *======================================================================================*/

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
	print_r( $error->getTrace() );

} // CATCH BLOCK.

/*=======================================================================================
 *	FINALLY																				*
 *======================================================================================*/

//
// FINAL BLOCK.
//
finally
{
	if( $rs instanceof ADORecordSet )
		$rs->Close();
	if( $rs_out instanceof ADORecordSet )
		$rs_out->Close();
	if( $dc instanceof ADOConnection )
		$dc->Close();

} // FINALLY BLOCK.

/*=======================================================================================
 *																						*
 *										FUNCTIONS										*
 *																						*
 *======================================================================================*/

	/**
	 * Parse options.
	 *
	 * This function will parse the provided options and return a list of options structured
	 * as an array with key as key and value as label.
	 *
	 * @param string				$theOptions			Options.
	 *
	 * @return array				The parsed options.
	 */
	function parseOptions( $theOptions )
	{
		//
		// Init local storage.
		//
		$options = Array();
		
		//
		// Parse blocks.
		//
		foreach( explode( ';', $theOptions ) as $block )
		{
			//
			// Parse key.
			//
			$pos = strpos( $block, ',' );
			if( $pos )
			{
				//
				// Get elements.
				//
				$key = trim( substr( $block, 0, $pos ) );
				$value = trim( substr( $block, $pos + 1 ) );
				
				//
				// Set enumeration.
				//
				$options[ $key ] = $value;
			
			} // Found divider.
			
			else
				throw new Exception( "Invalid option\n[$block]\n" );				// ==>
		
		} // Scanning blocks.
		
		return $options;															// ==>

	} // getTerms.


?>
