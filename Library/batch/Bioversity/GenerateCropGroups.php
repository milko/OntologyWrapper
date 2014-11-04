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
	$query = "SELECT DISTINCT `Group`, `CodeGroup` "
			."FROM `crop_enum` "
			."ORDER BY `Group` ASC";
	$rs = $dc_in->execute( $query );
	$fp->fwrite( "\n" );
	foreach( $rs as $group )
	{
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
			."`CodeCategory`, `CodeGroup`, "
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
		$kid = $crop[ 'CodeCategory' ];
		
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
		$xml .= "\t\t\t<item tag=\":taxon:crop:category\">:taxon:crop:category:$kid</item>\n";
		$xml .= "\t\t\t<item tag=\":taxon:crop:group\">:taxon:crop:group:$gid</item>\n";
		
		//
		// Handle taxa.
		//
		if( count( $taxa ) )
		{
			//
			// Open section.
			//
			$xml .= "\t\t\t<item tag=\":taxon:group:taxa\">\n";
			
			foreach( $taxa as $taxon )
			{
				//
				// Open element
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
		$xml .= "\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">:taxon:crop:category:$kid</item>\n";
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
		
		return (string) $wrapper->getSerial( $theIdentifier, TRUE );				// ==>

	} // getTag.

?>
