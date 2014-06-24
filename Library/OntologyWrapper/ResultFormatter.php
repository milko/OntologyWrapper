<?php

/**
 * ResultFormatter.php
 *
 * This file contains the definition of the {@link ResultFormatter} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ContainerObject;
use OntologyWrapper\IteratorObject;

/*=======================================================================================
 *																						*
 *								ResultFormatter.php										*
 *																						*
 *======================================================================================*/

/**
 * API.
 *
 * This file contains the API definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );

/**
 * Results formatter
 *
 * The duty of this object is to format the results of the provided iterator object into
 * a set of data elements which may be used by a client to display the results.
 *
 * The object stores this structure in a data member, this data member is set with a
 * reference to the array that will receive the results. The structure is divided into two
 * main blocks, the first one is structured as follows:
 *
 * <ul>
 *	<li><tt>{@link kAPI_RESPONSE_PAGING}</tt>: This element holds the iterator counters:
 *	 <ul>
 *		<li><tt>{@link kAPI_PAGING_AFFECTED}</tt>: This element will hold an integer
 *			indicating the total number of records affected by the query; this value does
 *			not take into consideration paging.
 *		<li><tt>{@link kAPI_PAGING_ACTUAL}</tt>: This element will hold an integer
 *			indicating the number of records returned by the iterator; this value takes into
 *			consideration paging.
 *		<li><tt>{@link kAPI_PAGING_SKIP}</tt>: This element will hold an integer indicating
 *			the number of records skipped.
 *		<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: This element will hold an integer indicating
 *			the maximum number of requested records.
 *	 </ul>
 * </ul>
 *
 * The second block is an array containing all the objects contained in the provided
 * iterator, each array element is indexed by the object's native identifier and the value
 * is formatted as a nested array of elements corresoinding to the object's data members and
 * structured as follows:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: This element holds the data property
 *		name or label.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: This element holds the data property
 *		information or description.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DATA}</tt>: This element holds the property data
 *		formatted as a string, if the property is a scalar, or an array if the property is
 *		an enumerated set: in this case the value will be formatted as an array of elements
 *		of the following structure:
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: The enumerated value name or
 *			label.
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: The enumerated value information
 *			or description.
 *	 </ul>
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_LINK}</tt>: This tag indicates the property
 *		link, which can take two forms:
 *	 <ul>
 *		<li><em>URL</em>: If the property contains an internet link, this element will hold
 *			the URL as a string.
 *		<li><em>Object reference</em>: If the property contains an object reference, this
 *			element will hold the following structure:
 *		 <ul>
 *			<li><tt>id</tt>: The referenced object native identifier as a string.
 *			<li><tt>coll</tt>: The referenced object collection name.
 *		 </ul>
 *		<li><em>Object sub-reference</em>: If the property contains an object sub-document,
 *			the element will hold the following structure:
 *		 <ul>
 *			<li><tt>id</tt>: The referenced object native identifier as a string.
 *			<li><tt>coll</tt>: The referenced object collection name.
 *			<li><tt>sub</tt>: The sub-document tag sequence number or native identifier.
 *			<li><tt>idx</tt>: The sub-document index, if the property holds a list of
 *				sub-documents.
 *		 </ul>
 *	 </ul>
 *		In both cases the {@link kAPI_PARAM_RESPONSE_FRMT_DATA} element will hold the link
 *		display name.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DOCU}</tt>: This element holds the eventual
 *		sub-document as an array.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/06/2014
 */
