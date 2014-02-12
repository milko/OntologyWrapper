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
 * This class implements a series of methods that can be used by member accessor methods to
 * handle properties and offsets in a standard way:
 *
 * <ul>
 *	<li><em>{@link manageOffset()}</em>: This method provides a standard interface to set,
 *		retrieve and delete offset values.
 *	<li><em>{@link manageSetOffset()}</em>: This method provides a standard interface to
 *		set, retrieve and delete elements of an array set, this method provides access to
 *		elements of the offset, rather than to the offset, which must be an array or
 *		{@link ArrayObject}.
 *	<li><em>{@link manageElementMatchOffset()}</em>: This method provides a standard
 *		interface to set, retrieve and delete elements of an array or {@link ArrayObject},
 *		these elements are an array of two items in which the first one is the discriminator
 *		and the second is the value. 
 *	<li><em>{@link manageProperty()}</em>: This method provides a standard interface to set,
 *		retrieve and reset data members.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
class ContainerObject extends \ArrayObject
{
		

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
	 * The method expects as the first parameter a reference to an {@link ArrayObject} or to
	 * an array, it will convert the provided parameter to an array and traverse it,
	 * converting recursively any {@link ArrayObject} instance into an array.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theSource</tt>: Source structure reference (<em>read-only</em>).
	 *	<li><tt>$theDestination</tt>: Destination array reference.
	 * </ul>
	 *
	 * @param reference				$theSource			Reference to the source structure.
	 * @param reference				$theDestination		Reference to the destination array.
	 *
	 * @static
	 *
	 * @throws Exception
	 */
	static function Object2Array( &$theSource, &$theDestination )
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

		

/*=======================================================================================
 *																						*
 *							PROTECTED MEMBER MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	manageOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage a scalar offset</h4>
	 *
	 * This class provides a protected interface for member accessor methods, both for
	 * properties and offsets, this method can be used to manage a scalar offset, its options
	 * involve setting, retrieving and deleting an offset of the provided array or ArrayObject.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theOffset</tt>: The offset to the attribute contained in the previous
	 *		parameter that is to be managed.
	 *	<li><tt>$theValue</tt>: The value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the offset's current value.
	 *		<li><tt>FALSE</tt>: Delete the offset.
	 *		<li><i>other</i>: Any other type represents the offset's new value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value of the offset <i>before</i> it was eventually
	 *			modified.
	 *		<li><tt>FALSE</tt>: Return the value of the offset <i>after</i> it was eventually
	 *			modified.
	 *	 </ul>
	 * </ul>
	 *
	 * @param string				$theOffset			Offset to be managed.
	 * @param mixed					$theValue			New value or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new value.
	 *
	 * @uses offsetSet()
	 * @uses offsetGet()
	 * @uses offsetUnset()
	 */
	protected function manageOffset( $theOffset, $theValue = NULL, $getOld = FALSE )
	{
		//
		// Normalise offset.
		//
		$theOffset = (string) $theOffset;
		
		//
		// Save current offset value.
		//
		$save = $this->offsetGet( $theOffset );
		
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $save;															// ==>
		
		//
		// Delete offset.
		//
		if( $theValue === FALSE )
			$this->offsetUnset( $theOffset );
		
		//
		// Set offset.
		//
		else
			$this->offsetSet( $theOffset, $theValue );
		
		if( $getOld )
			return $save;															// ==>
		
		return ( $theValue === FALSE )
			 ? NULL																	// ==>
			 : $theValue;															// ==>
	
	} // manageOffset.

	 
	/*===================================================================================
	 *	manageSetOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage value set offset</h4>
	 *
	 * This class provides a protected interface for member accessor methods, both for
	 * properties and offsets, this method can be used to manage the elements of a set of
	 * non repeting values, its options involve setting, retrieving and deleting elements of
	 * the set. This method generally applies to data of the {@link kTYPE_SET} data type.
	 *
	 * It is assumed that the value at the provided offset is either an array or an
	 * {@link ArrayObject}, if this is not the case, the method will raise an exception.
	 *
	 * If you want to manage the set itself, you have to use the offset management methods.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theOffset</tt>: The offset to the array attribute that is to be managed.
	 *	<li><tt>$theValue</tt>: The value of the array element.
	 *	<li><tt>$theOperation</tt>: The operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the value if it exists or <tt>NULL</tt>.
	 *		<li><tt>FALSE</tt>: Delete the value if it exists.
	 *		<li><i>other</i>: Any other type represents a new value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * When setting new values, if the current offset is empty, the method will set the new
	 * value in an aray; when deleting values, if the existing value is the last one, the
	 * method will delete the offset itself.
	 *
	 * @param string				$theOffset			Offset to be managed.
	 * @param mixed					$theValue			Array value.
	 * @param mixed					$theOperation		Operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new value.
	 *
	 * @throws Exception
	 *
	 * @uses offsetSet()
	 * @uses offsetGet()
	 * @uses offsetUnset()
	 */
	protected function manageSetOffset( $theOffset, $theValue, $theOperation = NULL,
															   $getOld = FALSE )
	{
		//
		// Normalise offset.
		//
		$theOffset = (string) $theOffset;
		
		//
		// Save current offset value.
		//
		$save = $this->offsetGet( $theOffset );
		
		//
		// Handle empty offset.
		//
		if( $save === NULL )
		{
			//
			// Handle retrieve and delete.
			//
			if( ($theOperation === NULL)
			 || ($theOperation === FALSE) )
				return NULL;														// ==>
			
			//
			// Handle new value.
			//
			$this->offsetSet( $theOffset, array( $theValue ) );
			
			return ( $getOld )
				 ? NULL																// ==>
				 : $theValue;														// ==>
		
		} // Empty offset.
		
		//
		// Check offset type.
		//
		if( is_array( $save )
		 || ($save instanceof \ArrayObject) )
		{
			//
			// Convert to array.
			//
			if( $save instanceof \ArrayObject )
				$array = $save->getArrayCopy();
			else
				$array = & $save;
			
			//
			// Return value.
			//
			if( $theOperation === NULL )
				return ( in_array( $theValue, $array ) )
					 ? $theValue													// ==>
					 : NULL;														// ==>
			
			//
			// Delete value.
			//
			if( $theOperation === FALSE )
			{
				//
				// Handle found.
				//
				if( in_array( $theValue, $array ) )
				{
					//
					// Delete value.
					//
					unset( $array[ array_search( $theValue, $array ) ] );
					
					//
					// Handle empty array.
					//
					if( ! count( $array ) )
						$this->offsetUnset( $theOffset );
					
					//
					// Update set.
					//
					else
					{
						//
						// Restore array.
						//
						$array = array_values( $array );
					
						//
						// Replace array.
						//
						if( is_array( $save ) )
							$this->offsetSet( $theOffset, $array );
						else
							$save->exchangeArray( $array );
					
					} // Non empty set.
				
					if( $getOld )
						return $theValue;											// ==>
					
				} // Found value.
				
				return NULL;														// ==>
				
			} // Delete value.
			
			//
			// Add value.
			//
			if( ! in_array( $theValue, $array ) )
			{
				//
				// Append value.
				//
				$save[] = $theValue;
				
				//
				// Update array.
				//
				if( is_array( $save ) )
					$this->offsetSet( $theOffset, $save );
				
				if( $getOld )
					return NULL;													// ==>
			
			} // New value.
			
			return $theValue;														// ==>
		
		} // Offset is array or ArrayObject.

		throw new Exception
				( "Expecting array or ArrayObject at offset [$theOffset]",
				  kERROR_PARAMETER );											// !@! ==>
	
	} // manageSetOffset.

	 
	/*===================================================================================
	 *	manageArrayOffset																*
	 *==================================================================================*/

