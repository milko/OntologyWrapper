<?php

/**
 * IteratorObject.php
 *
 * This file contains the definition of the {@link IteratorObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									IteratorObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Iterator object
 *
 * This <i>abstract</i> class represents the template of iterator objects which are used to
 * scan the results of a query. The class implements the {@link Iterator} and
 * {@link Countable} interfaces and it declares a series of prototypes to be implemented by
 * derived concrete classes.
 *
 * In this class the {@link Iterator} and {@link Countable} interfaces are proxies of the
 * cursor data member, all calls are routed to the cursor, in concrete derived classes you
 * can overload these methods to handle custom cursors.
 *
 * The class declares another set of virtual methods which perform specific actions on the
 * cursor:
 *
 * <ul>
 *	<li><tt>{@link affectedCount()</tt>: Return cursor affected count.
 *	<li><tt>{@link keys()</tt>: Return cursor keys.
 *	<li><tt>{@link skip()</tt>: Skip a number of elements before iterating.
 *	<li><tt>{@link limit()</tt>: Limit the maximum number of iterations.
 *	<li><tt>{@link sort()</tt>: Sort the cursor.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/03/2014
 */
abstract class IteratorObject implements \Iterator,
										 \Countable
{
	/**
	 * Accessors trait.
	 *
	 * We use this trait to provide a common framework for methods that manage properties
	 * and offsets.
	 */
	use	traits\Accessors;

	/**
	 * Cursor.
	 *
	 * This data member holds the query cursor.
	 *
	 * @var Iterator
	 */
	 protected $mCursor = NULL;

	/**
	 * Collection.
	 *
	 * This data member holds the collection.
	 *
	 * @var CollectionObject
	 */
	 protected $mCollection = NULL;

	/**
	 * Result.
	 *
	 * This data member holds an enumerated value determining what the iterator should
	 * return:
	 *
	 * <ul>
	 *	<li><tt>{@link kQUERY_OBJECT}</tt>: Return the matched object.
	 *	<li><tt>{@link kQUERY_ARRAY}</tt>: Return the matched object array value.
	 *	<li><tt>{@link kQUERY_NID}</tt>: Return the matched object native identifier.
	 * </ul>
	 *
	 * @var bitfield
	 */
	 protected $mResultType = NULL;

		

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
	 * The constructor expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCursor</b>: The query result cursor.
	 *	<li><b>$theCollection</b>: The collection to which the query was applied.
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
	 * @param bitfield				$theResult			Result type.
	 *
	 * @access public
	 */
	public function __construct( \Iterator		  $theCursor,
								 CollectionObject $theCollection,
												  $theResult = kQUERY_ARRAY)
	{
		//
		// Set cursor.
		//
		$this->mCursor = $theCursor;
		
		//
		// Set collection.
		//
		$this->mCollection = $theCollection;
		
		//
		// Set result.
		//
		$this->resultType( $theResult );
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC MEMBER INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	cursor																			*
	 *==================================================================================*/

	/**
	 * <h4>Return cursor</h4>
	 *
	 * This method can be used to retrieve the current object's cursor, which is the native
	 * cursor returned by the query.
	 *
	 * @access public
	 * @return Iterator				Query cursor.
	 */
	public function cursor()									{	return $this->mCursor;	}

	 
	/*===================================================================================
	 *	collection																		*
	 *==================================================================================*/

	/**
	 * <h4>Return collection</h4>
	 *
	 * This method can be used to retrieve the current object's collection, which is the
	 * collection object that was queried.
	 *
	 * @access public
	 * @return CollectionObject		Query collection.
	 */
	public function collection()							{	return $this->mCollection;	}

	 
	/*===================================================================================
	 *	resultType																		*
	 *==================================================================================*/

	/**
	 * Manage result type
	 *
	 * This method can be used to manage the <i>result type</i>, it accepts a parameter
	 * which represents either the result type code or the requested operation, depending on
	 * its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>FALSE</tt>: Delete the current value.
	 *	<li><tt>{@link kQUERY_OBJECT}</tt>: Return an object.
	 *	<li><tt>{@link kQUERY_ARRAY}</tt>: Return the object array.
	 *	<li><tt>{@link kQUERY_NID}</tt>: Return the object native identifier.
	 *	<li><i>other</i>: Will raise an exception.
	 * </ul>
	 *
	 * The second parameter is a boolean which if <tt>TRUE</tt> will return the <i>old</i>
	 * value when replacing or resetting; if <tt>FALSE</tt>, it will return the current
	 * value.
	 *
	 * @param mixed					$theValue			Data source name or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 * @param boolean				$doSync				<tt>TRUE</tt> will sync offsets.
	 *
	 * @access public
	 * @return mixed				<i>New</i> or <i>old</i> result type code.
	 *
	 * @throws Exception
	 *
	 * @see $mResultType
	 *
	 * @uses manageProperty()
	 * @uses resultType()
	 */
	public function resultType( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Check new value.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE) )
		{
			switch( $theValue )
			{
				case kQUERY_OBJECT:
				case kQUERY_ARRAY:
				case kQUERY_NID:
					break;
				
				default:
					throw new \Exception(
						"Invalid result type code." );							// !@! ==>
			}
		
		} // Verifying result type code.
		
		return $this->manageProperty( $this->mResultType, $theValue, $getOld );		// ==>
	
	} // resultType.

		

