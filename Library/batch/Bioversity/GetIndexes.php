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
 *									GetIndexes.php										*
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
	if( $argc < 2 )
		exit( "usage: php -f GetIndexes.php "
			 ."<database> "
			 ."<tag native identifier>\n" );										// ==>
	
	//
	// Get arguments.
	//
	$database = $argv[ 1 ];
	$tag = $argv[ 2 ];
	
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
		 ."Database:         ".substr( $parts[ 'path' ], 1 )."\n" );
	
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
		$serial = $tag;
		if( ($tmp = $wrapper->getObject( $tag, FALSE )) === NULL )
			exit( "Unknown tag [$tag]\n" );											// ==>
		$tag = $tmp[ kTAG_NID ];
	}
	else
	{
		if( ($serial = $wrapper->getSerial( $tag, FALSE )) === NULL )
			exit( "Unknown tag [$tag]\n" );											// ==>
	}
	
	//
	// Get tag object.
	//
	$tag = new OntologyWrapper\Tag( $wrapper, $tag );
	if( ($tmp = $tag[ kTAG_UNIT_OFFSETS ]) !== NULL )
	{
		$offsets = Array();
		foreach( $tmp as $offset )
		{
			$items = explode( '.', $offset );
			if( $items[ count( $items ) - 1 ] == $serial )
				$offsets[] = $offset;
		}
		
		echo( "\n" );
		
		foreach( $offsets as $offset )
			echo( "db.getCollection(\"_units\").ensureIndex( "
				 ."{ \"$offset\" : 1 }, "
				 ."{ name : \"ix_$offset\", "
				 ."sparse : true } )\n" );

		exit( "\nDone!\n" );														// ==>
	}

	exit( "No offsets" );															// ==>
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
}

?>
