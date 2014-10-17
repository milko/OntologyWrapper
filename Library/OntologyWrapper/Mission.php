<?php

/**
 * Mission.php
 *
 * This file contains the definition of the {@link Mission} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\UnitObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									Mission.php											*
 *																						*
 *======================================================================================*/

/**
 * Mission object
 *
 * This class is derived from the {@link UnitObject} class, it implements a mission object
 * which contains summary data regarding a series of collecting or other types of mission.
 *
 * The inherited attributes have the following function:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: By default the class sets the
 *		{@link kDOMAIN_MISSION} constant.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The optional authority is set with an institute
 *		code.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The identifier is set with the
 *		<tt>:mission:identifier</tt> tag.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: This property is not managed.
 *	<li><tt>{@link kTAG_VERSION}</tt>: This property is set with mission start date.
 * </ul>
 *
 * The object can be considered initialised when it has at least the identifier.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 05/06/2014
 */
class Mission extends UnitObject
{
	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_MISSION;

		

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
	 * In this class we return the unit {@link kTAG_IDENTIFIER} and the
	 * {@link kTAG_COLLECTION} separated by a slash, concatenated to the domain name.
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
		$name = Array();
		$domain = parent::getName( $theLanguage );
		
		//
		// Set authority.
		//
		if( $this->offsetExists( kTAG_AUTHORITY ) )
			$name[] = $this->offsetGet( kTAG_AUTHORITY );
		
		//
		// Set collection.
		//
		if( $this->offsetExists( kTAG_COLLECTION ) )
			$name[] = $this->offsetGet( kTAG_COLLECTION );
		
		//
		// Set identifier.
		//
		if( $this->offsetExists( kTAG_IDENTIFIER ) )
			$name[] = $this->offsetGet( kTAG_IDENTIFIER );
		
		//
		// Set version.
		//
		if( $this->offsetExists( kTAG_VERSION ) )
			$name[] = $this->offsetGet( kTAG_VERSION );
		
		return ( $domain !== NULL )
			 ? ($domain.' '.implode( ':', $name ))									// ==>
			 : implode( ':', $name );												// ==>
	
	} // getName.

		

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
									'struct::domain:mission' ) );					// ==>
	
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
		// Check identifier.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
			$this->offsetSet( kTAG_IDENTIFIER, $this->offsetGet( ':mission:identifier' ) );
		
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
 *									SHAPE UTILITIES										*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setObjectShapes																	*
	 *==================================================================================*/

	/**
	 * Set object shapes
	 *
	 * We overload this method to intercept polygons and multi-points: in that case we do
	 * not call the default {@link setObjectDisplayShape()} method to compute the display
	 * shape.
	 *
	 * @param boolean				$doUpdate			TRUE means force update.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> if the shapes were set or found.
	 */
	protected function setObjectShapes( $doUpdate = FALSE )
	{
		//
		// Reset shapes.
		//
		if( $doUpdate )
			$this->resetObjectShapes();
		
		//
		// Check object shape.
		//
		if( ! $this->offsetExists( kTAG_GEO_SHAPE ) )
		{
			//
			// Set actual shape.
			//
			if( $this->setObjectActualShape() )
			{
				//
				// Intercept line string and multi point.
				//
				$shape = $this->offsetGet( kTAG_GEO_SHAPE );
				switch( $shape[ kTAG_TYPE ] )
				{
					case 'Point':
					case 'Polygon':
					case 'MultiPoint':
						$this->offsetSet( kTAG_GEO_SHAPE_DISP, $shape );
						break;
					
					default:
						$this->setObjectDisplayShape();
						break;
				}
				
				return TRUE;														// ==>
			
			} // Actual shape was set.
		
		} // Shape not set.
		
		return FALSE;																// ==>
	
	} // setObjectShapes.

	 
	/*===================================================================================
	 *	setObjectActualShape															*
	 *==================================================================================*/

	/**
	 * Set object actual shape
	 *
	 * In this class we iterate the collecting missions and create a polygon from all the
	 * points of the collecting missions.
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
			$missions = $this->offsetGet( ':collecting:missions' );
			if( is_array( $missions ) )
			{
				//
				// Init local storage.
				//
				$tag_coll = $this->resolveOffset( ':mission:collecting', TRUE );
				$collection
					= static::ResolveCollection(
						static::ResolveDatabase( $this->mDictionary, TRUE ) );

				
				//
				// Collect collecting mission identifiers.
				//
				$identifiers = Array();
				foreach( $missions as $mission )
				{
					if( array_key_exists( $tag_coll, $mission ) )
						$identifiers[] = $mission[ $tag_coll ];
				}
				
				//
				// Get collecting mission shapes.
				//
				$fields = \ArrayObject( array( kTAG_GEO_SHAPE => TRUE ) );
				$query = array( kTAG_NID => ( ( count( $identifiers ) > 1 )
											? array( '$in' => $identifiers )
											: $identifiers[ 0 ] ),
								kTAG_OBJECT_TAGS => kTAG_GEO_SHAPE );
				$missions = $collection->connection()->find( $query, $fields );
				
				//
				// Check samples.
				//
				if( $missions->count() )
				{
					//
					// Load coordinates.
					//
					$geometry = Array();
					foreach( $missions as $mission )
					{
						//
						// Parse by shape type.
						//
						switch( $mission[ kTAG_GEO_SHAPE ][ kTAG_TYPE ] )
						{
							case 'Point':
								$geometry[] = $mission[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ];
								break;
							
							case 'LineString':
								foreach( $mission[ kTAG_GEO_SHAPE ][ kTAG_GEOMETRY ] as $c )
									$geometry[] = $c;
								break;
						}
					}
					
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
				
				} // Found collecting missions.
			
			} // Has collecting missions.
			
			return FALSE;															// ==>
		
		} // Shape not yet set.
		
		return TRUE;																// ==>
	
	} // setObjectActualShape.

	 

} // class Mission.


?>
