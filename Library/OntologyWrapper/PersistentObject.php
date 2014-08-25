<?php

/**
 * PersistentObject.php
 *
 * This file contains the definition of the {@link PersistentObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Wrapper;
use OntologyWrapper\OntologyObject;

/*=======================================================================================
 *																						*
 *								PersistentObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Query flags.
 *
 * This file contains the query flag definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Query.inc.php" );

/**
 * Import/Export API.
 *
 * This file contains the import/export API definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/ImportExport.xml.inc.php" );

/**
 * Persistent object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing objects that can
 * persist in a container and that are constituted by ontology offsets.
 *
 * The main purpose of this class is to add status and persistence management common to all
 * concrete derived classes.
 *
 * The class makes use of the {@link Status} trait to manage the object's state according to
 * actions:
 *
 * <ul>
 *	<li><tt>{@link isDirty}</tt>: This flag is set whenever any offset is modified, this
 *		status indicates that the contents of the object have changed since the lat time it
 *		was instantiated, loaded from a persistent store or committed to a persistent store.
 *	<li><tt>{@link isCommitted}</tt>: This flag is set whenever the object has been loaded
 *		or stored into a persistent container.
 * </ul>
 *
 * Objects derived from this class <em>must</em> define a constant called <em>kSEQ_NAME</em>
 * which provides a <em<string</em> representing the <em>default collection name</em> for
 * the current object: methods that commit or read objects of a specific class can then
 * resolve the collection given a database; this class does not declare this constant.
 *
 * All objects derived from this class feature the following offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: This represents the primary key of the object, it should
 *		be of a scalar type and will be unique within the collection in which the object is
 *		stored. This property is required.
 *	<li><tt>{@link kTAG_CLASS}</tt>: This represents the PHP class name of the current
 *		object, this information is used when loading objects from the persistent store.
 *		This property is required.
 *	<li><tt>{@link kTAG_MASTER}</tt>: This property holds a reference to another object
 *		derived from the same class which holds the information regarding the object.
 *		If an object features this property it means that it is an <em>alias</em>, which
 *		indicates that bthere may be a set of other objects pointing to the same
 *		<em>master</em>. This is used to prevent duplicate information from being stored
 *		in the database. This property is optional.
 *	<li><tt>{@link kTAG_TAG_COUNT}</tt>: This property holds an integer indicating the
 *		number of tag objects referencing the current object.
 *	<li><tt>{@link kTAG_TERM_COUNT}</tt>: This property holds an integer indicating the
 *		number of term objects referencing the current object.
 *	<li><tt>{@link kTAG_NODE_COUNT}</tt>: This property holds an integer indicating the
 *		number of node objects referencing the current object.
 *	<li><tt>{@link kTAG_EDGE_COUNT}</tt>: This property holds an integer indicating the
 *		number of edge objects referencing the current object.
 *	<li><tt>{@link kTAG_UNIT_COUNT}</tt>: This property holds an integer indicating the
 *		number of unit objects referencing the current object.
 *	<li><tt>{@link kTAG_ENTITY_COUNT}</tt>: This property holds an integer indicating the
 *		number of entity objects referencing the current object.
 *	<li><tt>{@link kTAG_OBJECT_TAGS}</tt>: This property is an array listing all tags used
 *		as leaf offsets in the current object. This means all tags referenced by object
 *		offsets which hold a value and are not structures. This property is managed
 *		internally.
 *	<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: This property is an array holding the same
 *		number of elements as {@link kTAG_OBJECT_TAGS}, in this case the array key is the
 *		tag sequence number and the value is the list of offset paths in which the tag was
 *		referenced in the current object as a leaf offset.
 *	<li><tt>{@link kTAG_OBJECT_REFERENCES}</tt>: This property is an array holding the list
 *		of object references featured by the object, the array is indexed by collection name
 *		and the values represent the native identifiers of the objects in the collection.
 *	<li><tt>{@link kTAG_RECORD_CREATED}</tt>: This property is the creation date time-stamp
 *		in the database native format. This attribute is automatically managed by the class.
 *	<li><tt>{@link kTAG_RECORD_MODIFIED}</tt>: This property is the last modification date
 *		time-stamp in the database native format. This attribute is automatically managed by
 *		the class.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/02/2014
 */
