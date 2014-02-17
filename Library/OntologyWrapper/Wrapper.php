<?php

/**
 * Wrapper.php
 *
 * This file contains the definition of the {@link Wrapper} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Tag;
use OntologyWrapper\Term;
use OntologyWrapper\Node;
use OntologyWrapper\Edge;
use OntologyWrapper\Dictionary;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseObject;
use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *										Wrapper.php										*
 *																						*
 *======================================================================================*/

/**
 * Tags.
 *
 * This file contains the default tag definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

/**
 * Types.
 *
 * This file contains the default data type definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Types.inc.php" );

/**
 * Tokens.
 *
 * This file contains the default token definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Tokens.inc.php" );

/**
 * Wrapper
 *
 * This class extends its ancestor to wrap an interface around the various components of the
 * system; the metadata, entities and the units.
 *
 * The object is considered {@link isInited()} when the metadata, entities and units
 * databases are set.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/02/2014
 */
class Wrapper extends Dictionary
{
	/**
	 * Status trait.
	 *
	 * In this class we handle the {@link isDirtyFlag()}
	 */
	use	traits\Status;

	/**
	 * Metadata.
	 *
	 * This data member holds the metadata {@link DatabaseObject} derived instance.
	 *
	 * @var DatabaseObject
	 */
	protected $mMetadata = NULL;

	/**
	 * Entities.
	 *
	 * This data member holds the entities {@link DatabaseObject} derived instance.
	 *
	 * @var DatabaseObject
	 */
	protected $mEntities = NULL;

	/**
	 * Units.
	 *
	 * This data member holds the units {@link DatabaseObject} derived instance.
	 *
	 * @var DatabaseObject
	 */
	protected $mUnits = NULL;

		

/*=======================================================================================
 *																						*
 *								PUBLIC CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Metadata																		*
	 *==================================================================================*/

	/**
	 * Manage metadata database
	 *
	 * This method can be used to manage the <i>metadata database</i>, it accepts a
	 * parameter which represents either the metadata database instance or the requested
	 * operation, depending on its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>FALSE</tt>: Delete the current value.
	 *	<li><tt>{@link DatabaseObject}</tt>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * The second parameter is a boolean which if <tt>TRUE</tt> will return the <i>old</i>
	 * value when replacing or resetting; if <tt>FALSE</tt>, it will return the current
	 * value.
	 *
	 * @param mixed					$theValue			Metadata database or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access public
	 * @return mixed				<i>New</i> or <i>old</i> metadata database.
	 *
	 * @throws Exception
	 *
	 * @see $mMetadata
	 *
	 * @uses manageProperty()
	 * @uses isInited()
	 * @uses isReady()
	 */
	public function Metadata( $theValue = NULL, $getOld = FALSE, $doOpen = TRUE )
	{
		//
		// Check metadata type.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE) )
		{
			//
			// Check data type.
			//
			if( ! ($theValue instanceof DatabaseObject) )
				throw new \Exception(
					"Invalid metadata database type." );						// !@! ==>
			
			//
			// Set dictionary.
			//
			$theValue->dictionary( $this );
			
			//
			// Open connection.
			//
			if( $doOpen )
				$theValue->openConnection();
		
		} // Setting new value.
		
		//
		// Manage member.
		//
		$save = $this->manageProperty( $this->mMetadata, $theValue, $getOld );
		
		//
		// Set inited status.
		//
		$this->isInited( $this->isReady() );
		
		return $save;																// ==>
	
	} // Metadata.

	 
	/*===================================================================================
	 *	Entities																		*
	 *==================================================================================*/

