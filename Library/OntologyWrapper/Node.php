<?php

/**
 * Node.php
 *
 * This file contains the definition of the {@link Node} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\PersistentObject;
use OntologyWrapper\Tag;
use OntologyWrapper\Term;
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
 * A node is a <em>vertex in a graph structure</em>, nodes reference
 * <em>{@link Term}</em> and <em>{@link Tag</em> instances, when referencing a
 * term, nodes are used to build <em>ontologies</em>, <em>type definitions</em> and
 * <em>controlloed vocabularies</em>; when referencing tags they are used to build <em>data
 * structures</em>, <em>input and output templates</em> and <em>search forms</em>.
 *
 * Node objects, along with edge objects, represent the presentation layer of the ontology,
 * users compose and consult network structures through these objects.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. This required attribute holds
 *		an <em>integer serial number</em>, nodes do not have a unique persistent identifier,
 *		since they act as references and because you may have more than one node referencing
 *		the same term or property. The native identifier is assigned automatically.
 *	<li><tt>{@link kTAG_TERM}</tt>: <em>Term</em>. This attribute is a <em>string</em> that
 *		holds a reference to the <em>term object</em> that the current node <em>represents
 *		in a graph structure</em>. If this offset is set, the {@link kTAG_TAG} offset must
 *		be omitted. This attribute must be managed with its offset.
 *	<li><tt>{@link kTAG_TAG}</tt>: <em>Tag</em>. This attribute is a <em>string</em> that
 *		holds a reference to the <em>tag object</em> that the current node <em>represents
 *		in a graph structure</em>. If this offset is set, the {@link kTAG_TERM} offset must
 *		be omitted. This attribute must be managed with its offset.
 * </ul>
 *
 * The {@link __toString()} method will return the value stored in the {@link kTAG_TERM} or
 * the {@link kTAG_TAG} offset. This value represents the node persistent identifier, which
 * is not, however, unique.
 *
 * Nodes cannot be uniquely identified via a persistent identifier, because more than one
 * node may share the same term or tag, this means that when searching for nodes you should
 * rely more on traversing a graph path, rather than selecting an object from a list.
 *
 * Objects of this class can hold any additional attribute that is considered necessary or
 * useful to define and share the current node. In this class we define only those
 * attributes that constitute the core functionality of the object, derived classes will add
 * attributes specific to the domain in which the object will operate.
 *
 * The object is considered initialised, {@link isInited()}, if it has at least the term
 * reference, {@link kTAG_TERM}, or the tag reference, {@link kTAG_TAG}.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */
