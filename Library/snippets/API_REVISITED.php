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
 * This tag identifies the operation.
 */
define( "kAPI_REQUEST_OPERATION",				'op' );

/**
 * Language.
 *
 * This tag identifies the default language.
 */
define( "kAPI_REQUEST_LANGUAGE",				'ln' );

/**
 * Parameters.
 *
 * This tag identifies the request parameters.
 */
define( "kAPI_REQUEST_PARAMETERS",				'pr' );

/*=======================================================================================
 *	RESPONSE																			*
 *======================================================================================*/

/**
 * Status section.
 *
 * This tag identifies the section which provides information on the outcome of the
 * operation, which includes the eventual error message if the operation failed.
 */
define( "kAPI_RESPONSE_STATUS",					'status' );

/**
 * Pagings section.
 *
 * This tag identifies the section which provides information on the number of affected
 * records, skipped records, the maximum number of returned records and the actual number of
 * returned records.
 */
define( "kAPI_RESPONSE_PAGING",					'paging' );

/**
 * Requests section.
 *
 * This tag identifies the section which holds the provided request, if required.
 */
define( "kAPI_RESPONSE_REQUEST",				'request' );

/**
 * Resultss section.
 *
 * This tag identifies the section which holds the operation results.
 */
define( "kAPI_RESPONSE_RESULTS",				'results' );

/**
 * Dictionarys section.
 *
 * This tag indicates the section containing the results data dictionary.
 */
define( "kAPI_RESULTS_DICTIONARY",				'dictionary' );

/*=======================================================================================
 *	STATUS SECTION																		*
 *======================================================================================*/

/**
 * State.
 *
 * This tag provides a general indication on the outcome of the operation.
 */
define( "kAPI_STATUS_STATE",					'state' );

/**
 * Code.
 *
 * This tag indicates an eventual status code in case of errors.
 */
define( "kAPI_STATUS_CODE",						'code' );

/**
 * File.
 *
 * This tag indicates the source filename where the eventual error was triggered.
 */
define( "kAPI_STATUS_FILE",						'file' );

/**
 * Line.
 *
 * This tag indicates the source file line where the eventual error was triggered.
 */
define( "kAPI_STATUS_LINE",						'line' );

/**
 * Message.
 *
 * This tag indicates the eventual error message.
 */
define( "kAPI_STATUS_MESSAGE",					'message' );

/**
 * Trace.
 *
 * This tag indicates the the eventual trace.
 */
define( "kAPI_STATUS_TRACE",					'trace' );

/*=======================================================================================
 *	PAGING SECTION																		*
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
 * This tag indicates the total number of records affected by the operation.
 */
define( "kAPI_PAGING_AFFECTED",					'affected' );

/*=======================================================================================
 *	DICTIONARY SECTION																	*
 *======================================================================================*/

/**
 * Collection.
 *
 * This tag indicates the record set collection name.
 */
define( "kAPI_DICTIONARY_COLLECTION",			'collection' );

/**
 * Reference count offset.
 *
 * This tag indicates the offset containing the reference count value related to the
 * collection of interest.
 */
define( "kAPI_DICTIONARY_REF_COUNT",			'count-offset' );

/**
 * Tags cross reference.
 *
 * This tag indicates the tags cross references list.
 */
define( "kAPI_DICTIONARY_TAGS",					'tags-xref' );

/**
 * Identifiers list.
 *
 * This tag indicates the list of identifiers.
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
 * This tag indicates the tags cluster list.
 */
define( "kAPI_DICTIONARY_CLUSTER",				'cluster' );

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
 * The service executed correctly.
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
 *	OPERATIONS																			*
 *======================================================================================*/

/**
 * Ping.
 *
 * This operation can be used to ping the service.
 */
define( "kAPI_OP_PING",							'ping' );

/**
 * List parameter constants.
 *
 * This service will return the list parameters and constants used by all services.
 */
define( "kAPI_OP_LIST_CONSTANTS",				'listConstants' );

/**
 * List operator parameters.
 *
 * This service will return the list of operators.
 */
define( "kAPI_OP_LIST_OPERATORS",				'listOperators' );

/**
 * List reference count parameters.
 *
 * This service will return the list of reference count indicators.
 */
define( "kAPI_OP_LIST_REF_COUNTS",				'listRefCounts' );

/**
 * Match tag labels.
 *
 * This service will return the list of tag labels matching the provided pattern and
 * language.
 */
define( "kAPI_OP_MATCH_TAG_LABELS",				'matchTagLabels' );