	/**
	 * Manage entities database
	 *
	 * This method can be used to manage the <i>entities database</i>, it accepts a
	 * parameter which represents either the entities database instance or the requested
	 * operation, depending on its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>FALSE</tt>: Delete the current value.
	 *	<li><tt>{@link DatabaseObject}</tt>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * The second parameter is a boolean which if <tt>TRUE</tt> will return the <i>old</i>
	 * value when replacing or resetting; if <tt>FALSE</tt>, it will return the current
	 * value.
	 *
	 * @param mixed					$theValue			Entities database or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access public
	 * @return mixed				<i>New</i> or <i>old</i> entities database.
	 *
	 * @throws Exception
	 *
	 * @see $mEntities
	 *
	 * @uses manageProperty()
	 * @uses isInited()
	 * @uses isReady()
	 */
	public function Entities( $theValue = NULL, $getOld = FALSE, $doOpen = TRUE )
	{
		//
		// Check entities type.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE) )
		{
			//
			// Check data type.
			//
			if( ! ($theValue instanceof DatabaseObject) )
				throw new \Exception(
					"Invalid entities database type." );						// !@! ==>
			
			//
			// Set dictionary.
			//
			$theValue->dictionary( $this );
			
			//
			// Open connection.
			//
			if( $doOpen )
				$theValue->openConnection();
		
		} // Setting new value.
		
		//
		// Manage member.
		//
		$save = $this->manageProperty( $this->mEntities, $theValue, $getOld );
		
		//
		// Set inited status.
		//
		$this->isInited( $this->isReady() );
		
		return $save;																// ==>
	
	} // Entities.

	 
	/*===================================================================================
	 *	Units																			*
	 *==================================================================================*/

	/**
	 * Manage units database
	 *
	 * This method can be used to manage the <i>units database</i>, it accepts a
	 * parameter which represents either the units database instance or the requested
	 * operation, depending on its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>FALSE</tt>: Delete the current value.
	 *	<li><tt>{@link DatabaseObject}</tt>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * The second parameter is a boolean which if <tt>TRUE</tt> will return the <i>old</i>
	 * value when replacing or resetting; if <tt>FALSE</tt>, it will return the current
	 * value.
	 *
	 * @param mixed					$theValue			Units database or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access public
	 * @return mixed				<i>New</i> or <i>old</i> units database.
	 *
	 * @throws Exception
	 *
	 * @see $mUnits
	 *
	 * @uses manageProperty()
	 * @uses isInited()
	 * @uses isReady()
	 */
	public function Units( $theValue = NULL, $getOld = FALSE, $doOpen = TRUE )
	{
		//
		// Check units type.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE) )
		{
			//
			// Check data type.
			//
			if( ! ($theValue instanceof DatabaseObject) )
				throw new \Exception(
					"Invalid units database type." );						// !@! ==>
			
			//
			// Set dictionary.
			//
			$theValue->dictionary( $this );
			
			//
			// Open connection.
			//
			if( $doOpen )
				$theValue->openConnection();
		
		} // Setting new value.
		
		//
		// Manage member.
		//
		$save = $this->manageProperty( $this->mUnits, $theValue, $getOld );
		
		//
		// Set inited status.
		//
		$this->isInited( $this->isReady() );
		
		return $save;																// ==>
	
	} // Units.

		

/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isConnected																		*
	 *==================================================================================*/

	/**
	 * Check if object is connected
	 *
	 * This method returns a boolean flag indicating whether the object is connected or not.
	 * In practice this is true if the metadata, entities and units connections are open.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 *
	 * @uses isInited()
	 *
	 * @see $mMetadata $mEntities $mUnits
	 */
	public function isConnected()
	{
		return ( $this->isInited()
			  && $this->mMetadata->isConnected()
			  && $this->mEntities->isConnected()
			  && $this->mUnits->isConnected() );									// ==>
	
	} // isConnected.

		
	/*===================================================================================
	 *	openConnections																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * This method can be used to connect the object's databases.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses isInited()
	 *
	 * @see $mMetadata $mEntities $mUnits
	 */
	public function openConnections()
	{
		//
		// Check if connected.
		//
		if( ! $this->isConnected() )
		{
			//
			// Check connections.
			//
			if( ! $this->isInited() )
				throw new \Exception(
					"Unable to open connections: "
				   ."missing required connections." );							// !@! ==>
		
			//
			// Open metadata.
			//
			$this->mMetadata->openConnection();
		
			//
			// Open entities.
			//
			$this->mEntities->openConnection();
		
			//
			// Open units.
			//
			$this->mUnits->openConnection();
		
			//
			// Set clean status.
			//
			$this->isDirty( FALSE );
		
		} // Not connected yet.
	
	} // openConnections.

	 
	/*===================================================================================
	 *	closeConnections																*
	 *==================================================================================*/

