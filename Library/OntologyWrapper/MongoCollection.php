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
	 *	resolve																			*
	 *==================================================================================*/

	/**
	 * Resolve an identifier
	 *
	 * We first check if the current collection is connected, if that is not the case, we
	 * raise an exception.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param mixed					$theOffset			Offset.
	 * @param mixed					$asObject			Return object if <tt>TRUE</tt>.
	 *
	 * @access public
	 * @return mixed				Found object, array, objects count or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function resolve( $theIdentifier, $theOffset = kTAG_NID, $asObject = TRUE )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Resolve offset.
			//
			$theOffset = (string) OntologyObject::resolveOffset( $theOffset, TRUE );
			
			//
			// Match object.
			//
			if( $asObject !== NULL )
			{
				//
				// Find first.
				//
				$object
					= $this->
						mConnection->
							findOne( array( $theOffset => $theIdentifier ) );
				if( $object !== NULL )
				{
					//
					// Return array.
					//
					if( ! $asObject )
						return $object;												// ==>
				
					//
					// Check class.
					//
					if( array_key_exists( kTAG_CLASS, $object ) )
					{
						//
						// Save class.
						//
						$class = $object[ kTAG_CLASS ];
					
						return new $class( $this, $object );						// ==>
				
					} // Has class.
			
					throw new \Exception(
						"Unable to resolve object: "
					   ."missing object class." );								// !@! ==>
			
				} // Found.
			
				return NULL;														// ==>
			
			} // Return object or array.
			
			//
			// Find objects.
			//
			$rs = $this-> mConnection-> find( array( $theOffset => $theIdentifier ) );
			
			return $rs->count();													// ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to resolve object: "
		   ."connection is not open." );										// !@! ==>
	
	} // resolve.

	 
	/*===================================================================================
	 *	getAll																			*
	 *==================================================================================*/

	/**
	 * Return all objects
	 *
	 * In this class we return a { @link MongoCursor} object.
	 *
	 * @access public
	 * @return Iterator				Selection of all objects of the collection.
	 *
	 * @throws Exception
	 */
	public function getAll()
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
			return $this->mConnection->find();										// ==>
			
		throw new \Exception(
			"Unable to get all object: "
		   ."connection is not open." );										// !@! ==>
	
	} // getAll.

		

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
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access protected
	 * @return DatabaseObject		Database instance.
	 */
	protected function newDatabase( $theParameter, $doOpen = TRUE)
	{
		//
		// Instantiate database.
		//
		$database = new MongoDatabase( $theParameter );
		
		//
		// Set dictionary.
		//
		$database->dictionary( $this->dictionary() );
		
		//
		// Open connection.
		//
		if( $doOpen )
			$database->openConnection();
		
		return $database;															// ==>
	
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
	 * In this class we commit the provided array and return its {@link kTAG_NID} value.
	 *
	 * @param reference				$theData			Data to commit.
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
