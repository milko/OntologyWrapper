<?php

/*=======================================================================================
 *																						*
 *									Tags.inc.php										*
 *																						*
 *======================================================================================*/
 
/**
 * Default attribute tags.
 *
 * This file contains the default ontology tag definitions, these offsets represent the
 * default tags used in the objects comprising the ontology and the core objects of this
 * library.
 *
 * Each entry is a definitions that holds the <em>global identifier</em> of the tag.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 13/01/2014
 */

/*
 * Tokens.
 */
//require_once( kPATH_DEFINITIONS_ROOT."/Tokens.inc.php" );

/*=======================================================================================
 *	INTERNAL OFFSETS																	*
 *======================================================================================*/

/**
 * Native identifier (<code>_id</code>)
 *
 * This offset is the <em>primary key</em> of all persistent objects, it doesn't have a
 * specific data type and all objects must have it. This attribute is internal and it is not
 * defined in the ontology.
 */
define( "kTAG_NID",						'_id' );

/**
 * Class (<code>class</code>)
 *
 * This offset represents the <em>object class name</em>, this string is used to
 * instantiate the correct object once loaded from a container. This attribute is internal
 * and it is not defined in the ontology.
 */
define( "kTAG_CLASS",					'class' );

/**
 * Type (<code>type</code>)
 *
 * Data type: kTYPE_STRING
 *
 * This offset represents a <em>type</em> or <em>category</em>, it must only be used as a
 * key element of a structured property. It is a string which qualifies the structure or
 * structure element. It is used in properties of type {@link kTYPE_SHAPE} as the shape type
 * indicator and in {@link kTYPE_TYPED_LIST} properties to qualify the current element.
 */
define( "kTAG_TYPE",					'type' );

/**
 * Language (<code>lang</code>)
 *
 * Data type: kTYPE_STRING
 *
 * This tag holds a <em>string</em> which represents a specific <em>language name or
 * code</em>, this tag is generally used as an element of a structure for indicating the
 * element's language. It is a required element of properties of type
 * {@link kTYPE_LANGUAGE_STRING} and {@link kTYPE_LANGUAGE_STRINGS}.
 */
define( "kTAG_LANGUAGE",				'lang' );

/**
 * Text (<code>text</code>)
 *
 * Data type: kTYPE_STRING
 *
 * This tag holds a <em>string</em> which represents a <em>text</em>, this tag is generally
 * used as an element of a structure for indicating the element's text. It is a required
 * element of properties of type {@link kTYPE_LANGUAGE_STRING} and
 * {@link kTYPE_LANGUAGE_STRINGS}.
 */
define( "kTAG_TEXT",					'text' );

/**
 * URL (<code>url</code>)
 *
 * Data type: kTYPE_URL
 *
 * This tag holds a <em>string</em> which represents an <em>internet address</em>, this tag
 * is generally used to hold an URL.
 */
define( "kTAG_URL",						'url' );

/**
 * Tag (<code>tag</code>)
 *
 * Data type: kTYPE_REF_TAG
 *
 * This tag holds a <em>string</em> which represents a reference to a tag, the latter's
 * native identifier.
 */
define( "kTAG_TAG_REF",					'tag' );

/**
 * Term (<code>term</code>)
 *
 * Data type: kTYPE_REF_TERM
 *
 * This tag holds a <em>string</em> which represents a reference to a term, the latter's
 * native identifier.
 */
define( "kTAG_TERM_REF",				'term' );

/**
 * Node (<code>node</code>)
 *
 * Data type: kTYPE_REF_NODE
 *
 * This tag holds an <em>integer</em> which represents a reference to a node, the latter's
 * native identifier.
 */
define( "kTAG_NODE_REF",				'node' );

/**
 * Edge (<code>edge</code>)
 *
 * Data type: kTYPE_REF_EDGE
 *
 * This tag holds a <em>string</em> which represents a reference to an edge, the latter's
 * native identifier.
 */
define( "kTAG_EDGE_REF",				'edge' );

/**
 * User (<code>user</code>)
 *
 * Data type: kTYPE_REF_USER
 *
 * This tag holds a <em>string</em> which represents a reference to a user, the latter's
 * native identifier.
 */
define( "kTAG_USER_REF",				'user' );

/**
 * Unit (<code>unit</code>)
 *
 * Data type: kTYPE_REF_UNIT
 *
 * This tag holds a <em>string</em> which represents a reference to a unit, the latter's
 * native identifier.
 */
define( "kTAG_UNIT_REF",				'unit' );

/**
 * Session (<code>session</code>)
 *
 * Data type: kTYPE_REF_SESSION
 *
 * This tag holds a <em>database native identifier</em> which represents a reference to a
 * session, the latter's native identifier.
 */
define( "kTAG_SESSION_REF",				'session' );

/**
 * Transaction (<code>transaction</code>)
 *
 * Data type: kTYPE_REF_TRANSACTION
 *
 * This tag holds a <em>database native identifier</em> which represents a reference to a
 * transaction, the latter's native identifier.
 */
define( "kTAG_TRANSACTION_REF",			'transaction' );

/**
 * File (<code>file</code>)
 *
 * Data type: kTYPE_REF_FILE
 *
 * This tag holds a <em>database native identifier</em> which represents a reference to a
 * file, the latter's native identifier.
 */
define( "kTAG_FILE_REF",				'file' );

/**
 * Collection (<code>collection</code>)
 *
 * Data type: kTYPE_STRING
 *
 * This tag holds a <em>string</em> which represents a reference to a database collection,
 * the latter's name.
 */
define( "kTAG_COLLECTION_REF",			'collection' );

/**
 * Database (<code>database</code>)
 *
 * Data type: kTYPE_STRING
 *
 * This tag holds a <em>string</em> which represents a reference to a database, the latter's
 * name.
 */
define( "kTAG_DATABASE_REF",			'database' );

/**
 * Geometry (<code>coordinates</code>)
 *
 * Data type: kTYPE_FLOAT
 * Data kind: kTYPE_LIST
 *
 * This offset represents the <em>geometry of a shape</em>, it is by default an array which
 * can be nested at several levels, depending on the type of geometry. It is used in
 * properties of type {@link kTYPE_SHAPE} to provide the shape geometry; incidentally, it
 * is named <tt>coordinates</tt> so that when used with the {@link kTAG_TYPE} tag it forms
 * a GeoJSON object.
 */
define( "kTAG_GEOMETRY",				'coordinates' );

/**
 * Radius (<code>radius</code>)
 *
 * Data type: kTYPE_INT
 *
 * This offset represents the <em>radius of a circle shape</em> in meters, it is used in
 * conjuction with {@link kTAG_TYPE} and {@link kTAG_GEOMETRY} to create a GeoJSON circle
 * shape.
 */
