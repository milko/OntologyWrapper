<?php

/**
 * ResultAggregator.php
 *
 * This file contains the definition of the {@link ResultAggregator} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ContainerObject;
use OntologyWrapper\IteratorObject;

/*=======================================================================================
 *																						*
 *								ResultAggregator.php									*
 *																						*
 *======================================================================================*/

/**
 * Results aggregator
 *
 * The duty of this object is to aggregate the results of the provided iterator object into
 * a single structure in which all the iterator element's tag and other object references
 * are resolved.
 *
 * The object stores this structure in a data member, the structure is divided into three
 * main blocks, the first one is structured as follows:
 *
 * <ul>
 *	<li><tt>{@link kPAGE}</tt>: This element holds the iterator counters:
 *	 <ul>
 *		<li><tt>{@link kAFFECTED}</tt>: This element will hold an integer indicating the
 *			total number of records affected by the query; this value does not take into
 *			consideration paging.
 *		<li><tt>{@link kRETURNED}</tt>: This element will hold an integer indicating the
 *			number of records returned by the iterator; this value takes into consideration
 *			paging.
 *		<li><tt>{@link kSTART}</tt>: This element will hold an integer indicating the
 *			number of records skipped.
 *		<li><tt>{@link kLIMIT}</tt>: This element will hold an integer indicating the
 *			maximum number of requested records.
 *	 </ul>
 * </ul>
 *
 * This first block holds the information pertaining to the iterator, the second block
 * holds information regarding the contents of the third block:
 *
 * <ul>
 *	<li><tt>{@link kDICT}</tt>: This element holds the cross references needed to access
 *		the elements of the result block:
 *	 <ul>
 *		<li><tt>{@link kCOLLECTION}</tt>: This element will hold the name of the iterator's
 *			collection.
 *		<li><tt>{@link kTAGXREF}</tt>: This element will hold the list of all referenced
 *			tags, the key represents the tag sequence number of the value the tag native
 *			identifier. This element makes it easier to select tag objects by offset.
 *		<li><tt>{@link kIDs}</tt>: This element will hold an array listing the native
 *			identifiers of the objects selected by the iterator. This is necessary to
 *			discriminate the actual result set from eventual objects, belonging to the
 *			iterator's collection, referenced by other objects in the set.
 *	 </ul>
 * </ul>
 *
 * The second block should be used to identify the result set and to reference tags by
 * offset. The third set is an array indexed by collection name, holding all tag objects
 * referenced by offsets and all eventual referenced objects belonging to the results set:
 *
 * <ul>
 *	<li><tt>{@link kOBJECTS}</tt>: This element holds the list of names of the collections
 *		featuring objects; each element is also an array holding the list of objects
 *		belonging to that collection indexed by native identifier. The objects are stored as
 *		arrays.
 * </ul>
 *
 * Object references are resolved as follows:
 *
 * <ul>
 *	<li><em>Offsets</em>: All offsets of all objects, including tag offsets, are resolved
 *		into tag objects, these are stored in the relative element of the {@link kOBJECTS}
 *		block and their native and sequence identifiers are cross referenced in the
 *		{@link kTAGXREF} element.
 *	<li><em>Object references</em>: All object references are resolved at the first level,
 *		which means that referenced objects will not resolve their references.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 23/03/2014
 */
class ResultAggregator
{
	/**
	 * Iterator.
	 *
	 * This protected data member holds the iterator.
	 *
	 * @var IteratorObject
	 */
	protected $mIterator = NULL;

	/**
	 * Results.
	 *
	 * This protected data member holds the iterator aggregated results.
	 *
	 * @var array
	 */
	protected $mResults = Array();

	/**
	 * Processed.
	 *
	 * This protected data member holds a flag indicating whether the iterator was
	 * processed, in that case, the result is not needed to be re-processed.
	 *
	 * @var array
	 */
	protected $mProcessed = FALSE;

	/**
	 * Page.
	 *
	 * This constant references the page block.
	 *
	 * @var string
	 */
	const kPAGE = 'page';

