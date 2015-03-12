<?php

/**
 * SessionUpload.php
 *
 * This file contains the definition of the {@link SessionUpload} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Session;

/*=======================================================================================
 *																						*
 *									SessionUpload.php									*
 *																						*
 *======================================================================================*/

/**
 * Domains.
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
class SessionUpload
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
		// Delete template file.
		//
		$file = $this->file();
		if( $file instanceof \SplFileInfo )
		{
			if( $file->isWritable() )
				unlink( $file->getRealPath() );
		}

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
		// TRY BLOCK.
		//
		try
		{
			//
			// Initialise workflow.
			//
			$this->session()->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
	
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
			// Transaction load.
			//
			if( ! $this->sessionLoad() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 10 );
	
			//
			// Transaction store.
			//
			if( ! $this->sessionStore() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 10 );
	
			//
			// Transaction structure.
			//
			if( ! $this->sessionStructure() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 10 );
	
			//
			// Transaction setup.
			//
			if( ! $this->sessionSetup() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 10 );
	
			//
			// Transaction validation.
			//
			if( ! $this->sessionValidation() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 10 );
			
			return $this->succeedSession();											// ==>
		}
		
		//
		// CATCH BLOCK.
		//
		catch( Exception $error )
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
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Delete pending sessions.
		//
		if( ! $this->deletePendingSessions() )
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
		// Create transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_LOAD ) );
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Check file.
		//
		if( ! $this->checkFile() )
			return FALSE;															// ==>
	
		//
		// Check file type.
		//
		if( ! $this->checkFileType() )
			return FALSE;															// ==>
	
		//
		// Load template.
		//
		if( ! $this->loadTemplate() )
			return FALSE;															// ==>
		
		//
		// Load template structure.
		//
		if( ! $this->loadTemplateStructure() )
			return FALSE;															// ==>
	
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
	 * @uses saveTemplateFile()
	 */
	protected function sessionStore()
	{
		//
		// Instantiate transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_STORE ) );
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Save file.
		//
		if( ! $this->saveTemplateFile() )
			return FALSE;															// ==>
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
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
		// Create transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_STRUCT ) );
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Check required worksheets.
		//
		if( ! $this->checkRequiredWorksheets() )
			return FALSE;															// ==>
		
		//
		// Check required worksheet fields.
		//
		if( ! $this->checkRequiredFields() )
			return FALSE;															// ==>
	
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
	 * @uses saveTemplateFile()
	 */
	protected function sessionSetup()
	{
		//
		// Instantiate transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_SETUP ) );
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Create collections.
		//
		if( ! $this->createWorkingCollections() )
			return FALSE;															// ==>
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionSetup.

	 
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
	 * @uses saveTemplateFile()
	 */
	protected function sessionValidation()
	{
		//
		// Init local storage.
		//
		$worksheets = $this->mParser->getWorksheets();
		
		//
		// Set session records.
		//
		$records = 0;
		foreach( $worksheets as $worksheet )
			$records += ($worksheet[ 'last_row' ] - $worksheet[ 'data_row' ] + 1);
		$this->session()->offsetSet( kTAG_COUNTER_RECORDS, $records );
		
		//
		// Iterate worksheets.
		//
		foreach( $worksheets as $wname => $worksheet )
		{
			//
			// Instantiate transaction.
			//
			$transaction
				= $this->transaction(
					$this->session()
						->newTransaction( kTYPE_TRANS_TMPL_WORKSHEET, $wname ) );
			
			//
			// Init progress.
			//
			$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
			
			//
			// Set records count.
			//
			$transaction->offsetSet(
				kTAG_COUNTER_RECORDS,
				$worksheet[ 'last_row' ] - $worksheet[ 'data_row' ] + 1 );
			
			//
			// Load worksheet data.
			//
			if( ! $this->loadWorksheetData( $wname, $records ) )
				return FALSE;														// ==>
	
			//
			// Close transaction.
			//
			$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
			$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		} // Iterating worksheets.
		
		return TRUE;																// ==>

	} // sessionValidation.

	

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
	 * This method will delete all pending sessions, that is, all user sessions of type
	 * upload that do not have a referencing session and that do not correspond to the
	 * current session.
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
		// Init local storage.
		//
		$transaction = $this->transaction();
		
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
							   kTAG_SESSION_TYPE => kTYPE_SESSION_UPLOAD,
							   kTAG_SESSION => array( '$exists' => FALSE ) ),
						kQUERY_NID );
		
		//
		// Handle sessions list.
		//
		if( $count = $sessions->count() )
		{
			//
			// Set count.
			//
			$transaction->offsetSet( kTAG_COUNTER_COLLECTIONS, $count );
			
			//
			// Save increment.
			//
			$increment = 100 / $count;
			
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
				$transaction->processed( 1 );
				$transaction->progress( $increment );
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
		
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction()
				->newTransaction( kTYPE_TRANS_TMPL_LOAD_FILE );
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Check file.
		//
		if( $file->getType() != 'file' )
		{
			$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
			$transaction->offsetSet( kTAG_ERROR_TYPE, kTYPE_ERROR_BAD_TMPL_FILE );
			$transaction->offsetSet( kTAG_ERROR_CODE, kTYPE_ERROR_CODE_FILE_BAD );
			$transaction->offsetSet( kTAG_TRANSACTION_MESSAGE,
									 'The file is either a directory or is invalid ['
									.$file->getRealPath()
									.'].' );
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			
			//
			// Remove reference to prevent deleting.
			//
			$this->mFile = NULL;
			
			return $this->failTransaction( kTYPE_STATUS_FATAL );					// ==>
		
		} // Bad file.
		
		//
		// Check if readable.
		//
		if( ! $file->isReadable() )
		{
			$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
			$transaction->offsetSet( kTAG_ERROR_TYPE, kTYPE_ERROR_BAD_TMPL_FILE );
			$transaction->offsetSet( kTAG_ERROR_CODE, kTYPE_ERROR_CODE_FILE_UNRWAD );
			$transaction->offsetSet( kTAG_TRANSACTION_MESSAGE,
									 'The file cannot be read ['
									.$file->getRealPath()
									.'].' );
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			
			//
			// Remove reference to prevent deleting.
			//
			$this->mFile = NULL;
			
			return $this->failTransaction( kTYPE_STATUS_FATAL );					// ==>
		
		} // Unreadable.
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
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
		
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction()
				->newTransaction( kTYPE_TRANS_TMPL_LOAD_TYPE );
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Check file extension.
		//
		switch( $tmp = $file->getExtension() )
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
				$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
				$transaction->offsetSet( kTAG_ERROR_TYPE, kTYPE_ERROR_BAD_TMPL_FILE );
				$transaction->offsetSet( kTAG_ERROR_CODE, kTYPE_ERROR_CODE_FILE_UNSUP );
				$transaction->offsetSet( kTAG_ERROR_RESOURCE,
										 "http://filext.com/file-extension/$tmp" );
				$transaction->offsetSet( kTAG_TRANSACTION_MESSAGE,
										 'The file type is not supported, please submit '
										."an Excel file." );
				$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
				
				return $this->failTransaction( kTYPE_STATUS_FATAL );				// ==>
		}
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
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
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Instantiate parser.
		//
		$this->mParser = new ExcelTemplateParser( $this->wrapper(), $this->file() );
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
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
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Load structure.
		//
		if( ! $this->mParser->loadStructure( $transaction ) )
			return $this->failTransaction();										// ==>
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // loadTemplateStructure.

	 
	/*===================================================================================
	 *	saveTemplateFile																*
	 *==================================================================================*/

	/**
	 * Check file reference
	 *
	 * This method will check whether the file reference points to a valid file and if the
	 * file is readable; it will also check if the file type is compatible with Excel
	 * files.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses session()
	 */
	protected function saveTemplateFile()
	{
		//
		// Init local storage.
		//
		$file = $this->file();
		$session = $this->session();
		
		//
		// Get file path.
		//
		$path = $file->getRealPath();
		
		//
		// Set metadata.
		//
		$metadata
			= array( kTAG_SESSION_TYPE
				  => $session->offsetGet( kTAG_SESSION_TYPE ) );
		
		//
		// Save file.
		//
		$session->saveFile( $path, $metadata );
		
		return TRUE;																// ==>

	} // saveTemplateFile.

	 
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
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Load structure.
		//
		if( ! $this->mParser->checkRequiredWorksheets( $transaction ) )
			return $this->failTransaction();										// ==>
	
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
		// Load structure.
		//
		if( ! $this->mParser->checkRequiredColumns( $this->transaction() ) )
			return $this->failTransaction();										// ==>
		
		return TRUE;																// ==>

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
		
		//
		// Iterate worksheets.
		//
		foreach( array_keys( $this->mParser->getWorksheets() ) as $worksheet )
		{
			//
			// Create collection.
			//
			$name = $this->getCollectionName( $worksheet );
			$this->mCollections[ $name ]
				= Session::ResolveDatabase( $this->wrapper(), TRUE )
					->collection( $name, TRUE );
			
			//
			// Add indexes.
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
		}
		
		//
		// Add unit collection.
		//
		$name = $this->getCollectionName( UnitObject::kSEQ_NAME );
		$this->mCollections[ $name ]
			= Session::ResolveDatabase( $this->wrapper(), TRUE )
				->collection( $name, TRUE );
		
		//
		// Add to session.
		//
		$this->session()->offsetSet( kTAG_CONN_COLLS, array_keys( $this->mCollections ) );
		
		return TRUE;																// ==>

	} // createWorkingCollections.

	 
	/*===================================================================================
	 *	loadWorksheetData																*
	 *==================================================================================*/

	/**
	 * Load worksheet data
	 *
	 * This method will validate and load worksheet data.
	 *
	 * @param string				$theWorksheet		Worksheet name.
	 * @param float					$theRecords			Session records total.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses session()
	 */
	protected function loadWorksheetData( $theWorksheet, $theRecords )
	{
		//
		// Init local storage.
		//
		$collection = $this->mCollections[ $this->getCollectionName( $theWorksheet ) ];
		$worksheet_data = $this->mParser->getWorksheets()[ $theWorksheet ];
		$fields_data = $this->mParser->getFields()[ $theWorksheet ];
		$records = $worksheet_data[ 'last_row' ] - $worksheet_data[ 'data_row' ] + 1;
		
		//
		// Iterate rows.
		//
		for( $row = $worksheet_data[ 'data_row' ];
				$row <= $worksheet_data[ 'last_row' ];
					$row++ )
		{
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
				$errors = 0;
				$transaction = NULL;
				
				//
				// Check required fields.
				//
				$errors
					+= $this->checkRowRequiredFields(
							$transaction,						// Row transaction.
							$record,							// Row record.
							$theWorksheet,						// Worksheet name.
							$row );								// Row number.
			
				//
				// Validate row.
				//
				foreach( array_keys( $record ) as $symbol )
					$errors
						+= $this->validateProperty(
								$transaction,					// Row transaction.
								$record,						// Row record.
								$theWorksheet,					// Worksheet name.
								$row,							// Row number.
								$symbol );						// Field symbol.
				
				//
				// Handle errors.
				//
				if( $errors )
				{
					//
					// Handle rejected.
					//
					$this->session()->rejected( 1 );
					$this->transaction()->rejected( 1 );
				
				} // Has errors.
				
				//
				// Handle valid row.
				//
				else
				{
					//
					// Set row number.
					//
					$record[ kTAG_NID ] = (int) $row;
					
					//
					// Write record.
					//
					$collection->commit( $record );
					
					//
					// Handle validated.
					//
					$this->session()->validated( 1 );
					$this->transaction()->validated( 1 );
				
				} // Valid row.
								
				//
				// Close transaction.
				//
				if( $transaction !== NULL )
				{
					$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
					$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
					$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
				}
				
			} // Record has data.
			
			//
			// Handle skipped.
			//
			else
			{
				$this->session()->skipped( 1 );
				$this->transaction()->skipped( 1 );
			
			} // Empty record.
			
			//
			// Update progress.
			//
			$this->session()->processed( 1, $theRecords );
			$this->transaction()->processed( 1, $records );
		
		} // Iterating worksheet row.
		
		return TRUE;																// ==>

	} // loadWorksheetData.

	

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
	 * @param int					$theRow				Row number.
	 *
	 * @access protected
	 * @return int					Number of missing fields.
	 *
	 * @uses file()
	 * @uses session()
	 */
	protected function checkRowRequiredFields( &$theTransaction,
												$theRecord,
												$theWorksheet,
												$theRow )
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
					$theRow,								// Row.
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
	 *	validateProperty																*
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
	 * @param int					$theRow				Row number.
	 * @param string				$theSymbol			Field symbol.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses validateString()
	 * @uses validateInteger()
	 * @uses validateFloat()
	 * @uses validateBoolean()
	 * @uses validateStruct()
	 * @uses validateArray()
	 * @uses validateArray()
	 */
	protected function validateProperty( &$theTransaction,
										 &$theRecord,
										  $theWorksheet,
										  $theRow,
										  $theSymbol )
	{
		//
		// Init local storage.
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
					$this->validateString(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_INT:
				case kTYPE_YEAR:
					$this->validateInteger(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_FLOAT:
					$this->validateFloat(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_BOOLEAN:
					$this->validateBoolean(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_STRUCT:
					$this->validateStruct(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_ARRAY:
					$this->validateArray(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_LANGUAGE_STRING:
					$this->validateLanguageString(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_LANGUAGE_STRINGS:
					$this->validateLanguageStrings(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_TYPED_LIST:
					$this->validateTypedList(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_SHAPE:
					$this->validateShape(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_URL:
					$this->validateLink(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_DATE:
					$this->validateDate(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_ENUM:
					$this->validateEnum(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_SET:
					$this->validateEnumSet(
						$theTransaction, $theRecord[ $theSymbol ],
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
			} // Parsing by type.
		
		} // Tag field.

	} // validateProperty.

	

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
		$session->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$session->offsetSet( kTAG_SESSION_END, TRUE );
		$session->offsetSet( kTAG_SESSION_STATUS, kTYPE_STATUS_OK );
		
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
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			$transaction->offsetSet( kTAG_TRANSACTION_STATUS, $theStatus );
			
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
		// Init local storage.
		//
		$status = array( kTYPE_STATUS_EXECUTING => 0,
						 kTYPE_STATUS_OK => 1, kTYPE_STATUS_MESSAGE => 2,
						 kTYPE_STATUS_WARNING => 3, kTYPE_STATUS_ERROR => 4,
						 kTYPE_STATUS_FATAL => 5, kTYPE_STATUS_EXCEPTION => 6 );
		
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
					->newTransaction( $theType, $theFieldWorksheet, $theFieldRow );
	
			//
			// Set transaction status.
			//
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, $theStatus );
	
		} // New transaction.
	
		//
		// Update transaction status.
		//
		elseif( $status[ $theStatus ]
				> $status[ $theTransaction->offsetGet( kTAG_TRANSACTION_STATUS ) ] )
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, $theStatus );
	
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
		// Init local storage.
		//
		$session = $this->session();
		
		//
		// Set error info.
		//
		$session->offsetSet( kTAG_ERROR_TYPE, 'Exception' );
		if( $theError->getCode() )
			$session->offsetSet( kTAG_ERROR_CODE, $theError->getCode() );
		$session->offsetSet( kTAG_TRANSACTION_MESSAGE, $theError->getMessage() );
		
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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		
		//
		// Cast value.
		//
		$theRecord[ $symbol ] = (string) $theRecord[ $symbol ];
		
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
				unset( $theRecord[ $symbol ] );
		
		} // List.
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$error = FALSE;
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
				$ok = CheckIntegerValue( $element );
				
				//
				// Correct value.
				//
				if( $ok )
					$result[] = $element;
				
				//
				// Invalid value.
				//
				elseif( $ok === FALSE )
				{
					$error = TRUE;
					break;													// =>
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
				
				return TRUE;														// ==>
			
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
			$ok = CheckIntegerValue( $theRecord[ $symbol ] );
			
			//
			// Remove if empty.
			//
			if( $ok === NULL )
				unset( $theRecord[ $symbol ] );
			
			//
			// Invalid value.
			//
			elseif( $ok === FALSE )
				$error = TRUE;
		
		} // Scalar value.
		
		//
		// Handle errors.
		//
		if( $error )
			return
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					'Invalid integer number.',					// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					kTYPE_ERROR_INVALID_VALUE,					// Error type.
					kTYPE_ERROR_CODE_BAD_NUMBER,				// Error code.
					NULL										// Error resource.
				);																	// ==>
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$error = FALSE;
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
				$ok = CheckFloatValue( $element );
				
				//
				// Correct value.
				//
				if( $ok )
					$result[] = $element;
				
				//
				// Invalid value.
				//
				elseif( $ok === FALSE )
				{
					$error = TRUE;
					break;													// =>
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
				
				return TRUE;														// ==>
			
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
			$ok = CheckFloatValue( $theRecord[ $symbol ] );
			
			//
			// Remove if empty.
			//
			if( $ok === NULL )
				unset( $theRecord[ $symbol ] );
			
			//
			// Invalid value.
			//
			elseif( $ok === FALSE )
				$error = TRUE;
		
		} // Scalar value.
		
		//
		// Handle errors.
		//
		if( $error )
			return
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					'Invalid floating point number.',			// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					kTYPE_ERROR_INVALID_VALUE,					// Error type.
					kTYPE_ERROR_CODE_BAD_NUMBER,				// Error code.
					NULL										// Error resource.
				);																	// ==>
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$error = FALSE;
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
				$ok = CheckBooleanValue( $element );
				
				//
				// Correct value.
				//
				if( $ok )
					$result[] = $element;
				
				//
				// Invalid value.
				//
				elseif( $ok === FALSE )
				{
					$error = TRUE;
					break;													// =>
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
				
				return TRUE;														// ==>
			
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
			$ok = CheckBooleanValue( $theRecord[ $symbol ] );
			
			//
			// Remove if empty.
			//
			if( $ok === NULL )
				unset( $theRecord[ $symbol ] );
			
			//
			// Invalid value.
			//
			elseif( $ok === FALSE )
				$error = TRUE;
		
		} // Scalar value.
		
		//
		// Handle errors.
		//
		if( $error )
			return
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Transaction type.
					kTYPE_STATUS_ERROR,							// Transaction status.
					'Invalid boolean value.',					// Transaction message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					kTYPE_ERROR_INVALID_VALUE,					// Error type.
					kTYPE_ERROR_CODE_BAD_BOOLEAN,				// Error code.
					NULL										// Error resource.
				);																	// ==>
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$count = strlen( $tokens );
		
		//
		// Handle missing tokens.
		//
		if( ! $count )
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
				);																	// ==>
		
		//
		// Handle too many tokens.
		//
		if( $count > 3 )
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
				);																	// ==>
		
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
			$ok = ( $count == 1 )
				? CheckArrayValue( $tmp, $tokens )
				: CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) );
			if( ! $ok )
			{
				unset( $theRecord[ $symbol ] );
				
				return TRUE;														// ==>
			}
			
			//
			// Iterate list.
			//
			$theRecord[ $symbol ] = Array();
			foreach( $tmp as $element )
			{
				if( $count == 1 )
					$theRecord[ $symbol ][] = array( $element );
				elseif( CheckArrayValue( $element, substr( $tokens, 1 ) ) )
					$theRecord[ $symbol ][] = $element;
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
		
		} // Scalar.
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$count = strlen( $tokens );
		
		//
		// Handle missing tokens.
		//
		if( ! $count )
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
				);																	// ==>
		
		//
		// Handle too many tokens.
		//
		if( $count > 3 )
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
				);																	// ==>
		
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
			$ok = ( $count == 1 )
				? CheckArrayValue( $tmp, $tokens )
				: CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) );
			if( ! $ok )
			{
				unset( $theRecord[ $symbol ] );
				
				return TRUE;														// ==>
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
				{
					//
					// Allocate string.
					//
					$index = count( $list_reference );
					$list_reference[ $index ] = Array();
					$string_reference = & $list_reference[ $index ];
					
					//
					// Set string.
					//
					$this->setLanguageString( $string_reference, NULL, $list );
				}
				
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
							// Allocate string.
							//
							$index = count( $list_reference );
							$list_reference[ $index ] = Array();
							$string_reference = & $list_reference[ $index ];
					
							//
							// Has not language separator token.
							//
							if( $count == 2 )
								$this->setLanguageString(
									$string_reference, NULL, $element );
							
							//
							// Has language separator token.
							//
							elseif( CheckArrayValue( $element, substr( $tokens, 2, 1 ) ) )
							{
								//
								// No language.
								//
								if( count( $element ) == 1 )
									$this->setLanguageString(
										$string_reference, NULL, $element[ 0 ] );
								
								//
								// Has language.
								//
								if( count( $element ) == 2 )
									$this->setLanguageString(
										$string_reference, $element[ 0 ], $element[ 1 ] );
								
								//
								// Is a mess.
								//
								else
								{
									$lang = $element[ 0 ];
									array_shift( $element );
									$text = implode( substr( $tokens, 2, 1 ), $element );
									$this->setLanguageString(
										$string_reference, $lang, $text );
								
								} // String is split.
							
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
				unset( $theRecord[ $symbol ] );
			
			//
			// Iterate strings.
			//
			$theRecord[ $symbol ] = Array();
			foreach( $tmp as $element )
			{
				//
				// No language.
				//
				if( count( $element ) == 1 )
					$this->setLanguageString(
						$theRecord[ $symbol ], NULL, $element[ 0 ] );
				
				//
				// Has language.
				//
				if( count( $element ) == 2 )
					$this->setLanguageString(
						$theRecord[ $symbol ], $element[ 0 ], $element[ 1 ] );
				
				//
				// String is split.
				//
				else
				{
					$lang = $element[ 0 ];
					array_shift( $element );
					$text = implode( substr( $tokens, 2, 1 ), $element );
					$this->setLanguageString( $theRecord[ $symbol ], $lang, $text );
				}
			
			} // Iterating strings.
		
		} // Scalar.
		
		//
		// Handle empty set.
		//
		if( ! count( $theRecord[ $symbol ] ) )
			unset( $theRecord[ $symbol ] );
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$count = strlen( $tokens );
		
		//
		// Handle missing tokens.
		//
		if( ! $count )
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
				);																	// ==>
		
		//
		// Handle too many tokens.
		//
		if( $count > 4 )
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
				);																	// ==>
		
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
			$ok = ( $count == 1 )
				? CheckArrayValue( $tmp, $tokens )
				: CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) );
			if( ! $ok )
			{
				unset( $theRecord[ $symbol ] );
				
				return TRUE;														// ==>
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
				{
					//
					// Allocate string.
					//
					$index = count( $list_reference );
					$list_reference[ $index ] = Array();
					$string_reference = & $list_reference[ $index ];
					
					//
					// Set string.
					//
					$this->setLanguageString( $string_reference, NULL, array( $list ) );
				}
				
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
							// Allocate string.
							//
							$index = count( $list_reference );
							$list_reference[ $index ] = Array();
							$string_reference = & $list_reference[ $index ];
					
							//
							// Has not language separator token.
							//
							if( $count == 2 )
								$this->setLanguageString(
									$string_reference, NULL, array( $element ) );
							
							//
							// Has language separator token.
							//
							elseif( CheckArrayValue( $element, substr( $tokens, 2, 1 ) ) )
							{
								//
								// No language.
								//
								if( count( $element ) == 1 )
									$this->setLanguageString(
										$string_reference, NULL, array( $element[ 0 ] ) );
								
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
										$this->setLanguageString(
											$string_reference,
											$element[ 0 ],
											array( $element[ 1 ] ) );
									
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
											$this->setLanguageString(
												$string_reference,
												$element[ 0 ],
												$element[ 1 ] );
									
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
				unset( $theRecord[ $symbol ] );
			
			//
			// Iterate strings.
			//
			$theRecord[ $symbol ] = Array();
			foreach( $tmp as $element )
			{
				//
				// No language separator.
				//
				if( count( $tokens ) == 1 )
					$this->setLanguageString(
						$theRecord[ $symbol ], NULL, array( $element ) );
				
				//
				// Has language separator.
				//
				elseif( count( $tokens ) == 2 )
				{
					//
					// Split language.
					//
					if( CheckArrayValue( $element, substr( $tokens, 1, 1 ) ) )
					{
						//
						// Is a mess.
						//
						if( count( $element ) > 2 )
						{
							$lang = $element[ 0 ];
							array_shift( $element );
							$text = implode( substr( $tokens, 2, 1 ), $element );
							$element = array( $lang, $text );
						
						} // String is split.
						
						//
						// Has no strings list separator.
						//
						if( $count == 2 )
							$this->setLanguageString(
								$theRecord[ $symbol ],
								$element[ 0 ],
								array( $element[ 1 ] ) );
						
						//
						// Has strings list separator.
						//
						else
						{
							//
							// Split strings.
							//
							if( CheckArrayValue( $element[ 1 ], substr( $tokens, 2, 1 ) ) )
								$this->setLanguageString(
									$theRecord[ $symbol ],
									$element[ 0 ],
									$element[ 1 ] );
						
						} // Has strings list separator.
					
					} // Has data.
				
				} // Has language separator.
			
			} // Iterating strings.
		
		} // Scalar.
		
		//
		// Handle empty set.
		//
		if( ! count( $theRecord[ $symbol ] ) )
			unset( $theRecord[ $symbol ] );
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$kind = $theFieldTag->offsetGet( kTAG_DATA_KIND );
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$count = strlen( $tokens );
		
		//
		// Handle missing tokens.
		//
		if( ! $count )
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
				);																	// ==>
		
		//
		// Handle too many tokens.
		//
		if( $count > 3 )
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
				);																	// ==>
		
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
			$ok = ( $count == 1 )
				? CheckArrayValue( $tmp, $tokens )
				: CheckArrayValue( $tmp, substr( $tokens, 0, 1 ) );
			if( ! $ok )
			{
				unset( $theRecord[ $symbol ] );
				
				return TRUE;														// ==>
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
				{
					//
					// Allocate string.
					//
					$index = count( $list_reference );
					$list_reference[ $index ] = Array();
					$string_reference = & $list_reference[ $index ];
					
					//
					// Set string.
					//
					$this->setTypedList( $string_reference, kTAG_TEXT, NULL, $list );
				}
				
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
							// Allocate string.
							//
							$index = count( $list_reference );
							$list_reference[ $index ] = Array();
							$string_reference = & $list_reference[ $index ];
					
							//
							// Has not language separator token.
							//
							if( $count == 2 )
								$this->setTypedList(
									$string_reference, kTAG_TEXT, NULL, $element );
							
							//
							// Has language separator token.
							//
							elseif( CheckArrayValue( $element, substr( $tokens, 2, 1 ) ) )
							{
								//
								// No language.
								//
								if( count( $element ) == 1 )
									$this->setTypedList(
										$string_reference, kTAG_TEXT, NULL, $element[ 0 ] );
								
								//
								// Has language.
								//
								if( count( $element ) == 2 )
									$this->setTypedList(
										$string_reference,
										kTAG_TEXT,
										$element[ 0 ],
										$element[ 1 ] );
								
								//
								// Is a mess.
								//
								else
								{
									$type = $element[ 0 ];
									array_shift( $element );
									$value = implode( substr( $tokens, 2, 1 ), $element );
									$this->setTypedList(
										$string_reference,
										kTAG_TEXT,
										$type,
										$value );
								
								} // String is split.
							
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
				unset( $theRecord[ $symbol ] );
			
			//
			// Iterate strings.
			//
			$theRecord[ $symbol ] = Array();
			foreach( $tmp as $element )
			{
				//
				// No language.
				//
				if( count( $element ) == 1 )
					$this->setTypedList(
						$theRecord[ $symbol ],
						kTAG_TEXT,
						NULL,
						$element[ 0 ] );
				
				//
				// Has language.
				//
				if( count( $element ) == 2 )
					$this->setTypedList(
						$theRecord[ $symbol ],
						kTAG_TEXT,
						$element[ 0 ],
						$element[ 1 ] );
				
				//
				// Value is split.
				//
				else
				{
					$type = $element[ 0 ];
					array_shift( $element );
					$value = implode( substr( $tokens, 2, 1 ), $element );
					$this->setTypedList(
						$theRecord[ $symbol ],
						kTAG_TEXT,
						$type,
						$value );
				}
			
			} // Iterating strings.
		
		} // Scalar.
		
		//
		// Handle empty set.
		//
		if( ! count( $theRecord[ $symbol ] ) )
			unset( $theRecord[ $symbol ] );
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
				$ok = CheckShapeValue( $element );
				
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
					switch( $ok )
					{
						case kTYPE_ERROR_CODE_NO_SHAPE_TYPE:
							$message = 'Missing shape type.';
							$error_type = kTYPE_ERROR_INVALID_VALUE;
							break;
						
						case kTYPE_ERROR_CODE_BAD_SHAPE_TYPE:
							$message = 'Invalid or unsupported shape type.';
							$error_type = kTYPE_ERROR_INVALID_VALUE;
							break;
						
						case kTYPE_ERROR_CODE_BAD_SHAPE_GEOMETRY:
							$message = 'Invalid shape geometry.';
							$error_type = kTYPE_ERROR_INVALID_VALUE;
							break;
					}
					
					return
						$this->failTransactionLog(
							$theTransaction,							// Transaction.
							$this->transaction(),						// Parent.
							kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
							kTYPE_STATUS_ERROR,							// Status.
							$message,									// Message.
							$theWorksheet,								// Worksheet.
							$theRow,									// Row.
							$theFieldData[ 'column_name' ],				// Column.
							$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
							$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
							$theRecord[ $symbol ],						// Value.
							$error_type,								// Error type.
							$ok,										// Error code.
							NULL										// Error resource.
						);															// ==>
				
				} // Error.
			
			} // Iterating elements.
			
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
			
			return TRUE;															// ==>
		
		} // List.
		
		//
		// Check value.
		//
		$ok = CheckShapeValue( $theRecord[ $symbol ] );
		
		//
		// Empty value.
		//
		elseif( $ok === NULL )
			unset( $theRecord[ $symbol ] );
		
		//
		// Invalid value.
		//
		elseif( $ok !== TRUE )
		{
			switch( $ok )
			{
				case kTYPE_ERROR_CODE_NO_SHAPE_TYPE:
					$message = 'Missing shape type.';
					$error_type = kTYPE_ERROR_INVALID_VALUE;
					break;
				
				case kTYPE_ERROR_CODE_BAD_SHAPE_TYPE:
					$message = 'Invalid or unsupported shape type.';
					$error_type = kTYPE_ERROR_INVALID_VALUE;
					break;
				
				case kTYPE_ERROR_CODE_BAD_SHAPE_GEOMETRY:
					$message = 'Invalid shape geometry.';
					$error_type = kTYPE_ERROR_INVALID_VALUE;
					break;
			}
			
			return
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
					kTYPE_STATUS_ERROR,							// Status.
					$message,									// Message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					$error_type,								// Error type.
					$ok,										// Error code.
					NULL										// Error resource.
				);																	// ==>
		
		} // Error.
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$error = FALSE;
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
			// Handle missing tokens.
			//
			if( ! count( $tokens ) )
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
			if( $count > 1 )
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
			$elements = ( strlen( $tokens ) )
					  ? explode( $tokens, $theRecord[ $symbol ] )
					  : array( $theRecord[ $symbol ] );
			
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
					// Check link.
					//
					if( @get_headers( $element ) === FALSE )
					{
						$error = TRUE;
						break;												// =>
					}
		
					//
					// Add value.
					//
					$result[] = $element;
				
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
				
				return TRUE;														// ==>
			
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
				// Check link.
				//
				if( @get_headers( $theRecord[ $symbol ] ) === FALSE )
					$error = TRUE;
			
			} // Not empty.
			
			//
			// Remove if empty.
			//
			else
				unset( $theRecord[ $symbol ] );
		
		} // Scalar value.
		
		//
		// Handle errors.
		//
		if( $error )
			return
				$this->failTransactionLog(
					$theTransaction,								// Transaction.
					$this->transaction(),							// Parent transaction.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,					// Transaction type.
					kTYPE_STATUS_WARNING,							// Transaction status.
					'Invalid or inactive link.',					// Transaction message.
					$theWorksheet,									// Worksheet.
					$theRow,										// Row.
					$theFieldData[ 'column_name' ],					// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
					$theRecord[ $symbol ],							// Value.
					kTYPE_ERROR_INVALID_VALUE,						// Error type.
					kTYPE_ERROR_CODE_BAD_LINK,						// Error code.
					NULL											// Error resource.
				);																	// ==>
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$theFieldData = $theFieldData[ $symbol ];
		
		//
		// Cast date.
		//
		$date = $theRecord[ $symbol ] = (string) $theRecord[ $symbol ];
		
		//
		// Handle non-standard format.
		//
		if( ! ctype_digit( $theRecord[ $symbol ] ) )
		{
			//
			// Check - separator.
			//
			if( strpos( '-', $date ) === FALSE )
			{
				//
				// Check / separator.
				//
				if( strpos( '/', $date ) === FALSE )
				{
					//
					// Check space separator.
					//
					if( strpos( ' ', $date ) === FALSE )
						return
							$this->failTransactionLog(
								$theTransaction,							// Transaction.
								$this->transaction(),						// Parent.
								kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
								kTYPE_STATUS_ERROR,							// Status.
								'Invalid date format.',						// Message.
								$theWorksheet,								// Worksheet.
								$theRow,									// Row.
								$theFieldData[ 'column_name' ],				// Column.
								$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
								$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
								$theRecord[ $symbol ],						// Value.
								kTYPE_ERROR_INVALID_VALUE,					// Error type.
								kTYPE_ERROR_CODE_BAD_DATE_FORMAT,			// Error code.
								NULL										// Error res.
							);														// ==>
					
					else
						$items = explode( ' ', $date );
				
				} // No slash separator.
				
				else
					$items = explode( '/', $date );
			
			} // No dash separator.
			
			else
				$items = explode( '-', $date );
			
			//
			// Normalise elements.
			//
			$elements = Array();
			foreach( $items as $item )
			{
				if( strlen( $item = trim( $item ) ) )
					$elements[] = $item;
			}
			
			//
			// Check format.
			//
			if( (! count( $elements ))										// No elements,
			 || (count( $elements ) != 3)									// or not ok,
			 || ( (strlen( $elements[ 0 ] ) != 4)							// or no start y
			   && (strlen( $elements[ count( $elements ) - 1 ] ) != 4) ) )	// and no end y.
				return
					$this->failTransactionLog(
						$theTransaction,							// Transaction.
						$this->transaction(),						// Parent.
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
						kTYPE_STATUS_ERROR,							// Status.
						'Invalid date format.',						// Message.
						$theWorksheet,								// Worksheet.
						$theRow,									// Row.
						$theFieldData[ 'column_name' ],				// Column.
						$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
						$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
						$theRecord[ $symbol ],						// Value.
						kTYPE_ERROR_INVALID_VALUE,					// Error type.
						kTYPE_ERROR_CODE_BAD_DATE_FORMAT,			// Error code.
						NULL										// Error res.
					);																// ==>
			
			//
			// Init date.
			//
			$date = '';
			
			//
			// Check YYYYMMDD.
			//
			if( strlen( $elements[ 0 ] ) == 4 )
			{
				foreach( $elements as $element )
					$date .= $element;
			}
			
			//
			// Check DDMMYYYY.
			//
			else
			{
				for( $i = count( $elements ) - 1; $i >= 0; $i-- )
					$date .= $elements[ $i ];
			}
		
		} // Non-standard format.
		
		//
		// Check date content.
		//
		if( ! ctype_digit( $date ) )
			return
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
					kTYPE_STATUS_ERROR,							// Status.
					'Invalid date contents.',					// Message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					kTYPE_ERROR_INVALID_VALUE,					// Error type.
					kTYPE_ERROR_CODE_BAD_DATE_FORMAT,			// Error code.
					NULL										// Error res.
				);																	// ==>
		
		//
		// Check full date.
		//
		if( strlen( $date ) == 8 )
		{
			$y = (int) substr( $date, 0, 4 );
			$m = (int) substr( $date, 4, 2 );
			$d = (int) substr( $date, 6, 2 );
		}
	
		//
		// Month.
		//
		elseif( strlen( $date ) == 6 )
		{
			$y = (int) substr( $date, 0, 4 );
			$m = (int) substr( $date, 4, 2 );
			$d = 1;
		}
	
		//
		// Year.
		//
		elseif( strlen( $date ) == 4 )
		{
			$y = (int) substr( $date, 0, 4 );
			$m = 1;
			$d = 1;
		}
		
		//
		// Bad format.
		//
		else
			return
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
					kTYPE_STATUS_ERROR,							// Status.
					'Invalid date structure.',					// Message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					kTYPE_ERROR_INVALID_VALUE,					// Error type.
					kTYPE_ERROR_CODE_BAD_DATE_FORMAT,			// Error code.
					NULL										// Error res.
				);																	// ==>
		
		//
		// Check date.
		//
		if( ! checkdate( $m, $d, $y ) )
			return
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
					kTYPE_STATUS_ERROR,							// Status.
					'Invalid date value.',						// Message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					kTYPE_ERROR_INVALID_VALUE,					// Error type.
					kTYPE_ERROR_CODE_BAD_DATE,					// Error code.
					NULL										// Error res.
				);																	// ==>
		
		//
		// Check year.
		//
		if( ($y < 1900)
		 || ($y > (int) date( "Y" )) )
			return
				$this->failTransactionLog(
					$theTransaction,							// Transaction.
					$this->transaction(),						// Parent.
					kTYPE_TRANS_TMPL_WORKSHEET_ROW,				// Type.
					kTYPE_STATUS_WARNING,						// Status.
					'Double check if year is correct.',			// Message.
					$theWorksheet,								// Worksheet.
					$theRow,									// Row.
					$theFieldData[ 'column_name' ],				// Column.
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),	// Alias.
					$theFieldNode->offsetGet( kTAG_TAG ),		// Tag.
					$theRecord[ $symbol ],						// Value.
					kTYPE_ERROR_DUBIOUS_VALUE,					// Error type.
					kTYPE_ERROR_CODE_DUBIOUS_YEAR,				// Error code.
					NULL										// Error res.
				);																	// ==>
		
		//
		// Set date.
		//
		$theRecord[ $symbol ] = $date;
		
		return TRUE;																// ==>

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
	 * @return boolean				<tt>TRUE</tt> correct value.
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
		$combinations = Array();
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		$theFieldData = $theFieldData[ $symbol ];
		$prefix = $theFieldNode->offsetGet( kTAG_PREFIX );
		$suffix = $theFieldNode->offsetGet( kTAG_SUFFIX );
		$collection
			= Term::ResolveCollection(
				Term::ResolveDatabase( $this->wrapper(), TRUE ),
				TRUE );
		
		//
		// Cast enumeration.
		//
		$theRecord[ $symbol ] = (string) $theRecord[ $symbol ];
		
		//
		// Handle no prefix or suffix.
		//
		if( ($prefix === NULL)
		 && ($suffix === NULL) )
			$combinations[] = $theRecord[ $symbol ];
		
		//
		// Handle prefix.
		//
		elseif( is_array( $prefix ) )
		{
			//
			// Iterate prefixes.
			//
			foreach( $prefix as $pre )
			{
				//
				// Handle suffixes.
				//
				if( is_array( $suffix ) )
				{
					//
					// Iterate suffixes.
					//
					foreach( $suffix as $suf )
						$combinations[] = $pre.$theRecord[ $symbol ].$suf;
				
				} // Has suffix.
				
				//
				// Handle no suffix.
				//
				else
					$combinations[] = $pre.$theRecord[ $symbol ];
			
			} // Iterating prefixes.
		
		} // Has prefix.
		
		//
		// Handle suffix.
		//
		else
		{
			//
			// Iterate suffixes.
			//
			foreach( $suffix as $suf )
				$combinations[] = $theRecord[ $symbol ].$suf;
		
		} // Has suffix.
		
		//
		// Check combinations.
		//
		foreach( $combinations as $combination )
		{
			//
			// Check terms.
			//
			if( $collection->matchOne( array( kTAG_NID => $combination ), kQUERY_COUNT ) )
			{
				//
				// Set value.
				//
				$theRecord[ $symbol ] = $combination;
				
				return TRUE;														// ==>
			}
		
		} // Iterating combinations.
		
		return
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
				$theRecord[ $symbol ],						// Value.
				kTYPE_ERROR_INVALID_CODE,					// Error type.
				kTYPE_ERROR_CODE_INVALID_ENUM,				// Error code.
				NULL										// Error res.
			);																		// ==>

	} // validateEnum.

	

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
	 *	setLanguageString																*
	 *==================================================================================*/

	/**
	 * Set a language string entry
	 *
	 * This method can be used to add an entry to a language string property, type
	 * {@link kTYPE_LANGUAGE_STRING}, the method expects the destination container, the
	 * language code and the string.
	 *
	 * @param array				   &$theContainer		Language string container.
	 * @param string				$theLanguage		Language code.
	 * @param string				$theString			String.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> added, <tt>FALSE</tt> updated.
	 */
	public function setLanguageString( &$theContainer, $theLanguage, $theString )
	{
		//
		// Init container
		//
		if( ! is_array( $theContainer ) )
			$theContainer = Array();

		//
		// Trim parameters
		//
		$theString = trim( $theString );
		$theLanguage = trim( $theLanguage );
		
		//
		// Skip empty string.
		//
		if( strlen( $theString ) )
		{
			//
			// Handle language.
			//
			if( strlen( $theLanguage ) )
			{
				//
				// Locate language.
				//
				foreach( $theContainer as $key => $value )
				{
					if( array_key_exists( kTAG_LANGUAGE, $value )
					 && ($value[ kTAG_LANGUAGE ] == $theLanguage) )
					{
						$theContainer[ $key ][ kTAG_TEXT ] = $theString;

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_LANGUAGE => $theLanguage,
										 kTAG_TEXT => $theString );
				
				return TRUE;														// ==>
			
			} // Has language.
			
			//
			// Handle no language.
			//
			else
			{
				//
				// Locate no language.
				//
				foreach( $theContainer as $key => $value )
				{
					if( ! array_key_exists( kTAG_LANGUAGE, $value ) )
					{
						$theContainer[ $key ][ kTAG_TEXT ] = $theString;

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_TEXT => $theString );
				
				return TRUE;														// ==>
			
			} // No language.
		
		} // Not an empty string.

	} // setLanguageString.

	 
	/*===================================================================================
	 *	setLanguageStrings																*
	 *==================================================================================*/

	/**
	 * Set a language strings entry
	 *
	 * This method can be used to add an entry to a language strings property, type
	 * {@link kTYPE_LANGUAGE_STRINGS}, the method expects the destination container, the
	 * language code and the strings.
	 *
	 * @param array				   &$theContainer		Language string container.
	 * @param string				$theLanguage		Language code.
	 * @param array					$theStrings			Strings.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> added, <tt>FALSE</tt> updated.
	 */
	public function setLanguageStrings( &$theContainer, $theLanguage, $theStrings )
	{
		//
		// Init container
		//
		if( ! is_array( $theContainer ) )
			$theContainer = Array();

		//
		// Trim parameters
		//
		$theLanguage = trim( $theLanguage );
		
		//
		// Skip empty strings.
		//
		if( count( $theStrings ) )
		{
			//
			// Trim strings.
			//
			$strings = Array();
			foreach( $theStrings as $string )
			{
				if( strlen( $tmp = trim( $string ) ) )
					$strings[] = $tmp;
			}
			
			//
			// Normalise strings.
			//
			$theStrings = array_values( array_unique( $strings ) );
			
			//
			// Handle language.
			//
			if( strlen( $theLanguage ) )
			{
				//
				// Locate language.
				//
				foreach( $theContainer as $key => $value )
				{
					//
					// Match language.
					//
					if( array_key_exists( kTAG_LANGUAGE, $value )
					 && ($value[ kTAG_LANGUAGE ] == $theLanguage) )
					{
						//
						// Iterate strings.
						//
						foreach( $theStrings as $string )
						{
							if( ! in_array( $string, $theContainer[ $key ][ kTAG_TEXT ] ) )
								$theContainer[ $key ][ kTAG_TEXT ][]
									= $string;
						}

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_LANGUAGE => $theLanguage,
										 kTAG_TEXT => $theStrings );
				
				return TRUE;														// ==>
			
			} // Has language.
			
			//
			// Handle no language.
			//
			else
			{
				//
				// Locate no language.
				//
				foreach( $theContainer as $key => $value )
				{
					//
					// Match no language.
					//
					if( ! array_key_exists( kTAG_LANGUAGE, $value ) )
					{
						//
						// Iterate strings.
						//
						foreach( $theStrings as $string )
						{
							if( ! in_array( $string, $theContainer[ $key ][ kTAG_TEXT ] ) )
								$theContainer[ $key ][ kTAG_TEXT ][]
									= $string;
						}

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_TEXT => $theStrings );
				
				return TRUE;														// ==>
			
			} // No language.
		
		} // Not an empty string.

	} // setLanguageStrings.

	 
	/*===================================================================================
	 *	setTypedList																	*
	 *==================================================================================*/

	/**
	 * Set a typed list entry
	 *
	 * This method can be used to add an entry to a typed list property, type
	 * {@link kTYPE_TYPED_LIST}, the method expects the destination container, the value
	 * tag, the language code and the string.
	 *
	 * @param array				   &$theContainer		Language string container.
	 * @param string				$theTag				Value tag.
	 * @param string				$theType			Language code.
	 * @param string				$theValue			String.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> added, <tt>FALSE</tt> updated.
	 */
	public function setTypedList( &$theContainer, $theTag, $theType, $theValue )
	{
		//
		// Init container
		//
		if( ! is_array( $theContainer ) )
			$theContainer = Array();

		//
		// Trim parameters
		//
		$theValue = trim( $theValue );
		$theType = trim( $theType );
		
		//
		// Skip empty value.
		//
		if( strlen( $theValue ) )
		{
			//
			// Handle type.
			//
			if( strlen( $theType ) )
			{
				//
				// Locate type.
				//
				foreach( $theContainer as $key => $value )
				{
					if( array_key_exists( kTAG_TYPE, $value )
					 && ($value[ kTAG_TYPE ] == $theType) )
					{
						$theContainer[ $key ][ $theTag ] = $theValue;

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( kTAG_TYPE => $theType,
										 $theTag => $theValue );
				
				return TRUE;														// ==>
			
			} // Has type.
			
			//
			// Handle no type.
			//
			else
			{
				//
				// Locate no type.
				//
				foreach( $theContainer as $key => $value )
				{
					if( ! array_key_exists( kTAG_TYPE, $value ) )
					{
						$theContainer[ $key ][ $theTag ] = $theValue;

						return FALSE;												// ==>
					}
				}
				
				//
				// Set element.
				//
				$theContainer[] = array( $theTag => $theValue );
				
				return TRUE;														// ==>
			
			} // No type.
		
		} // Not an empty value.

	} // setTypedList.

	 

} // class SessionUpload.


?>
