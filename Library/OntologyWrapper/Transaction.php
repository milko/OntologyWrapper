<?php

/**
 * Transaction.php
 *
 * This file contains the definition of the {@link Transaction} class.
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
 *									Transaction.php										*
 *																						*
 *======================================================================================*/

/**
 * Transaction object
 *
 * A transaction is a collection of operations belonging to a transaction, this class can be
 * used to track and document operations such as a data record validation and they represent
 * the base of logging in this system.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_SESSION}</tt>: <em>Session</em>. This required property is a
 *		reference to the session to which the current transaction belongs.
 *	<li><tt>{@link kTAG_USER}</tt>: <em>Transaction user</em>. The object reference for the
 *		user that triggered the transaction, it will be generally passed by the session.
 *	<li><tt>{@link kTAG_TRANSACTION}</tt>: <em>Transaction</em>. This optional property is a
 *		reference to the parent transaction.
 *	<li><tt>{@link kTAG_TRANSACTION_TYPE}</tt>: <em>Transaction type</em>. This required
 *		enumerated value indicates the type, function or scope of the transaction.
 *	<li><tt>{@link kTAG_TRANSACTION_START}</tt>: <em>Transaction start</em>. The starting
 *		time stamp of the transaction, it is generally set when inserted the first time.
 *	<li><tt>{@link kTAG_TRANSACTION_END}</tt>: <em>Transaction end</em>. The ending time
 *		stamp of the transaction, it is generally set by the transaction destructor.
 *	<li><tt>{@link kTAG_TRANSACTION_STATUS}</tt>: <em>Transaction status</em>. The result
 *		or outcome of the transaction.
 *	<li><tt>{@link kTAG_TRANSACTION_COLLECTION}</tt>: <em>Transaction colloection</em>. This
 *		property contains the alias of the collection to which the transaction belongs; this
 *		value is not related to database collections, rather to elements such as the
 *		worksheet of an Excel data template.
 *	<li><tt>{@link kTAG_TRANSACTION_RECORD}</tt>: <em>Transaction record</em>. This property
 *		contains a number referencing a record; in an Excel data template validation
 *		transaction, this refers to the row number.
 *	<li><tt>{@link kTAG_TRANSACTION_LOG}</tt>: <em>Transaction log</em>. This property is
 *		a container for all the operations of the transaction, for instance, in a data
 *		template validation transaction, the transaction records the status of the record
 *		validation while the transaction log records the status of the individual record
 *		field validations. This property features the following attributes:
 *	 <ul>
 *		<li><tt>{@link kTAG_TRANSACTION_ALIAS}</tt>: <em>Transaction alias</em>. The symbol,
 *			variable or identifier of the operation; in an Excel data template validation
 *			operation, this refers to the value in the column that identifies a specific
 *			data property, this would be used to determine the
 *			{@link kTAG_TRANSACTION_FIELD}.
 *		<li><tt>{@link kTAG_TRANSACTION_FIELD}</tt>: <em>Transaction field</em>. The field
 *			reference as an integer; in an Excel data template validation operation, this
 *			refers to the column number.
 *		<li><tt>{@link kTAG_TRANSACTION_VALUE}</tt>: <em>Transaction value</em>. The value
 *			involved in the operation; in an Excel data template validation operation, in
 *			the event of an errore, this property would hold the offending value.
 *		<li><tt>{@link kTAG_TAG}</tt>: <em>Transaction tag</em>. The reference to the Tag
 *			pbject associated with the field.
 *		<li><tt>{@link kTAG_TRANSACTION_STATUS}</tt>: <em>Transaction status</em>. The
 *			result or outcome of the operation.
 *		<li><tt>{@link kTAG_TRANSACTION_MESSAGE}</tt>: <em>Transaction message</em>. The
 *			eventual message returned by the operation.
 *	 </ul>
 * </ul>
 *
 * The typical workflow of a transaction is as follows:
 *
 * <ul>
 *	<li>The object is instantiated with the transaction reference and the transaction type.
 *	<li>The object is committed, so that a transaction identifier is generated.
 *	<li>The transaction log and operation counters will be updated by individual operations.
 *	<li>When all operations are finished, temporary resources are cleared.
 * </ul>.
 *
 * Because of this workflow, transactions can only be inseted and updating is prevented,
 * this behaviour is ensured by the parent class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 13/02/2015
 */
