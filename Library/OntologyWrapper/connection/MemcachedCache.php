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
 * To instantiate an object with the data source name you can provide an URL such as this
 * one:
 *
 * <code>memcached://user:pass@host:port#persistent_id</code>
 *
 * where:
 *
 * <ul>
 *	<li><code>memcached</code>: This represents the protocol and will be stored in the
 *		{@link kTAG_CONN_PROTOCOL} offset.
 *	<li><code>host</code>: This represents the host and will be stored in the
 *		{@link kTAG_CONN_HOST} offset.
 *	<li><code>port</code>: This represents the port and will be stored in the
 *		{@link kTAG_CONN_PORT} offset.
 *	<li><code>path</code>: This represents the socket and will be stored in the
 *		{@link kTAG_CONN_SOCKET} offset.
 *	<li><code>persistent_id</code>: This represents the persistent ID and will be stored in
 *		the {@link kTAG_CONN_PID} offset.
 * </ul>
 *
 * Or you can provide an array matching the above parameters.
 *
 * To instantiate an object using a UNIX socket, you should either provide an array with the
 * socket path in the {@link kTAG_CONN_SOCKET} offset, or a connection URL such as
 * <code>/path/to/socket</code>, omitting all other parameters. In the first case you may
 * also add other parameters, but these will be excluded from the data source name.
 *
 * If you want to instantiate a cache which uses a list of weighted hosts or an existing
 * cache, you should first instantiate the cache with a persistent ID and then in your
 * application only provide the persistent ID:
 *
 * <code><pre>
 * //
 * // Instantiate cache (only once!).
 * //
 * $cache = new Memcached( "persistent_id" );
 * $cache->addServer('host1', 11211, 33);
 * $cache->addServer('host2', 11211, 67);
 *
 * //
 * // In your application code.
 * //
 * $my_cache = new MemcachedCache( "#persistent_id" );
 * </pre></code>
 *
 * For more information on the specifics of this particular cache engine, please consult the
 * {@link Memcached} documentation.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 20/01/2014
 */
