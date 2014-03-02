<?php

/**
 * UnitObject.php
 *
 * This file contains the definition of the {@link UnitObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\PersistentObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									UnitObject.php										*
 *																						*
 *======================================================================================*/

/**
 * Domains.
 *
 * This file contains the default domain definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

/**
 * UnitObject
 *
 * Unit and entity objects share the same base identifier attributes set, this class
 * implements the common features of both derived classes.
 *
 * All concrete instances of this class share the following attributes:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: The domain is an enumeration that defines the type of
 *		the unit, it provides information on <em>what</em> the unit is.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The unit authority provides a formal identification
 *		to the object, it indicates <em>who</em> is responsible for the object information
 *		and identification.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: The unit collection provides a means for
 *		<em>disambiguation</em> of the object's <em>local identifier</em>, it acta as the
 *		namespace for an identifier, making the combination of local identifier and
 *		collection unique among all units of the same domain and authority.
 *	<li><tt>{@link kTAG_ID_LOCAL}</tt>: The unit local identifier is a code that should
 *		uniquely identify the object within the realm of its authority and collection.
 *	<li><tt>{@link kTAG_VERSION}</tt>: The unit version provides a means to have differnt
 *		versions of the same formal object.
 * </ul>
 *
 * All the above attributes concur in building the object's persistent identifier, which is
 * the concatenation of the domain, authority, collection, local identifier and version.
 *
 * A unit can be considered initialised when it has at least the domain, the authority and
 * the local identifier.
 *
 * This class is declared abstract, you must derive the class to instantiate it.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 01/03/2014
 */
abstract class UnitObject extends PersistentObject
{
	/**
	 * Sequences selector.
	 *
	 * This constant holds the <i>sequences</i> name for tags; this constant is also used as
	 * the <i>default collection name</i> in which objects of this class are stored.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_units';

	/**
	 * Default domain.
	 *
	 * This constant holds the <i>sequences</i> name for tags; this constant is also used as
	 * the <i>default collection name</i> in which objects of this class are stored.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_UNIT;

		

/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * Instantiate class.
	 *
	 * In this class we link the inited status with the presence of the unit domain,
	 * authority and local identifier.
	 *
	 * The constructor will automatically set the object domain to the default class domain.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @uses instantiateObject()
	 * @uses TermCount()
	 * @uses isInited()
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Load object with contents.
		//
		parent::__construct( $theContainer, $theIdentifier );
		
		//
		// Set default domain.
		//
		if( ! $this->offsetExists( kTAG_DOMAIN ) )
			$this->offsetSet( kTAG_DOMAIN, static::kDEFAULT_DOMAIN );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_DOMAIN ) &&
						 \ArrayObject::offsetExists( kTAG_AUTHORITY ) &&
						 \ArrayObject::offsetExists( kTAG_ID_LOCAL ) );

	} // Constructor.

	 
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return global identifier</h4>
	 *
	 * The global identifier of units is the combination of the object's domain, authority,
	 * collection, local identifier and version, the identifier is computed as follows:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_DOMAIN}</tt>: The domain is followed by the
	 *		{@link kTOKEN_DOMAIN_SEPARATOR}.
	 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The authority is followed by the
	 *		{@link kTOKEN_INDEX_SEPARATOR}.
	 *	<li><tt>{@link kTAG_COLLECTION}</tt>: The namespace is followed by the
	 *		{@link kTOKEN_NAMESPACE_SEPARATOR}.
	 *	<li><tt>{@link kTAG_ID_LOCAL}</tt>: The identifier is followed by the
	 *		{@link kTOKEN_INDEX_SEPARATOR}.
	 *	<li><tt>{@link kTAG_VERSION}</tt>: The version closes the identifier.
	 *	<li><tt>{@link kTOKEN_END_TAG}</tt>: This tag closes the whole identifier.
	 * </ul>
	 *
	 * Only the domain, authority and local identifier are required, all missing attributes
	 * will get omitted, along with the token that follows them.
	 *
	 * @access public
	 * @return string				The global identifier.
	 */
	public function __toString()
	{
		//
		// Handle domain.
		//
		$gid = ( $this->offsetExists( kTAG_DOMAIN ) )
			 ? $this->offsetGet( kTAG_DOMAIN )
			 : static::kDEFAULT_DOMAIN;
		$gid .= kTOKEN_DOMAIN_SEPARATOR;
		
		//
		// Handle authority.
		//
		if( $this->offsetExists( kTAG_AUTHORITY ) )
			$gid .= ($this->offsetGet( kTAG_AUTHORITY ).kTOKEN_INDEX_SEPARATOR);
		
		//
		// Handle collection.
		//
		if( $this->offsetExists( kTAG_COLLECTION ) )
			$gid .= ($this->offsetGet( kTAG_COLLECTION ).kTOKEN_NAMESPACE_SEPARATOR);
		
		//
		// Handle local identifier.
		//
		if( $this->offsetExists( kTAG_ID_LOCAL ) )
			$gid .= $this->offsetGet( kTAG_ID_LOCAL );
		
		//
		// Handle version.
		//
		if( $this->offsetExists( kTAG_VERSION ) )
			$gid .= (kTOKEN_INDEX_SEPARATOR.$this->offsetGet( kTAG_VERSION ));
		
		return $gid.kTOKEN_END_TAG;													// ==>
	
	} // __toString.

		

/*=======================================================================================
 *																						*
 *								STATIC CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveDatabase																	*
	 *==================================================================================*/

