<?php

/*=======================================================================================
 *																						*
 *									Predicates.inc.php									*
 *																						*
 *======================================================================================*/
 
/**
 * Predicate definitions.
 *
 * This file contains the term global identifiers of the default predicates.
 *
 * Predicates are represented by a term object and serve as connectors between two vertex
 * nodes, these are the default predicates:
 *
 * <ul>
 *	<li><tt>{@link kPREDICATE_SUBCLASS_OF}</tt>: <i>Subclass of</i>. This predicate
 +		indicates that the subject of the relationship is a subclass of the object of the
 *		relationship, in other words, the subject is derived from the object. This predicate
 *		is equivalent to the <tt>IS-A</tt> predicate, it states that the subject is an
 *		instance of the object.
 *	<li><tt>{@link kPREDICATE_SUBSET_OF}</tt>: <i>Subset of</i>. This predicate indicates
 *		that the subject of the relationship represents a subset of the object of the
 *		relationship, in other words, the subject is a subset of the object, or the subject
 *		is contained by the object.
 *	<li><tt>{@link kPREDICATE_ATTRIBUTE_OF}</tt>: <i>Attribute of</i>. This predicate
 *		indicates that the subject of the relationship is an attribute of the object of the
 *		relationship, this means that the subject of the relationship is part of the set of
 *		attributes of the object of the relationship.
 *	<li><tt>{@link kPREDICATE_PROPERTY_OF}</tt>: <i>PROPERTY of</i>. This predicate
 *		indicates that the subject of the relationship is a property of the object of the
 *		relationship, this means that the subject of the relationship should be of the
 *		{@link kKIND_FEATURE} kind.
 *	<li><tt>{@link kPREDICATE_SCALE_OF}</tt>: <i>Scale of</i>. This predicate is used by
 *		scale vertices to connect feature or trait vertices, the subject of the relationship
 *		represents a scale or unit and the predicate indicates that the object of the
 *		relationship uses that scale or unit. This predicate is specifically used to
 *		indicate the different scales in which a feature ir trait vertex can be expressed
 *		in.
 *	<li><tt>{@link kPREDICATE_METHOD_OF}</tt>: <i>Method of</i>. This predicate relates
 *		method vertices with feature vertices or other methods, it indicates that the
 *		subject of the relationship is a method, or workflow variation of the object of the
 *		relationship. This predicate is used to connect the pipeline of modifiers applied to
 *		a feature vertex.
 *	<li><tt>{@link kPREDICATE_ENUM_OF}</tt>: <i>Enumeration of</i>. This predicate relates
 *		vertex elements of an enumerated set, it indicates that the subject of the
 *		relationship is an enumerated set element instance, and if the object of the
 *		relationship is also an enumerated set element instance, this means that the subject
 *		is a subset of the object.
 *	<li><tt>{@link kPREDICATE_PREFERRED}</tt>: <i>Preferred choice</i>. This predicate
 *		indicates that the object of the relationship is the preferred choice, in other
 *		words, if possible, one should use the object of the relationship in place of the
 *		subject. This predicate will be used in general by obsolete or deprecated items. The
 *		scope of this predicate is similar to the {@link kPREDICATE_VALID} predicate, except
 *		that in this case the use of the subject of the relationship is only deprecated,
 *		while in the {@link kPREDICATE_VALID} predicate it is not valid.
 *	<li><tt>{@link kPREDICATE_VALID}</tt>: <i>Valid choice</i>. This predicate indicates
 *		that the object of the relationship is the valid choice, in other words, the subject
 *		of the relationship is obsolete or not valid, and one should use the object of the
 *		relationship in its place. This predicate will be used in general to store obsolete
 *		or deprecated versions. The scope of this predicate is similar to the
 *		{@link kPREDICATE_PREFERRED} predicate, except that in this case the use of the
 *		subject of the relationship is invalid, while in the {@link kPREDICATE_PREFERRED}
 *		predicate it is only deprecated.
 *	<li><tt>{@link kPREDICATE_LEGACY}</tt>: <i>Legacy version</i>. This predicate indicates
 *		that the object of the relationship is the former or legacy choice, in other words,
 *		the object of the relationship is obsolete or not valid. This predicate will be used
 *		in general to record historical information. The scope of this predicate is similar
 *		to the {@link kPREDICATE_PREFERRED} and {@link kPREDICATE_VALID} predicates, except
 *		that in this case the legacy choice might not be invalid, nor deprecated: it only
 *		means that the object of the relationship was used in the past and the subject of
 *		the relationship is currently used in its place.
 *	<li><tt>{@link kPREDICATE_XREF}</tt>: <i>Cross reference</i>. This predicate indicates
 *		that the subject of the relationship is related to the object of the relationship.
 *		This predicate will be found generally in both directions and does not represent any
 *		specific type of relationship, other than what the edge object attributes may
 *		indicate. The scope of this predicate is similar to the
 *		{@link kPREDICATE_XREF_EXACT} predicate, except that the latter indicates that the
 *		object of the relationship can be used in place of the subject, while in this
 *		predicate this is not necessarily true.
 *	<li><tt>{@link kPREDICATE_XREF_EXACT}</tt>: <i>Exact cross reference</i>. This predicate
 *		indicates that the object of the relationship can be used in place of the subject of
 *		the relationship. If the predicate is found in both directions, one could say that
 *		the two vertices are identical, except for their formal representation. The scope of
 *		this predicate is similar to the XREF predicate, except that the latter only
 *		indicates that both vertices are related, this predicate indicates that they are
 *		interchangeable.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 25/11/2012
 */

