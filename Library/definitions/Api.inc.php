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
define( "kAPI_REQUEST_LANGUAGE",				'ln' );

/**
 * Parameters.
 *
 * This tag identifies the service request parameters.
 */
define( "kAPI_REQUEST_PARAMETERS",				'pr' );

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
define( "kAPI_DICTIONARY_REF_COUNT",			'count-offset' );

/**
 * Tags cross reference.
 *
 * This tag indicates the dictionary tags cross references.
 */
define( "kAPI_DICTIONARY_TAGS",					'tags-xref' );

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
define( "kAPI_OP_LIST_CONSTANTS",				'listConstants' );

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
define( "kAPI_OP_LIST_OPERATORS",				'listOperators' );

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
define( "kAPI_OP_LIST_REF_COUNTS",				'listRefCounts' );

/**
 * List statistics.
 *
 * This tag defines the list statistics operation.
 *
 * This operation will return the list of statistics associated to the provided domain, the
 * service expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 *	<li><tt>{@link kAPI_PARAM_DOMAIN}</tt>: <em>Statistics domain</em>. This required
 *		parameter indicates the statistics domain, only one element is expected.
 * </ul>
 *
 * The response format is an array organised as follows:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_STAT}</tt>: The statistics code which can be set in the
 *		same parameter of the {@link kAPI_OP_MATCH_UNITS} service.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: The statistics name or label.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: The statistics description.
 * </ul>
 */
define( "kAPI_OP_LIST_STATS",					'listStats' );

/**
 * List domains.
 *
 * This tag defines the list domains operation.
 *
 * This operation will return the list of featured domains with their relative units count,
 * the service expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 * </ul>
 *
 * The response format is an array organised as follows:
 *
 * <ul>
 *	<li><tt>key</tt>: The domain code.
 *	<li><tt>value</tt>: The domain details:
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: The statistics name or label.
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: The statistics description.
 *		<li><tt>{@link kAPI_PARAM_RESPONSE_COUNT}</tt>: The number or unit records.
 *	 </ul>
 * </ul>
 */
define( "kAPI_OP_LIST_DOMAINS",					'listDomains' );

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
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_USER}</tt>: <em>User references</em>.
 *			Select only those tags which have their {@link kTAG_USER_COUNT} greater than
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
 *			zero.
 *	 </ul>
 *		The filter will be chained in <tt>AND</tt>.
 *	<li><tt>{@link kAPI_PARAM_EXCLUDED_TAGS}</tt>: <em>Tags to skip</em>. This optional
 *		parameter can be used to provide a list of tags that should be excluded from the
 *		search.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This required parameter
 *		indicates the maximum number of elements to be returned. If omitted, it will be
 *		set to the default constant {@link kSTANDARDS_STRINGS_LIMIT}.
 * </ul>
 */
define( "kAPI_OP_MATCH_TAG_LABELS",				'matchTagLabels' );

/**
 * Match tag summary labels.
 *
 * This tag defines the match tag summary labels operation.
 *
 * The service will return a list of tag label strings corresponding to the provided
 * pattern, language, operator and limit, these labels will only come from tags which can be
 * included in summaries.
 *
 * This service expects the same parameters as the {@link kAPI_OP_MATCH_TAG_LABELS} service,
 * except that the result will filter only labels from summary tags.
 */
define( "kAPI_OP_MATCH_TAG_SUMMARY_LABELS",		'matchTagSummaryLabels' );

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
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_USER}</tt>: <em>User references</em>.
 *			Select only those tags which have their {@link kTAG_USER_COUNT} greater than
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
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
 * This tag defines the match summary tags by label operation.
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
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_USER}</tt>: <em>User references</em>.
 *			Select only those tags which have their {@link kTAG_USER_COUNT} greater than
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
 *	 </ul>
 *		The filter will be chained in <tt>AND</tt>.
 *	<li><tt>{@link kAPI_PARAM_EXCLUDED_TAGS}</tt>: <em>Tags to skip</em>. This optional
 *		parameter can be used to provide a list of tags that should be excluded from the
 *		search.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This required parameter
 *		indicates the maximum number of elements to be returned. If omitted, it will be
 *		set to the default constant {@link kSTANDARDS_STRINGS_LIMIT}.
 * </ul>
 */
define( "kAPI_OP_MATCH_TAG_BY_LABEL",			'matchTagsByLabel' );