	/**
	 * Resolve the database
	 *
	 * In this class we return the units database.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param boolean				$doAssert			Raise exception if unable.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return DatabaseObject		Database or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	static function ResolveDatabase( Wrapper $theWrapper, $doAssert = TRUE, $doOpen = TRUE )
	{
		//
		// Get units database.
		//
		$database = $theWrapper->Units();
		if( $database instanceof DatabaseObject )
		{
			//
			// Open connection.
			//
			if( $doOpen )
				$database->openConnection();
			
			return $database;														// ==>
		
		} // Retrieved units database.
		
		//
		// Raise exception.
		//
		if( $doAssert )
			throw new \Exception(
				"Unable to resolve database: "
			   ."missing units reference in wrapper." );						// !@! ==>
		
		return NULL;																// ==>
	
	} // ResolveDatabase.

		

/*=======================================================================================
 *																						*
 *								STATIC OFFSET INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return the default offsets comprising the object's persistent
	 * identifier..
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_DOMAIN, kTAG_AUTHORITY,
								   kTAG_COLLECTION, kTAG_ID_LOCAL,
								   kTAG_VERSION ) );								// ==>
	
	} // DefaultOffsets.

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * In this class we link the inited status with the presence of the terms list, the data
	 * type and the label.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_DATA_TYPE kTAG_LABEL
	 *
	 * @uses TermCount()
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method to resolve offset.
		//
		parent::postOffsetSet( $theOffset, $theValue );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_DOMAIN ) &&
						 \ArrayObject::offsetExists( kTAG_AUTHORITY ) &&
						 \ArrayObject::offsetExists( kTAG_ID_LOCAL ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of the terms list, the data
	 * type and the label.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_DATA_TYPE kTAG_LABEL
	 *
	 * @uses TermCount()
	 */
	protected function postOffsetUnset( &$theOffset )
	{
		//
		// Call parent method to resolve offset.
		//
		parent::postOffsetUnset( $theOffset );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_DOMAIN ) &&
						 \ArrayObject::offsetExists( kTAG_AUTHORITY ) &&
						 \ArrayObject::offsetExists( kTAG_ID_LOCAL ) );
	
	} // postOffsetUnset.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitObjectIdentifiers														*
	 *==================================================================================*/

	/**
	 * Load object identifiers
	 *
	 * In this class we set the native identifier, if not yet filled, with the global
	 * identifier generated by the {@link __toString()} method and we set the sequence
	 * number, {@link kTAG_ID_SEQUENCE}, if it is not yet set, by requesting it from the
	 * database of the current object's container.
	 *
	 * @access protected
	 */
	protected function preCommitObjectIdentifiers()
	{
		//
		// Set native identifier.
		//
		if( ! \ArrayObject::offsetExists( kTAG_NID ) )
			\ArrayObject::offsetSet( kTAG_NID, $this->__toString() );
	
	} // preCommitObjectIdentifiers.

		

/*=======================================================================================
 *																						*
 *								PROTECTED STATUS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isReady																			*
	 *==================================================================================*/

	/**
	 * Check if object is ready
	 *
	 * In this class we ensure the object has the sequence number, {@link kTAG_ID_SEQUENCE}
	 * and the native identifier, {@link kTAG_NID}.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 *
	 * @see kTAG_NID kTAG_ID_SEQUENCE
	 *
	 * @uses isInited()
	 */
	protected function isReady()
	{
		return ( parent::isReady()
			  && $this->offsetExists( kTAG_NID ) );									// ==>
	
	} // isReady.

		

/*=======================================================================================
 *																						*
 *							PROTECTED OFFSET STATUS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	lockedOffsets																	*
	 *==================================================================================*/

	/**
	 * Return list of locked offsets
	 *
	 * In this class we return the {@link kTAG_DOMAIN}, {@link kTAG_AUTHORITY},
	 * {@link kTAG_COLLECTION}, {@link kTAG_ID_LOCAL} and the {@link kTAG_VERSION} offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_DOMAIN kTAG_AUTHORITY kTAG_COLLECTION kTAG_ID_LOCAL kTAG_VERSION
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
							array( kTAG_DOMAIN, kTAG_AUTHORITY,
								   kTAG_COLLECTION, kTAG_ID_LOCAL,
								   kTAG_VERSION ) );								// ==>
	
	} // lockedOffsets.

	

/*=======================================================================================
 *																						*
 *								PROTECTED OFFSET UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadObjectTag																	*
	 *==================================================================================*/

	/**
	 * Load object tags
	 *
	 * In this class we add the tag if it is not among the {@link DefaultOffsets()} and it
	 * is not {@link kTYPE_INDEXED}.
	 *
	 * @param integer				$theTag				Tag sequence number.
	 * @param reference				$theInfo			Tag information.
	 * @param reference				$theTags			Receives tags list.
	 *
	 * @access protected
	 */
	protected function loadObjectTag( $theTag, &$theInfo, &$theTags )
	{
		//
		// Check if eligible.
		//
		if( (! in_array( $theTag, static::DefaultOffsets() ))
		 && (! in_array( kTYPE_INDEXED, $theInfo[ 'kind' ] )) )
			parent::loadObjectTag( $theTag, $theInfo, $theTags );
	
	} // loadObjectTag.

	 

} // class UnitObject.


?>
