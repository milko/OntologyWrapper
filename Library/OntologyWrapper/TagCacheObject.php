<?php

/**
 * TagCacheObject.php
 *
 * This file contains the definition of the {@link TagCacheObject} class.
 */

namespace OntologyWrapper;

/*=======================================================================================
 *																						*
 *									TagCacheObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Tag cache object
 *
 * This <i>abstract</i> class is the prototype of classes that implement a tag cache.
 *
 * The main purpose of a tag cache is to hold in a fast retrieval cache all the current
 * ontology {@link Tag} objects and information, this cache is actively used by all classes
 * derived from {@link OntologyObject} for resolving offsets into {@link Tag} references and
 * to cast offset values according to the {@link Tag} declared data type.
 *
 * Concrete derived classes should implement a constructor that will instantiate the cache
 * and load all current tag identifiers and objects.
 *
 * The class features the following specific virtual methods:
 *
 * <ul>
 *	<li><tt>{@link init()}</tt>: This method should <i>initialise</i> the cache.
 *	<li><tt>{@link setTagId()}</tt>: This method should <i>set a key/value pair</i> in which
 *		the key represents the tag <i>global identifier</i> and the value the tag <i>native
 *		identifier</i>.
 *	<li><tt>{@link getTagId()}</tt>: This method should <i>return a tag native identifier
 *		given a tag global identifier</i>.
 *	<li><tt>{@link delTagId()}</tt>: This method should <i>delete a key/value pair</i> in
 *		which the key represents the tag <i>global identifier</i>.
 *	<li><tt>{@link setTagObject()}</tt>: This method should <i>set a key/value pair</i> in
 *		which the key represents the tag <i>native identifier</i> and the value the tag
 *		<i>object or array of contents</i>.
 *	<li><tt>{@link getTagObject()}</tt>: This method should <i>return a tag object or array
 *		of contents given a tag native identifier</i>.
 *	<li><tt>{@link delTagObject()}</tt>: This method should <i>delete a key/value pair</i>
 *		in which the key represents the tag <i>native identifier</i>.
 *	<li><tt>{@link getTagGID()}</tt>: This method should <i>return a tag global identifier
 *		given a tag native identifier</i>.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 29/01/2014
 */
abstract class TagCacheObject
{
		

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	init																			*
	 *==================================================================================*/

	/**
	 * Initialise cache
	 *
	 * This method should reset the cache, if initialised, and load all the current tag
	 * identifiers and objects.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	abstract public function init();

	 
	/*===================================================================================
	 *	setTagId																		*
	 *==================================================================================*/

	/**
	 * Set a tag identifier
	 *
	 * This method should set a key/value pair in the cache, in which the key represents a
	 * tag global identifier and the value the tag native identifier.
	 *
	 * The global identifier will be cast to <tt>string</tt> and the native identifier will
	 * be cast to <i>integer</i>.
	 *
	 * The method should raise an exception if the operation fails.
	 *
	 * @param string				$theKey				Global identifier.
	 * @param integer				$theValue			Native identifier.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	abstract public function setTagId( $theKey, $theValue );

	 
	/*===================================================================================
	 *	getTagId																		*
	 *==================================================================================*/

	/**
	 * Get a tag identifier
	 *
	 * This method should return a tag native identifier given a tag global identifier.
	 *
	 * If the provided global identifier is not matched and the second parameter is
	 * <tt>TRUE</tt>, the method should raise an exception; if the parameter is
	 * <tt>FALSE</tt>, the method should return <tt>NULL</tt>.
	 *
	 * @param string				$theKey				Global identifier.
	 * @param boolean				$doAssert			Assert match.
	 *
	 * @access public
	 * @return integer				The tag native identifier or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	abstract public function getTagId( $theKey, $doAssert = FALSE );

	 
	/*===================================================================================
	 *	delTagId																		*
	 *==================================================================================*/

	/**
	 * Delete a tag identifier
	 *
	 * This method should delete the tag native identifier matching the provided tag global
	 * identifier.
	 *
	 * If the provided global identifier is not matched and the second parameter is
	 * <tt>TRUE</tt>, the method should raise an exception; if the parameter is
	 * <tt>FALSE</tt> no exception should be raised.
	 *
	 * @param string				$theKey				Global identifier.
	 * @param boolean				$doAssert			Assert match.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	abstract public function delTagId( $theKey, $doAssert = FALSE );

	 
	/*===================================================================================
	 *	setTagObject																	*
	 *==================================================================================*/

	/**
	 * Set a tag object
	 *
	 * This method should set a key/value pair in the cache, in which the key represents a
	 * tag native identifier and the value the tag object or array of contents.
	 *
	 * The native identifier will be cast to <i>integer</i>.
	 *
	 * The method should raise an exception if the operation fails.
	 *
	 * @param integer				$theKey				Native identifier.
	 * @param mixed					$theValue			Tag object.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	abstract public function setTagObject( $theKey, $theValue );

	 
	/*===================================================================================
	 *	getTagObject																	*
	 *==================================================================================*/

	/**
	 * Get a tag object
	 *
	 * This method should return a tag object or contents array given a tag native
	 * identifier.
	 *
	 * If the provided native identifier is not matched and the second parameter is
	 * <tt>TRUE</tt>, the method should raise an exception; if the parameter is
	 * <tt>FALSE</tt>, the method should return <tt>NULL</tt>.
	 *
	 * @param integer				$theKey				Native identifier.
	 * @param boolean				$doAssert			Assert match.
	 *
	 * @access public
	 * @return mixed				The tag object, contents array or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	abstract public function getTagObject( $theKey, $doAssert = FALSE );

	 
	/*===================================================================================
	 *	delTagObject																	*
	 *==================================================================================*/

	/**
	 * Delete a tag object
	 *
	 * This method should delete the tag object matching the provided tag native identifier.
	 *
	 * If the provided native identifier is not matched and the second parameter is
	 * <tt>TRUE</tt>, the method should raise an exception; if the parameter is
	 * <tt>FALSE</tt> no exception should be raised.
	 *
	 * @param int					$theKey				Native identifier.
	 * @param boolean				$doAssert			Assert match.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	abstract public function delTagObject( $theKey, $doAssert = FALSE );

	 
	/*===================================================================================
	 *	getTagGID																		*
	 *==================================================================================*/

	/**
	 * Get a tag global identifier
	 *
	 * This method should return a tag global identifier given a tag native identifier.
	 *
	 * If the provided native identifier is not matched and the second parameter is
	 * <tt>TRUE</tt>, the method should raise an exception; if the parameter is
	 * <tt>FALSE</tt>, the method should return <tt>NULL</tt>.
	 *
	 * If you provide {@link kTAG_NID}, the method will return it.
	 *
	 * <i>Note that the provided key will be cast to <tt>integer</tt>, which means that
	 * if you did not provide a numeric value, the results will be unexpected</i>.
	 *
	 * @param integer				$theKey				Native identifier.
	 * @param boolean				$doAssert			Assert match.
	 *
	 * @access public
	 * @return mixed				The tag global identifier.
	 *
	 * @uses getTagObject()
	 */
	public function getTagGID( $theKey, $doAssert = FALSE )
	{
		//
		// Skip global identifier.
		//
		if( $theKey == kTAG_NID )
			return kTAG_NID;														// ==>
		
		//
		// Get tag.
		//
		$tag = $this->getTagObject( (int) $theKey, $doAssert );
		
		return $tag[ (string) kTAG_GID ];											// ==>
	
	} // getTagGID.

	 

} // class TagCacheObject.


?>