/**
 * Match tag by identifier.
 *
 * This tag defines the match tag by identifier operation.
 *
 * The service will return the tag whose native identifier or serial number matches the
 * provided parameter.
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_TAG}</tt>: This element holds the tag identifier(s).
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 * </ul>
 */
define( "kAPI_OP_MATCH_TAG_BY_IDENTIFIER",		'matchTagByIdentifier' );

/**
 * Match summary tags by label.
 *
 * This tag defines the match tag by label operation.
 *
 * The service will return a list of tag objects whose label matches the provided pattern,
 * language, operator and limit; these labels will only come from tags which can be included
 * in summaries.
 *
 * This service expects the same parameters as the {@link kAPI_OP_MATCH_TAG_BY_LABEL}
 * service, except that the result will include only summary tags.
 *
 * The result is an array of elements representing the disting offsets of the tags selected
 * by the label:
 *
 * <ul>
 *	<li><em>key</em>: The offset.
 *	<li><em>value</em>: An array structured as follows:
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_TAG}</tt>: This element holds that tag sequence number of
 *			the offset's tag.
 *		<li><em>Other elements</em>: The other elements of the array represent in order the
 *			tags that comprise the offset, starting from the root structure and ending with
 *			the leaf tag:
 *		 <ul>
 *			<li><em>key</em>: The tag sequence number.
 *			<li><em>value</em>: An array holding the following elements:
 *			 <ul>
 *				<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: The tag label.
 *				<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: The tag description.
 *			 </ul>
 *		 </ul>
 *	 </ul>
 * </ul>
 */
define( "kAPI_OP_MATCH_SUMMARY_TAG_BY_LABEL",	'matchSummaryTagsByLabel' );

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
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_USER}</tt>: <em>User references</em>.
 *			Select only those tags which have their {@link kTAG_USER_COUNT} greater than
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
 *	 </ul>
 *		The filter will be chained in <tt>AND</tt>.
 *	<li><tt>{@link kAPI_PAGING_LIMIT}</tt>: <em>Limit</em>. This required parameter
 *		indicates the maximum number of elements to be returned. If omitted, it will be
 *		set to the default constant {@link kSTANDARDS_STRINGS_LIMIT}.
 * </ul>
 */
define( "kAPI_OP_MATCH_TERM_BY_LABEL",			'matchTermsByLabel' );

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
 *		parameter determines what value the result parameter {@link kAPI_RESULT_ENUM_VALUE}
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
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_USER}</tt>: <em>User references</em>.
 *			Select only those tags which have their {@link kTAG_USER_COUNT} greater than
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
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
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_COUNT}</tt>: The enumeration reference count.
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
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_USER}</tt>: <em>User references</em>.
 *			Select only those tags which have their {@link kTAG_USER_COUNT} greater than
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
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
 * Get node form.
 *
 * This tag defines the get node form operation.
 *
 * The service will return the structure related to the provided form node, if the provided
 * node is not a form, the method will raise an exception.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_NODE}</tt>: <em>Node</em>. This required parameter is an
 *		integer referencing the node native identifier.
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
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_USER}</tt>: <em>User references</em>.
 *			Select only those tags which have their {@link kTAG_USER_COUNT} greater than
 *		<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: <em>Unit references</em>. Select
 *			only those tags which have their {@link kTAG_UNIT_COUNT} greater than zero.
 *	 </ul>
 *	<li><tt>{@link kAPI_PARAM_RECURSE}</tt>: <em>Recurse flag</em>. This optional flag, if
 *		set, will allow traversing root form nodes; if not set, root form nodes will not be
 *		recursed.
 * </ul>
 *
 * The result will be returned in the {@link kAPI_RESPONSE_RESULTS} section of the response,
 * it will be an array whose elements are structured as follows:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_ID}</tt>: This item will be set with the native identifier of
 *		a tag, if the element references a tag, or it will be missing if the element
 *		references a term.
 *	<li><tt>{@link kAPI_RESULT_ENUM_LABEL}</tt>: The tag ot term label.
 *	<li><tt>{@link kAPI_RESULT_ENUM_DESCR}</tt>: The tag or term description.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_COUNT}</tt>: The reference count, if a tag and the
 *		{@link kAPI_PARAM_REF_COUNT} parameter was provided.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_CHILDREN}</tt>: The children of the element in the
 *		same format as here.
 * </ul>
 */
define( "kAPI_OP_GET_NODE_FORM",				'getNodeForm' );

