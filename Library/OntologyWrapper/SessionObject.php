<?php

/**
 * SessionObject.php
 *
 * This file contains the definition of the {@link SessionObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Wrapper;
use OntologyWrapper\PersistentObject;

/*=======================================================================================
 *																						*
 *								SessionObject.php										*
 *																						*
 *======================================================================================*/

/**
 * Domains.
 *
 * This file contains the domain definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Domains.inc.php" );

/**
 * Session object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing session or
 * transaction objects that can persist in a container and that are constituted by ontology
 * offsets.
 *
 * The main purpose of this class is to implement abstract methods common to all session
 * objects.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 13/02/2014
 */
abstract class SessionObject extends PersistentObject
{
		

/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * Instantiate class.
	 *
	 * Objects derived from this class feature a <tt>MongoId</tt> native identifier, in this
	 * class we convert string identifiers to this type.
	 *
	 * We also assert that the container is provided and is a wrapper.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Assert container.
		//
		if( $theContainer !== NULL )
		{
			//
			// Assert wrapper.
			//
			if( $theContainer instanceof Wrapper )
			{
				//
				// Normalise identifier.
				//
				if( $theIdentifier !== NULL )
				{
					//
					// Convert to MongoId.
					//
					if( (! is_array( $theIdentifier ))
					 && (! ($theIdentifier instanceof \MongoId)) )
					{
						//
						// Convert to string.
						//
						$theIdentifier = (string) $theIdentifier;
				
						//
						// Handle valid identifier.
						//
						if( \MongoId::isValid( $theIdentifier ) )
							$theIdentifier = new \MongoId( $theIdentifier );
				
						//
						// Invalid identifier.
						//
						else
							throw new \Exception(
								"Cannot instantiate object: "
							   ."invalid identifier [$theIdentifier]." );		// !@! ==>
					}
		
				} // Provided identifier.
		
				//
				// Call parent method.
				// Note that we assert the object's existance.
				//
				parent::__construct( $theContainer, $theIdentifier, TRUE );
			
			} // Provided container.
			
			else
				throw new \Exception(
					"Cannot instantiate object: "
				   ."expecting the data wrapper." );							// !@! ==>
		
		} // Provided container.
		
