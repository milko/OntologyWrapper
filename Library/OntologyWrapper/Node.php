<?php

/**
 * Node.php
 *
 * This file contains the definition of the {@link Node} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Tag;
use OntologyWrapper\Term;
use OntologyWrapper\NodeObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *										Node.php										*
 *																						*
 *======================================================================================*/

/**
 * Node
 *
 * This class implements a persistent {@link NodeObject} instance, the class concentrates on
 * implementing all the necessary elements to ensure persistence to instances of this class
 * and referential integrity.
 *
 * The object is considered initialised, {@link isInited()}, if it has at least the term
 * reference, {@link kTAG_TERM}, or the tag reference, {@link kTAG_TAG}.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */
class Node extends NodeObject
{
	/**
	 * Persistent trait.
	 *
	 * We use this trait to make objects of this class persistent.
	 */
	use	\OntologyWrapper\PersistentTrait;

	/**
	 * Default collection name.
	 *
	 * This constant provides the <i>default collection name</i> in which objects of this
	 * class are stored.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_nodes';

		

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
	 * This constructor is standard for all persistent classes, we do nothing special here.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @uses instantiateObject()
	 *
	 * @see kTAG_TAG kTAG_TERM
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Load object with contents.
		//
		parent::__construct( $this->instantiateObject( $theContainer, $theIdentifier ) );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_TAG ) ||
						 \ArrayObject::offsetExists( kTAG_TERM ) );

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *							PUBLIC REFERENCE RESOLUTION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadTag																			*
	 *==================================================================================*/

	/**
	 * Load tag object
	 *
	 * This method can be used to resolve the tag into an object.
	 *
	 * The method will return the tag object if the operation succeeded and <tt>NULL</tt> if
	 * the object is not committed, if the object does not hold a collection reference, or
	 * if the object has no tag.
	 *
	 * If the tag cannot be resolved, the method will raise an exception.
	 *
	 * @access protected
	 * @return Tag					Resolved reference or <tt>NULL</tt>.
	 */
	public function loadTag()
	{
		//
		// Check if committed.
		//
		if( $this->isCommitted() )
		{
			//
			// Check collection.
			//
			if( $this->mCollection !== NULL )
			{
				//
				// Check namespace.
				//
				if( \ArrayObject::offsetExists( kTAG_TAG ) )
				{
					//
					// Get tags collection.
					//
					$collection
						= $this->mCollection
							->Parent()
							->Collection( Tag::kSEQ_NAME );
					$collection->openConnection();
					
					//
					// Resolve reference.
					//
					$id = \ArrayObject::offsetGet( kTAG_TAG );
					$object = $collection->resolve( $id );
					if( $object === NULL )
						throw new \Exception(
							"Unable to resolve [$id] tag." );					// !@! ==>
					
					return $object;													// ==>
					
				} // Has tag.
			
			} // Has collection.
		
		} // Committed.
		
		return NULL;																// ==>
	
	} // loadTag.

	 
	/*===================================================================================
	 *	loadTerm																		*
	 *==================================================================================*/

	/**
	 * Load term object
	 *
	 * This method can be used to resolve the term into an object.
	 *
	 * The method will return the term object if the operation succeeded and <tt>NULL</tt>
	 * if the object is not committed, if the object does not hold a collection reference,
	 * or if the object has no term.
	 *
	 * If the term cannot be resolved, the method will raise an exception.
	 *
	 * @access protected
	 * @return Term					Resolved reference or <tt>NULL</tt>.
	 */
	public function loadTerm()
	{
		//
		// Check if committed.
		//
		if( $this->isCommitted() )
		{
			//
			// Check collection.
			//
			if( $this->mCollection !== NULL )
			{
				//
				// Check namespace.
				//
				if( \ArrayObject::offsetExists( kTAG_TERM ) )
				{
					//
					// Get tags collection.
					//
					$collection
						= $this->mCollection
							->Parent()
							->Collection( Term::kSEQ_NAME );
					$collection->openConnection();
					
					//
					// Resolve reference.
					//
					$id = \ArrayObject::offsetGet( kTAG_TERM );
					$object = $collection->resolve( $id );
					if( $object === NULL )
						throw new \Exception(
							"Unable to resolve [$id] term." );					// !@! ==>
					
					return $object;													// ==>
					
				} // Has tag.
			
			} // Has collection.
		
		} // Committed.
		
		return NULL;																// ==>
	
	} // loadTerm.

		

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
	 * In this class we collect the tag or term.
	 *
	 * @param reference				$theContainer		Receives objects.
	 * @param boolean				$doObject			<tt>TRUE</tt> load objects.
	 *
	 * @access public
	 */
	public function collectReferences( &$theContainer, $doObject = TRUE )
	{
		//
		// Check if committed.
		//
		if( ! $this->isCommitted() )
			throw new \Exception(
				"Unable to collect references: "
			   ."the object is not committed." );								// !@! ==>

		//
		// Check collection.
		//
		if( $this->mCollection === NULL )
			throw new \Exception(
				"Unable to collect references: "
			   ."the object has no collection." );								// !@! ==>
		
		//
		// Handle tag.
		//
		if( \ArrayObject::offsetExists( kTAG_TAG ) )
		{
			//
			// Get tags collection.
			//
			$collection
				= $this->mCollection
					->Parent()
					->Collection( Tag::kSEQ_NAME );
			$collection->openConnection();

			//
			// Get tag.
			//
			$this->collectObjects(
				$theContainer,
				$collection,
				\ArrayObject::offsetGet( kTAG_TAG ),
				Tag::kSEQ_NAME,
				$doObject );
		
		} // Has tag.
		
		//
		// Handle term.
		//
		elseif( \ArrayObject::offsetExists( kTAG_TERM ) )
		{
			//
			// Get tags collection.
			//
			$collection
				= $this->mCollection
					->Parent()
					->Collection( Term::kSEQ_NAME );
			$collection->openConnection();

			//
			// Get tag.
			//
			$this->collectObjects(
				$theContainer,
				$collection,
				\ArrayObject::offsetGet( kTAG_TERM ),
				Term::kSEQ_NAME,
				$doObject );
		
		} // Has tag.
	
	} // collectReferences.

		

/*=======================================================================================
 *																						*
 *								STATIC INSTANTIATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveObject																	*
	 *==================================================================================*/

