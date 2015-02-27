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
 *	<li><tt>{@link kTAG_USER}</tt>: <em>Session user</em>. The object reference for the user
 *		that launched the session.
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
 *							PUBLIC MEMBERS ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	type																			*
	 *==================================================================================*/

	/**
	 * Manage session type
	 *
	 * This method can be used to set or retrieve the <i>session type</i>, it accepts a
	 * parameter which represents either the type or the requested operation, depending on
	 * its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><em>other</em>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * If the object is committed and you attempt to set the type, the method will raise an
	 * exception.
	 *
	 * @param mixed					$theValue			New type or operation.
	 *
	 * @access public
	 * @return mixed				Current type.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SESSION_TYPE
	 *
	 * @uses committed()
	 */
	public function type( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->offsetGet( kTAG_SESSION_TYPE );							// ==>
		
		//
		// Go on if not committed.
		//
		if( ! $this->committed() )
		{
			//
			// Check value.
			//
			switch( $theValue )
			{
				case kTYPE_SESSION_UPLOAD:
				case kTYPE_SESSION_UPDATE:
					$this->offsetSet( kTAG_SESSION_TYPE, $theValue );
					return $theValue;												// ==>
			}
			
			throw new \Exception(
				"Cannot set session type: "
			   ."invalid enumeration [$theValue]." );							// !@! ==>
		
		} // Object not committed.
		
		throw new \Exception(
			"Cannot set session type: "
		   ."the object is committed." );										// !@! ==>
	
	} // type.

	 
	/*===================================================================================
	 *	start																			*
	 *==================================================================================*/

	/**
	 * Manage session start
	 *
	 * This method can be used to set or retrieve the <i>session start</i>, it accepts a
	 * parameter which represents either the starting time stamp or the requested operation,
	 * depending on its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>TRUE</tt>: Set with current time stamp.
	 *	<li><em>other</em>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * If the object is committed and you attempt to set the start, the method will raise an
	 * exception.
	 *
	 * The object must have been instantiated with a wrapper.
	 *
	 * @param mixed					$theValue			New start or operation.
	 *
	 * @access public
	 * @return mixed				Current start.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SESSION_START
	 *
	 * @uses committed()
	 */
	public function start( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->offsetGet( kTAG_SESSION_START );							// ==>
		
		//
		// Go on if not committed.
		//
		if( ! $this->committed() )
		{
			//
			// Init value.
			//
			if( $theValue === TRUE )
				$theValue
					= self::ResolveCollection(
						self::ResolveDatabase( $this->mDictionary, TRUE ) )
							->getTimeStamp();
			
			//
			// Set value.
			//
			$this->offsetSet( kTAG_SESSION_START, $theValue );
			
			return $theValue;														// ==>
		
		} // Object not committed.
		
		throw new \Exception(
			"Cannot set session start: "
		   ."the object is committed." );										// !@! ==>
	
	} // start.

	 
	/*===================================================================================
	 *	end																				*
	 *==================================================================================*/

	/**
	 * Manage session end
	 *
	 * This method can be used to set or retrieve the <i>session end</i>, it accepts a
	 * parameter which represents either the ending time stamp or the requested operation,
	 * depending on its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value, depending on the commit status of the
	 *		object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will return the
	 *			value taken from the persistent object and will update the current object
	 *			with that value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			return the value found in the current object.
	 *	  </ul>
	 *	<li><tt>TRUE</tt>: Set with current time stamp, depending on the commit status of
	 *		the object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will set the
	 *			current time stamp in the persistent object and update the current object's
	 *			value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			set the current time stamp in the current object.
	 *	  </ul>
	 *	<li><em>other</em>: Set the value with the provided parameter, depending on the
	 *		commit status of the object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will set the
	 *			provided value in the persistent object and update the current object's
	 *			value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			set the provided value in the current object.
	 *	  </ul>
	 * </ul>
	 *
	 * If you provide <tt>FALSE</tt> as a value, the method will raise an exception.
	 *
	 * The object must have been instantiated with a wrapper.
	 *
	 * @param mixed					$theValue			New end or operation.
	 *
	 * @access public
	 * @return mixed				Current end.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SESSION_END
	 *
	 * @uses handleOffset()
	 */
	public function end( $theValue = NULL )
	{
		//
		// Check value.
		//
		if( $theValue !== FALSE )
		{
			//
			// Init value.
			//
			if( $theValue === TRUE )
				$theValue
					= self::ResolveCollection(
						self::ResolveDatabase( $this->mDictionary, TRUE ) )
							->getTimeStamp();
			
			return $this->handleOffset( kTAG_SESSION_END, $theValue );				// ==>
		
		} // Not allowed to delete.
		
		throw new \Exception(
			"Cannot delete session end." );										// !@! ==>
	
	} // end.

	 
	/*===================================================================================
	 *	status																			*
	 *==================================================================================*/

	/**
	 * Manage session status
	 *
	 * This method can be used to set or retrieve the <i>session status</i>, it accepts a
	 * parameter which represents either the status or the requested operation, depending on
	 * its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value, depending on the commit status of the
	 *		object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will return the
	 *			value taken from the persistent object and will update the current object
	 *			with that value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			return the value found in the current object.
	 *	  </ul>
	 *	<li><em>other</em>: Set the value with the provided parameter, depending if the
	 *		the object is committed or not:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will set the
	 *			provided value in the persistent object and update the current object's
	 *			value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			set the provided value in the current object.
	 *	  </ul>
	 * </ul>
	 *
	 * If you provide <tt>FALSE</tt> as a value, the method will raise an exception.
	 *
	 * The object must have been instantiated with a wrapper.
	 *
	 * @param mixed					$theValue			New status or operation.
	 *
	 * @access public
	 * @return mixed				Current status.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SESSION_STATUS
	 * @see kTYPE_STATUS_OK kTYPE_STATUS_FAILED kTYPE_STATUS_EXECUTING
	 *
	 * @uses handleOffset()
	 */
	public function status( $theValue = NULL )
	{
		//
		// Check value.
		//
		if( $theValue !== FALSE )
		{
			//
			// Check status.
			//
			if( $theValue !== NULL )
			{
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
			
			} // Provided status.
			
			return $this->handleOffset( kTAG_SESSION_STATUS, $theValue );			// ==>
		
		} // Not allowed to delete.
		
		throw new \Exception(
			"Cannot delete session status." );									// !@! ==>
	
	} // status.

	 
	/*===================================================================================
	 *	user																			*
	 *==================================================================================*/

	/**
	 * Manage user
	 *
	 * This method can be used to manage the session user, the method accepts the follo<ing
	 * parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: <em>User or operation</em>:
	 *	  <ul>
	 *		<li><tt>NULL</tt>: <em>Retrieve user</em>:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will return the
	 *				object's value.
	 *			<li><em>The object is committed</em>: The method will return the persistent
	 *				object's value and update the current object.
	 *		  </ul>
	 *		<li><em>other</em>: <em>Set user</em>:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will set the provided
	 *				value in the current object.
	 *			<li><em>The object is committed</em>: The method will first set the value in
	 *				the persistent object, then set it in the current object and return it.
	 *		  </ul>
	 *	  </ul>
	 *	<li><b>$doObject</b>: <em>Result type</em>: If <tt>TRUE</tt>, the method will
	 *		return the user object, rather than its reference.
	 * </ul>
	 *
	 * If you provide <tt>FALSE</tt> as a value, the method will raise an exception.
	 *
	 * The object must have been instantiated with a wrapper.
	 *
	 * The method will return the user or <tt>NULL</tt> if not set.
	 *
	 * @param mixed					$theValue			Session object or reference.
	 * @param boolean				$doObject			TRUE return object.
	 *
	 * @access public
	 * @return mixed				Session object or reference.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_USER
	 *
	 * @uses handleReference()
	 */
	public function user( $theValue = NULL, $doObject = FALSE )
	{
		//
		// Check value.
		//
		if( $theValue !== FALSE )
		{
			//
			// Check reference.
			//
			if( $theValue !== NULL )
			{
				//
				// Handle object.
				//
				if( $theValue instanceof User )
					$theValue = $theValue->offsetGet( kTAG_NID );
			
				//
				// Check if object exists.
				//
				User::ResolveObject( $this->mDictionary,
									 User::kSEQ_NAME,
									 $theValue,
									 TRUE );
	
			} // Checked reference.
		
			return $this->handleReference(
						kTAG_USER, 'User', $theValue, $doObject );					// ==>
		
		} // Not allowed to delete.
		
		throw new \Exception(
			"Cannot delete session user." );									// !@! ==>
	
	} // user.

	 
	/*===================================================================================
	 *	collections																				*
	 *==================================================================================*/

	/**
	 * Manage session collections
	 *
	 * This method can be used to set or retrieve the <i>session end</i>, it accepts a
	 * parameter which represents either the ending time stamp or the requested operation,
	 * depending on its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value, depending on the commit status of the
	 *		object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will return the
	 *			value taken from the persistent object and will update the current object
	 *			with that value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			return the value found in the current object.
	 *	  </ul>
	 *	<li><tt>TRUE</tt>: Set with current time stamp, depending on the commit status of
	 *		the object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will set the
	 *			current time stamp in the persistent object and update the current object's
	 *			value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			set the current time stamp in the current object.
	 *	  </ul>
	 *	<li><em>other</em>: Set the value with the provided parameter, depending on the
	 *		commit status of the object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will set the
	 *			provided value in the persistent object and update the current object's
	 *			value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			set the provided value in the current object.
	 *	  </ul>
	 * </ul>
	 *
	 * If you provide <tt>FALSE</tt> as a value, the method will raise an exception.
	 *
	 * The object must have been instantiated with a wrapper.
	 *
	 * @param mixed					$theValue			New end or operation.
	 *
	 * @access public
	 * @return mixed				Current end.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SESSION_END
	 *
	 * @uses handleOffset()
	 */
	public function collections( $theValue = NULL )
	{
		//
		// Check value.
		//
		if( $theValue !== FALSE )
		{
			//
			// Init value.
			//
			if( $theValue === TRUE )
				$theValue
					= self::ResolveCollection(
						self::ResolveDatabase( $this->mDictionary, TRUE ) )
							->getTimeStamp();
			
			return $this->handleOffset( kTAG_SESSION_END, $theValue );				// ==>
		
		} // Not allowed to delete.
		
		throw new \Exception(
			"Cannot delete session end." );										// !@! ==>
	
	} // collections.

	

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
	 * This method will create and commit a new transaction returning its object.
	 *
	 * If the current object is not committed, the method will raise an exception.
	 *
	 * @param string				$theType			Transaction type.
	 * @param string				$theCollection		Transaction collection.
	 *
	 * @access public
	 * @return Transaction			Transaction object.
	 *
	 * @throws Exception
	 *
	 * @uses committed()
	 */
	public function newTransaction( $theType, $theCollection = NULL )
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
			// Set session.
			//
			$transaction->session( $this );
		
			//
			// Set type.
			//
			$transaction->type( $theType );
		
			//
			// Set collection.
			//
			if( $theCollection !== NULL )
				$transaction->collection( $theCollection );
			
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
			array( kTAG_SESSION_STATUS, kTAG_SESSION_END, kTAG_CONN_COLLS ) );		// ==>
	
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
 *						PROTECTED OBJECT REFERENCING INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateManyToOne																	*
	 *==================================================================================*/

	/**
	 * Update many to one relationships
	 *
	 * In this class we overload this method to delete all related transactions and files
	 * when deleting the session.
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
			// Set session criteria.
			//
			$criteria = array( kTAG_SESSION => $this->offsetGet( kTAG_NID ) );
			
			//
			// Select transactions.
			//
			$list
				= Transaction::ResolveCollection(
					Transaction::ResolveDatabase(
						$this->mDictionary ) )
							->matchAll( $criteria, kQUERY_OBJECT );
			
			//
			// Delete transactions.
			//
			foreach( $list as $object )
				$object->deleteObject();
		
			//
			// Delete files.
			//
			$this->filesCollection()->deleteByCriteria( $criteria );
			
			//
			// Select sessions.
			//
			$list
				= Session::ResolveCollection(
					Session::ResolveDatabase(
						$this->mDictionary ) )
							->matchAll( $criteria, kQUERY_OBJECT );
			
			//
			// Delete sessions.
			//
			foreach( $list as $object )
				$object->deleteObject();
		
			//
			// Delete working collections.
			//
			if( $this->offsetExists( kTAG_CONN_COLLS ) )
			{
				foreach( $this->offsetGet( kTAG_CONN_COLLS ) as $collection )
					$this->resolveDatabase( static::kSEQ_NAME )
						->collection( $collection )
							->drop();
		
			} // Has working collections.
		
		} // Deleting session.
	
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
