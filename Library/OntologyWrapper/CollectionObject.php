<?php

/**
 * CollectionObject.php
 *
 * This file contains the definition of the {@link CollectionObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;
use OntologyWrapper\DatabaseObject;

/*=======================================================================================
 *																						*
 *								CollectionObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Query flags.
 *
 * This file contains the query flag definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Query.inc.php" );

/**
 * Collection object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing database
 * collection instances, this class extends the {@link ConnectionObject} class by
 * implementing an interface for the following functionalities:
 *
 * <ul>
 *	<li><em>Creation interface.</em> All collections can be dropped.
 *	<li><em>Query interface.</em> All collections share the same query framework, one can
 *		match one object, all objects from a selection, or all aobjects in the collection.
 *	<li><em>Modification interface.</em> All collections should allow objects to have their
 *		data members modified without needing to load the whole object.
 *	<li><em>Indexing interface.</em> All collections should allow indexing of object data
 *		members.
 *	<li><em>Time stamps.</em> All collections should provide a native time stamp type.
 *	<li><em>Name.</em> All collections should have a name.
 *	<li><em>Database.</em> All collections should allow creating a database.
 * </ul>
 *
 * In this library we use the MongoDB query language to express selection criteria, when
 * deriving classes that handle different database engines you can translate the Mongo query
 * into the native language of the specific database engine.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
abstract class CollectionObject extends ConnectionObject
{
		

/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * Instantiate class.
	 *
	 * We overload the constructor to instantiate a collection from the provided parameter
	 * if the parent object was not provided.
	 *
	 * @param mixed					$theParameter		Data source name or parameters.
	 * @param ConnectionObject		$theParent			Connection parent.
	 *
	 * @access public
	 *
	 * @uses ServerObject::DefaultOffsets()
	 * @uses DatabaseObject::DefaultOffsets()
	 *
	 * @uses newDatabase()
	 */
	public function __construct( $theParameter = NULL, $theParent = NULL )
	{
		//
		// Call parent constructor.
		//
		parent::__construct( $theParameter, $theParent );

		//
		// Create parent.
		//
		if( ($theParameter !== NULL)
		 && (! ($theParent instanceof ConnectionObject)) )
		{
			//
			// Get server and database parameters.
			//
			$params = Array();
			foreach( array_merge( ServerObject::DefaultOffsets(),
								  DatabaseObject::DefaultOffsets() )
						as $offset )
			{
				if( $this->offsetExists( $offset ) )
					$params[ $offset ] = $this->offsetGet( $offset );
			
			} // Extracting server parameters.
			
			//
			// Instantiate server.
			//
			$this->mParent = $this->newDatabase( $params );
		
		} // Missing parent.
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC CREATION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	drop																			*
	 *==================================================================================*/

	/**
	 * Drop the collection
	 *
	 * This method should drop the current collection.
	 *
	 * @access public
	 */
	abstract public function drop();

		

/*=======================================================================================
 *																						*
 *								PUBLIC MODIFICATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	modify																			*
	 *==================================================================================*/

	/**
	 * Modify object(s)
	 *
	 * This method should modify the objects selected by the provided criteria applying
	 * the provided modifications using the provided options.
	 *
	 * The method will return an array structured as follows:
	 *
	 * <ul>
	 *	<li><tt>affected</tt>: The affected records count.
	 *	<li><tt>modified</tt>: The modified records count.
	 * </ul>
	 *
	 * The format of the provided parameters is dependent on the specific database engine.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param array					$theCriteria		Object selection criteria.
	 * @param array					$theActions			Modification actions.
	 * @param array					$theOptions			Modification options.
	 *
	 * @access public
	 * @return array				Operation status.
	 */
	abstract public function modify( $theCriteria, $theActions, $theOptions );

	 
	/*===================================================================================
	 *	delete																			*
	 *==================================================================================*/

	/**
	 * Delete an object
	 *
	 * The method expects the provided parameter to be either the object itself, or the
	 * object's native identifier.
	 *
	 * The method will return the deleted object's identifier, {@link kTAG_NID}, if the
	 * object was deleted, or raise an exception if the operation could not be completed.
	 *
	 * @param mixed					$theObject			Object or identifier.
	 * @param array					$theOptions			Delete options.
	 *
	 * @access public
	 * @return mixed				Deleted object identifier.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_NID
	 *
	 * @uses isConnected()
	 * @uses deleteIdentifier()
	 */
	public function delete( $theObject, $theOptions = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle object.
			//
			if( $theObject instanceof PersistentObject )
			{
				//
			 	// Check identifier.
			 	//
			 	if( $theObject->offsetExists( kTAG_NID ) )
			 		$theObject = $theObject->offsetGet( kTAG_NID );
			 	
			 	else
					throw new \Exception(
						"Unable to delete object: "
					   ."missing object identifier." );							// !@! ==>
			
			} // Provided object.
				
			return $this->deleteIdentifier( $theObject );							// ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to delete object: "
		   ."connection is not open." );										// !@! ==>
	
	} // delete.

		

