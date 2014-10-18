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
 *							PROTECTED POST-COMMIT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postInsert																		*
	 *==================================================================================*/

	/**
	 * Handle object after insert
	 *
	 * We overload this method to automatically add to the collecting mission the current
	 * sample.
	 *
	 * The method will fill a record with the current sample information and add it to the
	 * <tt>:mission:collecting:samples</tt> structure of the related collecting mission;
	 * this will only occur if the current sample is related to a collecting mission
	 * (<tt>:mission:collecting</tt>).
	 *
	 * The elements used are:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_STRUCT_LABEL}</tt>: The list element will be set with the
	 *		following items:
	 *	 <ul>
	 *		<li><tt>:germplasm:identifier</tt>: The germplasm identifier.
	 *		<li><tt>:taxon:epithet</tt>: The sample scientific name.
	 *	 </ul>
	 *	<li><tt>:germplasm:sample</tt>: The germplasm reference.
	 *	<li><tt>:germplasm:identifier</tt>: The germplasm identifier.
	 *	<li><tt>:taxon:epithet</tt>: The sample scientific name.
	 *	<li><tt>mcpd:COLLDATE</tt>: The collecting date.
	 *	<li><tt>mcpd:COLLNUMB</tt>: The collecting number.
	 *	<li><tt>mcpd:COLLSRC</tt>: The collecting source.
	 *	<li><tt>mcpd:SAMPSTAT</tt>: The sample biological status.
	 * </ul>
	 *
	 * @param array					$theOffsets			Tag offsets to be added.
	 * @param array					$theReferences		Object references to be incremented.
	 *
	 * @access protected
	 */
	protected function postInsert( $theOffsets, $theReferences )
	{
		//
		// Call parent method.
		//
		parent::postInsert( $theOffsets, $theReferences );
		
		//
		// Check if related to a collecting mission.
		//
		if( $this->offsetExists( ':mission:collecting' ) )
		{
			//
			// Instantiate collecting mission.
			//
			$mission = new CollectingMission( $this->mDictionary,
											  $this->offsetGet( ':mission:collecting' ),
											  TRUE );
			
			//
			// Get samples list.
			//
			$list = $mission->offsetGet( ':mission:collecting:samples' );
			if( ! is_array( $list ) )
				$list = Array();
			
			//
			// Add current object in list.
			//
			$list[] = $this->fillListElements();
			
			//
			// Update collecting mission.
			//
			$mission->offsetSet( ':mission:collecting:samples', $list );
			$mission->commit();
		
		} // Related to collecting mission.
	
	} // postInsert.

	 
	/*===================================================================================
	 *	postUpdate																		*
	 *==================================================================================*/

	/**
	 * Handle object after update
	 *
	 * We overload this method to update the related collecting mission in order to update
	 * its shape, this will be done in all cases to handle cases in which the sample
	 * information has changed.
	 *
	 * The method will fill a record with the current sample information and replace the
	 * relative element in the <tt>:mission:collecting:samples</tt> structure of the related
	 * collecting mission; this will only occur if the current sample is related to a
	 * collecting mission (<tt>:mission:collecting</tt>).
	 *
	 * The elements used are:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_STRUCT_LABEL}</tt>: The list element will be set with the
	 *		following items:
	 *	 <ul>
	 *		<li><tt>:germplasm:identifier</tt>: The germplasm identifier.
	 *		<li><tt>:taxon:epithet</tt>: The sample scientific name.
	 *	 </ul>
	 *	<li><tt>:germplasm:sample</tt>: The germplasm reference.
	 *	<li><tt>:germplasm:identifier</tt>: The germplasm identifier.
	 *	<li><tt>:taxon:epithet</tt>: The sample scientific name.
	 *	<li><tt>mcpd:COLLDATE</tt>: The collecting date.
	 *	<li><tt>mcpd:COLLNUMB</tt>: The collecting number.
	 *	<li><tt>mcpd:COLLSRC</tt>: The collecting source.
	 *	<li><tt>mcpd:SAMPSTAT</tt>: The sample biological status.
	 * </ul>
	 *
	 * @param array					$theOffsets			Tag offsets to be added.
	 * @param array					$theReferences		Object references to be incremented.
	 *
	 * @access protected
	 */
	protected function postUpdate( $theOffsets, $theReferences )
	{
		//
		// Call parent method.
		//
		parent::postUpdate( $theOffsets, $theReferences );
		
		//
		// Check if related to a collecting mission.
		//
		if( $this->offsetExists( ':mission:collecting' ) )
		{
			//
			// Instantiate collecting mission.
			//
			$mission = new CollectingMission( $this->mDictionary,
											  $this->offsetGet( ':mission:collecting' ),
											  TRUE );
			
			//
			// Get samples list.
			//
			$list = $mission->offsetGet( ':mission:collecting:samples' );
			if( ! is_array( $list ) )
				$list = Array();
			
			//
			// Locate sample.
			//
			$done = FALSE;
			$tag = $this->resolveOffset( ':germplasm:sample', TRUE );
			foreach( array_keys( $list ) as $key )
			{
				if( array_key_exists( $tag, $list[ $key ] ) )
				{
					if( $list[ $key ][ $tag ] == $this->offsetGet( kTAG_NID ) )
					{
						$list[ $key ] = $this->fillListElements();
						$done = TRUE;
						break;												// =>
					}
				}
			}
			
			//
			// Add current object in list.
			//
			if( ! $done )
				$list[] = $this->fillListElements();
			
			//
			// Update collecting mission.
			//
			$mission->offsetSet( ':mission:collecting:samples', $list );
			$mission->commit();
		
		} // Related to collecting mission.
	
	} // postUpdate.

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-DELETE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postDelete																		*
	 *==================================================================================*/

	/**
	 * Handle object after delete
	 *
	 * We overload this method to remove the current object from related objects that
	 * feature this object in lists.
	 *
	 * The method will first call the inherited method, then it will check whether the
	 * current object is related to a collecting mission, in that case it will remove it
	 * from the collecting mission's list of samples.
	 *
	 * @param array					$theOffsets			Tag offsets to be removed.
	 * @param array					$theReferences		Object references to be decremented.
	 *
	 * @access protected
	 */
	protected function postDelete( $theOffsets, $theReferences )
	{
		//
		// Call parent method.
		//
		parent::postDelete( $theOffsets, $theReferences );
		
		//
		// Check if related to a collecting mission.
		//
		if( $this->offsetExists( ':mission:collecting' ) )
		{
			//
			// Instantiate collecting mission.
			//
			$mission = new CollectingMission( $this->mDictionary,
											  $this->offsetGet( ':mission:collecting' ),
											  TRUE );
			
			//
			// Handle samples list.
			//
			$list = $mission->offsetGet( ':mission:collecting:samples' );
			if( is_array( $list ) )
			{
				//
				// Locate sample.
				//
				$done = FALSE;
				$tag = $this->resolveOffset( ':germplasm:sample', TRUE );
				foreach( array_keys( $list ) as $key )
				{
					if( array_key_exists( $tag, $list[ $key ] ) )
					{
						if( $list[ $key ][ $tag ] == $this->offsetGet( kTAG_NID ) )
						{
							unset( $list[ $key ] );
							$list = array_values( $list );
							$done = TRUE;
							break;											// =>
						}
					}
				}
			
				//
				// Update collecting mission.
				//
				if( $done )
				{
					//
					// Update list.
					//
					if( count( $list ) )
						$mission->offsetSet( ':mission:collecting:samples', $list );
					
					//
					// Delete list.
					//
					else
						$mission->offsetUnset( ':mission:collecting:samples' );
					
					//
					// Update object.
					//
					$mission->commit();
				}
			
			} // Has samples.
		
		} // Related to collecting mission.
	
	} // postDelete.

		

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

		

