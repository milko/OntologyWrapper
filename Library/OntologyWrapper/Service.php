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

	 

} // class ServiceObject.


?>
