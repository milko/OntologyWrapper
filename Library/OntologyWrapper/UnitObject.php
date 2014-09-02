<?php

/**
 * UnitObject.php
 *
 * This file contains the definition of the {@link UnitObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\PersistentObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									UnitObject.php										*
 *																						*
 *======================================================================================*/

/**
 * Domains.
 *
 * This file contains the default domain definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

/**
 * UnitObject
 *
 * Unit and entity objects share the same base identifier attributes set, this class
 * implements the common features of both derived classes.
 *
 * All concrete instances of this class share the following attributes:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: The domain is an enumeration that defines the type of
 *		the unit, it provides information on <em>what</em> the unit is.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The unit authority provides a formal identification
 *		to the object, it indicates <em>who</em> is responsible for the object information
 *		and identification.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: The unit collection provides a means for
 *		<em>disambiguation</em> of the object's <em>identifier</em>, it acts as the
 *		namespace for an identifier, making the combination of identifier and collection
 *		unique among all units of the same domain and authority.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The unit identifier is a code that should uniquely
 *		identify the object within the realm of its authority and collection.
 *	<li><tt>{@link kTAG_VERSION}</tt>: The unit version provides a means to have different
 *		versions of the same formal object.
 *	<li><tt>{@link kTAG_ID_GRAPH}</tt>: <em>Unit graph node</em>. If the wrapper uses
 *		a graph database, this property will be used to reference the graph node which
 *		represents the current unit; it is an integer value which is automatically managed.
 * </ul>
 *
 * All the above attributes concur in building the object's persistent identifier, which is
 * the concatenation of the domain, authority, collection, identifier and version.
 *
 * A unit can be considered initialised when it has at least the domain and the identifier.
 *
 * This class is declared abstract, you must derive the class to instantiate it.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 01/03/2014
 */
abstract class UnitObject extends PersistentObject
{
	/**
	 * Sequences selector.
	 *
	 * This constant holds the <i>sequences</i> name for tags; this constant is also used as
	 * the <i>default collection name</i> in which objects of this class are stored.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_units';

	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_UNIT;

		

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
	 * In this class we link the inited status with the presence of the unit domain,
	 * authority and identifier.
	 *
	 * The constructor will automatically set the object domain to the default class domain.
	 *
	 * @param mixed					$theContainer		Data wrapper or properties.
	 * @param mixed					$theIdentifier		Object identifier or properties.
	 * @param boolean				$doAssert			Raise exception if not resolved.
	 *
	 * @access public
	 *
	 * @uses instantiateObject()
	 * @uses TermCount()
	 * @uses isInited()
	 */
	public function __construct( $theContainer = NULL,
								 $theIdentifier = NULL,
								 $doAssert = TRUE )
	{
		//
		// Load object with contents.
		//
		parent::__construct( $theContainer, $theIdentifier, $doAssert );
		
		//
		// Set default domain.
		//
		if( ! $this->offsetExists( kTAG_DOMAIN ) )
			$this->offsetSet( kTAG_DOMAIN, static::kDEFAULT_DOMAIN );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_DOMAIN ) &&
						 \ArrayObject::offsetExists( kTAG_IDENTIFIER ) );

	} // Constructor.

	 
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return global identifier</h4>
	 *
	 * The global identifier of units is the combination of the object's domain, authority,
	 * collection, identifier and version, the identifier is computed as follows:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_DOMAIN}</tt>: The domain is followed by the
	 *		{@link kTOKEN_DOMAIN_SEPARATOR}.
	 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: The authority is followed by the
	 *		{@link kTOKEN_INDEX_SEPARATOR}.
	 *	<li><tt>{@link kTAG_COLLECTION}</tt>: The namespace is followed by the
	 *		{@link kTOKEN_NAMESPACE_SEPARATOR}.
	 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: The identifier is followed by the
	 *		{@link kTOKEN_INDEX_SEPARATOR}.
	 *	<li><tt>{@link kTAG_VERSION}</tt>: The version closes the identifier.
	 *	<li><tt>{@link kTOKEN_END_TAG}</tt>: This tag closes the whole identifier.
	 * </ul>
	 *
	 * Only the domain and identifier are required, all missing attributes will get omitted,
	 * along with the token that follows them.
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
		// Handle identifier.
		//
		if( $this->offsetExists( kTAG_IDENTIFIER ) )
			$gid .= $this->offsetGet( kTAG_IDENTIFIER );
		
		//
		// Handle version.
		//
		if( $this->offsetExists( kTAG_VERSION ) )
			$gid .= (kTOKEN_INDEX_SEPARATOR.$this->offsetGet( kTAG_VERSION ));
		
		return $gid.kTOKEN_END_TAG;													// ==>
	
	} // __toString.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MASTER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setAlias																		*
	 *==================================================================================*/

	/**
	 * Signal object as alias
	 *
	 * In this class we shadow this method, since there cannot be alias units.
	 *
	 * @param boolean				$doSet				<tt>TRUE</tt> to set.
	 *
	 * @access public
	 */
	public function setAlias( $doSet = TRUE )											   {}

		

