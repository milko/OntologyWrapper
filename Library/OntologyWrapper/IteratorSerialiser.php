<?php

/**
 * IteratorSerialiser.php
 *
 * This file contains the definition of the {@link IteratorSerialiser} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ContainerObject;
use OntologyWrapper\ObjectIterator;

/*=======================================================================================
 *																						*
 *									IteratorSerialiser.php								*
 *																						*
 *======================================================================================*/

/**
 * API.
 *
 * This file contains the API definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );

/**
 * Iterator serialiser
 *
 * The duty of this class is to serialise query data returned by the services into a
 * formatted set of data. The goal is to serialise the paged results into a single structure
 * tagged by the API, providing a resolved set of data suited to be handled by user
 * interface clients.
 *
 * The class manages five main elements:
 *
 * <ul>
 *	<li><tt>{@link iterator()}</tt>: The iterator is an object derived from the
 *		{@link mIteratorObject} class which holds the results of a query on the units
 *		collection. The iterator must have been previously paged and sorted.
 *	<li><tt>{@link format()}</tt>: The format in which the iterator is to be serialised in:
 *	 <ul>
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_COLUMN}</tt>: Column view. The
 *			{@link UnitObject::ListOffsets()} method will be used to select the object
 *			properties to be included.
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_FORMAT}</tt>: Formatted view. The data will
 *			contain the set of properties of the objects.
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: Record view. The data will
 *			contain the actual objects and related objects in their original format.
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_MARKER}</tt>: Marker view. The data will
 *			contain marker data for the selected objects.
 *	 </ul>
 *	<li><tt>{@link domain()}</tt>: The domain of the objects featured by the iterator, this
 *		property is required only if the format is {@link kAPI_RESULT_ENUM_DATA_COLUMN},
 *		since in that case all objects should belong to the same domain.
 *	<li><tt>{@link shape()}</tt>: The shape offset, this property is required only if the
 *		format is {@link kAPI_RESULT_ENUM_DATA_MARKER} to indicate which object property
 *		contains the geographic shape.
 *	<li><tt>{@link language()}</tt>: The strings default language.
 * </ul>
 *
 * When requesting the {@link kAPI_RESULT_ENUM_DATA_COLUMN} or the
 * {@link kAPI_RESULT_ENUM_DATA_FORMAT} format, the results will be encoded as an array
 * indexed by object native identifier, containing the object data encoded as follows:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: This element holds the data property
 *		name or label, both at the property level as well as the value level.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: This element holds the data property
 *		and value information or description.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: This element holds the data property
 *		display string, or list of display elements.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_LINK}</tt>: This element holds the URL for
 *		properties that represent an internet links.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_SERV}</tt>: If the property is an object
 *		reference, this element holds the list of parameters that can be used to call the
 *		service that will retrieve the data of the referenced object.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DOCU}</tt>: If the property is a sub-structure,
 *		this element will hold the sub-structure data formatted in the same way as the root
 *		structure.
 * </ul>
 *
 * Once serialised, the iterator data is available via the {@link paging()},
 * {@link dictionary()} and {@link data()} methods which return respectively the paging, the
 * dictionary and the serialised data.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 30/06/2014
 */
class IteratorSerialiser
{
	/**
	 * Iterator.
	 *
	 * This protected data member holds the iterator.
	 *
	 * @var ObjectIterator
	 */
	protected $mIterator = NULL;

	/**
	 * Format.
	 *
	 * This protected data member holds the data format.
	 *
	 * @var string
	 */
	protected $mFormat = NULL;

	/**
	 * Domain.
	 *
	 * This protected data member holds the iterator objects domain.
	 *
	 * @var string
	 */
	protected $mDomain = NULL;

	/**
	 * Shape.
	 *
	 * This protected data member holds the default shape offset as the tag's serial number.
	 *
	 * @var int
	 */
	protected $mShape = NULL;

	/**
	 * Language.
	 *
	 * This protected data member holds the default language.
	 *
	 * @var string
	 */
	protected $mLanguage = NULL;

	/**
	 * Paging.
	 *
	 * This protected data member holds the paging information.
	 *
	 * @var array
	 */
	protected $mPaging = Array();

	/**
	 * Dictionary.
	 *
	 * This protected data member holds the dictionary information.
	 *
	 * @var array
	 */
	protected $mDictionary = Array();

	/**
	 * Data.
	 *
	 * This protected data member holds the serialised data for the iterator.
	 *
	 * @var array
	 */
	protected $mData = Array();

	/**
	 * Cache.
	 *
	 * This protected data member holds the object cache.
	 *
	 * @var array
	 */
	protected $mCache = Array();

	/**
	 * Processed flag.
	 *
	 * This protected data member holds the processed flag, which is set when the iterator
	 * is serialised.
	 *
	 * @var boolean
	 */
	protected $mProcessed = FALSE;

	/**
	 * Traverse sub-structures flag.
	 *
	 * This protected data member holds the flag that determines whether to traverse
	 * sub-substructures.
	 *
	 * @var boolean
	 */
	protected $mStructs = TRUE;

	/**
	 * Current unit.
	 *
	 * This protected data member holds the current unit object.
	 *
	 * @var PersistentObject
	 */
	protected $mCurrentUnit = NULL;

