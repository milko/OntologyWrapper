<?php

/*=======================================================================================
 *																						*
 *										local.inc.php									*
 *																						*
 *======================================================================================*/
 
/**
 *	Local include file.
 *
 *	This file should be included as the second file, it includes the file paths to relevant
 *	local directories and holds local definitions.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Skofic <m.skofic@cgiar.org>
 *	@version	1.00 11/03/2014
 */

/*=======================================================================================
 *	DEBUG FLAG																			*
 *======================================================================================*/

/**
 * Debug flag.
 *
 * This flag allows debugging information, disable it in production.
 */
define( "kDEBUG_FLAG",					TRUE );

/*=======================================================================================
 *	STANDARD DATA DICTIONARY															*
 *======================================================================================*/

/**
 * Default dictionary host.
 *
 * This tag indicates the standard data dictionary host.
 */
define( "kSTANDARDS_DDICT_HOST",		'localhost' );

/**
 * Default dictionary port.
 *
 * This tag indicates the standard data dictionary port.
 */
define( "kSTANDARDS_DDICT_PORT",		11211 );

/*=======================================================================================
 *	STANDARD GRAPH DSN																	*
 *======================================================================================*/

/**
 * Default graph database.
 *
 * This tag indicates the standard graqph database DSN.
 */
define( "kSTANDARDS_GRAPH_DB",			'neo4j://localhost:7474' );

/*=======================================================================================
 *	STANDARD DATABASE DSNs																*
 *======================================================================================*/

/**
 * Default metadata database.
 *
 * This tag indicates the standard metadata database DSN.
 */
//define( "kSTANDARDS_METADATA_DB",		'mongodb://localhost:27017/PGRDG?connect=1' );
define( "kSTANDARDS_METADATA_DB",		'mongodb://localhost:27017/TEST?connect=1' );

/**
 * Default entities database.
 *
 * This tag indicates the standard entities database DSN.
 */
//define( "kSTANDARDS_ENTITIES_DB",		'mongodb://localhost:27017/PGRDG?connect=1' );
define( "kSTANDARDS_ENTITIES_DB",		'mongodb://localhost:27017/TEST?connect=1' );

/**
 * Default units database.
 *
 * This tag indicates the standard units database DSN.
 */
//define( "kSTANDARDS_UNITS_DB",			'mongodb://localhost:27017/PGRDG?connect=1' );
define( "kSTANDARDS_UNITS_DB",			'mongodb://localhost:27017/TEST?connect=1' );

/*=======================================================================================
 *	DEFAULT VALUES																		*
 *======================================================================================*/

/**
 * Default language.
 *
 * This tag indicates the default language.
 */
define( "kSTANDARDS_LANGUAGE",			'en' );

/**
 * Default string list limit.
 *
 * This tag indicates the default strings list limit.
 */
define( "kSTANDARDS_STRINGS_LIMIT",		100 );

/**
 * Default enumerated list limit.
 *
 * This tag indicates the default enumerated list limit.
 */
define( "kSTANDARDS_ENUMS_LIMIT",		360 );

/**
 * Default units limit.
 *
 * This tag indicates the default units limit.
 */
define( "kSTANDARDS_UNITS_LIMIT",		50 );

/**
 * Maximum units limit.
 *
 * This tag indicates the maximum units limit.
 */
define( "kSTANDARDS_UNITS_MAX",			1000 );

/**
 * Maximum markers limit.
 *
 * This tag indicates the maximum markers limit.
 */
define( "kSTANDARDS_MARKERS_MAX",		10000 );

/*=======================================================================================
 *	CLIMATIC LIMITS																		*
 *======================================================================================*/

/**
 * Climate data URL.
 *
 * This token represents the base climate data service URL.
 */
define( "kCLIMATE_URL",				'http://geo.grinfo.net/features.php' );

/**
 * Minimum average distance.
 *
 * This token represents the minimum distance in meters, or coordinate uncertainty, that
 * will be used in climate data retrieval. This means that any uncertainty smaller than
 * this figure will not be used.
 */
define( "kCLIMATE_MIN_DIST",		925 );

/**
 * Maximum average distance.
 *
 * This token represents the efault maximum average tile distance from the provided
 * geometry in meters (10km.).
 */
define( "kCLIMATE_MAX_DIST",		111325 );

/**
 * Elevation range delta.
 *
 * This token represents the default range by which an elevation is decremented and
 * incremented in order to get the climate data (50m.).
 */
define( "kCLIMATE_DELTA_ELEV",		50 );

?>
