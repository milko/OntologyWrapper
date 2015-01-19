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
 *	<li><tt>{@link kTAG_CONN_CODE}</tt>: <em>User code</em>. This required attribute
 *		represents the user access code.
 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: <em>User password</em>. This required attribute
 *		represents the user access password, the value contains the user password oncoded
 *		on SHA1.
 *	<li><tt>{@link kTAG_ROLES}</tt>: <em>Roles</em>. This attribute holds the user roles or
 *		permissions:
 *	 <ul>
 *		<li><tt>{@link kTYPE_ROLE_INVITE}</tt>: The user may send user invitations to create
 *			other users; this includes the ability to manage created users along with their
 *			private data.
 *		<li><tt>{@link kTYPE_ROLE_UPLOAD}</tt>: The user may upload and curate data in the
 *			system.
 *		<li><tt>{@link kTYPE_ROLE_EDIT}</tt>: The user may edit static pages content.
 *		<li><tt>{@link kTYPE_ROLE_USERS}</tt>: The user may manage users regardless whether
 *			he/she is their referrer.
 *	 </ul>
 *	<li><tt>{@link kTAG_INVITES}</tt>: <em>Invites</em>. This attribute holds the list of
 *-		users invitations.
 * </ul>
 *
 * The {@link kTAG_IDENTIFIER} property is required by the class ancestor and is
 * automatically set from the {@link kTAG_ENTITY_PGP_FINGERPRINT} or the
 * {@link kTAG_CONN_CODE} properties in that order.
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
 *									PUBLIC ROLES INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	canInvite																		*
	 *==================================================================================*/

	/**
	 * Check whether user can invite
	 *
	 * This method will return <tt>TRUE</tt> if the current user can send invitations.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> can invite.
	 */
	public function canInvite()
	{
		return
			( $this->offsetExists( kTAG_ROLES )
		   && in_array( kTYPE_ROLE_INVITE, $this->offsetGet( kTAG_ROLES ) ) );		// ==>
	
	} // canInvite.

		

