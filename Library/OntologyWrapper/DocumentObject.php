<?php

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
 * Instances of this class store their data in the <i>inherited array data mamber</i> which
 * represents the <i>persistent</i> part of the object, while property data members are only
 * used as run-time attributes.
 *
 * No array offset element may hold the <tt>NULL</tt> value, setting an offset with that
 * value is equivalent to deleting the offset.
 *
 * Retrieving non-existant offsets will <i>not</i> generate a warning, the <tt>NULL</tt>
 * value will be returned.
 *
 * The class overloads the <b><tt>{@link ArrayObject::getArrayCopy()}</tt></b> method by
 * converting any <b><tt>{@link ArrayObject}</tt></b> instance into an array.
 *
 * Finally, the class features two methods, <b><tt>{@link arrayKeys()}</tt></b> and
 * <b><tt>{@link arrayValues()}</tt></b> which represent the equivalent functions to the
 * array <b><tt>{@link array_keys()}</tt></b> and <b><tt>{@link array_values()}</tt></b>
 * functions.
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
	 * values for non-existant offsets, in that case we return <tt>NULL</tt>.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return mixed				Offset value or <tt>NULL</tt> for non matching offsets.
	 *
	 * @uses _resolveOffset()
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
	 * @throws \Exception
	 *
	 * @uses _resolveOffset()
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
	 * We overload this method to ignore non-existant offsets.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @uses _resolveOffset()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Handle existing offset.
		//
		if( $this->offsetExists( $theOffset ) )
			@parent::offsetUnset( $theOffset );
	
	} // offsetUnset.

		

/*=======================================================================================
 *																						*
 *							PUBLIC ARRAY SERIALIZATION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getArrayCopy																	*
	 *==================================================================================*/

	/**
	 * Return a copy of the object array
	 *
	 * This method should return a copy of the array part of the object.
	 *
	 * We overload this method to ensure all embedded {@link ArrayObject} instances are
	 * also returned as arrays.
	 *
	 * @access public
	 * @return array				Serialized copy of the object's array.
	 */
	public function getArrayCopy()
	{
		//
		// Init local storage.
		//
		$array = Array();
		
		//
		// Iterate array elements.
		//
		foreach( parent::getArrayCopy() as $key => $value )
		{
			//
			// Serialise array objects.
			//
			if( $value instanceof \ArrayObject )
				$value = $value->getArrayCopy();
			
			//
			// Populate copy.
			//
			$array[ $key ] = $value;
		}
		
		return $array;																// ==>
	
	} // getArrayCopy.

		

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
	 */
	public function arrayValues()		{	return array_values( $this->getArrayCopy() );	}

	 

} // class DocumentObject.


?>
