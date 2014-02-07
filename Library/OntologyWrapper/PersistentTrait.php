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

	/**
	 * Container.
	 *
	 * This data member holds the object's persistent store reference, it should be a
	 * concrete instance derived from the {@link CollectionObject} class.
	 *
	 * This data member will be set when the object is instantiated from a collection.
	 *
	 * @var CollectionObject
	 */
	protected $mCollection = NULL;

		

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
	 * Note that we shadow the inherited method: we only use the
	 * {@link ContainerObject::offsetSet()} method.
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
				// Check immutable tags.
				//
				if( in_array( $theOffset, static::$sInternalTags )
				 || ($theOffset == kTAG_GID) )
					throw new \Exception(
						"Cannot modify the [$theOffset] offset: "
					   ."the object is committed." );							// !@! ==>
			
			} // Object is committed.
		
			//
			// Cast value.
			//
			$this->offsetCast( $theValue, $theOffset );
		
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
	 * Note that we shadow the inherited method: we only use the
	 * {@link ContainerObject::offsetUnset()} method.
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
				// Check immutable tags.
				//
				if( in_array( $theOffset, static::$sInternalTags )
				 || ($theOffset == kTAG_GID) )
					throw new \Exception(
						"Cannot modify the [$theOffset] offset: "
					   ."the object is committed." );							// !@! ==>
		
		} // Object is committed.
				
		ContainerObject::offsetUnset( (string) $theOffset );
		
		//
		// Set status.
		//
		$this->isDirty( TRUE );
	
	} // offsetUnset.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	insert																			*
	 *==================================================================================*/

	/**
	 * Insert the object
	 *
	 * This method should insert the current object into the provided persistent store.
	 *
	 * In this method we perform the following steps:
	 *
	 * <ul>
	 *	<li>We resolve the eventually provided persistent store into a collection object,
	 *		or we use the current object's collection; if this is not set, or if the
	 *		collection canot be resolved, the method will raise an exception.
	 *	<li>We call the <tt>{@link preCommit()}</tt> method that is responsible for
	 *		preparing the object for being committed.
	 *	<li>If the object is not initialised, {@link isInited()}, we raise an exception.
	 *	<li>We pass the current object to the collection's insert method and recuperate the
	 *		identifier.
	 *	<li>We call the <tt>{@link postCommit()}</tt> method that is responsible of cleaning
	 *		up the objecxt after the commit.
	 *	<li>We return the object's identifier.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 *
	 * @access public
	 * @return mixed				The object's native identifier.
	 *
	 * @throws Exception
	 */
	public function insert( $theContainer = NULL )
	{
		//
		// Handle container.
		//
		if( $theContainer !== NULL )
		{
			//
			// Resolve collection.
			//
			$theContainer = $this->resolveCollection( $theContainer );
			if( ! ($theContainer instanceof CollectionObject) )
				throw new \Exception(
					"Cannot insert object: "
				   ."invalid container parameter type." );						// !@! ==>
		
			//
			// Set collection.
			//
			$this->manageCollection( $theContainer );
		
		} // Provided persistent store.
		
		//
		// Use collection.
		//
		else
		{
			//
			// Get collection.
			//
			$theContainer = $this->manageCollection();
			if( ! ($theContainer instanceof CollectionObject) )
				throw new \Exception(
					"Cannot insert object: "
				   ."no collection provided." );								// !@! ==>
		
		} // Use current collection.
		
		//
		// Prepare object.
		//
		$this->preCommit();
		
		//
		// Check if object is ready.
		//
		if( ! $this->isInited() )
			throw new \Exception(
				"Cannot insert object: "
			   ."the object is not yet initialised." );							// !@! ==>
		
		//
		// Commit.
		//
		$id = $theContainer->insert( $this );
	
		//
		// Copy identifier if new.
		//
		if( ! $this->isCommitted() )
			$this->offsetSet( kTAG_NID, $id );
		
		//
		// Cleanup object.
		//
		$this->postCommit();
	
		//
		// Set object status.
		//
		$this->isDirty( FALSE );
		$this->isCommitted( TRUE );
		
		return $id;																	// ==>
	
	} // insert.

		

/*=======================================================================================
 *																						*
 *							PROTECTED INSTANTIATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	instantiateObject																*
	 *==================================================================================*/

	/**
	 * Instantiate object
	 *
	 * This method has been developed to provide a constructor template from the current
	 * trait. Since traits should not implement constructors, we implement this method which
	 * will return an array than can be used to instantiate the current object, all other
	 * object elements are handled in this method.
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
	 * This method will return an array which can be handed to the calling object
	 * constructor.
	 *
	 * Note that the {@link isCommitted()} flag is managed in this method and the
	 * {@link isDirty()} flag is not expected to be changed by this method.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access protected
	 * @return array				Object offsets.
	 */
	public function instantiateObject( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Instantiate empty object.
		//
		if( $theContainer === NULL )
			return Array();															// ==>
		
		//
		// Instantiate from object attributes.
		//
		elseif( $theIdentifier === NULL )
		{
			//
			// Handle array objects.
			//
			if( $theContainer instanceof \ArrayObject )
				return $theContainer->getArrayCopy();								// ==>
		
			//
			// Handle arrays.
			//
			elseif( is_array( $theContainer ) )
				return $theContainer;												// ==>
		
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
			// Resolve collection.
			//
			$collection = $this->resolveCollection( $theContainer );
			if( ! ($collection instanceof CollectionObject) )
				throw new \Exception(
					"Cannot instantiate object: "
				   ."invalid container parameter type." );						// !@! ==>
			
			//
			// Set collection.
			//
			$this->manageCollection( $collection );
			
			//
			// Resolve object.
			//
			$found = $this->resolveObject( $theIdentifier );
			
			//
			// Handle selected object.
			//
			if( $found !== NULL )
			{
				//
				// Set committed status.
				//
				$this->isCommitted( TRUE );
				
				return $found;														// ==>
				
			} // Found.
		
		} // Provided persistent store connection.
	
	} // instantiateObject.

		

