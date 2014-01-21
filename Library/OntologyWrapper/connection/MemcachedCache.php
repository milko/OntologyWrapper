<?php

/**
 * MemcachedCache.php
 *
 * This file contains the definition of the {@link MemcachedCache} class.
 */

namespace OntologyWrapper\connection;

use OntologyWrapper\CacheObject;

/*=======================================================================================
 *																						*
 *									MemcachedCache.php									*
 *																						*
 *======================================================================================*/

/**
 * Memcached cache object
 *
 * This class represents a concrete instance of a {@link CacheObject} implemented using
 * the {@link Memcached} class.
 *
 * The {@link DSN() DSN} property is represented by the host/port pair and the
 * {@link Connection() connection} by the {@link Memcached} object. A typical string could
 * be <code>memcached://example.net:11211/persistent_id</code> where:
 *
 * <ul>
 *	<li><code>memcached</code>: This represents the protocol.
 *	<li><code>example.net</code>: This represents the host.
 *	<li><code>11211</code>: This represents the port.
 *	<li><code>persistent_id</code>: This represents the persistent ID.
 * </ul>
 *
 * If you want to instantiate a cache that has several hosts or an existing cache, you
 * should instantiate the cache separately with a persistent ID and pass to this class
 * constructor only the persistent ID.
 *
 * <code><pre>
 * //
 * // Instantiate cache (only once!).
 //
 * $cache = new Memcached( "persistent_id" );
 * $cache->addServer('mem1.domain.com', 11211, 33);
 * $cache->addServer('mem2.domain.com', 11211, 67);
 *
 * //
 * // In your application code.
 * //
 * $my_cache = new MemcachedCache( "memcached:// /persistent_id" );
 * </pre></code>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 20/01/2014
 */
class MemcachedCache extends CacheObject
{
	/**
	 * URL dictionary.
	 *
	 * We overload this static data member to add the <code>path</code> parameter as the
	 * connection persistent identifier, {@link kTAG_CONN_PID}.
	 *
	 * @var array
	 */
	static $sParseURL = array( 'scheme'	=> kTAG_CONN_PROTOCOL,
							   'host'	=> kTAG_CONN_HOST,
							   'port'	=> kTAG_CONN_PORT,
							   'user'	=> kTAG_CONN_USER,
							   'pass'	=> kTAG_CONN_PASS,
							   'path'	=> kTAG_CONN_PID );

		

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	set																				*
	 *==================================================================================*/

	/**
	 * Set a key/value pair
	 *
	 * This method can be used to set a key/value pair.
	 *
	 * The method will raise an exception if the operation fails and if the cache is not
	 * yet open.
	 *
	 * @param mixed					$theKey				Key.
	 * @param mixed					$theValue			Value.
	 * @param integer				$theTime			Expiration.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses isConnected()
	 * @uses Connection()
	 */
	public function set( $theKey, $theValue, $theTime = 0 )
	{
		//
		// Check connection.
		//
		if( $this->isConnected() )
		{
			//
			// Get connection.
			//
			$connection = $this->Connection();

			//
			// Set pair.
			//
			$ok = $connection->set( $theKey, $theValue, $theTime );
			if( ! $ok )
			{
				$code = $connection->getResultCode();
				$message = $connection->getResultMessage();
				throw new \Exception( $message, $code );							// !@! ==>
			}
		
		} // Is connected.
		
		else
			throw new \Exception(
				"The cache in not open." );										// !@! ==>
	
	} // set.

	 
	/*===================================================================================
	 *	get																				*
	 *==================================================================================*/

	/**
	 * Get a value by key
	 *
	 * This method will return the value associated with the provided key, if the key was
	 * not found, the method should return <tt>NULL</tt>.
	 *
	 * If the operation failed, the method will raise an exception.
	 *
	 * @param mixed					$theKey				Key.
	 *
	 * @access public
	 * @return mixed				The value associated with the provided key.
	 *
	 * @uses isConnected()
	 * @uses Connection()
	 *
	 * @throws Exception
	 */
	public function get( $theKey )
	{
		//
		// Check connection.
		//
		if( $this->isConnected() )
		{
			//
			// Get connection.
			//
			$connection = $this->Connection();

			//
			// Get value.
			//
			$value = $connection->get( $theKey );
			$code = $connection->getResultCode();
			
			//
			// Handle found.
			//
			if( $code == \Memcached::RES_SUCCESS )
				return $value;														// ==>
			
			//
			// Handle not found.
			//
			if( $code == \Memcached::RES_NOTFOUND )
				return NULL;														// ==>
			
			//
			// Failed.
			//
			$message = $connection->getResultMessage();
			throw new \Exception( $message, $code );								// !@! ==>
		
		} // Is connected.
		
		else
			throw new \Exception(
				"The cache in not open." );										// !@! ==>
	
	} // get.

	 
	/*===================================================================================
	 *	del																				*
	 *==================================================================================*/

