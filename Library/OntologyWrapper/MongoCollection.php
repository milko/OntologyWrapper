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
	 *	matchOne																		*
	 *==================================================================================*/

	/**
	 * Match one object
	 *
	 * We first check if the current collection is connected, if that is not the case, we
	 * raise an exception.
	 *
	 * In this class we map the method over the {@link MongoCollection::findOne()} method
	 * when retrieving objects or identifiers and {@link MongoCollection::findOne()} method
	 * when retrieving counts.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param array					$theFields			Fields selection.
	 * @param bitfield				$theResult			Result type.
	 *
	 * @access public
	 * @return mixed				Matched data or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function matchOne( $theCriteria, $theResult = kQUERY_DEFAULT,
											$theFields = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Get result.
			//
			switch( $theResult & kRESULT_MASK )
			{
				case kQUERY_NID:
					$theFields = array( kTAG_NID => TRUE );
				case kQUERY_OBJECT:
				case kQUERY_ARRAY:
					$object
						= $this->
							mConnection->
								findOne( $theCriteria, $theFields );
					break;
					
				case kQUERY_COUNT:
					$rs
						= $this->
							mConnection->
								find( $theCriteria );
					return $rs->count();											// ==>
					break;
			
			} // Parsed result flags.
			
			//
			// Handle no matches.
			//
			if( $object === NULL )
			{
				//
				// Assert.
				//
				if( $theResult & kQUERY_ASSERT )
					throw new \Exception(
						"Unable to match object." );							// !@! ==>
				
				return NULL;														// ==>
			
			} // No matches.
			
			//
			// Handle result.
			//
			switch( $theResult & kRESULT_MASK )
			{
				case kQUERY_ARRAY:
				
					return $object;													// ==>
					
				case kQUERY_NID:
				
					if( ! array_key_exists( kTAG_NID, $object ) )
						throw new \Exception(
							"Unable to resolve identifier: "
						   ."missing object identifier." );						// !@! ==>
					
					return $object[ kTAG_NID ];										// ==>
				
				case kQUERY_OBJECT:
				
					if( ! array_key_exists( kTAG_CLASS, $object ) )
						throw new \Exception(
							"Unable to resolve object: "
						   ."missing object class." );							// !@! ==>
					
					$class = $object[ kTAG_CLASS ];
					
					return new $class( $this->dictionary(), $object );				// ==>
			
			} // Parsed result flags.
		
		} // Connected.
			
		throw new \Exception(
			"Unable to match object: "
		   ."connection is not open." );										// !@! ==>
	
	} // matchOne.

	 
	/*===================================================================================
	 *	matchAll																		*
	 *==================================================================================*/

	/**
	 * Match all objects
	 *
	 * In this class we perform the query using the {@link MongoCollection::find()} method,
	 * we then return a {@link MongoIterator} instance with the query cursor and collection.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param bitfield				$theResult			Result type.
	 * @param array					$theFields			Fields selection.
	 *
	 * @access public
	 * @return MongoIterator		Matched data or <tt>NULL</tt>.
	 */
	public function matchAll( $theCriteria = Array(),
							  $theResult = kQUERY_DEFAULT,
							  $theFields = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Make query.
			//
			$cursor
				= $this->
					mConnection->
						find( $theCriteria, $theFields );
			
			//
			// Handle no matches.
			//
			if( ! $cursor->count() )
			{
				//
				// Assert.
				//
				if( $theResult & kQUERY_ASSERT )
					throw new \Exception(
						"No matches." );										// !@! ==>
			
			} // No matches.
			
			return new MongoIterator( $cursor, $this, $theResult & kRESULT_MASK );	// ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to perform query: "
		   ."connection is not open." );										// !@! ==>
	
	} // matchAll.

	 
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
 *							PUBLIC INDEX MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getIndex																		*
	 *==================================================================================*/

	/**
	 * Get index
	 *
	 * This method will return the list of indexed offsets, each element of the returned
	 * array will contain the list of offsets used in the index: an array for multi offset
	 * indexes and a string for a single index.
	 *
	 * @access public
	 * @return array				The list of indexed offsets.
	 */
	public function getIndex()
	{
		//
		// Init local storage.
		//
		$index = Array();
		
		//
		// Iterate collection index records.
		//
		$list = $this->Connection()->getIndexInfo();
		foreach( $list as $info )
		{
			//
			// Get index offsets.
			//
			$offsets = array_keys( $info );
			
			//
			// Handle multiple offsets.
			//
			if( count( $offsets > 0 ) )
				$index[] = $offsets;
			
			//
			// Handle single offset.
			//
			else
				$index[] = $offsets[ 0 ];
		
		} // Iterating index info.
		
		return $index;																// ==>
	
	} // getIndex.

	 
	/*===================================================================================
	 *	createIndex																		*
	 *==================================================================================*/

	/**
	 * Set index
	 *
	 * In this class the two parameters are the same as those received by the
	 * {@link MongoCollection::ensureIndex()} method.
	 *
	 * @param array					$theIndex			Offset to index and index types.
	 * @param array					$theOptions			Index options.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function createIndex( $theIndex, $theOptions = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
			$this->Connection()->ensureIndex( $theIndex, $theOptions );
		
		else
			throw new \Exception(
				"Unable to create index: "
			   ."connection is not open." );									// !@! ==>
	
	} // createIndex.

	 
	/*===================================================================================
	 *	deleteIndex																		*
	 *==================================================================================*/

	/**
	 * Delete index
	 *
	 * In this class we use {@link MongoCollection::deleteIndexes()} to delete all indexes
	 * and {@link MongoCollection::deleteIndex()} to delete specific indexes.
	 *
	 * @param mixed					$theIndex			Offset or offsets.
	 *
	 * @access public
	 */
	public function deleteIndex( $theIndex = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Delete all indexes.
			//
			if( $theIndex === NULL )
				$this->Connection()->deleteIndexes();
			
			//
			// Delete specific indexes.
			//
			else
				$this->Connection()->deleteIndex( $theIndex );
		
		} // Connected.
		
		else
			throw new \Exception(
				"Unable to delete index: "
			   ."connection is not open." );									// !@! ==>
	
	} // deleteIndex.

		

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
	 * In this class we use the <tt>$inc</tt> operator.
	 *
	 * @param mixed					$theIdentifier		Object identifier or identifiers.
	 * @param string				$theReferenceOffset	Reference count offset.
	 * @param string				$theIdentOffset		Identifier offset.
	 * @param integer				$theReferenceCount	Reference count value.
	 *
	 * @access public
	 */
	public function updateReferenceCount( $theIdentifier,
										  $theReferenceOffset,
										  $theIdentOffset = kTAG_NID,
										  $theReferenceCount = 1 )
	{
		//
		// Set criteria.
		//
		$criteria = ( is_array( $theIdentifier ) )
				  ? array( (string) $theIdentOffset => array( '$in' => $theIdentifier ) )
				  : array( (string) $theIdentOffset => $theIdentifier );
	
		//
		// Set modifications.
		//
		$modifications = array( '$inc' => array( (string) $theReferenceOffset
											  => (int) $theReferenceCount ) );
	
		//
		// Set options.
		//
		$options = array( 'multiple' => TRUE, 'upsert' => FALSE );
		
		//
		// Update.
		//
		$ok = $this->Connection()->update( $criteria, $modifications, $options );
		if( ! $ok[ 'ok' ] )
			throw new Exception( $ok[ 'err' ] );								// !@! ==>
	
	} // updateReferenceCount.

	 
	/*===================================================================================
	 *	updateTagOffsets																*
	 *==================================================================================*/

	/**
	 * Update tag offsets
	 *
	 * In this class we use the <tt>$addToSet</tt> operator.
	 *
	 * @param int					$theTag				Tag native identifier.
	 * @param mixed					$theOffsets			List of tag offsets or offset.
	 *
	 * @access public
	 */
	public function updateTagOffsets( $theTag, $theOffsets )
	{
		//
		// Set criteria.
		//
		$criteria = array( (string) kTAG_ID_SEQUENCE => (int) $theTag );
	
		//
		// Set modifications.
		//
		$modifications = ( is_array( $theOffsets ) )
					   ? array(
							'$addToSet' => array(
								(string) kTAG_OFFSETS => array(
									'$each' => $theOffsets ) ) )
					   : array(
					   		'$addToSet' => array(
					   			(string) kTAG_OFFSETS => (string) $theOffsets ) );
	
		//
		// Set options.
		//
		$options = array( 'multiple' => FALSE, 'upsert' => FALSE );
		
		//
		// Update.
		//
		$ok = $this->Connection()->update( $criteria, $modifications, $options );
		if( ! $ok[ 'ok' ] )
			throw new Exception( $ok[ 'err' ] );								// !@! ==>
	
	} // updateTagOffsets.

	 
	/*===================================================================================
	 *	replaceOffsets																	*
	 *==================================================================================*/

	/**
	 * Replace offsets
	 *
	 * In this class we use the <tt>$set</tt> operator.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param array					$theProperties		Properties to be added or replaced.
	 *
	 * @access public
	 * @return integer				Number of objects affected (1 or 0).
	 *
	 * @throws Exception
	 */
	public function replaceOffsets( $theIdentifier, $theProperties )
	{
		//
		// Check offsets.
		//
		if( ! is_array( $theProperties ) )
			throw new \Exception(
				"Unable to replace properties: "
			   ."expecting an array." );										// !@! ==>
		elseif( ! count( $theProperties ) )
			return 0;																// ==>
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_NID => $theIdentifier );
	
		//
		// Set modifications.
		//
		$modifications = array( '$set' => $theProperties );
	
		//
		// Set options.
		//
		$options = array( 'multiple' => FALSE, 'upsert' => FALSE );
		
		//
		// Update.
		//
		$ok = $this->Connection()->update( $criteria, $modifications, $options );
		if( ! $ok[ 'ok' ] )
			throw new Exception( $ok[ 'err' ] );								// !@! ==>
		
		return $ok[ 'n' ];															// ==>
	
	} // replaceOffsets.

	 
	/*===================================================================================
	 *	deleteOffsets																	*
	 *==================================================================================*/

	/**
	 * Delete offsets
	 *
	 * In this class we use the <tt>$unset</tt> operator.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param array					$theOffsets			Offsets to be deleted.
	 *
	 * @access public
	 * @return integer				Number of objects affected (1 or 0).
	 */
	public function deleteOffsets( $theIdentifier, $theOffsets )
	{
		//
		// Check offsets.
		//
		if( ! is_array( $theOffsets ) )
			throw new \Exception(
				"Unable to delete properties: "
			   ."expecting an array." );										// !@! ==>
		elseif( ! count( $theOffsets ) )
			return 0;																// ==>
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_NID => $theIdentifier );
	
		//
		// Set modifications.
		//
		$tmp = Array();
		foreach( $theOffsets as $offset )
			$tmp[] = array( $offset => '' );
		$modifications = array( '$unset' => $tmp );
	
		//
		// Set options.
		//
		$options = array( 'multiple' => FALSE, 'upsert' => FALSE );
		
		//
		// Update.
		//
		$ok = $this->Connection()->update( $criteria, $modifications, $options );
		if( ! $ok[ 'ok' ] )
			throw new Exception( $ok[ 'err' ] );								// !@! ==>
		
		return $ok[ 'n' ];															// ==>
	
	} // deleteOffsets.

		

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

	 
	/*===================================================================================
	 *	replaceData																		*
	 *==================================================================================*/

	/**
	 * Save or replace provided data
	 *
	 * In this class we save the provided array and return its {@link kTAG_NID} value.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param reference				$theData			Data to save.
	 * @param array					$theOptions			Replace options.
	 *
	 * @access protected
	 * @return mixed				Object identifier.
	 */
	protected function replaceData( &$theData, &$theOptions )
	{
		//
		// Serialise object.
		//
		ContainerObject::Object2Array( $theData, $data );
		
		//
		// Insert.
		//
		$ok = $this->mConnection->save( $data, $theOptions );
		
		//
		// Get identifier.
		//
		$id = $data[ kTAG_NID ];
		
		//
		// Set identifier.
		//
		$theData[ kTAG_NID ] = $id;
		
		return $id;																	// ==>
	
	} // replaceData.

	 
	/*===================================================================================
	 *	deleteIdentifier																*
	 *==================================================================================*/

	/**
	 * Delete provided identifier
	 *
	 * This method should be implemented by concrete derived classes, it should delete the
	 * object matched by the provided identifier, if the object was matched, the method
	 * should return the identifier, if not, it should return <tt>NULL</tt>.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier or <tt>NULL</tt>.
	 */
	protected function deleteIdentifier( $theIdentifier, &$theOptions )
	{
		//
		// Normalise options.
		//
		if( ! is_array( $theOptions ) )
			$theOptions = Array();
		
		//
		// Set only one option.
		//
		$theOptions[ "justOne" ] = TRUE;
		
		//
		// Delete object.
		//
		$ok = $this->mConnection->remove( array( kTAG_NID => $theIdentifier ),
										  $theOptions );
		
		return ( $ok[ 'n' ] > 0 )
			 ? $theIdentifier														// ==>
			 : NULL;																// ==>
	
	} // deleteIdentifier.

	 

} // class MongoCollection.


?>
