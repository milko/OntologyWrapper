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
 * Mongo server object
 *
 * This class wraps the {@link ServerObject} class around the {@link MongoClient} class,
 * it represents a concrete instance of {@link ServerObject} which implements a MongoDB
 * server.
 *
 * To instantiate an object with the data source name you can provide an URL such as this
 * one:
 *
 * <code>mongodb://user:pass@host:port?opt1=val1&opt2=val2</code>
 *
 * where:
 *
 * <ul>
 *	<li><code>mongodb</code>: This represents the protocol and will be stored in the
 *		{@link kTAG_CONN_PROTOCOL} offset.
 *	<li><code>user</code>: This represents the user code and will be stored in the
 *		{@link kTAG_CONN_USER} offset.
 *	<li><code>pass</code>: This represents the user password and will be stored in the
 *		{@link kTAG_CONN_PASS} offset.
 *	<li><code>host</code>: This represents the host and will be stored in the
 *		{@link kTAG_CONN_HOST} offset.
 *	<li><code>port</code>: This represents the port and will be stored in the
 *		{@link kTAG_CONN_PORT} offset.
 *	<li><code>opt1=val1&opt2=val2</code>: This represents the server options which will be
 *		parsed and stored in the {@link kTAG_CONN_OPTS} offset; the resulting array will be
 *		passed as the second parameter of the {@link MongoClient::__construct()} method.
 *		<i>Note that all option values are strings, so you should use <tt>1</tt> or
 *		<tt>0</tt> for boolean values</i>.
 * </ul>
 *
 * Or you can provide an array matching the above parameters.
 *
 * To instantiate a replica set which has several hosts you should instantiate the object
 * with a list of offsets in which the {@link kTAG_CONN_HOST} will be an array.
 *
 * For more information on the specifics of this particular cache engine, please consult the
 * {@link Memcached} documentation.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 20/01/2014
 */
class MongoServer extends ServerObject
{


/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__wakeup																		*
	 *==================================================================================*/

	/**
	 * Wake up
	 *
	 * We overload this method to create the connection resource.
	 *
	 * @access public
	 *
	 * @uses openConnection()
	 */
	public function __wakeup()
	{
		//
		// Build the connection string.
		//
		$str = 'mongodb://';
		if( $this->offsetExists( kTAG_CONN_USER ) )
		{
			$str .= $this->offsetGet( kTAG_CONN_USER );
			if( $this->offsetExists( kTAG_CONN_PASS ) )
			{
				$str .= ':';
				$str .= $this->offsetGet( kTAG_CONN_PASS );
			}
			$str .= '@';
		}
		$str .= $this->offsetGet( kTAG_CONN_HOST );
		if( $this->offsetExists( kTAG_CONN_PORT ) )
		{
			$str .= ':';
			$str .= $this->offsetGet( kTAG_CONN_PORT );
		}
		
		//
		// Instantiate the Memcached object.
		//
		$this->mConnection = ( $this->offsetExists( kTAG_CONN_OPTS ) )
						   ? new \MongoClient( $str, $this->offsetGet( kTAG_CONN_OPTS ) )
						   : new \MongoClient( $str );

		//
		// Call parent method.
		//
		parent::__wakeup();
		
	} // __wakeup.

		

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
	 * {@link MongoClient}, the object is missing the options or the <code>connect</code>
	 * option is set to <tt>FALSE</tt>.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		if( $this->mConnection instanceof \MongoClient )
		{
			if( (! $this->offsetExists( kTAG_CONN_OPTS ))
			 || ( array_key_exists( 'connect', $tmp = $this->offsetGet( kTAG_CONN_OPTS ) )
			   && $tmp[ 'connect' ] ) )
				return TRUE;														// ==>
		}
		
		return FALSE;																// ==>
	
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
	 * We overload this method to instantiate the connection resource and open the
	 * connection if necessary.
	 *
	 * Note that this method will modify the object options: if there were options and the
	 * <code>connect</code> option was <tt>FALSE</tt>, it will set it to <tt>TRUE</tt>.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * @access protected
	 * @return Memcached			The native connection.
	 *
	 * @throws Exception
	 */
	protected function connectionOpen()
	{
		//
		// Instantiate connection resource.
		//
		if( ! ($this->mConnection instanceof \MongoClient) )
		{
			//
			// Build connection string.
			//
			$str = explode( '?', $this->DSN() )[ 0 ];
			
			//
			// Instantiate MongoClient.
			//
			$this->mConnection = ( $this->offsetExists( kTAG_CONN_OPTS ) )
							   ? new \MongoClient( $str,
												   $this->offsetGet( kTAG_CONN_OPTS ) )
							   : new \MongoClient( $str );
		
		} // Missing connection resource.
		
		//
		// Connect.
		//
		if( ! $this->isConnected() )
		{
			//
			// Connect.
			//
			$this->mConnection->connect();
			
			//
			// Mark connected.
			//
			if( $this->offsetExists( kTAG_CONN_OPTS )
			 && array_key_exists( 'connect', $tmp = $this->offsetGet( kTAG_CONN_OPTS ) )
			 && (! $tmp[ 'connect' ]) )
			{
				$tmp[ 'connect' ] = TRUE;
				$this->offsetSet( kTAG_CONN_OPTS, $tmp );
			}
		
		} // Not connected.
		
		return $this->mConnection;													// ==>
	
	} // connectionOpen.

	 
	/*===================================================================================
	 *	connectionClose																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * We overload this method to reset the connection resource.
	 *
	 * @access protected
	 */
	protected function connectionClose()					{	$this->mConnection = NULL;	}

		

/*=======================================================================================
 *																						*
 *								PROTECTED PARSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseOffset																		*
	 *==================================================================================*/

	/**
	 * Parse offset
	 *
	 * We overload this method to handle the case in which there are more than one host:
	 * a comma in the full host URL indicates a divider.
	 *
	 * @param reference				$theParameters		Receives parsed offset.
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Offset value.
	 *
	 * @access protected
	 */
	protected function parseOffset( &$theParameters, $theOffset, $theValue )
	{
		//
		// Parse offset.
		//
		switch( $theOffset )
		{
			case kTAG_CONN_HOST:
				if( is_array( $theValue ) )
					$theValue = implode( ',', $theValue );
				$theParameters[ 'host' ] = $theValue;
				break;
		
			default:
				if( $this->offsetExists( kTAG_CONN_HOST ) )
					parent::parseOffset( $theParameters, $theOffset, $theValue );
				break;
		
		} // Parsing offsets.
		
	} // parseOffset.

		

/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE INSTANTIATION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newDatabase																		*
	 *==================================================================================*/

	/**
	 * Return a new database instance
	 *
	 * We overload this method to instantiate a {@link MongoDatabase} object.
	 *
	 * @param array					$theOffsets			Full database offsets.
	 *
	 * @access protected
	 * @return DatabaseObject		Database instance.
	 */
	protected function newDatabase( $theOffsets )
	{
	
	} // newDatabase.

	 

} // class MongoServer.


?>
