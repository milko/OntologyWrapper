<?php

/**
 * SQL CWR checklist archive procedure.
 *
 * This file contains routines to load CWR checklist from an SQL database and archive it as
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
 *								ArchiveCwrCkToSQLDb.php									*
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
	// cwr_ck
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
$base_query = "SELECT * from `cwr_ck`";

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
echo( "\n==> Loading CWR checklists into $table.\n" );

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
	$pages = $page;
	echo( "  • Exporting\n" );
	$query = $base_query;
	if( $last !== NULL )
		$query .= " WHERE `ID` > $last";
	$query .= " ORDER BY `ID` LIMIT $start,$limit";
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
					   .'0x'.bin2hex( (string) $record[ 'ID' ] ).', '
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
			$query .= " WHERE `ID` > $last";
		$query .= " ORDER BY `ID` LIMIT $start,$limit";
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
		$theObject->offsetSet( ':unit:authority', $theData[ ':inventory:INSTCODE' ] );
		
		//
		// Set collection.
		//
		$theObject->offsetSet( ':unit:collection', $theData[ ':taxon:epithet' ] );
		
		//
		// Set identifier.
		//
		$tmp = Array();
		if( array_key_exists( 'cwr:ck:CWRCODE', $theData ) )
			$tmp[] = $theData[ 'cwr:ck:CWRCODE' ];
		if( array_key_exists( 'cwr:ck:NUMB', $theData ) )
			$tmp[] = $theData[ 'cwr:ck:NUMB' ];
		if( count( $tmp ) )
			$theObject->offsetSet( ':unit:identifier', implode( '-', $tmp ) );
		
		//
		// Set version.
		//
		$theObject->offsetSet( ':unit:version', $theData[ 'cwr:ck:TYPE' ] );
		
		/***********************************************************************
		 * Set unit inventory properties.
		 **********************************************************************/
		
		//
		// Set dataset.
		//
		$value = $theData[ 'cwr:ck:CWRCODE' ];
		$value = ( strlen( $value ) == 3 )
			   ? "iso:3166:1:alpha-3:".$theData[ 'cwr:ck:CWRCODE' ]
			   : "iso:3166:2:".$theData[ 'cwr:ck:CWRCODE' ];
		$tmp = new OntologyWrapper\Term( $theWrapper, $value );
		$tmp = OntologyWrapper\OntologyObject::SelectLanguageString( $tmp[ kTAG_LABEL ], 'en' );
		$theObject->offsetSet( ':inventory:dataset', "$tmp crop wild relative checklist" );
		
		//
		// Set inventory code.
		//
		$theObject->offsetSet( ':inventory:code', $theData[ 'cwr:ck:CWRCODE' ] );
		
		//
		// Set inventory administrative unit.
		//
		$value = $theData[ 'cwr:ck:CWRCODE' ];
		if( strlen( $value ) == 3 )
			$theObject->offsetSet( ':inventory:admin', "iso:3166:1:alpha-3:$value" );
		elseif( substr( $value, 0, 2 ) == 'GB' )
			$theObject->offsetSet( ':inventory:admin', "iso:3166:2:$value" );
		
		//
		// Set inventory institute.
		//
		$theObject->offsetSet(
			':inventory:institute',
			kDOMAIN_ORGANISATION
		   .'://http://fao.org/wiews:'
		   .$theData[ ':inventory:INSTCODE' ]
		   .kTOKEN_END_TAG );
		
		/***********************************************************************
		 * Set other properties.
		 **********************************************************************/
		
		//
		// Set checklist code.
		//
		$theObject->offsetSet( 'cwr:ck:CWRCODE', $theData[ 'cwr:ck:CWRCODE' ] );
		
		//
		// Set checklist number.
		//
		$theObject->offsetSet( 'cwr:ck:NUMB', $theData[ 'cwr:ck:NUMB' ] );
		
		//
		// Set checklist institute.
		//
		$theObject->offsetSet( 'cwr:INSTCODE', $theData[ ':inventory:INSTCODE' ] );
		
		//
		// Set checklist type.
		//
		$theObject->offsetSet( 'cwr:ck:TYPE',
							   'cwr:ck:TYPE:'.$theData[ 'cwr:ck:TYPE' ] );
		
		//
		// Set checklist priority.
		//
		if( array_key_exists( 'cwr:in:CRITPRIORI', $theData ) )
		{
			if( $theData[ 'cwr:in:CRITPRIORI' ] == '5;6' )
				$theData[ 'cwr:in:CRITPRIORI' ] = '5.6';
			$value = Array();
			$list = explode( ';', $theData[ 'cwr:in:CRITPRIORI' ] );
			foreach( $list as $element )
				$value[] = "cwr:in:CRITPRIORI:$element";
			$theObject->offsetSet( 'cwr:in:CRITPRIORI', $value );
		}
		
		//
		// Set endemism.
		//
		if( array_key_exists( 'cwr:ENDEMISM', $theData ) )
			$theObject->offsetSet( 'cwr:ENDEMISM',
								   'cwr:ENDEMISM:'.$theData[ 'cwr:ENDEMISM' ] );
		
		//
		// Set regnum.
		//
		if( array_key_exists( ':taxon:regnum', $theData ) )
			$theObject->offsetSet( ':taxon:regnum', $theData[ ':taxon:regnum' ] );
		
		//
		// Set phylum.
		//
		if( array_key_exists( ':taxon:phylum', $theData ) )
			$theObject->offsetSet( ':taxon:phylum', $theData[ ':taxon:phylum' ] );
		
		//
		// Set classis.
		//
		if( array_key_exists( ':taxon:classis', $theData ) )
			$theObject->offsetSet( ':taxon:classis', $theData[ ':taxon:classis' ] );
		
		//
		// Set ordo.
		//
		if( array_key_exists( ':taxon:ordo', $theData ) )
			$theObject->offsetSet( ':taxon:ordo', $theData[ ':taxon:ordo' ] );
		
		//
		// Set familia.
		//
		if( array_key_exists( ':taxon:familia', $theData ) )
			$theObject->offsetSet( ':taxon:familia', $theData[ ':taxon:familia' ] );
		
		//
		// Set subfamilia.
		//
		if( array_key_exists( ':taxon:subfamilia', $theData ) )
			$theObject->offsetSet( ':taxon:subfamilia', $theData[ ':taxon:subfamilia' ] );
		
		//
		// Set tribus.
		//
		if( array_key_exists( ':taxon:tribus', $theData ) )
			$theObject->offsetSet( ':taxon:tribus', $theData[ ':taxon:tribus' ] );
		
		//
		// Set subtribus.
		//
		if( array_key_exists( ':taxon:subtribus', $theData ) )
			$theObject->offsetSet( ':taxon:subtribus', $theData[ ':taxon:subtribus' ] );
		
		//
		// Set genus.
		//
		if( array_key_exists( ':taxon:genus', $theData ) )
			$theObject->offsetSet( ':taxon:genus', $theData[ ':taxon:genus' ] );
		
		//
		// Set species.
		//
		if( array_key_exists( ':taxon:species', $theData ) )
			$theObject->offsetSet( ':taxon:species', $theData[ ':taxon:species' ] );
		
		//
		// Set species:author.
		//
		if( array_key_exists( ':taxon:species:author', $theData ) )
			$theObject->offsetSet( ':taxon:species:author',
								   $theData[ ':taxon:species:author' ] );
		
		//
		// Set infraspecies.
		//
		if( array_key_exists( ':taxon:infraspecies', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies',
								   $theData[ ':taxon:infraspecies' ] );
		
		//
		// Set infraspecies:author.
		//
		if( array_key_exists( ':taxon:infraspecies:author', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies:author',
								   $theData[ ':taxon:infraspecies:author' ] );
		
		//
		// Set epithet.
		//
		if( array_key_exists( ':taxon:epithet', $theData ) )
			$theObject->offsetSet( ':taxon:epithet',
								   $theData[ ':taxon:epithet' ] );
		
		//
		// Set taxon reference.
		//
		if( array_key_exists( ':taxon:reference', $theData ) )
			$theObject->offsetSet( ':taxon:reference',
								   array( $theData[ ':taxon:reference' ] ) );
		
		//
		// Set chromosome number.
		//
		if( array_key_exists( 'cwr:CHROMOSNUMB', $theData ) )
		{
			$value = Array();
			$list = explode( ';', $theData[ 'cwr:CHROMOSNUMB' ] );
			foreach( $list as $element )
			{
				if( strlen( $tmp = trim( $element ) ) )
					$value[] = $tmp;
			}
			if( count( $value ) )
				$theObject->offsetSet( ':taxon:chromosome-number', $value );
		}
		
		//
		// Set genepool.
		//
		if( array_key_exists( 'cwr:GENEPOOL', $theData ) )
		{
			$value = Array();
			$list = explode( ';', $theData[ 'cwr:GENEPOOL' ] );
			foreach( $list as $element )
			{
				if( strlen( $tmp = trim( $element ) ) )
					$value[] = $tmp;
			}
			if( count( $value ) )
				$theObject->offsetSet( ':taxon:genepool', $value );
		}
		
		//
		// Set taxon group.
		//
		if( array_key_exists( 'cwr:TAXONGROUP', $theData ) )
		{
			$value = Array();
			$list = explode( ';', $theData[ 'cwr:TAXONGROUP' ] );
			foreach( $list as $element )
			{
				if( strlen( $tmp = trim( $element ) ) )
					$value[] = $tmp;
			}
			if( count( $value ) )
				$theObject->offsetSet( ':taxon:group', $value );
		}
		
		//
		// Set taxon status
		//
		if( array_key_exists( 'cwr:TAXONSTATUS', $theData ) )
		{
			$value = Array();
			$list = explode( ';', $theData[ 'cwr:TAXONSTATUS' ] );
			foreach( $list as $element )
			{
				switch( $element )
				{
					case '1':
						$value[] = ":taxon:occurrence-status:100";
						break;
					case '2':
						$value[] = ":taxon:occurrence-status:130";
						break;
					case '3':
						$value[] = ":taxon:occurrence-status:200";
						break;
					case '4':
						$value[] = ":taxon:occurrence-status:300";
						break;
					case '5':
						$value[] = ":taxon:occurrence-status:400";
						break;
					case '6':
						$value[] = ":taxon:occurrence-status:490";
						break;
				}
			}
			$theObject->offsetSet( ':taxon:occurrence-status', $value );
		}
		
		//
		// Set taxon economic value.
		//
		if( array_key_exists( 'cwr:ECOVALUE', $theData ) )
			$theObject->offsetSet( ':taxon:ecovalue',
								   array( $theData[ 'cwr:ECOVALUE' ] ) );
		
		//
		// Set taxon economic value reference.
		//
		if( array_key_exists( 'cwr:ECOVALUEREF', $theData ) )
			$theObject->offsetSet( ':taxon:ecovalue-ref',
								   array( $theData[ 'cwr:ECOVALUEREF' ] ) );
		
		//
		// Set country and administrative unit.
		//
		$value = $theData[ 'cwr:ck:CWRCODE' ];
		if( strlen( $value ) == 3 )
			$theObject->offsetSet( ':location:country', "iso:3166:1:alpha-3:$value" );
		elseif( substr( $value, 0, 2 ) == 'GB' )
			$theObject->offsetSet( ':location:country', "iso:3166:1:alpha-3:GBR" );
		
		//
		// Load threat data.
		//
		$sub = Array();
		loadThreat(	$sub,
					$theData,
					$theWrapper,
					$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':taxon:threat', $sub );
		
		//
		// Set checklist remarks.
		//
		if( array_key_exists( 'cwr:REMARKS', $theData ) )
			$theObject->offsetSet( 'cwr:ck:REMARKS', $theData[ 'cwr:REMARKS' ] );

	} // loadUnit.
	

	/**
	 * Load threat data.
	 *
	 * This function will load the threat data related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadThreat( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		//
		// Init local storage.
		//
		$sub = Array();
		
		//
		// Set assessment level.
		//
		if( array_key_exists( 'cwr:ASSLEVEL', $theUnit ) )
			$sub[ getTag( ':taxon:threat:assessment' ) ]
				= ':taxon:threat:assessment:'.$theUnit[ 'cwr:ASSLEVEL' ];
		
		//
		// Set assessment region.
		//
		if( array_key_exists( 'cwr:REGIONASS', $theUnit ) )
			$sub[ getTag( ':taxon:threat:region' ) ]
				= $theUnit[ 'cwr:REGIONASS' ];
		
		//
		// Set iucn category
		//
		if( array_key_exists( 'iucn:category', $theUnit ) )
		{
			$value = Array();
			$list = explode( ';', $theUnit[ 'iucn:category' ] );
			foreach( $list as $element )
			{
				switch( $element )
				{
					case '1':
						$element = 'EX';
						break;
					case '2':
						$element = 'EW';
						break;
					case '3':
						$element = 'CR';
						break;
					case '4':
						$element = 'EN';
						break;
					case '5':
						$element = 'VU';
						break;
					case '6':
						$element = 'NT';
						break;
					case '7':
						$element = 'LC';
						break;
					case '8':
						$element = 'DD';
						break;
					case '9':
						$element = 'NE';
						break;
					case '10':
						$element = NULL;
						break;
				}
				if( $element !== NULL )
					$value[] = "iucn:category:$element";
			}
			if( count( $value ) )
				$sub[ getTag( 'iucn:category' ) ]
					= $value;
		}
		
		//
		// Set structure label.
		//
		if( array_key_exists( getTag( ':taxon:threat:region' ), $sub ) )
			$sub[ kTAG_STRUCT_LABEL ]
				= $sub[ getTag( ':taxon:threat:region' ) ];
		elseif( array_key_exists( getTag( ':taxon:threat:assessment' ), $sub ) )
			$sub[ kTAG_STRUCT_LABEL ]
				= getEnum( $sub[ getTag( ':taxon:threat:assessment' ) ] );
		elseif( array_key_exists( getTag( 'iucn:category' ), $sub ) )
			$sub[ kTAG_STRUCT_LABEL ]
				= getEnum( $sub[ getTag( 'iucn:category' ) ][ 0 ] );
		
		//
		// Load sub-structure.
		//
		if( count( $sub ) )
			$theContainer[] = $sub;

	} // loadThreat.
	

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
	

	/**
	 * Get enum.
	 *
	 * This function will return the label of the provided enumeration.
	 *
	 * @param string				$theEnum			Enumeration.
	 * @return string				Term label.
	 */
	function getEnum( $theEnum )
	{
		global $wrapper;
		
		$term = new OntologyWrapper\Term( $wrapper, $theEnum );
		return OntologyWrapper\OntologyObject::SelectLanguageString(
				$term[ kTAG_LABEL ], 'en' );										// ==>

	} // getEnum.

?>
