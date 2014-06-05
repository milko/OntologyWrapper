<?php

/**
 * DictionaryObject.php
 *
 * This file contains the definition of the {@link DictionaryObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ContainerObject;

/*=======================================================================================
 *																						*
 *									DictionaryObject.php								*
 *																						*
 *======================================================================================*/

/**
 * Dictionary object
 *
 * A <em>dictionary</em> is used to <em>identify</em> and <em>document</em> data properties.
 * The dictionary stores a set of objects called <em>tags</em>, which contain all the
 * necessary information needed to understand what a specific data property is, how it can
 * be used, what data types it can take and all the information necessary to manage it.
 *
 * Tags are identified in two ways: with their <em>persistent identifier</em>, which is a
 * <em>string</em> that uniquely identifies the tag across all dictionaries, and with their
 * <em>serial identifier</em>, which is an <em>integer</em> that uniquely identifies the tag
 * only within the current dictionary.
 *
 * All persistent data properties of all objects of this library are <em>serial identifiers
 * of tag objects</em>. This dictionary provides the abiliti to convert among persistent and
 * serial identifiers, and it provides the ability to retrieve tag objects.
 *
 * The dictionary is essentially a cache that allows fast access to the tag elements of the
 * ontology, the dictionary allows retrieving the serial identifier given a persistent
 * identifier, or retrieve a tag object given a serial identifier.
 *
 * Derived classes must implement the constructor and the protected dictionary interface.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/02/2014
 */
abstract class DictionaryObject extends ContainerObject
{
	/**
	 * Dictionary identifier.
	 *
	 * This data member holds the dictionary identifier.
	 *
	 * @var string
	 */
	protected $mPid = NULL;

	/**
	 * Cache connection.
	 *
	 * This data member holds the dictionary cache connection.
	 *
	 * @var Memcached
	 */
	protected $mCache = NULL;

		

/*=======================================================================================
 *																						*
 *							PUBLIC CACHE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	cache																			*
	 *==================================================================================*/

	/**
	 * Return cache
	 *
	 * This method will return the current cache.
	 *
	 * @access public
	 */
	public function cache()										{	return $this->mCache;	}

		

/*=======================================================================================
 *																						*
 *							PUBLIC TAG MANAGEMENT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setTag																			*
	 *==================================================================================*/

	/**
	 * Set tag
	 *
	 * This method should commit the provided tag, both as an identifier and as an object.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTag</b>: Tag object, if derived from an array object, it will be
	 *		converted into an array, before it is set into the dictionary.
	 *	<li><b>$theLife</b>: Lifetime of the dictionary entry in seconds, 0 means permanent.
	 * </ul>
	 *
	 * @param mixed					$theTag				Tag object.
	 * @param integer				$theLife			Element lifetime.
	 *
	 * @access public
	 +
	 + @throws Exception
	 *
	 * @uses setEntry()
	 */
	public function setTag( $theTag, $theLife = 0 )
	{
		//
		// Normalise object.
		//
		if( $theTag instanceof \ArrayObject )
			$theTag = $theTag->getArrayCopy();
		
		//
		// Check if array.
		//
		if( ! is_array( $theTag ) )
			throw new \Exception(
				"Invalid or unsupported dictionary tag entry." );				// !@! ==>
		
		//
		// Check serial identifier.
		//
		if( ! array_key_exists( kTAG_ID_SEQUENCE, $theTag ) )
			throw new \Exception(
				"Missing tag serial identifier." );								// !@! ==>
		
		//
		// Check persistent identifier.
		//
		if( ! array_key_exists( kTAG_NID, $theTag ) )
			throw new \Exception(
				"Missing tag persistent identifier." );							// !@! ==>
		
		//
		// Set identifiers.
		//
		$this->setEntry( (string) $theTag[ kTAG_NID ],			// Persistent identifier.
						 (int)	  $theTag[ kTAG_ID_SEQUENCE ],	// Serial identifier.
						 		  $theLife );
		
		//
		// Set object.
		//
		$this->setEntry( (int)	  $theTag[ kTAG_ID_SEQUENCE ],	// Serial identifier.
						 		  $theTag,						// Object.
						 		  $theLife );
	
	} // setTag.

	 
	/*===================================================================================
	 *	setTagsByIterator																*
	 *==================================================================================*/

