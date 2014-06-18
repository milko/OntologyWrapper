<?php

/**
 * MCPD (CWR) load procedure.
 *
 * This file contains routines to load the crop wild relative accessions from an SQL
 * database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/06/2014
 */

/*=======================================================================================
 *																						*
 *							LoadCWRAccessionsFromSQLDb.php								*
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
 *	TEST																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 3 )
	exit( "Usage: "
		 ."script.php "
		 ."[SQL database DSN] "		// MySQLi://WEB-SERVICES:webservicereader@localhost/pgrdg?socket=/tmp/mysql.sock&persist
		 ."[mongo database DSN] "	// mongodb://localhost:27017/PGRDG
		 ."[graph DSN].\n" );		// neo4j://localhost:7474						// ==>

//
// Init local storage.
//
$db = $rsu = NULL;
$start = 0;
$limit = 1000;

//
// Load arguments.
//
$database = $argv[ 1 ];
$mongo = $argv[ 2 ];
$graph = ( $argc > 3 ) ? $argv[ 3 ] : NULL;
 
//
// Inform.
//
echo( "\n==> Loading crop wild relative related accessions.\n" );

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
	// Connect to database.
	//
	echo( "  • Connecting to SQL\n" );
	echo( "    - $database\n" );
	$db = NewADOConnection( $database );
	$db->Execute( "SET CHARACTER SET 'utf8'" );
	$db->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Read.
	//
	echo( "  • Importing " );
	$rsu = $db->execute( "SELECT * FROM `MCPD` LIMIT $start,$limit" );
	while( $rsu->RecordCount() )
	{
		//
		// Iterate recordset.
		//
		foreach( $rsu as $record )
		{
			//
			// Init loop storage.
			//
			$object = new OntologyWrapper\Accession( $wrapper );
		
			//
			// Parse unit.
			//
			foreach( $record as $key => $value )
			{
				//
				// Skip NULL records.
				//
				if( ($value !== NULL)
				 || strlen( trim( $value ) ) )
				{
					//
					// Parse record.
					//
					switch( $key )
					{
						case ':inventory:dataset':
						case ':inventory:NICODE':
						case ':unit:collection':
						case 'mcpd:ACCENUMB':
						case 'mcpd:ACQDATE':
						case ':taxon:genus':
						case ':taxon:species':
						case ':taxon:species:author':
						case ':taxon:infraspecies':
						case ':taxon:infraspecies:author':
						case ':taxon:epithet':
						case 'mcpd:COLLDESCR':
						case 'mcpd:COLLNUMB':
						case 'mcpd:COLLDATE':
						case ':location:locality':
						case 'mcpd:LATITUDE':
						case ':location:latitude:deg':
						case ':location:latitude:min':
						case ':location:latitude:sec':
						case ':location:latitude:hem':
						case ':location:latitude':
						case 'mcpd:LONGITUDE':
						case ':location:longitude:deg':
						case ':location:longitude:min':
						case ':location:longitude:sec':
						case ':location:longitude:hem':
						case ':location:longitude':
						case ':location:elevation':
						case 'mcpd:DONORDESCR':
						case 'mcpd:DONORNUMB':
						case 'mcpd:BREDDESCR':
						case 'mcpd:ANCEST':
						case 'mcpd:DUPLDESCR':
						case 'mcpd:ACCEURL':
						case 'mcpd:REMARKS':
						case ':unit:version':
							$object[ $key ] = $value;
							break;
			
						case ':taxon:names':
							$tmp = explode( ',', $value );
							$value = Array();
							foreach( $tmp as $item )
							{
								$item = trim( $item );
								if( strlen( $item ) )
									$value[] = $item;
							}
							if( count( $value ) )
								$object[ $key ] = $value;
							break;
			
						case 'mcpd:STORAGE':
							$tmp = explode( ',', $value );
							$value = Array();
							foreach( $tmp as $item )
								$value[] = "$key:$item";
							$object[ $key ] = $value;
							break;
			
						case ':taxon:crop':
						case ':taxon:annex-1':
						case 'mcpd:MLSSTAT1':
						case 'mcpd:AEGISSTAT':
						case 'mcpd:AVAILABLE':
						case 'mcpd:SAMPSTAT':
						case 'mcpd:COLLSRC':
							$object[ $key ] = "$key:$value";
							break;
					
						case ':inventory:INSTCODE':
							$object[ kTAG_AUTHORITY ] = $value;
						case 'mcpd:COLLCODE':
						case 'mcpd:DONORCODE':
						case 'mcpd:BREDCODE':
						case 'mcpd:DUPLSITE':
							$object[ $key ]
								= OntologyWrapper\FAOInstitute::FAOIdentifier(
									$value );
							break;
						
						case ':location:country':
							if( $tmp = OntologyWrapper\Term::ResolveCountryCode(
											$wrapper, $value ) )
								$object[ $key ] = $tmp;
							break;
						
						case 'mcpd:OTHERNUMB':
						case 'mcpd:ACCENAME':
							$tmp = explode( ';', $value );
							$value = Array();
							foreach( $tmp as $item )
							{
								$item = trim( $item );
								if( strlen( $item ) )
									$value[] = $item;
							}
							if( count( $value ) )
								$object[ $key ] = $value;
							break;
						
					} // Parsing record.
			
				} // Fields not empty.
		
			} // Iterating unit.
		
			//
			// Store record.
			//
			$object->commit( $wrapper );
	
		} // Iterating recordset.
		
		//
		// Close recordset.
		//
		$rsu->Close();
		$rsu = NULL;
			
		//
		// Inform.
		//
		echo( '.' );
		
		//
		// Read next.
		//
		$start += $limit;
		$rsu = $db->execute( "SELECT * FROM `MCPD` LIMIT $start,$limit" );
	
	} // Records left.

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
	if( $rsu instanceof ADORecordSet )
		$rsu->Close();
	if( $db instanceof ADOConnection )
		$db->Close();
}

?>
