<?php

/**
 * MongoDatabase.php
 *
 * This file contains the definition of the {@link MongoDatabase} class.
 */

namespace OntologyWrapper\connection;

use OntologyWrapper\DatabaseObject;

/*=======================================================================================
 *																						*
 *									MongoDatabase.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo database
 *
 * This class is a <i>concrete</i> implementation of the {@link DatabaseObject} wrapping a
 * {@link MongoDB} class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
class MongoDatabase extends DatabaseObject
{
		

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
	 * {@link MongoDB}.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		return ( $this->mConnection instanceof \MongoDB );							// ==>
	
	} // isConnected.

	 
	/*===================================================================================
	 *	getStatistics																	*
	 *==================================================================================*/

	/**
	 * Return statistics
	 *
	 * This method will call the {@link MongoDB::listCollections()} method.
	 *
	 * @access public
	 * @return mixed				Depends on driver.
	 */
	public function getStatistics()		{	return $this->mConnection->listCollections();	}

		

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
	 * This method will instantiate a {@link MongoClient} object and set it in the
	 * mConnection data member.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * If the operation fails, the method will raise an exception.
	 *
	 * @access protected
	 * @return mixed				The native connection.
	 */
	protected function connectionOpen()
	{
		//
		// Check parent.
		//
		if( $this->mParent instanceof MongoServer )
		{
			//
			// Connect server.
			//
			if( ! $this->mParent->isConnected() )
				$this->mParent->openConnection();
			
			//
			// Check database name.
			//
			if( $this->offsetExists( kTAG_CONN_BASE ) )
				$this->mConnection
					= $this->mParent
						->Connection()->selectDB(
							$this->offsetGet( kTAG_CONN_BASE ) );
			
			else
				throw new \Exception(
					"Unable to open connection: "
				   ."Missing database name." );									// !@! ==>
			
			return $this->mConnection;												// ==>
		
		} // Server set.
			
		throw new \Exception(
			"Unable to open connection: "
		   ."Missing server." );												// !@! ==>
	
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
	 * We implement the method to return a {@link MongoServer} instance.
	 *
	 * @param mixed					$theParameter		Server parameters.
	 *
	 * @access protected
	 * @return MongoServer			Server instance.
	 */
	protected function newServer( $theParameter )
	{
		return new MongoServer( $theParameter );									// ==>
	
	} // newServer.

	 
	/*===================================================================================
	 *	newCollection																		*
	 *==================================================================================*/

	/**
	 * Return a new collection instance
	 *
	 * We implement this method to return a {@link MongoCollection} instance.
	 *
	 * @param array					$theOffsets			Full collection offsets.
	 *
	 * @access protected
	 * @return CollectionObject		Collection instance.
	 */
	protected function newCollection( $theOffsets )
	{
		return new MongoCollection( $theOffsets );									// ==>
	
	} // newCollection.

	 

} // class MongoDatabase.


?>
