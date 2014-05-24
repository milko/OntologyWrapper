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
 * Request.
 *
 * This tag identifies the results section which holds the eventual service request.
 */
define( "kAPI_RESPONSE_REQUEST",				'request' );

/**
 * Results.
 *
 * This tag identifies the results section which holds the operation result.
 */
define( "kAPI_RESPONSE_RESULTS",				'results' );

/**
 * Dictionary.
 *
 * This tag indicates the results dictionary.
 */
define( "kAPI_RESULTS_DICTIONARY",				'dictionary' );

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
 * File.
 *
 * This tag indicates the source filename.
 */
define( "kAPI_STATUS_FILE",						'file' );

/**
 * Line.
 *
 * This tag indicates the source file line.
 */
define( "kAPI_STATUS_LINE",						'line' );

/**
 * Message.
 *
 * This tag indicates a status message.
 */
define( "kAPI_STATUS_MESSAGE",					'message' );

/**
 * Trace.
 *
 * This tag indicates the exception trace.
 */
define( "kAPI_STATUS_TRACE",					'trace' );

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
 *	DICTIONARY																			*
 *======================================================================================*/

/**
 * Collection.
 *
 * This tag indicates the dictionary collection name.
 */
define( "kAPI_DICTIONARY_COLLECTION",			'collection' );

/**
 * Tags cross reference.
 *
 * This tag indicates the dictionary tags cross references.
 */
define( "kAPI_DICTIONARY_TAGS",					'tags' );

/**
 * IDs list.
 *
 * This tag indicates the dictionary list of identifiers.
 */
define( "kAPI_DICTIONARY_IDS",					'ids' );

/*=======================================================================================
 *	OPERATIONS																			*
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

/**
 * List parameter constants.
 *
 * This tag defines the list parameter constants operation.
 *
 * This operation requires no parameters, it will return the key/value list of all parameter
 * constants.
 */
define( "kAPI_OP_LIST_CONSTANTS",				'list-constants' );

/**
 * List operator parameters.
 *
 * This tag defines the list operator parameters operation.
 *
 * This operation requires no parameters, it will return the list of all operator parameters
 * including the followig information:
 *
 * <ul>
 *	<li><i>index</i>: The key holds the operator key.
 *	<li><i>value</i>: The value is an array structured as follows:
 *	 <ul>
 *		<li><tt>key</tt>: The operator key.
 *		<li><tt>label</tt>: The operator label in the language provided to the service.
 *		<li><tt>title</tt>: The operator display title, in which the <tt>@pattern@</tt>
 *			token should be replaced by the search pattern.
 *		<li><tt>type</tt>: The operator type:
 *		 <ul>
 *			<li><tt>string</tt>: Operators applying to string values.
 *			<li><tt>range</tt>: Operators applying to range values.
 *		 </ul>
 *		<li><tt>main</tt>: A boolean flag which if <tt>TRUE</tt> indicates that the operator
 *			is one of the main choices, meaning that only one of those holding the same type
 *			may be used; if <tt>FALSE</tt> the operator is a flag modifier, meaning that any
 *			number of these may be included in the parameter.
 *		<li><tt>selected</tt>: A boolean flag indicating the default choice.
 *	 </ul>
 * </ul>
 */
define( "kAPI_OP_LIST_OPERATORS",				'list-operators' );

/**
 * List reference count parameters.
 *
 * This tag defines the list reference count parameters operation.
 *
 * This operation requires no parameters, it will return the key/value list of all
 * parameters governing reference count selection. The key represents the paramater flag
 * used to select only those items having a reference count in a specific collection, the
 * value holds the related tag holding the reference count, this value is the tag sequence
 * number.
 */
define( "kAPI_OP_LIST_REF_COUNTS",				'list-ref-counts' );

/**
 * Match tag labels.
 *
 * This tag defines the match tag labels operation.
 *
 * The service will return a list of tag label strings corresponding to the provided
 * pattern, language, operator and limit.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: <em>Pattern</em>. This required parameter
 *		contains the match pattern.
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 *	<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: <em>Operator</em>. This required parameter
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
 *	<li><tt>{@link kAPI_PARAM_REF_COUNT}</tt>: <em>Reference count</em>. This optional
 *		parameter may be provided as a string or an array, it will add a filter selecting
 *		only those tags which have values in the provided collection(s):
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_TAG}</tt>: <em>Tag references</em>. Select only
 *			those tags which have their {@link kTAG_TAG_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_TERM}</tt>: <em>Term references</em>. Select
 *			only those tags which have their {@link kTAG_TERM_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_NODE}</tt>: <em>Node references</em>. Select
 *			only those tags which have their {@link kTAG_NODE_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_EDGE}</tt>: <em>Edge references</em>. Select
 *			only those tags which have their {@link kTAG_EDGE_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_ENTITY}</tt>: <em>Entity references</em>.
 *			Select only those tags which have their {@link kTAG_ENTITY_COUNT} greater than
 *			zero.
 *	 </ul>
 *		The filter will be chained in <tt>AND</tt>.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This required parameter
 *		indicates the maximum number of elements to be returned. If omitted, it will be
 *		set to the default constant {@link kSTANDARDS_STRINGS_LIMIT}.
 * </ul>
 */
