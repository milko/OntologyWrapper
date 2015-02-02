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
// MILKO - To porevent needing to include toikens.
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
define( "kTAG_SUBJECT",					'@13' );

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
define( "kTAG_GRAPH_SUBJECT",			'@14' );

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
define( "kTAG_PREDICATE",				'@15' );

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
define( "kTAG_OBJECT",					'@16' );

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
define( "kTAG_GRAPH_OBJECT",			'@17' );

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
define( "kTAG_USER",					'@18' );

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
define( "kTAG_MASTER",					'@19' );

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
define( "kTAG_CATEGORY",				'@1a' );

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
define( "kTAG_DATA_TYPE",				'@1b' );

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
define( "kTAG_DATA_KIND",				'@1c' );

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
define( "kTAG_TERM_TYPE",				'@1d' );

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
define( "kTAG_NODE_TYPE",				'@1e' );

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
define( "kTAG_NAME",					'@1f' );

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
define( "kTAG_LABEL",					'@20' );

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
define( "kTAG_DEFINITION",				'@21' );

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
define( "kTAG_DESCRIPTION",				'@22' );

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
define( "kTAG_NOTE",					'@23' );

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
define( "kTAG_EXAMPLE",					'@24' );

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
define( "kTAG_STRUCT_LABEL",			'@25' );

/*=======================================================================================
 *	OBJECT STATISTICAL TAGS																*
 *======================================================================================*/

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
define( "kTAG_UNIT_COUNT",				'@26' );

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
define( "kTAG_USER_COUNT",			'@27' );

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
define( "kTAG_TAG_COUNT",				'@28' );

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
define( "kTAG_TERM_COUNT",				'@29' );

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
define( "kTAG_NODE_COUNT",				'@2a' );

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
define( "kTAG_EDGE_COUNT",				'@2b' );

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
define( "kTAG_TAG_OFFSETS",				'@2c' );

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
define( "kTAG_TERM_OFFSETS",			'@2d' );

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
define( "kTAG_NODE_OFFSETS",			'@2e' );

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
define( "kTAG_EDGE_OFFSETS",			'@2f' );

/**
 * Entity offsets (<code>:offset:entity</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST},
 *					   {@link kTAG_PRIVATE_MODIFY}, {@link kTYPE_PRIVATE_DISPLAY}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the <em>set of offset
 * paths</em> in which the tag was referenced <em>as a leaf offset in entity objects</em>.
 * This property is held exclusively by tag objects.
 */
define( "kTAG_ENTITY_OFFSETS",			'@30' );

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
define( "kTAG_UNIT_OFFSETS",			'@31' );

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
define( "kTAG_OBJECT_TAGS",				'@32' );

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
define( "kTAG_OBJECT_OFFSETS",			'@33' );

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
define( "kTAG_OBJECT_REFERENCES",		'@34' );

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
define( "kTAG_TAG_STRUCT",				'@35' );

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
define( "kTAG_TAG_STRUCT_IDX",			'@36' );

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
define( "kTAG_MIN_VAL",					'@37' );

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
define( "kTAG_MIN_RANGE",				'@38' );

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
define( "kTAG_MAX_VAL",					'@39' );

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
define( "kTAG_MAX_RANGE",				'@3a' );

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
define( "kTAG_PATTERN",					'@3b' );

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
define( "kTAG_DECIMALS",				'@3c' );

/*=======================================================================================
 *	GENERIC ATTRIBUTES																	*
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
define( "kTAG_GEO_SHAPE",				'@3d' );

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
define( "kTAG_GEO_SHAPE_DISP",			'@3e' );

/**
 * Creation time stamp (<code>:record:created</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TIME_STAMP}
 * </ul>
 *
 * This tag indicates the record creation time stamp.
 */
define( "kTAG_RECORD_CREATED",			'@3f' );

/**
 * Modification time stamp (<code>:record:modified</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TIME_STAMP}
 * </ul>
 *
 * This tag indicates the record modification time stamp.
 */
