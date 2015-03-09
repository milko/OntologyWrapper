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
 *								GenerateAnnex1Groups.php								*
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
	$fp = new SplFileObject( '/Library/WebServer/Library/OntologyWrapper/Library/batch/snippets/Annex1Groups.xml', 'w' );
			
	//
	// Connect to input database.
	//
	echo( "  • Connecting to input SQL\n" );
	echo( "    - $db_in\n" );
	$dc_in = NewADOConnection( $db_in );
	$dc_in->Execute( "SET CHARACTER SET 'utf8'" );
	$dc_in->SetFetchMode( ADODB_FETCH_ASSOC );
	
	//
	// Generate annex-1 food.
	//
	echo( "  • Generating annex-1 food\n" );
	
	//
	// Set annex-1 food term.
	//
	$xml = "\t<!-- :taxon:annex-1:100 -->\n";
	$xml .= "\t<META>\n";
	$xml .= "\t\t<TERM ns=\":taxon:annex-1\" lid=\"100\">\n";
	$xml .= "\t\t\t<item const=\"kTAG_LABEL\">\n";
	$xml .= "\t\t\t\t<item>\n";
	$xml .= "\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n";
	$xml .= "\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[Food]]></item>\n";
	$xml .= "\t\t\t\t</item>\n";
	$xml .= "\t\t\t</item>\n";
	
	//
	// Get annex-1 food epithets.
	//
	$query = "SELECT DISTINCT `Genus`, `Section`, `Species` "
			."FROM `annex1_enum` "
			."WHERE( (`Code` = '100') AND (`Genus` IS NOT NULL) ) "
			."ORDER BY `Genus`, `Section`, `Species` ASC";
	$rs = $dc_in->execute( $query );
	
	//
	// Open section.
	//
	if( $rs->RecordCount() )
		$xml .= "\t\t\t<item tag=\":taxon:group:taxa\">\n";
	
	//
	// Iterate taxa.
	//
	foreach( $rs as $taxon )
	{
		//
		// Open element.
		//
		$xml .= "\t\t\t\t<item>\n";
		
		//
		// Determine species name.
		//
		$species = Array();
		if( strlen( $taxon[ 'Genus' ] ) )
			$species[] = $taxon[ 'Genus' ];
		if( strlen( $taxon[ 'Species' ] ) )
			$species[] = $taxon[ 'Species' ];
		$species = implode( ' ', $species );
		
		//
		// Add species name.
		//
		$xml .= "\t\t\t\t\t<item tag=\":taxon:species:name\">$species</item>\n";
		
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
					case 'Genus':
						$tag = ':taxon:genus';
						break;
					case 'Section':
						$tag = ':taxon:sectio';
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
	if( $rs->RecordCount() )
		$xml .= "\t\t\t</item>\n";
	
	//
	// Close recordset.
	//
	$rs->Close();
	$rs = NULL;
	
	//
	// Get annex-1 food excluded epithets.
	//
	$query = "SELECT DISTINCT `ExGenus`, `ExSpecies` "
			."FROM `annex1_enum` "
			."WHERE( (`Code` = '100') AND (`ExGenus` IS NOT NULL) ) "
			."ORDER BY `ExGenus`, `ExSpecies` ASC";
	$rs = $dc_in->execute( $query );
	
	//
	// Open section.
	//
	if( $rs->RecordCount() )
		$xml .= "\t\t\t<item tag=\":taxon:group:taxa:excluded\">\n";
	
	//
	// Iterate taxa.
	//
	foreach( $rs as $taxon )
	{
		//
		// Open element.
		//
		$xml .= "\t\t\t\t<item>\n";
		
		//
		// Determine species name.
		//
		$species = Array();
		if( strlen( $taxon[ 'ExGenus' ] ) )
			$species[] = $taxon[ 'ExGenus' ];
		if( strlen( $taxon[ 'ExSpecies' ] ) )
			$species[] = $taxon[ 'ExSpecies' ];
		$species = implode( ' ', $species );
		
		//
		// Add species name.
		//
		$xml .= "\t\t\t\t\t<item tag=\":taxon:species:name\">$species</item>\n";
		
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
					case 'ExGenus':
						$tag = ':taxon:genus';
						break;
					case 'ExSpecies':
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
	if( $rs->RecordCount() )
		$xml .= "\t\t\t</item>\n";
	
	//
	// Close recordset.
	//
	$rs->Close();
	$rs = NULL;
	
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
	$xml .= "\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">:taxon:annex-1</item>\n";
	$xml .= "\t\t</EDGE>\n";
	$xml .= "\t</META>\n";
	
	//
	// Write file.
	//
	$fp->fwrite( $xml );
	
	//
	// Generate annex-1 forage.
	//
	echo( "  • Generating annex-1 forage\n" );
	
	//
	// Set annex-1 forage term.
	//
	$xml = "\t<!-- :taxon:annex-1:200 -->\n";
	$xml .= "\t<META>\n";
	$xml .= "\t\t<TERM ns=\":taxon:annex-1\" lid=\"200\">\n";
	$xml .= "\t\t\t<item const=\"kTAG_LABEL\">\n";
	$xml .= "\t\t\t\t<item>\n";
	$xml .= "\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n";
	$xml .= "\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[Forage]]></item>\n";
	$xml .= "\t\t\t\t</item>\n";
	$xml .= "\t\t\t</item>\n";
	$xml .= "\t\t</TERM>\n";
	$xml .= "\t\t<NODE>\n";
	$xml .= "\t\t\t<item const=\"kTAG_NODE_TYPE\">\n";
	$xml .= "\t\t\t\t<item>:type:node:enumeration</item>\n";
	$xml .= "\t\t\t</item>\n";
	$xml .= "\t\t</NODE>\n";
	$xml .= "\t\t<EDGE>\n";
	$xml .= "\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:ENUM-OF</item>\n";
	$xml .= "\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">:taxon:annex-1</item>\n";
	$xml .= "\t\t</EDGE>\n";
	$xml .= "\t</META>\n";
	
	//
	// Write file.
	//
	$fp->fwrite( $xml );
	
	//
	// Get annex-1 forage groups.
	//
	$query = "SELECT DISTINCT `Code`, `Subgroup` "
			."FROM `annex1_enum` "
			."WHERE `Subgroup` IS NOT NULL "
			."ORDER BY `Code`, `Subgroup` ASC";
	$forages = $dc_in->GetAll( $query );
	foreach( $forages as $forage )
	{
		//
		// Get code and name.
		//
		$code = $forage[ 'Code' ];
		$name = $forage[ 'Subgroup' ];
		
		//
		// Set annex-1 forage term.
		//
		$xml = "\t<!-- :taxon:annex-1:$code -->\n";
		$xml .= "\t<META>\n";
		$xml .= "\t\t<TERM ns=\":taxon:annex-1\" lid=\"$code\">\n";
		$xml .= "\t\t\t<item const=\"kTAG_LABEL\">\n";
		$xml .= "\t\t\t\t<item>\n";
		$xml .= "\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n";
		$xml .= "\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[$name]]></item>\n";
		$xml .= "\t\t\t\t</item>\n";
		$xml .= "\t\t\t</item>\n";
		
		//
		// Get annex-1 forage epithets.
		//
		$query = "SELECT DISTINCT `Genus`, `Section`, `Species` "
				."FROM `annex1_enum` "
				."WHERE( (`Code` = '$code') AND (`Genus` IS NOT NULL) ) "
				."ORDER BY `Genus`, `Section`, `Species` ASC";
		$rs = $dc_in->execute( $query );
	
		//
		// Open section.
		//
		if( $rs->RecordCount() )
			$xml .= "\t\t\t<item tag=\":taxon:group:taxa\">\n";
	
		//
		// Iterate taxa.
		//
		foreach( $rs as $taxon )
		{
			//
			// Open element.
			//
			$xml .= "\t\t\t\t<item>\n";
		
			//
			// Determine species name.
			//
			$species = Array();
			if( strlen( $taxon[ 'Genus' ] ) )
				$species[] = $taxon[ 'Genus' ];
			if( strlen( $taxon[ 'Species' ] ) )
				$species[] = $taxon[ 'Species' ];
			$species = implode( ' ', $species );
		
			//
			// Add species name.
			//
			$xml .= "\t\t\t\t\t<item tag=\":taxon:species:name\">$species</item>\n";
		
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
						case 'Genus':
							$tag = ':taxon:genus';
							break;
						case 'Section':
							$tag = ':taxon:sectio';
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
		if( $rs->RecordCount() )
			$xml .= "\t\t\t</item>\n";
	
		//
		// Close recordset.
		//
		$rs->Close();
		$rs = NULL;
		
		//
		// Get annex-1 food excluded epithets.
		//
		$query = "SELECT DISTINCT `ExGenus`, `ExSpecies` "
				."FROM `annex1_enum` "
				."WHERE( (`Code` = '$code') AND (`ExGenus` IS NOT NULL) ) "
				."ORDER BY `ExGenus`, `ExSpecies` ASC";
		$rs = $dc_in->execute( $query );
	
		//
		// Open section.
		//
		if( $rs->RecordCount() )
			$xml .= "\t\t\t<item tag=\":taxon:group:taxa:excluded\">\n";
	
		//
		// Iterate taxa.
		//
		foreach( $rs as $taxon )
		{
			//
			// Open element.
			//
			$xml .= "\t\t\t\t<item>\n";
		
			//
			// Determine species name.
			//
			$species = Array();
			if( strlen( $taxon[ 'ExGenus' ] ) )
				$species[] = $taxon[ 'ExGenus' ];
			if( strlen( $taxon[ 'ExSpecies' ] ) )
				$species[] = $taxon[ 'ExSpecies' ];
			$species = implode( ' ', $species );
		
			//
			// Add species name.
			//
			$xml .= "\t\t\t\t\t<item tag=\":taxon:species:name\">$species</item>\n";
		
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
						case 'ExGenus':
							$tag = ':taxon:genus';
							break;
						case 'ExSpecies':
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
		if( $rs->RecordCount() )
			$xml .= "\t\t\t</item>\n";
	
		//
		// Close recordset.
		//
		$rs->Close();
		$rs = NULL;
	
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
		$xml .= "\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">:taxon:annex-1:200</item>\n";
		$xml .= "\t\t</EDGE>\n";
		$xml .= "\t</META>\n";
	
		//
		// Write file.
		//
		$fp->fwrite( $xml );
	}

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
