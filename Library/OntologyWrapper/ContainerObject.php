<?php

/**
 * ContainerObject.php
 *
 * This file contains the definition of the {@link ContainerObject} class.
 */

namespace OntologyWrapper;

/*=======================================================================================
 *																						*
 *									ContainerObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Container object
 *
 * This class is the ancestor of classes that define objects that handle structured data and
 * that are persistent.
 *
 * The class extends <code>{@link ArrayObject}</code> by treating the inherited array as the
 * persistent store and all other data members as run-time data. The convention is that
 * items stored in the array part of the object are called <em>offsets</em> and are
 * considered <em>persistent</em>, while all other data members are called <em>members</em>
 * and are considered <em>run-time</em> data.
 *
 * This class implements a framework that governs tha management of the object's persistent
 * data. No offset may hold the <tt>NULL</tt> value, setting an offset with <tt>NULL</tt>
 * value is equivalent to <em>deleting the offset</em>.
 *
 * Retrieving non-existant offsets will <em>not</em> generate a warning, but only return
 * <tt>NULL</tt>.
 *
 * The class features a series of methods that derived classes may use to customise the
 * behaviour of the offset management methods:
 *
 * <ul>
 *	<li><tt>{@link preOffsetExists()}</tt>: This method is called <i>before</i> the
 *		{@link offsetExists()} method with the offset passed as a reference parameter, the
 *		method can be used to change the value of the offset or to provide a custom result:
 *		if the method returns <tt>NULL</tt>, {@link offsetExists()} will be called; if the
 *		method returns any other type of value, this will be returned and
 *		{@link offsetExists()} will be skipped.
 *	<li><tt>{@link preOffsetGet()}</tt>: This is called <i>before</i> the
 *		{@link offsetGet()} method with the offset passed as a reference parameter, the
 *		method can be used to change the value of the offset or to provide a custom result:
 *		if the method returns <tt>NULL</tt>, {@link offsetGet()} will be called; if the
 *		method returns any other type of value, this will be returned and
 *		{@link offsetGet()} will be skipped.
 *	<li><tt>{@link preOffsetSet()}</tt>: This is called <i>before</i> the
 *		{@link offsetSet()} method with the offset and value passed as reference parameters,
 *		the method can be used to change the offset or the value: if the method returns
 *		<tt>NULL</tt>, {@link offsetSet()} will be called; if the method returns any other
 *		type of value, the {@link offsetSet()} will be skipped.
 *	<li><tt>{@link postOffsetSet()}</tt>: This is called <i>after</i> the
 *		{@link offsetSet()} method with the offset and value passed as reference parameters,
 *		the method can be used to set status or statistical variables, it will only be
 *		called if the {@link offsetSet()} method was called.
 *	<li><tt>{@link preOffsetUnset()}</tt>: This is called <i>before</i> the
 *		{@link offsetUnset()} method with the offset passed as a reference parameter, the
 *		method can be used to change the offset: if the method returns <tt>NULL</tt>,
 *		{@link offsetUnset()} will be called; if the method returns any other type of value,
 *		the {@link offsetUnset()} will be skipped.
 *	<li><tt>{@link postOffsetUnset()}</tt>: This is called <i>after</i> the
 *		{@link offsetUnset()} method with the offset passed as a reference parameter, the
 *		method can be used to set status or statistical variables, it will only be called if
 *		the {@link offsetUnset()} method was called.
 * </ul>
 *
 * The class features a series of methods that are useful for handling the persistent data
 * as a whole:
 *
 * <ul>
 *	<li><em>{@link arrayKeys()}</em>: This method is the equivalent of the
 *		{@link array_keys()} function.
 *	<li><em>{@link arrayValues()}</em>: This method is the equivalent of the
 *		{@link array_values()} function.
 * </ul>
 *
 * The class features a static method, {@link Object2Array}, that can be used to
 * convert a structure of nested {@link ArrayObject} instances into a nested array, this
 * will be useful when normalising objects before persisting.
 *
 * This class uses the {@link Accessors} trait to provide a common framework to methods that
 * manage data properties and offsets.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
class ContainerObject extends \ArrayObject
{
	/**
	 * Offset accessors trait.
	 *
	 * We use this trait to provide a common framework for methods that manage offsets.
	 */
	use	traits\AccessorOffset;

	/**
	 * Property accessors trait.
	 *
	 * We use this trait to provide a common framework for methods that manage properties.
	 */
	use	traits\AccessorProperty;

		

