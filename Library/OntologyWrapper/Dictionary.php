<?php

/**
 * Dictionary.php
 *
 * This file contains the definition of the {@link Dictionary} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\DictionaryObject;

/*=======================================================================================
 *																						*
 *									Dictionary.php										*
 *																						*
 *======================================================================================*/

/**
 * Dictionary
 *
 * This class implements a <em>concrete</em> instance of the {@link DictionaryObject} class
 * which uses the {@link MemcachedDictionary} trait to implement the dictionary cache.
 *
 * This class implements a constructor which expects the {@link Memcached} persistent
 * identifier and the list of servers.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/02/2014
 */
class Dictionary extends DictionaryObject
{
	/**
	 * Memcached dictionary trait.
	 *
	 * We use this trait to implement the dictionary cache.
	 */
	use	traits\MemcachedDictionary;

		

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
	 *	<li><b>$theIdentifier</b>: This string parameter represents the {@limk Memcached}
	 *		persistent identifier provided to its constructor, the parameter is required,
	 *		since the cache should be active across sessions.
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
	 *		This parameter may be omitted if the cache has been instantiate elsewhere with
	 *		the same persistent identifier.
	 * </ul>
	 *
	 * The constructor will first instantiate the {@link Memcached} object, then it will
	 * check if the connection resource has already a list of servers associated, if that is
	 * the case we assume the cache is already initialised; if that is not the case, we
	 * will add the servers provided in the second parameter.
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
		// Call parent constructor.
		//
		parent::__construct();
		
		//
		// Init PID.
		//
		$this->mPid = (string) $theIdentifier;
		
		//
		// Init resource.
		//
		$this->mCache = new \Memcached( $this->mPid );
		
		//
		// Init cache.
		//
		if( ! count( $this->mCache->getServerList() ) )
		{
			//
			// Check servers.
			//
			if( is_array( $theServers ) )
			{
				//
				// Add servers.
				//
				if( ! $this->mCache->addServers( $theServers ) )
				{
					$code = $this->mCache->getResultCode();
					$message = $this->mCache->getResultMessage();
					throw new \Exception( $message, $code );					// !@! ==>
			
				} // Failed.
			
			} // Provided servers.
			
		} // Not initialised.

	} // Constructor.

	 

} // class Dictionary.


?>
