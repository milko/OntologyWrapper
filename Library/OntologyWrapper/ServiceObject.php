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
	 * in this class we only parse the {@link kAPI_OP_PING} operation; if the operation is
	 * not recognised, the method will raise an exception.
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
	 *	<li><tt>{@link kAPI_PARAM_HAS_TAG_REFS}</tt>: Tag reference count flag.
	 *	<li><tt>{@link kAPI_PARAM_HAS_TERM_REFS}</tt>: Term reference count flag.
	 *	<li><tt>{@link kAPI_PARAM_HAS_NODE_REFS}</tt>: Node reference count flag.
	 *	<li><tt>{@link kAPI_PARAM_HAS_EDGE_REFS}</tt>: Edge reference count flag.
	 *	<li><tt>{@link kAPI_PARAM_HAS_UNIT_REFS}</tt>: Unit reference count flag.
	 *	<li><tt>{@link kAPI_PARAM_HAS_ENTITY_REFS}</tt>: Entity reference count flag.
	 * </ul>
	 *
	 * Derived classes should handle their custom parameters or call the parent method, any
	 * parameter which is not handled will be ignored.
	 *
	 * @param string				$theKey				Parameter key.
	 * @param mixed					$theValue			Parameter value.
	 *
	 * @access protected
	 *
	 * @see kAPI_PARAM_PATTERN kAPI_PAGING_SKIP kAPI_PAGING_LIMIT
	 * @see kAPI_PARAM_HAS_TAG_REFS kAPI_PARAM_HAS_TERM_REFS
	 * @see kAPI_PARAM_HAS_NODE_REFS kAPI_PARAM_HAS_EDGE_REFS
	 * @see kAPI_PARAM_HAS_UNIT_REFS kAPI_PARAM_HAS_ENTITY_REFS
	 */
	protected function parseParameter( &$theKey, &$theValue )
	{
		//
		// Parse parameter.
		//
		switch( $theKey )
		{
			case kAPI_PARAM_PATTERN:
				if( strlen( $theValue ) )
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

			case kAPI_PARAM_HAS_TAG_REFS:
			case kAPI_PARAM_HAS_TERM_REFS:
			case kAPI_PARAM_HAS_NODE_REFS:
			case kAPI_PARAM_HAS_EDGE_REFS:
			case kAPI_PARAM_HAS_UNIT_REFS:
			case kAPI_PARAM_HAS_ENTITY_REFS:
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
	 * In this class we only check the ping operation which needs no validation, derived
	 * classes should match custom operations or call the parent method.
	 *
	 * Any unrecognised operation will raise an exception.
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
	 * @uses validateMatchLabelStringsOperator()
	 */
	protected function validateMatchLabelStrings()
	{
		//
		// Check operator.
		//
		if( $this->offsetExists( kAPI_PARAM_OPERATOR ) )
			$this->validateMatchLabelStringsOperator();
		else
			$this->offsetSet( kAPI_PARAM_OPERATOR,
							  array( kOPERATOR_CONTAINS, kOPERATOR_NOCASE ) );
		
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
		
	} // validateMatchLabelStrings.

	 
	/*===================================================================================
	 *	validateMatchLabelStringsOperator												*
	 *==================================================================================*/

	/**
	 * Validate match tag labels operator.
	 *
	 * This method will validate the operator parameter passed to all service operations
	 * which match label strings, the method will perform the following operations:
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
	 * The method assumes the operator to be set.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kOPERATOR_EQUAL kOPERATOR_EQUAL_NOT kOPERATOR_PREFIX
	 * @see kOPERATOR_CONTAINS kOPERATOR_SUFFIX kOPERATOR_REGEX
	 */
	protected function validateMatchLabelStringsOperator()
	{
		//
		// Init local storage.
		//
		$op = $this->offsetGet( kAPI_PARAM_OPERATOR );
		
		//
		// Check format.
		//
		if( ! is_array( $op ) )
			throw new \Exception(
				"Invalid operator parameter format." );							// !@! ==>
		
		//
		// Check count.
		//
		if( ! count( $op ) )
			throw new \Exception(
				"Empty operator parameter." );									// !@! ==>
		
		//
		// Init local storage.
		//
		$opts = array( kOPERATOR_EQUAL, kOPERATOR_EQUAL_NOT, kOPERATOR_PREFIX,
					   kOPERATOR_CONTAINS, kOPERATOR_SUFFIX, kOPERATOR_REGEX );
		
		//
		// Check operator.
		//
		$main = array_intersect( $opts, $op );
		if( count( $main ) > 1 )
			throw new \Exception(
				"Too many operator options." );									// !@! ==>
		if( ! count( $main ) )
			throw new \Exception(
				"Missing main operator option in parameter." );					// !@! ==>
		
	} // validateMatchLabelStringsOperator.

		

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
	 * This class handles the {@link kAPI_OP_PING} operation, derived classes can parse
	 * their custom operations or call the parent method.
	 *
	 * @access protected
	 *
	 * @see kAPI_OP_PING
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
	 * The method expects the following parameters to have been set:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: Match pattern.
	 *	<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: Match operator.
	 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: String language.
	 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: Limits.
	 * </ul>
	 *
	 * The method will also consider the following optional parameters:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_PARAM_HAS_TAG_REFS}</tt>: <em>Tag references</em>. Select only
	 *		those objects which are or are not referenced by tag objects.
	 *	<li><tt>{@link kAPI_PARAM_HAS_TERM_REFS}</tt>: <em>Term references</em>. Select only
	 *		those objects which are or are not referenced by term objects.
	 *	<li><tt>{@link kAPI_PARAM_HAS_NODE_REFS}</tt>: <em>Node references</em>. Select only
	 *		those objects which are or are not referenced by node objects.
	 *	<li><tt>{@link kAPI_PARAM_HAS_EDGE_REFS}</tt>: <em>Edge references</em>. Select only
	 *		those objects which are or are not referenced by edge objects.
	 *	<li><tt>{@link kAPI_PARAM_HAS_UNIT_REFS}</tt>: <em>Unit references</em>. Select only
	 *		those objects which are or are not referenced by unit objects.
	 *	<li><tt>{@link kAPI_PARAM_HAS_ENTITY_REFS}</tt>: <em>Entity references</em>. Select
	 *		only those objects which are or are not referenced by entity objects.
	 * </ul>
	 *
	 * @param CollectionObject		$theCollection		Data collection.
	 *
	 * @access protected
	 *
	 * @uses stringMatchPattern()
	 */
	protected function executeMatchLabelStrings( CollectionObject $theCollection )
	{
		//
		// Init local storage.
		//
		$limit = (int) $this->offsetGet( kAPI_PAGING_LIMIT );
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		
		//
		// Init results.
		//
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
		
		//
		// Set property.
		//
		$property = (string) ( $language == '*' )
				  ? (kTAG_LABEL.'.'.kTAG_TEXT)
				  : kTAG_TEXT;
		
		//
		// Init criteria.
		//
		$filter = $this->stringMatchPattern( $this->offsetGet( kAPI_PARAM_PATTERN ),
											 $this->offsetGet( kAPI_PARAM_OPERATOR ) );
		
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
		// Add tag reference count to criteria.
		//
		if( $this->offsetExists( kAPI_PARAM_HAS_TAG_REFS ) )
			$criteria[ (string) kTAG_TAG_COUNT ]
				= ( $this->offsetGet( kAPI_PARAM_HAS_TAG_REFS ) )
				? array( '$gt' => 0 )
				: 0;
		
		//
		// Add term reference count to criteria.
		//
		if( $this->offsetExists( kAPI_PARAM_HAS_TERM_REFS ) )
			$criteria[ (string) kTAG_TERM_COUNT ]
				= ( $this->offsetGet( kAPI_PARAM_HAS_TERM_REFS ) )
				? array( '$gt' => 0 )
				: 0;
		
		//
		// Add node reference count to criteria.
		//
		if( $this->offsetExists( kAPI_PARAM_HAS_NODE_REFS ) )
			$criteria[ (string) kTAG_NODE_COUNT ]
				= ( $this->offsetGet( kAPI_PARAM_HAS_NODE_REFS ) )
				? array( '$gt' => 0 )
				: 0;
		
		//
		// Add edge reference count to criteria.
		//
		if( $this->offsetExists( kAPI_PARAM_HAS_EDGE_REFS ) )
			$criteria[ (string) kTAG_EDGE_COUNT ]
				= ( $this->offsetGet( kAPI_PARAM_HAS_EDGE_REFS ) )
				? array( '$gt' => 0 )
				: 0;
		
		//
		// Add unit reference count to criteria.
		//
		if( $this->offsetExists( kAPI_PARAM_HAS_UNIT_REFS ) )
			$criteria[ (string) kTAG_UNIT_COUNT ]
				= ( $this->offsetGet( kAPI_PARAM_HAS_UNIT_REFS ) )
				? array( '$gt' => 0 )
				: 0;
		
		//
		// Add entity reference count to criteria.
		//
		if( $this->offsetExists( kAPI_PARAM_HAS_ENTITY_REFS ) )
			$criteria[ (string) kTAG_ENTITY_COUNT ]
				= ( $this->offsetGet( kAPI_PARAM_HAS_ENTITY_REFS ) )
				? array( '$gt' => 0 )
				: 0;
		
		//
		// Execute query.
		//
		$rs = $theCollection->matchAll(
			$criteria, kQUERY_ARRAY, array( (string) kTAG_LABEL => TRUE ) );
		
		//
		// Add affected count to paging.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_AFFECTED ]
			= $rs->count( FALSE );
		
		//
		// Skip records.
		//
		if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
			$rs->skip( $tmp );
		
		//
		// Iterate results.
		//
		$actual = 0;
		foreach( $rs as $record )
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
						$this->mResponse[ kAPI_RESPONSE_RESULTS ][] = $element[ kTAG_TEXT ];
						
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
		
	} // executeMatchLabelStrings.

		

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
		// Remove paging.
		//
		if( array_key_exists( kAPI_RESPONSE_PAGING, $this->mResponse ) )
			unset( $this->mResponse[ kAPI_RESPONSE_PAGING ] );
		
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
				 ? new \MongoRegex( '/^'.$thePattern.'$/i' )							// ==>
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

	 

} // class ServiceObject.


?>
