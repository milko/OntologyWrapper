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
	 *		object from the wrapper provided in the previous parameter, in the second case,
	 *		it is assumed that the provided array holds the persistent attributes of an
	 *		object committed in the provided container.
	 * </ul>
	 *
	 * The workflow is as follows:
	 *
	 * <ul>
	 *	<li><i>Empty object</i>: Both parameters are omitted.
	 *	<li><i>Empty object with wrapper</i>: The first parameter is a {@link Wrapper}
	 *		object and the second parameter is omitted.
	 *	<li><i>Filled non committed object</i>: The first parameter is an array.
	 *	<li><i>Filled committed object</i>: The first parameter is {@link Wrapper} object
	 *		and the second parameter is an array holding the object's persistent data.
	 *	<li><i>Load object from container</i>: The first parameter is a {@link Wrapper}
	 *		object and the second parameter is a scalar identifier.
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
	 * @uses dictionary()
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
		 && ($theContainer instanceof \ArrayObject)
		 && (! ($theContainer instanceof Wrapper)) )
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
 *							PUBLIC OBJECT STRUCTURE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	collectProperties																*
	 *==================================================================================*/

	/**
	 * Collect object properties
	 *
	 * This method will traverse the object and return the set of all tag sequence numbers
	 * referenced by offsets in the current object and the list of all referenced objects.
	 *
	 * The two provided reference parameters will be initialised by this method only if they
	 * are not alreadt an array.
	 *
	 * @param reference				$theTags			Receives tags set.
	 * @param reference				$theRefs			Receives object references.
	 * @param boolean				$doSubOffsets		Include sub-offsets.
	 *
	 * @access public
	 * @return array				List of property tag references.
	 *
	 * @uses traverseProperty()
	 */
	public function collectProperties( &$theTags, &$theRefs, $doSubOffsets = FALSE )
	{
		//
		// Init tags.
		//
		if( ! is_array( $theTags ) )
			$theTags = Array();
		
		//
		// Init references.
		//
		if( ! is_array( $theRefs ) )
			$theRefs = Array();
		
		//
		// Traverse object.
		//
		$iterator = $this->getIterator();
		iterator_apply( $iterator,
						array( $this, 'traverseProperty' ),
						array( $iterator, & $theTags, & $theRefs, $doSubOffsets ) );
	
	} // collectProperties.

		

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
	 *	<li>We pass the current object to the collection's commit method and recuperate the
	 *		identifier.
	 *	<li>We call the <tt>{@link postCommit()}</tt> method that is responsible of cleaning
	 *		up the objecxt after the commit.
	 *	<li>We return the object's identifier.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * The parameter to this method may be omitted if you instantiated the object by
	 * providing the wrapper.
	 *
	 * @param Wrapper				$theWrapper			Persistent store.
	 *
	 * @access public
	 * @return mixed				The object's native identifier.
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 * @uses dictionary()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses preCommit()
	 * @uses postCommit()
	 * @uses isDirty()
	 */
	public function commit( $theWrapper = NULL )
	{
		//
		// Do it only if the object is not committed.
		//
		if( ! $this->isCommitted() )
		{
			//
			// Handle wrapper.
			//
			if( $theWrapper !== NULL )
			{
				//
				// Check wrapper.
				//
				if( ! ($theWrapper instanceof Wrapper) )
					throw new \Exception(
						"Cannot commit object: "
					   ."invalid wrapper parameter type." );					// !@! ==>
				
				//
				// Set dictionary wrapper.
				//
				$this->dictionary( $theWrapper );
			
			} // Provided wrapper
			
			//
			// Use existing wrapper.
			//
			elseif( ! ($this->dictionary() instanceof Wrapper) )
				throw new \Exception(
					"Cannot commit object: "
				   ."the object is missing its wrapper." );						// !@! ==>
			
			//
			// Set wrapper.
			//
			else
				$theWrapper = $this->dictionary();
			
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $theWrapper, TRUE ) );
		
			//
			// Prepare object.
			//
			$this->preCommit( $tags, $references );
		
			//
			// Commit.
			//
			$id = $collection->commit( $this );
	
			//
			// Copy identifier if generated.
			//
			if( ! $this->offsetExists( kTAG_NID ) )
				$this->offsetSet( kTAG_NID, $id );
		
			//
			// Cleanup object.
			//
			$this->postCommit( $tags, $references );
	
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
	 * @uses lockedOffsets()
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
 *								PROTECTED PRE-COMMIT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommit																		*
	 *==================================================================================*/

	/**
	 * Prepare object for commit
	 *
	 * This method should prepare the object for being committed, the method will perform
	 * the following steps:
	 *
	 * <ul>
	 *	<li><tt>{@link preCommitPrepare()}</tt>: This method should prepare the object and
	 *		perform preliminary initialisation of the traversal data.
	 *	<li><tt>{@link preCommitTraverse()}</tt>: This method will traverse the object's
	 *		structure and eventual sub-structures validating and casting data properties and
	 *		collecting structure data which will be used by other commit phase methods to
	 *		ensure the object is fit for being committed.
	 *	<li><tt>{@link preCommitFinalise()}</tt>: This method should finalise the pre-commit
	 *		phase ensuring the object holds all the correct and necessary data.
	 *	<li><tt>{@link isReady()}</tt>: The final step of the pre-commit phase is to test
	 *		whether the object is ready to be committed.
	 * </ul>
	 *
	 * The method accepts two reference parameters which will be initialised by the
	 * {@link preCommitPrepare()} method, will be filled by the {@link preCommitTraverse()}
	 * method and will be passed to the {@link preCommitFinalise()} method:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This parameter collects all the leaf offsets of the object, it
	 *		is a set of tags, their data type and kind, and their relative offsets. The
	 *		array is a list of elements structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The tag sequence number (or current offset).
	 *		<li><tt>value</tt>: An array collecting all the relevant information about that
	 *			tag, each element of the array is structured as follows:
	 *		 <ul>
	 *			<li><tt>type</tt>: The item indexed by this key will contain the list of
	 *				data types of the tag.
	 *			<li><tt>kind</tt>: The item indexed by this key will contain the list of
	 *				data kinds of the tag.
	 *			<li><tt>offset</tt>: The item indexed by this key will contain the list of
	 *				all the offsets (obtained from the path at the current level) where the
	 *				current tag is featured as leaf node.
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theRefs</b>: This parameter collects all the object references featured in
	 *		the current object. It is an array of elements structured as folloes:
	 *	 <ul>
	 *		<li><tt>key</tt>: The referenced object's collection name.
	 *		<li><tt>value</tt>: An array collecting all the object identifiers and reference
	 *			counts for the collection indicated in the key, each element is an array
	 *			structured as follows:
	 *		 <ul>
	 *			<li><tt>id</tt>. The item indexed by this key contains the object native
	 *				identifier.
	 *			<li><tt>count</tt>. The item indexed by this key contains the number of
	 *				times the target object was referenced by the current object.
	 *		 </ul>
	 *	 </ul>
	 * </ul>
	 *
	 * These parameter will be initialised by the {@link preCommitPrepare()} method.
	 *
	 * Derived classes should not overload this method, they should, instead, overload the
	 * called methods.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses preCommitPrepare()
	 * @uses preCommitTraverse()
	 * @uses preCommitFinalise()
	 * @uses isReady()
	 */
	protected function preCommit( &$theTags, &$theRefs )
	{
		//
		// Prepare object.
		//
		$this->preCommitPrepare( $theTags, $theRefs );
	
		//
		// Traverse object.
		//
		$this->preCommitTraverse( $theTags, $theRefs );
		
		//
		// Finalise object.
		//
		$this->preCommitFinalise( $theTags, $theRefs );
	
		//
		// Check if object is ready.
		//
		if( ! $this->isReady() )
			throw new \Exception(
				"Cannot commit object: "
			   ."the object is not ready." );									// !@! ==>
	
	} // preCommit.

	 
	/*===================================================================================
	 *	preCommitPrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before commit
	 *
	 * This method will first perform global preliminary checks to ensure the object is fit
	 * for the pre-commit phase, if this is not the case, the method will raise an
	 * exception. In the current class we check if the object is {@link isInited()}.
	 *
	 * The second task of this method is to initialise the parameters that will be passed to
	 * the other methods involved in committing the object, both parameters will be set as
	 * empty arrays.
	 *
	 * Derived classes that wish to add actions to this phase should perform:
	 *
	 * <ul>
	 *	<li><em>Perform global preliminary validation.</em> Perform any check of global
	 *		scope that might prevent the object from being committed.
	 *	<li>Call the parent method.</em> This will ensure default preliminary validation and
	 *		initialisation of the parameter.
	 *	<li><em>Add custom elements to the traversal parameter.</em> If derived classes need
	 *		to pass additional data to the commit process, they can initialise it after
	 *		having called the parent method.
	 * </ul>
	 *
	 * In this class we check whether the object is initialised and initialise the data
	 * parameter passed to the method.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses isInited()
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Check if initialised.
		//
		if( ! $this->isInited() )
			throw new \Exception(
				"Unable to commit: "
			   ."the object is not initialised." );								// !@! ==>
		
		//
		// Initialise tags set.
		//
		if( ! is_array( $theTags ) )
			$theTags = Array();
		
		//
		// Initialise object references.
		//
		if( ! is_array( $theRefs ) )
			$theRefs = Array();
	
	} // preCommitPrepare.

		
	/*===================================================================================
	 *	preCommitTraverse																*
	 *==================================================================================*/

	/**
	 * Traverse object before commit
	 *
	 * This method will apply the {@link traverseStructure()} method to the object's
	 * persistent data iterator, the aforementioned method will be called for each offset
	 * of the object and will be recursed for each sub-structure of the object.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This parameter collects all the leaf offsets of the object, it
	 *		is a set of tags and their relative offsets. The array is a list of elements
	 *		structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The tag sequence number (or current offset).
	 *		<li><tt>value</tt>: An array collecting all the relevant information about that
	 *			tag, each element of the array is structured as follows:
	 *		 <ul>
	 *			<li><tt>type</tt>: The item indexed by this key will contain the list of
	 *				data types of the tag.
	 *			<li><tt>kind</tt>: The item indexed by this key will contain the list of
	 *				data kinds of the tag.
	 *			<li><tt>offset</tt>: The item indexed by this key will contain the list of
	 *				all the offsets (obtained from the path at the current level) where the
	 *				current tag is featured as leaf node.
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theRefs</b>: This parameter collects all the object references featured in
	 *		the current object. It is an array of elements structured as folloes:
	 *	 <ul>
	 *		<li><tt>key</tt>: The referenced object's collection name.
	 *		<li><tt>value</tt>: An array collecting all the object identifiers and reference
	 *			counts for the collection indicated in the key, each element is an array
	 *			structured as follows:
	 *		 <ul>
	 *			<li><tt>id</tt>. The item indexed by this key contains the object native
	 *				identifier.
	 *			<li><tt>count</tt>. The item indexed by this key contains the number of
	 *				times the target object was referenced by the current object.
	 *		 </ul>
	 *	 </ul>
	 * </ul>
	 *
	 * The method expects all parameters, to have been initialised.
	 *
	 * This method should not be overloaded by derived classes, rather, the methods called
	 * by the {@link traverseStructure()} method can be extended to provided custom
	 * validation or casting.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses traverseStructure()
	 */
	protected function preCommitTraverse( &$theTags, &$theRefs )
	{
		//
		// Init path.
		//
		$path = Array();
		
		//
		// Traverse object.
		//
		$iterator = $this->getIterator();
		iterator_apply( $iterator,
						array( $this, 'traverseStructure' ),
						array( $iterator, & $path, & $theTags, & $theRefs ) );
	
	} // preCommitTraverse.

	 
	/*===================================================================================
	 *	preCommitFinalise																*
	 *==================================================================================*/

	/**
	 * Finalise object before commit
	 *
	 * This method will be called before checking if the object is ready, {@link isReady()},
	 * its duty is to make the last preparations before the object is to be committed.
	 *
	 * The method calls two other methods:
	 *
	 * <ul>
	 *	<li><tt>{@link preCommitObjectTags()}</tt>: This method will iterate through all the
	 *		elements of the tags parameter and feed each one to the {@link loadObjectTag()}
	 *		method which will populate the {@link kTAG_OBJECT_TAGS} object property.
	 *	<li><tt>{@link preCommitObjectIdentifiers()}</tt>: This method is responsible for
	 *		setting the object's identifiers.
	 * </ul>
	 *
	 * Derived classes should only overload this method if there is the need to perform
	 * another main operation.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses preCommitObjectTags()
	 * @uses preCommitObjectIdentifiers()
	 */
	protected function preCommitFinalise( &$theTags, &$theRefs )
	{
		//
		// Load object tags.
		//
		$this->preCommitObjectTags( $theTags );
	
		//
		// Load object identifiers.
		//
		$this->preCommitObjectIdentifiers();
	
	} // preCommitFinalise.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitObjectTags																*
	 *==================================================================================*/

	/**
	 * Load object tags
	 *
	 * This method will collect the offset tags set from the tags parameter and populate the
	 * {@link kTAG_OBJECT_TAGS} offset.
	 *
	 * Derived classes should only overload this method if that offset should not be set,
	 * if not, they should overload the {@link loadObjectTag()} method which is called for
	 * each collected tag to filter which elements will be set in the offset.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 *
	 * @access protected
	 *
	 * @see kTAG_OBJECT_TAGS
	 *
	 * @uses loadObjectTag()
	 */
	protected function preCommitObjectTags( &$theTags )
	{
		//
		// Init local storage.
		//
		$tags = Array();
		
		//
		// Iterate tags.
		//
		foreach( $theTags as $tag => $info )
			$this->loadObjectTag( $tag, $info, $tags );
		
		//
		// Set offset.
		//
		if( count( $tags ) )
			$this->offsetSet( kTAG_OBJECT_TAGS, $tags );
	
	} // preCommitObjectTags.

	 
	/*===================================================================================
	 *	preCommitObjectIdentifiers														*
	 *==================================================================================*/

	/**
	 * Load object identifiers
	 *
	 * This method should load the object identifiers.
	 *
	 * In this class we do nothing, in derived classes you can overload this method if you
	 * need to compute identifiers.
	 *
	 * @access protected
	 */
	protected function preCommitObjectIdentifiers()										   {}

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-COMMIT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postCommit																		*
	 *==================================================================================*/

	/**
	 * Handle object after commit
	 *
	 * This method is called immediately after the object is committed, its duty is to
	 * handle the object after it was committed and to handle related objects.
	 *
	 * In this class we do the following:
	 *
	 * <ul>
	 *	<li><tt>{@link postCommitRefCount()}</tt>: We update the reference counts of all
	 *		objects referenced by the current object.
	 *	<li><tt>{@link postCommitTagOffsets()}</tt>: We update the offsets of all tags used in
	 *		the current object.
	 * </ul>
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses postCommitRefCount()
	 * @uses postCommitTagOffsets()
	 */
	protected function postCommit( &$theTags, &$theRefs )
	{
		//
		// Update reference counts.
		//
		$this->postCommitRefCount( $theRefs );
	
		//
		// Update tag offsets.
		//
		$this->postCommitTagOffsets( $theTags );
	
	} // postCommit.

	 
	/*===================================================================================
	 *	postCommitRefCount																*
	 *==================================================================================*/

	/**
	 * Update reference counts
	 *
	 * This method will update the reference counts of all objects referenced by the current
	 * one.
	 *
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses updateReferenceCount()
	 */
	protected function postCommitRefCount( &$theRefs )
	{
		//
		// Iterate by collection.
		//
		foreach( $theRefs as $collection => $references )
		{
			//
			// Iterate references.
			//
			foreach( $references as $reference )
				$this->updateReferenceCount(
					$collection, $reference[ 'id' ], $reference[ 'count' ] );
		
		} // Iterating collections.
	
	} // postCommitRefCount.

	 
	/*===================================================================================
	 *	postCommitTagOffsets															*
	 *==================================================================================*/

	/**
	 * Update tag offsets
	 *
	 * This method will update the offsets list of all tags used in the current object.
	 *
	 * Note that this method expects the {@link dictionary()} to be there.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 *
	 * @access protected
	 */
	protected function postCommitTagOffsets( &$theTags )
	{
		//
		// Resolve collection.
		//
		$collection
			= Tag::ResolveCollection(
				Tag::ResolveDatabase( $this->dictionary(), TRUE ) );
		
		//
		// Get tag identifiers.
		//
		$tags = array_keys( $theTags );
		
		//
		// Iterate tag elements.
		//
		foreach( $tags as $tag )
		{
			//
			// Reference info.
			//
			$ref = & $theTags[ $tag ];
			
			//
			// Update tag offsets.
			//
			$collection->updateTagOffsets( (int) $tag, $ref[ 'offset' ] );
		}
	
	} // postCommitTagOffsets.

	