/*=======================================================================================
 *																						*
 *								STATIC CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveDatabase																	*
	 *==================================================================================*/

	/**
	 * Resolve the database
	 *
	 * In this class we return the units database.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param boolean				$doAssert			Raise exception if unable.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return DatabaseObject		Database or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	static function ResolveDatabase( Wrapper $theWrapper, $doAssert = TRUE, $doOpen = TRUE )
	{
		//
		// Get units database.
		//
		$database = $theWrapper->Units();
		if( $database instanceof DatabaseObject )
		{
			//
			// Open connection.
			//
			if( $doOpen )
				$database->openConnection();
			
			return $database;														// ==>
		
		} // Retrieved units database.
		
		//
		// Raise exception.
		//
		if( $doAssert )
			throw new \Exception(
				"Unable to resolve database: "
			   ."missing units reference in wrapper." );						// !@! ==>
		
		return NULL;																// ==>
	
	} // ResolveDatabase.

		

/*=======================================================================================
 *																						*
 *								STATIC CLIMATE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GetClimateData																	*
	 *==================================================================================*/

	/**
	 * <h4>Retrieve climatic data</h4>
	 *
	 * This method will retrieve the climatic data for the provided parameters:
	 *
	 * <ul>
	 *	<li><b>$theShape</b>: The coordinates, point, polygon or rect, of the area for
	 *		which the climate is requested. This parameter may either be a
	 *		{@link CDataTypeShape} instance, or a GeoJSON shape array.
	 *	<li><b>$theRange</b>: This optional parameter represents the elevation range as an
	 *		array of two elements representing respectively the minimum and maximum
	 *		elevation. If the parameter is provided as a scalar, not a range, the method
	 *		will ignore it.
	 *	<li><b>$theDistance</b>: This parameter represents the maximum distance from the
	 *		provided geometry, if provided, it will be used to limit the matches only to
	 *		those tiles within that distance. <i>This value is equivalent to the "coordinate
	 *		uncertainty in meters", which represents the radius of the circle representing
	 *		the area: thus, the value that will be passed to the service will be multiplied
	 *		by two</i>.
	 * </ul>
	 *
	 * The method will return an array containing the list of all climate variables, no
	 * elements are nested.
	 *
	 * The provided shape format is expected to follow the GeoJson standard.
	 *
	 * If the climate data was not found, the method will return an empty array.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theShape			Site shape.
	 * @param array					$theRange			Elevation range.
	 * @param int					$theDistance		Coordinate uncertainty.
	 *
	 * @static
	 * @return array
	 *
	 * @throws Exception
	 */
	static function GetClimateData( $theWrapper, $theShape, $theRange = NULL,
															$theDistance = NULL )
	{
		//
		// Check shape.
		//
		if( is_array( $theShape )
		 && array_key_exists( kTAG_TYPE, $theShape )
		 && array_key_exists( kTAG_GEOMETRY, $theShape ) )
		{
			//
			// Init local storage.
			//
			$error = NULL;
			$range = FALSE;
			$climate = Array();
			$request = Array();
			
			//
			// Parse by shape type.
			//
			switch( $theShape[ kTAG_TYPE ] )
			{
				case 'Point':
					$request[] = 'point='.implode( ',', $theShape[ kTAG_GEOMETRY ] );
					break;
			
				case 'Rect':
					$tmp = 'rect=';
					$tmp .= implode( ',', $theShape[ kTAG_GEOMETRY ][ 0 ] );
					$tmp .= ';';
					$tmp .= implode( ',', $theShape[ kTAG_GEOMETRY ][ 1 ] );
					$request[] = $tmp;
					break;
			
				case 'Polygon':
					throw new \Exception(
						"Unable to get climate data: "
					   ."polygons are not yet supported." );					// !@! ==>
				
				default:
					$tmp = $theShape[ kTAG_TYPE ];
					throw new \Exception(
						"Unable to get climate data: "
					   ."unsupported shape type [$tmp]." );						// !@! ==>
			
			} // Parsed shape type.

			//
			// Handle elevation range.
			//
			if( is_array( $theRange ) )
			{
				$range = TRUE;
				$request[] = 'elevation='.implode( ',', $theRange );
	
			} // Elevation.
	
			//
			// Handle uncertainty.
			//
			if( $theDistance !== NULL )
			{
				//
				// Cast to integer.
				//
				$theDistance = (int) $theDistance;
				
				//
				// Handle valid uncertainty.
				//
				if( ($theDistance >= kCLIMATE_MIN_DIST)		// Minimum bound.
				 && ($theDistance <= kCLIMATE_MAX_DIST) )
				{
					$range = TRUE;
					$request[] = "distance=".$theDistance * 2;
				
				} // Valid distance.
				
				else
					$theDistance = NULL;
			
			} // Provided uncertainty.
			
			//
			// Handle ranges.
			//
			if( $range )
			{
				$request[] = 'near';
				$request[] = 'range';
			
			} // Elevation range and/or uncertainty.
	
			//
			// Handle contains.
			//
			else
				$request[] = 'contains';
			
			//
			// Check range distance.
			//
			if( in_array( 'range', $request )
			 && ($theDistance === NULL) )
				throw new \Exception(
					"Unable to get climate data: "
				   ."range queries require the distance, "
				   ."it is either missing or larger than "
				   .kCLIMATE_MAX_DIST
				   ." meters." );												// !@! ==>
		
			//
			// Get climate data.
			//
			$request = kCLIMATE_URL.'?'.implode( '&', $request );
			$data = @file_get_contents( $request );
			$data = ( strlen( $data ) )
				  ? json_decode( $data, TRUE )
				  : Array();
		
			//
			// Set climate data.
			//
			if( is_array( $data )
			 && array_key_exists( 'status', $data )
			 && array_key_exists( 'state', $data[ 'status' ] )
			 && ($data[ 'status' ][ 'state' ] == 'OK') )
			{
				//
				// Check if it found something.
				//
				if( array_key_exists( 'data', $data )
				 && count( $data[ 'data' ] ) )
				{
					//
					// Set tiles count.
					//
					if( array_key_exists( 'total', $data[ 'status' ] ) )
						$climate[ (string) $theWrapper->getSerial( ':environment:tiles' ) ]
							= (int) $data[ 'status' ][ 'total' ];
				
					//
					// Point to data.
					//
					if( $range )
						$data = & $data[ 'data' ];
					else
						$data = array_shift( $data[ 'data' ] );
			
					//
					// Set environmental data elevation range.
					//
					if( array_key_exists( 'elev', $data ) )
					{
						$ref = & $data[ 'elev' ];
						if( $range )
						{
							$climate[ (string) $theWrapper->getSerial(
								':environment:elevation-min' ) ] = (int) $ref[ 'l' ];
							$climate[ (string) $theWrapper->getSerial(
								':environment:elevation-mean' ) ] = (int) $ref[ 'm' ];
							$climate[ (string) $theWrapper->getSerial(
								':environment:elevation-max' ) ] = (int) $ref[ 'h' ];
						}
						else
							$climate[ (string) $theWrapper->getSerial(
								':environment:elevation-mean' ) ] = (int) $ref;
					}
			
					//
					// Set environmental data distance range.
					//
					if( array_key_exists( 'dist', $data ) )
					{
						if( $range )
						{
							$ref = & $data[ 'dist' ];
							$climate[ (string) $theWrapper->getSerial(
								':environment:distance-min' ) ] = (int) $ref[ 'l' ];
							$climate[ (string) $theWrapper->getSerial(
								':environment:distance-mean' ) ] = (int) $ref[ 'm' ];
							$climate[ (string) $theWrapper->getSerial(
								':environment:distance-max' ) ] = (int) $ref[ 'h' ];
						}
					}
			
					//
					// Point to climatic data.
					//
					if( array_key_exists( 'clim', $data )
					 && is_array( $data[ 'clim' ] ) )
					{
						//
						// Point to current climatic data.
						//
						$data = & $data[ 'clim' ];
						if( array_key_exists( '2000', $data )
						 && is_array( $data[ '2000' ] ) )
						{
							//
							// Point to data.
							//
							$data = & $data[ '2000' ];
			
							//
							// Set environment stratification data.
							//
							if( array_key_exists( 'gens', $data ) )
							{
								$ref = & $data[ 'gens' ];
								if( $range )
								{
									$tag = (string) $theWrapper->getSerial( 'gens' );
									$climate[ $tag ] = Array();
									foreach( $ref[ 'id' ] as $tmp )
										$climate[ $tag ][] = $tmp;
									
									$tag = (string) $theWrapper->getSerial( 'gens:clim' );
									$climate[ $tag ] = Array();
									foreach( $ref[ 'c' ] as $tmp )
										$climate[ $tag ][] = "gens:clim:$tmp";
									
									$tag = (string) $theWrapper->getSerial( 'gens:zone' );
									$climate[ $tag ] = Array();
									foreach( $ref[ 'e' ] as $tmp )
										$climate[ $tag ][] = "gens:zone:$tmp";
								}
								else
								{
									$tag = (string) $theWrapper->getSerial( 'gens' );
									$climate[ $tag ] = array( $ref[ 'id' ] );
									
									$tag = (string) $theWrapper->getSerial( 'gens:clim' );
									$tmp = $ref[ 'c' ];
									$climate[ $tag ] = array( "gens:clim:$tmp" );

									$tag = (string) $theWrapper->getSerial( 'gens:zone' );
									$tmp = $ref[ 'e' ];
									$climate[ $tag ] = array( "gens:zone:$tmp" );
								}
							}
			
							//
							// Set harmonized world soil data.
							//
							if( array_key_exists( 'hwsd', $data ) )
							{
								$ref = & $data[ 'hwsd' ];
								$tag = (string) $theWrapper->getSerial( 'hwsd' );
								if( $range )
								{
									$climate[ $tag ] = Array();
									foreach( $ref as $tmp )
										$climate[ $tag ][] = "hwsd:$tmp";
								}
								else
									$climate[ $tag ] = array( "hwsd:$ref" );
							}
			
							//
							// Set global human footprint.
							//
							if( array_key_exists( 'ghf', $data ) )
							{
								$ref = & $data[ 'ghf' ];
								$tag = (string) $theWrapper->getSerial(
													':environment:ghf' );
								if( $range )
								{
									$climate[ $tag ] = Array();
									foreach( $ref as $tmp )
										$climate[ $tag ][] = $tmp;
								}
								else
									$climate[ $tag ] = array( $ref );
							}
			
							//
							// Set global cover data.
							//
							if( array_key_exists( 'gcov', $data ) )
							{
								$ref = & $data[ 'gcov' ];
								$tag = (string) $theWrapper->getSerial( 'globcov' );
								if( $range )
								{
									$climate[ $tag ] = Array();
									foreach( $ref as $tmp )
										$climate[ $tag ][] = "globcov:$tmp";
								}
								else
									$climate[ $tag ] = array( "globcov:$ref" );
							}
			
							//
							// Set bio-climatic data.
							//
							if( array_key_exists( 'bio', $data ) )
							{
								$tag = (string) $theWrapper->getSerial(
													':environment:bio' );
								$climate[ $tag ] = Array();
								$ref = & $data[ 'bio' ];
								foreach( $ref as $key => $value )
								{
									//
									// Set element tag.
									//
									$sub = (string) $theWrapper->getSerial(
										':environment:bio:'.sprintf( '%02d', (int) $key ) );
									
									//
									// Parse by variable.
									//
									switch( $key )
									{
										case '1':
										case '2':
										case '5':
										case '6':
										case '8':
										case '9':
										case '10':
										case '11':
											$climate[ $tag ][ $sub ]
												= ( $range )
												? round( $value[ 'm' ]
													   / 10, 1 )
												: round( $value / 10, 1 );
											break;
					
										default:
											$climate[ $tag ][ $sub ]
												= ($range)
												? round( $value[ 'm' ], 2 )
												: round( $value, 2 );
											break;
									}
								}
							}
			
							//
							// Set precipitation data.
							//
							if( array_key_exists( 'prec', $data ) )
							{
								$tag = (string) $theWrapper->getSerial(
													':environment:precipitation' );
								$ref = & $data[ 'prec' ];
								foreach( $ref as $key => $value )
								{
									//
									// Set element prefix.
									//
									$pre = ':environment:precipitation:'
										  .sprintf( '%02d', (int) $key );
									$smin = (string) $theWrapper->getSerial( "$pre-min" );
									$smea = (string) $theWrapper->getSerial( "$pre-mean" );
									$smax = (string) $theWrapper->getSerial( "$pre-max" );
									
									if( $range )
									{
										$climate[ $tag ][ $smin ] = (int) $value[ 'l' ];
										$climate[ $tag ][ $smea ] = (int) $value[ 'm' ];
										$climate[ $tag ][ $smax ] = (int) $value[ 'h' ];
									}
									else
										$climate[ $tag ][ $smea ] = (int) $value;
			
								} // Iterating precipitation.
							}
			
							//
							// Set temperature data.
							//
							if( array_key_exists( 'temp', $data ) )
							{
								$tag = (string) $theWrapper->getSerial(
													':environment:temperature' );
								$ref = & $data[ 'temp' ];
								foreach( $ref as $key => $value )
								{
									switch( $key )
									{
										case 'm':
											$pre = 'mean';
											break;
										case 'l':
											$pre = 'min';
											break;
										case 'h':
											$pre = 'max';
											break;
									}
									
									foreach( $value as $month => $temp )
									{
										$month = sprintf( '%02d', (int) $month );
										if( $range )
										{
											foreach( $temp as $rng => $deg )
											{
												switch( $rng )
												{
													case 'm':
														$suf = 'mean';
														break;
													case 'l':
														$suf = 'min';
														break;
													case 'h':
														$suf = 'max';
														break;
												}
												$sub = (string) $theWrapper->getSerial(
															":environment:temperature:"
														   ."$month-$pre-$suf" );
												$climate[ $tag ][ $sub ]
													= round( $deg / 10, 1 );
											}
										}
										else
										{
											$sub = (string) $theWrapper->getSerial(
														":environment:temperature:"
													   ."$month-$pre-mean" );
											$climate[ $tag ][ $sub ]
												= round( $temp / 10, 1 );
										}
									}
			
								} // Iterating temperature.
							}
						
						} // Has current climatic data.
						
					} // Has climatic data.
				
				} // Found something.
			
			} // Received data.
			
			return $climate;														// ==>
		
		} // Has type and geometry.
		
		else
			throw new \Exception(
				"Unable to get climate data: "
			   ."invalid shape structure." );									// !@! ==>
		
	} // GetClimateData.

		

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
	 * In this class we return the domain name, derived classes should first call the parent
	 * method and catenate the local name with the parent name.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 */
	public function getName( $theLanguage )
	{
		//
		// Check wrapper.
		//
		if( ($this->mDictionary !== NULL)
		 && $this->offsetExists( kTAG_DOMAIN ) )
		{
			//
			// Get domain.
			//
			$domain
				= Term::ResolveCollection(
					Term::ResolveDatabase(
						$this->mDictionary ) )
							->matchOne(
								array( kTAG_NID => $this->offsetGet( kTAG_DOMAIN ) ),
								kQUERY_ARRAY,
								array( kTAG_LABEL => TRUE ) );
			
			return OntologyObject::SelectLanguageString(
						$domain[ kTAG_LABEL ],
						$theLanguage );												// ==>
		
		} // Has wrapper and domain.
		
		return NULL;																// ==>
	
	} // getName.

		