define( "kTAG_RADIUS",					'radius' );

/**
 * Full text values, weight 10 (<code>text-10</code>)
 *
 * Data type: kTYPE_STRING
 * Data kind: kTYPE_LIST
 *
 * This offset is automatically filled with all distinct values coming from all properties
 * whose tag has a kind of {@link kTYPE_FULL_TEXT_10}, enumerated values will be resolved to
 * the default language label. This field will be indexed for full-text search with weight
 * 10.
 */
define( "kTAG_FULL_TEXT_10",			'text-10' );

/**
 * Full text values, weight 6 (<code>text-06</code>)
 *
 * Data type: kTYPE_STRING
 * Data kind: kTYPE_LIST
 *
 * This offset is automatically filled with all distinct values coming from all properties
 * whose tag has a kind of {@link kTYPE_FULL_TEXT_06}, enumerated values will be resolved to
 * the default language label. This field will be indexed for full-text search with weight
 * 6.
 */
define( "kTAG_FULL_TEXT_06",			'text-06' );

/**
 * Full text values, weight 03 (<code>text-03</code>)
 *
 * Data type: kTYPE_STRING
 * Data kind: kTYPE_LIST
 *
 * This offset is automatically filled with all distinct values coming from all properties
 * whose tag has a kind of {@link kTYPE_FULL_TEXT_03}, enumerated values will be resolved to
 * the default language label. This field will be indexed for full-text search with weight
 * 3.
 */
define( "kTAG_FULL_TEXT_03",			'text-03' );

/*=======================================================================================
 *	FILE OBJECT INTERNAL TAGS															*
 *======================================================================================*/

/**
 * Filename (<code>filename</code>)
 *
 * Data type: kTYPE_STRING
 *
 * This tag holds a <em>string</em> representing a file name or path.
 */
define( "kTAG_FILE_NAME",				'filename' );

/**
 * Upload date (<code>uploadDate</code>)
 *
 * Data type: kTYPE_TIME_STAMP
 *
 * This tag holds a <em>time-stamp</em> indicating an upload event date.
 */
define( "kTAG_FILE_UPLOAD_DATE",		'uploadDate' );

/**
 * Length (<code>length</code>)
 *
 * Data type: kTYPE_INT
 *
 * This tag holds an <em>integer</em> representing a generic length.
 */
define( "kTAG_FILE_LENGTH",				'length' );

/**
 * Chunk size (<code>chunkSize</code>)
 *
 * Data type: kTYPE_INT
 *
 * This tag holds an <em>integer</em> representing a chunk size.
 */
define( "kTAG_FILE_CHUNK_SIZE",			'chunkSize' );

/**
 * Checksum (<code>md5</code>)
 *
 * Data type: kTYPE_STRING
 *
 * This tag holds a <em>string</em> representing an MD5 checksum.
 */
define( "kTAG_FILE_MD5",				'md5' );

/**
 * Content type (<code>contentType</code>)
 *
 * Data type: kTYPE_STRING
 *
 * This tag holds a <em>string</em> representing the file's MIME type.
 */
define( "kTAG_FILE_MIME_TYPE",			'contentType' );

/**
 * Aliases (<code>aliases</code>)
 *
 * Data type: kTYPE_STRING (array)
 *
 * This tag holds a list of <em>strings</em> representing the file's aliases.
 */
define( "kTAG_FILE_ALIASES",			'aliases' );

/*=======================================================================================
 *	OBJECT IDENTIFICATION TAGS															*
 *======================================================================================*/

/**
 * Namespace (<code>:namespace</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TERM}
 * </ul>
 *
 * This tag is a <em>reference to a term object</em>, it is a <em>string</em> representing
 * the <em>native identifier</em> of a term. Namespaces are used to <em>disambiguate
 * homonym local identifiers</em> in order to come up with a global unique identifier. This
 * identifier is <em>persistent</em>. 
 */
// MILKO - To prevent needing to include tokens.
//
//define( "kTAG_NAMESPACE",				kTOKEN_TAG_PREFIX.'1' );
define( "kTAG_NAMESPACE",				'@1' );

/**
 * Local identifier (<code>:id-local</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag is a <em>string</em> which represents the <em>local identifier</em> of an
 * object. Local identifiers are <em>unique within their namespace</em> and are
 * <em>persistent</em>. In general, the namespace is concatenated to the local identifier to
 * form the persistent identifier.
 */
define( "kTAG_ID_LOCAL",				'@2' );

/**
 * Persistent identifier (<code>:id-persistent</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag is a <em>string</em> which represents the <em>persistent identifier</em> of an
 * object. Persistent identifiers are <em>unique across namespaces</em>, they are
 * <em>global</em>, in that they <em>include the namespace</em> and they are
 * <em>persistent</em>. In general, this identifier is the concatenation of the namespace
 * and the local identifier.
 */
define( "kTAG_ID_PERSISTENT",			'@3' );

/**
 * Symbol (<code>:id-symbol</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag is an <em>string value</em> representing the <em>symbol</em> or <em>acronym</em>
 * of an object. This value is generally used to reference the object in data templates. The
 * value should be unique within the set of elements comprising the data template in which
 * the object is used, although this value is not required to be globally unique.
 */
define( "kTAG_ID_SYMBOL",				'@4' );

/**
 * Valid identifier (<code>:id-valid</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag is a <em>string</em> which represents the <em>persistent global identifier</em>
 * of the object that is <em>considered the valid choice</em>. This is generally used by
 * <em>legacy</em> or <em>obsolete</em> objects for referring to the <em>valid</em>,
 * <em>current</em> or <em>official</em> object.
 */
define( "kTAG_ID_VALID",				'@5' );

/**
 * Sequence number (<code>:id-sequence</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag is an <em>integer sequence number</em> which is <em>automatically assigned</em>
 * to objects just before they are <em>committed</em>. This represents an <em>identifier
 * unique to the collection</em> to which the object belongs. This identifier is <em>not
 * persistent</em>, in that it depends on the order in which the object was committed.
 */
define( "kTAG_ID_SEQUENCE",				'@6' );

/**
 * Sequence hash (<code>:id-hash</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag is a <em>hexadecimal sequence number</em>, prefixed by the
 * {@link kTOKEN_TAG_PREFIX}, which is <em>automatically assigned</em> to objects just
 * before they are <em>committed</em>. This represents an <em>identifier unique to the
 * collection</em> to which the object belongs. This identifier is <em>not persistent</em>,
 * in that it depends on the order in which the object was committed.
 */
define( "kTAG_ID_HASH",					'@7' );

