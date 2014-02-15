<?php

/**
 * Status.php
 *
 * This file contains the definition of the {@link Status} trait.
 */

namespace OntologyWrapper\traits;

/*=======================================================================================
 *																						*
 *										Status.php										*
 *																						*
 *======================================================================================*/

/**
 * Flags.
 *
 * This file contains the default flag definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Flags.inc.php" );

/**
 * Status trait
 *
 * The main purpose of this trait is to add status management to classes, this is done by
 * adding a bitfield {@link $mStatus} data member and a set of methods that handle this
 * bitfield.
 *
 * This trait defines the common methods for managing the bitfield data member and handles
 * the following flags:
 *
 * <ul>
 *	<li><tt>{@link kFLAG_STATE_INITED}</tt>: This flag indicates that the object is ready
 *		to be used and is set by the {@link isInited()} method; if the flag is not set,
 *		it means that the object is not functional.
 *	<li><tt>{@link kFLAG_STATE_DIRTY}</tt>: This flag is set whenever the object's offsets
 *		are modified and it can be managed by the {@link isDirty()} method. For persistent
 *		objects it is an indication that the object should be updated; if the flag is not
 *		set, it means that the object persistent properties are in the same state as when it
 *		was loaded from a persistent store.
 *	<li><tt>{@link kFLAG_STATE_COMMITTED}</tt>: This flag is set whenever the object is
 *		either loaded from or stored in a persistent store and can be managed by the
 *		{@link isCommitted()} method. It is an indication that the object is persistent; if
 *		the flag is not set, it means that the object was not loaded from or stored in a
 *		persistent store.
 *	<li><tt>{@link kFLAG_STATE_ENCODED}</tt>: This flag indicates an encoded state and can
 *		be managed by the {@link isEncoded()} method, which means that some object offsets
 *		have been encoded and need to be decoded before the object is saved in a persistent
 *		store or effectively used. This state is often associated to the network
 *		transmission of objects: some data types must be converted prior to be sent over the
 *		network; if the flag is not set, it means that the object can be saved in a
 *		persistent store or serialised for being transmitted on the network.
 * </ul>
 *
 * All the flag accessor methods are protected, since they provide access to the internal
 * workings of the object, if you need to provide status information you should do so by
 * using these methods in a public interface.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 23/01/2014
 */
trait Status
{
	/**
	 * Status flags.
	 *
	 * This data member holds the <i>status flags</i> in a bitfield value 
	 *
	 * @var int
	 */
	private $mStatus = kFLAG_DEFAULT;

		

/*=======================================================================================
 *																						*
 *								PROTECTED STATUS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	statusReset																		*
	 *==================================================================================*/

	/**
	 * Reset status
	 *
	 * This method can be used to reset the status, it will set it to {@link kFLAG_DEFAULT}.
	 *
	 * This is generally at the end of the constructor after having modified offsets to
	 * set the status to an idle state.
	 *
	 * @access protected
	 *
	 * @see kFLAG_DEFAULT
	 */
	protected function statusReset()					{	$this->mStatus = kFLAG_DEFAULT;	}

		

/*=======================================================================================
 *																						*
 *							PROTECTED STATUS MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isInited																		*
	 *==================================================================================*/

	/**
	 * Manage inited status
	 *
	 * This method can be used to get or set the object's inited state.
	 *
	 * An object becomes inited when it has all the required elements necessary for it to be
	 * correctly used or persistently stored. Such a state indicates that at least the
	 * minimum required information was initialised in the object.
	 *
	 * The counterpart state indicates that the object still lacks the necessary elements to
	 * successfully operate the object.
	 *
	 * This method operates by setting or clearing the {@link kFLAG_STATE_INITED} flag.
	 *
	 * The method features a single parameter:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: The method will return the object's inited state.
	 *	<li><tt>TRUE</tt>: The method will set the object's inited state.
	 *	<li><tt>FALSE</tt>: The method will reset the object's inited state.
	 * </ul>
	 *
	 * In all cases the method will return the state <i>after</i> it was eventually
	 * modified.
	 *
	 * @param mixed					$theState			<tt>TRUE</tt>, <tt>FALSE</tt> or
	 *													<tt>NULL</tt>.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> inited, <tt>FALSE</tt> idle.
	 *
	 * @see kFLAG_STATE_INITED
	 *
	 * @uses $this->manageBitField()
	 */
	protected function isInited( $theState = NULL )
	{
		return $this->manageBitField( $this->mStatus,
									  kFLAG_STATE_INITED,
									  $theState );									// ==>
	
	} // isInited.

	 
	/*===================================================================================
	 *	isDirty																			*
	 *==================================================================================*/

