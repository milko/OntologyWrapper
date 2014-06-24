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
 * This tag identifies the service request parameters.
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
 *
 * This block may contain the following elements:
 *
 * <ul>
 *	<li><tt>{@link kAPI_STATUS_STATE}</tt>: This element indicates the operation state.
 *	<li><tt>{@link kAPI_STATUS_CODE}</tt>: This element will hold the eventual status code.
 *	<li><tt>{@link kAPI_STATUS_CODE}</tt>: This element will hold the eventual status
 *		message.
 *	<li><tt>{@link kAPI_STATUS_FILE}</tt>: This element will hold the eventual exception
 *		source file path.
 *	<li><tt>{@link kAPI_STATUS_FILE}</tt>: This element will hold the eventual exception
 *		file line.
 *	<li><tt>{@link kAPI_STATUS_TRACE}</tt>: This element will hold the eventual exception
 *		trace.
 * </ul>
 */
define( "kAPI_RESPONSE_STATUS",					'status' );

/**
 * Paging.
 *
 * This tag identifies the paging section which provides information on the number of
 * affected records, skipped records, the maximum number of returned records and the actual
 * number of returned records.
 *
 * This block may contain the following elements:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PAGING_SKIP}</tt>: Paging start.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: Paging limit.
 *	<li><tt>{@link kAPI_PAGING_ACTUAL}</tt>: Number of actual returned records.
 *	<li><tt>{@link kAPI_PAGING_AFFECTED}</tt>: Number of affected records.
 * </ul>
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
 *
 * This block may contain the following elements:
 *
 * <ul>
 *	<li><tt>{@link kAPI_DICTIONARY_COLLECTION}</tt>: The working collection name.
 *	<li><tt>{@link kAPI_DICTIONARY_TAGS}</tt>: Tags cross reference indexed by sequence
 *		number, having the tag identifier as value.
 *	<li><tt>{@link kAPI_DICTIONARY_IDS}</tt>: List of returned identifiers.
 *	<li><tt>{@link kAPI_DICTIONARY_LIST_COLS}</tt>: List of table column header tags.
 *	<li><tt>{@link kAPI_DICTIONARY_CLUSTER}</tt>: List of clustered identifiers: an array
 *		indexed by cluster identifier (a term identifier), with as values the tag
 *		identifiers, featured in the {@link kAPI_DICTIONARY_IDS} list, belonging to that
 *		cluster.
 * </ul>
 */
define( "kAPI_RESULTS_DICTIONARY",				'dictionary' );

/*=======================================================================================
 *	STATUS																				*
 *======================================================================================*/

/**
 * State.
 *
 * This tag provides a general indication on the outcome of the operation, it can take two
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
 * Reference count offset.
 *
 * This tag indicates the collection reference count offset.
 */
define( "kAPI_DICTIONARY_REF_COUNT",			'ref-count' );

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

/**
 * Table column offsets.
 *
 * This tag indicates the list of table column tag references.
 */
define( "kAPI_DICTIONARY_LIST_COLS",			'cols' );

/**
 * Cluster.
 *
 * This tag indicates the dictionary cluster.
 */
