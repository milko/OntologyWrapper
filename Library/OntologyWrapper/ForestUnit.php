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
 *							PUBLIC CLIMATE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setClimateData																	*
	 *==================================================================================*/

	/**
	 * Set climate data
	 *
	 * This method can be used to set the climate data according to the provided parameters.
	 *
	 * This method is called automatically at commit time, but you may want to provide
	 * custom parameters when setting it.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDefDist</b>: Default error distance. This represents the default value
	 *		of the coordinate uncertainty expressed as the radius of a circle originating
	 *		from the object coordinates in meters. When providing climate data for an
	 *		elevation range, the coordinate uncertainty must be provided, if this value is
	 *		not available, it will be set with this parameter. The default value is taken
	 *		from the constant {@link kCLIMATE_DEF_DIST}.
	 *	<li><b>$theMinElev</b>: Minimum elevation range. This represents the minimum
	 *		elevation range. If the range is smaller than this value, it will be adjusted
	 *		to this value. The default value is taken from the constant
	 *		{@link kCLIMATE_DELTA_ELEV}.
	 * </ul>
	 *
	 * The method expects the object's data dictionary to have been set and will create the
	 * shape property if not yet set.
	 *
	 * @param integer				$theDefDist			Default coordinate uncertainty.
	 * @param integer				$theMinElev			Minimum elevation range.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> if the climate was set.
	 */
	public function setClimateData( $theDefDist = kCLIMATE_DEF_DIST,
									$theMinElev = kCLIMATE_DELTA_ELEV )
	{
		//
		// Create shapes.
		//
		if( $this->setObjectShapes() )
		{
			//
			// Check environment.
			//
			if( $this->offsetExists( ':environment' ) )
				return TRUE;														// ==>
			
			//
			// Init local storage.
			//
			$range = $dist = NULL;
		
			//
			// Handle elevation range.
			//
			if( $this->offsetExists( ':location:site:elevation:min' )
			 && $this->offsetExists( ':location:site:elevation:max' ) )
			{
				//
				// Reorder range.
				//
				$min = $this->offsetGet( ':location:site:elevation:min' );
				$max = $this->offsetGet( ':location:site:elevation:max' );
				if( $min > $max )
				{
					$this->offsetSet( ':location:site:elevation:min', $max );
					$this->offsetSet( ':location:site:elevation:max', $min );
					$tmp = $min;
					$min = $max;
					$max = $tmp;
				}
			
				//
				// Normalise range.
				//
				if( ($max - $min) < ($theMinElev * 2) )
				{
					$tmp = (int) floor( (($theMinElev * 2) - ($max - $min)) / 2 );
					$min -= $tmp;
					$max += $tmp;
				}
			
				//
				// Set range.
				//
				$range = array( $min, $max );
		
			} // Has elevation range.
		
			//
			// Handle distance range.
			//
			if( $this->offsetExists( 'fcu:unit:area' ) )
			{
				$dist = sqrt( $this->offsetGet( 'fcu:unit:area' ) * 10000 ) * 1.2;
				if( $dist < $theDefDist )
					$dist = $theDefDist;
			}
			elseif( $range !== NULL )
				$dist = $theDefDist;
		
			//
			// Get climate data.
			//
			$climate = static::GetClimateData( $this->mDictionary,
											   $this->offsetGet( kTAG_GEO_SHAPE ),
											   $range,
											   $dist );
		
			//
			// Set climate data.
			//
			if( count( $climate ) )
			{
				$this->offsetSet( ':environment', $climate );
			
				return TRUE;														// ==>
		
			} // Climate set.
		
		} // Had or created shape.
		
		return FALSE;																// ==>
	
	} // setClimateData.

		

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
								->collectStructureOffsets(
									'struct::domain:forest' ) );					// ==>
	
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
		// Check authority.
		//
		if( ! $this->offsetExists( kTAG_AUTHORITY ) )
			$this->offsetSet( kTAG_AUTHORITY, substr( $id, 0, 3 ) );
		
		//
		// Check identifier.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
			$this->offsetSet( kTAG_IDENTIFIER, substr( $id, 3 ) );
		
		//
		// Check version.
		//
		if( ! $this->offsetExists( kTAG_VERSION ) )
			$this->offsetSet( kTAG_VERSION,
							  $this->offsetGet( 'fcu:unit:data-collection' ) );
		
		//
		// Set taxon categories.
		//
		if( $this->offsetExists( 'fcu:population' ) )
		{
			//
			// Init local storage.
			//
			$tag_genus = $this->resolveOffset( ':taxon:genus', TRUE );
			$tag_species = $this->resolveOffset( ':taxon:species', TRUE );
			
			//
			// Iterate populations.
			//
			$populations = $this->offsetGet( 'fcu:population' );
			foreach( $populations as $key => $value )
			{
				//
				// Check genus.
				//
				if( array_key_exists( $tag_genus, $value ) )
				{
					//
					// Get categories.
					//
					$cats = ( array_key_exists( $tag_species, $value ) )
						  ? Term::ResolveTaxonGroup(
								$this->mDictionary,
								$value[ $tag_genus ],
								$value[ $tag_species ] )
						  : Term::ResolveTaxonGroup(
								$this->mDictionary,
								$value[ $tag_genus ] );
			
					//
					// Set categories.
					//
					if( count( $cats ) )
					{
						//
						// Update population.
						//
						foreach( $cats as $tag => $cat )
							$value[ $tag ] = $cat;
						
						//
						// Update populations.
						//
						$populations[ $key ] = $value;
					}
		
				} // Has genus.
			
			} // Iterating populations.
			
			//
			// Update populations.
			//
			$this->offsetSet( 'fcu:population', $populations );
		
		} // Has populations.
		
		//
		// Set shape.
		//
		$this->setObjectShapes( TRUE );
		
		//
		// Set climate data.
		//
		$this->setClimateData();
	
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
	
	} // preCommitPrepare.

		

/*=======================================================================================
 *																						*
 *									SHAPE UTILITIES										*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setObjectActualShape															*
	 *==================================================================================*/

	/**
	 * Set object actual shape
	 *
	 * In this class we use the latitude (<tt>:location:site:latitude</tt>) and longitude
	 * (<tt>:location:site:longitude</tt>) of the unit, if the shape was not already set
	 * as a polygon.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> if the shape was set or found.
	 */
	protected function setObjectActualShape()
	{
		//
		// Check shape.
		//
		if( ! $this->offsetExists( kTAG_GEO_SHAPE ) )
		{
			//
			// Check shape.
			//
			if( $this->offsetExists( ':location:site:latitude' )
			 && $this->offsetExists( ':location:site:longitude' ) )
				$this->offsetSet(
					kTAG_GEO_SHAPE,
					array( kTAG_TYPE => 'Point',
						   kTAG_GEOMETRY => array(
							(double) $this->offsetGet( ':location:site:longitude' ),
							(double) $this->offsetGet( ':location:site:latitude' ) ) ) );
			else
				return FALSE;														// ==>
		}
		
		return TRUE;																// ==>
	
	} // setObjectActualShape.

	 

} // class ForestUnit.


?>
