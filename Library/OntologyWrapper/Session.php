<?php

/**
 * Session.php
 *
 * This file contains the definition of the {@link Session} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\SessionObject;
use OntologyWrapper\Tag;
use OntologyWrapper\Term;
use OntologyWrapper\Edge;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *										Session.php										*
 *																						*
 *======================================================================================*/

/**
 * Domains.
 *
 * This file contains the domain definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

/**
 * Session object
 *
 * A session is a collection of transactions and their individual operations, this class
 * can be used to track and document operations such as a data upload or update and they
 * represent the base of logging in this system.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. The native identifier of a
 *		session is automatically generated when inserted the first time, it can then be
 *		used to reference the object and have {@link Transaction} objects update its
 *		members.
 *	<li><tt>{@link kTAG_SESSION_TYPE}</tt>: <em>Session type</em>. This required enumerated
 *		value indicates the type, function or scope of the session.
 *	<li><tt>{@link kTAG_SESSION_START}</tt>: <em>Session start</em>. The starting time stamp
 *		of the session, it is generally set when inserted the first time.
 *	<li><tt>{@link kTAG_SESSION_END}</tt>: <em>Session end</em>. The ending time stamp of
 *		the session, it is generally set by the session destructor.
 *	<li><tt>{@link kTAG_SESSION_STATUS}</tt>: <em>Session status</em>. The result or outcome
 *		of the operation.
 *	<li><tt>{@link kTAG_USER}</tt>: <em>Session user</em>. The object reference for the user
 *		that launched the session.
 *	<li><tt>{@link kTAG_SESSION}</tt>: <em>Session</em>. The object reference of the session
 *		to which this session is related; for instance, an upload session will reference an
 *		update session, so that when the latter completes successfully, the upload session
 *		can be cleared.
 *	<li><tt>{@link kTAG_SESSION_FILES}</tt>: <em>Session files</em>. The list of file
 *		references relating to the subject of the session, such as a template data file for
 *		upload sessions; these resources are persistent.
 *	<li><tt>{@link kTAG_SESSION_COLLECTIONS}</tt>: <em>Session collection</em>. The list of
 *		collection names related to the session operations; these collections are temporary
 *		and will be cleared once the session terminates.
 *	<li><tt>{@link kTAG_PROCESSED}</tt>: <em>Processed elements</em>. The number of elements
 *		processed by the session, this will typically be the transactions count relating to
 *		this session.
 *	<li><tt>{@link kTAG_VALIDATED}</tt>: <em>Validated elements</em>. The number of elements
 *		validated by the session, this will typically be the transactions count that were
 *		cleared by the validation process.
 *	<li><tt>{@link kTAG_REJECTED}</tt>: <em>Rejected elements</em>. The number of elements
 *		rejected by the session, this will typically be the transactions count that were
 *		not cleared by the validation process.
 *	<li><tt>{@link kTAG_SKIPPED}</tt>: <em>Skipped elements</em>. The number of elements
 *		skipped by the session, this will typically be the transactions count that were
 *		skipped by the validation process; such as empty data template lines.
 * </ul>
 *
 * The typical workflow of a session is as follows:
 *
 * <ul>
 *	<li>The object is instantiated with the session type, the requesting user and the list
 *		of files to be used.
 *	<li>The object is committed, so that a session identifier is generated.
 *	<li>Transaction objects will update the session counters and status.
 *	<li>When all transactions are finished, temporary resources are cleared.
 * </ul>.
 *
 * Because of this workflow, sessions can only be inseted and updating is prevented, this
 * behaviour is ensured by the parent class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 13/02/2015
 */