define( "kAPI_DICTIONARY_CLUSTER",				'cluster' );

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
 *	<li><tt>{@link kAPI_PARAM_REF_COUNT}</tt>: <em>Reference count</em>. This optional
 *		parameter detewrmines what value the result parameter {@link kAPI_RESULT_ENUM_VALUE}
 *		will hold: if provided, the parameter will hold the record count for that specific
 *		enumeration in the collection referenced by the value of
 *		{@link kAPI_PARAM_REF_COUNT}; if not provided, the {@link kAPI_RESULT_ENUM_VALUE}
 *		hold <tt>TRUE</tt> if the enumeration is selectable. The value of this parameter
 *		is a reference to the collection in which nthe enumeration is featured:
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
 *	<li><tt>{@link kAPI_RESULT_ENUM_VALUE}</tt>: The selection flag or values count.
 *	<li><tt>{@link kAPI_RESULT_ENUM_LABEL}</tt>: The enumerated value label.
 *	<li><tt>{@link kAPI_RESULT_ENUM_DESCR}</tt>: The enumerated value description.
 *	<li><tt>{@link kAPI_RESULT_ENUM_KIND}</tt>: The enumerated value kind.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_CHILDREN}</tt>: If the current enumeration has
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
 *	<li><tt>{@link kAPI_PARAM_REF_COUNT}</tt>: <em>Reference count</em>. This optional
 *		parameter detewrmines what value the result parameter {@link kAPI_RESULT_ENUM_VALUE}
 *		will hold: if provided, the parameter will hold the record count for that specific
 *		enumeration in the collection referenced by the value of
 *		{@link kAPI_PARAM_REF_COUNT}; if not provided, the {@link kAPI_RESULT_ENUM_VALUE}
 *		hold <tt>TRUE</tt> if the enumeration is selectable. The value of this parameter
 *		is a reference to the collection in which nthe enumeration is featured:
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
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_CHILDREN}</tt>: If the current enumeration has
 *		sub-elements, this item will contain the elements array.
 * </ul>
 */
define( "kAPI_OP_GET_NODE_ENUMERATIONS",		'getNodeEnumerations' );

