<?php

/**
 * Affiliation.php
 *
 * This file contains the definition of the {@link Affiliation} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *									Affiliation.php										*
 *																						*
 *======================================================================================*/

/**
 * Affiliation trait
 *
 * This trait implements a method for managing the entity affiliations offset,
 * {@link kTAG_ENTITY_AFFILIATION}. The method manages the individual affiliations by type.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 04/03/2014
 */
trait Affiliation
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Affiliation																			*
	 *==================================================================================*/

	/**
	 * Manage affiliation
	 *
	 * This method can be used to add, retrieve and delete individual affiliations by type,
	 * while managing all the mails as a whole can be done using the
	 * {@link kTAG_ENTITY_AFFILIATION} offset.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theType</b>: This parameter holds the <em>type</em> of the affiliation
	 *		we want to manage. If <tt>NULL</tt>, it means that there is an affiliation
	 *		without a type; this can occur if the it is the default one.
	 *	<li><b>$theValue</b>: This parameter holds the affiliated entity reference or
	 *		operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the affiliation of the provided type.
	 *		<li><tt>FALSE</tt>: Delete the affiliation of the provided type.
	 *		<li><i>other</i>: Any other value is cast to string and interpreted as the
	 *			entity reference of provided type which will be inserted, or that will
	 *			replace an existing entry.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * When providing a new affiliation, the method will call the
	 * {@link validateReference()} method to validate the reference.
	 *
	 * The method will return either the affiliation, or <tt>NULL</tt> if the type was not
	 * matched.
	 *
	 * @param string				$theType			Affiliation type.
	 * @param mixed					$theValue			Affiliation or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return mixed				Old or new value.
	 *
	 * @see kTAG_ENTITY_AFFILIATION kTAG_TYPE kTAG_ENTITY
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function Affiliation( $theType, $theValue = NULL, $getOld = FALSE )
	{
		//
		// Validate reference.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE) )
			$this->validateReference( $theValue, "EntityObject", kTYPE_REF_ENTITY );
		
		return $this->manageElementMatchOffset(
				kTAG_ENTITY_AFFILIATION, kTAG_TYPE, kTAG_ENTITY,
				$theType, $theValue, $getOld );										// ==>
	
	} // Affiliation.

	 

} // trait Affiliation.


?>