/*=======================================================================================
 *																						*
 *								PUBLIC ITERATOR INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	current																			*
	 *==================================================================================*/

	/**
	 * <h4>Return the value of the current element</h4>
	 *
	 * In this class we take the result of the cursor and pass it to the
	 * {@link shapeResult()} method which takes care of returning the desired type of value.
	 *
	 * @access public
	 * @return mixed				Current element value.
	 *
	 * @uses cursor()
	 * @uses shapeResult()
	 */
	public function current()
	{
		return $this->shapeResult( $this->cursor()->current() );					// ==>

	} // current.

	 
	/*===================================================================================
	 *	key																				*
	 *==================================================================================*/

	/**
	 * <h4>Return the key of the current element</h4>
	 *
	 * In this class we use the current element's native identifier as the key, or the
	 * cursor's key if the former is not available.
	 *
	 * @access public
	 * @return mixed				Current element's key.
	 *
	 * @uses cursor()
	 */
	public function key()
	{
		//
		// Get current element.
		//
		$current = $this->cursor()->current();
		
		//
		// Determine actual key.
		//
		if( array_key_exists( kTAG_NID, $current ) )
			return $current[ kTAG_NID ];											// ==>
		
		return $this->cursor()->key();												// ==>
	
	} // key.

	 
	/*===================================================================================
	 *	next																			*
	 *==================================================================================*/

	/**
	 * <h4>Advance to the next element</h4>
	 *
	 * We act as a proxy for the cursor.
	 *
	 * @access public
	 */
	public function next()									{	$this->cursor()->next();	}

	 
	/*===================================================================================
	 *	rewind																			*
	 *==================================================================================*/

	/**
	 * <h4>Rewind iterator</h4>
	 *
	 * We act as a proxy for the cursor.
	 *
	 * @access public
	 */
	public function rewind()								{	$this->cursor()->rewind();	}

	 
	/*===================================================================================
	 *	valid																			*
	 *==================================================================================*/

	/**
	 * <h4>Check if iterator is valid</h4>
	 *
	 * We act as a proxy for the cursor.
	 *
	 * @access public
	 * @return boolean
	 */
	public function valid()							{	return $this->cursor()->valid();	}

		

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
	 * We act as a proxy for the object's cursor, note that we should get the count
	 * including eventual limits and skip.
	 *
	 * In this class we return the cursor count.
	 *
	 * @access public
	 * @return int					Element count including skip and limits.
	 */
	public function count()
	{
		return $this->cursor()->count();											// ==>

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
	 * Return the total count excluding eventual limits and skip.
	 *
	 * Note that the cursor must implement the Countable interface in which the
	 * <tt>count()</tt> method should return the count including skip and limits.
	 *
	 * @access public
	 * @return int					Element count excluding skip and limits.
	 */
	abstract public function affectedCount();

		

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
	 * This method will return the keys of the cursor as an array.
	 *
	 * @access public
	 * @return array
	 */
	abstract public function keys();

	 
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
	abstract public function skip( $theCount );

	 
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
	abstract public function limit( $theCount );

	 
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
	abstract public function sort( $theOrder );

	 
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
	abstract public function fields( $theFields );

		

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
	 * This method will format the provided cursor value according to the
	 * {@link resultType()}.
	 *
	 * Depending on the {@link resultType()} value:
	 *
	 * <ul>
	 *	<li><tt>{@link kQUERY_OBJECT}</tt>: Return an object.
	 *	<li><tt>{@link kQUERY_ARRAY}</tt>: Return the object array.
	 *	<li><tt>{@link kQUERY_NID}</tt>: Return the object native identifier.
	 *	<li><i>other</i>: Will raise an exception.
	 * </ul>
	 *
	 * The method expects the value provided as an array.
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
			case kQUERY_ARRAY:
				return $theObject;													// ==>
		
			case kQUERY_OBJECT:
				//
				// Check class.
				//
				if( ! array_key_exists( kTAG_CLASS, $theObject ) )
					throw new \Exception(
						"Unable to create object: "
					   ."missing object class." );								// !@! ==>
				
				$class = $theObject[ kTAG_CLASS ];
				
				return new $class( $this->collection()->dictionary(), $theObject );	// ==>
		
			case kQUERY_NID:
				//
				// Check identifier.
				//
				if( ! array_key_exists( kTAG_NID, $theObject ) )
					throw new \Exception(
						"Unable to return identifier: "
					   ."not included in object." );							// !@! ==>
				
				return $theObject[ kTAG_NID ];										// ==>
		
		} // Parsed result type.
			
		throw new \Exception(
			"Invalid or unsupported result type." );							// !@! ==>
	
	} // shapeResult.

	 

} // class IteratorObject.


?>
