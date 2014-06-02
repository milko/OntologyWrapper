<?php

/**
 * ServiceObject.php
 *
 * This file contains the definition of the {@link ServiceObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ServiceObject;

/*=======================================================================================
 *																						*
 *										Service.php										*
 *																						*
 *======================================================================================*/

/**
 * Service object
 *
 * A <em>service</em> is an object that can be used to implement a set of web services, the
 * current class implements the base functionality, concrete derived classes will extend
 * this class to implement specific behaviours.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 03/05/2014
 */
class Service extends ServiceObject
{
		

/*=======================================================================================
 *																						*
 *							PROTECTED REQUEST PARSING INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseOperation																	*
	 *==================================================================================*/

	/**
	 * Parse operation.
	 *
	 * 
	 * In this class we assert the following operations:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 * </ul>
	 *
	 * @access protected
	 */
	protected function parseOperation()
	{
		//
		// Parse operation.
		//
		switch( $op = $_REQUEST[ kAPI_REQUEST_OPERATION ] )
		{
			case kAPI_OP_MATCH_TAG_LABELS:
			case kAPI_OP_MATCH_TERM_LABELS:
			case kAPI_OP_MATCH_TAG_BY_LABEL:
			case kAPI_OP_MATCH_TERM_BY_LABEL:
			case kAPI_OP_GET_TAG_ENUMERATIONS:
			case kAPI_OP_GET_NODE_ENUMERATIONS:
			case kAPI_OP_MATCH_UNITS:
				$this->offsetSet( kAPI_REQUEST_OPERATION, $op );
				break;
				
			default:
				parent::parseOperation();
				break;
		}
		
	} // parseOperation.

	 
	/*===================================================================================
	 *	parseParameter																	*
	 *==================================================================================*/

	/**
	 * Parse parameter.
	 *
	 * In this class we handle the following operations:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 * </ul>
	 *
	 * @param string				$theKey				Parameter key.
	 * @param mixed					$theValue			Parameter value.
	 *
	 * @access protected
	 *
	 * @uses parseStringMatchOperator()
	 */
	protected function parseParameter( &$theKey, &$theValue )
	{
		//
		// Parse by operation.
		//
		switch( $op = $this->offsetGet( kAPI_REQUEST_OPERATION ) )
		{
			//
			// Strings list.
			//
			case kAPI_OP_MATCH_TAG_LABELS:
			case kAPI_OP_MATCH_TERM_LABELS:
			case kAPI_OP_MATCH_TAG_BY_LABEL:
			case kAPI_OP_MATCH_TERM_BY_LABEL:
				//
				// Parse parameter.
				//
				switch( $theKey )
				{
					case kAPI_PARAM_OPERATOR:
						$this->parseStringMatchOperator( $theValue );
						$this->offsetSet( $theKey, $theValue );
						break;
				
					default:
						parent::parseParameter( $theKey, $theValue );
						break;
				}
				break;
				
			//
			// Get tag enumerations.
			//
			case kAPI_OP_GET_TAG_ENUMERATIONS:
				//
				// Parse parameter.
				//
				switch( $theKey )
				{
					case kAPI_PARAM_TAG:
						$this->offsetSet( $theKey, $theValue );
						break;
				
					default:
						parent::parseParameter( $theKey, $theValue );
						break;
				}
				break;
				
			//
			// Get node enumerations.
			//
			case kAPI_OP_GET_NODE_ENUMERATIONS:
				//
				// Parse parameter.
				//
				switch( $theKey )
				{
					case kAPI_PARAM_NODE:
						$this->offsetSet( $theKey, (int) $theValue );
						break;
				
					default:
						parent::parseParameter( $theKey, $theValue );
						break;
				}
				break;
				
			//
			// Match domains.
			//
			case kAPI_OP_MATCH_UNITS:
				//
				// Parse parameter.
				//
				switch( $theKey )
				{
					case kAPI_PARAM_CRITERIA:
					case kAPI_PARAM_DOMAIN:
					case kAPI_PARAM_DATA:
					case kAPI_PARAM_GROUP:
					case kAPI_PARAM_SHAPE:
					case kAPI_PARAM_SHAPE_OFFSET:
						$this->offsetSet( $theKey, $theValue );
						break;

					case kAPI_PARAM_DISTANCE:
						$this->offsetSet( $theKey, (int) $theValue );
						break;

					default:
						parent::parseParameter( $theKey, $theValue );
						break;
				}
				break;
				
			default:
				parent::parseParameter( $theKey, $theValue );
				break;
		}
	
	} // parseParameter.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PARSING UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseStringMatchOperator														*
	 *==================================================================================*/

