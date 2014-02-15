<?php

/**
 * PersistentObject.php
 *
 * This file contains the definition of the {@link PersistentObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\OntologyObject;

/*=======================================================================================
 *																						*
 *								PersistentObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Persistent object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing objects that can
 * persist in a container and that are constituted by ontology offsets.
 *
 * The main purpose of this class is to add the status and persistence traits providing the
 * prototypes needed to implement concrete persistent objects.
 *
 * The class makes use of the {@link Status} and {@link Persistence} traits:
 *
 * <ul>
 *	<li><tt>{@link Status}</tt>: This trait handles a bitfirld data member that keeps
 *		track of the object's status:
 *	 <ul>
 *		<li><tt>{@link isDirty()}</tt>: This flag is set whenever any offset is modified,
 *			this status can be tested whenever the object should be stored in a persistent
 *			container: if set, it means the object has been modified, if not set, it means
 *			that the object is identical to the persistent copy.
 *		<li><tt>{@link isCommitted()}</tt>: This flag is set whenever the object has been
 *			loaded or stored into a persistent container. This status can be useful to lock
 *			properties that cannot change once the object is stored.
 *	 </ul>
 *	<li><tt>{@link Persistence}</tt>: This trait handles the object persistence.
 * </ul>
 *
 * Objects derived from this class <em>must</em> define a constant called <em>kSEQ_NAME</em>
 * which provides a <em<string</em> representing the <em>default collection name</em> for
 * the current object: methods that commit or read objects of a specific class can then
 * resolve the collection given a database.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/02/2014
 */
