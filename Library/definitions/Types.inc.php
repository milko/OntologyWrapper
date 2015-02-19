<?php

/*=======================================================================================
 *																						*
 *									Types.inc.php										*
 *																						*
 *======================================================================================*/
 
/**
 * Type definitions.
 *
 * This file contains the default type and kind definitions.
 *
 * Each entry is a term object native identifier.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 25/11/2012
 */

/*=======================================================================================
 *	PRIMITIVE DATA TYPES																*
 *======================================================================================*/

/**
 * Mixed.
 *
 * A <i>mixed</i> data type indicates that the referred property may take any data type.
 */
define( "kTYPE_MIXED",							':type:mixed' );

/**
 * String.
 *
 * A <i>string</i> data type indicates that the referred property may hold <i>UNICODE</i>
 * characters, this type <i>does not include binary data</i>
 */
define( "kTYPE_STRING",							':type:string' );

/**
 * Integer.
 *
 * An <i>integer</i> data type indicates that the referred property may hold a <i>32 or 64
 * bit integral numeric value</i>
 */
define( "kTYPE_INT",							':type:int' );

/**
 * Float.
 *
 * A <i>float</i> data type indicates that the referred property may hold a <i>floating
 * point number</i>, also known as <i>double</i> or <i>real</i>. The precision of such value
 * is not inferred, in general it will be a <i>32 or 64 bit real</i>
 */
define( "kTYPE_FLOAT",							':type:float' );

/**
 * Boolean.
 *
 * A <i>boolean</i> data type is a binary value which can take one of two states: on/true or
 * off/false.
 */
define( "kTYPE_BOOLEAN",						':type:boolean' );

/*=======================================================================================
 *	STRUCTURED DATA TYPES																*
 *======================================================================================*/

/**
 * Struct.
 *
 * This data type defines a <em>structure</em>, this means that the value will be an
 * <em>object</em> or an array of objects if the data kind is a list.
 */
define( "kTYPE_STRUCT",							':type:struct' );

/**
 * Array.
 *
 * This data type defines a <em>list of key/value pairs</em>, the key will be in general a
 * string, while the value type is not inferred. This data type usually applies to arrays in
 * which the key part is the discriminant and determines the type of the value, while
 * traditional arrays are better defined by a fixed data type and a list data kind.
 */
define( "kTYPE_ARRAY",							':type:array' );

/**
 * String list by language.
 *
 * This data type defines a <em>list of strings expressed in different languages</em>. The
 * list elements are composed by <em>two key/value pairs</em>. The first pair has the
 * {@link kTAG_LANGUAGE} tag as its key and the value represents the language code. The
 * second pair has the {@link kTAG_TEXT} as its key and the value represents the text
 * expressed in the language defined by the first pair. No two elements may share the same
 * language and only one element may omit the language.
 */
define( "kTYPE_LANGUAGE_STRING",				':type:language-string' );

/**
 * Strings list by language.
 *
 * This data type defines a <em>list of strings expressed in different languages</em>. The
 * list elements are composed by <em>two key/value pairs</em>. The first pair has the
 * {@link kTAG_LANGUAGE} tag as its key and the value represents the language code. The
 * second pair has the {@link kTAG_TEXT} as its key and the value represents the list of
 * strings expressed in the language defined by the first pair. No two elements may share
 * the same language and only one element may omit the language.
 */
define( "kTYPE_LANGUAGE_STRINGS",				':type:language-strings' );

/**
 * Typed list.
 *
 * This data type defines a <em>list of elements categorised by type</em>. The list elements
 * are composed by <em>two key/value pairs</em>. The first pair has the {@link kTAG_TYPE}
 * tag as its key and the value represents the type of the element. The second pair has
 * an unspecified tag as the key and the value represents the element's value qualified by
 * the previous pair. No two elements may share the same type and only one element may omit
 * the type.
 */
define( "kTYPE_TYPED_LIST",						':type:typed-list' );

