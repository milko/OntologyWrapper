<?php

/**
 * ServiceDataFormatter.php
 *
 * This file contains the definition of the {@link ServiceDataFormatter} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ContainerObject;
use OntologyWrapper\IteratorObject;

/*=======================================================================================
 *																						*
 *								ServiceDataFormatter.php								*
 *																						*
 *======================================================================================*/

/**
 * API.
 *
 * This file contains the API definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );

/**
 * Service results formatter
 *
 * The duty of this class is to serialise query data returned by the services into a
 * formatted set of data.
 *
 * The class is instantiated with three items:
 *
 * <ul>
 *	<li><tt>{@link iterator()}</tt>: The {@link IteratorObject} instance returned by a
 *		service.
 *	<li><tt>{@link format()}</tt>: The data format indicator.
 *	<li><tt>{@link language()}</tt>: The default language for strings.
 * </ul>
 *
 * Once the object is instantiated it can be serialised with the {@link serialise()} method
 * which will populate two data members:
 *
 * <ul>
 *	<li><tt>{@link paging()}</tt>: The paging information, which is an array structured as
 *		follows:
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
 *	<li><tt>{@link data()}</tt>: The formatted iterator's data values, which is an array
 *		containing all the objects fetaured by the provided iterator, each array element is
 *		indexed by the object's native identifier and the value is formatted as a nested set
 *		of arrays containing the following items:
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: This element holds the data
 *			property name or label, both at the property level as well as the value level.
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: This element holds the data
 *			property and value information or description.
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: This element holds the data
 *			property display string, or list of display elements.
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_LINK}</tt>: This element holds the URL for
 *			properties that represent an internet links.
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_SERV}</tt>: If the property is an object
 *			reference, this element holds the list of parameters that can be used to call
 *			the service that will retrieve the data of the referenced object.
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DOCU}</tt>: If the property is a
 *			sub-structure, this element will hold the sub-structure data formatted in the
 *			same way as the root structure.
 *	 </ul>
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 26/06/2014
 */