	/**
	 * Set tags by iterator
	 *
	 * This method should commit the provided tags, both as identifiers and as an objects.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: Tags iterator, typically from a database query, the iterator
	 *		must have as key the tag native identifier
	 *	<li><b>$theLife</b>: Lifetime of the dictionary entry in seconds, 0 means permanent.
	 * </ul>
	 *
	 * @param IteratorObject		$theTags			Tag iterator.
	 * @param integer				$theLife			Elements lifetime.
	 *
	 * @access public
	 */
	public function setTagsByIterator( IteratorObject $theTags, $theLife = 0 )
	{
		//
		// Set iterator key to tag sequence number.
		//
		$theTags->setKeyOffset( kTAG_ID_SEQUENCE );
		
		//
		// Set objects.
		//
		$this->setEntriesByIterator( $theTags, $theLife );
		
		//
		// Set iterator key to tag native identifier.
		//
		$theTags->setKeyOffset( kTAG_NID );
		
		//
		// Load sequences.
		//
		$serials = Array();
		foreach( $theTags as $id => $tag )
			$serials[ $id ] = (int) $tag[ kTAG_ID_SEQUENCE ];
	
		//
		// Set sequences.
		//
		$this->setEntriesByArray( $serials, $theLife );
		
	} // setTagsByIterator.

	 
	/*===================================================================================
	 *	getSerial																		*
	 *==================================================================================*/

	/**
	 * Get serial number
	 *
	 * This method should return the serial identifier corresponding to the provided
	 * persistent identifier.
	 *
	 * The second parameter represents a boolean flag: if <tt>TRUE</tt> and the provided
	 * identifier is not matched, the method will raise an exception; if <tt>FALSE</tt>, the
	 * method will return <tt>NULL</tt> on a mismatch. By default this option is
	 * <tt>TRUE</tt>.
	 *
	 * @param string				$theIdentifier		Persistent identifier.
	 * @param boolean				$doAssert			If <tt>TRUE</tt> assert match.
	 *
	 * @access public
	 * @return integer				Sequence number or <tt>NULL</tt>.
	 +
	 + @throws Exception
	 *
	 * @uses getEntry()
	 */
	public function getSerial( $theIdentifier, $doAssert = TRUE )
	{
		//
		// Match offset.
		//
		$id = $this->getEntry( (string) $theIdentifier, $doAssert );
		if( $id !== NULL )
			return (int) $id;														// ==>
		
		//
		// Assert.
		//
		if( $doAssert )
			throw new \Exception(
				"Unmatched dictionary identifier [$theIdentifier]." );			// !@! ==>
		
		return NULL;																// ==>
		
	} // getSerial.

	 
	/*===================================================================================
	 *	getObject																		*
	 *==================================================================================*/

	/**
	 * Get object
	 *
	 * This method should return the tag object corresponding to the provided serial
	 * identifier.
	 *
	 * The second parameter represents a boolean flag: if <tt>TRUE</tt> and the provided
	 * identifier is not matched, the method will raise an exception; if <tt>FALSE</tt>, the
	 * method will return <tt>NULL</tt> on a mismatch. By default this option is
	 * <tt>TRUE</tt>.
	 *
	 * @param integer				$theIdentifier		Serial identifier.
	 * @param boolean				$doAssert			If <tt>TRUE</tt> assert match.
	 *
	 * @access public
	 * @return mixed				Tag object array or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses getEntry()
	 */
	public function getObject( $theIdentifier, $doAssert = TRUE )
	{
		//
		// Match offset.
		//
		$object = $this->getEntry( (int) $theIdentifier, $doAssert );
		if( $object !== NULL )
			return $object;															// ==>
		
		//
		// Assert.
		//
		if( $doAssert )
			throw new \Exception(
				"Unmatched dictionary identifier [$theIdentifier]." );			// !@! ==>
		
		return NULL;																// ==>
		
	} // getObject.

	 
	/*===================================================================================
	 *	getTypes																		*
	 *==================================================================================*/

