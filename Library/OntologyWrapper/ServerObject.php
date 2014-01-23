<?php

/**
 * ServerObject.php
 *
 * This file contains the definition of the {@link ServerObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;

/*=======================================================================================
 *																						*
 *									ServerObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Server object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing server instances,
 * the class features a method, {@link Database()}, which returns a {@link DatabaseObject}
 * instance connected to the current server.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 21/01/2014
 */
abstract class ServerObject extends ConnectionObject
{
		

/*=======================================================================================
 *																						*
 *								PUBLIC CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Database																		*
	 *==================================================================================*/

	/**
	 * Return database connection
	 *
	 * This method can be used to create a database connection, the method expects a
	 * parameter which may either be a data source name or an array of parameters.
	 *
	 * If a data source name is provided, it should be in the following format:
	 *
	 * <code>user:pass@name?key=value&...&key=value</code>
	 *
	 * in which the 
	 * where <code>user</code> is the user code, <code>pass</code> is the user password,
	 * <code>name</code> is the database name and the <code>key=value</code> pairs
	 * represent the database parameters.
	 *
	 * The parameter may also be provided as an array of key/values in which the following
	 * elements are supported:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_NAME}</tt>: The database name.
	 *	<li><tt>{@link kTAG_CONN_USER}</tt>: The <code>user</code> code.
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: The user <code>pass</code>word.
	 * </ul>
	 *
	 * The actual database instantiation is performed by a protected method,
	 * {@link getDatabase()} which must be implemented by concrete derived classes.
	 *
	 * Derived classes may define other parameters.
	 *
	 * @param mixed					$theName			Database name and parameters.
	 *
	 * @access public
	 * @return DatabaseObject		Database object.
	 */
	public function Database( $theDSN )
	{
		//
		// Normalise data source name.
		//
		if( ! is_array( $theDSN ) )
			$theDSN = (string) $theDSN;
		
		return $this->getDatabase( $theDSN );										// ==>
	
	} // Database.

		

/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	connectionOpen																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * This method should open the actual connection, the method is virtual and must be
	 * implemented by concrete derived instances.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @access protected
	 * @return mixed				The native connection.
	 */
	abstract protected function connectionOpen();

	 
	/*===================================================================================
	 *	connectionClose																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * This method should close the actual connection, the method is virtual and must be
	 * implemented by concrete derived instances.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @param mixed					$theConnection		Connection.
	 *
	 * @access protected
	 */
	abstract protected function connectionClose();

	 

} // class ServerObject.


?>
