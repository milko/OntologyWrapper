<?php

/**
 * MongoCollectionObjectTrait.php
 *
 * This file contains the definition of the {@link MongoCollectionObjectTrait} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *							MongoCollectionObjectTrait.php								*
 *																						*
 *======================================================================================*/

/**
 * MongoDB collection trait
 *
 * The main purpose of this trait is to implement the MongoDB specific methods related to
 * the {@link CollectionObject} abstract class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 15/02/2015
 */
trait MongoCollectionObjectTrait
{
		

/*=======================================================================================
 *																						*
 *								PUBLIC CREATION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	drop																			*
	 *==================================================================================*/

	/**
	 * Drop the collection
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
		
		//
		// Drop collection.
		//
		$this->mConnection->drop();
	
	} // drop.

		

/*=======================================================================================
 *																						*
 *								PUBLIC MODIFICATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	modify																			*
	 *==================================================================================*/

	/**
	 * Modify object(s)
	 *
	 * In this class we use the <tt>update</tt> method.
	 *
	 * @param array					$theCriteria		Object selection criteria.
	 * @param array					$theActions			Modification actions.
	 * @param array					$theOptions			Modification options.
	 *
	 * @access public
	 * @return array				Operation status.
	 */
	public function modify( $theCriteria, $theActions, $theOptions )
	{
		//
		// Update.
		//
		$ok = $this->mConnection->update( $theCriteria, $theActions, $theOptions );
		if( ! $ok[ 'ok' ] )
			throw new Exception( $ok[ 'err' ] );								// !@! ==>
		
		return array( 'affected' => $ok[ 'n' ],
					  'modified' => $ok[ 'updatedExisting' ] );						// ==>
	
	} // modify.

		

/*=======================================================================================
 *																						*
 *								PUBLIC MODIFICATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	replaceOffsets																	*
	 *==================================================================================*/

	/**
	 * Replace offsets
	 *
	 * In this class we use the <tt>$set</tt> and <tt>$unset</tt> operators.
	 *
	 * @param mixed					$theCriteria		Object selection criteria or id.
	 * @param array					$theProperties		Properties to be added or replaced.
	 *
	 * @access public
	 * @return integer				Number of objects affected (1 or 0).
	 *
	 * @throws Exception
	 */
	public function replaceOffsets( $theCriteria, $theProperties )
	{
		//
		// Check criteria.
		//
		if( ! is_array( $theCriteria ) )
			$theCriteria = array( kTAG_NID => $theCriteria );
		
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
		// Divide replace from delete.
		//
		$rep = $del = Array();
		foreach( $theProperties as $key => $value )
		{
			if( $value === NULL )
				$del[ $key ] = "";
			else
				$rep[ $key ] = $value;
		}
		
		//
		// Init results.
		//
		$results = 0;
		
		//
		// Set options.
		//
		$options = array( 'upsert' => FALSE,
						  'multiple' => TRUE );
		
		//
		// Remove.
		//
		if( count( $del ) )
		{
			//
			// Set modifications.
			//
			$modifications = array( '$unset' => $theProperties );
	
			//
			// Update.
			//
			$ok = $this->mConnection->update( $theCriteria, $modifications, $options );
			if( ! $ok[ 'ok' ] )
				throw new Exception( $ok[ 'err' ] );							// !@! ==>
			
			//
			// Update resupts.
			//
			$results += $ok[ 'n' ];
		
		} // Has deletions.
		
		//
		// Replace.
		//
		if( count( $rep ) )
		{
			//
			// Set modifications.
			//
			$modifications = array( '$set' => $theProperties );
	
			//
			// Update.
			//
			$ok = $this->mConnection->update( $theCriteria, $modifications, $options );
			if( ! $ok[ 'ok' ] )
				throw new Exception( $ok[ 'err' ] );							// !@! ==>
			
			//
			// Update resupts.
			//
			$results += $ok[ 'n' ];
		
		} // Has deletions.
	
		return $results;															// ==>
	
	} // replaceOffsets.

	 
	/*===================================================================================
	 *	updateSet																		*
	 *==================================================================================*/

