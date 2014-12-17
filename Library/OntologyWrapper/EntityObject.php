<?php

/**
 * EntityObject.php
 *
 * This file contains the definition of the {@link EntityObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\UnitObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									EntityObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Entity object
 *
 * This <em>abstract</em> class is derived from the {@link UnitObject} class, it implements
 * the base class from which concrete instances of <em>institutions</em> and
 * <em>individuals</em> can be derived.
 *
 * The inherited attributes have the following function:
 *
 * <ul>
 *	<li><tt>{@link kTAG_DOMAIN}</tt>: As in the parent class, the domain defines
 *		<em>what</em> the entity is.
 *	<li><tt>{@link kTAG_AUTHORITY}</tt>: As with the parent class, this attribute indicates
 *		who is responsible for the information and identifiers of the entity. In this class
 *		this attribute is set as a string, although this attribute will generally contain a
 *		reference to the entity which takes this function.
 *	<li><tt>{@link kTAG_COLLECTION}</tt>: The entity collection is used to disambiguate
 *		homonym identifiers belonging to the same domain and authority. This might be used
 *		to identify a division within a larger entity.
 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: This attribute represents the entity identifier.
 *	<li><tt>{@link kTAG_VERSION}</tt>: This attribute is used to store time stamp
 *		information regarding the entity record. This attribute <em>will not be used to
 *		compute the object's native identifier</em>.
 * </ul>
 *
 * The class features a series of other default attributes which characterise entities:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NAME}</tt>: <em>Name</em>. This required attribute represents the
 *		name of the entity.
 *	<li><tt>{@link kTAG_ENTITY_TYPE}</tt>: <em>Type</em>. This optional attribute represents
 *		the type of entity, it indicates <em>what</em> the entity is. The attribute is an
 *		enumerated set that can be managed with the {@link EntityType()} method.
 *	<li><tt>{@link kTAG_ENTITY_KIND}</tt>: <em>Kind</em>. This optional attribute represents
 *		the kind of entity, it indicates entity <em>activities</em>. The attribute is an
 *		enumerated set that can be managed with the {@link EntityKind()} method.
 *	<li><tt>{@link kTAG_ENTITY_MAIL}</tt>: <em>Mailing address</em>. This optional attribute
 *		represents the mailing addresses of the entity, it is a list of addresses
 *		discriminated by their type. The attribute can be managed with the
 *		{@link EntityMail()} method.
 *	<li><tt>{@link kTAG_ENTITY_EMAIL}</tt>: <em>Electronic mail address</em>. This optional
 *		attribute represents the e-mail addresses of the entity, it is a list of e-mails
 *		discriminated by their type. The attribute can be managed with the
 *		{@link EntityEmail()}
 *		method.
 *	<li><tt>{@link kTAG_ENTITY_PHONE}</tt>: <em>Telephone number</em>. This optional
 *		attribute represents the telephone numbers of the entity, it is a list of phones
 *		discriminated by their type. The attribute can be managed with the
 *		{@link EntityPhone()} method.
 *	<li><tt>{@link kTAG_ENTITY_FAX}</tt>: <em>Telefax number</em>. This optional
 *		attribute represents the telefax numbers of the entity, it is a list of faxes
 *		discriminated by their type. The attribute can be managed with the
 *		@link EntityFax()} method.
 *	<li><tt>{@link kTAG_ENTITY_NATIONALITY}</tt>: <em>Netionality</em>. This optional
 *		attribute indicates which is the nationality of the entity, it is an enumerated
 *		value that represents the ISO country code.
 *	<li><tt>{@link kTAG_ENTITY_AFFILIATION}</tt>: <em>Affiliation</em>. This optional
 *		attribute contains the list of entities to which the current entity is affiliated.
 *		The attribute is a list of elements each containing two items: the type of
 *		affiliation and the affiliated entity native identifier. The elements of this list
 *		can be managed with the {@link EntityAffiliation()} method.
 *	<li><tt>{@link kTAG_ENTITY_VALID}</tt>: <em>Valid entity</em>. This optional attribute
 *		represents a reference to the entity that is currently in use or that is preferred.
 *		This attribute is featured by obsolete or replaced entities and points to the
 *		entity which currently replaces them. This is used to maintain a historical record
 *		of entities: objects derived from this class should not be deleted.
 * </ul>
 *
 * An entity can be considered initialised when it has at least the domain, identifier and
 * name.
 *
 * This class is declared abstract, you must derive the class to instantiate it.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 01/03/2014
 */
abstract class EntityObject extends UnitObject
{
	/**
	 * Type trait.
	 *
	 * We use this trait to handle types.
	 */
	use	traits\EntityType;

	/**
	 * Kind trait.
	 *
	 * We use this trait to handle kinds.
	 */
	use	traits\EntityKind;

	/**
	 * Mail trait.
	 *
	 * We use this trait to handle the entity mailing addresses.
	 */
	use	traits\EntityMail;

	/**
	 * E-mail trait.
	 *
	 * We use this trait to handle the entity e-mail addresses.
	 */
	use	traits\EntityEmail;

	/**
	 * Telephone trait.
	 *
	 * We use this trait to handle the entity telephone numbers.
	 */
	use	traits\EntityPhone;

	/**
	 * Telefax trait.
	 *
	 * We use this trait to handle the entity telefax numbers.
	 */
	use	traits\EntityFax;

