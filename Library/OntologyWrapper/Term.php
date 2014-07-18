<?php

/**
 * Term.php
 *
 * This file contains the definition of the {@link Term} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\MetadataObject;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *										Term.php										*
 *																						*
 *======================================================================================*/

/**
 * Term
 *
 * A term object holds the necessary information to <i>uniquely identify</i>,
 * <i>document</i> and <i>share</i> a <i>generic term or concept</i> which is <i>not related
 * to a specific context</i>.
 *
 * For instance, a <tt>name</tt> is defined as a string or text that identifies something,
 * this is true for both a person name or an object name, however, the term <tt>name</tt>
 * will bare a different meaning depending on what context it is used in: the term object
 * holds the definition of that will not change with its context.
 *
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. This required attribute holds
 *		the term global identifier. By convention this value is the combination of the
 *		namespace, {@link kTAG_NAMESPACE}, and the local identifier, {@link kTAG_ID_LOCAL},
 *		separated by the {@link kTOKEN_NAMESPACE_SEPARATOR} token; this identifier may also
 *		be manually set. This attribute must be managed with its offset.
 *	<li><tt>{@link kTAG_NAMESPACE}</tt>: <em>Namespace</em>. This optional attribute is a
 *		reference to another term object that represents the namespace of the current term.
 *		It is by definition the global identifier of the namespace term. This attribute must
 *		be managed with its offset.
 *	<li><tt>{@link kTAG_ID_LOCAL}</tt>: <em>Local identifier</em>. This required attribute
 *		is a string that represents the current term unique identifier within its namespace.
 *		The combination of the current term's namespace and this attribute form the term's
 *		global identifier. This attribute must be managed with its offset.
 *	<li><tt>{@link kTAG_ID_GRAPH}</tt>: <em>Attribute graph node</em>. If the wrapper uses
 *		a graph database, this property will be used to reference the graph node which
 *		represents the current term as a data attribute of a tag; it is an integer value
 *		which is automatically managed.
 *	<li><tt>{@link kTAG_LABEL}</tt>: <em>Label</em>. The label represents the <i>name or
 *		short description</i> of the term that the current object defines. All terms
 *		<em>should</em> have a label, since this is how human users will be able to identify
 *		and select them. This attribute has the {@link kTYPE_LANGUAGE_STRINGS} data type,
 *		which is constituted by a list of elements in which the {@link kTAG_LANGUAGE} item
 *		holds the label language code and the {@link kTAG_TEXT} holds the label text. To
 *		populate and handle labels by language, use the {@link Label()} offset accessor
 *		method. Some terms may not have a language element, for instance the number
 *		<tt>2</tt> may not need to be expressed in other ways.
 *	<li><tt>{@link kTAG_DEFINITION}</tt>: <em>Definition</em>. The definition represents the
 *		<i>description or extended definition</i> of the term that the current object object
 *		defines. The definition is similar to the <em>description</em>, except that while
 *		the description provides context specific information, the definition should not.
 *		All terms <em>should</em> have a definition, if the object label is not enough to
 *		provide a sufficient definition. Definitions have the {@link kTYPE_LANGUAGE_STRINGS}
 *		data type in which the {@link kTAG_LANGUAGE} element holds the definition language
 *		code and the {@link kTAG_TEXT} holds the definition text. To populate and handle
 *		definitions by language, use the {@link Definition()} offset accessor method.
 *	<li><tt>{@link kTAG_SYNONYM}</tt>: <em>Synonyms</em>. This attribute is a <em>set of
 *		strings</em> representing <em>alternate identifiers of this term</em>, not formally
 *		defined in the current data set.
 *	<li><tt>{@link kTAG_TERM_TYPE}</tt>: <em>Type</em>. This attribute is an <em>enumerated
 *		set</em> which <em>qualifies</em> and sets a <em>context</en> for the current term.
 *		The individual elements can be managed with the {@link TermType()} method.
 *	<li><tt>{@link kTAG_MASTER}</tt>: <em>Master term</em>. This property can be used by
 *		<em>synonym terms</em> to <em>reference</em> a single term which represents an
 *		<em>instance</em> of the current term. The current term will hold only the required
 *		information, while the referenced term will hold the complete information. This is
 *		useful when there are several terms which are exact cross references.
 * </ul>
 *
 * The {@link __toString()} method will return the value stored in the native identifier,
 * if set, or the computed global identifier if at least the local identifier is set; if the
 * latter is not set, the method will fail.
 *
 * Objects of this class can hold any additional attribute that is considered necessary or
 * useful to define and share the current term. In this class we define only those
 * attributes that constitute the core functionality of the object, derived classes will add
 * attributes specific to the domain in which the object will operate.
 *
 * The object is considered initialised, {@link isInited()}, if it has at least the local
 * identifier, {@link kTAG_ID_LOCAL}, and the label, {@link kTAG_LABEL} or the master
 * {@link kTAG_MASTER}.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */
