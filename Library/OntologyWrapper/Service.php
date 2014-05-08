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
	 * </ul>
	 *
	 * @param string				$theKey				Parameter key.
	 * @param mixed					$theValue			Parameter value.
	 *
	 * @access protected
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
				//
				// Parse parameter.
				//
				switch( $theKey )
				{
					case kAPI_PARAM_PATTERN:
						$this->offsetSet( $theKey, $theValue );
						break;

					case kAPI_PARAM_OPERATOR:
						$this->parseStringMatchOperator( $theValue );
						$this->offsetSet( $theKey, $theValue );
						break;
				
					case kAPI_PAGING_LIMIT:
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
	 * This method will parse the provided string match operator casting the provided value
	 * to an array.
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
				$this->validateMatchTagLabels();
				break;
				
			default:
				parent::validateRequest();
				break;
		}
		
	} // validateRequest.

		

/*=======================================================================================
 *																						*
 *								PROTECTED VALIDATION UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	validateMatchTagLabels															*
	 *==================================================================================*/

	/**
	 * Validate natch tag labels service.
	 *
	 * We check whether all the required parameters are there and set eventual default
	 * parameters.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateMatchTagLabels()
	{
		//
		// Check language.
		//
		if( ! $this->offsetExists( kAPI_REQUEST_LANGUAGE ) )
			$this->offsetSet( kAPI_REQUEST_LANGUAGE, kSTANDARDS_LANGUAGE );
		
		//
		// Check operator.
		//
		if( ! $this->offsetExists( kAPI_PARAM_PATTERN ) )
			$this->offsetSet( kAPI_PARAM_PATTERN,
							  array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE ) );
		
		//
		// Check limit.
		//
		if( ! $this->offsetExists( kAPI_PAGING_LIMIT ) )
			$this->offsetSet( kAPI_PAGING_LIMIT, kSTANDARDS_STRINGS_LIMIT );
		
		//
		// Check pattern.
		//
		if( ! $this->offsetExists( kAPI_PARAM_PATTERN ) )
			throw new \Exception(
				"Missing required pattern parameter." );						// !@! ==>
		
	} // validateMatchTagLabels.

		

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
	 * </ul>
	 *
	 * @access protected
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
				
			default:
				parent::executeRequest();
				break;
		}
		
	} // executeRequest.

	 
	/*===================================================================================
	 *	executeMatchTagLabels															*
	 *==================================================================================*/

	/**
	 * Execute request.
	 *
	 * In this class we handle the following operations:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 * </ul>
	 *
	 * @access protected
	 */
	protected function executeMatchTagLabels()
	{
		//
		// Init local storage.
		//
		$property = (string) kTAG_LABEL;
		$collection = Tag::ResolveCollection( Tag::ResolveDatabase( $this->mWrapper ) );
		
	} // executeMatchTagLabels.

		

/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
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
	 * </ul>
	 *
	 * @access protected
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
				
			default:
				parent::executeRequest();
				break;
		}
		
	} // executeRequest.

	 

} // class ServiceObject.


?>
