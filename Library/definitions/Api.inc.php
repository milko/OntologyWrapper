<?php

/*=======================================================================================
 *																						*
 *										Api.inc.php										*
 *																						*
 *======================================================================================*/
 
/**
 * Service application interface.
 *
 * This file contains the definitions for the service application interface, 
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 04/05/2014
 */

/*=======================================================================================
 *	REQUEST																				*
 *======================================================================================*/

/**
 * Operation.
 *
 * This tag identifies the service operation.
 */
define( "kAPI_REQUEST_OPERATION",				'op' );

/**
 * Language.
 *
 * This tag identifies the service default language.
 */
define( "kAPI_REQUEST_LANGUAGE",				'lang' );

/**
 * Parameters.
 *
 * This tag identifies the service parameters.
 */
define( "kAPI_REQUEST_PARAMETERS",				'param' );

/*=======================================================================================
 *	RESPONSE																			*
 *======================================================================================*/

/**
 * Status.
 *
 * This tag identifies the status section which provides information on the outcome of the
 * operation, which includes the eventual error message if the operation failed.
 */
define( "kAPI_RESPONSE_STATUS",					'status' );

/**
 * Paging.
 *
 * This tag identifies the paging section which provides information on the number of
 * affected records, skipped records, the maximum number of returned records and the actual
 * number of returned records.
 */
define( "kAPI_RESPONSE_PAGING",					'paging' );

/**
 * Results.
 *
 * This tag identifies the results section which holds the operation result.
 */
define( "kAPI_RESPONSE_RESULTS",				'results' );

/*=======================================================================================
 *	STATUS																				*
 *======================================================================================*/

/**
 * State.
 *
 * This tag provides a general indicatrion on the outcome of the operation, it can take two
 * values:
 *
 * <ul>
 *	<li><tt>{@link kAPI_STATE_IDLE}</tt>: This indicates that the operation has not yet
 *		started.
 *	<li><tt>{@link kAPI_STATE_OK}</tt>: This indicates that the operation was successful.
 *	<li><tt>{@link kAPI_STATE_ERROR}</tt>: This indicates that the operation failed.
 * </ul>
 */
define( "kAPI_STATUS_STATE",					'state' );

/**
 * Code.
 *
 * This tag indicates a status code.
 */
define( "kAPI_STATUS_CODE",						'code' );

/**
 * Message.
 *
 * This tag indicates a status message.
 */
define( "kAPI_STATUS_MESSAGE",					'message' );

/*=======================================================================================
 *	STATE																				*
 *======================================================================================*/

/**
 * Idle.
 *
 * Idle state.
 *
 * The service has not yet parsed the request.
 */
define( "kAPI_STATE_IDLE",						'idle' );

/**
 * OK.
 *
 * Success state.
 *
 * The service has no errors.
 */
define( "kAPI_STATE_OK",						'ok' );

/**
 * Error.
 *
 * Error state.
 *
 * The service encountered an error.
 */
define( "kAPI_STATE_ERROR",						'error' );

/*=======================================================================================
 *	PAGING																				*
 *======================================================================================*/

/**
 * Skip.
 *
 * This tag indicates the number of skipped records.
 */
define( "kAPI_PAGING_SKIP",						'skipped' );

/**
 * Limit.
 *
 * This tag indicates the maximum number of returned records.
 */
define( "kAPI_PAGING_LIMIT",					'limit' );

/**
 * Actual.
 *
 * This tag indicates the actual number of returned records.
 */
define( "kAPI_PAGING_ACTUAL",					'actual' );

/**
 * Affected.
 *
 * This tag indicates the total number of affected records.
 */
define( "kAPI_PAGING_AFFECTED",					'affected' );

/*=======================================================================================
 *	GENERIC OPERATIONS																	*
 *======================================================================================*/

/**
 * Ping.
 *
 * This tag defines the ping operation.
 *
 * This operation requires no parameters, it will return the string "pong" in the status
 * message field.
 */
define( "kAPI_OP_PING",							'ping' );

/*=======================================================================================
 *	LIST OPERATIONS																		*
 *======================================================================================*/

