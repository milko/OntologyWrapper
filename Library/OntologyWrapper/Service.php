<?php

/**
 * Service.php
 *
 * This file contains the definition of the {@link Service} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Wrapper;
use OntologyWrapper\Session;
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
 * Tags.
 *
 * This file contains the tag definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

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
 * Domains.
 *
 * This file contains the domains definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

/**
 * Functions.
 *
 * This file contains common function definitions.
 */
require_once( kPATH_LIBRARY_ROOT."/Functions.php" );

/**
 * PHPMailer class.
 *
 * This file contains the definition of the PHPMailer class.
 */
require_once( kPATH_CLASSES_ROOT."/PHPMailer-master/PHPMailerAutoload.php" );

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

	/**
	 * Root.
	 *
	 * This data member holds a flag indicating whether the root element of a structure was
	 * processed.
	 *
	 * @var boolean
	 */
	protected $mRootProcessed = FALSE;

		

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
			
			//
			// Set encrypted state.
			//
			if( ! array_key_exists( kAPI_STATUS_CRYPTED,
									$this->mResponse[ kAPI_RESPONSE_STATUS ] ) )
				$this->mResponse[ kAPI_RESPONSE_STATUS ]
								[ kAPI_STATUS_CRYPTED ] = FALSE;
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
			{
				//
				// Exclude protected operations.
				//
				switch( $_REQUEST[ kAPI_REQUEST_OPERATION ] )
				{
					case kAPI_OP_INVITE_USER:
					case kAPI_OP_USER_INVITE:
					case kAPI_OP_ADD_USER:
					case kAPI_OP_GET_USER:
					case kAPI_OP_MOD_USER:
					case kAPI_OP_GET_MANAGED:
					case kAPI_OP_CHECK_USER_CODE:
					case kAPI_OP_UPLOAD_TEMPLATE:
					case kAPI_OP_UPDATE_TEMPLATE:
					case kAPI_OP_USER_SESSION:
					case kAPI_OP_SESSION_PROGRESS:
					case kAPI_OP_PUT_DATA:
					case kAPI_OP_GET_DATA:
					case kAPI_OP_DEL_DATA:
					// MILKO: Remove in production.
						$this->mResponse[ kAPI_RESPONSE_REQUEST ]
							= $this->getArrayCopy();
					// MILKO: end.
						break;
					
					default:
						$this->mResponse[ kAPI_RESPONSE_REQUEST ]
							= $this->getArrayCopy();
						break;
				}
			}
			
			//
			// Execute request.
			//
			$this->executeRequest();
			
			//
			// Send header.
			//
			header( 'Content-type: application/json' );
		
			exit( JsonEncode( $this->mResponse ) );									// ==>
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
 *										STATIC INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GetStatisticsList																*
	 *==================================================================================*/

	/**
	 * Get statistics
	 *
	 * This method will return the statistics list related to the provided domain, the
	 * result is an array whose elements are structured as follows:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_PARAM_STAT}</tt>: The statistics identifier.
	 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: The statistics title.
	 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: The statistics description.
	 * </ul>
	 *
	 * If the domain has no associated statistics, the method will return an empty array.
	 *
	 * @param string				$theDomain			Statistics domain.
	 *
	 * @static
	 * @return array				List of statistics.
	 */
	static function GetStatisticsList( $theDomain )
	{
		//
		// Init local storage.
		//
		$list = Array();
		
		//
		// Parse by domain.
		//
		switch( $theDomain )
		{
			case kDOMAIN_HH_ASSESSMENT:
				$id = 'abdh-species-01';
				$list[ $id ]
					= array( kAPI_PARAM_STAT => $id,
							 kAPI_PARAM_RESPONSE_FRMT_NAME
								=> 'Annual Species grown by households, '
								  .'area and contribution to food and income' );
				$id = 'abdh-species-02';
				$list[ $id ]
					= array( kAPI_PARAM_STAT => $id,
							 kAPI_PARAM_RESPONSE_FRMT_NAME
								 	=> 'Annual species by season grown and water regime '
								 	  .'(number of households)' );
				$id = 'abdh-species-03';
				$list[ $id ]
					= array( kAPI_PARAM_STAT => $id,
							 kAPI_PARAM_RESPONSE_FRMT_NAME
								=> 'Varieties grown by annual species '
								  .'by type and demand for seed/planting material' );
				$id = 'abdh-species-04';
				$list[ $id ]
					= array( kAPI_PARAM_STAT => $id,
							 kAPI_PARAM_RESPONSE_FRMT_NAME
								=> 'Sources of seed/planting material '
								  .'for annual species' );
				$id = 'abdh-species-05';
				$list[ $id ]
					= array( kAPI_PARAM_STAT => $id,
							 kAPI_PARAM_RESPONSE_FRMT_NAME
								=> 'Frequency of seed replacement for annual species' );
				$id = 'abdh-species-06';
				$list[ $id ]
					= array( kAPI_PARAM_STAT => $id,
							 kAPI_PARAM_RESPONSE_FRMT_NAME
								=> 'Decisions on species by gender' );
				$id = 'abdh-species-07';
				$list[ $id ]
					= array( kAPI_PARAM_STAT => $id,
							 kAPI_PARAM_RESPONSE_FRMT_NAME
								=> 'Species used by households and by objective '
								  .'of use and type of uses' );
				break;
		}
		
		return $list;																// ==>
	
	} // GetStatisticsList.

		

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
	 *	<li><tt>{@link kAPI_OP_LIST_STATS}</tt>: List statistics by domain.
	 *	<li><tt>{@link kAPI_OP_LIST_DOMAINS}</tt>: List domains and unit counts.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_IDENTIFIER}</tt>: Match tag by identifier.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_SUMMARY_LABELS}</tt>: Match summary tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL}</tt>: Match summary tag by label.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_FORM}</tt>: Get node form.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_STRUCT}</tt>: Get node structure.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 *	<li><tt>{@link kAPI_OP_INVITE_USER}</tt>: Invite user.
	 *	<li><tt>{@link kAPI_OP_USER_INVITE}</tt>: User invitation.
	 *	<li><tt>{@link kAPI_OP_ADD_USER}</tt>: Add user.
	 *	<li><tt>{@link kAPI_OP_GET_USER}</tt>: Get user.
	 *	<li><tt>{@link kAPI_OP_MOD_USER}</tt>: Modify user.
	 *	<li><tt>{@link kAPI_OP_GET_MANAGED}</tt>: Get managed users.
	 *	<li><tt>{@link kAPI_OP_CHECK_USER_CODE}</tt>: Check user code.
	 *	<li><tt>{@link kAPI_OP_UPLOAD_TEMPLATE}</tt>: Submit data template upload.
	 *	<li><tt>{@link kAPI_OP_UPDATE_TEMPLATE}</tt>: Submit data template update.
	 *	<li><tt>{@link kAPI_OP_USER_SESSION}</tt>: Get user session.
	 *	<li><tt>{@link kAPI_OP_SESSION_PROGRESS}</tt>: Get session progress.
	 *	<li><tt>{@link kAPI_OP_PUT_DATA}</tt>: Put data.
	 *	<li><tt>{@link kAPI_OP_GET_DATA}</tt>: Get data.
	 *	<li><tt>{@link kAPI_OP_DEL_DATA}</tt>: Delete data.
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
			case kAPI_OP_LIST_STATS:
			case kAPI_OP_LIST_DOMAINS:
			case kAPI_OP_MATCH_TAG_LABELS:
			case kAPI_OP_MATCH_TAG_SUMMARY_LABELS:
			case kAPI_OP_MATCH_TERM_LABELS:
			case kAPI_OP_MATCH_TAG_BY_LABEL:
			case kAPI_OP_MATCH_TAG_BY_IDENTIFIER:
			case kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL:
			case kAPI_OP_MATCH_TERM_BY_LABEL:
			case kAPI_OP_GET_TAG_ENUMERATIONS:
			case kAPI_OP_GET_NODE_ENUMERATIONS:
			case kAPI_OP_GET_NODE_FORM:
			case kAPI_OP_GET_NODE_STRUCT:
			case kAPI_OP_MATCH_UNITS:
			case kAPI_OP_MATCH_UNITSnew:
			case kAPI_OP_GET_UNIT:
			case kAPI_OP_INVITE_USER:
			case kAPI_OP_USER_INVITE:
			case kAPI_OP_ADD_USER:
			case kAPI_OP_GET_USER:
			case kAPI_OP_MOD_USER:
			case kAPI_OP_GET_MANAGED:
			case kAPI_OP_CHECK_USER_CODE:
			case kAPI_OP_UPLOAD_TEMPLATE:
			case kAPI_OP_UPDATE_TEMPLATE:
			case kAPI_OP_USER_SESSION:
			case kAPI_OP_SESSION_PROGRESS:
			case kAPI_OP_PUT_DATA:
			case kAPI_OP_GET_DATA:
			case kAPI_OP_DEL_DATA:
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
			// Decrypt parameters.
			//
			switch( $this->offsetGet( kAPI_REQUEST_OPERATION ) )
			{
				case kAPI_OP_INVITE_USER:
				case kAPI_OP_USER_INVITE:
				case kAPI_OP_ADD_USER:
				case kAPI_OP_GET_USER:
				case kAPI_OP_MOD_USER:
				case kAPI_OP_GET_MANAGED:
				case kAPI_OP_UPLOAD_TEMPLATE:
				case kAPI_OP_UPDATE_TEMPLATE:
				case kAPI_OP_USER_SESSION:
				case kAPI_OP_SESSION_PROGRESS:
				case kAPI_OP_PUT_DATA:
				case kAPI_OP_GET_DATA:
				case kAPI_OP_DEL_DATA:
					$encoder = new Encoder();
					$decoded = $encoder->decodeData( $_REQUEST[ kAPI_REQUEST_PARAMETERS ] );
					$_REQUEST[ kAPI_REQUEST_PARAMETERS ] = $decoded;
					break;
			}
			
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
	protected function parseParameter( $theKey, $theValue )
	{
		//
		// Parse parameter.
		//
		switch( $theKey )
		{
			case kAPI_PARAM_OBJECT:
			case kAPI_PARAM_SUMMARY:
				$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PARAM_PATTERN:
			case kAPI_PARAM_NODE:
			case kAPI_PARAM_DOMAIN:
			case kAPI_PARAM_DATA:
			case kAPI_PARAM_STAT:
			case kAPI_PARAM_FILE_PATH:
			case kAPI_PARAM_SHAPE_OFFSET:
				if( strlen( $theValue ) )
					$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PARAM_ID:
			case kAPI_PARAM_TAG:
				if( is_array( $theValue )
				 || strlen( $theValue ) )
					$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PARAM_SHAPE:
			case kAPI_PARAM_CRITERIA:
			case kAPI_PARAM_EXCLUDED_TAGS:
				if( is_array( $theValue ) )
					$this->offsetSet( $theKey, $theValue );
				break;

			case kAPI_PARAM_OPERATOR:
				$this->parseStringMatchOperator( $theValue );
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

			case kAPI_REQUEST_USER:
				$this->offsetSet( $theKey, $this->parseUser( $theValue ) );
				break;
		}
	
	} // parseParameter.

	 
	/*===================================================================================
	 *	parseUser																		*
	 *==================================================================================*/

	/**
	 * Parse user.
	 *
	 * The duty of this method is to resolve the provided user and return its object.
	 *
	 * The method expects the parameter to be one of the following:
	 *
	 * <ul>
	 *	<li><tt>array</tt>: The user code/password combination, with the password encoded in
	 *		SHA1.
	 *	<li><tt>string</tt> The user ientifier, {@link kTAG_IDENTIFIER}.
	 * </ul>
	 *
	 * @param string				$theUser			User identifier.
	 *
	 * @access protected
	 * @return User					The user object.
	 *
	 * @throws Exception
	 *
	 * @see kAPI_REQUEST_USER
	 */
	protected function parseUser( $theUser )
	{
		return ( is_array( $theUser ) )
			 ? User::UserByPassword(
			 	$this->mWrapper, $theUser, kPORTAL_DOMAIN, TRUE )					// ==>
			 : User::UserByIdentifier(
			 	$this->mWrapper, $theUser, kPORTAL_DOMAIN, TRUE );					// ==>
		
	} // parseUser.

		

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
	 *	<li><tt>{@link kAPI_OP_LIST_STATS}</tt>: List statistics by domain.
	 *	<li><tt>{@link kAPI_OP_LIST_DOMAINS}</tt>: List domains and unit counts.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_IDENTIFIER}</tt>: Match tag by identifier.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_SUMMARY_LABELS}</tt>: Match summary tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL}</tt>: Match summary tag by label.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_FORM}</tt>: Get node form.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_STRUCT}</tt>: Get node structure.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 *	<li><tt>{@link kAPI_OP_INVITE_USER}</tt>: Invite user.
	 *	<li><tt>{@link kAPI_OP_USER_INVITE}</tt>: User invitation.
	 *	<li><tt>{@link kAPI_OP_ADD_USER}</tt>: Add user.
	 *	<li><tt>{@link kAPI_OP_GET_USER}</tt>: Get user.
	 *	<li><tt>{@link kAPI_OP_MOD_USER}</tt>: Modify user.
	 *	<li><tt>{@link kAPI_OP_GET_MANAGED}</tt>: Get managed users.
	 *	<li><tt>{@link kAPI_OP_CHECK_USER_CODE}</tt>: Check user code.
	 *	<li><tt>{@link kAPI_OP_UPLOAD_TEMPLATE}</tt>: Submit data template upload.
	 *	<li><tt>{@link kAPI_OP_UPDATE_TEMPLATE}</tt>: Submit data template update.
	 *	<li><tt>{@link kAPI_OP_USER_SESSION}</tt>: Get user session.
	 *	<li><tt>{@link kAPI_OP_SESSION_PROGRESS}</tt>: Get session progress.
	 *	<li><tt>{@link kAPI_OP_PUT_DATA}</tt>: Put data.
	 *	<li><tt>{@link kAPI_OP_GET_DATA}</tt>: Get data.
	 *	<li><tt>{@link kAPI_OP_DEL_DATA}</tt>: Delete data.
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
			case kAPI_OP_LIST_DOMAINS:
				break;
			
			case kAPI_OP_LIST_STATS:
				$this->validateListStats();
				break;
			
			case kAPI_OP_MATCH_TAG_LABELS:
			case kAPI_OP_MATCH_TAG_SUMMARY_LABELS:
			case kAPI_OP_MATCH_TERM_LABELS:
			case kAPI_OP_MATCH_TAG_BY_LABEL:
			case kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL:
			case kAPI_OP_MATCH_TERM_BY_LABEL:
				$this->validateMatchLabelStrings();
				break;
				
			case kAPI_OP_MATCH_TAG_BY_IDENTIFIER:
				$this->validateMatchTagByIdentifier();
				break;
				
			case kAPI_OP_GET_TAG_ENUMERATIONS:
				$this->validateGetTagEnumerations();
				break;
				
			case kAPI_OP_GET_NODE_ENUMERATIONS:
				$this->validateGetNodeEnumerations();
				break;
				
			case kAPI_OP_GET_NODE_FORM:
			case kAPI_OP_GET_NODE_STRUCT:
				$this->validateGetNodeStruct();
				break;
				
			case kAPI_OP_MATCH_UNITS:
				$this->validateMatchUnits();
				break;
				
			case kAPI_OP_MATCH_UNITSnew:
				$this->validateMatchUnitsNew();
				break;
				
			case kAPI_OP_GET_UNIT:
				$this->validateGetUnit();
				break;
				
			case kAPI_OP_INVITE_USER:
				$this->validateInviteUser();
				break;
				
			case kAPI_OP_GET_USER:
			case kAPI_OP_USER_INVITE:
			case kAPI_OP_GET_MANAGED:
				$this->validateGetUser();
				break;
				
			case kAPI_OP_ADD_USER:
				$this->validateAddUser();
				break;
				
			case kAPI_OP_MOD_USER:
				$this->validateModUser();
				break;
				
			case kAPI_OP_CHECK_USER_CODE:
				$this->validateCheckUserCode();
				break;
				
			case kAPI_OP_UPLOAD_TEMPLATE:
				$this->validateSubmitTemplate();
				break;
				
			case kAPI_OP_UPDATE_TEMPLATE:
				$this->validateUpdateTemplate();
				break;
				
			case kAPI_OP_USER_SESSION:
				$this->validateGetUserSession();
				break;
				
			case kAPI_OP_SESSION_PROGRESS:
				$this->validateSessionProgress();
				break;
				
			case kAPI_OP_PUT_DATA:
				$this->validatePutData();
				break;
				
			case kAPI_OP_GET_DATA:
			case kAPI_OP_DEL_DATA:
				$this->validateGetData();
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
	 *	validateListStats																*
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
	protected function validateListStats()
	{
		//
		// Assert domain.
		//
		if( ! $this->offsetExists( kAPI_PARAM_DOMAIN ) )
			throw new \Exception(
				"Missing domain parameter." );									// !@! ==>
		
		//
		// Normalise domain.
		//
		$domain = $this->offsetGet( kAPI_PARAM_DOMAIN );
		if( is_array( $domain ) )
		{
			if( ! count( $domain ) )
				throw new \Exception(
					"Empty domain parameter." );								// !@! ==>
			$domain = array_shift( $domain );
		}
		
		//
		// Save domain.
		//
		$this->offsetSet( kAPI_PARAM_DOMAIN, $domain );
		
	} // validateListStats.

	 
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
		
		//
		// Check excluded tags.
		//
		if( $this->offsetExists( kAPI_PARAM_EXCLUDED_TAGS ) )
		{
			switch( $this->offsetGet( kAPI_REQUEST_OPERATION ) )
			{
				case kAPI_OP_MATCH_TAG_LABELS:
				case kAPI_OP_MATCH_TAG_SUMMARY_LABELS:
				case kAPI_OP_MATCH_TAG_BY_LABEL:
				case kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL:
					if( count( $tmp = $this->offsetGet( kAPI_PARAM_EXCLUDED_TAGS ) ) )
					{
						foreach( $tmp as $key => $value )
						{
							if( substr( $value, 0, 1 ) != kTOKEN_TAG_PREFIX )
								$value = $this->mWrapper->getSerial( $value, TRUE );
							$value
								= $this->mWrapper
									->getObject( $value, TRUE )[ kTAG_ID_HASH ];
							$tmp[ $key ] = $value;
						}
						$this->offsetSet( kAPI_PARAM_EXCLUDED_TAGS, array_unique( $tmp ) );
						break;
					}
				
				default:
					$this->offsetUnset( kAPI_PARAM_EXCLUDED_TAGS );
					break;
			}
		}
		
	} // validateMatchLabelStrings.

	 
	/*===================================================================================
	 *	validateMatchTagByIdentifier													*
	 *==================================================================================*/

	/**
	 * Validate get tag by identifier service.
	 *
	 * This method will check whether the tag identifier was provided and will normalise it
	 * in an array.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_TAG
	 */
	protected function validateMatchTagByIdentifier()
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
		$tags = $this->offsetGet( kAPI_PARAM_TAG );
		
		//
		// Normalise parameter.
		//
		if( ! is_array( $tags ) )
			$tags = array( $tags );
		
		//
		// Resolve tags.
		//
		$list = Array();
		foreach( $tags as $tag )
		{
			//
			// Handle serial number.
			//
			if( substr( $tag, 0, 1 ) == kTOKEN_TAG_PREFIX )
			{
				$tag = $this->mWrapper->getObject( $tag, FALSE );
				if( $tag )
					$tag = $tag[ kTAG_NID ];
			}
			
			//
			// Handle string offsets.
			//
			else
			{
				$tag = $this->mWrapper->getSerial( $tag, FALSE );
				if( $tag )
					$tag = $this->mWrapper->getObject( $tag, TRUE )[ kTAG_NID ];
			}
			
			//
			// Add tag.
			//
			if( $tag )
				$list[ $tag ] = $tag;
		}
		
		//
		// Set parameter.
		//
		$this->offsetSet( kAPI_PARAM_TAG, array_values( $list ) );
		
	} // validateMatchTagByIdentifier.

	 
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
		if( substr( $tag, 0, 1 ) != kTOKEN_TAG_PREFIX )
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
	 *	validateGetNodeStruct																*
	 *==================================================================================*/

	/**
	 * Validate get node form service.
	 *
	 * This method will validate all service operations which return node form structures,
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
	protected function validateGetNodeStruct()
	{
		//
		// Check parameter.
		//
		if( ! $this->offsetExists( kAPI_PARAM_NODE ) )
			throw new \Exception(
				"Missing required node parameter." );							// !@! ==>
		
		//
		// Check reference count.
		//
		if( $this->offsetExists( kAPI_PARAM_REF_COUNT ) )
			$this->validateCollection( $this->offsetGet( kAPI_PARAM_REF_COUNT ) );
		
		//
		// Get node reference.
		//
		$id = $this->offsetGet( kAPI_PARAM_NODE );
		
		//
		// Get nodes collection.
		//
		$collection
			= Node::ResolveCollection(
				Node::ResolveDatabase(
					$this->mWrapper ) );
		
		//
		// Resolve node.
		//
		if( ! $node
			= $collection
				->matchOne(
					array( kTAG_NID => $id ),
					kQUERY_ARRAY,
					array( kTAG_NODE_TYPE => TRUE ) ) )
			$node
				= $collection
					->matchOne(
						array( kTAG_ID_PERSISTENT => $id ),
						kQUERY_ASSERT | kQUERY_ARRAY,
						array( kTAG_NODE_TYPE => TRUE ) );
		
		//
		// Assert node.
		//
		if( ! $node )
			throw new \Exception(
				"Invalid node reference [$id]." );								// !@! ==>
		
		//
		// Parse by operation.
		//
		switch( $op = $this->offsetGet( kAPI_REQUEST_OPERATION ) )
		{
			case kAPI_OP_GET_NODE_FORM:
				//
				// Assert root and form types.
				//
				if( (! array_key_exists( kTAG_NODE_TYPE, $node ))
				 || (! in_array( kTYPE_NODE_ROOT, $node[ kTAG_NODE_TYPE ] ))
				 || (! in_array( kTYPE_NODE_FORM, $node[ kTAG_NODE_TYPE ] )) )
					throw new \Exception(
						"Node must be a root form." );							// !@! ==>
				break;
			
			case kAPI_OP_GET_NODE_STRUCT:
				//
				// Assert root and structure types.
				//
				if( (! array_key_exists( kTAG_NODE_TYPE, $node ))
				 || (! in_array( kTYPE_NODE_ROOT, $node[ kTAG_NODE_TYPE ] ))
				 || (! in_array( kTYPE_NODE_STRUCT, $node[ kTAG_NODE_TYPE ] )) )
					throw new \Exception(
						"Node must be a root structure." );						// !@! ==>
				break;
		}
		
		
		//
		// Save node native identifier.
		//
		$this->offsetSet( kAPI_PARAM_NODE, $node[ kTAG_NID ] );
		
	} // validateGetNodeStruct.

	 
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
		$tmp = ( $this->offsetExists( kAPI_PARAM_CRITERIA ) )
			 ? $this->offsetGet( kAPI_PARAM_CRITERIA )
			 : Array();
		
		//
		// Update criteria for statistics.
		//
		if( $this->offsetExists( kAPI_PARAM_STAT )
		 && $this->offsetExists( kAPI_PARAM_DATA )
		 && $this->offsetExists( kAPI_PARAM_DOMAIN )
		 && ($this->offsetGet( kAPI_PARAM_DATA ) == kAPI_RESULT_ENUM_DATA_STAT) )
			$this->setStatisticsCriteria( $tmp,
										  $this->offsetGet( kAPI_PARAM_STAT ),
										  $this->offsetGet( kAPI_PARAM_DOMAIN ) );
		
		//
		// Resolve criteria tag references.
		//
		$criteria = Array();
		foreach( $tmp as $key => $value )
		{
			if( ($key != kAPI_PARAM_FULL_TEXT_OFFSET)
			 && (substr( $key, 0, 1 ) != kTOKEN_TAG_PREFIX) )
				$key = $this->mWrapper->getSerial( $key, TRUE );
			
			$criteria[ $key ] = $value;
		}
		
		//
		// Validate group.
		//
		if( $this->offsetExists( kAPI_PARAM_GROUP ) )
		{
			//
			// Reset results type.
			//
			$this->offsetUnset( kAPI_PARAM_SUMMARY );
			$this->offsetUnset( kAPI_PARAM_DOMAIN );
			$this->offsetUnset( kAPI_PARAM_DATA );
			$this->offsetUnset( kAPI_PARAM_STAT );
	
			//
			// Reset limits.
			//
			$this->offsetUnset( kAPI_PAGING_SKIP );
			$this->offsetUnset( kAPI_PAGING_LIMIT );
			
			//
			// Validate group.
			//
			$this->validateGroup();
			
			//
			// Collect untracked offsets.
			//
			$untracked = array_merge( UnitObject::InternalOffsets(),
									  UnitObject::ExternalOffsets(),
									  UnitObject::DynamicOffsets() );
			
			//
			// Add groups to criteria.
			//
			foreach( $this->offsetGet( kAPI_PARAM_GROUP_DATA ) as $tag => $data )
			{
				//
				// Skip domain.
				//
				if( $tag == kTAG_DOMAIN )
					continue;												// =>
				
				//
				// Skip untracked offsets.
				//
				if( in_array( $tag, $untracked ) )
					continue;												// =>
				
				//
				// Skip existing.
				//
				if( array_key_exists( $tag, $criteria ) )
				{
					//
					// Add offset to criteria.
					//
					if( ! array_key_exists( kAPI_PARAM_OFFSETS, $criteria[ $tag ] ) )
						$criteria[ $tag ][ kAPI_PARAM_OFFSETS ]
							= array( $data[ kAPI_PARAM_OFFSETS ] );
					
					//
					// Append offset to criteria.
					//
					elseif( ! in_array( $data[ kAPI_PARAM_OFFSETS ],
										$criteria[ $tag ][ kAPI_PARAM_OFFSETS ] ) )
						$criteria[ $tag ][ kAPI_PARAM_OFFSETS ][]
							= $data[ kAPI_PARAM_OFFSETS ];
						
					continue;												// =>
				}
				
				//
				// Add offset to criteria.
				//
				$criteria[ $data[ kAPI_PARAM_OFFSETS ] ]
					= array( kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_OFFSET );
			
			} // Iterating groups.
	
		} // Provided group.
		
		//
		// Handle ungrouped results.
		//
		else
		{
			//
			// Assert domain.
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
						
					case kAPI_RESULT_ENUM_DATA_STAT:
						//
						// Assert statistics type.
						//
						if( ! $this->offsetExists( kAPI_PARAM_STAT ) )
							throw new \Exception(
								"Missing statistics type." );					// !@! ==>
						//
						// Remove limits.
						//
						$this->offsetUnset( kAPI_PAGING_SKIP );
						$this->offsetUnset( kAPI_PAGING_LIMIT );
						break;
					
					default:
						throw new \Exception(
							"Invalid result type [$tmp]." );					// !@! ==>
						break;
				}
			}
			
			//
			// Validate summaries.
			//
			if( $this->offsetExists( kAPI_PARAM_SUMMARY ) )
			{
				$tmp = $this->offsetGet( kAPI_PARAM_SUMMARY );
				if( ! is_array( $tmp ) )
					throw new \Exception(
						"Invalid summaries list, expecting an array." );		// !@! ==>
				if( ! count( $tmp ) )
					$this->offsetUnset( kAPI_PARAM_SUMMARY );
				else
				{
					foreach( $tmp as $item )
					{
						if( ! is_array( $item ) )
							throw new \Exception(
								"Invalid summaries list, "
							   ."expecting an array of arrays." );				// !@! ==>
						
						foreach( $item as $offset => $value )
						{
							$element
								= $this->buildCriteria(
									$offset, $value, array( $offset ) );
							$offsets = explode( '.', $offset );
							$criteria[ $offsets[ count( $offsets ) - 1 ] ] = $element;
						}
					}
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
			// Handle native tag identifier.
			//
			if( substr( $shape, 0, 1 ) != kTOKEN_TAG_PREFIX )
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
		// MILKO - Need to check if this is right.
		//
		elseif( $this->offsetExists( kAPI_PARAM_SHAPE_OFFSET )
			 && (! $this->offsetExists( kAPI_PARAM_GROUP )) )
		{
			//
			// Get shape offset.
			//
			$tmp = (string) $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET );
			
			//
			// Add shape if not already there.
			//
			if( ! array_key_exists( $tmp, $criteria ) )
				$criteria[ $tmp ]
					= array( kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_SHAPE );
		
		} // Has shape offset and not grouping.
		
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
	 *	validateMatchUnitsNew															*
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
	protected function validateMatchUnitsNew()
	{
		//
		// Init criteria.
		//
		if( ! $this->offsetExists( kAPI_PARAM_CRITERIA ) )
			$this->offsetSet( kAPI_PARAM_CRITERIA, Array() );
		
		//
		// Get criteria.
		//
		$criteria = $this->offsetGet( kAPI_PARAM_CRITERIA );
		
		//
		// Validate domain selection.
		//
		if( $this->offsetExists( kAPI_PARAM_DOMAIN ) )
			$this->validateMatchUnitsDomain( $criteria );
		
		//
		// Validate group selection.
		//
		else
			$this->validateMatchUnitsGroup( $criteria );
		
		//
		// Validate shape offset.
		//
		$this->validateShapeOffset( $criteria );
		
		//
		// Validate shape parameter.
		//
		$this->validateShapeParameter( $criteria );
		
		//
		// Update criteria.
		//
		$this->offsetSet( kAPI_PARAM_CRITERIA, $criteria );
		
	} // validateMatchUnitsNew.

	 
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
						"Missing shape offset." );								// !@! ==>
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
					"Invalid result type [$tmp]." );							// !@! ==>
				break;
		}
		
	} // validateGetUnit.

	 
	/*===================================================================================
	 *	validateInviteUser																*
	 *==================================================================================*/

	/**
	 * Validate invite user service.
	 *
	 * This method will call the validation process for the user invite service, the method
	 * will ensure all required data is provided and will format it as needed.
	 *
	 * The method will also ensure the inviting user (already asserted) has the necessary
	 * permissions.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateInviteUser()
	{
		//
		// Assert inviter.
		//
		if( $this->offsetExists( kAPI_REQUEST_USER ) )
		{
			//
			// Check roles.
			//
			$user = $this->offsetGet( kAPI_REQUEST_USER );
			if( $user->canInvite() )
			{
				//
				// Check invite data.
				//
				if( $this->offsetExists( kAPI_PARAM_OBJECT ) )
				{
					//
					// Check invite data format.
					//
					$data = $this->offsetGet( kAPI_PARAM_OBJECT );
					if( is_array( $data ) )
					{
						//
						// Assert and normalise required data.
						//
						if( ! array_key_exists( kTAG_ENTITY_EMAIL, $data ) )
							throw new \Exception(
								"Missing e-mail." );							// !@! ==>
						if( ! array_key_exists( kTAG_NAME, $data ) )
							throw new \Exception(
								"Missing name." );								// !@! ==>
						if( ! array_key_exists( kTAG_ROLES, $data ) )
							throw new \Exception(
								"Missing roles." );								// !@! ==>
						if( ! array_key_exists( kTAG_ENTITY_PGP_KEY, $data ) )
							throw new \Exception(
								"Missing key." );								// !@! ==>
						if( ! array_key_exists( kTAG_ENTITY_PGP_FINGERPRINT, $data ) )
							throw new \Exception(
								"Missing fingerprint." );						// !@! ==>
					
						//
						// Normalise e-mail.
						//
						$data[ kTAG_STRUCT_LABEL ] = $data[ kTAG_ENTITY_EMAIL ];
						$data[ kTAG_ENTITY_EMAIL ]
							= array(
								array( kTAG_TYPE => kTYPE_LIST_DEFAULT,
									   kTAG_TEXT => $data[ kTAG_STRUCT_LABEL ] ) );
					
						//
						// Add referrer.
						//
						$data[ kTAG_ENTITY_AFFILIATION ]
							= array(
								array( kTAG_TYPE => kTYPE_LIST_REFERRER,
									   kTAG_USER_REF => $user[ kTAG_NID ] ) );
					
						//
						// Update invitation.
						//
						$this->offsetSet( kAPI_PARAM_OBJECT, $data );
				
					} // Provided invite data as array.
			
					else
						throw new \Exception(
							"Invalid invitation format." );						// !@! ==>
			
				} // Has invite data.
			
				else
					throw new \Exception(
						"Missing invitation." );								// !@! ==>
		
			} // User can invite.
		
			else
				throw new \Exception(
					"Requestor cannot invite." );								// !@! ==>
		
		} // Provided inviter.
		
		else
			throw new \Exception(
				"Missing requestor." );											// !@! ==>
		
	} // validateInviteUser.

	 
	/*===================================================================================
	 *	validateAddUser																	*
	 *==================================================================================*/

	/**
	 * Validate add user service.
	 *
	 * This method will validate the operation parameters and perform the following
	 * actions:
	 *
	 * <ul>
	 *	<li>Instantiate the new user from the {@link kAPI_PARAM_OBJECT} local property.
	 *	<li>Instantiate the new user's referrer.
	 *	<li>Check if referrer can invite users.
	 *	<li>Remove the related invitation from the referrer's object.
	 *	<li>Save the new user object in the {@link kAPI_PARAM_OBJECT} property.
	 *	<li>Save the referrer object in the {@link kAPI_REFERRER} property.
	 * </ul>
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
		// Check object.
		//
		if( $this->offsetExists( kAPI_PARAM_OBJECT ) )
		{
			//
			// Instantiate user.
			//
			$user = new User( $this->offsetGet( kAPI_PARAM_OBJECT ) );
			
			//
			// Get referrer.
			//
			if( $user->offsetExists( kTAG_ENTITY_AFFILIATION ) )
			{
				//
				// Find referrer.
				//
				$referrer = NULL;
				foreach( $user->offsetGet( kTAG_ENTITY_AFFILIATION ) as $item )
				{
					if( $item[ kTAG_TYPE ] == kTYPE_LIST_REFERRER )
					{
						$referrer = $item[ kTAG_USER_REF ];
						break;												// =>
					}
				}
				
				//
				// Check referrer.
				//
				if( $referrer !== NULL )
				{
					//
					// Instantiate referrer.
					//
					$referrer = new User( $this->mWrapper, $referrer, TRUE );
					
					//
					// Check roles.
					//
					if( $referrer->offsetExists( kTAG_ROLES ) )
					{
						//
						// Check permissions.
						//
						if( in_array( kTYPE_ROLE_INVITE,
									  $referrer->offsetGet( kTAG_ROLES ) ) )
						{
							//
							// Check invitations.
							//
							if( $referrer->offsetExists( kTAG_INVITES ) )
							{
								//
								// Locate invitation.
								//
								$invite = NULL;
								$invites = $referrer->offsetGet( kTAG_INVITES );
								foreach( $invites as $key => $value )
								{
									if( $value[ kTAG_ENTITY_PGP_FINGERPRINT ]
										== $user->offsetGet( kTAG_ENTITY_PGP_FINGERPRINT ) )
									{
										$invite = $key;
										break;								// =>
									}
								}
								
								//
								// Check invitation.
								//
								if( $invite !== NULL )
								{
									//
									// Remove invitation.
									//
									unset( $invites[ $invite ] );
									if( count( $invites ) )
										$referrer->offsetSet( kTAG_INVITES,
															  array_values( $invites ) );
									else
										$referrer->offsetUnset( kTAG_INVITES );
									
									//
									// Save referrer.
									//
									$this->offsetSet( kAPI_REFERRER, $referrer );
									
									//
									// Save user.
									//
									$this->offsetSet( kAPI_PARAM_OBJECT, $user );
								
								} // Found invitation.
								
								else
									throw new \Exception(
										"Missing invitation." );				// !@! ==>
							
							} // Referrer has invitations.
		
							else
								throw new \Exception(
									"Missing invitations." );					// !@! ==>
							
						} // Referrer can invite.
		
						else
							throw new \Exception(
								"Referrer is not permitted." );					// !@! ==>
					
					} // Referrer has roles.
		
					else
						throw new \Exception(
							"Referrer has no roles." );							// !@! ==>
				
				} // Has referrer.
		
				else
					throw new \Exception(
						"Missing referrer." );									// !@! ==>
			
			} // User is affiliated.
		
			else
				throw new \Exception(
					"Missing affiliation." );									// !@! ==>
		
		} // Has user object
		
		else
			throw new \Exception(
				"Missing user object." );										// !@! ==>
		
	} // validateAddUser.

	 
	/*===================================================================================
	 *	validateModUser																	*
	 *==================================================================================*/

	/**
	 * Validate modify user service.
	 *
	 * This method will validate the operation parameters and perform the following
	 * actions:
	 *
	 * <ul>
	 *	<li>Assert the {@link kAPI_PARAM_ID} parameter.
	 *	<li>Load the user referenced {@link kAPI_PARAM_ID} parameter.
	 *	<li>Assert the {@link kAPI_PARAM_OBJECT} parameter.
	 *	<li>Check the {@link kAPI_PARAM_OBJECT} parameter format.
	 *	<li>Assert the {@link kAPI_REQUEST_USER} parameter.
	 *	<li>Verify whether the user referenced by the {@link kAPI_REQUEST_USER} can perform
	 *		the update.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateModUser()
	{
		//
		// Check object.
		//
		if( $this->offsetExists( kAPI_PARAM_ID ) )
		{
			//
			// Instantiate user.
			//
			$user = $this->offsetGet( kAPI_PARAM_ID );
			$this->offsetSet(
				kAPI_PARAM_ID,
				( is_array( $user ) )
					? User::UserByPassword(
			 			$this->mWrapper, $user, kPORTAL_DOMAIN, TRUE )
			 		: User::UserByIdentifier(
			 			$this->mWrapper, $user, kPORTAL_DOMAIN, TRUE ) );
			 
			 //
			 // Assert user data.
			 //
			if( $this->offsetExists( kAPI_PARAM_OBJECT ) )
			{
				//
				// Validate user data structure.
				//
				if( is_array( $this->offsetGet( kAPI_PARAM_OBJECT ) ) )
				{
					//
					// Assert requesting user.
					//
					if( $this->offsetExists( kAPI_REQUEST_USER ) )
					{
						//
						// Check if requesting user is referrer.
						//
						if( !
							count(
								$this->offsetGet( kAPI_PARAM_ID )
									->referrers(
										$this->offsetGet( kAPI_PARAM_ID ),
										$this->mWrapper ) ) )
							throw new \Exception(
								"Authorisation failure." );						// !@! ==>
					
					} // Provided requesting user.
		
					else
						throw new \Exception(
							"Missing requesting user." );						// !@! ==>
				
				} // User data is array.
		
				else
					throw new \Exception(
						"Invalid user data format." );							// !@! ==>
			
			} // Provided user data.
		
			else
				throw new \Exception(
					"Missing user data." );										// !@! ==>
		
		} // Provided user identifier
		
		else
			throw new \Exception(
				"Missing user identifier." );									// !@! ==>
		
	} // validateModUser.

	 
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
		// Assert identifier.
		//
		if( ! $this->offsetExists( kAPI_PARAM_ID ) )
			throw new \Exception(
				"Missing unit identifier parameter." );							// !@! ==>

		//
		// Validate identifier.
		//
		$param = $this->offsetExists( kAPI_PARAM_ID );
		if( is_array( $param )
		 && ( ($this->offsetGet( kAPI_REQUEST_OPERATION ) != kAPI_OP_GET_USER)
		   || (count( $param ) != 2) ) )
			throw new \Exception(
				"Invalid user identifier parameter format." );					// !@! ==>

		//
		// Assert result kind.
		//
		if( ! $this->offsetExists( kAPI_PARAM_DATA ) )
			throw new \Exception(
				"Missing results kind parameter." );							// !@! ==>
		
		//
		// Validate by format type.
		//
		switch( $tmp = $this->offsetGet( kAPI_PARAM_DATA ) )
		{
			case kAPI_RESULT_ENUM_DATA_RECORD:
			case kAPI_RESULT_ENUM_DATA_FORMAT:
				break;
			
			default:
				throw new \Exception(
					"Invalid result type [$tmp]." );							// !@! ==>
				break;
		}
		
	} // validateGetUser.

	 
	/*===================================================================================
	 *	validateCheckUserCode															*
	 *==================================================================================*/

	/**
	 * Validate check user code service.
	 *
	 * This method will validate the operation parameters and perform the following
	 * actions:
	 *
	 * <ul>
	 *	<li>Assert the {@link kAPI_PARAM_ID} parameter.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateCheckUserCode()
	{
		//
		// Check object.
		//
		if( ! $this->offsetExists( kAPI_PARAM_ID ) )
			throw new \Exception(
				"Missing user code." );											// !@! ==>
		
	} // validateCheckUserCode.

	 
	/*===================================================================================
	 *	validateSubmitTemplate															*
	 *==================================================================================*/

	/**
	 * Validate upload service.
	 *
	 * This method will call the validation process for the template submission service, the
	 * method will ensure all required data is provided and that the submitter user has the
	 * required permissions.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateSubmitTemplate()
	{
		//
		// Assert submitter.
		//
		if( $this->offsetExists( kAPI_REQUEST_USER ) )
		{
			//
			// Check roles.
			//
			$user = $this->offsetGet( kAPI_REQUEST_USER );
			$roles = $user->offsetGet( kTAG_ROLES );
			if( $roles !== NULL )
			{
				//
				// Check if he can submit templates.
				//
				if( in_array( kTYPE_ROLE_UPLOAD, $roles ) )
				{
					//
					// Check template reference.
					//
					if( $this->offsetExists( kAPI_PARAM_FILE_PATH ) )
					{
						//
						// Normalise to string.
						//
						$this->offsetSet(
							kAPI_PARAM_FILE_PATH,
							(string) $this->offsetGet( kAPI_PARAM_FILE_PATH ) );
					
					} // Provided template reference.
		
					else
						throw new \Exception(
							"Missing template reference." );					// !@! ==>
				
				} // User can upload.
		
				else
					throw new \Exception(
						"Requestor cannot upload." );							// !@! ==>
		
			} // User has roles.
		
			else
				throw new \Exception(
					"Requestor has no roles." );								// !@! ==>
		
		} // Provided submitter.
		
		else
			throw new \Exception(
				"Missing requestor." );											// !@! ==>
		
	} // validateSubmitTemplate.

	 
	/*===================================================================================
	 *	validateUpdateTemplate															*
	 *==================================================================================*/

	/**
	 * Validate update service.
	 *
	 * This method will call the validation process for the template update service, the
	 * method will ensure all required data is provided and that the submitter user has the
	 * required permissions.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateUpdateTemplate()
	{
		//
		// Assert submitter.
		//
		if( $this->offsetExists( kAPI_REQUEST_USER ) )
		{
			//
			// Check roles.
			//
			$user = $this->offsetGet( kAPI_REQUEST_USER );
			$roles = $user->offsetGet( kTAG_ROLES );
			if( $roles !== NULL )
			{
				//
				// Check if he can submit templates.
				//
				if( in_array( kTYPE_ROLE_UPLOAD, $roles ) )
				{
					//
					// Check current session.
					//
					if( $user->offsetExists( kTAG_SESSION ) )
					{
						//
						// Check session type.
						//
						$session = new Session( $this->mWrapper,
												$user->offsetGet( kTAG_SESSION ) );
						if( $session->offsetGet( kTAG_SESSION_TYPE )
							== kTYPE_SESSION_UPLOAD )
						{
							//
							// Save session.
							//
							$this->offsetSet( kAPI_PARAM_ID, $session );
						
						} // Is an upload session.
		
						else
							throw new \Exception(
								"There is no upload session." );							// !@! ==>
					
					} // Has session.
		
					else
						throw new \Exception(
							"These is no template to update." );							// !@! ==>
				
				} // User can upload.
		
				else
					throw new \Exception(
						"Requestor cannot upload." );							// !@! ==>
		
			} // User has roles.
		
			else
				throw new \Exception(
					"Requestor has no roles." );								// !@! ==>
		
		} // Provided submitter.
		
		else
			throw new \Exception(
				"Missing requestor." );											// !@! ==>
		
	} // validateUpdateTemplate.

	 
	/*===================================================================================
	 *	validateGetUserSession															*
	 *==================================================================================*/

	/**
	 * Validate get user session service.
	 *
	 * This method will call the validation process for the get user session service, the
	 * method will ensure all required data is provided and that the submitter user has the
	 * required permissions.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateGetUserSession()
	{
		//
		// Assert submitter.
		//
		if( $this->offsetExists( kAPI_REQUEST_USER ) )
		{
			//
			// Check roles.
			//
			$user = $this->offsetGet( kAPI_REQUEST_USER );
			$roles = $user->offsetGet( kTAG_ROLES );
			if( $roles !== NULL )
			{
				//
				// Check if he can submit templates.
				//
				if( ! in_array( kTYPE_ROLE_UPLOAD, $roles ) )
					throw new \Exception(
						"Requestor cannot upload." );							// !@! ==>
		
			} // User has roles.
		
			else
				throw new \Exception(
					"Requestor has no roles." );								// !@! ==>
		
		} // Provided submitter.
		
		else
			throw new \Exception(
				"Missing requestor." );											// !@! ==>
		
	} // validateGetUserSession.

	 
	/*===================================================================================
	 *	validateSessionProgress															*
	 *==================================================================================*/

	/**
	 * Validate session progress service.
	 *
	 * This method will call the validation process for the session progress service, the
	 * method will ensure all required data is provided and that the submitter user has the
	 * required permissions.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateSessionProgress()
	{
		//
		// Assert submitter.
		//
		if( $this->offsetExists( kAPI_REQUEST_USER ) )
		{
			//
			// Check roles.
			//
			$user = $this->offsetGet( kAPI_REQUEST_USER );
			$roles = $user->offsetGet( kTAG_ROLES );
			if( $roles !== NULL )
			{
				//
				// Check if he can submit templates.
				//
				if( in_array( kTYPE_ROLE_UPLOAD, $roles ) )
				{
					//
					// Check session reference.
					//
					if( ! $this->offsetExists( kAPI_PARAM_ID ) )
						throw new \Exception(
							"Missing session reference." );						// !@! ==>
				
				} // User can upload.
		
				else
					throw new \Exception(
						"Requestor cannot upload." );							// !@! ==>
		
			} // User has roles.
		
			else
				throw new \Exception(
					"Requestor has no roles." );								// !@! ==>
		
		} // Provided submitter.
		
		else
			throw new \Exception(
				"Missing requestor." );											// !@! ==>
		
	} // validateSessionProgress.

	 
	/*===================================================================================
	 *	validatePutData																	*
	 *==================================================================================*/

	/**
	 * Validate put data service.
	 *
	 * This method will call the validation process for the put data service, the method
	 * will ensure all required parameters are provided and that the submitter user has the
	 * required permissions.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validatePutData()
	{
		//
		// Assert submitter.
		//
		if( $this->offsetExists( kAPI_REQUEST_USER ) )
		{
			//
			// Check roles.
			//
			$user = $this->offsetGet( kAPI_REQUEST_USER );
			$roles = $user->offsetGet( kTAG_ROLES );
			if( $roles !== NULL )
			{
				//
				// Check if he can edit pages.
				//
				if( in_array( kTYPE_ROLE_EDIT, $roles ) )
				{
					//
					// Check data object.
					//
					if( ! $this->offsetExists( kAPI_PARAM_OBJECT ) )
						throw new \Exception(
							"Missing data object." );							// !@! ==>
				
				} // User can upload.
		
				else
					throw new \Exception(
						"Requestor cannot edit." );								// !@! ==>
		
			} // User has roles.
		
			else
				throw new \Exception(
					"Requestor has no roles." );								// !@! ==>
		
		} // Provided submitter.
		
		else
			throw new \Exception(
				"Missing requestor." );											// !@! ==>
		
	} // validatePutData.

	 
	/*===================================================================================
	 *	validateGetData																	*
	 *==================================================================================*/

	/**
	 * Validate get data service.
	 *
	 * This method will call the validation process for the get data service, the method
	 * will ensure all required parameters are provided and that the submitter user has the
	 * required permissions.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateGetData()
	{
		//
		// Assert submitter.
		//
		if( $this->offsetExists( kAPI_REQUEST_USER ) )
		{
			//
			// Check roles.
			//
			$user = $this->offsetGet( kAPI_REQUEST_USER );
			$roles = $user->offsetGet( kTAG_ROLES );
			if( $roles !== NULL )
			{
				//
				// Check if he can edit pages.
				//
				if( in_array( kTYPE_ROLE_EDIT, $roles ) )
				{
					//
					// Check data object.
					//
					if( ! $this->offsetExists( kAPI_PARAM_ID ) )
						throw new \Exception(
							"Missing object identifier." );						// !@! ==>
				
				} // User can upload.
		
				else
					throw new \Exception(
						"Requestor cannot edit." );								// !@! ==>
		
			} // User has roles.
		
			else
				throw new \Exception(
					"Requestor has no roles." );								// !@! ==>
		
		} // Provided submitter.
		
		else
			throw new \Exception(
				"Missing requestor." );											// !@! ==>
		
	} // validateGetData.

	 
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
	 * data member.
	 *
	 * @access protected
	 *
	 * @throws Exception
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
		$value = $tags = Array();
		foreach( $tmp as $key => $val )
		{
			//
			// Handle offsets.
			//
			if( $val[ kAPI_PARAM_INPUT_TYPE ] == kAPI_PARAM_INPUT_OFFSET )
			{
				//
				// Resolve offset elements.
				//
				foreach( explode( '.', $key ) as $offset )
				{
					$tag = $this->mWrapper->getObject( $offset, TRUE )[ kTAG_NID ];
					if( ! in_array( $tag, $tags ) )
						$tags[] = $tag;
				}
				
			} // Offset input.
			
			//
			// Handle inputs.
			//
			else
			{
				//
				// Handle tags.
				//
				if( $key != kAPI_PARAM_FULL_TEXT_OFFSET )
				{
					//
					// Handle tag sequence numbers.
					//
					if( substr( $key, 0, 1 ) == kTOKEN_TAG_PREFIX )
						$key = $this->mWrapper->getObject( $key, TRUE )[ kTAG_NID ];
			
					//
					// Add tag.
					//
					if( ! in_array( $key, $tags ) )
						$tags[] = $key;
			
				} // Not a full-text search.
			
			} // Not an offset assertion.
	
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
		$fields = array( kTAG_NID => TRUE, kTAG_ID_HASH => TRUE,
						 kTAG_TERMS => TRUE, kTAG_DATA_TYPE => TRUE,
						 $offsets_tag => TRUE );
		
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
			// Handle offset assertion.
			//
			elseif( $criteria[ kAPI_PARAM_INPUT_TYPE ] == kAPI_PARAM_INPUT_OFFSET )
			{
				//
				// Create cluster entry.
				//
				$this->mFilter[ $tag ]
					= array( kAPI_PARAM_VALUE_COUNT => 1,
							 kAPI_PARAM_CRITERIA => Array() );
				
				//
				// Reference cluster, values counter and criteria.
				//
				$cluster_ref = & $this->mFilter[ $tag ];
				$criteria_ref = & $cluster_ref[ kAPI_PARAM_CRITERIA ];
				
				//
				// Allocate criteria.
				//
				$criteria_ref[ kTAG_OBJECT_OFFSETS ] = Array();
				$criteria_ref = & $criteria_ref[ kTAG_OBJECT_OFFSETS ];
							 
				//
				// Set input and data types.
				//
				$criteria_ref[ kAPI_PARAM_INPUT_TYPE ] = kAPI_PARAM_INPUT_STRING;
				$criteria_ref[ kAPI_PARAM_DATA_TYPE ] = kTYPE_STRING;
		
				//
				// Set index flag.
				//
				$criteria_ref[ kAPI_PARAM_INDEX ] = TRUE;
				
				//
				// Set pattern.
				//
				$criteria_ref[ kAPI_PARAM_PATTERN ] = (string) $tag;
				
				//
				// Set operator.
				//
				$criteria_ref[ kAPI_PARAM_OPERATOR ] = array( kOPERATOR_EQUAL );
				
				//
				// Set offsets.
				//
				$criteria_ref[ kAPI_PARAM_OFFSETS ] = array( kTAG_OBJECT_OFFSETS );
				
				continue;													// =>
			
			} // Offset assertion.
			
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
				$tag_sequence = $tag_object[ kTAG_ID_HASH ];
			
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
					
					//
					// Validate shape.
					//
					$this->validateShape( $criteria[ kAPI_PARAM_SHAPE ] );
			
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
				// Handle only offsets.
				//
				if( array_key_exists( kAPI_PARAM_OFFSETS, $criteria ) )
					$criteria_ref[ $tag_sequence ][ kAPI_PARAM_OFFSETS ]
						= $criteria[ kAPI_PARAM_OFFSETS ];
					
				//
				// Handle only tag.
				//
				else
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
		// MILKO - Was boolean, now is an array: should revise or rewrite resolveFilter().
		/*
			$criteria_ref[ kAPI_PARAM_INDEX ]
				= ( array_key_exists( $tag_sequence, $indexes ) );
		*/
			$criteria_ref[ kAPI_PARAM_INDEX ]
				= ( array_key_exists( $tag_sequence, $indexes ) )
				? $indexes[ $tag_sequence ]
				: FALSE;
			
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
			// Handle offsets subset.
			//
			if( array_key_exists( kAPI_PARAM_OFFSETS, $criteria ) )
			{
				//
				// Set provided offsets.
				//
				$criteria_ref[ kAPI_PARAM_OFFSETS ] = $criteria[ kAPI_PARAM_OFFSETS ];
				
				//
				// Check tag offsets.
				//
				if( array_key_exists( $offsets_tag, $tag_object ) )
					$criteria_ref[ kAPI_QUERY_OFFSETS ]
						= ( count( array_diff( $tag_object[ $offsets_tag ],
											   $criteria[ kAPI_PARAM_OFFSETS ] ) ) > 0 );
			
			} // Provided offsets.
			
			//
			// Load tag offsets.
			//
			elseif( array_key_exists( $offsets_tag, $tag_object ) )
				$criteria_ref[ kAPI_PARAM_OFFSETS ]
					= $tag_object[ $offsets_tag ];
			
			//
			// Complain if missing.
			//
			else
				throw new \Exception(
					"Missing selection offsets for tag [$tag]." );				// !@! ==>
			
		} // Iterating criteria.
		
	} // validateSearchCriteria.

	 
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
	 * @see kAPI_PARAM_COLLECTION_UNIT kAPI_PARAM_COLLECTION_USER
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
						 kAPI_PARAM_COLLECTION_UNIT, kAPI_PARAM_COLLECTION_USER );
			
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
	 *	validateGroup																	*
	 *==================================================================================*/

	/**
	 * Validate group offset
	 *
	 * This method will validate the provided group offset and raise an exception if the
	 * related tag does not have the {@link kTYPE_SUMMARY} kind.
	 *
	 * The method will set the normalised list of group leaf tag sequence numbers in the
	 * {@link kAPI_PARAM_GROUP} parameter and collect all group element information in the
	 * internal {@link kAPI_PARAM_GROUP_DATA} parameter.
	 *
	 * Group elements can be provided as tag native identifiers or offsets.
	 *
	 * @access protected
	 * @return array				Normalised group list.
	 *
	 * @throws Exception
	 *
	 * @see kAPI_PARAM_GROUP kAPI_PARAM_GROUP_DATA
	 */
	protected function validateGroup()
	{
		//
		// Init local storage.
		//
		$data = Array();
		$group = $this->offsetGet( kAPI_PARAM_GROUP );
		
		//
		// Handle default group.
		//
		if( (! is_array( $group ))
		 || (! count( $group )) )
			$group = array( kTAG_DOMAIN );
		
		//
		// Normalise list.
		//
		$group = array_values( array_unique( $group ) );
		
		//
		// Check domain position.
		//
		$tmp = array_search( kTAG_DOMAIN, $group );
		if( $tmp
		 && ($tmp != (count( $group ) - 1)) )
			throw new \Exception(
				"Domain must be last group element." );							// !@! ==>
		
		//
		// Add domain.
		//
		if( $tmp === FALSE )
			$group[] = kTAG_DOMAIN;
		
		//
		// Update group parameter.
		//
		$this->offsetSet( kAPI_PARAM_GROUP, $group );
	
		//
		// Iterate group elements.
		//
		foreach( $group as $key => $element )
		{
			//
			// Init loop storage.
			//
			$structs = Array();
			
			//
			// Handle tag serial.
			//
			if( substr( $element, 0, 1 ) == kTOKEN_TAG_PREFIX )
			{
				//
				// Handle offset.
				//
				if( strpos( $element, '.' ) )	// Cannot have period.
				{
					//
					// Split structures.
					//
					$tmp = explode( '.', $element );
					if( count( $tmp ) > 1 )
						$tag
							= $this->mWrapper->getObject(
								$tmp[ count( $tmp ) - 1 ], TRUE );
			
					else
						throw new \Exception(
							"Invalid group element [$element]." );				// !@! ==>
				
					//
					// Collect structures.
					//
					for( $i = 0; $i < (count( $tmp ) - 1); $i++ )
						$structs[ $i ] = $tmp[ $i ];
				
				} // Offset.
				
				//
				// Handle serial.
				//
				else
				 	$tag = $this->mWrapper->getObject( $element, TRUE );
			
			} // Serial or offset.
			
			//
			// Handle tag native identifier.
			//
			else
			{
				$tmp = $this->mWrapper->getSerial( $element, TRUE );
			 	$tag = $this->mWrapper->getObject( $tmp, TRUE );
			 	$element = $tag[ kTAG_ID_HASH ];
			
			} // Native identifier.
		
			//
			// Check kind.
			//
			if( (! array_key_exists( kTAG_DATA_KIND, $tag ))
			 || (! in_array( kTYPE_SUMMARY, $tag[ kTAG_DATA_KIND ] )) )
				throw new \Exception(
					"Invalid kind for group element [$element]." );				// !@! ==>
			
			//
			// Set index.
			//
			$index = $tag[ kTAG_ID_HASH ];
		
			//
			// Load element info.
			//
			$data[ $index ] = Array();
			$data[ $index ][ kAPI_PARAM_OFFSETS ] = $element;
			$data[ $index ][ kAPI_PARAM_DATA_TYPE ] = $tag[ kTAG_DATA_TYPE ];
			
			//
			// Count lists.
			//
			$count = 0;
			if( $tag[ kTAG_DATA_TYPE ] == kTYPE_SET )
				$count++;
			foreach( $structs as $struct )
			{
				$tag = $this->mWrapper->getObject( $struct, TRUE );
				if( array_key_exists( kTAG_DATA_KIND, $tag )
				 && in_array( kTYPE_LIST, $tag[ kTAG_DATA_KIND ] ) )
					$count++;
			}
			
			//
			// Set lists count.
			//
			$data[ $index ][ kAPI_PARAM_GROUP_LIST ] = $count;
	
		} // Iterating group.
		
		//
		// Load group information.
		//
		$this->offsetSet( kAPI_PARAM_GROUP_DATA, $data );
		
	} // validateGroup.

	 
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
			
			//
			// Check coordinates structure.
			//
			if( ! is_array( $geom ) )
				throw new \Exception(
					"The geometry must be an array." );							// !@! ==>
			
			//
			// Check by type.
			//
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
					// Cast coordinates.
					//
					$geom[ 0 ] = (double) $geom[ 0 ];
					$geom[ 1 ] = (double) $geom[ 1 ];
					
					//
					// Check circle.
					//
					if( $type == 'Point' )
					{
						//
						// Check radius.
						//
						if( ! array_key_exists( kTAG_RADIUS, $theValue ) )
							throw new \Exception(
								"Shape of type [$type] "
							   ."must feature the radius." );					// !@! ==>
						
						//
						// Cast radius.
						//
						$theValue[ kTAG_RADIUS ] = (int) $theValue[ kTAG_RADIUS ];
					}
					
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
	 *	<li><tt>{@link kAPI_OP_LIST_STATS}</tt>: List statistics by domain.
	 *	<li><tt>{@link kAPI_OP_LIST_DOMAINS}</tt>: List domains and unit counts.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_LABELS}</tt>: Match tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_IDENTIFIER}</tt>: Match tag by identifier.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_SUMMARY_LABELS}</tt>: Match summary tag labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_LABELS}</tt>: Match term labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_TAG_BY_LABEL}</tt>: Match tag by labels.
	 *	<li><tt>{@link kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL}</tt>: Match summary tag by label.
	 *	<li><tt>{@link kAPI_OP_MATCH_TERM_BY_LABEL}</tt>: Match term by labels.
	 *	<li><tt>{@link kAPI_OP_GET_TAG_ENUMERATIONS}</tt>: Get tag enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_ENUMERATIONS}</tt>: Get node enumerations.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_FORM}</tt>: Get node form.
	 *	<li><tt>{@link kAPI_OP_GET_NODE_STRUCT}</tt>: Get node structure.
	 *	<li><tt>{@link kAPI_OP_MATCH_UNITS}</tt>: Match domains.
	 *	<li><tt>{@link kAPI_OP_INVITE_USER}</tt>: Invite user.
	 *	<li><tt>{@link kAPI_OP_USER_INVITE}</tt>: User invitation.
	 *	<li><tt>{@link kAPI_OP_ADD_USER}</tt>: Add user.
	 *	<li><tt>{@link kAPI_OP_GET_USER}</tt>: Get user.
	 *	<li><tt>{@link kAPI_OP_MOD_USER}</tt>: Modify user.
	 *	<li><tt>{@link kAPI_OP_GET_MANAGED}</tt>: Get managed users.
	 *	<li><tt>{@link kAPI_OP_CHECK_USER_CODE}</tt>: Check user code.
	 *	<li><tt>{@link kAPI_OP_UPLOAD_TEMPLATE}</tt>: Upload template.
	 *	<li><tt>{@link kAPI_OP_UPDATE_TEMPLATE}</tt>: Update template.
	 *	<li><tt>{@link kAPI_OP_USER_SESSION}</tt>: Get user session.
	 *	<li><tt>{@link kAPI_OP_SESSION_PROGRESS}</tt>: Get session progress.
	 *	<li><tt>{@link kAPI_OP_PUT_DATA}</tt>: Put data.
	 *	<li><tt>{@link kAPI_OP_GET_DATA}</tt>: Get data.
	 *	<li><tt>{@link kAPI_OP_DEL_DATA}</tt>: Delete data.
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
			
			case kAPI_OP_LIST_STATS:
				$this->executeListStats();
				break;
			
			case kAPI_OP_LIST_DOMAINS:
				$this->executeListDomains();
				break;
				
			case kAPI_OP_MATCH_TAG_LABELS:
			case kAPI_OP_MATCH_TAG_SUMMARY_LABELS:
				$this->executeMatchTagLabels();
				break;
				
			case kAPI_OP_MATCH_TERM_LABELS:
				$this->executeMatchTermLabels();
				break;
				
			case kAPI_OP_MATCH_TAG_BY_LABEL:
			case kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL:
				$this->executeMatchTagByLabel();
				break;
				
			case kAPI_OP_MATCH_TAG_BY_IDENTIFIER:
				$this->executeMatchTagByIdentifier();
				break;
				
			case kAPI_OP_MATCH_TERM_BY_LABEL:
				$this->executeMatchTermByLabel();
				break;
				
			case kAPI_OP_GET_TAG_ENUMERATIONS:
				$this->executeGetTagEnumerations();
				break;
				
			case kAPI_OP_GET_NODE_FORM:
			case kAPI_OP_GET_NODE_STRUCT:
				$this->executeGetNodeStructure();
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
				
			case kAPI_OP_INVITE_USER:
				$this->executeInviteUser();
				break;
				
			case kAPI_OP_ADD_USER:
				$this->executeAddUser();
				break;
				
			case kAPI_OP_GET_USER:
			case kAPI_OP_USER_INVITE:
				$this->executeGetUser();
				break;
				
			case kAPI_OP_GET_MANAGED:
				$this->executeGetManagedUsers();
				break;
				
			case kAPI_OP_MOD_USER:
				$this->executeModUser();
				break;
				
			case kAPI_OP_CHECK_USER_CODE:
				$this->executeCheckUserCode();
				break;
				
			case kAPI_OP_UPLOAD_TEMPLATE:
				$this->executeSubmitTemplate();
				break;
				
			case kAPI_OP_UPDATE_TEMPLATE:
				$this->executeUpdateTemplate();
				break;
				
			case kAPI_OP_USER_SESSION:
				$this->executeGetUserSession();
				break;
				
			case kAPI_OP_SESSION_PROGRESS:
				$this->executeSessionProgress();
				break;
				
			case kAPI_OP_PUT_DATA:
				$this->executePutData();
				break;
				
			case kAPI_OP_GET_DATA:
				$this->executeGetData();
				break;
				
			case kAPI_OP_DEL_DATA:
				$this->executeDelData();
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
		$ref[ "kAPI_REQUEST_USER" ] = kAPI_REQUEST_USER;
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
		$ref[ "kAPI_STATUS_CRYPTED" ] = kAPI_STATUS_CRYPTED;
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
		$ref[ "kAPI_OP_LIST_STATS" ] = kAPI_OP_LIST_STATS;
		$ref[ "kAPI_OP_LIST_DOMAINS" ] = kAPI_OP_LIST_DOMAINS;
		$ref[ "kAPI_OP_MATCH_TAG_LABELS" ] = kAPI_OP_MATCH_TAG_LABELS;
		$ref[ "kAPI_OP_MATCH_TAG_SUMMARY_LABELS" ] = kAPI_OP_MATCH_TAG_SUMMARY_LABELS;
		$ref[ "kAPI_OP_MATCH_TERM_LABELS" ] = kAPI_OP_MATCH_TERM_LABELS;
		$ref[ "kAPI_OP_MATCH_TAG_BY_LABEL" ] = kAPI_OP_MATCH_TAG_BY_LABEL;
		$ref[ "kAPI_OP_MATCH_TAG_BY_IDENTIFIER" ] = kAPI_OP_MATCH_TAG_BY_IDENTIFIER;
		$ref[ "kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL" ] = kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL;
		$ref[ "kAPI_OP_MATCH_TERM_BY_LABEL" ] = kAPI_OP_MATCH_TERM_BY_LABEL;
		$ref[ "kAPI_OP_GET_TAG_ENUMERATIONS" ] = kAPI_OP_GET_TAG_ENUMERATIONS;
		$ref[ "kAPI_OP_GET_NODE_ENUMERATIONS" ] = kAPI_OP_GET_NODE_ENUMERATIONS;
		$ref[ "kAPI_OP_GET_NODE_FORM" ] = kAPI_OP_GET_NODE_FORM;
		$ref[ "kAPI_OP_GET_NODE_STRUCT" ] = kAPI_OP_GET_NODE_STRUCT;
		$ref[ "kAPI_OP_MATCH_UNITS" ] = kAPI_OP_MATCH_UNITS;
		$ref[ "kAPI_OP_GET_UNIT" ] = kAPI_OP_GET_UNIT;
		$ref[ "kAPI_OP_INVITE_USER" ] = kAPI_OP_INVITE_USER;
		$ref[ "kAPI_OP_ADD_USER" ] = kAPI_OP_ADD_USER;
		$ref[ "kAPI_OP_GET_USER" ] = kAPI_OP_GET_USER;
		$ref[ "kAPI_OP_GET_MANAGED" ] = kAPI_OP_GET_MANAGED;
		$ref[ "kAPI_OP_CHECK_USER_CODE" ] = kAPI_OP_CHECK_USER_CODE;
		
		
		//
		// Load request parameters.
		//
		$ref[ "kAPI_PARAM_PATTERN" ] = kAPI_PARAM_PATTERN;
		$ref[ "kAPI_PARAM_REF_COUNT" ] = kAPI_PARAM_REF_COUNT;
		$ref[ "kAPI_PARAM_TAG" ] = kAPI_PARAM_TAG;
		$ref[ "kAPI_PARAM_TERM" ] = kAPI_PARAM_TERM;
		$ref[ "kAPI_PARAM_NODE" ] = kAPI_PARAM_NODE;
		$ref[ "kAPI_PARAM_PARENT_NODE" ] = kAPI_PARAM_PARENT_NODE;
		$ref[ "kAPI_PARAM_OPERATOR" ] = kAPI_PARAM_OPERATOR;
		$ref[ "kAPI_PARAM_RANGE_MIN" ] = kAPI_PARAM_RANGE_MIN;
		$ref[ "kAPI_PARAM_RANGE_MAX" ] = kAPI_PARAM_RANGE_MAX;
		$ref[ "kAPI_PARAM_INPUT_TYPE" ] = kAPI_PARAM_INPUT_TYPE;
		$ref[ "kAPI_PARAM_CRITERIA" ] = kAPI_PARAM_CRITERIA;
		$ref[ "kAPI_PARAM_OBJECT" ] = kAPI_PARAM_OBJECT;
		$ref[ "kAPI_PARAM_ID" ] = kAPI_PARAM_ID;
		$ref[ "kAPI_PARAM_DOMAIN" ] = kAPI_PARAM_DOMAIN;
		$ref[ "kAPI_PARAM_DATA" ] = kAPI_PARAM_DATA;
		$ref[ "kAPI_PARAM_STAT" ] = kAPI_PARAM_STAT;
		$ref[ "kAPI_PARAM_GROUP" ] = kAPI_PARAM_GROUP;
		$ref[ "kAPI_PARAM_SUMMARY" ] = kAPI_PARAM_SUMMARY;
		$ref[ "kAPI_PARAM_SHAPE" ] = kAPI_PARAM_SHAPE;
		$ref[ "kAPI_PARAM_SHAPE_OFFSET" ] = kAPI_PARAM_SHAPE_OFFSET;
		$ref[ "kAPI_PARAM_EXCLUDED_TAGS" ] = kAPI_PARAM_EXCLUDED_TAGS;
		$ref[ "kAPI_PARAM_FILE_PATH" ] = kAPI_PARAM_FILE_PATH;
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
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_NAME" ] = kAPI_PARAM_RESPONSE_FRMT_NAME;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_INFO" ] = kAPI_PARAM_RESPONSE_FRMT_INFO;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_DISP" ] = kAPI_PARAM_RESPONSE_FRMT_DISP;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_VALUE" ] = kAPI_PARAM_RESPONSE_FRMT_VALUE;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_MAP_LABEL" ] = kAPI_PARAM_RESPONSE_FRMT_MAP_LABEL;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_MAP_SHAPE" ] = kAPI_PARAM_RESPONSE_FRMT_MAP_SHAPE;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_LINK" ] = kAPI_PARAM_RESPONSE_FRMT_LINK;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_TAG" ] = kAPI_PARAM_RESPONSE_FRMT_TAG;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_TERM" ] = kAPI_PARAM_RESPONSE_FRMT_TERM;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_NODE" ] = kAPI_PARAM_RESPONSE_FRMT_NODE;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_EDGE" ] = kAPI_PARAM_RESPONSE_FRMT_EDGE;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_USER" ] = kAPI_PARAM_RESPONSE_FRMT_USER;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_UNIT" ] = kAPI_PARAM_RESPONSE_FRMT_UNIT;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_SERV" ] = kAPI_PARAM_RESPONSE_FRMT_SERV;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_DOCU" ] = kAPI_PARAM_RESPONSE_FRMT_DOCU;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_STATS" ] = kAPI_PARAM_RESPONSE_FRMT_STATS;
		$ref[ "kAPI_PARAM_RESPONSE_FRMT_HEAD" ] = kAPI_PARAM_RESPONSE_FRMT_HEAD;
		
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
		$ref[ "kOPERATOR_IRANGE" ] = kOPERATOR_IRANGE;
		$ref[ "kOPERATOR_ERANGE" ] = kOPERATOR_ERANGE;
		
		//
		// Load modifiers.
		//
		$ref[ "kOPERATOR_NOCASE" ] = kOPERATOR_NOCASE;
		
		//
		// Generic parameters.
		//
		$ref[ "kAPI_PARAM_INDEX" ] = kAPI_PARAM_INDEX;
		$ref[ "kAPI_PARAM_DATA_TYPE" ] = kAPI_PARAM_DATA_TYPE;
		$ref[ "kAPI_PARAM_DATA_KIND" ] = kAPI_PARAM_DATA_KIND;
		$ref[ "kAPI_PARAM_VALUE_COUNT" ] = kAPI_PARAM_VALUE_COUNT;
		$ref[ "kAPI_PARAM_OFFSETS" ] = kAPI_PARAM_OFFSETS;
		$ref[ "kAPI_PARAM_GROUP_DATA" ] = kAPI_PARAM_GROUP_DATA;
		$ref[ "kAPI_PARAM_GROUP_LIST" ] = kAPI_PARAM_GROUP_DATA;
		$ref[ "kAPI_SHAPE_TAG" ] = kAPI_SHAPE_TAG;
		
		//
		// Load result type parameters.
		//
		$ref[ "kAPI_RESULT_ENUM_DATA_COLUMN" ] = kAPI_RESULT_ENUM_DATA_COLUMN;
		$ref[ "kAPI_RESULT_ENUM_DATA_RECORD" ] = kAPI_RESULT_ENUM_DATA_RECORD;
		$ref[ "kAPI_RESULT_ENUM_DATA_FORMAT" ] = kAPI_RESULT_ENUM_DATA_FORMAT;
		$ref[ "kAPI_RESULT_ENUM_DATA_MARKER" ] = kAPI_RESULT_ENUM_DATA_MARKER;
		
		//
		// Load collection reference enumerated set.
		//
		$ref[ "kAPI_PARAM_COLLECTION_TAG" ] = Tag::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_TERM" ] = Term::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_NODE" ] = Node::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_EDGE" ] = Edge::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_UNIT" ] = UnitObject::kSEQ_NAME;
		$ref[ "kAPI_PARAM_COLLECTION_USER" ] = User::kSEQ_NAME;
		
		//
		// Load form input enumerated set.
		//
		$ref[ "kAPI_PARAM_INPUT_TEXT" ] = kAPI_PARAM_INPUT_TEXT;
		$ref[ "kAPI_PARAM_INPUT_STRING" ] = kAPI_PARAM_INPUT_STRING;
		$ref[ "kAPI_PARAM_INPUT_RANGE" ] = kAPI_PARAM_INPUT_RANGE;
		$ref[ "kAPI_PARAM_INPUT_ENUM" ] = kAPI_PARAM_INPUT_ENUM;
		$ref[ "kAPI_PARAM_INPUT_SHAPE" ] = kAPI_PARAM_INPUT_SHAPE;
		$ref[ "kAPI_PARAM_INPUT_OFFSET" ] = kAPI_PARAM_INPUT_OFFSET;
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
		$ref[ kAPI_PARAM_COLLECTION_USER ] = kTAG_USER_COUNT;
		
	} // executeListReferenceCountParameters.

	 
	/*===================================================================================
	 *	executeListStats																*
	 *==================================================================================*/

	/**
	 * Execute list statistics request.
	 *
	 * This method will handle the {@link kAPI_OP_LIST_STATS} operation.
	 *
	 * @access protected
	 */
	protected function executeListStats()
	{
		//
		// Initialise results.
		//
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
		$ref = & $this->mResponse[ kAPI_RESPONSE_RESULTS ];
		
		//
		// Load results.
		//
		$this->getStatistics( $ref,
							  $this->offsetGet( kAPI_REQUEST_LANGUAGE ),
							  $this->offsetGet( kAPI_PARAM_DOMAIN ) );
		
	} // executeListStats.

	 
	/*===================================================================================
	 *	executeListDomains																*
	 *==================================================================================*/

	/**
	 * Execute list domains request.
	 *
	 * This method will handle the {@link kAPI_OP_LIST_DOMAINS} operation.
	 *
	 * @access protected
	 */
	protected function executeListDomains()
	{
		//
		// Init local storage.
		//
		$lang = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		$fields = array( kTAG_LABEL => TRUE, kTAG_DEFINITION => TRUE );
		$col_terms = $this->mWrapper->resolveCollection( Term::kSEQ_NAME );
		$col_units = $this->mWrapper->resolveCollection( UnitObject::kSEQ_NAME );
		
		//
		// Initialise results.
		//
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
		$ref = & $this->mResponse[ kAPI_RESPONSE_RESULTS ];
		
		//
		// Get domains.
		//
		$domains = $col_units->connection()->distinct( kTAG_DOMAIN );
		
		//
		// Load domains.
		//
		$rs = $col_terms->matchAll( array( kTAG_NID => array( '$in' => $domains ) ),
									kQUERY_ARRAY,
									$fields );
		
		//
		// Load domains.
		//
		foreach( $rs as $term )
		{
			//
			// Allocate entry.
			//
			$ref[ $term[ kTAG_NID ] ] = Array();
			$ref_item = & $ref[ $term[ kTAG_NID ] ];
			
			//
			// Get label and definition.
			//
			$ref_item[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
				= OntologyObject::SelectLanguageString( $term[ kTAG_LABEL ], $lang );
			if( array_key_exists( kTAG_DEFINITION, $term ) )
				$ref_item[ kAPI_PARAM_RESPONSE_FRMT_INFO ]
					= OntologyObject::SelectLanguageString( $term[ kTAG_DEFINITION ],
															$lang );
			
			//
			// Get count.
			//
			$ref_item[ kAPI_PARAM_RESPONSE_COUNT ]
				= $col_units->matchOne( array( kTAG_DOMAIN => $term[ kTAG_NID ] ),
										kQUERY_COUNT );
		
		} // Iterating domain tags.
		
	} // executeListDomains.

	 
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
	 *	executeMatchTagByIdentifier														*
	 *==================================================================================*/

	/**
	 * Match tag by identifier.
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
	protected function executeMatchTagByIdentifier()
	{
		//
		// Init local storage.
		//
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = Array();
		
		//
		// Get tags list.
		//
		$tags = $this->offsetGet( kAPI_PARAM_TAG );
		if( count( $tags ) )
		{
			//
			// Match tags.
			//
			$criteria = array( kTAG_NID => array( '$in' => $tags ) );
			$rs
				= Tag::ResolveCollection(
					Tag::ResolveDatabase(
						$this->mWrapper ) )
							->matchAll(
								array( kTAG_NID => array( '$in' => $tags ) ),
								kQUERY_OBJECT );
			
			//
			// Skip records.
			//
			if( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
				$rs->skip( (int) $tmp );
		
			//
			// Set cursor limit.
			//
			if( ($tmp = $this->offsetGet( kAPI_PAGING_LIMIT )) !== NULL )
				$rs->limit( (int) $tmp );
		
			//
			// Format results.
			//
			$this->executeSerialiseResults( $rs, TRUE );
		
		} // Something to search.
		
		//
		// Handle no results.
		//
		else
		{
			$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_AFFECTED ] =
			$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_ACTUAL ] = 0;
		}
		
	} // executeMatchTagByIdentifier.

	 
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
	 *	executeGetNodeStructure															*
	 *==================================================================================*/

	/**
	 * Get node structure.
	 *
	 * The method will return the structure corresponding to the node stored in
	 * {@link kAPI_PARAM_NODE}.
	 *
	 * @access protected
	 */
	protected function executeGetNodeStructure()
	{
		//
		// Init local storage.
		//
		$node = $this->offsetGet( kAPI_PARAM_NODE );
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		
		//
		// Add collection reference count.
		//
		$ref_count = NULL;
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
						$ref_count = (string) kTAG_TAG_COUNT;
						break;
					
					case kAPI_PARAM_COLLECTION_TERM:
						$ref_count = (string) kTAG_TERM_COUNT;
						break;
					
					case kAPI_PARAM_COLLECTION_NODE:
						$ref_count = (string) kTAG_NODE_COUNT;
						break;
					
					case kAPI_PARAM_COLLECTION_EDGE:
						$ref_count = (string) kTAG_EDGE_COUNT;
						break;
					
					case kAPI_PARAM_COLLECTION_UNIT:
						$ref_count = (string) kTAG_UNIT_COUNT;
						break;
					
					case kAPI_PARAM_COLLECTION_USER:
						$ref_count = (string) kTAG_USER_COUNT;
						break;
				}
			}
		}
		
		//
		// Initialise result.
		//
		if( ! array_key_exists( kAPI_RESPONSE_RESULTS, $this->mResponse ) )
			$this->mResponse[ kAPI_RESPONSE_RESULTS ]
				= Array();
		
		//
		// Traverse form structure.
		//
		$this->traverseStructure(
			$this->mResponse[ kAPI_RESPONSE_RESULTS ], $node, $language, NULL, $ref_count );
		
	} // executeGetNodeStructure.

	 
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
			
				case kAPI_RESULT_ENUM_DATA_STAT:
					$this->executeUnitStats(
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
	 *	executeInviteUser																*
	 *==================================================================================*/

	/**
	 * Invite user.
	 *
	 * The method will add the invitation record to the inviting user and send the e-mail
	 * invitation.
	 *
	 * @access protected
	 */
	protected function executeInviteUser()
	{
		//
		// Init local storage.
		//
		$user = $this->offsetGet( kAPI_REQUEST_USER );
		$data = $this->offsetGet( kAPI_PARAM_OBJECT );
		$invites = ( $user->offsetExists( kTAG_INVITES ) )
				 ? $user->offsetGet( kTAG_INVITES )
				 : Array();
		
		//
		// Complete invitation fields.
		//
		if( ! array_key_exists( kTAG_ENTITY_FNAME, $data ) )
			$data[ kTAG_ENTITY_FNAME ] = '';
		if( ! array_key_exists( kTAG_ENTITY_LNAME, $data ) )
			$data[ kTAG_ENTITY_LNAME ] = '';
		if( ! array_key_exists( kTAG_CONN_CODE, $data ) )
			$data[ kTAG_CONN_CODE ] = '';
		if( ! array_key_exists( kTAG_CONN_PASS, $data ) )
			$data[ kTAG_CONN_PASS ] = '';
		
		//
		// Init mail parameters.
		//
		$mail_subject = file_get_contents( kPATH_LIBRARY_ROOT
										  ."/settings/invite_subject.txt" );
		$message_text = file_get_contents( kPATH_LIBRARY_ROOT
										  ."/settings/email_template_basic.txt" );
		$message_html = file_get_contents( kPATH_LIBRARY_ROOT
										  ."/settings/email_template_basic.html" );
		$mail_credentials
			= explode( "\t",
					   file_get_contents( kPATH_LIBRARY_ROOT
										 ."/settings/mailer.txt" ) );
		$mail_sender_email = 'No Reply';
		foreach( $user[ kTAG_ENTITY_EMAIL ] as $tmp )
		{
			if( $tmp[ kTAG_TYPE ] == kTYPE_LIST_DEFAULT )
			{
				$mail_sender_email = $tmp[ kTAG_TEXT ];
				break;														// =>
			}
		}
		
		//
		// Compile activation URL.
		//
		$url = 'http://'
			  .kPORTAL_HOST
			  .'/Activation?f='
			  .urlencode( base64_encode( $data[ kTAG_ENTITY_PGP_FINGERPRINT ] ) );
		
		//
		// Compile TEXT message.
		//
		$message_text = str_replace( '@user_name@', $data[ kTAG_NAME ], $message_text );
		$message_text = str_replace( '@inviter_name@', $user[ kTAG_NAME ], $message_text );
		$message_text = str_replace( '@inviter_email@', $tmp[ kTAG_TEXT ], $message_text );
		$message_text = str_replace( '@url@', $url, $message_text );
		
		//
		// Compile HTML message.
		//
		$message_html = str_replace( '@user_name@', $data[ kTAG_NAME ], $message_html );
		$message_html = str_replace( '@inviter_name@', $user[ kTAG_NAME ], $message_html );
		$message_html = str_replace( '@inviter_email@', $tmp[ kTAG_TEXT ], $message_html );
		$message_html = str_replace( '@url@', $url, $message_html );
		
		//
		// Init mail.
		//
		$mailer = new \PHPMailer();
		$mailer->Host = kPORTAL_HOST;
		$mailer->CharSet = 'UTF-8';
		$mailer->ContentType = 'text/html';
		$mailer->isHTML( TRUE );
		$mailer->SMTPAuth = TRUE;
		
		//
		// Set mail content.
		//
		$mailer->Subject = $mail_subject;
		$mailer->Body    = $message_html;
		$mailer->AltBody = $message_text;
		
		//
		// Set mail credentials.
		//
		$mailer->From = kPORTAL_MAILER;
		$mailer->FromName = kPORTAL_MAILER_NAME;
		$mailer->addAddress( $data[ kTAG_STRUCT_LABEL ], $data[ kTAG_NAME ] );
		$mailer->addReplyTo( $user[ kTAG_NAME ], $mail_sender_email );
		$mailer->Username = $mail_credentials[ 0 ];
		$mailer->Password = $mail_credentials[ 1 ];
		
		//
		// Send mail.
		//
		if( $mailer->send() )
		{
			//
			// Locate invitation.
			//
			$index = count( $invites );
			foreach( $invites as $key => $value )
			{
				if( $value[ kTAG_STRUCT_LABEL ]
					== $data[ kTAG_STRUCT_LABEL ] )
				{
					$index = $key;
					break;													// =>
				}
			}
		
			//
			// Add invite to user.
			//
			$invites[ $index ] = $data;
			$user->offsetSet( kTAG_INVITES, $invites );
		
			//
			// Update user.
			//
			$user->commit();
		
		} // E-mail was sent.
		
		else
			throw new Exception
				( "Unable to send mail: ["
				 .$mailer->ErrorInfo
				 ."]" );														// !@! ==>
		
	} // executeInviteUser.

	 
	/*===================================================================================
	 *	executeAddUser																	*
	 *==================================================================================*/

	/**
	 * Add user.
	 *
	 * The method will add the user stored in the {@link kAPI_PARAM_OBJECT} property and
	 * update the referrer stored in the {@link kAPI_REFERRER} property.
	 *
	 * @access protected
	 */
	protected function executeAddUser()
	{
		//
		// Insert user.
		//
		$this->offsetGet( kAPI_PARAM_OBJECT )->commit( $this->mWrapper );
		
		//
		// Update referrer.
		//
		$this->offsetGet( kAPI_REFERRER )->commit();
		
		//
		// Prepare get user.
		//
		$this->offsetSet( kAPI_PARAM_ID,
						  $this->offsetGet( kAPI_PARAM_OBJECT )[ kTAG_NID ] );
		$this->offsetUnset( kAPI_REFERRER );
		$this->offsetUnset( kAPI_PARAM_OBJECT );
		$this->offsetSet( kAPI_REQUEST_OPERATION, kAPI_OP_GET_USER );
		
		//
		// Get new user.
		//
		$this->executeGetUser();
		
	} // executeAddUser.

	 
	/*===================================================================================
	 *	executeGetUser																	*
	 *==================================================================================*/

	/**
	 * Get user.
	 *
	 * The method will match the user and return a clustered or formatted result.
	 *
	 * The user identifier will be treated as follows:
	 *
	 * <ul>
	 *	<li><tt>{@link kAPI_OP_GET_USER}</tt>: Get user operation.
	 *	 <ul>
	 *		<li><tt>array</tt>: User code and password encoded in SHA1.
	 *		<li><tt>string</tt>: User fingerprint.
	 *	 </ul>
	 *	<li><tt>{@link kAPI_OP_USER_INVITE}</tt>: Invite user operation.
	 *	 <ul>
	 *		<li><tt>string</tt>: Invited user fingerprint.
	 *	 </ul>
	 * </ul>
	 *
	 * Note that this method will also be called for the {@link kAPI_OP_USER_INVITE}
	 * operation.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function executeGetUser()
	{
		//
		// Init local storage.
		//
		$encoder = new Encoder();
		$param = $this->offsetGet( kAPI_PARAM_ID );
		$operation = $this->offsetGet( kAPI_REQUEST_OPERATION );
		$options = kFLAG_FORMAT_OPT_DYNAMIC | kFLAG_FORMAT_OPT_VALUES;
		if( (! is_array( $param ))
		 && (! $this->isManagedUser( $param ))
		 && ($operation != kAPI_OP_USER_INVITE) )
			$options |= kFLAG_FORMAT_OPT_PRIVATE;
		
		//
		// Set filter.
		//
		switch( $operation )
		{
			case kAPI_OP_GET_USER:
				$this->mFilter = ( is_array( $param ) )
							   ? array( kTAG_CONN_CODE => array_shift( $param ),
										kTAG_CONN_PASS => array_shift( $param ) )
							   : array( kTAG_ENTITY_PGP_FINGERPRINT => $param );
				break;
			
			case kAPI_OP_USER_INVITE:
				$offset = kTAG_INVITES.'.'.kTAG_ENTITY_PGP_FINGERPRINT;
				$this->mFilter = array( $offset => $param );
				break;
			
			default:
				throw new \Exception(
					"Bug: reached method from wrong operation." );				// !@! ==>
		}
		
		//
		// Parse by result type.
		//
		$results = Array();
		switch( $this->offsetGet( kAPI_PARAM_DATA ) )
		{
			case kAPI_RESULT_ENUM_DATA_RECORD:
				$this->executeClusterUnits(
					$results,
					User::kSEQ_NAME,
					$options );
				break;
		
			case kAPI_RESULT_ENUM_DATA_FORMAT:
				$this->executeFormattedUnits(
					$results,
					User::kSEQ_NAME,
					$options );
				break;
		
		} // Parsed result type.
		
		//
		// Check result.
		//
		if( count( $results ) )
		{
			//
			// Handle user invitation.
			//
			if( $operation == kAPI_OP_USER_INVITE )
			{
				//
				// Parse by result type.
				//
				switch( $this->offsetGet( kAPI_PARAM_DATA ) )
				{
					case kAPI_RESULT_ENUM_DATA_RECORD:
						if( array_key_exists( kAPI_RESULTS_DICTIONARY, $this->mResponse ) )
							$this->extractInvitation(
								$results,
								$this->mResponse[ kAPI_RESULTS_DICTIONARY ],
								$param );
						break;
		
					case kAPI_RESULT_ENUM_DATA_FORMAT:
						$this->extractInvitation(
							$results,
							$tmp,
							$param );
						break;
		
				} // Parsed result type.
			
			} // User invitation.
			
			//
			// Handle user record.
			//
			else
			{
				//
				// Replace native identifier with identifier.
				//
				$tmp = $results;
				$results = Array();
				foreach( $tmp as $rec )
					$results[ $rec[ kTAG_IDENTIFIER ]
								  [ kAPI_PARAM_RESPONSE_FRMT_DISP ] ] = $rec;
			
			} // User record.
			
			//
			// Encrypt result.
			//
			$results = JsonEncode( $results );
			$this->mResponse[ kAPI_RESPONSE_RESULTS ]
				= $encoder->encodeData( $results );
		
			//
			// Encrypt dictionary.
			//
			if( array_key_exists( kAPI_RESULTS_DICTIONARY, $this->mResponse ) )
			{
				$results = JsonEncode( $this->mResponse[ kAPI_RESULTS_DICTIONARY ] );
				$this->mResponse[ kAPI_RESULTS_DICTIONARY ]
					= $encoder->encodeData( $results );
			}

			//
			// Set encrypted state.
			//
			$this->mResponse[ kAPI_RESPONSE_STATUS ]
							[ kAPI_STATUS_CRYPTED ] = TRUE;
		
		} // Found something.
		
	} // executeGetUser.

	 
	/*===================================================================================
	 *	executeModUser																	*
	 *==================================================================================*/

	/**
	 * Modify user.
	 *
	 * The method will modify the user identified by the {@link kAPI_PARAM_ID} parameter
	 * with the data contained in the {@link kAPI_PARAM_OBJECT} parameter; <tt>NULL</tt>
	 * elements will be cleared.
	 *
	 * The method will traverse the provided data in {@link kAPI_PARAM_OBJECT} and update
	 * the existing data in the {@link kAPI_PARAM_ID} property of the current pbject and
	 * replace the object.
	 *
	 * Note that only root level attributes will be considered; this means that to update
	 * a sub-document, one needs to provide the full document.
	 *
	 * The provided data will be cleared of the following properties:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: User password.
	 *	<li><tt>{@link kTAG_ENTITY_IDENT}</tt>: User identifier.
	 *	<li><tt>{@link kTAG_IDENTIFIER}</tt>: Object identifier.
	 * </ul>
	 *
	 * @access protected
	 */
	protected function executeModUser()
	{
		//
		// Init local storage.
		//
		$object = $this->offsetGet( kAPI_PARAM_ID );
		$data = $this->offsetGet( kAPI_PARAM_OBJECT );
		$required = array( kTAG_ENTITY_PGP_KEY, kTAG_ENTITY_PGP_FINGERPRINT );
		$excluded = array( kTAG_CONN_PASS, kTAG_ENTITY_IDENT, kTAG_IDENTIFIER );
		
		//
		// Remove excluded offsets.
		//
		foreach( $excluded as $offset )
		{
			if( array_key_exists( $offset, $data ) )
				unset( $data[ $offset ] );
		}
		
		//
		// Remove required empty offsets.
		//
		foreach( $required as $offset )
		{
			if( array_key_exists( $offset, $data )
			 && ($data[ $offset ] === NULL) )
				unset( $data[ $offset ] );
		}
		
		//
		// Iterate data.
		//
		foreach( $data as $key => $value )
		{
			//
			// Delete offset.
			//
			if( $value === NULL )
				$object->offsetUnset( $key );
			
			//
			// Update offset.
			//
			else
				$object->offsetSet( $key, $value );
		}
		
		//
		// Update object.
		//
		$object->commit();
		
		// MILKO: Remove in production.
		//
		// Normalise request.
		//
		if( array_key_exists(
			kAPI_REQUEST_USER,
			$this->mResponse[ kAPI_RESPONSE_REQUEST ] ) )
			$this->mResponse[ kAPI_RESPONSE_REQUEST ]
							[ kAPI_REQUEST_USER ]
				= $this->mResponse[ kAPI_RESPONSE_REQUEST ]
								  [ kAPI_REQUEST_USER ]
								  [ kTAG_ENTITY_PGP_FINGERPRINT ];
		if( array_key_exists(
			kAPI_PARAM_ID,
			$this->mResponse[ kAPI_RESPONSE_REQUEST ] ) )
			$this->mResponse[ kAPI_RESPONSE_REQUEST ]
							[ kAPI_PARAM_ID ]
				= $this->mResponse[ kAPI_RESPONSE_REQUEST ]
								  [ kAPI_PARAM_ID ]
								  [ kTAG_ENTITY_PGP_FINGERPRINT ];
		// MILKO: end.
		
	} // executeModUser.

	 
	/*===================================================================================
	 *	executeGetManagedUsers															*
	 *==================================================================================*/

	/**
	 * Get managed usersuser.
	 *
	 * The method will find all users managed by the user whose fingerprint was provided
	 * in the {@link kAPI_PARAM_ID} parameter and return the list of matching users,
	 * excluding private data depending on whether the service requestor,
	 * {@link kAPI_REQUEST_USER}, was provided and was among the manager's referrer's
	 * inheritance chain.
	 *
	 * <em>Note that if the provided identifier is not resolved, no error will be
	 * raised</em>.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function executeGetManagedUsers()
	{
		//
		// Assert user.
		//
		$manager
			= User::UserByIdentifier(
				$this->mWrapper,					// Wrapper.
				$this->offsetGet( kAPI_PARAM_ID ),	// Fingerprint.
				kPORTAL_DOMAIN,						// Portal domain.
				TRUE );								// Assert.
		{
			//
			// Init local storage.
			//
			$encoder = new Encoder();
			$options = kFLAG_FORMAT_OPT_DYNAMIC | kFLAG_FORMAT_OPT_VALUES;
			if( ! $this->isManagedUser( $this->offsetGet( kAPI_PARAM_ID ) ) )
				$options |= kFLAG_FORMAT_OPT_PRIVATE;
			
			//
			// Set filter.
			//
			$this->mFilter = array
			(
				kTAG_ENTITY_AFFILIATION => array
				(
					'$elemMatch' => array
					(
						kTAG_TYPE => kTYPE_LIST_REFERRER,
						kTAG_USER_REF => $manager->offsetGet( kTAG_NID )
					)
				)
			);
		
			//
			// Parse by result type.
			//
			$results = Array();
			switch( $this->offsetGet( kAPI_PARAM_DATA ) )
			{
				case kAPI_RESULT_ENUM_DATA_RECORD:
					$this->executeClusterUnits(
						$results,
						User::kSEQ_NAME,
						$options );
					break;
		
				case kAPI_RESULT_ENUM_DATA_FORMAT:
					$this->executeFormattedUnits(
						$results,
						User::kSEQ_NAME,
						$options );
					break;
		
			} // Parsed result type.
		
			//
			// Check result.
			//
			if( count( $results ) )
			{
				//
				// Replace native identifier with identifier.
				//
				$tmp = $results;
				$results = Array();
				foreach( $tmp as $rec )
					$results[ $rec[ kTAG_IDENTIFIER ]
								  [ kAPI_PARAM_RESPONSE_FRMT_DISP ] ] = $rec;
				
				//
				// Encrypt result.
				//
				$results = JsonEncode( $results );
				$this->mResponse[ kAPI_RESPONSE_RESULTS ]
					= $encoder->encodeData( $results );
		
				//
				// Encrypt dictionary.
				//
				if( array_key_exists( kAPI_RESULTS_DICTIONARY, $this->mResponse ) )
				{
					$results = JsonEncode( $this->mResponse[ kAPI_RESULTS_DICTIONARY ] );
					$this->mResponse[ kAPI_RESULTS_DICTIONARY ]
						= $encoder->encodeData( $results );
				}

				//
				// Set encrypted state.
				//
				$this->mResponse[ kAPI_RESPONSE_STATUS ]
								[ kAPI_STATUS_CRYPTED ] = TRUE;
		
			} // Found something.
		
		} // Manager exists.
		
	} // executeGetManagedUsers.

	 
	/*===================================================================================
	 *	executeCheckUserCode															*
	 *==================================================================================*/

	/**
	 * Modify user.
	 *
	 * The method will return the record count of all users matching the user code provided
	 * in the {@link kAPI_PARAM_ID} updating the {@link kAPI_PAGING_AFFECTED} property of
	 * the paging section.
	 *
	 * @access protected
	 */
	protected function executeCheckUserCode()
	{
		//
		// Init local storage.
		//
		$criteria = array( kTAG_CONN_CODE => $this->offsetGet( kAPI_PARAM_ID ) );
		$collection = $this->mWrapper->resolveCollection( User::kSEQ_NAME );
		
		//
		// Check code.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ][ kAPI_PAGING_AFFECTED ]
			= $collection->matchAll( $criteria, kQUERY_COUNT );
		
	} // executeCheckUserCode.

	 
	/*===================================================================================
	 *	executeSubmitTemplate															*
	 *==================================================================================*/

	/**
	 * Submit template.
	 *
	 * The method will instantiate the template upload session, launch the session batch and
	 * return the session identifier.
	 *
	 * @access protected
	 */
	protected function executeSubmitTemplate()
	{
		//
		// Init local storage.
		//
		$encoder = new Encoder();
		$user = $this->offsetGet( kAPI_REQUEST_USER );
		$user_id = $user->offsetGet( kTAG_NID );
		$user_fingerprint = $user->offsetGet( kTAG_ENTITY_PGP_FINGERPRINT );
		
		//
		// Check if there is a running session.
		//
		if( ! file_exists(
				SessionBatch::LockFilePath(
					$user->offsetGet( kTAG_NID ) ) ) )
		{
			//
			// Instantiate session.
			//
			$session = new Session( $this->mWrapper );
			$session->offsetSet( kTAG_SESSION_TYPE, kTYPE_SESSION_UPLOAD );
			$session->offsetSet( kTAG_USER, $user_id );
			$session->offsetSet( kTAG_ENTITY_PGP_FINGERPRINT, $user_fingerprint );
			$session_id = $session->commit();
		
			//
			// Create log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					kPATH_BATCHES_ROOT."/log/$session_id.log",
					"Service start: ".date( "r" )."\n" );
		
			//
			// Copy session in persistent user object.
			//
			User::ResolveCollection(
				User::ResolveDatabase( $this->mWrapper, TRUE ), TRUE )
					->replaceOffsets(
						$user_id,
						array( kTAG_SESSION => $session->offsetGet( kTAG_NID ) ) );
		
			//
			// Launch batch.
			//
			$php = kPHP_BINARY;
			$script = kPATH_BATCHES_ROOT.'/Batch_LoadTemplate.php';
			$path = $this->offsetGet( kAPI_PARAM_FILE_PATH );
		
			//
			// Handle debug log.
			//
			if( kDEBUG_FLAG )
				$log = "'".kPATH_BATCHES_ROOT."/log/$session_id.batch'";
			else
				$log = '/dev/null';
		
			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					kPATH_BATCHES_ROOT."/log/$session_id.log",
					"Lanching batch: ".date( "r" )."\n" );
		
			//
			// Launch batch.
			//
			$process_id = exec( "$php -f $script '$session_id' '$path' > '$log' &" );
		
			//
			// Build result.
			//
			$result = array( kAPI_SESSION_ID => $session_id,
							 kAPI_PROCESS_ID => $process_id );
		
		} // No running sessions.
		
		//
		// Handle running session.
		//
		else
			$result = array( kAPI_SESSION_ID => (string) $user->offsetGet( kTAG_SESSION ) );

		//
		// Encrypt result.
		//
		$result = JsonEncode( $result );
		$this->mResponse[ kAPI_RESPONSE_RESULTS ]
			= $encoder->encodeData( $result );

		//
		// Set encrypted state.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_CRYPTED ] = TRUE;
		
	} // executeSubmitTemplate.

	 
	/*===================================================================================
	 *	executeUpdateTemplate															*
	 *==================================================================================*/

	/**
	 * Update template.
	 *
	 * The method will instantiate the template update session, launch the session batch and
	 * return the session identifier.
	 *
	 * @access protected
	 */
	protected function executeUpdateTemplate()
	{
		//
		// Init local storage.
		//
		$encoder = new Encoder();
		$user = $this->offsetGet( kAPI_REQUEST_USER );
		$user_id = $user->offsetGet( kTAG_NID );
		$user_fingerprint = $user->offsetGet( kTAG_ENTITY_PGP_FINGERPRINT );
		$upload_session = $this->offsetGet( kAPI_PARAM_ID );
		
		//
		// Check if there is a running session.
		//
		if( ! file_exists(
				SessionBatch::LockFilePath(
					$user->offsetGet( kTAG_NID ) ) ) )
		{
			//
			// Instantiate session.
			//
			$session = new Session( $this->mWrapper );
			$session->offsetSet( kTAG_SESSION_TYPE, kTYPE_SESSION_UPDATE );
			$session->offsetSet( kTAG_USER, $user_id );
			$session->offsetSet( kTAG_ENTITY_PGP_FINGERPRINT, $user_fingerprint );
			$session_id = $session->commit();
		
			//
			// Create log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					kPATH_BATCHES_ROOT."/log/$session_id.log",
					"Service start: ".date( "r" )."\n" );
		
			//
			// Copy session in persistent user object.
			//
			Session::ResolveCollection(
				Session::ResolveDatabase( $this->mWrapper, TRUE ), TRUE )
					->replaceOffsets(
						$upload_session->offsetGet( kTAG_NID ),
						array( kTAG_SESSION => $session->offsetGet( kTAG_NID ) ) );
		
			//
			// Copy session in persistent user object.
			//
			User::ResolveCollection(
				User::ResolveDatabase( $this->mWrapper, TRUE ), TRUE )
					->replaceOffsets(
						$user_id,
						array( kTAG_SESSION => $session->offsetGet( kTAG_NID ) ) );
		
			//
			// Launch batch.
			//
			$php = kPHP_BINARY;
			$script = kPATH_BATCHES_ROOT.'/Batch_CommitTemplate.php';
		
			//
			// Handle debug log.
			//
			if( kDEBUG_FLAG )
				$log = "'".kPATH_BATCHES_ROOT."/log/$session_id.batch'";
			else
				$log = '/dev/null';
		
			//
			// Write to log file.
			//
			if( kDEBUG_FLAG )
				file_put_contents(
					kPATH_BATCHES_ROOT."/log/$session_id.log",
					"Lanching batch: ".date( "r" )."\n" );
		
			//
			// Launch batch.
			//
			$process_id = exec( "$php -f $script '$session_id' > '$log' &" );
		
			//
			// Build result.
			//
			$result = array( kAPI_SESSION_ID => $session_id,
							 kAPI_PROCESS_ID => $process_id );
		
		} // No running sessions.
		
		//
		// Handle running session.
		//
		else
			$result = array( kAPI_SESSION_ID => (string) $user->offsetGet( kTAG_SESSION ) );

		//
		// Encrypt result.
		//
		$result = JsonEncode( $result );
		$this->mResponse[ kAPI_RESPONSE_RESULTS ]
			= $encoder->encodeData( $result );

		//
		// Set encrypted state.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_CRYPTED ] = TRUE;
		
	} // executeUpdateTemplate.

	 
	/*===================================================================================
	 *	executeGetUserSession															*
	 *==================================================================================*/

	/**
	 * Get user session.
	 *
	 * The method will return the session identifier and its running status.
	 *
	 * @access protected
	 */
	protected function executeGetUserSession()
	{
		//
		// Init local storage.
		//
		$result = NULL;
		$encoder = new Encoder();
		$user = $this->offsetGet( kAPI_REQUEST_USER );
		
		//
		// Handle session.
		//
		if( $user->offsetExists( kTAG_SESSION ) )
		{
			//
			// Get session.
			//
			$session = new Session( $this->mWrapper, $user->offsetGet( kTAG_SESSION ) );
			
			//
			// Set session.
			//
			$result = array( kAPI_SESSION_ID => (string) $session->offsetGet( kTAG_NID ),
							 kAPI_SESSION_TYPE => $session->offsetGet( kTAG_SESSION_TYPE ) );
			
			//
			// Check if running.
			//
			$result[ kAPI_SESSION_RUNNING ]
				= file_exists(
						SessionBatch::LockFilePath(
							$user->offsetGet( kTAG_NID ) ) );
			
			//
			// Set result.
			//
			$this->mResponse[ kAPI_RESPONSE_RESULTS ] = $result;
		
		} // User has session.
		
		//
		// Encrypt result.
		//
		$data = JsonEncode( $result );
		$this->mResponse[ kAPI_RESPONSE_RESULTS ]
			= $encoder->encodeData( $data );

		//
		// Set encrypted state.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_CRYPTED ] = TRUE;
		
	} // executeGetUserSession.

	 
	/*===================================================================================
	 *	executeSessionProgress															*
	 *==================================================================================*/

	/**
	 * Get session progress.
	 *
	 * The method will return the serialised set of root level transactions.
	 *
	 * @access protected
	 */
	protected function executeSessionProgress()
	{
		//
		// Init local storage.
		//
		$result = Array();
		$encoder = new Encoder();
		$session = $this->offsetGet( kAPI_PARAM_ID );
		
		//
		// Serialise session.
		//
		$this->loadSessionProgress( $result );
		if( count( $result ) )
			$this->loadTransactionProgress( $result, $session );
		
		//
		// Encrypt result.
		//
		$data = JsonEncode( $result );
		$this->mResponse[ kAPI_RESPONSE_RESULTS ]
			= $encoder->encodeData( $data );

		//
		// Set encrypted state.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_CRYPTED ] = TRUE;
		
	} // executeSessionProgress.

	 
	/*===================================================================================
	 *	executePutData																	*
	 *==================================================================================*/

	/**
	 * Put data.
	 *
	 * The method will return the stored data identifier.
	 *
	 * @access protected
	 */
	protected function executePutData()
	{
		//
		// Init local storage.
		//
		$result = Array();
		$encoder = new Encoder();
		$object = $this->offsetGet( kAPI_PARAM_OBJECT );
		
		//
		// Save object.
		//
		$id
			= User::ResolveDatabase( $this->mWrapper, TRUE )
				->collection( kSTANDARDS_PORTAL_COLLECTION, TRUE )
					->save( $object );
		
		//
		// Encrypt result.
		//
		$data = JsonEncode( $id );
		$this->mResponse[ kAPI_RESPONSE_RESULTS ]
			= $encoder->encodeData( $data );

		//
		// Set encrypted state.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_CRYPTED ] = TRUE;
		
	} // executePutData.

	 
	/*===================================================================================
	 *	executeGetData																	*
	 *==================================================================================*/

	/**
	 * Get data.
	 *
	 * The method will return the data matching the provided identifier.
	 *
	 * @access protected
	 */
	protected function executeGetData()
	{
		//
		// Init local storage.
		//
		$encoder = new Encoder();
		
		//
		// Save object.
		//
		$data
			= User::ResolveDatabase( $this->mWrapper, TRUE )
				->collection( kSTANDARDS_PORTAL_COLLECTION, TRUE )
					->matchOne( array( kTAG_NID => $this->offsetGet( kAPI_PARAM_ID ) ),
								kQUERY_ARRAY );
		
		//
		// Encrypt result.
		//
		$data = JsonEncode( $data );
		$this->mResponse[ kAPI_RESPONSE_RESULTS ]
			= $encoder->encodeData( $data );

		//
		// Set encrypted state.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_CRYPTED ] = TRUE;
		
	} // executeGetData.

	 
	/*===================================================================================
	 *	executeDelData																	*
	 *==================================================================================*/

	/**
	 * Delete data.
	 *
	 * The method will return the data identifier if matched or <tt>NULL</tt>.
	 *
	 * @access protected
	 */
	protected function executeDelData()
	{
		//
		// Init local storage.
		//
		$encoder = new Encoder();
		
		//
		// Delete object.
		//
		$ok
			= User::ResolveDatabase( $this->mWrapper, TRUE )
				->collection( kSTANDARDS_PORTAL_COLLECTION, TRUE )
					->delete( $this->offsetGet( kAPI_PARAM_ID ) );
		
		//
		// Encrypt result.
		//
		$data = JsonEncode( $ok );
		$this->mResponse[ kAPI_RESPONSE_RESULTS ]
			= $encoder->encodeData( $data );

		//
		// Set encrypted state.
		//
		$this->mResponse[ kAPI_RESPONSE_STATUS ]
						[ kAPI_STATUS_CRYPTED ] = TRUE;
		
	} // executeDelData.

		

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
				array( kTAG_LABEL => TRUE,
					   kTAG_UNIT_OFFSETS => TRUE ) ) );
		
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
				array( kTAG_LABEL => TRUE,
					   kTAG_UNIT_OFFSETS => TRUE ) ) );
		
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
			// Filter untracked tags.
			//
			$excluded = ( $this->offsetExists( kAPI_PARAM_EXCLUDED_TAGS ) )
					  ? $this->offsetGet( kAPI_PARAM_EXCLUDED_TAGS )
					  : Array();
			$criteria[ kTAG_ID_HASH ]
				= array( '$nin' => array_values(
					array_unique(
						array_merge(
							$excluded, UnitObject::UnmanagedOffsets() ) ) ) );
			
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
										  kTYPE_REF_USER, kTYPE_REF_SELF,
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
					
					case kAPI_PARAM_COLLECTION_USER:
						$criteria[ (string) kTAG_USER_COUNT ] = array( '$gt' => 0 );
						break;
				}
			}
		}
		
		//
		// Handle summary tags.
		//
		switch( $this->offsetGet( kAPI_REQUEST_OPERATION ) )
		{
			case kAPI_OP_MATCH_TAG_SUMMARY_LABELS:
			case kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL:
				$criteria[ kTAG_DATA_KIND ] = kTYPE_SUMMARY;
				break;
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
		// Format by operation.
		//
		switch( $this->offsetGet( kAPI_REQUEST_OPERATION ) )
		{
			case kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL:
				$this->executeSerialiseSummaryTags( $theIterator );
				break;
			
			default:
				$this->executeSerialiseResults( $theIterator, TRUE );
				break;
		}
		
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
		$pipeline = Array();
		$shape = $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET );
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		$groups = $this->offsetGet( kAPI_PARAM_GROUP_DATA );
		$list = 0;
		
		//
		// Set match.
		//
		if( count( $this->mFilter ) )
			$pipeline[] = array( '$match' => $this->mFilter );
		
		//
		// Set project 1.
		//
		$tmp = Array();
		foreach( $groups as $key => $value )
			$tmp[ (string) $key ] = '$'.$value[ kAPI_PARAM_OFFSETS ];
		if( $shape !== NULL )
			$tmp[ $shape ]
				= array( '$cond' => array(
						 'if' => '$'.$shape.'.type',
						 'then' => 1,
						 'else' => 0 ) );
		$pipeline[] = array( '$project' => $tmp );
		
		//
		// Set unwind(s).
		//
		foreach( $groups as $key => $value )
		{
			if( $tmp = $value[ kAPI_PARAM_GROUP_LIST ] )
			{
				$list += $tmp;
				while( $tmp-- )
					$pipeline[] = array( '$unwind' => '$'.$key );
			}
		}
		
		//
		// Handle array summary elements.
		//
		if( $list )
		{
			//
			// Set group1.
			//
			$tmp = array( '_id' => Array() );
			$tmp[ '_id' ][ 'id' ] = '$_id';
			foreach( array_keys( $groups ) as $element )
				$tmp[ '_id' ][ $element ] = '$'.$element;
			if( $shape !== NULL )
				$tmp[ $shape ] = array( '$sum' => '$'.$shape );
			$pipeline[] = array( '$group' => $tmp );
		
			//
			// Set project 2.
			//
			if( $shape !== NULL )
			{
				$tmp = Array();
				$tmp[ '_id' ] = '$_id';
				$tmp[ $shape ] = array(
					'$cond' => array(
						'if' => '$'.$shape,
						'then' => 1,
						'else' => 0 ) );
				$pipeline[] = array( '$project' => $tmp );
			}
		
			//
			// Set group2.
			//
			$tmp = array( '_id' => Array() );
			foreach( array_keys( $groups ) as $element )
				$tmp[ '_id' ][ $element ] = '$_id.'.$element;
			$tmp[ kAPI_PARAM_RESPONSE_COUNT ] = array( '$sum' => 1 );
			if( $shape !== NULL )
				$tmp[ kAPI_PARAM_RESPONSE_POINTS ] = array( '$sum' => '$'.$shape );
			$pipeline[] = array( '$group' => $tmp );
		
		} // Array summary elements.
		
		//
		// Handle scalar summary elements.
		//
		else
		{
			//
			// Set group.
			//
			$tmp = array( '_id' => Array() );
			foreach( array_keys( $groups ) as $element )
				$tmp[ '_id' ][ $element ] = '$'.$element;
			$tmp[ kAPI_PARAM_RESPONSE_COUNT ] = array( '$sum' => 1 );
			if( $shape !== NULL )
				$tmp[ kAPI_PARAM_RESPONSE_POINTS ] = array( '$sum' => '$'.$shape );
			$pipeline[] = array( '$group' => $tmp );
		
		} // Scalar summary elements.
		
		//
		// Set sort.
		//
		$tmp = Array();
		foreach( array_keys( $groups ) as $element )
			$tmp[ "_id.$element" ] = 1;
		$pipeline[] = array( '$sort' => $tmp );

		//
		// Aggregate.
		//
