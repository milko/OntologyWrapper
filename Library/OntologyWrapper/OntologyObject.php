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
	static function InternalOffsets()
	{
		return array( kTAG_NID, kTAG_CLASS, kTAG_SHAPE_TYPE, kTAG_SHAPE_GEOMETRY );	// ==>
	
	} // InternalOffsets.

	 
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
		
		return $this->mDictionary->getSerial( $theOffset, $doAssert );			// ==>
	
	} // resolveOffset.

	

/*=======================================================================================
 *																						*
 *								PROTECTED OFFSET UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getOffsetTypes																	*
	 *==================================================================================*/

	/**
	 * Resolve offset types
	 *
	 * This method will resolve the current offset into a {@link Tag} object and return in
	 * the provided reference parameters the data type and kind; this is done by using the
	 * {@link Dictionary} object stored in the current object's {@link $mDictionary} data
	 * member.
	 *
	 * The method expects the following parameters: 
	 *
	 * <ul>
	 *	<li><b>$theOffset</b>: This parameter represents the current offset tag reference.
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
	 * The method will return <tt>TRUE</tt> if the tag was resolved and <tt>NULL</tt> if the
	 * offset is internal.
	 *
	 * @param string				$theOffset			Current offset.
	 * @param reference				$theType			Receives data type.
	 * @param reference				$theKind			Receives data kind.
	 *
	 * @access protected
	 * @return mixed				<tt>TRUE</tt> if the tag was resolved.
	 *
	 * @throws Exception
	 */
	protected function getOffsetTypes( $theOffset, &$theType, &$theKind )
	{
		//
		// Init parameters.
		//
		$theType = Array();
		$theKind = Array();
		
		//
		// Skip internal tags.
		//
		if( ! in_array( (string) $theOffset, static::InternalOffsets() ) )
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
			if( is_int( $theOffset )
			 || ctype_digit( $theOffset ) )
			{
				//
				// Resolve tag.
				//
				$tag = $this->mDictionary->getObject( (int) $theOffset, TRUE );
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
						$theKind = $tag[ kTAG_DATA_KIND ];
					
					return TRUE;													// ==>
				
				} // Found tag.
		
			} // Numeric offset.
			
			throw new \Exception(
				"Invalid tag reference [$theOffset]." );						// !@! ==>
		
		} // Not an internal offset.
		
		return NULL;																// ==>
	
	} // getOffsetTypes.

	 

} // class OntologyObject.


?>
