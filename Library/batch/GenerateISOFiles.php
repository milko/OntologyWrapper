<?php

/**
 * Generate ISO XML files.
 *
 * This file contains the routine to build the ISO standards XML files.
 *
 *	@package	MyWrapper
 *	@subpackage	Data
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 17/09/2013
 *				2.00 11/03/2014
 */

/*=======================================================================================
 *																						*
 *									GenerateISOFiles.php								*
 *																						*
 *======================================================================================*/

/**
 * Global includes.
 *
 * This file contains the global definitions.
 */
require_once( 'includes.inc.php' );

/**
 * Local includes.
 *
 * This file contains the local definitions.
 */
require_once( 'local.inc.php' );

/**
 * Tags.
 *
 * This file contains the tag definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

/**
 * Types.
 *
 * This file contains the type definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Types.inc.php" );

/**
 * Predicates.
 *
 * This file contains the predicate definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Predicates.inc.php" );

/**
 * Tokens.
 *
 * This file contains the token definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Tokens.inc.php" );

/**
 * ISO definitions.
 *
 * This file contains the default ISO definitions.
 */
require_once( kPATH_STANDARDS_ROOT."/iso/iso.inc.php" );

/**
 * Functions.
 *
 * This file contains common function definitions.
 */
require_once( kPATH_LIBRARY_ROOT."/Functions.php" );


/*=======================================================================================
 *	RUN-TIME DEFINITIONS																*
 *======================================================================================*/
 
//
// Verbose flag.
//
define( "kOPTION_VERBOSE", TRUE );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Start session.
//
session_start();
 
//
// Inform.
//
if( kOPTION_VERBOSE )
	echo( "\n==> ISO standards XML files generation.\n" );

//
// Init local storage.
//
$start = time();

//
// TRY BLOCK.
//
try
{
	//
	// Decode ISO PO files standards.
	//
	if( kOPTION_VERBOSE )
		echo( "  • Decode ISO PO files.\n" );
	DecodeISOPOFiles();
	
	//
	// Handle ISO standards.
	//
	if( kOPTION_VERBOSE )
		echo( "  • Generating ISO standards.\n" );
	GenerateXMLISOFiles( kPATH_STANDARDS_ROOT );
	
	//
	// Handle WBI standards.
	//
	if( kOPTION_VERBOSE )
		echo( "  • Generating WBI standards.\n" );
	GenerateXMLWBIFiles( kPATH_STANDARDS_ROOT );
	
	//
	// Stopwatch.
	//
	if( kOPTION_VERBOSE )
		echo( "\nTime elapsed: ".(time() - $start)."\n" );
}

//
// CATCH BLOCK.
//
catch( Exception $error )
{
	echo( (string) $error );
}

if( kOPTION_VERBOSE )
	echo( "\nDone!\n" );

		

/*=======================================================================================
 *																						*
 *									PO FILE FUNCTIONS									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DecodeISOPOFiles																*
	 *==================================================================================*/

	/**
	 * Decode ISO PO files
	 *
	 * This method will parse all MO files, decode them into PO files and write to the
	 * {@link kISO_FILE_PO_DIR} directory the PHP serialised decode array.
	 *
	 * @throws Exception
	 */
	function DecodeISOPOFiles()
	{
		//
		// Init local storage.
		//
		$_SESSION[ kISO_LANGUAGES ] = Array();
		
		//
		// Init files list.
		// The order is important!!!
		//
		$_SESSION[ kISO_FILES ]
			= array( kISO_FILE_639_3, kISO_FILE_639, kISO_FILE_639_5,
					 kISO_FILE_3166, kISO_FILE_3166_2,
					 kISO_FILE_4217,
					 kISO_FILE_15924 );
		
		//
		// Point to MO files.
		//
		$_SESSION[ kISO_FILE_MO_DIR ] = kISO_CODES_PATH.kISO_CODES_PATH_LOCALE;
		$moditer = new DirectoryIterator( $_SESSION[ kISO_FILE_MO_DIR ] );

		//
		// Create temporary directory.
		//
		if( kOPTION_VERBOSE )
			echo( "    - Decoding PO files\n" );
		$_SESSION[ kISO_FILE_PO_DIR ] = tempnam( sys_get_temp_dir(), '' );
		if( file_exists( $_SESSION[ kISO_FILE_PO_DIR ] ) )
			unlink( $_SESSION[ kISO_FILE_PO_DIR ] );
		mkdir( $_SESSION[ kISO_FILE_PO_DIR ] );
		if( ! is_dir( $_SESSION[ kISO_FILE_PO_DIR ] ) )
			throw new Exception
				( "Unable to create temporary PO directory",
				  kERROR_STATE );												// !@! ==>
		$_SESSION[ kISO_FILE_PO_DIR ]
			= realpath( $_SESSION[ kISO_FILE_PO_DIR ] );
		
		//
		// Iterate languages.
		//
		foreach( $moditer as $language )
		{
			//
			// Handle valid directories.
			//
			if( $language->isDir()
			 && (! $language->isDot()) )
			{
				//
				// Save language code.
				//
				$code = $language->getBasename();
				$_SESSION[ kISO_LANGUAGES ][] = $code;
				if( kOPTION_VERBOSE )
					echo( "      $code\n" );
				
				//
				// Create language directory.
				//
				$dir = $_SESSION[ kISO_FILE_PO_DIR ]."/$code";
				DeleteFileDir( $dir );
				mkdir( $dir );
				if( ! is_dir( $dir ) )
					throw new Exception
						( "Unable to create temporary language directory",
						  kERROR_STATE );										// !@! ==>
				
				//
				// Iterate files.
				//
				$mofiter = new DirectoryIterator
					( $language->getRealPath().kISO_CODES_PATH_MSG );
				foreach( $mofiter as $file )
				{
					//
					// Skip invisible files.
					//
					$name = $file->getBasename( '.mo' );
					if( ! $file->isDot()
					 && in_array( $name, $_SESSION[ kISO_FILES ] ) )
					{
						//
						// Create filenames.
						//
						$filename_po = realpath( $dir )."/$name.po";
						$filename_key = realpath( $dir )."/$name.serial";
						
						//
						// Convert to PO.
						//
						$source = $file->getRealPath();
						exec( "msgunfmt -o \"$filename_po\" \"$source\"" );
						
						//
						// Convert to key.
						//
						file_put_contents(
							$filename_key,
							serialize( PO2Array( $filename_po ) ) );
					
					} // Workable file.
				
				} // Iterating files.
			
			} // Valid directory.
		
		} // Iterating languages.

	} // DecodeISOPOFiles.

		

