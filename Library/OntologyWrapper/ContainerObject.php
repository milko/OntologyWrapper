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
 * This <em>abstract</em> class is the ancestor of classes that define objects that handle
 * structured data and that are persistent.
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
 * Other data members are managed by method, {@link manageProperty()}, which offers a
 * common framework for member accessor methods.
 *
 * The class features a static method, {@link ArrayObject2Array}, that can be used to
 * convert a structure of nested {@link ArrayObject} instances into a nested array, this
 * will be useful when normalising objects before persisting.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
abstract class ContainerObject extends \ArrayObject
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
	 * We overload this method to handle unmatched offsets: we prevent warnings from being
	 * issued and return <tt>NULL</tt>.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return mixed				Offset value or <tt>NULL</tt>.
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
	 * We overload this method to handle <tt>NULL</tt> <em>values</em>: in that case we
	 * delete the offset.
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
	 * We overload this method to prevent warnings on unmatched offsets.
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
			// Recurse structures.
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

	 

} // class ContainerObject.


?>
