<?php

/**
 * MemcachedDictionary.php
 *
 * This file contains the definition of the {@link MemcachedDictionary} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *								MemcachedDictionary.php									*
 *																						*
 *======================================================================================*/

/**
 * Memcached dictionary cache
 *
 * This trait implements the virtual interface of the {@link DictionaryObject} using the
 * {@link Memcached} class.
 *
 * This trait requires the constructor, in the class that will be using it, to load the two
 * declared data members and to handle the servers.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
trait MemcachedDictionary
{
		

/*=======================================================================================
 *																						*
 *						PUBLIC DICTIONARY MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	dictionaryFlush																	*
	 *==================================================================================*/

	/**
	 * Flush dictionary
	 *
	 * In this trait we use the {@link Memcached::flush()} method.
	 *
	 * @param integer				$theDelay			Delay before flush.
	 *
	 * @access public
	 */
	public function dictionaryFlush( $theDelay = 0 )
	{
		$this->mCache->flush( $theDelay );
	
	} // dictionaryFlush.

		

/*=======================================================================================
 *																						*
 *								PUBLIC DICTIONARY UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	dictionaryKeys																	*
	 *==================================================================================*/

	/**
	 * Return dictionary keys
	 *
	 * This trait will return all dictionary keys.
	 *
	 * @access public
	 * @return array				List of dictionary keys.
	 */
	public function dictionaryKeys()
	{
		return $this->mCache->getAllKeys();											// ==>
	
	} // dictionaryKeys.

		