	/**
	 * Affected count.
	 *
	 * This constant references the affected count, which represents an integer indicating
	 * the total number of elements affected by the query, without considering paging.
	 */
	const kAFFECTED = 'affected';

	/**
	 * Returned count.
	 *
	 * This constant references the returned count, which represents an integer indicating
	 * the total number of elements returned by the operation; this takes into
	 * consideration paging.
	 */
	const kRETURNED = 'returned';

	/**
	 * Skipped.
	 *
	 * This constant references the number of skipped elements, this represents an integer
	 * referring to the <tt>start</tt> paging parameter which starts with <tt>0</tt>.
	 */
	const kSTART = 'start';

	/**
	 * Limit.
	 *
	 * This constant represents an integer referencing the maximum number of elements to be
	 * returned by the iterator.
	 */
	const kLIMIT = 'limit';

	/**
	 * Dictionary.
	 *
	 * This constant references the block holding the results cross references.
	 */
	const kDICT = 'dict';

	/**
	 * Collection.
	 *
	 * This constant represents the iterator's collection name, this will correspond to
	 * the key of the block holding objects returned by the iterator; and eventual
	 * referenced objects belonging to the same collection.
	 */
	const kCOLLECTION = 'coll';

	/**
	 * Tag cross references.
	 *
	 * This constant references the block holding all tag cross references: this is an
	 * array indexed by tag sequence number with as value the tag native identifier, this
	 * is used to resolve object offsets.
	 */
	const kTAGXREF = 'tags';

	/**
	 * Query identifiers.
	 *
	 * This constant references the block holding the native identifiers of the objects
	 * selected by the iterator.
	 */
	const kIDs = 'ids';

