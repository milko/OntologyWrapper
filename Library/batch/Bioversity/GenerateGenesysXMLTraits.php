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
 *								GenerateGenesysXMLTraits.php							*
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
$theNamespace = ':trait:term';
$path1 = "/Library/WebServer/Library/OntologyWrapper/Library/standards/trait";
$path2 = "/Library/WebServer/Library/OntologyWrapper/Library/standards/collections";

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
	// Generate terms.
	//
	echo( "==> Generating terms.\n" );
	generateTerms( $dc, $theNamespace, "$path1/Terms.xml" );

	//
	// Generate types.
	//
	echo( "==> Generating types.\n" );
	generateTypes( $dc, $theNamespace, "$path1/Types.xml" );

	//
	// Generate tags.
	//
	echo( "==> Generating tags.\n" );
	generateTags( $dc, $theNamespace, "$path1/Tags.xml" );

	//
	// Generate forms.
	//
	echo( "==> Generating forms.\n" );
	generateForms( $dc, $theNamespace, "$path2/FormTrait.xml" );

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
	if( $dc instanceof ADOConnection )
		$dc->Close();

} // FINALLY BLOCK.

/*=======================================================================================
 *																						*
 *										FUNCTIONS										*
 *																						*
 *======================================================================================*/

	/**
	 * Generate terms.
	 *
	 * This function will generate the terms XML file.
	 *
	 * @param ADOConnection			$theConnection		Database connection.
	 * @param string				$theNamespace		Terms namespace.
	 * @param string				$theFile			XML file path.
	 *
	 * @return array				The parsed options.
	 */
	function generateTerms( ADOConnection $theConnection, $theNamespace, $theFile )
	{
		//
		// Init local storage.
		//
		$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!--
	TRAIT TERMS
	Terms.xml
-->
<METADATA
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:noNamespaceSchemaLocation="https://gist.githubusercontent.com/milko/f2dcea3c3a94f69bb0bb/raw/715d8fd15a66ad25466c9efc4f767fd31fc9a21a/Dictionary.xsd">
	
	<!-- Terms -->

EOT;
		
		//
		// Open metadata block.
		//
		$xml .= ("\n\t<META>");

		//
		// Iterate terms.
		//
		$query = "SELECT * FROM `terms` ORDER BY `LID` ASC";
		$rs = $theConnection->execute( $query );
		foreach( $rs as $record )
		{
			//
			// Load elements.
			//
			$id = $record[ 'LID' ];
			$label = $record[ 'Label' ];
		
			//
			// Write type term.
			//
			$xml .= ("\n\t\t<!-- $theNamespace:$id -->\n");
			$xml .= ("\t\t<TERM ns=\"$theNamespace\" lid=\"$id\">\n");
			$xml .= ("\t\t\t<item const=\"kTAG_LABEL\">\n");
			$xml .= ("\t\t\t\t<item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[$label]]></item>\n");
			$xml .= ("\t\t\t\t</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t</TERM>\n");
		
		} $rs->Close();
		
		//
		// Close metadata block.
		//
		$xml .= ("\t</META>\n");
		
		//
		// Close metadata.
		//
		$xml .= ("</METADATA>\n");
	
		//
		// Write XML file.
		//
		file_put_contents( $theFile, $xml );

	} // generateTerms.
	

	/**
	 * Generate types.
	 *
	 * This function will generate the types XML file.
	 *
	 * @param ADOConnection			$theConnection		Database connection.
	 * @param string				$theNamespace		Terms namespace.
	 * @param string				$theFile			XML file path.
	 *
	 * @return array				The parsed options.
	 */
	function generateTypes( ADOConnection $theConnection, $theNamespace, $theFile )
	{
		//
		// Init local storage.
		//
		$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!--
	TRAIT ENUMERATED TYPES
	Types.xml
-->
<METADATA
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:noNamespaceSchemaLocation="https://gist.githubusercontent.com/milko/f2dcea3c3a94f69bb0bb/raw/715d8fd15a66ad25466c9efc4f767fd31fc9a21a/Dictionary.xsd">
	
	<!-- Types -->

EOT;
		
		//
		// Iterate types.
		//
		$query = "SELECT * "
				."FROM `scales` "
				."WHERE `IsEnum` = 1 "
				."ORDER BY `ID` ASC";
		$rs = $theConnection->execute( $query );
		foreach( $rs as $record )
		{
			//
			// Load type elements.
			//
			$scale_id = $record[ 'ID' ];
			$type_id = $record[ 'Term' ];
			$synonyms = explode( ',', $record[ 'Synonyms' ] );
		
			//
			// Open type block.
			//
			$xml .= ("\n\t<!-- $theNamespace:$type_id -->\n");
			$xml .= ("\t<META>\n");
		
			//
			// Write type node.
			//
			$xml .= ("\t\t<NODE term=\"$theNamespace:$type_id\">\n");
			$xml .= ("\t\t\t<item const=\"kTAG_NODE_TYPE\">\n");
			$xml .= ("\t\t\t\t<item>:type:node:type</item>\n");
			$xml .= ("\t\t\t\t<item>:kind:enumerated-node</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_SYNONYM\">\n");
			foreach( $synonyms as $item )
				$xml .= ("\t\t\t\t<item><![CDATA[$item]]></item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t</NODE>\n");

			//
			// Close type block.
			//
			$xml .= ("\t</META>\n");
		
			//
			// Iterate enumerations.
			//
			$query = "SELECT * FROM `enums` WHERE `Scale` = $scale_id ORDER BY `Key`";
			$re = $theConnection->execute( $query );
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
					$xml .= ("\t<!-- $theNamespace:$type_id:\-\- -->\n");
				else
					$xml .= ("\t<!-- $theNamespace:$type_id:$enum_id -->\n");
				$xml .= ("\t<META>\n");
		
				//
				// Write enumeration term.
				//
				$xml .= ("\t\t<TERM ns=\"$theNamespace:$type_id\" lid=\"$enum_id\">\n");
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
				$xml .= ("\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">$theNamespace:$type_id</item>\n");
				$xml .= ("\t\t</EDGE>\n");
		
				//
				// Close enumeration block.
				//
				$xml .= ("\t</META>\n");
			
			} $re->Close();
		
		} $rs->Close();
		
		//
		// Close metadata.
		//
		$xml .= ("</METADATA>\n");
	
		//
		// Write XML file.
		//
		file_put_contents( $theFile, $xml );

	} // generateTypes.
	
	
	/**
	 * Generate tags.
	 *
	 * This function will generate the tags XML file.
	 *
	 * @param ADOConnection			$theConnection		Database connection.
	 * @param string				$theNamespace		Terms namespace.
	 * @param string				$theFile			XML file path.
	 *
	 * @return array				The parsed options.
	 */
	function generateTags( ADOConnection $theConnection, $theNamespace, $theFile )
	{
		//
		// Init local storage.
		//
		$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!--
	TRAIT TAGS
	Tags.xml
-->
<METADATA
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:noNamespaceSchemaLocation="https://gist.githubusercontent.com/milko/f2dcea3c3a94f69bb0bb/raw/715d8fd15a66ad25466c9efc4f767fd31fc9a21a/Dictionary.xsd">
	
	<!-- Tags -->

EOT;
		
		//
		// Iterate tags.
		//
		$query = "SELECT `tags`.*, `Features`.`Label` AS `FeatureLabel`, `Scales`.`Label` AS `ScaleLabel` "
				."FROM `tags` "
				."LEFT JOIN `terms` `Features` "
				."ON( `Features`.`ID` = `tags`.`FeatureTerm` ) "
				."LEFT JOIN `terms` `Scales` "
				."ON( `Scales`.`ID` = `tags`.`ScaleTerm` ) "
				."ORDER BY `tags`.`ID` ASC";
		$rs = $theConnection->execute( $query );
		foreach( $rs as $record )
		{
			//
			// Load tag elements.
			//
			$feature = $record[ 'FeatureTerm' ];
			$feature_lab = $record[ 'FeatureLabel' ];
			$scale = $record[ 'ScaleTerm' ];
			$scale_lab = $record[ 'ScaleLabel' ];
			$type = $record[ 'Type' ];
			$unit = $record[ 'Unit' ];
			$synonyms = ( $record[ 'Synonyms' ] !== NULL )
					  ? explode( ',', $record[ 'Synonyms' ] )
					  : NULL;
		
			//
			// Open tag block.
			//
			$xml .= ("\n\t<!-- $theNamespace:$feature/:predicate:SCALE-OF/$theNamespace:$scale -->\n");
			$xml .= ("\t<META>\n");
		
			//
			// Write tag.
			//
			$xml .= ("\t\t<TAG>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_TERMS\">\n");
			$xml .= ("\t\t\t\t<item>$theNamespace:$feature</item>\n");
			$xml .= ("\t\t\t\t<item>:predicate:SCALE-OF</item>\n");
			$xml .= ("\t\t\t\t<item>$theNamespace:$scale</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_DATA_TYPE\">$type</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_DATA_KIND\">\n");
			switch( $type )
			{
				case ':type:enum':
					$xml .= ("\t\t\t\t<item>:type:categorical</item>\n");
					$xml .= ("\t\t\t\t<item>:type:full-text-03</item>\n");
					$xml .= ("\t\t\t\t<item>:type:summary</item>\n");
					break;
				
				case ':type:int':
				case ':type:float':
					$xml .= ("\t\t\t\t<item>:type:quantitative</item>\n");
					break;
				
				case ':type:string':
					$xml .= ("\t\t\t\t<item>:type:discrete</item>\n");
					$xml .= ("\t\t\t\t<item>:type:full-text-06</item>\n");
					break;
			}
			$xml .= ("\t\t\t</item>\n");
			if( $cats = getTagCategories( $theConnection, $record[ 'ID' ] ) )
			{
				$xml .= ("\t\t\t<item const=\"kTAG_CATEGORY\">\n");
				foreach( $cats as $cat )
					$xml .= ("\t\t\t\t<item>$cat</item>\n");
				$xml .= ("\t\t\t</item>\n");
			}
			$xml .= ("\t\t\t<item const=\"kTAG_LABEL\">\n");
			$xml .= ("\t\t\t\t<item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[$feature_lab]]></item>\n");
			$xml .= ("\t\t\t\t</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_DESCRIPTION\">\n");
			$xml .= ("\t\t\t\t<item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[$scale_lab]]></item>\n");
			$xml .= ("\t\t\t\t</item>\n");
			$xml .= ("\t\t\t</item>\n");
			if( $unit !== NULL )
				$xml .= ("\t\t\t<item tag=\"UO:0000000\">$unit</item>\n");
			if( is_array( $synonyms ) )
			{
				$xml .= ("\t\t\t<item const=\"kTAG_SYNONYM\">\n");
				foreach( $synonyms as $item )
					$xml .= ("\t\t\t\t<item><![CDATA[$item]]></item>\n");
				$xml .= ("\t\t\t</item>\n");
			}
			$xml .= ("\t\t</TAG>\n");
		
			//
			// Write node.
			//
			$xml .= ("\t\t<NODE>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_NODE_TYPE\">\n");
			$xml .= ("\t\t\t\t<item>:kind:property-node</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t</NODE>\n");
			
			//
			// Handle typed.
			//
			if( $type == ':type:enum' )
			{
				$xml .= ("\t\t<EDGE>\n");
				$xml .= ("\t\t\t<item const=\"kTAG_SUBJECT\" node=\"term\">$theNamespace:$scale</item>\n");
				$xml .= ("\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:TYPE-OF</item>\n");
				$xml .= ("\t\t</EDGE>\n");
			}

			//
			// Close type block.
			//
			$xml .= ("\t</META>\n");
		
		} $rs->Close();
		
		//
		// Close metadata.
		//
		$xml .= ("</METADATA>\n");
	
		//
		// Write XML file.
		//
		file_put_contents( $theFile, $xml );

	} // generateTags.
	
	
	/**
	 * Generate forms.
	 *
	 * This function will generate the forms XML file.
	 *
	 * @param ADOConnection			$theConnection		Database connection.
	 * @param string				$theNamespace		Terms namespace.
	 * @param string				$theFile			XML file path.
	 *
	 * @return array				The parsed options.
	 */
	function generateForms( ADOConnection $theConnection, $theNamespace, $theFile )
	{
		//
		// Init local storage.
		//
		$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!--
	TRAIT FORM
	FormTrait.xml
-->
<METADATA
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:noNamespaceSchemaLocation="https://gist.githubusercontent.com/milko/f2dcea3c3a94f69bb0bb/raw/715d8fd15a66ad25466c9efc4f767fd31fc9a21a/Dictionary.xsd">
	
	<!-- FORM -->

	<!-- form::trait -->
	<META>
		<NODE term=":trait" pid="form::trait">
			<item const="kTAG_NODE_TYPE">
				<item>:kind:root-node</item>
				<item>:type:node:form</item>
			</item>
			<item const="kTAG_DESCRIPTION">
				<item>
					<item const="kTAG_LANGUAGE">en</item>
					<item const="kTAG_TEXT"><![CDATA[This form contains all common trait search fields.]]></item>
				</item>
			</item>
		</NODE>
	</META>

EOT;
		
		//
		// Iterate categories.
		//
		$query = "SELECT `ID`, `Term` FROM `categories`";
		$cats = $theConnection->GetAll( $query );
		foreach( $cats as $cat )
		{
			//
			// Init local storage.
			//
			$cat_id = $cat[ 'ID' ];
			$cat_term = $cat[ 'Term' ];
			
			//
			// Open category block.
			//
			$xml .= ("\n\t<!-- form:$cat_term -->\n");
			$xml .= ("\t<META>\n");
			$xml .= ("\t\t<NODE term=\"$cat_term\" pid=\"form:$cat_term\" />\n");
			$xml .= ("\t\t<EDGE>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:PROPERTY-OF</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_OBJECT\" node=\"pid\">form::trait</item>\n");
			$xml .= ("\t\t</EDGE>\n");
			
			//
			// Iterate category tags.
			//
			$query = "SELECT DISTINCT `tags`.`FeatureTerm`, `tags`.`ScaleTerm` "
					."FROM `groups` "
					."LEFT JOIN `tags` "
					."ON( `tags`.`ID` = `groups`.`Tag` ) "
					."WHERE `groups`.`Category` = $cat_id "
					."ORDER BY `tags`.`ID` ASC";
			$rs = $theConnection->execute( $query );
			foreach( $rs as $record )
			{
				//
				// Load tag elements.
				//
				$feature = $record[ 'FeatureTerm' ];
				$scale = $record[ 'ScaleTerm' ];
		
				//
				// Write tag block.
				//
				$xml .= ("\n\t<!-- $theNamespace:$feature/:predicate:SCALE-OF/$theNamespace:$scale -->\n");
				$xml .= ("\t\t<EDGE>\n");
				$xml .= ("\t\t\t<item const=\"kTAG_SUBJECT\" node=\"tag\">$theNamespace:$feature/:predicate:SCALE-OF/$theNamespace:$scale</item>\n");
				$xml .= ("\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:PROPERTY-OF</item>\n");
				$xml .= ("\t\t</EDGE>\n");
		
			} $rs->Close();
			
			//
			// Close category block.
			//
			$xml .= ("\t</META>\n");
		
		} // Iterating categories.
		
		//
		// Close metadata.
		//
		$xml .= ("</METADATA>\n");
	
		//
		// Write XML file.
		//
		file_put_contents( $theFile, $xml );

	} // generateForms.
	

	/**
	 * Get tag categories.
	 *
	 * This function will parse the provided options and return a list of options structured
	 * as an array with key as key and value as label.
	 *
	 * @param ADOConnection			$theConnection		Database connection.
	 * @param int					$theTag				Tag local identifier.
	 *
	 * @return array				The tag's category terms list.
	 */
	function getTagCategories( $theConnection, $theTag )
	{
		//
		// Select categories.
		//
		$query = "SELECT DISTINCT `categories`.`Term` "
				."FROM `groups` "
				."LEFT JOIN `categories` "
				."ON( `categories`.`ID` = `groups`.`Category` ) "
				."WHERE `groups`.`Tag` = $theTag";
		return $theConnection->GetCol( $query );									// ==>

	} // getTagCategories.
	

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