class MemcachedCache extends CacheObject
{


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
	 * We overload the constructor to instantiate the {@link Memcached} object and set it
	 * into the {@link $mConnection} data member if the parameter was provided.
	 *
	 * This also means that when you instantiate the object with a parameter, you should
	 * always provide the persistent ID, if planned, to the constructor.
	 *
	 * @param mixed					$theParameter		Data source name or parameters.
	 * @param ConnectionObject		$theParent			Connection parent.
	 *
	 * @access public
	 */
	public function __construct( $theParameter = NULL, $theParent = NULL )
	{
		//
		// Call parent method.
		//
		parent::__construct( $theParameter, $theParent );
		
		//
		// Instantiate the Memcached object.
		//
		if( $theParameter !== NULL )
			$this->mConnection = ( $this->offsetExists( kTAG_CONN_PID ) )
							   ? new \Memcached( $this->offsetGet( kTAG_CONN_PID ) )
							   : new \Memcached();

	} // Constructor.

		

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
			// Set pair.
			//
			$ok = $this->mConnection->set( $theKey, $theValue, $theTime );
			if( ! $ok )
			{
				$code = $this->mConnection->getResultCode();
				$message = $this->mConnection->getResultMessage();
				throw new \Exception( $message, $code );						// !@! ==>
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
	 * @throws Exception
	 *
	 * @uses isConnected()
	 * @uses Connection()
	 */
	public function get( $theKey )
	{
		//
		// Check connection.
		//
		if( $this->isConnected() )
		{
			//
			// Get value.
			//
			$value = $this->mConnection->get( $theKey );
			$code = $this->mConnection->getResultCode();
			
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
			$message = $this->mConnection->getResultMessage();
			throw new \Exception( $message, $code );							// !@! ==>
		
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
	 * @throws Exception
	 *
	 * @uses isConnected()
	 * @uses Connection()
	 */
	public function del( $theKey )
	{
		//
		// Check connection.
		//
		if( $this->isConnected() )
		{
			//
			// Delete key.
			//
			$ok = $this->mConnection->delete( $theKey );
			if( $ok )
				return TRUE;														// ==>
			
			//
			// Handle not found.
			//
			$code = $this->mConnection->getResultCode();
			if( $code == \Memcached::RES_NOTFOUND )
				return NULL;														// ==>
			
			//
			// Failed.
			//
			$message = $this->mConnection->getResultMessage();
			throw new \Exception( $message, $code );							// !@! ==>
		
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
	 *
	 * @uses isConnected()
	 * @uses Connection()
	 */
	public function flush()
	{
		//
		// Check connection.
		//
		if( $this->isConnected() )
		{
			//
			// Invalidate cache.
			//
			if( ! $this->mConnection->flush() )
			{
				$code = $this->mConnection->getResultCode();
				$message = $this->mConnection->getResultMessage();
				throw new \Exception( $message, $code );						// !@! ==>
			
			} // Failed.
		
		} // Is connected.
		
		else
			throw new \Exception(
				"The cache in not open." );										// !@! ==>
	
	} // flush.

		

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
	 * We overload this method to check whether the connection resource has servers assigned
	 * to it: this determines whether the connection is open or not.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		if( $this->mConnection !== NULL )
			return ( count( $this->mConnection->getServerList() ) > 0 );			// ==>
		
		return FALSE;																// ==>
	
	} // isConnected.

		

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
	 *
	 * @throws Exception
	 */
	protected function connectionOpen()
	{
		//
		// Instantiate the Memcache.
		//
		if( $this->mConnection === NULL )
			$this->mConnection = ( $this->offsetExists( kTAG_CONN_PID ) )
							   ? new \Memcached( $this->offsetGet( kTAG_CONN_PID ) )
							   : new \Memcached();
		
		//
		// Add servers.
		//
		if( ! count( $this->mConnection->getServerList() ) )
		{
			//
			// Add by socket.
			//
			if( $this->offsetExists( kTAG_CONN_SOCKET ) )
				$ok = $this->mConnection->addServer(
					$this->offsetGet( kTAG_CONN_SOCKET ) );
			
			//
			// Add by host.
			//
			elseif( $this->offsetExists( kTAG_CONN_HOST ) )
				$ok = $this->mConnection->addServer(
					$this->offsetGet( kTAG_CONN_HOST ),
					$this->offsetGet( kTAG_CONN_PORT ) );
			
			//
			// Missing parameters.
			//
			else
				throw new \Exception(
					"Requires a host or a socket." );							// !@! ==>
			
			//
			// Check outcome.
			//
			if( ! $ok )
			{
				$code = $this->mConnection->getResultCode();
				$message = $this->mConnection->getResultMessage();
				throw new \Exception( $message, $code );						// !@! ==>
			
			} // Failed.
		
		} // No servers yet.
		
		return $this->mConnection;													// ==>
	
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
		if( ! $this->mConnection->quit() )
		{
			$code = $this->mConnection->getResultCode();
			$message = $this->mConnection->getResultMessage();
			throw new \Exception( $message, $code );							// !@! ==>
		
		} // Failed.
	
	} // connectionClose.

		

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
	 * We overload this method to parse the following offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_SOCKET}</tt>: The <code>path</code> URL element.
	 *	<li><tt>{@link kTAG_CONN_PID}</tt>: The <code>fragment</code> URL element.
	 * </ul>
	 *
	 * If the {@link kTAG_CONN_SOCKET} is set, we skip all other offsets, since the
	 * resulting connection URl would be invalid.
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
			case kTAG_CONN_SOCKET:
				$theParameters[ 'path' ] = $theValue;
				break;
		
			case kTAG_CONN_PID:
				$theParameters[ 'fragment' ] = $theValue;
				break;
		
			default:
				if( $this->offsetExists( kTAG_CONN_HOST ) )
					parent::parseOffset( $theParameters, $theOffset, $theValue );
				break;
		
		} // Parsing offsets.
		
	} // parseOffset.

		

/*=======================================================================================
 *																						*
 *							PROTECTED PARAMETER LOADING INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadDSNParameter																*
	 *==================================================================================*/

	/**
	 * Load connection parameters from DSN
	 *
	 * We overload this method to handle the following parameters:
	 *
	 * <ul>
	 *	<li><tt><code>path</code></tt>: We set it in {@link kTAG_CONN_SOCKET}.
	 *	<li><tt><code>fragment</code></tt>: We set it in {@link kTAG_CONN_PID}.
	 * </ul>
	 *
	 * and to remove all other parameters if there is the socket.
	 *
	 * @param reference				$theParameters		Original parameters list.
	 * @param string				$theKey				Parameter key.
	 * @param string				$theValue			Parameter value.
	 *
	 * @access protected
	 */
	protected function loadDSNParameter( &$theParameters, $theKey, $theValue = NULL )
	{
		//
		// Parse parameter.
		//
		switch( $theKey )
		{
			case 'path':
				$this->offsetSet( kTAG_CONN_SOCKET, $theValue );
				break;
			
			case 'fragment':
				$this->offsetSet( kTAG_CONN_PID, $theValue );
				break;
			
			default:
				parent::loadDSNParameter( $theParameters, $theKey, $theValue );
				break;
		
		} // Parsing parameter.
	
	} // loadDSNParameter.

	 

} // class MemcachedCache.


?>