define( "kAPI_OP_MATCH_TAG_LABELS",				'matchTagLabels' );

/**
 * Match term labels.
 *
 * This tag defines the match term labels operation.
 *
 * The service will return a list of term label strings corresponding to the provided
 * pattern, language, operator and limit.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: <em>Pattern</em>. This required parameter
 *		contains the match pattern.
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 *	<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: <em>Operator</em>. This required parameter
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
 *	<li><tt>{@link kAPI_PARAM_REF_COUNT}</tt>: <em>Reference count</em>. This optional
 *		parameter may be provided as a string or an array, it will add a filter selecting
 *		only those tags which have values in the provided collection(s):
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_TAG}</tt>: <em>Tag references</em>. Select only
 *			those tags which have their {@link kTAG_TAG_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_TERM}</tt>: <em>Term references</em>. Select
 *			only those tags which have their {@link kTAG_TERM_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_NODE}</tt>: <em>Node references</em>. Select
 *			only those tags which have their {@link kTAG_NODE_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_EDGE}</tt>: <em>Edge references</em>. Select
 *			only those tags which have their {@link kTAG_EDGE_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_ENTITY}</tt>: <em>Entity references</em>.
 *			Select only those tags which have their {@link kTAG_ENTITY_COUNT} greater than
 *			zero.
 *	 </ul>
 *		The filter will be chained in <tt>AND</tt>.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This required parameter
 *		indicates the maximum number of elements to be returned. If omitted, it will be
 *		set to the default constant {@link kSTANDARDS_STRINGS_LIMIT}.
 * </ul>
 */
define( "kAPI_OP_MATCH_TERM_LABELS",			'matchTermLabels' );

/**
 * Match tag by label.
 *
 * This tag defines the match tag by label operation.
 *
 * The service will return a list of tag objects whose label matches the provided pattern,
 * language, operator and limit.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: <em>Pattern</em>. This required parameter
 *		contains the match pattern.
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 *	<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: <em>Operator</em>. This required parameter
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
 *	<li><tt>{@link kAPI_PARAM_REF_COUNT}</tt>: <em>Reference count</em>. This optional
 *		parameter may be provided as a string or an array, it will add a filter selecting
 *		only those tags which have values in the provided collection(s):
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_TAG}</tt>: <em>Tag references</em>. Select only
 *			those tags which have their {@link kTAG_TAG_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_TERM}</tt>: <em>Term references</em>. Select
 *			only those tags which have their {@link kTAG_TERM_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_NODE}</tt>: <em>Node references</em>. Select
 *			only those tags which have their {@link kTAG_NODE_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_EDGE}</tt>: <em>Edge references</em>. Select
 *			only those tags which have their {@link kTAG_EDGE_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_ENTITY}</tt>: <em>Entity references</em>.
 *			Select only those tags which have their {@link kTAG_ENTITY_COUNT} greater than
 *			zero.
 *	 </ul>
 *		The filter will be chained in <tt>AND</tt>.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This required parameter
 *		indicates the maximum number of elements to be returned. If omitted, it will be
 *		set to the default constant {@link kSTANDARDS_STRINGS_LIMIT}.
 * </ul>
 */
define( "kAPI_OP_MATCH_TAG_BY_LABEL",			'matchTagByLabel' );

/**
 * Match term by label.
 *
 * This tag defines the match term by label operation.
 *
 * The service will return a list of term objects whose label matches the provided pattern,
 * language, operator and limit.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: <em>Pattern</em>. This required parameter
 *		contains the match pattern.
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 *	<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: <em>Operator</em>. This required parameter
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
 *	<li><tt>{@link kAPI_PARAM_REF_COUNT}</tt>: <em>Reference count</em>. This optional
 *		parameter may be provided as a string or an array, it will add a filter selecting
 *		only those tags which have values in the provided collection(s):
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_TAG}</tt>: <em>Tag references</em>. Select only
 *			those tags which have their {@link kTAG_TAG_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_TERM}</tt>: <em>Term references</em>. Select
 *			only those tags which have their {@link kTAG_TERM_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_NODE}</tt>: <em>Node references</em>. Select
 *			only those tags which have their {@link kTAG_NODE_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_EDGE}</tt>: <em>Edge references</em>. Select
 *			only those tags which have their {@link kTAG_EDGE_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_ENTITY}</tt>: <em>Entity references</em>.
 *			Select only those tags which have their {@link kTAG_ENTITY_COUNT} greater than
 *			zero.
 *	 </ul>
 *		The filter will be chained in <tt>AND</tt>.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This required parameter
 *		indicates the maximum number of elements to be returned. If omitted, it will be
 *		set to the default constant {@link kSTANDARDS_STRINGS_LIMIT}.
 * </ul>
 */