abstract class PersistentObject extends OntologyObject
{
	/**
	 * Persistent trait.
	 *
	 * We use this trait to make objects of this class persistent.
	 */
	use	traits\Persistence;

		

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
	 * Objects derived from this class share the same constructor prototype, this allows
	 * instantiating an object by providing content, as for the parent class, or by
	 * providing an identifier and a container to retrieve the object from a persistent
	 * store.
	 *
	 * The method accepts two parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: This may be either an array containing the object's
	 *		persistent attributes, or a reference to a persistent connection. If this
	 *		parameter is <tt>NULL</tt>, the next parameter will be ignored.
	 *	<li><b>$theIdentifier</b>: This parameter represents the object identifier or the
	 *		object persistent attributes: in the first case it will used to select the
	 *		object from the provided container, in the second case, it is assumed that the
	 *		provided array holds the persistent attributes of an object committed in the
	 *		provided container.
	 * </ul>
	 *
	 * The workflow is as follows:
	 *
	 * <ul>
	 *	<li><i>Empty object</i>: Both parameters are omitted.
	 *	<li><i>Filled non committed object</i>: The first parameter is an array.
	 *	<li><i>Load object from container</i>: The first parameter is connection object and
	 *		the second object is a scalar identifier.
	 *	<li><i>Filled committed object</i>: The first parameter is connection object and the
	 *		second parameter is an array holding the object's persistent data.
	 * </ul>
	 *
	 * Any other combination will raise an exception.
	 *
	 * This constructor sets the committed flag, derived classes should first call the
	 * parent constructor, then they should set the inited flag.
	 *
	 * @param ConnectionObject		$theContainer		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses resolveCollection()
	 * @uses manageCollection()
	 * @uses isCommitted()
	 */
	public function __construct( $theContainer = NULL, $theIdentifier = NULL )
	{
		//
		// Instantiate empty object.
		//
		if( $theContainer === NULL )
			parent::__construct();
		
		//
		// Instantiate from object attributes array.
		//
		elseif( is_array( $theContainer ) )
			parent::__construct( $theContainer );
		
		//
		// Instantiate from object.
		//
		elseif( ($theIdentifier === NULL)
		 && ($theContainer instanceof \ArrayObject) )
			parent::__construct( $theContainer->getArrayCopy() );
		
		//
		// Handle container.
		//
		elseif( $theContainer instanceof ConnectionObject )
		{
			//
			// Resolve collection.
			//
			$collection = $this->resolveCollection( $theContainer );
			if( ! ($collection instanceof CollectionObject) )
				throw new \Exception(
					"Cannot instantiate object: "
				   ."invalid container parameter type." );						// !@! ==>
			
			//
			// Open collection.
			//
			$collection->openConnection();
			
			//
			// Set collection.
			//
			$this->manageCollection( $collection );
			
			//
			// Load object data.
			//
			if( is_array( $theIdentifier ) )
			{
				//
				// Set committed status.
				//
				$this->isCommitted( TRUE );
				
				//
				// Call parent constructor.
				//
				parent::__construct( $theIdentifier );
				
			} // Provided data.
			
			//
			// Resolve object.
			//
			else
			{
				//
				// Find object.
				//
				$found = $this->mCollection->resolve( $theIdentifier, kTAG_NID, FALSE );
				if( $found !== NULL )
				{
					//
					// Set committed status.
					//
					$this->isCommitted( TRUE );
				
					//
					// Call parent constructor.
					//
					parent::__construct( $found );
				
				} // Found.
				
				//
				// Not found.
				//
				else
					parent::__construct();
			
			} // Provided identifier.
		
		} // Container connection.
		
		else
			throw new \Exception(
				"Cannot instantiate object: "
			   ."invalid container parameter type." );							// !@! ==>

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								STATIC INSTANTIATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveObject																	*
	 *==================================================================================*/

	/**
	 * Resolve object
	 *
	 * This method should be used to statically instantiate an object from the provided data
	 * store, it should attempt to select the object matching the provided identifier and
	 * return an instance of the originally committed class.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: The database or collection from which the object is to be
	 *		retrieved.
	 *	<li><b>$theIdentifier</b>: The objet native identifier.
	 *	<li><b>$doAssert</b>: If <tt>TRUE</tt>, if the object is not matched, the method
	 *		will raise an exception; if <tt>FALSE</tT>, the method will return
	 *		<tt>NULL</tt>.
	 * </ul>
	 *
	 * In this class we assume the provided identifier is the native identifier, derived
	 * classes can override this method to provide more options.
	 *
	 * @param ConnectionObject		$theConnection		Persistent store.
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param boolean				$doAssert			Assert object.
	 *
	 * @access public
	 * @return OntologyObject		Object or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	static function ResolveObject( ConnectionObject $theConnection,
													$theIdentifier,
													$doAssert = TRUE )
	{
		//
		// Resolve collection.
		//
		if( $theConnection instanceof DatabaseObject )
		{
			//
			// Get collection.
			//
			$theConnection = $theConnection->Collection( static::kSEQ_NAME );
			
			//
			// Connect it.
			//
			$theConnection->openConnection();
		
		} // Database connection.
		
		//
		// Find object.
		//
		$object = $theConnection->resolve( $theIdentifier );
		if( $object !== NULL )
			return $object;															// ==>
		
		//
		// Assert.
		//
		if( $doAssert )
			throw new \Exception(
				"Unable to locate object." );									// !@! ==>
		
		return NULL;																// ==>
	
	} // ResolveObject.

		

/*=======================================================================================
 *																						*
 *							PROTECTED OBJECT TRAVERSAL INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadOffsetReference																*
	 *==================================================================================*/

	/**
	 * Load offset references
	 *
	 * This method can be used to resolve an offset containing an object reference or a list
	 * of object references into a list of objects, list of arrays or list of counts.
	 *
	 * The method will return an array, indexed by the referenced object's native
	 * identifier, containing the results of the operation.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffset</b>: The offset containing the object reference.
	 *	<li><b>$theSelector</b>: The sequences selector, <tt>kSEQ_NAME</tt>, of the class
	 *		of the referenced object.
	 *	<li><b>$asObject</b>: This parameter determines what is returned:
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Return the referenced objects; the method will raise an
	 *			exception if any reference was not resolved.
	 *		<li><tt>TRUE</tt>: Return the referenced objects as arrays; the mathod will
	 *			raise an exception if any reference was not resolved.
	 *		<li><tt>NULL</tt>: Return the referenced objects count (1 or 0).
	 *	 </ul>
	 * </ul>
	 *
	 * If the offset contains a nested list od references, the method will return a
	 * flattened list of results.
	 *
	 * If the current object is not committed, if it doesn't have a collection, or if it
	 * doesn't have the offset, the method will return <tt>NULL</tt>.
	 *
	 * @param mixed					$theOffset			Object reference offset.
	 * @param string				$theSelector		Sequences selector.
	 * @param mixed					$asObject			Return object, array or count.
	 *
	 * @access protected
	 * @return mixed				Object, array, count or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 */
	protected function loadOffsetReference( $theOffset, $theSelector, $asObject = TRUE )
	{
		//
		// Check if committed.
		//
		if( $this->isCommitted() )
		{
			//
			// Check collection.
			//
			if( $this->mCollection !== NULL )
			{
				//
				// Check offset.
				//
				if( \ArrayObject::offsetExists( $theOffset ) )
				{
					//
					// Init local storage.
					//
					$references = $this->offsetGet( $theOffset );
					$collection
						= $this->mCollection
							->Parent()
							->Collection( $theSelector );
					$collection->openConnection();
					
					//
					// Handle list.
					//
					if( is_array( $references ) )
					{
						//
						// Flatten list.
						//
						$result = Array();
						$iterator = new \RecursiveIteratorIterator(
										new \RecursiveArrayIterator( $references ),
										\RecursiveIteratorIterator::LEAVES_ONLY );
						foreach( $iterator as $reference )
						{
							//
							// Check array.
							//
							if( ! array_key_exists( $reference, $result ) )
							{
								//
								// Resolve reference.
								//
								$object
									= $collection->resolve(
										$reference, kTAG_NID, $asObject );
								if( $object === NULL )
									throw new \Exception(
										"Unable to resolve [$reference]." );	// !@! ==>
							
								//
								// Set object.
								//
								$result[ $reference ] = $object;
						
							} // Not there already.
					
						} // Iterating terms.
					
						return $result;												// ==>
					
					} // List of references.
					
					//
					// Handle scalar.
					//
					else
					{
						//
						// Resolve reference.
						//
						$object = $collection->resolve( $references, kTAG_NID, $asObject );
						if( $object !== NULL )
							return $object;											// ==>
					
						throw new \Exception(
							"Unable to resolve [$id]." );						// !@! ==>
					
					} // Scalar reference.
					
				} // Has offset.
			
			} // Has collection.
		
		} // Committed.
		
		return NULL;																// ==>
	
	} // loadOffsetReference.

	 

} // class PersistentObject.


?>
