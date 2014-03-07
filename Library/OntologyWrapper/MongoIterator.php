<?php

/**
 * MongoIterator.php
 *
 * This file contains the definition of the {@link MongoIterator} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\MongoIterator;
use OntologyWrapper\MongoCollection;

/*=======================================================================================
 *																						*
 *									MongoIterator.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo iterator object
 *
 * This <i>concrete</i> class derived from {@link IteratorObject} implements a query
 * iterator which uses a {@link MongoCursor} instance as the object cursor.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/03/2014
 */
class MongoIterator extends IteratorObject
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
	 * We overload the parent constructor to check whether the cursor and collection are
	 * Mongo objects.
	 *
	 * @param Iterator				$theCursor			Query cursor.
	 * @param CollectionObject		$theCollection		Query collection.
	 * @param bitfield				$theResult			Result type.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function __construct( \Iterator		  $theCursor,
								 CollectionObject $theCollection,
												  $theResult = kQUERY_ARRAY)
	{
		//
		// Check cursor.
		//
		if( ! ($theCursor instanceof \MongoCursor) )
			throw new \Exception(
				"Invalid cursor type." );										// !@! ==>
		
		//
		// Check collection.
		//
		if( ! ($theCollection->Connection() instanceof \MongoCollection) )
			throw new \Exception(
				"Invalid collection type." );									// !@! ==>
		
		//
		// Call parent constructor.
		//
		parent::__construct( $theCursor, $theCollection, $theResult );
		
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
		return $this->cursor()->count( TRUE );										// ==>

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
	 * In this class we get the affected count from the cursor.
	 *
	 * @access public
	 * @return int					Element count excluding skip and limits.
	 */
	public function affectedCount()				{	return $this->cursor()->count( FALSE );	}

		

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
		$iterator = new static( $this->cursor(), $this->collection(), $this->resultType() );
		
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
	 * This method should be used only before the cursor has beed iterated, it will skip a
	 * number of elements before starting to iterate.
	 *
	 * If the cursor has already started iterating, the method should raise an exception.
	 *
	 * @param integer				$theCount			Number of elements to skip.
	 *
	 * @access public
	 */
	public function skip( $theCount )				{	$this->cursor()->skip( $theCount );	}

	 
	/*===================================================================================
	 *	limit																			*
	 *==================================================================================*/

	/**
	 * <h4>Limit the number of elements</h4>
	 *
	 * This method should be used only before the cursor has beed iterated, it will limit
	 * the number of elements to be iterated.
	 *
	 * If the cursor has already started iterating, the method should raise an exception.
	 *
	 * @param integer				$theCount			Maximum number of iterations.
	 *
	 * @access public
	 */
	public function limit( $theCount )			{	$this->cursor()->limit( $theCount );	}

	 
	/*===================================================================================
	 *	sort																			*
	 *==================================================================================*/

	/**
	 * <h4>Sort the cursor</h4>
	 *
	 * This method should be used only before the cursor has beed iterated, it will sort the
	 * cursor elements according to the provided array parameter:
	 *
	 * <ul>
	 *	<li><tt>key</tt>: The key corresponds to the field to be sorted.
	 *	<li><tt>value</tt>: The direction in which to sort:
	 *	 <ul>
	 *		<li><tt>1</tt>: Ascending.
	 *		<li><tt>-1</tt>: Descending.
	 *	 </ul>
	 * </ul>
	 *
	 * The sort is also determined by the order in which the array elements are provided.
	 *
	 * If the cursor has already started iterating, the method should raise an exception.
	 *
	 * @param array					$theOrder			Sort order indications.
	 *
	 * @access public
	 */
	public function sort( $theOrder )				{	$this->cursor()->sort( $theOrder );	}

	 
	/*===================================================================================
	 *	fields																			*
	 *==================================================================================*/

	/**
	 * <h4>Select the fields to be returned</h4>
	 *
	 * This method should be used only before the cursor has beed iterated, it will indicate
	 * which fields the cursor should return according to the provided array parameter:
	 *
	 * <ul>
	 *	<li><tt>key</tt>: The key corresponds to the field to be selected.
	 *	<li><tt>value</tt>: Whether to include or exclude it:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Include the field and exclude all others.
	 *		<li><tt>FALSE</tt>: Exclude the field and include all others.
	 *	 </ul>
	 * </ul>
	 *
	 * If the cursor has already started iterating, the method should raise an exception.
	 *
	 * @param array					$theFields			Fields selection.
	 *
	 * @access public
	 */
	public function fields( $theFields )		{	$this->cursor()->fields( $theFields );	}

	 

} // class MongoIterator.


?>