define( "kAPI_OP_MATCH_TERM_BY_LABEL",			'matchTermByLabel' );

/**
 * Get tag enumerations.
 *
 * This tag defines the get tag enumerations operation.
 *
 * The service will return the enumerated set related to the provided tag.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_TAG}</tt>: <em>Tag</em>. This required parameter can either be
 *		an integer referencing the tag sequence number or a string referencing the tag
 *		native identifier.
 *	<li><tt>{@link kAPI_PARAM_RECURSE}</tt>: <em>Recurse nested enumerations</em>. This
 *		flag parameter indicates that the response should contain all nested levels of the
 *		enumerated set, if this parameter is <tt>TRUE</tt>, the {@link kAPI_PAGING_LIMIT}
 *		parameter will be ignored.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This parameter is required and
 *		considered only if the {@link kAPI_PARAM_RECURSE} parameter is not provided: it
 *		indicates the maximum number of elements to be returned; if omitted, it will be
 *		set to the default constant {@link kSTANDARDS_ENUMS_LIMIT}.
 * </ul>
 *
 * The result will be returned in the {@link kAPI_RESPONSE_RESULTS} section of the response,
 * it will be an array whose elements are structured as follows:
 *
 * <ul>
 *	<li><tt>{@link kAPI_RESULT_ENUM_TERM}</tt>: The enumerated value identifier.
 *	<li><tt>{@link kAPI_RESULT_ENUM_NODE}</tt>: The enumerated value node identifier.
 *	<li><tt>{@link kAPI_RESULT_ENUM_LABEL}</tt>: The enumerated value label.
 *	<li><tt>{@link kAPI_RESULT_ENUM_DESCR}</tt>: The enumerated value description.
 *	<li><tt>{@link kAPI_RESULT_ENUM_KIND}</tt>: The enumerated value kind.
 *	<li><tt>{@link kAPI_RESULT_ENUM_CHILDREN}</tt>: If the current enumeration has
 *		sub-elements, this item will contain the elements array.
 * </ul>
 */
define( "kAPI_OP_GET_TAG_ENUMERATIONS",			'getTagEnumerations' );

/**
 * Get node enumerations.
 *
 * This tag defines the get node enumerations operation.
 *
 * The service will return the enumerated set related to the provided node.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_NODE}</tt>: <em>Node</em>. This required parameter is an
 *		integer referencing the node native identifier.
 *	<li><tt>{@link kAPI_PARAM_RECURSE}</tt>: <em>Recurse nested enumerations</em>. This
 *		flag parameter indicates that the response should contain all nested levels of the
 *		enumerated set, if this parameter is <tt>TRUE</tt>, the {@link kAPI_PAGING_LIMIT}
 *		parameter will be ignored.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This parameter is required and
 *		considered only if the {@link kAPI_PARAM_RECURSE} parameter is not provided: it
 *		indicates the maximum number of elements to be returned; if omitted, it will be
 *		set to the default constant {@link kSTANDARDS_ENUMS_LIMIT}.
 * </ul>
 *
 * The result will be returned in the {@link kAPI_RESPONSE_RESULTS} section of the response,
 * it will be an array whose elements are structured as follows:
 *
 * <ul>
 *	<li><tt>{@link kAPI_RESULT_ENUM_TERM}</tt>: The enumerated value identifier.
 *	<li><tt>{@link kAPI_RESULT_ENUM_NODE}</tt>: The enumerated value node identifier.
 *	<li><tt>{@link kAPI_RESULT_ENUM_LABEL}</tt>: The enumerated value label.
 *	<li><tt>{@link kAPI_RESULT_ENUM_DESCR}</tt>: The enumerated value description.
 *	<li><tt>{@link kAPI_RESULT_ENUM_KIND}</tt>: The enumerated value kind.
 *	<li><tt>{@link kAPI_RESULT_ENUM_CHILDREN}</tt>: If the current enumeration has
 *		sub-elements, this item will contain the elements array.
 * </ul>
 */
define( "kAPI_OP_GET_NODE_ENUMERATIONS",		'getNodeEnumerations' );

/*=======================================================================================
 *	REQUEST PARAMETERS																	*
 *======================================================================================*/

/**
 * Pattern (string).
 *
 * This tag defines the requested pattern.
 *
 * This parameter represents a string match pattern, it is used to match strings.
 */
define( "kAPI_PARAM_PATTERN",					'pattern' );