/**
 * Get node structure.
 *
 * This tag defines the get node structure operation.
 *
 * The service will return the structure related to the provided struct node, if the
 * provided node is not a structure, the method will raise an exception.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_NODE}</tt>: <em>Node</em>. This required parameter is an
 *		integer referencing the node native identifier.
 * </ul>
 *
 * The result will be returned in the {@link kAPI_RESPONSE_RESULTS} section of the response,
 * it will be an array whose elements are structured as follows:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_ID}</tt>: This item will be set with the native identifier of
 *		a tag, if the element references a tag, or it will be missing if the element
 *		references a term.
 *	<li><tt>{@link kAPI_RESULT_ENUM_LABEL}</tt>: The tag or term label.
 *	<li><tt>{@link kAPI_RESULT_ENUM_DESCR}</tt>: The tag or term description.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_COUNT}</tt>: The reference count, if a tag and the
 *		{@link kAPI_PARAM_REF_COUNT} parameter was provided.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_CHILDREN}</tt>: The children of the element in the
 *		same format as here.
 * </ul>
 */
define( "kAPI_OP_GET_NODE_STRUCT",				'getNodeStruct' );

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
 *				<li><tt>{@link kAPI_PARAM_INPUT_TEXT}</tt>: Full-text search:
 *				 <ul>
 *					<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: The search pattern.
 *				 </ul>
 *					Note that in this case the index cannot hold the tag reference, you must
 *					set this element's index to {@link kAPI_PARAM_FULL_TEXT_OFFSET}.
 *				<li><tt>{@link kAPI_PARAM_INPUT_STRING}</tt>: String search:
 *				 <ul>
 *					<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: The search pattern.
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
 *	<li><tt>{@link kAPI_PARAM_DATA}</tt>: <em>Results format</em>. This parameter must be
 *		provided if the {@link kAPI_PARAM_DOMAIN} parameter was provided, it indicates what
 *		kind of data the service should return:
 *	 <ul>
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_COLUMN}</tt>: The service will return a
 *			table set.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: The service will return a
 *			clustered record set.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_STAT}</tt>: The service will return the
 *			statistics according to the provided {@link kAPI_PARAM_STAT} parameter; in this
 *			case the {@link kAPI_PARAM_DOMAIN} parameter and the {@link kAPI_PARAM_STAT}
 *			parameters are required.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_FORMAT}</tt>: The service will return a
 *			formatted record set.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_MARKER}</tt>: The service will return a set of
 *			geographic markers, each element will contain the unit {@link kTAG_NID} and the
 *			value contained in the offset provided in the {@link kAPI_PARAM_SHAPE_OFFSET},
 *			which is required in this case.
 *	 </ul>
 *	<li><tt>{@link kAPI_PARAM_STAT}</tt>: <em>Statistics type</em>. This parameter is
 *		required if the {@link kAPI_PARAM_DATA} parameter is
 *		{@link kAPI_RESULT_ENUM_DATA_STAT}: it indicates which type of statistics is to be
 *		performed.
 *	<li><tt>{@link kAPI_PARAM_DOMAIN}</tt>: <em>Results domain</em>. If this parameter is
 *		provided, the service will return the results of the type provided in this
 *		parameter, if it is not provided, the next parameter is required. If this parameter
 *		is provided, the next parameter will be ignored. This parameter is required if the
 *		{@link kAPI_PARAM_DATA} parameter is {@link kAPI_RESULT_ENUM_DATA_STAT}.
 *	<li><tt>{@link kAPI_PARAM_GROUP}</tt>: <em>Group results</em>. This parameter must be
 *		provided if the {@link kAPI_PARAM_DOMAIN} is omitted: the value may be a string or
 *		an array of strings representing the tag native identifiers or sequence numbers by
 *		which the results should be grouped. The result will be a nested array containing
 *		the distinct values of the provided tags as keys and the record count as values.
 *		If the parameter is an array, the results will be clustered in the order in which
 *		the tags are provided, only the leaf elements will contain the record counts.
 *		<em>Note that the leaf element will always be the {@link kTAG_DOMAIN} property, if
 *		missing from the provided parametrer it will be added</em>.
 *	<li><tt>{@link kAPI_PARAM_SUMMARY}</tt>: <em>Summary selection</em>. This parameter
 *		should be provided if you reach this query from a summary page with more than one
 *		element (including the default domain property), it is structured as an array in
 *		which each element is an array of one item with its key represents the offset and
 *		its value the match value. <em>Note that you should not provide the domain leaf
 *		element in this parameter, the domain value should be instead provided in the
 *		{@link kAPI_PARAM_DOMAIN} parameter.</em> This parameter will be ignored if the
 *		{@link kAPI_PARAM_GROUP} parameter was provided.
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
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_COLUMN}</tt>: The service will return a
 *			table set.
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: The results are clustered by the
 *			{@link IteratorSerialiser} class.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_STAT}</tt>: The results are structured as
 *			follows:
 *		 <ul>
 *			<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: Statistics name or label.
 *			<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: Statistics description.
 *			<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_HEAD}</tt>: Statistics header.
 *			<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_DOCU}</tt>: Statistics data (one element
 *				per header element).
 *		 </ul>
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_FORMAT}</tt>: The service will return a
 *			formatted record set.
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_MARKER}</tt>: The results are destined to be
 *			fed to a map, it will be an array holding the following elements:
 *		 <ul>
 *			<li><tt>kAPI_PARAM_ID</tt>: The unit native identifier.
 *			<li><tt>kAPI_PARAM_DOMAIN</tt>: The unit domain.
 *			<li><tt>kAPI_PARAM_SHAPE</tt>: The unit shape geometry.
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
 *	<li><tt>{@link kAPI_PARAM_DATA}</tt>: <em>Data type</em>. This required parameter
 *		indicates how the unit data should be formatted:
 *	 <ul>
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_COLUMN}</tt>: The service will return a
 *			table set.
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: The results are clustered by the
 *			{@link IteratorSerialiser} class.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_FORMAT}</tt>: The service will return a
 *			formatted record set.
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_MARKER}</tt>: The results are destined to be
 *			fed to a map, it will be an array holding the following elements:
 *		 <ul>
 *			<li><tt>kAPI_PARAM_ID</tt>: The unit native identifier.
 *			<li><tt>kAPI_PARAM_DOMAIN</tt>: The unit domain.
 *			<li><tt>kAPI_PARAM_SHAPE</tt>: The unit shape geometry.
 *		 </ul>
 *	 </ul>
 * </ul>
 */
