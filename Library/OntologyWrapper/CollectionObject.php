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
	/**
	 * Object offsets.
	 *
	 * This static data member holds the list of default offsets used by collection objects.
	 *
	 * @var array
	 */
	static $sOffsets = array( kTAG_CONN_COLL );

		

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
	 * @see ServerObject::$sOffsets DatabaseObject::$sOffsets
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
			foreach( array_merge( ServerObject::$sOffsets, DatabaseObject::$sOffsets )
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
	 *	insert																			*
	 *==================================================================================*/

	/**
	 * Insert an object
	 *
	 * The method expects the provided parameter to be either an array or an
	 * {@link ArrayObject} instance.
	 *
	 * The method will call the virtual {@link insertData()} method, passing the received
	 * object to it, which will perform the actual insert.
	 *
	 * The method will return the inserted object's identifier, {@link kTAG_NID}.
	 *
	 * This method will also take care of setting the {@link kTAG_CLASS} offset.
	 *
	 * @param reference				$theObject			Object to insert.
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
	public function insert( &$theObject, $theOptions = Array() )
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
				"Unable to insert object: "
			   ."provided invalid or unsupported data type." );					// !@! ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to insert object: "
		   ."connection is not open." );										// !@! ==>
	
	} // insert.

	 
	/*===================================================================================
	 *	resolve																			*
	 *==================================================================================*/

	/**
	 * Resolve an object
	 *
	 * This method should select an object in the current collection matching the provided
	 * identifier with the provided offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIdentifier</b>: This parameter represents the value to match with the
	 *		provided offset.
	 *	<li><b>$theOffset</b>: This parameter represents either the native or persistent
	 *		identifier of the offset tag.
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
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param mixed					$theOffset			Offset.
	 * @param mixed					$asObject			Return object if <tt>TRUE</tt>.
	 *
	 * @access public
	 * @return mixed				Found object, array, objects count or <tt>NULL</tt>.
	 */
	abstract public function resolve( $theIdentifier, $theOffset = kTAG_NID,
													  $asObject = TRUE );

		

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
	 *
	 * @access protected
	 * @return DatabaseObject		Database instance.
	 */
	abstract protected function newDatabase( $theParameter );

		

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
	 * This method should be implemented by concrete derived classes, it should insert a
	 * new record in the current collection featuring the provided data and return the
	 * record identifier.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param reference				$theData			Data to insert.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier.
	 */
	abstract protected function insertData( &$theData, &$theOptions );

	 

} // class CollectionObject.


?>
