<?php

/**
 * UnitIteratorSerialiser.php
 *
 * This file contains the definition of the {@link UnitIteratorSerialiser} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ContainerObject;
use OntologyWrapper\IteratorObject;

/*=======================================================================================
 *																						*
 *								UnitIteratorSerialiser.php								*
 *																						*
 *======================================================================================*/

/**
 * API.
 *
 * This file contains the API definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );

/**
 * Unit iterator serialiser
 *
 * The duty of this class is to serialise unit query data returned by the services into a
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
class UnitIteratorSerialiser
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
	 * @param IteratorObject		$theIterator		Iterator.
	 * @param string				$theFormat			Data format.
	 * @param string				$theLanguage		Default language.
	 * @param string				$theDomain			Optional domain for columns.
	 * @param string				$theShape			Optional shape for markers.
	 *
	 * @access public
	 */
	public function __construct( IteratorObject $theIterator,
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
	 * @param IteratorObject		$theIterator		Iterator.
	 * @param boolean				$getOld				TRUE get old value.
	 *
	 * @access public
	 * @return IteratorObject		Current or previous iterator.
	 */
	public function iterator( $theIterator = NULL, $getOld = FALSE )
	{
		//
		// Handle new iterator.
		//
		if( $theIterator instanceof IteratorObject )
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
		$this->cacheTerm( $this->mIterator->collection()->dictionary(), $theDomain );
		
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
		$this->cacheTag( $this->mIterator->collection()->dictionary(), $theShape );
		
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
		// Init local storage.
		//
		$this->mDictionary[ kAPI_DICTIONARY_LIST_COLS ] = Array();
		$dict = & $this->mDictionary[ kAPI_DICTIONARY_LIST_COLS ];
		$wrapper = $this->mIterator->collection()->dictionary();
		
		//
		// Set table columns.
		//
		foreach( UnitObject::ListOffsets( $this->mDomain[ kTAG_NID ] ) as $col )
		{
			//
			// Cache tag.
			//
			$tag = $this->cacheTag( $wrapper, $col );
			
			//
			// Allocate labels.
			//
			$dict[ $tag[ kTAG_ID_SEQUENCE ] ] = Array();
			
			//
			// Set labels.
			//
			$this->setTagLabel( $dict[ $tag[ kTAG_ID_SEQUENCE ] ], $tag );
		
		} // Iterating columns.
		
		//
		// Iterate iterator.
		//
		$cols = array_keys( $dict );
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
				// Handle value.
				//
				if( ($value = $object[ $col ]) !== NULL )
				{
					//
					// Allocate value.
					//
					$data[ $col ] = Array();
					
					//
					// Format value.
					//
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
		// Init local storage.
		//
		$wrapper = $this->mIterator->collection()->dictionary();
		
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
			$this->setHiddenTags();
			
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
				// Handle publishable tags.
				//
				if( ! in_array( $key, $this->mHidden ) )
				{
					//
					// Cache tag.
					//
					$tag = $this->cacheTag( $wrapper, $key );
			
					//
					// Set labels.
					//
					$this->setTagLabel( $data, $tag );
			
					//
					// Set values.
					//
					$this->setDataValue( $data, $value, $tag );
				
				} // Publishable tag.
			
			} // Iterating object properties.
		
		} // Iterating objects.
		
	} // serialiseFormatted.

	 
	/*===================================================================================
	 *	serialiseMarkers																*
	 *==================================================================================*/

	/**
	 * Serialise markers
	 *
	 * This method will serialise the iterator data for markers
	 *
	 * @access protected
	 */
	protected function serialiseMarkers()
	{
		
	} // serialiseMarkers.

	 
	/*===================================================================================
	 *	serialiseRecords																*
	 *==================================================================================*/

	/**
	 * Serialise records
	 *
	 * This method will serialise the iterator data for aggregated records
	 *
	 * @access protected
	 */
	protected function serialiseRecords()
	{
		
	} // serialiseRecords.

	 

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
	 * This method will load the provided tag into the cache, if not yet there and return
	 * the tag object as an array.
	 *
	 * The provided parameter may be an array, in that case the method will add all the
	 * provided tags and return an array of tag array objects indexed by tag sequence
	 * number.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theTag				Tag native identifier or sequence.
	 *
	 * @access protected
	 */
	protected function cacheTag( Wrapper $theWrapper, $theTag )
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
				$object = $this->cacheTag( $theWrapper, $tag );
				
				//
				// Add tag.
				//
				if( ! array_key_exists( $tag, $result ) )
					$result[ $tag ] = $object;
			
			} // Iterating tags.
			
			return $result;															// ==>
		
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
										kQUERY_ARRAY );
				
				//
				// Normalise strings.
				//
				$strings = array( kTAG_LABEL, kTAG_DESCRIPTION );
				foreach( $strings as $string )
				{
					if( array_key_exists(
						$string, $this->mCache[ Tag::kSEQ_NAME ][ $theTag ] ) )
						$this->mCache[ Tag::kSEQ_NAME ][ $theTag ][ $string ]
							= OntologyObject::SelectLanguageString(
								$this->mCache[ Tag::kSEQ_NAME ][ $theTag ][ $string ],
								$this->mLanguage );
				
				} // Normalising strings.
		
			} // New entry.
			
			return $this->mCache[ Tag::kSEQ_NAME ][ $theTag ];						// ==>
		
		} // Provided scalar tag.
		
	} // cacheTag.

	 
	/*===================================================================================
	 *	cacheTerm																		*
	 *==================================================================================*/

	/**
	 * Load tag in cache
	 *
	 * This method will load the provided tag into the cache, if not yet there and return
	 * the tag object as an array.
	 *
	 * The provided parameter may be an array, in that case the method will add all the
	 * provided tags and return an array of tag array objects indexed by tag sequence
	 * number.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theTerm			Term native identifier.
	 *
	 * @access protected
	 */
	protected function cacheTerm( Wrapper $theWrapper, $theTerm )
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
				$object = $this->cacheTerm( $theWrapper, $term );
				
				//
				// Add term.
				//
				if( ! array_key_exists( $term, $result ) )
					$result[ $term ] = $object;
			
			} // Iterating terms.
			
			return $result;															// ==>
		
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
							->matchOne( array( kTAG_NID => $theTerm ), kQUERY_ARRAY );
				
				//
				// Normalise strings.
				//
				$strings = array( kTAG_LABEL, kTAG_DEFINITION );
				foreach( $strings as $string )
				{
					if( array_key_exists(
						$string, $this->mCache[ Term::kSEQ_NAME ][ $theTerm ] ) )
						$this->mCache[ Term::kSEQ_NAME ][ $theTerm ][ $string ]
							= OntologyObject::SelectLanguageString(
								$this->mCache[ Term::kSEQ_NAME ][ $theTerm ][ $string ],
								$this->mLanguage );
				
				} // Normalising strings.
		
			} // New entry.
			
			return $this->mCache[ Term::kSEQ_NAME ][ $theTerm ];					// ==>
		
		} // Provided scalar term.
		
	} // cacheTerm.

	 

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
	 * @access protected
	 */
	protected function setHiddenTags()
	{
		//
		// Check format.
		//
		if( $this->mFormat == kAPI_RESULT_ENUM_DATA_FORMAT )
		{
			//
			// Save object class.
			//
			$class = $object[ kTAG_CLASS ];
			
			//
			// Set exceptions.
			//
			$this->mHidden
				= array_merge(
					$class::DynamicOffsets(),
					$class::InternalOffsets() );
		
		} // Handle formatted.
		
		else
			$this->mHidden = Array();
		
	} // setHiddenTags.

	 
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
	 * This method expcets the current offset to have been cached.
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
			
			} // List of scalars.
			
			//
			// Scalar.
			//
			else
			{
				//
				// Format data.
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
						break;
			
					//
					// Language strings.
					//
					case kTYPE_LANGUAGE_STRINGS:
						$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
							= kAPI_PARAM_RESPONSE_TYPE_TYPED;
						break;
			
					//
					// Unit reference.
					//
					case kTYPE_REF_SELF:
					case kTYPE_REF_UNIT:
						$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
							= kAPI_PARAM_RESPONSE_TYPE_OBJECT;
						break;
			
					//
					// Geo JSON shape.
					//
					case kTYPE_SHAPE:
						$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
							= kAPI_PARAM_RESPONSE_TYPE_SHAPE;
						break;
			
					//
					// Boolean.
					//
					case kTYPE_BOOLEAN:
						$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
							= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
						break;
			
					//
					// Integer.
					//
					case kTYPE_INT:
						$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
							= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
						break;
			
					//
					// Float.
					//
					case kTYPE_FLOAT:
						$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
							= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
						break;
			
					//
					// Time-stamp.
					//
					case kTYPE_TIME_STAMP:
						$theContainer[ kAPI_PARAM_RESPONSE_FRMT_TYPE ]
							= kAPI_PARAM_RESPONSE_TYPE_SCALAR;
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
			
			} // Scalar.
		
		} // Scalar.
		
	} // setDataValue.

	 
	/*===================================================================================
	 *	formatEnumeration																*
	 *==================================================================================*/

	/**
	 * Format enumerated value
	 *
	 * The duty of this method is to format the provided enumerated value or set into the
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
	 *	formatScalar																	*
	 *==================================================================================*/

	/**
	 * Format scalar value
	 *
	 * The duty of this method is to format the provided scalar value or set into the
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

	 

} // class UnitIteratorSerialiser.


?>
