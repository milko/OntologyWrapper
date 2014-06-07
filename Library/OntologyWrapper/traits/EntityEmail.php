<?php

/**
 * EntityEmail.php
 *
 * This file contains the definition of the {@link EntityEmail} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *									EntityEmail.php										*
 *																						*
 *======================================================================================*/

/**
 * Entity e-mail trait
 *
 * This trait implements a method for managing the entity electronic mail address offset,
 * {@link kTAG_ENTITY_EMAIL}. The method manages the individual e-mail addresses by type.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 03/03/2014
 */
trait EntityEmail
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	EntityEmail																		*
	 *==================================================================================*/

	/**
	 * Manage e-mail address
	 *
	 * This method can be used to add, retrieve and delete individual e-mail addresses by
	 * type, while managing all the mails as a whole can be done using the
	 * {@link kTAG_ENTITY_EMAIL} offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theType</b>: This parameter holds the <em>type</em> of the e-mail address
	 *		we want to manage. If <tt>NULL</tt>, it means that there is an address without a
	 *		type; this can occur if the address is the default one.
	 *	<li><b>$theValue</b>: This parameter identifies the e-mail address or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the address of the provided type.
	 *		<li><tt>FALSE</tt>: Delete the address of the provided type.
	 *		<li><i>other</i>: Any other value is cast to string and interpreted as the
	 *			e-mail address of provided type which will be inserted, or that will
	 *			replace an existing entry.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will return either the e-mail, or <tt>NULL</tt> if the type was not
	 * matched.
	 *
	 * @param string				$theType			E-mail address type.
	 * @param mixed					$theValue			E-mail address or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_ENTITY_EMAIL kTAG_TYPE kTAG_TEXT
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function EntityEmail( $theType, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_ENTITY_EMAIL, kTAG_TYPE, kTAG_TEXT,
				$theType, $theValue, $getOld );										// ==>
	
	} // EntityEmail.

	 

} // trait EntityEmail.


?>
