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
	 * The method will initialise the 
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
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
		catch( Exception $error )
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
	 * Execute request.
	 *
	 * The constructor accepts a single parameter which is a {@link Wrapper} instance.
	 *
	 * The method will initialise the 
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 */
	public function handleRequest()
	{
		//
		// TRY BLOCK.
		//
		try
		{
			//
			// Set ready state.
			//
			$this->mResponse[ kAPI_RESPONSE_STATUS ]
							[ kAPI_STATUS_STATE ] = kAPI_STATE_OK;
			
			//
			// Parse by operation.
			//
			switch( $op = $this->offsetGet( kAPI_REQUEST_OPERATION ) )
			{
				case kAPI_OP_PING:
					$this->executePing()
					break;
			}
		
			//
			// Send header.
			//
			header( 'Content-type: application/json' );
		
			exit( JsonEncode( $this->mResponse ) );									// ==>
		}
		
		//
		// CATCH BLOCK.
		//
		catch( Exception $error )
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
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function parseRequest()
	{
		//
		// Parse operation.
		//
		$this->parseOperation();
		
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
	 * If no operation was provided, the method will raise an exception.
	 *
	 * Derived classes should overload this method by first calling the parent method, then
	 * check whether the provided operation is valid.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function parseOperation()
	{
		//
		// Check operation.
		//
		if( ! array_key_exists( kAPI_REQUEST_OPERATION, $_REQUEST ) )
			throw new \Exception(
				"Missing service operation." );									// !@! ==>
	
		//
		// Set operation.
		//
		$this->offsetSet( kAPI_REQUEST_OPERATION, $_REQUEST[ kAPI_REQUEST_OPERATION ] );
		
	} // parseOperation.

	 
	/*===================================================================================
	 *	parseParameters																	*
	 *==================================================================================*/

	/**
	 * Parse parameters.
	 *
	 * The duty of this method is to parse the provided parameters and set them into the
	 * current object.
	 *
	 * Each parameter will be fed to the protected {@link parseParameter()} method which
	 * derived classes can overload to handle custom values.
	 *
	 * Derived classes should not need to overload this method.
	 *
	 * @access protected
	 *
	 * @throws Exception
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
			$params = JsonDecode( $_REQUEST( kAPI_REQUEST_PARAMETERS ) );
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
	 * This class will simply store the parameter and its value in the current object's
	 * array, derived classes may overload this method to skip or format parameters, then
	 * call the parent method if the parameter is to be considered.
	 *
	 * Both the key and the value are provided as references, this may allow derived classes
	 * to transform parameters.
	 *
	 * @param string				$theKey				Parameter key.
	 * @param mixed					$theValue			Parameter value.
	 *
	 * @access protected
	 */
	protected function parseParameter( &$theKey, &$theValue )
	{
		//
		// Store parameter.
		//
		$this->offsetSet( $theKey, $theValue );
		
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
	 * object, its duty is to validate the request.
	 *
	 * In this class we validate the following operations:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_PING}</tt>: Ping.
	 * </ul>
	 *
	 * Derived classes should first match custom operations or call the parent method.
	 *
	 * @access protected
	 *
	 * @throws Exception
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
 *						PROTECTED REQUEST EXECUTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	executePing																		*
	 *==================================================================================*/

	/**
	 * Execute ping request.
	 *
	 * This method will set the "pong" string in the status message.
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
	 * Note that the scrit will exit here.
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
		if( ($tmp = $theException->getCode()) !== NULL )
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
		
		exit( JsonEncode( $this->mResponse ) );										// ==>
	
	} // _Exception2Status.

	 

} // class ServiceObject.


?>
