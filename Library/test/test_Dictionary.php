<?php

/**
 * {@link Dictionary} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link Dictionary} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/02/2014
 */

/*=======================================================================================
 *																						*
 *									test_Dictionary.php									*
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
// Session offsets.
//
require_once( kPATH_DEFINITIONS_ROOT.'/session.inc.php' );

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT.'/Tags.inc.php' );

//
// Type definitions.
//
require_once( kPATH_DEFINITIONS_ROOT.'/Types.inc.php' );


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
class MyClass extends OntologyWrapper\Dictionary
{
	public function AccessorOffset( $theOffset, $theValue = NULL, $getOld = FALSE )
	{	return $this->manageOffset( $theOffset, $theValue, $getOld );			}
	
	public function AccessorSetOffset( $theOffset, $theValue, $theOperation = NULL,
															$getOld = FALSE )
	{	return $this->manageSetOffset( $theOffset, $theValue, $theOperation, $getOld );
																				}
	
	public function AccessorArrayOffset( $theOffset, $theKey, $theValue = NULL,
															  $getOld = FALSE )
	{	return $this->manageArrayOffset( $theOffset, $theKey, $theValue, $getOld );
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
 
//
// Test class.
//
try
{
	//
	// Test parent class.
	// No parents.
	//
	if( kDEBUG_PARENT )
	{
		//
		// Instantiate empty object.
		//
		echo( '<h4>Instantiate empty object</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass( "test" );'.kSTYLE_HEAD_POS );
		$test = new MyClass( "test" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		var_dump( $test );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Set offset.
		//
		echo( '<h4>Set offset<br /><i>should add the value</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test["test"] = "TEST1";'.kSTYLE_HEAD_POS );
		$test["test"] = "TEST1";
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Set NULL offset.
		//
		echo( '<h4>Set NULL offset<br /><i>should add the value under offset 0</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[NULL] = "TEST2";'.kSTYLE_HEAD_POS );
		$test[NULL] = "TEST2";
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Set NULL value.
		//
		echo( '<h4>Set NULL value<br /><i>should delete "test" offset</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test["test"] = NULL;'.kSTYLE_HEAD_POS );
		$test["test"] = NULL;
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Set NULL offset and value.
		//
		echo( '<h4>Set NULL offset and value<br /><i>should throw a warning</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[NULL] = NULL;'.kSTYLE_HEAD_POS );
		$test[NULL] = NULL;
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Get offset 0.
		//
		echo( '<h4>Get offset 0<br /><i>should return "TEST2"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[0];'.kSTYLE_HEAD_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $test[0] ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Delete offset 0.
		//
		echo( '<h4>Delete offset<br /><i>should delete "TEST2"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->offsetUnset( 0 );'.kSTYLE_HEAD_POS );
		$test->offsetUnset( 0 );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Delete offset with NULL.
		//
		echo( '<h4>Delete offset with NULL<br /><i>should delete ["test"] => "TEST"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test["test"] = "TEST";'.kSTYLE_HEAD_POS );
		$test["test"] = "TEST";
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test["test"] = NULL;'.kSTYLE_HEAD_POS );
		$test["test"] = NULL;
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
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
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass( "test" );'.kSTYLE_HEAD_POS );
		$test = new MyClass( "test" );
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
		echo( '<hr>' );

		//
		// Test set offset.
		//
		echo( '<h4>Test set offset<br /><i>should set the "OFFSET" to "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass( "test" );'.kSTYLE_HEAD_POS );
		$test = new MyClass( "test" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorOffset( "OFFSET", "value" );'.kSTYLE_HEAD_POS );
		$test->AccessorOffset( "OFFSET", "value" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test retrieve offset.
		//
		echo( '<h4>Test retrieve offset<br /><i>should return "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "OFFSET" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "OFFSET" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test modify offset returning new value.
		//
		echo( '<h4>Test modify offset returning new value<br /><i>should return "new"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "OFFSET", "new" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "OFFSET", "new" );
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
		// Test modify offset returning old value.
		//
		echo( '<h4>Test modify offset returning old value<br /><i>should return "new"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "OFFSET", "modified", TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "OFFSET", "modified", TRUE );
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
		// Test reset offset returning old value.
		//
		echo( '<h4>Test reset offset returning old value<br /><i>should return "modified"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "OFFSET", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "OFFSET", FALSE, TRUE );
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
		// Test reset offset returning new value.
		//
		echo( '<h4>Test reset offset returning new value<br /><i>should return NULL</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "OFFSET", "new" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "OFFSET", "new" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "OFFSET", FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "OFFSET", FALSE );
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
		// Test retrieve non-existing offset.
		//
		echo( '<h4>Test retrieve non-existing offset<br /><i>should return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "NOT THERE" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "NOT THERE" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
		echo( '<hr>' );

		//
		// Test set offset set.
		//
		echo( '<h4>Test set offset set<br /><i>should set the "OFFSET" to "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass( "test" );'.kSTYLE_HEAD_POS );
		$test = new MyClass( "test" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorSetOffset( "OFFSET", "value", TRUE );'.kSTYLE_HEAD_POS );
		$test->AccessorSetOffset( "OFFSET", "value", TRUE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test retrieve offset set.
		//
		echo( '<h4>Test retrieve offset set<br /><i>should return "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "OFFSET", "value", NULL );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "OFFSET", "value", NULL );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test set duplicate offset set.
		//
		echo( '<h4>Test set duplicate offset set<br /><i>should not add another "value" to "OFFSET"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorSetOffset( "OFFSET", "value", TRUE );'.kSTYLE_HEAD_POS );
		$test->AccessorSetOffset( "OFFSET", "value", TRUE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test set new offset set.
		//
		echo( '<h4>Test set duplicate offset set<br /><i>should add another "new" to "OFFSET"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorSetOffset( "OFFSET", "new", TRUE );'.kSTYLE_HEAD_POS );
		$test->AccessorSetOffset( "OFFSET", "new", TRUE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test reset offset set returning old value.
		//
		echo( '<h4>Test reset offset set returning old value<br /><i>should return "value"; notice array keys</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "OFFSET", "value", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "OFFSET", "value", FALSE, TRUE );
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
		// Test reset non-existing set value.
		//
		echo( '<h4>Test reset non-existing set value<br /><i>should return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "OFFSET", "value", FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "OFFSET", "value", FALSE );
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
		// Test reset offset set returning new value.
		//
		echo( '<h4>Test reset offset set returning new value<br /><i>should delete "OFFSET"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "OFFSET", "new", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "OFFSET", "new", FALSE, TRUE );
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
		// Test retrieve non-existing offset set.
		//
		echo( '<h4>Test retrieve non-existing offset set<br /><i>should return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "NOT THERE", "new" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "NOT THERE", "new" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
		echo( '<hr>' );

		//
		// Test set array offset.
		//
		echo( '<h4>Test set array offset<br /><i>should set the "OFFSET" to "key" / "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass( "test" );'.kSTYLE_HEAD_POS );
		$test = new MyClass( "test" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorArrayOffset( "OFFSET", "key", "value" );'.kSTYLE_HEAD_POS );
		$test->AccessorArrayOffset( "OFFSET", "key", "value" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test retrieve array offset.
		//
		echo( '<h4>Test retrieve array offset<br /><i>should return "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorArrayOffset( "OFFSET", "key" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorArrayOffset("OFFSET", "key" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test modify array offset.
		//
		echo( '<h4>Test modify array offset<br /><i>should replace "value" with "new" in "OFFSET"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorArrayOffset( "OFFSET", "key", "new" );'.kSTYLE_HEAD_POS );
		$test->AccessorArrayOffset( "OFFSET", "key", "new" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test reset array offset returning old value.
		//
		echo( '<h4>Test reset array offset returning old value<br /><i>should return "new"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorArrayOffset( "OFFSET", "key", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorArrayOffset( "OFFSET", "key", FALSE, TRUE );
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
		// Test reset non-existing set value.
		//
		echo( '<h4>Test reset non-existing set value<br /><i>should return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorArrayOffset( "OFFSET", "key", FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorArrayOffset( "OFFSET", "key", FALSE );
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
		// Test retrieve non-existing set value.
		//
		echo( '<h4>Test retrieve non-existing set value<br /><i>should return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorArrayOffset( "OFFSET", "key" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorArrayOffset( "OFFSET", "key" );
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
		echo( '<hr>' );

		//
		// Test set element match offset.
		//
		echo( '<h4>Test set element match offset<br /><i>should set the "OFFSET" to "home"/"tel1" pair and return "tel1"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass( "test" );'.kSTYLE_HEAD_POS );
		$test = new MyClass( "test" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "home", "tel1", FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "home", "tel1", FALSE );
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
		// Test set other element match offset.
		//
		echo( '<h4>Test set other element match offset<br /><i>should set the "OFFSET" to "work"/"tel2" pair and return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "work", "tel2", TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "work", "tel2", TRUE );
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
		// Test set empty kind element match offset.
		//
		echo( '<h4>Test set empty kind element match offset<br /><i>should set the "OFFSET" element value to "tel3" pair and return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", NULL, "tel3" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", NULL, "tel3" );
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
		// Test retrieve home element match offset.
		//
		echo( '<h4>Test retrieve home element match offset<br /><i>should return "tel1"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "home" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "home" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test retrieve empty element match offset.
		//
		echo( '<h4>Test retrieve empty element match offset<br /><i>should return "tel3"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", NULL );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", NULL );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); var_dump( $value ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test replace work element match offset.
		//
		echo( '<h4>Test replace work element match offset<br /><i>should set the "work" phone to "WORK PHONE"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "work", "WORK PHONE" );'.kSTYLE_HEAD_POS );
		$test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "work", "WORK PHONE" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test reset element match offset returning old value.
		//
		echo( '<h4>Test reset element match offset returning old value<br /><i>should return "tel1"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "home", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "home", FALSE, TRUE );
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
		// Test reset element match offset returning new value.
		//
		echo( '<h4>Test reset element match offset returning ew value<br /><i>should return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "work", FALSE, FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", "work", FALSE, FALSE );
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
		// Test reset last element match offset.
		//
		echo( '<h4>Test reset last element match offset<br /><i>should detete the whole "OFFSET" offset and return "tel3"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", NULL, FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "OFFSET", "TYPE", "DATA", NULL, FALSE, TRUE );
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
	}
	
	//
	// Create test tag.
	//
	echo( '<h4>Create test tag</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$tag = Array();<br/>' );
	$tag = Array();
	echo( '$tag[ kTAG_NID ] = "test-tag";<br/>' );
	$tag[ kTAG_NID ] = "test-tag";
	echo( '$tag[ kTAG_ID_SEQUENCE ] = -1;<br/>' );
	$tag[ kTAG_ID_SEQUENCE ] = -1;
	echo( '$tag[ kTAG_DATA_TYPE ] = array( kTYPE_STRING );' );
	$tag[ kTAG_DATA_TYPE ] = array( kTYPE_STRING );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $tag ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	echo( '<hr>' );
	
	//
	// Test instantiate object.
	//
	echo( '<h4>Test instantiate object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass( kSESSION_DDICT, array( array( "localhost", 11211 ) ) );'.kSTYLE_HEAD_POS );
	$test = new MyClass( kSESSION_DDICT, array( array( "localhost", 11211 ) ) );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->dictionaryStats();'.kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->dictionaryStats() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->dictionaryCount();'.kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $test->dictionaryCount() );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Set tag.
	//
	echo( '<h4>Set tag</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->setTag( $tag );'.kSTYLE_HEAD_POS );
	$test->setTag( $tag );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->dictionaryCount();'.kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $test->dictionaryCount() );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Get identifier.
	//
	echo( '<h4>Get identifier<br /><i>should return -1</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$id = $test->getIdentifier( "test-tag" );'.kSTYLE_HEAD_POS );
	$id = $test->getIdentifier( "test-tag" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Get object.
	//
	echo( '<h4>Get object<br /><i>should return tag object</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$tag = $test->getObject( $id );'.kSTYLE_HEAD_POS );
	$tag = $test->getObject( $id );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $tag ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Delete tag.
	//
	echo( '<h4>Delete tag<br /><i>should have two objects less (identifier and object)</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->delTag( $id );'.kSTYLE_HEAD_POS );
	$test->delTag( $id );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $test->dictionaryCount() );
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
	echo( '<pre>' );
	echo( (string) $error );
	echo( '</pre>' );
}

echo( "\nDone!\n" );

?>
