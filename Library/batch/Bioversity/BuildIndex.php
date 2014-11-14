<?php

/**
 * Build units indexes.
 *
 * This file contains a script to build unit indexes.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 02/10/2014
 */

/*=======================================================================================
 *																						*
 *									BuildIndex.php										*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

//
// local includes.
//
require_once( 'local.inc.php' );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

//
// MAIN.
//
try
{
	//
	// Check arguments.
	//
	if( $argc < 3 )
		exit( "usage: php -f BuildIndex.php "
			 ."<database> "
			 ."<tag native identifier> "
			 ."[background build (Y/N)]\n" );										// ==>
	
	//
	// Get arguments.
	//
	$database = $argv[ 1 ];
	$tag = $argv[ 2 ];
	$background = TRUE;
	if( $argc > 1 )
		$background = ( ($argv[ 3 ] == '1') 
					 || (strtolower( $argv[ 3 ] ) == 'y')
					 || (strtolower( $argv[ 3 ] ) == 'Y')
					 || (strtolower( $argv[ 3 ] ) == 'true') );
	
	//
	// Parse database.
	//
	$parts = parse_url( $database );
	
	//
	// Inform.
	//
	echo( "\n"
		 ."Tag:              $tag\n"
		 ."Host:             ".$parts[ 'host' ]."\n"
		 ."Port:             ".$parts[ 'port' ]."\n"
		 ."Database:         ".substr( $parts[ 'path' ], 1 )."\n"
		 ."Background build: "
		 .( ( $background ) ? "Yes" : "No" )
		 ."\n" );
	
	//
	// Instantiate data dictionary.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( 'localhost', 11211 ) ) );

	//
	// Set databases.
	//
	$meta = $wrapper->Metadata(
		new OntologyWrapper\MongoDatabase( $database ) );
	$wrapper->Entities(
		new OntologyWrapper\MongoDatabase( $database ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase( $database ) );

	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Check tag.
	//
	if( substr( $tag, 0, 1 ) == kTOKEN_TAG_PREFIX )
	{
		if( $wrapper->getObject( $tag, FALSE ) === NULL )
			exit( "Unknown tag [$tag]\n" );											// ==>
	}
	else
	{
		$tmp = $wrapper->getSerial( $tag, FALSE );
		if( $tmp === NULL )
			exit( "Unknown tag [$tag]\n" );											// ==>
		$tag = $tmp;
	}
	
	//
	// Build index.
	//
	$indexes = OntologyWrapper\UnitObject::CreateIndex( $wrapper, $tag );
	if( is_array( $indexes ) )
		echo( implode( ', ', $indexes )."\n" );
	else
		echo( "No data\n" );
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