/**
 * Shape.
 *
 * This data type defines a <em>shape structure</em>, this type of object represents a
 * geometric shape and it is expressed as a GeoJSON construct.
 *
 * It is an array composed by two key/value pairs:
 *
 * <ul>
 *	<li><tt>{@link kTAG_TYPE}</tt>: The element indexed by this string contains the
 *		code indicating the type of the shape, these are the supported values:
 *	 <ul>
 *		<li><tt>Point</tt>: A point.
 *		<li><tt>LineString</tt>: A list of non closed points.
 *		<li><tt>Polygon</tt>: A polygon, including its rings.
 *	 </ul>
 *	<li><tt>{@link kTAG_GEOMETRY}</tt>: The element indexed by this string contains
 *		the <em>geometry of the shape</em>, which has a structure depending on the shape
 +		type:
 *	 <ul>
 *		<li><tt>Point</tt>: The point is an array of two floating point numbers,
 *			respectively the <em>longitude</em> and <em>latitude</em>.
 *		<li><tt>LineString</tt>: A line string is an array of points expressed as the
 *			<tt>Point</tt> geometry.
 *		<li><tt>Polygon</tt>: A polygon is a list of rings whose geometry is like the
 *			<tt>LineString</em> geometry, except that the first and last point must match.
 *			The first ring represents the outer boundary of the polygon, the other rings are
 *			optional and represent holes in the polygon.
 *	 </ul>
 * </ul>
 */
define( "kTYPE_SHAPE",							':type:shape' );

/*=======================================================================================
 *	STANDARDS DATA TYPES																*
 *======================================================================================*/

/**
 * Text.
 *
 * This type is derived from the string type, it indicates a text which is not intended for
 * searching. In general, this type will be used to qualify properties which contain large
 * text which cannot be indexed.
 */
define( "kTYPE_TEXT",							':type:text' );

/**
 * Link.
 *
 * A <i>link</i> data type indicates that the referred property is a <em>string</em>
 * representing an <em>URL</em> which is an internet link or network address.
 */
define( "kTYPE_URL",							':type:url' );

/**
 * Year.
 *
 * This type defines a year as an integer.
 */
define( "kTYPE_YEAR",							':type:year' );

/**
 * Date.
 *
 * This type defines a date in which the day and month can be omitted, it is a string
 * providing the date in <tt>YYYYMMDD</tt> format in which the day, or the day and month
 * may not be provided. This type can be used for ranges and sorted.
 */
define( "kTYPE_DATE",							':type:date' );

/*=======================================================================================
 *	ENUMERATED DATA TYPES																*
 *======================================================================================*/

/**
 * Enumeration.
 *
 * An <i>enumerated</i> data type indicates that the referred property may only hold <i>a
 * term reference</i>, that is, the <i>global identifier of a term object</i>. Enumerated
 * values are by default strings and must reference a term object.
 */
define( "kTYPE_ENUM",							':type:enum' );

/**
 * Enumerated set.
 *
 * An <i>enumerated set</i> data type indicates that the referred property may only hold
 * <i>a list of term reference</i>, that is, an array of <i>term native identifiers</i>. All
 * the elements of this list must be unique.
 */
define( "kTYPE_SET",							':type:enum-set' );

/*=======================================================================================
 *	REFERENCE DATA TYPES																*
 *======================================================================================*/

/**
 * Tag reference.
 *
 * A <i>tag reference</i> is a <em>string</em> that must correspond to the native identifier
 * of a {@link Tag} object.
 */
define( "kTYPE_REF_TAG",						':type:ref:tag' );

/**
 * Term reference.
 *
 * A <i>term reference</i> is a <em>string</em> that must correspond to the identifier of a
 * {@link Term} object.
 */
define( "kTYPE_REF_TERM",						':type:ref:term' );

/**
 * Node reference.
 *
 * A <i>node reference</i> is an <em>integer</em> that must correspond to the native
 * identifier of a {@link Node} object.
 */
define( "kTYPE_REF_NODE",						':type:ref:node' );

/**
 * Edge reference.
 *
 * An <i>edge reference</i> is a <em>string</em> that must correspond to the native
 * identifier of an {@link Edge} object.
 */
define( "kTYPE_REF_EDGE",						':type:ref:edge' );

/**
 * Unit reference.
 *
 * A <i>unit reference</i> is a <em>string</em> that must correspond to the native
 * identifier of a {@link Unit} object.
 */
define( "kTYPE_REF_UNIT",						':type:ref:unit' );

/**
 * User reference.
 *
 * An <i>user reference</i> is a <em>string</em> that must correspond to the native
 * identifier of an {@link User} object.
 */
define( "kTYPE_REF_USER",						':type:ref:user' );

/**
 * Session reference.
 *
 * An <i>session reference</i> is a <em>string</em> that must correspond to the native
 * identifier of a {@link Session} object.
 */
define( "kTYPE_REF_SESSION",					':type:ref:session' );

