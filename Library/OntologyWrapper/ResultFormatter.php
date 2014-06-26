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
 * The duty of this class is to format the results returned by services according to the
 * format type indicated in the service {@link kAPI_PARAM_DATA} parameter.
 *
 * The class will fill two of the main blocks of the service response:
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
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: This element holds the data property
 *		display string, or list of display elements.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_LINK}</tt>: This element holds the URL for
 *		properties that represent an internet link.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_SERV}</tt>: If the property is an object
 *		reference, this element holds the list of parameters that can be used to call the
 *		service that will retrieve the data of the referenced object.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_SMAP}</tt>: If the property is a shape, this
 *		element holds the list of parameters that can be used to call the service that will
 *		retrieve the marker information corresponding to the current shape.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DOCU}</tt>: If the property is a struct, this
 *		element holds the sub-document nested structure.
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
								( is_array( $object ) ) ? array_keys( $object )
														: $object->arrayKeys(),
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
	 * This method will iterate the provided object's properties and load the formatted
	 * results into the provided results container.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theTags</b>: List of publishable tag sequence numbers, only offsets
	 *		belonging to this list will be considered.
	 *	<li><b>$theObject</b>: Reference to the source object or structure.
	 *	<li><b>$theResults</b>: Reference to the results container, in this case it will be
	 *		the {@link kAPI_RESPONSE_RESULTS} element of the service response.
	 *	<li><b>$theLanguage</b>: Default language code.
	 * </ul>
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
				// Allocate property result.
				//
				$theResults[ $key ] = Array();
				
				//
				// Format property result.
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
	 * This method will load the property label, description and value into the provided
	 * containser referenceo.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theTags</b>: List of publishable tag sequence numbers, only offsets
	 *		belonging to this list will be considered.
	 *	<li><b>$theResults</b>: Reference to the results container, in this case it will be
	 *		the array that will contain the current property formatted value elements.
	 *	<li><b>$theValue</b>: Reference to the source property.
	 *	<li><b>$theTag</b>: Property tag sequence number.
	 *	<li><b>$theLanguage</b>: Default language code.
	 * </ul>
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
		// Save property tag.
		//
		$tag = $this->mCache[ Tag::kSEQ_NAME ][ $theTag ];
		
		//
		// Set property label.
		//
		$theResults[ kAPI_PARAM_RESPONSE_FRMT_NAME ] = $tag[ kTAG_LABEL ];
		
		//
		// Set property description.
		//
		if( array_key_exists( kTAG_DESCRIPTION, $tag ) )
			$theResults[ kAPI_PARAM_RESPONSE_FRMT_INFO ] = $tag[ kTAG_DESCRIPTION ];
		
		//
		// Set property value.
		//
		$this->formatPropertyValue(
			$theWrapper, $theTags, $theResults, $theValue, $tag, $theLanguage );
			
	} // formatProperty.

	 
	/*===================================================================================
	 *	formatPropertyValue																*
	 *==================================================================================*/

	/**
	 * Format property value
	 *
	 * This method will load the provided property value elements into the provided
	 * container reference.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theTags</b>: List of publishable tag sequence numbers, only offsets
	 *		belonging to this list will be considered.
	 *	<li><b>$theResults</b>: Reference to the results container, in this case it will be
	 *		the array that will contain the current property formatted value elements.
	 *	<li><b>$theValue</b>: Reference to the source property.
	 *	<li><b>$theTag</b>: Property tag sequence number.
	 *	<li><b>$theLanguage</b>: Default language code.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theTags			Object tags.
	 * @param array					$theResults			Results reference.
	 * @param mixed					$theValue			Property value.
	 * @param array					$theTag				Tag object.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function formatPropertyValue( Wrapper $theWrapper, &$theTags,
																 &$theResults,
																 &$theValue,
																 &$theTag,
																  $theLanguage )
	{
		//
		// Handle structures.
		//
		if( $theTag[ kTAG_DATA_TYPE ] == kTYPE_STRUCT )
		{
			//
			// Save container structure index offset.
			//
			$offset = ( array_key_exists( kTAG_TAG_STRUCT_IDX, $theTag ) )
					? $theWrapper->getSerial( $theTag[ kTAG_TAG_STRUCT_IDX ] )
					: NULL;
			
			//
			// Handle lists.
			//
			if( array_key_exists( kTAG_DATA_KIND, $theTag )
			 && in_array( kTYPE_LIST, $theTag[ kTAG_DATA_KIND ] ) )
			{
				//
				// Allocate structures list.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
				$list_ref = & $theResults[ kAPI_PARAM_RESPONSE_FRMT_DOCU ];
				
				//
				// Iterate structures.
				//
				foreach( $theValue as $value )
				{
					//
					// Allocate structure element.
					//
					$list_ref[] = Array();
					$struct_ref = & $list_ref[ count( $list_ref ) - 1 ];
					
					//
					// Set result display string.
					//
					if( array_key_exists( $offset, $value ) )
						$struct_ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
							= $value[ $offset ];
				
					//
					// Allocate result structure.
					//
					$struct_ref[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
				
					//
					// Set result structure.
					//
					$this->formatObject(
						$theWrapper,
						$theTags,
						$value,
						$struct_ref[ kAPI_PARAM_RESPONSE_FRMT_DOCU ],
						$theLanguage );
				
				} // Iterating structures list.
			
			} // Structures list.
			
			//
			// Handle scalar structure.
			//
			else
			{
				//
				// Set result display string.
				//
				if( array_key_exists( $offset, $theValue ) )
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $theValue[ $offset ];
				
				//
				// Allocate result structure.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
				
				//
				// Set result structure.
				//
				$this->formatObject(
					$theWrapper,
					$theTags,
					$theValue,
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DOCU ],
					$theLanguage );
			
			} // Scalar structure.
		
		} // Structure.
		
		//
		// Handle scalars.
		//
		else
			$this->resolveProperty(
				$theWrapper,
				$theTags,
				$theResults,
				$theValue,
				$theTag,
				$theLanguage );
			
	} // formatPropertyValue.

	 
	/*===================================================================================
	 *	resolveProperty																	*
	 *==================================================================================*/

	/**
	 * Resolve property
	 *
	 * This method will parse the provided property value and load the formatted result into
	 * the provided containser reference.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theResults</b>: Reference to the results container, in this case it will be
	 *		the array that currently contains the property label and description.
	 *	<li><b>$theValue</b>: Reference to the source property.
	 *	<li><b>$theTag</b>: Property tag object.
	 *	<li><b>$theLanguage</b>: Default language code.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theResults			Results reference.
	 * @param mixed					$theValue			Property value.
	 * @param array					$theTag				Tag object.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function resolveProperty( Wrapper $theWrapper, &$theTags,
															 &$theResults,
															 &$theValue,
															 &$theTag,
															  $theLanguage )
	{
		//
		// Parse by property data  type.
		//
		switch( $theTag[ kTAG_DATA_TYPE ] )
		{
			case kTYPE_ENUM:
			case kTYPE_SET:
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				$this->resolveEnum(
					$theWrapper,
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ],
					$theTag,
					$theValue,
					$theLanguage );
				break;
			
			case kTYPE_TYPED_LIST:
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				$this->resolveTypedList(
					$theWrapper,
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ],
					$theTag,
					$theValue,
					$theLanguage );
				break;
			
			case kTYPE_REF_UNIT:
				$this->resolveUnitReference(
					$theWrapper, $theResults, $theTag, $theValue, $theLanguage );
				break;
			
			case kTYPE_BOOLEAN:
				$this->resolveBoolean(
					$theWrapper, $theResults, $theTag, $theValue, $theLanguage );
				break;
			
			case kTYPE_MIXED:
			case kTYPE_STRING:
			case kTYPE_INT:
			case kTYPE_FLOAT:
			default:
				$this->resolveScalar(
					$theWrapper,
					$theResults,
					$theTag,
					$theValue,
					$theLanguage );
				break;
		
		} // Parsed by type.
			
	} // resolveProperty.

	 
	/*===================================================================================
	 *	resolveEnum																		*
	 *==================================================================================*/

	/**
	 * Resolve enumeration
	 *
	 * This method will resolve, parse and load the provided enumerated value into the
	 * provided results container reference.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theResults</b>: Reference to the results container, in this case it
	 *		references the {@link kAPI_PARAM_RESPONSE_FRMT_DISP} element of the results.
	 *	<li><b>$theTag</b>: Property tag object.
	 *	<li><b>$theValue</b>: Reference to the source property.
	 *	<li><b>$theLanguage</b>: Default language code.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theResults			Results reference.
	 * @param array					$theTag				Property tag.
	 * @param mixed					$theValue			Enumerated value.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function resolveEnum( Wrapper $theWrapper, &$theResults,
														 &$theTag,
														 &$theValue,
														  $theLanguage )
	{
		//
		// Handle list.
		//
		if( is_array( $theValue ) )
		{
			//
			// Handle single value.
			//
			if( count( $theValue ) == 1 )
				$this->resolveEnum(
					$theWrapper,
					$theResults,
					$theTag,
					$theValue[ 0 ],
					$theLanguage );
			
			//
			// Handle multiple values.
			//
			else
			{
				//
				// Iterate list.
				//
				foreach( $theValue as $value )
				{
					//
					// Allocate element.
					//
					$theResults[] = Array();
				
					//
					// Load element.
					//
					$this->resolveEnum(
						$theWrapper,
						$theResults[ count( $theResults ) - 1 ],
						$theTag,
						$value,
						$theLanguage );
			
				} // Iterating values.
			
			} // More than one value.
			
		} // List of values.
		
		//
		// Handle scalar.
		//
		else
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
			// Set label.
			//
			$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $term[ kTAG_LABEL ];
		
			//
			// Set definition.
			//
			if( array_key_exists( kTAG_DEFINITION, $term ) )
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_INFO ] = $term[ kTAG_DEFINITION ];
		
		} // Scalar value.
		
	} // resolveEnum.

	 
	/*===================================================================================
	 *	resolveTypedList																*
	 *==================================================================================*/

	/**
	 * Resolve typed list
	 *
	 * This method will resolve, parse and load the provided typed list value into the
	 * provided results container reference.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theResults</b>: Reference to the results container, in this case it
	 *		references the {@link kAPI_PARAM_RESPONSE_FRMT_DISP} element of the results.
	 *	<li><b>$theTag</b>: Property tag object.
	 *	<li><b>$theValue</b>: Reference to the source property.
	 *	<li><b>$theLanguage</b>: Default language code.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theResults			Results reference.
	 * @param array					$theTag				Property tag.
	 * @param mixed					$theValue			Enumerated value.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function resolveTypedList( Wrapper $theWrapper, &$theResults,
															  &$theTag,
															  &$theValue,
															   $theLanguage )
	{
		//
		// Handle single element.
		//
		if( count( $theValue ) == 1 )
		{
			//
			// Handle text value.
			//
			if( array_key_exists( kTAG_TEXT, $theValue[ 0 ] ) )
			{
				//
				// Handle typed element.
				//
				if( array_key_exists( kTAG_TYPE, $theValue[ 0 ] ) )
				{
					//
					// Set type.
					//
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
						= $theValue[ 0 ][ kTAG_TYPE ];
			
					//
					// Set text value.
					//
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $theValue[ 0 ][ kTAG_TEXT ];
				
				} // Typed.
				
				//
				// Handle typeless element.
				//
				else
					$theResults = $theValue[ 0 ][ kTAG_TEXT ];
				
			} // Text value.
			
			//
			// Handle URL value.
			//
			elseif( array_key_exists( kTAG_URL, $theValue[ 0 ] ) )
			{
				//
				// Set display to type.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= ( array_key_exists( kTAG_TYPE, $theValue[ 0 ] ) )
					? $theValue[ 0 ][ kTAG_TYPE ]
					: $theValue[ 0 ][ kTAG_URL ];
				
				//
				// Set value.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_LINK ]
					= $theValue[ 0 ][ kTAG_URL ];
			
			} // URL value.
		
		} // Single element.
		
		//
		// Handle multiple elements.
		//
		elseif( count( $theValue ) > 1 )
		{
			//
			// Iterate elements.
			//
			foreach( $theValue as $value )
			{
				//
				// Allocate element.
				//
				$theResults[] = Array();
				$ref = & $theResults[ count( $theResults ) - 1 ];
				
				//
				// Handle text value.
				//
				if( array_key_exists( kTAG_TEXT, $value ) )
				{
					//
					// Set type.
					//
					if( array_key_exists( kTAG_TYPE, $value ) )
						$ref[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
							= $value[ kTAG_TYPE ];
					
					//
					// Set value.
					//
					$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $value[ kTAG_TEXT ];
				
				} // Text value.
			
				//
				// Handle URL value.
				//
				elseif( array_key_exists( kTAG_URL, $value ) )
				{
					//
					// Set display.
					//
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= ( array_key_exists( kTAG_TYPE, $theValue[ 0 ] ) )
						? $value[ kTAG_TYPE ]
						: $value[ kTAG_URL ];
				
					//
					// Set value.
					//
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_LINK ]
						= $value[ kTAG_URL ];
			
				} // URL value.
			
			} // Iterating elements.
		
		} // Multiple elements.
			
	} // resolveTypedList.

	 
	/*===================================================================================
	 *	resolveUnitReference															*
	 *==================================================================================*/

	/**
	 * Resolve unit reference
	 *
	 * This method will resolve, parse and load the provided unit reference into the
	 * provided results container reference.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theResults</b>: Reference to the results container, in this case it
	 *		references the results container featuring the label and description.
	 *	<li><b>$theTag</b>: Property tag object.
	 *	<li><b>$theValue</b>: Reference to the source property.
	 *	<li><b>$theLanguage</b>: Default language code.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theResults			Results reference.
	 * @param array					$theTag				Property tag.
	 * @param mixed					$theValue			Enumerated value.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function resolveUnitReference( Wrapper $theWrapper, &$theResults,
																  &$theTag,
																  &$theValue,
																   $theLanguage )
	{
		//
		// Handle references list.
		//
		if( is_array( $theValue ) )
		{
			//
			// Handle single element.
			//
			if( count( $theValue ) == 1 )
				$this->resolveUnitReference(
					$theWrapper, 
					$theResults,
					$theTag,
					$theValue [ 0 ],
					$theLanguage );
			
			//
			// handle multiple references.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Allocate list.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				$list = & $theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
				
				//
				// Iterate references.
				//
				foreach( $theValue as $value )
				{
					//
					// Allocate result.
					//
					$list[] = Array();
					
					//
					// Set value.
					//
					$this->resolveUnitReference(
						$theWrapper, 
						$list[ count( $list ) - 1 ],
						$theTag,
						$value,
						$theLanguage );
				
				} // Iterating references.
			
			} // Multiple references.
		
		} // References list.
		
		//
		// Handle scalar reference.
		//
		else
		{
			//
			// Load referenced object.
			//
			$object
				= UnitObject::ResolveCollection(
					UnitObject::ResolveDatabase(
						$theWrapper ) )
						->matchOne(
							array( kTAG_NID => $theValue ),
							kQUERY_OBJECT );
			if( $object !== NULL )
			{
				//
				// Set object name.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= $object->getName( $theLanguage );
				
				//
				// Allocate service.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_SERV ] = Array();
				$ref = & $theResults[ kAPI_PARAM_RESPONSE_FRMT_SERV ];
				
				//
				// Set service operation and language.
				//
				$ref[ kAPI_REQUEST_OPERATION ] = kAPI_OP_GET_UNIT;
				$ref[ kAPI_REQUEST_LANGUAGE ] = $theLanguage;
				
				//
				// Allocate service parameters.
				//
				$ref[ kAPI_REQUEST_PARAMETERS ] = Array();
				$ref = & $ref[ kAPI_REQUEST_PARAMETERS ];
				
				//
				// Set object identifier and data format.
				//
				$ref[ kAPI_PARAM_ID ] = $theValue;
				$ref[ kAPI_PARAM_DATA ] = kAPI_RESULT_ENUM_DATA_FORMAT;
			
			} // Found referenced object.
		
		} // Scalar reference.
		
	} // resolveUnitReference.

	 
	/*===================================================================================
	 *	resolveBoolean																	*
	 *==================================================================================*/

	/**
	 * Resolve boolean
	 *
	 * This method will resolve, parse and load the provided boolean value into the provided
	 * results container reference.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theResults</b>: Reference to the results container, in this case it
	 *		references the results container featuring the label and description.
	 *	<li><b>$theTag</b>: Property tag object.
	 *	<li><b>$theValue</b>: Reference to the source property.
	 *	<li><b>$theLanguage</b>: Default language code.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theResults			Results reference.
	 * @param array					$theTag				Property tag.
	 * @param mixed					$theValue			Enumerated value.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function resolveBoolean( Wrapper $theWrapper, &$theResults,
															&$theTag,
															&$theValue,
															 $theLanguage )
	{
		//
		// Handle array.
		//
		if( is_array( $theValue ) )
		{
			//
			// Handle single value.
			//
			if( count( $theValue ) == 1 )
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= ( $theValue[ 0 ] )
					? 'Yes'
					: 'No';
			
			//
			// Handle multiple values.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Allocate display elements.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				$ref = & $theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
			
				//
				// Iterate list.
				//
				foreach( $theValue as $value )
					$ref[] = ( $value )
						   ? 'Yes'
						   : 'No';
			
			} // More than one value.
			
		} // List of values.
		
		//
		// Handle scalar.
		//
		else
			$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
				= ( $theValue )
				? 'Yes'
				: 'No';
		
	} // resolveBoolean.

	 
	/*===================================================================================
	 *	resolveScalar																	*
	 *==================================================================================*/

	/**
	 * Resolve scalar
	 *
	 * This method will resolve, parse and load the provided scalar value into the provided
	 * results container reference.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theResults</b>: Reference to the results container, in this case it
	 *		references the results container featuring the label and description.
	 *	<li><b>$theTag</b>: Property tag object.
	 *	<li><b>$theValue</b>: Reference to the source property.
	 *	<li><b>$theLanguage</b>: Default language code.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theResults			Results reference.
	 * @param array					$theTag				Property tag.
	 * @param mixed					$theValue			Enumerated value.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function resolveScalar( Wrapper $theWrapper, &$theResults,
														   &$theTag,
														   &$theValue,
															$theLanguage )
	{
		//
		// Handle array.
		//
		if( is_array( $theValue ) )
		{
			//
			// Handle single value.
			//
			if( count( $theValue ) == 1 )
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= $theValue[ 0 ];
			
			//
			// Handle multiple values.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Allocate display elements.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				$ref = & $theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
			
				//
				// Iterate list.
				//
				foreach( $theValue as $value )
					$ref[] = $value;
			
			} // More than one value.
			
		} // List of values.
		
		//
		// Handle scalar.
		//
		else
			$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
				= $theValue;
		
	} // resolveScalar.

	 

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
