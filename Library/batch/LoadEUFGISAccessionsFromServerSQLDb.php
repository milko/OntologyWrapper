<?php

/**
 * MCPD (CWR) load procedure.
 *
 * This file contains routines to load the EUFGIS relative accessions from an SQL
 * database.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 17/06/2014
 */

/*=======================================================================================
 *																						*
 *						LoadEUFGISAccessionsFromServerSQLDb.php							*
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
$query = <<<EOT
SELECT
	LEFT( `INVENTORY`, LOCATE( '-', `INVENTORY` ) - 1 ) AS `:inventory:dataset`,
	IF( LEFT( `INVENTORY`, LOCATE( '-', `INVENTORY` ) - 1 ) = 'EURISCO',
		RIGHT( `INVENTORY`, 3 ),
		IF( LEFT( `INVENTORY`, LOCATE( '-', `INVENTORY` ) - 1 ) = 'GRIN',
			'USA',
			NULL ) ) AS `:inventory:NICODE`,
	IF( `INSTCODE` IS NOT NULL,
		REPLACE( `INSTCODE`, ':', '' ),
		NULL )` AS `:inventory:INSTCODE`,
	`COLLECTION` AS `:unit:collection`,
	`ACCENUMB` AS `mcpd:ACCENUMB`,
	IF( `ACQDATE` IS NOT NULL,
		IF( SUBSTRING( `ACQDATE`, 5 ) = '0000' OR
			SUBSTRING( `ACQDATE`, 5 ) = '----',
			LEFT( `ACQDATE`, 4 ),
			IF( SUBSTRING( `ACQDATE`, 7 ) = '00' OR
				SUBSTRING( `ACQDATE`, 7 ) = '--',
				LEFT( `ACQDATE`, 6 ),
				`ACQDATE` ) ),
		NULL ) AS `mcpd:ACQDATE`,
	IF( LENGTH( `STORAGE` ) > 0,
		`STORAGE`,
		NULL ) AS `mcpd:STORAGE`,
	`CROP` AS `:taxon:crop`,
	`GENUS` AS `:taxon:genus`,
	`SPECIES` AS `:taxon:species`,
	`SPAUTHOR` AS `:taxon:species:author`,
	`SUBTAXA` AS `:taxon:infraspecies`,
	`SUBTAUTHOR` AS `:taxon:infraspecies:author`,
	CONCAT_WS( ' ',
		`Genus`,
		`Species`,
		`SUBTAXA` ) AS `:taxon:epithet`,
	`CROPNAME` AS `:taxon:names`,
	`ANNEX1` AS `:taxon:annex-1`,
	IF( `MLSSTAT` = '-',
		NULL,
		`MLSSTAT` ) AS `mcpd:MLSSTAT`,
	IF( `AEGISSTAT` = '-',
		NULL,
		`AEGISSTAT` )AS `mcpd:AEGISSTAT`,
	IF( `AVAILABLE` = '-',
		NULL,
		`AVAILABLE` ) AS `mcpd:AVAILABLE`,
	`SAMPSTAT` AS `mcpd:SAMPSTAT`,
	`COLLSRC` AS `mcpd:COLLSRC`,
	IF( `COLLCODE` IS NOT NULL,
		REPLACE( `COLLCODE`, ':', '' ),
		NULL )` AS `mcpd:COLLCODE`,
	`COLLDESCR` AS `mcpd:COLLDESCR`,
	`COLLNUMB` AS `mcpd:COLLNUMB`,
	IF( `COLLDATE` IS NOT NULL,
		IF( SUBSTRING( `COLLDATE`, 5 ) = '0000' OR
			SUBSTRING( `COLLDATE`, 5 ) = '----',
			LEFT( `COLLDATE`, 4 ),
			IF( SUBSTRING( `COLLDATE`, 7 ) = '00' OR
				SUBSTRING( `COLLDATE`, 7 ) = '--',
				LEFT( `COLLDATE`, 6 ),
				`COLLDATE` ) ),
		NULL ) AS `mcpd:COLLDATE`,
	`ORIGCTY` AS `:location:country`,
	`COLLSITE` AS `:location:locality`,
	`LATITUDE` AS `mcpd:LATITUDE`,
	IF( `LATITUDE` IS NOT NULL AND
		`LATITUDED` IS NOT NULL,
		IF( `LATITUDE` LIKE '%°%',
			CONVERT(
				LEFT( `LATITUDE`,
					  LOCATE( '°', `LATITUDE` ) - 1 ),
				UNSIGNED ),
			IF( `LATITUDE` LIKE '%N%' OR
				`LATITUDE` LIKE '%S%',
				CONVERT(
					LEFT( `LATITUDE`, 2 ),
					UNSIGNED ),
				NULL ) ),
		NULL ) AS `:location:latitude:deg`,
	IF( `LATITUDE` IS NOT NULL AND
		`LATITUDED` IS NOT NULL,
		IF( `LATITUDE` LIKE '%°%',
			IF( `LATITUDE` LIKE "%'%",
				CONVERT(
					SUBSTRING( `LATITUDE`,
							   LOCATE( '°', `LATITUDE` ) + 1,
							   LOCATE( "'", `LATITUDE` ) -
							   LOCATE( '°', `LATITUDE` ) - 1 ),
					DECIMAL( 8, 6 ) ),
				NULL ),
			IF( `LATITUDE` LIKE '%N%' OR
				`LATITUDE` LIKE '%S%',
				IF( SUBSTRING( `LATITUDE`, 3, 2 ) != '--',
					CONVERT(
						SUBSTRING( `LATITUDE`, 3, 2 ),
						DECIMAL( 8, 6 ) ),
					NULL ),
				NULL ) ),
		NULL ) AS `:location:latitude:min`,
	IF( `LATITUDE` IS NOT NULL AND
		`LATITUDED` IS NOT NULL,
		IF( `LATITUDE` LIKE '%°%',
			IF( `LATITUDE` LIKE '%"%',
				CONVERT(
					SUBSTRING( `LATITUDE`,
							   LOCATE( "'", `LATITUDE` ) + 1,
							   LOCATE( '"', `LATITUDE` ) -
							   LOCATE( "'", `LATITUDE` ) - 1 ),
					DECIMAL( 8, 6 ) ),
				NULL ),
			IF( `LATITUDE` LIKE '%N%' OR
				`LATITUDE` LIKE '%S%',
				IF( SUBSTRING( `LATITUDE`, 5, 2 ) != '--',
					CONVERT(
						SUBSTRING( `LATITUDE`, 5, 2 ),
						DECIMAL( 8, 6 ) ),
					NULL ),
				NULL ) ),
		NULL ) AS `:location:latitude:sec`,
	IF( `LATITUDE` IS NOT NULL AND
		`LATITUDED` IS NOT NULL,
		IF( `LATITUDE` LIKE '%°%',
			RIGHT( `LATITUDE`, 1 ),
			NULL ),
		IF( `LATITUDE` LIKE '%N%' OR
			`LATITUDE` LIKE '%S%',
			RIGHT( `LATITUDE`, 1 ),
			NULL ) ) AS `:location:latitude:hem`,
	`LATITUDED` AS `:location:latitude`,
	`LONGITUDE` AS `mcpd:LONGITUDE`,
	IF( `LONGITUDE` IS NOT NULL AND
		`LONGITUDED` IS NOT NULL,
		IF( `LONGITUDE` LIKE '%°%',
			CONVERT(
				LEFT( `LONGITUDE`,
					  LOCATE( '°', `LONGITUDE` ) - 1 ),
				UNSIGNED ),
			IF( `LONGITUDE` LIKE '%N%' OR
				`LONGITUDE` LIKE '%S%',
				CONVERT(
					LEFT( `LONGITUDE`, 3 ),
					UNSIGNED ),
				NULL ) ),
		NULL ) AS `:location:longitude:deg`,
	IF( `LONGITUDE` IS NOT NULL AND
		`LONGITUDED` IS NOT NULL,
		IF( `LONGITUDE` LIKE '%°%',
			IF( `LONGITUDE` LIKE "%'%",
				CONVERT(
					SUBSTRING( `LONGITUDE`,
							   LOCATE( '°', `LONGITUDE` ) + 1,
							   LOCATE( "'", `LONGITUDE` ) -
							   LOCATE( '°', `LONGITUDE` ) - 1 ),
					DECIMAL( 8, 6 ) ),
				NULL ),
			IF( `LONGITUDE` LIKE '%N%' OR
				`LONGITUDE` LIKE '%S%',
				IF( SUBSTRING( `LONGITUDE`, 4, 2 ) != '--',
					CONVERT(
						SUBSTRING( `LONGITUDE`, 4, 2 ),
						DECIMAL( 8, 6 ) ),
					NULL ),
				NULL ) ),
		NULL ) AS `:location:longitude:min`,
	IF( `LONGITUDE` IS NOT NULL AND
		`LONGITUDED` IS NOT NULL,
		IF( `LONGITUDE` LIKE '%°%',
			IF( `LONGITUDE` LIKE '%"%',
				CONVERT(
					SUBSTRING( `LONGITUDE`,
							   LOCATE( "'", `LONGITUDE` ) + 1,
							   LOCATE( '"', `LONGITUDE` ) -
							   LOCATE( "'", `LONGITUDE` ) - 1 ),
					DECIMAL( 8, 6 ) ),
				NULL ),
			IF( `LONGITUDE` LIKE '%N%' OR
				`LONGITUDE` LIKE '%S%',
				IF( SUBSTRING( `LONGITUDE`, 6, 2 ) != '--',
					CONVERT(
						SUBSTRING( `LONGITUDE`, 6, 2 ),
						DECIMAL( 8, 6 ) ),
					NULL ),
				NULL ) ),
		NULL ) AS `:location:longitude:sec`,
	IF( `LONGITUDE` IS NOT NULL AND
		`LONGITUDED` IS NOT NULL,
		IF( `LONGITUDE` LIKE '%°%',
			RIGHT( `LONGITUDE`, 1 ),
			NULL ),
		IF( `LONGITUDE` LIKE '%E%' OR
			`LONGITUDE` LIKE '%W%',
			RIGHT( `LONGITUDE`, 1 ),
			NULL ) ) AS `:location:longitude:hem`,
	`LONGITUDED` AS `:location:longitude`,
	CONVERT( `ELEVATION`, SIGNED ) AS `:location:elevation`,
	IF( `DONORCODE` IS NOT NULL,
		REPLACE( `DONORCODE`, ':', '' ),
		NULL ) AS `mcpd:DONORCODE`,
	`DONORDESCR` AS `mcpd:DONORDESCR`,
	`DONORNUMB` AS `mcpd:DONORNUMB`,
	IF( `BREDCODE` IS NOT NULL,
		REPLACE( `BREDCODE`, ':', '' ),
		NULL ) AS `mcpd:BREDCODE`,
	`BREDDESCR` AS `mcpd:BREDDESCR`,
	`ANCEST` AS `mcpd:ANCEST`,
	`OTHERNUMB` AS `mcpd:OTHERNUMB`,
	IF( `DUPLSITE` IS NOT NULL,
		REPLACE( `DUPLSITE`, ':', '' ),
		NULL )` AS `mcpd:DUPLSITE`,
	`DUPLDESCR` AS `mcpd:DUPLDESCR`,
	`ACCENAME` AS `mcpd:ACCENAME`,
	`ACCEURL` AS `mcpd:ACCEURL`,
	`REMARKS` AS `mcpd:REMARKS`,
	`Stamp` AS `:unit:version`
FROM
	`ACCESSIONS`
WHERE
(
	`GENUS` IN( 'Abies', 'Acer', 'Ailanthus', 'Alnus', 'Betula', 'Buxus',
				'Carpinus', 'Carya', 'Castanea', 'Cedrus', 'Corylus', 'Cupressus',
				'Fagus', 'Frangula', 'Fraxinus', 'Ilex', 'Juglans', 'Juniperus',
				'Larix', 'Liquidambar', 'Malus', 'Ostrya', 'Phoenix', 'Picea',
				'Pinus', 'Pistacia', 'Platanus', 'Populus', 'Prunus', 'Pseudotsuga',
				'Pterocarya', 'Pyrus', 'Quercus', 'Robinia', 'Salix', 'Sorbus',
				'Taxodium', 'Taxus', 'Tilia', 'Ulmus' )
)
EOT;

//
// Load arguments.
//
$database = $argv[ 1 ];
$mongo = $argv[ 2 ];
$graph = ( $argc > 3 ) ? $argv[ 3 ] : NULL;
 
//
// Inform.
//
echo( "\n==> Loading forest gene conservation units related accessions.\n" );

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
	$rsu = $db->execute( "$query\rLIMIT $start,$limit" );
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
							$object[ 'mcpd:INSTCODE' ] = $value;
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
		$rsu = $db->execute( "$query\rLIMIT $start,$limit" );
	
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
