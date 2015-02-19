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
 * requesting user and the upload template file path, the class implements the workflow
 * needed to execute a data template upload.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. The native identifier of a
 *		session is automatically generated when inserted the first time, it can then be
 *		used to reference the object and have {@link Transaction} objects update its
 *		members.
 *	<li><tt>{@link kTAG_SESSION_TYPE}</tt>: <em>Session type</em>. This required enumerated
 *		value indicates the type, function or scope of the session.
 *	<li><tt>{@link kTAG_SESSION_START}</tt>: <em>Session start</em>. The starting time stamp
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
	 * Data wrapper.
	 *
	 * This data member holds the <i>data wrapper</i>.
	 *
	 * @var Wrapper
	 */
	protected $mWrapper = NULL;

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
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param User					$theUser			Requesting user reference or object.
	 * @param string				$theFile			Template file path.
	 *
	 * @access public
	 *
	 * @uses wrapper()
	 * @uses session()
	 */
	public function __construct( Wrapper $theWrapper, $theUser, $theFile )
	{
		//
		// Set wrapper.
		//
		$this->wrapper( $theWrapper );
		
		//
		// Instantiate session.
		//
		$this->session( new Session( $this->wrapper() ) );
		$this->session()->type( kTYPE_SESSION_UPLOAD );
		$this->session()->user( $theUser );
		$this->session()->commit();
		
		//
		// Set metadata.
		//
		$metadata
			= array( kTAG_SESSION_TYPE
				  => $this->session()->offsetGet( kTAG_SESSION_TYPE ) );
		
		//
		// Set file.
		//
		$this->session()->saveFile( $theFile, $metadata );

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
	 * Manage wrapper
	 *
	 * This method can be used to set or retrieve the <i>data wrapper</i>, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theValue</tt>: The property value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current property value.
	 *		<li><tt>Wrapper</tt>: Set the value in the property.
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
	 * @param mixed					$theValue			New wrapper or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				Current or old wrapper.
	 *
	 * @throws Exception
	 *
	 * @uses manageProperty()
	 */
	public function wrapper( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Check new value.
		//
		if( ($theValue !== NULL)
		 && (! ($theValue instanceof Wrapper)) )
			throw new \Exception(
				"Cannot set session wrapper: "
			   ."invalid data type." );											// !@! ==>
		
		return $this->manageProperty( $this->mWrapper, $theValue, $getOld );		// ==>
	
	} // wrapper.

	 
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
	 *	<li><tt>{@link kTAG_SKIPPED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_REJECTED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_VALIDATED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_PROCESSED}</tt>: Processed elements.
	 * </ul>
	 *
	 * @access public
	 * @return array				Operation counters.
	 *
	 * @uses session()
	 */
	public function counters()					{	return $this->session()->counters();	}

	 

} // class SessionUpload.


?>
