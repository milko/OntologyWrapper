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
	 *	setNode																			*
	 *==================================================================================*/

	/**
	 * <h4>Save a node</h4>
	 *
	 * This method should insert a new node in the current graph, featuring the provided
	 * properties and the provided labels.
	 *
	 * The method should return the node identifier if the operation was successful, or
	 * raise an exception on errors.
	 *
	 * @param array					$theProperties		Node properties.
	 * @param array					$theLabels			Node labels.
	 *
	 * @access public
	 * @return int					The node identifier.
	 */
	abstract public function setNode( $theProperties = NULL, $theLabels = NULL );

	 
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
	 * @param int					$theIdentifier		Node identifier.
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
	 * @param mixed					$theIdentifier		Node identifier or object.
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
	 *	setEdge																			*
	 *==================================================================================*/

	/**
	 * <h4>Save an edge</h4>
	 *
	 * This method should insert or update the provided edge in the current graph.
	 *
	 * The method should return the edge identifier if the operation was successful.
	 *
	 * @param mixed					$theSubject			Subject node or identifier.
	 * @param mixed					$thePredicate		Predicate identifier or object.
	 * @param mixed					$theObject			Object node or identifier.
	 * @param array					$theProperties		Edge properties.
	 *
	 * @access public
	 * @return int					Edge identifier.
	 */
	abstract public function setEdge( $theSubject,
									  $thePredicate,
									  $theObject,
									  $theProperties = NULL );

	 
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
	 * @param mixed					$theIdentifier		Edge identifier or object.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt> deleted, <tt>NULL</tt> not found.
	 */
	abstract public function delEdge( $theIdentifier );

		

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
	 *	clear																			*
	 *==================================================================================*/

	/**
	 * <h4>Clear graph</h4>
	 *
	 * This method should delete all graph elements.
	 *
	 * @access public
	 */
	abstract public function clear();

	 
	/*===================================================================================
	 *	drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop graph</h4>
	 *
	 * This method should delete all graph elements or drop the graph database.
	 *
	 * @param string				$theDirectory		Data directory path.
	 * @param string				$theService			Service file path.
	 *
	 * @access public
	 */
	abstract public function drop( $theDirectory, $theService );
		


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
