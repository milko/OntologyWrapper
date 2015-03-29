<?php

/**
 * SessionUpdate.php
 *
 * This file contains the definition of the {@link SessionUpdate} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Session;
use OntologyWrapper\SessionBatch;

/*=======================================================================================
 *																						*
 *									SessionUpdate.php									*
 *																						*
 *======================================================================================*/

/**
 * Update session
 *
 * This class implements an update session object, it gets instantiated by providing the
 * related update session, the class implements the workflow needed to execute a data
 * update from the upload session.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 23/02/2015
 */
class SessionUpdate extends SessionBatch
{
	/**
	 * Property accessors trait.
	 *
	 * We use this trait to provide a common framework for methods that manage properties.
	 */
	use	traits\AccessorProperty;

	/**
	 * Session.
	 *
	 * This data member holds the <i>session object</i>.
	 *
	 * @var Session
	 */
	protected $mSession = NULL;

	/**
	 * Upload.
	 *
	 * This data member holds the <i>upload session object</i>.
	 *
	 * @var Session
	 */
	protected $Upload = NULL;

	/**
	 * Transaction.
	 *
	 * This data member holds the <i>current transaction object</i>.
	 *
	 * @var Transaction
	 */
	protected $mTransaction = NULL;

		

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
	 * This class is instantiated by providing an upload session and the upload template
	 * file reference.
	 *
	 * @param Session				$theSession			Update session object.
	 *
	 * @access public
	 */
	public function __construct( Session $theSession )
	{
		//
		// Call parent constructor.
		//
		parent::__construct( $theSession->offsetGet( kTAG_USER ) );
		
		//
		// Set session.
		//
		$this->session( $theSession );
		
	} // Constructor.

	 
	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * Destruct class.
	 *
	 * In this class we delete the template file when destructing the object.
	 *
	 * @access public
	 */
	public function __destruct()
	{
		//
		// Close open session.
		//
		if( $this->session()->offsetGet( kTAG_SESSION_STATUS ) == kTYPE_STATUS_EXECUTING )
		{
			$this->session()->offsetSet( kTAG_SESSION_STATUS, kTYPE_STATUS_EXCEPTION );
			$this->session()->offsetSet( kTAG_SESSION_END, TRUE );
		}
		
		//
		// Call parent constructor.
		//
		parent::__destruct();

	} // Destructor.

	

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBERS ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	wrapper																			*
	 *==================================================================================*/

	/**
	 * Get wrapper
	 *
	 * This method can be used to retrieve the <i>data wrapper</i>, the method will use the
	 * wrapper of the session member.
	 *
	 * @access public
	 * @return Wrapper				Data wrapper.
	 *
	 * @uses session()
	 */
	public function wrapper()					{	return $this->session()->dictionary();	}

	 
	/*===================================================================================
	 *	session																			*
	 *==================================================================================*/

	/**
	 * Manage session
	 *
	 * This method can be used to set or retrieve the <i>session</i>, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theValue</tt>: The property value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current property value.
	 *		<li><tt>Session</tt>: Set the value in the property.
	 *		<li><tt>string</tt>: Resolve session and set the value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value of the property <em>before</em> it was
	 *			eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value of the property <em>after</em> it was
	 *			eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * It is not allowed to delete sessions, this means that providing <tt>FALSE</tt> will
	 * raise an exception.
	 *
	 * @param mixed					$theValue			New session or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				Current or old session.
	 *
	 * @uses wrapper()
	 * @uses manageProperty()
	 */
	public function session( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Prevent deleting.
		//
		if( $theValue === FALSE )
			throw new \Exception(
				"You cannot delete the current session." );						// !@! ==>
		
		//
		// Check new value.
		//
		if( ($theValue !== NULL)
		 && (! ($theValue instanceof Session)) )
			$theValue = Session::ResolveObject(
							$this->wrapper(),
							Session::kSEQ_NAME,
							(string) $theValue,
							TRUE );
		
		return $this->manageProperty( $this->mSession, $theValue, $getOld );		// ==>
	
	} // session.

	 
	/*===================================================================================
	 *	upload																			*
	 *==================================================================================*/

