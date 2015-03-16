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
			
			//
			// Transaction relationships.
			//
			if( ! $this->sessionRelationships() )
				return $this->failSession();										// ==>
			
			//
			// Progress.
			//
			$this->session()->progress( 10 );
			
			//
			// Transaction objects.
			//
			if( ! $this->sessionObjects() )
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
		
		//
		// Init progress.
		//
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
						->newTransaction( kTYPE_TRANS_TMPL_WORKSHEET,
										  kTYPE_STATUS_EXECUTING,
										  $wname ) );
			
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
			if( $transaction->offsetGet( kTAG_TRANSACTION_STATUS )
					== kTYPE_STATUS_EXECUTING )
				$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		} // Iterating worksheets.
		
		return TRUE;																// ==>

	} // sessionValidation.

	 
	/*===================================================================================
	 *	sessionRelationships															*
	 *==================================================================================*/

	/**
	 * Validate worksheet relationships
	 *
	 * This method will validate the relationships between worksheets, if a worksheet row
	 * points to a non existant other worksheet row, a warning will be issued: that is
	 * because it is not a formal error.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 * @uses transaction()
	 * @uses saveTemplateFile()
	 */
	protected function sessionRelationships()
	{
		//
		// Get related fields.
		//
		$worksheets = $fields = Array();
		$tmp = $this->mParser->getWorksheets();
		foreach( $this->mParser->getFields() as $worksheet => $field )
		{
			foreach( $field as $key => $value )
			{
				if( array_key_exists( 'field', $value )
				 && array_key_exists( 'worksheet', $value ) )
				{
					if( ! array_key_exists( $worksheet, $fields ) )
					{
						$worksheets[ $worksheet ] = $tmp[ $worksheet ];
						$fields[ $worksheet ] = Array();
					
					} $fields[ $worksheet ][ $key ] = $value;
				}
			}
		}
		
		//
		// Iterate worksheets.
		//
		foreach( $worksheets as $wname => $worksheet )
		{
			//
			// Init loop storage.
			//
			$records
				= $this->mCollections[ $this->getCollectionName( $wname ) ]
					->matchAll( Array(), kQUERY_COUNT );
		
			//
			// Instantiate transaction.
			//
			$transaction
				= $this->transaction(
					$this->session()
						->newTransaction( kTYPE_TRANS_TMPL_RELATIONSHIPS,
										  kTYPE_STATUS_EXECUTING,
										  $wname ) );
			
			//
			// Set records count.
			//
			$transaction->offsetSet( kTAG_COUNTER_RECORDS, $records );
			
			//
			// Check worksheet relationships.
			//
			if( ! $this->checkWorksheetRelationships( $wname, $fields, $records ) )
				return FALSE;														// ==>
	
			//
			// Close transaction.
			//
			$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
			if( $transaction->offsetGet( kTAG_TRANSACTION_STATUS )
					== kTYPE_STATUS_EXECUTING )
				$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		} // Iterating worksheets.
		
		return TRUE;																// ==>

	} // sessionRelationships.

	 
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
		// Init local storage.
		//
		$worksheet = $this->mParser->getUnitWorksheet();
		$records
			= $this->mCollections
				[ $this->getCollectionName( $worksheet ) ]
					->matchAll( Array(), kQUERY_COUNT );
		
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
		// Set records count.
		//
		$transaction->offsetSet( kTAG_COUNTER_RECORDS, $records );
		
		//
		// Check worksheet relationships.
		//
		if( ! $this->createObjects( $worksheet, $records ) )
			return FALSE;															// ==>

		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		if( $transaction->offsetGet( kTAG_TRANSACTION_STATUS )
				== kTYPE_STATUS_EXECUTING )
			$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
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
		if( $file->getType() != 'file' )
		{
			//
			// Remove reference to prevent errors when deleting.
			//
			$this->mFile = NULL;
			
			//
			// Set transaction.
			//
			$message = 'The file is either a directory or is invalid [$path].';
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
			
			return $this->failTransaction( kTYPE_STATUS_FATAL );					// ==>
		
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
			$message = 'The file cannot be read [$path].';
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
			
			return $this->failTransaction( kTYPE_STATUS_FATAL );					// ==>
		
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
		
				return $this->failTransaction( kTYPE_STATUS_FATAL );				// ==>
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
			return $this->failTransaction();										// ==>
	
		//
		// Close transaction.
		//
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
		$name = $file->getFilename();
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
		// Init local storage.
		//
		$ok = TRUE;
		$worksheets = array_keys( $this->mParser->getWorksheets() );
		$increment = 100 / count( $worksheets );
		
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
			if( ! $this->mParser->checkRequiredColumns( $this->transaction(), $worksheet ) )
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
			$this->transaction()->progress( $increment );
		
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
	 * @param int					$theRecords			Session records total.
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
						+= $this->checkCellValue(
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

	 
	/*===================================================================================
	 *	checkWorksheetRelationships														*
	 *==================================================================================*/

	/**
	 * Load worksheet data
	 *
	 * This method will scan all related worksheet records and flag with a warning any
	 * record that does not have a valid relationship.
	 *
	 * @param string				$theWorksheet		Worksheet name.
	 * @param array					$theFields			Field data.
	 * @param int					$theRecords			Records count.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses session()
	 */
	protected function checkWorksheetRelationships( $theWorksheet, $theFields, $theRecords )
	{
		//
		// Init local storage.
		//
		$collection = $this->mCollections[ $this->getCollectionName( $theWorksheet ) ];
		$worksheet_data = $this->mParser->getWorksheets()[ $theWorksheet ];
		$fields_data = $theFields[ $theWorksheet ];
		$transaction = NULL;
		
		//
		// Iterate worksheet data.
		//
		$rs = $collection->matchAll( Array(), kQUERY_ARRAY );
		foreach( $rs as $record )
		{
			//
			// Iterate related fields.
			//
			$matched = $rejected = 0;
			foreach( $fields_data as $field => $data )
			{
				//
				// Init local storage.
				//
				$tag = $this->mParser->getNode( $data[ 'node' ] )->offsetGet( kTAG_TAG );
				
				//
				// Check if field is there.
				//
				if( array_key_exists( $field, $record ) )
				{
					//
					// Init local storage.
					//
					$matched++;
					$collection_rel
						= $this->mCollections
							[ $this->getCollectionName( $data[ 'worksheet' ] ) ];
			
					//
					// Check if field is related.
					//
					$criteria = array( $data[ 'field' ] => $record[ $field ] );
					if( ! $collection_rel->matchOne( $criteria, kQUERY_COUNT ) )
					{
						//
						// Create transaction.
						//
						$this->failTransactionLog(
							$transaction,							// Transaction.
							$this->transaction(),					// Parent transaction.
							kTYPE_TRANS_TMPL_RELATIONSHIPS_ROW,		// Transaction type.
							kTYPE_STATUS_WARNING,					// Transaction status.
							'Related record not found.',			// Transaction message.
							$theWorksheet,							// Worksheet.
							$record[ kTAG_NID ],					// Row.
							$data[ 'column_name' ],					// Column.
							$field,									// Alias.
							$tag,									// Tag.
							$record[ $field ],						// Value.
							kTYPE_ERROR_RELATED_NO_MATCH,			// Error type.
							kTYPE_ERROR_CODE_BAD_RELATIONSHIP,		// Error code.
							NULL );									// Error resource.
						
						//
						// Add rejected.
						//
						$rejected++;
					
					} // Not matched.
				
				} // Has field.
			
			} // Iterating related fields.
			
			//
			// Update parent progress.
			//
			$this->transaction()->processed( 1, $theRecords );
			
			//
			// Handle skiped.
			//
			if( ! $matched )
				$this->transaction()->skipped( 1 );
			
			//
			// Handle rejected.
			//
			elseif( $rejected )
			{
				//
				// Increment rejected.
				//
				$this->transaction()->rejected( 1 );
				
				//
				// Delete entry.
				//
				$collection->delete( $record[ kTAG_NID ] );
			}
			
			//
			// Handle validated.
			//
			else
				$this->transaction()->validated( 1 );
			
			//
			// Close current transaction.
			//
			if( $transaction !== NULL )
			{
				$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
				$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
			}
		
		} // Iterating worksheet data.
		
		return TRUE;																// ==>

	} // checkWorksheetRelationships.

	 
	/*===================================================================================
	 *	createObjects																	*
	 *==================================================================================*/

	/**
	 * Create objects from worksheets
	 *
	 * This method will create objects from the worksheet data.
	 *
	 * @param string				$theUnitWorksheet	Unit worksheet name.
	 * @param int					$theRecords			Unit worksheet records total.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses file()
	 * @uses session()
	 */
	protected function createObjects( $theUnitWorksheet, $theRecords )
	{
		//
		// Init local storage.
		//
		$fields = $this->mParser->getFields();
		$worksheets = $this->mParser->getWorksheets();
		$class = $this->mParser->getRoot()->offsetGet( kTAG_CLASS_NAME );
		$collection_units
			= $this->mCollections
				[ $this->getCollectionName( UnitObject::kSEQ_NAME ) ];
		
		//
		// Remove unit worksheet.
		//
		$worksheets_list = array_keys( $worksheets );
		unset( $worksheets_list[ array_search( $theUnitWorksheet, $worksheets_list ) ] );
		
		//
		// Iterate unit worksheet records.
		//
		$rs
			= $this->mCollections
				[ $this->getCollectionName( $theUnitWorksheet ) ]
					->matchAll( Array(), kQUERY_ARRAY );
		foreach( $rs as $unit_record )
		{
			//
			// Set progress.
			//
			$this->transaction()->processed( 1, $theRecords );
		
		} // Iterating unit worksheet records.
		
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
	 *	checkCellValue																	*
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
	 * @return int					Number of errors.
	 *
	 * @uses validateString()
	 * @uses validateInteger()
	 * @uses validateFloat()
	 * @uses validateBoolean()
	 * @uses validateStruct()
	 * @uses validateArray()
	 * @uses validateArray()
	 */
	protected function checkCellValue( &$theTransaction,
									   &$theRecord,
										$theWorksheet,
										$theRow,
										$theSymbol )
	{
		//
		// Init local storage.
		//
		$errors = 0;
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
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_INT:
				case kTYPE_YEAR:
					$errors +=
						$this->validateInteger(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_FLOAT:
					$errors +=
						$this->validateFloat(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_BOOLEAN:
					$this->validateBoolean(
						$theTransaction, $theRecord,
						$theWorksheet, $theRow,
						$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_STRUCT:
					$errors +=
						$this->validateStruct(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_ARRAY:
					$errors +=
						$this->validateArray(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_LANGUAGE_STRING:
					$errors +=
						$this->validateLanguageString(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_LANGUAGE_STRINGS:
					$errors +=
						$this->validateLanguageStrings(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_TYPED_LIST:
					$errors +=
						$this->validateTypedList(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_SHAPE:
					$errors +=
						$this->validateShape(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_URL:
					$errors +=
						$this->validateLink(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_DATE:
					$errors +=
						$this->validateDate(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_ENUM:
					$errors +=
						$this->validateEnum(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_SET:
					$errors +=
						$this->validateEnumSet(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_TAG:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Tag::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_TERM:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Term::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_NODE:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Node::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_EDGE:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Edge::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_UNIT:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, UnitObject::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_USER:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, User::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_SESSION:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Session::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_TRANSACTION:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, Transaction::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_FILE:
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, FileObject::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_REF_SELF:
					$class = $this->mParser->getRoot()->offsetGet( kTAG_CLASS_NAME );
					$errors +=
						$this->validateReference(
							$theTransaction, $theRecord, $class::kSEQ_NAME,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_OBJECT_ID:
					$errors +=
						$this->validateObjectId(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
				case kTYPE_TIME_STAMP:
					$errors +=
						$this->validateTimeStamp(
							$theTransaction, $theRecord,
							$theWorksheet, $theRow,
							$field_data[ $theSymbol ], $field_node, $field_tag );
					break;
			
			} // Parsing by type.
		
		} // Tag field.
		
		return $errors;																// ==>

	} // checkCellValue.

	

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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
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
			= SetlocalTransformations(
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
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
				$element = SetlocalTransformations( $element, $prefix, $suffix );
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
				= SetlocalTransformations(
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
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
				$element = SetlocalTransformations( $element, $prefix, $suffix );
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
				= SetlocalTransformations(
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
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
				$element = SetlocalTransformations( $element, $prefix, $suffix );
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
				= SetlocalTransformations(
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
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
						= array( SetlocalTransformations( $element, $prefix, $suffix ) );
				
				//
				// Liat and element separator.
				//
				elseif( CheckArrayValue( $element,substr( $tokens, 1 ) ) )
					$theRecord[ $symbol ][]
						= SetlocalTransformations( $element, $prefix, $suffix );
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
					= SetlocalTransformations( $theRecord[ $symbol ], $prefix, $suffix );
		
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
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
						SetlocalTransformations( $list, $prefix, $suffix ) );
				
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
									SetlocalTransformations( $element, $prefix, $suffix ) );
							
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
									SetlocalTransformations( $text, $prefix, $suffix ) );
							
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
				if( $count == 1 )
					SetLanguageString(
						$theRecord[ $symbol ],
						NULL,
						SetlocalTransformations( $element, $prefix, $suffix ) );
				
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
							SetlocalTransformations( $text, $prefix, $suffix ) );
					
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
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
						SetlocalTransformations( array( $list ), $prefix, $suffix ) );
				
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
									SetlocalTransformations(
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
										SetlocalTransformations(
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
											SetlocalTransformations(
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
												SetlocalTransformations(
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
				if( $count == 1 )
					SetLanguageStrings(
						$theRecord[ $symbol ],
						NULL,
						SetlocalTransformations( array( $element ), $prefix, $suffix ) );
				
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
								SetlocalTransformations(
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
									SetlocalTransformations( $text, $prefix, $suffix ) );
						
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
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
						SetlocalTransformations(
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
									SetlocalTransformations(
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
										SetlocalTransformations(
											$element[ 0 ], $prefix, $suffix ) );
								
								//
								// Has type.
								//
								if( count( $element ) == 2 )
									SetTypedList(
										$list_reference,
										kTAG_TEXT,
										$element[ 0 ],
										SetlocalTransformations(
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
										SetlocalTransformations(
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
				unset( $theRecord[ $symbol ] );
			
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
						SetlocalTransformations(
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
								SetlocalTransformations(
									$element[ 0 ], $prefix, $suffix ) );
						
						//
						// Has type.
						//
						else
							SetTypedList(
								$theRecord[ $symbol ],
								kTAG_TEXT,
								$element[ 0 ],
								SetlocalTransformations(
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
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
			if( count( $tokens ) > 1 )
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
				// Check link.
				//
				$element = SetlocalTransformations( $element, $prefix, $suffix );
				$ok = CheckLinkValue( $element, $error_type, $error_message );
				
				//
				// Handle valid.
				//
				if( $ok === TRUE )
					$result[] = $element;
				
				//
				// Handle error.
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
			// Check link.
			//
			$theRecord[ $symbol ]
				= SetlocalTransformations( $theRecord[ $symbol ], $prefix, $suffix );
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
		
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
				$element = SetlocalTransformations( $element, $prefix, $suffix );
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
				= SetlocalTransformations( $theRecord[ $symbol ], $prefix, $suffix );
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
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
			if( ! count( $tokens ) )
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
			if( count( $tokens ) > 1 )
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
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
			if( ! count( $errors ) )
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
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
			if( ! count( $tokens ) )
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
			if( count( $tokens ) > 1 )
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
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
			if( ! count( $tokens ) )
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
			if( count( $tokens ) > 1 )
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
								SetlocalTransformations( $element, $prefix, $suffix ) );
					
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
							SetlocalTransformations(
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
		GetlocalTransformations( $theFieldNode, $collection, $prefix, $suffix );
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
			if( count( $tokens ) > 1 )
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
								SetlocalTransformations( $element, $prefix, $suffix ) );
					
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
							SetlocalTransformations(
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

	

/*=======================================================================================
 *																						*
 *								PUBLIC DEBUG UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	test																			*
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
		$record = array( 'SYMBOL' => 'Polygon=12.8199,42.8422;12.8207,42.8158;12.8699,42.8166;12.8678,42.8398;12.8199,42.8422:12.8344,42.8347;12.8348,42.8225;12.857,42.8223;12.8566,42.8332;12.8344,42.8347' );
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
		$node[ kTAG_PREFIX ] = NULL;
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = array( 'iso:3166:1:alpha-3:', 'iso:3166:3:alpha-3:', 'iso:3166:2:' );
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = array( 'iso:3166:1:alpha-3:', 'iso:3166:3:alpha-3:', 'iso:3166:2:' );
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = NULL;
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = array( ':taxon' );
		$node[ kTAG_SUFFIX ] = array( ':category' );
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
		$node[ kTAG_PREFIX ] = array( ':taxon:crop' );
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = NULL;
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = array( ':taxon' );
		$node[ kTAG_SUFFIX ] = array( ':category' );
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
		$node[ kTAG_PREFIX ] = array( ':taxon:crop' );
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = NULL;
		$node[ kTAG_SUFFIX ] = NULL;
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
		$record = array( 'SYMBOL' => '1/:predicate:PROPERTY-OF/364' );
		$node[ kTAG_PREFIX ] = NULL;
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = array( '1/' );
		$node[ kTAG_SUFFIX ] = array( '/364' );
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
		$record = array( 'SYMBOL' => ':predicate:ENUM-OF/381, :predicate:SUBSET-OF/10591' );
		$node[ kTAG_PREFIX ] = array( '10007/' );
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = NULL;
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = array( ':domain:inventory' );
		$node[ kTAG_SUFFIX ] = array( 'Aegilops triuncialis:CYP;' );
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
		$node[ kTAG_PREFIX ] = array( ':domain:inventory://CYP/' );
		$node[ kTAG_SUFFIX ] = array( ':CYP;' );
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
		$node[ kTAG_PREFIX ] = NULL;
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = array( ':domain:individual://ITA406/pgrdiversity.bioversityinternational.org:' );
		$node[ kTAG_SUFFIX ] = array( ';' );
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
		$node[ kTAG_PREFIX ] = NULL;
		$node[ kTAG_SUFFIX ] = NULL;
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
		$node[ kTAG_PREFIX ] = NULL;
		$node[ kTAG_SUFFIX ] = NULL;
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