	/**
	 * Close connection
	 *
	 * This method can be used to close the object's database connections.
	 *
	 * @access public
	 *
	 * @uses isConnected()
	 *
	 * @see $mMetadata $mEntities $mUnits
	 */
	public function closeConnections()
	{
		//
		// Check connection.
		//
		if( $this->isConnected() )
		{
			//
			// Open metadata.
			//
			$this->mMetadata->closeConnection();
		
			//
			// Open entities.
			//
			$this->mEntities->closeConnection();
		
			//
			// Open units.
			//
			$this->mUnits->closeConnection();
		
			//
			// Set clean status.
			//
			$this->isDirty( FALSE );
		
		} // Was open.
	
	} // closeConnections.

		

/*=======================================================================================
 *																						*
 *							PUBLIC INITIALISATION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resetOntology																	*
	 *==================================================================================*/

	/**
	 * Reset databases
	 *
	 * This method can be used to reset the ontology, it will <b>erase the current
	 * ontology</em>, and re-load it from the files in the standards directory.
	 *
	 * <b><em>When you erase the ontology, you might lose tags and terms which are necessary
	 * for the entities and the data databases: be aware that by doing so you might render
	 * these databases useless</em></b>.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function resetOntology()
	{
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to reset ontology: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Reset the tag cache.
		//
		$this->dictionaryFlush();
		
		//
		// Drop the ontology database.
		//
		$this->mMetadata->drop();
		
		//
		// Load XML files.
		//
		$this->loadXMLFile( kPATH_STANDARDS_ROOT.'/default/Namespaces.xml' );
		$this->loadXMLFile( kPATH_STANDARDS_ROOT.'/default/Types.xml' );
		$this->loadXMLFile( kPATH_STANDARDS_ROOT.'/default/Tags.xml' );
	
	} // resetOntology.

	 
	/*===================================================================================
	 *	loadTagCache																	*
	 *==================================================================================*/

	/**
	 * Reload tag cache
	 *
	 * This method can be used to reset the tag cache.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadTagCache()
	{
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load tag cache: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Reset the tag cache.
		//
		$this->dictionaryFlush();
		
		//
		// Get tags collection.
		//
		$collection = $this->mMetadata->Collection( Tag::kSEQ_NAME );
		
		//
		// Load all tags.
		//
		$tags = $collection->getAll();
		foreach( $tags as $tag )
			$this->setTag( $tag, 0 );
	
	} // resetOntology.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PARSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadXMLFile																		*
	 *==================================================================================*/

	/**
	 * Load an XML file
	 *
	 * This method can be used to load an XML file containing metadata, entities or units.
	 * The method expects an XML file as the paraneter, any error during the load process
	 * will raise an exception.
	 *
	 * @param string				$theFile			XML file path.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadXMLFile( $theFile )
	{
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to parse file: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Check file.
		//
		$file = new \SplFileInfo( $theFile );
		if( ! $file->isFile() )
			throw new \Exception(
				"Unable to parse file [$theFile]: "
			   ."the provided file is not a file." );							// !@! ==>
		if( ! $file->isReadable() )
			throw new \Exception(
				"Unable to parse file [$theFile]: "
			   ."the provided file is not readable." );							// !@! ==>
		$theFile = $file->getRealPath();
		
		//
		// Load xml.
		//
		$xml = new \SimpleXMLElement( $theFile, NULL, TRUE );
		switch( $root = $xml->getName() )
		{
			case 'METADATA':
				$this->loadXMLMetadata( $xml );
				break;
		
			case 'ENTITIES':
				$this->loadXMLEntities( $xml );
				break;
		
			case 'UNITS':
				$this->loadXMLUnits( $xml );
				break;
			
			default:
				throw new \Exception(
					"Unable to parse file [$theFile]: "
				   ."invalid or unsupported root element [$root]." );			// !@! ==>
		
		} // Parsed root node.
	
	} // loadXMLFile.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PARSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadXMLMetadata																	*
	 *==================================================================================*/

