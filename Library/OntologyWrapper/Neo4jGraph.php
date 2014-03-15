<?php

/**
 * Neo4jGraph.php
 *
 * This file contains the definition of the {@link Neo4jGraph} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;

/*=======================================================================================
 *																						*
 *									Neo4jGraph.php										*
 *																						*
 *======================================================================================*/

/**
 * Server object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing graph database
 * instances, this class extends the {@link ConnectionObject} class to implement graph
 * specific functionality prototypes.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/03/2014
 */
abstract class Neo4jGraph extends ConnectionObject
{
		

/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isConnected																		*
	 *==================================================================================*/

	/**
	 * Check if connection is open
	 *
	 * We overload this method to assume the object is connected if the resource is a
	 * {@link \Everyman\Neo4j\Client}.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		return ( $this->mConnection instanceof \Everyman\Neo4j\Client );			// ==>
	
	} // isConnected.

		

/*=======================================================================================
 *																						*
 *							PUBLIC NODE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newNode																			*
	 *==================================================================================*/

	/**
	 * <h4>Create a new node</h4>
	 *
	 * In this class we return a new Neo4j Node instance.
	 *
	 * @param array					$theProperties		Node properties.
	 *
	 * @access public
	 * @return mixed				The node object.
	 */
	public function newNode( $theProperties = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Normalise properties.
			//
			if( $theProperties === NULL )
				$theProperties = Array();
			elseif( ! is_array( $theProperties ) )
				throw new \Exception(
					"Unable to instantiate node: "
				   ."provided properties are not an array." );					// !@! ==>
			
			return $this->mConnection->makeNode( $theProperties );					// ==>
		
		} // Is connected.

		throw new \Exception(
			"Unable to instantiate node: "
		   ."graph is not connected." );										// !@! ==>
	
	} // newNode.

	 
	/*===================================================================================
	 *	setNode																			*
	 *==================================================================================*/

	/**
	 * <h4>Save a node</h4>
	 *
	 * In this class we check if the provided node is of the correct type, save it and
	 * return its identifier.
	 *
	 * If provided, the second parameter must be an array, or the method will raise an
	 * exception.
	 *
	 * @param mixed					$theNode			Node object to be saved.
	 * @param mixed					$theProperties		Node properties.
	 * @param mixed					$theProperties		Node properties.
	 *
	 * @access public
	 * @return int					The node identifier.
	 */
	public function setNode( $theNode, $theProperties = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Check node type.
			//
			if( ! ($theNode instanceof Everyman\Neo4j\Node) )
				throw new \Exception(
					"Unable to save node: "
				   ."provided invalid node object type." );						// !@! ==>
			
			//
			// Handle properties.
			//
			if( $theProperties !== NULL )
			{
				//
				// Set properties.
				//
				if( is_array( $theProperties ) )
					$theNode->setProperties( $theProperties );
				
				else
					throw new \Exception(
						"Unable to save node: "
					   ."provided properties is not an array." );				// !@! ==>
			
			} // Provided properties.
			
			//
			// Save node.
			//
			if( ! $this->mConnection->saveNode( $theNode ) )
				throw new \Exception(
					"Unable to save node." );									// !@! ==>
			
			return $theNode->getId();												// ==>
		
		} // Is connected.

		throw new \Exception(
			"Unable to save node: "
		   ."graph is not connected." );										// !@! ==>
	
	} // setNode.

	 
	/*===================================================================================
	 *	getNode																			*
	 *==================================================================================*/

	/**
	 * <h4>Get an existing node</h4>
	 *
	 * In this class we return the node corresponding to the provided identifier or
	 * <tt>NULL</tt>.
	 *
	 * If the provided identifier is not an integer, we raise an exception.
	 *
	 * @param mixed					$theIdentifier		Node identifier.
	 * @param boolean				$doThrow			TRUE throw exception if not found.
	 *
	 * @access public
	 * @return mixed				The node object.
	 */
	public function getNode( $theIdentifier, $doThrow = FALSE )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Check identifier type.
			//
			if( is_integer( $theIdentifier ) )
			{
				//
				// Retrieve node.
				//
				$node = $this->mConnection->getNode( $theIdentifier, FALSE );
				if( $node !== NULL )
					return $node;													// ==>
				
				if( ! $doThrow )
					return NULL;													// ==>
				
				throw new Exception(
					"Node not found" );											// !@! ==>
			
			} // Provided integer identifier.
		
			throw new \Exception(
				"Unable to get node: "
			   ."provided invalid node identifier type." );						// !@! ==>
		
		} // Is connected.

		throw new \Exception(
			"Unable to get node: "
		   ."graph is not connected." );										// !@! ==>
	
	} // getNode.

	 
	/*===================================================================================
	 *	delNode																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete an existing node</h4>
	 *
	 * In this class we accept either the actual node, or the node identifier.
	 *
	 * @param mixed					$theIdentifier		Node identifier.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt> deleted, <tt>NULL</tt> not found.
	 */
	public function delNode( $theIdentifier )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle node.
			//
			if( $theIdentifier instanceof Everyman\Neo4j\Node )
				return $this->mConnection->deleteNode( $theIdentifier );			// ==>
			
			//
			// Check identifier type.
			//
			if( is_integer( $theIdentifier ) )
			{
				//
				// Get node.
				//
				$node = $this->getNode( $theIdentifier );
				if( $node instanceof Everyman\Neo4j\Node )
					return $this->mConnection->deleteNode( $node );					// ==>
			
				return NULL;														// ==>
			
			} // Provided integer identifier.
		
			throw new \Exception(
				"Unable to delete node: "
			   ."provided invalid node identifier type." );						// !@! ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to delete node: "
		   ."graph is not connected." );										// !@! ==>
	
	} // delNode.

		