define( "kAPI_OP_GET_UNIT",						'getUnit' );

/**
 * Add user.
 *
 * This tag defines the add user operation.
 *
 * The service will add or replace the provided user.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_OBJECT}</tt>: <em>Object</em>. The user object to be added or
 *		replaced.
 * </ul>
 */
define( "kAPI_OP_ADD_USER",						'addUser' );

/**
 * Get user.
 *
 * This tag defines the get user operation.
 *
 * The service will return a user matching the provided identifier as a clustered result.
 *
 * This operation expects the following parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_REQUEST_LANGUAGE}</tt>: <em>Language</em>. If the parameter is
 *		omitted, the {@link kSTANDARDS_LANGUAGE} constant will be used. The value represents
 *		a language code.
 *	<li><tt>{@link kAPI_PARAM_ID}</tt>: <em>Identifier</em>. This required parameter
 *		holds the user native identifier or the user code/password combination as an array.
 *	<li><tt>{@link kAPI_PARAM_DATA}</tt>: <em>Data type</em>. This required parameter
 *		indicates how the unit data should be formatted:
 *	 <ul>
 *		<li><tt>{@link kAPI_RESULT_ENUM_DATA_RECORD}</tt>: The results are clustered by the
 *			{@link IteratorSerialiser} class.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_FORMAT}</tt>: The service will return a
 *			formatted record set.
 *		 <li><tt>{@link kAPI_RESULT_ENUM_DATA_MARKER}</tt>: The service will return a set of
 *			geographic markers, each element will contain the user {@link kTAG_NID} and the
 *			value contained in the offset provided in the {@link kAPI_PARAM_SHAPE_OFFSET},
 *			which is required in this case.
 *	 </ul>
 *	<li><tt>{@link kAPI_PARAM_SHAPE_OFFSET}</tt>: <em>Shape offset</em>. This parameter is
 *		the tag reference of the shape, it is required if the
 *		{@link kAPI_RESULT_ENUM_DATA_MARKER} value was provided for the
 *		{@link kAPI_PARAM_DATA} parameter.
 * </ul>
 */
define( "kAPI_OP_GET_USER",						'getUser' );

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
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_USER}</tt>: Users.
 *	<li><tt>{@link kAPI_PARAM_COLLECTION_UNIT}</tt>: Units.
 * </ul>
 *
 * The service will only select those tags which have values in the provided collections.
 */
define( "kAPI_PARAM_REF_COUNT",					'has-values' );

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
 * Term (string).
 *
 * This tag defines the requested term.
 *
 * This parameter represents a string referencing a term native identifier.
 */
define( "kAPI_PARAM_TERM",						'term' );