	/**
	 * Resolve object
	 *
	 * This method can be used to statically instantiate an object from the provided data
	 * store, it will attempt to select the object matching the provided native identifier
	 * and return an instance of the originally committed class.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: The database or collection from which the object is to be
	 *		retrieved.
	 *	<li><b>$theIdentifier</b>: The objet native identifier.
	 *	<li><b>$doAssert</b>: If <tt>TRUE</tt>, if the object is not matched, the method
	 *		will raise an exception; if <tt>FALSE</tT>, the method will return
	 *		<tt>NULL</tt>.
	 * </ul>
	 *
	 * We implement this method to match objects in the nodes collection.
	 *
	 * @param ConnectionObject		$theConnection		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param boolean				$doAssert			Assert object.
	 *
	 * @access public
	 * @return OntologyObject		Object or <tt>NULL</tt>.
	 */
	static function ResolveObject( ConnectionObject $theConnection,
													$theIdentifier,
													$doAssert = TRUE )
	{
		//
		// Resolve collection.
		//
		if( $theConnection instanceof DatabaseObject )
		{
			//
			// Get collection.
			//
			$theConnection = $theConnection->Collection( self::kSEQ_NAME );
			
			//
			// Connect it.
			//
			$theConnection->openConnection();
		
		} // Database connection.
		
		//
		// Find object.
		//
		$object = $theConnection->resolve( $theIdentifier );
		if( $object !== NULL )
			return $object;															// ==>
		
		//
		// Assert.
		//
		if( $doAssert )
			throw new \Exception(
				"Unable to locate object." );									// !@! ==>
		
		return NULL;																// ==>
	
	} // ResolveObject.

		

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
	 * In this class we first check if the object is {@link isInited()}, if that is not the
	 * case, we raise an exception, since the object cannot be committed if not initialised.
	 *
	 * We then set the native identifier with a sequence number, if not yet set.
	 *
	 * When deleting we check whether the object has its native identifier.
	 *
	 * @param bitfield				$theOperation		Operation code.
	 *
	 * @access protected
	 *
	 * @throes Exception
	 */
	protected function preCommit( $theOperation = 0x00 )
	{
		//
		// Handle insert and update.
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
			// Set sequence number.
			//
			if( ! \ArrayObject::offsetExists( kTAG_NID ) )
				$this->offsetSet(
					kTAG_NID,
					$this->mCollection->getSequenceNumber(
						static::kSEQ_NAME ) );
		
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
	 *	postCommit																		*
	 *==================================================================================*/

	/**
	 * Cleanup object after commit
	 *
	 * In this class we do nothing... yet.
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
	 * In this class we ensure the object has the native identifier, {@link kTAG_NID}, the
	 * global identifier, {@linkl kTAG_PID}, the data type, {@link kTAG_DATA_TYPE}, and the
	 * label, {@link kTAG_LABEL}.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 */
	protected function isReady()
	{
		return ( parent::isReady()
			  && $this->isInited()
			  && $this->offsetExists( kTAG_NID ) );									// ==>
	
	} // isReady.

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * In this class we set the {@link isInited()} status.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_TAG kTAG_TERM
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method.
		//
		parent::postOffsetSet( $theOffset, $theValue );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_TAG ) ||
						 \ArrayObject::offsetExists( kTAG_TERM ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we set the {@link isInited()} status.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_TAG kTAG_TERM
	 */
	protected function postOffsetUnset( &$theOffset )
	{
		//
		// Call parent method.
		//
		parent::postOffsetUnset( $theOffset );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_TAG ) ||
						 \ArrayObject::offsetExists( kTAG_TERM ) );
	
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
	 * In this class we return the static {@link $sInternalTags} list, the {@link kTAG_PID},
	 * {@link kTAG_TAG} and the {@link kTAG_TERM} offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_TAG kTAG_TERM
	 */
	protected function lockedOffsets()
	{
		return array_merge( static::$sInternalTags,
							array( kTAG_PID,
								   kTAG_TAG, kTAG_TERM ) );							// ==>
	
	} // lockedOffsets.

	 

} // class Node.


?>
