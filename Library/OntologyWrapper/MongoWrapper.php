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
 * The class adds three public methods: {@link insert()}, {@link delete()} and
 * {@link modify()} that respectively insert, delete and modify data in the collection
 * relative to the object.
 *
 * The class also takes advantage of the batch methods of MongoDB to perform batch updates
 * in the database. Once a batch is opened, {@link openBatch()}, all operations are
 * added to the batch and the actual commit of all data will only occur when the
 * {@link closeBatch()} method is closed, or when the current object is destructed.
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
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
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
	 * This method is aware of batches, so if batches are active, the method will add the
	 * provided object to the batch; if the object lacks its native identifier, the method
	 * will return <tt>TRUE</tt>.
	 *
	 * @param PersistentObject		$theObject			Object to insert.
	 *
	 * @access public
	 * @return mixed				The object's native identifier, or <tt>TRUE</tt>.
	 *
	 * @uses resolveDatabase()
	 * @uses insertObject()
	 */
	public function insert( PersistentObject $theObject )
	{
		//
		// Init local storage.
		//
		$name = $theObject::kSEQ_NAME;
		
		//
		// Resolve database.
		//
		$database = $this->resolveDatabase( $name );
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
					$theObject[ kTAG_CLASS ] = get_class( $theObject );
					
					//
					// Persist.
					//
					return $this->insertObject(
								$theObject,
						  		$database->collection( $name, TRUE ) );				// ==>
			
				default:
					throw new \Exception(
						"Cannot insert object: "
					   ."invalid collection name [$name]." );					// !@! ==>
			
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
	 * This method is aware of batches, so if batches are active, the method will add the
	 * provided update to the batch.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param array					$theUpdates			Update criteria.
	 * @param string				$theCollection		Collection name.
	 *
	 * @access public
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
	 * This method will close any eventual open batches and open them again after replacing
	 * the object.
	 *
	 * If the provided object lacks its native identifier, the method will raise an
	 * exception.
	 *
	 * @param PersistentObject		$theObject			Object to insert.
	 *
	 * @access public
	 * @return mixed				The object's native identifier, or <tt>TRUE</tt>.
	 *
	 * @uses resolveDatabase()
	 * @uses insertObject()
	 */
	public function replace( PersistentObject $theObject )
	{
		//
		// Init local storage.
		//
		$name = $theObject::kSEQ_NAME;
		
		//
		// Check native identifier.
		//
		if( $theObject->offsetExists( kTAG_NID ) )
		{
			//
			// Resolve database.
			//
			$database = $this->resolveDatabase( $name );
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
						$theObject[ kTAG_CLASS ] = get_class( $theObject );
					
						//
						// Persist.
						//
						return $this->replaceObject(
									$theObject,
									$database->collection( $name, TRUE ) );			// ==>
			
					default:
						throw new \Exception(
							"Cannot insert object: "
						   ."invalid collection name [$name]." );				// !@! ==>
			
				} // Parsed by collection name.
		
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
	 * This method will delete the object identified by the provided identifier or actual
	 * object from the collection matching the provided name.
	 *
	 * The method will return the operation status.
	 *
	 * If the provided object lacks its native identifier, the method will raise an
	 * exception.
	 *
	 * @param mixed					$theObject			Object, or native identifier.
	 * @param string				$theCollection		Collection name.
	 *
	 * @access public
	 * @return mixed				The operation result.
	 *
	 * @uses resolveDatabase()
	 * @uses insertObject()
	 */
	public function delete( $theObject, $theCollection )
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
	 *	openBatch																		*
	 *==================================================================================*/

	/**
	 * Open batch
	 *
	 * This method will open a new batch transaction and return <tt>TRUE</tt> if it opened
	 * it, an array with the batch close operation if it closed an open batch, or
	 * <tt>FALSE</tt> if it encountered an open batch and did not close it.
	 *
	 * The parameter is a boolean flag that determines whether an open batch is to be
	 * closed, by default open batches will not be closed.
	 *
	 * @param boolean				$doClose			Close open batch.
	 *
	 * @access public
	 * @return mixed				<tt>TRUE</tt>, <tt>FALSE</tt> or an array.
	 *
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
		
		} // A batch was open.
		
		//
		// Open batch.
		//
		if( $doOpen )
			$this->mBatch = TRUE;
		
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
	 * @return mixed				The object native identifier or <tt>TRUE</tt>.
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
		
		return TRUE;																// ==>
	
	} // insertObject.

	 
	/*===================================================================================
	 *	updateObject																	*
	 *==================================================================================*/

	/**
	 * insert an object
	 *
	 * This method will insert the provided object into the provided collection.
	 *
	 * It is assumed that the update can affect multiple records and that no records will
	 * be created (<tt>multi</tt>=<tt>TRUE</tt>, <tt>upsert</tt>=<tt>FALSE</tt>)
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param array					$theUpdates			Update criteria.
	 * @param MongoCollection		$theCollection		Collection in which to insert.
	 *
	 * @access protected
	 * @return mixed				Operation result.
	 */
	protected function updateObject( 					$theCriteria,
														$theUpdates,
									 MongoCollection	$theCollection )
	{
		//
		// Handle batch.
		//
		if( $this->hasBatch() )
			return
				$this->getBatch( $theCollection, 'u' )
					->add( array( 'q' => $theCriteria,
								  'u' => $theUpdates,
								  'multi' => TRUE,
								  'upsert' => FALSE ) );							// ==>
		
		//
		// Handle update.
		//
		return
			$theCollection
				->connection()
					->update( $theCriteria,
							  $theUpdates,
							  array( 'multi' => TRUE,
							  		 'upsert' => FALSE ) );							// ==>
	
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
	 * @return mixed				The object native identifier.
	 */
	protected function replaceObject( PersistentObject	$theObject,
									  MongoCollection	$theCollection )
	{
		//
		// Init local storage.
		//
		$batch = $this->hasBatch();
		
		//
		// Close batch.
		//
		if( $batch )
			$this->closeBatch();
		
		//
		// Serialise object.
		//
		ContainerObject::Object2Array( $theObject, $data );
		
		//
		// Handle replace.
		//
		$theCollection
			->connection()
				->save( $data );
		
		return $theObject[ kTAG_NID ];												// ==>
	
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
	 * @return mixed				The operation status.
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
