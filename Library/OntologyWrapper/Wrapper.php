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
 * ISO definitions.
 *
 * This file contains the default ISO definitions.
 */
require_once( kPATH_STANDARDS_ROOT."/iso/iso.inc.php" );

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
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function resetOntology( $doLog = FALSE )
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
		// Reset collections.
		//
		if( $doLog )
			echo( "  • Resetting collections.\n" );
		$this->mMetadata->drop();
		Tag::CreateIndexes( $this->mMetadata );
		Term::CreateIndexes( $this->mMetadata );
		Node::CreateIndexes( $this->mMetadata );
		Edge::CreateIndexes( $this->mMetadata );
		
		//
		// Load XML files.
		//
		if( $doLog )
			echo( "  • Loading default XML files.\n" );
		
		$file = kPATH_STANDARDS_ROOT.'/default/Namespaces.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/Types.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/Tags.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/Domains.xml';
		if( $doLog ) echo( "    - $file\n" );
		$this->loadXMLFile( $file );
		
		$file = kPATH_STANDARDS_ROOT.'/default/Predicates.xml';
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
		
		//
		// Set sequence number.
		//
		$this->Metadata()->setSequenceNumber( Tag::kSEQ_NAME, kTAG_SEQUENCE_START );
	
	} // resetOntology.

	 
	/*===================================================================================
	 *	resetEntities																	*
	 *==================================================================================*/

	/**
	 * Reset entities
	 *
	 * This method can be used to reset the entities database, it will <b>erase the current
	 * entities collection</em> and load the FAO insatitutes.
	 *
	 * The method will take care of setting the necessary indexes.
	 *
	 * @access public
	 * @return array				Statistics.
	 *
	 * @throws Exception
	 */
	public function resetEntities()
	{
		//
		// Check if object is connected.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to reset entities: "
			   ."object is not connected." );									// !@! ==>
		
		//
		// Resety entity collection.
		//
		FAOInstitute::ResetCollection( $this->mEntities );
		
		//
		// Load FAO institutes.
		//
		return FAOInstitute::Maintain( $this );										// ==>
	
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
	 * executes and <tt>FALSE</tt> if the object is not connected, or if the metadata is
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
			// Get tags collection.
			//
			$collection = $this->mMetadata->Collection( Tag::kSEQ_NAME );
		
			//
			// Load all tags.
			//
			$tags = $collection->getAll();
			foreach( $tags as $tag )
				$this->setTag( $tag, 0 );
			
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
 *						PUBLIC STANDARDS INITIALISATION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	initISOStandards																*
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
	public function initISOStandards( $doLog = FALSE )
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
				"Unable to reset ontology: "
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
		// Populate the ISO 3166 alpha-3 aggregated countries.
		//
		if( $doLog )
			echo( "    - Merge ISO:3166:alpha-3 enumerations\n" );
		$this->copyRelationships(
			array( Node::GetTermMaster( $this,
										'iso:3166:1:alpha-3',
										kQUERY_ASSERT | kQUERY_NID ),
				   Node::GetTermMaster( $this,
				   						'iso:3166:3:alpha-3',
				   						kQUERY_ASSERT | kQUERY_NID ) ),
			kPREDICATE_INSTANCE_OF, kTYPE_RELATIONSHIP_IN,
			Node::GetTermMaster( $this, 'iso:3166:alpha-3', kQUERY_ASSERT | kQUERY_NID ),
			kPREDICATE_INSTANCE_OF, kTYPE_RELATIONSHIP_IN );
		
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
	
	} // initISOStandards.

	 
	/*===================================================================================
	 *	initWBIStandards																*
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
	public function initWBIStandards( $doLog = FALSE )
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
				"Unable to reset ontology: "
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
	
	} // initWBIStandards.

	 
	/*===================================================================================
	 *	initEntities																	*
	 *==================================================================================*/

	/**
	 * Load default entities
	 *
	 * This method can be used to load the default entities.
	 *
	 * @param boolean				$doLog				Log operations.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function initEntities( $doLog = FALSE )
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
	
	} // initEntities.

		

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
				
			case EntityObject::kSEQ_NAME:
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
				
			case EntityObject::kSEQ_NAME:
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
		// Init cache.
		//
		$cache = Array();
		
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
		if( isset( $theXML[ 'set' ] ) )
			$object = new Tag( $this, (string) $theXML[ 'set' ] );
		else
			$object = new Tag( $this );
		
		//
		// Load properties.
		//
		foreach( $theXML->{'item'} as $element )
			$this->loadXMLElement( $element, $object );
		
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
		if( isset( $theXML[ 'set' ] ) )
			$object = new Term( $this, (string) $theXML[ 'set' ] );
		else
			$object = new Term( $this );
		
		//
		// Load attributes.
		//
		$tmp = array( 'ns' => kTAG_NAMESPACE,
					  'lid' => kTAG_ID_LOCAL,
					  'pid' => kTAG_NID );
		foreach( $tmp as $key => $tag )
		{
			if( isset( $theXML[ $key ] ) )
				$object[ $tag ] = (string) $theXML[ $key ];
		}
		
		//
		// Load properties.
		//
		foreach( $theXML->{'item'} as $element )
			$this->loadXMLElement( $element, $object );
		
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
		if( isset( $theXML[ 'set' ] ) )
			$object = new Node( $this, (int) (string) $theXML[ 'set' ] );
		else
			$object = new Node( $this );
		
		//
		// Get tag or term from attributes.
		//
		$tmp = array( 'tag' => kTAG_TAG,
					  'term' => kTAG_TERM,
					  'pid' => kTAG_ID_PERSISTENT );
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
		foreach( $theXML->{'item'} as $element )
			$this->loadXMLElement( $element, $object );
		
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
		if( isset( $theXML[ 'set' ] ) )
			$object = new Edge( $this, (string) $theXML[ 'set' ] );
		else
			$object = new Edge( $this );
		
		//
		// Load properties.
		//
		foreach( $theXML->{'item'} as $element )
			$this->loadXMLElement( $element, $object );
		
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
	 *	loadXMLElement																	*
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
	protected function loadXMLElement( \SimpleXMLElement $theElement,
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
		if( isset( $theElement[ 'tag' ] ) )
			$key = $theObject->resolveOffset( (string) $theElement[ 'tag' ], TRUE );
		elseif( isset( $theElement[ 'seq' ] ) )
			$key = (string) $theElement[ 'seq' ];
		elseif( isset( $theElement[ 'const' ] ) )
			$key = constant( (string) $theElement[ 'const' ] );
		
		//
		// Handle array element key.
		//
		elseif( isset( $theElement[ 'key' ] ) )
			$key = (string) $theElement[ 'key' ];
		
		//
		// Handle node reference.
		//
		if( isset( $theElement[ 'node' ] ) )
			$theNode = (string) $theElement[ 'node' ];
		
		//
		// Handle scalar.
		//
		if( ! count( $theElement->{'item'} ) )
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
					case 'tag':
						$value
							= Node::GetTagMaster(
								$this,
								(string) $theElement,
								kQUERY_ASSERT | kQUERY_NID );
						break;
				
					case 'seq':
						$value
							= Node::GetTagMaster(
								$this,
								(int) (string) $theElement,
								kQUERY_ASSERT | kQUERY_NID );
						break;
				
					case 'term':
						$value
							= Node::GetTermMaster(
								$this,
								(string) $theElement,
								kQUERY_ASSERT | kQUERY_NID );
						break;
				
					case 'pid':
						$value
							= Node::GetPidNode(
								$this,
								(string) $theElement,
								kQUERY_ASSERT | kQUERY_NID );
						break;
				
					case 'node':
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
			foreach( $theElement->{'item'} as $element )
				$this->parseXMLItem( $value, $element, $theObject, $theNode );
	
		} // Array property.
	
	} // parseXMLItem.

		

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
