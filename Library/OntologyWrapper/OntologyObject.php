<?php

/**
 * OntologyObject.php
 *
 * This file contains the definition of the {@link OntologyObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ContainerObject;
use OntologyWrapper\Dictionary;

/*=======================================================================================
 *																						*
 *									OntologyObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Tags.
 *
 * This file contains the default tag definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

/**
 * Types.
 *
 * This file contains the default data type definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Types.inc.php" );

/**
 * Tokens.
 *
 * This file contains the default token definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Tokens.inc.php" );

/**
 * Ontology object
 *
 * Objects derived from this <i>abstract</i> class hold two types of data:
 *
 * <ul>
 *	<li><em>Run-time data</em>, which is stored in the object member properties.
 *	<li><em>Persistent data</em>, which is stored in the inherited array part of the object.
 * </ul>
 *
 * The main purpose of this class is to ensure that all persistent data elements are
 * <i>referenced</i>, <i>annotated</i> and <i>documented</i> in an ontology.
 *
 * Persistent data is stored as <i>key/value</i> pair elements of the inherited array, the
 * key part of the elements we call by convention <em>offset</em>, these offsets, in this
 * class, represent a reference to an object of the ontology that holds all the necessary
 * information to <i>identify</i>, <i>describe</i> and <i>validate</i> the value part of the
 * array element pairs.
 *
 * This class implements the bridge between object persistent data and the ontology,
 * ensuring that all data holds a reference to the ontology, which, itself, is implemented
 * by objects derived from this same class: this means that the whole system is self
 * sufficient and self documenting.
 *
 * Offsets can be uniquely identified in two ways: by native identifier, which is an integer
 * value which may change across implementations, and a global identifier, which is a sring
 * that will not change across implementations. This class provides a transparent interface
 * that allows referring to offsets both by their <i>native</i> identifier or by their
 * <i>global</i> identifier. Offsets, however, <em>will only hold the native
 * identifier</em>, which means that all persistent data offsets must be integers. This is
 * because global identifiers may become large strings, which poses a problem if these are
 * used as field names for data stored in a persistent container.
 *
 * Whenever the object is provided an offset, if this is a string, it will be fed to a
 * protected method, {@link resolveOffset()}, which will check if the string represents the
 * global identifier of an ontology Tag object, in that case, the method will return the
 * Tag's native integer identifier which will be used as the data offset. The class
 * features a public method, {@link InternalOffsets()}, that returns the list of exceptions.
 *
 * This means that to ensure referential integrity it is advisable to use integer constants
 * as offsets when available, or string offsets if the integer constant is not known or
 * available.
 *
 * The resolution of these offsets is provided by a {@link Dictionary} object which records
 * all the <em>Tag</em> objects of the ontology which are the entities that all offsets
 * reference: persistent data offsets represent these Tag native identifiers, while these
 * Tag object global identifiers are decoded by the {@link Dictionary} object to retrieve
 * the corresponding integer native identifier.
 *
 * The class declares the {@link __toString()} method as virtual, it is essential that all
 * derived classes implement this method which should return the current object's <em>global
 * identifier string</em>. The global identifier of an object can be considered its
 * signature or unique identifier, although a global identifier not need to be unique; all
 * objects derived from this class, just as the Tag object described above, must feature a
 * global identifier, which may or may not coincide with their native identifier.
 *
 * The class declares a method, {@link reference()}, which returns the current object's
 * <i>reference</i>, this will generally be the value of the {@link kTAG_NID} offset. If the
 * offset is not set, the method will raise an exception. This method will be put to use by
 * derived classes: when providing an object to an offset expecting an object reference, by
 * using this method one can be assured the provided object does have a reference.
 *
 * Finally, the class features an object tracersal interface that can be used to verify and
 * cast the object's persistent data.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
abstract class OntologyObject extends ContainerObject
{
	/**
	 * Dictionary.
	 *
	 * This protected data member holds the data dictionary reference.
	 *
	 * @var Dictionary
	 */
	protected $mDictionary = NULL;

		

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
	 * This method should return the current object's global identifier.
	 *
	 * All derived concrete classes must implement this method.
	 *
	 * @access public
	 * @return string				The global identifier.
	 */
	abstract public function __toString();

	

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	dictionary																		*
	 *==================================================================================*/

	/**
	 * Set or return data dictionary
	 *
	 * This method can be used to set or retrieve the object's data dictionary, which is
	 * required by all derived objects to resolve offsets.
	 *
	 * You should set this data member as soon as the object has been instantiated, before
	 * adding offsets to it.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDictionary</b>: Data dictionary or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return current dictionary.
	 *		<li><tt>{@link Dictionary}</tt>: Set dictionary with provided value.
	 *	 </ul>
	 *	<li><b>$getOld</b>: This parameter is a boolean which if <tt>TRUE</tt> will return
	 *		the <i>old</i> dictionary when replacing, if <tt>FALSE</tt>, it will return the
	 *		current value.
	 * </ul>
	 *
	 * The method will raise an exception if the dictionary holds any other type except the
	 * above.
	 *
	 * @param mixed					$theDictionary		New dictionary or <tt>NULL</tt>.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return Dictionary			Object data dictionary.
	 *
	 * @throws Exception
	 */
	public function dictionary( $theDictionary = NULL, $getOld = FALSE )
	{
		//
		// Return dictionary
		//
		if( $theDictionary === NULL )
			return $this->mDictionary;												// ==>
		
		//
		// Save old value.
		//
		$save = $this->mDictionary;
		
		//
		// Set dictionary.
		//
		if( $theDictionary instanceof Dictionary )
		{
			//
			// Replace dictionary.
			//
			$this->mDictionary = $theDictionary;
			
			if( $getOld )
				return $save;														// ==>
			
			return $theDictionary;													// ==>
		
		} // provided a dictionary.
		
		throw new \Exception(
			"Unable to set dictionary: "
		   ."invalid or unsupported value." );									// !@! ==>
	
	} // dictionary.

	

