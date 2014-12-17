<?php

/**
 * User.php
 *
 * This file contains the definition of the {@link User} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Individual;

/*=======================================================================================
 *																						*
 *										User.php										*
 *																						*
 *======================================================================================*/

/**
 * User
 *
 * This <em>concrete</em> class is derived from the {@link Individual} class, it implements
 * an <em>individual</em> or <em>person</em> which is a user of the system.
 *
 * The class adds a series of default attributes which characterise users:
 *
 * <ul>
 *	<li><tt>{@link kTAG_ID_SEQUENCE}</tt>: <em>Sequence ID</em>. This persistent automatic
 *		attribute is set when the record is first inserted, it will be used as the suffix
 *		for elements related to the user.
 *	<li><tt>{@link kTAG_CONN_CODE}</tt>: <em>User code</em>. This optional attribute can be
 *		set if the individual is also a user of the system, in that case this attribute can
 *		hold the user code credentials.
 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: <em>User password</em>. This optional attribute can
 *		be set if the individual is also a user of the system, in that case this attribute
 *		can hold the user password credentials.
 *	<li><tt>{@link kTAG_ROLES}</tt>: <em>Roles</em>. This attribute can be used to set the
 *		user roles.
 *	<li><tt>{@link kTAG_INVITES}</tt>: <em>Invites</em>. This attribute holds the list of
 *-		users invitations.
 * </ul>
 *
 * The above two properties are required.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 28/05/2014
 */
class User extends Individual
{
	/**
	 * Sequences selector.
	 *
	 * This constant holds the <i>default collection</i> name for entities.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_users';

	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_INDIVIDUAL;

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_CODE}</tt>: User code.
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: User password.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_CONN_CODE, kTAG_CONN_PASS, kTAG_ROLES ) );	// ==>
	
	} // DefaultOffsets.

		

/*=======================================================================================
 *																						*
 *								STATIC EXPORT INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	XMLRootElement																	*
	 *==================================================================================*/

	/**
	 * Return XML root element
	 *
	 * In this class we return the <tt>USERS</tt> root element.
	 *
	 * @static
	 * @return SimpleXMLElement		XML export root element.
	 */
	static function XMLRootElement()
	{
		return new \SimpleXMLElement(
						str_replace(
							'@@@', kIO_XML_USERS, kXML_STANDARDS_BASE ) );			// ==>
	
	} // XMLRootElement.

		

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
	 * In this class we link the inited status with the presence of the user code and pass.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_CONN_CODE kTAG_CONN_PASS
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_CONN_CODE ) &&
						 \ArrayObject::offsetExists( kTAG_CONN_PASS ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of the user code and pass.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_CONN_CODE kTAG_CONN_PASS
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_CONN_CODE ) &&
						 \ArrayObject::offsetExists( kTAG_CONN_PASS ) );
	
	} // postOffsetUnset.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitPrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before commit
	 *
	 * In this class we overload this method to set the default domain, authority and
	 * collection, if not yet set.
	 *
	 * Once we do this, we call the parent method.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Check domain.
		//
		if( ! $this->offsetExists( kTAG_DOMAIN ) )
			$this->offsetSet( kTAG_DOMAIN, static::kDEFAULT_DOMAIN );
		
		//
		// Check user code.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
			$this->offsetSet( kTAG_IDENTIFIER, $this->offsetGet( kTAG_CONN_CODE ) );
		
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
	
	} // preCommitPrepare.

		

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
	 * In this class we set the sequence identifier if inserting.
	 *
	 * @access protected
	 */
	protected function preCommitObjectIdentifiers()
	{
		//
		// Check if committed.
		//
		if( ! $this->isCommitted() )
		{
			//
			// Call parent method.
			//
			parent::preCommitObjectIdentifiers();
			
			//
			// Get sequence number.
			//
			$sequence
				= static::ResolveCollection(
					static::ResolveDatabase( $this->mDictionary, TRUE ) )
						->getSequenceNumber(
							static::kSEQ_NAME );
	
			//
			// Set sequence number.
			//
			$this->offsetSet( kTAG_ID_SEQUENCE, $sequence );
			
			//
			// Set user database name.
			//
			if( ! $this->offsetExists( kTAG_CONN_BASE ) )
				$this->offsetSet( kTAG_CONN_BASE, kTOKEN_UDB_PREFIX.$sequence );
		
		} // Not committed.
	
	} // preCommitObjectIdentifiers.

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	xmlUnitElement																	*
	 *==================================================================================*/

	/**
	 * Return XML unit element
	 *
	 * In this class we return the <tt>USER</tt> element.
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	protected function xmlUnitElement( \SimpleXMLElement $theRoot )
	{
		return $theRoot->addChild( kIO_XML_TRANS_USERS );							// ==>
	
	} // xmlUnitElement.

	 

} // class User.


?>
