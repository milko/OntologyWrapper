<?php

/**
 * Terms.php
 *
 * This file contains the definition of the {@link Terms} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *										Terms.php										*
 *																						*
 *======================================================================================*/

/**
 * Terms trait
 *
 * This trait implements a method for managing the terms list offset,
 * {@link kTAG_TERMS}. The method allows the management of the elements of the list.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/02/2014
 */
trait Terms
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	TermPush																		*
	 *==================================================================================*/

	/**
	 * Add to terms path
	 *
	 * This method can be used to append elements to the object's terms path, it will add
	 * the provided element to the end of the path.
	 *
	 * If you provide a {@link Term} as the parameter, the method will
	 * {@link reference()} it.
	 *
	 * The method will return the number of elements in the path.
	 *
	 * @param mixed					$theTerm			Term reference or object.
	 *
	 * @access public
	 * @return integer				Number of elements in path.
	 *
	 * @see kTAG_TERMS
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function TermPush( $theTerm )
	{
		//
		// Handle objects.
		//
		if( is_object( $theTerm ) )
		{
			//
			// If term, get its reference.
			//
			if( $theTerm instanceof \OntologyWrapper\Term )
				$theTerm = $theTerm->reference();
		
			//
			// If not a term, complain.
			//
			else
				throw new \Exception(
					"Unable to add element to terms path: "
				   ."provided an object other than term." );					// !@! ==>
	
		} // Object.
	
		//
		// Cast to setring.
		//
		else
			$theTerm = (string) $theTerm;
		
		//
		// Get current path.
		//
		$path = ( \ArrayObject::offsetExists( kTAG_TERMS ) )
			  ? \ArrayObject::offsetGet( kTAG_TERMS )
			  : Array();
		
		//
		// Add element.
		//
		$path[] = $theTerm;
		
		//
		// Get count.
		//
		$count = count( $path );
		
		//
		// Set offset.
		//
		$this->offsetSet( kTAG_TERMS, $path );
		
		return $count;																// ==>
	
	} // TermPush.

	 
	/*===================================================================================
	 *	TermPop																			*
	 *==================================================================================*/

	/**
	 * Pop terms from path
	 *
	 * This method can be used to pop elements off the end of the object's terms path, it
	 * will remove the last element in the sequence.
	 *
	 * When you remove the last element of the path, the method will also remove the offset.
	 *
	 * The method will return the removed element; if the path is empty, the method will
	 * return <tt>NULL</tt>.
	 *
	 * @access public
	 * @return string				Removed element or <tt>NULL</tt>.
	 *
	 * @see kTAG_TERMS
	 *
	 * @uses manageElementMatchOffset()
	 */
	public function TermPop()
	{
		//
		// Get current path.
		//
		if( \ArrayObject::offsetExists( kTAG_TERMS ) )
		{
			//
			// Get current path.
			//
			$path = \ArrayObject::offsetGet( kTAG_TERMS );
			
			//
			// Pop element.
			//
			$element = array_pop( $path );
			
			//
			// Update parh.
			//
			if( count( $path ) )
				$this->offsetSet( kTAG_TERMS, $path );
			
			//
			// Delete offset.
			//
			else
				$this->offsetUnset( kTAG_TERMS );
			
			return $element;														// ==>
		
		} // Has branch.
		
		return NULL;																// ==>
	
	} // TermPop.

	 
	/*===================================================================================
	 *	TermCount																		*
	 *==================================================================================*/

	/**
	 * Count terms path elements
	 *
	 * This method will return the number of elements in the object's terms path.
	 *
	 * @access public
	 * @return integer				Number of elements in terms path.
	 *
	 * @see kTAG_TERMS
	 */
	public function TermCount()
	{
		//
		// Check branch.
		//
		if( \ArrayObject::offsetExists( kTAG_TERMS ) )
			return count( \ArrayObject::offsetGet( kTAG_TERMS ) );					// ==>
		
		return 0;																	// ==>
	
	} // TermCount.

	 

} // trait Terms.


?>
