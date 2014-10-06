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
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
	
	} // preCommitPrepare.

	 

} // class CollectingMission.


?>
