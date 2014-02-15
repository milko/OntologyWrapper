<?php

/**
 * Edge.php
 *
 * This file contains the definition of the {@link Edge} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\PersistentObject;
use OntologyWrapper\Node;
use OntologyWrapper\Term;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *										Edge.php										*
 *																						*
 *======================================================================================*/

/**
 * Edge
 *
 * This class implements a <em>directed graph</em> by <em>relating a subject vertex</em>
 * with an <em>object vertex</em> through a <em>predicate</em>, the direction of the
 * relationship is <em>from the subject to the object</em>.
 *
 * The vertices of this relatonship, the subject and object, are {@link Node} instance
 * references, while the relationship predicate is represented by a {@link Term}
 * instance reference.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. This required attribute holds
 *		a <em>string</em> which represents the <em>combination of the subject, predicate and
 *		object</em> of the relationship. This attribute must be managed with its offset,
 *		although in derived classes it will be set automatically.
 *	<li><tt>{@link kTAG_SUBJECT}</tt>: <em>Subject</em>. This attribute represents the
 *		<em>origin of the relationship</em>, it is an <em>integer</em> value representing
 *		the <em>reference to a {@link Node} instance</em>. This attribute must be
 *		managed with its offset.
 *	<li><tt>{@link kTAG_PREDICATE}</tt>: <em>Predicate</em>. This attribute represents the
 *		<em>type of relationship</em>, it is a <em>string</em> value representing the
 *		<em>reference to a {@link Term} instance</em>. This attribute must be managed
 *		with its offset.
 *	<li><tt>{@link kTAG_OBJECT}</tt>: <em>Object</em>. This attribute represents the
 *		<em>destination of the relationship</em>, it is an <em>integer</em> value
 *		representing the <em>reference to a {@link Node} instance</em>. This attribute
 *		must be managed with its offset.
 * </ul>
 *
 * The {@link __toString()} method will return the value stored in the native identifier,
 * if set, or the computed native identifier, which is the concatenation of the subject,
 * predicate and object references separated by the {@link kTOKEN_INDEX_SEPARATOR} token.
 *
 * Objects of this class feature a primary key which is not persistent: the vertices
 * referenced in the native identifier are integer sequences which depend on the order these
 * objects were inserted: this means that both {@link Node} and {@link Edge}
 * instances must be re-created when exported.
 *
 * Objects of this class can hold any additional attribute that is considered necessary or
 * useful to define and share the current node. In this class we define only those
 * attributes that constitute the core functionality of the object, derived classes will add
 * attributes specific to the domain in which the object will operate.
 *
 * The object is considered initialised, {@link isInited()}, if it has at least the subject,
 * predicate and object references.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 11/02/2014
 */
class Edge extends PersistentObject
{
	/**
	 * Default collection name.
	 *
	 * This constant provides the <i>default collection name</i> in which objects of this
	 * class are stored.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_edges';

		

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
	 * In this class we link the inited status with the presence of the subject, predicate
	 * and object.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @see kTAG_SUBJECT kTAG_PREDICATE kTAG_OBJECT
	 *
	 * @uses instantiateObject()
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_OBJECT ) &&
						 \ArrayObject::offsetExists( kTAG_SUBJECT ) &&
						 \ArrayObject::offsetExists( kTAG_PREDICATE ) );

	} // Constructor.

	 
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return global identifier</h4>
	 *
	 * The global identifier of the current object is represented by the subject, predicate
	 * and object references separated by the {@link kTOKEN_INDEX_SEPARATOR} token.
	 *
	 * @access public
	 * @return string				The global identifier.
	 */
	public function __toString()
	{
		//
		// Get relationship terms.
		//
		$terms = Array();
		$terms[] = $this->offsetGet( kTAG_SUBJECT );
		$terms[] = $this->offsetGet( kTAG_PREDICATE );
		$terms[] = $this->offsetGet( kTAG_OBJECT );
		
		return implode( kTOKEN_INDEX_SEPARATOR, $terms );							// ==>
	
	} // __toString.

		

/*=======================================================================================
 *																						*
 *							PUBLIC REFERENCE RESOLUTION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadSubject																		*
	 *==================================================================================*/

