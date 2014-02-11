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
 *	INTERNAL OBJECT TAGS																	*
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
 *			all instances. This tag is internal and will not be defined in the
 *			ontology.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_NID",						'_id' );

/**
 * Class
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID/GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>_class</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Object class</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents the <i>object class name</i>, this
 *			string is used to instantiate the correct object once loaded from a container.
 *			This tag is internal and will not be defined in the ontology.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CLASS",						'_class' );

/*=======================================================================================
 *	DEFAULT OBJECT TAGS																	*
 *======================================================================================*/

/**
 * Namespace
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>1</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:ns</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Namespace</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents the <i>namespace</i> of an
 *			identifier, it is used to <i>disambiguate homonym local identifiers</i> in order
 *			to come up with a global unique identifier. Namespaces are by definition strings
 *			which should represent global identifiers, for this reason namespaces are
 *			persistent, which means that they will not change across implementations.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_NS",						1 );

/**
 * Local identifier
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>2</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:lid</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Local identifier</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents the unique <i>local
 *			identifier</i> of an object. This identifier represents the unique key of an
 *			object within its <i>namespace</i> and it is by definition a string. This
 *			identifier is persistent, which means that it will not change across
 *			implementations.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_LID",						2 );

/**
 * Persistent identifier
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>3</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:pid</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Pesistent identifier</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents the <i>persistent
 *			identifier</i> of an object. This identifier represents the unique key of an
 *			object and it is by definition a string. This identifier is persistent, which
 *			means that it will not change across implementations (<i>unlike the native
 *			identifier, which may change across implementations</i>).</td>
 *	</tr>
 * </table>
 */
define( "kTAG_PID",						3 );

/**
 * Sequence
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>4</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:seq</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:int</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Sequence</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a <i>unique identifier</i> other
 *			than the global identifier, it is constituted by an <i>sequential integer
 *			number</i>, which might change across implementations. The main use of such an
 *			identifier is to reduce storage requirements when indexing and referencing
 *			objects.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_SEQ",						4 );

/**
 * Branch
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>5</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:terms</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:ref-term</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data kind:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:list</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Branch</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag holds a <i>path of a graph branch</i>,
 *			constituted by a <i>sequence of vertices connected by predicates</i>. It is an
 *			array holding an odd number of elements representing <i>term references</i> in
 *			which the odd elements are vertices and the even elements are predicates.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_TERMS",					5 );

/**
 * Data type
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>6</code></td>
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
define( "kTAG_DATA_TYPE",				6 );

/**
 * Data kind
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>7</code></td>
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
define( "kTAG_DATA_KIND",				7 );

/**
 * Label
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>8</code></td>
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
 *			in the key/value pair indexed by {@link kTAG_SUB_LANGUAGE} and the label text in
 *			the pair indexed by {@link kTAG_SUB_TEXT}. No two elements may share the same
 *			language and only one element may omit the language pair.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_LABEL",					8 );

/**
 * Definition
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>9</code></td>
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
 *			store the language code in the key/value pair indexed by {@link kTAG_SUB_LANGUAGE}
 *			and the definition text in the pair indexed by {@link kTAG_SUB_TEXT}. No two
 *			elements may share the same language and only one element may omit the language
 *			pair.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_DEFINITION",				9 );

/**
 * Description
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>10</code></td>
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
 *			key/value pair indexed by {@link kTAG_SUB_LANGUAGE} and the definition text in the
 *			pair indexed by {@link kTAG_SUB_TEXT}. No two elements may share the same
 *			language and only one element may omit the language pair.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_DESCRIPTION",				10 );

/*=======================================================================================
 *	CONNECTION ATTRIBUTES																*
 *======================================================================================*/

/**
 * Protocol
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>11</code></td>
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
define( "kTAG_CONN_PROTOCOL",				11 );

/**
 * Host
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>12</code></td>
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
define( "kTAG_CONN_HOST",					12 );

/**
 * Port
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>13</code></td>
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
define( "kTAG_CONN_PORT",					13 );

/**
 * User
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>14</code></td>
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
define( "kTAG_CONN_USER",					14 );

/**
 * Pass
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>15</code></td>
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
define( "kTAG_CONN_PASS",					15 );

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
 *		<td align="left" valign="top">This tag represents a connection <i>database</i>
 *			name.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_BASE",					16 );

/**
 * Collection
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>17</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:collection</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Collection</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents a connection <i>collection</i>
 *			name .</td>
 *	</tr>
 * </table>
 */
define( "kTAG_CONN_COLL",					17 );

/**
 * Options
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>18</code></td>
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
define( "kTAG_CONN_OPTS",					18 );

/*=======================================================================================
 *	DEFAULT SUB-STRUCTURE TAGS															*
 *======================================================================================*/

/**
 * Language
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>19</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:sub:language</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Language code</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This attribute is a <em>string</em> representing a
 *			<em>language code</em>, this is generally used in combination with the
 *			{@link kTAG_SUB_TEXT} attribute to provide a list of strings in different
 *			languages.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_SUB_LANGUAGE",				19 );

/**
 * Text
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>20</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:sub:text</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Data type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:type:string</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Text</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This attribute is a <em>string</em> representing a
 *			<em>string</em> or <em>text</em>, this is generally used in combination with the
 *			{@link kTAG_SUB_LANGUAGE} attribute to provide a list of strings in different
 *			languages.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_SUB_TEXT",					20 );


?>
