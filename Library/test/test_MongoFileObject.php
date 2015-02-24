<?php

/**
 * {@link MongoFileObject} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link MongoFileObject} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 21/02/2015
 */

/*=======================================================================================
 *																						*
 *								test_MongoFileObject.php								*
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
// Style includes.
//
require_once( 'styles.inc.php' );

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

//
// Domain definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );

//
// Operators.
//
require_once( kPATH_DEFINITIONS_ROOT."/Operators.inc.php" );

//
// API.
//
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );


/*=======================================================================================
 *	RUNTIME SETTINGS																	*
 *======================================================================================*/
 
//
// Debug switches.
//
define( 'kDEBUG_PARENT', FALSE );


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

//
// Init local storage.
//
$prefix = "TEST";
$path = '/Library/WebServer/Library/OntologyWrapper/Library/snippets/Test Templates';
 
//
// Test class.
//
try
{
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
	$meta = $wrapper->metadata(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	$wrapper->units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/BIOVERSITY?connect=1" ) );
	
	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Instantiate filer.
	//
	echo( '<h4>Instantiate filer</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$filer = $wrapper->users()->filer( $prefix );' );
	$filer = $wrapper->users()->filer( $prefix );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $filer->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Create and save file.
	//
	echo( '<h4>Create and save file</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$file = OntologyWrapper\\MongoFileObject::NewFile( $filer, "$path/CWR_Checklist_Template.xlsx" );' );
	$file = OntologyWrapper\MongoFileObject::NewFile( $filer, "/Library/WebServer/Library/OntologyWrapper/Library/test/test_MongoFileObject.php" );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$id = $file->getID();' );
	$id = $file->getID();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $file->getSize();' );
	$data = $file->getSize();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $file->getHash();' );
	$data = $file->getHash();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $file->fileName();' );
	$data = $file->fileName();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $file->fileDate();' );
	$data = $file->fileDate();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $file->fileType();' );
	$data = $file->fileType();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $file->getMeta();' );
	$data = $file->getMeta();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
	
	//
	// Query files.
	//
	echo( '<h4>Query files</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$cursor = $filer->matchAll( array( kTAG_FILE_MIME_TYPE => "text/x-php" ), kQUERY_OBJECT );' );
	$cursor = $filer->matchAll( array( kTAG_FILE_MIME_TYPE => "text/x-php" ), kQUERY_OBJECT );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $cursor->count();' );
	$data = $cursor->count();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'foreach( $cursor as item ) echo( (string) $item->getID() );' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	foreach( $cursor as $item )
		var_dump( $item->getID() );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$cursor = $filer->matchAll( array( kTAG_FILE_MIME_TYPE => "text/x-php" ), kQUERY_ARRAY );' );
	$cursor = $filer->matchAll( array( kTAG_FILE_MIME_TYPE => "text/x-php" ), kQUERY_ARRAY );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $cursor->count();' );
	$data = $cursor->count();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'foreach( $cursor as item ) echo( (string) $item->getID() );' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	foreach( $cursor as $item )
		var_dump( $item->getID() );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

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
