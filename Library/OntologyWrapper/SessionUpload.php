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
		
		//
		// Iterate worksheets.
		//
		foreach( array_keys( $this->mParser->getWorksheets() ) as $worksheet )
		{
			$name = $this->getCollectionName( $worksheet );
			$this->mCollections[ $name ]
				= Session::ResolveDatabase( $this->wrapper(), TRUE )
					->collection( $name, TRUE );
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
	 * <em>intercepted</em> error, it will set the progress to 100, set the ending time and
	 * set the failed status.
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
		// Close session.
		//
		$transaction->offsetSet( kTAG_SESSION_END, TRUE );
		$transaction->offsetSet( kTAG_SESSION_STATUS, $theStatus );
		
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

	 

} // class SessionUpload.


?>
