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
		$this->isInited( \ArrayObject::offsetExists( kTAG_SESSION ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_TRANSACTION_STATUS ) );

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
	 * Manage transaction type
	 *
	 * This method can be used to set or retrieve the <i>transaction type</i>, it accepts a
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
	 * @see kTAG_TRANSACTION_TYPE
	 *
	 * @uses committed()
	 */
	public function type( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->offsetGet( kTAG_TRANSACTION_TYPE );						// ==>
		
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
				case kTYPE_TRANS_TMPL_ACQUISITION:
				case kTYPE_TRANS_TMPL_STORAGE:
				case kTYPE_TRANS_TMPL_PARSE:
				case kTYPE_TRANS_TMPL_WAREHOUSE:
				case kTYPE_TRANS_TMPL_WORKSHEETS:
				case kTYPE_TRANS_TMPL_PROPERTIES:
				case kTYPE_TRANS_TMPL_RECORDS:
				case kTYPE_TRANS_TMPL_CLEANUP:
				case kTYPE_TRANS_TMPL_CLOSE:
					$this->offsetSet( kTAG_TRANSACTION_TYPE, $theValue );
					return $theValue;												// ==>
			}
			
			throw new \Exception(
				"Cannot set transaction type: "
			   ."invalid enumeration [$theValue]." );							// !@! ==>
		
		} // Object not committed.
		
		throw new \Exception(
			"Cannot set transaction type: "
		   ."the object is committed." );										// !@! ==>
	
	} // type.

	 
	/*===================================================================================
	 *	start																			*
	 *==================================================================================*/

	/**
	 * Manage transaction start
	 *
	 * This method can be used to set or retrieve the <i>transaction start</i>, it accepts a
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
	 * @see kTAG_TRANSACTION_START
	 *
	 * @uses committed()
	 */
	public function start( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->offsetGet( kTAG_TRANSACTION_START );						// ==>
		
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
			$this->offsetSet( kTAG_TRANSACTION_START, $theValue );
			
			return $theValue;														// ==>
		
		} // Object not committed.
		
		throw new \Exception(
			"Cannot set transaction start: "
		   ."the object is committed." );										// !@! ==>
	
	} // start.

	 
	/*===================================================================================
	 *	end																				*
	 *==================================================================================*/

	/**
	 * Manage transaction end
	 *
	 * This method can be used to set or retrieve the <i>transaction end</i>, it accepts a
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
	 * @see kTAG_TRANSACTION_END
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
			
			return $this->handleOffset( kTAG_TRANSACTION_END, $theValue );			// ==>
		
		} // Not allowed to delete.
		
		throw new \Exception(
			"Cannot delete transaction end." );										// !@! ==>
	
	} // end.

	 
	/*===================================================================================
	 *	status																			*
	 *==================================================================================*/

	/**
	 * Manage transaction status
	 *
	 * This method can be used to set or retrieve the <i>transaction status</i>, it accepts a
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
	 * @see kTAG_TRANSACTION_STATUS
	 * @see kTYPE_STATUS_OK kTYPE_STATUS_EXECUTING
	 * @see kTYPE_STATUS_MESSAGE kTYPE_STATUS_WARNING
	 * @see kTYPE_STATUS_ERROR kTYPE_STATUS_FATAL kTYPE_STATUS_EXCEPTION
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
					case kTYPE_STATUS_EXECUTING:
					case kTYPE_STATUS_MESSAGE:
					case kTYPE_STATUS_WARNING:
					case kTYPE_STATUS_ERROR:
					case kTYPE_STATUS_FATAL:
					case kTYPE_STATUS_EXCEPTION:
						break;
					
					default:
						throw new \Exception(
							"Cannot set transaction status: "
						   ."invalid status type [$theValue]." );				// !@! ==>
				
				} // Parsed status.
			
			} // Provided status.
			
			return $this->handleOffset( kTAG_TRANSACTION_STATUS, $theValue );		// ==>
		
		} // Not allowed to delete.
		
		throw new \Exception(
			"Cannot delete transaction status." );								// !@! ==>
	
	} // status.

	 
	/*===================================================================================
	 *	collection																		*
	 *==================================================================================*/

	/**
	 * Manage transaction collection
	 *
	 * This method can be used to set or retrieve the <i>transaction collection</i>, it
	 * accepts a parameter which represents either the collection or the requested
	 * operation, depending on its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><em>other</em>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * If the object is committed and you attempt to set the value, the method will raise an
	 * exception.
	 *
	 * @param mixed					$theValue			New collection or operation.
	 *
	 * @access public
	 * @return mixed				Current collection.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_TRANSACTION_COLLECTION
	 *
	 * @uses committed()
	 */
	public function collection( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->offsetGet( kTAG_TRANSACTION_COLLECTION );					// ==>
		
		//
		// Go on if not committed.
		//
		if( ! $this->committed() )
		{
			//
			// Set offset.
			//
			$this->offsetSet( kTAG_TRANSACTION_COLLECTION, (string) $theValue );
			
			return $theValue;														// ==>
		
		} // Not committed.
		
		throw new \Exception(
			"Cannot set transaction collection: "
		   ."the object is committed." );										// !@! ==>
	
	} // collection.

	 
	/*===================================================================================
	 *	record																			*
	 *==================================================================================*/

	/**
	 * Manage transaction record
	 *
	 * This method can be used to set or retrieve the <i>transaction record number</i>, it
	 * accepts a parameter which represents either the record or the requested operation,
	 * depending on its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><em>other</em>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * If the object is committed and you attempt to set the value, the method will raise an
	 * exception.
	 *
	 * @param mixed					$theValue			New record number or operation.
	 *
	 * @access public
	 * @return mixed				Current record number.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_TRANSACTION_RECORD
	 *
	 * @uses committed()
	 */
	public function record( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->offsetGet( kTAG_TRANSACTION_RECORD );						// ==>
		
		//
		// Go on if not committed.
		//
		if( ! $this->committed() )
		{
			//
			// Set offset.
			//
			$this->offsetSet( kTAG_TRANSACTION_RECORD, (int) $theValue );
			
			return $theValue;														// ==>
		
		} // Not committed.
		
		throw new \Exception(
			"Cannot set transaction record: "
		   ."the object is committed." );										// !@! ==>
	
	} // record.

	

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
	 * @param string				$theMessage			Transaction message.
	 * @param string				$theAlias			Transaction alias.
	 * @param string				$theTag				Transaction tag reference.
	 * @param mixed					$theValue			Transaction value.
	 * @param int					$theField			Transaction field.
	 *
	 * @access public
	 *
	 * @see kTAG_TRANSACTION_LOG
	 */
	public function setLog( $theStatus, $theMessage = NULL,
										$theAlias = NULL, $theTag = NULL,
										$theValue = NULL, $theField = NULL )
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
					$this->status( $theStatus );
				
				//
				// Normalise tag.
				//
				$theTag = $this->resolveOffset( $theTag, TRUE );
				
				//
				// Load record.
				//
				$record[ kTAG_TRANSACTION_STATUS ] = $theStatus;
				if( $theMessage !== NULL )
					$record[ kTAG_TRANSACTION_MESSAGE ] = $theMessage;
				if( $theAlias !== NULL )
					$record[ kTAG_TRANSACTION_ALIAS ] = $theAlias;
				if( $theValue !== NULL )
					$record[ kTAG_TRANSACTION_VALUE ] = $theValue;
				if( $theField !== NULL )
					$record[ kTAG_TRANSACTION_FIELD ] = $theField;
				if( $theTag !== NULL )
					$record[ kTAG_TAG ] = $theTag;
				
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

	 
	/*===================================================================================
	 *	getLog																			*
	 *==================================================================================*/

	/**
	 * Get transaction log
	 *
	 * This method will return the current object's transaction log.
	 *
	 * @access public
	 * @return array				Transaction log.
	 */
	public function getLog()
	{
		//
		// Handle committed object.
		//
		if( $this->committed() )
		{
			//
			// Get logs.
			//
			$logs
				= $this->resolvePersistent( TRUE )
					->offsetGet( kTAG_TRANSACTION_LOG );
			
			//
			// Set logs.
			//
			$this->offsetSet( kTAG_TRANSACTION_LOG, $logs );
		
		} // Object is committed.
		
		return $this->offsetGet( kTAG_TRANSACTION_LOG );							// ==>
	
	} // getLog.

	

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

	 

} // class Transaction.


?>
