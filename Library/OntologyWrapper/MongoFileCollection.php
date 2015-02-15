<?php

/**
 * MongoFileCollection.php
 *
 * This file contains the definition of the {@link MongoFileCollection} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\FileCollection;
use OntologyWrapper\MongoDatabase;

/*=======================================================================================
 *																						*
 *								MongoFileCollection.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo file collection
 *
 * This class is a <i>concrete</i> implementation of the {@link FileCollection} wrapping a
 * {@link MongoGridFS} class.
 *
 * This class implements its methods via traits.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 *				1.50 15/02/2015 Implemented with trait methods.
 */
class MongoFileCollection extends FileCollection
{
	/**
	 * Collection object trait.
	 *
	 * We use this trait to handle collection objects.
	 */
	use	traits\MongoCollectionObjectTrait;
	 
	/**
	 * File collection trait.
	 *
	 * We use this trait to handle file collections.
	 */
	use	traits\MongoFileCollectionTrait;

		

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
	 * {@link MongoGridFS}.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		return ( $this->mConnection instanceof \MongoGridFS );						// ==>
	
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
			// Connect database.
			//
			if( ! $this->mParent->isConnected() )
				$this->mParent->openConnection();
			
			//
			// Check collection name.
			//
			if( $this->offsetExists( kTAG_CONN_COLL ) )
				$this->mConnection
					= new \MongoGridFS(
						$this->mParent->connection(),
						$this->offsetGet( kTAG_CONN_COLL ) );
			
			else
				throw new \Exception(
					"Unable to open connection: "
				   ."Missing collection name." );								// !@! ==>
			
			//
			// Instantiate collection.
			//
			return $this->mConnection;												// ==>
			
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

	 

} // class MongoFileCollection.


?>
