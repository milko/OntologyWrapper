<?php

/**
 * Service.php
 *
 * This file contains the definition of the {@link Service} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Wrapper;
use OntologyWrapper\ContainerObject;
use OntologyWrapper\IteratorSerialiser;

/*=======================================================================================
 *																						*
 *										Service.php										*
 *																						*
 *======================================================================================*/

/**
 * API.
 *
 * This file contains the API definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Api.inc.php" );

/**
 * Predicates.
 *
 * This file contains the predicates definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Predicates.inc.php" );

/**
 * Operators.
 *
 * This file contains the match operators definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Operators.inc.php" );

/**
 * Functions.
 *
 * This file contains common function definitions.
 */
require_once( kPATH_LIBRARY_ROOT."/Functions.php" );

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
class Service extends ContainerObject
{
	/**
	 * Wrapper.
	 *
	 * This data member holds the data wrapper.
	 *
	 * @var Wrapper
	 */
	protected $mWrapper = NULL;

	/**
	 * Response.
	 *
	 * This data member holds the service response.
	 *
	 * @var array
	 */
	protected $mResponse = Array();

	/**
	 * Filter.
	 *
	 * This data member holds the service filter.
	 *
	 * @var array
	 */
	protected $mFilter = Array();

		

/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * Instantiate class.
	 *
	 * The constructor accepts a single parameter which is a {@link Wrapper} instance.
	 *
	 * The method will perform the following steps:
	 *
	 * <ul>
	 *	<li><em>Init response status</em>: The response status state will be set to
	 *		{@link kAPI_STATE_IDLE}.
	 *	<li><em>Store wrapper</em>: The provided wrapper will be stored in the object.
	 *	<li><em>Parse request</em>: The {@link parseRequest()} method will parse and load
	 *		the provided parameters.
	 *	<li><em>Validate request</em>: The {@link validateRequest()} method will validate
	 *		the provided parameters.
	 *	<li><em>Set response status</em>: The response status state will be set to
	 *		{@link kAPI_STATE_OK}.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses parseRequest()
	 * @uses validateRequest()
	 * @uses handleException()
	 */
	public function __construct( $theWrapper )
	{
		//
		// TRY BLOCK.
		//
		try
		{
			//
			// Call parent constructor.
			//
			parent::__construct();
		
			//
			// Init status.
			//
			$this->mResponse[ kAPI_RESPONSE_STATUS ]
				= array( kAPI_STATUS_STATE => kAPI_STATE_IDLE );

			//
			// Store wrapper.
			//
			if( $theWrapper instanceof Wrapper )
				$this->mWrapper = $theWrapper;
			else
				throw new \Exception(
					"Invalid wrapper." );										// !@! ==>
			
			//
			// Parse request.
			//
			$this->parseRequest();
			
			//
			// Validate request.
			//
			$this->validateRequest();
			
			//
			// Set ready state.
			//
			$this->mResponse[ kAPI_RESPONSE_STATUS ]
							[ kAPI_STATUS_STATE ] = kAPI_STATE_OK;
		}
		
		//
		// CATCH BLOCK.
		//
		catch( \Exception $error )
		{
			$this->handleException( $error );										// ==>
		}
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *									PUBLIC EXECUTION METHODS							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	handleRequest																	*
	 *==================================================================================*/

	/**
	 * Handle request.
	 *
	 * This method will handle the request and return the result.
	 *
	 * The method will call the protected {@link executeRequest()} method which should
	 * perform what the current service was requested to do, it will then send a header
	 * signalling JSON content and send the json-encoded result.
	 *
	 * <em>Note that this method will exit the script, which means that you cannot do
	 * anything after this method; this also means that the caller does not need to set an
	 * {@link exit()} command</em>.
	 *
	 * The method will initialise the 
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 *
	 * @uses executeRequest()
	 * @uses handleException()
	 */
	public function handleRequest()
	{
		//
		// TRY BLOCK.
		//
		try
		{
			//
			// Log request.
			//
			if( $this->offsetGet( kAPI_PARAM_LOG_REQUEST ) )
				$this->mResponse[ kAPI_RESPONSE_REQUEST ]
					= $this->getArrayCopy();
			
			//
			// Execute request.
			//
			$this->executeRequest();
			
			//
			// Send header.
			//
			header( 'Content-type: application/json' );
		
			exit( JsonEncode( $this->mResponse ) );								// ==>
		}
		
		//
		// CATCH BLOCK.
		//
		catch( \Exception $error )
		{
			$this->handleException( $error );										// ==>
		}
		
	} // handleRequest.

		

/*=======================================================================================
 *																						*
 *							PROTECTED REQUEST PARSING INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseRequest																	*
	 *==================================================================================*/

	/**
	 * Parse request.
	 *
	 * This method will parse the request provided in the $_REQUEST global parameter, it
	 * will place all recognised parameters in the object's array, ignoring any unknown
	 * option.
	 *
	 * The method takes advantage of the following protected methods:
	 *
	 * <ul>
	 *	<li><tt>{@link parseOperation()}</tt>: This method is responsible of parsing the
	 *		operation.
	 *	<li><tt>{@link parseLanguage()}</tt>: This method is responsible of parsing the
	 *		language.
	 *	<li><tt>{@link parseParameters()}</tt>: This method is responsible of parsing the
	 *		additional parameters.
	 * </ul>
	 *
	 * The method will raise an exception if the operation is missing.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses parseOperation()
	 * @uses parseLanguage()
	 * @uses parseParameters()
	 */
	protected function parseRequest()
	{
		//
		// Check operation.
		//
		if( ! array_key_exists( kAPI_REQUEST_OPERATION, $_REQUEST ) )
			throw new \Exception(
				"Missing service operation." );									// !@! ==>
	
		//
		// Parse operation.
		//
		$this->parseOperation();
	
		//
		// Parse language.
		//
		$this->parseLanguage();
		
		//
		// Parse parameters.
		//
		$this->parseParameters();
		
	} // parseRequest.

	 
	/*===================================================================================
	 *	parseOperation																	*
	 *==================================================================================*/

	/**
	 * Parse operation.
	 *
	 * The duty of this method is to parse the provided operation and set it into the
	 * current object.
	 *
	 * In derived classes you should parse your custom operations or call the parent method,
	 * in this class we only parse the following operations:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_PING}</tt>: Ping.
	 *	<li><tt>{@link kAPI_OP_LIST_CONSTANTS}</tt>: List parameter constants.
	 *	<li><tt>{@link kAPI_OP_LIST_OPERATORS}</tt>: List operator parameters.
	 *	<li><tt>{@link kAPI_OP_LIST_REF_COUNTS}</tt>: List reference count parameters.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 *	<li><tt>{@link kAPI_OP_ADD_USER}</tt>: Add user.
	 *	<li><tt>{@link kAPI_OP_GET_USER}</tt>: Get user.
	 * </ul>
	 *
	 * If the operation is not recognised, the method will raise an exception.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_REQUEST_OPERATION
	 */
	protected function parseOperation()
	{
		//
		// Parse operation.
		//
		switch( $op = $_REQUEST[ kAPI_REQUEST_OPERATION ] )
		{
			case kAPI_OP_PING:
			case kAPI_OP_LIST_CONSTANTS:
			case kAPI_OP_LIST_OPERATORS:
			case kAPI_OP_LIST_REF_COUNTS:
			case kAPI_OP_MATCH_TAG_LABELS:
			case kAPI_OP_MATCH_TERM_LABELS:
			case kAPI_OP_MATCH_TAG_BY_LABEL:
			case kAPI_OP_MATCH_TERM_BY_LABEL:
			case kAPI_OP_GET_TAG_ENUMERATIONS:
			case kAPI_OP_GET_NODE_ENUMERATIONS:
			case kAPI_OP_MATCH_UNITS:
			case kAPI_OP_GET_UNIT:
			case kAPI_OP_ADD_USER:
			case kAPI_OP_GET_USER:
				$this->offsetSet( kAPI_REQUEST_OPERATION, $op );
				break;
			
			default:
				throw new \Exception(
					"Invalid or unsupported operation: "
				   ."[$op]." );													// !@! ==>
		}
		
	} // parseOperation.

	 
	/*===================================================================================
	 *	parseLanguage																	*
	 *==================================================================================*/

	/**
	 * Parse language.
	 *
	 * The duty of this method is to parse the provided language parameter, if the parameter
	 * was not sent, the method will set the default language, {@link kSTANDARDS_LANGUAGE}.
	 *
	 * @access protected
	 *
	 * @see kAPI_REQUEST_LANGUAGE
	 */
	protected function parseLanguage()
	{
		//
		// Set language.
		//
		$this->offsetSet( kAPI_REQUEST_LANGUAGE,
						  ( array_key_exists( kAPI_REQUEST_LANGUAGE, $_REQUEST ) )
						  ? $_REQUEST[ kAPI_REQUEST_LANGUAGE ]
						  : kSTANDARDS_LANGUAGE );
		
	} // parseLanguage.

	 
	/*===================================================================================
	 *	parseParameters																	*
	 *==================================================================================*/

	/**
	 * Parse parameters.
	 *
	 * The duty of this method is to parse the additional parameters provided in the
	 * {@link kAPI_REQUEST_PARAMETERS} argument of the service.
	 *
	 * Each parameter will be fed to the protected {@link parseParameter()} method which
	 * derived classes can overload to handle custom values.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_REQUEST_PARAMETERS
	 *
	 * @uses parseParameter()
	 */
	protected function parseParameters()
	{
		//
		// Check operation.
		//
		if( array_key_exists( kAPI_REQUEST_PARAMETERS, $_REQUEST ) )
		{
			//
			// Decode parameters.
			//
			$params = JsonDecode( $_REQUEST[ kAPI_REQUEST_PARAMETERS ] );
			if( is_array( $params ) )
			{
				//
				// Parse single parameters.
				//
				foreach( $params as $key => $value )
					$this->parseParameter( $key, $value );
			
			} // Parameters are an array.
			
			else
				throw new \Exception(
					"Invalid parameters format: "
				   ."expecting an array." );									// !@! ==>
		
		} // Has parameters.
		
	} // parseParameters.

	 
	/*===================================================================================
	 *	parseParameter																	*
	 *==================================================================================*/

	/**
	 * Parse parameter.
	 *
	 * The duty of this method is to parse the provided single parameter, the method expects
	 * the parameter key and value.
	 *
	 * Both the key and the value are provided as references, this may allow derived classes
	 * to transform parameters.
	 *
	 * Derived classes should handle their custom parameters or call the parent method, any
	 * parameter which is not handled will be ignored.
	 *
	 * @param string				$theKey				Parameter key.
	 * @param mixed					$theValue			Parameter value.
	 *
	 * @access protected
	 */
	protected function parseParameter( &$theKey, &$theValue )
	{
		//
		// Parse parameter.
		//
		switch( $theKey )
		{
			case kAPI_PARAM_PATTERN:
			case kAPI_PARAM_TAG:
			case kAPI_PARAM_NODE:
			case kAPI_PARAM_DOMAIN:
			case kAPI_PARAM_DATA:
			case kAPI_PARAM_SHAPE_OFFSET:
				if( strlen( $theValue ) )
					$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PARAM_ID:
				if( is_array( $theValue )
				 || strlen( $theValue ) )
					$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PARAM_SHAPE:
				if( is_array( $theValue ) )
					$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PARAM_OPERATOR:
				$this->parseStringMatchOperator( $theValue );
				$this->offsetSet( $theKey, $theValue );
				break;
		
			case kAPI_PARAM_CRITERIA:
				if( is_array( $theValue ) )
					$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PARAM_REF_COUNT:
				if( is_array( $theValue ) )
					$this->offsetSet( $theKey, $theValue );
				elseif( strlen( $theValue ) )
				{
					$tmp = explode( ',', $theValue );
					$this->offsetSet( $theKey, $tmp );
				}
				break;
			
			case kAPI_PARAM_GROUP:
				if( ( is_array( $theValue )
				   && (count( $theValue ) == 1)
				   && ($theValue[ 0 ] == kTAG_DOMAIN) )
				 || ($theValue == kTAG_DOMAIN) )
					$theValue = Array();
				$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PAGING_SKIP:
				$theValue = (int) $theValue;
				$this->offsetSet( $theKey, $theValue );
				$this->mResponse[ kAPI_RESPONSE_PAGING ][ $theKey ] = $theValue;
				break;

			case kAPI_PAGING_LIMIT:
				$theValue = (int) $theValue;
				$this->offsetSet( $theKey, $theValue );
				$this->mResponse[ kAPI_RESPONSE_PAGING ][ $theKey ] = $theValue;
				if( ! $this->offsetExists( kAPI_PAGING_SKIP ) )
				{
					$this->offsetSet( kAPI_PAGING_SKIP, 0 );
					$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_SKIP ] = 0;
				}
				break;

			case kAPI_PARAM_LOG_REQUEST:
			case kAPI_PARAM_LOG_TRACE:
			case kAPI_PARAM_RECURSE:
				$theValue = (boolean) $theValue;
				$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PARAM_OBJECT:
				if( is_array( $theValue ) )
				{
					$object = new User( $this->mWrapper );
					foreach( $theValue as $key => $value )
						$object[ $key ] = $value;
					$this->offsetSet( $theKey, $object );
				}
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
	 * This method will be called once all parameters have been parsed and set in the
	 * object, its duty is to validate the request; this activity involves ensuring all
	 * required parameters are there and perform eventual other modifications.
	 *
	 * In this class we check the following operations:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_PING}</tt>: Ping.
	 *	<li><tt>{@link kAPI_OP_LIST_CONSTANTS}</tt>: List parameter constants.
	 *	<li><tt>{@link kAPI_OP_LIST_OPERATORS}</tt>: List operator parameters.
	 *	<li><tt>{@link kAPI_OP_LIST_REF_COUNTS}</tt>: List reference count parameters.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 *	<li><tt>{@link kAPI_OP_ADD_USER}</tt>: Add user.
	 *	<li><tt>{@link kAPI_OP_GET_USER}</tt>: Get user.
	 * </ul>
	 *
	 * Any unrecognised operation will raise an exception.
	 *
	 * Derived classes should match custom operations or call the parent method.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_OP_PING
	 */
	protected function validateRequest()
	{
		//
		// Parse by operation.
		//
		switch( $op = $this->offsetGet( kAPI_REQUEST_OPERATION ) )
		{
			case kAPI_OP_PING:
			case kAPI_OP_LIST_CONSTANTS:
			case kAPI_OP_LIST_OPERATORS:
			case kAPI_OP_LIST_REF_COUNTS:
				break;
			
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
				
			case kAPI_OP_GET_UNIT:
				$this->validateGetUnit();
				break;
				
			case kAPI_OP_GET_USER:
				$this->validateGetUser();
				break;
				
			case kAPI_OP_ADD_USER:
				$this->validateAddUser();
				break;
			
			default:
				throw new \Exception(
					"Invalid or unsupported operation: "
				   ."[$op]." );													// !@! ==>
		}
		
	} // validateRequest.

		

/*=======================================================================================
 *																						*
 *							PROTECTED VALIDATION UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	validateMatchLabelStrings														*
	 *==================================================================================*/

	/**
	 * Validate match label strings services.
	 *
	 * This method will validate all service operations which match label strings, the
	 * method will perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Validate operator</em>: If the parameter is missing, it will set it by
	 *		default as "contains case and accent insensitive"; if the parameter is set, it
	 *		will ensure that it is conformant with the requested operation.
	 *	<li><em>Check limit</em>: If the parameter is missing, the method will raise an
	 *		exception; if its value is larger than the {@link kSTANDARDS_STRINGS_LIMIT}
	 *		constant, it will set it to that value.
	 *	<li><em>Check pattern</em>: If the parameter is missing, the method will raise an
	 *		exception.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_OPERATOR kAPI_PAGING_LIMIT kAPI_PARAM_PATTERN
	 * @see kOPERATOR_CONTAINS kOPERATOR_NOCASE
	 * @see kSTANDARDS_STRINGS_LIMIT
	 *
	 * @uses validateStringMatchOperator()
	 */
	protected function validateMatchLabelStrings()
	{
		//
		// Check operator.
		//
		$tmp = $this->offsetGet( kAPI_PARAM_OPERATOR );
		$this->validateStringMatchOperator( $tmp );
		$this->offsetSet( kAPI_PARAM_OPERATOR, $tmp );
		
		//
		// Check limit.
		//
		if( $this->offsetExists( kAPI_PAGING_LIMIT ) )
		{
			if( ($tmp = (int) $this->offsetGet( kAPI_PAGING_LIMIT ))
					> kSTANDARDS_STRINGS_LIMIT )
				$this->offsetSet( kAPI_PAGING_LIMIT, kSTANDARDS_STRINGS_LIMIT );
		}
		else
			throw new \Exception(
				"Missing required limits parameter." );							// !@! ==>
		
		//
		// Check pattern.
		//
		if( ! $this->offsetExists( kAPI_PARAM_PATTERN ) )
			throw new \Exception(
				"Missing required pattern parameter." );						// !@! ==>
		
		//
		// Check reference count.
		//
		if( $this->offsetExists( kAPI_PARAM_REF_COUNT ) )
			$this->validateCollection( $this->offsetGet( kAPI_PARAM_REF_COUNT ) );
		
	} // validateMatchLabelStrings.

	 
	/*===================================================================================
	 *	validateGetTagEnumerations														*
	 *==================================================================================*/

	/**
	 * Validate get tag enumerations service.
	 *
	 * This method will validate all service operations which return tag enumerated sets,
	 * the method will perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Check tag</em>: If the parameter is missing, the method will raise an
	 *		exception.
	 *	<li><em>Validate tag</em>: The method will check whether the provided tag reference
	 *		is valid, if that is not the case, the method will raise an exception.
	 *	<li><em>Check limit</em>: The limit will be reset if the recurse parameter is
	 *		provided, if not, the limit is required and will be set to the
	 *		{@link kSTANDARDS_ENUMS_LIMIT} constant if larger.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_TAG
	 */
	protected function validateGetTagEnumerations()
	{
		//
		// Check parameter.
		//
		if( ! $this->offsetExists( kAPI_PARAM_TAG ) )
			throw new \Exception(
				"Missing required tag parameter." );							// !@! ==>
		
		//
		// Get parameter.
		//
		$tag = $this->offsetGet( kAPI_PARAM_TAG );
		
		//
		// Handle string offsets.
		//
		if( (! is_int( $tag ))
		 && (! ctype_digit( $tag )) )
			$tag = $this->mWrapper->getSerial( $tag, TRUE );
		
		//
		// Get tag object.
		//
		$object = $this->mWrapper->getObject( $tag, TRUE );
		
		//
		// Get tag native identifier.
		//
		$tag = $object[ kTAG_NID ];
		
		//
		// Set parameter.
		//
		$this->offsetSet( kAPI_PARAM_TAG, $tag );
		
		//
		// Check recursion and limits.
		//
		$this->validateRecurseFlag( kSTANDARDS_ENUMS_LIMIT );
		
	} // validateGetTagEnumerations.

	 
	/*===================================================================================
	 *	validateGetNodeEnumerations														*
	 *==================================================================================*/

	/**
	 * Validate get node enumerations service.
	 *
	 * This method will validate all service operations which return node enumerated sets,
	 * the method will perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Check node</em>: If the parameter is missing, the method will raise an
	 *		exception.
	 *	<li><em>Check limit</em>: The limit will be reset if the recurse parameter is
	 *		provided, if not, the limit is required and will be set to the
	 *		{@link kSTANDARDS_ENUMS_LIMIT} constant if larger.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_NODE
	 */
	protected function validateGetNodeEnumerations()
	{
		//
		// Check parameter.
		//
		if( ! $this->offsetExists( kAPI_PARAM_NODE ) )
			throw new \Exception(
				"Missing required node parameter." );							// !@! ==>
		
		//
		// Check recursion and limits.
		//
		$this->validateRecurseFlag( kSTANDARDS_ENUMS_LIMIT );
		
	} // validateGetNodeEnumerations.

	 
	/*===================================================================================
	 *	validateMatchUnits																*
	 *==================================================================================*/

	/**
	 * Validate match units service.
	 *
	 * This method will validate all service operations which match units using a list of
	 * criteria, the method will perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Check criteria</em>: The method will check whether the criteria was
	 *		provided.
	 *	<li><em>Validate group</em>: If the group parameter was provided, we clear the
	 *		results type parameter.
	 *	<li><em>Validate results type</em>: If the group parameter was not provided, we
	 *		assert the group parameter.
	 *	<li><em>Validate shape</em>: If that parameter is provided:
	 *	 <ul>
	 *		<li>Check shape value format.
	 *		<li>Check shape structure.
	 *		<li>Check shape type; if point, we assert the distance parameter.
	 *	 </ul>
	 *	<li><em>Assert limits</em>: If the results type was provided, we assert this
	 *		parameter.
	 *	<li><em>Check criteria parameters</em>: The method will check whether all the
	 *		required criteria parameters are there.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_NODE
	 */
	protected function validateMatchUnits()
	{
		//
		// Get criteria.
		//
		$criteria = ( $this->offsetExists( kAPI_PARAM_CRITERIA ) )
				  ? $this->offsetGet( kAPI_PARAM_CRITERIA )
				  : Array();
		
		//
		// Validate group.
		//
		if( $this->offsetExists( kAPI_PARAM_GROUP ) )
		{
			//
			// Reset results type.
			//
			$this->offsetUnset( kAPI_PARAM_DOMAIN );
			$this->offsetUnset( kAPI_PARAM_DATA );
	
			//
			// Reset limits.
			//
			$this->offsetUnset( kAPI_PAGING_SKIP );
			$this->offsetUnset( kAPI_PAGING_LIMIT );
			
			//
			// Get value.
			//
			$tmp = $this->offsetGet( kAPI_PARAM_GROUP );
			
			//
			// Convert group into an array.
			//
			if( ! is_array( $tmp ) )
				$tmp = array( $tmp );
			
			//
			// Save groups.
			//
			$groups = $tmp;
			
			//
			// Set default group.
			//
			if( ! count( $tmp ) )
				$tmp[] = kTAG_DOMAIN;
			
			//
			// Check group elements.
			//
			foreach( $tmp as $key => $value )
			{
				//
				// Serialise tag native references.
				//
				$tmp[ $key ]
					= ( (! is_int( $value ))
				   && (! ctype_digit( $value )) )
					? $this->mWrapper->getSerial( $value, TRUE )
					: (int) $value;
				$type
					= $this->mWrapper
						->getObject( $tmp[ $key ], TRUE )[ kTAG_DATA_TYPE ];
				
				//
				// Assert enumerated set.
				//
				if( ($type != kTYPE_SET)
				 && ($type != kTYPE_ENUM) )
					throw new \Exception(
						"Group element [ "
					   .$tmp[ $key ]
					   ."] must be an enumerated set." );						// !@! ==>
			}
			
			//
			// Add domain.
			//
			if( ! in_array( kTAG_DOMAIN, $tmp ) )
				$tmp[] = kTAG_DOMAIN;
			
			//
			// Assert domain.
			//
			elseif( $tmp[ count( $tmp ) - 1 ] != kTAG_DOMAIN )
				throw new \Exception(
					"Domain must be last group element." );						// !@! ==>
			
			//
			// Update parameter.
			//
			$this->offsetSet( kAPI_PARAM_GROUP, $tmp );
			
			//
			// Collect untracked offsets.
			//
			$untracked = array_merge( UnitObject::InternalOffsets(),
									  UnitObject::ExternalOffsets(),
									  UnitObject::DynamicOffsets() );
			
			//
			// Add groups to criteria.
			//
			$keys = array_keys( $groups );
			foreach( $keys as $key )
			{
				//
				// Skip untracked offsets.
				//
				if( in_array( $tmp[ $key ], $untracked ) )
					continue;												// =>
				
				//
				// Skip existing.
				//
				if( array_key_exists( $groups[ $key ], $criteria ) )
					continue;												// =>
				
				//
				// Add to criteria.
				//
				$criteria[ $groups[ $key ] ]
					= array( kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM );
			
			} // Iterating groups.
	
		} // Provided group.
		
		//
		// Handle ungrouped results.
		//
		else
		{
			//
			// Assert result type.
			//
			if( ! $this->offsetExists( kAPI_PARAM_DOMAIN ) )
				throw new \Exception(
					"Missing domain parameter." );								// !@! ==>
	
			//
			// Assert limits.
			//
			if( ! $this->offsetExists( kAPI_PAGING_LIMIT ) )
				throw new \Exception(
					"Missing paging limits parameter." );						// !@! ==>
	
			//
			// Assert result kind.
			//
			if( ! $this->offsetExists( kAPI_PARAM_DATA ) )
				throw new \Exception(
					"Missing results kind parameter." );						// !@! ==>
			else
			{
				switch( $tmp = $this->offsetGet( kAPI_PARAM_DATA ) )
				{
					case kAPI_RESULT_ENUM_DATA_MARKER:
						//
						// Assert shape offset.
						//
						if( ! $this->offsetExists( kAPI_PARAM_SHAPE_OFFSET ) )
							throw new \Exception(
								"Missing shape offset." );						// !@! ==>
						//
						// Normalise limit.
						//
						if( $this->offsetGet( kAPI_PAGING_LIMIT ) > kSTANDARDS_MARKERS_MAX )
							$this->offsetSet( kAPI_PAGING_LIMIT, kSTANDARDS_MARKERS_MAX );
						break;
						
					case kAPI_RESULT_ENUM_DATA_COLUMN:
					case kAPI_RESULT_ENUM_DATA_RECORD:
					case kAPI_RESULT_ENUM_DATA_FORMAT:
						//
						// Normalise limit.
						//
						if( $this->offsetGet( kAPI_PAGING_LIMIT ) > kSTANDARDS_UNITS_MAX )
							$this->offsetSet( kAPI_PAGING_LIMIT, kSTANDARDS_UNITS_MAX );
						break;
					
					default:
						throw new \Exception(
							"Invalid result type [$tmp]." );					// !@! ==>
						break;
				}
			}
	
		} // Group not provided.
		
		//
		// Validate shape offset.
		//
		if( $this->offsetExists( kAPI_PARAM_SHAPE_OFFSET ) )
		{
			//
			// Get shape offset.
			//
			$shape = $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET );
			
			//
			// Handle numeric offsets.
			//
			if( is_int( $shape )
			 || ctype_digit( $shape ) )
				$shape = (int) $shape;
			
			//
			// Handle textual offsets.
			//
			else
				$this->offsetSet(
					kAPI_PARAM_SHAPE_OFFSET,
					$this->mWrapper->getSerial(
						$shape,
						TRUE ) );
		
		} // Provided shape offset.
	
		//
		// Validate shape.
		//
		if( $this->offsetExists( kAPI_PARAM_SHAPE ) )
		{
			//
			// Check shape offset.
			//
			if( ! $this->offsetExists( kAPI_PARAM_SHAPE_OFFSET ) )
				throw new \Exception(
					"Missing shape offset reference parameter." );				// !@! ==>
			
			//
			// Get shape.
			//
			$shape = $this->offsetGet( kAPI_PARAM_SHAPE );
		
			//
			// Check shape format.
			//
			$this->validateShape( $shape );
			
			//
			// Add shape to criteria.
			//
			$criteria[ (string) $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) ]
				= array( kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_SHAPE,
						 kAPI_PARAM_SHAPE => $shape );
	
		} // Provided shape.
		
