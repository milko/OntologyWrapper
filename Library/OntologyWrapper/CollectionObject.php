<?php

/**
 * CollectionObject.php
 *
 * This file contains the definition of the {@link CollectionObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;

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
	 *	resolveIdentifier																*
	 *==================================================================================*/

	/**
	 * Resolve an identifier
	 *
	 * This method should select an object in the current collection matching the provided
	 * identifier and return its contents as an array.
	 *
	 * The main function of this method is to determine what the caller is looking for based
	 * on the provided identifier and the nature of the current collection.
	 *
	 * Concrete derived classes should implement this method.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 * @return array				Found object as an array, or <tt>NULL</tt>.
	 */
	abstract public function resolveIdentifier( $theIdentifier );

		

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
