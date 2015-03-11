<?php

/**
 * {@link Session} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link Session} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 13/02/2015
 */

/*=======================================================================================
 *																						*
 *									test_Session.php									*
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
 *	TEST																				*
 *======================================================================================*/

//
// Managed offsets query.
//
// { "@7" : { "$in" : [ "@2a", "@60", "@19", "@2b", "@68" ] } }

//
// Init local storage.
//
$user = ":domain:individual://ITA406/pgrdiversity.bioversityinternational.org:E3EC37CC5D36ED5AABAC7BB46CB0CC8794693FC2;";
$file = "/Library/WebServer/Library/OntologyWrapper/Library/test/CWR_Checklist_Template.test.xlsx";
	
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
	// Instantiate upload session.
	//
	echo( '<h4>Instantiate upload session</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$upload = new OntologyWrapper\\Session( $wrapper )<br />;' );
	$upload = new OntologyWrapper\Session( $wrapper );
	echo( '$upload[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPLOAD;<br />' );
	$upload[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPLOAD;
	echo( '$upload[ kTAG_USER ] = $user;<br />' );
	$upload[ kTAG_USER ] = $user;
	echo( '$upload_id = $upload->commit();' );
	$upload_id = $upload->commit();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'ID' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $upload_id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $upload->getName( kSTANDARDS_LANGUAGE );' );
	$data = $upload->getName( kSTANDARDS_LANGUAGE );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $upload->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Set collections count and processed.
	//
	echo( '<h4>Set collections count and processed</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$upload->offsetSet( kTAG_COUNTER_COLLECTIONS, 90 );<br />;' );
	$upload->offsetSet( kTAG_COUNTER_COLLECTIONS, 90 );
	echo( '$upload->processed( 20, kTAG_COUNTER_COLLECTIONS );<br />;' );
	$upload->processed( 20, kTAG_COUNTER_COLLECTIONS );
	echo( '$counters = $upload->counters();' );
	$counters = $upload->counters();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Counters' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $counters );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$upload->processed( 10, kTAG_COUNTER_COLLECTIONS );<br />;' );
	$upload->processed( 10, kTAG_COUNTER_COLLECTIONS );
	echo( '$upload->validated( 5 );<br />;' );
	$upload->validated( 5 );
	echo( '$upload->rejected( 3 );<br />;' );
	$upload->rejected( 3 );
	echo( '$upload->skipped( 2 );<br />;' );
	$upload->skipped( 2 );
	echo( '$counters = $upload->counters();' );
	$counters = $upload->counters();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Counters' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $counters );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
		
	//
	// Instantiate transaction.
	//
	echo( '<h4>Instantiate transaction</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$transaction = $upload->newTransaction( kTYPE_TRANS_TMPL_PREPARE, "Collection", 9 );<br />' );
	$transaction = $upload->newTransaction( kTYPE_TRANS_TMPL_PREPARE, "Collection", 9 );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $transaction->getName( kSTANDARDS_LANGUAGE );' );
	$data = $transaction->getName( kSTANDARDS_LANGUAGE );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$transaction->processed( 10, kTAG_COUNTER_COLLECTIONS );<br />;' );
	$transaction->processed( 10, kTAG_COUNTER_COLLECTIONS );
	echo( '$transaction->validated( 5 );<br />;' );
	$transaction->validated( 5 );
	echo( '$transaction->rejected( 3 );<br />;' );
	$transaction->rejected( 3 );
	echo( '$transaction->skipped( 2 );<br />;' );
	$transaction->skipped( 2 );
	echo( '$counters = $transaction->counters();' );
	$counters = $transaction->counters();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Counters' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $counters );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$transaction = new OntologyWrapper\\Transaction( $wrapper, $transaction->offsetGet( kTAG_NID ) );<br />' );
	$transaction = new OntologyWrapper\Transaction( $wrapper, $transaction->offsetGet( kTAG_NID ) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $transaction->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Add transaction logs.
	//
	echo( '<h4>Add transaction logs</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
				$theTransaction->setLog(
					kTYPE_STATUS_ERROR,					// Status,
					$symbol,							// Alias.
					$field[ 'column_name' ],			// Field.
					NULL,								// Value.
					'Missing required field.',			// Message.
					$tag,								// Tag.
					kTYPE_ERROR_MISSING_REQUIRED,		// Error type.
					kTYPE_ERROR_CODE_REQ_FIELD,			// Error code.
					NULL );								// Error resource.
	echo( '$transaction->setLog( kTYPE_STATUS_MESSAGE, "Alias", "Field", "Value", "Message", kTAG_LABEL, kTYPE_ERROR_INVALID_VALUE, kTYPE_ERROR_CODE_FILE_UNSUP, "http://www.apple.com" );<br />' );
	$transaction->setLog( kTYPE_STATUS_MESSAGE, "Alias", "Field", "Value", "Message", kTAG_LABEL, kTYPE_ERROR_INVALID_VALUE, kTYPE_ERROR_CODE_FILE_UNSUP, "http://www.apple.com" );
	echo( '$data = $transaction[ kTAG_TRANSACTION_LOG ];' );
	$data = $transaction[ kTAG_TRANSACTION_LOG ];
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$transaction->setLog( kTYPE_STATUS_WARNING, "Alias", NULL, "Value", "Warning", kTAG_DEFINITION );<br />' );
	$transaction->setLog( kTYPE_STATUS_WARNING, "Alias", NULL, "Value", "Warning", kTAG_DEFINITION );
	echo( '$data = $transaction[ kTAG_TRANSACTION_LOG ];' );
	$data = $transaction[ kTAG_TRANSACTION_LOG ];
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $data );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$transaction = new OntologyWrapper\\Transaction( $wrapper, $transaction->offsetGet( kTAG_NID ) );<br />' );
	$transaction = new OntologyWrapper\Transaction( $wrapper, $transaction->offsetGet( kTAG_NID ) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $transaction->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Add file and close transaction.
	//
	echo( '<h4>Add file and close transaction</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$file_id = $transaction->saveFile( $file );' );
	$file_id = $transaction->saveFile( $file );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $file_id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$transaction[ kTAG_TRANSACTION_END ] = TRUE;<br />' );
	$transaction[ kTAG_TRANSACTION_END ] = TRUE;
	echo( '$transaction = new OntologyWrapper\\Transaction( $wrapper, $transaction->offsetGet( kTAG_NID ) );<br />' );
	$transaction = new OntologyWrapper\Transaction( $wrapper, $transaction->offsetGet( kTAG_NID ) );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $transaction->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
		
	//
	// Create related update session.
	//
	echo( '<h4>Create related update session</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$update = new OntologyWrapper\\Session( $wrapper );<br />' );
	$update = new OntologyWrapper\Session( $wrapper );
	echo( '$update[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPDATE;<br />' );
	$update[ kTAG_SESSION_TYPE ] = kTYPE_SESSION_UPDATE;
	echo( '$update[ kTAG_USER ] = $user;<br />' );
	$update[ kTAG_USER ] = $user;
	echo( '$update_id = $update->commit();' );
	$update_id = $update->commit();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'ID' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $update_id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
		
	//
	// Relate upload session.
	//
	echo( '<h4>Relate upload session</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$upload[ kTAG_SESSION ] = $update_id;<br />' );
	$upload[ kTAG_SESSION ] = $update_id;
	echo( '$related = $upload[ kTAG_SESSION ];' );
	$related = $upload[ kTAG_SESSION ];
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $related );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
		
	//
	// Delete session.
	//
	echo( '<h4>Delete session</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'OntologyWrapper\\Session::Delete( $wrapper, $update );<br />' );
	OntologyWrapper\Session::Delete( $wrapper, $update );
	echo( kSTYLE_HEAD_POS );
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
