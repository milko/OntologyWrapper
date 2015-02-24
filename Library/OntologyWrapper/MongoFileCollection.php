<?php

/**
 * MongoFileCollection.php
 *
 * This file contains the definition of the {@link MongoFileCollection} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\MongoDatabase;
use OntologyWrapper\MongoCollection;
use OntologyWrapper\MongoFileObject;

/*=======================================================================================
 *																						*
 *								MongoFileCollection.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo file collection
 *
 * This class extends the {@link MongoCollection} class by wrapping a {@link MongoGridFS}
 * class and enabling the management of {@link MongoFileObject} instances.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 24/02/2015
 */
class MongoFileCollection extends MongoCollection
{
		

/*=======================================================================================
 *																						*
 *								PUBLIC OBJECT INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newFileReference																*
	 *==================================================================================*/

	/**
	 * Return a new file object by reference
	 *
	 * This method should return a new {@link FileObject} instance given a file reference or
	 * path, the method will check whether the file is readable and set the mime type.
	 *
	 * @param mixed					$theFile			File path or reference.
	 *
	 * @access public
	 * @return FileObject			File object.
	 *
	 * @throws Exception
	 */
	public function newFileReference( $theFile )
	{
		//
		// Convert to reference.
		//
		if( ! ($theFile instanceof \SplFileInfo) )
			$theFile = new \SplFileInfo( (string) $theFile );
		
		//
		// Check if redable.
		//
		if( $theFile->isReadable() )
		{
			//
			// Instantiate object.
			//
			$object = new MongoFileObject( $this->dictionary() );
			
			//
			// Set file reference.
			//
			$object->setFileReference( $theFile );
			
			//
			// Init MIME type.
			//
			$object->offsetSet(
				kTAG_FILE_MIME_TYPE,
				mime_content_type( $theFile->getRealPath() ) );
		
		} // File is readable.
		
		throw new \Exception(
			"Unable to create file object: "
		   ."the file is not readable." );										// !@! ==>
	
	} // newFileReference.

	 
	/*===================================================================================
	 *	newFileUpload																	*
	 *==================================================================================*/

	/**
	 * Return a new file object by upload
	 *
	 * This method should return a new {@link FileObject} instance given the upload form
	 * element name.
	 *
	 * @param string				$theFile			Upload form element name.
	 *
	 * @access public
	 * @return FileObject			File object.
	 */
	public function newFileUpload( $theFile )
	{
		//
		// Instantiate object.
		//
		$object = new MongoFileObject( $this->dictionary() );
		
		//
		// Set file reference.
		//
		$object->setFileUpload( (string) $theFile );
	
	} // newFileUpload.

	 
	/*===================================================================================
	 *	newFileContent																	*
	 *==================================================================================*/

	/**
	 * Return a new file object by content
	 *
	 * This method should return a new {@link FileObject} instance given the file's content
	 * as a string of bytes.
	 *
	 * @param string				$theFile			File contents.
	 *
	 * @access public
	 * @return FileObject			File object.
	 */
	public function newFileContent( $theFile )
	{
		//
		// Instantiate object.
		//
		$object = new MongoFileObject( $this->dictionary() );
		
		//
		// Set file reference.
		//
		$object->setFileContent( (string) $theFile );
	
	} // newFileContent.

		

/*=======================================================================================
 *																						*
 *									PUBLIC QUERY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	matchID																			*
	 *==================================================================================*/

	/**
	 * Match by ID
	 *
	 * In this class we use the {@link MongoGridFS::get()}.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param boolean				$doAssert			Assert existance.
	 *
	 * @access public
	 * @return fileObject			Matched object or <tt>NULL</tt>.
	 */
	public function matchID( $theIdentifier, $doAssert = TRUE )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Match identifier.
			//
			$found = $this->connection()->get( $theIdentifier );
			
			//
			// Handle not found.
			//
			if( $doAssert
			 && ($found === NULL) )
				throw new \Exception(
					"Unable to match identifier: "
				   ."object not found [".(string) $theIdentifier."]." );		// !@! ==>
			
