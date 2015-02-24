<?php

/**
 * MongoIterator.php
 *
 * This file contains the definition of the {@link MongoIterator} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ObjectIterator;
use OntologyWrapper\MongoCollection;

/*=======================================================================================
 *																						*
 *									MongoIterator.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo iterator object
 *
 * This <i>concrete</i> class derived from {@link ObjectIterator} implements a query
 * iterator which uses a {@link MongoCursor} instance as the object cursor.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/03/2014
 */
class MongoIterator extends ObjectIterator
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
		// Check cursor.
		//
		if( ! ($theCursor instanceof \MongoCursor) )
			throw new \Exception(
				"Invalid cursor type." );										// !@! ==>
		
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
		return $this->mCursor->count( TRUE );										// ==>

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
	public function affectedCount()				{	return $this->mCursor->count( FALSE );	}

		

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
	 * When setting the value, you should do so only before the iterator was iterated.
	 *
	 * @param integer				$theCount			Number of elements to skip.
	 *
	 * @access public
	 * @return integer				Current skip value.
	 */
	public function skip( $theCount = NULL )
	{
		//
		// Skip.
		//
		if( $theCount !== NULL )
			$this->mCursor->skip( $theCount );
		
		return $this->mCursor->info()[ 'skip' ];									// ==>
	
	} // skip.

	 
	/*===================================================================================
	 *	limit																			*
	 *==================================================================================*/

	/**
	 * <h4>Limit the number of elements</h4>
	 *
	 * When setting the value, you should do so only before the iterator was iterated.
	 *
	 * @param integer				$theCount			Maximum number of iterations.
	 *
	 * @access public
	 * @return integer				Current limit value.
	 */
	public function limit( $theCount = NULL )
	{
		//
		// Limit.
		//
		if( $theCount !== NULL )
			$this->mCursor->limit( $theCount );
		
		return $this->mCursor->info()[ 'limit' ];									// ==>
	
	} // limit.

	 
	/*===================================================================================
	 *	fields																			*
	 *==================================================================================*/

	/**
	 * <h4>Select the fields to be returned</h4>
	 *
	 * When setting the value, you should do so only before the iterator was iterated.
	 *
	 * @param array					$theFields			Fields selection.
	 *
	 * @access public
	 * @return array				Current fields selection.
	 */
	public function fields( $theFields = NULL )
	{
		//
		// Limit.
		//
		if( $theFields !== NULL )
			$this->mCursor->fields( $theFields );
		
		return $this->mCursor->info()[ 'fields' ];									// ==>
	
	} // fields.

	 
	/*===================================================================================
	 *	sort																			*
	 *==================================================================================*/

	/**
	 * <h4>Sort the cursor</h4>
	 *
	 * Use this method only before the iterator was iterated.
	 *
	 * @param array					$theOrder			Sort order indications.
	 *
	 * @access public
	 * @return array				Current sort order.
	 */
	public function sort( $theOrder )				{	$this->mCursor->sort( $theOrder );	}

	 
	/*===================================================================================
	 *	setTimeout																		*
	 *==================================================================================*/

	/**
	 * <h4>Set cursor timeout</h4>
	 *
	 * This method can be used also once the iterator has been iterated.
	 *
	 * @param int					$theTimeout			Timeout in milliseconds.
	 *
	 * @access public
	 */
	public function setTimeout( $theTimeout )	{	$this->mCursor->timeout( $theTimeout );	}

	 

} // class MongoIterator.


?>
