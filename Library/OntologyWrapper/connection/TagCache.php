<?php

/**
 * TagCache.php
 *
 * This file contains the definition of the {@link TagCache} class.
 */

namespace OntologyWrapper\connection;

use OntologyWrapper\TagCacheObject;

/*=======================================================================================
 *																						*
 *										TagCache.php									*
 *																						*
 *======================================================================================*/

/**
 * Tag cache
 *
 * This class is a <i>concrete</i> derived instance of the {@link TagCacheObject} which
 * implements a tag cache that uses the {@link Memcached} class.
 *
 * The class implements the following additional methods:
 *
 * <ul>
 *	<li><tt>{@link init()}</tt>: The method will flush the current cache and reload all
 *		tags.
 *	<li><tt>{@link Pid()}</tt>: The method will return the connection persistent identifier.
 *	<li><tt>{@link Connection()}</tt>: The method will return the connection resource, which
 *		is, in this case, a {@link Memcached} instance.
 *	<li><tt>{@link stats()}</tt>: The method will return the connection statistics.
 * </ul>
 *
 * This class may be used as a template to implement tag caches that use other cache engines
 * if the {@link Memcached} class is not available.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 29/01/2014
 */
class TagCache extends TagCacheObject
{
	/**
	 * Cache persistent identifier.
	 *
	 * This data member holds the <i>{@link Memcached} connection persistent identifier</i>.
	 *
	 * @var string
	 */
	private $mPID = NULL;

