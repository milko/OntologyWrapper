<?php

/**
 * SQL GRIN CWR inventory archive procedure.
 *
 * This file contains routines to load GRIN CWR inventory from an SQL database and archive it as
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
 *								ArchiveCwrGrinToSQLDb.php								*
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
if( $argc < 5 )
	exit( "Usage: <script.php> "
	// MySQLi://user:pass@localhost/bioversity?socket=/tmp/mysql.sock&persist
				."<Input SQL database DSN> "
	// MySQLi://user:pass@localhost/bioversity_archive?socket=/tmp/mysql.sock&persist
				."<Output SQL database DSN> "
	// grin_cwr
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
$page = 5;
$dc_in = $dc_out = $rs = NULL;
$class = 'OntologyWrapper\Checklist';

//
// Init base query.
//
$base_query = "SELECT * from `grin_cwr`";

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
echo( "\n==> Loading GRIN CWR inventory into $table.\n" );

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
	$wrapper->loadTagCache();
	
	//
	// Resolve collection.
	//
	$collection
		= OntologyWrapper\UnitObject::ResolveCollection(
			OntologyWrapper\UnitObject::ResolveDatabase(
				$wrapper ) );
	
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
	// Import.
	//
	$pages = $page;
	echo( "  • Exporting\n" );
	$query = $base_query;
	if( $last !== NULL )
		$query .= " WHERE `Taxon` > $last";
	$query .= " ORDER BY `Taxon` LIMIT $start,$limit";
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
					   .'0x'.bin2hex( (string) $record[ 'Taxon' ] ).', '
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
		if( ! $pages-- )
		{
			echo( $start + $limit );
			$pages = $page;
		}
		else
			echo( '.' );
		
		//
		// Read next.
		//
		$start += $limit;
		$query = $base_query;
		if( $last !== NULL )
			$query .= " WHERE `Taxon` > $last";
		$query .= " ORDER BY `Taxon` LIMIT $start,$limit";
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
		//
		// Set dataset.
		//
		$theObject->offsetSet(
			':inventory:dataset',
			'U.S. National Inventory of crop wild relatives (CWR) and wild utilized species (WUS)' );
		
		//
		// Set authority.
		//
		$theObject->offsetSet( kTAG_AUTHORITY, 'USA126' );
		
		//
		// Set collection.
		//
		$theObject->offsetSet( kTAG_COLLECTION, $theData[ 'Taxon' ] );
		
		//
		// Set identifier.
		//
		$theObject->offsetSet( kTAG_IDENTIFIER, $theData[ 'Type' ] );
		
		//
		// Set version.
		//
		$theObject->offsetSet( kTAG_VERSION, '2013' );
		
		//
		// Set national inventory code.
		//
		$theObject->offsetSet( ':inventory:NICODE', 'USA' );
		
		//
		// Set country and administrative unit.
		//
		$theObject->offsetSet( ':location:country', "iso:3166:1:alpha-3:USA" );
		$theObject->offsetSet( ':inventory:admin', "iso:3166:1:alpha-3:USA" );
		
		//
		// Set checklist institute.
		//
		$theObject->offsetSet( 'cwr:INSTCODE', 'USA126' );
		
		//
		// Set familia.
		//
		if( array_key_exists( ':taxon:familia', $theData ) )
			$theObject->offsetSet( ':taxon:familia', $theData[ 'Family' ] );
		
		//
		// Set genus.
		//
		if( array_key_exists( ':taxon:genus', $theData ) )
			$theObject->offsetSet( ':taxon:genus', $theData[ 'Genus' ] );
		
		//
		// Set vernacular names.
		//
		if( array_key_exists( 'CWR_WUS_common_name', $theData ) )
			$theObject->offsetSet(
				':taxon:names',
				array(
					array( kTAG_TEXT => array( $theData[ 'CWR_WUS_common_name' ] ) ) ) );
		
		//
		// Set taxon reference.
		//
		$theObject->offsetSet(
			':taxon:reference',
			array( 'http://www.ars-grin.gov/cgi-bin/npgs/html/index.pl' ) );
		
		//
		// Set taxon designation.
		//
		if( array_key_exists( 'Type', $theData ) )
		{
			switch( $theData[ 'Type' ] )
			{
				case 'CWR':
					$tmp = 'Crop wild relative';
					break;
				case 'WUS':
					$tmp = 'Wild utilized species';
					break;
			}
			$theObject->offsetSet( ':taxon:designation', array( $tmp ) );
			
			//
			// Set taxon designation reference.
			//
			$theObject->offsetSet(
				':taxon:designation:ref',
				array( 'Khoury et al., (submitted manuscript)' ) );
		}
		
		//
		// Set priority level.
		//
		if( array_key_exists( 'Priority', $theData ) )
			$theObject->offsetSet( 'cwr:in:PRIORITY-LEVEL',
								   array( $theData[ 'Priority' ] ) );
		
		//
		// Set relation to crop.
		//
		if( array_key_exists( 'Relation_to_crop', $theData ) )
		{
			switch( $value = $theData[ 'Relation_to_crop' ] )
			{
				case 'Assume distant':
					$theObject->offsetSet( ':taxon:rel-crop', ':taxon:rel-crop:20' );
					break;
				
				case 'Close':
					$theObject->offsetSet( ':taxon:rel-crop', ':taxon:rel-crop:30' );
					break;
				
				case 'Distant':
					$theObject->offsetSet( ':taxon:rel-crop', ':taxon:rel-crop:10' );
					break;
				
				case 'Unknown':
					$theObject->offsetSet( ':taxon:rel-crop', ':taxon:rel-crop:99' );
					break;
				
				case 'WUS':
					$theObject->offsetSet( ':taxon:rel-crop', ':taxon:rel-crop:60' );
					break;
			}
		}
		
		//
		// Set genepool.
		//
		if( array_key_exists( 'GP_TG', $theData ) )
			$theObject->offsetSet( ':taxon:genepool',
								   array( $theData[ 'GP_TG' ] ) );
		
		//
		// Set genepool reference.
		//
		if( array_key_exists( 'GP_TG', $theData ) )
		{
			$value = Array();
			foreach( explode( ';', $theData[ 'GP_TG_citation' ] ) as $item )
			{
				$item = trim( $item );
				if( strlen( $item ) )
					$value[] = $item;
			}
			if( count( $value ) )
				$theObject->offsetSet( ':taxon:genepool-ref', $value );
		}
		
		//
		// Set taxon general occurence.
		//
		$list = Array();
		if( array_key_exists( 'Occurrence general', $theData ) )
			$list[] = $theData[ 'Occurrence general' ];
		if( array_key_exists( 'Occurrence specific', $theData ) )
		{
			foreach( explode( ';', $theData[ 'Occurrence specific' ] ) as $item )
			{
				if( strlen( $item = trim( $item ) ) )
				{
					if( ! in_array( $item, $list ) )
						$list[] = $item;
				}
			}
		}
		if( count( $list ) )
		{
			$value = Array();
			foreach( $list as $item )
			{
				switch( strtolower( $theData[ 'Occurrence general' ] ) )
				{
					case 'native':
						$value[] = ':taxon:occurrence-status:100';
						break;
				
					case 'non native':
						$value[] = ':taxon:occurrence-status:400';
						break;
				
					case 'adventive':
						$value[] = ':taxon:occurrence-status:430';
						break;
				
					case 'endemic':
						$value[] = ':taxon:occurrence-status:110';
						break;
				
					case 'naturalized':
						$value[] = ':taxon:occurrence-status:420';
						break;
				}
			}
			$theObject->offsetSet( ':taxon:occurrence-status', $value );
		}
		
		//
		// Set distribution.
		//
		if( array_key_exists( 'Distribution_states', $theData ) )
		{
			$dist = Array();
			$tag_dist = getTag( ':location:admin' );
			$tag_dist_notes = getTag( ':taxon:distribution:notes' );
			foreach( explode( '@', $theData[ 'Distribution_states' ] ) as $element )
			{
				if( strlen( $element = trim( $element ) ) )
				{
					$country = substr( $element, 0, 2 );
					$element = substr( $element, 4 );
					foreach( explode( ',', $element ) as $item )
					{
						if( $length = strlen( $item = trim( $item ) ) )
						{
							$dist_elm = Array();
							$state = substr( $item, 0, 2 );
							if( ($country == 'US')
							 && ($state == 'NN') )
								$state = 'NM';
							$dist_elm[ $tag_dist ] = "iso:3166:2:$country-$state";
							if( $length > 2 )
								$dist_elm[ $tag_dist_notes ] = "$country: $item";
							$dist[] = $dist_elm;
						}
					}
				}
			}
			if( count( $dist ) )
				$theObject->offsetSet( ':taxon:distribution', $dist );
		}
		
		//
		// Set national designation.
		//
		if( array_key_exists( 'Nox_weed', $theData ) )
		{
			switch( $theData[ 'Nox_weed' ] )
			{
				case 'Noxious_weed_FEDandState':
					$theObject->offsetSet( ':taxon:designation:national',
										   'Noxious weed FED and State' );
					break;
				case 'Noxious_weed_State':
					$theObject->offsetSet( ':taxon:designation:national',
										   'Noxious weed State' );
					break;
			}
		}
		
		//
		// Set taxon designation reference.
		//
		if( array_key_exists( 'CWR_WUS_citation', $theData ) )
		{
			$value = Array();
			foreach( explode( ';', $theData[ 'CWR_WUS_citation' ] ) as $item )
			{
				if( strlen( $item = trim( $item ) ) )
					$value[] = $item;
			}
			if( count( $value ) )
				$theObject->offsetSet( ':taxon:designation:ref', $value );
		}
		
		//
		// Set national designation notes.
		//
		if( array_key_exists( 'Occurrence_notes', $theData ) )
			$theObject->offsetSet( ':taxon:designation:notes',
								   $theData[ 'Occurrence_notes' ] );
		
		//
		// Init threat struct.
		//
		$threat = Array();
		
		//
		// Set national threat.
		//
		if( array_key_exists( 'US_ESA', $theData ) )
			$threat[ getTag( ':taxon:threat:national' ) ]
				= $theData[ 'US_ESA' ];
		
		//
		// Set NatServe threat.
		//
		if( array_key_exists( 'NatServe', $theData ) )
			$threat[ getTag( 'natserve:threat:code' ) ]
				= $theData[ 'NatServe' ];
		
		//
		// Set iucn criteria citation.
		//
		if( array_key_exists( 'IUCN_RL', $theData ) )
			$threat[ getTag( 'iucn:criteria-citation' ) ]
				= $theData[ 'IUCN_RL' ];
		
		//
		// Set threat.
		//
		if( count( $threat ) )
			$theObject->offsetSet( ':taxon:threat', array( $threat ) );
		
		//
		// Set associated crop taxa.
		//
		if( array_key_exists( 'Associated_crop_taxon', $theData ) )
			$theObject->offsetSet( ':taxon:rel-crop:taxa',
								   array( $theData[ 'Associated_crop_taxon' ] ) );
		
		//
		// Set associated crop common names.
		//
		if( array_key_exists( 'Associated_crop_common name', $theData ) )
		{
			$value = Array();
			foreach( explode( ',', $theData[ 'Associated_crop_common name' ] ) as $item )
			{
				if( strlen( $item = trim( $item ) ) )
				{
					if( $item != 'etc.' )
						$value[] = $item;
				}
			}
			if( count( $value ) )
				$theObject->offsetSet( ':taxon:rel-crop:names', $value );
		}
		
		//
		// Set taxon designation uses.
		//
		$value = Array();
		if( array_key_exists( 'Crop or WUS use_very general', $theData ) )
			$value[] = $theData[ 'Crop or WUS use_very general' ];
		if( array_key_exists( 'Crop or WUS use_very general', $theData ) )
			$value[] = $theData[ 'Crop or WUS use_very general' ];
		if( array_key_exists( 'Crop or WUS use_1', $theData ) )
			$value[] = $theData[ 'Crop or WUS use_1' ];
		if( array_key_exists( 'Crop or WUS use_2', $theData ) )
			$value[] = $theData[ 'Crop or WUS use_2' ];
		if( array_key_exists( 'Crop or WUS use_3', $theData ) )
			$value[] = $theData[ 'Crop or WUS use_3' ];
		if( count( $value ) )
			$theObject->offsetSet( ':taxon:designation:use', $value );

	} // loadUnit.
	

	/**
	 * Get tag.
	 *
	 * This function will return the tag serial number provided its native identifier, if
	 * the tag fails to resolve, the method will raise an exception.
	 *
	 * @param string				$theIdentifier		Native identifier.
	 * @return int					Serial identifier.
	 */
	function getTag( $theIdentifier )
	{
		global $wrapper;
		
		return $wrapper->getSerial( $theIdentifier, TRUE );							// ==>

	} // getTag.

?>