class ResultFormatter
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
	 * Cache.
	 *
	 * This protected data member holds the cache, indexed by collection name.
	 *
	 * @var array
	 */
	protected $mCache = Array();

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
		// Store iterator.
		//
		$this->mIterator = $theIterator;
		
		//
		// Init paging.
		//
		$this->mResults[ kAPI_RESPONSE_PAGING ]
			= array( kAPI_PAGING_AFFECTED => $theIterator->affectedCount(),
					 kAPI_PAGING_ACTUAL => $theIterator->count(),
					 kAPI_PAGING_SKIP => $theIterator->skip(),
					 kAPI_PAGING_LIMIT => $theIterator->limit() );
		
		//
		// Init results.
		//
		$this->mResults[ kAPI_RESPONSE_RESULTS ] = Array();

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	format																			*
	 *==================================================================================*/

	/**
	 * <h4>Format and return results</h4>
	 *
	 * This method will iterate the results set formatting the data, the method will return
	 * the resulting array.
	 *
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access public
	 * @return array				Aggregated results.
	 */
	public function format( $theLanguage = NULL )
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
			$results = & $this->mResults[ kAPI_RESPONSE_RESULTS ];
	
			//
			// Iterate.
			//
			foreach( $this->mIterator as $object )
			{
				//
				// Get list of tags to be excluded from the object.
				//
				$class = $object[ kTAG_CLASS ];
				$exclude
					= array_merge(											// Exclude
						$class::InternalOffsets(),							// internal,
						$class::DynamicOffsets(),							// dynamic,
						array( kTAG_TAG_OFFSETS, kTAG_TERM_OFFSETS,			// offsets,
							   kTAG_NODE_OFFSETS, kTAG_EDGE_OFFSETS,
							   kTAG_UNIT_OFFSETS, kTAG_ENTITY_OFFSETS ),
						array( kTAG_RECORD_CREATED, kTAG_RECORD_MODIFIED ),	// stamps,
						array( kTAG_DOMAIN, kTAG_AUTHORITY,
							   kTAG_COLLECTION, kTAG_IDENTIFIER ),			// unit info,
						array( kTAG_MIN_VAL, kTAG_MAX_VAL ),				// ranges,
						array( kTAG_GEO_SHAPE ) );							// shape.
								
				//
				// Save tags.
				//
				$tags
					= array_unique(
						array_diff(
							array_merge(
								array_keys( $object ),
								$object[ kTAG_OBJECT_TAGS ] ),
							$exclude ) );
				
				//
				// Cache tags.
				//
				$this->cacheTag( $wrapper, $tags, $theLanguage );
							
				//
				// Allocate object in results.
				//
				$results[ $object[ kTAG_NID ] ] = Array();
				
				//
				// Process object.
				//
				$this->formatObject(
					$wrapper,
					$tags,
					$object,
					$results[ $object[ kTAG_NID ] ],
					$theLanguage );
			
			} // Iterating objects.
		
			//
			// Signal processed.
			//
			$this->mProcessed = TRUE;
	
		} // Not processed.
	
		return $this->mResults;														// ==>
	
	} // format.

	 
	/*===================================================================================
	 *	table																			*
	 *==================================================================================*/

	/**
	 * Format and return table results
	 *
	 * This method will iterate the results set using the provided field set, the method
	 * will return the resulting array.
	 *
	 * @param array					$theFields			List of fields.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access public
	 * @return array				Aggregated results.
	 */
	public function table( $theFields, $theLanguage = NULL )
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
			$results = & $this->mResults[ kAPI_RESPONSE_RESULTS ];
	
			//
			// Iterate.
			//
			foreach( $this->mIterator as $object )
			{
				//
				// Cache tags.
				//
				$this->cacheTag( $wrapper, $theFields, $theLanguage );
							
				//
				// Allocate object in results.
				//
				$results[ $object[ kTAG_NID ] ] = Array();
				
				//
				// Process object.
				//
				$this->tableObject(
					$wrapper,
					$theFields,
					$object,
					$results[ $object[ kTAG_NID ] ],
					$theLanguage );
			
			} // Iterating objects.
		
			//
			// Signal processed.
			//
			$this->mProcessed = TRUE;
	
		} // Not processed.
	
		return $this->mResults;														// ==>
	
	} // table.

	 