/**
 * Match units.
 *
 * This tag defines the match units operation.
 *
 * The service will use the provided criteria to apply a filter to the units collection and
 * return information based on the provided parameters.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 *	<li><tt>{@link kAPI_PARAM_CRITERIA}</tt>: <em>Criteria</em>. This required parameter
 *		holds the search criteria, it is an array of elements structured as follows:
 *	 <ul>
 *		<li><em>index</em>: The index of the item must contain the tag's native identifier.
 *		<li><em>value</em>: The value of the item will contain the search criteria for the
 *			tag provided in the index, the following parameters are expected:
 *		 <ul>
 *			<li><tt>{@link kAPI_PARAM_INPUT_TYPE}</tt>: <em>Input type</em>. This string
 *				identifies the kind of form input control, the value is taken from an
 *				enumerated set and it determines which other items are to be expected:
 *			 <ul>
 *				<li><tt>{@link kAPI_PARAM_INPUT_STRING}</tt>: String search:
 *				 <ul>
 *					<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: The search pattern strings list.
 *					<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: <em>Operator</em>. This
 *						required parameter indicates what kind of match should be applied to
 *						the matched strings, it is an array that must contain one of the
 *						following:
 *					 <ul>
 *						<li><tt>{@link kOPERATOR_EQUAL}</tt>: <em>Equality</em>. The two
 *							match terms must be equal.
 *						<li><tt>{@link kOPERATOR_EQUAL_NOT}</tt>: <em>Inequality</em>. The
 *							two match terms must be different.
 *						<li><tt>{@link kOPERATOR_PREFIX}</tt>: <em>Prefix</em>. The target
 *							string must start with the pattern.
 *						<li><tt>{@link kOPERATOR_CONTAINS}</tt>: <em>Contains</em>. The
 *							target string must contain the pattern.
 *						<li><tt>{@link kOPERATOR_SUFFIX}</tt>: <em>Suffix</em>. The target
 *							string must end with the pattern.
 *						<li><tt>{@link kOPERATOR_REGEX}</tt>: <em>Regular expression</em>.
 *							The parameter is expected to contain a regular expression
 *							string.
 *					 </ul>
 *						and any of the following:
 *					 <ul>
 *						<li><tt>{@link kOPERATOR_NOCASE}</tt>: <em>Case insensitive</em>. If
 *							provided, it means that the matching operation is case and
 *							accent insensitive.
 *					 </ul>
 *				 </ul>
 *				<li><tt>{@link kAPI_PARAM_INPUT_RANGE}</tt>: Value range search:
 *				 <ul>
 *					<li><tt>{@link kAPI_PARAM_RANGE_MIN}</tt>: The minimum value of the
 *						range.
 *					<li><tt>{@link kAPI_PARAM_RANGE_MAX}</tt>: The maximum value of the
 *						range.
 *					<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: <em>Operator</em>. This
 *						parameter indicates what kind of range match should be applied, it
 *						is a string that can take one of the following values:
 *					 <ul>
 *						<li><tt>{@link kOPERATOR_IRANGE}</tt>: <em>Range inclusive</em>. The
 *							provided minimum and maximum are included in the matched range.
 *						<li><tt>{@link kOPERATOR_ERANGE}</tt>: <em>Range exclusive</em>. The
 *							provided minimum and maximum are excluded from the matched range.
 *					 </ul>
 *						If the parameter is omitted, the {@link kOPERATOR_IRANGE} operator
 *						is used by default.
 *				 </ul>
 *				<li><tt>{@link kAPI_PARAM_INPUT_ENUM}</tt>: Enumerated set search:
 *				 <ul>
 *					<li><tt>{@link kAPI_RESULT_ENUM_TERM}</tt>: An array containing the list
 *						of term native identifiers corresponding to the enumerated values to
 *						be matched.
 *				 </ul>
 *			 </ul>
 *			<li><tt>{@link kAPI_PARAM_OFFSETS}</tt>: <em>Selected offsets</em>. If this parameter
 *				is omitted, the selection criteria will apply to all offsets in which the
 *				current tag is used, this parameter can be used to provide a specific set of
 *				offsets.
 *		 </ul>
 *	 </ul>
 *	<li><tt>{@link kAPI_PARAM_DOMAIN}</tt>: <em>Results domain</em>. If this parameter is
 *		provided, the service will return the results of the type provided in this
 *		parameter, if it is not provided, the next parameter is required. If this parameter
 *		is provided, the next parameter will be ignored; the results will be clustered.
 *	<li><tt>{@link kAPI_PARAM_DATA}</tt>: <em>Results format</em>. This parameter must be
 *		provided if the {@link kAPI_PARAM_DOMAIN} parameter was provided, it indicates what
 *		kind of data the service should return:
 *	 <ul>
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: The service will return a
 *			clustered record set.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_FORMAT}</tt>: The service will return a
 *			formatted record set.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_MARKER}</tt>: The service will return a set of
 *			geographic markers, each element will contain the unit {@link kTAG_NID} and the
 *			value contained in the offset provided in the {@link kAPI_PARAM_SHAPE_OFFSET}.
 *	 </ul>
 *	<li><tt>{@link kAPI_PARAM_GROUP}</tt>: <em>Group results</em>. This parameter must be
 *		provided if the {@link kAPI_PARAM_DOMAIN} is omitted: the value may be a string or
 *		an array of strings representing the tag native identifiers or sequence numbers by
 *		which the results should be grouped. The result will be a nested array containing
 *		the distinct values of the provided tags as keys and the record count as values.
 *		If the parameter is an array, the results will be clustered in the order in which
 *		the tags are provided, only the leaf elements will contain the record counts.
 *		<em>Note that the leaf element will always be the {@link kTAG_DOMAIN} property, if
 *		missing from the provided parametrer it will be added</em>.
 *	<li><tt>{@link kAPI_PARAM_SHAPE}</tt>: <em>Geographic shape</em>. If this parameter is
 *		provided, the service will add the provided shape to the filter, the parameter is
 *		structured as a GeoJson shape of which the following types are supported:
 *	 <ul>
 *		<li><tt>Point</tt>: The service will select the first 100 records (or less with the
 *			limits parameter) closest to the provided point and within the provided
 *			distance.
 *		<li><tt>Circle</tt>: The service will select the first 100 records (or less with the
 *			limits parameter) closest to the provided point and within the provided
 *			radius.
 *		<li><tt>Polygon</tt>: The service will select all the records within the provided
 *			polygon, excluding eventual polygon holes.
 *		<li><tt>Rect</tt>: The service will select all the records within the provided
 *			rectangle.
 *	 </ul>
 *	<li><tt>{@link kAPI_PARAM_SHAPE_OFFSET}</tt>: <em>Shape offset</em>. This parameter is
 *		the tag reference of the shape, it is required if the {@link kAPI_PARAM_SHAPE}
 *		parameter was provided.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This required parameter
 *		indicates the maximum number of elements to be returned. In this service it is
 *		only relevant if the {@link kAPI_PARAM_DOMAIN} parameter was provided, in that case,
 *		If omitted, it will be set to the default constant {@link kSTANDARDS_UNITS_LIMIT}.
 * </ul>
 *
 * The results structure depends on the kind of request:
 *
 * <ul>
 *	<li><em>The {@link kAPI_PARAM_GROUP} parameter was provided</em>: In that case the
 *		results are a series of nested arrays representing the record count grouped by the
 *		elements provided in the {@link kAPI_PARAM_GROUP} parameter in which the leaf nodes
 *		contain the record count. Note that the leaf element will always represent the
 *		{@link kTAG_DOMAIN} property. The arrays will have the term native identifier as
 *		the key, the value will be an array containing the term's {@link kTAG_LABEL} and
 *		{@link kTAG_DEFINITION}, if the element is a leaf, the count will be set in the
 *		element indexed by {@link kAPI_PARAM_RESPONSE_COUNT}; if the element is not a leaf,
 *		the element indexed by {@link kAPI_PARAM_RESPONSE_CHILDREN} will hold a list of
 *		similar structures.
 *	<li><em>The {@link kAPI_PARAM_DOMAIN} parameter was provided</em>: In that case the
 *		results represent individual records, the format is defined by the
 *		{@link kAPI_PARAM_DATA} value:
 *	 <ul>
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: The results are clustered by the
 *			{@link ResultAggregator} class.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_FORMAT}</tt>: The service will return a
 *			formatted record set.
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_MARKER}</tt>: The results are destined to be
 *			fed to a map, it will be an array holding the following elements:
 *		 <ul>
 *			<li><tt>kAPI_PARAM_RESPONSE_IDENT</tt>: The unit native identifier.
 *			<li><tt>kTAG_TYPE</tt>: The shape type.
 *			<li><tt>kTAG_GEOMETRY</tt>: The shape geometry.
 *		 </ul>
 *	 </ul>
 * </ul>
 */
