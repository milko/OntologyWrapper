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
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveCollection																*
	 *==================================================================================*/

	/**
	 * Resolve the collection
	 *
	 * In this class we use the {@link kSEQ_NAME} constant as the default terms collection
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
	 * In this class we add the term and tag offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_TAG kTAG_TERM
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
							array( kTAG_TAG,
								   kTAG_TERM ) );									// ==>
	
	} // lockedOffsets.

	 

} // class Node.


?>
