<?php

/**
 * MongoCollection.php
 *
 * This file contains the definition of the {@link MongoCollection} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\MongoDatabase;
use OntologyWrapper\CollectionObject;

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
 *									PUBLIC QUERY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	matchID																			*
	 *==================================================================================*/

	/**
	 * Match by ID
	 *
	 * In this class we use the <tt>findOne()</tt> method.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param boolean				$doAssert			Assert existance.
	 *
	 * @access public
	 * @return mixed				Matched object or <tt>NULL</tt>.
	 */
	public function matchID( $theIdentifier, $doAssert = TRUE )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Match identifier.
			//
			$found
				= $this->connection()
					->findOne( array( kTAG_NID => $theIdentifier ) );
			
			//
			// Handle not found.
			//
			if( $doAssert
			 && ($found === NULL) )
				throw new \Exception(
					"Unable to match identifier: "
				   ."object not found [".(string) $theIdentifier."]." );		// !@! ==>
			
			return $found;															// ==>
		
		} // Is connected.
			
		throw new \Exception(
			"Unable to match identifier: "
		   ."connection is not open." );										// !@! ==>
	
	} // matchID.

	 
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
	 * when retrieving objects or identifiers and {@link MongoCollection::find()} method
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
	public function matchOne( $theCriteria,
							  $theResult = kQUERY_DEFAULT,
							  $theFields = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle fields.
			//
			if( count( $theFields ) )
			{
				//
				// Prevent fields if requested object.
				//
				if( ($theResult & kRESULT_MASK) == kQUERY_OBJECT )
					$theFields = Array();
				
				//
				// Convert fields to object.
				// This is necessary since PHP treats numeric indexes as integers.
				//
				else
					$theFields = new \ArrayObject( $theFields );
			
			} // Provided fields selection.
						
			//
			// Get result.
			//
			switch( $theResult & kRESULT_MASK )
			{
				case kQUERY_NID:
					//
					// Convert fields to object.
					// This is necessary since PHP treats numeric indexes as integers.
					//
					$theFields = new \ArrayObject( array( kTAG_NID => TRUE ) );
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
	 * @param array					$theKey				Key offset.
	 *
	 * @access public
	 * @return ObjectIterator		Matched data iterator.
	 */
	public function matchAll( $theCriteria = Array(),
							  $theResult = kQUERY_DEFAULT,
							  $theFields = Array(),
							  $theKey = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle fields.
			//
			if( count( $theFields ) )
				$theFields = new \ArrayObject( $theFields );
		/*
			{
				//
				// Prevent fields if requested object.
				//
				if( ($theResult & kRESULT_MASK) == kQUERY_OBJECT )
					$theFields = Array();
				
				//
				// Convert fields to object.
				// This is necessary since PHP treats numeric indexes as integers.
				//
				else
					$theFields = new \ArrayObject( $theFields );
			
			} // Provided fields selection.
		*/
					
			//
			// Get result.
			//
			switch( $theResult & kRESULT_MASK )
			{
				case kQUERY_NID:
					//
					// Set native identifier in fields.
					// This is necessary since PHP treats numeric indexes as integers.
					//
					$theFields = new \ArrayObject( array( kTAG_NID => TRUE ) );
				case kQUERY_OBJECT:
				case kQUERY_ARRAY:
					$cursor
						= $this->
							mConnection->
								find( $theCriteria, $theFields );
					break;
					
				case kQUERY_COUNT:
					return $this->mConnection->find( $theCriteria )->count();		// ==>
			
			} // Parsed result flags.
			
			//
			// Handle no matches.
			//
			if( (! $cursor->count())
			 && ($theResult & kQUERY_ASSERT) )
				throw new \Exception(
					"No matches." );											// !@! ==>
			
			return new MongoIterator(
					$cursor, $this,
					$theCriteria, $theFields, $theKey,
					$theResult & kRESULT_MASK );									// ==>
		
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
	 * @param array					$theFields			Fields selection.
	 *
	 * @access public
	 * @return Iterator				Selection of all objects in the collection.
	 */
	public function getAll( $theFields = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Convert fields to object.
			// This is necessary since PHP treats numeric indexes as integers.
			//
			if( count( $theFields ) )
				$theFields = new \ArrayObject( $theFields );
			
			return $this->mConnection->find( Array(), $theFields );					// ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to get all object: "
		   ."connection is not open." );										// !@! ==>
	
	} // getAll.

	 
	/*===================================================================================
	 *	aggregate																		*
	 *==================================================================================*/

	/**
	 * Aggregate pipeline
	 *
	 * In this class we use the <tt>aggregateCursor()</tt> method.
	 *
	 * @param array					$thePipeline		Aggregation pipeline.
	 * @param array					$theOptions			Aggregation options.
	 *
	 * @access public
	 * @return Iterator				Aggregated results.
	 */
	public function aggregate( $thePipeline, $theOptions = Array() )
	{
//
// MILKO - For some reason the aggregate cursor doesn't work.
//
		return $this->mConnection->aggregate( $thePipeline, $theOptions );			// ==>
		return $this->mConnection->aggregateCursor( $thePipeline, $theOptions );	// ==>
	
	} // aggregate.

		

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
	 * In this class we use the <tt>$push</tt> and the <tt>$pull</tt> operators.
	 *
	 * When adding structures, the elements parameter should be structured as follows:
	 *
	 * <ul>
	 *	<li><em>key</em>: The structures list offset.
	 *	<li><em>value</em>: The structure to be added.
	 * </ul>
	 *
	 * When deleting, the elements array must be structured as follows:
	 *
	 * <ul>
	 *	<li><em>key</em>: The structures list offset.
	 *	<li><em>value</em>: The selection criteria to be applied to the list od structures.
	 * </ul>
	 *
	 * Note that the elements parameter is expected to be correct, no check is done.
	 *
	 * @param mixed					$theCriteria		Object selection criteria or id.
	 * @param array					$theElements		List of structure elements.
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
	 * In this class we use the <tt>createIndex</tt> Mongo method.
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
	 * In this class we use the getIndexInfo() Mongo method.
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
	 * In this class we use <tt>deleteIndexes()</tt> to delete all indexes and
	 * {@link deleteIndex()} to delete specific indexes.
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
 *									PUBLIC TYPE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getObjectId																		*
	 *==================================================================================*/

	/**
	 * Get object identifier
	 *
	 * In this class we return a MongoId.
	 *
	 * @param string				$theIdentifier		String version of the identifier.
	 *
	 * @access public
	 * @return MongoId				Native cobject identifier.
	 */
	public function getObjectId( $theIdentifier )
	{
		//
		// Normalise identifier.
		//
		if( ! ($theIdentifier instanceof \MongoId) )
		{
			//
			// Convert to string.
			//
			$theIdentifier = (string) $theIdentifier;

			//
			// Handle valid identifier.
			//
			if( \MongoId::isValid( $theIdentifier ) )
				$theIdentifier = new \MongoId( $theIdentifier );

			//
			// Invalid identifier.
			//
			else
				throw new \Exception(
					"Cannot use identifier: "
				   ."invalid identifier [$theIdentifier]." );					// !@! ==>
		
		} // Not a native identifier.
		
		return $theIdentifier;														// ==>
	
	} // getObjectId.

	 
	/*===================================================================================
	 *	setObjectId																		*
	 *==================================================================================*/

	/**
	 * Set object identifier
	 *
	 * In this class we expect a MongoId.
	 *
	 * @param MongoId				$theIdentifier		Native version of the identifier.
	 *
	 * @access public
	 * @return string				Object identifier as a string.
	 */
	public function setObjectId( $theIdentifier )
	{
		//
		// Check identifier.
		//
		if( $theIdentifier instanceof \MongoId )
			return (string) $theIdentifier;											// ==>
		
		$type = ( is_object( $theIdentifier ) )
			  ? get_class( $theIdentifier )
			  : gettype( $theIdentifier );
			
		throw new \Exception(
			"Unable to convert identifier: "
		   ."invalid identifier data type [$type]" );							// !@! ==>
	
	} // getObjectId.

	 
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

		

/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE INTERFACE							*
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
	protected function insertData( &$theData, $theOptions )
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
	 * In this class we save the provided array, update the object's {@link kTAG_CLASS} and
	 * return its {@link kTAG_NID} value.
	 *
	 * @param reference				$theData			Data to save.
	 * @param array					$theOptions			Replace options.
	 *
	 * @access protected
	 * @return mixed				Object identifier.
	 */
	protected function replaceData( $theData, $theOptions )
	{
		//
		// Serialise object.
		//
		ContainerObject::Object2Array( $theData, $data );
		
		//
		// Set class.
		//
		if( $theData instanceof PersistentObject )
			$data[ kTAG_CLASS ] = get_class( $theData );
		
		//
		// Replace.
		//
		$ok = $this->mConnection->save( $data, $theOptions );
		
		return $data[ kTAG_NID ];													// ==>
	
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
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier or <tt>NULL</tt>.
	 */
	protected function deleteIdentifier( $theIdentifier, $theOptions = Array() )
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
