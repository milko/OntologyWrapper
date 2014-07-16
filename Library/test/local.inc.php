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
 *	STANDARDS SUB-FOLDER NAMES															*
 *======================================================================================*/

/**
 * Defaults.
 *
 * This tag indicates the directory name where the default standards XML files are stored.
 */
define( "kDIR_STANDARDS_DEFAULT",				'default' );

/**
 * Standards.
 *
 * This tag indicates the directory name where the standards XML files are stored.
 */
define( "kDIR_STANDARDS_STANDARDS",				'standards' );

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

/**
 * UNSD standards.
 *
 * This tag indicates the directory name where the UNSD standards XML files are stored.
 */
define( "kDIR_STANDARDS_UNSD",					'unsd' );

/**
 * FAO standards.
 *
 * This tag indicates the directory name where the FAO standards XML files are stored.
 */
define( "kDIR_STANDARDS_FAO",					'fao' );

/**
 * DWC standards.
 *
 * This tag indicates the directory name where the DWC standards XML files are stored.
 */
define( "kDIR_STANDARDS_DWC",					'dwc' );

/**
 * IUCN standards.
 *
 * This tag indicates the directory name where the IUCN standards XML files are stored.
 */
define( "kDIR_STANDARDS_IUCN",					'iucn' );

/**
 * MCPD standards.
 *
 * This tag indicates the directory name where the MCPD standards XML files are stored.
 */
define( "kDIR_STANDARDS_MCPD",					'mcpd' );

/**
 * LR standards.
 *
 * This tag indicates the directory name where the landrace standards XML files are stored.
 */
define( "kDIR_STANDARDS_LR",					'lr' );

/**
 * CWR standards.
 *
 * This tag indicates the directory name where the CWR standards XML files are stored.
 */
define( "kDIR_STANDARDS_CWR",					'cwr' );

/**
 * FCU standards.
 *
 * This tag indicates the directory name where the FCU standards XML files are stored.
 */
define( "kDIR_STANDARDS_FCU",					'fcu' );

/**
 * QTL standards.
 *
 * This tag indicates the directory name where the QTL standards XML files are stored.
 */
define( "kDIR_STANDARDS_QTL",					'qtl' );

/**
 * GR standards.
 *
 * This tag indicates the directory name where the genetic resources standards XML files
 * are stored.
 */
define( "kDIR_STANDARDS_GR",					'gr' );

/*=======================================================================================
 *	XML STANDARDS BASE HEADER															*
 *======================================================================================*/

/**
 * Standards base XML header.
 *
 * This constant holds the default base XML structure for all standards files.
 */
define( "kXML_STANDARDS_BASE",
		'<?xml version="1.0" encoding="UTF-8"?>'
	   .'<METADATA '
	   .'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
	   .'xsi:noNamespaceSchemaLocation="https://gist.githubusercontent.com/milko/f2dcea3c3a94f69bb0bb/raw/715d8fd15a66ad25466c9efc4f767fd31fc9a21a/Dictionary.xsd">'
	   .'</METADATA>' );

?>