		else
			throw new \Exception(
				"Cannot instantiate object: "
			   ."expecting the container." );									// !@! ==>

	} // Constructor.

	 
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return global identifier</h4>
	 *
	 * In this class we return the string representation of the <tt>MongoId</tt>.
	 *
	 * @access public
	 * @return string				The persistent identifier as a string.
	 *
	 * @see kTAG_NID
	 */
	public function __toString()
	{
		//
		// Check persistent identifier.
		//
		if( \ArrayObject::offsetExists( kTAG_NID ) )
			return (string) \ArrayObject::offsetGet( kTAG_NID );					// ==>
		
		return '';																	// ==>
	
	} // __toString.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	insert																			*
	 *==================================================================================*/

	/**
	 * Insert the object
	 *
	 * We overload this method to cast the native identifier to a string.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param bitfield				$theOptions			Operation options.
	 *
	 * @access public
	 * @return mixed				The object's native identifier or <tt>NULL</tt>.
	 */
	public function insert( $theWrapper = NULL, $theOptions = kFLAG_OPT_REL_ONE )
	{
		//
		// Call parent method.
		//
		$id = parent::insert( $theWrapper, $theOptions );
		
		//
		// Handle MongoId.
		//
		if( $id instanceof \MongoId )
			return (string) $id;													// ==>
		
		return $id;																	// ==>
	
	} // insert.

	 
	/*===================================================================================
	 *	commit																			*
	 *==================================================================================*/

	/**
	 * Commit the object
	 *
	 * We overload this method to cast the native identifier to a string.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param bitfield				$theOptions			Operation options.
	 *
	 * @access public
	 * @return mixed				The object's native identifier.
	 */
	public function commit( $theWrapper = NULL, $theOptions = kFLAG_OPT_REL_ONE )
	{
		//
		// Call parent method.
		//
		$id = parent::commit( $theWrapper, $theOptions );
		
		//
		// Handle MongoId.
		//
		if( $id instanceof \MongoId )
			return (string) $id;													// ==>
		
		return $id;																	// ==>
	
	} // commit.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	session																			*
	 *==================================================================================*/

	/**
	 * Manage session
	 *
	 * This method can be used to manage the session reference, the method accepts the
	 * follo<ing parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: <em>Session or operation</em>:
	 *	  <ul>
	 *		<li><tt>NULL</tt>: <em>Retrieve session</em>:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will return the
	 *				object's value.
	 *			<li><em>The object is committed</em>: The method will return the persistent
	 *				object's value and update the current object.
	 *		  </ul>
	 *		<li><tt>FALSE</tt>: <em>Delete session</em>:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will delete the current
	 *				object's value and return the old value.
	 *			<li><em>The object is committed</em>: The method will delete the persistent
	 *				and current object's values and return the old persistent object's
	 *				value.
	 *		  </ul>
	 *		<li><em>other</em>: <em>Set session</em>:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will set the provided
	 *				value in the current object.
	 *			<li><em>The object is committed</em>: The method will first set the value in
	 *				the persistent object, then set it in the current object and return it.
	 *		  </ul>
	 *	  </ul>
	 *	<li><b>$doObject</b>: <em>Result type</em>: If <tt>TRUE</tt>, the method will
	 *		return the session object, rather than its reference.
	 * </ul>
	 *
	 * The object must have been instantiated with a wrapper.
	 *
	 * The method will return the session or <tt>NULL</tt> if not set.
	 *
	 * @param mixed					$theValue			Session object or reference.
	 * @param boolean				$doObject			TRUE return object.
	 *
	 * @access public
	 * @return mixed				Session object or reference.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_SESSION
	 *
	 * @uses handleReference()
	 */
	public function session( $theValue = NULL, $doObject = FALSE )
	{
		//
		// Check reference.
		//
		if( ($theValue !== NULL)
		 && ($theValue !== FALSE) )
		{
			//
			// Handle object.
			//
			if( $theValue instanceof Session )
				$theValue = $theValue->offsetGet( kTAG_NID );
			
			//
			// Normalise identifier.
			//
			if( ! ($theValue instanceof \MongoId) )
			{
				//
				// Convert to string.
				//
				$theValue = (string) $theValue;
			
				//
				// Handle valid identifier.
				//
				if( \MongoId::isValid( $theValue ) )
					$theValue = new \MongoId( $theValue );
			
				//
				// Invalid identifier.
				//
				else
					throw new \Exception(
						"Unable to set session: "
					   ."invalid identifier in object [$theValue]." );			// !@! ==>
			
			} // Convert to MongoId.
			
			//
			// Check if object exists.
			//
			Session::ResolveObject( $this->mDictionary,
									Session::kSEQ_NAME,
									$theValue,
									TRUE );
	
		} // Checked reference.
		
		return $this->handleReference(
					kTAG_SESSION, 'Session', $theValue, $doObject );				// ==>
	
	} // session.

		