/*=======================================================================================
 *																						*
 *								PUBLIC REFERRER INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	managed																			*
	 *==================================================================================*/

	/**
	 * Return list of managed users
	 *
	 * This method will return the list of the users managed by the current user.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theManaged</b>: This optional parameter represents a target managed user
	 *		native identifier. When provided, the method will simply check whether the
	 *		provided user is among the managed descendance, by recursing managed users,
	 *		returning the current object if that is the case. If this parameter is omitted,
	 *		the method will simply return the list of managed user objects.
	 *	<li><b>$theWrapper</b>: This optional parameter represents the database wrapper, it
	 *		can be omitted if the current object has its dictionary set.
	 * </ul>
	 *
	 * The wrapper parameter may be omitted if the current user has its dictionary set.
	 *
	 * The method will either return an array indexed by user native identifier with the
	 * user object as value.
	 *
	 * @param mixed					$theManaged			Managed user native identifier.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 * @return array				List of managed objects.
	 */
	public function managed( $theManaged = NULL, $theWrapper = NULL )
	{
		//
		// Init local storage.
		//
		$list = Array();
		$this->resolveWrapper( $theWrapper );
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) );
		
		//
		// Handle target managed.
		//
		if( $theManaged !== NULL )
		{
			//
			// Normalise managed.
			//
			if( $theManaged instanceof self )
				$theManaged = $theManaged->offsetGet( kTAG_NID );
		
			//
			// Match self reference.
			//
			if( $theManaged == $this->offsetGet( kTAG_NID ) )
				return array( $theManaged => $this );								// ==>
		
		} // Provided target managed.
		
		//
		// Locate managed.
		//
		while( ($managed = $this->getManaged( $theWrapper )) !== NULL )
		{
			//
			// Convert to array.
			//
			$managed = iterator_to_array( $managed );
			
			//
			// Match managed.
			//
			if( ($theManaged !== NULL)
			 && array_key_exists( $theManaged, $managed ) )
				return array( $theManaged => $managed[ $theManaged ] );				// ==>
			
			//
			// Load list.
			//
			$list = array_merge( $list, $managed );
		
		} // Has managed.
		
		return $list;																// ==>
	
	} // managed.

	 
	/*===================================================================================
	 *	referrers																		*
	 *==================================================================================*/

	/**
	 * Return list of user's referrers
	 *
	 * This method will return the list of user objects which are in the current user's
	 * referrer's inheritance, starting with the current user's manager and ending with the
	 * manager's referrer.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theReferrer</b>: This optional parameter represents the referrer's native
	 *		identifier or object, if provided, the returned list will stop with that user or
	 *		the method will return an empty array if not among referrers. If this parameter
	 *		is omitted, the method will return the full list of referrers.
	 *	<li><b>$theWrapper</b>: This optional parameter represents the database wrapper, it
	 *		can be omitted if the current object has its dictionary set.
	 * </ul>
	 *
	 * The wrapper parameter may be omitted if the current user has its dictionary set.
	 *
	 * @param mixed					$theReferrer		Expected referrer.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 * @return array				List of referrer objects.
	 */
	public function referrers( $theReferrer = NULL, $theWrapper = NULL )
	{
		//
		// Init local storage.
		//
		$referrers = Array();
		$this->resolveWrapper( $theWrapper );
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) );
		
		//
		// Normalise referrer.
		//
		if( $theReferrer instanceof self )
			$theReferrer = $theReferrer->offsetGet( kTAG_NID );
		
		//
		// Match self reference.
		//
		if( ($theReferrer !== NULL)
		 && ($theReferrer == $this->offsetGet( kTAG_NID )) )
			return array( $theReferrer => $this );									// ==>
		
		//
		// Locate referrer.
		//
		$referrer = $this->getReferrer();
		while( $referrer !== NULL )
		{
			//
			// Load referrer.
			//
			$tmp = $collection->matchOne( array( kTAG_NID => $referrer ),
										  kQUERY_OBJECT | kQUERY_ASSERT );
			$referrers[ $tmp->offsetGet( kTAG_NID ) ] = $tmp;
			
			//
			// Match target referrer.
			//
			if( $referrer == $theReferrer )
				return $referrers;													// ==>
			
			//
			// Get parent referrer.
			//
			$referrer = $tmp->getReferrer();
		
		} // Has referrers.
		
		//
		// Handle unmatched referrer.
		//
		if( $theReferrer !== NULL )
			return Array();															// ==>
		
		return $referrers;															// ==>
	
	} // referrers.

		

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

	 
	/*===================================================================================
	 *	PrivateOffsets																	*
	 *==================================================================================*/

	/**
	 * Return private offsets
	 *
	 * In this class we return:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_CODE}</tt>: User code.
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: User password.
	 *	<li><tt>{@link kTAG_ENTITY_PGP_KEY}</tt>: Public key.
	 *	<li><tt>{@link kTAG_ENTITY_PGP_FINGERPRINT}</tt>: Fingerprint.
	 *	<li><tt>{@link kTAG_ENTITY_IDENT}</tt>: Identifier.
	 *	<li><tt>{@link kTAG_INVITES}</tt>: Invitations.
	 *	<li><tt>{@link kTAG_MANAGED_COUNT}</tt>: Managed count.
	 *	<li><tt>{@link kTAG_ID_SEQUENCE}</tt>: Sequence number.
	 *	<li><tt>{@link kTAG_CONN_BASE}</tt>: Database name.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function PrivateOffsets()
	{
		return array_merge( parent::PrivateOffsets(),
							array( kTAG_CONN_CODE, kTAG_CONN_PASS,
								   kTAG_ENTITY_PGP_KEY, kTAG_ENTITY_PGP_FINGERPRINT,
								   kTAG_ENTITY_IDENT,
								   kTAG_INVITES, kTAG_MANAGED_COUNT,
								   kTAG_ID_SEQUENCE, kTAG_CONN_BASE ) );			// ==>
	
	} // PrivateOffsets.

		

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
 *								STATIC INSTANTIATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	UserByIdentifier																*
	 *==================================================================================*/

	/**
	 * Instantiate user by identifier
	 *
	 * This method will return the user object matching the provided identifier,
	 * {@link kTAG_IDENTIFIER}, or <tt>NULL</tt> if not found.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param string				$theIdentifier		User identifier.
	 * @param string				$theCollection		Users collection.
	 * @param boolean				$doAssert			Assert user.
	 *
	 * @static
	 * @return User					User object or <tt>NULL<tt>.
	 */
	static function UserByIdentifier( Wrapper $theWrapper,
											  $theIdentifier,
											  $theCollection = NULL,
											  $doAssert = TRUE )
	{
		//
		// Set options.
		//
		$options = kQUERY_OBJECT;
		if( $doAssert )
			$options |= kQUERY_ASSERT;
		
		//
		// Set filter.
		//
		$filter = array( kTAG_IDENTIFIER => $theIdentifier );
		if( $theCollection !== NULL )
			$filter[ kTAG_COLLECTION ]
				= (string) $theCollection;
		
		return
			static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) )
					->matchOne(
						$filter,
						$options );													// ==>
	
	} // UserByIdentifier.

	 
	/*===================================================================================
	 *	UserByPassword																	*
	 *==================================================================================*/

	/**
	 * Instantiate user by code and password
	 *
	 * This method will return the user object matching the provided user name and password
	 * provided as an array of the two respective elements; the password must be SHA1
	 * encoded.
	 *
	 * The method will return <tt>NULL</tt> if not found.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theIdentifier		User code and password.
	 * @param string				$theCollection		Users collection.
	 * @param boolean				$doAssert			Assert user.
	 *
	 * @static
	 * @return User					User object or <tt>NULL<tt>.
	 */
	static function UserByPassword( Wrapper $theWrapper,
											$theIdentifier,
											$theCollection = NULL,
											$doAssert = TRUE )
	{
		//
		// Assert identifier.
		//
		if( (! is_array( $theIdentifier ))
		 || (count( $theIdentifier ) != 2) )
			throw new \Exception(
				"Invalid user identification." );								// !@! ==>
			
		//
		// Set options.
		//
		$options = kQUERY_OBJECT;
		if( $doAssert )
			$options |= kQUERY_ASSERT;
		
		//
		// Set filter.
		//
		$filter = array( kTAG_CONN_CODE => array_shift( $theIdentifier ),
						 kTAG_CONN_PASS => array_shift( $theIdentifier ) );
		if( $theCollection !== NULL )
			$filter[ kTAG_COLLECTION ]
				= (string) $theCollection;
		
		return
			static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) )
					->matchOne(
						$filter,
						$options );													// ==>
	
	} // UserByPassword.

		

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
	 * In this class we overload this method to:
	 *
	 * <ul>
	 *	<li>Initialise the domain.
	 *	<li>Set the record identifier to the user's fingerprint,
	 *		{@link kTAG_ENTITY_PGP_FINGERPRINT}, or to the user code,
	 *		{@link kTAG_CONN_CODE}.
	 *	<li>Reset managed users count, if necessary.
	 * </ul>
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
		// Init domain.
		//
		if( ! $this->offsetExists( kTAG_DOMAIN ) )
			$this->offsetSet( kTAG_DOMAIN, static::kDEFAULT_DOMAIN );
		
		//
		// Init identifier.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
		{
			if( $this->offsetExists( kTAG_ENTITY_PGP_FINGERPRINT ) )
				$this->offsetSet( kTAG_IDENTIFIER,
								  $this->offsetGet( kTAG_ENTITY_PGP_FINGERPRINT ) );
			elseif( $this->offsetExists( kTAG_CONN_CODE ) )
				$this->offsetSet( kTAG_IDENTIFIER,
								  $this->offsetGet( kTAG_CONN_CODE ) );
		}
		
		//
		// Reset managed users count.
		//
		if( (! $this->offsetExists( kTAG_MANAGED_COUNT ))		// Missing managed count,
		 || (! $this->offsetExists( kTAG_ROLES ))				// or missing roles,
		 || (! in_array( kTYPE_ROLE_INVITE,						// or cannot invite.
		   				 $this->offsetGet( kTAG_ROLES ) )) )
			$this->updateReferrerCount( 0 );
		
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
			// Init authority.
			//
			if( ! $this->offsetExists( kTAG_AUTHORITY ) )
				$this->offsetSet( kTAG_AUTHORITY, kPORTAL_AUTHORITY );
		
			//
			// Init collection.
			//
			if( ! $this->offsetExists( kTAG_COLLECTION ) )
				$this->offsetSet( kTAG_COLLECTION, kPORTAL_DOMAIN );
		
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
		
			//
			// Call parent method.
			//
			parent::preCommitObjectIdentifiers();
			
		} // Not committed.
	
	} // preCommitObjectIdentifiers.

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-COMMIT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postInsert																		*
	 *==================================================================================*/

	/**
	 * Handle object after insert
	 *
	 * We overload this method to increment the user's count of the current object's
	 * manager.
	 *
	 * @param array					$theOffsets			Tag offsets to be added.
	 * @param array					$theReferences		Object references to be incremented.
	 * @param bitfield				$theOptions			Operation options.
	 *
	 * @access protected
	 */
	protected function postInsert( $theOffsets, $theReferences, $theOptions )
	{
		//
		// Call parent method.
		//
		parent::postInsert( $theOffsets, $theReferences, $theOptions );
		
		//
		// Handle referrer.
		//
		$this->updateReferrerCount( 1 );
	
	} // postInsert.

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-DELETE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postDelete																		*
	 *==================================================================================*/

	/**
	 * Handle object after delete
	 *
	 * We overload this method to decrement the user's count of the current object's
	 * manager.
	 *
	 * @param array					$theOffsets			Tag offsets to be removed.
	 * @param array					$theReferences		Object references to be decremented.
	 *
	 * @access protected
	 */
	protected function postDelete( $theOffsets, $theReferences )
	{
		//
		// Call parent method.
		//
		parent::postDelete( $theOffsets, $theReferences );
		
		//
		// Handle referrer.
		//
		$this->updateReferrerCount( -1 );
	
	} // postDelete.

		

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

		

