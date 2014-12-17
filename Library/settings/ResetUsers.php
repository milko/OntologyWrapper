<?php

/**
 * Administrator user initialisation.
 *
 * This file contains a script to create the default administrator's user accounts.
 * Once created, change the password.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Batch
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 09/12/2014
 */

/*=======================================================================================
 *																						*
 *									ResetUsers.php										*
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
// Session includes.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );


/*=======================================================================================
 *	MAIN																				*
 *======================================================================================*/
 
//
// Test class.
//
try
{
	//
	// Instantiate wrapper.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( kSTANDARDS_DDICT_HOST, kSTANDARDS_DDICT_PORT ) ) );

	//
	// Set databases.
	//
	$wrapper->Metadata(
		new OntologyWrapper\MongoDatabase(
			kSTANDARDS_METADATA_DB ) );
	$wrapper->Users(
		new OntologyWrapper\MongoDatabase(
			kSTANDARDS_ENTITIES_DB ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			kSTANDARDS_UNITS_DB ) );

	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Get users collection.
	//
	$collection = $wrapper->resolveCollection( OntologyWrapper\User::kSEQ_NAME );
	
	//
	// Delete existing users.
	//
	$rs = $collection->matchAll( Array(), kQUERY_NID );
	foreach( $rs as $id )
		OntologyWrapper\User::Delete( $wrapper, $id );
	
	//
	// Reset users sequence number.
	//
	OntologyWrapper\User::ResolveDatabase( $wrapper, TRUE )
		->setSequenceNumber( OntologyWrapper\User::kSEQ_NAME, 1 );
	
	//
	// Load administrator.
	//
	$wrapper->loadXMLFile( kPATH_LIBRARY_ROOT."/settings/Admin.xml" );

}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( '<pre>'.$error->xdebug_message.'</pre>' );
}

echo( "\nDone!\n" );

?>
