<?php

/**
 * Connection.php
 *
 * This file contains the definition of the {@link Connection} class.
 */

namespace OntologyWrapper\connection;

use OntologyWrapper\connection\MemcachedCache;

/*=======================================================================================
 *																						*
 *									Connection.php										*
 *																						*
 *======================================================================================*/

/**
 * Connection generator
 *
 * This static class allows the creation of connection objects from data source names, it
 * features a single static method, {@link NewConnection()}, that expects a connection
 * string and will return an instance of the class corresponding to the connection protocol.
 *
 * If the connection protocol is not supported, the method will raise an exception.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 20/01/2014
 */
class Connection
{
		

/*=======================================================================================
 *																						*
 *									STATIC INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	NewConnection																	*
	 *==================================================================================*/

	/**
	 * Return a connection instance
	 *
	 * This method will return a connection instance from the provided data source name.
	 *
	 * If the connection protocol is not supported, the method will raise an exception.
	 *
	 * This class supports:
	 *
	 * <ul>
	 *	<li><code>memcached</code>: Will return an instance of {@link MemcachedCache}.
	 * </ul>
	 *
	 * @param string				$theDSN				Data source name.
	 *
	 * @static
	 *
	 * @throws Exception
	 */
	static function NewConnection( $theDSN )
	{
		//
		// Parse protocol.
		//
		$params = parse_url( (string) $theDSN );
		if( is_array( $params ) )
		{
			//
			// Get protocol.
			//
			$protocol = ( array_key_exists( 'scheme', $params ) )
					  ? $params[ 'scheme' ]
					  : '';
			
			//
			// Parse by protocol.
			//
			switch( strtolower( $protocol ) )
			{
				case 'memcached':
					return new MemcachedCache( $theDSN );							// ==>
				
				default:
					throw new \Exception(
						"Unsupported protocol [$protocol]." );					// !@! ==>
			
			} // Parsing protocol.
		
		} // Valid DSN.
		
		else
			throw new \Exception(
				"Malformed connection string [$theDSN]." );						// !@! ==>
	
	} // NewConnection.

	 

} // class MemcachedCache.


?>