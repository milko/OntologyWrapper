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
 * Class (<code>_class</code>)
 *
 * This offset represents the <em>object class name</em>, this string is used to
 * instantiate the correct object once loaded from a container. This attribute is internal
 * and it is not defined in the ontology.
 */
define( "kTAG_CLASS",					'_class' );

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
define( "kTAG_NAMESPACE",				1 );

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
define( "kTAG_ID_LOCAL",				2 );

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
define( "kTAG_ID_PERSISTENT",			3 );

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
define( "kTAG_ID_VALID",				4 );

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
define( "kTAG_ID_SEQUENCE",				5 );

/*=======================================================================================
 *	OBJECT IDENTIFICATION TAGS															*
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
define( "kTAG_DOMAIN",					6 );

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
define( "kTAG_AUTHORITY",				7 );

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
define( "kTAG_COLLECTION",				8 );

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
define( "kTAG_IDENTIFIER",				9 );

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
define( "kTAG_VERSION",					10 );

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
define( "kTAG_TAG",						11 );

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
define( "kTAG_TAGS",					12 );

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
define( "kTAG_TERM",					13 );

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
define( "kTAG_TERMS",					14 );

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
define( "kTAG_SUBJECT",					15 );

/**
 * Relationship predicate (<code>:relationship:predicate</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_TERM}
 * </ul>
 *
 * This tag holds a <em>term object reference</em>, it is a <em>string</em> that represents
 * the term <em>native identifier</em>. This tag describes the <em>predicate of a directed
 * graph relationship</em>.
 */
define( "kTAG_PREDICATE",				16 );

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
define( "kTAG_OBJECT",					17 );

/**
 * Entity reference (<code>:entity</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_ENTITY}
 * </ul>
 *
 * This tag holds a <em>string</em> representing an <em>entity native identifier</em>, it is
 * a <em>reference to an entity object</em>.
 */
define( "kTAG_ENTITY",					18 );

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
define( "kTAG_MASTER",					19 );

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
define( "kTAG_CATEGORY",				20 );

/**
 * Data type (<code>:type:data</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_SET}
 * </ul>
 *
 * This tag holds an <em>enumerated set</em> of <em>term object references</em> which
 * indicate the <em>data type</em> of a data property. This type corresponds to the
 * <em>primitive data representation and structure of a data property</em>.
 */
define( "kTAG_DATA_TYPE",				21 );

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
define( "kTAG_DATA_KIND",				22 );

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
define( "kTAG_NODE_TYPE",				23 );

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
define( "kTAG_NAME",					24 );

/**
 * Label (<code>:label</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_LANGUAGE_STRINGS}
 * </ul>
 *
 * This tag holds a <em>list of strings<em> representing <en>labels of an object in several
 * languages</em>. Each element holds the <em>language</em> in which the label is expressed
 * in and the <em>text</em> of the label.
 */
define( "kTAG_LABEL",					25 );

/**
 * Definition (<code>:definition</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_LANGUAGE_STRINGS}
 * </ul>
 *
 * This tag holds a <em>list of texts<em> representing <en>definitions of an object in
 * several languages</em>. Each element holds the <em>language</em> in which the definition
 * is expressed in and the <em>text</em> of the definition. <em>A definition should provide
 * detailed information on an object without reference to the context</em>.
 */
define( "kTAG_DEFINITION",				26 );

/**
 * Description (<code>:description</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_LANGUAGE_STRINGS}
 * </ul>
 *
 * This tag holds a <em>list of texts<em> representing <en>descriptions of an object in
 * several languages</em>. Each element holds the <em>language</em> in which the description
 * is expressed in and the <em>text</em> of the description. <em>A description should add
 * context related information to the definition of the object</em>.
 */
define( "kTAG_DESCRIPTION",				27 );

/**
 * Notes (<code>:notes</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_LIST}
 * </ul>
 *
 * This tag holds a series of <em>notes<em> or <em>comments</em> in a single text unrelated
 * to a specific language.
 */
define( "kTAG_NOTE",					28 );

/*=======================================================================================
 *	OBJECT STATISTICAL TAGS																*
 *======================================================================================*/

/**
 * Units count (<code>:unit-count</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTYPE_PRIVATE_IN}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of unit objects
 * featuring a specific property</em>. This is generally used to assess <em>tag usage
 * frequency in unit objects</em>.
 */
define( "kTAG_UNIT_COUNT",				29 );

/**
 * Entity count (<code>:entity-count</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTYPE_PRIVATE_IN}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of entity objects
 * featuring a specific property</em>. This is generally used to assess <em>tag usage
 * frequency in entity objects</em>.
 */
define( "kTAG_ENTITY_COUNT",			30 );

