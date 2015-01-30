<?php

/**
 * MongoWrapper.php
 *
 * This file contains the definition of the {@link MongoWrapper} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Wrapper;

/*=======================================================================================
 *																						*
 *									MongoWrapper.php									*
 *																						*
 *======================================================================================*/


/**
 * Mongo wrapper
 *
 * This class extends its ancestor by implementing all its collections and databases as
 * MongoDB objects by default.
 *
 * The class adds four public methods which can be used to load data into the collections
 * referenced by the current wrapper:
 *
 * <ul>
 *	<li><tt>{@link insert()}<tt>: Insert the provided object into its relative collection.
 *	<li><tt>{@link update()}<tt>: Update data in the provided collection.
 *	<li><tt>{@link replace()}<tt>: replace the provided object in its relative collection.
 *	<li><tt>{@link delete()}<tt>: Delete the provided object from its relative collection.
 * </ul>
 *
 * The class also takes advantage of the batch methods of MongoDB to perform batch updates
 * in the database. Once a batch is opened, {@link openBatch()}, all operations, except the
 * {@link replace()} method, are added to the batch and the actual commit of all data will
 * only occur when the {@link closeBatch()} method is closed, or when the current object is
 * destructed.
 *
 * Although similar, batch operations should not be considered as transactions, since there
 * is no rollback feature.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/02/2014
 */
class MongoWrapper extends Wrapper
{
	/**
	 * Batch flag.
	 *
	 * This data member holds a flag indicating whether a batch is active.
	 *
	 * @var boolean
	 */
	private $mBatch = FALSE;

	/**
	 * Batch list.
	 *
	 * This data member holds the container of batch operations as an array structured as
	 * follows:
	 *
	 * <ul>
	 *	<li><tt>i</tt>: This element holds an array indexed by collection name with as value
	 *		the mongo batch class receiving insert operations.
	 *	<li><tt>u</tt>: This element holds an array indexed by collection name with as value
	 *		the mongo batch class receiving update operations.
	 *	<li><tt>d</tt>: This element holds an array indexed by collection name with as value
	 *		the mongo batch class receiving delete operations.
	 * </ul>
	 *
	 * Batches are executed in the above order.
	 *
	 * @var array
	 */
	protected $mBatchList = [ 'i' => Array(), 'u' => Array(), 'd' => Array() ];

		

/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * Destruct instance.
	 *
	 * The destructor will close the batch if open, it will do so <em>before</em> calling
	 * the parent method.
	 *
	 * @access public
	 *
	 * @uses closeBatch()
	 */
	public function __destruct()
	{
		//
		// Execute eventual batches.
		//
		$this->closeBatch();
		
		//
		// Call parent destructor.
		//
		parent::__destruct();
	
	} // Destructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	insert																			*
	 *==================================================================================*/

	/**
	 * Insert object
	 *
	 * This method will insert the provided object into its related collection and return
	 * the object's native identifier, if successful, or raise an exception on errors.
	 *
	 * The method will insert the object into the collection, if no batch is open, or add
	 * the operation to the open batch if open; in that case, if the object lacks its
	 * native identifier, the method will return <tt>TRUE</tt>.
	 *
	 * The method will first resolve the database related to the object, then set the
	 * class in the object and call the {@link insertObject()} method which will take care
	 * of either inserting the abject into the collection, or add it to the eventual open
	 * batch.
	 *
	 * The method will return the object's native identifier or <tt>NULL</tt> if adding an
	 * object lacking its native identifier to a batch.
	 *
	 * Any error will raise an exception.
	 *
	 * @param PersistentObject		$theObject			Object to insert.
	 *
	 * @access public
	 * @return mixed				The object's native identifier, or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses resolveDatabase()
	 * @uses insertObject()
	 */
	public function insert( PersistentObject $theObject )
	{
		//
		// Init local storage.
		//
		$class = get_class( $theObject );
		$collection = $class::kSEQ_NAME;
		
		//
		// Resolve database.
		//
		$database = $this->resolveDatabase( $collection );
		if( $database !== NULL )
		{
			//
			// Resolve collection.
			//
			switch( $name )
			{
				case Tag::kSEQ_NAME:
				case Term::kSEQ_NAME:
				case Node::kSEQ_NAME:
				case Edge::kSEQ_NAME:
				case User::kSEQ_NAME:
				case UnitObject::kSEQ_NAME:
				
					//
					// Set class.
					//
					$theObject[ kTAG_CLASS ] = $class;
					
					//
					// Persist.
					//
					return $this->insertObject(
								$theObject,
						  		$database->collection( $collection, TRUE ) );		// ==>
			
				default:
					throw new \Exception(
						"Cannot insert object: "
					   ."invalid collection name [$collection]." );				// !@! ==>
			
			} // Parsed by collection name.
		
		} // Resolved database.
		
		throw new \Exception(
			"Cannot insert object: "
		   ."database is not set." );											// !@! ==>
	
	} // insert.

	 
	/*===================================================================================
	 *	update																			*
	 *==================================================================================*/

