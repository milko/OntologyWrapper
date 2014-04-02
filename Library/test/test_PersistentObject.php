<?php

/**
 * {@link PersistentObject} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link PersistentObject} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 13/01/2014
 */

/*=======================================================================================
 *																						*
 *								test_PersistentObject.php								*
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
// Domain definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

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
 *	CLASS SETTINGS																		*
 *======================================================================================*/
 
//
// Cast current class.
//
class MyClass extends OntologyWrapper\PersistentObject
{
	const kSEQ_NAME = 'test';
	
	static function ResolveDatabase( OntologyWrapper\Wrapper $theWrapper,
															 $doAssert = TRUE,
															 $doOpen = TRUE )
	{	return $theWrapper->Units();											}

	static function ResolveRefCountTag( $theCollection )
	{
		if( $theCollection == 'test' )
			return kTAG_UNIT_COUNT;
		return parent::ResolveRefCountTag( $theCollection );
	
	} // ResolveRefCountTag.
	
	static function ResolveOffsetsTag( $theCollection )
	{
		if( $theCollection == 'test' )
			return kTAG_UNIT_OFFSETS;
		return parent::ResolveOffsetsTag( $theCollection );
	
	} // ResolveOffsetsTag.
	
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{	parent::__construct( $theContainer, $theIdentifier );
		$this->isInited( TRUE );												}

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
	
	public function __toString()						{	return "hello!";	}
	
	static function OffsetTypes( OntologyWrapper\DictionaryObject $theDictionary,
												  $theOffset,
												 &$theType, &$theKind,
												  $doAssert = TRUE )
	{
		switch( $theOffset )
		{
			case -1:
				$theType = kTYPE_FLOAT;
				$theKind = array( kTYPE_LIST );
				return TRUE;
			
			case -2:
				$theType = kTYPE_STRUCT;
				$theKind = array( kTYPE_LIST );
				return TRUE;
			
			case -3:
				$theType = kTYPE_STRUCT;
				$theKind = Array();
				return TRUE;
			
			case -4:
				$theType = kTYPE_SHAPE;
				$theKind = Array();
				return TRUE;
		}

		return parent::OffsetTypes(
			$theDictionary, $theOffset, $theType, $theKind, $doAssert );
	}
	
	public function Inited()	{	return ( $this->isInited() ) ? 'checked="1"' : '';	}
	public function Dirty()		{	return ( $this->isDirty() ) ? 'checked="1"' : '';	}
	public function Committed()	{	return ( $this->isCommitted() ) ? 'checked="1"' : '';	}
	public function Alias()		{	return ( $this->isAlias() ) ? 'checked="1"' : '';	}
	