	/**
	 * Hidden tags.
	 *
	 * This protected data member holds the list of tags that will be hidden.
	 *
	 * @var array
	 */
	protected $mHidden = Array();

		

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
	 * The constructor accepts all required object properties, the iterator, format and
	 * language are required, the other parameters may be set via the member accessor
	 * methods and are required depending on the type of format.
	 *
	 * The iterator should have been paged by the caller: in this class we do not handle
	 * paging and sorting, we simply scan the iterator.
	 *
	 * @param ObjectIterator		$theIterator		Iterator.
	 * @param string				$theFormat			Data format.
	 * @param string				$theLanguage		Default language.
	 * @param string				$theDomain			Optional domain for columns.
	 * @param string				$theShape			Optional shape for markers.
	 *
	 * @access public
	 */
	public function __construct( ObjectIterator $theIterator,
												$theFormat,
												$theLanguage,
												$theDomain = NULL,
												$theShape = NULL )
	{
		//
		// Store iterator.
		//
		$this->iterator( $theIterator );
		
		//
		// Store domain.
		//
		if( $theDomain !== NULL )
			$this->domain( $theDomain );
		
		//
		// Store shape.
		//
		if( $theShape !== NULL )
			$this->shape( $theShape );
		
		//
		// Store language.
		//
		$this->language( $theLanguage );
		
		//
		// Store format.
		//
		$this->format( $theFormat );

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC MEMBER INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	iterator																		*
	 *==================================================================================*/

	/**
	 * Manage iterator
	 *
	 * This method can be used to set the iterator, each time the iterator is set, the
	 * {@link paging()} information will be updated.
	 *
	 * If the provided parameter is not an iterator, the method will return the current
	 * iterator.
	 *
	 * Whenever the iterator is modified, the method will reset the processed flag, the
	 * paging information and the serialised data.
	 *
	 * The method does not allow resetting the iterator.
	 *
	 * @param ObjectIterator		$theIterator		Iterator.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return ObjectIterator		Current or previous iterator.
	 */
	public function iterator( $theIterator = NULL, $getOld = FALSE )
	{
		//
		// Handle new iterator.
		//
		if( $theIterator instanceof ObjectIterator )
		{
			//
			// Save old iterator.
			//
			$save = $this->mIterator;
			
			//
			// Set data member.
			//
			$this->mIterator = $theIterator;
		
			//
			// Reset processed flag.
			//
			$this->mProcessed = FALSE;
		
			//
			// Reset serialised data.
			//
			$this->mData = Array();
		
			//
			// Store paging information.
			//
			$this->mPaging
				= array( kAPI_PAGING_AFFECTED => $theIterator->affectedCount(),
						 kAPI_PAGING_ACTUAL => $theIterator->count(),
						 kAPI_PAGING_SKIP => $theIterator->skip(),
						 kAPI_PAGING_LIMIT => $theIterator->limit() );
			
			if( $getOld	)
				return $save;														// ==>
			
			return $theIterator;													// ==>
		
		} // Provided new iterator.
	
		return $this->mIterator;													// ==>
	
	} // iterator.

	 
	/*===================================================================================
	 *	format																			*
	 *==================================================================================*/

	/**
	 * Manage format
	 *
	 * This method can be used to set the format, the method will check if the provided
	 * value is correct.
	 *
	 * Provide <tt>NULL</tt> to retrieve the current format.
	 *
	 * Whenever the format is modified, the method will reset the processed flag, the
	 * paging information and the serialised data.
	 *
	 * The method does not allow resetting the format.
	 *
	 * @param string				$theFormat			Format.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return string				Current or previous format.
	 *
	 * @throws Exception
	 */
	public function format( $theFormat = NULL, $getOld = FALSE )
	{
		//
		// Return current format.
		//
		if( $theFormat === NULL )
			return $this->mFormat;													// ==>
		
		//
		// Save current format.
		//
		$save = $this->mFormat;
		
		//
		// Parse provided format.
		//
		switch( $theFormat )
		{
			case kAPI_RESULT_ENUM_DATA_COLUMN:
				if( $this->mDomain === NULL )
					throw new \Exception(
						"Missing domain." );									// !@! ==>
				break;
				
			case kAPI_RESULT_ENUM_DATA_MARKER:
				if( $this->mShape === NULL )
					throw new \Exception(
						"Missing shape." );										// !@! ==>
				break;
				
			case kAPI_RESULT_ENUM_DATA_FORMAT:
			case kAPI_RESULT_ENUM_DATA_RECORD:
				break;
		}
		
		//
		// Set data member.
		//
		$this->mFormat = $theFormat;
	
		//
		// Reset processed flag.
		//
		$this->mProcessed = FALSE;
	
		//
		// Reset serialised data.
		//
		$this->mData = Array();
		
		if( $getOld	)
			return $save;															// ==>
	
		return $this->mFormat;														// ==>
	
	} // format.

	 
	/*===================================================================================
	 *	domain																			*
	 *==================================================================================*/

	/**
	 * Manage domain
	 *
	 * This method can be used to set the domain, the method will cache the provided domain
	 * and set the data member as a reference to the cached array object.
	 *
	 * Provide <tt>NULL</tt> to retrieve the current domain.
	 *
	 * Whenever the domain is modified, the method will reset the processed flag, the
	 * paging information and the serialised data.
	 *
	 * The method does not allow resetting the domain.
	 *
	 * @param string				$theDomain			Domain.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return array				Current or previous domain array object.
	 */
	public function domain( $theDomain = NULL, $getOld = FALSE )
	{
		//
		// Return current domain.
		//
		if( $theDomain === NULL )
			return $this->mDomain;													// ==>
		
		//
		// Save current format.
		//
		$save = $this->mDomain;
		
		//
		// Cache domain.
		//
		$this->cacheTerm(
			$this->mIterator->collection()->dictionary(),
			$this->mCache,
			$theDomain );
		
		//
		// Set data member.
		//
		$this->mDomain = & $this->mCache[ Term::kSEQ_NAME ][ $theDomain ];
	
		//
		// Reset processed flag.
		//
		$this->mProcessed = FALSE;
	
		//
		// Reset serialised data.
		//
		$this->mData = Array();
		
		if( $getOld	)
			return $save;															// ==>
	
		return $this->mDomain;														// ==>
	
	} // domain.

	 
	/*===================================================================================
	 *	shape																			*
	 *==================================================================================*/

	/**
	 * Manage shape
	 *
	 * This method can be used to set the shape, the method will cache the provided shape
	 * and set the data member as a reference to the cached array object.
	 *
	 * Provide <tt>NULL</tt> to retrieve the current shape.
	 *
	 * Whenever the shape is modified, the method will reset the processed flag, the
	 * paging information and the serialised data.
	 *
	 * The method does not allow resetting the shape.
	 *
	 * @param string				$theShape			Shape.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return array				Current or previous shape array object.
	 */
	public function shape( $theShape = NULL, $getOld = FALSE )
	{
		//
		// Return current shape.
		//
		if( $theShape === NULL )
			return $this->mShape;													// ==>
		
		//
		// Save current shape.
		//
		$save = $this->mShape;
		
		//
		// Cache shape.
		//
		$this->cacheTag(
			$this->mIterator->collection()->dictionary(),
			$this->mCache,
			$theShape );
		
		//
		// Set data member.
		//
		$this->mShape = & $this->mCache[ Tag::kSEQ_NAME ][ $theShape ];
	
		//
		// Reset processed flag.
		//
		$this->mProcessed = FALSE;
	
		//
		// Reset serialised data.
		//
		$this->mData = Array();
		
		if( $getOld	)
			return $save;															// ==>
	
		return $this->mShape;														// ==>
	
	} // shape.

	 
	/*===================================================================================
	 *	language																			*
	 *==================================================================================*/

	/**
	 * Manage language
	 *
	 * This method can be used to set the language, the method will set the language data
	 * member to the provided value.
	 *
	 * Provide <tt>NULL</tt> to retrieve the current language.
	 *
	 * The method does not allow resetting the language.
	 *
	 * @param string				$theLanguage		Language.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return array				Current or previous language.
	 */
	public function language( $theLanguage = NULL, $getOld = FALSE )
	{
		//
		// Return current language.
		//
		if( $theLanguage === NULL )
			return $this->mLanguage;												// ==>
		
		//
		// Save current language.
		//
		$save = $this->mLanguage;
		
		//
		// Set data member.
		//
		$this->mLanguage = $theLanguage;
	
		//
		// Reset processed flag.
		//
		$this->mProcessed = FALSE;
	
		//
		// Reset serialised data.
		//
		$this->mData = Array();
		
		if( $getOld	)
			return $save;															// ==>
	
		return $theLanguage;														// ==>
	
	} // language.

		

/*=======================================================================================
 *																						*
 *									PUBLIC DATA INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	paging																			*
	 *==================================================================================*/

	/**
	 * Return paging information
	 *
	 * This method will return the paging information; if the iterator was not yet
	 * serialised, the method will return <tt>NULL</tt>.
	 *
	 * @access public
	 * @return array				Paging information or <tt>NULL</tt>.
	 */
	public function paging()
	{
		//
		// Check processed flag.
		//
		if( $this->mProcessed )
			return $this->mPaging;													// ==>
		
		return NULL;																// ==>
	
	} // paging.

	 
	/*===================================================================================
	 *	dictionary																		*
	 *==================================================================================*/

	/**
	 * Return dictionary information
	 *
	 * This method will return the dictionary information; if the iterator was not yet
	 * serialised, the method will return <tt>NULL</tt>.
	 *
	 * @access public
	 * @return array				Dictionary information or <tt>NULL</tt>.
	 */
	public function dictionary()
	{
		//
		// Check processed flag.
		//
		if( $this->mProcessed )
			return $this->mDictionary;												// ==>
		
		return NULL;																// ==>
	
	} // dictionary.

	 
	/*===================================================================================
	 *	data																			*
	 *==================================================================================*/

	/**
	 * Return serialised data
	 *
	 * This method will return the serialised data; if the iterator was not yet serialised,
	 * the method will return <tt>NULL</tt>.
	 *
	 * @access public
	 * @return array				Serialised data or <tt>NULL</tt>.
	 */
	public function data()
	{
		//
		// Check processed flag.
		//
		if( $this->mProcessed )
			return $this->mData;													// ==>
		
		return NULL;																// ==>
	
	} // data.

		

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	serialise																		*
	 *==================================================================================*/

	/**
	 * Serialise iterator
	 *
	 * This method will serialise the iterator according to the parameters provided at
	 * instantiation.
	 *
	 * If the iterator was previously serialised, the method will do nothing.
	 *
	 * @access public
	 */
	public function serialise()
	{
		//
		// Check if already serialised.
		//
		if( ! $this->mProcessed )
		{
			//
			// Parse by format.
			//
			switch( $this->mFormat )
			{
				case kAPI_RESULT_ENUM_DATA_COLUMN:
					$this->serialiseColumns();
					break;
			
				case kAPI_RESULT_ENUM_DATA_FORMAT:
					$this->serialiseFormatted();
					break;
			
				case kAPI_RESULT_ENUM_DATA_MARKER:
					$this->serialiseMarkers();
					break;
			
				case kAPI_RESULT_ENUM_DATA_RECORD:
					$this->serialiseRecords();
					break;
			
			} // Parsed by format.
			
			//
			// Signal processed.
			//
			$this->mProcessed = TRUE;
			
			//
			// Clear cache.
			//
			$this->mCache = Array();
		
		} // Not already processed.
	
	} // serialise.

	 

/*=======================================================================================
 *																						*
 *								PROTECTED SERAILISING INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	serialiseColumns																*
	 *==================================================================================*/

	/**
	 * Serialise columns
	 *
	 * This method will serialise the iterator data for columns
	 *
	 * @access protected
	 */
	protected function serialiseColumns()
	{
		//
		// Set columns in dictionary.
		//
		$this->setColumns();
		
		//
		// Iterate iterator.
		//
		$cols = array_keys( $this->mDictionary[ kAPI_DICTIONARY_LIST_COLS ] );
		foreach( $this->mIterator as $object )
		{
			//
			// Save current unit.
			//
			$this->mCurrentUnit = $object;
			
			//
			// Allocate data.
			//
			$this->mData[ $object[ kTAG_NID ] ] = Array();
			$data = & $this->mData[ $object[ kTAG_NID ] ];
			
			//
			// Iterate columns.
			//
			foreach( $cols as $col )
			{
				//
				// Convert object.
				//
				$object = (array) $object;
				
				//
				// Handle value.
				//
				if( array_key_exists( $col, $object ) )
				{
					//
					// Save value.
					//
					$value = $object[ $col ];
					
					//
					// Allocate value.
					//
					$data[ $col ] = Array();
					
					//
					// Handle score.
					//
					if( $col == kAPI_PARAM_RESPONSE_TYPE_SCORE )
						$data[ $col ]
							= array( kAPI_PARAM_RESPONSE_FRMT_TYPE
									=> kAPI_PARAM_RESPONSE_TYPE_SCORE,
									 kAPI_PARAM_RESPONSE_FRMT_DISP
									=> $value );
					
					//
					// Handle value.
					//
					else
						$this->setDataValue(
							$data[ $col ],
							$value,
							$this->mCache[ Tag::kSEQ_NAME ][ $col ] );
				
				} // Has column.
			
			} // Iterating columns.
		
		} // Iterating iterator.
		
	} // serialiseColumns.

	 
	/*===================================================================================
	 *	serialiseFormatted																*
	 *==================================================================================*/

	/**
	 * Serialise formatted
	 *
	 * This method will serialise the iterator data for formatted records
	 *
	 * @access protected
	 */
	protected function serialiseFormatted()
	{
		//
		// Iterate objects.
		//
		foreach( $this->mIterator as $object )
		{
			//
			// Save current unit.
			//
			$this->mCurrentUnit = $object;
			
			//
			// Set excluded offsets.
			//
			$this->setHiddenTags( $object );
			
			//
			// Allocate data.
			//
			$this->mData[ $object[ kTAG_NID ] ] = Array();
			$data = & $this->mData[ $object[ kTAG_NID ] ];
			
			//
			// Iterate object properties.
			//
			foreach( $object as $key => $value )
			{
				//
				// Exclude hidden properties.
				//
				if( ! in_array( $key, $this->mHidden ) )
					$this->setProperty( $data, $key, $value );
			
			} // Iterating object properties.
		
		} // Iterating objects.
		
	} // serialiseFormatted.

	 
	/*===================================================================================
	 *	serialiseMarkers																*
	 *==================================================================================*/

	/**
	 * Serialise markers
	 *
	 * This method will serialise the iterator data for markers.
	 *
	 * @access protected
	 */
	protected function serialiseMarkers()
	{
		//
		// Init local storage.
		//
		$shape = $this->mShape[ kTAG_ID_SEQUENCE ];
		
		//
		// Init feature collection.
		//
		$this->mData = array( "type" => "FeatureCollection",
							  "features" => Array() );
		
		//
		// Iterate objects.
		//
		foreach( $this->mIterator as $object )
			$this->mData[ "features" ][]
				= array( 'type' => 'Feature',
						'geometry' => $object[ $shape ],
						'properties' => array(
							kAPI_PARAM_ID => $object[ kTAG_NID ],
							kAPI_PARAM_DOMAIN => $this->mDomain[ kTAG_NID ] ) );
		
	} // serialiseMarkers.

	 
	/*===================================================================================
	 *	serialiseRecords																*
	 *==================================================================================*/

	/**
	 * Serialise records
	 *
	 * This method will serialise the iterator data for aggregated records.
	 *
	 * @access protected
	 */
	protected function serialiseRecords()
	{
		//
		// Set columns in dictionary.
		//
		$this->setColumns();
		
		//
		// Set collection.
		//
		$collection = $this->mIterator->collection()[ kTAG_CONN_COLL ];
		$this->mDictionary[ kAPI_DICTIONARY_COLLECTION ] = $collection;
		
		//
		// Set collection reference count offset.
		//
		$this->mDictionary[ kAPI_DICTIONARY_REF_COUNT ]
			= PersistentObject::ResolveRefCountTag( $collection );
		
		//
		// Iterate objects.
		//
		foreach( $this->mIterator as $object )
		{
			//
			// Save current object.
			//
			$this->mCurrentUnit = $object;
			
			//
			// Set excluded offsets.
			//
			$this->setHiddenTags( $object );
			
			//
			// Set identifier.
			//
			$this->mDictionary[ kAPI_DICTIONARY_IDS ][] = $object[ kTAG_NID ];
			
			//
			// Set record.
			//
			$this->setRecord( $this->mData, $object );
		
		} // Iterating objects.
		
		//
		// Cluster tags.
		//
		$this->clusterTags();
		
	} // serialiseRecords.

	 

/*=======================================================================================
 *																						*
 *								PROTECTED PARSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setColumns																		*
	 *==================================================================================*/

	/**
	 * Set table columns
	 *
	 * The duty of this method is to set the table column information according to the
	 * current domain.
	 *
	 * The method will format the columns in two ways, depending on the data format:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_RESULT_ENUM_DATA_COLUMN}</tt>: The dictionary
	 *		{@link kAPI_DICTIONARY_LIST_COLS} element will be loaded with an array of
	 *		elements holding the column tag label and description.
	 *	<li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: The dictionary
	 *		{@link kAPI_DICTIONARY_LIST_COLS} element will be loaded with an array indexed
	 *		by tag serial numbers with tag native identifiers as value.
	 * </ul>
	 *
	 * This method expects the domain parameter to have been set.
	 *
	 * @access protected
	 */
	protected function setColumns()
	{
		//
		// Init local storage.
		//
		$this->mDictionary[ kAPI_DICTIONARY_LIST_COLS ] = Array();
		$dict = & $this->mDictionary[ kAPI_DICTIONARY_LIST_COLS ];
		$wrapper = $this->mIterator->collection()->dictionary();
		
		//
		// Determine full-text search.
		//
		$full_text = array_key_exists( '$text', $this->mIterator->criteria() );
		if( (! $full_text)
		 && array_key_exists( '$and', $this->mIterator->criteria() ) )
		{
			foreach( $this->mIterator->criteria()[ '$and' ] as $tmp )
			{
				if( $full_text = array_key_exists( '$text', $tmp ) )
					break;													// =>
			}
		}
		
		//
		// Add full text header and top score.
		//
		if( $full_text )
		{
			//
			// Set top score.
			//
			$this->mDictionary[ kAPI_PARAM_RESPONSE_TYPE_SCORE ]
				= $this->mIterator->current()[ kAPI_PARAM_RESPONSE_TYPE_SCORE ];
			
			//
			// Set score column.
			//
			$dict[ kAPI_PARAM_RESPONSE_TYPE_SCORE ]
				= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Score',
						 kAPI_PARAM_RESPONSE_FRMT_INFO => 'Result relevance score.' );
		}
		
		//
		// Set table columns.
		//
		foreach( UnitObject::ListOffsets( $this->mDomain[ kTAG_NID ] ) as $col )
		{
			//
			// Cache tag.
			//
			$tag = $this->cacheTag( $wrapper, $this->mCache, $col );
			
			//
			// Set tag identifier.
			//
			if( $this->mFormat == kAPI_RESULT_ENUM_DATA_RECORD )
				$dict[] = $tag[ kTAG_ID_SEQUENCE ];
			
			//
			// Format tag.
			//
			else
			{
				//
				// Allocate labels.
				//
				$dict[ $tag[ kTAG_ID_SEQUENCE ] ] = Array();
		
				//
				// Set labels.
				//
				$this->setTagLabel( $dict[ $tag[ kTAG_ID_SEQUENCE ] ], $tag );
			
			} // Formatted results.
		
		} // Iterating columns.
		
	} // setColumns.

	 
	/*===================================================================================
	 *	setRecord																		*
	 *==================================================================================*/

