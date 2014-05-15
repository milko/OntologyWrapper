<?php

/**
 * Default tags table generator.
 *
 * This file contains routines to generate the default tags table.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 15/05/2014
 */

/*=======================================================================================
 *																						*
 *								GenerateDefaultTagsXML.php								*
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
// Token definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tokens.inc.php" );


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/
 
//
// Try.
//
try
{
	//
	// Init local storage.
	//
	$count = 0;
	$list = <<<EOT
	/**
	 * Default tags table.
	 *
	 * This static member holds the type and kind information regarding all default tags.
	 */
	public static \$sDefaultTags = array
	(

EOT;
	
	//
	// Load xml.
	//
	$xml = new \SimpleXMLElement( kPATH_STANDARDS_ROOT."/default/Tags.xml", NULL, TRUE );
	foreach( $xml->{'META'} as $block )
	{
		//
		// Iterate tags.
		//
		foreach( $block->{'TAG'} as $tag )
		{
			//
			// Init loop storage.
			//
			$element = '';
			$terms = Array();
			$type = NULL;
			$kind = Array();
			$min = NULL;
			$max = NULL;
			$pattern = NULL;
			
			//
			// Close previous.
			//
			if( $count++ )
				$element .= "\t\t),\n";
		
			//
			// Write index.
			//
			$element .= ("\t\t$count => array\n\t\t(\n");
			
			//
			// Iterate items.
			//
			foreach( $tag->{'item'} as $item )
			{
				//
				// Handle terms.
				//
				if( isset( $item[ 'const' ] )
				 && ($item[ 'const' ] == 'kTAG_TERMS') )
				{
					foreach( $item->item as $tmp )
						$terms[] = (string) $tmp;
				}
			
				//
				// Handle type.
				//
				if( isset( $item[ 'const' ] )
				 && ($item[ 'const' ] == 'kTAG_DATA_TYPE') )
					$type = (string) $item;
			
				//
				// Handle kind.
				//
				if( isset( $item[ 'const' ] )
				 && ($item[ 'const' ] == 'kTAG_DATA_KIND') )
				{
					foreach( $item->item as $tmp )
						$kind[] = "'".(string) $tmp."'";
				}
			
				//
				// Handle minimum range.
				//
				if( isset( $item[ 'const' ] )
				 && ($item[ 'const' ] == 'kTAG_MIN_RANGE') )
					$min = (float) (string) $item;
			
				//
				// Handle maximum range.
				//
				if( isset( $item[ 'const' ] )
				 && ($item[ 'const' ] == 'kTAG_MAX_RANGE') )
					$max = (float) (string) $item;
			
				//
				// Handle data pattern.
				//
				if( isset( $item[ 'const' ] )
				 && ($item[ 'const' ] == 'kTAG_PATTERN') )
					$pattern = (string) $item;
			
			} // Iterating tag items.
		
			//
			// Write identifier.
			//
			$id = implode( kTOKEN_INDEX_SEPARATOR, $terms );
			$element .= ("\t\t\tkTAG_NID\t=> '$id',\n");
		
			//
			// Write type.
			//
			$element .= ("\t\t\tkTAG_DATA_TYPE\t=> '$type',\n");
		
			//
			// Write kind.
			//
			$element .= ("\t\t\tkTAG_DATA_KIND\t=> array( ".implode( ', ', $kind )." )");
		
			//
			// Write minimum.
			//
			if( $min !== NULL )
				$element .= (",\n\t\t\tkTAG_MIN_RANGE\t=> $min");
		
			//
			// Write maximum.
			//
			if( $max !== NULL )
				$element .= (",\n\t\t\tkTAG_MAX_RANGE\t=> $max");
		
			//
			// Write pattern.
			//
			if( $pattern !== NULL )
				$element .= (",\n\t\t\tkTAG_PATTERN\t=> '$pattern'");
		
			//
			// Add to list.
			//
			$list .= "\n$element";
		
		} // Iterating tags.
		
	} // Iterating XML file.
	
	//
	// Close table.
	//
	$list .= "\n\t\t)\n\t);\n";
	
	echo( "$list\n" );
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
}

echo( "\nDone!\n" );

?>
