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
	 * Property accessors trait.
	 *
	 * We use this trait to provide a common framework for methods that manage properties.
	 */
	use	traits\AccessorProperty;

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
	 * Criteria.
	 *
	 * This data member holds the query criteria.
	 *
	 * @var array
	 */
	 protected $mCriteria = NULL;

	/**
	 * Fields.
	 *
	 * This data member holds the selection fields.
	 *
	 * @var array
	 */
	 protected $mFields = NULL;

	/**
	 * Key.
	 *
	 * This data member holds the iterator key field.
	 *
	 * @var string
	 */
	 protected $mKey = NULL;

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
	 *	<li><b>$theCriteria</b>: The query filter.
	 *	<li><b>$theFields</b>: The query fields.
	 *	<li><b>$theKey</b>: The iterator key.
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
		// Set cursor.
		//
		$this->mCursor = $theCursor;
		
		//
		// Set collection.
		//
		$this->mCollection = $theCollection;
		
		//
		// Set criteria.
		//
		$this->mCriteria = $theCriteria;
		
		//
		// Add class to fields.
		//
		if( ($theResult & kRESULT_MASK) == kQUERY_OBJECT )
		{
			if( ( is_array( $theFields )
			   && (! array_key_exists( kTAG_CLASS, $theFields )) )
			 || ( ($theFields instanceof \ArrayObject)
			   && (! $theFields->offsetExists( kTAG_CLASS )) ) )
				$theFields[ kTAG_CLASS ] = TRUE;
		}
		
		//
		// Set fields.
		//
		$this->mFields = $theFields;
		
		//
		// Init default key.
		//
		if( $theKey === NULL )
		{
			//
			// Get key offset.
			//
			switch( $this->mCollection->connection()->getName() )
			{
				case Tag::kSEQ_NAME:
					$offset = Tag::GetReferenceKey();
					break;
		
				case Term::kSEQ_NAME:
					$offset = Term::GetReferenceKey();
					break;
		
				case Node::kSEQ_NAME:
					$offset = Node::GetReferenceKey();
					break;
		
				case Edge::kSEQ_NAME:
					$offset = Edge::GetReferenceKey();
					break;
		
				case User::kSEQ_NAME:
					$offset = EntityObject::GetReferenceKey();
					break;
		
				case UnitObject::kSEQ_NAME:
					$offset = UnitObject::GetReferenceKey();
					break;
			
				default:
					$offset = kTAG_NID;
					break;
		
			} // Parsed collection name.
		
		} // Key not provided.
		
		//
		// Set key.
		//
		$this->mKey = $theKey;
		
		//
		// Set result.
		//
		$this->resultType( $theResult & kRESULT_MASK );
		
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
	 *	criteria																		*
	 *==================================================================================*/

	/**
	 * <h4>Return criteria</h4>
	 *
	 * This method can be used to retrieve the current object's criteria, which is the query
	 * filter.
	 *
	 * @access public
	 * @return array				Query filter.
	 */
	public function criteria()								{	return $this->mCriteria;	}

	 
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

	 
	/*===================================================================================
	 *	setKeyOffset																	*
	 *==================================================================================*/

	/**
	 * Set key offset
	 *
	 * This method can be used to set the key offset, it expects a parameter which represents
	 * the offset in the current element from which to get the current key value.
	 *
	 * @param mixed					$theOffset			Key offset.
	 *
	 * @access public
	 */
	public function setKeyOffset( $theOffset )				{	$this->mKey = $theOffset;	}

		

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
	 * @uses shapeResult()
	 */
	public function current()
	{
		return $this->shapeResult( $this->mCursor->current() );						// ==>

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
	 */
	public function key()
	{
		//
		// Init local storage.
		//
		$current = $this->mCursor->current();
		
		//
		// Determine actual key.
		//
		if( array_key_exists( $this->mKey, $current ) )
			return $current[ $this->mKey ];											// ==>
		
		return $this->mCursor->key();												// ==>
	
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
	public function next()										{	$this->mCursor->next();	}

	 
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
	public function rewind()								{	$this->mCursor->rewind();	}

	 
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
	public function valid()								{	return $this->mCursor->valid();	}

		

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
	 * This method can be used to skip a number of records, if you provide an integer, the
	 * iterator will start from the element corresponding to the provided value; in this
	 * case the method should be used only before the cursor has beed iterated.
	 *
	 * If you provide <tt>NULL</tt>, the method should return the current skip value.
	 *
	 * The method will return the current skip value.
	 *
	 * @param integer				$theCount			Number of elements to skip.
	 *
	 * @access public
	 * @return integer				Current skip value.
	 */
	abstract public function skip( $theCount = NULL );

	 
	/*===================================================================================
	 *	limit																			*
	 *==================================================================================*/

	/**
	 * <h4>Limit the number of elements</h4>
	 *
	 * This method can be used to provide the maximum number of records to be returned, if
	 * you provide an integer, the iterator will limit its results to the number
	 * corresponding to the provided value; in this case the method should be used only
	 * before the cursor has beed iterated.
	 *
	 * If you provide <tt>NULL</tt>, the method should return the current limit value.
	 *
	 * The method will return the current limit value.
	 *
	 * @param integer				$theCount			Maximum number of iterations.
	 *
	 * @access public
	 * @return integer				Current limit value.
	 */
	abstract public function limit( $theCount = NULL );

	 
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
	 * If you provide <tt>NULL</tt>, the method will return the current fields selection.
	 *
	 * The method will return the current fields selection.
	 *
	 * @param array					$theFields			Fields selection.
	 *
	 * @access public
	 * @return array				Current fields selection.
	 */
	abstract public function fields( $theFields = NULL );

	 
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
	 * @return array				Current sort order.
	 */
	abstract public function sort( $theOrder );

	 
	/*===================================================================================
	 *	setTimeout																		*
	 *==================================================================================*/

	/**
	 * <h4>Set cursor timeout</h4>
	 *
	 * This method should be used to set the cursor timeout, the method should be available
	 * also once the cursor has been iterated.
	 *
	 * The method accepts a single parameter indicating the timeout in milliseconds.
	 *
	 * @param int					$theTimeout			Timeout in milliseconds.
	 *
	 * @access public
	 */
	abstract public function setTimeout( $theTimeout );

		

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
		
			case kQUERY_ARRAY:
				return $theObject;													// ==>
		
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