/*=======================================================================================
 *																						*
 *								PUBLIC UPDATE INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	processed																		*
	 *==================================================================================*/

	/**
	 * Manage the processed count
	 *
	 * This method will either retrieve the current processed count from the persistent
	 * object, or increment the count by the provided value.
	 *
	 * If the parameter is <tt>NULL</tt>, the method will retrieve the count; if not, the
	 * method will cast the parameter to an integer and update the count of the persistent
	 * object by that value and return <tt>TRUE</tt>.
	 *
	 * The current object's count will not be updated, because of this, the count should not
	 * be counted on :-)
	 *
	 * @param mixed					$theValue			Increment delta or <tt>NULL</tt>.
	 *
	 * @access public
	 *
	 * @see kTAG_PROCESSED
	 *
	 * @uses resolvePersistent()
	 * @uses updateCount()
	 */
	public function processed( $theValue = NULL )
	{
		//
		// Retrieve count.
		//
		if( $theValue === NULL )
			return
				$this->resolvePersistent( TRUE )
					->offsetGet( kTAG_PROCESSED );									// ==>
		
		//
		// Update count.
		//
		$this->updateCount( kTAG_PROCESSED, (int) $theValue );
		
		return TRUE;																// ==>
	
	} // processed.

	 
	/*===================================================================================
	 *	validated																		*
	 *==================================================================================*/

	/**
	 * Manage the validated count
	 *
	 * This method will either retrieve the current validated count from the persistent
	 * object, or increment the count by the provided value.
	 *
	 * If the parameter is <tt>NULL</tt>, the method will retrieve the count; if not, the
	 * method will cast the parameter to an integer and update the count of the persistent
	 * object by that value and return <tt>TRUE</tt>.
	 *
	 * The current object's count will not be updated, because of this, the count should not
	 * be counted on :-)
	 *
	 * @param mixed					$theValue			Increment delta or <tt>NULL</tt>.
	 *
	 * @access public
	 *
	 * @see kTAG_VALIDATED
	 *
	 * @uses resolvePersistent()
	 * @uses updateCount()
	 */
	public function validated( $theValue = NULL )
	{
		//
		// Retrieve count.
		//
		if( $theValue === NULL )
			return
				$this->resolvePersistent( TRUE )
					->offsetGet( kTAG_VALIDATED );									// ==>
		
		//
		// Update count.
		//
		$this->updateCount( kTAG_VALIDATED, (int) $theValue );
		
		return TRUE;																// ==>
	
	} // validated.

	 
	/*===================================================================================
	 *	rejected																		*
	 *==================================================================================*/

	/**
	 * Manage the rejected count
	 *
	 * This method will either retrieve the current rejected count from the persistent
	 * object, or increment the count by the provided value.
	 *
	 * If the parameter is <tt>NULL</tt>, the method will retrieve the count; if not, the
	 * method will cast the parameter to an integer and update the count of the persistent
	 * object by that value and return <tt>TRUE</tt>.
	 *
	 * The current object's count will not be updated, because of this, the count should not
	 * be counted on :-)
	 *
	 * @param mixed					$theValue			Increment delta or <tt>NULL</tt>.
	 *
	 * @access public
	 *
	 * @see kTAG_REJECTED
	 *
	 * @uses resolvePersistent()
	 * @uses updateCount()
	 */
	public function rejected( $theValue = NULL )
	{
		//
		// Retrieve count.
		//
		if( $theValue === NULL )
			return
				$this->resolvePersistent( TRUE )
					->offsetGet( kTAG_REJECTED );									// ==>
		
		//
		// Update count.
		//
		$this->updateCount( kTAG_REJECTED, (int) $theValue );
		
		return TRUE;																// ==>
	
	} // rejected.

	 
	/*===================================================================================
	 *	skipped																			*
	 *==================================================================================*/

	/**
	 * Manage the skipped count
	 *
	 * This method will either retrieve the current skipped count from the persistent
	 * object, or increment the count by the provided value.
	 *
	 * If the parameter is <tt>NULL</tt>, the method will retrieve the count; if not, the
	 * method will cast the parameter to an integer and update the count of the persistent
	 * object by that value and return <tt>TRUE</tt>.
	 *
	 * The current object's count will not be updated, because of this, the count should not
	 * be counted on :-)
	 *
	 * @param mixed					$theValue			Increment delta or <tt>NULL</tt>.
	 *
	 * @access public
	 *
	 * @see kTAG_SKIPPED
	 *
	 * @uses resolvePersistent()
	 * @uses updateCount()
	 */
	public function skipped( $theValue = NULL )
	{
		//
		// Retrieve count.
		//
		if( $theValue === NULL )
			return
				$this->resolvePersistent( TRUE )
					->offsetGet( kTAG_SKIPPED );									// ==>
		
		//
		// Update count.
		//
		$this->updateCount( kTAG_SKIPPED, (int) $theValue );
		
		return TRUE;																// ==>
	
	} // skipped.

	 
	/*===================================================================================
	 *	counters																		*
	 *==================================================================================*/

	/**
	 * Retrieve operation counters
	 *
	 * This method can be used to retrieve the current operations counters, the method will
	 * return an array with the following keys:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_SKIPPED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_REJECTED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_VALIDATED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_PROCESSED}</tt>: Processed elements.
	 * </ul>
	 *
	 * If the object is committed, the method will fetch the data from the persistent object
	 * and update the current object.
	 *
	 * @access public
	 * @return array				Operation counters.
	 *
	 * @uses session()
	 */
	public function counters()
	{
		//
		// Handle committed object.
		//
		if( $this->committed() )
		{
			//
			// Update session.
			//
			$object = $this->resolvePersistent( TRUE );
			
			//
			// Set counters.
			//
			$counters = array( kTAG_SKIPPED, kTAG_REJECTED,
							   kTAG_VALIDATED, kTAG_PROCESSED );
			foreach( $counters as $counter )
				$this->offsetSet( $counter, $object->offsetGet( $counter ) );
		
		} // Object is committed.
		
		return array(
				kTAG_SKIPPED => $this->offsetGet( kTAG_SKIPPED ),
				kTAG_REJECTED => $this->offsetGet( kTAG_REJECTED ),
				kTAG_VALIDATED => $this->offsetGet( kTAG_VALIDATED ),
				kTAG_PROCESSED => $this->offsetGet( kTAG_PROCESSED ) );				// ==>
	
	} // counters.

	 