class Term extends MetadataObject
{
	/**
	 * Label trait.
	 *
	 * We use this trait to handle labels.
	 */
	use	traits\Label;

	/**
	 * Definition trait.
	 *
	 * We use this trait to handle definitions.
	 */
	use	traits\Definition;

	/**
	 * Type trait.
	 *
	 * We use this trait to handle types.
	 */
	use	traits\TermType;

	/**
	 * Synonym trait.
	 *
	 * We use this trait to handle synonyms.
	 */
	use	traits\Synonym;

	/**
	 * Default collection name.
	 *
	 * This constant provides the <i>default collection name</i> in which objects of this
	 * class are stored.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_terms';

		

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
	 * In this class we link the inited status with the presence of the local identifier and
	 * the label.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @uses isInited()
	 *
	 * @see kTAG_ID_LOCAL kTAG_LABEL
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Load object with contents.
		//
		parent::__construct( $theContainer, $theIdentifier );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_ID_LOCAL ) &&
						 ( \ArrayObject::offsetExists( kTAG_LABEL ) ||
						   \ArrayObject::offsetExists( kTAG_MASTER ) ) );

	} // Constructor.

	 
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return global identifier</h4>
	 *
	 * If the native identifier, {@link kTAG_NID}, is set, this method will return its
	 * value. If that offset is not yet set, the method will compute the global identifier
	 * by concatenating the object's namespace, {@link kTAG_NAMESPACE}, with the object's
	 * local identifier, {@link kTAG_ID_LOCAL}, separated by the
	 * {@link kTOKEN_NAMESPACE_SEPARATOR} token. This will only occur if the object has the
	 * local identifier, if that is not the case, the method will return an empty string to
	 * prevent the method from causing an error.
	 *
	 * @access public
	 * @return string				The global identifier.
	 */
	public function __toString()
	{
		//
		// Get native identifier.
		//
		if( \ArrayObject::offsetExists( kTAG_NID ) )
			return \ArrayObject::offsetGet( kTAG_NID );								// ==>
		
		//
		// Compute global identifier.
		//
		if( \ArrayObject::offsetExists( kTAG_ID_LOCAL ) )
			return ( \ArrayObject::offsetExists( kTAG_NAMESPACE ) )
				 ? (\ArrayObject::offsetGet( kTAG_NAMESPACE )
				   .kTOKEN_NAMESPACE_SEPARATOR
				   .\ArrayObject::offsetGet( kTAG_ID_LOCAL ))						// ==>
				 : \ArrayObject::offsetGet( kTAG_ID_LOCAL );						// ==>
		
		return '';																	// ==>
	
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
	 * In this class we return the term's label.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 */
	public function getName( $theLanguage )
	{
		//
		// Check label.
		//
		if( $this->offsetExists( kTAG_LABEL ) )
			return OntologyObject::SelectLanguageString(
				$this->offsetGet( kTAG_LABEL ), $theLanguage );						// ==>
		
		return NULL;																// ==>
	
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
	 * In this class we return the metadata database.
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
		// Get metadata database.
		//
		$database = $theWrapper->Metadata();
		if( $database instanceof DatabaseObject )
		{
			//
			// Open connection.
			//
			if( $doOpen )
				$database->openConnection();
			
			return $database;														// ==>
		
		} // Retrieved metadata database.
		
		//
		// Raise exception.
		//
		if( $doAssert )
			throw new \Exception(
				"Unable to resolve database: "
			   ."missing metadata reference in wrapper." );						// !@! ==>
		
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
	 *	<li><tt>{@link kTAG_ID_LOCAL}</tt>: Local identifier.
	 *	<li><tt>{@link kTAG_NAMESPACE}</tt>: Namespace.
	 *	<li><tt>{@link kTAG_LABEL}</tt>: Labels.
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
		// Set local identifier index.
		//
		$collection->createIndex( array( kTAG_ID_LOCAL => 1 ),
								  array( "name" => "LID" ) );
		
		//
		// Set namespace index.
		//
		$collection->createIndex( array( kTAG_NAMESPACE => 1 ),
								  array( "name" => "NAMESPACE",
										 "sparse" => TRUE ) );
		
		//
		// Set graph node identifier index.
		//
		$collection->createIndex( array( kTAG_ID_GRAPH => 1 ),
								  array( "name" => "GRAPH",
										 "sparse" => TRUE ) );
		
		//
		// Set label index.
		//
		$collection->createIndex( array( kTAG_LABEL => 1 ),
								  array( "name" => "LABEL",
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
	 *	<li><tt>{@link kTAG_NAMESPACE}</tt>: Term namespace reference.
	 *	<li><tt>{@link kTAG_ID_LOCAL}</tt>: Term local identifier.
	 *	<li><tt>{@link kTAG_TERM_TYPE}</tt>: Term type.
	 *	<li><tt>{@link kTAG_LABEL}</tt>: Term label.
	 *	<li><tt>{@link kTAG_DEFINITION}</tt>: Term definition.
	 *	<li><tt>{@link kTAG_SYNONYM}</tt>: Term synonyms.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_NAMESPACE, kTAG_ID_LOCAL, kTAG_TERM_TYPE,
								   kTAG_LABEL, kTAG_DEFINITION,
								   kTAG_SYNONYM ) );								// ==>
	
	} // DefaultOffsets.

		

/*=======================================================================================
 *																						*
 *								STATIC RESOLUTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveCountryCode																*
	 *==================================================================================*/

	/**
	 * Resolve a country code
	 *
	 * This method can be used to resolve a country code, it expects a code and will check
	 * whether the code exists among the ISO standards, the method will check the provided
	 * code in the following order:
	 *
	 * <ul>
	 *	<li><em>2 character codes</em>: <tt>iso:3166:1:alpha-2</tt>
	 *	<li><em>3 character codes</em>:
	 *	 <ul>
	 *		<li><em>Numeric</em>:
	 *		 <ul>
	 *			<li><tt>iso:3166:1:numeric</tt>
	 *			<li><tt>iso:3166:3:numeric</tt>
	 *		 </ul>
	 *		<li><em>Character</em>:
	 *		 <ul>
	 *			<li><tt>iso:3166:1:alpha-3</tt>
	 *			<li><tt>iso:3166:3:alpha-3</tt>
	 *		 </ul>
	 *	 </ul>
	 *	<li><em>4 character codes</em>: <tt>iso:3166:3:alpha-4</tt>
	 * </ul>
	 *
	 * If the code is resolved, the method will return the code with its namespace, if not,
	 * the method will return <tt>NULL</tt>. If the code size doesn't match the above cases,
	 * the method will return <tt>FALSE</tt>.
	 *
	 * <em>Note that this is not an exact procedure, there may be synonyms, so you are
	 * better off curating the data at the source</em>.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param string				$theCode			Country code.
	 *
	 * @static
	 * @return string				Full country code, <tt>NULL</tt> or <tt>FALSE</tt>.
	 */
	static function ResolveCountryCode( Wrapper $theWrapper, $theCode )
	{
		//
		// Init local storage.
		//
		$criteria = array( kTAG_NID => NULL );
		$collection
			= Term::ResolveCollection(
				Term::ResolveDatabase( $theWrapper ) );
		
		//
		// Parse by code size.
		//
		switch( strlen( $theCode ) )
		{
			case 2:
				$code = "iso:3166:1:alpha-2:$theCode";
				$criteria[ kTAG_NID ] = $code;
				if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
					return $code;													// ==>
				return NULL;														// ==>
			
			case 3:
				if( ctype_digit( $theCode ) )
				{
					$code = "iso:3166:1:numeric:$theCode";
					$criteria[ kTAG_NID ] = $code;
					if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
						return $code;												// ==>
					$code = "iso:3166:3:numeric:$theCode";
					$criteria[ kTAG_NID ] = $code;
					if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
						return $code;												// ==>
				}
				else
				{
					$code = "iso:3166:1:alpha-3:$theCode";
					$criteria[ kTAG_NID ] = $code;
					if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
						return $code;												// ==>
					$code = "iso:3166:3:alpha-3:$theCode";
					$criteria[ kTAG_NID ] = $code;
					if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
						return $code;												// ==>
				}
				return NULL;														// ==>
			
			case 4:
				$code = "iso:3166:3:alpha-4:$theCode";
				$criteria[ kTAG_NID ] = $code;
				if( $collection->matchOne( $criteria, kQUERY_COUNT ) )
					return $code;													// ==>
				return NULL;														// ==>
			
			default:
				return FALSE;														// ==>
		}
		
		return NULL;																// ==>
	
	} // ResolveCountryCode.

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before setting it
	 *
	 * In this class we cast the value of the namespace into a term reference, ensuring
	 * that if an object is provided this is a term.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_NAMESPACE
	 */
	protected function preOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method to resolve offset.
		//
		$ok = parent::preOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
		{
			//
			// Validate offsets.
			//
			switch( $theOffset )
			{
				case kTAG_MASTER:
				case kTAG_NAMESPACE:
					$this->validateReference(
						$theValue, kTYPE_REF_TERM, __class__, $theOffset );
					break;
			}
			
		} // Passed preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * In this class we set the {@link isInited()} status if the object has the local
	 * identifier and the label.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_ID_LOCAL kTAG_LABEL
	 *
	 * @uses isInited()
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method.
		//
		parent::postOffsetSet( $theOffset, $theValue );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_ID_LOCAL ) &&
						 ( \ArrayObject::offsetExists( kTAG_LABEL ) ||
						   \ArrayObject::offsetExists( kTAG_MASTER ) ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we reset the {@link isInited()} status if the object is missing the
	 * local identifier or the label.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_ID_LOCAL kTAG_LABEL
	 *
	 * @uses isInited()
	 */
	protected function postOffsetUnset( &$theOffset )
	{
		//
		// Call parent method.
		//
		parent::postOffsetUnset( $theOffset );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_ID_LOCAL ) &&
						 ( \ArrayObject::offsetExists( kTAG_LABEL ) ||
						   \ArrayObject::offsetExists( kTAG_MASTER ) ) );
	
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
	 * identifier generated by the {@link __toString()} method.
	 *
	 * @access protected
	 *
	 * @uses __toString()
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
 *							PROTECTED POST-DELETE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postDeleteTags																	*
	 *==================================================================================*/

