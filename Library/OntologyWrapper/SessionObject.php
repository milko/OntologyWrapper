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
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @see kTAG_TAG kTAG_TERM
	 *
	 * @uses isInited()
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
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
					   ."invalid identifier [$theIdentifier]." );				// !@! ==>
			}
		
		} // Provided identifier.
		
		//
		// Call parent method.
		// Note that we assert the object's existance.
		//
		parent::__construct( $theContainer, $theIdentifier, TRUE );

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
 *							PUBLIC OBJECT REFERENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getSession																		*
	 *==================================================================================*/

	/**
	 * Get referenced session
	 *
	 * This method will return the referenced session object; if none is set, the method
	 * will return <tt>NULL</tt>, or raise an exception if the second parameter is
	 * <tt>TRUE</tt>.
	 *
	 * The first parameter is the wrapper in which the current object is, or will be,
	 * stored: if the current object has the {@link dictionary()}, this parameter may be
	 * omitted; if the wrapper cannot be resolved, the method will raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param boolean				$doAssert			Raise exception if not matched.
	 *
	 * @access public
	 * @return PersistentObject		Referenced user or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function getSession( $theWrapper = NULL, $doAssert = TRUE )
	{
		//
		// Check session.
		//
		if( $this->offsetExists( kTAG_SESSION ) )
		{
			//
			// Resolve wrapper.
			//
			$this->resolveWrapper( $theWrapper );
		
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $theWrapper, TRUE ) );
			
			//
			// Set criteria.
			//
			$criteria = array( kTAG_NID => $this->offsetGet( kTAG_SESSION ) );
			
			//
			// Locate object.
			//
			$object = $collection->matchOne( $criteria );
			if( $doAssert
			 && ($object === NULL) )
				throw new \Exception(
					"Unable to get session: "
				   ."referenced object not matched." );							// !@! ==>
			
			return $object;															// ==>
		
		} // Has session.
		
		return NULL;																// ==>
	
	} // getSession.

		

/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateProcessed																	*
	 *==================================================================================*/

	/**
	 * Update the processed count
	 *
	 * This method will update the processed count by the provided delta.
	 *
	 * The method will first update the object in the database, then update the counter in
	 * the current object and return the updated value relative to the contents of the
	 * current object. Because of this, the count should not be counted on :-)
	 *
	 * @param int					$theCount			Increment delta.
	 *
	 * @access public
	 * @return int					Updated count relative to current object.
	 *
	 * @see kTAG_PROCESSED
	 */
	public function updateProcessed( $theCount = 1 )
	{
		return $this->updateCount( kTAG_PROCESSED, $theCount );						// ==>
	
	} // updateProcessed.

	 
	/*===================================================================================
	 *	updateValidated																	*
	 *==================================================================================*/

	/**
	 * Update the validated count
	 *
	 * This method will update the validated count by the provided delta.
	 *
	 * The method will first update the object in the database, then update the counter in
	 * the current object and return the updated value relative to the contents of the
	 * current object. Because of this, the count should not be counted on :-)
	 *
	 * @param int					$theCount			Increment delta.
	 *
	 * @access public
	 * @return int					Updated count relative to current object.
	 *
	 * @see kTAG_VALIDATED
	 */
	public function updateValidated( $theCount = 1 )
	{
		return $this->updateCount( kTAG_VALIDATED, $theCount );						// ==>
	
	} // updateValidated.

	 
	/*===================================================================================
	 *	updateRejected																	*
	 *==================================================================================*/

	/**
	 * Update the rejected count
	 *
	 * This method will update the rejected count by the provided delta.
	 *
	 * The method will first update the object in the database, then update the counter in
	 * the current object and return the updated value relative to the contents of the
	 * current object. Because of this, the count should not be counted on :-)
	 *
	 * @param int					$theCount			Increment delta.
	 *
	 * @access public
	 * @return int					Updated count relative to current object.
	 *
	 * @see kTAG_REJECTED
	 */
	public function updateRejected( $theCount = 1 )
	{
		return $this->updateCount( kTAG_REJECTED, $theCount );						// ==>
	
	} // updateRejected.

	 
	/*===================================================================================
	 *	updateSkipped																	*
	 *==================================================================================*/

	/**
	 * Update the skipped count
	 *
	 * This method will update the skipped count by the provided delta.
	 *
	 * The method will first update the object in the database, then update the counter in
	 * the current object and return the updated value relative to the contents of the
	 * current object. Because of this, the count should not be counted on :-)
	 *
	 * @param int					$theCount			Increment delta.
	 *
	 * @access public
	 * @return int					Updated count relative to current object.
	 *
	 * @see kTAG_SKIPPED
	 */
	public function updateSkipped( $theCount = 1 )
	{
		return $this->updateCount( kTAG_SKIPPED, $theCount );						// ==>
	
	} // updateSkipped.

		