		//
		// Add shape offset to criteria.
		//
		elseif( $this->offsetExists( kAPI_PARAM_SHAPE_OFFSET )
			 && (! $this->offsetExists( kAPI_PARAM_GROUP )) )
			$criteria[ (string) $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) ]
				= array( kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_SHAPE );
		
		//
		// Update criteria.
		//
		if( is_array( $criteria ) )
			$this->offsetSet( kAPI_PARAM_CRITERIA, $criteria );
		
		//
		// Require criteria.
		//
		else
			throw new \Exception(
				"Missing search criteria." );									// !@! ==>
		
		//
		// Validate criteria.
		//
		$this->validateSearchCriteria();
		
	} // validateMatchUnits.

	 
	/*===================================================================================
	 *	validateGetUnit																	*
	 *==================================================================================*/

	/**
	 * Validate get unit service.
	 *
	 * This method will validate all service operations which match a single unit using an
	 * identifier, the method will perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Check identifier</em>: The method will check whether the identifier was
	 *		provided.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_NODE
	 */
	protected function validateGetUnit()
	{
		//
		// Validate identifier.
		//
		if( ! $this->offsetExists( kAPI_PARAM_ID ) )
			throw new \Exception(
				"Missing unit identifier parameter." );							// !@! ==>

		//
		// Assert result kind.
		//
		if( ! $this->offsetExists( kAPI_PARAM_DATA ) )
			throw new \Exception(
				"Missing results kind parameter." );							// !@! ==>
		else
		{
			//
			// Validate by format type.
			//
			switch( $tmp = $this->offsetGet( kAPI_PARAM_DATA ) )
			{
				case kAPI_RESULT_ENUM_DATA_MARKER:
					//
					// Assert shape offset.
					//
					if( ! $this->offsetExists( kAPI_PARAM_SHAPE_OFFSET ) )
						throw new \Exception(
							"Missing shape offset." );							// !@! ==>
					//
					// Normalise limit.
					//
					if( $this->offsetGet( kAPI_PAGING_LIMIT ) > kSTANDARDS_MARKERS_MAX )
						$this->offsetSet( kAPI_PAGING_LIMIT, kSTANDARDS_MARKERS_MAX );
					break;
					
				case kAPI_RESULT_ENUM_DATA_RECORD:
				case kAPI_RESULT_ENUM_DATA_FORMAT:
					//
					// Normalise limit.
					//
					if( $this->offsetGet( kAPI_PAGING_LIMIT ) > kSTANDARDS_UNITS_MAX )
						$this->offsetSet( kAPI_PAGING_LIMIT, kSTANDARDS_UNITS_MAX );
					break;
				
				default:
					throw new \Exception(
						"Invalid result type [$tmp]." );						// !@! ==>
					break;
			}
		}
		
	} // validateGetUnit.

	 
	/*===================================================================================
	 *	validateGetUser																	*
	 *==================================================================================*/

	/**
	 * Validate get user service.
	 *
	 * This method will call the unit validation process, then check whether the identifier
	 * parameter has the correct format.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateGetUser()
	{
		//
		// Call unit validation process.
		//
		$this->validateGetUnit();

		//
		// Validate identifier.
		//
		$param = $this->offsetExists( kAPI_PARAM_ID );
		if( is_array( $param )
		 && (count( $param ) != 2) )
			throw new \Exception(
				"Invalid user identifier parameter format." );					// !@! ==>
		
	} // validateGetUser.

	 
	/*===================================================================================
	 *	validateAddUser																	*
	 *==================================================================================*/

	/**
	 * Validate add user service.
	 *
	 * This method will ensure the user object was set.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_NODE
	 */
	protected function validateAddUser()
	{
		//
		// Validate identifier.
		//
		if( ! $this->offsetExists( kAPI_PARAM_OBJECT ) )
			throw new \Exception(
				"Missing or invalid user object." );							// !@! ==>
		
	} // validateAddUser.

	 
	/*===================================================================================
	 *	validateStringMatchOperator														*
	 *==================================================================================*/

	/**
	 * Validate string match operator.
	 *
	 * This method will validate the operator parameter passed to all service operations
	 * which match strings, the method will perform the following operations:
	 *
	 * <ul>
	 *	<li>Assert that the parameter is an array.
	 *	<li>Assert that the array is not empty.
	 *	<li>Assert that the parameter contains no more than one main operator.
	 *	<li>Assert that the parameter contains at least one main operator.
	 * </ul>
	 *
	 * If the operator is missing we will assume by default contains case and accent
	 * insensitive.
	 *
	 * Any error will raise an exception.
	 *
	 * @param string				$theValue			Operator value.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kOPERATOR_EQUAL kOPERATOR_EQUAL_NOT kOPERATOR_PREFIX
	 * @see kOPERATOR_CONTAINS kOPERATOR_SUFFIX kOPERATOR_REGEX
	 */
	protected function validateStringMatchOperator( &$theValue )
	{
		//
		// Check if set.
		//
		if( $theValue !== NULL )
		{
			//
			// Check format.
			//
			if( ! is_array( $theValue ) )
				throw new \Exception(
					"Invalid operator parameter format." );						// !@! ==>
		
			//
			// Check count.
			//
			if( ! count( $theValue ) )
				throw new \Exception(
					"Empty operator parameter." );								// !@! ==>
		
			//
			// Init local storage.
			//
			$opts = array( kOPERATOR_EQUAL, kOPERATOR_EQUAL_NOT, kOPERATOR_PREFIX,
						   kOPERATOR_CONTAINS, kOPERATOR_SUFFIX/*, kOPERATOR_REGEX*/ );
		
			//
			// Check operator.
			//
			$main = array_intersect( $opts, $theValue );
			if( count( $main ) > 1 )
				throw new \Exception(
					"Too many operator options." );								// !@! ==>
			if( ! count( $main ) )
				throw new \Exception(
					"Missing main operator option in parameter." );				// !@! ==>
		
		} // Provided.
		
		//
		// Set default operator.
		//
		else
			$theValue = array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE );
		
	} // validateStringMatchOperator.

	 
	/*===================================================================================
	 *	validateRangeMatchOperator														*
	 *==================================================================================*/

	/**
	 * Validate range match operator.
	 *
	 * This method will validate the operator parameter passed to all service operations
	 * which match ranges, the method will perform the following operations:
	 *
	 * <ul>
	 *	<li>Assert that the parameter is an array.
	 *	<li>Assert that the array is not empty.
	 *	<li>Assert that the parameter contains no more than one main operator.
	 *	<li>Assert that the parameter contains at least one main operator.
	 * </ul>
	 *
	 * Any error will raise an exception.
	 *
	 * @param string				$theValue			Operator value.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kOPERATOR_IRANGE kOPERATOR_ERANGE
	 */
	protected function validateRangeMatchOperator( &$theValue )
	{
		//
		// Check if set.
		//
		if( $theValue !== NULL )
		{
			//
			// Check format.
			//
			if( ! is_array( $theValue ) )
				throw new \Exception(
					"Invalid operator parameter format." );						// !@! ==>
		
			//
			// Check count.
			//
			if( ! count( $theValue ) )
				throw new \Exception(
					"Empty operator parameter." );								// !@! ==>
		
			//
			// Init local storage.
			//
			$opts = array( kOPERATOR_IRANGE, kOPERATOR_ERANGE );
		
			//
			// Check operator.
			//
			$main = array_intersect( $opts, $theValue );
			if( count( $main ) > 1 )
				throw new \Exception(
					"Too many operator options." );								// !@! ==>
			if( ! count( $main ) )
				throw new \Exception(
					"Missing main operator option in parameter." );				// !@! ==>
		
		} // Provided.
		
		//
		// Set default operator.
		//
		else
			$theValue = array( kOPERATOR_IRANGE );
		
	} // validateRangeMatchOperator.

	 
	/*===================================================================================
	 *	validateCollection																*
	 *==================================================================================*/

	/**
	 * Validate collection reference.
	 *
	 * This method will validate the collection parameter.
	 *
	 * Any error will raise an exception.
	 *
	 * This method expects the collection parameter to be an array if provided.
	 *
	 * @param array					$theValue			Collection.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_COLLECTION_TAG kAPI_PARAM_COLLECTION_TERM
	 * @see kAPI_PARAM_COLLECTION_NODE kAPI_PARAM_COLLECTION_EDGE
	 * @see kAPI_PARAM_COLLECTION_UNIT kAPI_PARAM_COLLECTION_ENTITY
	 */
	protected function validateCollection( $theValue )
	{
		//
		// Check if set.
		//
		if( $theValue !== NULL )
		{
			//
			// Init local storage.
			//
			$collections
				= array( kAPI_PARAM_COLLECTION_TAG, kAPI_PARAM_COLLECTION_TERM,
						 kAPI_PARAM_COLLECTION_NODE, kAPI_PARAM_COLLECTION_EDGE,
						 kAPI_PARAM_COLLECTION_UNIT, kAPI_PARAM_COLLECTION_ENTITY );
			
			//
			// Iterate values.
			//
			foreach( $theValue as $collection )
			{
				if( ! in_array( $collection, $collections ) )
					throw new \Exception(
						"Invalid or unsupported collection "
					   ."[$collection]." );										// !@! ==>
			}
		
		} // Provided.
		
	} // validateCollection.

	 
	/*===================================================================================
	 *	validateRecurseFlag																*
	 *==================================================================================*/

	/**
	 * Validate recurse flag.
	 *
	 * This method will validate the recurse flag parameter passed to all service
	 * operations which may recursively traverse structures.
	 *
	 * The method will remove paging if the {@link kAPI_PARAM_RECURSE} flag was provided;
	 * if the flag was not provided, the method will set the default limits to the provided
	 * maximum value, or raise an exception if both the flag and limits are missing.
	 *
	 * Any error will raise an exception.
	 *
	 * @param int					$theLimit			Default limits.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateRecurseFlag( $theLimit )
	{
		//
		// Handle flag.
		//
		if( $this->offsetExists( kAPI_PARAM_RECURSE ) )
		{
			//
			// Reset paging.
			//
			$this->offsetUnset( kAPI_PAGING_SKIP );
			$this->offsetUnset( kAPI_PAGING_LIMIT );
		}
		
		//
		// Handle missing limits.
		//
		elseif( $this->offsetExists( kAPI_PAGING_LIMIT ) )
		{
			//
			// Handle limits overflow.
			//
			if( ((int) $this->offsetGet( kAPI_PAGING_LIMIT )) > $theLimit )
				$this->offsetSet( kAPI_PAGING_LIMIT, $theLimit );
		}
		
		//
		// Require limits.
		//
		else
			throw new \Exception(
				"Missing required limits parameter." );							// !@! ==>
		
	} // validateRecurseFlag.

	 
	/*===================================================================================
	 *	validateShape																	*
	 *==================================================================================*/

	/**
	 * Validate shape
	 *
	 * This method will validate the shape parameter passed to all service operations which
	 * should select objects based on geographic queries.
	 *
	 * Any error will raise an exception.
	 *
	 * @param array					$theValue			Shape value.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_SHAPE
	 */
	protected function validateShape( &$theValue )
	{
		//
		// Check if set.
		//
		if( $theValue !== NULL )
		{
			//
			// Check format.
			//
			if( ! is_array( $theValue ) )
				throw new \Exception(
					"Invalid shape parameter: "
				   ."the value is not an array." );								// !@! ==>
		
			//
			// Check shape structure.
			//
			if( (! array_key_exists( kTAG_TYPE, $theValue ))
			 || (! array_key_exists( kTAG_GEOMETRY, $theValue ))
			 || (! is_array( $theValue[ kTAG_GEOMETRY ] )) )
				throw new \Exception(
					"Invalid shape geometry." );								// !@! ==>
			
			//
			// Check shape contents.
			//
			$geom = $theValue[ kTAG_GEOMETRY ];
			switch( $type = $theValue[ kTAG_TYPE ] )
			{
				//
				// Points and circles.
				//
				case 'Point':
				case 'Circle':
					//
					// Check geometry elements.
					//
					if( count( $geom ) != 2 )
						throw new \Exception(
							"Geometry for [$type] "
						   ."must have two elements." );						// !@! ==>
					
					//
					// Check coordinates structure.
					//
					if( ! is_array( $geom[ 0 ] ) )
						throw new \Exception(
							"The first element of [$type] "
						   ."must be an array." );								// !@! ==>
					
					//
					// Check coordinates.
					//
					if( count( $geom[ 0 ] ) != 2 )
						throw new \Exception(
							"The first element of [$type] "
						   ."must contain a pair of coordinates." );			// !@! ==>
					
					//
					// Cast coordinates.
					//
					$geom[ 0 ][ 0 ] = (double) $geom[ 0 ][ 0 ];
					$geom[ 0 ][ 1 ] = (double) $geom[ 0 ][ 1 ];
					
					//
					// Cast distance.
					//
					if( $type == 'Point' )
						$geom[ 1] = (int) $geom[ 1 ];
					
					break;
							
				case 'Rect':
					//
					// Check geometry elements.
					//
					if( count( $geom ) != 2 )
						throw new \Exception(
							"Geometry for [$type] "
						   ."must have two elements." );						// !@! ==>
					
					//
					// Check coordinates structure.
					//
					if( ! is_array( $geom[ 0 ] ) )
						throw new \Exception(
							"The first element of [$type] "
						   ."must contain the bottom left coordinates." );		// !@! ==>
					
					if( ! is_array( $geom[ 1 ] ) )
						throw new \Exception(
							"The second element of [$type] "
						   ."must contain the upper right coordinates." );		// !@! ==>
					
					//
					// Cast coordinates.
					//
					for( $i = 0; $i < 2; $i ++ )
					{
						for( $j = 0; $j < 2; $j ++ )
							$geom[ $i ][ $j ]
								= (double) $geom[ $i ][ $j ];
					}
					
					break;
					
				case 'Polygon':
					//
					// Traverse geometry.
					//
					for( $i = 0; $i < count( $geom ); $i++ )
					{
						//
						// Check ring.
						//
						if( ! is_array( $geom[ $i ] ) )
							throw new \Exception(
								"Invalid ring structure for [$type]." );		// !@! ==>
						
						//
						// Check vertices.
						//
						for( $j = 0; $j < count( $geom[ $i ] ); $j++ )
						{
							if( ! is_array( $geom[ $i ][ $j ] ) )
								throw new \Exception(
									"Invalid vertex for [$type]." );			// !@! ==>
							
							if( count( $geom[ $i ][ $j ] ) != 2 )
								throw new \Exception(
									"Invalid vertex for [$type]"
								   ."a vertex must have two elements." );		// !@! ==>
							
							//
							// Cast.
							//
							$geom[ $i ][ $j ][ 0 ] = (double) $geom[ $i ][ $j ][ 0 ];
							$geom[ $i ][ $j ][ 1 ] = (double) $geom[ $i ][ $j ][ 1 ];
						}
					}
					
					break;
			
				default:
					throw new \Exception(
						"Invalid shape type [$type]." );						// !@! ==>
		
			} // Parsing shape type.
			
			//
			// Update geometry.
			//
			$theValue[ kTAG_GEOMETRY ] = $geom;
		
		} // Provided.
		
	} // validateShape.

	 
	/*===================================================================================
	 *	validateSearchCriteria															*
	 *==================================================================================*/

	/**
	 * Validate search criteria.
	 *
	 * This method will validate the search criteria, this method assumes the criteria is
	 * present.
	 *
	 * While validating the criteria, the method will cluster the criteria in the filter
	 * fata member.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_INPUT_TEXT kAPI_PARAM_INPUT_STRING
	 * @see kAPI_PARAM_INPUT_RANGE kAPI_PARAM_INPUT_ENUM
	 */
	protected function validateSearchCriteria()
	{
		//
		// Check format.
		//
		if( ! is_array( $tmp = $this->offsetGet( kAPI_PARAM_CRITERIA ) ) )
			throw new \Exception(
				"Invalid search criteria format." );							// !@! ==>
		
		//
		// Resolve tags.
		//
		$value = Array();
		foreach( $tmp as $key => $val )
		{
			//
			// Handle tags.
			//
			if( $key != kAPI_PARAM_FULL_TEXT_OFFSET )
			{
				//
				// Handle tag sequence numbers.
				//
				if( is_int( $key )
				 || ctype_digit( $key ) )
					$key = $this->mWrapper->getObject( (int) $key, TRUE )[ kTAG_NID ];
			
			} // Not a full-text search.
			
			//
			// Set criteria.
			//
			$value[ $key ] = $val;
		
		} // Iterating criteria.
		
		//
		// Update criteria.
		//
		$this->offsetSet( kAPI_PARAM_CRITERIA, $value );
		
		//
		// Init local storage.
		//
		$this->mFilter = Array();
		$offsets_tag = PersistentObject::ResolveOffsetsTag( UnitObject::kSEQ_NAME );
		$ref_count_tag = PersistentObject::ResolveRefCountTag( UnitObject::kSEQ_NAME );
		$indexes
			= PersistentObject::ResolveCollectionByName(
				 $this->mWrapper, UnitObject::kSEQ_NAME )
					->getIndexedOffsets();
		
		//
		// Select tag fields.
		//
		$fields = array( kTAG_NID => TRUE, kTAG_ID_SEQUENCE => TRUE,
						 kTAG_TERMS => TRUE, kTAG_DATA_TYPE => TRUE,
						 $offsets_tag => TRUE );
		
		//
		// Filter tags only.
		//
		$tags
			= array_values(
				array_diff(
					array_keys( $value ),
					array( kAPI_PARAM_FULL_TEXT_OFFSET ) ) );
		
		//
		// Search tags.
		//
		if( count( $tags ) )
		{
			//
			// Set criteria.
			//
			$criteria = array( kTAG_NID => ( ( count( $tags ) > 1 )
										   ? array( '$in' => array_values( $tags ) )
										   : $tags[ 0 ] ) );
			
			//
			// Select tags.
			//
			$tags
				= iterator_to_array(
					PersistentObject::ResolveCollectionByName(
						$this->mWrapper, Tag::kSEQ_NAME )
						->matchAll(
							$criteria,
							kQUERY_ARRAY,
							$fields ) );
		
		} // Has tags.

		//
		// Iterate criteria.
		//
		foreach( $value as $tag => $criteria )
		{
			//
			// Handle full-text search.
			//
			if( $tag == kAPI_PARAM_FULL_TEXT_OFFSET )
			{
				//
				// Check pattern.
				//
				if( ! array_key_exists( kAPI_PARAM_PATTERN, $criteria ) )
					throw new \Exception(
						"Missing search pattern for full text search." );		// !@! ==>
				
				//
				// Create cluster entry.
				//
				$this->mFilter[ kAPI_PARAM_FULL_TEXT_OFFSET ]
					= array( kAPI_PARAM_VALUE_COUNT => 1,
							 kAPI_PARAM_CRITERIA => Array() );
				
				//
				// Reference cluster, values counter and criteria.
				//
				$cluster_ref = & $this->mFilter[ kAPI_PARAM_FULL_TEXT_OFFSET ];
				$criteria_ref = & $cluster_ref[ kAPI_PARAM_CRITERIA ];
				
				//
				// Allocate criteria.
				//
				$criteria_ref[ kAPI_PARAM_FULL_TEXT_OFFSET ] = Array();
				$criteria_ref = & $criteria_ref[ kAPI_PARAM_FULL_TEXT_OFFSET ];
							 
				//
				// Set input and data types.
				//
				$criteria_ref[ kAPI_PARAM_INPUT_TYPE ] = $criteria[ kAPI_PARAM_INPUT_TYPE ];
		
				//
				// Set index flag.
				//
				$criteria_ref[ kAPI_PARAM_INDEX ] = TRUE;
		
				//
				// Set pattern.
				//
				$criteria_ref[ kAPI_PARAM_PATTERN ] = $criteria[ kAPI_PARAM_PATTERN ];
		
				//
				// Set offsets.
				//
				$criteria_ref[ kAPI_PARAM_OFFSETS ] = array( '$text' );
				
				continue;													// =>
			
			} // Full text search.
			
			//
			// Handle tag reference.
			//
			else
			{
				//
				// Get tag object.
				//
				if( array_key_exists( $tag, $tags ) )
					$tag_object = & $tags[ $tag ];
				else
					throw new \Exception(
						"Unknown property [$tag]." );							// !@! ==>
			
				//
				// Get tag sequence number.
				//
				$tag_sequence = $tag_object[ kTAG_ID_SEQUENCE ];
			
				//
				// Get cluster key.
				//
				$cluster_key = Tag::GetClusterKey( $tag_object[ kTAG_TERMS ] );
			
			} // Tag reference.
			
			//
			// Create cluster entry.
			//
			if( ! array_key_exists( $cluster_key, $this->mFilter ) )
				$this->mFilter[ $cluster_key ]
					= array( kAPI_PARAM_VALUE_COUNT => 0,
							 kAPI_PARAM_CRITERIA => Array() );
			
			//
			// Reference cluster, values counter and criteria.
			//
			$cluster_ref = & $this->mFilter[ $cluster_key ];
			$counter_ref = & $cluster_ref[ kAPI_PARAM_VALUE_COUNT ];
			$criteria_ref = & $cluster_ref[ kAPI_PARAM_CRITERIA ];
			
			//
			// Handle no value.
			//
			if( count( $criteria ) == 1 )
			{
				//
				// Set tag.
				//
				$criteria_ref[ $tag_sequence ] = NULL;
				
				continue;													// =>
			
			} // No value.
			
			//
			// Handle input type.
			//
			if( ! array_key_exists( kAPI_PARAM_INPUT_TYPE, $criteria ) )
				throw new \Exception(
					"Missing input type for tag [$tag]." );						// !@! ==>
			
			//
			// Check required and empty values.
			//
			$has_values = TRUE;
			switch( $tmp = $criteria[ kAPI_PARAM_INPUT_TYPE ] )
			{
				//
				// Strings.
				//
				case kAPI_PARAM_INPUT_STRING:
					//
					// Require search pattern.
					//
					if( ! array_key_exists( kAPI_PARAM_PATTERN, $criteria ) )
						throw new \Exception(
							"Missing search pattern for tag [$tag]." );			// !@! ==>
					
					//
					// Handle empty search pattern.
					//
					if( ! strlen( $criteria[ kAPI_PARAM_PATTERN ] ) )
					{
						$has_values = FALSE;
						break;
					
					} // No value.
					
					break;
			
				//
				// Ranges.
				//
				case kAPI_PARAM_INPUT_RANGE:
					//
					// Require minimum.
					//
					if( ! array_key_exists( kAPI_PARAM_RANGE_MIN, $criteria ) )
						throw new \Exception(
							"Missing minimum range for tag [$tag]." );			// !@! ==>
					
					//
					// Require maximum.
					//
					if( ! array_key_exists( kAPI_PARAM_RANGE_MAX, $criteria ) )
						throw new \Exception(
							"Missing maximum range for tag [$tag]." );			// !@! ==>
					
					//
					// Set minimum.
					//
					if( ! strlen( $criteria[ kAPI_PARAM_RANGE_MIN ] ) )
						$criteria[ kAPI_PARAM_RANGE_MIN ]
							= $criteria[ kAPI_PARAM_RANGE_MAX ];
					
					//
					// Set maximum.
					//
					if( ! strlen( $criteria[ kAPI_PARAM_RANGE_MAX ] ) )
						$criteria[ kAPI_PARAM_RANGE_MAX ]
							= $criteria[ kAPI_PARAM_RANGE_MIN ];
					
					//
					// Handle empty range.
					//
					if( (! strlen( $criteria[ kAPI_PARAM_RANGE_MIN ] ))
					 && (! strlen( $criteria[ kAPI_PARAM_RANGE_MAX ] )) )
					{
						$has_values = FALSE;
						break;
					
					} // No value.
				
					break;
			
				//
				// Enumerations.
				//
				case kAPI_PARAM_INPUT_ENUM:
					//
					// Require tags.
					//
					if( ! array_key_exists( kAPI_RESULT_ENUM_TERM, $criteria ) )
						throw new \Exception(
							"Missing enumerated values [$tag]." );				// !@! ==>
					
					//
					// Handle array.
					//
					if( is_array( $criteria[ kAPI_RESULT_ENUM_TERM ] ) )
					{
						if( (! count( $criteria[ kAPI_RESULT_ENUM_TERM ] ))
						 || (! strlen( $criteria[ kAPI_RESULT_ENUM_TERM ][ 0 ] )) )
						{
							$has_values = FALSE;
							break;
						
						}
					
					} // Received array.
					
					//
					// Handle string.
					//
					elseif( ! strlen( $criteria[ kAPI_RESULT_ENUM_TERM ] ) )
					{
						$has_values = FALSE;
						break;
					}
				
					break;
			
				//
				// Shapes.
				//
				case kAPI_PARAM_INPUT_SHAPE:
					//
					// Require tags.
					//
					if( ! array_key_exists( kAPI_PARAM_SHAPE, $criteria ) )
						throw new \Exception(
							"Missing shape [$tag]." );							// !@! ==>
				
					break;
					
				//
				// Default.
				//
				case kAPI_PARAM_INPUT_DEFAULT:
					//
					// Require search pattern.
					//
					if( ! array_key_exists( kAPI_PARAM_PATTERN, $criteria ) )
						throw new \Exception(
							"Missing search pattern for tag [$tag]." );			// !@! ==>
					
					//
					// Handle empty search pattern.
					//
					if( ($criteria[ kAPI_PARAM_PATTERN ] !== FALSE)
					 && (! strlen( $criteria[ kAPI_PARAM_PATTERN ] )) )
					{
						$has_values = FALSE;
						break;
					
					} // No value.
				
					break;
				
				//
				// UNSUPPORTED.
				//
				default:
					throw new \Exception(
						"Invalid or unsupported input type [$tmp]." );			// !@! ==>
			
			} // Parsing by input type.
			
			//
			// Handle no values.
			//
			if( ! $has_values )
			{
				//
				// Set tag.
				//
				$criteria_ref[ $tag_sequence ] = NULL;
		
				continue;													// =>
			
			} // Has no values.
			
			//
			// Increment values count.
			//
			$counter_ref++;
			
			//
			// Allocate criteria.
			//
			$criteria_ref[ $tag_sequence ] = Array();
			$criteria_ref = & $criteria_ref[ $tag_sequence ];
			
			//
			// Set input and data types.
			//
			$criteria_ref[ kAPI_PARAM_INPUT_TYPE ] = $criteria[ kAPI_PARAM_INPUT_TYPE ];
			$criteria_ref[ kAPI_PARAM_DATA_TYPE ] = $tag_object[ kTAG_DATA_TYPE ];
		
			//
			// Set index flag.
			//
			$criteria_ref[ kAPI_PARAM_INDEX ]
				= ( array_key_exists( $tag_sequence, $indexes ) );
			
			//
			// Check criteria.
			//
			switch( $tmp = $criteria[ kAPI_PARAM_INPUT_TYPE ] )
			{
				//
				// Strings.
				//
				case kAPI_PARAM_INPUT_STRING:
					//
					// Cast pattern.
					//
					$criteria[ kAPI_PARAM_PATTERN ]
						= (string) $criteria[ kAPI_PARAM_PATTERN ];
			
					//
					// Check operator.
					//
					if( ! array_key_exists( kAPI_PARAM_OPERATOR, $criteria ) )
						$criteria[ kAPI_PARAM_OPERATOR ] = NULL;
					$this->validateStringMatchOperator( $criteria[ kAPI_PARAM_OPERATOR ] );
					
					//
					// Set filter.
					//
					$criteria_ref[ kAPI_PARAM_PATTERN ] = $criteria[ kAPI_PARAM_PATTERN ];
					$criteria_ref[ kAPI_PARAM_OPERATOR ] = $criteria[ kAPI_PARAM_OPERATOR ];
					
					break;
			
				//
				// Ranges.
				//
				case kAPI_PARAM_INPUT_RANGE:
					//
					// Cast minimum.
					//
					OntologyObject::CastScalar(
						$criteria[ kAPI_PARAM_RANGE_MIN ],
						$tag_object[ kTAG_DATA_TYPE ] );
			
					//
					// Cast maximum.
					//
					OntologyObject::CastScalar(
						$criteria[ kAPI_PARAM_RANGE_MAX ],
						$tag_object[ kTAG_DATA_TYPE ] );
			
					//
					// Check operator.
					//
					if( ! array_key_exists( kAPI_PARAM_OPERATOR, $criteria ) )
						$criteria[ kAPI_PARAM_OPERATOR ] = NULL;
					$this->validateRangeMatchOperator( $criteria[ kAPI_PARAM_OPERATOR ] );
					
					//
					// Set filter.
					//
					$criteria_ref[ kAPI_PARAM_RANGE_MIN ]
						= $criteria[ kAPI_PARAM_RANGE_MIN ];
					$criteria_ref[ kAPI_PARAM_RANGE_MAX ]
						= $criteria[ kAPI_PARAM_RANGE_MAX ];
					$criteria_ref[ kAPI_PARAM_OPERATOR ]
						= $criteria[ kAPI_PARAM_OPERATOR ];

					break;
			
				//
				// Enumerations.
				//
				case kAPI_PARAM_INPUT_ENUM:
					//
					// Normalise enumerations.
					//
					if( ! is_array( $criteria[ kAPI_RESULT_ENUM_TERM ] ) )
						$criteria[ kAPI_RESULT_ENUM_TERM ]
							= array( $criteria[ kAPI_RESULT_ENUM_TERM ] );
				
					//
					// Cast enumerations.
					//
					foreach( $criteria[ kAPI_RESULT_ENUM_TERM ] as $k => $v )
						$criteria[ kAPI_RESULT_ENUM_TERM ][ $k ]
							= (string) $v;
					
					//
					// Set filter.
					//
					$criteria_ref[ kAPI_RESULT_ENUM_TERM ]
						= $criteria[ kAPI_RESULT_ENUM_TERM ];
					
					break;
			
				//
				// Shapes.
				//
				case kAPI_PARAM_INPUT_SHAPE:
					//
					// Add element.
					//
					$criteria_ref[ kAPI_PARAM_SHAPE ] = $criteria[ kAPI_PARAM_SHAPE ];
					
					break;
					
				//
				// Default.
				//
				case kAPI_PARAM_INPUT_DEFAULT:
					//
					// Set filter.
					//
					$criteria_ref[ kAPI_PARAM_PATTERN ] = $criteria[ kAPI_PARAM_PATTERN ];
					
					break;
			
			} // Parsing by input type.
			
			//
			// Add offsets to filter.
			//
			if( array_key_exists( kAPI_PARAM_OFFSETS, $criteria ) )
				$criteria_ref[ kAPI_PARAM_OFFSETS ]
					= $criteria[ kAPI_PARAM_OFFSETS ];
			elseif( array_key_exists( $offsets_tag, $tag_object ) )
				$criteria_ref[ kAPI_PARAM_OFFSETS ]
					= $tag_object[ $offsets_tag ];
			else
				throw new \Exception(
					"Missing selection offsets for tag [$tag]." );				// !@! ==>
			
		} // Iterating criteria.
		
	} // validateSearchCriteria.

		

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
	 * This method will parse the request operation and execute the appropriate action.
	 *
	 * In this class we execute the following operations:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_PING}</tt>: Ping.
	 *	<li><tt>{@link kAPI_OP_LIST_CONSTANTS}</tt>: List parameter constants.
	 *	<li><tt>{@link kAPI_OP_LIST_OPERATORS}</tt>: List operator parameters.
	 *	<li><tt>{@link kAPI_OP_LIST_REF_COUNTS}</tt>: List reference count parameters.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 *	<li><tt>{@link kAPI_OP_ADD_USER}</tt>: Add user.
	 * </ul>
	 *
	 * Derived classes can parse their custom operations or call the parent method.
	 *
	 * @access protected
	 *
	 * @see kAPI_OP_PING kAPI_OP_LIST_CONSTANTS
	 */
	protected function executeRequest()
	{
		//
		// Parse by operation.
		//
		switch( $this->offsetGet( kAPI_REQUEST_OPERATION ) )
		{
			case kAPI_OP_PING:
				$this->executePing();
				break;
			
			case kAPI_OP_LIST_CONSTANTS:
				$this->executeListParameterConstants();
				break;
			
			case kAPI_OP_LIST_OPERATORS:
				$this->executeListParameterOperators();
				break;
			
			case kAPI_OP_LIST_REF_COUNTS:
				$this->executeListReferenceCountParameters();
				break;
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
				
			case kAPI_OP_GET_UNIT:
				$this->executeGetUnit();
				break;
				
			case kAPI_OP_ADD_USER:
				$this->executeAddUser();
				break;
				
			case kAPI_OP_GET_USER:
				$this->executeGetUser();
				break;
		}
		
	} // executeRequest.

	 
	/*===================================================================================
	 *	executePing																		*
	 *==================================================================================*/

	/**
	 * Execute ping request.
	 *
	 * This method will handle the {@link kAPI_OP_PING} operation.
	 *
	 * @access protected
	 */
	protected function executePing()
	{
		//
		// Set pong.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_MESSAGE ] = 'pong';
		
	} // executePing.

	 
	/*===================================================================================
	 *	executeListParameterConstants													*
	 *==================================================================================*/

	/**
	 * Execute list parameter constants request.
	 *
	 * This method will handle the {@link kAPI_OP_LIST_CONSTANTS} operation.
	 *
	 * @access protected
	 */
	protected function executeListParameterConstants()
	{
		//
		// Initialise results.
		//
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
		$ref = & $this->mResponse[ kAPI_RESPONSE_RESULTS ];
		
		//
		// Load request parameters.
		//
		$ref[ "kAPI_REQUEST_OPERATION" ] = kAPI_REQUEST_OPERATION;
		$ref[ "kAPI_REQUEST_LANGUAGE" ] = kAPI_REQUEST_LANGUAGE;
		$ref[ "kAPI_REQUEST_PARAMETERS" ] = kAPI_REQUEST_PARAMETERS;
		
		//
		// Load response parameters.
		//
		$ref[ "kAPI_RESPONSE_STATUS" ] = kAPI_RESPONSE_STATUS;
		$ref[ "kAPI_RESPONSE_PAGING" ] = kAPI_RESPONSE_PAGING;
		$ref[ "kAPI_RESPONSE_REQUEST" ] = kAPI_RESPONSE_REQUEST;
		$ref[ "kAPI_RESPONSE_RESULTS" ] = kAPI_RESPONSE_RESULTS;
		$ref[ "kAPI_RESULTS_DICTIONARY" ] = kAPI_RESULTS_DICTIONARY;
		
		//
		// Load status parameters.
		//
		$ref[ "kAPI_STATUS_STATE" ] = kAPI_STATUS_STATE;
		$ref[ "kAPI_STATUS_CODE" ] = kAPI_STATUS_CODE;
		$ref[ "kAPI_STATUS_FILE" ] = kAPI_STATUS_FILE;
		$ref[ "kAPI_STATUS_LINE" ] = kAPI_STATUS_LINE;
		$ref[ "kAPI_STATUS_MESSAGE" ] = kAPI_STATUS_MESSAGE;
		$ref[ "kAPI_STATUS_TRACE" ] = kAPI_STATUS_TRACE;
		
		//
		// Load paging parameters.
		//
		$ref[ "kAPI_PAGING_SKIP" ] = kAPI_PAGING_SKIP;
		$ref[ "kAPI_PAGING_LIMIT" ] = kAPI_PAGING_LIMIT;
		$ref[ "kAPI_PAGING_ACTUAL" ] = kAPI_PAGING_ACTUAL;
		$ref[ "kAPI_PAGING_AFFECTED" ] = kAPI_PAGING_AFFECTED;
		
		//
		// Load state constants.
		//
		$ref[ "kAPI_STATE_IDLE" ] = kAPI_STATE_IDLE;
		$ref[ "kAPI_STATE_OK" ] = kAPI_STATE_OK;
		$ref[ "kAPI_STATE_ERROR" ] = kAPI_STATE_ERROR;
		
		//
		// Load dictionary parameters.
		//
		$ref[ "kAPI_DICTIONARY_COLLECTION" ] = kAPI_DICTIONARY_COLLECTION;
		$ref[ "kAPI_DICTIONARY_REF_COUNT" ] = kAPI_DICTIONARY_REF_COUNT;
		$ref[ "kAPI_DICTIONARY_TAGS" ] = kAPI_DICTIONARY_TAGS;
		$ref[ "kAPI_DICTIONARY_IDS" ] = kAPI_DICTIONARY_IDS;
		$ref[ "kAPI_DICTIONARY_LIST_COLS" ] = kAPI_DICTIONARY_LIST_COLS;
		$ref[ "kAPI_DICTIONARY_CLUSTER" ] = kAPI_DICTIONARY_CLUSTER;
		
		//
		// Load operations.
		//
		$ref[ "kAPI_OP_PING" ] = kAPI_OP_PING;
		$ref[ "kAPI_OP_LIST_CONSTANTS" ] = kAPI_OP_LIST_CONSTANTS;
		$ref[ "kAPI_OP_LIST_OPERATORS" ] = kAPI_OP_LIST_OPERATORS;
		$ref[ "kAPI_OP_LIST_REF_COUNTS" ] = kAPI_OP_LIST_REF_COUNTS;
		$ref[ "kAPI_OP_MATCH_TAG_LABELS" ] = kAPI_OP_MATCH_TAG_LABELS;
		$ref[ "kAPI_OP_MATCH_TERM_LABELS" ] = kAPI_OP_MATCH_TERM_LABELS;
		$ref[ "kAPI_OP_MATCH_TAG_BY_LABEL" ] = kAPI_OP_MATCH_TAG_BY_LABEL;
		$ref[ "kAPI_OP_MATCH_TERM_BY_LABEL" ] = kAPI_OP_MATCH_TERM_BY_LABEL;
		$ref[ "kAPI_OP_GET_TAG_ENUMERATIONS" ] = kAPI_OP_GET_TAG_ENUMERATIONS;
		$ref[ "kAPI_OP_GET_NODE_ENUMERATIONS" ] = kAPI_OP_GET_NODE_ENUMERATIONS;
		$ref[ "kAPI_OP_MATCH_UNITS" ] = kAPI_OP_MATCH_UNITS;
		$ref[ "kAPI_OP_GET_UNIT" ] = kAPI_OP_GET_UNIT;
		$ref[ "kAPI_OP_ADD_USER" ] = kAPI_OP_ADD_USER;
		$ref[ "kAPI_OP_GET_USER" ] = kAPI_OP_GET_USER;
		
		
		//
		// Load request parameters.
		//
		$ref[ "kAPI_PARAM_PATTERN" ] = kAPI_PARAM_PATTERN;
		$ref[ "kAPI_PARAM_REF_COUNT" ] = kAPI_PARAM_REF_COUNT;
		$ref[ "kAPI_PARAM_TAG" ] = kAPI_PARAM_TAG;
		$ref[ "kAPI_PARAM_TERM" ] = kAPI_PARAM_TERM;
		$ref[ "kAPI_PARAM_NODE" ] = kAPI_PARAM_NODE;
		$ref[ "kAPI_PARAM_OPERATOR" ] = kAPI_PARAM_OPERATOR;
		$ref[ "kAPI_PARAM_RANGE_MIN" ] = kAPI_PARAM_RANGE_MIN;
		$ref[ "kAPI_PARAM_RANGE_MAX" ] = kAPI_PARAM_RANGE_MAX;
		$ref[ "kAPI_PARAM_INPUT_TYPE" ] = kAPI_PARAM_INPUT_TYPE;
		$ref[ "kAPI_PARAM_CRITERIA" ] = kAPI_PARAM_CRITERIA;
		$ref[ "kAPI_PARAM_OBJECT" ] = kAPI_PARAM_OBJECT;
		$ref[ "kAPI_PARAM_ID" ] = kAPI_PARAM_ID;
		$ref[ "kAPI_PARAM_DOMAIN" ] = kAPI_PARAM_DOMAIN;
		$ref[ "kAPI_PARAM_DATA" ] = kAPI_PARAM_DATA;
		$ref[ "kAPI_PARAM_GROUP" ] = kAPI_PARAM_GROUP;
		$ref[ "kAPI_PARAM_SHAPE" ] = kAPI_PARAM_SHAPE;
		$ref[ "kAPI_PARAM_SHAPE_OFFSET" ] = kAPI_PARAM_SHAPE_OFFSET;
		$ref[ "kAPI_PARAM_FULL_TEXT_OFFSET" ] = kAPI_PARAM_FULL_TEXT_OFFSET;
		
		//
		// Load generic request flag parameters.
		//
		$ref[ "kAPI_PARAM_LOG_REQUEST" ] = kAPI_PARAM_LOG_REQUEST;
		$ref[ "kAPI_PARAM_LOG_TRACE" ] = kAPI_PARAM_LOG_TRACE;
		$ref[ "kAPI_PARAM_RECURSE" ] = kAPI_PARAM_RECURSE;
		$ref[ "kAPI_PARAM_RESPONSE_COUNT" ] = kAPI_PARAM_RESPONSE_COUNT;
		$ref[ "kAPI_PARAM_RESPONSE_POINTS" ] = kAPI_PARAM_RESPONSE_POINTS;
		
		//
		// Load formatted request parameters.
		//
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_TYPE" ] = kAPI_PARAM_RESPONSE_FRMT_TYPE;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_NAME" ] = kAPI_PARAM_RESPONSE_FRMT_NAME;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_INFO" ] = kAPI_PARAM_RESPONSE_FRMT_INFO;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_DISP" ] = kAPI_PARAM_RESPONSE_FRMT_DISP;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_LINK" ] = kAPI_PARAM_RESPONSE_FRMT_LINK;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_SERV" ] = kAPI_PARAM_RESPONSE_FRMT_SERV;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_DOCU" ] = kAPI_PARAM_RESPONSE_FRMT_DOCU;
		
		//
		// Load formatted response types.
		//
		$ref[ "kAPI_PARAM_RESPONSE_TYPE_SCALAR" ] = kAPI_PARAM_RESPONSE_TYPE_SCALAR;
		$ref[ "kAPI_PARAM_RESPONSE_TYPE_LINK" ] = kAPI_PARAM_RESPONSE_TYPE_LINK;
		$ref[ "kAPI_PARAM_RESPONSE_TYPE_ENUM" ] = kAPI_PARAM_RESPONSE_TYPE_ENUM;
		$ref[ "kAPI_PARAM_RESPONSE_TYPE_TYPED" ] = kAPI_PARAM_RESPONSE_TYPE_TYPED;
		$ref[ "kAPI_PARAM_RESPONSE_TYPE_OBJECT" ] = kAPI_PARAM_RESPONSE_TYPE_OBJECT;
		$ref[ "kAPI_PARAM_RESPONSE_TYPE_SHAPE" ] = kAPI_PARAM_RESPONSE_TYPE_SHAPE;
		$ref[ "kAPI_PARAM_RESPONSE_TYPE_STRUCT" ] = kAPI_PARAM_RESPONSE_TYPE_STRUCT;
		
		//
		// Load enumeration element parameters.
		//
		$ref[ "kAPI_RESULT_ENUM_TERM" ] = kAPI_RESULT_ENUM_TERM;
		$ref[ "kAPI_RESULT_ENUM_NODE" ] = kAPI_RESULT_ENUM_NODE;
		$ref[ "kAPI_RESULT_ENUM_LABEL" ] = kAPI_RESULT_ENUM_LABEL;
		$ref[ "kAPI_RESULT_ENUM_DESCR" ] = kAPI_RESULT_ENUM_DESCR;
		$ref[ "kAPI_RESULT_ENUM_VALUE" ] = kAPI_RESULT_ENUM_VALUE;
		$ref[ "kAPI_PARAM_RESPONSE_CHILDREN" ] = kAPI_PARAM_RESPONSE_CHILDREN;
		
		//
		// Load operators.
		//
		$ref[ "kOPERATOR_EQUAL" ] = kOPERATOR_EQUAL;
		$ref[ "kOPERATOR_EQUAL_NOT" ] = kOPERATOR_EQUAL_NOT;
		$ref[ "kOPERATOR_PREFIX" ] = kOPERATOR_PREFIX;
		$ref[ "kOPERATOR_CONTAINS" ] = kOPERATOR_CONTAINS;
		$ref[ "kOPERATOR_SUFFIX" ] = kOPERATOR_SUFFIX;