/*=======================================================================================
 *																						*
 *								STATIC CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveDatabase																	*
	 *==================================================================================*/

	/**
	 * Resolve the database
	 *
	 * In this class we use the {@link EntityObject} method.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param boolean				$doAssert			Raise exception if unable.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return DatabaseObject		Database or <tt>NULL</tt>.
	 */
	static function ResolveDatabase( Wrapper $theWrapper, $doAssert = TRUE, $doOpen = TRUE )
	{
		return User::ResolveDatabase( $theWrapper, $doAssert, $doOpen );			// ==>
	
	} // ResolveDatabase.

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	UnmanagedOffsets																*
	 *==================================================================================*/

	/**
	 * Return unmanaged offsets
	 *
	 * In this class we exclude all offsets that are supposed to be set externally, this
	 * includes:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_SESSION}</tt>: Session reference.
	 *	<li><tt>{@link kTAG_PROCESSED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_VALIDATED}</tt>: Validated elements.
	 *	<li><tt>{@link kTAG_REJECTED}</tt>: Rejected elements.
	 *	<li><tt>{@link kTAG_SKIPPED}</tt>: Skipped elements.
	 * </ul>
	 *
	 * @static
	 * @return array				List of unmanaged offsets.
	 *
	 * @see kTAG_SESSION kTAG_PROCESSED kTAG_VALIDATED kTAG_REJECTED kTAG_SKIPPED
	 */
	static function UnmanagedOffsets()
	{
		return array_merge(
			parent::UnmanagedOffsets(),
			array( kTAG_SESSION,
				   kTAG_PROCESSED, kTAG_VALIDATED, kTAG_REJECTED, kTAG_SKIPPED ) );	// ==>
	
	} // UnmanagedOffsets.

	

