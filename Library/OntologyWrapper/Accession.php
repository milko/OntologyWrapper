<?php

/**
 * Accession.php
 *
 * This file contains the definition of the {@link Accession} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\UnitObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									Accession.php										*
 *																						*
 *======================================================================================*/

/**
 * Accession object
 *
 * This class is derived from the {@link UnitObject} class, it implements an accession
 * which uses the multicrop passport descriptors as its default properties.
 *
 * The inherited attributes have the following function:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: By default the class sets the
 *		{@link kDOMAIN_ACCESSION} constant.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The authority is set with the institute code,
 *		<tt>:inventory:institute</tt> tag.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The identifier is set with the
 *		<tt>mcpd:ACCENUMB</tt> tag.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: This property is optionally set by the client.
 *	<li><tt>{@link kTAG_VERSION}</tt>: This property is set with the original creation date.
 * </ul>
 *
 * All the above properties, except the version, are used to compute the object's
 * native identifier.
 *
 * The object can be considered initialised when it has at least the domain, authority and
 * the identifier set.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 05/06/2014
 */
class Accession extends UnitObject
{
	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_ACCESSION;

		

/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return global identifier</h4>
	 *
	 * We override this method to exclude the version from the elements that comprise the
	 * global identifier.
	 *
	 * @access public
	 * @return string				The global identifier.
	 */
	public function __toString()
	{
		//
		// Handle domain.
		//
		$gid = ( $this->offsetExists( kTAG_DOMAIN ) )
			 ? $this->offsetGet( kTAG_DOMAIN )
			 : static::kDEFAULT_DOMAIN;
		$gid .= kTOKEN_DOMAIN_SEPARATOR;
		
		//
		// Handle authority.
		//
		if( $this->offsetExists( kTAG_AUTHORITY ) )
			$gid .= ($this->offsetGet( kTAG_AUTHORITY ).kTOKEN_INDEX_SEPARATOR);
		
		//
		// Handle collection.
		//
		if( $this->offsetExists( kTAG_COLLECTION ) )
			$gid .= ($this->offsetGet( kTAG_COLLECTION ).kTOKEN_NAMESPACE_SEPARATOR);
		
		//
		// Handle local identifier.
		//
		if( $this->offsetExists( kTAG_IDENTIFIER ) )
			$gid .= $this->offsetGet( kTAG_IDENTIFIER );
		
		return $gid.kTOKEN_END_TAG;													// ==>
	
	} // __toString.

	

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
	 * In this class we return the accession {@link kTAG_AUTHORITY}, {@link kTAG_COLLECTION}
	 * and {@link kTAG_IDENTIFIER} separated by colons, concatenated to the domain name.
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
		$domain = ( $this->offsetExists( kTAG_DOMAIN ) )
				? static::ResolveCollection(
					static::ResolveDatabase(
						$this->mDictionary ) )
							->matchOne(
								array( kTAG_NID => $this->offsetGet( kTAG_DOMAIN ) ),
								kQUERY_ARRAY,
								array( kTAG_LABEL => TRUE ) )[ kTAG_LABEL ]
				: NULL;

		//
		// Set holding institute.
		//
		if( $this->offsetExists( 'mcpd:INSTCODE' ) )
			$name[] = '['.$this->offsetGet( 'mcpd:INSTCODE' ).']';
		elseif( $this->offsetExists( kTAG_AUTHORITY ) )
			$name[] = $this->offsetGet( kTAG_AUTHORITY );
		
		//
		// Set scientific name.
		//
		if( $this->offsetExists( ':taxon:species:name' ) )
			$name[] = $this->offsetGet( ':taxon:species:name' );
		elseif( $this->offsetExists( ':taxon:epithet' ) )
			$name[] = $this->offsetGet( ':taxon:epithet' );
		
		//
		// Set accession number.
		//
		if( $this->offsetExists( 'mcpd:ACCENUMB' ) )
			$name[] = '('.$this->offsetGet( 'mcpd:ACCENUMB' ).')';
		elseif( $this->offsetExists( kTAG_IDENTIFIER ) )
			$name[] = $this->offsetGet( kTAG_IDENTIFIER );
		