/*=======================================================================================
 *	DEFAULT PREDICATES																	*
 *======================================================================================*/

/**
 * Subclass of.
 *
 * This predicate indicates that the subject of the relationship is a subclass of the
 * object of the relationship, in other words, the subject is derived from the object. This
 * predicate is equivalent to the IS-A predicate, it states that the subject is an instance
 * of the object.
 */
define( "kPREDICATE_SUBCLASS_OF",				':relationship:predicate:SUBCLASS-OF' );

/**
 * Subrank of.
 *
 * This predicate indicates that the subject of the relationship belongs to the next lowest
 * rank than the object of the relationship.
 */
define( "kPREDICATE_SUBRANK_OF",				':relationship:predicate:SUBRANK-OF' );

/**
 * Subset of.
 *
 * This predicate indicates that the subject of the relationship represents a subset of the
 * object of the relationship, in other words, the subject is a subset of the object, or the
 * subject is contained by the object.
 */
define( "kPREDICATE_SUBSET_OF",					':relationship:predicate:SUBSET-OF' );

/**
 * Part of.
 *
 * This predicate indicates that the subject of the relationship represents a part or
 * component of the object of the relationship, in other words, the subject is part of or is
 * a component of the object.
 */
define( "kPREDICATE_PART_OF",					':relationship:predicate:PART-OF' );

/**
 * Type of.
 *
 * This predicate indicates that the subject of the relationship represents the type of the
 * object of the relationship. This predicate is used as a group and a proxy: it can be used
 * to define a group of elements which can then be related as a whole to other objects; it
 * acts as a proxy, because the group holder is not related to the object of the
 * relationship, the elements of the groupo are. This predicate is used to define controlled
 * vocabularies and relate these to tags, without gaving to duplicate the set elements.
 */
define( "kPREDICATE_TYPE_OF",					':relationship:predicate:TYPE-OF' );

/**
 * Function of.
 *
 * This predicate indicates that the subject of the relationship represents a function or
 * trait group of the object of the relationship, in other words, the subject is a group of
 * functions that can be applied to the object.
 */
define( "kPREDICATE_FUNCTION_OF",				':relationship:predicate:FUNCTION-OF' );

/**
 * Collection of.
 *
 * This predicate indicates that the subject of the relationship is a collection belonging
 * to the object. This predicate is similar to {@link kPREDICATE_PROPERTY_OF}, except that
 * the latter case the subject is a scalar element of the object, while, in this case, the
 * subject is a template for the collection of elements that belong to the object.
 */
define( "kPREDICATE_COLLECTION_OF",				':relationship:predicate:COLLECTION-OF' );

/**
 * Attribute of.
 *
 * This predicate indicates that the subject of the relationship is an attribute of the
 * object of the relationship, this means that the subject of the relationship is part of
 * the set of attributes of the object of the relationship.
 */
define( "kPREDICATE_ATTRIBUTE_OF",				':relationship:predicate:ATTRIBUTE-OF' );

/**
 * Property of.
 *
 * This predicate indicates that the subject of the relationship is a property of the
 * object of the relationship, this means that the subject of the relationship should be of
 * the {@link kKIND_FEATURE} kind.
 */
define( "kPREDICATE_PROPERTY_OF",				':relationship:predicate:PROPERTY-OF' );

/**
 * Trait of.
 *
 * This predicate relates a trait to its category vertex, it indicates that the subject of
 * the relationship is a trait of the relationship object. This predicate is used to connect
 * feature vertices to their parent vertex.
 */
define( "kPREDICATE_TRAIT_OF",					':relationship:predicate:TRAIT-OF' );