class Transaction extends SessionObject
{
	/**
	 * Default collection name.
	 *
	 * This constant provides the <i>default collection name</i> in which objects of this
	 * class are stored.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_transactions';

	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_TRANSACTION;

		

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
	 * In this class we link the inited status with the presence of the transaction, transaction
	 * type and status.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @see kTAG_SESSION kTAG_TRANSACTION_TYPE kTAG_TRANSACTION_STATUS
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
		$this->isInited( ( \ArrayObject::offsetExists( kTAG_SESSION ) ||
						   \ArrayObject::offsetExists( kTAG_TRANSACTION ) ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_STATUS ) );

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC REFERENCE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getParentTranaction																*
	 *==================================================================================*/

	/**
	 * Get parent transaction
	 *
	 * This method will return the referencing transaction if set and committed; if none of
	 * these two conditions are true, the method will return <tt>NULL</tt>.
	 *
	 * @access public
	 * @return Transaction			Referencing transaction or <tt>NULL</tt>.
	 *
	 * @see kTAG_TRANSACTION
	 *
	 * @uses resolvePersistent()
	 */
	public function getParentTranaction()
	{
		//
		// Check if committed.
		//
		if( $this->committed() )
		{
			//
			// Check if set.
			//
			$id = $this->resolvePersistent( TRUE )->offsetGet( kTAG_TRANSACTION );
			if( $id !== NULL )
				return
					static::ResolveCollection(
						static::ResolveDatabase( $this->mDictionary, TRUE ), TRUE )
							->matchOne( array( kTAG_NID => $id ),
										kQUERY_OBJECT | kQUERY_ASSERT );			// ==>
		
		} // Is committed.
		
		return NULL;																// ==>
	
	} // getParentTranaction.

	

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
	 *	<li><tt>{@link kTAG_TRANSACTION}</tt>: Referencing transaction.
	 *	<li><tt>{@link kTAG_TRANSACTION_END}</tt>: Transaction end.
	 *	<li><tt>{@link kTAG_TRANSACTION_LOG}</tt>: Transaction log.
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
				case kTAG_TRANSACTION:
				case kTAG_TRANSACTION_END:
				case kTAG_TRANSACTION_LOG:
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
	 *	<li><tt>{@link kTAG_TRANSACTION}</tt>: Referencing transaction.
	 *	<li><tt>{@link kTAG_TRANSACTION_STATUS}</tt>: Status.
	 *	<li><tt>{@link kTAG_TRANSACTION_END}</tt>: Transaction end.
	 *	<li><tt>{@link kTAG_TRANSACTION_LOG}</tt>: Transaction log.
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
				case kTAG_TRANSACTION:
				case kTAG_TRANSACTION_STATUS:
				case kTAG_TRANSACTION_END:
				case kTAG_TRANSACTION_LOG:
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
	 *	<li><tt>{@link kTAG_TRANSACTION}</tt>: Referencing transaction.
	 *	<li><tt>{@link kTAG_TRANSACTION_STATUS}</tt>: Status.
	 *	<li><tt>{@link kTAG_TRANSACTION_LOG}</tt>: Transaction log.
	 *	<li><tt>{@link kTAG_TRANSACTION_START}</tt>: Transaction start, we also intercept
	 *		<tt>TRUE</tt> for setting the current time stamp.
	 *	<li><tt>{@link kTAG_TRANSACTION_END}</tt>: Transaction end, we also intercept
	 *		<tt>TRUE</tt> for setting the current time stamp.
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
			case kTAG_TRANSACTION:
				//
				// Normalise value.
				//
				if( $theValue instanceof Transaction )
					$theValue = $theValue->offsetGet( kTAG_NID );
				
				//
				// Handle committed.
				//
				if( $this->committed() )
				{
					//
					// Get collection.
					//
					$collection
						= static::ResolveCollection(
							static::ResolveDatabase( $this->mDictionary, TRUE ),
							TRUE );
					//
					// Normalise identifier.
					//
					$tmp = $collection->getObjectId( $theValue );
					if( $tmp === NULL )
						throw new \Exception(
							"Cannot use identifier: "
						   ."invalid transaction identifier [$theValue]." );	// !@! ==>
					//
					// Update.
					//
					$collection
						->replaceOffsets(
							$this->offsetGet( kTAG_NID ),
							array( $theOffset => $tmp ) );
				}
				
				//
				// Handle uncommitted.
				//
				else
					parent::offsetSet( $theOffset, $theValue );
				