			return $found;															// ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to match object: "
		   ."connection is not open." );										// !@! ==>
	
	} // matchID.

	 
	/*===================================================================================
	 *	matchOne																		*
	 *==================================================================================*/

	/**
	 * Match one object
	 *
	 * We first check if the current collection is connected, if that is not the case, we
	 * raise an exception.
	 *
	 * In this class we map the method over the {@link MongoGridFS::findOne()} method when
	 * retrieving objects or identifiers and {@link MongoGridFS::find()} method when
	 * retrieving counts.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param array					$theFields			Fields selection.
	 * @param bitfield				$theResult			Result type.
	 *
	 * @access public
	 * @return mixed				Matched data or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	public function matchOne( $theCriteria,
							  $theResult = kQUERY_OBJECT,
							  $theFields = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle fields.
			//
			if( count( $theFields ) )
			{
				//
				// Prevent fields if requested object.
				//
				if( ($theResult & kRESULT_MASK) == kQUERY_OBJECT )
					$theFields = Array();
				
				//
				// Convert fields to object.
				// This is necessary since PHP treats numeric indexes as integers.
				//
				else
					$theFields = new \ArrayObject( $theFields );
			
			} // Provided fields selection.
						
			//
			// Query collection.
			//
			switch( $theResult & kRESULT_MASK )
			{
				case kQUERY_NID:
					//
					// Convert fields to object.
					// This is necessary since PHP treats numeric indexes as integers.
					//
					$theFields = new \ArrayObject( array( kTAG_NID => TRUE ) );
				case kQUERY_OBJECT:
					$object
						= $this->
							mConnection->
								findOne( $theCriteria, $theFields );
					break;
					
				case kQUERY_COUNT:
					$rs
						= $this->
							mConnection->
								find( $theCriteria );
					
					return $rs->count();											// ==>
			
			} // Parsed result flags.
			
			//
			// Handle no matches.
			//
			if( $object === NULL )
			{
				//
				// Assert.
				//
				if( $theResult & kQUERY_ASSERT )
					throw new \Exception(
						"Unable to match object." );							// !@! ==>
				
				return NULL;														// ==>
			
			} // No matches.
			
			//
			// Handle result.
			//
			switch( $theResult & kRESULT_MASK )
			{
				case kQUERY_NID:
				
					if( ! array_key_exists( kTAG_NID, $object->file ) )
						throw new \Exception(
							"Unable to resolve identifier: "
						   ."missing object identifier." );						// !@! ==>
					
					return $object->file[ kTAG_NID ];								// ==>
				
				case kQUERY_OBJECT:
				
					return new MongoFileObject( $this->dictionary(), $object );		// ==>
			
			} // Parsed result flags.
		
		} // Connected.
			
		throw new \Exception(
			"Unable to match object: "
		   ."connection is not open." );										// !@! ==>
	
	} // matchOne.

	 
	/*===================================================================================
	 *	matchAll																		*
	 *==================================================================================*/

	/**
	 * Match all objects
	 *
	 * In this class we perform the query using the {@link MongoGridFS::find()} method, we
	 * then return a {@link MongoGridFSCursor} instance with the query cursor and
	 * collection.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param bitfield				$theResult			Result type.
	 * @param array					$theFields			Fields selection.
	 * @param array					$theKey				Key offset.
	 *
	 * @access public
	 * @return ObjectIterator		Matched data iterator.
	 */
	public function matchAll( $theCriteria = Array(),
							  $theResult = kQUERY_OBJECT,
							  $theFields = Array(),
							  $theKey = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Handle fields.
			//
			if( count( $theFields ) )
			{
				//
				// Prevent fields if requested object.
				//
				if( ($theResult & kRESULT_MASK) == kQUERY_OBJECT )
					$theFields = Array();
				
				//
				// Convert fields to object.
				// This is necessary since PHP treats numeric indexes as integers.
				//
				else
					$theFields = new \ArrayObject( $theFields );
			
			} // Provided fields selection.
					
			//
			// Get result.
			//
			switch( $theResult & kRESULT_MASK )
			{
				case kQUERY_NID:
					//
					// Set native identifier in fields.
					// This is necessary since PHP treats numeric indexes as integers.
					//
					$theFields = new \ArrayObject( array( kTAG_NID => TRUE ) );
				case kQUERY_OBJECT:
				case kQUERY_ARRAY:
					$cursor
						= $this->
							mConnection->
								find( $theCriteria, $theFields );
					break;
					
				case kQUERY_COUNT:
					return $this->mConnection->find( $theCriteria )->count();		// ==>
			
			} // Parsed result flags.
			
			//
			// Handle no matches.
			//
			if( (! $cursor->count())
			 && ($theResult & kQUERY_ASSERT) )
				throw new \Exception(
					"No matches." );											// !@! ==>
			
			return new MongoFileIterator(
							$cursor,
							$this,
							$theCriteria,
							$theKey,
							$theResult & kRESULT_MASK );							// ==>
		
		} // Connected.
			
		throw new \Exception(
			"Unable to perform query: "
		   ."connection is not open." );										// !@! ==>
	
	} // matchAll.

		