/*=======================================================================================
 *																						*
 *							PUBLIC EDGE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newEdge																			*
	 *==================================================================================*/

	/**
	 * <h4>Create a new edge</h4>
	 *
	 * This method should return a new edge connecting the provided subject and object nodes
	 * via the provided predicate, holding the eventual provided properties.
	 *
	 * The returned edge is not supposed to be saved yet.
	 *
	 * @param mixed					$theSubject			Subject node or identifier.
	 * @param array					$thePredicate		Edge predicate native identifier.
	 * @param mixed					$theObject			Object node or identifier.
	 * @param array					$theProperties		Edge properties.
	 *
	 * @access public
	 * @return mixed				Edge object.
	 */
	public function newEdge( $theSubject, $thePredicate, $theObject, $theProperties = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Check properties.
			//
			if( $theProperties !== NULL )
			{
				//
				// Set properties.
				//
				if( ! is_array( $theProperties ) )
					throw new \Exception(
						"Unable to instantiate edge object: "
					   ."provided properties is not an array." );				// !@! ==>
			
			} // Provided properties.
			
			//
			// Init properties.
			//
			else
				$theProperties = Array();
			
			//
			// Resolve subject.
			//
			if( ! ($theSubject instanceof Everyman\Neo4j\Node) )
			{
				//
				// Resolve node.
				//
				if( is_integer( $theSubject ) )
					$theSubject = $this->getNode( $theSubject );
				
				else
					throw new \Exception(
						"Unable to instantiate edge object: "
					   ."provided invalid subject node identifier type." );		// !@! ==>
				
				//
				// Check node.
				//
				if( ! ($theSubject instanceof Everyman\Neo4j\Node) )
					throw new \Exception(
						"Unable to instantiate edge object: "
					   ."provided invalid subject node object type." );			// !@! ==>
			
			} // Not a node.
			
			//
			// Resolve object.
			//
			if( ! ($theObject instanceof Everyman\Neo4j\Node) )
			{
				//
				// Resolve node.
				//
				if( is_integer( $theObject ) )
					$theObject = $this->getNode( $theObject );
				
				else
					throw new \Exception(
						"Unable to instantiate edge object: "
					   ."provided invalid object node identifier type." );		// !@! ==>
				
				//
				// Check node.
				//
				if( ! ($theObject instanceof Everyman\Neo4j\Node) )
					throw new \Exception(
						"Unable to instantiate edge object: "
					   ."provided invalid object node object type." );			// !@! ==>
			
			} // Not a node.
			
			//
			// Instantiate edge.
			//
			$edge = $this->mConnection->makeRelationship( $theProperties );
			
			//
			// Set subject.
			//
			$edge->setStartNode( $theSubject );
			
			//
			// Set object.
			//
			$edge->setEndNode( $theObject );
			
			//
			// Set predicate.
			//
			$edge->setType( (string) $thePredicate );
			
			return $edge;															// ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to instantiate edge object: "
		   ."graph is not connected." );										// !@! ==>
	
	} // newEdge.

	 
	/*===================================================================================
	 *	setEdge																			*
	 *==================================================================================*/

	/**
	 * <h4>Save an edge</h4>
	 *
	 * In this class we save the provided edge.
	 *
	 * @param mixed					$theEdge			Edge object to be saved.
	 *
	 * @access public
	 * @return int					Edge identifier.
	 */
	public function setEdge( $theEdge )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Check edge type.
			//
			if( ! ($theEdge instanceof Everyman\Neo4j\Relationship) )
				throw new \Exception(
					"Unable to save edge: "
				   ."invalid edge object type." );								// !@! ==>
			
			//
			// Save node.
			//
			if( ! $this->mConnection->saveRelationship( $theEdge ) )
				return FALSE;														// ==>
			
			return $theEdge->getId();												// ==>
		
		} // Is connected.

		throw new \Exception(
			"Unable to save edge: "
		   ."graph is not connected." );										// !@! ==>
	
	} // setEdge.

	 
	/*===================================================================================
	 *	getEdge																			*
	 *==================================================================================*/

	/**
	 * <h4>Get an existing edge</h4>
	 *
	 * In this class we return the edge corresponding to the provided identifier, or
	 * <tt>NULL</tt>.
	 *
	 * @param mixed					$theIdentifier		Edge identifier.
	 * @param boolean				$doThrow			TRUE throw exception if not found.
	 *
	 * @access public
	 * @return mixed				Edge object.
	 */
	public function getEdge( $theIdentifier, $doThrow = FALSE )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Check identifier type.
			//
			if( is_integer( $theIdentifier ) )
			{
				//
				// Get edge.
				//
				$edge = $this->mConnection->getRelationship( $theIdentifier, FALSE );
				if( $edge !== NULL )
					return $edge;													// ==>
				
				if( ! $doThrow )
					return NULL;													// ==>
				
				throw new Exception(
					"Edge not found" );											// !@! ==>
			
			} // Provided integer identifier.
		
			throw new \Exception(
				"Unable to get edge: "
			   ."provided invalid edge identifier type." );						// !@! ==>
		
		} // Is connected.

		throw new \Exception(
			"Unable to get edge: "
		   ."graph is not connected." );										// !@! ==>
	
	} // getEdge.

	 
	/*===================================================================================
	 *	delEdge																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete an existing edge</h4>
	 *
	 * In this class we accept either the actual edge, or the node identifier.
	 *
	 * @param mixed					$theIdentifier		Edge identifier.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt> deleted, <tt>NULL</tt> not found.
	 */
	public function delEdge( $theIdentifier )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle node.
			//
			if( $theIdentifier instanceof Everyman\Neo4j\Relationship )
				return $this->mConnection->deleteRelationship( $theIdentifier );	// ==>
			
			//
			// Check identifier type.
			//
			if( is_integer( $theIdentifier ) )
			{
				//
				// Get node.
				//
				$edge = $this->getEdge( $theIdentifier );
				if( $edge instanceof Everyman\Neo4j\Relationship )
					return $this->mConnection->deleteRelationship( $edge );			// ==>
			
				return NULL;														// ==>
			
			} // Provided integer identifier.
		
			throw new \Exception(
				"Unable to delete edge: "
			   ."provided invalid identifier type." );						// !@! ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to delete edge: "
		   ."graph is not connected." );										// !@! ==>
	
	} // delEdge.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PROPERTY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getNodeProperties																*
	 *==================================================================================*/

	/**
	 * <h4>Get node properties</h4>
	 *
	 * In this class we check whether the node is a Neo4j graph node or an integer, in all
	 * other cases we raise an exception.
	 *
	 * @param mixed					$theNode			Node object or reference.
	 *
	 * @access public
	 * @return array				The node properties
	 */
	public function getNodeProperties( $theNode )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Get node.
			//
			if( is_integer( $theNode ) )
			{
				$theNode = $this->getNode( $theNode );
				if( $theNode === NULL )
					return NULL;													// ==>
			
			} // Provided reference.
			
			//
			// Return properties.
			//
			if( $theNode instanceof Everyman\Neo4j\Node )
				return $theNode->getProperties();									// ==>
			
			throw new \Exception(
				"Unable to get node properties: "
			   ."provided invalid node object type." );							// !@! ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to get node properties: "
		   ."graph is not connected." );										// !@! ==>
	
	} // getNodeProperties.

	 
	/*===================================================================================
	 *	setNodeLabel																	*
	 *==================================================================================*/

	/**
	 * <h4>Set node label</h4>
	 *
	 * This method can be used to set a node's node labels, this feature is specific to
	 * Neo4j and is not declared in the abstract ancestor.
	 *
	 * The method accepts two parameters: the node identifier or the node itself and the
	 * label or labels to be added.
	 *
	 * If the node cannot be resolved, the method will raise an exception.
	 *
	 * The method will return the full set of labels currently held by the node.
	 *
	 * @param mixed					$theNode			Node object or identifier.
	 * @param mixed					$theLabel			Label or labels.
	 *
	 * @access public
	 * @return array				The node labels.
	 */
	public function setNodeLabel( $theNode, $theLabel )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Get node.
			//
			if( is_integer( $theNode ) )
			{
				$theNode = $this->getNode( $theNode );
				if( $theNode === NULL )
					throw new \Exception(
						"Unable to set node label: "
					   ."unresolved node reference." );							// !@! ==>
			
			} // Provided reference.
			
			//
			// Handle node.
			//
			if( $theNode instanceof Everyman\Neo4j\Node )
			{
				//
				// Normalise labels.
				//
				if( ! is_array( $theLabel ) )
					$theLabel = array( $theLabel );
				
				return $theNode->addLabels( $theLabel );							// ==>
			
			} // Correct node type.
			
			else
				throw new \Exception(
					"Unable to set node label: "
				   ."provided invalid node object type." );						// !@! ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to set node label: "
		   ."graph is not connected." );										// !@! ==>
	
	} // setNodeLabel.

	 
	/*===================================================================================
	 *	getNodeLabel																	*
	 *==================================================================================*/

	/**
	 * <h4>Get node label</h4>
	 *
	 * This method can be used to retrieve a node's labels, this feature is specific to
	 * Neo4j and is not declared in the abstract ancestor.
	 *
	 * The method accepts one parameter: the node identifier or the node itself.
	 *
	 * If the node cannot be resolved, the method will raise an exception.
	 *
	 * The method will return the full set of labels currently held by the node.
	 *
	 * @param mixed					$theNode			Node object or identifier.
	 *
	 * @access public
	 * @return array				The node labels.
	 */
	public function getNodeLabel( $theNode )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Get node.
			//
			if( is_integer( $theNode ) )
			{
				$theNode = $this->getNode( $theNode );
				if( $theNode === NULL )
					throw new \Exception(
						"Unable to get node labels: "
					   ."unresolved node reference." );							// !@! ==>
			
			} // Provided reference.
			
			//
			// Return properties.
			//
			if( $theNode instanceof Everyman\Neo4j\Node )
				return $theNode->getLabels();										// ==>
			
			else
				throw new \Exception(
					"Unable to get node labels: "
				   ."provided invalid node object type." );						// !@! ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to get node labels: "
		   ."graph is not connected." );										// !@! ==>
	
	} // getNodeLabel.

	 
	/*===================================================================================
	 *	delNodeLabel																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete node label</h4>
	 *
	 * This method can be used to delete a node's labels, this feature is specific to
	 * Neo4j and is not declared in the abstract ancestor.
	 *
	 * The method accepts one parameter: the node identifier or the node itself.
	 *
	 * If the node cannot be resolved, the method will raise an exception.
	 *
	 * The method will return the full set of labels held by the node after deleting the
	 * provided labels.
	 *
	 * @param mixed					$theNode			Node object or identifier.
	 * @param mixed					$theLabel			Label or labels.
	 *
	 * @access public
	 * @return array				The node labels.
	 */
	public function delNodeLabel( $theNode, $theLabel )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Get node.
			//
			if( is_integer( $theNode ) )
			{
				$theNode = $this->getNode( $theNode );
				if( $theNode === NULL )
					throw new \Exception(
						"Unable to delete node labels: "
					   ."unresolved node reference." );							// !@! ==>
			
			} // Provided reference.
			
			//
			// Handle node.
			//
			if( $theNode instanceof Everyman\Neo4j\Node )
			{
				//
				// Normalise labels.
				//
				if( ! is_array( $theLabel ) )
					$theLabel = array( $theLabel );
				
				return $theNode->removeLabels( $theLabel );							// ==>
			
			} // Correct node type.
			
			else
				throw new \Exception(
					"Unable to delete node labels: "
				   ."provided invalid node object type." );						// !@! ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to delete node labels: "
		   ."graph is not connected." );										// !@! ==>
	
	} // delNodeLabel.

		

