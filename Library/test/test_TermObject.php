<?php

/**
 * {@link TermObject} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link TermObject} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 08/02/2014
 */

/*=======================================================================================
 *																						*
 *									test_TermObject.php									*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

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
define( 'kDEBUG_PARENT', TRUE );


/*=======================================================================================
 *	CLASS SETTINGS																		*
 *======================================================================================*/
 
//
// Cast current class.
//
class MyClass extends OntologyWrapper\TermObject
{
	public function AccessorOffset( $theOffset, $theValue = NULL, $getOld = FALSE )
	{	return $this->manageOffset( $theOffset, $theValue, $getOld );			}
	
	public function AccessorSetOffset( $theOffset, $theValue, $theOperation = NULL,
															$getOld = FALSE )
	{	return $this->manageSetOffset( $theOffset, $theValue, $theOperation, $getOld );
																				}
	
	public function AccessorElementMatchOffset( $theOffset, $theTypeOffset, $theDataOffset,
														  $theTypeValue, $theDataValue = NULL,
														  $getOld = FALSE )
	{	return $this->manageElementMatchOffset( $theOffset,
												$theTypeOffset, $theDataOffset,
												$theTypeValue, $theDataValue,
												$getOld );						}
	
	public function AccessorProperty( &$theMember, $theValue = NULL, $getOld = FALSE )
	{	return $this->manageProperty( $theMember, $theValue, $getOld );			}
}


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
	// Instantiate main tag cache.
	//
	$_SESSION[ kSESSION_DDICT ]
		= new OntologyWrapper\TagCache(
			kSESSION_DDICT,
			array( array( 'localhost', 11211 ) ) );
	
	//
	// Init cache.
	//
	$_SESSION[ kSESSION_DDICT ]->init();
	
	//
	// Test parent class.
	//
	if( kDEBUG_PARENT )
	{
		echo( "<h3>Parent class test</h3>" );
		//
		// Instantiate empty object.
		//
		echo( '<h4>Instantiate empty object</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		var_dump( $test );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
	
		//
		// Test set property.
		//
		echo( '<h4>Test set property<br /><i>should set the "$property" to "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorProperty( $test->property, "value" );'.kSTYLE_HEAD_POS );
		$test->AccessorProperty( $test->property, "value" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test retrieve property.
		//
		echo( '<h4>Test retrieve property<br /><i>should return "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorProperty( $test->property );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorProperty( $test->property );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test modify property returning new value.
		//
		echo( '<h4>Test modify property returning new value<br /><i>should return "new"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorProperty( $test->property, "new" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorProperty( $test->property, "new" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test modify property returning old value.
		//
		echo( '<h4>Test modify property returning old value<br /><i>should return "new"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorProperty( $test->property, "modified", TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorProperty( $test->property, "modified", TRUE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test reset property returning old value.
		//
		echo( '<h4>Test reset property returning old value<br /><i>should return "modified"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorProperty( $test->property, FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorProperty( $test->property, FALSE, TRUE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test reset property returning new value.
		//
		echo( '<h4>Test reset property returning new value<br /><i>should return NULL</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorProperty( $test->property, "new" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorProperty( $test->property, "new" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorProperty( $test->property, FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorProperty( $test->property, FALSE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Set offset by global identifier.
		//
		echo( '<h4>Set offset by global identifier<br /><i>should use kTAG_LABEL</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[ ":label" ] = "LABEL";'.kSTYLE_HEAD_POS );
		$test[ ":label" ] = "LABEL";
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Set offset by native identifier.
		//
		echo( '<h4>Set offset by native identifier<br /><i>should replace kTAG_LABEL</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[ kTAG_LABEL ] = "NEW LABEL";'.kSTYLE_HEAD_POS );
		$test[ kTAG_LABEL ] = "NEW LABEL";
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Set invalid offset.
		//
		echo( '<h4>Set invalid offset<br /><i>should raise an exception</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[ "not good" ] = "will never be set";'.kSTYLE_HEAD_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		try
		{
			$test[ "not good" ] = "will never be set";
		}
		catch( \Exception $error )
		{
			echo( $error->xdebug_message );
		}
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Unset by global identifier.
		//
		echo( '<h4>Unset by global identifier<br /><i>should delete kTAG_LABEL</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[ kTAG_LABEL ] = NULL;'.kSTYLE_HEAD_POS );
		$test[ kTAG_LABEL ] = NULL;
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
	} echo( '<hr>' );
	
	//
	// Header.
	//
	if( kDEBUG_PARENT )
		echo( "<h3>Current class test</h3>" );

	//
	// Set empty object.
	//
	echo( '<h4>Set empty object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
	$test = new MyClass();
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set namespace.
	//
	echo( '<h4>Test set namespace</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test[ kTAG_NS ] = "namespace";'.kSTYLE_HEAD_POS );
	$test[ kTAG_NS ] = "namespace";
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( (string) $test );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set local identifier.
	//
	echo( '<h4>Test local identifier</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test[ kTAG_LID ] = "local-id";'.kSTYLE_HEAD_POS );
	$test[ kTAG_LID ] = "local-id";
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( (string) $test );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test remove namespace.
	//
	echo( '<h4>Test remove namespace</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test[ kTAG_NS ] = NULL;'.kSTYLE_HEAD_POS );
	$test[ kTAG_NS ] = NULL;
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( (string) $test );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set native identifier.
	//
	echo( '<h4>Test set native identifier</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test[ kTAG_NID ] = "global-id";'.kSTYLE_HEAD_POS );
	$test[ kTAG_NID ] = "global-id";
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( (string) $test );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set label.
	//
	echo( '<h4>Test set label</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->Label( "en", "Label" );'.kSTYLE_HEAD_POS );
	$test->Label( "en", "Label" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test retrieve label.
	//
	echo( '<h4>Test retrieve label<br /><i>should retrieve "Label"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Label( "en" );'.kSTYLE_HEAD_POS );
	$value = $test->Label( "en" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $value );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set other language label.
	//
	echo( '<h4>Test set other language label<br /><i>should add "Etichetta"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Label( "it", "Etichetta" );'.kSTYLE_HEAD_POS );
	$value = $test->Label( "it", "Etichetta" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $value );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set duplicate language label.
	//
	echo( '<h4>Test set duplicate language label</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Label( "it", "Etichetta" );'.kSTYLE_HEAD_POS );
	$value = $test->Label( "it", "Etichetta" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test delete label.
	//
	echo( '<h4>Test delete label<br /><i>should delete "Label"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Label( "en", FALSE, TRUE );'.kSTYLE_HEAD_POS );
	$value = $test->Label( "en", FALSE, TRUE );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $value );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test retrieve non matching language label.
	//
	echo( '<h4>Test retrieve non matching language label<br /><i>should return <tt>NULL</tt></i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Label( "en" );'.kSTYLE_HEAD_POS );
	$value = $test->Label( "en" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $value );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );

	//
	// Test set definition.
	//
	echo( '<h4>Test set definition</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->Definition( "en", "Definition" );'.kSTYLE_HEAD_POS );
	$test->Definition( "en", "Definition" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test retrieve definition.
	//
	echo( '<h4>Test retrieve definition<br /><i>should retrieve "Definition"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Definition( "en" );'.kSTYLE_HEAD_POS );
	$value = $test->Definition( "en" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $value );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set other language definition.
	//
	echo( '<h4>Test set other language definition<br /><i>should add "Descrizione"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Definition( "it", "Descrizione" );'.kSTYLE_HEAD_POS );
	$value = $test->Definition( "it", "Descrizione" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $value );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set duplicate language definition.
	//
	echo( '<h4>Test set duplicate language definition</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Definition( "it", "Descrizione" );'.kSTYLE_HEAD_POS );
	$value = $test->Definition( "it", "Descrizione" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test delete definition.
	//
	echo( '<h4>Test delete definition<br /><i>should delete "Definition"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Definition( "en", FALSE, TRUE );'.kSTYLE_HEAD_POS );
	$value = $test->Definition( "en", FALSE, TRUE );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $value );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test retrieve non matching language definition.
	//
	echo( '<h4>Test retrieve non matching language definition<br /><i>should return <tt>NULL</tt></i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$value = $test->Definition( "en" );'.kSTYLE_HEAD_POS );
	$value = $test->Definition( "en" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $value );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set namespace by object.
	//
	echo( '<h4>Test set namespace by object<br /><i>should set "global-id"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$other = new MyClass();'.kSTYLE_HEAD_POS );
	$other = new MyClass();
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$other[ kTAG_NS ] = $test;'.kSTYLE_HEAD_POS );
	$other[ kTAG_NS ] = $test;
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $other ); echo( '</pre>' );
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
