<?php

/**
 * Tag.php
 *
 * This file contains the definition of the {@link Tag} class.
 */

namespace OntologyWrapper;

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
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveCollection																*
	 *==================================================================================*/

	/**
	 * Resolve the collection
	 *
	 * In this class we use the {@link kSEQ_NAME} constant as the default tags collection
	 * name.
	 *
	 * If the method is passed a {@link DatabaseObject} derived instance, the method will
	 * return a collection for that database with the {@link kSEQ_NAME} name.
	 *
	 * If the method is passed any other kind of value it will let its parent handle it.
	 *
	 * @param ConnectionObject		$theConnection		Persistent store.
	 *
	 * @access protected
	 * @return CollectionObject		Collection or <tt>NULL</tt>.
	 */
	public function resolveCollection( ConnectionObject $theConnection )
	{
		//
		// Handle databases.
		//
		if( $theConnection instanceof DatabaseObject )
			return $theConnection->Collection( static::kSEQ_NAME );					// ==>
		
		return parent::resolveCollection( $theConnection );							// ==>
	
	} // resolveConnection.

		

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
	 * In this class we add the sequence number, the terms list, the data type and the data
	 * kind offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_SEQ kTAG_TERMS kTAG_DATA_TYPE kTAG_DATA_KIND
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
							array( kTAG_SEQ,
								   kTAG_TERMS,
								   kTAG_DATA_TYPE,
								   kTAG_DATA_KIND ) );								// ==>
	
	} // lockedOffsets.

	 

} // class Tag.


?>