define( "kAPI_OP_MATCH_UNITS",					'matchUnits' );

/**
 * Get unit.
 *
 * This tag defines the get unit operation.
 *
 * The service will return a unit matching the provided identifier as a clustered result.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 *	<li><tt>{@link kAPI_PARAM_ID}</tt>: <em>Identifier</em>. This required parameter
 *		holds the unit native identifier.
 * </ul>
 */
define( "kAPI_OP_GET_UNIT",						'getUnit' );

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
 * Search collection (string).
 *
 * This tag defines the requested search collection.
 *
 * This parameter is a string which indicates which collection we intend to search, the
 * value must be taken from the following enumerated set:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_TAG}</tt>: Tags.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_TERM}</tt>: Terms.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_NODE}</tt>: Nodes.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_EDGE}</tt>: Edges.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: Units.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_ENTITY}</tt>: Entities.
 * </ul>
 */
define( "kAPI_PARAM_COLLECTION",				'collection' );

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

/**
 * Minimum value (int/float).
 *
 * This tag defines the range minimum value.
 *
 * The parameter is an integer or floating point value signalling the minimum value of a
 * range.
 */
define( "kAPI_PARAM_RANGE_MIN",					'min' );

/**
 * Maximum value (int/float).
 *
 * This tag defines the range maximum value.
 *
 * The parameter is an integer or floating point value signalling the maximum value of a
 * range.
 */
define( "kAPI_PARAM_RANGE_MAX",					'max' );

/**
 * Input type (string).
 *
 * This tag defines the criteria input type.
 *
 * The parameter will be sent along with the search criteria to identify the specific input
 * control associated with the current criteria element. This value is a string which can
 * take one of the following values:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_INPUT_STRING}</tt>: A string search control.
 *	<li><tt>{@link kAPI_PARAM_INPUT_RANGE}</tt>: A range search control.
 *	<li><tt>{@link kAPI_PARAM_INPUT_ENUM}</tt>: An enumerated set selection control.
 *	<li><tt>{@link kAPI_PARAM_INPUT_SHAPE}</tt>: An geographic area; note that this will not
 *		come from a traditional form element, but rather from a map selection.
 * </ul>
 */