/*=======================================================================================
 *																						*
 *							PUBLIC OBJECT REFERENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	reference																		*
	 *==================================================================================*/

	/**
	 * Return object reference
	 *
	 * This method will return the current object's reference. This value should uniquely
	 * identify the referenced object, making it easy to retrieve the object given this
	 * reference.
	 *
	 * In this class, and generally in all classes, the reference of an object is its native
	 * identifier, {@link kTAG_NID}.
	 *
	 * The method must raise an exception if the reference cannot be provided.
	 *
	 * @access public
	 * @return mixed				Object reference.
	 *
	 * @throws Exception
	 */
	public function reference()
	{
		//
		// Check native identifier.
		//
		if( $this->offsetExists( kTAG_NID ) )
			return $this->offsetGet( kTAG_NID );									// ==>
			
		throw new \Exception(
			"Unable to get object reference." );								// !@! ==>
	
	} // reference.

		

/*=======================================================================================
 *																						*
 *								STATIC OFFSET INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	InternalOffsets																	*
	 *==================================================================================*/

	/**
	 * Return internal offsets
	 *
	 * This method will return the current object list of internal offsets, these offsets
	 * are not defined in the data dictionary and are private to the object. This method
	 * is used to exclude these offsets from the default offset resolution workflow.
	 *
	 * In this class we return {@link kTAG_NID} and {@link kTAG_CLASS}, which all persistent
	 * objects share.
	 *
	 * @static
	 * @return array				List of internal offsets.
	 */
	static function InternalOffsets()			{	return array( kTAG_NID, kTAG_CLASS );	}

	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * This method will return the current object list of default offsets, these offsets
	 * represent the default offsets of the object, which means that all objects derived
	 * from this class may feature these offsets. This method is used to exclude these
	 * offsets from statistical procedures, such as {@link CollectOffsets()}, since it is
	 * implied that these offsets will be there.
	 *
	 * In this class we return an empty array.
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()									{	return Array();	}

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preOffsetExists																	*
	 *==================================================================================*/

	/**
	 * Handle offset before checking it
	 *
	 * In this class we resolve the offset.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> check offset, other, return.
	 *
	 * @uses resolveOffset()
	 */
	protected function preOffsetExists( &$theOffset )
	{
		//
		// Call parent method.
		//
		$ok = parent::preOffsetExists( $theOffset );
		if( $ok === NULL )
			$theOffset = (string) $this->resolveOffset( $theOffset );
		
		return $ok;																	// ==>
	
	} // preOffsetExists.

	 
	/*===================================================================================
	 *	preOffsetGet																	*
	 *==================================================================================*/

	/**
	 * Handle offset before getting it
	 *
	 * In this class we resolve the offset.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> get offset value, other, return.
	 *
	 * @uses resolveOffset()
	 */
	protected function preOffsetGet( &$theOffset )
	{
		//
		// Call parent method.
		//
		$ok = parent::preOffsetGet( $theOffset );
		if( $ok === NULL )
			$theOffset = (string) $this->resolveOffset( $theOffset );
		
		return $ok;																	// ==>
	
	} // preOffsetGet.

	 
	/*===================================================================================
	 *	preOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before setting it
	 *
	 * In this class we resolve the offset.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @uses resolveOffset()
	 */
	protected function preOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method.
		//
		$ok = parent::preOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
			$theOffset = (string) $this->resolveOffset( $theOffset, TRUE );
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 
	/*===================================================================================
	 *	preOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before deleting it
	 *
	 * In this class we resolve the offset.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> delete offset value, other, return.
	 *
	 * @uses resolveOffset()
	 */
	protected function preOffsetUnset( &$theOffset )
	{
		//
		// Call parent method.
		//
		$ok = parent::preOffsetUnset( $theOffset );
		if( $ok === NULL )
			$theOffset = (string) $this->resolveOffset( $theOffset );
		
		return $ok;																	// ==>
	
	} // preOffsetUnset.

	