/**
 * Graph reference (<code>:id-graph</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag is an <em>integer value</em> used to reference a graph element. Nodes, tags,
 * entities and units are represented in a graph by nodes, while edges reference graph
 * edges. This offset is used to link objects of the document store with objects in the
 * graph store.
 */
define( "kTAG_ID_GRAPH",				'@8' );

/*=======================================================================================
 *	OBJECT CLASSIFICATION TAGS															*
 *======================================================================================*/

/**
 * Domain (<code>:unit:domain</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ENUM}
 * </ul>
 *
 * This tag represents the <em>domain</em> of a unit object, it is an <em>enumerated
 * value</em> which represents the <em>kind</em> or <em>nature</em> of the object, this type
 * of property is used to <em>disambiguate objects of different domains within the same
 * collection</em>.
 */
define( "kTAG_DOMAIN",					'@9' );

/**
 * Authority (<code>:unit:authority</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag is a <em>string</em> representing the <em>identifier</em> or <em>reference</em>
 * of the <em>entity object</em> which is responsible for the <em>identification</em> of the
 * unit, or which is the <em>author of the information</em> regarding the unit.
 */
define( "kTAG_AUTHORITY",				'@a' );

/**
 * Collection (<code>:unit:collection</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag is a <em>string</em> representing the <em>name</em> or <em>code</em> of the
 * <em>collection</em> to which a unit object belongs. It is used to disambiguate units
 * sharing the same domain and identifier; it may also be used to indicate the group to
 * which a unit belongs.
 */
define( "kTAG_COLLECTION",				'@b' );

/**
 * identifier (<code>:unit:identifier</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag is a <em>string</em> representing the <em>identifier</em> of a unit object, this
 * value must be unique among unit objects of the same domain and collection.
 */
define( "kTAG_IDENTIFIER",				'@c' );

/**
 * Version (<code>:unit:version</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag is a <em>string</em> representing a <em>version</em> or an <em>iteration</em> of
 * a unit object. This attribute can be used to differentiate between different iterations
 * of the same object, or to provide a time stamp for the object's information.
 */
define( "kTAG_VERSION",					'@d' );

/**
 * Synonym (<code>:synonym</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a list of <em>strings</em> representing <em>alternate identifications</em>
 * of the object. These identifiers should not be defined in the current database, nor
 * available as a link, these should be external known synonyms of te current object.
 */
define( "kTAG_SYNONYM",					'@e' );

/*=======================================================================================
 *	OBJECT REFERENCE TAGS																*
 *======================================================================================*/

/**
 * Tag (<code>:tag</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TAG}
 * </ul>
 *
 * This tag holds a <em>string</em> representing a <em>tag object reference</em>, it is the
 * <em>tag native identifier</em> of the <em>tag object</em> it references.
 */
define( "kTAG_TAG",						'@f' );

/**
 * Tags (<code>:tags</code>)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TAG}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of strings</em> representing <em>tag object references</em>,
 * these elements are the <em>native identifiers</em> of the <em>tag objects</em> they
 * reference.
 */
define( "kTAG_TAGS",					'@10' );

/**
 * Term (<code>:term</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TERM}
 * </ul>
 *
 * This tag holds a <em>string</em> representing a <em>term object reference</em>, it is the
 * <em>native identifier</em> of the <em>term object</em> it references.
 */
define( "kTAG_TERM",					'@11' );

/**
 * Terms (<code>:terms</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TERM}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of strings</em> representing <em>term object references</em>,
 * these elements are the <em>native identifiers</em> of the <em>term objects</em> they
 * reference.
 */
define( "kTAG_TERMS",					'@12' );

/**
 * Node (<code>:node</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_NODE}
 * </ul>
 *
 * This tag holds an <em>integer</em> representing a <em>node object reference</em>, it is
 * the <em>native identifier</em> of the <em>node object</em> it references.
 */
define( "kTAG_NODE",					'@13' );

/**
 * Nodes (<code>:nodes</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_NODE}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of integers</em> representing <em>node object references</em>,
 * these elements are the <em>native identifiers</em> of the <em>node objects</em> they
 * reference.
 */
define( "kTAG_NODES",					'@14' );

/**
 * Edge (<code>:edge</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_EDGE}
 * </ul>
 *
 * This tag holds a <em>string</em> representing an <em>edge object reference</em>, it is
 * the <em>native identifier</em> of the <em>edge object</em> it references.
 */
define( "kTAG_EDGE",					'@15' );

/**
 * Edges (<code>:edges</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_EDGE}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of strings</em> representing <em>edge object references</em>,
 * these elements are the <em>native identifiers</em> of the <em>edge objects</em> they
 * reference.
 */
define( "kTAG_EDGES",					'@16' );

/**
 * Unit reference (<code>:unit:reference</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_UNIT}
 * </ul>
 *
 * This tag holds a <em>string</em> representing a <em>unit native identifier</em>, it is
 * a <em>reference to a unit object</em>.
 */
define( "kTAG_UNIT",					'@17' );

/**
 * Units (<code>:unit:references</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_UNIT}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of strings</em> representing <em>unit object references</em>,
 * these elements are the <em>native identifiers</em> of the <em>unit objects</em> they
 * reference.
 */
define( "kTAG_UNITS",					'@18' );

/**
 * User reference (<code>:entity:user</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_USER}
 * </ul>
 *
 * This tag holds a <em>string</em> representing a <em>user native identifier</em>, it is
 * a <em>reference to a user object</em>.
 */
define( "kTAG_USER",					'@19' );

/**
 * Users (<code>:entity:users</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_USER}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of strings</em> representing <em>user object references</em>,
 * these elements are the <em>native identifiers</em> of the <em>user objects</em> they
 * reference.
 */
define( "kTAG_USERS",					'@1a' );

/**
 * Session reference (<code>:session:reference</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_SESSION}
 * </ul>
 *
 * This tag holds a <em>database specific type</em> representing a <em>session native
 * identifier</em>, it is a <em>reference to a session object</em>.
 */
define( "kTAG_SESSION",					'@1b' );

/**
 * Sessions (<code>:session:references</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_SESSION}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of database specific types</em> representing <em>session object
 * references</em>, these elements are the <em>native identifiers</em> of the <em>session
 * objects</em> they reference.
 */
define( "kTAG_SESSIONS",				'@1c' );

/**
 * Transaction reference (<code>:transaction:reference</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TRANSACTION}
 * </ul>
 *
 * This tag holds an <em>database specific type</em> representing a <em>transaction native
 * identifier</em>, it is a <em>reference to a transaction object</em>.
 */
define( "kTAG_TRANSACTION",				'@1d' );

