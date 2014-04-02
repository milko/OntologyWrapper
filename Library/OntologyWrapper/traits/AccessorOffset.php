<?php

/**
 * AccessorOffset.php
 *
 * This file contains the definition of the {@link AccessorOffset} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *									AccessorOffset.php									*
 *																						*
 *======================================================================================*/

/**
 * Offset accessor trait
 *
 * The main purpose of this trait is to provide a series of templates for managing object
 * offsets and structure components. The idea is to provide a common framework to member
 * accessor methods by sharing standards.
 *
 * This trait implements the following protected methods:
 *
 * <ul>
 *	<li><em>{@link manageOffset()}</em>: This method provides a standard interface to set,
 *		retrieve and delete offset values.
 *	<li><em>{@link manageSetOffset()}</em>: This method provides a standard interface to
 *		set, retrieve and delete elements of an array set, this method provides access to
 *		elements of the offset, rather than to the offset, which must be an array or
 *		{@link ArrayObject}.
 *	<li><em>{@link manageArrayOffset()}</em>: This method provides a standard interface to
 *		set, retrieve and delete key/value pair elements based on their key.
 *	<li><em>{@link manageElementMatchOffset()}</em>: This method provides a standard
 *		interface to set, retrieve and delete elements of an array or {@link ArrayObject},
 *		these elements are an array of two items in which the first one is the discriminator
 *		and the second is the value. 
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/02/2014
 */
