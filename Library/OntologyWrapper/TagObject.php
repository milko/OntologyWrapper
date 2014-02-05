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
 * A tag object is used to identify, describe and document a data property, it holds the
 * data property offset, data and cardinality types and the label and description of the
 * property.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_LABEL}</tt>: <em>Label</em>. The label tag represents the <i>name or
 *		short description</i> of the property that the current tag defines. All tags
 *		<em>should</em> have a label, since this is how human users will be able to identify
 *		a property. Labels have the {@link kTYPE_KIND_VALUE} data type in which the
 *		{@link kTAG_PART_KIND} element holds the label language code and the
 *		{@link kTAG_PART_VALUE} holds the label text. To populate and handle labels by
 *		language, use the {@link Label()} offset accessor method.
 *	<li><tt>{@link kTAG_DESCRIPTION}</tt>: <em>Description</em>. The description tag
 *		represents the <i>description or extended definition</i> of the property that the
 *		current tag defines. The description is similar to the <em>definition</em>, except
 *		that while the definition provides a description of the object it defines unrelated
 *		to context, the description adds to the definition the elements added by the current
 *		context. All tags <em>should</em> have a label, if the tag label is not enough to
 *		provide a sufficient description or definition. Descriptions have the
 *		{@link kTYPE_KIND_VALUE} data type in which the {@link kTAG_PART_KIND} element holds
 *		the description language code and the {@link kTAG_PART_VALUE} holds the description
 *		text. To populate and handle descriptions by language, use the {@link Description()}
 *		offset accessor method.
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
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 05/02/2014
 */
class TagObject extends OntologyObject
{
			

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
	 * @see kTAG_DESCRIPTION kTAG_PART_KIND kTAG_PART_VALUE
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function Description( $theLanguage, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_DESCRIPTION, kTAG_PART_KIND, kTAG_PART_VALUE,
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

	 

} // class TagObject.


?>
