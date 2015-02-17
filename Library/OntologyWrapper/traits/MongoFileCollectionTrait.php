<?php

/**
 * MongoFileCollectionTrait.php
 *
 * This file contains the definition of the {@link MongoFileCollectionTrait} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *							MongoFileCollectionTrait.php								*
 *																						*
 *======================================================================================*/

/**
 * MongoDB file collection trait
 *
 * The main purpose of this trait is to implement the MongoDB specific methods related to
 * the {@link FileCollection} abstract class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 15/02/2015
 */
trait MongoFileCollectionTrait
{
		

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
	 * In this class we use the {@link MongoGridFS::get() }.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 *
	 * @access public
	 * @return mixed				Matched object or <tt>NULL</tt>.
	 */
	public function matchID( $theIdentifier )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
			return $this->connection()->get( $theIdentifier );						// ==>
			
		throw new \Exception(
			"Unable to match object: "
		   ."connection is not open." );										// !@! ==>
	
	} // matchOne.

	 
	/*===================================================================================
	 *	matchOne																		*
	 *==================================================================================*/

	/**
	 * Match one object
	 *
	 * We first check if the current collection is connected, if that is not the case, we
	 * raise an exception.
	 *
	 * In this class we map the method over the {@link MongoGridFS::findOne()} method when
	 * retrieving objects or identifiers and {@link MongoGridFS::find()} method when
	 * retrieving counts.
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
							  $theResult = kQUERY_OBJECT,
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
			// Query collection.
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
				case kQUERY_NID:
				
					if( ! array_key_exists( kTAG_NID, $object ) )
						throw new \Exception(
							"Unable to resolve identifier: "
						   ."missing object identifier." );						// !@! ==>
					
					return $object[ kTAG_NID ];										// ==>
				
				case kQUERY_OBJECT:
					
					return $object;													// ==>
			
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
	 * In this class we perform the query using the {@link MongoGridFS::find()} method, we
	 * then return a {@link MongoGridFSCursor} instance with the query cursor and
	 * collection.
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
							  $theResult = kQUERY_OBJECT,
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
			
			return $cursor;															// ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to perform query: "
		   ."connection is not open." );										// !@! ==>
	
	} // matchAll.

	 

/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	storeFile																		*
	 *==================================================================================*/

	/**
	 * Store file
	 *
	 * In this class we use the <tt>MongoGridFS</tt> object to store the provided file.
	 *
	 * @param SplFileInfo			$theFile			File reference.
	 * @param array					$theMetadata		File metadata.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object native identifier.
	 */
	protected function storeFile( \SplFileInfo $theFile, $theMetadata, $theOptions )
	{
		return $this->connection()->storeFile(
					$theFile->getRealPath(), $theMetadata, $theOptions );			// ==>
	
	} // storeFile.

	 
	/*===================================================================================
	 *	storeUpload																		*
	 *==================================================================================*/

	/**
	 * Store uploaded file
	 *
	 * In this class we use the <tt>MongoGridFS</tt> object to store the provided file.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param string				$theFile			Name attribute value.
	 * @param array					$theMetadata		File metadata.
	 *
	 * @access protected
	 * @return mixed				Object native identifier.
	 */
	protected function storeUpload( $theFile, $theMetadata )
	{
		return $this->connection()->storeFile( $theFile, $theMetadata );			// ==>
	
	} // storeUpload.

	 
	/*===================================================================================
	 *	storeData																		*
	 *==================================================================================*/

	/**
	 * Delete provided identifier
	 *
	 * In this class we use the <tt>MongoGridFS</tt> object to store the provided data.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param string				$theData			Data bytes string.
	 * @param array					$theMetadata		File metadata.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object native identifier.
	 */
	protected function storeData( $theData, $theMetadata, $theOptions )
	{
		return $this->connection()
					->storeBytes( $theData, $theMetadata, $theOptions );			// ==>
	
	} // storeData.

	 
	/*===================================================================================
	 *	deleteIdentifier																*
	 *==================================================================================*/

	/**
	 * Delete provided identifier
	 *
	 * In this class we use the <tt>MongoGridFS</tt> delete method; the options are ignored
	 * here.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param array					$theOptions			Delete options.
	 *
	 * @access protected
	 * @return mixed				Object identifier or <tt>NULL</tt>.
	 */
	protected function deleteIdentifier( $theIdentifier, $theOptions = Array() )
	{
		return $this->connection()->delete( $theIdentifier, $theOptions );			// ==>
	
	} // deleteIdentifier.

	 
	/*===================================================================================
	 *	deleteSelection																	*
	 *==================================================================================*/

	/**
	 * Delete selection
	 *
	 * This method should delete all objects matching the provided selection.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param array					$theOptions			Delete options.
	 *
	 * @access protected
	 * @return mixed				Operation status.
	 */
	protected function deleteSelection( $theCriteria, $theOptions )
	{
		return $this->connection()->remove( $theCriteria, $theOptions );			// ==>
	
	} // deleteSelection.

		

} // trait MongoFileCollectionTrait.


?>