	/**
	 * Load subject vertex object
	 *
	 * This method can be used to resolve the subject vertex into an object, array or check
	 * whether the vertex exists.
	 *
	 * The method expects a single parameter that determines what the method should return:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: Return the node object; if it is not found, raise an exception.
	 *	<li><tt>TRUE</tt>: Return the node array; if it is not found, raise an exception.
	 *	<li><tt>NULL</tt>: Return the count of nodes matching the identifier (1 or 0).
	 * </ul>
	 *
	 * If the current object is not committed, if it doesn't have a collection, or if it
	 * doesn't have the subject, the method will return <tt>NULL</tt>.
	 *
	 * @param mixed					$asObject			Return object, array or count.
	 *
	 * @access protected
	 * @return mixed				Node object, array, count or <tt>NULL</tt>.
	 *
	 * @see kTAG_SUBJECT Node::kSEQ_NAME
	 *
	 * @uses loadOffsetReference()
	 */
	public function loadSubject( $asObject = TRUE )
	{
		return $this->loadOffsetReference(
					kTAG_SUBJECT, Node::kSEQ_NAME, $asObject );						// ==>
	
	} // loadSubject.

	 
	/*===================================================================================
	 *	loadPredicate																	*
	 *==================================================================================*/

	/**
	 * Load predicate object
	 *
	 * This method can be used to resolve the predicate into an object, array or check
	 * whether the predicate exists.
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
	 * doesn't have the predicate, the method will return <tt>NULL</tt>.
	 *
	 * @param mixed					$asObject			Return object, array or count.
	 *
	 * @access protected
	 * @return mixed				Term object, array, count or <tt>NULL</tt>.
	 *
	 * @see kTAG_PREDICATE Term::kSEQ_NAME
	 *
	 * @uses loadOffsetReference()
	 */
	public function loadPredicate( $asObject = TRUE )
	{
		return $this->loadOffsetReference(
					kTAG_PREDICATE, Term::kSEQ_NAME, $asObject );					// ==>
	
	} // loadPredicate.

	 
	/*===================================================================================
	 *	loadObject																		*
	 *==================================================================================*/

	/**
	 * Load object vertex object
	 *
	 * This method can be used to resolve the object vertex into an object, array or check
	 * whether the vertex exists.
	 *
	 * The method expects a single parameter that determines what the method should return:
	 *
	 * <ul>
	 *	<li><tt>TRUE</tt>: Return the node object; if it is not found, raise an exception.
	 *	<li><tt>TRUE</tt>: Return the node array; if it is not found, raise an exception.
	 *	<li><tt>NULL</tt>: Return the count of nodes matching the identifier (1 or 0).
	 * </ul>
	 *
	 * If the current object is not committed, if it doesn't have a collection, or if it
	 * doesn't have the object vertex, the method will return <tt>NULL</tt>.
	 *
	 * @param mixed					$asObject			Return object, array or count.
	 *
	 * @access protected
	 * @return mixed				Node object, array, count or <tt>NULL</tt>.
	 *
	 * @see kTAG_OBJECT Node::kSEQ_NAME
	 *
	 * @uses loadOffsetReference()
	 */
	public function loadObject( $asObject = TRUE )
	{
		return $this->loadOffsetReference(
					kTAG_OBJECT, Node::kSEQ_NAME, $asObject );						// ==>
	
	} // loadObject.

		

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
	 * In this class we collect the subject, predicate and object.
	 *
	 * @param reference				$theContainer		Receives objects.
	 * @param boolean				$doObject			<tt>TRUE</tt> load objects.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SUBJECT kTAG_OBJECT kTAG_PREDICATE
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
		// Handle subject and object.
		//
		if( \ArrayObject::offsetExists( kTAG_SUBJECT )
		 || \ArrayObject::offsetExists( kTAG_OBJECT ) )
		{
			//
			// Get tags collection.
			//
			$collection
				= $this->mCollection
					->Parent()
					->Collection( Node::kSEQ_NAME );
			$collection->openConnection();

			//
			// Get subject.
			//
			if( \ArrayObject::offsetExists( kTAG_SUBJECT ) )
				$this->collectObjects(
					$theContainer,
					$collection,
					\ArrayObject::offsetGet( kTAG_SUBJECT ),
					Tag::kSEQ_NAME,
					$doObject );

			//
			// Get object.
			//
			if( \ArrayObject::offsetExists( kTAG_OBJECT ) )
				$this->collectObjects(
					$theContainer,
					$collection,
					\ArrayObject::offsetGet( kTAG_OBJECT ),
					Tag::kSEQ_NAME,
					$doObject );
		
		} // Has subject and/or object.
		