/**
 * String list.
 *
 * This tag defines the string list operation.
 *
 * This operation will return a list of strings corresponding to the provided target
 * collection, property, match pattern, language, operator and limit.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{ @link kAPI_PARAM_COLLECTION}</tt>: <em>Collection</em>. This required
 *		parameter is an enumerated set that determines which collection will be searched, it
 *		features the following choices:
 *	 <ul>
 *		<li><tt>tag</tt>: Tag objects.
 *		<li><tt>term</tt>: Term objects.
 *		<li><tt>node</tt>: Node objects.
 *		<li><tt>edge</tt>: Edge objects.
 *		<li><tt>unit</tt>: All unit class of objects.
 *		<li><tt>entity</tt>: All entity class of objects.
 *	 </ul>
 *	<li><tt>{ @link kAPI_PARAM_PROPERTY}</tt>: <em>Property</em>. This required parameter
 *		represents the property or field to be searched in the provided collection. It can
 *		either hold a tag's native identifier or sequence number.
 *	<li><tt>{ @link kAPI_PARAM_PATTERN}</tt>: <em>Pattern</em>. This required parameter
 *		contains the match pattern.
 *	<li><tt>{ @link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 *	<li><tt>{ @link kAPI_PARAM_OPERATOR}</tt>: <em>Operator</em>. This required parameter
 *		indicates what kind of match should be applied to the searched strings, it is an
 *		array that must contain one of the following:
 *	 <ul>
 *		<li><tt>{@link kOPERATOR_EQUAL}</tt>: <em>Equality</em>. The two match terms must be
 *			equal.
 *		<li><tt>{@link kOPERATOR_EQUAL_NOT}</tt>: <em>Inequality</em>. The two match terms
 *			must be different.
 *		<li><tt>{@link kOPERATOR_PREFIX}</tt>: <em>Prefix</em>. The target string must start
 *			with the query pattern.
 *		<li><tt>{@link kOPERATOR_CONTAINS}</tt>: <em>Contains</em>. The target string must
 *			contain the query pattern.
 *		<li><tt>{@link kOPERATOR_SUFFIX}</tt>: <em>Suffix</em>. The target string must end
 *			with the query pattern.
 *		<li><tt>{@link kOPERATOR_REGEX}</tt>: <em>Regular expression</em>. The parameter is
 *			expected to contain a regular expression string.
 *	 </ul>
 *		and any of the following:
 *	 <ul>
 *		<li><tt>{@link kOPERATOR_NOCASE}</tt>: <em>Case insensitive</em>. If provided, it
 *			means that the matching operation is case and accent insensitive.
 *	 </ul>
 *	<li><tt>{ @link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This required parameter
 *		indicates the maximum number of elements to be returned. If omitted, it will be
 *		set to the default constant {@link kSTANDARDS_STRINGS_LIMIT}.
 * </ul>
 */
define( "kAPI_OP_STRING_LIST",					'string-list' );

/*=======================================================================================
 *	PARAMETERS																			*
 *======================================================================================*/

/**
 * Collection (string).
 *
 * This tag defines the requested collection.
 *
 * This parameter is used to determine which object class and collection we want to operate
 * on. For instance, if we are reauesting a list of labels corresponding to an autocomplete
 * string, this parameter will determine which collection will be used for searching.
 *
 * This parameter is an enumerated set:
 *
 * <ul>
 *	<li><tt>tag</tt>: Tag objects.
 *	<li><tt>term</tt>: Term objects.
 *	<li><tt>node</tt>: Node objects.
 *	<li><tt>edge</tt>: Edge objects.
 *	<li><tt>unit</tt>: All unit class of objects.
 *	<li><tt>entity</tt>: All entity class of objects.
 * </ul>
 */
define( "kAPI_PARAM_COLLECTION",				'collection' );

/**
 * Property (string).
 *
 * This tag defines the requested property.
 *
 * This parameter represents the target property, it should either be a tag native
 * identifier, or sequence number.
 */
define( "kAPI_PARAM_PROPERTY",					'property' );

/**
 * Pattern (string).
 *
 * This tag defines the requested pattern.
 *
 * This parameter represents a string match pattern, it is used to match strings.
 */
define( "kAPI_PARAM_PATTERN",					'pattern' );

/**
 * Match operator (strings array).
 *
 * This tag defines the requested string match operator.
 *
 * These are the required choices:
 *
 * <ul>
 *	<li><tt>{@link kOPERATOR_EQUAL}</tt>: <em>Equality</em>. The two match terms must be
 *		equal.
 *	<li><tt>{@link kOPERATOR_EQUAL_NOT}</tt>: <em>Inequality</em>. The two match terms must
 *		be different.
 *	<li><tt>{@link kOPERATOR_PREFIX}</tt>: <em>Prefix</em>. The target string must start
 *		with the query pattern.
 *	<li><tt>{@link kOPERATOR_CONTAINS}</tt>: <em>Contains</em>. The target string must
 *		contain the query pattern.
 *	<li><tt>{@link kOPERATOR_SUFFIX}</tt>: <em>Suffix</em>. The target string must end with
 *		the query pattern.
 *	<li><tt>{@link kOPERATOR_REGEX}</tt>: <em>Regular expression</em>. The parameter is
 *		expected to contain a regular expression string.
 * </ul>
 *
 * The parameter must be an array which contains one of the above choices and optionally any
 * number of modifiers from the following list:
 *
 * <ul>
 *	<li><tt>{@link kOPERATOR_NOCASE}</tt>: <em>Case insensitive</em>. If provided, it means
 *		that the matching operation is case and accent insensitive.
 * </ul>
 */
define( "kAPI_PARAM_OPERATOR",					'operator' );


?>
