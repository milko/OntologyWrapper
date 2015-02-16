<?php

/**
 * FileCollection.php
 *
 * This file contains the definition of the {@link FileCollection} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\CollectionObject;

/*=======================================================================================
 *																						*
 *									FileCollection.php									*
 *																						*
 *======================================================================================*/

/**
 * File collection
 *
 * This <i>abstract</i> class is the ancestor of all classes representing collection
 * instances managing files. The class is derived from the {@link CollectionObject} class
 * since it should provide the same functionality, but it also extends it to provide
 * a specific interface for file management.
 *
 * Files are tagged by metadata, this is the same as the data in persistent objects, but
 * includes default tags which do not belong to the realm of Tag objects:
 *
 * <ul>
 *	<li><tt>{@link kTAG_FILE_UPLOAD_DATE}</tt>: The date when the file was stored.
 *	<li><tt>{@link kTAG_FILE_LENGTH}</tt>: The file size in bytes.
 *	<li><tt>{@link kTAG_FILE_CHUNK_SIZE}</tt>: The file chunks size in bytes.
 *	<li><tt>{@link kTAG_FILE_MD5}</tt>: The file contents MD5 checksum.
 *	<li><tt>{@link kTAG_FILE_NAME}</tt>: The path to the file, this represents both the
 *		input and output file path.
 *	<li><tt>{@link kTAG_FILE_MIME_TYPE}</tt>: The file MIME type.
 *	<li><tt>{@link kTAG_FILE_ALIASES}</tt>: A list of aliases for the file.
 * </ul>
 *
 * The first four are automatically set when data is stored, the fifth when a file path was
 * provided, this data represents the default properties of a file, which can be augmented
 * with any property defined in the ontology.
 *
 * As for its ancestor, this class does not implement specific protocols, it is the duty
 * of concrete derived classes to do so.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/02/2015
 */
abstract class FileCollection extends CollectionObject
{
		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	saveFile																		*
	 *==================================================================================*/

	/**
	 * Save a file
	 *
	 * This method will store the provided file in the current collection.
	 *
	 * This method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFile</b>: The file reference in the following formats:
	 *	  <ul>
	 *		<li><tt>string</tt>: The file path.
	 *		<li><tt>SplFileInfo</tt>: The file information object.
	 *	  </ul>
	 *	<li><b>$theMetadata</b>: An array of key/values representing the file's metadata,
	 *		this metadata can later be used to select files from the collection. This
	 *		metadata falls into three main categories:
	 *	  <ul>
	 *		<li><em>Reserved properties</em>: These are the properties that the operation
	 *			will automatically set and that will be available for querying, you should
	 *			not provide metadata with the following tags:
	 *		  <ul>
	 *			<li><tt>{@link kTAG_FILE_MD5}</tt>: The MD5 checksum of the file contents.
	 *			<li><tt>{@link kTAG_FILE_LENGTH}</tt>: The lenght in bytes of the file.
	 *			<li><tt>{@link kTAG_FILE_CHUNK_SIZE}</tt>: The lenght in bytes of the file
	 *				chunks.
	 *			<li><tt>{@link kTAG_FILE_NAME}</tt>: The path of the file as it was provided
	 *				in this method.
	 *		  </ul>
	 *		<li><em>Default properties</em>: These are the properties that the collection
	 *			expects by default, these are independent of the ontology tags and can be
	 *			used to qualify the file independently from the ontology:
	 *		  <ul>
	 *			<li><tt>{@link kTAG_FILE_MIME_TYPE}</tt>: The MIME type of the file.
	 *			<li><tt>{@link kTAG_FILE_ALIASES}</tt>: A list of alias strings for the
	 *				file.
	 *		  </ul>
	 *		<li><em>Tags</em>: All existing ontology tags may be used as in structures
	 *			derived from the {@link  PersistentObject} class.
	 *	  </ul>
	 *	<li><b>$theOptions</b>: An array of key/values holding the operation options, the
	 *		content and nature of these values is dependent on the specific derived
	 *		implementation.
	 * </ul>
	 *
	 * The method will return the inserted object's identifier, {@link kTAG_NID}.
	 *
	 * @param mixed					$theFile			File path or reference.
	 * @param array					$theMetadata		File metadata.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access public
	 * @return mixed				Inserted object identifier.
	 *
	 * @throws Exception
	 *
	 * @uses isConnected()
	 * @uses storeFile()
	 */
	public function saveFile( $theFile, $theMetadata = Array(), $theOptions = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Get file reference.
			//
			if( ! ($theFile instanceof \SplFileInfo) )
				$theFile = new \SplFileInfo( (string) $theFile );
			
			//
			// Check if redable.
			//
			if( $theFile->isReadable() )
			{
				//
				// Normalise metadata.
				//
				if( count( $theMetadata ) )
				{
					//
					// Init local storage.
					//
					$excluded
						= array( kTAG_FILE_MD5,
								 kTAG_FILE_NAME,
								 kTAG_FILE_LENGTH, kTAG_FILE_CHUNK_SIZE );
				
					//
					// Clean metadata.
					//
					foreach( $excluded as $exclude )
					{
						if( array_key_exists( $exclude, $theMetadata ) )
							unset( $theMetadata[ $exclude ] );
					}
	
				} // Provided metadata.
		
				//
				// Set MIME type.
				//
				if( ! array_key_exists( kTAG_FILE_MIME_TYPE, $theMetadata ) )
					$theMetadata[ kTAG_FILE_MIME_TYPE ]
						= mime_content_type( $theFile->getRealPath() );
				
				return $this->storeFile( $theFile, $theMetadata, $theOptions );		// ==>
			
			} // File is readable.
			
			throw new \Exception(
				"Unable to save file: "
			   ."the file is not readable." );									// !@! ==>
			
		} // Connected.
			