/*=======================================================================================
 *																						*
 *							PROTECTED OBJECT TRAVERSAL INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	traverseStructure																*
	 *==================================================================================*/

	/**
	 * Traverse structure
	 *
	 * This method's duty is to validate and normalise an offset of the current object, the
	 * method will be called for each offset at the root or sub-structure level of the
	 * current object. This means that this method will not be called for elements of a list
	 * offset, kind {@link kTYPE_LIST}.
	 *
	 * The method is passed a series of reference parameters that will be populated as this
	 * method traverses the object's structure, this data will be then used in the commit
	 * workflow to perform tasks related to referenced objects and statistical information.
	 *
	 * These parameters are:
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter contains the element currently pointed to by
	 *		the iterator. This iterator is not recursive, each time a sub-structure is
	 *		encountered, a new iterator is generated and this method is handed over.
	 *	<li><b>$thePath</b>: This run-time parameter contains the path to the current
	 *		iterator element represented by a list of offsets, starting from the root
	 *		offset and ending with the offset at the current depth. The current iterator
	 *		element's offset is pushed at entry and popped at exit.
	 *	<li><b>$theTags</b>: This parameter collects all the leaf offsets of the object, it
	 *		represents the set of tag sequence numbers used as offsets in the current
	 *		object, the parameter is an array of elements structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The tag sequence number (or current offset).
	 *		<li><tt>value</tt>: An array collecting all the relevant information about that
	 *			tag, each element of the array is structured as follows:
	 *		 <ul>
	 *			<li><tt>type</tt>: The item indexed by this key will contain the list of
	 *				data types of the tag.
	 *			<li><tt>kind</tt>: The item indexed by this key will contain the list of
	 *				data kinds of the tag.
	 *			<li><tt>offset</tt>: The item indexed by this key will contain the list of
	 *				all the offsets (obtained from the path at the current level) where the
	 *				current tag is featured as leaf node.
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theRefs</b>: This parameter collects all the object references featured in
	 *		the current object. It is an array of elements structured as folloes:
	 *	 <ul>
	 *		<li><tt>key</tt>: The referenced object's collection name.
	 *		<li><tt>value</tt>: An array collecting all the object identifiers and reference
	 *			counts for the collection indicated in the key, each element is an array
	 *			structured as follows:
	 *		 <ul>
	 *			<li><tt>id</tt>. The item indexed by this key contains the object native
	 *				identifier.
	 *			<li><tt>count</tt>. The item indexed by this key contains the number of
	 *				times the target object was referenced by the current object.
	 *		 </ul>
	 *	 </ul>
	 * </ul>
	 *
	 * The method will perform the following steps:
	 *
	 * <ul>
	 *	<li><em>Push the current offset to the path</em>: The current offset will be
	 *		appended to the path parameter.
	 *	<li><em>Collect offset tag information</em>: The {@link collectOffsetInformation()}
	 *		method will determine the types and kinds of the current offset value and update
	 *		the tags parameter. If the current offset is an internal offset, all steps
	 *		except the last one will be skipped.
	 *	<li><em>Verify offset structure</em>: The {@link verifyOffsetStructure()} method
	 *		will check if the current element value has the correct structure.
	 *	<li><em>Verify and cast value</em>: If the current offset type is not a structure,
	 *		{@link kTYPE_STRUCT}, the {@link traverseValue()} method will be used to verify
	 *		the offset value and cast it to the correct data type.
	 *	<li><em>Recurse structures</em>: If the current element is a structure, its elements
	 *		will be iterated and handed to this method. Structure lists will recursively be
	 *		iterated.
	 *	<li><em>Scan lists</em>: If the current element is a list of scalar elements, each
	 *		element of the list will be handed to the {@link traverseValue()} method which
	 *		will take care of validating and casting the value.
	 *	<li><em>Pop offset from path</em>: The current offset will be popped from the path
	 *		parameter.
	 * </ul>
	 *
	 * This method is final, derived classes should only need to overload the methods called
	 * by this one.
	 *
	 * This method is used by the PHP {@link iterator_apply()} method, which means that it
	 * should return <tt>TRUE</tt> to continue the object traversal, or <tt>FALSE</tt> to
	 * stop it: it will return <tt>TRUE</tt> by default.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$thePath			Offsets path.
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt>, or <tt>FALSE</tt> to stop the traversal.
	 *
	 * @uses collectOffsetInformation()
	 * @uses verifyOffsetStructure()
	 * @uses traverseValue()
	 */
	final protected function traverseStructure( \Iterator $theIterator, &$thePath,
																		&$theTags,
																		&$theRefs )
	{
		//
		// Push to path.
		//
		$thePath[] = $theIterator->key();
		
		//
		// Collect offset information.
		//
		$offset
			= $this->collectOffsetInformation(
				$theIterator, $thePath, $theTags, $type, $kind );
		
		//
		// Skip internal offsets.
		//
		if( $offset !== NULL )
		{
			//
			// Verify offset structure.
			//
			$this->verifyOffsetStructure( $theIterator, $type, $kind, $offset );
			
			//
			// Handle scalar offset.
			//
			if( (! in_array( kTYPE_LIST, $kind ))
			 && (! in_array( kTYPE_STRUCT, $type )) )
				$this->traverseValue( $theIterator, $theRefs, $type, $kind, $offset );
		
			//
			// Handle structure and list offsets.
			//
			else
			{
				//
				// Save list or structure.
				//
				$list = new \ArrayObject( $theIterator->current() );
		
				//
				// Handle structure.
				//
				if( in_array( kTYPE_STRUCT, $type ) )
				{
					//
					// Handle structure lists.
					//
					if( in_array( kTYPE_LIST, $kind ) )
					{
						//
						// Iterate list.
						//
						foreach( $list as $idx => $struct )
						{
							//
							// Traverse structure.
							//
							$struct = new \ArrayObject( $struct );
							$iterator = $struct->getIterator();
							iterator_apply( $iterator,
											array( $this, 'traverseStructure' ),
											array( $iterator, & $thePath,
															  & $theTags,
															  & $theRefs ) );
		
							//
							// Update structure.
							//
							if( $struct->count() )
								$list[ $idx ] = $struct->getArrayCopy();
				
						} // Iterating list.
			
					} // List of structures.
			
					//
					// Handle scalar structure.
					//
					else
					{
						//
						// Traverse structure.
						//
						$iterator = $list->getIterator();
						iterator_apply( $iterator,
										array( $this, 'traverseStructure' ),
										array( $iterator, & $thePath,
														  & $theTags,
														  & $theRefs ) );
			
					} // Scalar structure.
		
				} // Structure.
			
				//
				// Handle list of scalars.
				//
				else
				{
					//
					// Iterate scalar list.
					//
					$iterator = $list->getIterator();
					iterator_apply( $iterator,
									array( $this, 'traverseValue' ),
									array( $iterator, & $theRefs,
													  & $type,
													  & $kind,
													  & $offset ) );
			
				} // List of scalars.

				//
				// Update current iterator value.
				//
				$theIterator->offsetSet( $theIterator->key(), $list->getArrayCopy() );
		
			} // Structured offset.
		
		} // Not an internal offset.
		
		//
		// Pop from path.
		//
		array_pop( $thePath );
		
		return TRUE;																// ==>
	
	} // traverseStructure.

	 
	/*===================================================================================
	 *	traverseProperty																*
	 *==================================================================================*/

	/**
	 * Traverse properties
	 *
	 * This method will be called for each element of the object structure, it will add to
	 * the provided parameter the set of all tags used by the object as offsets.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This parameter will collect all the tags referenced by offsets
	 *		in the object. This is an array containing the set of tag sequence numbers.
	 *	<li><b>$theRefs</b>: This parameter collects all the object references featured in
	 *		the current object. It is an array of elements structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The referenced object's collection name.
	 *		<li><tt>value</tt>: An array collecting all the referenced object's native
	 *			identifiers for the collection indicated in the key.
	 *	 </ul>
	 *	<li><b>$doSubOffsets</b>: This flag indicates whether to add sub-structure tags to
	 *		the tags list.
	 * </ul>
	 *
	 * This method is used by the PHP {@link iterator_apply()} method, which means that it
	 * should return <tt>TRUE</tt> to continue the object traversal, or <tt>FALSE</tt> to
	 * stop it.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theTags			Receives tags set.
	 * @param reference				$theRefs			Receives object references.
	 * @param boolean				$doSubOffsets		Include sub-offsets.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt>, or <tt>FALSE</tt> to stop the traversal.
	 *
	 * @uses InternalOffsets()
	 * @uses getOffsetTypes()
	 * @uses loadPropertyReferences()
	 * @uses loadSubProperties()
	 */
	protected function traverseProperty( \Iterator $theIterator,
													&$theTags,
													&$theRefs,
													$doSubOffsets = TRUE )
	{
		//
		// Init local storage.
		//
		$key = $theIterator->key();
		
		//
		// Skip internal offsets.
		//
		if( ! in_array( $key, static::InternalOffsets() ) )
		{
			//
			// Add to set.
			//
			if( ! in_array( (int) $key, $theTags ) )
				$theTags[] = (int) $key;
		
			//
			// Collect offset types.
			//
			$this->getOffsetTypes( $key, $type, $kind );
		
			//
			// Save list or structure.
			//
			$list = $theIterator->current();
	
			//
			// Handle structure offsets.
			//
			if( in_array( kTYPE_STRUCT, $type ) )
			{
				//
				// Handle structure lists.
				//
				if( in_array( kTYPE_LIST, $kind ) )
				{
					//
					// Iterate list.
					//
					foreach( $list as $idx => $struct )
					{
						//
						// Traverse structure.
						//
						$iterator = new \ArrayIterator( $struct );
						iterator_apply( $iterator,
										array( $this, 'traverseProperty' ),
										array( $iterator, & $theTags,
														  & $theRefs,
															$doSubOffsets ) );
			
					} // Iterating list.
		
				} // List of structures.
		
				//
				// Handle scalar structure.
				//
				else
				{
					//
					// Traverse structure.
					//
					$iterator = new \ArrayIterator( $list );
					iterator_apply( $iterator,
									array( $this, 'traverseProperty' ),
									array( $iterator, & $theTags,
													  & $theRefs,
														$doSubOffsets ) );
		
				} // Scalar structure.
		
			} // Structured offset.
			
			//
			// Handle scalar offsets.
			//
			else
			{
				//
				// Load references.
				//
				$this->loadPropertyReferences(
					$theIterator->current(), $theRefs, $type, $kind );
				
				//
				// Handle sub-structure properties.
				//
				if( $doSubOffsets )
					$this->loadSubProperties(
						$theIterator->current(), $theTags, $type, $kind );
			
			} // Scalar offset.
		
		} // Not an internal offset.
		
		return TRUE;																// ==>
	
	} // traverseProperty.

	 
	/*===================================================================================
	 *	traverseValue																	*
	 *==================================================================================*/

	/**
	 * Traverse value
	 *
	 * This method will be called by iterators that traverse list offset values, or by
	 * methods which are traversing a scalar offset value.
	 *
	 * The main duties of this method are:
	 *
	 * <ul>
	 *	<li><em>Validate the offset value</em>: The {@link verifyValue()} method will check
	 *		whether the current offset's value is correct.
	 *	<li><em>Validate references</em>: The {@link verifyReference()} method will validate
	 *		object reference values:
	 *	 <ul>
	 *		<li>If the reference is provided as an uncommitted object, the method will
	 *			commit the object and replace it with its native identifier.
	 *		<li>If the reference is provided as a committed object, the method will replace
	 *			it with its native identifier. <em>We assume here that a committed object
	 *			exists in its collection</em>.
	 *		<li>If the reference is provided as an object reference, the method will check
	 *			whether the reference is correct.
	 *		<li>Once the reference was validated, the method will add the object reference
	 *			to the provided references parameter updating the reference count.
	 *	 </ul>
	 *		<em>Note that an offset having an object reference as its data type is assumed
	 *		to have only that data type; for the moment we do not handle the case in which
	 *		a value may have both a primitive data type and be also an object
	 *		reference</em>.
	 *	<li><em>Cast the offset value</em>: The {@link castValue()} method will cast the
	 *		offset value to the data type of the tag corresponding to the leaf node of the
	 *		offsets path.
	 * </ul>
	 *
	 * The above methods will check whether the current offset has <em>a single data
	 * type</em>: only in that case will they operate on the value.
	 *
	 * This method is used by the PHP {@link iterator_apply()} method, which means that it
	 * should return <tt>TRUE</tt> to continue the object traversal, or <tt>FALSE</tt> to
	 * stop it.
	 *
	 * This method is final, derived classes should not overload this method, but rather the
	 * methods it calls.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theRefs			Object references.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset string.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> continues the traversal.
	 *
	 * @uses verifyValue()
	 * @uses verifyReference()
	 * @uses castValue()
	 */
	final protected function traverseValue( \Iterator $theIterator, &$theRefs,
																	&$theType,
																	&$theKind,
																	&$theOffset )
	{
		//
		// Init local storage.
		//
		$ref_types = array( kTYPE_REF_TAG, kTYPE_REF_TERM,
							kTYPE_REF_NODE, kTYPE_REF_EDGE,
							kTYPE_REF_ENTITY, kTYPE_REF_UNIT );
		
		//
		// Verify value.
		//
		$this->verifyValue( $theIterator, $theType, $theKind, $theOffset );
		
		//
		// Verify reference.
		//
		if( array_intersect( $theType, $ref_types ) )
			$this->verifyReference(
				$theIterator, $theRefs, $theType, $theKind, $theOffset );
		
		//
		// Cast value.
		//
		$this->castValue( $theIterator, $theType, $theKind, $theOffset );
		
		return TRUE;																// ==>
	
	} // traverseValue.

		

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
	 * @return boolean				<tt>TRUE</tt> means ready.
	 *
	 * @uses isInited()
	 */
	protected function isReady()
	{
		return ( $this->isInited()
			  && ($this->mDictionary !== NULL) );									// ==>
	
	} // isReady.

		

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

		

