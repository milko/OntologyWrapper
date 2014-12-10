<?php

/**
 * Node.php
 *
 * This file contains the definition of the {@link Node} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\MetadataObject;
use OntologyWrapper\Tag;
use OntologyWrapper\Term;
use OntologyWrapper\Edge;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *										Node.php										*
 *																						*
 *======================================================================================*/

/**
 * Domains.
 *
 * This file contains the domain definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

/**
 * Node object
 *
 * A node is a <em>vertex in a graph structure</em>, nodes reference
 * <em>{@link Term}</em> and <em>{@link Tag}</em> instances, when referencing a
 * term, nodes are used to build <em>ontologies</em>, <em>type definitions</em> and
 * <em>controlled vocabularies</em>; when referencing tags they are used to build <em>data
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
 *	<li><tt>{@link kTAG_ID_PERSISTENT}</tt>: <em>Persistent identifier</em>. This optional
 *		attribute holds a string which represents a unique persitent identifier, this value
 *		must be unique among all nodes and is optional. The main duty of this attribute is
 *		to disambiguate nodes pointing to the same term or tag.
 *	<li><tt>{@link kTAG_ID_GRAPH}</tt>: <em>Graph node reference</em>. If the wrapper uses
 *		a graph database, this property will be used to reference the graph node which
 *		represents the current node; it is an integer value which is automatically managed.
 *	<li><tt>{@link kTAG_TERM}</tt>: <em>Term</em>. This attribute is a <em>string</em> that
 *		holds a reference to the <em>term object</em> that the current node <em>represents
 *		in a graph structure</em>. If this offset is set, the {@link kTAG_TAG} offset must
 *		be omitted. This attribute must be managed with its offset.
 *	<li><tt>{@link kTAG_TAG}</tt>: <em>Tag</em>. This attribute is a <em>string</em> that
 *		holds a reference to the <em>tag object</em> that the current node <em>represents
 *		in a graph structure</em>. If this offset is set, the {@link kTAG_TERM} offset must
 *		be omitted. This attribute must be managed with its offset.
 *	<li><tt>{@link kTAG_NODE_TYPE}</tt>: <em>Type</em>. This attribute is an <em>enumerated
 *		set</em> which <em>qualifies</em> and sets a <em>context</en> for the current node.
 *		The individual elements can be managed with the {@link NodeType()} method.
 *	<li><tt>{@link kTAG_MASTER}</tt>: <em>Master node</em>. This property is featured by
 *		alias nodes, it <em>references</em> the <em>master node</em>. This property is
 *		handled automatically by the object.
 * </ul>
 *
 * The {@link __toString()} method will return the value stored in the {@link kTAG_TERM} or
 * the {@link kTAG_TAG} offset, this value is not used as an identifier.
 *
 * Node persistent identifiers are automatically assigned sequence numbers, this is because
 * you may have more than one node pointing to the same term or tag. The object features a
 * persistent unique identifier, {@link kTAG_ID_PERSISTENT}, which can be used to match a
 * specific node, this attribute is optional and it is used to discriminate between
 * <em>master</em> and <em>alias</em> nodes.
 *
 * There are two main types of nodes: <em>master</em> and <em>alias</em>. A master node is
 * considered the main referer of the featured term or tag, there can be only one master
 * node which points to a specific term or tag. An alias node is, as the word says, an alias
 * of the term or tag it points to. A node is an alias if it has the {@link kTAG_MASTER}
 * property, there can be any number of alias nodes pointing to the same term or tag.
 *
 * In general, master nodes are used to build the main ontology where all the functional
 * relationships are described. Alias nodes are used to create views over the master nodes
 * ontology, these are used to provide templates, forms and output views.
 *
 * 
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
class Node extends MetadataObject
{
	/**
	 * Type trait.
	 *
	 * We use this trait to handle types.
	 */
	use	traits\NodeType;

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
		
		//
		// Set alias status.
		//
		if( $this->offsetExists( kTAG_MASTER ) )
			$this->isAlias( TRUE );

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
 *							PUBLIC NAME MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Get object name
	 *
	 * In this class we return the node's term or tag name.
	 *
	 * The method will raise an exception if the current object lacks a wrapper reference.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 */
	public function getName( $theLanguage )
	{
		return $this->getReferenced()->getName();									// ==>
	
	} // getName.

		

