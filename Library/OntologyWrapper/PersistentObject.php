<?php

/**
 * PersistentObject.php
 *
 * This file contains the definition of the {@link PersistentObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Wrapper;
use OntologyWrapper\OntologyObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

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
 * The main purpose of this class is to add the status and persistence traits providing the
 * prototypes needed to implement concrete persistent objects.
 *
 * The class makes use of the {@link Status} and {@link Persistence} traits:
 *
 * <ul>
 *	<li><tt>{@link Status}</tt>: This class handles a bitfirld data member that keeps
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
 *	<li><tt>{@link Persistence}</tt>: This class handles the object persistence.
 * </ul>
 *
 * Objects derived from this class <em>must</em> define a constant called <em>kSEQ_NAME</em>
 * which provides a <em<string</em> representing the <em>default collection name</em> for
 * the current object: methods that commit or read objects of a specific class can then
 * resolve the collection given a database.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/02/2014
 */
abstract class PersistentObject extends OntologyObject
{
	/**
	 * Status trait.
	 *
	 * In this class we handle the {@link is_committed()} flag.
	 */
	use	traits\Status;

		

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
	 * Objects derived from this class share the same constructor prototype, they should not
	 * overload this method. The method accepts two parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: This may either be an array containing the object's
	 *		persistent attributes, or a reference to a {@link Wrapper} object. If this
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
	 *	<li><i>Load object from container</i>: The first parameter is a {@link Wrapper}
	 *		object and the second parameter is a scalar identifier.
	 *	<li><i>Filled committed object</i>: The first parameter is {@link Wrapper} object
	 *		and the second parameter is an array holding the object's persistent data.
	 * </ul>
	 *
	 * Any other combination will raise an exception.
	 *
	 * This constructor sets the committed flag, derived classes should first call the
	 * parent constructor, then they should set the inited flag.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses isCommitted()
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Instantiate empty object.
		//
		if( $theContainer === NULL )
			parent::__construct();
		
		//
		// Instantiate from object attributes array.
		//
		elseif( is_array( $theContainer ) )
			parent::__construct( $theContainer );
		
		//
		// Instantiate from object.
		//
		elseif( ($theIdentifier === NULL)
		 && ($theContainer instanceof \ArrayObject) )
			parent::__construct( $theContainer->getArrayCopy() );
		
		//
		// Handle wrapper.
		//
		elseif( $theContainer instanceof Wrapper )
		{
			//
			// Set dictionary.
			//
			$this->dictionary( $theContainer );
			
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $theContainer, TRUE ) );
			
			//
			// Open collection.
			//
			$collection->openConnection();
			
			//
			// Load object data.
			//
			if( is_array( $theIdentifier ) )
			{
				//
				// Set committed status.
				//
				$this->isCommitted( TRUE );
				
				//
				// Call parent constructor.
				//
				parent::__construct( $theIdentifier );
				
			} // Provided data.
			
			//
			// Resolve object.
			//
			else
			{
				//
				// Find object.
				//
				$found = $collection->resolve( $theIdentifier, kTAG_NID, FALSE );
				if( $found !== NULL )
				{
					//
					// Set committed status.
					//
					$this->isCommitted( TRUE );
				
					//
					// Call parent constructor.
					//
					parent::__construct( $found );
				
				} // Found.
				
				//
				// Not found.
				//
				else
					parent::__construct();
			
			} // Provided identifier.
		
		} // Container connection.
		
		else
			throw new \Exception(
				"Cannot instantiate object: "
			   ."invalid container parameter type." );							// !@! ==>

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	commit																			*
	 *==================================================================================*/

	/**
	 * Insert the object
	 *
	 * This method should commit the current object into the provided persistent store.
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
	 *	<li>We pass the current object to the collection's commit method and recuperate the
	 *		identifier.
	 *	<li>We call the <tt>{@link postCommit()}</tt> method that is responsible of cleaning
	 *		up the objecxt after the commit.
	 *	<li>We return the object's identifier.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Persistent store.
	 *
	 * @access public
	 * @return mixed				The object's native identifier.
	 *
	 * @throws Exception
	 *
	 * @uses isDirty()
	 * @uses isCommitted()
	 * @uses dictionary()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses preCommit()
	 * @uses isReady()
	 * @uses postCommit()
	 */
	public function commit( Wrapper $theWrapper )
	{
		//
		// Do it only if the object is dirty or not committed.
		//
		if( $this->isDirty()
		 || (! $this->isCommitted()) )
		{
			//
			// Set dictionary wrapper.
			//
			$this->dictionary( $theWrapper );
			
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $theWrapper, TRUE ) );
			
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
			// Commit.
			//
			$id = $collection->commit( $this );
	
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
	
	} // commit.

		

