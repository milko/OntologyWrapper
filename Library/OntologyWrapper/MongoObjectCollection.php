<?php

/**
 * MongoObjectCollection.php
 *
 * This file contains the definition of the {@link MongoObjectCollection} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ObjectCollection;
use OntologyWrapper\MongoDatabase;

/*=======================================================================================
 *																						*
 *								MongoObjectCollection.php								*
 *																						*
 *======================================================================================*/

/**
 * Mongo collection
 *
 * This class is a <i>concrete</i> implementation of the {@link ObjectCollection} wrapping a
 * {@link MongoObjectCollection} class.
 *
 * This class implements its methods via traits.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 *				1.50 15/02/2015 Implemented with trait methods.
 */
class MongoObjectCollection extends ObjectCollection
{
	/**
	 * Collection object trait.
	 *
	 * We use this trait to handle collection objects.
	 */
	use	traits\MongoCollectionObjectTrait;
	 
	/**
	 * Object collection trait.
	 *
	 * We use this trait to handle object collections.
	 */
	use	traits\MongoObjectCollectionTrait;

		

/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isConnected																		*
	 *==================================================================================*/

	/**
	 * Check if connection is open
	 *
	 * We overload this method to assume the object is connected if the resource is a
	 * {@link MongoCollection}.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		return ( $this->mConnection instanceof \MongoCollection );					// ==>
	
	} // isConnected.

		

/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	connectionOpen																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * This method will instantiate a {@link \MongoCollection} object and set it in the
	 * {@link mConnection} data member.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * If the operation fails, the method will raise an exception.
	 *
	 * @access protected
	 * @return mixed				The native connection.
	 *
	 * @throws Exception
	 */
	protected function connectionOpen()
	{
		//
		// Check parent.
		//
		if( $this->mParent instanceof MongoDatabase )
		{
			//
			// Connect server.
			//
			if( ! $this->mParent->isConnected() )
				$this->mParent->openConnection();
			
			//
			// Check collection name.
			//
			if( $this->offsetExists( kTAG_CONN_COLL ) )
				$this->mConnection
					= $this->mParent
						->mConnection->selectCollection(
							$this->offsetGet( kTAG_CONN_COLL ) );
			
			else
				throw new \Exception(
					"Unable to open connection: "
				   ."Missing collection name." );								// !@! ==>
			
			return $this->mConnection;												// ==>
		
		} // Server set.
			
		throw new \Exception(
			"Unable to open connection: "
		   ."Missing database." );												// !@! ==>
	
	} // connectionOpen.

	 
	/*===================================================================================
	 *	connectionClose																	*
	 *==================================================================================*/

	/**
	 * Close connection
	 *
	 * We overload this method to reset the connection resource.
	 *
	 * @access protected
	 */
	protected function connectionClose()					{	$this->mConnection = NULL;	}

	 

} // class MongoObjectCollection.


?>