	/**
	 * Manage upload session
	 *
	 * This method can be used to set or retrieve the <i>upload session</i>, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theValue</tt>: The property value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current property value.
	 *		<li><tt>Session</tt>: Set the value in the property.
	 *		<li><tt>string</tt>: Resolve session and set the value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value of the property <em>before</em> it was
	 *			eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value of the property <em>after</em> it was
	 *			eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * It is not allowed to delete sessions, this means that providing <tt>FALSE</tt> will
	 * raise an exception.
	 *
	 * @param mixed					$theValue			New session or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				Current or old session.
	 */
	public function upload( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Prevent deleting.
		//
		if( $theValue === FALSE )
			throw new \Exception(
				"You cannot delete the current upload session." );					// !@! ==>
		
		//
		// Check new value.
		//
		if( ($theValue !== NULL)
		 && (! ($theValue instanceof Session)) )
			$theValue = Session::ResolveObject(
							$this->wrapper(),
							Session::kSEQ_NAME,
							(string) $theValue,
							TRUE );
		
		return $this->manageProperty( $this->mUpload, $theValue, $getOld );			// ==>
	
	} // upload.

	 
	/*===================================================================================
	 *	transaction																		*
	 *==================================================================================*/

	/**
	 * Manage transaction
	 *
	 * This method can be used to set or retrieve the <i>current transaction</i>, the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theValue</tt>: The property value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current property value.
	 *		<li><tt>Transaction</tt>: Set the value in the property.
	 *		<li><tt>string</tt>: Resolve transaction and set the value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value of the property <em>before</em> it was
	 *			eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value of the property <em>after</em> it was
	 *			eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * It is not allowed to delete transactions, this means that providing <tt>FALSE</tt>
	 * will raise an exception.
	 *
	 * @param mixed					$theValue			New transaction or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				Current or old transaction.
	 *
	 * @uses wrapper()
	 * @uses manageProperty()
	 */
	public function transaction( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Prevent deleting.
		//
		if( $theValue === FALSE )
			throw new \Exception(
				"You cannot delete the current transaction." );					// !@! ==>
		
		//
		// Check new value.
		//
		if( ($theValue !== NULL)
		 && (! ($theValue instanceof Transaction)) )
			$theValue = Transaction::ResolveObject(
							$this->wrapper(),
							Transaction::kSEQ_NAME,
							(string) $theValue,
							TRUE );
		
		return $this->manageProperty( $this->mTransaction, $theValue, $getOld );	// ==>
	
	} // transaction.

	

/*=======================================================================================
 *																						*
 *								PUBLIC SESSION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	execute																			*
	 *==================================================================================*/

	/**
	 * Execute session
	 *
	 * This method will execute the current session.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	public function execute()
	{
		//
		// Init local storage.
		//
		$log = kPATH_BATCHES_ROOT
			  ."/log/"
			  .(string) $this->session()->offsetGet( kTAG_NID )
			  .".log";

		//
		// Write to log file.
		//
		if( kDEBUG_FLAG )
			file_put_contents(
				$log,
				"Batch execution start: ".date( "r" )."\n",
				FILE_APPEND );
		
		//
		// TRY BLOCK.
		//
		try
		{
			//
			// Set maximum execution time.
			//
			$max_exec_time = ini_set( 'max_execution_time', 0 );
			
			//
			// Initialise progress.
			//
			$this->session()->offsetSet( kTAG_COUNTER_PROGRESS, 0 );

			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionPrepare()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction prepare.
			//
			if( ! $this->sessionPrepare() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 10 );

			//
			// Reset session records counters.
			//
			$this->session()->offsetSet( kTAG_COUNTER_PROCESSED, 0 );
			$this->session()->offsetSet( kTAG_COUNTER_VALIDATED, 0 );
			$this->session()->offsetSet( kTAG_COUNTER_REJECTED, 0 );
		
			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionUpdate()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction update.
			//
			if( ! $this->sessionUpdate() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 80 );
		
			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionCleanup()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction update.
			//
			if( ! $this->sessionCleanup() )
				return $this->failSession();										// ==>
			
			//
			// Finalise progress.
			//
			$this->session()->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
			
			//
			// Reset maximum execution time.
			//
			ini_set( 'max_execution_time', $max_exec_time );
			
			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"Batch execution end: ".date( "r" )."\n",
					FILE_APPEND );
	
			return $this->succeedSession();											// ==>
		}
		
		//
		// CATCH BLOCK.
		//
		catch( \Exception $error )
		{
			return $this->exceptionSession( $error );								// ==>
		}
		
	} // execute.

	

/*=======================================================================================
 *																						*
 *							PROTECTED TRANSACTIONS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	sessionPrepare																	*
	 *==================================================================================*/

