<?php

/**
 * Connect descriptors and ancillary in trust database.
 *
 * This file contains routines to connect the descriptors with their controlled vocabularies
 * in the metadata SQL database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	EXPORT
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 30/06/2015
 */

/*=======================================================================================
 *																						*
 *							ExportTrustConnectAncillarySQL.php							*
 *																						*
 *======================================================================================*/

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
	// MySQLi://user:pass@localhost/metadata?socket=/tmp/mysql.sock&persist
				."<SQL database DSN>\n" );											// ==>

//
// Load arguments.
//
$db = $argv[ 1 ];

//
// Inform.
//
echo( "\n==> Connecting descriptors wit their controlled vocabulary.\n" );

//
// Try.
//
try
{
	//
	// Connect to SQL database.
	//
	echo( "  • Connecting to SQL\n" );
	echo( "    - $db\n" );
	$dc = NewADOConnection( $db );
	$dc->Execute( "SET CHARACTER SET 'utf8'" );
	$dc->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Handle ISO 3166-1.
	//
	connectAncillary( 'ISO 3166-1:alpha-3', 'MCPD:ORIGCTY', $dc );

	//
	// Handle ISO 3166-3.
	//
	connectAncillary( 'ISO 3166-3:alpha-3', 'MCPD:ORIGCTY', $dc );

	//
	// Handle SAMPSTAT.
	//
	connectAncillary( 'MCPD:SAMPSTAT', 'MCPD:SAMPSTAT', $dc );

	//
	// Handle COLLSRC.
	//
	connectAncillary( 'MCPD:COLLSRC', 'MCPD:COLLSRC', $dc );

	//
	// Handle STORAGE.
	//
	connectAncillary( 'MCPD:STORAGE', 'MCPD:STORAGE', $dc );

	//
	// Handle MLSSTAT.
	//
	connectAncillary( 'MCPD:MLSSTAT', 'MCPD:MLSSTAT', $dc );

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
	if( $dc instanceof ADOConnection )
		$dc->Close();
	
} // FINALLY BLOCK.


/*=======================================================================================
 *	FUNCTIONS																			*
 *======================================================================================*/

	/**
	 * Generate ISO 3166-1:alpha-3 ancillary data.
	 *
	 * This function will load the provided data into the provided SQL script file.
	 *
	 * @param string				$theNamespace		Ancillary namespace.
	 * @param string				$theDescriptor		Descriptor global identifier.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function connectAncillary( $theNamespace, $theDescriptor, $theDatabase )
	{
		//
		// Find descriptor native identifier.
		//
		$query = "SELECT `ID` FROM `DESCRIPTOR` WHERE `GID` = "
				.encodeString( $theDescriptor );
		$ds = $theDatabase->GetOne( $query );
		if( $ds )
		{
			//
			// Select ancillary namespace.
			//
			$query = "SELECT `ID` FROM `ANCILLARY` WHERE `GID` = "
					.encodeString( $theNamespace );
			$ns = $theDatabase->GetOne( $query );
			
			//
			// Iterate ancillary.
			//
			$query = "SELECT * FROM `ANCILLARY` "
					."WHERE `Namespace` = $ns "
					."AND `Parent` IS NULL "
					."ORDER BY `LID`";
			$rs = $theDatabase->Execute( $query );
			$sort = 1;
			foreach( $rs as $record )
			{
				//
				// Connect.
				//
				$data = Array();
				$data[] = $record[ 'ID' ];
				$data[] = $ds;
				$data[] = $sort++;
				$query = "INSERT INTO `VOCABULARY` VALUES( ".implode( ', ', $data ).')';
				$theDatabase->Execute( $query );
			
			} // Iterating ancillary.
			
			//
			// Cleanup.
			//
			$rs->Close();
			$rs = NULL;
		
		} // Found descriptor.
		
		else
			throw new Exception( "Unknown descriptor [$theDescriptor]" );		// !@! ==>
		
	} // connectAncillary.


	/**
	 * Encode string and return it i HEX.
	 *
	 * This function will detect the string encoding, set it to UTF-8 and return its HEX.
	 *
	 * @param string				$theString			String to encode.
	 */
	function encodeString( $theString )
	{
		//
		// Detect encoding.
		//
		if( mb_detect_encoding( $theString ) != 'UTF-8' )
			$theString = mb_convert_encoding( $theString, 'UTF-8' );
		
		return '0x'.bin2hex( $theString );											// ==>
		
	} // encodeString.

?>