	/**
	 * Objects.
	 *
	 * This constant references the block holding all the objects referenced by the
	 * result set, it is an array indexed by collection name, holding the list of objects,
	 * represented as the object array, belonging to the collection.
	 */
	const kOBJECTS = 'objs';

		

/*=======================================================================================
 *																						*
 *											MAGIC										*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * Instantiate class.
	 *
	 * The object should be instantiated with an iterator object, this iterator should have
	 * been paged by the caller: in this class we do not handle paging and sorting, we
	 * simply scan the iterator.
	 *
	 * The constructor will store the iterator and exctract the relevant information from
	 * it, it will then initialise the standard blocks.
	 *
	 * @param IteratorObject		$theIterator		Iterator.
	 *
	 * @access public
	 */
	public function __construct( IteratorObject $theIterator )
	{
		//
		// Set results type.
		//
		$theIterator->resultType( kQUERY_ARRAY );
		
		//
		// Reset fields.
		//
		$theIterator->fields( Array() );
		
		//
		// Store iterator.
		//
		$this->mIterator = $theIterator;
		
		//
		// Init page.
		//
		$this->mResults[ self::kPAGE ]
			= array( self::kAFFECTED => $theIterator->affectedCount(),
					 self::kRETURNED => $theIterator->count(),
					 self::kSTART => $theIterator->skip(),
					 self::kLIMIT => $theIterator->limit() );
		
		//
		// Init dictionary.
		//
		$this->mResults[ self::kDICT ]
			= array( self::kCOLLECTION => $theIterator->collection()[ kTAG_CONN_COLL ],
					 self::kTAGXREF => Array(),
					 self::kIDs => Array() );
		
		//
		// Init results.
		//
		$this->mResults[ self::kOBJECTS ] = Array();

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *									PUBLIC MEMBER INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resultSet																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the result set</h4>
	 *
	 * This method will return the current result set.
	 *
	 * @access public
	 * @return array				Result set.
	 */
	public function resultSet()									{	return $this->mResults;	}

		

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	aggregate																		*
	 *==================================================================================*/

	/**
	 * <h4>Aggregate and return results</h4>
	 *
	 * This method will iterate the results set aggregating the data, the method will return
	 * the resulting array.
	 *
	 * @param string				$theLanguage		Default language code.
	 * @param boolean				$doRefStructs		<tt>TRUE</tt> reference structures.
	 *
	 * @access public
	 * @return array				Aggregated results.
	 */
	public function aggregate( $theLanguage = NULL, $doRefStructs = FALSE )
	{
		//
		// Check if it needs to be processed.
		//
		if( ! $this->mProcessed )
		{
			//
			// Init local storage.
			//
			$collection = $this->mIterator->collection();
			$wrapper = $collection->dictionary();
			$name = $collection[ kTAG_CONN_COLL ];
	
			//
			// Iterate iterator.
			//
			foreach( $this->mIterator as $key => $value )
			{
				//
				// Store identifier.
				//
				$this->identify( $key );
		
				//
				// Process object.
				//
				$this->process( $wrapper, $value, $theLanguage, $doRefStructs, TRUE );
		
				//
				// Store object.
				//
				$this->mResults[ self::kOBJECTS ]
							   [ $name ]
							   [ $key ] = $value;
	
			} // Iterating iterator.
		
			//
			// Signal processed.
			//
			$this->mProcessed = TRUE;
	
		} // Not processed.
	
		return $this->mResults;													// ==>
	
	} // aggregate.

	 

/*=======================================================================================
 *																						*
 *							PROTECTED PROCESSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	identify																		*
	 *==================================================================================*/

	/**
	 * Load identifier
	 *
	 * This method will load the provided identifier in the main identifiers list.
	 *
	 * In this class we set the provided value in the {@link kIDs} element of the
	 * {@link kDICT} block; derived classes may overload this method to build a custom
	 * identifiers structure.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access protected
	 */
	protected function identify( $theIdentifier )
	{
		$this->mResults[ self::kDICT ]
					   [ self::kIDs ]
					   [] = $theIdentifier;
		
	} // identify.

	 
	/*===================================================================================
	 *	process																			*
	 *==================================================================================*/

	/**
	 * Process object
	 *
	 * This method will parse the provided object and perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Collect tags and references</em>. The method will collect the object's tags
	 *		and references.
	 *	<li><em>Serialise object</em>. The method will traverse the object and perform the
	 *		following actions:
	 *	 <ul>
	 *		<li><em>Remove unpublished offsets</em>: all offsets not making part of the
	 *			{@link kTAG_OBJECT_TAGS} property will be removed from the object.
	 *		<li><em>Select default language strings</em>: if the language code was provided,
	 *			all language string property values will be replaced by the string matching
	 *			the provided language code, following these rules:
	 *		 <ul>
	 *			<li>If the string matching the code is there, use it.
	 *			<li>If a code <tt>0</tt> is there, use it.
	 *			<li>Use the first string.
	 *		 </ul>
	 *		<li><em>Shadow structures</em>: if the fourth parameter is <tt>TRUE</tt>, the
	 *			method will replace all structure property values with the offset path of
	 *			the property; this value can be used later to retrieve the single structure
	 *			properties.
	 *	 </ul>
	 *	<li><em>Load object references</em>. The method will load all the objects referenced
	 *		by the current object, if the fifth parameter is <tt>TRUE</tt>.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param reference				$theObject			Object array reference.
	 * @param string				$theLanguage		Default language code.
	 * @param boolean				$doRefStructs		<tt>TRUE</tt> reference structures.
	 * @param boolean				$doRefObjects		<tt>TRUE</tt> load object refs.
	 *
	 * @access protected
	 */
	protected function process( Wrapper $theWrapper, &$theObject,
													  $theLanguage,
													  $doRefStructs,
													  $doRefObjects = TRUE )
	{
		//
		// Save tags and references.
		//
		$tags = $theObject[ kTAG_OBJECT_TAGS ];
		$refs = ( array_key_exists( kTAG_OBJECT_REFERENCES, $theObject ) )
			  ? $theObject[ kTAG_OBJECT_REFERENCES ]
			  : Array();
		
		//
		// Serialise object.
		//
		$this->processObject(
			$theWrapper, $theObject, $tags, $theLanguage, $doRefStructs );
		
		//
		// Load tags.
		//
		$this->loadTags( $theWrapper, $tags, $theLanguage, $doRefStructs );
		
		//
		// Load references.
		//
		if( count( $refs )
		 && $doRefObjects )
			$this->loadReferences( $theWrapper, $refs, $theLanguage, $doRefStructs );
		
	} // process.

	 
	/*===================================================================================
	 *	processObject																	*
	 *==================================================================================*/

	/**
	 * Reduce object
	 *
	 * This method will remove unreferenced tags from the provided object.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param reference				$theObject			Object array.
	 * @param reference				$theTags			Tag sequence numbers.
	 * @param string				$theLanguage		Default language code.
	 * @param boolean				$doRefStructs		<tt>TRUE</tt> reference structures.
	 *
	 * @access protected
	 */
	protected function processObject( Wrapper $theWrapper,
									  &$theObject, &$theTags,
									   $theLanguage, $doRefStructs )
	{
		//
		// Traverse object.
		//
		$path = Array();
		$serial = Array();
		$struct = new \ArrayObject( $theObject );
		$iterator = $struct->getIterator();
		iterator_apply( $iterator,
						array( $this, 'traverseObject' ),
						array( $iterator, $theWrapper, & $serial,
													   & $path,
													   & $theTags,
														 $theLanguage,
														 $doRefStructs ) );
		
		//
		// Update object.
		//
		$theObject = $serial;
	
	} // processObject.

	 
	/*===================================================================================
	 *	traverseObject																	*
	 *==================================================================================*/

	/**
	 * Traverse object structure
	 *
	 * This method's duty is to traverse the object's structure and:
	 *
	 * <ul>
	 *	<li><em>Remove unpublished tags</em>. These are tags not included in the object's
	 *		{@link kTAG_OBJECT_TAGS} property.
	 *	<li><em>Select default language strings</em>. If the {@link aggregate()} method was
	 *		provided a default language code, the method will select the corresponding
	 *		string and replace the offset's value with that string, following these tules:
	 *	 <ul>
	 *		<li>If the string matching the code is there, use it.
	 *		<li>If a code <tt>0</tt> is there, use it.
	 *		<li>Use the first string.
	 *	 </ul>
	 *	<li><em>Reference structures</em>. If the {@link aggregate()} method was provided
	 *		that option, all structure offset values will be replaced with the pffset path
	 *		to the structure property.
	 * </ul>
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: The iterator pointing to the current property.
	 *	<li><b>$theWrapper</b>: The data wrapper.
	 *	<li><b>$theNew</b>: This array reference will receive the processed object.
	 *	<li><b>$thePath</b>: An array that will be used to compute the path of the current
	 *		property.
	 *	<li><b>$theTags</b>: The list of published tag sequence numbers.
	 *	<li><b>$theLanguage</b>: The default language code.
	 *	<li><b>$doRefStructs</b>: If <tt>TRUE</tt>, structure contents will be replaced with
	 *		the path to the property.
	 * </ul>
	 *
	 * This method is used by the PHP {@link iterator_apply()} method, which means that it
	 * should return <tt>TRUE</tt> to continue the object traversal, or <tt>FALSE</tt> to
	 * stop it: it will return <tt>TRUE</tt> by default.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param reference				$theNew				Receives processed object.
	 * @param reference				$thePath			Offsets path.
	 * @param reference				$theTags			Tag sequence numbers.
	 * @param string				$theLanguage		Default language code.
	 * @param boolean				$doRefStructs		<tt>TRUE</tt> reference structures.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt>, or <tt>FALSE</tt> to stop the traversal.
	 */
	protected final function traverseObject( \Iterator $theIterator,
											 Wrapper   $theWrapper, &$theNew,
											 						&$thePath,
											 						&$theTags,
																	 $theLanguage,
																	 $doRefStructs = TRUE )
	{
		//
		// Handle published tags.
		//
		if( in_array( $offset = $theIterator->key(), $theTags ) )
		{
			//
			// Init local storage.
			//
			$property = $theIterator->current();
			
			//
			// Push to path.
			//
			$thePath[] = $offset;
		
			//
			// Collect offset data types.
			//
			PersistentObject::OffsetTypes( $theWrapper, $offset, $type, $kind );
			
			//
			// Handle structure.
			//
			if( $type == kTYPE_STRUCT )
			{
				//
				// Init new object property.
				//
				$theNew[ $offset ] = Array();
				$reference = & $theNew[ $offset ];
				
				//
				// Handle structure lists.
				//
				if( in_array( kTYPE_LIST, $kind ) )
				{
					//
					// Iterate list.
					//
					foreach( $property as $idx => $struct )
					{
						//
						// Init new object property.
						//
						$reference[ $idx ] = Array();
						$ref = & $reference[ $idx ];
				
						//
						// Traverse structure.
						//
						$struct = new \ArrayObject( $struct );
						$iterator = $struct->getIterator();
						iterator_apply( $iterator,
										array( $this, 'traverseObject' ),
										array( $iterator, $theWrapper, & $ref,
																	   & $thePath,
																	   & $theTags,
															  			 $theLanguage,
															  			 $doRefStructs ) );
			
					} // Iterating list.
		
				} // List of structures.
		
				//
				// Handle scalar structure.
				//
				else
				{
					//
					// Shadow structures.
					//
					if( $doRefStructs )
						$theNew[ $offset ] = implode( '.', $thePath );
					
					//
					// Traverse structure.
					//
					else
					{
						$struct = new \ArrayObject( $property );
						$iterator = $struct->getIterator();
						iterator_apply( $iterator,
										array( $this, 'traverseObject' ),
										array( $iterator, $theWrapper, & $reference,
																	   & $theTags,
																	   & $theTags,
																		 $theLanguage,
																		 $doRefStructs ) );
					} // Do not shadow structures.
		
				} // Scalar structure.
			
			} // Structure.
			
			//
			// Handle scalars.
			//
			else
			{
				//
				// Handle language strings.
				//
				if( ($theLanguage !== NULL)
				 && ($type == kTYPE_LANGUAGE_STRINGS) )
					$property = $this->selectLanguageString( $property, $theLanguage );
				
				//
				// Set new object.
				//
				$theNew[ $offset ] = $property;
			
			} // Scalar value.
		
			//
			// Pop from path.
			//
			array_pop( $thePath );
		
		} // Published tag.
	
		return TRUE;																// ==>
	
	} // traverseObject.

	 
	/*===================================================================================
	 *	loadTags																		*
	 *==================================================================================*/

	/**
	 * Load tags
	 *
	 * This method will load the tag objects provided in the parameter.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param reference				$theTags			Tag sequence numbers.
	 * @param string				$theLanguage		Default language code.
	 * @param boolean				$doRefStructs		<tt>TRUE</tt> reference structures.
	 *
	 * @access protected
	 */
	protected function loadTags( Wrapper $theWrapper,
										&$theTags,
										 $theLanguage, $doRefStructs )
	{
		//
		// Resolve collection.
		//
		$collection
			= Tag::ResolveCollection(
				Tag::ResolveDatabase( $theWrapper, TRUE ) );
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_ID_SEQUENCE => array( '$in' => $theTags ) );
		
		//
		// Load tags.
		//
		$iter = $collection->matchAll( $criteria, kQUERY_ARRAY );
		foreach( $iter as $key => $value )
		{
			//
			// Skip existing.
			//
			if( ! in_array( $key, $this->mResults[ self::kDICT ][ self::kTAGXREF ] ) )
			{
				//
				// Load xref.
				//
				$this->mResults[ self::kDICT ]
							   [ self::kTAGXREF ]
							   [ $value[ kTAG_ID_SEQUENCE ] ]
					= $key;
			
				//
				// Process tag.
				//
				$this->process( $theWrapper, $value, $theLanguage, $doRefStructs, TRUE );
			
				//
				// Load object.
				//
				$this->mResults[ self::kOBJECTS ]
							   [ Tag::kSEQ_NAME ]
							   [ $key ]
					= $value;
			
			} // Not there yet.
		
		} // Iterating results.
	
	} // loadTags.

	 
	/*===================================================================================
	 *	loadReferences																	*
	 *==================================================================================*/

