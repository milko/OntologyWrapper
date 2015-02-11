<?php

/**
 * CachedStructure.php
 *
 * This file contains the definition of the {@link CachedStructure} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Tag;
use OntologyWrapper\Term;
use OntologyWrapper\Node;
use OntologyWrapper\Edge;

/*=======================================================================================
 *																						*
 *									CachedStructure.php									*
 *																						*
 *======================================================================================*/

/**
 * Cached structure
 *
 * This <em>abstract</em> class implements an object that can traverse a nodes graph by
 * caching its elements.
 *
 * The structure nodes and the latter's related tag and term are cached along with the nodes
 * themselves, while the edges will be queried from the database.
 *
 * The class is instantiated by providing a wrapper, a root node persistent or native
 * identifier and the default language for labels and descriptions.
 *
 * The structure is returned as an array indexed by prericate with as value an array of node
 * native identifiers, which can then be retrieved via three public methods:
 *
 * <ul>
 *	<li><em>{@link getTag()}</em>: Retrieve a tag by native identifier.
 *	<li><em>{@link getTerm()}</em>: Retrieve a term by native identifier.
 *	<li><em>{@link getNode()}</em>: Retrieve a node by native identifier.
 *	<li><em>{@link getRoot()}</em>: Retrieve the root node.
 * </ul>
 *
 * All the above methods will return the cached object or cache it before returning. Methods
 * handling the structure relationship are protected, so that specialised derived classes
 * can implement a relevant interface.
 *
 * All cached objects will have their labels, definitions and descriptions reduced to the
 * default language provided in the constructor and nodes missing their label will feature
 * the label of the related term and tag in that order.
 *
 * The main goal is to use this class as the parent of concrete derived classes that
 * implement specialised structures.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2015
 */
abstract class CachedStructure
{
	/**
	 * Root node.
	 *
	 * This data member holds the root node native identifier.
	 *
	 * @var int
	 */
	 protected $mRoot = NULL;

	/**
	 * Tags cache.
	 *
	 * This data member holds the tag objects cache, indexed by object native identifier.
	 *
	 * @var array
	 */
	 protected $mTags = Array();

	/**
	 * Terms cache.
	 *
	 * This data member holds the term objects cache, indexed by object native identifier.
	 *
	 * @var array
	 */
	 protected $mTerms = Array();

	/**
	 * Nodes cache.
	 *
	 * This data member holds the node objects cache, indexed by object native identifier.
	 *
	 * @var array
	 */
	 protected $mNodes = Array();

	/**
	 * Wrapper.
	 *
	 * This data member holds the wrapper.
	 *
	 * @var Wrapper
	 */
	 protected $mWrapper = NULL;

