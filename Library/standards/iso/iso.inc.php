<?php

/*=======================================================================================
 *																						*
 *										iso.inc.php										*
 *																						*
 *======================================================================================*/
 
/**
 *	ISO definitions.
 *
 *	This file contains the definitions needed to extract the ISO standards from the brew
 *	iso-codes package.
 *
 *	@package	MyWrapper
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Skofic <m.skofic@cgiar.org>
 *	@version	1.00 14/10/2012
 */

/*=======================================================================================
 *	DIRECTORY PATHS																		*
 *======================================================================================*/

/**
 * ISO-codes main directory path.
 *
 * This defines the path to the <i>share</i> directory that contains all the necessary data.
 *
 * You may customise this.
 */
define( "kISO_CODES_PATH",			'/Library/WebServer/Library/iso-codes-files/share' );

/**
 * ISO-codes locale directory path.
 *
 * This defines the path to the <i>locale</i> directory <i>relative</i> to the
 * {@link kISO_CODES_PATH} path.
 *
 * You may customise this.
 */
define( "kISO_CODES_PATH_LOCALE",			'/locale' );

/**
 * ISO-codes XML directory path.
 *
 * This defines the path to the <i>xml</i> directory <i>relative</i> to the
 * {@link kISO_CODES_PATH} path.
 *
 * You may customise this.
 */
define( "kISO_CODES_PATH_XML",				'/xml/iso-codes' );

/**
 * ISO-codes messages directory path.
 *
 * This defines the path to the <i>LC_MESSAGES</i> directory base name.
 *
 * You may customise this.
 */
define( "kISO_CODES_PATH_MSG",				'/LC_MESSAGES' );

/*=======================================================================================
 *	FILENAMES																			*
 *======================================================================================*/

/**
 * ISO 639 filename.
 *
 * This defines the filename of the ISO 639 files; notice that this does not include the
 * file extension.
 */
define( "kISO_FILE_639",					'iso_639' );

/**
 * ISO 639-3 filename.
 *
 * This defines the filename of the ISO 639-3 files; notice that this does not include the
 * file extension.
 */
define( "kISO_FILE_639_3",					'iso_639_3' );

/**
 * ISO 639-5 filename.
 *
 * This defines the filename of the ISO 639-5 files; notice that this does not include the
 * file extension.
 */
define( "kISO_FILE_639_5",					'iso_639_5' );

/**
 * ISO 3166 filename.
 *
 * This defines the filename of the ISO 3166 files; notice that this does not include the
 * file extension.
 */
define( "kISO_FILE_3166",					'iso_3166' );

/**
 * ISO 3166-2 filename.
 *
 * This defines the filename of the ISO 639-3 files; notice that this does not include the
 * file extension.
 */
define( "kISO_FILE_3166_2",					'iso_3166_2' );

/**
 * ISO 4217 filename.
 *
 * This defines the filename of the ISO 4217 files; notice that this does not include the
 * file extension.
 */
define( "kISO_FILE_4217",					'iso_4217' );

/**
 * ISO 15924 filename.
 *
 * This defines the filename of the ISO 15924 files; notice that this does not include the
 * file extension.
 */
define( "kISO_FILE_15924",					'iso_15924' );

/*=======================================================================================
 *	SESSION OFFSETS																		*
 *======================================================================================*/

/**
 * PO files directory path.
 *
 * This defines the PO files directory path.
 */
define( "kISO_FILE_PO_DIR",					'ISO-PO' );

/**
 * MO files directory path.
 *
 * This defines the MO files directory path.
 */
define( "kISO_FILE_MO_DIR",					'ISO-MO' );

/**
 * Language codes list.
 *
 * This defines the language codes list.
 */
define( "kISO_LANGUAGES",					'ISO-LG' );

/**
 * File names list.
 *
 * This defines the file names list.
 */
define( "kISO_FILES",						'ISO-FL' );

?>
