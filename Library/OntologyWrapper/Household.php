<?php

/**
 * Household.php
 *
 * This file contains the definition of the {@link Household} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\UnitObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									Household.php										*
 *																						*
 *======================================================================================*/

/**
 * Household object
 *
 * This class is derived from the {@link UnitObject} class, it implements a household
 * agro bio-diversity assessment object.
 *
 * The inherited attributes have the following function:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: By default the class sets the
 *		{@link kDOMAIN_HH_ASSESSMENT} constant.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The authority is set with the FAO code of the
 *		institute that conducted the assessment, if relevant or available.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The identifier is set with the
 *		<tt>abdh:ID_HOUSEHOLD</tt> tag.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: This property is filled with the
 *		<tt>abdh:STATE</tt>, <tt>abdh:DISTRICT</tt>, <tt>abdh:BLOCKS</tt> and
 *		<tt>abdh:VILLAGE</tt> tags, separated by commas.
 *	<li><tt>{@link kTAG_VERSION}</tt>: This property is filled with the reference year.
 * </ul>
 *
 * All the above properties, except the collection and version, are used to compute the
 * object's native identifier.
 *
 * The object can be considered initialised when it has at least the domain and the
 * identifier set.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 25/08/2014
 */
class Household extends UnitObject
{
	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_HH_ASSESSMENT;

		

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
	 * In this class we return the domain name with a separator space, the
	 * <tt>{@link kTAG_IDENTIFIER}</tt> and <tt>{@link kTAG_AUTHORITY}</tt> tags separated
	 * by a slash, the <tt>abdh:STATE</tt>, <tt>abdh:DISTRICT</tt>, <tt>abdh:BLOCKS</tt> and
	 * <tt>abdh:VILLAGE</tt> tags, separated by commas and the <tt>{@link kTAG_VERSION}</tt>
	 * separated by a space.
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
		
		//
		// Set identifier and authority.
		//
		$tmp = Array();
		if( $this->offsetExists( kTAG_IDENTIFIER ) )
			$tmp[] = $this->offsetGet( kTAG_IDENTIFIER );
		if( $this->offsetExists( kTAG_AUTHORITY ) )
			$tmp[] = $this->offsetGet( kTAG_AUTHORITY );
		if( count( $tmp ) )
			$name .= (' '.implode( '/', $tmp ));
		
		//
		// Set collection.
		//
		if( $this->offsetExists( kTAG_COLLECTION ) )
			$name .= (' '.
					  implode( ', ',
					  		   explode( ',',
					  		   			$this->offsetGet( kTAG_COLLECTION ) ) ));
		
		//
		// Set version.
		//
		if( $this->offsetExists( kTAG_VERSION ) )
			$name .= (' '.$this->offsetGet( kTAG_VERSION ));
		
		return $name;																// ==>
	
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
	 * {@link collectStructureOffsets()} of the <tt>struct:mcpd</tt> structure node (PID).
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							$this->mDictionary
								->collectStructureOffsets( 'struct:abdh' ) );		// ==>
	
	} // DefaultOffsets.

		

/*=======================================================================================
 *																						*
 *							PROTECTED OFFSET STATUS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	lockedOffsets																	*
	 *==================================================================================*/

	/**
	 * Return list of locked offsets
	 *
	 * In this class we return the <tt>abdh:ID_HOUSEHOLD</tt>, <tt>abdh:INSTITUTE</tt>,
	 * <tt>abdh:REF-YEAR</tt>, <tt>abdh:STATE</tt>, <tt>abdh:DISTRICT</tt>,
	 * <tt>abdh:BLOCKS</tt> and <tt>abdh:VILLAGE</tt> tags.
	 * offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_DOMAIN kTAG_AUTHORITY kTAG_COLLECTION kTAG_IDENTIFIER kTAG_VERSION
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
							array( 'abdh:ID_HOUSEHOLD', 'abdh:INSTITUTE', 'abdh:REF-YEAR',
								   'abdh:STATE', 'abdh:DISTRICT', 'abdh:BLOCKS',
								   'abdh:VILLAGE' ) );								// ==>
	
	} // lockedOffsets.

		

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
	 * In this class we overload this method to set the default domain, identifier,
	 * collection and version, if not yet set.
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
			$this->offsetSet( kTAG_DOMAIN, static::kDEFAULT_DOMAIN );
		
		//
		// Check identifier.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
			$this->offsetSet( kTAG_IDENTIFIER, $this->offsetGet( 'abdh:ID_HOUSEHOLD' ) );
		
		//
		// Check authority.
		//
		if( ! $this->offsetExists( kTAG_AUTHORITY ) )
			$this->offsetSet( kTAG_AUTHORITY, $this->offsetGet( 'abdh:INSTITUTE' ) );
		
		//
		// Check collection.
		//
		if( ! $this->offsetExists( kTAG_COLLECTION ) )
		{
			$tmp = Array();
			if( $this->offsetExists( 'abdh:STATE' ) )
				$tmp[] = $this->offsetGet( 'abdh:STATE' );
			if( $this->offsetExists( 'abdh:DISTRICT' ) )
				$tmp[] = $this->offsetGet( 'abdh:DISTRICT' );
			if( $this->offsetExists( 'abdh:BLOCKS' ) )
				$tmp[] = $this->offsetGet( 'abdh:BLOCKS' );
			if( $this->offsetExists( 'abdh:VILLAGE' ) )
				$tmp[] = $this->offsetGet( 'abdh:VILLAGE' );
			if( count( $tmp ) )
				$this->offsetSet( kTAG_COLLECTION, implode( ',', $tmp ) );
		}
		
		//
		// Check version.
		//
		if( ! $this->offsetExists( kTAG_VERSION ) )
		{
			if( $this->offsetExists( 'abdh:REF-YEAR' ) )
				$this->offsetSet( kTAG_VERSION, $this->offsetGet( 'abdh:REF-YEAR' ) );
		}
		
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
	
	} // preCommitPrepare.

	 

} // class Household.


?>
