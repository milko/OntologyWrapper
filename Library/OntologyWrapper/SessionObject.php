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
 * The class features the following default offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: <em>Native identifier</em>. The native identifier of a
 *		session or transaction is automatically generated when inserted the first time, it
 *		can then be used to reference the object and have {@link Transaction} objects update
 *		its members.
 *	<li><tt>{@link kTAG_SESSION}</tt>: <em>Session</em>. The object reference of the session
 *		to which the current object is related.
 *	<li><tt>{@link kTAG_COUNTER_PROCESSED}</tt>: <em>Processed elements</em>. The number of elements
 *		processed by the session, this will typically be the transactions count relating to
 *		this session.
 *	<li><tt>{@link kTAG_COUNTER_VALIDATED}</tt>: <em>Validated elements</em>. The number of elements
 *		validated by the session, this will typically be the transactions count that were
 *		cleared by the validation process.
 *	<li><tt>{@link kTAG_COUNTER_REJECTED}</tt>: <em>Rejected elements</em>. The number of elements
 *		rejected by the session, this will typically be the transactions count that were
 *		not cleared by the validation process.
 *	<li><tt>{@link kTAG_COUNTER_SKIPPED}</tt>: <em>Skipped elements</em>. The number of elements
 *		skipped by the session, this will typically be the transactions count that were
 *		skipped by the validation process; such as empty data template lines.
 *	<li><tt>{@link kTAG_COUNTER_COLLECTIONS}</tt>: <em>Collections count</em>. The total
 *		number of collections.
 *	<li><tt>{@link kTAG_COUNTER_RECORDS}</tt>: <em>Records count</em>. The total number of
 *		records.
 *	<li><tt>{@link kTAG_COUNTER_FIELDS}</tt>: <em>Fields count</em>. The total number of
 *		fields.
 *	<li><tt>{@link kTAG_COUNTER_PROGRESS}</tt>: <em>Progress</em>. The current progress
 *		percentage: it is the percentage of the processed count by one of the three above
 *		properties.
 * </ul>
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
	 * Objects derived from this class feature a native identifier, in this class we convert
	 * string identifiers to this type.
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
				if( ($theIdentifier !== NULL)
				 && (! is_array( $theIdentifier )) )
					$theIdentifier
						= static::ResolveCollection(
							static::ResolveDatabase( $theContainer, TRUE ) )
							->getObjectId( $theIdentifier );
		
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
	 * In this class we return the string representation of the native identifier.
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
		
		return
			static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) )
					->setObjectId( $id );											// ==>
	
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
		// Resolve wrapper.
		//
		$this->resolveWrapper( $theWrapper );
		
		return
			static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) )
					->setObjectId( parent::commit( $theWrapper, $theOptions ) );	// ==>
	
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
			if( $theValue instanceof PesistentObject )
				$theValue = $theValue->offsetGet( kTAG_NID );
			
			//
			// Normalise identifier.
			//
			$theValue
				= static::ResolveCollection(
					static::ResolveDatabase( $this->mDictionary, TRUE ) )
					->getObjectId( $theValue );
			
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

	 
	/*===================================================================================
	 *	totalCollections																*
	 *==================================================================================*/

	/**
	 * Manage collections count
	 *
	 * This method can be used to set or retrieve the <i>collections count</i>, it accepts a
	 * parameter which represents either the count or the requested operation, depending on
	 * its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value, depending on the commit status of the
	 *		object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will return the
	 *			value taken from the persistent object and will update the current object
	 *			with that value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			return the value found in the current object.
	 *	  </ul>
	 *	<li><em>other</em>: Set the value with the provided parameter, depending on the
	 *		commit status of the object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will set the
	 *			provided value in the persistent object and update the current object's
	 *			value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			set the provided value in the current object.
	 *	  </ul>
	 * </ul>
	 *
	 * If you provide <tt>FALSE</tt> as a value, the method will raise an exception.
	 *
	 * The object must have been instantiated with a wrapper.
	 *
	 * @param mixed					$theValue			New count or operation.
	 *
	 * @access public
	 * @return mixed				Total collections count.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_COUNTER_COLLECTIONS
	 *
	 * @uses handleOffset()
	 */
	public function totalCollections( $theValue = NULL )
	{
		//
		// Check value.
		//
		if( $theValue !== FALSE )
			return $this->handleOffset( kTAG_COUNTER_COLLECTIONS, $theValue );		// ==>
		
		throw new \Exception(
			"Cannot delete collections count." );								// !@! ==>
	
	} // totalCollections.

	 
	/*===================================================================================
	 *	totalRecords																	*
	 *==================================================================================*/

	/**
	 * Manage records count
	 *
	 * This method can be used to set or retrieve the <i>records count</i>, it accepts a
	 * parameter which represents either the count or the requested operation, depending on
	 * its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value, depending on the commit status of the
	 *		object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will return the
	 *			value taken from the persistent object and will update the current object
	 *			with that value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			return the value found in the current object.
	 *	  </ul>
	 *	<li><em>other</em>: Set the value with the provided parameter, depending on the
	 *		commit status of the object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will set the
	 *			provided value in the persistent object and update the current object's
	 *			value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			set the provided value in the current object.
	 *	  </ul>
	 * </ul>
	 *
	 * If you provide <tt>FALSE</tt> as a value, the method will raise an exception.
	 *
	 * The object must have been instantiated with a wrapper.
	 *
	 * @param mixed					$theValue			New count or operation.
	 *
	 * @access public
	 * @return mixed				Total records count.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_COUNTER_RECORDS
	 *
	 * @uses handleOffset()
	 */
	public function totalRecords( $theValue = NULL )
	{
		//
		// Check value.
		//
		if( $theValue !== FALSE )
			return $this->handleOffset( kTAG_COUNTER_RECORDS, $theValue );			// ==>
		
		throw new \Exception(
			"Cannot delete records count." );									// !@! ==>
	
	} // totalRecords.

	 
	/*===================================================================================
	 *	totalFields																		*
	 *==================================================================================*/

	/**
	 * Manage fields count
	 *
	 * This method can be used to set or retrieve the <i>fields count</i>, it accepts a
	 * parameter which represents either the count or the requested operation, depending on
	 * its value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value, depending on the commit status of the
	 *		object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will return the
	 *			value taken from the persistent object and will update the current object
	 *			with that value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			return the value found in the current object.
	 *	  </ul>
	 *	<li><em>other</em>: Set the value with the provided parameter, depending on the
	 *		commit status of the object:
	 *	  <ul>
	 *		<li><em>Committed</em>: If the object is committed, the method will set the
	 *			provided value in the persistent object and update the current object's
	 *			value.
	 *		<li><em>Not committed</em>: If the object is not committed, the method will
	 *			set the provided value in the current object.
	 *	  </ul>
	 * </ul>
	 *
	 * If you provide <tt>FALSE</tt> as a value, the method will raise an exception.
	 *
	 * The object must have been instantiated with a wrapper.
	 *
	 * @param mixed					$theValue			New count or operation.
	 *
	 * @access public
	 * @return mixed				Total fields count.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_COUNTER_FIELDS
	 *
	 * @uses handleOffset()
	 */
	public function totalFields( $theValue = NULL )
	{
		//
		// Check value.
		//
		if( $theValue !== FALSE )
			return $this->handleOffset( kTAG_COUNTER_FIELDS, $theValue );			// ==>
		
		throw new \Exception(
			"Cannot delete records count." );									// !@! ==>
	
	} // totalFields.

		

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
	 * The second parameter represents the offset of the reference count, if the parameter
	 * is provided, the method will also update the {@link kTAG_COUNTER_PROGRESS} property
	 * in the persistent object; the parameter may tajke the following values:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_COUNTER_COLLECTIONS}</tt>: Collections progress.
	 *	<li><tt>{@link kTAG_COUNTER_RECORDS}</tt>: Records progress.
	 *	<li><tt>{@link kTAG_COUNTER_FIELDS}</tt>: Fields progress.
	 * </ul>
	 *
	 * The current object's count will not be updated, because of this, the count should not
	 * be counted on :-)
	 *
	 * Note that an exception will be triggered if the object is not committed.
	 *
	 * @param mixed					$theValue			Increment delta or <tt>NULL</tt>.
	 * @param string				$theCounter			Total count offset.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @see kTAG_COUNTER_PROCESSED
	 *
	 * @uses resolvePersistent()
	 * @uses updateCount()
	 */
	public function processed( $theValue = NULL, $theCounter = NULL )
	{
		//
		// Validate counter.
		//
		if( $theCounter !== NULL )
		{
			switch( $theCounter )
			{
				case kTAG_COUNTER_FIELDS:
				case kTAG_COUNTER_RECORDS:
				case kTAG_COUNTER_COLLECTIONS:
					break;
			
				default:
					throw new \Exception(
						"Unable to set progress: "
					   ."Provided invalid counter reference [$theCounter]." );	// !@! ==>
			}
		}
		
		//
		// Retrieve count.
		//
		if( $theValue === NULL )
			return
				$this->resolvePersistent( TRUE )
					->offsetGet( kTAG_COUNTER_PROCESSED );
		
		//
		// Update count.
		//
		$this->updateCount( kTAG_COUNTER_PROCESSED, (int) $theValue, $theCounter );
		
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
	 * @see kTAG_COUNTER_VALIDATED
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
					->offsetGet( kTAG_COUNTER_VALIDATED );									// ==>
		
		//
		// Update count.
		//
		$this->updateCount( kTAG_COUNTER_VALIDATED, (int) $theValue );
		
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
	 * @see kTAG_COUNTER_REJECTED
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
					->offsetGet( kTAG_COUNTER_REJECTED );									// ==>
		
		//
		// Update count.
		//
		$this->updateCount( kTAG_COUNTER_REJECTED, (int) $theValue );
		
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
	 * @see kTAG_COUNTER_SKIPPED
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
					->offsetGet( kTAG_COUNTER_SKIPPED );									// ==>
		
		//
		// Update count.
		//
		$this->updateCount( kTAG_COUNTER_SKIPPED, (int) $theValue );
		
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
	 *	<li><tt>{@link kTAG_COUNTER_SKIPPED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_COUNTER_REJECTED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_COUNTER_VALIDATED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_COUNTER_PROCESSED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_COUNTER_COLLECTIONS}</tt>: Collections count.
	 *	<li><tt>{@link kTAG_COUNTER_RECORDS}</tt>: Records count.
	 *	<li><tt>{@link kTAG_COUNTER_FIELDS}</tt>: Fields count.
	 *	<li><tt>{@link kTAG_COUNTER_PROGRESS}</tt>: Progress.
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
		// Init local storage.
		//
		$result = Array();
		$counters = array( kTAG_COUNTER_SKIPPED, kTAG_COUNTER_REJECTED,
						   kTAG_COUNTER_VALIDATED, kTAG_COUNTER_PROCESSED,
						   kTAG_COUNTER_COLLECTIONS, kTAG_COUNTER_RECORDS,
						   kTAG_COUNTER_FIELDS, kTAG_COUNTER_PROGRESS );
		
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
			foreach( $counters as $counter )
			{
				if( $object->offsetExists( $counter ) )
					$this->offsetSet( $counter, $object->offsetGet( $counter ) );
			}
		
		} // Object is committed.
		
		//
		// Build result.
		//
		foreach( $counters as $counter )
		{
			if( $this->offsetExists( $counter ) )
				$result[ $counter ] = $this->offsetGet( $counter );
		}
		
		return $result;																// ==>
	
	} // counters.

		