/**
 * Node (int).
 *
 * This tag defines the requested node.
 *
 * This parameter represents an integer referencing a node native identifier.
 */
define( "kAPI_PARAM_NODE",						'node' );

/**
 * Parent node (int).
 *
 * This tag defines the parent node.
 *
 * This parameter represents an integer referencing a parent node native identifier.
 */
define( "kAPI_PARAM_PARENT_NODE",				'parent-node' );

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
 *	<li><tt>{@link kAPI_PARAM_INPUT_TEXT}</tt>: A full-text search control.
 *	<li><tt>{@link kAPI_PARAM_INPUT_STRING}</tt>: A string search control.
 *	<li><tt>{@link kAPI_PARAM_INPUT_RANGE}</tt>: A range search control.
 *	<li><tt>{@link kAPI_PARAM_INPUT_ENUM}</tt>: An enumerated set selection control.
 *	<li><tt>{@link kAPI_PARAM_INPUT_SHAPE}</tt>: A geographic area; note that this will not
 *		come from a traditional form element, but rather from a map selection.
 *	<li><tt>{@link kAPI_PARAM_INPUT_OFFSET}</tt>: An offset presence; note that this will
 *		not come from a traditional form element, but rather from a group request.
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
 *	<li><em>index</em>: The index of the item must contain the tag's native identifier, or
 *		the offset in case of a {@link kAPI_PARAM_INPUT_OFFSET} input type.
 *	<li><em>value</em>: The value of the item will contain the search criteria for the tag
 *		provided in the index, it is an array fraturing the following elements:
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_INPUT_TYPE}</tt>: <em>Input type</em>. This string
 *			identifies the kind of form input control, the value is taken from an enumerated
 *			set and it determines which other items are to be expected:
 *		 <ul>
 *			<li><tt>{@link kAPI_PARAM_INPUT_TEXT}</tt>: Full-text search:
 *			 <ul>
 *				<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: The search pattern.
 *			 </ul>
 *				Note that in this case the index cannot hold the tag reference, you must
 *				set this element's index to {@link kAPI_PARAM_FULL_TEXT_OFFSET}.
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
 *			<li><tt>{@link kAPI_PARAM_INPUT_SHAPE}</tt>: A geographic area; note that this
 *				will not come from a traditional form element, but rather from a map
 *				selection.
 *			 <ul>
 *				<li><tt>{@link kAPI_PARAM_SHAPE}</tt>: The shape (required). The value
 *					should be a GeoJSON structure amon the following types:
 *				 <ul>
 *					<li><tt>Point</tt>: The service will select the first 100 records (or
 *						less with the limits parameter) closest to the provided point and
 *						less than the provided distance.
 *					<li><tt>Circle</tt>: The service will select the first 100 records (or
 *						less with the limits parameter) closest to the provided point and
 *						within the provided radius.
 *					<li><tt>Polygon</tt>: The service will select all the records within the
 *						provided polygon, excluding eventual polygon holes.
 *					<li><tt>Rect</tt>: The service will select all the records within the
 *						provided rectangle.
 *				 </ul>
 *				<li><tt>{@link kAPI_PARAM_SHAPE_OFFSET}</tt>: The tag reference to the shape
 *					property. meters from the provided point (required if the shape is a
 *					point).
 *			 </ul>
 *			<li><tt>{@link kAPI_PARAM_INPUT_OFFSET}</tt>: An offset presence. This kind of
 *				input is used to ensure the presence of specific offsets, rather than of
 *				specific tags, this will not come from a traditional form element, but it
 *				will be provided by a summary group request. This type does not contain any
 *				element, since the offset is indicated in the array element index.
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
 * Object (array).
 *
 * This tag defines the object.
 *
 * The parameter is an array containing the object to be set or replaced.
 */
define( "kAPI_PARAM_OBJECT",					'object' );

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
define( "kAPI_PARAM_DOMAIN",					'domain' );

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
 *	 <li><tt>{@link kAPI_RESULT_ENUM_DATA_COLUMN}</tt>: The service will return a table set.
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
define( "kAPI_PARAM_DATA",						'data' );

/**
 * Statistics type (string).
 *
 * This tag defines the statistics type.
 *
 * This parameter is used by services requesting a statistical summary, it defines what type
 * of statistics is to be performed.
 */
