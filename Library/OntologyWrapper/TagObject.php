<?php

/**
 * TagObject.php
 *
 * This file contains the definition of the {@link TagObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\OntologyObject;

/*=======================================================================================
 *																						*
 *									TagObject.php										*
 *																						*
 *======================================================================================*/

/**
 * Tag object
 *
 * This class extends {@link OntologyObject} to implement a concrete tag object class.
 *
 * A tag object is used to <i>identify</i>, <i>document</i> and <i>share</i> a data
 * property, it represents the <i>metadata</i> of a data property, or its <i>definition</i>
 * in an ontology.
 *
 * This class features a {@link kTAG_TERMS} offset which is an array of {@link TermObject}
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
 * Tags have another offset, {@link kTAG_SEQ}, which is an integer sequence number: this
 * value must be unique within the tags domain of the current ontology. Unlike global
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
 *	<li><tt>{@link kTAG_SEQ}</tt>: <em>Sequence</em>. This required attribute holds
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
 *		the elements of this path, use the {@link BranchPush()} amd {@link BranchPop()}
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
 *		and select them. This attribute has the {@link kTYPE_KIND_VALUE} data type, which
 *		is constituted by a list of elements in which the {@link kTAG_SUB_LANGUAGE} item holds
 *		the label language code and the {@link kTAG_SUB_TEXT} holds the label text. To
 *		populate and handle labels by language, use the {@link Label()} offset accessor
 *		method.
 *	<li><tt>{@link kTAG_DESCRIPTION}</tt>: <em>Description</em>. The description tag
 *		represents the <i>description or extended definition</i> of the property that the
 *		current tag defines. The description is similar to the <em>definition</em>, except
 *		that while the definition provides a description of the object it defines unrelated
 *		to context, the description adds to the definition the elements added by the current
 *		context. All tags <em>should</em> have a description, if the tag label is not enough
 *		to provide a sufficient description or definition. Descriptions have the
 *		{@link kTYPE_KIND_VALUE} data type, which is constituted by a list of elements in
 *		which the {@link kTAG_SUB_LANGUAGE} item holds the description language code and the
 *		{@link kTAG_SUB_TEXT} holds the description text. To populate and handle
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
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 05/02/2014
 */
class TagObject extends OntologyObject
{
		

/*=======================================================================================
 *																						*
 *											MAGIC										*
 *																						*
 *======================================================================================*/


	 
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
 *							PUBLIC OFFSET ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Label																			*
	 *==================================================================================*/

	/**
	 * Manage tag label
	 *
	 * This method can be used to add, retrieve and delete individual labels by language,
	 * while managing all the labels as a whole can be done using the {@link kTAG_LABEL}
	 * offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theLanguage</b>: This parameter holds the language code of the text we
	 *		want to manage. If <tt>NULL</tt>, it means that there is a text without a
	 *		language; this can occur if the label is a name unrelated to any language.
	 *	<li><b>$theValue</b>: This parameter identifies the label text or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the label of the provided language.
	 *		<li><tt>FALSE</tt>: Delete the label of the provided language.
	 *		<li><i>other</i>: Any other value is cast to string and interpreted as the text
	 *			of the label in the provided language which will be inserted, or that will
	 *			replace an existing entry.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will return either the label text, or <tt>NULL</tt> if the language was
	 * not matched.
	 *
	 * @param string				$theLanguage		Label language.
	 * @param mixed					$theValue			Label text or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_LABEL kTAG_SUB_LANGUAGE kTAG_SUB_TEXT
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function Label( $theLanguage, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_LABEL, kTAG_SUB_LANGUAGE, kTAG_SUB_TEXT,
				$theLanguage, $theValue, $getOld );									// ==>
	
	} // Label.

	 
	/*===================================================================================
	 *	Description																		*
	 *==================================================================================*/

	/**
	 * Manage tag description
	 *
	 * This method can be used to add, retrieve and delete individual descriptions by
	 * language, while managing all the descriptions as a whole can be done using the
	 * {@link kTAG_DESCRIPTION} offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theLanguage</b>: This parameter holds the language code of the text we
	 *		want to manage. The <tt>NULL</tt> value should generally <em>not</em> be used,
	 *		definitions may not contain language elements, while descriptons should.
	 *	<li><b>$theValue</b>: This parameter identifies the description text or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the description of the provided language.
	 *		<li><tt>FALSE</tt>: Delete the description of the provided language.
	 *		<li><i>other</i>: Any other value is cast to string and interpreted as the text
	 *			of the description in the provided language which will be inserted, or that
	 *			will replace an existing entry.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will return either the description text, or <tt>NULL</tt> if the language
	 * was not matched.
	 *
	 * @param string				$theLanguage		Description language.
	 * @param mixed					$theValue			Description text or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_DESCRIPTION kTAG_SUB_LANGUAGE kTAG_SUB_TEXT
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function Description( $theLanguage, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_DESCRIPTION, kTAG_SUB_LANGUAGE, kTAG_SUB_TEXT,
				$theLanguage, $theValue, $getOld );									// ==>
	
	} // Description.

	 
	/*===================================================================================
	 *	DataType																		*
	 *==================================================================================*/