	/**
	 * Handle object tags after commit
	 *
	 * In this class we shadow this method since we do not keep track of object tags.
	 *
	 * @param reference				$theTags			Property leaf tags.
	 *
	 * @access protected
	 *
	 * @uses updateReferenceCount()
	 */
	protected function postDeleteTags( &$theTags )										   {}

		

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
	 * In this class we ensure the object is initialised, {@link isInited()} and has the
	 * native identifier.
	 *
	 * @access protected
	 * @return Boolean				<tt>TRUE</tt> means ready.
	 *
	 * @see kTAG_NID
	 *
	 * @uses isInited()
	 */
	protected function isReady()
	{
		return ( parent::isReady()
			  && \ArrayObject::offsetExists( kTAG_NID ) );							// ==>
	
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
	 * In this class we return the {@link kTAG_NAMESPACE} and the {@link kTAG_ID_LOCAL}
	 * offsets.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_NAMESPACE kTAG_ID_LOCAL
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
							array( kTAG_NAMESPACE,
								   kTAG_ID_LOCAL ) );								// ==>
	
	} // lockedOffsets.

		

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
	 * We overload this method to add the {@link kTAG_NAMESPACE} and {@link kTAG_ID_LOCAL}
	 * offsets to the untracked offsets list, since these are set in the unit node
	 * attributes.
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
		
		//
		// Add namespace and local identifier to untracked.
		//
		$theUntracked = array_merge( $theUntracked,
									 array( kTAG_NAMESPACE, kTAG_ID_LOCAL ) );
		
		//
		// Traverse object.
		//
		$this->exportXMLStructure( $this, $unit, $theWrapper, $theUntracked );
	
	} // exportXMLObject.

	 
	/*===================================================================================
	 *	loadXML																			*
	 *==================================================================================*/

	/**
	 * Load from XML
	 *
	 * In this class we overload the inherited method to handle the {@link kTAG_NAMESPACE}
	 * and {@link kTAG_ID_LOCAL} offsets whose data is found in the root node attributes.
	 *
	 * @param SimpleXMLElement		$theContainer		Export container (unit).
	 *
	 * @access public
	 */
	public function loadXML( \SimpleXMLElement $theContainer )
	{
		//
		// Load namespace.
		//
		if( $theContainer[ kIO_XML_ATTR_NAMESPACE ] !== NULL )
			$this[ kTAG_NAMESPACE ]
				= (string) $theContainer[ kIO_XML_ATTR_NAMESPACE ];
	
		//
		// Load local identifier.
		//
		if( $theContainer[ kIO_XML_ATTR_ID_LOCAL ] !== NULL )
			$this[ kTAG_ID_LOCAL ]
				= (string) $theContainer[ kIO_XML_ATTR_ID_LOCAL ];
		
		//
		// Load other data.
		//
		parent::loadXML( $theContainer );
	
	} // loadXML.

	
	/*===================================================================================
	 *	xmlUnitElement																	*
	 *==================================================================================*/

	/**
	 * Return XML unit element
	 *
	 * In this class we return the <tt>TERM</tt> element.
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	protected function xmlUnitElement( \SimpleXMLElement $theRoot )
	{
		//
		// Create element.
		//
		$element = parent::xmlUnitElement( $theRoot )->addChild( kIO_XML_META_TERM );
		
		//
		// Set namespace.
		//
		if( $this->offsetExists( kTAG_NAMESPACE ) )
			$element->addAttribute( kIO_XML_ATTR_NAMESPACE,
									$this->offsetGet( kTAG_NAMESPACE ) );
		
		//
		// Set local identifier.
		//
		if( $this->offsetExists( kTAG_ID_LOCAL ) )
			$element->addAttribute( kIO_XML_ATTR_ID_LOCAL,
									$this->offsetGet( kTAG_ID_LOCAL ) );
		
		//
		// Set native identifier.
		//
		if( $this->offsetExists( kTAG_NID ) )
			$element->addAttribute( kIO_XML_ATTR_ID_PERSISTENT,
									$this->offsetGet( kTAG_NID ) );
		
		return $element;															// ==>
	
	} // xmlUnitElement.

		

/*=======================================================================================
 *																						*
 *								PROTECTED GRAPH UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	createGraphNode																	*
	 *==================================================================================*/

	/**
	 * Create graph node
	 *
	 * This class does not handle graph nodes directly, it is through nodes that terms are
	 * stored in the graph.
	 *
	 * @param DatabaseGraph			$theGraph			Graph connection.
	 *
	 * @access protected
	 * @return mixed				Node identifier, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 */
	protected function createGraphNode( DatabaseGraph $theGraph )		{	return FALSE;	}

	 

} // class Term.


?>