/*=======================================================================================
 *																						*
 *								STATIC EXPORT INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	XMLRootElement																	*
	 *==================================================================================*/

	/**
	 * Return XML root element
	 *
	 * In this class we return the <tt>UNITS</tt> root element.
	 *
	 * @static
	 * @return SimpleXMLElement		XML export root element.
	 */
	static function XMLRootElement()
	{
		return new \SimpleXMLElement(
						str_replace(
							'@@@', kIO_XML_UNITS, kXML_STANDARDS_BASE ) );			// ==>
	
	} // XMLRootElement.

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before setting it
	 *
	 * We overload this method to convert objects into their native identifiers and to
	 * manage other offsets according to the commit state of the object: if the object is
	 * not committed, the method will only manage the offsets in the current object; if the
	 * object is committed, some offsets will be locked, while other offsets will be set
	 * both in the current and persistent objects.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 */
	protected function preOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Intercept object references.
		//
		if( $theValue instanceof PersistentObject )
			$theValue = $theValue->reference();
		
		return parent::preOffsetSet( $theOffset, $theValue );						// ==>
	
	} // preOffsetSet.

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	xmlUnitElement																	*
	 *==================================================================================*/

	/**
	 * Return XML unit element
	 *
	 * In this class we return the <tt>UNIT</tt> element.
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	protected function xmlUnitElement( \SimpleXMLElement $theRoot )
	{
		return $theRoot->addChild( kIO_XML_TRANS_UNITS );							// ==>
	
	} // xmlUnitElement.

		

/*=======================================================================================
 *																						*
 *							PROTECTED PERSISTENCE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateObject																	*
	 *==================================================================================*/

	/**
	 * Update the object
	 *
	 * We overload this method to prevent updating an existing session, if called, this
	 * method will raise an exception.
	 *
	 * @param CollectionObject		$theCollection		Data collection.
	 * @param bitfield				$theOptions			Operation options.
	 *
	 * @access protected
	 * @return mixed				The object's native identifier.
	 *
	 * @throws Exception
	 */
	protected function updateObject( CollectionObject $theCollection, $theOptions )
	{
		throw new \Exception(
			"Cannot update object: "
		   ."sessions can only be inserted." );									// !@! ==>
	
	} // updateObject.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommitPrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before commit
	 *
	 * In this class we initialise the operation counters.
	 *
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @see kTAG_PROCESSED kTAG_VALIDATED kTAG_REJECTED kTAG_SKIPPED
	 *
	 * @uses start()
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Initialise counters.
		//
		$this->offsetSet( kTAG_SKIPPED, 0 );
		$this->offsetSet( kTAG_REJECTED, 0 );
		$this->offsetSet( kTAG_VALIDATED, 0 );
		$this->offsetSet( kTAG_PROCESSED, 0 );
		
		//
		// Initialise transaction status.
		//
		$this->status( kTYPE_STATUS_EXECUTING );
		
		//
		// Initialise start.
		//
		$this->start( TRUE );
		
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
		
	} // preCommitPrepare.

		