	/**
	 * Update object
	 *
	 * This method will update the objects selected by the provided criteria in the
	 * collection identified by the provided collection name.
	 *
	 * The method will update the objects selected by the provided criteria in the
	 * collection identified by the provided name, if no batch is open, or add the operation
	 * to the open batch if open.
	 *
	 * The method will first resolve the database related to the object, then call the
	 * {@link updateObject()} method which will take care of either updating, or adding the
	 * operation to an open batch.
	 *
	 * The method will return an array containing the operation status.
	 *
	 * Any error will raise an exception.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param array					$theUpdates			Update criteria.
	 * @param string				$theCollection		Collection name.
	 *
	 * @access public
	 * @return mixed				The operation result.
	 *
	 * @throws Exception
	 *
	 * @uses resolveDatabase()
	 * @uses updateObject()
	 */
	public function update( $theCriteria, $theUpdates, $theCollection )
	{
		//
		// Resolve database.
		//
		$database = $this->resolveDatabase( $theCollection );
		if( $database !== NULL )
		{
			//
			// Handle collection.
			//
			if( $theCollection instanceof MongoCollection )
				return $this->updateObject(
							$theCriteria,
							$theUpdates,
							$theCollection );										// ==>
			
			//
			// Normalise collection name.
			//
			$theCollection = (string) $theCollection;
			
			//
			// Resolve collection.
			//
			switch( $theCollection )
			{
				case Tag::kSEQ_NAME:
				case Term::kSEQ_NAME:
				case Node::kSEQ_NAME:
				case Edge::kSEQ_NAME:
				case User::kSEQ_NAME:
				case UnitObject::kSEQ_NAME:
				
					//
					// Persist.
					//
					return $this->updateObject(
								$theCriteria,
								$theUpdates,
						  		$database->collection( $theCollection, TRUE ) );	// ==>
			
				default:
					throw new \Exception(
						"Cannot update objects: "
					   ."invalid collection name [$theCollection]." );			// !@! ==>
			
			} // Parsed by collection name.
		
		} // Resolved database.
		
		throw new \Exception(
			"Cannot update object: "
		   ."database is not set." );											// !@! ==>
	
	} // update.

	 
	/*===================================================================================
	 *	replace																			*
	 *==================================================================================*/

	/**
	 * Replace object
	 *
	 * This method will replace the provided object into its related collection and return
	 * the object's native identifier, if successful, or raise an exception on errors.
	 *
	 * Before replacing the object, the method will close any open batch, it will then
	 * replace the object and finally re-open the batch.
	 *
	 * The method will return the object's native identifier; if that is missing the method
	 * will raise an exception.
	 *
	 * Any error will raise an exception.
	 *
	 * @param PersistentObject		$theObject			Object to insert.
	 *
	 * @access public
	 * @return mixed				The operation result.
	 *
	 * @uses resolveDatabase()
	 * @uses hasBatch()
	 * @uses closeBatch()
	 * @uses replaceObject()
	 * @uses openBatch()
	 */
	public function replace( PersistentObject $theObject )
	{
		//
		// Init local storage.
		//
		$class = get_class( $theObject );
		$collection = $class::kSEQ_NAME;
		
		//
		// Check native identifier.
		//
		if( $theObject->offsetExists( kTAG_NID ) )
		{
			//
			// Resolve database.
			//
			$database = $this->resolveDatabase( $collection );
			if( $database !== NULL )
			{
				//
				// Close batch.
				//
				if( $batch = $this->hasBatch() )
					$this->closeBatch();
		
				//
				// Resolve collection.
				//
				switch( $collection )
				{
					case Tag::kSEQ_NAME:
					case Term::kSEQ_NAME:
					case Node::kSEQ_NAME:
					case Edge::kSEQ_NAME:
					case User::kSEQ_NAME:
					case UnitObject::kSEQ_NAME:
				
						//
						// Set class.
						//
						$theObject[ kTAG_CLASS ] = get_class( $theObject );
					
						//
						// Persist.
						//
						$ok
							= $this->replaceObject(
								$theObject,
								$database->collection( $collection, TRUE ) );		// ==>
						
						break;
			
					default:
						throw new \Exception(
							"Cannot insert object: "
						   ."invalid collection name [$collection]." );			// !@! ==>
			
				} // Parsed by collection name.
				
				//
				// Open batch.
				//
				if( $batch )
					$this->openBatch();
				
				return $ok;															// ==>
		
			} // Resolved database.
		
			throw new \Exception(
				"Cannot insert object: "
			   ."database is not set." );										// !@! ==>
		
		} // Has native identifier.
	
		throw new \Exception(
			"Cannot replace object: "
		   ."missing native identifier." );										// !@! ==>
		
	} // replace.

	 
	/*===================================================================================
	 *	delete																			*
	 *==================================================================================*/