	/**
	 * Parse and load metadata
	 *
	 * This method will parse and load the provided metadata XML structure.
	 *
	 * It is assumed that the object has its comnnections open and that the provided XML
	 * structure has the correct root element.
	 *
	 * @param SimpleXMLElement		$theXML				Metadata XML.
	 *
	 * @access protected
	 */
	protected function loadXMLMetadata( \SimpleXMLElement $theXML )
	{
		//
		// Iterate meta-blocks.
		//
		foreach( $theXML->{'META'} as $block )
			$this->loadXMLMetadataBlock( $block );
	
	} // loadXMLMetadata.

	 
	/*===================================================================================
	 *	loadXMLEntities																	*
	 *==================================================================================*/

	/**
	 * Parse and load entities
	 *
	 * This method will parse and load the provided entities XML structure.
	 *
	 * It is assumed that the object has its comnnections open and that the provided XML
	 * structure has the correct root element.
	 *
	 * @param SimpleXMLElement		$theXML				Entities XML.
	 *
	 * @access protected
	 */
	protected function loadXMLEntities( \SimpleXMLElement $theXML )
	{
	
	} // loadXMLEntities.

	 
	/*===================================================================================
	 *	loadXMLUnits																	*
	 *==================================================================================*/

	/**
	 * Parse and load entities
	 *
	 * This method will parse and load the provided entities XML structure.
	 *
	 * It is assumed that the object has its comnnections open and that the provided XML
	 * structure has the correct root element.
	 *
	 * @param SimpleXMLElement		$theXML				Entities XML.
	 *
	 * @access protected
	 */
	protected function loadXMLUnits( \SimpleXMLElement $theXML )
	{
	
	} // loadXMLUnits.

	 
	/*===================================================================================
	 *	loadXMLMetadataBlock															*
	 *==================================================================================*/

	/**
	 * Parse and load metadata block
	 *
	 * This method will parse and load the provided metadata transaction block.
	 *
	 * @param SimpleXMLElement		$theXML				Metadata transaction block.
	 *
	 * @access protected
	 */
	protected function loadXMLMetadataBlock( \SimpleXMLElement $theXML )
	{
		//
		// Iterate terms.
		//
		foreach( $theXML->{'TERM'} as $item )
			$this->loadXMLTerm( $item, $cache );
	
		//
		// Iterate tags.
		//
		foreach( $theXML->{'TAG'} as $item )
			$this->loadXMLTag( $item, $cache );
	
		//
		// Iterate nodes.
		//
		foreach( $theXML->{'NODE'} as $item )
			$this->loadXMLNode( $item, $cache );
	
		//
		// Iterate edges.
		//
		foreach( $theXML->{'EDGE'} as $item )
			$this->loadXMLEdge( $item, $cache );
	
	} // loadXMLMetadataBlock.

	 
	/*===================================================================================
	 *	loadXMLTerm																		*
	 *==================================================================================*/