define( "kTAG_RECORD_MODIFIED",			'@40' );

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
define( "kTAG_CONN_PROTOCOL",			'@41' );

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
define( "kTAG_CONN_HOST",				'@42' );

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
define( "kTAG_CONN_PORT",				'@43' );

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
define( "kTAG_CONN_CODE",				'@44' );

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
define( "kTAG_CONN_PASS",				'@45' );

/**
 * Database name (<code>:connection:database</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific <em>database</em>.
 */
define( "kTAG_CONN_BASE",				'@46' );

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
define( "kTAG_CONN_COLL",				'@47' );

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
define( "kTAG_CONN_OPTS",				'@48' );

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
define( "kTAG_ENTITY_IDENT",			'@49' );

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
define( "kTAG_ENTITY_FNAME",			'@4a' );

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
define( "kTAG_ENTITY_LNAME",			'@4b' );

/**
 * Entity title (<code>:entity:title</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> representing the <em>title</em> of an entity.
 */
define( "kTAG_ENTITY_TITLE",			'@4c' );

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
define( "kTAG_ENTITY_TYPE",				'@4d' );

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
define( "kTAG_ENTITY_KIND",				'@4e' );

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
define( "kTAG_ENTITY_ACRONYM",			'@4f' );

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
define( "kTAG_ENTITY_MAIL",				'@50' );

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
define( "kTAG_ENTITY_EMAIL",			'@51' );

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
define( "kTAG_ENTITY_LINK",				'@52' );

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
define( "kTAG_ENTITY_PHONE",			'@53' );

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
define( "kTAG_ENTITY_FAX",				'@54' );

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
define( "kTAG_ENTITY_TLX",				'@55' );

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
define( "kTAG_ENTITY_AFFILIATION",		'@56' );

/**
 * Entity mationality (<code>:entity:nationality</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ENUM}
 * </ul>
 *
 * This tag holds an <em>enumerated value representing the nationality of the entity</em>.
 */
define( "kTAG_ENTITY_NATIONALITY",		'@57' );

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
define( "kTAG_ENTITY_VALID",			'@58' );

/**
 * Entity PGP public key (<code>:entity:pgp-key</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds the <em>PGP public key</em> which identifies a specific <em>user</em>.
 */
define( "kTAG_ENTITY_PGP_KEY",			'@59' );

/**
 * Entity PGP fingerprint (<code>:entity:pgp-fingerprint</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_TEXT}
 * </ul>
 *
 * This tag holds the <em>PGP fingerprint</em> which identifies a specific <em>user</em>.
 */
define( "kTAG_ENTITY_PGP_FINGERPRINT",	'@5a' );

/**
 * Entity icon (<code>:entity:icon</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag indicates the entity icon name.
 */
define( "kTAG_ENTITY_ICON",				'@5b' );

/*=======================================================================================
 *	MANAGEMENT OFFSETS																	*
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
define( "kTAG_ROLES",					'@5c' );

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
define( "kTAG_INVITES",					'@5d' );

/**
 * Class (<code>:class</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag indicates a class name.
 */
define( "kTAG_CLASS_NAME",				'@5e' );

/**
 * Token (<code>:token</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag indicates a token.
 */
define( "kTAG_TOKEN",					'@5f' );

/**
 * Managed users (<code>:managed-count</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 * </ul>
 *
 * This tag indicates the number of managed users.
 */
define( "kTAG_MANAGED_COUNT",			'@60' );

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
define( "kTAG_OPERATION_APPEND",		'@6f' );

/*=======================================================================================
 *	DEFAULT TAGS LIMIT																	*
 *======================================================================================*/

/**
 * Dynamic tag sequence origin
 *
 * This defines the first dynamically assigned sequence tag number [0x70].
 */
define( "kTAG_SEQUENCE_START",			112 );


?>
