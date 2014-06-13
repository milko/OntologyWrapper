<?php

/**
 * EUFGIS load procedure.
 *
 * This file contains routines to load EUFGIS from an SQL database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 05/06/2014
 */

/*=======================================================================================
 *																						*
 *								LoadChecklistsFromSQLDb.php								*
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
$db = $rs = NULL;

//
// Load arguments.
//
$database = $argv[ 1 ];
$mongo = $argv[ 2 ];
$graph = ( $argc > 3 ) ? $argv[ 3 ] : NULL;
 
//
// Try.
//
try
{
	//
	// Inform.
	//
	echo( "\n==> Connecting.\n" );
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
	// Import.
	//
	echo( "  • Importing\n" );
	$rsu = $db->execute( "SELECT * FROM `DATA-CK`" );
	foreach( $rsu as $record )
	{
		//
		// Init loop storage.
		//
		$object = new OntologyWrapper\Checklist( $wrapper );
		
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
				// Skip local fields.
				//
				if( ($key == '__ID')
				 || ($key == '__KEY') )
					continue;												// =>
				
				//
				// Parse record.
				//
				switch( $key )
				{
					case 'cwr:ck:CWRCODE':
						if( strlen( $value ) == 3 )
						{
							$object[ ':location:country' ]
								= "iso:3166:1:alpha-3:$value";
							$object[ ':location:admin' ]
								= "iso:3166:1:alpha-3:$value";
						}
						elseif( substr( $value, 0, 2 ) == 'GB' )
						{
							$object[ ':location:country' ]
								= "iso:3166:1:alpha-3:GBR";
							$object[ ':location:admin' ]
								= "iso:3166:2:$value";
						}
					
					case 'cwr:ck:NUMB':
					case 'cwr:CHROMOSNUMB':
					case 'cwr:GENEPOOL':
					case ':taxon:regnum':
					case ':taxon:phylum':
					case ':taxon:classis':
					case ':taxon:ordo':
					case ':taxon:familia':
					case ':taxon:subfamilia':
					case ':taxon:tribus':
					case ':taxon:subtribus':
					case ':taxon:genus':
					case ':taxon:species':
					case ':taxon:species:author':
					case ':taxon:infraspecies':
					case ':taxon:infraspecies:author':
					case 'cwr:TAXREF':
					case 'cwr:REGIONASS':
					case 'cwr:REDLISTCAT':
					case 'cwr:URLPUBREDLISTASS':
					case 'cwr:REMARKS':
						$object[ $key ] = $value;
						break;
					
					case ':inventory:INSTCODE':
						$object[ $key ]
							= OntologyWrapper\FAOInstitute::FAOIdentifier( $value );
						break;
					
					case 'cwr:ENDEMISM':
						$object[ $key ] = (boolean) $value;
						break;
					
					case 'cwr:ck:TYPE':
					case 'cwr:ck:CRITPRIORI':
					case 'cwr:ASSLEVEL':
					case 'cwr:TAXONSTATUS':
					case 'cwr:OCCURTHREAT':
						$object[ $key ] = "$key:$value";
						break;
					
					case 'cwr:REF':
					case 'cwr:URL':
					case 'cwr:SYNONYMS':
					case 'cwr:SYNREF':
					case ':taxon:names':
					case 'cwr:TAXONGROUP':
					case 'cwr:REFTAXONGROUP':
					case 'cwr:LISTSPCROSS':
					case 'cwr:LISTSPCROSSREF':
					case 'cwr:METHCROSSREF':
					case 'cwr:REFREDLISTASS':
					case 'cwr:ECOVALUE':
					case 'cwr:ECOVALUEREF':
						$tmp = explode( ';', $value );
						$value = Array();
						foreach( $tmp as $item )
						{
							if( strlen( $item = trim( $item ) ) )
								$value[] = $item;
						}
						if( count( $value ) )
							$object[ $key ] = $value;
						break;
						
					case 'cwr:SUCCROSSREF':
						$tmp = explode( ';', $value );
						$value = Array();
						foreach( $tmp as $item )
						{
							if( strlen( $item = trim( $item ) ) )
								$value[] = (int) $item;
						}
						if( count( $value ) )
							$object[ $key ] = $value;
						break;
						
					case 'iucn:category':
					case 'iucn:criteria':
					case 'cwr:USEOFTAXON':
					case 'iucn:threat':
						$tmp = explode( ';', $value );
						$value = Array();
						foreach( $tmp as $item )
						{
							if( strlen( $item = trim( $item ) ) )
								$value[] = "$key:$item";
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
	
	} // Iterating units.

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
	if( $rs instanceof ADORecordSet )
		$rs->Close();
	if( $db instanceof ADOConnection )
		$db->Close();
}

?>
