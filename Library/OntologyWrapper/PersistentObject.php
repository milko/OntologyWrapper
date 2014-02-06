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
	 * Persistent trait.
	 *
	 * We use this trait to make objects of this class persistent.
	 */
	use	PersistentTrait;

		

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
			// Load object.
			//
			$found = $this->objectLoad( $theContainer, $theIdentifier );
			
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

	 

} // class PersistentObject.


?>