//		$ref[ "kOPERATOR_REGEX" ] = kOPERATOR_REGEX;
		$ref[ "kOPERATOR_IRANGE" ] = kOPERATOR_IRANGE;
		$ref[ "kOPERATOR_ERANGE" ] = kOPERATOR_ERANGE;
		
		//
		// Load result type parameters.
		//
		$ref[ "kAPI_RESULT_ENUM_DATA_COLUMN" ] = kAPI_RESULT_ENUM_DATA_COLUMN;
		$ref[ "kAPI_RESULT_ENUM_DATA_RECORD" ] = kAPI_RESULT_ENUM_DATA_RECORD;
		$ref[ "kAPI_RESULT_ENUM_DATA_FORMAT" ] = kAPI_RESULT_ENUM_DATA_FORMAT;
		$ref[ "kAPI_RESULT_ENUM_DATA_MARKER" ] = kAPI_RESULT_ENUM_DATA_MARKER;
		
		//
		// Load modifiers.
		//
		$ref[ "kOPERATOR_NOCASE" ] = kOPERATOR_NOCASE;
		
		//
		// Load collection reference enumerated set.
		//
		$ref[ "kAPI_PARAM_COLLECTION_TAG" ] = Tag::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_TERM" ] = Term::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_NODE" ] = Node::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_EDGE" ] = Edge::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_UNIT" ] = UnitObject::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_ENTITY" ] = User::kSEQ_NAME;
		
		//
		// Load form input enumerated set.
		//
		$ref[ "kAPI_PARAM_INPUT_TEXT" ] = kAPI_PARAM_INPUT_TEXT;
		$ref[ "kAPI_PARAM_INPUT_STRING" ] = kAPI_PARAM_INPUT_STRING;
		$ref[ "kAPI_PARAM_INPUT_RANGE" ] = kAPI_PARAM_INPUT_RANGE;
		$ref[ "kAPI_PARAM_INPUT_ENUM" ] = kAPI_PARAM_INPUT_ENUM;
		$ref[ "kAPI_PARAM_INPUT_SHAPE" ] = kAPI_PARAM_INPUT_SHAPE;
		$ref[ "kAPI_PARAM_INPUT_DEFAULT" ] = kAPI_PARAM_INPUT_DEFAULT;
		
	} // executeListParameterConstants.

	 
	/*===================================================================================
	 *	executeListParameterOperators													*
	 *==================================================================================*/

	/**
	 * Execute list operator parameters request.
	 *
	 * This method will handle the {@link kAPI_OP_LIST_OPERATORS} operation.
	 *
	 * @access protected
	 */
	protected function executeListParameterOperators()
	{
		//
		// Initialise results.
		//
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
		$ref = & $this->mResponse[ kAPI_RESPONSE_RESULTS ];
		
		//
		// Parse by language.
		//
		switch( $this->offsetGet( kAPI_REQUEST_LANGUAGE ) )
		{
			case 'en':
			default:
				$ref[ 'title' ] = "Search data properties by label:";
				$ref[ 'placeholder' ] = "Data property label pattern...";
				$ref[ kOPERATOR_EQUAL ]
					= array( 'key' => kOPERATOR_EQUAL,
							 'label' => 'Equals',
							 'title' => 'Equals @pattern@',
							 'type' => 'string',
							 'main' => TRUE,
							 'selected' => FALSE );
				$ref[ kOPERATOR_EQUAL_NOT ]
					= array( 'key' => kOPERATOR_EQUAL_NOT,
							 'label' => 'Not equals',
							 'title' => 'Not equals @pattern@',
							 'type' => 'string',
							 'main' => TRUE,
							 'selected' => FALSE );
				$ref[ kOPERATOR_PREFIX ]
					= array( 'key' => kOPERATOR_PREFIX,
							 'label' => 'Starts with',
							 'title' => 'Starts with @pattern@',
							 'type' => 'string',
							 'main' => TRUE,
							 'selected' => FALSE );
				$ref[ kOPERATOR_CONTAINS ]
					= array( 'key' => kOPERATOR_CONTAINS,
							 'label' => 'Contains',
							 'title' => 'Contains @pattern@',
							 'type' => 'string',
							 'main' => TRUE,
							 'selected' => TRUE );
				$ref[ kOPERATOR_SUFFIX ]
					= array( 'key' => kOPERATOR_SUFFIX,
							 'label' => 'Ends with',
							 'title' => 'Ends with @pattern@',
							 'type' => 'string',
							 'main' => TRUE,
							 'selected' => FALSE );
//				$ref[ kOPERATOR_REGEX ]
//					= array( 'key' => kOPERATOR_REGEX,
//							 'label' => 'Regular expression',
//							 'title' => 'Regular expression [@pattern@]',
//							 'type' => 'string',
//							 'main' => TRUE,
//							 'selected' => FALSE );
				$ref[ kOPERATOR_IRANGE ]
					= array( 'key' => kOPERATOR_IRANGE,
							 'label' => 'Range inclusive',
							 'type' => 'range',
							 'main' => TRUE,
							 'selected' => TRUE );
				$ref[ kOPERATOR_ERANGE ]
					= array( 'key' => kOPERATOR_ERANGE,
							 'label' => 'Range exclusive',
							 'type' => 'range',
							 'main' => TRUE,
							 'selected' => FALSE );
				$ref[ kOPERATOR_NOCASE ]
					= array( 'key' => kOPERATOR_NOCASE,
							 'label' => 'Case and accent insensitive',
							 'title' => 'case and accent insensitive',
							 'type' => 'string',
							 'main' => FALSE,
							 'selected' => TRUE );
		}
		
	} // executeListParameterOperators.

	 
	/*===================================================================================
	 *	executeListReferenceCountParameters												*
	 *==================================================================================*/

	/**
	 * Execute list operator parameters request.
	 *
	 * This method will handle the {@link kAPI_OP_LIST_OPERATORS} operation.
	 *
	 * @access protected
	 */
	protected function executeListReferenceCountParameters()
	{
		//
		// Initialise results.
		//
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
		$ref = & $this->mResponse[ kAPI_RESPONSE_RESULTS ];
		
		//
		// Load results.
		//
		$ref[ kAPI_PARAM_COLLECTION_TAG ] = kTAG_TAG_COUNT;
		$ref[ kAPI_PARAM_COLLECTION_TERM ] = kTAG_TERM_COUNT;
		$ref[ kAPI_PARAM_COLLECTION_NODE ] = kTAG_NODE_COUNT;
		$ref[ kAPI_PARAM_COLLECTION_EDGE ] = kTAG_EDGE_COUNT;
		$ref[ kAPI_PARAM_COLLECTION_UNIT ] = kTAG_UNIT_COUNT;
		$ref[ kAPI_PARAM_COLLECTION_ENTITY ] = kTAG_ENTITY_COUNT;
		
	} // executeListReferenceCountParameters.

	 
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
									  $this->mResponse[ kAPI_RESPONSE_RESULTS ],
									  UnitObject::kSEQ_NAME );
	
		//
		// Return individual results.
		//
		else
		{
			//
			// Parse by result type.
			//
			switch( $this->offsetGet( kAPI_PARAM_DATA ) )
			{
				case kAPI_RESULT_ENUM_DATA_COLUMN:
					$this->executeTableUnits(
						$this->mResponse[ kAPI_RESPONSE_RESULTS ],
						UnitObject::kSEQ_NAME );
					break;
			
				case kAPI_RESULT_ENUM_DATA_RECORD:
					$this->executeClusterUnits(
						$this->mResponse[ kAPI_RESPONSE_RESULTS ],
						UnitObject::kSEQ_NAME );
					break;
			
				case kAPI_RESULT_ENUM_DATA_FORMAT:
					$this->executeFormattedUnits(
						$this->mResponse[ kAPI_RESPONSE_RESULTS ],
						UnitObject::kSEQ_NAME );
					break;
			
				case kAPI_RESULT_ENUM_DATA_MARKER:
					$this->executeMarkerUnits(
						$this->mResponse[ kAPI_RESPONSE_RESULTS ],
						UnitObject::kSEQ_NAME );
					break;
			
			} // Parsed result type.
		
		} // Not grouped.
		
	} // executeMatchUnits.

	 
	/*===================================================================================
	 *	executeGetUnit																	*
	 *==================================================================================*/

	/**
	 * Get unit.
	 *
	 * The method will match the unit and return a clustered, formatted or marker result.
	 *
	 * @access protected
	 */
	protected function executeGetUnit()
	{
		//
		// Set filter.
		//
		$this->mFilter = array( kTAG_NID => $this->offsetGet( kAPI_PARAM_ID ) );
		
		//
		// Initialise result.
		//
		if( ! array_key_exists( kAPI_RESPONSE_RESULTS, $this->mResponse ) )
			$this->mResponse[ kAPI_RESPONSE_RESULTS ]
				= Array();

		//
		// Parse by result type.
		//
		switch( $this->offsetGet( kAPI_PARAM_DATA ) )
		{
			case kAPI_RESULT_ENUM_DATA_COLUMN:
				$this->executeTableUnits(
					$this->mResponse[ kAPI_RESPONSE_RESULTS ],
					UnitObject::kSEQ_NAME );
				break;
		
			case kAPI_RESULT_ENUM_DATA_RECORD:
				$this->executeClusterUnits(
					$this->mResponse[ kAPI_RESPONSE_RESULTS ],
					UnitObject::kSEQ_NAME );
				break;
		
			case kAPI_RESULT_ENUM_DATA_FORMAT:
				$this->executeFormattedUnits(
					$this->mResponse[ kAPI_RESPONSE_RESULTS ],
					UnitObject::kSEQ_NAME );
				break;
		
			case kAPI_RESULT_ENUM_DATA_MARKER:
				$this->mFilter[ $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) ]
					= array( '$exists' => TRUE );
				$this->executeMarkerUnits(
					$this->mResponse[ kAPI_RESPONSE_RESULTS ],
					UnitObject::kSEQ_NAME );
				break;
		
		} // Parsed result type.
		
	} // executeGetUnit.

	 
	/*===================================================================================
	 *	executeGetUser																	*
	 *==================================================================================*/

	/**
	 * Get user.
	 *
	 * The method will match the user and return a clustered, formatted or marker result.
	 *
	 * @access protected
	 */
	protected function executeGetUser()
	{
		//
		// Set filter.
		//
		$param = $this->offsetGet( kAPI_PARAM_ID );
		$this->mFilter = ( is_array( $param ) )
					   ? array( kTAG_CONN_USER => array_shift( $param ),
					   			kTAG_CONN_PASS => array_shift( $param ) )
					   : array( kTAG_NID => $this->offsetGet( kAPI_PARAM_ID ) );
		
		//
		// Initialise result.
		//
		if( ! array_key_exists( kAPI_RESPONSE_RESULTS, $this->mResponse ) )
			$this->mResponse[ kAPI_RESPONSE_RESULTS ]
				= Array();

		//
		// Parse by result type.
		//
		switch( $this->offsetGet( kAPI_PARAM_DATA ) )
		{
			case kAPI_RESULT_ENUM_DATA_COLUMN:
				$this->executeTableUnits(
					$this->mResponse[ kAPI_RESPONSE_RESULTS ],
					User::kSEQ_NAME );
				break;
		
			case kAPI_RESULT_ENUM_DATA_RECORD:
				$this->executeClusterUnits(
					$this->mResponse[ kAPI_RESPONSE_RESULTS ],
					User::kSEQ_NAME );
				break;
		
			case kAPI_RESULT_ENUM_DATA_FORMAT:
				$this->executeFormattedUnits(
					$this->mResponse[ kAPI_RESPONSE_RESULTS ],
					User::kSEQ_NAME );
				break;
		
			case kAPI_RESULT_ENUM_DATA_MARKER:
				$this->mFilter[ $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) ]
					= array( '$exists' => TRUE );
				$this->executeMarkerUnits(
					$this->mResponse[ kAPI_RESPONSE_RESULTS ],
					User::kSEQ_NAME );
				break;
		
		} // Parsed result type.
		
	} // executeGetUser.

	 
	/*===================================================================================
	 *	executeAddUser																	*
	 *==================================================================================*/

	/**
	 * Add user.
	 *
	 * The method will add the provided user to the database.
	 *
	 * @access protected
	 */
	protected function executeAddUser()
	{
		//
		// Add or replace.
		//
		$this->offsetGet( kAPI_PARAM_OBJECT )->commit();
		
	} // executeGetUnit.

		