	/**
	 * Parse and load XML term
	 *
	 * This method will parse and load the provided term XML structure.
	 *
	 * @param SimpleXMLElement		$theXML				Term XML structure.
	 * @param reference				$theCache			Objects cache.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function loadXMLTerm( \SimpleXMLElement $theXML, &$theCache )
	{
		//
		// Init cache.
		//
		if( ! is_array( $theCache ) )
			$theCache = Array();
		
		//
		// Check if updating.
		//
		$mod = isset( $theXML[ 'modify' ] );
		if( $mod )
			$id = $attributes[ 'modify' ];
		
		//
		// Instantiate term.
		//
		$object = ( $mod )
			  ? new Term( $this->Metadata(), $theXML[ 'modify' ] )
			  : new Term();
		
		//
		// Assert modifications.
		//
		if( $mod
		 && (! $object->isCommitted()) )
			throw new \Exception(
				"Unable to update term [$id]: "
			   ."the term does not exist." );									// !@! ==>
		
		//
		// Load attributes.
		//
		if( ! $mod )
		{
			$tmp = array( 'ns' => kTAG_NAMESPACE,
						  'lid' => kTAG_ID_LOCAL,
						  'pid' => kTAG_NID );
			foreach( $tmp as $key => $tag )
			{
				if( isset( $theXML[ $key ] ) )
					$object[ $tag ] = (string) $theXML[ $key ];
			}
		
		} // Not modifying.
		
		//
		// Load properties.
		//
		foreach( $theXML->{'item'} as $element )
		{
			//
			// Load property.
			//
			$this->loadXMLElement( $tag, $key, $value, $element );
			
			//
			// Set property.
			//
			if( $tag !== NULL )
				$object[ $tag ] = $value;
			else
				throw new \Exception(
					"Unable to set term property: "
				   ."the the property is missing its offset." );				// !@! ==>
		
		} // Iterating properties.
		
		//
		// Commit object.
		//
		$object->insert( $this );
		
		//
		// Load cache.
		//
		if( ! array_key_exists( Term::kSEQ_NAME, $theCache ) )
			$theCache[ Term::kSEQ_NAME ] = Array();
		$theCache[ Term::kSEQ_NAME ][ $object[ kTAG_NID ] ] = $object;
	
	} // loadXMLTerm.

	 
	/*===================================================================================
	 *	loadXMLTag																		*
	 *==================================================================================*/

	/**
	 * Parse and load XML tag
	 *
	 * This method will parse and load the provided tag XML structure.
	 *
	 * @param SimpleXMLElement		$theXML				Tag XML structure.
	 * @param reference				$theCache			Objects cache.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function loadXMLTag( \SimpleXMLElement $theXML, &$theCache )
	{
		//
		// Init cache.
		//
		if( ! is_array( $theCache ) )
			$theCache = Array();
		
		//
		// Instantiate tag.
		//
		$object = new Tag();
		
		//
		// Load properties.
		//
		foreach( $theXML->{'item'} as $element )
		{
			//
			// Load property.
			//
			$this->loadXMLElement( $tag, $key, $value, $element );
			
			//
			// Set property.
			//
			if( $tag !== NULL )
				$object[ $tag ] = $value;
			else
				throw new \Exception(
					"Unable to set tag property: "
				   ."the the property is missing its offset." );				// !@! ==>
		
		} // Iterating properties.
		
		//
		// Commit object.
		//
		$object->insert( $this );
		
		//
		// Load cache.
		//
		if( ! array_key_exists( Tag::kSEQ_NAME, $theCache ) )
			$theCache[ Tag::kSEQ_NAME ] = Array();
		$theCache[ Tag::kSEQ_NAME ][ $object[ kTAG_NID ] ] = $object;
	
	} // loadXMLTag.

	 
	/*===================================================================================
	 *	loadXMLElement																	*
	 *==================================================================================*/

