<?php

/**
 * Convert OBO Unit Ontology to XML.
 *
 * This file contains routines to generate XML files based on the Unit Ontology in OBO which
 * are compatible with the current library.
 *
 * The script expects the following parameters:
 *
 * <ul>
 *	<li><em>Destination directory</em>: The destination directory path.
 * </ul>
 *
 *	@package	OntologyWrapper
 *	@subpackage	Utilities
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 21/11/2014
 */

/*=======================================================================================
 *																						*
 *									UnitOntology2XML.php								*
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

//
// Output file name.
//
define( "kOUT_FILE_NAME",	"Units.xml" );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc < 2 )
	exit( "Usage: <script.php> <destination directory path>" );						// ==>

//
// Normalise output path.
//
if( substr( $argv[ 1 ], strlen( $argv[ 1 ] ) - 1, 1 ) != '/' )
	$argv[ 1 ] .= '/';

//
// Inform.
//
echo( "\n==> Converting Unit Ontology file.\n" );

/*=======================================================================================
 *	TRY																					*
 *======================================================================================*/

//
// Try.
//
try
{
	//
	// Open files.
	//
	$input
		= new SplFileObject(
			'http://unit-ontology.googlecode.com/svn/trunk/unit.obo', "r" );

	//
	// Inform.
	//
	echo( "\n==> Converting OBO file.\n" );
	echo( "    Input:  ".$input->getRealPath()."\n" );
	echo( "    Output: ".$argv[ 1 ].kOUT_FILE_NAME."\n" );
	
	//
	// Init XML.
	//
	$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!--
	BREEDING EVENT ATTRIBUTES
	BreedingEventAttributes.xml
-->
<METADATA
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:noNamespaceSchemaLocation="https://gist.githubusercontent.com/milko/f2dcea3c3a94f69bb0bb/raw/715d8fd15a66ad25466c9efc4f767fd31fc9a21a/Dictionary.xsd">
	
	<!-- Namespace -->
	
	<!-- UO -->
	<META>
		<TERM lid="UO">
			<item const="kTAG_LABEL">
				<item>
					<item const="kTAG_LANGUAGE">en</item>
					<item const="kTAG_TEXT">Unit ontology</item>
				</item>
			</item>
		</TERM>
	</META>
	
	<!-- Terms -->


EOT;
	
	//
	// Get terms.
	//
	$terms = getTerms( $input );
	
	//
	// Load terms.
	//
	foreach( $terms as $tag => $term )
	{
		//
		// Parse identifier.
		//
		$tmp = explode( ':', $term[ 'id' ] );
		$ns = $tmp[ 0 ];
		$id = $tmp[ 1 ];
		
		//
		// Open block.
		//
		$xml .= ("\t<!-- $ns:$id -->\n");
		$xml .= ("\t<META>\n");
		
		//
		// Write term.
		//
		$xml .= ("\t\t<TERM ns=\"$ns\" lid=\"$id\">\n");
		$xml .= ("\t\t\t<item const=\"kTAG_LABEL\">\n");
		$xml .= ("\t\t\t\t<item>\n");
		$xml .= ("\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n");
		$xml .= ("\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[".$term[ 'name' ]."]]></item>\n");
		$xml .= ("\t\t\t\t</item>\n");
		$xml .= ("\t\t\t</item>\n");
		if( array_key_exists( 'def', $term ) )
		{
			$xml .= ("\t\t\t<item const=\"kTAG_DEFINITION\">\n");
			$xml .= ("\t\t\t\t<item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[".$term[ 'def' ]."]]></item>\n");
			$xml .= ("\t\t\t\t</item>\n");
			$xml .= ("\t\t\t</item>\n");
		}
		if( array_key_exists( 'synonym', $term ) )
		{
			$xml .= ("\t\t\t<item const=\"kTAG_SYNONYM\">\n");
			if( ! is_array( $term[ 'synonym' ] ) )
				$term[ 'synonym' ] = array( $term[ 'synonym' ] );
			foreach( $term[ 'synonym' ] as $tmp )
				$xml .= ("\t\t\t\t<item>$tmp</item>\n");
			$xml .= ("\t\t\t</item>\n");
		}
		if( array_key_exists( 'creation_date', $term ) )
			$xml .= ("\t\t\t<item const=\"kTAG_VERSION\">".$term[ 'creation_date' ]."</item>\n");
		$xml .= ("\t\t</TERM>\n");
		
		//
		// Handle unit type.
		//
		if( $tag == 'UO:0000000' )
		{
			$xml .= ("\t\t<TAG>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_TERMS\">\n");
			$xml .= ("\t\t\t\t<item>$ns:$id</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_DATA_TYPE\">:type:enum</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_DATA_KIND\">\n");
			$xml .= ("\t\t\t\t<item>:type:categorical</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_DESCRIPTION\">\n");
			$xml .= ("\t\t\t\t<item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_LANGUAGE\">en</item>\n");
			$xml .= ("\t\t\t\t\t<item const=\"kTAG_TEXT\"><![CDATA[This attribute holds the enumerated value describing the unit.]]></item>\n");
			$xml .= ("\t\t\t\t</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t</TAG>\n");
			$xml .= ("\t\t<NODE term=\"$ns:$id\">\n");
			$xml .= ("\t\t\t<item const=\"kTAG_NODE_TYPE\">\n");
			$xml .= ("\t\t\t\t<item>:type:node:type</item>\n");
			$xml .= ("\t\t\t\t<item>:kind:enumerated-node</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t</NODE>\n");
			$xml .= ("\t\t<NODE tag=\"$ns:$id\">\n");
			$xml .= ("\t\t\t<item const=\"kTAG_NODE_TYPE\">\n");
			$xml .= ("\t\t\t\t<item>:kind:property-node</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t</NODE>\n");
			$xml .= ("\t\t<EDGE>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_SUBJECT\" node=\"term\">$ns:$id</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:TYPE-OF</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_OBJECT\" node=\"tag\">$ns:$id</item>\n");
			$xml .= ("\t\t</EDGE>\n");
		
		} // Type term.
		
		//
		// Handle enumeration.
		//
		elseif( array_key_exists( 'subset', $term ) )
		{
			//
			// Handle enumneration.
			//
			if( $term[ 'subset' ] == 'unit_slim' )
			{
				$xml .= ("\t\t<NODE>\n");
				$xml .= ("\t\t\t<item const=\"kTAG_NODE_TYPE\">\n");
				$xml .= ("\t\t\t\t<item>:type:node:enumeration</item>\n");
				$xml .= ("\t\t\t</item>\n");
				$xml .= ("\t\t</NODE>\n");
			}
			
			//
			// Handle group.
			//
			else
				$xml .= ("\t\t<NODE />\n");
		
		} // Parsing subset.
		
		//
		// No subset: assume enumeration.
		//
		else
		{
			$xml .= ("\t\t<NODE>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_NODE_TYPE\">\n");
			$xml .= ("\t\t\t\t<item>:type:node:enumeration</item>\n");
			$xml .= ("\t\t\t</item>\n");
			$xml .= ("\t\t</NODE>\n");
		
		} // No subset.
		
		//
		// Close block.
		//
		$xml .= ("\t</META>\n\n");
	
	} // Loading terms.
	
	//
	// Load custom units.
	//
	$xml .= <<<EOT
	<!-- UO:mg/g -->
	<META>
		<TERM ns="UO" lid="mg/g">
			<item const="kTAG_LABEL">
				<item>
					<item const="kTAG_LANGUAGE">en</item>
					<item const="kTAG_TEXT"><![CDATA[milligram per gram]]></item>
				</item>
			</item>
			<item const="kTAG_DEFINITION">
				<item>
					<item const="kTAG_LANGUAGE">en</item>
					<item const="kTAG_TEXT"><![CDATA[A mass unit density which is equal to mass of an object in milligrams divided by the volume in grams.]]></item>
				</item>
			</item>
			<item const="kTAG_SYNONYM">
				<item>mg/g</item>
			</item>
		</TERM>
		<NODE>
			<item const="kTAG_NODE_TYPE">
				<item>:type:node:enumeration</item>
			</item>
		</NODE>
	</META>
	
	<!-- UO:ug/g -->
	<META>
		<TERM ns="UO" lid="ug/g">
			<item const="kTAG_LABEL">
				<item>
					<item const="kTAG_LANGUAGE">en</item>
					<item const="kTAG_TEXT"><![CDATA[microgram per gram]]></item>
				</item>
			</item>
			<item const="kTAG_DEFINITION">
				<item>
					<item const="kTAG_LANGUAGE">en</item>
					<item const="kTAG_TEXT"><![CDATA[A mass unit density which is equal to mass of an object in micrograms divided by the volume in grams.]]></item>
				</item>
			</item>
			<item const="kTAG_SYNONYM">
				<item>ug/g</item>
			</item>
		</TERM>
		<NODE>
			<item const="kTAG_NODE_TYPE">
				<item>:type:node:enumeration</item>
			</item>
		</NODE>
	</META>
	
	<!-- UO:g/plot -->
	<META>
		<TERM ns="UO" lid="g/plot">
			<item const="kTAG_LABEL">
				<item>
					<item const="kTAG_LANGUAGE">en</item>
					<item const="kTAG_TEXT"><![CDATA[grams per plot]]></item>
				</item>
			</item>
			<item const="kTAG_SYNONYM">
				<item>g/plot</item>
			</item>
		</TERM>
		<NODE>
			<item const="kTAG_NODE_TYPE">
				<item>:type:node:enumeration</item>
			</item>
		</NODE>
	</META>
EOT;
	
	//
	// Open relationships block.
	//
	$xml .= ("\n\n\t<!-- Relationships -->\n\n");
	$xml .= ("\t<META>\n");
	
	//
	// Load relationships.
	//
	foreach( $terms as $tag => $term )
	{
		//
		// Select only related.
		//
		if( array_key_exists( 'is_a', $term ) )
		{
			//
			// Parse identifier.
			//
			$tmp = explode( ':', $term[ 'id' ] );
			$ns = $tmp[ 0 ];
			$id = $tmp[ 1 ];
			$rel = $term[ 'is_a' ];
			
			//
			// Write edge.
			//
			$xml .= ("\t\t<!-- $ns:$id -->\n");
			$xml .= ("\t\t<EDGE>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_SUBJECT\" node=\"term\">$ns:$id</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_PREDICATE\">:predicate:ENUM-OF</item>\n");
			$xml .= ("\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">$rel</item>\n");
			$xml .= ("\t\t</EDGE>\n");
		
		} // Is related.
	
	} // Iterating terms.
	
	//
	// Close relationships block.
	//
	$xml .= ("\t</META>\n");
	
	//
	// Close root.
	//
	$xml .= ("</METADATA>\n");
	
	//
	// Write XML file.
	//
	file_put_contents( $argv[ 1 ].kOUT_FILE_NAME, $xml );

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

} // FINALLY BLOCK.

/*=======================================================================================
 *																						*
 *										FUNCTIONS										*
 *																						*
 *======================================================================================*/

	/**
	 * Get terms.
	 *
	 * This function will load all terms in the provided OBO file and return an array.
	 *
	 * @param SplFileObject			$theFile			Input file.
	 *
	 * @return array				The terms list.
	 */
	function getTerms( SplFileObject $theFile )
	{
		//
		// Init local storage.
		//
		$terms = Array();
		
		//
		// Locate term.
		//
		while( (! $theFile->eof())
			&& (($block = trim( $theFile->fgets() )) != '[Term]') );
		
		//
		// Iterate file.
		//
		while( ! $theFile->eof() )
		{
			//
			// Init term.
			//
			$term = Array();
			
			//
			// Load term.
			//
			while( (! $theFile->eof())
				&& (($block = trim( $theFile->fgets() )) != '[Term]') )
			{
				//
				// Skip empty lines.
				//
				if( strlen( $block ) )
				{
					//
					// Parse line.
					//
					if( preg_match( "/^([a-zA-Z_]+):(.+)/", $block, $matches ) )
					{
						//
						// Normalise matches.
						//
						$matches[ 1 ] = trim( $matches[ 1 ] );
						$matches[ 2 ] = trim( $matches[ 2 ] );
						
						//
						// Match identifier.
						//
						if( $matches[ 1 ] == 'id' )
							$index = $matches[ 2 ];
						
						//
						// Normalise value.
						//
						switch( $matches[ 1 ] )
						{
							case 'id':
								$index = $matches[ 2 ];
								break;
							
							case 'def':
								preg_match( "/^\"(.+)\"/", $matches[ 2 ], $prop );
								$matches[ 2 ] = $prop[ 1 ];
								break;
							
							case 'is_a':
								preg_match( "/^(UO:[0-9]+)/", $matches[ 2 ], $prop );
								$matches[ 2 ] = $prop[ 1 ];
								break;
							
							case 'synonym':
								preg_match( "/^\"(.+)\"/", $matches[ 2 ], $prop );
								$matches[ 2 ] = $prop[ 1 ];
								break;
						}
						
						//
						// Set new property.
						//
						if( ! array_key_exists( $matches[ 1 ], $term ) )
							$term[ $matches[ 1 ] ] = $matches[ 2 ];
						
						//
						// Add existing property.
						//
						elseif( $matches[ 1 ] != 'is_a' )
						{
							//
							// Convert to array.
							//
							if( ! is_array( $term[ $matches[ 1 ] ] ) )
								$term[ $matches[ 1 ] ] = array( $term[ $matches[ 1 ] ] );
							
							//
							// Add element.
							//
							$term[ $matches[ 1 ] ][] = $matches[ 2 ];
						
						} // Existing element.
					
					} // Matched property.
				
				} // Not empty.
			
			} // In term block;
			
			//
			// Add to terms.
			//
			$terms[ $index ] = $term;
		
		} // Iterating file.
		
		return $terms;																// ==>

	} // getTerms.


?>
