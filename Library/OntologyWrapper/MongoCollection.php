<?php

/**
 * MongoCollection.php
 *
 * This file contains the definition of the {@link MongoCollection} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\CollectionObject;
use OntologyWrapper\MongoDatabase;

/*=======================================================================================
 *																						*
 *									MongoCollection.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo database
 *
 * This class is a <i>concrete</i> implementation of the {@link CollectionObject} wrapping a
 * {@link MongoDB} class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */
class MongoCollection extends CollectionObject
{
		

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
	 * This method will drop the current collection.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function drop()
	{
		//
		// Check connection.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to drop collection: "
			   ."collection is not connected." );								// !@! ==>
		
		$this->mConnection->drop();
	
	} // drop.

		

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
		return ( $this->mConnection instanceof \MongoCollection );					// ==>
	
	} // isConnected.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveIdentifier																*
	 *==================================================================================*/

	/**
	 * Resolve an identifier
	 *
	 * In this class we assume the provided identifier is either the object's native or
	 * global identifier, if none of the two are matched, the method will return
	 * <tt>NULL</tt>.
	 *
	 * We first check if the current collection is connected, if that is not the case, we
	 * raise an exception.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 * @return array				Found object as an array, or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function resolveIdentifier( $theIdentifier )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Try native identifier.
			//
			$object = $this->mConnection->findOne( array( kTAG_NID => $theIdentifier ) );
			if( $object !== NULL )
				return $object;														// ==>
			
			return $this->mConnection->findOne(
						array( (string) kTAG_PID => $theIdentifier ) );				// ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to resolve identifier: "
		   ."connection is not open." );										// !@! ==>
	
	} // resolveIdentifier.

		

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
	 * {@link Connection()} data member.
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
						->Connection()->selectCollection(
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
	 * We implement the method to return a {@link MongoServer} instance.
	 *
	 * @param mixed					$theParameter		Server parameters.
	 *
	 * @access protected
	 * @return DatabaseObject		Database instance.
	 */
	protected function newDatabase( $theParameter )
	{
		return new MongoDatabase( $theParameter );									// ==>
	
	} // newDatabase.

		

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
	 * In this class we insert the provided array and return its {@link kTAG_NID} value.
	 *
	 * @param reference				$theData			Data to insert.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier.
	 */
	function insertData( &$theData, &$theOptions )
	{
		//
		// Serialise object.
		//
		ContainerObject::Object2Array( $theData, $data );
		
		//
		// Insert.
		//
		$ok = $this->mConnection->insert( $data, $theOptions );
		
		//
		// Get identifier.
		//
		$id = $data[ kTAG_NID ];
		
		//
		// Set identifier.
		//
		$theData[ kTAG_NID ] = $id;
		
		return $id;																	// ==>
	
	} // insertData.

	 

} // class MongoCollection.


?>