class ServiceDataFormatter
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
	 * Format.
	 *
	 * This protected data member holds the data format, it is an enumerated value that can
	 * take one of the following values:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_RESULT_ENUM_DATA_COLUMN}</tt>: <em>Column view.</em> This
	 *		represents a formatted view of the object properties corresponding to the
	 *		{@link UnitObject::ListOffsets()} set of properties corresponding to the
	 *		object's domain; in this case the class assumes the iterator to contain objects
	 *		belonging to a single domain. In this case the {@link data()} member will be
	 *		an array of elements indexed by the object native identifier, containing the
	 *		formatted object's properties.
	 *	<li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: <em>Aggregated view.</em> This
	 *		represents a view in which all objects are stored in their original format,
	 *		along with all other related objects. In this case the {@link data()} member
	 *		will be an array of elements indexed by collection name containing the list of
	 *		related objects belonging to the current collection.
	 * </ul>
	 *
	 * @var string
	 */
	protected $mFormat = kAPI_RESULT_ENUM_DATA_COLUMN;

	/**
	 * Results.
	 *
	 * This protected data member holds the serialised data for the iterator.
	 *
	 * @var array
	 */
	protected $mResults = Array();

	/**
	 * Cache.
	 *
	 * This protected data member holds the object cache.
	 *
	 * @var array
	 */
	protected $mCache = Array();

	/**
	 * Current object.
	 *
	 * This protected data member holds the iterated current object.
	 *
	 * @var PersistentObject
	 */
	protected $mCurrentObject = NULL;

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
	 * @param IteratorObject		$theIterator		Iterator.
	 * @param array					$theResults			Results array reference.
	 *
	 * @access public
	 */
	public function __construct( IteratorObject $theIterator, &$theResults )
	{
		//
		// Store iterator.
		//
		$this->mIterator = $theIterator;
		
		//
		// Initialise results.
		//
		if( ! is_array( $theResults ) )
			$theResults = Array();
		
		//
		// Init results paging.
		//
		$theResults[ kAPI_RESPONSE_PAGING ]
			= array( kAPI_PAGING_AFFECTED => $theIterator->affectedCount(),
					 kAPI_PAGING_ACTUAL => $theIterator->count(),
					 kAPI_PAGING_SKIP => $theIterator->skip(),
					 kAPI_PAGING_LIMIT => $theIterator->limit() );
		
		//
		// Init results.
		//
		$theResults[ kAPI_RESPONSE_RESULTS ] = Array();
		
		//
		// Save results.
		//
		$this->mResults = & $theResults;

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	formatted																		*
	 *==================================================================================*/

	/**
	 * Format and return results
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
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				
				//
				// Load data.
				//
				$this->resolveProperty(
					$theWrapper,
					$theTags,
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ],
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
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = NULL;
				$this->resolveProperty(
					$theWrapper,
					$theTags,
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ],
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
		// Parse by type.
		//
		switch( $tag[ kTAG_DATA_TYPE ] )
		{
			case kTYPE_ENUM:
			case kTYPE_SET:
				$this->resolveEnum(
					$theWrapper, $theResults, $tag, $theValue, $theLanguage );
				break;
			
			case kTYPE_TYPED_LIST:
				$this->resolveTypedList(
					$theWrapper, $theResults, $tag, $theValue, $theLanguage );
				break;
			
			case kTYPE_BOOLEAN:
				$this->resolveBoolean(
					$theWrapper, $theResults, $tag, $theValue, $theLanguage );
				break;
			
			case kTYPE_MIXED:
			case kTYPE_STRING:
			case kTYPE_INT:
			case kTYPE_FLOAT:
			default:
				$this->resolveScalar(
					$theWrapper, $theResults, $tag, $theValue, $theLanguage );
				break;
		
		} // Parsed by type.
			
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
	 * @param array					$theTag				Property tag.
	 * @param mixed					$theValue			Enumerated value.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function resolveEnum( Wrapper $theWrapper, &$theResults,
														 &$theTag,
														  $theValue,
														  $theLanguage )
	{
		//
		// Handle array.
		//
		if( is_array( $theValue ) )
		{
			//
			// Allocate results.
			//
			$theResults = Array();
			
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
			// Allocate data.
			//
			if( ! is_array( $theResults ) )
				$theResults = Array();
		
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
	 * This method will resolve the provided typed list value.
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
															   $theValue,
															   $theLanguage )
	{
		//
		// Handle single element.
		//
		if( count( $theValue ) == 1 )
		{
			//
			// Handle typeless element.
			//
			if( ! array_key_exists( kTAG_TYPE, $theValue[ 0 ] ) )
			{
				//
				// Handle text.
				//
				if( array_key_exists( kTAG_TEXT, $theValue[ 0 ] ) )
					$theResults = $theValue[ 0 ][ kTAG_TEXT ];
				
				//
				// Handle URL.
				//
				elseif( array_key_exists( kTAG_URL, $theValue[ 0 ] ) )
				{
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = 'View';
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_LINK ]
						= $theValue[ 0 ][ kTAG_URL ];
				
				} // URL.
			
			} // Set data.
			
			//
			// Handle typed value.
			//
			else
			{
				//
				// Set type.
				//
				$theResults[ kAPI_PARAM_RESPONSE_FRMT_NAME ] = $theValue[ 0 ][ kTAG_TYPE ];

				//
				// Handle text.
				//
				if( array_key_exists( kTAG_TEXT, $theValue[ 0 ] ) )
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $theValue[ 0 ][ kTAG_TEXT ];
				
				//
				// Handle URL.
				//
				elseif( array_key_exists( kTAG_URL, $theValue[ 0 ] ) )
				{
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = 'View';
					$theResults[ kAPI_PARAM_RESPONSE_FRMT_LINK ]
						= $theValue[ 0 ][ kTAG_URL ];
				
				} // URL.
			
			} // Typed value.
		
		} // Single element.
		
		//
		// Handle multiple elements.
		//
		else
		{
			//
			// Iterate elements.
			//
			$keys = array_keys( $theValue );
			foreach( $keys as $key )
			{
				//
				// Allocate element.
				//
				$theResults[] = Array();
				$ref = & $theResults[ count( $theResults ) - 1 ];
				
				//
				// Handle typeless element.
				//
				if( ! array_key_exists( kTAG_TYPE, $theValue[ $key ] ) )
					$ref = $theValue[ $key ][ kTAG_TEXT ];
			
				//
				// Handle typed value.
				//
				else
				{
					$ref[ kAPI_PARAM_RESPONSE_FRMT_NAME ] = $theValue[ $key ][ kTAG_TYPE ];
					$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $theValue[ $key ][ kTAG_TEXT ];
			
				} // Typed value.
			
			} // Iterating elements.
		
		} // Multiple elements.
			
	} // resolveTypedList.

	 
	/*===================================================================================
	 *	resolveBoolean																	*
	 *==================================================================================*/

	/**
	 * Resolve boolean
	 *
	 * This method will resolve the provided boolean value.
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
															 $theValue,
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
				$this->resolveScalar(
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
					$this->resolveScalar(
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
			$theResults = ( $theValue ) ? 'Yes' : 'No';
		
	} // resolveBoolean.

	 
	/*===================================================================================
	 *	resolveScalar																	*
	 *==================================================================================*/

	/**
	 * Resolve scalar
	 *
	 * This method will resolve the provided scalar value.
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
															$theValue,
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
				$this->resolveScalar(
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
					$this->resolveScalar(
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
			$theResults = $theValue;
		
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

	 

} // class ServiceDataFormatter.


?>
