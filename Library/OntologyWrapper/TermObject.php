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
 * For instance, a <tt>name</tt> is defined as a string or text that identifies something,
 * this is true for both a person name or an object name, however, the term <tt>name</tt>
 * will bare a different meaning depending on what context it is used in: the term object
 * holds the definition of that will not change with its context.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. This required attribute holds
 *		the term global identifier. By convention this value is the combination of the
 *		namespace, {@link kTAG_NS}, and the local identifier, {@link kTAG_LID}, separated by
 *		the {@link kTOKEN_NAMESPACE_SEPARATOR} token. In practice, the global identifier may
 *		be manually set. This attribute must be managed with its offset.
 *	<li><tt>{@link kTAG_NS}</tt>: <em>Namespace</em>. This optional attribute is a reference
 *		to another term object that represents the namespace of the current term. It is by
 *		definition the global identifier of the namespace term. This attribute must be
 *		managed with its offset.
 *	<li><tt>{@link kTAG_LID}</tt>: <em>Local identifier</em>. This required attribute is a
 *		string that represents the current term unique identifier within its namespace. The
 *		combination of the current term's namespace and this attribute form the term's
 *		global identifier. This attribute must be managed with its offset.
 *	<li><tt>{@link kTAG_LABEL}</tt>: <em>Label</em>. The label represents the <i>name or
 *		short description</i> of the term that the current object defines. All terms
 *		<em>should</em> have a label, since this is how human users will be able to identify
 *		and select them. This attribute has the {@link kTYPE_KIND_VALUE} data type, which
 *		is constituted by a list of elements in which the {@link kTAG_SUB_LANGUAGE} item holds
 *		the label language code and the {@link kTAG_SUB_TEXT} holds the label text. To
 *		populate and handle labels by language, use the {@link Label()} offset accessor
 *		method. Some terms may not have a language element, for instance the number
 *		<tt>2</tt> may not need to be expressed in other ways.
 *	<li><tt>{@link kTAG_DEFINITION}</tt>: <em>Definition</em>. The definition represents the
 *		<i>description or extended definition</i> of the term that the current object object
 *		defines. The definition is similar to the <em>description</em>, except that while
 *		the description provides context specific information, the definition should not.
 *		All terms <em>should</em> have a definition, if the object label is not enough to
 *		provide a sufficient definition. Definitions have the {@link kTYPE_KIND_VALUE} data
 *		type in which the {@link kTAG_SUB_LANGUAGE} element holds the definition language code
 *		and the {@link kTAG_SUB_TEXT} holds the definition text. To populate and handle
 *		definitions by language, use the {@link Definition()} offset accessor method.
 * </ul>
 *
 * The {@link __toString()} method will return the value stored in the native identifier,
 * if set, or the computed global identifier if at least the local identifier is set; if the
 * latter is not set, the method will fail.
 *
 * Objects of this class can hold any additional attribute that is considered necessary or
 * useful to define and share the current term. In this class we define only those
 * attributes that constitute the core functionality of the object, derived classes will add
 * attributes specific to the domain in which the object will operate.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */
class TermObject extends OntologyObject
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
	 * If the native identifier, {@link kTAG_NID}, is set, this method will return its
	 * value. If that offset is not yet set, the method will compute the global identifier
	 * by concatenating the object's namespace, {@link kTAG_NS}, with the object's local
	 * identifier, {@link kTAG_LID}, separated by the {@link kTOKEN_NAMESPACE_SEPARATOR}
	 * token. This will only occur if the object has the local identifier, if that is not
	 * the case, the method will return an empty string to prevent the method from causing
	 * an error.
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
		if( \ArrayObject::offsetExists( kTAG_LID ) )
			return ( \ArrayObject::offsetExists( kTAG_NS ) )
				 ? (\ArrayObject::offsetGet( kTAG_NS )
				   .kTOKEN_NAMESPACE_SEPARATOR
				   .\ArrayObject::offsetGet( kTAG_LID ))							// ==>
				 : \ArrayObject::offsetGet( kTAG_LID );								// ==>
		
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
	 * @see kTAG_DEFINITION kTAG_SUB_LANGUAGE kTAG_SUB_TEXT
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function Definition( $theLanguage, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_DEFINITION, kTAG_SUB_LANGUAGE, kTAG_SUB_TEXT,
				$theLanguage, $theValue, $getOld );									// ==>
	
	} // Definition.

		

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
	 * In this class we return <tt>TRUE</tt> , assuming the object is ready.
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
	 * In this class we cast the value of the namespace into a term reference, ensuring
	 * that if an object is provided this is a term.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @throws Exception
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
			// Intercept namespace.
			//
			if( $theOffset == kTAG_NS )
			{
				//
				// Handle objects.
				//
				if( is_object( $theValue ) )
				{
					//
					// If term, get its reference.
					//
					if( $theValue instanceof self )
						$theValue = $theValue->Reference();
				
					//
					// If not a term, complain.
					//
					else
						throw new \Exception(
							"Unable to set namespace: "
						   ."provided an object other than term." );			// !@! ==>
			
				} // Object.
			
				//
				// Cast to setring.
				//
				else
					$theValue = (string) $theValue;
			
			} // Setting namespace.
			
		} // Passed preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 

} // class TermObject.


?>