/*=======================================================================================
 *																						*
 *								PUBLIC MODIFICATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateReferenceCount															*
	 *==================================================================================*/

	/**
	 * Update reference count
	 *
	 * This method should update the reference count of the provided objects, the method
	 * accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIdent</b>: The object reference or list of references.
	 *	<li><b>$theIdentOffset</b>: The offset corresponding to the provided references,
	 *		this corresponds to a tag sequence number.
	 *	<li><b>$theCountOffset</b>: The offset holding the reference count, this corresponds
	 *		to a tag sequence number.
	 *	<li><b>$theCount</b>: The number by which the count must be incremented.
	 * </ul>
	 *
	 * The method should select all objects whose <tt>$theIdentOffset</tt> matches the list
	 * of references provided in <tt>$theIdent</tt> and for each one increment the value
	 * stored in the <tt>$theCountOffset</tt> by the count provided in <tt>$theCount</tt>.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theIdent			Object identifier or identifiers.
	 * @param string				$theIdentOffset		Object identifier offset.
	 * @param string				$theCountOffset		Reference count offset.
	 * @param integer				$theCount			Reference count delta.
	 *
	 * @access public
	 */
	abstract public function updateReferenceCount( $theIdent,
												   $theIdentOffset,
												   $theCountOffset,
												   $theCount = 1 );

	 
	/*===================================================================================
	 *	updateSet																		*
	 *==================================================================================*/

	/**
	 * Update set
	 *
	 * This method should add or delete the provided elements to and from the set contained
	 * in the provided object reference, the method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIdent</b>: The object reference or list of references.
	 *	<li><b>$theIdentOffset</b>: The offset corresponding to the provided references,
	 *		this corresponds to a tag sequence number.
	 *	<li><b>$theElements</b>: The list of elements to be added or deleted, this is an
	 *		aray structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The offset of the set.
	 *		<li><tt>value</tt>: The value or values to be added.
	 *	 </ul>
	 *	<li><b>$doAdd</b>: If <tt>TRUE</tt> the elements will be added; if <tt>FALSE</tt>
	 *		the elements will be deleted.
	 * </ul>
	 *
	 * The method should select all objects whose <tt>$theIdentOffset</tt> matches the list
	 * of references provided in <tt>$theIdent</tt>, once the object is located, the method
	 * should iterate the elements in <tt>$theElements</tt> adding or removing from the
	 * offset provided in the element key the value or values provided in the element value,
	 * without generating duplicates when adding.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theIdent			Object identifier or identifiers.
	 * @param string				$theIdentOffset		Object identifier offset.
	 * @param array					$theElements		List of elements to be added.
	 * @param boolean				$doAdd				<tt>TRUE</tt> add.
	 *
	 * @access public
	 */
	abstract public function updateSet( $theIdent, $theIdentOffset, $theElements, $doAdd );

	 
	/*===================================================================================
	 *	replaceOffsets																	*
	 *==================================================================================*/

	/**
	 * Replace offsets
	 *
	 * This method should set or replace the provided offsets in the object identified by
	 * the provided native identifier.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIdentifier</b>: The native identifier of the object.
	 *	<li><b>$theProperties</b>: The properties to be added or replaced in the object.
	 * </ul>
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param array					$theProperties		Properties to be added or replaced.
	 *
	 * @access public
	 * @return integer				Number of objects affected (1 or 0).
	 */
	abstract public function replaceOffsets( $theIdentifier, $theProperties );

	 
	/*===================================================================================
	 *	deleteOffsets																	*
	 *==================================================================================*/

	/**
	 * Delete offsets
	 *
	 * This method should delete the provided offsets from the object identified by the
	 * provided native identifier.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIdentifier</b>: The native identifier of the object.
	 *	<li><b>$theOffsets</b>: The offsets to be deleted from the object, only the top
	 *		level offsets, not the offset values.
	 * </ul>
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param array					$theOffsets			Offsets to be deleted.
	 *
	 * @access public
	 * @return integer				Number of objects affected (1 or 0).
	 */
	abstract public function deleteOffsets( $theIdentifier, $theOffsets );

		