define( "kAPI_PARAM_INPUT_TYPE",				'input-type' );

/**
 * Search criteria (array).
 *
 * This tag defines the search criteria list.
 *
 * The parameter is an array containing the list of tags and relative match values to be
 * used in a search.
 *
 * Each element is structured as follows:
 *
 * <ul>
 *	<li><em>index</em>: The index of the item must contain the tag's native identifier.
 *	<li><em>value</em>: The value of the item will contain the search criteria for the tag
 *		provided in the index, it is an array fraturing the following elements:
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_INPUT_TYPE}</tt>: <em>Input type</em>. This string
 *			identifies the kind of form input control, the value is taken from an enumerated
 *			set and it determines which other items are to be expected:
 *		 <ul>
 *			<li><tt>{@link kAPI_PARAM_INPUT_STRING}</tt>: String search:
 *			 <ul>
 *				<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: The search pattern strings list.
 *				<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: <em>Operator</em>. This required
 *					parameter indicates what kind of match should be applied to the searched
 *					strings, it is an array that must contain one of the following:
 *				 <ul>
 *					<li><tt>{@link kOPERATOR_EQUAL}</tt>: <em>Equality</em>. The two match
 *						terms must be equal.
 *					<li><tt>{@link kOPERATOR_EQUAL_NOT}</tt>: <em>Inequality</em>. The two
 *						match terms must be different.
 *					<li><tt>{@link kOPERATOR_PREFIX}</tt>: <em>Prefix</em>. The target
 *						string must start with the pattern.
 *					<li><tt>{@link kOPERATOR_CONTAINS}</tt>: <em>Contains</em>. The target
 *						string must contain the pattern.
 *					<li><tt>{@link kOPERATOR_SUFFIX}</tt>: <em>Suffix</em>. The target
 *						string must end with the pattern.
 *					<li><tt>{@link kOPERATOR_REGEX}</tt>: <em>Regular expression</em>. The
 *						parameter is expected to contain a regular expression string.
 *				 </ul>
 *					and any of the following:
 *				 <ul>
 *					<li><tt>{@link kOPERATOR_NOCASE}</tt>: <em>Case insensitive</em>. If
 *						provided, it means that the matching operation is case and accent
 *						insensitive.
 *				 </ul>
 *			 </ul>
 *			<li><tt>{@link kAPI_PARAM_INPUT_RANGE}</tt>: Value range search:
 *			 <ul>
 *				<li><tt>{@link kAPI_PARAM_RANGE_MIN}</tt>: The minimum value of the range.
 *				<li><tt>{@link kAPI_PARAM_RANGE_MAX}</tt>: The maximum value of the range.
 *				<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: <em>Operator</em>. This optionsl
 *					parameter indicates what kind of range match should be applied, it is a
 *					string that can take one of the following values:
 *				 <ul>
 *					<li><tt>{@link kOPERATOR_IRANGE}</tt>: <em>Range inclusive</em>. The
 *						provided minimum and maximum are included in the matched range.
 *					<li><tt>{@link kOPERATOR_ERANGE}</tt>: <em>Range exclusive</em>. The
 *						provided minimum and maximum are excluded from the matched range.
 *				 </ul>
 *					If the parameter is omitted, the {@link kOPERATOR_IRANGE} operator is
 *					used by default.
 *			 </ul>
 *			<li><tt>{@link kAPI_PARAM_INPUT_ENUM}</tt>: Enumerated set search:
 *			 <ul>
 *				<li><tt>{@link kAPI_RESULT_ENUM_TERM}</tt>: An array containing the list of
 *					term native identifiers corresponding to the enumerated values to be
 *					matched.
 *			 </ul>
 *			<li><tt>{@link kAPI_PARAM_OFFSETS}</tt>: <em>Selected offsets</em>. If this parameter
 *				is omitted, the selection criteria will apply to all offsets in which the
 *				current tag is used, this parameter can be used to provide a specific set of
 *				offsets.
 *		 </ul>
 *	 </ul>
 * </ul>
 */