define( "kAPI_PARAM_STAT",						'stat' );

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
 * Result summary (array).
 *
 * This tag defines the results summary matches.
 *
 * This parameter is used by services originating from a summary results set, it is an array
 * containing the offset and match value of all the elements of the current summary element.
 *
 * The elements key represents the offset and the value the match value. When providing
 * values in this parameter, you should not provide the leaf element of the summary, by
 * default the domain: this element's value belongs in the {@link @link kAPI_PARAM_DOMAIN}
 * parameter.
 */
define( "kAPI_PARAM_SUMMARY",					'summary' );

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

/**
 * Excluded tags (array).
 *
 * This tag defines the excluded tags list.
 *
 * This parameter can be used to prevent the search from covering the tags provided in the
 * list, the elements may be the tag native identifier or the tag squence number. This
 * parameter is generally used by services selecting tags or tag labels.
 */
define( "kAPI_PARAM_EXCLUDED_TAGS",				'exclude-tags' );

/**
 * Full-text search tag (string).
 *
 * This tag defines the full-text search offset, which does not correspond to any offset.
 */
define( "kAPI_PARAM_FULL_TEXT_OFFSET",			'$search' );

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
 * Points (int).
 *
 * This parameter represents the points count.
 */
define( "kAPI_PARAM_RESPONSE_POINTS",			'points' );

/**
 * Childern (int).
 *
 * This parameter represents a list of sub-elements.
 */
define( "kAPI_PARAM_RESPONSE_CHILDREN",			'children' );

/*=======================================================================================
 *	FORMATTED RESPONSE PARAMETERS														*
 *======================================================================================*/

