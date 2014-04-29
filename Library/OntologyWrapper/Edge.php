<?php

/**
 * Edge.php
 *
 * This file contains the definition of the {@link Edge} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\PersistentObject;
use OntologyWrapper\Term;
use OntologyWrapper\Node;
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
 *	<li><tt>{@link kTAG_ID_GRAPH}</tt>: <em>Graph edge reference</em>. If the wrapper uses
 *		a graph database, this property will be used to reference the graph edge which
 *		represents the current edge; it is an integer value which is automatically managed.
 *	<li><tt>{@link kTAG_SUBJECT}</tt>: <em>Subject</em>. This attribute represents the
 *		<em>origin of the relationship</em>, it is an <em>integer</em> value representing
 *		the <em>reference to a {@link Node} instance</em>. This attribute must be
 *		managed with its offset.
 *	<li><tt>{@link kTAG_GRAPH_SUBJECT}</tt>: <em>Graph subject vertex</em>. If the wrapper
 *		uses a graph database, this property will hold the graph node reference of the
 *		subject vertex.
 *	<li><tt>{@link kTAG_PREDICATE}</tt>: <em>Predicate</em>. This attribute represents the
 *		<em>type of relationship</em>, it is a <em>string</em> value representing the
 *		<em>reference to a {@link Term} instance</em>. This attribute must be managed
 *		with its offset.
 *	<li><tt>{@link kTAG_OBJECT}</tt>: <em>Object</em>. This attribute represents the
 *		<em>destination of the relationship</em>, it is an <em>integer</em> value
 *		representing the <em>reference to a {@link Node} instance</em>. This attribute
 *		must be managed with its offset.
 *	<li><tt>{@link kTAG_GRAPH_OBJECT}</tt>: <em>Graph object vertex</em>. If the wrapper
 *		uses a graph database, this property will hold the graph node reference of the
 *		object vertex.
 *	<li><tt>{@link kTAG_NAME}</tt>: <em>Path name</em>. This attribute represents the edge
 *		<em>path</em> represented by the <em>persistent identifiers</em> of the referenced
 *		objects; this property is equivalent to the native identifier, except that the
 *		subject and object terms are represented by the native identifier of the referenced
 *		ovjects.
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
 *							PUBLIC MASTER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setAlias																		*
	 *==================================================================================*/

	/**
	 * Signal object as alias
	 *
	 * In this class we shadow this method, since there cannot be alias edges.
	 *
	 * @param boolean				$doSet				<tt>TRUE</tt> to set.
	 *
	 * @access public
	 */
	public function setAlias( $doSet = TRUE )											   {}

		