/**
 * Transactions (<code>:transaction:references</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TRANSACTION}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of database specific types</em> representing <em>transaction
 * object references</em>, these elements are the <em>native identifiers</em> of the
 * <em>transaction objects</em> they reference.
 */
define( "kTAG_TRANSACTIONS",			'@1e' );

/**
 * File reference (<code>:file:reference</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_FILE}
 * </ul>
 *
 * This tag holds an <em>database specific type</em> representing a <em>file object native
 * identifier</em>, it is a <em>reference to a file object</em>.
 */
define( "kTAG_FILE",					'@1f' );

/**
 * Files (<code>:file:references</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_FILE}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of database specific types</em> representing <em>file object
 * references</em>, these elements are the <em>native identifiers</em> of the <em>file
 * objects</em> they reference.
 */
define( "kTAG_FILES",					'@20' );

/*=======================================================================================
 *	OBJECT RELATIONSHIP TAGS															*
 *======================================================================================*/

/**
 * Relationship subject (<code>:relationship:subject</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_NODE}
 * </ul>
 *
 * This tag holds an <em>integer</em> representing a <em>node native identifier</em>, it is
 * a <em>reference to a node object</em> through its <em>sequence number</em>. This tag
 * describes the <em>origin vertex of a directed graph relationship</em>.
 */
define( "kTAG_SUBJECT",					'@21' );

/**
 * Graph relationship subject (<code>:relationship:graph-subject</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag holds an <em>integer</em> representing the <em>reference to a graph node</em>.
 * This tag describes the <em>origin vertex of a directed graph relationship</em> in the
 * graph, this property is used by edge objects to reference the subject node in the graph.
 */
define( "kTAG_GRAPH_SUBJECT",			'@22' );

/**
 * Relationship predicate (<code>:predicate</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TERM}
 * </ul>
 *
 * This tag holds a <em>term object reference</em>, it is a <em>string</em> that represents
 * the term <em>native identifier</em>. This tag describes the <em>predicate of a directed
 * graph relationship</em>.
 */
define( "kTAG_PREDICATE",				'@23' );

/**
 * Relationship object (<code>:relationship:object</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_NODE}
 * </ul>
 *
 * This tag holds an <em>integer</em> representing a <em>node native identifier</em>, it is
 * a <em>reference to a node object</em> through its <em>sequence number</em>. This tag
 * describes the <em>destination vertex of a directed graph relationship</em>.
 */
define( "kTAG_OBJECT",					'@24' );

/**
 * Graph relationship object (<code>:relationship:graph-object</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag holds an <em>integer</em> representing the <em>reference to a graph node</em>.
 * This tag describes the <em>destination vertex of a directed graph relationship</em> in
 * the graph, this property is used by edge objects to reference the subject node in the
 * graph.
 */
define( "kTAG_GRAPH_OBJECT",			'@25' );

/**
 * Master (<code>:master</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_SELF}
 * </ul>
 *
 * This tag holds value representing a <em>reference</em> to an <em>object of the same class
 * as the holder</em>. Master objects represent either the master copy of the object, or
 * an object that contains information shared by several alias objects which are those
 * featuring this property.
 */
define( "kTAG_MASTER",					'@26' );

/*=======================================================================================
 *	OBJECT CATEGORY TAGS																*
 *======================================================================================*/

/**
 * Category (<code>:category</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SET}
 * </ul>
 *
 * This tag holds an <em>enumerated set</em> of <em>term object references</em> which
 * represent the <em>different categories to which an object belongs</em>.
 */
define( "kTAG_CATEGORY",				'@27' );

/**
 * Data type (<code>:type:data</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ENUM}
 * </ul>
 *
 * This tag holds a <em>term object references</em> which indicate the <em>data type</em>
 * of a data property. This type corresponds to the <em>primitive data representation and
 * structure of a data property</em>.
 */
define( "kTAG_DATA_TYPE",				'@28' );

/**
 * Data kind (<code>:type:kind</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SET}
 * </ul>
 *
 * This tag holds an <em>enumerated set</em> of <em>term object references</em> which
 * indicate the <em>cardinality</em> and <em>requirements</em> of a data property. This type
 * corresponds to the <em>attributes of a data property</em>, <em>not to its type</em>.
 */
define( "kTAG_DATA_KIND",				'@29' );

/**
 * term type (<code>:type:term</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SET}
 * </ul>
 *
 * This tag holds an <em>enumerated set</em> of <em>term object references</em> which
 * indicate the <em>type</em> of a term object. This value <em>qualifies the term type</em>,
 * it indicates the <em>context in which the term is used</em>.
 */
define( "kTAG_TERM_TYPE",				'@2a' );

/**
 * Node type (<code>:type:node</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SET}
 * </ul>
 *
 * This tag holds an <em>enumerated set</em> of <em>term object references</em> which
 * indicate the <em>type</em> of a node object. This value <em>qualifies the node type</em>,
 * it indicates the <em>context in which the node is used</em>.
 */
define( "kTAG_NODE_TYPE",				'@2b' );

/**
 * Session type (<code>:type:session</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ENUM}
 * </ul>
 *
 * This tag indicates the session type.
 */
define( "kTAG_SESSION_TYPE",			'@2c' );

/**
 * Transaction type (<code>:type:transaction</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ENUM}
 * </ul>
 *
 * This tag indicates the transaction type.
 */
define( "kTAG_TRANSACTION_TYPE",		'@2d' );

/*=======================================================================================
 *	OBJECT DESCRIPTION TAGS																*
 *======================================================================================*/

/**
 * Name (<code>:name</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> representing the <en>name of an object</em>. This is
 * generally the way humans refer to the object and it is <em>not related to a specific
 * language</em>.
 */
define( "kTAG_NAME",					'@2e' );

/**
 * Label (<code>:label</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_LANGUAGE_STRING}
 * </ul>
 *
 * This tag holds a <em>list of strings<em> representing <en>labels of an object in several
 * languages</em>. Each element holds the <em>language</em> in which the label is expressed
 * in and the <em>text</em> of the label.
 */
define( "kTAG_LABEL",					'@2f' );

/**
 * Definition (<code>:definition</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_LANGUAGE_STRING}
 * </ul>
 *
 * This tag holds a <em>list of texts<em> representing <en>definitions of an object in
 * several languages</em>. Each element holds the <em>language</em> in which the definition
 * is expressed in and the <em>text</em> of the definition. <em>A definition should provide
 * detailed information on an object without reference to the context</em>.
 */
define( "kTAG_DEFINITION",				'@30' );

/**
 * Description (<code>:description</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_LANGUAGE_STRING}
 * </ul>
 *
 * This tag holds a <em>list of texts<em> representing <en>descriptions of an object in
 * several languages</em>. Each element holds the <em>language</em> in which the description
 * is expressed in and the <em>text</em> of the description. <em>A description should add
 * context related information to the definition of the object</em>.
 */