/**
 * Match term labels.
 *
 * This service will return the list of term labels matching the provided pattern and
 * language.
 */
define( "kAPI_OP_MATCH_TERM_LABELS",			'matchTermLabels' );

/**
 * Match tag by label.
 *
 * This service will return the list of tag objects whose label match the provided pattern
 * and language.
 */
define( "kAPI_OP_MATCH_TAG_BY_LABEL",			'matchTagsByLabel' );

/**
 * Match term by label.
 *
 * This service will return the list of tag objects whose label match the provided pattern
 * and language.
 */
define( "kAPI_OP_MATCH_TERM_BY_LABEL",			'matchTermsByLabel' );

/**
 * Get tag enumerations.
 *
 * This service will return the list of enumerated values related to the provided tag
 * reference.
 */
define( "kAPI_OP_GET_TAG_ENUMERATIONS",			'getTagEnumerations' );

/**
 * Get node enumerations.
 *
 * This service will return the list of enumerated values related to the provided node
 * reference.
 */
define( "kAPI_OP_GET_NODE_ENUMERATIONS",		'getNodeEnumerations' );

/**
 * Match units.
 *
 * This service will return the list of unit objects matching the provided search criteria.
 */
define( "kAPI_OP_MATCH_UNITS",					'matchUnits' );

/**
 * Get unit.
 *
 * This service will return the unit object matching the provided identifier.
 */
define( "kAPI_OP_GET_UNIT",						'getUnit' );

/*=======================================================================================
 *	REQUEST PARAMETERS																	*
 *======================================================================================*/

/**
 * Pattern (string).
 *
 * This parameter indicates the match pattern.
 */
define( "kAPI_PARAM_PATTERN",					'pattern' );

/**
 * Match operator (strings array).
 *
 * This parameter indicates the match operator.
 */
define( "kAPI_PARAM_OPERATOR",					'operator' );

/**
 * Reference count (string/array).
 *
 * This parameter lists the collection(s) in which the referenced tag must have at least one
 * value.
 */
define( "kAPI_PARAM_REF_COUNT",					'has-values' );

/**
 * Tag (string/int).
 *
 * This parameter represents either an integer referencing a tag sequence number or a string
 * referencing a tag native identifier.
 */
define( "kAPI_PARAM_TAG",						'tag' );

/**
 * Node (int).
 *
 * This parameter represents an integer referencing a node native identifier.
 */
define( "kAPI_PARAM_NODE",						'node' );

/**
 * Minimum value (int/float).
 *
 * This parameter indicates a range minimum value.
 */
define( "kAPI_PARAM_RANGE_MIN",					'min' );

/**
 * Maximum value (int/float).
 *
 * This parameter indicates a range maximum value.
 */
define( "kAPI_PARAM_RANGE_MAX",					'max' );

/**
 * Input type (string).
 *
 * This parameter indicates the type of the input.
 */
define( "kAPI_PARAM_INPUT_TYPE",				'input-type' );

/**
 * Search criteria (array).
 *
 * This parameter indicates the search criteria.
 */
define( "kAPI_PARAM_CRITERIA",					'criteria' );

/**
 * Object identifier (mixed).
 *
 * This parameter indicates an object identifier.
 */
define( "kAPI_PARAM_ID",						'id' );

/**
 * Results domain (string).
 *
 * This parameter indicates the object domain or type.
 */
define( "kAPI_PARAM_DOMAIN",					'domain' );

/**
 * Result format (string).
 *
 * This parameter indicates the result format.
 */
define( "kAPI_PARAM_DATA",						'data' );

/**
 * Result grouping (string).
 *
 * This parameter indicates the list of properties by which to group results.
 */
define( "kAPI_PARAM_GROUP",						'grouping' );

/**
 * Geographic shape (shape).
 *
 * This parameter indicates a geometric shape.
 */
define( "kAPI_PARAM_SHAPE",						'shape' );

/**
 * Geographic shape offset (string/int).
 *
 * This parameter indicates the offset of the geometric shape used for geographical queries.
 */
define( "kAPI_PARAM_SHAPE_OFFSET",				'shape-offset' );

/*=======================================================================================
 *	GENERIC FLAG REQUEST PARAMETERS														*
 *======================================================================================*/

/**
 * Log request (boolean).
 *
 * This flag determines whether the service should return a copy of the request.
 */
define( "kAPI_PARAM_LOG_REQUEST",				'log-request' );

