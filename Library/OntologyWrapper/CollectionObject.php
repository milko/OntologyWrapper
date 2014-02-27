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
 * Collection object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing database
 * collection instances, this class extends the {@link ConnectionObject} class to implement
 * collection specific functionality prototypes.
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
	 * We overload the constructor to instantiate a database from the provided parameter if
	 * the parent object was not provided.
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
		
		} // Mising parent.
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
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

	 
	/*===================================================================================
	 *	commit																			*
	 *==================================================================================*/

	/**
	 * Insert an object
	 *
	 * The method expects the provided parameter to be either an array or an
	 * {@link ArrayObject} instance.
	 *
	 * The method will call the virtual {@link insertData()} method, passing the received
	 * object to it, which will perform the actual commit.
	 *
	 * The method will return the inserted object's identifier, {@link kTAG_NID}.
	 *
	 * This method will also take care of setting the {@link kTAG_CLASS} offset.
	 *
	 * @param reference				$theObject			Object to commit.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access public
	 * @return mixed				Inserted object identifier.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_CLASS
	 *
	 * @uses isConnected()
	 * @uses insertData()
	 */
	public function commit( &$theObject, $theOptions = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Check object type.
			//
			if( is_array( $theObject )
			 || ($theObject instanceof \ArrayObject) )
			{
			 	//
			 	// Set class.
			 	//
			 	if( is_object( $theObject ) )
				 	$theObject[ kTAG_CLASS ]
				 		= get_class( $theObject );
			 	
				return $this->insertData( $theObject, $theOptions );				// ==>
			 
			 } // Correct type.
			
			throw new \Exception(
				"Unable to commit object: "
			   ."provided invalid or unsupported data type." );					// !@! ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to commit object: "
		   ."connection is not open." );										// !@! ==>
	
	} // commit.

	 
	/*===================================================================================
	 *	resolve																			*
	 *==================================================================================*/

	/**
	 * Resolve an object
	 *
	 * This method should select an object in the current collection matching the provided
	 * identifier with the provided value.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: This parameter represents the value to match with the provided
	 *		offset.
	 *	<li><b>$theOffset</b>: This parameter represents the offset to match.
	 *	<li><b>$asObject</b>: This parameter determines what the method should return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the object; if there are more than one objects
	 *			selected, this method should only return the first.
	 *		<li><tt>FALSE</tt>: Return the object array; if there are more than one objects
	 *			selected, this method should only return the first.
	 *		<li><tt>NULL</tt>: Return the objects count.
	 *	 </ul>
	 * </ul>
	 *
	 * Concrete derived classes should implement this method.
	 *
	 * @param mixed					$theValue			Offset value.
	 * @param mixed					$theOffset			Offset.
	 * @param mixed					$asObject			What to return.
	 *
	 * @access public
	 * @return mixed				Found object, array, objects count or <tt>NULL</tt>.
	 */
	abstract public function resolve( $theValue, $theOffset = kTAG_NID, $asObject = TRUE );

	 
	/*===================================================================================
	 *	getAll																			*
	 *==================================================================================*/

	/**
	 * Return all objects
	 *
	 * This method should select all the objects of the collection and return an iterator.
	 *
	 * Concrete derived classes should implement this method.
	 *
	 * @access public
	 * @return Iterator				Selection of all objects of the collection.
	 */
	abstract public function getAll();

		