	/**
	 * Parse string match operator.
	 *
	 * This method will parse the provided string match operator converting the provided
	 * value to an array using the comma as a separator.
	 *
	 * If the resiulting array is empty, the method will set by default the operator to
	 * "contains case and accent insensitive".
	 *
	 * @access protected
	 *
	 * @param mixed					$theValue			Parameter value.
	 */
	protected function parseStringMatchOperator( &$theValue )
	{
		//
		// Set to array.
		//
		if( ! is_array( $theValue ) )
		{
			$tmp = explode( ',', $theValue );
			$theValue = Array();
			foreach( $tmp as $value )
			{
				if( strlen( $string = trim( $value ) ) )
					$theValue[] = $string;
			}
		
		} // Not an array.
		
		//
		// Set default operator.
		//
		if( ! count( $theValue ) )
			$theValue = array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE );
		
	} // parseStringMatchOperator.

		

/*=======================================================================================
 *																						*
 *							PROTECTED REQUEST VALIDATION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	validateRequest																	*
	 *==================================================================================*/

	/**
	 * Validate request.
	 *
	 * In this class we handle the following operations:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 * </ul>
	 *
	 * @access protected
	 */
	protected function validateRequest()
	{
		//
		// Parse by operation.
		//
		switch( $op = $this->offsetGet( kAPI_REQUEST_OPERATION ) )
		{
			case kAPI_OP_MATCH_TAG_LABELS:
			case kAPI_OP_MATCH_TERM_LABELS:
			case kAPI_OP_MATCH_TAG_BY_LABEL:
			case kAPI_OP_MATCH_TERM_BY_LABEL:
				$this->validateMatchLabelStrings();
				break;
				
			case kAPI_OP_GET_TAG_ENUMERATIONS:
				$this->validateGetTagEnumerations();
				break;
				
			case kAPI_OP_GET_NODE_ENUMERATIONS:
				$this->validateGetNodeEnumerations();
				break;
				
			case kAPI_OP_MATCH_UNITS:
				$this->validateMatchUnits();
				break;
				
			default:
				parent::validateRequest();
				break;
		}
		
	} // validateRequest.

		

