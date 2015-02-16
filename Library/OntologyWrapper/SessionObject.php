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
	/**
	 * Offset accessors trait.
	 *
	 * We use this trait to provide a common framework for methods that manage offsets.
	 */
	use	traits\AccessorOffset;

		

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
 *								PUBLIC MEMBER ACCESSOR INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	manageSession																	*
	 *==================================================================================*/

	/**
	 * Manage session
	 *
	 * This method can be used to manage the session reference, the provided parameter is
	 * either the new session reference, or <tt>NULL</tt> to retrieve the current reference,
	 * the parameter will be handled as follows:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: <em>Retrieve session</tt>:
	 *	  <ul>
	 *		<li><em>The object is not committed</em>: The method will return the object's
	 *			value.
	 *		<li><em>The object is committed</em>: The method will read the object from the
	 *			database and use the persistent value.
	 *	  </ul>
	 *	<li><em>other</em>: <em>Set session</tt>:
	 *	  <ul>
	 *		<li><em>The object is not committed</em>: The method will set the provided
	 *			value in the current object.
	 *		<li><em>The object is committed</em>: The method will first set the value in the
	 *			persistent object, then set it in the current object
	 *	  </ul>
	 * </ul>
	 *
	 * If the second parameter is <tt>TRUE</tt>, if a session is found, the method will
	 * return the referenced object
	 *
	 * The method will return the current session or <tt>NULL</tt> if not set.
	 *
	 * @param mixed					$theValue			Session object or reference.
	 * @param boolean				$doObject			TRUE return object.
	 *
	 * @access public
	 * @return mixed				Session object or reference.
	 *
	 * @throws Exception
	 */
	public function manageSession( $theValue = NULL, $doObject = FALSE )
	{
@@@ MILKO @@@ CHECK IT.
		//
		// Retrieve value.
		//
		if( $theValue === NULL )
		{
			//
			// Handle uncommitted.
			//
			if( ! $this->committed() )
			{
				//
				// Check if there.
				//
				if( ! $this->offsetExists( kTAG_SESSION ) )
					return NULL;													// ==>
					
				//
				// Return reference.
				//
				if( ! $doObject )
					return $this->offsetGet( kTAG_SESSION );						// ==>
				
				//
				// Check wrapper.
				//
				if( ! $this->mDictionary !== NULL )
					throw new \Exception(
						"Unable to get session: "
					   ."missing wrapper." );									// !@! ==>
		
				//
				// Return object.
				//
				return
					static::ResolveObject(
						$this->mDictionary,
						Session::kSEQ_NAME,
						$this->offsetGet( kTAG_SESSION ),
						TRUE );														// ==>
			
			} // Not committed.
		
			//
			// Get persistent object.
			//
			$persistent = $this->resolvePersistent( TRUE );
				
			//
			// Check if there.
			//
			if( ! $persistent->offsetExists( kTAG_SESSION ) )
				return NULL;														// ==>
				
			//
			// Return reference.
			//
			if( ! $doObject )
				return $persistent->offsetGet( kTAG_SESSION );						// ==>
	
			//
			// Return object.
			//
			return
				static::ResolveObject(
					$this->mDictionary,
					Session::kSEQ_NAME,
					$persistent->offsetGet( kTAG_SESSION ),
					TRUE );															// ==>
	
		} // Retrieve value.
	
		//
		// Normalise session.
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
				   ."invalid identifier in object [$theValue]." );				// !@! ==>
		}
		
		//
		// Set data member.
		//
		$this->offsetSet( kTAG_SESSION, $theValue );
			
		//
		// Handle uncommitted.
		//
		if( ! $this->committed() )
		{
			//
			// Return reference.
			//
			if( ! $doObject )
				return $theValue;													// ==>
	
			//
			// Return object.
			//
			return
				static::ResolveObject(
					$this->mDictionary,
					Session::kSEQ_NAME,
					$theValue,
					TRUE );															// ==>
		
		} // Not committed.
		
		//
		// Check wrapper.
		//
		if( ! $this->mDictionary !== NULL )
			throw new \Exception(
				"Unable to set session: "
			   ."missing wrapper." );											// !@! ==>

		//
		// Set property.
		//
		static::ResolveCollection(
			static::ResolveDatabase( $this->mDictionary, TRUE ) )
				->replaceOffsets(
					$this->offsetGet( kTAG_NID ),
					array( kTAG_SESSION => $theValue ) );
		
		//
		// Return reference.
		//
		if( ! $doObject )
			return $theValue;														// ==>

		//
		// Return object.
		//
		return
			static::ResolveObject(
				$this->mDictionary,
				Session::kSEQ_NAME,
				$theValue,
				TRUE );																// ==>
	
	} // manageSession.

		

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
	 *	manageSession																	*
	 *==================================================================================*/

	/**
	 * Manage session
	 *
	 * This method can be used to manage the session reference, the provided parameter is
	 * either the new session reference, or <tt>NULL</tt> to retrieve the current reference.
	 *
	 * If the object is committed, the method will both set the session in the current
	 * object and update the session in the committed object.
	 *
	 * The method will return the current session or <tt>NULL</tt> if not set.
	 *
	 * @param mixed					$theSession			Session object or reference.
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
 *								PUBLIC UPDATE INTERFACE									*
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
 *							STATIC MEMBERS ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GetObject																		*
	 *==================================================================================*/

	/**
	 * Get the object
	 *
	 * This method can be used to retrieve the object from the provided wrapper identified
	 * by the provided identifier of the caller's class.
	 *
	 * It is assumed that the calling class is derived from this one.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @static
	 * @return						SessionObject or <tt>NULL</tt>.
	 */
	static function GetObject( Wrapper $theWrapper, $theIdentifier )
	{
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
					"Cannot retrieve object: "
				   ."invalid identifier [$theIdentifier]." );					// !@! ==>
		}
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_NID => $theIdentifier );
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) );
		
		return $collection->matchOne( $criteria, kQUERY_OBJECT );					// ==>
	
	} // GetObject.

	 
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
 *							PROTECTED MEMBER MANAGEMENT INTERFACE						*
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