	/**
	 * Load references
	 *
	 * This method will load the object references provided in the parameter.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param reference				$theRefs			Object references.
	 * @param string				$theLanguage		Default language code.
	 * @param boolean				$doRefStructs		<tt>TRUE</tt> reference structures.
	 *
	 * @access protected
	 */
	protected function loadReferences( Wrapper $theWrapper,
											  &$theRefs,
											   $theLanguage, $doRefStructs )
	{
		//
		// Iterate collections.
		//
		foreach( $theRefs as $coll => $objects )
		{
			//
			// Resolve collection.
			//
			$collection = PersistentObject::ResolveCollectionByName( $theWrapper, $coll );
	
			//
			// Set criteria.
			//
			$criteria = array( kTAG_NID => array( '$in' => $objects ) );
	
			//
			// Load references.
			//
			$iter = $collection->matchAll( $criteria, kQUERY_ARRAY );
			
			//
			// Create container.
			//
			if( $iter->affectedCount()
			 && (! array_key_exists( $coll, $this->mResults[ self::kOBJECTS ] )) )
				$this->mResults[ self::kOBJECTS ][ $coll ] = Array();
			
			//
			// Iterate references.
			//
			foreach( $iter as $key => $value )
			{
				//
				// Skip existing.
				//
				if( ! array_key_exists( $key, $this->mResults[ self::kOBJECTS ][ $coll ] ) )
				{
					//
					// Process reference.
					//
					$this->process(
						$theWrapper, $value, $theLanguage, $doRefStructs, FALSE );
					
					//
					// Load object.
					//
					$this->mResults[ self::kOBJECTS ]
								   [ $coll ]
								   [ $key ]
						= $value;
				
				} // Not there yet.
			
			} // Iterating references.
		
		} // Iterating collections.
	
	} // loadReferences.

	 
	/*===================================================================================
	 *	selectLanguageString																		*
	 *==================================================================================*/