define( "kTAG_DESCRIPTION",				'@31' );

/**
 * Notes (<code>:notes</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a series of <em>notes<em> or <em>comments</em> in a list of texts
 * unrelated to a specific language.
 */
define( "kTAG_NOTE",					'@32' );

/**
 * Examples (<code>:examples</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a series of <em>examples<em> or <em>instances</em> in a list of texts
 * unrelated to a specific language.
 */
define( "kTAG_EXAMPLE",					'@33' );

/**
 * Structure label (<code>:struct-label</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag is used in structure lists as the label for each element, in the case that the
 * structure elements do not have a unique scalar property. This tag is not searchable, nor
 * displayed in the detail.
 */
define( "kTAG_STRUCT_LABEL",			'@34' );

/*=======================================================================================
 *	OBJECT REFERENCE COUNT TAGS															*
 *======================================================================================*/

/**
 * Tag count (<code>:ref-count:tag</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of tag objects
 * that reference the current object</em>.
 */
define( "kTAG_TAG_COUNT",				'@35' );

/**
 * Term count (<code>:ref-count:term</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of term objects
 * that reference the current object</em>.
 */
define( "kTAG_TERM_COUNT",				'@36' );

/**
 * Node count (<code>:ref-count:node</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of node objects
 * that reference the current object</em>.
 */
define( "kTAG_NODE_COUNT",				'@37' );

/**
 * Edge count (<code>:ref-count:edge</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of edge objects
 * that reference the current object</em>.
 */
define( "kTAG_EDGE_COUNT",				'@38' );

/**
 * Units count (<code>:ref-count:unit</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of unit objects
 * featuring a specific property</em>. This is generally used to assess <em>tag usage
 * frequency in unit objects</em>.
 */
define( "kTAG_UNIT_COUNT",				'@39' );

/**
 * Users count (<code>:ref-count:user</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of entity objects
 * featuring a specific property</em>. This is generally used to assess <em>tag usage
 * frequency in entity objects</em>.
 */
define( "kTAG_USER_COUNT",				'@3a' );

/**
 * Sessions count (<code>:ref-count:session</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of session objects
 * featuring a specific property</em>. This is generally used to assess <em>tag usage
 * frequency in session objects</em>.
 */
define( "kTAG_SESSION_COUNT",			'@3b' );

/**
 * Transactions count (<code>:ref-count:transaction</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of transaction
 * objects featuring a specific property</em>. This is generally used to assess <em>tag
 * usage frequency in transaction objects</em>.
 */
define( "kTAG_TRANSACTION_COUNT",		'@3c' );

/**
 * Files count (<code>:ref-count:file</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of file objects
 * featuring a specific property</em>. This is generally used to assess <em>tag usage
 * frequency in transaction objects</em>.
 */
define( "kTAG_FILE_COUNT",				'@3d' );

/*=======================================================================================
 *	OBJECT OFFSET REFERENCE TAGS														*
 *======================================================================================*/

/**
 * Tag offsets (<code>:offset:tag</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in tag objects</em>.
 * This property is held exclusively by tag objects.
 */
define( "kTAG_TAG_OFFSETS",				'@3e' );

/**
 * Term offsets (<code>:offset:term</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in term objects</em>.
 * This property is held exclusively by tag objects.
 */
define( "kTAG_TERM_OFFSETS",			'@3f' );

/**
 * Node offsets (<code>:offset:node</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in node objects</em>.
 * This property is held exclusively by tag objects.
 */
define( "kTAG_NODE_OFFSETS",			'@40' );

/**
 * Edge offsets (<code>:offset:edge</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in edge objects</em>.
 * This property is held exclusively by tag objects.
 */
define( "kTAG_EDGE_OFFSETS",			'@41' );

/**
 * Unit offsets (<code>:offset:unit</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in unit objects</em>.
 * This property is held exclusively by tag objects.
 */
define( "kTAG_UNIT_OFFSETS",			'@42' );

/**
 * User offsets (<code>:offset:user</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in user objects</em>.
 * This property is held exclusively by tag objects.
 */
define( "kTAG_USER_OFFSETS",			'@43' );

/**
 * Session offsets (<code>:offset:session</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in session objects</em>.
 * This property is held exclusively by tag objects.
 */
define( "kTAG_SESSION_OFFSETS",			'@44' );

/**
 * Transaction offsets (<code>:offset:transaction</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in transaction
 * objects</em>. 
 * This property is held exclusively by tag objects.
 */
define( "kTAG_TRANSACTION_OFFSETS",		'@45' );

/**
 * File offsets (<code>:offset:file</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in file* objects</em>. 
 * This property is held exclusively by tag objects.
 */
define( "kTAG_FILE_OFFSETS",			'@46' );

/*=======================================================================================
 *	OBJECT STATISTICAL TAGS																*
 *======================================================================================*/

/**
 * Object tags (<code>:object-tags</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>elements</em> holding a <em>tag sequence
 * number</em> and all the <em>leaf offset paths</em> where the tag is referenced.
 */
define( "kTAG_OBJECT_TAGS",				'@47' );

/**
 * Object offsets (<code>:object-offsets</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds the list of <em>offset paths for all tags representing leaf offsets</em>.
 */
define( "kTAG_OBJECT_OFFSETS",			'@48' );

/**
 * Object references (<code>:object-references</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ARRAY}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds the <em>list of object references/em> featured by the current object, the
 * property is an array, <em>indexed by collection name</em> with as value the references
 * to objects in that collection.
 */
define( "kTAG_OBJECT_REFERENCES",		'@49' );

/*=======================================================================================
 *	PROPERTY DESCRIPTION TAGS															*
 *======================================================================================*/

/**
 * Tag container structure (<code>:tag:struct</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TAG}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag is used to provide the <em>the current tag's container</em>, the value should be
 * a reference to a {@link kTYPE_STRUCT} tag in which the current tag must be stored. If
 * set, the current offset should be stored in the offset defined in this property.
 */
define( "kTAG_TAG_STRUCT",				'@4a' );

/**
 * Container structure list index (<code>:tag:struct-index</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TAG}
 *	<li><em>Kind</em>: {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag indicates <em>which offset in the current structure acts as the index</em>.
 * This means that the tag object holding this property must be a structure and a list,
 * the value of this property is a tag native identifier referencing the element of the
 * structure that represents the structure index or key. No two elements of the list may
 * have an offset, defined by the current attribute, with the same value.
 */
define( "kTAG_TAG_STRUCT_IDX",			'@4b' );