define( "kAPI_PARAM_CRITERIA",					'criteria' );

/**
 * Object identifier (mixed).
 *
 * This tag defines the object identifier.
 *
 * This parameter is used by services requesting a single specific object, the parameter
 * should hold the object identifier, which will generally be its native identifier.
 */
define( "kAPI_PARAM_ID",						'id' );

/**
 * Results domain (string).
 *
 * This tag defines the results domain.
 *
 * This parameter is used by services selecting units, it indicates what type of unit to
 * select. The value is the enumerated set of the {@link kTAG_DOMAIN} unit property.
 */
define( "kAPI_PARAM_DOMAIN",					'result-domain' );

/**
 * Result type (string).
 *
 * This tag defines the result type.
 *
 * This parameter is used by services selecting units, it indicates what kind of data the
 * service should return. This parameter is required if the {@link kAPI_PARAM_DOMAIN} is
 * provided: it indicates what kind of data the service should return:
 *
 * <ul>
 *	 <li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: The service will return a clustered
 *		record set.
 *	 <li><tt>{@link kAPI_RESULT_ENUM_DATA_FORMAT}</tt>: The service will return a formatted
 *		record set.
 *	 <li><tt>{@link kAPI_RESULT_ENUM_DATA_MARKER}</tt>: The service will return a set of
 *		geographic markers.
 * </ul>
 *
 * This parameter is ignored if the {@link kAPI_PARAM_DOMAIN} parameter is not provided.
 */
define( "kAPI_PARAM_DATA",						'result-data' );

/**
 * Result grouping (string).
 *
 * This tag defines the results grouping.
 *
 * This parameter is used by services selecting units, it provides a list of property
 * identifiers which determine the results groupings.
 *
 * The value may either be a string or a list of strings, in the first case the result will
 * be an array indexed by the property value with the records count as value, in the second
 * case the result will be a nested array clustering the groups starting from the first
 * element to the last; only the leaf elements will hold the record count.
 */
define( "kAPI_PARAM_GROUP",						'grouping' );

/**
 * Geographic shape (shape).
 *
 * This tag defines the geographic shape.
 *
 * This parameter is used by services selecting units, it provides a geographic shape which
 * can be used to further filter units based on their location.
 *
 * The value must have the following format:
 *
 * <ul>
 *	<li><tt>{@link kTAG_TYPE}</tt>: This element indicates the shape type.
 *	<li><tt>{@link kTAG_GEOMETRY}</tt>: This element indicates the shape geometry.
 * </ul>
 *
 * Depending on the type of the shape:
 *
 * <ul>
 *	<li><tt>Point</tt>: The geometry should be an array of two elements, the first holding
 *		an array with the longitude and latitude, the second should hold the maximum
 *		distance in meters. The service will select the first 100 (at most) units closest to
 *		the provided point.
 *	<li><tt>Circle</tt>: The geometry should be an array of two elements, the first holding
 *		an array with the longitude and latitude, the second should hold the circle radius
 *		in radians. The service will select all units contained in the circle.
 *	<li><tt>Polygon</tt>: The geometry should be a series of arrays holding the vertices of
 *		the polygon. The first array provides the exterior ring as a list of coordinate
 *		arrays, the subsequent arrays represent the inner rings of the polygon. The service
 *		will select all the records within the provided polygon, excluding eventual polygon
 *		holes.
 *	<li><tt>Rect</tt>: The service will select all the records within the provided
 *		rectangle. The value is a set of two arrays, providing respectively the bottom left
 *		and upper right coordinates. The service will select all the records within the
 *		provided rect.
 * </ul>
 */
define( "kAPI_PARAM_SHAPE",						'shape' );

/**
 * Geographic shape offset (string/int).
 *
 * This tag defines the geographic shape offset.
 *
 * This parameter is required if the {@link kAPI_PARAM_SHAPE} parameter was provided, it
 * defines the offset corresponding to the shape property; it should be the tag native
 * identifier.
 */
