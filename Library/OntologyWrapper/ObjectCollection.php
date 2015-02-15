<?php

/**
 * ObjectCollection.php
 *
 * This file contains the definition of the {@link ObjectCollection} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *								ObjectCollection.php									*
 *																						*
 *======================================================================================*/

/**
 * Object collection
 *
 * This <i>abstract</i> class is the ancestor of all classes implementing a collection of
 * objects, the class derives its query interface from its ancestor and implements its
 * persistence interface by concentrating on storing objects.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
abstract class ObjectCollection extends CollectionObject
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
	 * This method should select a single object according to the provided criteria, the
	 * method should return a value according to the second parameter.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: This parameter represents the selection criteria, this
	 *		value is an array which represents a query expressed in the MongoDB query
	 *		language.
	 *	<li><b>$theResult</b>: This parameter determines what the method should return, it
	 *		is a bitfield which accepts two sets of values:
	 *	 <ul>
	 *		<li><tt>{@link kQUERY_ASSERT}</tt>: If this flag is set and the criteria doesn't
	 *			match any record, the method should raise an exception.
	 *		<li><em>Result type</em>: This set of values can be added to the previous flag,
	 *			only one of these should be provided:
	 *		 <ul>
	 *			<li><tt>{@link kQUERY_OBJECT}</tt>: Return the matched object.
	 *			<li><tt>{@link kQUERY_ARRAY}</tt>: Return the matched object array value.
	 *			<li><tt>{@link kQUERY_NID}</tt>: Return the matched object native
	 *				identifier.
	 *			<li><tt>{@link kQUERY_COUNT}</tt>: Return the number of matched objects.
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theFields</b>: This parameter represents the fields selection, it is an
	 *		array indexed by offset with a boolean value indicating whether or not to
	 *		include the field.
	 * </ul>
	 *
	 * If you omit the second parameter, the method should return the matched object.
	 *
	 * If there is more than one match for the provided criteria, this method will return
	 * only the first one, in no particular order.
	 *
	 * If there is no match, the method will return <tt>NULL</tt> if the
	 * {@link kQUERY_ASSERT} flag was <em>not</em> set, or raise an exception.
	 *
	 * Concrete derived classes should implement this method.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param bitfield				$theResult			Result type.
	 * @param array					$theFields			Fields selection.
	 *
	 * @access public
	 * @return mixed				Matched data or <tt>NULL</tt>.
	 */
	abstract public function matchOne( $theCriteria,
									   $theResult = kQUERY_DEFAULT,
									   $theFields = Array() );

	 
	/*===================================================================================
	 *	matchAll																		*
	 *==================================================================================*/

	/**
	 * Match all objects
	 *
	 * This method should select the set of objects matching the provided criteria, the
	 * method should return an object implementing the {@link Iterator}, {@link Countable}
	 * and {iCursor} interfaces.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: This parameter represents the selection criteria, this
	 *		value is an array which represents a query expressed in the MongoDB query
	 *		language.
	 *	<li><b>$theResult</b>: This parameter will be passed to the iterator returned by the
	 *		method, it determines what kind of data the iterator will return. This parameter
	 *		is a bitfield which accepts two sets of values:
	 *	 <ul>
	 *		<li><tt>{@link kQUERY_ASSERT}</tt>: If this flag is set and the criteria doesn't
	 *			match any record, the method should raise an exception.
	 *		<li><em>Result type</em>: This set of values can be added to the previous flag,
	 *			only one of these should be provided:
	 *		 <ul>
	 *			<li><tt>{@link kQUERY_OBJECT}</tt>: Return an object iterator (default).
	 *			<li><tt>{@link kQUERY_ARRAY}</tt>: Return an array iterator.
	 *			<li><tt>{@link kQUERY_NID}</tt>: Return an identifier iterator.
	 *		 </ul>
	 *			Any other value will trigger an exception.
	 *	 </ul>
	 *	<li><b>$theFields</b>: This parameter represents the fields selection, it is an
	 *		array indexed by offset with a boolean value indicating whether or not to
	 *		include the field.
	 *	<li><b>$theKey</b>: This parameter represents the iterator key offset, it can be
	 *		used to set which value the {@link key()} function should return: the value is
	 *		the offset that will be used to get the key value.
	 * </ul>
	 *
	 * If you omit the second parameter, the the iterator returned by this method will
	 * objects.
	 *
	 * Concrete derived classes should implement this method.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param bitfield				$theResult			Result type.
	 * @param array					$theFields			Fields selection.
	 * @param array					$theKey				Key offset.
	 *
	 * @access public
	 * @return ObjectIterator		Matched data iterator.
	 */
	abstract public function matchAll( $theCriteria = Array(),
									   $theResult = kQUERY_DEFAULT,
									   $theFields = Array(),
									   $theKey = NULL );

	 
	/*===================================================================================
	 *	getAll																			*
	 *==================================================================================*/

	/**
	 * Return all objects
	 *
	 * This method should select all the objects of the collection and return an iterator,
	 * this iterator is not an instance of {@link ObjectIterator}, but the cursor of the
	 * native database engine; by default it should be an iterator whose elements are array
	 * representations of the selected objects.
	 *
	 * Concrete derived classes should implement this method.
	 *
	 * @param array					$theFields			Fields selection.
	 *
	 * @access public
	 * @return Iterator				Selection of all objects in the collection.
	 */
	abstract public function getAll( $theFields = Array() );

	 
	/*===================================================================================
	 *	aggregate																		*
	 *==================================================================================*/

	/**
	 * Aggregate pipeline
	 *
	 * This method expects an aggregation pipeline and should return the result as an
	 * iterator.
	 *
	 * Concrete derived classes must implement this method.
	 *
	 * @param array					$thePipeline		Aggregation pipeline.
	 * @param array					$theOptions			Aggregation options.
	 *
	 * @access public
	 * @return Iterator				Aggregated results.
	 */
	abstract public function aggregate( $thePipeline, $theOptions = Array() );

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	commit																			*
	 *==================================================================================*/

	/**
	 * Insert an object
	 *
	 * The method expects the provided parameter to be either an array or an
	 * {@link ArrayObject} instance.
	 *
	 * The method will call the virtual {@link insertData()} method, passing the received
	 * object to it, which will perform the actual commit.
	 *
	 * The method will return the inserted object's identifier, {@link kTAG_NID}.
	 *
	 * @param mixed					$theObject			Object to commit.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access public
	 * @return mixed				Inserted object identifier.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_CLASS
	 *
	 * @uses isConnected()
	 * @uses insertData()
	 */
	public function commit( &$theObject, $theOptions = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Check object type.
			//
			if( is_array( $theObject )
			 || ($theObject instanceof \ArrayObject) )
				return $this->insertData( $theObject, $theOptions );				// ==>
			
			throw new \Exception(
				"Unable to commit object: "
			   ."provided invalid or unsupported data type." );					// !@! ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to commit object: "
		   ."connection is not open." );										// !@! ==>
	
	} // commit.

	 
	/*===================================================================================
	 *	save																			*
	 *==================================================================================*/

	/**
	 * Save or replace an object
	 *
	 * The method expects the provided parameter to be either an array or an
	 * {@link ArrayObject} instance.
	 *
	 * The method will call the virtual {@link replaceData()} method, passing the received
	 * object to it, which will perform the actual replace.
	 *
	 * The method will return the replaced object's identifier, {@link kTAG_NID}.
	 *
	 * @param reference				$theObject			Object to commit.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access public
	 * @return mixed				Replaced object identifier.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_CLASS
	 *
	 * @uses isConnected()
	 * @uses replaceData()
	 */
	public function save( &$theObject, $theOptions = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Check object type.
			//
			if( is_array( $theObject )
			 || ($theObject instanceof \ArrayObject) )
				return $this->replaceData( $theObject, $theOptions );				// ==>
			
			throw new \Exception(
				"Unable to save object: "
			   ."provided invalid or unsupported data type." );					// !@! ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to save object: "
		   ."connection is not open." );										// !@! ==>
	
	} // save.

		

