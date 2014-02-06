<?php

/**
 * PersistentObject.php
 *
 * This file contains the definition of the {@link PersistentObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\OntologyObject;

/*=======================================================================================
 *																						*
 *								PersistentObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Persistent object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing objects that can
 * persist in a container and that are constituted by ontology offsets.
 *
 * The main purpose of this class is to add the status and persistence traits to the parent
 * class, providing the prototypes needed to implement concrete persistent objects.
 *
 * The class makes use of the {@link StatusTrait} and {@link PersistenceTrait} traits:
 *
 * <ul>
 *	<li><tt>{@link StatusTrait}</tt>: This trait handles a bitfirld data member that keeps
 *		track of the object's status:
 *	 <ul>
 *		<li><tt>{@link isDirty()}</tt>: This flag is set whenever any offset is modified,
 *			this status can be tested whenever the object should be stored in a persistent
 *			container: if set, it means the object has been modified, if not set, it means
 *			that the object is identical to the persistent copy.
 *		<li><tt>{@link isCommitted()}</tt>: This flag is set whenever the object has been
 *			loaded or stored into a persistent container. This status can be useful to lock
 *			properties that cannot change once the object is stored.
 *	 </ul>
 *	<li><tt>{@link PersistenceTrait}</tt>: This trait handles the object persistence.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
abstract class PersistentObject extends OntologyObject
{
	/**
	 * Status trait.
	 *
	 * In this class we handle the {@link isDirty()} and the {@link isCommitted()} flags.
	 */
	use	StatusTrait;

	/**
	 * Persistence trait.
	 *
	 * In this class we handle the {@link isDirty()} and the {@link isCommitted()} flags.
	 */
//	use	PersistenceTrait;

		

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
	 * Objects derived from this class share the same constructor prototype, this allows
	 * instantiating an object by providing content, as for the parent class, or by
	 * providing an identifier and a container to retrieve the object from a persistent
	 * store.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: This may be either an array containing the object's
	 *		persistent attributes, or a reference to a persistent connection, in which case
	 *		the second parameter is required to select the object. If this parameter is
	 *		<tt>NULL</tt>, the next parameter will be ignored.
	 *	<li><b>$theIdentifier</b>: This parameter should only be provided if the fist
	 *		parameter is a persistent connection: this value will be used to find the object
	 *		using the provided connection.
	 * </ul>
	 *
	 * Once the object has been instantiated, the method will reset the {@link isDirty()}
	 * flag.
	 *
	 * @param mixed					$theContainer		Object attributes or container.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Instantiate empty object.
		//
		if( $theContainer === NULL )
			parent::__construct();
		
		//
		// Instantiate from object attributes.
		//
		elseif( $theIdentifier === NULL )
		{
			//
			// Handle array objects.
			//
			if( $theContainer instanceof \ArrayObject )
				parent::__construct( $theContainer->getArrayCopy() );
		
			//
			// Handle arrays.
			//
			elseif( is_array( $theContainer ) )
				parent::__construct( $theContainer );
		
			//
			// Complain.
			//
			else
				throw new \Exception(
					"Cannot instantiate object: "
				   ."invalid container parameter type." );						// !@! ==>
		
		} // Identifier not provided.
		
		//
		// Instantiate from persistent store.
		//
		else
		{
			//
			// Select object.
			//
			$found = $this->objectFind( $theContainer, $theIdentifier );
			
			//
			// Handle selected object.
			//
			if( $found !== NULL )
			{
				//
				// Load object.
				//
				parent::__construct( $found );
				
				//
				// Set committed status.
				//
				$this->isCommitted( TRUE );
			
			} // Found.
		
		} // Provided persistent store connection.
		
		//
		// Reset dirty status.
		//
		$this->isDirty( FALSE );

	} // Constructor.

		

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
	 *	objectFind																		*
	 *==================================================================================*/

	/**
	 * Find an object
	 *
	 * This method should select the object matching the provided identifier in the provided
	 * persistent container and return the object attributes as an array.
	 *
	 * If the provided identifier was not resolved, the method should return <tt>NULL</tt>.
	 *
	 * @param mixed					$theContainer		Persistent container.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access protected
	 * @return array				Found object as an array, or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function objectFind( $theContainer, $theIdentifier )
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
	
	} // objectFind.

	 

} // class PersistentObject.


?>