/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveCollection																*
	 *==================================================================================*/

	/**
	 * Resolve the collection
	 *
	 * This method should resolve a collection object from the provided
	 * {@link ConnectionObject} parameter. This method is generally called by the
	 * constructor or by the persistence methods to select the {@link CollectionObject}
	 * instance from which the object will be retrieved, or in which the object will be
	 * stored.
	 *
	 * The provided parameter should be an instance of the {@link ConnectionObject} class,
	 * the main duty of this method is to determine the collection of the current object
	 * and return it.
	 *
	 * If the collection cannot be resolved, the method should return <tt>NULL</tt>.
	 *
	 * In this class we simply return the provided parameter if it is a
	 * {@link CollectionObject} instance, if not, we return <tt>NULL</tt>.
	 *
	 * @param ConnectionObject		$theConnection		Persistent store.
	 *
	 * @access protected
	 * @return CollectionObject		Collection or <tt>NULL</tt>.
	 */
	public function resolveCollection( ConnectionObject $theConnection )
	{
		//
		// Handle collection object.
		//
		if( $theConnection instanceof CollectionObject )
			return $theConnection;													// ==>
		
		return NULL;																// ==>
	
	} // resolveConnection.

	 
	/*===================================================================================
	 *	manageCollection																*
	 *==================================================================================*/

	/**
	 * Manage the current object's collection
	 *
	 * This method can be used to set, delete or retrieve the object's collection, which
	 * represents the object's persistent store.
	 *
	 * The value may only be modified as long as the object is not yet
	 * {@link isCommitted()}, once this status is set, this value can only be consulted.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theValue</tt>: The collection object or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the current collection.
	 *		<li><tt>FALSE</tt>: Reset the current collection to <tt>NULL</tt>.
	 *		<li><tt>{@link CollectionObject}</tt>: Set the current collection to the
	 *			provided value.
	 *		<li><em>other</em>: Any other type will raise an exception.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the collection <em>before</em> it was eventually
	 *			modified.
	 *		<li><tt>FALSE</tt>: Return the collection <em>after</em> it was eventually
	 *			modified.
	 *	 </ul>
	 * </ul>
	 *
	 * @param mixed					$theValue			Value or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access protected
	 * @return CollectionObject		Persistent store or <tt>NULL</tt>.
	 *
	 * @uses isConnected()
	 *
	 * @throws Exception
	 *
	 * @uses manageProperty()
	 */
	protected function manageCollection( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Check modifications.
		//
		if( $theValue !== NULL )
		{
			//
			// Lock if committed.
			//
			if( $this->isCommitted() )
				throw new \Exception(
					"Cannot modify collection: "
				   ."the object is committed." );								// !@! ==>
			
			//
			// Check collection type.
			//
			if( $theValue !== FALSE )
			{
				//
				// Check collection data type.
				//
				if( ! ($theValue instanceof CollectionObject) )
					throw new \Exception(
						"Cannot set collection: "
					   ."invalid or unsupported data type." );					// !@! ==>
			
			} // Not deleting.
		
		} // New collection.
		
		return $this->manageProperty( $this->mCollection, $theValue, $getOld );		// ==>
	
	} // manageCollection.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveObject																	*
	 *==================================================================================*/

	/**
	 * Resolve an object
	 *
	 * This method should select the record of the current object's collection matching the
	 * provided identifier and return the result as an array, if found, or <tt>NULL</tt> if
	 * not.
	 *
	 * The method will first check if the current object has a collection, if that is not
	 * the case, this method will raise an exception; it will then call the collection's
	 * {@link resolveIdentifier()} method that will do the actual job.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access protected
	 * @return array				Found object as an array, or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function resolveObject( $theIdentifier )
	{
		//
		// Check collection.
		//
		$collection = $this->manageCollection();
		if( $collection !== NULL )
			return $collection->resolveIdentifier( $theIdentifier );				// ==>
		
		//
		// Invalid container type.
		//
		throw new \Exception(
			"Cannot resolve object: "
		   ."no collection to search in." );									// !@! ==>
	
	} // resolveObject.

		

/*=======================================================================================
 *																						*
 *								PROTECTED COMMIT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommit																		*
	 *==================================================================================*/

	/**
	 * Prepare object for commit
	 *
	 * This method should prepare the object for being committed, it should compute the
	 * eventual identifiers and commit the eventual related objects.
	 *
	 * The method must be implemented by concrete derived classes.
	 *
	 * @access protected
	 */
	abstract protected function preCommit();

	 
	/*===================================================================================
	 *	postCommit																		*
	 *==================================================================================*/

	/**
	 * Cleanup object after commit
	 *
	 * This method should cleanup the object after it was committed, it should perform
	 * eventual iodentifiers and commit the eventual related objects.
	 *
	 * The method must be implemented by concrete derived classes.
	 *
	 * @access protected
	 */
	abstract protected function postCommit();

	 

} // class PersistentTrait.


?>