	/**
	 * Delete object
	 *
	 * This method will delete the object identified by the provided native identifier or
	 * actual object and return the operation status.
	 *
	 * The method expects either the object to be deleted, or its native identifier and its
	 * related collection name or object.
	 *
	 * The method will delete from the collection, if no batch is open, or add the operation
	 * to the open batch.
	 *
	 * Any error will raise an exception.
	 *
	 * @param mixed					$theObject			Object, or native identifier.
	 * @param string				$theCollection		Collection name if provided id.
	 *
	 * @access public
	 * @return mixed				The operation result.
	 *
	 * @uses resolveDatabase()
	 * @uses deleteObject()
	 */
	public function delete( $theObject, $theCollection = NULL )
	{
		//
		// Resolve identifier.
		//
		if( $theObject instanceof PersistentObject )
		{
			//
			// Check native identifier.
			//
			if( ! $theObject->offsetExists( kTAG_NID ) )
				throw new \Exception(
					"Cannot delete object: "
				   ."missing native identifier." );								// !@! ==>
			
			//
			// Init local storage.
			//
			$class = get_class( $theObject );
			$theCollection = $class::kSEQ_NAME;
		
			//
			// Save identifier.
			//
			$theObject = $theObject->offsetGet( kTAG_NID );
		
		} // Provided object.
		
		//
		// Resolve database.
		//
		$database = $this->resolveDatabase( $theCollection );
		if( $database !== NULL )
		{
			//
			// Handle collection.
			//
			if( $theCollection instanceof MongoCollection )
				return $this->deleteObject(
							$theObject,
							$theCollection );										// ==>
			
			//
			// Resolve collection.
			//
			switch( $theCollection )
			{
				case Tag::kSEQ_NAME:
				case Term::kSEQ_NAME:
				case Node::kSEQ_NAME:
				case Edge::kSEQ_NAME:
				case User::kSEQ_NAME:
				case UnitObject::kSEQ_NAME:
			
					//
					// Persist.
					//
					return $this->deleteObject(
								$theObject,
								$database->collection( $name, TRUE ) );				// ==>
		
				default:
					throw new \Exception(
						"Cannot delete object: "
					   ."invalid collection name [$theCollection]." );			// !@! ==>
		
			} // Parsed by collection name.
	
		} // Resolved database.
	
		throw new \Exception(
			"Cannot DELETE object: "
		   ."database is not set." );											// !@! ==>
		
	} // delete.

		

/*=======================================================================================
 *																						*
 *									PUBLIC BATCH INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	hasBatch																		*
	 *==================================================================================*/

	/**
	 * Check if a batch is open
	 *
	 * This method will return <tt>TRUE</tt> if a batch is currently open, or
	 * <tt>FALSE</tt>.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> means active batch.
	 */
	public function hasBatch()							{	return (boolean) $this->mBatch;	}

		
	/*===================================================================================
	 *	openBatch																		*
	 *==================================================================================*/

	/**
	 * Open batch
	 *
	 * This method will open a new batch transaction and return <tt>TRUE</tt> if it opened
	 * it, an array with the batch close operation if it closed an open batch, or
	 * <tt>FALSE</tt> if it encountered an open batch and did not close it.
	 *
	 * The parameter is a boolean flag that determines whether an existing open batch is to
	 * be closed; by default open batches will not be closed.
	 *
	 * @param boolean				$doClose			Close open batch.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt>, <tt>FALSE</tt> or an array.
	 *
	 * @uses hasBatch()
	 * @uses closeBatch()
	 * @uses initBatch()
	 */
	public function openBatch( $doClose = FALSE )
	{
		//
		// Init local storage.
		//
		$result = TRUE;
		
		//
		// Close open batch.
		//
		if( $this->hasBatch() )
		{
			//
			// Ignore batch.
			//
			if( ! $doClose )
				return FALSE;														// ==>
			
			//
			// Close open batch.
			//
			$result = $this->closeBatch();
		
		} // Found an open batch.
		
		//
		// Reset batches.
		//
		$this->initBatch();
		
		//
		// Open batch.
		//
		$this->mBatch = TRUE;
		
		return $result;																// ==>
	
	} // openBatch.

	 
	/*===================================================================================
	 *	closeBatch																		*
	 *==================================================================================*/