	/**
	 * Manage dirty status
	 *
	 * This method can be used to get or set the object's dirty state.
	 *
	 * A dirty object is one that was modified since the last time this state was probed. In
	 * general, this state should be set whenever the persistent properties of the object
	 * are modified.
	 *
	 * In this class we automatically set this state when setting or unsetting offsets.
	 *
	 * The method features a single parameter:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: The method will return the object's dirty state.
	 *	<li><tt>TRUE</tt>: The method will set the object's dirty state.
	 *	<li><tt>FALSE</tt>: The method will reset the object's dirty state.
	 * </ul>
	 *
	 * In all cases the method will return the state <i>after</i> it was eventually
	 * modified.
	 *
	 * @param mixed					$theState			<tt>TRUE</tt>, <tt>FALSE</tt> or
	 *													<tt>NULL</tt>.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> dirty, <tt>FALSE</tt> clean.
	 *
	 * @see kFLAG_STATE_DIRTY
	 *
	 * @uses $this->manageBitField()
	 */
	protected function isDirty( $theState = NULL )
	{
		return $this->manageBitField( $this->mStatus,
									  kFLAG_STATE_DIRTY,
									  $theState );									// ==>
	
	} // isDirty.

	 
	/*===================================================================================
	 *	isCommitted																		*
	 *==================================================================================*/

	/**
	 * Manage committed status
	 *
	 * This method can be used to get or set the object's committed state.
	 *
	 * A committed object is one that has either been loaded from a container or committed
	 * to a container, this state can be used in conjunction with the
	 * {@link kFLAG_STATE_DIRTY} flag to determine whether an object needs to be committed.
	 *
	 * The method features a single parameter:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: The method will return the object's committed state.
	 *	<li><tt>TRUE</tt>: The method will set the object's committed state.
	 *	<li><tt>FALSE</tt>: The method will reset the object's committed state.
	 * </ul>
	 *
	 * In all cases the method will return the state <i>after</i> it was eventually
	 * modified.
	 *
	 * @param mixed					$theState			<tt>TRUE</tt>, <tt>FALSE</tt> or
	 *													<tt>NULL</tt>.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> committed, <tt>FALSE</tt> uncommitted.
	 *
	 * @see kFLAG_STATE_COMMITTED
	 *
	 * @uses $this->manageBitField()
	 */
	protected function isCommitted( $theState = NULL )
	{
		return $this->manageBitField( $this->mStatus,
									  kFLAG_STATE_COMMITTED,
									  $theState );									// ==>
	
	} // isCommitted.

	 
	/*===================================================================================
	 *	isEncoded																		*
	 *==================================================================================*/

	/**
	 * Manage encoded status
	 *
	 * This method can be used to get or set the object's encoded state.
	 *
	 * This flag determines whether the object should take care of serialising custom data
	 * types before the object is transmitted over the network.
	 *
	 * The method features a single parameter:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: The method will return the object's encoded state.
	 *	<li><tt>TRUE</tt>: The method will set the object's encoded state.
	 *	<li><tt>FALSE</tt>: The method will reset the object's encoded state.
	 * </ul>
	 *
	 * In all cases the method will return the state <i>after</i> it was eventually
	 * modified.
	 *
	 * @param mixed					$theState			<tt>TRUE</tt>, <tt>FALSE</tt> or
	 *													<tt>NULL</tt>.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> supports encoding, <tt>FALSE</tt> does not
	 *								support encoding.
	 *
	 * @see kFLAG_STATE_ENCODED
	 *
	 * @uses $this->manageBitField()
	 */
	protected function isEncoded( $theState = NULL )
	{
		return $this->manageBitField( $this->mStatus,
									  kFLAG_STATE_ENCODED,
									  $theState );									// ==>
	
	} // isEncoded.

		

/*=======================================================================================
 *																						*
 *							PROTECTED BITFIELD MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	manageBitField																	*
	 *==================================================================================*/

	/**
	 * Manage a bit-field property
	 *
	 * This method can be used to manage a bitfield property, it accepts the following
	 * parameters:
	 *
	 * <ul>
	 *	<li><tt>&$theField</tt>: Reference to the bit-field property.
	 *	<li><tt>$theMask</tt>: Bit-field mask.
	 *	<li><tt>$theState</tt>: State or operator:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the masked bitfield.
	 *		<li><tt>FALSE</tt>: Turn <i>off</i> the masked bits.
	 *		<li><i>other</i>: Turn <i>on</i> the masked bits.
	 *	 </ul>
	 * </ul>
	 *
	 * In all cases the method will return the status <i>after</i> it was eventually
	 * modified.
	 *
	 * @param reference				$theField			Bit-field reference.
	 * @param bitfield				$theMask			Bit-field mask.
	 * @param mixed					$theState			Value or operator.
	 *
	 * @access protected
	 * @return bitfield				Current masked status.
	 *
	 * @see kFLAG_DEFAULT_MASK
	 */
	protected function manageBitField( &$theField, $theMask, $theState = NULL )
	{
		//
		// Normalise mask (mask sign bit).
		//
		$theMask &= kFLAG_DEFAULT_MASK;
		
		//
		// Modify status.
		//
		if( $theState !== NULL )
		{
			//
			// Set mask.
			//
			if( (boolean) $theState )
				$theField |= $theMask;
			
			//
			// Reset mask.
			//
			else
				$theField &= (~ $theMask);
		}
		
		return $theField & $theMask;												// ==>
	
	} // manageBitField.

	 

} // trait Status.


?>