		//
		// Handle predicate.
		//
		if( \ArrayObject::offsetExists( kTAG_PREDICATE ) )
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
				\ArrayObject::offsetGet( kTAG_PREDICATE ),
				Term::kSEQ_NAME,
				$doObject );
		
		} // Has predicate.
	
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
	 * or the provided array of subject, predicate, object references and return an instance
	 * of the originally committed class.
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
	 * We implement this method to match objects in the edges collection.
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
		// Normalise identifier.
		//
		if( is_array( $theIdentifier ) )
			$theIdentifier = implode( kTOKEN_INDEX_SEPARATOR, $theIdentifier );
		
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
	 * We then set the native identifier, if not yet filled, with the global identifier
	 * generated by the {@link __toString()} method.
	 *
	 * When deleting we check whether the object has its native identifier.
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
	 * @uses __toString()
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
	 * In this class we ensure the object has the native identifier, {@link kTAG_NID}.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 *
	 * @see kTAG_NID
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
	 * In this class we cast the value of the relationship vertices into node reference, and
	 * the value of the predicate into a term reference, if provided as objects; we also
	 * ensure the provided objects arer of the correct type.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SUBJECT kTAG_PREDICATE kTAG_OBJECT
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
			// Intercept node.
			//
			if( ($theOffset == kTAG_SUBJECT)
			 || ($theOffset == kTAG_OBJECT) )
			{
				//
				// Handle objects.
				//
				if( is_object( $theValue ) )
				{
					//
					// If term, get its reference.
					//
					if( $theValue instanceof Node )
						$theValue = $theValue->Reference();
				
					//
					// If not a term, complain.
					//
					else
						throw new \Exception(
							"Unable to set edge vertex: "
						   ."provided an object other than a node." );			// !@! ==>
			
				} // Object.
			
				//
				// Cast to integer.
				//
				else
					$theValue = (int) $theValue;
			
			} // Setting tag.
			
			//
			// Intercept term.
			//
			if( $theOffset == kTAG_PREDICATE )
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
							"Unable to set predicate: "
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
	 * In this class we set the {@link isInited()} status.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_SUBJECT kTAG_PREDICATE kTAG_OBJECT
	 *
	 * @uses isInited()
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_OBJECT ) &&
						 \ArrayObject::offsetExists( kTAG_SUBJECT ) &&
						 \ArrayObject::offsetExists( kTAG_PREDICATE ) );
	
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
	 * @see kTAG_SUBJECT kTAG_PREDICATE kTAG_OBJECT
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_OBJECT ) &&
						 \ArrayObject::offsetExists( kTAG_SUBJECT ) &&
						 \ArrayObject::offsetExists( kTAG_PREDICATE ) );
	
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
	 * In this class we add the subject, predicate and object offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_SUBJECT kTAG_PREDICATE kTAG_OBJECT
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
							array( kTAG_OBJECT,
								   kTAG_SUBJECT,
								   kTAG_PREDICATE ) );								// ==>
	
	} // lockedOffsets.

	 

} // class Edge.


?>
