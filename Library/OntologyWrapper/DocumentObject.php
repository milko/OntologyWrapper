<?php

/**
 * DocumentObject.php
 *
 * This file contains the definition of the {@link DocumentObject} class.
 */

namespace OntologyWrapper;

/*=======================================================================================
 *																						*
 *									DocumentObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Document object
 *
 * This class extends the <b><tt>{@link ArrayObject}</tt></b> class, it adds a series of
 * restrictions to the inherited class and represents the main building block for most
 * classes in this library.
 *
 * Instances of this class store their data in the <i>inherited array data member</i> which
 * represents the <i>persistent</i> part of the object, while property data members are only
 * used in run-time.
 *
 * No array offset element may hold the <tt>NULL</tt> value, <i>setting an offset with
 * <tt>NULL</tt> value is equivalent to deleting the offset</i>.
 *
 * Retrieving non-existant offsets will <i>not</i> generate a warning, the <tt>NULL</tt>
 * value will be returned instead.
 *
 * The class features two methods, <b><tt>{@link arrayKeys()}</tt></b> and
 * <b><tt>{@link arrayValues()}</tt></b> which represent the equivalent functions to the
 * array <b><tt>{@link array_keys()}</tt></b> and <b><tt>{@link array_values()}</tt></b>
 * functions.
 *
 * The class features a method that should be used to manage object data members, the
 * {@link manageProperty()} method should be used to set, retrieve and reset object
 * properties.
 *
 * The class features a static method, {@link ArrayObject2Array}, that can be used to
 * convert a structure of nested {@link ArrayObject} instances into a nested array, this
 * will be useful when normalising objects for persisting.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
class DocumentObject extends \ArrayObject
{
		

/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	offsetGet																		*
	 *==================================================================================*/

	/**
	 * Return a value at a given offset
	 *
	 * This method should return the value corresponding to the provided offset.
	 *
	 * We overload this method to prevent warnings from being generated when requesting
	 * values for non-existant offsets and returning <tt>NULL</tt> when the offset does not
	 * exist.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return mixed				Offset value or <tt>NULL</tt> for non matching offsets.
	 *
	 * @uses offsetExists()
	 */
	public function offsetGet( $theOffset )
	{
		//
		// Matched offset.
		//
		if( $this->offsetExists( $theOffset ) )
			return parent::offsetGet( $theOffset );									// ==>
		
		return NULL;																// ==>
	
	} // offsetGet.

	 
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * Set a value at a given offset
	 *
	 * This method should set the provided value corresponding to the provided offset.
	 *
	 * We overload this method to handle <tt>NULL</tt> values: in that case we delete the
	 * offset.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @access public
	 *
	 * @uses offsetUnset()
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Handle new value.
		//
		if( $theValue !== NULL )
			parent::offsetSet( $theOffset, $theValue );
		
		//
		// Delete offset.
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
	 * This method should reset the value corresponding to the provided offset.
	 *
	 * We overload this method to skip non-existant offsets.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @uses offsetExists()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Handle existing offset.
		//
		if( $this->offsetExists( $theOffset ) )
			parent::offsetUnset( $theOffset );
	
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
	 * This method has the same function as the PHP function <tt>array_keys()</i>, it will
	 * return an array comprised of all object's offsets.
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
	 * This method has the same function as the PHP function <tt>array_values()</i>, it
	 * will return an array comprised of all object's offset values.
	 *
	 * @access public
	 * @return array				List of object offset values.
	 *
	 * @uses getArrayCopy()
	 */
	public function arrayValues()		{	return array_values( $this->getArrayCopy() );	}

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
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
	 *		<li><i>other</i>: Any other type represents the new value of the property.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value of the property <i>before</i> it was
	 *			eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value of the property <i>after</i> it was
	 *			eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * @param reference				$theMember			Reference to the data member.
	 * @param mixed					$theValue			Value or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				Old or new property value.
	 */
	public function manageProperty( &$theMember, $theValue = NULL, $getOld = FALSE )
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

		

/*=======================================================================================
 *																						*
 *							STATIC SERIALISATION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ArrayObject2Array																*
	 *==================================================================================*/

	/**
	 * <h4>Convert structure to array</h4>
	 *
	 * This method can be used to convert all {@link ArrayObject} derived instances into
	 * arrays, the method expects a source parameter which can be either an
	 * {@link ArrayObject} instance or an array, the method will traverse the source
	 * parameter and convert any {@link ArrayObject} instance into an array in the
	 * destination parameter.
	 *
	 * This method will convert all embedded {@link ArrayObject} instances at any level of
	 * depth into arrays, this method is used by {@link getArrayCopy()} to traverse the
	 * object's structure.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theSource</tt>: Source structure reference.
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
	static function ArrayObject2Array( &$theSource, &$theDestination )
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
			// Handle structures.
			//
			if( is_array( $theSource[ $key ] )
			 || ($theSource[ $key ] instanceof \ArrayObject) )
				static::ArrayObject2Array( $theSource[ $key ], $theDestination[ $key ] );
			
			//
			// Handle scalars.
			//
			else
				$theDestination[ $key ] = $theSource[ $key ];
		
		} // Iterating source.
	
	} // ArrayObject2Array.

	 

} // class DocumentObject.


?>