/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	offsetExists																	*
	 *==================================================================================*/

	/**
	 * Check if an offset exists
	 *
	 * We overload this method to call the preflight method: if it returns <tt>NULL</tt> we
	 * call the parent method; if not, we return the received value.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> the offset exists.
	 *
	 * @uses preOffsetExists()
	 */
	public function offsetExists( $theOffset )
	{
		//
		// Call preflight.
		//
		$value = $this->preOffsetExists( $theOffset );
		if( $value !== NULL )
			return $value;															// ==>
		
		return parent::offsetExists( $theOffset );									// ==>
	
	} // offsetExists.

	 
	/*===================================================================================
	 *	offsetGet																		*
	 *==================================================================================*/

	/**
	 * Return a value at a given offset
	 *
	 * We overload this method to call the preflight method: if it returns <tt>NULL</tt> we
	 * call the parent method; if not, we return the received value.
	 *
	 * We also overload this method to handle unmatched offsets: we prevent warnings from
	 * being issued and return <tt>NULL</tt>.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return mixed				Offset value or <tt>NULL</tt>.
	 *
	 * @uses preOffsetGet()
	 */
	public function offsetGet( $theOffset )
	{
		//
		// Call preflight.
		//
		$value = $this->preOffsetGet( $theOffset );
		if( $value !== NULL )
			return $value;															// ==>
		
		//
		// Matched offset.
		//
		if( parent::offsetExists( $theOffset ) )
			return parent::offsetGet( $theOffset );									// ==>
		
		return NULL;																// ==>
	
	} // offsetGet.

	 
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * Set a value at a given offset
	 *
	 * We overload this method to call the preflight and postflight methods: if the
	 * preflight method returns <tt>NULL</tt> we call the parent method; if not, we stop.
	 *
	 * We also overload this method to handle <tt>NULL</tt> <em>values</em>: in that case we
	 * delete the offset.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @access public
	 *
	 * @uses preOffsetSet()
	 * @uses postOffsetSet()
	 * @uses offsetUnset()
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Skip deletions.
		//
		if( $theValue !== NULL )
		{
			//
			// Call preflight.
			//
			if( $this->preOffsetSet( $theOffset, $theValue ) === NULL )
			{
				//
				// Set value.
				//
				parent::offsetSet( $theOffset, $theValue );
				
				//
				// Call postflight.
				//
				$this->postOffsetSet( $theOffset, $theValue );
			
			} // Preflight passed.
		
		} // Not deleting.
		
		//
		// Handle delete.
		//
		else
			$this->offsetUnset( $theOffset );
	
	} // offsetSet.

	 
	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * Reset a value at a given offset
	 *
	 * We overload this method to call the preflight and postflight methods: if the
	 * preflight method returns <tt>NULL</tt> we call the parent method; if not, we stop.
	 *
	 * We also overload this method to prevent warnings on unmatched offsets.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @uses preOffsetUnset()
	 * @uses postOffsetUnset()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Call preflight.
		//
		if( $this->preOffsetUnset( $theOffset ) === NULL )
		{
			//
			// Delete value.
			//
			if( parent::offsetExists( $theOffset ) )
				parent::offsetUnset( $theOffset );
			
			//
			// Call postflight.
			//
			$this->postOffsetUnset( $theOffset );
		
		} // Postflight passed.
	
	} // offsetUnset.

		

/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY UTILITY INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	arrayKeys																		*
	 *==================================================================================*/

	/**
	 * Return object's offsets
	 *
	 * This method has the same function as the PHP function {@link array_keys()}, it will
	 * return all the object's offset keys as an array.
	 *
	 * @access public
	 * @return array				List of object offsets.
	 *
	 * @uses getArrayCopy()
	 */
	public function arrayKeys()				{	return array_keys( $this->getArrayCopy() );	}

	 
	/*===================================================================================
	 *	arrayValues																		*
	 *==================================================================================*/

	/**
	 * Return object's offset values
	 *
	 * This method has the same function as the PHP function {@link array_values()}, it
	 * will return all the object's offset values as an array.
	 *
	 * @access public
	 * @return array				List of object offset values.
	 *
	 * @uses getArrayCopy()
	 */
	public function arrayValues()		{	return array_values( $this->getArrayCopy() );	}

	 

