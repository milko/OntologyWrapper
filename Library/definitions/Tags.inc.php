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
 * Data kind
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>3</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:data-kind</code></td>
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
define( "kTAG_DATA_KIND",				3 );

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
 *		<td align="left" valign="top"><code>:type:kind/value</code></td>
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
 *		<td align="left" valign="top"><code>:type:kind/value</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
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

/**
 * Description
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>6</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:description</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:kind/value</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Description</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a <i>description</i>, this
 *			property is similar to a <i>definition</i>, except that while definitions are
 *			not dependant on the context, descriptions are. This property is generally used
 *			to add context dependant information to the definition. Descriptions have the
 *			same structure as labeld and definitions: they store the language code in the
 *			key/value pair indexed by {@link kTAG_PART_KIND} and the definition text in the
 *			pair indexed by {@link kTAG_PART_VALUE}. No two elements may share the same
 *			language and only one element may omit the language pair.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_DESCRIPTION",				6 );

/*=======================================================================================
 *	CONNECTION ATTRIBUTES																*
 *======================================================================================*/

/**
 * Protocol
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>7</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:protocol</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Protocol</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a connection <i>protocol or
 *			scheme</i> used in a network communication.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_PROTOCOL",				7 );

/**
 * Host
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>8</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:host</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Host</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a connection <i>domain name or
 *			internet address</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_HOST",					8 );

/**
 * Port
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>9</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:port</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:int</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Port</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a connection <i>TCP or UDP
 *			port</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_PORT",					9 );

/**
 * Socket
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>10</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:socket</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Socket</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a <i>connection socket</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_SOCKET",					10 );

/**
 * User
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>11</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:user</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">User code</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a <i>code used to authenticate
 *			with a service</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_USER",					11 );

/**
 * Pass
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>12</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:pass</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">User password</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a <i>password used to authenticate
 *			with a service</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_PASS",					12 );

/**
 * Persistent identifier
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>13</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:pid</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Persistent identifier</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a connection <i>persistent
 *			identifier</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_PID",						13 );

/**
 * Name
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>14</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:name</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Name</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a connection <i>name</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_NAME",						14 );

/**
 * Options
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>15</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:options</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:array</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Options</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents the <i>connection options</i> as
 *			a list of <i>key</i>/<i>value</i> pairs.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_OPTS",						15 );

/**
 * Database
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>16</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:database</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Database</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a database <i>name</i>.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_DBASE",						16 );

/*=======================================================================================
 *	DEFAULT SUB-STRUCTURE TAGS															*
 *======================================================================================*/

/**
 * Kind part
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>17</code></td>
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
define( "kTAG_PART_KIND",				17 );

/**
 * Value part
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>18</code></td>
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
define( "kTAG_PART_VALUE",				18 );


?>
