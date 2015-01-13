<?php

/**
 * Inventory.php
 *
 * This file contains the definition of the {@link Inventory} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\UnitObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									Inventory.php										*
 *																						*
 *======================================================================================*/

/**
 * Inventory object
 *
 * This class is derived from the {@link UnitObject} class, it implements a species
 * inventory which uses the crop wild relative standards as its default properties.
 *
 * The inherited attributes have the following function:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: By default the class sets the
 *		{@link kDOMAIN_INVENTORY} constant.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The authority is set with the institute code,
 *		<tt>:inventory:institute</tt> tag.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The identifier is set with the
 *		<tt>:inventory:code</tt> and <tt>cwr:in:NIENUMB</tt> tags separated by a dash.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: This property is set with the value of the
 *		<tt>:taxon:epithet</tt> tag.
 *	<li><tt>{@link kTAG_VERSION}</tt>: This property is set with an autonumber sequence.
 * </ul>
 *
 * The object can be considered initialised when it has all the above properties.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 05/06/2014
 */
class Inventory extends UnitObject
{
	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_INVENTORY;

		

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
	 * In this class we link the inited status with the presence of all the default
	 * identifier properties.
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
						 \ArrayObject::offsetExists( kTAG_AUTHORITY ) &&
						 \ArrayObject::offsetExists( kTAG_COLLECTION ) );

	} // Constructor.

		

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
									'struct::domain:inventory:cwr' ) );				// ==>
	
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
	 * In this class we link the inited status with the presence of the default identifiers.
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
						 \ArrayObject::offsetExists( kTAG_AUTHORITY ) &&
						 \ArrayObject::offsetExists( kTAG_COLLECTION ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of the default identifiers.
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
						 \ArrayObject::offsetExists( kTAG_AUTHORITY ) &&
						 \ArrayObject::offsetExists( kTAG_COLLECTION ) );
	
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
		// Set taxon.
		//
		if( ! $this->offsetExists( ':taxon:epithet' ) )
		{
			$taxon = Array();
			$taxon[] = $this->offsetGet( ':taxon:genus' );
			if( $this->offsetExists( ':taxon:species' ) )
				$taxon[] = $this->offsetGet( ':taxon:species' );
			if( $this->offsetExists( ':taxon:infraspecies' ) )
				$taxon[] = $this->offsetGet( ':taxon:infraspecies' );
			if( count( $taxon ) )
			{
				$taxon = implode( ' ', $taxon );
				$this->offsetSet( ':taxon:epithet', $taxon );
			}
		
		} // Taxon not yet set.
		
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
		// Check domain.
		//
		if( ! $this->offsetExists( kTAG_DOMAIN ) )
			$this->offsetSet( kTAG_DOMAIN,
							  static::kDEFAULT_DOMAIN );
		
		//
		// Check authority.
		//
		if( ! $this->offsetExists( kTAG_AUTHORITY ) )
			$this->offsetSet( kTAG_AUTHORITY, $this->offsetGet( 'cwr:INSTCODE' ) );
		
		//
		// Check identifier.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
			$this->offsetSet( kTAG_IDENTIFIER, $this->offsetGet( ':inventory:code' )
											  .'-'
											  .$this->offsetGet( 'cwr:in:NIENUMB' ) );
		
		//
		// Check collection.
		//
		if( ! $this->offsetExists( kTAG_COLLECTION ) )
			$this->offsetSet( kTAG_COLLECTION, $this->offsetGet( ':taxon:epithet' ) );
		
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
	
	} // preCommitPrepare.

		

/*=======================================================================================
 *																						*
 *							PROTECTED RESOLUTION UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getFullTextReference															*
	 *==================================================================================*/

	/**
	 * Retun the object full text reference
	 *
	 * In this class we differentiate inventories by code, the returned code will be:
	 *
	 * <ul>
	 *	<li><tt>in</tt>: This prefix determines the domain.
	 *	<li><tt>:inventory:code</tt>: The inventory code.
	 * </ul>
	 *
	 * The values will be separated by a <tt>§</tt> character and the full string will be
	 * enclosed in a pair of <tt>§</tt> characters.
	 *
	 * @access public
	 * @return string				Full text search reference.
	 */
	public function getFullTextReference()
	{
		//
		// Init local storage.
		//
		$string = Array();
		
		//
		// Init domain.
		//
		$string[] = 'in';
		
		//
		// Set code.
		//
		$string[] = $this->offsetGet( ':inventory:code' );
		
		//
		// Set string.
		//
		$string = implode( '§', $string );
		
		return '§'.$string.'§';														// ==>
		
	} // getFullTextReference.

	 

} // class Inventory.


?>
