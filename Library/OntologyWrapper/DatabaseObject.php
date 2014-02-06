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
	 * We overload the constructor to instantiate a server from the provided parameter if
	 * the parent object was not provided.
	 *
	 * @param mixed					$theParameter		Data source name or parameters.
	 * @param ConnectionObject		$theParent			Connection parent.
	 *
	 * @access public
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
			$offsets = array( kTAG_CONN_PROTOCOL, kTAG_CONN_HOST, kTAG_CONN_PORT,
							  kTAG_CONN_USER, kTAG_CONN_PASS, kTAG_CONN_OPTS );
			foreach( $offsets as $offset )
			{
				if( $this->offsetExists( $offset ) )
					$params[ $offset ] = $this->offsetGet( $offset );
			
			} // Extracting server parameters.
			
			//
			// Instantiate server.
			//
			$this->mParent = $this->newServer( $params );
		
		} // Mising parent.
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC CONNECTION INTERFACE								*
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
