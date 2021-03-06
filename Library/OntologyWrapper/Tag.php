<?php

/**
 * Tag.php
 *
 * This file contains the definition of the {@link Tag} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\MetadataObject;
use OntologyWrapper\Term;
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
 * A tag object is used to <i>identify</i>, <i>document</i> and <i>share</i> a data
 * property, it represents the <i>metadata</i> of a data property, or its <i>definition</i>
 * in an ontology.
 *
 * This class features a {@link kTAG_TERMS} offset which is an array of {@link Term}
 * references representing a path of vertex and predicate terms of the ontology graph, this
 * chain of elements represents a lexical construct, like a phrase, that provides the
 * explanation or definition of the current object.
 *
 * Using a chain of terms, rather than a single term, to provide metadata for a data element
 * gives much more flexibility and reusability when defining a data dictionary. This
 * sequence is divided in three main sections:
 *
 * <ul>
 *	<li><i>Feature</i>: The first vertex represents the data element feature or trait, this
 *		term defines <em>what the data element is</em>.
 *	<li><i>Method</i>: The vertices between the first and the last ones represent the
 *		<em>methodology</em> or <em>method</em> by which the described data was obtained.
 *	<li><i>Scale</i>: The last vertex of the path represents the <em>unit</em> or
 *		<em>scale</em> in which the described value is expressed in.
 * </ul>
 *
 * The concatenation of these term references, separated by the
 * {@link kTOKEN_INDEX_SEPARATOR} becomes the object global identifier which is stored in
 * the object's native identifier offset.
 *
 * Tags have another offset, {@link kTAG_ID_HASH}, which is a hex sequence number prefixed
 * by the {@link kTOKEN_TAG_PREFIX} token: this value must be unique within the tags domain
 * of the current ontology. Unlike global identifiers, this value may change across
 * implementations, but this is the value used to uniquely identify tags among the other
 * elements of the ontology and database.
 *
 * <em>All offsets in all classes, including this one, are tag sequence numbers, which makes
 * the Tag class key in the structure and behaviour of all the elements implemented in this
 * library</em>.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. This required attribute holds
 *		a string value which represents the global identifier of the current object. This
 *		identifier is immutable and represents the unique key. This string is constituted by
 *		the concatenation of all term references stored in the current object's branch,
 *		{@link kTAG_TERMS}. This attribute must be managed with its offset; in derived
 *		classes it will be automatically assigned end only available as read-only.
 *	<li><tt>{@link kTAG_ID_HASH}</tt>: <em>Sequence</em>. This required attribute holds
 *		a hexadecimal string preceded by the {@link kTOKEN_TAG_PREFIX} token which
 *		represents the current object's sequence number, as with the global identifier, this
 *		value must be unique, except that it may change across implementations. All offset
 *		keys in all objects derived from this class ancestor are references to this sequence
 *		number. This attribute must be managed with its offset; in derived classes it will
 *		be automatically assigned and only available as read-only.
 *	<li><tt>{@link kTAG_ID_SYMBOL}</tt>: <em>Symbol</em>. This optional attribute holds
 *		a string value which represents the current object's symbol or variable name. This
 *		value will be used to reference the current tag in data templates, so it is
 *		important that this value be unique within all templates in which the tag is
 *		referenced; ideally, this value should be equivalent to the global unique
 *		identifier.
 *	<li><tt>{@link kTAG_ID_GRAPH}</tt>: <em>Property graph node</em>. If the wrapper uses
 *		a graph database, this property will be used to reference the graph node which
 *		represents the current tag as a data property; it is an integer value which is
 *		automatically managed.
 *	<li><tt>{@link kTAG_TERMS}</tt>: <em>Branch</em>. This required attribute holds
 *		the list of terms comprising the current tag: this is an array of term references
 *		provided as an odd sequence of vertices and predicates forming a path of the
 *		ontology graph in which the first vertex defines the feature, the middle ones
 *		define the methodology and the last element indicates the scale or unit. To populate
 *		the elements of this path, use the {@link TermPush()} amd {@link TermPop()}
 *		offset accessor methods which respectively add and remove elements of the branch as
 *		if it was a stack.
 *	<li><tt>{@link kTAG_DATA_TYPE}</tt>: <em>Data type</em>. This attribute is an enumerated
 *		value indicating the <em>data type</em> that the value of the property defined by
 *		the current tag may take.
 *	<li><tt>{@link kTAG_DATA_KIND}</tt>: <em>Data kind</em>. This attribute is an enumerated
 *		set of values providing the <em>data attributes</em> of the property defined by the
 *		current tag. This may be whether the property is a list of values, or if the
 *		property is required or not. To populate and handle individual data kinds use the
 *		{@link DataKind()} offset accessor method.
 *	<li><tt>{@link kTAG_LABEL}</tt>: <em>Label</em>. The label represents the <i>name or
 *		short description</i> of the data property that the current object defines. All tags
 *		<em>should</em> have a label, since this is how human users will be able to identify
 *		and select them. This attribute has the {@link kTYPE_LANGUAGE_STRING} data type,
 *		which is constituted by a list of elements in which the {@link kTAG_LANGUAGE} item
 *		holdsthe label language code and the {@link kTAG_TEXT} holds the label text. To
 *		populate and handle labels by language, use the {@link Label()} offset accessor
 *		method.
 *	<li><tt>{@link kTAG_DESCRIPTION}</tt>: <em>Description</em>. The description tag
 *		represents the <i>description or extended definition</i> of the property that the
 *		current tag defines. The description is similar to the <em>definition</em>, except
 *		that while the definition provides a description of the object it defines unrelated
 *		to context, the description adds to the definition the elements added by the current
 *		context. All tags <em>should</em> have a description, if the tag label is not enough
 *		to provide a sufficient description or definition. Descriptions have the
 *		{@link kTYPE_LANGUAGE_STRING} data type, which is constituted by a list of elements
 *		in which the {@link kTAG_LANGUAGE} item holds the description language code and the
 *		{@link kTAG_TEXT} holds the description text. To populate and handle
 *		descriptions by language, use the {@link Description()} offset accessor method.
 *	<li><tt>{@link kTAG_SYNONYM}</tt>: <em>Synonyms</em>. This attribute is a <em>set of
 *		strings</em> representing <em>alternate identifiers of this tag</em>, not formally
 *		defined in the current data set.
 *	<li><tt>{@link kTAG_MIN_VAL}</tt>: <em>Minimum value</em>. This attribute is a floating
 *		point value representing the <em>minimum</em> of the <em>range of values</em>
 *		featured by the tag.
 *	<li><tt>{@link kTAG_MIN_RANGE}</tt>: <em>Minimum range</em>. This attribute is a
 *		floating point value representing the <em>minimum</em> value that the current tag
 *		may take.
 *	<li><tt>{@link kTAG_MAX_VAL}</tt>: <em>Maximum value</em>. This attribute is a floating
 *		point value representing the <em>maximum</em> of the <em>range of values</em>
 *		featured by the tag.
 *	<li><tt>{@link kTAG_MAX_RANGE}</tt>: <em>Maximum range</em>. This attribute is a
 *		floating point value representing the <em>maximum</em> value that the current tag
 *		may take.
 *	<li><tt>{@link kTAG_PATTERN}</tt>: <em>Regular expression pattern</em>. This attribute
 *		holds a <em>string</em> which represents a <em>regular expression pattern</em> which
 *		can be used to <em>validate data identified by this tag</em>.
 *	<li><tt>{@link kTAG_DECIMALS}</tt>: <em>Decimal places</em>. This attribute holds an
 *		<em>integer</em> which represents the number of decimal places a floating point
 *		number should display.
 *	<li><tt>{@link kTAG_TAG_OFFSETS}</tt>: <em>Tag offsets</em>. This attribute is handled
 *		automatically and should not be modified by clients, it collects all the offsets
 *		(sequence of tags indicating the structure path to a leaf offset) in which the tag
 *		was used by other tag objects as a leaf offset, that is, an offset holding a value.
 *	<li><tt>{@link kTAG_TERM_OFFSETS}</tt>: <em>Term offsets</em>. This attribute is handled
 *		automatically and should not be modified by clients, it collects all the offsets
 *		(sequence of tags indicating the structure path to a leaf offset) in which the tag
 *		was used by term objects as a leaf offset, that is, an offset holding a value.
 *	<li><tt>{@link kTAG_NODE_OFFSETS}</tt>: <em>Node offsets</em>. This attribute is handled
 *		automatically and should not be modified by clients, it collects all the offsets
 *		(sequence of tags indicating the structure path to a leaf offset) in which the tag
 *		was used by node objects as a leaf offset, that is, an offset holding a value.
 *	<li><tt>{@link kTAG_EDGE_OFFSETS}</tt>: <em>Edge offsets</em>. This attribute is handled
 *		automatically and should not be modified by clients, it collects all the offsets
 *		(sequence of tags indicating the structure path to a leaf offset) in which the tag
 *		was used by edge objects as a leaf offset, that is, an offset holding a value.
 *	<li><tt>{@link kTAG_UNIT_OFFSETS}</tt>: <em>Unit offsets</em>. This attribute is
 *		handled automatically and should not be modified by clients, it collects all the
 *		offsets (sequence of tags indicating the structure path to a leaf offset) in which
 *		the tag was used by unit objects as a leaf offset, that is, an offset holding a
 *		value.
 *	<li><tt>{@link kTAG_USER_OFFSETS}</tt>: <em>Entity offsets</em>. This attribute is
 *		handled automatically and should not be modified by clients, it collects all the
 *		offsets (sequence of tags indicating the structure path to a leaf offset) in which
 *		the tag was used by entity objects as a leaf offset, that is, an offset holding a
 *		value.
 *	<li><tt>{@link kTAG_TAG_STRUCT}</tt>: <em>Container structure</em>. This attribute holds
 *		a tag object reference that must be a structure, if set, it indicates that the
 *		property defined by the current tag must be stored in the property referenced by the
 *		the value of this attribute.
 *		This means that whenever a property defined by a tag featuring this offset is stored
 *		in an object, this property must be placed inside of the offset referenced by the
 *		value of this attribute.
 *	<li><tt>{@link kTAG_TAG_STRUCT_IDX}</tt>: <em>Container structure index</em>. This
 *		attribute should only be featured by tags which define a list of structures. The
 *		value of this attribute is a tag object reference which represents the structure
 *		element which acts as the list index. This means that no two elements of a list of
 *		structures may share the same value in the offset defined by the current attribute.
 *		This also means that the offset defined in this property <em>is required</em> by the
 *		hosting structure.
 * </ul>
 *
 * The {@link __toString()} method will return the value stored in the global identifier,
 * if set, or the computed global identifier which is the concatenation of all term
 * references stored in the {@link kTAG_TERMS} offset, separated by the
 * {@link kTOKEN_INDEX_SEPARATOR}.
 *
 * The class also features a pair of methods, {@link Feature()} and {@link Scale()}, which
 * respectively return the first and last vertex of the ontology branch this object
 * represents: the feature is a term that represents the main feature or trait that the
 * current tag defines, the scale is a term that represents the scale or unit in which
 * values defined by the current tag are expressed in.
 *
 * Objects of this class can hold any additional attribute that is considered necessary or
 * useful to define and share the current tag. In this class we define only those
 * attributes that constitute the core functionality of the object, derived classes will add
 * attributes specific to the domain in which the object will operate and eventually
 * restrict the functionality of other offsets to provide referential integrity.
 *
 * The object is considered initialised, {@link isInited()}, if it has at least the terms
 * path, {@link kTAG_TERMS}, with an odd number of elements, the data type,
 * {@link kTAG_DATA_TYPE}, and the label, {@link kTAG_LABEL}.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */
class Tag extends MetadataObject
{
	/**
	 * Label trait.
	 *
	 * We use this trait to handle labels.
	 */
	use	traits\Label;

	/**
	 * Definition trait.
	 *
	 * We use this trait to handle definitions.
	 */
	use	traits\Definition;

	/**
	 * Synonym trait.
	 *
	 * We use this trait to handle synonyms.
	 */
	use	traits\Synonym;

	/**
	 * Terms trait.
	 *
	 * We use this trait to handle the terms list.
	 */
	use	traits\Terms;

	/**
	 * Data kind trait.
	 *
	 * We use this trait to handle data kinds.
	 */
	use	traits\DataKind;

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
	 * In this class we link the inited status with the presence of the terms list, the data
	 * type and the label.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @uses instantiateObject()
	 * @uses TermCount()
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_TERMS ) &&
						 \ArrayObject::offsetExists( kTAG_DATA_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_LABEL ) );

	} // Constructor.

	 
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return global identifier</h4>
	 *
	 * The global identifier of tags is stored in its {@link kTAG_NID} offset: if set, this
	 * method will return that value. If that offset is not set, the method will concatenate
	 * the value of all the elements of the {@link kTAG_TERMS} separated by the
	 * {@link kTOKEN_INDEX_SEPARATOR} token.
	 *
	 * If the {@link kTAG_TERMS} offset is not set, the method will return an empty string.
	 *
	 * @access public
	 * @return string				The global identifier.
	 */
	public function __toString()
	{
		//
		// Get native identifier.
		//
		if( \ArrayObject::offsetExists( kTAG_NID ) )
			return \ArrayObject::offsetGet( kTAG_NID );								// ==>
		
		//
		// Compute global identifier.
		//
		if( \ArrayObject::offsetExists( kTAG_TERMS ) )
			return implode( kTOKEN_INDEX_SEPARATOR,
							\ArrayObject::offsetGet( kTAG_TERMS ) );				// ==>
		
		return '';																	// ==>
	
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
	 * In this class we shadow this method, since there cannot be alias tags.
	 *
	 * @param boolean				$doSet				<tt>TRUE</tt> to set.
	 *
	 * @access public
	 */
	public function setAlias( $doSet = TRUE )											   {}

	

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
	 * In this class we return the tag's label.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 */
	public function getName( $theLanguage )
	{
		//
		// Check label.
		//
		if( $this->offsetExists( kTAG_LABEL ) )
			return OntologyObject::SelectLanguageString(
				$this->offsetGet( kTAG_LABEL ), $theLanguage );						// ==>
		
		return NULL;																// ==>
	
	} // getName.

		

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
		$database = $theWrapper->metadata();
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
	 *	<li><tt>{@link kTAG_ID_HASH}</tt>: Sequence number.
	 *	<li><tt>{@link kTAG_TERMS}</tt>: Terms path.
	 *	<li><tt>{@link kTAG_LABEL}</tt>: Labels.
	 *	<li><tt>{@link kTAG_TAG_COUNT}</tt>: Tags count.
	 *	<li><tt>{@link kTAG_TERM_COUNT}</tt>: Terms count.
	 *	<li><tt>{@link kTAG_NODE_COUNT}</tt>: Nodes count.
	 *	<li><tt>{@link kTAG_EDGE_COUNT}</tt>: Edges count.
	 *	<li><tt>{@link kTAG_UNIT_COUNT}</tt>: Units count.
	 *	<li><tt>{@link kTAG_USER_COUNT}</tt>: Users count.
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
		// Set sequence identifier index.
		//
		$collection->createIndex( array( kTAG_ID_HASH => 1 ),
								  array( "name" => "SEQUENCE",
								  		 "unique" => TRUE ) );
		
		//
		// Set path index.
		//
		$collection->createIndex( array( kTAG_TERMS => 1 ),
								  array( "name" => "PATH" ) );
		
		//
		// Set label index.
		//
		$collection->createIndex( array( kTAG_LABEL => 1 ),
								  array( "name" => "LABEL" ) );
		
		//
		// Set reference counts.
		//
		$collection->createIndex( array( kTAG_TAG_COUNT => 1 ),
								  array( "name" => "TAGS_COUNT",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_TERM_COUNT => 1 ),
								  array( "name" => "TERMS_COUNT",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_NODE_COUNT => 1 ),
								  array( "name" => "NODES_COUNT",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_EDGE_COUNT => 1 ),
								  array( "name" => "EDGES_COUNT",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_UNIT_COUNT => 1 ),
								  array( "name" => "UNITS_COUNT",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_USER_COUNT => 1 ),
								  array( "name" => "USERS_COUNT",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_SESSION_COUNT => 1 ),
								  array( "name" => "SESSIONS_COUNT",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_TRANSACTION_COUNT => 1 ),
								  array( "name" => "TRANSACTIONS_COUNT",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_FILE_COUNT => 1 ),
								  array( "name" => "FILES_COUNT",
								  		 "sparse" => TRUE ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.

		

/*=======================================================================================
 *																						*
 *								STATIC UPDATE INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	UpdateRange																		*
	 *==================================================================================*/

	/**
	 * Update range
	 *
	 * This method should update the range of the provided tags, the method will first
	 * update the minimum, {@link kTAG_MIN_VAL}, and the maximum, {@link kTAG_MAX_VAL},
	 * properties of the provided tag(s).
	 *
	 * Once these properties are update, the method will update the tag's
	 * {@link kTAG_OBJECT_TAGS}, {@link kTAG_TAG_OFFSETS} and {@link kTAG_OBJECT_OFFSETS}
	 * with the above bounds tags.
	 *
	 * The method expects an array containing the tag identifiers and the bounds:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The array index should contain the tag identifier corresponding
	 *		to the provided offset parameter.
	 *	<li><tt>value</tt>: The array value should be an array indexed by
	 *		{@link kTAG_MIN_VAL} and/or {@link kTAG_MAX_VAL} with the corresponding values
	 *		being the bounds.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Database wrapper.
	 * @param array					$theBounds			Tag identifiers and range bounds.
	 * @param int					$theOffset			Tag identifier sequence number.
	 *
	 * @static
	 *
	 * @throws Exception
	 */
	static function UpdateRange( Wrapper $theWrapper, $theBounds,
													  $theOffset = kTAG_ID_HASH )
	{
		//
		// Check bounds.
		//
		if( ! is_array( $theBounds ) )
			throw new \Exception(
				"Invalid bounds parameter: "
			   ."expecting an array." );										// !@! ==>
		
		//
		// Validate tag identifier.
		//
		$theWrapper->getObject( $theOffset, TRUE );
		
		// Resolve tag collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) );
		
		//
		// Iterate bounds.
		//
		foreach( $theBounds as $tag => $bounds )
			$collection->modify(
				array( $theOffset => $tag ),
				array( '$min' => array( (string) kTAG_MIN_VAL
											  => $bounds[ kTAG_MIN_VAL ] ),
					   '$max' => array( (string) kTAG_MAX_VAL
					   						  => $bounds[ kTAG_MAX_VAL ] ) ),
				array( 'multi' => FALSE, 'upsert' => FALSE ) );
		
	} // UpdateRange.

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ExternalOffsets																	*
	 *==================================================================================*/

	/**
	 * Return external offsets
	 *
	 * In this class we return the offsets featured by tag objects which record tag usage
	 * statistics:
	 *
	 * <ul>
	 *	<li><em>Offset paths</em>:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_TAG_OFFSETS}</tt>: List of offset paths used by the current
	 *			tag in all tags in which it is referenced.
	 *		<li><tt>{@link kTAG_TERM_OFFSETS}</tt>: List of offset paths used by the current
	 *			tag in all terms in which it is referenced.
	 *		<li><tt>{@link kTAG_NODE_OFFSETS}</tt>: List of offset paths used by the current
	 *			tag in all nodes in which it is referenced.
	 *		<li><tt>{@link kTAG_EDGE_OFFSETS}</tt>: List of offset paths used by the current
	 *			tag in all edges in which it is referenced.
	 *		<li><tt>{@link kTAG_UNIT_OFFSETS}</tt>: List of offset paths used by the current
	 *			tag in all units in which it is referenced.
	 *		<li><tt>{@link kTAG_USER_OFFSETS}</tt>: List of offset paths used by the
	 *			current tag in all entities in which it is referenced.
	 *	 </ul>
	 *	<li><em>Usage ranges</em>:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_MIN_VAL}</tt>: Minimum range value.
	 *		<li><tt>{@link kTAG_MAX_VAL}</tt>: Maximum range value.
	 *	 </ul>
	 * </ul>
	 *
	 * @static
	 * @return array				List of external offsets.
	 */
	static function ExternalOffsets()
	{
		return array_merge(
			parent::ExternalOffsets(),
			array( kTAG_TAG_OFFSETS, kTAG_TERM_OFFSETS,
				   kTAG_NODE_OFFSETS, kTAG_EDGE_OFFSETS,
				   kTAG_UNIT_OFFSETS, kTAG_USER_OFFSETS ),
			array( kTAG_MIN_VAL, kTAG_MAX_VAL ) );									// ==>
	
	} // ExternalOffsets.

	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_ID_HASH}</tt>: Tag offset number.
	 *	<li><tt>{@link kTAG_TERMS}</tt>: Tag terms path.
	 *	<li><tt>{@link kTAG_DATA_TYPE}</tt>: Tag data type.
	 *	<li><tt>{@link kTAG_DATA_KIND}</tt>: Tag data kind.
	 *	<li><tt>{@link kTAG_LABEL}</tt>: Tag label.
	 *	<li><tt>{@link kTAG_DESCRIPTION}</tt>: Tag description.
	 *	<li><tt>{@link kTAG_SYNONYM}</tt>: Tag synonyms.
	 *	<li><tt>{@link kTAG_MIN_VAL}</tt>: Minimum featured value.
	 *	<li><tt>{@link kTAG_MAX_VAL}</tt>: Maximum featured value.
	 *	<li><tt>{@link kTAG_MIN_RANGE}</tt>: Minimum allowed value.
	 *	<li><tt>{@link kTAG_MAX_RANGE}</tt>: Maximum allowed value.
	 *	<li><tt>{@link kTAG_PATTERN}</tt>: Regular expression pattern.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_ID_HASH,
								   kTAG_TERMS,
								   kTAG_DATA_TYPE, kTAG_DATA_KIND,
								   kTAG_LABEL, kTAG_DESCRIPTION,
								   kTAG_LABEL, kTAG_DESCRIPTION, kTAG_SYNONYM,
								   kTAG_MIN_VAL, kTAG_MAX_VAL,
								   kTAG_MAX_RANGE, kTAG_MAX_RANGE,
								   kTAG_PATTERN ) );								// ==>
	
	} // DefaultOffsets.

		