class Node extends PersistentObject
{
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
	 * In this class we link the inited status with the presence of the tag or the term.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @see kTAG_TAG kTAG_TERM
	 *
	 * @uses isInited()
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Load object with contents.
		//
		parent::__construct( $theContainer, $theIdentifier );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_TAG ) ||
						 \ArrayObject::offsetExists( kTAG_TERM ) );

	} // Constructor.

	 
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return global identifier</h4>
	 *
	 * If the object holds the term reference, this will be returned; if it holds the tag
	 * reference, it will be returned; if none of these are set, the method will return an
	 * empty string.
	 *
	 * @access public
	 * @return string				The persistent identifier.
	 */
	public function __toString()
	{
		//
		// Get term.
		//
		if( \ArrayObject::offsetExists( kTAG_TERM ) )
			return \ArrayObject::offsetGet( kTAG_TERM );							// ==>
		
		//
		// Get tag.
		//
		if( \ArrayObject::offsetExists( kTAG_TAG ) )
			return \ArrayObject::offsetGet( kTAG_TAG );								// ==>
		
		return '';																	// ==>
	
	} // __toString.

		

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
	 * In this class we return the metadata database.
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
		//
		// Get metadata database.
		//
		$database = $theWrapper->Metadata();
		if( $database instanceof DatabaseObject )
		{
			//
			// Open connection.
			//
			if( $doOpen )
				$database->openConnection();
			
			return $database;														// ==>
		
		} // Retrieved metadata database.
		
		//
		// Raise exception.
		//
		if( $doAssert )
			throw new \Exception(
				"Unable to resolve database: "
			   ."missing metadata reference in wrapper." );						// !@! ==>
		
		return NULL;																// ==>
	
	} // ResolveDatabase.

		

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
	 * In this class we cast the value of the term into a term reference, or the value of a
	 * tag in a tag reference; we also ensure that provided objects are of the correct
	 * class.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_TAG kTAG_TERM
	 */
	protected function preOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method to resolve offset.
		//
		$ok = parent::preOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
		{
			//
			// Intercept tag.
			//
			if( $theOffset == kTAG_TAG )
			{
				//
				// Handle objects.
				//
				if( is_object( $theValue ) )
				{
					//
					// If term, get its reference.
					//
					if( $theValue instanceof Tag )
						$theValue = $theValue->reference();
				
					//
					// If not a term, complain.
					//
					else
						throw new \Exception(
							"Unable to set tag reference: "
						   ."provided an object other than a tag." );			// !@! ==>
			
				} // Object.
			
				//
				// Cast to string.
				//
				else
					$theValue = (string) $theValue;
			
			} // Setting tag.
			
			//
			// Intercept term.
			//
			if( $theOffset == kTAG_TERM )
			{
				//
				// Handle objects.
				//
				if( is_object( $theValue ) )
				{
					//
					// If term, get its reference.
					//
					if( $theValue instanceof Term )
						$theValue = $theValue->reference();
				
					//
					// If not a term, complain.
					//
					else
						throw new \Exception(
							"Unable to set term reference: "
						   ."provided an object other than a term." );			// !@! ==>
			
				} // Object.
			
				//
				// Cast to string.
				//
				else
					$theValue = (string) $theValue;
			
			} // Setting term.
			
		} // Passed preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * In this class we delete the tag when we set the term and vice-versa.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_TAG kTAG_TERM
	 *
	 * @uses isInited()
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Handle new tag.
		//
		if( $theOffset == kTAG_TAG )
			$this->offsetUnset( kTAG_TERM );
	
		//
		// Handle new term.
		//
		if( $theOffset == kTAG_TERM )
			$this->offsetUnset( kTAG_TAG );
	
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
	 *
	 * @uses isInited()
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
 *								PROTECTED PRE-COMMIT UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitObjectTags																*
	 *==================================================================================*/

	/**
	 * Load object tags
	 *
	 * In this class we shadow this method since we do not keep track of object tags.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 *
	 * @access protected
	 */
	protected function preCommitObjectTags( &$theTags )									   {}

	 
	/*===================================================================================
	 *	preCommitObjectIdentifiers														*
	 *==================================================================================*/

	/**
	 * Load object identifiers
	 *
	 * In this class we set the native identifier with the sequence number.
	 *
	 * @access protected
	 */
	protected function preCommitObjectIdentifiers()
	{
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $this->dictionary(), TRUE ) );
		
		//
		// Set sequence number.
		//
		if( ! \ArrayObject::offsetExists( kTAG_NID ) )
			$this->offsetSet(
				kTAG_NID,
				$collection->getSequenceNumber(
					static::kSEQ_NAME ) );
	
	} // preCommitObjectIdentifiers.

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-COMMIT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postCommitTagOffsets															*
	 *==================================================================================*/

	/**
	 * Update tag offsets
	 *
	 * In this class we shadow this method since we do not keep track of tag offsets.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 *
	 * @access protected
	 */
	protected function postCommitTagOffsets( &$theTags )								   {}

		

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
	 * In this class we ensure the object has the native identifier, {@link kTAG_NID}.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 *
	 * @uses isReady()
	 * @uses isInited()
	 */
	protected function isReady()
	{
		return ( parent::isReady()
			  && $this->offsetExists( kTAG_NID ) );									// ==>
	
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
	 * In this class we return the {@link kTAG_TAG} and the {@link kTAG_TERM} offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_TAG kTAG_TERM
	 */
	protected function lockedOffsets()
	{
		return array_merge( $this->InternalOffsets(),
							array( kTAG_TAG, kTAG_TERM ) );							// ==>
	
	} // lockedOffsets.

	 

} // class Node.


?>
