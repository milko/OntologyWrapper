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
	 * This data member holds the <i>template</i> reference, it will be an SplFileInfo object,
	 * once the file will be saved, it will be a file object.
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
	 * This class is instantiated by providing a data wrapper, a requesting user and the
	 * path to the template file.
	 *
	 * @param Session				$theSession			Related session object.
	 * @param string				$theFile			Template file path.
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
	 * Note that the data member will hold a file object once the file will have been saved,
	 * this can only be done using offsets, so, the method will return a mixed result.
	 *
	 * Replacing or clearing the value is only allowed if the current data member is
	 * <tt>NULL</tt> or if it holds an SplFileInfo, if that is not the case, the method will
	 * raise an exception.
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
		if( $theValue !== NULL )
		{
			//
			// Save current value.
			//
			if( ($this->mTemplate !== NULL)
			 && (! ($this->mTemplate instanceof \SplFileInfo)) )
				throw new \Exception(
					"Cannot set template: "
				   ."the file was already saved." );							// !@! ==>
			
			//
			// Handle path.
			//
			if( ! ($theValue instanceof \SplFileInfo) )
				$theValue = new \SplFileInfo( (string) $theValue );
			
			//
			// Check if readable.
			//
			if( ! $theFile->isReadable() )
				throw new \Exception(
					"Cannot set template: "
				   ."the file is not readable." );								// !@! ==>
		
		} // New template or delete.
		
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
	 * @return mixed				Current or old session.
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
 *							PUBLIC SESSION DATA ACCESSOR INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getID																			*
	 *==================================================================================*/

	/**
	 * Get session ID
	 *
	 * This method can be used to retrieve the session ID.
	 *
	 * @access public
	 * @return string				Session name.
	 *
	 * @uses session()
	 */
	public function getID()	{	return (string) $this->session()->offsetGet( kTAG_NID );	}

	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Get session name
	 *
	 * This method can be used to retrieve the session name.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Session name.
	 *
	 * @uses session()
	 */
	public function getName( $theLanguage = kSTANDARDS_LANGUAGE )
	{
		return $this->session()->getName( $theLanguage );							// ==>
	
	} // getName.

	 
	/*===================================================================================
	 *	getType																			*
	 *==================================================================================*/

	/**
	 * Get session type
	 *
	 * This method can be used to retrieve the session type.
	 *
	 * @access public
	 * @return string				Session type.
	 *
	 * @uses session()
	 */
	public function getType()						{	return $this->session()->type();	}

	 
	/*===================================================================================
	 *	getStart																		*
	 *==================================================================================*/

	/**
	 * Get session start
	 *
	 * This method can be used to retrieve the session start.
	 *
	 * @access public
	 * @return MongoDate			Session start.
	 *
	 * @uses session()
	 */
	public function getStart()						{	return $this->session()->start();	}

	 
	/*===================================================================================
	 *	getEnd																			*
	 *==================================================================================*/

	/**
	 * Get session end
	 *
	 * This method can be used to retrieve the session end.
	 *
	 * @access public
	 * @return MongoDate			Session end.
	 *
	 * @uses session()
	 */
	public function getEnd()							{	return $this->session()->end();	}

	 
	/*===================================================================================
	 *	getStatus																		*
	 *==================================================================================*/

	/**
	 * Get session end
	 *
	 * This method can be used to retrieve the session status.
	 *
	 * @access public
	 * @return string				Session status.
	 *
	 * @uses session()
	 */
	public function getStatus()						{	return $this->session()->status();	}

	 
	/*===================================================================================
	 *	getUser																			*
	 *==================================================================================*/

	/**
	 * Get session user
	 *
	 * This method can be used to retrieve the session user identifier.
	 *
	 * @access public
	 * @return string				Session user.
	 *
	 * @uses session()
	 */
	public function getUser()						{	return $this->session()->user();	}

	 
	/*===================================================================================
	 *	getSession																		*
	 *==================================================================================*/

	/**
	 * Get referencing session
	 *
	 * This method can be used to retrieve the referencing session identifier.
	 *
	 * @access public
	 * @return ObjectId				Referencing session identifier.
	 *
	 * @uses session()
	 */
	public function getSession()					{	return $this->session()->session();	}

	

/*=======================================================================================
 *																						*
 *						PUBLIC SESSION COUNTERS ACCESSOR INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	counters																		*
	 *==================================================================================*/

	/**
	 * Manage operation counters
	 *
	 * This method can be used to retrieve the current operations counters, the method will
	 * return an array with the following keys:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_COUNTER_SKIPPED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_COUNTER_REJECTED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_COUNTER_VALIDATED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_COUNTER_PROCESSED}</tt>: Processed elements.
	 * </ul>
	 *
	 * @access public
	 * @return array				Operation counters.
	 *
	 * @uses session()
	 */
	public function counters()					{	return $this->session()->counters();	}

	

/*=======================================================================================
 *																						*
 *								PROTECTED OPERATIONS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
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
	 * @return ObjecId				The file object identifier.
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
		
		$this->session()->saveFile( $path, $metadata );								// ==>

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
		$this->session()->

	} // initCollections.

	 

} // class SessionUpload.


?>
