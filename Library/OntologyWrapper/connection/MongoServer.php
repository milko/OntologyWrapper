<?php

/**
 * MongoServer.php
 *
 * This file contains the definition of the {@link MongoServer} class.
 */

namespace OntologyWrapper\connection;

use OntologyWrapper\ServerObject;

/*=======================================================================================
 *																						*
 *									MongoServer.php										*
 *																						*
 *======================================================================================*/

/**
 * Mongo server
 *
 * This class is a <i>concrete</i> implementation of the {@link ServerObject} wrapping a
 * {@link MongoClient} class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
class MongoServer extends ServerObject
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isConnected																		*
	 *==================================================================================*/

	/**
	 * Check if connection is open
	 *
	 * We overload this method to assume the object is connected if the resource is a
	 * {@link MongoClient}.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		return ( $this->mConnection instanceof \MongoClient );						// ==>
	
	} // isConnected.

	 
	/*===================================================================================
	 *	getStatistics																	*
	 *==================================================================================*/

	/**
	 * Return statistics
	 *
	 * This method will call the {@link MongoClient::getConnections()} method.
	 *
	 * @access public
	 * @return mixed				Depends on driver.
	 */
	public function getStatistics()		{	return $this->mConnection->getConnections();	}

		

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
	 * This method will instantiate a {@link MongoClient} object and set it in the
	 * mConnection data member.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * If the operation fails, the method will raise an exception.
	 *
	 * @access protected
	 * @return mixed				The native connection.
	 */
	protected function connectionOpen()
	{
		//
		// Load parameters.
		//
		$params = $this->getArrayCopy();
		if( array_key_exists( kTAG_CONN_OPTS, $params ) )
		{
			$options = $params[ kTAG_CONN_OPTS ];
			unset( $params[ kTAG_CONN_OPTS ] );
		}
		else
			$options = NULL;
		
		//
		// Build data source name.
		//
		$dsn = $this->parseOffsets( $params );
		
		//
		// Set client.
		//
		$this->mConnection = ( $options !== NULL )
						   ? new \MongoClient( $dsn, $options )
						   : new \MongoClient( $dsn );
		
		return $this->mConnection;													// ==>
	
	} // connectionOpen.

	 
	/*===================================================================================
	 *	connectionClose																	*
	 *==================================================================================*/

	/**
	 * Close connection
	 *
	 * We overload this method to reset the connection resource.
	 *
	 * @access protected
	 */
	protected function connectionClose()					{	$this->mConnection = NULL;	}

		

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
	protected function newDatabase( $theOffsets )
	{
		return new MongoDatabase( $theOffsets );									// ==>
	
	} // newDatabase;

	 

} // class MongoServer.


?>
