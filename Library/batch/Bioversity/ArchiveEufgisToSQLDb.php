<?php

/**
 * SQL EUFGIS archive procedure.
 *
 * This file contains routines to load EUFGIS from an SQL database and archive it as XML
 * in the archive database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 29/08/2014
 */

/*=======================================================================================
 *																						*
 *								ArchiveEufgisToSQLDb.php								*
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

/**
 * Settings.
 */
define( 'kDO_CLIMATE', TRUE );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 5 )
	exit( "Usage: <script.php> "
	// MySQLi://user:pass@localhost/bioversity?socket=/tmp/mysql.sock&persist
				."<Input SQL database DSN> "
	// MySQLi://user:pass@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist
				."<Output SQL database DSN> "
	// eufgis
				."<Output SQL database table> "
	// mongodb://localhost:27017/BIOVERSITY
				."<mongo database DSN> "
	// neo4j://localhost:7474 or ""
				."[graph DSN]"
	// "'last identifier'"
				."[last ID (including quotes if string)]\n" );						// ==>

//
// Init local storage.
//
$start = 0;
$limit = 100;
$dc_in = $dc_out = $rs = NULL;
$class = 'OntologyWrapper\ForestUnit';

//
// Load arguments.
//
$db_in = $argv[ 1 ];
$db_out = $argv[ 2 ];
$table = $argv[ 3 ];
$mongo = $argv[ 4 ];
$graph = ( ($argc > 5) && strlen( $argv[ 5 ] ) ) ? $argv[ 5 ] : NULL;
$last = ( $argc > 6 ) ? $argv[ 6 ] : NULL;

//
// Inform.
//
echo( "\n==> Loading EUFGIS into $table.\n" );

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
	// Connect to input database.
	//
	echo( "  • Connecting to input SQL\n" );
	echo( "    - $db_in\n" );
	$dc_in = NewADOConnection( $db_in );
	$dc_in->Execute( "SET CHARACTER SET 'utf8'" );
	$dc_in->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Connect to output database.
	//
	echo( "  • Connecting to output SQL\n" );
	echo( "    - $db_out\n" );
	$dc_out = NewADOConnection( $db_out );
	$dc_out->Execute( "SET CHARACTER SET 'utf8'" );
	$dc_out->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Clearing output.
	//
	if( $last === NULL )
	{
		$rs = $dc_out->Execute( "TRUNCATE TABLE `$table`" );
		$rs->Close();
	}
	
	//
	// Import.
	//
	echo( "  • Exporting\n" );
	$query = "SELECT * FROM `fcu_unit` ";
	if( $last !== NULL )
		$query .= "WHERE( `UnitID` > $last ) ";
	$query .= "ORDER BY `UnitID` LIMIT $start,$limit";
	$rs = $dc_in->execute( $query );
	while( $rs->RecordCount() )
	{
		//
		// Iterate page.
		//
		foreach( $rs as $record )
		{
			//
			// Scan record.
			//
			$data = Array();
			foreach( $record as $key => $value )
			{
				//
				// Normalise value.
				//
				if( strlen( trim( $value ) ) )
					$data[ $key ] = trim( $value );
			
			} // Scanning record.
			
			//
			// Skip empty records.
			//
			if( ! count( $data ) )
				continue;													// =>
		
			//
			// Instantiate object.
			//
			$object = new $class( $wrapper );
			
			//
			// Load unit.
			//
			loadUnit( $object, $data, $wrapper, $dc_in );
			
			//
			// Load climate.
			//
			if( kDO_CLIMATE )
				$object->setClimateData();
			
			//
			// Validate object.
			//
			$object->validate();
			
			//
			// Save record.
			//
			$xml = $object->export( 'xml' );
			$insert = ( $last === NULL )
					? "INSERT INTO `$table`( "
					: "REPLACE INTO `$table`( ";
			$insert .= ("`id`, `class`, `xml` ) VALUES( "
					   .'0x'.bin2hex( (string) $record[ 'UnitID' ] ).', '
					   .'0x'.bin2hex( get_class( $object ) ).', '
					   .'0x'.bin2hex( $xml->asXML() ).' )');
			$dc_out->Execute( $insert );
			
		} // Iterating page.
		
		//
		// Close recordset.
		//
		$rs->Close();
		$rs = NULL;
			
		//
		// Inform.
		//
		echo( '.' );
		
		//
		// Read next.
		//
		$start += $limit;
		$query = "SELECT * FROM `fcu_unit` ";
		if( $last !== NULL )
			$query .= "WHERE( `UnitID` > $last ) ";
		$query .= "ORDER BY `UnitID` LIMIT $start,$limit";
		$rs = $dc_in->execute( $query );
	
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
	if( $dc_in instanceof ADOConnection )
		$dc_in->Close();
	if( $dc_out instanceof ADOConnection )
		$dc_out->Close();

} // FINALLY BLOCK.


