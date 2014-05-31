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
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access public
	 * @return DatabaseObject		Database object.
	 *
	 * @uses newDatabase()
	 */
	public function Database( $theName, $doOpen = TRUE )
	{
		//
		// Get current server parameters.
		//
		$params = $this->getArrayCopy();
		
		//
		// Add database name.
		//
		$params[ kTAG_CONN_BASE ] = $theName;
		
		//
		// Instantiate database.
		//
		$database = $this->newDatabase( $params, $doOpen );
		
		//
		// Set data dictionary.
		//
		$database->dictionary( $this->dictionary() );
		
		return $database;															// ==>
	
	
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
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return {@link kTAG_CONN_PROTOCOL}, {@link kTAG_CONN_HOST},
	 * {@link kTAG_CONN_PORT}, {@link kTAG_CONN_USER}, {@link kTAG_CONN_PASS} and
	 * {@link kTAG_CONN_OPTS}.
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_CONN_PROTOCOL,
								   kTAG_CONN_HOST, kTAG_CONN_PORT,
								   kTAG_CONN_USER, kTAG_CONN_PASS,
								   kTAG_CONN_OPTS ) );								// ==>
	
	} // DefaultOffsets;

		

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
	 * <em>When implementing this method you should not forget to set the dictionary</em>.
	 *
	 * @param array					$theOffsets			Full database offsets.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access protected
	 * @return DatabaseObject		Database instance.
	 */
	abstract protected function newDatabase( $theOffsets, $doOpen = TRUE );

	 

} // class ServerObject.


?>