	/**
	 * Close batch
	 *
	 * This method will execute the eventual open batch.
	 *
	 * If no open batch was encountered, the method will return <tt>FALSE</tt>.
	 *
	 * If an open batch was closed, the method will return an array with the results of the
	 * operation.
	 *
	 * The parameter is a boolean flag that indicates whether to opena a batch after closing
	 * it, by default a batch will not be opened.
	 *
	 * @param boolean				$doOpen				Open batch after executing.
	 *
	 * @access public
	 *
	 * @uses hasBatch()
	 * @uses initBatch()
	 */
	public function closeBatch( $doOpen = FALSE )
	{
		//
		// Init local storage.
		//
		$result = NULL;
		
		//
		// Handle open batch.
		//
		if( $this->hasBatch() )
		{
			//
			// TRY BLOCK
			//
			try
			{
				//
				// Init local storage.
				//
				$result = Array();
		
				//
				// Iterate insert batches.
				//
				foreach( $this->mBatchList[ 'i' ] as $key => $batch )
					$result[ $key ]
						= $batch->execute( array( 'ordered' => FALSE ) );
		
				//
				// Iterate update batches.
				//
				foreach( $this->mBatchList[ 'u' ] as $key => $batch )
					$result[ $key ]
						= $batch->execute( array( 'ordered' => FALSE ) );
		
				//
				// Iterate delete batches.
				//
				foreach( $this->mBatchList[ 'd' ] as $key => $batch )
					$result[ $key ]
						= $batch->execute( array( 'ordered' => FALSE ) );
				
				//
				// Reset batches.
				//
				$this->initBatch();
			}
			
			//
			// CATCH BLOCK
			//
			catch( \Exception $error )
			{
				//
				// Reset batches.
				//
				$this->initBatch();
				
				//
				// Throw exception.
				//
				throw $error;													// !@! ==>
			}
		
			//
			// Open batch.
			//
			if( $doOpen )
				$this->mBatch = TRUE;
		
		} // A batch was open.
		
		return $result;																// ==>
	
	} // closeBatch.

		

/*=======================================================================================
 *																						*
 *							PROTECTED INITIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	initBatch																		*
	 *==================================================================================*/

	/**
	 * Initialise batch
	 *
	 * This method will initialise the batch flag and list.
	 *
	 * @access protected
	 */
	protected function initBatch()
	{
		//
		// Reset flag.
		//
		$this->mBatch = FALSE;
		
		//
		// Reset batch list.
		//
		$this->mBatchList = [ 'i' => Array(), 'u' => Array(), 'd' => Array() ];
	
	} // initBatch.

		

/*=======================================================================================
 *																						*
 *							PROTECTED PERSISTENCE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	insertObject																	*
	 *==================================================================================*/

	/**
	 * Insert an object
	 *
	 * This method will insert the provided object into the provided collection.
	 *
	 * @param PersistentObject		$theObject			Object to insert.
	 * @param MongoCollection		$theCollection		Collection in which to insert.
	 *
	 * @access protected
	 * @return mixed				The object's native identifier, or <tt>NULL</tt>.
	 *
	 * @uses hasBatch()
	 * @uses getBatch()
	 */
	protected function insertObject( PersistentObject	$theObject,
									 MongoCollection	$theCollection )
	{
		//
		// Serialise object.
		//
		ContainerObject::Object2Array( $theObject, $data );
		
		//
		// Handle batch.
		//
		if( $this->hasBatch() )
			$this->getBatch( $theCollection, 'i' )
				->add( $data );
		
		//
		// Handle insert.
		//
		else
			$theCollection
				->connection()
					->insert( $data );
		
		//
		// Get native identifier.
		//
		if( array_key_exists( kTAG_NID, $data ) )
		{
			//
			// Copy identifier.
			//
			$theObject[ kTAG_NID ] = $data[ kTAG_NID ];
			
			return $data[ kTAG_NID ];												// ==>
		
		} // Has native identifier.
		
		return NULL;																// ==>
	
	} // insertObject.

	 
	/*===================================================================================
	 *	updateObject																	*
	 *==================================================================================*/

