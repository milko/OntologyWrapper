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
	 * Template.
	 *
	 * This data member holds the <i>template</i> reference, it will be an SplFileInfo
	 * object.
	 *
	 * @var mixed
	 */
	protected $mTemplate = NULL;

	/**
	 * Transaction.
	 *
	 * This data member holds the <i>current transaction object</i>.
	 *
	 * @var Transaction
	 */
	protected $mTransaction = NULL;

	/**
	 * Collections.
	 *
	 * This data member holds the <i>list of working collections</i> as an array structured
	 * as follows:
	 *
	 * <ul>
	 *	<li><em>index</tt>: The collection name.
	 *	<li><em>value</tt>: The collection object.
	 * </ul>
	 *
	 * @var array
	 */
	protected $mCollections = NULL;

	/**
	 * Work file reference.
	 *
	 * This data member holds the object used to parse the Excel template file.
	 *
	 * @var mixed
	 */
	protected $mWorkFile = NULL;

		

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
	 * @uses wrapper()
	 * @uses session()
	 */
	public function __construct( Session $theSession, $theFile )
	{
		//
		// Set session.
		//
		$this->session( $theSession );
		
		//
		// Set file path.
		//
		$this->template( $theFile );

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
		// Delete template file.
		//
		$file = $this->template();
		if( $file instanceof \SplFileInfo )
			unlink( $file->getRealPath() );

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
	 *		<li><tt>string</tt>: Set the value with the object identified by the value.
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
	 * @param mixed					$theValue			New session or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				Current or old session.
	 *
	 * @uses manageProperty()
	 */
	public function session( $theValue = NULL, $getOld = FALSE )
	{
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
	 *	template																		*
	 *==================================================================================*/

	/**
	 * Manage template
	 *
	 * This method can be used to set or retrieve the <i>template</i>, the method expects
	 * the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theValue</tt>: The property value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current property value.
	 *		<li><tt>SplFileInfo</tt>: Set the value in the property.
	 *		<li><em>other</em>: The method will raise an exception.
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
	 * The first time this member is set, the value is supposed to reference the original
	 * template file; the next time the member is set, it will hold the reference to the
	 * template working copy.
	 *
	 * @param mixed					$theValue			New file reference or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				Current or old file.
	 *
	 * @uses manageProperty()
	 */
	public function template( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Check new value.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE) )
		{
			//
			// Handle path.
			//
			if( ! ($theValue instanceof \SplFileInfo) )
				$theValue = new \SplFileInfo( (string) $theValue );
			
			//
			// Check if readable.
			//
			if( ! $theValue->isReadable() )
				throw new \Exception(
					"Cannot set template: "
				   ."the file is not readable." );								// !@! ==>
		
		} // New template.
		
		return $this->manageProperty( $this->mTemplate, $theValue, $getOld );		// ==>
	
	} // template.

	 
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
	 *		<li><tt>Session</tt>: Set the value in the property.
	 *		<li><tt>string</tt>: Set the value with the object identified by the value.
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
	 * @param mixed					$theValue			New transaction or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				Current or old transaction.
	 *
	 * @uses manageProperty()
	 */
	public function transaction( $theValue = NULL, $getOld = FALSE )
	{
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
	 *
	 * @uses session()
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
			{
				$this->failSession();
			
				return FALSE;														// ==>
			}
			else
				$this->session()->progress( 10 );
	
			//
			// Transaction store.
			//
			if( ! $this->sessionStore() )
			{
				$this->failSession();
			
				return FALSE;														// ==>
			}
			else
				$this->session()->progress( 10 );
	
			//
			// Transaction load.
			//
			if( ! $this->sessionLoad() )
			{
				$this->failSession();
			
				return FALSE;														// ==>
			}
			else
				$this->session()->progress( 10 );
			
			//
			// Close session.
			//
			$this->succeedSession();

			return TRUE;															// ==>
		}
		
		//
		// CATCH BLOCK.
		//
		catch( Exception $error )
		{
			$this->exceptionSession( $error );
		}
		
		return FALSE;																// ==>
		
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
	 * This method will perform the initialisation transaction, clearing any pending upload
	 * sessions.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 */
	protected function sessionPrepare()
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
							   kTAG_SESSION_TYPE => kTYPE_SESSION_UPLOAD,
							   kTAG_SESSION => array( '$exists' => FALSE ) ),
						kQUERY_NID );
		
		//
		// Handle sessions list.
		//
		if( $count = $sessions->count() )
		{
			//
			// Create transaction.
			//
			$transaction
				= $this->transaction(
					$this->session()->newTransaction( kTYPE_TRANS_TMPL_PREPARE ) );
			$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
			
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
		
			//
			// Close transaction.
			//
			$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
			$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		}
		
		return TRUE;																// ==>

	} // sessionPrepare.

	 
	/*===================================================================================
	 *	sessionStore																	*
	 *==================================================================================*/

	/**
	 * Store template
	 *
	 * This method will store the current template.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
	 */
	protected function sessionStore()
	{
		//
		// Init local storage.
		//
		$collection
			= FileObject::ResolveCollection(
				FileObject::ResolveDatabase( $this->wrapper(), TRUE ), TRUE );
		
		//
		// Create transaction.
		//
		$transaction
			= $this->transaction(
				$this->session()->newTransaction( kTYPE_TRANS_TMPL_STORE ) );
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
		
		//
		// Save file reference.
		//
		$transaction->offsetSet( kTAG_FILE, $this->saveTemplate() );
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionStore.

	 
	/*===================================================================================
	 *	sessionLoad																		*
	 *==================================================================================*/

	/**
	 * Load template
	 *
	 * This method will parse, identify and load template data dictionary and template file
	 * elements.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 *
	 * @uses session()
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
		// Load template file.
		//
		$this->mWorkFile = \PHPExcel_IOFactory::load( $this->template()->getRealPath() );
		
		//
		// Check PID.
		//
		$properties = $this->mWorkFile->getProperties();
		if( in_array( 'PID', $properties->getCustomProperties() ) )
		{
			//
			// Get template PID.
			//
			$pid = $properties->getCustomPropertyValue( 'PID' );
var_dump( $pid );
		}
		else
			throw new \Exception(
				"Cannot load template: "
			   ."missing PID property." );										// !@! ==>
	
		//
		// Close transaction.
		//
		$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
		$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return TRUE;																// ==>

	} // sessionLoad.

	

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
	 *
	 * @uses session()
	 */
	public function succeedSession()
	{
		$this->session()->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$this->session()->offsetSet( kTAG_SESSION_END, TRUE );
		$this->session()->offsetSet( kTAG_SESSION_STATUS, kTYPE_STATUS_OK );

	} // succeedSession.

	 
	/*===================================================================================
	 *	failSession																		*
	 *==================================================================================*/

	/**
	 * Fail session
	 *
	 * This method can be used to set the session data in the case of a <em>intercepted</em>
	 * error, it will set the progress to 100, set the ending time and set the failed
	 * status.
	 *
	 * @access public
	 *
	 * @uses session()
	 */
	public function failSession()
	{
		$this->session()->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$this->session()->offsetSet( kTAG_SESSION_END, TRUE );
		$this->session()->offsetSet( kTAG_SESSION_STATUS, kTYPE_STATUS_FAILED );

	} // failSession.

	 
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
	 *
	 * @uses session()
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
		
		//
		// Set session info.
		//
		$this->session()->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
		$this->session()->offsetSet( kTAG_SESSION_END, TRUE );
		$this->session()->offsetSet( kTAG_SESSION_STATUS, kTYPE_STATUS_FAILED );

	} // exceptionSession.

	 
	/*===================================================================================
	 *	saveTemplate																	*
	 *==================================================================================*/

	/**
	 * Save template
	 *
	 * This method can be used to save the current object's template in the session's file
	 * store.
	 *
	 * Once saved, the template data member will hold the file object.
	 *
	 * @access public
	 * @return string				The file object identifier.
	 *
	 * @uses session()
	 */
	public function saveTemplate()
	{
		//
		// Get file path.
		//
		if( $this->mTemplate instanceof \SplFileInfo )
			$path = $this->mTemplate->getRealPath();
		
		//
		// Check if set.
		//
		elseif( $this->mTemplate === NULL )
			throw new \Exception(
				"Cannot save template: "
			   ."missing file reference." );									// !@! ==>
		
		//
		// Set metadata.
		//
		$metadata
			= array( kTAG_SESSION_TYPE
				  => $this->session()->offsetGet( kTAG_SESSION_TYPE ) );
		
		return $this->session()->saveFile( $path, $metadata );						// ==>

	} // saveTemplate.

	 
	/*===================================================================================
	 *	initCollections																	*
	 *==================================================================================*/

	/**
	 * Initialise working collections
	 *
	 * This method will instantiate all working collections, the method expects an array
	 * of collection names, the resulting collection names will be composed as follows:
	 * <user database kTAG_CONN_BASE>_<worksheet name>.
	 *
	 * The parameter is expected to be an array.
	 *
	 * @param array					$theNames			Collection names.
	 *
	 * @access public
	 *
	 * @uses session()
	 */
	public function initCollections( $theNames )
	{
		//
		// Drop eventual collections.
		//
		if( is_array( $this->mCollections ) )
		{
			foreach( $this->mCollections as $collection )
				$collection->drop();
		}
		
		//
		// Instantiate collections.
		//
		$this->mCollections = Array();
		foreach( $theNames as $collection )
			$this->mCollection[ $collection ]
				= Session::ResolveDatabase( $this->session()->dictionary(), TRUE, TRUE )
					->collection( $collection, TRUE );
		
		//
		// Save collections in session.
		//
		$this->session()->offsetSet( kTAG_COUNTER_COLLECTIONS, $this->mCollections );

	} // initCollections.

	 

} // class SessionUpload.


?>
