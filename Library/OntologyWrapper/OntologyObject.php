<?php

/**
 * OntologyObject.php
 *
 * This file contains the definition of the {@link OntologyObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\DocumentObject;
use OntologyWrapper\CacheObject;

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
 * Session.
 *
 * This file contains the default session offset definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );

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
 * {@link tag kTAG_IDENT_NID}, which is the only string offset allowed.
 *
 * The {@link resolveOffset()} method takes advantage of a {@link CacheObject} instance
 * stored in the session {@link kSESSION_DDICT} offset for resolving tags, if this object is
 * not set, an exception will be raised.
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
		return parent::offsetExists( $this->resolveOffset( $theOffset ) );			// ==>
	
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
	 * @uses resolveOffset()
	 */
	public function offsetGet( $theOffset )
	{
		return parent::offsetGet( $this->resolveOffset( $theOffset ) );				// ==>
	
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
	 * @throws Exception
	 *
	 * @uses resolveOffset()
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		parent::offsetSet( $this->resolveOffset( $theOffset ), $theValue );
	
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
	 * @uses resolveOffset()
	 */
	public function offsetUnset( $theOffset )
	{
		parent::offsetUnset( $this->resolveOffset( $theOffset ) );
	
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
	 * identifier</i>, with the exception of the <tt>{@link kTAG_IDENT_NID}</tt> tag which
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
	 * @see kTAG_IDENT_NID
	 *
	 * @uses offsetResolve()
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
		if( $theOffset != kTAG_IDENT_NID )
			return $this->offsetResolve( (string) $theOffset );						// ==>
		
		return $theOffset;															// ==>
	
	} // resolveOffset.

		

/*=======================================================================================
 *																						*
 *							PROTECTED TAG RESOLUTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	offsetResolve																	*
	 *==================================================================================*/

	/**
	 * <h4>Resolve offset</h4>
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
	protected function offsetResolve( $theIdentifier )
	{
		//
		// Check if cache is set.
		//
		if( isset( $_SESSION )
		 && array_key_exists( kSESSION_DDICT, $_SESSION )
		 && ($_SESSION[ kSESSION_DDICT ] instanceof CacheObject) )
		{
			//
			// Resolve offset.
			//
			$tag = $_SESSION[ kSESSION_DDICT ]->get( $theIdentifier );
			if( $tag !== NULL )
				return $tag;														// ==>
		
			throw new \Exception(
				"Unknown tag [$theIdentifier]." );								// !@! ==>
		
		} // Data dictionary is set.
		
		throw new \Exception(
			"Data dictionary not set." );										// !@! ==>
	
	} // offsetResolve.

	 

} // class OntologyObject.


?>