//
// MILKO - There must be a bug in the PHP driver:
//		   this operation fails saying that element 0 of the pipeline
//		   is not an object, this fails also when setting that element to an object.
//
		$rs_units
			= PersistentObject::ResolveCollectionByName(
				$this->mWrapper, $theCollection )
					->aggregate(
						$pipeline,
						array( 'allowDiskUse' => true ) );
						
		//
		// Iterate results.
		//
//
// MILKO - Need to do this if aggregate doesn't use cursor.
//
$rs_units = & $rs_units[ 'result' ];
		
		//
		// Collect enumerated summary elements.
		//
		$enums = Array();
		foreach( $groups as $tag => $group )
		{
			switch( $group[ kAPI_PARAM_DATA_TYPE ] )
			{
				case kTYPE_SET:
				case kTYPE_ENUM:
					$enums[] = $tag;
					break;
			}
			
			//
			// Collect terms.
			//
			$tmp = Array();
			if( count( $enums ) )
			{
				foreach( $rs_units as $record )
				{
					foreach( $record[ kTAG_NID ] as $key => $value )
					{
						if( in_array( $key, $enums ) )
						{
							if( ! in_array( $value, $tmp ) )
								$tmp[] = $value;
						}
					}
				}
			}
		}
		
		//
		// Resolve enumerated summary elements.
		//
		if( count( $tmp ) )
		{
			//
			// Collect terms.
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
		}
		
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
			foreach( $groups as $tag => $group )
			{
				//
				// Get value.
				//
				$value = ( array_key_exists( $tag, $record[ '_id' ] ) )
					   ? $record[ '_id' ][ $tag ]
					   : '';
				
				//
				// Create element.
				//
				if( ! array_key_exists( $value, $ref ) )
				{
					//
					// Allocate element.
					//
					$ref[ $value ] = Array();
					
					//
					// Set offset.
					//
					$ref[ $value ][ kAPI_PARAM_OFFSETS ] = $group[ kAPI_PARAM_OFFSETS ];
					
					//
					// Set pattern.
					//
					$ref[ $value ][ kAPI_PARAM_PATTERN ] = $value;
					
					//
					// Handle enumeration.
					//
					if( in_array( $tag, $enums ) )
					{
						//
						// Reference term.
						//
						$term_ref = $terms[ $value ];
						
						//
						// Load label.
						//
						if( array_key_exists( kTAG_LABEL, $term_ref ) )
							$ref[ $value ][ kAPI_PARAM_RESPONSE_FRMT_NAME ]
								= $term_ref[ kTAG_LABEL ];
					
						//
						// Load definition.
						//
						if( array_key_exists( kTAG_DEFINITION, $term_ref ) )
							$ref[ $value ][ kAPI_PARAM_RESPONSE_FRMT_INFO ]
								= $term_ref[ kTAG_DEFINITION ];
						
					} // Enumerated value.
					
					//
					// Handle non-enumerated value.
					//
					else
						$ref[ $value ][ kAPI_PARAM_RESPONSE_FRMT_NAME ] = $value;
					
				} // New element.
				
				//
				// Handle leaf node.
				//
				if( $tag == kTAG_DOMAIN )
				{
					//
					// Set units count.
					//
					$ref[ $value ][ kAPI_PARAM_RESPONSE_COUNT ]
						= $record[ kAPI_PARAM_RESPONSE_COUNT ];
					
					//
					// Set units coordinates.
					//
					if( array_key_exists( kAPI_PARAM_RESPONSE_POINTS, $record ) )
						$ref[ $value ][ kAPI_PARAM_RESPONSE_POINTS ]
							= $record[ kAPI_PARAM_RESPONSE_POINTS ];
					
					//
					// Set domain statistics.
					//
					$ref[ $value ][ kAPI_PARAM_RESPONSE_FRMT_STATS ]
						= count( static::GetStatisticsList( $value ) );
				}
				
				//
				// Handle container node.
				//
				else
				{
					//
					// Allocate children container.
					//
					if( ! array_key_exists( kAPI_PARAM_RESPONSE_CHILDREN, $ref[ $value ] ) )
						$ref[ $value ][ kAPI_PARAM_RESPONSE_CHILDREN ] = Array();
					
					//
					// Point to container.
					//
					$ref = & $ref[ $value ][ kAPI_PARAM_RESPONSE_CHILDREN ];
				
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
		// Get paging.
		//
		$skip  = ( ($tmp = $this->offsetGet( kAPI_PAGING_SKIP )) > 0 )
			   ? (int) $tmp
			   : NULL;
		$limit = ( ($tmp = $this->offsetGet( kAPI_PAGING_LIMIT )) !== NULL )
			   ? (int) $tmp
			   : NULL;
		
		//
		// Determine full-text search.
		//
		$full_text = array_key_exists( '$text', $this->mFilter );
		if( (! $full_text)
		 && array_key_exists( '$and', $this->mFilter ) )
		{
			foreach( $this->mFilter[ '$and' ] as $tmp )
			{
				if( $full_text = array_key_exists( '$text', $tmp ) )
				{
					$full_text = TRUE;
					break;													// =>
				}
			}
		}
		
		//
		// Handle full-text search.
		//
		if( $full_text )
		{
			//
			// Perform query to get results count.
			//
			$affected
				= PersistentObject::ResolveCollectionByName(
					$this->mWrapper, $theCollection )
						->matchAll(
							$this->mFilter, kQUERY_COUNT );
			
			//
			// Set pipeline.
			//
			$pipeline = Array();
			$pipeline[] = array( '$match' => $this->mFilter );
			$pipeline[] = array( '$sort' => array(
							'score' => array(
								'$meta' => 'textScore' ) ) );
			if( $skip !== NULL )
				$pipeline[] = array( '$skip' => $skip );
			if( $limit !== NULL )
				$pipeline[] = array( '$limit' => $limit );
			
			//
			// Project pipeline.
			//
			$offsets = array( 'score' => array( '$meta' => "textScore" ) );
			foreach( UnitObject::ListOffsets( $this->offsetGet( kAPI_PARAM_DOMAIN ) )
						as $offset )
			{
				//
				// Handle numeric offset.
				//
				if( substr( $offset, 0, 1 ) == kTOKEN_TAG_PREFIX )
					$offsets[ $offset ] = TRUE;
				
				//
				// Resolve offset.
				//
				else
					$offsets[ $this->mWrapper->getSerial( $offset ) ] = TRUE;
			}
			$pipeline[] = array( '$project' => $offsets );
			
			//
			// Execute request.
			//
			$results
				= new \ArrayObject(
					PersistentObject::ResolveCollectionByName(
						$this->mWrapper, $theCollection )
							->aggregate( $pipeline,
										 array( 'allowDiskUse' => TRUE ) ) );
			
			//
			// Instantiate iterator.
			//
			$iterator
				= new ArrayCursorIterator(
					new \ArrayIterator( $results[ 'result' ] ),
					PersistentObject::ResolveCollectionByName(
						$this->mWrapper, $theCollection ),
					$this->mFilter );
			
		} // Full text search.
		
		//
		// Handle property- only search.
		//
		else
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
		
		} // No full text search.
	
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
		if( $full_text )
		{
			//
			// Adjust affected count.
			//
			$this->mResponse[ kAPI_RESPONSE_PAGING ]
							[ kAPI_PAGING_AFFECTED ] = $affected;
			
			//
			// Update limits.
			//
			$this->mResponse[ kAPI_RESPONSE_PAGING ]
							[ kAPI_PAGING_SKIP ] = (int) $skip;
			$this->mResponse[ kAPI_RESPONSE_PAGING ]
							[ kAPI_PAGING_LIMIT ] = (int) $limit;
		
		} // Full text search.
	
		//
		// Set dictionary.
		//
		$this->mResponse[ kAPI_RESULTS_DICTIONARY ]
						[ kAPI_DICTIONARY_LIST_COLS ]
			= $formatter->dictionary()[ kAPI_DICTIONARY_LIST_COLS ];
		if( array_key_exists( kAPI_PARAM_RESPONSE_FRMT_SCORE, $formatter->dictionary() ) )
			$this->mResponse[ kAPI_RESULTS_DICTIONARY ]
							[ kAPI_PARAM_RESPONSE_FRMT_SCORE ]
				= $formatter->dictionary()[ kAPI_PARAM_RESPONSE_FRMT_SCORE ];
	
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
	 * The last parameter indicates the format options:
	 *
	 * <ul>
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_DYNAMIC}</tt>: Exclude dynamic tags.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_PRIVATE}</tt>: Exclude private tags.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_NATIVES}</tt>: Include tag native identifiers in
	 *		formatted results with offset {@link kAPI_PARAM_TAG}.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_VALUES}</tt>: Include values in formatted results
	 *		with offset {@link kAPI_PARAM_RESPONSE_FRMT_VALUE}.
	 * </ul>
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param string				$theCollection		collection name.
	 * @param bitfield				$theOptions			Format options.
	 *
	 * @access protected
	 */
	protected function executeFormattedUnits( &$theContainer,
											   $theCollection,
											   $theOptions = kFLAG_DEFAULT )
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
					$this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ),	// Shape.
					$theOptions );									// Options.
		
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
	 * The last parameter indicates the format options:
	 *
	 * <ul>
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_DYNAMIC}</tt>: Exclude dynamic tags.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_PRIVATE}</tt>: Exclude private tags.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_NATIVES}</tt>: Include tag native identifiers in
	 *		formatted results with offset {@link kAPI_PARAM_TAG}.
	 *	<li><tt>{@link kFLAG_FORMAT_OPT_VALUES}</tt>: Include values in formatted results
	 *		with offset {@link kAPI_PARAM_RESPONSE_FRMT_VALUE}.
	 * </ul>
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param string				$theCollection		Collection name.
	 * @param bitfield				$theOptions			Format options.
	 *
	 * @access protected
	 */
	protected function executeClusterUnits( &$theContainer,
											 $theCollection,
											 $theOptions = kFLAG_DEFAULT )
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
					$this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ),	// Shape.
					$theOptions );									// Options.
		
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


	/*===================================================================================
	 *	executeUnitStats																*
	 *==================================================================================*/

	/**
	 * Perform statistics.
	 *
	 * This method expects the filter data member set with the requested query.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param string				$theCollection		collection name.
	 *
	 * @access protected
	 */
	protected function executeUnitStats( &$theContainer, $theCollection )
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
		// Perform statistics.
		//
		switch( $tmp = $this->offsetGet( kAPI_PARAM_STAT ) )
		{
			case 'abdh-species-01':
				$this->executeUnitStat1( $theContainer, $iterator );
				break;
			
			case 'abdh-species-02':
				$this->executeUnitStat2( $theContainer, $iterator );
				break;
			
			case 'abdh-species-03':
				$this->executeUnitStat3( $theContainer, $iterator );
				break;
			
			case 'abdh-species-04':
				$this->executeUnitStat4( $theContainer, $iterator );
				break;
			
			case 'abdh-species-05':
				$this->executeUnitStat5( $theContainer, $iterator );
				break;
			
			case 'abdh-species-06':
				$this->executeUnitStat6( $theContainer, $iterator );
				break;
			
			case 'abdh-species-07':
				$this->executeUnitStat7( $theContainer, $iterator );
				break;
			
			default:
				throw new \Exception(
					"Unknown statistics type [$tmp]. " );						// !@! ==>
		}
		
	} // executeUnitStats.


	/*===================================================================================
	 *	executeUnitStat1																*
	 *==================================================================================*/

	/**
	 * Perform statistic 1.
	 *
	 * This method will perform the statistics according to the data provided in the
	 * iterator parameter.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param ObjectIterator		$theIterator		Iterator.
	 *
	 * @access protected
	 */
	protected function executeUnitStat1( &$theContainer, $theIterator )
	{
		//
		// Save tags.
		//
		$tag_cat = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
		$tag_epithet = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
		$tag_cname = $this->mWrapper->getSerial( 'abdh:NAME_ENG', TRUE );
		$tag_cultot = $this->mWrapper->getSerial( 'abdh:Q2.4b', TRUE );
		$cont_food = $this->mWrapper->getSerial( 'abdh:Q2.6', TRUE );
		$cont_inco = $this->mWrapper->getSerial( 'abdh:Q2.7', TRUE );
		
		//
		// Set title
		//
		$this->getStatistics( $theContainer,
							  $this->offsetGet( kAPI_REQUEST_LANGUAGE ),
							  $this->offsetGet( kAPI_PARAM_DOMAIN ),
							  $this->offsetGet( kAPI_PARAM_STAT ) );
		
		//
		// Set header.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ] = Array();
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Common name',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'No. of households',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '%',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Total area',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Contribution to food',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Contribution to income',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		
		//
		// Iterate.
		//
		$tot_cult = 0;
		$data = Array();
		foreach( $theIterator as $object )
		{
			//
			// Get species sub-structure.
			//
			if( ($species = $object->offsetGet( 'abdh:species' )) !== NULL )
			{
				//
				// Iterate species.
				//
				foreach( $species as $record )
				{
					//
					// Check required tags.
					//
					if( array_key_exists( $tag_epithet, $record )
					 && array_key_exists( $tag_cultot, $record )
					 && array_key_exists( $cont_food, $record )
					 && array_key_exists( $cont_inco, $record )
					 && array_key_exists( $tag_cat, $record )
					 && ($record[ $tag_cat ] == 'abdh:SPECIES_CAT:1') )
					{
						//
						// Create new entry.
						//
						if( ! array_key_exists( $record[ $tag_epithet ], $data ) )
						{
							//
							// Init record.
							//
							$data[ $record[ $tag_epithet ] ]
								= array(
									$record[ $tag_epithet ],
									NULL,
									1,
									0,
									0,
									0,
									0 );
					
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
					
							//
							// Set common name.
							//
							if( array_key_exists( $tag_cname, $record ) )
								$ref[ 1 ] = $record[ $tag_cname ];
				
						} // New species.
						
						//
						// Init existing species.
						//
						else
						{
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
					
							//
							// Increment households count.
							//
							$ref[ 2 ]++;
						
						} // Existing species.
						
						//
						// Set cultivated area.
						//
						$ref[ 4 ] += $record[ $tag_cultot ];
						$tot_cult += $record[ $tag_cultot ];
						
						//
						// Set contribution to food.
						//
						switch( $record[ $cont_food ] )
						{
							case 'abdh:Q2.6:1':
								$ref[ 5 ] += 1;
								break;
							case 'abdh:Q2.6:2':
								$ref[ 5 ] += 2;
								break;
							case 'abdh:Q2.6:3':
								$ref[ 5 ] += 3;
								break;
						}
						
						//
						// Set contribution to income.
						//
						switch( $record[ $cont_inco ] )
						{
							case 'abdh:Q2.7:1':
								$ref[ 6 ] += 1;
								break;
							case 'abdh:Q2.7:2':
								$ref[ 6 ] += 2;
								break;
							case 'abdh:Q2.7:3':
								$ref[ 6 ] += 3;
								break;
						}
					
					} // Has required tags,
				
				} // Iterating species.
			
			} // Has species.
		
		} // Iterating.
		
		//
		// Finalise results.
		//
		foreach( array_keys( $data ) as $key )
		{
			//
			// Set cultivated land percentage.
			//
			$data[ $key ][ 3 ] = ( $data[ $key ][ 4 ] * 100 ) / $tot_cult;
			
			//
			// Set contribution ratio.
			//
			$data[ $key ][ 5 ] /= $data[ $key ][ 2 ];
			$data[ $key ][ 6 ] /= $data[ $key ][ 2 ];
			
			//
			// Round figures.
			//
			$data[ $key ][ 3 ] = round( $data[ $key ][ 3 ], 2 );
			$data[ $key ][ 5 ] = round( $data[ $key ][ 5 ], 2 );
			$data[ $key ][ 6 ] = round( $data[ $key ][ 6 ], 2 );
		
		} // Iterating data.
		
		//
		// Set data.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = array_values( $data );
		
	} // executeUnitStat1.


	/*===================================================================================
	 *	executeUnitStat2																*
	 *==================================================================================*/

	/**
	 * Perform statistic 2.
	 *
	 * This method will perform the statistics according to the data provided in the
	 * iterator parameter.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param ObjectIterator		$theIterator		Iterator.
	 *
	 * @access protected
	 */
	protected function executeUnitStat2( &$theContainer, $theIterator )
	{
		//
		// Save tags.
		//
		$tag_cat = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
		$tag_epithet = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
		$tag_cname = $this->mWrapper->getSerial( 'abdh:NAME_ENG', TRUE );
		$tag_season = $this->mWrapper->getSerial( 'abdh:Q2.2a', TRUE );
		$tag_water = $this->mWrapper->getSerial( 'abdh:Q2a', TRUE );
		
		//
		// Set title
		//
		$this->getStatistics( $theContainer,
							  $this->offsetGet( kAPI_REQUEST_LANGUAGE ),
							  $this->offsetGet( kAPI_PARAM_DOMAIN ),
							  $this->offsetGet( kAPI_PARAM_STAT ) );
		
		//
		// Set header.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ] = Array();
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Common name',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'No. of households',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '%',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Rabi',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Kharif',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Rainfed',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Khadim',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Tube-well irrigation',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Canal irrigation',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		//
		// Iterate.
		//
		$count = 0;
		$data = Array();
		foreach( $theIterator as $object )
		{
			//
			// Init households counter.
			//
			$house_spec = 0;
			
			//
			// Get species sub-structure.
			//
			if( ($species = $object->offsetGet( 'abdh:species' )) !== NULL )
			{
				//
				// Iterate species.
				//
				foreach( $species as $record )
				{
					//
					// Check required offsets.
					//
					if( array_key_exists( $tag_epithet, $record )
					 && ( array_key_exists( $tag_season, $record )
					   || array_key_exists( $tag_water, $record ) )
					 && array_key_exists( $tag_cat, $record )
					 && ($record[ $tag_cat ] == 'abdh:SPECIES_CAT:1') )
					{
						//
						// Increment species count.
						//
						$house_spec++;
						
						//
						// Create new entry.
						//
						if( ! array_key_exists( $record[ $tag_epithet ], $data ) )
						{
							//
							// Init record.
							//
							$data[ $record[ $tag_epithet ] ]
								= array(
									$record[ $tag_epithet ],
									NULL,
									1,
									0,
									0,
									0,
									0,
									0,
									0,
									0 );
				
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Set common name.
							//
							if( array_key_exists( $tag_cname, $record ) )
								$ref[ 1 ] = $record[ $tag_cname ];
			
						} // New species.
					
						//
						// Init existing species.
						//
						else
						{
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Increment households count.
							//
							$ref[ 2 ]++;
					
						} // Existing species.
					
						//
						// Handle season.
						//
						if( array_key_exists( $tag_season, $record ) )
						{
							foreach( $record[ $tag_season ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q2.2a:1':
										$ref[ 4 ]++;
										break;
									case 'abdh:Q2.2a:2':
										$ref[ 5 ]++;
										break;
								}
							}
					
						} // Has season.
					
						//
						// Handle water.
						//
						if( array_key_exists( $tag_water, $record ) )
						{
							foreach( $record[ $tag_water ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q2a:1':
										$ref[ 6 ]++;
										break;
									case 'abdh:Q2a:2':
										$ref[ 7 ]++;
										break;
									case 'abdh:Q2a:3':
										$ref[ 8 ]++;
										break;
									case 'abdh:Q2a:4':
										$ref[ 9 ]++;
										break;
								}
							}
					
						} // Has water.
					
					} // Has species epithet.
				
				} // Iterating species.
			
			} // Has species.
			
			//
			// Increment households count.
			//
			if( $house_spec )
				$count++;
		
		} // Iterating.
		
		//
		// Calculate percentages.
		//
		foreach( array_keys( $data ) as $species )
		{
			if( $count )
			{
				$data[ $species ][ 3 ]
					= round( ( $data[ $species ][ 2 ] * 100 ) / $count, 2 );
			}
		}
		
		//
		// Set data.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = array_values( $data );
		
	} // executeUnitStat2.


	/*===================================================================================
	 *	executeUnitStat3																*
	 *==================================================================================*/

	/**
	 * Perform statistic 3.
	 *
	 * This method will perform the statistics according to the data provided in the
	 * iterator parameter.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param ObjectIterator		$theIterator		Iterator.
	 *
	 * @access protected
	 */
	protected function executeUnitStat3( &$theContainer, $theIterator )
	{
		//
		// Save tags.
		//
		$tag_cat = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
		$tag_epithet = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
		$tag_cname = $this->mWrapper->getSerial( 'abdh:NAME_ENG', TRUE );
		$tag_vcount = $this->mWrapper->getSerial( 'abdh:Q2.16', TRUE );
		$tag_desi = $this->mWrapper->getSerial( 'abdh:Q2.17', TRUE );
		$tag_impr = $this->mWrapper->getSerial( 'abdh:Q2.18', TRUE );
		$tag_want = $this->mWrapper->getSerial( 'abdh:Q2.19', TRUE );
		$tag_type = $this->mWrapper->getSerial( 'abdh:Q2.20', TRUE );
		
		//
		// Set title
		//
		$this->getStatistics( $theContainer,
							  $this->offsetGet( kAPI_REQUEST_LANGUAGE ),
							  $this->offsetGet( kAPI_PARAM_DOMAIN ),
							  $this->offsetGet( kAPI_PARAM_STAT ) );
		
		//
		// Set header.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ] = Array();
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Common name',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'No. of households',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '%',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Total number of varieties',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Number of desi',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Number of hybrid/improved',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '% yes',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Desi',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Improved',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Both',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		//
		// Iterate.
		//
		$count = 0;
		$data = Array();
		foreach( $theIterator as $object )
		{
			//
			// Init households counter.
			//
			$house_spec = 0;
			
			//
			// Get species sub-structure.
			//
			if( ($species = $object->offsetGet( 'abdh:species' )) !== NULL )
			{
				//
				// Iterate species.
				//
				foreach( $species as $record )
				{
					//
					// Check required offsets.
					//
					if( array_key_exists( $tag_epithet, $record )
					 && array_key_exists( $tag_vcount, $record )
					 && array_key_exists( $tag_want, $record )
					 && array_key_exists( $tag_cat, $record )
					 && ($record[ $tag_cat ] == 'abdh:SPECIES_CAT:1') )
					{
						//
						// Increment species count.
						//
						$house_spec++;
						
						//
						// Create new entry.
						//
						if( ! array_key_exists( $record[ $tag_epithet ], $data ) )
						{
							//
							// Init record.
							//
							$data[ $record[ $tag_epithet ] ]
								= array(
									$record[ $tag_epithet ],
									NULL,
									1,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0 );
				
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Set common name.
							//
							if( array_key_exists( $tag_cname, $record ) )
								$ref[ 1 ] = $record[ $tag_cname ];
			
						} // New species.
					
						//
						// Init existing species.
						//
						else
						{
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Increment households count.
							//
							$ref[ 2 ]++;
					
						} // Existing species.
						
						//
						// Handle varieties count.
						//
						if( array_key_exists( $tag_vcount, $record ) )
							$ref[ 4 ] += $record[ $tag_vcount ];
						
						//
						// Handle number of desi.
						//
						if( array_key_exists( $tag_desi, $record ) )
							$ref[ 5 ] += $record[ $tag_desi ];
						
						//
						// Handle number of improved.
						//
						if( array_key_exists( $tag_impr, $record ) )
							$ref[ 6 ] += $record[ $tag_impr ];
						
						//
						// Handle want others.
						//
						if( array_key_exists( $tag_want, $record ) )
						{
							if( $record[ $tag_want ] == 'abdh:Q2.19:1' )
								$ref[ 7 ]++;
						}
					
						//
						// Handle which.
						//
						if( array_key_exists( $tag_type, $record ) )
						{
							switch( $record[ $tag_type ] )
							{
								case 'abdh:Q2.20:1':
									$ref[ 8 ]++;
									break;
								case 'abdh:Q2.20:2':
									$ref[ 9 ]++;
									break;
								case 'abdh:Q2.20:3':
									$ref[ 10 ]++;
									break;
							}
					
						} // Has season.
					
					} // Has species epithet.
				
				} // Iterating species.
			
			} // Has species.
			
			//
			// Increment households count.
			//
			if( $house_spec )
				$count++;
		
		} // Iterating.
		
		//
		// Normalise results.
		//
		foreach( array_keys( $data ) as $species )
		{
			//
			// Set species percentage.
			//
			if( $count )
			{
				$data[ $species ][ 3 ]
					= round( ( $data[ $species ][ 2 ] * 100 ) / $count, 2 );
			}
			
			//
			// Set yes percentage.
			//
			if( $data[ $species ][ 2 ] > 0 )
				$data[ $species ][ 7 ]
					= round( ( $data[ $species ][ 7 ] * 100 ) / $data[ $species ][ 2 ], 2 );
		}
		
		//
		// Set data.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = array_values( $data );
		
	} // executeUnitStat3.


	/*===================================================================================
	 *	executeUnitStat4																*
	 *==================================================================================*/

	/**
	 * Perform statistic 4.
	 *
	 * This method will perform the statistics according to the data provided in the
	 * iterator parameter.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param ObjectIterator		$theIterator		Iterator.
	 *
	 * @access protected
	 */
	protected function executeUnitStat4( &$theContainer, $theIterator )
	{
		//
		// Save tags.
		//
		$tag_cat = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
		$tag_epithet = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
		$tag_cname = $this->mWrapper->getSerial( 'abdh:NAME_ENG', TRUE );
		$tag_source = $this->mWrapper->getSerial( 'abdh:Q2.10', TRUE );
		$tag_who = $this->mWrapper->getSerial( 'abdh:Q2.11a', TRUE );
		$tag_trans = $this->mWrapper->getSerial( 'abdh:Q6a', TRUE );
		
		//
		// Set title
		//
		$this->getStatistics( $theContainer,
							  $this->offsetGet( kAPI_REQUEST_LANGUAGE ),
							  $this->offsetGet( kAPI_PARAM_DOMAIN ),
							  $this->offsetGet( kAPI_PARAM_STAT ) );
		
		//
		// Set header.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ] = Array();
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Common name',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'No. of households',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '%',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Saved from previous harvest',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Obtained outside of farm',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Family',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Neighbour',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Friend',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Public seed traider',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Private seed traider',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Local market',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Government emergency programme',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'NGO',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Other',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '% Purchase',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '% Exchange of seed',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '% Barter for other goods',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '% Credit',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '% Gift',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '% Other',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		
		//
		// Iterate.
		//
		$count = 0;
		$data = Array();
		foreach( $theIterator as $object )
		{
			//
			// Init households counter.
			//
			$house_spec = 0;
			
			//
			// Get species sub-structure.
			//
			if( ($species = $object->offsetGet( 'abdh:species' )) !== NULL )
			{
				//
				// Iterate species.
				//
				foreach( $species as $record )
				{
					//
					// Check required offsets.
					//
					if( array_key_exists( $tag_epithet, $record )
					 && array_key_exists( $tag_source, $record )
					 && array_key_exists( $tag_cat, $record )
					 && ($record[ $tag_cat ] == 'abdh:SPECIES_CAT:1') )
					{
						//
						// Increment species count.
						//
						$house_spec++;
						
						//
						// Create new entry.
						//
						if( ! array_key_exists( $record[ $tag_epithet ], $data ) )
						{
							//
							// Init record.
							//
							$data[ $record[ $tag_epithet ] ]
								= array(
									$record[ $tag_epithet ],
									NULL,
									1,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0 );
				
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Set common name.
							//
							if( array_key_exists( $tag_cname, $record ) )
								$ref[ 1 ] = $record[ $tag_cname ];
			
						} // New species.
					
						//
						// Init existing species.
						//
						else
						{
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Increment households count.
							//
							$ref[ 2 ]++;
					
						} // Existing species.
						
						//
						// Handle source of seed.
						//
						if( array_key_exists( $tag_source, $record ) )
						{
							switch( $record[ $tag_source ] )
							{
								case 'abdh:Q2.10:1':
									$ref[ 4 ]++;
									break;
								case 'abdh:Q2.10:2':
									$ref[ 5 ]++;
									break;
							}
						}
						
						//
						// Handle provider of seed.
						//
						if( array_key_exists( $tag_who, $record ) )
						{
							foreach( $record[ $tag_who ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q2.11a:1':
										$ref[ 6 ]++;
										break;
									case 'abdh:Q2.11a:2':
										$ref[ 7 ]++;
										break;
									case 'abdh:Q2.11a:3':
										$ref[ 8 ]++;
										break;
									case 'abdh:Q2.11a:4':
										$ref[ 9 ]++;
										break;
									case 'abdh:Q2.11a:5':
										$ref[ 10 ]++;
										break;
									case 'abdh:Q2.11a:6':
										$ref[ 11 ]++;
										break;
									case 'abdh:Q2.11a:7':
										$ref[ 12 ]++;
										break;
									case 'abdh:Q2.11a:8':
										$ref[ 13 ]++;
										break;
									case 'abdh:Q2.11a:9':
										$ref[ 14 ]++;
										break;
								}
							}
						}
						
						//
						// Handle transaction type.
						//
						if( array_key_exists( $tag_trans, $record ) )
						{
							foreach( $record[ $tag_trans ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q6a:1':
										$ref[ 15 ]++;
										break;
									case 'abdh:Q6a:2':
										$ref[ 16 ]++;
										break;
									case 'abdh:Q6a:3':
										$ref[ 17 ]++;
										break;
									case 'abdh:Q6a:4':
										$ref[ 18 ]++;
										break;
									case 'abdh:Q6a:5':
										$ref[ 19 ]++;
										break;
									case 'abdh:Q6a:6':
										$ref[ 20 ]++;
										break;
								}
							}
						}
					
					} // Has species epithet.
				
				} // Iterating species.
			
			} // Has species.
			
			//
			// Increment households count.
			//
			if( $house_spec )
				$count++;
		
		} // Iterating.
		
		//
		// Normalise results.
		//
		foreach( array_keys( $data ) as $species )
		{
			//
			// Calculate species percentage.
			//
			if( $count )
			{
				$data[ $species ][ 3 ]
					= round( ( $data[ $species ][ 2 ] * 100 ) / $count, 2 );
			}
			
			//
			// Get the sum of transaction types.
			//
			for( $sum = 0, $i = 15; $i < count( $data[ $species ] ); $i++ )
				$sum += $data[ $species ][ $i ];
			
			//
			// Calculate percentages.
			//
			if( $sum )
			{
				for( $i = 15; $i < count( $data[ $species ] ); $i++ )
					$data[ $species ][ $i ]
						= round( ( $data[ $species ][ $i ] * 100 ) / $sum, 2 );
			}
		}
		
		//
		// Set data.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = array_values( $data );
		
	} // executeUnitStat4.


	/*===================================================================================
	 *	executeUnitStat5																*
	 *==================================================================================*/

	/**
	 * Perform statistic 5.
	 *
	 * This method will perform the statistics according to the data provided in the
	 * iterator parameter.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param ObjectIterator		$theIterator		Iterator.
	 *
	 * @access protected
	 */
	protected function executeUnitStat5( &$theContainer, $theIterator )
	{
		//
		// Save tags.
		//
		$tag_cat = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
		$tag_epithet = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
		$tag_cname = $this->mWrapper->getSerial( 'abdh:NAME_ENG', TRUE );
		$tag_freq = $this->mWrapper->getSerial( 'abdh:Q2.15a', TRUE );
		
		//
		// Set title
		//
		$this->getStatistics( $theContainer,
							  $this->offsetGet( kAPI_REQUEST_LANGUAGE ),
							  $this->offsetGet( kAPI_PARAM_DOMAIN ),
							  $this->offsetGet( kAPI_PARAM_STAT ) );
		
		//
		// Set header.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ] = Array();
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Common name',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'No. of households',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '%',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Renew seeds every year',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Renew seeds every 2 years',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Renew seeds every 3 years',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Never renew seeds',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Other',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		//
		// Iterate.
		//
		$count = 0;
		$data = Array();
		foreach( $theIterator as $object )
		{
			//
			// Init households counter.
			//
			$house_spec = 0;
			
			//
			// Get species sub-structure.
			//
			if( ($species = $object->offsetGet( 'abdh:species' )) !== NULL )
			{
				//
				// Iterate species.
				//
				foreach( $species as $record )
				{
					//
					// Check required offsets.
					//
					if( array_key_exists( $tag_epithet, $record )
					 && array_key_exists( $tag_freq, $record )
					 && array_key_exists( $tag_cat, $record )
					 && ($record[ $tag_cat ] == 'abdh:SPECIES_CAT:1') )
					{
						//
						// Increment species count.
						//
						$house_spec++;
						
						//
						// Create new entry.
						//
						if( ! array_key_exists( $record[ $tag_epithet ], $data ) )
						{
							//
							// Init record.
							//
							$data[ $record[ $tag_epithet ] ]
								= array(
									$record[ $tag_epithet ],
									NULL,
									1,
									0,
									0,
									0,
									0,
									0,
									0 );
				
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Set common name.
							//
							if( array_key_exists( $tag_cname, $record ) )
								$ref[ 1 ] = $record[ $tag_cname ];
			
						} // New species.
					
						//
						// Init existing species.
						//
						else
						{
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Increment households count.
							//
							$ref[ 2 ]++;
					
						} // Existing species.
						
						//
						// Handle seed renewal.
						//
						if( array_key_exists( $tag_freq, $record ) )
						{
							switch( $record[ $tag_freq ] )
							{
								case 'abdh:Q2.15a:1':
									$ref[ 4 ]++;
									break;
								case 'abdh:Q2.15a:2':
									$ref[ 5 ]++;
									break;
								case 'abdh:Q2.15a:3':
									$ref[ 6 ]++;
									break;
								case 'abdh:Q2.15a:4':
									$ref[ 7 ]++;
									break;
								case 'abdh:Q2.15a:5':
									$ref[ 8 ]++;
									break;
							}
						}
					
					} // Has species epithet.
				
				} // Iterating species.
			
			} // Has species.
			
			//
			// Increment households count.
			//
			if( $house_spec )
				$count++;
		
		} // Iterating.
		
		//
		// Calculate percentages.
		//
		foreach( array_keys( $data ) as $species )
		{
			if( $count )
			{
				$data[ $species ][ 3 ]
					= round( ( $data[ $species ][ 2 ] * 100 ) / $count, 2 );
			}
		}
		
		//
		// Set data.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = array_values( $data );
		
	} // executeUnitStat5.


	/*===================================================================================
	 *	executeUnitStat6																*
	 *==================================================================================*/

	/**
	 * Perform statistic 5.
	 *
	 * This method will perform the statistics according to the data provided in the
	 * iterator parameter.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param ObjectIterator		$theIterator		Iterator.
	 *
	 * @access protected
	 */
	protected function executeUnitStat6( &$theContainer, $theIterator )
	{
		//
		// Save tags.
		//
		$tag_cat = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
		$tag_epithet = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
		$tag_cname = $this->mWrapper->getSerial( 'abdh:NAME_ENG', TRUE );
		$tag_care = $this->mWrapper->getSerial( 'abdh:Q7a', TRUE );
		$tag_a_seed = $this->mWrapper->getSerial( 'abdh:Q2.22a', TRUE );
		$tag_field = $this->mWrapper->getSerial( 'abdh:Q8a', TRUE );
		$tag_a_cons = $this->mWrapper->getSerial( 'abdh:Q2.24a', TRUE );
		$tag_a_mark = $this->mWrapper->getSerial( 'abdh:Q2.25a', TRUE );
		$tag_harv = $this->mWrapper->getSerial( 'abdh:Q9a', TRUE );
		$tag_use = $this->mWrapper->getSerial( 'abdh:Q10a', TRUE );
		$tag_sale = $this->mWrapper->getSerial( 'abdh:Q11a', TRUE );
		
		//
		// Set title
		//
		$this->getStatistics( $theContainer,
							  $this->offsetGet( kAPI_REQUEST_LANGUAGE ),
							  $this->offsetGet( kAPI_PARAM_DOMAIN ),
							  $this->offsetGet( kAPI_PARAM_STAT ) );
		
		//
		// Set header.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ] = Array();
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Common name',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Species type',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'No. of households',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '%',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Husband takes care of the species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Wife takes care of the species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Both take care of the species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Children take care of the species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Others take care of the species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Husband takes decisions about seed planting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Wife takes decisions about seed planting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Both take decisions about seed planting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Children decisions about seed planting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Others take decisions about seed planting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Husband takes decisions about field management',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Wife takes decisions about field management',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Both take decisions about field management',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Children decisions about field management',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Others take decisions about field management',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Husband takes decisions about harvesting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Wife takes decisions about harvesting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Both take decisions about harvesting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Children decisions about harvesting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Others take decisions about harvesting',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Husband takes decisions about uses',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Wife takes decisions about uses',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Both take decisions about uses',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Children decisions about uses',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Others take decisions about uses',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Husband takes decisions about consumption',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Wife takes decisions about consumption',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Both take decisions about consumption',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Children decisions about consumption',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Others take decisions about consumption',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Husband takes decisions about marketing/sale',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Wife takes decisions about marketing/sale',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Both take decisions about marketing/sale',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Children decisions about marketing/sale',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Others take decisions about marketing/sale',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		//
		// Iterate.
		//
		$count = 0;
		$data = Array();
		foreach( $theIterator as $object )
		{
			//
			// Init households counter.
			//
			$house_spec = 0;
			
			//
			// Get species sub-structure.
			//
			if( ($species = $object->offsetGet( 'abdh:species' )) !== NULL )
			{
				//
				// Iterate species.
				//
				foreach( $species as $record )
				{
					//
					// Check required offsets.
					//
					if( array_key_exists( $tag_epithet, $record )
					 && array_key_exists( $tag_cat, $record )
					 && ( array_key_exists( $tag_care, $record )
					   || array_key_exists( $tag_a_seed, $record )
					   || array_key_exists( $tag_field, $record )
					   || array_key_exists( $tag_a_cons, $record )
					   || array_key_exists( $tag_a_mark, $record )
					   || array_key_exists( $tag_harv, $record )
					   || array_key_exists( $tag_use, $record )
					   || array_key_exists( $tag_sale, $record ) ) )
					{
						//
						// Increment species count.
						//
						$house_spec++;
						
						//
						// Create new entry.
						//
						if( ! array_key_exists( $record[ $tag_epithet ], $data ) )
						{
							//
							// Init record.
							//
							$data[ $record[ $tag_epithet ] ]
								= array(
									$record[ $tag_epithet ],
									NULL,
									NULL,
									1,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0 );
				
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Set common name.
							//
							if( array_key_exists( $tag_cname, $record ) )
								$ref[ 1 ] = $record[ $tag_cname ];
				
							//
							// Set species type.
							//
							if( array_key_exists( $tag_cat, $record ) )
							{
								switch( $record[ $tag_cat ] )
								{
									case 'abdh:SPECIES_CAT:1':
										$ref[ 2 ] = 'Annual plant species';
										break;
									case 'abdh:SPECIES_CAT:2':
										$ref[ 2 ] = 'Perennial plant species';
										break;
									case 'abdh:SPECIES_CAT:3':
										$ref[ 2 ] = 'Wild plant species';
										break;
									case 'abdh:SPECIES_CAT:4':
										$ref[ 2 ] = 'Domesticated animal species';
										break;
								}
							}
			
						} // New species.
					
						//
						// Init existing species.
						//
						else
						{
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Increment households count.
							//
							$ref[ 3 ]++;
					
						} // Existing species.
						
						//
						// Handle who takes care.
						//
						if( array_key_exists( $tag_care, $record ) )
						{
							foreach( $record[ $tag_care ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q7a:1':
										$ref[ 5 ]++;
										break;
									case 'abdh:Q7a:2':
										$ref[ 6 ]++;
										break;
									case 'abdh:Q7a:3':
										$ref[ 7 ]++;
										break;
									case 'abdh:Q7a:4':
										$ref[ 8 ]++;
										break;
									case 'abdh:Q7a:5':
										$ref[ 9 ]++;
										break;
								}
							}
						}
						
						//
						// Handle seed planting.
						//
						if( array_key_exists( $tag_a_seed, $record ) )
						{
							foreach( $record[ $tag_a_seed ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q2.22a:1':
										$ref[ 10 ]++;
										break;
									case 'abdh:Q2.22a:2':
										$ref[ 11 ]++;
										break;
									case 'abdh:Q2.22a:3':
										$ref[ 12 ]++;
										break;
									case 'abdh:Q2.22a:4':
										$ref[ 13 ]++;
										break;
									case 'abdh:Q2.22a:5':
										$ref[ 14 ]++;
										break;
								}
							}
						}
						
						//
						// Handle field management.
						//
						if( array_key_exists( $tag_field, $record ) )
						{
							foreach( $record[ $tag_field ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q8a:1':
										$ref[ 15 ]++;
										break;
									case 'abdh:Q8a:2':
										$ref[ 16 ]++;
										break;
									case 'abdh:Q8a:3':
										$ref[ 17 ]++;
										break;
									case 'abdh:Q8a:4':
										$ref[ 18 ]++;
										break;
									case 'abdh:Q8a:5':
										$ref[ 19 ]++;
										break;
								}
							}
						}
						
						//
						// Handle harvesting.
						//
						if( array_key_exists( $tag_harv, $record ) )
						{
							foreach( $record[ $tag_harv ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q9a:1':
										$ref[ 20 ]++;
										break;
									case 'abdh:Q9a:2':
										$ref[ 21 ]++;
										break;
									case 'abdh:Q9a:3':
										$ref[ 22 ]++;
										break;
									case 'abdh:Q9a:4':
										$ref[ 23 ]++;
										break;
									case 'abdh:Q9a:5':
										$ref[ 24 ]++;
										break;
								}
							}
						}
						
						//
						// Handle uses.
						//
						if( array_key_exists( $tag_use, $record ) )
						{
							foreach( $record[ $tag_use ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q10a:1':
										$ref[ 25 ]++;
										break;
									case 'abdh:Q10a:2':
										$ref[ 26 ]++;
										break;
									case 'abdh:Q10a:3':
										$ref[ 27 ]++;
										break;
									case 'abdh:Q10a:4':
										$ref[ 28 ]++;
										break;
									case 'abdh:Q10a:5':
										$ref[ 29 ]++;
										break;
								}
							}
						}
						
						//
						// Handle consumption.
						//
						if( array_key_exists( $tag_a_cons, $record ) )
						{
							foreach( $record[ $tag_a_cons ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q2.24a:1':
										$ref[ 30 ]++;
										break;
									case 'abdh:Q2.24a:2':
										$ref[ 31 ]++;
										break;
									case 'abdh:Q2.24a:3':
										$ref[ 32 ]++;
										break;
									case 'abdh:Q2.24a:4':
										$ref[ 33 ]++;
										break;
									case 'abdh:Q2.24a:5':
										$ref[ 34 ]++;
										break;
								}
							}
						}
						
						//
						// Handle marketing/sale.
						//
						if( array_key_exists( $tag_a_mark, $record ) )
						{
							foreach( $record[ $tag_a_mark ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q2.25a:1':
										$ref[ 35 ]++;
										break;
									case 'abdh:Q2.25a:2':
										$ref[ 36 ]++;
										break;
									case 'abdh:Q2.25a:3':
										$ref[ 37 ]++;
										break;
									case 'abdh:Q2.25a:4':
										$ref[ 38 ]++;
										break;
									case 'abdh:Q2.25a:5':
										$ref[ 39 ]++;
										break;
								}
							}
						}
						elseif( array_key_exists( $tag_sale, $record ) )
						{
							foreach( $record[ $tag_sale ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q11a:1':
										$ref[ 35 ]++;
										break;
									case 'abdh:Q11a:2':
										$ref[ 36 ]++;
										break;
									case 'abdh:Q11a:3':
										$ref[ 37 ]++;
										break;
									case 'abdh:Q11a:4':
										$ref[ 38 ]++;
										break;
									case 'abdh:Q11a:5':
										$ref[ 39 ]++;
										break;
								}
							}
						}
					
					} // Has species epithet.
				
				} // Iterating species.
			
			} // Has species.
			
			//
			// Increment households count.
			//
			if( $house_spec )
				$count++;
		
		} // Iterating.
		
		//
		// Calculate percentages.
		//
		foreach( array_keys( $data ) as $species )
		{
			if( $count )
			{
				$data[ $species ][ 4 ]
					= round( ( $data[ $species ][ 3 ] * 100 ) / $count, 2 );
			}
		}
		
		//
		// Set data.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = array_values( $data );
		
	} // executeUnitStat6.


	/*===================================================================================
	 *	executeUnitStat7																*
	 *==================================================================================*/

	/**
	 * Perform statistic 7.
	 *
	 * This method will perform the statistics according to the data provided in the
	 * iterator parameter.
	 *
	 * @param array					$theContainer		Reference to the results container.
	 * @param ObjectIterator		$theIterator		Iterator.
	 *
	 * @access protected
	 */
	protected function executeUnitStat7( &$theContainer, $theIterator )
	{
		//
		// Save tags.
		//
		$tag_cat = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
		$tag_epithet = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
		$tag_cname = $this->mWrapper->getSerial( 'abdh:NAME_ENG', TRUE );
		$tag_obj = $this->mWrapper->getSerial( 'abdh:Q3', TRUE );
		$tag_use = $this->mWrapper->getSerial( 'abdh:Q5a', TRUE );
		
		//
		// Set title
		//
		$this->getStatistics( $theContainer,
							  $this->offsetGet( kAPI_REQUEST_LANGUAGE ),
							  $this->offsetGet( kAPI_PARAM_DOMAIN ),
							  $this->offsetGet( kAPI_PARAM_STAT ) );
		
		//
		// Set header.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ] = Array();
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Species',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Common name',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Species type',
					 kAPI_PARAM_DATA_TYPE => kTYPE_STRING );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'No. of households',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => '%',
					 kAPI_PARAM_DATA_TYPE => kTYPE_FLOAT );
		
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Self consumption exclusively',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Selling exclusively',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Both self consumption and selling',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Food',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Fodder/animal feed',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Medecine',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Fuel',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Construction material',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_HEAD ][]
			= array( kAPI_PARAM_RESPONSE_FRMT_NAME => 'Other',
					 kAPI_PARAM_DATA_TYPE => kTYPE_INT );
		
		//
		// Iterate.
		//
		$count = 0;
		$data = Array();
		foreach( $theIterator as $object )
		{
			//
			// Init households counter.
			//
			$house_spec = 0;
			
			//
			// Get species sub-structure.
			//
			if( ($species = $object->offsetGet( 'abdh:species' )) !== NULL )
			{
				//
				// Iterate species.
				//
				foreach( $species as $record )
				{
					//
					// Check required offsets.
					//
					if( array_key_exists( $tag_epithet, $record )
					 && array_key_exists( $tag_obj, $record )
					 && array_key_exists( $tag_use, $record )
					 && array_key_exists( $tag_cat, $record ) )
					{
						//
						// Increment species count.
						//
						$house_spec++;
						
						//
						// Create new entry.
						//
						if( ! array_key_exists( $record[ $tag_epithet ], $data ) )
						{
							//
							// Init record.
							//
							$data[ $record[ $tag_epithet ] ]
								= array(
									$record[ $tag_epithet ],
									NULL,
									NULL,
									1,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0,
									0 );
				
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Set common name.
							//
							if( array_key_exists( $tag_cname, $record ) )
								$ref[ 1 ] = $record[ $tag_cname ];
				
							//
							// Set species type.
							//
							if( array_key_exists( $tag_cat, $record ) )
							{
								switch( $record[ $tag_cat ] )
								{
									case 'abdh:SPECIES_CAT:1':
										$ref[ 2 ] = 'Annual plant species';
										break;
									case 'abdh:SPECIES_CAT:2':
										$ref[ 2 ] = 'Perennial plant species';
										break;
									case 'abdh:SPECIES_CAT:3':
										$ref[ 2 ] = 'Wild plant species';
										break;
									case 'abdh:SPECIES_CAT:4':
										$ref[ 2 ] = 'Domesticated animal species';
										break;
								}
							}
			
						} // New species.
					
						//
						// Init existing species.
						//
						else
						{
							//
							// Reference record.
							//
							$ref = & $data[ $record[ $tag_epithet ] ];
				
							//
							// Increment households count.
							//
							$ref[ 3 ]++;
					
						} // Existing species.
						
						//
						// Handle objectives of production.
						//
						if( array_key_exists( $tag_obj, $record ) )
						{
							switch( $record[ $tag_obj ] )
							{
								case 'abdh:Q3:1':
									$ref[ 5 ]++;
									break;
								case 'abdh:Q3:2':
									$ref[ 6 ]++;
									break;
								case 'abdh:Q3:3':
									$ref[ 7 ]++;
									break;
							}
						}
						
						//
						// Handle uses of production.
						//
						if( array_key_exists( $tag_use, $record ) )
						{
							foreach( $record[ $tag_use ] as $item )
							{
								switch( $item )
								{
									case 'abdh:Q5a:1':
										$ref[ 8 ]++;
										break;
									case 'abdh:Q5a:2':
										$ref[ 9 ]++;
										break;
									case 'abdh:Q5a:3':
										$ref[ 10 ]++;
										break;
									case 'abdh:Q5a:4':
										$ref[ 11 ]++;
										break;
									case 'abdh:Q5a:5':
										$ref[ 12 ]++;
										break;
									case 'abdh:Q5a:6':
										$ref[ 13 ]++;
										break;
								}
							}
						}
					
					} // Has species epithet.
				
				} // Iterating species.
			
			} // Has species.
			
			//
			// Increment households count.
			//
			if( $house_spec )
				$count++;
		
		} // Iterating.
		
		//
		// Calculate percentages.
		//
		foreach( array_keys( $data ) as $species )
		{
			if( $count )
			{
				$data[ $species ][ 4 ]
					= round( ( $data[ $species ][ 3 ] * 100 ) / $count, 2 );
			}
		}
		
		//
		// Set data.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = array_values( $data );
		
	} // executeUnitStat7.


	/*===================================================================================
	 *	executeSerialiseResults															*
	 *==================================================================================*/

	/**
	 * Serialise results.
	 *
	 * This method will serialise the data from the provided iterator.
	 *
	 * @param ObjectIterator		$theIterator		Iterator object.
	 *
	 * @access protected
	 */
	protected function executeSerialiseResults( ObjectIterator $theIterator )
	{
		//
		// Instantiate results formatter.
		//
		$formatter
			= new IteratorSerialiser(
					$theIterator,									// Iterator.
					kAPI_RESULT_ENUM_DATA_RECORD,					// Format.
					$this->offsetGet( kAPI_REQUEST_LANGUAGE ) );	// language.
		
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
		
	} // executeSerialiseResults.


	/*===================================================================================
	 *	executeSerialiseSummaryTags														*
	 *==================================================================================*/

	/**
	 * Serialise summary tags.
	 *
	 * This method will serialise the data from the provided iterator which is expected to
	 * hold a list of summary tag objects.
	 *
	 * @param ObjectIterator		$theIterator		Iterator object.
	 *
	 * @access protected
	 */
	protected function executeSerialiseSummaryTags( ObjectIterator $theIterator )
	{
		//
		// Init local storage.
		//
		$results = Array();
		$language = $this->offsetGet( kAPI_REQUEST_LANGUAGE );
		
		//
		// Iterate tags.
		//
		foreach( $theIterator as $object )
		{
			//
			// Check tag offsets.
			//
			$offsets = $object->offsetGet( kTAG_UNIT_OFFSETS );
			if( is_array( $offsets ) )
			{
				//
				// Iterate tag offsets.
				//
				foreach( $offsets as $offset )
				{
					//
					// Allocate offset.
					//
					$results[ $offset ] = Array();
					$offset_ref = & $results[ $offset ];
				
					//
					// Set tag reference.
					//
					$offset_ref[ kAPI_PARAM_TAG ] = $object->offsetGet( kTAG_ID_HASH );
					
					//
					// Reference structure.
					//
					$struct_ref = & $offset_ref;
					
					//
					// Explode structures.
					//
					$structs = explode( '.', $offset );
					foreach( $structs as $struct )
					{
						//
						// Allocate struct element.
						//
						$struct_ref[ kAPI_PARAM_RESPONSE_CHILDREN ] = Array();
						$struct_ref = & $struct_ref[ kAPI_PARAM_RESPONSE_CHILDREN ];
					
						//
						// Resolve structure tag.
						//
						$tag = ( $struct == $object[ kTAG_ID_HASH ] )
							 ? $object
							 : $this->mWrapper->getObject( $struct, TRUE );
			
						//
						// Set tag label.
						//
						if( isset( $tag[ kTAG_LABEL ] ) )
							$struct_ref[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
								= OntologyObject::SelectLanguageString(
									$tag[ kTAG_LABEL ], $language );
			
						//
						// Set tag description.
						//
						if( isset( $tag[ kTAG_DESCRIPTION ] ) )
							$struct_ref[ kAPI_PARAM_RESPONSE_FRMT_INFO ]
								= OntologyObject::SelectLanguageString(
									$tag[ kTAG_DESCRIPTION ], $language );
				
					} // Iterating structure.
			
				} // Iterating tag offsets.
			
			} // Has offsets.
		
		} // Iterated iterator.
		
		//
		// Set data.
		//
		$this->mResponse[ kAPI_RESPONSE_RESULTS ] = $results;
		
	} // executeSerialiseSummaryTags.

		

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
 *						PROTECTED SESSION & TRANSACTION UTILITIES						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadSessionProgress																*
	 *==================================================================================*/

	/**
	 * Load session progress.
	 *
	 * The method will resolve the session provided in the {@link kAPI_PARAM_ID} parameter,
	 * serialise the result selecting only relevant properties, set the paging information
	 * in the session result and set the serialised session data in the provided array
	 * container; if the session could not be found, the method will set the container to an
	 * empty array.
	 *
	 * @param array				   &$theContainer		Receives serialised session.
	 *
	 * @access protected
	 * @return array				Serialised session.
	 */
	protected function loadSessionProgress( &$theContainer )
	{
		//
		// Init local storage.
		//
		$session = $this->offsetGet( kAPI_PARAM_ID );
		$collection
			= Session::ResolveCollection(
				Session::ResolveDatabase( $this->mWrapper, TRUE ),
				TRUE );
		$id = $collection->getObjectId( $session );
		if( $id === NULL )
			throw new \Exception(
				"Cannot use identifier: "
			   ."invalid session identifier [$session]." );						// !@! ==>
		
		//
		// Get session.
		//
		$object = new Session( $this->mWrapper, $id );
		
		//
		// Get session iterator.
		//
		$iterator
			= $collection
				->matchAll(
					array( kTAG_NID => $id ),
					kQUERY_OBJECT );
		
		//
		// Instantiate results formatter.
		//
		$formatter
			= new IteratorSerialiser(
					$iterator,										// Iterator.
					kAPI_RESULT_ENUM_DATA_FORMAT,					// Format.
					$this->offsetGet( kAPI_REQUEST_LANGUAGE ),		// Language.
					NULL,											// Domain.
					NULL,											// Shape.
					kFLAG_FORMAT_OPT_DYNAMIC |						// Options.
					kFLAG_FORMAT_OPT_PRIVATE |
					kFLAG_FORMAT_OPT_VALUES  |
					kFLAG_FORMAT_OPT_TYPE_KIND );
	
		//
		// Serialise iterator.
		//
		$formatter->serialise();
	
		//
		// Set paging.
		//
		$this->mResponse[ kAPI_RESPONSE_PAGING ] = $formatter->paging();
		
		//
		// Load session data.
		//
		$this->serialiseSession( $theContainer, $formatter, TRUE );
		
	} // loadSessionProgress.

	 
	/*===================================================================================
	 *	loadTransactionProgress															*
	 *==================================================================================*/

	/**
	 * Load session progress.
	 *
	 * The method will resolve the session provided in the {@link kAPI_PARAM_ID} parameter,
	 * serialise the result selecting only relevant properties, set the paging information
	 * in the session result and set the serialised session data in the provided array
	 * container; if the session could not be found, the method will set the container to an
	 * empty array.
	 *
	 * @param array				   &$theContainer		Receives serialised session.
	 * @param string				$theSession			Session identifier.
	 *
	 * @access protected
	 */
	protected function loadTransactionProgress( &$theContainer, $theSession )
	{
		//
		// Init local storage.
		//
		$collection
			= Transaction::ResolveCollection(
				Transaction::ResolveDatabase( $this->mWrapper, TRUE ),
				TRUE );
		
		//
		// Get transaction iterator.
		//
		$tmp = $collection->getObjectId( $theSession );
		if( $tmp === NULL )
			throw new \Exception(
				"Cannot use identifier: "
			   ."invalid session identifier [$theSession]." );					// !@! ==>
		$iterator
			= $collection
				->matchAll(
					array( kTAG_SESSION => $tmp,
						   kTAG_TRANSACTION => array( '$exists' => FALSE ) ),
					kQUERY_OBJECT );
		
		//
		// Sort by start time stamp.
		//
		$iterator->sort( array( kTAG_TRANSACTION_START => 1 ) );
		
		//
		// Instantiate results formatter.
		//
		$formatter
			= new IteratorSerialiser(
					$iterator,										// Iterator.
					kAPI_RESULT_ENUM_DATA_FORMAT,					// Format.
					$this->offsetGet( kAPI_REQUEST_LANGUAGE ),		// Language.
					NULL,											// Domain.
					NULL,											// Shape.
					kFLAG_FORMAT_OPT_DYNAMIC |						// Options.
					kFLAG_FORMAT_OPT_PRIVATE |
					kFLAG_FORMAT_OPT_VALUES  |
					kFLAG_FORMAT_OPT_TYPE_KIND );
	
		//
		// Serialise iterator.
		//
		$formatter->serialise();
		
		//
		// Serialise transactions.
		//
		$this->serialiseTransaction( $theContainer, $formatter, TRUE );
		
	} // loadTransactionProgress.

	 
	/*===================================================================================
	 *	loadNestedTransactions															*
	 *==================================================================================*/

	/**
	 * Load nested transactions
	 *
	 * The method will load all nested transactions related to the provided transaction
	 * identifier.
	 *
	 * The transactions will be loaded in nested {@link kAPI_PARAM_RESPONSE_FRMT_DOCU}
	 * elements.
	 *
	 * @param array				   &$theContainer		Receives serialised session.
	 * @param string				$theTransaction		Transaction identifier.
	 *
	 * @access protected
	 */
	protected function loadNestedTransactions( &$theContainer, $theTransaction )
	{
		//
		// Init local storage.
		//
		$collection
			= Transaction::ResolveCollection(
				Transaction::ResolveDatabase( $this->mWrapper, TRUE ),
				TRUE );
		
		//
		// Get transaction iterator.
		//
		$tmp = $collection->getObjectId( $theTransaction );
		if( $tmp === NULL )
			throw new \Exception(
				"Cannot use identifier: "
			   ."invalid transaction identifier [$theTransaction]." );			// !@! ==>
		$iterator
			= $collection
				->matchAll(
					array( kTAG_TRANSACTION => $tmp ),
					kQUERY_OBJECT );
		
		//
		// Check if found.
		//
		if( $iterator->count() )
		{
			//
			// Sort by start time stamp.
			//
			$iterator->sort( array( kTAG_TRANSACTION_START => 1 ) );
		
			//
			// Instantiate results formatter.
			//
			$formatter
				= new IteratorSerialiser(
						$iterator,										// Iterator.
						kAPI_RESULT_ENUM_DATA_FORMAT,					// Format.
						$this->offsetGet( kAPI_REQUEST_LANGUAGE ),		// Language.
						NULL,											// Domain.
						NULL,											// Shape.
						kFLAG_FORMAT_OPT_DYNAMIC |						// Options.
						kFLAG_FORMAT_OPT_PRIVATE |
						kFLAG_FORMAT_OPT_TYPE_KIND );
	
			//
			// Serialise iterator.
			//
			$formatter->serialise();
		
			//
			// Serialise transactions.
			//
			$this->serialiseTransaction( $theContainer, $formatter, FALSE );
		
		} // Found transaction.
		
	} // loadNestedTransactions.

		
	/*===================================================================================
	 *	serialiseSession																*
	 *==================================================================================*/

	/**
	 * Serialise session.
	 *
	 * This method will serialise the provided session into the provided array parameter.
	 *
	 * If the last parameter is <tt>TRUE</tt>, only the session properties relevant to
	 * progress will be serialised.
	 *
	 * @param array				   &$theContainer		Receives serialised session.
	 * @param IteratorSerialiser	$theIterator		Session iterator serialiser.
	 * @param boolean				$doProgress			If <tt>TRUE</tt> for progress.
	 *
	 * @access protected
	 * @return array				Search criteria.
	 */
	protected function serialiseSession(				   &$theContainer,
										 IteratorSerialiser $theIterator,
										 					$doProgress = TRUE )
	{
		//
		// Set properties.
		//
		$properties = array( kTAG_SESSION_TYPE,
							 kTAG_SESSION_START, kTAG_SESSION_END,
							 kTAG_SESSION_STATUS,
							 kTAG_COUNTER_PROGRESS );
		
		//
		// Set counters.
		//
		$counters = array( kTAG_COUNTER_RECORDS,
						   kTAG_COUNTER_PROCESSED, kTAG_COUNTER_VALIDATED,
						   kTAG_COUNTER_REJECTED, kTAG_COUNTER_SKIPPED );
		
		//
		// Get iterator array.
		//
		$objects = $theIterator->getIteratorArray();
		
		//
		// Iterate sessions (will be only one).
		//
		foreach( $theIterator->data() as $data )
		{
			//
			// Pop object and get status.
			//
			$object = array_shift( $objects );
			$status = $object->offsetGet( kTAG_SESSION_STATUS );
			
			//
			// Set session properties.
			//
			foreach( $properties as $property )
			{
				//
				// Check property.
				//
				if( array_key_exists( $property, $data ) )
					$theContainer[ $property ]
						= $data[ $property ];
			}
			
			//
			// Load counters.
			//
			foreach( $counters as $counter )
			{
				//
				// Check property.
				//
				if( array_key_exists( $counter, $data )
				 && $object->offsetGet( $counter ) )
					$theContainer[ $counter ]
						= $data[ $counter ];
			}
			
			//
			// Load additional properties.
			//
			if( (! $doProgress)
			 || ($status == kTYPE_STATUS_FATAL)
			 || ($status == kTYPE_STATUS_FAILED)
			 || ($status == kTYPE_STATUS_EXCEPTION) )
			{
				//
				// Collect extra properties.
				//
				$extra = array( kTAG_ERROR_TYPE, kTAG_ERROR_CODE, kTAG_TRANSACTION_MESSAGE,
								kTAG_ERROR_RESOURCE );
				
				//
				// Iterate extra properties.
				//
				foreach( $extra as $property )
				{
					//
					// Check property.
					//
					if( array_key_exists( $property, $data ) )
						$theContainer[ $property ]
							= $data[ $property ];
				}
			}
		
		} // Iterating sessions.
		
	} // serialiseSession.
	
	
	/*===================================================================================
	 *	serialiseTransaction															*
	 *==================================================================================*/

	/**
	 * Serialise transaction.
	 *
	 * This method will serialise the provided transaction into the provided array
	 * parameter.
	 *
	 * If the last parameter is <tt>TRUE</tt>, only the transaction properties relevant to
	 * progress will be serialised.
	 *
	 * @param array				   &$theContainer		Receives serialised transaction.
	 * @param IteratorSerialiser	$theIterator		Session iterator serialiser.
	 * @param boolean				$doProgress			If <tt>TRUE</tt> for progress.
	 *
	 * @access protected
	 */
	protected function serialiseTransaction(				   &$theContainer,
											 IteratorSerialiser $theIterator,
										 						$doProgress = TRUE )
	{
		//
		// Set properties.
		//
		$properties = array( kTAG_TRANSACTION_TYPE,
							 kTAG_TRANSACTION_START, kTAG_TRANSACTION_END,
							 kTAG_TRANSACTION_STATUS,
							 kTAG_COUNTER_PROGRESS, kTAG_TRANSACTION_COLLECTION );
		
		//
		// Set counters.
		//
		$counters = array( kTAG_COUNTER_RECORDS,
						   kTAG_COUNTER_PROCESSED, kTAG_COUNTER_VALIDATED,
						   kTAG_COUNTER_REJECTED, kTAG_COUNTER_SKIPPED );
		
		//
		// Get iterator array.
		//
		$objects = $theIterator->getIteratorArray();
		
		//
		// Allocate transactions list.
		//
		$theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] = Array();
		$ref = & $theContainer[ kAPI_PARAM_RESPONSE_FRMT_DOCU ];
		
		//
		// Iterate transactions.
		//
		foreach( $theIterator->data() as $data )
		{
			//
			// Pop object and get status.
			//
			$object = array_shift( $objects );
			$status = $object->offsetGet( kTAG_TRANSACTION_STATUS );
			
			//
			// Allocate transaction element.
			//
			$index = count( $ref );
			$ref[] = Array();
			
			//
			// Set transaction properties.
			//
			foreach( $properties as $property )
			{
				//
				// Check property.
				//
				if( array_key_exists( $property, $data ) )
					$ref[ $index ][ $property ]
						= $data[ $property ];
			}
			
			//
			// Load counters.
			//
			foreach( $counters as $counter )
			{
				//
				// Check property.
				//
				if( array_key_exists( $counter, $data )
				 && $object->offsetGet( $counter ) )
					$ref[ $index ][ $counter ]
						= $data[ $counter ];
			}
			
			//
			// Load additional properties.
			//
			if( (! $doProgress)
			 || ($status == kTYPE_STATUS_FATAL)
			 || ($status == kTYPE_STATUS_FAILED)
			 || ($status == kTYPE_STATUS_EXCEPTION) )
			{
				//
				// Collect extra properties.
				//
				$extra = array( kTAG_TRANSACTION_RECORD,
								kTAG_ERROR_TYPE, kTAG_ERROR_CODE, kTAG_TRANSACTION_MESSAGE,
								kTAG_ERROR_RESOURCE );
				
				//
				// Iterate extra properties.
				//
				foreach( $extra as $property )
				{
					//
					// Check property.
					//
					if( array_key_exists( $property, $data ) )
						$ref[ $index ][ $property ]
							= $data[ $property ];
				}
				
				//
				// Check log.
				//
				if( array_key_exists( kTAG_TRANSACTION_LOG, $data ) )
					$ref[ $index ][ kTAG_TRANSACTION_LOG ]
						= $data[ kTAG_TRANSACTION_LOG ];
				
				//
				// Load nested transactions.
				//
				$this->loadNestedTransactions(
							$ref[ $index ], $object->offsetGet( kTAG_NID ) );
			}
		
		} // Iterating transactions.
		
	} // serialiseTransaction.

		

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
							=> array( array( $geom[ 0 ], $geom[ 1 ] ),
									  $theShape[ kTAG_RADIUS ] / 6371000 ) ) );								// ==>
			
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
					   :  key( $cluster[ kAPI_PARAM_CRITERIA ] );
				
				//
				// Load tag match clause.
				//
				if( $cluster_count > 1 )
					$root[] = array( kTAG_OBJECT_TAGS => $match );
				else
					$root[ kTAG_OBJECT_TAGS ] = $match;
				
				//
				// Check for offsets match.
				//
				if( $criteria_count )
				{
					//
					// Collect offsets.
					//
					$offsets = Array();
					foreach( $cluster[ kAPI_PARAM_CRITERIA ] as $tag => $criteria )
					{
						if( is_array( $criteria ) )
						{
							//
							// Intercept offsets.
							//
							if( array_key_exists( kAPI_PARAM_OFFSETS, $criteria ) )
							{
								//
								// Set match value.
								//
								$match = ( count( $criteria[ kAPI_PARAM_OFFSETS ] ) > 1 )
									   ? array( '$in' => $criteria[ kAPI_PARAM_OFFSETS ] )
									   : $criteria[ kAPI_PARAM_OFFSETS ][ 0 ];
						
								//
								// Load offset match clause.
								//
								if( $cluster_count > 1 )
									$root[] = array( kTAG_OBJECT_OFFSETS => $match );
								else
									$root[ kTAG_OBJECT_OFFSETS ] = $match;
						
							} // Has offsets.
						
						} // Criteria is set.
					
					} // Iterating cluster criteria.
				
				} // Has criteria.
			
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
							$criteria_ref[] = array( kTAG_OBJECT_TAGS => $tag );
						else
							$criteria_ref[ kTAG_OBJECT_TAGS ] = $tag;
					
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
							//
							// Use tag reference.
							//
							if( (! array_key_exists( kAPI_QUERY_OFFSETS, $criteria ))
							 || (! $criteria[ kAPI_QUERY_OFFSETS ]) )
							{
								if( $parent_cri !== NULL )
									$criteria_ref[] = array( kTAG_OBJECT_TAGS => $tag );
								else
									$criteria_ref[ kTAG_OBJECT_TAGS ] = $tag;
							
							} // Resolve using tag.
							
							//
							// Use offset reference.
							//
							else
							{
								//
								// Handle single offset.
								//
								if( count( $criteria[ kAPI_PARAM_OFFSETS ] ) == 1 )
								{
									if( $parent_cri !== NULL )
										$criteria_ref[]
											= array(
												kTAG_OBJECT_OFFSETS
											 => $criteria[ kAPI_PARAM_OFFSETS ][ 0 ] );
									else
										$criteria_ref[ kTAG_OBJECT_OFFSETS ]
											= $criteria[ kAPI_PARAM_OFFSETS ][ 0 ];
								
								} // One offset.
								
								//
								// Handle multiple offsets.
								//
								elseif( count( $criteria[ kAPI_PARAM_OFFSETS ] ) > 1 )
								{
									if( $parent_cri !== NULL )
										$criteria_ref[]
											= array(
												kTAG_OBJECT_OFFSETS => array(
													'$in' => $criteria[ kAPI_PARAM_OFFSETS ]
												) );
									else
										$criteria_ref[ kTAG_OBJECT_OFFSETS ]
											= array(
												'$in' => $criteria[ kAPI_PARAM_OFFSETS ] );
								
								} // many offsets.
							
							} // Resolve using offsets.
						
						} // Not indexed.
						
						//
						// Handle indexed.
						//
						elseif( is_array( $criteria[ kAPI_PARAM_INDEX ] ) )
						{
							//
							// Intercept unindexed offsets.
							//
							$tmp
								= array_values(
									array_diff( $criteria[ kAPI_PARAM_OFFSETS ],
											   $criteria[ kAPI_PARAM_INDEX ] ) );
							if( count( $tmp ) )
							{
								//
								// Handle single offset.
								//
								if( count( $tmp ) == 1 )
								{
									if( $parent_cri !== NULL )
										$criteria_ref[]
											= array( kTAG_OBJECT_OFFSETS => $tmp[ 0 ] );
									else
										$criteria_ref[ kTAG_OBJECT_OFFSETS ]
											= $tmp[ 0 ];
								
								} // One offset.
								
								//
								// Handle multiple offsets.
								//
								else
								{
									if( $parent_cri !== NULL )
										$criteria_ref[]
											= array(
												kTAG_OBJECT_OFFSETS
													=> array( '$in' => $tmp ) );
									else
										$criteria_ref[ kTAG_OBJECT_OFFSETS ]
											= array( '$in' => $tmp );
								
								} // many offsets.
							
							} // Has unindexed offsets.
						
						} // Indexed.
						
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
									
									if( ($parent_cri !== NULL)
									 || ($offsets_count > 1) )
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
									
									if( ($parent_cri !== NULL)
									 || ($offsets_count > 1) )
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
									
									if( ($parent_cri !== NULL)
									 || ($offsets_count > 1) )
										$criteria_ref[] = array( $offset => $clause );
									else
										$criteria_ref[ $offset ] = $clause;
									
									break;
				
								default:
									if( ($parent_cri !== NULL)
									 || ($offsets_count > 1) )
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
				case kAPI_PARAM_COLLECTION_USER: return kTAG_USER_COUNT;		// ==>
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

	 
	/*===================================================================================
	 *	buildCriteria																	*
	 *==================================================================================*/

	/**
	 * Build criteria entry.
	 *
	 * This method will return a criteria entry according to the provided parameters; it is
	 * the responsibility of the caller to manage the criteria element key.
	 *
	 * The match is assumed an exact match.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value.
	 * @param array					$theOffsets			Offsets list.
	 *
	 * @access protected
	 * @return array				Criteria record.
	 */
	protected function buildCriteria( $theOffset, $theValue, $theOffsets = NULL )
	{
		//
		// Init local storage.
		//
		$criteria = Array();
		
		//
		// Get leaf tag.
		//
		$tag = explode( '.', $theOffset );
		$tag = $tag[ count( $tag ) - 1 ];
		$tag = $this->mWrapper->getObject( $tag, TRUE );
		
		//
		// Handle by type.
		//
		switch( $type = $tag[ kTAG_DATA_TYPE ] )
		{
			case kTYPE_STRING:
			case kTYPE_TEXT:
			case kTYPE_URL:
			case kTYPE_YEAR:
			case kTYPE_DATE:
				$criteria[ kAPI_PARAM_INPUT_TYPE ] = kAPI_PARAM_INPUT_STRING;
				$criteria[ kAPI_PARAM_PATTERN ] = (string) $theValue;
				$criteria[ kAPI_PARAM_OPERATOR ] = array( kOPERATOR_EQUAL );
				break;
			
			case kTYPE_INT:
				$criteria[ kAPI_PARAM_INPUT_TYPE ] = kAPI_PARAM_INPUT_DEFAULT;
				$criteria[ kAPI_PARAM_PATTERN ] = (int) $theValue;
				break;
			
			case kTYPE_FLOAT:
				$criteria[ kAPI_PARAM_INPUT_TYPE ] = kAPI_PARAM_INPUT_DEFAULT;
				$criteria[ kAPI_PARAM_PATTERN ] = (double) $theValue;
				break;
			
			case kTYPE_BOOLEAN:
				$criteria[ kAPI_PARAM_INPUT_TYPE ] = kAPI_PARAM_INPUT_DEFAULT;
				$criteria[ kAPI_PARAM_PATTERN ] = (boolean) $theValue;
				break;
			
			case kTYPE_ENUM:
			case kTYPE_SET:
				$criteria[ kAPI_PARAM_INPUT_TYPE ] = kAPI_PARAM_INPUT_ENUM;
				if( is_array( $theValue ) )
				{
					$tmp = Array();
					foreach( $theValue as $value )
						$tmp[] = (string) $value;
					$criteria[ kAPI_RESULT_ENUM_TERM ] = $tmp;
				}
				else
					$criteria[ kAPI_RESULT_ENUM_TERM ] = array( (string) $theValue );
				break;
			
			default:
				throw new \Exception(
					"Invalid or unsupported type [$type]." );					// !@! ==>
		}
		
		//
		// Handle offsets.
		//
		if( is_array( $theOffsets ) )
			$criteria[ kAPI_PARAM_OFFSETS ] = $theOffsets;
		
		return $criteria;															// ==>
		
	} // buildCriteria.

	 
	/*===================================================================================
	 *	traverseStructure																*
	 *==================================================================================*/

	/**
	 * Traverse structure.
	 *
	 * This method will traverse the structure of the provided form node, loading the
	 * information in the provided container.
	 *
	 * The information is loaded as follows:
	 *
	 * <ul>
	 *	<li><tt>{@lnk kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: The node label, or the label of
	 *		the element the node references.
	 *	<li><tt>{@lnk kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: The node description, the
	 *		definition of the term, or the description of the tag.
	 *	<li><tt>{@lnk kAPI_PARAM_TAG}</tt>: If the node references a tag, the tag's native
	 *		identifier.
	 *	<li><tt>{@lnk kAPI_PARAM_RESPONSE_CHILDREN}</tt>: If there are nodes relating to
	 *		the provided node via the {@link kPREDICATE_PROPERTY_OF} or
	 *		{@link kPREDICATE_SUBCLASS_OF} predicates, these elements will be loaded in this
	 *		property; in the latter predicate case, the related node will act as a proxy for
	 *		the current element.
	 * </ul>
	 *
	 * If any element cannot be resolved, the method will raise an exception.
	 *
	 * @param array					$theContainer		Receives information.
	 * @param int					$theNode			Node native identifier.
	 * @param string				$theLanguage		Default language.
	 * @param int					$theParent			Parent node native identifier.
	 * @param string				$theRefCount		Reference count tag.
	 *
	 * @access protected
	 * @return array				Criteria record.
	 */
	protected function traverseStructure( &$theContainer, $theNode,
														  $theLanguage,
														  $theParent = NULL,
														  $theRefCount = NULL )
	{
		//
		// Allocate element.
		//
		$index = count( $theContainer );
		$theContainer[ $index ] = Array();
		
		//
		// Load element information.
		//
		$node
			= $this->loadNodeElementInfo(
				$theContainer[ $index ], $theNode, $theLanguage,
				$theParent, $theRefCount );
		
		//
		// Handle root node.
		//
		$is_root = ( $node->NodeType( kTYPE_NODE_ROOT ) !== NULL );
		
		//
		// Recurse structure.
		//
		if( (! $is_root)								// Not a root,
		 || (! $this->mRootProcessed)					// or root not yet processed,
		 || $this->offsetGet( kAPI_PARAM_RECURSE ) )	// or traverse roots.
		{
			//
			// Set root node flag.
			//
			if( $is_root )
				$this->mRootProcessed = TRUE;
		
			//
			// Allocate children.
			//
			$children = Array();
		
			//
			// Load related nodes.
			//
			$this->traverseEdges(
				$children, $theNode, $theLanguage, $theParent, $theRefCount );
		
			//
			// Set children.
			//
			if( count( $children ) )
				$theContainer[ $index ][ kAPI_PARAM_RESPONSE_CHILDREN ] = $children;
		
		} // Not a root node.
		
	} // traverseStructure.

	 
	/*===================================================================================
	 *	traverseEdges																	*
	 *==================================================================================*/

	/**
	 * Traverse edges.
	 *
	 * This method will scan the edges that point to the provided node, recursing subclass
	 * predicates.
	 *
	 * If any element cannot be resolved, the method will raise an exception.
	 *
	 * @param array					$theContainer		Receives information.
	 * @param int					$theNode			Node native identifier.
	 * @param string				$theLanguage		Default language.
	 * @param int					$theParent			Parent node native identifier.
	 * @param string				$theRefCount		Reference count tag.
	 *
	 * @access protected
	 * @return array				Criteria record.
	 */
	protected function traverseEdges( &$theContainer, $theNode,
													  $theLanguage,
													  $theParent = NULL,
													  $theRefCount = NULL )
	{
		//
		// Get related elements.
		//
		$edges
			= Edge::ResolveCollection(
				Edge::ResolveDatabase( $this->mWrapper ) )
					->matchAll(
						array( kTAG_OBJECT => $theNode,
							   kTAG_PREDICATE
									=> array( '$in'
										=> array( kPREDICATE_PROPERTY_OF,
												  kPREDICATE_SUBCLASS_OF ) ) ),
						kQUERY_ARRAY,
						array( kTAG_SUBJECT => TRUE,
							   kTAG_PREDICATE => TRUE ) );
		
		//
		// Iterate related.
		//
		foreach( $edges as $edge )
		{
			//
			// Recurse subclasses.
			//
			if( $edge[ kTAG_PREDICATE ] == kPREDICATE_SUBCLASS_OF )
				$this->traverseEdges(
					$theContainer, $edge[ kTAG_SUBJECT ], $theLanguage,
					$theNode, $theRefCount );
			
			//
			// Load node information.
			//
			else
				$this->traverseStructure(
					$theContainer, $edge[ kTAG_SUBJECT ], $theLanguage,
					$theNode, $theRefCount );
		
		} // Iterating related.
		
	} // traverseEdges.

	 
	/*===================================================================================
	 *	loadNodeElementInfo																*
	 *==================================================================================*/

	/**
	 * Load node element information.
	 *
	 * This method will load the provided container with the information pertaining to the
	 * provided node, the information will be set as follows:
	 *
	 * <ul>
	 *	<li><tt>{@lnk kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: The node label, or the label of
	 *		the element the node references.
	 *	<li><tt>{@lnk kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: The node description, the
	 *		definition of the term, or the description of the tag.
	 *	<li><tt>{@lnk kAPI_PARAM_TAG}</tt>: If the node references a tag, the tag's native
	 *		identifier.
	 * </ul>
	 *
	 * If the node cannot be resolved, the method will raise an exception.
	 *
	 * @param array					$theContainer		Receives information.
	 * @param mixed					$theNode			Node native identifier.
	 * @param string				$theLanguage		Default language.
	 * @param int					$theParent			Parent node native identifier.
	 * @param string				$theRefCount		Reference count tag.
	 *
	 * @access protected
	 * @return Node					Node object.
	 */
	protected function loadNodeElementInfo( &$theContainer, $theNode,
															$theLanguage,
															$theParent = NULL,
															$theRefCount = NULL )
	{
		//
		// Init local storage.
		//
		$collection
			= Node::ResolveCollection(
				Node::ResolveDatabase(
					$this->mWrapper ) );
		$referenced = NULL;
		
		//
		// Resolve node.
		//
		$node
			= $collection
				->matchOne(
					array( kTAG_NID => $theNode ),
					kQUERY_ASSERT | kQUERY_OBJECT );
		
		//
		// Resolve referenced.
		//
		$referenced = $node->getReferenced();
		
		//
		// Set parent node identifier, if there.
		//
		if( $theParent !== NULL )
			$theContainer[ kAPI_PARAM_PARENT_NODE ] = $theParent;
		
		//
		// Set node identifier, if root.
		//
	//	if( $node->NodeType( kTYPE_NODE_ROOT ) !== NULL )
			$theContainer[ kAPI_PARAM_NODE ] = $node[ kTAG_NID ];
		
		//
		// Set label.
		//
		if( $node->offsetExists( kTAG_LABEL ) )
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
				= OntologyObject::SelectLanguageString(
					$node->offsetGet( kTAG_LABEL ), $theLanguage );
		
		else
		{
			if( $referenced->offsetExists( kTAG_LABEL ) )
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_NAME ]
					= OntologyObject::SelectLanguageString(
						$referenced->offsetGet( kTAG_LABEL ), $theLanguage );
		}
		
		//
		// Set description.
		//
		if( $node->offsetExists( kTAG_DESCRIPTION ) )
			$theContainer[ kAPI_PARAM_RESPONSE_FRMT_INFO ]
			= OntologyObject::SelectLanguageString(
				$node->offsetGet( kTAG_DESCRIPTION ), $theLanguage );
		else
		{
			if( $referenced->offsetExists( kTAG_DESCRIPTION ) )
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_INFO ]
					= OntologyObject::SelectLanguageString(
						$referenced->offsetGet( kTAG_DESCRIPTION ), $theLanguage );
			elseif( $referenced->offsetExists( kTAG_DEFINITION ) )
				$theContainer[ kAPI_PARAM_RESPONSE_FRMT_INFO ]
					= OntologyObject::SelectLanguageString(
						$referenced->offsetGet( kTAG_DEFINITION ), $theLanguage );
		}
		
		//
		// Set tag identifier.
		//
		if( $node->offsetExists( kTAG_TAG ) )
		{
			//
			// Set tag reference.
			//
			$theContainer[ kAPI_PARAM_TAG ]
				= $node->offsetGet( kTAG_TAG );
			
			//
			// Set tag data type.
			//
			if( $referenced->offsetExists( kTAG_DATA_TYPE ) )
				$theContainer[ kAPI_PARAM_DATA_TYPE ]
					= $referenced->offsetGet( kTAG_DATA_TYPE );
			
			//
			// Set tag data kind.
			//
			if( $referenced->offsetExists( kTAG_DATA_KIND ) )
				$theContainer[ kAPI_PARAM_DATA_KIND ]
					= $referenced->offsetGet( kTAG_DATA_KIND );
			
			//
			// Set reference count.
			//
			if( $theRefCount !== NULL )
			{
				$theContainer[ kAPI_PARAM_RESPONSE_COUNT ]
					= ( $referenced->offsetExists( $theRefCount ) )
					? $referenced->offsetGet( $theRefCount )
					: 0;
			}
		}
		
		return $node;																// ==>
		
	} // loadNodeElementInfo.

	 
	/*===================================================================================
	 *	getStatistics																	*
	 *==================================================================================*/

	/**
	 * Load statistics information information.
	 *
	 * This method will load the provided container with the information pertaining to the
	 * statistics related to the provided domain in the provided language.
	 *
	 * The container is expected to point to an array.
	 *
	 * @param array					$theContainer		Results container.
	 * @param string				$theLanguage		Default language.
	 * @param string				$theDomain			Statistics domain.
	 * @param string				$theStatistics		Optional statistics code.
	 *
	 * @access protected
	 */
	protected function getStatistics( &$theContainer, $theLanguage,
													  $theDomain,
													  $theStatistics = NULL )
	{
		//
		// Get domain statistics.
		//
		$list = static::GetStatisticsList( $theDomain );
		
		//
		// Handle specific statistics.
		//
		if( $theStatistics !== NULL )
		{
			if( array_key_exists( $theStatistics, $list ) )
				$theContainer = $list[ $theStatistics ];
		}
		
		//
		// Handle all statistics.
		//
		else
			$theContainer = array_values( $list );
		
	} // getStatistics.

	 
	/*===================================================================================
	 *	setStatisticsCriteria															*
	 *==================================================================================*/

	/**
	 * Set statistics criteria.
	 *
	 * This method will update the criteria according to the requested statistics.
	 *
	 * The container is expected to hold the current criteria.
	 *
	 * @param array					$theContainer		Current criteria.
	 * @param string				$theStatistics		Statistics code.
	 * @param string				$theDomain			Statistics domain.
	 *
	 * @access protected
	 */
	protected function setStatisticsCriteria( &$theContainer, $theStatistics, $theDomain )
	{
		//
		// Normalise domain.
		//
		if( is_array( $theDomain ) )
			$theDomain = array_shift( $theDomain );
		
		//
		// Parse by domain.
		//
		switch( $theDomain )
		{
			case kDOMAIN_HH_ASSESSMENT:
				switch( $theStatistics )
				{
					case 'abdh-species-01':
						$struct = $this->mWrapper->getSerial( 'abdh:species', TRUE );
						if( ! array_key_exists( 'abdh:SPECIES_CAT', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
							$theContainer[ 'abdh:SPECIES_CAT' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
								kAPI_RESULT_ENUM_TERM => array( 'abdh:SPECIES_CAT:1' ),
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( ':taxon:epithet', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
							$theContainer[ ':taxon:epithet' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.4b', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.4b', TRUE );
							$theContainer[ 'abdh:Q2.4b' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.6', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.6', TRUE );
							$theContainer[ 'abdh:Q2.6' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.7', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.7', TRUE );
							$theContainer[ 'abdh:Q2.7' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						break;
						
					case 'abdh-species-02':
						$struct = $this->mWrapper->getSerial( 'abdh:species', TRUE );
						if( ! array_key_exists( 'abdh:SPECIES_CAT', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
							$theContainer[ 'abdh:SPECIES_CAT' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
								kAPI_RESULT_ENUM_TERM => array( 'abdh:SPECIES_CAT:1' ),
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( ':taxon:epithet', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
							$theContainer[ ':taxon:epithet' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						break;
						
					case 'abdh-species-03':
						$struct = $this->mWrapper->getSerial( 'abdh:species', TRUE );
						if( ! array_key_exists( 'abdh:SPECIES_CAT', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
							$theContainer[ 'abdh:SPECIES_CAT' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
								kAPI_RESULT_ENUM_TERM => array( 'abdh:SPECIES_CAT:1' ),
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( ':taxon:epithet', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
							$theContainer[ ':taxon:epithet' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.16', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.16', TRUE );
							$theContainer[ 'abdh:Q2.16' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.17', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.17', TRUE );
							$theContainer[ 'abdh:Q2.17' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.18', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.18', TRUE );
							$theContainer[ 'abdh:Q2.18' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.19', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.19', TRUE );
							$theContainer[ 'abdh:Q2.19' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.20', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.20', TRUE );
							$theContainer[ 'abdh:Q2.20' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						break;
						
					case 'abdh-species-04':
						$struct = $this->mWrapper->getSerial( 'abdh:species', TRUE );
						if( ! array_key_exists( 'abdh:SPECIES_CAT', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
							$theContainer[ 'abdh:SPECIES_CAT' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
								kAPI_RESULT_ENUM_TERM => array( 'abdh:SPECIES_CAT:1' ),
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( ':taxon:epithet', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
							$theContainer[ ':taxon:epithet' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.10', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.10', TRUE );
							$theContainer[ 'abdh:Q2.10' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						break;
						
					case 'abdh-species-05':
						$struct = $this->mWrapper->getSerial( 'abdh:species', TRUE );
						if( ! array_key_exists( 'abdh:SPECIES_CAT', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
							$theContainer[ 'abdh:SPECIES_CAT' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
								kAPI_RESULT_ENUM_TERM => array( 'abdh:SPECIES_CAT:1' ),
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( ':taxon:epithet', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
							$theContainer[ ':taxon:epithet' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q2.15a', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q2.15a', TRUE );
							$theContainer[ 'abdh:Q2.15a' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						break;
						
					case 'abdh-species-06':
						$struct = $this->mWrapper->getSerial( 'abdh:species', TRUE );
						if( ! array_key_exists( 'abdh:SPECIES_CAT', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
							$theContainer[ 'abdh:SPECIES_CAT' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
								kAPI_RESULT_ENUM_TERM => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( ':taxon:epithet', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
							$theContainer[ ':taxon:epithet' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						break;
						
					case 'abdh-species-07':
						$struct = $this->mWrapper->getSerial( 'abdh:species', TRUE );
						if( ! array_key_exists( 'abdh:SPECIES_CAT', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:SPECIES_CAT', TRUE );
							$theContainer[ 'abdh:SPECIES_CAT' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_ENUM,
								kAPI_RESULT_ENUM_TERM => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( ':taxon:epithet', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( ':taxon:epithet', TRUE );
							$theContainer[ ':taxon:epithet' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q3', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q3', TRUE );
							$theContainer[ 'abdh:Q3' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						if( ! array_key_exists( 'abdh:Q5a', $theContainer ) )
						{
							$tag = $this->mWrapper->getSerial( 'abdh:Q5a', TRUE );
							$theContainer[ 'abdh:Q5a' ] = array(
								kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_DEFAULT,
								kAPI_PARAM_PATTERN => NULL,
								kAPI_PARAM_OFFSETS => array( "$struct.$tag" ) );
						}
						break;
				}
				break;
		}
		
	} // setStatisticsCriteria.

	 
	/*===================================================================================
	 *	validateMatchUnitsDomain														*
	 *==================================================================================*/

	/**
	 * Validate match units domain operation.
	 *
	 * The duty of this method is to validate a match units operation in which the domain
	 * was provided.
	 *
	 * @param array					$theCriteria		Criteria reference.
	 *
	 * @access protected
	 */
	protected function validateMatchUnitsDomain( &$theCriteria )
	{
		//
		// Assert domain.
		//
		if( ! $this->offsetExists( kAPI_PARAM_DOMAIN ) )
			throw new \Exception(
				"Missing domain parameter." );									// !@! ==>

		//
		// Add domain to criteria.
		//
		$theCriteria[ kTAG_DOMAIN ]
			= $this->buildCriteria(
				kTAG_DOMAIN, $this->offsetGet( kAPI_PARAM_DOMAIN ) );
		
		//
		// Remove unrelated parameters.
		//
		$this->offsetUnset( kAPI_PARAM_GROUP );
		
		//
		// Validate result format.
		//
		$this->validateDataFormat( $theCriteria );
		
	} // validateMatchUnitsDomain.

	 
	/*===================================================================================
	 *	validateDataFormat																*
	 *==================================================================================*/

	/**
	 * Validate data format.
	 *
	 * The duty of this method is to validate the data format provided to a match units
	 * operation.
	 *
	 * @param array					$theCriteria		Criteria reference.
	 *
	 * @access protected
	 */
	protected function validateDataFormat( &$theCriteria )
	{
		//
		// Assert result format.
		//
		if( ! $this->offsetExists( kAPI_PARAM_DATA ) )
			throw new \Exception(
				"Missing results kind parameter." );							// !@! ==>
		
		//
		// Parse data format.
		//
		switch( $this->offsetGet( kAPI_PARAM_DATA ) )
		{
			//
			// Map markers.
			//
			case kAPI_RESULT_ENUM_DATA_MARKER:
			
				//
				// Assert shape offset.
				//
				if( ! $this->offsetExists( kAPI_PARAM_SHAPE_OFFSET ) )
					throw new \Exception(
						"Missing shape offset." );								// !@! ==>
				
				//
				// Normalise limits.
				//
				if( ! $this->offsetExists( kAPI_PAGING_SKIP ) )
					$this->offsetSet( kAPI_PAGING_SKIP, 0 );
				if( (! $this->offsetExists( kAPI_PAGING_LIMIT ))
				 || ($this->offsetGet( kAPI_PAGING_LIMIT ) > kSTANDARDS_MARKERS_MAX) )
					$this->offsetSet( kAPI_PAGING_LIMIT, kSTANDARDS_MARKERS_MAX );
				
				break;
		
			//
			// Data listings.
			//
			case kAPI_RESULT_ENUM_DATA_COLUMN:
			case kAPI_RESULT_ENUM_DATA_RECORD:
			case kAPI_RESULT_ENUM_DATA_FORMAT:
			
				//
				// Normalise limits.
				//
				if( ! $this->offsetExists( kAPI_PAGING_SKIP ) )
					$this->offsetSet( kAPI_PAGING_SKIP, 0 );
				if( (! $this->offsetExists( kAPI_PAGING_LIMIT ))
				 || ($this->offsetGet( kAPI_PAGING_LIMIT ) > kSTANDARDS_UNITS_LIMIT) )
					$this->offsetSet( kAPI_PAGING_LIMIT, kSTANDARDS_UNITS_LIMIT );
				
				break;
		
			//
			// Statistics.
			//
			case kAPI_RESULT_ENUM_DATA_STAT:
			
				//
				// Assert statistics type.
				//
				if( ! $this->offsetExists( kAPI_PARAM_STAT ) )
					throw new \Exception(
						"Missing statistics type." );							// !@! ==>
				
				//
				// Remove limits.
				//
				$this->offsetUnset( kAPI_PAGING_SKIP );
				$this->offsetUnset( kAPI_PAGING_LIMIT );
				
				//
				// Load statistics criteria.
				//
				$this->setStatisticsCriteria( $theCriteria,
											  $this->offsetGet( kAPI_PARAM_STAT ),
											  $this->offsetGet( kAPI_PARAM_DOMAIN ) );
				
				break;
		
			//
			// Unknown.
			//
			default:
				$tmp = $this->offsetGet( kAPI_PARAM_DATA );
				throw new \Exception(
					"Invalid result type [$tmp]." );							// !@! ==>
				
				break;
		
		} // Parsing data format.
		
		//
		// Resolve criteria serial identifiers.
		//
		$criteria = Array();
		foreach( $theCriteria as $key => $value )
		{
			//
			// Convert to serial.
			//
			if( ($key != kAPI_PARAM_FULL_TEXT_OFFSET)
			 && (substr( $key, 0, 1 ) != kTOKEN_TAG_PREFIX) )
				$key = $this->mWrapper->getSerial( $key, TRUE );
			
			//
			// Update criteria.
			//
			$criteria[ $key ] = $value;
		
		} // Conversing native to serial tag identifiers.
		
		//
		// Update criteria.
		//
		$theCriteria = $criteria;
		
	} // validateDataFormat.

	 
	/*===================================================================================
	 *	validateShapeOffset																*
	 *==================================================================================*/

	/**
	 * Validate shape offset.
	 *
	 * The duty of this method is to validate the provided shape offset.
	 *
	 * @param array					$theCriteria		Criteria reference.
	 *
	 * @access protected
	 */
	protected function validateShapeOffset( &$theCriteria )
	{
		//
		// Check shape offset.
		//
		if( $this->offsetExists( kAPI_PARAM_SHAPE_OFFSET ) )
		{
			//
			// Get shape offset.
			//
			$offset = $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET );
			
			//
			// Get serial identifier.
			//
			if( substr( $offset, 0, 1 ) != kTOKEN_TAG_PREFIX )
				$offset = $this->mWrapper->getSerial( $offset, TRUE );
			
			//
			// Check type.
			//
			$shape = $this->mWrapper->getObject( $offset, TRUE );
			if( $shape[ kTAG_DATA_TYPE ] != kTYPE_SHAPE )
				throw new \Exception(
					"Invalid shape offset." );									// !@! ==>
			
			//
			// Update parameter.
			//
			$this->offsetSet( kAPI_PARAM_SHAPE_OFFSET, $offset );
		
		} // Provided shape offset.
		
	} // validateShapeOffset.

	 
	/*===================================================================================
	 *	validateShapeParameter															*
	 *==================================================================================*/

	/**
	 * Validate shape parameter.
	 *
	 * The duty of this method is to validate the provided shape parameter.
	 *
	 * @param array					$theCriteria		Criteria reference.
	 *
	 * @access protected
	 */
	protected function validateShapeParameter( &$theCriteria )
	{
		//
		// Check shape parameter.
		//
		if( $this->offsetExists( kAPI_PARAM_SHAPE ) )
		{
			//
			// Init local storage.
			//
			$shape = $this->offsetGet( kAPI_PARAM_SHAPE );
			$offset = $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET );
			
			//
			// Check shape offset.
			//
			if( $offset === NULL )
				throw new \Exception(
					"Missing shape offset parameter." );						// !@! ==>
		
			//
			// Check shape format.
			//
			$this->validateShape( $shape );
			
			//
			// Add shape to criteria.
			//
			$theCriteria[ (string) $this->offsetGet( kAPI_PARAM_SHAPE_OFFSET ) ]
				= array( kAPI_PARAM_INPUT_TYPE => kAPI_PARAM_INPUT_SHAPE,
						 kAPI_PARAM_SHAPE => $shape );
		
		} // Provided shape offset.
		
	} // validateShapeParameter.

	 
	/*===================================================================================
	 *	extractInvitation																*
	 *==================================================================================*/

	/**
	 * Replace invitation.
	 *
	 * This method will replace the existing user record with the invitation matched by the
	 * provided fingerprint, replacing the user reference in both the results and the
	 * dictionary by the fingerprint.
	 *
	 * This method will also ensure the requesting user is a manager of the inviting user,
	 * if that is not the case the method will raise an exception.
	 *
	 * @param array					$theResults			Results record.
	 * @param array					$theDictionary		Dictionary record.
	 * @param string				$theFingerprint		Invited fingerprint.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means done.
	 */
	protected function extractInvitation( &$theResults, &$theDictionary, $theFingerprint )
	{
		//
		// Parse by format.
		//
		switch( $this->offsetGet( kAPI_PARAM_DATA ) )
		{
			case kAPI_RESULT_ENUM_DATA_RECORD:
				//
				// Check identifier.
				//
				if( array_key_exists( kAPI_DICTIONARY_IDS, $theDictionary )
				 && count( $theDictionary[ kAPI_DICTIONARY_IDS ] ) )
				{
					//
					// Save user identifier.
					//
					$id = $theDictionary[ kAPI_DICTIONARY_IDS ][ 0 ];
					
					//
					// Validate requestor.
					//
					if( $this->offsetExists( kAPI_REQUEST_USER ) )
					{
						//
						// Handle unauthorised.
						//
						if( ! count(
								$this->offsetGet( kAPI_REQUEST_USER )
								->managed( $id, $this->mWrapper ) ) )
							throw new \Exception(
								"Authorisation failure." );							// !@! ==>
				
					} // Has requestor.
			
					//
					// Locate users.
					//
					if( array_key_exists( User::kSEQ_NAME, $theResults ) )
					{
						//
						// Locate user.
						//
						$users = & $theResults[ User::kSEQ_NAME ];
						if( array_key_exists( $id, $users ) )
						{
							//
							// Locate invites.
							//
							$user = & $users[ $id ];
							if( array_key_exists( kTAG_INVITES, $user ) )
							{
								//
								// Locate invitation.
								//
								$invites = & $user[ kTAG_INVITES ];
								for( $key = 0; $key < count( $invites ); $key++ )
								{
									//
									// Match invitation.
									//
									if( $invites[ $key ][ kTAG_ENTITY_PGP_FINGERPRINT ]
											== $theFingerprint )
									{
										//
										// Set invitation in results.
										//
										$users[ $theFingerprint ] = $invites[ $key ];
								
										//
										// Reset old result.
										//
										unset( $users[ $id ] );
							
										//
										// Set dictionary identifier.
										//
										$theDictionary[ kAPI_DICTIONARY_IDS ][ 0 ]
											= $theFingerprint;
								
										return TRUE;								// ==>
								
									} // Found.
						
								} // Locating invitation.
					
							} // Has invites.
				
						} // Has user.
			
					} // Has users.
		
				} // Has identifier.
		
				//
				// Reset parameters.
				//
				$theResults = $theDictionary = Array();
		
				return FALSE;														// ==>
		
			case kAPI_RESULT_ENUM_DATA_FORMAT:
				//
				// Save user identifier.
				//
				$id = key( $theResults );
				
				//
				// Check invites.
				//
				if( count( $theResults )
				 && array_key_exists( kTAG_INVITES, current( $theResults ) )
				 && array_key_exists( kAPI_PARAM_RESPONSE_FRMT_DOCU,
				 					  current( $theResults )[ kTAG_INVITES ] ) )
				{
					//
					// Locate invitation.
					//
					foreach( current( $theResults )[ kTAG_INVITES ]
												   [ kAPI_PARAM_RESPONSE_FRMT_DOCU ]
								as $item )
					{
						//
						// Match invitation.
						//
						if( array_key_exists( kTAG_ENTITY_PGP_FINGERPRINT,
											  $item[ kAPI_PARAM_RESPONSE_FRMT_DOCU ] ) )
						{
							//
							// Set invitation.
							//
							$theResults = $item[ kAPI_PARAM_RESPONSE_FRMT_DOCU ];
							
							return TRUE;											// ==>
						
						} // Matched invitation.
					
					} // Locating invitation.
				
				} // Has invites.
				
				return FALSE;														// ==>
		
		} // Parsing format.

	} // extractInvitation.

	 
	/*===================================================================================
	 *	isManagedUser																	*
	 *==================================================================================*/

	/**
	 * Check whether user is managed
	 *
	 * This method will return <tt>TRUE</tt> if the provided user is managed by the current
	 * referrer, <tt>false</tt> if not and <tt>NULL</tt> if the user doesn't exist.
	 *
	 * @param string				$theFingerprint		User fingerprint.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means done.
	 */
	protected function isManagedUser( $theFingerprint )
	{
		//
		// Check manager.
		//
		if( $this->offsetExists( kAPI_REQUEST_USER ) )
		{
			//
			// Get user.
			//
			$user = User::UserByIdentifier(
				$this->mWrapper, $theFingerprint, kPORTAL_DOMAIN, FALSE );
			if( $user instanceof User )
				return
					count(
						$user->referrers(
							$this->offsetGet( kAPI_REQUEST_USER ) ) );				// ==>
			
			return NULL;															// ==>
		
		} // Provided manager.
		
		return FALSE;																// ==>

	} // extractInvitation.

	 

} // class Service.


?>