/*=======================================================================================
 *																						*
 *								STATIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


		
	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * Delete an object
	 *
	 * We overload this method to normalise the identifier.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theIdentifier		Object native identifier.
	 *
	 * @static
	 * @return mixed				Identifier, <tt>NULL</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 */
	static function Delete( Wrapper $theWrapper, $theIdentifier )
	{
		//
		// Get collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper, TRUE ) );
		
		//
		// Perform deletion.
		//
		$theIdentifier
			= parent::Delete( $theWrapper,
							  $collection->getObjectId( $theIdentifier ) );
		
		return ( $theIdentifier === NULL )
			 ? NULL																	// ==>
			 : $collection->setObjectId( $theIdentifier );							// ==>
	
	} // Delete.

	 

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
	 *	<li><tt>{@link kTAG_COUNTER_PROCESSED}</tt>: Processed elements.
	 *	<li><tt>{@link kTAG_COUNTER_VALIDATED}</tt>: Validated elements.
	 *	<li><tt>{@link kTAG_COUNTER_REJECTED}</tt>: Rejected elements.
	 *	<li><tt>{@link kTAG_COUNTER_SKIPPED}</tt>: Skipped elements.
	 *	<li><tt>{@link kTAG_COUNTER_COLLECTIONS}</tt>: Collections count.
	 *	<li><tt>{@link kTAG_COUNTER_RECORDS}</tt>: Records count.
	 *	<li><tt>{@link kTAG_COUNTER_FIELDS}</tt>: Fields count.
	 *	<li><tt>{@link kTAG_COUNTER_PROGRESS}</tt>: Progress.
	 * </ul>
	 *
	 * @static
	 * @return array				List of unmanaged offsets.
	 *
	 * @see kTAG_COUNTER_PROCESSED kTAG_COUNTER_VALIDATED
	 * @see kTAG_COUNTER_REJECTED kTAG_COUNTER_SKIPPED
	 * @see kTAG_COUNTER_FIELDS kTAG_COUNTER_RECORDS kTAG_COUNTER_COLLECTIONS
	 * @see kTAG_COUNTER_PROGRESS
	 */
	static function UnmanagedOffsets()
	{
		return array_merge(
			parent::UnmanagedOffsets(),
			array( kTAG_SESSION,
				   kTAG_COUNTER_PROCESSED, kTAG_COUNTER_VALIDATED,
				   kTAG_COUNTER_COLLECTIONS, kTAG_COUNTER_RECORDS,
				   kTAG_COUNTER_FIELDS, kTAG_COUNTER_PROGRESS ) );					// ==>
	
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
	 * @see kTAG_COUNTER_PROCESSED kTAG_COUNTER_VALIDATED kTAG_COUNTER_REJECTED kTAG_COUNTER_SKIPPED
	 *
	 * @uses start()
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Initialise counters.
		//
		$this->offsetSet( kTAG_COUNTER_SKIPPED, 0 );
		$this->offsetSet( kTAG_COUNTER_REJECTED, 0 );
		$this->offsetSet( kTAG_COUNTER_VALIDATED, 0 );
		$this->offsetSet( kTAG_COUNTER_PROCESSED, 0 );
		
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
	 * 
	 *
	 * @param string				$theCounter			Counter offset.
	 * @param int					$theCount			Increment delta.
	 * @param string				$theTotal			Total count offset.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kTAG_COUNTER_PROCESSED kTAG_COUNTER_VALIDATED
	 * @see kTAG_COUNTER_REJECTED kTAG_COUNTER_SKIPPED
	 * @see kTAG_COUNTER_FIELDS kTAG_COUNTER_RECORDS kTAG_COUNTER_COLLECTIONS
	 * @see kTAG_COUNTER_PROGRESS
	 */
	protected function updateCount( $theCounter, $theCount, $theTotal = NULL )
	{
		//
		// Check counter.
		//
		switch( $theCounter )
		{
			case kTAG_COUNTER_PROCESSED:
			case kTAG_COUNTER_VALIDATED:
			case kTAG_COUNTER_REJECTED:
			case kTAG_COUNTER_SKIPPED:
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
					array( $theCounter => (int) $theCount ) );
		
		//
		// Handle progress.
		//
		if( ($theTotal !== NULL)
		 && $this->offsetExists( $theTotal ) )
		{
			//
			// Calculate progress.
			//
			$progress = ( $this->offsetGet( $theCounter ) * 100 )
					  / $this->offsetGet( $theTotal );
			
			//
			// Update progress in object.
			//
			$this->offsetSet( kTAG_COUNTER_PROGRESS, $progress );
			
			//
			// Update progress in database.
			//
			static::ResolveCollection(
				static::ResolveDatabase( $this->mDictionary, TRUE ) )
					->replaceOffsets(
						array( kTAG_NID => $this->offsetGet( kTAG_NID ) ),
						array( kTAG_COUNTER_PROGRESS => $progress ) );
		
		} // Provided total count.
	
	} // updateCount.

	 

} // class SessionObject.


?>