/*=======================================================================================
 *																						*
 *							PROTECTED DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setEntry																		*
	 *==================================================================================*/

	/**
	 * Set a dictionary entry
	 *
	 * In this trait we use the {@link Memcached::set()} method to add entries to the
	 * dictionary. This method will receive two kinds of objects:
	 *
	 * <ul>
	 *	<li><em>Identifiers</em>: In this case the <em>key is a string</em> representing the
	 *		persistent identifier of a tag, and the <em>value is an integer</em>
	 *		representing the native identifier of the tag.
	 *	<li><em>Tags</em>: In this case the <em>key is an integer</em> representing the
	 *		native identifier of the tag and the <em>value is an object or array</em>,
	 *		representing the tag object.
	 * </ul>
	 *
	 * The method expects the parameters to be correctly casted.
	 *
	 * @param mixed					$theKey				Entry key.
	 * @param mixed					$theValue			Entry value.
	 * @param integer				$theLife			Entry lifetime.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function setEntry( $theKey, $theValue, $theLife )
	{
		//
		// Load element.
		//
		if( ! $this->mCache->set( $theKey, $theValue, $theLife ) )
		{
			$code = $this->mCache->getResultCode();
			$message = $this->mCache->getResultMessage();
			throw new \Exception( $message, $code );							// !@! ==>
		
		} // Failed.
	
	} // setEntry.

	 
	/*===================================================================================
	 *	setEntriesByArray																*
	 *==================================================================================*/

	/**
	 * Set a list dictionary entries from an array
	 *
	 * In this trait we use the {@link Memcached::setMulti()} method to add entries to the
	 * dictionary. This method will receive two kinds of objects:
	 *
	 * The method expects the parameters to be correctly casted.
	 *
	 * @param array					$theEntries			Entries array.
	 * @param integer				$theLife			Entry lifetime.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function setEntriesByArray( &$theEntries, $theLife )
	{
		//
		// Load elements.
		//
		if( ! $this->mCache->setMulti( $theEntries, $theLife ) )
		{
			$code = $this->mCache->getResultCode();
			$message = $this->mCache->getResultMessage();
			throw new \Exception( $message, $code );							// !@! ==>
	
		} // Failed.
	
	} // setEntriesByArray.

	 
	/*===================================================================================
	 *	setEntriesByIterator															*
	 *==================================================================================*/

	/**
	 * Set a list of dictionary entries from an iterator
	 *
	 * In this trait we use the {@link Memcached::setMulti()} method to add entries to the
	 * dictionary. This method expects an iterator indexed by entry key.
	 *
	 * The method expects the parameters to be correctly casted.
	 *
	 * @param Iterator				$theEntries			Entries iterator.
	 * @param integer				$theLife			Entry lifetime.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function setEntriesByIterator( \Iterator $theEntries, $theLife )
	{
		//
		// Load elements.
		//
		if( ! $this->mCache->setMulti( iterator_to_array( $theEntries ), $theLife ) )
		{
			$code = $this->mCache->getResultCode();
			$message = $this->mCache->getResultMessage();
			throw new \Exception( $message, $code );							// !@! ==>
	
		} // Failed.
	
	} // setEntriesByIterator.

	 
	/*===================================================================================
	 *	getEntry																		*
	 *==================================================================================*/

	/**
	 * Get a dictionary entry
	 *
	 * In this trait we use the {@link Memcached::get()} method to retrieve entries from the
	 * dictionary. This method will select two kinds of objects:
	 *
	 * <ul>
	 *	<li><em>Identifiers</em>: In this case the <em>key is a string</em> representing the
	 *		persistent identifier of a tag.
	 *	<li><em>Tags</em>: In this case the <em>key is an integer</em> representing the
	 *		native identifier of the tag.
	 * </ul>
	 *
	 * The method expects the parameter to be correctly casted.
	 *
	 * @param mixed					$theKey				Entry key.
	 *
	 * @access protected
	 * @return mixed				Entry value or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	protected function getEntry( $theKey )
	{
		//
		// get dictionary entry.
		//
		$value = $this->mCache->get( $theKey );
		
		//
		// Get status code.
		//
		$code = $this->mCache->getResultCode();
		
		//
		// Handle found.
		//
		if( $code == \Memcached::RES_SUCCESS )
			return $value;															// ==>
		
		//
		// Handle not found.
		//
		if( $code == \Memcached::RES_NOTFOUND )
			return NULL;															// ==>
		
		//
		// Failed.
		//
		$message = $this->mCache->getResultMessage();
		throw new \Exception( $message, $code );								// !@! ==>
	
	} // getEntry.

	 
	/*===================================================================================
	 *	delEntry																		*
	 *==================================================================================*/

	/**
	 * Get a dictionary entry
	 *
	 * In this trait we use the {@link Memcached::delete()} method to delete entries from
	 * the dictionary. This method will select two kinds of objects:
	 *
	 * <ul>
	 *	<li><em>Identifiers</em>: In this case the <em>key is a string</em> representing the
	 *		persistent identifier of a tag.
	 *	<li><em>Tags</em>: In this case the <em>key is an integer</em> representing the
	 *		native identifier of the tag.
	 * </ul>
	 *
	 * The method will return <tT>TRUE</tt> if the entry was deleted, <tt>FALSE</tt> if the
	 * entry was not found and raise an exception on any other error.
	 *
	 * The method expects the parameter to be correctly casted.
	 *
	 * @param mixed					$theKey				Entry key.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> deleted, <tt>FALSE</tt> not matched.
	 *
	 * @throws Exception
	 */
	protected function delEntry( $theKey )
	{
		//
		// Delete key.
		//
		if( ! $this->mCache->delete( $theKey ) )
		{
			//
			// Get status.
			//
			$code = $this->mCache->getResultCode();
			
			//
			// Handle not found.
			//
			if( $code == \Memcached::RES_NOTFOUND )
				return FALSE;													// ==>
			{
				$message = $this->mCache->getResultMessage();
				throw new \Exception( $message, $code );						// !@! ==>
			
			} // Failed.
			
			//
			// Assert not found.
			//
			if( $doAssert )
				throw new \Exception(
					"Unmatched global identifier [$theKey]." );					// !@! ==>
		
		} // Not deleted.
	
	} // delEntry.

	 

} // class MemcachedDictionary.


?>