		throw new \Exception(
			"Unable to save file: "
		   ."connection is not open." );										// !@! ==>
	
	} // saveFile.

	 
	/*===================================================================================
	 *	saveUpload																		*
	 *==================================================================================*/

	/**
	 * Save an upload
	 *
	 * This method will store an uploaded file in the current collection.
	 *
	 * This method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFile</b>: The name of the uploaded file(s) to store. This should
	 *		correspond to the file field's name attribute in the HTML form.
	 *	<li><b>$theMetadata</b>: An array of key/values representing the file's metadata,
	 *		this metadata can later be used to select files from the collection. This
	 *		metadata falls into three main categories:
	 *	  <ul>
	 *		<li><em>Reserved properties</em>: These are the properties that the operation
	 *			will automatically set and that will be available for querying, you should
	 *			not provide metadata with the following tags:
	 *		  <ul>
	 *			<li><tt>{@link kTAG_FILE_MD5}</tt>: The MD5 checksum of the file contents.
	 *			<li><tt>{@link kTAG_FILE_LENGTH}</tt>: The lenght in bytes of the file.
	 *			<li><tt>{@link kTAG_FILE_CHUNK_SIZE}</tt>: The lenght in bytes of the file
	 *				chunks.
	 *		  </ul>
	 *		<li><em>Default properties</em>: These are the properties that the collection
	 *			expects by default, these are independent of the ontology tags and can be
	 *			used to qualify the file independently from the ontology:
	 *		  <ul>
	 *			<li><tt>{@link kTAG_FILE_NAME}</tt>: If you omut this parameter it will be
	 *				filled with the path to the uploaded file, bu you may relpace that
	 *				value with another path of your choice.
	 *			<li><tt>{@link kTAG_FILE_MIME_TYPE}</tt>: The MIME type of the file; may be
	 *				already set by the method.
	 *			<li><tt>{@link kTAG_FILE_ALIASES}</tt>: A list of alias strings for the
	 *				file.
	 *		  </ul>
	 *		<li><em>Tags</em>: All existing ontology tags may be used as in structures
	 *			derived from the {@link  PersistentObject} class.
	 *	  </ul>
	 *	<li><b>$theOptions</b>: An array of key/values holding the operation options, the
	 *		content and nature of these values is dependent on the specific derived
	 *		implementation.
	 * </ul>
	 *
	 * The method will return the inserted object's identifier, {@link kTAG_NID}.
	 *
	 * @param string				$theFile			HTML form attribute name.
	 * @param array					$theMetadata		File metadata.
	 *
	 * @access public
	 * @return mixed				Inserted object identifier.
	 *
	 * @throws Exception
	 *
	 * @uses isConnected()
	 * @uses storeUpload()
	 */
	public function saveUpload( $theFile, $theMetadata = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Normalise metadata.
			//
			if( count( $theMetadata ) )
			{
				//
				// Init local storage.
				//
				$excluded
					= array( kTAG_FILE_MD5,
							 kTAG_FILE_LENGTH, kTAG_FILE_CHUNK_SIZE );
			
				//
				// Clean metadata.
				//
				foreach( $excluded as $exclude )
				{
					if( array_key_exists( $exclude, $theMetadata ) )
						unset( $theMetadata[ $exclude ] );
				}
		
			} // Provided metadata.
				
			return $this->storeUpload( $theFile, $theMetadata, $theOptions );		// ==>
			
		} // Connected.
			
		throw new \Exception(
			"Unable to save upload: "
		   ."connection is not open." );										// !@! ==>
	
	} // saveUpload.

	 
	/*===================================================================================
	 *	saveData																		*
	 *==================================================================================*/

	/**
	 * Save data
	 *
	 * This method will store the provided data in the current collection.
	 *
	 * This method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theData</b>: The string of bytes to be stored.
	 *	<li><b>$theMetadata</b>: An array of key/values representing the metadata, this
	 *		metadata can later be used to select the stored data from the collection. This
	 *		metadata falls into three main categories:
	 *	  <ul>
	 *		<li><em>Reserved properties</em>: These are the properties that the operation
	 *			will automatically set and that will be available for querying, you should
	 *			not provide metadata with the following tags:
	 *		  <ul>
	 *			<li><tt>{@link kTAG_FILE_MD5}</tt>: The MD5 checksum of the file contents.
	 *			<li><tt>{@link kTAG_FILE_LENGTH}</tt>: The lenght in bytes of the file.
	 *			<li><tt>{@link kTAG_FILE_CHUNK_SIZE}</tt>: The lenght in bytes of the file
	 *				chunks.
	 *		  </ul>
	 *		<li><em>Default properties</em>: These are the properties that the collection
	 *			expects by default, these are independent of the ontology tags and can be
	 *			used to qualify the file independently from the ontology:
	 *		  <ul>
	 *			<li><tt>{@link kTAG_FILE_NAME}</tt>: The file name; note that this may later
	 *				be used to actually save a physical file in that location.
	 *			<li><tt>{@link kTAG_FILE_MIME_TYPE}</tt>: The MIME type of the file.
	 *			<li><tt>{@link kTAG_FILE_ALIASES}</tt>: A list of alias strings for the
	 *				file.
	 *		  </ul>
	 *		<li><em>Tags</em>: All existing ontology tags may be used as in structures
	 *			derived from the {@link  PersistentObject} class.
	 *	  </ul>
	 *	<li><b>$theOptions</b>: An array of key/values holding the operation options, the
	 *		content and nature of these values is dependent on the specific derived
	 *		implementation.
	 * </ul>
	 *
	 * The method will return the inserted object's identifier, {@link kTAG_NID}.
	 *
	 * @param mixed					$theData			Data bytes string.
	 * @param array					$theMetadata		File metadata.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access public
	 * @return mixed				Inserted object identifier.
	 *
	 * @throws Exception
	 *
	 * @uses isConnected()
	 * @uses storeData()
	 */
	public function saveData( $theData, $theMetadata = Array(), $theOptions = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Cast data.
			//
			$theData = (string) $theData;
			
			//
			// Normalise metadata.
			//
			if( count( $theMetadata ) )
			{
				//
				// Init local storage.
				//
				$excluded
					= array( kTAG_FILE_MD5, kTAG_FILE_LENGTH, kTAG_FILE_CHUNK_SIZE );
			
				//
				// Clean metadata.
				//
				foreach( $excluded as $exclude )
				{
					if( array_key_exists( $exclude, $theMetadata ) )
						unset( $theMetadata[ $exclude ] );
				}
		
			} // Provided metadata.
			
			return $this->storeData( $theData, $theMetadata, $theOptions );			// ==>
			
		} // Connected.
			
		throw new \Exception(
			"Unable to save data: "
		   ."connection is not open." );										// !@! ==>
	
	} // saveData.

		