/*=======================================================================================
 *																						*
 *							PROTECTED OFFSET RESOLUTION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveOffset																	*
	 *==================================================================================*/

	/**
	 * Resolve offset
	 *
	 * This method will resolve the provided offset into a {@link Tag} native
	 * identifier, this is done by using a {@link Dictionary} object stored in the current
	 * object's {@link $mDictionary} data member.
	 *
	 * If you provide an integer or a numeric string, the method will simply cast the value
	 * to an integer and return it.
	 *
	 * All other types of offsets, except those returned by the {@link InternalOffsets()}
	 * method, will be used to locate the tag native identifier using a {@link Dictionary}
	 * object stored in the current object's {@link $mDictionary} data member; if the
	 * provided offset cannot be resolved, the method will raise an exception if the second
	 * parameter is <tt>TRUE</tt>, or <tt>NULL</tt> if the second parameter is
	 * <tt>FALSE</tt>.
	 *
	 * The method will raise an exception if the tag cache is not set.
	 *
	 * @param mixed					$theOffset			Data offset.
	 * @param boolean				$doAssert			Assert offset tag reference.
	 *
	 * @access protected
	 * @return mixed				Resolved offset.
	 *
	 * @throws Exception
	 *
	 * @uses InternalOffsets()
	 */
	protected function resolveOffset( $theOffset, $doAssert = FALSE )
	{
		//
		// Handle numeric offsets.
		//
		if( is_int( $theOffset )
		 || ctype_digit( $theOffset ) )
			return (int) $theOffset;												// ==>
		
		//
		// Handle internal offsets.
		//
		if( in_array( $theOffset, $this->InternalOffsets() ) )
			return $theOffset;														// ==>
		
		//
		// Check cache.
		//
		if( ! ($this->mDictionary instanceof Dictionary) )
			throw new \Exception(
				"Missing data dictionary." );									// !@! ==>
		
		return $this->mDictionary->getIdentifier( $theOffset, $doAssert );			// ==>
	
	} // resolveOffset.

	 
	/*===================================================================================
	 *	castOffsetValue																	*
	 *==================================================================================*/

	/**
	 * Cast offset
	 *
	 * This method can be used to cast a value to the data type of its referred Tag.
	 *
	 * The method will first resolve the offset into a Tag and then it will use the Tag's
	 * data type to cast the value.
	 *
	 * The value will be cast only if the Tag has <em>one</em> data type, if the provided
	 * value is an array, the method will cast each element to that data type.
	 *
	 * If the method is unable to resolve the offset and the assert flag parameter is set,
	 * the method will raise an exception.
	 *
	 * This method will handle in-line the data types of a series of default tags, this is
	 * necessary when loading the default ontology for the first time: since there are no
	 * tags in the system yet, any attempt to resolve these tags would fail; if you plan on
	 * changing the data type of default tags, you should edit this method accordingly.
	 *
	 * @param reference				$theValue			Value to cast.
	 * @param mixed					$theOffset			Data offset.
	 * @param boolean				$doAssert			Assert offset tag reference.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function castOffsetValue( &$theValue, $theOffset, $doAssert = FALSE )
	{
		//
		// Resolve offset.
		//
		$offset_save = $theOffset;
		$theOffset = $this->resolveOffset( $theOffset, $doAssert );
		
		//
		// Handle default tags.
		//
		switch( $theOffset )
		{
			case kTAG_NID:
				return;																// ==>
			
			case kTAG_NAMESPACE:
			case kTAG_ID_LOCAL:
			case kTAG_ID_PERSISTENT:
			case kTAG_CLASS:
			case kTAG_CONN_PROTOCOL:
			case kTAG_CONN_HOST:
			case kTAG_CONN_USER:
			case kTAG_CONN_PASS:
			case kTAG_CONN_BASE:
			case kTAG_CONN_COLL:
				$theValue = (string) $theValue;
				return;																// ==>
		
			case kTAG_ID_SEQUENCE:
			case kTAG_CONN_PORT:
				$theValue = (int) $theValue;
				return;																// ==>
			
			case kTAG_TERMS:
			case kTAG_DATA_TYPE:
			case kTAG_DATA_KIND:
				break;
		}
		
		//
		// Resolve tag.
		//
		$tag = $this->mDictionary->getObject( $theOffset, $doAssert );
		
		//
		// Skip multiple types.
		//
		if( count( $tag[ kTAG_DATA_TYPE ] ) == 1 )
		{
			//
			// Get list indicator.
			//
			$is_list = ( ($tag[ kTAG_DATA_KIND ] !== NULL)
					  && in_array( kTYPE_LIST, $tag[ kTAG_DATA_KIND ] ) );
		
			//
			// Assert lists.
			//
			if( $is_list
			 && (! is_array( $theValue )) )
				throw new \Exception(
					"Unable to cast [$offset_save]: "
				   ."the list is not an array." );								// !@! ==>
		
			//
			// Parse by type.
			//
			$type = current( $tag[ kTAG_DATA_TYPE ] );
			switch( $type )
			{
				//
				// Structures.
				//
				case kTYPE_STRUCT:
				
					//
					// Handle struct list.
					//
					if( $is_list )
					{
						//
						// Iterate list.
						//
						$keys = array_keys( $theValue );
						foreach( $keys as $key )
						{
							//
							// Assert array.
							//
							if( ! is_array( $theValue ) )
								throw new \Exception(
									"Unable to cast [$offset_save]: "
								   ."the value is not an array." );				// !@! ==>
					
							//
							// Iterate structure.
							//
							$ref = & $theValue[ $key ];
							$offsets = array_keys( $ref );
							foreach( $offsets as $offset )
								$this->castOffsetValue(
									$ref[ $offset ], $offset, $doAssert );
						
						} // Iterating list.
					
					} // Struct list.
					
					//
					// Handle scalar struct.
					//
					else
					{
						//
						// Assert array.
						//
						if( ! is_array( $theValue ) )
							throw new \Exception(
								"Unable to cast [$offset_save]: "
							   ."the value is not an array." );					// !@! ==>
				
						//
						// Iterate structure.
						//
						$offsets = array_keys( $theValue );
						foreach( $offsets as $offset )
							$this->castOffsetValue(
								$theValue[ $offset ], $offset, $doAssert );
					
					} // Scalar struct.
					
					break;
			
				//
				// Category lists.
				//
				case kTYPE_LANGUAGE_STRINGS:
					
					//
					// Handle categories list.
					//
					if( $is_list )
					{
						//
						// Iterate list.
						//
						$indexes = array_keys( $theValue );
						foreach( $indexes as $index )
						{
							//
							// Assert array.
							//
							if( ! is_array( $theValue[ $index ] ) )
								throw new \Exception(
									"Unable to cast [$offset_save]: "
								   ."the value is not an array." );				// !@! ==>
					
							//
							// Iterate category.
							//
							$list = & $theValue[ $index ];
							$keys = array_keys( $list );
							foreach( $keys as $key )
							{
								//
								// Assert array.
								//
								if( ! is_array( $list[ $key ] ) )
									throw new \Exception(
										"Unable to cast [$offset_save]: "
									   ."the element is not an array." );		// !@! ==>
				
								//
								// Iterate category elements.
								//
								$ref = $list[ $key ];
								$offsets = array_keys( $ref );
								foreach( $offsets as $offset )
									$this->castOffsetValue(
										$ref[ $offset ], $offset, $doAssert );
							
							} // Iterating category elements.
						
						} // Iterating list.
					
					} // Category list.
					
					//
					// Handle category.
					//
					else
					{
						//
						// Assert array.
						//
						if( ! is_array( $theValue ) )
							throw new \Exception(
								"Unable to cast [$offset_save]: "
							   ."the value is not an array." );					// !@! ==>
				
						//
						// Iterate category.
						//
						$keys = array_keys( $theValue );
						foreach( $keys as $key )
						{
							//
							// Assert array.
							//
							if( ! is_array( $theValue ) )
								throw new \Exception(
									"Unable to cast [$offset_save]: "
								   ."the element is not an array." );			// !@! ==>
				
							//
							// Iterate category elements.
							//
							$ref = $theValue[ $key ];
							$offsets = array_keys( $ref );
							foreach( $offsets as $offset )
								$this->castOffsetValue(
									$ref[ $offset ], $offset, $doAssert );
						
						} // Iterating category elements.
					
					} // Scalar category.
					
					break;
			
				//
				// Enumerated sets.
				//
				case kTYPE_SET:
				
					//
					// Handle sets list.
					//
					if( $is_list )
					{
						//
						// Iterate list.
						//
						$indexes = array_keys( $theValue );
						foreach( $indexes as $index )
						{
							//
							// Assert array.
							//
							if( ! is_array( $theValue ) )
								throw new \Exception(
									"Unable to cast [$offset_save]: "
								   ."the set is not an array." );				// !@! ==>
					
							//
							// Iterate set.
							//
							$ref = & $theValue[ $index ];
							$keys = array_keys( $ref );
							foreach( $keys as $key )
								$ref[ $key ] = (string) $ref[ $key ];
						
						} // Iterating list.
					
					} // Sets list.
					
					//
					// Handle set.
					//
					else
					{
						//
						// Assert array.
						//
						if( ! is_array( $theValue ) )
							throw new \Exception(
								"Unable to cast [$offset_save]: "
							   ."the set is not an array." );					// !@! ==>
				
						//
						// Iterate set.
						//
						$keys = array_keys( $theValue );
						foreach( $keys as $key )
							$theValue[ $key ] = (string) $theValue[ $key ];
					
					} // Set.
					
					break;
				
				//
				// String scalars.
				//
				case kTYPE_STRING:
				case kTYPE_ENUM:
				case kTYPE_REF_TERM:
					
					//
					// Handle list.
					//
					if( $is_list )
					{
						//
						// Iterate list.
						//
						$indexes = array_keys( $theValue );
						foreach( $indexes as $index )
							$theValue[ $index ] = (string) $theValue[ $index ];
					
					} // List.
					
					//
					// handle scalar.
					//
					else
						$theValue = (string) $theValue;
					
					break;
				
				//
				// Integer scalars.
				//
				case kTYPE_INT:
				case kTYPE_REF_TAG:
				case kTYPE_REF_NODE:
					
					//
					// Handle list.
					//
					if( $is_list )
					{
						//
						// Iterate list.
						//
						$indexes = array_keys( $theValue );
						foreach( $indexes as $index )
							$theValue[ $index ] = (int) $theValue[ $index ];
					
					} // List.
					
					//
					// handle scalar.
					//
					else
						$theValue = (int) $theValue;
					
					break;
				
				//
				// Float scalars.
				//
				case kTYPE_FLOAT:
					
					//
					// Handle list.
					//
					if( $is_list )
					{
						//
						// Iterate list.
						//
						$indexes = array_keys( $theValue );
						foreach( $indexes as $index )
							$theValue[ $index ] = (double) $theValue[ $index ];
					
					} // List.
					
					//
					// handle scalar.
					//
					else
						$theValue = (double) $theValue;
					
					break;
			
				//
				// Skip these types.
				//
				case kTYPE_MIXED:
				case kTYPE_ARRAY:

					break;
				
				//
				// Unknown types.
				//
				default:
					throw new \Exception(
						"Unable to cast [$offset_save]: "
					   ."Unknown or unsupported type [$type]." );				// !@! ==>
			
			} // Parsed type.
		
		} // One data type.
	
	} // castOffsetValue.

	

/*=======================================================================================
 *																						*
 *							PROTECTED OBJECT TRAVERSAL INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	traverse																		*
	 *==================================================================================*/

	/**
	 * Traverse object
	 *
	 * This method will traverse the object's offsets and apply the
	 * {@link traverseOffsets()} method to each traversed element.
	 *
	 * The method will return an array which collects all tag references used by the
	 * object's offsets, this will be an array in which the key represents the tag reference
	 * and the value will be the list of offsets in which the tag was used.
	 *
	 * For more information on what actions are performed during the traversal, please
	 * consult the reference of the {@link traverseOffsets()} method.
	 *
	 * @access protected
	 * @return array				Object offsets.
	 *
	 * @uses traverseOffsets()
	 */
	protected function traverse()
	{
		//
		// Init local storage.
		//
		$path = Array();
		$offsets = Array();
		$iterator = $this->getIterator();

		//
		// Traverse object.
		//
		iterator_apply( $iterator,
						array( $this, 'traverseOffsets' ),
						array( $iterator, & $offsets, & $path ) );
		
		return $offsets;															// ==>
	
	} // traverse.

		
	/*===================================================================================
	 *	traverseOffsets																	*
	 *==================================================================================*/

	/**
	 * Handle offset value
	 *
	 * This method will be called for each offset of the current object, its duty is to
	 * perform a series of operations on all the elements of the object by using a set of
	 * protected method that derived classes can overload to implement custom actions. These
	 * methods are:
	 *
	 * <ul>
	 *	<li><tt>{@link traverseResolveOffset()</tt>. This method will resolve the current
	 *		offset and return in the reference parameters the current offset's data type and
	 *		kind.
	 *	<li><tt>{@link traverseCollectOffset()</tt>. This method will populate the
	 *		<tt>$theOffsets</tt> parameter, this is an array in which the keys represent
	 *		the object offset tag references and the value represents the list of offsets
	 *		in which that tag was used..
	 *	<li><tt>{@link traverseVerifyStructure()</tt>. This method will check whether the
	 *		structure of the current offset is correct.
	 *	<li><tt>{@link traverseVerifyValue()</tt>. This method will be called if the current
	 *		offset is neither a list nor a structure, its duty is to verify the value of the
	 *		current offset, if this is neither a list nor a structure.
	 *	<li><tt>{@link traverseCastValue()</tt>. This method will be called if the current
	 *		offset is neither a list nor a structure, its duty is to cast the value of the
	 *		current offset to the data type indicated by the tag referenced by the offset.
	 * </ul>
	 *
	 * Derived classes should overload the above methods and not the current one.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter is the iterator pointing to the current
	 *		traversal element.
	 *	<li><b>$theOffsets</b>: This reference parameter will receive the offsets list,
	 *		which is an array indexed by the offset tag reference with as value the list of
	 *		offsets in which the tag was used.
	 *	<li><b>$thePath</b>: This reference parameter will receive the current offset path
	 *		in the form of an array; only offsets will be set, array element indexes will
	 *		not be included.
	 * </ul>
	 *
	 * This method is used by the PHP {@link iterator_apply()} method, which means that it
	 * should return <tt>TRUE</tt> to continue the object traversal, or <tt>FALSE</tt> to
	 * stop it.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theOffsets			Receives the offsets list.
	 * @param reference				$thePath			Receives the current path.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt>, or <tt>FALSE</tt> to stop the traversal.
	 *
	 * @uses traverseResolveOffset()
	 * @uses traverseCollectOffset()
	 * @uses traverseVerifyStructure()
	 * @uses traverseHandleValue()
	 */
	protected function traverseOffsets( \Iterator $theIterator, &$theOffsets, &$thePath )
	{
		//
		// Init local storage.
		//
		$key = $theIterator->key();
		$value = $theIterator->current();
		
		//
		// Collect offset and types.
		//
		$this->traverseResolveOffset( $theIterator, $type, $kind );
		
		//
		// Collect offset.
		//
		$offset = $this->traverseCollectOffset( $theIterator, $thePath );
		
		//
		// Add offset.
		//
		if( ! in_array( kTYPE_STRUCT, $type ) )
			$this->traverseAddOffset( $theIterator, $theOffsets, $offset );
		
		//
		// Verify structure.
		//
		$is_list = $this->traverseVerifyStructure( $theIterator, $type, $kind, $offset );
		
		//
		// Handle scalar offsets.
		//
		if( ! $is_list )
			$this->traverseHandleValue( $theIterator, $type, $kind, $offset );
		
		//
		// Handle structure offsets.
		//
		else
		{
			//
			// Save list or structure.
			//
			$list = new \ArrayObject( $value );
		
			//
			// Handle structure.
			//
			if( in_array( kTYPE_STRUCT, $type ) )
			{
				//
				// Handle structure lists.
				//
				if( in_array( kTYPE_LIST, $kind ) )
				{
					//
					// Iterate list.
					//
					foreach( $list as $idx => $struct )
					{
						//
						// Traverse structure.
						//
						$struct = new \ArrayObject( $struct );
						$iterator = $struct->getIterator();
						iterator_apply( $iterator,
										array( $this, 'traverseOffsets' ),
										array( $iterator, & $theOffsets, & $thePath ) );
		
						//
						// Update structure.
						//
						if( $struct->count() )
							$list[ $idx ] = $struct->getArrayCopy();
				
					} // Iterating list.
			
				} // List of structures.
			
				//
				// Handle scalar structure.
				//
				else
				{
					//
					// Traverse structure.
					//
					$iterator = $list->getIterator();
					iterator_apply( $iterator,
									array( $this, 'traverseOffsets' ),
									array( $iterator, & $theOffsets, & $thePath ) );
			
				} // Scalar structure.
		
			} // Structure.
			
			//
			// Handle list of scalars.
			//
			else
			{
				//
				// Iterate scalar list.
				//
				$iterator = $list->getIterator();
				iterator_apply( $iterator,
								array( $this, 'traverseHandleValue' ),
								array( $iterator,
									   & $type, & $kind,
									   $offset ) );
			
			} // List of scalars.

			//
			// Update current iterator.
			//
			$theIterator->offsetSet( $key, $list->getArrayCopy() );
		
		} // Structured offset.
		
		//
		// Pop path.
		//
		array_pop( $thePath );
		
		return TRUE;																// ==>
	
	} // traverseOffsets.

		
	/*===================================================================================
	 *	traverseResolveOffset															*
	 *==================================================================================*/

	/**
	 * Resolve offset type
	 *
	 * This method will resolve the current offset into a {@link Tag} object and return in
	 * the provided reference parameters the data type and kind; this is done by using the
	 * {@link Dictionary} object stored in the current object's {@link $mDictionary} data
	 * member.
	 *
	 * The method expects the following parameters: 
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter is the iterator pointing to the current
	 *		traversal element, this element must be an offset and not an offset sub-element.
	 *	<li><b>$theType</b>: This parameter will receive the data type of the referenced
	 *		tag, if the tag could not be resolved, the parameter will hold an empty array.
	 *	<li><b>$theKind</b>: This parameter will receive the data kind of the referenced
	 *		tag, if the tag could not be resolved, or if the tag has no data kind, the
	 *		parameter will hold an empty array.
	 * </ul>
	 *
	 * The method will raise an exception if the current offset is not an integer, a numeric
	 * string or part of the internal offsets.
	 *
	 * The method will return <tt>TRUE</tt> if the tag was resolved; <tt>FALSE</tt> if the
	 * tag was not resolved and <tt>NULL</tt> if the offset is internal.
	 *
	 * Derived classes should overload this method 
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Receives data type.
	 * @param reference				$theKind			Receives data kind.
	 *
	 * @access protected
	 * @return mixed				<tt>TRUE</tt>, <tt>FALSE</tt> or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	protected function traverseResolveOffset( \Iterator $theIterator,
													   &$theType,
													   &$theKind )
	{
		//
		// Init parameters.
		//
		$key = $theIterator->key();
		$theType = Array();
		$theKind = Array();
		
		//
		// Skip internal tags.
		//
		if( ! in_array( (string) $theIterator->key(), static::InternalOffsets() ) )
		{
			//
			// Check cache.
			//
			if( ! ($this->mDictionary instanceof Dictionary) )
				throw new \Exception(
					"Missing data dictionary." );								// !@! ==>
	
			//
			// Handle numeric offsets.
			//
			if( is_int( $key )
			 || ctype_digit( $key ) )
			{
				//
				// Resolve tag.
				//
				$tag = $this->mDictionary->getObject( (int) $key, TRUE );
				if( $tag !== NULL )
				{
					//
					// Get data type.
					//
					$theType = $tag[ kTAG_DATA_TYPE ];
					
					//
					// Get data kind.
					//
					if( array_key_exists( kTAG_DATA_KIND, $tag ) )
						$theKind = $tag[ kTAG_KIND ];
					
					return TRUE;													// ==>
				
				} // Found tag.
				
				return FALSE;														// ==>
		
			} // Numeric offset.
			
			throw new \Exception(
				"Invalid tag reference [$key]." );								// !@! ==>
		
		} // Not an internal offset.
		
		return NULL;																// ==>
	
	} // traverseResolveOffset.


	/*===================================================================================
	 *	traverseCollectOffset															*
	 *==================================================================================*/

	/**
	 * Collect offset
	 *
	 * This method will be called for each offset of the current object structure, its duty
	 * is to collect the offsets in the path and return the offset string.
	 *
	 * The method expects the following parameters: 
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter is the iterator pointing to the current
	 *		traversal element, this element must be an offset and not an offset sub-element.
	 *	<li><b>$thePath</b>: This reference parameter will receive the current offset path.
	 * </ul>
	 *
	 * The method will return the current offset string, which is composed of the
	 * concatenation of all offsets at different levels separated by a point.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$thePath			Receives the current path.
	 *
	 * @access protected
	 * @return string				Current offset.
	 */
	protected function traverseCollectOffset( \Iterator $theIterator,
													   &$thePath )
	{
		//
		// Init parameters.
		//
		$key = $theIterator->key();
		
		//
		// Set path tag.
		//
		$thePath[] = $key;
		
		return implode( '.', $thePath );											// ==>
	
	} // traverseCollectOffset.


	/*===================================================================================
	 *	traverseAddOffset																*
	 *==================================================================================*/

	/**
	 * Add offset
	 *
	 * This method will be called for each offset of the current object structure, its duty
	 * is to add the current offset string to the list of tag offsets.
	 *
	 * The method expects the following parameters: 
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter is the iterator pointing to the current
	 *		traversal element, this element must be an offset and not an offset sub-element.
	 *	<li><b>$theOffsets</b>: This reference parameter will receive the offsets list,
	 *		which is an array indexed by the offset tag reference with as value the list of
	 *		offsets in which the tag was used.
	 *	<li><b>$theOffset</b>: This parameter holds the offset string.
	 * </ul>
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theOffsets			Receives the offsets list.
	 * @param string				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return string				Current offset.
	 */
	protected function traverseAddOffset( \Iterator $theIterator,
												   &$theOffsets,
												    $theOffset )
	{
		//
		// Init local storage.
		//
		$key = $theIterator->key();

		//
		// Add offset to offsets.
		//
		if( array_key_exists( $key, $theOffsets ) )
		{
			$ref = & $theOffsets[ $key ];
			if( ! in_array( $theOffset, $ref ) )
				$ref[] = $theOffset;
		}
		else
			$theOffsets[ $key ] = array( $theOffset );
	
	} // traverseAddOffset.

	 
	/*===================================================================================
	 *	traverseVerifyStructure															*
	 *==================================================================================*/

	/**
	 * Verify offset
	 *
	 * This method should verify that the current element of the provided iterator has the
	 * correct structure and content.
	 *
	 * In this class we verify whether lists, structures and structured types are indeed
	 * arrays and raise an exception if that is not the case. Note that we only check
	 * structured data types if the offset has a single data type.
	 *
	 * The method will return <tt>TRUE</tt> if the offset value is either a structure or a
	 * list, and <tt>FALSE</tt> if the offset value is a scalar data type; in derived
	 * classes you can call the parent method and perform custom checks if the parent method
	 * returned <tt>FALSE</tt>.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Data type.
	 * @param reference				$theKind			Data kind.
	 * @param string				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> if structure or list.
	 *
	 * @throws Exception
	 */
	protected function traverseVerifyStructure( \Iterator $theIterator,
														 &$theType,
														 &$theKind,
														  $theOffset )
	{
		//
		// Assert lists.
		//
		if( in_array( kTYPE_LIST, $theKind ) )
		{
			//
			// Verify list.
			//
			if( ! is_array( $theIterator->current() ) )
				throw new \Exception(
					"Invalid offset list value in [$theOffset]: "
				   ."the value is not an array." );								// !@! ==>
			
			return TRUE;															// ==>
		
		} // List.
		
		//
		// Assert structure.
		// Note that if it is a structure,
		// it cannot have any other data type.
		//
		if( in_array( kTYPE_STRUCT, $theType ) )
		{
			//
			// Verify structure.
			//
			if( ! is_array( $theIterator->current() ) )
				throw new \Exception(
					"Invalid offset structure value in [$theOffset]: "
				   ."the value is not an array." );								// !@! ==>
			
			return TRUE;															// ==>
		
		} // Is a structure.
		
		return FALSE;																// ==>
	
	} // traverseVerifyStructure.

	 
	/*===================================================================================
	 *	traverseHandleValue																*
	 *==================================================================================*/

	/**
	 * Handle value
	 *
	 * This method should handle the current offset value, it should verify if the value
	 * is correct and cast the value to the provided data type.
	 *
	 * This method should only be called for scalar offset values, list scalars should call
	 * this method for each element.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Data type.
	 * @param reference				$theKind			Data kind.
	 * @param string				$theOffset			Current offset.
	 *
	 * @access protected
	 *
	 * @uses traverseVerifyValue()
	 * @uses traverseCastValue()
	 */
	protected function traverseHandleValue( \Iterator $theIterator,
													 &$theType,
													 &$theKind,
													  $theOffset )
	{
		//
		// Verify value.
		//
		$this->traverseVerifyValue( $theIterator, $theType, $theKind, $theOffset );
		
		//
		// Cast value.
		//
		$this->traverseCastValue( $theIterator, $theType, $theKind, $theOffset );
		
		return TRUE;																// ==>
	
	} // traverseHandleValue.

	 
	/*===================================================================================
	 *	traverseVerifyValue																*
	 *==================================================================================*/

	/**
	 * Verify offset value
	 *
	 * This method should verify the current offset value, this method is called by the
	 * {@link traverseVerifyStructure()} method if the current offset is not a structure or a list.
	 *
	 * In this class we assert that structured types are arrays if there is only one offset
	 * type.
	 *
	 * The method will return <tt>NULL</tt> if the offset has more than one type,
	 * <tt>TRUE</tt> if the value type was verified and <tt>FALSE</tt> if it was not
	 * verified.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Data type.
	 * @param reference				$theKind			Data kind.
	 * @param string				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 */
	protected function traverseVerifyValue( \Iterator $theIterator,
													 &$theType,
													 &$theKind,
													  $theOffset )
	{
		//
		// Verify single data types.
		//
		if( count( $theType ) == 1 )
		{
			//
			// Assert array values.
			//
			switch( $tmp = current( $theType ) )
			{
				case kTYPE_ARRAY:
				case kTYPE_SET:
				case kTYPE_LANGUAGE_STRINGS:
					if( ! is_array( $theIterator->current() ) )
						throw new \Exception(
							"Invalid offset value in [$theOffset]: "
						   ."the value is not an array." );						// !@! ==>
					
					return TRUE;													// ==>
			
			} // Parsed data type.
			
			return FALSE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // traverseVerifyValue.

	 
	/*===================================================================================
	 *	traverseCastValue																	*
	 *==================================================================================*/

	/**
	 * Cast offset
	 *
	 * This method should cast the current element of the provided iterator to the correct
	 * data type. This method can also be used to verify structured type elements.
	 *
	 * The method will return <tt>TRUE</tt> if the value was cast, <tt>FALSE</tt> if not and
	 * <tt>NULL</tt> if the offset has more than one data type.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Data type.
	 * @param reference				$theKind			Data kind.
	 * @param string				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 */
	protected function traverseCastValue( \Iterator $theIterator,
												   &$theType,
												   &$theKind,
													$theOffset )
	{
		//
		// Cast only single types.
		//
		if( count( $theType ) == 1 )
		{
			//
			// Init local storage.
			//
			$key = $theIterator->key();
			$value = $theIterator->current();
			
			//
			// Parse by type.
			//
			switch( current( $theType ) )
			{
				//
				// Strings.
				//
				case kTYPE_STRING:
				case kTYPE_ENUM:
				case kTYPE_REF_TERM:
				case kTYPE_REF_TAG:
				case kTYPE_REF_EDGE:
				case kTYPE_REF_ENTITY:
				case kTYPE_REF_UNIT:
					$theIterator->offsetSet( $key, (string) $value );
					return TRUE;													// ==>
				
				//
				// Integers.
				//
				case kTYPE_INT:
				case kTYPE_REF_NODE:
					$theIterator->offsetSet( $key, (int) $value );
					return TRUE;													// ==>
		
				//
				// Floats.
				//
				case kTYPE_FLOAT:
					$theIterator->offsetSet( $key, (double) $value );
					return TRUE;													// ==>
		
				//
				// Enumerated sets.
				//
				case kTYPE_SET:
					// Iterate set.
					$idxs = array_keys( $value );
					foreach( $idxs as $idx )
						$value[ $idx ] = (string) $value[ $idx ];
					// Set value.
					$theIterator->offsetSet( $key, $value );
					return TRUE;													// ==>
		
				case kTYPE_LANGUAGE_STRINGS:
					// Iterate language strings.
					$idxs = array_keys( $value );
					foreach( $idxs as $idx )
					{
						// Check if array.
						if( is_array( $value[ $idx ] ) )
						{
							// Check text element.
							if( array_key_exists( kTAG_TEXT, $value[ $idx ] ) )
								$value[ $idx ][ kTAG_TEXT ]
									= (string) $value[ $idx ][ kTAG_TEXT ];
							// Missing text element.
							else
								throw new \Exception(
									"Invalid offset value element in [$theOffset]: "
								   ."missing text item." );						// !@! ==>
							// Cast language.
							if( array_key_exists( kTAG_LANGUAGE, $value[ $idx ] ) )
								$value[ $idx ][ kTAG_LANGUAGE ]
									= (string) $value[ $idx ][ kTAG_LANGUAGE ];
						}
						// Invalid format.
						else
							throw new \Exception(
								"Invalid offset value element in [$theOffset]: "
							   ."the value is not an array." );					// !@! ==>
					}
					// Set value.
					$theIterator->offsetSet( $key, $value );
					return TRUE;													// ==>
		
			} // Parsed type.
			
			return FALSE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // traverseCastValue.

	 

} // class OntologyObject.


?>
