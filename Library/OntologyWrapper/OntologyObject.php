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
 * The main purpose of this class is to match all offsets to an ontology domain, ensuring
 * that any persistent value held by instances of this class is annotated by an entry in the
 * ontology.
 *
 * All offsets of the embedded array inherited by the {@link ArrayObject} class must refer
 * to a {@link Tag} object, which represents the object used by the ontology to describe
 * data properties.
 *
 * {@link Tag} objects feature a <em>native identifier</em> and a <em>global identifier<em>,
 * both are unique, except that the global identifier represents the published and
 * persistent identifier, while the native identifier is used internally, both as the
 * primary key of the {@link Tag} and as the offset key for persistent data properties in
 * all objects derived from this class.
 *
 * The global identifier is a string, while the native identifier is an integer: the reason
 * for using the latter is that it will be generally much shorter than the global identifier
 * and it will never be composed by invalid characters.
 *
 * This class provides the ability to automatically resolve offsets provided as global
 * identifiers into {@link Tag} native identifiers and resolve these native identifiers into
 * objects that can provide the expected data type and all the other documentation related
 * to the value at the provided offset.
 *
 * The class features a protected method, {@link offsetResolve()}, which is called whenever an
 * offset is used: its duty is to resolve the provided offset into a {@link Tag} reference.
 * All string offsets are resolved into tag native identifiers, all integer offsets are
 * assumed correct: this means that you should either always use tag global identifiers as
 * offsets, or use tag definitions.
 *
 * The class features also a {@link offsetCast()} method which can be used to cast an offset
 * value, this method should be implemented in derived classes, here it does nothing.
 *
 * The {@link offsetIdentifier()} method can be used to perform the reverse of
 * {@link offsetResolve()}: given a native identifier it will return a global identifier.
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
	 * This method should return a boolean value indicating whether the provided offset
	 * exists in the current array.
	 *
	 * We overload this method to resolve eventual string offsets into tag references.
	 *
	 * In this class we do not assert offset references.
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
		return parent::offsetExists(
					(string) $this->offsetResolve( $theOffset, FALSE ) );			// ==>
	
	} // offsetExists.

	 
	/*===================================================================================
	 *	offsetGet																		*
	 *==================================================================================*/

	/**
	 * Return a value at a given offset
	 *
	 * This method should return the value corresponding to the provided offset.
	 *
	 * We overload this method to resolve eventual string offsets into tags.
	 *
	 * In this class we do not assert offset references.
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
		return parent::offsetGet(
					(string) $this->offsetResolve( $theOffset, FALSE ) );			// ==>
	
	} // offsetGet.

	 
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * Set a value at a given offset
	 *
	 * This method should set the provided value corresponding to the provided offset.
	 *
	 * We overload this method to resolve eventual string offsets into tags and we call the
	 * {@link offsetCast()} method to cast the value.
	 *
	 * In this class we assert offset references.
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
			// Resolve offset.
			//
			$offset = $this->offsetResolve( $theOffset, TRUE );
		
			//
			// Cast value.
			//
			$this->offsetCast( $theValue, $theOffset, $offset );
			
			//
			// Set value.
			//
			parent::offsetSet( (string) $offset, $theValue );
		
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
	 * This method should reset the value corresponding to the provided offset.
	 *
	 * We overload this method to resolve eventual string offsets into tags.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @uses offsetResolve()
	 */
	public function offsetUnset( $theOffset )
	{
		parent::offsetUnset( (string) $this->offsetResolve( $theOffset, FALSE ) );
	
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
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffset</b>: This parameter contains the original offset, which can be
	 *		either a <tt>string</tt>, in which case it is assumed to be a {@link Tag} global
	 *		identifier, or an <tt>integer</tt>, in which case it is assumed to be a
	 *		{@link Tag} native identifier. In the latter case the method will assume the
	 *		offset to be correct. If you provide the {@link kTAG_NID} constant, the method
	 *		will return it.
	 *	<li><b>$doAssert</b>: This parameter is a flag that determines what should be done
	 *		if an offset doesn't match a tag:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: In this case if the offset cannot be matched, the method will
	 *			raise an exception. 
	 *		<li><tt>FALSE</tt>: In this case if the offset cannot be matched, the method
	 *			will return the original offset.
	 *	 </ul>
	 * </ul>
	 *
	 * In general you can determine if an offset was resolved by checking if the result of
	 * this method is an integer, or if it is {@link kTAG_NID}.
	 *
	 * The method will raise an exception if the tag cache is not set.
	 *
	 * @param mixed					$theOffset			Data offset.
	 * @param boolean				$doAssert			Assert offset reference.
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
		// Handle native identifier and integer.
		//
		if( is_int( $theOffset )
		 || ($theOffset == kTAG_NID) )
			return $theOffset;														// ==>
		
		//
		// Check cache.
		//
		if( (! isset( $_SESSION ))
		 || (! array_key_exists( kSESSION_DDICT, $_SESSION )) )
			throw new \Exception(
				"Tag cache is not set in the session." );						// !@! ==>
		
		//
		// Resolve offset.
		// We let the cache assert the key.
		//
		$offset = $_SESSION[ kSESSION_DDICT ]->getTagId( $theOffset, $doAssert );
		
		//
		// Return resilved.
		//
		if( $offset !== NULL )
			return $offset;															// ==>
		
		return $theOffset;															// ==>
	
	} // offsetResolve.

	 
	/*===================================================================================
	 *	offsetIdentifier																*
	 *==================================================================================*/

	/**
	 * <h4>Decode offset</h4>
	 *
	 * This method will resolve the provided {@link Tag} native identifier into its global
	 * identifier.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffset</b>: This parameter contains the native identifier.
	 *	<li><b>$doAssert</b>: This parameter is a flag that determines what should be done
	 *		if the identifier is not matched:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: In this case if the identifier cannot be matched, the method
	 *			will raise an exception. 
	 *		<li><tt>FALSE</tt>: In this case if the identifier cannot be matched, the method
	 *			will return <tt>NULL</tt>.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will cast the identifier to an integer.
	 *
	 * The method will raise an exception if the tag cache is not set.
	 *
	 * @param int					$theOffset			Native identifier.
	 * @param boolean				$doAssert			Assert offset reference.
	 *
	 * @access public
	 * @return string				Global identifier or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 *
	 * @see kSESSION_DDICT
	 */
	public function offsetIdentifier( $theOffset, $doAssert = FALSE )
	{
		//
		// Check cache.
		//
		if( (! isset( $_SESSION ))
		 || (! array_key_exists( kSESSION_DDICT, $_SESSION )) )
			throw new \Exception(
				"Tag cache is not set in the session." );						// !@! ==>
		
		return $_SESSION[ kSESSION_DDICT ]->getTagGID( $theOffset, $doAssert );		// ==>
	
	} // offsetIdentifier.

	 
	/*===================================================================================
	 *	offsetCast																		*
	 *==================================================================================*/

	/**
	 * <h4>Cast offset value</h4>
	 *
	 * This method will cast the offset value to the desired data type, the method expects
	 * the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: This parameter references the value to be cast.
	 *	<li><b>$theOffset</b>: This parameter contains the original offset.
	 *	<li><b>$theIdentifier</b>: This parameter contains the resolved offset.
	 * </ul>
	 *
	 * In this class we do nothing, in derived classes you can overload this method to
	 * handle the value data type by either parsing the original offset (if provided as a
	 * string, thus as a global identifier), or by loading the tag object and using its data
	 * type field.
	 *
	 * @param reference				$theValue			Offset value.
	 * @param mixed					$theOffset			Original offset.
	 * @param integer				$theIdentifier		Resolved offset.
	 *
	 * @access public
	 */
	public function offsetCast( &$theValue, $theOffset, $theIdentifier )				   {}

	 

} // class OntologyObject.


?>
