<?php

/**
 * StructureIterator.php
 *
 * This file contains the definition of the {@link StructureIterator} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Tag;
use OntologyWrapper\Term;
use OntologyWrapper\Node;
use OntologyWrapper\Edge;

/*=======================================================================================
 *																						*
 *									StructureIterator.php								*
 *																						*
 *======================================================================================*/

/**
 * Structure iterator
 *
 * This class implements an iterator which can traverse a structure of {@link Node} objects
 * given a root node and a set of predicate filters, caching all results.
 *
 * The class is instantiated by providing a wrapper and a root node persistent or native
 * identifier.
 *
 * The class features the following methods.
 *
 * <ul>
 *	<li><tt>{@link getChildren()}</tt>: Load all nodes and related objects <em>pointed to</em>
 *		by the provided node.
 *	<li><tt>{@link getParents()}</tt>: Load all nodes and related objects <em>pointing
 *		to</em> the provided node.
 * </ul>
 *
 * All objects are cached.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/03/2014
 */
class StructureIterator
{
	/**
	 * Property accessors trait.
	 *
	 * We use this trait to provide a common framework for methods that manage properties.
	 */
	use	traits\AccessorProperty;

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
	 * Edges cache.
	 *
	 * This data member holds the edge objects cache, indexed by object native identifier.
	 *
	 * @var array
	 */
	 protected $mEdges = Array();