		return ( $domain !== NULL )
			 ? ($domain.' '.implode( ' ', $name ))									// ==>
			 : implode( ' ', $name );												// ==>
	
	} // getName.

	

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
			// Get coordinates.
			//
			if( $this->offsetExists( ':domain:accession:collecting' ) )
				$event = $this->offsetGet( ':domain:accession:collecting' );
			elseif( $this->offsetExists( ':domain:accession:breeding' ) )
				$event = $this->offsetGet( ':domain:accession:breeding' );
			else
				return FALSE;														// ==>
			
			//
			// Resolve error and elevation tags.
			//
			$oerr = $this->resolveOffset( ':location:site:error', TRUE );
			$oalt = $this->resolveOffset( ':location:site:elevation', TRUE );
			$oenv = $this->resolveOffset( ':environment', TRUE );
			
			//
			// Init local storage.
			//
			$range = $dist = NULL;
		
			//
			// Handle elevation range.
			//
			if( array_key_exists( $oalt, $event ) )
			{
				//
				// Set range.
				//
				$tmp = $event[ $oalt ];
				$range = array( $tmp - $theMinElev, $tmp + $theMinElev );
		
			} // Has elevation range.
		
			//
			// Set collecting site error.
			//
			if( array_key_exists( $oerr, $event ) )
			{
				//
				// Get value.
				//
				$tmp = $event[ $oerr ];
			
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
					if( $tmp < $theDefDist )
						$dist = $theDefDist;
			
					//
					// Set value.
					//
					else
						$dist = $tmp;
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
	 * {@link collectStructureOffsets()} of the <tt>struct:mcpd</tt> structure node (PID).
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							$this->mDictionary
								->collectStructureOffsets(
									'struct::domain:accession' ) );					// ==>
	
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
			$this->offsetSet( kTAG_DOMAIN, static::kDEFAULT_DOMAIN );
		
		//
		// Check authority.
		//
		if( ! $this->offsetExists( kTAG_AUTHORITY ) )
			$this->offsetSet( kTAG_AUTHORITY, $this->offsetGet( 'mcpd:INSTCODE' ) );
		
		//
		// Check identifier.
		//
		if( ! $this->offsetExists( kTAG_IDENTIFIER ) )
			$this->offsetSet( kTAG_IDENTIFIER, $this->offsetGet( 'mcpd:ACCENUMB' ) );
		
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
	 * (<tt>:location:site:longitude</tt>) of the colleting or breeding site, and the
	 * coordinate error (<tt>:location:site:error</tt>) as the circle radius.
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
			// Get coordinates.
			//
			if( $this->offsetExists( ':domain:accession:collecting' ) )
				$event = $this->offsetGet( ':domain:accession:collecting' );
			elseif( $this->offsetExists( ':domain:accession:breeding' ) )
				$event = $this->offsetGet( ':domain:accession:breeding' );
			else
				return FALSE;														// ==>
			
			//
			// Get coordinates.
			//
			$olat = $this->resolveOffset( ':location:site:latitude', TRUE );
			$olon = $this->resolveOffset( ':location:site:longitude', TRUE );
			$oerr = $this->resolveOffset( ':location:site:error', TRUE );
			
			//
			// Check coordinates.
			//
			if( array_key_exists( $olat, $event )
			 && array_key_exists( $olon, $event ) )
			{
				//
				// Set circle.
				//
				if( array_key_exists( $oerr, $event )
				 && ($event[ $oerr ] > kCLIMATE_MIN_DIST) )
					$this->offsetSet(
						kTAG_GEO_SHAPE,
						array( kTAG_TYPE => 'Circle',
							   kTAG_GEOMETRY => array(
								   (double) $event[ $olon ],
								   (double) $event[ $olat ] ),
							   kTAG_RADIUS => (int) $event[ $oerr ] ) );
				
				//
				// Set point.
				//
				else
					$this->offsetSet(
						kTAG_GEO_SHAPE,
						array( kTAG_TYPE => 'Point',
							   kTAG_GEOMETRY => array(
								   (double) $event[ $olon ],
								   (double) $event[ $olat ] ) ) );
			}
			else
				return FALSE;														// ==>
		
		} // Shape not yet set.
		
		return TRUE;																// ==>
	
	} // setObjectActualShape.

	 

} // class Accession.


?>
