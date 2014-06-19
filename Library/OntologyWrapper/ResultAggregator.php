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
 * API.
 *
 * This file contains the API definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );

/**
 * Results aggregator
 *
 * The duty of this object is to aggregate the results of the provided iterator object into
 * a single structure in which all the iterator element's tag and other object references
 * are resolved.
 *
 * The object stores this structure in a data member, this data member is set with a
 * reference to the array that will receive the results. The structure is divided into three
 * main blocks, the first one is structured as follows:
 *
 * <ul>
 *	<li><tt>{@link kAPI_RESPONSE_PAGING}</tt>: This element holds the iterator counters:
 *	 <ul>
 *		<li><tt>{@link kAPI_PAGING_AFFECTED}</tt>: This element will hold an integer
 *			indicating the total number of records affected by the query; this value does not
 *			take into consideration paging.
 *		<li><tt>{@link kAPI_PAGING_ACTUAL}</tt>: This element will hold an integer indicating
 *			the number of records returned by the iterator; this value takes into
 *			consideration paging.
 *		<li><tt>{@link kAPI_PAGING_SKIP}</tt>: This element will hold an integer indicating
 *			the number of records skipped.
 *		<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: This element will hold an integer indicating
 *			the maximum number of requested records.
 *	 </ul>
 * </ul>
 *
 * This first block holds the information pertaining to the iterator, the second block
 * holds information regarding the contents of the third block:
 *
 * <ul>
 *	<li><tt>{@link kAPI_RESULTS_DICTIONARY}</tt>: This element holds the cross references
 *		needed to access the elements of the result block:
 *	 <ul>
 *		<li><tt>{@link kAPI_DICTIONARY_COLLECTION}</tt>: This element will hold the name of
 *			the iterator's collection.
 *		<li><tt>{@link kAPI_DICTIONARY_TAGS}</tt>: This element will hold the list of all
 *			referenced tags, the key represents the tag sequence number of the value the tag
 *			native identifier. This element makes it easier to select tag objects by offset.
 *		<li><tt>{@link kAPI_DICTIONARY_IDS}</tt>: This element will hold an array listing
 *			the native identifiers of the objects selected by the iterator. This is
 *			necessary to discriminate the actual result set from eventual objects, belonging
 *			to the iterator's collection, referenced by other objects in the set.
 *	 </ul>
 * </ul>
 *
 * The second block should be used to identify the result set and to reference tags by
 * offset. The third set is an array indexed by collection name, holding all tag objects
 * referenced by offsets and all eventual referenced objects belonging to the results set:
 *
 * <ul>
 *	<li><tt>{@link kAPI_RESPONSE_RESULTS}</tt>: This element holds the list of names of the
 *		collections featuring objects; each element is also an array holding the list of
 *		objects belonging to that collection indexed by native identifier. The objects are
 *		stored as arrays.
 * </ul>
 *
 * Object references are resolved as follows:
 *
 * <ul>
 *	<li><em>Offsets</em>: All offsets of all objects, including tag offsets, are resolved
 *		into tag objects, these are stored in the relative element of the
 *		{@link kAPI_RESPONSE_RESULTS} block and their native and sequence identifiers are
 *		cross referenced in the {@link kAPI_DICTIONARY_TAGS} element.
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
	 * The object should be instantiated with an iterator object and a reference to an array
	 * that will receive the results.
	 *
	 * The iterator should have been paged by the caller: in this class we do not handle
	 * paging and sorting, we simply scan the iterator.
	 *
	 * The constructor will store the iterator and exctract the relevant information from
	 * it, it will then initialise the paging, dictionary and object blocks.
	 *
	 * @param IteratorObject		$theIterator		Iterator.
	 * @param array					$theResults			Results array reference.
	 *
	 * @access public
	 */
	public function __construct( IteratorObject $theIterator, &$theResults )
	{
		//
		// Check results.
		//
		if( ! is_array( $theResults ) )
			$theResults = Array();
		
		//
		// Set results.
		//
		$this->mResults = & $theResults;
		
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
		$this->mResults[ kAPI_RESPONSE_PAGING ]
			= array( kAPI_PAGING_AFFECTED => $theIterator->affectedCount(),
					 kAPI_PAGING_ACTUAL => $theIterator->count(),
					 kAPI_PAGING_SKIP => $theIterator->skip(),
					 kAPI_PAGING_LIMIT => $theIterator->limit() );
		
		//
		// Init dictionary.
		//
		$collection = $theIterator->collection()[ kTAG_CONN_COLL ];
		$this->mResults[ kAPI_RESULTS_DICTIONARY ]
			= array( kAPI_DICTIONARY_COLLECTION => $collection,
					 kAPI_DICTIONARY_REF_COUNT
					 	=> PersistentObject::ResolveRefCountTag( $collection ),
					 kAPI_DICTIONARY_IDS => Array(),
					 kAPI_DICTIONARY_TAGS => Array() );
		
		//
		// Init results.
		//
		$this->mResults[ kAPI_RESPONSE_RESULTS ] = Array();

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
			$cols = NULL;
	
			//
			// Iterate iterator.
			//
			foreach( $this->mIterator as $key => $value )
			{
				//
				// Load columns.
				//
				if( $cols === NULL )
				{
					//
					// Get domain.
					//
					if( array_key_exists( kTAG_DOMAIN, $value ) )
					{
						//
						// Get table columns.
						//
						$cols = UnitObject::ListOffsets( $value[ kTAG_DOMAIN ] );
						if( count( $cols ) )
						{
							//
							// Convert native identifiers in serials.
							//
							$list = array_keys( $cols );
							foreach( $list as $item )
							{
								//
								// Convert to native identifier.
								//
								if( (! is_int( $cols[ $item ] ))
								 && (!ctype_digit( $cols[ $item ] )) )
									$cols[ $item ]
										= $wrapper->getSerial( $cols[ $item ], TRUE );
							
								//
								// Set in dictionary.
								//
								$this->mResults[ kAPI_RESULTS_DICTIONARY ]
											   [ kAPI_DICTIONARY_LIST_COLS ]
													= $cols;
							
							} // Iterated columns.
						
						} // Has columns.
					
					} // Has domain.
					
					//
					// No domain => no columns.
					//
					else
						$cols = Array();
				
				} // Not processed columns yet.
		
				//
				// Store identifier.
				//
				$this->identify( $key );
		
				//
				// Process object.
				//
				$this->aggregateProcess(
					$wrapper, $value, $theLanguage, $doRefStructs, TRUE );
		
				//
				// Store object.
				//
				$this->mResults[ kAPI_RESPONSE_RESULTS ]
							   [ $name ]
							   [ $key ] = $value;
	
			} // Iterating iterator.
			
			//
			// Add table column offsets.
			//
			if( array_key_exists( kAPI_DICTIONARY_LIST_COLS,
								  $this->mResults[ kAPI_RESULTS_DICTIONARY ] ) )
				$this->loadTags( $wrapper, $cols, $theLanguage, $doRefStructs );
			
			//
			// Cluster tags.
			//
			if( $name == Tag::kSEQ_NAME )
				$this->clusterTags();
		
			//
			// Signal processed.
			//
			$this->mProcessed = TRUE;
	
		} // Not processed.
	
		return $this->mResults;														// ==>
	
	} // aggregate.

		