/*=======================================================================================
 *																						*
 *							PUBLIC SEQUENCE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setSequenceNumber																*
	 *==================================================================================*/

	/**
	 * Set sequence number
	 *
	 * This method should initialise a sequence number associated to the provided parameter.
	 * This operation is equivalent to resetting an auto-number for a database.
	 *
	 * Once the sequence is set, the next requested sequence number will hold the value set
	 * by this method, so to start counting from <tt>1</tt> you should provide this value to
	 * this method.
	 *
	 * This method is intended to be handled by database objects, in this class we simply
	 * let the object's parent, a database, perform the action.
	 *
	 * Derived classes should never need to overload this method.
	 *
	 * @param string				$theSequence		Sequence selector.
	 * @param integer				$theNumber			Sequence number.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function setSequenceNumber( $theSequence, $theNumber = 1 )
	{
		//
		// Check parent.
		//
		if( ! ($this->mParent instanceof DatabaseObject) )
			throw new \Exception(
				"Unable to set sequence number: "
			   ."the collection is missing its database." );					// !@! ==>
		
		//
		// Let papa do it.
		//
		$this->mParent->setSequenceNumber( $theSequence, $theNumber );
	
	} // setSequenceNumber.

	 
	/*===================================================================================
	 *	getSequenceNumber																*
	 *==================================================================================*/

	/**
	 * Return sequence number
	 *
	 * This method should return a sequence number associated to the provided parameter.
	 * This operation is equivalent to requesting an auto-number for a database.
	 *
	 * Each time a sequence number is requested, the sequence seed is updated, so use this
	 * method only when the sequence is required.
	 *
	 * If the sequence selector is not found, a new one will be created starting with the
	 * number <tt>1</tt>, so, if you need to start with another number, use the
	 * {@link setSequenceNumber()} before.
	 *
	 * This method is intended to be handled by database objects, in this class we simply
	 * let the object's parent, a database, perform the action.
	 *
	 * Derived classes should never need to overload this method.
	 *
	 * @param string				$theSequence		Sequence selector.
	 *
	 * @access public
	 * @return integer				Sequence number.
	 */
	public function getSequenceNumber( $theSequence )
	{
		//
		// Check parent.
		//
		if( ! ($this->mParent instanceof DatabaseObject) )
			throw new \Exception(
				"Unable to get sequence number: "
			   ."the collection is missing its database." );					// !@! ==>
		
		return $this->mParent->getSequenceNumber( $theSequence );				 // ==>
	
	} // setSequenceNumber.

		

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
	abstract public function createIndex( $theIndex, $theOptions );

	 
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
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateReferenceCount															*
	 *==================================================================================*/

	/**
	 * Update reference count
	 *
	 * This method should update the reference count for the object identified by the
	 * provided parameters.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIdentifier</b>: The native identifier of the object.
	 *	<li><b>$theReferenceOffset</b>: The offset of the object holding the reference
	 *		count.
	 *	<li><b>$theReferenceCount</b>: The number by which the count must be incremented.
	 * </ul>
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param string				$theReferenceOffset	Reference count offset.
	 * @param integer				$theReferenceCount	Reference count value.
	 *
	 * @access public
	 */
	abstract public function updateReferenceCount( $theIdentifier,
												   $theReferenceOffset,
												   $theReferenceCount );

	 
	/*===================================================================================
	 *	updateTagOffsets																*
	 *==================================================================================*/

	/**
	 * Update tag offsets
	 *
	 * This method should add the provided offsets to the tag referenced by the provided
	 * parameter.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTag</b>: The native identifier of the tag.
	 *	<li><b>$theOffsets</b>: The list of offsets for that tag.
	 * </ul>
	 *
	 * Derived classes must implement this method.
	 *
	 * @param int					$theTag				Tag native identifier.
	 * @param array					$theOffsets			List of tag offsets.
	 *
	 * @access public
	 */
	abstract public function updateTagOffsets( $theTag, $theOffsets );

		

/*=======================================================================================
 *																						*
 *								STATIC OFFSET INTERFACE									*
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
	 *	insertData																		*
	 *==================================================================================*/

	/**
	 * Insert provided data
	 *
	 * This method should be implemented by concrete derived classes, it should commit a
	 * new record in the current collection featuring the provided data and return the
	 * record identifier.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param reference				$theData			Data to commit.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier.
	 */
	abstract protected function insertData( &$theData, &$theOptions );

	 

} // class CollectionObject.


?>