/*=======================================================================================
 *																						*
 *							PROTECTED MEMBER MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	handleOffset																	*
	 *==================================================================================*/

	/**
	 * Manage offset
	 *
	 * This method can be used to manage object offset properties, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffset</b>: <em>Property offset</em>, the offset of the property.
	 *	<li><b>$theValue</b>: <em>Value or operation</em>, depending on the type, this
	 *		parameter is either the new value to be set, or the requested operation:
	 *	  <ul>
	 *		<li><tt>NULL</tt>: <em>Retrieve value</em>, the current value will be returned,
	 *			the source of the value depends on the following:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will return the value
	 *				stored in the current object.
	 *			<li><em>The object is committed</em>: The method will read the current
	 *				object from the database and use the value stored in it.
	 *		  </ul>
	 *		<li><tt>FALSE</tt>: <em>Delete value</em>, the value will be deleted:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will remove the value
	 *				from the current object and return the old value.
	 *			<li><em>The object is committed</em>: The method will remove the value from
	 *				both the current and persistent objects and return the old value stored
	 *				in the persistent object.
	 *		  </ul>
	 *		<li><em>other</em>: <em>Set value</em>, the provided value will be set:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will set the provided
	 *				value in the current object and return the value.
	 *			<li><em>The object is committed</em>: The method will set the value in both
	 *				the current and persistent objects and return the value.
	 *		  </ul>
	 *	  </ul>
	 * </ul>
	 *
	 * @param string				$theOffset			Value offset.
	 * @param mixed					$theValue			Object or operation.
	 *
	 * @access protected
	 * @return mixed				Object or reference.
	 *
	 * @uses committed()
	 * @uses resolvePersistent()
	 */
	protected function handleOffset( $theOffset, $theValue = NULL )
	{
		//
		// Handle committed current object.
		//
		if( ! $this->committed() )
		{
			//
			// Return current value.
			//
			if( $theValue === NULL )
				return $this->offsetGet( $theOffset );								// ==>
			
			//
			// Delete value.
			//
			if( $theValue === FALSE )
			{
				//
				// Get current value.
				//
				$save = $this->offsetGet( $theOffset );
				
				//
				// Delete value.
				//
				$this->offsetUnset( $theOffset );
				
				return $save;														// ==>
			
			} // Delete value.
			
			//
			// Set value.
			//
			$this->offsetSet( $theOffset, $theValue );
			
			return $theValue;														// ==>
			
		} // Current object is not committed.
	
		//
		// Get persistent value.
		//
		$save
			= $this
				->resolvePersistent( TRUE )
					->offsetGet( $theOffset );
		
		//
		// Delete value.
		//
		if( $theValue === FALSE )
		{
			//
			// Remove from current object.
			//
			$this->offsetUnset( $theOffset );
			
			//
			// Remove from persistent object.
			//
			static::ResolveCollection(
				static::ResolveDatabase( $this->mDictionary, TRUE ) )
					->replaceOffsets(
						$this->offsetGet( kTAG_NID ),
						array( $theOffset => NULL ) );
			
			return $save;															// ==>
		
		} // Delete value.
		
		//
		// Return value.
		//
		if( $theValue === NULL )
		{
			//
			// Update current object.
			//
			$this->offsetSet( $theOffset, $save );
		
			return $save;															// ==>
		
		} // Return value.
		
		//
		// Set in current object.
		//
		$this->offsetSet( $theOffset, $theValue );
		
		//
		// Set in persistent object.
		//
		static::ResolveCollection(
			static::ResolveDatabase( $this->mDictionary, TRUE ) )
				->replaceOffsets(
					$this->offsetGet( kTAG_NID ),
					array( $theOffset => $theValue ) );
		
		return $theValue;															// ==>
	
	} // handleOffset.

	 
	/*===================================================================================
	 *	handleReference																	*
	 *==================================================================================*/

	/**
	 * Manage reference
	 *
	 * This method can be used to manage object references, the method expects the following
	 * parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffset</b>: <em>Reference offset</tt>, the offset of the reference.
	 *	<li><b>$theValue</b>: <em>Value or operation</em>, depending on the type, this
	 *		parameter is either the new reference to be set, or the requested operation:
	 *	  <ul>
	 *		<li><tt>NULL</tt>: <em>Retrieve value</em>, the current value will be returned,
	 *			the source of the value depends on the following:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will return the value
	 *				stored in the current object.
	 *			<li><em>The object is committed</em>: The method will read the current
	 *				object from the database and use its value.
	 *		  </ul>
	 *		<li><tt>FALSE</tt>: <em>Delete value</em>, the value will be deleted:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will remove the value
	 *				from the current object and return the old value.
	 *			<li><em>The object is committed</em>: The method will remove the value from
	 *				both the current and persistent objects and return the old value stored
	 *				in the persistent object.
	 *		  </ul>
	 *		<li><em>other</em>: <em>Set value</em>, the provided value will be set:
	 *		  <ul>
	 *			<li><em>The object is not committed</em>: The method will set the provided
	 *				value in the current object.
	 *			<li><em>The object is committed</em>: The method will first set the value in the
	 *				persistent object, then set it in the current object.
	 *		  </ul>
	 *	  </ul>
	 *	<li><b>$theClass</b>: <em>Object class</em>, this parameter holds the class of the
	 *		referenced object.
	 *	<li><b>$doObject</b>: <em>Return reference or object</tt>, if this parameter is
	 *		<tt>TRUE</tt>, the method will return the actual object, if not, it will
	 *		return the object reference.
	 * </ul>
	 *
	 * When setting the value, the reference will be checked; when retrieving the value,
	 * if the value is not set, the method will return <tt>NULL</tt>.
	 *
	 * @param string				$theOffset			Value offset.
	 * @param string				$theClass			Object class.
	 * @param mixed					$theValue			Object or operation.
	 * @param boolean				$doObject			TRUE return object.
	 *
	 * @access protected
	 * @return mixed				Object or reference.
	 *
	 * @uses committed()
	 * @uses resolvePersistent()
	 */
	protected function handleReference( $theOffset, $theClass, $theValue = NULL,
															   $doObject = FALSE )
	{
		//
		// Handle committed current object.
		//
		if( ! $this->committed() )
		{
			//
			// Get current value.
			//
			$save = $this->offsetGet( $theOffset );
			
			//
			// Delete value.
			//
			if( $theValue === FALSE )
				$this->offsetUnset( $theOffset );
			
			//
			// Set value.
			//
			elseif( $theValue !== NULL )
				$this->offsetSet( $theOffset, $theValue );
			
			//
			// Return object.
			//
			if( $doObject )
			{
				//
				// Check if there.
				//
				if( $save === NULL )
					return NULL;													// ==>
	
				//
				// Return object.
				//
				return
					static::ResolveObject(
						$this->mDictionary,
						$theClass::kSEQ_NAME,
						$save,
						TRUE );														// ==>
			
			} // Return object.
			
			return $save;															// ==>
			
		} // Current object is not committed.
	
		//
		// Get persistent value.
		//
		$save
			= $this
				->resolvePersistent( TRUE )
					->offsetGet( $theOffset );
		
		//
		// Delete value.
		//
		if( $theValue === FALSE )
		{
			//
			// Remove from current object.
			//
			$this->offsetUnset( $theOffset );
			
			//
			// Remove from persistent object.
			//
			static::ResolveCollection(
				static::ResolveDatabase( $this->mDictionary, TRUE ) )
					->replaceOffsets(
						$this->offsetGet( kTAG_NID ),
						array( $theOffset => NULL ) );
		
		} // Delete value.
		
		//
		// Set value.
		//
		elseif( $theValue !== NULL )
		{
			//
			// Set in current object.
			//
			$this->offsetSet( $theOffset, $theValue );
			
			//
			// Set in persistent object.
			//
			static::ResolveCollection(
				static::ResolveDatabase( $this->mDictionary, TRUE ) )
					->replaceOffsets(
						$this->offsetGet( kTAG_NID ),
						array( $theOffset => $theValue ) );
		
		} // Set value.
		
		//
		// Update current object's value.
		//
		else
			$this->offsetSet( $theOffset, $save );

		//
		// Return reference.
		//
		if( ! $doObject )
			return $save;															// ==>

		//
		// Check if there.
		//
		if( $save === NULL )
			return NULL;															// ==>
			
		//
		// Return object.
		//
		return
			static::ResolveObject(
				$this->mDictionary,
				$theClass::kSEQ_NAME,
				$save,
				TRUE );																// ==>
	
	} // handleReference.

	 
	/*===================================================================================
	 *	updateCount																		*
	 *==================================================================================*/

	/**
	 * Update a counter
	 *
	 * This method will update the count of the provided offset by the provided delta of the
	 * persistent object.
	 *
	 * @param string				$theCounter			Counter offset.
	 * @param int					$theCount			Increment delta.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kTAG_PROCESSED kTAG_VALIDATED kTAG_REJECTED kTAG_SKIPPED
	 */
	protected function updateCount( $theCounter, $theCount )
	{
		//
		// Check counter.
		//
		switch( $theCounter )
		{
			case kTAG_PROCESSED:
			case kTAG_VALIDATED:
			case kTAG_REJECTED:
			case kTAG_SKIPPED:
				break;
		
			default:
				throw new \Exception(
					"Cannot increment counter: "
				   ."invalid counter reference [$theCounter]." );				// !@! ==>
	
		} // Valid counter.
		
		//
		// Check identifier.
		//
		if( ! $this->offsetExists( kTAG_NID ) )
			throw new \Exception(
				"Cannot increment counter: "
			   ."missing object identifier." );									// !@! ==>
		
		//
		// Update count.
		//
		static::ResolveCollection(
			static::ResolveDatabase( $this->mDictionary, TRUE ) )
				->updateReferenceCount(
					array( kTAG_NID => $this->offsetGet( kTAG_NID ) ),
					array( $theCounter =>  (int) $theCount ) );
	
	} // updateCount.

	 

} // class SessionObject.


?>
