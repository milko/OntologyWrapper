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
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Term:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:mixed</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Mixed</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">A <i>mixed</i> data type indicates that the referred
 *			property may take any data type.</td>
 *	</tr>
 * </table>
 */
define( "kTYPE_MIXED",							':type:mixed' );

/**
 * String.
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Term:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">String</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">A <i>string</i> data type indicates that the referred
 *			property may hold <i>alphanumeric</i> and <i>punctuation</i> characters, this
 *			type <i>does not include binary data</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTYPE_STRING",							':type:string' );

/**
 * Integer.
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Term:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:int</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Integer</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">An <i>integer</i> data type indicates that the
 *			referred property may hold a <i>32 or 64 bit integral numeric value</i>,.</td>
 *	</tr>
 * </table>
 */
define( "kTYPE_INT",							':type:int' );

/**
 * Float.
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Term:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:float</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Float</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">A <i>float</i> data type indicates that the
 *			referred property may hold a <i>floating point number</i>, also known as
 *			<i>double</i> or <i>real</i>, the precision of such value is not inferred, in
 *			general it will be a <i>32 or 64 bit real</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTYPE_FLOAT",							':type:float' );

/*=======================================================================================
 *	STRUCTURED DATA TYPES																*
 *======================================================================================*/

/**
 * Array.
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Term:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:array</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Array</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">An <i>array</i> data type indicates that the
 *			referred property may only hold <i>a list of key/value pairs</i>, the data type
 *			of the array keys and values is not inferred.</td>
 *	</tr>
 * </table>
 */
define( "kTYPE_ARRAY",							':type:array' );

/**
 * Element match list.
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Term:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:type-value</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Element match list</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This data type defines a <i>list of elements comprised
 *			of two key value pairs</i> in which the first pair, whose key is the
 *			{@link kTAG_PART_KIND} tag, defines the type, kind or qualification of the
 *			property stored as the value of the second pair in which the key is the
 *			{@link kTAG_PART_VALUE} tag. For instance a home telephone number could be
 *			stored as an element as an array of two key/value pairs in which the first
 *			pair with key {@link kTAG_PART_KIND} would hold the <tt>home</tt> string value,
 *			while the second pair with key {@link kTAG_PART_VALUE} would hold the actual
 *			telephone number. Lists of this kind cannot have more than one element matching
 *			the same {@link kTAG_PART_KIND} value.</td>
 *	</tr>
 * </table>
 */
define( "kTYPE_ELEMENT",						':type:elem-match' );

/*=======================================================================================
 *	REFERENCE DATA TYPES																*
 *======================================================================================*/

/**
 * Enumeration.
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Term:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:enum</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Enumeration</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">An <i>enumerated</i> data type indicates that the
 *			referred property may only hold <i>a term reference</i>, that is, the <i>global
 *			identifier of a term object</i>. Enumerated values are by default strings and
 *			must refer to a term.</td>
 *	</tr>
 * </table>
 */
define( "kTYPE_ENUM",							':type:enum' );

/**
 * Enumerated set.
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Term:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:set</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Enumerated set</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">An <i>enumerated set</i> data type indicates that the
 *			referred property may only hold <i>a list of term reference</i>, that is, an
 *			array of <i>term object global identifiers</i>. All the elements of this list
 *			must be unique.</td>
 *	</tr>
 * </table>
 */
define( "kTYPE_SET",							':type:set' );

/*=======================================================================================
 *	CARDINALITY TYPES																	*
 *======================================================================================*/

/**
 * List.
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Term:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:list</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">List</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">A <i>list</i> cardinality type indicates that the
 *			referred property will hold a <i>list of values</i> defined by the property
 *			<i>data type</i>.
 *	</tr>
 * </table>
 */
define( "kTYPE_LIST",							':type:list' );


?>