	/**
	 * Set record
	 *
	 * The duty of this method is to aggregate the provided object and all related objects
	 * in the provided container.
	 *
	 * @access protected
	 */
	protected function setRecord( &$theContainer, $theObject )
	{
		//
		// Init local storage.
		//
		$class = $theObject[ kTAG_CLASS ];
		
		//
		// Load record.
		//
		switch( $class::kSEQ_NAME )
		{
			case Tag::kSEQ_NAME:
				$this->cacheTag(
					$this->mIterator->collection()->dictionary(),
					$this->mData,
					$theObject,
					FALSE );
				break;
				
			case Term::kSEQ_NAME:
				$this->cacheTerm(
					$this->mIterator->collection()->dictionary(),
					$this->mData,
					$theObject );
				break;
				
			case Node::kSEQ_NAME:
				$this->cacheNode(
					$this->mIterator->collection()->dictionary(),
					$this->mData,
					$theObject );
				break;
				
			case Edge::kSEQ_NAME:
				$this->cacheEdge(
					$this->mIterator->collection()->dictionary(),
					$this->mData,
					$theObject );
				break;
				
			case UnitObject::kSEQ_NAME:
				$this->cacheUnit(
					$this->mIterator->collection()->dictionary(),
					$this->mData,
					$theObject );
				break;
				
			case User::kSEQ_NAME:
				$this->cacheUser(
					$this->mIterator->collection()->dictionary(),
					$this->mData,
					$theObject );
				break;
		
		} // Parsed collection name.
		
		//
		// Iterate object properties.
		//
		foreach( $theObject as $key => $value )
		{
			//
			// Exclude hidden properties.
			//
			if( ! in_array( $key, $this->mHidden ) )
				$this->aggregateProperty( $class, $key, $value );
		
		} // Iterating object properties.
		
	} // setRecord.

	 
	/*===================================================================================
	 *	setProperty																		*
	 *==================================================================================*/

