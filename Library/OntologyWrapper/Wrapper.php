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
use OntologyWrapper\FAOInstitute;
use OntologyWrapper\Dictionary;
use OntologyWrapper\ServerObject;
use OntologyWrapper\DatabaseGraph;
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
 * Predicates.
 *
 * This file contains the default predicate definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Predicates.inc.php" );

/**
 * ISO definitions.
 *
 * This file contains the default ISO definitions.
 */
require_once( kPATH_STANDARDS_ROOT."/iso/iso.inc.php" );

/**
 * Import/Export API.
 *
 * This file contains the import/export API definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/ImportExport.xml.inc.php" );

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

	/**
	 * Graph.
	 *
	 * This data member holds the graph {@link DatabaseGraph} derived instance.
	 *
	 * @var DatabaseGraph
	 */
	protected $mGraph = NULL;

		

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
			// Set server dictionary.
			//
			$theValue->parent()->dictionary( $this );
			
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
		// Load dictionary.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE)
		 && (! $this->dictionaryFilled()) )
			$this->loadTagCache();
		
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

	 
	/*===================================================================================
	 *	Graph																			*
	 *==================================================================================*/

	/**
	 * Manage graph database
	 *
	 * This method can be used to manage the <i>graph database</i>, it accepts a parameter
	 * which represents either the graph database instance or the requested operation,
	 * depending on its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>FALSE</tt>: Delete the current value.
	 *	<li><tt>{@link DatabaseGraph}</tt>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * The second parameter is a boolean which if <tt>TRUE</tt> will return the <i>old</i>
	 * value when replacing or resetting; if <tt>FALSE</tt>, it will return the current
	 * value.
	 *
	 * @param mixed					$theValue			Hraph database or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access public
	 * @return mixed				<i>New</i> or <i>old</i> graph database.
	 *
	 * @throws Exception
	 *
	 * @see $mGraph
	 *
	 * @uses manageProperty()
	 * @uses isInited()
	 * @uses isReady()
	 */
	public function Graph( $theValue = NULL, $getOld = FALSE, $doOpen = TRUE )
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
			if( ! ($theValue instanceof DatabaseGraph) )
				throw new \Exception(
					"Invalid graph database type." );						// !@! ==>
			
			//
			// Open connection.
			//
			if( $doOpen )
				$theValue->openConnection();
		
		} // Setting new value.
		
		//
		// Manage member.
		//
		$save = $this->manageProperty( $this->mGraph, $theValue, $getOld );
		
		return $save;																// ==>
	
	} // Graph.

		

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
	 * @param boolean				$doLog				Log operations.
	 * @param boolean				$doDrop				Drop collections.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function resetOntology( $doLog = FALSE, $doDrop = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Resetting ontology.\n" );
		
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
		if( $doLog )
			echo( "  • Flushing data dictionary.\n" );
		$this->dictionaryFlush( 0 );
		
		//
		// Reset tags.
		//
		if( $doLog )
			echo( "  • Resetting tags.\n" );
		if( $doDrop )
			$this->mMetadata->collection( Tag::kSEQ_NAME, TRUE )->drop();
		Tag::CreateIndexes( $this->mMetadata );
		
		//
		// Reset terms.
		//
		if( $doLog )
			echo( "  • Resetting terms.\n" );
		if( $doDrop )
			$this->mMetadata->collection( Term::kSEQ_NAME, TRUE )->drop();
		Term::CreateIndexes( $this->mMetadata );
		
		//
		// Reset nodes.
		//
		if( $doLog )
			echo( "  • Resetting nodes.\n" );
		if( $doDrop )
			$this->mMetadata->collection( Node::kSEQ_NAME, TRUE )->drop();
		Node::CreateIndexes( $this->mMetadata );
		
		//
		// Reset edges.
		//
		if( $doLog )
			echo( "  • Resetting edges.\n" );
		if( $doDrop )
			$this->mMetadata->collection( Edge::kSEQ_NAME, TRUE )->drop();
		Edge::CreateIndexes( $this->mMetadata );
		
		//
		// Load XML base files.
		//
		if( $doLog )
			echo( "  • Loading XML term files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/default/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/Predicates.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/Types.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		//
		// Load XML tag files.
		//
		if( $doLog )
			echo( "  • Loading XML tag files.\n" );
		
		
		$file = kPATH_STANDARDS_ROOT.'/default/Tags.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Set tags sequence number.
		//
		$this->Metadata()->setSequenceNumber( Tag::kSEQ_NAME, kTAG_SEQUENCE_START );
		
		//
		// Load XML enumerated files.
		//
		if( $doLog )
			echo( "  • Loading XML type files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/default/DataTypes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/DataKinds.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/TermTypes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/NodeTypes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/EntityTypes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/EntityKinds.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/DomainTypes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/PredicateTypes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML schema files.
		//
		if( $doLog )
			echo( "  • Loading default XML schema files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/default/SchemaRecord.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/SchemaRefCount.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/SchemaObjectOffsets.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/SchemaUnit.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/SchemaOrganisation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/SchemaIndividual.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/SchemaEntity.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML structure files.
		//
		if( $doLog )
			echo( "  • Loading default XML structure files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/default/StructureTag.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
		$file = kPATH_STANDARDS_ROOT.'/default/StructureTerm.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
		$file = kPATH_STANDARDS_ROOT.'/default/StructureNode.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
		$file = kPATH_STANDARDS_ROOT.'/default/StructureEdge.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
		$file = kPATH_STANDARDS_ROOT.'/default/StructureEntity.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
	} // resetOntology.

	 
	/*===================================================================================
	 *	resetUnits																		*
	 *==================================================================================*/

	/**
	 * Reset units
	 *
	 * This method can be used to reset the units database, it will erase the current units
	 * collection and load the FAO insatitutes.
	 *
	 * The method will take care of setting the necessary indexes.
	 *
	 * @param boolean				$doLog				Log operations.
	 * @param boolean				$doDrop				Drop collections.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function resetUnits( $doLog = FALSE, $doDrop = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Resetting units.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to reset entities: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Reset units collection.
		//
		if( $doLog )
			echo( "  • Resetting collection.\n" );
		if( $doDrop )
			$this->mUnits->collection( User::kSEQ_NAME, TRUE )->drop();
		
		//
		// Create units collection entity indexes.
		//
		if( $doLog )
			echo( "  • Creating unit entity indexes.\n" );
		Individual::CreateIndexes( $this->mUnits );
		Institution::CreateIndexes( $this->mUnits );
		
		return NULL;																// ==>
	
	} // resetUnits.

	 
	/*===================================================================================
	 *	resetEntities																	*
	 *==================================================================================*/

	/**
	 * Reset entities
	 *
	 * This method can be used to reset the entities database, it will erase the current
	 * entities collection and load the FAO insatitutes.
	 *
	 * The method will take care of setting the necessary indexes.
	 *
	 * @param boolean				$doLog				Log operations.
	 * @param boolean				$doDrop				Drop collections.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function resetEntities( $doLog = FALSE, $doDrop = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Resetting entities.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to reset entities: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Reset entity collection.
		//
		if( $doLog )
			echo( "  • Resetting collection.\n" );
		if( $doDrop )
			$this->mEntities->collection( User::kSEQ_NAME, TRUE )->drop();
		User::CreateIndexes( $this->mEntities );
		
		return NULL;																// ==>
	
	} // resetEntities.

	 
	/*===================================================================================
	 *	loadTagCache																	*
	 *==================================================================================*/

	/**
	 * Reload tag cache
	 *
	 * This method can be used to reset the tag cache.
	 *
	 * The method will return <tt>TRUE</tt> if the object is connected and the operation was
	 * executed and <tt>FALSE</tt> if the object is not connected, or if the metadata is
	 * missing.
	 *
	 * Note that although the operation might have been executed, this doesn't mean that the
	 * dictionary was loaded: this depends on the contents of the metadata database.
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
		if( $this->isConnected()
		 && ($this->mMetadata !== NULL) )
		{
			//
			// Open metadata conection.
			//
			$this->mMetadata->openConnection();
		
			//
			// Reset the tag cache.
			//
			$this->dictionaryFlush( 0 );
		
			//
			// Set dictionary.
			//
			$this->setTagsByIterator(
				new MongoIterator(
					$this->mMetadata->collection( Tag::kSEQ_NAME )
						->getAll( $this->getTagOffsets() ),
					$this->mMetadata->collection( Tag::kSEQ_NAME ),
					Array() ),
				0 );
			
			return TRUE;															// ==>
		
		} // Connected with metadata.
		
		return FALSE;																// ==>
	
	} // resetOntology.

		

/*=======================================================================================
 *																						*
 *							PUBLIC ONTOLOGY UTILITIES INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	copyRelationships																*
	 *==================================================================================*/

	/**
	 * <h4>Copy relationships from one node to another</h4>
	 *
	 * This method can be used to copy all the relationships of a given predicate affecting
	 * one node to another node. The method will select the source node relationships of the
	 * provided predicate and direction, it will duplicate these relationships, it will
	 * substitute the source node with the destination node and replace the source predicate
	 * with the destination predicate.
	 *
	 * The method expects these parameters:
	 *
	 * <ul>
	 *	<li><b>$theSrcNode</b>: Source node or list of node native identifiers. This
	 *		represents the original relationships vertex.
	 *	<li><b>$theSrcPredicate</b>: Source relationship predicate term native identifier
	 *		or list.
	 *	<li><b>$theSrcDirection</b>: Source relationship direction:
	 *	 <ul>
	 *		<li><tt>{@link kTYPE_RELATIONSHIP_IN}</tt>: All relationships pointing to the
	 *			source node.
	 *		<li><tt>{@link kTYPE_RELATIONSHIP_OUT}</tt>: All relationships originating from
	 *			the source node.
	 *		<li><tt>{@link kTYPE_RELATIONSHIP_ALL}</tt>: Both of the above.
	 *	 </ul>
	 * </ul>
	 *	<li><b>$theDstNode</b>: Destination node native identifier.
	 *	<li><b>$theDstPredicate</b>: Destination relationship predicate term native
	 *		identifier.
	 *	<li><b>$theDstDirection</b>: Destination relationship direction:
	 *	 <ul>
	 *		<li><tt>{@link kTYPE_RELATIONSHIP_IN}</tt>: All relationships will point to the
	 *			destination node.
	 *		<li><tt>{@link kTYPE_RELATIONSHIP_OUT}</tt>: All relationships will originate
	 *			from the destination node.
	 *		<li><tt>{@link kTYPE_RELATIONSHIP_ALL}</tt>: The vertex holding the source node
	 *			will be replaced with the destination node.
	 *	 </ul>
	 * </ul>
	 *
	 * The created {@link Edge} objects will be set exclusively with the predicate and
	 * vertices.
	 *
	 * If there will be resulting duplicate edges, the method will raise an exception.
	 *
	 * @param mixed					$theSrcNode			Source relationship vertex(s).
	 * @param mixed					$theSrcPredicate	Source predicate(s).
	 * @param string				$theSrcDirection	Source relationship direction.
	 * @param int					$theDstNode			Destination relationship vertex.
	 * @param string				$theDstPredicate	Destination predicate.
	 * @param string				$theDstDirection	Destination relationship direction.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function copyRelationships( $theSrcNode, $theSrcPredicate, $theSrcDirection,
									   $theDstNode, $theDstPredicate, $theDstDirection )
	{
		//
		// Handle source vertex list.
		//
		if( is_array( $theSrcNode ) )
		{
			foreach( $theSrcNode as $node )
				$this->CopyRelationships(
					$node, $theSrcPredicate, $theSrcDirection,
					$theDstNode, $theDstPredicate, $theDstDirection );
		
		} // List.
		
		//
		// Handle single standard.
		//
		else
		{
			//
			// Normalise source predicate.
			//
			if( ! is_array( $theSrcPredicate ) )
				$theSrcPredicate = array( $theSrcPredicate );
			
			//
			// Resolve edges collection.
			//
			$collection
				= Edge::ResolveCollection(
					Edge::ResolveDatabase( $this, TRUE ) );
			
			//
			// Set criteria.
			//
			switch( $theSrcDirection )
			{
				case kTYPE_RELATIONSHIP_IN:
					$criteria = array( kTAG_OBJECT => (int) $theSrcNode );
					break;
				
				case kTYPE_RELATIONSHIP_OUT:
					$criteria = array( kTAG_SUBJECT => (int) $theSrcNode );
					break;
				
				case kTYPE_RELATIONSHIP_ALL:
					$criteria
						= array( '$or' => array(
							array( kTAG_SUBJECT => (int) $theSrcNode ),
							array( kTAG_OBJECT => (int) $theSrcNode ) ) );
					break;
				
				default:
					throw new Exception
						( "Invalid source relationship direction" );			// !@! ==>
			
			} // Parsed direction.
			
			//
			// Add predicates filter.
			//
			$criteria[ kTAG_PREDICATE ] = array( '$in' => $theSrcPredicate );
			
			//
			// Iterate source relationships.
			//
			$rs = $collection->matchAll( $criteria, kQUERY_ARRAY );
			foreach( $rs as $source )
			{
				//
				// Instantiate object.
				//
				$object = new Edge( $this );
				
				//
				// Init object data.
				//
				$object[ kTAG_SUBJECT ] = $source[ kTAG_SUBJECT ];
				$object[ kTAG_PREDICATE ] = $theDstPredicate;
				$object[ kTAG_OBJECT ] = $source[ kTAG_OBJECT ];
				
				//
				// Set vertex.
				//
				switch( $theDstDirection )
				{
					case kTYPE_RELATIONSHIP_IN:
						$object[ kTAG_OBJECT ] = (int) $theDstNode;
						break;
				
					case kTYPE_RELATIONSHIP_OUT:
						$object[ kTAG_SUBJECT ] = (int) $theDstNode;
						break;
				
					case kTYPE_RELATIONSHIP_ALL:
						if( $source[ kTAG_SUBJECT ] == (int) $theSrcNode )
							$object[ kTAG_SUBJECT ] = (int) $theDstNode;
						else
							$object[ kTAG_OBJECT ] = (int) $theDstNode;
						break;
				
					default:
						throw new Exception
							( "Invalid destination relationship direction" );	// !@! ==>
			
				} // Parsed direction.
				
				//
				// Commit object.
				//
				$object->commit();
			
			} // Iterating source relationships.
		
		} // Scalar.

	} // copyRelationships.

		

/*=======================================================================================
 *																						*
 *						PUBLIC STANDARDS INITIALISATION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadISOStandards																*
	 *==================================================================================*/

	/**
	 * Load ISO standards
	 *
	 * This method can be used to load the ISO standards.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadISOStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading ISO standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load ISO: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load default XML files.
		//
		if( $doLog )
			echo( "  • Loading default XML files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/iso/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/Tags.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/ISO639.types.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/ISO639.tags.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/ISO3166.types.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/ISO3166.tags.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/ISO4217.types.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/ISO15924.types.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load generated ISO639 XML files.
		//
		if( $doLog )
			echo( "  • Loading generated ISO639 XML files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/iso/iso639-3.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/iso/iso639-2.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso639-1.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso639-2B.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso639-2T.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso639-5.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load cross references.
		//
		$file = kPATH_STANDARDS_ROOT.'/iso/iso639-xref.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load generated ISO3166 XML files.
		//
		if( $doLog )
			echo( "  • Loading generated ISO3166 XML files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso3166-1-alpha3.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso3166-1-alpha2.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso3166-1-numeric.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso3166-2.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso3166-3-alpha3.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso3166-3-alpha4.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso3166-3-numeric.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/iso/iso3166-xref.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		//
		// Load XML location files.
		//
		if( $doLog )
			echo( "  • Loading location XML files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/default/iso-locations-country.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso3166-2-subset.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/default/iso-locations-subset.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		//
		// Load XML location region cross reference files.
		//
		$file = kPATH_STANDARDS_ROOT.'/iso/ISO3166-regions.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load generated ISO4217 XML files.
		//
		if( $doLog )
			echo( "  • Loading generated ISO4217 XML files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso4217-A-alpha.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso4217-A-numeric.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso4217-H-alpha.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso4217-H-numeric.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso4217-xref.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load generated ISO15924 XML files.
		//
		if( $doLog )
			echo( "  • Loading generated ISO15924 XML files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso15924-alpha4.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso15924-numeric.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/iso/iso15924-xref.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadISOStandards.

	 
	/*===================================================================================
	 *	loadWBIStandards																*
	 *==================================================================================*/

	/**
	 * Load WBI standards
	 *
	 * This method can be used to load the WBI standards.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadWBIStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading WBI standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load WBI: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load default XML files.
		//
		if( $doLog )
			echo( "  • Loading default WBI files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/wbi/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/wbi/Tags.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/wbi/WBI-income.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/wbi/WBI-lending.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load generated WBI XML files.
		//
		if( $doLog )
			echo( "  • Loading generated WBI XML files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/wbi/WBI-xref.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadWBIStandards.

	 
	/*===================================================================================
	 *	loadIUCNStandards																*
	 *==================================================================================*/

	/**
	 * Load IUCN standards
	 *
	 * This method can be used to load the IUCN standards in the metadata database, it will
	 * load the standards XML files.
	 *
	 * Note that this method expects other standards to have been loaded.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function loadIUCNStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading IUCN standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load IUCN: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load XML namespace files.
		//
		if( $doLog )
			echo( "  • Loading XML namespace files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/iucn/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML attribute files.
		//
		if( $doLog )
			echo( "  • Loading XML attribute files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/iucn/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/iucn/Types.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/iucn/Tags.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadIUCNStandards.

	 
	/*===================================================================================
	 *	loadNatServeStandards															*
	 *==================================================================================*/

	/**
	 * Load NatureServe standards
	 *
	 * This method can be used to load the NatureServe standards in the metadata database,
	 * it will load the standards XML files.
	 *
	 * Note that this method expects other standards to have been loaded.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function loadNatServeStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading NatureServe standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load NaturalServe: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load XML namespace files.
		//
		if( $doLog )
			echo( "  • Loading XML namespace files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/natserve/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML attribute files.
		//
		if( $doLog )
			echo( "  • Loading XML attribute files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/natserve/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadNatServeStandards.

	 
	/*===================================================================================
	 *	loadFAOStandards																*
	 *==================================================================================*/

	/**
	 * Load FAO standards
	 *
	 * This method can be used to load the FAO standards in the metadata database, it will
	 * load the standards XML files.
	 *
	 * Note that this method expects other standards to have been loaded.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function loadFAOStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading FAO standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load FAO: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load XML namespace files.
		//
		if( $doLog )
			echo( "  • Loading XML namespace files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/fao/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML attribute files.
		//
		if( $doLog )
			echo( "  • Loading XML attribute files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/fao/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadFAOStandards.

	 
	/*===================================================================================
	 *	loadEECStandards																*
	 *==================================================================================*/

	/**
	 * Load EEC standards
	 *
	 * This method can be used to load the EEC standards in the metadata database, it will
	 * load the standards XML files.
	 *
	 * Note that this method expects other standards to have been loaded.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function loadEECStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading EEC standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load EEC: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load XML namespace files.
		//
		if( $doLog )
			echo( "  • Loading XML namespace files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/eec/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML attribute files.
		//
		if( $doLog )
			echo( "  • Loading XML attribute files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/eec/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadEECStandards.

	 
	/*===================================================================================
	 *	loadGENSStandards																*
	 *==================================================================================*/

	/**
	 * Load GENS standards
	 *
	 * This method can be used to load the GENS standards in the metadata database, it will
	 * load the standards XML files.
	 *
	 * Note that this method expects other standards to have been loaded.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function loadGENSStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading GENS standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load GENS: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load XML namespace files.
		//
		if( $doLog )
			echo( "  • Loading XML namespace files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/gens/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML attribute files.
		//
		if( $doLog )
			echo( "  • Loading XML attribute files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/gens/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadGENSStandards.

	 
	/*===================================================================================
	 *	loadGLOBCOVStandards															*
	 *==================================================================================*/

	/**
	 * Load GLOBCOV standards
	 *
	 * This method can be used to load the GLOBCOV standards in the metadata database, it
	 * will load the standards XML files.
	 *
	 * Note that this method expects other standards to have been loaded.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function loadGLOBCOVStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading GLOBCOV standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load GLOBCOV: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load XML namespace files.
		//
		if( $doLog )
			echo( "  • Loading XML namespace files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/globcov/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML attribute files.
		//
		if( $doLog )
			echo( "  • Loading XML attribute files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/globcov/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadGLOBCOVStandards.

	 
	/*===================================================================================
	 *	loadHWSDStandards																*
	 *==================================================================================*/

	/**
	 * Load HWSD standards
	 *
	 * This method can be used to load the HWSD standards in the metadata database, it
	 * will load the standards XML files.
	 *
	 * Note that this method expects other standards to have been loaded.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function loadHWSDStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading HWSD standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load HWSD: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load XML namespace files.
		//
		if( $doLog )
			echo( "  • Loading XML namespace files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/hwsd/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML attribute files.
		//
		if( $doLog )
			echo( "  • Loading XML attribute files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/hwsd/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadHWSDStandards.

	 
	/*===================================================================================
	 *	loadMCPDStandards																*
	 *==================================================================================*/

	/**
	 * Load Multicrop Passport Descriptors standards
	 *
	 * This method can be used to load the MCPD standards.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadMCPDStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading MCPD standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load MCPD: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load MCPD namespace XML files.
		//
		if( $doLog )
			echo( "  • Loading MCPD files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/mcpd/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/mcpd/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadMCPDStandards.

	 
	/*===================================================================================
	 *	loadFCUStandards																*
	 *==================================================================================*/

	/**
	 * Load Forest Fene Conservation Unit standards
	 *
	 * This method can be used to load the FCU standards.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadFCUStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading FCU standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load FCU: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load namespace XML files.
		//
		if( $doLog )
			echo( "  • Loading FCU files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/fcu/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/fcu/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadFCUStandards.

	 
	/*===================================================================================
	 *	loadCWRStandards																*
	 *==================================================================================*/

	/**
	 * Load crop wild relative standards
	 *
	 * This method can be used to load the CWR standards.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadCWRStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading CWR standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load CWR: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load namespace XML files.
		//
		if( $doLog )
			echo( "  • Loading CWR namespace files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/cwr/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load attribute XML files.
		//
		if( $doLog )
			echo( "  • Loading CWR attribute files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/cwr/CommonAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/cwr/ChecklistAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/cwr/InventoryAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/cwr/PopulationAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadCWRStandards.

	 
	/*===================================================================================
	 *	loadABDHStandards																*
	 *==================================================================================*/

	/**
	 * Load agro bio-diversity household assessment standards
	 *
	 * This method can be used to load the ABDH standards.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadABDHStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading ABDH standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load ABDH: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load namespace XML files.
		//
		if( $doLog )
			echo( "  • Loading ABDH namespace files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/abdh/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load attribute XML files.
		//
		if( $doLog )
			echo( "  • Loading ABDH attribute files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/abdh/HouseholdAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/RespondentAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/SpeciesAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/AnnualPlantsAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/PerennialPlantsAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/WildPlantsAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/DomesticatedAnimalAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/EconomicAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/MarketAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/SocialAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/abdh/RiskAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadABDHStandards.

	 
	/*===================================================================================
	 *	loadLRStandards																	*
	 *==================================================================================*/

	/**
	 * Load landrace standards
	 *
	 * This method can be used to load the LR standards.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadLRStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading LR standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load LR: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load namespace XML files.
		//
		if( $doLog )
			echo( "  • Loading LR namespace files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/lr/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load attribute XML files.
		//
		if( $doLog )
			echo( "  • Loading LR attribute files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/lr/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load type XML files.
		//
		if( $doLog )
			echo( "  • Loading LR type files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumCollectorRisk.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumConservation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumContinuity.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumDemand.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumDestination.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumDistribution.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumFarmerRisk.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumMotivation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumPart.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumPeriod.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumSelection.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumStatus.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumSystem.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumTenancy.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/lr/EnumUse.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load tag XML files.
		//
		if( $doLog )
			echo( "  • Loading LR tag files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/lr/Tags.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadLRStandards.

	 
	/*===================================================================================
	 *	loadQTLStandards																*
	 *==================================================================================*/

	/**
	 * Load QTL standards
	 *
	 * This method can be used to load the QTL standards.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadQTLStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading QTL standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load QTL: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load namespace XML files.
		//
		if( $doLog )
			echo( "  • Loading QTL namespace files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/qtl/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load attribute XML files.
		//
		if( $doLog )
			echo( "  • Loading QTL attribute files.\n" );

		$file = kPATH_STANDARDS_ROOT.'/qtl/Attributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
	
	} // loadQTLStandards.

	 
	/*===================================================================================
	 *	loadStandards																	*
	 *==================================================================================*/

	/**
	 * Load standards
	 *
	 * This method can be used to load the standards in the metadata database, it will
	 * load the standards XML files.
	 *
	 * Note that this method expects other standards to have been loaded.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function loadStandards( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading standards.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load standards: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load standards additions files.
		//
		if( $doLog )
			echo( "  • Loading XML additions files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/Additions_ISO.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML namespace files.
		//
		if( $doLog )
			echo( "  • Loading XML namespace files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/Categories.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML base attribute files.
		//
		if( $doLog )
			echo( "  • Loading XML attribute files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/InventoryAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/LocationAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/SiteAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/TaxonomyAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/GermplasmAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/MissionAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/CollectingMissionAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/CollectingEventAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/BreedingEventAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/CrossabilityAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/PrecipitationAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/TemperatureAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/ClimaticAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/EnvironmentalAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/standard/TrialAttributes.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Create species name index for terms.
		//
		if( $doLog )
			echo( "  • Creating species name index for terms.\n" );
		
		$included = (string) $this->getSerial( ':taxon:group:taxa', TRUE );
		$excluded = (string) $this->getSerial( ':taxon:group:taxa:excluded', TRUE );
		$collection
			= Term::ResolveCollection(
				Term::ResolveDatabase( $this ) );
		
		$collection
			->createIndex(
				array( $included => 1 ),
				array( "name" => "TAXA_INCLUDED",
					   "sparse" => TRUE ) );
		
		$collection
			->createIndex(
				array( $excluded => 1 ),
				array( "name" => "TAXA_EXCLUDED",
					   "sparse" => TRUE ) );
	
	} // loadStandards.

	 
	/*===================================================================================
	 *	loadCollections																	*
	 *==================================================================================*/

	/**
	 * Load collections
	 *
	 * This method can be used to load the standards collection elements in the metadata
	 * database, it will load the collection XML files.
	 *
	 * Note that this method expects other standards to have been loaded, so it must be
	 * called as the last element.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function loadCollections( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading collections.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to load collections: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load XML default relations files.
		//
		if( $doLog )
			echo( "  • Loading XML default relations files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/DefaultTypeRelations.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML taxonomy schema files.
		//
		if( $doLog )
			echo( "  • Loading XML taxonomy schema files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankClass.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankFamily.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankGenus.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankInfraspecific.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankKingdom.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankLegion.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankOrder.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankPhylum.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankSpecies.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonRankTribe.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonEpithets.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonGroups.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonNames.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonReferences.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonThreat.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonBiology.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonConservation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonCrossability.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonDistribution.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonEconomy.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxonPolicy.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaTaxon.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML location schema files.
		//
		if( $doLog )
			echo( "  • Loading XML location schema files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationCoordinates.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationDirections.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationEnumerations.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationGeoreference.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationHabitat.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationIdent.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationLegal.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationMonitoring.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationNames.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationProtection.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationSoil.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocationUse.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLocation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML household schema files.
		//
		if( $doLog )
			echo( "  • Loading XML household schema files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHousehold.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdAnimalSpecies.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdAnnualSpecies.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdCultivatedSpecies.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdFamily.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdHousing.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdIncome.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdInterview.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdLand.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdLandOther.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdMarket.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdOwnership.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdPerennialSpecies.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdRisk.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdSocialNetworking.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdSocialStatus.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdSpecies.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdWater.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaHouseholdWildSpecies.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		//
		// Load XML other schema files.
		//
		if( $doLog )
			echo( "  • Loading XML other schema files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaInventory.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaCooperator.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaCwrPopulation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaMaterialTransfer.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLandrace.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLandraceMaintainer.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/SchemaLandraceMonitoring.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML environment sub-structure files.
		//
		if( $doLog )
			echo( "  • Loading XML environment sub-structure files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructBioclimatic.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructPrecipitation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructTemperature.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructEnvironment.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML taxonomy sub-structure files.
		//
		if( $doLog )
			echo( "  • Loading XML taxonomy sub-structure files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructTaxonConservation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructTaxonThreat.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructTaxonCrossability.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructTaxonDistribution.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML common sub-structure files.
		//
		if( $doLog )
			echo( "  • Loading XML common sub-structure files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructBreedingEntities.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructCollectingEntities.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructSafetyDuplicates.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructGermplasmNeighbourhood.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructMaterialTransfers.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML household sub-structure files.
		//
		if( $doLog )
			echo( "  • Loading XML household sub-structure files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructHouseholdEconomy.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructHouseholdInterview.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructHouseholdMarket.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructHouseholdRisk.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructHouseholdSocial.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructHouseholdSpecies.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML other sub-structure files.
		//
		if( $doLog )
			echo( "  • Loading XML other sub-structure files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructAccessionBreeding.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructAccessionCollecting.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructAccessionManagement.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructAccessionSource.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructAccessionStatus.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructGermplasmEvaluation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructCollectingMission.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructCollectingMissionTaxa.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructCollectingSamples.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructCwrPopulation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructForestPopulation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructMissionLocations.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructMissionTaxa.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructTrialMeasurement.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructQtlCharacteristics.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructGermplasmQtl.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		//
		// Load XML structure files.
		//
		if( $doLog )
			echo( "  • Loading XML structure files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/StructureAccession.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureCollectingMission.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureCwrChecklist.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureCwrInventory.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureForest.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureLandrace.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureHousehold.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureMission.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureCollectingSample.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureTrial.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/StructureQtl.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/Structures.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		//
		// Load XML form files.
		//
		if( $doLog )
			echo( "  • Loading XML form files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/collections/FormAccession.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormBreeding.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormCollecting.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormTrial.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormQtl.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormCollectingMission.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormCwrChecklist.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormCwrInventory.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormCwrPopulation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormEnvironment.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormForest.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormHousehold.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormInventory.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormLocation.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormMaterialTransfer.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormMission.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormTaxon.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/FormLandrace.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

		$file = kPATH_STANDARDS_ROOT.'/collections/Forms.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );

	
	} // loadCollections.

	 
	/*===================================================================================
	 *	loadFAOInstitutes																*
	 *==================================================================================*/

	/**
	 * Load default entities
	 *
	 * This method can be used to reset and load the default entities.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function loadFAOInstitutes( $doLog = FALSE )
	{
		//
		// Inform.
		//
		if( $doLog )
			echo( "\n==> Loading default entities.\n" );
		
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to reset ontology: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Load default FAO institutes.
		//
		if( $doLog )
			echo( "  • Loading FAO institutes.\n" );

		$institutes = new FAOInstitute();
		$institutes->Maintain( $this );
	
	} // loadFAOInstitutes.

		

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
			case kIO_XML_METADATA:
				$this->loadXMLMetadata( $xml );
				break;
		
			case kIO_XML_UNITS:
				$this->loadXMLUnits( $xml );
				break;
		
			case kIO_XML_USERS:
				$this->loadXMLUsers( $xml );
				break;
			
			default:
				throw new \Exception(
					"Unable to parse file [$theFile]: "
				   ."invalid or unsupported root element [$root]." );			// !@! ==>
		
		} // Parsed root node.
	
	} // loadXMLFile.

		

/*=======================================================================================
 *																						*
 *								PUBLIC TRAVERSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	collectStructureOffsets															*
	 *==================================================================================*/

	/**
	 * Get structure offsets.
	 *
	 * This method will return the list of offsets belonging to the provided structure or
	 * schema node.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theNode</b>: The structure or schema node native or persistent identifier.
	 *	<li><b>$theType</b>: This parameter indicates the requested output format:
	 *	 <ul>
	 *		<li><tt>0</tt>: The returned array will hold the flattened list of all leaf
	 *			offsets.
	 *		<li><tt>1</tt>: The returned array will hold the list of tag sequence numbers
	 *			structured as the schema, holding the tag references in the key.
	 *		<li><tt>2</tt>: The returned array will hold the list of tag native identifiers
	 *			structured as the schema, holding the tag references in the key.
	 *	 </ul>
	 *		If omitted, the method will use the <tt>0</tt> value by default.
	 * </ul>
	 *
	 * If the node cannot be resolved, the method will raise an exception.
	 *
	 * All schema and structure elements present as sub-elements will be resolved.
	 *
	 * @param mixed					$theNode			Node reference.
	 * @param int					$theType			Result type.
	 *
	 * @access public
	 * @return array				List of offsets belonging to the structure.
	 *
	 * @throws Exception
	 */
	public function collectStructureOffsets( $theNode, $theType = 0 )
	{
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to traverse structure: "
			   ."object is not connected." );									// !@! ==>

		//
		// Check type.
		//
		switch( $theType )
		{
			case 0:
			case 1:
			case 2:
				break;
			
			default:
				throw new \Exception(
					"Invalid traversal type parameter [$theType]." );			// !@! ==>
		}

		//
		// Init local storage.
		//
		$offsets = Array();
		
		//
		// Resolve node.
		//
		if( ! is_int( $theNode ) )
			$theNode
				= Node::ResolveCollection(
					Node::ResolveDatabase( $this ) )
						->matchOne(
							array( kTAG_ID_PERSISTENT => $theNode ),
							kQUERY_ASSERT | kQUERY_NID );
		
		//
		// Traverse root structure.
		//
		$this->traverseStructureOffsets( $offsets, $theNode, $theType, $level );
		
		return $offsets;															// ==>
		
	} // collectStructureOffsets.

		

/*=======================================================================================
 *																						*
 *								PUBLIC RESOLUTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveCollection																*
	 *==================================================================================*/

	/**
	 * Resolve collection
	 *
	 * Given a collection name, this method will return a collection reference.
	 *
	 * @param string				$theCollection		Collection name.
	 *
	 * @access public
	 * @return CollectionObject		The collection reference.
	 *
	 * @throws Exception
	 */
	public function resolveCollection( $theCollection )
	{
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to resolve collection: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Resolve collection.
		//
		switch( (string) $theCollection )
		{
			case Tag::kSEQ_NAME:
				return Tag::ResolveCollection(
						Tag::ResolveDatabase( $this, TRUE ) );						// ==>
				
			case Term::kSEQ_NAME:
				return Term::ResolveCollection(
						Term::ResolveDatabase( $this, TRUE ) );						// ==>
				
			case Node::kSEQ_NAME:
				return Node::ResolveCollection(
						Node::ResolveDatabase( $this, TRUE ) );						// ==>
				
			case Edge::kSEQ_NAME:
				return Edge::ResolveCollection(
						Edge::ResolveDatabase( $this, TRUE ) );						// ==>
				
			case User::kSEQ_NAME:
				return EntityObject::ResolveCollection(
						EntityObject::ResolveDatabase( $this, TRUE ) );				// ==>
				
			case UnitObject::kSEQ_NAME:
				return UnitObject::ResolveCollection(
						UnitObject::ResolveDatabase( $this, TRUE ) );				// ==>
			
			default:
				throw new \Exception(
					"Cannot resolve collection: "
				   ."invalid collection name [$collection]." );					// !@! ==>
		}
	
	} // resolveCollection.

		

/*=======================================================================================
 *																						*
 *								STATIC RESOLUTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveCollectionClass															*
	 *==================================================================================*/

	/**
	 * Resolve collection class
	 *
	 * Given a collection name, this method will return the base class name.
	 *
	 * @param string				$theCollection		Collection name.
	 *
	 * @static
	 * @return string				The base class name.
	 *
	 * @throws Exception
	 */
	static function ResolveCollectionClass( $theCollection )
	{
		//
		// Resolve collection.
		//
		switch( (string) $theCollection )
		{
			case Tag::kSEQ_NAME:
				return 'OntologyWrapper\Tag';										// ==>
				
			case Term::kSEQ_NAME:
				return 'OntologyWrapper\Term';										// ==>
				
			case Node::kSEQ_NAME:
				return 'OntologyWrapper\Node';										// ==>
				
			case Edge::kSEQ_NAME:
				return 'OntologyWrapper\Edge';										// ==>
				
			case User::kSEQ_NAME:
				return 'OntologyWrapper\EntityObject';								// ==>
				
			case UnitObject::kSEQ_NAME:
				return 'OntologyWrapper\UnitObject';								// ==>
			
			default:
				throw new \Exception(
					"Cannot resolve class: "
				   ."invalid collection name [$collection]." );					// !@! ==>
		}
	
	} // ResolveCollectionClass.

	 

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
		foreach( $theXML->{kIO_XML_TRANS_META} as $block )
			$this->loadXMLRootBlock( $block );
	
	} // loadXMLMetadata.

	 
	/*===================================================================================
	 *	loadXMLUsers																	*
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
	protected function loadXMLUsers( \SimpleXMLElement $theXML )
	{
	
	} // loadXMLUsers.

	 
	/*===================================================================================
	 *	loadXMLUnits																	*
	 *==================================================================================*/

	/**
	 * Parse and load entities
	 *
	 * This method will parse and load the provided units XML structure.
	 *
	 * It is assumed that the object has its comnnections open and that the provided XML
	 * structure has the correct root element.
	 *
	 * @param SimpleXMLElement		$theXML				Units XML.
	 *
	 * @access protected
	 */
	protected function loadXMLUnits( \SimpleXMLElement $theXML )
	{
		//
		// Iterate meta-blocks.
		//
		foreach( $theXML->{kIO_XML_TRANS_UNITS} as $block )
			$this->loadXMLUnit( $block );
	
	} // loadXMLUnits.

	 
	/*===================================================================================
	 *	loadXMLRootBlock																*
	 *==================================================================================*/

	/**
	 * Parse and load root block
	 *
	 * This method will parse and load the provided rot transaction block.
	 *
	 * @param SimpleXMLElement		$theXML				Root transaction block.
	 *
	 * @access protected
	 */
	protected function loadXMLRootBlock( \SimpleXMLElement $theXML )
	{
		//
		// Init cache.
		//
		$cache = Array();
		
		//
		// Iterate terms.
		//
		foreach( $theXML->{kIO_XML_META_TERM} as $item )
			$this->loadXMLTerm( $item, $cache );
	
		//
		// Iterate tags.
		//
		foreach( $theXML->{kIO_XML_META_TAG} as $item )
			$this->loadXMLTag( $item, $cache );
	
		//
		// Iterate nodes.
		//
		foreach( $theXML->{kIO_XML_META_NODE} as $item )
			$this->loadXMLNode( $item, $cache );
	
		//
		// Iterate edges.
		//
		foreach( $theXML->{kIO_XML_META_EDGE} as $item )
			$this->loadXMLEdge( $item, $cache );
	
		//
		// Iterate edges.
		//
		foreach( $theXML->{kIO_XML_TRANS_UNITS} as $item )
			$this->loadXMLUnit( $item );
	
	} // loadXMLRootBlock.

	 
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
		// Instantiate object.
		//
		if( isset( $theXML[ kIO_XML_ATTR_UPDATE ] ) )
			$object = new Tag( $this, (string) $theXML[ kIO_XML_ATTR_UPDATE ] );
		else
			$object = new Tag( $this );
		
		//
		// Load properties.
		//
		foreach( $theXML->{kIO_XML_DATA} as $element )
			$this->parseXMLElement( $element, $object );
		
		//
		// Commit.
		//
		$object->commit();
	
		//
		// Load cache.
		//
		if( ! array_key_exists( Tag::kSEQ_NAME, $theCache ) )
			$theCache[ Tag::kSEQ_NAME ] = Array();
		$theCache[ Tag::kSEQ_NAME ][ $object[ kTAG_NID ] ] = $object;
	
	} // loadXMLTag.

	 
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
		// Instantiate object.
		//
		if( isset( $theXML[ kIO_XML_ATTR_UPDATE ] ) )
			$object = new Term( $this, (string) $theXML[ kIO_XML_ATTR_UPDATE ] );
		else
			$object = new Term( $this );
		
		//
		// Load attributes.
		//
		$tmp = array( kIO_XML_ATTR_NAMESPACE => kTAG_NAMESPACE,
					  kIO_XML_ATTR_ID_LOCAL => kTAG_ID_LOCAL,
					  kIO_XML_ATTR_ID_PERSISTENT => kTAG_NID );
		foreach( $tmp as $key => $tag )
		{
			if( isset( $theXML[ $key ] ) )
				$object[ $tag ] = (string) $theXML[ $key ];
		}
		
		//
		// Load properties.
		//
		foreach( $theXML->{kIO_XML_DATA} as $element )
			$this->parseXMLElement( $element, $object );
		
		//
		// Commit.
		//
		$object->commit();
	
		//
		// Load cache.
		//
		if( ! array_key_exists( Term::kSEQ_NAME, $theCache ) )
			$theCache[ Term::kSEQ_NAME ] = Array();
		$theCache[ Term::kSEQ_NAME ][ $object[ kTAG_NID ] ] = $object;
	
	} // loadXMLTerm.

	 
	/*===================================================================================
	 *	loadXMLNode																		*
	 *==================================================================================*/

	/**
	 * Parse and load XML node
	 *
	 * This method will parse and load the provided node XML structure.
	 *
	 * @param SimpleXMLElement		$theXML				Node XML structure.
	 * @param reference				$theCache			Objects cache.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function loadXMLNode( \SimpleXMLElement $theXML, &$theCache )
	{
		//
		// Instantiate object.
		//
		if( isset( $theXML[ kIO_XML_ATTR_UPDATE ] ) )
			$object = new Node( $this, (int) (string) $theXML[ kIO_XML_ATTR_UPDATE ] );
		else
			$object = new Node( $this );
		
		//
		// Get tag or term from attributes.
		//
		$tmp = array( kIO_XML_ATTR_REF_TAG => kTAG_TAG,
					  kIO_XML_ATTR_REF_TERM => kTAG_TERM,
					  kIO_XML_ATTR_ID_PERSISTENT => kTAG_ID_PERSISTENT );
		foreach( $tmp as $key => $tag )
		{
			if( isset( $theXML[ $key ] ) )
				$object[ $tag ] = (string) $theXML[ $key ];
		}
	
		//
		// Get tag or term from cache.
		//
		if( (! $object->offsetExists( kTAG_TAG ))
		 && (! $object->offsetExists( kTAG_TERM )) )
		{
			//
			// Check term.
			//
			if( array_key_exists( Term::kSEQ_NAME, $theCache )
			 && (count( $theCache[ Term::kSEQ_NAME ] ) == 1) )
				$object[ kTAG_TERM ] = key( $theCache[ Term::kSEQ_NAME ] );
	
			//
			// Check tag.
			//
			elseif( array_key_exists( Tag::kSEQ_NAME, $theCache )
				 && (count( $theCache[ Tag::kSEQ_NAME ] ) == 1) )
				$object[ kTAG_TAG ] = key( $theCache[ Tag::kSEQ_NAME ] );
		
			else
				throw new \Exception(
					"Unable to create node: "
				   ."missing tag and term." );									// !@! ==>
	
		} // Not provided with attributes.
		
		//
		// Load properties.
		//
		foreach( $theXML->{kIO_XML_DATA} as $element )
			$this->parseXMLElement( $element, $object );
		
		//
		// Commit.
		//
		$object->commit();
	
		//
		// Load cache.
		//
		if( ! array_key_exists( Node::kSEQ_NAME, $theCache ) )
			$theCache[ Node::kSEQ_NAME ] = Array();
		$theCache[ Node::kSEQ_NAME ][ $object[ kTAG_NID ] ] = $object;
	
	} // loadXMLNode.

	 
	/*===================================================================================
	 *	loadXMLEdge																		*
	 *==================================================================================*/

	/**
	 * Parse and load XML edge
	 *
	 * This method will parse and load the provided edge XML structure.
	 *
	 * @param SimpleXMLElement		$theXML				Edge XML structure.
	 * @param reference				$theCache			Objects cache.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function loadXMLEdge( \SimpleXMLElement $theXML, &$theCache )
	{
		//
		// Instantiate object.
		//
		if( isset( $theXML[ kIO_XML_ATTR_UPDATE ] ) )
			$object = new Edge( $this, (string) $theXML[ kIO_XML_ATTR_UPDATE ] );
		else
			$object = new Edge( $this );
		
		//
		// Load properties.
		//
		foreach( $theXML->{kIO_XML_DATA} as $element )
			$this->parseXMLElement( $element, $object );
		
		//
		// Load subject from cache.
		//
		if( ! $object->offsetExists( kTAG_SUBJECT ) )
		{
			//
			// Check node.
			//
			if( array_key_exists( Node::kSEQ_NAME, $theCache )
			 && (count( $theCache[ Node::kSEQ_NAME ] ) == 1) )
				$object[ kTAG_SUBJECT ] = key( $theCache[ Node::kSEQ_NAME ] );
			
			else
				throw new \Exception(
					"Unable to create edge: "
				   ."missing subject." );										// !@! ==>
		}
		
		//
		// Load predicate from cache.
		//
		if( ! $object->offsetExists( kTAG_PREDICATE ) )
		{
			//
			// Check node.
			//
			if( array_key_exists( Term::kSEQ_NAME, $theCache )
			 && (count( $theCache[ Term::kSEQ_NAME ] ) == 1) )
				$object[ kTAG_PREDICATE ] = key( $theCache[ Term::kSEQ_NAME ] );
		
			else
				throw new \Exception(
					"Unable to create edge: "
				   ."missing predicate." );										// !@! ==>
		}
	
		//
		// Load predicate from cache.
		//
		if( ! $object->offsetExists( kTAG_PREDICATE ) )
		{
			//
			// Check term.
			//
			if( array_key_exists( Term::kSEQ_NAME, $theCache )
			 && (count( $theCache[ Term::kSEQ_NAME ] ) == 1) )
				$object[ kTAG_PREDICATE ] = key( $theCache[ Term::kSEQ_NAME ] );
		
			else
				throw new \Exception(
					"Unable to create edge: "
				   ."missing predicate." );										// !@! ==>
		}
	
		//
		// Load object from cache.
		//
		if( ! $object->offsetExists( kTAG_OBJECT ) )
		{
			//
			// Check node.
			//
			if( array_key_exists( Node::kSEQ_NAME, $theCache )
			 && (count( $theCache[ Node::kSEQ_NAME ] ) == 1) )
				$object[ kTAG_OBJECT ] = key( $theCache[ Node::kSEQ_NAME ] );
		
			else
				throw new \Exception(
					"Unable to create edge: "
				   ."missing object." );										// !@! ==>
		}
		
		//
		// Commit.
		//
		$object->commit();
	
		//
		// Load cache.
		//
		if( ! array_key_exists( Edge::kSEQ_NAME, $theCache ) )
			$theCache[ Edge::kSEQ_NAME ] = Array();
		$theCache[ Edge::kSEQ_NAME ][ $object[ kTAG_NID ] ] = $object;
	
	} // loadXMLEdge.

	 
	/*===================================================================================
	 *	loadXMLUnit																		*
	 *==================================================================================*/

	/**
	 * Parse and load XML unit
	 *
	 * This method will parse and load the provided unit XML structure.
	 *
	 * @param SimpleXMLElement		$theXML				Unit XML structure.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function loadXMLUnit( \SimpleXMLElement $theXML )
	{
		//
		// Get class.
		//
		$class = (string) $theXML[ kIO_XML_ATTR_QUAL_CLASS ];
		
		//
		// Instantiate object.
		//
		if( isset( $theXML[ kIO_XML_ATTR_UPDATE ] ) )
			$object = new $class( $this, (string) $theXML[ kIO_XML_ATTR_UPDATE ] );
		else
			$object = new $class( $this );
		
		//
		// Load properties.
		//
		foreach( $theXML->{kIO_XML_DATA} as $element )
			$this->parseXMLElement( $element, $object );
		
		//
		// Commit.
		//
		$object->commit();
	
	} // loadXMLUnit.

	 
	/*===================================================================================
	 *	parseXMLElement																	*
	 *==================================================================================*/

	/**
	 * Parse and load XML element
	 *
	 * This method will parse and load the provided XML element, the provided element is
	 * supposed to be a root level offset of the object.
	 *
	 * The method expects two parameters:
	 *
	 * <ul>
	 *	<li><b>$theElement</b>: The XML element.
	 *	<li><b>$theObject</b>: The object to load.
	 * </ul>
	 *
	 * @param SimpleXMLElement		$theElement			XML element.
	 * @param PersistentObject		$theObject			The object to load.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function parseXMLElement( \SimpleXMLElement $theElement,
									   PersistentObject  $theObject )
	{
		//
		// Init local storage.
		//
		$value = NULL;
		
		//
		// Load property.
		//
		$this->parseXMLItem( $value, $theElement, $theObject );
		
		//
		// Check property.
		//
		if( ! key( $value ) )
			throw new \Exception(
				"Unable to set property tag: "
			   ."the the property is missing its offset." );					// !@! ==>
		
		//
		// Set property.
		//
		$theObject[ key( $value ) ] = current( $value );
			
	} // parseXMLItem.

	 
	/*===================================================================================
	 *	parseXMLItem																	*
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
	 * @param reference				$theValue			Receives value.
	 * @param SimpleXMLElement		$theElement			XML element.
	 * @param PersistentObject		$theObject			The object to load.
	 * @param string				$theNode			Node reference type.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function parseXMLItem( &$theValue, \SimpleXMLElement $theElement,
												 PersistentObject  $theObject,
												 				   $theNode = NULL )
	{
		//
		// Reset key.
		//
		$key = NULL;
		
		//
		// Determine tag.
		//
		if( isset( $theElement[ kIO_XML_ATTR_REF_TAG ] ) )
			$key
				= $theObject->resolveOffset(
					(string) $theElement[ kIO_XML_ATTR_REF_TAG ], TRUE );
		elseif( isset( $theElement[ kIO_XML_ATTR_REF_TAG_SEQ ] ) )
			$key = (string) $theElement[ kIO_XML_ATTR_REF_TAG_SEQ ];
		elseif( isset( $theElement[ kIO_XML_ATTR_QUAL_CONST ] ) )
			$key = constant( (string) $theElement[ kIO_XML_ATTR_QUAL_CONST ] );
		
		//
		// Handle array element key.
		//
		elseif( isset( $theElement[ kIO_XML_ATTR_QUAL_KEY ] ) )
			$key = (string) $theElement[ kIO_XML_ATTR_QUAL_KEY ];
		
		//
		// Handle node reference.
		//
		if( isset( $theElement[ kIO_XML_ATTR_REF_NODE ] ) )
			$theNode = (string) $theElement[ kIO_XML_ATTR_REF_NODE ];
		
		//
		// Handle scalar.
		//
		if( ! count( $theElement->{kIO_XML_DATA} ) )
		{
			//
			// Resolve node.
			//
			if( $theNode !== NULL )
			{
				//
				// Parse reference type.
				//
				switch( $theNode )
				{
					case kIO_XML_ATTR_NODE_TAG:
						$value
							= Node::GetTagMaster(
								$this,
								(string) $theElement,
								kQUERY_ASSERT | kQUERY_NID );
						break;
				
					case kIO_XML_ATTR_NODE_SEQ:
						$value
							= Node::GetTagMaster(
								$this,
								(int) (string) $theElement,
								kQUERY_ASSERT | kQUERY_NID );
						break;
				
					case kIO_XML_ATTR_NODE_TERM:
						$value
							= Node::GetTermMaster(
								$this,
								(string) $theElement,
								kQUERY_ASSERT | kQUERY_NID );
						break;
				
					case kIO_XML_ATTR_NODE_PID:
						$value
							= Node::GetPidNode(
								$this,
								(string) $theElement,
								kQUERY_ASSERT | kQUERY_NID );
						break;
				
					case kIO_XML_ATTR_NODE_ID:
						$value = (string) $theElement;
						break;
					
					default:
						throw new \Exception(
							"Unable to set property: "
						   ."invalid node reference type [$theNode]." );		// !@! ==>
				
				} // Parsed node reference type.
			
			} // Node reference.
			
			//
			// Set value.
			//
			else
				$value = (string) $theElement;
			
			//
			// Handle empty value.
			//
			if( $theValue === NULL )
			{
				//
				// Handle key.
				//
				if( $key !== NULL )
					$theValue = array( $key => $value );
				else
					throw new \Exception(
						"Unable to set property: "
					   ."the property is missing its offset." );				// !@! ==>
			
			} // Empty value.
			
			//
			// Existing value.
			//
			elseif( is_array( $theValue ) )
			{
				//
				// Handle key.
				//
				if( $key !== NULL )
					$theValue[ $key ] = $value;
				
				//
				// Handle array element.
				//
				else
					$theValue[] = $value;
			
			} // Existing value.
		
		} // Scalar value.
		
		//
		// Handle array.
		//
		else
		{
			//
			// Init value.
			//
			if( ! is_array( $theValue ) )
				$theValue = Array();
			
			//
			// Set key.
			//
			if( $key !== NULL )
			{
				$theValue[ $key ] = Array();
				$value = & $theValue[ $key ];
			
			} // Has key.
			
			//
			// Set array element.
			//
			else
			{
				$index = count( $theValue );
				$theValue[ $index ] = Array();
				$value = & $theValue[ $index ];
			
			} // No key.
			
			//
			// Load elements.
			//
			foreach( $theElement->{kIO_XML_DATA} as $element )
				$this->parseXMLItem( $value, $element, $theObject, $theNode );
	
		} // Array property.
	
	} // parseXMLItem.

		

/*=======================================================================================
 *																						*
 *							PROTECTED TRAVERSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	traverseStructureOffsets														*
	 *==================================================================================*/

	/**
	 * Traverse structure offsets.
	 *
	 * This method will traverse the relationships of the provided node, filling the
	 * provided array container with the list of matched offsets.
	 *
	 * The main duty of this method is to iterate all nodes pointing to the provided node
	 * via the {@link kPREDICATE_PROPERTY_OF} or {@link kPREDICATE_SUBCLASS_OF} predicates,
	 * loading the provided array container with the encountered tag offsets according to
	 * the provided type parameter, eventually recursing nested structures and schemas.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffsets</b>: This parameter will receive the matched offsets.
	 *	<li><b>$theNode</b>: The structure or schema node native identifier.
	 *	<li><b>$theType</b>: This parameter indicates the requested output format:
	 *	 <ul>
	 *		<li><tt>0</tt>: Collects flattened list of leaf offsets.
	 *		<li><tt>1</tt>: Collects structured list of tag sequence numbers.
	 *		<li><tt>2</tt>: Collects structured list of tag native identifiers.
	 *	 </ul>
	 *	<li><b>$theLevel</b>: This parameter is initialised and handled by this method, it
	 *		holds an array of elements holding the nested level tag sequence numbers, this
	 *		array will only hold tags of type {@link kTYPE_STRUCT}.
	 * </ul>
	 *
	 * If the node cannot be resolved, the method will raise an exception.
	 *
	 * All schema and structure elements present as sub-elements will be resolved.
	 *
	 * @param array					$theOffsets			Receives offsets.
	 * @param int					$theNode			Node native identifier.
	 * @param int					$theType			Result type.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function traverseStructureOffsets( &$theOffsets,
												  $theNode, $theType,
												 &$theLevel )
	{
		//
		// Init level.
		//
		if( ! is_array( $theLevel ) )
			$theLevel = Array();
		
		//
		// Locate edges.
		//
		$edges
			= Edge::ResolveCollection(
				Edge::ResolveDatabase( $this ) )
					->matchAll(
						array( kTAG_OBJECT => $theNode,
							   kTAG_PREDICATE
									=> array( '$in'
										=> array( kPREDICATE_PROPERTY_OF,
												  kPREDICATE_SUBCLASS_OF ) ) ),
						kQUERY_ASSERT | kQUERY_OBJECT,
						array( kTAG_SUBJECT => TRUE,
							   kTAG_PREDICATE => TRUE ) );
		
		//
		// Iterate results.
		//
		foreach( $edges as $edge )
		{
			//
			// Recurse schemas.
			//
			if( $edge[ kTAG_PREDICATE ] == kPREDICATE_SUBCLASS_OF )
				$this->traverseStructureOffsets(
					$theOffsets, $edge[ kTAG_SUBJECT ], $theType, $theLevel );
			
			//
			// Load offset.
			//
			else
			{
				//
				// Get subject node.
				//
				$node
					= Node::ResolveCollection(
						Node::ResolveDatabase( $this ) )
							->matchOne(
								array( kTAG_NID => $edge[ kTAG_SUBJECT ] ),
								kQUERY_ASSERT | kQUERY_ARRAY,
								array( kTAG_TAG => TRUE ) );
				
				//
				// Assert it points to a tag.
				//
				if( array_key_exists( kTAG_TAG, $node ) )
				{
					//
					// Get tag.
					//
					$tag
						= Tag::ResolveCollection(
							Tag::ResolveDatabase( $this ) )
								->matchOne(
									array( kTAG_NID => $node[ kTAG_TAG ] ),
									kQUERY_ASSERT | kQUERY_ARRAY,
									array( kTAG_ID_SEQUENCE => TRUE,
										   kTAG_DATA_TYPE => TRUE ) );
					
					//
					// Save identifiers.
					//
					$id = $tag[ kTAG_NID ];
					$seq = $tag[ kTAG_ID_SEQUENCE ];
					
					//
					// Handle structure.
					//
					if( $tag[ kTAG_DATA_TYPE ] == kTYPE_STRUCT )
					{
						//
						// Push structure.
						//
						$theLevel[] = $seq;
						
						//
						// Allocate and reference structure container.
						//
						switch( $theType )
						{
							case 1:
								$theOffsets[ $seq ] = Array();
								$ref = & $theOffsets[ $seq ];
								break;
						
							case 2:
								$theOffsets[ $id ] = Array();
								$ref = & $theOffsets[ $id ];
								break;
							
							default:
								$ref = & $theOffsets;
								break;
						
						} // By result type.
						
						//
						// Recurse structure.
						//
						$this->traverseStructureOffsets(
							$ref,
							$edge[ kTAG_SUBJECT ],
							$theType,
							$theLevel );
						
						//
						// Pop structure.
						//
						array_pop( $theLevel );
					
					} // Structure tag.
					
					//
					// Handle property.
					//
					else
					{
						//
						// Load property.
						//
						switch( $theType )
						{
							case 0:
								$theOffsets[]
									= implode(
										'.',
										array_merge(
											$theLevel,
											array( $seq ) ) );
								break;
						
							case 1:
								$theOffsets[ $seq ] = $seq;
								break;
						
							case 2:
								$theOffsets[ $id ] = $id;
								break;
						
						} // By result type.
					
					} // Property tag.
				
				} // Tag node.
				
				else
					throw new \Exception(
						"Invalid structure element pointing to [$theNode]: "
					   ."node ["
					   .$node[ kTAG_NID]
					   ."] should reference a tag." );							// !@! ==>
			
			} // Not a schema.
		
		} // Iterating edges.
		
	} // traverseStructureOffsets.

		

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