	/**
	 * Update set
	 *
	 * In this class we use the <tt>$addToSet</tt> and the <tt>$pull</tt> operators.
	 *
	 * @param mixed					$theCriteria		Object selection criteria or id.
	 * @param array					$theElements		List of elements to be added.
	 * @param boolean				$doAdd				<tt>TRUE</tt> add.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function updateSet( $theCriteria, $theElements, $doAdd )
	{
		//
		// Check criteria.
		//
		if( ! is_array( $theCriteria ) )
			$theCriteria = array( kTAG_NID => $theCriteria );
		
		//
		// Handle elements.
		//
		if( is_array( $theElements ) )
		{
			//
			// Check elements count.
			//
			if( count( $theElements ) )
			{
				//
				// Set options.
				//
				$options = array( 'upsert' => FALSE,
								  'multiple' => TRUE );
				
				//
				// Iterate values.
				//
				$modifications = Array();
				foreach( $theElements as $key => $value )
				{
					//
					// Add to set.
					//
					if( $doAdd )
						$modifications[ $key ]
							= ( is_array( $value ) )
							? array( '$each' => $value )
							: $value;
					
					//
					// Remove from set.
					//
					else
						$modifications[ $key ]
							= ( is_array( $value ) )
							? $value
							: array( $value );
				
				} // Iterating elements.
				
				//
				// Finalise modifications.
				//
				$modifications = ( $doAdd )
							   ? array( '$addToSet' => $modifications )
							   : array( '$pullAll' => $modifications );
	
				//
				// Update.
				//
				$ok = $this->mConnection->update( $theCriteria, $modifications, $options );
				if( ! $ok[ 'ok' ] )
					throw new Exception( $ok[ 'err' ] );						// !@! ==>
			
			} // provided elements.
		
		} // Provided elements array
		
		else
			throw new \Exception(
				"Unable to add to set: "
			   ."expecting an array of elements." );							// !@! ==>
		
	} // updateSet.

	 
	/*===================================================================================
	 *	updateStructList																*
	 *==================================================================================*/

	/**
	 * Update list of structures
	 *
	 * In this class we use the <tt>$addToSet</tt> and the <tt>$pull</tt> operators.
	 *
	 * Note that the elements parameter is expected to be correct, no check is done.
	 *
	 * @param mixed					$theCriteria		Object selection criteria or id.
	 * @param array					$theElements		List of structures to be added.
	 * @param boolean				$doAdd				<tt>TRUE</tt> add.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function updateStructList( $theCriteria, $theElements, $doAdd )
	{
		//
		// Check criteria.
		//
		if( ! is_array( $theCriteria ) )
			$theCriteria = array( kTAG_NID => $theCriteria );
		
		//
		// Check elements.
		//
		if( ! is_array( $theElements ) )
			throw new \Exception(
				"Unable to add to set: "
			   ."expecting an array of elements." );							// !@! ==>
		
		//
		// Set options.
		//
		$options = array( 'upsert' => FALSE,
						  'multiple' => TRUE );
		
		//
		// Init modifications.
		//
		$modifications = ( $doAdd )
					   ? array( '$push' => $theElements )
					   : array( '$pull' => $theElements );
	
		//
		// Update.
		//
		$ok = $this->mConnection->update( $theCriteria, $modifications, $options );
		if( ! $ok[ 'ok' ] )
			throw new Exception( $ok[ 'err' ] );								// !@! ==>
	
	} // updateStructList.

	 
	/*===================================================================================
	 *	updateReferenceCount															*
	 *==================================================================================*/

	/**
	 * Update reference count
	 *
	 * In this class we use the <tt>$inc</tt> operator.
	 *
	 * Note that the increments are expecyted to be correct, no check is done.
	 *
	 * @param mixed					$theCriteria		Object selection criteria or id.
	 * @param array					$theElements		List of offsets and increments.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function updateReferenceCount( $theCriteria, $theElements )
	{
		//
		// Check criteria.
		//
		if( ! is_array( $theCriteria ) )
			$theCriteria = array( kTAG_NID => $theCriteria );
		
		//
		// Check elements.
		//
		if( ! is_array( $theElements ) )
			throw new \Exception(
				"Unable to update count: "
			   ."expecting an array of elements." );							// !@! ==>
		
		//
		// Set options.
		//
		$options = array( 'upsert' => FALSE,
						  'multiple' => TRUE );
		
		//
		// Set modifications.
		//
		$modifications = array( '$inc' => $theElements );
		
		//
		// Update.
		//
		$ok = $this->mConnection->update( $theCriteria, $modifications, $options );
		if( ! $ok[ 'ok' ] )
			throw new Exception( $ok[ 'err' ] );								// !@! ==>
	
	} // updateReferenceCount.

		

/*=======================================================================================
 *																						*
 *							PUBLIC INDEX MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	createIndex																		*
	 *==================================================================================*/