/*=======================================================================================
 *																						*
 *							PUBLIC STRUCTURE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getNodeEdges																	*
	 *==================================================================================*/

	/**
	 * <h4>Get node edges</h4>
	 *
	 * This method can be used to retrieve the provided node's edges according to the
	 * provided sense.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theNode</tt>: The node of which we want the relationships.
	 *	<li><tt>$thePredicate</tt>: The eventual predicate or predicates references that
	 *		must be present in the relationships.
	 *	<li><tt>$theSense</tt>: The relationship direction:
	 *	 <ul>
	 *		<li><tt>{@link kTYPE_RELATIONSHIP_IN}</tt>: Incoming relationships: all edges
	 *			in which the node is the subject of the relationship.
	 *		<li><tt>{@link kTYPE_RELATIONSHIP_OUT}</tt>: Outgoing relationships: all edges
	 *			in which the node is the object of the relationship.
	 *		<li><tt>{@link kTYPE_RELATIONSHIP_ALL}</tt>: All relationships: all edges
	 *			connected to the node.
	 *	 </ul>
	 * </ul>
	 *
	 * The method should return an array of edges.
	 *
	 * @param mixed					$theNode			Reference node.
	 * @param mixed					$thePredicate		Edge predicate(s).
	 * @param string				$theSense			Relationship sense.
	 *
	 * @access public
	 * @return mixed
	 */
	public function getNodeEdges( $theNode, $thePredicate = NULL,
											$theSense = kTYPE_RELATIONSHIP_ALL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Resolve node.
			//
			if( ! ($theNode instanceof Everyman\Neo4j\Node) )
			{
				//
				// Resolve node.
				//
				if( is_integer( $theNode ) )
					$theNode = $this->getNode( $theNode );
				
				else
					throw new \Exception(
						"Unable to get node edges: "
					   ."provided invalid node type." );						// !@! ==>
				
				//
				// Check node.
				//
				if( ! ($theNode instanceof Everyman\Neo4j\Node) )
					throw new \Exception(
						"Unable to get node edges: "
					   ."node not found." );									// !@! ==>
			
			} // Not a node.
			
			//
			// Normalise predicates.
			//
			if( $thePredicate === NULL )
				$thePredicate = Array();
			else
			{
				//
				// Normalise predicates list.
				//
				if( is_array( $thePredicate ) )
				{
					//
					// Iterate elements.
					//
					$keys = array_keys( $thePredicate );
					foreach( $keys as $Key )
						$thePredicate[ $key ] = (string) $thePredicate[ $key ];
				
				} // Predicates list.
				
				//
				// Normalise predicate.
				//
				else
					$thePredicate = (string) $thePredicate;
			
			} // Normalise predicates.
			
			//
			// Normalise sense.
			//
			switch( $theSense )
			{
				case kTYPE_RELATIONSHIP_IN:
					$theSense = Everyman\Neo4j\Relationship::DirectionIn;
					break;

				case kTYPE_RELATIONSHIP_OUT:
					$theSense = Everyman\Neo4j\Relationship::DirectionOut;
					break;

				case kTYPE_RELATIONSHIP_ALL:
					$theSense = Everyman\Neo4j\Relationship::DirectionAll;
					break;
				
				default:
					if( $theSense !== NULL )
						throw new \Exception(
							"Unable to get node edges: "
						   ."unsupported relationship sense." );				// !@! ==>
					break;
			
			} // Normalising sense.
			
			return $this->mConnection
						->getNodeRelationships(
							$theNode, $thePredicate, $theSense );					// ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to get node edges: "
		   ."graph is not connected." );										// !@! ==>
	
	} // getNodeEdges.

	 

