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
			// Intercept sequence number.
			//
			if( $theOffset == kTAG_ID_SEQUENCE )
				$theValue = (int) $theValue;
			
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
 *								PROTECTED PRE-COMMIT INTERFACE							*
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
				static::ResolveDatabase( $this->dictionary(), TRUE ) );
		
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
 *							PROTECTED POST-COMMIT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postCommit																		*
	 *==================================================================================*/

	/**
	 * Cleanup object after commit
	 *
	 * In this class we set the newly inserted tag into the cache.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 */
	protected function postCommit( &$theTags, &$theRefs )
	{
		//
		// Call parent method.
		//
		parent::postCommit( $theTags, $theRefs );
		
		//
		// Set cache.
		//
		$this->dictionary()->setTag( $this, 0 );
	
	} // postCommit.

	 
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