/**
 * Transaction reference.
 *
 * An <i>transaction reference</i> is a <em>string</em> that must correspond to the native
 * identifier of a {@link Transaction} object.
 */
define( "kTYPE_REF_TRANSACTION",				':type:ref:transaction' );

/**
 * Self reference.
 *
 * This type defines an <em>reference</em> to an <em>object of the same class</em>.
 */
define( "kTYPE_REF_SELF",						':type:ref:self' );

/*=======================================================================================
 *	CUSTOM DATA TYPES																	*
 *======================================================================================*/

/**
 * Time-stamp.
 *
 * A <i>time-stamp</i> is a <em>database native type</em> expressing a date and time,
 * <em>its structure is dependent on the specific database driver</em>.
 */
define( "kTYPE_TIME_STAMP",						':type:time-stamp' );

/*=======================================================================================
 *	DEFAULT TERM TYPES																	*
 *======================================================================================*/

/**
 * Instance.
 *
 * A metadata instance.
 *
 * An instance is a term which represents the actual object that it defines, the term
 * represents the metadata and instance at the same time. This happens generally with
 * elements of an enumerated set: an enumerated value instance term will hold data in
 * addition to metadata regarding the object that it defines.
 */
define( "kTYPE_TERM_INSTANCE",					':type:term:instance' );

/*=======================================================================================
 *	DEFAULT NODE KINDS																	*
 *======================================================================================*/

/**
 * Root.
 *
 * An entry point of an ontology.
 *
 * This kind represents a door or entry point of a tree or graph. It can be either the node
 * from which the whole structure originates from, or a node that represents a specific
 * thematic entry point. In general, such objects will have other attributes that will
 * qualify the kind of the structure.
 */
define( "kTYPE_NODE_ROOT",						':kind:root-node' );

/**
 * Property.
 *
 * The full data property definition.
 *
 * This kind of node references a {@link Tag} object which contains all the necessary
 * information to define and describe a data property.
 */
define( "kTYPE_NODE_PROPERTY",					':kind:property-node' );

/**
 * Enumerated.
 *
 * A controlled vocabulary.
 *
 * This kind of node describes a controlled vocabulary, it has implicitly the
 * {@link kTYPE_NODE_TYPE} type holding an enumerated set of values. This kind of node can
 * be used to define a specific controlled vocabulary, its elements are related to this node
 * by the {@link kPREDICATE_ENUM_OF} predicate and this node can define a tag referring to
 * the latter using the kPREDICATE_TYPE_OF} predicate.
 */
define( "kTYPE_NODE_ENUMERATED",				':kind:enumerated-node' );

/*=======================================================================================
 *	DEFAULT NODE TYPES																	*
 *======================================================================================*/

/**
 * Ontology.
 *
 * An ontology.
 *
 * This is a graph structure that represents an ontology, the element that bares this
 * attribute is expected to be a root node, structures of this kind usually represent the
 * full set of elements comprising an ontology which will be used by views to create
 * thematic selections.
 */
define( "kTYPE_NODE_ONTOLOGY",					':type:node:ontology' );

/**
 * Type.
 *
 * A type or controlled vocabulary.
 *
 * This is a structure that represents a type or controlled vocabulary, the element that
 * bares this attribute is expected to be a root node and its structure must be a tree (at
 * most one parent node). The main use of such a kind is to group all elements representing
 * a type or controlled vocabulary that comprises the full set of attributes, views that
 * reference such structures can be used to represent thematic subsets of such types or
 * controlled vocabularies.
 */
define( "kTYPE_NODE_TYPE",						':type:node:type' );

/**
 * View.
 *
 * A view.
 *
 * This represents a view into an existing structure or structures, the element that bares
 * this attribute is expected to be a root node and the structure is expected to be either
 * a selection or an aggregation of elements from different existing structures. The main
 * goal is to create specific thematic views.
 */
define( "kTYPE_NODE_VIEW",						':type:node:view' );

/**
 * Template.
 *
 * A data template.
 *
 * This is a view that represents a template, the element that bares this attribute is
 * expected to be a root node and its structure must be a tree (at most one parent node).
 * Templates are generally used to import and export data recorded using elements from the
 * ontologies.
 */
define( "kTYPE_NODE_TEMPLATE",					':type:node:template' );

