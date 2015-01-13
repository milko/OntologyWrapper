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
	 *	updateOneToMany																	*
	 *==================================================================================*/

	/**
	 * Update one to many relationships
	 *
	 * In this class we overload the method to select all collecting missions related to
	 * the current object.
	 *
	 * @param bitfield				$theOptions			Operation options.
	 *
	 * @access protected
	 */
	protected function updateOneToMany( $theOptions )
	{
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
							=> kDOMAIN_COLLECTING_MISSION,
						$this->resolveOffset( ':mission' )
							=> $this->offsetGet( kTAG_NID ) );
		
		//
		// Load collecting missions.
		//
		$list = Array();
		$rs = $collection->matchAll( $query, kQUERY_OBJECT );
		foreach( $rs as $record )
		{
			//
			// Set collecting mission.
			//
			$element = $record->attributesList();
			if( count( $element ) )
				$list[] = $element;
			
		} // Iterating collecting mission.
		
		//
		// Set list.
		//
		if( count( $list ) )
			$this->offsetSet( ':collecting:missions', $list );
		
		//
		// Delete collecting missions.
		//
		else
			$this->offsetUnset( ':collecting:missions' );
	
	} // updateOneToMany.

	 

} // class Mission.


?>
