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
 * Data dictionary.
 *
 * <table>
 *	<tr>
 *		<td align="right" valign="top"><i>Offset:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>ddict</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Type:&nbsp;</i></td>
 *		<td align="left" valign="top"><code>CacheObject</code></td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Label:&nbsp;</i></td>
 *		<td align="left" valign="top">Data dictionaty</td>
 *	</tr>
 *	<tr>
 *		<td align="right" valign="top"><i>Description:&nbsp;</i></td>
 *		<td align="left" valign="top">This tag represents the offset of the session element
 *			that holds the data dictionary. This cache allows retrieving the native
 *			identifier of a {@link TagObject} given its global identifier, and the
 *			{@link TagObject} object given its native identifier.<br /><i>Note that this tag
 *			is also used the {@link Memcached} persistent ID for the tag cache</i>.</td>
 *	</tr>
 * </table>
 */
define( "kSESSION_DDICT",						'ddict' );


?>