/**
 * Form.
 *
 * A search form.
 *
 * This is a view that represents a search form, the element that bares this attribute is
 * expected to be a root node and its structure must be a tree (at most one parent node).
 * Structures of this kind can be used as search form templates where the branches represent
 * categories and the leaf nodes the attributes to be searched.
 */
define( "kTYPE_NODE_FORM",						':type:node:form' );

/**
 * Structure.
 *
 * A data structure.
 *
 * This is a view that represents a data structure, the element that bares this attribute is
 * expected to be a root node and its structure must be a tree (at most one parent node).
 * Structures of this kind can be used as templates to define the physical structure of an
 * object.
 */
define( "kTYPE_NODE_STRUCT",					':type:node:struct' );

/**
 * Schema.
 *
 * A data schema.
 *
 * This is a view that represents a data schema, the element that bares this attribute is
 * expected to be a root node and its structure must be a tree (at most one parent node).
 * Structures of this kind can be used as templates to define common sub-structures which
 * will be used by structures to define the physical structure of an object.
 */
define( "kTYPE_NODE_SCHEMA",					':type:node:schema' );

/**
 * Feature.
 *
 * A feature or attribute of an object that can be described or measured.
 *
 * This kind of node defines a feature, property or attribute of an object that can be
 * described or measured. This kind of node will generally be found as a leaf of the
 * structure describing an object. Plant height is a plant characteristic that belongs to
 * the category of morphological traits: the latter is not a feature, while plant height is.
 */
define( "kTYPE_NODE_FEATURE",					':type:node:feature' );

/**
 * Method.
 *
 * A method or variation of an object's feature measurement.
 *
 * This kind of node is required whenever an object's feature can be measured in different
 * ways or with different workflows without becoming a different feature. Plant height is an
 * attribute of a plant which can be measured after a month or at flowering time; the
 * attribute is the same, but the method is different.
 */
define( "kTYPE_NODE_METHOD",					':type:node:method' );

/**
 * Scale.
 *
 * The scale or unit in which a measurement is expressed in.
 *
 * This kind of node describes in what unit or scale a measurement is expressed in. Plant
 * height may be measured in centimeters or inches, as well as in intervals or finite
 * categories.
 */
define( "kTYPE_NODE_SCALE",						':type:node:scale' );

/**
 * Enumeration.
 *
 * An element of a controlled vocabulary.
 *
 * This kind of node describes a controlled vocabulary element. These nodes derive from
 * scale nodes and represent the valid choices of enumeration and enumerated set scale
 * nodes. An ISO 3166 country code could be considered an enumeration node.
 */
define( "kTYPE_NODE_ENUMERATION",				':type:node:enumeration' );

/**
 * Term.
 *
 * A term.
 *
 * This kind of node describes a reference to a term with no specific qualification, this
 * type is only used to qualify a node that has no type.
 */
define( "kTYPE_NODE_TERM",						':type:node:term' );

/*=======================================================================================
 *	DEFAULT SESSION TYPES																*
 *======================================================================================*/

/**
 * Upload.
 *
 * This type of session covers template uploads.
 */
define( "kTYPE_SESSION_UPLOAD",					':type:session:100' );

/**
 * Update.
 *
 * Update session.
 *
 * This type of session covers data updates.
 */
define( "kTYPE_SESSION_UPDATE",					':type:session:200' );

/*=======================================================================================
 *	DEFAULT UPLOAD TRANSACTION TYPES													*
 *======================================================================================*/

/**
 * Template acquisition.
 *
 * This type of transaction covers the registration of a template.
 */
define( "kTYPE_TRANS_TMPL_ACQUISITION",			':type:transaction:110' );

/**
 * Template storage.
 *
 * This type of transaction covers the template file storage.
 */
define( "kTYPE_TRANS_TMPL_STORAGE",				':type:transaction:120' );

/**
 * Template parsing.
 *
 * This type of transaction covers the parsing of the template file.
 */
define( "kTYPE_TRANS_TMPL_PARSE",				':type:transaction:130' );

/**
 * Template warehouse.
 *
 * This type of transaction covers the set up of the template's warehouse resources.
 */
define( "kTYPE_TRANS_TMPL_WAREHOUSE",			':type:transaction:140' );

/**
 * Template worksheets validation.
 *
 * This type of transaction covers the validation of the template's worksheets.
 */
define( "kTYPE_TRANS_TMPL_WORKSHEETS",			':type:transaction:150' );

/**
 * Template properties validation.
 *
 * This type of transaction covers the validation of the template's properties.
 */
