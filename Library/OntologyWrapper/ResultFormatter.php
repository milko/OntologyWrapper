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
 * The second block is an array containing all the objects contained in the provided
 * iterator, each array element is indexed by the object's native identifier ans the value
 * is formatted as a nested array of elements corresoinding to the object's data members and
 * structured as follows:
 *
 * <ul>
 *	<li><tt>name</tt>: This element holds the data property label.
 *	<li><tt>info</tt>: This element holds the data property description.
 *	<li><tt>data</tt>: This element holds the formatted data property value as a string.
 *	<li><tt>link</tt>: If the property is an URl, this element will hold its address.
 *	<li><tt>serv</tt>: If the property is a reference to another object, this element will
 *		hold the native identifier of the object.
 *	<li><tt>coll</tt>: If the property is a reference to another object, this element will
 *		hold the collection name for that object.
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
	 * Tag cache.
	 *
	 * This protected data member holds the tags cache.
	 *
	 * @var array
	 */
	protected $mTagCache = Array();

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
	 * This method will iterate the results set aggregating the data, the method will return
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
					= array_diff(
						array_merge(											// Exclude
							$class::InternalOffsets(),							// internal,
							$class::DynamicOffsets(),							// dynamic,
							array( kTAG_TAG_OFFSETS, kTAG_TERM_OFFSETS,			// offsets
								   kTAG_NODE_OFFSETS, kTAG_EDGE_OFFSETS,
								   kTAG_UNIT_OFFSETS, kTAG_ENTITY_OFFSETS ) ),	// except
						array( kTAG_RECORD_CREATED, kTAG_RECORD_MODIFIED ),		// stamps
						array( kTAG_MIN_VAL, kTAG_MAX_VAL ) );					// and range
								
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
				// Allocate object in results.
				//
				$index = count( $results );
				$results[ $index ] = Array();
				$results_object = & $results[ $index ];
		
				//
				// Iterate object properties.
				//
				foreach( $object as $key => $value )
				{
					//
					// Handle published tags.
					//
					if( in_array(
							$key,
							array_merge(
								$tags,
								PersistentObject::GetReferenceCounts() ) ) )
					{
						//
						// Allocate property.
						//
						$results_object[ $key ] = Array();
						$results_property = & $results_object[ $key ];
		
						//
						// Collect offset data types.
						//
						if( ! array_key_exists( $key, $this->mTagCache ) )
							$this->mTagCache[ $key ]
								= Tag::ResolveCollection(
									Tag::ResolveDatabase(
										$this->mIterator->collection()->dictionary() ) )
											->matchOne(
												array( kTAG_ID_SEQUENCE => $key ) );
						//
						// Get label and description.
						//
						$results_property[ 'name' ]
							= OntologyObject::SelectLanguageString(
									$this->mTagCache[ $key ][ kTAG_LABEL ],
									$theLanguage );
						if( $this->mTagCache[ $key ]->offsetExists( kTAG_DESCRIPTION ) )
							$results_property[ 'info' ]
								= OntologyObject::SelectLanguageString(
										$this->mTagCache[ $key ][ kTAG_DESCRIPTION ],
										$theLanguage );
			
					} // Publishable tag.
		
				} // Iterated object properties.
			
			} // Iterating objects.
		
			//
			// Signal processed.
			//
			$this->mProcessed = TRUE;
	
		} // Not processed.
	
		return $this->mResults;														// ==>
	
	} // format.

	 

/*=======================================================================================
 *																						*
 *							PROTECTED PROCESSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	formatObject																	*
	 *==================================================================================*/

	/**
	 * Format object
	 *
	 * This method will parse the provided object.
	 *
	 * @param array					$theObject			Object array reference.
	 * @param array					$theTags			Object tags.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 */
	protected function formatObject( &$theObject,
									 &$theTags,
									  $theLanguage )
	{
		
	} // formatObject.

	 

} // class ResultFormatter.


?>