/*=======================================================================================
 *																						*
 *							PROTECTED PROCESSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	tableObject																		*
	 *==================================================================================*/

	/**
	 * Table object
	 *
	 * This method will parse the provided object.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theTags			Object tags.
	 * @param array					$theObject			Object array reference.
	 * @param array					$theResults			Results array reference.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function tableObject( Wrapper $theWrapper, &$theTags,
														 &$theObject,
														 &$theResults,
														  $theLanguage )
	{
		//
		// Iterate object properties.
		//
		foreach( $theObject as $key => $value )
		{
			//
			// Handle published tags.
			//
			if( in_array( $key, $theTags ) )
			{
				//
				// Allocate property.
				//
				$theResults[ $key ] = Array();
				
				//
				// Format property.
				//
				$this->formatProperty(
					$theWrapper,
					$theTags,
					$theResults[ $key ],
					$value,
					$key,
					$theLanguage );
			
			} // Publishable tag.

		} // Iterated object properties.
		
	} // tableObject.

	 
	/*===================================================================================
	 *	formatObject																	*
	 *==================================================================================*/

	/**
	 * Format object
	 *
	 * This method will parse the provided object.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theTags			Object tags.
	 * @param array					$theObject			Object array reference.
	 * @param array					$theResults			Results array reference.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function formatObject( Wrapper $theWrapper, &$theTags,
														  &$theObject,
														  &$theResults,
														   $theLanguage )
	{
		//
		// Iterate object properties.
		//
		foreach( $theObject as $key => $value )
		{
			//
			// Handle published tags.
			//
			if( in_array(
					$key,
					array_merge(
						$theTags,
						PersistentObject::GetReferenceCounts() ) ) )
			{
				//
				// Allocate property.
				//
				$theResults[ $key ] = Array();
				
				//
				// Format property.
				//
				$this->formatProperty(
					$theWrapper,
					$theTags,
					$theResults[ $key ],
					$value,
					$key,
					$theLanguage );
			
			} // Publishable tag.

		} // Iterated object properties.
		
	} // formatObject.

	 
	/*===================================================================================
	 *	formatProperty																	*
	 *==================================================================================*/

	/**
	 * Format property
	 *
	 * This method will parse the provided property.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theTags			Object tags.
	 * @param array					$theResults			Results reference.
	 * @param mixed					$theValue			Property value.
	 * @param int					$theTag				Tag sequence number.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function formatProperty( Wrapper $theWrapper, &$theTags,
															&$theResults,
															&$theValue,
															 $theTag,
															 $theLanguage )
	{
		//
		// Init local storage.
		//
		$tag = $this->mCache[ Tag::kSEQ_NAME ][ $theTag ];
		
		//
		// Load label.
		//
		$theResults[ kAPI_PARAM_RESPONSE_FRMT_NAME ] = $tag[ kTAG_LABEL ];
		
		//
		// Load description.
		//
		if( array_key_exists( kTAG_DESCRIPTION, $tag ) )
			$theResults[ kAPI_PARAM_RESPONSE_FRMT_INFO ] = $tag[ kTAG_DESCRIPTION ];
		
		//
		// Handle lists.
		//
		if( array_key_exists( kTAG_DATA_KIND, $tag )
		 && in_array( kTYPE_LIST, $tag[ kTAG_DATA_KIND ] ) )
		{
			//
			// Handle structures
			//
			if( $tag[ kTAG_DATA_TYPE ] == kTYPE_STRUCT )
			{
				//
				// Allocate data.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
				$ref = & $theResults[ kAPI_PARAM_RESPONSE_FRMT_DOCU ];
				
				//
				// Iterate structures.
				//
				foreach( $theValue as $value )
				{
					//
					// Allocate structure.
					//
					$ref[] = Array();
					
					//
					// Parse structure.
					//
					$this->formatObject(
						$theWrapper,
						$theTags,
						$value,
						$ref[ count( $ref ) - 1 ],
						$theLanguage );
				
				} // Iterating structures.
			
			} // Structure.
			
			//
			// Handle scalars.
			//
			else
			{
				//
				// Allocate data.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DATA ] = Array();
				
				//
				// Load data.
				//
				$this->resolveProperty(
					$theWrapper,
					$theTags,
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DATA ],
					$theValue,
					$theTag,
					$theLanguage );
			
			} // Scalar.
					
		} // List.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Handle structures
			//
			if( $tag[ kTAG_DATA_TYPE ] == kTYPE_STRUCT )
			{
				//
				// Allocate data.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
				
				//
				// Parse structure.
				//
				$this->formatObject(
					$theWrapper,
					$theTags,
					$theValue,
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DOCU ],
					$theLanguage );
			
			} // Structure.
			
			//
			// Handle scalars.
			//
			else
			{
				//
				// Allocate data.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DATA ] = NULL;
				$this->resolveProperty(
					$theWrapper,
					$theTags,
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DATA ],
					$theValue,
					$theTag,
					$theLanguage );
			
			} // Scalar.
		
		} // Scalar.
			
	} // formatProperty.

	 
	/*===================================================================================
	 *	resolveProperty																	*
	 *==================================================================================*/

	/**
	 * Resolve property
	 *
	 * This method will resolve the provided property.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theTags			Object tags.
	 * @param array					$theResults			Results reference.
	 * @param mixed					$theValue			Property value.
	 * @param int					$theTag				Tag sequence number.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function resolveProperty( Wrapper $theWrapper, &$theTags,
															 &$theResults,
															 &$theValue,
															  $theTag,
															  $theLanguage )
	{
		//
		// Init local storage.
		//
		$tag = $this->mCache[ Tag::kSEQ_NAME ][ $theTag ];
		
		//
		// Handle arrays.
		//
		if( is_array( $theValue ) )
		{
			//
			// Iterate list.
			//
			$theResults = Array();
			foreach( $theValue as $key => $value )
			{
				$theResults[ $key ] = NULL;
				$this->resolveProperty(
					$theWrapper,
					$theTags,
					$theResults[ $key ],
					$value,
					$theTag,
					$theLanguage );
			}
		
		} // List of values.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Parse by type.
			//
			switch( $tag[ kTAG_DATA_TYPE ] )
			{
				case kTYPE_ENUM:
				case kTYPE_SET:
					$this->resolveEnum( $theWrapper, $theResults, $theValue, $theLanguage );
					break;
				
				case kTYPE_MIXED:
				default:
					$theResults = $theValue;
					break;
			}
		
		} // Scalar.
			
	} // resolveProperty.

	 
	/*===================================================================================
	 *	resolveEnum																		*
	 *==================================================================================*/

	/**
	 * Resolve enumeration
	 *
	 * This method will resolve the provided enumerated value.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theResults			Results reference.
	 * @param mixed					$theValue			Enumerated value.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function resolveEnum( Wrapper $theWrapper, &$theResults,
														  $theValue,
														  $theLanguage )
	{
		//
		// Cache term.
		//
		$this->cacheTerm( $theWrapper, $theValue, $theLanguage );
		
		//
		// Reference term.
		//
		$term = & $this->mCache[ Term::kSEQ_NAME ][ $theValue ];
		
		//
		// Allocate data.
		//
		$theResults = Array();
		
		//
		// Set label.
		//
		$theResults[ kAPI_PARAM_RESPONSE_FRMT_NAME ] = $term[ kTAG_LABEL ];
		
		//
		// Set definition.
		//
		if( array_key_exists( kTAG_DEFINITION, $term ) )
			$theResults[ kAPI_PARAM_RESPONSE_FRMT_INFO ] = $term[ kTAG_DEFINITION ];
			
	} // resolveEnum.

	 

/*=======================================================================================
 *																						*
 *								PROTECTED CACHING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	cacheTag																		*
	 *==================================================================================*/

	/**
	 * Load tag in cache
	 *
	 * This method will load the provided tag into the cache, if not yet there.
	 *
	 * The provided parameter may be an array.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theTag				Tag native identifier or sequence.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function cacheTag( Wrapper $theWrapper, $theTag, $theLanguage )
	{
		//
		// Handle array.
		//
		if( is_array( $theTag ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			
			//
			// Cache tags.
			//
			foreach( $theTag as $tag )
			{
				//
				// Get tag.
				//
				$tag = $this->cacheTag( $theWrapper, $tag, $theLanguage );
				
				//
				// Add tag.
				//
				if( ! array_key_exists( $tag[ kTAG_ID_SEQUENCE ], $result ) )
					$result[ $tag[ kTAG_ID_SEQUENCE ] ] = $tag;
			
			} // Iterating tags.
		
		} // Provided list of tags.
		
		//
		// Handle scalar tag.
		//
		else
		{
			//
			// Convert to sequence number.
			//
			if( (! is_int( $theTag ))
			 && (! ctype_digit( $theTag )) )
				$theTag = $theWrapper->getSerial( $theTag, TRUE );
		
			//
			// Check collection.
			//
			if( ! array_key_exists( Tag::kSEQ_NAME, $this->mCache ) )
				$this->mCache[ Tag::kSEQ_NAME ] = Array();
		
			//
			// Add tag.
			//
			if( ! array_key_exists( $theTag, $this->mCache[ Tag::kSEQ_NAME ] ) )
			{
				//
				// Get object.
				//
				$this->mCache[ Tag::kSEQ_NAME ][ $theTag ]
					= Tag::ResolveCollection(
						Tag::ResolveDatabase( $theWrapper, TRUE ) )
							->matchOne( array( kTAG_ID_SEQUENCE => $theTag ),
										kQUERY_ARRAY,
										array( kTAG_LABEL => TRUE,
											   kTAG_DESCRIPTION => TRUE,
											   kTAG_DATA_TYPE => TRUE,
											   kTAG_DATA_KIND => TRUE ) );
			
				//
				// Handle default language.
				//
				$this->mCache[ Tag::kSEQ_NAME ][ $theTag ][ kTAG_LABEL ]
					= OntologyObject::SelectLanguageString(
						$this->mCache[ Tag::kSEQ_NAME ][ $theTag ][ kTAG_LABEL ],
						$theLanguage );
				if( array_key_exists( kTAG_DESCRIPTION,
									  $this->mCache[ Tag::kSEQ_NAME ][ $theTag ] ) )
					$this->mCache[ Tag::kSEQ_NAME ][ $theTag ][ kTAG_DESCRIPTION ]
						= OntologyObject::SelectLanguageString(
							$this->mCache[ Tag::kSEQ_NAME ][ $theTag ][ kTAG_DESCRIPTION ],
							$theLanguage );
		
			} // New entry.
		
		} // Provided scalar tag.
		
	} // cacheTag.

	 
	/*===================================================================================
	 *	cacheTerm																		*
	 *==================================================================================*/

	/**
	 * Load term in cache
	 *
	 * This method will load the provided term into the cache, if not yet there.
	 *
	 * The provided parameter may be an array.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theTerm			Term native identifier.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function cacheTerm( Wrapper $theWrapper, $theTerm, $theLanguage )
	{
		//
		// Handle array.
		//
		if( is_array( $theTerm ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			
			//
			// Cache terms.
			//
			foreach( $theTerm as $term )
			{
				//
				// Get term.
				//
				$term = $this->cacheTerm( $theWrapper, $term, $theLanguage );
				
				//
				// Add term.
				//
				if( ! array_key_exists( $term[ kTAG_NID ], $result ) )
					$result[ $term[ kTAG_NID ] ] = $term;
			
			} // Iterating terms.
		
		} // Provided list of terms.
		
		//
		// Handle scalar term.
		//
		else
		{
			//
			// Check collection.
			//
			if( ! array_key_exists( Term::kSEQ_NAME, $this->mCache ) )
				$this->mCache[ Term::kSEQ_NAME ] = Array();
		
			//
			// Add term.
			//
			if( ! array_key_exists( $theTerm, $this->mCache[ Term::kSEQ_NAME ] ) )
			{
				//
				// Get object.
				//
				$this->mCache[ Term::kSEQ_NAME ][ $theTerm ]
					= Term::ResolveCollection(
						Term::ResolveDatabase( $theWrapper, TRUE ) )
							->matchOne( array( kTAG_NID => $theTerm ),
										kQUERY_ARRAY,
										array( kTAG_LABEL => TRUE,
											   kTAG_DEFINITION => TRUE ) );
			
				//
				// Handle default language.
				//
				$this->mCache[ Term::kSEQ_NAME ][ $theTerm ][ kTAG_LABEL ]
					= OntologyObject::SelectLanguageString(
						$this->mCache[ Term::kSEQ_NAME ][ $theTerm ][ kTAG_LABEL ],
						$theLanguage );
				if( array_key_exists( kTAG_DEFINITION,
									  $this->mCache[ Term::kSEQ_NAME ][ $theTerm ] ) )
					$this->mCache[ Term::kSEQ_NAME ][ $theTerm ][ kTAG_DEFINITION ]
						= OntologyObject::SelectLanguageString(
							$this->mCache[ Term::kSEQ_NAME ][ $theTerm ][ kTAG_DEFINITION ],
							$theLanguage );
		
			} // New entry.
		
		} // Provided scalar term.
		
	} // cacheTerm.

	 

} // class ResultFormatter.


?>