	/**
	 * Traverse language strings
	 *
	 * This method's duty is to replace the property with the string matching the provided
	 * language code, the method will perform the following steps:
	 *
	 * <ul>
	 *	<li>If the string matching the code is there, use it.
	 *	<li>If a code <tt>0</tt> is there, use it.
	 *	<li>Use the first string.
	 * </ul>
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theStrings</b>: The property containing the strings.
	 *	<li><b>$theLanguage</b>: The default language code.
	 * </ul>
	 *
	 * This method is used by the PHP {@link iterator_apply()} method, which means that it
	 * should return <tt>TRUE</tt> to continue the object traversal, or <tt>FALSE</tt> to
	 * stop it: it will return <tt>TRUE</tt> by default.
	 *
	 * @param array					$theStrings			Language strings property.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 * @return string				The selected string.
	 */
	protected final function selectLanguageString( $theStrings, $theLanguage )
	{
		//
		// Init local storage.
		//
		$first = NULL;
		
		//
		// Locate language code.
		//
		foreach( $theStrings as $string )
		{
			//
			// Match language code.
			//
			if( $string[ kTAG_LANGUAGE ] == $theLanguage )
				return $string[ kTAG_TEXT ];										// ==>
			
			//
			// Set first.
			//
			if( $first === NULL )
				$first = $string[ kTAG_TEXT ];
		
		} // Iterating language strings.
	
		//
		// Locate default string.
		//
		foreach( $theStrings as $string )
		{
			//
			// Match default code.
			//
			if( $string[ kTAG_LANGUAGE ] == 0 )
				return $string[ kTAG_TEXT ];										// ==>
		
		} // Iterating language strings.
		
		return $first;																// ==>
	
	} // selectLanguageString.

	 

} // class ResultAggregator.


?>