/**
 * Method of.
 *
 * This predicate relates method vertices with feature vertices or other methods, it
 * indicates that the subject of the relationship is a method, or workflow variation of the
 * object of the relationship. This predicate is used to connect the pipeline of modifiers
 * applied to a feature vertex.
 */
define( "kPREDICATE_METHOD_OF",					':relationship:predicate:METHOD-OF' );

/**
 * Scale of.
 *
 * This predicate is used by scale vertices to connect feature or trait vertices, the
 * subject of the relationship represents a scale or unit and the predicate indicates that
 * the object of the relationship uses that scale or unit. This predicate is specifically
 * used to indicate the different scales in which a feature ir trait vertex can be expressed
 * in.
 */
define( "kPREDICATE_SCALE_OF",					':relationship:predicate:SCALE-OF' );

/**
 * Enumeration of.
 *
 * This predicate relates vertex elements of an enumerated set, it indicates that the
 * subject of the relationship is an enumerated set element instance, and if the object of
 * the relationship is also an enumerated set element instance, this means that the subject
 * is a subset of the object.
 */
define( "kPREDICATE_ENUM_OF",					':relationship:predicate:ENUM-OF' );

/**
 * Instance of.
 *
 * This predicate relates a type to its instance, it indicates that the object of the
 * relationship is an instance of the subject of the relationship.
 */
define( "kPREDICATE_INSTANCE_OF",				':relationship:predicate:INSTANCE-OF' );

/**
 * Aggregate.
 *
 * This predicate indicates that the subject of the relationship should be <i>aggregated</i>
 * into the object of the relationship, in other words, the subject should be copied into
 * the object. This predicate will be used in general to <i>copy</i> a value from a
 * descriptor belonging to a set specific to a kind of domain into a descriptor which is
 * common to a series of different unit kinds. This is useful for searching among
 * heterogeneous kinds of objects.
 */
define( "kPREDICATE_AGGREGATE",					':relationship:predicate:AGGREGATE' );

/**
 * Preferred choice.
 *
 * This predicate indicates that the object of the relationship is the preferred choice, in
 * other words, if possible, one should use the object of the relationship in place of the
 * subject. This predicate will be used in general by obsolete or deprecated items.
 *
 * The scope of this predicate is similar to the VALID predicate, except that in this case
 * the use of the subject of the relationship is only deprecated, while in the VALID
 * predicate it is not valid.
 */
define( "kPREDICATE_PREFERRED",					':relationship:predicate:PREFERRED' );

/**
 * Valid choice.
 *
 * This predicate indicates that the object of the relationship is the valid choice, in
 * other words, the subject of the relationship is obsolete or not valid, and one should use
 * the object of the relationship in its place. This predicate will be used in general to
 * store the obsolete or deprecated versions.
 *
 * The scope of this predicate is similar to the PREFERRED predicate, except that in this
 * case the use of the subject of the relationship is invalid, while in the PREFERRED
 * predicate it is only deprecated.
 */
define( "kPREDICATE_VALID",						':relationship:predicate:VALID' );

/**
 * Legacy version.
 *
 * This predicate indicates that the object of the relationship is the former or legacy
 * choice, in other words, the object of the relationship is obsolete or not valid. This
 * predicate will be used in general to record historical information.
 *
 * The scope of this predicate is similar to the PREFERRED and VALID predicates, except that
 * in this case the legacy choice might not be invalid, nor deprecated: it only means that
 * the object of the relationship was used in the past and the subject of the relationship
 * is currently used in its place.
 */
define( "kPREDICATE_LEGACY",					':relationship:predicate:LEGACY' );

/**
 * Cross reference.
 *
 * This predicate indicates that the subject of the relationship is related to the object
 * of the relationship. This predicate will be found generally in both directions and does
 * not represent any specific type of relationship, other than what the edge object
 * attributes may indicate.
 *
 * The scope of this predicate is similar to the XREF-EXACT predicate, except that the
 * latter indicates that the object of the relationship can be used in place of the subject,
 * while in this predicate this is not necessarily true.
 */
define( "kPREDICATE_XREF",						':relationship:predicate:XREF' );

/**
 * Exact cross reference.
 *
 * This predicate indicates that the object of the relationship can be used in place of the
 * subject of the relationship. If the predicate is found in both directions, one could say
 * that the two vertices are identical, except for their formal representation.
 *
 * The scope of this predicate is similar to the XREF predicate, except that the latter only
 * indicates that both vertices are related, this predicate indicates that they are
 * interchangeable.
 */
define( "kPREDICATE_XREF_EXACT",				':relationship:predicate:XREF-EXACT' );


?>
