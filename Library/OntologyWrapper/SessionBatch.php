<?php

/**
 * SessionBatch.php
 *
 * This file contains the definition of the {@link SessionBatch} class.
 */

namespace OntologyWrapper;

/*=======================================================================================
 *																						*
 *								SessionBatch.php										*
 *																						*
 *======================================================================================*/

/**
 * Session batch object
 *
 * This class is the ancestor of all classes implementing batch jobs.
 *
 * The class features a default constructor and destructor that will take care of creating
 * and deleting the batch lock file.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 23/03/2015
 */
class SessionBatch
{
	/**
	 * Lock file.
	 *
	 * This data member holds the <i>lock file path</i>.
	 *
	 * @var string
	 */
	protected $mLockFile = NULL;

		

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
	 * The constructor will instantiate the class with a prefix and suffix used to create
	 * the lock file name.
	 *
	 * @param string				$theUser			User identifier.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function __construct( $theUser )
	{
		//
		// Get file path.
		//
		$this->mLockFile = static::LockFilePath( $theUser );
		
		//
		// Create file.
		//
		$fs = fopen( $this->mLockFile, "w+" );
		if( $fs )
			fclose( $fs );
		
		//
		// Handle errors.
		//
		else
			throw new \Exception( "Unable to create lock file." );				// !@! ==>

	} // Constructor.

	 
	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * Destruct class.
	 *
	 * This method will delete the lock file if it still exists.
	 *
	 * @access public
	 */
	public function __destruct()
	{
		//
		// Check file.
		//
		if( file_exists( $this->mLockFile ) )
			unlink( $this->mLockFile );
		
		//
		// Reset member.
		//
		$this->mLockFile = NULL;

	} // Destructor.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getLockFile																		*
	 *==================================================================================*/

	/**
	 * Return the lock file
	 *
	 * This method will return the lock file path.
	 *
	 * @access public
	 * @return string				The lock file path.
	 */
	public function getLockFile()							{	return $this->mLockFile;	}

		

/*=======================================================================================
 *																						*
 *									STATIC INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	LockFilePath																	*
	 *==================================================================================*/

	/**
	 * Return the lock file path
	 *
	 * @param string				$theUser			User identifier.
	 *
	 * This method will return the lock file path according to the provided parameters.
	 *
	 * @static
	 * @return string				The lock file path.
	 */
	static function LockFilePath( $theUser )
	{
		//
		// Create name.
		//
		$name = kPORTAL_PREFIX.md5( $theUser ).".lock";
		
		//
		// Create base path.
		//
		$path = sys_get_temp_dir();
		if( substr( $path, strlen( $path ) - 1, 1 ) != '/' )
			$path .= '/';
		
		return $path.$name;															// ==>
	
	} // LockFilePath.

	 

} // class SessionBatch.


?>