	/**
	 * Graph cache.
	 *
	 * This data member holds the graph cache, it is an array structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The index is the node native identifier.
	 *	<li><em>value</em>: The value is an array structured as follows:
	 *	  <ul>
	 *		<li><em>index</em>: One of the following:
	 *		  <ul>
	 *			<li><tt>c</tt>: Child relationships.
	 *			<li><tt>p</tt>: Parent relationships.
	 *		  </ul>
	 *		<li><em>value</em>: An array structured as follows:
	 *		  <ul>
	 *			<li><tt>p</tt>: An array with the list of queried predicates; if empty,
	 *				it means that all predicates were matched.
	 *			<li><tt>n</tt>: An array holding the list of nodes structured as follows:
	 *			  <ul>
	 *				<li><em>index</em>: The predicate.
	 *				<li><em>value</em>: An aray of node native identifiers.
	 *			  </ul>
	 *		  </ul>
	 *	  </ul>
	 * </ul>
	 *
	 * @var array
	 */
	 protected $mGraph = Array();

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
	 * The constructor expects the wrapper, a reference to the root node as its native
	 * identifier and the default language.
	 *
	 * @param Wrapper				$theWrapper			Database wrapper.
	 * @param mixed					$theIdentifier		Root node identifier or object.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access public
	 */
	public function __construct( Wrapper $theWrapper, $theRoot, $theLanguage = NULL )
	{
		//
		// Set wrapper.
		//
		$this->mWrapper = $theWrapper;
		
		//
		// Cache root.
		//
		if( ! ($theRoot instanceof PersistentObject) )
			$theRoot = new Node( $theWrapper, $theRoot );
		
		
		//
		// Set root.
		//
		$this->mRoot = $this->cacheObject( $theRoot );
		
		//
		// Set language.
		//
		if( $theLanguage !== NULL )
			$this->mLanguage = $theLanguage;
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
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
	 *	getGraph																			*
	 *==================================================================================*/

	/**
	 * Return structure graph
	 *
	 * This method will return the structure graph.
	 *
	 * @access public
	 * @return array				Structure graph.
	 */
	public function getGraph()									{	return $this->mGraph;	}

		

/*=======================================================================================
 *																						*
 *								PUBLIC ITERATOR INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getChildren																		*
	 *==================================================================================*/

	/**
	 * Load child nodes
	 *
	 * This method will cache all nodes and related objects that are pointed to by the
	 * provided node with the provided predicates.
	 *
	 * If the node is omitted, it is assumed the root node; the node may also be an array.
	 *
	 * If the predicates are omitted, all predicates will be considered; note that the
	 * {@link kPREDICATE_SUBCLASS_OF} predicate is added by this method on all queries.
	 *
	 * The graph structure will be cached and cached results will not be reloaded from the
	 * database.
	 *
	 * The method will return an array structured as follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The node native identifier.
	 *	<li><tt>value</tt>: The predicate:
	 * </ul>
	 *
	 * @param mixed					$theNode			Node object or identifier.
	 * @param array					$thePredicates		List of predicates.
	 *
	 * @access public
	 * @return array				List of child nodes.
	 */
	public function getChildren( $theNode = NULL, $thePredicates = NULL )
	{
		//
		// Init local storage.
		//
		$results = $matches = Array();
		
		//
		// Normalise node.
		//
		if( $theNode === NULL)
			$theNode = $this->mRoot;
		elseif( $theNode instanceof Node )
			$theNode = $this->cacheObject( $theNode );
		elseif( is_array( $theNode ) )
		{
			foreach( $theNode as $node )
			{
				if( $node === NULL)
					$node = $this->mRoot;
				elseif( $node instanceof Node )
					$node = $this->cacheObject( $node );
				else
					$node = $this->cacheObject( new Node( $this->mWrapper, $node ) );
				$results[ $node ] = $this->getChildren( $node, $thePredicates );
			}
			
			return $results;														// ==>
		}
		else
			$theNode = $this->cacheObject( new Node( $this->mWrapper, $theNode ) );
		
		//
		// Normalise predicates.
		//
		if( $thePredicates !== NULL )
		{
			if( ! is_array( $thePredicates ) )
				$thePredicates = array( kPREDICATE_SUBCLASS_OF, $thePredicates );
			else
				$thePredicates[] = kPREDICATE_SUBCLASS_OF;
		}
		
		//
		// Locate node in graph cache.
		//
		$matched = FALSE;
		if( array_key_exists( $theNode, $this->mGraph ) )
		{
			//
			// Match children.
			//
			if( array_key_exists( 'c', $this->mGraph[ $theNode ] ) )
			{
				//
				// Check scanned predicates.
				//
				if( count( $this->mGraph[ $theNode ][ 'c' ][ 'p' ] ) )
					$matches
						= array_diff( $thePredicates,
									  $this->mGraph[ $theNode ][ 'c' ][ 'p' ] );
				
				//
				// Use graph cache.
				//
				if( ! count( $matches ) )
					$matched = TRUE;
			
			} // Child nodes in graph cache.
		
		} // Node in graph cache.
		
		//
		// Load children.
		//
		if( ! $matched )
		{
			if( isset( $matches ) )
				$this->loadChildren( $theNode, $matches );
			else
				$this->loadChildren( $theNode, $thePredicates );
		}
		
		//
		// Get all children.
		//
		if( $thePredicates === NULL )
			return $this->mGraph[ $theNode ][ 'c' ][ 'n' ];							// ==>
		
		//
		// Compile result.
		//
		foreach( $thePredicates as $predicate )
		{
			//
			// Check predicate.
			//
			if( array_key_exists( $predicate, $this->mGraph[ $theNode ][ 'c' ][ 'n' ] ) )
				$results[ $predicate ]
					= $this->mGraph[ $theNode ][ 'c' ][ 'n' ][ $predicate ];
		
		} // Scanning graph cache.
		
		return $results;															// ==>
		
	} // getChildren.

		

/*=======================================================================================
 *																						*
 *								PROTECTED CACHING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
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
		$id = $theObject->offsetGet( kTAG_NID );
		$tags = array( kTAG_LABEL, kTAG_DEFINITION, kTAG_DESCRIPTION );
		
		//
		// Parse class.
		//
		$class = get_class( $theObject );
		switch( $class::kSEQ_NAME )
		{
			case Tag::kSEQ_NAME:
				$cache = & $this->mTags;
				break;
			
			case Term::kSEQ_NAME:
				$cache = & $this->mTerms;
				break;
			
			case Node::kSEQ_NAME:
				// Load referenced tag.
				if( $theObject->offsetExists( kTAG_TAG ) )
					$this->cacheObject(
						new Tag( $this->mWrapper,
								 $theObject->offsetGet( kTAG_TAG ) ) );
				// Load referenced term.
				if( $theObject->offsetExists( kTAG_TERM ) )
					$this->cacheObject(
						new Term( $this->mWrapper,
								  $theObject->offsetGet( kTAG_TERM ) ) );
				$cache = & $this->mNodes;
				break;
			
			case Edge::kSEQ_NAME:
				// Load edge object.
				$this->cacheObject(
					new Edge( $this->mWrapper,
							  $theObject->offsetGet( kTAG_OBJECT ) ) );
				// Load edge subject.
				$this->cacheObject(
					new Edge( $this->mWrapper,
							  $theObject->offsetGet( kTAG_SUBJECT ) ) );
				$cache = & $this->mEdges;
				break;
			
			default:
				throw new \Exception(
					"Unable to cache object: "
				   ."unsupported class [$class]." );							// !@! ==>
		
		} // Parsed class.
		
		//
		// Cache object.
		//
		if( ! array_key_exists( $id, $cache ) )
		{
			//
			// Normalise language strings.
			//
			foreach( $tags as $tag )
			{
				//
				// Skip existing.
				//
				if( $theObject->offsetExists( $tag ) )
					$theObject->offsetSet(
						$tag,
						OntologyObject::SelectLanguageString(
							$theObject->offsetGet( $tag ),
							$this->mLanguage ) );
			}
			
			//
			// Cache object.
			//
			$cache[ $id ] = $theObject;
		}
		
		return $id;																	// ==>
	
	} // cacheObject.

	 
	/*===================================================================================
	 *	loadChildren																		*
	 *==================================================================================*/

	/**
	 * Load child relationships
	 *
	 * This method will load and cache all relationships stemming from the provided node of
	 * the provided predicates.
	 *
	 * If the predicates parameter is <tt>NULL</tt>, it means that all predicates will be
	 * considered.
	 *
	 * @param int					$theNode			Origin node.
	 * @param array					$thePredicates		Predicates list.
	 *
	 * @access protected
	 */
	protected function loadChildren( $theNode, $thePredicates )
	{
		//
		// Build criteria.
		//
		$criteria = array( kTAG_OBJECT => $theNode );
		if( count( $thePredicates ) == 1 )
			$criteria[ kTAG_PREDICATE ] = current( $thePredicates );
		elseif( count( $thePredicates ) > 1 )
			$criteria[ kTAG_PREDICATE ][ '$in' ] = $thePredicates;
		
		//
		// Query edges.
		//
		$edges
			= $this->mWrapper->resolveCollection( Edge::kSEQ_NAME )
				->matchAll( $criteria, kQUERY_OBJECT );
		
		//
		// Handle selection.
		//
		if( $edges->count() )
		{
			//
			// Init local storage.
			//
			if( ! array_key_exists( $theNode, $this->mGraph ) )
				$this->mGraph[ $theNode ] = Array();
			$cache = & $this->mGraph[ $theNode ];
			if( ! array_key_exists( 'c', $cache ) )
			{
				$cache[ 'c' ] = Array();
				$cache = & $cache[ 'c' ];
				$cache[ 'p' ] = ( is_array( $thePredicates ) )
							  ? $thePredicates
							  : Array();
				$cache[ 'n' ] = Array();
				$cache = & $cache[ 'n' ];
			}
			
			//
			// Load edges.
			//
			foreach( $edges as $edge )
			{
				//
				// Init local storage.
				//
				$predicate = $edge->offsetGet( kTAG_PREDICATE );
				$node = $this->cacheObject( $edge->getSubject( $this->mWrapper ) );
				
				//
				// Load in graph.
				//
				if( ! array_key_exists( $predicate, $cache ) )
					$cache[ $predicate ] = Array();
				if( ! in_array( $node, $cache[ $predicate ] ) )
					$cache[ $predicate ][] = $node;
		
			} // Loading edges.
		
		} // Found relationshipd.
		
	} // loadChildren.

	 

} // class StructureIterator.


?>
