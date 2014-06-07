<?php

/**
 * Label.php
 *
 * This file contains the definition of the {@link Label} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *										Label.php										*
 *																						*
 *======================================================================================*/

/**
 * Label trait
 *
 * This trait implements a method for managing the label offset, {@link kTAG_LABEL}. The
 * method manages the individual labels by language.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/02/2014
 */
trait Label
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
	 * @see kTAG_LABEL kTAG_LANGUAGE kTAG_TEXT
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function Label( $theLanguage, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_LABEL, kTAG_LANGUAGE, kTAG_TEXT,
				$theLanguage, $theValue, $getOld );									// ==>
	
	} // Label.

	 

} // trait Label.


?>
