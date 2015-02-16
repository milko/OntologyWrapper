<?php

/**
 * MongoObjectCollectionTrait.php
 *
 * This file contains the definition of the {@link MongoObjectCollectionTrait} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *							MongoObjectCollectionTrait.php								*
 *																						*
 *======================================================================================*/

/**
 * MongoDB object collection trait
 *
 * The main purpose of this trait is to implement the MongoDB specific methods related to
 * the {@link ObjectCollection} abstract class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 15/02/2015
 */
trait MongoObjectCollectionTrait
{
		

/*=======================================================================================
 *																						*
 *									PUBLIC QUERY INTERFACE								*
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
			
			return new \OntologyWrapper\MongoIterator(
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
	protected function insertData( &$theData, $theOptions )
	{
		//
		// Serialise object.
		//
		\OntologyWrapper\ContainerObject::Object2Array( $theData, $data );
		
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
		\OntologyWrapper\ContainerObject::Object2Array( $theData, $data );
		
		//
		// Replace.
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

		

/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
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
	protected function deleteIdentifier( $theIdentifier, $theOptions )
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

	 

} // trait MongoObjectCollectionTrait.


?>