	/**
	 * Set property
	 *
	 * The duty of this method is to cache the property tag, allocate the property in the
	 * provided container, set the property labels and format the property value.
	 *
	 * @param array					$theContainer		Data container.
	 * @param string				$theOffset			Data offset.
	 * @param mixed					$theValue			Data value.
	 *
	 * @access protected
	 */
	protected function setProperty( &$theContainer, $theOffset, $theValue )
	{
		//
		// Cache tag.
		//
		$tag
			= $this->cacheTag(
				$this->mIterator->collection()->dictionary(),
				$this->mCache,
				$theOffset );
		
		//
		// Allocate property.
		//
		$theContainer[ $tag[ kTAG_ID_SEQUENCE ] ] = Array();
		$ref = & $theContainer[ $tag[ kTAG_ID_SEQUENCE ] ];

		//
		// Set labels.
		//
		$this->setTagLabel( $ref, $tag );

		//
		// Set values.
		//
		$this->setDataValue( $ref, $theValue, $tag );
		
	} // setProperty.

	 
	/*===================================================================================
	 *	setTagLabel																		*
	 *==================================================================================*/

	/**
	 * Set tag label
	 *
	 * The duty of this method is to set the provided container with the label and description
	 * of the provided tag.
	 *
	 * @param array					$theContainer		Data container.
	 * @param array					$theTag				Tag array object.
	 *
	 * @access protected
	 *
	 * @see kAPI_PARAM_RESPONSE_FRMT_NAME kAPI_PARAM_RESPONSE_FRMT_INFO
	 */
	protected function setTagLabel( &$theContainer, $theTag )
	{
		//
		// Set label.
		//
		if( array_key_exists( kTAG_LABEL, $theTag ) )
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
				= $theTag[ kTAG_LABEL ];
		
		//
		// Set description.
		//
		if( array_key_exists( kTAG_DESCRIPTION, $theTag ) )
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_INFO ]
				= $theTag[ kTAG_DESCRIPTION ];
		
	} // setTagLabel.

	 
	/*===================================================================================
	 *	setDataValue																	*
	 *==================================================================================*/

	/**
	 * Set value
	 *
	 * The duty of this method is to set the provided container with the provided data
	 * value.
	 *
	 * This method expects the current offset tag to have been cached.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Tag array object.
	 *
	 * @access protected
	 */
	protected function setDataValue( &$theContainer, $theValue, $theTag )
	{
		//
		// Handle structures.
		//
		if( $theTag[ kTAG_DATA_TYPE ] == kTYPE_STRUCT )
		{
			//
			// Set type.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
				= kAPI_PARAM_RESPONSE_TYPE_STRUCT;
			
			//
			// Save container structure index offset.
			//
			$offset = ( array_key_exists( kTAG_TAG_STRUCT_IDX, $theTag ) )
					? $this->mIterator
						->collection()
							->dictionary()
								->getSerial( $theTag[ kTAG_TAG_STRUCT_IDX ] )
					: NULL;
			
			//
			// Handle structures list.
			//
			if( array_key_exists( kTAG_DATA_KIND, $theTag )
			 && in_array( kTYPE_LIST, $theTag[ kTAG_DATA_KIND ] ) )
			{
				//
				// Allocate list.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
				$list = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ];
				
				//
				// Iterate structures.
				//
				foreach( $theValue as $struct )
				{
					//
					// Allocate list element.
					//
					$list[] = Array();
					$ref = & $list[ count( $list ) - 1 ];
					
					//
					// Set type.
					//
					$ref[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
						= kAPI_PARAM_RESPONSE_TYPE_STRUCT;
			
					//
					// Set structure index string.
					//
					if( array_key_exists( $offset, $struct ) )
						$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
							= $struct[ $offset ];
				
					//
					// Allocate structure.
					//
					$ref[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
					
					//
					// Iterate structure properties.
					//
					foreach( $struct as $key => $value )
					{
						//
						// Exclude hidden properties.
						//
						if( ! in_array( $key, $this->mHidden ) )
							$this->setProperty(
								$ref[ kAPI_PARAM_RESPONSE_FRMT_DOCU ],
								$key,
								$value );
					
					} // Iterating structure properties.
				
				} // Iterating list.
			
			} // Structures list.
			
			//
			// Handle scalar structure.
			//
			else
			{
				//
				// Set structure index string.
				//
				if( array_key_exists( $offset, $theValue ) )
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $theValue[ $offset ];
			
				//
				// Allocate structure.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
				
				//
				// Iterate structure properties.
				//
				foreach( $theValue as $key => $value )
				{
					//
					// Exclude hidden properties.
					//
					if( ! in_array( $key, $this->mHidden ) )
						$this->setProperty(
							$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ],
							$key,
							$value );
				
				} // Iterating structure properties.
			
			} // Scalar structure.
			
		} // Structure.
		
		//
		// Handle scalars.
		//
		else
		{
			//
			// Handle list of scalars.
			//
			if( array_key_exists( kTAG_DATA_KIND, $theTag )
			 && in_array( kTYPE_LIST, $theTag[ kTAG_DATA_KIND ] ) )
			{
				//
				// Parse by data type.
				//
				switch( $theTag[ kTAG_DATA_TYPE ] )
				{
					//
					// Structured scalars.
					//
					case kTYPE_SET:
					case kTYPE_TYPED_LIST:
					case kTYPE_LANGUAGE_STRING:
					case kTYPE_LANGUAGE_STRINGS:
					case kTYPE_SHAPE:
						//
						// Allocate list.
						//
						$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
						$list = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
						
						//
						// Iterate elements.
						//
						foreach( $theValue as $value )
						{
							//
							// Allocate element.
							//
							$list[] = Array();
							
							//
							// Format element.
							//
							$this->formatDataValue(
								$list[ count( $list ) - 1 ],
								$value,
								$theTag );
						
						} // Iterating list.
						
						break;
	
					//
					// Miscellanea.
					//
					case kTYPE_ENUM:
					case kTYPE_REF_SELF:
					case kTYPE_REF_UNIT:
					case kTYPE_BOOLEAN:
					case kTYPE_INT:
					case kTYPE_FLOAT:
					case kTYPE_MIXED:
					case kTYPE_TIME_STAMP:
					case kTYPE_STRING:
					case kTYPE_TEXT:
					case kTYPE_YEAR:
					case kTYPE_DATE:
			
					//
					// Other.
					//
					default:
						$this->formatDataValue( $theContainer, $theValue, $theTag );
						break;
		
				} // Parsed data type.
			
			} // List of scalars.
			
			//
			// Scalar.
			//
			else
				$this->formatDataValue( $theContainer, $theValue, $theTag );
		
		} // Scalar.
		
	} // setDataValue.

	 