	/**
	 * Prepare session
	 *
	 * This method will load the upload transaction.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 * @uses checkFileReference()
	 * @uses loadUploadInformation()
	 */
	protected function sessionPrepare()
	{
		//
		// Instantiate transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_UPDT_PREPARE ) );
		
		//
		// Prepare session.
		//
		if( ! $this->loadUploadInformation() )
			return FALSE;															// ==>
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionPrepare.

	 
	/*===================================================================================
	 *	sessionUpdate																	*
	 *==================================================================================*/

	/**
	 * Update database
	 *
	 * This method will load the template data into the public database.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 */
	protected function sessionUpdate()
	{
		//
		// Instantiate transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_UPDT_DATA ) );
		
		//
		// Select all template objects.
		//
		$records
			= Session::ResolveDatabase( $this->wrapper(), TRUE )
				->collection( $this->getCollectionName( UnitObject::kSEQ_NAME ), TRUE )
					->matchAll( Array(), kQUERY_ARRAY );
		
		//
		// Load template data.
		//
		if( ! $this->loadTemplateData( $records ) )
			return FALSE;															// ==>
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionUpdate.

	 
	/*===================================================================================
	 *	sessionCleanup																	*
	 *==================================================================================*/

	/**
	 * Cleanup session
	 *
	 * This method will drop the working collections if at least one object was committed.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	protected function sessionCleanup()
	{
		//
		// Instantiate transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_UPDT_CLEAN ) );
		
		//
		// Check if there is at least one validated.
		//
		if( $this->upload()->offsetGet( kTAG_COUNTER_VALIDATED ) )
		{
			//
			// Iterate collections.
			//
			foreach( $this->upload()->offsetGet( kTAG_CONN_COLLS ) as $collection )
				Session::ResolveDatabase( $this->wrapper(), TRUE )
					->collection( $collection, TRUE )
						->drop();
		}
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionCleanup.

	

/*=======================================================================================
 *																						*
 *							PROTECTED OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadUploadInformation															*
	 *==================================================================================*/

	/**
	 * Load upload session information
	 *
	 * This method will load the class name, working collections and template file reference
	 * from the upload session to the current session.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses wrapper()
	 * @uses session()
	 * @uses transaction()
	 */
	protected function loadUploadInformation()
	{
		//
		// Locate upload session.
		//
		$upload
			= Session::ResolveCollection(
				Session::ResolveDatabase( $this->wrapper(), TRUE ), TRUE )
					->matchOne(
						array( kTAG_SESSION => $this->session()->offsetGet( kTAG_NID ),
							   kTAG_USER => $this->session()->offsetGet( kTAG_USER ) ),
						kQUERY_OBJECT );
		
		//
		// Copy relevant information.
		//
		if( $upload !== NULL )
		{
			//
			// Set data member.
			//
			$this->upload( $upload );
			
			//
			// Load class name and working collection names.
			//
			$this->session()->offsetSet(
				kTAG_CLASS_NAME, $upload->offsetGet( kTAG_CLASS_NAME ) );
			$this->session()->offsetSet(
				kTAG_CONN_COLLS, $upload->offsetGet( kTAG_CONN_COLLS ) );
			
			//
			// Load template file object.
			//
			$this->session()->offsetSet( kTAG_FILE, $upload->offsetGet( kTAG_FILE ) );
			
			//
			// Remove file reference from upload session.
			//
			$upload->offsetUnset( kTAG_FILE );
			
			return TRUE;															// ==>
		
		} // Found upload session.
		
		//
		// Post error.
		//
		$transaction = $this->transaction();
		$message = "Unable to locate upload session.";
		$this->failTransactionLog(
			$transaction,								// Transaction.
			NULL,										// Parent transaction.
			kTYPE_TRANS_UPDT_PREPARE,					// Transaction type.
			kTYPE_STATUS_FATAL,							// Transaction status.
			$message,									// Transaction message.
			NULL,										// Worksheet.
			NULL,										// Row.
			NULL,										// Column.
			NULL,										// Alias.
			NULL,										// Tag.
			NULL,										// Value.
			kTYPE_ERROR_MISSING_REQUIRED,				// Error type.
			kTYPE_ERROR_CODE_NO_UPLOAD,					// Error code.
			NULL										// Error resource.
		);
		
		return FALSE;																// ==>

	} // loadUploadInformation.

	 
	/*===================================================================================
	 *	loadTemplateData																*
	 *==================================================================================*/

	/**
	 * Assert required worksheets
	 *
	 * This method will check whether all required worksheets are there.
	 *
	 * @param ObjectIterator		$theRecords			Records iterator.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses transaction()
	 */
	protected function loadTemplateData( ObjectIterator $theRecords )
	{
		//
		// Init local storage.
		//
		$count = $theRecords->count();
		$class = $this->upload()->offsetGet( kTAG_CLASS_NAME );
		
		//
		// Initialise session counters.
		//
		$this->session()->offsetSet( kTAG_COUNTER_RECORDS, $count );
		UpdateProcessCounter( $start_processed_session, $increment_processed_session,
							  kTAG_COUNTER_PROCESSED );
		UpdateProcessCounter( $start_validated_session, $increment_validated_session,
							  kTAG_COUNTER_VALIDATED );
		UpdateProcessCounter( $start_rejected_session, $increment_rejected_session,
							  kTAG_COUNTER_REJECTED );
	
		//
		// Initialise transaction counters.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_RECORDS, $count );
		UpdateProcessCounter( $start_processed, $increment_processed,
							  kTAG_COUNTER_PROCESSED );
		UpdateProcessCounter( $start_validated, $increment_validated,
							  kTAG_COUNTER_VALIDATED );
		UpdateProcessCounter( $start_rejected, $increment_rejected,
							  kTAG_COUNTER_REJECTED );

		//
		// Reset session records counters.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_PROCESSED, 0 );
		$this->transaction()->offsetSet( kTAG_COUNTER_VALIDATED, 0 );
		$this->transaction()->offsetSet( kTAG_COUNTER_REJECTED, 0 );
		
		//
		// Iterate records.
		//
		foreach( $theRecords as $record )
		{
			//
			// Init local storage.
			//
			$transaction = NULL;
		
			//
			// Object commit TRY block..
			//
			try
			{
				//
				// Replace object.
				//
				if( UnitObject::ResolveCollection(
						UnitObject::ResolveDatabase( $this->wrapper(), TRUE ), TRUE )
							->matchOne( array( kTAG_NID => $record[ kTAG_NID ] ),
										kQUERY_COUNT ) )
				{
					//
					// Remove row indicator.
					//
					if( array_key_exists( kTAG_ROW, $record ) )
						unset( $record[ kTAG_ROW ] );
					
					//
					// Instantiate object.
					//
					$object = new $class( $this->wrapper(), $record );
				
				} // Replace object.
				
				//
				// Insert object.
				//
				else
				{
					//
					// Instantiate object.
					//
					$object = new $class( $this->wrapper() );
			
					//
					// Load properties.
					//
					foreach( $record as $key => $value )
					{
						//
						// Intercept row number.
						//
						if( $key == kTAG_ROW )
							continue;											// =>
				
						//
						// Set object property.
						//
						$object[ $key ] = $value;
			
					} // Iterating record properties.
				
				} // Insert object.
			
				//
				// Set user and session references.
				//
				$object->offsetSet(
					kTAG_USER,
					$this->session()
						->offsetGet( kTAG_USER ) );
				$object->offsetSet(
					kTAG_SESSION_START,
					$this->session()
						->offsetGet( kTAG_SESSION_START ) );
				
				//
				// Commit object.
				//
				$object->commit();
				
				//
				// Update session validated.
				//
				$increment_validated_session++;
				UpdateProcessCounter(
					$start_validated_session, $increment_validated_session,
					kTAG_COUNTER_VALIDATED,
					$this->session() );
				
				//
				// Update transaction validated.
				//
				$increment_validated++;
				UpdateProcessCounter(
					$start_validated, $increment_validated,
					kTAG_COUNTER_VALIDATED,
					$this->transaction() );
			
			} // Object commit TRY block.
		
			//
			// Catch class validation exceptions.
			//
			catch( \Exception $error )
			{
				//
				// Post error.
				//
				$this->failTransactionLog(
					$transaction,								// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_UPDT_DATA_OBJECT,				// Transaction type.
					kTYPE_STATUS_EXCEPTION,						// Transaction status.
					$error->getMessage(),						// Transaction message.
					NULL,										// Worksheet.
					$record[ kTAG_NID ],						// Row.
					NULL,										// Column.
					NULL,										// Alias.
					NULL,										// Tag.
					NULL,										// Value.
					kTYPE_ERROR_BUG,							// Error type.
					kTYPE_ERROR_CODE_OBJECT_VALIDATION,			// Error code.
					NULL										// Error resource.
				);
			
				//
				// Update session rejected.
				//
				$increment_rejected_session++;
				UpdateProcessCounter(
					$start_rejected_session, $increment_rejected_session,
					kTAG_COUNTER_REJECTED,
					$this->session() );
			
				//
				// Update transaction rejected.
				//
				$increment_rejected++;
				UpdateProcessCounter(
					$start_rejected, $increment_rejected,
					kTAG_COUNTER_REJECTED,
					$this->transaction() );
			
			} // Object commit CATCH block.
	
			//
			// Close transaction.
			//
			if( $transaction!== NULL )
				$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			
			//
			// Update session processed.
			//
			$increment_processed_session++;
			UpdateProcessCounter( $start_processed_session, $increment_processed_session,
								  kTAG_COUNTER_PROCESSED,
								  $this->session() );
			
			//
			// Update transaction processed.
			//
			$increment_processed++;
			UpdateProcessCounter( $start_processed, $increment_processed,
								  kTAG_COUNTER_PROCESSED,
								  $this->transaction(),
								  $count );
		
		} // Iterating records.
		
		//
		// Update session processed.
		//
		UpdateProcessCounter(
			$start_processed_session, $increment_processed_session,
			kTAG_COUNTER_PROCESSED,
			$this->session(), NULL, TRUE);
		
		//
		// Update transaction processed.
		//
		UpdateProcessCounter(
			$start_processed, $increment_processed,
			kTAG_COUNTER_PROCESSED,
			$this->transaction(), NULL, TRUE );
		
		//
		// Update session validated.
		//
		UpdateProcessCounter(
			$start_VALIDATED_session, $increment_validated_session,
			kTAG_COUNTER_VALIDATED,
			$this->session(), NULL, TRUE);
		
		//
		// Update transaction validated.
		//
		UpdateProcessCounter(
			$start_validated, $increment_validated,
			kTAG_COUNTER_VALIDATED,
			$this->transaction(), NULL, TRUE );
		
		//
		// Update session rejected.
		//
		UpdateProcessCounter(
			$start_rejected_session, $increment_rejected_session,
			kTAG_COUNTER_REJECTED,
			$this->session(), NULL, TRUE );
		
		//
		// Update transaction rejected.
		//
		UpdateProcessCounter(
			$start_rejected, $increment_rejected,
			kTAG_COUNTER_REJECTED,
			$this->transaction(), NULL, TRUE );
		
		return TRUE;																// ==>

	} // loadTemplateData.

	

/*=======================================================================================
 *																						*
 *								PROTECTED OPERATIONS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	succeedSession																	*
	 *==================================================================================*/

	/**
	 * Succeed session
	 *
	 * This method can be used to set the session data if completed, it will set the
	 * progress to 100, set the ending time and set the OK status.
	 *
	 * @access public
	 * @return boolean				Returns <tt>TRUE</tt>.
	 *
	 * @uses session()
	 */
	public function succeedSession()
	{
		//
		// Init local storage.
		//
		$session = $this->session();
		
		//
		// Close session.
		//
		$session->offsetSet( kTAG_SESSION_STATUS, kTYPE_STATUS_OK );
		$session->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$session->offsetSet( kTAG_SESSION_END, TRUE );
		
		return TRUE;																// ==>

	} // succeedSession.

	 
	/*===================================================================================
	 *	failSession																		*
	 *==================================================================================*/

	/**
	 * Fail session
	 *
	 * This method can be used to set the session data in the case of an
	 * <em>intercepted</em> error, it will set the progress to 100, set the ending time and
	 * set the failed status.
	 *
	 * @access public
	 * @return boolean				Returns <tt>FALSE</tt>.
	 *
	 * @uses session()
	 */
	public function failSession()
	{
		//
		// Init local storage.
		//
		$session = $this->session();
		
		//
		// Close session.
		//
		$session->offsetSet( kTAG_SESSION_END, TRUE );
		$session->offsetSet( kTAG_SESSION_STATUS, kTYPE_STATUS_FAILED );
		
		return FALSE;																// ==>

	} // failSession.

	 
	/*===================================================================================
	 *	failTransaction																	*
	 *==================================================================================*/

	/**
	 * Fail transaction
	 *
	 * This method can be used to set the transaction data in the case of an
	 * <em>intercepted</em> error, it will set the ending time, the failed status and set
	 * both properties for all parent transactions.
	 *
	 * @param string				$theStatus			Status code.
	 *
	 * @access public
	 * @return boolean				Returns <tt>FALSE</tt>.
	 *
	 * @uses session()
	 */
	public function failTransaction( $theStatus = kTYPE_STATUS_FAILED )
	{
		//
		// Init local storage.
		//
		$transaction = $this->transaction();
		
		//
		// Traverse parentship.
		//
		while( $transaction !== NULL )
		{
			//
			// Close session.
			//
			$transaction->offsetSet( kTAG_TRANSACTION_STATUS, $theStatus );
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			
			//
			// Get parent.
			//
			$transaction = $transaction->getParentTranaction();
		}
		
		return FALSE;																// ==>

	} // failTransaction.

	 
	/*===================================================================================
	 *	failTransactionLog																*
	 *==================================================================================*/

	/**
	 * Fail transaction log
	 *
	 * This method can be used to set the transaction log in the case of an
	 * <em>intercepted</em> error, it will set the ending time, the failed status and the
	 * log entry.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param Transaction			$theParent			Parent transaction.
	 * @param string				$theType			Transaction type.
	 * @param string				$theStatus			Transaction status.
	 * @param string				$theMessage			Message.
	 * @param string				$theFieldWorksheet	Worksheet name.
	 * @param int					$theFieldRow		Row number.
	 * @param string				$theFieldColumn		Column.
	 * @param string				$theFieldAlias		Symbol.
	 * @param Tag					$theFieldTag		Field tag.
	 * @param mixed					$theFieldData		Value.
	 * @param string				$theErrorType		Error type.
	 * @param int					$theErrorCode		Error code.
	 * @param int					$theErrorResource	Error resource.
	 *
	 * @access public
	 * @return boolean				Returns <tt>FALSE</tt>.
	 *
	 * @uses session()
	 */
	protected function failTransactionLog( &$theTransaction,
											$theParent,
											$theType,
											$theStatus,
											$theMessage,
											$theFieldWorksheet,
											$theFieldRow,
											$theFieldColumn,
											$theFieldAlias,
											$theFieldTag,
											$theFieldData,
											$theErrorType,
											$theErrorCode,
											$theErrorResource )
	{
		//
		// New transaction.
		//
		if( $theTransaction === NULL )
		{
			//
			// Create transaction.
			//
			$theTransaction
				= $theParent
					->newTransaction(
						$theType, $theStatus, $theFieldWorksheet, $theFieldRow );
	
		} // New transaction.
	
		//
		// Set log.
		//
		$theTransaction
			->setLog(
				$theStatus,				// Status,
				$theFieldAlias,			// Alias.
				$theFieldColumn,		// Field.
				$theFieldData,			// Value.
				$theMessage,			// Message.
				$theFieldTag,			// Tag.
				$theErrorType,			// Error type.
				$theErrorCode,			// Error code.
				$theErrorResource );	// Error resource.
		
		return FALSE;																// ==>

	} // failTransactionLog.

	 
	/*===================================================================================
	 *	exceptionSession																*
	 *==================================================================================*/

	/**
	 * Exception session
	 *
	 * This method can be used to set the session data in the case of an exception, it will
	 * set the error information, the progress to 100, set the ending time and set the failed
	 * status.
	 *
	 * @param Exception				$theError			Exception.
	 *
	 * @access public
	 * @return boolean				Returns <tt>FALSE</tt>.
	 *
	 * @uses session()
	 * @uses failSession()
	 */
	public function exceptionSession( \Exception $theError )
	{
		//
		// Set error info.
		//
		$this->session()->offsetSet( kTAG_ERROR_TYPE, 'Exception' );
		if( $theError->getCode() )
			$this->session()->offsetSet( kTAG_ERROR_CODE, $theError->getCode() );
		$this->session()->offsetSet( kTAG_TRANSACTION_MESSAGE, $theError->getMessage() );
		
		return $this->failSession();												// ==>

	} // exceptionSession.

	

/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getCollectionName																*
	 *==================================================================================*/

	/**
	 * Compile a collection name
	 *
	 * This method can be used to compile collection names according to the current user,
	 * the method expects a suffix and will return the full collection name.
	 *
	 * @param string				$theSuffix			Collection name suffix.
	 *
	 * @access public
	 * @return string				Collection name.
	 *
	 * @uses session()
	 */
	public function getCollectionName( $theSuffix )
	{
		//
		// Get current session user.
		//
		$user = new User( $this->wrapper(), $this->session()->offsetGet( kTAG_USER ) );
		
		return $user->offsetGet( kTAG_ID_SEQUENCE )."_$theSuffix";					// ==>

	} // getCollectionName.

	 

} // class SessionUpdate.


?>
