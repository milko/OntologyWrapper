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
 * Each entry is a definitions that holds the <i>global identifier</i> of the tag.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 13/01/2014
 */

/*=======================================================================================
 *	DEFAULT OBJECT TAGS																	*
 *======================================================================================*/

/**
 * Native identifier
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID/GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>_id</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:mixed</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Native identifier</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents the unique <i>native
 *			identifier</i> of an object. This identifier represents the primary key of an
 *			object, it can take any data type and it is not guaranteed to be persistent in
 *			all instances.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_NID",						'_id' );

/**
 * Global identifier
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>1</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:gid</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Global identifier</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents the unique <i>global
 *			identifier</i> of an object. This identifier represents the unique key of an
 *			object and it is by definition a string. This identifier is persistent, which
 *			means that it will not change across implementations (<i>unlike the native
 *			identifier, which may change across implementations</i>).</td>
 *	</tr>
 * </table>
 */
define( "kTAG_GID",						1 );

/**
 * Data type
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>2</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:data-type</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:set</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Data type</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a <i>data type</i>, it is an
 *			<i>enumerated set of term references</i> which define the format and type of
 *			a data property.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_DATA_TYPE",				2 );

/**
 * Cardinality type
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>3</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:cardinality-type</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:set</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Cardinality type</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a <i>cardinality type</i>, it is
 *			an <i>enumerated set of term references</i> which define the structure and
 *			properties of a data attribute. This generally indicates whether a property is
 *			a list, whether it is required or not, if it is indexed and other.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CARD_TYPE",				3 );

/**
 * Label
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>4</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:label</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:elem-match</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Label</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a <i>label</i>, it is a <i>short
 *			description or name</i> referring to an object. Labels store the language code
 *			in the key/value pair indexed by {@link kTAG_PART_KIND} and the label text in
 *			the pair indexed by {@link kTAG_PART_VALUE}. No two elements may share the same
 *			language and only one element may omit the language pair.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_LABEL",					4 );

/**
 * Definition
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>5</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:definition</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:elem-match</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">Label</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a <i>definition</i>, it is a
 *			<i>description that represents the definition</i> of an object. Definitions
 *			store the language code in the key/value pair indexed by {@link kTAG_PART_KIND}
 *			and the definition text in the pair indexed by {@link kTAG_PART_VALUE}. No two
 *			elements may share the same language and only one element may omit the language
 *			pair.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_DEFINITION",				5 );

/*=======================================================================================
 *	DEFAULT SUB-STRUCTURE TAGS															*
 *======================================================================================*/

/**
 * Kind part
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>6</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:part:kind</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Kind part</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag is generally used in combination with the
 *			{@link kTAG_PART_VALUE} to create a <i>list of typed or qualified entries</i>.
 *			Each entry is an array of two key/value pairs in which the pair featuring this
 *			tag as a key has as value a term that <i>defines the type, value or
 *			qualification</i> of the property stored in the value part of the other pair.
 *			For instance a home telephone number could be stored as an array of two
 *			key/value pairs in which the first pair, indexed by this tag, would hold as
 *			value the <tt>home</tt> string, while the second pair would be indexd by the
 *			{@link kTAG_PART_VALUE} tag and hold as value the telephone number.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_PART_KIND",				6 );

/**
 * Value part
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>7</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:part:value</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:mixed</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Value part</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag is generally used in combination with the
 *			{@link kTAG_PART_KIND} tag to create a <i>list of typed or qualified
 *			entries</i>. Each entry is an array of two key/value pairs in which the pair
 *			featuring the {@link kTAG_PART_KIND} tag as a key has as value a term that
 *			defines the type, value or qualification of the property stored as the value of
 *			the pair which uses this tag as the key. For instance a home telephone number
 *			could be stored as an array of two key/value pairs in which the first pair,
 *			indexed by the {@link kTAG_PART_KIND} tag, would hold as value the <tt>home</tt>
 *			string, while the second pair would be indexd by this tag and hold as value the
 *			telephone number.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_PART_VALUE",				7 );


?>
