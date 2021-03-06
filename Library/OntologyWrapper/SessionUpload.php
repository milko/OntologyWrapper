<?php

/**
 * SessionUpload.php
 *
 * This file contains the definition of the {@link SessionUpload} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Session;
use OntologyWrapper\SessionBatch;

/*=======================================================================================
 *																						*
 *									SessionUpload.php									*
 *																						*
 *======================================================================================*/

/**
 * Excel parser.
 *
 * This file contains the default Excel library definitions.
 */
require_once( kPATH_LIBRARY_EXCEL."/PHPExcel.php" );

/**
 * Upload session
 *
 * This class implements an upload session object, it gets instantiated by providing the
 * related session and the upload template file path, the class implements the workflow
 * needed to execute a data template upload.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/02/2015
 */
class SessionUpload extends SessionBatch
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
	 * Transaction.
	 *
	 * This data member holds the <i>current transaction object</i>.
	 *
	 * @var Transaction
	 */
	protected $mTransaction = NULL;

	/**
	 * File.
	 *
	 * This data member holds the <i>template file</i> reference.
	 *
	 * @var SplFileInfo
	 */
	protected $mFile = NULL;

	/**
	 * Template parser.
	 *
	 * This data member holds the template parser.
	 *
	 * @var ExcelTemplateParser
	 */
	protected $mParser = NULL;

	/**
	 * Template worksheets iterator.
	 *
	 * This data member holds the template worksheets iterator.
	 *
	 * @var TemplateWorksheetsIterator
	 */
	protected $mIterator = NULL;

	/**
	 * Working collections.
	 *
	 * This data member holds the list of working collections as an array indexed by
	 * collection name with the connection as value.
	 *
	 * @var array
	 */
	protected $mCollections = Array();

		

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
	 * @param Session				$theSession			Related session object.
	 * @param mixed					$theFile			Template file path or reference.
	 *
	 * @access public
	 *
	 * @uses session()
	 * @uses file()
	 */
	public function __construct( Session $theSession, $theFile )
	{
		//
		// Call parent constructor.
		//
		parent::__construct( $theSession->offsetGet( kTAG_USER ) );
		
		//
		// Set session.
		//
		$this->session( $theSession );
		
		//
		// Set file reference.
		//
		$this->file( $theFile );
		
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
	 *
	 * @uses file()
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
		// Delete template file.
		//
		$file = $this->file();
		if( $file instanceof \SplFileInfo )
		{
			if( $file->isWritable() )
				unlink( $file->getRealPath() );
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

	 
	/*===================================================================================
	 *	file																			*
	 *==================================================================================*/

	/**
	 * Manage template file
	 *
	 * This method can be used to set or retrieve the <i>template</i>, the method expects
	 * the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theValue</tt>: The property value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current property value.
	 *		<li><tt>SplFileInfo</tt>: Set the value in the property.
	 *		<li><em>other</em>: The method will assume the value is a string holding the
	 *			template file path.
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
	 * It is not allowed to delete the file reference, this means that providing
	 * <tt>FALSE</tt> will raise an exception.
	 *
	 * @param mixed					$theValue			New file reference or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				Current or old file.
	 *
	 * @uses manageProperty()
	 */
	public function file( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Prevent deleting.
		//
		if( $theValue === FALSE )
			throw new \Exception(
				"You cannot delete the current file reference." );				// !@! ==>
		
		//
		// Check new value.
		//
		if( $theValue !== NULL )
		{
			//
			// Handle path.
			//
			if( ! ($theValue instanceof \SplFileInfo) )
				$theValue = new \SplFileInfo( (string) $theValue );
		
		} // New template.
		
		return $this->manageProperty( $this->mFile, $theValue, $getOld );			// ==>
	
	} // file.

	

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
	 *
	 * @uses session()
	 * @uses sessionPrepare()
	 * @uses sessionStore()
	 * @uses sessionLoad()
	 * @uses succeedSession()
	 * @uses failSession()
	 * @uses exceptionSession()
	 */
	public function execute()
	{
		//
		// Init local storage.
		//
		$transactions = 8;
		$increment = 100 / 8;
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
			$this->session()->progress( 0.71 );

			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionLoad()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction load.
			//
			if( ! $this->sessionLoad() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 8.51 );

			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionStore()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction store.
			//
			if( ! $this->sessionStore() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 0.71 );

			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionStructure()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction structure.
			//
			if( ! $this->sessionStructure() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 0.71 );

			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionSetup()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction setup.
			//
			if( ! $this->sessionSetup() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 0.71 );

			//
			// Reset session records counters.
			//
			$this->session()->offsetSet( kTAG_COUNTER_PROCESSED, 0 );
			$this->session()->offsetSet( kTAG_COUNTER_VALIDATED, 0 );
			$this->session()->offsetSet( kTAG_COUNTER_REJECTED, 0 );
			$this->session()->offsetSet( kTAG_COUNTER_SKIPPED, 0 );
		
			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionCopy()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction copy.
			//
			if( ! $this->sessionCopy() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 73.13 );
	
			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionValidation()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction validation.
			//
			if( ! $this->sessionValidation() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 14.91 );
	
			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					$log,
					"  • sessionObjects()".date( "r" )."\n",
					FILE_APPEND );
	
			//
			// Transaction objects.
			//
			if( ! $this->sessionObjects() )
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
	 * This method will perform the initialisation transaction:
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 * @uses checkFileReference()
	 * @uses deletePendingSessions()
	 */
	protected function sessionPrepare()
	{
		//
		// Instantiate transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_PREPARE ) );
		
		//
		// Delete pending sessions.
		//
		if( ! $this->deletePendingSessions() )
			return $this->failTransaction();										//  ==>
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionPrepare.

	 
	/*===================================================================================
	 *	sessionLoad																		*
	 *==================================================================================*/

	/**
	 * Load template
	 *
	 * This method will:
	 *
	 * <ul>
	 *	<li><tt>{@link kTYPE_TRANS_TMPL_LOAD_FILE}</tt>: Identify and assert file.
	 *	<li><tt>{@link kTYPE_TRANS_TMPL_LOAD_TYPE}</tt>: Identify and assert file type.
	 *	<li><tt>{@link kTYPE_TRANS_TMPL_LOAD_DDICT}</tt>: Load template structure.
	 *	<li><tt>{@link kTYPE_TRANS_TMPL_LOAD_ITEMS}</tt>: Load template stracture elements.
	 * </ul>
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 */
	protected function sessionLoad()
	{
		//
		// Init local storage.
		//
		$count = 4;
		
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_LOAD ) );
		
		//
		// Initialise progress.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		UpdateProcessCounter( $start, $increment, kTAG_COUNTER_PROCESSED );
		
		//
		// Check file.
		//
		if( ! $this->checkFile() )
			return $this->failTransaction();										//  ==>
	
		//
		// Update progress.
		//
		$increment++;
		UpdateProcessCounter(
			$start, $increment,
			kTAG_COUNTER_PROCESSED,
			$transaction, $count );
	
		//
		// Check file type.
		//
		if( ! $this->checkFileType() )
			return $this->failTransaction();										//  ==>
	
		//
		// Update progress.
		//
		$increment++;
		UpdateProcessCounter(
			$start, $increment,
			kTAG_COUNTER_PROCESSED,
			$transaction, $count );
	
		//
		// Load template.
		//
		if( ! $this->loadTemplate() )
			return $this->failTransaction();										//  ==>
	
		//
		// Update progress.
		//
		$increment++;
		UpdateProcessCounter(
			$start, $increment,
			kTAG_COUNTER_PROCESSED,
			$transaction, $count );
		
		//
		// Load template structure.
		//
		if( ! $this->loadTemplateStructure() )
			return $this->failTransaction();										//  ==>
		
		//
		// Set class name in session.
		//
		$this->session()->offsetSet(
			kTAG_CLASS_NAME,
			$this->mParser->getRoot()->offsetGet( kTAG_CLASS_NAME ) );
	
		//
		// Update progress.
		//
		$increment++;
		UpdateProcessCounter(
			$start, $increment,
			kTAG_COUNTER_PROCESSED,
			$transaction, $count );
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionLoad.

	 
	/*===================================================================================
	 *	sessionStore																	*
	 *==================================================================================*/

	/**
	 * Store template file
	 *
	 * This method will store the current template file into the database.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 */
	protected function sessionStore()
	{
		//
		// Instantiate transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_STORE ) );
		
		//
		// Save file.
		//
		$this->session()
			->saveFile( $this->file()->getRealPath(),
						array( kTAG_SESSION_TYPE
								=> $this->session()
									->offsetGet( kTAG_SESSION_TYPE ) ) );
		
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionStore.

	 
	/*===================================================================================
	 *	sessionStructure																*
	 *==================================================================================*/

	/**
	 * Check required template elements
	 *
	 * This method will:
	 *
	 * <ul>
	 *	<li><tt>{@link kTYPE_TRANS_TMPL_STRUCT_WORKSHEETS}</tt>: Assert required worksheets.
	 *	<li><tt>{@link kTYPE_TRANS_TMPL_STRUCT_COLUMNS}</tt>: Assert required columns.
	 * </ul>
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 */
	protected function sessionStructure()
	{
		//
		// Init local storage.
		//
		$count = 2;
		
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_STRUCT ) );
		
		//
		// Initialise progress.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		UpdateProcessCounter( $start, $increment, kTAG_COUNTER_PROCESSED );
		
		//
		// Check required worksheets.
		//
		if( ! $this->checkRequiredWorksheets() )
			return $this->failTransaction();										//  ==>
	
		//
		// Update progress.
		//
		$increment++;
		UpdateProcessCounter(
			$start, $increment,
			kTAG_COUNTER_PROCESSED,
			$transaction, $count );
		
		//
		// Check required worksheet fields.
		//
		if( ! $this->checkRequiredFields() )
			return $this->failTransaction();										//  ==>
	
		//
		// Update progress.
		//
		$increment++;
		UpdateProcessCounter(
			$start, $increment,
			kTAG_COUNTER_PROCESSED,
			$transaction, $count );
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionStructure.

	 
	/*===================================================================================
	 *	sessionSetup																	*
	 *==================================================================================*/

	/**
	 * Setup working collections
	 *
	 * This method will create all working collections abd store their reference in the
	 * session object.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 */
	protected function sessionSetup()
	{
		//
		// Instantiate transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_SETUP ) );
		
		//
		// Create collections.
		//
		if( ! $this->createWorkingCollections() )
			return $this->failTransaction();										//  ==>
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionSetup.

	 
	/*===================================================================================
	 *	sessionCopy																		*
	 *==================================================================================*/

	/**
	 * Copy template data to database
	 *
	 * This method will copy the template data to the working collections.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 */
	protected function sessionCopy()
	{
		//
		// Copy root worksheet data.
		//
		if( ! $this->copyWorksheetData(
				$this->mIterator->getRoot()[ 'W' ],
				$this->mIterator->getRoot()[ 'K' ] ) )
			return $this->failTransaction();										//  ==>
		
		//
		// Copy other worksheets data.
		//
		if( ! $this->copyWorksheetData() )
			return $this->failTransaction();										//  ==>
		
		return TRUE;																// ==>

	} // sessionCopy.

	 
	/*===================================================================================
	 *	sessionValidation																*
	 *==================================================================================*/

	/**
	 * Validate and load worksheet data
	 *
	 * This method will validate and load worksheet data.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 */
	protected function sessionValidation()
	{
		//
		// Get root worksheet.
		//
		$worksheet = $this->mParser->getUnitWorksheet();
		
		//
		// Instantiate root transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()
					->newTransaction( kTYPE_TRANS_TMPL_WORKSHEET,
									  kTYPE_STATUS_EXECUTING,
									  $worksheet ) );
		
		//
		// Validate root worksheet data.
		//
		if( ! $this->validateWorksheetData( $worksheet ) )
			return $this->failTransaction();										//  ==>
		
		//
		// Validate related worksheets.
		//
		foreach( $this->mIterator as $worksheet )
		{
			//
			// Get record count.
			//
			$records
				= $this->mCollections[ $this->getCollectionName( $worksheet[ 'W' ] ) ]
					->matchAll( Array(), kQUERY_COUNT );
			
			//
			// Instantiate transaction.
			//
			$transaction
				= $this->transaction(
					$this->session()
						->newTransaction( kTYPE_TRANS_TMPL_WORKSHEET,
										  kTYPE_STATUS_EXECUTING,
										  $worksheet[ 'W' ] ) );
			
			//
			// Validate related worksheet data.
			//
			if( ! $this->validateWorksheetData( $worksheet[ 'W' ] ) )
				return $this->failTransaction();									//  ==>
		
		} // Iterating worksheet.
		
		return TRUE;															// ==>

	} // sessionValidation.

	 
	/*===================================================================================
	 *	sessionObjects																	*
	 *==================================================================================*/

	/**
	 * Create objects
	 *
	 * This method will create the object from the worksheet stored data, in the process it
	 * will set a warning for all records that will be replaced.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	protected function sessionObjects()
	{
		//
		// Select root worksheet records.
		//
		$records
			= $this->mCollections
				[ $this->getCollectionName( $this->mIterator->getRoot()[ 'W' ] ) ]
				->matchAll( array( '_valid' => TRUE ), kQUERY_ARRAY );
		
		//
		// Instantiate transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()
					->newTransaction( kTYPE_TRANS_TMPL_OBJECTS,
									  kTYPE_STATUS_EXECUTING,
									  NULL ) );
		
		//
		// Create objects.
		//
		if( ! $this->createObjects( $records ) )
			return $this->failTransaction();										//  ==>
		
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionObjects.

	

/*=======================================================================================
 *																						*
 *							PROTECTED OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	deletePendingSessions															*
	 *==================================================================================*/

	/**
	 * Delete pending sessions
	 *
	 * This method will delete all pending sessions, that is, all user sessions.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses wrapper()
	 * @uses session()
	 * @uses transaction()
	 */
	protected function deletePendingSessions()
	{
		//
		// Load pending sessions.
		//
		$sessions
			= Session::ResolveCollection(
				Session::ResolveDatabase( $this->wrapper(), TRUE ), TRUE )
					->matchAll(
						array( kTAG_NID
									=> array( '$ne'
										=> $this->session()->offsetGet( kTAG_NID ) ),
							   kTAG_USER => $this->session()->offsetGet( kTAG_USER ) ),
						kQUERY_NID );
		
		//
		// Handle sessions list.
		//
		if( $count = $sessions->count() )
		{
			//
			// Set count.
			//
			$this->transaction()->offsetSet( kTAG_COUNTER_COLLECTIONS, $count );
			
			//
			// Initialise progress.
			//
			$this->transaction()->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
			UpdateProcessCounter( $start, $increment, kTAG_COUNTER_PROCESSED );
			
			//
			// Delete sessions.
			//
			foreach( $sessions as $session )
			{
				//
				// Delete session.
				//
				Session::Delete( $this->wrapper(), $session );
				
				//
				// Update progress.
				//
				$increment++;
				UpdateProcessCounter(
					$start, $increment,
					kTAG_COUNTER_PROCESSED,
					$this->transaction(), $count );
			}
		}
		
		return TRUE;																// ==>

	} // deletePendingSessions.

	 
	/*===================================================================================
	 *	checkFile																		*
	 *==================================================================================*/

	/**
	 * Check file
	 *
	 * This method will check whether the file is valid and readable.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses transaction()
	 */
	protected function checkFile()
	{
		//
		// Init local storage.
		//
		$file = $this->file();
		$name = $file->getFilename();
		
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction()
				->newTransaction( kTYPE_TRANS_TMPL_LOAD_FILE );
		
		//
		// Check file.
		//
		if( ! $file->isFile() )
		{
			//
			// Remove reference to prevent errors when deleting.
			//
			$this->mFile = NULL;
			
			//
			// Set transaction.
			//
			$message = "The file is either a directory or is invalid.";
			$this->failTransactionLog(
				$transaction,							// Transaction.
				$this->transaction(),					// Parent transaction.
				kTYPE_TRANS_TMPL_LOAD_FILE,				// Transaction type.
				kTYPE_STATUS_FATAL,						// Transaction status.
				$message,								// Transaction message.
				NULL,									// Worksheet.
				NULL,									// Row.
				NULL,									// Column.
				NULL,									// Alias.
				NULL,									// Tag.
				$name,									// Value.
				kTYPE_ERROR_BAD_TMPL_FILE,				// Error type.
				kTYPE_ERROR_CODE_FILE_BAD,				// Error code.
				NULL );									// Error resource.
	
			//
			// Close transaction.
			//
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			
			return FALSE;															// ==>
		
		} // Bad file.
		
		//
		// Check if readable.
		//
		if( ! $file->isReadable() )
		{
			//
			// Remove reference to prevent errors when deleting.
			//
			$this->mFile = NULL;
			
			//
			// Set transaction.
			//
			$message = 'The file cannot be read.';
			$this->failTransactionLog(
				$transaction,							// Transaction.
				$this->transaction(),					// Parent transaction.
				kTYPE_TRANS_TMPL_LOAD_FILE,				// Transaction type.
				kTYPE_STATUS_FATAL,						// Transaction status.
				$message,								// Transaction message.
				NULL,									// Worksheet.
				NULL,									// Row.
				NULL,									// Column.
				NULL,									// Alias.
				NULL,									// Tag.
				$name,									// Value.
				kTYPE_ERROR_BAD_TMPL_FILE,				// Error type.
				kTYPE_ERROR_CODE_FILE_UNRWAD,			// Error code.
				NULL );									// Error resource.
	
			//
			// Close transaction.
			//
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			
			return FALSE;															// ==>
		
		} // Unreadable.

		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // checkFile.

	 
	/*===================================================================================
	 *	checkFileType																	*
	 *==================================================================================*/

	/**
	 * Check file type
	 *
	 * This method will check whether the file type is supported.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses transaction()
	 */
	protected function checkFileType()
	{
		//
		// Init local storage.
		//
		$file = $this->file();
		$name = $file->getFilename();
		
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction()
				->newTransaction( kTYPE_TRANS_TMPL_LOAD_TYPE );
		
		//
		// Check file extension.
		//
		switch( $ext = $file->getExtension() )
		{
			case 'xlsx':	//	Excel (OfficeOpenXML) Spreadsheet
			case 'xlsm':	//	Excel (OfficeOpenXML) Macro Spreadsheet
			case 'xltx':	//	Excel (OfficeOpenXML) Template
			case 'xltm':	//	Excel (OfficeOpenXML) Macro Template
			case 'xls':		//	Excel (BIFF) Spreadsheet
			case 'xlt':		//	Excel (BIFF) Template
			case 'xml':		//	Excel 2003 SpreadSheetML
				break;
			
			default:
				//
				// Set transaction.
				//
				$message = 'The file type is not supported, please submit an Excel file.';
				$this->failTransactionLog(
					$transaction,							// Transaction.
					$this->transaction(),					// Parent transaction.
					kTYPE_TRANS_TMPL_LOAD_TYPE,				// Transaction type.
					kTYPE_STATUS_FATAL,						// Transaction status.
					$message,								// Transaction message.
					NULL,									// Worksheet.
					NULL,									// Row.
					NULL,									// Column.
					NULL,									// Alias.
					NULL,									// Tag.
					$name,									// Value.
					kTYPE_ERROR_BAD_TMPL_FILE,				// Error type.
					kTYPE_ERROR_CODE_FILE_UNSUP,			// Error code.
					"http://filext.com/file-extension/$ext"	// Error resource.
				);
	
				//
				// Close transaction.
				//
				$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
				return FALSE;														// ==>
		}
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // checkFileType.

	 
	/*===================================================================================
	 *	loadTemplate																	*
	 *==================================================================================*/

	/**
	 * Load template
	 *
	 * This method will instantiate the template parser
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses transaction()
	 */
	protected function loadTemplate()
	{
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction()
				->newTransaction( kTYPE_TRANS_TMPL_LOAD_DDICT );
		
		//
		// Instantiate parser.
		//
		$this->mParser = new ExcelTemplateParser( $this->wrapper(), $this->file() );
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // loadTemplate.

	 
	/*===================================================================================
	 *	loadTemplateStructure															*
	 *==================================================================================*/

	/**
	 * Load template structure
	 *
	 * This method will load the template structure
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses transaction()
	 */
	protected function loadTemplateStructure()
	{
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction()
				->newTransaction( kTYPE_TRANS_TMPL_LOAD_ITEMS );
		
		//
		// Load structure.
		//
		if( ! $this->mParser->loadStructure( $transaction ) )
			return FALSE;															// ==>
		
		//
		// Instantiate iterator.
		//
		$this->mIterator = new TemplateWorksheetsIterator( $this->mParser );
		
		//
		// Set root node reference in session.
		//
		$this->session()->offsetSet(
			kTAG_NODE,
			$this->mParser->getRoot()->offsetGet( kTAG_NID ) );
	
		//
		// Set class name in session.
		//
		$this->session()->offsetSet(
			kTAG_CLASS_NAME,
			$this->mParser->getRoot()->offsetGet( kTAG_CLASS_NAME ) );
		
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // loadTemplateStructure.

	 
	/*===================================================================================
	 *	checkRequiredWorksheets															*
	 *==================================================================================*/

	/**
	 * Assert required worksheets
	 *
	 * This method will check whether all required worksheets are there.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses transaction()
	 */
	protected function checkRequiredWorksheets()
	{
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction()
				->newTransaction( kTYPE_TRANS_TMPL_STRUCT_WORKSHEETS );
		
		//
		// Load structure.
		//
		if( ! $this->mParser->checkRequiredWorksheets( $transaction ) )
			return FALSE;															// ==>
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // checkRequiredWorksheets.

	 
	/*===================================================================================
	 *	checkRequiredFields																*
	 *==================================================================================*/

	/**
	 * Assert required fields
	 *
	 * This method will check whether all required worksheets are there.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses transaction()
	 */
	protected function checkRequiredFields()
	{
		//
		// Init local storage.
		//
		$ok = TRUE;
		$worksheets = array_keys( $this->mParser->getWorksheets() );
		$count = count( $worksheets );
		
		//
		// Initialise progress.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		UpdateProcessCounter( $start, $increment, kTAG_COUNTER_PROCESSED );
		
		//
		// Iterate worksheets.
		//
		foreach( $worksheets as $worksheet )
		{
			//
			// Create transaction.
			//
			$transaction
				= $this->transaction()
					->newTransaction( kTYPE_TRANS_TMPL_STRUCT_COLUMNS,
									  kTYPE_STATUS_EXECUTING,
									  $worksheet );
		
			//
			// Check required columns.
			//
			if( ! $this->mParser->checkRequiredColumns( $transaction, $worksheet ) )
				$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
			
			//
			// Handle errors.
			//
			else
			{
				$ok = FALSE;
				$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
			}
			
			//
			// Close transaction.
			//
			$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			
			//
			// Update progress.
			//
			$increment++;
			UpdateProcessCounter(
				$start, $increment,
				kTAG_COUNTER_PROCESSED,
				$this->transaction(), $count );
		
		} // Iterating worksheets.
		
		return $ok;																	// ==>

	} // checkRequiredFields.

	 
	/*===================================================================================
	 *	createWorkingCollections														*
	 *==================================================================================*/

	/**
	 * Create working collections
	 *
	 * This method will create the working collection connections and save their reference
	 * in the current session.
	 *
	 * Collection names correspond to the template worksheet names prefixed by the user
	 * hash.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses session()
	 */
	protected function createWorkingCollections()
	{
		//
		// Init local storage.
		//
		$this->mCollections = Array();
		$fields = $this->mParser->getFields();
		$worksheets = $this->mParser->getWorksheets();
		$count = count( $worksheets ) + 1;
		
		//
		// Initialise progress.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		UpdateProcessCounter( $start, $increment, kTAG_COUNTER_PROCESSED );
		
		//
		// Iterate worksheets.
		//
		foreach( array_keys( $worksheets ) as $worksheet )
		{
			//
			// Create collection.
			//
			$name = $this->getCollectionName( $worksheet );
			$collection
				= Session::ResolveDatabase( $this->wrapper(), TRUE )
					->collection( $name, TRUE );
			
			//
			// Drop collection.
			//
			$collection->drop();
			
			//
			// Save collection.
			//
			$this->mCollections[ $name ] = $collection;
			
			//
			// Add reference indexes.
			//
			foreach( $fields[ $worksheet ] as $field_name => $field )
			{
				if( array_key_exists( 'indexed', $field ) )
				{
					if( array_key_exists( 'unique', $field ) )
						$this->mCollections[ $name ]
							->createIndex( array( $field_name => 1 ),
										   array( "unique" => TRUE ) );
					else
						$this->mCollections[ $name ]
							->createIndex( array( $field_name => 1 ) );
				}
			}
			
			//
			// Add status index.
			//
			$this->mCollections[ $name ]->createIndex( array( '_valid' => 1 ) );
		
			//
			// Update progress.
			//
			$increment++;
			UpdateProcessCounter(
				$start, $increment,
				kTAG_COUNTER_PROCESSED,
				$this->transaction(), $count );
		}
		
		//
		// Add unit collection.
		//
		$name = $this->getCollectionName( UnitObject::kSEQ_NAME );
		$this->mCollections[ $name ]
			= Session::ResolveDatabase( $this->wrapper(), TRUE )
				->collection( $name, TRUE );
		
		//
		// Drop collection.
		//
		$this->mCollections[ $name ]->drop();
	
		//
		// Update progress.
		//
		$increment++;
		UpdateProcessCounter(
			$start, $increment,
			kTAG_COUNTER_PROCESSED,
			$this->transaction(), $count );
		
		//
		// Add to session.
		//
		$this->session()->offsetSet( kTAG_CONN_COLLS, array_keys( $this->mCollections ) );
		
		return TRUE;																// ==>

	} // createWorkingCollections.

	 
	/*===================================================================================
	 *	copyWorksheetData																*
	 *==================================================================================*/

	/**
	 * Copy worksheet data to database
	 *
	 * This method will copy the worksheet data from the template to the database.
	 *
	 * The method expects a parameter that represents the worksheet to process, if the
	 * parameter is not provided, the method will iterate all worksheets, except the root
	 * worksheet.
	 *
	 * The last optional parameter is provided as an array structured as follows:
	 *
	 * <ul>
	 *	<li><tt>W</tt>: Worksheet name.
	 *	<li><tt>K</tt>: Worksheet key field name.
	 * </ul>
	 *
	 * @param string				$theWorksheet		Worksheet name.
	 * @param string				$theFieldIdx		Worksheet index field name.
	 * @param string				$theFieldRel		Relation field name.
	 * @param array					$theParent			Parent worksheet and field.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	protected function copyWorksheetData( $theWorksheet = NULL,
										  $theFieldIdx = NULL,
										  $theFieldRel = NULL,
										  $theParent = NULL )
	{
		//
		// Handle all worksheets.
		//
		if( $theWorksheet === NULL )
		{
			//
			// Iterate worksheets.
			//
			foreach( $this->mIterator as $element )
			{
				//
				// Init local storage.
				//
				$worksheet = $element[ 'W' ];
				$index = ( array_key_exists( 'K', $element ) )
					   ? $element[ 'K' ]
					   : NULL;
				$link = ( array_key_exists( 'F', $element ) )
					   ? $element[ 'F' ]
					   : NULL;
				
				//
				// Recurse.
				//
				if( ! $this->copyWorksheetData(
						$worksheet, $index, $link, $this->mIterator->parent() ) )
					return FALSE;													// ==>
			
			} // Iterating worksheet.
			
			return TRUE;															// ==>
		
		} // Worksheet not provided.
		
		//
		// Init local storage.
		//
		$collection = $this->mCollections[ $this->getCollectionName( $theWorksheet ) ];
		$worksheet_data = $this->mParser->getWorksheets()[ $theWorksheet ];
		$fields_data = $this->mParser->getFields()[ $theWorksheet ];
		$records = $worksheet_data[ 'last_row' ] - $worksheet_data[ 'data_row' ] + 1;
		
		//
		// Create transaction.
		//
		$this->transaction(
			$this->session()
				->newTransaction( kTYPE_TRANS_TMPL_LOAD_DATA,
								  kTYPE_STATUS_EXECUTING,
								  $theWorksheet ) );
		
		//
		// Set records count.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_RECORDS, $records );
	
		//
		// Initialise session counters.
		//
		UpdateProcessCounter( $start_processed_session, $increment_processed_session,
							  kTAG_COUNTER_PROCESSED );
		UpdateProcessCounter( $start_rejected_session, $increment_rejected_session,
							  kTAG_COUNTER_REJECTED );
		UpdateProcessCounter( $start_skipped_session, $increment_skipped_session,
							  kTAG_COUNTER_SKIPPED );
	
		//
		// Initialise transaction counters.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		UpdateProcessCounter( $start_processed, $increment_processed,
							  kTAG_COUNTER_PROCESSED );
		UpdateProcessCounter( $start_validated, $increment_validated,
							  kTAG_COUNTER_VALIDATED );
		UpdateProcessCounter( $start_rejected, $increment_rejected,
							  kTAG_COUNTER_REJECTED );
		UpdateProcessCounter( $start_skipped, $increment_skipped,
							  kTAG_COUNTER_SKIPPED );
		
		//
		// Iterate rows.
		//
		for( $row = $worksheet_data[ 'data_row' ];
				$row <= $worksheet_data[ 'last_row' ];
					$row++ )
		{
			//
			// Init local storage.
			//
			$transaction = NULL;
			
			//
			// Load row data.
			//
			$record = Array();
			foreach( $fields_data as $symbol => $field_data )
			{
				//
				// Get value.
				//
				$value
					= $this->mParser->getCellValue(
						$theWorksheet, $row, $field_data[ 'column_name' ] );
				
				//
				// Set value.
				//
				if( strlen( $value = trim( $value ) ) )
					$record[ $symbol ] = $value;
			
			} // Loading row data.
			
			//
			// Handle record.
			//
			if( count( $record ) )
			{
				//
				// Init local storage.
				//
				$ok = TRUE;
				
				//
				// Handle index field.
				//
				if( $theFieldIdx !== NULL )
				{
					//
					// Check if index field exists.
					//
					if( array_key_exists( $theFieldIdx, $record ) )
					{
						//
						// Check unique field duplicates.
						//
						if( array_key_exists( 'unique', $fields_data[ $theFieldIdx ] ) )
						{
							//
							// Locate object.
							//
							$duplicate
								= $collection->matchOne(
									array( $theFieldIdx => $record[ $theFieldIdx ] ),
									kQUERY_ARRAY );
					
							//
							// Handle duplicate.
							//
							if( $duplicate !== NULL )
							{
								$message = "A record already exists with the same key in "
										  ."row [".$duplicate[ kTAG_NID ]."].";
								$this->failTransactionLog(
									$transaction,								// Trans.
									$this->transaction(),						// Parent.
									kTYPE_TRANS_TMPL_LOAD_DATA_ROW,				// Type.
									kTYPE_STATUS_ERROR,							// Status.
									$message,									// Message.
									$theWorksheet,								// Wrksh.
									$row,										// Row.
									$fields_data[ $theFieldIdx ][ 'column_name' ],	// Column.
									$theFieldIdx,									// Alias.
									NULL,										// Tag.
									$record[ $theFieldIdx ],						// Value.
									kTYPE_ERROR_DUPLICATE_KEY,					// Err type.
									kTYPE_ERROR_CODE_DUPLICATE_KEY,				// Err code.
									NULL										// Err res.
								);
						
								$ok = FALSE;
					
							} // Set transaction.
				
						} // Unique index field.
					
					} // Provided index field in record.
					
					//
					// Handle missing index field.
					//
					else
					{
						$message = "Missing index field.";
						$this->failTransactionLog(
							$transaction,									// Transaction.
							$this->transaction(),							// Parent.
							kTYPE_TRANS_TMPL_LOAD_DATA_ROW,					// Type.
							kTYPE_STATUS_ERROR,								// Status.
							$message,										// Message.
							$theWorksheet,									// Worksheet.
							$row,											// Row.
							$fields_data[ $theFieldIdx ][ 'column_name' ],	// Column.
							$theFieldIdx,									// Alias.
							NULL,											// Tag.
							NULL,											// Value.
							kTYPE_ERROR_MISSING_REQUIRED,					// Err type.
							kTYPE_ERROR_CODE_REQ_FIELD,						// Err code.
							NULL											// Err resource.
						);
						
						$ok = FALSE;
					
					} // Missing index field in record.
				
				} // Provided index field parameter.
				
				//
				// Check for relationships.
				//
				if( $theFieldRel !== NULL )
				{
					//
					// Check if relation field is missing from record.
					//
					if( ! array_key_exists( $theFieldRel, $record ) )
					{
						$message = "Missing field which references the "
								  .$theParent[ 'W' ]." worksheet.";
						$this->failTransactionLog(
							$transaction,									// Transaction.
							$this->transaction(),							// Parent.
							kTYPE_TRANS_TMPL_LOAD_DATA_ROW,					// Type.
							kTYPE_STATUS_ERROR,								// Status.
							$message,										// Message.
							$theWorksheet,									// Worksheet.
							$row,											// Row.
							$fields_data[ $theFieldRel ][ 'column_name' ],	// Column.
							$theFieldRel,									// Alias.
							NULL,											// Tag.
							NULL,											// Value.
							kTYPE_ERROR_MISSING_REQUIRED,					// Err type.
							kTYPE_ERROR_CODE_REQ_FIELD,						// Err code.
							NULL											// Err resource.
						);
					
						$ok = FALSE;
					
					} // Missing related field.
					
					//
					// Check related worksheet value.
					//
					elseif( ! $this->mCollections
								[ $this->getCollectionName( $theParent[ 'W' ] ) ]
									->matchOne(
										array( $theParent[ 'K' ]
												=> $record[ $theFieldRel ] ),
										kQUERY_COUNT ) )
					{
						$message = "The value of this field does not correspond to any "
								  ."rows of worksheet ".$theParent[ 'W' ]." and column "
								  .$this->mParser->getFields()[ $theParent[ 'W' ] ]
								  							  [ $theParent[ 'K' ] ]
								  							  [ 'column_name' ]
								  ." with symbol ".$theParent[ 'K' ].".";
						$this->failTransactionLog(
							$transaction,									// Transaction.
							$this->transaction(),							// Parent.
							kTYPE_TRANS_TMPL_LOAD_DATA_ROW,					// Type.
							kTYPE_STATUS_ERROR,								// Status.
							$message,										// Message.
							$theWorksheet,									// Worksheet.
							$row,											// Row.
							$fields_data[ $theFieldRel ][ 'column_name' ],	// Column.
							$theFieldRel,									// Alias.
							NULL,											// Tag.
							$record[ $theFieldRel ],						// Value.
							kTYPE_ERROR_RELATED_NO_MATCH,					// Err type.
							kTYPE_ERROR_CODE_BAD_RELATIONSHIP,				// Err code.
							NULL											// Err resource.
						);
					
						$ok = FALSE;
					
					} // Missing related record.
				
				} // Has relationship field.
				
				//
				// Commit record.
				//
				if( $ok )
				{
					//
					// Set identifier.
					//
					$record[ kTAG_NID ] = $row;
					
					//
					// Commit record.
					//
					$collection->commit( $record );
					
					//
					// Update validated.
					//
					$increment_validated++;
					UpdateProcessCounter(
						$start_validated, $increment_validated,
						kTAG_COUNTER_VALIDATED,
						$this->transaction() );
				
				} // Valid record.
	
				//
				// Handle rejected.
				//
				else
				{
					//
					// Update session processed.
					//
					$increment_processed_session++;
					UpdateProcessCounter(
						$start_processed_session, $increment_processed_session,
						kTAG_COUNTER_PROCESSED,
						$this->session() );
					
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
				}
		
				//
				// Close transaction.
				//
				if( $transaction!== NULL )
					$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			
			} // Has data.
			
			//
			// Handle skipped.
			//
			else
			{
				//
				// Update session processed.
				//
				$increment_processed_session++;
				UpdateProcessCounter(
					$start_processed_session, $increment_processed_session,
					kTAG_COUNTER_PROCESSED,
					$this->session() );
				
				//
				// Update session skipped.
				//
				$increment_skipped_session++;
				UpdateProcessCounter(
					$start_skipped_session, $increment_skipped_session,
					kTAG_COUNTER_SKIPPED,
					$this->session() );
				
				//
				// Update transaction skipped.
				//
				$increment_skipped++;
				UpdateProcessCounter(
					$start_skipped, $increment_skipped,
					kTAG_COUNTER_SKIPPED,
					$this->transaction() );
			}
			
			//
			// Update processed.
			//
			$increment_processed++;
			UpdateProcessCounter(
				$start_processed, $increment_processed,
				kTAG_COUNTER_PROCESSED,
				$this->transaction(), $records );
		
		} // Iterating worksheet rows.
		
		//
		// Update session processed.
		//
		$increment_processed_session++;
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
			$this->transaction(), $records, TRUE );
		
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
		$increment_rejected_session++;
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
		
		//
		// Update session skipped.
		//
		$increment_skipped_session++;
		UpdateProcessCounter(
			$start_skipped_session, $increment_skipped_session,
			kTAG_COUNTER_SKIPPED,
			$this->session(), NULL, TRUE );
		
		//
		// Update transaction skipped.
		//
		UpdateProcessCounter(
			$start_skipped, $increment_skipped,
			kTAG_COUNTER_SKIPPED,
			$this->transaction(), NULL, TRUE );
		
		//
		// Close transaction.
		//
		$this->transaction()->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$this->transaction()->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$this->transaction()->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // copyWorksheetData.

	 
	/*===================================================================================
	 *	validateWorksheetData																*
	 *==================================================================================*/

	/**
	 * Load worksheet data
	 *
	 * This method will validate and load worksheet data.
	 *
	 * @param string				$theWorksheet		Worksheet name.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses session()
	 */
	protected function validateWorksheetData( $theWorksheet )
	{
		//
		// Init local storage.
		//
		$collection = $this->mCollections[ $this->getCollectionName( $theWorksheet ) ];
		$fields_data = $this->mParser->getFields()[ $theWorksheet ];
		$validated = $rejected = Array();
		
		//
		// Select rows.
		//
		$rs = $collection->matchAll( Array(), kQUERY_ARRAY );
		$records = $rs->count();
		
		//
		// Set records count.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_RECORDS, $records );
		
	
		//
		// Initialise session counters.
		//
		UpdateProcessCounter( $start_processed_session, $increment_processed_session,
							  kTAG_COUNTER_PROCESSED );
		UpdateProcessCounter( $start_validated_session, $increment_validated_session,
							  kTAG_COUNTER_VALIDATED );
		UpdateProcessCounter( $start_rejected_session, $increment_rejected_session,
							  kTAG_COUNTER_REJECTED );
		UpdateProcessCounter( $start_skipped_session, $increment_skipped_session,
							  kTAG_COUNTER_SKIPPED );
	
		//
		// Initialise transaction counters.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		UpdateProcessCounter( $start_processed, $increment_processed,
							  kTAG_COUNTER_PROCESSED );
		UpdateProcessCounter( $start_validated, $increment_validated,
							  kTAG_COUNTER_VALIDATED );
		UpdateProcessCounter( $start_rejected, $increment_rejected,
							  kTAG_COUNTER_REJECTED );
		
		//
		// Select rows.
		//
		foreach( $rs as $record )
		{
			//
			// Init local storage.
			//
			$errors = 0;
			$transaction = NULL;
			
			//
			// Check required fields.
			//
			$errors
				+= $this->checkRowRequiredFields(
						$transaction,						// Row transaction.
						$record,							// Row record.
						$theWorksheet );					// Worksheet name.
		
			//
			// Validate row.
			//
			foreach( array_keys( $record ) as $symbol )
				$errors
					+= $this->checkColumnValue(
							$transaction,					// Row transaction.
							$record,						// Row record.
							$theWorksheet,					// Worksheet name.
							$symbol );						// Field symbol.
			
			//
			// Handle errors.
			//
			if( $errors )
			{
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
				
				//
				// Set validation flag.
				//
				$record[ '_valid' ] = FALSE;
				
			} // Has errors.
			
			//
			// Handle valid row.
			//
			else
			{
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
				
				//
				// Set validation flag.
				//
				$record[ '_valid' ] = TRUE;
			
			} // Valid row.
			
			//
			// Write record.
			//
			$collection->save( $record );
							
			//
			// Close transaction.
			//
			if( $transaction !== NULL )
			{
				$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
				$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			}
			
			//
			// Update session processed.
			//
			$increment_processed_session++;
			UpdateProcessCounter(
				$start_processed_session, $increment_processed_session,
				kTAG_COUNTER_PROCESSED,
				$this->session() );
			
			//
			// Update transaction processed.
			//
			$increment_processed++;
			UpdateProcessCounter(
				$start_processed, $increment_processed,
				kTAG_COUNTER_PROCESSED,
				$this->transaction(), $records );
		
		} // Iterating worksheet rows.
		
		//
		// Update session processed.
		//
		$increment_processed_session++;
		UpdateProcessCounter(
			$start_processed_session, $increment_processed_session,
			kTAG_COUNTER_PROCESSED,
			$this->session(), NULL, TRUE );
		
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
		$increment_validated_session++;
		UpdateProcessCounter(
			$start_validated_session, $increment_validated_session,
			kTAG_COUNTER_VALIDATED,
			$this->session(), NULL, TRUE );
		
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
		$increment_rejected_session++;
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
	
		//
		// Close parent transaction.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$this->transaction()->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$this->transaction()->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // validateWorksheetData.

	 
	/*===================================================================================
	 *	createObjects																	*
	 *==================================================================================*/

	/**
	 * Load objects
	 *
	 * This method will expects the root worksheet records and will create an object for
	 * each record.
	 *
	 * @param ObjectIterator		$theRecords			Records iterator.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	protected function createObjects( ObjectIterator $theRecords )
	{
		//
		// Init local storage.
		//
		$count = $theRecords->count();
		$root = $this->mIterator->getRoot();
		$class = $this->mParser->getRoot()->offsetGet( kTAG_CLASS_NAME );
		$collection_units
			= UnitObject::ResolveCollection(
				UnitObject::ResolveDatabase( $this->wrapper(), TRUE ),
				TRUE );
		$collection_objects
			= $this->mCollections[
				$this->getCollectionName( UnitObject::kSEQ_NAME )
			];
		
		//
		// Set records count.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_RECORDS, $count );
	
		//
		// Initialise session counters.
		//
		UpdateProcessCounter( $start_processed_session, $increment_processed_session,
							  kTAG_COUNTER_PROCESSED );
		UpdateProcessCounter( $start_rejected_session, $increment_rejected_session,
							  kTAG_COUNTER_REJECTED );
		UpdateProcessCounter( $start_validated_session, $increment_validated_session,
							  kTAG_COUNTER_VALIDATED );
	
		//
		// Initialise transaction counters.
		//
		$this->transaction()->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		UpdateProcessCounter( $start_processed, $increment_processed,
							  kTAG_COUNTER_PROCESSED );
		UpdateProcessCounter( $start_validated, $increment_validated,
							  kTAG_COUNTER_VALIDATED );
		UpdateProcessCounter( $start_rejected, $increment_rejected,
							  kTAG_COUNTER_REJECTED );
		
		
		//
		// Iterate root worksheet records.
		//
		foreach( $theRecords as $record )
		{
			//
			// Init local storage.
			//
			$transaction = NULL;
			
			//
			// Instantiate object.
			//
			$object = new $class( $this->wrapper() );
		
			//
			// Set object properties.
			//
			if( ! $this->setObjectProperties( $object, $root[ 'W' ], $record ) )
				return FALSE;														// ==>
			
			//
			// Handle related worksheets.
			//
			if( array_key_exists( 'C', $this->mIterator->getStruct() ) )
			{
				//
				// Traverse worksheet structure.
				//
				if( ! $this->setWorksheetProperties(
						$object,
						$this->mIterator->getStruct()[ 'C' ],
						$record[ $root[ 'K' ] ] ) )
					return FALSE;													// ==>
			
			} // Has related worksheets.
		
			//
			// Class validation TRY block.
			//
			try
			{
				//
				// Validate object.
				//
				$object->validate( TRUE, TRUE, FALSE );
			
				//
				// Check for duplicate record.
				//
				$id = $object->offsetGet( kTAG_NID );
				$dup = $collection_objects->matchOne( array( kTAG_NID => $id ),
													  kQUERY_ARRAY );
				if( $dup !== NULL )
				{
					//
					// Post error.
					//
					$message = "The object cannot be saved because there is another object with "
							  ."the same unique identifier from row "
							  .$dup[ kTAG_ROW ]
							  .": check the significant properties of both rows.";
					$this->failTransactionLog(
						$transaction,								// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_ERROR,							// Transaction status.
						$message,									// Transaction message.
						$root[ 'W' ],								// Worksheet.
						$record[ kTAG_NID ],						// Row.
						NULL,										// Column.
						NULL,										// Alias.
						NULL,										// Tag.
						$id,										// Value.
						kTYPE_ERROR_DUPLICATE_RECOD,				// Error type.
						kTYPE_ERROR_CODE_DUPLICATE_OBJECT,			// Error code.
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
				
				} // Duplicate object.
				
				//
				// Object is not duplicate.
				//
				else
				{
					//
					// Check if it will be replaced.
					//
					if( $collection_units->matchOne( array( kTAG_NID => $id ),
													 kQUERY_COUNT ) )
					{
						//
						// Post message.
						//
						$message = 'The object will be replaced in the database.';
						$this->failTransactionLog(
							$transaction,							// Transaction.
							$this->transaction(),					// Parent transaction.
							kTYPE_TRANS_TMPL_WORKSHEET_ROW,			// Transaction type.
							kTYPE_STATUS_MESSAGE,					// Transaction status.
							$message,								// Transaction message.
							$root[ 'W' ],							// Worksheet.
							$record[ kTAG_NID ],					// Row.
							NULL,									// Column.
							NULL,									// Alias.
							NULL,									// Tag.
							$id,									// Value.
							kTYPE_MESSAGE_OBJECT_REPLACE,			// Error type.
							kTYPE_MESSAGE_CODE_REPLACE_OBJECT,		// Error code.
							NULL									// Error resource.
						);
					
					} // Record exists.
					
					//
					// Commit object.
					//
					$object->offsetSet( kTAG_ROW, $record[ kTAG_NID ] );
					$collection_objects->commit( $object );
					
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
				
				} // Unique object.
			
			} // Validation TRY block.
			
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
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_EXCEPTION,						// Transaction status.
					$error->getMessage(),						// Transaction message.
					$root[ 'W' ],								// Worksheet.
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
			
			} // Validation CATCH block.
	
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
		// Set session records.
		//
		$this->session()->offsetSet(
			kTAG_COUNTER_RECORDS,
			$collection_objects->matchAll( Array(), kQUERY_COUNT ) );
		
		//
		// Update session processed.
		//
		$increment_processed_session++;
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
		$increment_validated_session++;
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
		$increment_rejected_session++;
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

	} // createObjects.

	

/*=======================================================================================
 *																						*
 *							PROTECTED VALIDATION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	checkRowRequiredFields															*
	 *==================================================================================*/

	/**
	 * Check row required fields
	 *
	 * This method will check whether the provided row has all required fields, if that is
	 * not the case, the method will create the row error transaction, add the log entries
	 * and return the number of missing fields.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array					$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 *
	 * @access protected
	 * @return int					Number of missing fields.
	 *
	 * @uses file()
	 * @uses session()
	 */
	protected function checkRowRequiredFields( &$theTransaction,
												$theRecord,
												$theWorksheet )
	{
		//
		// Get missing required fields.
		//
		$missing
			= array_diff(
				$this->mParser->getRequiredFields()[ $theWorksheet ],
				array_keys( $theRecord ) );
		
		//
		// Handle missing.
		//
		if( count( $missing ) )
		{
			//
			// Init local storage.
			//
			$fields = $this->mParser->getFields()[ $theWorksheet ];
			
			//
			// Add logs.
			//
			foreach( $missing as $symbol )
			{
				//
				// Init local storage.
				//
				$field = $fields[ $symbol ];
				$tag
					= $this->mParser
						->getNode( $field[ 'node' ] )
							->offsetGet( kTAG_TAG );
				
				//
				// Create transaction.
				//
				$this->failTransactionLog(
					$theTransaction,						// Transaction.
					$this->transaction(),					// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,			// Transaction type.
					kTYPE_STATUS_ERROR,						// Transaction status.
					'Missing required field.',				// Transaction message.
					$theWorksheet,							// Worksheet.
					$theRecord[ kTAG_NID ],					// Row.
					$field[ 'column_name' ],				// Column.
					$symbol,								// Alias.
					$tag,									// Tag.
					NULL,									// Value.
					kTYPE_ERROR_MISSING_REQUIRED,			// Error type.
					kTYPE_ERROR_CODE_REQ_FIELD,				// Error code.
					NULL );									// Error resource.
			}
			
			return count( $missing );												// ==>
		
		} // Missing required fields.
		
		return 0;																	// ==>

	} // checkRowRequiredFields.

	 
	/*===================================================================================
	 *	checkColumnValue																*
	 *==================================================================================*/

	/**
	 * Validate property
	 *
	 * This method will validate the provided property value and create, if necessary the
	 * error transaction.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param string				$theSymbol			Field symbol.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function checkColumnValue( &$theTransaction,
										 &$theRecord,
										  $theWorksheet,
										  $theSymbol )
	{
		//
		// Init local storage.
		//
		$errors = 0;
		
		//
		// Skip private properties.
		//
		if( substr( $theSymbol, 0, 1 ) == '_' )
			return $errors;															// ==>
		
		//
		// Load column info.
		//
		$field_data = $this->mParser->getFields()[ $theWorksheet ];
		$field_node = $this->mParser->getNode( $field_data[ $theSymbol ][ 'node' ] );
		$field_tag = ( $field_node->offsetExists( kTAG_TAG ) )
				   ? $this->mParser->getTag( $field_node->offsetGet( kTAG_TAG ) )
				   : NULL;
		
		//
		// Cast local field.
		//
		if( $field_tag === NULL )
			$theRecord[ $theSymbol ]
				= (string) $theRecord[ $theSymbol ];
		
		//
		// Handle tag field.
		//
		else
		{
			//
			// Parse by type.
			//
			switch( $field_tag->offsetGet( kTAG_DATA_TYPE ) )
			{
				case kTYPE_MIXED:
					break;
			
				case kTYPE_STRING:
				case kTYPE_TEXT:
					$errors +=
						$this->validateString(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_INT:
				case kTYPE_YEAR:
					$errors +=
						$this->validateInteger(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_FLOAT:
					$errors +=
						$this->validateFloat(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_BOOLEAN:
					$this->validateBoolean(
						$theTransaction, $theRecord,
						$theWorksheet, $theRecord[ kTAG_NID ],
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_STRUCT:
					$errors +=
						$this->validateStruct(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_ARRAY:
					$errors +=
						$this->validateArray(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_LANGUAGE_STRING:
					$errors +=
						$this->validateLanguageString(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_LANGUAGE_STRINGS:
					$errors +=
						$this->validateLanguageStrings(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_TYPED_LIST:
					$errors +=
						$this->validateTypedList(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_SHAPE:
					$errors +=
						$this->validateShape(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_URL:
					$errors +=
						$this->validateLink(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_DATE:
					$errors +=
						$this->validateDate(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_ENUM:
					$errors +=
						$this->validateEnum(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_SET:
					$errors +=
						$this->validateEnumSet(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_TAG:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Tag::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_TERM:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Term::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_NODE:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Node::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_EDGE:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Edge::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_UNIT:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, UnitObject::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_USER:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, User::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_SESSION:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Session::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_TRANSACTION:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Transaction::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_FILE:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, FileObject::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_SELF:
					$class = $this->mParser->getRoot()->offsetGet( kTAG_CLASS_NAME );
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, $class::kSEQ_NAME,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_OBJECT_ID:
					$errors +=
						$this->validateObjectId(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_TIME_STAMP:
					$errors +=
						$this->validateTimeStamp(
							$theTransaction, $theRecord,
							$theWorksheet, $theRecord[ kTAG_NID ],
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
			} // Parsing by type.
		
		} // Tag field.
		
		return $errors;																// ==>

	} // checkColumnValue.

	

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
 *							PROTECTED VALIDATION UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	validateString																	*
	 *==================================================================================*/

	/**
	 * Validate string
	 *
	 * This method will validate the provided string property, it will simply cast the
	 * value to a string.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors (0).
	 */
	protected function validateString( &$theTransaction,
									   &$theRecord,
										$theWorksheet,
										$theRow,
										$theFieldData,
										$theFieldNode,
										$theFieldTag )
	{
		//
		// Init local storage.
		//
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
		//
		// Cast value.
		//
		$theRecord[ $symbol ] = trim( $theRecord[ $symbol ] );
		if( ! strlen( $theRecord[ $symbol ] ) )
		{
			unset( $theRecord[ $symbol ] );
			return 0;																// ==>
		}
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
			
			//
			// Split elements.
			//
			$elements = ( strlen( $tokens ) )
					  ? explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] )
					  : array( $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			$theRecord[ $symbol ] = Array();
			foreach( $elements as $element )
			{
				if( strlen( $element = trim( $element ) ) )
					$theRecord[ $symbol ][] = $element;
			}
			
			//
			// Remove if empty.
			//
			if( ! count( $theRecord[ $symbol ] ) )
			{
				unset( $theRecord[ $symbol ] );
				return 0;															// ==>
			}
		
		} // List.
		
		//
		// Apply transformations.
		//
		$theRecord[ $symbol ]
			= SetLocalTransformations(
				$theRecord[ $symbol ], $prefix, $suffix );
		
		return 0;																	// ==>

	} // validateString.

	 
	/*===================================================================================
	 *	validateInteger																	*
	 *==================================================================================*/

	/**
	 * Validate integer
	 *
	 * This method will validate the provided integer property, it will first check if the
	 * value is numeric, if that is not the case, it will add a log to the provided
	 * transaction; if the value is correct, it will cast it to an integer.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateInteger( &$theTransaction,
										&$theRecord,
										 $theWorksheet,
										 $theRow,
										 $theFieldData,
										 $theFieldNode,
										 $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		
			//
			// Split elements.
			//
			$elements = ( strlen( $tokens ) )
					  ? explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] )
					  : array( $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Check value.
				//
				$element = SetLocalTransformations( $element, $prefix, $suffix );
				$ok = CheckIntegerValue( $element, $error_type, $error_message );
				
				//
				// Correct value.
				//
				if( $ok === TRUE )
					$result[] = $element;
				
				//
				// Invalid value.
				//
				elseif( $ok !== NULL )
				{
					$error++;
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_ERROR,							// Transaction status.
						$error_message,								// Transaction message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$element,									// Value.
						$error_type,								// Error type.
						$ok,										// Error code.
						NULL										// Error resource.
					);
				}
			
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Remove if empty.
				//
				if( count( $result ) )
					$theRecord[ $symbol ] = $result;
			
				//
				// Set value.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Check value.
			//
			$theRecord[ $symbol ]
				= SetLocalTransformations(
					$theRecord[ $symbol ], $prefix, $suffix );
			$ok = CheckIntegerValue( $theRecord[ $symbol ], $error_type, $error_message );
			
			//
			// Remove if empty.
			//
			if( $ok === NULL )
				unset( $theRecord[ $symbol ] );
			
			//
			// Invalid value.
			//
			elseif( $ok !== TRUE )
			{
				$error++;
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					$error_message,								// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					$error_type,								// Error type.
					$ok,										// Error code.
					NULL										// Error resource.
				);
			}
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateInteger.

	 
	/*===================================================================================
	 *	validateFloat																	*
	 *==================================================================================*/

	/**
	 * Validate float
	 *
	 * This method will validate the provided float property, it will first check if the
	 * value is numeric, if that is not the case, it will add a log to the provided
	 * transaction; if the value is correct, it will cast it to a double.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateFloat( &$theTransaction,
									  &$theRecord,
									   $theWorksheet,
									   $theRow,
									   $theFieldData,
									   $theFieldNode,
									   $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
			
			//
			// Split elements.
			//
			$elements = ( strlen( $tokens ) )
					  ? explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] )
					  : array( $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Check value.
				//
				$element = SetLocalTransformations( $element, $prefix, $suffix );
				$ok = CheckFloatValue( $element, $error_type, $error_message );
				
				//
				// Correct value.
				//
				if( $ok === TRUE )
					$result[] = $element;
				
				//
				// Invalid value.
				//
				elseif( $ok !== NULL )
				{
					$error++;
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_ERROR,							// Transaction status.
						$error_message,								// Transaction message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$element,									// Value.
						$error_type,								// Error type.
						$ok,										// Error code.
						NULL										// Error resource.
					);
				}
			
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Remove if empty.
				//
				if( count( $result ) )
					$theRecord[ $symbol ] = $result;
			
				//
				// Set value.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Check value.
			//
			$theRecord[ $symbol ]
				= SetLocalTransformations(
					$theRecord[ $symbol ], $prefix, $suffix );
			$ok = CheckFloatValue( $theRecord[ $symbol ], $error_type, $error_message );
			
			//
			// Remove if empty.
			//
			if( $ok === NULL )
				unset( $theRecord[ $symbol ] );
			
			//
			// Invalid value.
			//
			elseif( $ok !== TRUE )
			{
				$error++;
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					$error_message,								// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					$error_type,								// Error type.
					$ok,										// Error code.
					NULL										// Error resource.
				);
			}
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateFloat.

	 
	/*===================================================================================
	 *	validateBoolean																	*
	 *==================================================================================*/

	/**
	 * Validate boolean
	 *
	 * This method will validate the provided boolean property following these rules:
	 *
	 * <ul>
	 *	<li><tt>y</tt>: <tt>TRUE</tt>.
	 *	<li><tt>n</tt>: <tt>FALSE</tt>.
	 *	<li><tt>yes</tt>: <tt>TRUE</tt>.
	 *	<li><tt>no</tt>: <tt>FALSE</tt>.
	 *	<li><tt>true</tt>: <tt>TRUE</tt>.
	 *	<li><tt>false</tt>: <tt>FALSE</tt>.
	 *	<li><tt>1</tt>: <tt>TRUE</tt>.
	 *	<li><tt>0</tt>: <tt>FALSE</tt>.
	 * </ul>
	 *
	 * If the value does not match the above values, the methos will issue an error.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateBoolean( &$theTransaction,
										&$theRecord,
										 $theWorksheet,
										 $theRow,
										 $theFieldData,
										 $theFieldNode,
										 $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
			
			//
			// Split elements.
			//
			$elements = ( strlen( $tokens ) )
					  ? explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] )
					  : array( $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Check value.
				//
				$element = SetLocalTransformations( $element, $prefix, $suffix );
				$ok = CheckBooleanValue( $element, $error_type, $error_message );
				
				//
				// Correct value.
				//
				if( $ok === TRUE )
					$result[] = $element;
				
				//
				// Invalid value.
				//
				elseif( $ok !== NULL )
				{
					$error++;
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_ERROR,							// Transaction status.
						$error_message,								// Transaction message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$element,									// Value.
						$error_type,								// Error type.
						$ok,										// Error code.
						NULL										// Error resource.
					);
				}
			
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Remove if empty.
				//
				if( count( $result ) )
					$theRecord[ $symbol ] = $result;
			
				//
				// Set value.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Check value.
			//
			$theRecord[ $symbol ]
				= SetLocalTransformations(
					$theRecord[ $symbol ], $prefix, $suffix );
			$ok = CheckBooleanValue( $theRecord[ $symbol ], $error_type, $error_message );
			
			//
			// Remove if empty.
			//
			if( $ok === NULL )
				unset( $theRecord[ $symbol ] );
			
			//
			// Invalid value.
			//
			elseif( $ok !== TRUE )
			{
				$error++;
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					$error_message,								// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					$error_type,								// Error type.
					$ok,										// Error code.
					NULL										// Error resource.
				);
			}
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateBoolean.

	 
	/*===================================================================================
	 *	validateStruct																	*
	 *==================================================================================*/

	/**
	 * Validate structure
	 *
	 * Structures cannot be expressed in a template cell, the method will raise an
	 * exception.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> correct value.
	 */
	protected function validateStruct( &$theTransaction,
									   &$theRecord,
										$theWorksheet,
										$theRow,
										$theFieldData,
										$theFieldNode,
										$theFieldTag )
	{
		//
		// Init local storage.
		//
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		
		throw new \Exception(
			"Cannot set $symbol value: "
		   ."it is a structure, invalid template definition." );				// !@! ==>

	} // validateStruct.

	 
	/*===================================================================================
	 *	validateArray																	*
	 *==================================================================================*/

	/**
	 * Validate array
	 *
	 * This method will validate the provided array property, it will load the
	 * {@link kTAG_TOKEN} element of the node in order to separate array elements.
	 *
	 * Depending on the number of tokens:
	 *
	 * <ul>
	 *	<li><tt>0</tt>: If there are no tokens, an error will be issued.
	 *	<li><tt>1</tt>: The token will be used to separate eventual list elements, in which
	 *		case the array will have a single keyless element.
	 *	<li><tt>2</tt>: In case of a list property, the first token will be used to split
	 *		list elements and the second to split array elements; if not a list property,
	 *		the first token will be used to split array elements and the second to split the
	 *		element key from the value.
	 *	<li><tt>3</tt>: In case of a list property, the first token will be used to split
	 *		list elements, the second to split array elements and the third to split the
	 *		element key from the value; if not a list property, the last two tokens will be
	 *		used respectively to split array elements and key/value pairs.
	 * </ul>
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateArray( &$theTransaction,
									  &$theRecord,
									   $theWorksheet,
									   $theRow,
									   $theFieldData,
									   $theFieldNode,
									   $theFieldTag )
	{
		//
		// Init local storage.
		//
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		$count = strlen( $tokens );
		
		//
		// Handle missing tokens.
		//
		if( ! $count )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Missing separator tokens in template.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				NULL,										// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
				NULL										// Error resource.
			);
			
			return 1;																// ==>
		}
		
		//
		// Handle too many tokens.
		//
		if( $count > 3 )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Too many tokens in template definition.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				$tokens,									// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
				NULL										// Error resource.
			);
			
			return 1;																// ==>
		}
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Split list.
			//
			$tmp = $theRecord[ $symbol ];
			if( ! CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) ) )
			{
				unset( $theRecord[ $symbol ] );
				
				return 0;															// ==>
			}
			
			//
			// Iterate list.
			//
			$theRecord[ $symbol ] = Array();
			foreach( $tmp as $element )
			{
				//
				// Only list separator.
				//
				if( $count == 1 )
					$theRecord[ $symbol ][]
						= array( SetLocalTransformations( $element, $prefix, $suffix ) );
				
				//
				// Liat and element separator.
				//
				elseif( CheckArrayValue( $element,substr( $tokens, 1 ) ) )
					$theRecord[ $symbol ][]
						= SetLocalTransformations( $element, $prefix, $suffix );
			}
			
			//
			// Handle empty set.
			//
			if( ! count( $theRecord[ $symbol ] ) )
				unset( $theRecord[ $symbol ] );
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Normalise tokens.
			//
			if( $count == 3 )
				$tokens = substr( $tokens, 1 );
			
			//
			// Split.
			//
			if( ! CheckArrayValue( $theRecord[ $symbol ], $tokens ) )
				unset( $theRecord[ $symbol ] );
			
			//
			// Apply transformations.
			//
			else
				$theRecord[ $symbol ]
					= SetLocalTransformations( $theRecord[ $symbol ], $prefix, $suffix );
		
		} // Scalar.
		
		return 0;																	// ==>

	} // validateArray.

	 
	/*===================================================================================
	 *	validateLanguageString															*
	 *==================================================================================*/

	/**
	 * Validate language string
	 *
	 * This method will validate the provided language string property, it will load the
	 * {@link kTAG_TOKEN} element of the node in order to separate elements.
	 *
	 * Depending on the number of tokens:
	 *
	 * <ul>
	 *	<li><tt>0</tt>: If there are no tokens, an error will be issued.
	 *	<li><tt>1</tt>: The token will be used to separate eventual list elements, in which
	 *		case there will be one string without language.
	 *	<li><tt>2</tt>: In case of a list property, the first token will be used to split
	 *		list elements and the second to split strings; if not a list property, the first
	 *		token will be used to split elements and the second to split the language from
	 *		the string.
	 *	<li><tt>3</tt>: In case of a list property, the first token will be used to split
	 *		list elements, the second to split elements and the third to split the language
	 *		from the string; if not a list property, the last two tokens will be used
	 *		respectively to split elements and language/string pairs.
	 * </ul>
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateLanguageString( &$theTransaction,
											   &$theRecord,
												$theWorksheet,
												$theRow,
												$theFieldData,
												$theFieldNode,
												$theFieldTag )
	{
		//
		// Init local storage.
		//
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		$count = strlen( $tokens );
		
		//
		// Handle missing tokens.
		//
		if( ! $count )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Missing separator tokens in template.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				NULL,										// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
				NULL										// Error resource.
			);
			
			return 1;																// ==>
		}
		
		//
		// Handle too many tokens.
		//
		if( $count > 3 )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Too many tokens in template definition.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				$tokens,									// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
				NULL										// Error resource.
			);
			
			return 1;																// ==>
		}
		
		//
		// Handle list.
		//
		$tmp = $theRecord[ $symbol ];
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Split list.
			//
			if( ! CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) ) )
			{
				unset( $theRecord[ $symbol ] );
				
				return 0;															// ==>
			}
			
			//
			// Iterate list.
			//
			$theRecord[ $symbol ] = Array();
			$value_reference = & $theRecord[ $symbol ];
			foreach( $tmp as $list )
			{
				//
				// Allocate list element.
				//
				$index = count( $value_reference );
				$value_reference[ $index ] = Array();
				$list_reference = & $value_reference[ $index ];
				
				//
				// No string separator token.
				//
				if( $count == 1 )
					SetLanguageString(
						$list_reference,
						NULL,
						SetLocalTransformations( $list, $prefix, $suffix ) );
				
				//
				// Has string separator token.
				//
				else
				{
					//
					// Split strings.
					//
					if( CheckArrayValue( $list, substr( $tokens, 1, 1 ) ) )
					{
						//
						// Iterate strings.
						//
						foreach( $list as $element )
						{
							//
							// Has not language separator token.
							//
							if( $count == 2 )
								SetLanguageString(
									$list_reference,
									NULL,
									SetLocalTransformations( $element, $prefix, $suffix ) );
							
							//
							// Has language separator token.
							//
							elseif( CheckArrayValue( $element, substr( $tokens, 2, 1 ) ) )
							{
								//
								// Handle mess.
								//
								if( count( $element ) > 2 )
								{
									$lang = $element[ 0 ];
									array_shift( $element );
									$text = implode( substr( $tokens, 2, 1 ), $element );
								}
								
								//
								// No language.
								//
								elseif( count( $element ) == 1 )
								{
									$lang = NULL;
									$text = $element[ 0 ];
								}
								
								//
								// Has language.
								//
								else
								{
									$lang = $element[ 0 ];
									$text = $element[ 1 ];
								}
								
								//
								// Set value.
								//
								SetLanguageString(
									$list_reference,
									$lang,
									SetLocalTransformations( $text, $prefix, $suffix ) );
							
							} //Has language separator token.
						
						} // Iterating strings.
					
					} // Split srings.
				
				} // Has string separator token.
			
			} // Iterating list elements.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Normalise tokens.
			//
			if( $count == 3 )
				$tokens = substr( $tokens, 1 );
			
			//
			// Split strings.
			//
			if( ! CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) ) )
			{
				unset( $theRecord[ $symbol ] );
				return 0;															// ==>
			}
			
			//
			// Iterate strings.
			//
			$theRecord[ $symbol ] = Array();
			foreach( $tmp as $element )
			{
				//
				// No language.
				//
				if( $count == 1 )
					SetLanguageString(
						$theRecord[ $symbol ],
						NULL,
						SetLocalTransformations( $element, $prefix, $suffix ) );
				
				//
				// Has language.
				//
				else
				{
					//
					// Split language.
					//
					if( CheckArrayValue( $element, substr( $tokens, 1, 1 ) ) )
					{
						//
						// String is split.
						//
						if( count( $element ) > 2 )
						{
							$lang = $element[ 0 ];
							array_shift( $element );
							$text = implode( substr( $tokens, 1, 1 ), $element );
						
						} // String is split.
						
						//
						// Missing language.
						//
						if( count( $element ) == 1 )
						{
							$lang = NULL;
							$text = $element[ 0 ];
						}
						
						//
						// Has language.
						//
						else
						{
							$lang = $element[ 0 ];
							$text = $element[ 1 ];
						}
						
						//
						// Set value.
						//
						SetLanguageString(
							$theRecord[ $symbol ],
							$lang,
							SetLocalTransformations( $text, $prefix, $suffix ) );
					
					} // Not empty.
				
				} // Has language.
			
			} // Iterating strings.
		
		} // Scalar.
		
		//
		// Handle empty set.
		//
		if( ! count( $theRecord[ $symbol ] ) )
			unset( $theRecord[ $symbol ] );
		
		return 0;																	// ==>

	} // validateLanguageString.

	 
	/*===================================================================================
	 *	validateLanguageStrings															*
	 *==================================================================================*/

	/**
	 * Validate language strings
	 *
	 * This method will validate the provided language strings property, it will load the
	 * {@link kTAG_TOKEN} element of the node in order to separate elements.
	 *
	 * Depending on the number of tokens:
	 *
	 * <ul>
	 *	<li><tt>0</tt>: If there are no tokens, an error will be issued.
	 *	<li><tt>1</tt>: The token will be used to separate eventual list elements, in which
	 *		case there will be one string without language.
	 *	<li><tt>2</tt>: In case of a list property, the first token will be used to split
	 *		list elements and the second to split strings; if not a list property, the first
	 *		token will be used to split elements and the second to split the language from
	 *		the string.
	 *	<li><tt>3</tt>: In case of a list property, the first token will be used to split
	 *		list elements, the second to split elements and the third to split the language
	 *		from the strings; if not a list property, the first token will be used to split
	 *		string blocks, the second to split the language and the third to split the list
	 *		of strings.
	 *	<li><tt>4</tt>: In case of a list property, the first token will be used to split
	 *		list elements, the second to split string blocks, the third to split the
	 *		language and the fourth to split the list of strings; if not a list property,
	 *		the last three tokens will be used.
	 * </ul>
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateLanguageStrings( &$theTransaction,
												&$theRecord,
												 $theWorksheet,
												 $theRow,
												 $theFieldData,
												 $theFieldNode,
												 $theFieldTag )
	{
		//
		// Init local storage.
		//
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		$count = strlen( $tokens );
		
		//
		// Handle missing tokens.
		//
		if( ! $count )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Missing separator tokens in template.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				NULL,										// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
				NULL										// Error resource.
			);
			
			return 1;																// ==>
		}
		
		//
		// Handle too many tokens.
		//
		if( $count > 4 )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Too many tokens in template definition.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				$tokens,									// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
				NULL										// Error resource.
				);
			
			return 1;																// ==>
		}
		
		//
		// Handle list.
		//
		$tmp = $theRecord[ $symbol ];
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Split list.
			//
			if( ! CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) ) )
			{
				unset( $theRecord[ $symbol ] );
				
				return 0;															// ==>
			}
			
			//
			// Iterate list.
			//
			$theRecord[ $symbol ] = Array();
			$value_reference = & $theRecord[ $symbol ];
			foreach( $tmp as $list )
			{
				//
				// Allocate list element.
				//
				$index = count( $value_reference );
				$value_reference[ $index ] = Array();
				$list_reference = & $value_reference[ $index ];
				
				//
				// No string separator token.
				//
				if( $count == 1 )
					SetLanguageStrings(
						$list_reference,
						NULL,
						SetLocalTransformations( array( $list ), $prefix, $suffix ) );
				
				//
				// Has string separator token.
				//
				else
				{
					//
					// Split strings.
					//
					if( CheckArrayValue( $list, substr( $tokens, 1, 1 ) ) )
					{
						//
						// Iterate strings.
						//
						foreach( $list as $element )
						{
							//
							// Has not language separator token.
							//
							if( $count == 2 )
								SetLanguageStrings(
									$list_reference,
									NULL,
									SetLocalTransformations(
										array( $element ), $prefix, $suffix ) );
							
							//
							// Has language separator token.
							//
							elseif( CheckArrayValue( $element, substr( $tokens, 2, 1 ) ) )
							{
								//
								// No language.
								//
								if( count( $element ) == 1 )
									SetLanguageStrings(
										$list_reference,
										NULL,
										SetLocalTransformations(
											array( $element[ 0 ] ), $prefix, $suffix ) );
								
								//
								// Has language.
								//
								else
								{
									//
									// Is a mess.
									//
									if( count( $element ) > 2 )
									{
										$lang = $element[ 0 ];
										array_shift( $element );
										$text = implode( substr( $tokens, 2, 1 ),
														 $element );
										$element = array( $lang, $text );
									
									} // String is split.
									
									//
									// Has no strings list separator.
									//
									if( $count == 3 )
										SetLanguageStrings(
											$list_reference,
											$element[ 0 ],
											SetLocalTransformations(
												array( $element[ 1 ] ),
												$prefix,
												$suffix ) );
									
									//
									// Has strings list separator.
									//
									else
									{
										//
										// Split strings.
										//
										if( CheckArrayValue( $element[ 1 ],
															 substr( $tokens, 3, 1 ) ) )
											SetLanguageStrings(
												$list_reference,
												$element[ 0 ],
												SetLocalTransformations(
													$element[ 1 ],
													$prefix,
													$suffix ) );
									
									} // Has strings list separator.
								
								} // Has language.
							
							} //Has language separator token.
						
						} // Iterating strings.
					
					} // Split srings.
				
				} // Has string separator token.
			
			} // Iterating list elements.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Normalise tokens.
			//
			if( $count == 4 )
				$tokens = substr( $tokens, 1 );
			
			//
			// Split strings.
			//
			if( ! CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) ) )
			{
				unset( $theRecord[ $symbol ] );
				return 0;															// ==>
			}
			
			//
			// Iterate strings.
			//
			$theRecord[ $symbol ] = Array();
			foreach( $tmp as $element )
			{
				//
				// No language separator.
				//
				if( $count == 1 )
					SetLanguageStrings(
						$theRecord[ $symbol ],
						NULL,
						SetLocalTransformations( array( $element ), $prefix, $suffix ) );
				
				//
				// Has language.
				//
				else
				{
					//
					// Split language.
					//
					if( CheckArrayValue( $element, substr( $tokens, 1, 1 ) ) )
					{
						//
						// String is split.
						//
						if( count( $element ) > 2 )
						{
							$lang = $element[ 0 ];
							array_shift( $element );
							$text = implode( substr( $tokens, 1, 1 ), $element );
							$element = array( $lang, $text );
						
						} // String is split.
						
						//
						// Init elements.
						//
						if( count( $element ) == 1 )
						{
							$lang = NULL;
							$text = $element[ 0 ];
						}
						else
						{
							$lang = $element[ 0 ];
							$text = $element[ 1 ];
						}
						
						//
						// Has no strings list separator.
						//
						if( $count == 2 )
							SetLanguageStrings(
								$theRecord[ $symbol ],
								$lang,
								SetLocalTransformations(
									array( $text ), $prefix, $suffix ) );
						
						//
						// Has strings list separator.
						//
						else
						{
							//
							// Split strings list.
							//
							if( CheckArrayValue( $text, substr( $tokens, 2, 1 ) ) )
								SetLanguageStrings(
									$theRecord[ $symbol ],
									$lang,
									SetLocalTransformations( $text, $prefix, $suffix ) );
						
						} // Has strings list separator.
					
					} // Not empty.
				
				} // Has language.
			
			} // Iterating strings.
		
		} // Scalar.
		
		//
		// Handle empty set.
		//
		if( ! count( $theRecord[ $symbol ] ) )
			unset( $theRecord[ $symbol ] );
		
		return 0;																	// ==>

	} // validateLanguageStrings.

	 
	/*===================================================================================
	 *	validateTypedList																*
	 *==================================================================================*/

	/**
	 * Validate typed list
	 *
	 * This method will validate the provided language string property, it will load the
	 * {@link kTAG_TOKEN} element of the node in order to separate the elements and the
	 * type from the value.
	 *
	 * Depending on the number of tokens:
	 *
	 * <ul>
	 *	<li><tt>0</tt>: If there are no tokens, an error will be issued.
	 *	<li><tt>1</tt>: The token will be used to separate eventual list elements, in which
	 *		case the typed list will have a single typeless element.
	 *	<li><tt>2</tt>: In case of a list property, the first token will be used to split
	 *		list elements and the second to split typed list elements; if not a list
	 *		property, the first token will be used to split typed list elements and the
	 *		second to split the type from the value.
	 *	<li><tt>3</tt>: In case of a list property, the first token will be used to split
	 *		list elements, the second to split typed list elements and the third to split
	 *		the type from the value; if not a list property, the last two tokens will be
	 *		used respectively to split typed list elements and type/value pairs.
	 * </ul>
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateTypedList( &$theTransaction,
										  &$theRecord,
										   $theWorksheet,
										   $theRow,
										   $theFieldData,
										   $theFieldNode,
										   $theFieldTag )
	{
		//
		// Init local storage.
		//
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		$count = strlen( $tokens );
		
		//
		// Handle missing tokens.
		//
		if( ! $count )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Missing separator tokens in template.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				NULL,										// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
				NULL										// Error resource.
			);
			
			return 1;																// ==>
		}
		
		//
		// Handle too many tokens.
		//
		if( $count > 3 )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Too many tokens in template definition.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				$tokens,									// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
				NULL										// Error resource.
			);
			
			return 1;																// ==>
		}
		
		//
		// Handle list.
		//
		$tmp = $theRecord[ $symbol ];
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Split list.
			//
			if( ! CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) ) )
			{
				unset( $theRecord[ $symbol ] );
				
				return 0;															// ==>
			}
			
			//
			// Iterate list.
			//
			$theRecord[ $symbol ] = Array();
			$value_reference = & $theRecord[ $symbol ];
			foreach( $tmp as $list )
			{
				//
				// Allocate list element.
				//
				$index = count( $value_reference );
				$value_reference[ $index ] = Array();
				$list_reference = & $value_reference[ $index ];
				
				//
				// No string separator token.
				//
				if( $count == 1 )
					SetTypedList(
						$list_reference,
						kTAG_TEXT,
						NULL,
						SetLocalTransformations(
							$list, $prefix, $suffix ) );
				
				//
				// Has string separator token.
				//
				else
				{
					//
					// Split strings.
					//
					if( CheckArrayValue( $list, substr( $tokens, 1, 1 ) ) )
					{
						//
						// Iterate strings.
						//
						foreach( $list as $element )
						{
							//
							// Has not type separator token.
							//
							if( $count == 2 )
								SetTypedList(
									$list_reference,
									kTAG_TEXT,
									NULL,
									SetLocalTransformations(
										$element, $prefix, $suffix ) );
							
							//
							// Has type separator token.
							//
							elseif( CheckArrayValue( $element, substr( $tokens, 2, 1 ) ) )
							{
								//
								// No type.
								//
								if( count( $element ) == 1 )
									SetTypedList(
										$list_reference,
										kTAG_TEXT,
										NULL,
										SetLocalTransformations(
											$element[ 0 ], $prefix, $suffix ) );
								
								//
								// Has type.
								//
								if( count( $element ) == 2 )
									SetTypedList(
										$list_reference,
										kTAG_TEXT,
										$element[ 0 ],
										SetLocalTransformations(
											$element[ 1 ], $prefix, $suffix ) );
								
								//
								// Is a mess.
								//
								else
								{
									$type = $element[ 0 ];
									array_shift( $element );
									$value = implode( substr( $tokens, 2, 1 ), $element );
									SetTypedList(
										$list_reference,
										kTAG_TEXT,
										$type,
										SetLocalTransformations(
											$value, $prefix, $suffix ) );
								
								} // String is split.
							
							} // Has type separator token.
						
						} // Iterating strings.
					
					} // Split srings.
				
				} // Has string separator token.
			
			} // Iterating list elements.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Normalise tokens.
			//
			if( $count == 3 )
				$tokens = substr( $tokens, 1 );
			
			//
			// Split strings.
			//
			if( ! CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) ) )
			{
				unset( $theRecord[ $symbol ] );
				return 0;															// ==>
			}
			
			//
			// Iterate strings.
			//
			$theRecord[ $symbol ] = Array();
			foreach( $tmp as $element )
			{
				//
				// No type.
				//
				if( $count == 1 )
					SetTypedList(
						$theRecord[ $symbol ],
						kTAG_TEXT,
						NULL,
						SetLocalTransformations(
							$element, $prefix, $suffix ) );
				
				//
				// Has type.
				//
				else
				{
					//
					// Split type.
					//
					if( CheckArrayValue( $element, substr( $tokens, 1, 1 ) ) )
					{
						//
						// String is split.
						//
						if( count( $element ) > 2 )
						{
							$type = $element[ 0 ];
							array_shift( $element );
							$value = implode( substr( $tokens, 1, 1 ), $element );
							$element = array( $type, $value );
						
						} // String is split.
						
						//
						// Missing type.
						//
						if( count( $element ) == 1 )
							SetTypedList(
								$theRecord[ $symbol ],
								kTAG_TEXT,
								NULL,
								SetLocalTransformations(
									$element[ 0 ], $prefix, $suffix ) );
						
						//
						// Has type.
						//
						else
							SetTypedList(
								$theRecord[ $symbol ],
								kTAG_TEXT,
								$element[ 0 ],
								SetLocalTransformations(
									$element[ 1 ], $prefix, $suffix ) );
					
					} // Not empty.
				
				} // Has type.
			
			} // Iterating strings.
		
		} // Scalar.
		
		//
		// Handle empty set.
		//
		if( ! count( $theRecord[ $symbol ] ) )
			unset( $theRecord[ $symbol ] );
		
		return 0;																	// ==>

	} // validateTypedList.

	 
	/*===================================================================================
	 *	validateShape																	*
	 *==================================================================================*/

	/**
	 * Validate shape
	 *
	 * This method will validate the provided shape property, by default a shape is
	 * provided as a string of the form <tt>type</tt>=geometry where the equal
	 * (<tt>=</tt>) sign separates the shape type from the geometry, the semicolon
	 * (<tt>;</tt>) separates longitude/latitude pairs, the comma (<tt>,</tt>) separates the
	 * longitude from the latitude and the colon (<tt>:</tt>) separates the eventual linear
	 * ring coordinate arrays.
	 *
	 * These are the valid shape types:
	 *
	 * <ul>
	 *	<tt>Point</tt>: A point <tt>Point=lon,lat</tt>.
	 *	<tt>Circle</tt>: A circle <tt>Circle=lon,lat,radius</tt>.
	 *	<tt>MultiPoint</tt>: A collection of points <tt>MultiPoint=lon,lat;lon,lat...</tt>.
	 *	<tt>LineString</tt>: A collection of lines <tt>LineString=lon,lat;lon,lat...</tt>,
	 *		in this case there must be at least two pairs of coordinates.
	 *	<tt>Polygon</tt>: A polygon <tt>Polygon=lon,lat;lon,lat:lon,lat;lon,lat...</tt>,
	 *		where the colon (<tt>:</tt>) separates the linear ring coordinate arrays: the
	 *		first coordinate array represents the exterior ring, the other eventual elements
	 *		the interior rings or holes.
	 * </ul>
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateShape( &$theTransaction,
									  &$theRecord,
									   $theWorksheet,
									   $theRow,
									   $theFieldData,
									   $theFieldNode,
									   $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
			
			//
			// Split elements.
			//
			$elements = ( strlen( $tokens ) )
					  ? explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] )
					  : array( $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Check value.
				//
				$ok = CheckShapeValue( $element, $error_type, $error_message );
				
				//
				// Correct value.
				//
				if( $ok === TRUE )
					$result[] = $element;
				
				//
				// Invalid value.
				//
				elseif( $ok !== NULL )
				{
					$error++;
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_ERROR,							// Transaction status.
						$error_message,								// Transaction message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$element,									// Value.
						$error_type,								// Error type.
						$ok,										// Error code.
						NULL										// Error resource.
					);
				}
			
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Remove if empty.
				//
				if( count( $result ) )
					$theRecord[ $symbol ] = $result;
			
				//
				// Set value.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Check value.
			//
			$ok = CheckShapeValue( $theRecord[ $symbol ], $error_type, $error_message );
			
			//
			// Remove if empty.
			//
			if( $ok === NULL )
				unset( $theRecord[ $symbol ] );
			
			//
			// Invalid value.
			//
			elseif( $ok !== TRUE )
			{
				$error++;
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					$error_message,								// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					$error_type,								// Error type.
					$ok,										// Error code.
					NULL										// Error resource.
				);
			}
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateShape.

	 
	/*===================================================================================
	 *	validateLink																	*
	 *==================================================================================*/

	/**
	 * Validate link
	 *
	 * This method will validate the provided URL property, it will load the link headers to
	 * check if the URL is active, if that is not the case, the method will issue a
	 * warning.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateLink( &$theTransaction,
									 &$theRecord,
									  $theWorksheet,
									  $theRow,
									  $theFieldData,
									  $theFieldNode,
									  $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		
			//
			// Handle missing tokens.
			//
			if( ! strlen( $tokens ) )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_WARNING,						// Transaction status.
					'Missing separator tokens in template.',	// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					NULL,										// Value.
					kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
					kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
					NULL										// Error resource.
				);
				
				return 0;															// ==>
			}
		
			//
			// Handle too many tokens.
			//
			if( strlen( $tokens ) > 1 )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_WARNING,						// Transaction status.
					'Too many tokens in template definition.',	// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$tokens,									// Value.
					kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
					kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
					NULL										// Error resource.
				);
				
				return 0;															// ==>
			}
		
			//
			// Split elements.
			//
			$elements = explode( $tokens, $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Check link.
				//
				$element = SetLocalTransformations( $element, $prefix, $suffix );
				$ok = CheckLinkValue( $element, $error_type, $error_message );
				
				//
				// Skip empty.
				//
				if( $ok !== NULL )
					$result[] = $element;
				
				//
				// Handle error.
				//
				elseif( $ok !== TRUE )
				{
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_WARNING,						// Transaction status.
						$error_message,								// Transaction message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$element,									// Value.
						$error_type,								// Error type.
						$ok,										// Error code.
						NULL										// Error resource.
					);
				}
		
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Remove if empty.
				//
				if( count( $result ) )
					$theRecord[ $symbol ] = $result;
			
				//
				// Set value.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Check link.
			//
			$theRecord[ $symbol ]
				= SetLocalTransformations( $theRecord[ $symbol ], $prefix, $suffix );
			$ok = CheckLinkValue( $theRecord[ $symbol ], $error_type, $error_message );
			
			//
			// Remove if empty.
			//
			if( $ok === NULL )
				unset( $theRecord[ $symbol ] );
			
			//
			// Handle error.
			//
			elseif( $ok !== TRUE )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					$error_message,								// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					$error_type,								// Error type.
					$ok,										// Error code.
					NULL										// Error resource.
				);
			}
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateLink.

	 
	/*===================================================================================
	 *	validateDate																	*
	 *==================================================================================*/

	/**
	 * Validate date
	 *
	 * This method will validate the provided date property, it will attempt to interpret
	 * the date if it was not provided in the expected manner and issue an error if the
	 * date is not valid.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateDate( &$theTransaction,
									 &$theRecord,
									  $theWorksheet,
									  $theRow,
									  $theFieldData,
									  $theFieldNode,
									  $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$error = FALSE;
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		
			//
			// Split elements.
			//
			$elements = ( strlen( $tokens ) )
					  ? explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] )
					  : array( $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Check value.
				//
				$element = SetLocalTransformations( $element, $prefix, $suffix );
				$ok = CheckDateValue( $element, $error_type, $error_message );
				
				//
				// Correct value.
				//
				if( $ok === TRUE )
					$result[] = $element;
				
				//
				// Invalid value.
				//
				elseif( $ok !== NULL )
				{
					$error++;
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_ERROR,							// Transaction status.
						$error_message,								// Transaction message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$element,									// Value.
						$error_type,								// Error type.
						$ok,										// Error code.
						NULL										// Error resource.
					);
				}
			
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Remove if empty.
				//
				if( count( $result ) )
					$theRecord[ $symbol ] = $result;
			
				//
				// Set value.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Check value.
			//
			$theRecord[ $symbol ]
				= SetLocalTransformations( $theRecord[ $symbol ], $prefix, $suffix );
			$ok = CheckDateValue( $theRecord[ $symbol ], $error_type, $error_message );
			
			//
			// Remove if empty.
			//
			if( $ok === NULL )
				unset( $theRecord[ $symbol ] );
			
			//
			// Invalid value.
			//
			elseif( $ok !== TRUE )
			{
				$error++;
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					$error_message,								// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					$error_type,								// Error type.
					$ok,										// Error code.
					NULL										// Error resource.
				);
			}
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateDate.

	 
	/*===================================================================================
	 *	validateEnum																	*
	 *==================================================================================*/

	/**
	 * Validate enumeration
	 *
	 * This method will validate the provided enumerated property, it will attempt to
	 * match the provided value with the {@link kTAG_PREFIX} and {@link kTAG_SUFFIX} node
	 * elements in the terms collection; if there is not a match, the method will issue an
	 * error.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateEnum( &$theTransaction,
									 &$theRecord,
									  $theWorksheet,
									  $theRow,
									  $theFieldData,
									  $theFieldNode,
									  $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		$collection
			= Term::ResolveCollection(
				Term::ResolveDatabase( $this->wrapper(), TRUE ),
				TRUE );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$results = $errors = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		
			//
			// Handle missing tokens.
			//
			if( ! strlen( $tokens ) )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_WARNING,						// Transaction status.
					'Missing separator tokens in template.',	// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					NULL,										// Value.
					kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
					kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
					NULL										// Error resource.
				);
				
				return 1;															// ==>
			}
		
			//
			// Handle too many tokens.
			//
			if( strlen( $tokens ) > 1 )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_WARNING,						// Transaction status.
					'Too many tokens in template definition.',	// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$tokens,									// Value.
					kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
					kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
					NULL										// Error resource.
				);
				
				return 1;															// ==>
			}
		
			//
			// Split elements.
			//
			$elements = ( strlen( $tokens ) )
					  ? explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] )
					  : array( $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Get combinations.
				//
				$matched = FALSE;
				$combinations = CheckStringCombinations( $element, $prefix, $suffix );
				foreach( $combinations as $combination )
				{
					//
					// Match enumeration.
					//
					$criteria = array( kTAG_NID => $combination );
					if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
					{
						//
						// Save matched.
						//
						$results[] = $combination;
						
						$matched = TRUE;
						break;												// =>
					
					} // Matched.
				
				} // Iterating combinations.
				
				//
				// Handle errors.
				//
				if( ! $matched )
				{
					$error++;
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
						kTYPE_STATUS_ERROR,							// Status.
						'Invalid code.',							// Message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$element,									// Value.
						kTYPE_ERROR_INVALID_CODE,					// Error type.
						kTYPE_ERROR_CODE_INVALID_ENUM,				// Error code.
						NULL										// Error res.
					);
				}
			
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Remove if empty.
				//
				if( count( $results ) )
					$theRecord[ $symbol ] = $results;
			
				//
				// Set value.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Get combinations.
			//
			$matched = FALSE;
			$combinations = CheckStringCombinations( $theRecord[ $symbol ],
													 $prefix,
													 $suffix );
			foreach( $combinations as $combination )
			{
				//
				// Match enumeration.
				//
				$criteria = array( kTAG_NID => $combination );
				if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
				{
					//
					// Save matched.
					//
					$theRecord[ $symbol ] = $combination;
					
					$matched = TRUE;
					break;													// =>
				
				} // Matched.
			
			} // Iterating combinations.
			
			//
			// Handle errors.
			//
			if( ! $matched )
			{
				$error++;
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					'Invalid code.',							// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					kTYPE_ERROR_INVALID_CODE,					// Error type.
					kTYPE_ERROR_CODE_INVALID_ENUM,				// Error code.
					NULL										// Error res.
				);
			}
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateEnum.

	 
	/*===================================================================================
	 *	validateEnumSet																	*
	 *==================================================================================*/

	/**
	 * Validate enumerated set
	 *
	 * This method will validate the provided enumerated set property, it will attempt to
	 * match the provided value with the {@link kTAG_PREFIX} and {@link kTAG_SUFFIX} node
	 * elements in the terms collection; if there is not a match, the method will issue an
	 * error.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateEnumSet( &$theTransaction,
										&$theRecord,
										 $theWorksheet,
										 $theRow,
										 $theFieldData,
										 $theFieldNode,
										 $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$results = Array();
		$error_value = NULL;
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$prefix = $theFieldNode->offsetGet( kTAG_PREFIX );
		$suffix = $theFieldNode->offsetGet( kTAG_SUFFIX );
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$count = strlen( $tokens );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		$collection
			= Term::ResolveCollection(
				Term::ResolveDatabase( $this->wrapper(), TRUE ),
				TRUE );
		
		//
		// Handle missing tokens.
		//
		if( ! $count )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Missing separator tokens in template.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				NULL,										// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
				NULL										// Error resource.
			);
			
			return 1;																// ==>
		}
		
		//
		// Handle too many tokens.
		//
		if( $count > 2 )
		{
			$this->failTransactionLog(
				$theTransaction,							// Transaction.
				$this->transaction(),						// Parent transaction.
				kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
				kTYPE_STATUS_WARNING,						// Transaction status.
				'Too many tokens in template definition.',	// Transaction message.
				$theWorksheet,								// Worksheet.
				$theRow,									// Row.
				$theFieldData[ 'column_name' ],				// Column.
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
				$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
				$tokens,									// Value.
				kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
				kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
				NULL										// Error resource.
			);
			
			return 1;																// ==>
		}
		
		//
		// Handle list.
		//
		$tmp = $theRecord[ $symbol ];
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Handle invalid token count.
			//
			if( $count != 2 )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_WARNING,						// Transaction status.
					'Template definition should have 2 tokens.',// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$tokens,									// Value.
					kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
					kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
					NULL										// Error resource.
				);
				
				return 1;															// ==>
			}
		
			//
			// Split list.
			//
			if( ! CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) ) )
			{
				unset( $theRecord[ $symbol ] );
				
				return 0;															// ==>
			}
			
			//
			// Iterate list.
			//
			foreach( $tmp as $list )
			{
				//
				// Init local storage.
				//
				$set = Array();
				
				//
				// Split set.
				//
				if( $count == 2 )
				{
					//
					// Split set.
					//
					if( ! CheckArrayValue( $list, substr( $tokens, 1, 1 ) ) )
						continue;											// =>
				
				} // Has set splitter.
				
				//
				// Has no set splitter.
				//
				else
					$list = array( $list );
				
				//
				// Iterate set.
				//
				foreach( $list as $element )
				{
					//
					// Check combinations.
					//
					$combinations = CheckStringCombinations( $element, $prefix, $suffix );
					if( count( $combinations ) )
					{
						//
						// Iterate combinations.
						//
						$matched = FALSE;
						foreach( $combinations as $combination )
						{
							//
							// Match enumeration.
							//
							$criteria = array( kTAG_NID => $combination );
							if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
							{
								//
								// Save matched.
								//
								$set[] = $combination;
					
								$matched = TRUE;
								break;										// =>
				
							} // Matched.
			
						} // Iterating combinations.
			
						//
						// Handle errors.
						//
						if( ! $matched )
						{
							$error++;
							$this->failTransactionLog(
								$theTransaction,							// Transaction.
								$this->transaction(),						// Parent.
								kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
								kTYPE_STATUS_ERROR,							// Status.
								'Invalid code.',							// Message.
								$theWorksheet,								// Worksheet.
								$theRow,									// Row.
								$theFieldData[ 'column_name' ],				// Column.
								$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
								$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
								$element,									// Value.
								kTYPE_ERROR_INVALID_CODE,					// Error type.
								kTYPE_ERROR_CODE_INVALID_ENUM,				// Error code.
								NULL										// Error res.
							);
						}
				
					} // Has combinations.
				
				} // Iterating set.
				
				//
				// Handle found.
				//
				if( count( $set ) )
					$results[] = $set;
			
			} // Iterating list.
			
			//
			// Handle no errors.
			//
			if( ! count( $error ) )
			{
				//
				// Set value.
				//
				if( count( $results ) )
					$theRecord[ $symbol ] = $results;
			
				//
				// Remove if empty.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Normalise tokens.
			//
			if( $count == 2 )
				$tokens = substr( $tokens, 1 );
			
			//
			// Split set.
			//
			if( ! CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) ) )
			{
				unset( $theRecord[ $symbol ] );
				
				return 0;															// ==>
			}
			
			//
			// Iterate set.
			//
			foreach( $tmp as $element )
			{
				//
				// Get combinations.
				//
				$combinations = CheckStringCombinations( $element, $prefix, $suffix );
				if( count( $combinations ) )
				{
					//
					// Iterate combinations.
					//
					$matched = FALSE;
					foreach( $combinations as $combination )
					{
						//
						// Match enumeration.
						//
						$criteria = array( kTAG_NID => $combination );
						if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
						{
							//
							// Save matched.
							//
							$results[] = $combination;
				
							$matched = TRUE;
							break;										// =>
			
						} // Matched.
		
					} // Iterating combinations.
		
					//
					// Handle errors.
					//
					if( ! $matched )
					{
						$error++;
						$this->failTransactionLog(
							$theTransaction,							// Transaction.
							$this->transaction(),						// Parent.
							kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
							kTYPE_STATUS_ERROR,							// Status.
							'Invalid code.',							// Message.
							$theWorksheet,								// Worksheet.
							$theRow,									// Row.
							$theFieldData[ 'column_name' ],				// Column.
							$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
							$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
							$element,									// Value.
							kTYPE_ERROR_INVALID_CODE,					// Error type.
							kTYPE_ERROR_CODE_INVALID_ENUM,				// Error code.
							NULL										// Error res.
						);
					}
			
				} // Has combinations.
			
			} // Iterating set.
			
			//
			// Set value.
			//
			if( ! $error )
				$theRecord[ $symbol ] = $results;
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateEnumSet.

	 
	/*===================================================================================
	 *	validateReference																*
	 *==================================================================================*/

	/**
	 * Validate reference
	 *
	 * This method will validate the provided reference property, it will attempt to
	 * match the provided value with the {@link kTAG_PREFIX} and {@link kTAG_SUFFIX} node
	 * elements, if available, in the target collection; if there is not a match, the method
	 * will issue an error.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theCollection		Collection name.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateReference( &$theTransaction,
										  &$theRecord,
										   $theCollection,
										   $theWorksheet,
										   $theRow,
										   $theFieldData,
										   $theFieldNode,
										   $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$prefix = $theFieldNode->offsetGet( kTAG_PREFIX );
		$suffix = $theFieldNode->offsetGet( kTAG_SUFFIX );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		$collection = $this->wrapper()->resolveCollection( $theCollection );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$results = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		
			//
			// Handle missing tokens.
			//
			if( ! strlen( $tokens ) )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_WARNING,						// Transaction status.
					'Missing separator tokens in template.',	// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					NULL,										// Value.
					kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
					kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
					NULL										// Error resource.
				);
				
				return 1;															// ==>
			}
		
			//
			// Handle too many tokens.
			//
			if( strlen( $tokens ) > 1 )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_WARNING,						// Transaction status.
					'Too many tokens in template definition.',	// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$tokens,									// Value.
					kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
					kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
					NULL										// Error resource.
				);
				
				return 1;															// ==>
			}
		
			//
			// Split elements.
			//
			$elements = explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Get combinations.
				//
				$matched = FALSE;
				$combinations = CheckStringCombinations( $element, $prefix, $suffix );
				foreach( $combinations as $combination )
				{
					//
					// Parse by collection.
					//
					switch( $theCollection )
					{
						case Node::kSEQ_NAME:
							if( ctype_digit( $combination ) )
							{
								$criteria = array( kTAG_NID => (int) $combination );
								$ok = $collection->matchOne( $criteria, kQUERY_NID );
							}
							else
								$ok
									= Node::GetPidNode(
										$this->wrapper(), $combination, kQUERY_NID );
							break;
						
						case Session::kSEQ_NAME:
						case Transaction::kSEQ_NAME:
						case FileObject::kSEQ_NAME:
							$combination = $collection->getObjectId( $combination );
							if( $combination === NULL )
							{
								$errors[] = $combination;
								continue;											// ==>
							}
							$criteria = array( kTAG_NID => $combination );
							if( $ok = $collection->matchOne( $criteria, kQUERY_NID ) )
								$ok = $collection->setObjectId( $ok );
						
						default:
							$criteria = array( kTAG_NID => $combination );
							$ok = $collection->matchOne( $criteria, kQUERY_NID );
							break;
					}
					
					//
					// Handle matched.
					//
					if( $ok )
					{
						//
						// Save matched.
						//
						$results[] = $ok;
						
						$matched = TRUE;
						break;												// =>
					
					} // Matched.
				
				} // Iterating combinations.
				
				//
				// Handle errors.
				//
				if( ! $matched )
				{
					$error++;
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
						kTYPE_STATUS_ERROR,							// Status.
						'Invalid reference.',						// Message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$element,									// Value.
						kTYPE_ERROR_INVALID_CODE,					// Error type.
						kTYPE_ERROR_CODE_INVALID_ENUM,				// Error code.
						NULL										// Error res.
					);
				}
			
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Set value.
				//
				if( count( $results ) )
					$theRecord[ $symbol ] = $results;
			
				//
				// Remove if empty.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Get combinations.
			//
			$matched = FALSE;
			$combinations = CheckStringCombinations( $theRecord[ $symbol ],
													 $prefix,
													 $suffix );
			foreach( $combinations as $combination )
			{
				//
				// Parse by collection.
				//
				switch( $theCollection )
				{
					case Node::kSEQ_NAME:
						if( ctype_digit( $combination ) )
						{
							$criteria = array( kTAG_NID => (int) $combination );
							$ok = $collection->matchOne( $criteria, kQUERY_NID );
						}
						else
							$ok
								= Node::GetPidNode(
									$this->wrapper(), $combination, kQUERY_NID );
						break;
					
					case Session::kSEQ_NAME:
					case Transaction::kSEQ_NAME:
					case FileObject::kSEQ_NAME:
						$combination = $collection->getObjectId( $combination );
						if( $combination === NULL )
							continue;										// =>
						$criteria = array( kTAG_NID => $combination );
						if( $ok = $collection->matchOne( $criteria, kQUERY_NID ) )
							$ok = $collection->setObjectId( $ok );
					
					default:
						$criteria = array( kTAG_NID => $combination );
						$ok = $collection->matchOne( $criteria, kQUERY_NID );
						break;
				}
				
				//
				// Handle matched.
				//
				if( $ok )
				{
					//
					// Save matched.
					//
					$theRecord[ $symbol ] = $ok;
					
					$matched = TRUE;
					break;													// =>
				
				} // Matched.
			
			} // Iterating combinations.
			
			//
			// Handle errors.
			//
			if( ! $matched )
			{
				$error++;
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
					kTYPE_STATUS_ERROR,							// Status.
					'Invalid reference.',						// Message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					kTYPE_ERROR_INVALID_CODE,					// Error type.
					kTYPE_ERROR_CODE_INVALID_ENUM,				// Error code.
					NULL										// Error res.
				);
			}
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateReference.

	 
	/*===================================================================================
	 *	validateObjectId																*
	 *==================================================================================*/

	/**
	 * Validate object identifier
	 *
	 * This method will validate the provided object identifier property and cast it to the
	 * native database type, we use here the current object's sessions collection.
	 *
	 * If the identifier is invalid, the method will issue an error.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateObjectId( &$theTransaction,
										 &$theRecord,
										  $theWorksheet,
										  $theRow,
										  $theFieldData,
										  $theFieldNode,
										  $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		$collection = $this->wrapper()->resolveCollection( Session::kSEQ_NAME );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		
			//
			// Handle missing tokens.
			//
			if( ! strlen( $tokens ) )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_WARNING,						// Transaction status.
					'Missing separator tokens in template.',	// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					NULL,										// Value.
					kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
					kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
					NULL										// Error resource.
				);
				
				return 1;															// ==>
			}
		
			//
			// Handle too many tokens.
			//
			if( strlen( $tokens ) > 1 )
			{
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_WARNING,						// Transaction status.
					'Too many tokens in template definition.',	// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$tokens,									// Value.
					kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
					kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
					NULL										// Error resource.
				);
				
				return 1;															// ==>
			}
		
			//
			// Split elements.
			//
			$elements = explode( $tokens, $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Trim value.
				//
				$element = trim( $element );
				if( strlen( $element ) )
				{
					//
					// Check identifier.
					//
					$id
						= $collection
							->getObjectId(
								SetLocalTransformations( $element, $prefix, $suffix ) );
					
					//
					// Handle error.
					//
					if( $id === NULL )
					{
						$error++;
						$this->failTransactionLog(
							$theTransaction,							// Transaction.
							$this->transaction(),						// Parent.
							kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
							kTYPE_STATUS_ERROR,							// Status.
							'Invalid object identifier.',				// Message.
							$theWorksheet,								// Worksheet.
							$theRow,									// Row.
							$theFieldData[ 'column_name' ],				// Column.
							$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
							$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
							$element,									// Value.
							kTYPE_ERROR_INVALID_VALUE,					// Error type.
							kTYPE_ERROR_CODE_INVALID_OBJECT_ID,			// Error code.
							NULL										// Error resource.
						);
					}
		
					//
					// Add value.
					//
					else
						$result[] = $id;
				
				} // Not empty.
		
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Remove if empty.
				//
				if( count( $result ) )
					$theRecord[ $symbol ] = $result;
			
				//
				// Set value.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Trim value.
			//
			$theRecord[ $symbol ] = trim( $theRecord[ $symbol ] );
			if( strlen( $theRecord[ $symbol ] ) )
			{
				//
				// Check identifier.
				//
				$id
					= $collection
						->getObjectId(
							SetLocalTransformations(
								$theRecord[ $symbol ], $prefix, $suffix ) );
				
				//
				// Handle error.
				//
				if( $id === NULL )
				{
					$error++;
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
						kTYPE_STATUS_ERROR,							// Status.
						'Invalid object identifier.',				// Message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$theRecord[ $symbol ],						// Value.
						kTYPE_ERROR_INVALID_VALUE,					// Error type.
						kTYPE_ERROR_CODE_INVALID_OBJECT_ID,			// Error code.
						NULL										// Error resource.
					);
				}
	
				//
				// Set value.
				//
				else
					$theRecord[ $symbol ] = $id;
			
			} // Not empty.
			
			//
			// Remove if empty.
			//
			else
				unset( $theRecord[ $symbol ] );
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateObjectId.

	 
	/*===================================================================================
	 *	validateTimeStamp																*
	 *==================================================================================*/

	/**
	 * Validate time stamp
	 *
	 * This method will validate the provided time stamp property and cast it to the
	 * native database type, we use here the current object's sessions collection.
	 *
	 * If the provided property is numeric, it will be interpreted as an integer
	 * representing the number of seconds since the epoch (Jan 1970 00:00:00.000 UTC); if
	 * not, the method will use the strtotime() function to interpret the string.
	 *
	 * If the time stamp is invalid, the method will issue an error.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Data record.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return int					Number of errors.
	 */
	protected function validateTimeStamp( &$theTransaction,
										 &$theRecord,
										  $theWorksheet,
										  $theRow,
										  $theFieldData,
										  $theFieldNode,
										  $theFieldTag )
	{
		//
		// Init local storage.
		//
		$error = 0;
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		GetLocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		$collection = $this->wrapper()->resolveCollection( Session::kSEQ_NAME );
		
		//
		// Handle list.
		//
		if( is_array( $kind )
		 && in_array( kTYPE_LIST, $kind ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		
			//
			// Handle missing tokens.
			//
			if( ! strlen( $tokens ) )
				return
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_WARNING,						// Transaction status.
						'Missing separator tokens in template.',	// Transaction message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						NULL,										// Value.
						kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
						kTYPE_ERROR_CODE_NO_TOKEN,					// Error code.
						NULL										// Error resource.
					);																// ==>
		
			//
			// Handle too many tokens.
			//
			if( strlen( $tokens ) > 1 )
				return
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_WARNING,						// Transaction status.
						'Too many tokens in template definition.',	// Transaction message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$tokens,									// Value.
						kTYPE_ERROR_BAD_TMPL_STRUCT,				// Error type.
						kTYPE_ERROR_CODE_BAD_TOKENS,				// Error code.
						NULL										// Error resource.
					);																// ==>
		
			//
			// Split elements.
			//
			$elements = explode( $tokens, $theRecord[ $symbol ] );
			
			//
			// Compile results.
			//
			foreach( $elements as $element )
			{
				//
				// Trim value.
				//
				$element = trim( $element );
				if( strlen( $element ) )
				{
					//
					// Check timestamp.
					//
					$time
						= $collection
							->getTimeStamp(
								SetLocalTransformations( $element, $prefix, $suffix ) );
					
					//
					// Handle errors.
					//
					if( $time === FALSE )
					{
						$error++;
						$this->failTransactionLog(
							$theTransaction,								// Transaction.
							$this->transaction(),							// Parent transaction.
							kTYPE_TRANS_TMPL_WORKSHEET_ROW,					// Transaction type.
							kTYPE_STATUS_WARNING,							// Transaction status.
							'Invalid time-stamp.',							// Transaction message.
							$theWorksheet,									// Worksheet.
							$theRow,										// Row.
							$theFieldData[ 'column_name' ],					// Column.
							$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
							$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
							$element,										// Value.
							kTYPE_ERROR_INVALID_VALUE,						// Error type.
							kTYPE_ERROR_CODE_INVALID_TIME_STAMP,			// Error code.
							NULL											// Error resource.
						);
					}
		
					//
					// Add value.
					//
					else
						$result[] = $time;
				
				} // Not empty.
		
			} // Iterating elements.
			
			//
			// Handle no errors.
			//
			if( ! $error )
			{
				//
				// Remove if empty.
				//
				if( count( $result ) )
					$theRecord[ $symbol ] = $result;
			
				//
				// Set value.
				//
				else
					unset( $theRecord[ $symbol ] );
			
			} // No errors.
		
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Trim value.
			//
			$theRecord[ $symbol ] = trim( $theRecord[ $symbol ] );
			if( strlen( $theRecord[ $symbol ] ) )
			{
				//
				// Check stamp.
				//
				$time
					= $collection
						->getTimeStamp(
							SetLocalTransformations(
								$theRecord[ $symbol ], $prefix, $suffix ) );
				
				//
				// Handle errors.
				//
				if( $time === FALSE )
				{
					$error++;
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent transaction.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
						kTYPE_STATUS_WARNING,						// Transaction status.
						'Invalid time-stamp.',						// Transaction message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$theRecord[ $symbol ],						// Value.
						kTYPE_ERROR_INVALID_VALUE,					// Error type.
						kTYPE_ERROR_CODE_INVALID_TIME_STAMP,		// Error code.
						NULL										// Error resource.
					);
				}
	
				//
				// Set value.
				//
				else
					$theRecord[ $symbol ] = $time;
			
			} // Not empty.
			
			//
			// Remove if empty.
			//
			else
				unset( $theRecord[ $symbol ] );
		
		} // Scalar value.
		
		return $error;																// ==>

	} // validateTimeStamp.

	

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
	
	
	/*===================================================================================
	 *	setWorksheetProperties															*
	 *==================================================================================*/

	/**
	 * Set worksheets properties
	 *
	 * This method will traverse the provided list of child worksheets loading the provided
	 * object's properties.
	 *
	 * @param mixed				   &$theObject			Object or array.
	 * @param array					$theWorksheets		List of child worksheets.
	 * @param mixed					$theParentIndex		Parent index field value.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	protected function setWorksheetProperties( &$theObject,
												$theWorksheets,
												$theParentIndex )
	{
		//
		// Iterate worksheets.
		//
		foreach( $theWorksheets as $current )
		{
			//
			// Init local storage.
			//
			$container = Array();
			$current_info = $this->mIterator->getList()[ $current[ 'N' ] ];
			$parent_info = $this->mIterator->getList()[ $current_info[ 'P' ] ];
			$struct_tag
				= $this->mParser->getTag(
					$this->mParser->getNode(
						$this->mParser->getWorksheets()[ $current_info[ 'W' ] ][ 'node' ] )
							->offsetGet( kTAG_TAG ) );
			$is_list = ( $struct_tag->offsetExists( kTAG_DATA_KIND )
					  && in_array( kTYPE_LIST, $struct_tag->offsetGet( kTAG_DATA_KIND ) ) );
			
			//
			// Select worksheet records.
			//
			$criteria = array( $current_info[ 'F' ] => $theParentIndex );
			$records
				= $this->mCollections[ $this->getCollectionName( $current_info[ 'W' ] ) ]
					->matchAll( array( $current_info[ 'F' ] => $theParentIndex,
									   '_valid' => TRUE ),
								kQUERY_ARRAY );
			
			//
			// Load worksheet records.
			//
			if( $records->count() )
			{
				//
				// Iterate records.
				//
				foreach( $records as $record )
				{
					//
					// Set container reference.
					//
					if( $is_list )
					{
						$container[] = Array();
						$reference = & $container[ count( $container ) - 1 ];
					}
					else
						$reference = & $container;
					
					//
					// Load properties.
					//
					$tmp = Array();
					if( ! $this->setObjectProperties(
						$reference, $current_info[ 'W' ], $record ) )
						return FALSE;												//  ==>
					
					//
					// Handle related worksheets.
					//
					if( array_key_exists( 'C', $current ) )
					{
						//
						// Traverse worksheet structure.
						//
						if( ! $this->setWorksheetProperties(
								$reference,
								$current[ 'C' ],
								$record[ $current_info[ 'K' ] ] ) )
							return FALSE;											//  ==>
			
					} // Has related worksheets.
		
				} // Iterating records.
	
				//
				// Update object.
				//
				$theObject[ $struct_tag->offsetGet( kTAG_ID_HASH ) ] = $container;
			
			} // Has records.
		
		} // Iterating worksheets.
		
		return TRUE;																// ==>

	} // setWorksheetProperties.
	
	
	/*===================================================================================
	 *	setObjectProperties																*
	 *==================================================================================*/

	/**
	 * Set object properties
	 *
	 * This method will load the provided properties in the provided container.
	 *
	 * @param mixed				   &$theObject			Object or array.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param array					$theRecord			Properties.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	protected function setObjectProperties( &$theObject, $theWorksheet, $theRecord )
	{
		//
		// Iterate properties.
		//
		foreach( $theRecord as $key => $value )
		{
			//
			// Skip private properties.
			//
			if( substr( $key, 0, 1 ) != '_' )
			{
				//
				// Handle tag value.
				//
				$field = $this->mParser->getFields()[ $theWorksheet ][ $key ];
				$node = $this->mParser->getNode( $field[ 'node' ] );
				if( $node->offsetExists( kTAG_TAG ) )
					$this->setObjectProperty( $theObject, $node, $key, $value );
			
			} // Not a private property.
		
		} // Iterating properties.
		
		return TRUE;																// ==>

	} // setObjectProperties.

	 
	/*===================================================================================
	 *	setObjectProperty																*
	 *==================================================================================*/

	/**
	 * Set object property from worksheets
	 *
	 * This method will set the provided property in the provided object.
	 *
	 * @param mixed				   &$theObject			Receiving object or array.
	 * @param Node					$theNode			Node identifier.
	 * @param string				$theKey				Property key.
	 * @param string				$theValue			Property value.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> set, <tt>FALSE</tt> skipped.
	 */
	protected function setObjectProperty( &$theObject, $theNode, $theKey, $theValue )
	{
		//
		// Get property tag.
		//
		$tag_object = $this->mParser->getTag( $theNode->offsetGet( kTAG_TAG ) );
		
		//
		// Set structured property.
		//
		if( $theNode->offsetExists( kTAG_TAGS ) )
		{
			//
			// Build structured offset.
			//
			$offset = Array();
			foreach( $theNode->offsetGet( kTAG_TAGS ) as $tag )
				$offset[]
					= $this->mParser->getTag( $tag )
						->offsetGet( kTAG_ID_HASH );
			$offset[] = $tag_object->offsetGet( kTAG_ID_HASH );
			$offset = implode( '.', $offset );
		
		} // Structured offset.
		
		//
		// Set scalar offset.
		//
		else
			$offset = $tag_object->offsetGet( kTAG_ID_HASH );
		
		//
		// Set property.
		//
		$theObject[ $offset ] = $theValue;
		
		//
		// Apply transformations.
		//
		$transformations = GetExternalTransformations( $theNode );
		foreach( $transformations as $transformation )
		{
			//
			// Get transformation arguments.
			//
			$val = $theValue;
			$collection = $prefix = $suffix = NULL;
			$tag = $this->mParser->getTag( $transformation[ kTAG_TAG ] );
			$offset = $tag->offsetGet( kTAG_ID_HASH );
			if( array_key_exists( kTAG_CONN_COLL, $transformation ) )
				$collection = $transformation[ kTAG_CONN_COLL ];
			if( array_key_exists( kTAG_PREFIX, $transformation ) )
				$prefix = $transformation[ kTAG_PREFIX ];
			if( array_key_exists( kTAG_SUFFIX, $transformation ) )
				$suffix = $transformation[ kTAG_SUFFIX ];
			
			//
			// Handle reference.
			//
			if( $collection !== NULL )
			{
				//
				// Check combinations.
				//
				$collection = $this->wrapper()->resolveCollection( $collection );
				$combinations = CheckStringCombinations( $val, $prefix, $suffix );
				foreach( $combinations as $combination )
				{
					//
					// Match.
					//
					if( $collection->matchOne( array( kTAG_NID => $combination ),
											   kQUERY_COUNT ) )
					{
						$theObject[ $offset ] = $combination;
						break;												// =>
					}
				
				} // Testing combinations.
			
			} // Located reference.
			
			//
			// Handle value.
			//
			else
			{
				//
				// Transform value.
				//
				if( count( $prefix ) + count( $suffix ) )
				{
					//
					// Transform value.
					//
					if( $prefix )
						$val = $prefix[ 0 ].$val;
					if( $suffix )
						$val = $val.$suffix[ 0 ];
			
				} // Has transformations.
				
				//
				// Set property.
				//
				$theObject[ $offset ] = $val;
			
			} // Not a reference.
			
		} // Iterating transformations.

	} // setObjectProperty.

	

/*=======================================================================================
 *																						*
 *								PUBLIC DEBUG UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	test																			*
	 *==================================================================================*/

	/**
	 * Test validations
	 *
	 * This method will test validations.
	 *
	 * @access public
	 */
	public function test()
	{
		//
		// Create transaction.
		//
		$this->transaction(
			$this->session()
				->newTransaction(
					kTYPE_TRANS_TMPL_WORKSHEET ) );
		
		//
		// Create tag.
		//
		$tag = new Tag( $this->wrapper() );
		$tag[ "_id" ] = ":test";
		$tag[ kTAG_DATA_TYPE ] = kTYPE_STRING;
		
		//
		// Create node.
		//
		$node = new Node( $this->wrapper() );
		$node[ "_id" ] = 75000;
		$node[ kTAG_TAG ] = ":test";
		$node[ kTAG_ID_SYMBOL ] = 'SYMBOL';
		$node[ kTAG_TOKEN ] = ',';
		
		//
		// Create field data.
		//
		$fields
			= array(
				'SYMBOL' => array(
					'column_name' => 'A',
					'column_number' => 1 ) );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => 22 );

		//
		// Test validateString.
		//
		echo( '<b>validateString()</b><br />' );
		var_dump( $record );
		$ok = $this->validateString(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'uno , due' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		//
		// Test validateString.
		//
		var_dump( $record );
		$ok = $this->validateString(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'uno , due' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		//
		// Test validateString.
		//
		var_dump( $record );
		$ok = $this->validateString(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => '12' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_INT;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateInteger.
		//
		echo( '<b>validateInteger()</b><br />' );
		var_dump( $record );
		$ok = $this->validateInteger(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => '12 , 34' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		//
		// Test validateInteger.
		//
		var_dump( $record );
		$ok = $this->validateInteger(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => '12' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_FLOAT;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateFloat.
		//
		echo( '<b>validateFloat()</b><br />' );
		var_dump( $record );
		$ok = $this->validateFloat(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => '12 , 34' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		//
		// Test validateFloat.
		//
		var_dump( $record );
		$ok = $this->validateFloat(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => 'y' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_BOOLEAN;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateBoolean.
		//
		echo( '<b>validateBoolean()</b><br />' );
		var_dump( $record );
		$ok = $this->validateBoolean(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'Y, YES, TRUE, 1, n, no, false, 0' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		//
		// Test validateBoolean.
		//
		var_dump( $record );
		$ok = $this->validateBoolean(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => '1 ,2 ,3' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_ARRAY;
		$tag[ kTAG_DATA_KIND ] = NULL;
		$node[ kTAG_TOKEN ] = ',';

		//
		// Test validateArray.
		//
		echo( '<b>validateArray()</b><br />' );
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		var_dump( $record );
		$ok = $this->validateArray(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'uno ; due' );
		$node[ kTAG_TOKEN ] = ';,';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateBoolean.
		//
		var_dump( $record );
		$ok = $this->validateArray(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'uno,1;due , 2' );
		$node[ kTAG_TOKEN ] = ';,';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateArray.
		//
		var_dump( $record );
		$ok = $this->validateArray(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'uno,1;due , 2 : one, 1; two, 2' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ':;,';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		echo( "<em>List</em><br />" );
		//
		// Test validateArray.
		//
		var_dump( $record );
		$ok = $this->validateArray(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'uno,1;due , 2 ' );
		$tag[ kTAG_DATA_KIND ] = NULL;
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateArray.
		//
		var_dump( $record );
		$ok = $this->validateArray(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano;français;belge' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_LANGUAGE_STRING;
		$tag[ kTAG_DATA_KIND ] = NULL;
		$node[ kTAG_TOKEN ] = ';';

		//
		// Test validateLanguageString.
		//
		echo( '<b>validateLanguageString()</b><br />' );
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		var_dump( $record );
		$ok = $this->validateLanguageString(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano;français;belge' );
		$node[ kTAG_TOKEN ] = ';@';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateLanguageString.
		//
		var_dump( $record );
		$ok = $this->validateLanguageString(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano : en@ inglese ; it @ italian;français;belge' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ':;@';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateLanguageString.
		//
		var_dump( $record );
		$ok = $this->validateLanguageString(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano : en@ inglese ; fr @ cianfrese' );
		$tag[ kTAG_DATA_KIND ] = NULL;
		$node[ kTAG_TOKEN ] = ':;@';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateLanguageString.
		//
		var_dump( $record );
		$ok = $this->validateLanguageString(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano;français;belge' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_LANGUAGE_STRINGS;
		$tag[ kTAG_DATA_KIND ] = NULL;
		$node[ kTAG_TOKEN ] = ';';

		//
		// Test validateLanguageStrings.
		//
		echo( '<b>validateLanguageStrings()</b><br />' );
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		var_dump( $record );
		$ok = $this->validateLanguageStrings(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano;français;belge' );
		$node[ kTAG_TOKEN ] = ';@';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateLanguageStrings.
		//
		var_dump( $record );
		$ok = $this->validateLanguageStrings(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english , inglese;it@italiano;français;belge,begïe' );
		$node[ kTAG_TOKEN ] = ';@,';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateLanguageStrings.
		//
		var_dump( $record );
		$ok = $this->validateLanguageStrings(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english , inglese;it@italiano;français;belge,begïe : uno;en@pippo;it@pippa,peppe' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ':;@,';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateLanguageStrings.
		//
		var_dump( $record );
		$ok = $this->validateLanguageStrings(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english , inglese;it@italiano;français;belge,begïe : uno;en@pippo;it@pippa,peppe' );
		$tag[ kTAG_DATA_KIND ] = NULL;
		$node[ kTAG_TOKEN ] = ':;@,';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateLanguageStrings.
		//
		var_dump( $record );
		$ok = $this->validateLanguageStrings(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano;français;belge' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_TYPED_LIST;
		$tag[ kTAG_DATA_KIND ] = NULL;
		$node[ kTAG_TOKEN ] = ';';

		//
		// Test validateTypedList.
		//
		echo( '<b>validateTypedList()</b><br />' );
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		var_dump( $record );
		$ok = $this->validateTypedList(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano;français;belge' );
		$node[ kTAG_TOKEN ] = ';@';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateTypedList.
		//
		var_dump( $record );
		$ok = $this->validateTypedList(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano : en@ inglese ; it @ italian;français;belge' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ':;@';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateTypedList.
		//
		var_dump( $record );
		$ok = $this->validateTypedList(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'en@english;it@italiano : en@ inglese ; fr @ cianfrese' );
		$tag[ kTAG_DATA_KIND ] = NULL;
		$node[ kTAG_TOKEN ] = ':;@';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateTypedList.
		//
		var_dump( $record );
		$ok = $this->validateTypedList(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => 'Point=7.456,46.302' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_SHAPE;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateShape.
		//
		echo( '<b>validateShape()</b><br />' );
		var_dump( $record );
		$ok = $this->validateShape(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'MultiPoint=7.456 , 46.302 ; 102.12 , 37.22' );
		//
		// Test validateShape.
		//
		var_dump( $record );
		$ok = $this->validateShape(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'LineString=7.456 , 46.302 ; 102.12 , 37.22' );
		//
		// Test validateShape.
		//
		var_dump( $record );
		$ok = $this->validateShape(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'Polygon=12.8199,42.8422;12.8207,42.8158;12.8699,42.8166;12.8678,42.8398:12.8344,42.8347;12.8348,42.8225;12.857,42.8223;12.8566,42.8332' );
		//
		// Test validateShape.
		//
		var_dump( $record );
		$ok = $this->validateShape(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'Point=7.456,46.302 @LineString=7.456 , 46.302 ; 102.12 , 37.22 @ MultiPoint=7.456 , 46.302 ; 102.12 , 37.22@Polygon=12.8199,42.8422;12.8207,42.8158;12.8699,42.8166;12.8678,42.8398;12.8199,42.8422:12.8344,42.8347;12.8348,42.8225;12.857,42.8223;12.8566,42.8332;12.8344,42.8347' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = '@';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateShape.
		//
		var_dump( $record );
		$ok = $this->validateShape(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => 'http://www.apple.com' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_URL;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateLink.
		//
		echo( '<b>validateLink()</b><br />' );
		var_dump( $record );
		$ok = $this->validateLink(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'http://www.apple.com ; http://google.com' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ';';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateLink.
		//
		var_dump( $record );
		$ok = $this->validateLink(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => '20010101' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_DATE;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateDate.
		//
		echo( '<b>validateDate()</b><br />' );
		var_dump( $record );
		$ok = $this->validateDate(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => '20010101;2002-02-02 ; 2002/02;2005;01/01/1987;12-1987' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ';';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateDate.
		//
		var_dump( $record );
		$ok = $this->validateDate(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => 'iso:3166:1:alpha-3:ITA' );
		$node[ kTAG_TRANSFORM ] = NULL;
		$tag[ kTAG_DATA_TYPE ] = kTYPE_ENUM;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateEnum.
		//
		echo( '<b>validateEnum()</b><br />' );
		var_dump( $record );
		$ok = $this->validateEnum(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'ITA;YUG;IT-RM' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array(
					 'iso:3166:1:alpha-3:', 'iso:3166:3:alpha-3:', 'iso:3166:2:' ) ) );
		$node[ kTAG_TOKEN ] = ';';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateEnum.
		//
		var_dump( $record );
		$ok = $this->validateEnum(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => 'ITA,YUG,IT-RM' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array(
					 'iso:3166:1:alpha-3:', 'iso:3166:3:alpha-3:', 'iso:3166:2:' ) ) );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_SET;
		$tag[ kTAG_DATA_KIND ] = NULL;
		$node[ kTAG_TOKEN ] = ',';

		//
		// Test validateEnumSet.
		//
		echo( '<b>validateEnumSet()</b><br />' );
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		var_dump( $record );
		$ok = $this->validateEnumSet(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'ITA,YUG,IT-RM ; USA , GB-SCT' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ';,';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateEnumSet.
		//
		var_dump( $record );
		$ok = $this->validateEnumSet(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'ITA,YUG,IT-RM' );
		$tag[ kTAG_DATA_KIND ] = NULL;
		$node[ kTAG_TOKEN ] = ';,';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateEnumSet.
		//
		var_dump( $record );
		$ok = $this->validateEnumSet(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => ':taxon:crop:category' );
		$node[ kTAG_TRANSFORM ] = NULL;
		$tag[ kTAG_DATA_TYPE ] = kTYPE_REF_TAG;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateReference.
		//
		echo( '<b>validateReference() [tag]</b><br />' );
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Tag::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => ':crop' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array( ':taxon' ),
					   kTAG_SUFFIX => array( ':category' ) ) );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Tag::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => ':category,:group' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array( ':taxon:crop' ) ) );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ',';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Tag::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => ':taxon:crop:category' );
		$node[ kTAG_TRANSFORM ] = NULL;
		$tag[ kTAG_DATA_TYPE ] = kTYPE_REF_TERM;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateReference.
		//
		echo( '<b>validateReference() [term]</b><br />' );
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Term::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => ':crop' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array( ':taxon' ),
					   kTAG_SUFFIX => array( ':category' ) ) );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Term::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => ':category,:group' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array( ':taxon:crop' ) ) );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ',';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Term::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => '152' );
		$node[ kTAG_TRANSFORM ] = NULL;
		$tag[ kTAG_DATA_TYPE ] = kTYPE_REF_NODE;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateReference.
		//
		echo( '<b>validateReference() [node]</b><br />' );
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Node::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'form::domain:accession' );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Node::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => '152, form::domain:accession' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ',';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Node::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => '1/:predicate:PROPERTY-OF/365' );
		$node[ kTAG_TRANSFORM ] = NULL;
		$tag[ kTAG_DATA_TYPE ] = kTYPE_REF_EDGE;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateReference.
		//
		echo( '<b>validateReference() [edge]</b><br />' );
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Edge::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => ':predicate:PROPERTY-OF' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array( '1/' ),
					   kTAG_SUFFIX => array( '/365' ) ) );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Edge::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => ':predicate:ENUM-OF/381' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array( '1007/' ) ) );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ',';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Edge::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => ':domain:inventory://CYP/Aegilops triuncialis:CYP;' );
		$node[ kTAG_TRANSFORM ] = NULL;
		$tag[ kTAG_DATA_TYPE ] = kTYPE_REF_UNIT;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateReference.
		//
		echo( '<b>validateReference() [unit]</b><br />' );
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				UnitObject::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => '://CYP/' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array( ':domain:inventory' ),
					   kTAG_SUFFIX => array( 'Aegilops triuncialis:CYP;' ) ) );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				UnitObject::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'Aegilops triuncialis, Allium amethystinum, Elymus elongatus haifensis' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array( ':domain:inventory://CYP/' ),
					   kTAG_SUFFIX => array( ':CYP;' ) ) );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ',';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				UnitObject::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => ':domain:individual://ITA406/pgrdiversity.bioversityinternational.org:7C4D3533C21C608B39E8EAB256B4AFB771FA534A;' );
		$node[ kTAG_TRANSFORM ] = NULL;
		$tag[ kTAG_DATA_TYPE ] = kTYPE_REF_USER;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateReference.
		//
		echo( '<b>validateReference() [user]</b><br />' );
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				User::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A' );
		$node[ kTAG_TRANSFORM ]
			= array(
				array( kTAG_PREFIX => array( ':domain:individual://ITA406/pgrdiversity.bioversityinternational.org:' ),
					   kTAG_SUFFIX => array( ';' ) ) );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				User::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => '7C4D3533C21C608B39E8EAB256B4AFB771FA534A, E3EC37CC5D36ED5AABAC7BB46CB0CC8794693FC2' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ',';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateReference.
		//
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				User::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => (string) $this->session() );
		$node[ kTAG_TRANSFORM ] = NULL;
		$tag[ kTAG_DATA_TYPE ] = kTYPE_REF_SESSION;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateReference.
		//
		echo( '<b>validateReference() [session]</b><br />' );
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				Session::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );
	
	/*
		//
		// Create record.
		//
		$class = $this->mParser->getRoot()->offsetGet( kTAG_CLASS_NAME );
		$record = array( 'SYMBOL' => ':domain:inventory://CYP/Aegilops triuncialis:CYP;' );
		$node[ kTAG_TRANSFORM ] = NULL;
		$tag[ kTAG_DATA_TYPE ] = kTYPE_REF_SELF;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateReference.
		//
		echo( '<b>validateReference() [self]</b><br />' );
		var_dump( $record );
		$ok = $this->validateReference(
				$transaction,
				$record,
				$class::kSEQ_NAME,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );
	*/

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => '55032ad9b0a1db8b110041c7' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_REF_USER;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateObjectId.
		//
		echo( '<b>validateObjectId()</b><br />' );
		var_dump( $record );
		$ok = $this->validateObjectId(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => '55032ad9b0a1db8b110041c7, 55032ad2b0a1dbfb110041c7' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ',';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateObjectId.
		//
		var_dump( $record );
		$ok = $this->validateObjectId(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

		//
		// Create record.
		//
		$record = array( 'SYMBOL' => '2013-09-22T10:41:44.451999' );
		$tag[ kTAG_DATA_TYPE ] = kTYPE_TIME_STAMP;
		$tag[ kTAG_DATA_KIND ] = NULL;

		//
		// Test validateTimeStamp.
		//
		echo( '<b>validateTimeStamp()</b><br />' );
		var_dump( $record );
		$ok = $this->validateTimeStamp(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		//
		// Update data.
		//
		$record = array( 'SYMBOL' => 'now, 1426288982' );
		$tag[ kTAG_DATA_KIND ] = array( kTYPE_LIST );
		$node[ kTAG_TOKEN ] = ',';
		echo( "<em>Token</em>: ".$node[ kTAG_TOKEN ].'<br />' );
		//
		// Test validateTimeStamp.
		//
		var_dump( $record );
		$ok = $this->validateTimeStamp(
				$transaction,
				$record,
				'WORKSHEET',
				12,
				$fields[ 'SYMBOL' ],
				$node,
				$tag );
		var_dump( $ok );
		var_dump( $record );
		echo( '<hr />' );
		echo( '<hr />' );

	} // test.

	 

} // class SessionUpload.


?>
