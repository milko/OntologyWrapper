<?php

/**
 * DatabaseGraph.php
 *
 * This file contains the definition of the {@link DatabaseGraph} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;

/*=======================================================================================
 *																						*
 *									DatabaseGraph.php									*
 *																						*
 *======================================================================================*/

/**
 * Graph database object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing graph database
 * instances, this class extends the {@link ConnectionObject} class to implement graph
 * specific functionality prototypes.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/03/2014
 */
abstract class DatabaseGraph extends ConnectionObject
{
		

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
	 * This method should return a new node optionally filled with the provided attributes.
	 *
	 * The returned node is not supposed to be saved yet.
	 *
	 * @param array					$theProperties		Node properties.
	 *
	 * @access public
	 * @return mixed				The node object.
	 */
	abstract public function newNode( $theProperties = NULL );

	 
	/*===================================================================================
	 *	setNode																			*
	 *==================================================================================*/

	/**
	 * <h4>Save a node</h4>
	 *
	 * This method should insert or update the provided node in the current graph.
	 *
	 * The method should return the node identifier if the operation was successful.
	 *
	 * If you provide the <tt>$theProperties</tt> parameter, these will be set in the node
	 * before it is saved.
	 *
	 * @param mixed					$theNode			Node object to be saved.
	 * @param mixed					$theProperties		Node properties.
	 *
	 * @access public
	 * @return int					The node identifier.
	 */
	abstract public function setNode( $theNode, $theProperties = NULL );

	 
	/*===================================================================================
	 *	GetNode																			*
	 *==================================================================================*/

	/**
	 * <h4>Get an existing node</h4>
	 *
	 * This method should return a node corresponding to the provided identifier.
	 *
	 * If the second parameter is <tt>TRUE</tt> and the node was not found, the method
	 * should raise an exception.
	 *
	 * @param mixed					$theIdentifier		Node identifier.
	 * @param boolean				$doThrow			TRUE throw exception if not found.
	 *
	 * @access public
	 * @return mixed				The node object.
	 */
	abstract public function GetNode( $theIdentifier, $doThrow = FALSE );

	 
	/*===================================================================================
	 *	delNode																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete an existing node</h4>
	 *
	 * This method should delete the provided node from the current graph.
	 *
	 * The method should return <tt>TRUE</tt> if the operation was successful and
	 * <tt>NULL</tt> if the provided identifier is not resolved.
	 *
	 * @param mixed					$theIdentifier		Node identifier.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt> deleted, <tt>NULL</tt> not found.
	 */
	abstract public function delNode( $theIdentifier );

		

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
	abstract public function newEdge( $theSubject, $thePredicate, $theObject,
									  $theProperties = NULL );

	 
	/*===================================================================================
	 *	setEdge																			*
	 *==================================================================================*/

	/**
	 * <h4>Save an edge</h4>
	 *
	 * This method should insert or update the provided edge in the current graph.
	 *
	 * The method should return the edge identifier if the operation was successful.
	 *
	 * @param mixed					$theEdge			Edge object to be saved.
	 *
	 * @access public
	 * @return int					Edge identifier.
	 */
	abstract public function setEdge( $theEdge );

	 
	/*===================================================================================
	 *	getEdge																			*
	 *==================================================================================*/

	/**
	 * <h4>Get an existing edge</h4>
	 *
	 * This method should return an edge corresponding to the provided identifier.
	 *
	 * If the second parameter is <tt>TRUE</tt> and the edge was not found, the method
	 * should raise an exception.
	 *
	 * @param mixed					$theIdentifier		Edge identifier.
	 * @param boolean				$doThrow			TRUE throw exception if not found.
	 *
	 * @access public
	 * @return mixed				Edge object.
	 */
	abstract public function getEdge( $theIdentifier, $doThrow = FALSE );

	 
	/*===================================================================================
	 *	delEdge																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete an existing edge</h4>
	 *
	 * This method should delete the provided edge from the current graph.
	 *
	 * The method should return <tt>TRUE</tt> if the operation was successful and
	 * <tt>NULL</tt> if the provided identifier is not resolved.
	 *
	 * @param mixed					$theIdentifier		Edge identifier.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt> deleted, <tt>NULL</tt> not found.
	 */
	abstract public function delEdge( $theIdentifier );

		

/*=======================================================================================
 *																						*
 *								PUBLIC PROPERTY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setNodeProperties																*
	 *==================================================================================*/

	/**
	 * <h4>Set node properties</h4>
	 *
	 * This method can be used to set the provided node's properties.
	 *
	 * The first parameter can be either the node, or the node identifier and the second
	 * parameter is an array containinf the properties we want to set.
	 *
	 * If the node reference is not resolved, the method should return <tt>FALSE</tt>.
	 *
	 * If the provided node is not of the correct type, the method should raise an
	 * exception.
	 *
	 * @param mixed					$theNode			Node object or reference.
	 * @param array					$theProperties		Node properties.
	 *
	 * @access public
	 */
	abstract public function setNodeProperties( $theNode, $theProperties );

	 
	/*===================================================================================
	 *	getNodeProperties																*
	 *==================================================================================*/

	/**
	 * <h4>Get node properties</h4>
	 *
	 * This method can be used to retrieve the provided node's properties.
	 *
	 * The method accepts one parameter which can either be the node, or the node identifier
	 * for which we want the properties.
	 *
	 * If the node reference is not resolved, the method should return <tt>FALSE</tt>.
	 *
	 * If the provided node is not of the correct type, the method should raise an
	 * exception.
	 *
	 * @param mixed					$theNode			Node object or reference.
	 *
	 * @access public
	 * @return array				The node properties
	 */
	abstract public function getNodeProperties( $theNode );

		

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
	abstract public function getNodeEdges( $theNode, $thePredicate = NULL,
													 $theSense = NULL );

		

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop graph</h4>
	 *
	 * This method should delete all graph elements or drop the graph database.
	 *
	 * @access public
	 */
	abstract public function drop();
		


/*=======================================================================================
 *																						*
 *								STATIC OFFSET INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return {@link kTAG_CONN_PROTOCOL}, {@link kTAG_CONN_HOST},
	 * {@link kTAG_CONN_PORT}, {@link kTAG_CONN_USER}, {@link kTAG_CONN_PASS} and
	 * {@link kTAG_CONN_OPTS}.
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_CONN_PROTOCOL,
								   kTAG_CONN_HOST, kTAG_CONN_PORT,
								   kTAG_CONN_USER, kTAG_CONN_PASS,
								   kTAG_CONN_OPTS ) );								// ==>
	
	} // DefaultOffsets;

	 

} // class DatabaseGraph.


?>
