<?php

/**
 * Tag.php
 *
 * This file contains the definition of the {@link Tag} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\PersistentObject;
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
 * Tags have another offset, {@link kTAG_ID_SEQUENCE}, which is an integer sequence number:
 * this value must be unique within the tags domain of the current ontology. Unlike global
 * identifiers, this value may change across implementations, but this is the value used to
 * uniquely identify tags among the other elements of the ontology and database.
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
 *	<li><tt>{@link kTAG_ID_SEQUENCE}</tt>: <em>Sequence</em>. This required attribute holds
 *		an integer value which represents the current object's sequence number, as with the
 *		global identifier, this value must be unique, except that it may change across
 *		implementations. All offset keys in all objects derived from this class ancestor are
 *		references to this sequence number. This attribute must be managed with its offset;
 *		in derived classes it will be automatically assigned end only available as
 *		read-only.
 *	<li><tt>{@link kTAG_TERMS}</tt>: <em>Branch</em>. This required attribute holds
 *		the list of terms comprising the current tag: this is an array of term references
 *		provided as an odd sequence of vertices and predicates forming a path of the
 *		ontology graph in which the first vertex defines the feature, the middle ones
 *		define the methodology and the last element indicates the scale or unit. To populate
 *		the elements of this path, use the {@link TermPush()} amd {@link TermPop()}
 *		offset accessor methods which respectively add and remove elements of the branch as
 *		if it was a stack.
 *	<li><tt>{@link kTAG_DATA_TYPE}</tt>: <em>Data type</em>. This attribute is an enumerated
 *		set of values listing all the <em>data types</em> that the value of the property
 *		defined by the current tag may take. To populate and handle individual data types
 *		use the {@link DataType()} offset accessor method. This property is
 *		<em>required</em> by all tag objects.
 *	<li><tt>{@link kTAG_DATA_KIND}</tt>: <em>Data kind</em>. This attribute is an enumerated
 *		set of values providing the <em>data attributes</em> of the property defined by the
 *		current tag. This may be whether the property is a list of values, or if the
 *		property is required or not. To populate and handle individual data kinds use the
 *		{@link DataKind()} offset accessor method.
 *	<li><tt>{@link kTAG_LABEL}</tt>: <em>Label</em>. The label represents the <i>name or
 *		short description</i> of the data property that the current object defines. All tags
 *		<em>should</em> have a label, since this is how human users will be able to identify
 *		and select them. This attribute has the {@link kTYPE_LANGUAGE_STRINGS} data type,
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
 *		{@link kTYPE_LANGUAGE_STRINGS} data type, which is constituted by a list of elements
 *		in which the {@link kTAG_LANGUAGE} item holds the description language code and the
 *		{@link kTAG_TEXT} holds the description text. To populate and handle
 *		descriptions by language, use the {@link Description()} offset accessor method.
 *	<li><tt>{@link kTAG_SYNONYM}</tt>: <em>Synonyms</em>. This attribute is a <em>set of
 *		strings</em> representing <em>alternate identifiers of this tag</em>, not formally
 *		defined in the current data set.
 *	<li><tt>{@link kTAG_MIN}</tt>: <em>Range minimum</em>. This attribute is a floating
 *		point value representing the <em>minimum</em> of the <em>range of values</em>
 *		identified by this tag.
 *	<li><tt>{@link kTAG_MAX}</tt>: <em>Range maximum</em>. This attribute is a floating
 *		point value representing the <em>maximum</em> of the <em>range of values</em>
 *		identified by this tag.
 *	<li><tt>{@link kTAG_PATTERN}</tt>: <em>Regular expression pattern</em>. This attribute
 *		holds a <em>string</em> which represents a <em>regular expression pattern</em> which
 *		can be used to <em>validate data identified by this tag</em>.
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
 *	<li><tt>{@link kTAG_ENTITY_OFFSETS}</tt>: <em>Entity offsets</em>. This attribute is
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
 * In this class we set the sequence number, {@link kTAG_ID_SEQUENCE}, by retrieving a 
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */
class Tag extends PersistentObject
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
	 * Data type trait.
	 *
	 * We use this trait to handle data types.
	 */
	use	traits\DataType;

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
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	commit																			*
	 *==================================================================================*/

	/**
	 * Insert the object
	 *
	 * We overload this method to add the object to the data dictionary cache.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 * @return mixed				The object's native identifier.
	 *
	 * @uses dictionary()
	 */
	public final function commit( $theWrapper = NULL )
	{
		//
		// Call parent method
		//
		$id = parent::commit( $theWrapper );
		
		//
		// Set cache.
		//
		$this->dictionary()->setTag( $this, 0 );
		
		return $id;																	// ==>
	
	} // commit.

		

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
	 *	<li><tt>{@link kTAG_ID_SEQUENCE}</tt>: Sequence number.
	 *	<li><tt>{@link kTAG_TERMS}</tt>: Terms path.
	 *	<li><tt>{@link kTAG_LABEL}</tt>: Labels.
	 *	<li><tt>{@link kTAG_TAG_COUNT}</tt>: Tags count.
	 *	<li><tt>{@link kTAG_TERM_COUNT}</tt>: Terms count.
	 *	<li><tt>{@link kTAG_NODE_COUNT}</tt>: Nodes count.
	 *	<li><tt>{@link kTAG_EDGE_COUNT}</tt>: Edges count.
	 *	<li><tt>{@link kTAG_UNIT_COUNT}</tt>: Units count.
	 *	<li><tt>{@link kTAG_ENTITY_COUNT}</tt>: Entities count.
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
		$collection->createIndex( array( kTAG_ID_SEQUENCE => 1 ),
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
		// Set tags count index.
		//
	/*
		$collection->createIndex( array( kTAG_TAG_COUNT => 1 ),
								  array( "name" => "TAGS",
								  		 "sparse" => TRUE ) );
	*/
		
		//
		// Set terms count index.
		//
	/*
		$collection->createIndex( array( kTAG_TERM_COUNT => 1 ),
								  array( "name" => "TERMS",
								  		 "sparse" => TRUE ) );
	*/
		
		//
		// Set nodes count index.
		//
	/*
		$collection->createIndex( array( kTAG_NODE_COUNT => 1 ),
								  array( "name" => "NODES",
								  		 "sparse" => TRUE ) );
	*/
		
		//
		// Set edges count index.
		//
	/*
		$collection->createIndex( array( kTAG_EDGE_COUNT => 1 ),
								  array( "name" => "EDGES",
								  		 "sparse" => TRUE ) );
	*/
		
		//
		// Set units count index.
		//
		$collection->createIndex( array( kTAG_UNIT_COUNT => 1 ),
								  array( "name" => "UNITS",
								  		 "sparse" => TRUE ) );
		
		//
		// Set entities count index.
		//
		$collection->createIndex( array( kTAG_ENTITY_COUNT => 1 ),
								  array( "name" => "ENTITIES",
								  		 "sparse" => TRUE ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.

		

/*=======================================================================================
 *																						*
 *							STATIC OBJECT REFERENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GetReferenceKey																	*
	 *==================================================================================*/

	/**
	 * Return reference key
	 *
	 * In this class we use {@link kTAG_ID_SEQUENCE}.
	 *
	 * @static
	 * @return string				Key offset.
	 */
	static function GetReferenceKey()						{	return kTAG_ID_SEQUENCE;	}

		

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
	 * In this class we cast the value of the sequence number into an integer.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @see kTAG_NAMESPACE
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
			// Intercept custom offsets.
			//
			switch( $theOffset )
			{
				case kTAG_ID_SEQUENCE:
					$theValue = (int) $theValue;
					break;
			}
			
		} // Passed preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 
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
	 * We overload this method to remove the tag from the data dictionary.
	 *
	 * @access protected
	 * @return mixed				Native identifier, <tt>NULL</tt> or <tt>FALSE</tt>.
	 *
	 * @uses dictionary()
	 */
	protected final function deleteObject()
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
		$this->dictionary()->delTag( $this, 0 );
		
		return $id;																	// ==>
	
	} // deleteObject.

	 
	/*===================================================================================
	 *	modifyObject																	*
	 *==================================================================================*/

	/**
	 * Modify object
	 *
	 * We overload this method to update the object in the data dictionary cache.
	 *
	 * @param mixed					$theOffsets			Offsets to be modified.
	 * @param boolean				$doSet				<tt>TRUE</tt> means add or replace.
	 *
	 * @access protected
	 * return integer				Number of objects affected.
	 *
	 * @throws Exception
	 */
	protected function modifyObject( $theOffsets, $doSet )
	{
		//
		// Call parent method.
		//
		$ok = parent::modifyObject( $theOffsets, $doSet );
		
		//
		// Set cache.
		//
		$this->dictionary()->setTag( $this, 0 );
		
		return $ok;																	// ==>
	
	} // modifyObject.

		

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
					$term = new Term( $this->dictionary(), $term );
					
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
			// Assert current tag is a list of structures.
			//
			if( (! $this->offsetExists( kTAG_DATA_TYPE ))
			 || (! array_key_exists( kTYPE_STRUCT, $this->offsetGet( kTAG_DATA_TYPE ) ))
			 || (! offsetExists( kTAG_DATA_KIND ))
			 || (! array_key_exists( kTYPE_LIST, $this->offsetGet( kTAG_DATA_KIND ) )) )
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
	 * number, {@link kTAG_ID_SEQUENCE}, if it is not yet set, by requesting it from the
	 * database of the current object's container.
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
				static::ResolveDatabase( $this->dictionary() ) );
		
		//
		// Set native identifier.
		//
		if( ! \ArrayObject::offsetExists( kTAG_NID ) )
			\ArrayObject::offsetSet( kTAG_NID, $this->__toString() );
		
		//
		// Set sequence number.
		//
		if( ! \ArrayObject::offsetExists( kTAG_ID_SEQUENCE ) )
			$this->offsetSet(
				kTAG_ID_SEQUENCE,
				$collection->getSequenceNumber(
					static::kSEQ_NAME ) );
	
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
	 * In this class we ensure the object has the sequence number, {@link kTAG_ID_SEQUENCE}
	 * and the native identifier, {@link kTAG_NID}.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 *
	 * @see kTAG_NID kTAG_ID_SEQUENCE
	 *
	 * @uses isInited()
	 */
	protected function isReady()
	{
		return ( parent::isReady()
			  && $this->offsetExists( kTAG_ID_SEQUENCE )
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
	 * In this class we return the {@link kTAG_ID_SEQUENCE}, {@link kTAG_TERMS},
	 * {@link kTAG_DATA_TYPE} and the {@link kTAG_DATA_KIND} offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_ID_SEQUENCE kTAG_TERMS kTAG_DATA_TYPE kTAG_DATA_KIND
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
							array( kTAG_ID_SEQUENCE, kTAG_TERMS,
								   kTAG_DATA_TYPE, kTAG_DATA_KIND ) );				// ==>
	
	} // lockedOffsets.

	 

} // class Tag.


?>
