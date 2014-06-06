<?php

/**
 * EntityLink.php
 *
 * This file contains the definition of the {@link EntityLink} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *										EntityLink.php									*
 *																						*
 *======================================================================================*/

/**
 * Entity link trait
 *
 * This trait implements a method for managing the entity internet address offset,
 * {@link kTAG_ENTITY_LINK}. The method manages the individual internet addresses by type.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/03/2014
 */
trait EntityLink
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	EntityLink																		*
	 *==================================================================================*/

	/**
	 * Manage internet address
	 *
	 * This method can be used to add, retrieve and delete individual internet addresses by
	 * type, while managing all the URLs as a whole can be done using the
	 * {@link kTAG_ENTITY_LINK} offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theType</b>: This parameter holds the <em>type</em> of the internet address
	 *		we want to manage. If <tt>NULL</tt>, it means that there is an address without a
	 *		type; this can occur if the address is the default one.
	 *	<li><b>$theValue</b>: This parameter identifies the internet address or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the address of the provided type.
	 *		<li><tt>FALSE</tt>: Delete the address of the provided type.
	 *		<li><i>other</i>: Any other value is cast to string and interpreted as the
	 *			internet address of provided type which will be inserted, or that will
	 *			replace an existing entry.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will return either the URL, or <tt>NULL</tt> if the type was not matched.
	 *
	 * @param string				$theType			Internet address type.
	 * @param mixed					$theValue			Internet address or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_ENTITY_LINK kTAG_GEN_TYPE kTAG_GEN_URL
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function EntityLink( $theType, $theValue = NULL, $getOld = FALSE )
	{
		return $this->manageElementMatchOffset(
				kTAG_ENTITY_LINK, kTAG_GEN_TYPE, kTAG_GEN_URL,
				$theType, $theValue, $getOld );										// ==>
	
	} // EntityLink.

	 

} // trait EntityLink.


?>