abstract class PersistentObject extends OntologyObject
{
	/**
	 * Default tags table.
	 *
	 * This static member holds the type and kind information regarding all default tags.
	 */
	public static $sDefaultTags = array
	(
		kTAG_NAMESPACE => array
		(
			kTAG_NID	=> ':namespace',
			kTAG_DATA_TYPE	=> kTYPE_REF_TERM,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_03,
									  kTYPE_SUMMARY,
									  kTYPE_LOOKUP )
		),
		kTAG_ID_LOCAL => array
		(
			kTAG_NID	=> ':id-local',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_06,
									  kTYPE_LOOKUP )
		),
		kTAG_ID_PERSISTENT => array
		(
			kTAG_NID	=> ':id-persistent',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_10,
									  kTYPE_LOOKUP )
		),
		kTAG_ID_VALID => array
		(
			kTAG_NID	=> ':id-valid',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_06 )
		),
		kTAG_ID_SEQUENCE => array
		(
			kTAG_NID	=> ':id-sequence',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ID_GRAPH => array
		(
			kTAG_NID	=> ':id-graph',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_DOMAIN => array
		(
			kTAG_NID	=> ':unit:domain',
			kTAG_DATA_TYPE	=> kTYPE_ENUM,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_FULL_TEXT_03,
									  kTYPE_SUMMARY )
		),
		kTAG_AUTHORITY => array
		(
			kTAG_NID	=> ':unit:authority',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_06,
									  kTYPE_SUMMARY )
		),
		kTAG_COLLECTION => array
		(
			kTAG_NID	=> ':unit:collection',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_06,
									  kTYPE_SUMMARY )
		),
		kTAG_IDENTIFIER => array
		(
			kTAG_NID	=> ':unit:identifier',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_10 )
		),
		kTAG_VERSION => array
		(
			kTAG_NID	=> ':unit:version',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_QUANTITATIVE )
		),
		kTAG_SYNONYM => array
		(
			kTAG_NID	=> ':synonym',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_06,
									  kTYPE_LIST )
		),
		kTAG_TAG => array
		(
			kTAG_NID	=> ':tag',
			kTAG_DATA_TYPE	=> kTYPE_REF_TAG,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_TAGS => array
		(
			kTAG_NID	=> ':tags',
			kTAG_DATA_TYPE	=> kTYPE_REF_TAG,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST )
		),
		kTAG_TERM => array
		(
			kTAG_NID	=> ':term',
			kTAG_DATA_TYPE	=> kTYPE_REF_TERM,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_TERMS => array
		(
			kTAG_NID	=> ':terms',
			kTAG_DATA_TYPE	=> kTYPE_REF_TERM,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST )
		),
		kTAG_SUBJECT => array
		(
			kTAG_NID	=> ':relationship:subject',
			kTAG_DATA_TYPE	=> kTYPE_REF_NODE,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_GRAPH_SUBJECT => array
		(
			kTAG_NID	=> ':relationship:graph-subject',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_PREDICATE => array
		(
			kTAG_NID	=> ':predicate',
			kTAG_DATA_TYPE	=> kTYPE_REF_TERM,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_OBJECT => array
		(
			kTAG_NID	=> ':relationship:object',
			kTAG_DATA_TYPE	=> kTYPE_REF_NODE,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_GRAPH_OBJECT => array
		(
			kTAG_NID	=> ':relationship:graph-object',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_ENTITY => array
		(
			kTAG_NID	=> ':entity',
			kTAG_DATA_TYPE	=> kTYPE_REF_ENTITY,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_MASTER => array
		(
			kTAG_NID	=> ':master',
			kTAG_DATA_TYPE	=> kTYPE_REF_SELF,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_CATEGORY => array
		(
			kTAG_NID	=> ':category',
			kTAG_DATA_TYPE	=> kTYPE_SET,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_FULL_TEXT_06,
									  kTYPE_SUMMARY )
		),
		kTAG_DATA_TYPE => array
		(
			kTAG_NID	=> ':type:data',
			kTAG_DATA_TYPE	=> kTYPE_ENUM,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_FULL_TEXT_03,
									  kTYPE_SUMMARY )
		),
		kTAG_DATA_KIND => array
		(
			kTAG_NID	=> ':type:kind',
			kTAG_DATA_TYPE	=> kTYPE_SET,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_FULL_TEXT_03,
									  kTYPE_SUMMARY )
		),
		kTAG_TERM_TYPE => array
		(
			kTAG_NID	=> ':type:term',
			kTAG_DATA_TYPE	=> kTYPE_SET,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_SUMMARY )
		),
		kTAG_NODE_TYPE => array
		(
			kTAG_NID	=> ':type:node',
			kTAG_DATA_TYPE	=> kTYPE_SET,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_FULL_TEXT_03,
									  kTYPE_SUMMARY )
		),
		kTAG_NAME => array
		(
			kTAG_NID	=> ':name',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_06 )
		),
		kTAG_LABEL => array
		(
			kTAG_NID	=> ':label',
			kTAG_DATA_TYPE	=> kTYPE_LANGUAGE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_06 )
		),
		kTAG_DEFINITION => array
		(
			kTAG_NID	=> ':definition',
			kTAG_DATA_TYPE	=> kTYPE_LANGUAGE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_03 )
		),
		kTAG_DESCRIPTION => array
		(
			kTAG_NID	=> ':description',
			kTAG_DATA_TYPE	=> kTYPE_LANGUAGE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_03 )
		),
		kTAG_NOTE => array
		(
			kTAG_NID	=> ':notes',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST,
									  kTYPE_FULL_TEXT_03 )
		),
		kTAG_EXAMPLE => array
		(
			kTAG_NID	=> ':examples',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST )
		),
		kTAG_UNIT_COUNT => array
		(
			kTAG_NID	=> ':ref-count:unit',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_QUANTITATIVE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_ENTITY_COUNT => array
		(
			kTAG_NID	=> ':ref-count:entity',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_QUANTITATIVE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_TAG_COUNT => array
		(
			kTAG_NID	=> ':ref-count:tag',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_QUANTITATIVE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_TERM_COUNT => array
		(
			kTAG_NID	=> ':ref-count:term',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_QUANTITATIVE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_NODE_COUNT => array
		(
			kTAG_NID	=> ':ref-count:node',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_QUANTITATIVE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_EDGE_COUNT => array
		(
			kTAG_NID	=> ':ref-count:edge',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_QUANTITATIVE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_TAG_OFFSETS => array
		(
			kTAG_NID	=> ':offset:tag',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST,
									  kTAG_PRIVATE_SEARCH,
									  kTAG_PRIVATE_MODIFY,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_TERM_OFFSETS => array
		(
			kTAG_NID	=> ':offset:term',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST,
									  kTAG_PRIVATE_SEARCH,
									  kTAG_PRIVATE_MODIFY,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_NODE_OFFSETS => array
		(
			kTAG_NID	=> ':offset:node',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST,
									  kTAG_PRIVATE_SEARCH,
									  kTAG_PRIVATE_MODIFY,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_EDGE_OFFSETS => array
		(
			kTAG_NID	=> ':offset:edge',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST,
									  kTAG_PRIVATE_SEARCH,
									  kTAG_PRIVATE_MODIFY,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_ENTITY_OFFSETS => array
		(
			kTAG_NID	=> ':offset:entity',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST,
									  kTAG_PRIVATE_SEARCH,
									  kTAG_PRIVATE_MODIFY,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_UNIT_OFFSETS => array
		(
			kTAG_NID	=> ':offset:unit',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST,
									  kTAG_PRIVATE_SEARCH,
									  kTAG_PRIVATE_MODIFY,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_OBJECT_TAGS => array
		(
			kTAG_NID	=> ':object-tags',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_LIST,
									  kTAG_PRIVATE_SEARCH,
									  kTAG_PRIVATE_MODIFY,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_OBJECT_OFFSETS => array
		(
			kTAG_NID	=> ':object-offsets',
			kTAG_DATA_TYPE	=> kTYPE_ARRAY,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_SEARCH,
									  kTAG_PRIVATE_MODIFY,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_OBJECT_REFERENCES => array
		(
			kTAG_NID	=> ':object-references',
			kTAG_DATA_TYPE	=> kTYPE_ARRAY,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_SEARCH,
									  kTAG_PRIVATE_MODIFY,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_TAG_STRUCT => array
		(
			kTAG_NID	=> ':tag:struct',
			kTAG_DATA_TYPE	=> kTYPE_REF_TAG,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_SEARCH,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_TAG_STRUCT_IDX => array
		(
			kTAG_NID	=> ':tag:struct-index',
			kTAG_DATA_TYPE	=> kTYPE_REF_TAG,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_SEARCH,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_MIN_VAL => array
		(
			kTAG_NID	=> ':min-val',
			kTAG_DATA_TYPE	=> kTYPE_FLOAT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_MIN_RANGE => array
		(
			kTAG_NID	=> ':min-range',
			kTAG_DATA_TYPE	=> kTYPE_FLOAT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_MAX_VAL => array
		(
			kTAG_NID	=> ':max-val',
			kTAG_DATA_TYPE	=> kTYPE_FLOAT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_MAX_RANGE => array
		(
			kTAG_NID	=> ':max-range',
			kTAG_DATA_TYPE	=> kTYPE_FLOAT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_PATTERN => array
		(
			kTAG_NID	=> ':grep',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_GEO_SHAPE => array
		(
			kTAG_NID	=> ':geo',
			kTAG_DATA_TYPE	=> kTYPE_SHAPE,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_SEARCH,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_RECORD_CREATED => array
		(
			kTAG_NID	=> ':record:created',
			kTAG_DATA_TYPE	=> kTYPE_TIME_STAMP,
			kTAG_DATA_KIND	=> array( kTYPE_QUANTITATIVE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_RECORD_MODIFIED => array
		(
			kTAG_NID	=> ':record:modified',
			kTAG_DATA_TYPE	=> kTYPE_TIME_STAMP,
			kTAG_DATA_KIND	=> array( kTYPE_QUANTITATIVE,
									  kTAG_PRIVATE_MODIFY )
		),
		kTAG_CONN_PROTOCOL => array
		(
			kTAG_NID	=> ':connection:protocol',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_CONN_HOST => array
		(
			kTAG_NID	=> ':connection:host',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_CONN_PORT => array
		(
			kTAG_NID	=> ':connection:port',
			kTAG_DATA_TYPE	=> kTYPE_INT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_CONN_USER => array
		(
			kTAG_NID	=> ':connection:user',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_SEARCH,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_CONN_PASS => array
		(
			kTAG_NID	=> ':connection:pass',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTAG_PRIVATE_SEARCH,
									  kTYPE_PRIVATE_DISPLAY )
		),
		kTAG_CONN_BASE => array
		(
			kTAG_NID	=> ':connection:database',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_CONN_COLL => array
		(
			kTAG_NID	=> ':connection:collection',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_CONN_OPTS => array
		(
			kTAG_NID	=> ':connection:options',
			kTAG_DATA_TYPE	=> kTYPE_ARRAY,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ENTITY_FNAME => array
		(
			kTAG_NID	=> ':entity:fname',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_10 )
		),
		kTAG_ENTITY_LNAME => array
		(
			kTAG_NID	=> ':entity:lname',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE,
									  kTYPE_FULL_TEXT_10 )
		),
		kTAG_ENTITY_TYPE => array
		(
			kTAG_NID	=> ':type:entity',
			kTAG_DATA_TYPE	=> kTYPE_SET,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_FULL_TEXT_03,
									  kTYPE_SUMMARY )
		),
		kTAG_ENTITY_KIND => array
		(
			kTAG_NID	=> ':kind:entity',
			kTAG_DATA_TYPE	=> kTYPE_SET,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_FULL_TEXT_03,
									  kTYPE_SUMMARY )
		),
		kTAG_ENTITY_ACRONYM => array
		(
			kTAG_NID	=> ':entity:acronym',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_LIST,
									  kTYPE_FULL_TEXT_10 )
		),
		kTAG_ENTITY_MAIL => array
		(
			kTAG_NID	=> ':entity:mail',
			kTAG_DATA_TYPE	=> kTYPE_TYPED_LIST,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ENTITY_EMAIL => array
		(
			kTAG_NID	=> ':entity:email',
			kTAG_DATA_TYPE	=> kTYPE_TYPED_LIST,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ENTITY_LINK => array
		(
			kTAG_NID	=> ':entity:url',
			kTAG_DATA_TYPE	=> kTYPE_TYPED_LIST,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ENTITY_PHONE => array
		(
			kTAG_NID	=> ':entity:phone',
			kTAG_DATA_TYPE	=> kTYPE_TYPED_LIST,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ENTITY_FAX => array
		(
			kTAG_NID	=> ':entity:fax',
			kTAG_DATA_TYPE	=> kTYPE_TYPED_LIST,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ENTITY_AFFILIATION => array
		(
			kTAG_NID	=> ':entity:affiliation',
			kTAG_DATA_TYPE	=> kTYPE_TYPED_LIST,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ENTITY_NATIONALITY => array
		(
			kTAG_NID	=> ':entity:nationality',
			kTAG_DATA_TYPE	=> kTYPE_ENUM,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_FULL_TEXT_03,
									  kTYPE_SUMMARY )
		),
		kTAG_ENTITY_VALID => array
		(
			kTAG_NID	=> ':entity:valid',
			kTAG_DATA_TYPE	=> kTYPE_REF_SELF,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ENTITY_PGP_KEY => array
		(
			kTAG_NID	=> ':entity:pgp-key',
			kTAG_DATA_TYPE	=> kTYPE_TEXT,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ENTITY_PGP_FINGERPRINT => array
		(
			kTAG_NID	=> ':entity:pgp-fingerprint',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_DISCRETE )
		),
		kTAG_ROLES => array
		(
			kTAG_NID	=> ':roles',
			kTAG_DATA_TYPE	=> kTYPE_STRING,
			kTAG_DATA_KIND	=> array( kTYPE_CATEGORICAL,
									  kTYPE_LIST )
		)
	);

	/**
	 * Status trait.
	 *
	 * In this class we handle the {@link is_committed()} flag.
	 */
	use	traits\Status;

		

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
	 * Objects derived from this class share the same constructor prototype and should not
	 * overload this method.
	 *
	 * The method accepts three parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: This may either be an array containing the object's
	 *		persistent attributes, or a reference to a {@link Wrapper} object. If this
	 *		parameter is <tt>NULL</tt>, the next parameter will be ignored.
	 *	<li><b>$theIdentifier</b>: This parameter represents the object identifier or the
	 *		object persistent attributes: in the first case it will used to select the
	 *		object from the wrapper provided in the previous parameter, in the second case,
	 *		it is assumed that the provided array holds the persistent attributes of an
	 *		object committed in the provided container.
	 *	<li><b>$doAssert</b>: This boolean parameter is relevant only if the first parameter
	 *		is a wrapper and the second is an identifier: if <tt>TRUE</tt>, the method will
	 *		raise an exception if the object was not found.
	 * </ul>
	 *
	 * The workflow is as follows:
	 *
	 * <ul>
	 *	<li><i>Empty object</i>: Both parameters are omitted.
	 *	<li><i>Empty object with wrapper</i>: The first parameter is a {@link Wrapper}
	 *		object and the second parameter is omitted. In this case an empty object is
	 *		instantiated, the committed status will not be set.
	 *	<li><i>Filled non committed object</i>: The first parameter is an array. In this
	 *		case the committed status is not set, but the object will have content.
	 *	<li><i>Filled committed object</i>: The first parameter is {@link Wrapper} object
	 *		and the second parameter is an array holding the object's persistent data. This
	 *		combination can be used when you want to load a persistent object with its
	 *		contents, in this case the object will be set committed.
	 *	<li><i>Load object from container</i>: The first parameter is a {@link Wrapper}
	 *		object and the second parameter is a scalar identifier. Use this combination to
	 *		load an object from the database, to check whether the object was loaded you
	 *		must call the {@link committed()} method or provide <tt>TRUE</tt> in the third
	 *		parameter to raise an exception if the object was not resolved; defaults to
	 *		<tt>TRUE</tt>.
	 * </ul>
	 *
	 * Any other combination will raise an exception.
	 *
	 * This constructor sets the {@link isCommitted()} flag, derived classes should first
	 * call the parent constructor, then they should set the {@link isInited()} flag.
	 *
	 * @param mixed					$theContainer		Data wrapper or properties.
	 * @param mixed					$theIdentifier		Object identifier or properties.
	 * @param boolean				$doAssert			Raise exception if not resolved.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 */
	public function __construct( $theContainer = NULL,
								 $theIdentifier = NULL,
								 $doAssert = TRUE )
	{
		//
		// Instantiate empty object.
		//
		if( $theContainer === NULL )
			parent::__construct();
		
		//
		// Load object attributes from array.
		//
		elseif( is_array( $theContainer ) )
			parent::__construct( $theContainer );
		
		//
		// Load object attributes from object.
		//
		elseif( ($theIdentifier === NULL)
		 && ($theContainer instanceof \ArrayObject)
		 && (! ($theContainer instanceof Wrapper)) )
			parent::__construct( $theContainer->getArrayCopy() );
		
		//
		// Handle wrapper.
		//
		elseif( $theContainer instanceof Wrapper )
		{
			//
			// Set dictionary.
			//
			$this->dictionary( $theContainer );
			
			//
			// Load object data.
			//
			if( is_array( $theIdentifier ) )
			{
				//
				// Call parent constructor.
				//
				parent::__construct( $theIdentifier );
				
				//
				// Set committed status.
				//
				$this->isCommitted( TRUE );
				
			} // Provided data.
			
			//
			// Resolve object.
			//
			elseif( $theIdentifier !== NULL )
			{
				//
				// Resolve collection.
				//
				$collection
					= static::ResolveCollection(
						static::ResolveDatabase( $theContainer ) );
			
				//
				// Find object.
				//
				$found = $collection->matchOne( array( kTAG_NID => $theIdentifier ),
												kQUERY_ARRAY );
				if( $found !== NULL )
				{
					//
					// Set committed status.
					//
					$this->isCommitted( TRUE );
				
					//
					// Call parent constructor.
					//
					parent::__construct( $found );
				
				} // Found.
				
				//
				// Not found.
				//
				elseif( $doAssert )
					throw new \Exception(
						"Cannot instantiate object: "
					   ."unresolved identifier [$theIdentifier]." );			// !@! ==>
				
				//
				// Empty object.
				//
				else
					parent::__construct();
			
			} // Provided identifier.
			
			//
			// Empty object.
			//
			else
				parent::__construct();
		
		} // Container connection.
		
		else
			throw new \Exception(
				"Cannot instantiate object: "
			   ."invalid container parameter type." );							// !@! ==>

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	insert																			*
	 *==================================================================================*/

	/**
	 * Insert the object
	 *
	 * This method will insert the current object into the provided persistent store only
	 * if it doesn't exist already:
	 *
	 * <ul>
	 *	<li>The object is not committed:
	 *	 <ul>
	 *		<li>The object already exists in the store: the method will do nothing.
	 *		<li>The object does not exist in the store: the object will be inserted.
	 *	 </ul>
	 *	<li>The object is committed: the method will do nothing.
	 * </ul>
	 *
	 * The method will return the inserted object native identifier or <tt>NULL</tt> if the
	 * the object was not inserted.
	 *
	 * Do not overload this method, you should overload the methods called in this method.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 * @return mixed				The object's native identifier or <tt>NULL</tt>.
	 *
	 * @uses isCommitted()
	 * @uses resolveWrapper()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses insertObject()
	 * @uses isDirty()
	 */
	public function insert( $theWrapper = NULL )
	{
		//
		// Skip committed object.
		//
		if( ! $this->isCommitted() )
		{
			//
			// Resolve wrapper.
			//
			$this->resolveWrapper( $theWrapper );
		
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $theWrapper ) );
		
			//
			// Insert.
			//
			$id = $this->insertObject( $collection );
			
			//
			// Handle inserted object.
			//
			if( $id !== NULL )
			{
				//
				// Set object status.
				//
				$this->isDirty( FALSE );
				$this->isCommitted( TRUE );
	
				return $id;															// ==>
			
			} // Object inserted.
		
		} // Not committed.
		
		return NULL;																// ==>
	
	} // insert.

	 
	/*===================================================================================
	 *	commit																			*
	 *==================================================================================*/

	/**
	 * Commit the object
	 *
	 * This method will insert or update the current object into the provided persistent
	 * store, the method expects a single parameter representing the wrapper, this can be
	 * omitted if the object was instantiated with a wrapper.
	 *
	 * The method will call the {@link commitObject()} method if the object was not
	 * committed and the {@link updateObject()} method if it was.
	 *
	 * Do not overload this method, you should overload the methods called in this method.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 * @return mixed				The object's native identifier.
	 *
	 * @uses resolveWrapper()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses isCommitted()
	 * @uses updateObject()
	 * @uses commitObject()
	 * @uses isDirty()
	 */
	public function commit( $theWrapper = NULL )
	{
		//
		// Resolve wrapper.
		//
		$this->resolveWrapper( $theWrapper );
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) );
		
		//
		// Commit.
		//
		$id = ( $this->isCommitted() )
			? $this->updateObject( $collection )
			: $this->commitObject( $collection );

		//
		// Set object status.
		//
		$this->isDirty( FALSE );
		$this->isCommitted( TRUE );
	
		return $id;																	// ==>
	
	} // commit.

	 
	/*===================================================================================
	 *	export																			*
	 *==================================================================================*/

	/**
	 * Export the object
	 *
	 * This method will export the object in the provided format using the provided options.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFormat</b>: The format in which the object should be exported:
	 *	 <ul>
	 *		<li><tt>xml</tt>: XML, the result of the method will be a SimpleXMLElement
	 *			object.
	 *	 </ul>
	 *	<li><b>$theContainer</b>: The container in which the object is to be exported; the
	 *		type of this value depends on the format:
	 *	 <ul>
	 *		<li><tt>xml</tt>: A SimpleXMLElement object.
	 *	 </ul>
	 *		Or <tt>NULL</tt> to have the method create it.
	 *	<li><b>$theWrapper</b>: The data wrapper related to the object, this parameter may
	 *		be omitted if the object was instantiated with a wrapper.
	 * </ul>
	 *
	 * The exported object is to be assumed a new object, this means that you should only
	 * use this method if the exported objects are to be inserted.
	 *
	 * @param string				$theFormat			Dump format.
	 * @param mixed					$theContainer		Dump container.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 * @return mixed				The exported object.
	 *
	 * @throws Exception
	 */
	public function export( $theFormat = 'xml', $theContainer = NULL, $theWrapper = NULL )
	{
		//
		// Resolve wrapper.
		//
		$this->resolveWrapper( $theWrapper );
	
		//
		// Collect untracked offsets.
		//
		$class = get_class( $this );
		$excluded = array_merge( $class::InternalOffsets(),
								 $class::ExternalOffsets(),
								 $class::DynamicOffsets() );
		
		//
		// Dump object.
		//
		switch( $theFormat )
		{
			case 'xml':
				//
				// Create container.
				//
				if( $theContainer === NULL )
					$theContainer = static::XMLRootElement();
				//
				// Validate container.
				//
				elseif( ! ($theContainer instanceof \SimpleXMLElement) )
					throw new \Exception(
						"Unable to dump object: "
					   ."invalid or unsupported XML container." );				// !@! ==>
				//
				// Load container.
				//
				$this->exportXMLObject( $theContainer, $theWrapper, $excluded );	// ==>
				
				break;
			
			default:
				throw new \Exception(
					"Unable to dump object: "
				   ."invalid or unsupported format [$theFormat]." );			// !@! ==>
		}
		
		return $theContainer;														// ==>
	
	} // export.

	

/*=======================================================================================
 *																						*
 *							PUBLIC MASTER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getMaster																		*
	 *==================================================================================*/

	/**
	 * Get master object
	 *
	 * This method will return the master object, if the current object is already a master,
	 * the method will return it; if the master cannot be located, the method will raise an
	 * exception.
	 *
	 * The first parameter is the wrapper in which the current object is, or will be,
	 * stored: if the current object has the {@link dictionary()}, this parameter may be
	 * omitted; if the wrapper cannot be resolved, the method will raise an exception.
	 *
	 * <em>This method assumes that the {@link kTAG_MASTER} property is correctly set, if
	 * that is not the case, the method will return the current object, which may not be
	 * what you want</em>.
	 *
	 * <em>Note that this method should only be called on objects that are
	 * {@link isCommitted()}, if that is not the case, the method will raise an
	 * exception</em>.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 *
	 * @access public
	 * @return PersistentObject		Master node.
	 *
	 * @throws Exception
	 */
	public function getMaster( $theWrapper = NULL )
	{
		//
		// Handle master.
		//
		if( ! $this->offsetExists( kTAG_MASTER ) )
			return $this;															// ==>
		
		//
		// Resolve wrapper.
		//
		if( $theWrapper === NULL )
		{
			//
			// Get current object's wrapper.
			//
			$theWrapper = $this->mDictionary;
			
			//
			// Check wrapper.
			//
			if( ! ($theWrapper instanceof Wrapper) )
				throw new \Exception(
					"Unable to resolve referenced: "
				   ."missing wrapper." );										// !@! ==>
		
		} // Wrapper not provided.
	
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) );
		
		return $collection->matchOne(
			array( kTAG_NID => $this->offsetGet( kTAG_MASTER ) ),
			kQUERY_ASSERT | kQUERY_OBJECT );										// ==>
	
	} // getMaster.

	 
	/*===================================================================================
	 *	setAlias																		*
	 *==================================================================================*/

	/**
	 * Signal object as alias
	 *
	 * This method can be used to set or reset the object {@link isAlias()} flag, this
	 * signals that the current object is an alias of the object referenced by the
	 * {@link kTAG_MASTER} offset value: to set the status pass <tt>TRUE</tt> in the
	 * parameter and <tt>FALSE</tt> to reset it.
	 *
	 * This method should only be called on non committed objects, once set, this status is
	 * immutable, so in that case the method will raise an exception.
	 *
	 * When resetting the status, the method will also remove the eventual
	 * {@link kTAG_MASTER} attribute.
	 *
	 * <em>Note that not any object can be set as alias: objects that can take this state
	 * must feature a method that selects their master object, so you should shadow this
	 * method in derived classes that do not implement the concept of master and alias.
	 *
	 * @param boolean				$doSet				<tt>TRUE</tt> to set.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @see kTAG_MASTER
	 *
	 * @uses isAlias()
	 * @uses isCommitted()
	 */
	public function setAlias( $doSet = TRUE )
	{
		//
		// Normalise flag.
		//
		$doSet = (boolean) $doSet;
		
		//
		// Set status.
		//
		if( $doSet )
		{
			//
			// Check if needed.
			//
			if( ! $this->isAlias() )
			{
				//
				// Check if committed.
				//
				if( ! $this->isCommitted() )
					$this->isAlias( $doSet );
			
				else
					throw new \Exception(
						"Cannot set alias status: "
					   ."the object is already committed." );					// !@! ==>
		
			} // Not an alias already.
		
		} // Set status
		
		//
		// Reset status.
		//
		else
		{
			//
			// Check if needed.
			//
			if( $this->isAlias() )
			{
				//
				// Check if committed.
				//
				if( ! $this->isCommitted() )
				{
					//
					// Set status.
					//
					$this->isAlias( $doSet );
					
					//
					// Remove master.
					//
					$this->offsetUnset( kTAG_MASTER );
				
				} // Not committed.
			
				else
					throw new \Exception(
						"Cannot reset alias status: "
					   ."the object is already committed." );					// !@! ==>
		
			} // Not an alias already.
		
		} // Reset status.
	
	} // setAlias.

	

/*=======================================================================================
 *																						*
 *							PUBLIC NAME MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Get object name
	 *
	 * This method should return the object name, this value represents a name or label that
	 * characterises the current object.
	 *
	 * Derived classes must overload this method.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 */
	abstract public function getName( $theLanguage );

		

/*=======================================================================================
 *																						*
 *								STATIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


		
	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * Delete an object
	 *
	 * This method will delete the object corresponding to the provided native identifier.
	 *
	 * Deleting objects is a static matter, since, in order to ensure referential integrity,
	 * it is necessary to traverse an object which is a mirror of the persistent image. The
	 * methods which actually delete the object are declared protected, so that it should
	 * not be easy to delete an incomplete object.
	 *
	 * This method should be called using the base class of the object one wants to delete,
	 * this means that either you know the class, or you may call this method from the
	 * object that you want to delete.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: The data wrapper.
	 *	<li><b>$theIdentifier</b>: The object native identifier.
	 * </ul>
	 *
	 * The method will first load the object from the persistent store and then it will
	 * call its protected {@link deleteObject()} method, returning:
	 *
	 * <ul>
	 *	<li><em>Native identifier</em>: If the object was deleted, the method will return
	 *		the deleted object's native identifier.
	 *	<li><tt>NULL</tt>: This value will be returned if the method was unable to locate
	 *		the object.
	 *	<li><tt>FALSE</tt>: This value will be returned if the object features reference
	 *		counts, this means that other objects reference it and for that reason it cannot
	 *		be deleted.
	 * </ul>
	 *
	 * When loading the object only the {@link kTAG_NID}, {@link kTAG_OBJECT_OFFSETS} and
	 * {@link kTAG_OBJECT_REFERENCES} will be loaded, since these are the offsets that
	 * contain the necessary information for maintaining referential integrity, if you need
	 * to handle other object offsets, you should overload the static
	 * {@link DeleteFieldsSelection()} method.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theIdentifier		Object native identifier.
	 *
	 * @static
	 * @return mixed				Identifier, <tt>NULL</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses DeleteFieldsSelection()
	 */
	static function Delete( Wrapper $theWrapper, $theIdentifier )
	{
		//
		// Check if wrapper is connected.
		//
		if( ! $theWrapper->isConnected() )
			throw new \Exception(
				"Unable to resolve collection: "
			   ."wrapper is not connected." );									// !@! ==>
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) );
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_NID => $theIdentifier );
		
		//
		// Select fields.
		//
		$fields = static::DeleteFieldsSelection();
		
		//
		// Resolve object.
		//
		$object = $collection->matchOne( $criteria, kQUERY_OBJECT, $fields );
		
		//
		// Delete object.
		//
		if( $object instanceof self )
			return $object->deleteObject();											// ==>
		
		return NULL;																// ==>
	
	} // Delete.

	 

/*=======================================================================================
 *																						*
 *									STATIC IMPORT UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Import																			*
	 *==================================================================================*/

	/**
	 * Import
	 *
	 * This method can be used to instantiate an object from an export container, the
	 * method expects the data wrapper and the export container.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theContainer		Export container.
	 *
	 * @static
	 * @return PersistentObject		The imported object.
	 *
	 * @throws Exception
	 */
	static function Import( Wrapper $theWrapper, $theContainer )
	{
		//
		// Handle XML export.
		//
		if( $theContainer instanceof \SimpleXMLElement )
			return static::ImportXML( $theWrapper, $theContainer );					// ==>
		
		throw new \Exception(
			"Unable to import: "
		   ."invalid or unsupported export format." );							// !@! ==>
	
	} // Import.

		
	/*===================================================================================
	 *	ImportXML																			*
	 *==================================================================================*/

	/**
	 * Import
	 *
	 * This method will instantiate an object from the provided XML container.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param SimpleXMLElement		$theContainer		Export container.
	 *
	 * @static
	 * @return PersistentObject		The imported object.
	 *
	 * @throws Exception
	 */
	static function ImportXML( Wrapper $theWrapper, \SimpleXMLElement $theContainer )
	{
		//
		// Parse by root node.
		//
		switch( $tmp = $theContainer->getName() )
		{
			case kIO_XML_META_TAG:
				$object = new Tag( $theWrapper );
				break;
		
			case kIO_XML_META_TERM:
				$object = new Term( $theWrapper );
				break;
		
			case kIO_XML_META_NODE:
				$object = new Node( $theWrapper );
				break;
		
			case kIO_XML_META_EDGE:
				$object = new Edge( $theWrapper );
				break;
		
			case kIO_XML_TRANS_UNITS:
			case kIO_XML_TRANS_USERS:
				$class = (string) $theContainer[ kIO_XML_ATTR_QUAL_CLASS ];
				if( strlen( $class ) )
					$object = new $class( $theWrapper );
				else
					throw new \Exception(
						"Unable to import: "
					   ."missing object class." );								// !@! ==>
				break;
			
			default:
				throw new \Exception(
					"Unable to import: "
				   ."invalid root node [$tmp]." );								// !@! ==>
		
		} // Parsed root node.
		
		//
		// Load data.
		//
		$object->loadXML( $theContainer );
		
		return $object;																// ==>
	
	} // ImportXML.

		

/*=======================================================================================
 *																						*
 *								STATIC PERSISTENCE UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	CreateIndexes																	*
	 *==================================================================================*/

	/**
	 * Create indexes
	 *
	 * This method will create the default indexes for the current class.
	 *
	 * In this class we index the following offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_MASTER}</tt>: This will speed the search for the master object.
	 *	<li><tt>{@link kTAG_OBJECT_TAGS}</tt>: This will help the selection of objects
	 *		based on the searched offsets.
	 *	<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: This is necessary to speed referencial
	 *		integrity.
	 * </ul>
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 *
	 * @static
	 * @return CollectionObject		The collection.
	 */
	static function CreateIndexes( DatabaseObject $theDatabase )
	{
		//
		// Get and open collection.
		//
		$collection = $theDatabase->collection( static::kSEQ_NAME );
		
		//
		// Set full-text search.
		//
		$collection->createIndex( array( kTAG_FULL_TEXT_10 => 'text',
										 kTAG_FULL_TEXT_06 => 'text',
										 kTAG_FULL_TEXT_03 => 'text' ),
								  array( "weights" => array(
								  			kTAG_FULL_TEXT_10 => 10,
								  			kTAG_FULL_TEXT_06 => 6,
								  			kTAG_FULL_TEXT_03 => 3 ),
								  		 "name" => "FULL_TEXT" ) );
		
		//
		// Set master.
		//
		$collection->createIndex( array( kTAG_MASTER => 1 ),
								  array( "name" => "MASTER",
								  		 "sparse" => TRUE ) );
		
		//
		// Set tags.
		//
		$collection->createIndex( array( kTAG_OBJECT_TAGS => 1 ),
								  array( "name" => "TAGS" ) );
		
		//
		// Set offsets.
		//
		$collection->createIndex( array( kTAG_OBJECT_OFFSETS => 1 ),
								  array( "name" => "OFFSETS" ) );
		
		//
		// Set reference counts.
		//
		$collection->createIndex( array( kTAG_TAG_COUNT => 1 ),
								  array( "name" => "TAG-REFS",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_TERM_COUNT => 1 ),
								  array( "name" => "TERM-REFS",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_NODE_COUNT => 1 ),
								  array( "name" => "NODE-REFS",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_EDGE_COUNT => 1 ),
								  array( "name" => "EDGE-REFS",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_UNIT_COUNT => 1 ),
								  array( "name" => "UNIT-REFS",
								  		 "sparse" => TRUE ) );
		$collection->createIndex( array( kTAG_ENTITY_COUNT => 1 ),
								  array( "name" => "ENTITY-REFS",
								  		 "sparse" => TRUE ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.


	/*===================================================================================
	 *	DeleteFieldsSelection															*
	 *==================================================================================*/

	/**
	 * Return delete fields selection
	 *
	 * This method should return the fields selection used to load the object to be deleted.
	 * By default we load the {@link kTAG_NID}, {@link kTAG_OBJECT_OFFSETS} and
	 * {@link kTAG_OBJECT_REFERENCES} properties, derived classes may overload this method
	 * to add other offsets.
	 *
	 * @static
	 * @return array				Delete object fields selection.
	 *
	 * @throws Exception
	 */
	static function DeleteFieldsSelection()
	{
		return array( kTAG_NID => TRUE,
					  kTAG_CLASS => TRUE,
					  kTAG_OBJECT_OFFSETS => TRUE,
					  kTAG_OBJECT_REFERENCES => TRUE );								// ==>
	
	} // DeleteFieldsSelection.

		

/*=======================================================================================
 *																						*
 *								STATIC RESOLUTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveDatabase																	*
	 *==================================================================================*/

	/**
	 * Resolve the database
	 *
	 * This method should return a {@link DatabaseObject} instance corresponding to the
	 * default database of the current class extracted from the provided {@link Wrapper}
	 * instance.
	 *
	 * Since we cannot declare this method abstract, we raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param boolean				$doAssert			Raise exception if unable.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return DatabaseObject		Database or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	static function ResolveDatabase( Wrapper $theWrapper, $doAssert = TRUE, $doOpen = TRUE )
	{
		throw new \Exception(
			"Unable to resolve database: "
		   ."this method must be implemented." );								// !@! ==>
	
	} // ResolveDatabase.

	 
	/*===================================================================================
	 *	ResolveCollection																*
	 *==================================================================================*/

	/**
	 * Resolve the collection
	 *
	 * This method should return a {@link CollectionObject} instance corresponding to the
	 * persistent store in which the current object was either read or will be inserted.
	 *
	 * The method expects the object to feature a constant, {@link kSEQ_NAME}, which serves
	 * the double purpose of providing the default collection name and the eventual sequence
	 * number index: the method will use this constant and the provided database reference
	 * to return the default {@link CollectionObject} instance.
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return CollectionObject		Collection or <tt>NULL</tt>.
	 */
	static function ResolveCollection( DatabaseObject $theDatabase, $doOpen = TRUE )
	{
		return $theDatabase->collection( static::kSEQ_NAME, $doOpen );				// ==>
	
	} // ResolveCollection.

		
	/*===================================================================================
	 *	ResolveCollectionByName															*
	 *==================================================================================*/

	/**
	 * Resolve collection by name
	 *
	 * Given a wrapper and a collection name, this method will return a collection
	 * reference.
	 *
	 * If the wrapper is not connected, or if the collection could not be resolved, the
	 * method will raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param string				$theCollection		Collection name.
	 *
	 * @static
	 * @return CollectionObject		The collection reference.
	 *
	 * @throws Exception
	 */
	static function ResolveCollectionByName( Wrapper $theWrapper, $theCollection )
	{
		//
		// Check if wrapper is connected.
		//
		if( ! $theWrapper->isConnected() )
			throw new \Exception(
				"Unable to resolve collection: "
			   ."wrapper is not connected." );									// !@! ==>
		
		//
		// Resolve collection.
		//
		switch( (string) $theCollection )
		{
			case Tag::kSEQ_NAME:
				return Tag::ResolveCollection(
						Tag::ResolveDatabase( $theWrapper ) );						// ==>
				
			case Term::kSEQ_NAME:
				return Term::ResolveCollection(
						Term::ResolveDatabase( $theWrapper ) );						// ==>
				
			case Node::kSEQ_NAME:
				return Node::ResolveCollection(
						Node::ResolveDatabase( $theWrapper ) );						// ==>
				
			case Edge::kSEQ_NAME:
				return Edge::ResolveCollection(
						Edge::ResolveDatabase( $theWrapper ) );						// ==>
				
			case User::kSEQ_NAME:
				return User::ResolveCollection(
						User::ResolveDatabase( $theWrapper ) );						// ==>
				
			case UnitObject::kSEQ_NAME:
				return UnitObject::ResolveCollection(
						UnitObject::ResolveDatabase( $theWrapper ) );				// ==>
			
			default:
				throw new \Exception(
					"Cannot resolve collection: "
				   ."invalid collection name [$theCollection]." );				// !@! ==>
		
		} // Parsed collection name.
	
	} // ResolveCollectionByName.

	 
	/*===================================================================================
	 *	ResolveRefCountTag																*
	 *==================================================================================*/

	/**
	 * Resolve reference count tag
	 *
	 * Given a collection name, this method will return the tag sequence number
	 * corresponding to the offset holding the number of objects, stored in the provided
	 * collection reference, that reference the current object.
	 *
	 * If the tag could not be resolved, the method will raise an exception.
	 *
	 * @param string				$theCollection		Collection name.
	 *
	 * @static
	 * @return integer				Tag sequence number.
	 *
	 * @throws Exception
	 */
	static function ResolveRefCountTag( $theCollection )
	{
		//
		// Select reference count tag according to provided collection.
		//
		switch( (string) $theCollection )
		{
			case Tag::kSEQ_NAME:
				return kTAG_TAG_COUNT;												// ==>
		
			case Term::kSEQ_NAME:
				return kTAG_TERM_COUNT;												// ==>
		
			case Node::kSEQ_NAME:
				return kTAG_NODE_COUNT;												// ==>
	
			case Edge::kSEQ_NAME:
				return kTAG_EDGE_COUNT;												// ==>
		
			case User::kSEQ_NAME:
				return kTAG_ENTITY_COUNT;											// ==>
		
			case UnitObject::kSEQ_NAME:
				return kTAG_UNIT_COUNT;												// ==>
		
			default:
				throw new \Exception(
					"Cannot resolve reference count tag: "
				   ."invalid collection name [$theCollection]." );				// !@! ==>
		
		} // Parsed collection name.
	
	} // ResolveRefCountTag.

	 
	/*===================================================================================
	 *	ResolveOffsetsTag																*
	 *==================================================================================*/

	/**
	 * Resolve offsets tag
	 *
	 * Given a collection name, this method will return the tag sequence number
	 * corresponding to the tag object offset holding the set of offsets in which the tag
	 * was used by objects stored in the provided collection.
	 *
	 * If the tag could not be resolved, the method will raise an exception.
	 *
	 * @param string				$theCollection		Collection name.
	 *
	 * @static
	 * @return integer				Tag sequence number.
	 *
	 * @throws Exception
	 */
	static function ResolveOffsetsTag( $theCollection )
	{
		//
		// Select offsets tag according to provided collection.
		//
		switch( (string) $theCollection )
		{
			case Tag::kSEQ_NAME:
				return kTAG_TAG_OFFSETS;											// ==>
		
			case Term::kSEQ_NAME:
				return kTAG_TERM_OFFSETS;											// ==>
		
			case Node::kSEQ_NAME:
				return kTAG_NODE_OFFSETS;											// ==>
	
			case Edge::kSEQ_NAME:
				return kTAG_EDGE_OFFSETS;											// ==>
		
			case User::kSEQ_NAME:
				return kTAG_ENTITY_OFFSETS;											// ==>
		
			case UnitObject::kSEQ_NAME:
				return kTAG_UNIT_OFFSETS;											// ==>
		
			default:
				throw new \Exception(
					"Cannot resolve offsets tag: "
				   ."invalid collection name [$theCollection]." );				// !@! ==>
		
		} // Parsed collection name.
	
	} // ResolveOffsetsTag.

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ExternalOffsets																	*
	 *==================================================================================*/

	/**
	 * Return external offsets
	 *
	 * In this class we return the following offsets:
	 *
	 * <ul>
	 *	<li><em>Reference counting offsets</em>:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_TAG_COUNT}</tt>: Number of tags referencing the current
	 *			object.
	 *		<li><tt>{@link kTAG_TERM_COUNT}</tt>: Number of terms referencing the current
	 *			object.
	 *		<li><tt>{@link kTAG_NODE_COUNT}</tt>: Number of nodes referencing the current
	 *			object.
	 *		<li><tt>{@link kTAG_EDGE_COUNT}</tt>: Number of edges referencing the current
	 *			object.
	 *		<li><tt>{@link kTAG_UNIT_COUNT}</tt>: Number of units referencing the current
	 *			object.
	 *		<li><tt>{@link kTAG_ENTITY_COUNT}</tt>: Number of entities referencing the
	 *			current object.
	 *	 </ul>
	 *	<li><em>Offset tracking offsets</em>:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_TAG_OFFSETS}</tt>: Tag object offsets.
	 *		<li><tt>{@link kTAG_TERM_OFFSETS}</tt>: Term object offsets.
	 *		<li><tt>{@link kTAG_NODE_OFFSETS}</tt>: Node object offsets.
	 *		<li><tt>{@link kTAG_EDGE_OFFSETS}</tt>: Edge object offsets.
	 *		<li><tt>{@link kTAG_UNIT_OFFSETS}</tt>: Unit object offsets.
	 *		<li><tt>{@link kTAG_ENTITY_OFFSETS}</tt>: Entity object offsets.
	 *	 </ul>
	 *	<li><em>Time-stamp offsets</em>:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_RECORD_CREATED}</tt>: Record creation stamp.
	 *		<li><tt>{@link kTAG_RECORD_MODIFIED}</tt>: Record modification stamp.
	 *	 </ul>
	 * </ul>
	 *
	 * @static
	 * @return array				List of external offsets.
	 */
	static function ExternalOffsets()
	{
		return array_merge(
			parent::ExternalOffsets(),
			array( kTAG_TAG_COUNT, kTAG_TERM_COUNT,
				   kTAG_NODE_COUNT, kTAG_EDGE_COUNT,
				   kTAG_UNIT_COUNT, kTAG_ENTITY_COUNT ),
			array( kTAG_TAG_OFFSETS, kTAG_TERM_OFFSETS,
				   kTAG_NODE_OFFSETS, kTAG_EDGE_OFFSETS,
				   kTAG_UNIT_OFFSETS, kTAG_ENTITY_OFFSETS ),
			array( kTAG_RECORD_CREATED, kTAG_RECORD_MODIFIED ) );					// ==>
	
	} // ExternalOffsets.

	 
	/*===================================================================================
	 *	DynamicOffsets																	*
	 *==================================================================================*/

	/**
	 * Return dynamic offsets
	 *
	 * In this class we return the offsets used to manage the object's properties:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_OBJECT_TAGS}</tt>: List of tags referenced by the object's
	 *		properties.
	 *	<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: List of object property offset paths
	 *		grouped by tag.
	 *	<li><tt>{@link kTAG_OBJECT_REFERENCES}</tt>: List of objects referenced by the
	 *		the current object grouped by collection.
	 * </ul>
	 *
	 * @static
	 * @return array				List of dynamic offsets.
	 */
	static function DynamicOffsets()
	{
		return array_merge(
			parent::DynamicOffsets(),
			array( kTAG_OBJECT_TAGS,  kTAG_OBJECT_OFFSETS,
				   kTAG_OBJECT_REFERENCES ) );										// ==>
	
	} // DynamicOffsets.

	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * The default offsets are those that the object is expected to manage, besides these,
	 * objects accept any other kind of offset.
	 *
	 * In this class we return:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_RECORD_CREATED}</tt>: Record creation time.
	 *	<li><tt>{@link kTAG_RECORD_MODIFIED}</tt>: Record last modification time.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_RECORD_CREATED, kTAG_RECORD_MODIFIED ) );	// ==>
	
	} // DefaultOffsets.

		
	/*===================================================================================
	 *	OffsetTypes																		*
	 *==================================================================================*/

	/**
	 * Resolve offset types
	 *
	 * In this class we hard-code the data of the default tags using the static
	 * {@link $sDefaultTags} member, this is to allow loading the data dictionary on a
	 * pristine system.
	 *
	 * If the provided offset is not among the ones handled in this method, it will call
	 * the parent method.
	 *
	 * @param DictionaryObject		$theDictionary		Data dictionary.
	 * @param mixed					$theOffset			Offset.
	 * @param string				$theType			Receives data type.
	 * @param array					$theKind			Receives data kind.
	 * @param mixed					$theMin				Receives minimum data range.
	 * @param mixed					$theMax				Receives maximum data range.
	 * @param string				$thePattern			Receives data pattern.
	 * @param boolean				$doAssert			If <tt>TRUE</tt> assert offset.
	 *
	 * @static
	 * @return mixed				<tt>TRUE</tt> if the tag was resolved, or <tt>NULL</tt>.
	 */
	static function OffsetTypes( DictionaryObject $theDictionary,
												  $theOffset,
												 &$theType, &$theKind,
												 &$theMin, &$theMax, &$thePattern,
												  $doAssert = TRUE )
	{
		//
		// Handle default tags.
		//
		if( array_key_exists( $theOffset, self::$sDefaultTags ) )
		{
			//
			// Set type.
			//
			$theType = self::$sDefaultTags[ $theOffset ][ kTAG_DATA_TYPE ];

			//
			// Set kind.
			//
			$theKind = ( array_key_exists( kTAG_DATA_KIND,
										   self::$sDefaultTags[ $theOffset ] ) )
					 ? self::$sDefaultTags[ $theOffset ][ kTAG_DATA_KIND ]
					 : Array();

			//
			// Set range.
			//
			$theMin  = ( array_key_exists( kTAG_MIN_RANGE,
										   self::$sDefaultTags[ $theOffset ] ) )
					 ? self::$sDefaultTags[ $theOffset ][ kTAG_MIN_RANGE ]
					 : NULL;
			$theMax  = ( array_key_exists( kTAG_MAX_RANGE,
										   self::$sDefaultTags[ $theOffset ] ) )
					 ? self::$sDefaultTags[ $theOffset ][ kTAG_MAX_RANGE ]
					 : NULL;

			//
			// Set pattern.
			//
			$thePattern = ( array_key_exists( kTAG_PATTERN,
										   self::$sDefaultTags[ $theOffset ] ) )
					 ? self::$sDefaultTags[ $theOffset ][ kTAG_PATTERN ]
					 : NULL;
			
			return TRUE;															// ==>
		
		} // Default offset.
		
		return parent::OffsetTypes(
					$theDictionary, $theOffset,
					$theType, $theKind, $theMin, $theMax, $thePattern,
					$doAssert );													// ==>
		
	} // OffsetTypes.


	/*===================================================================================
	 *	GetReferenceKey																	*
	 *==================================================================================*/

	/**
	 * Return reference key
	 *
	 * The reference key is the offset that will be used when storing a set of objects into
	 * a multi class matrix. By default we use the native identifier, but in some cases
	 * other required and unique identifiers may be used for this purpose.
	 *
	 * In this class we use {@link kTAG_NID}.
	 *
	 * @static
	 * @return string				Key offset.
	 */
	static function GetReferenceKey()								{	return kTAG_NID;	}

		
	/*===================================================================================
	 *	GetReferenceTypes																*
	 *==================================================================================*/

	/**
	 * Get reference types
	 *
	 * This method will return the list of types that represent an object reference.
	 *
	 * @static
	 * @return array				List of reference types.
	 */
	static function GetReferenceTypes()
	{
		return array( kTYPE_REF_TAG, kTYPE_REF_TERM, kTYPE_REF_NODE, kTYPE_REF_EDGE,
					  kTYPE_REF_ENTITY, kTYPE_REF_UNIT,
					  kTYPE_REF_SELF,
					  kTYPE_ENUM, kTYPE_SET );										// ==>
	
	} // GetReferenceTypes.

		
	/*===================================================================================
	 *	GetReferenceCounts																*
	 *==================================================================================*/

	/**
	 * Get reference counts
	 *
	 * This method will return the list of tags that represent an object reference counts.
	 *
	 * @static
	 * @return array				List of reference counts.
	 */
	static function GetReferenceCounts()
	{
		return array
		(
			kTAG_TAG_COUNT, kTAG_TERM_COUNT, kTAG_NODE_COUNT,
			kTAG_EDGE_COUNT, kTAG_UNIT_COUNT, kTAG_ENTITY_COUNT
		);																			// ==>
	
	} // GetReferenceCounts.

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before setting it
	 *
	 * We overload this method to prevent modifying the global and native identifiers if the
	 * object is committed, {@link isCommitted()}.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 * @uses lockedOffsets()
	 */
	protected function preOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Resolve offset.
		//
		$ok = parent::preOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
		{
			//
			// Check if committed.
			//
			if( $this->isCommitted() )
			{
				//
				// Check immutable tags.
				//
				if( in_array( $theOffset, $this->lockedOffsets() ) )
					throw new \Exception(
						"Cannot set the [$theOffset] offset: "
					   ."the object is committed." );							// !@! ==>
		
			} // Object is committed.
			
			//
			// Parse offsets.
			//
			switch( $theOffset )
			{
				//
				// Check container structure.
				//
				case kTAG_TAG_STRUCT:
					$tag
						= $this->mDictionary
							->getObject(
								$this->resolveOffset( $theValue, TRUE ) );
					if( $tag !== NULL )
					{
						if( array_key_exists( kTAG_DATA_TYPE, $tag ) )
						{
							if( $tag[ kTAG_DATA_TYPE ] != kTYPE_STRUCT )
								throw new \Exception(
									"Cannot set the [$theOffset] offset: "
								   ."[$theValue] is not a structure." );		// !@! ==>
						}
						else
							throw new \Exception(
								"Cannot set the [$theOffset] offset: "
							   ."[$theValue] is missing its data type." );		// !@! ==>
					}
					else
						throw new \Exception(
							"Cannot set the [$theOffset] offset: "
						   ."missing data dictionary." );						// !@! ==>
					break;
			
			} // Parsed offsets.
		
		} // Intercepted by preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * We overload the parent method to set the {@link isDirty()} status.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @uses isDirty()
	 * @uses setAlias()
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Resolve offset.
		//
		$ok = parent::postOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
			$this->isDirty( TRUE );
		
		//
		// Handle master.
		//
		if( $theOffset == kTAG_MASTER )
			$this->setAlias( TRUE );
		
		return $ok;																	// ==>
		
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	preOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before deleting it
	 *
	 * We overload this method to prevent modifying the global and native identifiers if the
	 * object is committed, {@link isCommitted()}.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> delete offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 * @uses lockedOffsets()
	 */
	protected function preOffsetUnset( &$theOffset )
	{
		//
		// Resolve offset.
		//
		$ok = parent::preOffsetUnset( $theOffset );
		if( $ok === NULL )
		{
			//
			// Check if committed.
			//
			if( $this->isCommitted() )
			{
				//
				// Check immutable tags.
				//
				if( in_array( $theOffset, $this->lockedOffsets() ) )
					throw new \Exception(
						"Cannot delete the [$theOffset] offset: "
					   ."the object is committed." );							// !@! ==>
		
			} // Object is committed.
		
		} // Intercepted by preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetUnset.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * This method can be used to manage the object after calling the
	 * {@link ArrayObject::OffsetUnset()} method.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @uses isDirty()
	 * @uses setAlias()
	 */
	protected function postOffsetUnset( &$theOffset )
	{
		//
		// Resolve offset.
		//
		$ok = parent::postOffsetUnset( $theOffset );
		if( $ok === NULL )
			$this->isDirty( TRUE );
		
		//
		// Handle master.
		//
		if( $theOffset == kTAG_MASTER )
			$this->setAlias( FALSE );
		
		return $ok;																	// ==>
		
	} // postOffsetUnset.

		

/*=======================================================================================
 *																						*
 *							PROTECTED PERSISTENCE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	insertObject																	*
	 *==================================================================================*/

	/**
	 * Insert the object
	 *
	 * This method will insert the current object into the provided persistent store only if
	 * not yet committed, the method  will perform the following steps:
	 *
	 * <ul>
	 *	<li>We call the <tt>{@link preCommit()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link preCommitPrepare()}</tt>: Prepare the object before committing.
	 *		<li><tt>{@link preCommitTraverse()}</tt>: Traverse the object's properties
	 *			validating formats and references.
	 *		<li><tt>{@link preCommitFinalise()}</tt>: Load the dynamic object properties and
	 *			compute the eventual object identifiers.
	 *	 </ul>
	 *	<li>We check whether the object already exists, if that is the case we exit.:
	 *	<li>We pass the current object to the collection's
	 *		{@link CollectionObject::commit()} method and recuperate the identifier.
	 *	<li>We call the <tt>{@link postInsert()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link postCommitReferences()}</tt>: Update object references.
	 *		<li><tt>{@link postCommitTags()}</tt>: Update object tags.
	 *	 </ul>
	 *	<li>We set the object {@link isCommitted()} and reset the {@link isDirty()} status.
	 *	<li>We return the object's identifier.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * Note that this method can only be called with objects that do not have native
	 * identifiers assigned by the database, oin that case all objects will appear to be
	 * new and will be inserted.
	 *
	 * Do not overload this method, you should overload the methods called in this method.
	 *
	 * @param CollectionObject		$theCollection		Data collection.
	 *
	 * @access protected
	 * @return mixed				The object's native identifie or <tt>NULL</tt>.
	 *
	 * @uses preCommit()
	 * @uses postInsert()
	 */
	protected function insertObject( CollectionObject $theCollection )
	{
		//
		// Prepare object.
		//
		$this->preCommit( $tags, $refs );
		
		//
		// Set creation time stamp.
		//
		if( ! $this->offsetExists( kTAG_RECORD_CREATED ) )
			$this->offsetSet( kTAG_RECORD_CREATED, $theCollection->getTimeStamp() );
		
		//
		// Check if the object exists.
		//
		if( ($id = $this->offsetGet( kTAG_NID )) !== NULL )
		{
			//
			// Check object.
			//
			if( $theCollection->matchOne( array( kTAG_NID => $id ), kQUERY_COUNT ) )
				return NULL;														// ==>
		
		} // Has native identifier.
	
		//
		// Commit.
		//
		$id = $theCollection->commit( $this );

		//
		// Set native identifier if generated.
		//
		if( ! $this->offsetExists( kTAG_NID ) )
			$this->offsetSet( kTAG_NID, $id );
	
		//
		// Update tag offsets and object references.
		//
		$tags = $this->offsetGet( kTAG_OBJECT_OFFSETS );
		$refs = $this->offsetGet( kTAG_OBJECT_REFERENCES );
		$this->postInsert( $tags, $refs );
		
		//
		// Handle tag value ranges.
		//
		$this->updateTagRanges();
	
		return $id;																	// ==>
	
	} // insertObject.

	 
	/*===================================================================================
	 *	commitObject																	*
	 *==================================================================================*/

	/**
	 * Commit the object
	 *
	 * This method will insert the current object into the provided persistent store, the
	 * method  will perform the following steps:
	 *
	 * <ul>
	 *	<li>We call the <tt>{@link preCommit()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link preCommitPrepare()}</tt>: Prepare the object before committing.
	 *		<li><tt>{@link preCommitTraverse()}</tt>: Traverse the object's properties
	 *			validating formats and references.
	 *		<li><tt>{@link preCommitFinalise()}</tt>: Load the dynamic object properties and
	 *			compute the eventual object identifiers.
	 *	 </ul>
	 *	<li>We pass the current object to the collection's
	 *		{@link CollectionObject::commit()} method and recuperate the identifier.
	 *	<li>We call the <tt>{@link postInsert()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link postCommitReferences()}</tt>: Update object references.
	 *		<li><tt>{@link postCommitTags()}</tt>: Update object tags.
	 *	 </ul>
	 *	<li>We set the object {@link isCommitted()} and reset the {@link isDirty()} status.
	 *	<li>We return the object's identifier.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * Do not overload this method, you should overload the methods called in this method.
	 *
	 * @param CollectionObject		$theCollection		Data collection.
	 *
	 * @access protected
	 * @return mixed				The object's native identifier.
	 *
	 * @uses preCommit()
	 * @uses postInsert()
	 */
	protected function commitObject( CollectionObject $theCollection )
	{
		//
		// Prepare object.
		//
		$this->preCommit( $tags, $refs );
		
		//
		// Set creation time stamp.
		//
		if( ! $this->offsetExists( kTAG_RECORD_CREATED ) )
			$this->offsetSet( kTAG_RECORD_CREATED, $theCollection->getTimeStamp() );
	
		//
		// Commit.
		//
		$id = $theCollection->commit( $this );

		//
		// Set native identifier if generated.
		//
		if( ! $this->offsetExists( kTAG_NID ) )
			$this->offsetSet( kTAG_NID, $id );
	
		//
		// Update tag offsets and object references.
		//
		$tags = $this->offsetGet( kTAG_OBJECT_OFFSETS );
		$refs = $this->offsetGet( kTAG_OBJECT_REFERENCES );
		$this->postInsert( $tags, $refs );
		
		//
		// Handle tag value ranges.
		//
		$this->updateTagRanges();
	
		return $id;																	// ==>
	
	} // commitObject.

	 
	/*===================================================================================
	 *	updateObject																	*
	 *==================================================================================*/

	/**
	 * Update the object
	 *
	 * This method will update the current object in the provided persistent store, the
	 * method  will perform the following steps:
	 *
	 * <ul>
	 *	<li>We call the <tt>{@link preCommit()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link preCommitPrepare()}</tt>: Prepare the object before committing.
	 *		<li><tt>{@link preCommitTraverse()}</tt>: Traverse the object's properties
	 *			validating formats and references.
	 *		<li><tt>{@link preCommitFinalise()}</tt>: Load the dynamic object properties and
	 *			compute the eventual object identifiers.
	 *	 </ul>
	 *	<li>We pass the current object to the collection's
	 *		{@link CollectionObject::save()} method and recuperate the identifier.
	 *	<li>We call the <tt>{@link postUpdate()}</tt> method that is responsible of updating
	 *		object references and object tags.
	 *	 </ul>
	 *	<li>We return the object's identifier.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * Do not overload this method, you should overload the methods called in this method.
	 *
	 * @param CollectionObject		$theCollection		Data collection.
	 *
	 * @access protected
	 * @return mixed				The object's native identifier.
	 *
	 * @see kTAG_OBJECT_OFFSETS kTAG_OBJECT_REFERENCES
	 *
	 * @uses preCommit()
	 * @uses postUpdate()
	 */
	protected function updateObject( CollectionObject $theCollection )
	{
		//
		// Load persistent image.
		//
		$old
			= $theCollection->matchOne(
				array( kTAG_NID => $this->offsetGet( kTAG_NID ) ),
				kQUERY_ASSERT | kQUERY_ARRAY,
				array( kTAG_OBJECT_OFFSETS => TRUE,
					   kTAG_OBJECT_REFERENCES => TRUE ) );
		
		//
		// Prepare object.
		//
		$this->preCommit( $tags, $refs );
		
		//
		// Set modification time stamp.
		//
		$this->offsetSet( kTAG_RECORD_MODIFIED, $theCollection->getTimeStamp() );
	
		//
		// Commit.
		//
		$id = $theCollection->save( $this );
	
		//
		// Update references.
		//
		$this->postUpdate( $old[ kTAG_OBJECT_OFFSETS ], $old[ kTAG_OBJECT_REFERENCES ] );
		
		//
		// Handle tag value ranges.
		//
		$this->updateTagRanges();
	
		return $id;																	// ==>
	
	} // updateObject.

	 
	/*===================================================================================
	 *	deleteObject																	*
	 *==================================================================================*/

	/**
	 * Delete the object
	 *
	 * This method will delete the current object from its persistent store, the method is
	 * declared protected, because it should not be called by clients: rather, the static
	 * {@link Delete()} method should be used for this purpose.
	 *
	 * Deleting an object involves much more than simply removing the object from its
	 * container: all reference counts and tag offsets must be updated to maintain
	 * referential integrity, for this reason, this method should be called by an object
	 * which is {@link isCommitted()} and not {@link isDirty()}; if that is not the case, an
	 * exception will be raised.
	 *
	 * The object only needs the {@link kTAG_NID}, {@link kTAG_OBJECT_OFFSETS} and
	 * {@link kTAG_OBJECT_REFERENCES} properties, these are the offsets loaded by the
	 * static {@link Delete()} method which calls this one; if derived classes need other
	 * properties, they should also modify the static {@link Delete()} method accordingly.
	 *
	 * This method follows a workflow similar to the {@link commit()} method:
	 *
	 * <ul>
	 *	<li>We check whether the object is committed and not dirty.
	 *	<li>We resolve the object's collection.
	 *	<li>We call the <tt>{@link preDelete()}</tt> method which is responsible for:
	 *	 <ul>
	 *		<li>checking whether the current object is referenced, in which case the method
	 *			will return <tt>FALSE</tt> and not delete the object;
	 *		<li>collecting data type and kind information related to the object's tags. This
	 *			operation will use the current object's {@link kTAG_OBJECT_OFFSETS} and
	 *			{@link kTAG_OBJECT_REFERENCES} properties of the current object to build the
	 *			tag and reference parameters holding the same information as the parameters
	 *			shared by the commit phase;
	 *		<li>performing final operations before the object gets deleted.
	 *	 </ul>
	 *	<li>We pass the current object to the collection's
	 *		{@link CollectionObject::delete()} method which will delete it from the
	 *		collection and recuperate the result which will be returned by this method.
	 *	<li>We call the <tt>{@link postDelete()}</tt> method which is the counterpart of the
	 *		{@link postInsert()} method, it will:
	 *	 <ul>
	 *		<li><tt>update tag reference count and offsets;
	 *		<li><tt>update referenced object's reference counts.
	 *	 </ul>
	 *		This method will be called only if the collection delete method returns a non
	 *		<tt>NULL</tt> result: in that case it means the object doesn't exist in the
	 *		collection, thus referential integrity is not affected. <em>This should only
	 *		happen if the object has been deleted after it was loaded and before this method
	 *		was called.</em>
	 *	<li>We reset both the object's {@link isCommitted()} and {@link isDirty()} status.
	 *	<li>We return the result of the collection delete operation.
	 * </ul>
	 *
	 * The method will return the object's native identifier if it was deleted; if the
	 * object is referenced, the method will return <tt>FALSE</tt>; if the object was not
	 * found in the container, the method will return <tt>NULL</tt>.
	 *
	 * You should overload the methods called in this method, not this method.
	 *
	 * @access protected
	 * @return mixed				Native identifier, <tt>NULL</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 * @uses isDirty()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses preDelete()
	 * @uses postDelete()
	 */
	protected function deleteObject()
	{
		//
		// Do it only if the object is committed and clean.
		//
		if( $this->isCommitted()
		 && (! $this->isDirty()) )
		{
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $this->mDictionary ) );
		
			//
			// Prepare object.
			//
			if( ! $this->preDelete() )
				return FALSE;														// ==>
		
			//
			// Delete.
			//
			$ok = $collection->delete( $this );
			if( $ok !== NULL )
			{
				$tags = $this->offsetGet( kTAG_OBJECT_OFFSETS );
				$refs = $this->offsetGet( kTAG_OBJECT_REFERENCES );
				$this->postDelete( $tags, $refs );
			}
	
			//
			// Set object status.
			//
			$this->isDirty( FALSE );
			$this->isCommitted( FALSE );
		
			return $this->offsetGet( kTAG_NID );									// ==>
		
		} // Clean and committed.
		
		throw new \Exception(
			"Cannot delete object: "
		   ."the object is not committed or was modified." );					// !@! ==>
	
	} // deleteObject.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommit																		*
	 *==================================================================================*/

	/**
	 * Prepare object for commit
	 *
	 * This method should prepare the object for being committed, the method will perform
	 * the following steps:
	 *
	 * <ul>
	 *	<li><tt>{@link preCommitPrepare()}</tt>: This method will check if the object is
	 *		initialised, derived classes may overload this method to perform custom actions.
	 *	<li><tt>{@link preCommitTraverse()}</tt>: This method will traverse the object's
	 *		structure performing the following actions:
	 *	 <ul>
	 *		<li><em>Collect information</em>: The method will collect all referenced tags,
	 *			all offset paths and all referenced objects: this information will be used
	 *			in the post-commit phase to update reference counts and tag offset usage.
	 *		<li><em>Validate properties</em>: The method will validate the structure and
	 *			values of the object's properties, if any invalid value is encountered, an
	 *			exception will be raised.
	 *		<li><em>Cast properties</em>: The method will cast all property values to the
	 *			relative tag's data type.
	 *	 </ul>
	 *	<li><tt>{@link preCommitFinalise()}</tt>: This method will load the object's
	 *		{@link kTAG_OBJECT_TAGS}, {@link kTAG_OBJECT_OFFSETS} and
	 *		{@link kTAG_OBJECT_REFERENCES} properties, it will then compute eventual object
	 *		identifiers.
	 *	<li><tt>{@link isReady()}</tt>: The final step of the pre-commit phase is to test
	 *		whether the object is ready to be committed, if that is not the case, the method
	 *		will raise an exception.
	 * </ul>
	 *
	 * The method accepts two array reference parameters which will be initialised in this
	 * method, these will be filled by the {@link preCommitTraverse()} method and will be
	 * used to set the {@link kTAG_OBJECT_TAGS}, {@link kTAG_OBJECT_OFFSETS} and
	 * {@link kTAG_OBJECT_REFERENCES} properties. These arrays are structured as follows:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This array collects the set of all tags referenced by the
	 *		object's properties, except for the offsets corresponding to the
	 *		{@link InternalOffsets()}, the array is structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The tag sequence number.
	 *		<li><tt>value</tt>: An array collecting all the relevant information about that
	 *			tag, each element of the array is structured as follows:
	 *		 <ul>
	 *			<li><tt>{@link kTAG_DATA_TYPE}</tt>: The item indexed by this key will
	 *				contain the corresponding tag object offset.
	 *			<li><tt>{@link kTAG_DATA_KIND}</tt>: The item indexed by this key will
	 *				contain the corresponding tag object offset.
	 *			<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: The item indexed by this key will
	 *				collect the list of all the offsets in which the current tag appears as
	 *				a leaf offset. In practice, this element collects all the possible
	 *				offsets at any depth level in which the current tag holds a value. This
	 *				also means that it will only be filled if the current tag is not a
	 *				structural element.
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theRefs</b>: This parameter collects all the object references featured in
	 *		the current object. It is an array of elements structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The referenced object's collection name.
	 *		<li><tt>value</tt>: The set of all native identifiers representing the
	 *			referenced objects.
	 *	 </ul>
	 * </ul>
	 *
	 * These parameter will be initialised in this method.
	 *
	 * Derived classes should overload the called methods rather than the current one.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses preCommitPrepare()
	 * @uses preCommitTraverse()
	 * @uses preCommitFinalise()
	 * @uses isReady()
	 */
	protected function preCommit( &$theTags, &$theRefs )
	{
		//
		// Init parameters.
		//
		$theTags = $theRefs = Array();
		
		//
		// Prepare object.
		//
		$this->preCommitPrepare( $theTags, $theRefs );
	
		//
		// Traverse object.
		//
		$this->preCommitTraverse( $theTags, $theRefs );
		
		//
		// Finalise object.
		//
		$this->preCommitFinalise( $theTags, $theRefs );
	
		//
		// Check if object is ready.
		//
		if( ! $this->isReady() )
			throw new \Exception(
				"Cannot commit object: "
			   ."the object is not ready." );									// !@! ==>
	
	} // preCommit.

	 
	/*===================================================================================
	 *	preCommitPrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before commit
	 *
	 * This method is called by the {@link preCommit()} method, it should perform
	 * preliminary checks to ensure that the object is fit to be committed.
	 *
	 * In this class we check if the object is {@link isInited()}, in derived classes you
	 * can overload this method to perform custom checks.
	 *
	 * The method features the caller parameters, see the {@link preCommit()} method for a
	 * description of those parameters.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses isInited()
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Check if initialised.
		//
		if( ! $this->isInited() )
			throw new \Exception(
				"Unable to commit: "
			   ."the object is not initialised." );								// !@! ==>
		
	} // preCommitPrepare.

		
	/*===================================================================================
	 *	preCommitTraverse																*
	 *==================================================================================*/

	/**
	 * Traverse object before commit
	 *
	 * This method is called by the {@link preCommit()} method, it will traverse the current
	 * object, fill the provided parameters and validate property values. For a description
	 * of the parameters to this method, please consult the {@link preCommit()} method
	 * documentation.
	 *
	 * In this class we perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Clear local offsets</em>: The method will delete all offsets returned by
	 *		the {@link DynamicOffsets()} static method, this is to ensure these properties
	 *		are filled with current data.
	 *	<li><em>Parse object</em>: The method will call the {@link parseObject()} method
	 *		that will perform the following actions:
	 *	 <ul>
	 *		<li><em>Collect information</em>: The method will collect all referenced tags,
	 *			all offset paths and all referenced objects: this information will be used
	 *			in the post-commit phase to update reference counts and tag offset usage.
	 *		<li><em>Validate properties</em>: The method will validate the structure and
	 *			values of the object's properties, if any invalid value is encountered, an
	 *			exception will be raised.
	 *		<li><em>Cast properties</em>: The method will cast all property values to the
	 *			relative tag's data type.
	 *	 </ul>
	 * </ul>
	 *
	 * The last parameter of this method is a flag which if set will activate the
	 * validation, reference check and casting of the offsets; if not set, the method will
	 * only collect tag and reference information.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 *
	 * @uses DynamicOffsets()
	 * @uses parseObject()
	 */
	protected function preCommitTraverse( &$theTags, &$theRefs, $doValidate = TRUE )
	{
		//
		// Remove private offsets.
		//
		foreach( static::DynamicOffsets() as $offset )
			$this->offsetUnset( $offset );
	
		//
		// Parse object.
		//
		$this->parseObject( $theTags, $theRefs, $doValidate );
	
	} // preCommitTraverse.

	 
	/*===================================================================================
	 *	preCommitFinalise																*
	 *==================================================================================*/

	/**
	 * Finalise object before commit
	 *
	 * This method is called by the {@link preCommit()} method, it will be executed before
	 * checking if the object is ready, {@link isReady()}, its duty is to make the last
	 * preparations before the object is to be committed.
	 *
	 * The method calls three methods:
	 *
	 * <ul>
	 *	<li><tt>{@link preCommitObjectTags()}</tt>: This method is responsible for loading
	 *		the {@link kTAG_OBJECT_TAGS} and {@link kTAG_OBJECT_OFFSETS} object properties.
	 *	<li><tt>{@link preCommitObjectReferences()}</tt>: This method is responsible for
	 *		loading the {@link kTAG_OBJECT_REFERENCES} object property.
	 *	<li><tt>{@link preCommitObjectIdentifiers()}</tt>: This method is responsible of
	 *		computing eventual object identifiers.
	 * </ul>
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses preCommitObjectTags()
	 * @uses preCommitObjectReferences()
	 * @uses preCommitObjectIdentifiers()
	 */
	protected function preCommitFinalise( &$theTags, &$theRefs )
	{
		//
		// Load object tags.
		//
		$this->preCommitObjectTags( $theTags );
	
		//
		// Load object references.
		//
		$this->preCommitObjectReferences( $theRefs );
	
		//
		// Compute object identifiers.
		//
		$this->preCommitObjectIdentifiers();
	
		//
		// Handle graph references.
		//
		if( (! $this->isCommitted())
		 && (($graph = $this->mDictionary->Graph()) !== NULL) )
			$this->preCommitGraphReferences( $graph );
	
	} // preCommitFinalise.

	 
	/*===================================================================================
	 *	preCommitObjectTags																*
	 *==================================================================================*/

	/**
	 * Load object tags
	 *
	 * This method is called by the {@link preCommitFinalise()} method, it will collect the
	 * offset tags from the provided parameter and populate the {@link kTAG_OBJECT_TAGS}
	 * and {@link kTAG_OBJECT_OFFSETS} properties of the current object.
	 *
	 * Note that the provided list of tags should only include leaf offset references, in
	 * other words, only properties which are not of the {@link kTYPE_STRUCT} type.
	 *
	 * The provided parameter <em>must</em> be an array reference.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * @param array					$theTags			Object tags.
	 *
	 * @access protected
	 *
	 * @see kTAG_OBJECT_TAGS kTAG_OBJECT_OFFSETS
	 */
	protected function preCommitObjectTags( &$theTags )
	{
		//
		// Check parameter.
		//
		if( count( $theTags ) )
		{
			//
			// Set tag list property.
			//
			$this->offsetSet( kTAG_OBJECT_TAGS, array_keys( $theTags ) );
			
			//
			// Set tag offsets property.
			//
			$offsets = Array();
			foreach( $theTags as $tag => $info )
			{
				//
				// Select leaf tags.
				//
				if( array_key_exists( kTAG_OBJECT_OFFSETS, $info ) )
					$offsets[ $tag ] = $info[ kTAG_OBJECT_OFFSETS ];
			
			} // Iterating tags.
			
			//
			// Set property.
			//
			if( count( $offsets ) )
				$this->offsetSet( kTAG_OBJECT_OFFSETS, $offsets );
		
		} // Has tags.
	
	} // preCommitObjectTags.

	 
	/*===================================================================================
	 *	preCommitObjectReferences														*
	 *==================================================================================*/

	/**
	 * Load object references
	 *
	 * This method is called by the {@link preCommitFinalise()} method, it will collect the
	 * object references from the provided parameter and populate the
	 * {@link kTAG_OBJECT_REFERENCES} property of the current object.
	 *
	 * The provided parameter <em>must</em> be an array reference.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * In this class we simply copy the parameter to the property, derived classes may
	 * overload this method to perform custom modifications and call the parent method that
	 * will set the property.
	 *
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @see kTAG_OBJECT_REFERENCES
	 */
	protected function preCommitObjectReferences( &$theRefs )
	{
		//
		// Check references.
		//
		if( count( $theRefs ) )
			$this->offsetSet( kTAG_OBJECT_REFERENCES, $theRefs );
	
	} // preCommitObjectReferences.

	 
	/*===================================================================================
	 *	preCommitObjectIdentifiers														*
	 *==================================================================================*/

	/**
	 * Load object identifiers
	 *
	 * This method is called by the {@link preCommitFinalise()} method, its duty is to
	 * compute eventual object identifiers.
	 *
	 * In this class we do not handle identifiers, derived classes should overload this
	 * method if they need to compute identifiers.
	 *
	 * This method should only be called if the object is not committed, since it is here
	 * that all immutable identifiers are set: in derived classes be sure to make this
	 * check.
	 *
	 * @access protected
	 */
	protected function preCommitObjectIdentifiers()										   {}

	 
	/*===================================================================================
	 *	preCommitGraphReferences														*
	 *==================================================================================*/

	/**
	 * Load object in graph
	 *
	 * This method is called by the {@link preCommitFinalise()} method, its duty is to
	 * load the object into the eventual graph
	 *
	 * The method will be called only if the object is not committed and if the current
	 * object's wrapper features a graph.
	 *
	 * Derived classes should not overload this method, but rather the methods it calls;
	 * the only exception is if the object should not be stored in the graph: in that case
	 * overload the method to return <tt>FALSE</tt>.
	 *
	 * If the method returns an integer, it means that the value represents the graph node
	 * identifier; if the method returns <tt>FALSE</tt>, it means that there will be no
	 * graph node.
	 *
	 * @param DatabaseGraph			$theGraph			Graph connection.
	 *
	 * @access protected
	 * @return mixed				The node identifier or <tt>FALSE</tt>.
	 */
	protected function preCommitGraphReferences( DatabaseGraph $theGraph )
	{
		//
		// Create graph node.
		//
		$id = $this->createGraphNode( $theGraph );
		if( $id === FALSE )
			return FALSE;															// ==>
		
		//
		// Set object offset.
		//
		$this->offsetSet( kTAG_ID_GRAPH, $id );
		
		//
		// Create related graph nodes.
		//
		$this->createRelatedGraphNodes( $theGraph );
		
		return $id;																	// ==>
	
	} // preCommitGraphReferences.

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-COMMIT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postInsert																		*
	 *==================================================================================*/

	/**
	 * Handle object after insert
	 *
	 * This method is called immediately after the object was inserted, its duty is to
	 * update object and tag reference counts and tag offset paths, the method will perform
	 * the following steps:
	 *
	 * <ul>
	 *	<li><em>Update tag reference counts</em>: All tags referenced by the object's leaf 
	 *		offsets will have their reference count property related to the current object's
	 *		base class incremented.
	 *	<li><em>Update tag offsets</em>: The set of offset paths in which a specific tag was
	 *		referenced as a leaf offset will be added to the tag's property related to the
	 *		current object's base class.
	 *	<li><em>Update object reference counts</em>: All objects referenced by the current
	 *		object will have their reference count property related to the current object's
	 *		base class incremented.
	 * </ul>
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffsets</b>: This array parameter must be structured as the
	 *		{@link kTAG_OBJECT_OFFSETS} property, the provided offsets will be added to the
	 *		relative tags property set.
	 *	<li><b>$theReferences</b>: This array parameter must be structured as the
	 *		{@link kTAG_OBJECT_REFERENCES} property, the reference counts of the objects
	 *		referenced by the identifiers provided in the parameter will be incremented.
	 * </ul>
	 *
	 * This method is identical to the {@link postDelete()} method, except that in this case
	 * offsets will be added and reference counts will be incremented.
	 *
	 * The provided array references are read-only.
	 *
	 * @param array					$theOffsets			Tag offsets to be added.
	 * @param array					$theReferences		Object references to be incremented.
	 *
	 * @access protected
	 *
	 * @see kTAG_OBJECT_OFFSETS, kTAG_OBJECT_REFERENCES
	 *
	 * @uses ResolveOffsetsTag()
	 * @uses updateObjectReferenceCount()
	 */
	protected function postInsert( &$theOffsets, &$theReferences )
	{
		//
		// Resolve tag collection.
		//
		$tag_collection
			= Tag::ResolveCollection(
				Tag::ResolveDatabase( $this->mDictionary ) );
		
		//
		// Resolve tag offsets property.
		//
		$offsets_tag = static::ResolveOffsetsTag( static::kSEQ_NAME );
		
		//
		// Handle tags.
		//
		if( is_array( $theOffsets ) )
		{
			//
			// Update tags reference count.
			//
			$this->updateObjectReferenceCount(
				Tag::kSEQ_NAME,								// Tags collection.
				array_keys( $theOffsets ),					// Tags identifiers.
				kTAG_ID_SEQUENCE,							// Tags identifiers offset.
				1 );										// Reference count.
		
			//
			// Update new offsets.
			//
			foreach( $theOffsets as $key => $value )
				$tag_collection->updateSet(
					(int) $key,								// Tag identifiers.
					kTAG_ID_SEQUENCE,						// Tag identifiers offset.
					array( $offsets_tag => $value ),		// Offsets set.
					TRUE );									// Add to set.
		
		} // Has tag offsets.
		
		//
		// Handle object references.
		//
		if( is_array( $theReferences ) )
		{
			foreach( $theReferences as $key => $value )
				$this->updateObjectReferenceCount(
					$key,									// Collection name.
					$value,									// Identifiers.
					kTAG_NID,								// Identifiers offset.
					1 );									// Reference count.
		
		} // Has references.
	
	} // postInsert.

	 
	/*===================================================================================
	 *	postUpdate																		*
	 *==================================================================================*/

	/**
	 * Handle object after update
	 *
	 * This method is called immediately after the object was updated, its duty is to
	 * update object and tag reference counts and tag offset paths, the method will perform
	 * the following steps:
	 *
	 * <ul>
	 *	<li><em>Update new tags reference counts</em>: All new object tags will have their
	 *		relative tag object reference count property related to the current object
	 *		incremented.
	 *	<li><em>Update new tag offsets</em>: All new tag offsets will be added to the
	 *		relative tag object property determined by the static
	 *		{@link ResolveOffsetsTag()} method.
	 *	<li><em>Update new object reference counts</em>: All new objects referenced by the
	 *		current object will have their reference count property related to the current
	 *		object's base class incremented.
	 *	<li><em>Select deleted offsets</em>: All offsets no longer part of the current
	 *		object will be selected.
	 *	<li><em>Filter existing offsets</em>: The deleted offsets selection will be rediuced
	 *		by removing all offsets which currently exist in the collection.
	 *	<li><em>Update deleted tag offsets</em>: All deleted tag offsets will be removed
	 *		from the relative tag object property determined by the static
	 *		{@link ResolveOffsetsTag()} method.
	 *	<li><em>Update deleted object reference counts</em>: All deleted objects referenced
	 *		by the current object will have their reference count property related to the
	 *		current object's base class decremented.
	 * </ul>
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffsets</b>: This array parameter is expected to be the
	 *		{@link kTAG_OBJECT_OFFSETS} property of the persistent object <i>before it was
	 *		updated</i>.
	 *	<li><b>$theReferences</b>: This array parameter is expected to be the
	 *		{@link kTAG_OBJECT_REFERENCES} property of the persistent object <i>before it
	 *		was updated</i>.
	 * </ul>
	 *
	 * The provided array references are read-only.
	 *
	 * @param array					$theOffsets			Original tag offsets.
	 * @param array					$theReferences		Original object references.
	 *
	 * @access protected
	 *
	 * @see kTAG_OBJECT_OFFSETS, kTAG_OBJECT_REFERENCES
	 *
	 * @uses ResolveOffsetsTag()
	 * @uses updateObjectReferenceCount()
	 * @uses compareObjectOffsets()
	 * @uses compareObjectReferences()
	 * @uses filterExistingOffsets()
	 */
	protected function postUpdate( &$theOffsets, &$theReferences )
	{
		//
		// Resolve tag collection.
		//
		$tag_collection
			= Tag::ResolveCollection(
				Tag::ResolveDatabase( $this->mDictionary ) );
		
		//
		// Resolve tag offsets property.
		//
		$offsets_tag = static::ResolveOffsetsTag( static::kSEQ_NAME );
		
		//
		// Save offsets and references.
		//
		$offsets = $this->offsetGet( kTAG_OBJECT_OFFSETS );
		$references = $this->offsetGet( kTAG_OBJECT_REFERENCES );
		
		//
		// Update new tags reference count.
		//
		$tags = array_diff( array_keys( $offsets ), array_keys( $theOffsets ) );
		if( count( $tags ) )
			$this->updateObjectReferenceCount(
				Tag::kSEQ_NAME,							// Tags collection.
				array_values( $tags ),					// Tags identifiers.
				kTAG_ID_SEQUENCE,						// Tags identifiers offset.
				1 );									// Reference count.
		
		//
		// Update new offsets.
		//
		$list = $this->compareObjectOffsets( $offsets, $theOffsets, TRUE );
		foreach( $list as $key => $value )
			$tag_collection->updateSet(
				(int) $key,								// Tag identifiers.
				kTAG_ID_SEQUENCE,						// Tag identifiers offset.
				array( $offsets_tag
						=> array_values( $value ) ),	// Offsets set.
				TRUE );									// Add to set.
		
		//
		// Update new object references.
		//
		$list = $this->compareObjectReferences( $references, $theReferences, TRUE );
		foreach( $list as $key => $value )
			$this->updateObjectReferenceCount(
				$key,									// Collection name.
				array_values( $value ),					// Identifiers.
				kTAG_NID,								// Identifiers offset.
				1 );									// Reference count.
		
		//
		// Update deleted tags reference count.
		//
		$tags = array_diff( array_keys( $theOffsets ), array_keys( $offsets ) );
		if( count( $tags ) )
			$this->updateObjectReferenceCount(
				Tag::kSEQ_NAME,							// Tags collection.
				array_values( $tags ),					// Tags identifiers.
				kTAG_ID_SEQUENCE,						// Tags identifiers offset.
				-1 );									// Reference count.
		
		//
		// Select deleted offsets.
		//
		$list = $this->compareObjectOffsets( $theOffsets, $offsets, TRUE );
		
		//
		// Filter existing offsets.
		//
		$this->filterExistingOffsets( $tag_collection, $list );
		
		//
		// Update deleted offsets.
		//
		foreach( $list as $key => $value )
			$tag_collection->updateSet(
				(int) $key,								// Tag identifiers.
				kTAG_ID_SEQUENCE,						// Tag identifiers offset.
				array( $offsets_tag
						=> array_values( $value ) ),	// Offsets set.
				FALSE );								// Pull from set.
		
		//
		// Update deleted object references.
		//
		$list = $this->compareObjectReferences( $theReferences, $references, TRUE );
		foreach( $list as $key => $value )
			$this->updateObjectReferenceCount(
				$key,									// Collection name.
				array_values( $value ),					// Identifiers.
				kTAG_NID,								// Identifiers offset.
				-1 );									// Reference count.
	
	} // postUpdate.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-DELETE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preDelete																		*
	 *==================================================================================*/

	/**
	 * Prepare object for delete
	 *
	 * This method should prepare the object for being deleted, it mirrors the counterpart
	 * {@link preCommit()} method, except that it is intended for deleting objects. The
	 * following steps will be performed:
	 *
	 * <ul>
	 *	<li><tt>{@link preDeletePrepare()}</tt>: This method should check whether the object
	 *		can be deleted, in this class we verify that the object is not referenced.
	 *	<li><tt>{@link preDeleteTraverse()}</tt>: This method is the counterpart of the
	 *		{@link precommitTraverse()} method, in this class it does nothing, derived
	 *		classes may overload it to modify the reference properties of the current
	 *		object.
	 *	<li><tt>{@link preDeleteFinalise()}</tt>: This method should finalise the pre-delete
	 *		phase ensuring the object is ready to be deleted.
	 * </ul>
	 *
	 * Unlike the {@link preCommit()} method, this one doesn't need to compute tag and
	 * references information, since that information is already contained in the
	 * {@link kTAG_OBJECT_OFFSETS} and {@link kTAG_OBJECT_REFERENCES} properties of the
	 * current object.
	 *
	 * The method will return <tt>TRUE</tt> if the object can be deleted, if any of the
	 * called methods do not return <tt>TRUE</tt>, the operation will be aborted.
	 *
	 * Derived classes should overload the called methods rather than the current one.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> the object can be deleted.
	 *
	 * @uses preDeletePrepare()
	 * @uses preDeleteTraverse()
	 * @uses preDeleteFinalise()
	 */
	protected function preDelete()
	{
		//
		// Prepare object.
		//
		if( ! $this->preDeletePrepare() )
			return FALSE;															// ==>
	
		//
		// Traverse object.
		//
		if( ! $this->preDeleteTraverse() )
			return FALSE;															// ==>
		
		//
		// Finalise object.
		//
		if( ! $this->preDeleteFinalise() )
			return FALSE;															// ==>
		
		return TRUE;																// ==>
	
	} // preDelete.

	 
	/*===================================================================================
	 *	preDeletePrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before delete
	 *
	 * This method should perform a preliminary check to ensure whether the object can be
	 * deleted, the method returns a boolean value which if <tt>TRUE</tt> it indicates that
	 * it is safe to delete the object.
	 *
	 * In this class we check whether the object is referenced, in that case the method will
	 * return <tt>FALSE</tt>.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> the object can be deleted.
	 *
	 * @see kTAG_UNIT_COUNT kTAG_ENTITY_COUNT
	 * @see kTAG_TAG_COUNT kTAG_TERM_COUNT kTAG_NODE_COUNT kTAG_EDGE_COUNT
	 */
	protected function preDeletePrepare()
	{	
		//
		// Check reference counts.
		//
		if( $this->offsetGet( kTAG_UNIT_COUNT )
		 || $this->offsetGet( kTAG_TAG_COUNT )
		 || $this->offsetGet( kTAG_TERM_COUNT )
		 || $this->offsetGet( kTAG_NODE_COUNT )
		 || $this->offsetGet( kTAG_EDGE_COUNT )
		 || $this->offsetGet( kTAG_ENTITY_COUNT ) )
			return FALSE;															// ==>
		
		return TRUE;																// ==>
	
	} // preDeletePrepare.

		
	/*===================================================================================
	 *	preDeleteTraverse																*
	 *==================================================================================*/

	/**
	 * Traverse object before delete
	 *
	 * This method is called by the {@link preDelete()} method, in this class it does
	 * nothing; derived classes may overload it to modify the {@link kTAG_OBJECT_OFFSETS}
	 * and {@link kTAG_OBJECT_REFERENCES} properties of the current object that will be used
	 * in the post-delete phase to update referential integrity.
	 *
	 * In this class we return by default <tt>TRUE</tt>.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> the object can be deleted.
	 */
	protected function preDeleteTraverse()								{	return TRUE;	}

	 
	/*===================================================================================
	 *	preDeleteFinalise																*
	 *==================================================================================*/

	/**
	 * Finalise object before delete
	 *
	 * This method will be called before the object will be deleted, it is the last chance
	 * to perform custom checks or modify the parameters passed to the post-delete phase.
	 *
	 * In this class we do nothing.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> the object can be deleted.
	 */
	protected function preDeleteFinalise()								{	return TRUE;	}

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-DELETE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postDelete																		*
	 *==================================================================================*/

	/**
	 * Handle object after delete
	 *
	 * This method is called immediately after the object was deleted, its duty is to update
	 * object and tag reference counts and tag offset paths, the method will perform the
	 * following steps:
	 *
	 * <ul>
	 *	<li><em>Update tag reference counts</em>: All tags referenced by the object's leaf 
	 *		offsets will have their reference count property related to the current object's
	 *		base class decremented.
	 *	<li><em>Update tag offsets</em>: The set of offset paths in which a specific tag was
	 *		referenced as a leaf offset will be removed from the tag's property related to
	 *		the current object's base class.
	 *	<li><em>Update object reference counts</em>: All objects referenced by the current
	 *		object will have their reference count property related to the current object's
	 *		base class decremented.
	 * </ul>
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffsets</b>: This array parameter must be structured as the
	 *		{@link kTAG_OBJECT_OFFSETS} property, the provided offsets will be removed from
	 *		the relative tags property set.
	 *	<li><b>$theReferences</b>: This array parameter must be structured as the
	 *		{@link kTAG_OBJECT_REFERENCES} property, the reference counts of the objects
	 *		referenced by the identifiers provided in the parameter will be decremented.
	 * </ul>
	 *
	 * This method is identical to the {@link postInsert()} method, except that in this case
	 * offsets will be removed and reference counts will be decremented.
	 *
	 * The provided array references are read-only.
	 *
	 * @param array					$theOffsets			Tag offsets to be removed.
	 * @param array					$theReferences		Object references to be decremented.
	 *
	 * @access protected
	 *
	 * @see kTAG_OBJECT_OFFSETS, kTAG_OBJECT_REFERENCES
	 *
	 * @uses ResolveOffsetsTag()
	 * @uses updateObjectReferenceCount()
	 * @uses filterExistingOffsets()
	 */
	protected function postDelete( &$theOffsets, &$theReferences )
	{
		//
		// Resolve tag collection.
		//
		$tag_collection
			= Tag::ResolveCollection(
				Tag::ResolveDatabase( $this->mDictionary ) );
		
		//
		// Resolve tag offsets property.
		//
		$tag_ref_count = static::ResolveOffsetsTag( static::kSEQ_NAME );
		
		//
		// Handle tags.
		//
		if( is_array( $theOffsets ) )
		{
			//
			// Update deleted tags reference count.
			//
			$this->updateObjectReferenceCount(
				Tag::kSEQ_NAME,							// Tags collection.
				array_keys( $theOffsets ),				// Tags identifiers.
				kTAG_ID_SEQUENCE,						// Tags identifiers offset.
				-1 );									// Reference count.
		
			//
			// Filter existing offsets.
			//
			$this->filterExistingOffsets( $tag_collection, $theOffsets );
		
			//
			// Update deleted offsets.
			//
			foreach( $theOffsets as $key => $value )
				$tag_collection->updateSet(
					(int) $key,								// Tag identifiers.
					kTAG_ID_SEQUENCE,						// Tag identifiers offset.
					array( $tag_ref_count
							=> array_values( $value ) ),	// Offsets set.
					FALSE );								// Pull from set.
		
		} // Has tag offsets.
		
		//
		// Handle references.
		//
		if( is_array( $theReferences ) )
		{
			foreach( $theReferences as $key => $value )
				$this->updateObjectReferenceCount(
					$key,									// Collection name.
					$value,									// Identifiers.
					kTAG_NID,								// Identifiers offset.
					-1 );									// Reference count.
		
		} // Has references.
	
	} // postDelete.

	

/*=======================================================================================
 *																						*
 *							PROTECTED OBJECT PARSING INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseObject																		*
	 *==================================================================================*/

	/**
	 * Parse object
	 *
	 * The duty of this method is to traverse the current object structure, collect tag,
	 * offset and reference information and eventually validate the object properties.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This parameter will receive the list of tags used in the
	 *		current object, the parameter is a reference to an array indexed by tag sequence
	 *		number holding the following elements:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_DATA_TYPE}</tt>: The item indexed by this key will contain
	 *			the tag data type.
	 *		<li><tt>{@link kTAG_DATA_KIND}</tt>: The item indexed by this key will contain
	 *			the tag data kinds.
	 *		<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: The item indexed by this key will
	 *			collect all the possible offsets at any depth level in which the current tag
	 *			holds a scalar value (not a structure).
	 *		 </ul>
	 *	<li><b>$theRefs</b>: This parameter will receive the list of all object references
	 *		held by the object, the parameter is an array reference in which the key is the
	 *		collection name and the value is a list of native identifiers of the referenced
	 *		objects held by the collection.
	 *	<li><b>$doValidate</b>: If this parameter is <tt>TRUE</tt>, the object's properties
	 *		will be validated and cast to their correct type.
	 * </ul>
	 *
	 * @param array					$theTags			Receives tag information.
	 * @param array					$theRefs			Receives references information.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 *
	 * @uses parseStructure()
	 */
	protected function parseObject( &$theTags, &$theRefs, $doValidate = TRUE )
	{
		//
		// Init local storage.
		//
		$path = $theTags = $theRefs = Array();
		
		//
		// Get object array copy.
		//
		$object = $this->getArrayCopy();
		
		//
		// Iterate properties.
		//
		$this->parseStructure(
			$object, $path, $theTags, $theRefs, $doValidate );
		
		//
		// Update object.
		//
		if( $doValidate )
		{
			//
			// Save full-text properties.
			//
			$text10 = $this->offsetGet( kTAG_FULL_TEXT_10 );
			$text06 = $this->offsetGet( kTAG_FULL_TEXT_06 );
			$text03 = $this->offsetGet( kTAG_FULL_TEXT_03 );
			
			//
			// Replace data.
			//
			$this->exchangeArray( $object );
			
			//
			// Reset full-text properties.
			//
			if( $text10 !== NULL )
				$this->offsetSet( kTAG_FULL_TEXT_10, $text10 );
			if( $text06 !== NULL )
				$this->offsetSet( kTAG_FULL_TEXT_06, $text06 );
			if( $text03 !== NULL )
				$this->offsetSet( kTAG_FULL_TEXT_03, $text03 );
		
		} // Validated object.
	
	} // parseObject.

	 
	/*===================================================================================
	 *	parseStructure																	*
	 *==================================================================================*/

	/**
	 * Parse structure
	 *
	 * This method will parse the provided structure collecting tag, offset and object
	 * reference information in the provided reference parameters, the method will perform
	 * the following actions:
	 *
	 * <ul>
	 *	<li><b>Collect tag information</b>: The method will collect all tags referenced by
	 *		the leaf offsets of the provided structure and for each tag it will collect the
	 *		data type, data kind and offset path.
	 *	<li><b>Collect object references</b>: The method will collect all object references
	 *		contained in the provided structure, these references will be grouped by
	 *		collection.
	 *	<li><em>Validate properties</em>: The method will validate all properties of the
	 *		provided structure, if the last parameter is <tt>TRUE</tt>.
	 *	<li><em>Cast properties</em>: The method will cast all properties of the provided
	 *		structure to the expected data type, if the last parameter is <tt>TRUE</tt>.
	 * </ul>
	 *
	 * The above actions will only be applied to offsets not belonging to the list of
	 * internal offsets, {@link InternalOffsets()}, and the method will recursively be
	 * applied to all nested structures.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theStructure</b>: This parameter is a reference to an array containing the
	 *		structure.
	 *	<li><b>$thePath</b>: This parameter is a reference to an array representing the path
	 *		of offsets pointing to the provided structure.
	 *	<li><b>$theTags</b>: This parameter is a reference to an array which will receive
	 *		tag information related to the provided structure's offsets, the array is
	 *		structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The element key represents the tag sequence numbers referenced
	 *			by the offset. Each element is an array structured as follows:
	 *		 <ul>
	 *			<li>{@link kTAG_DATA_TYPE}</tt>: The item holding this key will contain the
	 *				tag data type.
	 *			<li>{@link kTAG_DATA_KIND}</tt>: The item holding this key will contain the
	 *				tag data kind; if the tag has no data kind, this item will be an empty
	 *				array.
	 *			<li>{@link kTAG_OBJECT_OFFSETS}</tt>: The item holding this key will
	 *				contain the list of offset paths in which the current tag is referenced
	 *				as a leaf offset (an offset holding a value, not a structure).
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theRefs</b>: This parameter is a reference to an array which will receive
	 *		the list of object references held by the structure, the array is structured as
	 *		follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The element key represents a collection name.
	 *		<li><tt>value</tt>: The element value is the list of references to objects
	 *			belonging to the collection.
	 *	 </ul>
	 *	<li><b>$doValidate</b>: This boolean flag indicates whether the method should
	 *		validate and cast the structure elements.
	 * </ul>
	 *
	 * @param array					$theStructure		Structure.
	 * @param array					$thePath			Receives the offset path.
	 * @param array					$theTags			Receives the tag information.
	 * @param array					$theRefs			Receives the object references.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses InternalOffsets()
	 * @uses OffsetTypes()
	 * @uses parseStructure()
	 * @uses parseProperty()
	 * @uses loadTagInformation()
	 * @uses loadReferenceInformation()
	 */
	protected function parseStructure( &$theStructure, &$thePath, &$theTags, &$theRefs,
										$doValidate )
	{
		//
		// Init local storage.
		//
		$tags = array_keys( $theStructure );
		$exclude = array_merge( static::InternalOffsets(), static::UnmanagedOffsets() );
		
		//
		// Iterate properties.
		//
		foreach( $tags as $tag )
		{
			//
			// Skip internal offsets.
			//
			if( ! in_array( $tag, $exclude ) )
			{
				//
				// Push offset to path.
				//
				$thePath[] = $tag;
		
				//
				// Compute offset.
				//
				$offset = implode( '.', $thePath );
		
				//
				// Reference property.
				//
				$property_ref = & $theStructure[ $tag ];
			
				//
				// Copy type and kind.
				//
				if( array_key_exists( $tag, $theTags ) )
				{
					//
					// Copy type and kind.
					//
					$type = $theTags[ $tag ][ kTAG_DATA_TYPE ];
					$kind = $theTags[ $tag ][ kTAG_DATA_KIND ];
	
					//
					// Copy data validation info.
					//
					$min = $theTags[ $tag ][ kTAG_MIN_RANGE ];
					$max = $theTags[ $tag ][ kTAG_MAX_RANGE ];
					$pattern = $theTags[ $tag ][ kTAG_PATTERN ];
	
				} // Already parsed.
	
				//
				// Determine tag information.
				//
				else
					static::OffsetTypes(
						$this->mDictionary,
						$tag,
						$type, $kind,
						$min, $max, $pattern,
						TRUE );
				
				//
				// Handle lists.
				//
				if( in_array( kTYPE_LIST, $kind ) )
				{
					//
					// Verify list.
					//
					if( ! is_array( $property_ref ) )
						throw new \Exception(
							"Invalid list in [$offset]: "
						   ."the value is not an array." );						// !@! ==>
					
					//
					// Iterate list elements.
					//
					$keys = array_keys( $property_ref );
					foreach( $keys as $key )
					{
						//
						// Reference element.
						//
						$element_ref = & $property_ref[ $key ];
						
						//
						// Handle structures.
						//
						if( $type == kTYPE_STRUCT )
						{
							//
							// Verify structure.
							//
							if( ! is_array( $element_ref ) )
								throw new \Exception(
									"Invalid structure in [$offset]: "
								   ."the value is not an array." );				// !@! ==>
				
							//
							// Parse structure.
							//
							$this->parseStructure(
								$element_ref,
								$thePath, $theTags, $theRefs, $doValidate );
						
						} // Structure.
						
						//
						// Handle scalars.
						//
						else
						{
							//
							// Parse property.
							//
							$class
								= $this->parseProperty(
									$element_ref,
									$type, $kind,
									$min, $max, $pattern,
									$offset, $doValidate );
						
							//
							// Load tag information.
							//
							$this->loadTagInformation(
								$theTags,
								$kind, $type,
								$min, $max, $pattern,
								$offset, $tag );
				
							//
							// Load reference information.
							//
							$this->loadReferenceInformation(
								$element_ref, $theRefs, $type, $offset );
			
						} // Scalar.
					
					} // Iterating list elements.
				
				} // List.
				
				//
				// Handle structure.
				//
				elseif( $type == kTYPE_STRUCT )
				{
					//
					// Verify structure.
					//
					if( ! is_array( $property_ref ) )
						throw new \Exception(
							"Invalid structure value in [$offset]: "
						   ."the value is not an array." );						// !@! ==>
			
					//
					// Traverse structure properties.
					//
					$this->parseStructure(
						$property_ref,
						$thePath, $theTags, $theRefs, $doValidate );
				
				} // Structure.
				
				//
				// Handle scalar.
				//
				else
				{
					//
					// Parse property.
					//
					$class
						= $this->parseProperty(
							$property_ref,
							$type, $kind,
							$min, $max, $pattern,
							$offset, $doValidate );
				
					//
					// Load tag information.
					//
					$this->loadTagInformation(
						$theTags,
						$kind, $type,
						$min, $max, $pattern,
						$offset, $tag );
				
					//
					// Load reference information.
					//
					$this->loadReferenceInformation(
						$property_ref, $theRefs, $type, $offset );
	
				} // Scalar.
		
				//
				// Pop offset from path.
				//
				array_pop( $thePath );
			
			} // Not an internal offset.
	
		} // Iterating properties.
		
	} // parseStructure.

	 
	/*===================================================================================
	 *	parseProperty																	*
	 *==================================================================================*/

	/**
	 * Parse property
	 *
	 * The duty of this method is to parse the provided scalar property and perform the
	 * following actions:
	 *
	 * <ul>
	 *	<li><em>Validate reference</em>: If the provided property is an object reference,
	 *		the method will commit it, if it is a non committed object and the .
	 *	<li><em>Validate properties</em>: The method will check if the provided list or
	 *		structure is an array, or validate the provided property if it is a scalar.
	 *	<li><em>Cast properties</em>: The method will cast the property value if it is a
	 *		scalar.
	 * </ul>
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theProperty</b>: The property to parse, a scalar property is expected.
	 *	<li><b>$theType</b>: The property data type.
	 *	<li><b>$thePath</b>: The property offset path.
	 *	<li><b>$doValidate</b>: This boolean flag indicates whether the method should
	 *		validate and cast the structure elements.
	 * </ul>
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theType			Data type.
	 * @param array					$theKind			Data kind.
	 * @param mixed					$theMin				Minimum data range.
	 * @param mixed					$theMax				Maximum data range.
	 * @param string				$thePattern			Data pattern.
	 * @param string				$thePath			Offset path.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 * @return string				Eventual property class name.
	 *
	 * @uses validateProperty()
	 * @uses getReferenceTypeClass()
	 * @uses parseReference()
	 * @uses validateReference()
	 * @uses castProperty()
	 */
	protected function parseProperty( &$theProperty,
									   $theType, $theKind,
									   $theMin, $theMax, $thePattern,
									   $thePath, $doValidate )
	{
		//
		// Validate scalar.
		//
		if( $doValidate )
			$this->validateProperty(
				$theProperty, $theType, $theKind, $theMin, $theMax, $thePattern, $thePath );
		
		//
		// Get reference class.
		//
		$class = $this->getReferenceTypeClass( $theType );
		
		//
		// Parse object reference.
		//
		if( $class !== NULL )
			$this->parseReference( $theProperty, $class, $thePath );
		
		//
		// Validate.
		//
		if( $doValidate )
		{
			//
			// Validate reference.
			//
			if( $class !== NULL )
				$this->validateReference(
					$theProperty, $theType, $class, $thePath );
			
			//
			// Cast value.
			//
			$this->castProperty(
				$theProperty, $theType, $thePath );
		
		} // Validate.
		
		//
		// Add to full-text index.
		//
		$this->addToFullText( $theProperty, kSTANDARDS_LANGUAGE, $theType, $theKind );
		
		return $class;																// ==>
		
	} // parseProperty.

	 
	/*===================================================================================
	 *	parseReference																	*
	 *==================================================================================*/

	/**
	 * Parse reference
	 *
	 * The duty of this method is to parse the provided reference expressed as an object,
	 * the method will perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Check class</em>: If the provided property is an object and is not an
	 *		instance of the provided class, the method will raise an exception.
	 *	<li><em>Commit object</em>: If the provided property is an uncommitted object, the
	 *		method will commit it.
	 *	<li><em>Check object identifier</em>: If the provided property is an object which
	 *		lacks its native identifier, the method will raise an exception.
	 *	<li><em>Use object native identifier</em>: The method will replace the object with
	 *		its native identifier.
	 * </ul>
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theClass			Object class name.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function parseReference( &$theProperty, $theClass, $thePath )
	{
		//
		// Handle objects.
		//
		if( is_object( $theProperty ) )
		{
			//
			// Verify class.
			//
			if( ! ($theProperty instanceof $theClass) )
				throw new \Exception(
					"Invalid object reference in [$thePath]: "
				   ."incorrect class [$theClass]." );							// !@! ==>
	
			//
			// Commit object.
			//
			if( ! $theProperty->isCommitted() )
				$id = $theProperty->commit( $this->mDictionary );
	
			//
			// Get identifier.
			//
			elseif( ! $theProperty->offsetExists( kTAG_NID ) )
				throw new \Exception(
					"Invalid object in [$thePath]: "
				   ."missing native identifier." );								// !@! ==>
	
			//
			// Set identifier.
			//
			$theProperty = $theProperty[ kTAG_NID ];

		} // Property is an object.
		
	} // parseReference.

	 
	/*===================================================================================
	 *	validateProperty																*
	 *==================================================================================*/

	/**
	 * Validate property
	 *
	 * The duty of this method is to validate the provided scalar property. In this class
	 * we check whether the structure of the property is correct, we assert the following
	 * properties:
	 *
	 * <ul>
	 *	<li>We check whether structured data types are arrays.
	 *	<li>We check the contents of shapes.
	 *	<li>We assert that all other data types are not arrays.
	 * </ul>
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theType			Data type.
	 * @param array					$theKind			Data kind.
	 * @param mixed					$theMin				Receives minimum data range.
	 * @param mixed					$theMax				Receives maximum data range.
	 * @param string				$thePattern			Receives data pattern.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateProperty( &$theProperty, $theType, $theKind,
										  $theMin, $theMax, $thePattern,
										  $thePath )
	{
		//
		// Validate by type.
		//
		switch( $theType )
		{
			case kTYPE_SET:
			case kTYPE_ARRAY:
			case kTYPE_TYPED_LIST:
			case kTYPE_LANGUAGE_STRING:
			case kTYPE_LANGUAGE_STRINGS:
				if( ! is_array( $theProperty ) )
					throw new \Exception(
						"Invalid offset value in [$thePath] type [$theType]: "
					   ."the value is not an array." );							// !@! ==>
				
				break;
			
			case kTYPE_SHAPE:
				if( ! is_array( $theProperty ) )
					throw new \Exception(
						"Invalid offset value in [$thePath] type [$theType]: "
					   ."the value is not an array." );							// !@! ==>
				
				if( (! array_key_exists( kTAG_TYPE, $theProperty ))
				 || (! array_key_exists( kTAG_GEOMETRY, $theProperty ))
				 || (! is_array( $theProperty[ kTAG_GEOMETRY ] )) )
					throw new \Exception(
						"Invalid offset value in [$thePath] type [$theType]: "
					   ."invalid shape geometry." );							// !@! ==>
				
				break;
			
			default:
				if( is_array( $theProperty ) )
					throw new \Exception(
						"Invalid offset value in [$thePath] type [$theType]: "
					   ."array not expected." );								// !@! ==>
				
				break;
		
		} // Parsed data type.
		
		//
		// Validate range.
		//
		if( ($theMin !== NULL)
		 && ($theMax !== NULL) )
		{
			//
			// Handle minimum.
			//
			if( $theProperty < $theMin )
				throw new \Exception(
					"Value out of range in offset [$thePath] type [$theType]: "
				   ."[$theProperty] smaller than [$theMin]." );					// !@! ==>
		
			//
			// Handle maximum.
			//
			if( $theProperty > $theMax )
				throw new \Exception(
					"Value out of range in offset [$thePath] type [$theType]: "
				   ."[$theProperty] greater than [$theMax]." );					// !@! ==>
		
		} // Has range.
/*
MILKO - Need to check.		
		//
		// Validate pattern.
		//
		if( $thePattern !== NULL )
		{
			//
			// Check pattern.
			//
			if( ! preg_match( $thePattern, (string) $theProperty ) )
				throw new \Exception(
					"Invalid data pattern in offset [$thePath] type [$theType]: "
				   ."[$theProperty] mismatches [$thePattern]." );				// !@! ==>
		
		} // Has pattern.
*/
	
	} // validateProperty.

	 
	/*===================================================================================
	 *	validateReference																*
	 *==================================================================================*/

	/**
	 * Validate reference
	 *
	 * The duty of this method is to validate the provided object reference, the method will
	 * perform the following actions:
	 *
	 * <ul>
	 *	<li><em>Cast reference</em>: The method will cast the object references.
	 *	<li><em>Assert reference</em>: The method will resolve the references.
	 * </ul>
	 *
	 * This method expects an object reference, not an object, the latter case must have
	 * been handled by the {@link parseReference()} method.
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theType			Data type.
	 * @param string				$theClass			Object class name.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function validateReference( &$theProperty,
										   $theType, $theClass, $thePath )
	{
		//
		// Cast identifier.
		//
		switch( $theType )
		{
			case kTYPE_REF_TAG:
			case kTYPE_ENUM:
			case kTYPE_REF_TERM:
			case kTYPE_REF_EDGE:
			case kTYPE_REF_ENTITY:
			case kTYPE_REF_UNIT:
				$theProperty = (string) $theProperty;
				break;

			case kTYPE_REF_NODE:
				$theProperty = (int) $theProperty;
				break;

			case kTYPE_SET:
				foreach( $theProperty as $key => $val )
					$theProperty[ $key ] = (string) $val;
				break;
	
			case kTYPE_REF_SELF:
				switch( $theClass::kSEQ_NAME )
				{
					case Tag::kSEQ_NAME:
					case Term::kSEQ_NAME:
					case Edge::kSEQ_NAME:
					case UnitObject::kSEQ_NAME:
					case User::kSEQ_NAME:
						$theProperty = (string) $theProperty;
						break;
					case Node::kSEQ_NAME:
						$theProperty = (int) $theProperty;
						break;
				}
				break;

		} // Parsed type.

		//
		// Resolve collection.
		//
		$collection
			= $theClass::ResolveCollection(
				$theClass::ResolveDatabase( $this->mDictionary ) );

		//
		// Handle references list.
		//
		if( is_array( $theProperty ) )
		{
			//
			// Iterate list.
			//
			foreach( $theProperty as $val )
			{
				//
				// Assert reference.
				//
				if( ! $collection->matchOne( array( kTAG_NID => $val ), kQUERY_COUNT ) )
					throw new \Exception(
						"Unresolved reference in [$thePath]: "
					   ."($val)." );											// !@! ==>
			
			} // Iterating references list.
		
		} // List of references.
		
		//
		// Handle reference.
		//
		else
		{
			//
			// Assert.
			//
			if( ! $collection->matchOne( array( kTAG_NID => $theProperty ), kQUERY_COUNT ) )
				throw new \Exception(
					"Unresolved reference in [$thePath]: "
				   ."($theProperty)." );										// !@! ==>
		
		} // Scalar reference.
		
	} // validateReference.

	 
	/*===================================================================================
	 *	castProperty																	*
	 *==================================================================================*/

	/**
	 * Cast scalar
	 *
	 * The duty of this method is to cast the provided scalar property to the provided data
	 * type.
	 *
	 * @param mixed					$theProperty		Property.
	 * @param string				$theType			Data type.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses parseStructure()
	 * @uses CastScalar()
	 */
	protected function castProperty( &$theProperty,
									  $theType, $thePath )
	{
		//
		// Cast property.
		//
		switch( $theType )
		{
			//
			// Language strings.
			//
			case kTYPE_TYPED_LIST:
			case kTYPE_LANGUAGE_STRING:
			case kTYPE_LANGUAGE_STRINGS:
				//
				// Init loop storage.
				//
				$tags = Array();
				$path = explode( '.', $thePath );
				//
				// Iterate elements.
				//
				$idxs = array_keys( $theProperty );
				foreach( $idxs as $idx )
				{
					//
					// Check format.
					//
					if( ! is_array( $theProperty[ $idx ] ) )
						throw new \Exception(
							"Invalid offset value element in [$thePath]: "
						   ."the value is not an array." );						// !@! ==>
					//
					// Traverse element.
					//
					$ref = $theProperty[ $idx ];
					$this->parseStructure(
						$theProperty[ $idx ], $path, $tags, $theRefs, TRUE );
				}
				break;
	
			//
			// Other.
			//
			default:
				static::CastScalar( $theProperty, $theType );
				break;
	
		} // Parsed type.
	
	} // castProperty.

	 
	/*===================================================================================
	 *	loadTagInformation																*
	 *==================================================================================*/

	/**
	 * Load tag information
	 *
	 * The duty of this method is to load the provided tag information into the provided
	 * array reference, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This parameter will receive the the tag information, it is a
	 *		reference to an array structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The element key represents the tag sequence number, the value
	 *			is an array structured as follows:
	 *		 <ul>
	 *			<li>{@link kTAG_DATA_TYPE}</tt>: The item holding this key will contain the
	 *				tag data type.
	 *			<li>{@link kTAG_DATA_KIND}</tt>: The item holding this key will contain the
	 *				tag data kind; if the tag has no data kind, this item will be an empty
	 *				array.
	 *			<li>{@link kTAG_MIN_RANGE}</tt>: The item holding this key will contain the
	 *				tag minimum range value.
	 *			<li>{@link kTAG_MAX_RANGE}</tt>: The item holding this key will contain the
	 *				tag maximum range value.
	 *			<li>{@link kTAG_PATTERN}</tt>: The item holding this key will contain the
	 *				tag data pattern.
	 *			<li>{@link kTAG_OBJECT_OFFSETS}</tt>: The item holding this key will
	 *				contain the list of offset paths in which the current tag is referenced
	 *				as a leaf offset (an offset holding a value, not a structure).
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theKind</b>: This parameter holds the tag data kind, if missing, it will be
	 *		an empty array.
	 *	<li><b>$theType</b>: This parameter holds the tag data type, if missing, it will be
	 *		<tt>NULL</tt>.
	 *	<li><b>$thePath</b>: This parameter holds the offset path.
	 *	<li><b>$theTag</b>: This parameter holds the tag sequence number.
	 * </ul>
	 *
	 * @param array					$theTags			Receives tag information.
	 * @param array					$theKind			Data kind.
	 * @param string				$theType			Data type.
	 * @param mixed					$theMin				Receives minimum data range.
	 * @param mixed					$theMax				Receives maximum data range.
	 * @param string				$thePattern			Receives data pattern.
	 * @param string				$thePath			Offset path.
	 * @param string				$theTag				Tag sequence number.
	 *
	 * @access protected
	 */
	public function loadTagInformation( &$theTags,
										$theKind, $theType,
										$theMin, $theMax, $thePattern,
										$thePath, $theTag )
	{
		//
		// Copy tag information.
		//
		if( array_key_exists( $theTag, $theTags ) )
		{
			//
			// Update offset path.
			//
			if( ! in_array( $thePath, $theTags[ $theTag ][ kTAG_OBJECT_OFFSETS ] ) )
				$theTags[ $theTag ][ kTAG_OBJECT_OFFSETS ][] = $thePath;

		} // Already parsed.

		//
		// Collect tag information.
		//
		else
		{
			//
			// Set type and kind.
			//
			$theTags[ $theTag ][ kTAG_DATA_TYPE ] = $theType;
			$theTags[ $theTag ][ kTAG_DATA_KIND ] = $theKind;
	
			//
			// Set data validation information.
			//
			$theTags[ $theTag ][ kTAG_MIN_RANGE ] = $theMin;
			$theTags[ $theTag ][ kTAG_MAX_RANGE ] = $theMax;
			$theTags[ $theTag ][ kTAG_PATTERN ] = $thePattern;
	
			//
			// Set offset path.
			//
			$theTags[ $theTag ][ kTAG_OBJECT_OFFSETS ] = array( $thePath );

		} // New tag.
	
	} // loadTagInformation.

	 
	/*===================================================================================
	 *	loadReferenceInformation														*
	 *==================================================================================*/

	/**
	 * Load reference information
	 *
	 * The duty of this method is to load the provided array reference with the eventual
	 * object references, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theProperty</b>: This parameter represents the current property.
	 *	<li><b>$theRefs</b>: This parameter is a reference to an array which will receive
	 *		the list of object references held by the structure, the array is structured as
	 *		follows:
	 *	<li><b>$theType</b>: This parameter holds the tag data type.
	 *	<li><b>$thePath</b>: This parameter holds the offset path.
	 * </ul>
	 *
	 * @param mixed					$theProperty		Property.
	 * @param array					$theRefs			Receives object references.
	 * @param string				$theType			Data type.
	 * @param string				$thePath			Offset path.
	 *
	 * @access protected
	 *
	 * @uses getReferenceTypeCollection()
	 */
	public function loadReferenceInformation( &$theProperty, &$theRefs, $theType, $thePath )
	{
		//
		// Get reference collection name.
		//
		$collection = $this->getReferenceTypeCollection( $theType );
		
		//
		// Get reference class.
		//
		if( $collection !== NULL )
		{
			//
			// Handle references list.
			//
			if( is_array( $theProperty ) )
			{
				//
				// Iterate list.
				//
				foreach( $theProperty as $reference )
				{
					//
					// Update references.
					//
					if( ! array_key_exists( $collection, $theRefs ) )
						$theRefs[ $collection ] = array( $reference );
					elseif( ! in_array( $theRefs[ $collection ], $theProperty ) )
						$theRefs[ $collection ][] = $reference;
			
				} // Iterating list.
		
			} // List of references.
		
			//
			// Scalar reference.
			//
			else
			{
				//
				// Update references.
				//
				if( ! array_key_exists( $collection, $theRefs ) )
					$theRefs[ $collection ] = array( $theProperty );
				elseif( ! in_array( $theProperty, $theRefs[ $collection ] ) )
					$theRefs[ $collection ][] = $theProperty;
		
			} // Scalar reference.
		
		} // Is an object reference.
		
	} // loadReferenceInformation.

	 
	/*===================================================================================
	 *	filterExistingOffsets															*
	 *==================================================================================*/

	/**
	 * Filter existing offsets
	 *
	 * The duty of this method is to remove from the provided offset paths list all those
	 * elements which exist in the provided collection. This method should be called after
	 * deleting or modifying an object, the processed list can then be used to update the
	 * tag offset paths.
	 *
	 * The provided offsets list should have the same structure as the
	 * {@link kTAG_OBJECT_OFFSETS} property.
	 *
	 * for each offset, the method will check whether it exists in the provided collection,
	 * if that is the case, the method will remove it from the array item, removing tag
	 * elements if there are no offsets left.
	 *
	 * The method expects the tags parameter to be an array.
	 *
	 * @param CollectionObject		$theCollection		Collection.
	 * @param array					$theTags			Object tags.
	 *
	 * @access protected
	 */
	protected function filterExistingOffsets( CollectionObject $theCollection, &$theTags )
	{
		//
		// Iterate tag offsets.
		//
		$tags = array_keys( $theTags );
		foreach( $tags as $tag )
		{
			//
			// Init loop storage.
			//
			$ref = & $theTags[ $tag ];

			//
			// Iterate offsets.
			//
			foreach( $ref as $offset )
			{
				//
				// Check offset.
				//
				if( $theCollection->matchAll(
					array( kTAG_OBJECT_OFFSETS.".$tag" => (string) $offset ),
					kQUERY_ARRAY ) )
					unset( $ref[ $offset ] );
		
			} // Iterating offsets.
		
			//
			// Handle empty list.
			//
			if( ! count( $ref ) )
				unset( $theTags[ $tag ] );
		
		} // Iterating tag offsets.
	
	} // filterExistingOffsets.

	

/*=======================================================================================
 *																						*
 *								PROTECTED STATUS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isReady																			*
	 *==================================================================================*/

	/**
	 * Check if object is ready
	 *
	 * This method should return <tt>TRUE</tt> if the object is ready to be committed.
	 *
	 * In this class we ensure the object is initialised and that it holds the dictionary.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means ready.
	 *
	 * @uses isInited()
	 */
	protected function isReady()
	{
		return ( $this->isInited()
			  && ($this->mDictionary !== NULL) );									// ==>
	
	} // isReady.

		

/*=======================================================================================
 *																						*
 *							PROTECTED OFFSET STATUS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	lockedOffsets																	*
	 *==================================================================================*/

	/**
	 * Return list of locked offsets
	 *
	 * This method should return the list of locked offsets, that is, the offsets which
	 * cannot be modified once the object has been committed.
	 *
	 * In this class we return the list of internal tags plus the {@link kTAG_MASTER}.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_MASTER
	 *
	 * @uses InternalOffsets()
	 */
	protected function lockedOffsets()
	{
		return array_merge(
			array_diff(
				$this->InternalOffsets(),
				array( kTAG_FULL_TEXT_10, kTAG_FULL_TEXT_06, kTAG_FULL_TEXT_03 ) ),
			(array) kTAG_MASTER );													// ==>
	
	} // lockedOffsets.

		

/*=======================================================================================
 *																						*
 *							PROTECTED RESOLUTION UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getReferenceTypeClass															*
	 *==================================================================================*/

	/**
	 * Resolve object reference data type class
	 *
	 * Given a data type, this method will return the base class name corresponding to the
	 * referenced object, or <tt>NULL</tt> if the data type is not an object reference.
	 *
	 * If provided the {@link kTYPE_REF_SELF} data type, the method will return the base
	 * class name of the current object.
	 *
	 * @param string				$theType			Data type.
	 *
	 * @access public
	 * @return string				Base class name.
	 */
	public function getReferenceTypeClass( $theType )
	{
		//
		// Parse by data type.
		//
		switch( $theType )
		{
			case kTYPE_REF_TAG:
				return 'OntologyWrapper\Tag';										// ==>
		
			case kTYPE_SET:
			case kTYPE_ENUM:
			case kTYPE_REF_TERM:
				return 'OntologyWrapper\Term';										// ==>
		
			case kTYPE_REF_NODE:
				return 'OntologyWrapper\Node';										// ==>
		
			case kTYPE_REF_EDGE:
				return 'OntologyWrapper\Edge';										// ==>
		
			case kTYPE_REF_UNIT:
				return 'OntologyWrapper\UnitObject';								// ==>
		
			case kTYPE_REF_ENTITY:
				return 'OntologyWrapper\EntityObject';								// ==>
		
			case kTYPE_REF_SELF:
				if( $this instanceof Tag )
					return 'OntologyWrapper\Tag';									// ==>
				elseif( $this instanceof Term )
					return 'OntologyWrapper\Term';									// ==>
				elseif( $this instanceof Node )
					return 'OntologyWrapper\Node';									// ==>
				elseif( $this instanceof Edge )
					return 'OntologyWrapper\Edge';									// ==>
				elseif( $this instanceof UnitObject )
					return 'OntologyWrapper\UnitObject';							// ==>
				elseif( $this instanceof EntityObject )
					return 'OntologyWrapper\EntityObject';							// ==>
				break;
		
		} // Parsed collection name.
		
		return NULL;																// ==>
	
	} // getReferenceTypeClass.

	 
	/*===================================================================================
	 *	getReferenceTypeCollection														*
	 *==================================================================================*/

	/**
	 * Retun the collection name for a reference data type
	 *
	 * Given a data type, this method will return the collection name corresponding to the
	 * referenced object, or <tt>NULL</tt> if the data type is not an object reference.
	 *
	 * If provided the {@link kTYPE_REF_SELF} data type, the method will return the current
	 * object's collection name.
	 *
	 * @param string				$theType			Data type.
	 *
	 * @access public
	 * @return string				Base class name.
	 */
	public function getReferenceTypeCollection( $theType )
	{
		//
		// Parse by data type.
		//
		switch( $theType )
		{
			case kTYPE_REF_TAG:
				return Tag::kSEQ_NAME;												// ==>
		
			case kTYPE_SET:
			case kTYPE_ENUM:
			case kTYPE_REF_TERM:
				return Term::kSEQ_NAME;												// ==>
		
			case kTYPE_REF_NODE:
				return Node::kSEQ_NAME;												// ==>
		
			case kTYPE_REF_EDGE:
				return Edge::kSEQ_NAME;												// ==>
		
			case kTYPE_REF_UNIT:
				return UnitObject::kSEQ_NAME;										// ==>
		
			case kTYPE_REF_ENTITY:
				return User::kSEQ_NAME;										// ==>
		
			case kTYPE_REF_SELF:
				return static::kSEQ_NAME;											// ==>
		
		} // Parsed data type.
		
		return NULL;																// ==>
	
	} // getReferenceTypeCollection.

		

/*=======================================================================================
 *																						*
 *								PROTECTED REFERENCE UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateObjectReferenceCount														*
	 *==================================================================================*/

	/**
	 * Update object reference count
	 *
	 * This method expects the collection, identifiers and identifier offsets of the objects
	 * in which the reference count property referred to this object will be updated by the
	 * count provided in the last parameter.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The name of the collection containing the objects in
	 *		which the reference count properties should be updated.
	 *	<li><b>$theIdent</b>: The object identifier or list of identifiers of the provided
	 *		collection.
	 *	<li><b>$theIdentOffset</b>: The offset matching to the provided identifiers.
	 *	<li><b>$theCount</b>: The reference count by which to update.
	 * </ul>
	 *
	 * The method will first resolve the reference count property tag corresponding to the
	 * current object using the static {@link ResolveRefCountTag()} method; it will then
	 * select all objects in the provided collection whose provided identifier offset
	 * matches the provided identifiers list; it will then update the reference count
	 * property, resolved in the first step, of all the selected objects by the count
	 * provided in the last parameter.
	 *
	 * The method assumes the current object has its {@link dictionary()} set.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theIdent			Object identifier or identifiers.
	 * @param string				$theIdentOffset		Identifier offset.
	 * @param integer				$theCount			Reference count.
	 *
	 * @access protected
	 *
	 * @uses ResolveRefCountTag()
	 * @uses ResolveCollectionByName()
	 */
	protected function updateObjectReferenceCount( $theCollection,
												   $theIdent, $theIdentOffset = kTAG_NID,
												   $theCount = 1 )
	{
		//
		// Resolve reference count tag according to current object.
		//
		$tag_ref_count = static::ResolveRefCountTag( static::kSEQ_NAME );
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollectionByName(
				$this->mDictionary, (string) $theCollection );
		
		//
		// Update reference count.
		//
		$collection->updateReferenceCount(
			$theIdent, $theIdentOffset, $tag_ref_count, $theCount );
	
	} // updateObjectReferenceCount.

	 
	/*===================================================================================
	 *	updateTagRanges																	*
	 *==================================================================================*/

	/**
	 * Update tag ranges
	 *
	 * This method will update all tag objects range values according to the current object
	 * tag values which are recorded in the {@link kTAG_OBJECT_OFFSETS} property.
	 *
	 * The method will cycle all object's tag offsets and with all those of kind continuous,
	 * {@link kTYPE_QUANTITATIVE}, it will collect the minimum and maximum values and update
	 * the related tag object's  minimum, {@link kTAG_MIN_VAL},and maximum
	 * {@link kTAG_MAX_VAL}, properties.
	 *
	 * The method assumes the current object has its {@link dictionary()} set.
	 *
	 * @access protected
	 */
	protected function updateTagRanges()
	{
		//
		// Init local storage.
		//
		$bounds = Array();
		$tags = $this->offsetGet( kTAG_OBJECT_OFFSETS );
		
		//
		// Iterate offsets.
		//
		foreach( $tags as $tag => $offsets )
		{
			//
			// Get type and kind.
			//
			static::OffsetTypes(
				$this->mDictionary, $tag,
				$type, $kind,
				$min, $max, $pattern,
				TRUE );
		
			//
			// Handle quantitative kinds.
			//
			if( in_array( kTYPE_QUANTITATIVE, $kind ) )
			{
				//
				// Init local storage.
				//
				$min = $max = NULL;
				
				//
				// Iterate offsets.
				//
				foreach( $offsets as $offset )
				{
					//
					// Get value.
					//
					$value = $this->offsetGet( $offset );
					if( $value !== NULL )
					{
						//
						// Handle minimum.
						//
						if( ($min === NULL)
						 || ($value < $min) )
							$min = $value;
						
						//
						// Handle maximum.
						//
						if( ($max === NULL)
						 || ($value > $max) )
							$max = $value;
					
					} // Has value.
				
				} // Iterating offsets.
				
				//
				// Check limits.
				//
				if( ($min !== NULL)
				 || ($max !== NULL) )
				{
					//
					// Init local storage.
					//
					$bounds[ $tag ] = Array();
					$ref = & $bounds[ $tag ];
				
					//
					// Compute minimum modification.
					//
					if( $min !== NULL )
						$ref[ kTAG_MIN_VAL ] = $min;
				
					//
					// Compute maximum modification.
					//
					if( $max !== NULL )
						$ref[ kTAG_MAX_VAL ] = $min;
				
				} // Has at least a limit.
				
			} // Quantitative tag.
		
		} // Iterating offsets.
		
		//
		// Apply modifications.
		//
		if( count( $bounds ) )
			Tag::UpdateRange( $this->mDictionary, $bounds, kTAG_ID_SEQUENCE );
		
	} // updateTagRanges.

		

/*=======================================================================================
 *																						*
 *								PROTECTED GRAPH UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	createGraphNode																	*
	 *==================================================================================*/

	/**
	 * Create graph node
	 *
	 * The responsibiliuty of this method is to create the current object's graph node and
	 * to return its identifier. If the object already has a related node, the method will
	 * only return its identifier.
	 *
	 * If the object should not be stored in the graph, the method should return
	 * <tt>FALSE</tt>.
	 *
	 * This method is called by the {@link preCommitGraphReferences()} method which will
	 * only call it if the current object is not committed and if the current object's
	 * wrapper features a graph connection.
	 *
	 * @param DatabaseGraph			$theGraph			Graph connection.
	 *
	 * @access protected
	 * @return mixed				Node identifier, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 */
	protected function createGraphNode( DatabaseGraph $theGraph )
	{
		//
		// Check if object is already referenced.
		//
		if( $this->offsetExists( kTAG_ID_GRAPH ) )
			return $this->offsetGet( kTAG_ID_GRAPH );								// ==>
		
		//
		// Match existing graph node.
		//
		$id = $this->matchGraphNode( $theGraph );
		if( is_int( $id ) )
			return $id;																// ==>
		
		//
		// Init graph parameters.
		//
		$this->setGraphProperties( $labels, $properties );
		
		return $theGraph->setNode( $properties, $labels );							// ==>
		
	} // createGraphNode.

	 
	/*===================================================================================
	 *	createRelatedGraphNodes															*
	 *==================================================================================*/

	/**
	 * Create related graph nodes
	 *
	 * The responsibility of this method is to create eventual related graph nodes, the
	 * method expects the graph connection and the current object's graph node reference
	 * must have been set in the {@link kTAG_ID_GRAPH} property.
	 *
	 * If In thyis class we do nothing.
	 *
	 * @param DatabaseGraph			$theGraph			Graph connection.
	 *
	 * @access protected
	 */
	protected function createRelatedGraphNodes( DatabaseGraph $theGraph )				   {} 

	 
	/*===================================================================================
	 *	matchGraphNode																	*
	 *==================================================================================*/

	/**
	 * Match graph node
	 *
	 * The responsibility of this method is to check whether a node already exists in the
	 * graph, in that case the method should return its identifier, if not, it should
	 * return <tt>FALSE</tt>.
	 *
	 * The caller determines a match if the returned falue is an integer.
	 *
	 * In this class we return <tt>FALSE</tt> by default.
	 *
	 * @param DatabaseGraph			$theGraph			Graph connection.
	 *
	 * @access protected
	 * @return integer				Graph node identifier, or <tt>FALSE</tt>.
	 */
	protected function matchGraphNode( DatabaseGraph $theGraph )		{	return FALSE;	}

	 
	/*===================================================================================
	 *	setGraphProperties																*
	 *==================================================================================*/

	/**
	 * Compute graph labels and properties
	 *
	 * This method should compute the current object's graph labels and properties, these
	 * should be set in the reference parameters.
	 *
	 * The method returns the following values:
	 *
	 * <ul>
	 *	<li><tt>integer</tt>: An integer indicates that the object is already referenced in
	 *		the graph.
	 *	<li><tt>TRUE</tt>: This value indicates that the properties have been set.
	 *	<li><tt>FALSE</tt>: This value indicates that the object should not be set in the
	 *		graph.
	 * </ul>
	 *
	 * In this class we reset the labels, the default properties are set as:
	 *
	 * <ul>
	 *	<li><tt>STORE</tt>: We set the current object's kSEQ_NAME constant.
	 *	<li><tt>CLASS</tt>: We set the current object's class name. (We cannot use the
	 *		{@link kTAG_CLASS} offset, since it is set by the collection object when
	 *		committing.)
	 *	<li><tt>{@link kTAG_NID}</tt>: We set the object's {@link kTAG_NID}.
	 * </ul>
	 *
	 * Derived classes can call the parent method, then set the labels and eventual other
	 * properties.
	 *
	 * @param array					$theLabels			Labels.
	 * @param array					$theProperties		Properties.
	 *
	 * @access protected
	 * @return mixed				Node identifier, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 */
	protected function setGraphProperties( &$theLabels, &$theProperties )
	{
		//
		// Init graph parameters.
		//
		$theLabels = $theProperties = Array();
	
		//
		// Set data store.
		//
		$theProperties[ 'STORE' ] = static::kSEQ_NAME;
	
		//
		// Set object class.
		//
		$theProperties[ 'CLASS' ] = get_class( $this );
	
		//
		// Set native identifier.
		//
		$theProperties[ kTAG_NID ] = $this->offsetGet( kTAG_NID );
	
	} // setGraphProperties.

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	exportXMLObject																	*
	 *==================================================================================*/

	/**
	 * Export the current object in XML format
	 *
	 * The method will return the XML representation of the object as a SimpleXMLElement
	 * object.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: The root XML node element.
	 *	<li><b>$theWrapper</b>: The data wrapper related to the object.
	 *	<li><b>$theUntracked</b>: The list of offsets to be excluded.
	 * </ul>
	 *
	 * The provided parameter represents dynamic and run-time offsets that are managed by
	 * the object's persistent framework.
	 *
	 * The method will generate a single XML element containing the object.
	 *
	 * @param SimpleXMLElement		$theContainer		Dump container.
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theUntracked		List of untracked offsets.
	 *
	 * @access protected
	 */
	protected function exportXMLObject( \SimpleXMLElement $theContainer,
										Wrapper			  $theWrapper,
														  $theUntracked )
	{
		//
		// Create unit.
		//
		$unit = static::xmlUnitElement( $theContainer );
		
		//
		// Traverse object.
		//
		$this->exportXMLStructure( $this, $unit, $theWrapper, $theUntracked );
	
	} // exportXMLObject.

	 
	/*===================================================================================
	 *	exportXMLStructure																*
	 *==================================================================================*/

	/**
	 * Export the provided structure to XML
	 *
	 * The method will load the provided structure into the provided XML container.
	 *
	 * @param mixed					$theStructure		Structure to export.
	 * @param SimpleXMLElement		$theContainer		XML container (parent).
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theUntracked		List of untracked offsets.
	 *
	 * @access protected
	 */
	protected function exportXMLStructure(					 $theStructure,
										   \SimpleXMLElement $theContainer,
										   Wrapper			 $theWrapper,
															 $theUntracked )
	{
		//
		// Check structure.
		//
		if( is_array( $theStructure )
		 || ($theStructure instanceof \ArrayObject) )
		{
			//
			// Traverse structure.
			//
			foreach( $theStructure as $offset => $property )
			{
				//
				// Handle only numeric offsets.
				//
				if( is_int( $offset )
				 || ctype_digit( $offset ) )
				{
					//
					// Load tag.
					//
					$tag = $theWrapper->getObject( static::resolveOffset( $offset, TRUE ) );
				
					//
					// Skip dynamic tags.
					//
					if( ! in_array( $tag[ kTAG_ID_SEQUENCE ], $theUntracked ) )
					{
						//
						// Create element.
						//
						$element = $theContainer->addChild( kIO_XML_DATA );
						$element->addAttribute( kIO_XML_ATTR_REF_TAG, $tag[ kTAG_NID ] );
					
						//
						// Handle list.
						//
						if( array_key_exists( kTAG_DATA_KIND, $tag )
						 && in_array( kTYPE_LIST, $tag[ kTAG_DATA_KIND ] ) )
						{
							//
							// Iterate list.
							//
							foreach( $property as $value )
							{
								//
								// Create item.
								//
								$item = $element->addChild( kIO_XML_DATA );
				
								//
								// Export property.
								//
								$this->exportXMLProperty(
									$value, $item, $theWrapper, $theUntracked, $tag );
			
							} // Iterating list.
					
						} // List.
					
						//
						// Handle scalar.
						//
						else
							$this->exportXMLProperty(
								$property, $element, $theWrapper, $theUntracked, $tag );
		
					} // Not a dynamic tag.
				
				} // Numeric offset.
		
			} // Traversing structure.
		
		} // Provided iterator.
		
		else
			throw new \Exception(
				"Unable to export object: "
			   ."invalid or unsupported structure iterator." );					// !@! ==>
	
	} // exportXMLStructure.

	 
	/*===================================================================================
	 *	exportXMLProperty																*
	 *==================================================================================*/

	/**
	 * Export the provided property to XML
	 *
	 * The method will load the provided property into the provided XML container.
	 *
	 * @param mixed					$theProperty		Property to export.
	 * @param SimpleXMLElement		$theContainer		XML container (parent).
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param array					$theUntracked		List of untracked offsets.
	 * @param array					$theTag				Property tag.
	 *
	 * @access protected
	 */
	protected function exportXMLProperty(					$theProperty,
										  \SimpleXMLElement $theContainer,
										  Wrapper			$theWrapper,
															$theUntracked,
															$theTag )
	{
		//
		// Parse by data type.
		//
		switch( $theTag[ kTAG_DATA_TYPE ] )
		{
			case kTYPE_STRUCT:
				$this->exportXMLStructure(
					$theProperty, $theContainer, $theWrapper, $theUntracked );
				break;
			
			case kTYPE_TYPED_LIST:
			case kTYPE_LANGUAGE_STRING:
			case kTYPE_LANGUAGE_STRINGS:
				foreach( $theProperty as $element )
				{
					$tmp0 = $theContainer->addChild( kIO_XML_DATA );
					foreach( $element as $key => $value )
					{
						$tmp1 = $tmp0->addChild( kIO_XML_DATA );
						$tmp1->addAttribute( kIO_XML_ATTR_QUAL_KEY, $key );
						$node = dom_import_simplexml( $tmp1 );
						$tmp = $node->ownerDocument;
						$node->appendChild( $tmp->createCDATASection( $value ) );
					}
				}
				break;
			
			case kTYPE_SHAPE:
				$tmp0 = $theContainer->addChild( kIO_XML_DATA, $theProperty[ kTAG_TYPE ] );
				$tmp0->addAttribute( kIO_XML_ATTR_QUAL_KEY, kTAG_TYPE );
				$tmp0 = $theContainer->addChild( kIO_XML_DATA );
				$tmp0->addAttribute( kIO_XML_ATTR_QUAL_KEY, kTAG_GEOMETRY );
				$this->exportXMLArray( $theProperty[ kTAG_GEOMETRY ], $tmp0 );
				break;
			
			case kTYPE_ENUM:
				$theContainer[ 0 ] = $theProperty;
				break;
			
			case kTYPE_SET:
				foreach( $theProperty as $element )
					$theContainer->addChild( kIO_XML_DATA, $element );
				break;
			
			case kTYPE_ARRAY:
				foreach( $theProperty as $key => $value )
				{
					$tmp0 = $theContainer->addChild( kIO_XML_DATA, $value );
					$tmp0->addAttribute( kIO_XML_ATTR_QUAL_KEY, $key );
				}
				break;
			
			case kTYPE_BOOLEAN:
				$theProperty = (int) $theProperty;
			case kTYPE_STRING:
			case kTYPE_TEXT:
			case kTYPE_URL:
			case kTYPE_YEAR:
			case kTYPE_DATE:
			case kTYPE_INT:
			case kTYPE_FLOAT:
			case kTYPE_REF_TAG:
			case kTYPE_REF_TERM:
			case kTYPE_REF_NODE:
			case kTYPE_REF_EDGE:
			case kTYPE_REF_ENTITY:
			case kTYPE_REF_UNIT:
			case kTYPE_REF_SELF:
				$node = dom_import_simplexml( $theContainer );
				$tmp = $node->ownerDocument;
				$node->appendChild( $tmp->createCDATASection( $theProperty ) );
				break;
			
			case kTYPE_MIXED:
				break;
		
		} // Parsed by data type.
	
	} // exportXMLProperty.

	 
	/*===================================================================================
	 *	exportXMLArray																	*
	 *==================================================================================*/

	/**
	 * Export the provided array to XML
	 *
	 * The method will load the provided array into the provided XML container, the method
	 * expects an array or a series of nested arrays.
	 *
	 * @param mixed					$theProperty		Property to export.
	 * @param SimpleXMLElement		$theContainer		XML container (parent).
	 *
	 * @access protected
	 */
	protected function exportXMLArray(					 $theProperty,
									   \SimpleXMLElement $theContainer )
	{
		//
		// Handle array.
		//
		if( is_array( $theProperty ) )
		{
			//
			// Create element.
			//
			$node = $theContainer->addChild( kIO_XML_DATA );
			
			//
			// Iterate array.
			//
			foreach( $theProperty as $element )
				$this->exportXMLArray( $element, $node );
		
		} // Array.
		
		//
		// Handle scalar.
		//
		else
			$theContainer->addChild( kIO_XML_DATA, $theProperty );
	
	} // exportXMLArray.

	 
	/*===================================================================================
	 *	xmlUnitElement																	*
	 *==================================================================================*/

	/**
	 * Return XML unit element
	 *
	 * This method should return the default XML unit element as a SimpleXMLElement object.
	 *
	 * The method expects the root element, it will add the unit element to it and return
	 * the newly created child element.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	abstract protected function xmlUnitElement( \SimpleXMLElement $theRoot );

		

/*=======================================================================================
 *																						*
 *								PROTECTED IMPORT UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadXML																			*
	 *==================================================================================*/

	/**
	 * Load from XML
	 *
	 * This method will load the object with the data contained in the provided XML
	 * container.
	 *
	 * In this class the method will process all the {@link kIO_XML_DATA} element of the
	 * root node, derived classes should overload this method to handle root level
	 * attributes.
	 *
	 * The object is expected to have its wrapper set.
	 *
	 * @param SimpleXMLElement		$theContainer		Export container.
	 *
	 * @access protected
	 */
	protected function loadXML( \SimpleXMLElement $theContainer )
	{
		//
		// Iterate nodes.
		//
		foreach( $theContainer->{kIO_XML_DATA} as $element )
		{
			//
			// Init loop storage.
			//
			$value = NULL;
			
			//
			// parse XML element.
			//
			$this->parseXMLElement( $element, $value );
		
		} // Iterating root elements.
	
	} // loadXML.

	 
	/*===================================================================================
	 *	parseXMLElement																	*
	 *==================================================================================*/

	/**
	 * Parse XML element
	 *
	 * The duty of this method is to parse the provided XML element and return the element
	 * value in the provided container reference and the element offset as the result.
	 *
	 * @param SimpleXMLElement		$theElement			XML element.
	 * @param mixed					$theContainer		Value container.
	 *
	 * @access protected
	 * @return string				Offset.
	 *
	 * @throws Exception
	 */
	protected function parseXMLElement( \SimpleXMLElement $theElement, &$theContainer )
	{
		//
		// Parse element offset.
		//
		$offset = $this->parseXMLElementOffset( $theElement );
		
		//
		// Parse element value.
		//
		$value = $this->parseXMLElementValue( $theElement, $theContainer );
		
		//
		// Handle sub-structure.
		//
		if( is_array( $theContainer ) )
			$theContainer[ $offset ] = $value;
		
		//
		// Handle property.
		//
		else
			$this->offsetSet( $offset, $value );
		
		
		
		//
		// Get offset from tag reference.
		//
		if( $theElement[ kIO_XML_ATTR_REF_TAG ] !== NULL )
			$offset = $this->resolveOffset(
				(string) $theElement[ kIO_XML_ATTR_REF_TAG ], TRUE );
		
		//
		// Get offset from tag sequence number.
		//
		elseif( $theElement[ kIO_XML_ATTR_REF_TAG_SEQ ] !== NULL )
			$offset = (int) $theElement[ kIO_XML_ATTR_REF_TAG_SEQ ];
		
		//
		// Get offset from constant.
		//
		elseif( $theElement[ kIO_XML_ATTR_QUAL_CONST ] !== NULL )
			$offset = constant( (string) $theElement[ kIO_XML_ATTR_QUAL_CONST ] );
		
		//
		// Get offset from key.
		//
		elseif( $theElement[ kIO_XML_ATTR_QUAL_KEY ] !== NULL )
			$offset = constant( (string) $theElement[ kIO_XML_ATTR_QUAL_KEY ] );
		
		//
		// Handle array.
		//
		elseif( ! count( $theElement->attributes() ) )
		{
		}
		
		else
			throw new \Exception(
				"Unable to load XML element: "
			   ."missing tag reference." );										// !@! ==>
		
		//
		// Handle value.
		//
		$theContainer = $this->parseXMLItem( $theElement, $offset );
		
		return $offset;																// ==>
	
	} // parseXMLElement.

	 
	/*===================================================================================
	 *	parseXMLItem																	*
	 *==================================================================================*/

	/**
	 * Parse XML item
	 *
	 * The duty of this method is to parse the provided XML element and return the element
	 * value in the provided reference parameter and the element offset as the result.
	 *
	 * @param SimpleXMLElement		$theElement			XML element.
	 * @param string				$theOffset			Property offset.
	 *
	 * @access protected
	 * @return mixed				Property value.
	 *
	 * @throws Exception
	 */
	protected function parseXMLItem( \SimpleXMLElement $theElement, $theOffset )
	{
		//
		// Handle node reference.
		//
		if( ($tmp = $theElement[ kIO_XML_ATTR_REF_NODE ]) !== NULL )
			return $this->parseXMLNodeReference( $theElement, (string) $tmp );		// ==>
	
	} // parseXMLItem.

	 
	/*===================================================================================
	 *	parseXMLNodeReference															*
	 *==================================================================================*/

	/**
	 * Parse XML node reference
	 *
	 * The duty of this method is to parse the provided XML node reference element and
	 * return the reference value.
	 *
	 * The method expects the XML element and the reference type.
	 *
	 * @param SimpleXMLElement		$theElement			XML element.
	 * @param string				$theType			Reference type.
	 *
	 * @access protected
	 * @return mixed				Node reference.
	 *
	 * @throws Exception
	 */
	protected function parseXMLNodeReference( \SimpleXMLElement $theElement, $theType )
	{
		//
		// Parse reference type.
		//
		switch( $theType )
		{
			case kIO_XML_ATTR_NODE_TAG:
				return
					Node::GetTagMaster(
						$this->mDictionary,
						(string) $theElement,
						kQUERY_ASSERT | kQUERY_NID )
							[ kTAG_NID ];											// ==>
		
			case kIO_XML_ATTR_NODE_SEQ:
				return
					Node::GetTagMaster(
						$this->mDictionary,
						(int) (string) $theElement,
						kQUERY_ASSERT | kQUERY_NID )
							[ kTAG_NID ];											// ==>
				break;
		
			case kIO_XML_ATTR_NODE_TERM:
				return
					Node::GetTermMaster(
						$this->mDictionary,
						(string) $theElement,
						kQUERY_ASSERT | kQUERY_NID )
							[ kTAG_NID ];											// ==>
				break;
		
			case kIO_XML_ATTR_NODE_PID:
				return
					Node::GetPidNode(
						$this->mDictionary,
						(string) $theElement,
						kQUERY_ASSERT | kQUERY_NID )
							[ kTAG_NID ];											// ==>
				break;
		
			case kIO_XML_ATTR_NODE_ID:
				return (int) (string) $theElement;									// ==>
				break;
		
		} // Parsed node reference type.
		
		throw new \Exception(
			"Unable to set property: "
		   ."invalid node reference type [$tmp]." );							// !@! ==>
	
	} // parseXMLNodeReference.

		

/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveWrapper																	*
	 *==================================================================================*/

	/**
	 * Resolve wrapper
	 *
	 * This method can be used to resolve the wrapper, it expects a reference to a wrapper
	 * which will either set the current object's {@link dictionary()}, or will be set by
	 * the current object's {@link dictionary()}.
	 *
	 * The method assumes that the wrapper must be resolved, if that is not the case, the
	 * method will raise an exception.
	 *
	 * @param reference				$theWrapper			Data wrapper.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function resolveWrapper( &$theWrapper )
	{
		//
		// Use dictionary.
		//
		if( $theWrapper === NULL )
		{
			//
			// Set wrapper with dictionary.
			//
			$theWrapper = $this->mDictionary;
			if( $theWrapper === NULL )
				throw new \Exception( "Missing wrapper." );						// !@! ==>
		
		} // Used object dictionary.
		
		//
		// Set dictionary.
		//
		elseif( $theWrapper instanceof Wrapper )
			$this->dictionary( $theWrapper );
		
		//
		// Invalid wrapper.
		//
		else
			throw new \Exception( "Invalid wrapper type." );					// !@! ==>
	
	} // resolveWrapper.

	 
	/*===================================================================================
	 *	compareObjectOffsets															*
	 *==================================================================================*/

	/**
	 * Compare object offsets
	 *
	 * This method expects two parameters structured as the {@link kTAG_OBJECT_OFFSETS}
	 * property, it will return either the differences or the matches between the two
	 * parameters.
	 *
	 * The difference is computed by selecting all elements featured by the first parameter
	 * not existing in the second parameter; the similarity is computed by selecting only
	 * those elements belonging to both parameters.
	 *
	 * If the third parameter is <tt>TRUE</tt>, the method will compute the difference, if
	 * not, the similarity.
	 *
	 * The method will return the computed array, no elements will be empty.
	 *
	 * @param reference				$theNew				New parameter.
	 * @param reference				$theOld				Old parameter.
	 * @param boolean				$doDiff				<tt>TRUE</tt> compute difference.
	 *
	 * @access protected
	 * @return array				The difference or intersection.
	 *
	 * @see kTAG_OBJECT_OFFSETS
	 */
	protected function compareObjectOffsets( &$theNew, &$theOld, $doDiff )
	{
		//
		// Init local storage.
		//
		$result = Array();
		
		//
		// Iterate reference parameter.
		//
		foreach( $theNew as $tag => $offsets )
		{
			//
			// Handle difference.
			//
			if( $doDiff )
			{
				//
				// New tag.
				//
				if( ! array_key_exists( $tag, $theOld ) )
					$result[ $tag ] = $offsets;
				
				//
				// Existing tag.
				//
				else
				{
					//
					// Select differences.
					//
					$tmp = array_diff( $offsets, $theOld[ $tag ] );
					if( count( $tmp ) )
						$result[ $tag ] = $tmp;
				
				} // Existing tag.
			
			} // Difference.
		
			//
			// Handle intersection.
			//
			else
			{
				//
				// Existing tag.
				//
				if( array_key_exists( $tag, $theOld ) )
				{
					//
					// Compute intersection.
					//
					$tmp = array_intersect( $offsets, $theOld[ $tag ] );
					if( count( $tmp ) )
						$result[ $tag ] = $tmp;
				
				} // Existing tag.
			
			} // Difference.
		
		} // Iterating first parameter.
		
		return $result;																// ==>
	
	} // compareObjectOffsets.

	 
	/*===================================================================================
	 *	compareObjectReferences															*
	 *==================================================================================*/

	/**
	 * Compare object references
	 *
	 * This method expects two parameters structured as the {@link kTAG_OBJECT_REFERENCES}
	 * property, it will return either the differences or the matches between the two
	 * parameters.
	 *
	 * The difference is computed by selecting all elements featured by the first parameter
	 * not existing in the second parameter; the similarity is computed by selecting only
	 * those elements belonging to both parameters.
	 *
	 * If the third parameter is <tt>TRUE</tt>, the method will compute the difference, if
	 * not, the similarity.
	 *
	 * The method will return the computed array, no elements will be empty.
	 *
	 * @param reference				$theNew				New parameter.
	 * @param reference				$theOld				Old parameter.
	 * @param boolean				$doDiff				<tt>TRUE</tt> compute difference.
	 *
	 * @access protected
	 * @return array				The difference or intersection.
	 *
	 * @see kTAG_OBJECT_REFERENCES
	 */
	protected function compareObjectReferences( &$theNew, &$theOld, $doDiff )
	{
		//
		// Init local storage.
		//
		$result = Array();
		
		//
		// Iterate reference parameter.
		//
		foreach( $theNew as $collection => $identifiers )
		{
			//
			// Handle difference.
			//
			if( $doDiff )
			{
				//
				// New collection.
				//
				if( ! array_key_exists( $collection, $theOld ) )
					$result[ $collection ] = $identifiers;
				
				//
				// Existing collection.
				//
				else
				{
					//
					// Select differences.
					//
					$tmp = array_diff( $identifiers, $theOld[ $collection ] );
					if( count( $tmp ) )
						$result[ $collection ] = $tmp;
				
				} // Existing collection.
			
			} // Difference.
		
			//
			// Handle intersection.
			//
			else
			{
				//
				// Existing collection.
				//
				if( array_key_exists( $collection, $theOld ) )
				{
					//
					// Compute intersection.
					//
					$tmp = array_intersect( $identifiers, $theOld[ $collection ] );
					if( count( $tmp ) )
						$result[ $collection ] = $tmp;
				
				} // Existing collection.
			
			} // Difference.
		
		} // Iterating first parameter.
		
		return $result;																// ==>
	
	} // compareObjectReferences.

	 
	/*===================================================================================
	 *	addToFullText																	*
	 *==================================================================================*/

	/**
	 * Add value to full text
	 *
	 * This method will add the label of the provided term to the {@link kTAG_FULL_TEXT_10},
	 * {@link kTAG_FULL_TEXT_06} and {@link kTAG_FULL_TEXT_03} of the current object.
	 *
	 * The method assumes the current object has its wrapper set and the default language
	 * definition is in the includes.
	 *
	 * If the term is not resolved, the method will do nothing.
	 *
	 * @param mixed					$theValue			Value to add.
	 * @param string				$theLanguage		Text language.
	 * @param string				$theType			Tag type.
	 * @param array					$theKind			Tag kind.
	 *
	 * @access protected
	 *
	 * @see kTAG_FULL_TEXT_10 kTAG_FULL_TEXT_06 kTAG_FULL_TEXT_03
	 * @see kTYPE_FULL_TEXT_10 kTYPE_FULL_TEXT_06 kTYPE_FULL_TEXT_03
	 */
	protected function addToFullText( $theValue, $theLanguage, $theType, $theKind )
	{
		//
		// Check value.
		//
		if( $theValue !== NULL )
		{
			//
			// Check kind.
			//
			if( $weight = array_intersect( $theKind,
										   array( kTYPE_FULL_TEXT_10,
												  kTYPE_FULL_TEXT_06,
												  kTYPE_FULL_TEXT_03 ) ) )
			{
				//
				// Get weight.
				//
				switch( $weight = array_shift( $weight ) )
				{
					case kTYPE_FULL_TEXT_10:
						$offset = kTAG_FULL_TEXT_10;
						break;
					case kTYPE_FULL_TEXT_06:
						$offset = kTAG_FULL_TEXT_06;
						break;
					case kTYPE_FULL_TEXT_03:
						$offset = kTAG_FULL_TEXT_03;
						break;
					
					default:
						throw new \Exception(
							"Unable to set full-text property: "
						   ."invalid weight type [$weight]." );					// !@! ==>
				}
				
				//
				// Parse by type.
				//
				switch( $theType )
				{
					case kTYPE_TEXT:
					case kTYPE_STRING:
						//
						// Recurse arrays.
						//
						if( is_array( $theValue ) )
						{
							foreach( $theValue as $value )
								$this->addToFullText(
									$value,
									$theLanguage,
									$theType,
									$theKind );
						}
						//
						// Hamdle scalars.
						//
						else
						{
							//
							// Skip empty strings.
							//
							if( strlen( $theValue ) )
							{
								//
								// Init full-text property.
								//
								$text = $this->offsetGet( $offset );
								if( $text === NULL )
									$text = Array();
								//
								// Add to property.
								//
								if( ! in_array( $theValue, $text ) )
								{
									$text[] = (string) $theValue;
									$this->offsetSet( $offset, $text );
								}
							}
						}
						break;
			
					case kTYPE_LANGUAGE_STRING:
					case kTYPE_LANGUAGE_STRINGS:
					case kTYPE_TYPED_LIST:
						//
						// Init full-text property.
						//
						$text = $this->offsetGet( $offset );
						if( $text === NULL )
							$text = Array();
						//
						// Iterate list.
						//
						foreach( $theValue as $value )
						{
							//
							// Assert text component.
							//
							if( array_key_exists( kTAG_TEXT, $value ) )
							{
								//
								// Handle typed list.
								//
								if( $theType == kTYPE_TYPED_LIST )
								{
									if( strlen( $value[ kTAG_TEXT ] )
									 && (! in_array( $value[ kTAG_TEXT ], $text )) )
										$text[] = $value[ kTAG_TEXT ];
								}
								//
								// Handle language strings.
								//
								else
								{
									//
									// Select language.
									//
									if( (! array_key_exists( kTAG_LANGUAGE, $value ))
									 || ($value[ kTAG_LANGUAGE ] == $theLanguage) )
									{
										//
										// Handle language string.
										//
										if( $theType == kTYPE_LANGUAGE_STRING )
										{
											if( strlen( $value[ kTAG_TEXT ] )
											 && (! in_array( $value[ kTAG_TEXT ], $text )) )
												$text[] = $value[ kTAG_TEXT ];
										}
										//
										// Handle language strings.
										//
										else
										{
											foreach( $value[ kTAG_TEXT ] as $element )
											{
												if( strlen( $element )
												 && (! in_array( $element, $text )) )
													$text[] = (string) $element;
											}
										}
									}
								}
							}
						}
						//
						// Update to property.
						//
						if( count( $text ) )
							$this->offsetSet( $offset, $text );
						break;
			
					case kTYPE_SET:
						//
						// Recurse enumerated set.
						//
						foreach( $theValue as $value )
							$this->addToFullText(
								$value,
								$theLanguage,
								kTYPE_ENUM,
								$theKind );
						break;
		
					case kTYPE_ENUM:
						//
						// Get term.
						//
						$term = new Term( $this->mDictionary, $theValue );
						//
						// Recurse with term label.
						//
						if( $term->isCommitted() )
							$this->addToFullText(
								$term[ kTAG_LABEL ],
								$theLanguage,
								kTYPE_LANGUAGE_STRING,
								$theKind );
						else
							throw new \Exception(
								"Unable to set full-text property: "
							   ."unresolved term [$theValue]." );				// !@! ==>
						break;
		
				} // Parsed by type.
			
			} // Add to full-text.
		
		} // Value provided.
	
	} // addToFullText.

	 

} // class PersistentObject.


?>