	/**
	 * <h4>Manage value set offset</h4>
	 *
	 * This class provides a protected interface for member accessor methods, both for
	 * properties and offsets, this method can be used to manage the elements of a key/value
	 * array, its options involve setting, retrieving and deleting elements of the list by
	 * key. This method generally applies to data of the {@link kTYPE_ARRAY} data type.
	 *
	 * It is assumed that the value at the provided offset is either an array or an
	 * {@link ArrayObject}, if this is not the case, the method will raise an exception.
	 *
	 * If you want to manage the array itself, you have to use the offset management
	 + methods.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theOffset</tt>: The offset to the array attribute that is to be managed.
	 *	<li><tt>$theKey</tt>: The element key.
	 *	<li><tt>$theValue</tt>: The element value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the value at the provided key, or <tt>NULL</tt>.
	 *		<li><tt>FALSE</tt>: Delete the value at the provided key.
	 *		<li><i>other</i>: Any other type represents a new or a replacement value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * When setting new values, if the current offset is empty, the method will set the new
	 * value in an aray; when deleting values, if the existing value is the last one, the
	 * method will delete the offset itself.
	 *
	 * @param string				$theOffset			Offset to be managed.
	 * @param mixed					$theKey				Element key.
	 * @param mixed					$theValue			Element value or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new value.
	 *
	 * @throws Exception
	 *
	 * @uses offsetSet()
	 * @uses offsetGet()
	 * @uses offsetUnset()
	 */
	protected function manageArrayOffset( $theOffset, $theKey, $theValue = NULL,
															   $getOld = FALSE )
	{
		//
		// Normalise offset.
		//
		$theOffset = (string) $theOffset;
		
		//
		// Save current offset value.
		//
		$save = $this->offsetGet( $theOffset );
		
		//
		// Handle empty offset.
		//
		if( $save === NULL )
		{
			//
			// Handle retrieve and delete.
			//
			if( ($theValue === NULL)
			 || ($theValue === FALSE) )
				return NULL;														// ==>
			
			//
			// Handle new value.
			//
			$this->offsetSet( $theOffset, array( $theKey => $theValue ) );
			
			return ( $getOld )
				 ? NULL																// ==>
				 : $theValue;														// ==>
		
		} // Empty offset.
		
		//
		// Check offset type.
		//
		if( is_array( $save )
		 || ($save instanceof \ArrayObject) )
		{
			//
			// Convert to array.
			//
			if( $save instanceof \ArrayObject )
				$array = $save->getArrayCopy();
			else
				$array = & $save;
			
			//
			// Check current element.
			//
			$found = array_key_exists( $theKey, $array );
			
			//
			// Return value.
			//
			if( $theValue === NULL )
				return ( $found  )
					 ? $array[ $theKey ]											// ==>
					 : NULL;														// ==>
			
			//
			// Save old value.
			//
			$old = ( $found )
				 ? $array[ $theKey ]
				 : NULL;
			
			//
			// Delete value.
			//
			if( $theValue === FALSE )
			{
				//
				// Handle found.
				//
				if( $found )
				{
					//
					// Delete value.
					//
					unset( $array[ $theKey ] );
					
					//
					// Handle empty array.
					//
					if( ! count( $array ) )
						$this->offsetUnset( $theOffset );
					
					//
					// Update set.
					//
					else
					{
						//
						// Replace array.
						//
						if( is_array( $save ) )
							$this->offsetSet( $theOffset, $array );
						else
							$save->exchangeArray( $array );
					
					} // Non empty set.
				
					if( $getOld )
						return $old;												// ==>
					
				} // Found value.
				
				return NULL;														// ==>
				
			} // Delete value.
			
			//
			// Set value.
			//
			$array[ $theKey ] = $theValue;
			
			//
			// Replace array.
			//
			if( is_array( $save ) )
				$this->offsetSet( $theOffset, $array );
			else
				$save->exchangeArray( $array );
		
			if( $getOld )
				return $old;														// ==>
			
			return $theValue;														// ==>
		
		} // Offset is array or ArrayObject.

		throw new Exception
				( "Expecting array or ArrayObject at offset [$theOffset]",
				  kERROR_PARAMETER );											// !@! ==>
	
	} // manageArrayOffset.

	 
	/*===================================================================================
	 *	manageElementMatchOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Manage element match offset</h4>
	 *
	 * This class provides a protected interface for member accessor methods, both for
	 * properties and offsets, this method can be used to manage the elements of an array of
	 * array elements in which one item's value represents the discriminant.
	 *
	 * Offsets of this kind are arrays or {@link ArrayObject} instances that represent a list
	 * of elements, each of which is constituted by an array of two elements, where the value
	 * matching the <tt>$theTypeOffset</tt> key represents the element key and the value
	 * matching the <tt>$theDataOffset</tt> key represents the element's value.
	 *
	 * This method allows retrieving the element value and deleting or setting an element.
	 *
	 * It is assumed that the value at the provided offset is either an array or an
	 * {@link ArrayObject}, if this is not the case, the method will raise an exception.
	 *
	 * If you want to manage the set itself, you have to use the offset management methods.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theOffset</tt>: The offset to the attribute containing the match list.
	 *	<li><tt>$theTypeOffset</tt>: The offset to the type within the element.
	 *	<li><tt>$theDataOffset</tt>: The offset to the data within the element.
	 *	<li><tt>$theTypeValue</tt>: The value of the element's type to match, it represents
	 *		the key to the element; we assume the value to be a string. A <tt>NULL</tt>
	 *		value is used to select the element missing the <tt>$theTypeOffset</tt> offset.
	 *	<li><tt>$theDataValue</tt>: The value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the offset's <tt>$theDataOffset</tt> value.
	 *		<li><tt>FALSE</tt>: Delete the element matchhing the <tt>$theTypeOffset</tt>.
	 *		<li><i>other</i>: Any other type represents the element's new value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value of the offset <i>before</i> it was
	 *			eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value of the offset <i>after</i> it was
	 *			eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method expects each element to be a structure containing at least an element
	 * indexed by the <tt>$theDataOffset</tt> offset, if that is not the case, the method
	 * will raise an exception. All elements are supposed to be arrays.
	 *
	 * @param string				$theOffset			Offset to be managed.
	 * @param string				$theTypeOffset		Offset of type item.
	 * @param string				$theDataOffset		Offset of data item.
	 * @param string				$theTypeValue		Type value.
	 * @param mixed					$theDataValue		New value or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new value.
	 *
	 * @throws Exception
	 *
	 * @uses offsetSet()
	 * @uses offsetGet()
	 * @uses offsetUnset()
	 */
	protected function manageElementMatchOffset( $theOffset,
												 $theTypeOffset, $theDataOffset,
												 $theTypeValue, $theDataValue = NULL,
												 $getOld = FALSE )
	{
		//
		// Init local storage.
		//
		$list = $this->offsetGet( $theOffset );
		
		//
		// Handle empty offset.
		//
		if( $list === NULL )
		{
			//
			// Handle retrieve and delete.
			//
			if( ($theDataValue === NULL)
			 || ($theDataValue === FALSE) )
				return NULL;														// ==>
			
			//
			// Handle new element.
			//
			if( $theTypeValue !== NULL )
				$this->offsetSet( $theOffset,
								  array( array( $theTypeOffset => $theTypeValue,
												$theDataOffset => $theDataValue ) ) );
			else
				$this->offsetSet( $theOffset,
								  array( array( $theDataOffset => $theDataValue ) ) );
			
			if( $getOld )
				return NULL;														// ==>
			
			return $theDataValue;													// ==>
		
		} // Empty offset.
		
		//
		// Check offset type.
		//
		if( is_array( $list )
		 || ($list instanceof \ArrayObject) )
		{
			//
			// Init local storage.
			//
			$index = NULL;
			
			//
			// Convert to array.
			//
			if( $list instanceof \ArrayObject )
				$array = $list->getArrayCopy();
			else
				$array = & $list;
	
			//
			// Iterate list.
			//
			foreach( $array as $key => $element )
			{
				//
				// Check element data type.
				//
				if( ! is_array( $element ) )
					throw new \Exception
							( "Expecting a list of arrays at offset [$theOffset]",
							  kERROR_PARAMETER );								// !@! ==>
				
				//
				// Check data element.
				//
				if( ! array_key_exists( $theDataOffset, $element ) )
					throw new \Exception
							( "Expecting an element featuring the [$theDataOffset] offset",
							  kERROR_PARAMETER );								// !@! ==>
				
				//
				// Match element.
				//
				if( ( ($theTypeValue === NULL)
				   && (! array_key_exists( $theTypeOffset, $element )) )
				 || ( ($theTypeValue !== NULL)
				   && array_key_exists( $theTypeOffset, $element )
				   && ($element[ $theTypeOffset ] == $theTypeValue) ) )
				{
					//
					// Save index.
					//
					$index = $key;
					
					break;													// =>
				
				} // Matched.
			
			} // Iterating elements.
		
			//
			// Return value.
			//
			if( $theDataValue === NULL )
				return ( $index !== NULL )
					 ? $element[ $theDataOffset ]									// ==>
					 : NULL;														// ==>
			
			//
			// Delete element.
			//
			if( $theDataValue === FALSE )
			{
				//
				// Handle matched element.
				//
				if( $index !== NULL )
				{
					//
					// Delete element.
					//
					unset( $array[ $key ] );
					
					//
					// Handle empty array.
					//
					if( ! count( $array ) )
						$this->offsetUnset( $theOffset );
					
					//
					// Update array.
					//
					else
					{
						//
						// Restore array.
						//
						$array = array_values( $array );
					
						//
						// Replace array.
						//
						if( is_array( $list ) )
							$this->offsetSet( $theOffset, $array );
						else
							$list->exchangeArray( $array );
					
					} // Non empty set.
				
					if( $getOld )
						return $element[ $theDataOffset ];							// ==>
				
				} // Matched element.
				
				return NULL;														// ==>
			
			} // Delete element.
			
			//
			// Add element.
			//
			if( $index === NULL )
			{
				//
				// Build element.
				//
				$element = ( $theTypeValue !== NULL )
						 ? array( $theTypeOffset => $theTypeValue,
						 		  $theDataOffset => $theDataValue )
						 : array( $theDataOffset => $theDataValue );
				
				//
				// Add element.
				//
				$list[] = $element;
				
				//
				// Handle arrays.
				//
				if( is_array( $list ) )
					$this->offsetSet( $theOffset, $list );
				
				if( $getOld )
					return NULL;													// ==>
			
			} // Element not matched.
			
			//
			// Update element.
			//
			else
			{
				//
				// Save value.
				//
				$save = $element[ $theDataOffset ];
				
				//
				// Update element.
				//
				$element[ $theDataOffset ] = $theDataValue;
				
				//
				// Update list.
				//
				$list[ $key ] = $element;
				
				//
				// Update array.
				//
				if( is_array( $list ) )
					$this->offsetSet( $theOffset, $list );
				
				if( $getOld )
					return $save;													// ==>
			
			} // Element matched.
			
			return $theDataValue;													// ==>
		
		} // Correct offset data type.

		throw new Exception
				( "Expecting array or ArrayObject at offset [$theOffset]",
				  kERROR_PARAMETER );											// !@! ==>
	
	} // manageElementMatchOffset.

		
	/*===================================================================================
	 *	manageProperty																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage a property</h4>
	 *
	 * This library implements a standard interface for managing object properties using
	 * accessor methods, this method implements this interface:
	 *
	 * <ul>
	 *	<li><tt>&$theMember</tt>: Reference to the property being managed.
	 *	<li><tt>$theValue</tt>: The property value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current property value.
	 *		<li><tt>FALSE</tt>: Reset the property to <tt>NULL</tt>, the default value.
	 *		<li><em>other</em>: Any other type represents the new value of the property.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value of the property <em>before</em> it was
	 *			eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value of the property <em>after</em> it was
	 *			eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * @param reference				$theMember			Reference to the data member.
	 * @param mixed					$theValue			Value or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new property value.
	 */
	protected function manageProperty( &$theMember, $theValue = NULL, $getOld = FALSE )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $theMember;														// ==>

		//
		// Save current value.
		//
		$save = $theMember;
		
		//
		// Set new value.
		//
		if( $theValue !== FALSE )
			$theMember = $theValue;
		
		//
		// Reset value.
		//
		else
			$theMember = NULL;
		
		if( $getOld )
			return $save;															// ==>
		
		return $theMember;															// ==>
	
	} // manageProperty.

	 

} // class ContainerObject.


?>
