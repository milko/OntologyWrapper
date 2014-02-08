<?php

/**
 * DatabaseObject.php
 *
 * This file contains the definition of the {@link DatabaseObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;

/*=======================================================================================
 *																						*
 *									DatabaseObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Database object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing database
 * connection instances, this class extends the {@link ConnectionObject} class to implement
 * database specific functionality prototypes.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
abstract class DatabaseObject extends ConnectionObject
{
	/**
	 * Object offsets.
	 *
	 * This static data member holds the list of default offsets used by database objects.
	 *
	 * @var array
	 */
	static $sOffsets = array( kTAG_CONN_BASE );

		

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
	 * We overload the constructor to instantiate a server from the provided parameter, if
	 * the parent object was not provided, and set it as the parent.
	 *
	 * @param mixed					$theParameter		Data source name or parameters.
	 * @param ConnectionObject		$theParent			Connection parent.
	 *
	 * @access public
	 *
	 * @see ServerObject::$sOffsets
	 *
	 * @uses newServer()
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
			// Get server parameters.
			//
			$params = Array();
			foreach( ServerObject::$sOffsets as $offset )
			{
				if( $this->offsetExists( $offset ) )
					$params[ $offset ] =
						$this->offsetGet( $offset );
			
			} // Extracting server parameters.
			
			//
			// Instantiate server.
			//
			$this->mParent = $this->newServer( $params );
		
		} // Missing parent.
		
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
	 * Drop the database
	 *
	 * This method should drop the current database.
	 *
	 * @access public
	 */
	abstract public function drop();

		

/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Collection																		*
	 *==================================================================================*/

	/**
	 * Return collection connection
	 *
	 * This method can be used to return a collection connection from the current database.
	 *
	 * The method expects a single parameter which represents the collection name, the
	 * method should return an instance of a class derived from {@link CollectionObject}.
	 *
	 * @param string				$theName			Collection name.
	 *
	 * @access public
	 * @return CollectionObject		Collection object.
	 *
	 * @uses newCollection()
	 */
	public function Collection( $theName )
	{
		//
		// Get current database parameters.
		//
		$params = $this->getArrayCopy();
		
		//
		// Add collection name.
		//
		$params[ kTAG_CONN_COLL ] = $theName;
		
		return $this->newCollection( $params );										// ==>
	
	} // Collection.

	 
	/*===================================================================================
	 *	getCollections																	*
	 *==================================================================================*/

	/**
	 * Return collection names
	 *
	 * This method should return the list of collection names of the current database, the
	 * method should return the following retults:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: The operation is not supported.
	 *	<li><tt>FALSE</tt>: The database is not connected.
	 *	<li><tt>array</tt>: The database collection names.
	 * </ul>
	 *
	 * We implement the method in this class as a fall-back.
	 *
	 * @access public
	 * @return array				Server statistics or <tt>NULL</tt> if unsupported.
	 */
	public function getCollections()									{	return NULL;	}

		

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
	 * Derived classes must implement this method.
	 *
	 * @param string				$theSequence		Sequence selector.
	 * @param integer				$theNumber			Sequence number.
	 *
	 * @access public
	 */
	abstract public function setSequenceNumber( $theSequence, $theNumber = 1 );

	 
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
	 * Derived classes must implement this method.
	 *
	 * @param string				$theSequence		Sequence selector.
	 *
	 * @access public
	 * @return integer				Sequence number.
	 */
	abstract public function getSequenceNumber( $theSequence );

		

/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newServer																		*
	 *==================================================================================*/

	/**
	 * Return a new server instance
	 *
	 * This method should be implemented by concrete derived classes, it expects a list of
	 * offsets or a data source name containing the necessary elements to instantiate a
	 * {@link ServerObject} instance which will be considered the current object's parent.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theParameter		Server parameters.
	 *
	 * @access protected
	 * @return ServerObject			Server instance.
	 */
	abstract protected function newServer( $theParameter );

	 
	/*===================================================================================
	 *	newCollection																		*
	 *==================================================================================*/

	/**
	 * Return a new collection instance
	 *
	 * This method should be implemented by concrete derived classes, it expects a list of
	 * offsets which include database information and should use them to instantiate a
	 * {@link CollectionObject} instance.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param array					$theOffsets			Full collection offsets.
	 *
	 * @access protected
	 * @return CollectionObject		Collection instance.
	 */
	abstract protected function newCollection( $theOffsets );

	 

} // class DatabaseObject.


?>
