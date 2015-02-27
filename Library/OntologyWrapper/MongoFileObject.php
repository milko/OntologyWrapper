<?php

/**
 * MongoFileObject.php
 *
 * This file contains the definition of the {@link MongoFileObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\FileObject;
use OntologyWrapper\MongoFileCollection;

/*=======================================================================================
 *																						*
 *									MongoFileObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo file object
 *
 * This class is a {@link FileObject} wrapper for the <tt>MongoGridFSFile</tt> class,
 * {@link MongoFileCollection} objects create instances of this class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 21/02/2015
 */
class MongoFileObject extends FileObject
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
	 * We overload this method to allow instantiating the object from a
	 * <tt>MongoGridFSFile</tt> instance.
	 *
	 * @param mixed					$theContainer		Data wrapper or properties.
	 * @param mixed					$theIdentifier		Object identifier or properties.
	 * @param boolean				$doAssert			Raise exception if not resolved.
	 *
	 * @access public
	 *
	 * @uses isCommitted()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 */
	public function __construct( $theContainer = NULL,
								 $theIdentifier = NULL,
								 $doAssert = TRUE )
	{
		//
		// Handle native object.
		//
		if( ($theContainer instanceof Wrapper)
		 && ($theIdentifier instanceof \MongoGridFSFile) )
		{
			//
			// Extract object components.
			//
			$this->mObject = $theIdentifier;
			
			//
			// Call parent method.
			//
			parent::__construct( $theContainer, $theIdentifier->file );
			
			//
			// Reset dirty flag.
			//
			$this->isDirty( FALSE );
		
		} // Provided object identifier.
		
		else
			parent::__construct( $theContainer, $theIdentifier, $doAssert );

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC FILE DATA INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getContents																		*
	 *==================================================================================*/

	/**
	 * Get the file data
	 *
	 * In this class we use a MongoGridFSFile object.
	 *
	 * @access public
	 * @return string				File contents.
	 */
	public function getContents()					{	return $this->mObject->getBytes();	}

	 
	/*===================================================================================
	 *	getStream																		*
	 *==================================================================================*/

	/**
	 * Get the file data
	 *
	 * In this class we use a MongoGridFSFile object.
	 *
	 * @access public
	 * @return resource				File resource.
	 */
	public function getStream()					{	return $this->mObject->getResource();	}

	 
	/*===================================================================================
	 *	writeFile																		*
	 *==================================================================================*/

	/**
	 * Write the file to disk
	 *
	 * This method should save the file to disk, ither at the path found in the metadata, or
	 * at the path provided to this method; it is the duty of concrete derived classes to
	 * implement this feature.
	 *
	 * @param string				$thePath			The file path or <tt>NULL</tt>.
	 *
	 * @access public
	 * @return int					Written bytes count.
	 */
	public function writeFile( $thePath = NULL )
	{
		return $this->mObject->write( $thePath );									// ==>
	
	} // writeFile.

	 

} // class MongoFileObject.


?>
