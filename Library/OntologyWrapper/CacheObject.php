<?php

/**
 * CacheObject.php
 *
 * This file contains the definition of the {@link CacheObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;

/*=======================================================================================
 *																						*
 *									CacheObject.php										*
 *																						*
 *======================================================================================*/

/**
 * Cache object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing cache instances,
 * which provide a fast access to key/value pairs.
 *
 * The main purpose of this class is to wrap specific cache engines, such as
 * {@link Memcache}, in a common interface to abstract the workings from the specifics of
 * the technology used.
 *
 * The class features four main operations:
 *
 * <ul>
 *	<li><tt>{@link set}</tt>: This method will <i>set a key/value pair</i>.
 *	<li><tt>{@link get}</tt>: This method will <i>retrieve the value associated with a
 *		key</i>.
 *	<li><tt>{@link del}</tt>: This method will <i>delete the value associated with a
 *		key</i>.
 *	<li><tt>{@link flush}</tt>: This method will <i>invalidate all cache entries</i>.
 * </ul>
 *
 * All the above methods are virtual, concrete derived classes must implement them.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 20/01/2014
 */
abstract class CacheObject extends ConnectionObject
{
		

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
	 * This method should set a key/value pair in the cache, if the key already exists, the
	 * method should replace the existing entry.
	 *
	 * The last parameter represents the expiration time in seconds; a <tt>0</tt> value
	 * means that the entry does not expire.
	 *
	 * If the operation failed, the method should raise an exception.
	 *
	 * @param mixed					$theKey				Key.
	 * @param mixed					$theValue			Value.
	 * @param integer				$theTime			Expiration.
	 *
	 * @access public
	 */
	abstract public function set( $theKey, $theValue, $theTime = 0 );

	 
	/*===================================================================================
	 *	get																				*
	 *==================================================================================*/

	/**
	 * Get a value by key
	 *
	 * This method should return the value associated with the provided key, if the key was
	 * not found, the method should return <tt>NULL</tt>.
	 *
	 * If the operation failed, the method should raise an exception.
	 *
	 * @param mixed					$theKey				Key.
	 *
	 * @access public
	 * @return mixed				The value associated with the provided key.
	 */
	abstract public function get( $theKey );

	 
	/*===================================================================================
	 *	del																				*
	 *==================================================================================*/

	/**
	 * Delete a value by key
	 *
	 * This method should delete the value associated with the provided key, if the key was
	 * found, the method should return <tt>TRUE</tt>; if the key was not found, the method
	 * should return <tt>NULL</tt>.
	 *
	 * If the operation failed, the method should raise an exception.
	 *
	 * @param mixed					$theKey				Key.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt> means deleted, <tt>NULL</tt> not found.
	 */
	abstract public function del( $theKey );

	 
	/*===================================================================================
	 *	flush																			*
	 *==================================================================================*/

	/**
	 * Invalidate cache
	 *
	 * This method should invalidate the cache, clearing all keys.
	 *
	 * If the operation failed, the method should raise an exception.
	 *
	 * @access public
	 */
	abstract public function flush();

	 

} // class CacheObject.


?>