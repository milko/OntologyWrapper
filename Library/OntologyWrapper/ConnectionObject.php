<?php

/**
 * ConnectionObject.php
 *
 * This file contains the definition of the {@link ConnectionObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\OntologyObject;

/*=======================================================================================
 *																						*
 *								ConnectionObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Connection object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing connection
 * instances, such as servers, databases and collections.
 *
 * The main purpose of this class is to wrap a common interface around concrete instances of
 * specific database or cache engines.
 *
 * The class features two properties:
 *
 * <ul>
 *	<li><tt>{@link $mDSN}</tt>: The <i>data source name</i>.
 *	<li><tt>{@link $mConnection}</tt>: The <i>connection resource</i>.
 * </ul>
 *
 * The class features a virtual method which must be implemented by derived concrete
 * instances which uses the data source name to create the connection resource.
 *
 * The connection parameters are stored in the array part of the object, which means that
 * these parameters must be defined as {@link Tag} objects.
 *
 * A series of virtual methods take care of creating the connection and parsing the
 * parameters:
 *
 * <ul>
 *	<li><tt>{@link createConnection()}</tt>: This method will create or open the connection.
 *	<li><tt>{@link closeConnection()}</tt>: This method will close the connection if open.
 *	<li><tt>{@link parseConnection()}</tt>: This method will parse the provided data source
 *		name and return the list of parameters.
 *	<li><tt>{@link parseParameters()}</tt>: This method will parse the parameters and return
 *		the data source name.
 * </ul>
 *
 * When the object goes out of context, there are a series of methods that will take care of
 * closing and reopening connections:
 *
 * <ul>
 *	<li><tt>{@link __sleep()}</tt>: This method will close the connection, if open, and
 *		set the {@link $mConnection connection} property to <tt>TRUE</tt> as an indication
 *		that the connection must be opened once the object gets back into scope.
 *	<li><tt>{@link __wakeup()}</tt>: This method will open the connection, if the object
 *		went out of scope while the connection was open.
 * </ul>
 *
 * The class provides accessor methods for the two object properties, {@link manageDSN()}
 * and {@link manageConnection()}: derived classes should overload these methods for
 * validation purposes.
 *
 * This object represents the building block for all concrete instances that represent
 * servers, databases, data collections and caches.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/01/2014
 */
abstract class ConnectionObject extends OntologyObject
{
	/**
	 * Data source name.
	 *
	 * This data member holds the <i>data source name</i>, or <tt>DSN</tt>. It is a string
	 * holding all the connection parameters that is used to instantiate the actual
	 * connection.
	 *
	 * @var string
	 */
	protected $mDSN = NULL;

	/**
	 * Connection resource.
	 *
	 * This data member holds the <i>connection resource</i>, or <i>native connection</i>.
	 * This property represents the actual connection resource.
	 *
	 * @var mixed
	 */
	protected $mConnection = NULL;

		

/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__sleep																			*
	 *==================================================================================*/

	/**
	 * <h4>Sleep</h4>
	 *
	 * This method will close the connection and replace the connection resource with
	 * <tt>TRUE</tt> if the connection was open.
	 *
	 * @access public
	 *
	 * @uses closeConnection()
	 */
	public function __sleep()
	{
		//
		// Check current connection.
		//
		if( $this->mConnection !== NULL )
		{
			//
			// Close connection.
			//
			$this->closeConnection();
			
			//
			// Mark as open.
			//
			$this->mConnection = TRUE;
		
		} // Open connection.
		
	} // __sleep.

	 
	/*===================================================================================
	 *	__wakeup																		*
	 *==================================================================================*/

	/**
	 * <h4>Wake up</h4>
	 *
	 * This method will re-open the connection if it was closed by the {@link __sleep()}
	 * method.
	 *
	 * @access public
	 *
	 * @uses openConnection()
	 */
	public function __wakeup()
	{
		//
		// Open closed connection.
		//
		if( $this->mConnection !== NULL )
			$this->openConnection();
		
	} // __wakeup.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	manageDSN																		*
	 *==================================================================================*/

	/**
	 * Manage data source name
	 *
	 * This method can be used to manage the data source name, it accepts a parameter which
	 * represents either the data source name or the requested operation, depending on its
	 * value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>FALSE</tt>: Delete the current value.
	 *	<li><i>other</i>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * The second parameter is a boolean which if <tt>TRUE</tt> will return the <i>old</i>
	 * value when replacing or resetting; if <tt>FALSE</tt>, it will return the current
	 * value.
	 *
	 * @param mixed					$theValue			Data source name or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				<i>New</i> or <i>old</i> data source name.
	 *
	 * @see $mDSN
	 * @uses manageProperty()
	 */
	public function manageDSN( $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageProperty( $this->mDSN, (string) $theValue, $getOld );	// ==>
	
	} // manageDSN.

	 
	/*===================================================================================
	 *	manageConnection																*
	 *==================================================================================*/

	/**
	 * Manage connection.
	 *
	 * This method can be used to manage the connection resource, it accepts a parameter
	 * which represents either the connection or the requested operation, depending on its
	 * value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>FALSE</tt>: Delete the current value.
	 *	<li><i>other</i>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * The second parameter is a boolean which if <tt>TRUE</tt> will return the <i>old</i>
	 * value when replacing or resetting; if <tt>FALSE</tt>, it will return the current
	 * value.
	 *
	 * @param mixed					$theValue			Connection or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				<i>New</i> or <i>old</i> connection.
	 *
	 * @see $mConnection
	 * @uses manageProperty()
	 */
	public function manageConnection( $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageProperty( $this->mConnection, $theValue, $getOld );		// ==>
	
	} // manageConnection.

	 

} // class ConnectionObject.


?>
