<?php

/**
 * SQL collecting sample archive procedure.
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
 *									GenerateCropGroups.php								*
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
// Inform.
//
echo( "\n==> Generating crop groups.\n" );

//
// Parse arguments.
//
if( $argc < 3 )
	exit( "Usage: <script.php> <user> <pass>\n" );									// ==>
$user = $argv[ 1 ];
$pass = $argv[ 2 ];

//
// Try.
//
try
{
	//
	// Init local storage.
	//
	$db_in = "MySQLi://$user:$pass@localhost/bioversity?socket=/tmp/mysql.sock&persist";
	
	//
	// Inform.
	//
	echo( "  • Creating file.\n" );
	
	//
	// Open file.
	//
	$fp = new SplFileObject( '/Library/WebServer/Library/OntologyWrapper/Library/batch/snippets/CropGroups.xml', 'w' );
			
	//
	// Connect to input database.
	//
	echo( "  • Connecting to input SQL\n" );
	echo( "    - $db_in\n" );
	$dc_in = NewADOConnection( $db_in );
	$dc_in->Execute( "SET CHARACTER SET 'utf8'" );
	$dc_in->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Generate crop categories.
	//
	echo( "  • Generating crop categories\n" );
	$query = "SELECT DISTINCT `Category`, `CodeCategory` "
			."FROM `crop_enum` ORDER BY `CodeCategory` ASC";
	$rs = $dc_in->execute( $query );
	$fp->fwrite( "\n" );
	foreach( $rs as $category )
	{
		//
		// Get category.
		//
		$kid = $category[ 'CodeCategory' ];
		$kname = $category[ 'Category' ];
		
		//
		// Set category term.
		//
		$xml = "\t<!-- :taxon:crop:category:$kid -->\n";
		$xml .= "\t<META>\n";
		$xml .= "\t\t<TERM ns=\":taxon:crop:category\" lid=\"$kid\">\n";
		$xml .= "\t\t\t<item const=\"kTAG_LABEL\">\n";
		$xml .= "\t\t\t\t<item>\n";
		$xml .= "\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n";
		$xml .= "\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[$kname]]></item>\n";
		$xml .= "\t\t\t\t</item>\n";
		$xml .= "\t\t\t</item>\n";
		$xml .= "\t\t</TERM>\n";
		
		//
		// Set category node and edge.
		//
		$xml .= "\t\t<NODE>\n";
		$xml .= "\t\t\t<item const=\"kTAG_NODE_TYPE\">\n";
		$xml .= "\t\t\t\t<item>:type:node:enumeration</item>\n";
		$xml .= "\t\t\t</item>\n";
		$xml .= "\t\t</NODE>\n";
		$xml .= "\t\t<EDGE>\n";
		$xml .= "\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:ENUM-OF</item>\n";
		$xml .= "\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">:taxon:crop:category</item>\n";
		$xml .= "\t\t</EDGE>\n";
		$xml .= "\t</META>\n";
		
		//
		// Write file.
		//
		$fp->fwrite( $xml );
	}
	
	//
	// Close recordset.
	//
	$rs->Close();
	$rs = NULL;
	
	//
	// Generate crop groups.
	//
	echo( "  • Generating crop groups\n" );
	$query = "SELECT DISTINCT `Category`, `CodeCategory`, `Group`, `CodeGroup` "
			."FROM `crop_enum` "
			."ORDER BY `Group` ASC";
	$rs = $dc_in->execute( $query );
	$fp->fwrite( "\n" );
	foreach( $rs as $group )
	{
		//
		// Get category.
		//
		$kid = $category[ 'CodeCategory' ];
		
		//
		// Get group.
		//
		$gid = $group[ 'CodeGroup' ];
		$gname = $group[ 'Group' ];
		
		//
		// Set group term.
		//
		$xml = "\t<!-- :taxon:crop:group:$gid -->\n";
		$xml .= "\t<META>\n";
		$xml .= "\t\t<TERM ns=\":taxon:crop:group\" lid=\"$gid\">\n";
		$xml .= "\t\t\t<item const=\"kTAG_LABEL\">\n";
		$xml .= "\t\t\t\t<item>\n";
		$xml .= "\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n";
		$xml .= "\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[$gname]]></item>\n";
		$xml .= "\t\t\t\t</item>\n";
		$xml .= "\t\t\t</item>\n";
		$xml .= "\t\t\t<item tag=\":taxon:category\">:taxon:crop:category:$kid</item>\n";
		$xml .= "\t\t</TERM>\n";
		
		//
		// Set group node and edge.
		//
		$xml .= "\t\t<NODE>\n";
		$xml .= "\t\t\t<item const=\"kTAG_NODE_TYPE\">\n";
		$xml .= "\t\t\t\t<item>:type:node:enumeration</item>\n";
		$xml .= "\t\t\t</item>\n";
		$xml .= "\t\t</NODE>\n";
		$xml .= "\t\t<EDGE>\n";
		$xml .= "\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:ENUM-OF</item>\n";
		$xml .= "\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">:taxon:crop:group</item>\n";
		$xml .= "\t\t</EDGE>\n";
		$xml .= "\t\t<EDGE>\n";
		$xml .= "\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:SUBSET-OF</item>\n";
		$xml .= "\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">:taxon:crop:category:$kid</item>\n";
		$xml .= "\t\t</EDGE>\n";
		$xml .= "\t</META>\n";
		
		//
		// Write file.
		//
		$fp->fwrite( $xml );
	}
	
	//
	// Close recordset.
	//
	$rs->Close();
	$rs = NULL;
	
	//
	// Generate crops.
	//
	echo( "  • Generating crops\n" );
	$query = "SELECT DISTINCT "
			."`Category`, `CodeCategory`, "
			."`Group`, `CodeGroup`, "
			."`Crop`, `CodeCrop` "
			."FROM `crop_enum` "
			."ORDER BY `Crop` ASC";
	$rs = $dc_in->execute( $query );
	$fp->fwrite( "\n" );
	foreach( $rs as $crop )
	{
		//
		// Get category.
		//
		$kid = $category[ 'CodeCategory' ];
		
		//
		// Get group.
		//
		$gid = $crop[ 'CodeGroup' ];
		
		//
		// Get crop.
		//
		$cid = $crop[ 'CodeCrop' ];
		$cname = $crop[ 'Crop' ];
		
		//
		// Collect taxa.
		//
		$query = "SELECT `Family`, `Genus`, `Species` "
				."FROM `crop_enum` "
				."WHERE `CodeCrop` = $cid";
		$taxa = $dc_in->GetAll( $query );
		
		//
		// Set crop term.
		//
		$xml = "\t<!-- :taxon:crop:$cid -->\n";
		$xml .= "\t<META>\n";
		$xml .= "\t\t<TERM ns=\":taxon:crop\" lid=\"$cid\">\n";
		$xml .= "\t\t\t<item const=\"kTAG_LABEL\">\n";
		$xml .= "\t\t\t\t<item>\n";
		$xml .= "\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n";
		$xml .= "\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[$cname]]></item>\n";
		$xml .= "\t\t\t\t</item>\n";
		$xml .= "\t\t\t</item>\n";
		$xml .= "\t\t\t<item tag=\":taxon:category\">:taxon:crop:category:$kid</item>\n";
		$xml .= "\t\t\t<item tag=\":taxon:group\">:taxon:crop:group:$gid</item>\n";
		
		//
		// Handle taxa.
		//
		if( count( $taxa ) )
		{
			//
			// Open section.
			//
			$xml .= "\t\t\t<item tag=\":taxon:crop:taxa\">\n";
			
			foreach( $taxa as $taxon )
			{
				//
				// Open element
				$xml .= "\t\t\t\t<item>\n";
				
				//
				// Determine label.
				//
				$label = Array();
				if( array_key_exists( 'Genus', $taxon ) )
					$label[] = $taxon[ 'Genus' ];
				if( array_key_exists( 'Species', $taxon ) )
					$label[] = $taxon[ 'Species' ];
				$label = count( $label )
					   ? implode( ' ', $label )
					   : 'taxon';
				
				//
				// Add label.
				//
				$xml .= "\t\t\t\t\t<item tag=\":struct-label\">$label</item>\n";
				
				//
				// Add taxa.
				//
				foreach( $taxon as $key => $value )
				{
					//
					// Check value.
					//
					if( strlen( trim( $value ) ) )
					{
						//
						// Parse epithet.
						//
						switch( $key )
						{
							case 'Family':
								$tag = ':taxon:familia';
								break;
							case 'Genus':
								$tag = ':taxon:genus';
								break;
							case 'Species':
								$tag = ':taxon:species';
								break;
							
							default:
								$tag = NULL;
								break;
						}
						
						//
						// Add epithet.
						//
						if( $tag !== NULL )
							$xml .= "\t\t\t\t\t<item tag=\"$tag\">$value</item>\n";
					}
				}
				
				//
				// Close element.
				//
				$xml .= "\t\t\t\t</item>\n";
			}
			
			//
			// Close section.
			//
			$xml .= "\t\t\t</item>\n";
		}
		
		//
		// Close term.
		//
		$xml .= "\t\t</TERM>\n";
	
		//
		// Set crop node and edge.
		//
		$xml .= "\t\t<NODE>\n";
		$xml .= "\t\t\t<item const=\"kTAG_NODE_TYPE\">\n";
		$xml .= "\t\t\t\t<item>:type:node:enumeration</item>\n";
		$xml .= "\t\t\t</item>\n";
		$xml .= "\t\t</NODE>\n";
		$xml .= "\t\t<EDGE>\n";
		$xml .= "\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:ENUM-OF</item>\n";
		$xml .= "\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">:taxon:crop</item>\n";
		$xml .= "\t\t</EDGE>\n";
		$xml .= "\t\t<EDGE>\n";
		$xml .= "\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:SUBSET-OF</item>\n";
		$xml .= "\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">:taxon:crop:group:$gid</item>\n";
		$xml .= "\t\t</EDGE>\n";
		$xml .= "\t</META>\n";
		
		//
		// Write file.
		//
		$fp->fwrite( $xml );
	}
	
	//
	// Close recordset.
	//
	$rs->Close();
	$rs = NULL;

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
	if( $rs instanceof ADORecordset )
		$rs->Close();
	if( $dc_in instanceof ADOConnection )
		$dc_in->Close();

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
		$theObject->offsetSet( kTAG_AUTHORITY, 'ITA406' );
		
		//
		// Set collection.
		//
		$theObject->offsetSet( kTAG_COLLECTION,
							   $theData[ ':collecting:mission:identifier' ]
							  .'/'
							  .$theData[ ':collecting:event:identifier' ] );
		
		//
		// Set identifier.
		//
		$theObject->offsetSet( kTAG_IDENTIFIER, $theData[ 'ID' ] );
				
		/***********************************************************************
		 * Set unit inventory properties.
		 **********************************************************************/
		
		//
		// Set dataset.
		//
		$theObject->offsetSet(
			':inventory:dataset',
			'Germplasm Collecting Missions Database' );
		
		/***********************************************************************
		 * Set other properties.
		 **********************************************************************/
		
		//
		// Set mission.
		//
		if( array_key_exists( ':collecting:mission:identifier', $theData ) )
		{
			//
			// Set mission.
			//
			$theObject->offsetSet( ':mission',
								   OntologyWrapper\Mission::kDEFAULT_DOMAIN
								  .'://'
								  .'ITA406/'
								  .$theData[ ':collecting:mission:identifier' ]
								  .kTOKEN_END_TAG );
			
			//
			// Set code.
			//
			$theObject->offsetSet( ':mission:identifier',
								   $theData[ ':collecting:mission:identifier' ] );
		}
		
		//
		// Set collecting mission.
		//
		if( array_key_exists( ':collecting:event:identifier', $theData ) )
		{
			//
			// Set collecting mission.
			//
			$theObject->offsetSet( ':mission:collecting',
								   OntologyWrapper\CollectingMission::kDEFAULT_DOMAIN
								  .'://'
								  .'ITA406/'
								  .$theData[ ':collecting:mission:identifier' ]
								  .':'
								  .$theData[ ':collecting:event:identifier' ]
								  .kTOKEN_END_TAG );
			
			//
			// Set code.
			//
			$theObject->offsetSet( ':mission:collecting:identifier',
								   $theData[ ':collecting:event:identifier' ] );
		}
		
		//
		// Set germplasm identifier.
		//
		$theObject->offsetSet( ':germplasm:identifier',
							   $theData[ ':collecting:mission:identifier' ]
							  .':'
							  .$theData[ ':collecting:event:identifier' ]
							  .'/'
							  .$theData[ 'ID' ] );
		
		//
		// Set collecting date.
		//
		if( array_key_exists( 'mcpd:COLLDATE', $theData ) )
			$theObject->offsetSet( 'mcpd:COLLDATE',
								   $theData[ 'mcpd:COLLDATE' ] );
		
		//
		// Set collecting number.
		//
		if( array_key_exists( 'mcpd:COLLNUMB', $theData ) )
			$theObject->offsetSet( 'mcpd:COLLNUMB',
								   $theData[ 'mcpd:COLLNUMB' ] );
		
		//
		// Set collecting source.
		//
		if( array_key_exists( 'MCPD:COLLSRC', $theData ) )
			$theObject->offsetSet( 'mcpd:COLLSRC',
								   $theData[ 'MCPD:COLLSRC' ] );
		
		//
		// Set sample status.
		//
		if( array_key_exists( 'MCPD:SAMPSTAT', $theData ) )
			$theObject->offsetSet( 'mcpd:SAMPSTAT',
								   $theData[ 'MCPD:SAMPSTAT' ] );
		
		//
		// Set sample family.
		//
		if( array_key_exists( ':taxon:familia', $theData ) )
			$theObject->offsetSet( ':taxon:genus',
								   $theData[ ':taxon:genus' ] );
		
		//
		// Set sample genus.
		//
		if( array_key_exists( ':taxon:genus', $theData ) )
			$theObject->offsetSet( ':taxon:genus',
								   $theData[ ':taxon:genus' ] );
		
		//
		// Set sample sectio.
		//
		if( array_key_exists( ':taxon:sectio', $theData ) )
			$theObject->offsetSet( ':taxon:sectio',
								   $theData[ ':taxon:sectio' ] );
		
		//
		// Set sample species.
		//
		if( array_key_exists( ':taxon:species', $theData ) )
			$theObject->offsetSet( ':taxon:species',
								   $theData[ ':taxon:species' ] );
		
		//
		// Set sample infraspecific epithet.
		//
		if( array_key_exists( ':taxon:infraspecies', $theData ) )
			$theObject->offsetSet( ':taxon:infraspecies',
								   $theData[ ':taxon:infraspecies' ] );
		
		//
		// Set sample species name.
		//
		if( array_key_exists( ':taxon:genus', $theData )
		 && array_key_exists( ':taxon:species', $theData ) )
			$theObject->offsetSet(
				':taxon:species:name',
				implode( ' ', array( $theData[ ':taxon:genus' ],
									 $theData[ ':taxon:species' ] ) ) );
		
		//
		// Set sample epithet.
		//
		if( array_key_exists( ':taxon:epithet', $theData ) )
			$theObject->offsetSet( ':taxon:epithet',
								   $theData[ ':taxon:epithet' ] );
		
		//
		// Set sample valid name.
		//
		if( array_key_exists( ':taxon:valid', $theData ) )
			$theObject->offsetSet( ':taxon:valid',
								   $theData[ ':taxon:valid' ] );
		
		//
		// Set vernacular names.
		//
		if( array_key_exists( ':taxon:names', $theData ) )
			$theObject->offsetSet( ':taxon:names',
								   array(
								   	array( kTAG_TEXT => array(
								   		$theData[ ':taxon:names' ] ) ) ) );
		
		//
		// Set sample designation use.
		//
		if( array_key_exists( ':taxon:designation:use', $theData ) )
			$theObject->offsetSet( ':taxon:designation:use',
								   array( $theData[ ':taxon:designation:use' ] ) );
		
		//
		// Set sample taxon reference.
		//
		if( array_key_exists( 'TAXCODE_GRIN', $theData ) )
		{
			$theObject->offsetSet(
				':taxon:reference',
				array( 'http://www.ars-grin.gov/cgi-bin/npgs/html/index.pl' ) );
			$theObject->offsetSet(
				':taxon:url',
				'http://www.ars-grin.gov/cgi-bin/npgs/html/taxon.pl?'
			   .$theData[ 'TAXCODE_GRIN' ] );
		}
		
		//
		// Set country.
		//
		if( array_key_exists( ':location:country', $theData ) )
		{
			$code = $theData[ ':location:country' ];
			$name = getCountry( $code );
			$theObject->offsetSet( ':location:country', $code );
		}
		
		//
		// Set sample admin 1.
		//
		if( array_key_exists( ':location:admin-1', $theData ) )
			$theObject->offsetSet( ':location:admin-1',
								   $theData[ ':location:admin-1' ] );
		
		//
		// Set sample admin 2.
		//
		if( array_key_exists( ':location:admin-2', $theData ) )
			$theObject->offsetSet( ':location:admin-2',
								   $theData[ ':location:admin-2' ] );
		
		//
		// Set sample admin 3.
		//
		if( array_key_exists( ':location:admin-3', $theData ) )
			$theObject->offsetSet( ':location:admin-3',
								   $theData[ ':location:admin-3' ] );
		
		//
		// Set sample locality.
		//
		if( array_key_exists( ':location:locality', $theData ) )
			$theObject->offsetSet( ':location:locality',
								   $theData[ ':location:locality' ] );
		
		//
		// Set sample elevation.
		//
		if( array_key_exists( ':location:site:elevation', $theData ) )
			$theObject->offsetSet( ':location:site:elevation',
								   $theData[ ':location:site:elevation' ] );
		
		//
		// Set sample provided latitude.
		//
		if( array_key_exists( ':location:site:latitude:provided', $theData ) )
			$theObject->offsetSet( ':location:site:latitude:provided',
								   $theData[ ':location:site:latitude:provided' ] );
		
		//
		// Set sample latitude.
		//
		if( array_key_exists( ':location:site:latitude', $theData ) )
			$theObject->offsetSet( ':location:site:latitude',
								   $theData[ ':location:site:latitude' ] );
		
		//
		// Set sample provided longitude.
		//
		if( array_key_exists( ':location:site:longitude:provided', $theData ) )
			$theObject->offsetSet( ':location:site:longitude:provided',
								   $theData[ ':location:site:longitude:provided' ] );
		
		//
		// Set sample longitude.
		//
		if( array_key_exists( ':location:site:longitude', $theData ) )
			$theObject->offsetSet( ':location:site:longitude',
								   $theData[ ':location:site:longitude' ] );
		
		//
		// Set sample error.
		//
		if( array_key_exists( ':location:site:error', $theData ) )
			$theObject->offsetSet( ':location:site:error',
								   $theData[ ':location:site:error' ] );
		
		//
		// Set sample georeference source.
		//
		if( array_key_exists( ':location:site:georeference-source', $theData ) )
			$theObject->offsetSet( ':location:site:georeference-source',
								   $theData[ ':location:site:georeference-source' ] );
		
		//
		// Set sample georeference method.
		//
		if( array_key_exists( ':location:site:georeference-method', $theData ) )
			$theObject->offsetSet( ':location:site:georeference-method',
								   $theData[ ':location:site:georeference-method' ] );
		
		//
		// Set sample georeference tool.
		//
		if( array_key_exists( ':location:site:georeference-tool', $theData ) )
			$theObject->offsetSet( ':location:site:georeference-tool',
								   $theData[ ':location:site:georeference-tool' ] );
		
		//
		// Set sample site notes.
		//
		if( array_key_exists( ':location:site:notes', $theData ) )
			$theObject->offsetSet( ':location:site:notes',
								   $theData[ ':location:site:notes' ] );
		
		//
		// Load collectors.
		//
		$sub = Array();
		loadCollectors( $sub,
						$theData,
						$theWrapper,
						$theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':collecting:entities', $sub );
		
		//
		// Load germplasm neighbourhood.
		//
		$sub = Array();
		loadNeighbourhood( $sub,
						   $theData,
						   $theWrapper,
						   $theDatabase );
		if( count( $sub ) )
			$theObject->offsetSet( ':germplasm:neighbourhood', $sub );
		
		//
		// Set collecting event notes.
		//
		if( array_key_exists( ':collecting:event:notes', $theData ) )
			$theObject->offsetSet( ':mission:collecting:notes',
								   $theData[ ':collecting:event:notes' ] );
		
		//
		// Set collecting sample notes.
		//
		if( array_key_exists( ':collecting:samples:notes', $theData ) )
			$theObject->offsetSet( ':collecting:notes',
								   $theData[ ':collecting:samples:notes' ] );
		
	} // loadUnit.
	

	/**
	 * Load collectors.
	 *
	 * This function will load the mission collectors related to the provided <b>$theUnit</b>
	 * parameter into the container provided in the <b>$theContainer</b> parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadCollectors( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		global $wrapper;
		
		//
		// Check institutes.
		//
		if( array_key_exists( 'mcpd:COLLCODE', $theUnit ) )
		{
			//
			// Get institutes.
			//
			$institutes = explode( ';', $theUnit[ 'mcpd:COLLCODE' ] );
			foreach( $institutes as $institute )
			{
				//
				// Init local storage.
				//
				$sub = $data = Array();
	
				//
				// Check institute.
				//
				if( strlen( $institute = trim( $institute ) ) )
				{
					//
					// Get institute.
					//
					$query = "SELECT * FROM `cmdb_institutes` "
							."WHERE `INSTCODE` = "
							.'0x'.bin2hex( $institute );
					$record = $theDatabase->GetRow( $query );
					
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
						continue;											// =>
					//
					// Determine institute identifier.
					//
					$institute_id = ( array_key_exists( 'FAOCODE', $data ) )
								  ? (kDOMAIN_ORGANISATION
									.'://http://fao.org/wiews:'
									.strtoupper( $data[ 'FAOCODE' ] )
									.kTOKEN_END_TAG)
								  : NULL;
			
					//
					// Determine institute object.
					//
					$institute = ( $institute_id !== NULL )
							   ? new OntologyWrapper\FAOInstitute( $wrapper, $institute_id,
							   												 FALSE )
							   : NULL;
			
					//
					// Check institute object.
					//
					if( $institute !== NULL )
					{
						if( ! $institute->committed() )
							$institute = $institute_id = NULL;
					}
			
					//
					// Set organisation name.
					//
					if( array_key_exists( 'NAME_NAT', $data ) )
						$sub[ kTAG_NAME ] = $data[ 'NAME_NAT' ];
					elseif( array_key_exists( 'NAME_ENG', $data ) )
						$sub[ kTAG_NAME ] = $data[ 'NAME_ENG' ];
					elseif( $institute !== NULL )
						$sub[ kTAG_NAME ] = $institute->offsetGet( ':name' );
					elseif( array_key_exists( 'ACRONYM', $data ) )
						$sub[ kTAG_NAME ] = $data[ 'ACRONYM' ];
					elseif( array_key_exists( 'ECPACRONYM', $data ) )
						$sub[ kTAG_NAME ] = $data[ 'ECPACRONYM' ];
					else
						$sub[ kTAG_NAME ] = 'unknown';
			
					//
					// Set collecting institute code.
					//
					if( array_key_exists( 'FAOCODE', $data ) )
						$sub[ getTag( 'mcpd:COLLCODE' ) ]
							= $data[ 'FAOCODE' ];
			
					//
					// Set collecting institute name.
					//
					if( array_key_exists( 'NAME_NAT', $data ) )
						$sub[ getTag( 'mcpd:COLLDESCR' ) ]
							= $data[ 'NAME_NAT' ];
					elseif( array_key_exists( 'NAME_ENG', $data ) )
						$sub[ getTag( 'mcpd:COLLDESCR' ) ]
							= $data[ 'NAME_ENG' ];
		
					//
					// Set entity type.
					//
					if( array_key_exists( 'ORGTYPE', $data ) )
						$sub[ getTag( ':type:entity' ) ]
							= explode( ';', $data[ 'ORGTYPE' ] );
		
					//
					// Set entity kind.
					//
					if( array_key_exists( 'ORGKIND', $data ) )
						$sub[ getTag( ':kind:entity' ) ]
							= explode( ';', $data[ 'ORGKIND' ] );
			
					//
					// Set institute.
					//
					if( $institute_id !== NULL )
						$sub[ getTag( ':inventory:institute' ) ]
							= $institute_id;
			
					//
					// Set cooperator details.
					//
					else
					{
						//
						// Set entity acronym.
						//
						$tmp = Array();
						if( array_key_exists( 'ACRONYM', $data ) )
							$tmp[] = $data[ 'ACRONYM' ];
						if( array_key_exists( 'ECPACRONYM', $data ) )
							$tmp[] = $data[ 'ECPACRONYM' ];
						if( count( $tmp ) )
							$sub[ getTag( ':entity:acronym' ) ]
								= $tmp;
			
						//
						// Set URL.
						//
						if( array_key_exists( 'URL', $data ) )
							$sub[ getTag( ':entity:url' ) ]
								= array( array( kTAG_TEXT => $data[ 'URL' ] ) );
			
						//
						// Set nationality.
						//
						$country_name = $country_code = NULL;
						if( array_key_exists( 'CTY', $data ) )
						{
							$country_code = $data[ 'CTY' ];
							$country_name = getCountry( $country_code );
							$sub[ getTag( ':entity:nationality' ) ] = $country_code;
						}
		
						//
						// Set address.
						//
						$address = Array();
						if( array_key_exists( 'STREET_POB', $record ) )
						{
							if( strlen( $tmp = trim( $record[ 'STREET_POB' ] ) ) )
								$address[] = $record[ 'STREET_POB' ];
						}
						$city = '';
						if( array_key_exists( 'ZIP_CODE', $record ) )
						{
							if( strlen( $tmp = trim( $record[ 'ZIP_CODE' ] ) ) )
								$city .= ($record[ 'ZIP_CODE' ].' ');
						}
						if( array_key_exists( 'CITY_STATE', $record ) )
						{
							if( strlen( $tmp = trim( $record[ 'CITY_STATE' ] ) ) )
								$city .= ($record[ 'CITY_STATE' ].' ');
						}
						if( strlen( $city ) )
							$address[] = $city;
						if( $country_name !== NULL )
							$address[] = $country_name;
						if( count( $address ) )
							$sub[ getTag( ':entity:mail' ) ]
								= array( array( kTAG_TEXT => implode( "\n", $address ) ) );
			
						//
						// Set e-mail.
						//
						if( array_key_exists( 'EMA', $data ) )
							$sub[ getTag( ':entity:email' ) ]
								= array( array( kTAG_TEXT => $data[ 'EMA' ] ) );
			
						//
						// Set telephone.
						//
						if( array_key_exists( 'TLF', $data ) )
							$sub[ getTag( ':entity:phone' ) ]
								= array( array( kTAG_TEXT => $data[ 'TLF' ] ) );
			
						//
						// Set telefax.
						//
						if( array_key_exists( 'FAX', $data ) )
							$sub[ getTag( ':entity:fax' ) ]
								= array( array( kTAG_TEXT => $data[ 'FAX' ] ) );
			
						//
						// Set telex.
						//
						if( array_key_exists( 'TLX', $data ) )
							$sub[ getTag( ':entity:tlx' ) ]
								= array( array( kTAG_TEXT => $data[ 'TLX' ] ) );
		
						//
						// Set elevation.
						//
						if( array_key_exists( 'ALT', $data ) )
							$sub[ getTag( ':location:site:elevation' ) ]
								= $data[ 'ALT' ];
		
						//
						// Set latitude.
						//
						if( array_key_exists( 'LAT', $data ) )
							$sub[ getTag( ':location:site:latitude:provided' ) ]
								= $data[ 'LAT' ];
		
						//
						// Set longitude.
						//
						if( array_key_exists( 'LONG_', $data ) )
							$sub[ getTag( ':location:site:longitude:provided' ) ]
								= $data[ 'LONG_' ];
		
						//
						// Set version.
						//
						if( array_key_exists( 'UPDATED', $data ) )
							$sub[ getTag( ':unit:version' ) ]
								= $data[ 'UPDATED' ];
			
					} // Not an institute or not known institite.

					//
					// Set remarks.
					//
					if( array_key_exists( 'REMARKS', $data ) )
						$sub[ getTag( ':notes' ) ]
							= array( $data[ 'REMARKS' ] );
			
					//
					// Set element.
					//
					if( count( $sub ) )
						$theContainer[] = $sub;
				
				} // Has institute.
			
			} // Iterating institutes.
		
		} // Has collectors.

	} // loadCollectors.
	

	/**
	 * Load germplasm neighbourhood.
	 *
	 * This function will load the accession germplasm neighbourhood related to the provided
	 * <b>$theUnit</b> parameter into the container provided in the <b>$theContainer</b>
	 * parameter.
	 *
	 * @param array					$theContainer		Container.
	 * @param array					$theUnit			Unit data.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param ADOConnection			$theDatabase		SQL connection.
	 */
	function loadNeighbourhood( &$theContainer, $theUnit, $theWrapper, $theDatabase )
	{
		global $wrapper;
		
		//
		// Iterate taxa data.
		//
		$id = $theUnit[ 'ID' ];
		$query = "SELECT * FROM `cmdb_sample_neighbourhood` "
				."WHERE `NEW_ID_SAMPLE` = $id";
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
			// Load institute.
			//
			$query = "SELECT * FROM `cmdb_institutes` "
					."WHERE `INSTCODE` = '"
					.$data[ 'INSTCODE' ]
					."'";
			$tmp = $theDatabase->GetRow( $query );
			$instrec = Array();
			foreach( $tmp as $key => $value )
			{
				//
				// Normalise value.
				//
				if( strlen( trim( $value ) ) )
					$instrec[ $key ] = trim( $value );
			
			} // Scanning record.
			
			//
			// Determine institute identifier.
			//
			$institute_id = ( array_key_exists( 'FAOCODE', $instrec ) )
						  ? (kDOMAIN_ORGANISATION
							.'://http://fao.org/wiews:'
							.strtoupper( $instrec[ 'FAOCODE' ] )
							.kTOKEN_END_TAG)
						  : NULL;
			
			//
			// Determine institute object.
			//
			$institute = ( $institute_id !== NULL )
					   ? new OntologyWrapper\FAOInstitute( $wrapper, $institute_id, FALSE )
					   : NULL;
			
			//
			// Check institute object.
			//
			if( $institute !== NULL )
			{
				if( ! $institute->committed() )
					$institute = $institute_id = NULL;
				else
					$instcode = $instrec[ 'FAOCODE' ];
			}
			else
				$instcode = $instrec[ 'INSTCODE' ];
			
			//
			// Set germplasm identifier.
			//
			$sub[ getTag( ':germplasm:identifier' ) ]
				= $instcode.':'.$data[ 'AccessionNumber' ];
			
			//
			// Set institute object.
			//
			if( $institute_id !== NULL )
				$sub[ getTag( ':inventory:institute' ) ]
					= $institute_id;
			
			//
			// Set institute details.
			//
			else
			{
				//
				// Set entity acronym.
				//
				$tmp = Array();
				if( array_key_exists( 'ACRONYM', $instrec ) )
					$tmp[] = $instrec[ 'ACRONYM' ];
				if( array_key_exists( 'ECPACRONYM', $instrec ) )
					$tmp[] = $instrec[ 'ECPACRONYM' ];
				if( count( $tmp ) )
					$sub[ getTag( ':entity:acronym' ) ]
						= $tmp;
			
				//
				// Set URL.
				//
				if( array_key_exists( 'URL', $instrec ) )
					$sub[ getTag( ':entity:url' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'URL' ] ) );
			
				//
				// Set nationality.
				//
				$country_name = $country_code = NULL;
				if( array_key_exists( 'CTY', $instrec ) )
				{
					$country_code = $instrec[ 'CTY' ];
					$country_name = getCountry( $country_code );
					$sub[ getTag( ':entity:nationality' ) ] = $country_code;
				}
		
				//
				// Set address.
				//
				$address = Array();
				if( array_key_exists( 'STREET_POB', $record ) )
				{
					if( strlen( $tmp = trim( $record[ 'STREET_POB' ] ) ) )
						$address[] = $record[ 'STREET_POB' ];
				}
				$city = '';
				if( array_key_exists( 'ZIP_CODE', $record ) )
				{
					if( strlen( $tmp = trim( $record[ 'ZIP_CODE' ] ) ) )
						$city .= ($record[ 'ZIP_CODE' ].' ');
				}
				if( array_key_exists( 'CITY_STATE', $record ) )
				{
					if( strlen( $tmp = trim( $record[ 'CITY_STATE' ] ) ) )
						$city .= ($record[ 'CITY_STATE' ].' ');
				}
				if( strlen( $city ) )
					$address[] = $city;
				if( $country_name !== NULL )
					$address[] = $country_name;
				if( count( $address ) )
					$sub[ getTag( ':entity:mail' ) ]
						= array( array( kTAG_TEXT => implode( "\n", $address ) ) );
			
				//
				// Set e-mail.
				//
				if( array_key_exists( 'EMA', $instrec ) )
					$sub[ getTag( ':entity:email' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'EMA' ] ) );
			
				//
				// Set telephone.
				//
				if( array_key_exists( 'TLF', $instrec ) )
					$sub[ getTag( ':entity:phone' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'TLF' ] ) );
			
				//
				// Set telefax.
				//
				if( array_key_exists( 'FAX', $instrec ) )
					$sub[ getTag( ':entity:fax' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'FAX' ] ) );
			
				//
				// Set telex.
				//
				if( array_key_exists( 'TLX', $instrec ) )
					$sub[ getTag( ':entity:tlx' ) ]
						= array( array( kTAG_TEXT => $instrec[ 'TLX' ] ) );
		
				//
				// Set elevation.
				//
				if( array_key_exists( 'ALT', $instrec ) )
					$sub[ getTag( ':location:site:elevation' ) ]
						= $instrec[ 'ALT' ];
		
				//
				// Set latitude.
				//
				if( array_key_exists( 'LAT', $instrec ) )
					$sub[ getTag( ':location:site:latitude:provided' ) ]
						= $instrec[ 'LAT' ];
		
				//
				// Set longitude.
				//
				if( array_key_exists( 'LONG_', $instrec ) )
					$sub[ getTag( ':location:site:longitude:provided' ) ]
						= $instrec[ 'LONG_' ];
		
				//
				// Set version.
				//
				if( array_key_exists( 'UPDATED', $instrec ) )
					$sub[ getTag( ':unit:version' ) ]
						= $instrec[ 'UPDATED' ];
			
			} // Not an institute or not known institite.
		
			//
			// Set accession number.
			//
			$sub[ getTag( 'mcpd:ACCENUMB' ) ]
				= $data[ 'AccessionNumber' ];
			
			//
			// Set notes.
			//
			$tmp = Array();
			if( array_key_exists( 'NOTES', $data ) )
				$tmp[] = $data[ 'NOTES' ];
			if( array_key_exists( 'OldAccessionNumber', $data ) )
				$tmp[] = $data[ 'OldAccessionNumber' ];
			if( count( $tmp ) )
				$sub[ getTag( ':notes' ) ]
					= $tmp;
			
			//
			// Set element.
			//
			if( count( $sub ) )
				$theContainer[] = $sub;
		
		} // Iterating records.

	} // loadNeighbourhood.
	

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
		$region = new OntologyWrapper\Term( $wrapper, $theIdentifier, TRUE );
		
		return OntologyWrapper\OntologyObject::SelectLanguageString(
				$region[ getTag( ':label' ) ], 'en' );										// ==>

	} // getRegion.
	

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
				$country[ getTag( ':label' ) ], 'en' );										// ==>

	} // getCountry.
	

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