	/**
	 * Manage data type
	 *
	 * This method can be used to add, retrieve and delete individual data type enumerated
	 * values; to manage the enumerated set as a whole, use the {@link kTAG_DATA_TYPE}
	 * offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: This parameter represents the enumerated value we want to
	 *		manage.
	 *	<li><b>$theOperation</b>: This parameter identifies the operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the enumerated value, if it exists, or <tt>NULL</tt>.
	 *		<li><tt>FALSE</tt>: Delete the enumerated value, if it exists.
	 *		<li><i>other</i>: Any other value means that we want to set the enumerated value
	 *			provided in the previous parameter.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will return either the enumerated value, or <tt>NULL</tt> if the value was
	 * not matched.
	 *
	 * @param string				$theValue			Data type.
	 * @param mixed					$theOperation		Operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_DATA_TYPE
	 *
	 * @uses manageSetOffset()
	 */
	public function DataType( $theValue, $theOperation = NULL, $getOld = FALSE )
	{
		return $this->manageSetOffset(
				kTAG_DATA_TYPE,
				(string) $theValue, $theOperation, $getOld );						// ==>
	
	} // DataType.

	 
	/*===================================================================================
	 *	DataKind																		*
	 *==================================================================================*/

	/**
	 * Manage data kind
	 *
	 * This method can be used to add, retrieve and delete individual data kind enumerated
	 * values; to manage the enumerated set as a whole, use the {@link kTAG_DATA_KIND}
	 * offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: This parameter represents the enumerated value we want to
	 *		manage.
	 *	<li><b>$theOperation</b>: This parameter identifies the operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the enumerated value, if it exists, or <tt>NULL</tt>.
	 *		<li><tt>FALSE</tt>: Delete the enumerated value, if it exists.
	 *		<li><i>other</i>: Any other value means that we want to set the enumerated value
	 *			provided in the previous parameter.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will return either the enumerated value, or <tt>NULL</tt> if the value was
	 * not matched.
	 *
	 * @param string				$theValue			Data type.
	 * @param mixed					$theOperation		Operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_DATA_KIND
	 *
	 * @uses manageSetOffset()
	 */
	public function DataKind( $theValue, $theOperation = NULL, $getOld = FALSE )
	{
		return $this->manageSetOffset(
				kTAG_DATA_KIND,
				(string) $theValue, $theOperation, $getOld );						// ==>
	
	} // DataKind.

	

/*=======================================================================================
 *																						*
 *							PUBLIC BRANCH ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	BranchPush																		*
	 *==================================================================================*/

	/**
	 * Add to terms path
	 *
	 * This method can be used to append elements to the object's terms path, it will add
	 * the provided element to the end of the path.
	 *
	 * If you provide a {@link TermObject} as the parameter, the method will
	 * {@link Reference()} it.
	 *
	 * The method will return the number of elements in the path.
	 *
	 * @param mixed					$theTerm			Term reference or object.
	 *
	 * @access public
	 * @return integer				Number of elements in path.
	 *
	 * @see kTAG_TERMS
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function BranchPush( $theTerm )
	{
		//
		// Handle objects.
		//
		if( is_object( $theTerm ) )
		{
			//
			// If term, get its reference.
			//
			if( $theTerm instanceof TermObject )
				$theTerm = $theTerm->Reference();
		
			//
			// If not a term, complain.
			//
			else
				throw new \Exception(
					"Unable to add element to terms path: "
				   ."provided an object other than term." );					// !@! ==>
	
		} // Object.
	
		//
		// Cast to setring.
		//
		else
			$theTerm = (string) $theTerm;
		
		//
		// Get current path.
		//
		$path = ( \ArrayObject::offsetExists( kTAG_TERMS ) )
			  ? \ArrayObject::offsetGet( kTAG_TERMS )
			  : Array();
		
		//
		// Add element.
		//
		$path[] = $theTerm;
		
		//
		// Get count.
		//
		$count = count( $path );
		
		//
		// Set offset.
		//
		$this->offsetSet( kTAG_TERMS, $path );
		
		return $count;																// ==>
	
	} // BranchPush.

	 
	/*===================================================================================
	 *	BranchPop																		*
	 *==================================================================================*/

	/**
	 * Add to terms path
	 *
	 * This method can be used to pop elements off the end of the object's terms path, it
	 * will remove the last element in the sequence.
	 *
	 * When you remove the last element of the path, the method will also remove the offset.
	 *
	 * The method will return the removed element; if the path is empty, the method will
	 * return <tt>NULL</tt>.
	 *
	 * @access public
	 * @return string				Removed element or <tt>NULL</tt>.
	 *
	 * @see kTAG_TERMS
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function BranchPop()
	{
		//
		// Get current path.
		//
		if( \ArrayObject::offsetExists( kTAG_TERMS ) )
		{
			//
			// Get current path.
			//
			$path = \ArrayObject::offsetGet( kTAG_TERMS );
			
			//
			// Pop element.
			//
			$element = array_pop( $path );
			
			//
			// Update parh.
			//
			if( count( $path ) )
				$this->offsetSet( kTAG_TERMS, $path );
			
			//
			// Delete offset.
			//
			else
				$this->offsetUnset( kTAG_TERMS );
			
			return $element;														// ==>
		
		} // Has branch.
		
		return NULL;																// ==>
	
	} // BranchPop.

	 
	/*===================================================================================
	 *	BranchCount																		*
	 *==================================================================================*/

	/**
	 * Count terms path elements
	 *
	 * This method will return the number of elements in the object's terms path.
	 *
	 * @access public
	 * @return integer				Number of elements in terms path.
	 *
	 * @see kTAG_TERMS
	 */
	public function BranchCount()
	{
		//
		// Check branch.
		//
		if( \ArrayObject::offsetExists( kTAG_TERMS ) )
			return count( \ArrayObject::offsetGet( kTAG_TERMS ) );					// ==>
		
		return 0;																	// ==>
	
	} // BranchCount.

		

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
	 * In this class we return <tt>TRUE</tt>, to allow method chaining across the
	 * inheritance.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 */
	protected function isReady()										{	return TRUE;	}

		

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
	 * @see kTAG_NS
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
			if( $theOffset == kTAG_SEQ )
				$theValue = (int) $theValue;
			
		} // Passed preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 

} // class TagObject.


?>