/*=======================================================================================
 *																						*
 *								STATIC CLUSTER INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GetClusterKey																	*
	 *==================================================================================*/

	/**
	 * Get cluster key
	 *
	 * This method will return the tag cluster key associated to the provided terms list.
	 *
	 * By default we cluster tags by feature term.
	 *
	 * @param mixed					$theValue			Tag or tag terms.
	 *
	 * @static
	 * @return string				Tag cluster key.
	 */
	static function GetClusterKey( $theValue )
	{
		//
		// Handle tag.
		//
		if( $theValue instanceof Tag )
			return ( is_array( $tmp = $theValue[ kTAG_TERMS ] ) )
				 ? $tmp[ 0 ]														// ==>
				 : NULL;															// ==>
		
		//
		// handle terms path.
		//
		if( is_array( $theValue ) )
			return $theValue[ 0 ];													// ==>
		
		return NULL;																// ==>
	
	} // GetClusterKey.

		

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
	 * In this class we link the inited status with the presence of the terms list, the data
	 * type and the label.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_DATA_TYPE kTAG_LABEL
	 *
	 * @uses TermCount()
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_TERMS ) &&
						 \ArrayObject::offsetExists( kTAG_DATA_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_LABEL ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of the terms list, the data
	 * type and the label.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_DATA_TYPE kTAG_LABEL
	 *
	 * @uses TermCount()
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_TERMS ) &&
						 \ArrayObject::offsetExists( kTAG_DATA_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_LABEL ) );
	
	} // postOffsetUnset.

		