trait AccessorOffset
{
		

/*=======================================================================================
 *																						*
 *							PROTECTED MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	manageOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage a scalar offset</h4>
	 *
	 * This class provides a protected interface for member accessor methods, both for
	 * properties and offsets, this method can be used to manage a scalar offset, its options
	 * involve setting, retrieving and deleting an offset of the provided array or ArrayObject.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theOffset</tt>: The offset to the attribute contained in the previous
	 *		parameter that is to be managed.
	 *	<li><tt>$theValue</tt>: The value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the offset's current value.
	 *		<li><tt>FALSE</tt>: Delete the offset.
	 *		<li><i>other</i>: Any other type represents the offset's new value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value of the offset <i>before</i> it was eventually
	 *			modified.
	 *		<li><tt>FALSE</tt>: Return the value of the offset <i>after</i> it was eventually
	 *			modified.
	 *	 </ul>
	 * </ul>
	 *
	 * @param string				$theOffset			Offset to be managed.
	 * @param mixed					$theValue			New value or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new value.
	 *
	 * @uses offsetSet()
	 * @uses offsetGet()
	 * @uses offsetUnset()
	 */
	protected function manageOffset( $theOffset, $theValue = NULL, $getOld = FALSE )
	{
		//
		// Normalise offset.
		//
		$theOffset = (string) $theOffset;
		
		//
		// Save current offset value.
		//
		$save = $this->offsetGet( $theOffset );
		
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $save;															// ==>
		
		//
		// Delete offset.
		//
		if( $theValue === FALSE )
			$this->offsetUnset( $theOffset );
		
		//
		// Set offset.
		//
		else
			$this->offsetSet( $theOffset, $theValue );
		
		if( $getOld )
			return $save;															// ==>
		
		return ( $theValue === FALSE )
			 ? NULL																	// ==>
			 : $theValue;															// ==>
	
	} // manageOffset.

	 
	/*===================================================================================
	 *	manageSetOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage value set offset</h4>
	 *
	 * This class provides a protected interface for member accessor methods, both for
	 * properties and offsets, this method can be used to manage the elements of a set of
	 * non repeting values, its options involve setting, retrieving and deleting elements of
	 * the set. This method generally applies to data of the {@link kTYPE_SET} data type.
	 *
	 * It is assumed that the value at the provided offset is either an array or an
	 * {@link ArrayObject}, if this is not the case, the method will raise an exception.
	 *
	 * If you want to manage the set itself, you have to use the offset management methods.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theOffset</tt>: The offset to the array attribute that is to be managed.
	 *	<li><tt>$theValue</tt>: The value of the array element.
	 *	<li><tt>$theOperation</tt>: The operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the value if it exists or <tt>NULL</tt>.
	 *		<li><tt>FALSE</tt>: Delete the value if it exists.
	 *		<li><i>other</i>: Any other type represents a new value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * When setting new values, if the current offset is empty, the method will set the new
	 * value in an aray; when deleting values, if the existing value is the last one, the
	 * method will delete the offset itself.
	 *
	 * @param string				$theOffset			Offset to be managed.
	 * @param mixed					$theValue			Array value.
	 * @param mixed					$theOperation		Operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new value.
	 *
	 * @throws Exception
	 *
	 * @uses offsetSet()
	 * @uses offsetGet()
	 * @uses offsetUnset()
	 */
	protected function manageSetOffset( $theOffset, $theValue, $theOperation = NULL,
															   $getOld = FALSE )
	{
		//
		// Normalise offset.
		//
		$theOffset = (string) $theOffset;
		
		//
		// Save current offset value.
		//
		$save = $this->offsetGet( $theOffset );
		
		//
		// Handle empty offset.
		//
		if( $save === NULL )
		{
			//
			// Handle retrieve and delete.
			//
			if( ($theOperation === NULL)
			 || ($theOperation === FALSE) )
				return NULL;														// ==>
			
			//
			// Handle new value.
			//
			$this->offsetSet( $theOffset, array( $theValue ) );
			
			return ( $getOld )
				 ? NULL																// ==>
				 : $theValue;														// ==>
		
		} // Empty offset.
		
		//
		// Check offset type.
		//
		if( is_array( $save )
		 || ($save instanceof \ArrayObject) )
		{
			//
			// Convert to array.
			//
			if( $save instanceof \ArrayObject )
				$array = $save->getArrayCopy();
			else
				$array = & $save;
			
			//
			// Return value.
			//
			if( $theOperation === NULL )
				return ( in_array( $theValue, $array ) )
					 ? $theValue													// ==>
					 : NULL;														// ==>
			
			//
			// Delete value.
			//
			if( $theOperation === FALSE )
			{
				//
				// Handle found.
				//
				if( in_array( $theValue, $array ) )
				{
					//
					// Delete value.
					//
					unset( $array[ array_search( $theValue, $array ) ] );
					
					//
					// Handle empty array.
					//
					if( ! count( $array ) )
						$this->offsetUnset( $theOffset );
					
					//
					// Update set.
					//
					else
					{
						//
						// Restore array.
						//
						$array = array_values( $array );
					
						//
						// Replace array.
						//
						if( is_array( $save ) )
							$this->offsetSet( $theOffset, $array );
						else
							$save->exchangeArray( $array );
					
					} // Non empty set.
				
					if( $getOld )
						return $theValue;											// ==>
					
				} // Found value.
				
				return NULL;														// ==>
				
			} // Delete value.
			
			//
			// Add value.
			//
			if( ! in_array( $theValue, $array ) )
			{
				//
				// Append value.
				//
				$save[] = $theValue;
				
				//
				// Update array.
				//
				if( is_array( $save ) )
					$this->offsetSet( $theOffset, $save );
				
				if( $getOld )
					return NULL;													// ==>
			
			} // New value.
			
			return $theValue;														// ==>
		
		} // Offset is array or ArrayObject.

		throw new Exception
				( "Expecting array or ArrayObject at offset [$theOffset]" );	// !@! ==>
	
	} // manageSetOffset.

	 
	/*===================================================================================
	 *	manageArrayOffset																*
	 *==================================================================================*/