	/**
	 * Get tag types
	 *
	 * This method should return the tag data type, kind, range and pattern in the provided
	 * reference parameters, the method expects the identifier to be a tag sequence number,
	 * it will be cast to an integer.
	 *
	 * The last parameter represents a boolean flag: if <tt>TRUE</tt> and the provided
	 * identifier is not matched, the method will raise an exception; if <tt>FALSE</tt>, the
	 * method will set the data type to <tt>NULL</tt>, the data kind to an empty array, the
	 * range to an empty array and the pattern to <tt>NULL</tt>.
	 *
	 * @param integer				$theIdentifier		Serial identifier.
	 * @param string				$theType			Receives data type.
	 * @param array					$theKind			Receives data kind.
	 * @param mixed					$theMin				Receives minimum data range.
	 * @param mixed					$theMax				Receives maximum data range.
	 * @param string				$thePattern			Receives data pattern.
	 * @param boolean				$doAssert			If <tt>TRUE</tt> assert match.
	 *
	 * @access public
	 *
	 * @throws Exception
	 * @return boolean				<tt>TRUE</tt> means the tag was found.
	 *
	 * @uses getEntry()
	 */
	public function getTypes( $theIdentifier, &$theType, &$theKind,
											  &$theMin, &$theMax, &$thePattern,
											  $doAssert = TRUE )
	{
		//
		// Init parameters.
		//
		$theType = NULL;
		$theKind = Array();
		$theMin = NULL;
		$theMax = NULL;
		$thePattern = NULL;
		
		//
		// Match offset.
		//
		$object = $this->getEntry( (int) $theIdentifier, $doAssert );
		if( $object !== NULL )
		{
			//
			// Set parameters.
			//
			$theType = $object[ kTAG_DATA_TYPE ];
			if( array_key_exists( kTAG_DATA_KIND, $object ) )
				$theKind = $object[ kTAG_DATA_KIND ];
			if( array_key_exists( kTAG_MIN_RANGE, $object ) )
				$theMin = $object[ kTAG_MIN_RANGE ];
			if( array_key_exists( kTAG_MAX_RANGE, $object ) )
				$theMax = $object[ kTAG_MAX_RANGE ];
			if( array_key_exists( kTAG_PATTERN, $object ) )
				$thePattern = $object[ kTAG_PATTERN ];
			
			return TRUE;															// ==>
		
		} // Found tag.
		
		//
		// Assert.
		//
		elseif( $doAssert )
			throw new \Exception(
				"Unmatched dictionary identifier [$theIdentifier]." );			// !@! ==>
		
		return FALSE;																// ==>
		
	} // getTypes.

	 
	/*===================================================================================
	 *	getTagOffsets																	*
	 *==================================================================================*/

	/**
	 * Get dictionary tag offsets
	 *
	 * This method should return the list of tag sequence numbers corresponding to the
	 * tag properties that will be stored in the dictionary.
	 *
	 * By default we store:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_NID}</tt>: Native identifier.
	 *	<li><tt>{@link kTAG_ID_SEQUENCE}</tt>: Sequence number.
	 *	<li><tt>{@link kTAG_DATA_TYPE}</tt>: Data type.
	 *	<li><tt>{@link kTAG_DATA_KIND}</tt>: Data kind.
	 *	<li><tt>{@link kTAG_TAG_STRUCT}</tt>: Container structure.
	 *	<li><tt>{@link kTAG_TAG_STRUCT_IDX}</tt>: Container structure list index.
	 *	<li><tt>{@link kTAG_MIN_RANGE}</tt>: Range minimum bound.
	 *	<li><tt>{@link kTAG_MAX_RANGE}</tt>: Range maximum bound.
	 *	<li><tt>{@link kTAG_PATTERN}</tt>: Value pattern.
	 * </ul>
	 *
	 * Other properties are either not needed for this purpose, or they are modified
	 * frequently, making reading from the database slower, but safer.
	 *
	 * The method will return an array suited to be used for querying with a field
	 * selection, the tag offsets are the key and a boolean is the value.
	 *
	 * @access public
	 * @return array				List of tag offsets.
	 */
	public function getTagOffsets()
	{
		return array( kTAG_NID => TRUE, kTAG_ID_SEQUENCE => TRUE,
					  kTAG_DATA_TYPE => TRUE, kTAG_DATA_KIND => TRUE,
					  kTAG_TAG_STRUCT => TRUE, kTAG_TAG_STRUCT_IDX => TRUE,
					  kTAG_MIN_RANGE => TRUE, kTAG_MAX_RANGE => TRUE,
					  kTAG_PATTERN => TRUE );										// ==>
	
	} // getTagOffsets.

	 
	/*===================================================================================
	 *	delTag																			*
	 *==================================================================================*/