				break;
			
			case kTAG_TRANSACTION_STATUS:
				//
				// Check value.
				//
				switch( $theValue )
				{
					case kTYPE_STATUS_OK:
					case kTYPE_STATUS_EXECUTING:
					case kTYPE_STATUS_MESSAGE:
					case kTYPE_STATUS_WARNING:
					case kTYPE_STATUS_ERROR:
					case kTYPE_STATUS_FAILED:
					case kTYPE_STATUS_FATAL:
					case kTYPE_STATUS_EXCEPTION:
						break;
					
					default:
						throw new \Exception(
							"Cannot set transaction status: "
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
			
			case kTAG_TRANSACTION_END:
			case kTAG_TRANSACTION_START:
				//
				// Set current stamp.
				//
				if( $theValue === TRUE )
					$theValue
						= self::ResolveCollection(
							self::ResolveDatabase( $this->mDictionary, TRUE ) )
								->getTimeStamp();
			
			case kTAG_TRANSACTION_LOG:
				
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
	 *	<li><tt>{@link kTAG_TRANSACTION}</tt>: Referencing transaction.
	 *	<li><tt>{@link kTAG_TRANSACTION_STATUS}</tt>: Status.
	 *	<li><tt>{@link kTAG_TRANSACTION_LOG}</tt>: Transaction log.
	 *	<li><tt>{@link kTAG_TRANSACTION_END}</tt>: Transaction end, we also intercept
	 *		<tt>TRUE</tt> for setting the current time stamp.
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
				case kTAG_TRANSACTION:
				case kTAG_TRANSACTION_STATUS:
				case kTAG_TRANSACTION_LOG:
				case kTAG_TRANSACTION_END:
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
 *								PUBLIC LOG MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setLog																			*
	 *==================================================================================*/

	/**
	 * Set transaction log
	 *
	 * This method will add a transaction log record to the persistent transaction object.
	 *
	 * The method will also update the current and persistent transaction status.
	 *
	 * If the object is not committed, the method will raise an exception.
	 *
	 * @param string				$theStatus			Transaction status.
	 * @param string				$theAlias			Transaction alias.
	 * @param int					$theField			Transaction field.
	 * @param mixed					$theValue			Transaction value.
	 * @param string				$theMessage			Transaction message.
	 * @param string				$theTag				Transaction tag reference.
	 * @param string				$theErrorType		Error type.
	 * @param int					$theErrorCode		Error code.
	 * @param string				$theErrorResource	Error resource.
	 *
	 * @access public
	 *
	 * @see kTAG_TRANSACTION_LOG
	 */
	public function setLog( $theStatus, $theAlias = NULL, $theField = NULL,
										$theValue = NULL, $theMessage = NULL,
										$theTag = NULL, $theErrorType = NULL,
										$theErrorCode = NULL, $theErrorResource = NULL )
	{
		//
		// Handle committed object.
		//
		if( $this->committed() )
		{
			//
			// Init local storage.
			//
			$record = Array();
			$status = array( kTYPE_STATUS_EXECUTING => 0,
							 kTYPE_STATUS_OK => 1, kTYPE_STATUS_MESSAGE => 2,
							 kTYPE_STATUS_WARNING => 3, kTYPE_STATUS_ERROR => 4,
							 kTYPE_STATUS_FATAL => 5, kTYPE_STATUS_EXCEPTION => 6 );
		
			//
			// Handle status.
			//
			if( array_key_exists( $theStatus, $status ) )
			{
				//
				// Update status.
				//
				if( $status[ $theStatus ]
					> $status[ $this->offsetGet( kTAG_TRANSACTION_STATUS ) ] )
					$this->offsetSet( kTAG_TRANSACTION_STATUS, $theStatus );
				
				//
				// Normalise tag.
				//
				if( $theTag !== NULL )
					$theTag = $this->resolveOffset( $theTag, TRUE );
				
				//
				// Load record.
				//
				$record[ kTAG_TRANSACTION_STATUS ] = $theStatus;
				if( $theAlias !== NULL )
					$record[ kTAG_TRANSACTION_ALIAS ] = $theAlias;
				if( $theField !== NULL )
					$record[ kTAG_TRANSACTION_FIELD ] = $theField;
				if( $theValue !== NULL )
					$record[ kTAG_TRANSACTION_VALUE ] = $theValue;
				if( $theMessage !== NULL )
					$record[ kTAG_TRANSACTION_MESSAGE ] = $theMessage;
				if( $theTag !== NULL )
					$record[ kTAG_TAG ] = $theTag;
				if( $theErrorType !== NULL )
					$record[ kTAG_ERROR_TYPE ] = $theErrorType;
				if( $theErrorCode !== NULL )
					$record[ kTAG_ERROR_CODE ] = (int) $theErrorCode;
				if( $theErrorResource !== NULL )
					$record[ kTAG_ERROR_RESOURCE ] = (int) $theErrorResource;
				
				//
				// Update persistent object.
				//
				static::ResolveCollection(
					static::ResolveDatabase( $this->mDictionary, TRUE ) )
						->updateStructList(
							$this->offsetGet( kTAG_NID ),
							array( kTAG_TRANSACTION_LOG => $record ),
							TRUE );
		
			} // Valid status.
			
			else
				throw new \Exception(
					"Cannot set transaction log: "
				   ."invalid status [$theStatus]." );							// !@! ==>
		
		} // Object is committed.
		
		else
			throw new \Exception(
				"Cannot set transaction log: "
			   ."the object is not committed." );								// !@! ==>
	
	} // setLog.

	

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
	 * In this class we return the transaction type, start and status.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 *
	 * @see kTAG_TRANSACTION_TYPE kTAG_TRANSACTION_START kTAG_TRANSACTION_STATUS
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
		if( $this->offsetExists( kTAG_TRANSACTION_TYPE ) )
			$name[]
				= self::SelectLanguageString(
					Term::ResolveObject(
						$this->mDictionary,
						Term::kSEQ_NAME,
						$this->offsetGet( kTAG_TRANSACTION_TYPE ),
						TRUE )
							->offsetGet( kTAG_LABEL ),
					$theLanguage );
		
		//
		// Set start.
		//
		if( $this->offsetExists( kTAG_TRANSACTION_START ) )
			$name[]
				= date(
					'Y/m/d h:i:s',
					$this->offsetGet( kTAG_TRANSACTION_START )
						->sec )
				 .' '
				 . $this->offsetGet( kTAG_TRANSACTION_START )
				 	->usec;
		
		//
		// Set status.
		//
		if( $this->offsetExists( kTAG_TRANSACTION_STATUS ) )
			$name[]
				= '('
				 .self::SelectLanguageString(
					Term::ResolveObject(
						$this->mDictionary,
						Term::kSEQ_NAME,
						$this->offsetGet( kTAG_TRANSACTION_STATUS ),
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
		// Set related session index.
		//
		$collection->createIndex( array( kTAG_SESSION => 1 ),
								  array( "name" => "SESSION" ) );
		
		//
		// Set transaction index.
		//
		$collection->createIndex( array( kTAG_TRANSACTION => 1 ),
								  array( "name" => "TRANSACTION",
								  		 "sparse" => TRUE ) );
		
		//
		// Set transaction type index.
		//
		$collection->createIndex( array( kTAG_TRANSACTION_TYPE => 1 ),
								  array( "name" => "TYPE" ) );
		
		//
		// Set transaction status index.
		//
		$collection->createIndex( array( kTAG_TRANSACTION_STATUS => 1 ),
								  array( "name" => "STATUS" ) );
		
		//
		// Set collection index.
		//
		$collection->createIndex( array( kTAG_TRANSACTION_COLLECTION => 1 ),
								  array( "name" => "COLLECTION",
								  		 "sparse" => TRUE ) );
		
		//
		// Set record index.
		//
		$collection->createIndex( array( kTAG_TRANSACTION_RECORD => 1 ),
								  array( "name" => "RECORD",
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
	 *	<li><tt>{@link kTAG_TRANSACTION}</tt>: Parent transaction.
	 *	<li><tt>{@link kTAG_TRANSACTION_STATUS}</tt>: Transaction status.
	 *	<li><tt>{@link kTAG_TRANSACTION_END}</tt>: Transaction end.
	 *	<li><tt>{@link kTAG_TRANSACTION_LOG}</tt>: Transaction log.
	 *	<li><tt>{@link kTAG_TRANSACTION_ALIAS}</tt>: Transaction log alias.
	 *	<li><tt>{@link kTAG_TRANSACTION_FIELD}</tt>: Transaction log field.
	 *	<li><tt>{@link kTAG_TRANSACTION_VALUE}</tt>: Transaction log value.
	 *	<li><tt>{@link kTAG_TRANSACTION_MESSAGE}</tt>: Transaction log message.
	 *	<li><tt>{@link kTAG_ERROR_TYPE}</tt>: Error type.
	 *	<li><tt>{@link kTAG_TAG}</tt>: Transaction tag.
	 * </ul>
	 *
	 * @static
	 * @return array				List of unmanaged offsets.
	 *
	 * @see kTAG_TRANSACTION_STATUS kTAG_TRANSACTION_START kTAG_TRANSACTION_END
	 * @see kTAG_TRANSACTION_ALIAS kTAG_TRANSACTION_FIELD kTAG_TRANSACTION_VALUE
	 * @see kTAG_TRANSACTION_MESSAGE kTAG_TAG kTAG_TRANSACTION
	 */
	static function UnmanagedOffsets()
	{
		return array_merge(
			parent::UnmanagedOffsets(),
			array( kTAG_TRANSACTION,
				   kTAG_TRANSACTION_STATUS, kTAG_TRANSACTION_END,
				   kTAG_TRANSACTION_ALIAS, kTAG_TRANSACTION_FIELD, kTAG_TRANSACTION_VALUE,
				   kTAG_TRANSACTION_MESSAGE, kTAG_ERROR_TYPE, kTAG_TAG ) );			// ==>
	
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
									'schema::domain:transaction' ) );				// ==>
	
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
	 * In this class we link the inited status with the presence of the session, transaction
	 * type and status.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_SESSION kTAG_TRANSACTION_TYPE kTAG_TRANSACTION_STATUS
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
		$this->isInited( ( \ArrayObject::offsetExists( kTAG_SESSION ) ||
						   \ArrayObject::offsetExists( kTAG_TRANSACTION ) ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_STATUS ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of the session, transaction
	 * type and status.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_SESSION kTAG_TRANSACTION_TYPE kTAG_TRANSACTION_STATUS
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
		$this->isInited( ( \ArrayObject::offsetExists( kTAG_SESSION ) ||
						   \ArrayObject::offsetExists( kTAG_TRANSACTION ) ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_STATUS ) );
	
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
	 * In this class we initialise the transaction status and start time stamp.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @see kTAG_TRANSACTION_START kTAG_TRANSACTION_STATUS
	 *
	 * @uses start()
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Initialise transaction start.
		//
		if( ! $this->offsetExists( kTAG_TRANSACTION_START ) )
			$this->offsetSet( kTAG_TRANSACTION_START, TRUE );
		
		//
		// Initialise session status.
		//
		if( ! $this->offsetExists( kTAG_TRANSACTION_STATUS ) )
			$this->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_EXECUTING );
		
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
	 * In this class we overload this method to delete all related transactions and files.
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
			// Get transactions collection.
			//
			$collection
				= Transaction::ResolveCollection(
					Transaction::ResolveDatabase( $this->mDictionary, TRUE ) );
			
			//
			// Set criteria.
			//
			$criteria = array( '$or' => Array() );
			$criteria[ '$or' ][]
				= array( kTAG_TRANSACTION => $this->offsetGet( kTAG_NID ) );
			$criteria[ '$or' ][]
				= array( kTAG_TRANSACTIONS => $this->offsetGet( kTAG_NID ) );
		
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
			$criteria[ '$or' ][]
				= array( kTAG_TRANSACTION => $this->offsetGet( kTAG_NID ) );
			$criteria[ '$or' ][]
				= array( kTAG_TRANSACTIONS => $this->offsetGet( kTAG_NID ) );
		
			//
			// Delete related.
			//
			$list = $collection->matchAll( $criteria, kQUERY_OBJECT );
			foreach( $list as $element )
				$element->deleteObject();
		
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
	 * In this class, besides setting a self reference, we also set a reference to the user
	 * and the session.
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
		// Set session reference.
		//
		if( $this->offsetExists( kTAG_SESSION ) )
			$theObject->offsetSet(
				kTAG_SESSION,
				$this->offsetGet( kTAG_SESSION ) );
		
		//
		// Set sessions reference.
		//
		if( $this->offsetExists( kTAG_SESSIONS ) )
			$theObject->offsetSet(
				kTAG_SESSIONS,
				$this->offsetGet( kTAG_SESSIONS ) );
		
		//
		// Call parent method.
		//
		parent::copySelfReference( $theObject );
		
	} // copySelfReference.

	 

} // class Transaction.


?>
