<?php

/**
 * Neo4jGraph.php
 *
 * This file contains the definition of the {@link Neo4jGraph} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\DatabaseGraph;

/*=======================================================================================
 *																						*
 *									Neo4jGraph.php										*
 *																						*
 *======================================================================================*/

/**
 * Server object
 *
 * This <i>concrete</i> class implements a graph database based on Neo4j.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/03/2014
 */
class Neo4jGraph extends DatabaseGraph
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
 *									PUBLIC QUERY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	query																			*
	 *==================================================================================*/

	/**
	 * Perform a query
	 *
	 * This method will perform the provided Cypher query and return the result.
	 *
	 * @param string				$theQuery			Cypher query string.
	 * @param array					$theVariables		Query replacement variables.
	 *
	 * @access public
	 * @return Iterator				Query result set.
	 */
	public function query( $theQuery, $theVariables = Array() )
	{
		//
		// Check replacement variables.
		//
		if( ! is_array( $theVariables ) )
			throw new \Exception(
				"Unable to execute query: "
			   ."replacement variables are not an array." );					// !@! ==>
			
		//
		// Instantiate Cypher query.
		//
		$query = new \Everyman\Neo4j\Cypher\Query( $this->Connection(),
												   (string) $theQuery,
												   $theVariables );
		
		return $query->getResultSet();												// ==>
	
	} // query.

		

