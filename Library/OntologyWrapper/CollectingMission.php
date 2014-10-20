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
 *									REFERENTIAL UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	attributesList																	*
	 *==================================================================================*/

	/**
	 * Return object list attributes
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
	protected function attributesList()
	{
		//
		// Init local storage.
		//
		$element = Array();
		$country = $admin = $region = NULL;
		
		//
		// Resolve country.
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
		
		//
		// Resolve administrative unit.
		//
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
		
		//
		// Resolve region.
		//
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
		// Set region.
		//
		if( $region !== NULL )
			$element[ $this->resolveOffset( ':location:region', TRUE ) ]
				= $this->offsetGet( ':location:region' );
		
		//
		// Set admin.
		//
		if( $admin !== NULL )
			$element[ $this->resolveOffset( ':location:admin', TRUE ) ]
				= $this->offsetGet( ':location:admin' );
		
		//
		// Set country.
		//
		if( $country !== NULL )
			$element[ $this->resolveOffset( ':location:country', TRUE ) ]
				= $this->offsetGet( ':location:country' );
		
		return $element;															// ==>
	
	} // attributesList.

		

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
 *						PROTECTED OBJECT REFERENCING INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateManyToOne																	*
	 *==================================================================================*/

	/**
	 * Update many to one relationships
	 *
	 * In this class we overload this method to update the mission, <tt>:mission</tt>,
	 * by updating it passing the {@link kFLAG_OPT_REL_MANY} flag to the operation.
	 *
	 * @param bitfield				$theOptions			Operation options.
	 *
	 * @access protected
	 */
	protected function updateManyToOne( $theOptions )
	{
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
			// Update mission.
			//
			$mission->commit( NULL, kFLAG_OPT_REL_MANY );
	
		} // Related to mission.
	
	} // updateManyToOne.

	 
	/*===================================================================================
	 *	updateOneToMany																	*
	 *==================================================================================*/

	/**
	 * Update one to many relationships
	 *
	 * In this class we overload the method to select all collected samples related to
	 * the current object.
	 *
	 * @param bitfield				$theOptions			Operation options.
	 *
	 * @access protected
	 */
	protected function updateOneToMany( $theOptions )
	{
		//
		// Init local storage.
		//
		$list = Array();
		$this->offsetUnset( ':mission:collecting:samples' );
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $this->mDictionary, TRUE ) );
		
		//
		// Build query.
		//
		$query = array( kTAG_DOMAIN
							=> kDOMAIN_SAMPLE_COLLECTED,
						$this->resolveOffset( ':mission:collecting' )
							=> $this->offsetGet( kTAG_NID ) );
		
		//
		// Load collected samples.
		//
		$rs = $collection->matchAll( $query, kQUERY_OBJECT );
		if( $rs->count() )
		{
			//
			// Iterate collected samples.
			//
			$rs->sort( array( $this->resolveOffset( 'mcpd:COLLDATE', TRUE ) => 1 ) );
			foreach( $rs as $record )
			{
				//
				// Set collected samples list.
				//
				$element = $record->attributesList();
				if( count( $element ) )
					$list[] = $element;
				
			} // Iterating 
			
			//
			// Set collected samples list.
			//
			if( count( $list ) )
				$this->offsetSet( ':mission:collecting:samples', $list );
		
		} // Has collecting missions.
	
	} // updateOneToMany.

		

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
			// Init local storage.
			//
			$coordinates = Array();
		
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $this->mDictionary, TRUE ) );
		
			//
			// Build query.
			//
			$query
				= array( '$and' => array(
					array( kTAG_DOMAIN => kDOMAIN_SAMPLE_COLLECTED ),
					array( $this->resolveOffset( ':mission:collecting' )
							=> $this->offsetGet( kTAG_NID ) ),
					array( kTAG_OBJECT_TAGS
							=> (int) $this->resolveOffset( ':location:site:latitude' ) ),
					array( kTAG_OBJECT_TAGS
							=> (int) $this->resolveOffset( ':location:site:longitude' ) ) ) );
		
			//
			// Load collected samples.
			//
			$rs = $collection->matchAll( $query, kQUERY_OBJECT );
			if( $rs->count() )
			{
				//
				// Iterate collected samples.
				//
				$rs->sort( array( $this->resolveOffset( 'mcpd:COLLDATE', TRUE ) => 1 ) );
				foreach( $rs as $record )
				{
					//
					// Init local storage.
					//
					$index = count( $coordinates );
					$lat = $record->offsetGet( ':location:site:latitude' );
					$lon = $record->offsetGet( ':location:site:longitude' );
					
					//
					// Check coordinates.
					//
					if( ($lat !== NULL )
					 && ($lon !== NULL ) )
						$coordinates[ md5( array( $lon, $lat ), TRUE ) ]
							= array( $lon, $lat );
				}
				
				//
				// Normalise coordinates.
				//
				$coordinates = array_values( $coordinates );
			
				//
				// Copy sample shape.
				//
				if( count( $coordinates ) == 1 )
					$this->offsetSet(
						kTAG_GEO_SHAPE,
						array( kTAG_TYPE => 'Point',
							   kTAG_GEOMETRY => $coordinates[ 0 ] ) );
			
				//
				// Set multipoint.
				//
				elseif( count( $coordinates ) == 2 )
					$this->offsetSet(
						kTAG_GEO_SHAPE,
						array( kTAG_TYPE => 'MultiPoint',
							   kTAG_GEOMETRY => $coordinates ) );
			
				//
				// Set line string.
				//
				elseif( count( $coordinates ) > 2 )
					$this->offsetSet(
						kTAG_GEO_SHAPE,
						array( kTAG_TYPE => 'LineString',
							   kTAG_GEOMETRY =>  $coordinates ) );
				
				//
				// No points.
				//
				else
					return FALSE;													// ==>
					
				return TRUE;														// ==>
		
			} // Has collecting missions.
			
			return FALSE;															// ==>
		
		} // Shape not yet set.
		
		return TRUE;																// ==>
	
	} // setObjectActualShape.

	 

} // class CollectingMission.


?>
