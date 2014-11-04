<?php

/**
 * Sample.php
 *
 * This file contains the definition of the {@link Sample} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\UnitObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									Sample.php											*
 *																						*
 *======================================================================================*/

/**
 * Sample object
 *
 * This abstract class is derived from the {@link UnitObject} class, it implements a
 * germplasm sample object which can be instantiated as either a collecting or a breeding
 * sample.
 *
 * The inherited attributes have the following function:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: By default the class sets the
 *		{@link kDOMAIN_SAMPLE} constant.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The optional authority is set with an institute
 *		code.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The identifier is set with the
 *		<tt>:germplasm:identifier</tt> tag.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: This property is not managed.
 *	<li><tt>{@link kTAG_VERSION}</tt>: This property is not managed.
 * </ul>
 *
 * The object can be considered initialised when it has at least the identifier.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/10/2014
 */
abstract class Sample extends UnitObject
{
	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_SAMPLE;

		

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
			$this->offsetSet( kTAG_IDENTIFIER,
							  $this->offsetGet( ':germplasm:identifier' ) );
		
		//
		// Set taxon categories.
		//
		if( $this->offsetExists( ':taxon:genus' ) )
		{
			//
			// Get categories.
			//
			$cats = ( $this->offsetExists( ':taxon:species' ) )
				  ? Term::ResolveTaxonGroup(
				  		$this->mDictionary,
				  		$this->offsetGet( ':taxon:genus' ),
				  		$this->offsetGet( ':taxon:species' ) )
				  : Term::ResolveTaxonGroup(
				  		$this->mDictionary,
				  		$this->offsetGet( ':taxon:genus' ) );
			
			//
			// Set categories.
			//
			if( count( $cats ) )
			{
				foreach( $cats as $key => $value )
					$this->offsetSet( $key, $value );
			}
		
		} // Has genus.
		
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
	
	} // preCommitPrepare.

	 

} // class Sample.


?>