	/**
	 * Affiliation trait.
	 *
	 * We use this trait to handle affiliations.
	 */
	use	traits\EntityAffiliation;

		

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
	 * In this class we link the inited status with the presence of the entity name.
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
		$this->isInited( parent::isInited() &&
						 \ArrayObject::offsetExists( kTAG_NAME ) );

	} // Constructor.

	 
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
	 * In this class we return the entity {@link kTAG_NAME}.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 */
	public function getName( $theLanguage )
	{
		return $this->offsetGet( kTAG_NAME );										// ==>
	
	} // getName.

		

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
	 * In this class we return the entities database.
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
		$database = $theWrapper->Users();
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
			   ."missing entities reference in wrapper." );						// !@! ==>
		
		return NULL;																// ==>
	
	} // ResolveDatabase.

		

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
	 *	<li><tt>{@link kTAG_NAME}</tt>: Name.
	 *	<li><tt>{@link kTAG_ENTITY_TYPE}</tt>: Type.
	 *	<li><tt>{@link kTAG_ENTITY_KIND}</tt>: Kind.
	 *	<li><tt>{@link kTAG_ENTITY_NATIONALITY}</tt>: Nationality.
	 *	<li><tt>{@link kTAG_ENTITY_AFFILIATION}</tt>: Affiliation.
	 *	<li><tt>{@link kTAG_ENTITY_VALID}</tt>: Valid entity reference.
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
		// Set name index.
		//
		$collection->createIndex( array( kTAG_NAME => 1 ),
								  array( "name" => "NAME" ) );
		
		//
		// Set type index.
		//
		$collection->createIndex( array( kTAG_ENTITY_TYPE => 1 ),
								  array( "name" => "TYPE",
								  		 "sparse" => TRUE ) );
		
		//
		// Set kind index.
		//
		$collection->createIndex( array( kTAG_ENTITY_KIND => 1 ),
								  array( "name" => "KIND",
								  		 "sparse" => TRUE ) );
		
		//
		// Set valid index.
		//
		$collection->createIndex( array( kTAG_ENTITY_VALID => 1 ),
								  array( "name" => "VALID",
								  		 "sparse" => TRUE ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.

		

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
	 * In this class we return:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_NAME}</tt>: Entity name.
	 *	<li><tt>{@link kTAG_ENTITY_TYPE}</tt>: Entity type.
	 *	<li><tt>{@link kTAG_ENTITY_KIND}</tt>: Entity kind.
	 *	<li><tt>{@link kTAG_ENTITY_MAIL}</tt>: Entity mailing address.
	 *	<li><tt>{@link kTAG_ENTITY_EMAIL}</tt>: Entity e-mail address.
	 *	<li><tt>{@link kTAG_ENTITY_PHONE}</tt>: Entity telephone number.
	 *	<li><tt>{@link kTAG_ENTITY_FAX}</tt>: Entity telefax number.
	 *	<li><tt>{@link kTAG_ENTITY_NATIONALITY}</tt>: Entity nationality.
	 *	<li><tt>{@link kTAG_ENTITY_AFFILIATION}</tt>: Entity affiliation.
	 *	<li><tt>{@link kTAG_ENTITY_VALID}</tt>: Valid entity.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_NAME,
								   kTAG_ENTITY_TYPE, kTAG_ENTITY_KIND,
								   kTAG_ENTITY_MAIL, kTAG_ENTITY_EMAIL,
								   kTAG_ENTITY_PHONE, kTAG_ENTITY_FAX,
								   kTAG_ENTITY_AFFILIATION, kTAG_ENTITY_NATIONALITY,
								   kTAG_ENTITY_VALID ) );							// ==>
	
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
	 * In this class we link the inited status with the presence of the terms list, the data
	 * type and the label.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_NAME
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
		$this->isInited( parent::isInited() &&
						 \ArrayObject::offsetExists( kTAG_NAME ) );
	
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
	 * @see kTAG_NAME
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
		$this->isInited( parent::isInited() &&
						 \ArrayObject::offsetExists( kTAG_NAME ) );
	
	} // postOffsetUnset.

		

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
	 * We overload this method to set the entity type and kind.
	 *
	 * @param array					$theLabels			Labels.
	 * @param array					$theProperties		Properties.
	 *
	 * @access protected
	 * @return mixed				Node identifier, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @see kTAG_ENTITY_TYPE kTAG_ENTITY_KIND
	 */
	protected function setGraphProperties( &$theLabels, &$theProperties )
	{
		//
		// Init graph parameters.
		//
		parent::setGraphProperties( $theLabels, $theProperties );
		
		//
		// Set entity type.
		//
		if( $this->offsetExists( kTAG_ENTITY_TYPE ) )
			$theProperties[ (string) kTAG_ENTITY_TYPE ]
				= $this->offsetGet( kTAG_ENTITY_TYPE );
		
		//
		// Set entity kind.
		//
		if( $this->offsetExists( kTAG_ENTITY_KIND ) )
			$theProperties[ (string) kTAG_ENTITY_KIND ]
				= $this->offsetGet( kTAG_ENTITY_KIND );
		
		//
		// Set entity affiliation.
		//
		if( $this->offsetExists( kTAG_ENTITY_AFFILIATION ) )
			$theProperties[ (string) kTAG_ENTITY_AFFILIATION ]
				= $this->offsetGet( kTAG_ENTITY_AFFILIATION );
	
	} // setGraphProperties.

	 

} // class EntityObject.


?>
