<?php

/*=======================================================================================
 *																						*
 *									Operators.inc.php									*
 *																						*
 *======================================================================================*/
 
/**
 *	Match operators.
 *
 *	This file contains the enumerated set definition of all standard match operators.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 08/05/2014
 */

/*=======================================================================================
 *	OPERATORS																			*
 *======================================================================================*/

/**
 * Equal.
 *
 * This enumeration represents equality.
 */
define( "kOPERATOR_EQUAL",				'$EQ' );			// Equals.

/**
 * Not equal.
 *
 * This enumeration represents inequality.
 */
define( "kOPERATOR_EQUAL_NOT",			'$NE' );			// Not equal.

/**
 * Prefix.
 *
 * This enumeration represents prefix comparaison: <i>starts with</i> (for strings).
 */
define( "kOPERATOR_PREFIX",				'$PX' );			// Starts with.

/**
 * Contains.
 *
 * This enumeration represents content comparaison: <i>contains</i> (for strings).
 */
define( "kOPERATOR_CONTAINS",			'$CX' );			// Contains.

/**
 * Suffix.
 *
 * This enumeration represents suffix comparaison: <i>ends with</i> (for strings).
 */
define( "kOPERATOR_SUFFIX",				'$SX' );			// Ends with.

/**
 * Regular expression.
 *
 * This enumeration represents a regular expression (for strings).
 */
define( "kOPERATOR_REGEX",				'$RE' );			// Regular expression.

/*=======================================================================================
 *	MODIFIERS																			*
 *======================================================================================*/

/**
 * Case and accent insensitive.
 *
 * This enumeration indicates a case and accent insensitive match.
 */
define( "kOPERATOR_NOCASE",				'$i' );				// Case insensitive.


?>