/**
 * Reference count (string/array).
 *
 * This tag defines the requested reference count collection.
 *
 * This parameter is a flag that indicates the collection or collections in which the
 * requested tag must have values. The parameter may either be a string or an array from
 * the following enumerated set:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_TAG}</tt>: Tags.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_TERM}</tt>: Terms.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_NODE}</tt>: Nodes.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_EDGE}</tt>: Edges.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: Units.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_ENTITY}</tt>: Entities.
 * </ul>
 *
 * The service will only select those tags which have values in the provided collections.
 */
define( "kAPI_PARAM_REF_COUNT",					'ref-count' );

/**
 * Tag (string/int).
 *
 * This tag defines the requested tag.
 *
 * This parameter represents either an integer referencing a tag sequence number or a string
 * referencing a tag native identifier.
 */
define( "kAPI_PARAM_TAG",						'tag' );

/**
 * Node (int).
 *
 * This tag defines the requested node.
 *
 * This parameter represents an integer referencing a node native identifier.
 */
define( "kAPI_PARAM_NODE",						'node' );

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

/*=======================================================================================
 *	GENERIC REQUEST FLAG PARAMETERS														*
 *======================================================================================*/

/**
 * Log request (boolean).
 *
 * This parameter determines whether the request should be returned by the service.
 *
 * If the parameter is <tt>TRUE</tt>, the service will return the request in the
 * {@link kAPI_RESPONSE_REQUEST} section of the response.
 *
 * If the parameter is <tt>FALSE</tt> or omitted, the request will not be returned.
 */
define( "kAPI_PARAM_LOG_REQUEST",				'log-request' );

/**
 * Trace (boolean).
 *
 * This parameter determines whether eventual errors should feature the exception trace.
 *
 * If the parameter is <tt>TRUE</tt>, the error will include the trace.
 */
define( "kAPI_PARAM_LOG_TRACE",					'log-trace' );

/**
 * Recurse (boolean).
 *
 * This parameter determines whether the request should recursively be applied to nested
 * levels.
 *
 * This parameter is relevant only to those services which need to traverse structures, if
 * the parameter is <tt>TRUE</tt>, the service will traverse all nested levels, returning
 * the tree of results; if the parameter is <tt>FALSE</tt>, the service will only traverse
 * the root level of the structure.
 */
define( "kAPI_PARAM_RECURSE",					'recurse' );

/*=======================================================================================
 *	ENUMERATION RESULT PARAMETERS														*
 *======================================================================================*/

/**
 * Term (string).
 *
 * This tag is used when returning an enumeration element, it defines the element's term.
 */
define( "kAPI_RESULT_ENUM_TERM",				'term' );

/**
 * Node (int).
 *
 * This tag is used when returning an enumeration element, it defines the element's node.
 */
define( "kAPI_RESULT_ENUM_NODE",				'node' );

/**
 * Label (string).
 *
 * This tag is used when returning an enumeration element, it defines the element's label.
 */
define( "kAPI_RESULT_ENUM_LABEL",				'label' );

/**
 * Description (string).
 *
 * This tag is used when returning an enumeration element, it defines the element's
 * definition or description.
 */
define( "kAPI_RESULT_ENUM_DESCR",				'description' );

/**
 * Value (boolean).
 *
 * This tag is used when returning an enumeration element, if <tt>TRUE</tt>, the element can
 * be considered as an enumerated value, if not, the element is a category.
 */
define( "kAPI_RESULT_ENUM_VALUE",				'value' );

/**
 * Children (array).
 *
 * This tag is used when returning an enumeration element, it defines the element's subset,
 * that is, it will contain all the elements whose parent is the current element.
 */
define( "kAPI_RESULT_ENUM_CHILDREN",			'children' );

/*=======================================================================================
 *	COLLECTION REFERENCE ENUMERATED SET													*
 *======================================================================================*/

/**
 * Tags (string).
 *
 * This parameter indicates a reference to the tags collection.
 */
define( "kAPI_PARAM_COLLECTION_TAG",			'_tags' );

/**
 * Terms (string).
 *
 * This parameter indicates a reference to the terms collection.
 */
define( "kAPI_PARAM_COLLECTION_TERM",			'_terms' );

/**
 * Nodes (string).
 *
 * This parameter indicates a reference to the nodes collection.
 */
define( "kAPI_PARAM_COLLECTION_NODE",			'_nodes' );

/**
 * Edges (string).
 *
 * This parameter indicates a reference to the edges collection.
 */
define( "kAPI_PARAM_COLLECTION_EDGE",			'_edges' );

/**
 * Units (string).
 *
 * This parameter indicates a reference to the units collection.
 */
define( "kAPI_PARAM_COLLECTION_UNIT",			'_units' );

/**
 * Entities (string).
 *
 * This parameter indicates a reference to the entities collection.
 */
define( "kAPI_PARAM_COLLECTION_ENTITY",			'_entities' );


?>