/*=======================================================================================
 *																						*
 *								STATIC CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveDatabase																	*
	 *==================================================================================*/

	/**
	 * Resolve the database
	 *
	 * This method should return a {@link DatabaseObject} instance corresponding to the
	 * default database of the current class extracted from the provided {@link Wrapper}
	 * instance.
	 *
	 * Since we cannot declare this method abstract, we raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param boolean				$doAssert			Raise exception if unable.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return DatabaseObject		Database or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	static function ResolveDatabase( Wrapper $theWrapper, $doAssert = TRUE, $doOpen = TRUE )
	{
		throw new \Exception(
			"Unable to resolve database: "
		   ."this method must be implemented." );								// !@! ==>
	
	} // ResolveDatabase.

	 
	/*===================================================================================
	 *	ResolveCollection																*
	 *==================================================================================*/

	/**
	 * Resolve the collection
	 *
	 * This method should return a {@link CollectionObject} instance corresponding to the
	 * persistent store in which the current object was either read or will be inserted.
	 *
	 * The method expects the object to feature a constant, {@link kSEQ_NAME}, which serves
	 * the double purpose of providing the default collection name and the eventual sequence
	 * number index: the method will use this constant and the provided database reference
	 * to return the default {@link CollectionObject} instance.
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return CollectionObject		Collection or <tt>NULL</tt>.
	 */
	static function ResolveCollection( DatabaseObject $theDatabase, $doOpen = TRUE )
	{
		return $theDatabase->Collection( static::kSEQ_NAME, $doOpen );				// ==>
	
	} // ResolveCollection.

		

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
	 * the first bit is set if the object is committed and the second bit is set if we are
	 * storing the object.
	 *
	 * <ul>
	 *	<li><tt>0x01</tt>: <em>Insert</em>
	 *	 <ul>
	 *		<li>Check if the object is {@link isInited()}.
	 *		<li><tt>{@link preCommitValidate()</tt>: Validate object.
	 *		<li><tt>{@link preCommitIdentify()</tt>: Set object identifiers.
	 *		<li><tt>{@link isReady()</tt>: Check if object is ready.
	 *		<li><tt>{@link preCommitRelated()</tt>: Commit related objects.
	 *	 </ul>
	 *	<li><tt>0x11</tt>: <em>Update</em>
	 *	 <ul>
	 *		<li>Nothing yet (this operation should not be implemented).
	 *	 </ul>
	 *	<li><tt>0x10</tt>: <em>Delete</em>
	 *	 <ul>
	 *		<li>Check if the object has its native identifier, {@link kTAG_NID}.
	 *	 </ul>
	 * </ul>
	 *
	 * Derived classes should not overload this method, they should, instead, overload the
	 * called methods.
	 *
	 * @param bitfield				$theOperation		Operation code.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kTAG_NID
	 *
	 * @uses isInited()
	 */
	protected function preCommit( $theOperation = 0x00 )
	{
		//
		// Handle commit and update.
		//
		if( $theOperation & 0x01 )
		{
			//
			// Check if initialised.
			//
			if( ! $this->isInited() )
				throw new \Exception(
					"Unable to commit: "
				   ."the object is not initialised." );							// !@! ==>
			
			//
			// Validate object.
			//
			$this->preCommitValidate();
			
			//
			// Identify object.
			//
			$this->preCommitIdentify();
		
			//
			// Check if object is ready.
			//
			if( ! $this->isReady() )
				throw new \Exception(
					"Cannot commit object: "
				   ."the object is not yet initialised." );						// !@! ==>
			
			//
			// Commit related.
			//
			$this->preCommitRelated();
		
		} // Saving.
		
		//
		// Handle delete.
		//
		else
		{
			//
			// Ensure the object has its native identifier.
			//
			if( ! \ArrayObject::offsetExists( kTAG_NID ) )
				throw new \Exception(
					"Unable to delete: "
				   ."the object is missing its native identifier." );			// !@! ==>
		
		} // Deleting.
	
	} // preCommit.

	 
	/*===================================================================================
	 *	preCommitValidate																*
	 *==================================================================================*/

	/**
	 * Validate object before commit
	 *
	 * This method should validate the object before being committed.
	 *
	 * Derived classes must implement this method and should not overload the caller.
	 *
	 * @access protected
	 */
	abstract protected function preCommitValidate();

	 
	/*===================================================================================
	 *	preCommitIdentify																*
	 *==================================================================================*/

	/**
	 * Set object identifiers before commit
	 *
	 * This method should set the object identifiers.
	 *
	 * Derived classes must implement this method and should not overload the caller.
	 *
	 * @access protected
	 */
	abstract protected function preCommitIdentify();

	 
	/*===================================================================================
	 *	preCommitRelated																*
	 *==================================================================================*/

	/**
	 * Commit related objects
	 *
	 * This method should commit related objects before the current object is committed.
	 *
	 * Derived classes must implement this method and should not overload the caller.
	 *
	 * @access protected
	 */
	abstract protected function preCommitRelated();

	 
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
	 * In this class we do nothing.
	 *
	 * @param bitfield				$theOperation		Operation code.
	 *
	 * @access protected
	 */
	protected function postCommit( $theOperation = 0x00 )								   {}

		

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
	 * This method should return <tt>TRUE</tt> if the object is ready to be committed.
	 *
	 * In this class we ensure the object is initialised and that it holds the dictionary.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 */
	protected function isReady()
	{
		return ( $this->isInited()
			  && ($this->mDictionary !== NULL) );									// ==>
	
	} // isReady.

		

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
	 * @uses isCommitted()
	 * @uses InternalOffsets()
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
				if( in_array( $theOffset, $this->lockedOffsets() ) )
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
	 * @uses isCommitted()
	 * @uses lockedOffsets()
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
	 * In this class we return the list of internal tags.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @uses InternalOffsets()
	 */
	protected function lockedOffsets()				{	return $this->InternalOffsets();	}

	 

} // class PersistentObject.


?>