/*=======================================================================================
 *																						*
 *							ISO XML FILE GENERATION FUNCTIONS							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GenerateXMLISOFiles																*
	 *==================================================================================*/

	/**
	 * Generate ISO XML files
	 *
	 * This method will generate the ISO XML files and store them into the provided base
	 * directory.
	 *
	 * @param string				$theDirectory		Files container directory.
	 */
	function GenerateXMLISOFiles( $theDirectory )
	{
		//
		// Inform.
		//
		if( kOPTION_VERBOSE )
			echo( "    - Generating ISO XML files\n" );
		
		//
		// Drop generated XML files.
		//
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-1.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-2.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-2B.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-2T.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-3.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-5.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-xref.xml' );
		
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-1-alpha2.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-1-alpha3.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-1-numeric.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-3-alpha3.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-3-alpha4.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-3-numeric.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-2-subset.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-2.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-xref.xml' );
		
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-A-alpha.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-A-numeric.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-H-alpha.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-H-numeric.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-xref.xml' );
		
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso15924-alpha4.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso15924-numeric.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_ISO.'/iso15924-xref.xml' );
		
		//
		// Drop default generated XML files.
		//
		@unlink( $theDirectory."/".kDIR_STANDARDS_DEFAULT.'/iso-locations-country.xml' );
		@unlink( $theDirectory."/".kDIR_STANDARDS_DEFAULT.'/iso-locations-subset.xml' );
		
		//
		// Generate ISO parts 1, 2 and 3 standards.
		//
		if( kOPTION_VERBOSE )
			echo( "      ".kISO_FILE_639_3."\n" );
		ISOGenerate6393XML( $theDirectory );
		
		//
		// Generate ISO 639 standards.
		//
		if( kOPTION_VERBOSE )
			echo( "      ".kISO_FILE_639."\n" );
		ISOGenerate639XML( $theDirectory );
		
		//
		// Generate ISO part 5 standard.
		//
		if( kOPTION_VERBOSE )
			echo( "      ".kISO_FILE_639_5."\n" );
		ISOGenerate6395XML( $theDirectory );
		
		//
		// Generate ISO 3166 standards.
		//
		if( kOPTION_VERBOSE )
			echo( "      ".kISO_FILE_3166."\n" );
		ISOGenerate31661XML( $theDirectory );
		ISOGenerate31663XML( $theDirectory );
		
		//
		// Generate ISO 3166-2 standards.
		//
		if( kOPTION_VERBOSE )
			echo( "      ".kISO_FILE_3166_2."\n" );
		ISOGenerate31662XML( $theDirectory );
		
		//
		// Generate ISO 4217 standards.
		//
		if( kOPTION_VERBOSE )
			echo( "      ".kISO_FILE_4217."\n" );
		ISOGenerate4217XML( $theDirectory );
		
		//
		// Generate ISO 15924 standards.
		//
		if( kOPTION_VERBOSE )
			echo( "      ".kISO_FILE_15924."\n" );
		ISOGenerate15924XML( $theDirectory );
		
		//
		// Complete ISO 3166 relationships.
		//
		ISOGenerate31663XMLRelations( $theDirectory );
		
	} // GenerateXMLISOFiles.

	 
	/*===================================================================================
	 *	GenerateXMLWBIFiles																*
	 *==================================================================================*/

	/**
	 * Generate WBI XML files
	 *
	 * This method will generate the WBI XML files and store them into the provided base
	 * directory.
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function GenerateXMLWBIFiles( $theDirectory )
	{
		//
		// Inform.
		//
		if( kOPTION_VERBOSE )
			echo( "    - Generating WBI XML files\n" );
		
		//
		// Drop generated XML files.
		//
		@unlink( $theDirectory."/".kDIR_STANDARDS_WBI.'/wbi-xref.xml' );
		
		//
		// Generate WBI relationships.
		//
		if( kOPTION_VERBOSE )
			echo( "      WBI-RELATIONSHIPS.xml\n" );
		WBIGenerateXMLRelations( $theDirectory );
		
	} // GenerateXMLWBIFiles.

		

/*=======================================================================================
 *																						*
 *						PROTECTED ISO XML FILE GENERATION FUNCTIONS						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ISOGenerate6393XML																*
	 *==================================================================================*/

	/**
	 * Generate ISO 639 XML files
	 *
	 * This method will generate the XML ISO 639 part1, part 2 and part 3 files.
	 *
	 * The method will load the information from the iso-codes processed files and write
	 * the following files in the provided directory:
	 *
	 * <ul>
	 *	<li><tt>ISO639-1.xml</tt>: Part 1 codes.
	 *	<li><tt>ISO639-2.xml</tt>: Part 2 codes.
	 *	<li><tt>ISO639-3.xml</tt>: Part 3 codes.
	 *	<li><tt>ISO639-xref.xml</tt>: Part 1, 2 and 3 cross references.
	 * </ul>
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function ISOGenerate6393XML( $theDirectory )
	{
		//
		// Load XML file.
		//
		$file_in = kISO_CODES_PATH.kISO_CODES_PATH_XML.'/'.kISO_FILE_639_3.'.xml';
		$xml_in = simplexml_load_file( $file_in  );
		if( $xml_in instanceof SimpleXMLElement )
		{
			//
			// Inform.
			//
			if( kOPTION_VERBOSE )
				echo( "        • ISO 639 1, 2, 3\n" );
			
			//
			// Set default namespaces.
			//
			$ns_1 = 'iso:639:1';
			$ns_2 = 'iso:639:2';
			$ns_3 = 'iso:639:3';
			$ns_status = 'iso:639:status';
			$ns_scope = 'iso:639:scope';
			$ns_type = 'iso:639:type';
			$ns_inverted_name = 'iso:639:inverted_name';
			$ns_common_name = 'iso:639:common_name';
			
			//
			// Open XML structures.
			//
			$xml_1 = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_2 = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_3 = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_xref = OntologyWrapper\MetadataObject::XMLRootElement();
			
			//
			// Set target files name.
			//
			$file_1 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-1.xml';
			$file_2 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-2.xml';
			$file_3 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-3.xml';
			$file_xref = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-xref.xml';
			
			//
			// Iterate XML file.
			//
			foreach( $xml_in->{'iso_639_3_entry'} as $record )
			{
				//
				// Check identifier.
				//
				if( $record[ 'id' ] !== NULL )
				{
					//
					// Save identifier.
					//
					$id3 = (string) $record[ 'id' ];
					$gid3 = $ns_3.kTOKEN_NAMESPACE_SEPARATOR.$id3;
					
					//
					// Create unit.
					//
					$unit = $xml_3->addChild( kIO_XML_TRANS_META );
					
					//
					// Create term.
					//
					$term = $unit->addChild( kIO_XML_META_TERM );
					$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_3 );
					$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id3 );
					
					//
					// Set term instance.
					//
					$element = $term->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_TERM_TYPE' );
					$element->addChild( kIO_XML_DATA, kTYPE_TERM_INSTANCE );
					
					//
					// Set term synonyms.
					//
					$element_syn_3 = $term->addChild( kIO_XML_DATA );
					$element_syn_3->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
					$item = $element_syn_3->addChild( kIO_XML_DATA, $id3 );
					
					//
					// Init term names.
					//
					$names = Array();
					
					//
					// Set term reference name.
					//
					if( $record[ 'reference_name' ] !== NULL )
					{
						$tmp = (string) $record[ 'reference_name' ];
						$names[ kTAG_LABEL ][ 0 ] = $tmp;
					}
					
					//
					// Set term english name.
					//
					if( $record[ 'name' ] !== NULL )
					{
						$tmp = (string) $record[ 'name' ];
						$names[ kTAG_LABEL ][ 'en' ] = $tmp;
					}
					
					//
					// Collect language strings.
					//
					ISOCollectLanguageElements( $names, kISO_FILE_639_3 );
					
					//
					// Set language strings.
					//
					foreach( $names as $tag => $strings )
						AddLanguageStrings( $term, $tag, $strings );
					
					//
					// Set inverted name.
					//
					if( $record[ 'inverted_name' ] !== NULL )
					{
						$tmp = (string) $record[ 'inverted_name' ];
						$element = $term->addChild( kIO_XML_DATA, htmlspecialchars( $tmp ) );
						$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_inverted_name );
					
					} // Has inverted name.
					
					//
					// Set common name.
					//
					if( $record[ 'common_name' ] !== NULL )
					{
						$tmp = (string) $record[ 'common_name' ];
						$element = $term->addChild( kIO_XML_DATA, htmlspecialchars( $tmp ) );
						$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_common_name );
					
					} // Has common name.
					
					//
					// Set status.
					//
					if( $record[ 'status' ] !== NULL )
					{
						$tmp = (string) $record[ 'status' ];
						$element = $term->addChild( kIO_XML_DATA, htmlspecialchars( $tmp ) );
						$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_status );
					
					} // Has status.

					//
					// Set scope.
					//
					if( $record[ 'scope' ] !== NULL )
					{
						$tmp = trim( (string) $record[ 'scope' ] );
						if( $tmp == 'L' )
							$tmp = 'R';
						$tmp = $ns_scope.kTOKEN_NAMESPACE_SEPARATOR.$tmp;
						$element = $term->addChild( kIO_XML_DATA, $tmp );
						$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_scope );
					
					} // Has scope.

					//
					// Set type.
					//
					if( $record[ 'type' ] !== NULL )
					{
						$tmp = trim( (string) $record[ 'type' ] );
						if( $tmp == 'Genetic, Ancient' )
						{
							$element = $term->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_type );
							$tmp = $ns_type.kTOKEN_NAMESPACE_SEPARATOR.'A';
							$item = $element->addChild( kIO_XML_DATA, $tmp );
							$tmp = $ns_type.kTOKEN_NAMESPACE_SEPARATOR.'Genetic';
							$item = $element->addChild( kIO_XML_DATA, $tmp );
						}
						else
						{
							$element = $term->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_type );
							$tmp = $ns_type.kTOKEN_NAMESPACE_SEPARATOR.$tmp;
							$element->addChild( kIO_XML_DATA, $tmp );
						}
					
					} // Has type.
					
					//
					// Create node.
					//
					$node = $unit->addChild( kIO_XML_META_NODE );
					
					//
					// Set node type.
					//
					$element = $node->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Relate to parent.
					//
					$edge = $unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_3 );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Reset cross reference data.
					//
					$id1 = $id2 = $unit_xref = NULL;
					
					//
					// Handle part 1.
					//
					if( $record[ 'part1_code' ] !== NULL )
					{
						//
						// Save local identifier.
						//
						$id1 = (string) $record[ 'part1_code' ];
						$gid1 = $ns_1.kTOKEN_NAMESPACE_SEPARATOR.$id1;
					
						//
						// Add term synonym.
						//
						if( ! count( $element_syn_3->xpath( "item[text()='$id1']" ) ) )
							$element_syn_3->addChild( kIO_XML_DATA, $id1 );
						
						//
						// Create part 1 code.
						//
						if( ! count( $xml_1->xpath( "//TERM[@lid='$id1']" ) ) )
						{
							//
							// Create unit.
							//
							$unit = $xml_1->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term = $unit->addChild( kIO_XML_META_TERM );
							$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_1 );
							$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id1 );
					
							//
							// Set master.
							//
							$element = $term->addChild( kIO_XML_DATA, $gid3 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
							//
							// Set term synonyms.
							//
							$element_syn_1 = $term->addChild( kIO_XML_DATA );
							$element_syn_1->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_1->addChild( kIO_XML_DATA, $id1 );
							$element_syn_1->addChild( kIO_XML_DATA, $id3 );
					
							//
							// Create node.
							//
							$node = $unit->addChild( kIO_XML_META_NODE );
					
							//
							// Set node type.
							//
							$element = $node->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
							$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
							//
							// Relate to parent.
							//
							$edge = $unit->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $ns_1 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // New part 1 element.
						
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Relate to part 3 element.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid1 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has part 1 code.
					
					//
					// Handle part 2.
					//
					if( $record[ 'part2_code' ] !== NULL )
					{
						//
						// Save local identifier.
						//
						$id2 = (string) $record[ 'part2_code' ];
						$gid2 = $ns_2.kTOKEN_NAMESPACE_SEPARATOR.$id2;
						
						//
						// Add term synonym.
						//
						if( ! count( $element_syn_3->xpath( "item[text()='$id2']" ) ) )
							$element_syn_3->addChild( kIO_XML_DATA, $id2 );
						
						//
						// Create part 2 code.
						//
						if( ! count( $xml_1->xpath( "//TERM[@lid='$id2']" ) ) )
						{
							//
							// Create unit.
							//
							$unit = $xml_2->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term = $unit->addChild( kIO_XML_META_TERM );
							$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_2 );
							$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id2 );
					
							//
							// Set master.
							//
							$element = $term->addChild( kIO_XML_DATA, $gid3 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
							//
							// Set term synonyms.
							//
							$element_syn_2 = $term->addChild( kIO_XML_DATA );
							$element_syn_2->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_2->addChild( kIO_XML_DATA, $id2 );
							$element_syn_2->addChild( kIO_XML_DATA, $id3 );
							if( $id1 !== NULL )
							{
								$element_syn_2->addChild( kIO_XML_DATA, $id1 );
								$element_syn_1->addChild( kIO_XML_DATA, $id2 );
							}
					
							//
							// Create node.
							//
							$node = $unit->addChild( kIO_XML_META_NODE );
					
							//
							// Set node type.
							//
							$element = $node->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
							$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
							//
							// Relate to parent.
							//
							$edge = $unit->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $ns_2 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // New part 2 element.
						
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Relate to part 3 element.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid2 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has part 2 code.
					
					//
					// Cross-reference part 1.
					//
					if( $id1 !== NULL )
					{
						//
						// Reference part 3.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid1 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
						//
						// Cross reference part 1 from part 2.
						//
						if( $gid2 !== NULL )
						{
							$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, $gid2 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $gid1 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // Has part 2.
					
					} // Has part 1.
					
					//
					// Cross-reference part 2.
					//
					if( $id2 !== NULL )
					{
						//
						// Reference part 3.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid2 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
						//
						// Cross reference part 2 from part 1.
						//
						if( $gid1 !== NULL )
						{
							$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, $gid1 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $gid2 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // Has part 1.
					
					} // Has part 2.
				
				} // Has record identifier.
			
			} // Iterating entries.
			
			//
			// Write files.
			//
			@unlink( $file_1 ); $xml_1->asXML( $file_1 );
			@unlink( $file_2 ); $xml_2->asXML( $file_2 );
			@unlink( $file_3 ); $xml_3->asXML( $file_3 );
			@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
		
		} // Loaded file.
		
		else
			throw new Exception
				( "Unable to load XML file [$file_in]",
				  kERROR_STATE );												// !@! ==>
		
	} // ISOGenerate6393XML.
	
	
	/*===================================================================================
	 *	ISOGenerate639XML																*
	 *==================================================================================*/

	/**
	 * Generate ISO 639 XML files
	 *
	 * This method will generate the XML ISO 639 2B and 2T files.
	 *
	 * The method will load the information from the iso-codes processed files and write
	 * the following files in the provided directory:
	 *
	 * <ul>
	 *	<li><tt>ISO639-2B.xml</tt>: Bibliobraphic codes.
	 *	<li><tt>ISO639-2T.xml</tt>: Terminological codes.
	 *	<li><tt>ISO639-xref.xml</tt>: 2B and 2T cross references.
	 * </ul>
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function ISOGenerate639XML( $theDirectory )
	{
		//
		// Load XML file.
		//
		$file_in = kISO_CODES_PATH.kISO_CODES_PATH_XML.'/'.kISO_FILE_639.'.xml';
		$xml_in = simplexml_load_file( $file_in );
		if( $xml_in instanceof SimpleXMLElement )
		{
			//
			// Inform.
			//
			if( kOPTION_VERBOSE )
				echo( "        • ISO 639 2B, 2T\n" );
			
			//
			// Set default namespaces.
			//
			$ns_1 = 'iso:639:1';
			$ns_2b = 'iso:639:2B';
			$ns_2t = 'iso:639:2T';
			$ns_common_name = 'iso:639:common_name';
			
			//
			// Set target files name.
			//
			$file_1 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-1.xml';
			$file_2b = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-2B.xml';
			$file_2t = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-2T.xml';
			$file_xref = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-xref.xml';
			
			//
			// Open XML structures.
			//
			$xml_1 = simplexml_load_file( $file_1 );
			$xml_2b = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_2t = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_xref = simplexml_load_file( $file_xref );
			
			//
			// Iterate XML file.
			//
			foreach( $xml_in->{'iso_639_entry'} as $record )
			{
				//
				// Init local storage.
				//
				$id1 = $gd3 = $id2b = $id2t = $names = $unit_xref = NULL;
			
				//
				// Check part 1 code.
				//
				if( $record[ 'iso_639_1_code' ] !== NULL )
				{
					//
					// Save identifier.
					//
					$id1 = (string) $record[ 'iso_639_1_code' ];
					
					//
					// Check if part 1 code exists.
					//
					if( count( $xml_1->xpath( "//TERM[@lid='$id1']" ) ) )
						$gid1 = $ns_1.kTOKEN_NAMESPACE_SEPARATOR.$id1;
					else
						$id1 = NULL;
				
				} // Has part 1 code.
			
				//
				// Check bibliographic code.
				//
				if( $record[ 'iso_639_2B_code' ] !== NULL )
				{
					//
					// Save identifier.
					//
					$id2b = (string) $record[ 'iso_639_2B_code' ];
					$gid2b = $ns_2b.kTOKEN_NAMESPACE_SEPARATOR.$id2b;
					
					//
					// Create unit.
					//
					$unit = $xml_2b->addChild( kIO_XML_TRANS_META );
					
					//
					// Create term.
					//
					$term = $unit->addChild( kIO_XML_META_TERM );
					$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_2b );
					$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id2b );
					
					//
					// Set master.
					//
					if( $id1 !== NULL )
					{
						$element = $term->addChild( kIO_XML_DATA, $gid1 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
					} // Has master reference.
					
					//
					// Load term data.
					//
					else
					{
						//
						// Init term names.
						//
						$names = Array();
					
						//
						// Set term english name.
						//
						if( $record[ 'name' ] !== NULL )
						{
							$tmp = (string) $record[ 'name' ];
							$names[ kTAG_LABEL ][ 'en' ] = $tmp;
						}
					
						//
						// Collect language strings.
						//
						ISOCollectLanguageElements( $names, kISO_FILE_639 );
					
						//
						// Set language strings.
						//
						foreach( $names as $tag => $strings )
							AddLanguageStrings( $term, $tag, $strings );
					
					} // No master reference.
				
					//
					// Set common name.
					//
					if( $record[ 'common_name' ] !== NULL )
					{
						$tmp = (string) $record[ 'common_name' ];
						$element = $term->addChild( kIO_XML_DATA, htmlspecialchars( $tmp ) );
						$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_common_name );
				
					} // Has common name.
					
					//
					// Create node.
					//
					$node = $unit->addChild( kIO_XML_META_NODE );
					
					//
					// Set node type.
					//
					$element = $node->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Relate to parent.
					//
					$edge = $unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_2b );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Cross reference part 1.
					//
					if( $id1 !== NULL )
					{
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
					
						//
						// Relate to part 1 element.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid2b );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid1 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has part 1.
				
				} // Has bibliographic code.
			
				//
				// Check terminological code.
				//
				if( $record[ 'iso_639_2T_code' ] !== NULL )
				{
					//
					// Save identifier.
					//
					$id2t = (string) $record[ 'iso_639_2T_code' ];
					$gid2t = $ns_2t.kTOKEN_NAMESPACE_SEPARATOR.$id2t;
					
					//
					// Create unit.
					//
					$unit = $xml_2t->addChild( kIO_XML_TRANS_META );
					
					//
					// Create term.
					//
					$term = $unit->addChild( kIO_XML_META_TERM );
					$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_2t );
					$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id2t );
			
					//
					// Set master.
					//
					if( $id1 !== NULL )
					{
						$element = $term->addChild( kIO_XML_DATA, $gid1 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
					} // Has master reference.
					
					//
					// Load term data.
					//
					else
					{
						//
						// Handle names.
						//
						if( $names === NULL )
						{
							//
							// Init term names.
							//
							$names = Array();
					
							//
							// Set term english name.
							//
							if( $record[ 'name' ] !== NULL )
							{
								$tmp = (string) $record[ 'name' ];
								$names[ kTAG_LABEL ][ 'en' ] = $tmp;
							}
					
							//
							// Collect language strings.
							//
							ISOCollectLanguageElements( $names, kISO_FILE_639 );
					
						} // No names yet.
					
						//
						// Set language strings.
						//
						foreach( $names as $tag => $strings )
							AddLanguageStrings( $term, $tag, $strings );
					
					} // No master reference.
					
					//
					// Set common name.
					//
					if( $record[ 'common_name' ] !== NULL )
					{
						$tmp = (string) $record[ 'common_name' ];
						$element = $term->addChild( kIO_XML_DATA, htmlspecialchars( $tmp ) );
						$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_common_name );
				
					} // Has common name.
					
					//
					// Create node.
					//
					$node = $unit->addChild( kIO_XML_META_NODE );
					
					//
					// Set node type.
					//
					$element = $node->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Relate to parent.
					//
					$edge = $unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_2t );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Cross reference part 1.
					//
					if( $id1 !== NULL )
					{
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
					
						//
						// Relate to part 1 element.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid2t );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid1 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has part 1.
				
				} // Created terminological code.
				
				//
				// Cross-reference bibliographical code.
				//
				if( $id2b !== NULL )
				{
					//
					// Reference part 1.
					//
					if( $id1 !== NULL )
					{
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Cross reference part 1 code.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid1 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid2b );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
				
					} // Has part 1 code.
				
					//
					// Reference terminological code.
					//
					if( $id2t !== NULL )
					{
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Cross reference bibliographical code.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid2b );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid2t );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
				
					} // Has part 1 code.
				
				} // Has bibliographical code.
				
				//
				// Cross-reference terminological code.
				//
				if( $id2b !== NULL )
				{
					//
					// Reference part 1.
					//
					if( $id1 !== NULL )
					{
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Cross reference part 1 code.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid1 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid2t );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
				
					} // Has part 1 code.
				
					//
					// Reference bibliographical code.
					//
					if( $id2t !== NULL )
					{
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Cross reference terminological code.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid2t );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid2b );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
				
					} // Has part 1 code.
				
				} // Has terminological code.
			
			} // Iterating entries.
			
			//
			// Write files.
			//
			@unlink( $file_2b ); $xml_2b->asXML( $file_2b );
			@unlink( $file_2t ); $xml_2t->asXML( $file_2t );
			@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
		
		} // Loaded file.
		
		else
			throw new Exception
				( "Unable to load XML file [$file_in]",
				  kERROR_STATE );												// !@! ==>
		
	} // ISOGenerate639XML.

	 
	/*===================================================================================
	 *	ISOGenerate6395XML																*
	 *==================================================================================*/

	/**
	 * Generate ISO 639-5 XML files
	 *
	 * This method will generate the XML ISO 639 part5 file.
	 *
	 * The method will load the information from the iso-codes processed files and write
	 * the following files in the provided directory:
	 *
	 * <ul>
	 *	<li><tt>ISO639-5.xml</tt>: Part 5 codes.
	 * </ul>
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function ISOGenerate6395XML( $theDirectory )
	{
		//
		// Load XML file.
		//
		$file_in = kISO_CODES_PATH.kISO_CODES_PATH_XML.'/'.kISO_FILE_639_5.'.xml';
		$xml_in = simplexml_load_file( $file_in  );
		if( $xml_in instanceof SimpleXMLElement )
		{
			//
			// Inform.
			//
			if( kOPTION_VERBOSE )
				echo( "        • ISO 639 5\n" );
			
			//
			// Set default namespaces.
			//
			$ns_5 = 'iso:639:5';
			
			//
			// Open XML structures.
			//
			$xml_5 = OntologyWrapper\MetadataObject::XMLRootElement();
			
			//
			// Set target files name.
			//
			$file_5 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-5.xml';
			$file_xref = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso639-xref.xml';
			
			//
			// Open XML structures.
			//
			$xml_xref = simplexml_load_file( $file_xref );
			
			//
			// Create cross reference unit.
			//
			$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
			
			//
			// Load elements.
			//
			foreach( $xml_in->{'iso_639_5_entry'} as $record )
			{
				//
				// Check identifier.
				//
				if( $record[ 'id' ] !== NULL )
				{
					//
					// Save identifier.
					//
					$id5 = (string) $record[ 'id' ];
					$gid5 = $ns_5.kTOKEN_NAMESPACE_SEPARATOR.$id5;
					
					//
					// Create unit.
					//
					$unit = $xml_5->addChild( kIO_XML_TRANS_META );
					
					//
					// Create term.
					//
					$term = $unit->addChild( kIO_XML_META_TERM );
					$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_5 );
					$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id5 );
					
					//
					// Set term instance.
					//
					$element = $term->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_TERM_TYPE' );
					$element->addChild( kIO_XML_DATA, kTYPE_TERM_INSTANCE );
					
					//
					// Set term synonyms.
					//
					$element_syn_5 = $term->addChild( kIO_XML_DATA );
					$element_syn_5->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
					$item = $element_syn_5->addChild( kIO_XML_DATA, $id5 );
					
					//
					// Init term names.
					//
					$names = Array();
					
					//
					// Set term english name.
					//
					if( $record[ 'name' ] !== NULL )
					{
						$tmp = (string) $record[ 'name' ];
						$names[ kTAG_LABEL ][ 'en' ] = $tmp;
					}
					
					//
					// Collect language strings.
					//
					ISOCollectLanguageElements( $names, kISO_FILE_639_5 );
					
					//
					// Set language strings.
					//
					foreach( $names as $tag => $strings )
						AddLanguageStrings( $term, $tag, $strings );
					
					//
					// Create node.
					//
					$node = $unit->addChild( kIO_XML_META_NODE );
					
					//
					// Set node type.
					//
					$element = $node->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Relate to class.
					//
					$edge = $unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_5 );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Handle parent.
					//
					if( $record[ 'parents' ] !== NULL )
					{
						//
						// Iterate parents.
						//
						$parents = explode( ',', (string) $record[ 'parents' ] );
						foreach( $parents as $parent )
						{
							//
							// Trim identifier.
							//
							$parent = trim( $parent );
						
							//
							// Save parent identifier.
							//
							$gid5parent = $ns_5.kTOKEN_NAMESPACE_SEPARATOR.$parent;
					
							//
							// Relate to parent.
							//
							$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, $gid5 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_SUBSET_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $gid5parent );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
							
						} // Iterating parents.
					
					} // Has parent.
				
				} // Has record identifier.
			
			} // Iterating entries.
			
			//
			// Write files.
			//
			@unlink( $file_5 ); $xml_5->asXML( $file_5 );
			@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
		
		} // Loaded file.
		
		else
			throw new Exception
				( "Unable to load XML file [$file_in]",
				  kERROR_STATE );												// !@! ==>
		
	} // ISOGenerate6395XML.
	
	 
	/*===================================================================================
	 *	ISOGenerate31661XML																*
	 *==================================================================================*/

	/**
	 * Generate ISO 3166-1 XML files
	 *
	 * This method will generate the XML ISO 3166 part 1 files.
	 *
	 * The method will load the information from the iso-codes processed files and write
	 * the following files in the provided directory:
	 *
	 * <ul>
	 *	<li><tt>ISO3166-1-alpha2.xml</tt>: Alpha-2 codes.
	 *	<li><tt>ISO3166-1-alpha3.xml</tt>: Alpha-3 codes.
	 *	<li><tt>ISO3166-1-numeric.xml</tt>: Numeric codes.
	 *	<li><tt>ISO3166-xref.xml</tt>: Alpha-1, alpha-2 and numeric codes cross references.
	 * </ul>
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function ISOGenerate31661XML( $theDirectory )
	{
		//
		// Load XML file.
		//
		$file_in = kISO_CODES_PATH.kISO_CODES_PATH_XML.'/'.kISO_FILE_3166.'.xml';
		$xml_in = simplexml_load_file( $file_in  );
		if( $xml_in instanceof SimpleXMLElement )
		{
			//
			// Inform.
			//
			if( kOPTION_VERBOSE )
				echo( "        • ISO 3166 1\n" );
			
			//
			// Set default namespaces.
			//
			$ns_2 = 'iso:3166:1:alpha-2';
			$ns_3 = 'iso:3166:1:alpha-3';
			$ns_n = 'iso:3166:1:numeric';
			$ns_common_name = 'iso:3166:common_name';
			$ns_location = 'iso:3166:location';
			$ns_country_sub = 'iso:3166:country-sub';
			
			//
			// Set target files name.
			//
			$file_2 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-1-alpha2.xml';
			$file_3 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-1-alpha3.xml';
			$file_n = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-1-numeric.xml';
			$file_sub = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-2-subset.xml';
			$file_xref = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-xref.xml';
			$file_location = $theDirectory."/".kDIR_STANDARDS_DEFAULT
														.'/iso-locations-country.xml';
			
			//
			// Open XML structures.
			//
			$xml_2 = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_3 = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_n = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_sub = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_xref = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_location = OntologyWrapper\MetadataObject::XMLRootElement();
			
			//
			// Load counry subsets file.
			//
			$xml_sub_in
				= simplexml_load_file(
					kISO_CODES_PATH.kISO_CODES_PATH_XML.'/'.kISO_FILE_3166_2.'.xml' );
			
			//
			// Iterate XML file.
			//
			foreach( $xml_in->{'iso_3166_entry'} as $record )
			{
				//
				// Reset cross reference data.
				//
				$id12 = $id3 = $idn = $unit_xref = NULL;
				
				//
				// Check identifier.
				//
				if( $record[ 'alpha_3_code' ] !== NULL )
				{
					//
					// Save country identifier.
					//
					$id3 = (string) $record[ 'alpha_3_code' ];
					$gid3 = $ns_3.kTOKEN_NAMESPACE_SEPARATOR.$id3;
					
					//
					// Save location pid.
					//
					$pid_loc = ":location:".$gid3;
					
					//
					// Create units.
					//
					$unit = $xml_3->addChild( kIO_XML_TRANS_META );
					$loc_unit = $xml_location->addChild( kIO_XML_TRANS_META );
					
					//
					// Create term.
					//
					$term = $unit->addChild( kIO_XML_META_TERM );
					$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_3 );
					$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id3 );
					
					//
					// Set term instance.
					//
					$element = $term->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_TERM_TYPE' );
					$element->addChild( kIO_XML_DATA, kTYPE_TERM_INSTANCE );
					
					//
					// Set term synonyms.
					//
					$element_syn_3 = $term->addChild( kIO_XML_DATA );
					$element_syn_3->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
					$element_syn_3->addChild( kIO_XML_DATA, $id3 );
					
					//
					// Init term names.
					//
					$country_name = NULL;
					$names = Array();
					
					//
					// Set term english name.
					//
					if( $record[ 'name' ] !== NULL )
					{
						$tmp = (string) $record[ 'name' ];
						$names[ kTAG_LABEL ][ 'en' ] = $tmp;
						$country_name = $tmp;
					}
					
					//
					// Set term english official name.
					//
					if( $record[ 'official_name' ] !== NULL )
					{
						$tmp = (string) $record[ 'official_name' ];
						$names[ kTAG_DEFINITION ][ 'en' ] = $tmp;
						if( $country_name === NULL )
							$country_name = $tmp;
					}
					
					//
					// Set term common name.
					//
					if( $record[ 'common_name' ] !== NULL )
					{
						$tmp = (string) $record[ 'common_name' ];
						$names[ $ns_common_name ][ 'en' ] = $tmp;
						if( $country_name === NULL )
							$country_name = $tmp;
					}
					
					//
					// Collect language strings.
					//
					ISOCollectLanguageElements( $names, kISO_FILE_3166 );
					
					//
					// Set language strings.
					//
					foreach( $names as $tag => $strings )
						AddLanguageStrings( $term, $tag, $strings );
					
					//
					// Create country node.
					//
					$node = $unit->addChild( kIO_XML_META_NODE );
					$element = $node->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Create location node.
					//
					$loc_node = $loc_unit->addChild( kIO_XML_META_NODE );
					$loc_node->addAttribute( kIO_XML_ATTR_REF_TERM, $gid3 );
					$loc_node->addAttribute( kIO_XML_ATTR_ID_PERSISTENT, $pid_loc );
					$loc_element = $loc_node->addChild( kIO_XML_DATA );
					$loc_element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$loc_item = $loc_element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Relate country to parent.
					//
					$edge = $unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_3 );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Handle alpha 2.
					//
					if( $record[ 'alpha_2_code' ] !== NULL )
					{
						//
						// Save local identifier.
						//
						$id2 = (string) $record[ 'alpha_2_code' ];
						$gid2 = $ns_2.kTOKEN_NAMESPACE_SEPARATOR.$id2;
						
						//
						// Iterate subdivisions.
						//
						foreach(
							$xml_sub_in->xpath(
								"//iso_3166_country[@code='$id2']//iso_3166_subset" )
								as $subset )
						{
							//
							// Save subdivision name.
							//
							$name = (string) $subset[ 'type' ];
							$sub_name = "$country_name $name";
							
							//
							// Save identifier.
							//
							$id_sub = $id2.kTOKEN_NAMESPACE_SEPARATOR.$name;
							$gid_sub = $ns_location.kTOKEN_NAMESPACE_SEPARATOR.$id_sub;
					
							//
							// Create unit.
							//
							$unit_sub = $xml_sub->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term_sub = $unit_sub->addChild( kIO_XML_META_TERM );
							$term_sub->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_location );
							$term_sub->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id_sub );
					
							//
							// Set label.
							//
							$element_sub = $term_sub->addChild( kIO_XML_DATA );
							$element_sub->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_LABEL' );
							$element_sub = $element_sub->addChild( kIO_XML_DATA );
							$item_sub = $element_sub->addChild( kIO_XML_DATA, 'en' );
							$item_sub->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_LANGUAGE' );
							$item_sub = $element_sub->addChild( kIO_XML_DATA, $sub_name );
							$item_sub->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_TEXT' );
					
							//
							// Set synonyms.
							//
							$element_syn_sub = $term_sub->addChild( kIO_XML_DATA );
							$element_syn_sub->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_sub->addChild( kIO_XML_DATA, $id_sub );
					
							//
							// Create node.
							//
							$node_sub = $unit_sub->addChild( kIO_XML_META_NODE );
					
							//
							// Relate to parent.
							//
							$edge_sub = $unit_sub->addChild( kIO_XML_META_EDGE );
							$element_sub = $edge_sub->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element_sub->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element_sub = $edge_sub->addChild( kIO_XML_DATA, $pid_loc );
							$element_sub->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element_sub->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_NODE_PID );
						
						} // Iterating subdivisions.
					
						//
						// Add term synonym.
						//
						$element_syn_3->addChild( kIO_XML_DATA, $id2 );
						
						//
						// Create alpha 2 code.
						//
						if( ! count( $xml_2->xpath( "//TERM[@lid='$id2']" ) ) )
						{
							//
							// Create unit.
							//
							$unit = $xml_2->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term = $unit->addChild( kIO_XML_META_TERM );
							$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_2 );
							$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id2 );
					
							//
							// Set master.
							//
							$element = $term->addChild( kIO_XML_DATA, $gid3 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
							//
							// Set term synonyms.
							//
							$element_syn_2 = $term->addChild( kIO_XML_DATA );
							$element_syn_2->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_2->addChild( kIO_XML_DATA, $id2 );
							$element_syn_2->addChild( kIO_XML_DATA, $id3 );
					
							//
							// Create node.
							//
							$node = $unit->addChild( kIO_XML_META_NODE );
					
							//
							// Set node type.
							//
							$element = $node->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
							$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
							//
							// Relate to parent.
							//
							$edge = $unit->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $ns_2 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // New alpha 2 element.
						
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Relate to alpha 3 element.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid2 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						//
						// Cross-reference location.
						//
						$edge = $loc_unit->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid2 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					
						$edge = $loc_unit->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid2 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has alpha 2 code.
					
					//
					// Handle numeric code.
					//
					if( $record[ 'numeric_code' ] !== NULL )
					{
						//
						// Save local identifier.
						//
						$idn = (string) $record[ 'numeric_code' ];
						$gidn = $ns_n.kTOKEN_NAMESPACE_SEPARATOR.$idn;
				
						//
						// Add term synonym.
						//
						$element_syn_3->addChild( kIO_XML_DATA, $idn );
						
						//
						// Create numeric code.
						//
						if( ! count( $xml_n->xpath( "//TERM[@lid='$idn']" ) ) )
						{
							//
							// Create unit.
							//
							$unit = $xml_n->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term = $unit->addChild( kIO_XML_META_TERM );
							$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_n );
							$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $idn );
					
							//
							// Set master.
							//
							$element = $term->addChild( kIO_XML_DATA, $gid3 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
							//
							// Set term synonyms.
							//
							$element_syn_n = $term->addChild( kIO_XML_DATA );
							$element_syn_n->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_n->addChild( kIO_XML_DATA, $idn );
							$element_syn_n->addChild( kIO_XML_DATA, $id3 );
							if( $id2 !== NULL )
							{
								$element_syn_n->addChild( kIO_XML_DATA, $id2 );
								$element_syn_2->addChild( kIO_XML_DATA, $idn );
							}
					
							//
							// Create node.
							//
							$node = $unit->addChild( kIO_XML_META_NODE );
					
							//
							// Set node type.
							//
							$element = $node->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
							$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
							//
							// Relate to parent.
							//
							$edge = $unit->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $ns_n );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // New numeric code.
						
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Relate to alpha 3 element.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						//
						// Cross-reference location.
						//
						$edge = $loc_unit->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					
						$edge = $loc_unit->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has numeric code.
					
					//
					// Cross-reference alpha 2.
					//
					if( $id2 !== NULL )
					{
						//
						// Reference alpha 3.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid2 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
						//
						// Cross reference alpha 2 from numeric.
						//
						if( $gidn !== NULL )
						{
							$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, $gidn );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $gid2 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // Has numeric.
					
					} // Has alpha 2.
					
					//
					// Cross-reference numeric.
					//
					if( $idn !== NULL )
					{
						//
						// Reference alpha 3.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
						//
						// Cross reference alpha 2 from numeric.
						//
						if( $gid2 !== NULL )
						{
							$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, $gid2 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $gidn );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // Has part 1.
					
					} // Has part 2.
				
				} // Has record identifier.
			
			} // Iterating entries.
			
			//
			// Write files.
			//
			@unlink( $file_2 ); $xml_2->asXML( $file_2 );
			@unlink( $file_3 ); $xml_3->asXML( $file_3 );
			@unlink( $file_n ); $xml_n->asXML( $file_n );
			@unlink( $file_sub ); $xml_sub->asXML( $file_sub );
			@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
			@unlink( $file_location ); $xml_location->asXML( $file_location );
		
		} // Loaded file.
		
		else
			throw new Exception
				( "Unable to load XML file [$file_in]",
				  kERROR_STATE );												// !@! ==>
		
	} // ISOGenerate31661XML.	
	
	 
	/*===================================================================================
	 *	ISOGenerate31663XML																*
	 *==================================================================================*/

	/**
	 * Generate ISO 3166-3 XML files
	 *
	 * This method will generate the XML ISO 3166 part 3 files.
	 *
	 * The method will load the information from the iso-codes processed files and write
	 * the following files in the provided directory:
	 *
	 * <ul>
	 *	<li><tt>ISO3166-3-alpha3.xml</tt>: Alpha-3 codes.
	 *	<li><tt>ISO3166-3-alpha4.xml</tt>: Alpha-4 codes.
	 *	<li><tt>ISO3166-1-numeric.xml</tt>: Numeric codes.
	 *	<li><tt>ISO3166-xref.xml</tt>: Alpha-3, alpha-4 and numeric codes cross references.
	 * </ul>
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function ISOGenerate31663XML( $theDirectory )
	{
		//
		// Load XML file.
		//
		$file_in = kISO_CODES_PATH.kISO_CODES_PATH_XML.'/'.kISO_FILE_3166.'.xml';
		$xml_in = simplexml_load_file( $file_in  );
		if( $xml_in instanceof SimpleXMLElement )
		{
			//
			// Inform.
			//
			if( kOPTION_VERBOSE )
				echo( "        • ISO 3166 3\n" );
			
			//
			// Set default namespaces.
			//
			$ns_3 = 'iso:3166:3:alpha-3';
			$ns_4 = 'iso:3166:3:alpha-4';
			$ns_n = 'iso:3166:3:numeric';
			$ns_date_witdrawn = 'iso:date_withdrawn';
			$ns_location = 'iso:3166:location';
			$ns_country_sub = 'iso:3166:country-sub';
			
			//
			// Set target files name.
			//
			$file_3 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-3-alpha3.xml';
			$file_4 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-3-alpha4.xml';
			$file_n = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-3-numeric.xml';
			$file_xref = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-xref.xml';
			$file_location = $theDirectory."/".kDIR_STANDARDS_DEFAULT
													.'/iso-locations-country.xml';
			
			//
			// Open XML structures.
			//
			$xml_3 = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_4 = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_n = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_xref = simplexml_load_file( $file_xref );
			$xml_location = simplexml_load_file( $file_location );
			
			//
			// Iterate XML file.
			//
			foreach( $xml_in->{'iso_3166_3_entry'} as $record )
			{
				//
				// Reset cross reference data.
				//
				$id13 = $id4 = $idn = $unit_xref = NULL;
				
				//
				// Check identifier.
				//
				if( $record[ 'alpha_3_code' ] !== NULL )
				{
					//
					// Save identifier.
					//
					$id3 = (string) $record[ 'alpha_3_code' ];
					$gid3 = $ns_3.kTOKEN_NAMESPACE_SEPARATOR.$id3;
					
					//
					// Save location pid.
					//
					$pid_loc = ":location:".$gid3;
					
					//
					// Create units.
					//
					$unit = $xml_3->addChild( kIO_XML_TRANS_META );
					$loc_unit = $xml_location->addChild( kIO_XML_TRANS_META );
					
					//
					// Create term.
					//
					$term = $unit->addChild( kIO_XML_META_TERM );
					$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_3 );
					$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id3 );
					
					//
					// Set term instance.
					//
					$element = $term->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_TERM_TYPE' );
					$element->addChild( kIO_XML_DATA, kTYPE_TERM_INSTANCE );
					
					//
					// Set term synonyms.
					//
					$element_syn_3 = $term->addChild( kIO_XML_DATA );
					$element_syn_3->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
					$element_syn_3->addChild( kIO_XML_DATA, $id3 );
					
					//
					// Init term names.
					//
					$names = Array();
					
					//
					// Set term english name.
					//
					if( $record[ 'names' ] !== NULL )
					{
						$tmp = (string) $record[ 'names' ];
						$names[ kTAG_LABEL ][ 'en' ] = $tmp;
					}
					
					//
					// Set term english definition.
					//
					if( $record[ 'comment' ] !== NULL )
					{
						$tmp = (string) $record[ 'comment' ];
						$names[ kTAG_DEFINITION ][ 'en' ] = $tmp;
					}
					
					//
					// Collect language strings.
					//
					ISOCollectLanguageElements( $names, kISO_FILE_3166 );
					
					//
					// Set language strings.
					//
					foreach( $names as $tag => $strings )
						AddLanguageStrings( $term, $tag, $strings );
					
					//
					// Set date withdrawn.
					//
					if( $record[ 'date_withdrawn' ] !== NULL )
					{
						$tmp = (string) $record[ 'date_withdrawn' ];
						$element = $term->addChild( kIO_XML_DATA, htmlspecialchars( $tmp ) );
						$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_date_witdrawn );
					}
					
					//
					// Create country node.
					//
					$node = $unit->addChild( kIO_XML_META_NODE );
					$element = $node->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Create location node.
					//
					$loc_node = $loc_unit->addChild( kIO_XML_META_NODE );
					$loc_node->addAttribute( kIO_XML_ATTR_REF_TERM, $gid3 );
					$loc_node->addAttribute( kIO_XML_ATTR_ID_PERSISTENT, $pid_loc );
					$loc_element = $loc_node->addChild( kIO_XML_DATA );
					$loc_element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$loc_item = $loc_element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Relate location node to country subdivisions.
					//
					$edge = $loc_unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_country_sub );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Relate country to parent.
					//
					$edge = $unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_3 );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Handle alpha 4.
					//
					if( $record[ 'alpha_4_code' ] !== NULL )
					{
						//
						// Save local identifier.
						//
						$id4 = (string) $record[ 'alpha_4_code' ];
						$gid4 = $ns_4.kTOKEN_NAMESPACE_SEPARATOR.$id4;
					
						//
						// Add term synonym.
						//
						$element_syn_3->addChild( kIO_XML_DATA, $id4 );
						
						//
						// Create alpha 4 code.
						//
						if( ! count( $xml_4->xpath( "//TERM[@lid='$id4']" ) ) )
						{
							//
							// Create unit.
							//
							$unit = $xml_4->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term = $unit->addChild( kIO_XML_META_TERM );
							$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_4 );
							$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id4 );
					
							//
							// Set master.
							//
							$element = $term->addChild( kIO_XML_DATA, $gid3 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
							//
							// Set term synonyms.
							//
							$element_syn_4 = $term->addChild( kIO_XML_DATA );
							$element_syn_4->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_4->addChild( kIO_XML_DATA, $id4 );
							$element_syn_4->addChild( kIO_XML_DATA, $id3 );
					
							//
							// Create node.
							//
							$node = $unit->addChild( kIO_XML_META_NODE );
					
							//
							// Set node type.
							//
							$element = $node->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
							$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
							//
							// Relate to parent.
							//
							$edge = $unit->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $ns_4 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // New alpha 4 element.
						
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Relate to alpha 3 element.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid4 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						//
						// Cross-reference location.
						//
						$edge = $loc_unit->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid4 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					
						$edge = $loc_unit->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid4 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has alpha 4 code.
					
					//
					// Handle numeric code.
					//
					if( $record[ 'numeric_code' ] !== NULL )
					{
						//
						// Save local identifier.
						//
						$idn = (string) $record[ 'numeric_code' ];
						$gidn = $ns_n.kTOKEN_NAMESPACE_SEPARATOR.$idn;
				
						//
						// Add term synonym.
						//
						$element_syn_3->addChild( kIO_XML_DATA, $idn );
						
						//
						// Create numeric code.
						//
						if( ! count( $xml_n->xpath( "//TERM[@lid='$idn']" ) ) )
						{
							//
							// Create unit.
							//
							$unit = $xml_n->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term = $unit->addChild( kIO_XML_META_TERM );
							$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_n );
							$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $idn );
					
							//
							// Set master.
							//
							$element = $term->addChild( kIO_XML_DATA, $gid3 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
							//
							// Set term synonyms.
							//
							$element_syn_n = $term->addChild( kIO_XML_DATA );
							$element_syn_n->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_n->addChild( kIO_XML_DATA, $idn );
							$element_syn_n->addChild( kIO_XML_DATA, $id3 );
							if( $id4 !== NULL )
							{
								$element_syn_n->addChild( kIO_XML_DATA, $id4 );
								$element_syn_4->addChild( kIO_XML_DATA, $idn );
							}
					
							//
							// Create node.
							//
							$node = $unit->addChild( kIO_XML_META_NODE );
					
							//
							// Set node type.
							//
							$element = $node->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
							$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
							//
							// Relate to parent.
							//
							$edge = $unit->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $ns_n );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // New numeric code.
						
						//
						// Create cross reference unit.
						//
						if( $unit_xref === NULL )
							$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
						
						//
						// Relate to alpha 3 element.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						//
						// Cross-reference location.
						//
						$edge = $loc_unit->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					
						$edge = $loc_unit->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has numeric code.
					
					//
					// Cross-reference alpha 4.
					//
					if( $id4 !== NULL )
					{
						//
						// Reference alpha 3.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid4 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
						//
						// Cross reference alpha 4 from numeric.
						//
						if( $idn !== NULL )
						{
							$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, $gidn );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $gid4 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // Has numeric.
					
					} // Has alpha 4.
					
					//
					// Cross-reference numeric.
					//
					if( $idn !== NULL )
					{
						//
						// Reference alpha 3.
						//
						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid3 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
						//
						// Cross reference alpha 4 from numeric.
						//
						if( $id4 !== NULL )
						{
							$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, $gid4 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $gidn );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // Has alpha 4.
					
					} // Has numeric.
				
				} // Has record identifier.
			
			} // Iterating entries.
			
			//
			// Write files.
			//
			@unlink( $file_3 ); $xml_3->asXML( $file_3 );
			@unlink( $file_4 ); $xml_4->asXML( $file_4 );
			@unlink( $file_n ); $xml_n->asXML( $file_n );
			@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
			@unlink( $file_location ); $xml_location->asXML( $file_location );
		
		} // Loaded file.
		
		else
			throw new Exception
				( "Unable to load XML file [$file_in]",
				  kERROR_STATE );												// !@! ==>
		
	} // ISOGenerate31663XML.	
	
	 
	/*===================================================================================
	 *	ISOGenerate31662XML																*
	 *==================================================================================*/

	/**
	 * Generate ISO 3166-2 XML files
	 *
	 * This method will generate the XML ISO 3166 part 2 files.
	 *
	 * The method will load the information from the iso-codes processed files and write
	 * the following files in the provided directory:
	 *
	 * <ul>
	 *	<li><tt>ISO3166-2-alpha2.xml</tt>: Alpha-2 codes.
	 *	<li><tt>ISO3166-xref.xml</tt>: Subdivision and country codes cross references.
	 * </ul>
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function ISOGenerate31662XML( $theDirectory )
	{
		//
		// Load XML file.
		//
		$file_in = kISO_CODES_PATH.kISO_CODES_PATH_XML.'/'.kISO_FILE_3166_2.'.xml';
		$xml_in = simplexml_load_file( $file_in  );
		if( $xml_in instanceof SimpleXMLElement )
		{
			//
			// Inform.
			//
			if( kOPTION_VERBOSE )
				echo( "        • ISO 3166 2\n" );
			
			//
			// Set default namespaces.
			//
			$ns_2 = 'iso:3166:1:alpha-2';
			$ns_3 = 'iso:3166:1:alpha-3';
			$ns_n = 'iso:3166:1:numeric';
			$ns_sub = 'iso:3166:2';
			$ns_type = 'iso:3166:2:type';
			$ns_location = 'iso:3166:location';
			
			//
			// Set target files name.
			//
			$file_2 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-1-alpha2.xml';
			$file_sub = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-2.xml';
			$file_xref = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-xref.xml';
			$file_location = $theDirectory."/".kDIR_STANDARDS_DEFAULT
														.'/iso-locations-subset.xml';
			
			//
			// Open XML structures.
			//
			$xml_2 = simplexml_load_file( $file_2 );
			$xml_sub = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_xref = simplexml_load_file( $file_xref );
			$xml_location = OntologyWrapper\MetadataObject::XMLRootElement();
			
			//
			// Iterate XML file.
			//
			foreach( $xml_in->{'iso_3166_country'} as $country )
			{
				//
				// Reset cross reference data.
				//
				$idsub = $gid3 = $gidn = $unit_xref = NULL;
			
				//
				// Check identifier.
				//
				if( $country[ 'code' ] !== NULL )
				{
					//
					// Reference country alpha-2 code.
					//
					$id_2 = (string) $country[ 'code' ];
					$gid_2 = $ns_2.kTOKEN_NAMESPACE_SEPARATOR.$id_2;
					
					//
					// Get country synonyms.
					//
					$id3 = NULL;
					$term_2 = $xml_2->xpath( "//TERM[@lid='$id_2']" )[ 0 ];
					$syns = $term_2->xpath( "item[@const='kTAG_SYNONYM']" );
					if( count( $syns ) )
					{
						//
						// Iterate synonyms.
						//
						foreach( $syns[ 0 ]->item as $syn )
						{
							//
							// Skip if same as above.
							//
							$syn = (string) $syn;
							if( $id_2 != $syn )
							{
								//
								// Handle numeric.
								//
								if( ctype_digit( $syn ) )
									$gidn = $ns_n.kTOKEN_NAMESPACE_SEPARATOR.$syn;
								
								//
								// Handle alpha-3.
								//
								else
									$gid3 = $ns_3.kTOKEN_NAMESPACE_SEPARATOR.$syn;
							
							} // Not alpha-2 code.
						
						} // Iterating synonyms.
					
					} // Has synonyms.
					
					//
					// Iterate subsets.
					//
					foreach( $country->{'iso_3166_subset'} as $subset )
					{
						//
						// Get subset type.
						//
						if( $subset[ 'type' ] !== NULL )
						{
							//
							// Save subset type.
							//
							$type = (string) $subset[ 'type' ];
							
							//
							// Save subdivision identifier.
							//
							$id_subset = $id_2.kTOKEN_NAMESPACE_SEPARATOR.$type;
							$gid_subset = $ns_location.kTOKEN_NAMESPACE_SEPARATOR.$id_subset;
							
							//
							// Iterate subset entries.
							//
							foreach( $subset->{'iso_3166_2_entry'} as $entry )
							{
								//
								// Get entry code.
								//
								if( $entry[ 'code' ] !== NULL )
								{
									//
									// Save entry code.
									//
									$idsub = (string) $entry[ 'code' ];
									$gidsub = $ns_sub.kTOKEN_NAMESPACE_SEPARATOR.$idsub;
					
									//
									// Save location pid.
									//
									$pid_loc = ":location:".$gidsub;
					
									//
									// Create unit.
									//
									$unit = $xml_sub->addChild( kIO_XML_TRANS_META );
									$unit_sub = $xml_location->addChild( kIO_XML_TRANS_META );
					
									//
									// Create term.
									//
									$term = $unit->addChild( kIO_XML_META_TERM );
									$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_sub );
									$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $idsub );
					
									//
									// Set type property.
									//
									$element = $term->addChild( kIO_XML_DATA, $type );
									$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_type );
					
									//
									// Set term synonyms.
									//
									$element = $term->addChild( kIO_XML_DATA );
									$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
									$element->addChild( kIO_XML_DATA, $idsub );
					
									//
									// Init term names.
									//
									$names = Array();
					
									//
									// Set term label.
									//
									if( $entry[ 'name' ] !== NULL )
									{
										$tmp = (string) $entry[ 'name' ];
										$names[ kTAG_LABEL ][ 'en' ] = $tmp;
									}
					
									//
									// Collect language strings.
									//
									ISOCollectLanguageElements( $names, kISO_FILE_3166_2 );
					
									//
									// Set language strings.
									//
									foreach( $names as $tag => $strings )
										AddLanguageStrings( $term, $tag, $strings );
					
									//
									// Create subdivision node.
									//
									$node = $unit->addChild( kIO_XML_META_NODE );
									$element = $node->addChild( kIO_XML_DATA );
									$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
									$item = $element->addChild( kIO_XML_DATA,
																kTYPE_NODE_ENUMERATION );
									
									//
									// Create location node.
									//
									$node_sub = $unit_sub->addChild( kIO_XML_META_NODE );
									$node_sub->addAttribute( kIO_XML_ATTR_REF_TERM, $gidsub );
									$node_sub->addAttribute( kIO_XML_ATTR_ID_PERSISTENT, $pid_loc );
									$loc_element = $node_sub->addChild( kIO_XML_DATA );
									$loc_element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
									$loc_item = $loc_element->addChild( kIO_XML_DATA,
																kTYPE_NODE_ENUMERATION );
					
									//
									// Relate subdivision to parent.
									//
									$edge = $unit->addChild( kIO_XML_META_EDGE );
									$element = $edge->addChild(
										kIO_XML_DATA, kPREDICATE_ENUM_OF );
									$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
									$element = $edge->addChild( kIO_XML_DATA, $ns_sub );
									$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
									$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
									
									//
									// Relate location to subset.
									//
									$edge_sub = $unit_sub->addChild( kIO_XML_META_EDGE );
									$element_sub = $edge_sub->addChild(
										kIO_XML_DATA, kPREDICATE_ENUM_OF );
									$element_sub->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
									$element_sub = $edge_sub->addChild( kIO_XML_DATA, $gid_subset );
									$element_sub->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
									$element_sub->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
									
									//
									// Determine supersets.
									//
									$parents = Array();
									if( $entry[ 'parent' ] !== NULL )
									{
										$tmp = $id_2.'-'.(string) $entry[ 'parent' ];
										$parents[]
											= $ns_sub.kTOKEN_NAMESPACE_SEPARATOR.$tmp;
									}
									else
									{
										//
										// Parent is country.
										//
										$parents[] = $gid_2;
										if( $gid3 !== NULL )
											$parents[] = $gid3;
										if( $gidn !== NULL )
											$parents[] = $gidn;
									}
						
									//
									// Create cross reference unit.
									//
									if( $unit_xref === NULL )
										$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
									
									//
									// Add subset relationships.
									//
									foreach( $parents as $parent )
									{
										$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
										$element = $edge->addChild( kIO_XML_DATA, $gidsub );
										$element->addAttribute(
											kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
										$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
										$element = $edge->addChild(
											kIO_XML_DATA, kPREDICATE_SUBSET_OF );
										$element->addAttribute(
											kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
										$element = $edge->addChild( kIO_XML_DATA, $parent );
										$element->addAttribute(
											kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
										$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
									}
									
								} // Has entry code.
							
							} // Iterating subset entries.
						
						} // Has subset type.
					
					} // Iterating subsets.
				
				} // Has country identifier.
			
			} // Iterating entries.
			
			//
			// Write files.
			//
			@unlink( $file_sub ); $xml_sub->asXML( $file_sub );
			@unlink( $file_location ); $xml_location->asXML( $file_location );
			@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
		
		} // Loaded file.
		
		else
			throw new Exception
				( "Unable to load XML file [$file_in]",
				  kERROR_STATE );												// !@! ==>
		
	} // ISOGenerate31662XML.	
	
	 
	/*===================================================================================
	 *	ISOGenerate31663XMLRelations													*
	 *==================================================================================*/

	/**
	 * Generate ISO 3166 XML relationships
	 *
	 * This method will update the ISO 3166 XML cross references file by adding:
	 *
	 * <ul>
	 *	<li><tt>{@link kPREDICATE_VALID}</tt>: Relate all valid country relationships.
	 *	<li><tt>{@link kPREDICATE_LEGACY}</tt>: Relate all legacy country relationships.
	 *	<li><tt>{@link kPREDICATE_SUBSET}</tt>: Relate all countries with continents.
	 * </ul>
	 *
	 * The method will update the ISO3166-xref.xml file.
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function ISOGenerate31663XMLRelations( $theDirectory )
	{
		//
		// Inform.
		//
		if( kOPTION_VERBOSE )
			echo( "        • ISO 3166 relationships\n" );
	
		//
		// Set default namespaces.
		//
		$ns_13 = 'iso:3166:1:alpha-3';
		$ns_33 = 'iso:3166:3:alpha-3';
		$ns_1n = 'iso:3166:1:numeric';
		$ns_3n = 'iso:3166:3:numeric';
		
		//
		// Set target files name.
		//
		$file_13 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-1-alpha3.xml';
		$file_33 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-3-alpha3.xml';
		$file_rel = $theDirectory."/".kDIR_STANDARDS_ISO.'/ISO3166-RELATIONSHIPS.xml';
		$file_xref = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-xref.xml';
		
		//
		// Open XML structures.
		//
		$xml_13 = simplexml_load_file( $file_13 );
		$xml_33 = simplexml_load_file( $file_33 );
		$xml_rel = simplexml_load_file( $file_rel );
		$xml_xref = simplexml_load_file( $file_xref );
		
		//
		// Handle valid relationsips.
		//
		$xpath = "//EDGE/item[.='".kPREDICATE_VALID."']";
		$list = $xml_rel->xpath( $xpath );
		foreach( $list as $element )
		{
			//
			// Load subject and object.
			//
			$edge = $element->xpath( ".." )[ 0 ];
			$subject = (string) $edge->xpath(
				"item[@const='kTAG_SUBJECT']" )[ 0 ];
			$object = (string) $edge->xpath(
				"item[@const='kTAG_OBJECT']" )[ 0 ];
			
			//
			// Get namespaces.
			//
			$subject_ns = substr( $subject, 0, strlen( $subject ) - 3 );
			$object_ns = substr( $object, 0, strlen( $subject ) - 3 );
			
			//
			// Get identifiers.
			//
			$subject_id = substr( $subject, strlen( $subject_ns ) );
			$object_id = substr( $object, strlen( $object_ns ) );
			
			//
			// Create cross reference unit.
			//
			$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
			
			//
			// Write current relationship.
			//
			$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
			$element = $edge->addChild( kIO_XML_DATA, $subject );
			$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
			$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
			$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_VALID );
			$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
			$element = $edge->addChild( kIO_XML_DATA, $object );
			$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
			$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
			
			//
			// Locate subject.
			//
			$subject_rec = ( substr( $subject_ns, 9, 1 ) == '3' )
						 ? $xml_33->xpath( "//TERM[@lid='$subject_id']" )[ 0 ]
						 : $xml_13->xpath( "//TERM[@lid='$subject_id']" )[ 0 ];
			$subject_nns = ( substr( $subject_ns, 9, 1 ) == '3' )
						 ? $ns_3n
						 : $ns_1n;
			$subject_syn = $subject_rec->xpath( "item[@const='kTAG_SYNONYM']" )[ 0 ];
			$subject_num = NULL;
			foreach( $subject_syn->xpath( "item" ) as $item )
			{
				if( ctype_digit( (string) $item ) )
				{
					$subject_num = (string) $item;
					break;
				}
			}
			
			//
			// Locate object.
			//
			$object_rec = ( substr( $object_ns, 9, 1 ) == '3' )
						 ? $xml_33->xpath( "//TERM[@lid='$object_id']" )[ 0 ]
						 : $xml_13->xpath( "//TERM[@lid='$object_id']" )[ 0 ];
			$object_nns = ( substr( $object_ns, 9, 1 ) == '3' )
						 ? $ns_3n
						 : $ns_1n;
			$object_syn = $object_rec->xpath( "item[@const='kTAG_SYNONYM']" )[ 0 ];
			$object_num = NULL;
			foreach( $object_syn->xpath( "item" ) as $item )
			{
				if( ctype_digit( (string) $item ) )
				{
					$object_num = (string) $item;
					break;
				}
			}
			
			//
			// Relate numeric codes.
			//
			if( ($subject_num !== NULL)
			 && ($object_num !== NULL)
			 && ($subject_num !== $object_num) )
			{
				$subject_gid = $subject_nns.kTOKEN_NAMESPACE_SEPARATOR.$subject_num;
				$object_gid = $object_nns.kTOKEN_NAMESPACE_SEPARATOR.$object_num;
				
				$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
				$element = $edge->addChild( kIO_XML_DATA, $subject_gid );
				$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
				$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
				$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_VALID );
				$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
				$element = $edge->addChild( kIO_XML_DATA, $object_gid );
				$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
				$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
			}
		
		} // Iterating valid relationships.
		
		//
		// Handle legacy relationsips.
		//
		$xpath = "//EDGE/item[.='".kPREDICATE_LEGACY."']";
		$list = $xml_rel->xpath( $xpath );
		foreach( $list as $element )
		{
			//
			// Load subject and object.
			//
			$edge = $element->xpath( ".." )[ 0 ];
			$subject = (string) $edge->xpath(
				"item[@const='kTAG_SUBJECT']" )[ 0 ];
			$object = (string) $edge->xpath(
				"item[@const='kTAG_OBJECT']" )[ 0 ];
			
			//
			// Get namespaces.
			//
			$subject_ns = substr( $subject, 0, strlen( $subject ) - 3 );
			$object_ns = substr( $object, 0, strlen( $subject ) - 3 );
			
			//
			// Get identifiers.
			//
			$subject_id = substr( $subject, strlen( $subject_ns ) );
			$object_id = substr( $object, strlen( $object_ns ) );
			
			//
			// Create cross reference unit.
			//
			$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
			
			//
			// Write current relationship.
			//
			$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
			$element = $edge->addChild( kIO_XML_DATA, $subject );
			$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
			$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
			$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_LEGACY );
			$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
			$element = $edge->addChild( kIO_XML_DATA, $object );
			$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
			$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
			
			//
			// Locate subject.
			//
			$subject_rec = ( substr( $subject_ns, 9, 1 ) == '3' )
						 ? $xml_33->xpath( "//TERM[@lid='$subject_id']" )[ 0 ]
						 : $xml_13->xpath( "//TERM[@lid='$subject_id']" )[ 0 ];
			$subject_nns = ( substr( $subject_ns, 9, 1 ) == '3' )
						 ? $ns_3n
						 : $ns_1n;
			$subject_syn = $subject_rec->xpath( "item[@const='kTAG_SYNONYM']" )[ 0 ];
			$subject_num = NULL;
			foreach( $subject_syn->xpath( "item" ) as $item )
			{
				if( ctype_digit( (string) $item ) )
				{
					$subject_num = (string) $item;
					break;
				}
			}
			
			//
			// Locate object.
			//
			$object_rec = ( substr( $object_ns, 9, 1 ) == '3' )
						 ? $xml_33->xpath( "//TERM[@lid='$object_id']" )[ 0 ]
						 : $xml_13->xpath( "//TERM[@lid='$object_id']" )[ 0 ];
			$object_nns = ( substr( $object_ns, 9, 1 ) == '3' )
						 ? $ns_3n
						 : $ns_1n;
			$object_syn = $object_rec->xpath( "item[@const='kTAG_SYNONYM']" )[ 0 ];
			$object_num = NULL;
			foreach( $object_syn->xpath( "item" ) as $item )
			{
				if( ctype_digit( (string) $item ) )
				{
					$object_num = (string) $item;
					break;
				}
			}
			
			//
			// Relate numeric codes.
			//
			if( ($subject_num !== NULL)
			 && ($object_num !== NULL)
			 && ($subject_num !== $object_num) )
			{
				$subject_gid = $subject_nns.kTOKEN_NAMESPACE_SEPARATOR.$subject_num;
				$object_gid = $object_nns.kTOKEN_NAMESPACE_SEPARATOR.$object_num;
				
				$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
				$element = $edge->addChild( kIO_XML_DATA, $subject_gid );
				$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
				$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
				$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_LEGACY );
				$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
				$element = $edge->addChild( kIO_XML_DATA, $object_gid );
				$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
				$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
			}
		
		} // Iterating legacy relationships.
		
		//
		// Handle subset relationsips.
		//
		$xpath = "//EDGE/item[.='".kPREDICATE_SUBSET_OF."']";
		$list = $xml_rel->xpath( $xpath );
		foreach( $list as $element )
		{
			//
			// Load subject and object.
			//
			$edge = $element->xpath( ".." )[ 0 ];
			$subject = (string) $edge->xpath(
				"item[@const='kTAG_SUBJECT']" )[ 0 ];
			$object = (string) $edge->xpath(
				"item[@const='kTAG_OBJECT']" )[ 0 ];
			
			//
			// Get namespaces.
			//
			$subject_ns = substr( $subject, 0, strlen( $subject ) - 3 );
			
			//
			// Get identifiers.
			//
			$subject_id = substr( $subject, strlen( $subject_ns ) );
			
			//
			// Create cross reference unit.
			//
			$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );
			
			//
			// Write current relationship.
			//
			$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
			$element = $edge->addChild( kIO_XML_DATA, $subject );
			$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
			$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
			$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_SUBSET_OF );
			$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
			$element = $edge->addChild( kIO_XML_DATA, $object );
			$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
			$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
			
			//
			// Locate subject.
			//
			$subject_rec = ( substr( $subject_ns, 9, 1 ) == '3' )
						 ? $xml_33->xpath( "//TERM[@lid='$subject_id']" )[ 0 ]
						 : $xml_13->xpath( "//TERM[@lid='$subject_id']" )[ 0 ];
			$subject_nns = ( substr( $subject_ns, 9, 1 ) == '3' )
						 ? $ns_3n
						 : $ns_1n;
			$subject_syn = $subject_rec->xpath( "item[@const='kTAG_SYNONYM']" )[ 0 ];
		
		} // Iterating subset relationships.
		
		//
		// Write files.
		//
		@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
		
	} // ISOGenerate31663XMLRelations.	
	
	 
	/*===================================================================================
	 *	ISOGenerate4217XML																*
	 *==================================================================================*/

	/**
	 * Generate ISO 4217 XML files
	 *
	 * This method will generate the XML ISO 4217 file.
	 *
	 * The method will load the information from the iso-codes processed files and write
	 * the following files in the provided directory:
	 *
	 * <ul>
	 *	<li><tt>ISO4217-A-letter.xml</tt>: Active letter codes.
	 *	<li><tt>ISO4217-A-numeric.xml</tt>: Active numeric codes.
	 *	<li><tt>ISO4217-H-letter.xml</tt>: Historic letter codes.
	 *	<li><tt>ISO4217-H-numeric.xml</tt>: Historic numeric codes.
	 * </ul>
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function ISOGenerate4217XML( $theDirectory )
	{
		//
		// Load XML file.
		//
		$file_in = kISO_CODES_PATH.kISO_CODES_PATH_XML.'/'.kISO_FILE_4217.'.xml';
		$xml_in = simplexml_load_file( $file_in  );
		if( $xml_in instanceof SimpleXMLElement )
		{
			//
			// Inform.
			//
			if( kOPTION_VERBOSE )
				echo( "        • ISO 4217\n" );
			
			//
			// Set default namespaces.
			//
			$ns_al = 'iso:4217:A:alpha';
			$ns_an = 'iso:4217:A:numeric';
			$ns_hl = 'iso:4217:H:alpha';
			$ns_hn = 'iso:4217:H:numeric';
			$ns_date_witdrawn = 'iso:date_withdrawn';
			
			//
			// Set target files name.
			//
			$file_al = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-A-alpha.xml';
			$file_an = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-A-numeric.xml';
			$file_hl = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-H-alpha.xml';
			$file_hn = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-H-numeric.xml';
			$file_xref = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso4217-xref.xml';
			
			//
			// Open XML structures.
			//
			$xml_al = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_an = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_hl = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_hn = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_xref = OntologyWrapper\MetadataObject::XMLRootElement();
			
			//
			// Iterate active entries.
			//
			foreach( $xml_in->{'iso_4217_entry'} as $record )
			{
				//
				// Reset cross reference data.
				//
				$idal = $idan = $unit_xref = NULL;
				
				//
				// Check letter code.
				//
				if( $record[ 'letter_code' ] !== NULL )
				{
					//
					// Save identifier.
					//
					$idal = (string) $record[ 'letter_code' ];
					$gidal = $ns_al.kTOKEN_NAMESPACE_SEPARATOR.$idal;
					
					//
					// Create unit.
					//
					$unit = $xml_al->addChild( kIO_XML_TRANS_META );
					
					//
					// Create term.
					//
					$term = $unit->addChild( kIO_XML_META_TERM );
					$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_al );
					$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $idal );
					
					//
					// Set term instance.
					//
					$element = $term->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_TERM_TYPE' );
					$element->addChild( kIO_XML_DATA, kTYPE_TERM_INSTANCE );
					
					//
					// Set term synonyms.
					//
					$element_syn_l = $term->addChild( kIO_XML_DATA );
					$element_syn_l->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
					$element_syn_l->addChild( kIO_XML_DATA, $idal );
					
					//
					// Init term names.
					//
					$names = Array();
					
					//
					// Set term label.
					//
					if( $record[ 'currency_name' ] !== NULL )
					{
						$tmp = (string) $record[ 'currency_name' ];
						$names[ kTAG_LABEL ][ 'en' ] = $tmp;
					}
					
					//
					// Collect language strings.
					//
					ISOCollectLanguageElements( $names, kISO_FILE_4217 );
					
					//
					// Set language strings.
					//
					foreach( $names as $tag => $strings )
						AddLanguageStrings( $term, $tag, $strings );
					
					//
					// Create node.
					//
					$node = $unit->addChild( kIO_XML_META_NODE );
					
					//
					// Set node type.
					//
					$element = $node->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Relate to parent.
					//
					$edge = $unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_al );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Handle numeric code.
					//
					if( $record[ 'numeric_code' ] !== NULL )
					{
						//
						// Save local identifier.
						//
						$idan = (string) $record[ 'numeric_code' ];
						$gidan = $ns_an.kTOKEN_NAMESPACE_SEPARATOR.$idan;
					
						//
						// Add term synonym.
						//
						$element_syn_l->addChild( kIO_XML_DATA, $idan );
						
						//
						// Create numeric code.
						//
						if( ! count( $xml_an->xpath( "//TERM[@lid='$idan']" ) ) )
						{
							//
							// Create unit.
							//
							$unit = $xml_an->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term = $unit->addChild( kIO_XML_META_TERM );
							$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_an );
							$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $idan );
					
							//
							// Set master.
							//
							$element = $term->addChild( kIO_XML_DATA, $gidal );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
							//
							// Set term synonyms.
							//
							$element_syn_n = $term->addChild( kIO_XML_DATA );
							$element_syn_n->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_n->addChild( kIO_XML_DATA, $idan );
							$element_syn_n->addChild( kIO_XML_DATA, $idal );
					
							//
							// Create node.
							//
							$node = $unit->addChild( kIO_XML_META_NODE );
					
							//
							// Set node type.
							//
							$element = $node->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
							$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
							//
							// Relate to parent.
							//
							$edge = $unit->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $ns_an );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // New numeric element.
						
						//
						// Relate with letter element.
						//
						$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );

						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gidan );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gidal );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );

						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gidal );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gidan );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has numeric code.
				
				} // Has letter code.
			
			} // Iterating active codes.
			
			//
			// Iterate historic entries.
			//
			foreach( $xml_in->{'historic_iso_4217_entry'} as $record )
			{
				//
				// Reset cross reference data.
				//
				$idhl = $ihan = $unit_xref = NULL;
				
				//
				// Check letter code.
				//
				if( $record[ 'letter_code' ] !== NULL )
				{
					//
					// Save identifier.
					//
					$idhl = (string) $record[ 'letter_code' ];
					$gidhl = $ns_hl.kTOKEN_NAMESPACE_SEPARATOR.$idhl;
					
					//
					// Create unit.
					//
					$unit = $xml_hl->addChild( kIO_XML_TRANS_META );
					
					//
					// Create term.
					//
					$term = $unit->addChild( kIO_XML_META_TERM );
					$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_hl );
					$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $idhl );
					
					//
					// Set term instance.
					//
					$element = $term->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_TERM_TYPE' );
					$element->addChild( kIO_XML_DATA, kTYPE_TERM_INSTANCE );
					
					//
					// Set term synonyms.
					//
					$element_syn_l = $term->addChild( kIO_XML_DATA );
					$element_syn_l->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
					$element_syn_l->addChild( kIO_XML_DATA, $idhl );
					
					//
					// Init term names.
					//
					$names = Array();
					
					//
					// Set term label.
					//
					if( $record[ 'currency_name' ] !== NULL )
					{
						$tmp = (string) $record[ 'currency_name' ];
						$names[ kTAG_LABEL ][ 'en' ] = $tmp;
					}
					
					//
					// Collect language strings.
					//
					ISOCollectLanguageElements( $names, kISO_FILE_4217 );
					
					//
					// Set language strings.
					//
					foreach( $names as $tag => $strings )
						AddLanguageStrings( $term, $tag, $strings );
					
					//
					// Set date withdrawn.
					//
					if( $record[ 'date_withdrawn' ] !== NULL )
					{
						$tmp = (string) $record[ 'date_withdrawn' ];
						$element = $term->addChild( kIO_XML_DATA, htmlspecialchars( $tmp ) );
						$element->addAttribute( kIO_XML_ATTR_REF_TAG, $ns_date_witdrawn );
					}
					
					//
					// Create node.
					//
					$node = $unit->addChild( kIO_XML_META_NODE );
					
					//
					// Set node type.
					//
					$element = $node->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Relate to parent.
					//
					$edge = $unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_hl );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Handle numeric code.
					//
					if( $record[ 'numeric_code' ] !== NULL )
					{
						//
						// Save local identifier.
						//
						$idhn = (string) $record[ 'numeric_code' ];
						$gidhn = $ns_hn.kTOKEN_NAMESPACE_SEPARATOR.$idhn;
					
						//
						// Add term synonym.
						//
						$element_syn_l->addChild( kIO_XML_DATA, $idhn );
						
						//
						// Create numeric code.
						//
						if( ! count( $xml_hn->xpath( "//TERM[@lid='$idhn']" ) ) )
						{
							//
							// Create unit.
							//
							$unit = $xml_hn->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term = $unit->addChild( kIO_XML_META_TERM );
							$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_hn );
							$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $idhn );
					
							//
							// Set master.
							//
							$element = $term->addChild( kIO_XML_DATA, $gidhl );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
							//
							// Set term synonyms.
							//
							$element_syn_n = $term->addChild( kIO_XML_DATA );
							$element_syn_n->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_n->addChild( kIO_XML_DATA, $idhn );
							$element_syn_n->addChild( kIO_XML_DATA, $idhl );
					
							//
							// Create node.
							//
							$node = $unit->addChild( kIO_XML_META_NODE );
					
							//
							// Set node type.
							//
							$element = $node->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
							$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
							//
							// Relate to parent.
							//
							$edge = $unit->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $ns_hn );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // New numeric element.
						
						//
						// Relate with letter element.
						//
						$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );

						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gidhn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gidhl );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );

						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gidhl );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gidhn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has numeric code.
				
				} // Has letter code.
			
			} // Iterating historic codes.
			
			//
			// Write files.
			//
			@unlink( $file_al ); $xml_al->asXML( $file_al );
			@unlink( $file_an ); $xml_an->asXML( $file_an );
			@unlink( $file_hl ); $xml_hl->asXML( $file_hl );
			@unlink( $file_hn ); $xml_hn->asXML( $file_hn );
			@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
		
		} // Loaded file.
		
		else
			throw new Exception
				( "Unable to load XML file [$file_in]",
				  kERROR_STATE );												// !@! ==>
		
	} // ISOGenerate4217XML.	
	
	 
	/*===================================================================================
	 *	ISOGenerate15924XML																*
	 *==================================================================================*/

	/**
	 * Generate ISO 15924 XML files
	 *
	 * This method will generate the XML ISO 15924 file.
	 *
	 * The method will load the information from the iso-codes processed files and write
	 * the following files in the provided directory:
	 *
	 * <ul>
	 *	<li><tt>ISO15924-alpha4.xml</tt>: Alpha-4 script codes.
	 *	<li><tt>ISO15924-numeric.xml</tt>: Numeric script codes.
	 * </ul>
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function ISOGenerate15924XML( $theDirectory )
	{
		//
		// Load XML file.
		//
		$file_in = kISO_CODES_PATH.kISO_CODES_PATH_XML.'/'.kISO_FILE_15924.'.xml';
		$xml_in = simplexml_load_file( $file_in  );
		if( $xml_in instanceof SimpleXMLElement )
		{
			//
			// Inform.
			//
			if( kOPTION_VERBOSE )
				echo( "        • ISO 15924\n" );
			
			//
			// Set default namespaces.
			//
			$ns_4 = 'iso:15924:alpha-4';
			$ns_n = 'iso:15924:numeric';
			
			//
			// Set target files name.
			//
			$file_4 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso15924-alpha4.xml';
			$file_n = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso15924-numeric.xml';
			$file_xref = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso15924-xref.xml';
			
			//
			// Open XML structures.
			//
			$xml_4 = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_n = OntologyWrapper\MetadataObject::XMLRootElement();
			$xml_xref = OntologyWrapper\MetadataObject::XMLRootElement();
			
			//
			// Iterate XML file.
			//
			foreach( $xml_in->{'iso_15924_entry'} as $record )
			{
				//
				// Reset cross reference data.
				//
				$idn = $unit_xref = NULL;
				
				//
				// Check letter code.
				//
				if( $record[ 'alpha_4_code' ] !== NULL )
				{
					//
					// Save identifier.
					//
					$id4 = (string) $record[ 'alpha_4_code' ];
					$gid4 = $ns_4.kTOKEN_NAMESPACE_SEPARATOR.$id4;
					
					//
					// Create unit.
					//
					$unit = $xml_4->addChild( kIO_XML_TRANS_META );
					
					//
					// Create term.
					//
					$term = $unit->addChild( kIO_XML_META_TERM );
					$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_4 );
					$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $id4 );
					
					//
					// Set term instance.
					//
					$element = $term->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_TERM_TYPE' );
					$element->addChild( kIO_XML_DATA, kTYPE_TERM_INSTANCE );
					
					//
					// Set term synonyms.
					//
					$element_syn_4 = $term->addChild( kIO_XML_DATA );
					$element_syn_4->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
					$element_syn_4->addChild( kIO_XML_DATA, $id4 );
					
					//
					// Init term names.
					//
					$names = Array();
					
					//
					// Set term label.
					//
					if( $record[ 'name' ] !== NULL )
					{
						$tmp = (string) $record[ 'name' ];
						$names[ kTAG_LABEL ][ 'en' ] = $tmp;
					}
					
					//
					// Collect language strings.
					//
					ISOCollectLanguageElements( $names, kISO_FILE_15924 );
					
					//
					// Set language strings.
					//
					foreach( $names as $tag => $strings )
						AddLanguageStrings( $term, $tag, $strings );
					
					//
					// Create node.
					//
					$node = $unit->addChild( kIO_XML_META_NODE );
					
					//
					// Set node type.
					//
					$element = $node->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
					$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
					//
					// Relate to parent.
					//
					$edge = $unit->addChild( kIO_XML_META_EDGE );
					$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
					$element = $edge->addChild( kIO_XML_DATA, $ns_4 );
					$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
					$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					//
					// Handle numeric code.
					//
					if( $record[ 'numeric_code' ] !== NULL )
					{
						//
						// Save local identifier.
						//
						$idn = (string) $record[ 'numeric_code' ];
						$gidn = $ns_n.kTOKEN_NAMESPACE_SEPARATOR.$idn;
					
						//
						// Add term synonym.
						//
						$element_syn_4->addChild( kIO_XML_DATA, $idn );
						
						//
						// Create numeric code.
						//
						if( ! count( $xml_n->xpath( "//TERM[@lid='$idn']" ) ) )
						{
							//
							// Create unit.
							//
							$unit = $xml_n->addChild( kIO_XML_TRANS_META );
					
							//
							// Create term.
							//
							$term = $unit->addChild( kIO_XML_META_TERM );
							$term->addAttribute( kIO_XML_ATTR_NAMESPACE, $ns_n );
							$term->addAttribute( kIO_XML_ATTR_ID_LOCAL, $idn );
					
							//
							// Set master.
							//
							$element = $term->addChild( kIO_XML_DATA, $gid4 );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_MASTER' );
					
							//
							// Set term synonyms.
							//
							$element_syn_n = $term->addChild( kIO_XML_DATA );
							$element_syn_n->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SYNONYM' );
							$element_syn_n->addChild( kIO_XML_DATA, $idn );
							$element_syn_n->addChild( kIO_XML_DATA, $id4 );
					
							//
							// Create node.
							//
							$node = $unit->addChild( kIO_XML_META_NODE );
					
							//
							// Set node type.
							//
							$element = $node->addChild( kIO_XML_DATA );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_NODE_TYPE' );
							$item = $element->addChild( kIO_XML_DATA, kTYPE_NODE_ENUMERATION );
					
							//
							// Relate to parent.
							//
							$edge = $unit->addChild( kIO_XML_META_EDGE );
							$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_ENUM_OF );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
							$element = $edge->addChild( kIO_XML_DATA, $ns_n );
							$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
							$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						
						} // New numeric element.
						
						//
						// Relate with alpha-4 element.
						//
						$unit_xref = $xml_xref->addChild( kIO_XML_TRANS_META );

						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gid4 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );

						$edge = $unit_xref->addChild( kIO_XML_META_EDGE );
						$element = $edge->addChild( kIO_XML_DATA, $gid4 );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_SUBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
						$element = $edge->addChild( kIO_XML_DATA, kPREDICATE_XREF_EXACT );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_PREDICATE' );
						$element = $edge->addChild( kIO_XML_DATA, $gidn );
						$element->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_OBJECT' );
						$element->addAttribute( kIO_XML_ATTR_REF_NODE, kIO_XML_ATTR_REF_TERM );
					
					} // Has numeric code.
				
				} // Has letter code.
			
			} // Iterating active codes.
			
			//
			// Write files.
			//
			@unlink( $file_4 ); $xml_4->asXML( $file_4 );
			@unlink( $file_n ); $xml_n->asXML( $file_n );
			@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
		
		} // Loaded file.
		
		else
			throw new Exception
				( "Unable to load XML file [$file_in]",
				  kERROR_STATE );												// !@! ==>
		
	} // ISOGenerate15924XML.	

	 
	/*===================================================================================
	 *	ISOCollectLanguageElements														*
	 *==================================================================================*/

	/**
	 * Collect language elements
	 *
	 * This method will iterate all language files and add the corresponding language
	 * strings to the provided list.
	 *
	 * The method will update by reference the provided list.
	 *
	 * @param reference			   &$theStrings			List of strings.
	 * @param string				$theFile			File body name.
	 */
	function ISOCollectLanguageElements( &$theStrings, $theFile )
	{
		//
		// Check if names were found.
		//
		if( count( $theStrings ) )
		{
			//
			// Iterate languages.
			//
			foreach( $_SESSION[ kISO_LANGUAGES ] as $language )
			{
				//
				// Check language file.
				//
				$file_path = $_SESSION[ kISO_FILE_PO_DIR ]."/$language/$theFile.serial";
				if( is_file( $file_path ) )
				{
					//
					// Instantiate keys array.
					//
					$keys = unserialize( file_get_contents( $file_path ) );
				
					//
					// Iterate attributes.
					//
					foreach( array_keys( $theStrings ) as $tag )
					{
						if( $theStrings[ $tag ] )
						{
							//
							// Determine key.
							//
							if( array_key_exists( 'en', $theStrings[ $tag ] ) )
								$key = $theStrings[ $tag ][ 'en' ];
							elseif( array_key_exists( 0, $theStrings[ $tag ] ) )
								$key = $theStrings[ $tag ][ 0 ];
							else
								continue;
							if( array_key_exists( $key, $keys ) )
								$theStrings[ $tag ][ $language ] = $keys[ $key ];
						}
					}
			
				} // Language file exists.
		
			} // Iterating languages.
		
		} // Found names.
		
	} // ISOCollectLanguageElements.

		