/*=======================================================================================
 *																						*
 *							PROTECTED EXECUTION UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	executeMatchLabelStrings														*
	 *==================================================================================*/

	/**
	 * Match label strings.
	 *
	 * This method will match all label strings from objects stored in the provided
	 * collection.
	 *
	 * The method uses the {@link executeMatchLabelStringsQuery()} method to produce the
	 * query cursor and the {@link executeMatchLabelStringsResults()} method to compile the
	 * results.
	 *
	 * @param CollectionObject		$theCollection		Data collection.
	 *
	 * @access protected
	 *
	 * @uses executeMatchLabelStringsQuery()
	 * @uses executeMatchLabelStringsResults()
	 */
	protected function executeMatchLabelStrings( CollectionObject $theCollection )
	{
		$this->executeMatchLabelStringsResults(
			$this->executeMatchLabelStringsQuery(
				$theCollection,
				array( (string) kTAG_LABEL => TRUE ) ) );
		
	} // executeMatchLabelStrings.

	 
	/*===================================================================================
	 *	executeMatchLabelObjects														*
	 *==================================================================================*/

	/**
	 * Match label objects.
	 *
	 * This method will match all objects in the provided collection matching labels.
	 *
	 * The method uses the {@link executeMatchLabelStringsQuery()} method to produce the
	 * query cursor and the {@link executeMatchLabelObjectsResults()} method to compile the
	 * results.
	 *
	 * @param CollectionObject		$theCollection		Data collection.
	 *
	 * @access protected
	 *
	 * @uses executeMatchLabelStringsQuery()
	 * @uses executeMatchLabelObjectsResults()
	 */
	protected function executeMatchLabelObjects( CollectionObject $theCollection )
	{
		$this->executeMatchLabelObjectsResults(
			$this->executeMatchLabelStringsQuery(
				$theCollection,
				array( (string) kTAG_LABEL => TRUE ) ) );
		
	} // executeMatchLabelObjects.


	/*===================================================================================
	 *	executeMatchLabelStringsQuery													*
	 *==================================================================================*/

	/**
	 * Query label strings.
	 *
	 * This method will match all label strings from objects stored in the provided
	 * collection and return a recordset.
	 *
	 * The method expects the following parameters to have been set:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: Match pattern.
	 *	<li><tt>{@link kAPI_PARAM_REF_COUNT}</tt>: Collection reference count.
	 *	<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: Match operator.
	 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: String language.
	 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: Limits.
	 * </ul>
	 *
	 * The last parameter represents the fields selection.
	 *
	 * The method will set the affected count and will skip eventual records, other cursor
	 * operations will have to be performed by the caller.
	 *
	 * @param CollectionObject		$theCollection		Data collection.
	 * @param array					$theFields			Fields selection.
	 *
	 * @access protected
	 * @return ObjectIterator		Matched data or <tt>NULL</tt>.
	 *
	 * @uses stringMatchPattern()
	 */
	protected function executeMatchLabelStringsQuery( CollectionObject $theCollection,
																	   $theFields = Array() )
	{
		//
		// Init local storage.
		//
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
		$filter = $this->stringMatchPattern( $this->offsetGet( kAPI_PARAM_PATTERN ),
											 $this->offsetGet( kAPI_PARAM_OPERATOR ) );
		
		//
		// Set property.
		//
		$property = (string) ( $language == '*' )
				  ? (kTAG_LABEL.'.'.kTAG_TEXT)
				  : kTAG_TEXT;
		
		//
		// Init criteria.
		//
		$criteria = array
		(
			(string) kTAG_LABEL => array
			(
				'$elemMatch' => array
				(
					(string) kTAG_TEXT => $filter,
					kTAG_LANGUAGE => $language
				)
			)
		);
		
		//
		// Handle tag strings.
		//
		if( $theCollection->getName() == Tag::kSEQ_NAME )
		{
			//
			// Filter hidden tags.
			//
			$criteria[ (string) kTAG_DATA_KIND ] = array( '$ne' => kTAG_PRIVATE_SEARCH );
			
			//
			// Filter internal tags.
			//
			$criteria[ (string) kTAG_NID ]
				= array( '$nin' => array_merge( UnitObject::InternalOffsets() ) );
			
			//
			// Filter untracked tags.
			//
			$criteria[ (string) kTAG_ID_SEQUENCE ]
				= array( '$nin' => UnitObject::UnmanagedOffsets() );
			
			//
			// Filter unsupported types.
			// MILKO - Need to handle these in the future.
			//
			$criteria[ (string) kTAG_DATA_TYPE ]
				= array( '$nin' => array( kTYPE_MIXED, kTYPE_STRUCT, kTYPE_ARRAY,
										  kTYPE_LANGUAGE_STRING, kTYPE_LANGUAGE_STRINGS,
										  kTYPE_TYPED_LIST,
										  kTYPE_SHAPE, kTYPE_REF_TAG, kTYPE_REF_TERM,
										  kTYPE_REF_NODE, kTYPE_REF_EDGE, kTYPE_REF_UNIT,
										  kTYPE_REF_ENTITY, kTYPE_REF_SELF,
										  kTYPE_TIME_STAMP ) );
		
		} // Searching tags
		
		//
		// Add collection reference count.
		//
		if( $this->offsetExists( kAPI_PARAM_REF_COUNT ) )
		{
			//
			// Iterate collections.
			//
			foreach( $this->offsetGet( kAPI_PARAM_REF_COUNT ) as $collection )
			{
				switch( $collection )
				{
					case kAPI_PARAM_COLLECTION_TAG:
						$criteria[ (string) kTAG_TAG_COUNT ] = array( '$gt' => 0 );
						break;
					
					case kAPI_PARAM_COLLECTION_TERM:
						$criteria[ (string) kTAG_TERM_COUNT ] = array( '$gt' => 0 );
						break;
					
					case kAPI_PARAM_COLLECTION_NODE:
						$criteria[ (string) kTAG_NODE_COUNT ] = array( '$gt' => 0 );
						break;
					
					case kAPI_PARAM_COLLECTION_EDGE:
						$criteria[ (string) kTAG_EDGE_COUNT ] = array( '$gt' => 0 );
						break;
					
					case kAPI_PARAM_COLLECTION_UNIT:
						$criteria[ (string) kTAG_UNIT_COUNT ] = array( '$gt' => 0 );
						break;
					
					case kAPI_PARAM_COLLECTION_ENTITY:
						$criteria[ (string) kTAG_ENTITY_COUNT ] = array( '$gt' => 0 );
						break;
				}
			}
		}
		
		//
		// Execute query.
		//
		$rs = $theCollection->matchAll( $criteria, kQUERY_OBJECT, $theFields );
		
		//
		// Add affected count to paging.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_AFFECTED ]
			= $rs->affectedCount();
	
		//
		// Skip records.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
			$rs->skip( $tmp );
	
		return $rs;																	// ==>
	
	} // executeMatchLabelStringsQuery.


	/*===================================================================================
	 *	executeMatchLabelStringsResults													*
	 *==================================================================================*/

	/**
	 * Build label strings results.
	 *
	 * This method will use the provided cursor to fill the service results with the
	 * requested label strings.
	 *
	 * @param ObjectIterator		$theIterator		Iterator object.
	 *
	 * @access protected
	 */
	protected function executeMatchLabelStringsResults( ObjectIterator $theIterator )
	{
		//
		// Iterate results.
		//
		$actual = 0;
		$limit = (int) $this->offsetGet( kAPI_PAGING_LIMIT );
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		foreach( $theIterator as $record )
		{
			//
			// Increment actual count.
			//
			$actual++;
		
			//
			// Locate language.
			//
			foreach( $record[ kTAG_LABEL ] as $element )
			{
				//
				// Match language.
				//
				if( $element[ kTAG_LANGUAGE ] == $language )
				{
					//
					// Skip duplicates.
					//
					if( ! in_array( $element[ kTAG_TEXT ],
									$this->mResponse[ kAPI_RESPONSE_RESULTS ] ) )
					{
						//
						// Add to results.
						//
						$this->mResponse[ kAPI_RESPONSE_RESULTS ][]
							= $element[ kTAG_TEXT ];
					
						//
						// Decrement limits.
						//
						$limit--;
				
					} // Not duplicate.
				
					break;													// =>
			
				} // Matched.
		
			} // Iterating languages.
		
			//
			// Check limit.
			//
			if( $limit <= 0 )
				break;														// =>
	
		} // Iterating results.
	
		//
		// Add actual counts to paging.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_ACTUAL ] = $actual;
		
	} // executeMatchLabelStringsResults.


	/*===================================================================================
	 *	executeMatchLabelObjectsResults													*
	 *==================================================================================*/

	/**
	 * Build label objects results.
	 *
	 * This method will use the provided cursor to fill the service results with the
	 * requested label objects.
	 *
	 * @param ObjectIterator		$theIterator		Iterator object.
	 *
	 * @access protected
	 */
	protected function executeMatchLabelObjectsResults( ObjectIterator $theIterator )
	{
		//
		// Skip records.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
			$theIterator->skip( (int) $tmp );
		
		//
		// Set cursor limit.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_LIMIT )) !== NULL )
			$theIterator->limit( (int) $tmp );
		
		//
		// Instantiate results formatter.
		//
		$formatter
			= new IteratorSerialiser(
					$theIterator,									// Iterator.
					kAPI_RESULT_ENUM_DATA_RECORD,					// Format.
					$this->offsetGet( kAPI_REQUEST_LANGUAGE ) );	// Language.
		
		//
		// Serialise iterator.
		//
		$formatter->serialise();
		
		//
		// Set paging.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ] = $formatter->paging();
		
		//
		// Set dictionary.
		//
		$dictionary = $formatter->dictionary();
		$elements = array( kAPI_DICTIONARY_COLLECTION, kAPI_DICTIONARY_REF_COUNT,
						   kAPI_DICTIONARY_IDS, kAPI_DICTIONARY_TAGS,
						   kAPI_DICTIONARY_CLUSTER );
		foreach( $elements as $element )
		{
			if( array_key_exists( $element, $dictionary ) )
				$this->mResponse[ kAPI_RESULTS_DICTIONARY ][ $element ]
					= $dictionary[ $element ];
		}
		
		//
		// Set data.
		//
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = $formatter->data();
		
	} // executeMatchLabelObjectsResults.


	/*===================================================================================
	 *	executeLoadEnumerations															*
	 *==================================================================================*/

	/**
	 * Load enumerations.
	 *
	 * This method expects an iterator containing a list of enumerations and a reference to
	 * the results element that will receive the results.
	 *
	 * @param ObjectIterator		$theIterator		Iterator object.
	 * @param array					$theContainer		Reference to the results container.
	 *
	 * @access protected
	 */
	protected function executeLoadEnumerations( ObjectIterator $theIterator,
															  &$theContainer )
	{
		//
		// Iterate results.
		//
		foreach( $theIterator as $edge )
		{
			//
			// Handle enumeration.
			//
			if( $edge[ kTAG_PREDICATE ] == kPREDICATE_ENUM_OF )
				$this->executeLoadEnumeration( $edge, $theContainer );
			
			//
			// Handle type.
			//
			elseif( $edge[ kTAG_PREDICATE ] == kPREDICATE_TYPE_OF )
			{
				//
				// Locate enumerations.
				//
				$edges
					= Edge::ResolveCollection(
						Edge::ResolveDatabase(
							$this->mWrapper ) )
							->matchAll(
								array( kTAG_OBJECT => $edge[ kTAG_SUBJECT ],
									   kTAG_PREDICATE
											=> array( '$in'
												=> array( kPREDICATE_TYPE_OF,
														  kPREDICATE_ENUM_OF ) ) ),
								kQUERY_ARRAY,
								array( kTAG_SUBJECT => TRUE,
									   kTAG_PREDICATE => TRUE ) );
				
				//
				// Recurse.
				//
				$this->executeLoadEnumerations( $edges, $theContainer );
			
			} // Found type.
		
		} // Iterating edges.
		
	} // executeLoadEnumerations.


	/*===================================================================================
	 *	executeLoadEnumeration															*
	 *==================================================================================*/

	/**
	 * Load single enumeration.
	 *
	 * This method expects an iterator containing a list of enumerations and a reference to
	 * the results element that will receive the results.
	 *
	 * @param array					$theEdge			Edge object.
	 * @param array					$theContainer		Reference to the results container.
	 *
	 * @access protected
	 */
	protected function executeLoadEnumeration( &$theEdge, &$theContainer )
	{
		//
		// Init local storage.
		//
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		$fields = array( kTAG_LABEL => TRUE,
						 kTAG_DEFINITION => TRUE );
		$ref_count = $this->getRefCountTag( $this->offsetGet( kAPI_PARAM_REF_COUNT ) );
		if( is_array( $ref_count )
		 && (count( $ref_count ) > 1) )
			$ref_count = array_shift( $ref_count );
		if( $ref_count !== NULL )
			$fields[ $ref_count ] = TRUE;
		
		//
		// Load node.
		//
		$node
			= Node::ResolveCollection(
				Node::ResolveDatabase(
					$this->mWrapper ) )
						->matchOne(
							array( kTAG_NID => $theEdge[ kTAG_SUBJECT ] ),
							kQUERY_ARRAY,
							array( kTAG_NODE_TYPE => TRUE,
								   kTAG_TERM => TRUE,
								   kTAG_LABEL => TRUE,
								   kTAG_DESCRIPTION => TRUE ) );
		
		//
		// Load term.
		//
		$term
			= Term::ResolveCollection(
				Term::ResolveDatabase(
					$this->mWrapper ) )
						->matchOne(
							array( kTAG_NID => $node[ kTAG_TERM ] ),
							kQUERY_ARRAY,
							$fields );
		
		//
		// Allocate element.
		//
		$index = count( $theContainer );
		$theContainer[ $index ] = Array();
		$ref = & $theContainer[ $index ];
		
		//
		// Load term.
		//
		$ref[ kAPI_RESULT_ENUM_TERM ] = $term[ kTAG_NID ];
		
		//
		// Load node.
		//
		$ref[ kAPI_RESULT_ENUM_NODE ] = $node[ kTAG_NID ];
		
		//
		// Load label.
		//
		if( array_key_exists( kTAG_LABEL, $node ) )
			$ref[ kAPI_RESULT_ENUM_LABEL ]
				= OntologyObject::SelectLanguageString(
					$node[ kTAG_LABEL ], $language );
		elseif( array_key_exists( kTAG_LABEL, $term ) )
			$ref[ kAPI_RESULT_ENUM_LABEL ]
				= OntologyObject::SelectLanguageString(
					$term[ kTAG_LABEL ], $language );
		
		//
		// Load description.
		//
		if( array_key_exists( kTAG_DESCRIPTION, $node ) )
			$ref[ kAPI_RESULT_ENUM_DESCR ]
				= OntologyObject::SelectLanguageString(
					$node[ kTAG_DESCRIPTION ], $language );
		elseif( array_key_exists( kTAG_DEFINITION, $term ) )
			$ref[ kAPI_RESULT_ENUM_DESCR ]
				= OntologyObject::SelectLanguageString(
					$term[ kTAG_DEFINITION ], $language );
		
		//
		// Set node kind.
		//
		if( array_key_exists( kTAG_NODE_TYPE, $node ) )
			$ref[ kAPI_RESULT_ENUM_VALUE ]
				= ( in_array( kTYPE_NODE_ENUMERATION, $node[ kTAG_NODE_TYPE ] ) );
		
		//
		// Set reference count.
		//
		if( ($ref_count !== NULL)
		 && array_key_exists( $ref_count, $term ) )
			$ref[ kAPI_PARAM_RESPONSE_COUNT ]
				= $term[ $ref_count ];
				
		//
		// Update affected count.
		//
		if( ! $this->offsetExists( kAPI_PARAM_RECURSE ) )
			$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_AFFECTED ]++;
		
		//
		// Check for children.
		//
		$edges
			= Edge::ResolveCollection(
				Edge::ResolveDatabase(
					$this->mWrapper ) )
					->matchAll(
						array( kTAG_OBJECT => $node[ kTAG_NID ],
							   kTAG_PREDICATE
									=> array( '$in'
										=> array( kPREDICATE_TYPE_OF,
												  kPREDICATE_ENUM_OF ) ) ),
						kQUERY_ARRAY,
						array( kTAG_SUBJECT => TRUE,
							   kTAG_PREDICATE => TRUE ) );
		
		//
		// Handle children.
		//
		if( $edges->count() )
		{
			//
			// Recurse.
			//
			if( $this->offsetGet( kAPI_PARAM_RECURSE ) )
			{
				//
				// Allocate children element.
				//
				$ref[ kAPI_PARAM_RESPONSE_CHILDREN ] = Array();
	
				//
				// Recurse.
				//
				$this->executeLoadEnumerations( $edges,
												$ref[ kAPI_PARAM_RESPONSE_CHILDREN ] );
		
			} // Recurse enumerations.
			
			//
			// Save count.
			//
			else
				$ref[ kAPI_PARAM_RESPONSE_CHILDREN ] = (int) $edges->count( FALSE );
		
		} // Has children.
		
	} // executeLoadEnumeration.


	/*===================================================================================
	 *	executeGroupUnits																*
	 *==================================================================================*/

	/**
	 * Group units.
	 *
	 * This method expects the filter data member set with the requested query.
	 *
	 * @param array					$theGroup			Groupings list.
	 * @param array					$theContainer		Reference to the results container.
	 * @param string				$theCollection		collection name.
	 *
	 * @access protected
	 */
	protected function executeGroupUnits( $theGroup, &$theContainer, $theCollection )
	{
		//
		// Init local storage.
		//
		$pipeline = $grouping = $identifiers = Array();
		$match = $project = $unwind = $group = $sort = Array();
		$shape = $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET );
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		foreach( $theGroup as $tmp )
		{
			$grouping[ $tmp ] = '$'.$tmp;
			$identifiers[ $tmp ] = "_id.$tmp";
		}
		
		//
		// Set match.
		//
		$match = $this->mFilter;
		
		//
		// Set project.
		//
		$project = array_count_values( $theGroup );
		if( $shape !== NULL )
			$project[ $shape ]
				= array( '$cond' => array(
						 'if' => '$'.$shape.'.type',
						 'then' => 1,
						 'else' => 0 ) );
		
		//
		// Set unwind.
		//
		foreach( $theGroup as $tmp )
		{
			if( $this->mWrapper->getObject( $tmp, TRUE )[ kTAG_DATA_TYPE ] == kTYPE_SET )
				$unwind[] = array( '$unwind' => '$'.$tmp );
		}
		
		//
		// Set group.
		//
		$group
			= array( '_id' => $grouping,
					 kAPI_PARAM_RESPONSE_COUNT => array( '$sum' => 1 ) );
		if( $shape !== NULL )
			$group[ kAPI_PARAM_RESPONSE_POINTS ] = array( '$sum' => '$'.kTAG_GEO_SHAPE );
		
		//
		// Set sort.
		//
		$sort = array_count_values( $identifiers );
		
		//
		// Fill pipeline.
		//
		if( count( $match ) )
			$pipeline[] = array( '$match' => $match );
		if( count( $project ) )
			$pipeline[] = array( '$project' => $project );
		if( count( $unwind ) )
		{
			foreach( $unwind as $tmp )
				$pipeline[] = $tmp;
		}
		if( count( $group ) )
			$pipeline[] = array( '$group' => $group );
		if( count( $sort ) )
			$pipeline[] = array( '$sort' => $sort );

		//
		// Aggregate.
		//
		$rs_units
			= PersistentObject::ResolveCollectionByName(
				$this->mWrapper, $theCollection )
					->aggregate(
						$pipeline );
		//
		// Iterate results.
		//