/*=======================================================================================
 *																						*
 *								PROTECTED FORMATTING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	aggregateProperty																*
	 *==================================================================================*/

	/**
	 * Aggregate property
	 *
	 * The duty of this method is to aggregate the provided offset tag and parse the
	 * eventual structured value.
	 *
	 * @param string				$theClass			Object class.
	 * @param string				$theOffset			Data offset.
	 * @param mixed					$theValue			Data value.
	 * @param boolean				$doTags				TRUE means recurse tags.
	 *
	 * @access protected
	 */
	protected function aggregateProperty( $theClass, $theOffset, $theValue,
										  $doTags = TRUE )
	{
		//
		// Cache tag.
		//
		$tag
			= $this->cacheTag(
				$this->mIterator->collection()->dictionary(),
				$this->mData,
				$theOffset,
				FALSE );
		
		//
		// Update dictionary tags xrefs.
		//
		$this->mDictionary[ kAPI_DICTIONARY_TAGS ]
						  [ $tag[ kTAG_ID_SEQUENCE ] ]
			= $tag[ kTAG_NID ];
		
		//
		// Handle value.
		//
		switch( $tag[ kTAG_DATA_TYPE ] )
		{
			//
			// Structures.
			//
			case kTYPE_STRUCT:
				if( array_key_exists( kTAG_DATA_KIND, $tag )
				 && in_array( kTYPE_LIST, $tag[ kTAG_DATA_KIND ] ) )
				{
					foreach( $theValue as $struct )
					{
						foreach( $struct as $key => $value )
						{
							if( ! in_array( $key, $this->mHidden ) )
								$this->aggregateProperty( $theClass, $key, $value );
		
						} // Iterating object properties.
					}
				}
				else
				{
					foreach( $theValue as $key => $value )
					{
						if( ! in_array( $key, $this->mHidden ) )
							$this->aggregateProperty( $theClass, $key, $value );
		
					} // Iterating object properties.
				}
				break;
	
			//
			// Enumerated values.
			//
			case kTYPE_ENUM:
				if( is_array( $theValue ) )
				{
					foreach( $theValue as $value )
						$this->cacheTerm(
							$this->mIterator->collection()->dictionary(),
							$this->mData,
							$value );
				}
				else
					$this->cacheTerm(
						$this->mIterator->collection()->dictionary(),
						$this->mData,
						$theValue );
				break;
	
			//
			// Enumerated sets.
			//
			case kTYPE_SET:
				if( array_key_exists( kTAG_DATA_KIND, $tag )
				 && in_array( kTYPE_LIST, $tag[ kTAG_DATA_KIND ] ) )
				{
					foreach( $theValue as $value )
					{
						foreach( $value as $element )
							$this->cacheTerm(
								$this->mIterator->collection()->dictionary(),
								$this->mData,
								$element );
					}
				}
				else
				{
					foreach( $theValue as $value )
						$this->cacheTerm(
							$this->mIterator->collection()->dictionary(),
							$this->mData,
							$value );
				}
				break;
	
			//
			// Unit reference.
			//
			case kTYPE_REF_SELF:
				switch( $theClass::kSEQ_NAME )
				{
					case Tag::kSEQ_NAME:
						if( is_array( $theValue ) )
						{
							foreach( $theValue as $value )
								$this->cacheTag(
									$this->mIterator->collection()->dictionary(),
									$this->mData,
									$value );
						}
						else
							$this->cacheTag(
								$this->mIterator->collection()->dictionary(),
								$this->mData,
								$theValue );
						break;
				
					case Term::kSEQ_NAME:
						if( is_array( $theValue ) )
						{
							foreach( $theValue as $value )
								$this->cacheTerm(
									$this->mIterator->collection()->dictionary(),
									$this->mData,
									$value );
						}
						else
							$this->cacheTerm(
								$this->mIterator->collection()->dictionary(),
								$this->mData,
								$theValue );
						break;
				
					case Node::kSEQ_NAME:
						if( is_array( $theValue ) )
						{
							foreach( $theValue as $value )
								$this->cacheNode(
									$this->mIterator->collection()->dictionary(),
									$this->mData,
									$value );
						}
						else
							$this->cacheNode(
								$this->mIterator->collection()->dictionary(),
								$this->mData,
								$theValue );
						break;
				
					case Edge::kSEQ_NAME:
						if( is_array( $theValue ) )
						{
							foreach( $theValue as $value )
								$this->cacheEdge(
									$this->mIterator->collection()->dictionary(),
									$this->mData,
									$value );
						}
						else
							$this->cacheEdge(
								$this->mIterator->collection()->dictionary(),
								$this->mData,
								$theValue );
						break;
				
					case UnitObject::kSEQ_NAME:
						if( is_array( $theValue ) )
						{
							foreach( $theValue as $value )
								$this->cacheUnit(
									$this->mIterator->collection()->dictionary(),
									$this->mData,
									$value );
						}
						else
							$this->cacheUnit(
								$this->mIterator->collection()->dictionary(),
								$this->mData,
								$theValue );
						break;
				
					case User::kSEQ_NAME:
						if( is_array( $theValue ) )
						{
							foreach( $theValue as $value )
								$this->cacheUser(
									$this->mIterator->collection()->dictionary(),
									$this->mData,
									$value );
						}
						else
							$this->cacheUser(
								$this->mIterator->collection()->dictionary(),
								$this->mData,
								$theValue );
						break;
				}
				break;
				
			case kTYPE_REF_UNIT:
				if( is_array( $theValue ) )
				{
					foreach( $theValue as $value )
						$this->cacheUnit(
							$this->mIterator->collection()->dictionary(),
							$this->mData,
							$value );
				}
				else
					$this->cacheUnit(
						$this->mIterator->collection()->dictionary(),
						$this->mData,
						$theValue );
				break;
		
		} // Parsed by data type.
		
		//
		// Recurse tags.
		//
		if( $doTags )
		{
			//
			// Handle tag tags.
			//
			foreach( $tag as $key => $value )
			{
				//
				// Exclude hidden properties.
				//
				if( ! in_array( $key, $this->mHidden ) )
					$this->aggregateProperty( 'OntologyWrapper\Tag', $key, $value, FALSE );
		
			} // Iterating tag tags.
		
		} // Recurse tags.
		
	} // aggregateProperty.

	 
	/*===================================================================================
	 *	formatDataValue																	*
	 *==================================================================================*/

	/**
	 * Format value
	 *
	 * The duty of this method is to format and set the provided container with the provided
	 * data value.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Tag array object.
	 *
	 * @access protected
	 */
	protected function formatDataValue( &$theContainer, $theValue, $theTag )
	{
		//
		// Parse by data type.
		//
		switch( $theTag[ kTAG_DATA_TYPE ] )
		{
			//
			// Enumerated values.
			//
			case kTYPE_SET:
			case kTYPE_ENUM:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_ENUM;
				$this->formatEnumeration( $theContainer, $theValue, $theTag );
				break;
	
			//
			// Typed list.
			//
			case kTYPE_TYPED_LIST:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_TYPED;
				$this->formatTypedList( $theContainer, $theValue, $theTag );
				break;
	
			//
			// Language strings.
			//
			case kTYPE_LANGUAGE_STRING:
			case kTYPE_LANGUAGE_STRINGS:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_TYPED;
				$this->formatLanguageStrings( $theContainer, $theValue, $theTag );
				break;
	
			//
			// Unit reference.
			//
			case kTYPE_REF_SELF:
			case kTYPE_REF_UNIT:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_OBJECT;
				$this->formatUnitReference( $theContainer, $theValue, $theTag );
				break;
	
			//
			// Geo JSON shape.
			//
			case kTYPE_SHAPE:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_SHAPE;
				$this->formatShape( $theContainer, $theValue, $theTag );
				break;
	
			//
			// Boolean.
			//
			case kTYPE_BOOLEAN:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
				$this->formatBoolean( $theContainer, $theValue, $theTag );
				break;
	
			//
			// Integer.
			//
			case kTYPE_INT:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
				$this->formatInteger( $theContainer, $theValue, $theTag );
				break;
	
			//
			// Float.
			//
			case kTYPE_FLOAT:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
				$this->formatFloat( $theContainer, $theValue, $theTag );
				break;
	
			//
			// Time-stamp.
			//
			case kTYPE_TIME_STAMP:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
				$this->formatTimeStamp( $theContainer, $theValue, $theTag );
				break;
	
			//
			// Miscellanea.
			//
			case kTYPE_MIXED:
			case kTYPE_STRING:
			case kTYPE_TEXT:
			case kTYPE_YEAR:
			case kTYPE_DATE:
			
			//
			// Other.
			//
			default:
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
				$this->formatScalar( $theContainer, $theValue, $theTag );
				break;
		
		} // Parsed data type.
		
	} // formatDataValue.

	 
	/*===================================================================================
	 *	formatEnumeration																*
	 *==================================================================================*/

	/**
	 * Format enumerated value
	 *
	 * The duty of this method is to format the provided enumerated value and set into the
	 * provided container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatEnumeration( &$theContainer, $theValue, $theTag )
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
				$this->formatEnumeration( $theContainer, $theValue[ 0 ], $theTag );
			
			//
			// Handle multiple values.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Allocate list.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				$list = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
				
				//
				// Iterate list.
				//
				foreach( $theValue as $value )
				{
					//
					// Allocate element.
					//
					$list[] = Array();
					$ref = & $list[ count( $list ) - 1 ];
				
					//
					// Cache term.
					//
					$this->cacheTerm(
						$this->mIterator->collection()->dictionary(),
						$this->mCache,
						$value );
		
					//
					// Reference term.
					//
					$term = & $this->mCache[ Term::kSEQ_NAME ][ $value ];
		
					//
					// Set label.
					//
					$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $term[ kTAG_LABEL ];
		
					//
					// Set definition.
					//
					if( array_key_exists( kTAG_DEFINITION, $term ) )
						$ref[ kAPI_PARAM_RESPONSE_FRMT_INFO ] = $term[ kTAG_DEFINITION ];
			
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
			$this->cacheTerm(
				$this->mIterator->collection()->dictionary(),
				$this->mCache,
				$theValue );
		
			//
			// Reference term.
			//
			$term = & $this->mCache[ Term::kSEQ_NAME ][ $theValue ];
		
			//
			// Allocate element.
			//
			if( array_key_exists( kTAG_DEFINITION, $term ) )
			{
				//
				// Allocate element.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				
				//
				// Set reference.
				//
				$ref = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
			}
			else
			{
				//
				// Update type.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
				
				//
				// Set reference.
				//
				$ref = & $theContainer;
			}
			
			//
			// Set label.
			//
			$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $term[ kTAG_LABEL ];
		
			//
			// Set definition.
			//
			if( array_key_exists( kTAG_DEFINITION, $term ) )
				$ref[ kAPI_PARAM_RESPONSE_FRMT_INFO ] = $term[ kTAG_DEFINITION ];
		
		} // Scalar value.
		
	} // formatEnumeration.

	 
	/*===================================================================================
	 *	formatTypedList																	*
	 *==================================================================================*/

	/**
	 * Format typed list value
	 *
	 * The duty of this method is to format the provided typed list value and set into the
	 * provided container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatTypedList( &$theContainer, $theValue, $theTag )
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
					// Allocate element.
					//
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
					$ref = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
					
					//
					// Set type.
					//
					$ref[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
						= $theValue[ 0 ][ kTAG_TYPE ];
			
					//
					// Set text value.
					//
					$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $theValue[ 0 ][ kTAG_TEXT ];
				
				} // Typed.
				
				//
				// Handle typeless element.
				//
				else
				{
					//
					// Change type.
					//
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
						= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
					
					//
					// Set scalar value.
					//
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $theValue[ 0 ][ kTAG_TEXT ];
				
				} // Reduce to scalar.
				
			} // Text value.
			
			//
			// Handle URL value.
			//
			elseif( array_key_exists( kTAG_URL, $theValue[ 0 ] ) )
			{
				//
				// Allocate element.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				$ref = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
				
				//
				// Set display to type.
				//
				$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= ( array_key_exists( kTAG_TYPE, $theValue[ 0 ] ) )
					? $theValue[ 0 ][ kTAG_TYPE ]
					: $theValue[ 0 ][ kTAG_URL ];
				
				//
				// Set value.
				//
				$ref[ kAPI_PARAM_RESPONSE_FRMT_LINK ]
					= $theValue[ 0 ][ kTAG_URL ];
			
			} // URL value.
		
		} // Single element.
		
		//
		// Handle multiple elements.
		//
		elseif( count( $theValue ) > 1 )
		{
			//
			// Allocate list.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
			$list = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
			
			//
			// Iterate elements.
			//
			foreach( $theValue as $value )
			{
				//
				// Allocate element.
				//
				$list[] = Array();
				$ref = & $list[ count( $theContainer ) - 1 ];
				
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
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= ( array_key_exists( kTAG_TYPE, $theValue[ 0 ] ) )
						? $value[ kTAG_TYPE ]
						: $value[ kTAG_URL ];
				
					//
					// Set value.
					//
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_LINK ]
						= $value[ kTAG_URL ];
			
				} // URL value.
			
			} // Iterating elements.
		
		} // Multiple elements.
		
	} // formatTypedList.

	 
	/*===================================================================================
	 *	formatLanguageStrings															*
	 *==================================================================================*/

	/**
	 * Format language strings value
	 *
	 * The duty of this method is to format the provided language strings and set into the
	 * provided container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatLanguageStrings( &$theContainer, $theValue, $theTag )
	{
		//
		// Handle single element.
		//
		if( count( $theValue ) == 1 )
		{
			//
			// Handle language.
			//
			if( array_key_exists( kTAG_LANGUAGE, $theValue[ 0 ] ) )
			{
				//
				// Handle language.
				//
				if( $theValue[ 0 ][ kTAG_LANGUAGE ] !== '0' )
				{
					//
					// Allocate element.
					//
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
					$ref = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
					
					//
					// Set language.
					//
					$ref[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
						= $theValue[ 0 ][ kTAG_LANGUAGE ];
					
					//
					// Set string.
					//
					$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $theValue[ 0 ][ kTAG_TEXT ];
				
				} // Indicated language.
				
				//
				// Handle no language.
				//
				else
				{
					//
					// Correct type.
					//
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
						= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
					
					//
					// Set scalar value.
					//
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $theValue[ 0 ][ kTAG_TEXT ];
				
				} // Reduce to scalar.
			
			} // Provided language.
			
			//
			// Handle no language.
			//
			else
			{
				//
				// Correct type.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
					= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
				
				//
				// Set scalar value.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= $theValue[ 0 ][ kTAG_TEXT ];
			
			} // Reduce to scalar.
		
		} // Single element.
		
		//
		// Handle multiple elements.
		//
		elseif( count( $theValue ) > 1 )
		{
			//
			// Allocate list.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
			$list = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
			
			//
			// Iterate strings.
			//
			foreach( $theValue as $value )
			{
				//
				// Allocate element.
				//
				$list[] = Array();
				$ref = & $list[ count( $theContainer ) - 1 ];
				
				//
				// Handle language.
				//
				if( array_key_exists( kTAG_LANGUAGE, $value ) )
				{
					//
					// Handle language.
					//
					if( $value[ kTAG_LANGUAGE ] !== '0' )
					{
						//
						// Set language.
						//
						$ref[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
							= $value[ kTAG_LANGUAGE ];
					
						//
						// Set string.
						//
						$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
							= $value[ kTAG_TEXT ];
				
					} // Indicated language.
				
					//
					// Handle no language.
					//
					else
						$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
							= $value[ kTAG_TEXT ];
			
				} // Provided language.
			
				//
				// Handle no language.
				//
				else
					$ref[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
						= $value[ kTAG_TEXT ];
			
			} // Iterating strings.
		
		} // Multiple elements.
		
	} // formatLanguageStrings.

	 
	/*===================================================================================
	 *	formatUnitReference																*
	 *==================================================================================*/

	/**
	 * Format unit reference value
	 *
	 * The duty of this method is to format the provided unit reference and set into the
	 * provided container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatUnitReference( &$theContainer, $theValue, $theTag )
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
					$theContainer,
					$theValue [ 0 ],
					$theTag );
			
			//
			// Handle multiple references.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Allocate list.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				$list = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
				
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
						$list[ count( $list ) - 1 ],
						$value,
						$theTag );
				
				} // Iterating references.
			
			} // Multiple references.
		
		} // References list.
		
		//
		// Handle scalar reference.
		//
		else
		{
			//
			// Cache referenced object.
			//
			$object
				= $this->cacheUnit(
					$this->mIterator->collection()->dictionary(),
					$this->mCache,
					$theValue,
					TRUE );
			
			//
			// Set object name.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
				= $object->getName( $this->mLanguage );
			
			//
			// Allocate service.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_SERV ] = Array();
			$ref = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_SERV ];
			
			//
			// Set service operation and language.
			//
			$ref[ kAPI_REQUEST_OPERATION ] = kAPI_OP_GET_UNIT;
			$ref[ kAPI_REQUEST_LANGUAGE ] = $this->mLanguage;
			
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
		
		} // Scalar reference.
		
	} // formatUnitReference.

	 
	/*===================================================================================
	 *	formatShape																		*
	 *==================================================================================*/

	/**
	 * Format shape value
	 *
	 * The duty of this method is to format the provided shape and set into the provided
	 * container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatShape( &$theContainer, $theValue, $theTag )
	{
		//
		// Set object name.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
			= 'View on map';
		
		//
		// Allocate service.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_SERV ] = Array();
		$ref = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_SERV ];
		
		//
		// Set service operation and language.
		//
		$ref[ kAPI_REQUEST_OPERATION ] = kAPI_OP_GET_UNIT;
		$ref[ kAPI_REQUEST_LANGUAGE ] = $this->mLanguage;
		
		//
		// Allocate service parameters.
		//
		$ref[ kAPI_REQUEST_PARAMETERS ] = Array();
		$ref = & $ref[ kAPI_REQUEST_PARAMETERS ];
		
		//
		// Set object identifier and data format.
		//
		$ref[ kAPI_PARAM_ID ] = $this->mCurrentUnit[ kTAG_NID ];
		$ref[ kAPI_PARAM_DATA ] = kAPI_RESULT_ENUM_DATA_MARKER;
		
	} // formatShape.

	 
	/*===================================================================================
	 *	formatBoolean																	*
	 *==================================================================================*/

	/**
	 * Format boolean value
	 *
	 * The duty of this method is to format the provided boolean value and set into the
	 * provided container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatBoolean( &$theContainer, $theValue, $theTag )
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
				$theValue = ( $theValue[ 0 ] ) ? 'Yes' : 'No';
			
			//
			// Handle multiple values.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Convert values.
				//
				$keys = array_keys( $theValue );
				foreach( $keys as $key )
					$theValue[ $key ] = ( $theValue[ $key ] ) ? 'Yes' : 'No';
			
			} // More than one value.
			
		} // List of values.
		
		//
		// Handle scalar.
		//
		else
			$theValue = ( $theValue ) ? 'Yes' : 'No';
		
		//
		// Load container.
		//
		$this->formatScalar( $theContainer, $theValue, $theTag );
		
	} // formatBoolean.

	 
	/*===================================================================================
	 *	formatInteger																	*
	 *==================================================================================*/

	/**
	 * Format integer value
	 *
	 * The duty of this method is to format the provided integer value and set into the
	 * provided container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatInteger( &$theContainer, $theValue, $theTag )
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
				$theValue = number_format( $theValue[ 0 ] );
			
			//
			// Handle multiple values.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Convert values.
				//
				$keys = array_keys( $theValue );
				foreach( $keys as $key )
					$theValue[ $key ] = number_format( $theValue[ $key ] );
			
			} // More than one value.
			
		} // List of values.
		
		//
		// Handle scalar.
		//
		else
			$theValue = number_format( $theValue );
		
		//
		// Load container.
		//
		$this->formatScalar( $theContainer, $theValue, $theTag );
		
	} // formatInteger.

	 
	/*===================================================================================
	 *	formatFloat																		*
	 *==================================================================================*/

	/**
	 * Format float value
	 *
	 * The duty of this method is to format the provided float value and set into the
	 * provided container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatFloat( &$theContainer, $theValue, $theTag )
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
				$theValue = number_format( $theValue[ 0 ], 4 );
			
			//
			// Handle multiple values.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Convert values.
				//
				$keys = array_keys( $theValue );
				foreach( $keys as $key )
					$theValue[ $key ] = number_format( $theValue[ $key ], 4 );
			
			} // More than one value.
			
		} // List of values.
		
		//
		// Handle scalar.
		//
		else
			$theValue = number_format( $theValue, 4 );
		
		//
		// Load container.
		//
		$this->formatScalar( $theContainer, $theValue, $theTag );
		
	} // formatFloat.

	 
	/*===================================================================================
	 *	formatTimeStamp																	*
	 *==================================================================================*/

	/**
	 * Format time stamp value
	 *
	 * The duty of this method is to format the provided time stamp value and set into the
	 * provided container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatTimeStamp( &$theContainer, $theValue, $theTag )
	{
		//
		// Init local storage.
		//
		$collection = $this->mIterator->collection();

		//
		// Handle array.
		//
		if( is_array( $theValue ) )
		{
			//
			// Handle single value.
			//
			if( count( $theValue ) == 1 )
				$theValue
					= $collection->parseTimeStamp( $theValue[ 0 ] );
			
			//
			// Handle multiple values.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Convert values.
				//
				$keys = array_keys( $theValue );
				foreach( $keys as $key )
					$theValue[ $key ]
						= $collection->parseTimeStamp( $theValue[ $key ] );
			
			} // More than one value.
			
		} // List of values.
		
		//
		// Handle scalar.
		//
		else
			$theValue
				= $collection->parseTimeStamp( $theValue );
		
		//
		// Load container.
		//
		$this->formatScalar( $theContainer, $theValue, $theTag );
		
	} // formatTimeStamp.

	 
	/*===================================================================================
	 *	formatScalar																	*
	 *==================================================================================*/

	/**
	 * Format scalar value
	 *
	 * The duty of this method is to format the provided scalar value and set into the
	 * provided container.
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Data value.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatScalar( &$theContainer, $theValue, $theTag )
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
				$this->formatScalar( $theContainer, $theValue[ 0 ], $theTag );
			
			//
			// Handle multiple values.
			//
			elseif( count( $theValue ) > 1 )
			{
				//
				// Allocate display elements.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				$ref = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ];
			
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
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $theValue;
		
	} // formatScalar.

	 

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
	 * This method will load the provided tag into the provided container, if not yet there,
	 * and return the cached tag as an array object.
	 *
	 * The provided parameter may be an array, in that case the method will interpret the
	 * parameter as a list of references, add all the resolved objects to the provided
	 * container and return an array indexed by object identifier, with as value the object
	 * array.
	 *
	 * If the last parameter is <tt>TRUE</tt>, the tag identifier will be its sequence
	 * number; if <tt>FALSE</tt>, its native identifier.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Tag reference or object.
	 * @param boolean				$doSequence			TRUE, use sequence as identifier.
	 *
	 * @access protected
	 * @return mixed				The cached tag or tags.
	 */
	protected function cacheTag( Wrapper $theWrapper, &$theContainer, $theValue,
										 $doSequence = TRUE )
	{
		//
		// Handle list of references.
		//
		if( is_array( $theValue ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			
			//
			// Iterate list.
			//
			foreach( $theValue as $value )
			{
				//
				// Get object.
				//
				$object
					= $this->cacheTag(
						$theWrapper, $theContainer, $value, $doSequence );
				
				//
				// Save object.
				//
				if( ! array_key_exists( $value, $result ) )
					$result[ $value ] = $object;
			
			} // Iterating list.
			
			return $result;															// ==>
		
		} // Provided list of references.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Save collection name.
			//
			$collection = Tag::kSEQ_NAME;
			
			//
			// Save identifier.
			//
			if( $theValue instanceof Tag )
				$id = ( $doSequence )
					? $theValue[ kTAG_ID_SEQUENCE ]
					: $theValue[ kTAG_NID ];
			else
			{
				if( $doSequence )
					$id = ( is_int( $theValue )
						 || ctype_digit( $theValue ) )
						? (int) $theValue
						: $theWrapper->getSerial( $theValue, TRUE );
				else
					$id
						= $theWrapper
							->getObject( $theValue, TRUE )
								[ kTAG_NID ];
			}
			
			//
			// Allocate collection.
			//
			if( ! array_key_exists( $collection, $theContainer ) )
				$theContainer[ $collection ] = Array();
			
			//
			// Load object.
			//
			if( ! array_key_exists( $id, $theContainer[ $collection ] ) )
			{
				//
				// Handle object.
				//
				if( $theValue instanceof Tag )
					$theContainer[ $collection ][ $id ]
						= $theValue->getArrayCopy();
				
				//
				// Handle reference.
				//
				else
				{
					//
					// Set criteria.
					//
					if( is_int( $id ) )
						$criteria = array( kTAG_ID_SEQUENCE => $id );
					else
						$criteria = array( kTAG_NID => $id );
						
					//
					// Get object.
					//
					$theContainer[ $collection ][ $id ]
						= Tag::ResolveCollection(
							Tag::ResolveDatabase( $theWrapper, TRUE ) )
								->matchOne( $criteria,
											kQUERY_ARRAY );
				
				} // Provided reference.
			
				//
				// Normalise strings.
				//
				$strings = array( kTAG_LABEL, kTAG_DESCRIPTION );
				foreach( $strings as $string )
				{
					//
					// Check string.
					//
					if( array_key_exists( $string, $theContainer[ $collection ][ $id ] ) )
						$theContainer[ $collection ][ $id ][ $string ]
							= OntologyObject::SelectLanguageString(
								$theContainer[ $collection ][ $id ][ $string ],
								$this->mLanguage );
			
				} // Normalising strings.
			
			} // New entry.
		
		} // Provided scalar tag.
		
		return $theContainer[ Tag::kSEQ_NAME ][ $id ];								// ==>
		
	} // cacheTag.

	 
	/*===================================================================================
	 *	cacheTerm																		*
	 *==================================================================================*/

	/**
	 * Load term in cache
	 *
	 * This method will load the provided term into the provided container, if not yet
	 * there, and return the cached term as an array object.
	 *
	 * The provided parameter may be an array, in that case the method will interpret the
	 * parameter as a list of references, add all the resolved objects to the provided
	 * container and return an array indexed by object identifier, with as value the object
	 * array.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Term reference or object.
	 *
	 * @access protected
	 * @return mixed				The cached term or terms.
	 */
	protected function cacheTerm( Wrapper $theWrapper, &$theContainer, $theValue )
	{
		return $this->cacheObject(
				$theWrapper,
				$theContainer,
				'OntologyWrapper\Term',
				array( kTAG_LABEL, kTAG_DEFINITION ),
				$theValue,
				FALSE );															// ==>
		
	} // cacheTerm.

	 
	/*===================================================================================
	 *	cacheNode																		*
	 *==================================================================================*/

	/**
	 * Load node in cache
	 *
	 * This method will load the provided node into the provided container, if not yet
	 * there, and return the node object as an array.
	 *
	 * The provided parameter may be an array, in that case the method will add all the
	 * provided nodes and return an array of node array objects indexed by node native
	 * identifier.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Node reference or object.
	 *
	 * @access protected
	 * @return mixed				The cached node or nodes.
	 */
	protected function cacheNode( Wrapper $theWrapper, &$theContainer, $theValue )
	{
		return $this->cacheObject(
				$theWrapper,
				$theContainer,
				'OntologyWrapper\Node',
				array( kTAG_LABEL, kTAG_DEFINITION, kTAG_DESCRIPTION ),
				$theValue,
				FALSE );															// ==>
		
	} // cacheNode.

	 
	/*===================================================================================
	 *	cacheEdge																		*
	 *==================================================================================*/

	/**
	 * Load edge in cache
	 *
	 * This method will load the provided edge into the provided container, if not yet
	 * there, and return the edge object as an array.
	 *
	 * The provided parameter may be an array, in that case the method will add all the
	 * provided edges and return an array of edge array objects indexed by edge native
	 * identifier.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Edge reference or object.
	 *
	 * @access protected
	 * @return mixed				The cached edge or edges.
	 */
	protected function cacheEdge( Wrapper $theWrapper, &$theContainer, $theValue )
	{
		return $this->cacheObject(
				$theWrapper,
				$theContainer,
				'OntologyWrapper\Edge',
				array( kTAG_LABEL, kTAG_DEFINITION, kTAG_DESCRIPTION ),
				$theValue,
				FALSE );															// ==>
		
	} // cacheEdge.

	 
	/*===================================================================================
	 *	cacheUnit																		*
	 *==================================================================================*/

	/**
	 * Load unit in cache
	 *
	 * This method will load the provided unit into the provided container, if not yet
	 * there, and return the unit object as an array.
	 *
	 * The provided parameter may be an array, in that case the method will add all the
	 * provided units and return an array of unit objects indexed by unit native identifier.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Unit reference or object.
	 * @param boolean				$doObject			TRUE means cache object.
	 *
	 * @access protected
	 * @return mixed				The cached unit or units.
	 */
	protected function cacheUnit( Wrapper $theWrapper, $theContainer, $theValue,
										  $doObject = FALSE )
	{
		return $this->cacheObject(
				$theWrapper,
				$theContainer,
				'OntologyWrapper\UnitObject',
				array( kTAG_LABEL, kTAG_DEFINITION, kTAG_DESCRIPTION ),
				$theValue,
				$doObject );														// ==>
		
	} // cacheUnit.

	 
	/*===================================================================================
	 *	cacheUser																		*
	 *==================================================================================*/

	/**
	 * Load user in cache
	 *
	 * This method will load the provided user into the provided container, if not yet
	 * there, and return the user object as an array.
	 *
	 * The provided parameter may be an array, in that case the method will add all the
	 * provided users and return an array of user array objects indexed by term native
	 * identifier.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			User reference or object.
	 * @param boolean				$doObject			TRUE means cache object.
	 *
	 * @access protected
	 * @return mixed				The cached user or users.
	 */
	protected function cacheUser( Wrapper $theWrapper, &$theContainer, $theValue,
										  $doObject = FALSE )
	{
		return $this->cacheObject(
				$theWrapper,
				$theContainer,
				'OntologyWrapper\User',
				array( kTAG_LABEL, kTAG_DEFINITION, kTAG_DESCRIPTION ),
				$theValue,
				$doObject );														// ==>
		
	} // cacheUser.
	
	
	/*===================================================================================
	 *	cacheObject																		*
	 *==================================================================================*/

	/**
	 * Load object in cache
	 *
	 * This method will load the provided node into the provided container, if not yet
	 * there, and return the cached node as an object or array object.
	 *
	 * The provided parameter may be an array, in that case the method will interpret the
	 * parameter as a list of references, add all the resolved objects to the provided
	 * container and return an array indexed by object identifier, with as value the object
	 * or array object.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theContainer		Data container.
	 * @param string				$theClass			Object class name.
	 * @param array					$theStrings			Language string offsets.
	 * @param mixed					$theValue			Object reference or object.
	 * @param boolean				$doObject			TRUE means cache object.
	 *
	 * @access protected
	 * @return mixed				The cached object or objects.
	 */
	protected function cacheObject( Wrapper $theWrapper, &$theContainer,
											$theClass, $theStrings, $theValue,
											$doObject = FALSE )
	{
		//
		// Handle list of references.
		//
		if( is_array( $theValue ) )
		{
			//
			// Init local storage.
			//
			$result = Array();
			
			//
			// Iterate list.
			//
			foreach( $theValue as $value )
			{
				//
				// Get object.
				//
				$object
					= $this->cacheObject(
						$theWrapper, $theContainer,
						$theClass, $theStrings, $value,
						$doObject );
				
				//
				// Save object.
				//
				if( ! array_key_exists( $value, $result ) )
					$result[ $value ] = $object;
			
			} // Iterating list.
			
			return $result;															// ==>
		
		} // Provided list of references.
		
		//
		// Init local storage.
		//
		$id = ( $theValue instanceof PersistentObject )
			? $theValue[ kTAG_NID ]
			: $theValue;
		
		//
		// Allocate collection.
		//
		if( ! array_key_exists( $theClass::kSEQ_NAME, $theContainer ) )
			$theContainer[ $theClass::kSEQ_NAME ] = Array();
		
		//
		// Reference cache.
		//
		$cache = & $theContainer[ $theClass::kSEQ_NAME ];
		
		//
		// Load object.
		//
		if( ! array_key_exists( $id, $cache ) )
		{
			//
			// Load cache.
			//
			$cache[ $id ] = ( $theValue instanceof PersistentObject )
						  ? ( ( $doObject )
							? $theValue
							: $theValue->getArrayCopy() )
						  : $theClass::ResolveCollection(
								$theClass::ResolveDatabase( $theWrapper, TRUE ) )
									->matchOne( array( kTAG_NID => $id ),
												( $doObject ) ? kQUERY_OBJECT
															  : kQUERY_ARRAY );
		
			//
			// Normalise strings.
			//
			foreach( $theStrings as $string )
			{
				//
				// Check string.
				//
				if( ( $doObject
				   && $cache[ $id ]->offsetExists( $string ) )
				 || ( (! $doObject)
				   && array_key_exists( $string, $cache[ $id ] ) ) )
					$cache[ $id ][ $string ]
						= OntologyObject::SelectLanguageString(
							$cache[ $id ][ $string ],
							$this->mLanguage );
		
			} // Normalising strings.
		
		} // New entry.
		
		return $cache[ $id ];														// ==>
		
	} // cacheObject.


	 