/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isConnected																		*
	 *==================================================================================*/

	/**
	 * Check if connection is open
	 *
	 * We overload this method to assume the object is connected if the resource is a
	 * {@link MongoGridFS}.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		return ( $this->mConnection instanceof \MongoGridFS );						// ==>
	
	} // isConnected.

		

/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	connectionOpen																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * We overload this method to create a <tt>MongoGridFS</tt> connection.
	 *
	 * @access protected
	 * @return mixed				The native connection.
	 *
	 * @throws Exception
	 */
	protected function connectionOpen()
	{
		//
		// Check parent.
		//
		if( $this->mParent instanceof MongoDatabase )
		{
			//
			// Connect database.
			//
			if( ! $this->mParent->isConnected() )
				$this->mParent->openConnection();
			
			//
			// Check collection name.
			//
			if( $this->offsetExists( kTAG_CONN_COLL ) )
				$this->mConnection
					= new \MongoGridFS(
						$this->mParent->connection(),
						$this->offsetGet( kTAG_CONN_COLL ) );
			
			else
				throw new \Exception(
					"Unable to open connection: "
				   ."Missing collection name." );								// !@! ==>
			
			//
			// Instantiate collection.
			//
			return $this->mConnection;												// ==>
		
		} // Server set.
			
		throw new \Exception(
			"Unable to open connection: "
		   ."Missing database." );												// !@! ==>
	
	} // connectionOpen.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	insertData																		*
	 *==================================================================================*/

	/**
	 * Insert provided data
	 *
	 * We override this method to assert that the provided data is an instance of the
	 * {@link MongoFileObject} class.
	 *
	 * @param reference				$theData			Data to commit.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier.
	 */
	protected function insertData( &$theData, $theOptions )
	{
		//
		// Assert data type.
		//
		if( $theData instanceof MongoFileObject )
		{
			//
			// Init local storage.
			//
			$file = $theData->getFile();
			
			//
			// Serialise metadata.
			//
			ContainerObject::Object2Array( $theData, $data );
			
			//
			// Store by reference.
			//
			if( $file instanceof \SplFileInfo )
				$id = $this->connection()->storeFile(
						$file->getRealPath(), $data, $theOptions );
			
			//
			// Store by upload.
			//
			elseif( is_array( $file ) )
			{
				//
				// Check format.
				//
				if( array_key_exists( 'name', $file ) )
					$id = $this->connection()->storeUpload(
							$file[ 'name' ], $data );
				
				else
					throw new \Exception(
						"Unable to insert data: "
					   ."invalid upload file reference format." );				// !@! ==>
			}
			
			//
			// Store by content.
			//
			else
				$id = $this->connection()->storeBytes(
						(string) $file, $data, $theOptions );
			
			//
			// Reload object.
			//
			$object = $this->matchID( $id, TRUE );
			
			//
			// Set object.
			//
			$theData->setObject( $object );
			
			//
			// Clear file.
			//
			$theData->clearFile();
			
			//
			// Load metadata.
			//
			foreach( $object->file as $key => $value )
				$theData[ $key ] = $value;
			
			return $id;																// ==>
		
		} // Provided file object. 
			
		throw new \Exception(
			"Unable to insert data: "
		   ."expecting file object." );											// !@! ==>
	
	} // insertData.

	 
	/*===================================================================================
	 *	replaceData																		*
	 *==================================================================================*/

	/**
	 * Save or replace provided data
	 *
	 * In this class we save the provided array, update the object's {@link kTAG_CLASS} and
	 * return its {@link kTAG_NID} value.
	 *
	 * @param reference				$theData			Data to save.
	 * @param array					$theOptions			Replace options.
	 *
	 * @access protected
	 * @return mixed				Object identifier.
	 */
	protected function replaceData( $theData, $theOptions )
	{
		//
		// Serialise object.
		//
		ContainerObject::Object2Array( $theData, $data );
		
		//
		// Set class.
		//
		if( $theData instanceof PersistentObject )
			$data[ kTAG_CLASS ] = get_class( $theData );
		
		//
		// Replace.
		//
		$ok = $this->mConnection->save( $data, $theOptions );
		
		//
		// Get identifier.
		//
		$id = $data[ kTAG_NID ];
		
		//
		// Set identifier.
		//
		$theData[ kTAG_NID ] = $id;
		
		return $id;																	// ==>
	
	} // replaceData.

	 
	/*===================================================================================
	 *	deleteIdentifier																*
	 *==================================================================================*/

	/**
	 * Delete provided identifier
	 *
	 * This method should be implemented by concrete derived classes, it should delete the
	 * object matched by the provided identifier, if the object was matched, the method
	 * should return the identifier, if not, it should return <tt>NULL</tt>.
	 *
	 * @param mixed					$theIdentifier		Object identifier.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier or <tt>NULL</tt>.
	 */
	protected function deleteIdentifier( $theIdentifier, $theOptions = Array() )
	{
		//
		// Normalise options.
		//
		if( ! is_array( $theOptions ) )
			$theOptions = Array();
		
		//
		// Set only one option.
		//
		$theOptions[ "justOne" ] = TRUE;
		
		//
		// Delete object.
		//
		$ok = $this->mConnection->remove( array( kTAG_NID => $theIdentifier ),
										  $theOptions );
		
		return ( $ok[ 'n' ] > 0 )
			 ? $theIdentifier														// ==>
			 : NULL;																// ==>
	
	} // deleteIdentifier.

	 

} // class MongoFileCollection.


?>