/**
 * Property type (string).
 *
 * This tag indicates the type of the formatted property, it indicates how the
 * {@link kAPI_PARAM_RESPONSE_FRMT_DISP} is structured and whether to expect additional
 * parameters:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_TYPE_SCALAR}</tt>: Scalar or list of scalars.
 *		This type represents a scalar value or a list of scalar values.
 *		The {@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter will contain either a scalar
 *		value or a list of scalar values.
 *		No other data related patameters should be expected.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_TYPE_LINK}</tt>: URL or list of URLs.
 *		This type represents a single or list of URL references consisting of a display
 *		string and a web address.
 *		If the response represents a single link, it will feature the display string in the
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter and the web address in the
 *		{@link kAPI_PARAM_RESPONSE_FRMT_LINK} parameter, both at the root level.
 *		If the response represents a list of links, it will feature a single
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter structured as an array of elements
 *		each holding a pair of {@link kAPI_PARAM_RESPONSE_FRMT_DISP} and
 *		{@link kAPI_PARAM_RESPONSE_FRMT_LINK} parameters.
 *		No other data related patameters should be expected.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_TYPE_ENUM}</tt>: Enumerated value or set.
 *		This type represents an enumerated value or set of values.
 *		If the response represents an enumerated value, the
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter will contain a
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter which represents the display value
 *		and an optional {@link kAPI_PARAM_RESPONSE_FRMT_INFO} parameter representing the
 *		description.
 *		If the response represents an enumerated set of values, the
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter will be an array of elements, each
 *		holding a {@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter and an optional
 *		{@link kAPI_PARAM_RESPONSE_FRMT_INFO} parameter.
 *		No other data related patameters should be expected.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_TYPE_TYPED}</tt>: Typed list.
 *		This type represents a list of elements containing a type and a value.
 *		If the response represents a single element, the
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter will contain a
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter representing the display string and
 *		a {@link kAPI_PARAM_RESPONSE_FRMT_NAME} parameter representing the element type; if
 *		the element does not feature the type, it will be converted to a scalar type.
 *		If the response represents a list of elements, the
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter will contain a list of elements,
 *		each featuring a {@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter representing the
 *		display string and an optional {@link kAPI_PARAM_RESPONSE_FRMT_NAME} parameter
 *		representing the element type.
 *		No other data related patameters should be expected.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_TYPE_OBJECT}</tt>: Object reference.
 *		This type represents a reference to another object which should be presented as
 *		a link which opens a page or section containing the details of the referenced
 *		object.
 *		If the response represents a single object reference, the
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter will hold the display name of the
 *		object and the {@link kAPI_PARAM_RESPONSE_FRMT_SERV} parameter will contain the set
 *		of service parameters that can be used to retrieve the object; this set can be
 *		directly used to query the service.
 *		If the response represents a list of object references, it will contain a single
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter which is an array of elements, each
 *		containing a {@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter holding the display
 *		name of the object and a {@link kAPI_PARAM_RESPONSE_FRMT_SERV} parameter contaning
 *		the set of service parameters that can be used to retrieve the object; in this case
 *		no other parameters, except {@link kAPI_PARAM_RESPONSE_FRMT_DISP}, are expected at
 *		the root level.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_TYPE_SHAPE}</tt>: Object shape.
 *		This type represents the current object's shape, which can be used to retrieve the
 *		object's coordinates in order to display the object's location on a map.
 *		The structure of this response is identical to the
 *		{@link kAPI_PARAM_RESPONSE_TYPE_OBJECT} structure.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_TYPE_STRUCT}</tt>: Substructure(s).
 *		This type represents a sub-structure or list of sub-structures, it should be
 *		represented as a disclosure triangle which may be opened to reveal the substructure
 *		contents; sub-structures may be nested.
 *		If the response represents a scalar structure, there should be a disclosure triangle
 *		next to the label at the root level, the optional
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter represents the display value to be
 *		set next to the label and the {@link kAPI_PARAM_RESPONSE_FRMT_DOCU} parameter
 *		contains the structure values which should be parsed as the root object.
 *		If the response represents a list of structures, there should be a disclosure
 *		triangle next to the label at the root level which will reveal the elements of the
 *		list; the {@link kAPI_PARAM_RESPONSE_FRMT_DOCU} parameter is an array containing the
 *		elements of the structures list. Each element has a
 *		{@link kAPI_PARAM_RESPONSE_FRMT_DISP} parameter which represents the structure name,
 *		a disclosure triangle should be placed next to this item which should disclose that
 *		structure's contents; the element has the {@link kAPI_PARAM_RESPONSE_FRMT_DOCU}
 *		parameter which contains the structure values which should be parsed as the root
 *		object.
 * </ul>
 *
 * The above descriptions assume that the response contains the
 * {@link kAPI_PARAM_RESPONSE_FRMT_NAME} and optional {@link kAPI_PARAM_RESPONSE_FRMT_INFO}
 * parameters.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_TYPE",		'type' );

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
 * Property display (string/array).
 *
 * This tag indicates the property display data, formatted as a string or array of strings.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_DISP",		'disp' );

/**
 * Map label (string).
 *
 * This tag indicates the map label of a marker or shape.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_MAP_LABEL",	'map-label' );

/**
 * Map shape (shape).
 *
 * This tag indicates the map shape of an object.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_MAP_SHAPE",	'map-shape' );

/**
 * Property link (string/array).
 *
 * This tag indicates the property URL as a string.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_LINK",		'link' );

/**
 * Property service (array).
 *
 * This tag indicates the property service as an array containing the service parameters.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_SERV",		'serv' );

/**
 * Property sub-document (array).
 *
 * This tag indicates the property sub-document as an array.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_DOCU",		'docu' );

/**
 * Statistics count (int).
 *
 * This tag indicates the statistics count for the current domain.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_STATS",		'stats-count' );

/**
 * Statistics header (array).
 *
 * This tag indicates the statistics header as an array, the elements are structured as
 * follows:
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_NAME}</tt>: Header element name.
 *	<li><tt>{@link kAPI_PARAM_RESPONSE_FRMT_INFO}</tt>: Header element description.
 *	<li><tt>{@link kAPI_PARAM_DATA_TYPE}</tt>: Column data type.
 * </ul>
 */
define( "kAPI_PARAM_RESPONSE_FRMT_HEAD",		'head' );

/*=======================================================================================
 *	FORMATTED RESPONSE TYPES															*
 *======================================================================================*/

/**
 * Scalar (string).
 *
 * This tag indicates a scalar response, which refers to a scalar value.
 */
define( "kAPI_PARAM_RESPONSE_TYPE_SCALAR",		'scalar' );

/**
 * Link (string).
 *
 * This tag indicates a link response, which refers to an URL.
 */
define( "kAPI_PARAM_RESPONSE_TYPE_LINK",		'link' );

/**
 * Enumeration (string).
 *
 * This tag indicates an enumerated response, which refers to an enumerated value or set.
 */
define( "kAPI_PARAM_RESPONSE_TYPE_ENUM",		'enum' );

/**
 * Typed list (string).
 *
 * This tag indicates a typed list response, which refers to a list of typed values.
 */
define( "kAPI_PARAM_RESPONSE_TYPE_TYPED",		'typed' );

/**
 * Object reference (string).
 *
 * This tag indicates an object reference response, which refers to another object.
 */
define( "kAPI_PARAM_RESPONSE_TYPE_OBJECT",		'object' );

