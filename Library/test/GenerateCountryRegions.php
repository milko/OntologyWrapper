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
 *								GenerateCountryRegions.php								*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

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
// Init local storage.
//
$countries = Array();
$xml_out = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<!--
	ISO regions
	ISO3166-regions.xml
-->
<METADATA
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:noNamespaceSchemaLocation="http://resources.grinfo.net/Schema/Dictionary.xsd">
	<META>
    
	    <!-- LOCATION REGIONS & COUNTRIES -->


EOT;
 
//
// Try.
//
try
{
	//
	// Connect.
	//
	$m = new MongoClient( 'mongodb://localhost:27017' );
	$d = $m->selectDB( 'PGRDG' );
	$c = $d->selectCollection( '_terms' );
	
	//
	// Load xml.
	//
	$xml = new \SimpleXMLElement( '/Library/WebServer/Library/OntologyWrapper/Library/snippets/CountryRegions.xml', NULL, TRUE );
	foreach( $xml->{'entry'} as $entry )
	{
		//
		// Get code and region.
		//
		$code = (string) $entry->code;
		$region = (string) $entry->region;
		
		//
		// Try valid code.
		//
		$iso = "iso:3166:1:alpha-3:$code";
		$criteria = array( kTAG_NID => $iso );
		$rs = $c->find( $criteria );
		
		//
		// Try legacy code.
		if( ! $rs->count() )
		{
			$iso = "iso:3166:3:alpha-3:$code";
			$criteria = array( kTAG_NID => $iso );
			$rs = $c->find( $criteria );
			if( ! $rs->count() )
				var_dump( "Invalid code [$code]" );
			else	
				$countries[ $iso ] = $region;
		}
		else	
			$countries[ $iso ] = $region;
		
	} // Iterating XML file.
	
	//
	// Locate all valid codes.
	//
	$rs = $c->find( array( kTAG_NAMESPACE => 'iso:3166:1:alpha-3' ) );
	foreach( $rs as $record )
	{
		if( ! array_key_exists( $record[ kTAG_NID ], $countries ) )
		{
			switch( $record[ kTAG_ID_LOCAL ] )
			{
				case 'BES':
					$countries[ $record[ kTAG_NID ] ] = '029';
					break;
				
				case 'SSD':
					$countries[ $record[ kTAG_NID ] ] = '015';
					break;
				
				default:
					var_dump( $record[ kTAG_NID ] );
					break;
			}
		}
	}
	
	//
	// Locate all legacy codes.
	//
	$rs = $c->find( array( kTAG_NAMESPACE => 'iso:3166:3:alpha-3' ) );
	foreach( $rs as $record )
	{
		if( ! array_key_exists( $record[ kTAG_NID ], $countries ) )
		{
			switch( $record[ kTAG_ID_LOCAL ] )
			{
				case 'ETH':
					$countries[ $record[ kTAG_NID ] ] = '014';
					break;
				
				case 'ATF':
					break;
				
				case 'DEU':
					$countries[ $record[ kTAG_NID ] ] = '155';
					break;
				
				case 'PAN':
					$countries[ $record[ kTAG_NID ] ] = '013';
					break;
				
				case 'KNA':
					$countries[ $record[ kTAG_NID ] ] = '029';
					break;
				
				case 'ESH':
					$countries[ $record[ kTAG_NID ] ] = '011';
					break;
				
				case 'VAT':
					$countries[ $record[ kTAG_NID ] ] = '039';
					break;
				
				case 'YEM':
					$countries[ $record[ kTAG_NID ] ] = '145';
					break;
				
				default:
					var_dump( $record[ kTAG_NID ] );
					break;
			}
		}
	}
	
	//
	// Iterate countries.
	//
	foreach( $countries as $country => $region )
	{
		//
		// Convert namespace.
		//
		if( substr( $country, 0, 18 ) == 'iso:3166:1:alpha-3' )
			$country = 'iso:3166:location:1'.substr( $country, 18 );
		else
			$country = 'iso:3166:location:3'.substr( $country, 18 );
		
		//
		// Open edge.
		//
		$xml_out .= ("\t\t<EDGE>\r\t\t\t<item const=\"kTAG_SUBJECT\" node=\"term\">"
					."$country</item>\r"
					."\t\t\t<item cont=\"kTAG_PREDICATE\">:predicate:ENUM-OF</item>\r"
					."\t\t\t<item const=\"kTAG_OBJECT\" node=\"term\">"
					."iso:3166:location:$region</item>\r"
					."\t\t</EDGE>\r");
	}
	
	//
	// Close XML.
	//
	$xml_out .= "\t</META>\r</METADATA>\r";
	
	//
	// Write file.
	//
	file_put_contents( 'ISO3166-regions.xml', $xml_out );
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
