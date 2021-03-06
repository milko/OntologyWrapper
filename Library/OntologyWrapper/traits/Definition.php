<?php

/**
 * Definition.php
 *
 * This file contains the definition of the {@link Definition} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *									Definition.php										*
 *																						*
 *======================================================================================*/

/**
 * Definition trait
 *
 * This trait implements a method for managing the definition offset,
 * {@link kTAG_DEFINITION}. The method manages the individual definitions by language.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/02/2014
 */
trait Definition
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
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
	 * @see kTAG_DEFINITION kTAG_LANGUAGE kTAG_TEXT
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function Definition( $theLanguage, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_DEFINITION, kTAG_LANGUAGE, kTAG_TEXT,
				$theLanguage, $theValue, $getOld );									// ==>
	
	} // Definition.

	 

} // trait Definition.


?>
