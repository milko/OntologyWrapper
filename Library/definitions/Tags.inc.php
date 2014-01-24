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
 *	IDENTIFICATION ATTRIBUTES															*
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
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>mixed</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Native identifier</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Definition:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents the unique native identifier of
 *			the object. This identifier is used as the default unique key for all persistent
 *			objects.</td>
 *	</tr>
 * </table>
 */
define( "kTAG_IDENT_NID",					'_id' );

/*=======================================================================================
 *	CONNECTION ATTRIBUTES																*
 *======================================================================================*/

/**
 * Protocol
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>1</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:protocol</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>string</code></td>
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
define( "kTAG_CONN_PROTOCOL",				1 );

/**
 * Host
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>2</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:host</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>string</code></td>
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
define( "kTAG_CONN_HOST",					2 );

/**
 * Port
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>3</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:port</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>integer</code></td>
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
define( "kTAG_CONN_PORT",					3 );

/**
 * Socket
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>4</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:socket</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>string</code></td>
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
define( "kTAG_CONN_SOCKET",					4 );

/**
 * User
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>5</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:user</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>string</code></td>
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
define( "kTAG_CONN_USER",					5 );

/**
 * Pass
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>6</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:pass</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>string</code></td>
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
define( "kTAG_CONN_PASS",					6 );

/**
 * Persistent identifier
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>7</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:pid</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>string</code></td>
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
define( "kTAG_CONN_PID",						7 );

/**
 * Name
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>8</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:name</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>string</code></td>
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
define( "kTAG_CONN_NAME",						8 );

/**
 * Options
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>9</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:options</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>array</code></td>
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
define( "kTAG_CONN_OPTS",						9 );

/**
 * Database
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>NID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>10</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>GID:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>:connection:database</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>string</code></td>
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
define( "kTAG_CONN_DBASE",						10 );


?>