/*=======================================================================================
 *																						*
 *						PROTECTED WBI XML FILE GENERATION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	WBIGenerateXMLRelations															*
	 *==================================================================================*/

	/**
	 * <h4>Generate WBI XML relationships</h4>
	 *
	 * This method will update the WBI XML cross references file by adding:
	 *
	 * <ul>
	 *	<li><tt>wbi:group</tt>: Add the WBI group tag to all countries.
	 * </ul>
	 *
	 * The method will update the WBI-xref.xml file.
	 *
	 * @param string				$theDirectory		Files container directory.
	 *
	 * @throws Exception
	 */
	function WBIGenerateXMLRelations( $theDirectory )
	{
		//
		// Inform.
		//
		if( kOPTION_VERBOSE )
			echo( "        • WBI relationships\n" );
	
		//
		// Set default namespaces.
		//
		$ns_2 = 'iso:3166:1:alpha-2';
		$ns_3 = 'iso:3166:1:alpha-3';
		$ns_33 = 'iso:3166:3:alpha-3';
		$ns_n = 'iso:3166:1:numeric';
		$ns_3n = 'iso:3166:3:numeric';
		
		//
		// Set target files name.
		//
		$file_in = $theDirectory."/".kDIR_STANDARDS_WBI.'/WBI-RELATIONSHIPS.xml';
		$file_3 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-1-alpha3.xml';
		$file_33 = $theDirectory."/".kDIR_STANDARDS_ISO.'/iso3166-3-alpha3.xml';
		$file_xref = $theDirectory."/".kDIR_STANDARDS_WBI.'/wbi-xref.xml';
		
		//
		// Open XML structures.
		//
		$xml_in = simplexml_load_file( $file_in );
		$xml_3 = simplexml_load_file( $file_3 );
		$xml_33 = simplexml_load_file( $file_33 );
		$xml_xref = OntologyWrapper\MetadataObject::XMLRootElement();
		
		//
		// Iterate terms.
		//
		$terms = $xml_in->xpath( "//TERM" );
		foreach( $terms as $rec_term )
		{
			//
			// Get version.
			//
			$version = $rec_term->xpath( "item[@tag='wbi:version']" );
			$version = ( count( $version ) )
					 ? (string) $version[ 0 ]
					 : NULL;
			
			//
			// Get group items.
			//
			$items = Array();
			foreach( $rec_term->xpath( "item[@tag='wbi:group']" )[ 0 ] as $item )
				$items[ (string) $item ] = (string) $item;
			
			//
			// Get alpha-3 identifiers.
			//
			$gid3 = (string) $rec_term[ kIO_XML_ATTR_UPDATE ];
			$id3 = substr( $gid3, strlen( $gid3 ) - 3 );
			
			//
			// Determine standard.
			//
			if( substr( $gid3, 9, 1 ) == '1' )
			{
				$xml = $xml_3;
				$ns = $ns_3;
			}
			else
			{
				$xml = $xml_33;
				$ns = $ns_33;
			}
			
			//
			// Locate country synonyms.
			//
			$country_rec = $xml->xpath( "//TERM[@lid='$id3']" )[ 0 ];
			$syns = $country_rec->xpath( "item[@const='kTAG_SYNONYM']" )[ 0 ];
			
			//
			// Build items list.
			//
			$list = Array();
			foreach( $syns as $syn )
			{
				//
				// Normalise.
				//
				$syn = (string) $syn;
				
				//
				// Handle part 1.
				//
				if( substr( $gid3, 9, 1 ) == '1' )
					$list[] = ( $id3 != $syn )
							? ( ( ctype_digit( $syn ) )
							  ? ($ns_n.kTOKEN_NAMESPACE_SEPARATOR.$syn)
							  : ($ns_2.kTOKEN_NAMESPACE_SEPARATOR.$syn) )
							: ($ns_3.kTOKEN_NAMESPACE_SEPARATOR.$syn);
				
				//
				// Handle part 3.
				//
				else
				{
					if( $id3 == $syn )
						$list[] = $ns_33.kTOKEN_NAMESPACE_SEPARATOR.$syn;
					elseif( ctype_digit( $syn ) )
						$list[] = $ns_3n.kTOKEN_NAMESPACE_SEPARATOR.$syn;
				
				} // Part 3.
			
			} // Creating aliases.
			
			//
			// Create cross reference unit.
			//
			$unit = $xml_xref->addChild( kIO_XML_TRANS_META );
			
			//
			// Iterate enumerations.
			//
			foreach( $list as $enum )
			{
				//
				// Create term.
				//
				$term = $unit->addChild( kIO_XML_META_TERM );
				$term->addAttribute( kIO_XML_ATTR_UPDATE, $enum );
				
				//
				// Set version.
				//
				if( $version !== NULL )
				{
					$element = $term->addChild( kIO_XML_DATA, htmlspecialchars( $version ) );
					$element->addAttribute( kIO_XML_ATTR_REF_TAG, 'wbi:version' );
				
				} // Has version.
				
				//
				// Set categories.
				//
				if( count( $items ) )
				{
					$element = $term->addChild( kIO_XML_DATA );
					$element->addAttribute( kIO_XML_ATTR_REF_TAG, 'wbi:group' );
					
					foreach( $items as $item )
						$element->addChild( kIO_XML_DATA, $item );
				
				} // Has categories.
			
			} // Iterating enumerations list.
			
		} // Iterating terms.
		
		//
		// Write files.
		//
		@unlink( $file_xref ); $xml_xref->asXML( $file_xref );
		
	} // WBIGenerateXMLRelations.	

		

