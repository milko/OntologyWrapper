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
 *	STANDARDS SUB-FOLDER NAMES															*
 *======================================================================================*/

/**
 * Default standards.
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
	   .'xsi:noNamespaceSchemaLocation="http://resources.grinfo.net/Schema/Dictionary.xsd">'
	   .'</METADATA>' );

?>
