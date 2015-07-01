<?php

/**
 * Create ancillary data for trust ISO 3166 ANCILLARY tables.
 *
 * This file contains routines to create trust ISO 3166 SQL update script.
 *
 *	@package	OntologyWrapper
 *	@subpackage	EXPORT
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 26/06/2015
 */

/*=======================================================================================
 *																						*
 *								ExportTrustISO3166SQL.php								*
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
	// mongodb://localhost:27017/BIOVERSITY
				."<mongo database DSN> "
	// MySQLi://user:pass@localhost/metadata?socket=/tmp/mysql.sock&persist
				."<SQL database DSN>\n" );											// ==>

//
// Load arguments.
//
$mongo = $argv[ 1 ];
$db = $argv[ 2 ];

//
// Inform.
//
echo( "\n==> Creating SQL script for ISO 3166-1:alpha-3 trust database.\n" );

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
	generateSQL( 'iso:3166:1:alpha-3', $wrapper, $dc );
	
	//
	// Handle ISO 3166-3.
	//
	generateSQL( 'iso:3166:3:alpha-3', $wrapper, $dc, TRUE );

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
	 * @param string				$theNamespace		Mongo namespace.
	 * @param Wrapper				$theWrapper			Mongo collection.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function generateSQL( $theNamespace, $theWrapper, $theDatabase )
	{
		//
		// Resolve collection.
		//
		$collection
			= OntologyWrapper\Term::ResolveCollection(
				OntologyWrapper\Term::ResolveDatabase(
					$theWrapper ) );
		
		//
		// Select namespace terms.
		//
		$rs = $collection->matchAll( array( kTAG_NAMESPACE => $theNamespace ),
									 kQUERY_OBJECT );
		
		//
		// Handle namespace terms.
		//
		if( $rs->count() )
		{
			//
			// Select SQL namespace.
			//
			switch( $theNamespace )
			{
				case 'iso:3166:1:alpha-3':
					$namespace = 'ISO 3166-1:alpha-3';
					$dups = Array();
					break;
				
				case 'iso:3166:3:alpha-3':
					$namespace = 'ISO 3166-3:alpha-3';
					$dups = array( 'ATF', 'DEU', 'ESH', 'ETH', 'KNA', 'PAN', 'VAT', 'YEM' );
					break;
				
				default:
					throw new Exception(
								"Unknown ISO namespace [$theNamespace]." );			// ==>
			}
			
			//
			// Inform.
			//
			echo( "  • Loading namespace [$namespace]\n" );
			
			//
			// Find SQL namespace.
			//
			$query = "SELECT `ID` FROM `ANCILLARY` WHERE `GID` = 0x".bin2hex( $namespace );
			$sql_namespace = $theDatabase->GetOne( $query );
			
			//
			// Init SQL command.
			//
			$command = "INSERT INTO `ANCILLARY`(`Namespace`, `LID`, `GID`) VALUES\n";
			
			//
			// Iterate values.
			//
			$first = TRUE;
			foreach( $rs as $object )
			{
				//
				// Skip duplicates.
				//
				if( ! in_array( $object[ kTAG_ID_LOCAL ], $dups ) )
				{
					//
					// Handle comma.
					//
					if( ! $first )
						$command .= ",\n";
					else
						$first = FALSE;
				
					//
					// Handle data.
					//
					$command .= ('('.$sql_namespace.', ')
								.(encodeString( $object[ kTAG_ID_LOCAL ] ).', ')
								.(encodeString( $namespace.':'.$object[ kTAG_ID_LOCAL ] ).')');
				}
			}
			
			//
			// Close statement.
			//
			$command .= ";";
			
			//
			// Write statement.
			//
			$theDatabase->Execute( $command );
			
			//
			// Handle labels and definitions.
			//
			foreach( $rs as $object )
			{
				//
				// Skip duplicates.
				//
				if( ! in_array( $object[ kTAG_ID_LOCAL ], $dups ) )
				{
					//
					// Init SQL command.
					//
					$commands = Array();
					$command = "INSERT INTO `ANCILLARY_NAMES`"
							  ."(`Ancillary`, `Language`, `Label`, `Definition`) VALUES\n";
				
					//
					// Get ancillary identifier.
					//
					$gid = "0x".bin2hex( $namespace.':'.$object[ kTAG_ID_LOCAL ] );
					$query = "SELECT `ID` FROM `ANCILLARY` WHERE `GID` = $gid";
					$id = $theDatabase->GetOne( $query );
				
					//
					// Iterate labels.
					//
					$definitions = $object[ kTAG_DEFINITION ];
					foreach( $object[ kTAG_LABEL ] as $text )
					{
						//
						// Only main languages.
						//
						if( $text[ kTAG_LANGUAGE ] == 'en' )
						{
							//
							// Init label.
							//
							$record = array( $id,
											 "'".$text[ kTAG_LANGUAGE ]."'",
											 encodeString( $text[ kTAG_TEXT ] ),
											 'NULL' );
					
							//
							// Set definition.
							//
							if( $definitions !== NULL )
							{
								foreach( $definitions as $definition )
								{
									if( $text[ kTAG_LANGUAGE ] == $definition[ kTAG_LANGUAGE ] )
									{
										$record[ 3 ] = encodeString( $definition[ kTAG_TEXT ] );
										break;										// =>
									}
								}
							}
					
							//
							// Add statement.
							//
							$commands[] = '('.implode( ', ', $record ).')';
						}
					}
				
					//
					// Execute statement.
					//
					$theDatabase->Execute( $command.implode( ', ', $commands ).';' );
				}
			}
		
		} // Found terms.
		
	} // generateSQL.


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