define( "kAPI_PARAM_SHAPE_OFFSET",				'shape-offset' );

/*=======================================================================================
 *	GENERIC FLAG REQUEST PARAMETERS														*
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
 *	GENERIC RESPONSE PARAMETERS															*
 *======================================================================================*/

/**
 * Count (int).
 *
 * This parameter represents a count.
 */
define( "kAPI_PARAM_RESPONSE_COUNT",			'count' );

/**
 * Childern (int).
 *
 * This parameter represents a list of sub-elements.
 */
define( "kAPI_PARAM_RESPONSE_CHILDREN",			'children' );

/**
 * Identifier (mixed).
 *
 * This parameter represents an identifier.
 */
define( "kAPI_PARAM_RESPONSE_IDENT",			'ident' );

/*=======================================================================================
 *	FORMATTED RESPONSE PARAMETERS														*
 *======================================================================================*/

/**
 * Property name or label (string).
 *
 * This tag indicates the property name or label.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_NAME",		'name' );

/**
 * Property info or description (string).
 *
 * This tag indicates the property information or description.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_INFO",		'info' );

/**
 * Property data (string/array).
 *
 * This tag indicates the property data, formatted as a string or array of strings.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_DATA",		'data' );

/**
 * Property link (string/array).
 *
 * This tag indicates the property link, which can take two forms:
 *
 * <ul>
 *	<li><em>URL</em>: If the property contains an internet link, this element will hold the
 *		URL as a string.
 *	<li><em>Object reference</em>: If the property contains an object reference, this
 *		element will hold the following structure:
 *	 <ul>
 *		<li><tt>id</tt>: The referenced object native identifier as a string.
 *		<li><tt>coll</tt>: The referenced object collection name.
 *	 </ul>
 * </ul>
 *
 * In both cases the {@link kAPI_PARAM_RESPONSE_FRMT_DATA} element will hold the link
 * display name.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_LINK",		'link' );

/**
 * Property sub-document (array).
 *
 * This tag indicates the property sub-document as an array.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_DOCU",		'docu' );

/*=======================================================================================
 *	ENUMERATION LIST PARAMETERS															*
 *======================================================================================*/

/**
 * Term (string).
 *
 * This tag is used when returning an enumeration element, it defines the element's term.
 *
 * This parameter is also used to provide an enumerated value search element, in that case
 * the parameter is an array.
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

/*=======================================================================================
 *	RESULT TYPE ENUMERATED SET															*
 *======================================================================================*/

/**
 * Record (string).
 *
 * This value indicates a result of type clustered records
 */
define( "kAPI_RESULT_ENUM_DATA_RECORD",			'record' );

/**
 * Formatted (string).
 *
 * This value indicates a result of type formatted records
 */
define( "kAPI_RESULT_ENUM_DATA_FORMAT",			'formatted' );

/**
 * Marker (string).
 *
 * This value indicates a result of type geographic markers
 */
define( "kAPI_RESULT_ENUM_DATA_MARKER",			'marker' );

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

/*=======================================================================================
 *	FORM INPUT TYPE ENUMERATED SET														*
 *======================================================================================*/

/**
 * String input (string).
 *
 * This parameter indicates a form string input.
 *
 * A form element of this type should feature the following elements:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: The search pattern strings list (required), a
 *		string containing the search pattern.
 *	<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: The search operator (required), one of the
 *		following:
 *	 <ul>
 *		<li><tt>{@link kOPERATOR_EQUAL}</tt>: <em>Equality</em>.
 *		<li><tt>{@link kOPERATOR_EQUAL_NOT}</tt>: <em>Inequality</em>.
 *		<li><tt>{@link kOPERATOR_PREFIX}</tt>: <em>Starts with</em>.
 *		<li><tt>{@link kOPERATOR_CONTAINS}</tt>: <em>Contains</em>.
 *		<li><tt>{@link kOPERATOR_SUFFIX}</tt>: <em>Ends with</em>.
 *		<li><tt>{@link kOPERATOR_REGEX}</tt>: <em>Regular expression</em>.
 *	 </ul>
 *		and any of the following:
 *	 <ul>
 *		<li><tt>{@link kOPERATOR_NOCASE}</tt>: <em>Case and accent insensitive</em>.
 *	 </ul>
 * </ul>
 */
