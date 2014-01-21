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
 * <code>_id</code>
 *
 * This tag represents the unique native identifier of the object. This identifier is
 * used as the default unique key for all persistent objects.
 */
define( "kTAG_IDENT_NID",						'_id' );

/*=======================================================================================
 *	CONNECTION ATTRIBUTES																*
 *======================================================================================*/

/**
 * Protocol
 *
 * <code>:connection:protocol</code>
 *
 * This tag represents a connection <i>protocol or scheme</i> used in a network
 * communication.
 */
define( "kTAG_CONN_PROTOCOL",					1 );

/**
 * Host
 *
 * <code>:connection:host</code>
 *
 * This tag represents a connection <i>domain name or internet address</i>.
 */
define( "kTAG_CONN_HOST",						2 );

/**
 * Port
 *
 * <code>:connection:port</code>
 *
 * This tag represents a connection <i>TCP or UDP port</i>.
 */
define( "kTAG_CONN_PORT",						3 );

/**
 * User
 *
 * <code>:connection:user</code>
 *
 * This tag represents a <i>code used to authenticate with a service</i>.
 */
define( "kTAG_CONN_USER",						4 );

/**
 * Pass
 *
 * <code>:connection:pass</code>
 *
 * This tag represents a <i>password used to authenticate with a service</i>.
 */
define( "kTAG_CONN_PASS",						5 );

/**
 * Persistent identifier
 *
 * <code>:connection:pid</code>
 *
 * This tag represents a connection <i>persistent identifier</i>.
 */
define( "kTAG_CONN_PID",						6 );


?>
