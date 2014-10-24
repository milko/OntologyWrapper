<?php

/**
 * SQL mission archive procedure.
 *
 * This file contains routines to load missions from an SQL database and archive it as
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
 *									ArchiveQtlToSQLDb.php								*
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
	// qtl
				."<Output SQL database table> "
	// mongodb://localhost:27017/BIOVERSITY
				."<mongo database DSN> "
	// neo4j://localhost:7474 or ""
				."[graph DSN]"
	// last identifier
				."[last ID (including quotes if string)]\n" );						// ==>

//
// Init local storage.
//
$start = 0;
$limit = 100;
$page = 2;
$dc_in = $dc_out = $rs = NULL;
$class = 'OntologyWrapper\Qtl';

//
// Init base query.
//
$base_query = "SELECT * from `qtl`";

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
echo( "\n==> Loading QTLs into $table.\n" );

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
		$query .= " WHERE `id` > $last";
	$query .= " ORDER BY `id` LIMIT $start,$limit";
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
			$insert = "INSERT INTO `$table`( ";
			$insert .= ("`id`, `class`, `xml` ) VALUES( "
					   .$record[ 'id' ].', '
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
			$query .= " WHERE `id` > $last";
		$query .= " ORDER BY `id` LIMIT $start,$limit";
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
		if( array_key_exists( 'QTL:INSTCODE', $theData ) )
			$theObject->offsetSet( kTAG_AUTHORITY, $theData[ 'QTL:INSTCODE' ] );
		
		//
		// Set identifier.
		//
		if( array_key_exists( 'QTL:UNID', $theData ) )
			$theObject->offsetSet( kTAG_IDENTIFIER, $theData[ 'QTL:UNID' ] );
				
		/***********************************************************************
		 * Set unit inventory properties.
		 **********************************************************************/
		
		//
		// Set dataset.
		//
		$theObject->offsetSet(
			':inventory:dataset',
			'Plant Research International' );
		
		//
		// Set inventory code.
		//
		if( array_key_exists( 'QTL:NICODE', $theData ) )
			$theObject->offsetSet( ':inventory:code', $theData[ 'QTL:NICODE' ] );
		
		//
		// Set inventory administrative unit.
		//
		if( array_key_exists( 'QTL:NICODE', $theData ) )
		{
			$value = $theData[ 'QTL:NICODE' ];
			$theObject->offsetSet( ':inventory:admin', "iso:3166:1:alpha-3:$value" );
		}
		
		//
		// Set inventory institute.
		//
		if( array_key_exists( 'QTL:INSTCODE', $theData ) )
			$theObject->offsetSet(
				':inventory:institute',
				kDOMAIN_ORGANISATION
			   .'://http://fao.org/wiews:'
			   .$theData[ 'QTL:INSTCODE' ]
			   .kTOKEN_END_TAG );
		
		/***********************************************************************
		 * Set other properties.
		 **********************************************************************/
		
		//
		// Set QTL identifier.
		//
		if( array_key_exists( 'QTL:UNID', $theData ) )
			$theObject->offsetSet( 'qtl:UNID',
								   $theData[ 'QTL:UNID' ] );
		
		//
		// Set trait names.
		//
		if( array_key_exists( 'QTL:TRAITNAME', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'QTL:TRAITNAME' ], ';' ) ) )
				$theObject->offsetSet( 'qtl:TRAITNAME', $tmp );
		}
		
		//
		// Set QTL names.
		//
		if( array_key_exists( 'QTL:NAME', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'QTL:NAME' ], ';' ) ) )
				$theObject->offsetSet( 'qtl:NAME', $tmp );
		}
		
		//
		// Set accession identifier.
		//
		if( array_key_exists( 'QTL:ACCESSIONID', $theData ) )
		{
			$tmp = $theData[ 'QTL:ACCESSIONID' ];
			$theObject->offsetSet( ':germplasm:identifier', $tmp );
			switch( $tmp )
			{
				case 'Rivera':
					$theObject->offsetSet( 'mcpd:ACCENAME', array( $tmp ) );
					break;
				case 'BRA 2856':
					$theObject->offsetSet( 'mcpd:INSTCODE', 'DEU146' );
					$theObject->offsetSet( 'mcpd:ACCENUMB', $tmp );
					break;
			}
		}
		
		//
		// Set sequence homologues.
		//
		if( array_key_exists( 'QTL:SEQHOM', $theData ) )
			$theObject->offsetSet( 'qtl:SEQHOM',
								   $theData[ 'QTL:SEQHOM' ] );
		
		//
		// Set QTL references.
		//
		if( array_key_exists( 'QTL:REF', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'QTL:REF' ], ';' ) ) )
				$theObject->offsetSet( 'qtl:REF', $tmp );
		}
		
		//
		// Set QTL URLs.
		//
		if( array_key_exists( 'QTL:URL', $theData ) )
		{
			if( count( $tmp = setList( $theData[ 'QTL:URL' ], ';' ) ) )
				$theObject->offsetSet( 'qtl:URL', $tmp );
		}
		
		//
		// Set family.
		//
		if( array_key_exists( 'QTL:FAMILY', $theData ) )
			$theObject->offsetSet( ':taxon:familia',
								   $theData[ 'QTL:FAMILY' ] );
		//
		// Set genus.
		//
		if( array_key_exists( 'QTL:GENUS', $theData ) )
			$theObject->offsetSet( ':taxon:genus',
								   $theData[ 'QTL:GENUS' ] );
		//
		// Set species.
		//
		if( array_key_exists( 'QTL:SPECIES', $theData ) )
			$theObject->offsetSet( ':taxon:species',
								   $theData[ 'QTL:SPECIES' ] );
		//
		// Set species author.
		//
		if( array_key_exists( 'QTL:SPAUTHOR', $theData ) )
			$theObject->offsetSet( ':taxon:species',
								   $theData[ 'QTL:SPAUTHOR' ] );
		
		//
		// Set sample species name.
		//
		if( array_key_exists( 'QTL:GENUS', $theData )
		 && array_key_exists( 'QTL:SPECIES', $theData ) )
			$theObject->offsetSet(
				':taxon:species:name',
				implode( ' ', array( $theData[ 'QTL:GENUS' ],
									 $theData[ 'QTL:SPECIES' ] ) ) );
		
		//
		// Set epithet.
		//
		if( array_key_exists( 'QTL:GENUS', $theData )
		 && array_key_exists( 'QTL:SPECIES', $theData ) )
		{
			$tmp = Array();
			if( array_key_exists( 'QTL:GENUS', $theData ) )
				$tmp[] = $theData[ 'QTL:GENUS' ];
			if( array_key_exists( 'QTL:SPECIES', $theData ) )
				$tmp[] = $theData[ 'QTL:SPECIES' ];
			$theObject->offsetSet( ':taxon:epithet', implode( ' ', $tmp ) );
		}
		
		//
		// Load crossability data.
		//
		$sub = Array();
		loadCrossability( $sub,
						  $theData,
						  $theWrapper,
						  $theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':taxon:cross', $sub );
		
		//
		// Load QTL characteristics.
		//
		$sub = Array();
		loadCharacteristics( $sub,
							 $theData,
							 $theWrapper,
							 $theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( 'qtl:characteristics', $sub );
		
	} // loadUnit.
	

	/**
	 * Load crossability.
	 *
	 * This function will load the crossability data related to the provided <b>$theData</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theData			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadCrossability( &$theContainer, $theData, $theWrapper, $theDatabase )
	{
		//
		// Iterate crossability data.
		//
		$id = $theData[ 'QTL:UNID' ];
		$query = "SELECT * "
				."FROM `qtl_cross` "
				."WHERE `QTL:UNID` = "
				.'0x'.bin2hex( $id );
		$records = $theDatabase->GetAll( $query );
		foreach( $records as $record )
		{
			//
			// Init local storage.
			//
			$sub = $data = Array();
			
			//
			// Scan record.
			//
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
			// Set structure label.
			//
			if( array_key_exists( 'QTL:TYPEPOP', $data ) )
				$sub[ kTAG_STRUCT_LABEL ] = $data[ 'QTL:TYPEPOP' ];
			elseif( array_key_exists( 'QTL:SUCCROSSREF', $data ) )
				$sub[ kTAG_STRUCT_LABEL ] = $data[ 'QTL:SUCCROSSREF' ];
			else
				$sub[ kTAG_STRUCT_LABEL ] = 'cross';
		
			//
			// Set QTL cross species.
			//
			if( array_key_exists( 'QTL:LISTSPCROSS', $data ) )
			{
				if( count( $tmp = setList( $data[ 'QTL:LISTSPCROSS' ], ';' ) ) )
					$sub[ getTag( ':taxon:cross:species' ) ] = $tmp;
			}
		
			//
			// Set QTL cross methods.
			//
			if( array_key_exists( 'QTL:METHCROSSREF', $data ) )
			{
				if( count( $tmp = setList( $data[ 'QTL:METHCROSSREF' ], ';' ) ) )
					$sub[ getTag( ':taxon:cross:methods' ) ] = $tmp;
			}
		
			//
			// Set QTL cross references.
			//
			if( array_key_exists( 'QTL:LISTSPCROSSREF', $data ) )
			{
				if( count( $tmp = setList( $data[ 'QTL:LISTSPCROSSREF' ], ';' ) ) )
					$sub[ getTag( ':taxon:cross:references' ) ] = $tmp;
			}
		
			//
			// Set QTL cross success rate.
			//
			if( array_key_exists( 'QTL:SUCCROSSREF', $data ) )
				$sub[ getTag( ':taxon:cross:success' ) ]
					= $data[ 'QTL:SUCCROSSREF' ];
		
			//
			// Set QTL cross population.
			//
			if( array_key_exists( 'QTL:TYPEPOP', $data ) )
				$sub[ getTag( ':taxon:cross:population' ) ]
					= $data[ 'QTL:TYPEPOP' ];
			
			//
			// Set element.
			//
			if( count( $sub ) )
				$theContainer[] = $sub;
		}

	} // loadCrossability.
	

	/**
	 * Load characteristics.
	 *
	 * This function will load the characteristics data related to the provided
	 * <b>$theData</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theData			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadCharacteristics( &$theContainer, $theData, $theWrapper, $theDatabase )
	{
		//
		// Iterate crossability data.
		//
		$id = $theData[ 'QTL:UNID' ];
		$query = "SELECT * "
				."FROM `qtl_char` "
				."WHERE `QTL:UNID` = "
				.'0x'.bin2hex( $id );
		$records = $theDatabase->GetAll( $query );
		foreach( $records as $record )
		{
			//
			// Init local storage.
			//
			$sub = $data = Array();
			
			//
			// Scan record.
			//
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
			// Set QTL number.
			//
			if( array_key_exists( 'QTL:NUMBQTLACCESS', $data ) )
				$sub[ getTag( 'qtl:NUMBQTLACCESS' ) ]
					= $data[ 'QTL:NUMBQTLACCESS' ];
			
			//
			// Set QTL percentage of variation.
			//
			if( array_key_exists( 'QTL:PERCVARIATIONQTL', $data ) )
				$sub[ getTag( 'qtl:PERCVARIATIONQTL' ) ]
					= $data[ 'QTL:PERCVARIATIONQTL' ];
			
			//
			// Set QTL LOD score.
			//
			if( array_key_exists( 'QTL:LODSCORE', $data ) )
				$sub[ getTag( 'qtl:LODSCORE' ) ]
					= $data[ 'QTL:LODSCORE' ];
		
			//
			// Set QTL genetic map and QTL mappings.
			//
			if( array_key_exists( 'QTL:GENMAPQTLMAP', $data ) )
				$sub[ getTag( 'qtl:GENMAPQTLMAP' ) ]
					= $data[ 'QTL:GENMAPQTLMAP' ];
		
			//
			// Set QTL linked markers.
			//
			if( array_key_exists( 'QTL:MARKLINK', $data ) )
			{
				if( count( $tmp = setList( $data[ 'QTL:MARKLINK' ], ';' ) ) )
					$sub[ getTag( 'qtl:MARKLINK' ) ] = $tmp;
			}
			
			//
			// Set QTL (snp).
			//
			if( array_key_exists( 'QTL:SNP', $data ) )
				$sub[ getTag( 'qtl:SNP' ) ]
					= $data[ 'QTL:SNP' ];
		
			//
			// Set QTL parental description.
			//
			if( array_key_exists( 'QTL:PARENTDESCR', $data ) )
			{
				if( count( $tmp = setList( $data[ 'QTL:PARENTDESCR' ], ';' ) ) )
					$sub[ getTag( 'qtl:PARENTDESCR' ) ] = $tmp;
			}
		
			//
			// Set QTL parental mean standard deviation.
			//
			if( array_key_exists( 'QTL:PARENTMEANSD', $data ) )
			{
				if( count( $tmp = setList( $data[ 'QTL:PARENTMEANSD' ], ';' ) ) )
					$sub[ getTag( 'qtl:PARENTMEANSD' ) ] = $tmp;
			}
			
			//
			// Set QTL trait ontology identifier.
			//
			if( array_key_exists( 'QTL:TONUMBLINK', $data ) )
				$sub[ getTag( 'qtl:TONUMBLINK' ) ]
					= $data[ 'QTL:TONUMBLINK' ];
			
			//
			// Set QTL remarks.
			//
			if( array_key_exists( 'REMARKS', $data ) )
				$sub[ getTag( 'qtl:characteristics:remarks' ) ]
					= $data[ 'REMARKS' ];
			
			//
			// Set element.
			//
			if( count( $sub ) )
				$theContainer[] = $sub;
		}

	} // loadCharacteristics.
	

	/**
	 * Get country.
	 *
	 * This function will return the country name and its code in the provided parameter
	 * that should contain its code.
	 *
	 * @param string				$theIdentifier		Country code, receives full code.
	 * @return string				Country name.
	 */
	function getCountry( &$theIdentifier )
	{
		global $wrapper;
		
		//
		// Get country code.
		//
		$code = OntologyWrapper\Term::ResolveCountryCode( $wrapper, $theIdentifier );
		if( ! $code )
			throw new Exception( "Unknown country [$theIdentifier]." );			// !@! ==>
		
		//
		// Set country code.
		//
		$theIdentifier = $code;
		
		//
		// Get country name.
		//
		$country = new OntologyWrapper\Term( $wrapper, $code );
		
		return OntologyWrapper\OntologyObject::SelectLanguageString(
				$country[ kTAG_LABEL ], 'en' );										// ==>

	} // getCountry.
	

	/**
	 * Get region.
	 *
	 * This function will return the region name referenced by the provided parameter
	 * that should contain its code.
	 *
	 * @param string				$theIdentifier		Region code.
	 * @return string				Region name.
	 */
	function getRegion( $theIdentifier )
	{
		global $wrapper;
		
		//
		// Get region name.
		//
		$region = new OntologyWrapper\Term( $wrapper, $theIdentifier );
		
		return OntologyWrapper\OntologyObject::SelectLanguageString(
				$region[ kTAG_LABEL ], 'en' );										// ==>

	} // getRegion.
	

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
	 * Set list.
	 *
	 * This function will return a trimmed list.
	 *
	 * @param string				$theString			Strings.
	 * @param string				$theDivider			List divider.
	 * @return array				Trimmed list or an empty array.
	 */
	function setList( $theString, $theDivider )
	{
		//
		// Init local storage.
		//
		$list = Array();
		
		//
		// Split string.
		//
		foreach( explode( $theDivider, $theString ) as $item )
		{
			$item = trim( $item );
			if( strlen( $item ) )
				$list[] = $item;
		}
		
		return $list;																// ==>

	} // setList.

?>
