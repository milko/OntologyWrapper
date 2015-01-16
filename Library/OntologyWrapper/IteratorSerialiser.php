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
 *			contain the actual objects and related objects in their original format, or
 *			excluding dynamic offsets depending on the last constructor parameter.
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
 *	<li><tt>{@link kAPI_PARAM_TAG}</tt>: This element holds the data property tag serial
 *		identifier.
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
	 * Offsets tag.
	 *
	 * This protected data member holds the offsets tag to be tracked, this is relevant when
	 * serialising tags, the value corresponds to the tag offset which records the offsets
	 * found in a particular collection, these are the valid values:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_TAG_OFFSETS}</tt>: Tag offsets.
	 *	<li><tt>{@link kTAG_TERM_OFFSETS}</tt>: Term offsets.
	 *	<li><tt>{@link kTAG_NODE_OFFSETS}</tt>: Node offsets.
	 *	<li><tt>{@link kTAG_EDGE_OFFSETS}</tt>: Edge offsets.
	 *	<li><tt>{@link kTAG_UNIT_OFFSETS}</tt>: Unit offsets.
	 *	<li><tt>{@link kTAG_ENTITY_OFFSETS}</tt>: Entity offsets.
	 * </ul>
	 *
	 * @var string
	 */
	protected $mOffset = NULL;

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

	/**
	 * Format options.
	 *
	 * This protected data member holds a bitfield representing the format options:
	 *
	 * <ul>
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_DYNAMIC}</tt>: Exclude dynamic tags.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_PRIVATE}</tt>: Exclude private tags.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_NATIVES}</tt>: Include tag native identifiers in
	 *		formatted results with offset {@link kAPI_PARAM_TAG}.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_VALUES}</tt>: Include values in formatted results
	 *		with offset {@link kAPI_PARAM_RESPONSE_FRMT_VALUE}.
	 * </ul>
	 *
	 * @var array
	 */
	protected $mOptions = kFLAG_DEFAULT;

		

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
	 * The last parameter represents the format options:
	 *
	 * <ul>
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_DYNAMIC}</tt>: Exclude dynamic tags.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_PRIVATE}</tt>: Exclude private tags.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_NATIVES}</tt>: Include tag native identifiers in
	 *		formatted results with offset {@link kAPI_PARAM_TAG}.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_VALUES}</tt>: Include values in formatted results
	 *		with offset {@link kAPI_PARAM_RESPONSE_FRMT_VALUE}.
	 * </ul>
	 *
	 * @param ObjectIterator		$theIterator		Iterator.
	 * @param string				$theFormat			Data format.
	 * @param string				$theLanguage		Default language.
	 * @param string				$theDomain			Optional domain for columns.
	 * @param string				$theShape			Optional shape for markers.
	 * @param bitfield				$theOptions			Format options.
	 *
	 * @access public
	 */
	public function __construct( ObjectIterator $theIterator,
												$theFormat,
												$theLanguage,
												$theDomain = NULL,
												$theShape = NULL,
												$theOptions = kFLAG_DEFAULT )
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
		
		//
		// Store dynamic tags flag.
		//
		$this->options( $theOptions );

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

	 
	/*===================================================================================
	 *	options																			*
	 *==================================================================================*/

	/**
	 * Manage options
	 *
	 * This method can be used to set the options, provide <tt>NULL</tt> to retrieve the
	 * current value.
	 *
	 * The method does not allow resetting the flag.
	 *
	 * @param bitfield				$theFlag			Flag.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return bitfield				Current or previous flag.
	 */
	public function options( $theFlag = NULL, $getOld = FALSE )
	{
		//
		// Return current flag.
		//
		if( $theFlag === NULL )
			return $this->mOptions;													// ==>
		
		//
		// Save current flag.
		//
		$save = $this->mOptions;
		
		//
		// Set data member.
		//
		$this->mOptions = $theFlag;
		
		if( $getOld	)
			return $save;															// ==>
	
		return $theFlag;															// ==>
	
	} // options.

		

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
					if( $col == kAPI_PARAM_RESPONSE_FRMT_SCORE )
						$data[ $col ] = $value;
					
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
		$shape = $this->mShape[ kTAG_ID_HASH ];
		
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
		// Init local storage.
		//
		$offsets = Array();
		
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
			
			//
			// Check tag offsets.
			//
			$class = $object[ kTAG_CLASS ];
			if( $class::kSEQ_NAME == Tag::kSEQ_NAME )
				$this->CollectOffsetTags( $offsets, $object );
		
		} // Iterating objects.
		
		//
		// Handle tag offsets.
		//
		foreach( $offsets as $object )
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
			// Set record.
			//
			$this->setRecord( $this->mData, $object );
			
			//
			// Set dictionary xref.
			//
			$this->mDictionary[ kAPI_DICTIONARY_TAGS ]
							  [ $object[ kTAG_ID_HASH ] ]
				= $object[ kTAG_NID ];
		
		} // Iterating tag offsets.
		
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
			$this->mDictionary[ kAPI_PARAM_RESPONSE_FRMT_SCORE ]
				= $this->mIterator->current()[ kAPI_PARAM_RESPONSE_FRMT_SCORE ];
			
			//
			// Set score column.
			//
			$dict[ kAPI_PARAM_RESPONSE_FRMT_SCORE ]
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
				$dict[] = $tag[ kTAG_ID_HASH ];
			
			//
			// Format tag.
			//
			else
			{
				//
				// Allocate labels.
				//
				$dict[ $tag[ kTAG_ID_HASH ] ] = Array();
		
				//
				// Set labels.
				//
				$this->setTagLabel( $dict[ $tag[ kTAG_ID_HASH ] ], $tag );
			
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
		// Skip structure label.
		//
		if( (! array_key_exists( kTAG_ID_HASH, $tag ))
		 || ($tag[ kTAG_ID_HASH ] != kTAG_STRUCT_LABEL) )
		{
			//
			// Allocate property.
			//
			$theContainer[ $tag[ kTAG_ID_HASH ] ] = Array();
			$ref = & $theContainer[ $tag[ kTAG_ID_HASH ] ];

			//
			// Set labels.
			//
			$this->setTagLabel( $ref, $tag );
			
			//
			// Set tag identifier.
			//
			$this->setTagIdentifier( $ref, $tag );

			//
			// Set data type and kind.
			//
			$this->setTagType( $ref, $tag, TRUE );
		
			//
			// Set data value.
			//
			$this->setDataValue( $ref, $theValue, $tag );
			
		} // Not a structure label.
		
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
	 *	setTagIdentifier																*
	 *==================================================================================*/

	/**
	 * Set tag identifier
	 *
	 * The duty of this method is to set the provided container with the tag native
	 * identifier.
	 *
	 * @param array					$theContainer		Data container.
	 * @param array					$theTag				Tag array object.
	 *
	 * @access protected
	 *
	 * @see kFLAG_FORMAT_OPT_NATIVES kAPI_PARAM_TAG kTAG_NID
	 */
	protected function setTagIdentifier( &$theContainer, $theTag )
	{
		//
		// Check options.
		//
		if( $this->options() & kFLAG_FORMAT_OPT_NATIVES )
			$theContainer[ kAPI_PARAM_TAG ] = $theTag[ kTAG_NID ];
		
	} // setTagIdentifier.

	 
	/*===================================================================================
	 *	setTagType																		*
	 *==================================================================================*/

	/**
	 * Set tag type
	 *
	 * The duty of this method is to set the provided container with the tag data type and
	 * kind.
	 *
	 * The last parameter determines whether to set the data kind.
	 *
	 * @param array					$theContainer		Data container.
	 * @param array					$theTag				Tag array object.
	 * @param boolean				$setKind			Set data kind.
	 *
	 * @access protected
	 *
	 * @see kFLAG_FORMAT_OPT_NATIVES kAPI_PARAM_TAG kTAG_NID
	 */
	protected function setTagType( &$theContainer, $theTag, $setKind = TRUE )
	{
		//
		// Set data type.
		//
		$theContainer[ kAPI_PARAM_DATA_TYPE ] = $theTag[ kTAG_DATA_TYPE ];
		
		//
		// Set data kind.
		//
		if( $setKind
		 && array_key_exists( kTAG_DATA_KIND, $theTag ) )
			$theContainer[ kAPI_PARAM_DATA_KIND ]
				= $theTag[ kTAG_DATA_KIND ];
		
	} // setTagType.

	 
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
		// Check if list.
		//
		$is_list = ( array_key_exists( kTAG_DATA_KIND, $theTag ) &&
				  in_array( kTYPE_LIST, $theTag[ kTAG_DATA_KIND ] ) );
		
		//
		// Handle structures.
		//
		if( $theTag[ kTAG_DATA_TYPE ] == kTYPE_STRUCT )
		{
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
			if( $is_list )
			{
				//
				// Allocate list.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
				
				//
				// Iterate structures.
				//
				foreach( $theValue as $struct )
				{
					//
					// Allocate structure.
					//
					$container = Array();
					
					//
					// Load structure.
					//
					$this->setDataStructure( $container, $theTag, $struct, $offset );
					
					//
					// Add structure.
					//
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ][] = $container;
				
				} // Iterating structures.
			
			} // List of structures.
			
			//
			// Handle scalar structure.
			//
			else
				$this->setDataStructure( $theContainer, $theTag, $theValue, $offset );
		
		} // Data structure.
		
		//
		// Handle scalars.
		//
		else
		{
			//
			// Handle scalars list.
			//
			if( $is_list )
			{
				//
				// Parse by type.
				//
				switch( $theTag[ kTAG_DATA_TYPE ] )
				{
					//
					// Primitive types.
					//
					case kTYPE_MIXED:
					case kTYPE_STRING:
					case kTYPE_INT:
					case kTYPE_FLOAT:
					case kTYPE_BOOLEAN:
					case kTYPE_TEXT:
					case kTYPE_YEAR:
					case kTYPE_DATE:
					case kTYPE_URL:
						$this->formatDataValue( $theContainer, $theValue, $theTag );
						break;
					
					//
					// Other types.
					//
					default:
						$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
						foreach( $theValue as $value )
						{
							$container = Array();
							$this->formatDataValue( $container, $value, $theTag );
							$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ][] = $container;
						}
						break;
				
				} // Parsing by data type.
		
			} // List of scalars.
			
			//
			// Handle scalar.
			//
			else
				$this->formatDataValue( $theContainer, $theValue, $theTag );
		
		} // Data scalar.
				
	} // setDataValue.

	 
	/*===================================================================================
	 *	setDataStructure																	*
	 *==================================================================================*/

	/**
	 * Set structure
	 *
	 * The duty of this method is to set the provided container with the provided data
	 * structure.
	 *
	 * @param array					$theContainer		Data container.
	 * @param array					$theTag				Tag array object.
	 * @param mixed					$theValue			Data structure.
	 * @param string				$theOffset			Structure label offset.
	 *
	 * @access protected
	 */
	protected function setDataStructure( &$theContainer, $theTag, $theValue, $theOffset )
	{
		//
		// Copy data type.
		//
		if( ! array_key_exists( kAPI_PARAM_DATA_TYPE, $theContainer ) )
			$this->setTagType( $theContainer, $theTag, FALSE );
			
		//
		// Set structure label.
		//
		if( array_key_exists( $theOffset, $theValue ) )
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
				= $theValue[ $theOffset ];

		//
		// Allocate structure.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
	
		//
		// Load structure properties.
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
				
	} // setDataStructure.

	 

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
						  [ $tag[ kTAG_ID_HASH ] ]
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
			case kTYPE_ENUM:
			case kTYPE_SET:
				$this->formatEnumeration( $theContainer, $theValue, $theTag );
				if( $this->options() & kFLAG_FORMAT_OPT_VALUES )
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_VALUE ] = $theValue;
				break;
	
			//
			// Language strings.
			//
			case kTYPE_LANGUAGE_STRING:
			case kTYPE_LANGUAGE_STRINGS:
				$this->formatLanguageStrings( $theContainer, $theValue, $theTag );
				if( $this->options() & kFLAG_FORMAT_OPT_VALUES )
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_VALUE ] = $theValue;
				break;
	
			//
			// Typed list.
			//
			case kTYPE_TYPED_LIST:
				$this->formatTypedList( $theContainer, $theValue, $theTag );
				if( $this->options() & kFLAG_FORMAT_OPT_VALUES )
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_VALUE ] = $theValue;
				break;
	
			//
			// Geo JSON shape.
			//
			case kTYPE_SHAPE:
				$this->formatShape( $theContainer, $theValue, $theTag );
				if( $this->options() & kFLAG_FORMAT_OPT_VALUES )
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_VALUE ] = $theValue;
				break;
	
			//
			// Object reference.
			//
			case kTYPE_REF_SELF:
				if( $this->mCurrentUnit->offsetExists( kTAG_CLASS ) )
				{
					$theTag[ kTAG_DATA_TYPE ]
						= PersistentObject::ResolveTypeByClass(
							$this->mCurrentUnit->offsetGet( kTAG_CLASS ) );
					$this->formatDataValue( $theContainer, $theValue, $theTag );
				}
				break;
			
			case kTYPE_REF_TAG:
			case kTYPE_REF_TERM:
			case kTYPE_REF_NODE:
			case kTYPE_REF_EDGE:
			case kTYPE_REF_USER:
			case kTYPE_REF_UNIT:
				$this->formatObjectReference( $theContainer, $theValue, $theTag );
				if( $this->options() & kFLAG_FORMAT_OPT_VALUES )
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_VALUE ] = $theValue;
				break;
	
			//
			// Time-stamp.
			//
			case kTYPE_TIME_STAMP:
				$this->formatTimeStamp( $theContainer, $theValue, $theTag );
				if( $this->options() & kFLAG_FORMAT_OPT_VALUES )
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_VALUE ] = $theValue;
				break;
			
			//
			// Other.
			//
			default:
				$this->formatScalar( $theContainer, $theValue, $theTag );
				if( $this->options() & kFLAG_FORMAT_OPT_VALUES )
				{
					switch( $theTag[ kTAG_DATA_TYPE ] )
					{
						case kTYPE_MIXED:
						case kTYPE_INT:
						case kTYPE_FLOAT:
						case kTYPE_BOOLEAN:
						case kTYPE_YEAR:
						case kTYPE_DATE:
						case kTYPE_URL:
							$theContainer[ kAPI_PARAM_RESPONSE_FRMT_VALUE ] = $theValue;
							break;
					}
				}
				break;
		
		} // Parsed data type.
		
	} // formatDataValue.

	 
	/*===================================================================================
	 *	formatScalar																	*
	 *==================================================================================*/

	/**
	 * Format scalar value
	 *
	 * The duty of this method is to format the provided scalar value and set into the
	 * provided container.
	 *
	 * This method assumes the value is of a scalar native data type, which means that
	 * if the data kind is a list, the {@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter
	 * will contain an array of values.
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
			// Allocate display elements.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
		
			//
			// Iterate list.
			//
			foreach( $theValue as $key => $value )
			{
				//
				// Parse by data type.
				//
				switch( $theTag[ kTAG_DATA_TYPE ] )
				{
					case kTYPE_INT:
						$value = number_format( $value, 0 );
						break;
						
					case kTYPE_FLOAT:
						$value = number_format( $value, 2 );
						break;
						
					case kTYPE_BOOLEAN:
						$value = ( $value ) ? 'Yes' : 'No';
						break;
						
					case kTYPE_DATE:
						$value = DisplayDate( $value );
						break;
								
				} // Parsing by data type.
				
				//
				// Set value.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ][] = $value;
			
			} // Iterating list of values.
			
		} // List of values.
		
		//
		// Handle scalar.
		//
		else
		{
			//
			// Parse by data type.
			//
			switch( $theTag[ kTAG_DATA_TYPE ] )
			{
				case kTYPE_INT:
					$theValue = number_format( $theValue, 0 );
					break;
					
				case kTYPE_FLOAT:
					$theValue = number_format( $theValue, 2 );
					break;
					
				case kTYPE_BOOLEAN:
					$theValue = ( $theValue ) ? 'Yes' : 'No';
					break;
					
				case kTYPE_DATE:
					$theValue = DisplayDate( $theValue );
					break;
							
			} // Parsing by data type.
			
			//
			// Set value.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $theValue;
		
		} // Scalar value.
		
	} // formatScalar.

	 
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
			// Allocate list.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
			
			//
			// Iterate list.
			//
			foreach( $theValue as $value )
			{
				//
				// Allocate element.
				//
				$element = Array();
			
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
				$element[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $term[ kTAG_LABEL ];
	
				//
				// Set definition.
				//
				if( array_key_exists( kTAG_DEFINITION, $term ) )
					$element[ kAPI_PARAM_RESPONSE_FRMT_INFO ]
						= $term[ kTAG_DEFINITION ];
				
				//
				// Add element.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ][] = $element;
		
			} // Iterating values.
			
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
			// Hamdle definition.
			//
			if( array_key_exists( kTAG_DEFINITION, $term ) )
			{
				//
				// Allocate block.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
			
				//
				// Set label.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
							 [ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= $term[ kTAG_LABEL ];
		
				//
				// Set definition.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
							 [ kAPI_PARAM_RESPONSE_FRMT_INFO ]
					= $term[ kTAG_DEFINITION ];
			
			} // Has definition.
			
			//
			// Handle label only.
			//
			else
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= $term[ kTAG_LABEL ];
		
		} // Scalar value.
		
	} // formatEnumeration.

	 
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
		// Allocate element.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
		
		//
		// Iterate elements.
		//
		foreach( $theValue as $value )
		{
			//
			// Allocate display block.
			//
			$element = Array();
			
			//
			// Set label.
			//
			if( array_key_exists( kTAG_LANGUAGE, $value ) )
				$element[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
					= ( $value[ kTAG_LANGUAGE ] == '0' )
					? 'Default'
					: $value[ kTAG_LANGUAGE ];
			
			//
			// Set value.
			//
			$element[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
				= $value[ kTAG_TEXT ];
			
			//
			// Add element.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ][] = $element;
		
		} // Iterating elements.
		
	} // formatLanguageStrings.

	 
	/*===================================================================================
	 *	formatTypedList																	*
	 *==================================================================================*/

	/**
	 * Format typed list
	 *
	 * The duty of this method is to format the provided typed list value and set into the
	 * provided container.
	 *
	 * Each element uses the {@link kTAG_TYPE} as the {@link kAPI_PARAM_RESPONSE_FRMT_NAME}
	 * by default, the other items of the element determine what the element represents:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_URL}</tt>: An URL or link:
	 *	 <ul>
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_LINK}</tt>: Will contain the URL.
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: The {@link kTAG_TEXT} item
	 *			if there, or the URL.
	 *	 </ul>
	 *	<li><tt>{@link kTAG_NID}</tt>: A native identifier:
	 *	 <ul>
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: Will contain the identifier.
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: If not set, it will be
	 *			set as <tt>ID</tt>.
	 *	 </ul>
	 *	<li><tt>{@link kTAG_CLASS}</tt>: A class:
	 *	 <ul>
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: Will contain the value.
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: If not set, it will be
	 *			set as <tt>Class</tt>.
	 *	 </ul>
	 *	<li><tt>{@link kTAG_LANGUAGE}</tt>: A language:
	 *	 <ul>
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: Will contain the value.
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: If not set, it will be
	 *			set as <tt>Language</tt>.
	 *	 </ul>
	 *	<li><tt>{@link kTAG_TEXT}</tt>: A text:
	 *	 <ul>
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: Will contain the value.
	 *	 </ul>
	 *	<li><em>Object references</em>:
	 *	 <ul>
	 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: The object(s) name.
	 *		<li><tt>{@link kAPI_DICTIONARY_COLLECTION}</tt>: The object(s) collection.
	 *		<li><tt>{@link kAPI_PARAM_ID}</tt>: The object(s) identifiers.
	 *	 </ul>
	 * </ul>
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
		// Allocate list.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
		
		//
		// Iterate elements.
		//
		foreach( $theValue as $value )
		{
			//
			// Allocate display block.
			//
			$element = Array();
			
			//
			// Set default label.
			//
			if( array_key_exists( kTAG_TYPE, $value ) )
				$element[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
					= $value[ kTAG_TYPE ];
			
			//
			// Set URL.
			// If the element has an URL, it means that it is an URL.
			//
			if( array_key_exists( kTAG_URL, $value ) )
			{
				//
				// Set value.
				//
				$element[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= ( array_key_exists( kTAG_TEXT, $value ) )
					? $value[ kTAG_TEXT ]
					: $value[ kTAG_URL ];
			
				//
				// Set link.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_LINK ]
					= $value[ kTAG_URL ];
		
			} // URL value.
			
			//
			// Handle native identifier.
			// 
			//
			elseif( array_key_exists( kTAG_NID, $value ) )
			{
				//
				// Set label.
				//
				if( ! array_key_exists( kAPI_PARAM_RESPONSE_FRMT_NAME, $element ) )
					$element[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
						= 'ID';
				
				//
				// Set value.
				//
				$element[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $value[ kTAG_NID ];
			}
			
			//
			// Handle class.
			//
			elseif( array_key_exists( kTAG_CLASS, $value ) )
			{
				//
				// Set label.
				//
				if( ! array_key_exists( kAPI_PARAM_RESPONSE_FRMT_NAME, $element ) )
					$element[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
						= 'Class';
				
				//
				// Set value.
				//
				$element[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $value[ kTAG_CLASS ];
			}
			
			//
			// Handle language.
			//
			elseif( array_key_exists( kTAG_LANGUAGE, $value ) )
			{
				//
				// Set label.
				//
				if( ! array_key_exists( kAPI_PARAM_RESPONSE_FRMT_NAME, $element ) )
					$element[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
						= 'Language';
				
				//
				// Set value.
				//
				$element[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = $value[ kTAG_LANGUAGE ];
			}
			
			//
			// Handle text.
			//
			elseif( array_key_exists( kTAG_TEXT, $value ) )
				$element[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= $value[ kTAG_TEXT ];
			
			//
			// Handle object references.
			//
			else
				$this->formatObjectReference( $element, $value, $theTag );
			
			//
			// Add element.
			//
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ][] = $element;
		
		} // Iterating elements.
		
	} // formatTypedList.

	 
	/*===================================================================================
	 *	formatObjectReference															*
	 *==================================================================================*/

	/**
	 * Format object reference
	 *
	 * The duty of this method is to format the provided object reference and set into the
	 * provided container:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DISP}</tt>: The object(s) name.
	 *	<li><tt>{@link kAPI_DICTIONARY_COLLECTION}</tt>: The object(s) collection.
	 *	<li><tt>{@link kAPI_PARAM_ID}</tt>: The object(s) identifiers.
	 * </ul>
	 *
	 * @param array					$theContainer		Data container.
	 * @param mixed					$theValue			Typed list element.
	 * @param array					$theTag				Offset tag.
	 *
	 * @access protected
	 */
	protected function formatObjectReference( &$theContainer, $theValue, $theTag )
	{
		//
		// Init local storage.
		//
		$refs = array( kTAG_TAG_REF => Tag::kSEQ_NAME,
					   kTAG_TERM_REF => Term::kSEQ_NAME,
					   kTAG_NODE_REF => Node::kSEQ_NAME,
					   kTAG_EDGE_REF => Edge::kSEQ_NAME,
					   kTAG_USER_REF => User::kSEQ_NAME,
					   kTAG_UNIT_REF => UnitObject::kSEQ_NAME );
		
		//
		// Identify reference property.
		//
		$reference = Array();
		foreach( $refs as $key => $val )
		{
			//
			// Match element property.
			//
			if( array_key_exists( $key, $theValue ) )
			{
				$reference[ 'class' ] = $val;
				$reference[ 'value' ] = $theValue[ $key ];
				
				break;														// =>
			
			} // Matched element property.
		
		} // Iterating object reference offsets.
		
		//
		// Handle matched reference class.
		//
		if( count( $reference ) )
		{
			//
			// Set object collection.
			//
			$theContainer[ kAPI_DICTIONARY_COLLECTION ]
				= $reference[ 'class' ];
	
			//
			// Set object identifier.
			//
			$theContainer[ kAPI_PARAM_ID ]
				= $reference[ 'value' ];

			//
			// Handle references list.
			//
			if( is_array( $reference[ 'value' ] ) )
			{
				//
				// Allocate list.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = Array();
				
				//
				// Iterate list.
				//
				foreach( $reference[ 'value' ] as $value )
				{
					//
					// Resolve object.
					//
					$object
						= PersistentObject::ResolveObject(
							$this->mIterator->collection()->dictionary(),
							$reference[ 'class' ],
							$value,
							TRUE );
		
					//
					// Set object name.
					//
					$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ][]
						= $object->getName( $this->mLanguage );
				
				} // Iterating list.
			
			} // List of references.
			
			//
			// Handle single reference.
			//
			else
			{
				//
				// Resolve object.
				//
				$object
					= PersistentObject::ResolveObject(
						$this->mIterator->collection()->dictionary(),
						$reference[ 'class' ],
						$reference[ 'value' ],
						TRUE );
		
				//
				// Set object name.
				//
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ]
					= $object->getName( $this->mLanguage );
			
			} // Single reference.
		
		} // Matched reference class.
		
	} // formatObjectReference.

	 
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
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DISP ] = 'View on map';
		
		//
		// Set object map label.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_MAP_LABEL ]
			= $this->mCurrentUnit[ kTAG_NID ];
		
		//
		// Set object map shape.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_MAP_SHAPE ]
			= $theValue;
		
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
		$ref[ kAPI_PARAM_DATA ] = kAPI_RESULT_ENUM_DATA_FORMAT;
		
	} // formatShape.

	 
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
					? $theValue[ kTAG_ID_HASH ]
					: $theValue[ kTAG_NID ];
			else
			{
				if( $doSequence )
					$id = ( substr( $theValue, 0, 1 ) == kTOKEN_TAG_PREFIX )
						? $theValue
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
					if( substr( $id, 0, 1 ) == kTOKEN_TAG_PREFIX )
						$criteria = array( kTAG_ID_HASH => $id );
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
		// Handle users.
		//
		$this->mHidden = ( $class == (kPATH_NAMESPACE_ROOT.'\User') )
					   ? array( kTAG_CONN_PASS )
					   : Array();
		
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
						$this->mHidden,
						$class::DynamicOffsets(),
						$class::InternalOffsets(),
						$this->collectExcludedOffsets( $class ),
						array( kTAG_GEO_SHAPE, kTAG_GEO_SHAPE_DISP ) );
				// MILKO - Excluded display shapes.
				
				break;
			
			case kAPI_RESULT_ENUM_DATA_RECORD:
				//
				// Set exceptions.
				//
				$this->mHidden
					= array_merge(
						$this->mHidden,
						$class::InternalOffsets(),
						$this->collectExcludedOffsets( $class ) );
				
				break;
		}
		
		//
		// Normalise list.
		//
		$this->mHidden = array_values( array_unique( $this->mHidden ) );
		
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

	 
	/*===================================================================================
	 *	CollectOffsetTags																*
	 *==================================================================================*/

	/**
	 * Collect offset tags
	 *
	 * This method will collect the offset tag objects of the provided tag into the provided
	 * container.
	 *
	 * @param array					$theContainer		Receives tag objects.
	 * @param PersistentObject		$theTag				Tag object or array object.
	 *
	 * @access protected
	 */
	protected function CollectOffsetTags( &$theContainer, PersistentObject $theTag )
	{
		//
		// Init local storage.
		//
		$offsets = array( kTAG_TAG_OFFSETS, kTAG_TERM_OFFSETS, kTAG_NODE_OFFSETS,
						  kTAG_EDGE_OFFSETS, kTAG_ENTITY_OFFSETS, kTAG_UNIT_OFFSETS );
		
		//
		// Iterate tags.
		//
		foreach( $offsets as $offset )
		{
			//
			// Check for offset.
			//
			if( $theTag->offsetExists( $offset ) )
			{
				//
				// Iterate offsets.
				//
				foreach( $theTag[ $offset ] as $element )
				{
					//
					// Parse nested offset tag.
					//
					$tags = explode( '.', $element );
					if( count( $tags ) > 1 )
					{
						//
						// Iterate nested tags.
						//
						for( $i = 0; $i < (count( $tags ) - 1); $i++ )
						{
							//
							// Add if not duplicate.
							//
							if( ! array_key_exists( $tags[ $i ], $theContainer ) )
								$theContainer[ $tags[ $i ] ]
									= new Tag(
										$this->mIterator
											->collection()
												->dictionary(),
										$this->mIterator
											->collection()
												->dictionary()
													->getObject( $tags[ $i ] )
														[ kTAG_NID ] );
						}
					}
				}
			}
		}
	
	} // CollectOffsetTags.


	 
/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	collectExcludedOffsets															*
	 *==================================================================================*/

	/**
	 * Get excluded offsets
	 *
	 * The duty of this method is to return the list of offsets to be excluded according to
	 * the current object's options.
	 *
	 * @param string				$theClass			Current object's class.
	 *
	 * @access protected
	 * @return array				List of excluded offsets according to object options.
	 */
	protected function collectExcludedOffsets( $theClass )
	{
		//
		// Init local storage.
		//
		$offsets = Array();
		$options = $this->options();
		
		//
		// Collect dynamic offsets.
		//
		if( $options & kFLAG_FORMAT_OPT_DYNAMIC )
		{
			foreach( $theClass::DynamicOffsets() as $offset )
				$offsets[ $offset ] = $offset;
		}
		
		//
		// Collect private offsets.
		//
		if( $options & kFLAG_FORMAT_OPT_PRIVATE )
		{
			foreach( $theClass::PrivateOffsets() as $offset )
				$offsets[ $offset ] = $offset;
		}
		
		return array_values( $offsets );											// ==>
		
	} // collectExcludedOffsets.

	 

} // class IteratorSerialiser.


?>