//
// MILKO - Need to do this if aggregate doesn't use cursor.
//
$rs_units = & $rs_units[ 'result' ];
		$tmp = Array();
		foreach( $rs_units as $record )
		{
			//
			// Collect terms.
			//
			foreach( $record[ kTAG_NID ] as $term )
			{
				if( ! in_array( $term, $tmp ) )
					$tmp[] = $term;
			
			} // Iterating terms.
		
		} // Collecting tags.
		
		//
		// Resolve terms.
		//
		$rs_terms
			= Term::ResolveCollection(
				Term::ResolveDatabase(
					$this->mWrapper ) )
						->matchAll(
							array( kTAG_NID => array( '$in' => $tmp ) ),
							kQUERY_ARRAY,
							array( kTAG_LABEL => TRUE, kTAG_DEFINITION => TRUE ) );
		
		//
		// Load terms.
		//
		$terms = Array();
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		foreach( $rs_terms as $key => $value )
		{
			$terms[ $key ] = Array();
			if( array_key_exists( kTAG_LABEL, $value ) )
				$terms[ $key ][ kTAG_LABEL ]
					= OntologyObject::SelectLanguageString(
						$value[ kTAG_LABEL ], $language );
			if( array_key_exists( kTAG_DEFINITION, $value ) )
				$terms[ $key ][ kTAG_DEFINITION ]
					= OntologyObject::SelectLanguageString(
						$value[ kTAG_DEFINITION ], $language );
		
		} // Loading temrs.
		
		//
		// Serialise group.
		//
		foreach( $rs_units as $record )
		{
			//
			// Point to container.
			//
			$ref = & $theContainer;
			
			//
			// Iterate groups.
			//
			foreach( $theGroup as $group )
			{
				//
				// Get term.
				//
				$term = $record[ '_id' ][ $group ];
				$term_ref = & $terms[ $term ];
				
				//
				// Create element.
				//
				if( ! array_key_exists( $term, $ref ) )
				{
					//
					// Allocate element.
					//
					$ref[ $term ] = Array();
					
					//
					// Load label.
					//
					if( array_key_exists( kTAG_LABEL, $term_ref ) )
						$ref[ $term ][ kTAG_LABEL ]
							= $term_ref[ kTAG_LABEL ];
					
					//
					// Load definition.
					//
					if( array_key_exists( kTAG_DEFINITION, $term_ref ) )
						$ref[ $term ][ kTAG_DEFINITION ]
							= $term_ref[ kTAG_DEFINITION ];
				
				} // New element.
				
				//
				// Handle leaf node.
				//
				if( $group == kTAG_DOMAIN )
				{
					$ref[ $term ][ kAPI_PARAM_RESPONSE_COUNT ]
						= $record[ kAPI_PARAM_RESPONSE_COUNT ];
					if( array_key_exists( kAPI_PARAM_RESPONSE_POINTS, $record ) )
						$ref[ $term ][ kAPI_PARAM_RESPONSE_POINTS ]
							= $record[ kAPI_PARAM_RESPONSE_POINTS ];
				}
				
				//
				// Handle container node.
				//
				else
				{
					//
					// Allocate children container.
					//
					if( ! array_key_exists( kAPI_PARAM_RESPONSE_CHILDREN, $ref[ $term ] ) )
						$ref[ $term ][ kAPI_PARAM_RESPONSE_CHILDREN ] = Array();
					
					//
					// Point to container.
					//
					$ref = & $ref[ $term ][ kAPI_PARAM_RESPONSE_CHILDREN ];
				
				} // Container node.
			
			} // Iterating groups.
		
		} // Iterating results.
		
	} // executeGroupUnits.


	/*===================================================================================
	 *	executeTableUnits																*
	 *==================================================================================*/

	/**
	 * Cluster units.
	 *
	 * This method expects the filter data member set with the requested query.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param string				$theCollection		collection name.
	 *
	 * @access protected
	 */
	protected function executeTableUnits( &$theContainer, $theCollection )
	{
		//
		// Execute request.
		//
		$iterator
			= PersistentObject::ResolveCollectionByName(
				$this->mWrapper, $theCollection )
					->matchAll(
						$this->mFilter, kQUERY_OBJECT );
	
		//
		// Skip records.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
			$iterator->skip( (int) $tmp );
		
		//
		// Set cursor limit.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_LIMIT )) !== NULL )
			$iterator->limit( (int) $tmp );
		
		//
		// Instantiate results formatter.
		//
		$formatter
			= new IteratorSerialiser(
					$iterator,										// Iterator.
					kAPI_RESULT_ENUM_DATA_COLUMN,					// Format.
					$this->offsetGet( kAPI_REQUEST_LANGUAGE ),		// Language.
					$this->offsetGet( kAPI_PARAM_DOMAIN ),			// Domain.
					$this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) );	// Shape.
		
		//
		// Serialise iterator.
		//
		$formatter->serialise();
		
		//
		// Set paging.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ] = $formatter->paging();
		
		//
		// Set dictionary.
		//
		$this->mResponse[ kAPI_RESULTS_DICTIONARY ]
						[ kAPI_DICTIONARY_LIST_COLS ]
			= $formatter->dictionary()[ kAPI_DICTIONARY_LIST_COLS ];
		
		//
		// Set data.
		//
		$theContainer = $formatter->data();
		
	} // executeTableUnits.


	/*===================================================================================
	 *	executeFormattedUnits															*
	 *==================================================================================*/

	/**
	 * Format units.
	 *
	 * This method expects the filter data member set with the requested query.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param string				$theCollection		collection name.
	 *
	 * @access protected
	 */
	protected function executeFormattedUnits( &$theContainer, $theCollection )
	{
		//
		// Execute request.
		//
		$iterator
			= PersistentObject::ResolveCollectionByName(
				$this->mWrapper, $theCollection )
					->matchAll(
						$this->mFilter, kQUERY_OBJECT );
	
		//
		// Skip records.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
			$iterator->skip( (int) $tmp );
		
		//
		// Set cursor limit.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_LIMIT )) !== NULL )
			$iterator->limit( (int) $tmp );
		
		//
		// Instantiate results formatter.
		//
		$formatter
			= new IteratorSerialiser(
					$iterator,										// Iterator.
					kAPI_RESULT_ENUM_DATA_FORMAT,					// Format.
					$this->offsetGet( kAPI_REQUEST_LANGUAGE ),		// Language.
					$this->offsetGet( kAPI_PARAM_DOMAIN ),			// Domain.
					$this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) );	// Shape.
		
		//
		// Serialise iterator.
		//
		$formatter->serialise();
		
		//
		// Set paging.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ] = $formatter->paging();
		
		//
		// Set data.
		//
		$theContainer = $formatter->data();
		
	} // executeFormattedUnits.


	/*===================================================================================
	 *	executeMarkerUnits																*
	 *==================================================================================*/

	/**
	 * Marker units.
	 *
	 * This method expects the filter data member set with the requested query.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param string				$theCollection		collection name.
	 *
	 * @access protected
	 */
	protected function executeMarkerUnits( &$theContainer, $theCollection )
	{
		//
		// Init local storage.
		//
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		$shape = $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET );
		
		//
		// Execute request.
		//
		$iterator
			= PersistentObject::ResolveCollectionByName(
				$this->mWrapper, $theCollection )
					->matchAll(
						$this->mFilter,
						kQUERY_ARRAY,
						array( $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) => TRUE ) );
		
		//
		// Skip records.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
			$iterator->skip( (int) $tmp );
		
		//
		// Set cursor limit.
		//
		if( $this->offsetExists( kAPI_PAGING_LIMIT ) )
			$iterator->limit( (int) $this->offsetGet( kAPI_PAGING_LIMIT ) );
		
		//
		// Instantiate results formatter.
		//
		$formatter
			= new IteratorSerialiser(
					$iterator,										// Iterator.
					kAPI_RESULT_ENUM_DATA_MARKER,					// Format.
					$this->offsetGet( kAPI_REQUEST_LANGUAGE ),		// Language.
					$this->offsetGet( kAPI_PARAM_DOMAIN ),			// Domain.
					$this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) );	// Shape.
		
		//
		// Serialise iterator.
		//
		$formatter->serialise();
		
		//
		// Set paging.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ] = $formatter->paging();
		
		//
		// Set data.
		//
		$theContainer = $formatter->data();
		
	} // executeMarkerUnits.


	/*===================================================================================
	 *	executeClusterUnits																*
	 *==================================================================================*/

	/**
	 * Cluster units.
	 *
	 * This method expects the filter data member set with the requested query.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param string				$theCollection		collection name.
	 *
	 * @access protected
	 */
	protected function executeClusterUnits( &$theContainer, $theCollection )
	{
		//
		// Execute request.
		//
		$iterator
			= PersistentObject::ResolveCollectionByName(
				$this->mWrapper, $theCollection )
					->matchAll(
						$this->mFilter, kQUERY_OBJECT );
	
		//
		// Skip records.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
			$iterator->skip( (int) $tmp );
		
		//
		// Set cursor limit.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_LIMIT )) !== NULL )
			$iterator->limit( (int) $tmp );
		
		//
		// Instantiate results formatter.
		//
		$formatter
			= new IteratorSerialiser(
					$iterator,										// Iterator.
					kAPI_RESULT_ENUM_DATA_RECORD,					// Format.
					$this->offsetGet( kAPI_REQUEST_LANGUAGE ),		// Language.
					$this->offsetGet( kAPI_PARAM_DOMAIN ),			// Domain.
					$this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) );	// Shape.
		
		//
		// Serialise iterator.
		//
		$formatter->serialise();
		
		//
		// Set paging.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ] = $formatter->paging();
		
		//
		// Set dictionary.
		//
		$dictionary = $formatter->dictionary();
		$elements = array( kAPI_DICTIONARY_COLLECTION, kAPI_DICTIONARY_REF_COUNT,
						   kAPI_DICTIONARY_LIST_COLS, kAPI_DICTIONARY_IDS,
						   kAPI_DICTIONARY_TAGS );
		foreach( $elements as $element )
		{
			if( array_key_exists( $element, $dictionary ) )
				$this->mResponse[ kAPI_RESULTS_DICTIONARY ][ $element ]
					= $dictionary[ $element ];
		}
		
		//
		// Set data.
		//
		$theContainer = $formatter->data();
		
	} // executeClusterUnits.

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXCEPTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	handleException																	*
	 *==================================================================================*/

	/**
	 * Handle exception.
	 *
	 * This method should be called whenever an exception is catched, it will load the
	 * exception arguments in the {@link kAPI_RESPONSE_STATUS} block, delete all other
	 * blocks and return the JSON encoded result.
	 *
	 * <em>Note that the script will exit in this method</em>.
	 *
	 * @param Exception				$theException		Exception.
	 *
	 * @access protected
	 */
	protected function handleException( \Exception $theException )
	{
		//
		// Init status.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
			= array( kAPI_STATUS_STATE => kAPI_STATE_ERROR );
		
		//
		// Set status code.
		//
		if( $tmp = $theException->getCode() )
			$this->mResponse[ kAPI_RESPONSE_STATUS ]
							[ kAPI_STATUS_CODE ] = $tmp;
		
		//
		// Set status message.
		//
		if( ($tmp = $theException->getMessage()) !== NULL )
			$this->mResponse[ kAPI_RESPONSE_STATUS ]
							[ kAPI_STATUS_MESSAGE ] = $tmp;
		
		//
		// Set file path.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_FILE ] = $theException->getFile();
		
		//
		// Set file line.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_LINE ] = $theException->getLine();
		
		//
		// Set trace.
		//
		if( $this->offsetGet( kAPI_PARAM_LOG_TRACE ) )
			$this->mResponse[ kAPI_RESPONSE_STATUS ]
							[ kAPI_STATUS_TRACE ] = $theException->getTrace();
		
		//
		// Remove paging.
		//
		if( array_key_exists( kAPI_RESPONSE_PAGING, $this->mResponse ) )
			unset( $this->mResponse[ kAPI_RESPONSE_PAGING ] );
		
		//
		// Remove dictionary.
		//
		if( array_key_exists( kAPI_RESULTS_DICTIONARY, $this->mResponse ) )
			unset( $this->mResponse[ kAPI_RESULTS_DICTIONARY ] );
		
		//
		// Remove results.
		//
		if( array_key_exists( kAPI_RESPONSE_RESULTS, $this->mResponse ) )
			unset( $this->mResponse[ kAPI_RESPONSE_RESULTS ] );
		
		//
		// Send header.
		//
		header( 'Content-type: application/json' );
		
		exit( JsonEncode( $this->mResponse ) );									// ==>
	
	} // _Exception2Status.

		

