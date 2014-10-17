<?php

/**
 * CollectingMission.php
 *
 * This file contains the definition of the {@link CollectingMission} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\UnitObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;
use OntologyWrapper\Mission;

/*=======================================================================================
 *																						*
 *								CollectingMission.php									*
 *																						*
 *======================================================================================*/

/**
 * Collecting mission object
 *
 * This class is derived from the {@link UnitObject} class, it implements a collecting
 * mission which contains summary data regarding a series of collecting events.
 *
 * The inherited attributes have the following function:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: By default the class sets the
 *		{@link kDOMAIN_COLLECTING_MISSION} constant.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The optional authority is set with an institute
 *		code.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The identifier is set with the
 *		<tt>:mission:collecting:identifier</tt>.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: This property is set with the
 *		<tt>:mission:identifier</tt> tag.
 *	<li><tt>{@link kTAG_VERSION}</tt>: This property is not handled.
 * </ul>
 *
 * The object can be considered initialised when it has all the above properties.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 05/06/2014
 */
class CollectingMission extends Mission
{
	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_COLLECTING_MISSION;

		

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
									'struct::domain:mission:collecting' ) );		// ==>
	
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
		if( ! $this->offsetExists( kTAG_DOMAIN ) )
			$this->offsetSet( kTAG_DOMAIN,
							  static::kDEFAULT_DOMAIN );
		
		//
		// Check collection.
		//
		if( (! $this->offsetExists( kTAG_COLLECTION ))
		 || ($this->offsetGet( kTAG_COLLECTION )
		 	!= $this->offsetGet( ':mission:identifier' )) )
			$this->offsetSet( kTAG_COLLECTION, $this->offsetGet( ':mission:identifier' ) );
		
		//
		// Check identifier.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
			$this->offsetSet( kTAG_IDENTIFIER,
							  $this->offsetGet( ':mission:collecting:identifier' ) );
		
		//
		// Create shape.
		//
		$this->setObjectShapes( TRUE );
		
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
	 * We overload this method to automatically add to the mission the current collecting
	 * mission.
	 *
	 * The method will fill a record with the current collecting mission information and add
	 * it to the <tt>:collecting:missions</tt> structure of the related mission; this will
	 * only occur if the current collecting mission is related to a mission
	 * (<tt>†</tt>).
	 *
	 * The elements used are:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_STRUCT_LABEL}</tt>: The list element will be set with the
	 *		following items:
	 *	 <ul>
	 *		<li><tt>:mission:collecting:identifier</tt>: The collecting mission identifier.
	 *		<li><tt>:location:country</tt>: The country,
	 *		<li><tt>:location:admin</tt>: or the administrative unit,
	 *		<li><tt>:location:region</tt>: or the region.
	 *	 </ul>
	 *	<li><tt>:mission:collecting</tt>: The collecting mission reference.
	 *	<li><tt>:mission:collecting:identifier</tt>: The collecting mission identifier.
	 *	<li><tt>:mission:collecting:start</tt>: The start date.
	 *	<li><tt>:mission:collecting:end</tt>: The end date.
	 *	<li><tt>:location:country</tt>: The country.
	 *	<li><tt>:location:admin</tt>: The administrative unit.
	 *	<li><tt>:location:region</tt>: The region.
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
		// Check if related to a mission.
		//
		if( $this->offsetExists( ':mission' ) )
		{
			//
			// Instantiate mission.
			//
			$mission = new Mission( $this->mDictionary,
									$this->offsetGet( ':mission' ),
									TRUE );
			
			//
			// Load current collecting mission in mission.
			//
			$list = $mission->offsetGet( ':collecting:missions' );
			if( ! is_array( $list ) )
				$list = Array();
			$list[] = $this->fillListElements();
			
			//
			// Update mission.
			//
			if( count( $list ) )
			{
				//
				// Update sub-structure.
				//
				$mission->offsetSet( ':collecting:missions', $list );
				
				//
				// Update object.
				//
				$mission->commit();
			}
		
		} // Related to mission.
	
	} // postInsert.

	 
	/*===================================================================================
	 *	postUpdate																		*
	 *==================================================================================*/

	/**
	 * Handle object after update
	 *
	 * We overload this method to update the related mission in order to update
	 * its shape, this will be done in all cases to handle cases in which the collecting
	 * information has changed.
	 *
	 * The method will fill a record with the current collecting information and replace the
	 * relative element in the <tt>:collecting:missions</tt> structure of the related
	 * collecting mission; this will only occur if the current collecting is related to a
	 * mission (<tt>:mission</tt>).
	 *
	 * The elements used are:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_STRUCT_LABEL}</tt>: The list element will be set with the
	 *		following items:
	 *	 <ul>
	 *		<li><tt>:mission:collecting:identifier</tt>: The collecting mission identifier.
	 *		<li><tt>:location:country</tt>: The country,
	 *		<li><tt>:location:admin</tt>: or the administrative unit,
	 *		<li><tt>:location:region</tt>: or the region.
	 *	 </ul>
	 *	<li><tt>:mission:collecting</tt>: The collecting mission reference.
	 *	<li><tt>:mission:collecting:identifier</tt>: The collecting mission identifier.
	 *	<li><tt>:mission:collecting:start</tt>: The start date.
	 *	<li><tt>:mission:collecting:end</tt>: The end date.
	 *	<li><tt>:location:country</tt>: The country.
	 *	<li><tt>:location:admin</tt>: The administrative unit.
	 *	<li><tt>:location:region</tt>: The region.
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
		// Check if related to a mission.
		//
		if( $this->offsetExists( ':mission' ) )
		{
			//
			// Instantiate mission.
			//
			$mission = new Mission( $this->mDictionary,
									$this->offsetGet( ':mission' ),
									TRUE );
			
			//
			// Get collecting missions list.
			//
			$list = $mission->offsetGet( ':collecting:missions' );
			if( ! is_array( $list ) )
				$list = Array();
			
			//
			// Locate collecting mission.
			//
			$done = FALSE;
			$tag = $this->resolveOffset( ':mission:collecting', TRUE );
			foreach( array_keys( $list ) as $key )
			{
				//
				// Match collecting mission.
				//
				if( array_key_exists( $tag, $list[ $key ] )
				 && ($list[ $key ][ $tag ] == $this->offsetGet( kTAG_NID )) )
				{
					$list[ $key ] = $this->fillListElements();
					$done = TRUE;
					break;													// =>
				}
			}
			
			//
			// Add current object in list.
			//
			if( ! $done )
				$list[] = $this->fillListElements();
			
			//
			// Update mission.
			//
			$mission->offsetSet( ':collecting:missions', $list );
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
	 * current object is related to a mission, in that case it will remove it.
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
		// Check if related to a mission.
		//
		if( $this->offsetExists( ':mission' ) )
		{
			//
			// Instantiate mission.
			//
			$mission = new Mission( $this->mDictionary,
									$this->offsetGet( ':mission' ),
									TRUE );
			
			//
			// Handle collecting missions list.
			//
			$list = $mission->offsetGet( ':collecting:missions' );
			if( is_array( $list ) )
			{
				//
				// Locate collecting mission.
				//
				$done = FALSE;
				$tag = $this->resolveOffset( ':mission:collecting', TRUE );
				foreach( array_keys( $list ) as $key )
				{
					//
					// Match collecting mission.
					//
					if( array_key_exists( $tag, $list[ $key ] )
					 && ($list[ $key ][ $tag ] == $this->offsetGet( kTAG_NID )) )
					{
						unset( $list[ $key ] );
						$list = array_values( $list );
						$done = TRUE;
						break;												// =>
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
						$mission->offsetSet( ':collecting:missions', $list );
					
					//
					// Delete list.
					//
					else
						$mission->offsetUnset( ':collecting:missions' );
					
					//
					// Update object.
					//
					$mission->commit();
				}
			
			} // Has collecting missions.
		
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
	 * In this class we iterate the collecting mission samples and create a line string
	 * with the coordinates of the samples ordered by date.
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
			// Get samples.
			//
			$samples = $this->offsetGet( ':mission:collecting:samples' );
			if( is_array( $samples ) )
			{
				//
				// Init local storage.
				//
				$tag_date = $this->resolveOffset( 'mcpd:COLLDATE', TRUE );
				$tag_sample = $this->resolveOffset( ':germplasm:sample', TRUE );
				$tag_lat = $this->resolveOffset( ':location:site:latitude', TRUE );
				$tag_lon = $this->resolveOffset( ':location:site:longitude', TRUE );
				$collection
					= static::ResolveCollection(
						static::ResolveDatabase( $this->mDictionary, TRUE ) );
				
				//
				// Collect sample identifiers.
				//
				$identifiers = Array();
				foreach( $samples as $sample )
				{
					if( array_key_exists( $tag_sample, $sample ) )
						$identifiers[] = $sample[ $tag_sample ];
				}
				
				//
				// Get samples.
				//
				$fields = new \ArrayObject( array( $tag_date => TRUE,
												   $tag_lat => TRUE,
												   $tag_lon => TRUE ) );
				$query
					= array(
						'$and' => array(
							array( kTAG_NID =>  ( ( count( $identifiers ) > 1 )
												? array( '$in' => $identifiers )
												: $identifiers[ 0 ] ) ),
							array( kTAG_OBJECT_TAGS => $tag_lat ),
							array( kTAG_OBJECT_TAGS => $tag_lon ) ) );
				$samples
					= $collection->connection()->find( $query, $fields );
				
				//
				// Check samples.
				//
				if( $samples->count() )
				{
					//
					// Sort and load coordinates.
					//
					$samples->sort( array( $tag_date => 1 ) );
					$geometry = Array();
					foreach( $samples as $sample )
						$geometry[] = array( $sample[ $tag_lon ], $sample[ $tag_lat ] );
					
					//
					// Set point.
					//
					if( count( $geometry ) == 1 )
						$this->offsetSet(
							kTAG_GEO_SHAPE,
							array( kTAG_TYPE => 'Point',
								   kTAG_GEOMETRY => $geometry[ 0 ] ) );
					
					//
					// Set multipoint.
					//
					elseif( count( $geometry ) == 2 )
						$this->offsetSet(
							kTAG_GEO_SHAPE,
							array( kTAG_TYPE => 'MultiPoint',
								   kTAG_GEOMETRY => $geometry ) );
					
					//
					// Set polygon.
					//
					elseif( count( $geometry ) > 2 )
					{
						$geometry[] = $geometry[ 0 ];
						$this->offsetSet(
							kTAG_GEO_SHAPE,
							array( kTAG_TYPE => 'Polygon',
								   kTAG_GEOMETRY => array( Polygon( $geometry ) ) ) );
					}
					
					//
					// No point.
					//
					else
						return FALSE;												// ==>
					
					return TRUE;													// ==>
				
				} // Found samples.
			
			} // Has samples.
			
			return FALSE;															// ==>
		
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
	 *		<li><tt>:mission:collecting:identifier</tt>: The collecting mission identifier.
	 *		<li><tt>:location:country</tt>: The country,
	 *		<li><tt>:location:admin</tt>: or the administrative unit,
	 *		<li><tt>:location:region</tt>: or the region.
	 *	 </ul>
	 *	<li><tt>:mission:collecting</tt>: The collecting mission reference.
	 *	<li><tt>:mission:collecting:identifier</tt>: The collecting mission identifier.
	 *	<li><tt>:mission:collecting:start</tt>: The start date.
	 *	<li><tt>:mission:collecting:end</tt>: The end date.
	 *	<li><tt>:location:country</tt>: The country.
	 *	<li><tt>:location:admin</tt>: The administrative unit.
	 *	<li><tt>:location:region</tt>: The region.
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
		$country = $admin = $region = NULL;
		
		//
		// Load locations.
		//
		if( $this->offsetExists( ':location:country' ) )
		{
			$object
				= new Term( $this->mDictionary,
							$this->offsetGet( ':location:country' ),
							TRUE );
			$country
				= static::SelectLanguageString( $object[ kTAG_LABEL ],
												kSTANDARDS_LANGUAGE );
		}
		if( $this->offsetExists( ':location:admin' ) )
		{
			$object
				= new Term( $this->mDictionary,
							$this->offsetGet( ':location:admin' ),
							TRUE );
			$admin
				= static::SelectLanguageString( $object[ kTAG_LABEL ],
												kSTANDARDS_LANGUAGE );
		}
		if( $this->offsetExists( ':location:region' ) )
			$region = $this->offsetGet( ':location:region' );
		
		//
		// Set element label.
		//
		$tmp = Array();
		if( $this->offsetExists( ':mission:collecting:identifier' ) )
			$tmp[] = '('.$this->offsetGet( ':mission:collecting:identifier' ).')';
		else
			$tmp[] = '(unknown)';
		if( $country !== NULL )
			$tmp[] = $country;
		elseif( $admin !== NULL )
			$tmp[] = $admin;
		elseif( $region !== NULL )
			$tmp[] = $region;
		$element[ kTAG_STRUCT_LABEL ] = ( count( $tmp ) )
									  ? implode( ' ', $tmp )
									  : 'unknown';
		
		//
		// Set collecting mission reference.
		//
		$element[ $this->resolveOffset( ':mission:collecting', TRUE ) ]
			= $this->offsetGet( kTAG_NID );
		
		//
		// Set collecting mission identifier.
		//
		if( $this->offsetExists( ':mission:collecting:identifier' ) )
			$element[ $this->resolveOffset( ':mission:collecting:identifier', TRUE ) ]
				= $this->offsetGet( ':mission:collecting:identifier' );
		
		//
		// Set collecting mission start.
		//
		if( $this->offsetExists( ':mission:collecting:start' ) )
			$element[ $this->resolveOffset( ':mission:collecting:start', TRUE ) ]
				= $this->offsetGet( ':mission:collecting:start' );
		
		//
		// Set collecting mission end.
		//
		if( $this->offsetExists( ':mission:collecting:end' ) )
			$element[ $this->resolveOffset( ':mission:collecting:end', TRUE ) ]
				= $this->offsetGet( ':mission:collecting:end' );
		
		//
		// Set country.
		//
		if( $country !== NULL )
			$element[ $this->resolveOffset( ':location:country', TRUE ) ]
				= $this->offsetGet( ':location:country' );
		
		//
		// Set admin.
		//
		if( $admin !== NULL )
			$element[ $this->resolveOffset( ':location:admin', TRUE ) ]
				= $this->offsetGet( ':location:admin' );
		
		//
		// Set region.
		//
		if( $admin !== NULL )
			$element[ $this->resolveOffset( ':location:region', TRUE ) ]
				= $this->offsetGet( ':location:region' );
		
		return $element;															// ==>
	
	} // fillListElements.

	 

} // class CollectingMission.


?>
