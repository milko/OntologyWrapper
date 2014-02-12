<?php

/**
 * Tag.php
 *
 * This file contains the definition of the {@link Tag} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Term;
use OntologyWrapper\TagObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *										Tag.php											*
 *																						*
 *======================================================================================*/

/**
 * Tag
 *
 * This class implements a persistent {@link TagObject} instance, the class concentrates on
 * implementing all the necessary elements to ensure persistence to instances of this class
 * and referential integrity.
 *
 * The object is considered initialised, {@link isInited()}, if it has at least the terms
 * path, {@link kTAG_TERMS}, with an odd number of elements, the data type,
 * {@link kTAG_DATA_TYPE}, and the label, {@link kTAG_LABEL}.
 *
 * In this class we set the sequence number, {@link kTAG_SEQ}, by retrieving a 
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */
class Tag extends TagObject
{
	/**
	 * Persistent trait.
	 *
	 * We use this trait to make objects of this class persistent.
	 */
	use	\OntologyWrapper\PersistentTrait;

	/**
	 * Sequences selector.
	 *
	 * This constant holds the <i>sequences</i> name for tags; this constant is also used as
	 * the <i>default collection name</i> in which objects of this class are stored.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_tags';

		

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
		$count = $this->TermCount();
		$this->isInited( $count &&
						 ($count % 2) &&
						 \ArrayObject::offsetExists( kTAG_DATA_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_LABEL ) );

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *							PUBLIC REFERENCE RESOLUTION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadTerms																		*
	 *==================================================================================*/