/*=======================================================================================
 *																						*
 *							PUBLIC NODE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setNode																			*
	 *==================================================================================*/

	/**
	 * <h4>Save a node</h4>
	 *
	 * In this class we instantiate the node, set the properties, save it and set the
	 * labels.
	 *
	 * If provided, the parameters must be an array, or the method will raise an
	 * exception.
	 *
	 * @param array					$theProperties		Node properties.
	 * @param mixed					$theLabels			Node labels.
	 *
	 * @access public
	 * @return int					The node identifier.
	 */
	public function setNode( $theProperties = NULL, $theLabels = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle properties.
			//
			if( $theProperties !== NULL )
			{
				//
				// Check properties.
				//
				if( ! is_array( $theProperties ) )
					throw new \Exception(
						"Unable to save node: "
					   ."provided properties is not an array." );				// !@! ==>
			
			} // Provided properties.
			
			//
			// Instantiate node.
			//
			$node = ( is_array( $theProperties ) )
				   ? $this->mConnection->makeNode( $theProperties )
				   : $this->mConnection->makeNode();
			
			//
			// Save node.
			//
			if( ! $this->mConnection->saveNode( $node ) )
				throw new \Exception(
					"Unable to save node." );									// !@! ==>
			
			//
			// Set node labels.
			//
			if( $theLabels !== NULL )
			{
				//
				// Normalise labels.
				//
				if( ! is_array( $theLabels ) )
					$theLabels
						= array(
							$this->Connection()->makeLabel( (string) $theLabels ) );
				else
				{
					foreach( $theLabels as $key => $value )
						$theLabels[ $key ]
							= $this->Connection()->makeLabel( (string) $value );
				
				} // Provided labels list
				
				//
				// Set labels.
				//
				$node->addLabels( $theLabels );
			
			} // Provided properties.
			
			return $node->getId();													// ==>
		
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
	 * @param int					$theIdentifier		Node identifier.
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
	 * @param mixed					$theNode			Node identifier or object.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt> deleted, <tt>NULL</tt> not found.
	 */
	public function delNode( $theNode )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle node.
			//
			if( $theNode instanceof \Everyman\Neo4j\Node )
				return $this->mConnection->deleteNode( (int) $theNode );			// ==>
			
			//
			// Check identifier type.
			//
			if( is_integer( $theNode ) )
			{
				//
				// Get node.
				//
				$node = $this->getNode( $theNode );
				if( $node instanceof \Everyman\Neo4j\Node )
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
 *								PUBLIC NODE PROPERTY INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setNodeProperties																*
	 *==================================================================================*/

	/**
	 * <h4>Set node properties</h4>
	 *
	 * In this class we check whether the node is a Neo4j graph node or an integer, in all
	 * other cases we raise an exception.
	 *
	 * @param mixed					$theNode			Node object or reference.
	 * @param array					$theProperties		Node properties.
	 *
	 * @access public
	 */
	public function setNodeProperties( $theNode, $theProperties )
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
						"Unable to set node properties: "
					   ."unresolved node." );									// !@! ==>
			
			} // Provided reference.
			
			//
			// Check node.
			//
			if( $theNode instanceof \Everyman\Neo4j\Node )
			{
				//
				// Check properties.
				//
				if( is_array( $theProperties ) )
				{
					//
					// Set properties.
					//
					$theNode->setProperties( $theProperties );
					
					//
					// Save node.
					//
					$this->mConnection->saveNode( $theNode );
				
				} // Correct properties format.
			
				else
					throw new \Exception(
						"Unable to set node properties: "
					   ."provided invalid properties type." );					// !@! ==>
			
			} // Correct node.
			
			else
				throw new \Exception(
					"Unable to set node properties: "
				   ."provided invalid node object type." );						// !@! ==>
			
		} // Is connected.
		
		else
			throw new \Exception(
				"Unable to set node properties: "
			   ."graph is not connected." );									// !@! ==>
	
	} // setNodeProperties.

	 
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
			if( $theNode instanceof \Everyman\Neo4j\Node )
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
	 *	delNodeProperties																*
	 *==================================================================================*/

	/**
	 * <h4>Set node properties</h4>
	 *
	 * In this class we check whether the node is a Neo4j graph node or an integer, in all
	 * other cases we raise an exception.
	 *
	 * @param mixed					$theNode			Node object or reference.
	 * @param mixed					$theProperties		Node property keys or key.
	 *
	 * @access public
	 */
	public function delNodeProperties( $theNode, $theProperties )
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
						"Unable to delete node properties: "
					   ."unresolved node." );									// !@! ==>
			
			} // Provided reference.
			
			//
			// Check node.
			//
			if( $theNode instanceof \Everyman\Neo4j\Node )
			{
				//
				// Handle property list.
				//
				if( is_array( $theProperties ) )
				{
					foreach( $theProperties as $property )
						$theNode->removeProperty( (string) $property );
				
				} // Correct properties format.
				
				//
				// Handle single property.
				//
				else
					$theNode->removeProperty( (string) $theProperties );
				
				//
				// Save node.
				//
				$this->mConnection->saveNode( $theNode );
			
			} // Correct node.
			
			else
				throw new \Exception(
					"Unable to set node properties: "
				   ."provided invalid node object type." );						// !@! ==>
			
		} // Is connected.
		
		else
			throw new \Exception(
				"Unable to set node properties: "
			   ."graph is not connected." );									// !@! ==>
	
	} // delNodeProperties.

	 
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
	 * Note that the node must have been saved before setting the label, or an exception will
	 * be raised.
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
			if( $theNode instanceof \Everyman\Neo4j\Node )
			{
				//
				// Normalise labels.
				//
				if( ! is_array( $theLabel ) )
					$theLabel = array( $theLabel );
				
				//
				// Cast labels.
				//
				foreach( $theLabel as $key => $value )
					$theLabel[ $key ] = $this->Connection()->makeLabel( (string) $value );
				
				//
				// Set label.
				//
				$labels = $theNode->addLabels( $theLabel );
				
				//
				// Normalise labels.
				//
				foreach( $labels as $key => $value )
					$labels[ $key ] = $value->getName();
				
				return $labels;														// ==>
			
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
			if( $theNode instanceof \Everyman\Neo4j\Node )
			{
				//
				// Get label objects.
				//
				$labels = $theNode->getLabels();
				
				//
				// Get label names.
				//
				foreach( $labels as $key => $value )
					$labels[ $key ] = $value->getName();
				
				return $labels;														// ==>
			
			} // Correct type of node.
			
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
			if( $theNode instanceof \Everyman\Neo4j\Node )
			{
				//
				// Normalise labels.
				//
				if( ! is_array( $theLabel ) )
					$theLabel
						= array(
							$this->Connection()->makeLabel( (string) $theLabel ) );
				else
				{
					foreach( $theLabel as $key => $value )
						$theLabel[ $key ]
							= $this->Connection()->makeLabel( (string) $value );
				}
				
				$labels = $theNode->removeLabels( $theLabel );
				
				//
				// Normalise labels.
				//
				foreach( $labels as $key => $value )
					$labels[ $key ] = $value->getName();
				
				return $labels;														// ==>
			
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
 *							PUBLIC EDGE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setEdge																			*
	 *==================================================================================*/

	/**
	 * <h4>Create a new edge</h4>
	 *
	 * In this class we normalise the edge parameters, instantiate the edge and save it,
	 * returning the edge sequence number.
	 *
	 * The predicate is assumed to be a term reference, no check will be performed, so you
	 * should ensure the provided string is a correct term reference before calling this
	 * method.
	 *
	 * @param mixed					$theSubject			Subject node or identifier.
	 * @param mixed					$thePredicate		Predicate identifier or object.
	 * @param mixed					$theObject			Object node or identifier.
	 * @param array					$theProperties		Edge properties.
	 *
	 * @access public
	 * @return integer				Edge sequence number.
	 */
	public function setEdge( $theSubject, $thePredicate, $theObject, $theProperties = NULL )
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
			if( ! ($theSubject instanceof \Everyman\Neo4j\Node) )
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
				if( ! ($theSubject instanceof \Everyman\Neo4j\Node) )
					throw new \Exception(
						"Unable to instantiate edge object: "
					   ."provided invalid subject node object type." );			// !@! ==>
			
			} // Not a node.
			
			//
			// Resolve object.
			//
			if( ! ($theObject instanceof \Everyman\Neo4j\Node) )
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
				if( ! ($theObject instanceof \Everyman\Neo4j\Node) )
					throw new \Exception(
						"Unable to instantiate edge object: "
					   ."provided invalid object node object type." );			// !@! ==>
			
			} // Not a node.
			
			//
			// Normalise predicate.
			//
			if( $thePredicate instanceof Term )
				$thePredicate = $thePredicate[ kTAG_NID ];
			else
				$thePredicate = (string) $thePredicate;
			
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
			
			//
			// Save edge.
			//
			if( ! $this->mConnection->saveRelationship( $edge ) )
				throw new \Exception(
					"Unable to save edge object." );							// !@! ==>
			
			return $edge->getId();													// ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to instantiate edge object: "
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
	 * @param mixed					$theEdge			Edge identifier or object.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt> deleted, <tt>NULL</tt> not found.
	 */
	public function delEdge( $theEdge )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle node.
			//
			if( $theEdge instanceof \Everyman\Neo4j\Relationship )
				return $this->mConnection->deleteRelationship( $theEdge );			// ==>
			
			//
			// Check identifier type.
			//
			if( is_integer( $theEdge ) )
			{
				//
				// Get node.
				//
				$theEdge = $this->getEdge( $theEdge );
				if( $theEdge instanceof \Everyman\Neo4j\Relationship )
					return $this->mConnection->deleteRelationship( $theEdge );		// ==>
			
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
 *								PUBLIC EDGE PROPERTY INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setEdgeProperties																*
	 *==================================================================================*/

	/**
	 * <h4>Set edge properties</h4>
	 *
	 * In this class we check whether the edge is a Neo4j graph edge or an integer, in all
	 * other cases we raise an exception.
	 *
	 * @param mixed					$theEdge			Edge object or reference.
	 * @param array					$theProperties		Edge properties.
	 *
	 * @access public
	 */
	public function setEdgeProperties( $theEdge, $theProperties )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Get edge.
			//
			if( is_integer( $theEdge ) )
			{
				$theEdge = $this->getEdge( $theEdge );
				if( $theEdge === NULL )
					throw new \Exception(
						"Unable to set edge properties: "
					   ."unresolved edge." );									// !@! ==>
			
			} // Provided reference.
			
			//
			// Check edge.
			//
			if( $theEdge instanceof \Everyman\Neo4j\Relationship )
			{
				//
				// Check properties.
				//
				if( is_array( $theProperties ) )
				{
					//
					// Set properties.
					//
					$theEdge->setProperties( $theProperties );
					
					//
					// Save edge.
					//
					$this->mConnection->saveRelationship( $theEdge );
				
				} // Correct properties format.
			
				else
					throw new \Exception(
						"Unable to set edge properties: "
					   ."provided invalid properties type." );					// !@! ==>
			
			} // Correct edge.
			
			else
				throw new \Exception(
					"Unable to set edge properties: "
				   ."provided invalid edge object type." );						// !@! ==>
			
		} // Is connected.
		
		else
			throw new \Exception(
				"Unable to set edge properties: "
			   ."graph is not connected." );									// !@! ==>
	
	} // setEdgeProperties.

	 
	/*===================================================================================
	 *	getEdgeProperties																*
	 *==================================================================================*/

	/**
	 * <h4>Get edge properties</h4>
	 *
	 * In this class we check whether the edge is a Neo4j graph edge or an integer, in all
	 * other cases we raise an exception.
	 *
	 * @param mixed					$theEdge			Edge object or reference.
	 *
	 * @access public
	 * @return array				The edge properties
	 */
	public function getEdgeProperties( $theEdge )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Get edge.
			//
			if( is_integer( $theEdge ) )
			{
				$theEdge = $this->getEdge( $theEdge );
				if( $theEdge === NULL )
					return NULL;													// ==>
			
			} // Provided reference.
			
			//
			// Return properties.
			//
			if( $theEdge instanceof \Everyman\Neo4j\Relationship )
				return $theEdge->getProperties();									// ==>
			
			throw new \Exception(
				"Unable to get edge properties: "
			   ."provided invalid edge object type." );							// !@! ==>
			
		} // Is connected.

		throw new \Exception(
			"Unable to get edge properties: "
		   ."graph is not connected." );										// !@! ==>
	
	} // getEdgeProperties.

	 
	/*===================================================================================
	 *	delEdgeProperties																*
	 *==================================================================================*/

	/**
	 * <h4>Set edge properties</h4>
	 *
	 * In this class we check whether the edge is a Neo4j graph edge or an integer, in all
	 * other cases we raise an exception.
	 *
	 * @param mixed					$theEdge			Edge object or reference.
	 * @param mixed					$theProperties		Edge property keys or key.
	 *
	 * @access public
	 */
	public function delEdgeProperties( $theEdge, $theProperties )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Get edge.
			//
			if( is_integer( $theEdge ) )
			{
				$theEdge = $this->getEdge( $theEdge );
				if( $theEdge === NULL )
					throw new \Exception(
						"Unable to delete edge properties: "
					   ."unresolved edge." );									// !@! ==>
			
			} // Provided reference.
			
			//
			// Check edge.
			//
			if( $theEdge instanceof \Everyman\Neo4j\Relationship )
			{
				//
				// Handle property list.
				//
				if( is_array( $theProperties ) )
				{
					foreach( $theProperties as $property )
						$theEdge->removeProperty( (string) $property );
				
				} // Correct properties format.
				
				//
				// Handle single property.
				//
				else
					$theEdge->removeProperty( (string) $theProperties );
				
				//
				// Save edge.
				//
				$this->mConnection->saveRelationship( $theEdge );
			
			} // Correct edge.
			
			else
				throw new \Exception(
					"Unable to set edge properties: "
				   ."provided invalid edge object type." );						// !@! ==>
			
		} // Is connected.
		
		else
			throw new \Exception(
				"Unable to set edge properties: "
			   ."graph is not connected." );									// !@! ==>
	
	} // delEdgeProperties.

		

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
			if( ! ($theNode instanceof \Everyman\Neo4j\Node) )
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
				if( ! ($theNode instanceof \Everyman\Neo4j\Node) )
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
					$theSense = \Everyman\Neo4j\Relationship::DirectionIn;
					break;

				case kTYPE_RELATIONSHIP_OUT:
					$theSense = \Everyman\Neo4j\Relationship::DirectionOut;
					break;

				case kTYPE_RELATIONSHIP_ALL:
					$theSense = \Everyman\Neo4j\Relationship::DirectionAll;
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
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	clear																			*
	 *==================================================================================*/

	/**
	 * <h4>Clear graph</h4>
	 *
	 * In this class we send a Cypher query to clear the graph.
	 *
	 * @access public
	 */
	public function clear()
	{
		//
		// Set query.
		//
		$query = "MATCH (n) "
				."OPTIONAL MATCH (n)-[r]-() "
				."DELETE n,r";
		
		//
		// Execute query.
		//
		$this->query( $query );
	
	} // clear.

	 
	/*===================================================================================
	 *	drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop graph</h4>
	 *
	 * In this class we stop the server, delete the data directory and restart the server.
	 *
	 * @param string				$theDirectory		Data directory path.
	 *
	 * @access public
	 */
	public function drop( $theDirectory )
	{
		//
		// Stop server.
		//
		exec( 'launchctl unload -w /Users/milko/Library/LaunchAgents/org.neo4j.server.plist' );
		
		//
		// Wait a bit.
		//
		sleep( 5 );
		
		//
		// Remove data directory.
		//
		exec( "rm -r $theDirectory" );
		
		//
		// Restart server.
		//
		exec( 'launchctl load -w /Users/milko/Library/LaunchAgents/org.neo4j.server.plist' );
		
		//
		// Wait a bit.
		//
		sleep( 10 );
		
		//
		// Write node 0.
		//
		$this->setNode();
	
	} // drop.

	 
	/*===================================================================================
	 *	backup																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop graph</h4>
	 *
	 * In this class we stop the server, copy the provided data directory to the provided
	 * destination directory and restart the server.
	 *
	 * @param string				$theSource			Data directory path.
	 * @param string				$theDest			Backup directory path.
	 *
	 * @access public
	 */
	public function backup( $theSource, $theDest )
	{
		//
		// Stop server.
		//
		exec( 'launchctl unload -w /Users/milko/Library/LaunchAgents/org.neo4j.server.plist' );
		
		//
		// Wait a bit.
		//
		sleep( 5 );
		
		//
		// Remove data directory.
		//
		exec( "zip -r $theDest/neo4j.zip $theSource" );
		
		//
		// Restart server.
		//
		exec( 'launchctl load -w /Users/milko/Library/LaunchAgents/org.neo4j.server.plist' );
		
		//
		// Wait a bit.
		//
		sleep( 10 );
	
	} // backup.

	 

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
		// Remove scheme.
		//
		if( array_key_exists( kTAG_CONN_PROTOCOL, $params ) )
			unset( $params[ kTAG_CONN_PROTOCOL ] );
		
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