/*=======================================================================================
 *																						*
 *								PROTECTED PARSING UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setHiddenTags																	*
	 *==================================================================================*/

	/**
	 * Set hidden tags
	 *
	 * The duty of this method is to set the list of tags that should not be published.
	 *
	 * This is done according to the current data format, only the
	 * <tt>{@link kAPI_RESULT_ENUM_DATA_FORMAT}</tt> is relevant in this case, since the
	 * <tt>{@link kAPI_RESULT_ENUM_DATA_COLUMN}</tt> has a fixed set, the
	 * <tt>{@link kAPI_RESULT_ENUM_DATA_MARKER}</tt> uses only the object native identifier
	 * and the shape tag, and the <tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt> format
	 * selects all properties.
	 *
	 * Once the method has completed, the {@link $mHidden} data member will hold the
	 * list of tag sequence numbers which will not be published.
	 *
	 * By default we hide the object's internal and dynamic offsets.
	 *
	 * @param PersistentObject		$theObject			Object.
	 *
	 * @access protected
	 */
	protected function setHiddenTags( PersistentObject $theObject )
	{
		//
		// Save object class.
		//
		$class = $theObject[ kTAG_CLASS ];
	
		//
		// Check format.
		//
		switch( $this->mFormat )
		{
			case kAPI_RESULT_ENUM_DATA_FORMAT:
				//
				// Set exceptions.
				//
				$this->mHidden
					= array_merge(
						$class::DynamicOffsets(),
						$class::InternalOffsets(),
						array( kTAG_GEO_SHAPE ) );		// Added shape to excluded.
				
				break;
			
			case kAPI_RESULT_ENUM_DATA_RECORD:
				//
				// Set exceptions.
				//
				$this->mHidden
					= $class::InternalOffsets();
				
				break;
			
			default:
				$this->mHidden = Array();
				break;
		}
		
	} // setHiddenTags.

	 
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
			$this->mDictionary[ kAPI_DICTIONARY_CLUSTER ] = Array();
			$ref = & $this->mDictionary[ kAPI_DICTIONARY_CLUSTER ];
			
			//
			// Check identifiers.
			//
			if( ! array_key_exists( kAPI_DICTIONARY_IDS, $this->mDictionary ) )
				$this->mDictionary[ kAPI_DICTIONARY_IDS ] = Array();
			
			//
			// Iterate result identifiers.
			//
			foreach( $this->mDictionary[ kAPI_DICTIONARY_IDS ] as $id )
			{
				//
				// Get cluster.
				//
				$cluster
					= Tag::GetClusterKey(
						$this->mData[ Tag::kSEQ_NAME ]
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

	 

} // class IteratorSerialiser.


?>
