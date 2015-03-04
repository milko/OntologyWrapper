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
 * Session object
 *
 * A session is a collection of transactions and their individual operations, this class
 * can be used to track and document operations such as a data upload or update and they
 * represent the base of logging in this system.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_SESSION_TYPE}</tt>: <em>Session type</em>. This required enumerated
 *		value indicates the type, function or scope of the session.
 *	<li><tt>{@link kTAG_SESSION_START}</tt>: <em>Session start</em>. The starting time stamp
 *		of the session, it is generally set when inserted the first time.
 *	<li><tt>{@link kTAG_SESSION_END}</tt>: <em>Session end</em>. The ending time stamp of
 *		the session, it is generally set by the session destructor.
 *	<li><tt>{@link kTAG_SESSION_STATUS}</tt>: <em>Session status</em>. The result or outcome
 *		of the operation.
 *	<li><tt>{@link kTAG_CONN_COLLS}</tt>: <em>Session collections</em>. The list of
 *		collection names related to the session operations; these collections are temporary
 *		and will be cleared once the session terminates.
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
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	offsetExists																	*
	 *==================================================================================*/

	/**
	 * Check if an offset exists
	 *
	 * We overload this method to intercept the following offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_SESSION_END}</tt>: Session end.
	 *	<li><tt>{@link kTAG_CONN_COLLS}</tt>: Working collections.
	 * </ul>
	 *
	 * Note that this method will consider the offset extern, only if provided as an offset,
	 * if provided as a tag native identifier it will function in the default manner.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> the offset exists.
	 *
	 * @uses resolvePersistent()
	 */
	public function offsetExists( $theOffset )
	{
		//
		// Handle committed objects.
		//
		if( $this->committed() )
		{
			//
			// Handle extern properties.
			//
			switch( $theOffset )
			{
				case kTAG_SESSION_END:
				case kTAG_CONN_COLLS:
					return
						in_array( $theOffset,
								  $this->resolvePersistent( TRUE )
								  	->arrayKeys() );								// ==>
			}
		
		} // Committed.
		
		return parent::offsetExists( $theOffset );									// ==>
	
	} // offsetExists.

	 
	/*===================================================================================
	 *	offsetGet																		*
	 *==================================================================================*/

	/**
	 * Return a value at a given offset
	 *
	 * We overload this method to intercept extern properties, these are prompted from the
	 * database rather than from the object when the latter is committed: these are the
	 * extern offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_SESSION_STATUS}</tt>: Status.
	 *	<li><tt>{@link kTAG_SESSION_END}</tt>: Session end.
	 *	<li><tt>{@link kTAG_CONN_COLLS}</tt>: Fields count.
	 * </ul>
	 *
	 * Note that this method will consider the offset extern, only if provided as an offset,
	 * if provided as a tag native identifier it will function in the default manner.
	 *
	 * @param mixed					$theOffset			Offset.
	 *
	 * @access public
	 * @return mixed				Offset value or <tt>NULL</tt>.
	 *
	 * @uses resolvePersistent()
	 */
	public function offsetGet( $theOffset )
	{
		//
		// Handle committed objects.
		//
		if( $this->committed() )
		{
			//
			// Handle extern properties.
			//
			switch( $theOffset )
			{
				case kTAG_SESSION_STATUS:
				case kTAG_SESSION_END:
				case kTAG_CONN_COLLS:
					$data = $this->resolvePersistent( TRUE )->getArrayCopy();
					return ( array_key_exists( $theOffset, $data ) )
						 ? $data[ $theOffset ]										// ==>
						 : NULL;													// ==>
			}
		
		} // Committed.
		
		return parent::offsetGet( $theOffset );										// ==>
	
	} // offsetGet.

	 
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * Set a value at a given offset
	 *
	 * We overload this method to intercept extern properties, these are set in the database
	 * rather than from the object when the latter is committed: these are the extern
	 * offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_SESSION_STATUS}</tt>: Status, we also check the value.
	 *	<li><tt>{@link kTAG_CONN_COLLS}</tt>: Working collections.
	 *	<li><tt>{@link kTAG_SESSION_START}</tt>: Session start, we also intercept
	 *		<tt>TRUE</tt> for setting the current time stamp.
	 *	<li><tt>{@link kTAG_SESSION_END}</tt>: Session end, we also intercept <tt>TRUE</tt>
	 *		for setting the current time stamp.
	 * </ul>
	 *
	 * Note that this method will consider the offset extern, only if provided as an offset,
	 * if provided as a tag native identifier it will function in the default manner.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Parse by offset.
		//
		switch( $theOffset )
		{
			case kTAG_SESSION_STATUS:
				//
				// Check value.
				//
				switch( $theValue )
				{
					case kTYPE_STATUS_OK:
					case kTYPE_STATUS_FAILED:
					case kTYPE_STATUS_EXECUTING:
						break;
					
					default:
						throw new \Exception(
							"Cannot set session status: "
						   ."invalid status type [$theValue]." );				// !@! ==>
				
				} // Parsed status.
				
				//
				// Handle committed.
				//
				if( $this->committed() )
					static::ResolveCollection(
						static::ResolveDatabase( $this->mDictionary, TRUE ), TRUE )
							->replaceOffsets(
								$this->offsetGet( kTAG_NID ),
								array( $theOffset => $theValue ) );
				
				//
				// Handle uncommitted.
				//
				else
					parent::offsetSet( $theOffset, $theValue );
				
				break;
			
			case kTAG_SESSION_END:
			case kTAG_SESSION_START:
				//
				// Set current stamp.
				//
				if( $theValue === TRUE )
					$theValue
						= self::ResolveCollection(
							self::ResolveDatabase( $this->mDictionary, TRUE ) )
								->getTimeStamp();
			
			case kTAG_CONN_COLLS:
				
				//
				// Handle committed.
				//
				if( $this->committed() )
					static::ResolveCollection(
						static::ResolveDatabase( $this->mDictionary, TRUE ), TRUE )
							->replaceOffsets(
								$this->offsetGet( kTAG_NID ),
								array( $theOffset => $theValue ) );
				
				//
				// Handle uncommitted.
				//
				else
					parent::offsetSet( $theOffset, $theValue );
				
				break;
			
			default:
				parent::offsetSet( $theOffset, $theValue );
				
				break;
		}
	
	} // offsetSet.

	 
	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * Reset a value at a given offset
	 *
	 * We overload this method to intercept extern properties:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_SESSION}</tt>: Referencing session.
	 *	<li><tt>{@link kTAG_SESSION_STATUS}</tt>: Status, we also check the value.
	 *	<li><tt>{@link kTAG_CONN_COLLS}</tt>: Working collections.
	 *	<li><tt>{@link kTAG_SESSION_END}</tt>: Session end, we also intercept <tt>TRUE</tt>
	 *		for setting the current time stamp.
	 * </ul>
	 *
	 * Note that this method will consider the offset extern, only if provided as an offset,
	 * if provided as a tag native identifier it will function in the default manner.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @uses nestedOffsetUnset()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Handle committed objects.
		//
		if( $this->committed() )
		{
			//
			// Handle extern properties.
			//
			switch( $theOffset )
			{
				case kTAG_SESSION_STATUS:
				case kTAG_CONN_COLLS:
				case kTAG_SESSION_END:
					static::ResolveCollection(
						static::ResolveDatabase( $this->mDictionary, TRUE ) )
							->replaceOffsets(
								$this->offsetGet( kTAG_NID ),
								array( $theOffset => NULL ) );
					break;
				
				default:
					parent::offsetUnset( $theOffset );
					break;
			}
		
		} // Committed.
		
		//
		// Handle uncommitted objects.
		//
		else
			parent::offsetUnset( $theOffset );
	
	} // offsetUnset.

	

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newTransaction																	*
	 *==================================================================================*/

	/**
	 * Create transaction
	 *
	 * This method can be used to create a transaction referenced by the current session,
	 * holding the provided parameters; the transaction will be committed by this method.
	 *
	 * If the current object is not committed, the method will raise an exception.
	 *
	 * @param string				$theType			Transaction type.
	 * @param string				$theCollection		Transaction collection.
	 * @param int					$theRecord			Transaction record.
	 *
	 * @access public
	 * @return Transaction			Transaction object.
	 *
	 * @throws Exception
	 *
	 * @uses committed()
	 */
	public function newTransaction( $theType, $theCollection = NULL, $theRecord = NULL )
	{
		//
		// Check if committed.
		//
		if( $this->committed() )
		{
			//
			// Instantiate object.
			//
			$transaction = new Transaction( $this->mDictionary );
		
			//
			// Set user and session.
			//
			$tags = array( kTAG_USER => kTAG_USER, kTAG_NID => kTAG_SESSION );
			foreach( $tags as $key => $value )
				$transaction->offsetSet( $value, $this->offsetGet( $key ) );
		
			//
			// Set type.
			//
			$transaction->offsetSet( kTAG_TRANSACTION_TYPE, $theType );
		
			//
			// Set collection.
			//
			if( $theCollection !== NULL )
				$transaction->offsetSet( kTAG_TRANSACTION_COLLECTION, $theCollection );
		
			//
			// Set record.
			//
			if( $theRecord !== NULL )
				$transaction->offsetSet( kTAG_TRANSACTION_RECORD, $theRecord );
			
			//
			// Commit transaction.
			//
			$transaction->commit();
			
			return $transaction;													// ==>
		
		} // Object is committed.
		
		throw new \Exception(
			"Cannot create transaction: "
		   ."the session is not committed." );									// !@! ==>
	
	} // newTransaction.

	

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
	 * In this class we return the session type, start and status.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 *
	 * @see kTAG_SESSION_TYPE kTAG_SESSION_START kTAG_SESSION_STATUS
	 */
	public function getName( $theLanguage )
	{
		//
		// Init local storage.
		//
		$name = Array();
		
		//
		// Set type.
		//
		if( $this->offsetExists( kTAG_SESSION_TYPE ) )
			$name[]
				= self::SelectLanguageString(
					Term::ResolveObject(
						$this->mDictionary,
						Term::kSEQ_NAME,
						$this->offsetGet( kTAG_SESSION_TYPE ),
						TRUE )
							->offsetGet( kTAG_LABEL ),
					$theLanguage );
		
		//
		// Set start.
		//
		if( $this->offsetExists( kTAG_SESSION_START ) )
			$name[]
				= date(
					'Y/m/d h:i:s',
					$this->offsetGet( kTAG_SESSION_START )
						->sec )
				 .' '
				 . $this->offsetGet( kTAG_SESSION_START )
				 	->usec;
		
		//
		// Set status.
		//
		if( $this->offsetExists( kTAG_SESSION_STATUS ) )
			$name[]
				= '('
				 .self::SelectLanguageString(
					Term::ResolveObject(
						$this->mDictionary,
						Term::kSEQ_NAME,
						$this->offsetGet( kTAG_SESSION_STATUS ),
						TRUE )
							->offsetGet( kTAG_LABEL ),
					$theLanguage )
				 .')';
		
		return implode( ' ', $name );												// ==>
	
	} // getName.

		

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
		// Set session start index.
		//
		$collection->createIndex( array( kTAG_SESSION_START => 1 ),
								  array( "name" => "START" ) );
		
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
	 *	<li><tt>{@link kTAG_SESSION}</tt>: Referred session.
	 *	<li><tt>{@link kTAG_SESSION_STATUS}</tt>: Session status.
	 *	<li><tt>{@link kTAG_SESSION_END}</tt>: Session end.
	 *	<li><tt>{@link kTAG_CONN_COLLS}</tt>: Session working collections.
	 * </ul>
	 *
	 * @static
	 * @return array				List of unmanaged offsets.
	 *
	 * @see kTAG_SESSION_STATUS kTAG_SESSION_END
	 */
	static function UnmanagedOffsets()
	{
		return array_merge(
			parent::UnmanagedOffsets(),
			array( kTAG_SESSION,
				   kTAG_SESSION_STATUS, kTAG_SESSION_END, kTAG_CONN_COLLS ) );		// ==>
	
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
	 * In this class we initialise the session status and start time stamp.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @see kTAG_SESSION_START kTAG_SESSION_STATUS
	 *
	 * @uses start()
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Initialise session start.
		//
		if( ! $this->offsetExists( kTAG_SESSION_START ) )
			$this->offsetSet( kTAG_SESSION_START, TRUE );
		
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

	

/*=======================================================================================
 *																						*
 *						PROTECTED OBJECT REFERENCING INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateManyToOne																	*
	 *==================================================================================*/

	/**
	 * Update many to one relationships
	 *
	 * In this class we overload this method to delete all related sessions, transactions
	 * and files.
	 *
	 * @param bitfield				$theOptions			Operation options.
	 *
	 * @access protected
	 */
	protected function updateManyToOne( $theOptions )
	{
		//
		// Check options.
		//
		if( ($theOptions & kFLAG_OPT_DELETE)	// Deleting
		 && ($theOptions & kFLAG_OPT_REL_ONE) )	// and many to one relationships.
		{
			//
			// Get sessions collection.
			//
			$collection
				= Session::ResolveCollection(
					Session::ResolveDatabase( $this->mDictionary, TRUE ) );
			
			//
			// Set criteria.
			//
			$criteria = array( '$or' => Array() );
			$criteria[ '$or' ][] = array( kTAG_SESSION => $this->offsetGet( kTAG_NID ) );
			$criteria[ '$or' ][] = array( kTAG_SESSIONS => $this->offsetGet( kTAG_NID ) );
		
			//
			// Delete related.
			//
			$list = $collection->matchAll( $criteria, kQUERY_OBJECT );
			foreach( $list as $element )
				$element->deleteObject();
		
			//
			// Get transactions collection.
			//
			$collection
				= Transaction::ResolveCollection(
					Transaction::ResolveDatabase( $this->mDictionary, TRUE ) );
			
			//
			// Set criteria.
			//
			$criteria = array( '$or' => Array() );
			$criteria[ '$or' ][] = array( kTAG_SESSION => $this->offsetGet( kTAG_NID ) );
			$criteria[ '$or' ][] = array( kTAG_SESSIONS => $this->offsetGet( kTAG_NID ) );
		
			//
			// Delete related.
			//
			$list = $collection->matchAll( $criteria, kQUERY_OBJECT );
			foreach( $list as $element )
				$element->deleteObject();
		
			//
			// Get files collection.
			//
			$collection
				= FileObject::ResolveCollection(
					FileObject::ResolveDatabase( $this->mDictionary, TRUE ) );
			
			//
			// Set criteria.
			//
			$criteria = array( '$or' => Array() );
			$criteria[ '$or' ][] = array( kTAG_SESSION => $this->offsetGet( kTAG_NID ) );
			$criteria[ '$or' ][] = array( kTAG_SESSIONS => $this->offsetGet( kTAG_NID ) );
		
			//
			// Delete related.
			//
			$list = $collection->matchAll( $criteria, kQUERY_OBJECT );
			foreach( $list as $element )
				$element->deleteObject();
			
			//
			// Handle working collections.
			//
			if( $this->offsetExists( kTAG_CONN_COLLS ) )
			{
				//
				// Get sessions database.
				//
				$database = Session::ResolveDatabase( $this->mDictionary, TRUE ) );
				
				//
				// Iterate collections.
				//
				foreach( $this->offsetGet( kTAG_CONN_COLLS ) as $collection )
					$database->collection( $collectionm TRUE )
						->drop();
			
			} // Has working collections.
		
		} // Deleting file.
		
		//
		// Call parent method.
		//
		else
			parent::updateManyToOne( $theOptions );
	
	} // updateManyToOne.

		

/*=======================================================================================
 *																						*
 *								PROTECTED REFERENCE UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	copySelfReference																*
	 *==================================================================================*/

	/**
	 * Copy self reference
	 *
	 * In this class, besides setting a self reference, we also set a reference to the user.
	 *
	 * @param PersistentObject		$theObject			Target object.
	 *
	 * @access protected
	 */
	protected function copySelfReference( PersistentObject $theObject )
	{
		//
		// Set user reference.
		//
		if( $this->offsetExists( kTAG_USER ) )
			$theObject->offsetSet(
				kTAG_USER,
				$this->offsetGet( kTAG_USER ) );
		
		//
		// Set users reference.
		//
		if( $this->offsetExists( kTAG_USERS ) )
			$theObject->offsetSet(
				kTAG_USERS,
				$this->offsetGet( kTAG_USERS ) );
		
		//
		// Call parent method.
		//
		parent::copySelfReference( $theObject );
		
	} // copySelfReference.

	 

} // class Session.


?>