/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	stringMatchPattern																*
	 *==================================================================================*/

	/**
	 * Return string match criteria pattern.
	 *
	 * This method will return the pattern corresponding to the provided operator that
	 * should be used to match a string.
	 *
	 * The methoid will only consider the following main operators:
	 *
	 * <ul>
	 *	<li><tt>{@link kOPERATOR_EQUAL}</tt>: Equality.
	 *	<li><tt>{@link kOPERATOR_EQUAL_NOT}</tt>: Inequality.
	 *	<li><tt>{@link kOPERATOR_PREFIX}</tt>: Prefix.
	 *	<li><tt>{@link kOPERATOR_CONTAINS}</tt>: Contains.
	 *	<li><tt>{@link kOPERATOR_SUFFIX}</tt>: Suffix.
	 *	<li><tt>{@link kOPERATOR_REGEX}</tt>: Regular expression.
	 * </ul>
	 *
	 * If none of the above are present in the operator, the method will raise an exception.
	 *
	 * The method will only return the match value to be used in the filter criteria, it is
	 * up to the caller to add the property reference.
	 *
	 * @param string				$thePattern			Match pattern.
	 * @param array					$theOperator		Match operator.
	 *
	 * @access protected
	 * @return array				Search criteria.
	 */
	protected function stringMatchPattern( $thePattern, $theOperator )
	{
		//
		// Handle equality.
		//
		if( in_array( kOPERATOR_EQUAL, $theOperator ) )
			return ( in_array( kOPERATOR_NOCASE, $theOperator ) )
				 ? new \MongoRegex( '/^'.$thePattern.'$/i' )						// ==>
				 : $thePattern;														// ==>
		
		//
		// Handle inequality.
		//
		elseif( in_array( kOPERATOR_EQUAL_NOT, $theOperator ) )
			return ( in_array( kOPERATOR_NOCASE, $theOperator ) )
				 ? array( '$ne' => new \MongoRegex( '/^'.$thePattern.'$/i' ) )		// ==>
				 : array( '$ne' => $thePattern );									// ==>
		
		//
		// Handle prefix.
		//
		elseif( in_array( kOPERATOR_PREFIX, $theOperator ) )
			return ( in_array( kOPERATOR_NOCASE, $theOperator ) )
				 ? new \MongoRegex( '/^'.$thePattern.'/i' )							// ==>
				 : new \MongoRegex( '/^'.$thePattern.'/' );							// ==>
		
		//
		// Handle contains.
		//
		elseif( in_array( kOPERATOR_CONTAINS, $theOperator ) )
			return ( in_array( kOPERATOR_NOCASE, $theOperator ) )
				 ? new \MongoRegex( '/'.$thePattern.'/i' )							// ==>
				 : new \MongoRegex( '/'.$thePattern.'/' );							// ==>
		
		//
		// Handle suffix.
		//
		elseif( in_array( kOPERATOR_SUFFIX, $theOperator ) )
			return ( in_array( kOPERATOR_NOCASE, $theOperator ) )
				 ? new \MongoRegex( '/'.$thePattern.'$/i' )							// ==>
				 : new \MongoRegex( '/'.$thePattern.'$/' );							// ==>
		
		//
		// Handle regular expression.
		//
		elseif( in_array( kOPERATOR_REGEX, $theOperator ) )
			return new \MongoRegex( $thePattern );									// ==>

		throw new \Exception(
			"Missing string pattern match operator." );							// !@! ==>
		
	} // stringMatchPattern.

	 
	/*===================================================================================
	 *	rangeMatchPattern																*
	 *==================================================================================*/

	/**
	 * Return range match criteria pattern.
	 *
	 * This method will return the query corresponding to the provided range input type.
	 *
	 * Depending on the clause operator:
	 *
	 * <ul>
	 *	<li><tt>{@link kOPERATOR_IRANGE}</tt>: Include bounds.
	 *	<li><tt>{@link kOPERATOR_ERANGE}</tt>: Exclude bounds.
	 * </ul>
	 *
	 * If none of the above are present in the operator, the method will assume range
	 * inclusive.
	 *
	 * The method will only return the match value to be used in the filter criteria, it is
	 * up to the caller to add the property reference.
	 *
	 * @param mixed					$theMin				Minimum bound.
	 * @param mixed					$theMax				Maximum bound.
	 * @param array					$theOperator		Match operator.
	 *
	 * @access protected
	 * @return array				Search criteria.
	 */
	protected function rangeMatchPattern( $theMin, $theMax, $theOperator )
	{
		//
		// Handle range inclusive.
		//
		if( in_array( kOPERATOR_ERANGE, $theOperator ) )
			return array( '$gt' => $theMin, '$lt' => $theMax );						// ==>
		
		return array( '$gte' => $theMin, '$lte' => $theMax );						// ==>
		
	} // rangeMatchPattern.

	 
	/*===================================================================================
	 *	shapeMatchPattern																*
	 *==================================================================================*/

	/**
	 * Return shape match criteria pattern.
	 *
	 * This method will return the query corresponding to the provided shape input type.
	 *
	 * @param array					$theShape			Shape criteria.
	 *
	 * @access protected
	 * @return array				Search criteria.
	 */
	protected function shapeMatchPattern( $theShape )
	{
		//
		// Parse by type.
		//
		$geom = & $theShape[ kTAG_GEOMETRY ];
		switch( $type = $theShape[ kTAG_TYPE ] )
		{
			case 'Point':
				return
					array( '$nearSphere'
						=> array( '$geometry' => array( 'type' => $type,
														'coordinates' => $geom[ 0 ] ),
								  '$maxDistance' => $geom[ 1 ] ) );					// ==>
			
			case 'Circle':
				return
					array( '$geoWithin'
						=> array( '$centerSphere'
							=> array( $geom[ 0 ],
									  $geom[ 1 ] ) ) );								// ==>
			
			case 'Rect':
				return
					array( '$geoWithin'
						=> array( '$box' => $geom ) );								// ==>
			
			case 'Polygon':
				return
					array( '$geoWithin'
						=> array( '$geometry' => $theShape ) );						// ==>
		}
		
	} // shapeMatchPattern.

	 
	/*===================================================================================
	 *	resolveFilter																	*
	 *==================================================================================*/

	/**
	 * Resolve filter.
	 *
	 * This method will convert the clustered clauses stored in the filter data member into
	 * a query that can be used by the current database.
	 *
	 * @access protected
	 */
	protected function resolveFilter()
	{
		//
		// Init local storage.
		//
		$query = Array();
		$root = & $query;
		$parent = NULL;
		
		//
		// Handle many clusters.
		//
		if( ($cluster_count = count( $this->mFilter )) > 1 )
		{
			$query[ '$and' ] = Array();
			$root = & $query[ '$and' ];
			$parent = 'a';
		
		} // Many clusters.
		
		//
		// Iterate clusters.
		//
		foreach( $this->mFilter as $cluster_key => $cluster )
		{
			//
			// Init loop storage.
			//
			$cluster_ref = & $root;
			$criteria_count = count( $cluster[ kAPI_PARAM_CRITERIA ] );
			$parent_clu = $parent;
			
			//
			// Handle cluster with no values.
			//
			if( ! $cluster[ kAPI_PARAM_VALUE_COUNT ] )
			{
				//
				// Get tag match.
				//
				$match = ( $criteria_count > 1 )
					   ? array( '$in' => array_keys( $cluster[ kAPI_PARAM_CRITERIA ] ) )
					   :  (int) key( $cluster[ kAPI_PARAM_CRITERIA ] );
				
				//
				// Load tag match clause.
				//
				if( $cluster_count > 1 )
					$root[] = array( kTAG_OBJECT_TAGS => $match );
				else
					$root[ kTAG_OBJECT_TAGS ] = $match;
			
			} // Cluster has no values.
			
			//
			// Handle cluster with values.
			//
			else
			{
				//
				// Handle many criteria.
				//
				if( $criteria_count > 1 )
				{
					$parent_clu = 'o';
					if( $cluster_count > 1 )
					{
						$index = count( $cluster_ref );
						$cluster_ref[ $index ] = array( '$or' => Array() );
						$cluster_ref = & $cluster_ref[ $index ][ '$or' ];
					}
					else
					{
						$cluster_ref[ '$or' ] = Array();
						$cluster_ref = & $cluster_ref[ '$or' ];
					}
			
				} // Many criteria.
				
				//
				// Iterate criteria.
				//
				foreach( $cluster[ kAPI_PARAM_CRITERIA ] as $tag => $criteria )
				{
					//
					// Init loop storage.
					//
					$criteria_ref = & $cluster_ref;
					$offsets_count = count( $criteria[ kAPI_PARAM_OFFSETS ] );
					$parent_cri = $parent_clu;
					
					//
					// Handle unindexed in many criteria.
					//
					if( ($criteria !== NULL)				// Has value
					 && (! $criteria[ kAPI_PARAM_INDEX ])	// and has no index
					 && ($parent_cri == 'o') )				// and in OR.
					{
						$index = count( $criteria_ref );
						$criteria_ref[ $index ] = array( '$and' => Array() );
						$criteria_ref = & $criteria_ref[ $index ][ '$and' ];
						$parent_cri = 'a';
					
					} // Enclose in AND.
					
					//
					// Handle no value.
					//
					if( $criteria === NULL )
					{
						if( $parent_cri !== NULL )
							$criteria_ref[] = array( kTAG_OBJECT_TAGS => (int) $tag );
						else
							$criteria_ref[ kTAG_OBJECT_TAGS ] = (int) $tag;
					
					} // No value.
					
					//
					// Handle value.
					//
					else
					{
						//
						// Handle unindexed.
						//
						if( ! $criteria[ kAPI_PARAM_INDEX ] )
						{
							if( $parent_cri !== NULL )
								$criteria_ref[] = array( kTAG_OBJECT_TAGS => (int) $tag );
							else
								$criteria_ref[ kTAG_OBJECT_TAGS ] = (int) $tag;
						
						} // Not indexed.
			
						//
						// Handle many offsets.
						//
						if( $offsets_count > 1 )
						{
							if( $parent_cri == 'a' )
							{
								$index = count( $criteria_ref );
								$criteria_ref[ $index ] = array( '$or' => Array() );
								$criteria_ref = & $criteria_ref[ $index ][ '$or' ];
							}
							elseif( $parent_cri === NULL )
							{
								$criteria_ref[ '$or' ] = Array();
								$criteria_ref = & $criteria_ref[ '$or' ];
							}
			
						} // Has many offsets.
					
						//
						// Iterate criteria offsets.
						//
						foreach( $criteria[ kAPI_PARAM_OFFSETS ] as $offset )
						{
							//
							// Parse input type.
							//
							switch( $criteria[ kAPI_PARAM_INPUT_TYPE ] )
							{
								//
								// Full-text search.
								//
								case kAPI_PARAM_INPUT_TEXT:
									$clause
										= array( '$search'
													=> $criteria[ kAPI_PARAM_PATTERN ] );
									
									if( ($parent_cri !== NULL)
									 || ($offsets_count > 1) )
										$criteria_ref[] = array( $offset => $clause );
									else
										$criteria_ref[ $offset ] = $clause;
										
									break;
					
								//
								// Strings.
								//
								case kAPI_PARAM_INPUT_STRING:
									$clause
										= $this->stringMatchPattern(
											$criteria[ kAPI_PARAM_PATTERN ],
											$criteria[ kAPI_PARAM_OPERATOR ] );
									
									if( ($parent_cri !== NULL)
									 || ($offsets_count > 1) )
										$criteria_ref[] = array( $offset => $clause );
									else
										$criteria_ref[ $offset ] = $clause;
										
									break;
					
								//
								// Match ranges.
								//
								case kAPI_PARAM_INPUT_RANGE:
									$clause
										= $this->rangeMatchPattern(
											$criteria[ kAPI_PARAM_RANGE_MIN ],
											$criteria[ kAPI_PARAM_RANGE_MAX ],
											$criteria[ kAPI_PARAM_OPERATOR ] );
									
									if( $parent_cri !== NULL )
										$criteria_ref[] = array( $offset => $clause );
									else
										$criteria_ref[ $offset ] = $clause;

									break;
					
								//
								// Enumerations.
								//
								case kAPI_PARAM_INPUT_ENUM:
									$clause
										= ( count( $criteria[ kAPI_RESULT_ENUM_TERM ] )
												> 1 )
										? array( '$in'
											=> $criteria[ kAPI_RESULT_ENUM_TERM ] )
										: $criteria[ kAPI_RESULT_ENUM_TERM ][ 0 ];
									
									if( $parent_cri !== NULL )
										$criteria_ref[] = array( $offset => $clause );
									else
										$criteria_ref[ $offset ] = $clause;
										
									break;
					
								//
								// Shapes.
								//
								case kAPI_PARAM_INPUT_SHAPE:
									$clause
										= $this->shapeMatchPattern(
											$criteria[ kAPI_PARAM_SHAPE ] );
									
									if( $parent_cri !== NULL )
										$criteria_ref[] = array( $offset => $clause );
									else
										$criteria_ref[ $offset ] = $clause;
									
									break;
				
								default:
									if( $parent_cri !== NULL )
										$criteria_ref[]
											= array( $offset
												=> $criteria[ kAPI_PARAM_PATTERN ] );
									else
										$criteria_ref[ $offset ]
											= $criteria[ kAPI_PARAM_PATTERN ];
									
									break;
			
							} // Parsing input types.
			
						} // Iterating criteria offsets.
					
					} // Has value.
			
				} // Iterating cluster criteria.
			
			} // Cluster has values.
		
		} // Iterating clusters.
		
		//
		// Add domain selection.
		//
		if( $this->offsetExists( kAPI_PARAM_DOMAIN ) )
		{
			if( $parent == 'a' )
				$root[] = array( kTAG_DOMAIN => $this->offsetGet( kAPI_PARAM_DOMAIN ) );
			else
				$root[ kTAG_DOMAIN ] = $this->offsetGet( kAPI_PARAM_DOMAIN );
		
		} // Domain selection.

		//
		// Update filter.
		//
		$this->mFilter = $query;
		
		//
		// Debug.
		//
		if( kDEBUG_FLAG )
			$this->mResponse[ kAPI_RESULTS_DICTIONARY ][ 'query' ] = $this->mFilter;
		
	} // resolveFilter.

	 
	/*===================================================================================
	 *	getRefCountTag																	*
	 *==================================================================================*/

	/**
	 * Get reference count tag.
	 *
	 * This method will return the reference count tags related to the provided} value,
	 * which should be the {@link kAPI_PARAM_REF_COUNT} service parameter.
	 *
	 * If the value is an array, the method will return an array indexed by the element
	 * with as value the reference count tag; if the value has one element, the method will
	 * return the reference count tag; if the value is missing, the method will return
	 * <tt>NULL</tt>.
	 *
	 * @param array					$theCollection		Collection name.
	 *
	 * @access protected
	 * @return mixed				Reference count tags.
	 */
	protected function getRefCountTag( $theCollection )
	{
		//
		// Handle missing parameter.
		//
		if( $theCollection === NULL )
			return NULL;															// ==>
		
		//
		// Handle scalar.
		//
		if( ! is_array( $theCollection ) )
		{
			//
			// Return tag.
			//
			switch( $theCollection )
			{
				case kAPI_PARAM_COLLECTION_TAG: return kTAG_TAG_COUNT;				// ==>
				case kAPI_PARAM_COLLECTION_TERM: return kTAG_TERM_COUNT;			// ==>
				case kAPI_PARAM_COLLECTION_NODE: return kTAG_NODE_COUNT;			// ==>
				case kAPI_PARAM_COLLECTION_EDGE: return kTAG_EDGE_COUNT;			// ==>
				case kAPI_PARAM_COLLECTION_UNIT: return kTAG_UNIT_COUNT;			// ==>
				case kAPI_PARAM_COLLECTION_ENTITY: return kTAG_ENTITY_COUNT;		// ==>
			}
			
			return NULL;															// ==>
		
		} // Scalar.
	
		//
		// Handle single element.
		//
		if( count( $theCollection ) == 1 )
			return $this->getRefCountTag( $theCollection[ 0 ] );					// ==>
		
		//
		// Iterate elements.
		//
		$result = Array();
		foreach( $tmp as $element )
			$result[ $element ] = $this->getRefCountTag( $element );
		
		return $result;																// ==>
		
	} // getRefCountTag.

	 

} // class Service.


?>