/*=======================================================================================
 *																						*
 *								PROTECTED REFERENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	validateReference																*
	 *==================================================================================*/

	/**
	 * Validate object reference
	 *
	 * This method will validate the provided reference, it will check whether the value is
	 * an object, in which case it will use its native identifier if committed, or check
	 * whether it is of the correct type.
	 *
	 * @param reference				$theValue			Object reference.
	 * @param string				$theClass			Ancestor class name.
	 * @param string				$theType			Reference data type.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kTYPE_REF_TERM kTYPE_REF_TAG kTYPE_REF_NODE kTYPE_REF_EDGE
	 * @see kTYPE_REF_ENTITY kTYPE_REF_UNIT 
	 */
	protected function validateReference( &$theValue, $theClass, $theType )
	{
		//
		// Handle namespace object.
		//
		if( is_object( $theValue ) )
		{
			//
			// Handle terms.
			//
			if( $theValue instanceof $theClass )
			{
				//
				// Get object reference.
				//
				if( $theValue->isCommitted() )
					$theValue = $theValue->reference();
			
			} // Is a term.
			
			else
				throw new \Exception(
					"Unable to set object reference: "
				   ."provided an object other than $theClass." );				// !@! ==>
		
		} // Namespace object.
		
		//
		// Handle reference.
		//
		else
		{
			//
			// Parse type.
			//
			switch( $theType )
			{
				case kTYPE_REF_TERM:
				case kTYPE_REF_TAG:
				case kTYPE_REF_EDGE:
				case kTYPE_REF_ENTITY:
				case kTYPE_REF_UNIT:
					$theValue = (string) $theValue;
					break;
				
				case kTYPE_REF_NODE:
					$theValue = (int) $theValue;
					break;
			}
		
		} // Object reference.
	
	} // validateReference.

	

