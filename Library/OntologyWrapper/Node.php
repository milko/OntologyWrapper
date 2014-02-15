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
 *							PUBLIC REFERENCE RESOLUTION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadTag																			*
	 *==================================================================================*/

	/**
	 * Load tag object
	 *
	 * This method can be used to resolve the tag into an object, array or check whether the
	 * tag exists.
	 *
	 * The method expects a single parameter that determines what the method should return:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: Return the namespace object; if it is not found, raise an
	 *		exception.
	 *	<li><tt>TRUE</tt>: Return the namespace array; if it is not found, raise an
	 *		exception.
	 *	<li><tt>NULL</tt>: Return the count of namespaces matching the identifier (1 or 0).
	 * </ul>
	 *
	 * If the current object is not committed, if it doesn't have a collection, or if it
	 * doesn't have the tag, the method will return <tt>NULL</tt>.
	 *
	 * @param mixed					$asObject			Return object, array or count.
	 *
	 * @access protected
	 * @return mixed				Tag object, array, count or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_TAG, Tag::kSEQ_NAME
	 *
	 * @uses loadOffsetReference()
	 */
	public function loadTag( $asObject = TRUE )
	{
		return $this->loadOffsetReference(
					kTAG_TAG, Tag::kSEQ_NAME, $asObject );							// ==>
	
	} // loadTag.

	 
	/*===================================================================================
	 *	loadTerm																		*
	 *==================================================================================*/

	/**
	 * Load term object
	 *
	 * This method can be used to resolve the term into an object, array or check whether
	 * the term exists.
	 *
	 * The method expects a single parameter that determines what the method should return:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: Return the term object; if it is not found, raise an exception.
	 *	<li><tt>TRUE</tt>: Return the term array; if it is not found, raise an exception.
	 *	<li><tt>NULL</tt>: Return the count of terms matching the identifier (1 or 0).
	 * </ul>
	 *
	 * If the current object is not committed, if it doesn't have a collection, or if it
	 * doesn't have the term, the method will return <tt>NULL</tt>.
	 *
	 * @param mixed					$asObject			Return object, array or count.
	 *
	 * @access protected
	 * @return mixed				Term object, array, count or <tt>NULL</tt>.
	 *
	 * @see kTAG_TERM Term::kSEQ_NAME
	 *
	 * @uses loadOffsetReference()
	 */
	public function loadTerm( $asObject = TRUE )
	{
		return $this->loadOffsetReference(
					kTAG_TERM, Term::kSEQ_NAME, $asObject );						// ==>
	
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
	 *
	 * @throws Exception
	 *
	 * @see kTAG_TAG kTAG_TERM
	 *
	 * @uses collectObjects()
	 */
	public function collectReferences( &$theContainer, $doObject = TRUE )
	{
		//
		// Call parent method.
		//
		parent::collectReferences( $theContainer, $doObject );
		
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
	 *
	 * @throws Exception
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
	 * @throws Exception
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
						$theValue = $theValue->Reference();
				
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
						$theValue = $theValue->Reference();
				
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
		return array_merge( static::$sInternalTags,
							array( kTAG_TAG, kTAG_TERM ) );							// ==>
	
	} // lockedOffsets.

	 

} // class Node.


?>
