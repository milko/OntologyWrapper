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
 *	PHP BINARY																			*
 *======================================================================================*/

/**
 * PHP binary.
 *
 * This definition should hold the path to the PHP binary.
 */
define( "kPHP_BINARY",					"/usr/local/php5/bin/php" );

/*=======================================================================================
 *	PORTAL IDENTIFICATION																*
 *======================================================================================*/

/**
 * Portal domain.
 *
 * This tag indicates the portal domain, this value will usually be the same as the portal
 * web domain, except that this value should not change, since it is used in the protal
 * user's identifiers.
 *
 * This value will be set in the {@link kTAG_COLLECTION} property of the portal users.
 */
define( "kPORTAL_DOMAIN",				'pgrdiversity.bioversityinternational.org' );

/**
 * Portal host.
 *
 * This tag indicates the portal host.
 */
define( "kPORTAL_HOST",					'pgrdiversity.bioversityinternational.org' );

/**
 * Portal prefix.
 *
 * This tag indicates the default portal prefix.
 */
define( "kPORTAL_PREFIX",				'pgrdg' );

/**
 * Portal authority.
 *
 * This tag indicates the portal authority, this value represents the identifier of the
 * entity which is the author or which is responsible for the portal's information.
 *
 * This value will be set in the {@link kTAG_AUTHORITY} property of the portal users.
 */
define( "kPORTAL_AUTHORITY",			'ITA406' );

/*=======================================================================================
 *	PORTAL MAILER INFORMATION															*
 *======================================================================================*/

/**
 * Portal mailer.
 *
 * This tag indicates the default mail address for the portal.
 */
define( "kPORTAL_MAILER",				'pgrdg@grinfo.net' );

/**
 * Portal mailer name.
 *
 * This tag indicates the default mailer name for the portal.
 */
define( "kPORTAL_MAILER_NAME",			'Gateway Mailer' );

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
 *	STANDARD DATABASE DSNs																*
 *======================================================================================*/

/**
 * Default metadata database.
 *
 * This tag indicates the standard metadata database DSN.
 */
define( "kSTANDARDS_METADATA_DB",		'mongodb://localhost:27017/BIOVERSITY?connect=1' );

/**
 * Default entities database.
 *
 * This tag indicates the standard entities database DSN.
 */
define( "kSTANDARDS_ENTITIES_DB",		'mongodb://localhost:27017/BIOVERSITY?connect=1' );

/**
 * Default units database.
 *
 * This tag indicates the standard units database DSN.
 */
define( "kSTANDARDS_UNITS_DB",			'mongodb://localhost:27017/BIOVERSITY?connect=1' );

/**
 * Default graph database.
 *
 * This tag indicates the standard graqph database DSN.
 */
define( "kSTANDARDS_GRAPH_DB",			'neo4j://localhost:7474' );

/*=======================================================================================
 *	STANDARD PORTAL COLLECTION NAME														*
 *======================================================================================*/

/**
 * Default portal collection name.
 *
 * This tag indicates the default portal collection name.
 */
define( "kSTANDARDS_PORTAL_COLLECTION",	'_portal' );

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
define( "kCLIMATE_URL",					'http://geo.grinfo.net/features.php' );

/**
 * Default average distance.
 *
 * This token represents the default distance in meters, or coordinate uncertainty, that
 * will be used in climate data retrieval. This value will be used whenever the distance is
 * required but not provided.
 */
define( "kCLIMATE_DEF_DIST",			7000 );

/**
 * Minimum average distance.
 *
 * This token represents the minimum distance in meters, or coordinate uncertainty, that
 * will be used in climate data retrieval. This means that any uncertainty smaller than
 * this figure will not be used.
 */
define( "kCLIMATE_MIN_DIST",			925 );

/**
 * Maximum average distance.
 *
 * This token represents the default maximum average tile distance from the provided
 * geometry in meters (10km.).
 */
define( "kCLIMATE_MAX_DIST",			111325 );

/**
 * Elevation range delta.
 *
 * This token represents the default range by which an elevation is decremented and
 * incremented in order to get the climate data (50m.).
 */
define( "kCLIMATE_DELTA_ELEV",			50 );

/*=======================================================================================
 *	STANDARDS SUB-FOLDER NAMES															*
 *======================================================================================*/

/**
 * Defaults.
 *
 * This tag indicates the directory name where the default standards XML files are stored.
 */
define( "kDIR_STANDARDS_DEFAULT",		'default' );

/**
 * ISO standards.
 *
 * This tag indicates the directory name where the ISO standards XML files are stored.
 */
define( "kDIR_STANDARDS_ISO",			'iso' );

/**
 * WBI standards.
 *
 * This tag indicates the directory name where the WBI standards XML files are stored.
 */
define( "kDIR_STANDARDS_WBI",			'wbi' );

?>