	/**
	 * Parse and load XML element
	 *
	 * This method will parse and load the provided XML element and return the tag, the
	 * eventual array element key and the value in the provided references.
	 *
	 * This method is called recursively, it will first parse the attributes of the element
	 * determining the current tag, then it will traverse eventual embedded structures until
	 * a scalar value is found which will be fed to the {@link castXMLScalarValue()} method
	 * that will cast the value according to the most recent tag's data type.
	 *
	 * @param reference				$theTag				Receives tag identifier.
	 * @param reference				$theKey				Receives key identifier.
	 * @param reference				$theValue			Receives tag value.
	 * @param SimpleXMLElement		$theElement			XML element.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses castXMLScalarValue()
	 */
	protected function loadXMLElement( &$theTag, &$theKey, &$theValue,
									   \SimpleXMLElement $theElement )
	{
		//
		// Reset key.
		//
		$theKey = NULL;
		
		//
		// Handle language string.
		//
		if( isset( $theElement[ 'lang' ] ) )
		{
			//
			// Init value.
			//
			$theValue = Array();
			
			//
			// Set language.
			//
			if( strlen( (string) $theElement[ 'lang' ] ) )
				$theValue[ kTAG_LANGUAGE ] = (string) $theElement[ 'lang' ];
			
			//
			// Set text.
			//
			$theValue[ kTAG_TEXT ] = (string) $theElement;
		
		} // Language string property.
		
		//
		// Handle other properties.
		//
		else
		{
			//
			// Parse tag constant.
			//
			if( isset( $theElement[ 'const' ] ) )
				$theTag = $theKey = constant( (string) $theElement[ 'const' ] );

			//
			// Parse persistent identifier.
			//
			elseif( isset( $theElement[ 'pid' ] ) )
				$theTag = $theKey = (string) $theElement[ 'pid' ];

			//
			// Parse sequence number.
			//
			elseif( isset( $theElement[ 'tag' ] ) )
				$theTag = $theKey = (int) $theElement[ 'tag' ];

			//
			// Parse array element key.
			//
			elseif( isset( $theElement[ 'key' ] ) )
				$theKey = (string) $theElement[ 'key' ];
		
			//
			// Handle array.
			//
			if( count( $theElement->{'item'} ) )
			{
				//
				// Reset value.
				//
				$theValue = Array();
			
				//
				// Load elements.
				//
				foreach( $theElement->{'item'} as $element )
				{
					//
					// Load property.
					//
					$this->loadXMLElement( $theTag, $key, $value, $element );
			
					//
					// Set property.
					//
					if( $key === NULL )
						$theValue[] = $value;
					else
						$theValue[ (string) $key ] = $value;
		
				} // Iterating properties.
		
			} // Array property.
		
			//
			// Handle scalar.
			//
			else
				$theValue = $this->castXMLScalarValue( $theElement, $theTag );
		
		} // Not a language string.
	
	} // loadXMLElement.

		

/*=======================================================================================
 *																						*
 *								PROTECTED CASTING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	castXMLScalarValue																*
	 *==================================================================================*/

	/**
	 * Cast an XML scalar value
	 *
	 * This method will cast the provided XML scalar value according to the current offset.
	 * The method accepts the tag reference and the XML element, it will return the cast
	 * value.
	 *
	 * Note that this method expects a scalar value, although the provided tag reference
	 * may refer to a list of values.
	 *
	 * This method will handle directly a series of default tags, this is necessary when
	 * loading the ontology for the first time, because tags cannot be resolved.
	 *
	 * @param SimpleXMLElement		$theElement			Element XML.
	 * @param integer				$theTag				Tag reference.
	 *
	 * @access protected
	 * @return mixed				Cast value.
	 *
	 * @uses OntologyObject::castOffsetValue()
	 */
	protected function castXMLScalarValue( \SimpleXMLElement $theElement, $theTag )
	{
		//
		// Get string value.
		//
		$value = (string) $theElement;
		
		//
		// Handle default tags.
		//
		switch( $theTag )
		{
			case kTAG_NID:
				return $value;														// ==>
			
			case kTAG_NAMESPACE:
			case kTAG_ID_LOCAL:
			case kTAG_ID_PERSISTENT:
			case kTAG_CLASS:
			case kTAG_CONN_PROTOCOL:
			case kTAG_CONN_HOST:
			case kTAG_CONN_USER:
			case kTAG_CONN_PASS:
			case kTAG_CONN_BASE:
			case kTAG_CONN_COLL:
				return (string) $value;												// ==>
		
			case kTAG_ID_SEQUENCE:
			case kTAG_CONN_PORT:
				return (int) $value;												// ==>
			
			case kTAG_TERMS:
			case kTAG_DATA_TYPE:
			case kTAG_DATA_KIND:
				return (string) $value;												// ==>
			
			default:
				OntologyObject::castOffsetValue( $value, $theTag, TRUE );
				break;
		}
		
		return $value;																// ==>
	
	} // loadXMLMetadata.

		

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
	 * This method returns a boolean flag indicating whether the object is ready to be
	 * connected, in practice, this is true if the object has the metadata, entities and
	 * units connections.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> is ready.
	 *
	 * @see $mMetadata $mEntities $mUnits
	 */
	protected function isReady()
	{
		return ( ($this->mMetadata !== NULL)
			  && ($this->mEntities !== NULL)
			  && ($this->mUnits !== NULL) );										// ==>
	
	} // isReady.

	 

} // class Wrapper.


?>
