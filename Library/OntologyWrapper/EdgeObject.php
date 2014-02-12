<?php

/**
 * EdgeObject.php
 *
 * This file contains the definition of the {@link EdgeObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\TagObject;
use OntologyWrapper\TermObject;

/*=======================================================================================
 *																						*
 *									EdgeObject.php										*
 *																						*
 *======================================================================================*/

/**
 * Edge object
 *
 * This class extends {@link OntologyObject} to implement a concrete edge object class.
 *
 * This class implements a <em>directed graph</em> by <em>relating a subject vertex</em>
 * with an <em>object vertex</em> through a <em>predicate</em>, the direction of the
 * relationship is <em>from the subject to the object</em>.
 *
 * The vertices of this relatonship, the subject and object, are {@link NodeObject} instance
 * references, while the relationship predicate is represented by a {@link TermObject}
 * instance reference.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. This required attribute holds
 *		a <em>string</em> which represents the <em>combination of the subject, predicate and
 *		object</em> of the relationship. This attribute must be managed with its offset,
 *		although in derived classes it will be set automatically.
 *	<li><tt>{@link kTAG_SUBJECT}</tt>: <em>Subject</em>. This attribute represents the
 *		<em>origin of the relationship</em>, it is an <em>integer</em> value representing
 *		the <em>reference to a {@link NodeObject} instance</em>. This attribute must be
 *		managed with its offset.
 *	<li><tt>{@link kTAG_PREDICATE}</tt>: <em>Predicate</em>. This attribute represents the
 *		<em>type of relationship</em>, it is a <em>string</em> value representing the
 *		<em>reference to a {@link TermObject} instance</em>. This attribute must be managed
 *		with its offset.
 *	<li><tt>{@link kTAG_OBJECT}</tt>: <em>Object</em>. This attribute represents the
 *		<em>destination of the relationship</em>, it is an <em>integer</em> value
 *		representing the <em>reference to a {@link NodeObject} instance</em>. This attribute
 *		must be managed with its offset.
 * </ul>
 *
 * The {@link __toString()} method will return the value stored in the native identifier,
 * if set, or the computed native identifier, which is the concatenation of the subject,
 * predicate and object references separated by the {@link kTOKEN_INDEX_SEPARATOR} token.
 *
 * Objects of this class feature a primary key which is not persistent: the vertices
 * referenced in the native identifier are integer sequences which depend on the order these
 * objects were inserted: this means that both {@link NodeObject} and {@link EdgeObject}
 * instances must be re-created when exported.
 *
 * Objects of this class can hold any additional attribute that is considered necessary or
 * useful to define and share the current node. In this class we define only those
 * attributes that constitute the core functionality of the object, derived classes will add
 * attributes specific to the domain in which the object will operate.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 11/02/2014
 */
class EdgeObject extends OntologyObject
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
	 * The global identifier of the current object is represented by the subject, predicate
	 * and object references separated by the {@link kTOKEN_INDEX_SEPARATOR} token.
	 *
	 * @access public
	 * @return string				The global identifier.
	 */
	public function __toString()
	{
		//
		// Get relationship terms.
		//
		$terms = Array();
		$terms[] = $this->offsetGet( kTAG_SUBJECT );
		$terms[] = $this->offsetGet( kTAG_PREDICATE );
		$terms[] = $this->offsetGet( kTAG_OBJECT );
		
		return implode( kTOKEN_INDEX_SEPARATOR, $terms );							// ==>
	
	} // __toString.

		

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
	 * In this class we return <tt>TRUE</tt>, assuming the object is ready.
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
	 * In this class we cast the value of the relationship vertices into node reference, and
	 * the value of the predicate into a term reference, if provided as objects; we also
	 * ensure the provided objects arer of the correct type.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SUBJECT kTAG_PREDICATE kTAG_OBJECT
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
			// Intercept node.
			//
			if( ($theOffset == kTAG_SUBJECT)
			 || ($theOffset == kTAG_OBJECT) )
			{
				//
				// Handle objects.
				//
				if( is_object( $theValue ) )
				{
					//
					// If term, get its reference.
					//
					if( $theValue instanceof NodeObject )
						$theValue = $theValue->Reference();
				
					//
					// If not a term, complain.
					//
					else
						throw new \Exception(
							"Unable to set edge vertex: "
						   ."provided an object other than a node." );			// !@! ==>
			
				} // Object.
			
				//
				// Cast to integer.
				//
				else
					$theValue = (int) $theValue;
			
			} // Setting tag.
			
			//
			// Intercept term.
			//
			if( $theOffset == kTAG_PREDICATE )
			{
				//
				// Handle objects.
				//
				if( is_object( $theValue ) )
				{
					//
					// If term, get its reference.
					//
					if( $theValue instanceof TermObject )
						$theValue = $theValue->Reference();
				
					//
					// If not a term, complain.
					//
					else
						throw new \Exception(
							"Unable to set predicate: "
						   ."provided an object other than a term." );			// !@! ==>
			
				} // Object.
			
				//
				// Cast to string.
				//
				else
					$theValue = (string) $theValue;
			
			} // Setting term.
			
		} // Passed preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 

} // class EdgeObject.


?>
