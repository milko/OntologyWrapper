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
			// Create transaction.
			//
			if( $theTransaction === NULL )
				$theTransaction
					= $this->transaction()
						->newTransaction(
							kTYPE_TRANS_TMPL_WORKSHEET_ROW, $theWorksheet, $theRow );
			
			//
			// Set transaction status.
			//
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_ERROR );
			
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
				// Set log.
				//
				$theTransaction->setLog(
					kTYPE_STATUS_ERROR,					// Status,
					$symbol,							// Alias.
					$field[ 'column_name' ],			// Field.
					NULL,								// Value.
					'Missing required field.',			// Message.
					$tag,								// Tag.
					kTYPE_ERROR_MISSING_REQUIRED,		// Error type.
					kTYPE_ERROR_CODE_REQ_FIELD,			// Error code.
					NULL );								// Error resource.
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
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_INT:
				case kTYPE_YEAR:
					$this->validateInteger(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_FLOAT:
					$this->validateFloat(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_BOOLEAN:
					$this->validateBoolean(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_STRUCT:
					$this->validateStruct(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_ARRAY:
					$this->validateArray(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_LANGUAGE_STRING:
					$this->validateLanguageString(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_LANGUAGE_STRINGS:
					$this->validateLanguageStrings(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_TYPED_LIST:
					$this->validateTypedList(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_SHAPE:
					$this->validateShape(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			/*
				case kTYPE_URL:
					$this->validateLink(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_DATE:
					$this->validateDate(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_ENUM:
					$this->validateEnum(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			
				case kTYPE_SET:
					$this->validateEnumSet(
						$theTransaction, $theRecord, $theWorksheet, $theRow,
						$field_data, $field_node, $field_tag );
					break;
			*/
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
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
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
		
		//
		// Cast value.
		//
		$theRecord[ $symbol ] = (string) $theRecord[ $symbol ];

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
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
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
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		
		//
		// Cast value.
		//
		if( is_numeric( $theRecord[ $symbol ] ) )
			$theRecord[ $symbol ]
				= (int) $theRecord[ $symbol ];
		
		//
		// Handle error.
		//
		else
		{
			//
			// Init local storage.
			//
			$tag_id = ( $theFieldTag !== NULL )
					? $theFieldTag->offsetGet( kTAG_TAG )
					: NULL;
			
			//
			// Create transaction.
			//
			if( $theTransaction === NULL )
				$theTransaction
					= $this->transaction()
						->newTransaction(
							kTYPE_TRANS_TMPL_WORKSHEET_ROW, $theWorksheet, $theRow );
			
			//
			// Set transaction status.
			//
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_ERROR );
			
			//
			// Set log.
			//
			$theTransaction->setLog(
				kTYPE_STATUS_ERROR,								// Status,
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
				$theFieldData[ 'column_name' ],					// Field.
				$theRecord[ $symbol ],							// Value.
				'Invalid integer number.',						// Message.
				$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
				kTYPE_ERROR_INVALID_VALUE,						// Error type.
				kTYPE_ERROR_CODE_BAD_NUMBER,					// Error code.
				NULL );											// Error resource.
		
		} // Invalid value.

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
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
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
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		
		//
		// Cast value.
		//
		if( is_numeric( $theRecord[ $symbol ] ) )
			$theRecord[ $symbol ]
				= (double) $theRecord[ $symbol ];
		
		//
		// Handle error.
		//
		else
		{
			//
			// Init local storage.
			//
			$tag_id = ( $theFieldTag !== NULL )
					? $theFieldTag->offsetGet( kTAG_TAG )
					: NULL;
			
			//
			// Create transaction.
			//
			if( $theTransaction === NULL )
				$theTransaction
					= $this->transaction()
						->newTransaction(
							kTYPE_TRANS_TMPL_WORKSHEET_ROW, $theWorksheet, $theRow );
			
			//
			// Set transaction status.
			//
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_ERROR );
			
			//
			// Set log.
			//
			$theTransaction->setLog(
				kTYPE_STATUS_ERROR,								// Status,
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
				$theFieldData[ 'column_name' ],					// Field.
				$theRecord[ $symbol ],							// Value.
				'Invalid floating point number.',				// Message.
				$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
				kTYPE_ERROR_INVALID_VALUE,						// Error type.
				kTYPE_ERROR_CODE_BAD_NUMBER,					// Error code.
				NULL );											// Error resource.
		
		} // Invalid value.

	} // validateFloat.

	 
	/*===================================================================================
	 *	validateBoolean																	*
	 *==================================================================================*/

	/**
	 * Validate boolean
	 *
	 * This method will validate the provided boolean property, the value will be simply
	 * cast to a boolean, following these rules:
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
	 *	<li><em>other</em>: The value will be cast to a boolean..
	 * </ul>
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
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
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		
		//
		// Cast value.
		//
		switch( strtolower( $theRecord[ $symbol ] ) )
		{
			case '1':
				$theRecord[ $symbol ] = TRUE;
				break;
		
			case 'y':
				$theRecord[ $symbol ] = TRUE;
				break;
		
			case 'yes':
				$theRecord[ $symbol ] = TRUE;
				break;
		
			case 'true':
				$theRecord[ $symbol ] = TRUE;
				break;
		
			case '0':
				$theRecord[ $symbol ] = FALSE;
				break;
		
			case 'n':
				$theRecord[ $symbol ] = FALSE;
				break;
		
			case 'no':
				$theRecord[ $symbol ] = FALSE;
				break;
		
			case 'false':
				$theRecord[ $symbol ] = FALSE;
				break;
			
			default:
				$theRecord[ $symbol ] = (boolean) $theRecord[ $symbol ];
				break;
		
		} // Parsing value.

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
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
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
	 * {@link kTAG_TOKEN} element of the node in order to separate array elements: the first
	 * token represents the array elements separator, if there is a second token this will
	 * be used to separate the element key and value; if there is no {@link kTAG_TOKEN}
	 * element of the node, the array will have a single element with the contents of the
	 * field and a warning will be issued.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
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
		
		//
		// Handle tokens.
		//
		if( $count = strlen( $tokens ) )
		{
			//
			// Separate elements.
			//
			$elements = explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] );
			
			//
			// Separate items.
			//
			if( $count > 1 )
			{
				//
				// Init local storage.
				//
				$theRecord[ $symbol ] = Array();
				
				//
				// Iterate elements.
				//
				foreach( $elements as $element )
				{
					//
					// Separate items.
					//
					$items = explode( substr( $tokens, 1, 1 ), $element );
					
					//
					// Handle no key.
					//
					if( count( $items ) == 1 )
						$theRecord[ $symbol ][] = $items[ 1 ];
					
					//
					// Handle key.
					//
					elseif( count( $items ) == 2 )
						$theRecord[ $symbol ][ $items[ 0 ] ] = $items[ 1 ];
					
					//
					// Handle mess.
					//
					else
					{
						$key = $items[ 0 ];
						array_shift( $items );
						$value = implode( substr( $tokens, 1, 1 ), $items );
						$theRecord[ $symbol ][ $key ] = $value;
					}
				}
			
			} // Has items separaor.
			
			//
			// Array of elements.
			//
			else
				$theRecord[ $symbol ] = $elements;
		
		} // Provided separator tokens.
		
		//
		// Handle missing tokens.
		//
		else
		{
			//
			// Cast value.
			//
			$theRecord[ $symbol ] = array( $theRecord[ $symbol ] );
			
			//
			// New transaction.
			//
			if( $theTransaction === NULL )
			{
				//
				// Create transaction.
				//
				$theTransaction
					= $this->transaction()
						->newTransaction(
							kTYPE_TRANS_TMPL_WORKSHEET_ROW, $theWorksheet, $theRow );
			
				//
				// Set transaction status.
				//
				$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_WARNING );
			
			} // New transaction.
			
			//
			// Update transaction status.
			//
			else
			{
				//
				// Get status.
				//
				$status = $theTransaction->offsetGet( kTAG_TRANSACTION_STATUS );
				
				//
				// Update status.
				//
				if( ($status == kTYPE_STATUS_OK)
				 || ($status == kTYPE_STATUS_MESSAGE)
				 || ($status == kTYPE_STATUS_EXECUTING) )
					$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS,
												kTYPE_STATUS_WARNING );
			
			} // Update transaction status.
			
			//
			// Set log.
			//
			$theTransaction->setLog(
				kTYPE_STATUS_WARNING,							// Status,
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
				$theFieldData[ 'column_name' ],					// Field.
				NULL,											// Value.
				'Missing separator tokens in template.',		// Message.
				$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
				kTYPE_ERROR_BAD_TMPL_STRUCT,					// Error type.
				kTYPE_ERROR_CODE_NO_TOKEN,						// Error code.
				NULL );											// Error resource.
		
		} // Missing tokens.

	} // validateArray.

	 
	/*===================================================================================
	 *	validateLanguageString															*
	 *==================================================================================*/

	/**
	 * Validate language string
	 *
	 * This method will validate the provided language string property, it will load the
	 * {@link kTAG_TOKEN} element of the node in order to separate the elements and the
	 * language from the string.
	 *
	 * By default there should be two tokens: the first to separate elements and the second
	 * to separate the language from the string.
	 *
	 * If there is only one token, it is assumed there is no language.
	 *
	 * If the tokens are missing, a warning will be issued and the string will be set with
	 * the property without language.
	 *
	 * If there are more than 2 tokens, only the first two will be used and no warning will
	 * be issued.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
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
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		
		//
		// Handle tokens.
		//
		if( $count = strlen( $tokens ) )
		{
			//
			// Separate elements.
			//
			$elements = explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] );
			foreach( $elements as $element )
			{
				//
				// Handle no language.
				//
				if( $count == 1 )
					$this->setLanguageString(
						$theRecord[ $symbol ], NULL, $element );
				
				//
				// Handle language.
				//
				else
				{
					$items = explode( substr( $tokens, 1, 1 ), $element );
					if( count( $items ) == 1 )
						$this->setLanguageString(
							$theRecord[ $symbol ], NULL, $items[ 0 ] );
					elseif( count( $items ) == 2 )
						$this->setLanguageString(
							$theRecord[ $symbol ], $items[ 0 ], $items[ 1 ] );
					else
					{
						$lang = $items[ 0 ];
						array_shift( $items );
						$text = implode( substr( $tokens, 1, 1 ), $items );
						$this->setLanguageString(
							$theRecord[ $symbol ], $lang, $text );
					}
				}
			
			} // Iterating elements.
		
		} // Provided separator tokens.
		
		//
		// Handle missing tokens.
		//
		else
		{
			//
			// Cast value.
			//
			$theRecord[ $symbol ] = array( kTAG_TEXT => $theRecord[ $symbol ] );
			
			//
			// New transaction.
			//
			if( $theTransaction === NULL )
			{
				//
				// Create transaction.
				//
				$theTransaction
					= $this->transaction()
						->newTransaction(
							kTYPE_TRANS_TMPL_WORKSHEET_ROW, $theWorksheet, $theRow );
			
				//
				// Set transaction status.
				//
				$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_WARNING );
			
			} // New transaction.
			
			//
			// Update transaction status.
			//
			else
			{
				//
				// Get status.
				//
				$status = $theTransaction->offsetGet( kTAG_TRANSACTION_STATUS );
				
				//
				// Update status.
				//
				if( ($status == kTYPE_STATUS_OK)
				 || ($status == kTYPE_STATUS_MESSAGE)
				 || ($status == kTYPE_STATUS_EXECUTING) )
					$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS,
												kTYPE_STATUS_WARNING );
			
			} // Update transaction status.
			
			//
			// Set log.
			//
			$theTransaction->setLog(
				kTYPE_STATUS_WARNING,							// Status,
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
				$theFieldData[ 'column_name' ],					// Field.
				NULL,											// Value.
				'Missing separator tokens in template.',		// Message.
				$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
				kTYPE_ERROR_BAD_TMPL_STRUCT,					// Error type.
				kTYPE_ERROR_CODE_NO_TOKEN,						// Error code.
				NULL );											// Error resource.
		
		} // Missing tokens.

	} // validateLanguageString.

	 
	/*===================================================================================
	 *	validateLanguageStrings															*
	 *==================================================================================*/

	/**
	 * Validate language string
	 *
	 * This method will validate the provided language string property, it will load the
	 * {@link kTAG_TOKEN} element of the node in order to separate the elements, the
	 * language and the strings.
	 *
	 * By default there should be three tokens: the first to separate elements, the second
	 * to separate the language and the third to separate the strings.
	 *
	 * If the third token is missing it is assumed there is only one string; if the second
	 * token is missing, it is assumed that there is one string without language.
	 *
	 * If the tokens are missing, a warning will be issued and the string will be set with
	 * the property without language.
	 *
	 * If there are more than 3 tokens, only the first three will be used and no warning
	 * will be issued.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
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
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		
		//
		// Handle tokens.
		//
		if( $count = strlen( $tokens ) )
		{
			//
			// Separate elements.
			//
			$elements = explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] );
			foreach( $elements as $element )
			{
				//
				// Handle one token.
				//
				if( $count == 1 )
					$this->setLanguageStrings(
						$theRecord[ $symbol ], NULL, $array( $element ) );
				
				//
				// Handle two tokens.
				//
				elseif( $count == 2 )
				{
					//
					// Get language.
					//
					$items = explode( substr( $tokens, 1, 1 ), $element );
					if( count( $items ) == 1 )
						$this->setLanguageStrings(
							$theRecord[ $symbol ], NULL, $items );
					elseif( count( $items ) == 2 )
						$this->setLanguageStrings(
							$theRecord[ $symbol ], $items[ 0 ], $array( $items[ 1 ] ) );
					else
					{
						$lang = $items[ 0 ];
						array_shift( $items );
						$text = implode( substr( $tokens, 1, 1 ), $items );
						$this->setLanguageString(
							$theRecord[ $symbol ], $lang, array( $text ) );
					}
				}
				
				//
				// Handle at least three.
				//
				else
				{
					//
					// Get language.
					//
					$items = explode( substr( $tokens, 1, 1 ), $element );
					if( count( $items ) == 1 )
					{
						$lang = NULL;
						$text = explode( substr( $tokens, 2, 1 ), $items[ 0 ] );
						$this->setLanguageStrings( $theRecord[ $symbol ], $lang, $text );
					}
					elseif( count( $items ) == 2 )
					{
						$lang = $items[ 0 ];
						$text = explode( substr( $tokens, 2, 1 ), $items[ 1 ] );
						$this->setLanguageStrings( $theRecord[ $symbol ], $lang, $text );
					}
					else
					{
						$lang = $items[ 0 ];
						array_shift( $items );
						$text = implode( substr( $tokens, 1, 1 ), $items );
						$text = explode( substr( $tokens, 2, 1 ), $text );
						$this->setLanguageString(
							$theRecord[ $symbol ], $lang, $text );
					}
				}
			
			} // Iterating elements.
		
		} // Provided separator tokens.
		
		//
		// Handle missing tokens.
		//
		else
		{
			//
			// Cast value.
			//
			$theRecord[ $symbol ] = array( kTAG_TEXT => $theRecord[ $symbol ] );
			
			//
			// New transaction.
			//
			if( $theTransaction === NULL )
			{
				//
				// Create transaction.
				//
				$theTransaction
					= $this->transaction()
						->newTransaction(
							kTYPE_TRANS_TMPL_WORKSHEET_ROW, $theWorksheet, $theRow );
			
				//
				// Set transaction status.
				//
				$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_WARNING );
			
			} // New transaction.
			
			//
			// Update transaction status.
			//
			else
			{
				//
				// Get status.
				//
				$status = $theTransaction->offsetGet( kTAG_TRANSACTION_STATUS );
				
				//
				// Update status.
				//
				if( ($status == kTYPE_STATUS_OK)
				 || ($status == kTYPE_STATUS_MESSAGE)
				 || ($status == kTYPE_STATUS_EXECUTING) )
					$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS,
												kTYPE_STATUS_WARNING );
			
			} // Update transaction status.
			
			//
			// Set log.
			//
			$theTransaction->setLog(
				kTYPE_STATUS_WARNING,							// Status,
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
				$theFieldData[ 'column_name' ],					// Field.
				NULL,											// Value.
				'Missing separator tokens in template.',		// Message.
				$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
				kTYPE_ERROR_BAD_TMPL_STRUCT,					// Error type.
				kTYPE_ERROR_CODE_NO_TOKEN,						// Error code.
				NULL );											// Error resource.
		
		} // Missing tokens.

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
	 * By default there should be two tokens: the first to separate elements, the second
	 * to separate the type from the value.
	 *
	 * If there is only one token, the {@link kTAG_TYPE} is omitted.
	 *
	 * If the tokens are missing, a warning will be issued and the string will be set with
	 * the property without language.
	 *
	 * The value token is by default {@link kTAG_TEXT}.
	 *
	 * If there are more than 2 tokens, only the first two will be used and no warning will
	 * be issued.
	 *
	 * @param Transaction		   &$theTransaction		Transaction reference.
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
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
		$tokens = $theFieldNode->offsetGet( kTAG_TOKEN );
		$symbol = $theFieldNode->offsetGet( kTAG_ID_SYMBOL );
		
		//
		// Handle tokens.
		//
		if( $count = strlen( $tokens ) )
		{
			//
			// Separate elements.
			//
			$elements = explode( substr( $tokens, 0, 1 ), $theRecord[ $symbol ] );
			foreach( $elements as $element )
			{
				//
				// Handle no type.
				//
				if( $count == 1 )
					$this->setTypedList(
						$theRecord[ $symbol ], kTAG_TEXT, NULL, $element );
				
				//
				// Handle type.
				//
				else
				{
					$items = explode( substr( $tokens, 1, 1 ), $element );
					if( count( $items ) == 1 )
						$this->setTypedList(
							$theRecord[ $symbol ], kTAG_TEXT, NULL, $items[ 0 ] );
					elseif( count( $items ) == 2 )
						$this->setTypedList(
							$theRecord[ $symbol ], kTAG_TEXT, $items[ 0 ], $items[ 1 ] );
					else
					{
						$lang = $items[ 0 ];
						array_shift( $items );
						$text = implode( substr( $tokens, 1, 1 ), $items );
						$this->setTypedList(
							$theRecord[ $symbol ], kTAG_TEXT, $lang, $text );
					}
				}
			
			} // Iterating elements.
		
		} // Provided separator tokens.
		
		//
		// Handle missing tokens.
		//
		else
		{
			//
			// Cast value.
			//
			$theRecord[ $symbol ] = array( kTAG_TEXT => $theRecord[ $symbol ] );
			
			//
			// New transaction.
			//
			if( $theTransaction === NULL )
			{
				//
				// Create transaction.
				//
				$theTransaction
					= $this->transaction()
						->newTransaction(
							kTYPE_TRANS_TMPL_WORKSHEET_ROW, $theWorksheet, $theRow );
			
				//
				// Set transaction status.
				//
				$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_WARNING );
			
			} // New transaction.
			
			//
			// Update transaction status.
			//
			else
			{
				//
				// Get status.
				//
				$status = $theTransaction->offsetGet( kTAG_TRANSACTION_STATUS );
				
				//
				// Update status.
				//
				if( ($status == kTYPE_STATUS_OK)
				 || ($status == kTYPE_STATUS_MESSAGE)
				 || ($status == kTYPE_STATUS_EXECUTING) )
					$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS,
												kTYPE_STATUS_WARNING );
			
			} // Update transaction status.
			
			//
			// Set log.
			//
			$theTransaction->setLog(
				kTYPE_STATUS_WARNING,							// Status,
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
				$theFieldData[ 'column_name' ],					// Field.
				NULL,											// Value.
				'Missing separator tokens in template.',		// Message.
				$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
				kTYPE_ERROR_BAD_TMPL_STRUCT,					// Error type.
				kTYPE_ERROR_CODE_NO_TOKEN,						// Error code.
				NULL );											// Error resource.
		
		} // Missing tokens.

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
	 * @param array				   &$theRecord			Row data.
	 * @param string				$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theFieldData		Field data.
	 * @param Node					$theFieldNode		Field node or <tt>NULL</tt>.
	 * @param Tag					$theFieldTag		Field tag or <tt>NULL</tt>.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> correct shape.
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
		
		//
		// Get type.
		//
		$items = explode( '=', $theRecord[ $symbol ] );
		if( count( $items ) == 2 )
		{
			//
			// Save by type.
			//
			$type = trim( $items[ 0 ] );
			
			//
			// Handle point.
			//
			if( $type == 'Point' )
			{
				//
				// Parse geometry.
				//
				$geometry = ParseGeometry( $items[ 1 ] );
				if( $geometry !== FALSE )
				{
					//
					// Check ring.
					//
					if( count( $geometry ) == 1 )
					{
						//
						// Check points.
						//
						if( count( $geometry[ 0 ] ) == 1 )
						{
							//
							// Check coordinates.
							//
							if( count( $geometry[ 0 ][ 0 ] ) == 2 )
							{
								//
								// Set shape.
								//
								$theRecord[ $symbol ]
									= array( kTAG_TYPE => $type,
											 kTAG_GEOMETRY => $geometry[ 0 ][ 0 ] );
								
								return TRUE;										// ==>
							
							} // Two coordinates.
						
						} // One point.
					
					} // One ring.
				
				} // Correct geometry.
			
			} // Point.
			
			//
			// Handle circle.
			//
			elseif( $type == 'Circle' )
			{
				//
				// Parse geometry.
				//
				$geometry = ParseGeometry( $items[ 1 ] );
				if( $geometry !== FALSE )
				{
					//
					// Check ring.
					//
					if( count( $geometry ) == 1 )
					{
						//
						// Check points.
						//
						if( count( $geometry[ 0 ] ) == 1 )
						{
							//
							// Check coordinates.
							//
							if( count( $geometry[ 0 ][ 0 ] ) == 3 )
							{
								//
								// Set shape.
								//
								$theRecord[ $symbol ]
									= array( kTAG_TYPE => $type,
											 kTAG_RADIUS => $geometry[ 0 ][ 0 ][ 2 ],
											 kTAG_GEOMETRY
											 	=> array( $geometry[ 0 ][ 0 ][ 0 ],
											 			  $geometry[ 0 ][ 0 ][ 1 ] ) );
								
								return TRUE;										// ==>
							
							} // Two coordinates.
						
						} // One point.
					
					} // One ring.
				
				} // Correct geometry.
			
			} // Circle.
			
			//
			// Handle multipoint.
			//
			elseif( ($type == 'MultiPoint')
				 || ($type == 'LineString') )
			{
				//
				// Parse geometry.
				//
				$geometry = ParseGeometry( $items[ 1 ] );
				if( $geometry !== FALSE )
				{
					//
					// Check ring.
					//
					if( count( $geometry ) == 1 )
					{
						//
						// Check points.
						//
						if( count( $geometry[ 0 ] ) > 1 )
						{
							//
							// Set shape.
							//
							$theRecord[ $symbol ]
								= array( kTAG_TYPE => $type,
										 kTAG_GEOMETRY => $geometry[ 0 ] );
							
							return TRUE;											// ==>
						
						} // One point.
					
					} // One ring.
				
				} // Correct geometry.
			
			} // MultiPoint or LineString.
			
			//
			// Handle polygon.
			//
			elseif( $type == 'Polygon' )
			{
				//
				// Parse geometry.
				//
				$geometry = ParseGeometry( $items[ 1 ] );
				if( $geometry !== FALSE )
				{
					//
					// Set shape.
					//
					$theRecord[ $symbol ]
						= array( kTAG_TYPE => $type,
								 kTAG_GEOMETRY => $geometry );
					
					return TRUE;													// ==>
				
				} // Correct geometry.
			
			} // Polygon.
			
			//
			// Unsupported type.
			//
			else
			{
				//
				// Create transaction.
				//
				if( $theTransaction === NULL )
					$theTransaction
						= $this->transaction()
							->newTransaction(
								kTYPE_TRANS_TMPL_WORKSHEET_ROW,
								$theWorksheet,
								$theRow );
		
				//
				// Set transaction status.
				//
				$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_ERROR );
			
				//
				// Set log.
				//
				$theTransaction->setLog(
					kTYPE_STATUS_ERROR,								// Status,
					$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
					$theFieldData[ 'column_name' ],					// Field.
					$theRecord[ $symbol ],							// Value.
					"Invalid or unsupported shape type [$type].",	// Message.
					$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
					kTYPE_ERROR_INVALID_VALUE,						// Error type.
					kTYPE_ERROR_CODE_BAD_SHAPE_TYPE,				// Error code.
					NULL );											// Error resource.
			
			} // Unsupported or invalid type.
		
		} // Has type.
		
		//
		// Handle missing type.
		//
		else
		{
			//
			// Create transaction.
			//
			if( $theTransaction === NULL )
				$theTransaction
					= $this->transaction()
						->newTransaction(
							kTYPE_TRANS_TMPL_WORKSHEET_ROW,
							$theWorksheet,
							$theRow );
		
			//
			// Set transaction status.
			//
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_ERROR );
			
			//
			// Set log.
			//
			$theTransaction->setLog(
				kTYPE_STATUS_ERROR,								// Status,
				$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
				$theFieldData[ 'column_name' ],					// Field.
				$theRecord[ $symbol ],							// Value.
				'Missing shape type.',							// Message.
				$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
				kTYPE_ERROR_INVALID_VALUE,						// Error type.
				kTYPE_ERROR_CODE_NO_SHAPE_TYPE,					// Error code.
				NULL );											// Error resource.
		
		} // Missing type.
		
		//
		// Create transaction.
		//
		if( $theTransaction === NULL )
			$theTransaction
				= $this->transaction()
					->newTransaction(
						kTYPE_TRANS_TMPL_WORKSHEET_ROW,
						$theWorksheet,
						$theRow );
	
		//
		// Set transaction status.
		//
		$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_ERROR );
		
		//
		// Set log.
		//
		$theTransaction->setLog(
			kTYPE_STATUS_ERROR,								// Status,
			$theFieldNode->offsetGet( kTAG_ID_SYMBOL ),		// Alias.
			$theFieldData[ 'column_name' ],					// Field.
			$theRecord[ $symbol ],							// Value.
			'Invalid geometry for [$type].',				// Message.
			$theFieldNode->offsetGet( kTAG_TAG ),			// Tag.
			kTYPE_ERROR_INVALID_VALUE,						// Error type.
			kTYPE_ERROR_CODE_BAD_SHAPE_GEOMETRY,			// Error code.
			NULL );											// Error resource.

	} // validateShape.

	

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