	/**
	 * Cache connection.
	 *
	 * This data member holds the <i>{@link Memcached} instance</i>.
	 *
	 * @var Memcached
	 */
	private $mConnection = NULL;

		

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
	 * The constructor accepts two parameters:
	 *
	 * <ul>
	 *	<li><b>$theId</b>: This string parameter represents the {@limk Memcached} persistent
	 *		identifier provided to its constructor, the parameter is required, since the
	 *		cache should be active across sessions.
	 *	<li><b>$theServers</b>: This array parameter represents the list of servers that
	 *		serve the cache, it is equivalent to the parameter of the
	 *		{@link Memcached::addServers()} method, it is a list of elements comprised by
	 *		three parameters:
	 *	 <ul>
	 *		<li><i>Host</i>: The server host.
	 *		<li><i>Port</i>: The server port.
	 *		<li><i>Weight</i>: The weight of the server relative to the total weight of all
	 *			the servers in the pool.
	 *	 </ul>
	 *		This parameter may be omitted if the cache has been initialised beforehand.
	 * </ul>
	 *
	 * The constructor will first instantiate the {@link Memcached} object, then it will
	 * check if the connection resource has already a list of servers associated, if that is
	 * the case we assume the cache is already initialised; if that is not the case, we
	 * will add the servers provided in the second parameter and load all the current tag
	 * identifiers and objects.
	 *
	 * @param mixed					$theIdentifier		Persistent identifier.
	 * @param array					$theServers			List of servers.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses init()
	 */
	public function __construct( $theIdentifier, $theServers = NULL )
	{
		//
		// Init PID.
		//
		$this->mPID = (string) $theIdentifier;
		
		//
		// Init resource.
		//
		$this->mConnection = new \Memcached( $this->mPID );
		
		//
		// Init cache.
		//
		if( ! count( $this->mConnection->getServerList() ) )
		{
			//
			// Add servers.
			//
			if( ! $this->mConnection->addServers( $theServers ) )
			{
				$code = $this->mConnection->getResultCode();
				$message = $this->mConnection->getResultMessage();
				throw new \Exception( $message, $code );						// !@! ==>
			
			} // Failed.
			
			//
			// Add tags.
			//
			$this->init();
		
		} // Not initialised.

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Pid																				*
	 *==================================================================================*/

	/**
	 * Return persistent identifier.
	 *
	 * This method will return the current connection persistent identifier.
	 *
	 * @access public
	 * @return string				Persistent identifier.
	 *
	 * @see $mPID
	 */
	public function PID()											{	return $this->mPID;	}

	 
	/*===================================================================================
	 *	Connection																		*
	 *==================================================================================*/

	/**
	 * Return connection resource.
	 *
	 * This method will return the current connection resource object, the method can be
	 * used to perform custom actions on the cache.
	 *
	 * @access public
	 * @return Memcached			Connection resource.
	 *
	 * @see $mConnection
	 */
	public function Connection()							{	return $this->mConnection;	}

		

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	init																			*
	 *==================================================================================*/

	/**
	 * Initialise cache
	 *
	 * This method will invalidate the cache and load all tag identifiers and objects.
	 *
	 * If the operation fails, the method will raise an exception.
	 *
	 * @access public
	 *
	 * @uses setTagId()
	 * @uses setTagObject()
	 */
	public function init()
	{
		//
		// Check cache.
		//
		if( $this->mConnection instanceof \Memcached )
		{
			//
			// Init local storage.
			//
			$ids = array( ':gid' => 1, ':data-type' => 2, ':cardinality-type' => 3,
						  ':label' => 4, ':definition' => 5,
						  ':part:kind' => 6, ':part:value' => 7 );
			$objs = array( 1 => array( '_id' => 1, '1' => ':gid',
									   '3' => 'Global identifier', '2' => ':type:string' ),
						   2 => array( '_id' => 2, '1' => ':data-type',
									   '3' => 'Data type', '2' => ':type:set' ),
						   3 => array( '_id' => 3, '1' => ':cardinality-type',
									   '3' => 'Data type', '2' => ':type:set' ),
						   4 => array( '_id' => 4, '1' => ':label',
									   '3' => 'Label', '2' => ':type:elem-match' ),
						   5 => array( '_id' => 5, '1' => ':definition',
									   '3' => 'Definition', '2' => ':type:elem-match' ),
						   6 => array( '_id' => 6, '1' => ':part:kind',
									   '3' => 'Kind part', '2' => ':type:string' ),
						   7 => array( '_id' => 7, '1' => ':part:value',
									   '3' => 'Value part', '2' => ':type:mixed' ) );
		
			//
			// Flush cache.
			//
			$this->mConnection->flush();
		
			//
			// Load identifiers.
			//
			foreach( $ids as $key => $value )
				$this->setTagId( $key, $value );
		
			//
			// Load objects.
			//
			foreach( $objs as $key => $value )
				$this->setTagObject( $key, $value );
		
		} // Cache initialised.
		
	} // init.

		
	/*===================================================================================
	 *	setTagId																		*
	 *==================================================================================*/

	/**
	 * Set a tag identifier
	 *
	 * This method will set a key/value pair in the cache consisting of the tag global
	 * identifier as the key and the tag native identifier as the value; both values will be
	 * cast.
	 *
	 * The expiration period is infinite by default, since the tags should persist in the
	 * cache.
	 *
	 * The method assumes the cache is initialised.
	 *
	 * @param string				$theKey				Global identifier.
	 * @param integer				$theValue			Native identifier.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function setTagId( $theKey, $theValue )
	{
		//
		// Load cache.
		//
		if( ! $this->mConnection->set( (string) $theKey, (int) $theValue, 0 ) )
		{
			$code = $this->mConnection->getResultCode();
			$message = $this->mConnection->getResultMessage();
			throw new \Exception( $message, $code );							// !@! ==>
		
		} // Failed.
	
	} // setTagId.

	 
	/*===================================================================================
	 *	getTagId																		*
	 *==================================================================================*/

	/**
	 * Get a tag identifier
	 *
	 * This method will return a tag native identifier given a tag global identifier, if
	 * the identifier is not matched the method will raise an exception if the second
	 * parameter is <tt>TRUE</tt>, or <tt>NULL</tt> if the second parameter is
	 * <tt>FALSE</tt>.
	 *
	 * The method assumes the cache is initialised.
	 *
	 * @param string				$theKey				Global identifier.
	 * @param boolean				$doAssert			Assert match.
	 *
	 * @access public
	 * @return integer				The tag native identifier or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function getTagId( $theKey, $doAssert = FALSE )
	{
		//
		// Get value and status.
		//
		$value = $this->mConnection->get( (string) $theKey );
		$code = $this->mConnection->getResultCode();
		
		//
		// Handle found.
		//
		if( $code == \Memcached::RES_SUCCESS )
			return $value;															// ==>
		
		//
		// Handle not found.
		//
		if( $code == \Memcached::RES_NOTFOUND )
		{
			//
			// Do not assert.
			//
			if( ! $doAssert )
				return NULL;														// ==>
			
			throw new \Exception(
				"Unmatched global identifier [$theKey]." );						// !@! ==>
		
		} // Not found.
		
		//
		// Failed.
		//
		$message = $this->mConnection->getResultMessage();
		throw new \Exception( $message, $code );								// !@! ==>
	
	} // getTagId.

	 
	/*===================================================================================
	 *	delTagId																		*
	 *==================================================================================*/

	/**
	 * Delete a tag identifier
	 *
	 * This method will delete the tag native identifier matching the provided tag global
	 * identifier.
	 *
	 * If the provided global identifier is not matched and the second parameter is
	 * <tt>TRUE</tt>, the method will raise an exception; if the parameter is <tt>FALSE</tt>
	 * no exception should be raised.
	 *
	 * @param string				$theKey				Global identifier.
	 * @param boolean				$doAssert			Assert match.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function delTagId( $theKey, $doAssert = FALSE )
	{
		//
		// Delete key.
		//
		if( ! $this->mConnection->delete( (string) $theKey ) )
		{
			//
			// Get status.
			//
			$code = $this->mConnection->getResultCode();
			
			//
			// Handle failed.
			//
			if( $code != \Memcached::RES_NOTFOUND )
			{
				$message = $this->mConnection->getResultMessage();
				throw new \Exception( $message, $code );						// !@! ==>
			
			} // Failed.
			
			//
			// Assert not found.
			//
			if( $doAssert )
				throw new \Exception(
					"Unmatched global identifier [$theKey]." );					// !@! ==>
		
		} // Not deleted.
	
	} // delTagId.

	 
	/*===================================================================================
	 *	setTagObject																	*
	 *==================================================================================*/

	/**
	 * Set a tag object
	 *
	 * This method will set a key/value pair in the cache consisting of the tag native
	 * identifier as the key and the tag object or array as the value; the identifier will
	 * be cast.
	 *
	 * The expiration period is infinite by default, since the tags should persist in the
	 * cache.
	 *
	 * The method assumes the cache is initialised.
	 *
	 * @param integer				$theKey				Native identifier.
	 * @param mixed					$theValue			Tag object.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function setTagObject( $theKey, $theValue )
	{
		//
		// Load cache.
		//
		if( ! $this->mConnection->set( (string) $theKey, $theValue, 0 ) )
		{
			$code = $this->mConnection->getResultCode();
			$message = $this->mConnection->getResultMessage();
			throw new \Exception( $message, $code );							// !@! ==>
		
		} // Failed.
	
	} // setTagObject.

	 
	/*===================================================================================
	 *	getTagObject																	*
	 *==================================================================================*/

	/**
	 * Get a tag object
	 *
	 * This method will return a tag object or array given a tag native identifier, if
	 * the identifier is not matched the method will raise an exception if the second
	 * parameter is <tt>TRUE</tt>, or <tt>NULL</tt> if the second parameter is
	 * <tt>FALSE</tt>.
	 *
	 * The method assumes the cache is initialised.
	 *
	 * @param integer				$theKey				Native identifier.
	 * @param boolean				$doAssert			Assert match.
	 *
	 * @access public
	 * @return mixed				The tag object, contents array or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function getTagObject( $theKey, $doAssert = FALSE )
	{
		//
		// Get value and status.
		//
		$value = $this->mConnection->get( (int) $theKey );
		$code = $this->mConnection->getResultCode();
		
		//
		// Handle found.
		//
		if( $code == \Memcached::RES_SUCCESS )
			return $value;															// ==>
		
		//
		// Handle not found.
		//
		if( $code == \Memcached::RES_NOTFOUND )
		{
			//
			// Do not assert.
			//
			if( ! $doAssert )
				return NULL;														// ==>
			
			throw new \Exception(
				"Unmatched native identifier [$theKey]." );						// !@! ==>
		
		} // Not found.
		
		//
		// Failed.
		//
		$message = $this->mConnection->getResultMessage();
		throw new \Exception( $message, $code );								// !@! ==>
	
	} // getTagObject.

	 
	/*===================================================================================
	 *	delTagObject																	*
	 *==================================================================================*/

	/**
	 * Delete a tag object
	 *
	 * This method will delete the tag object matching the provided tag native identifier.
	 *
	 * If the provided native identifier is not matched and the second parameter is
	 * <tt>TRUE</tt>, the method will raise an exception; if the parameter is <tt>FALSE</tt>
	 * no exception should be raised.
	 *
	 * @param int					$theKey				Native identifier.
	 * @param boolean				$doAssert			Assert match.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function delTagObject( $theKey, $doAssert = FALSE )
	{
		//
		// Delete key.
		//
		if( ! $this->mConnection->delete( (int) $theKey ) )
		{
			//
			// Get status.
			//
			$code = $this->mConnection->getResultCode();
			
			//
			// Handle failed.
			//
			if( $code != \Memcached::RES_NOTFOUND )
			{
				$message = $this->mConnection->getResultMessage();
				throw new \Exception( $message, $code );						// !@! ==>
			
			} // Failed.
			
			//
			// Assert not found.
			//
			if( $doAssert )
				throw new \Exception(
					"Unmatched native identifier [$theKey]." );					// !@! ==>
		
		} // Not deleted.
	
	} // delTagObject.

		

/*=======================================================================================
 *																						*
 *								PUBLIC STATISTICS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	stats																			*
	 *==================================================================================*/

	/**
	 * Return cache statistics
	 *
	 * This method will return the current cache statistics.
	 *
	 * @access public
	 * @return array
	 */
	public function stats()						{	return $this->mConnection->getStats();	}

		

/*=======================================================================================
 *																						*
 *							PROTECTED CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	connectionClose																	*
	 *==================================================================================*/

	/**
	 * Close connection
	 *
	 * This method will close the connection to the cache and reset the connection resource.
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function connectionClose()
	{
		//
		// Check connection.
		//
		if( $this->mConnection instanceof \Memcached )
		{
			//
			// Close connection.
			//
			if( ! $this->mConnection->quit() )
			{
				$code = $this->mConnection->getResultCode();
				$message = $this->mConnection->getResultMessage();
				throw new \Exception( $message, $code );						// !@! ==>
		
			} // Failed.
		
			//
			// Reset connection.
			//
			$this->mConnection = NULL;
		
		} // Connected.
		
	} // connectionClose.

	 

} // class TagCache.


?>