/*=======================================================================================
 *																						*
 *							PUBLIC INDEX MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	createIndex																		*
	 *==================================================================================*/

	/**
	 * Create index
	 *
	 * This method should create an index in the current collection related to the provided
	 * parameters:
	 *
	 * <ul>
	 *	<li><b>$theIndex</b>: This is an array indexed by offset with value the index type.
	 *		The index type is specific to the database engine, the parameter is an array in
	 *		order to provide multi-offset indexes.
	 *	<li><b>$theOptions</b>: This array contains the index options, the key represents
	 *		the option and the value the option value. Concrete collection instances will
	 *		have to handle these.
	 * </ul>
	 *
	 * Derived classes must implement this method.
	 *
	 * @param array					$theIndex			Offset to index and index types.
	 * @param array					$theOptions			Index options.
	 *
	 * @access public
	 */
	abstract public function createIndex( $theIndex, $theOptions = Array() );

	 
	/*===================================================================================
	 *	getIndex																		*
	 *==================================================================================*/

	/**
	 * Get index
	 *
	 * This method should return the collection indexes information, the output format is
	 * dependent on the database engine.
	 *
	 * Derived classes must implement this method.
	 *
	 * @access public
	 * @return array				The collection index information.
	 */
	abstract public function getIndex();

	 
	/*===================================================================================
	 *	getIndexedOffsets																*
	 *==================================================================================*/

	/**
	 * Get indexed offsets
	 *
	 * This method should return the list of indexed offsets, the method will return an
	 * array indexed by tag sequence number, with as value the list of indexed offsets.
	 *
	 * Derived classes must implement this method.
	 *
	 * @access public
	 * @return array				The list of indexed offsets.
	 */
	abstract public function getIndexedOffsets();

	 
	/*===================================================================================
	 *	deleteIndex																		*
	 *==================================================================================*/

	/**
	 * Delete index
	 *
	 * This method should delete the index or indexes provided in the parameter. If you omit
	 * the parameter the method should delete all indexes.
	 *
	 * @param mixed					$theIndex			Offset or offsets.
	 *
	 * @access public
	 */
	abstract public function deleteIndex( $theIndex = NULL );

		

/*=======================================================================================
 *																						*
 *									PUBLIC TYPE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getTimeStamp																	*
	 *==================================================================================*/

	/**
	 * Get time-stamp
	 *
	 * This method should return the current time-stamp in the native database format.
	 *
	 * @access public
	 * @return mixed				Native current time-stamp.
	 */
	abstract public function getTimeStamp();

	 
	/*===================================================================================
	 *	parseTimeStamp																	*
	 *==================================================================================*/

	/**
	 * Get time-stamp
	 *
	 * This method should return a formatted time stamp string.
	 *
	 * @param mixed					$theStamp			Time-stamp.
	 *
	 * @access public
	 * @return string				Human readable time-stamp.
	 */
	abstract public function parseTimeStamp( $theStamp );

		

/*=======================================================================================
 *																						*
 *								PUBLIC INFORMATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Return collection name
	 *
	 * This method should return the collection name:
	 *
	 * We implement the method in this class as a fall-back.
	 *
	 * @access public
	 * @return string				Collection name.
	 */
	public function getName()											{	return NULL;	}

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return the {@link kTAG_CONN_COLL} offset.
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_CONN_COLL ) );								// ==>
	
	} // DefaultOffsets;

		

/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newDatabase																		*
	 *==================================================================================*/

	/**
	 * Return a new database instance
	 *
	 * This method should be implemented by concrete derived classes, it expects a list of
	 * offsets or a data source name containing the necessary elements to instantiate a
	 * {@link DatabaseObject} instance which will be considered the current object's parent.
	 *
	 * Note that these parameters must also include the {@link ServerObject} parameters.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theParameter		Database parameters.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access protected
	 * @return DatabaseObject		Database instance.
	 */
	abstract protected function newDatabase( $theParameter, $doOpen = TRUE );

		

/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	deleteIdentifier																*
	 *==================================================================================*/

	/**
	 * Delete provided identifier
	 *
	 * This method should be implemented by concrete derived classes, it should delete the
	 * object matched by the provided identifier, if the object was matched, the method
	 * should return the identifier, if not, it should return <tt>NULL</tt>.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier or <tt>NULL</tt>.
	 */
	abstract protected function deleteIdentifier( $theIdentifier, $theOptions );

	 

} // class CollectionObject.


?>
