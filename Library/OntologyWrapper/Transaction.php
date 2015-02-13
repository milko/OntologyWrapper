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
 * Domains.
 *
 * This file contains the domain definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

/**
 * Transaction object
 *
 * A transaction is a collection of operations belonging to a session, this class can be
 * used to track and document operations such as a data record validation and they represent
 * the base of logging in this system.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. The native identifier of a
 *		transaction is automatically generated when inserted the first time, it can then be
 *		used to reference the object.
 *	<li><tt>{@link kTAG_SESSION}</tt>: <em>Session</em>. This required property is a
 *		reference to the session to which the current transaction belongs.
 *	<li><tt>{@link kTAG_TRANSACTION_TYPE}</tt>: <em>Transaction type</em>. This required
 *		enumerated value indicates the type, function or scope of the transaction.
 *	<li><tt>{@link kTAG_TRANSACTION_START}</tt>: <em>Transaction start</em>. The starting
 *		time stamp of the transaction, it is generally set when inserted the first time.
 *	<li><tt>{@link kTAG_TRANSACTION_END}</tt>: <em>Transaction end</em>. The ending time
 *		stamp of the transaction, it is generally set by the transaction destructor.
 *	<li><tt>{@link kTAG_TRANSACTION_STATUS}</tt>: <em>Transaction status</em>. The result
 *		or outcome of the transaction.
 *	<li><tt>{@link kTAG_PROCESSED}</tt>: <em>Processed elements</em>. The number of elements
 *		processed by the transaction, this will typically be the operations count relating
 *		to this transaction.
 *	<li><tt>{@link kTAG_VALIDATED}</tt>: <em>Validated elements</em>. The number of elements
 *		validated by the transaction, this will typically be the operations count that
 *		were cleared by the validation process.
 *	<li><tt>{@link kTAG_REJECTED}</tt>: <em>Rejected elements</em>. The number of elements
 *		rejected by the transaction, this will typically be the operations count that were
 *		not cleared by the validation process.
 *	<li><tt>{@link kTAG_SKIPPED}</tt>: <em>Skipped elements</em>. The number of elements
 *		skipped by the transaction, this will typically be the operations count that were
 *		skipped by the validation process; such as empty fields.
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
 * The typical workflow of a session is as follows:
 *
 * <ul>
 *	<li>The object is instantiated with the session reference and the transaction type.
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
	 * In this class we link the inited status with the presence of the session, transaction
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_SESSION ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_STATUS ) );

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
 *								STATIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	SetStatus																		*
	 *==================================================================================*/

	/**
	 * Set the status
	 *
	 * This method can be used to set the transaction status of the object identified by
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
			case kTYPE_STATUS_EXECUTING:
			case kTYPE_STATUS_OK:
			case kTYPE_STATUS_MESSAGE:
			case kTYPE_STATUS_WARNING:
			case kTYPE_STATUS_ERROR:
			case kTYPE_STATUS_FATAL:
			case kTYPE_STATUS_EXCEPTION:
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
			= Transaction::ResolveCollection(
				Transaction::ResolveDatabase( $theWrapper, TRUE ) );
	
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
		// Set related session index.
		//
		$collection->createIndex( array( kTAG_SESSION => 1 ),
								  array( "name" => "SESSION" ) );
		
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
	 *	<li><tt>{@link kTAG_TRANSACTION_STATUS}</tt>: Transaction status.
	 *	<li><tt>{@link kTAG_TRANSACTION_START}</tt>: Transaction start.
	 *	<li><tt>{@link kTAG_TRANSACTION_END}</tt>: Transaction end.
	 *	<li><tt>{@link kTAG_TRANSACTION_ALIAS}</tt>: Transaction alias.
	 *	<li><tt>{@link kTAG_TRANSACTION_FIELD}</tt>: Transaction field.
	 *	<li><tt>{@link kTAG_TRANSACTION_VALUE}</tt>: Transaction value.
	 *	<li><tt>{@link kTAG_TRANSACTION_MESSAGE}</tt>: Transaction message.
	 *	<li><tt>{@link kTAG_TAG}</tt>: Transaction tag.
	 * </ul>
	 *
	 * @static
	 * @return array				List of unmanaged offsets.
	 *
	 * @see kTAG_TRANSACTION_STATUS kTAG_TRANSACTION_START kTAG_TRANSACTION_END
	 * @see kTAG_TRANSACTION_ALIAS kTAG_TRANSACTION_FIELD kTAG_TRANSACTION_VALUE
	 * @see kTAG_TRANSACTION_MESSAGE kTAG_TAG
	 */
	static function UnmanagedOffsets()
	{
		return array_merge(
			parent::UnmanagedOffsets(),
			array( kTAG_TRANSACTION_STATUS,
				   kTAG_TRANSACTION_START, kTAG_TRANSACTION_END,
				   kTAG_TRANSACTION_ALIAS, kTAG_TRANSACTION_FIELD, kTAG_TRANSACTION_VALUE,
				   kTAG_TRANSACTION_MESSAGE, kTAG_TAG ) );							// ==>
	
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_SESSION ) &&
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_SESSION ) &&
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
	 * In this class we initialise the transaction start and status properties.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kTAG_TRANSACTION_START kTAG_TRANSACTION_STATUS
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Initialise transaction start.
		//
		if( ! $this->offsetExists( kTAG_TRANSACTION_START ) )
			$this->offsetSet( kTAG_TRANSACTION_START, new \MongoTimestamp() );
		
		//
		// Initialise transaction status.
		//
		if( ! $this->offsetExists( kTAG_TRANSACTION_STATUS ) )
			$this->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_EXECUTING );
		
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
		
	} // preCommitPrepare.

	 

} // class Transaction.


?>
