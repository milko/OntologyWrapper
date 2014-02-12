<?php

/**
 * NodeObject.php
 *
 * This file contains the definition of the {@link NodeObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\TagObject;
use OntologyWrapper\TermObject;

/*=======================================================================================
 *																						*
 *									NodeObject.php										*
 *																						*
 *======================================================================================*/

/**
 * Node object
 *
 * This class extends {@link OntologyObject} to implement a concrete node object class.
 *
 * A node is a <em>vertex in a graph structure</em>, nodes reference
 * <em>{@link TermObject}</em> and <em>{@link TagObject</em> instances, when referencing a
 * term, nodes are used to build <em>ontologies</em>, <em>type definitions</em> and
 * <em>controlloed vocabularies</em>; when referencing tags they are used to build <em>data
 * structures</em>, <em>input and output templates</em> and <em>search forms</em>.
 *
 * Node objects, along with edge objects, represent the presentation layer of the ontology,
 * users compose and consult network structures through these objects.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. This required attribute holds
 *		an <em>integer serial number</em>, nodes do not have a unique persistent identifier,
 *		since they act as references and because you may have more than one node referencing
 *		the same term or property. The native identifier is assigned automatically.
 *	<li><tt>{@link kTAG_TERM}</tt>: <em>Term</em>. This attribute is a <em>string</em> that
 *		holds a reference to the <em>term object</em> that the current node <em>represents
 *		in a graph structure</em>. If this offset is set, the {@link kTAG_TAG} offset must
 *		be omitted. This attribute must be managed with its offset.
 *	<li><tt>{@link kTAG_TAG}</tt>: <em>Tag</em>. This attribute is a <em>string</em> that
 *		holds a reference to the <em>tag object</em> that the current node <em>represents
 *		in a graph structure</em>. If this offset is set, the {@link kTAG_TERM} offset must
 *		be omitted. This attribute must be managed with its offset.
 * </ul>
 *
 * The {@link __toString()} method will return the value stored in the {@link kTAG_TERM} or
 * the {@link kTAG_TAG} offset. This value represents the node persistent identifier, which
 * is not, however, unique.
 *
 * Nodes cannot be uniquely identified via a persistent identifier, because more than one
 * node may share the same term or tag, this means that when searching for nodes you should
 * rely more on traversing a graph path, rather than selecting an object from a list.
 *
 * Objects of this class can hold any additional attribute that is considered necessary or
 * useful to define and share the current node. In this class we define only those
 * attributes that constitute the core functionality of the object, derived classes will add
 * attributes specific to the domain in which the object will operate.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 11/02/2014
 */
class NodeObject extends OntologyObject
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
	 * If the object holds the term reference, this will be returned; if it holds the tag
	 * reference, it will be returned; if none of these are set, the method will return an
	 * empty string.
	 *
	 * @access public
	 * @return string				The persistent identifier.
	 */
	public function __toString()
	{
		//
		// Get term.
		//
		if( \ArrayObject::offsetExists( kTAG_TERM ) )
			return \ArrayObject::offsetGet( kTAG_TERM );							// ==>
		
		//
		// Get tag.
		//
		if( \ArrayObject::offsetExists( kTAG_TAG ) )
			return \ArrayObject::offsetGet( kTAG_TAG );								// ==>
		
		return '';																	// ==>
	
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
	 * In this class we cast the value of the term into a term reference, or the value of a
	 * tag in a tag reference; we also ensure that provided objects are of the correct
	 * class.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_TAG kTAG_TERM
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
			// Intercept tag.
			//
			if( $theOffset == kTAG_TAG )
			{
				//
				// Handle objects.
				//
				if( is_object( $theValue ) )
				{
					//
					// If term, get its reference.
					//
					if( $theValue instanceof TagObject )
						$theValue = $theValue->Reference();
				
					//
					// If not a term, complain.
					//
					else
						throw new \Exception(
							"Unable to set tag reference: "
						   ."provided an object other than a tag." );			// !@! ==>
			
				} // Object.
			
				//
				// Cast to string.
				//
				else
					$theValue = (string) $theValue;
			
			} // Setting tag.
			
			//
			// Intercept term.
			//
			if( $theOffset == kTAG_TERM )
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
							"Unable to set term reference: "
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

	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * In thid class we delete the tag when we set the term and vice-versa.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Handle new tag.
		//
		if( $theOffset == kTAG_TAG )
			$this->offsetUnset( kTAG_TERM );
	
		//
		// Handle new term.
		//
		if( $theOffset == kTAG_TERM )
			$this->offsetUnset( kTAG_TAG );
	
	} // postOffsetSet.

	 

} // class NodeObject.


?>
