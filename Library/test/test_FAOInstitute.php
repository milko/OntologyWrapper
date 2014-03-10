<?php

/**
 * {@link FAOInstitute} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link FAOInstitute} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/03/2014
 */

/*=======================================================================================
 *																						*
 *									test_FAOInstitute.php								*
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
class MyClass extends OntologyWrapper\FAOInstitute
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
	
	protected function getOffsetTypes( $theOffset, &$theType, &$theKind )
	{
		switch( $theOffset )
		{
			case -1:
				$theType = array( kTYPE_FLOAT );
				$theKind = array( kTYPE_LIST );
				return TRUE;
			
			case -2:
				$theType = array( kTYPE_STRUCT );
				$theKind = array( kTYPE_LIST );
				return TRUE;
			
			case -3:
				$theType = array( kTYPE_STRUCT );
				$theKind = Array();
				return TRUE;
		}

		return parent::getOffsetTypes( $theOffset, $theType, $theKind );
	}
	
	public function Inited()	{	return ( $this->isInited() ) ? 'checked="1"' : '';	}
	public function Dirty()		{	return ( $this->isDirty() ) ? 'checked="1"' : '';	}
	public function Committed()	{	return ( $this->isCommitted() ) ? 'checked="1"' : '';	}
	public function Alias()		{	return ( $this->isAlias() ) ? 'checked="1"' : '';	}
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
	$meta->drop();
	$wrapper->Entities(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	$wrapper->Units(
		new OntologyWrapper\MongoDatabase(
			"mongodb://localhost:27017/TEST?connect=1" ) );
	
	//
	// Reset ontology.
	//
	$wrapper->resetOntology();
	
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
		echo( '<hr>' );

		//
		// Create test object.
		//
		echo( '<h4>Create test object</h4>' );
		$array = array
		(
			kTAG_AUTHORITY => 'authority',
			kTAG_COLLECTION => 'collection',
			kTAG_IDENTIFIER => 'id',
			kTAG_VERSION => 'version',
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
			kTAG_DATA_TYPE => array( ':type:ref:term', ':type:int' )
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

		//
		// Test collect main properties.
		//
		echo( '<h4>Test collect main properties</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->collectProperties( $tags, $refs, FALSE );'.kSTYLE_HEAD_POS );
		$test->collectProperties( $tags, $refs, FALSE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $tags ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $refs ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );

		//
		// Test collect all properties.
		//
		echo( '<h4>Test collect all properties</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$test->collectProperties( $tags, $refs, TRUE );'.kSTYLE_HEAD_POS );
		$test->collectProperties( $tags, $refs, TRUE );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $tags ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_DATA_PRE );
		echo( '<pre>' ); print_r( $refs ); echo( '</pre>' );
		echo( kSTYLE_DATA_POS );
		echo( kSTYLE_ROW_POS );
		echo( kSTYLE_TABLE_POS );
		echo( '<hr>' );
		
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
		// Load object.
		//
		echo( '<h4>Load object</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE );
		echo( '$test[ kTAG_AUTHORITY ] = "authority";<br />' );
		$test[ kTAG_AUTHORITY ] = "authority";
		echo( '$test[ kTAG_COLLECTION ] = "collection";<br />' );
		$test[ kTAG_COLLECTION ] = "collection";
		echo( '$test[ kTAG_IDENTIFIER ] = "identifier";<br />' );
		$test[ kTAG_IDENTIFIER ] = "identifier";
		echo( '$test[ kTAG_VERSION ] = "version";<br />' );
		$test[ kTAG_VERSION ] = "version";
		echo( '$test[ kTAG_NAME ] = "NAME";<br />' );
		$test[ kTAG_NAME ] = "NAME";
		echo( kSTYLE_HEAD_POS );
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

		//
		// Commit object.
		//
		echo( '<h4>Commit object</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$id = $test->commit( $wrapper );'.kSTYLE_HEAD_POS );
		$id = $test->commit( $wrapper );
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
		// Load default object.
		//
		echo( '<h4>Load default object</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE );
		echo( '$test[ kTAG_AUTHORITY ] = "AUTHORITY";<br />' );
		$test[ kTAG_AUTHORITY ] = "AUTHORITY";
		echo( '$test[ kTAG_COLLECTION ] = "COLLECTION";<br />' );
		$test[ kTAG_COLLECTION ] = "COLLECTION";
		echo( '$test[ kTAG_IDENTIFIER ] = "IDENTIFIER";<br />' );
		$test[ kTAG_IDENTIFIER ] = "IDENTIFIER";
		echo( '$test[ kTAG_VERSION ] = "VERSION";<br />' );
		$test[ kTAG_VERSION ] = "VERSION";
		echo( kSTYLE_HEAD_POS );
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
	
		//
		// Load current object.
		//
		echo( '<h4>Load current object</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE );
		echo( '$test[ kTAG_NAME ] = "Entity name";<br />' );
		$test[ kTAG_NAME ] = "Entity name";
		echo( '$test->EntityType( ":type:entity:211", TRUE );<br />' );
		$test->EntityType( ":type:entity:211", TRUE );
		echo( '$test->EntityType( ":type:entity:300", TRUE );<br />' );
		$test->EntityType( ":type:entity:300", TRUE );
		echo( '$test->EntityKind( ":kind:entity:100", TRUE );<br />' );
		$test->EntityKind( ":kind:entity:100", TRUE );
		echo( '$test->EntityKind( ":kind:entity:200", TRUE );<br />' );
		$test->EntityKind( ":kind:entity:200", TRUE );
		echo( '$test->EntityMail( "work", "Via dei Tre Denari, 472/a\n00057 Maccarese (RM)\nITALIA" );<br />' );
		$test->EntityMail( "work", "Via dei Tre Denari, 472/a\n00057 Maccarese (RM)\nITALIA" );
		echo( '$test->EntityMail( "home", "Via leMani dal Naso, 25\n00057 Caccarese (RM)\nITALIA" );<br />' );
		$test->EntityMail( "home", "Via leMani dal Naso, 25\n00057 Caccarese (RM)\nITALIA" );
		echo( '$test->EntityEmail( "work", "me@mail.com" );<br />' );
		$test->EntityEmail( "work", "me@mail.com" );
		echo( '$test->EntityEmail( "home", "me@mail.net" );<br />' );
		$test->EntityEmail( "home", "me@mail.net" );
		echo( '$test->EntityPhone( "work", "+39 06 6118286" );<br />' );
		$test->EntityPhone( "work", "+39 06 6118286" );
		echo( '$test->EntityPhone( "home", "+39 06 7118563" );<br />' );
		$test->EntityPhone( "home", "+39 06 7118563" );
		echo( '$test->EntityFax( NULL, "+39 06 7118565" );<br />' );
		$test->EntityFax( NULL, "+39 06 7118565" );
		echo( '$test[ kTAG_ENTITY_COUNTRY ] = "ITA";<br />' );
		$test[ kTAG_ENTITY_COUNTRY ] = "ITA";
		echo( '$test->EntityAffiliation( "default", ":domain:organisation://authority/collection:id;" );' );
		$test->EntityAffiliation( "default", ":domain:organisation://authority/collection:id;" );
		echo( kSTYLE_HEAD_POS );
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

		//
		// Commit object.
		//
		echo( '<h4>Commit object</h4>' );
		echo( kSTYLE_TABLE_PRE );
		echo( kSTYLE_ROW_PRE );
		echo( kSTYLE_HEAD_PRE.'$id = $test->commit( $wrapper );'.kSTYLE_HEAD_POS );
		$id = $test->commit( $wrapper );
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
	} echo( '<hr>' );
	
	//
	// Header.
	//
	if( kDEBUG_PARENT )
		echo( "<h3>Current class test</h3>" );

	//
	// Test FAO identifier.
	//
	echo( '<h4>Test FAO identifier</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$id = MyClass::FAOIdentifier( "ITA303" );'.kSTYLE_HEAD_POS );
	$id = MyClass::FAOIdentifier( "ITA303" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Instantiate object.
	//
	echo( '<h4>Instantiate object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test = new MyClass();' );
	$test = new MyClass();
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test->instituteCode( "ITA303" );<br/>' );
	$test->instituteCode( "ITA303" );
	echo( '$test[ kTAG_NAME ] = "IPGRI International";<br/>' );
	$test[ kTAG_NAME ] = "IPGRI International";
	echo( '$test->EntityAcronym( "IPGRI", TRUE );<br/>' );
	$test->EntityAcronym( "IPGRI", TRUE );
	echo( '$test->EntityAcronym( "Bioversity", TRUE );' );
	$test->EntityAcronym( "Bioversity", TRUE );
	echo( kSTYLE_HEAD_POS );
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

	//
	// Commit object.
	//
	echo( '<h4>Commit object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$id = $test->commit( $wrapper );'.kSTYLE_HEAD_POS );
	$id = $test->commit( $wrapper );
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
	// Maintain institutes.
	//
	echo( '<h4>Maintain institutes</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$stat = MyClass::Maintain( $wrapper );'.kSTYLE_HEAD_POS );
	$stat = MyClass::Maintain( $wrapper );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $stat );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Maintain again institutes.
	//
	echo( '<h4>Maintain again institutes</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$stat = MyClass::Maintain( $wrapper );'.kSTYLE_HEAD_POS );
	$stat = MyClass::Maintain( $wrapper );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $stat );
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
