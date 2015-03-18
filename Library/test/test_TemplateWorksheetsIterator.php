<?php

/**
 * {@link TemplateStructure} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link TemplateStructure} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/03/2015
 */

/*=======================================================================================
 *																						*
 *							test_TemplateWorksheetsIterator.php							*
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
$file = '/Library/WebServer/Library/OntologyWrapper/Library/test/CWR_Checklist_Template.test.xlsx';
$user = ":domain:individual://ITA406/pgrdiversity.bioversityinternational.org:E3EC37CC5D36ED5AABAC7BB46CB0CC8794693FC2;";
$fingerprint = "E3EC37CC5D36ED5AABAC7BB46CB0CC8794693FC2";
 
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
	// Instantiate session.
	//
	echo( '<h4>Instantiate session</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$session = new OntologyWrapper\\Session( $wrapper )<br />;' );
	$session = new OntologyWrapper\Session( $wrapper );
	echo( '$session[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPLOAD;<br />' );
	$session[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPLOAD;
	echo( '$session[ kTAG_USER ] = $user;<br />' );
	$session[ kTAG_USER ] = $user;
	echo( '$session[ kTAG_ENTITY_PGP_FINGERPRINT ] = $fingerprint;<br />' );
	$session[ kTAG_ENTITY_PGP_FINGERPRINT ] = $fingerprint;
	echo( '$session_id = $session->commit();<br />' );
	$session_id = $session->commit();
	echo( '$transaction = $session->newTransaction( kTYPE_TRANS_TMPL_PREPARE, kTYPE_STATUS_EXECUTING, "Collection", 9 );<br />' );
	$transaction = $session->newTransaction( kTYPE_TRANS_TMPL_PREPARE, kTYPE_STATUS_EXECUTING, "Collection", 9 );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
	
	//
	// Instantiate parser.
	//
	echo( '<h4>Instantiate parser</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$parser = new OntologyWrapper\\ExcelTemplateParser( $wrapper, $file );<br />' );
	$parser = new OntologyWrapper\ExcelTemplateParser( $wrapper, $file );
	echo( '$ok = $parser->loadStructure( $transaction );' );
	$ok = $parser->loadStructure( $transaction );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $ok );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Instantiate iterator.
	//
	echo( '<h4>Instantiate iterator</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$iter = new OntologyWrapper\\TemplateWorksheetsIterator( $parser );' );
	$iter = new OntologyWrapper\TemplateWorksheetsIterator( $parser );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'getList()' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $iter->getList() );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'getStruct()' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $iter->getStruct() );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'getRoot()' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $iter->getRoot() );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Iterate.
	//
	echo( '<h4>Iterate</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$iter->rewind();' );
	$iter->rewind();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	
	while( $iter->valid() )
	{
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE );
		echo( '$iter->key();' );
		echo( kSTYLE_HEAD_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		var_dump( $iter->key() );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE );
		echo( '$iter->current();' );
		echo( kSTYLE_HEAD_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		var_dump( $iter->current() );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE );
		echo( '$iter->parent();' );
		echo( kSTYLE_HEAD_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		var_dump( $iter->parent() );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE );
		echo( '$iter->next();' );
		$iter->next();
		echo( kSTYLE_HEAD_POS );
		echo( kSTYLE_ROW_POS );
	}
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Iterate all.
	//
	echo( '<h4>Iterate all</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$list = iterator_to_array( $iter );' );
	$list = iterator_to_array( $iter );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $list );
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
