<?php

/**
 * EntityPhone.php
 *
 * This file contains the definition of the {@link EntityPhone} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *									EntityPhone.php										*
 *																						*
 *======================================================================================*/

/**
 * Entity telephone trait
 *
 * This trait implements a method for managing the entity telephone number offset,
 * {@link kTAG_ENTITY_PHONE}. The method manages the individual telephone numbers by type.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 03/03/2014
 */
trait EntityPhone
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	EntityPhone																		*
	 *==================================================================================*/

	/**
	 * Manage telephone number
	 *
	 * This method can be used to add, retrieve and delete individual telephone numbers by
	 * type, while managing all the telephones as a whole can be done using the
	 * {@link kTAG_ENTITY_PHONE} offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theType</b>: This parameter holds the <em>type</em> of the telephone number
	 *		we want to manage. If <tt>NULL</tt>, it means that there is a phone without a
	 *		type; this can occur if the telephone is the default one.
	 *	<li><b>$theValue</b>: This parameter identifies the telephone number or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the phone of the provided type.
	 *		<li><tt>FALSE</tt>: Delete the phone of the provided type.
	 *		<li><i>other</i>: Any other value is cast to string and interpreted as the
	 *			telephone number of provided type which will be inserted, or that will
	 *			replace an existing entry.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will return either the phone number, or <tt>NULL</tt> if the type was not
	 * matched.
	 *
	 * @param string				$theType			Telephone number type.
	 * @param mixed					$theValue			Telephone number or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_ENTITY_PHONE kTAG_GEN_TYPE kTAG_GEN_TEXT
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function EntityPhone( $theType, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_ENTITY_PHONE, kTAG_GEN_TYPE, kTAG_GEN_TEXT,
				$theType, $theValue, $getOld );										// ==>
	
	} // EntityPhone.

	 

} // trait EntityPhone.


?>