/*=======================================================================================
 *																						*
 *									XML UTILITY FUNCTIONS								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	AddLanguageStrings																*
	 *==================================================================================*/

	/**
	 * Add language strings
	 *
	 * This function will add the provided language strings to the provided XML element.
	 * The function expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theElement</em>: The XML element that will receive the strings.
	 *	<li><b>$theTag</em>: The strings list tag, as a sequence number.
	 *	<li><b>$theStrings</em>: The array of strings indexed by language.
	 * </ul>
	 *
	 * @throws Exception
	 */
	function AddLanguageStrings( SimpleXMLElement $theElement, $theTag, &$theStrings )
	{
		//
		// Create language strings list element.
		//
		$element = $theElement->addChild( kIO_XML_DATA );
		$element->addAttribute( kIO_XML_ATTR_REF_TAG, $theTag );
		
		//
		// Iterate strings.
		//
		foreach( $theStrings as $language => $string )
		{
			//
			// Create string list element.
			//
			$item = $element->addChild( kIO_XML_DATA );
			
			//
			// Create language item.
			//
			$tmp = $item->addChild( kIO_XML_DATA, $language );
			$tmp->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_LANGUAGE' );
			
			//
			// Create language item.
			//
			$tmp = $item->addChild( kIO_XML_DATA, htmlspecialchars( $string ) );
			$tmp->addAttribute( kIO_XML_ATTR_QUAL_CONST, 'kTAG_TEXT' );
		
		} // Iterating language strings.

	} // AddLanguageStrings.


?>