/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	connectionOpen																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * This method will instantiate a {@link \Everyman\Neo4j\Client} object and set it in
	 * the mConnection data member.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * If the operation fails, the method will raise an exception.
	 *
	 * The method will use the {@link kTAG_CONN_HOST} and {@link kTAG_CONN_PORT} offsets
	 * exclusively.
	 *
	 * @access protected
	 * @return mixed				The native connection.
	 */
	protected function connectionOpen()
	{
		//
		// Load parameters.
		//
		$params = $this->getArrayCopy();
		if( array_key_exists( kTAG_CONN_PORT, $params ) )
		{
			$options = $params[ kTAG_CONN_PORT ];
			unset( $params[ kTAG_CONN_PORT ] );
		}
		else
			$options = '7474';
		
		//
		// Build data source name.
		//
		$dsn = $this->parseOffsets( $params );
		
		//
		// Set client.
		//
		$this->mConnection = ( $options !== NULL )
						   ? new \Everyman\Neo4j\Client( $dsn, $options )
						   : new \Everyman\Neo4j\Client( $dsn );
		
		return $this->mConnection;													// ==>
	
	} // connectionOpen.

	 
	/*===================================================================================
	 *	connectionClose																	*
	 *==================================================================================*/

	/**
	 * Close connection
	 *
	 * We overload this method to reset the connection resource.
	 *
	 * @access protected
	 */
	protected function connectionClose()					{	$this->mConnection = NULL;	}

	 

} // class Neo4jGraph.


?>