	/**
	 * Delete a value by key
	 *
	 * This method will delete the value associated with the provided key, if the key was
	 * found, the method will return <tt>TRUE</tt>; if the key was not found, the method
	 * will return <tt>NULL</tt>.
	 *
	 * If the operation failed, the method will raise an exception.
	 *
	 * @param mixed					$theKey				Key.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt> means deleted, <tt>NULL</tt> not found.
	 *
	 * @uses isConnected()
	 * @uses Connection()
	 *
	 * @throws Exception
	 */
	public function del( $theKey )
	{
		//
		// Check connection.
		//
		if( $this->isConnected() )
		{
			//
			// Get connection.
			//
			$connection = $this->Connection();

			//
			// Delete key.
			//
			$ok = $connection->delete( $theKey );
			if( $ok )
				return TRUE;														// ==>
			
			//
			// Handle not found.
			//
			$code = $connection->getResultCode();
			if( $code == \Memcached::RES_NOTFOUND )
				return NULL;														// ==>
			
			//
			// Failed.
			//
			$message = $connection->getResultMessage();
			throw new \Exception( $message, $code );								// !@! ==>
		
		} // Is connected.
		
		else
			throw new \Exception(
				"The cache in not open." );										// !@! ==>
	
	} // del.

	 
	/*===================================================================================
	 *	flush																			*
	 *==================================================================================*/

	/**
	 * Invalidate cache
	 *
	 * This method will invalidate the cache, clearing all keys.
	 *
	 * If the operation failed, the method will raise an exception.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function flush()
	{
		//
		// Check connection.
		//
		if( $this->isConnected() )
		{
			//
			// Get connection.
			//
			$connection = $this->Connection();

			//
			// Invalidate cache.
			//
			if( ! $connection->flush() )
			{
				$code = $connection->getResultCode();
				$message = $connection->getResultMessage();
				throw new \Exception( $message, $code );						// !@! ==>
			
			} // Failed.
		
		} // Is connected.
		
		else
			throw new \Exception(
				"The cache in not open." );										// !@! ==>
	
	} // flush.

		

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
	 * This method will instantiate the {@link Memcached} object with the provided DSN as
	 * the persistent ID, add servers, if necessary, and return the connection resource.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * @access protected
	 * @return Memcached			The native connection.
	 */
	protected function connectionOpen()
	{
		//
		// Instantiate cache.
		//
		$connection = new \Memcached( $this->offsetGet( kTAG_CONN_PID ) );
		
		//
		// Add servers.
		//
		if( ! count( $connection->getServerList() ) )
		{
			//
			// Add server.
			//
			if( ! $connection->addServer(
					$this->offsetGet( kTAG_CONN_HOST ),
					$this->offsetGet( kTAG_CONN_PORT ) ) )
			{
				$code = $connection->getResultCode();
				$message = $connection->getResultMessage();
				throw new \Exception( $message, $code );							// !@! ==>
			
			} // Failed.
		
		} // No servers yet.
		
		return $connection;															// ==>
	
	} // connectionOpen.

	 
	/*===================================================================================
	 *	connectionClose																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * This method should close the actual connection.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open, this means that it assumes the {@link Connection()} data member holds a
	 * {@link Memcached} object.
	 *
	 * @access protected
	 */
	protected function connectionClose()
	{
		//
		// Get cache.
		//
		$connection = $this->Connection();
		if( ! $connection->quit() )
		{
			$code = $connection->getResultCode();
			$message = $connection->getResultMessage();
			throw new \Exception( $message, $code );								// !@! ==>
		
		} // Failed.
	
	} // connectionClose.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PARSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseParameters																	*
	 *==================================================================================*/

	/**
	 * Parse connection parameters
	 *
	 * We ovewrload this method to parse the persistent identifier, {@link kTAG_CONN_PID}.
	 *
	 * @access protected
	 * @return mixed				Data source name or <tt>FALSE</tt> if empty.
	 *
	 * @see kTAG_CONN_PID
	 */
	protected function parseParameters()
	{
		//
		// Parse default parameters.
		//
		$dsn = parent::parseParameters();
		if( $dsn !== FALSE )
		{
			//
			// Add persistent identifier.
			//
			if( $this->offsetGet( kTAG_CONN_PID ) )
				$dsn .= ('/'.$this->offsetGet( kTAG_CONN_PID ));
		
		} // Not empty.
		
		return $dsn;																// ==>
	
	} // parseParameters.

		

/*=======================================================================================
 *																						*
 *							PROTECTED PARAMETER LOADING INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadMainParameters																*
	 *==================================================================================*/

	/**
	 * Load connection main parameters
	 *
	 * We overload this method to strip the leading slash from the persistent identifier,
	 * {@link kTAG_CONN_PID}.
	 *
	 * @param reference				$theParameters		Array of parsed parameters.
	 *
	 * @access protected
	 *
	 * @see $sParseURL kTAG_CONN_PID
	 */
	protected function loadMainParameters( &$theParameters )
	{
		//
		// Call parent method.
		//
		parent::loadMainParameters( $theParameters );
		
		//
		// Handle persistent identifier.
		//
		if( $this->offsetExists( kTAG_CONN_PID ) )
			$this->offsetSet(
				kTAG_CONN_PID,
				substr( $this->offsetGet( kTAG_CONN_PID ), 1 ) );
	
	} // loadMainParameters.

	 

} // class MemcachedCache.


?>