/*=======================================================================================
 *																						*
 *								PUBLIC DELETION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	deleteByCriteria																*
	 *==================================================================================*/

	/**
	 * Delete selection
	 *
	 * This method should remove all objects matching the provided criteria.
	 *
	 * @param array					$theCriteria		Object selection criteria.
	 * @param array					$theOptions			Delete options.
	 *
	 * @access public
	 * @return mixed				Operation status.
	 *
	 * @throws Exception
	 *
	 * @uses isConnected()
	 * @uses deleteSelection()
	 */
	public function deleteByCriteria( $theCriteria, $theOptions = Array() )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
			return $this->deleteSelection( $theCriteria, $theOptions );				// ==>
			
		throw new \Exception(
			"Unable to delete objects: "
		   ."connection is not open." );										// !@! ==>
	
	} // deleteByCriteria.

		

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
	 * This method should select a single object matched by the provided native identifier.
	 *
	 * Concrete derived classes should implement this method.
	 *
	 * @param mixed					$theIdentifier		Object native identifier.
	 *
	 * @access public
	 * @return mixed				Matched object or <tt>NULL</tt>.
	 */
	abstract public function matchID( $theIdentifier );

	 
	/*===================================================================================
	 *	matchOne																		*
	 *==================================================================================*/

	/**
	 * Match one object
	 *
	 * This method should select a single object according to the provided criteria, the
	 * method will return an object dependant on the derived concrete class implementation.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: This parameter represents the selection criteria, this
	 *		value is an array which represents a query expressed in the MongoDB query
	 *		language.
	 *	<li><b>$theResult</b>: This parameter determines what the method should return, it
	 *		is a bitfield which accepts two sets of values:
	 *	 <ul>
	 *		<li><tt>{@link kQUERY_ASSERT}</tt>: If this flag is set and the criteria doesn't
	 *			match any record, the method should raise an exception.
	 *		<li><em>Result type</em>: This set of values can be added to the previous flag,
	 *			only one of these should be provided:
	 *		 <ul>
	 *			<li><tt>{@link kQUERY_OBJECT}</tt>: Return the matched file object.
	 *			<li><tt>{@link kQUERY_NID}</tt>: Return the matched object native
	 *				identifier.
	 *			<li><tt>{@link kQUERY_COUNT}</tt>: Return the number of matched objects.
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theFields</b>: This parameter represents the fields selection, it is an
	 *		array indexed by offset with a boolean value indicating whether or not to
	 *		include the field.
	 * </ul>
	 *
	 * If you omit the second parameter, the method should return the matched object.
	 *
	 * If there is more than one match for the provided criteria, this method will return
	 * only the first one, in no particular order.
	 *
	 * If there is no match, the method will return <tt>NULL</tt> if the
	 * {@link kQUERY_ASSERT} flag was <em>not</em> set, or raise an exception.
	 *
	 * Concrete derived classes should implement this method.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param bitfield				$theResult			Result type.
	 * @param array					$theFields			Fields selection.
	 *
	 * @access public
	 * @return mixed				Matched data or <tt>NULL</tt>.
	 */
	abstract public function matchOne( $theCriteria,
									   $theResult = kQUERY_DEFAULT,
									   $theFields = Array() );

	 
	/*===================================================================================
	 *	matchAll																		*
	 *==================================================================================*/

	/**
	 * Match all objects
	 *
	 * This method should select the set of objects matching the provided criteria, the
	 * method should return an object implementing the {@link Iterator}, {@link Countable}
	 * and {iCursor} interfaces.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: This parameter represents the selection criteria, this
	 *		value is an array which represents a query expressed in the MongoDB query
	 *		language.
	 *	<li><b>$theResult</b>: This parameter will be passed to the iterator returned by the
	 *		method, it determines what kind of data the iterator will return. This parameter
	 *		is a bitfield which accepts two sets of values:
	 *	 <ul>
	 *		<li><tt>{@link kQUERY_ASSERT}</tt>: If this flag is set and the criteria doesn't
	 *			match any record, the method should raise an exception.
	 *		<li><em>Result type</em>: This set of values can be added to the previous flag,
	 *			only one of these should be provided:
	 *		 <ul>
	 *			<li><tt>{@link kQUERY_OBJECT}</tt>: Return a file object iterator (default).
	 *			<li><tt>{@link kQUERY_NID}</tt>: Return an identifier iterator.
	 *		 </ul>
	 *			Any other value will trigger an exception.
	 *	 </ul>
	 *	<li><b>$theFields</b>: This parameter represents the fields selection, it is an
	 *		array indexed by offset with a boolean value indicating whether or not to
	 *		include the field.
	 *	<li><b>$theKey</b>: This parameter represents the iterator key offset, it can be
	 *		used to set which value the {@link key()} function should return: the value is
	 *		the offset that will be used to get the key value.
	 * </ul>
	 *
	 * If you omit the second parameter, the the iterator returned by this method will
	 * objects.
	 *
	 * Concrete derived classes should implement this method.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param bitfield				$theResult			Result type.
	 * @param array					$theFields			Fields selection.
	 * @param array					$theKey				Key offset.
	 *
	 * @access public
	 * @return ObjectIterator		Matched data iterator.
	 */
	abstract public function matchAll( $theCriteria = Array(),
									   $theResult = kQUERY_DEFAULT,
									   $theFields = Array(),
									   $theKey = NULL );

		