	/**
	 * Load term objects list
	 *
	 * This method can be used to resolve the list of terms into a list of objects.
	 *
	 * The method will return an array, indexed by term native identifier, containing the
	 * resolved objects.
	 *
	 * If any term cannot be resolved, the method will raise an exception.
	 *
	 * @access protected
	 * @return array				List of term objects or <tt>NULL</tt>.
	 */
	public function loadTerms()
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
				// Check terms.
				//
				if( \ArrayObject::offsetExists( kTAG_TERMS ) )
				{
					//
					// Init local storage.
					//
					$terms = \ArrayObject::offsetGet( kTAG_TERMS );
					$collection
						= $this->mCollection
							->Parent()
							->Collection( Term::kSEQ_NAME );
					$collection->openConnection();
					
					//
					// Iterate terms.
					//
					$result = Array();
					foreach( $terms as $term )
					{
						//
						// Check array.
						//
						if( ! array_key_exists( $term, $result ) )
						{
							//
							// Resolve reference.
							//
							$object = $collection->resolve( $term );
							if( $object === NULL )
								throw new \Exception(
									"Unable to resolve [$term] term." );		// !@! ==>
							
							//
							// Set object.
							//
							$result[ $term ] = $object;
						
						} // Not there already.
					
					} // Iterating terms.
					
					return $result;													// ==>
					
				} // Has terms.
			
			} // Has collection.
		
		} // Committed.
		
		return NULL;																// ==>
	
	} // loadTerms.

	 
	/*===================================================================================
	 *	loadDataTypes																	*
	 *==================================================================================*/

	/**
	 * Load data type objects list
	 *
	 * This method can be used to resolve the list of data types into a list of objects.
	 *
	 * The method will return an array, indexed by term native identifier, containing the
	 * resolved objects.
	 *
	 * If any term cannot be resolved, the method will raise an exception.
	 *
	 * @access protected
	 * @return array				List of term objects or <tt>NULL</tt>.
	 */
	public function loadDataTypes()
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
				// Check terms.
				//
				if( \ArrayObject::offsetExists( kTAG_DATA_TYPE ) )
				{
					//
					// Init local storage.
					//
					$terms = \ArrayObject::offsetGet( kTAG_DATA_TYPE );
					$collection
						= $this->mCollection
							->Parent()
							->Collection( Term::kSEQ_NAME );
					$collection->openConnection();
					
					//
					// Iterate terms.
					//
					$result = Array();
					foreach( $terms as $term )
					{
						//
						// Check array.
						//
						if( ! array_key_exists( $term, $result ) )
						{
							//
							// Resolve reference.
							//
							$object = $collection->resolve( $term );
							if( $object === NULL )
								throw new \Exception(
									"Unable to resolve [$term] term." );		// !@! ==>
							
							//
							// Set object.
							//
							$result[ $term ] = $object;
						
						} // Not there already.
					
					} // Iterating terms.
					
					return $result;													// ==>
					
				} // Has terms.
			
			} // Has collection.
		
		} // Committed.
		
		return NULL;																// ==>
	
	} // loadDataTypes.

	 
	/*===================================================================================
	 *	loadDataKinds																	*
	 *==================================================================================*/

	/**
	 * Load data type objects list
	 *
	 * This method can be used to resolve the list of data types into a list of objects.
	 *
	 * The method will return an array, indexed by term native identifier, containing the
	 * resolved objects.
	 *
	 * If any term cannot be resolved, the method will raise an exception.
	 *
	 * @access protected
	 * @return array				List of term objects or <tt>NULL</tt>.
	 */
	public function loadDataKinds()
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
				// Check terms.
				//
				if( \ArrayObject::offsetExists( kTAG_DATA_KIND ) )
				{
					//
					// Init local storage.
					//
					$terms = \ArrayObject::offsetGet( kTAG_DATA_KIND );
					$collection
						= $this->mCollection
							->Parent()
							->Collection( Term::kSEQ_NAME );
					$collection->openConnection();
					
					//
					// Iterate terms.
					//
					$result = Array();
					foreach( $terms as $term )
					{
						//
						// Check array.
						//
						if( ! array_key_exists( $term, $result ) )
						{
							//
							// Resolve reference.
							//
							$object = $collection->resolve( $term );
							if( $object === NULL )
								throw new \Exception(
									"Unable to resolve [$term] term." );		// !@! ==>
							
							//
							// Set object.
							//
							$result[ $term ] = $object;
						
						} // Not there already.
					
					} // Iterating terms.
					
					return $result;													// ==>
					
				} // Has terms.
			
			} // Has collection.
		
		} // Committed.
		
		return NULL;																// ==>
	
	} // loadDataKinds.

		

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
	 * In this class we collect the terms list, the data types and the data kinds.
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
		// Get terms collection.
		//
		$collection
			= $this->mCollection
				->Parent()
				->Collection( Term::kSEQ_NAME );
		$collection->openConnection();

		//
		// Check terms.
		//
		if( \ArrayObject::offsetExists( kTAG_TERMS ) )
			$this->collectObjects(
				$theContainer,
				$collection,
				\ArrayObject::offsetGet( kTAG_TERMS ),
				Term::kSEQ_NAME,
				$doObject );

		//
		// Check data types.
		//
		if( \ArrayObject::offsetExists( kTAG_DATA_TYPE ) )
			$this->collectObjects(
				$theContainer,
				$collection,
				\ArrayObject::offsetGet( kTAG_DATA_TYPE ),
				Term::kSEQ_NAME,
				$doObject );

		//
		// Check data kinds.
		//
		if( \ArrayObject::offsetExists( kTAG_DATA_KIND ) )
			$this->collectObjects(
				$theContainer,
				$collection,
				\ArrayObject::offsetGet( kTAG_DATA_KIND ),
				Term::kSEQ_NAME,
				$doObject );
	
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
	 *	<li><b>$theIdentifier</b>: The objet native identifier or sequence number.
	 *	<li><b>$doAssert</b>: If <tt>TRUE</tt>, if the object is not matched, the method
	 *		will raise an exception; if <tt>FALSE</tT>, the method will return
	 *		<tt>NULL</tt>.
	 * </ul>
	 *
	 * We implement this method to match objects in the tags collection by matching string
	 * identifiers with the native identifier and integer identifiers with the sequence
	 * number.
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
		$object = ( is_int( $theIdentifier ) )
				? $theConnection->resolve( $theIdentifier, kTAG_SEQ, TRUE )
				: $theConnection->resolve( (string) $theIdentifier, kTAG_NID, TRUE );
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
	 * We then set the native identifier, if not yet filled, with the global identifier
	 * generated by the {@link __toString()} method.
	 *
	 * We finally set the sequence number, {@link kTAG_SEQ}, if it is not yet set by
	 * requesting it from the database of the current object's container.
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
			// Set native identifier.
			//
			if( ! \ArrayObject::offsetExists( kTAG_NID ) )
				\ArrayObject::offsetSet( kTAG_NID, $this->__toString() );
			
			//
			// Set sequence number.
			//
			if( ! \ArrayObject::offsetExists( kTAG_SEQ ) )
				$this->offsetSet(
					kTAG_SEQ,
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
	 * In this class we set the newly inserted or updated tag into the cache, or delete it
	 * from the cache if deleting.
	 *
	 * @param bitfield				$theOperation		Operation code.
	 *
	 * @access protected
	 */
	protected function postCommit( $theOperation = 0x00 )
	{
		//
		// Check cache.
		//
		if( (! isset( $_SESSION ))
		 || (! array_key_exists( kSESSION_DDICT, $_SESSION )) )
			throw new \Exception(
				"Tag cache is not set in the session." );						// !@! ==>
		
		//
		// Init local storage.
		//
		$nid = (string) $this->offsetGet( kTAG_NID );
		$seq = (int) $this->offsetGet( kTAG_SEQ );
		
		//
		// Set cache.
		//
		if( $theOperation & 0x01 )
		{
			//
			// Set tag identifier.
			//
			$_SESSION[ kSESSION_DDICT ]->setTagId( $nid, $seq );
		
			//
			// Set tag object.
			//
			$_SESSION[ kSESSION_DDICT ]->setTagObject( $seq, $this->getArrayCopy() );
		
		} // Saving.
		
		//
		// Delete cache.
		//
		else
		{
			//
			// Delete tag identifier.
			//
			$_SESSION[ kSESSION_DDICT ]->delTagId( $this->offsetGet( kTAG_NID ) );
		
			//
			// Set tag object.
			//
			$_SESSION[ kSESSION_DDICT ]->delTagObject( (int) $this->offsetGet( kTAG_SEQ ) );
		
		} // Saving.
	
	} // postCommit.

		

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
			  && $this->offsetExists( kTAG_SEQ )
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
	 * @see kTAG_TERMS kTAG_DATA_TYPE kTAG_LABEL
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method to resolve offset.
		//
		parent::postOffsetSet( $theOffset, $theValue );
		
		//
		// Set initialised status.
		//
		$count = $this->TermCount();
		$this->isInited( $count &&
						 ($count % 2) &&
						 \ArrayObject::offsetExists( kTAG_DATA_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_LABEL ) );
	
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
	 * @see kTAG_DATA_TYPE kTAG_LABEL
	 */
	protected function postOffsetUnset( &$theOffset )
	{
		//
		// Call parent method to resolve offset.
		//
		parent::postOffsetUnset( $theOffset );
		
		//
		// Set initialised status.
		//
		$count = $this->TermCount();
		$this->isInited( $count &&
						 ($count % 2) &&
						 \ArrayObject::offsetExists( kTAG_DATA_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_LABEL ) );
	
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
	 * {@link kTAG_SEQ}, {@link kTAG_TERMS}, {@link kTAG_DATA_TYPE} and the
	 * {@link kTAG_DATA_KIND} offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_SEQ kTAG_TERMS kTAG_DATA_TYPE kTAG_DATA_KIND
	 */
	protected function lockedOffsets()
	{
		return array_merge( static::$sInternalTags,
							array( kTAG_PID,
								   kTAG_SEQ, kTAG_TERMS,
								   kTAG_DATA_TYPE, kTAG_DATA_KIND ) );				// ==>
	
	} // lockedOffsets.

	 

} // class Tag.


?>