/**
 * Minimum value (<code>:min-val</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_FLOAT}
 * </ul>
 *
 * This tag holds a <em>floating point number</em> representing the <em>minimum value</em>
 * occurrence of the property.
 */
define( "kTAG_MIN_VAL",					'@4c' );

/**
 * Minimum range (<code>:min-range</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_FLOAT}
 * </ul>
 *
 * This tag holds a <em>floating point number</em> representing the <em>minimum value</em>
 * that a property may hold.
 */
define( "kTAG_MIN_RANGE",				'@4d' );

/**
 * Maximum (<code>:max-val</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_FLOAT}
 * </ul>
 *
 * This tag holds a <em>floating point number</em> representing the <em>maximum value</em>
 * occurrence of the property.
 */
define( "kTAG_MAX_VAL",					'@4e' );

/**
 * Maximum range (<code>:max-range</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_FLOAT}
 * </ul>
 *
 * This tag holds a <em>floating point number</em> representing the <em>maximum value</em>
 * that a property may hold.
 */
define( "kTAG_MAX_RANGE",				'@4f' );

/**
 * Pattern (<code>:grep</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> representing a <em>regular expression pattern</em>, it
 * used to provide a validation pattern for coded strings.
 */
define( "kTAG_PATTERN",					'@50' );

/**
 * Decimal places (<code>:decimals</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag holds an <em>integer</em> representing the number of decimal places to be
 * displayed, this tag is used in floating point tags to round the value before displaying.
 */
define( "kTAG_DECIMALS",				'@51' );

/*=======================================================================================
 *	GEOMETRIC GEOMETRIC ATTRIBUTES														*
 *======================================================================================*/

/**
 * Geographic location shape (<code>:shape</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SHAPE}
 * </ul>
 *
 * This tag holds the <em>geographic shape</em> of an object. This value is expressed as
 * a GeoJSON geometric shape which describes the position and shape of an object. This value
 * should represent the <em>actual shape of the object</em>, as opposed to the
 * {@link kTAG_GEO_SHAPE_DISP} tag which represents the shape to be displayed.
 */
define( "kTAG_GEO_SHAPE",				'@52' );

/**
 * Geographic location display shape (<code>:shape-disp</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SHAPE}
 * </ul>
 *
 * This tag holds the <em>displayed geographic shape</em> of an object. This value is
 * expressed as a GeoJSON geometric shape which describes the position and shape of an
 * object. This value should represent the <em>displayed shape of the object</em>, as
 * opposed to the {@link kTAG_GEO_SHAPE} tag which represents the actual object's shape.
 */
define( "kTAG_GEO_SHAPE_DISP",			'@53' );

/*=======================================================================================
 *	GENERIC TIME ATTRIBUTES																*
 *======================================================================================*/

/**
 * Creation time stamp (<code>:record:created</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TIME_STAMP}
 * </ul>
 *
 * This tag indicates the record creation time stamp.
 */
define( "kTAG_RECORD_CREATED",			'@54' );

/**
 * Modification time stamp (<code>:record:modified</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TIME_STAMP}
 * </ul>
 *
 * This tag indicates the record modification time stamp.
 */
define( "kTAG_RECORD_MODIFIED",			'@55' );

/*=======================================================================================
 *	GENERIC COUNTER ATTRIBUTES															*
 *======================================================================================*/

/**
 * Processed elements (<code>:counter:processed</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the number of elements processed.
 */
define( "kTAG_COUNTER_PROCESSED",		'@56' );

/**
 * Validated elements (<code>:counter:validated</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the number of elements validated.
 */
define( "kTAG_COUNTER_VALIDATED",		'@57' );

/**
 * Rejected elements (<code>:counter:rejected</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the number of elements rejected.
 */
define( "kTAG_COUNTER_REJECTED",		'@58' );

/**
 * Skipped elements (<code>:counter:skipped</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the number of elements skipped.
 */
define( "kTAG_COUNTER_SKIPPED",			'@59' );

/**
 * Collections count (<code>:counter:collections</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the total number of collections.
 */
define( "kTAG_COUNTER_COLLECTIONS",		'@5a' );

/**
 * Record count (<code>:counter:records</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the total number of records.
 */
define( "kTAG_COUNTER_RECORDS",			'@5b' );

/**
 * Field count (<code>:counter:fields</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the total number of fields.
 */
define( "kTAG_COUNTER_FIELDS",			'@5c' );

/**
 * Progress (<code>:counter:progress</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_FLOAT}
 * </ul>
 *
 * This tag indicates the progress as a percentage.
 */
define( "kTAG_COUNTER_PROGRESS",		'@5d' );

/*=======================================================================================
 *	STRING ELEMENT ATTRIBUTES															*
 *======================================================================================*/

/**
 * Prefix (<code>:prefix</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a list of strings representing prefixes, it is generally used by templates
 * providing only the enumerated value suffix in order to identify the related term.
 */
define( "kTAG_PREFIX",					'@5e' );

/**
 * Suffix (<code>:suffix</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a list of strings representing suffixes, it is generally used by templates
 * providing only the significant portion of the value: the suffix will be appended to the
 * original value.
 */
define( "kTAG_SUFFIX",					'@5f' );

/**
 * Token (<code>:token</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag indicates a token.
 */
define( "kTAG_TOKEN",					'@60' );

/*=======================================================================================
 *	STATIC ATTRIBUTES																	*
 *======================================================================================*/

/**
 * Class (<code>:class</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag indicates a class name.
 */
define( "kTAG_CLASS_NAME",				'@61' );

/*=======================================================================================
 *	TEMPLATE ATTRIBUTES																	*
 *======================================================================================*/

/**
 * Name line (<code>:line-name</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the line number in which templates feature the column name or label.
 */
define( "kTAG_LINE_NAME",				'@62' );

/**
 * Info line (<code>:line-info</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the line number in which templates feature the column description or
 * information.
 */
define( "kTAG_LINE_INFO",				'@63' );

/**
 * Examples line (<code>:line-examples</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the line number in which templates feature the column examples.
 */
define( "kTAG_LINE_EXAMPLES",			'@64' );

/**
 * Symbol line (<code>:line-symbol</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the line number in which templates feature the column symbol.
 */
define( "kTAG_LINE_SYMBOL",				'@65' );

/**
 * Data line (<code>:line-data</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the line number in which templates feature the first record.
 */
define( "kTAG_LINE_DATA",				'@66' );

