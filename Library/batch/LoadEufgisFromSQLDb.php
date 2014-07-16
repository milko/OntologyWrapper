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
		 ."[SQL database DSN] "		// MySQLi://WEB-SERVICES:webservicereader@localhost/pgrdg?socket=/tmp/mysql.sock&persist
		 ."[mongo database DSN] "	// mongodb://localhost:27017/PGRDG
		 ."[graph DSN].\n" );		// neo4j://localhost:7474						// ==>

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
// Inform.
//
echo( "\n==> Loading forest gene conservation units.\n" );

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
	// Load dictionary.
	//
	$tmp = array( 'fcu:unit:number' => NULL,
				  ':location:country' => NULL,
				  'fcu:unit:last-visit' => NULL,
				  'fcu:unit:gene-number' => NULL,
				  ':location:admin-1' => NULL,
				  ':location:admin-2' => NULL,
				  ':location:admin-3' => NULL,
				  ':location:locality' => NULL,
				  ':location:longitude' => NULL,
				  ':location:latitude' => NULL,
				  ':location:elevation:min' => NULL,
				  ':location:elevation:max' => NULL,
				  ':location:datum' => NULL,
				  ':location:restricted' => NULL,
				  'fcu:unit:data-collection' => NULL,
				  'fcu:unit:area' => NULL,
				  'fcu:unit:ownership/:predicate:SCALE-OF/eufgis:UnitOwnership' => NULL,
				  'fcu:unit:type/:predicate:SCALE-OF/eufgis:UnitType' => NULL,
				  ':taxon:epithet' => NULL,
				  'fcu:unit:species' => NULL,
				  'fcu:unit:remarks' => NULL,
				  'fcu:unit:remarks-soil' => NULL,
				  'fcu:population' => NULL,
				  'fcu:population:last-visit' => NULL,
				  'fcu:population:establishment' => NULL,
				  'fcu:population:status/:predicate:SCALE-OF/eufgis:PopulationStatus' => NULL,
				  'fcu:population:situ/:predicate:SCALE-OF/eufgis:PopulationSitu' => NULL,
				  'fcu:population:origin/:predicate:SCALE-OF/eufgis:PopulationOrigin' => NULL,
				  'fcu:population:system/:predicate:SCALE-OF/eufgis:PopulationSystem' => NULL,
				  'fcu:population:system/:predicate:SCALE-OF/eufgis:PopulationSystem' => NULL,
				  'fcu:population:management/:predicate:SCALE-OF/eufgis:PopulationManagement' => NULL,
				  'fcu:population:justification/:predicate:SCALE-OF/eufgis:PopulationJustification' => NULL,
				  'fcu:population:reproducing/:predicate:SCALE-OF/eufgis:PopulationReproducingTrees' => NULL,
				  'fcu:population:sex-ratio/:predicate:SCALE-OF/eufgis:PopulationSexRatio' => NULL,
				  'fcu:population:regeneration/:predicate:SCALE-OF/eufgis:PopulationRegeneration' => NULL,
				  'fcu:population:distribution/:predicate:SCALE-OF/eufgis:PopulationDistribution' => NULL,
				  'fcu:population:share' => NULL,
				  'fcu:population:remarks' => NULL );
	$dd = Array();
	foreach( array_keys( $tmp ) as $key )
		$dd[ $key ]
			= $wrapper->getSerial( $key );
	
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
					case 'UnitNumber':
						$object[ $dd[ 'fcu:unit:number' ] ] = $value;
						break;
			
					case 'UnitCountry':
						if( $tmp = OntologyWrapper\Term::ResolveCountryCode(
										$wrapper, $value ) )
							$object[ $dd[ ':location:country' ] ] =  $tmp;
						else
							throw new \Exception(
								"Invalid country code [$value]." );				// !@! ==>
						break;
			
					case 'UnitDataCollectionYear':
						$object[ $dd[ 'fcu:unit:data-collection' ] ] = (string) $value;
						$object[ kTAG_VERSION ] = (string) $value;
						break;
			
					case 'UnitLastVisitYear':
						$object[ $dd[ 'fcu:unit:last-visit' ] ] = (string) $value;
						break;
			
					case 'UnitGeneNumber':
						$object[ $dd[ 'fcu:unit:gene-number' ] ] = $value;
						break;
			
					case 'UnitProvince':
						$object[ $dd[ ':location:admin-1' ] ] = $value;
						break;
			
					case 'UnitDepartment':
						$object[ $dd[ ':location:admin-2' ] ] = $value;
						break;
			
					case 'UnitMunicipality':
						$object[ $dd[ ':location:admin-3' ] ] = $value;
						break;
			
					case 'UnitLocalName':
						$object[ $dd[ ':location:locality' ] ] = $value;
						break;
			
					case 'UnitLongitudeD':
						if( ! $record[ 'UnitCoordinatesRestriction' ] )
							$object[ $dd[ ':location:longitude' ] ] = $value;
						break;
			
					case 'UnitLongitudeMap':
						if( $record[ 'UnitCoordinatesRestriction' ] )
							$object[ $dd[ ':location:longitude' ] ] = $value;
						break;
			
					case 'UnitLatitudeD':
						if( ! $record[ 'UnitCoordinatesRestriction' ] )
							$object[ $dd[ ':location:latitude' ] ] = $value;
						break;
			
					case 'UnitLatitudeMap':
						if( $record[ 'UnitCoordinatesRestriction' ] )
							$object[ $dd[ ':location:latitude' ] ] = $value;
						break;
			
					case 'UnitMinimumElevation':
						$object[ $dd[ ':location:elevation:min' ] ] = $value;
						break;
			
					case 'UnitMaximumElevation':
						$object[ $dd[ ':location:elevation:max' ] ] = $value;
						break;
			
					case 'UnitGeodeticDatum':
						$object[ $dd[ ':location:datum' ] ] = ":location:datum:$value";
						break;
			
					case 'UnitCoordinatesRestriction':
						$object[ $dd[ ':location:restricted' ] ] = (boolean) $value;
						break;
			
					case 'UnitArea':
						$object[ $dd[ 'fcu:unit:area' ] ] = $value;
						break;
			
					case 'UnitOwnership':
						$object[ $dd[ 'fcu:unit:ownership/:predicate:SCALE-OF/eufgis:UnitOwnership' ] ]
							= "eufgis:UnitOwnership:$value";
						break;
			
					case 'UnitType':
						$tmp = Array();
						foreach( explode( ',', $value ) as $item )
							$tmp[] = "eufgis:UnitType:$item";
						$object[ $dd[ 'fcu:unit:type/:predicate:SCALE-OF/eufgis:UnitType' ] ]
							= $tmp;
						break;
			
					case 'UnitTaxa':
						$field = Array();
						$tmp = explode( ';', $value );
						foreach( $tmp as $item )
						{
							if( strlen( $item = trim( $item ) ) )
								$field[] = $item;
						}
						$object[ $dd[ 'fcu:unit:species' ] ] = $field;
						break;
			
					case 'UnitSoilRemarks':
						$object[ $dd[ 'fcu:unit:remarks-soil' ] ] = $value;
						break;
			
					case 'UnitRemarks':
						$object[ $dd[ 'fcu:unit:remarks' ] ] = $value;
						break;
			
				} // Parsing record.
			
			} // Fields not empty.
		
		} // Iterating unit.
		
		//
		// Set shape.
		//
		if( $object->offsetExists( ':location:longitude' )
		 && $object->offsetExists( ':location:latitude' ) )
			$object->offsetSet(
				':shape',
				array( kTAG_TYPE => 'Point',
					   kTAG_GEOMETRY => array(
					   		$object->offsetGet( ':location:longitude' ),
					   		$object->offsetGet( ':location:latitude' ) ) ) );
		
		//
		// Set average elevation.
		//
		if( $object->offsetExists( ':location:elevation:min' )
		 && $object->offsetExists( ':location:elevation:max' ) )
			$object->offsetSet( ':location:elevation',
								(int) (($this->offsetGet( ':location:elevation:max' )
									-	$this->offsetGet( ':location:elevation:min' ))
									/	2) );
		
		//
		// Load populations.
		//
		$rsp = $db->execute( "SELECT * FROM `EUFGIS_POPULATIONS` "
							 ."WHERE `PopulationUnitID` = '"
							 .$record[ 'UnitID' ]
							 ."'" );
		if( $rsp->RecordCount() )
		{
			//
			// Init population record.
			//
			$pops = Array();
			
			//
			// Iterate populations.
			//
			foreach( $rsp as $record )
			{
				//
				// Reference record.
				//
				$index = count( $pops );
				$pops[ $index ] = Array();
				$pop = & $pops[ $index ];
				
				//
				// Set target species.
				//
				$pop[ $dd[ ':taxon:epithet' ] ] = $record[ 'PopulationTargetSpecies' ];
				
				//
				// Iterate record.
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
							case 'PopulationLastVisitYear':

								$tag = $dd[ 'fcu:population:last-visit' ];
								$pop[ $tag ] = (string) $value;
								break;
					
							case 'PopulationEstablishmentYear':
								$tag = $dd[ 'fcu:population:establishment' ];
								$pop[ $tag ] = (string) $value;
								break;
					
							case 'PopulationStatus':
								$tag = $dd[ 'fcu:population:status/:predicate:SCALE-OF/eufgis:PopulationStatus' ];
								$pop[ $tag ]
									= "eufgis:PopulationStatus:$value";
								break;
					
							case 'PopulationSitu':
								$tag = $dd[ 'fcu:population:situ/:predicate:SCALE-OF/eufgis:PopulationSitu' ];
								$pop[ $tag ]
									= "eufgis:PopulationSitu:$value";
								break;
					
							case 'PopulationOrigin':
								$tag = $dd[ 'fcu:population:origin/:predicate:SCALE-OF/eufgis:PopulationOrigin' ];
								$pop[ $tag ]
									= "eufgis:PopulationOrigin:$value";
								break;
					
							case 'PopulationSystem':
								$tag = $dd[ 'fcu:population:system/:predicate:SCALE-OF/eufgis:PopulationSystem' ];
								$pop[ $tag ]
									= "eufgis:PopulationSystem:$value";
								break;
					
							case 'PopulationManagement':
								$tag = $dd[ 'fcu:population:management/:predicate:SCALE-OF/eufgis:PopulationManagement' ];
								$pop[ $tag ]
									= "eufgis:PopulationManagement:$value";
								break;
					
							case 'PopulationJustification':
								$tag = $dd[ 'fcu:population:justification/:predicate:SCALE-OF/eufgis:PopulationJustification' ];
								$pop[ $tag ]
									= "eufgis:PopulationJustification:$value";
								break;
					
							case 'PopulationReproducingTrees':
								$tag = $dd[ 'fcu:population:reproducing/:predicate:SCALE-OF/eufgis:PopulationReproducingTrees' ];
								$pop[ $tag ]
									= "eufgis:PopulationReproducingTrees:$value";
								break;
					
							case 'PopulationSexRatio':
								$tag = $dd[ 'fcu:population:sex-ratio/:predicate:SCALE-OF/eufgis:PopulationSexRatio' ];
								$pop[ $tag ]
									= "eufgis:PopulationSexRatio:$value";
								break;
					
							case 'PopulationRegeneration':
								$tag = $dd[ 'fcu:population:regeneration/:predicate:SCALE-OF/eufgis:PopulationRegeneration' ];
								$pop[ $tag ]
									= "eufgis:PopulationRegeneration:$value";
								break;
					
							case 'PopulationDistribution':
								$tmp = Array();
								foreach( explode( ',', $value ) as $item )
									$tmp[] = "eufgis:PopulationDistribution:$item";
								$tag = $dd[ 'fcu:population:distribution/:predicate:SCALE-OF/eufgis:PopulationDistribution' ];
								$pop[ $tag ] = $tmp;
								break;
								break;
					
							case 'PopulationShare':
								$tag = $dd[ 'fcu:population:share' ];
								$pop[ $tag ] = $value;
								break;
					
							case 'PopulationRemarks':
								$tag = $dd[ 'fcu:population:remarks' ];
								$pop[ $tag ] = $value;
								break;
					
						} // Parsing record.
					
					} // Field not empty.
				
				} // Iterating record.
			
			} // Iterating populations.
			
			//
			// Set populations.
			//
			$object[ $dd[ 'fcu:population' ] ] = $pops;
		
		} // Has populations.
		
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