	/**
	 * Delete tag
	 *
	 * This method should delete a tag entry corresponding to the provided persistent or
	 * serial identifier. This means that the method will delete both the identifier and the
	 * object entries.
	 *
	 * Note that an integer identifier is assumed to be the serial identifier and anything
	 * else will be cast to string and assumed to be the persistent identifier.
	 *
	 * The second parameter rperesents a boolean flag: if <tt>TRUE</tt> and the provided
	 * identifier is not matched, the method will raise an exception. By default this option
	 * is <tt>FALSE</tt>.
	 *
	 * @param mixed					$theIdentifier		Persistent or serial identifier.
	 * @param boolean				$doAssert			If <tt>TRUE</tt> assert match.
	 *
	 * @access public
	 *
	 * @uses delEntry()
	 * @return boolean				<tt>TRUE</tt> deleted, <tt>FALSE</tt> not found.
	 */
	public function delTag( $theIdentifier, $doAssert = FALSE )
	{
		//
		// Handle persistent identifier.
		//
		if( is_int( $theIdentifier ) )
		{
			//
			// Get tag.
			//
			$tag = $this->getObject( $theIdentifier, $doAssert );
			if( $tag === NULL )
				return FALSE;														// ==>
			
			//
			// Save serial identifier.
			//
			$id_serial = $theIdentifier;
			
			//
			// Save persistent identifier.
			//
			$id_persist = $tag[ kTAG_NID ];
		
		} // Provided serial identifier.
		
		//
		// Handle persistent identifier.
		//
		else
		{
			//
			// Save persistent identifier.
			//
			$id_persist = (string) $theIdentifier;
		
			//
			// Get serial identifier.
			//
			$id_serial = $this->getSerial( $id_persist, $doAssert );
			if( $id_serial === NULL )
				return FALSE;														// ==>
			
		} // Provided persistent identifier.
		
		//
		// Delete identifier.
		//
		if( ! $this->delEntry( $id_persist ) )
		{
			//
			// Assert.
			//
			if( $doAssert )
				throw new \Exception(
					"Unmatched persistent dictionary identifier "
				   ."[$id_persist]." );											// !@! ==>
		
		} // Not matched.
		
		//
		// Delete object.
		//
		if( ! $this->delEntry( $id_serial ) )
		{
			//
			// Assert.
			//
			if( $doAssert )
				throw new \Exception(
					"Unmatched serial dictionary identifier "
				   ."[$id_serial]." );											// !@! ==>
		
		} // Not matched.
	
	} // delTag.

		

/*=======================================================================================
 *																						*
 *						PUBLIC DICTIONARY MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	dictionaryFilled																*
	 *==================================================================================*/

	/**
	 * Check if dictionary is filled
	 *
	 * This method will return <tt>TRUE</tt> if the current dictionary can resolve the
	 * <tt>kTAG_DOMAIN</tt> identifier.
	 *
	 * We assume that if the dictionary can resolve this identifier, it means it must be
	 * filled.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> means filled.
	 */
	public function dictionaryFilled()
	{
		//
		// Get domain tag object.
		//
		$domain = $this->getObject( kTAG_DOMAIN, FALSE );
		
		return ( $domain !== NULL );												// ==>
	
	} // dictionaryFilled.

	 
	/*===================================================================================
	 *	dictionaryFlush																	*
	 *==================================================================================*/

	/**
	 * Flush dictionary elements
	 *
	 * This method should invalidate all the elements of the dictionary.
	 *
	 * The method expects one parameter that corresponds to the delay in seconds before the
	 * elements will be invalidated.
	 *
	 * @param integer				$theDelay			Delay before flush.
	 *
	 * @access public
	 */
	abstract public function dictionaryFlush( $theDelay = 0 );

		

/*=======================================================================================
 *																						*
 *							PROTECTED DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setEntry																		*
	 *==================================================================================*/

	/**
	 * Set a dictionary entry
	 *
	 * This method should commit a new entry in the dictionary, if it doesn't exist yet, or
	 * replace the matching entry if it already exists.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theKey</b>: Entry key.
	 *	<li><b>$theValue</b>: Entry value.
	 *	<li><b>$theLife</b>: Entry lifetime in seconds, 0 means permanent.
	 * </ul>
	 *
	 * If the operation fails the method should raise an exception.
	 *
	 * @param mixed					$theKey				Entry key.
	 * @param mixed					$theValue			Entry value.
	 * @param integer				$theLife			Entry lifetime.
	 *
	 * @access protected
	 */
	abstract protected function setEntry( $theKey, $theValue, $theLife );

	 
	/*===================================================================================
	 *	getEntry																		*
	 *==================================================================================*/

	/**
	 * Get a dictionary entry
	 *
	 * This method should return the dictionary entry corresponding to the provided key; if
	 * the entry is not matched, the method should return <tt>NULL</tt>.
	 *
	 * If the operation fails the method should raise an exception.
	 *
	 * @param mixed					$theKey				Entry key.
	 *
	 * @access protected
	 * @return mixed				Entry value or <tt>NULL</tt>.
	 */
	abstract protected function getEntry( $theKey );

	 
	/*===================================================================================
	 *	delEntry																		*
	 *==================================================================================*/

	/**
	 * Get a dictionary entry
	 *
	 * This method should delete the dictionary entry corresponding to the provided key; if
	 * the entry was deleted, the method should return <tt>TRUE</tt>, if the entry was not
	 * found, the method should return <tt>FALSE</tt>.
	 *
	 * If the operation fails the method should raise an exception.
	 *
	 * @param mixed					$theKey				Entry key.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> deleted, <tt>FALSE</tt> not matched.
	 */
	abstract protected function delEntry( $theKey );

	 

} // class DictionaryObject.


?>