/*=======================================================================================
 *																						*
 *								STATIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	CreateIndexes																	*
	 *==================================================================================*/

	/**
	 * Create indexes
	 *
	 * In this class we index the following offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_DOMAIN}</tt>: Domain.
	 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: Authority.
	 *	<li><tt>{@link kTAG_COLLECTION}</tt>: Collection.
	 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: Identifier.
	 *	<li><tt>{@link kTAG_VERSION}</tt>: Version.
	 * </ul>
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 *
	 * @static
	 * @return CollectionObject		The collection.
	 */
	static function CreateIndexes( DatabaseObject $theDatabase )
	{
		//
		// Set parent indexes and retrieve collection.
		//
		$collection = parent::CreateIndexes( $theDatabase );
		
		//
		// Set domain index.
		//
		$collection->createIndex( array( kTAG_DOMAIN => 1 ),
								  array( "name" => "DOMAIN" ) );
		
		//
		// Set authority index.
		//
		$collection->createIndex( array( kTAG_AUTHORITY => 1 ),
								  array( "name" => "AUTHORITY" ) );
		
		//
		// Set collection index.
		//
		$collection->createIndex( array( kTAG_COLLECTION => 1 ),
								  array( "name" => "COLLECTION",
								  		 "sparse" => TRUE ) );
		
		//
		// Set identifier index.
		//
		$collection->createIndex( array( kTAG_IDENTIFIER => 1 ),
								  array( "name" => "LID" ) );
		
		//
		// Set version index.
		//
		$collection->createIndex( array( kTAG_VERSION => 1 ),
								  array( "name" => "VERSION",
								  		 "sparse" => TRUE ) );
		
		//
		// Set graph node identifier index.
		//
		$collection->createIndex( array( kTAG_ID_GRAPH => 1 ),
								  array( "name" => "GRAPH" ) );
		
		//
		// Set geographic unit index.
		//
		$collection->createIndex( array( kTAG_GEO_SHAPE => "2dsphere" ),
								  array( "name" => "SHAPE",
								  		 "sparse" => TRUE ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	UnmanagedOffsets																*
	 *==================================================================================*/

	/**
	 * Return unmanaged offsets
	 *
	 * In this class we return the offsets that are required by the object:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_DOMAIN}</tt>: Object domain.
	 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: Object identifier.
	 * </ul>
	 *
	 * These tags will not be part of the offset management framework, since they are
	 * required.
	 *
	 * @static
	 * @return array				List of unmanaged offsets.
	 */
	static function UnmanagedOffsets()
	{
		return array_merge(
			parent::UnmanagedOffsets(),
			array( kTAG_DOMAIN, kTAG_IDENTIFIER ) );								// ==>
	
	} // UnmanagedOffsets.

	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_DOMAIN}</tt>: Unit domain.
	 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: Unit authority.
	 *	<li><tt>{@link kTAG_COLLECTION}</tt>: Unit collection.
	 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: Unit identifier.
	 *	<li><tt>{@link kTAG_VERSION}</tt>: Unit version.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_DOMAIN, kTAG_AUTHORITY,
								   kTAG_COLLECTION, kTAG_IDENTIFIER,
								   kTAG_VERSION ) );								// ==>
	
	} // DefaultOffsets.

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ListOffsets																		*
	 *==================================================================================*/

	/**
	 * Return list offsets
	 *
	 * The list offsets are those that will be used to provide a table view of objects
	 * belonging to the provided domain.
	 *
	 * @param string				$theDomain			Object domain.
	 *
	 * @static
	 * @return array				List of list offsets.
	 */
	static function ListOffsets( $theDomain )
	{
		//
		// Parse domain.
		//
		switch( $theDomain )
		{
			case kDOMAIN_UNIT:
				return array( kTAG_DOMAIN, kTAG_AUTHORITY,
							  kTAG_COLLECTION, kTAG_IDENTIFIER,
							  kTAG_VERSION );										// ==>
		
			case kDOMAIN_ENTITY:
				return array( kTAG_NAME );											// ==>
		
			case kDOMAIN_INDIVIDUAL:
				return array( kTAG_NAME );											// ==>
		
			case kDOMAIN_ORGANISATION:
				return array( kTAG_IDENTIFIER, kTAG_ENTITY_ACRONYM, kTAG_NAME );	// ==>
		
			case kDOMAIN_ACCESSION:
				return array( 'mcpd:INSTCODE', 'mcpd:ACCENUMB',
							  ':taxon:epithet' );									// ==>
		
			case kDOMAIN_FOREST:
				return array( 'fcu:unit:number', 'fcu:unit:data-collection',
							  ':location:country', ':location:admin-1',
							  ':location:admin-2', ':location:admin-3' );			// ==>
		
			case kDOMAIN_CHECKLIST:
				return array( ':taxon:epithet', 'cwr:ck:TYPE', 'cwr:ck:CWRCODE',
							  'cwr:ck:NUMB', ':location:admin' );									// ==>
		
			case kDOMAIN_INVENTORY:
				return array( ':taxon:epithet', ':inventory:NICODE',
							  'cwr:in:NIENUMB', ':unit:version' );					// ==>
		
			case kDOMAIN_HH_ASSESSMENT:
				return array( 'abdh:ID_HOUSEHOLD',
							  'abdh:STATE', 'abdh:DISTRICT', 'abdh:BLOCKS', 'abdh:VILLAGE',
							  ':unit:version' );									// ==>
		
		} // Parsed domain.
		
		return Array();																// ==>
	
	} // ListOffsets.

		