define( "kTYPE_TRANS_TMPL_PROPERTIES",			':type:transaction:160' );

/**
 * Template records validation.
 *
 * This type of transaction covers the validation of the template's records.
 */
define( "kTYPE_TRANS_TMPL_RECORDS",				':type:transaction:170' );

/**
 * Template transaction cleanup.
 *
 * This type of transaction covers the cleanup of the template warehouse resources.
 */
define( "kTYPE_TRANS_TMPL_CLEANUP",				':type:transaction:180' );

/**
 * Template transaction close.
 *
 * This type of transaction covers the closing of the template transaction.
 */
define( "kTYPE_TRANS_TMPL_CLOSE",				':type:transaction:190' );

/*=======================================================================================
 *	DEFAULT UPDATE TRANSACTION TYPES													*
 *======================================================================================*/

/**
 * Update resources selection.
 *
 * This type of transaction covers the selection of the update resources.
 */
define( "kTYPE_TRANS_UPDT_RESOURCES",			':type:transaction:210' );

/**
 * Data update.
 *
 * This type of transaction covers the data update.
 */
define( "kTYPE_TRANS_UPDT_DATA",				':type:transaction:220' );

/**
 * Update transaction cleanup.
 *
 * This type of transaction covers the cleanup of the update transaction resources.
 */
define( "kTYPE_TRANS_UPDT_CLEANUP",				':type:transaction:280' );

/**
 * Update transaction close.
 *
 * This type of transaction covers the closing of the update transaction.
 */
define( "kTYPE_TRANS_UPDT_CLOSE",				':type:transaction:290' );

/*=======================================================================================
 *	CARDINALITY TYPES																	*
 *======================================================================================*/

/**
 * List.
 *
 * A <i>list</i> cardinality type indicates that the referred property will hold a <i>list
 * of values</i> whose elements will have the data type defined by the <i>data type</i>
 * property.
 */
define( "kTYPE_LIST",							':type:list' );

/**
 * Full-text (weight 10).
 *
 * A <em>full-text</em> property is one which will be added to the <em>full-text
 * search index</em>, this specific type holds a weight of 10.
 */
define( "kTYPE_FULL_TEXT_10",					':type:full-text-10' );

/**
 * Full-text (weight 6).
 *
 * A <em>full-text</em> property is one which will be added to the <em>full-text
 * search index</em>, this specific type holds a weight of 6.
 */
define( "kTYPE_FULL_TEXT_06",					':type:full-text-06' );

/**
 * Full-text (weight 3).
 *
 * A <em>full-text</em> property is one which will be added to the <em>full-text
 * search index</em>, this specific type holds a weight of 3.
 */
define( "kTYPE_FULL_TEXT_03",					':type:full-text-03' );

/**
 * Summary.
 *
 * A <em>summary</em> property is one which can be used to group results in a summary.
 */
define( "kTYPE_SUMMARY",						':type:summary' );

/**
 * Lookup.
 *
 * A <em>lookup</em> property is a textual field that can be searched with auto-complete,
 * this implies that the property must be indexed.
 */
define( "kTYPE_LOOKUP",							':type:lookup' );

/**
 * Categorical.
 *
 * A <i>categorical</i> property is one which can take on one of a limited, and usually
 * fixed, number of possible values. In general, properties which take their values from an
 * enumerated set of choices are of this kind.
 */
define( "kTYPE_CATEGORICAL",					':type:categorical' );

/**
 * Quantitative.
 *
 * A <i>quantitative</i> property is one whose type of information based in quantities or
 * else quantifiable data. In general numerical values which can be aggregated in ranges
 * fall under this category.
 */
define( "kTYPE_QUANTITATIVE",					':type:quantitative' );

/**
 * Discrete.
 *
 * A <i>discrete</i> property is one which may <em>take an indefinite number of values</em>,
 * which <em>differentiates it from a categorical</em> property, and whose values are
 * <em>not continuous</em>, which <em>differentiates it from a quantitative property</em>.
 */
define( "kTYPE_DISCRETE",						':type:discrete' );

/**
 * Recommended.
 *
 * A <i>recommended</i> property is not mandatory, but its inclusion is highly encouraged.
 */
define( "kTYPE_RECOMMENDED",					':type:recommended' );

/**
 * Required.
 *
 * A <i>mandatory</i> property is required, its omission will cause an error.
 */
define( "kTYPE_MANDATORY",						':type:mandatory' );