/**
 * Trace (boolean).
 *
 * This parameter determines whether eventual errors should feature the exception trace.
 */
define( "kAPI_PARAM_LOG_TRACE",					'log-trace' );

/**
 * Recurse (boolean).
 *
 * This parameter determines whether the operation should be applied recursively.
 */
define( "kAPI_PARAM_RECURSE",					'recurse' );

/*=======================================================================================
 *	GENERIC RESPONSE PARAMETERS															*
 *======================================================================================*/

/**
 * Count (int).
 *
 * This parameter indicates a count.
 */
define( "kAPI_PARAM_RESPONSE_COUNT",			'count' );

/**
 * Points (int).
 *
 * This parameter indicates a geographical points count.
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
 * This parameter indicates the response data block type.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_TYPE",		'type' );

/**
 * Property name or label (string).
 *
 * This tag indicates the response data block name or label.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_NAME",		'name' );

/**
 * Property info or description (string).
 *
 * This tag indicates the response data block information or description.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_INFO",		'info' );

/**
 * Property display (string/array).
 *
 * This tag indicates the response data block display string.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_DISP",		'disp' );

/**
 * Property link (string/array).
 *
 * This tag indicates the response data block internet address.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_LINK",		'link' );

/**
 * Property service (array).
 *
 * This tag indicates the response data block service parameters list.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_SERV",		'serv' );

/**
 * Property sub-document (array).
 *
 * This tag indicates the response data block sub-document.
 */
define( "kAPI_PARAM_RESPONSE_FRMT_DOCU",		'docu' );

/*=======================================================================================
 *	RESPONSE DATA BLOCK TYPES															*
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
 * This tag indicates a link response, which refers to an internet address.
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

/*=======================================================================================
 *	ENUMERATED VALUE PARAMETERS															*
 *======================================================================================*/

/**
 * Term (string).
 *
 * This parameter is used when returning an enumerated value, it indicates the element's
 * term native identifier.
 */
define( "kAPI_RESULT_ENUM_TERM",				'term' );

/**
 * Node (int).
 *
 * This parameter is used when returning an enumerated value, it indicates the element's
 * node native identifier.
 */
define( "kAPI_RESULT_ENUM_NODE",				'node' );

/**
 * Label (string).
 *
 * This parameter is used when returning an enumerated value, it indicates the element's
 * label.
 */
define( "kAPI_RESULT_ENUM_LABEL",				'label' );

/**
 * Description (string).
 *
 * This parameter is used when returning an enumerated value, it indicates the element's
 * definition or description.
 */
define( "kAPI_RESULT_ENUM_DESCR",				'description' );

/**
 * Value (boolean).
 *
 * This flag is used when returning an enumerated value, if <tt>TRUE</tt>, the element can
 * be considered as an enumerated value, if not, the element is a category.
 */
define( "kAPI_RESULT_ENUM_VALUE",				'value' );

/*=======================================================================================
 *	RESULT FORMATS																		*
 *======================================================================================*/

/**
 * Table (string).
 *
 * This value indicates a table columns result format.
 */
define( "kAPI_RESULT_ENUM_DATA_COLUMN",			'column' );

/**
 * Formatted (string).
 *
 * This value indicates a result encoded using the current API.
 */
define( "kAPI_RESULT_ENUM_DATA_FORMAT",			'formatted' );

/**
 * Marker (string).
 *
 * This value indicates a result formatted as geographic markers.
 */
define( "kAPI_RESULT_ENUM_DATA_MARKER",			'marker' );

/**
 * Record (string).
 *
 * This value indicates a result formatted as a set of aggregated object records.
 */
define( "kAPI_RESULT_ENUM_DATA_RECORD",			'record' );

/*=======================================================================================
 *	COLLECTIONS																			*
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
 * String input (string).
 *
 * This parameter indicates a form string input.
 */
define( "kAPI_PARAM_INPUT_STRING",				'input-string' );

/**
 * Range input (string).
 *
 * This parameter indicates a form range input.
 */
define( "kAPI_PARAM_INPUT_RANGE",				'input-range' );

/**
 * Enumeration input (string).
 *
 * This parameter indicates a form enumneration input.
 */
define( "kAPI_PARAM_INPUT_ENUM",				'input-enum' );

/**
 * Shape input (string).
 *
 * This parameter indicates a form shape input.
 */
define( "kAPI_PARAM_INPUT_SHAPE",				'input-shape' );

/**
 * Default input (string).
 *
 * This parameter indicates a form default input.
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
