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
	 *	<li>If the object is not ready, {@link isReady()}, we raise an exception.
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
	 *
	 * @uses resolveCollection()
	 * @uses preCommit()
	 * @uses isReady()
	 * @uses isCommitted()
	 * @uses postCommit()
	 * @uses isDirty()
	 * @uses isCommitted()
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
			// Open collection.
			//
			$theContainer->openConnection();
		
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
			$theContainer = $this->mCollection();
			if( ! ($theContainer instanceof CollectionObject) )
				throw new \Exception(
					"Cannot insert object: "
				   ."no collection provided." );								// !@! ==>
		
		} // Use current collection.
		
		//
		// Compute operation.
		//
		$op = 0x01;										// Signal saving.
		$op |= ( $this->isCommitted() ) ? 0x10 : 0x00;	// Signal committed.
		
		//
		// Prepare object.
		//
		$this->preCommit( $op );
		
		//
		// Check if object is ready.
		//
		if( ! $this->isReady() )
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
		$this->postCommit( $op );
	
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
	 *
	 * @throws Exception
	 *
	 * @uses resolveCollection()
	 * @uses manageCollection()
	 * @uses resolveObject()
	 * @uses isCommitted()
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
			// Open collection.
			//
			$collection->openConnection();
			
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
	 * @throws Exception
	 *
	 * @uses isCommitted()
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
		if( $this->mCollection !== NULL )
			return $this->mCollection->resolveIdentifier( $theIdentifier );			// ==>
		
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
	 * The method accepts a single bitfield parameter that indicates the current operation:
	 *
	 * <ul>
	 *	<li><tt>0x01</tt>: Insert.
	 *	<li><tt>0x11</tt>: Update.
	 *	<li><tt>0x10</tt>: Delete.
	 * </ul>
	 *
	 * The first bit is set if the object is committed and the second bit is set if we are
	 * storing the object.
	 *
	 * The method must be implemented by concrete derived classes.
	 *
	 * @param bitfield				$theOperation		Operation code.
	 *
	 * @access protected
	 */
	abstract protected function preCommit( $theOperation = 0x00 );

	 
	/*===================================================================================
	 *	postCommit																		*
	 *==================================================================================*/

	/**
	 * Cleanup object after commit
	 *
	 * This method should cleanup the object after it was committed, it should perform
	 * eventual identifiers and commit the eventual related objects.
	 *
	 * The method accepts a single bitfield parameter that indicates the current operation:
	 *
	 * <ul>
	 *	<li><tt>0x01</tt>: Insert.
	 *	<li><tt>0x11</tt>: Update.
	 *	<li><tt>0x10</tt>: Delete.
	 * </ul>
	 *
	 * The first bit is set if the object is committed and the second bit is set if we are
	 * storing the object.
	 *
	 * The method must be implemented by concrete derived classes.
	 *
	 * @param bitfield				$theOperation		Operation code.
	 *
	 * @access protected
	 */
	abstract protected function postCommit( $theOperation = 0x00 );

		

/*=======================================================================================
 *																						*
 *								PROTECTED STATUS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isReady																			*
	 *==================================================================================*/

	/**
	 * Check if object is ready
	 *
	 * This method should return <tt>TRUE</tt> if the object holds all the necessary
	 * attributes.
	 *
	 * All derived classes must implement this method.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 */
	abstract protected function isReady();

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before setting it
	 *
	 * We overload this method to prevent modifying the global and native identifiers if the
	 * object is committed, {@link isCommitted()}.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @see self::$sInternalTags
	 *
	 * @uses isCommitted()
	 */
	protected function preOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Resolve offset.
		//
		$ok = parent::preOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
		{
			//
			// Check if committed.
			//
			if( $this->isCommitted() )
			{
				//
				// Check immutable tags.
				//
				if( in_array( $theOffset, static::$sInternalTags )
				 || ($theOffset == kTAG_PID) )
					throw new \Exception(
						"Cannot set the [$theOffset] offset: "
					   ."the object is committed." );							// !@! ==>
		
			} // Object is committed.
		
		} // Intercepted by preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * We overload the parent method to set the {@link isDirty()} status.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @uses isDirty()
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Resolve offset.
		//
		$ok = parent::postOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
			$this->isDirty( TRUE );
		
		return $ok;																	// ==>
		
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	preOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before deleting it
	 *
	 * We overload this method to prevent modifying the global and native identifiers if the
	 * object is committed, {@link isCommitted()}.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> delete offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @see self::$sInternalTags
	 *
	 * @uses isCommitted()
	 */
	protected function preOffsetUnset( &$theOffset )
	{
		//
		// Resolve offset.
		//
		$ok = parent::preOffsetUnset( $theOffset );
		if( $ok === NULL )
		{
			//
			// Check if committed.
			//
			if( $this->isCommitted() )
			{
				//
				// Check immutable tags.
				//
				if( in_array( $theOffset, static::$sInternalTags )
				 || ($theOffset == kTAG_PID) )
					throw new \Exception(
						"Cannot delete the [$theOffset] offset: "
					   ."the object is committed." );							// !@! ==>
		
			} // Object is committed.
		
		} // Intercepted by preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetUnset.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * This method can be used to manage the object after calling the
	 * {@link ArrayObject::OffsetUnset()} method.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @uses isDirty()
	 */
	protected function postOffsetUnset( &$theOffset )
	{
		//
		// Resolve offset.
		//
		$ok = parent::postOffsetUnset( $theOffset );
		if( $ok === NULL )
			$this->isDirty( TRUE );
		
		return $ok;																	// ==>
		
	} // postOffsetUnset.

	 

} // class PersistentTrait.


?>
