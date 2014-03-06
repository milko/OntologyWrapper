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

/**
 * State mask.
 *
 * This value masks all the state flags.
 */
define( "kFLAG_STATE_MASK",				0x0000000F );

/*=======================================================================================
 *	OBJECT STATE																		*
 *======================================================================================*/

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


?>