/*=======================================================================================
 *	FUNCTIONS																			*
 *======================================================================================*/

	/**
	 * Load unit data.
	 *
	 * This function will load the unit data provided in the <b>$theData</b> parameter
	 * into the object provided in the <b>$theObject</b> parameter.
	 *
	 * The function will take care of loading the target species data.
	 *
	 * @param PersistentObject		$theObject			Object.
	 * @param array					$theData			Data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadUnit( $theObject, $theData, $theWrapper, $theDatabase )
	{
		/***********************************************************************
		 * Set unit identification properties.
		 **********************************************************************/
		
		//
		// Set authority.
		//
		$theObject->offsetSet( kTAG_AUTHORITY,
							   substr( $theData[ 'UnitNumber' ], 0, 3 ) );
		
		//
		// Set identifier.
		//
		$theObject->offsetSet( kTAG_IDENTIFIER,
							   substr( $theData[ 'UnitNumber' ], 3 ) );
		
		//
		// Set version.
		//
		$theObject->offsetSet( kTAG_VERSION, $theData[ 'UnitDataCollectionYear' ] );
		
		/***********************************************************************
		 * Set unit inventory properties.
		 **********************************************************************/
		
		//
		// Set dataset.
		//
		$theObject->offsetSet( ':inventory:dataset',
			'European information system on forest genetic resources (EUFGIS)' );
		
		//
		// Set inventory code.
		//
		$theObject->offsetSet( ':inventory:code', $theData[ 'UnitCountry' ] );
		
		//
		// Set inventory administrative unit.
		//
		$theObject->offsetSet( ':inventory:admin', 'iso:3166:1:alpha-3:'
												  .$theData[ 'UnitCountry' ] );
		
		/***********************************************************************
		 * Set other properties.
		 **********************************************************************/
		
		//
		// Set unit number.
		//
		if( array_key_exists( 'UnitNumber', $theData ) )
			$theObject->offsetSet( 'fcu:unit:number',
								   $theData[ 'UnitNumber' ] );
		
		//
		// Set forest gene-number.
		//
		if( array_key_exists( 'UnitGeneNumber', $theData ) )
			$theObject->offsetSet( 'fcu:unit:gene-number',
								   $theData[ 'UnitGeneNumber' ] );
		
		//
		// Set country.
		//
		if( array_key_exists( 'UnitCountry', $theData ) )
			$theObject->offsetSet( ':location:country',
								   'iso:3166:1:alpha-3:'.$theData[ 'UnitCountry' ] );
		
		//
		// Set province.
		//
		if( array_key_exists( 'UnitProvince', $theData ) )
			$theObject->offsetSet( ':location:admin-1',
								   $theData[ 'UnitProvince' ] );
		
		//
		// Set department.
		//
		if( array_key_exists( 'UnitDepartment', $theData ) )
			$theObject->offsetSet( ':location:admin-2',
								   $theData[ 'UnitDepartment' ] );
		
		//
		// Set municipality.
		//
		if( array_key_exists( 'UnitMunicipality', $theData ) )
			$theObject->offsetSet( ':location:admin-3',
								   $theData[ 'UnitMunicipality' ] );
		
		//
		// Set local name.
		//
		if( array_key_exists( 'UnitLocalName', $theData ) )
			$theObject->offsetSet( ':location:locality',
								   $theData[ 'UnitLocalName' ] );
		
		//
		// Set minimum elevation.
		//
		if( array_key_exists( 'UnitMinimumElevation', $theData ) )
			$theObject->offsetSet( ':location:site:elevation:min',
								   (int) $theData[ 'UnitMinimumElevation' ] );
		
		//
		// Set maximum elevation.
		//
		if( array_key_exists( 'UnitMaximumElevation', $theData ) )
			$theObject->offsetSet( ':location:site:elevation:max',
								   (int) $theData[ 'UnitMaximumElevation' ] );
		
		//
		// Set datum.
		//
		if( array_key_exists( 'UnitGeodeticDatum', $theData ) )
			$theObject->offsetSet( ':location:site:datum',
								   ':location:site:datum:'.$theData[ 'UnitGeodeticDatum' ] );
		
		//
		// Set coordinates restriction.
		//
		if( array_key_exists( 'UnitCoordinatesRestriction', $theData ) )
			$theObject->offsetSet( ':location:site:coordinates-restricted',
								   ( $theData[ 'UnitCoordinatesRestriction' ] ) ? TRUE
								   												: FALSE );
		
		//
		// Set coordinates.
		//
		if( ! $theObject->offsetGet( ':location:site:coordinates-restricted' ) )
		{
			if( array_key_exists( 'UnitLatitudeD', $theData ) )
				$theObject->offsetSet( ':location:site:latitude',
									   $theData[ 'UnitLatitudeD' ] );
			if( array_key_exists( 'UnitLatitude', $theData ) )
			{
				if( $count = count( $tmp = ParseCoordinate( $theData[ 'UnitLatitude' ] ) ) )
				{
					$theObject->offsetSet( ':location:site:latitude:deg', $tmp[ 'D' ] );
					$theObject->offsetSet( ':location:site:latitude:hem', $tmp[ 'H' ] );
					switch( $count )
					{
						case 4:
							$theObject->offsetSet( ':location:site:latitude:sec', $tmp[ 'S' ] );
						case 3:
							$theObject->offsetSet( ':location:site:latitude:min', $tmp[ 'M' ] );
							break;
					}
				}
			}
									   
			if( array_key_exists( 'UnitLongitudeD', $theData ) )
				$theObject->offsetSet( ':location:site:longitude',
									   $theData[ 'UnitLongitudeD' ] );
			if( array_key_exists( 'UnitLongitude', $theData ) )
			{
				if( $count = count( $tmp = ParseCoordinate( $theData[ 'UnitLongitude' ] ) ) )
				{
					$theObject->offsetSet( ':location:site:longitude:deg', $tmp[ 'D' ] );
					$theObject->offsetSet( ':location:site:longitude:hem', $tmp[ 'H' ] );
					switch( $count )
					{
						case 4:
							$theObject->offsetSet( ':location:site:longitude:sec', $tmp[ 'S' ] );
						case 3:
							$theObject->offsetSet( ':location:site:longitude:min', $tmp[ 'M' ] );
							break;
					}
				}
			}
		}
		
		//
		// Set unit area.
		//
		if( array_key_exists( 'UnitArea', $theData ) )
			$theObject->offsetSet( 'fcu:unit:area',
								   (float) $theData[ 'UnitArea' ] );
		
		//
		// Set unit ownership.
		//
		if( array_key_exists( 'UnitOwnership', $theData ) )
			$theObject->offsetSet( 'fcu:unit:ownership',
								   'fcu:unit:ownership:'.$theData[ 'UnitOwnership' ] );
		
		//
		// Set unit type.
		//
		if( array_key_exists( 'UnitType', $theData ) )
		{
			$list = Array();
			foreach( explode( ',', $theData[ 'UnitType' ] ) as $enum )
				$list[] = "fcu:unit:type:$enum";
			$theObject->offsetSet( 'fcu:unit:type', $list );
		}
		
		//
		// Set unit data collection.
		//
		if( array_key_exists( 'UnitDataCollectionYear', $theData ) )
			$theObject->offsetSet( 'fcu:unit:data-collection',
								   (string) $theData[ 'UnitDataCollectionYear' ] );
		
		//
		// Set unit last visit.
		//
		if( array_key_exists( 'UnitLastVisitYear', $theData ) )
			$theObject->offsetSet( 'fcu:unit:last-visit',
								   (string) $theData[ 'UnitLastVisitYear' ] );
		
		//
		// Set unit soil remarks.
		//
		if( array_key_exists( 'UnitSoilRemarks', $theData ) )
			$theObject->offsetSet( 'fcu:unit:remarks-soil',
								   $theData[ 'UnitSoilRemarks' ] );
		
		//
		// Set unit remarks.
		//
		if( array_key_exists( 'UnitRemarks', $theData ) )
			$theObject->offsetSet( 'fcu:unit:remarks',
								   $theData[ 'UnitRemarks' ] );
		
		//
		// Set unit taxa.
		//
		if( array_key_exists( 'UnitTaxa', $theData ) )
		{
			$tmp = explode( ';', $theData[ 'UnitTaxa' ] );
			foreach( $tmp as $key => $value )
				$tmp[ $key ] = trim( $value );
			$theObject->offsetSet( 'fcu:unit:species', $tmp );
		}
		
		//
		// Load target species data.
		//
		$sub = Array();
		loadSpecies( $sub,
					 $theData[ 'UnitID' ],
					 $theWrapper,
					 $theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( 'fcu:population', $sub );

	} // loadUnit.
	

	/**
	 * Load species data.
	 *
	 * This function will load the target species data identified by the unit identifier
	 * provided in the <b>$theUnit</b> parameter into the container provided in the
	 * <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param string				$theUnit			Unit identifier.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadSpecies( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$start = 0;
		$limit = 100;
		
		//
		// Select respondents.
		//
		$rs = $theDatabase->execute( "SELECT * FROM `fcu_species` "
									."WHERE( `UnitID` = '$theUnit' ) "
									."LIMIT $start,$limit" );
		while( $rs->RecordCount() )
		{
			//
			// Iterate page.
			//
			foreach( $rs as $record )
			{
				//
				// Scan record.
				//
				$data = Array();
				foreach( $record as $key => $value )
				{
					//
					// Normalise value.
					//
					if( strlen( trim( $value ) ) )
						$data[ $key ] = trim( $value );
			
				} // Scanning record.
			
				//
				// Skip empty records.
				//
				if( ! count( $data ) )
					continue;													// =>
				
				//
				// Init sub.
				//
				$sub = Array();
			
				//
				// Set population species.
				//
				if( array_key_exists( 'PopulationTargetSpecies', $data ) )
				{
					$pos = strpos( $data[ 'PopulationTargetSpecies' ], ' ' );
					if( $pos !== FALSE )
					{
						$genus = substr( $data[ 'PopulationTargetSpecies' ], 0, $pos );
						$species = substr( $data[ 'PopulationTargetSpecies' ], $pos + 1 );
					}
					else
					{
						$genus = $data[ 'PopulationTargetSpecies' ];
						$species = NULL;
					}
					
					if( strlen( $genus ) )
					{
						$tag = (string) $theWrapper->getSerial( ':taxon:genus' );
						$sub[ $tag ] = $genus;
					}
					if( strlen( $species ) )
					{
						$tag = (string) $theWrapper->getSerial( ':taxon:species' );
						$sub[ $tag ] = $species;
					}

					$tag = (string) $theWrapper->getSerial( ':taxon:epithet' );
					$sub[ $tag ] = $data[ 'PopulationTargetSpecies' ];
				}
			
				//
				// Set population number.
				//
				if( array_key_exists( 'PopulationUnitNumber', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:number' );
					$sub[ $tag ] = $data[ 'PopulationUnitNumber' ];
				}
			
				//
				// Set population establishment year.
				//
				if( array_key_exists( 'PopulationEstablishmentYear', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:establishment' );
					$sub[ $tag ] = (string) $data[ 'PopulationEstablishmentYear' ];
				}
			
				//
				// Set population last visit year.
				//
				if( array_key_exists( 'PopulationLastVisitYear', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:last-visit' );
					$sub[ $tag ] = (string) $data[ 'PopulationLastVisitYear' ];
				}
		
				//
				// Set population status.
				//
				if( array_key_exists( 'PopulationStatus', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:status' );
					$sub[ $tag ] = 'fcu:population:status:'
								  .$data[ 'PopulationStatus' ];
				}
		
				//
				// Set population situ.
				//
				if( array_key_exists( 'PopulationSitu', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:situ' );
					$sub[ $tag ] = 'fcu:population:situ:'
								  .$data[ 'PopulationSitu' ];
				}
		
				//
				// Set population origin.
				//
				if( array_key_exists( 'PopulationOrigin', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:origin' );
					$sub[ $tag ] = 'fcu:population:origin:'
								  .$data[ 'PopulationOrigin' ];
				}
		
				//
				// Set population system.
				//
				if( array_key_exists( 'PopulationSystem', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:system' );
					$sub[ $tag ] = 'fcu:population:system:'
								  .$data[ 'PopulationSystem' ];
				}
		
				//
				// Set population management.
				//
				if( array_key_exists( 'PopulationManagement', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:management' );
					$sub[ $tag ] = 'fcu:population:management:'
								  .$data[ 'PopulationManagement' ];
				}
		
				//
				// Set population justification.
				//
				if( array_key_exists( 'PopulationJustification', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:justification' );
					$sub[ $tag ] = 'fcu:population:justification:'
								  .$data[ 'PopulationJustification' ];
				}
		
				//
				// Set population reproducing.
				//
				if( array_key_exists( 'PopulationReproducingTrees', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:reproducing' );
					$sub[ $tag ] = 'fcu:population:reproducing:'
								  .$data[ 'PopulationReproducingTrees' ];
				}
		
				//
				// Set population sex.
				//
				if( array_key_exists( 'PopulationSexRatio', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:sex-ratio' );
					$sub[ $tag ] = 'fcu:population:sex-ratio:'
								  .$data[ 'PopulationSexRatio' ];
				}
		
				//
				// Set population regeneration.
				//
				if( array_key_exists( 'PopulationRegeneration', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:regeneration' );
					$sub[ $tag ] = 'fcu:population:regeneration:'
								  .$data[ 'PopulationRegeneration' ];
				}
		
				//
				// Set population distribution.
				//
				if( array_key_exists( 'PopulationDistribution', $data ) )
				{
					$tmp = Array();
					foreach( explode( ',', $data[ 'PopulationDistribution' ] ) as $it )
						$tmp[] = "fcu:population:distribution:$it";
					$tag = (string) $theWrapper->getSerial( 'fcu:population:distribution' );
					$sub[ $tag ] = $tmp;
				}
			
				//
				// Set population share.
				//
				if( array_key_exists( 'PopulationShare', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:share' );
					$sub[ $tag ] = $data[ 'PopulationShare' ];
				}
			
				//
				// Set population remarks.
				//
				if( array_key_exists( 'PopulationRemarks', $data ) )
				{
					$tag = (string) $theWrapper->getSerial( 'fcu:population:remarks' );
					$sub[ $tag ] = $data[ 'PopulationRemarks' ];
				}
		
				//
				// Load record.
				//
				$theContainer[] = $sub;
			
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
			$rs = $theDatabase->execute( "SELECT * FROM `fcu_species` "
										."WHERE( `UnitID` = '$theUnit' ) "
										."LIMIT $start,$limit" );
	
		} // Records left.
		
		//
		// Close iterator.
		//
		if( $rs instanceof ADORecordSet )
			$rs->Close();

	} // loadSpecies.

?>