	public function traverseObject( &$theTags, &$theRefs, $doValidate = TRUE )
	{
		$this->parseObject( $theTags, $theRefs, $doValidate );
	}
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
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->Entities(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	
	//
	// Load data dictionary.
	//
	$wrapper->loadTagCache();
	
	//
	// Drop test collection.
	//
	MyClass::ResolveCollection(
		MyClass::ResolveDatabase( $wrapper ) )
			->drop();
	
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
		echo( '<hr>' );

		//
		// Test set offset.
		//
		echo( '<h4>Test set offset<br /><i>should set the "1" to "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass( $wrapper );'.kSTYLE_HEAD_POS );
		$test = new MyClass( $wrapper );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorOffset( "1", "value" );'.kSTYLE_HEAD_POS );
		$test->AccessorOffset( "1", "value" );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "1" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "1" );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "1", "new" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "1", "new" );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "1", "modified", TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "1", "modified", TRUE );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "1", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "1", FALSE, TRUE );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "1", "new" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "1", "new" );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorOffset( "1", FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorOffset( "1", FALSE );
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
		echo( '<h4>Test set offset set<br /><i>should set the "1" to "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorSetOffset( "1", "value", TRUE );'.kSTYLE_HEAD_POS );
		$test->AccessorSetOffset( "1", "value", TRUE );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "1", "value", NULL );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "1", "value", NULL );
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
		echo( '<h4>Test set duplicate offset set<br /><i>should not add another "value" to "1"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorSetOffset( "1", "value", TRUE );'.kSTYLE_HEAD_POS );
		$test->AccessorSetOffset( "1", "value", TRUE );
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
		echo( '<h4>Test set duplicate offset set<br /><i>should add another "new" to "1"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorSetOffset( "1", "new", TRUE );'.kSTYLE_HEAD_POS );
		$test->AccessorSetOffset( "1", "new", TRUE );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "1", "value", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "1", "value", FALSE, TRUE );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "1", "value", FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "1", "value", FALSE );
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
		echo( '<h4>Test reset offset set returning new value<br /><i>should delete "1"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "1", "new", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "1", "new", FALSE, TRUE );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorSetOffset( "2", "new" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorSetOffset( "2", "new" );
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
		echo( '<h4>Test set array offset<br /><i>should set the "1" to "key" / "value"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorArrayOffset( "1", "key", "value" );'.kSTYLE_HEAD_POS );
		$test->AccessorArrayOffset( "1", "key", "value" );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorArrayOffset( "1", "key" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorArrayOffset("1", "key" );
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
		echo( '<h4>Test modify array offset<br /><i>should replace "value" with "new" in "1"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->AccessorArrayOffset( "1", "key", "new" );'.kSTYLE_HEAD_POS );
		$test->AccessorArrayOffset( "1", "key", "new" );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorArrayOffset( "1", "key", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorArrayOffset( "1", "key", FALSE, TRUE );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorArrayOffset( "1", "key", FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorArrayOffset( "1", "key", FALSE );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorArrayOffset( "1", "key" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorArrayOffset( "1", "key" );
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
		echo( '<h4>Test set element match offset<br /><i>should set the "1" to "home"/"tel1" pair and return "tel1"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
		$test = new MyClass();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "home", "tel1", FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "home", "tel1", FALSE );
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
		echo( '<h4>Test set other element match offset<br /><i>should set the "1" to "work"/"tel2" pair and return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "work", "tel2", TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "work", "tel2", TRUE );
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
		echo( '<h4>Test set empty kind element match offset<br /><i>should set the "1" element value to "tel3" pair and return <tt>NULL</tt></i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", NULL, "tel3" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", NULL, "tel3" );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "home" );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "home" );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", NULL );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", NULL );
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
		echo( kSTYLE_HEAD_PRE.'$test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "work", "WORK PHONE" );'.kSTYLE_HEAD_POS );
		$test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "work", "WORK PHONE" );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "home", FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "home", FALSE, TRUE );
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
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "work", FALSE, FALSE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", "work", FALSE, FALSE );
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
		echo( '<h4>Test reset last element match offset<br /><i>should detete the whole "1" offset and return "tel3"</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", NULL, FALSE, TRUE );'.kSTYLE_HEAD_POS );
		$value = $test->AccessorElementMatchOffset( "1", "TYPE", "DATA", NULL, FALSE, TRUE );
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
		echo( kSTYLE_HEAD_PRE.'$test = new MyClass( $wrapper );'.kSTYLE_HEAD_POS );
		$test = new MyClass( $wrapper );
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

		//
		// Test failed object reference.
		//
		echo( '<h4>Test failed object reference<br /><i>should raise an exception</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$ref = $test->reference();'.kSTYLE_HEAD_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		try
		{
			$ref = $test->reference();
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
		// Test successful object reference.
		//
		echo( '<h4>Test successful object reference<br /><i>should return the native identifier</i></h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test[ kTAG_NID ] = "native identifier";'.kSTYLE_HEAD_POS );
		$test[ kTAG_NID ] = "native identifier";
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$ref = $test->reference();'.kSTYLE_HEAD_POS );
		$ref = $test->reference();
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		var_dump( $ref );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
	} echo( '<hr>' );
	
	//
	// Header.
	//
	if( kDEBUG_PARENT )
		echo( "<h3>Current class test</h3>" );

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
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Store and load object.
	//
	echo( '<h4>Store and load object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test[ kTAG_NID ] = "OBJECT1";'.kSTYLE_HEAD_POS );
	$test[ kTAG_NID ] = "OBJECT1";
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$id = $test->commit( $wrapper );'.kSTYLE_HEAD_POS );
	$id = $test->commit( $wrapper );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass( $wrapper, $id );'.kSTYLE_HEAD_POS );
	$test = new MyClass( $wrapper, $id );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $test->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Create test object.
	//
	echo( '<h4>Create test object</h4>' );
	$array = array
	(
		kTAG_NID => "ID",
		kTAG_CLASS => "OntologyObject",
		kTAG_DOMAIN => kDOMAIN_UNIT,
		kTAG_NAME => 123,
		-1 => array( "12.47", "35.22", 5.01263, 12 ),
		-3 => array
		(
			kTAG_NAME => 321,
			kTAG_DESCRIPTION => array
			(
				array( kTAG_LANGUAGE => "en",
					   kTAG_TEXT => "Description" ),
				array( kTAG_LANGUAGE => "it",
					   kTAG_TEXT => "Descrizione" ),
				array( kTAG_LANGUAGE => 3,
					   kTAG_TEXT => 4 )
			),
		),
		-2 => array
		(
			array
			(
				-3 => array
				(
					kTAG_CONN_USER => 444,
					kTAG_LABEL => array
					(
						array( kTAG_LANGUAGE => "en",
							   kTAG_TEXT => "Test" ),
						array( kTAG_LANGUAGE => "it",
							   kTAG_TEXT => "Collaudo" ),
						array( kTAG_LANGUAGE => 5,
							   kTAG_TEXT => 6 )
					)
				)
			),
			array
			(
				-2 => array
				(
					array
					(
						-3 => array
						(
							kTAG_CONN_PASS => "secter",
							kTAG_LABEL => array
							(
								array( kTAG_LANGUAGE => "en",
									   kTAG_TEXT => "Test" ),
								array( kTAG_LANGUAGE => "it",
									   kTAG_TEXT => "Collaudo" ),
								array( kTAG_LANGUAGE => 5,
									   kTAG_TEXT => 6 )
							),
						),
					),
					array
					(
						kTAG_CONN_BASE => "database",
						-1 => array( "11.47", "33.47", 88.01263, 92 ),
					)
				)
			)
		),
		kTAG_LABEL => array
		(
			array( kTAG_LANGUAGE => "en",
				   kTAG_TEXT => "Connection" ),
			array( kTAG_LANGUAGE => "it",
				   kTAG_TEXT => "Connessione" ),
			array( kTAG_LANGUAGE => 1,
				   kTAG_TEXT => 2 )
		),
		kTAG_CONN_PORT => "80",
		-4 => array( kTAG_SHAPE_TYPE => 'LineString',
					 kTAG_SHAPE_GEOMETRY => array( array( '25', '32' ),
					 							   array( '37', '42' ),
					 							   array( '59', '63.26' ),
					 							   array( '89', '71.12' ) ) ),
		kTAG_DATA_KIND => array( ':type:ref:term', ':type:int' )
	);
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass( $array );'.kSTYLE_HEAD_POS );
	$test = new MyClass( $array );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test->dictionary( $wrapper );'.kSTYLE_HEAD_POS );
	$test->dictionary( $wrapper );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Inited: <input type="checkbox" disabled="true" '.$test->Inited().'>&nbsp;' );
	echo( 'Dirty: <input type="checkbox" disabled="true" '.$test->Dirty().'>&nbsp;' );
	echo( 'Committed: <input type="checkbox" disabled="true" '.$test->Committed().'>&nbsp;' );
	echo( 'Alias: <input type="checkbox" disabled="true" '.$test->Alias().'>' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $test->getArrayCopy() );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
$test->traverseObject( $tags, $refs, TRUE );
echo( kSTYLE_TABLE_PRE );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_HEAD_PRE.'$test->traverseObject( $tags, $refs, TRUE );'.kSTYLE_HEAD_POS );
$test->traverseObject( $tags, $refs, TRUE );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_HEAD_PRE.'TAGS'.kSTYLE_HEAD_POS );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_DATA_PRE );
var_dump( $tags );
echo( kSTYLE_DATA_POS );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_HEAD_PRE.'REFS'.kSTYLE_HEAD_POS );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_DATA_PRE );
var_dump( $refs );
echo( kSTYLE_DATA_POS );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_HEAD_PRE.'OBJECT'.kSTYLE_HEAD_POS );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_ROW_PRE );
echo( kSTYLE_DATA_PRE );
var_dump( $test->getArrayCopy() );
echo( kSTYLE_DATA_POS );
echo( kSTYLE_ROW_POS );
echo( kSTYLE_TABLE_POS );
echo( '<hr>' );

exit;

	//
	// Commit object.
	//
	echo( '<h4>Commit object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$id = $test->commit();'.kSTYLE_HEAD_POS );
	$id = $test->commit();
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Inited: <input type="checkbox" disabled="true" '.$test->Inited().'>&nbsp;' );
	echo( 'Dirty: <input type="checkbox" disabled="true" '.$test->Dirty().'>&nbsp;' );
	echo( 'Committed: <input type="checkbox" disabled="true" '.$test->Committed().'>&nbsp;' );
	echo( 'Alias: <input type="checkbox" disabled="true" '.$test->Alias().'>' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $test->getArrayCopy() );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Load object.
	//
	echo( '<h4>Load object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass( $wrapper, $id );'.kSTYLE_HEAD_POS );
	$test = new MyClass( $wrapper, $id );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Inited: <input type="checkbox" disabled="true" '.$test->Inited().'>&nbsp;' );
	echo( 'Dirty: <input type="checkbox" disabled="true" '.$test->Dirty().'>&nbsp;' );
	echo( 'Committed: <input type="checkbox" disabled="true" '.$test->Committed().'>&nbsp;' );
	echo( 'Alias: <input type="checkbox" disabled="true" '.$test->Alias().'>' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $test->getArrayCopy() );
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