/*=======================================================================================
 *																						*
 *									REFERENTIAL UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	fillListElements																*
	 *==================================================================================*/

	/**
	 * Set list labels
	 *
	 * This method will return an array with the properties that the current
	 * object uses when referenced in a list of references from another object.
	 *
	 * The following elements will be set:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_STRUCT_LABEL}</tt>: The list element will be set with the
	 *		following items:
	 *	 <ul>
	 *		<li><tt>:germplasm:identifier</tt>: The germplasm identifier.
	 *		<li><tt>:taxon:epithet</tt>: The sample scientific name.
	 *	 </ul>
	 *	<li><tt>:germplasm:sample</tt>: The germplasm reference.
	 *	<li><tt>:germplasm:identifier</tt>: The germplasm identifier.
	 *	<li><tt>:taxon:epithet</tt>: The sample scientific name.
	 *	<li><tt>mcpd:COLLDATE</tt>: The collecting date.
	 *	<li><tt>mcpd:COLLNUMB</tt>: The collecting number.
	 *	<li><tt>mcpd:COLLSRC</tt>: The collecting source.
	 *	<li><tt>mcpd:SAMPSTAT</tt>: The sample biological status.
	 * </ul>
	 *
	 * @access protected
	 * @return array				The list of properties.
	 */
	protected function fillListElements()
	{
		//
		// Init local storage.
		//
		$element = Array();
		
		//
		// Set element label.
		//
		$tmp = Array();
		if( $this->offsetExists( ':germplasm:identifier' ) )
			$tmp[] = '('.$this->offsetGet( ':germplasm:identifier' ).')';
		elseif( $this->offsetExists( 'mcpd:COLLNUMB' ) )
			$tmp[] = '('.$this->offsetGet( 'mcpd:COLLNUMB' ).')';
		if( $this->offsetExists( ':taxon:epithet' ) )
			$tmp[] = $this->offsetGet( ':taxon:epithet' );
		$element[ kTAG_STRUCT_LABEL ] = ( count( $tmp ) )
									  ? implode( ' ', $tmp )
									  : 'unknown';
		
		//
		// Set germplasm reference.
		//
		$element[ $this->resolveOffset( ':germplasm:sample', TRUE ) ]
			= $this->offsetGet( kTAG_NID );
		
		//
		// Set germplasm identifier.
		//
		if( $this->offsetExists( ':germplasm:identifier' ) )
			$element[ $this->resolveOffset( ':germplasm:identifier', TRUE ) ]
				= $this->offsetGet( ':germplasm:identifier' );
		
		//
		// Set genus.
		//
		if( $this->offsetExists( ':taxon:genus' ) )
			$element[ $this->resolveOffset( ':taxon:genus', TRUE ) ]
				= $this->offsetGet( ':taxon:genus' );
		
		//
		// Set species name.
		//
		if( $this->offsetExists( ':taxon:species:name' ) )
			$element[ $this->resolveOffset( ':taxon:species:name', TRUE ) ]
				= $this->offsetGet( ':taxon:species:name' );
		
		//
		// Set taxon epithet.
		//
		if( $this->offsetExists( ':taxon:epithet' ) )
			$element[ $this->resolveOffset( ':taxon:epithet', TRUE ) ]
				= $this->offsetGet( ':taxon:epithet' );
		
		//
		// Set country.
		//
		if( $this->offsetExists( ':location:country' ) )
			$element[ $this->resolveOffset( ':location:country', TRUE ) ]
				= $this->offsetGet( ':location:country' );
		
		//
		// Set collecting date.
		//
		if( $this->offsetExists( 'mcpd:COLLDATE' ) )
			$element[ $this->resolveOffset( 'mcpd:COLLDATE', TRUE ) ]
				= $this->offsetGet( 'mcpd:COLLDATE' );
		
		//
		// Set collecting number.
		//
		if( $this->offsetExists( 'mcpd:COLLNUMB' ) )
			$element[ $this->resolveOffset( 'mcpd:COLLNUMB', TRUE ) ]
				= $this->offsetGet( 'mcpd:COLLNUMB' );
		
		//
		// Set collecting source.
		//
		if( $this->offsetExists( 'mcpd:COLLSRC' ) )
			$element[ $this->resolveOffset( 'mcpd:COLLSRC', TRUE ) ]
				= $this->offsetGet( 'mcpd:COLLSRC' );
		
		//
		// Set biological status.
		//
		if( $this->offsetExists( 'mcpd:SAMPSTAT' ) )
			$element[ $this->resolveOffset( 'mcpd:SAMPSTAT', TRUE ) ]
				= $this->offsetGet( 'mcpd:SAMPSTAT' );
		
		return $element;															// ==>
	
	} // fillListElements.

	 

} // class CollectingSample.


?>
