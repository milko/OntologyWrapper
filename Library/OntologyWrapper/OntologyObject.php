<?php

/**
 * OntologyObject.php
 *
 * This file contains the definition of the {@link OntologyObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ContainerObject;
use OntologyWrapper\connection\TagCache;

/*=======================================================================================
 *																						*
 *									OntologyObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Tags.
 *
 * This file contains the default tag definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

/**
 * Types.
 *
 * This file contains the default data type definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Types.inc.php" );

/**
 * Session.
 *
 * This file contains the default session offset definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );

/**
 * Ontology object
 *
 * Objects of this class hold two types of data: <i>run-time data</i>, which is stored in
 * the object properties and <i>persistent data</i>, which is stored as <i>offsets</i> in
 * the inherited array part of the object.
 *
 * The main purpose of this class is to ensure that all persistent data is annotated and
 * documented by an ontology, guaranteeing that any persistent data element is defined and
 * described by an entry of the ontology represented by an instance of the {@link Tag}
 * class, which is itself derived from this class.
 *
 * All persistent data elements are represented by a key/value pair, in which the key is
 * the data offset and the value the persistent data value. All persistent data offsets
 * must be integer values or alphanumeric strings, with the exception of the
 * {@link kTAG_NID} constant, these integer values represent references to {@link Tag}
 * object instances, which hold all the necessary information and references to document
 * the current persistent data element.
 *
 * This class implements the bridge between data and the ontology ensuring that all data
 * elements are defined in this ontology, which, itself, is implemented by objects derived
 * from this class: this means that the whole system is self documenting.
 *
 * Whenever the object uses an offset, this will be first fed to a public method,
 * {@link offsetResolve()}, which will take care of translating <i>global identifiers</i>
 * into <i>native identifiers</i>. The {@link Tag} object native identifier is represented
 * by an integer value, this value is used as the data offset. These native identifiers are
 * not persistent, in other words, these value may change from one implementation to the
 * other: for this reason {@link Tag} objects also hold a <i>global identifier</i> which is
 * a non-numeric string: this string will relmain the same across implementations and can
 * be considered the <i>immutable tag identifier/i>.
 *
 * In this class by default an integer offset is assumed to be a {@link Tag} object
 * reference, while a string offset will be resolved into a tag reference. This means that
 * if you want to ensure that all offsets are correct you should always use tag global
 * identifiers as offsets, while you should only use tag constants as integers.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
class OntologyObject extends ContainerObject
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
	 * We overload this method to resolve eventual string offsets into tag references; in
	 * this case we do not assert the offset resolution.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> the offset exists.
	 *
	 * @uses offsetResolve()
	 */
	public function offsetExists( $theOffset )
	{
		return parent::offsetExists( (string) $this->offsetResolve( $theOffset ) );	// ==>
	
	} // offsetExists.

	 
	/*===================================================================================
	 *	offsetGet																		*
	 *==================================================================================*/

	/**
	 * Return a value at a given offset
	 *
	 * We overload this method to resolve eventual string offsets into tag references; in
	 * this case we do not assert the offset resolution.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return mixed				Offset value or <tt>NULL</tt> for non matching offsets.
	 *
	 * @uses offsetResolve()
	 */
	public function offsetGet( $theOffset )
	{
		return parent::offsetGet( (string) $this->offsetResolve( $theOffset ) );	// ==>
	
	} // offsetGet.

	 
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * Set a value at a given offset
	 *
	 * We overload this method to cast the provided value prior to seting the offset. This
	 * is done by the {@link offsetCast()} method which will also take care of resolving
	 * the offset; in this case if the method is not able to resolve the tag, it will raise
	 * an exception.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @access public
	 * @throws Exception
	 *
	 * @uses offsetResolve()
	 * @uses offsetCast()
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Skip deletions.
		//
		if( $theValue !== NULL )
		{
			//
			// Cast value.
			//
			$theOffset = $this->offsetCast( $theValue, $theOffset );
		
			//
			// Resolve offset and set value.
			//
			parent::offsetSet( (string) $theOffset, $theValue );
		
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
	 * We overload this method to resolve eventual string offsets into tag references; in
	 * this case we do not assert the offset resolution.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @uses offsetResolve()
	 */
	public function offsetUnset( $theOffset )
	{
		parent::offsetUnset( (string) $this->offsetResolve( $theOffset ) );
	
	} // offsetUnset.

		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET RESOLUTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	offsetResolve																	*
	 *==================================================================================*/

	/**
	 * <h4>Resolve offset</h4>
	 *
	 * This method will resolve the provided offset into a {@link Tag} native identifier,
	 * this is done by using a {@link TagCache} object stored in the {@link kSESSION_DDICT}
	 * entry of the current session.
	 *
	 * If you provide an integer or a numeric string, the method will simply cast the value
	 * to an integer and return it.
	 *
	 * If you provide the native identifier string, {@link kTAG_NID}, the method will return
	 * it.
	 *
	 * All other types of offsets will be used to locate the tag native identifier using a
	 * {@link TagCache} object stored in the {@link kSESSION_DDICT} offset of the current
	 * session; if the provided offset cannot be resolved, the method will raise an
	 * exception if the second parameter is <tt>TRUE</tt>, or <tt>NULL</tt> if the second
	 * parameter is <tt>FALSE</tt>.
	 *
	 * The method will raise an exception if the tag cache is not set.
	 *
	 * @param mixed					$theOffset			Data offset.
	 * @param boolean				$doAssert			Assert offset tag reference.
	 *
	 * @access public
	 * @return mixed				Resolved offset.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_NID
	 * @see kSESSION_DDICT
	 */
	public function offsetResolve( $theOffset, $doAssert = FALSE )
	{
		//
		// Handle numeric offsets.
		//
		if( is_int( $theOffset )
		 || ctype_digit( $theOffset ) )
			return (int) $theOffset;												// ==>
		
		//
		// Handle native identifier.
		//
		if( $theOffset == kTAG_NID )
			return $theOffset;														// ==>
		
		//
		// Check cache.
		//
		if( (! isset( $_SESSION ))
		 || (! array_key_exists( kSESSION_DDICT, $_SESSION )) )
			throw new \Exception(
				"Tag cache is not set in the session." );						// !@! ==>
		
		return $_SESSION[ kSESSION_DDICT ]->getTagId( $theOffset, $doAssert );		// ==>
	
	} // offsetResolve.

	 
	/*===================================================================================
	 *	offsetCast																		*
	 *==================================================================================*/

	/**
	 * <h4>Cast offset value</h4>
	 *
	 * This method will cast the offset value to the data type held by the tag referenced by
	 * the offset. The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: This parameter references the value to be cast.
	 *	<li><b>$theOffset</b>: This parameter contains the original offset.
	 * </ul>
	 *
	 * If the method is unable to resolve the offset it will raise an exception.
	 *
	 * The method casts the value in the provided parameter and returns the resolved offset.
	 *
	 * @param reference				$theValue			Offset value.
	 * @param mixed					$theOffset			Original offset.
	 *
	 * @access public
	 */
	public function offsetCast( &$theValue, $theOffset )
	{
		//
		// Resolve tag.
		//
		$tag = $_SESSION[ kSESSION_DDICT ]
					->getTagObject( $this->offsetResolve( $theOffset, TRUE ), TRUE );
		
		//
		// Cast value.
		//
		$this->castOffsetValue( $theValue, $tag );
		
		return $offset;																// ==>
	
	} // offsetCast.

		

/*=======================================================================================
 *																						*
 *							PROTECTED OFFSET MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	castOffsetValue																	*
	 *==================================================================================*/

	/**
	 * <h4>Cast provided value</h4>
	 *
	 * This method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: This parameter references the value to be cast.
	 *	<li><b>$theTag</b>: This parameter contains the tag object.
	 * </ul>
	 *
	 * The method will cast the value according to the provided tag's data type.
	 *
	 * In this class if the tag has more than one data type, the value will not be changed.
	 *
	 * @param reference				$theValue			Offset value.
	 * @param mixed					$theTag				Tag object.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @see kTAG_NID
	 * @see kSESSION_DDICT
	 */
	public function castOffsetValue( &$theValue, $theTag )
	{
		//
		// Check data types count.
		//
		if( count( $theTag[ kTAG_DATA_TYPE ] ) == 1 )
		{
			//
			// Handle arrays.
			//
			if( array_key_exists( kTAG_CARD_TYPE, $theTag )
			 && in_array( kTYPE_LIST, $theTag[ kTAG_CARD_TYPE ] ) )
			{
				$keys = array_keys( $theValue );
				foreach( $keys as $key )
					$this->castValue( $theValue[ $key ],
									  current( $theTag[ kTAG_DATA_TYPE ] ) );
			}
			
			//
			// Handle scalars.
			//
			else
				$this->castValue( $theValue, current( $theTag[ kTAG_DATA_TYPE ] ) );
		
		} // Only one data type.
	
	} // castOffsetValue.

	 
	/*===================================================================================
	 *	castValue																		*
	 *==================================================================================*/

	/**
	 * <h4>Cast value</h4>
	 *
	 * This method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: This parameter references the value to be cast.
	 *	<li><b>$theType</b>: This parameter represents the data type.
	 * </ul>
	 *
	 * The method will cast the value according to the provided data type.
	 *
	 * @param reference				$theValue			Offset value.
	 * @param string				$theType			Data type.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @see kTAG_NID
	 * @see kSESSION_DDICT
	 */
	public function castValue( &$theValue, $theType )
	{
		//
		// Parse data type.
		//
		switch( $theType )
		{
			case kTYPE_STRING:
			case kTYPE_ENUM:
			case kTYPE_SET:
				$theValue = (string) $theValue;
				break;
			
			case kTYPE_INT:
				$theValue = (int) $theValue;
				break;
			
			case kTYPE_FLOAT:
				$theValue = (double) $theValue;
				break;
		
		} // Parsing data type.
	
	} // castValue.

	 

} // class OntologyObject.


?>