class Session extends SessionObject
{
	/**
	 * Default collection name.
	 *
	 * This constant provides the <i>default collection name</i> in which objects of this
	 * class are stored.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_sessions';

	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_SESSION;

		

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
	 * In this class we link the inited status with the presence of the session type, user
	 * and status.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @see kTAG_USER kTAG_SESSION_TYPE kTAG_SESSION_STATUS
	 *
	 * @uses isInited()
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Load object with contents.
		//
		parent::__construct( $theContainer, $theIdentifier );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_USER ) &&
						 \ArrayObject::offsetExists( kTAG_SESSION_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_SESSION_STATUS ) );

	} // Constructor.

	

/*=======================================================================================
 *																						*
 *							PUBLIC NAME MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Get object name
	 *
	 * In this class we return the object identifier.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 */
	public function getName( $theLanguage )					{	return $this->__toString();	}

		

/*=======================================================================================
 *																						*
 *							PUBLIC OBJECT REFERENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getUser																			*
	 *==================================================================================*/

	/**
	 * Get referenced user
	 *
	 * This method will return the requesting user object; if none is set, the method will
	 * return <tt>NULL</tt>, or raise an exception if the second parameter is <tt>TRUE</tt>.
	 *
	 * The first parameter is the wrapper in which the current object is, or will be,
	 * stored: if the current object has the {@link dictionary()}, this parameter may be
	 * omitted; if the wrapper cannot be resolved, the method will raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param boolean				$doAssert			Raise exception if not matched.
	 *
	 * @access public
	 * @return PersistentObject		Referenced user or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function getUser( $theWrapper = NULL, $doAssert = TRUE )
	{
		//
		// Check user.
		//
		if( $this->offsetExists( kTAG_USER ) )
		{
			//
			// Resolve wrapper.
			//
			$this->resolveWrapper( $theWrapper );
		
			//
			// Resolve collection.
			//
			$collection
				= User::ResolveCollection(
					User::ResolveDatabase( $theWrapper, TRUE ) );
			
			//
			// Set criteria.
			//
			$criteria = array( kTAG_NID => $this->offsetGet( kTAG_USER ) );
			
			//
			// Locate object.
			//
			$object = $collection->matchOne( $criteria );
			if( $doAssert
			 && ($object === NULL) )
				throw new \Exception(
					"Unable to get user: "
				   ."referenced object not matched." );							// !@! ==>
			
			return $object;															// ==>
		
		} // Has user.
		
		return NULL;																// ==>
	
	} // getUser.

		

/*=======================================================================================
 *																						*
 *								STATIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	SetStatus																		*
	 *==================================================================================*/

	/**
	 * Set the status
	 *
	 * This method can be used to set the session status of the object identified by
	 * the provided identifier of the calling class.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param string				$theStatus			Status value.
	 *
	 * @static
	 */
	static function SetStatus( Wrapper $theWrapper, $theIdentifier, $theStatus )
	{
		//
		// Check status.
		//
		switch( $theStatus )
		{
			case kTYPE_STATUS_OK:
			case kTYPE_STATUS_FAILED:
			case kTYPE_STATUS_EXECUTING:
				break;
			
			//
			// Invalid status.
			//
			default:
				throw new \Exception(
					"Cannot set status: "
				   ."invalid status value [$theStatus]." );						// !@! ==>
		}
		
		//
		// Normalise identifier.
		//
		if( ! ($theIdentifier instanceof \MongoId) )
		{
			//
			// Convert to string.
			//
			$theIdentifier = (string) $theIdentifier;
			
			//
			// Handle valid identifier.
			//
			if( \MongoId::isValid( $theIdentifier ) )
				$theIdentifier = new \MongoId( $theIdentifier );
			
			//
			// Invalid identifier.
			//
			else
				throw new \Exception(
					"Cannot set status: "
				   ."invalid identifier [$theIdentifier]." );					// !@! ==>
		}
		
		//
		// Resolve collection.
		//
		$collection
			= Session::ResolveCollection(
				Session::ResolveDatabase( $theWrapper, TRUE ) );
	
		//
		// Set property.
		//
		$collection->replaceOffsets(
			$theIdentifier,										// Object ID.
			array( kTAG_SESSION_STATUS => $theStatus ) );		// Modifications.
	
	} // SetStatus.

		

/*=======================================================================================
 *																						*
 *								STATIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	CreateIndexes																	*
	 *==================================================================================*/