/**
 * Shape (string).
 *
 * This tag indicates a shape response, which refers to the object's shape.
 */
define( "kAPI_PARAM_RESPONSE_TYPE_SHAPE",		'shape' );

/**
 * Struct (string).
 *
 * This tag indicates a structure response, which refers to a sub-structure.
 */
define( "kAPI_PARAM_RESPONSE_TYPE_STRUCT",		'struct' );

/**
 * Score (float).
 *
 * This tag indicates a search relevance score.
 */
define( "kAPI_PARAM_RESPONSE_TYPE_SCORE",		'score' );

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
 * Table (string).
 *
 * This value indicates a result of type table records
 */
define( "kAPI_RESULT_ENUM_DATA_COLUMN",			'column' );

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

/**
 * Record (string).
 *
 * This value indicates a result of type clustered records
 */
define( "kAPI_RESULT_ENUM_DATA_RECORD",			'record' );

/**
 * Statistics (string).
 *
 * This value indicates a result of type statistics
 */
define( "kAPI_RESULT_ENUM_DATA_STAT",			'stats' );

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
 * Users (string).
 *
 * This parameter indicates a reference to the entities collection.
 */
define( "kAPI_PARAM_COLLECTION_USER",			'_users' );

/**
 * Units (string).
 *
 * This parameter indicates a reference to the units collection.
 */
define( "kAPI_PARAM_COLLECTION_UNIT",			'_units' );

/*=======================================================================================
 *	FORM INPUT TYPE ENUMERATED SET														*
 *======================================================================================*/

/**
 * Full-text input (string).
 *
 * This parameter indicates a form full-text input.
 *
 * A form element of this type should feature the following elements:
 *
 * <ul>
 *	<li><tt>{@link kAPI_PARAM_PATTERN}</tt>: The search pattern strings list (required), a
 *		string containing the search pattern.
 * </ul>
 *
 * <b>Note that when using this type of input, the tag reference must be
 * {@link kAPI_PARAM_FULL_TEXT_OFFSET}</b>.
 */
define( "kAPI_PARAM_INPUT_TEXT",				'input-text' );

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
 *	<li><tt>{@link kAPI_PARAM_SHAPE}</tt>: The shape (required). The value should be a
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
 * Offset presence (array).
 *
 * This parameter indicates an offset assertion.
 *
 * This will generally be provided as a computed value, rather than from a traditional form,
 * it ensures that a specific offset is present.
 *
 * The offset is indicated in the array element index and no other parameter is required.
 */
define( "kAPI_PARAM_INPUT_OFFSET",				'input-offset' );

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
 *	GENERIC PARAMETERS																	*
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
 * Data kind (array).
 *
 * This parameter will hold the tag data kind.
 */
define( "kAPI_PARAM_DATA_KIND",					'data-kind' );

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

/**
 * Group data (array).
 *
 * This parameter collects details of the {@link kAPI_PARAM_GROUP} parameter elements which
 * are needed to handle summaries, it is an array structured as follows:
 *
 * <ul>
 *	<li><tt>key</tt>: The tag sequence number, this means that one can only use a tag once
 *		in a summary, tags found in different structures can only appear once.
 *	<li><tt>value</tt>: The value is an array containing the following information:
 *	 <ul>
 *		<li><tt>{@link kAPI_PARAM_OFFSETS}</tt>: Element offset.
 *		<li><tt>{@link kAPI_PARAM_DATA_TYPE}</tt>: Element tag data type.
 *		<li><tt>{@link kAPI_PARAM_GROUP_LIST}</tt>: List count.
 *	 </ul>
 * </ul>
 */
define( "kAPI_PARAM_GROUP_DATA",				'group-data' );

/**
 * List flag (int).
 *
 * This parameter holds a value that indicates how many times the summary group element
 * must be unwinded.
 */
define( "kAPI_PARAM_GROUP_LIST",				'group-list' );

/**
 * Default shape tag identifier (string).
 *
 * This parameter holds the native identifier of the default shape.
 */
define( "kAPI_SHAPE_TAG",						':shape-disp' );

/**
 * Offsets subset (boolean).
 *
 * This parameter is set if a subset of the offsets related to a criteria is provided, this
 * means that the search will not operate on all offsets.
 *
 * The outcome is that if not set, unindexed properties will be search-optimised with the
 * {@link kTAG_OBJECT_TAGS}, while if set, the {@link kTAG_OBJECT_OFFSETS} property will be
 * used.
 */
define( "kAPI_QUERY_OFFSETS",					'query-offsets' );


?>