/*=======================================================================================
 *																						*
 *							PUBLIC OBJECT REFERENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getReferenced																	*
	 *==================================================================================*/

	/**
	 * Get referenced object
	 *
	 * This method will return either the term or tag object if any is set; if none are set,
	 * the method will return <tt>NULL</tt>, or raise an exception if the second parameter
	 * is <tt>TRUE</tt>.
	 *
	 * The first parameter is the wrapper in which the current object is, or will be,
	 * stored: if the current object has the {@link dictionary()}, this parameter may be
	 * omitted; if the wrapper cannot be resolved, the method will raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param boolean				$doAssert			Raise exception if not matched.
	 *
	 * @access public
	 * @return PersistentObject		Referenced tag or term or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function getReferenced( $theWrapper = NULL, $doAssert = TRUE )
	{
		//
		// Check tag and term.
		//
		if( $this->offsetExists( kTAG_TAG )
		 || $this->offsetExists( kTAG_TERM ) )
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
					   ."missing wrapper." );									// !@! ==>
			
			} // Wrapper not provided.
		
			//
			// Resolve collection.
			//
			$collection = ( $this->offsetExists( kTAG_TAG ) )
						? Tag::ResolveCollection(
							Tag::ResolveDatabase( $theWrapper, TRUE ) )
						: Term::ResolveCollection(
							Term::ResolveDatabase( $theWrapper, TRUE ) );
			
			//
			// Set criteria.
			//
			$criteria = array( kTAG_NID => ( $this->offsetExists( kTAG_TAG ) )
										   ? $this->offsetGet( kTAG_TAG )
										   : $this->offsetGet( kTAG_TERM ) );
			
			//
			// Locate object.
			//
			$object = $collection->matchOne( $criteria );
			if( $doAssert
			 && ($object === NULL) )
				throw new \Exception(
					"Unable to resolve referenced: "
				   ."referenced object not matched." );							// !@! ==>
			
			return $object;															// ==>
		
		} // Has tag or term.
		
		return NULL;																// ==>
	
	} // getReferenced.

		

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
	 *	<li><tt>{@link kTAG_ID_PERSISTENT}</tt>: Persistent identifier.
	 *	<li><tt>{@link kTAG_TAG}</tt>: Tag reference.
	 *	<li><tt>{@link kTAG_TERM}</tt>: Term reference.
	 *	<li><tt>{@link kTAG_NODE_TYPE}</tt>: Node type.
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
		// Set offsets.
		//
		$collection->createIndex( array( kTAG_OBJECT_OFFSETS => 1 ),
								  array( "name" => "OFFSETS" ) );
		
		//
		// Set persistent identifier index.
		//
		$collection->createIndex( array( kTAG_ID_PERSISTENT => 1 ),
								  array( "name" => "PID",
								  		 "unique" => TRUE,
								  		 "sparse" => TRUE ) );
		
		//
		// Set graph node identifier index.
		//
		$collection->createIndex( array( kTAG_ID_GRAPH => 1 ),
								  array( "name" => "GRAPH" ) );
		
		//
		// Set tag index.
		//
		$collection->createIndex( array( kTAG_TAG => 1 ),
								  array( "name" => "TAG",
								  		 "sparse" => TRUE ) );
		
		//
		// Set term index.
		//
		$collection->createIndex( array( kTAG_TERM => 1 ),
								  array( "name" => "TERM",
								  		 "sparse" => TRUE ) );
		
		//
		// Set type index.
		//
		$collection->createIndex( array( kTAG_NODE_TYPE => 1 ),
								  array( "name" => "TYPE",
								  		 "sparse" => TRUE ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.

		

/*=======================================================================================
 *																						*
 *							STATIC OBJECT REFERENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GetTagMaster																	*
	 *==================================================================================*/

	/**
	 * Get tag node master
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: This parameter represents the wrapper containing both the
	 *		nodes and the tags.
	 *	<li><b>$theIdentifier</b>: This parameter represents the tag identifier:
	 *	 <ul>
	 *		<li><tt>integer</tt>: If the value is an integer, the method will match the
	 *			identifier with the {@link kTAG_ID_HASH} offset of the tag;
	 *		<li><tt>string</tt>: Any other type will be cast to a string, the method will
	 *			match the identifier with the {@link kTAG_NID} offset of the tag.
	 *	 </ul>
	 *	<li><b>$theResult</b>: This parameter determines what the method should return, it
	 *		is a bitfield which accepts two sets of values:
	 *	 <ul>
	 *		<li><tt>{@link kQUERY_ASSERT}</tt>: If this flag is set and the criteria doesn't
	 *			match any record, the method will raise an exception.
	 *		<li><em>Result type</em>: This set of values can be added to the previous flag,
	 *			only one of these should be provided:
	 *		 <ul>
	 *			<li><tt>{@link kQUERY_OBJECT}</tt>: Return the matched object.
	 *			<li><tt>{@link kQUERY_ARRAY}</tt>: Return the matched object array value.
	 *			<li><tt>{@link kQUERY_NID}</tt>: Return the matched object native
	 *				identifier.
	 *			<li><tt>{@link kQUERY_COUNT}</tt>: Return the number of matched objects.
	 *		 </ul>
	 *	 </ul>
	 * </ul>
	 *
	 * By default the result is set to return the native identifier.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param mixed					$theIdentifier		Tag native identifier or sequence.
	 * @param bitfield				$theResult			Result type.
	 *
	 * @static
	 * @return Node					Master node or <tt>NULL</tt>
	 */
	static function GetTagMaster( Wrapper $theWrapper,
										  $theIdentifier,
										  $theResult = kQUERY_NID )
	{
		//
		// Resolve sequence number.
		//
		if( substr( $theIdentifier, 0, 1 ) == kTOKEN_TAG_PREFIX )
		{
			//
			// Init local storage.
			//
			$result = kQUERY_COUNT | ( $theResult & kQUERY_NID );
			
			//
			// Resolve collection.
			//
			$collection
				= Tag::ResolveCollection(
					Tag::ResolveDatabase( $theWrapper, TRUE ) );
			
			//
			// Set criteria.
			//
			$criteria = array( kTAG_ID_HASH => $theIdentifier );
			
			//
			// Locate tag.
			//
			$theIdentifier = $collection->matchOne( $criteria, $result );
		
		} // Provided sequence number.
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) );
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_TAG => $theIdentifier,
						   kTAG_MASTER => array( '$exists' => FALSE ) );
		
		return $collection->matchOne( $criteria, $theResult );						// ==>
	
	} // GetTagMaster.

	 
	/*===================================================================================
	 *	GetTermMaster																	*
	 *==================================================================================*/

	/**
	 * Get term node master
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: This parameter represents the wrapper containing both the
	 *		nodes and the tags.
	 *	<li><b>$theIdentifier</b>: This parameter represents the term native identifier.
	 *	<li><b>$theResult</b>: This parameter determines what the method should return, it
	 *		is a bitfield which accepts two sets of values:
	 *	 <ul>
	 *		<li><tt>{@link kQUERY_ASSERT}</tt>: If this flag is set and the criteria doesn't
	 *			match any record, the method will raise an exception.
	 *		<li><em>Result type</em>: This set of values can be added to the previous flag,
	 *			only one of these should be provided:
	 *		 <ul>
	 *			<li><tt>{@link kQUERY_OBJECT}</tt>: Return the matched object.
	 *			<li><tt>{@link kQUERY_ARRAY}</tt>: Return the matched object array value.
	 *			<li><tt>{@link kQUERY_NID}</tt>: Return the matched object native
	 *				identifier.
	 *			<li><tt>{@link kQUERY_COUNT}</tt>: Return the number of matched objects.
	 *		 </ul>
	 *	 </ul>
	 * </ul>
	 *
	 * By default the result is set to return the native identifier.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param mixed					$theIdentifier		Term native identifier.
	 * @param bitfield				$theResult			Result type.
	 *
	 * @static
	 * @return Node					Master node or <tt>NULL</tt>
	 */
	static function GetTermMaster( Wrapper $theWrapper,
										   $theIdentifier,
										   $theResult = kQUERY_NID )
	{
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) );
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_TERM => $theIdentifier,
						   kTAG_MASTER => array( '$exists' => FALSE ) );
		
		return $collection->matchOne( $criteria, $theResult );						// ==>
	
	} // GetTermMaster.

	 
	/*===================================================================================
	 *	GetPidNode																		*
	 *==================================================================================*/

	/**
	 * Get node by PID
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: This parameter represents the wrapper containing both the
	 *		nodes and the tags.
	 *	<li><b>$theIdentifier</b>: This parameter represents the node persistent identifier.
	 *	<li><b>$theResult</b>: This parameter determines what the method should return, it
	 *		is a bitfield which accepts two sets of values:
	 *	 <ul>
	 *		<li><tt>{@link kQUERY_ASSERT}</tt>: If this flag is set and the criteria doesn't
	 *			match any record, the method will raise an exception.
	 *		<li><em>Result type</em>: This set of values can be added to the previous flag,
	 *			only one of these should be provided:
	 *		 <ul>
	 *			<li><tt>{@link kQUERY_OBJECT}</tt>: Return the matched object.
	 *			<li><tt>{@link kQUERY_ARRAY}</tt>: Return the matched object array value.
	 *			<li><tt>{@link kQUERY_NID}</tt>: Return the matched object native
	 *				identifier.
	 *			<li><tt>{@link kQUERY_COUNT}</tt>: Return the number of matched objects.
	 *		 </ul>
	 *	 </ul>
	 * </ul>
	 *
	 * By default the result is set to return the native identifier.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param string				$theIdentifier		Node persistent identifier.
	 * @param bitfield				$theResult			Result type.
	 *
	 * @static
	 * @return Node					Node or <tt>NULL</tt>
	 */
	static function GetPidNode( Wrapper $theWrapper,
										$theIdentifier,
										$theResult = kQUERY_NID )
	{
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) );
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_ID_PERSISTENT => (string) $theIdentifier );
		
		return $collection->matchOne( $criteria, $theResult );						// ==>
	
	} // GetPidNode.

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_ID_PERSISTENT}</tt>: Node persistent identifier.
	 *	<li><tt>{@link kTAG_TAG}</tt>: Node tag reference.
	 *	<li><tt>{@link kTAG_TERM}</tt>: Node term reference.
	 *	<li><tt>{@link kTAG_NODE_TYPE}</tt>: Node type.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_ID_PERSISTENT,
								   kTAG_TAG, kTAG_TERM,
								   kTAG_NODE_TYPE ) );								// ==>
	
	} // DefaultOffsets.

		

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
			// Parse offset.
			//
			switch( $theOffset )
			{
				//
				// Intercept tag.
				//
				case kTAG_TAG:
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
							   ."provided an object other than a tag." );		// !@! ==>
			
					} // Object.
			
					//
					// Cast to string.
					//
					else
						$theValue = (string) $theValue;
						
					break;
			
				//
				// Intercept term.
				//
				case kTAG_TERM:
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
							   ."provided an object other than a term." );		// !@! ==>
			
					} // Object.
			
					//
					// Cast to string.
					//
					else
						$theValue = (string) $theValue;
						
					break;
			
				//
				// Intercept master.
				//
				case kTAG_MASTER:
					//
					// Validate offsets.
					//
					$this->validateReference(
						$theValue, kTYPE_REF_NODE, __class__, $theOffset );
						
					break;
			
			} // Parsed offset.
			
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
		// Handle offsets.
		//
		switch( $theOffset )
		{
			case kTAG_TAG:
				$this->offsetUnset( kTAG_TERM );
				break;
			
			case kTAG_TERM:
				$this->offsetUnset( kTAG_TAG );
				break;
		}
	
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
 *								PROTECTED PRE-COMMIT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitPrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before commit
	 *
	 * If the object is not an alias, we check whether there is another similar master
	 * object, in that case we set the object as an alias.
	 *
	 * We load the master object reference if the {@link isAlias()} status is set. If the
	 * master cannot be found, we raise an exception.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses isInited()
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
		
		//
		// Get referenced object offset.
		// Note that at this point either the term or the tag is set.
		//
		$offset = ( $this->offsetExists( kTAG_TAG ) )
				? kTAG_TAG
				: kTAG_TERM;
	
		//
		// Get object reference.
		//
		$ref = $this->offsetGet( $offset );
		
		//
		// Get master.
		//
		$master = ( $this->offsetExists( kTAG_TAG ) )
				? static::GetTagMaster( $this->mDictionary, $ref, kQUERY_NID )
				: static::GetTermMaster( $this->mDictionary, $ref, kQUERY_NID );
	
		//
		// Handle master object.
		//
		if( (! $this->isAlias())
		 && ($master !== NULL) )
			$this->isAlias( TRUE );
		
		//
		// Handle master reference.
		//
		if( $this->isAlias() )
			$this->offsetSet( kTAG_MASTER, $master );
		
	} // preCommitPrepare.

		

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
	 * In this class we set the native identifier with the sequence number.
	 *
	 * @access protected
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
			$graph = $this->mDictionary->Graph();
	
			//
			// Set sequence number.
			//
			$this->offsetSet(
				kTAG_NID,
				(int)
					static::ResolveCollection(
						static::ResolveDatabase( $this->mDictionary, TRUE ) )
							->getSequenceNumber(
								static::kSEQ_NAME ) );
		
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

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	exportXMLObject																	*
	 *==================================================================================*/

	/**
	 * Export the current object in XML format
	 *
	 * We overload this method to add the {@link kTAG_TAG}, {@link kTAG_TERM} and
	 * {@link kTAG_ID_PERSISTENT} offsets to the untracked offsets list, since these are set
	 * in the unit node attributes.
	 *
	 * @param SimpleXMLElement		$theContainer		Dump container.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theUntracked		List of untracked offsets.
	 *
	 * @access protected
	 */
	protected function exportXMLObject( \SimpleXMLElement $theContainer,
										Wrapper			  $theWrapper,
														  $theUntracked )
	{
		//
		// Create unit.
		//
		$unit = static::xmlUnitElement( $theContainer );
		
		//
		// Add tag, term and persistent identifier to untracked.
		//
		$theUntracked = array_merge( $theUntracked,
									 array( kTAG_TAG, kTAG_TERM, kTAG_ID_PERSISTENT ) );
		
		//
		// Traverse object.
		//
		$this->exportXMLStructure( $this, $unit, $theWrapper, $theUntracked );
	
	} // exportXMLObject.

	 
	/*===================================================================================
	 *	loadXML																			*
	 *==================================================================================*/

	/**
	 * Load from XML
	 *
	 * In this class we overload the inherited method to handle the {@link kTAG_TAG},
	 * {@link kTAG_TERM} and {@link kTAG_ID_PERSISTENT} offsets whose data is found in the
	 * root node attributes.
	 *
	 * @param SimpleXMLElement		$theContainer		Export container (unit).
	 *
	 * @access public
	 */
	public function loadXML( \SimpleXMLElement $theContainer )
	{
		//
		// Load tag reference.
		//
		if( $theContainer[ kIO_XML_ATTR_REF_TAG ] !== NULL )
			$this[ kTAG_TAG ]
				= (string) $theContainer[ kIO_XML_ATTR_REF_TAG ];
	
		//
		// Load term reference.
		//
		if( $theContainer[ kIO_XML_ATTR_REF_TERM ] !== NULL )
			$this[ kTAG_TERM ]
				= (string) $theContainer[ kIO_XML_ATTR_REF_TERM ];
	
		//
		// Load persistent identifier.
		//
		if( $theContainer[ kIO_XML_ATTR_ID_PERSISTENT ] !== NULL )
			$this[ kTAG_ID_PERSISTENT ]
				= (string) $theContainer[ kIO_XML_ATTR_ID_PERSISTENT ];
		
		//
		// Load other data.
		//
		parent::loadXML( $theContainer );
	
	} // loadXML.

	
	/*===================================================================================
	 *	xmlUnitElement																	*
	 *==================================================================================*/

	/**
	 * Return XML unit element
	 *
	 * In this class we return the <tt>NODE</tt> element.
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	protected function xmlUnitElement( \SimpleXMLElement $theRoot )
	{
		//
		// Create element.
		//
		$element = parent::xmlUnitElement( $theRoot )->addChild( kIO_XML_META_NODE );
		
		//
		// Set tag.
		//
		if( $this->offsetExists( kTAG_TAG ) )
			$element->addAttribute( kIO_XML_ATTR_REF_TAG,
									$this->offsetGet( kTAG_TAG ) );
		
		//
		// Set term.
		//
		if( $this->offsetExists( kTAG_TERM ) )
			$element->addAttribute( kIO_XML_ATTR_REF_TERM,
									$this->offsetGet( kTAG_TERM ) );
		
		//
		// Set persistent identifier.
		//
		if( $this->offsetExists( kTAG_ID_PERSISTENT ) )
			$element->addAttribute( kIO_XML_ATTR_ID_PERSISTENT,
									$this->offsetGet( kTAG_ID_PERSISTENT ) );
		
		return $element;															// ==>
	
	} // xmlUnitElement.

		

/*=======================================================================================
 *																						*
 *								PROTECTED GRAPH UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	matchGraphNode																	*
	 *==================================================================================*/

	/**
	 * Match graph node
	 *
	 * In this class we handle graph nodes as follows:
	 *
	 * <ul>
	 *	<li><em>Tag reference</em>: If the node references a tag, we get it and return its
	 *		{@link kTAG_ID_GRAPH} offset. This is implicit, since tags store their graph
	 *		nodes.
	 *	<li><em>Term reference</em>: Term references come in many kinds an colours, to
	 *		match a node in the graph we need to perform the following query:
	 *	 <ul>
	 *		<li><em>Select node type</em>: Node types are enumerated sets, which means that
	 *			we first need to select which node type to use. This is done by eliminating
	 *			from the types the {@link kTYPE_NODE_ROOT}, {@link kTYPE_NODE_PROPERTY} and
	 *			the {@link kTYPE_NODE_ENUMERATED}; this should leave us with a single value.
	 *			This is performed by the {@link getNodeDomain()} method.
	 *		<li><em>Match nodes</em>: We query the collection in <tt>AND</tt> as follows:
	 *		 <ul>
	 *			<li>Match term identifier.
	 *			<li>Match node type.
	 *			<li>Match records that feature the {@link kTAG_ID_GRAPH} property.
	 *		 </ul>
	 *			If we get more than one node, we pick the first one; this should not happen,
	 *			since we match this case.
	 *	 </ul>
	 * </ul>
	 *
	 * @param DatabaseGraph			$theGraph			Graph connection.
	 *
	 * @access protected
	 * @return integer				Graph node identifier, or <tt>FALSE</tt>.
	 */
	protected function matchGraphNode( DatabaseGraph $theGraph )
	{
		//
		// Handle tag reference.
		//
		if( $this->offsetExists( kTAG_TAG ) )
			return $this->getReferenced()->offsetGet( kTAG_ID_GRAPH );				// ==>
		
		//
		// Get node domain.
		//
		$domain = $this->getNodeDomain();
		
		//
		// Compile criteria.
		//
		$criteria = Array();
		$criteria[ (string) kTAG_TERM ] = $this->offsetGet( kTAG_TERM );
		$criteria[ (string) kTAG_ID_GRAPH ] = array( '$exists' => TRUE );
		$criteria[ (string) kTAG_NODE_TYPE ] = ( $domain == kTYPE_NODE_TERM )
											 ? array( '$exists' => FALSE )
											 : $domain;
		
		//
		// Match node.
		//
		$node
			= static::ResolveCollection(
				static::ResolveDatabase( $this->mDictionary, TRUE ) )
					->matchOne( $criteria, kQUERY_OBJECT );
		
		//
		// Return graph node reference.
		//
		if( $node !== NULL )
			return $node->offsetGet( kTAG_ID_GRAPH );								// ==>
		
		return FALSE;																// ==>
	
	} // matchGraphNode.

	 
	/*===================================================================================
	 *	setGraphProperties																*
	 *==================================================================================*/

	/**
	 * Compute graph labels and properties
	 *
	 * This method will only be called for nodes that reference terms, in this case we
	 * overload this method to set the {@link kTAG_ID_LOCAL} to the term's native
	 * identifier.
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
		// Set label.
		//
		$theLabels[] = $this->getNodeDomain();
	
		//
		// Set identifier.
		//
		$theProperties[ 'GID' ] = $this->offsetGet( kTAG_TERM );
	
	} // setGraphProperties.

	 
	/*===================================================================================
	 *	getNodeDomain																	*
	 *==================================================================================*/

	/**
	 * Get node domain
	 *
	 * The node domain is the node type that characterises the current node, it is the
	 * element of the {@link kTAG_NODE_TYPE} enumerated set which is not among the
	 * {@link kTYPE_NODE_ROOT}, {@link kTYPE_NODE_PROPERTY} and the
	 * {@link kTYPE_NODE_ENUMERATED} node types.
	 *
	 * The method will exclude the above values and return the first element left; if there
	 * are no elements left, it will return the {@link kTYPE_NODE_TERM}.
	 *
	 * @param array					$theLabels			Labels.
	 * @param array					$theProperties		Properties.
	 *
	 * @access protected
	 */
	protected function getNodeDomain()
	{
		//
		// Handle missing types.
		//
		if( ! $this->offsetExists( kTAG_NODE_TYPE ) )
			return kTYPE_NODE_TERM;													// ==>
		
		//
		// Reduce types.
		//
		$types
			= array_diff(
				$this->offsetGet( kTAG_NODE_TYPE ),
				array( kTYPE_NODE_ROOT, kTYPE_NODE_PROPERTY, kTYPE_NODE_ENUMERATED ) );
		
		//
		// Return first.
		//
		if( count( $types ) )
			return array_shift( $types );											// ==>
		
		return kTYPE_NODE_TERM;														// ==>
	
	} // getNodeDomain.

	 

} // class Node.


?>