/*=======================================================================================
 *																						*
 *								PROTECTED REFERRER UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getManaged																		*
	 *==================================================================================*/

	/**
	 * Get current object's managed users
	 *
	 * This method can be used to retrieve the current user's managed users, the method will
	 * return the list of managed users as an array indexed by user native identifier and
	 * the user object as value.
	 *
	 * The wrapper may be omitted if the current object has its dictionary set.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access protected
	 * @return ObjectIterator		The list of managed user objects or <tt>NULL</tt>.
	 */
	protected function getManaged( $theWrapper = NULL )
	{
		//
		// Init local storage.
		//
		$this->resolveWrapper( $theWrapper );
		
		//
		// Assert native identifier.
		//
		if( $this->offsetGet( kTAG_NID ) )
		{
			//
			// Set criteria.
			//
			$criteria = array
			(
				kTAG_ENTITY_AFFILIATION => array
				(
					'$elemMatch' => array
					(
						kTAG_TYPE => kTYPE_LIST_REFERRER,
						kTAG_USER_REF => $this->offsetGet( kTAG_NID )
					)
				)
			);
			
			return
				static::ResolveCollection(
					static::ResolveDatabase(
						$theWrapper ) )
						->matchAll(
							array(
								'$elemMatch' => array(
									kTAG_TYPE => kTYPE_LIST_REFERRER,
									kTAG_USER_REF => $this->offsetGet( kTAG_NID ) ) ),
							kQUERY_OBJECT );										// ==>
		
		} // Has native identifier.
		
		return NULL;																// ==>
	
	} // getManaged.

	 
	/*===================================================================================
	 *	getReferrer																		*
	 *==================================================================================*/

	/**
	 * Get current object's referrer
	 *
	 * This method can be used to retrieve the current user's referrer, the method will
	 * return the referrer's native identifier, or <tt>NULL</tt> if unavailable.
	 *
	 * @access protected
	 * @return mixed				The referrer's native identifier or <tt>NULL</tt>.
	 */
	protected function getReferrer()
	{
		//
		// Handle referrer.
		//
		if( $this->offsetExists( kTAG_ENTITY_AFFILIATION ) )
		{
			//
			// Iterate affiliations.
			//
			foreach( $this->offsetGet( kTAG_ENTITY_AFFILIATION ) as $element )
			{
				//
				// Match referrer.
				//
				if( array_key_exists( kTAG_TYPE, $element )
				 && ($element[ kTAG_TYPE ] == kTYPE_LIST_REFERRER) )
				{
					//
					// Check user identifier.
					//
					if( array_key_exists( kTAG_USER_REF, $element ) )
						return $element[ kTAG_USER_REF ];							// ==>
			
				} // Matched referrer.
		
			} // Iterating affiliations.
	
		} // Has affiliation.
		
		return NULL;																// ==>
	
	} // getReferrer.

	 
	/*===================================================================================
	 *	updateReferrerCount																*
	 *==================================================================================*/

	/**
	 * Update referrer count
	 *
	 * This method can be used to increment or decrement the current user's referrer count,
	 * it will update the current object's {@link kTAG_MANAGED_COUNT} offset according to
	 * the provided delta parameter:
	 *
	 * <ul>
	 *	<li><tt>0</tt>: This value should be passed when <em>inserting</em> a user who has
	 *		the {@link kTYPE_ROLE_INVITE} role: the method will set the current object's
	 *		{@link kTAG_MANAGED_COUNT} offset to zero. It is assumed you call this method
	 *		<em>before</em> inserting the object.
	 *	<li><tt>greater than 0</tt>: This value should be passed when <em>inserting</em> a
	 *		user who has a referrer, in that case the method will increment the
	 *		{@link kTAG_MANAGED_COUNT} offset of the referrer's object by the value passed
	 *		in the parameter. It is assumed you call this method <em>after</em> inserting
	 *		the object.
	 *	<li><tt>smaller than 0</tt>: This value should be passed when <em>deleting</em> a
	 *		user who has a referrer, in that case the method will decrement the
	 *		{@link kTAG_MANAGED_COUNT} offset of the referrer's object by the value passed
	 *		in the parameter. It is assumed you call this method <em>after</em> deleting
	 *		the object.
	 * </ul>
	 *
	 * The method will reset the managed count in all cases if the delta is zero; if not,
	 * only if the current user has a referrer, the method will update its managed count.
	 *
	 * @param int					$theDelta			Increment delta.
	 *
	 * @access protected
	 */
	protected function updateReferrerCount( $theDelta )
	{
		//
		// Handle referrer user.
		//
		if( ! $theDelta )
			$this->offsetSet( kTAG_MANAGED_COUNT, 0 );
		
		//
		// Handle user referrer.
		//
		else
		{
			//
			// Handle referrer.
			//
			$referrer = $this->getReferrer();
			if( $referrer !== NULL )
				static::ResolveCollection(
					static::ResolveDatabase( $this->mDictionary, TRUE ) )
						->updateReferenceCount(
							$referrer,				// Referrer ID.
							kTAG_NID,				// ID offset.
							kTAG_MANAGED_COUNT,		// Count offset.
							(int) $theDelta );		// Delta.
		
		} // Positive or negative delta.
	
	} // updateReferrerCount.

	 

} // class User.


?>