/*=======================================================================================
 *																						*
 *								PROTECTED OFFSET UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	collectOffsetInformation														*
	 *==================================================================================*/

	/**
	 * Collect offset information
	 *
	 * This method will collect all the necessary information regarding the current offset.
	 * This method expects the provided iterator's current element to be pointing to an
	 * offset, not to an element of a list offset.
	 *
	 * The main duty of this method is to:
	 *
	 * <ul>
	 *	<li><em>Add offset to path</em>. The method add the current offset to the path
	 *		parameter.
	 *	<li><em>Generate offset string</em>. The method will generate the offset string from
	 *		the provided path parameter, the resulting string will contain all the offsets
	 *		traversed at the current level separated by a period; this string can be used to
	 *		refer to the specific offset, this string will be returned by the method.
	 *	<li><em>Resolve the offset data type and kind</em>. The method should return in the
	 *		provided reference parameters the current offset's types and kinds.
	 *	<li><em>Verify offset structure</em>. The current offset data structure will be
	 *		verified.
	 * </ul>
	 *
	 * The method will return the current offset string, or <tt>NULL</tt> if the offset is
	 * internal; in the latter case, this method will do nothing.
	 *
	 * Derived classes should not need to overload this method.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$thePath			Offsets path.
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theType			Receives data type.
	 * @param reference				$theKind			Receives data kind.
	 *
	 * @access protected
	 * @return string				Current offset string.
	 *
	 * @uses InternalOffsets()
	 * @uses getOffsetTypes()
	 */
	protected function collectOffsetInformation( \Iterator $theIterator, &$thePath,
																		 &$theTags,
																		 &$theType,
																		 &$theKind )
	{
		//
		// Skip internal offsets.
		//
		if( ! in_array( $theIterator->key(), static::InternalOffsets() ) )
		{
			//
			// Init local storage.
			//
			$tag = $theIterator->key();
		
			//
			// Determine offset string.
			//
			$offset = implode( '.', $thePath );
			
			//
			// Handle existing tag.
			//
			if( array_key_exists( $tag, $theTags ) )
			{
				//
				// Get types and kinds.
				//
				$theType = $theTags[ $tag ][ 'type' ];
				$theKind = $theTags[ $tag ][ 'kind' ];
				
				//
				// Check offset.
				//
				if( ! in_array( $offset, $theTags[ $tag ][ 'offset' ] ) )
					$theTags[ $tag ][ 'offset' ][] = $offset;
			
			} // Existing tag.
			
			//
			// Handle new tag.
			//
			else
			{
				//
				// Get types and kinds.
				//
				$this->getOffsetTypes( $tag, $theType, $theKind );
				
				//
				// Add tag if not a structure.
				//
				if( ! in_array( kTYPE_STRUCT, $theType ) )
				{
					//
					// Set types and kinds.
					//
					$theTags[ $tag ][ 'type' ] = $theType;
					$theTags[ $tag ][ 'kind' ] = $theKind;
					
					//
					// Set offset.
					//
					$theTags[ $tag ][ 'offset' ] = array( $offset );
				
				} // Not a structure.
			
			} // New tag.
			
			return $offset;															// ==>
		
		} // Not an internal offset.
		
		return NULL;																// ==>
	
	} // collectOffsetInformation.

	 
	/*===================================================================================
	 *	getOffsetTypes																	*
	 *==================================================================================*/

	/**
	 * Resolve offset type
	 *
	 * In this class we hard-code the data types and kinds of the default tags, this is to
	 * allow loading the data dictionary on a pristine system.
	 *
	 * If the provided offset is not among the ones handled in this method, it will call
	 * the inherited one.
	 *
	 * @param string				$theOffset			Current offset.
	 * @param reference				$theType			Receives data type.
	 * @param reference				$theKind			Receives data kind.
	 *
	 * @access protected
	 * @return mixed				<tt>TRUE</tt> if the tag was resolved.
	 */
	protected function getOffsetTypes( $theOffset, &$theType, &$theKind )
	{
		//
		// Handle default tags.
		//
		switch( $theOffset )
		{
			//
			// Scalar strings.
			//
			case kTAG_COLLECTION:
			case kTAG_ID_LOCAL:
			case kTAG_ID_PERSISTENT:
			case kTAG_ID_VALID:
			case kTAG_VERSION:
			case kTAG_NAME:
			case kTAG_LANGUAGE:
			case kTAG_TEXT:
			case kTAG_CONN_PROTOCOL:
			case kTAG_CONN_HOST:
			case kTAG_CONN_USER:
			case kTAG_CONN_PASS:
			case kTAG_CONN_BASE:
			case kTAG_CONN_COLL:
				$theType = array( kTYPE_STRING );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Enumerations.
			//
			case kTAG_DOMAIN:
				$theType = array( kTYPE_ENUM );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Enumerated sets.
			//
			case kTAG_CATEGORY:
			case kTAG_DATA_TYPE:
			case kTAG_DATA_KIND:
				$theType = array( kTYPE_SET );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar integers.
			//
			case kTAG_ID_SEQUENCE:
			case kTAG_CONN_PORT:
				$theType = array( kTYPE_INT );
				$theKind = Array();
				return TRUE;														// ==>
			
			//
			// Scalar term references.
			//
			case kTAG_NAMESPACE:
			case kTAG_TERM:
			case kTAG_PREDICATE:
				$theType = array( kTYPE_REF_TERM );
				$theKind = Array();
				return TRUE;														// ==>
			
			//
			// Scalar tag references.
			//
			case kTAG_TAG:
				$theType = array( kTYPE_REF_TAG );
				$theKind = Array();
				return TRUE;														// ==>
			
			//
			// Scalar node references.
			//
			case kTAG_SUBJECT:
			case kTAG_OBJECT:
				$theType = array( kTYPE_REF_NODE );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar entity references.
			//
			case kTAG_AUTHORITY:
			case kTAG_AFFILIATION:
				$theType = array( kTYPE_REF_ENTITY );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar array references.
			//
			case kTAG_CONN_OPTS:
				$theType = array( kTYPE_ARRAY );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar language string references.
			//
			case kTAG_LABEL:
			case kTAG_DEFINITION:
			case kTAG_DESCRIPTION:
				$theType = array( kTYPE_LANGUAGE_STRINGS );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Term reference lists.
			//
			case kTAG_TERMS:
				$theType = array( kTYPE_REF_TERM );
				$theKind = array( kTYPE_LIST );
				return TRUE;														// ==>
		
			//
			// Tag reference lists.
			//
			case kTAG_TAGS:
				$theType = array( kTYPE_REF_TAG );
				$theKind = array( kTYPE_LIST );
				return TRUE;														// ==>
		
			//
			// String lists.
			//
			case kTAG_NOTES:
				$theType = array( kTYPE_STRING );
				$theKind = array( kTYPE_LIST );
				return TRUE;														// ==>
		
			//
			// Private integers.
			//
			case kTAG_UNIT_COUNT:
			case kTAG_ENTITY_COUNT:
				$theType = array( kTYPE_INT );
				$theKind = array( kTYPE_PRIVATE );
				return TRUE;														// ==>
		
		} // Parsing default tags.
		
		return parent::getOffsetTypes( $theOffset, $theType, $theKind );			// ==>
	
	} // getOffsetTypes.

	 
	/*===================================================================================
	 *	verifyOffsetStructure															*
	 *==================================================================================*/

	/**
	 * Verify offset structure
	 *
	 * This method should verify the structure of the current offset value.
	 *
	 * In this class we verify whether lists and structures are arrays and raise an
	 * exception if that is not the case.
	 *
	 * When we check if the data type contains the {@link kTYPE_STRUCT} type, we assume that
	 * in that case the property cannot have any other primitive data type, therefore the
	 * value <em>must</em> be an array.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> if structure or list.
	 *
	 * @throws Exception
	 */
	protected function verifyOffsetStructure( \Iterator $theIterator, &$theType,
																	  &$theKind,
																	  &$theOffset )
	{
		//
		// Assert lists.
		//
		if( in_array( kTYPE_LIST, $theKind ) )
		{
			//
			// Verify list.
			//
			if( ! is_array( $theIterator->current() ) )
				throw new \Exception(
					"Invalid offset list value in [$theOffset]: "
				   ."the value is not an array." );								// !@! ==>
		
		} // List.
	
		//
		// Assert structure.
		//
		elseif( in_array( kTYPE_STRUCT, $theType ) )
		{
			//
			// Verify structure.
			//
			if( ! is_array( $theIterator->current() ) )
				throw new \Exception(
					"Invalid offset structure value in [$theOffset]: "
				   ."the value is not an array." );								// !@! ==>
		
		} // Is a structure.
		
	} // verifyOffsetStructure.

	 
	/*===================================================================================
	 *	verifyValue																		*
	 *==================================================================================*/

	/**
	 * Verify offset value
	 *
	 * This method should verify if the current element's value is correct, this method is
	 * called by the {@link traverseValue()} method which is called only if the current
	 * offset is neither a structure nor a list; list elements, however, are passed to this
	 * method.
	 *
	 * In this class we assert that structured types are arrays, <em>only if the current
	 * offset data type has a single entry</em>.
	 *
	 * The method will return <tt>NULL</tt> if the offset has more than one data type,
	 * <tt>TRUE</tt> if the value was verified and <tt>FALSE</tt> if it was not verified.
	 *
	 * Derived classes can handle custom cases by calling the parent method and checking the
	 * retuned value.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset string.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 */
	protected function verifyValue( \Iterator $theIterator, &$theType,
															&$theKind,
															&$theOffset )
	{
		//
		// Verify single data types.
		//
		if( count( $theType ) == 1 )
		{
			//
			// Assert array values.
			//
			switch( current( $theType ) )
			{
				case kTYPE_ARRAY:
				case kTYPE_SET:
				case kTYPE_LANGUAGE_STRINGS:
					if( ! is_array( $theIterator->current() ) )
						throw new \Exception(
							"Invalid offset value in [$theOffset]: "
						   ."the value is not an array." );						// !@! ==>
					
					return TRUE;													// ==>
			
			} // Parsed data type.
			
			return FALSE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // verifyValue.

	 
	/*===================================================================================
	 *	castValue																		*
	 *==================================================================================*/

	/**
	 * Cast offset value
	 *
	 * The duty of this method is to cast the iterator's current value to the correct data
	 * type, the method will only be called for scalar values.
	 *
	 * If the property has more than one data type, the method will do nothing; you should
	 * overload this method in derived classes only if you plan to handle offsets that can
	 * have more than one data type.
	 *
	 * This method will not handle object reference types.
	 *
	 * The method will return <tt>TRUE</tt> if the value was cast, <tt>FALSE</tt> if not and
	 * <tt>NULL</tt> if the offset has more than one data type.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset string.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 */
	protected function castValue( \Iterator $theIterator, &$theType,
														  &$theKind,
														  &$theOffset )
	{
		//
		// Cast only single types.
		//
		if( count( $theType ) == 1 )
		{
			//
			// Init local storage.
			//
			$type = current( $theType );
			$key = $theIterator->key();
			$value = $theIterator->current();
			
			//
			// Parse by type.
			//
			switch( $type )
			{
				//
				// Strings.
				//
				case kTYPE_STRING:
				case kTYPE_ENUM:
					$theIterator->offsetSet( $key, (string) $value );
					return TRUE;													// ==>
				
				//
				// Integers.
				//
				case kTYPE_INT:
					$theIterator->offsetSet( $key, (int) $value );
					return TRUE;													// ==>
		
				//
				// Floats.
				//
				case kTYPE_FLOAT:
					$theIterator->offsetSet( $key, (double) $value );
					return TRUE;													// ==>
		
				//
				// Enumerated sets.
				//
				case kTYPE_SET:
					//
					// Iterate set.
					//
					$idxs = array_keys( $value );
					foreach( $idxs as $idx )
						$value[ $idx ] = (string) $value[ $idx ];
					//
					// Set value.
					//
					$theIterator->offsetSet( $key, $value );
					return TRUE;													// ==>
		
				//
				// Language strings.
				//
				case kTYPE_LANGUAGE_STRINGS:
					//
					// Iterate language strings.
					//
					$idxs = array_keys( $value );
					foreach( $idxs as $idx )
					{
						//
						// Check if array.
						//
						if( is_array( $value[ $idx ] ) )
						{
							//
							// Cast text element.
							//
							if( array_key_exists( kTAG_TEXT, $value[ $idx ] ) )
								$value[ $idx ][ kTAG_TEXT ]
									= (string) $value[ $idx ][ kTAG_TEXT ];
							//
							// Missing text element.
							//
							else
								throw new \Exception(
									"Invalid offset value element in [$theOffset]: "
								   ."missing text item." );						// !@! ==>
							//
							// Cast language.
							//
							if( array_key_exists( kTAG_LANGUAGE, $value[ $idx ] ) )
								$value[ $idx ][ kTAG_LANGUAGE ]
									= (string) $value[ $idx ][ kTAG_LANGUAGE ];
						}
						//
						// Invalid format.
						//
						else
							throw new \Exception(
								"Invalid offset value element in [$theOffset]: "
							   ."the value is not an array." );					// !@! ==>
					}
					//
					// Set value.
					//
					$theIterator->offsetSet( $key, $value );
					return TRUE;													// ==>
		
			} // Parsed type.
			
			return FALSE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // castValue.

	 
	/*===================================================================================
	 *	verifyReference																	*
	 *==================================================================================*/

	/**
	 * Verify object reference
	 *
	 * The duty of this method is to resolve and verify offset values which should be object
	 * references.
	 *
	 * The method expects the current offset to have an object reference type, this must
	 * have been checked beforehand; if the data type is not an object reference, the method
	 * will ignore the value.
	 *
	 * The current element may either be an object reference or the object itself, if the
	 * object is not {@link isCommitted()}, the method will commit it; if the object is
	 * {@link isCommitted()}, the method assumes the object exists in its container; in all
	 * other cases the method assumes the value represents an object reference and it will
	 * check if the referenced object exists.
	 *
	 * The method will raise an exception if the provided object is not of the correct class
	 * and if the provided object reference is not found in its default container. It is
	 * assumed that all references belong to the current data dictionary wtrapper,
	 * {@link dictionary()}.
	 *
	 * In this class we handle all object reference data types, <em>only if the current
	 * offset data type has a single entry</em>.
	 *
	 * The method will return <tt>NULL</tt> if the offset has more than one data type,
	 * <tt>TRUE</tt> if the reference was resolved and <tt>FALSE</tt> if it was not
	 * resolved; this will only happen if the element's data type is not recognised.
	 *
	 * Derived classes can handle custom cases by calling the parent method and checking the
	 * retuned value.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theRefs			Object references.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset string.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses dictionary()
	 * @uses addReferenceCount()
	 */
	protected function verifyReference( \Iterator $theIterator, &$theRefs,
																&$theType,
																&$theKind,
																&$theOffset )
	{
		//
		// Verify single data types.
		//
		if( count( $theType ) == 1 )
		{
			//
			// Init local storage.
			//
			$type = current( $theType );
			$value = $theIterator->current();
			$classes = array( kTYPE_REF_TAG => 'OntologyWrapper\Tag',
							  kTYPE_REF_TERM => 'OntologyWrapper\Term',
							  kTYPE_REF_NODE => 'OntologyWrapper\Node',
							  kTYPE_REF_EDGE => 'OntologyWrapper\Edge',
							  kTYPE_REF_ENTITY => 'OntologyWrapper\Entity',
							  kTYPE_REF_UNIT => 'OntologyWrapper\Unit' );
		
			//
			// Check type.
			//
			if( ! array_key_exists( $type, $classes ) )
				return FALSE;														// ==>
		
			//
			// Handle objects.
			//
			if( is_object( $value ) )
			{
				//
				// Verify class.
				//
				if( ! ($value instanceof $classes[ $type ]) )
					throw new \Exception(
						"Invalid object reference in [$theOffset]: "
					   ."incorrect class object." );							// !@! ==>
			
				//
				// Commit object.
				//
				if( ! $value->isCommitted() )
					$id = $value->commit( $this->dictionary() );
			
				//
				// Get identifier.
				//
				elseif( ! $value->offsetExists( kTAG_NID ) )
					throw new \Exception(
						"Invalid object in [$theOffset]: "
					   ."missing native identifier." );							// !@! ==>
			
				//
				// Get identifier.
				//
				else
					$id = $value[ kTAG_NID ];
			
				//
				// Set identifier.
				//
				$theIterator->offsetSet( $theIterator->key(), $id );
			
				//
				// Add reference count.
				//
				$this->addReferenceCount( $theRefs, $type, $id, 1 );
			
				return TRUE;														// ==>
		
			} // Property is an object.
		
			//
			// Resolve collection.
			//
			switch( $type )
			{
				case kTYPE_REF_TAG:
					$collection
						= Tag::ResolveCollection(
							Tag::ResolveDatabase( $this->dictionary(), TRUE ) );
					$value = (string) $value;
					break;
		
				case kTYPE_REF_TERM:
					$collection
						= Term::ResolveCollection(
							Term::ResolveDatabase( $this->dictionary(), TRUE ) );
					$value = (string) $value;
					break;
		
				case kTYPE_REF_NODE:
					$collection
						= Node::ResolveCollection(
							Node::ResolveDatabase( $this->dictionary(), TRUE ) );
					$value = (int) $value;
					break;
		
				case kTYPE_REF_EDGE:
					$name = Edge::kSEQ_NAME;
					$collection
						= Edge::ResolveCollection(
							Edge::ResolveDatabase( $this->dictionary(), TRUE ) );
					$value = (string) $value;
					break;
		
				case kTYPE_REF_ENTITY:
					$collection
						= Entity::ResolveCollection(
							Entity::ResolveDatabase( $this->dictionary(), TRUE ) );
					$value = (string) $value;
					break;
		
				case kTYPE_REF_UNIT:
					$collection
						= Unit::ResolveCollection(
							Unit::ResolveDatabase( $this->dictionary(), TRUE ) );
					$value = (string) $value;
					break;
			
				default:
					return FALSE;													// ==>
		
			} // Parsed type.
		
			//
			// Resolve reference.
			//
			if( ! $collection->resolve( $value, kTAG_NID, NULL ) )
				throw new \Exception(
					"Unresolved reference in [$theOffset]: "
				   ."($value)." );												// !@! ==>
		
			//
			// Cast value.
			//
			$theIterator->offsetSet( $theIterator->key(), $value );
		
			//
			// Add reference count.
			//
			$this->addReferenceCount( $theRefs, $type, $value, 1 );
		
			return TRUE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // verifyReference.

	 
	/*===================================================================================
	 *	addReferenceCount																*
	 *==================================================================================*/

	/**
	 * Add reference count
	 *
	 * This method will add the reference count to the provided traversal data parameter,
	 * the method is called by the {@link castReference()} method and it will increment the
	 * reference count for the collection and object identifier provided as parameter.
	 *
	 * @param reference				$theRefs			Object references.
	 * @param string				$theType			Offset data type.
	 * @param mixed					$theIdentifier		Referenced object identifier.
	 * @param integer				$theReferences		Reference count.
	 *
	 * @access protected
	 */
	protected function addReferenceCount( &$theRefs, $theType,
													 $theIdentifier,
													 $theReferences = 1 )
	{
		//
		// Determine collection.
		//
		switch( $theType )
		{
			case kTYPE_REF_TAG:
				$collection = Tag::kSEQ_NAME;
				break;
		
			case kTYPE_REF_TERM:
				$collection = Term::kSEQ_NAME;
				break;
		
			case kTYPE_REF_NODE:
				$collection = Node::kSEQ_NAME;
				break;
		
			case kTYPE_REF_EDGE:
				$collection = Edge::kSEQ_NAME;
				break;
		
			case kTYPE_REF_ENTITY:
				$collection = Entity::kSEQ_NAME;
				break;
		
			case kTYPE_REF_UNIT:
				$collection = Unit::kSEQ_NAME;
				break;
		
		} // Parsed type.
		
		//
		// Create collection entry.
		//
		if( ! array_key_exists( $collection, $theRefs ) )
			$theRefs[ $collection ]
				= array( array( 'id' => $theIdentifier,
								'count' => $theReferences ) );
		
		//
		// Handle collection entry.
		//
		else
		{
			//
			// Reference collection.
			//
			$ref = & $theRef[ $collection ];
			
			//
			// Find identifier.
			//
			$keys = array_keys( $ref );
			foreach( $keys as $key )
			{
				//
				// Match identifier.
				//
				if( $ref[ $key ][ 'id' ] === $theIdentifier )
				{
					//
					// Increment reference count.
					//
					$ref[ $key ][ 'count' ] += $theReferences;
					
					return;															// ==>
				
				} // Matched.
			
			} // Iterating collection references.
			
			//
			// Add identifier.
			//
			$ref[ $key ][ 'id' ] = $theIdentifier;
			
			//
			// Set reference count.
			//
			$ref[ $key ][ 'count' ] = $theReferences;
		
		} // Has collection.
	
	} // addReferenceCount.

	 
	/*===================================================================================
	 *	updateReferenceCount															*
	 *==================================================================================*/

	/**
	 * Update reference count
	 *
	 * This method will update the references count of the object identified by the provided
	 * parameter.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param integer				$theReferences		Reference count.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses dictionary()
	 */
	protected function updateReferenceCount( $theCollection,
											 $theIdentifier,
											 $theReferences )
	{
		//
		// Init local storage.
		//
		$dictionary = $this->dictionary();
		if( $dictionary === NULL )
			throw new \Exception(
				"Unable to update reference count: "
			   ."missing data dictionary." );									// !@! ==>
		
		//
		// Resolve collection.
		//
		switch( $theCollection )
		{
			case Tag::kSEQ_NAME:
				$collection
					= Tag::ResolveCollection(
						Tag::ResolveDatabase( $dictionary, TRUE ) );
				break;
		
			case Term::kSEQ_NAME:
				$collection
					= Term::ResolveCollection(
						Term::ResolveDatabase( $dictionary, TRUE ) );
				break;
		
			case Node::kSEQ_NAME:
				$collection
					= Term::ResolveCollection(
						Term::ResolveDatabase( $dictionary, TRUE ) );
				break;
		
			case Edge::kSEQ_NAME:
				$collection
					= Edge::ResolveCollection(
						Edge::ResolveDatabase( $dictionary, TRUE ) );
				break;
		
			case Entity::kSEQ_NAME:
				$collection
					= Entity::ResolveCollection(
						Entity::ResolveDatabase( $dictionary, TRUE ) );
				break;
		
			case Unit::kSEQ_NAME:
				$collection
					= Unit::ResolveCollection(
						Unit::ResolveDatabase( $dictionary, TRUE ) );
				break;
		
			default:
				throw new \Exception(
					"Unable to update reference count in collection [$theCollection]: "
				   ."unknown collection." );									// !@! ==>
		
		} // Parsed collection.
		
		//
		// Resolve tag.
		//
		switch( static::kSEQ_NAME )
		{
			case Tag::kSEQ_NAME:
				$tag = kTAG_TAG_COUNT;
				break;
		
			case Term::kSEQ_NAME:
				$tag = kTAG_TERM_COUNT;
				break;
		
			case Node::kSEQ_NAME:
				$tag = kTAG_NODE_COUNT;
				break;
		
			case Edge::kSEQ_NAME:
				$tag = kTAG_EDGE_COUNT;
				break;
		
			case Entity::kSEQ_NAME:
				$tag = kTAG_ENTITY_COUNT;
				break;
		
			case Unit::kSEQ_NAME:
				$tag = kTAG_UNIT_COUNT;
				break;
		
			default:
				throw new \Exception(
					"Unable to update reference count: "
				   ."unknown current object reference count tag." );			// !@! ==>
		
		} // Parsed collection.
		
		//
		// Update reference count.
		//
		$collection->updateReferenceCount( $theIdentifier, $tag, $theReferences );
	
	} // updateReferenceCount.

	 
	/*===================================================================================
	 *	loadObjectTag																	*
	 *==================================================================================*/

	/**
	 * Load object tags
	 *
	 * This method will load the provided parameter with the tag references used by offsets
	 * of the current object.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTag</b>: Tag sequence number.
	 *	<li><b>$theInfo</b>: Tag information.
	 *	<li><b>$theTags</b>: Receives tag list.
	 * </ul>
	 *
	 * In this class we simply add the provided tag, derived classes can overload this
	 * method to exclude certain tags from the list.
	 *
	 * @param integer				$theTag				Tag sequence number.
	 * @param reference				$theInfo			Tag information.
	 * @param reference				$theTags			Receives tags list.
	 *
	 * @access protected
	 */
	protected function loadObjectTag( $theTag, &$theInfo, &$theTags )
	{
		//
		// Cast tag.
		//
		$theTag = (int) $theTag;
	
		//
		// Add to set.
		//
		if( ! in_array( $theTag, $theTags ) )
			$theTags[] = $theTag;
	
	} // loadObjectTag.

	 
	/*===================================================================================
	 *	loadPropertyReferences															*
	 *==================================================================================*/

	/**
	 * Load property references
	 *
	 * This method will load in the provided parameter the object references contained in
	 * the provided iterator element.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: Property value.
	 *	<li><b>$theRefs</b>: This parameter will receive the object references, it is an
	 *		array structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The referenced object's collection name.
	 *		<li><tt>value</tt>: An array collecting all the referenced object's native
	 *			identifiers for the collection indicated in the key.
	 *	 </ul>
	 *	<li><b>$theType</b>: Property data types.
	 *	<li><b>$theKind</b>: Property data kinds.
	 * </ul>
	 *
	 * The method will only consider properties which represent object references and will
	 * handle list properties.
	 *
	 * @param mixed					$theValue			Property value.
	 * @param reference				$theRefs			Receives object references.
	 * @param reference				$theType			Property data types.
	 * @param reference				$theKind			Property data kind.
	 *
	 * @access protected
	 */
	protected function loadPropertyReferences( $theValue, &$theRefs,
														  &$theType,
														  &$theKind )
	{
		//
		// Init local storage.
		//
		$types = array( kTYPE_ENUM, kTYPE_SET,
						kTYPE_REF_TAG, kTYPE_REF_TERM, kTYPE_REF_NODE, kTYPE_REF_EDGE,
						kTYPE_REF_ENTITY, kTYPE_REF_UNIT );
		$tags  = array( kTYPE_REF_TAG );
		$terms = array( kTYPE_ENUM, kTYPE_SET, kTYPE_REF_TERM );
		$nodes = array( kTYPE_REF_NODE );
		$edges = array( kTYPE_REF_EDGE );
		$units = array( kTYPE_REF_UNIT );
		$entities = array( kTYPE_REF_ENTITY );
		
		//
		// Skip non-references.
		//
		if( count( array_intersect( $theType, $types ) ) )
		{
			//
			// Handle lists.
			//
			if( in_array( kTYPE_LIST, $theKind ) )
			{
				//
				// Remove list from kind.
				//
				$kind = array_diff( $theKind, array( kTYPE_LIST ) );
				
				//
				// Recurse list elements.
				//
				foreach( $theValue as $value )
					$this->loadPropertyReferences(
						$value, $theRefs, $theType, $kind );
			
			} // List.
			
			//
			// Handle scalars.
			//
			else
			{
				//
				// Get tag collection name.
				//
				if( count( array_intersect( $theType, $tags ) ) )
					$collection = Tag::kSEQ_NAME;
			
				//
				// Get term collection name.
				//
				elseif( count( array_intersect( $theType, $terms ) ) )
					$collection = Term::kSEQ_NAME;
			
				//
				// Get node collection name.
				//
				elseif( count( array_intersect( $theType, $nodes ) ) )
					$collection = Node::kSEQ_NAME;
			
				//
				// Get edge collection name.
				//
				elseif( count( array_intersect( $theType, $edges ) ) )
					$collection = Edge::kSEQ_NAME;
			
				//
				// Get unit collection name.
				//
				elseif( count( array_intersect( $theType, $units ) ) )
					$collection = Unit::kSEQ_NAME;
			
				//
				// Get entity collection name.
				//
				elseif( count( array_intersect( $theType, $entities ) ) )
					$collection = Entity::kSEQ_NAME;
				
				//
				// Allocate collection.
				//
				if( ! array_key_exists( $collection, $theRefs ) )
					$theRefs[ $collection ]
						= Array();
				
				//
				// Reference collection.
				//
				$ref = & $theRefs[ $collection ];
				
				//
				// Handle array values.
				//
				if( is_array( $theValue ) )
				{
					foreach( $theValue as $value )
					{
						if( ! in_array( $value, $ref ) )
							$ref[] = $value;
					}
				
				} // List of references.
				
				//
				// Handle scalar values.
				//
				elseif( ! in_array( $theValue, $ref ) )
					$ref[] = $theValue;
			
			} // Scalar.
		
		} // Is a reference.
	
	} // loadPropertyReferences.

	 
	/*===================================================================================
	 *	loadSubProperties																*
	 *==================================================================================*/

	/**
	 * Load sub-properties
	 *
	 * This method will load in the provided parameter the provided iterator element's
	 * sub-properties.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: Property value.
	 *	<li><b>$theTags</b>: This parameter will receive the list of sub-properties of the
	 *		provided property.
	 *	<li><b>$theType</b>: Property data types.
	 *	<li><b>$theKind</b>: Property data kinds.
	 * </ul>
	 *
	 * The method will only consider properties which are structured scalar.
	 *
	 * @param mixed					$theValue			Property value.
	 * @param reference				$theTags			Receives sub-property tags.
	 * @param reference				$theType			Property data types.
	 * @param reference				$theKind			Property data kind.
	 *
	 * @access protected
	 */
	protected function loadSubProperties( $theValue, &$theTags,
													 &$theType,
													 &$theKind )
	{
		//
		// Init local storage.
		//
		$types = array( kTYPE_LANGUAGE_STRINGS );
		
		//
		// Skip non-structured types.
		//
		if( count( array_intersect( $theType, $types ) ) )
		{
			//
			// Handle lists.
			//
			if( in_array( kTYPE_LIST, $theKind ) )
			{
				//
				// Remove list from kind.
				//
				$kind = array_diff( $theKind, array( kTYPE_LIST ) );
				
				//
				// Recurse list elements.
				//
				foreach( $theValue as $value )
					$this->loadSubProperties(
						$value, $theTags, $theType, $kind );
			
			} // List.
			
			//
			// Handle scalars.
			//
			else
			{
				//
				// Iterate language strings.
				//
				foreach( $theValue as $element )
				{
					//
					// Iterate structure elements.
					//
					$offsets = array_keys( $element );
					foreach( $offsets as $offset )
					{
						if( ! in_array( (int) $offset, $theTags ) )
							$theTags[] = (int) $offset;
				
					} // Iterating languafe string offsets.
			
				} // Iterating structure elements.
			
			} // Scalar.
		
		} // Is a reference.
	
	} // loadSubProperties.

	 

} // class PersistentObject.


?>