/*=======================================================================================
 *																						*
 *								STATIC EXPORT INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	XMLRootElement																	*
	 *==================================================================================*/

	/**
	 * Return XML root element
	 *
	 * In this class we return the <tt>UNITS</tt> root element.
	 *
	 * @static
	 * @return SimpleXMLElement		XML export root element.
	 */
	static function XMLRootElement()
	{
		return new \SimpleXMLElement(
						str_replace(
							'@@@', kIO_XML_UNITS, kXML_STANDARDS_BASE ) );			// ==>
	
	} // XMLRootElement.

		

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
	 * In this class we link the inited status with the presence of the terms list, the data
	 * type and the label.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_DOMAIN kTAG_AUTHORITY kTAG_IDENTIFIER
	 *
	 * @uses TermCount()
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_DOMAIN ) &&
						 \ArrayObject::offsetExists( kTAG_IDENTIFIER ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of the terms list, the data
	 * type and the label.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_DOMAIN kTAG_AUTHORITY kTAG_IDENTIFIER
	 *
	 * @uses TermCount()
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_DOMAIN ) &&
						 \ArrayObject::offsetExists( kTAG_IDENTIFIER ) );
	
	} // postOffsetUnset.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitFinalise																*
	 *==================================================================================*/

	/**
	 * Finalise object before commit
	 *
	 * We overload this method here to add the {@link kTAG_IDENTIFIER} value to the
	 * full-text search property: we need to do this here because both the
	 * {@link kTAG_IDENTIFIER} and {@link kTAG_COLLECTION} properties are not included in
	 * the managed tags, since they are both required.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses addToFullText()
	 */
	protected function preCommitFinalise( &$theTags, &$theRefs )
	{
		//
		// Check identifier.
		//
		if( $this->offsetExists( kTAG_IDENTIFIER ) )
		{
			//
			// Get identifier info.
			//
			$tag = $this->mDictionary->getObject( kTAG_IDENTIFIER, TRUE );
			
			//
			// Add identifier to full-text search.
			//
			$this->addToFullText( $this->offsetGet( kTAG_IDENTIFIER ),
								  kSTANDARDS_LANGUAGE,
								  $tag[ kTAG_DATA_TYPE ],
								  $tag[ kTAG_DATA_KIND ] );
		
		} // Has identifier.
		
		//
		// Call parent method.
		//
		parent::preCommitFinalise( $theTags, $theRefs );
	
	} // preCommitFinalise.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitObjectIdentifiers														*
	 *==================================================================================*/

	/**
	 * Load object identifiers
	 *
	 * In this class we set the native identifier, if not yet filled, with the global
	 * identifier generated by the {@link __toString()} method and we set the sequence
	 * number, {@link kTAG_ID_SEQUENCE}, if it is not yet set, by requesting it from the
	 * database of the current object's container.
	 *
	 * @access protected
	 */
	protected function preCommitObjectIdentifiers()
	{
		//
		// Check if committed.
		//
		if( ! $this->isCommitted() )
		{
			//
			// Call parent method.
			//
			parent::preCommitObjectIdentifiers();
			
			//
			// Set native identifier.
			//
			if( ! \ArrayObject::offsetExists( kTAG_NID ) )
				\ArrayObject::offsetSet( kTAG_NID, $this->__toString() );
		
		} // Not committed.
	
	} // preCommitObjectIdentifiers.

		

