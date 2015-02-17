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
	 *	replaceOffsets																	*
	 *==================================================================================*/

	/**
	 * Replace offsets
	 *
	 * This method should be used to replace or remove offsets in the object matching the
	 * provided criteria.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: Object selection criteria or object identifier:
	 *	  <ul>
	 *		<li><tt>array</tt>: The selection criteria in MongoDB standard.
	 *		<li><em>other</em>: The object native identifier.
	 *	  </ul>
	 *	<li><b>$theProperties</b>: The properties to be replaced or removed, it is an array
	 *		structured as follows:
	 *	  <ul>
	 *		<li><em>index</em>: The offset.
	 *		<li><em>value</em>: The the value:
	 *		  <ul>
	 *			<li><tt>NULL</tt>: The offset will be removed.
	 *			<li><em>other</em>: The offset value will be replaced with the provided
	 *				value.
	 *		  </ul>
	 *	  </ul>
	 * </ul>
	 *
	 * If the matched objects do not feature the property, this will be added; unmatched
	 * objects will not be created.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theCriteria		Object selection criteria or id.
	 * @param array					$theProperties		Properties to be added or replaced.
	 *
	 * @access public
	 * @return integer				Number of objects affected.
	 */
	abstract public function replaceOffsets( $theCriteria, $theProperties );

	 
	/*===================================================================================
	 *	updateSet																		*
	 *==================================================================================*/

	/**
	 * Update set
	 *
	 * This method should either add or remove the provided values from the sets in the
	 * objects matching the provided criteria.
	 *
	 * The method will treat the target properties as sets, by preventing duplicate
	 * elements. The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: Object selection criteria or object identifier:
	 *	  <ul>
	 *		<li><tt>array</tt>: The selection criteria in MongoDB standard.
	 *		<li><em>other</em>: The object native identifier.
	 *	  </ul>
	 *	<li><b>$theElements</b>: The list of set offsets and values as an array structured
	 *		as follows:
	 *	 <ul>
	 *		<li><em>key</em>: The set offset.
	 *		<li><em>value</em>: The values to be added or removed.
	 *	 </ul>
	 *	<li><b>$doAdd</b>: If <tt>TRUE</tt> the elements will be added; if <tt>FALSE</tt>
	 *		the elements will be deleted.
	 * </ul>
	 *
	 * The method will select the objects according to the provided criteria and either
	 * remove matching values, or add the provided values preventing duplicate elements in
	 * the set.
	 *
	 * If the matched objects do not feature the property, this will be added; unmatched
	 * objects will not be created.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theCriteria		Object selection criteria or id.
	 * @param array					$theElements		List of elements to be added.
	 * @param boolean				$doAdd				<tt>TRUE</tt> add.
	 *
	 * @access public
	 */
	abstract public function updateSet( $theCriteria, $theElements, $doAdd );

		
	/*===================================================================================
	 *	updateStructList																*
	 *==================================================================================*/

	/**
	 * Update list of structures
	 *
	 * This method should either add or remove structures from the objects selected by the
	 * provided criteria.
	 *
	 * The method expects the target properties to be lists of structures, if you want to
	 * remove elements from a set, use the {@link updateSet()} method.
	 *
	 * These are the parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: Object selection criteria or object identifier:
	 *	  <ul>
	 *		<li><tt>array</tt>: The selection criteria in MongoDB standard.
	 *		<li><em>other</em>: The object native identifier.
	 *	  </ul>
	 *	<li><b>$theElements</b>: The list of offsets and structures as an array structured
	 *		as follows:
	 *	 <ul>
	 *		<li><em>key</em>: The structure offset.
	 *		<li><em>value</em>: Depending on whether to add or remove:
	 *		  <ul>
	 *			<li><em>Add</em>: The structure to be added.
	 *			<li><em>Delete</em>: The selection criteria for the elements to be removed
	 *				in MongoDB standard.
	 *		  </ul>
	 *	 </ul>
	 *	<li><b>$doAdd</b>: If <tt>TRUE</tt> the elements will be added; if <tt>FALSE</tt>
	 *		the elements will be deleted.
	 * </ul>
	 *
	 * The provided offsets must correspond to list of structures, the method will select
	 * the objects matching the provided criteria and either add the values of the provided
	 * array, or use the values of the provided array as a selector to determine which
	 * elements of the structures list should be removed.
	 *
	 * If the matched objects do not feature the property, this will be added; unmatched
	 * objects will not be created.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theCriteria		Object selection criteria or id.
	 * @param array					$theElements		List of structures to be added.
	 * @param boolean				$doAdd				<tt>TRUE</tt> add.
	 *
	 * @access public
	 */
	abstract public function updateStructList( $theCriteria, $theElements, $doAdd );

	 
	/*===================================================================================
	 *	updateReferenceCount															*
	 *==================================================================================*/

	/**
	 * Update reference count
	 *
	 * This method should update the reference count of the provided offsets in the objects
	 * selected by the provided criteria, the methodaccepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: Object selection criteria or object identifier:
	 *	  <ul>
	 *		<li><tt>array</tt>: The selection criteria in MongoDB standard.
	 *		<li><em>other</em>: The object native identifier.
	 *	  </ul>
	 *	<li><b>$theElements</b>: The list of counter offsets and increments as an array:
	 *	 <ul>
	 *		<li><em>key</em>: The counter offset.
	 *		<li><em>value</em>: The counter increment.
	 *	 </ul>
	 * </ul>
	 *
	 * The provided offsets must correspond to numeric properties, the method will select
	 * the objects matching the provided criteria and apply the provided increment to the
	 * corresponding offsets.
	 *
	 * If the matched objects do not feature the property, this will be set to the provided
	 * increment; unmatched objects will not be created.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theCriteria		Object selection criteria or id.
	 * @param array					$theElements		List of offsets and increments.
	 *
	 * @access public
	 */
	abstract public function updateReferenceCount( $theCriteria, $theElements );

	 

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
	 * @param array					$theOptions			Delete options.
	 *
	 * @access protected
	 * @return mixed				Object identifier or <tt>NULL</tt>.
	 */
	abstract protected function deleteIdentifier( $theIdentifier, $theOptions = Array() );

	 

} // class CollectionObject.


?>