define( "kAPI_PARAM_INPUT_STRING",				'input-string' );

/**
 * Range input (string).
 *
 * This parameter indicates a form range input.
 *
 * A form element of this type should feature the following elements:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_RANGE_MIN}</tt>: Minimum range (required).
 *	<li><tt>{@link kAPI_PARAM_RANGE_MAX}</tt>: Maximum range (required).
 *	<li><tt>{@link kAPI_PARAM_OPERATOR}</tt>: The search operator (optional, defaults to
 *		{@link kOPERATOR_IRANGE}), one of the following:
 *	 <ul>
 *		<li><tt>{@link kOPERATOR_IRANGE}</tt>: <em>Range inclusive</em>.
 *		<li><tt>{@link kOPERATOR_ERANGE}</tt>: <em>Range exclusive</em>.
 *	 </ul>
 * </ul>
 */
define( "kAPI_PARAM_INPUT_RANGE",				'input-range' );

/**
 * Enumeration input (string).
 *
 * This parameter indicates a form enumneration input.
 *
 * A form element of this type should feature the following elements:
 *
 * <ul>
 *	<li><tt>{@link kAPI_RESULT_ENUM_TERM}</tt>: Enumerated set (required).
 * </ul>
 */
define( "kAPI_PARAM_INPUT_ENUM",				'input-enum' );

/**
 * Shape input (string).
 *
 * This parameter indicates a form shape input.
 *
 * A form element of this type should feature the following elements:
 *
 * <ul>
 *	<li><tt>{@link kAPI_RESULT_SHAPE}</tt>: The shape (required). The value should be a
 *		GeoJSON structure amon the following types:
 *	 <ul>
 *		<li><tt>Point</tt>: The service will select the first 100 records (or less with the
 *			limits parameter) closest to the provided point and less than the provided
 *			distance.
 *		<li><tt>Circle</tt>: The service will select the first 100 records (or less with the
 *			limits parameter) closest to the provided point and within the provided radius.
 *		<li><tt>Polygon</tt>: The service will select all the records within the provided
 *			polygon, excluding eventual polygon holes.
 *		<li><tt>Rect</tt>: The service will select all the records within the provided
 *			rectangle.
 *	 </ul>
 *	<li><tt>{@link kAPI_PARAM_SHAPE_OFFSET}</tt>: The tag reference to the shape property.
 *		meters from the provided point (required if the shape is a point).
 * </ul>
 */
define( "kAPI_PARAM_INPUT_SHAPE",				'input-shape' );

/**
 * Default input (string).
 *
 * This parameter indicates a form default input.
 *
 * A form element of this type should feature the following elements:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: The value to match (required), an equality test
 *		will be applied.
 * </ul>
 */
define( "kAPI_PARAM_INPUT_DEFAULT",				'input-default' );

/*=======================================================================================
 *	INTERNAL PARAMETERS																	*
 *======================================================================================*/

/**
 * Index (array).
 *
 * This parameter will hold the list of indexes for the criteria tag.
 */
define( "kAPI_PARAM_INDEX",						'index' );

/**
 * Data type (string).
 *
 * This parameter will hold the tag data type.
 */
define( "kAPI_PARAM_DATA_TYPE",					'data-type' );

/**
 * Value count (int).
 *
 * This parameter will hold the match value count of the current container.
 */
define( "kAPI_PARAM_VALUE_COUNT",				'values' );

/**
 * Offsets (array).
 *
 * This parameter will hold the list of offsets for the current criteria tag; this parameter
 * may also be provided as part of a criteria to specify which offsets should be searched.
 */
define( "kAPI_PARAM_OFFSETS",					'offsets' );


?>