/*=======================================================================================
 *																						*
 *								PROTECTED STATUS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isReady																			*
	 *==================================================================================*/

	/**
	 * Check if object is ready
	 *
	 * In this class we ensure the object has the sequence number, {@link kTAG_ID_SEQUENCE}
	 * and the native identifier, {@link kTAG_NID}.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 *
	 * @see kTAG_NID kTAG_ID_SEQUENCE
	 */
	protected function isReady()
	{
		return ( parent::isReady()
			  && $this->offsetExists( kTAG_NID ) );									// ==>
	
	} // isReady.

		

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
	 * In this class we return the {@link kTAG_DOMAIN}, {@link kTAG_AUTHORITY},
	 * {@link kTAG_COLLECTION}, {@link kTAG_IDENTIFIER} and the {@link kTAG_VERSION}
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
							array( kTAG_DOMAIN, kTAG_AUTHORITY,
								   kTAG_COLLECTION, kTAG_IDENTIFIER,
								   kTAG_VERSION ) );								// ==>
	
	} // lockedOffsets.

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	xmlUnitElement																	*
	 *==================================================================================*/

	/**
	 * Return XML unit element
	 *
	 * In this class we return the <tt>UNIT</tt> element.
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	protected function xmlUnitElement( \SimpleXMLElement $theRoot )
	{
		return $theRoot->addChild( kIO_XML_TRANS_UNITS );							// ==>
	
	} // xmlUnitElement.

		

/*=======================================================================================
 *																						*
 *								PROTECTED GRAPH UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setGraphProperties																*
	 *==================================================================================*/

	/**
	 * Compute graph labels and properties
	 *
	 * We overload this method to set the object's default domain.
	 *
	 * @param array					$theLabels			Labels.
	 * @param array					$theProperties		Properties.
	 *
	 * @access protected
	 * @return mixed				Node identifier, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 */
	protected function setGraphProperties( &$theLabels, &$theProperties )
	{
		//
		// Init graph parameters.
		//
		parent::setGraphProperties( $theLabels, $theProperties );
		
		//
		// Set label.
		//
		$theLabels[] = static::kDEFAULT_DOMAIN;
	
		//
		// Set identifier.
		//
		$theProperties[ 'GID' ] = $this->offsetGet( kTAG_NID );
	
	} // setGraphProperties.

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	exportXMLObject																	*
	 *==================================================================================*/

	/**
	 * Export the current object in XML format
	 *
	 * We overload this method to add the class name to the unit element.
	 *
	 * @param SimpleXMLElement		$theContainer		Dump container.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theUntracked		List of untracked offsets.
	 *
	 * @access protected
	 */
	protected function exportXMLObject( \SimpleXMLElement $theContainer,
										Wrapper			  $theWrapper,
														  $theUntracked )
	{
		//
		// Create unit.
		//
		$unit = static::xmlUnitElement( $theContainer );
		$unit->addAttribute( kIO_XML_ATTR_QUAL_CLASS, get_class( $this ) );
		
		//
		// Traverse object.
		//
		$this->exportXMLStructure( $this, $unit, $theWrapper, $theUntracked );
	
	} // exportXMLObject.

	 

} // class UnitObject.


?>
