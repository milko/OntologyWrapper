<?php

/**
 * ArrayCursorIterator.php
 *
 * This file contains the definition of the {@link ArrayCursorIterator} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ObjectIterator;

/*=======================================================================================
 *																						*
 *								ArrayCursorIterator.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo iterator object
 *
 * This <i>concrete</i> class derived from {@link ObjectIterator} implements a query
 * iterator which uses an array iterator instance as the object cursor.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 04/09/2014
 */
class ArrayCursorIterator extends ObjectIterator
{
	/**
	 * Skip.
	 *
	 * This data member holds the skip value.
	 *
	 * @var int
	 */
	 protected $mSkip = 0;

	/**
	 * Limit.
	 *
	 * This data member holds the limit value.
	 *
	 * @var int
	 */
	 protected $mLimit = NULL;

		

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
	 * We overload the parent constructor to check whether the cursor and collection are
	 * Mongo objects.
	 *
	 *	<li><b>$theCursor</b>: The query result cursor.
	 *	<li><b>$theCollection</b>: The collection to which the query was applied.
	 *	<li><b>$theCriteria</b>: The query filter.
	 *	<li><b>$theFields</b>: The query fields.
	 *	<li><b>$theResult</b>: A bitfield value determining what kind of data the iterator
	 *		will return:
	 *	 <ul>
	 *		<li><tt>{@link kQUERY_OBJECT}</tt>: Return the matched object.
	 *		<li><tt>{@link kQUERY_ARRAY}</tt>: Return the matched object array value.
	 *		<li><tt>{@link kQUERY_NID}</tt>: Return the matched object native identifier.
	 *	 </ul>
	 * </ul>
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
												  $theResult = kQUERY_ARRAY )
	{
		//
		// Check collection.
		//
		if( ! ($theCollection->connection() instanceof \MongoCollection) )
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
 *								PUBLIC COUNTABLE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	count																			*
	 *==================================================================================*/

	/**
	 * <h4>Return cursor count</h4>
	 *
	 * We act as a proxy for the object's cursor, in this case we get the count including
	 * eventual limits and skip.
	 *
	 * @access public
	 * @return int					Element count including skip and limits.
	 */
	public function count()
	{
		return $this->mCursor->count();												// ==>

	} // count.

		

/*=======================================================================================
 *																						*
 *									COUNTABLE INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	affectedCount																	*
	 *==================================================================================*/

	/**
	 * <h4>Return affected count</h4>
	 *
	 * In this class we get the actual count, since there is no concept of affected count.
	 *
	 * @access public
	 * @return int					Element count excluding skip and limits.
	 */
	public function affectedCount()								{	return $this->count();	}

		

/*=======================================================================================
 *																						*
 *								PUBLIC CURSOR INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	keys																			*
	 *==================================================================================*/

	/**
	 * <h4>Return the cursor keys</h4>
	 *
	 * Return the iterator keys as an array, we iterate a copy of the current iterator.
	 *
	 * @access public
	 * @return array
	 */
	public function keys()
	{
		//
		// Init local storage.
		//
		$keys = Array();
		
		//
		// Create other iterator.
		//
		$iterator = new static( $this->mCursor, $this->collection(), $this->resultType() );
		
		//
		// Fill keys.
		//
		$iterator->rewind();
		while( $iterator->valid() )
		{
			//
			// Add key.
			//
			$keys[] = $iterator->key();
			
			//
			// Advance.
			//
			$iterator->next();
		}
		
		return $keys;																// ==>
	
	} // keys.

	 
	/*===================================================================================
	 *	skip																			*
	 *==================================================================================*/

	/**
	 * <h4>Skip a number of elements</h4>
	 *
	 * We slice the iterator.
	 *
	 * @param integer				$theCount			Number of elements to skip.
	 *
	 * @access public
	 * @return integer				Current skip value.
	 */
	public function skip( $theCount = NULL )
	{
		//
		// Set new value.
		//
		if( $theCount !== NULL )
		{
			//
			// Get array.
			//
			$array = iterator_to_array( $this->mCursor );
		
			//
			// Slice.
			//
			$array = array_slice( $array, (int) $theCount, FALSE );
		
			//
			// Replace.
			//
			$this->mCursor = new \ArrayIterator( $array );
			
			//
			// Set value.
			//
			$this->mSkip = (int) $theCount;
		}
		
		return $this->mSkip;														// ==>
	
	} // skip.

	 
	/*===================================================================================
	 *	limit																			*
	 *==================================================================================*/

	/**
	 * <h4>Limit the number of elements</h4>
	 *
	 * We slice the iterator.
	 *
	 * @param integer				$theCount			Maximum number of iterations.
	 *
	 * @access public
	 * @return integer				Current limit value.
	 */
	public function limit( $theCount = NULL )
	{
		//
		// Set new value.
		//
		if( $theCount !== NULL )
		{
			//
			// Get array.
			//
			$array = iterator_to_array( $this->mCursor );
			
			//
			// Determine slice.
			//
			$delta = ((int) $theCount) - count( $array );
		
			//
			// Slice.
			//
			if( $delta > 0 )
				$array = array_slice( $array, (int) ($delta * -1), FALSE );
		
			//
			// Replace.
			//
			$this->mCursor = new \ArrayIterator( $array );
			
			//
			// Set value.
			//
			$this->mLimit = (int) $theCount;
		}
		
		return $this->mLimit;														// ==>
	
	} // limit.

	 
	/*===================================================================================
	 *	fields																			*
	 *==================================================================================*/

	/**
	 * <h4>Select the fields to be returned</h4>
	 *
	 * We remove excluded fields.
	 *
	 * @param array					$theFields			Fields selection.
	 *
	 * @access public
	 * @return array				Current fields selection.
	 */
	public function fields( $theFields = NULL )
	{
		//
		// Check fields.
		//
		if( $theFields !== NULL )
		{
			//
			// Normalise fields.
			//
			$no = $yes = Array();
			foreach( $theFields as $key => $value )
			{
				if( $value )
					$yes[ $key ] = $value;
				else
					$no[ $key ] = $value;
			}
			
			//
			// Handle ID.
			//
			if( ! array_key_exists( kTAG_NID, $no ) )
				$yes[ kTAG_NID ] = TRUE;
			
			//
			// Iterate array keys.
			//
			$array = iterator_to_array( $this->mCursor );
			foreach( array_keys( $array ) as $key )
			{
				//
				// Select included.
				//
				if( count( $yes ) )
					$array[ $key ] = array_intersect_key( $array[ $key ], $yes );
			
				//
				// Remove excluded included.
				//
				if( count( $no ) )
					$array[ $key ] = array_diff_key( $array[ $key ], $no );
			
			} // Iterating array keys.
			
			//
			// Update cursor.
			//
			$this->mCursor = new \ArrayIterator( $array );
		
		} // Provided fields.
		
		return $theFields;															// ==>
	
	} // fields.

	 
	/*===================================================================================
	 *	sort																			*
	 *==================================================================================*/

	/**
	 * <h4>Sort the cursor</h4>
	 *
	 * We use the stored iterator function.
	 *
	 * @param array					$theOrder			Sort order indications.
	 *
	 * @access public
	 * @return array				Current sort order.
	 */
	public function sort( $theOrder )						{	$this->mCursor->asort();	}

	 
	/*===================================================================================
	 *	setTimeout																		*
	 *==================================================================================*/

	/**
	 * <h4>Set cursor timeout</h4>
	 *
	 * In this class we ignore this command.
	 *
	 * @param int					$theTimeout			Timeout in milliseconds.
	 *
	 * @access public
	 */
	public function setTimeout( $theTimeout )											   {}

	 

} // class ArrayCursorIterator.


?>
