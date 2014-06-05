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
 *								LoadEufgisFromSQLDb.php									*
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
		 ."[SQL database DSN] "
		 ."[mongo database DSN] "
		 ."[graph DSN].\n" );														// ==>

//
// Init local storage.
//
$db = $rsu = $rsp = NULL;

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
	$rsu = $db->execute( "SELECT * FROM `EUFGIS_UNITS`" );
	foreach( $rsu as $record )
	{
		//
		// Init loop storage.
		//
		$populations = Array();
		$object = new OntologyWrapper\ForestUnit( $wrapper );
		
		//
		// Parse record.
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
					case 'UnitNumber':
						$object[ 'fcu:unit:number' ] = $value;
						break;
			
					case 'UnitCountry':
						if( $tmp = OntologyWrapper\Term::ResolveCountryCode(
										$wrapper, $value ) )
							$object[ ':location:country' ] =  $tmp;
						else
							throw new \Exception(
								"Invalid country code [$value]." );				// !@! ==>
						break;
			
					case 'UnitDataCollectionYear':
						$object[ kTAG_VERSION ] = $value;
						break;
			
					case 'UnitLastVisitYear':
						$object[ 'fcu:unit:last-visit' ] = $value;
						break;
			
					case 'UnitGeneNumber':
						$object[ 'fcu:unit:gene-number' ] = $value;
						break;
			
					case 'UnitProvince':
						$object[ ':location:admin-1' ] = $value;
						break;
			
					case 'UnitDepartment':
						$object[ ':location:admin-2' ] = $value;
						break;
			
					case 'UnitMunicipality':
						$object[ ':location:admin-3' ] = $value;
						break;
			
					case 'UnitLocalName':
						$object[ ':location:locality' ] = $value;
						break;
			
					case 'UnitLongitudeD':
						if( ! $record[ 'UnitCoordinatesRestriction' ] )
							$object[ ':location:longitude' ] = $value;
						break;
			
					case 'UnitLongitudeMap':
						if( $record[ 'UnitCoordinatesRestriction' ] )
							$object[ ':location:longitude' ] = $value;
						break;
			
					case 'UnitLatitudeD':
						if( ! $record[ 'UnitCoordinatesRestriction' ] )
							$object[ ':location:latitude' ] = $value;
						break;
			
					case 'UnitLatitudeMap':
						if( $record[ 'UnitCoordinatesRestriction' ] )
							$object[ ':location:latitude' ] = $value;
						break;
			
					case 'UnitMinimumElevation':
						$object[ ':location:elevation:min' ] = $value;
						break;
			
					case 'UnitMaximumElevation':
						$object[ ':location:elevation:max' ] = $value;
						break;
			
					case 'UnitGeodeticDatum':
						break;
			
				} // Parsing record.
			
			} // Fields not empty.
		
		} // Iterating record.
		
		//
		// Set shape.
		//
		if( $object->offsetExists( ':location:longitude' )
		 && $object->offsetExists( ':location:latitude' ) )
			$object->offsetSet(
				':shape',
				array( kTAG_SHAPE_TYPE => 'Point',
					   kTAG_SHAPE_GEOMETRY => array(
					   		$object->offsetGet( ':location:longitude' ),
					   		$object->offsetGet( ':location:latitude' ) ) ) );
		
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
	if( $rsu instanceof ADORecordSet )
		$rsu->Close();
	if( $rsp instanceof ADORecordSet )
		$rsp->Close();
	if( $db instanceof ADOConnection )
		$db->Close();
}

?>
