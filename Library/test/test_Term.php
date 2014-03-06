<?php

/**
 * {@link Term} test suite.
 *
 * This file contains routines to test and demonstrate the behaviour of the
 * {@link Term} class.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Test
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 08/02/2014
 */

/*=======================================================================================
 *																						*
 *										test_Term.php									*
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
class MyClass extends OntologyWrapper\Term
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
			kTAG_NID => "ID",
			kTAG_CLASS => "OntologyObject",
			kTAG_DOMAIN => ":type",
			kTAG_NAME => 123,
			kTAG_ID_LOCAL => 'id',
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
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass( $wrapper );'.kSTYLE_HEAD_POS );
	$test = new MyClass( $wrapper );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set namespace.
	//
	echo( '<h4>Test set namespace</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test[ kTAG_NAMESPACE ] = "namespace";'.kSTYLE_HEAD_POS );
	$test[ kTAG_NAMESPACE ] = "namespace";
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
	echo( kSTYLE_HEAD_PRE.'$test[ kTAG_ID_LOCAL ] = "local-id";'.kSTYLE_HEAD_POS );
	$test[ kTAG_ID_LOCAL ] = "local-id";
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
	echo( kSTYLE_HEAD_PRE.'$test[ kTAG_NAMESPACE ] = NULL;'.kSTYLE_HEAD_POS );
	$test[ kTAG_NAMESPACE ] = NULL;
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
	echo( kSTYLE_HEAD_PRE.'$other[ kTAG_ID_LOCAL ] = "other";'.kSTYLE_HEAD_POS );
	$other[ kTAG_ID_LOCAL ] = "other";
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$other->Label( "it", "Quaquaraqua!" );'.kSTYLE_HEAD_POS );
	$other->Label( "it", "Quaquaraqua!" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$other[ kTAG_NAMESPACE ] = $test;'.kSTYLE_HEAD_POS );
	$other[ kTAG_NAMESPACE ] = $test;
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Inited: <input type="checkbox" disabled="true" '.$other->Inited().'>&nbsp;' );
	echo( 'Dirty: <input type="checkbox" disabled="true" '.$other->Dirty().'>&nbsp;' );
	echo( 'Committed: <input type="checkbox" disabled="true" '.$other->Committed().'>&nbsp;' );
	echo( 'Alias: <input type="checkbox" disabled="true" '.$other->Alias().'>' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $other->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Commit object.
	//
	echo( '<h4>Commit object<br /><i>should replace the namespace object by its reference"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$other->commit( $wrapper );'.kSTYLE_HEAD_POS );
	$other->commit( $wrapper );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $other->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Test set new namespace by object.
	//
	echo( '<h4>Test set new namespace by object<br /><i>should set "global-id"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$new = new MyClass( $wrapper );<br/>' );
	$new = new MyClass( $wrapper );
	echo( '$new[ kTAG_ID_LOCAL ] = "new";<br/>' );
	$new[ kTAG_ID_LOCAL ] = "new";
	echo( '$new->Label( "it", "New object" );' );
	$new->Label( "it", "New object" );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$cont = new MyClass();'.kSTYLE_HEAD_POS );
	$cont = new MyClass();
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$cont[ kTAG_ID_LOCAL ] = "cont";'.kSTYLE_HEAD_POS );
	$cont[ kTAG_ID_LOCAL ] = "cont";
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$cont->Label( "it", "Quaquaraqua!" );'.kSTYLE_HEAD_POS );
	$cont->Label( "it", "Quaquaraqua!" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$cont[ kTAG_NAMESPACE ] = $new;'.kSTYLE_HEAD_POS );
	$cont[ kTAG_NAMESPACE ] = $new;
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Inited: <input type="checkbox" disabled="true" '.$cont->Inited().'>&nbsp;' );
	echo( 'Dirty: <input type="checkbox" disabled="true" '.$cont->Dirty().'>&nbsp;' );
	echo( 'Committed: <input type="checkbox" disabled="true" '.$cont->Committed().'>&nbsp;' );
	echo( 'Alias: <input type="checkbox" disabled="true" '.$cont->Alias().'>' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $cont->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );

	//
	// Commit object.
	//
	echo( '<h4>Commit object<br /><i>should replace the namespace object by its reference"</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$cont->commit( $wrapper );'.kSTYLE_HEAD_POS );
	$cont->commit( $wrapper );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $cont->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$new = new MyClass( $wrapper, "new" );'.kSTYLE_HEAD_POS );
	$new = new MyClass( $wrapper, "new" );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Inited: <input type="checkbox" disabled="true" '.$new->Inited().'>&nbsp;' );
	echo( 'Dirty: <input type="checkbox" disabled="true" '.$new->Dirty().'>&nbsp;' );
	echo( 'Committed: <input type="checkbox" disabled="true" '.$new->Committed().'>&nbsp;' );
	echo( 'Alias: <input type="checkbox" disabled="true" '.$new->Alias().'>' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $new->getArrayCopy() ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Instantiate and load object.
	//
	echo( '<h4>Instantiate and load object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass();'.kSTYLE_HEAD_POS );
	$test = new MyClass();
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$test[ kTAG_ID_LOCAL ] = "namespace";'.'<br \>' );
	$test[ kTAG_ID_LOCAL ] = "namespace";
	echo( '$test->Label( "en", "Namespace" );'.'<br \>' );
	$test->Label( "en", "Namespace" );
	echo( '$test->Definition( "en", "This is a namespace" );'.'<br \>' );
	$test->Definition( "en", "This is a namespace" );
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
	// Instantiate loaded object.
	//
	echo( '<h4>Instantiate loaded object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$data = $test->getArrayCopy();'.kSTYLE_HEAD_POS );
	$data = $test->getArrayCopy();
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$test = new MyClass( $data );'.kSTYLE_HEAD_POS );
	$test = new MyClass( $data );
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
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Instantiate from committed.
	//
	echo( '<h4>Instantiate from committed</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$data = $test->getArrayCopy();<br />' );
	$data = $test->getArrayCopy();
	echo( '$test = new MyClass( $wrapper, $data );' );
	$test = new MyClass( $wrapper, $data );
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
	echo( '<pre>' ); print_r( $test ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Instantiate and load other object.
	//
	echo( '<h4>Instantiate and load other object</h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$other = new MyClass();'.kSTYLE_HEAD_POS );
	$other = new MyClass();
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( '$other[ kTAG_NAMESPACE ] = $test;'.'<br \>' );
	$other[ kTAG_NAMESPACE ] = $test;
	echo( '$other[ kTAG_ID_LOCAL ] = "term";'.'<br \>' );
	$other[ kTAG_ID_LOCAL ] = "term";
	echo( '$other->Label( "en", "Term" );'.'<br \>' );
	$other->Label( "en", "Term" );
	echo( '$other->Definition( "en", "This is a term" );'.'<br \>' );
	$other->Definition( "en", "This is a term" );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$id = $other->commit( $wrapper );'.kSTYLE_HEAD_POS );
	$id = $other->commit( $wrapper );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Inited: <input type="checkbox" disabled="true" '.$other->Inited().'>&nbsp;' );
	echo( 'Dirty: <input type="checkbox" disabled="true" '.$other->Dirty().'>&nbsp;' );
	echo( 'Committed: <input type="checkbox" disabled="true" '.$other->Committed().'>&nbsp;' );
	echo( 'Alias: <input type="checkbox" disabled="true" '.$other->Alias().'>' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $id );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	echo( '<pre>' ); print_r( $other ); echo( '</pre>' );
	echo( kSTYLE_DATA_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_TABLE_POS );
	echo( '<hr>' );
	
	//
	// Insert again.
	//
	echo( '<h4>Insert again<br /><i>will raise an exception if inserted (should not)</i></h4>' );
	echo( kSTYLE_TABLE_PRE );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE.'$id = $other->commit( $wrapper );'.kSTYLE_HEAD_POS );
	$id = $other->commit( $wrapper );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_HEAD_PRE );
	echo( 'Inited: <input type="checkbox" disabled="true" '.$other->Inited().'>&nbsp;' );
	echo( 'Dirty: <input type="checkbox" disabled="true" '.$other->Dirty().'>&nbsp;' );
	echo( 'Committed: <input type="checkbox" disabled="true" '.$other->Committed().'>&nbsp;' );
	echo( 'Alias: <input type="checkbox" disabled="true" '.$other->Alias().'>' );
	echo( kSTYLE_HEAD_POS );
	echo( kSTYLE_ROW_POS );
	echo( kSTYLE_ROW_PRE );
	echo( kSTYLE_DATA_PRE );
	var_dump( $id );
	echo( kSTYLE_DATA_POS );
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
