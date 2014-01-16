<?php

/**
 * OntologyObject.php
 *
 * This file contains the definition of the {@link OntologyObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\DocumentObject;

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
 * Ontology object
 *
 * The main purpose of this class is to match all offsets to an ontology domain, ensuring
 * that any persistent value held by instances of this class is annotated by an entry of the
 * ontology.
 *
 * All offsets of the embedded array inherited by the {@link ArrayObject} class must refer
 * to a {@link Tag} object, which represents the ontology entry associated with the offset
 * value, this ensures that all data values are documented in the ontology.
 *
 * Offsets are represented by a <i>tag</i> which is an <tt>integer</tt> representing the
 * <i>native identifier</i> of a {@link Tag} object, this identifier is not persistent, so
 * the {@link Tag} has also a <tt>string</tt> <i>global identifier</i> which is immutable.
 *
 * This class provides the ability to automatically {@link resolveOffset() resolve} these
 * tags, which means that offsets can be referred to either by the native or global
 * {@link Tag} identifier: <tt>integer</tt> offsets are assumed to be the native identifier,
 * <tt>string</tt> offsets will be {@link resolveOffset() resolved} into native identifiers
 * prior to accessing the offset value with the only exception of the native identifier
 * {@link tag kTAG_NID}, which is the only string offset allowed.
 *
 * Any attempt to reference an offset that cannot be {@link resolveOffset() resolved} will
 * raise an exception.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
class OntologyObject extends DocumentObject
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
	 * We overload this method to resolve eventual string offsets into tags.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> the offset exists.
	 *
	 * @uses resolveOffset()
	 */
	public function offsetExists( $theOffset )
	{
		//
		// Resolve offset.
		//
		$theOffset = $this->resolveOffset( $theOffset );
		
		return parent::offsetExists( $theOffset );									// ==>
	
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
		// Resolve offset.
		//
		$theOffset = $this->resolveOffset( $theOffset );
		
		return parent::offsetGet( $theOffset );										// ==>
	
	} // offsetGet.

	 
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * Set a value at a given offset
	 *
	 * This method should set the provided value corresponding to the provided offset.
	 *
	 * We overload this method to resolve eventual string offsets into tags.
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
		// Resolve offset.
		//
		$theOffset = $this->resolveOffset( $theOffset );
		
		parent::offsetSet( $theOffset, $theValue );
	
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
	 * @uses _resolveOffset()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Resolve offset.
		//
		$theOffset = $this->resolveOffset( $theOffset );
		
		parent::offsetUnset( $theOffset );
	
	} // offsetUnset.

		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET RESOLUTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Resolve offset</h4>
	 *
	 * This method will resolve the provided offset into a tag.
	 *
	 * The method will consider any <tt>string</tt> provided offset as a {@link Tag}
	 * <i>global identifier</i> and will resolve it into the {@link Tag} <i>native
	 * identifier</i>, with the exception of the <tt>{@link kTAG_NID}</tt> tag which
	 * represents the object key; if the offset cannot be resolved, the method will raise an
	 * exception.
	 *
	 * <i>Note: if the provided integer does not match a {@link Tag} native identifier, the
	 * method will not raise an exception: this means that it is advisable to always use
	 * global identifiers or integer constants.</i>
	 *
	 * @param mixed					$theOffset			Native or global identifier.
	 *
	 * @access public
	 * @return integer				Native identifier.
	 *
	 * @uses _resolveTag()
	 */
	public function resolveOffset( $theOffset )
	{
		//
		// Assume native identifier.
		//
		if( is_int( $theOffset ) )
			return $theOffset;														// ==>
		
		//
		// Skip native identifier tag.
		//
		if( $theOffset != kTAG_NID )
			return $this->_resolveTag( (string) $theOffset );						// ==>
		
		return kTAG_NID;															// ==>
	
	} // resolveOffset.

		

/*=======================================================================================
 *																						*
 *							PROTECTED TAG RESOLUTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	_resolveTag																		*
	 *==================================================================================*/

	/**
	 * <h4>Resolve tag</h4>
	 *
	 * This method will resolve the provided {@link Tag} global identifier into the Tag's
	 * native identifier.
	 *
	 * If the provided identifier cannot be resolved, the method will raise an exception.
	 *
	 * @param string				$theIdentifier		Tag global identifier.
	 *
	 * @access protected
	 * @return integer				Tag native identifier.
	 *
	 * @throws Exception
	 */
	protected function _resolveTag( $theIdentifier )
	{
		return (int) $theOffset;													// ==>
	
	} // _resolveTag.

	 

} // class OntologyObject.


?>
