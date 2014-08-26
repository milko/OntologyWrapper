<?php

/**
 * ForestUnit.php
 *
 * This file contains the definition of the {@link ForestUnit} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\UnitObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									ForestUnit.php										*
 *																						*
 *======================================================================================*/

/**
 * Forest Gene Conservation Unit
 *
 * This class is derived from the {@link UnitObject} class, it implements a forest gene
 * conservation unit which uses the FCU standards as its default properties.
 *
 * The inherited attributes have the following function:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: By default the class sets the {@link kDOMAIN_FOREST}
 *		constant.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The authority is set with the first three
 *		characters of the unit number.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The identifier is set with the value of the
 *		<tt>fcu:unit:number</tt> tag starting from character 4.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: This property is not handled by default.
 *	<li><tt>{@link kTAG_VERSION}</tt>: This attribute is set with the value of the
 *		<tt>fcu:unit:data-collection</tt> tag.
 * </ul>
 *
 * The object can be considered initialised when it has at least the domain, identifier and
 * version.
 *
 *	@author		Milko A. kofi <m.skofic@cgiar.org>
 *	@version	1.00 05/06/2014
 */
class ForestUnit extends UnitObject
{
	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_FOREST;

		

/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * Instantiate class.
	 *
	 * In this class we link the inited status with the presence of the version.
	 *
	 * The constructor will automatically set the object domain to the default class domain.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @uses instantiateObject()
	 * @uses TermCount()
	 * @uses isInited()
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Load object with contents.
		//
		parent::__construct( $theContainer, $theIdentifier );
		
		//
		// Set default domain.
		//
		if( ! $this->offsetExists( kTAG_DOMAIN ) )
			$this->offsetSet( kTAG_DOMAIN, static::kDEFAULT_DOMAIN );
		
		//
		// Set initialised status.
		//
		$this->isInited( parent::isInited() &&
						 \ArrayObject::offsetExists( kTAG_VERSION ) );

	} // Constructor.

	

/*=======================================================================================
 *																						*
 *							PUBLIC NAME MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Get object name
	 *
	 * In this class we return the unit {@link kTAG_AUTHORITY} concatenated with the
	 * {@link kTAG_IDENTIFIER} and the {@link kTAG_VERSION} separated by a slash,
	 * concatenated to the domain name.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 */
	public function getName( $theLanguage )
	{
		//
		// Init local storage
		//
		$name = parent::getName( $theLanguage );
		
		return ( $name !== NULL )
			 ? ($name.' '.$this->offsetGet( kTAG_AUTHORITY )
						 .$this->offsetGet( kTAG_IDENTIFIER )
						 .'/'.$this->offsetGet( kTAG_VERSION ))						// ==>
			 : ($this->offsetGet( kTAG_AUTHORITY )
			   .$this->offsetGet( kTAG_IDENTIFIER )
			   .'/'.$this->offsetGet( kTAG_VERSION ));								// ==>
	
	} // getName.

		

/*=======================================================================================
 *																						*
 *								STATIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	CreateIndexes																	*
	 *==================================================================================*/

	/**
	 * Create indexes
	 *
	 * In this class we index the following offsets:
	 *
	 * <ul>
	 * </ul>
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 *
	 * @static
	 * @return CollectionObject		The collection.
	 */
	static function CreateIndexes( DatabaseObject $theDatabase )
	{
		//
		// Init local storage.
		//
		$taxon = $this->resolveOffset( ':taxon:epithet' );
		
		//
		// Set parent indexes and retrieve collection.
		//
		$collection = parent::CreateIndexes( $theDatabase );
		
		//
		// Set country index.
		//
		$collection->createIndex(
			array( $this->resolveOffset( ':location:country' ) => 1 ),
			array( "name" => "COUNTRY",
				   "sparse" => TRUE ) );
		
		//
		// Set elevation index.
		//
		$collection->createIndex(
			array( $this->resolveOffset( ':location:elevation:min' ) => 1 ),
			array( "name" => "ELEVATION_MIN",
				   "sparse" => TRUE ) );
		$collection->createIndex(
			array( $this->resolveOffset( ':location:elevation:max' ) => 1 ),
			array( "name" => "ELEVATION_MAX",
				   "sparse" => TRUE ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return the parent offsets and the results of
	 * {@link collectStructureOffsets()} of the <tt>struct:fcu:unit</tt> structure node
	 * (PID).
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							$this->mDictionary
								->collectStructureOffsets( 'struct:fcu:unit' ) );	// ==>
	
	} // DefaultOffsets.

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * In this class we link the inited status with the presence of the version.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_VERSION
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method to resolve offset.
		//
		parent::postOffsetSet( $theOffset, $theValue );
		
		//
		// Set initialised status.
		//
		$this->isInited( parent::isInited() &&
						 \ArrayObject::offsetExists( kTAG_VERSION ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of the version.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_VERSION
	 */
	protected function postOffsetUnset( &$theOffset )
	{
		//
		// Call parent method to resolve offset.
		//
		parent::postOffsetUnset( $theOffset );
		
		//
		// Set initialised status.
		//
		$this->isInited( parent::isInited() &&
						 \ArrayObject::offsetExists( kTAG_VERSION ) );
	
	} // postOffsetUnset.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitPrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before commit
	 *
	 * In this class we overload this method to set the default domain, identifier and
	 * version, if not yet set.
	 *
	 * Once we do this, we call the parent method.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Init local storage.
		//
		$id = $this->offsetGet( 'fcu:unit:number' );
		
		//
		// Check domain.
		//
		if( ! $this->offsetExists( kTAG_DOMAIN ) )
			$this->offsetSet( kTAG_DOMAIN,
							  static::kDEFAULT_DOMAIN );
		
		//
		// Check identifier.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
			$this->offsetSet( kTAG_IDENTIFIER, substr( $id, 3 ) );
		
		//
		// Check authority.
		//
		if( ! $this->offsetExists( kTAG_AUTHORITY ) )
			$this->offsetSet( kTAG_AUTHORITY, substr( $id, 0, 3 ) );
		
		//
		// Check version.
		//
		if( ! $this->offsetExists( kTAG_VERSION ) )
			$this->offsetSet( kTAG_VERSION,
							  $this->offsetGet( 'fcu:unit:data-collection' ) );
		
		//
		// Create shape.
		//
		if( ! $this->offsetExists( kTAG_GEO_SHAPE ) )
		{
			//
			// Check coordinates.
			//
			if( $this->offsetExists( ':location:latitude' )
			 && $this->offsetExists( ':location:longitude' ) )
				$this->offsetSet( kTAG_GEO_SHAPE,
								  array( kTAG_TYPE => 'Point',
								  		 kTAG_GEOMETRY => array(
								  		 	$this->offsetGet( ':location:longitude' ),
								  		 	$this->offsetGet( ':location:latitude' ) ) ) );
		
		} // Shape not yet set.
	
		//
		// Load climate data.
		//
		if( $this->offsetExists( kTAG_GEO_SHAPE ) )
		{
		
		} // Shape not yet set.
	
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
	
	} // preCommitPrepare.

	 

} // class ForestUnit.


?>