/*=======================================================================================
 *																						*
 *						PROTECTED REQUEST EXECUTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	executeRequest																	*
	 *==================================================================================*/

	/**
	 * Execute request.
	 *
	 * In this class we handle the following operations:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @uses executeMatchTagLabels()
	 * @uses executeMatchTermLabels()
	 * @uses executeMatchTagByLabel()
	 */
	protected function executeRequest()
	{
		//
		// Parse by operation.
		//
		switch( $this->offsetGet( kAPI_REQUEST_OPERATION ) )
		{
			case kAPI_OP_MATCH_TAG_LABELS:
				$this->executeMatchTagLabels();
				break;
				
			case kAPI_OP_MATCH_TERM_LABELS:
				$this->executeMatchTermLabels();
				break;
				
			case kAPI_OP_MATCH_TAG_BY_LABEL:
				$this->executeMatchTagByLabel();
				break;
				
			case kAPI_OP_MATCH_TERM_BY_LABEL:
				$this->executeMatchTermByLabel();
				break;
				
			case kAPI_OP_GET_TAG_ENUMERATIONS:
				$this->executeGetTagEnumerations();
				break;
				
			case kAPI_OP_GET_NODE_ENUMERATIONS:
				$this->executeGetNodeEnumerations();
				break;
				
			case kAPI_OP_MATCH_UNITS:
				$this->executeMatchUnits();
				break;
				
			default:
				parent::executeRequest();
				break;
		}
		
	} // executeRequest.

	 
	/*===================================================================================
	 *	executeMatchTagLabels															*
	 *==================================================================================*/

	/**
	 * Match tag labels.
	 *
	 * The method will resolve the appropriate collection and pass it to the
	 * {@link executeMatchLabelStrings()} method.
	 *
	 * @access protected
	 *
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses executeMatchLabelStrings()
	 */
	protected function executeMatchTagLabels()
	{
		$this->executeMatchLabelStrings(
			Tag::ResolveCollection(
				Tag::ResolveDatabase(
					$this->mWrapper ) ) );
		
	} // executeMatchTagLabels.

	 
	/*===================================================================================
	 *	executeMatchTermLabels															*
	 *==================================================================================*/

	/**
	 * Match term labels.
	 *
	 * The method will resolve the appropriate collection and pass it to the
	 * {@link executeMatchLabelStrings()} method.
	 *
	 * @access protected
	 *
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses executeMatchLabelStrings()
	 */
	protected function executeMatchTermLabels()
	{
		$this->executeMatchLabelStrings(
			Term::ResolveCollection(
				Term::ResolveDatabase(
					$this->mWrapper ) ) );
		
	} // executeMatchTermLabels.

	 
	/*===================================================================================
	 *	executeMatchTagByLabel															*
	 *==================================================================================*/

	/**
	 * Match tag by label.
	 *
	 * The method will resolve the appropriate collection and pass it to the
	 * {@link executeMatchLabelObjects()} method.
	 *
	 * @access protected
	 *
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses executeMatchLabelObjects()
	 */
	protected function executeMatchTagByLabel()
	{
		$this->executeMatchLabelObjects(
			Tag::ResolveCollection(
				Tag::ResolveDatabase(
					$this->mWrapper ) ) );
		
	} // executeMatchTagByLabel.

	 
	/*===================================================================================
	 *	executeMatchTermByLabel															*
	 *==================================================================================*/

	/**
	 * Match term by label.
	 *
	 * The method will resolve the appropriate collection and pass it to the
	 * {@link executeMatchLabelObjects()} method.
	 *
	 * @access protected
	 *
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses executeMatchLabelObjects()
	 */
	protected function executeMatchTermByLabel()
	{
		$this->executeMatchLabelObjects(
			Term::ResolveCollection(
				Term::ResolveDatabase(
					$this->mWrapper ) ) );
		
	} // executeMatchTermByLabel.

	 
	/*===================================================================================
	 *	executeGetTagEnumerations														*
	 *==================================================================================*/

	/**
	 * Get tag enumerations.
	 *
	 * The method will perform the following actions:
	 *
	 * <ul>
	 *	<li>Locate tag node.
	 *	<li>Locate all enumerated types or values pointing to that node.
	 *	<li>Load all found elements.
	 *	<li>Recurse nested enumerations.
	 * </ul>
	 *
	 * @access protected
	 */
	protected function executeGetTagEnumerations()
	{
		//
		// Init local storage.
		//
		$tag = $this->offsetGet( kAPI_PARAM_TAG );
		
		//
		// Locate root node.
		//
		$node
			= Node::ResolveCollection(
				Node::ResolveDatabase(
					$this->mWrapper ) )
						->matchOne(
							array( kTAG_TAG => $tag ),
							kQUERY_ASSERT | kQUERY_NID );
		
		//
		// Locate enumerations.
		//
		$edges
			= Edge::ResolveCollection(
				Edge::ResolveDatabase(
					$this->mWrapper ) )
						->matchAll(
							array( kTAG_OBJECT => $node,
								   kTAG_PREDICATE
								   		=> array( '$in'
								   			=> array( kPREDICATE_TYPE_OF,
								   					  kPREDICATE_ENUM_OF ) ) ),
							kQUERY_OBJECT,
							array( kTAG_SUBJECT => TRUE,
								   kTAG_PREDICATE => TRUE ) );
		
		//
		// Reset affected count.
		//
		if( ! $this->offsetExists( kAPI_PARAM_RECURSE ) )
			$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_AFFECTED ]
				= 0;
		
		//
		// Skip records.
		//
		if( $this->offsetExists( kAPI_PAGING_SKIP ) )
		{
			if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
				$edges->skip( $tmp );
		}
		
		//
		// Set limit.
		//
		if( $this->offsetExists( kAPI_PAGING_LIMIT ) )
			$edges->limit( (int) $this->offsetGet( kAPI_PAGING_LIMIT ) );
		
		//
		// Load enumerations.
		//
		if( $edges->count( TRUE ) )
		{
			//
			// Allocate results.
			//
			$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
			
			//
			// Load enumerations.
			//
			$this->executeLoadEnumerations( $edges,
											$this->mResponse[ kAPI_RESPONSE_RESULTS ] );
		
		} // has enumerations.
		
	} // executeGetTagEnumerations.

	 
	/*===================================================================================
	 *	executeGetNodeEnumerations														*
	 *==================================================================================*/

	/**
	 * Get node enumerations.
	 *
	 * The method will perform the following actions:
	 *
	 * <ul>
	 *	<li>Locate all enumerated types or values pointing to node parameter.
	 *	<li>Load all found elements.
	 *	<li>Recurse nested enumerations.
	 * </ul>
	 *
	 * @access protected
	 */
	protected function executeGetNodeEnumerations()
	{
		//
		// Locate root node.
		//
		$node
			= Node::ResolveCollection(
				Node::ResolveDatabase(
					$this->mWrapper ) )
						->matchOne(
							array( kTAG_NID => $this->offsetGet( kAPI_PARAM_NODE ) ),
							kQUERY_ASSERT | kQUERY_NID );
		
		//
		// Locate enumerations.
		//
		$edges
			= Edge::ResolveCollection(
				Edge::ResolveDatabase(
					$this->mWrapper ) )
						->matchAll(
							array( kTAG_OBJECT => $node,
								   kTAG_PREDICATE
								   		=> array( '$in'
								   			=> array( kPREDICATE_TYPE_OF,
								   					  kPREDICATE_ENUM_OF ) ) ),
							kQUERY_OBJECT,
							array( kTAG_SUBJECT => TRUE,
								   kTAG_PREDICATE => TRUE ) );
		
		//
		// Reset affected count.
		//
		if( ! $this->offsetExists( kAPI_PARAM_RECURSE ) )
			$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_AFFECTED ]
				= 0;
		
		//
		// Skip records.
		//
		if( $this->offsetExists( kAPI_PAGING_SKIP ) )
		{
			if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
				$edges->skip( $tmp );
		}
		
		//
		// Set limit.
		//
		if( $this->offsetExists( kAPI_PAGING_LIMIT ) )
			$edges->limit( (int) $this->offsetGet( kAPI_PAGING_LIMIT ) );
		
		//
		// Load enumerations.
		//
		if( $edges->count( TRUE ) )
		{
			//
			// Allocate results.
			//
			$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
			
			//
			// Load enumerations.
			//
			$this->executeLoadEnumerations( $edges,
											$this->mResponse[ kAPI_RESPONSE_RESULTS ] );
		
		} // has enumerations.
		
	} // executeGetNodeEnumerations.

	 
	/*===================================================================================
	 *	executeMatchUnits																*
	 *==================================================================================*/

	/**
	 * Match units.
	 *
	 * The method will perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Cluster by feature</em>: The method will cluster the search criteria by tag
	 *		feature.
	 * </ul>
	 *
	 * @access protected
	 */
	protected function executeMatchUnits()
	{
		//
		// Build filter query.
		//
		$this->resolveFilter();
		
		//
		// Initialise result.
		//
		if( ! array_key_exists( kAPI_RESPONSE_RESULTS, $this->mResponse ) )
			$this->mResponse[ kAPI_RESPONSE_RESULTS ]
				= Array();
		
		//
		// Return grouped results.
		//
		if( $this->offsetExists( kAPI_PARAM_GROUP ) )
			$this->executeGroupUnits( $this->offsetGet( kAPI_PARAM_GROUP ),
									  $this->mResponse[ kAPI_RESPONSE_RESULTS ] );
		
		//
		// Return clustered results.
		//
		else
			$this->executeClusterUnits( $this->mResponse[ kAPI_RESPONSE_RESULTS ] );
		
	} // executeMatchUnits.

	 

} // class ServiceObject.


?>