/*=======================================================================================
 *																						*
 *							STATIC SERIALISATION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Object2Array																	*
	 *==================================================================================*/

	/**
	 * <h4>Convert object to array</h4>
	 *
	 * This method can be used to obtain an array of arrays from a nested structure.
	 *
	 * The method expects an {@link ArrayObject} or to an array, it will convert the
	 * provided parameter to an array and traverse it, converting recursively any
	 * {@link ArrayObject} instance into an array.
	 *
	 * The first parameter represents the source structure, the second parameter will
	 * receive the flattened structure.
	 *
	 * @param mixed					$theSource			Source structure.
	 * @param reference				$theDestination		Reference to the destination array.
	 *
	 * @static
	 *
	 * @throws Exception
	 */
	static function Object2Array( $theSource, &$theDestination )
	{
		//
		// Init destination.
		//
		if( ! is_array( $theDestination ) )
			$theDestination = Array();
		
		//
		// Convert source.
		//
		if( $theSource instanceof \ArrayObject )
			$theSource = $theSource->getArrayCopy();
		
		//
		// Check if array.
		//
		elseif( ! is_array( $theSource ) )
			throw new \Exception( "Bug: received source parameter of type: ["
								.gettype( $theSource )
								."]." );										// !@! ==>
		
		//
		// Iterate source array.
		//
		$keys = array_keys( $theSource );
		foreach( $keys as $key )
		{
			//
			// Recurse structures.
			//
			if( is_array( $theSource[ $key ] )
			 || ($theSource[ $key ] instanceof \ArrayObject) )
				static::Object2Array( $theSource[ $key ], $theDestination[ $key ] );
			
			//
			// Handle scalars.
			//
			else
				$theDestination[ $key ] = $theSource[ $key ];
		
		} // Iterating source.
	
	} // Object2Array.

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preOffsetExists																	*
	 *==================================================================================*/

	/**
	 * Handle offset before checking it
	 *
	 * This method can be used to manage the offset before passing it to the inherited
	 * {@link ArrayObject::OffsetExists()} method.
	 *
	 * The method provides the offset as a reference, if the method returns <tt>NULL</tt>
	 * it means that the offset must be passed to the inherited
	 * {@link ArrayObject::OffsetExists()}; if the method returns any other value, this will
	 * be returned and the inherited {@link ArrayObject::OffsetExists()} will be skipped.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> check offset, other, return.
	 */
	protected function preOffsetExists( &$theOffset )					{	return NULL;	}

	 
	/*===================================================================================
	 *	preOffsetGet																	*
	 *==================================================================================*/

	/**
	 * Handle offset before getting it
	 *
	 * This method can be used to manage the offset before passing it to the inherited
	 * {@link ArrayObject::OffsetGet()} method.
	 *
	 * The method provides the offset as a reference, if the method returns <tt>NULL</tt>
	 * it means that the offset must be passed to the inherited
	 * {@link ArrayObject::OffsetGet()}; if the method returns any other value, this must be
	 * returned and the inherited {@link ArrayObject::OffsetGet()} skipped.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> get offset value, other, return.
	 */
	protected function preOffsetGet( &$theOffset )						{	return NULL;	}

	 
	/*===================================================================================
	 *	preOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before setting it
	 *
	 * This method can be used to manage the offset before passing it to the inherited
	 * {@link ArrayObject::OffsetSet()} method.
	 *
	 * The method provides the offset and value as references, if the method returns
	 * <tt>NULL</tt> it means that the offset and value must be passed to the inherited
	 * {@link ArrayObject::OffsetSet()}; if the method returns any other value, this means
	 * that the inherited {@link ArrayObject::OffsetSet()} should be skipped.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 */
	protected function preOffsetSet( &$theOffset, &$theValue )			{	return NULL;	}

	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * This method can be used to manage the object after calling the
	 * {@link ArrayObject::OffsetSet()} method.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )							   {}

	 
	/*===================================================================================
	 *	preOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before deleting it
	 *
	 * This method can be used to manage the offset before passing it to the inherited
	 * {@link ArrayObject::OffsetUnset()} method.
	 *
	 * The method provides the offset as reference, if the method returns <tt>NULL</tt> it
	 * means that the offset and value must be passed to the inherited
	 * {@link ArrayObject::OffsetUnset()}; if the method returns any other value, this means
	 * that the inherited {@link ArrayObject::OffsetUnset()} should be skipped.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> delete offset value, other, return.
	 */
	protected function preOffsetUnset( &$theOffset )					{	return NULL;	}

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * This method can be used to manage the object after calling the
	 * {@link ArrayObject::OffsetUnset()} method.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 */
	protected function postOffsetUnset( &$theOffset )									   {}

	 

} // class ContainerObject.


?>