/*=======================================================================================
 *																						*
 *							PROTECTED PERSISTENCE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	deleteObject																	*
	 *==================================================================================*/

	/**
	 * Delete the object
	 *
	 * We overload this method to remove the object from the data dictionary cache.
	 *
	 * @access protected
	 * @return mixed				Native identifier, <tt>NULL</tt> or <tt>FALSE</tt>.
	 */
	protected function deleteObject()
	{
		//
		// Call parent method
		//
		$id = parent::deleteObject();
		if( $id === FALSE )
			return FALSE;															// ==>
		
		//
		// Reset cache.
		//
		$this->mDictionary->delTag( $this, 0 );
		
		return $id;																	// ==>
	
	} // deleteObject.

		

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
	 * In this class we copy the feature term label to the current object if not yet set.
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
		// Check label.
		//
		if( ! $this->offsetExists( kTAG_LABEL ) )
		{
			//
			// Check terms.
			//
			if( $this->offsetExists( kTAG_TERMS ) )
			{
				//
				// Get feature term.
				//
				$term = $this->offsetGet( kTAG_TERMS )[ 0 ];
				
				//
				// Handle object.
				//
				if( $term instanceof Term )
				{
					//
					// Copy label.
					//
					if( $term->offsetExists( kTAG_LABEL ) )
						$this->offsetSet( kTAG_LABEL, $term->offsetGet( kTAG_LABEL ) );
					else
						throw new \Exception(
							"Unable to commit: "
						   ."missing term label." );							// !@! ==>
				
				} // Term object.
				
				//
				// Handle reference.
				//
				else
				{
					//
					// Instantiate term.
					//
					$term = new Term( $this->mDictionary, $term );
					
					//
					// Set label.
					//
					if( $term->offsetExists( kTAG_LABEL ) )
						$this->offsetSet( kTAG_LABEL, $term->offsetGet( kTAG_LABEL ) );
					else
						throw new \Exception(
							"Unable to commit: "
						   ."missing term label." );							// !@! ==>
				
				} // Term reference.
			
			} // Has terms path.
		
		} // Missing label.
		
		//
		// Check structure list index.
		//
		if( $this->offsetExists( kTAG_TAG_STRUCT_IDX ) )
		{
			//
			// Get data type and kind.
			//
			$type = $this->offsetGet( kTAG_DATA_TYPE );
			$kind = $this->offsetGet( kTAG_DATA_KIND );
			
			//
			// Assert current tag is a list of structures.
			//
			if( ($type != kTYPE_STRUCT)
			 || ($kind === NULL)
			 || (! in_array( kTYPE_LIST, $kind )) )
				throw new \Exception(
					"Cannot be a structure list index: "
				   ."The current tag is not a structure and not a list." );		// !@! ==>
		
		} // Has structure list index.
		
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
	
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
	 * In this class we set the native identifier, if not yet filled, with the global
	 * identifier generated by the {@link __toString()} method and we set the sequence
	 * number, {@link kTAG_ID_HASH}, if it is not yet set, by requesting it from the
	 * database of the current object's container.
	 *
	 * We only perform the above operations if the object is not committed.
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
			// Set native identifier.
			//
			if( ! \ArrayObject::offsetExists( kTAG_NID ) )
				\ArrayObject::offsetSet( kTAG_NID, $this->__toString() );
		
			//
			// Set sequence number.
			//
			if( ! \ArrayObject::offsetExists( kTAG_ID_HASH ) )
				$this->offsetSet(
					kTAG_ID_HASH,
					kTOKEN_TAG_PREFIX
				   .dechex(
				   		(int) static::ResolveCollection(
							static::ResolveDatabase( $this->mDictionary ) )
								->getSequenceNumber( static::kSEQ_NAME ) ) );
		
		} // Not committed.
	
	} // preCommitObjectIdentifiers.

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-COMMIT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postInsert																		*
	 *==================================================================================*/

	/**
	 * Handle object after insert
	 *
	 * We overload this method to add/update the object in the data dictionary cache.
	 *
	 * @param array					$theOffsets			Tag offsets to be added.
	 * @param array					$theReferences		Object references to be incremented.
	 * @param bitfield				$theOptions			Operation options.
	 *
	 * @uses ResolveOffsetsTag()
	 * @uses updateObjectReferenceCount()
	 */
	protected function postInsert( $theOffsets, $theReferences, $theOptions )
	{
		//
		// Call parent method.
		//
		parent::postInsert( $theOffsets, $theReferences, $theOptions );
		
		//
		// Set cache.
		//
		$this->mDictionary
			->setTag(
				array_intersect_key(
					$this->getArrayCopy(),
					$this->mDictionary->getTagOffsets() ),
				0 );
	
	} // postInsert.

		

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
	 * In this class we ensure the object has the sequence number, {@link kTAG_ID_HASH}
	 * and the native identifier, {@link kTAG_NID}.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 *
	 * @see kTAG_NID kTAG_ID_HASH
	 *
	 * @uses isInited()
	 */
	protected function isReady()
	{
		return ( parent::isReady()
			  && $this->offsetExists( kTAG_ID_HASH )
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
	 * In this class we return the {@link kTAG_ID_HASH}, {@link kTAG_TERMS},
	 * {@link kTAG_DATA_TYPE} and the {@link kTAG_DATA_KIND} offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_ID_HASH kTAG_TERMS kTAG_DATA_TYPE kTAG_DATA_KIND
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
							array( kTAG_ID_HASH, kTAG_TERMS,
								   kTAG_DATA_TYPE, kTAG_DATA_KIND ) );				// ==>
	
	} // lockedOffsets.

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	xmlUnitElement																	*
	 *==================================================================================*/

	/**
	 * Return XML unit element
	 *
	 * In this class we return the <tt>TAG</tt> element.
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	protected function xmlUnitElement( \SimpleXMLElement $theRoot )
	{
		return parent::xmlUnitElement( $theRoot )->addChild( kIO_XML_META_TAG );	// ==>
	
	} // xmlUnitElement.

		

/*=======================================================================================
 *																						*
 *								PROTECTED GRAPH UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	createRelatedGraphNodes															*
	 *==================================================================================*/

	/**
	 * Create related graph nodes
	 *
	 * In this class we create or reference all terms used in the tag path, the first term
	 * will be related to the current node via the {@link kPREDICATE_TRAIT_OF} predicate,
	 * the last term via the {@link kPREDICATE_SCALE_OF} and the eventual middle terms via
	 * the {@link kPREDICATE_METHOD_OF} predicate.
	 *
	 * Note that the terms we use are the odd ones, the even ones, the predicates, are not
	 * considered.
	 *
	 * @param DatabaseGraph			$theGraph			Graph connection.
	 *
	 * @access protected
	 */
	protected function createRelatedGraphNodes( DatabaseGraph $theGraph )
	{
		//
		// Get current graph node reference.
		//
		$id = $this->offsetGet( kTAG_ID_GRAPH );
		
		//
		// Get terms collection.
		//
		$collection
			= Term::ResolveCollection(
				Term::ResolveDatabase( $this->mDictionary, TRUE ) );
		
		//
		// Iterate tag terms.
		//
		$path = $this->offsetGet( kTAG_TERMS );
		for( $i = 0; $i < count( $path ); $i += 2 )
		{
			//
			// Get term object.
			//
			$term
				= $collection->matchOne(
					array( kTAG_NID => $path[ $i ] ),
					kQUERY_ASSERT | kQUERY_OBJECT );
			
			//
			// Get node reference.
			//
			if( $term->offsetExists( kTAG_ID_GRAPH ) )
				$term_id = $term->offsetGet( kTAG_ID_GRAPH );
			
			//
			// Create node.
			//
			else
			{
				//
				// Set node properties.
				//
				$labels = $properties = Array();
				$labels[] = kDOMAIN_ATTRIBUTE;
				$properties[ 'STORE' ] = Term::kSEQ_NAME;
				$properties[ 'CLASS' ] = get_class( $term );
				$properties[ 'GID' ] = $path[ $i ];
				$properties[ kTAG_NID ] = $path[ $i ];
				
				//
				// Create node.
				//
				$term_id = $theGraph->setNode( $properties, $labels );
				
				//
				// Update term.
				//
				$collection->replaceOffsets(
					$path[ $i ],
					array( kTAG_ID_GRAPH => $term_id ) );
			
			} // Term not in graph.
		
			//
			// Handle feature.
			//
			if( ! $i )
				$theGraph->setEdge( $term_id, kPREDICATE_TRAIT_OF, $id );
		
			//
			// Handle method.
			//
			elseif( $i < (count( $path ) - 1) )
				$theGraph->setEdge( $term_id, kPREDICATE_METHOD_OF, $id );
		
			//
			// Handle scale.
			//
			if( $i == (count( $path ) - 1) )
				$theGraph->setEdge( $term_id, kPREDICATE_SCALE_OF, $id );
		
		} // Iterating tag terms.
	
	} // createRelatedGraphNodes.

	 
	/*===================================================================================
	 *	setGraphProperties																*
	 *==================================================================================*/

	/**
	 * Compute graph labels and properties
	 *
	 * In this class we call the parent method, then we set the label to
	 * {@link kDOMAIN_PROPERTY} and set the data type property.
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
		$theLabels[] = kDOMAIN_PROPERTY;
	
		//
		// Set identifier.
		//
		$theProperties[ 'GID' ] = $this->offsetGet( kTAG_NID );
	
	} // setGraphProperties.

	 

} // class Tag.


?>