	/**
	 * Language.
	 *
	 * This data member holds the default language code.
	 *
	 * @var string
	 */
	 protected $mLanguage = kSTANDARDS_LANGUAGE;

		

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
	 * The constructor expects the wrapper, a reference to the root node as its native or
	 * persistent identifier and the default language.
	 *
	 * @param Wrapper				$theWrapper			Database wrapper.
	 * @param mixed					$theIdentifier		Root node identifier or object.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access public
	 */
	public function __construct( Wrapper $theWrapper,
										 $theRoot,
										 $theLanguage = kSTANDARDS_LANGUAGE )
	{
		//
		// Set wrapper.
		//
		$this->mWrapper = $theWrapper;
		
		//
		// Cache root.
		//
		$this->mRoot = $this->cacheNode( $theRoot );
		
		//
		// Set language.
		//
		$this->mLanguage = $theLanguage;
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getWrapper																		*
	 *==================================================================================*/

	/**
	 * Return data wrapper
	 *
	 * This method will return the data wrapper.
	 *
	 * @access public
	 * @return Wrapper				Data wrapper.
	 */
	public function getWrapper()								{	return $this->mWrapper;	}

	 
	/*===================================================================================
	 *	getLanguage																		*
	 *==================================================================================*/

	/**
	 * Return default language
	 *
	 * This method will return the default language.
	 *
	 * @access public
	 * @return string				Language code.
	 */
	public function getLanguage()							{	return $this->mLanguage;	}

	 
	/*===================================================================================
	 *	getRoot																			*
	 *==================================================================================*/

	/**
	 * Return root object
	 *
	 * This method will return the root object.
	 *
	 * @access public
	 * @return Node					Root node.
	 */
	public function getRoot()					{	return $this->mNodes[ $this->mRoot ];	}

	 
	/*===================================================================================
	 *	getTag																			*
	 *==================================================================================*/

	/**
	 * Get tag
	 *
	 * This method will return the tag object related to the provided native identifier.
	 *
	 * If the object is not cached, the method will cache it.
	 *
	 * @param string				$theIdentifier		Object native identifier.
	 *
	 * @access public
	 * @return Tag					Tag object.
	 */
	public function getTag( $theIdentifier )
	{
		//
		// Cache object.
		//
		if( ! array_key_exists( $theIdentifier, $this->mTags ) )
			$this->cacheTag( $theIdentifier );
		
		return $this->mTags[ $theIdentifier ];										// ==>
		
	} // getTag.

	 
	/*===================================================================================
	 *	getTerm																			*
	 *==================================================================================*/

	/**
	 * Get term
	 *
	 * This method will return the term object related to the provided native identifier.
	 *
	 * If the object is not cached, the method will cache it.
	 *
	 * @param string				$theIdentifier		Object native identifier.
	 *
	 * @access public
	 * @return Term					Term object.
	 */
	public function getTerm( $theIdentifier )
	{
		//
		// Cache object.
		//
		if( ! array_key_exists( $theIdentifier, $this->mTerms ) )
			$this->cacheTerm( $theIdentifier );
		
		return $this->mTerms[ $theIdentifier ];										// ==>
		
	} // getTerm.

	 
	/*===================================================================================
	 *	getNode																			*
	 *==================================================================================*/

	/**
	 * Get node
	 *
	 * This method will return the node object related to the provided native identifier or
	 * persistent identifier.
	 *
	 * If the object is not cached, the method will cache it.
	 *
	 * @param mixed					$theIdentifier		Object native or persistent id.
	 *
	 * @access public
	 * @return Node					Node object.
	 */
	public function getNode( $theIdentifier )
	{
		//
		// Cache object.
		//
		if( ! array_key_exists( $theIdentifier, $this->mNodes ) )
			$theIdentifier = $this->cacheNode( $theIdentifier );
		
		return $this->mNodes[ $theIdentifier ];										// ==>
		
	} // getNode.

		

/*=======================================================================================
 *																						*
 *								PROTECTED STRUCTURE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getRelationships																*
	 *==================================================================================*/

	/**
	 * Cache and retrieve relationships
	 *
	 * This method will load all relationships of the current node, of the provided
	 * predicates in the provided direction.
	 *
	 * The method will return an array indexed by predicate with as value an array of node
	 * native identifiers corresponding to the related nodes; any
	 * {@link kPREDICATE_SUBCLASS_OF} predicate will be recursed.
	 *
	 * To consider all predicates, provide <tt>NULL</tt> in the parameter.
	 *
	 * @param int					$theNode			Origin node.
	 * @param string				$theDirection		'i' incoming, 'o' outgoing.
	 * @param array					$thePredicates		Predicates list.
	 *
	 * @access protected
	 * @return array				List of related node native identifiers.
	 */
	protected function getRelationships( $theNode, $theDirection, $thePredicates = NULL )
	{
		//
		// Init local storage.
		//
		$results = Array();
		
		//
		// Check direction.
		//
		switch( $theDirection )
		{
			case 'i':
				$match = kTAG_OBJECT;
				$target = kTAG_SUBJECT;
				break;
			
			case 'o':
				$match = kTAG_SUBJECT;
				$target = kTAG_OBJECT;
				break;
			
			default:
				throw new \Exception(
					"Unable to cache relationships: "
				   ."invalid direction [$theDirection]." );						// !@! ==>
		}
		
		//
		// Normalise node.
		//
		if( ! is_array( $theNode ) )
			$theNode = array( $theNode );
		
		//
		// Normalise predicates.
		//
		if( $thePredicates !== NULL )
		{
			if( is_array( $thePredicates )
			 && (! in_array( kPREDICATE_SUBCLASS_OF, $thePredicates )) )
				$thePredicates[] = kPREDICATE_SUBCLASS_OF;
			elseif( $thePredicates != kPREDICATE_SUBCLASS_OF )
				$thePredicates = array( $thePredicates, kPREDICATE_SUBCLASS_OF );
		}
		
		//
		// Scan node.
		//
		while( ($node = array_shift( $theNode )) !== NULL )
		{
			//
			// Cache node.
			//
			if( $node instanceof Node )
				$node = $this->cacheObject( $node );
			
			//
			// Build criteria.
			//
			$criteria = array( $match => $node );
			if( $thePredicates !== NULL )
			{
				if( is_array( $thePredicates ) )
				{
					if( count( $thePredicates ) == 1 )
						$criteria[ kTAG_PREDICATE ] = current( $thePredicates );
					elseif( count( $thePredicates ) > 1 )
						$criteria[ kTAG_PREDICATE ][ '$in' ] = $thePredicates;
				}
				else
					$criteria[ kTAG_PREDICATE ] = $thePredicates;
			}

			//
			// Query edges.
			//
			$edges
				= $this->mWrapper->resolveCollection( Edge::kSEQ_NAME )
					->matchAll( $criteria, kQUERY_OBJECT );

			//
			// Load edges.
			//
			foreach( $edges as $edge )
			{
				//
				// Load predicate.
				//
				$predicate = $edge->offsetGet( kTAG_PREDICATE );
				
				//
				// Skip schemas.
				//
				if( $predicate != kPREDICATE_SUBCLASS_OF )
				{
					//
					// Allocate predicate.
					//
					if( ! array_key_exists( $predicate, $results ) )
						$results[ $predicate ] = Array();
					
					//
					// Check recursion.
					//
					if( ! in_array( $edge->offsetGet( $target ), $results[ $predicate ] ) )
						$results[ $predicate ][]
							= ( $theDirection == 'i' )
							? $this->cacheObject( $edge->getSubject( $this->mWrapper ) )
							: $this->cacheObject( $edge->getObject( $this->mWrapper ) );
				
				} // Not a schema.
				
				//
				// Handle schema.
				//
				else
					$theNode[] = $edge->offsetGet( $target );

			} // Loading edges.
	
		} // Scanning node.
		
		return $results;															// ==>
		
	} // getRelationships.

		

/*=======================================================================================
 *																						*
 *								PROTECTED CACHING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	cacheTag																		*
	 *==================================================================================*/

	/**
	 * Cache a tag
	 *
	 * This method will resolve that provided tag identifier and cache the object; it is
	 * also popssible to provide the actual object.
	 *
	 * @param mixed					$theObject			Tag native identifier or object.
	 *
	 * @access protected
	 * @return mixed				The native identifier.
	 */
	protected function cacheTag( $theObject )
	{
		//
		// Handle identifier.
		//
		if( ! ($theObject instanceof Tag) )
			$theObject
				= PersistentObject::ResolveObject(
					$this->mWrapper,
					Tag::kSEQ_NAME,
					(string) $theObject,
					TRUE );
		
		return $this->cacheObject( $theObject );									// ==>
	
	} // cacheTag.

	 
	/*===================================================================================
	 *	cacheTerm																		*
	 *==================================================================================*/

	/**
	 * Cache a term
	 *
	 * This method will resolve that provided term identifier and cache the object; it is
	 * also popssible to provide the actual object.
	 *
	 * @param mixed					$theObject			Term native identifier or object.
	 *
	 * @access protected
	 * @return mixed				The native identifier.
	 */
	protected function cacheTerm( $theObject )
	{
		//
		// Handle identifier.
		//
		if( ! ($theObject instanceof Term) )
			$theObject
				= PersistentObject::ResolveObject(
					$this->mWrapper,
					Term::kSEQ_NAME,
					(string) $theObject,
					TRUE );
		
		return $this->cacheObject( $theObject );									// ==>
	
	} // cacheTerm.

	 
	/*===================================================================================
	 *	cacheNode																		*
	 *==================================================================================*/

	/**
	 * Cache a node
	 *
	 * This method will resolve that provided node native or persistent identifier and cache
	 * the object; it is also popssible to provide the actual object.
	 *
	 * @param mixed					$theObject			Node native identifier or object.
	 *
	 * @access protected
	 * @return mixed				The native identifier.
	 */
	protected function cacheNode( $theObject )
	{
		//
		// Handle identifier.
		//
		if( ! ($theObject instanceof Node) )
			$theObject
				= ( is_int( $theObject ) )
				? PersistentObject::ResolveObject(
					$this->mWrapper,
					Tag::kSEQ_NAME,
					$theObject,
					TRUE )
				: Node::GetPidNode(
					$this->mWrapper,
					(string) $theObject,
					kQUERY_OBJECT );
		
		return $this->cacheObject( $theObject );									// ==>
	
	} // cacheNode.

	 
	/*===================================================================================
	 *	cacheEdge																		*
	 *==================================================================================*/

	/**
	 * Cache an edge
	 *
	 * This method will resolve that provided term identifier and cache the object; it is
	 * also popssible to provide the actual object.
	 *
	 * @param mixed					$theObject			Term native identifier or object.
	 *
	 * @access protected
	 * @return mixed				The native identifier.
	 */
	protected function cacheEdge( $theObject )
	{
		//
		// Handle identifier.
		//
		if( ! ($theObject instanceof Term) )
			$theObject
				= PersistentObject::ResolveObject(
					$this->mWrapper,
					Term::kSEQ_NAME,
					(string) $theObject,
					TRUE );
		
		return $this->cacheObject( $theObject );									// ==>
	
	} // cacheEdge.

	 
	/*===================================================================================
	 *	cacheObject																		*
	 *==================================================================================*/

	/**
	 * Cache object
	 *
	 * This method will cache the provided object and return its native identifier.
	 *
	 * @param PersistentObject		$theObject			Object to cache.
	 *
	 * @access protected
	 * @return mixed				The native identifier.
	 *
	 * @throws Exception
	 */
	protected function cacheObject( PersistentObject $theObject )
	{
		//
		// Init local storage.
		//
		$class = get_class( $theObject );
		$id = $theObject->offsetGet( kTAG_NID );
		$collection = Wrapper::ResolveClassCollection( $class );
		
		//
		// Check cache.
		//
		if( $this->getCachedObject( $id, $collection ) !== NULL )
			return $id;																// ==>
		
		//
		// Init local storage.
		//
		$tags = array( kTAG_LABEL, kTAG_DEFINITION, kTAG_DESCRIPTION );
		
		//
		// Parse class.
		//
		switch( $collection )
		{
			case Tag::kSEQ_NAME:
				$cache = & $this->mTags;
				break;
			
			case Term::kSEQ_NAME:
				$cache = & $this->mTerms;
				break;
			
			case Node::kSEQ_NAME:
				$cache = & $this->mNodes;
				// Load referenced tag.
				if( $theObject->offsetExists( kTAG_TAG ) )
					$this->cacheTag(  $theObject->offsetGet( kTAG_TAG ) );
				// Load referenced term.
				if( $theObject->offsetExists( kTAG_TERM ) )
					$this->cacheTerm(  $theObject->offsetGet( kTAG_TERM ) );
				break;
			
			default:
				throw new \Exception(
					"Unable to cache object: "
				   ."unsupported class [$class]." );							// !@! ==>
		
		} // Parsed class.
		
		//
		// Normalise language strings.
		//
		foreach( $tags as $tag )
		{
			if( $theObject->offsetExists( $tag ) )
				$theObject->offsetSet(
					$tag,
					OntologyObject::SelectLanguageString(
						$theObject->offsetGet( $tag ),
						$this->mLanguage ) );
		}
		
		//
		// Copy labels to nodes.
		//
		if( ($collection == Node::kSEQ_NAME)				// Is a node
		 && (! $theObject->offsetExists( kTAG_LABEL )) )	// and is missing label.
			$theObject->offsetSet(
				kTAG_LABEL,
				( $theObject->offsetExists( kTAG_TERM ) )
				? $this->mTerms[ $theObject->offsetGet( kTAG_TERM ) ]
					->offsetGet( kTAG_LABEL )
				: $this->mTags[ $theObject->offsetGet( kTAG_TAG ) ]
					->offsetGet( kTAG_TAG ) );
		
		//
		// Cache object.
		//
		$cache[ $id ] = $theObject;
		
		return $id;																	// ==>
	
	} // cacheObject.

	 
	/*===================================================================================
	 *	getCachedObject																	*
	 *==================================================================================*/

	/**
	 * Retrieve cached object
	 *
	 * This method will return the cached object corresponding to the provided identifier
	 * collection name.
	 *
	 * If the object was not found, the method will return <tt>NULL</tt>.
	 *
	 * If the provided collection name is invalid, the method will raise an exception.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param string				$theCollection		Collection kSEQ_NAME.
	 *
	 * @access protected
	 * @return mixed				The cached object or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	protected function getCachedObject( $theIdentifier, $theCollection )
	{
		//
		// Parse collection.
		//
		switch( $theCollection )
		{
			case Tag::kSEQ_NAME:
				if( array_key_exists( $theIdentifier, $this->mTags ) )
					return $this->mTags[ $theIdentifier ];							// ==>
				break;
			
			case Term::kSEQ_NAME:
				if( array_key_exists( $theIdentifier, $this->mTerms ) )
					return $this->mTerms[ $theIdentifier ];							// ==>
				break;
			
			case Node::kSEQ_NAME:
				if( array_key_exists( $theIdentifier, $this->mNodes ) )
					return $this->mNodes[ $theIdentifier ];							// ==>
				break;
			
			default:
				throw new \Exception(
					"Unable to retrieve object: "
				   ."unsupported collection [$theCollection]." );				// !@! ==>
		
		} // Parsed class.
		
		return NULL;																// ==>
	
	} // getCachedObject.

	 

} // class CachedStructure.


?>
