<?php

/**
 * CollectingSample.php
 *
 * This file contains the definition of the {@link CollectingSample} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\UnitObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;
use OntologyWrapper\Sample;

/*=======================================================================================
 *																						*
 *								CollectingSample.php									*
 *																						*
 *======================================================================================*/

/**
 * Collecting sample object
 *
 * This class is derived from the {@link Sample} class, it implements a collected germplasm
 * sample.
 *
 * The inherited attributes have the following function:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: By default the class sets the
 *		{@link kDOMAIN_SAMPLE_COLLECTED} constant.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The authority is set with the
 *		<tt>mcpd:COLLCODE</tt> tag.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The identifier is set with the
 *		<tt>:germplasm:identifier</tt>.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: This property is set with the
 *		<tt>:mission:identifier</tt> and <tt>:mission:collecting:identifier</tt> tags.
 *	<li><tt>{@link kTAG_VERSION}</tt>: This property is set with the collecting date.
 * </ul>
 *
 * The object can be considered initialised when it has all the above properties.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 05/06/2014
 */
class CollectingSample extends Sample
{
	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_SAMPLE_COLLECTED;

		

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
			if( $this->offsetExists( ':location:site:elevation' ) )
			{
				//
				// Set range.
				//
				$tmp = $this->offsetGet( ':location:site:elevation' );
				$range = array( $tmp - $theMinElev, $tmp + $theMinElev );
		
			} // Has elevation range.
		
			//
			// Set collecting site error.
			//
			if( $this->offsetExists( ':location:site:error' ) )
			{
				//
				// Get value.
				//
				$tmp = $this->offsetGet( ':location:site:error' );
			
				//
				// Handle value.
				//
				if( $tmp )
				{
					//
					// Handle error overflow.
					//
					if( $tmp > kCLIMATE_MAX_DIST )
						return FALSE;												// ==>
			
					//
					// Handle error underflow.
					//
					$dist = ( $tmp < $theDefDist )
						  ? $theDefDist
						  : $tmp;
				}
			}
		
			//
			// Enforce distance.
			//
			if( ($dist === NULL)
			 && ($range !== NULL) )
				$dist = $theDefDist;
		
			//
			// Get climate data.
			// Note that we use the point shape by default.
			//
			$climate = static::GetClimateData( $this->mDictionary,
											   $this->offsetGet( kTAG_GEO_SHAPE_DISP ),
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
		
		} // Has shapes.
		
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
	 * {@link collectStructureOffsets()} of the <tt>struct:cwr:in</tt> structure node (PID).
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							$this->mDictionary
								->collectStructureOffsets(
									'struct::domain:sample:collected' ) );			// ==>
	
	} // DefaultOffsets.

		

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
		// Check domain.
		//
		// Done by parent.
		
		//
		// Check authority.
		//
		if( ! $this->offsetExists( kTAG_AUTHORITY ) )
		{
			if( $this->offsetExists( 'mcpd:COLLCODE' ) )
				$this->offsetSet( kTAG_AUTHORITY,
								  $this->offsetGet( 'mcpd:COLLCODE' ) );
		}
		
		//
		// Check collection.
		//
		if( ! $this->offsetExists( kTAG_COLLECTION ) )
		{
			$tmp = Array();
			if( $this->offsetExists( ':mission:identifier' ) )
				$tmp[] = $this->offsetGet( ':mission:identifier' );
			if( $this->offsetExists( ':mission:collecting:identifier' ) )
				$tmp[] = $this->offsetGet( ':mission:collecting:identifier' );
			if( count( $tmp ) )
				$this->offsetSet( kTAG_COLLECTION,
								  implode( kTOKEN_INDEX_SEPARATOR, $tmp ) );
		}
		
		//
		// Check identifier.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
		{
			if( $this->offsetExists( ':germplasm:identifier' ) )
				$this->offsetSet( kTAG_IDENTIFIER,
								  $this->offsetGet( ':germplasm:identifier' ) );
		}
		
		//
		// Check version.
		//
		if( ! $this->offsetExists( kTAG_VERSION ) )
		{
			if( $this->offsetExists( 'mcpd:COLLDATE' ) )
				$this->offsetSet( kTAG_VERSION,
								  $this->offsetGet( 'mcpd:COLLDATE' ) );
		}
		
		//
		// Set taxon.
		//
		if( ! $this->offsetExists( ':taxon:epithet' ) )
		{
			//
			// Start with genus.
			//
			if( $this->offsetExists( ':taxon:genus' ) )
			{
				$taxon = Array();
				$taxon[] = $this->offsetGet( ':taxon:genus' );
				if( $this->offsetExists( ':taxon:species' ) )
					$taxon[] = $this->offsetGet( ':taxon:species' );
				if( $this->offsetExists( ':taxon:infraspecies' ) )
					$taxon[] = $this->offsetGet( ':taxon:infraspecies' );
				$taxon = implode( ' ', $taxon );
				$this->offsetSet( ':taxon:epithet', $taxon );
			
			} // Has genus.
		
		} // Taxon not yet set.
		
		//
		// Create shape.
		//
		$this->setObjectShapes();
		
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
	 * (<tt>:location:site:longitude</tt>) of the colleting site, and the coordinate error
	 * (<tt>:location:site:error</tt>) as the circle radius.
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
			// Check coordinates.
			//
			if( $this->offsetExists( ':location:site:latitude' )
			 && $this->offsetExists( ':location:site:longitude' ) )
			{
				//
				// Set circle.
				//
				if( $this->offsetExists( ':location:site:error' )
				 && ($this->offsetGet( ':location:site:error' ) > kCLIMATE_MIN_DIST) )
					$this->offsetSet(
						kTAG_GEO_SHAPE,
						array(
							kTAG_TYPE => 'Circle',
							kTAG_GEOMETRY => array(
							   (double) $this->offsetGet( ':location:site:longitude' ),
							   (double) $this->offsetGet( ':location:site:latitude' ) ),
						    kTAG_RADIUS => (int) $this->offsetGet( ':location:site:error' ) ) );
				
				//
				// Set point.
				//
				else
					$this->offsetSet(
						kTAG_GEO_SHAPE,
						array(
							kTAG_TYPE => 'Point',
						    kTAG_GEOMETRY => array(
							   (double) $this->offsetGet( ':location:site:longitude' ),
							   (double) $this->offsetGet( ':location:site:latitude' ) ) ) );
			}
			else
				return FALSE;														// ==>
		
		} // Shape not yet set.
		
		return TRUE;																// ==>
	
	} // setObjectActualShape.

	 

} // class CollectingSample.


?>
