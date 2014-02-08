<?php

/**
 * TermObject.php
 *
 * This file contains the definition of the {@link TermObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\OntologyObject;

/*=======================================================================================
 *																						*
 *									TermObject.php										*
 *																						*
 *======================================================================================*/

/**
 * Term object
 *
 * This class extends {@link OntologyObject} to implement a concrete term object class.
 *
 * A term object holds the necessary information to <i>uniquely identify</i>,
 * <i>document</i> and <i>share</i> a <i>generic term or concept</i> which is <i>not related
 * to a specific context</i>.
 *
 * For instance, a <tt>name</tt> is defined as a string or text that identifies an object,
 * this is true for both a person name or an object name, however, the term <tt>name</tt>
 * will bare a different meaning depending on what context it is used in: the term object
 * holds the definition of the term that will not change with its context.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NS}</tt>: <em>Namespace</em>. This optional attribute is a reference
 *		to another term object that represents the namespace of the current term. It is by
 *		definition the global identifier of the namespace term. This attribute must be
 *		managed with its offset.
 *	<li><tt>{@link kTAG_LID}</tt>: <em>Local identifier</em>. This required attribute is a
 *		string that represents the current term unique identifier within its namespace. The
 *		combination of the current term's namespace and this attribute form the term's
 *		global identifier. This attribute must be managed with its offset.
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. This required attribute is
 *		the combination of the term namespace and local identifier, this value should be
 *		unique within the whole terms domain. The attribute is managed with its offset.
 *	<li><tt>{@link kTAG_LABEL}</tt>: <em>Label</em>. The label represents the <i>name or
 *		short description</i> of the term that the current object defines. All terms
 *		<em>should</em> have a label, since this is how human users will be able to identify
 *		and select them. Labels have the {@link kTYPE_KIND_VALUE} data type in which the
 *		{@link kTAG_PART_KIND} element holds the label language code and the
 *		{@link kTAG_PART_VALUE} holds the label text. To populate and handle labels by
 *		language, use the {@link Label()} offset accessor method. Some terms may not have
 *		a language element, for instance the number <tt>2</tt> may not need to be expressed
 *		in other ways.
 *	<li><tt>{@link kTAG_DEFINITION}</tt>: <em>Definition</em>. The definition represents the
 *		<i>description or extended definition</i> of the term that the current object object
 *		defines. The definition is similar to the <em>description</em>, except that while
 *		the description provides context specific information, the definition should not.
 *		All terms <em>should</em> have a definition, if the object label is not enough to
 *		provide a sufficient definition. Definitions have the {@link kTYPE_KIND_VALUE} data
 *		type in which the {@link kTAG_PART_KIND} element holds the definition language code
 *		and the {@link kTAG_PART_VALUE} holds the definition text. To populate and handle
 *		definitions by language, use the {@link Definition()} offset accessor method.
 * </ul>
 *
 * The object features a method that can be used to retrieve the object's global identifier
 * which, in this class, corresponds to the native identifier: if the offset is set, the
 * method will return its value; if the offset is not set, the method will return the
 * concatenation of the namespace and the local identifier separated by the
 * {@link kTOKEN_NAMESPACE_SEPARATOR} token.
 *
 * Objects of this class can hold any additional attribute that is considered necessary or
 * useful to define and share the current term.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */
class TermObject extends OntologyObject
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GID																				*
	 *==================================================================================*/

	/**
	 * Global identifier
	 *
	 * This method can be used to set, retrieve and compute the object's global identifier,
	 * which corresponds to the object's native identifier offset, {@link kTAG_NID}.
	 *
	 * If the object is {@link isCommitted()}, the method will only allow you to retrieve
	 * the value, an exception will be raised if you try to set it (this is inherited
	 * behaviour).
	 *
	 * If the offset is not yet set, the method will compute the global identifier by
	 * concatenating the object's namespace, {@link kTAG_NS}, with the object's local
	 * identifier, {@link kTAG_LID}, separated by the {@link kTOKEN_NAMESPACE_SEPARATOR}
	 * token. This will only occur if the object has the local identifier, if that is not
	 * the case, the method will return <tt>NULL</tt>.
	 *
	 * The method expects a single parameter which represents either the new global
	 * identifier, or, if <tt>NULL</tt>, the request to retrieve it.
	 *
	 * If the object has the native identifier offset, it will return it; if it has the
	 * local identifier it will compute the global identifier; if it doesn't have the local
	 * identifier it will return <tt>NULL</tt>.
	 *
	 * @param mixed					$theValue			Global identifier or operation.
	 *
	 * @access public
	 * @return string				Global identifier or <tt>NULL</tt>.
	 *
	 * @see kTAG_LABEL kTAG_PART_KIND kTAG_PART_VALUE
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function GID( $theValue = NULL )
	{
		//
		// Return global identifier.
		//
		if( $theValue === NULL )
			return ( \ArrayObject::offsetExists( kTAG_NID ) )
				 ? \ArrayObject::offsetGet( kTAG_NID )								// ==>
				 : ( ( $this->offsetExists( kTAG_LID ) )
				   ? ( ( $this->offsetExists( kTAG_NS ) )
					 ? (\ArrayObject::offsetGet( kTAG_NS )
					   .kTOKEN_NAMESPACE_SEPARATOR
					   .\ArrayObject::offsetGet( kTAG_LID ))						// ==>
					 : \ArrayObject::offsetGet( kTAG_LID ) )						// ==>
				   : NULL );														// ==>
		
		//
		// Set global identifier.
		//
		$this->offsetSet( kTAG_NID, $theValue );
		
		return $theValue;															// ==>
	
	} // GID.

	 
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
	 * @see kTAG_LABEL kTAG_PART_KIND kTAG_PART_VALUE
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function Label( $theLanguage, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_LABEL, kTAG_PART_KIND, kTAG_PART_VALUE,
				$theLanguage, $theValue, $getOld );									// ==>
	
	} // Label.

	 
	/*===================================================================================
	 *	Definition																		*
	 *==================================================================================*/

	/**
	 * Manage term description
	 *
	 * This method can be used to add, retrieve and delete individual definitions by
	 * language, while managing all the definitions as a whole can be done using the
	 * {@link kTAG_DEFINITION} offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theLanguage</b>: This parameter holds the language code of the text we
	 *		want to manage. The <tt>NULL</tt> value should only be used if the label is not
	 *		related to a specific language.
	 *	<li><b>$theValue</b>: This parameter identifies the definition text or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the definition of the provided language.
	 *		<li><tt>FALSE</tt>: Delete the definition of the provided language.
	 *		<li><i>other</i>: Any other value is cast to string and interpreted as the text
	 *			of the definition in the provided language which will be inserted, or that
	 *			will replace an existing entry.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will return either the definition text, or <tt>NULL</tt> if the language
	 * was not matched.
	 *
	 * @param string				$theLanguage		Definition language.
	 * @param mixed					$theValue			Definition text or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_DEFINITION kTAG_PART_KIND kTAG_PART_VALUE
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function Definition( $theLanguage, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_DEFINITION, kTAG_PART_KIND, kTAG_PART_VALUE,
				$theLanguage, $theValue, $getOld );									// ==>
	
	} // Definition.

	 

} // class TermObject.


?>