/*=======================================================================================
 *																						*
 *							PUBLIC SEQUENCE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setSequenceNumber																*
	 *==================================================================================*/

	/**
	 * Set sequence number
	 *
	 * This method should initialise a sequence number associated to the provided parameter.
	 * This operation is equivalent to resetting an auto-number for a database.
	 *
	 * Once the sequence is set, the next requested sequence number will hold the value set
	 * by this method, so to start counting from <tt>1</tt> you should provide this value to
	 * this method.
	 *
	 * This method is intended to be handled by database objects, in this class we simply
	 * let the object's parent, a database, perform the action.
	 *
	 * Derived classes should never need to overload this method.
	 *
	 * @param string				$theSequence		Sequence selector.
	 * @param integer				$theNumber			Sequence number.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function setSequenceNumber( $theSequence, $theNumber = 1 )
	{
		//
		// Check parent.
		//
		if( ! ($this->mParent instanceof DatabaseObject) )
			throw new \Exception(
				"Unable to set sequence number: "
			   ."the collection is missing its database." );					// !@! ==>
		
		//
		// Let papa do it.
		//
		$this->mParent->setSequenceNumber( $theSequence, $theNumber );
	
	} // setSequenceNumber.

	 
	/*===================================================================================
	 *	getSequenceNumber																*
	 *==================================================================================*/

	/**
	 * Return sequence number
	 *
	 * This method should return a sequence number associated to the provided parameter.
	 * This operation is equivalent to requesting an auto-number for a database.
	 *
	 * Each time a sequence number is requested, the sequence seed is updated, so use this
	 * method only when the sequence is required.
	 *
	 * If the sequence selector is not found, a new one will be created starting with the
	 * number <tt>1</tt>, so, if you need to start with another number, use the
	 * {@link setSequenceNumber()} before.
	 *
	 * This method is intended to be handled by database objects, in this class we simply
	 * let the object's parent, a database, perform the action.
	 *
	 * Derived classes should never need to overload this method.
	 *
	 * @param string				$theSequence		Sequence selector.
	 *
	 * @access public
	 * @return integer				Sequence number.
	 */
	public function getSequenceNumber( $theSequence )
	{
		//
		// Check parent.
		//
		if( ! ($this->mParent instanceof DatabaseObject) )
			throw new \Exception(
				"Unable to get sequence number: "
			   ."the collection is missing its database." );					// !@! ==>
		
		return $this->mParent->getSequenceNumber( $theSequence );				 // ==>
	
	} // getSequenceNumber.

		

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
	 * This method should be implemented by concrete derived classes, it should commit a
	 * new record in the current collection featuring the provided data and return the
	 * record identifier.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param reference				$theData			Data to commit.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier.
	 */
	abstract protected function insertData( &$theData, $theOptions );

	 
	/*===================================================================================
	 *	replaceData																		*
	 *==================================================================================*/

	/**
	 * Save or replace provided data
	 *
	 * This method should be implemented by concrete derived classes, it should save or
	 * replace a record in the current collection featuring the provided data and return the
	 * record identifier.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param reference				$theData			Data to save.
	 * @param array					$theOptions			Replace options.
	 *
	 * @access protected
	 * @return mixed				Object identifier.
	 */
	abstract protected function replaceData( $theData, $theOptions );

	 

} // class ObjectCollection.


?>
