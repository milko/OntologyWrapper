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
	
	/**
	 * Current offset path.
	 *
	 * This constant provides the current offset path array offset.
	 *
	 * @var string
	 */
	const kCOMMIT_DATA_OFFSET_PATH = 'path';
	
	/**
	 * Offset tags.
	 *
	 * This constant provides the offset tags array offset.
	 *
	 * @var string
	 */
	const kCOMMIT_DATA_OFFSET_TAGS = 'tags';
	
	/**
	 * Reference counts.
	 *
	 * This constant provides the offset for the references count array.
	 *
	 * @var string
	 */
	const kCOMMIT_DATA_OFFSET_REFS = 'refs';
	
	/**
	 * Types.
	 *
	 * This constant provides the offset for the tag data types.
	 *
	 * @var string
	 */
	const kCOMMIT_DATA_OFFSET_TYPE = 'type';
	
	/**
	 * Kinds.
	 *
	 * This constant provides the offset for the tag data kinds.
	 *
	 * @var string
	 */
	const kCOMMIT_DATA_OFFSET_KIND = 'kind';
	
	/**
	 * Offsets.
	 *
	 * This constant provides the offset for the offset strings list.
	 *
	 * @var string
	 */
	const kCOMMIT_DATA_OFFSET_OFFSETS = 'offsets';

		

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
			elseif( ! ($this->mDictionary instanceof Wrapper) )
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
			$this->preCommit( $data );
		
			//
			// Commit.
			//
			$id = $collection->commit( $this );
	
			//
			// Copy identifier if missing.
			//
			if( ! $this->offsetExists( kTAG_NID ) )
				$this->offsetSet( kTAG_NID, $id );
		
			//
			// Cleanup object.
			//
			$this->postCommit( $data );
	
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
	 * The method accepts a single array reference parameter that will be populated as the
	 * object is traversed, this array contains the following elements:
	 *
	 * <ul>
	 *	<li><tt>{@link kCOMMIT_DATA_OFFSET_PATH}</tt>: This is an array holding the list of
	 *		offsets found at the different levels corresponding to the current level, it is
	 *		used to compute the current offset string.
	 *	<li><tt>{@link kCOMMIT_DATA_OFFSET_TAGS}</tt>: This is an array holding the list of
	 *		tags used in the object, the array has as key the tag reference and as value the
	 *		list of offsets in which the tag is used.
	 *	<li><tt>{@link kCOMMIT_DATA_OFFSET_REFS}</tt>: This is an array holding as key the
	 *		collection name and as value the number of times properties of the current
	 *		object references objects of the collection set in the key.
	 * </ul>
	 *
	 * This parameter will be initialised by the {@link preCommitPrepare()} method.
	 *
	 * Derived classes should not overload this method, they should, instead, overload the
	 * called methods.
	 *
	 * @param reference				$theData			Commit data.
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
	protected function preCommit( &$theData )
	{
		//
		// Prepare object.
		//
		$this->preCommitPrepare( $theData );
	
		//
		// Traverse object.
		//
		$this->preCommitTraverse( $theData );
		
		//
		// Finalise object.
		//
		$this->preCommitFinalise( $theData );
	
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
	 * The second task of this method is to initialise the parameter that will be passed to
	 * the other methods involved in committing the object, this array is structured as
	 * follows:
	 *
	 * <ul>
	 *	<li><tt>{@link kCOMMIT_DATA_OFFSET_PATH}</tt>: This array contains the list of
	 *		offset tag references encountered at each depth level corresponding to the
	 *		current property. This information is used to compute the offset string.
	 *	<li><tt>{@link kCOMMIT_DATA_OFFSET_TAGS}</tt>: This array contains the list of tag
	 *		references used as offsets. The array is structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The array element key represents the tag reference.
	 *		<li><tt>value</tt>: The array element value is an array structured as follows:
	 *		 <ul>
	 *			<li><tt>{@link kCOMMIT_DATA_OFFSET_TYPE}</tt>: Will receive the tag data
	 *				type.
	 *			<li><tt>{@link kCOMMIT_DATA_OFFSET_KIND}</tt>: Will receive the tag data
	 *				kind.
	 *			<li><tt>{@link kCOMMIT_DATA_OFFSET_OFFSETS}</tt>: Will receive the list of
	 *				offset strings which is the concatenation of all tags comprising the
	 *				path to the current depth level.
	 *		 </ul>
	 *	 </ul>
	 * </ul>
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
	 * @param reference				$theData			Commit data.
	 *
	 * @access protected
	 *
	 * @see kCOMMIT_DATA_OFFSET_PATH kCOMMIT_DATA_OFFSET_TAGS kCOMMIT_DATA_OFFSET_REFS
	 *
	 * @uses isInited()
	 */
	protected function preCommitPrepare( &$theData )
	{
		//
		// Check if initialised.
		//
		if( ! $this->isInited() )
			throw new \Exception(
				"Unable to commit: "
			   ."the object is not initialised." );								// !@! ==>
		
		//
		// Initialise commit data.
		//
		if( ! is_array( $theData ) )
			$theData = Array();
		
		//
		// Init offsets path.
		//
		if( ! array_key_exists( static::kCOMMIT_DATA_OFFSET_PATH, $theData ) )
			$theData[ static::kCOMMIT_DATA_OFFSET_PATH ] = Array();
		
		//
		// Init offset strings.
		//
		if( ! array_key_exists( static::kCOMMIT_DATA_OFFSET_TAGS, $theData ) )
			$theData[ static::kCOMMIT_DATA_OFFSET_TAGS ] = Array();
		
		//
		// Init object references.
		//
		if( ! array_key_exists( static::kCOMMIT_DATA_OFFSET_REFS, $theData ) )
			$theData[ static::kCOMMIT_DATA_OFFSET_REFS ] = Array();
	
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
	 * This method should not be overloaded by derived classes, rather, the methods called
	 * by the {@link traverseStructure()} method can be extended to provided custom
	 * validation or casting.
	 *
	 * @param reference				$theData			Commit data.
	 *
	 * @access protected
	 *
	 * @uses traverseStructure()
	 */
	protected function preCommitTraverse( &$theData )
	{
		//
		// Traverse object.
		//
		$iterator = $this->getIterator();
		iterator_apply( $iterator,
						array( $this, 'traverseStructure' ),
						array( $iterator, & $theData ) );
	
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
	 * In general, this method can be overloaded to set the object's identifiers and any
	 * other property which depends on data collected during the object's traversal.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theData			Commit data.
	 *
	 * @access protected
	 */
	protected function preCommitFinalise( &$theData )									   {}

		

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
	 * This method is called immediately after the object was committed, its duty is to
	 * cleanup eventual run-time data and update eventual related objects.
	 *
	 * The method accepts a single reference parameter which is the same provided and
	 * populated by the pre-commit methods.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theData			Commit data.
	 *
	 * @access protected
	 */
	protected function postCommit( &$theData )											   {}

	

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
	 * This method will be called for each element of the traversed structure, either the
	 * root object structure or a sub-structure, this means that the iterator's current
	 * element handed to this method must have the offset as the key and the offset value as
	 * the value.
	 *
	 * The method will perform the following steps:
	 *
	 * <ul>
	 *	<li><em>Resolve tag data type and kind</em>: The {@link getOffsetTypes()} method
	 *		will resolve the current offset and retrieve the referenced tag's data type and
	 *		kind.
	 *	<li><em>Resolve offset path</em>: The path of the current offset will be added to
	 *		the current path and converted to a string.
	 *	<li><em>Collect tag offsets</em>: If the current offset type is not a structure,
	 *		{@link kTYPE_STRUCT}, the tag, types and offset string will be added to the
	 *		{@link kCOMMIT_DATA_OFFSET_TAGS} element of the traversal data.
	 *	<li><em>Verify structure</em>: The structure of the current iterator element will be
	 *		validated.
	 *	<li><em>Scalar offsets</em>: If the current element is a scalar offset, that is, not
	 *		a list and not a structure:
	 *	 <ul>
	 *		<li><em>Handle property value</em>: The current property value will be validated
	 *			and cast to the required data type.
	 *	 </ul>
	 *	<li><em>Structure offsets</em>: If the current element is a structure, the method
	 *		will recursively call itself for each element of the structure.
	 *	<li><em>List offsets</em>: If the current element is a list of scalars, each element
	 *		of the list will be validated and cast.
	 *	<li><em>Pop offset</em>: The current offset will be popped from the offsets path
	 *		list.
	 * </ul>
	 *
	 * This method should not be overloaded, only the methods called by this one should be
	 * derived:
	 *
	 * <ul>
	 *	<li><tt>{@link getOffsetTypes()</tt>. This method will return the current offset's
	 *		data type and kind in the provided reference parameters, there should be no need
	 *		to overload this method.
	 *	<li><tt>{@link verifyStructure()</tt>. This method's duty is to verify the structure
	 *		of the current property, one would overload this method to check the structure
	 *		of properties which should have a custom structure; in this class we only ensure
	 *		that structure type and list kind properties are indeed arrays.
	 *	<li><tt>{@link traverseHandleValue()</tt>. This method is called for each scalar
	 *		property, both if at structure root level or if part of a list of scalars, the
	 *		method calls the following methods:
	 *	 <ul>
	 *		<li><tt>{@link verifyValue()}</tt>: The duty of this method is to validate the
	 *			property value.
	 *		<li><tt>{@link castValue()}</tt>: The duty of this method is to cast the value
	 *			of the property to the correct data type.
	 *	 </ul>
	 * </ul>
	 *
	 * Derived classes should overload the above methods and not the current one.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter is the iterator pointing to the current
	 *		traversal element.
	 *	<li><b>$theData</b>: This reference parameter will receive data during the object
	 *		traversal, this data will be available to all methods involved in the object
	 *		committal process.
	 * </ul>
	 *
	 * This method is used by the PHP {@link iterator_apply()} method, which means that it
	 * should return <tt>TRUE</tt> to continue the object traversal, or <tt>FALSE</tt> to
	 * stop it.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theData			Receives traversal data.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt>, or <tt>FALSE</tt> to stop the traversal.
	 *
	 * @see kCOMMIT_DATA_OFFSET_PATH kCOMMIT_DATA_OFFSET_TAGS kCOMMIT_DATA_OFFSET_REFS
	 * @see kCOMMIT_DATA_OFFSET_TYPE kCOMMIT_DATA_OFFSET_KIND kCOMMIT_DATA_OFFSET_OFFSETS
	 *
	 * @uses getOffsetTypes()
	 * @uses verifyStructure()
	 * @uses traverseHandleValue()
	 */
	final protected function traverseStructure( \Iterator $theIterator, &$theData )
	{
		//
		// Init local storage.
		//
		$key = $theIterator->key();
		
		//
		// Collect offset types.
		//
		$this->getOffsetTypes( $key, $type, $kind );
		
		//
		// Add offset to path.
		//
		$theData[ static::kCOMMIT_DATA_OFFSET_PATH ][] = $key;
		
		//
		// Determine offset string.
		//
		$offset = implode( '.', $theData[ static::kCOMMIT_DATA_OFFSET_PATH ] );
		
		//
		// Handle scalar property.
		//
		if( ! in_array( kTYPE_STRUCT, $type ) )
		{
			//
			// Handle new tag.
			//
			if( ! array_key_exists( $key, $theData[ static::kCOMMIT_DATA_OFFSET_TAGS ] ) )
				$theData[ static::kCOMMIT_DATA_OFFSET_TAGS ][ $key ]
					= array( static::kCOMMIT_DATA_OFFSET_TYPE => $type,
							 static::kCOMMIT_DATA_OFFSET_KIND => $kind,
							 static::kCOMMIT_DATA_OFFSET_OFFSETS => Array() );
			
			//
			// Handle offset string.
			//
			if( ! in_array( $offset, $theData[ static::kCOMMIT_DATA_OFFSET_TAGS ]
											 [ $key ]
											 [ static::kCOMMIT_DATA_OFFSET_OFFSETS ] ) )
				$theData[ static::kCOMMIT_DATA_OFFSET_TAGS ]
						[ $key ]
						[ static::kCOMMIT_DATA_OFFSET_OFFSETS ]
						[] = $offset;
		
		} // Not a sub-structure.
		
		//
		// Verify property structure.
		//
		if( ! $this->verifyStructure( $theIterator, $theData, $offset ) )
			$this->traverseHandleValue( $theIterator, $theData, $key, $offset );
		
		//
		// Handle structure offsets.
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
										array( $iterator, & $theData ) );
		
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
									array( $iterator, & $theData ) );
			
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
								array( $this, 'traverseHandleValue' ),
								array( $iterator, & $theData, $key, $offset ) );
			
			} // List of scalars.

			//
			// Update current iterator.
			//
			$theIterator->offsetSet( $key, $list->getArrayCopy() );
		
		} // Structured offset.
		
		//
		// Pop path.
		//
		array_pop( $theData[ static::kCOMMIT_DATA_OFFSET_PATH ] );
		
		return TRUE;																// ==>
	
	} // traverseStructure.

	 
	/*===================================================================================
	 *	traverseHandleValue																*
	 *==================================================================================*/

	/**
	 * Handle value
	 *
	 * The duty of this method is to handle scalar property values, that is, either scalar
	 * values or scalar elements of a list of values; structure properties should never be
	 * provided to this method.
	 *
	 * The method calls the following other methods:
	 *
	 * <ul>
	 *	<li><tt>{@link verifyValue()}</tt>: The duty of this method is to validate the
	 *		value.
	 *	<li><tt>{@link castValue()}</tt>: The duty of this method is to cast the value to
	 *		the correct data type.
	 * </ul>
	 *
	 * This method should handle the current offset scalar value, it should verify if the
	 * value is correct and cast the value to the provided data type.
	 *
	 * This method should only be called for scalar offset values, list scalars should call
	 * this method for each element.
	 *
	 * The method will return <tt>TRUE</tt> to continue the object traversal.
	 *
	 * Derived classes should not overload this method, but rather the methods it calls.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theData			Receives traversal data.
	 * @param string				$theTag				Working offset.
	 * @param string				$theOffset			Current offset string.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> continues the traversal.
	 *
	 * @uses verifyValue()
	 * @uses castValue()
	 */
	final protected function traverseHandleValue( \Iterator $theIterator,
														   &$theData,
															$theTag,
															$theOffset )
	{
		//
		// Verify value.
		//
		$this->verifyValue( $theIterator, $theData, $theTag, $theOffset );
		
		//
		// Cast value.
		//
		$this->castValue( $theIterator, $theData, $theTag, $theOffset );
		
		return TRUE;																// ==>
	
	} // traverseHandleValue.

		

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
	 *
	 * @throws Exception
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
	 *	verifyStructure																	*
	 *==================================================================================*/

	/**
	 * Verify offset structure
	 *
	 * This method should verify that the current element of the provided iterator has the
	 * correct structure and content.
	 *
	 * In this class we verify whether lists, structures and structured types are indeed
	 * arrays and raise an exception if that is not the case. Note that we only check
	 * structured data types if the offset has a single data type.
	 *
	 * The method will return <tt>TRUE</tt> if the offset value is either a structure or a
	 * list, and <tt>FALSE</tt> if the offset value is a scalar data type; in derived
	 * classes you can call the parent method and perform custom checks if the parent method
	 * returned <tt>FALSE</tt>.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theData			Receives traversal data.
	 * @param string				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> if structure or list.
	 *
	 * @throws Exception
	 */
	protected function verifyStructure( \Iterator $theIterator, &$theData, $theOffset )
	{
		//
		// Assert lists.
		//
		if( in_array( kTYPE_LIST, $theData[ static::kCOMMIT_DATA_OFFSET_TAGS ]
										  [ $theIterator->key() ]
										  [ static::kCOMMIT_DATA_OFFSET_KIND ] ) )
		{
			//
			// Verify list.
			//
			if( ! is_array( $theIterator->current() ) )
				throw new \Exception(
					"Invalid offset list value in [$theOffset]: "
				   ."the value is not an array." );								// !@! ==>
			
			return TRUE;															// ==>
		
		} // List.
		
		//
		// Assert structure.
		// Note that if it is a structure,
		// it cannot have any other data type.
		//
		if( in_array( kTYPE_STRUCT, $theData[ static::kCOMMIT_DATA_OFFSET_TAGS ]
											[ $theIterator->key() ]
											[ static::kCOMMIT_DATA_OFFSET_TYPE ] ) )
		{
			//
			// Verify structure.
			//
			if( ! is_array( $theIterator->current() ) )
				throw new \Exception(
					"Invalid offset structure value in [$theOffset]: "
				   ."the value is not an array." );								// !@! ==>
			
			return TRUE;															// ==>
		
		} // Is a structure.
		
		return FALSE;																// ==>
	
	} // verifyStructure.

	 
	/*===================================================================================
	 *	verifyValue																		*
	 *==================================================================================*/

	/**
	 * Verify offset value
	 *
	 * This method should verify the current offset value, this method is called by the
	 * {@link traverseHandleValue()} method if the current offset is not a structure nor
	 * a list.
	 *
	 * In this class we assert that structured types are arrays if there is only one offset
	 * type.
	 *
	 * The method will return <tt>NULL</tt> if the offset has more than one type,
	 * <tt>TRUE</tt> if the value type was verified and <tt>FALSE</tt> if it was not
	 * verified.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theData			Receives traversal data.
	 * @param string				$theTag				Working offset.
	 * @param string				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 */
	protected function verifyValue( \Iterator $theIterator, &$theData, $theTag, $theOffset )
	{
		//
		// Get data type.
		//
		$type = $theData[ static::kCOMMIT_DATA_OFFSET_TAGS ]
						[ $theTag ]
						[ static::kCOMMIT_DATA_OFFSET_KIND ];
		
		//
		// Verify single data types.
		//
		if( count( $type ) == 1 )
		{
			//
			// Assert array values.
			//
			switch( $tmp = current( $type ) )
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
	 * This method makes use of the {@link castReference()} method that handles specifically
	 * object references.
	 *
	 * The method will return <tt>TRUE</tt> if the value was cast, <tt>FALSE</tt> if not and
	 * <tt>NULL</tt> if the offset has more than one data type.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theData			Receives traversal data.
	 * @param string				$theTag				Working offset.
	 * @param string				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 */
	protected function castValue( \Iterator $theIterator, &$theData, $theTag, $theOffset )
	{
		//
		// Get data type.
		//
		$type = $theData[ static::kCOMMIT_DATA_OFFSET_TAGS ]
						[ $theTag ]
						[ static::kCOMMIT_DATA_OFFSET_KIND ];
		
		//
		// Cast only single types.
		//
		if( count( $type ) == 1 )
		{
			//
			// Init local storage.
			//
			$type = current( $type );
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
					// Iterate set.
					$idxs = array_keys( $value );
					foreach( $idxs as $idx )
						$value[ $idx ] = (string) $value[ $idx ];
					// Set value.
					$theIterator->offsetSet( $key, $value );
					return TRUE;													// ==>
		
				//
				// Language strings.
				//
				case kTYPE_LANGUAGE_STRINGS:
					// Iterate language strings.
					$idxs = array_keys( $value );
					foreach( $idxs as $idx )
					{
						// Check if array.
						if( is_array( $value[ $idx ] ) )
						{
							// Check text element.
							if( array_key_exists( kTAG_TEXT, $value[ $idx ] ) )
								$value[ $idx ][ kTAG_TEXT ]
									= (string) $value[ $idx ][ kTAG_TEXT ];
							// Missing text element.
							else
								throw new \Exception(
									"Invalid offset value element in [$theOffset]: "
								   ."missing text item." );						// !@! ==>
							// Cast language.
							if( array_key_exists( kTAG_LANGUAGE, $value[ $idx ] ) )
								$value[ $idx ][ kTAG_LANGUAGE ]
									= (string) $value[ $idx ][ kTAG_LANGUAGE ];
						}
						// Invalid format.
						else
							throw new \Exception(
								"Invalid offset value element in [$theOffset]: "
							   ."the value is not an array." );					// !@! ==>
					}
					// Set value.
					$theIterator->offsetSet( $key, $value );
					return TRUE;													// ==>
		
				//
				// Object references.
				//
				case kTYPE_REF_TAG:
				case kTYPE_REF_TERM:
				case kTYPE_REF_NODE:
				case kTYPE_REF_EDGE:
				case kTYPE_REF_ENTITY:
				case kTYPE_REF_UNIT:
					return $this->castReference(
								$theIterator, $theData, $type, $theOffset );		// ==>
		
			} // Parsed type.
			
			return FALSE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // castValue.

	 
	/*===================================================================================
	 *	castReference																	*
	 *==================================================================================*/

	/**
	 * Verify object reference
	 *
	 * This method will verify the current property object reference, it will perform the
	 * following actions:
	 *
	 * <ul>
	 *	<li>If the property is an object:
	 *	 <ul>
	 *		<li>if the object is committed, we copy its native identifier and assume it
	 *			exists.
	 *		<li>if the object is not committed, we commit it and copy its native identifier.
	 *	 </ul>
	 *	<li>If the property is not an object, we assume it is a reference and we check it.
	 * </ul>
	 *
	 * The method assumes the data type is an object reference and when committing we force
	 * the current object's wrapper.
	 *
	 * The method will return <tt>TRUE</tt> if the reference was verified and <tt>FALSE</tt>
	 * if not.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theData			Receives traversal data.
	 * @param string				$theType			Offset data type.
	 * @param string				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> if verified.
	 *
	 * @throws Exception
	 */
	protected function castReference( \Iterator $theIterator,
											   &$theData,
												$theType,
												$theOffset )
	{
		//
		// Init local storage.
		//
		$value = $theIterator->current();
		$classes = array( kTYPE_REF_TAG => 'OntologyWrapper\Tag',
						  kTYPE_REF_TERM => 'OntologyWrapper\Term',
						  kTYPE_REF_NODE => 'OntologyWrapper\Node',
						  kTYPE_REF_EDGE => 'OntologyWrapper\Edge',
						  kTYPE_REF_ENTITY => 'OntologyWrapper\Entity',
						  kTYPE_REF_UNIT => 'OntologyWrapper\Unit' );
		
		//
		// Check class.
		//
		if( ! array_key_exists( $theType, $classes ) )
			return FALSE;															// ==>
		
		//
		// Handle objects.
		//
		if( is_object( $value ) )
		{
			//
			// Verify class.
			//
			if( ! ($value instanceof $classes[ $theType ]) )
				throw new \Exception(
					"Invalid object reference in [$theOffset]: "
				   ."incorrect class object." );								// !@! ==>
			
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
				   ."missing native identifier." );								// !@! ==>
			
			//
			// Get identifier.
			//
			else
				$id = $value[ kTAG_NID ];
			
			//
			// Set identifier.
			//
			$theIterator->offsetSet( $theIterator->key(), $id );
			
			return TRUE;															// ==>
		
		} // Property is an object.
		
		//
		// Resolve collection.
		//
		switch( $theType )
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
				return FALSE;														// ==>
		
		} // Parsed type.
		
		//
		// Resolve reference.
		//
		if( ! $collection->resolve( $value, kTAG_NID, NULL ) )
			throw new \Exception(
				"Unresolved reference in [$theOffset]: "
			   ."($value)." );													// !@! ==>
		
		//
		// Cast value.
		//
		$theIterator->offsetSet( $key, $value );
		
		return TRUE;																// ==>
	
	} // castReference.

	 

} // class PersistentObject.


?>