/*=======================================================================================
 *																						*
 *							PUBLIC OBJECT REFERENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getSubject																		*
	 *==================================================================================*/

	/**
	 * Get subject object
	 *
	 * This method will return the subject node object if any is set; if none are set,
	 * the method will return <tt>NULL</tt>; if the subject object cannot be found, the
	 * method will raise an exception.
	 *
	 * The parameter is the wrapper in which the current object is, or will be, stored: if
	 * the current object has the {@link dictionary()}, this parameter may be omitted; if
	 * the wrapper cannot be resolved, the method will raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 *
	 * @access public
	 * @return Node					Subject node or <tt>NULL</tt>
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SUBJECT
	 *
	 * @uses getReferenced()
	 */
	public function getSubject( $theWrapper = NULL )
	{
		return $this->getReferenced( kTAG_SUBJECT, $theWrapper );					// ==>
	
	} // getSubject.

	 
	/*===================================================================================
	 *	getPredicate																	*
	 *==================================================================================*/

	/**
	 * Get predicate object
	 *
	 * This method will return the predicate term object if any is set; if none are set,
	 * the method will return <tt>NULL</tt>; if the predicate object cannot be found, the
	 * method will raise an exception.
	 *
	 * The parameter is the wrapper in which the current object is, or will be, stored: if
	 * the current object has the {@link dictionary()}, this parameter may be omitted; if
	 * the wrapper cannot be resolved, the method will raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 *
	 * @access public
	 * @return Term					Predicate term or <tt>NULL</tt>
	 *
	 * @throws Exception
	 *
	 * @see kTAG_PREDICATE
	 *
	 * @uses getReferenced()
	 */
	public function getPredicate( $theWrapper = NULL )
	{
		return $this->getReferenced( kTAG_PREDICATE, $theWrapper );					// ==>
	
	} // getPredicate.

	 
	/*===================================================================================
	 *	getObject																		*
	 *==================================================================================*/

	/**
	 * Get object object
	 *
	 * This method will return the object node object if any is set; if none are set,
	 * the method will return <tt>NULL</tt>; if the object object cannot be found, the
	 * method will raise an exception.
	 *
	 * The parameter is the wrapper in which the current object is, or will be, stored: if
	 * the current object has the {@link dictionary()}, this parameter may be omitted; if
	 * the wrapper cannot be resolved, the method will raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 *
	 * @access public
	 * @return Node					Object node or <tt>NULL</tt>
	 *
	 * @throws Exception
	 *
	 * @see kTAG_OBJECT
	 *
	 * @uses getReferenced()
	 */
	public function getObject( $theWrapper = NULL )
	{
		return $this->getReferenced( kTAG_OBJECT, $theWrapper );					// ==>
	
	} // getObject.

		

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
 *								STATIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	CreateIndexes																	*
	 *==================================================================================*/

	/**
	 * Create indexes
	 *
	 * In this class we index the following offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_SUBJECT}</tt>: Relationship origin vertex.
	 *	<li><tt>{@link kTAG_PREDICATE}</tt>: Relationship predicate.
	 *	<li><tt>{@link kTAG_OBJECT}</tt>: Relationship destination vertex.
	 * </ul>
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 *
	 * @static
	 * @return CollectionObject		The collection.
	 */
	static function CreateIndexes( DatabaseObject $theDatabase )
	{
		//
		// Set parent indexes and retrieve collection.
		//
		$collection = parent::CreateIndexes( $theDatabase );
		
		//
		// Set graph node identifier index.
		//
		$collection->createIndex( array( kTAG_ID_GRAPH => 1 ),
								  array( "name" => "GRAPH" ) );
		
		//
		// Set subject index.
		//
		$collection->createIndex( array( kTAG_SUBJECT => 1 ),
								  array( "name" => "SUBJECT" ) );
		
		//
		// Set graph subject index.
		//
		$collection->createIndex( array( kTAG_GRAPH_SUBJECT => 1 ),
								  array( "name" => "SUBJECT-VERTEX" ) );
		
		//
		// Set predicate index.
		//
		$collection->createIndex( array( kTAG_PREDICATE => 1 ),
								  array( "name" => "PREDICATE" ) );
		
		//
		// Set object index.
		//
		$collection->createIndex( array( kTAG_OBJECT => 1 ),
								  array( "name" => "OBJECT" ) );
		
		//
		// Set graph object index.
		//
		$collection->createIndex( array( kTAG_GRAPH_OBJECT => 1 ),
								  array( "name" => "OBJECT-VERTEX" ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.

		

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
						$theValue = $theValue->reference();
				
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
						$theValue = $theValue->reference();
				
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
 *								PROTECTED PRE-COMMIT UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitObjectIdentifiers														*
	 *==================================================================================*/

	/**
	 * Load object identifiers
	 *
	 * In this class we set the native identifier, if not yet filled, with the global
	 * identifier generated by the {@link __toString()} method, we then check whether the
	 * edge already exists, in that case we raise an exception.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function preCommitObjectIdentifiers()	
	{
		//
		// Check if committed.
		//
		if( ! $this->isCommitted() )
		{
			//
			// Call parent method.
			//
			parent::preCommitObjectIdentifiers();
			
			//
			// Init local storage.
			//
			$id = $this->__toString();
			$dictionary = $this->mDictionary;
			$graph = $dictionary->Graph();
		
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $dictionary, TRUE ) );
		
			//
			// Check for duplicates.
			//
			if( $collection->matchOne( array( kTAG_NID => $id ), kQUERY_COUNT ) )
				throw new \Exception(
					"Duplicate edge object [$id]." );							// !@! ==>
		
			//
			// Set native identifier.
			//
			if( ! \ArrayObject::offsetExists( kTAG_NID ) )
				\ArrayObject::offsetSet( kTAG_NID, $id );
		
			//
			// Set path name.
			//
			if( ! \ArrayObject::offsetExists( kTAG_NAME ) )
				\ArrayObject::offsetSet( kTAG_NAME, $this->getPathName() );
		
		} // Not committed.
	
	} // preCommitObjectIdentifiers.

		

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

		

/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getReferenced																	*
	 *==================================================================================*/

	/**
	 * Get referenced object
	 *
	 * This method will return either the {@link kTAG_SUBJECT}, {@link kTAG_PREDICATE}, or
	 * the {@link kTAG_OBJECT} objects. If the requested property is not set, the method
	 * will return <tt>NULL</tt>; if the property is set as an object, the method will
	 * return it; if the referenced object cannot be found, the method will raise an
	 * exception.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffset</b>: This parameter expects one of the following:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_SUBJECT}</tt>: The edge subject.
	 *		<li><tt>{@link kTAG_PREDICATE}</tt>: The edge predicate.
	 *		<li><tt>{@link kTAG_OBJECT}</tt>: The edge object.
	 *	 </ul>
	 *		Any other value will trigger an exception.
	 *	<li><b>$theWrapper</b>: This parameter is the object's wrapper, if the current
	 *		object has the {@link dictionary()}, this parameter may be omitted; if the
	 *		wrapper cannot be resolved, the method will raise an exception.
	 * </ul>
	 *
	 * @param string				$theOffset			Subject, predicate or object offset.
	 * @param Wrapper				$theWrapper			Wrapper.
	 *
	 * @access protected
	 * @return PersistentObject		Subject or object node, predicate term or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	protected function getReferenced( $theOffset, $theWrapper = NULL )
	{
		//
		// Resolve wrapper.
		//
		if( $theWrapper === NULL )
		{
			//
			// Get current object's wrapper.
			//
			$theWrapper = $this->mDictionary;
			
			//
			// Check wrapper.
			//
			if( ! ($theWrapper instanceof Wrapper) )
				throw new \Exception(
					"Unable to resolve referenced: "
				   ."missing wrapper." );										// !@! ==>
		
		} // Wrapper not provided.
		
		//
		// Resolve reference.
		//
		switch( $theOffset )
		{
			case kTAG_SUBJECT:
			case kTAG_PREDICATE:
			case kTAG_OBJECT:
				$object = $this->offsetGet( $theOffset );
				break;
			
			default:
				throw new \Exception(
					"Invalid offset [$theOffset]." );							// !@! ==>
		
		} // Parsing offset.
		
		//
		// Handle unset.
		//
		if( $object === NULL )
			return NULL;															// ==>
		
		//
		// Handle object.
		//
		if( $object instanceof PersistentObject )
			return $object;															// ==>
		
		//
		// Resolve collection.
		//
		switch( $theOffset )
		{
			case kTAG_PREDICATE:
				$collection = Term::ResolveCollection(
								Term::ResolveDatabase( $theWrapper, TRUE ) );
				break;
				
			case kTAG_SUBJECT:
			case kTAG_OBJECT:
				$collection = Node::ResolveCollection(
								Node::ResolveDatabase( $theWrapper, TRUE ) );
				break;
		}
		
		return $collection->matchOne( array( kTAG_NID => $object ),
									  kQUERY_ASSERT | kQUERY_OBJECT );				// ==>
	
	} // getReferenced.

	 
	/*===================================================================================
	 *	getPathName																		*
	 *==================================================================================*/

	/**
	 * Get path name
	 *
	 * The edge persistent identifier represents the edge path in which the subject and
	 * object references are represented by their native identifier; this method returns an
	 * equivalent path, except that in this case the subject and object references are
	 * represented by the persistent identifiers of the node's referenced objects.
	 *
	 * It is assumed the current object has its {@link dictionary()} set.
	 *
	 * @access protected
	 * @return string				Edge path using referenced persistent identifiers.
	 */
	protected function getPathName()
	{
		//
		// Get edge term references.
		//
		$terms = Array();
		$terms[] = $this->getSubject()->getReferenced()[ kTAG_NID ];
		$terms[] = $this->getPredicate()[ kTAG_NID ];
		$terms[] = $this->getObject()->getReferenced()[ kTAG_NID ];
		
		return implode( kTOKEN_INDEX_SEPARATOR, $terms );							// ==>
	
	} // getPathName.

		

/*=======================================================================================
 *																						*
 *								PROTECTED GRAPH UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	createGraphNode																	*
	 *==================================================================================*/

	/**
	 * Create graph node
	 *
	 * In this class we need to override this method, since we are creating relationships
	 * instead of nodes.
	 *
	 * The <tt>matchGraphNode()</tt> becomes {@link matchGraphEdge()}, the
	 * {@link setGraphProperties()} method remains unchanged and the statement in which the
	 * node is created must be replaced with a statement creating an edge.
	 *
	 * @param DatabaseGraph			$theGraph			Graph connection.
	 *
	 * @access protected
	 * @return mixed				Node identifier, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 */
	protected function createGraphNode( DatabaseGraph $theGraph )
	{
		//
		// Check if object is already referenced.
		//
		if( $this->offsetExists( kTAG_ID_GRAPH ) )
			return $this->offsetGet( kTAG_ID_GRAPH );								// ==>
		
		//
		// Match existing graph edge.
		//
		$id = $this->matchGraphEdge( $theGraph );
		if( is_int( $id ) )
			return $id;																// ==>
		
		//
		// Init graph parameters.
		//
		$this->setGraphProperties( $labels, $properties );
		
		return $theGraph->setEdge( $this->offsetGet( kTAG_GRAPH_SUBJECT ),
								   $this->offsetGet( kTAG_PREDICATE ),
								   $this->offsetGet( kTAG_GRAPH_OBJECT ),
								   $properties );									// ==>
		
	} // createGraphNode.

	 
	/*===================================================================================
	 *	matchGraphEdge																	*
	 *==================================================================================*/

	/**
	 * Match graph edge
	 *
	 * The responsibility of this method is to check whether an edge already exists in the
	 * graph, this is done by matching the {@link kTAG_GRAPH_SUBJECT},
	 * {@link kTAG_GRAPH_OBJECT} and {@link kTAG_PREDICATE} properties.
	 *
	 * The subject and object nodes must feature by definition the {@link kTAG_ID_GRAPH}
	 * property.
	 *
	 * @param DatabaseGraph			$theGraph			Graph connection.
	 *
	 * @access protected
	 * @return integer				Graph node identifier, or <tt>FALSE</tt>.
	 */
	protected function matchGraphEdge( DatabaseGraph $theGraph )
	{
		//
		// Get subject and object graph node references.
		//
		$this->offsetSet( kTAG_GRAPH_SUBJECT,
						  $this->getSubject()->offsetGet( kTAG_ID_GRAPH ) );
		$this->offsetSet( kTAG_GRAPH_OBJECT,
						  $this->getObject()->offsetGet( kTAG_ID_GRAPH ) );
		
		//
		// Compile criteria.
		//
		$criteria = Array();
		$criteria[ (string) kTAG_GRAPH_SUBJECT ] = $this->offsetGet( kTAG_GRAPH_SUBJECT );
		$criteria[ (string) kTAG_PREDICATE ] = $this->offsetGet( kTAG_PREDICATE );
		$criteria[ (string) kTAG_GRAPH_OBJECT ] = $this->offsetGet( kTAG_GRAPH_OBJECT );
		
		//
		// Match edge.
		//
		$edge
			= static::ResolveCollection(
				static::ResolveDatabase( $this->mDictionary, TRUE ) )
					->matchOne( $criteria, kQUERY_OBJECT );
		
		//
		// Return graph node reference.
		//
		if( $edge !== NULL )
			return $edge->offsetGet( kTAG_ID_GRAPH );								// ==>
		
		return FALSE;																// ==>
	
	} // matchGraphEdge.

	 
	/*===================================================================================
	 *	setGraphProperties																*
	 *==================================================================================*/

	/**
	 * Compute graph labels and properties
	 *
	 * In this class we overload the parent method to store the {@link kTAG_NAME} property.
	 *
	 * @param array					$theLabels			Labels.
	 * @param array					$theProperties		Properties.
	 *
	 * @access protected
	 */
	protected function setGraphProperties( &$theLabels, &$theProperties )
	{
		//
		// Init parameters.
		//
		parent::setGraphProperties( $theLabels, $theProperties );
	
		//
		// Set data type.
		//
		$theProperties[ (string) kTAG_NAME ] = $this->offsetGet( kTAG_NAME );
	
	} // setGraphProperties.

	 

} // class Edge.


?>
