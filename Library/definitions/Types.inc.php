<?php

/*=======================================================================================
 *																						*
 *									Types.inc.php										*
 *																						*
 *======================================================================================*/
 
/**
 * Type definitions.
 *
 * This file contains the default data and cardinality types.
 *
 * Each entry defines the <i>global identifier</i> of a term.
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
 * Indexed.
 *
 * An <i>indexed</i> cardinality type indicates that the referred property is indexed.
 */
define( "kTYPE_INDEXED",						':type:indexed' );

/**
 * Private.
 *
 * A <i>private</i> cardinality type indicates that the referred property is handled
 * internally and it must not be managed by clients.
 */
define( "kTYPE_PRIVATE",						':type:private' );


?>
