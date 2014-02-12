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
		// Do it only if the object is dirty or not committed.
		//
		if( $this->isDirty()
		 || (! $this->isCommitted()) )
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
					   ."invalid container parameter type." );					// !@! ==>
			
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
				$theContainer = $this->mCollection;
				if( ! ($theContainer instanceof CollectionObject) )
					throw new \Exception(
						"Cannot insert object: "
					   ."no collection provided." );							// !@! ==>
		
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
				   ."the object is not yet initialised." );						// !@! ==>
		
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
		
		} // Dirty or not committed.
		
		return $this->offsetGet( kTAG_NID );										// ==>
	
	} // insert.

		

/*=======================================================================================
 *																						*
 *							PUBLIC OBJECT AGGREGATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	collectReferences																*
	 *==================================================================================*/

	/**
	 * Collect references
	 *
	 * This method should resolve and collect all the current object's references into the
	 * provided array reference. The array is divided into a series of sub-arrays with an
	 * offset corresponding to the relative class {@link kSEQ_NAME} constant Each of these
	 * sub-arrays will contain the list of objects with the offset corresponding to the
	 * object native identifier.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: Receives objects.
	 *	<li><b>$doObject</b>: If <tt>TRUE</tt>, the data will be loaded as objects, if
	 *		<tt>FALSE</tt>, as arrays.
	 * </ul>
	 *
	 * If the current object is not committed, or it doesn't feature the collection, or any
	 * reference cannot be resolved, the method should raise an exception.
	 *
	 * Derived classes should implement the method.
	 *
	 * @param reference				$theContainer		Receives objects.
	 * @param boolean				$doObject			<tt>TRUE</tt> load objects.
	 *
	 * @access public
	 */
	abstract public function collectReferences( &$theContainer, $doObject = TRUE );

		

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
	 * The method accepts two parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: This may be either an array containing the object's
	 *		persistent attributes, or a reference to a persistent connection. If this
	 *		parameter is <tt>NULL</tt>, the next parameter will be ignored.
	 *	<li><b>$theIdentifier</b>: This parameter represents the object identifier or the
	 *		object persistent attributes: in the first case it will used to select the
	 *		object from the provided container, in the second case, it is assumed that the
	 *		provided array holds the persistent attributes of an object committed in the
	 *		provided container.
	 * </ul>
	 *
	 * The workflow is as follows:
	 *
	 * <ul>
	 *	<li><i>Empty object</i>: Both parameters are omitted.
	 *	<li><i>Filled non committed object</i>: The first parameter is an array.
	 *	<li><i>Load object from container</i>: The first parameter is connection object and
	 *		the second object is a scalar identifier.
	 *	<li><i>Filled committed object</i>: The first parameter is connection object and the
	 *		second parameter is an array holding the object's persistent data.
	 * </ul>
	 *
	 * Any other combination will raise an exception.
	 *
	 * This method will return an array which can be handed to the calling object's
	 * constructor.
	 *
	 * Note that the {@link isCommitted()} flag is managed in this method and the
	 * {@link isDirty()} flag is not expected to be changed by this method.
	 *
	 * @param mixed					$theContainer		Persistent store or data.
	 * @param mixed					$theIdentifier		Object identifier or data.
	 *
	 * @access protected
	 * @return array				Object offsets.
	 *
	 * @throws Exception
	 *
	 * @uses resolveCollection()
	 * @uses manageCollection()
	 * @uses isCommitted()
	 */
	protected function instantiateObject( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Instantiate empty object.
		//
		if( $theContainer === NULL )
			return Array();															// ==>
		
		//
		// Instantiate from object attributes array.
		//
		if( is_array( $theContainer ) )
			return $theContainer;													// ==>
		
		//
		// Instantiate from object.
		//
		if( ($theIdentifier === NULL)
		 && ($theContainer instanceof \ArrayObject) )
			return $theContainer->getArrayCopy();									// ==>
		
		//
		// Handle container.
		//
		if( $theContainer instanceof ConnectionObject )
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
			// Load object data.
			//
			if( is_array( $theIdentifier ) )
			{
				//
				// Set committed status.
				//
				$this->isCommitted( TRUE );
				
				return $theIdentifier;												// ==>
				
			} // Provided data.
			
			//
			// Resolve object.
			//
			$found = $this->mCollection->resolve( $theIdentifier, kTAG_NID, FALSE );
			if( $found !== NULL )
			{
				//
				// Set committed status.
				//
				$this->isCommitted( TRUE );
				
				return $found;														// ==>
				
			} // Found.
			
			return Array();															// ==>
		
		} // Container connection.
		
		throw new \Exception(
			"Cannot instantiate object: "
		   ."invalid container parameter type." );								// !@! ==>
	
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
	 * If the collection cannot be resolved, the method should raise an exception.
	 *
	 * In this trait we assume all using objects implement a constant, {@link kSEQ_NAME},
	 * which serves the double purpose of providing the default database name and the
	 * eventual sequence number index.
	 *
	 * @param ConnectionObject		$theConnection		Persistent store.
	 *
	 * @access protected
	 * @return CollectionObject		Collection or <tt>NULL</tt>.
	 */
	protected function resolveCollection( ConnectionObject $theConnection )
	{
		//
		// Handle collection.
		//
		if( $theConnection instanceof CollectionObject )
			return $theConnection;													// ==>
		
		//
		// Handle databases.
		//
		if( $theConnection instanceof DatabaseObject )
			return $theConnection->Collection( static::kSEQ_NAME );					// ==>
		
		throw new \Exception(
			"Invalid or unsupported connection." );								// !@! ==>
	
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
				if( in_array( $theOffset, $this->lockedOffsets() ) )
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

		

/*=======================================================================================
 *																						*
 *							PROTECTED OFFSET STATUS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	lockedOffsets																	*
	 *==================================================================================*/

	/**
	 * Return list of locked offsets
	 *
	 * This method should return the list of locked offsets, that is, the offsets which
	 * cannot be modified once the object has been committed.
	 *
	 * Derived classes must implement this method.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_PID
	 * @see self::$sInternalTags
	 */
	abstract protected function lockedOffsets();

		

/*=======================================================================================
 *																						*
 *							PROTECTED OBJECT AGGREGATION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	collectObjects																	*
	 *==================================================================================*/

	/**
	 * Collect objects
	 *
	 * This method will resolve and collect the provided list of object references loading
	 * them into the provided array reference, objects will be indexed by native identifier
	 * and existing objects will not be resolved.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: Receives objects.
	 *	<li><b>$theConnection</b>: Objects collection.
	 *	<li><b>$theReferences</b>: Either a list of references or a scalar reference.
	 *	<li><b>$theClass</b>: A string corresponding to the container offset that will
	 *		receive the list of objects.
	 *	<li><b>$doObject</b>: If <tt>TRUE</tt>, the data will be loaded as objects, if
	 *		<tt>FALSE</tt>, as arrays.
	 * </ul>
	 *
	 * @param reference				$theContainer		Receives objects.
	 * @param CollectionObject		$theCollection		Objects collection.
	 * @param array					$theReferences		Object references.
	 * @param string				$theClass			Sub-container index.
	 * @param boolean				$doObject			<tt>TRUE</tt> load objects.
	 *
	 * @access protected
	 */
	protected function collectObjects( &$theContainer, CollectionObject  $theCollection,
										   								 $theReferences,
										   								 $theClass,
										   								 $doObject = TRUE )
	{
		//
		// Init array.
		//
		if( ! is_array( $theContainer ) )
			$theContainer = Array();
		
		//
		// Init objects list.
		//
		if( ! array_key_exists( $theClass, $theContainer ) )
			$theContainer[ $theClass ] = Array();
		
		//
		// Normalise references.
		//
		if( ! is_array( $theReferences ) )
			$theReferences = array( $theReferences );
		
		//
		// Iterate references.
		//
		$ref = & $theContainer[ $theClass ];
		foreach( $theReferences as $reference )
		{
			//
			// Check if there.
			//
			if( ! array_key_exists( $reference, $ref ) )
			{
				//
				// Resolve object.
				//
				$tmp = $theCollection->resolve( $reference );
				if( $tmp === NULL )
					throw new \Exception(
						"Unable to resolve [$reference] object." );				// !@! ==>
			
				//
				// Load result.
				//
				$ref[ $reference ] = ( $doObject )
								   ? $tmp
								   : $tmp->getArrayCopy();
			
			} // New object.
		
		} // Iterating references.
	
	} // collectObjects.

	 

} // class PersistentTrait.


?>