/**
 * Private search.
 *
 * This kind indicates that the referred property should not be available to clients for
 * searching.
 */
define( "kTAG_PRIVATE_SEARCH",					':type:private-search' );

/**
 * Private modify.
 *
 * This kind indicates that the referred property is handled internally and it must not be
 * modified by clients.
 */
define( "kTAG_PRIVATE_MODIFY",					':type:private-modify' );

/**
 * Private display.
 *
 * This kind indicates that the referred property should not be displayed to clients.
 */
define( "kTYPE_PRIVATE_DISPLAY",				':type:private-display' );

/*=======================================================================================
 *	RELATIONSHIP TYPES																	*
 *======================================================================================*/

/**
 * Incoming.
 *
 * An <i>incoming relationship</i> indicates that the relationship is originating from an
 * external vertex directed to the current vertex.
 */
define( "kTYPE_RELATIONSHIP_IN",				':relationship:in' );

/**
 * Outgoing.
 *
 * An <i>outgoing relationship</i> indicates that the relationship is originating from the
 * current vertex directed to an external vertex.
 */
define( "kTYPE_RELATIONSHIP_OUT",				':relationship:out' );

/**
 * Cross referenced.
 *
 * An <i>cross referenced relationship</i> indicates that the relationship is going in both
 * directions: from the current vertex to an external vertex and from the external vertex to
 * the current vertex.
 */
define( "kTYPE_RELATIONSHIP_ALL",				':relationship:all' );

/*=======================================================================================
 *	ROLE TYPES																			*
 *======================================================================================*/

/**
 * Login.
 *
 * This role allows users to login.
 */
define( "kTYPE_ROLE_LOGIN",						':roles:user-login' );

/**
 * User invite.
 *
 * This role allows users to send user invitations.
 */
define( "kTYPE_ROLE_INVITE",					':roles:user-invite' );

/**
 * Upload data.
 *
 * This role allows users to upload data templates.
 */
define( "kTYPE_ROLE_UPLOAD",					':roles:user-upload' );

/**
 * Edit pages.
 *
 * This role allows users to edit portal pages.
 */
define( "kTYPE_ROLE_EDIT",						':roles:page-edit' );

/**
 * Manage all users.
 *
 * This role allows users to manage all users.
 */
define( "kTYPE_ROLE_USERS",						':roles:manage-users' );

/*=======================================================================================
 *	TYPED LISTs TYPES																	*
 *======================================================================================*/

/**
 * Default.
 *
 * This is the type used for default typed list elements.
 */
define( "kTYPE_LIST_DEFAULT",					'Default' );

/**
 * Referrer.
 *
 * This is the type used in the {@link kTAG_ENTITY_AFFILIATION} property to indicate the
 * user who is the authority.
 */
define( "kTYPE_LIST_REFERRER",					'Referrer' );

/**
 * Institute.
 *
 * This is the type used in the {@link kTAG_ENTITY_AFFILIATION} property to indicate the
 * main institute to which the user belongs.
 */
define( "kTYPE_LIST_INSTITUTE",					'Institute' );

/*=======================================================================================
 *	OPERATION STATUS TYPES																*
 *======================================================================================*/

/**
 * Completed.
 *
 * This status indicates a successful completion.
 */
define( "kTYPE_STATUS_OK",						':type:status:ok' );

/**
 * Failed.
 *
 * This status indicates a failed operation.
 */
define( "kTYPE_STATUS_FAILED",					':type:status:failed' );

/**
 * Executing.
 *
 * This status indicates an idle state.
 */
define( "kTYPE_STATUS_EXECUTING",				':type:status:executing' );

/**
 * Reports.
 *
 * This status indicates a successful completion with messages.
 */
define( "kTYPE_STATUS_MESSAGE",					':type:status:message' );

/**
 * Warning.
 *
 * This status indicates a successful completion with a warning.
 */
define( "kTYPE_STATUS_WARNING",					':type:status:warning' );

/**
 * Error.
 *
 * This status indicates a failed operation with an error.
 */
define( "kTYPE_STATUS_ERROR",					':type:status:error' );

/**
 * Fatal.
 *
 * This status indicates a failed operation with a fatal error.
 */
define( "kTYPE_STATUS_FATAL",					':type:status:fatal' );

/**
 * Exception.
 *
 * This status indicates a failed operation with an exception, maybe a bug.
 */
define( "kTYPE_STATUS_EXCEPTION",				':type:status:exception' );


?>
