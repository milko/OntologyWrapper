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
		
			case kDOMAIN_CWR_CHECKLIST:
				return array( ':taxon:epithet', 'cwr:ck:TYPE', 'cwr:ck:CWRCODE',
							  'cwr:ck:NUMB', ':location:admin' );									// ==>
		
			case kDOMAIN_CWR_INVENTORY:
				return array( ':taxon:epithet', ':inventory:NICODE',
							  'cwr:in:NIENUMB', ':unit:version' );					// ==>
		
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
						str_replace( '@@@', 'UNITS', kXML_STANDARDS_BASE ) );		// ==>
	
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
		return $theRoot->addChild( 'UNIT' );										// ==>
	
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
		$unit->addAttribute( 'class', get_class( $this ) );
		
		//
		// Traverse object.
		//
		$this->exportXMLStructure( $this, $unit, $theWrapper, $theUntracked );
	
	} // exportXMLObject.

	 

} // class UnitObject.


?>
