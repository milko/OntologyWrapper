<?php

/**
 * MongoFileIterator.php
 *
 * This file contains the definition of the {@link MongoFileIterator} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\MongoIterator;

/*=======================================================================================
 *																						*
 *								MongoFileIterator.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo file iterator object
 *
 * This class derived from {@link ObjectIterator} implements a query iterator which uses a
 * MongoGridFSCursor instance as the object cursor.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 22/02/2015
 */
class MongoFileIterator extends MongoIterator
{
		

/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * Instantiate class.
	 *
	 * We overload the parent constructor to assert MongoGridFS and MongoGridFSCursor
	 * parameters.
	 *
	 * @param Iterator				$theCursor			Query cursor.
	 * @param CollectionObject		$theCollection		Query collection.
	 * @param array					$theCriteria		Query criteria.
	 * @param array					$theFields			Query fields.
	 * @param mixed					$theKey				Iterator key.
	 * @param bitfield				$theResult			Result type.
	 *
	 * @access public
	 */
	public function __construct( \Iterator		  $theCursor,
								 CollectionObject $theCollection,
								 				  $theCriteria,
								 				  $theFields = Array(),
								 				  $theKey = NULL,
												  $theResult = kQUERY_OBJECT )
	{
		//
		// Check cursor.
		//
		if( ! ($theCursor instanceof \MongoGridFSCursor) )
			throw new \Exception(
				"Invalid cursor type." );										// !@! ==>
		
		//
		// Check collection.
		//
		if( ! ($theCollection->connection() instanceof \MongoGridFS) )
			throw new \Exception(
				"Invalid collection type." );									// !@! ==>
		
		//
		// Call parent constructor.
		//
		parent::__construct( $theCursor,
							 $theCollection, $theCriteria,
							 $theFields, $theKey,
							 $theResult );
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PROTECTED DATA INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	shapeResult																		*
	 *==================================================================================*/

	/**
	 * Cast iterator value
	 *
	 * We overload this class to prevent array results and handle MongoFileObject instances.
	 *
	 * @param array					$theObject			Current cursor value.
	 *
	 * @access protected
	 * @return mixed				The formatted value.
	 *
	 * @throws Exception
	 */
	protected function shapeResult( $theObject )
	{
		//
		// Parse by result type.
		//
		switch( $this->resultType() )
		{
			case kQUERY_OBJECT:
				return new MongoFileObject( $this->collection()->dictionary(),
											$theObject );							// ==>
		
			case kQUERY_ARRAY:
				return $theObject->file;											// ==>
		
			case kQUERY_NID:
				//
				// Check identifier.
				//
				if( ! array_key_exists( kTAG_NID, $theObject->file ) )
					throw new \Exception(
						"Unable to return identifier: "
					   ."not included in object." );							// !@! ==>
				
				return $theObject->file[ kTAG_NID ];								// ==>
		
		} // Parsed result type.
			
		throw new \Exception(
			"Invalid or unsupported result type." );							// !@! ==>
	
	} // shapeResult.

	 

} // class MongoIterator.


?>
