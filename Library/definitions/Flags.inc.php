<?php

/*=======================================================================================
 *																						*
 *										Flags.inc.php									*
 *																						*
 *======================================================================================*/
 
/**
 *	Status flags.
 *
 *	This file contains the default status flags used to provide status managemnent to
 * classes.
 *
 * These flags are stored in a bitfield property in which the first 31 bits can be used.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 03/05/2009
 *				2.00 23/11/2010
 *				3.00 13/02/2012
 *				4.00 03/09/2012
 */

/*=======================================================================================
 *	DEFAULT VALUES																		*
 *======================================================================================*/

/**
 * Default state.
 *
 * This bitfield value represents the default flags state: all flags <tt>OFF</tt>.
 */
define( "kFLAG_DEFAULT",				0x00000000 );

/*=======================================================================================
 *	DEFAULT MASKS																		*
 *======================================================================================*/

/**
 * Status mask.
 *
 * This bitfield value represents the default flags mask.
 */
define( "kFLAG_DEFAULT_MASK",			0x7FFFFFFF );

/*=======================================================================================
 *	STATUS																				*
 *======================================================================================*/

/**
 * State mask.
 *
 * This value masks all the state flags.
 */
define( "kFLAG_STATE_MASK",				0x0000000F );

/**
 * Initialised.
 *
 * This bitfield value indicates that the object has been initialised, this means that all
 * required data members are present.
 *
 * If this flag is not set, it means that the object lacks required elements, thus it will
 * not work correctly.
 */
define( "kFLAG_STATE_INITED",			0x00000001 );

/**
 * Dirty.
 *
 * This bitfield value indicates that the object has been modified. In general this state is
 * only triggered by modifications to object offsets, or persistent properties; run time
 * members should not affect tjis flag.
 *
 * In general, methods that modify an object offset value will set this flag and methods
 * that freeze the state of the object, such as the constructor or when an object is stored
 * persistently, will reset it.
 *
 * If the flag is not set, this means that the object has not been modified;
 */
define( "kFLAG_STATE_DIRTY",			0x00000002 );

/**
 * Committed.
 *
 * This bitfield value indicates that the object has been either loaded from a persistent
 * container, or that it has been saved to a persistent container.
 *
 * If the flag is off, this means that the object was not instantiated from a persistent
 * container, or that it was not yet inserted into a persistent container.
 */
define( "kFLAG_STATE_COMMITTED",		0x00000004 );

/**
 * Alias.
 *
 * This bitfield value indicates that the current object is an alias, this means that it
 * must feature the {@link kTAG_MASTER} property which references the master object.
 *
 * In general, this flag is used to indicate that the current object is an alias, or to
 * signal that the current non committed object should load its master before being
 * committed.
 */
define( "kFLAG_STATE_ALIAS",			0x00000008 );

/*=======================================================================================
 *	OPTIONS																				*
 *======================================================================================*/

/**
 * Options mask.
 *
 * This value masks all the option flags.
 */
define( "kFLAG_OPT_MASK",				0x000000F0 );

/**
 * Access mask.
 *
 * This value masks all the access flags.
 */
define( "kFLAG_OPT_ACCESS_MASK",		0x00000030 );

/**
 * Insert.
 *
 * This bitfield value represents the insert operation.
 */
define( "kFLAG_OPT_INSERT",				0x00000010 );

/**
 * Update.
 *
 * This bitfield value represents the update operation.
 */
define( "kFLAG_OPT_UPDATE",				0x00000030 );

/**
 * Delete.
 *
 * This bitfield value represents the delete operation.
 */
define( "kFLAG_OPT_DELETE",				0x00000020 );

/**
 * Relate one.
 *
 * This bitfield value enables or disables many to one relationship operations: if the flag
 * is set, many to one relationships must be handled; if set to <tt>FALSE</tt>, many to one
 * relationships should be ignored.
 */
define( "kFLAG_OPT_REL_ONE",			0x00000040 );

/**
 * Relate many.
 *
 * This bitfield value enables or disables one to many relationship operations: if the flag
 * is set, one to many relationships must be handled; if set to <tt>FALSE</tt>, one to many
 * relationships should be ignored.
 */
define( "kFLAG_OPT_REL_MANY",			0x00000080 );

/*=======================================================================================
 *	FORMAT OPTIONS																		*
 *======================================================================================*/

/**
 * Default options.
 *
 * This value represents the default options.
 */
define( "kFLAG_FORMAT_OPT_DEFAULT",		0x00000007 );

/**
 * Exclude dynamic offsets.
 *
 * This value will exclude dynamic offsets.
 */
define( "kFLAG_FORMAT_OPT_DYNAMIC",		0x00000001 );

/**
 * Add tag native identifiers.
 *
 * This value will add tag native identifiers.
 */
define( "kFLAG_FORMAT_OPT_NATIVES",		0x00000002 );

/**
 * Add values.
 *
 * This value will add values.
 */
define( "kFLAG_FORMAT_OPT_VALUES",		0x00000004 );


?>
