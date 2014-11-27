<?php

/**
 * Generate Genesys enumerations.
 *
 * This file contains routines to generate the Genesys enumeration SQL records.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Utilities
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 26/11/2014
 */

/*=======================================================================================
 *																						*
 *								GenerateGenesysXMLEnums.php								*
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
// Init global storage.
//
$namespace = ':trait:scale';
$path = "/Library/WebServer/Library/OntologyWrapper/Library/standards/trait";

//
// Init XML.
//
$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!--
	TRAIT ENUMERATED TYPES
	Enumerations.xml
-->
<METADATA
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:noNamespaceSchemaLocation="https://gist.githubusercontent.com/milko/f2dcea3c3a94f69bb0bb/raw/715d8fd15a66ad25466c9efc4f767fd31fc9a21a/Dictionary.xsd">
	
	<!-- Enumerations -->

EOT;

//
// Inform.
//
echo( "\n==> Generating Genesys enumerations XML file.\n" );

//
// Parse arguments.
//
if( $argc < 2 )
	exit( "Usage: <script.php> "
	// MySQLi://user:pass@localhost/bioversity_genesys?socket=/tmp/mysql.sock&persist
				."<SQL database DSN>\n" );											// ==>

//
// Load arguments.
//
$db_in = $argv[ 1 ];

//
// Set tables.
//
$table_types = 'types';
$table_enums = 'scales';

/*=======================================================================================
 *	TRY																					*
 *======================================================================================*/

//
// Try.
//
try
{
	//
	// Init local storage.
	//
	$rs = $re = $dc = NULL;
	
	//
	// Connect to database.
	//
	echo( "  • Connecting to SQL\n" );
	echo( "    - $db_in\n" );
	$dc = NewADOConnection( $db_in );
	$dc->Execute( "SET CHARACTER SET 'utf8'" );
	$dc->SetFetchMode( ADODB_FETCH_ASSOC );

	//
	// Inform.
	//
	echo( "\n==> Exporting enumerations.\n" );
	
	//
	// Iterate types.
	//
	$query = "SELECT * FROM `$table_types` ORDER BY `ID` ASC";
	$rs = $dc->execute( $query );
	foreach( $rs as $record )
	{
		//
		// Load type elements.
		//
		$type_id = $record[ 'ID' ];
		$type_data = $record[ 'DataType' ];
		$type_label = $record[ 'Label' ];
		$type_synonyms = explode( ',', $record[ 'Symbol' ] );
		
		//
		// Open type block.
		//
		$xml .= ("\n\t<!-- $namespace:$type_id -->\n");
		$xml .= ("\t<META>\n");
		
		//
		// Write type term.
		//
		$xml .= ("\t\t<TERM ns=\"$namespace\" lid=\"$type_id\">\n");
		$xml .= ("\t\t\t<item const=\"kTAG_LABEL\">\n");
		$xml .= ("\t\t\t\t<item>\n");
		$xml .= ("\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n");
		$xml .= ("\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[$type_label]]></item>\n");
		$xml .= ("\t\t\t\t</item>\n");
		$xml .= ("\t\t\t</item>\n");
		if( count( $type_synonyms ) )
		{
			$xml .= ("\t\t\t<item const=\"kTAG_SYNONYM\">\n");
			foreach( $type_synonyms as $item )
				$xml .= ("\t\t\t\t<item><![CDATA[$item]]></item>\n");
			$xml .= ("\t\t\t</item>\n");
		}
		$xml .= ("\t\t</TERM>\n");
		
		//
		// Write tag.
		//
		$xml .= ("\t\t<TAG>\n");
		$xml .= ("\t\t\t<item const=\"kTAG_TERMS\">\n");
		$xml .= ("\t\t\t\t<item>$namespace:$type_id</item>\n");
		$xml .= ("\t\t\t</item>\n");
		$xml .= ("\t\t\t<item const=\"kTAG_DATA_TYPE\">$type_data</item>\n");
		$xml .= ("\t\t\t<item const=\"kTAG_DATA_KIND\">\n");
		$xml .= ("\t\t\t\t<item>type:categorical</item>\n");
		$xml .= ("\t\t\t</item>\n");
		$xml .= ("\t\t</TAG>\n");
		
		//
		// Write type node.
		//
		$xml .= ("\t\t<NODE term=\"$namespace:$type_id\">\n");
		$xml .= ("\t\t\t<item const=\"kTAG_NODE_TYPE\">\n");
		$xml .= ("\t\t\t\t<item>:type:node:type</item>\n");
		$xml .= ("\t\t\t\t<item>:kind:enumerated-node</item>\n");
		$xml .= ("\t\t\t</item>\n");
		$xml .= ("\t\t</NODE>\n");
		
		//
		// Write tag node.
		//
		$xml .= ("\t\t<NODE tag=\"$namespace:$type_id\">\n");
		$xml .= ("\t\t\t<item const=\"kTAG_NODE_TYPE\">\n");
		$xml .= ("\t\t\t\t<item>:kind:property-node</item>\n");
		$xml .= ("\t\t\t</item>\n");
		$xml .= ("\t\t</NODE>\n");
		
		//
		// Write type relationship.
		//
		$xml .= ("\t\t<EDGE>\n");
		$xml .= ("\t\t\t<item const=\"kTAG_SUBJECT\" node=\"term\">$namespace:$type_id</item>\n");
		$xml .= ("\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:TYPE-OF</item>\n");
		$xml .= ("\t\t\t<item const=\"kTAG_OBJECT\" node=\"tag\">$namespace:$type_id</item>\n");
		$xml .= ("\t\t</EDGE>\n");

		//
		// Close type block.
		//
		$xml .= ("\t</META>\n");
		
		//
		// Iterate enumerations.
		//
		$query = "SELECT * FROM `$table_enums` WHERE `Type_Id` = $type_id ORDER BY `Key`";
		$re = $dc->execute( $query );
		foreach( $re as $record )
		{
			//
			// Load enumeration elements.
			//
			$enum_id = $record[ 'Key' ];
			$enum_label = $record[ 'Label' ];
		
			//
			// Open enumeration block.
			//
			if( $enum_id == '--' )
				$xml .= ("\t<!-- $namespace:$type_id:\-\- -->\n");
			else
				$xml .= ("\t<!-- $namespace:$type_id:$enum_id -->\n");
			$xml .= ("\t<META>\n");
		
			//
			// Write enumeration term.
			//
			$xml .= ("\t\t<TERM ns=\"$namespace:$type_id\" lid=\"$enum_id\">\n");
			$xml .= ("\t\t\t<item const=\"kTAG_LABEL\">\n");
			$xml .= ("\t\t\t\t<item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[$enum_label]]></item>\n");
			$xml .= ("\t\t\t\t</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t</TERM>\n");
		
			//
			// Write enumeration node.
			//
			$xml .= ("\t\t<NODE>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_NODE_TYPE\">\n");
			$xml .= ("\t\t\t\t<item>:type:node:enumeration</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t</NODE>\n");
		
			//
			// Write enumeration relationship.
			//
			$xml .= ("\t\t<EDGE>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:ENUM-OF</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">$namespace:$type_id</item>\n");
			$xml .= ("\t\t</EDGE>\n");
		
			//
			// Close enumeration block.
			//
			$xml .= ("\t</META>\n");
		}
	
	} // Scanning input table.
	
	//
	// Close root.
	//
	$xml .= ("</METADATA>\n");
	
	//
	// Write XML file.
	//
	file_put_contents( "$path/Enumerations.xml", $xml );

	echo( "\nDone!\n" );

} // TRY BLOCK.

/*=======================================================================================
 *	CATCH																				*
 *======================================================================================*/

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
	print_r( $error->getTrace() );

} // CATCH BLOCK.