	/**
	 * insert an object
	 *
	 * This method will update objects selected by the provided criteria in the provided
	 * collection applying the provided actions.
	 *
	 * It is assumed that the update can affect multiple records and that no records will
	 * be created (<tt>multi</tt>=<tt>TRUE</tt>, <tt>upsert</tt>=<tt>FALSE</tt>)
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param array					$theActions			Update actions.
	 * @param MongoCollection		$theCollection		Collection in which to insert.
	 *
	 * @access protected
	 * @return mixed				The operation result.
	 *
	 * @uses hasBatch()
	 * @uses getBatch()
	 */
	protected function updateObject( 					$theCriteria,
														$theActions,
									 MongoCollection	$theCollection )
	{
		//
		// Handle batch.
		//
		if( $this->hasBatch() )
			return
				$this->getBatch( $theCollection, 'u' )
					->add( array( 'q' => $theCriteria,
								  'u' => $theActions,
								  'multi' => TRUE,
								  'upsert' => FALSE ) );							// ==>
		
		//
		// Handle update.
		//
		$ok
			= $theCollection
				->connection()
					->update( $theCriteria,
							  $theActions,
							  array( 'multi' => TRUE,
							  		 'upsert' => FALSE ) );
		
		//
		// Handle errors.
		//
		if( ! $ok[ 'ok' ] )
			throw new Exception( $ok[ 'err' ] );								// !@! ==>
		
		return $ok;																	// ==>
	
	} // updateObject.

	 
	/*===================================================================================
	 *	replaceObject																	*
	 *==================================================================================*/

	/**
	 * Replace an object
	 *
	 * This method will replace the provided object into the provided collection.
	 *
	 * Note that this method <em>will not consider batches</tt>, furgermore, <em>if any
	 * batch is active it will be closed, the object will be replaced and the batch will
	 * be reopened.
	 *
	 * The method will return the native identifier of the object, or <tt>TRUE</tt>.
	 *
	 * Note that this method expects the object to have its native identifier.
	 *
	 * @param PersistentObject		$theObject			Object to insert.
	 * @param MongoCollection		$theCollection		Collection in which to insert.
	 *
	 * @access protected
	 * @return mixed				The operation result.
	 */
	protected function replaceObject( PersistentObject	$theObject,
									  MongoCollection	$theCollection )
	{
		//
		// Serialise object.
		//
		ContainerObject::Object2Array( $theObject, $data );
		
		return
			$theCollection
				->connection()
					->save( $data );												// ==>
	
	} // replaceObject.

	 
	/*===================================================================================
	 *	deleteObject																	*
	 *==================================================================================*/

	/**
	 * Delete an object
	 *
	 * This method will delete the object identified by the provided identifier from the
	 * provided collection.
	 *
	 * The method will return the object's identifier if the object was deleted, or
	 * <tt>NULL</tt> if the identifier was not matched.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param MongoCollection		$theCollection		Collection in which to insert.
	 *
	 * @access protected
	 * @return mixed				The operation result.
	 *
	 * @uses hasBatch()
	 * @uses getBatch()
	 */
	protected function deleteObject(					$theIdentifier,
									 MongoCollection	$theCollection )
	{
		//
		// Init local storage.
		//
		$criteria = array( kTAG_NID => $theIdentifier );
		
		//
		// Handle batch.
		//
		if( $this->hasBatch() )
			return
				$this->getBatch( $theCollection, 'd' )
					->add(
						array( 'q' => $criteria,
							   'limit' => 1 ) );									// ==>
		
		//
		// Handle delete.
		//
		return
			$theCollection
				->connection()
					->remove( $criteria );											// ==>
	
	} // deleteObject.

		

/*=======================================================================================
 *																						*
 *								PROTECTED BATCH UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getBatch																		*
	 *==================================================================================*/

	/**
	 * Get a batch
	 *
	 * This method will locate and return, or create and return the batch objec
	 * corresponding to the provided collection.
	 *
	 * @param MongoCollection		$theCollection		Collection batch.
	 * @param string				$theType			Batch type (i, u, d).
	 *
	 * @access protected
	 */
	protected function getBatch( MongoCollection $theCollection, $theType )
	{
		//
		// Get existing batch.
		//
		if( array_key_exists( $theCollection->getName(), $this->mBatchList[$theType ] ) )
			return $this->mBatchList[ $theType ][ $theCollection->getName() ];		// ==>
		
		//
		// Create batch.
		//
		return new \MongoInsertBatch( $theCollection->connection(),
									  array( 'ordered' => FALSE ) );				// ==>
	
	} // getBatch.

	 

} // class Wrapper.


?>