/**
 * Value transform (<code>:tag-transform</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRUCT}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag indicates the list of structures providing value copy instructions, it is used
 * by templates to copy and transform a value to other tags. The structure elements will
 * typically hold the following items:
 *
 * <ul>
 *	<li><tt>{@link kTAG_TAG}</tt>: The tag reference that will receive the value, if this is
 *		the only element, the value will be simply copied to that tag.
 *	<li><tt>{@link kTAG_PREFIX}</tt>: Before the value will be copied to the above tag, this
 *		string will be prefixed to the original value.
 *	<li><tt>{@link kTAG_SUFFIX}</tt>: Before the value will be copied to the above tag, this
 *		string will be appended to the original value.
 *	<li><tt>{@link kTAG_CONN_COLL}</tt>: If this tag is provided, it means that the value is
 *		an object reference and this tag holds the collection in which the related object
 *		resides.
 * </ul>
 *
 * The prefix and suffix are lists, this means that the correct prefix or suffix will have
 * to be identified. For instance, when providing a country code, this may either be an ISO
 * 3166-1 or a legacy 3166-3 country: the first successful match will determine which prefix
 * will be used; if you provide both a prefix and a suffix, all combinations will be used.
 */
define( "kTAG_TRANSFORM",				'@67' );

/*=======================================================================================
 *	SESSION OFFSETS																		*
 *======================================================================================*/

/**
 * Session start (<code>:session:start</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TIME_STAMP}
 * </ul>
 *
 * This tag indicates the session start timestamp.
 */
define( "kTAG_SESSION_START",			'@68' );

/**
 * Session end (<code>:session:end</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TIME_STAMP}
 * </ul>
 *
 * This tag indicates the session end timestamp.
 */
define( "kTAG_SESSION_END",				'@69' );

/**
 * Session status (<code>:session:status</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ENUM}
 * </ul>
 *
 * This tag indicates the session final status.
 */
define( "kTAG_SESSION_STATUS",			'@6a' );

/*=======================================================================================
 *	TRANSACTION OFFSETS																	*
 *======================================================================================*/

/**
 * Transaction start (<code>:transaction:start</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TIME_STAMP}
 * </ul>
 *
 * This tag indicates the transaction start timestamp.
 */
define( "kTAG_TRANSACTION_START",		'@6b' );

/**
 * Transaction end (<code>:transaction:end</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TIME_STAMP}
 * </ul>
 *
 * This tag indicates the transaction end timestamp.
 */
define( "kTAG_TRANSACTION_END",			'@6c' );

/**
 * Transaction status (<code>:transaction:status</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ENUM}
 * </ul>
 *
 * This tag indicates the transaction final status.
 */
define( "kTAG_TRANSACTION_STATUS",		'@6d' );

/**
 * Transaction collection (<code>:transaction:collection</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag indicates the transaction collection or worksheet.
 */
define( "kTAG_TRANSACTION_COLLECTION",	'@6e' );

/**
 * Transaction record (<code>:transaction:record</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the transaction record or row number.
 */
define( "kTAG_TRANSACTION_RECORD",		'@6f' );

/**
 * Transaction field (<code>:transaction:field</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_CATEGORICAL}
 * </ul>
 *
 * This tag indicates the transaction field or column name.
 */
define( "kTAG_TRANSACTION_FIELD",		'@70' );

/**
 * Transaction alias (<code>:transaction:alias</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag indicates the transaction alias.
 */
define( "kTAG_TRANSACTION_ALIAS",		'@71' );

/**
 * Transaction value (<code>:transaction:value</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_MIXED}
 * </ul>
 *
 * This tag indicates the transaction value.
 */
define( "kTAG_TRANSACTION_VALUE",		'@72' );

/**
 * Transaction message (<code>:transaction:message</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TEXT}
 * </ul>
 *
 * This tag indicates the transaction message.
 */
define( "kTAG_TRANSACTION_MESSAGE",		'@73' );

/**
 * Transaction log (<code>:transaction:log</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRUCT}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag indicates the transaction log or sub-elements list.
 */
define( "kTAG_TRANSACTION_LOG",			'@74' );

/*=======================================================================================
 *	ENTITY ATTRIBUTES																	*
 *======================================================================================*/

/**
 * Entity identifier (<code>:entity:identifier</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> representing the <em>identifier</em> of an entity, this
 * code should hold the most specific piece of information that can be used to identify an
 * entity.
 */
define( "kTAG_ENTITY_IDENT",			'@75' );

/**
 * Entity first name (<code>:entity:fname</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> representing the <em>first name</em> of an entity, this
 * implies that the entity is an individual.
 */
define( "kTAG_ENTITY_FNAME",			'@76' );

/**
 * Entity last name (<code>:entity:lname</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> representing the <em>surname</em> of an entity, this
 * implies that the entity is an individual.
 */
define( "kTAG_ENTITY_LNAME",			'@77' );

/**
 * Entity title (<code>:entity:title</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> representing the <em>title</em> of an entity.
 */
define( "kTAG_ENTITY_TITLE",			'@78' );

/**
 * Entity type (<code>:type:entity</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SET}
 * </ul>
 *
 * This tag holds an <em>enumerated set</em> which describes the <em>types of an
 * entity</em>.
 */
define( "kTAG_ENTITY_TYPE",				'@79' );

/**
 * Entity kind (<code>:kind:entity</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SET}
 * </ul>
 *
 * This tag holds an <em>enumerated set</em> which describes the <em>kinds of an
 * entity</em>.
 */
define( "kTAG_ENTITY_KIND",				'@7a' );

/**
 * Entity acronym (<code>:entity:acronym</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a a <em>list of strings</em> representing the entity <em>acronyms</em> or
 * <em>abbreviations</em>.
 */
define( "kTAG_ENTITY_ACRONYM",			'@7b' );

/**
 * Entity mail (<code>:entity:mail</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TYPED_LIST}
 * </ul>
 *
 * This tag holds a <em>list of mailing addresses discriminated by their type</em>. Each
 * element of the list represents an address which should be used according to its type.
 */
define( "kTAG_ENTITY_MAIL",				'@7c' );

/**
 * Entity e-mail (<code>:entity:email</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TYPED_LIST}
 * </ul>
 *
 * This tag holds a <em>list of e-mail addresses discriminated by their type</em>. Each
 * element of the list represents an e-mail which should be used according to its type.
 */
define( "kTAG_ENTITY_EMAIL",			'@7d' );

/**
 * Entity link (<code>:entity:url</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TYPED_LIST}
 * </ul>
 *
 * This tag holds a <em>list of internet addresses discriminated by their type</em>. Each
 * element of the list represents an internet link which can be categorised according to its
 * type.
 */
define( "kTAG_ENTITY_LINK",				'@7e' );

/**
 * Entity phone (<code>:entity:phone</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TYPED_LIST}
 * </ul>
 *
 * This tag holds a <em>list of telephone numbers discriminated by their type</em>. Each
 * element of the list represents a phone number which should be used according to its type.
 */