/**
 * Tag count (<code>:tag-count</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTYPE_PRIVATE_IN}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of tag objects
 * that reference the current object</em>.
 */
define( "kTAG_TAG_COUNT",				31 );

/**
 * Term count (<code>:term-count</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTYPE_PRIVATE_IN}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of term objects
 * that reference the current object</em>.
 */
define( "kTAG_TERM_COUNT",				32 );

/**
 * Node count (<code>:node-count</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTYPE_PRIVATE_IN}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of node objects
 * that reference the current object</em>.
 */
define( "kTAG_NODE_COUNT",				33 );

/**
 * Edge count (<code>:node-count</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTYPE_PRIVATE_IN}
 * </ul>
 *
 * This tag holds an <em>integer</em> value representing the <em>number of edge objects
 * that reference the current object</em>.
 */
define( "kTAG_EDGE_COUNT",				34 );

/**
 * Offsets (<code>:offsets</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 *	<li><em>Kind</em>: {@link kTYPE_ARRAY}, {@link kTYPE_PRIVATE_IN}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>strings</em> representing the different
 * <em>offsets</em> used by the holding <em>tag object</em>.
 */
define( "kTAG_OFFSETS",					35 );

/**
 * Object tags (<code>:object-tags</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_INT}
 *	<li><em>Kind</em>: {@link kTYPE_ARRAY}, {@link kTYPE_PRIVATE_IN}
 * </ul>
 *
 * This tag holds an <em>array</em> of <em>integers</em> representing the <em>list of tag
 * sequence numbers used as offsets in the current object</em>.
 */
define( "kTAG_OBJECT_TAGS",				36 );

/*=======================================================================================
 *	GENERIC TAGS																		*
 *======================================================================================*/

/**
 * Type (<code>:type</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which represents a discriminating type or category.
 */
define( "kTAG_TYPE",					37 );

/**
 * Language (<code>:language</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which represents a specific <em>language name or
 * code</em>, this tag is generally used as an element of a structure for indicating the
 * element's language.
 */
define( "kTAG_LANGUAGE",				38 );

/**
 * Text (<code>:text</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which represents a <em>text</em>, this tag is generally
 * used as an element of a structure for indicating the element's text.
 */
define( "kTAG_TEXT",					39 );

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
define( "kTAG_CONN_PROTOCOL",			40 );

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
define( "kTAG_CONN_HOST",				41 );

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
define( "kTAG_CONN_PORT",				42 );

/**
 * Connection user code (<code>:connection:user</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific <em>user code</em> used to
 * <em>authenticate with a service</em>.
 */
define( "kTAG_CONN_USER",				43 );

/**
 * Connection user password (<code>:connection:password</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific <em>user password</em> which
 * allows to <em>authenticate with a service</em>.
 */
define( "kTAG_CONN_PASS",				44 );

/**
 * Database name (<code>:connection:database</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_STRING}
 * </ul>
 *
 * This tag holds a <em>string</em> which identifies a specific <em>database</em>.
 */
define( "kTAG_CONN_BASE",				45 );

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
define( "kTAG_CONN_COLL",				46 );

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
define( "kTAG_CONN_OPTS",				47 );

/*=======================================================================================
 *	ENTITY ATTRIBUTES																	*
 *======================================================================================*/

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
define( "kTAG_ENTITY_TYPE",				48 );

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
define( "kTAG_ENTITY_KIND",				49 );

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
define( "kTAG_ENTITY_MAIL",				50 );

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
define( "kTAG_ENTITY_EMAIL",			51 );

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
define( "kTAG_ENTITY_PHONE",			52 );

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
define( "kTAG_ENTITY_FAX",				53 );

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
 */
define( "kTAG_ENTITY_AFFILIATION",		54 );

/**
 * Entity country (<code>:entity:country</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_ENUM}
 * </ul>
 *
 * This tag holds an <em>enumerated value representing the country of the entity</em>.
 */
define( "kTAG_ENTITY_COUNTRY",			55 );

/**
 * Valid entity (<code>:entity:valid</code)
 *
 * <ul>
 *	<li><em>Type</em>: {@link kTYPE_REF_ENTITY}
 * </ul>
 *
 * This tag holds a reference to the <em>currently valid or preferred entity</em>. This
 * attribute is used by obsolete or defunct entities for referring to the current substitute
 * or valid entity.
 */
define( "kTAG_ENTITY_VALID",			56 );

/*=======================================================================================
 *	DEFAULT TAGS LIMIT																	*
 *======================================================================================*/

/**
 * Dynamic tag sequence origin
 *
 * This defines the first dynamically assigned sequence tag number.
 */
define( "kTAG_SEQUENCE_START",			101 );


?>
