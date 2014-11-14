<?php

/*=======================================================================================
 *																						*
 *										Tokens.inc.php									*
 *																						*
 *======================================================================================*/
 
/**
 * Tokens.
 *
 * This file contains the common tokens used in this library.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 07/02/2014
 */

/*=======================================================================================
 *	TOKENS																				*
 *======================================================================================*/

/**
 * Tag prefix token.
 *
 * This value defines the token that prefixes tag serial identifiers.
 */
define( "kTOKEN_TAG_PREFIX",		    '#' );		    	// Tag prefix token.

/**
 * Domain separator token.
 *
 * This value defines the token that separates the object domain from other elements.
 */
define( "kTOKEN_DOMAIN_SEPARATOR",		'://' );			// Domain separator token.

/**
 * Class separator token.
 *
 * This value defines the token that separates the object class from other elements.
 */
define( "kTOKEN_CLASS_SEPARATOR",		'::' );				// Class separator token.

/**
 * Namespace separator token.
 *
 * This value defines the token used to separate namespaces from codes.
 */
define( "kTOKEN_NAMESPACE_SEPARATOR",	':' );				// Namespace separator token.

/**
 * Sub-offset separator token.
 *
 * This value defines the token used to indicate sub-offsets.
 */
define( "kTOKEN_OFFSET_SEPARATOR",		'.' );				// Offset separator token.

/**
 * Index separator token.
 *
 * This value defines the token used to separate index elements.
 */
define( "kTOKEN_INDEX_SEPARATOR",		'/' );				// Index separator token.

/**
 * Namespace opening token.
 *
 * This token is used to enclose namespaces or parent identification when merged with other
 * identification sections, this value is used as a prefix.
 */
define( "kTOKEN_INDEX_OPEN_TAG",		'{' );				// Namespace start tag.

/**
 * Namespace closing token.
 *
 * This token is used to enclose namespaces or parent identification when merged with other
 * identification sections, this value is used as a postfix.
 */
define( "kTOKEN_INDEX_CLOSE_TAG",		'}' );				// Namespace end tag.

/**
 * Identification end token.
 *
 * This token should close all identification strings.
 */
define( "kTOKEN_END_TAG",				';' );				// Identification end tag.


?>