/*=======================================================================================
 *																						*
 *								STATIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	SetSession																		*
	 *==================================================================================*/

	/**
	 * Set the session
	 *
	 * This method can be used to set the session reference of the object identified by
	 * the provided identifier of the calling class.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param mixed					$theSession			Session reference.
	 *
	 * @static
	 */
	static function SetSession( Wrapper $theWrapper, $theIdentifier, $theSession )
	{
		//
		// Normalise session reference.
		//
		if( ! ($theSession instanceof \MongoId) )
		{
			//
			// Convert to string.
			//
			$theSession = (string) $theSession;
			
			//
			// Handle valid identifier.
			//
			if( \MongoId::isValid( $theSession ) )
				$theSession = new \MongoId( $theSession );
			
			//
			// Invalid identifier.
			//
			else
				throw new \Exception(
					"Cannot set session: "
				   ."invalid session reference [$theSession]." );				// !@! ==>
		}
		
		//
		// Normalise identifier.
		//
		if( ! ($theIdentifier instanceof \MongoId) )
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
					"Cannot set session: "
				   ."invalid identifier [$theIdentifier]." );				// !@! ==>
		}
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) );
	
		//
		// Set property.
		//
		$collection->replaceOffsets(
			$theIdentifier,								// Object ID.
			array( kTAG_SESSION => $theSession ) );		// Modifications.
	
	} // SetSession.

		
	/*===================================================================================
	 *	UpdateCounter																	*
	 *==================================================================================*/

	/**
	 * Update a counter
	 *
	 * This method can be used to increment or decrement one of the following counters:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_PROCESSED}</tt>: Processed count.
	 *	<li><tt>{@link kTAG_VALIDATED}</tt>: Processed count.
	 *	<li><tt>{@link kTAG_REJECTED}</tt>: Processed count.
	 *	<li><tt>{@link kTAG_SKIPPED}</tt>: Processed count.
	 * </ul>
	 *
	 * If you provide an offset that is not among the ones above, the method will raise an
	 * exception.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param string				$theCounter			Counter offset.
	 * @param int					$theCount			Increment delta.
	 *
	 * @static
	 */
	static function UpdateCounter( Wrapper $theWrapper,
										   $theIdentifier,
										   $theCounter,
										   $theCount = 1 )
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
		}
		
		//
		// Normalise identifier.
		//
		if( ! ($theIdentifier instanceof \MongoId) )
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
					"Cannot increment counter: "
				   ."invalid identifier [$theIdentifier]." );					// !@! ==>
		}
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) );
	
		//
		// Increment count.
		//
		$collection->updateReferenceCount( $theIdentifier,		// Native identifier.
										   kTAG_NID,			// Identifier offset.
										   $theCounter,			// Counter offset.
										   (int) $theCount );	// Count.
	
	} // UpdateCounter.

	 

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
	 *
	 * @throws Exception
	 */
	static function ResolveDatabase( Wrapper $theWrapper, $doAssert = TRUE, $doOpen = TRUE )
	{
		return EntityObject::ResolveDatabase( $theWrapper, $doAssert, $doOpen );	// ==>
	
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
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * In this class we intercept the session reference and cast it to a MongoId.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_SESSION
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Intercept object references.
		//
		if( $theValue instanceof PersistentObject )
			$theValue = $theValue->reference();
		
		//
		// Call parent method to resolve offset.
		//
		parent::postOffsetSet( $theOffset, $theValue );
		
		//
		// Set initialised status.
		//
		$this->isInited( \ArrayObject::offsetExists( kTAG_USER ) &&
						 \ArrayObject::offsetExists( kTAG_SESSION_TYPE ) &&
						 \ArrayObject::offsetExists( kTAG_SESSION_STATUS ) );
	
	} // postOffsetSet.

		

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
	 * @throws Exception
	 *
	 * @see kTAG_PROCESSED kTAG_VALIDATED kTAG_REJECTED kTAG_SKIPPED
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Call parent method.
		//
		parent::preCommitPrepare( $theTags, $theRefs );
		
		//
		// Initialise counters.
		//
		if( ! $this->offsetExists( kTAG_PROCESSED ) )
			$this->offsetSet( kTAG_PROCESSED, 0 );
			
		if( ! $this->offsetExists( kTAG_VALIDATED ) )
			$this->offsetSet( kTAG_VALIDATED, 0 );
			
		if( ! $this->offsetExists( kTAG_REJECTED ) )
			$this->offsetSet( kTAG_REJECTED, 0 );
			
		if( ! $this->offsetExists( kTAG_SKIPPED ) )
			$this->offsetSet( kTAG_SKIPPED, 0 );
		
	} // preCommitPrepare.

		

/*=======================================================================================
 *																						*
 *								PROTECTED OPERATIONS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateCount																		*
	 *==================================================================================*/

	/**
	 * Update a counter
	 *
	 * This method will update the count of the provided offset by the provided delta, the
	 * method will first update the object in the database, then update the counter in the
	 * current object and return the updated value relative to the contents of the current
	 * object. Because of this, the count should not be counted on :-)
	 *
	 * @param string				$theCounter			Counter offset.
	 * @param int					$theCount			Increment delta.
	 *
	 * @access protected
	 * @return int					Updated count relative to current object.
	 */
	protected function updateCount( $theCounter, $theCount )
	{
		//
		// Check wrapper.
		//
		if( $this->mDictionary !== NULL )
		{
			//
			// Check identifier.
			//
			if( $this->offsetExists( kTAG_NID ) )
				static::UpdateCounter(
					$this->mDictionary,
					$this->offsetGet( kTAG_NID ),
					$theCounter,
					$theCount );
			else
				throw new \Exception( "Missing native identifier." );			// !@! ==>
		
			//
			// Update in object.
			//
			$count = $this->offsetGet( $theCounter );
			$count += (int) $theCount;
			$this->offsetSet( $theCounter, $count );
		
			return $count;															// ==>
		
		} // Has wrapper.
		
		throw new \Exception( "Missing wrapper." );								// !@! ==>
	
	} // updateCount.

	 

} // class SessionObject.


?>