/*=======================================================================================
 *																						*
 *								STATIC CLUSTER INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GetTagClusterKey																*
	 *==================================================================================*/

	/**
	 * Get tag cluster key
	 *
	 * This method will return the tag cluster key associated to the provided terms list.
	 *
	 * By default we cluster tags by feature term.
	 *
	 * @param array					$theTerms			Tag terms.
	 *
	 * @static
	 * @return string				Tag cluster key.
	 */
	static function GetTagClusterKey( &$theTerms )
	{
		return $theTerms[ 0 ];														// ==>
	
	} // GetTagClusterKey.

	 

/*=======================================================================================
 *																						*
 *							PROTECTED PROCESSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	aggregateProcess																*
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
	 *		<li><em>Remove internal offsets</em>: all internal offsets will be removed from
	 *			the object.
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
	 * @param array					$theObject			Object array reference.
	 * @param string				$theLanguage		Default language code.
	 * @param boolean				$doRefStructs		<tt>TRUE</tt> reference structures.
	 * @param boolean				$doRefObjects		<tt>TRUE</tt> load object refs.
	 *
	 * @access protected
	 */
	protected function aggregateProcess( Wrapper $theWrapper, &$theObject,
															   $theLanguage,
															   $doRefStructs,
															   $doRefObjects = TRUE )
	{
		//
		// Get object class.
		//
		$class = $theObject[ kTAG_CLASS ];
		
		//
		// Get list of tags to be excluded from the object.
		//
		$exclude
			= array_diff(
				array_merge(											// Exclude
					$class::InternalOffsets(),							// internal,
					$class::DynamicOffsets(),							// dynamic,
					array( kTAG_TAG_OFFSETS, kTAG_TERM_OFFSETS,			// offsets
						   kTAG_NODE_OFFSETS, kTAG_EDGE_OFFSETS,
						   kTAG_UNIT_OFFSETS, kTAG_ENTITY_OFFSETS ) ),	// except
				array( kTAG_RECORD_CREATED, kTAG_RECORD_MODIFIED ),		// timestamps
				array( kTAG_MIN_VAL, kTAG_MAX_VAL ) );					// and ranges.
								
		//
		// Save tags.
		//
		$tags
			= array_unique(
				array_diff(
					array_merge(
						array_keys( $theObject ),
						$theObject[ kTAG_OBJECT_TAGS ] ),
					$exclude ) );
		
		//
		// Save object references.
		//
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
		
	} // aggregateProcess.

	 
	/*===================================================================================
	 *	identify																		*
	 *==================================================================================*/

	/**
	 * Load identifier
	 *
	 * This method will load the provided identifier in the main identifiers list.
	 *
	 * In this class we set the provided value in the {@link kAPI_DICTIONARY_IDS} element of
	 * the {@link kAPI_RESULTS_DICTIONARY} block; derived classes may overload this method
	 * to build a custom identifiers structure.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access protected
	 */
	protected function identify( $theIdentifier )
	{
		$this->mResults[ kAPI_RESULTS_DICTIONARY ]
					   [ kAPI_DICTIONARY_IDS ]
					   [] = $theIdentifier;
		
	} // identify.

	 
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
	protected function processObject( Wrapper $theWrapper, &$theObject, &$theTags,
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
																	 $doRefStructs = FALSE )
	{
		//
		// Copy offset.
		//
		$offset = $theIterator->key();
		
		//
		// Handle published tags.
		//
		if( in_array( $offset,
					  array_merge( $theTags, PersistentObject::GetReferenceCounts() ) ) )
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
			PersistentObject::OffsetTypes(
				$theWrapper, $offset, $type, $kind, $min, $max, $pattern );
			
			//
			// Handle structure.
			//
			if( $type == kTYPE_STRUCT )
			{
				//
				// Shadow structures.
				//
				if( $doRefStructs )
					$theNew[ $offset ] = implode( '.', $thePath );
				
				//
				// Process structures.
				//
				else
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
											array( $iterator, $theWrapper,
															& $ref,
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
											array( $iterator, $theWrapper,
															& $reference,
															& $theTags,
															& $theTags,
															  $theLanguage,
															  $doRefStructs ) );
						} // Do not shadow structures.
		
					} // Scalar structure.
				
				} // Process structure.
			
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
				 	$property
				 		= OntologyObject::SelectLanguageString(
				 			$property, $theLanguage );
				
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
		$criteria = array( kTAG_ID_SEQUENCE => array( '$in' => array_values( $theTags ) ) );
		
		//
		// Load tags.
		//
		$iter = $collection->matchAll( $criteria, kQUERY_ARRAY );
		foreach( $iter as $key => $value )
		{
			//
			// Skip existing.
			//
			if( ! in_array( $key, $this->mResults[ kAPI_RESULTS_DICTIONARY ]
												 [ kAPI_DICTIONARY_TAGS ] ) )
			{
				//
				// Load xref.
				//
				$this->mResults[ kAPI_RESULTS_DICTIONARY ]
							   [ kAPI_DICTIONARY_TAGS ]
							   [ $value[ kTAG_ID_SEQUENCE ] ]
					= $key;
			
				//
				// Process tag.
				//
				$this->aggregateProcess(
					$theWrapper, $value, $theLanguage, $doRefStructs, TRUE );
			
				//
				// Load object.
				//
				$this->mResults[ kAPI_RESPONSE_RESULTS ]
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
			 && (! array_key_exists( $coll, $this->mResults[ kAPI_RESPONSE_RESULTS ] )) )
				$this->mResults[ kAPI_RESPONSE_RESULTS ][ $coll ] = Array();
			
			//
			// Iterate references.
			//
			foreach( $iter as $key => $value )
			{
				//
				// Skip existing.
				//
				if( ! array_key_exists( $key, $this->mResults[ kAPI_RESPONSE_RESULTS ]
															 [ $coll ] ) )
				{
					//
					// Process reference.
					//
					$this->aggregateProcess(
						$theWrapper, $value, $theLanguage, $doRefStructs, FALSE );
					
					//
					// Load object.
					//
					$this->mResults[ kAPI_RESPONSE_RESULTS ]
								   [ $coll ]
								   [ $key ]
						= $value;
				
				} // Not there yet.
			
			} // Iterating references.
		
		} // Iterating collections.
	
	} // loadReferences.

	 
	/*===================================================================================
	 *	clusterTags																		*
	 *==================================================================================*/

	/**
	 * Cluster tags
	 *
	 * This method will update the response dictionary cluster.
	 *
	 * This method should only be called on tags query collections.
	 *
	 * @access protected
	 */
	protected function clusterTags()
	{
		//
		// Init local storage.
		//
		if( $this->mIterator->collection()[ kTAG_CONN_COLL ] == Tag::kSEQ_NAME )
		{
			//
			// Reference dictionary cluster.
			//
			$ref = & $this->mResults[ kAPI_RESULTS_DICTIONARY ];
			$ref[ kAPI_DICTIONARY_CLUSTER ] = Array();
			$ref = & $ref[ kAPI_DICTIONARY_CLUSTER ];
			
			//
			// Iterate result identifiers.
			//
			foreach( $this->mResults[ kAPI_RESULTS_DICTIONARY ][ kAPI_DICTIONARY_IDS ]
						as $id )
			{
				//
				// Get cluster.
				//
				$cluster
					= static::GetTagClusterKey(
						$this->mResults[ kAPI_RESPONSE_RESULTS ]
									   [ Tag::kSEQ_NAME ]
									   [ $id ]
									   [ kTAG_TERMS ] );
				
				//
				// Create cluster.
				//
				if( ! array_key_exists( $cluster, $ref ) )
					$ref[ $cluster ] = array( $id );
				
				//
				// Update cluster.
				//
				elseif( ! array_key_exists( $id, $ref[ $cluster ] ) )
					$ref[ $cluster ][] = $id;
			
			} // Iterating identifiers.
		
		} // Tags collection.
	
	} // clusterTags.

	 

} // class ResultAggregator.


?>