/*=======================================================================================
 *	FINALLY																				*
 *======================================================================================*/

//
// FINAL BLOCK.
//
finally
{
	if( $rs instanceof ADORecordSet )
		$rs->Close();
	if( $re instanceof ADORecordSet )
		$re->Close();
	if( $dc instanceof ADOConnection )
		$dc->Close();

} // FINALLY BLOCK.

/*=======================================================================================
 *																						*
 *										FUNCTIONS										*
 *																						*
 *======================================================================================*/

	/**
	 * Parse options.
	 *
	 * This function will parse the provided options and return a list of options structured
	 * as an array with key as key and value as label.
	 *
	 * @param string				$theOptions			Options.
	 *
	 * @return array				The parsed options.
	 */
	function parseOptions( $theOptions )
	{
		//
		// Init local storage.
		//
		$options = Array();
		
		//
		// Parse blocks.
		//
		foreach( explode( ';', $theOptions ) as $block )
		{
			//
			// Parse key.
			//
			$pos = strpos( $block, ',' );
			if( $pos )
			{
				//
				// Get elements.
				//
				$key = trim( substr( $block, 0, $pos ) );
				$value = trim( substr( $block, $pos + 1 ) );
				
				//
				// Set enumeration.
				//
				$options[ $key ] = $value;
			
			} // Found divider.
			
			else
				throw new Exception( "Invalid option\n[$block]\n" );				// ==>
		
		} // Scanning blocks.
		
		return $options;															// ==>

	} // getTerms.


?>