define( "kTAG_ENTITY_PHONE",			'@7f' );

/**
 * Entity fax (<code>:entity:fax</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TYPED_LIST}
 * </ul>
 *
 * This tag holds a <em>list of telefax numbers discriminated by their type</em>. Each
 * element of the list represents a fax number which should be used according to its type.
 */
define( "kTAG_ENTITY_FAX",				'@80' );

/**
 * Entity telex (<code>:entity:tlx</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TYPED_LIST}
 * </ul>
 *
 * This tag holds a <em>list of telex numbers discriminated by their type</em>. Each
 * element of the list represents a telex code which should be used according to its type.
 */
define( "kTAG_ENTITY_TLX",				'@81' );

/**
 * Entity affiliation (<code>:entity:affiliation</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TYPED_LIST}
 * </ul>
 *
 * This tag holds a <em>list of entity references discriminated by their type</em>. Each
 * element of the list represents an entity object reference which is qualified by the
 * element's type.
 *
 * <em>Note that the entity reference should point to a units collection entity: entities
 * stored in the entities collection exist solely for the purpose of providing a container
 * for system users.</em>
 */
define( "kTAG_ENTITY_AFFILIATION",		'@82' );

/**
 * Entity mationality (<code>:entity:nationality</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ENUM}
 * </ul>
 *
 * This tag holds an <em>enumerated value representing the nationality of the entity</em>.
 */
define( "kTAG_ENTITY_NATIONALITY",		'@83' );

/**
 * Valid entity (<code>:entity:valid</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_SELF}
 * </ul>
 *
 * This tag holds a reference to the <em>currently valid or preferred entity</em>. This
 * attribute is used by obsolete or defunct entities for referring to the current substitute
 * or valid entity.
 */
define( "kTAG_ENTITY_VALID",			'@84' );

/**
 * Entity PGP public key (<code>:entity:pgp-key</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds the <em>PGP public key</em> which identifies a specific <em>user</em>.
 */
define( "kTAG_ENTITY_PGP_KEY",			'@85' );

/**
 * Entity PGP fingerprint (<code>:entity:pgp-fingerprint</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TEXT}
 * </ul>
 *
 * This tag holds the <em>PGP fingerprint</em> which identifies a specific <em>user</em>.
 */
define( "kTAG_ENTITY_PGP_FINGERPRINT",	'@86' );

/**
 * Entity icon (<code>:entity:icon</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag indicates the entity icon name.
 */
define( "kTAG_ENTITY_ICON",				'@87' );

/*=======================================================================================
 *	USER ATTRIBUTES																		*
 *======================================================================================*/

/**
 * Roles (<code>:roles</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SET}
 * </ul>
 *
 * This tag indicates the list of roles a user or service has in regards to the system, the
 * value is a set of string values which are defined and managed by the user interface
 * system.
 */
define( "kTAG_ROLES",					'@88' );

/**
 * Invites (<code>:invites</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRUCT}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag indicates the list of invitations.
 */
define( "kTAG_INVITES",					'@89' );

/**
 * Managed users (<code>:managed-count</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the number of managed users.
 */
define( "kTAG_MANAGED_COUNT",			'@8a' );

/*=======================================================================================
 *	CONNECTION ATTRIBUTES																*
 *======================================================================================*/

/**
 * Connection protocol (<code>:connection:protocol</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific network connection
 * <em>protocol</em> or <em>scheme</em>.
 */
define( "kTAG_CONN_PROTOCOL",			'@8b' );

/**
 * Connection host (<code>:connection:host</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific network connection
 * <em>domain name</em> or <em>internet address</em>.
 */
define( "kTAG_CONN_HOST",				'@8c' );

/**
 * Connection port (<code>:connection:port</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag holds an <em>integer</em> which identifies a specific network <em>TCP or UDP
 * port number</em>.
 */
define( "kTAG_CONN_PORT",				'@8d' );

/**
 * Connection credentials code (<code>:connection:code</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific <em>code</em> used to
 * <em>authenticate with a service</em>.
 */
define( "kTAG_CONN_CODE",				'@8e' );

/**
 * Connection credentials password (<code>:connection:password</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific <em>password</em> which
 * allows to <em>authenticate with a service</em>.
 */
define( "kTAG_CONN_PASS",				'@8f' );

/**
 * Database name (<code>:connection:database</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific <em>database</em>.
 */
define( "kTAG_CONN_BASE",				'@90' );

/**
 * Database names (<code>:connection:databases</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of strings</em> which identify specific <em>databases</em>.
 */
define( "kTAG_CONN_BASES",				'@91' );

/**
 * Collection name (<code>:connection:collection</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific <em>database
 * collection</em>.
 */
define( "kTAG_CONN_COLL",				'@92' );

/**
 * Collection names (<code>:connection:collections</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a <em>list of strings</em> which identifiy specific <em>database
 * collections</em>.
 */
define( "kTAG_CONN_COLLS",				'@93' );

/**
 * Connection options (<code>:connection:options</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ARRAY}
 * </ul>
 *
 * This tag holds a <em>list of key/value pairs</em> which represent the <em>options for a
 * network connection</em>. The key part identifies the option, the value part provides the
 * option value.
 */
define( "kTAG_CONN_OPTS",				'@94' );

/*=======================================================================================
 *	ERROR OFFSETS																		*
 *======================================================================================*/

/**
 * Error type (<code>:error:type</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds the error type, it is a string that categorises the error.
 */
define( "kTAG_ERROR_TYPE",				'@95' );

/**
 * Error code (<code>:error:code</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds the error code, it is a string that identifies the error.
 */
define( "kTAG_ERROR_CODE",				'@96' );

/**
 * Error resource (<code>:error:resource</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_URL}
 * </ul>
 *
 * This tag holds the internet address of the resource which might help correcting the
 * error.
 */
define( "kTAG_ERROR_RESOURCE",			'@97' );

/*=======================================================================================
 *	OPERATION OFFSETS																	*
 *======================================================================================*/

/**
 * Append to array
 *
 * This tag indicates an append to array directive, it is used when setting nested offsets:
 * whenever this offset is encountered in a sequence of nested offsets, the sequence
 * following this offset will be appended to the offset preceding this tag.
 */
define( "kTAG_OPERATION_APPEND",		'@9f' );

/*=======================================================================================
 *	DEFAULT TAGS LIMIT																	*
 *======================================================================================*/

/**
 * Dynamic tag sequence origin
 *
 * This defines the first dynamically assigned sequence tag number [0xA0].
 */
define( "kTAG_SEQUENCE_START",			160 );


?>
