<?php

/*=======================================================================================
 *																						*
 *									Session.inc.php										*
 *																						*
 *======================================================================================*/
 
/**
 * Default session tags.
 *
 * This file contains the default session offsets, these tags may be changed in case of
 * conflict.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/01/2014
 */

/*=======================================================================================
 *	CONNECTIONS																			*
 *======================================================================================*/

/**
 * DDICT
 *
 * Data dictionary.
 *
 * This tag represents the offset of the session element that holds the data dictionary.
 * This cache allows retrieving the native identifier of a {@link Tag} given its global
 * identifier, and the {@link Tag} object given its native identiofier.
 *
 * Note that this tag also represents the {@link Memcached} persistent ID for the tag cache.
 */
define( "kSESSION_DDICT",						'ddict' );


?>
