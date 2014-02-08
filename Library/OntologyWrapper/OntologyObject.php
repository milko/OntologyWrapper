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
 * Tokens.
 *
 * This file contains the default token definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Tokens.inc.php" );

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
 * described by an entry of the ontology represented by an instance of the {@link TagObject}
 * class, which is itself derived from this class.
 *
 * All persistent data elements are represented by a key/value pair, in which the key is
 * the data offset and the value the persistent data value. All persistent data offsets
 * must be integer values or alphanumeric strings, with the exception of the tags defined in
 * the static {@link $sInternalTags} list. These integer values represent references to
 * Tag object instances, which hold all the necessary information to document the current
 * persistent data element.
 *
 * This class implements the bridge between data and the ontology ensuring that all data
 * elements are defined in this ontology, which, itself, is implemented by objects derived
 * from this same class: this means that the whole system is self documenting.
 *
 * Whenever the object uses an offset, this will be first fed to a public method,
 * {@link offsetResolve()}, which will take care of translating <i>global identifiers</i>
 * into <i>native identifiers</i>. The Tag object native identifier is represented by an
 * integer value, this value is used as the data offset. These native identifiers are not
 * persistent, in other words, these value may change from one implementation to the other:
 * for this reason Tag objects also hold a <i>global identifier</i> which is a non-numeric
 * string: this string will remain the same across implementations and can be considered the
 * <i>immutable tag identifier/i>.
 *
 * In this class by default an integer offset is assumed to be a correct Tag object
 * reference, while a string offset will be resolved into a tag reference. This means that
 * if you want to ensure that all offsets are correct you should always use tag global
 * identifiers as offsets, while you should only use known tag native identifiers as integer
 * offsets.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
class OntologyObject extends ContainerObject
{
	/**
	 * Internal tags.
	 *
	 * This static data member holds the list of internal tags, this is the list of string
	 * offsets that do not need to be resolved.
	 *
	 * @var array
	 */
	static $sInternalTags = array( kTAG_NID, kTAG_CLASS );

		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET RESOLUTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	offsetResolve																	*
	 *==================================================================================*/

	/**
	 * Resolve offset
	 *
	 * This method will resolve the provided offset into a {@link TagObject} native
	 * identifier, this is done by using a {@link TagCache} object stored in the
	 * {@link kSESSION_DDICT} entry of the current session.
	 *
	 * If you provide an integer or a numeric string, the method will simply cast the value
	 * to an integer and return it.
	 *
	 * All other types of offsets, except those listed in the ststic {@link $sInternalTags}
	 * data member, will be used to locate the tag native identifier using a
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
	 * @see $sInternalTags
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
		// Handle internal offsets.
		//
		if( in_array( $theOffset, static::$sInternalTags ) )
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
	 * In this class we resolve the offset.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> check offset, other, return.
	 *
	 * @uses offsetResolve()
	 */
	protected function preOffsetExists( &$theOffset )
	{
		//
		// Call parent method.
		//
		$ok = parent::preOffsetExists( $theOffset );
		if( $ok === NULL )
			$theOffset = (string) $this->offsetResolve( $theOffset );
		
		return $ok;																	// ==>
	
	} // preOffsetExists.

	 
	/*===================================================================================
	 *	preOffsetGet																	*
	 *==================================================================================*/

	/**
	 * Handle offset before getting it
	 *
	 * In this class we resolve the offset.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> get offset value, other, return.
	 *
	 * @uses offsetResolve()
	 */
	protected function preOffsetGet( &$theOffset )
	{
		//
		// Call parent method.
		//
		$ok = parent::preOffsetGet( $theOffset );
		if( $ok === NULL )
			$theOffset = (string) $this->offsetResolve( $theOffset );
		
		return $ok;																	// ==>
	
	} // preOffsetGet.

	 
	/*===================================================================================
	 *	preOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before setting it
	 *
	 * In this class we resolve the offset.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @uses offsetResolve()
	 */
	protected function preOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method.
		//
		$ok = parent::preOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
			$theOffset = (string) $this->offsetResolve( $theOffset, TRUE );
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 
	/*===================================================================================
	 *	preOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before deleting it
	 *
	 * In this class we resolve the offset.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> delete offset value, other, return.
	 *
	 * @uses offsetResolve()
	 */
	protected function preOffsetUnset( &$theOffset )
	{
		//
		// Call parent method.
		//
		$ok = parent::preOffsetUnset( $theOffset );
		if( $ok === NULL )
			$theOffset = (string) $this->offsetResolve( $theOffset );
		
		return $ok;																	// ==>
	
	} // preOffsetUnset.

	 

} // class OntologyObject.


?>
