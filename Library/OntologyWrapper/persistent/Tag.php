<?php

/**
 * Tag.php
 *
 * This file contains the definition of the {@link Tag} class.
 */

namespace OntologyWrapper\persistent;

use OntologyWrapper\TagObject;

/*=======================================================================================
 *																						*
 *										Tag.php											*
 *																						*
 *======================================================================================*/

/**
 * Tag
 *
 * This class implements a persistent {@link TagObject} instance, the class concentrates on
 * implementing all the necessary elements to ensure persistence to instances of this class.
 *
 * The object is considered initialised, {@link isInited()}, if it has at least the data
 * type, {@link kTAG_DATA_TYPE}, and the label, {@link kTAG_LABEL}.
 *
 * The specific ontology related functionality will be implemented by a derived class.
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
	 * This constant holds the <i>sequences</i> name for tags.
	 *
	 * @var string
	 */
	const kSEQ_NAME = 'TAG';

		

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
		parent::__construct( $this->instantiateObject( $theContainer, $theIdentifier ) );

	} // Constructor.

		

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
	 * In this class we set the native identifier, {@link kTAG_NID}, with the sequence
	 * number.
	 *
	 * Note that if we reach this point we know the current object has a collection.
	 *
	 * @access protected
	 */
	protected function preCommit()
	{
		//
		// Set native identifier if not there.
		//
		if( ! $this->offsetExists( kTAG_NID ) )
			$this->offsetSet(
				kTAG_NID,
				$this->mCollection->getSequenceNumber(
					self::kSEQ_NAME ) );
	
	} // preCommit.

	 
	/*===================================================================================
	 *	postCommit																		*
	 *==================================================================================*/

	/**
	 * Cleanup object after commit
	 *
	 * In this class we do nothing... yet.
	 *
	 * @access protected
	 */
	protected function postCommit(){}

		

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
	 * global identifier, {@linkl kTAG_GID}, the data type, {@link kTAG_DATA_TYPE}, and the
	 * label, {@link kTAG_LABEL}.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 */
	protected function isReady()
	{
		return ( parent::isReady()
			  && $this->isInited()
			  && $this->offsetExists( kTAG_GID )
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
	 * @see kTAG_DATA_TYPE kTAG_LABEL
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_DATA_TYPE ) &&
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_DATA_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_LABEL ) );
	
	} // postOffsetUnset.

	 

} // class Tag.


?>
