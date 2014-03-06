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

/*=======================================================================================
 *	STRUCTURED DATA TYPES																*
 *======================================================================================*/

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
 * Struct.
 *
 * This data type defines a <em>structure</em>, this means that the value will be an
 * <em>object</em> or an array of objects if the data kind is a list.
 */
define( "kTYPE_STRUCT",							':type:struct' );

/**
 * Language string elements list.
 *
 * This data type defines a <em>list of strings expressed in different languages</em>. The
 * list elements are composed by <em>two key/value pairs</em>. The first pair has the
 * {@link kTAG_LANGUAGE} tag as its key and the value represents the language code. The
 * second pair has the {@link kTAG_TEXT} as its key and the value represents the text
 * expressed in the language defined by the first pair. No two elements may share the same
 * language and only one element may omit the language pair.
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
 * the type pair.
 */
define( "kTYPE_TYPED_LIST",						':type:typed-list' );

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
 * Entity reference.
 *
 * An <i>entity reference</i> is a <em>string</em> that must correspond to the native
 * identifier of an {@link Entity} object.
 */
define( "kTYPE_REF_ENTITY",						':type:ref:entity' );

/**
 * Unit reference.
 *
 * A <i>unit reference</i> is a <em>string</em> that must correspond to the native
 * identifier of a {@link Unit} object.
 */
define( "kTYPE_REF_UNIT",						':type:ref:unit' );

/**
 * Self reference.
 *
 * This type defines an <em>reference</em> to an <em>object of the same class</em>.
 */
define( "kTYPE_REF_SELF",						':type:ref:self' );

/*=======================================================================================
 *	DEFAULT NODE TYPES																	*
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
define( "kTYPE_NODE_ROOT",						':type:node:root' );

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
define( "kTYPE_NODE_STRUCT",						':type:node:struct' );

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
define( "kTYPE_NODE_SCHEMA",						':type:node:schema' );

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
define( "kTYPE_NODE_FEATURE",						':type:node:feature' );

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
define( "kTYPE_NODE_METHOD",						':type:node:method' );

/**
 * Scale.
 *
 * The scale or unit in which a measurement is expressed in.
 *
 * This kind of node describes in what unit or scale a measurement is expressed in. Plant
 * height may be measured in centimeters or inches, as well as in intervals or finite
 * categories.
 */
define( "kTYPE_NODE_SCALE",							':type:node:scale' );

/**
 * Property.
 *
 * The full data property definition.
 *
 * This kind of node references a {@link Tag} object which contains al the necessary
 * information to define and describe a data property.
 */
define( "kTYPE_NODE_PROPERTY",						':type:node:property' );

/**
 * Instance.
 *
 * A metadata instance.
 *
 * In general, ontology nodes represent metadata, in some cases nodes may represent actual
 * data: an instance node is a node that represents the metadata and data of an object. An
 * ISO 3166 country code can be considered an enumeration node that constitutes the metadata
 * for the country it represents, but if you store data regarding that country in the node,
 * this may become also an instance node, because it represents the object it defines.
 */
define( "kTYPE_NODE_INSTANCE",						':type:node:instance' );

/**
 * Enumeration.
 *
 * An element of a controlled vocabulary.
 *
 * This kind of node describes a controlled vocabulary element. These nodes derive from
 * scale nodes and represent the valid choices of enumeration and enumerated set scale
 * nodes. An ISO 3166 country code could be considered an enumeration node.
 */
define( "kTYPE_NODE_ENUMERATION",					':type:node:enumeration' );

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
 * Private input.
 *
 * A <i>private input</i> cardinality type indicates that the referred property is handled
 * internally and it must not be set by clients.
 */
define( "kTYPE_PRIVATE_IN",						':type:private-in' );

/**
 * Private output.
 *
 * A <i>private output</i> cardinality type indicates that the referred property internal
 * and it should not be displayed to clients.
 */
define( "kTYPE_PRIVATE_OUT",					':type:private-out' );


?>
