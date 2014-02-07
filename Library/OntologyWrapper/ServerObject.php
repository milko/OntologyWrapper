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
 * This <i>abstract</i> class is the ancestor of all classes representing server connection
 * instances, this class extends the {@link ConnectionObject} class to implement server
 * specific functionality prototypes.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
abstract class ServerObject extends ConnectionObject
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Database																		*
	 *==================================================================================*/

	/**
	 * Return database connection
	 *
	 * This method can be used to return a database connection from the current server.
	 *
	 * The method expects a single parameter which represents the database name, the method
	 * should return an instance of a class derived from {@link DatabaseObject}.
	 *
	 * @param string				$theName			Database name.
	 *
	 * @access public
	 * @return DatabaseObject		Database object.
	 *
	 * @uses newDatabase()
	 */
	public function Database( $theName )
	{
		//
		// Get current server parameters.
		//
		$params = $this->getArrayCopy();
		
		//
		// Add database name.
		//
		$params[ kTAG_CONN_BASE ] = $theName;
		
		return $this->newDatabase( $params );										// ==>
	
	} // Database.

	 
	/*===================================================================================
	 *	getStatistics																	*
	 *==================================================================================*/

	/**
	 * Return statistics
	 *
	 * This method should return the server statistics, the result depends on the specific
	 * driver.
	 *
	 * The method should return the following retults:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: The operation is not supported.
	 *	<li><tt>FALSE</tt>: The server is not connected.
	 *	<li><tt>array</tt>: The server statistics.
	 * </ul>
	 *
	 * We implement the method in this class as a fall-back.
	 *
	 * @access public
	 * @return array				Server statistics, <tt>NULL</tt> or <tt>FALSE</tt>.
	 */
	public function getStatistics()										{	return NULL;	}

		

/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newDatabase																		*
	 *==================================================================================*/

	/**
	 * Return a new database instance
	 *
	 * This method should implemented by concrete derived classes, it expects a list of
	 * offsets which include server information and should use them to instantiate a
	 * {@link DatabaseObject} instance.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param array					$theOffsets			Full database offsets.
	 *
	 * @access protected
	 * @return DatabaseObject		Database instance.
	 */
	abstract protected function newDatabase( $theOffsets );

	 

} // class ServerObject.


?>
