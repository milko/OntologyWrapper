<?php

/**
 * ServiceObject.php
 *
 * This file contains the definition of the {@link ServiceObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Wrapper;
use OntologyWrapper\ContainerObject;

/*=======================================================================================
 *																						*
 *									ServiceObject.php									*
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
abstract class ServiceObject extends ContainerObject
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
	 * Counter.
	 *
	 * This data member holds a counter.
	 *
	 * @var int
	 */
	protected $mCounter = 0;

	/**
	 * Offsets.
	 *
	 * This data member holds the offsets tag reference.
	 *
	 * @var int
	 */
	protected $mOffsets = NULL;

		

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
	 * In this class we handle the following parameters:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: String match pattern.
	 *	<li><tt>{@link kAPI_PAGING_SKIP}</tt>: Recordset skip value.
	 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: Recordset limits value.
	 *	<li><tt>{@link kAPI_PARAM_LOG_REQUEST}</tt>: Log request.
	 *	<li><tt>{@link kAPI_PARAM_LOG_TRACE}</tt>: Log trace.
	 *	<li><tt>{@link kAPI_PARAM_RECURSE}</tt>: Recurse structures.
	 * </ul>
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
			case kAPI_PARAM_COLLECTION:
				if( strlen( $theValue ) )
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
		}
	
	} // parseParameter.

		

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
			$this->validateReferenceCount( $this->offsetGet( kAPI_PARAM_REF_COUNT ) );
		
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
	 *	validateSearchWithCriteria														*
	 *==================================================================================*/

	/**
	 * Validate collection search service.
	 *
	 * This method will validate all service operations which search a collection usinf a
	 * list of criteria, the method will perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Check collection</em>: If the parameter is missing, the method will raise an
	 *		exception; the method will check if the provided collection is valid.
	 *	<li><em>Check criteria parameters</em>: The method will check whether all the
	 *		required criteria parameters are therew.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_NODE
	 */
	protected function validateSearchWithCriteria()
	{
		//
		// Check collection.
		//
		$this->validateSearchCollection( $tmp = $this->offsetGet( kAPI_PARAM_COLLECTION ) );
		
		//
		// Resolve offsets tag identifier.
		//
		$this->mOffsets = PersistentObject::ResolveOffsetsTag( $tmp );
		
		//
		// Check criteria.
		//
		$tmp = $this->offsetGet( kAPI_PARAM_CRITERIA );
		$this->validateSearchCriteria( $tmp );
		$this->offsetSet( kAPI_PARAM_CRITERIA, $tmp );
		
	} // validateSearchWithCriteria.

	 
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
						   kOPERATOR_CONTAINS, kOPERATOR_SUFFIX, kOPERATOR_REGEX );
		
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
	 *	validateReferenceCount															*
	 *==================================================================================*/

	/**
	 * Validate reference count reference.
	 *
	 * This method will validate the reference count parameter passed to all service
	 * operations which select tags and requuire only tags with values, the method will
	 * check whether the provided parameter value(s) is valid.
	 *
	 * Any error will raise an exception.
	 *
	 * This method expects the reference count parameter to be an array if provided.
	 *
	 * @param array					$theValue			Reference count enums.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_COLLECTION_TAG kAPI_PARAM_COLLECTION_TERM
	 * @see kAPI_PARAM_COLLECTION_NODE kAPI_PARAM_COLLECTION_EDGE
	 * @see kAPI_PARAM_COLLECTION_UNIT kAPI_PARAM_COLLECTION_ENTITY
	 */
	protected function validateReferenceCount( $theValue )
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
						"Invalid or unsupported collection reference count "
					   ."[$collection]." );										// !@! ==>
			}
		
		} // Provided.
		
	} // validateReferenceCount.

	 
	/*===================================================================================
	 *	validateSearchCollection														*
	 *==================================================================================*/

	/**
	 * Validate search collection reference.
	 *
	 * This method will validate the search collection parameter passed to all service
	 * operations which perform a search criteria on a specific collection.
	 *
	 * The method assumes the collection to be required.
	 *
	 * Any error will raise an exception.
	 *
	 * @param string				$theValue			Search collection.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_COLLECTION_UNIT kAPI_PARAM_COLLECTION_ENTITY
	 */
	protected function validateSearchCollection( $theValue )
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
				= array( kAPI_PARAM_COLLECTION_UNIT, kAPI_PARAM_COLLECTION_ENTITY );
			
			//
			// Check value.
			//
			if( ! in_array( $theValue, $collections ) )
				throw new \Exception(
					"Invalid or unsupported collection reference "
				   ."[$theValue]." );											// !@! ==>
		
		} // Provided.
		
		//
		// Require collection parameter.
		//
		else
			throw new \Exception(
				"Missing required collection parameter." );						// !@! ==>
		
	} // validateSearchCollection.

	 
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
	 *	validateSearchCriteria															*
	 *==================================================================================*/

	/**
	 * Validate search criteria.
	 *
	 * This method will validate the provided search criteria, if the criteria is missing,
	 * the method will raise an exception.
	 *
	 * The method expects the {@link kAPI_PARAM_COLLECTION} parameter to have been set.
	 *
	 * @param array					$theValue			Search criteria.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_INPUT_STRING kAPI_PARAM_INPUT_RANGE kAPI_PARAM_INPUT_ENUM
	 */
	protected function validateSearchCriteria( &$theValue )
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
					"Invalid search criteria format." );						// !@! ==>
			
			//
			// Get indexes.
			//
			$indexes
				= PersistentObject::ResolveCollectionByName(
					$this->mWrapper,
					$this->offsetGet( kAPI_PARAM_COLLECTION ) )
						->getIndexedOffsets();
			
			//
			// Iterate criteria.
			//
			foreach( $theValue as $tag => $criteria )
			{
				//
				// Resolve offset.
				//
				$offset = ( (! is_int( $tag )) && (! ctype_digit( $tag )) )
						? $this->mWrapper->getSerial( $tag, TRUE )
						: (int) $tag;
				
				//
				// Get tag object.
				//
				$tag_object = $this->mWrapper->getObject( $offset, TRUE );
				
				//
				// Add tag sequence number to criteria.
				//
				$criteria[ kTAG_ID_SEQUENCE ] = $offset;
				
				//
				// Add tag data type criteria.
				//
				$criteria[ kTAG_DATA_TYPE ] = $tag_object[ kTAG_DATA_TYPE ];
				
				//
				// Add tag terms to criteria.
				//
				$criteria[ kTAG_TERMS ] = $tag_object[ kTAG_TERMS ];
				
				//
				// Add tag data kind to criteria.
				//
				if( array_key_exists( kTAG_DATA_KIND, $tag_object ) )
					$criteria[ kTAG_DATA_KIND ]
						= $tag_object[ kTAG_DATA_KIND ];
				
				//
				// Add tag offsets to criteria.
				//
				if( array_key_exists( $this->mOffsets, $tag_object ) )
					$criteria[ $this->mOffsets ]
						= $tag_object[ $this->mOffsets ];
				
				//
				// Add index flag to criteria.
				//
				if( array_key_exists( $offset, $indexes ) )
					$criteria[ kAPI_PARAM_INDEX ]
						= $indexes[ $offset ];
				
				//
				// Check required fields.
				//
				if( array_key_exists( kAPI_PARAM_INPUT_TYPE, $criteria ) )
				{
					//
					// Parse by input type.
					//
					switch( $tmp = $criteria[ kAPI_PARAM_INPUT_TYPE ] )
					{
						//
						// Strings.
						//
						case kAPI_PARAM_INPUT_STRING:
							//
							// Consider only criteria with values.
							//
							if( count( $criteria ) > 1 )
							{
								//
								// Require search pattern.
								//
								if( ! array_key_exists( kAPI_PARAM_PATTERN, $criteria ) )
									throw new \Exception(
										"Missing search pattern for tag "
										   ."[$tag]." );						// !@! ==>
								
								//
								// Cast pattern.
								//
								$criteria[ kAPI_PARAM_PATTERN ]
									= (string) $criteria[ kAPI_PARAM_PATTERN ];
							
								//
								// Check operator.
								//
								if( array_key_exists( kAPI_PARAM_OPERATOR, $criteria ) )
									$this->validateStringMatchOperator(
										$criteria[ kAPI_PARAM_OPERATOR ] );
								
								//
								// Update criteria.
								//
								$theValue[ $tag ] = $criteria;
							}
							
							break;
						
						//
						// Ranges.
						//
						case kAPI_PARAM_INPUT_RANGE:
							//
							// Consider only criteria with values.
							//
							if( count( $criteria ) > 1 )
							{
								//
								// Require minimum.
								//
								if( ! array_key_exists( kAPI_PARAM_RANGE_MIN, $criteria ) )
									throw new \Exception(
										"Missing minimum range for tag "
									   ."[$tag]." );							// !@! ==>
								
								//
								// Cast minimum.
								//
								OntologyObject::CastScalar(
									$criteria[ kAPI_PARAM_RANGE_MIN ], $type );
							
								//
								// Require maximum.
								//
								if( ! array_key_exists( kAPI_PARAM_RANGE_MAX, $criteria ) )
									throw new \Exception(
										"Missing maximum range for tag "
									   ."[$tag]." );							// !@! ==>
								
								//
								// Cast maximum.
								//
								OntologyObject::CastScalar(
									$criteria[ kAPI_PARAM_RANGE_MAX ], $type );
							
								//
								// Check operator.
								//
								if( ! array_key_exists( kAPI_PARAM_OPERATOR, $criteria ) )
									$criteria[ kAPI_PARAM_OPERATOR ] = NULL;
								$this->validateRangeMatchOperator(
									$criteria[ kAPI_PARAM_OPERATOR ] );
								
								//
								// Update criteria.
								//
								$theValue[ $tag ] = $criteria;
							}
							
							break;
						
						//
						// Enum.
						//
						case kAPI_PARAM_INPUT_ENUM:
							//
							// Consider only criteria with values.
							//
							if( count( $criteria ) > 1 )
							{
								//
								// Require tags.
								//
								if( ! array_key_exists( kAPI_RESULT_ENUM_TERM, $criteria ) )
									throw new \Exception(
										"Missing enumerated values "
									   ."[$tag]." );							// !@! ==>
								
								//
								// Cast enumerations.
								//
								if( ! is_array( $criteria[ kAPI_RESULT_ENUM_TERM ] ) )
									$criteria[ kAPI_RESULT_ENUM_TERM ]
										= array( $criteria[ kAPI_RESULT_ENUM_TERM ] );
								foreach( $criteria[ kAPI_RESULT_ENUM_TERM ] as $k => $v )
									$criteria[ kAPI_RESULT_ENUM_TERM ][ $k ]
										= (string) $v;
								
								//
								// Update criteria.
								//
								$theValue[ $tag ] = $criteria;
							}
							
							break;
						
						default:
							throw new \Exception(
								"Invalid or unsupported input type [$tmp]." );	// !@! ==>
					
					} // Parsed input type.
				
				} // Has input type.
				
				//
				// Require input type.
				//
				else
					throw new \Exception(
						"Missing input type for tag [$tag]." );					// !@! ==>
			
			} // Iterating criteria.
		
		} // Provided.
		
		//
		// Set default operator.
		//
		else
			throw new \Exception(
				"Missing search criteria." );									// !@! ==>
		
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
		$ref[ "kAPI_DICTIONARY_TAGS" ] = kAPI_DICTIONARY_TAGS;
		$ref[ "kAPI_DICTIONARY_IDS" ] = kAPI_DICTIONARY_IDS;
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
		$ref[ "kAPI_OP_MATCH_DOMAINS" ] = kAPI_OP_MATCH_DOMAINS;
		
		//
		// Load request parameters.
		//
		$ref[ "kAPI_PARAM_PATTERN" ] = kAPI_PARAM_PATTERN;
		$ref[ "kAPI_PARAM_REF_COUNT" ] = kAPI_PARAM_REF_COUNT;
		$ref[ "kAPI_PARAM_COLLECTION" ] = kAPI_PARAM_COLLECTION;
		$ref[ "kAPI_PARAM_TAG" ] = kAPI_PARAM_TAG;
		$ref[ "kAPI_PARAM_NODE" ] = kAPI_PARAM_NODE;
		$ref[ "kAPI_PARAM_OPERATOR" ] = kAPI_PARAM_OPERATOR;
		$ref[ "kAPI_PARAM_RANGE_MIN" ] = kAPI_PARAM_RANGE_MIN;
		$ref[ "kAPI_PARAM_RANGE_MAX" ] = kAPI_PARAM_RANGE_MAX;
		$ref[ "kAPI_PARAM_INPUT_TYPE" ] = kAPI_PARAM_INPUT_TYPE;
		$ref[ "kAPI_PARAM_CRITERIA" ] = kAPI_PARAM_CRITERIA;
		
		//
		// Load generic request flag parameters.
		//
		$ref[ "kAPI_PARAM_LOG_REQUEST" ] = kAPI_PARAM_LOG_REQUEST;
		$ref[ "kAPI_PARAM_LOG_TRACE" ] = kAPI_PARAM_LOG_TRACE;
		$ref[ "kAPI_PARAM_RECURSE" ] = kAPI_PARAM_RECURSE;
		$ref[ "kAPI_PARAM_INDEXED" ] = kAPI_PARAM_INDEXED;
		
		//
		// Load enumeration element parameters.
		//
		$ref[ "kAPI_RESULT_ENUM_TERM" ] = kAPI_RESULT_ENUM_TERM;
		$ref[ "kAPI_RESULT_ENUM_NODE" ] = kAPI_RESULT_ENUM_NODE;
		$ref[ "kAPI_RESULT_ENUM_LABEL" ] = kAPI_RESULT_ENUM_LABEL;
		$ref[ "kAPI_RESULT_ENUM_DESCR" ] = kAPI_RESULT_ENUM_DESCR;
		$ref[ "kAPI_RESULT_ENUM_VALUE" ] = kAPI_RESULT_ENUM_VALUE;
		$ref[ "kAPI_RESULT_ENUM_CHILDREN" ] = kAPI_RESULT_ENUM_CHILDREN;
		
		//
		// Load operators.
		//
		$ref[ "kOPERATOR_EQUAL" ] = kOPERATOR_EQUAL;
		$ref[ "kOPERATOR_EQUAL_NOT" ] = kOPERATOR_EQUAL_NOT;
		$ref[ "kOPERATOR_PREFIX" ] = kOPERATOR_PREFIX;
		$ref[ "kOPERATOR_CONTAINS" ] = kOPERATOR_CONTAINS;
		$ref[ "kOPERATOR_SUFFIX" ] = kOPERATOR_SUFFIX;
		$ref[ "kOPERATOR_REGEX" ] = kOPERATOR_REGEX;
		$ref[ "kOPERATOR_IRANGE" ] = kOPERATOR_IRANGE;
		$ref[ "kOPERATOR_ERANGE" ] = kOPERATOR_ERANGE;
		
		//
		// Load modifiers.
		//
		$ref[ "kOPERATOR_NOCASE" ] = kOPERATOR_NOCASE;
		
		//
		// Load collection reference enumerated set.
		//
		$ref[ "kAPI_PARAM_COLLECTION_TAG" ] = kAPI_PARAM_COLLECTION_TAG;
		$ref[ "kAPI_PARAM_COLLECTION_TERM" ] = kAPI_PARAM_COLLECTION_TERM;
		$ref[ "kAPI_PARAM_COLLECTION_NODE" ] = kAPI_PARAM_COLLECTION_NODE;
		$ref[ "kAPI_PARAM_COLLECTION_EDGE" ] = kAPI_PARAM_COLLECTION_EDGE;
		$ref[ "kAPI_PARAM_COLLECTION_UNIT" ] = kAPI_PARAM_COLLECTION_UNIT;
		$ref[ "kAPI_PARAM_COLLECTION_ENTITY" ] = kAPI_PARAM_COLLECTION_ENTITY;
		
		//
		// Load form input enumerated set.
		//
		$ref[ "kAPI_PARAM_INPUT_STRING" ] = kAPI_PARAM_INPUT_STRING;
		$ref[ "kAPI_PARAM_INPUT_RANGE" ] = kAPI_PARAM_INPUT_RANGE;
		$ref[ "kAPI_PARAM_INPUT_ENUM" ] = kAPI_PARAM_INPUT_ENUM;
		
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
				$ref[ kOPERATOR_REGEX ]
					= array( 'key' => kOPERATOR_REGEX,
							 'label' => 'Regular expression',
							 'title' => 'Regular expression [@pattern@]',
							 'type' => 'string',
							 'main' => TRUE,
							 'selected' => FALSE );
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
	 * @return IteratorObject		Matched data or <tt>NULL</tt>.
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
					(string) kTAG_LANGUAGE => $language
				)
			)
		);
		
		//
		// Filter hidden tags.
		//
		if( $theCollection->getName() == Tag::kSEQ_NAME )
			$criteria[ (string) kTAG_DATA_KIND ]
				= array( '$ne' => kTAG_PRIVATE_SEARCH );
		
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
		$rs = $theCollection->matchAll( $criteria, kQUERY_ARRAY, $theFields );
		
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
	 * @param IteratorObject		$theIterator		Iterator object.
	 *
	 * @access protected
	 */
	protected function executeMatchLabelStringsResults( IteratorObject $theIterator )
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
	 * @param IteratorObject		$theIterator		Iterator object.
	 *
	 * @access protected
	 */
	protected function executeMatchLabelObjectsResults( IteratorObject $theIterator )
	{
		//
		// Set cursor limit.
		//
		$theIterator->limit( (int) $this->offsetGet( kAPI_PAGING_LIMIT ) );
		
		//
		// Instantiate results aggregator.
		//
		$aggregator = new ResultAggregator( $theIterator, $this->mResponse );
		
		//
		// Aggregate results.
		//
		$aggregator->aggregate( $this->offsetGet( kAPI_REQUEST_LANGUAGE ) );
		
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
	 * @param IteratorObject		$theIterator		Iterator object.
	 * @param array					$theContainer		Reference to the results container.
	 *
	 * @access protected
	 */
	protected function executeLoadEnumerations( IteratorObject $theIterator,
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
							array( kTAG_LABEL => TRUE,
								   kTAG_DEFINITION => TRUE ) );
		
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
				$ref[ kAPI_RESULT_ENUM_CHILDREN ] = Array();
	
				//
				// Recurse.
				//
				$this->executeLoadEnumerations( $edges,
												$ref[ kAPI_RESULT_ENUM_CHILDREN ] );
		
			} // Recurse enumerations.
			
			//
			// Save count.
			//
			else
				$ref[ kAPI_RESULT_ENUM_CHILDREN ] = (int) $edges->count( FALSE );
		
		} // Has children.
		
	} // executeLoadEnumeration.

		

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
	 *	clusterSearchCriteria															*
	 *==================================================================================*/

	/**
	 * Return clustered search criteria.
	 *
	 * This method will cluster the current search criteria, the method will return an
	 * array of clustered criteria structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The cluster term identifier.
	 *	<li><em>value</em>: The criteria elements belonging to that cluster.
	 * </ul>
	 *
	 * The method expects the {@link kAPI_PARAM_CRITERIA} parameter to be set.
	 *
	 * @access protected
	 */
	protected function clusterSearchCriteria()
	{
		//
		// Init local storage.
		//
		$cluster = Array();
		$criteria = $this->offsetGet( kAPI_PARAM_CRITERIA );
		
		//
		// Iterate criteria.
		//
		foreach( $criteria as $key => $value )
			$cluster[ ResultAggregator::GetTagClusterKey( $value[ kTAG_TERMS ] ) ]
					[ $key ]
				= $value;
		
		//
		// Update criteria.
		//
		$this->offsetSet( kAPI_PARAM_CRITERIA, $cluster );
		
	} // clusterSearchCriteria.

	 
	/*===================================================================================
	 *	getQueryCriteria																*
	 *==================================================================================*/

	/**
	 * Return search criteria.
	 *
	 * This method will build and return the query criteria based on the provided search
	 * criteria.
	 *
	 * The query builder will follow these rules:
	 *
	 * <ul>
	 *	<li>For each clause;
	 *	 <ul>
	 *		<li>If the clause has values:
	 *		 <ul>
	 *			<li>Build value clause,
	 *			<li>If the tag is not indexed:
	 *			 <ul>
	 *				<li>Add tags search clause
	 *			 </ul>
	 *		 </ul>
	 *		<li>Replace the clause with the query.
	 *	 </ul>
	 *	<li>For each cluster:
	 *	 <ul>
	 *		<li>If the cluster has more than one element:
	 *		 <ul>
	 *			<li>Transform the cluster in an OR clause
	 *		 </ul>
	 *		<li>If the cluster has one element:
	 *		 <ul>
	 *			<li>Replace cluster with query.
	 *		 </ul>
	 *	 </ul>
	 * </ul>
	 *
	 * The method expects the {@link kAPI_PARAM_CRITERIA} parameter to be set.
	 *
	 * @access protected
	 */
	protected function getQueryCriteria()
	{
		//
		// Init local storage.
		//
		$query = Array();
		$search = $this->offsetGet( kAPI_PARAM_CRITERIA );
		
		//
		// Iterate clusters.
		//
		foreach( $search as $cluster => $element )
		{
			//
			// Iterate cluster tags.
			//
			foreach( $element as $tag => $criteria )
			{
			
			} // Iterating cluster tags.
		
		} // Iterating clusters.
		
		return $query;																// ==>
		
	} // getQueryCriteria.

	 

} // class ServiceObject.


?>