	/**
	 * Set index
	 *
	 * In this class the two parameters are the same as those received by the
	 * {@link MongoObjectCollection::ensureIndex()} method.
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
			$this->mConnection->createIndex( $theIndex, $theOptions );
		
		else
			throw new \Exception(
				"Unable to create index: "
			   ."connection is not open." );									// !@! ==>
	
	} // createIndex.

	 
	/*===================================================================================
	 *	getIndex																		*
	 *==================================================================================*/

	/**
	 * Get index
	 *
	 * In this class we use the getIndexInfo() Mongo function.
	 *
	 * @access public
	 * @return array				The collection index information.
	 */
	public function getIndex()				{	return $this->mConnection->getIndexInfo();	}

	 
	/*===================================================================================
	 *	getIndexedOffsets																*
	 *==================================================================================*/

	/**
	 * Get index
	 *
	 * In this class we parse the getIndexInfo() array.
	 *
	 * @access public
	 * @return array				The list of indexed offsets.
	 */
	public function getIndexedOffsets()
	{
		//
		// Init local storage.
		//
		$index = Array();
		
		//
		// Iterate index information.
		//
		foreach( $this->mConnection->getIndexInfo() as $info )
		{
			//
			// Skip native identifier.
			//
			if( (count( $info[ 'key' ] ) == 1)
			 && (($key = key( $info[ 'key' ] )) != kTAG_NID) )
			{
				//
				// Explode offsets.
				//
				$offsets = explode( '.', $key );
				
				//
				// Handle new tag.
				//
				$offset = $offsets[ count( $offsets ) - 1 ];
				if( ! array_key_exists( $offset, $index ) )
					$index[ $offset ] = array( $key );
				
				//
				// Handle existing tag.
				//
				elseif( ! in_array( $key, $index[ $offset ] ) )
					$index[ $offset ][] = $key;
			
			} // Not native identifier.
		
		} // Iterating indexes.
		
		return $index;																// ==>
	
	} // getIndexedOffsets.

	 
	/*===================================================================================
	 *	deleteIndex																		*
	 *==================================================================================*/

	/**
	 * Delete index
	 *
	 * In this class we use {@link MongoObjectCollection::deleteIndexes()} to delete all indexes
	 * and {@link MongoObjectCollection::deleteIndex()} to delete specific indexes.
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
				$this->mConnection->deleteIndexes();
			
			//
			// Delete specific indexes.
			//
			else
				$this->mConnection->deleteIndex( $theIndex );
		
		} // Connected.
		
		else
			throw new \Exception(
				"Unable to delete index: "
			   ."connection is not open." );									// !@! ==>
	
	} // deleteIndex.

		

/*=======================================================================================
 *																						*
 *									PUBLIC TYPE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getTimeStamp																	*
	 *==================================================================================*/

	/**
	 * Get time-stamp
	 *
	 * In this class we return a MongoDate value.
	 *
	 * @access public
	 * @return mixed				Native current time-stamp.
	 */
	public function getTimeStamp()							{	return new \MongoDate();	}

	 
	/*===================================================================================
	 *	parseTimeStamp																	*
	 *==================================================================================*/

	/**
	 * Get time-stamp
	 *
	 * In this class we convert the time-stamp 
	 *
	 * @param mixed					$theStamp			Time-stamp.
	 *
	 * @access public
	 * @return string				Human readable time-stamp.
	 */
	public function parseTimeStamp( $theStamp )
	{
		//
		// Check type.
		//
		if( $theStamp instanceof \MongoDate )
			return date( "r", $theStamp->sec );										// ==>
		
		return (string) $theStamp;													// ==>
	
	} // parseTimeStamp.

		

/*=======================================================================================
 *																						*
 *								PUBLIC INFORMATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Return collection name
	 *
	 * In this class we return the collection name, if the connection is set, or call the
	 * parent method.
	 *
	 * @access public
	 * @return string				Collection name.
	 */
	public function getName()
	{
		//
		// Check connection.
		//
		if( $this->mConnection instanceof \MongoCollection )
		{
			//
			// Get full name.
			//
			$name = $this->mConnection->getName();
			
			//
			// Separate namespaces.
			//
			$name = explode( '.', $name );
			
			return $name[ count( $name ) - 1 ];										// ==>
		
		} // Has connection.
		
		return parent::getName();													// ==>

	} // getName.

		

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
		$database = new \OntologyWrapper\MongoDatabase( $theParameter );
		
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

	 

} // trait MongoCollectionObjectTrait.


?>
