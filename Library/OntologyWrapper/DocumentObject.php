<?php

namespace OntologyWrapper

/*=======================================================================================
 *																						*
 *									DocumentObject.php									*
 *																						*
 *======================================================================================*/

/**
 * <h4>Document object</h4>
 *
 * This class represents the main building block of this library, it implements a base
 * <i>document</i> object which is an extension and restriction of the base
 * {@link ArrayObject} class, the inherited array represents the object data members, while
 * eventual optional object members are only used for internal private matters.
 *
 * The main purpose of this class is to match all offsets to an ontology domain, ensuring
 * that any value held by the embedded object array resolves into an ontology element.
 *
 * The unique identifier of an object is represented by the <i>native identifier offset</i>,
 * {@link DocumentObject::kTAG_NID}, which is the only alphabetic offset allowed in this
 * class, all other offsets <i>must</i> be numeric, equivalent to an integer. This is
 * because these numeric constants refer to {@link ontology\Tag} instances which define and
 * document the object's data values.
 *
 * It is possible to set an offset by providing a string value: in that case the provided
 * value is interpreted as the tag global identifier which must be resolved into the tag's
 * native identifier.
 *
 * No offset may hold the <tt>NULL</tt> value, setting an offset with this value is
 * equivalent to deleting the offset.
 *
 * Requesting an inexistant offset will not trigger a warning, instead, the <tt>NULL</tt>
 * value will be returned, an indication that the offset doesn't exist.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
class DocumentObject extends ArrayObject
{
	/**
	 * <b>Native identifier constant</b>
	 *
	 * This tag represents the native identifier offset of an object.
	 *
	 * @var string
	 */
	 const kTAG_NID			= '_id';

		

/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	offsetGet																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a value at a given offset</h4>
	 *
	 * This method should return the value corresponding to the provided offset.
	 *
	 * We overload this method to implement the object's offset rules:
	 *
	 * <ul>
	 *	<li><tt>integer</tt>: When provided with an integer value, the method will behave as
	 *		its ancestor.
	 *	<li><tt>DocumentObject::kTAG_NID</tt>: When provided with this value, the method
	 *		will behave as its ancestor.
	 *	<li><tt>string</tt>: When provided with a string, the method will call the protected
	 *		{@link resolveOffset()} method that should resolve the string into a numeric
	 *		offset.
	 * </ul>
	 *
	 * If the provided offset cannot be resolved, or if the resolved offset cannot be found,
	 * the method will return <tt>NULL</tt>.
	 *
	 * This method will not generate warnings for non matching offsets.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return mixed				Offset value or <tt>NULL</tt> for non matching offsets.
	 *
	 * @uses resolveOffset()
	 */
	public function offsetGet( $theOffset )
	{
		//
		// Resolve offset.
		//
		if( (! is_int( $theOffset ))					// Not an integer
		 && (! ctype_digit( (string) $theOffset ))		// and not numeric
		 && (self::kTAG_NID != (string) $theOffset) )	// and not the native identifier.
			$theOffset = $this->resolveOffset( (string) $theOffset );
		
		return @parent::offsetGet( $theOffset );									// ==>
	
	} // offsetGet.

	 
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * <h4>Set a value at a given offset</h4>
	 *
	 * This method should set the provided value corresponding to the provided offset.
	 *
	 * We overload this method to implement the object's offset rules:
	 *
	 * <ul>
	 *	<li><tt>integer</tt>: When provided with an integer value, the method will behave as
	 *		its ancestor.
	 *	<li><tt>NULL</tt>: When provided with this value, the method will unset the offset.
	 *	<li><tt>string</tt>: When provided with a string, the method will call the protected
	 *		{@link resolveOffset()} method that should resolve the string into a numeric
	 *		offset; if the string cannot be resolved, the method will raise an exception.
	 * </ul>
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @access public
	 * @throws Exception
	 *
	 * @uses resolveOffset()
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Resolve offset.
		//
		if( ($theOffset !== NULL)						// Not NULL
		 && (! is_int( $theOffset ))					// and not an integer
		 && (! ctype_digit( (string) $theOffset ))		// and not numeric
		 && (self::kTAG_NID != (string) $theOffset) )	// and not the native identifier.
		{
			//
			// Resolve offset.
			//
			$resolved = $this->resolveOffset( (string) $theOffset );
			
			//
			// Rise exception if failed.
			//
			if( $resolved === NULL )
				throw new Exception
					( "Unable to resolve offset [$theOffset]",
					  kERROR_PARAMETER );										// !@! ==>
			
			//
			// Use resolved offset.
			//
			$theOffset = $resolved;
		
		} // Not a valid offset.

		//
		// Set value.
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
	 * <h4>Reset a value at a given offset</h4>
	 *
	 * This method should reset the value corresponding to the provided offset.
	 *
	 * We overload this method to resolve string offsets not matching the native identifier.
	 *
	 * If the offset cannot be resolved, the method will ignore it.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @uses resolveOffset()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Resolve offset.
		//
		if( (! is_int( $theOffset ))					// Not an integer
		 && (! ctype_digit( (string) $theOffset ))		// and not numeric
		 && (self::kTAG_NID != (string) $theOffset) )	// and not the native identifier.
			$theOffset = $this->resolveOffset( (string) $theOffset );
		
		//
		// Delete offset.
		//
		if( $theOffset !== NULL )
			@parent::offsetUnset( $theOffset );
	
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
	 * <h4>Return object's offsets</h4>
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
	 * <h4>Return object's offset values</h4>
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
