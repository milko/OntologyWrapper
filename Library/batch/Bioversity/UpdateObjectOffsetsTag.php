<?php

/**
 * SQL archive load procedure.
 *
 * This file contains routines to load ovjects from the XML SQL archive.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 29/08/2014
 */

/*=======================================================================================
 *																						*
 *								UpdateObjectOffsetsTag.php								*
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
// Domain definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

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
// Inform.
//
echo( "\n==> Updating object offsets property.\n" );

//
// Try.
//
try
{
	//
	// Inform.
	//
	echo( "  • Connecting.\n" );
	$m = new MongoClient( 'mongodb://localhost:27017' );
	$d = $m->selectDB( 'BIOVERSITY' );
	$c = $d->selectCollection( OntologyWrapper\UnitObject::kSEQ_NAME );
	
	//
	// Update.
	//
	echo( "  • Updating\n" );
	$rs = $c->find( Array(), array( kTAG_OBJECT_OFFSETS => TRUE ) );
	foreach( $rs as $id => $record )
	{
		if( array_key_exists( kTAG_OBJECT_OFFSETS, $record ) )
		{
			$new = Array();
			$old = $record[ kTAG_OBJECT_OFFSETS ];
			foreach( $old as $tag => $offsets )
			{
				foreach( $offsets as $offset )
					$new[] = $offset;
			}

			if( count( $new ) )
				$c->update(
					array( '_id' => $id ),
					array(
						'$set' => array(
							kTAG_OBJECT_OFFSETS => $new ) ) );
		}
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

?>
