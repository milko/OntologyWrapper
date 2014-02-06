<?php

/**
 * PersistentTrait.php
 *
 * This file contains the definition of the {@link PersistentTrait} class.
 */

namespace OntologyWrapper;

/*=======================================================================================
 *																						*
 *									PersistentTrait.php									*
 *																						*
 *======================================================================================*/

/**
 * Persistence trait
 *
 * The main purpose of this trait is to add the ability for classes to store and retrieve
 * objects in and from persistent stores.
 *
 * This trait defines the common methods for managing the object in a persistent store:
 *
 * <ul>
 * </ul>
 *
 * This trait makes also use of the {@link StatusTrait} trait in the following way:
 *
 * <ul>
 *	<li><tt>{@link isDirty()}</tt>: This flag is set whenever any offset is modified, this
 *		status can be tested whenever the object should be stored in a persistent container:
 *		if set, it means the object has been modified, if not set, it means that the object
 *		is identical to the persistent copy.
 *	<li><tt>{@link isCommitted()}</tt>: This flag is set whenever the object has been loaded
 *		or stored into a persistent container. This status can be useful to lock properties
 *		that cannot change once the object is stored.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
trait PersistentTrait
{
	/**
	 * Status trait.
	 *
	 * In this class we handle the {@link is_committed()} flag.
	 */
	use	StatusTrait;

		

/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * Set a value at a given offset
	 *
	 * We overload this method to prevent modifying the global and native identifiers if the
	 * object is committed, {@link isCommitted()}.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @access public
	 *
	 * @uses isConnected()
	 *
	 * @throws Exception
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Skip deletions.
		//
		if( $theValue !== NULL )
		{
			//
			// Resolve offset.
			//
			$theOffset = $this->offsetResolve( $theOffset, TRUE );
			
			//
			// Check if committed.
			//
			if( $this->isCommitted() )
			{
				//
				// Check global identifier.
				//
				if( $theOffset == kTAG_GID )
					throw new \Exception(
						"Cannot modify global identifier: "
					   ."the object is committed." );							// !@! ==>
			
				//
				// Check native identifier.
				//
				if( $theOffset == kTAG_NID )
					throw new \Exception(
						"Cannot modify native identifier: "
					   ."the object is committed." );							// !@! ==>
			
			} // Object is committed.
		
			//
			// Cast value.
			//
			$theOffset = $this->offsetCast( $theValue, $theOffset );
		
			//
			// Set offset value.
			//
			ContainerObject::offsetSet( (string) $theOffset, $theValue );
			
			//
			// Set status.
			//
			$this->isDirty( TRUE );
		
		} // Not deleting.
		
		//
		// Handle delete.
		//
		else
			$this->offsetUnset( $theOffset );
	
	} // offsetSet.

	 
	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * Reset a value at a given offset
	 *
	 * We overload this method to prevent deleting values while the connection is open.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses isConnected()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Resolve offset.
		//
		$theOffset = $this->offsetResolve( $theOffset, TRUE );
		
		//
		// Check if committed.
		//
		if( $this->isCommitted() )
		{
			//
			// Check global identifier.
			//
			if( $theOffset == kTAG_GID )
				throw new \Exception(
					"Cannot modify global identifier: "
				   ."the object is committed." );								// !@! ==>
		
			//
			// Check native identifier.
			//
			if( $theOffset == kTAG_NID )
				throw new \Exception(
					"Cannot modify native identifier: "
				   ."the object is committed." );								// !@! ==>
		
		} // Object is committed.
				
		ContainerObject::offsetUnset( (string) $theOffset );
		
		//
		// Set status.
		//
		$this->isDirty( TRUE );
	
	} // offsetUnset.

		

/*=======================================================================================
 *																						*
 *							PROTECTED OBJECT RESOLUTION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	objectResolve																	*
	 *==================================================================================*/

	/**
	 * Resolve an object
	 *
	 * This method should select the object using the provided identifier in the provided
	 * persistent store container and return an array with the object's contents, if found,
	 * or <tt>NULL</tt> if not found.
	 *
	 * In this trait we handle array and array object containers, concrete classes should
	 * overload this method to handle other kinds of containers.
	 *
	 * @param mixed					$theContainer		Persistent container.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access protected
	 * @return array				Found object as an array, or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function objectResolve( $theContainer, $theIdentifier )
	{
		//
		// Handle arrays and array objects.
		//
		if( is_array( $theContainer )
		 || ($theContainer instanceof ArrayObject) )
		{
			//
			// Serialise array object.
			//
			if( $theContainer instanceof ArrayObject )
				$theContainer = $theContainer->getArrayCopy();
			
			//
			// Locate identifier.
			//
			if( array_key_exists( $theIdentifier, $theContainer ) )
				return $theContainer[ $theIdentifier ];								// ==>
			
			return NULL;															// ==>
		
		} // Array or array object.
		
		//
		// Invalid container type.
		//
		throw new \Exception(
			"Cannot find object: "
		   ."invalid or unsupported container type." );							// !@! ==>
	
	} // objectResolve.

	 

} // class PersistentTrait.


?>