	/**
	 * Create indexes
	 *
	 * In this class we index the following offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_ID_PERSISTENT}</tt>: Persistent identifier.
	 *	<li><tt>{@link kTAG_TAG}</tt>: Tag reference.
	 *	<li><tt>{@link kTAG_TERM}</tt>: Term reference.
	 *	<li><tt>{@link kTAG_NODE_TYPE}</tt>: Session type.
	 * </ul>
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 *
	 * @static
	 * @return CollectionObject		The collection.
	 */
	static function CreateIndexes( DatabaseObject $theDatabase )
	{
		//
		// Set parent indexes and retrieve collection.
		//
		$collection = parent::CreateIndexes( $theDatabase );
		
		//
		// Set session type index.
		//
		$collection->createIndex( array( kTAG_SESSION_TYPE => 1 ),
								  array( "name" => "TYPE" ) );
		
		//
		// Set session status index.
		//
		$collection->createIndex( array( kTAG_SESSION_STATUS => 1 ),
								  array( "name" => "STATUS" ) );
		
		//
		// Set session user index.
		//
		$collection->createIndex( array( kTAG_USER => 1 ),
								  array( "name" => "USER" ) );
		
		//
		// Set related session index.
		//
		$collection->createIndex( array( kTAG_SESSION => 1 ),
								  array( "name" => "SESSION",
								  		 "sparse" => TRUE ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	UnmanagedOffsets																*
	 *==================================================================================*/

	/**
	 * Return unmanaged offsets
	 *
	 * In this class we exclude all offsets that are supposed to be set externally, this
	 * includes:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_SESSION_STATUS}</tt>: Session status.
	 *	<li><tt>{@link kTAG_SESSION_START}</tt>: Session start.
	 *	<li><tt>{@link kTAG_SESSION_END}</tt>: Session end.
	 * </ul>
	 *
	 * @static
	 * @return array				List of unmanaged offsets.
	 *
	 * @see kTAG_SESSION_STATUS kTAG_SESSION_START kTAG_SESSION_END
	 */
	static function UnmanagedOffsets()
	{
		return array_merge(
			parent::UnmanagedOffsets(),
			array( kTAG_SESSION_STATUS,
				   kTAG_SESSION_START, kTAG_SESSION_END ) );						// ==>
	
	} // UnmanagedOffsets.

	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we use the session schema.
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							$this->mDictionary
								->collectStructureOffsets(
									'schema::domain:session' ) );					// ==>
	
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
	 * In this class we link the inited status with the presence of the session type, user
	 * and status.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_USER kTAG_SESSION_TYPE kTAG_SESSION_STATUS
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_USER ) &&
						 \ArrayObject::offsetExists( kTAG_SESSION_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_SESSION_STATUS ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of the session type, user
	 * and status.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_USER kTAG_SESSION_TYPE kTAG_SESSION_STATUS
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_USER ) &&
						 \ArrayObject::offsetExists( kTAG_SESSION_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_SESSION_STATUS ) );
	
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
	 * In this class we initialise the session start and status properties.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SESSION_START kTAG_SESSION_STATUS
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Initialise session start.
		//
		if( ! $this->offsetExists( kTAG_SESSION_START ) )
			$this->offsetSet( kTAG_SESSION_START, new \MongoTimestamp() );
		
		//
		// Initialise session status.
		//
		if( ! $this->offsetExists( kTAG_SESSION_STATUS ) )
			$this->offsetSet( kTAG_SESSION_STATUS, kTYPE_STATUS_EXECUTING );
		
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
		
	} // preCommitPrepare.

	 

} // class Session.


?>