	/**
	 * <h4>Manage value set offset</h4>
	 *
	 * This class provides a protected interface for member accessor methods, both for
	 * properties and offsets, this method can be used to manage the elements of a key/value
	 * array, its options involve setting, retrieving and deleting elements of the list by
	 * key. This method generally applies to data of the {@link kTYPE_ARRAY} data type.
	 *
	 * It is assumed that the value at the provided offset is either an array or an
	 * {@link ArrayObject}, if this is not the case, the method will raise an exception.
	 *
	 * If you want to manage the array itself, you have to use the offset management
	 + methods.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theOffset</tt>: The offset to the array attribute that is to be managed.
	 *	<li><tt>$theKey</tt>: The element key.
	 *	<li><tt>$theValue</tt>: The element value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the value at the provided key, or <tt>NULL</tt>.
	 *		<li><tt>FALSE</tt>: Delete the value at the provided key.
	 *		<li><i>other</i>: Any other type represents a new or a replacement value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * When setting new values, if the current offset is empty, the method will set the new
	 * value in an aray; when deleting values, if the existing value is the last one, the
	 * method will delete the offset itself.
	 *
	 * @param string				$theOffset			Offset to be managed.
	 * @param mixed					$theKey				Element key.
	 * @param mixed					$theValue			Element value or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new value.
	 *
	 * @throws Exception
	 *
	 * @uses offsetSet()
	 * @uses offsetGet()
	 * @uses offsetUnset()
	 */
	protected function manageArrayOffset( $theOffset, $theKey, $theValue = NULL,
															   $getOld = FALSE )
	{
		//
		// Normalise offset.
		//
		$theOffset = (string) $theOffset;
		
		//
		// Save current offset value.
		//
		$save = $this->offsetGet( $theOffset );
		
		//
		// Handle empty offset.
		//
		if( $save === NULL )
		{
			//
			// Handle retrieve and delete.
			//
			if( ($theValue === NULL)
			 || ($theValue === FALSE) )
				return NULL;														// ==>
			
			//
			// Handle new value.
			//
			$this->offsetSet( $theOffset, array( $theKey => $theValue ) );
			
			return ( $getOld )
				 ? NULL																// ==>
				 : $theValue;														// ==>
		
		} // Empty offset.
		
		//
		// Check offset type.
		//
		if( is_array( $save )
		 || ($save instanceof \ArrayObject) )
		{
			//
			// Convert to array.
			//
			if( $save instanceof \ArrayObject )
				$array = $save->getArrayCopy();
			else
				$array = & $save;
			
			//
			// Check current element.
			//
			$found = array_key_exists( $theKey, $array );
			
			//
			// Return value.
			//
			if( $theValue === NULL )
				return ( $found  )
					 ? $array[ $theKey ]											// ==>
					 : NULL;														// ==>
			
			//
			// Save old value.
			//
			$old = ( $found )
				 ? $array[ $theKey ]
				 : NULL;
			
			//
			// Delete value.
			//
			if( $theValue === FALSE )
			{
				//
				// Handle found.
				//
				if( $found )
				{
					//
					// Delete value.
					//
					unset( $array[ $theKey ] );
					
					//
					// Handle empty array.
					//
					if( ! count( $array ) )
						$this->offsetUnset( $theOffset );
					
					//
					// Update set.
					//
					else
					{
						//
						// Replace array.
						//
						if( is_array( $save ) )
							$this->offsetSet( $theOffset, $array );
						else
							$save->exchangeArray( $array );
					
					} // Non empty set.
				
					if( $getOld )
						return $old;												// ==>
					
				} // Found value.
				
				return NULL;														// ==>
				
			} // Delete value.
			
			//
			// Set value.
			//
			$array[ $theKey ] = $theValue;
			
			//
			// Replace array.
			//
			if( is_array( $save ) )
				$this->offsetSet( $theOffset, $array );
			else
				$save->exchangeArray( $array );
		
			if( $getOld )
				return $old;														// ==>
			
			return $theValue;														// ==>
		
		} // Offset is array or ArrayObject.

		throw new Exception
				( "Expecting array or ArrayObject at offset [$theOffset]" );	// !@! ==>
	
	} // manageArrayOffset.

	 
	/*===================================================================================
	 *	manageElementMatchOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Manage element match offset</h4>
	 *
	 * This class provides a protected interface for member accessor methods, both for
	 * properties and offsets, this method can be used to manage the elements of an array of
	 * array elements in which one item's value represents the discriminant.
	 *
	 * Offsets of this kind are arrays that represent a list of elements, each of which is
	 * constituted by an array of two items, where the value matching the
	 * <tt>$theTypeOffset</tt> key represents the element key and the value matching the
	 * <tt>$theDataOffset</tt> key represents the element's value.
	 *
	 * This method allows retrieving the element value and deleting or setting an element.
	 *
	 * It is assumed that the value at the provided offset is an array, if this is not the
	 * case, the method will raise an exception.
	 *
	 * If you want to manage the set itself, you have to use the offset management methods.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theOffset</tt>: The offset to the attribute containing the match list.
	 *	<li><tt>$theTypeOffset</tt>: The offset to the type within the element.
	 *	<li><tt>$theDataOffset</tt>: The offset to the data within the element.
	 *	<li><tt>$theTypeValue</tt>: The value of the element's type to match, it represents
	 *		the key to the element; we assume the value to be a string. A <tt>NULL</tt>
	 *		value is used to select the element missing the <tt>$theTypeOffset</tt> offset.
	 *	<li><tt>$theDataValue</tt>: The value or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the offset's <tt>$theDataOffset</tt> value.
	 *		<li><tt>FALSE</tt>: Delete the element matchhing the <tt>$theTypeOffset</tt>.
	 *		<li><i>other</i>: Any other type represents the element's new value.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value of the offset <i>before</i> it was
	 *			eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value of the offset <i>after</i> it was
	 *			eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method expects each element to be a structure containing at least an element
	 * indexed by the <tt>$theDataOffset</tt> offset, if that is not the case, the method
	 * will raise an exception. All elements are supposed to be arrays.
	 *
	 * @param string				$theOffset			Offset to be managed.
	 * @param string				$theTypeOffset		Offset of type item.
	 * @param string				$theDataOffset		Offset of data item.
	 * @param string				$theTypeValue		Type value.
	 * @param mixed					$theDataValue		New value or operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new value.
	 *
	 * @throws Exception
	 *
	 * @uses offsetSet()
	 * @uses offsetGet()
	 * @uses offsetUnset()
	 */
	protected function manageElementMatchOffset( $theOffset,
												 $theTypeOffset, $theDataOffset,
												 $theTypeValue, $theDataValue = NULL,
												 $getOld = FALSE )
	{
		//
		// Init local storage.
		//
		$list = $this->offsetGet( $theOffset );
		
		//
		// Handle empty offset.
		//
		if( $list === NULL )
		{
			//
			// Handle retrieve and delete.
			//
			if( ($theDataValue === NULL)
			 || ($theDataValue === FALSE) )
				return NULL;														// ==>
			
			//
			// Handle new element.
			//
			if( $theTypeValue !== NULL )
				$this->offsetSet( $theOffset,
								  array( array( $theTypeOffset => $theTypeValue,
												$theDataOffset => $theDataValue ) ) );
			else
				$this->offsetSet( $theOffset,
								  array( array( $theDataOffset => $theDataValue ) ) );
			
			if( $getOld )
				return NULL;														// ==>
			
			return $theDataValue;													// ==>
		
		} // Empty offset.
		
		//
		// Check offset type.
		//
		if( is_array( $list )
		 || ($list instanceof \ArrayObject) )
		{
			//
			// Init local storage.
			//
			$index = NULL;
			
			//
			// Convert to array.
			//
			if( $list instanceof \ArrayObject )
				$array = $list->getArrayCopy();
			else
				$array = & $list;
	
			//
			// Iterate list.
			//
			foreach( $array as $key => $element )
			{
				//
				// Check element data type.
				//
				if( ! is_array( $element ) )
					throw new \Exception
							( "Expecting a list of arrays at offset "
							 ."[$theOffset]" );									// !@! ==>
				
				//
				// Check data element.
				//
				if( ! array_key_exists( $theDataOffset, $element ) )
					throw new \Exception
							( "Expecting an element featuring the "
							 ."[$theDataOffset] offset" );						// !@! ==>
				
				//
				// Match element.
				//
				if( ( ($theTypeValue === NULL)
				   && (! array_key_exists( $theTypeOffset, $element )) )
				 || ( ($theTypeValue !== NULL)
				   && array_key_exists( $theTypeOffset, $element )
				   && ($element[ $theTypeOffset ] == $theTypeValue) ) )
				{
					//
					// Save index.
					//
					$index = $key;
					
					break;													// =>
				
				} // Matched.
			
			} // Iterating elements.
		
			//
			// Return value.
			//
			if( $theDataValue === NULL )
				return ( $index !== NULL )
					 ? $element[ $theDataOffset ]									// ==>
					 : NULL;														// ==>
			
			//
			// Delete element.
			//
			if( $theDataValue === FALSE )
			{
				//
				// Handle matched element.
				//
				if( $index !== NULL )
				{
					//
					// Delete element.
					//
					unset( $array[ $key ] );
					
					//
					// Handle empty array.
					//
					if( ! count( $array ) )
						$this->offsetUnset( $theOffset );
					
					//
					// Update array.
					//
					else
					{
						//
						// Restore array.
						//
						$array = array_values( $array );
					
						//
						// Replace array.
						//
						if( is_array( $list ) )
							$this->offsetSet( $theOffset, $array );
						else
							$list->exchangeArray( $array );
					
					} // Non empty set.
				
					if( $getOld )
						return $element[ $theDataOffset ];							// ==>
				
				} // Matched element.
				
				return NULL;														// ==>
			
			} // Delete element.
			
			//
			// Add element.
			//
			if( $index === NULL )
			{
				//
				// Build element.
				//
				$element = ( $theTypeValue !== NULL )
						 ? array( $theTypeOffset => $theTypeValue,
						 		  $theDataOffset => $theDataValue )
						 : array( $theDataOffset => $theDataValue );
				
				//
				// Add element.
				//
				$list[] = $element;
				
				//
				// Handle arrays.
				//
				if( is_array( $list ) )
					$this->offsetSet( $theOffset, $list );
				
				if( $getOld )
					return NULL;													// ==>
			
			} // Element not matched.
			
			//
			// Update element.
			//
			else
			{
				//
				// Save value.
				//
				$save = $element[ $theDataOffset ];
				
				//
				// Update element.
				//
				$element[ $theDataOffset ] = $theDataValue;
				
				//
				// Update list.
				//
				$list[ $key ] = $element;
				
				//
				// Update array.
				//
				if( is_array( $list ) )
					$this->offsetSet( $theOffset, $list );
				
				if( $getOld )
					return $save;													// ==>
			
			} // Element matched.
			
			return $theDataValue;													// ==>
		
		} // Correct offset data type.

		throw new Exception
				( "Expecting array or ArrayObject at offset [$theOffset]" );	// !@! ==>
	
	} // manageElementMatchOffset.

	 
	/*===================================================================================
	 *	manageElementListOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Manage element list offset</h4>
	 *
	 * This class provides a protected interface for member accessor methods, both for
	 * properties and offsets, this method can be used to manage the elements of an array of
	 * array elements in which one item's value represents the discriminant and the other
	 * item is a list of values.
	 *
	 * Offsets of this kind are arrays that represent a list of elements, each of which is
	 * constituted by an array of two items, where the value matching the
	 * <tt>$theTypeOffset</tt> key represents the element key and the value matching the
	 * <tt>$theDataOffset</tt> key represents the element's value. The element's value, in
	 * this case, is a list of values: this method allows managing the individual elements
	 * of this list.
	 *
	 * It is assumed that the value at the provided offset is an array, if this is not the
	 * case, the method will raise an exception.
	 *
	 * If you want to manage the set itself, you have to use the offset management methods.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theOffset</tt>: The offset to the attribute containing the match list.
	 *	<li><tt>$theTypeOffset</tt>: The offset to the element item representing the
	 *		discriminator.
	 *	<li><tt>$theDataOffset</tt>: The offset to the element holding the list of values
	 *		related to the discriminator.
	 *	<li><tt>$theTypeValue</tt>: The value of the discriminator element to match.
	 *	<li><tt>$theDataValue</tt>: The value of the list element related to the
	 *		discriminator, or the list operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Select all the elements of the list related to the provided
	 *			discriminator.
	 *		<li><i>other</i>: Any other type will be cast to a string and will represent the
	 *			list value to be matched.
	 *	 </ul>
	 *	<li><tt>$theOperation</tt>: The operation to be performed:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the list value matched by the <tt>$theDataValue</tt>
	 *			parameter.
	 *		<li><tt>FALSE</tt>: Delete the list value matched by the <tt>$theDataValue</tt>
	 *			parameter.
	 *		<li><tt>TRUE</tt>: Add the list value provided in the <tt>$theDataValue</tt>
	 *			parameter to the list.
	 *	 </ul>
	 *	<li><tt>$getOld</tt>: Determines what the method will return:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the value <i>before</i> it was eventually modified.
	 *		<li><tt>FALSE</tt>: Return the value <i>after</i> it was eventually modified.
	 *	 </ul>
	 * </ul>
	 *
	 * The method expects each element to be a structure containing containing both the
	 * <tt>$theTypeOffset</tt> and <tt>$theDataOffset</tt> items, if that is not the case,
	 * the method will raise an exception. All elements are supposed to be arrays.
	 *
	 * @param string				$theOffset			Offset to be managed.
	 * @param string				$theTypeOffset		Offset of type item.
	 * @param string				$theDataOffset		Offset of data item.
	 * @param string				$theTypeValue		Discriminator value.
	 * @param mixed					$theDataValue		Lis value selector.
	 * @param mixed					$theOperation		Operation.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access protected
	 * @return mixed				Old or new value.
	 *
	 * @throws Exception
	 *
	 * @uses offsetSet()
	 * @uses offsetGet()
	 * @uses offsetUnset()
	 */
	protected function manageElementListOffset( $theOffset,
												$theTypeOffset, $theDataOffset,
												$theTypeValue, $theDataValue = NULL,
												$theOperation = NULL, $getOld = FALSE )
	{
		//
		// Init local storage.
		//
		$list = $this->offsetGet( $theOffset );
		
		//
		// Handle empty offset.
		//
		if( $list === NULL )
		{
			//
			// Handle retrieve and delete.
			//
			if( ($theOperation === NULL)
			 || ($theOperation === FALSE) )
				return NULL;														// ==>
			
			//
			// Assert the data value.
			//
			if( $theDataValue === NULL )
				throw new \Exception
						( "Expecting a value for offset [$theOffset]." );		// !@! ==>
			
			//
			// Handle new element.
			//
			if( $theOperation )
			{
				$this->offsetSet(
					$theOffset,
					array( array( $theTypeOffset => $theTypeValue,
						   $theDataOffset => array( $theDataValue ) ) ) );
			
				if( $getOld )
					return NULL;													// ==>
			
				return $theDataValue;												// ==>
			
			} // New element.
			
			return NULL;															// ==>
		
		} // Empty offset.
		
		//
		// Check offset type.
		//
		if( is_array( $list ) )
		{
			//
			// Init local storage.
			//
			$idx_element = $idx_item = NULL;
	
			//
			// Match element.
			//
			foreach( $list as $key => $value )
			{
				//
				// Check element data type.
				//
				if( ! is_array( $value ) )
					throw new \Exception
							( "Expecting a list of arrays "
							 ."at offset [$theOffset]." );						// !@! ==>
				
				//
				// Check type element.
				//
				if( ! array_key_exists( $theTypeOffset, $value ) )
					throw new \Exception
							( "Expecting an element featuring "
							 ."the [$theTypeOffset] offset." );					// !@! ==>
				
				//
				// Check data element.
				//
				if( ! array_key_exists( $theDataOffset, $value ) )
					throw new \Exception
							( "Expecting an element featuring "
							 ."the [$theDataOffset] offset." );					// !@! ==>
				
				//
				// Match discriminator.
				//
				if( $value[ $theTypeOffset ] == $theTypeValue )
				{
					//
					// Reference element.
					//
					$idx_element = $key;
					
					//
					// Match item.
					//
					if( $theDataValue !== NULL )
					{
						foreach( $value[ $theDataOffset ] as $key => $value )
						{
							if( $value == $theDataValue )
							{
								$idx_item = $key;
								
								break;										// =>
							
							} // Matched data value.
						
						} // Iterating data values.
					
					} // Provided data match.
					
					break;													// =>
				
				} // Matched discriminator.
			
			} // Iterating elements.
		
			//
			// Return.
			//
			if( $theOperation === NULL )
			{
				//
				// Handle element not matched.
				//
				if( $idx_element === NULL )
					return NULL;													// ==>
				
				//
				// Return data list.
				//
				if( $theDataValue === NULL )
					return $list[ $idx_element ][ $theDataOffset ];					// ==>
				
				//
				// Handle item not matched.
				//
				if( $idx_item === NULL )
					return NULL;													// ==>
				
				return $list[ $idx_element ][ $theDataOffset ][ $idx_item ];		// ==>
			
			} // Retrieve.
			
			//
			// Delete.
			//
			if( $theOperation === FALSE )
			{
				//
				// Handle element not matched.
				//
				if( $idx_element === NULL )
					return NULL;													// ==>
				
				//
				// Delete data list.
				//
				if( $theDataValue === NULL )
				{
					//
					// Save element.
					//
					$save = $list[ $idx_element ];
					
					//
					// Delete element.
					//
					unset( $list[ $idx_element ] );
					
					//
					// Handle no elements.
					//
					if( ! count( $list ) )
						$this->offsetUnset( $theOffset );
					
					//
					// Update array.
					//
					else
					{
						//
						// Restore array.
						//
						$list = array_values( $list );
					
						//
						// Replace array.
						//
						$this->offsetSet( $theOffset, $list );
					
					} // Non empty set.
				
				} // Delete data list.
				
				//
				// Delete data item.
				//
				else
				{
					//
					// Handle item not matched.
					//
					if( $idx_item === NULL )
						return NULL;												// ==>
				
					//
					// Reference element.
					//
					$ref_element = & $list[ $idx_element ];
				
					//
					// Reference data items.
					//
					$ref_items = & $ref_element[ $theDataValue ];
				
					//
					// Save item.
					//
					$save = $ref_items[ $idx_item ];
				
					//
					// Delete item.
					//
					unset( $ref_items[ $idx_item ] );
				
					//
					// Handle no items.
					//
					if( ! count( $ref_items ) )
					{
						//
						// Delete element.
						//
						unset( $list[ $idx_element ] );
					
						//
						// Handle no elements.
						//
						if( ! count( $list ) )
							$this->offsetUnset( $theOffset );
					
						//
						// Update items.
						//
						else
						{
							//
							// Restore elements.
							//
							$list = array_values( $list );
					
							//
							// Replace elements.
							//
							$this->offsetSet( $theOffset, $list );
					
						} // Non empty set.
				
					} // No more items.
				
					//
					// Update items.
					//
					else
					{
						//
						// Restore items.
						//
						$ref_items = array_values( $ref_items );
				
						//
						// Replace elements.
						//
						$this->offsetSet( $theOffset, $list );
					
					} // Items left.
				
				} // Delete data item.
					
				if( $getOld )
					return $save;													// ==>
				
				return NULL;														// ==>
			
			} // Delete.
			
			//
			// Assert the data value.
			//
			if( $theDataValue === NULL )
				throw new \Exception
						( "Expecting a value for offset [$theOffset]." );		// !@! ==>
			
			//
			// Add new element.
			//
			if( $idx_element === NULL )
				$list[] = array( array( $theTypeOffset => $theTypeValue ),
								 array( $theDataOffset => array( $theDataValue ) ) );
			
			//
			// Handle existing element.
			//
			else
			{
				//
				// Handle duplicate.
				//
				if( $idx_item !== NULL )
					return $theDataValue;											// ==>
			
				//
				// Add item.
				//
				$list[ $idx_element ][ $theDataOffset ][] = $theDataValue;
			
				//
				// Replace elements.
				//
				$this->offsetSet( $theOffset, $list );
			
			} // Existing element.
			
			if( $getOld )
				return NULL;														// ==>
			
			return $theDataValue;													// ==>
		
		} // Correct offset data type.

		throw new Exception
				( "Expecting array at offset [$theOffset]." );					// !@! ==>
	
	} // manageElementListOffset.

	 

} // trait AccessorOffset.


?>
