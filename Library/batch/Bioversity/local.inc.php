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
 *	PORTAL PREFIX																		*
 *======================================================================================*/

/**
 * Portal prefix.
 *
 * This tag indicates the default portal prefix.
 */
define( "kPORTAL_PREFIX",	'pgrdg' );

/*=======================================================================================
 *	GRAPH SERVICE REFERENCES															*
 *======================================================================================*/

/**
 * Graph service switch.
 *
 * This tag flag indicates whether to use or not the graph database.
 */
define( "kGRAPH_DO",		FALSE );

/**
 * Graph service filename.
 *
 * This tag indicates the graph service filename, this refers to the launchctl file that
 * runs the graph database.
 */
define( "kGRAPH_SERVICE",	'/Users/milko/Library/LaunchAgents/org.neo4j.server.plist' );

/**
 * Graph data directory.
 *
 * This tag indicates the directory where the graph database is stored, it will be used to
 * clear the database, the path must finish with the directory token.
 */
define( "kGRAPH_DIR",		'/Volumes/Data/Neo4j/' );

/*=======================================================================================
 *	DEFAULT VALUES																		*
 *======================================================================================*/

/**
 * Default language.
 *
 * This tag indicates the default language.
 */
define( "kSTANDARDS_LANGUAGE",			'en' );

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
 * Default average distance.
 *
 * This token represents the default distance in meters, or coordinate uncertainty, that
 * will be used in climate data retrieval. This value will be used whenever the distance is
 * required but not provided.
 */
define( "kCLIMATE_DEF_DIST",		7000 );

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
 * This token represents the default maximum average tile distance from the provided
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

/*=======================================================================================
 *	STANDARDS SUB-FOLDER NAMES															*
 *======================================================================================*/

/**
 * Defaults.
 *
 * This tag indicates the directory name where the default standards XML files are stored.
 */
define( "kDIR_STANDARDS_DEFAULT",				'default' );

/**
 * ISO standards.
 *
 * This tag indicates the directory name where the ISO standards XML files are stored.
 */
define( "kDIR_STANDARDS_ISO",					'iso' );

/**
 * WBI standards.
 *
 * This tag indicates the directory name where the WBI standards XML files are stored.
 */
define( "kDIR_STANDARDS_WBI",					'wbi' );

?>
