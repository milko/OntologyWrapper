<?php

/**
 * EntityMail.php
 *
 * This file contains the definition of the {@link EntityMail} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *										EntityMail.php									*
 *																						*
 *======================================================================================*/

/**
 * Entity mail trait
 *
 * This trait implements a method for managing the entity mailing address offset,
 * {@link kTAG_ENTITY_MAIL}. The method manages the individual mailing addresses by type.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 03/03/2014
 */
trait EntityMail
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	EntityMail																		*
	 *==================================================================================*/

	/**
	 * Manage mailing address
	 *
	 * This method can be used to add, retrieve and delete individual mailing addresses by
	 * type, while managing all the mails as a whole can be done using the
	 * {@link kTAG_ENTITY_MAIL} offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theType</b>: This parameter holds the <em>type</em> of the mailing address
	 *		we want to manage. If <tt>NULL</tt>, it means that there is an address without a
	 *		type; this can occur if the address is the default one.
	 *	<li><b>$theValue</b>: This parameter identifies the mailing address or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the address of the provided type.
	 *		<li><tt>FALSE</tt>: Delete the address of the provided type.
	 *		<li><i>other</i>: Any other value is cast to string and interpreted as the
	 *			mailing address of provided type which will be inserted, or that will
	 *			replace an existing entry.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will return either the address text, or <tt>NULL</tt> if the type was not
	 * matched.
	 *
	 * @param string				$theType			Mailing address type.
	 * @param mixed					$theValue			Mailing address or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_ENTITY_MAIL kTAG_GEN_TYPE kTAG_GEN_TEXT
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function EntityMail( $theType, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_ENTITY_MAIL, kTAG_GEN_TYPE, kTAG_GEN_TEXT,
				$theType, $theValue, $getOld );										// ==>
	
	} // EntityMail.

	 

} // trait EntityMail.


?>