/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	storeFile																		*
	 *==================================================================================*/

	/**
	 * Store file
	 *
	 * This method should store the provided file along with the provided metadata using the
	 * provided options. The method should return the netive identifier of the inserted file
	 * object.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param SplFileInfo			$theFile			File reference.
	 * @param array					$theMetadata		File metadata.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object native identifier.
	 */
	abstract protected function storeFile( \SplFileInfo $theFile,
														$theMetadata,
														$theOptions );

	 
	/*===================================================================================
	 *	storeUpload																		*
	 *==================================================================================*/

	/**
	 * Store uploaded file
	 *
	 * This method should store the file matching the provided name along with the provided
	 * metadata using the provided options. The method should return the netive identifier
	 * of the inserted file  object. The provided file name is the <em>name</em> attribute
	 * of the upload HTML form.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theFile			Name attribute value.
	 * @param array					$theMetadata		File metadata.
	 *
	 * @access protected
	 * @return mixed				Object native identifier.
	 */
	abstract protected function storeUpload( $theFile, $theMetadata );

	 
	/*===================================================================================
	 *	storeData																		*
	 *==================================================================================*/

	/**
	 * Delete provided identifier
	 *
	 * This method should store the provided bytes string along with the provided metadata
	 * using the provided options. The method should return the netive identifier of the
	 * inserted file object.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theData			Data bytes string.
	 * @param array					$theMetadata		File metadata.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object native identifier.
	 */
	abstract protected function storeData( $theData, $theMetadata, $theOptions );

	 
	/*===================================================================================
	 *	deleteSelection																	*
	 *==================================================================================*/

	/**
	 * Delete selection
	 *
	 * This method should delete all objects matching the provided selection.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param array					$theCriteria		Selection criteria.
	 * @param array					$theOptions			Insert options.
	 *
	 * @access protected
	 * @return mixed				Object identifier or <tt>NULL</tt>.
	 */
	abstract protected function deleteSelection( $theCriteria, $theOptions );

	 

} // class FileCollection.


?>
