<?php

/**
 * XML dump test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the XML dump
 * framework.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/07/2014
 */

/*=======================================================================================
 *																						*
 *								test_ObjectExportXML.php								*
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
// Style includes.
//
require_once( 'styles.inc.php' );

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );


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

session_start();
 
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
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->users(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );

/*	
	//
	// Drop database.
	//
	$meta->drop();
	
	//
	// Load database.
	//
	$command = 'unzip '
			  .'/Library/WebServer/Library/OntologyWrapper/Library/backup/data/TEST.zip '
			  .'-d /Library/WebServer/Library/OntologyWrapper/Library/backup/data';
	exec( $command );
	$command = 'mongorestore --directoryperdb '
			  .'/Library/WebServer/Library/OntologyWrapper/Library/backup/data/';
	exec( $command );
	$command = 'rm -r /Library/WebServer/Library/OntologyWrapper/Library/backup/data/TEST';
	exec( $command );
*/	
	
	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Test parent class.
	//
	if( kDEBUG_PARENT )
	{
		echo( "<h3>Parent class test</h3>" );
	} echo( '<hr>' );
	
	//
	// Header.
	//
	if( kDEBUG_PARENT )
		echo( "<h3>Current class test</h3>" );
		
	//
	// Load accession from database.
	//
	echo( '<h4>Load accession from database</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new OntologyWrapper\Accession( $wrapper, ":domain:accession://AUT001/Aegilops:BVAL-210005;" );<br/>' );
	$test = new OntologyWrapper\Accession( $wrapper, ":domain:accession://AUT001/Aegilops:BVAL-210005;" );
	echo( '$xml = $test->export();' );
	$xml = $test->export();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( htmlspecialchars( $xml->asXML() ) ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Load forest from database.
	//
	echo( '<h4>Load forest from database</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new OntologyWrapper\ForestUnit( $wrapper, ":domain:forest://AUT/00001/1996;" );<br/>' );
	$test = new OntologyWrapper\ForestUnit( $wrapper, ":domain:forest://AUT/00001/1996;" );
	echo( '$xml = $test->dump();' );
	$xml = $test->export();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( htmlspecialchars( $xml->asXML() ) ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Load organization from database.
	//
	echo( '<h4>Load organization from database</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new OntologyWrapper\FAOInstitute( $wrapper, ":domain:organisation://http://fao.org/wiews:ITA406;" );<br/>' );
	$test = new OntologyWrapper\FAOInstitute( $wrapper, ":domain:organisation://http://fao.org/wiews:ITA406;" );
	echo( '$xml = $test->dump();' );
	$xml = $test->export();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( htmlspecialchars( $xml->asXML() ) ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Load term from database.
	//
	echo( '<h4>Load term from database</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new OntologyWrapper\Term( $wrapper, "iso:3166:1:alpha-3:ITA" );<br/>' );
	$test = new OntologyWrapper\Term( $wrapper, "iso:3166:1:alpha-3:ITA" );
	echo( '$xml = $test->dump();' );
	$xml = $test->export();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( htmlspecialchars( $xml->asXML() ) ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
		
	//
	// Load accession from XML.
	//
	echo( '<h4>Load accession from XML</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new OntologyWrapper\Accession( $wrapper, ":domain:accession://AUT001/Aegilops:BVAL-210005;" );<br/>' );
	$test = new OntologyWrapper\Accession( $wrapper, ":domain:accession://AUT001/Aegilops:BVAL-210005;" );
	echo( '$xml = $test->export();<br/>' );
	$xml = $test->export();
	echo( '$test = OntologyWrapper\PersistentObject::Import( $wrapper, $xml->{kIO_XML_TRANS_UNITS} );<br/>' );
	$test = OntologyWrapper\PersistentObject::Import( $wrapper, $xml->{kIO_XML_TRANS_UNITS} );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Load forest from XML.
	//
	echo( '<h4>Load forest from XML</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new OntologyWrapper\ForestUnit( $wrapper, ":domain:forest://AUT/00001/1996;" );<br/>' );
	$test = new OntologyWrapper\ForestUnit( $wrapper, ":domain:forest://AUT/00001/1996;" );
	echo( '$xml = $test->export();<br/>' );
	$xml = $test->export();
	echo( '$test = OntologyWrapper\PersistentObject::Import( $wrapper, $xml->{kIO_XML_TRANS_UNITS} );<br/>' );
	$test = OntologyWrapper\PersistentObject::Import( $wrapper, $xml->{kIO_XML_TRANS_UNITS} );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Load organisation from XML.
	//
	echo( '<h4>Load organisation from XML</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new OntologyWrapper\FAOInstitute( $wrapper, ":domain:organisation://http://fao.org/wiews:ITA406;" );<br/>' );
	$test = new OntologyWrapper\FAOInstitute( $wrapper, ":domain:organisation://http://fao.org/wiews:ITA406;" );
	echo( '$xml = $test->export();<br/>' );
	$xml = $test->export();
	echo( '$test = OntologyWrapper\PersistentObject::Import( $wrapper, $xml->{kIO_XML_TRANS_UNITS} );<br/>' );
	$test = OntologyWrapper\PersistentObject::Import( $wrapper, $xml->{kIO_XML_TRANS_UNITS} );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Load term from XML.
	//
	echo( '<h4>Load term from XML</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new OntologyWrapper\Term( $wrapper, "iso:3166:1:alpha-3:ITA" );<br/>' );
	$test = new OntologyWrapper\Term( $wrapper, "iso:3166:1:alpha-3:ITA" );
	echo( '$xml = $test->export();<br/>' );
	$xml = $test->export();
	echo( '$test = OntologyWrapper\PersistentObject::Import( $wrapper, $xml->{kIO_XML_TRANS_META}->{kIO_XML_META_TERM} );<br/>' );
	$test = OntologyWrapper\PersistentObject::Import( $wrapper, $xml->{kIO_XML_TRANS_META}->{kIO_XML_META_TERM} );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->getArrayCopy() ); echo( '</pre>' );
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
	echo( $error->xdebug_message );
}

echo( "\nDone!\n" );

?>
