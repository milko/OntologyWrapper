<?php

/**
 * DatabaseObject.php
 *
 * This file contains the definition of the {@link DatabaseObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;
use OntologyWrapper\ServerObject;

/*=======================================================================================
 *																						*
 *									DatabaseObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Database object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing database
 * connection instances, this class extends the {@link ConnectionObject} class to implement
 * database specific functionality prototypes.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
abstract class DatabaseObject extends ConnectionObject
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
	 * We overload the constructor to instantiate a server from the provided parameter, if
	 * the parent object was not provided, and set it as the parent.
	 *
	 * @param mixed					$theParameter		Data source name or parameters.
	 * @param ConnectionObject		$theParent			Connection parent.
	 *
	 * @access public
	 *
	 * @uses ServerObject::DefaultOffsets()
	 *
	 * @uses newServer()
	 */
	public function __construct( $theParameter = NULL, $theParent = NULL )
	{
		//
		// Call parent constructor.
		//
		parent::__construct( $theParameter, $theParent );

		//
		// Create parent.
		//
		if( ($theParameter !== NULL)
		 && (! ($theParent instanceof ConnectionObject)) )
		{
			//
			// Get server parameters.
			//
			$params = Array();
			foreach( ServerObject::DefaultOffsets() as $offset )
			{
				if( $this->offsetExists( $offset ) )
					$params[ $offset ] =
						$this->offsetGet( $offset );
			
			} // Extracting server parameters.
			
			//
			// Instantiate server.
			//
			$this->mParent = $this->newServer( $params );
		
		} // Missing parent.
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	drop																			*
	 *==================================================================================*/

	/**
	 * Drop the database
	 *
	 * This method should drop the current database.
	 *
	 * @access public
	 */
	abstract public function drop();

		

/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	collection																		*
	 *==================================================================================*/

	/**
	 * Return collection connection
	 *
	 * This method can be used to return a collection connection from the current database.
	 *
	 * The method expects a single parameter which represents the collection name, the
	 * method should return an instance of a class derived from {@link CollectionObject}.
	 *
	 * @param string				$theName			Collection name.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access public
	 * @return CollectionObject		Collection object.
	 *
	 * @uses newCollection()
	 */
	public function collection( $theName, $doOpen = TRUE )
	{
		//
		// Get current database parameters.
		//
		$params = $this->getArrayCopy();
		
		//
		// Add collection name.
		//
		$params[ kTAG_CONN_COLL ] = $theName;
		
		//
		// Instantiate collection.
		//
		$collection = $this->newCollection( $params, $doOpen );
		
		//
		// Set data dictionary.
		//
		$collection->dictionary( $this->dictionary() );
		
		return $collection;															// ==>
	
	} // collection.

	 
	/*===================================================================================
	 *	filer																			*
	 *==================================================================================*/

	/**
	 * Return file connection
	 *
	 * This method can be used to return a file collection connection from the current
	 * database, it uses its {@link DatabaseObject::filer()} method.
	 *
	 * By default the collection name will be {@link FileObject::kSEQ_NAME}.
	 *
	 * The method parameter determines whether the connection should be opened.
	 *
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access public
	 * @return CollectionObject		Filer object.
	 *
	 * @uses newFiler()
	 */
	public function filer( $doOpen = TRUE )
	{
		//
		// Get current database parameters.
		//
		$params = $this->getArrayCopy();
		
		//
		// Add collection name.
		//
		$params[ kTAG_CONN_COLL ] = FileObject::kSEQ_NAME;
		
		//
		// Instantiate collection.
		//
		$collection = $this->newFiler( $params, $doOpen );
		
		//
		// Set data dictionary.
		//
		$collection->dictionary( $this->dictionary() );
		
		return $collection;															// ==>
	
	} // filer.

	 
	/*===================================================================================
	 *	getCollections																	*
	 *==================================================================================*/

	/**
	 * Return collection names
	 *
	 * This method should return the list of collection names of the current database, the
	 * method should return the following retults:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: The operation is not supported.
	 *	<li><tt>FALSE</tt>: The database is not connected.
	 *	<li><tt>array</tt>: The database collection names.
	 * </ul>
	 *
	 * We implement the method in this class as a fall-back.
	 *
	 * @access public
	 * @return array				Server statistics or <tt>NULL</tt> if unsupported.
	 */
	public function getCollections()									{	return NULL;	}

	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Return database name
	 *
	 * This method should return the database name:
	 *
	 * We implement the method in this class as a fall-back.
	 *
	 * @access public
	 * @return string				Database name.
	 */
	public function getName()											{	return NULL;	}

		

/*=======================================================================================
 *																						*
 *							PUBLIC SEQUENCE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setSequenceNumber																*
	 *==================================================================================*/

	/**
	 * Set sequence number
	 *
	 * This method should initialise a sequence number associated to the provided parameter.
	 * This operation is equivalent to resetting an auto-number for a database.
	 *
	 * Once the sequence is set, the next requested sequence number will hold the value set
	 * by this method, so to start counting from <tt>1</tt> you should provide this value to
	 * this method.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param string				$theSequence		Sequence selector.
	 * @param integer				$theNumber			Sequence number.
	 *
	 * @access public
	 */
	abstract public function setSequenceNumber( $theSequence, $theNumber = 1 );

	 
	/*===================================================================================
	 *	getSequenceNumber																*
	 *==================================================================================*/

	/**
	 * Return sequence number
	 *
	 * This method should return a sequence number associated to the provided parameter.
	 * This operation is equivalent to requesting an auto-number for a database.
	 *
	 * Each time a sequence number is requested, the sequence seed is updated, so use this
	 * method only when the sequence is required.
	 *
	 * If the sequence selector is not found, a new one will be created starting with the
	 * number <tt>1</tt>, so, if you need to start with another number, use the
	 * {@link setSequenceNumber()} before.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param string				$theSequence		Sequence selector.
	 *
	 * @access public
	 * @return integer				Sequence number.
	 */
	abstract public function getSequenceNumber( $theSequence );

		

/*=======================================================================================
 *																						*
 *									PUBLIC TYPE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getObjectId																		*
	 *==================================================================================*/

	/**
	 * Get object identifier
	 *
	 * This method should return a native object identifier, given a string.
	 *
	 * @param string				$theIdentifier		String version of the identifier.
	 *
	 * @access public
	 * @return mixed				Native cobject identifier.
	 */
	abstract public function getObjectId( $theIdentifier );

	 
	/*===================================================================================
	 *	setObjectId																		*
	 *==================================================================================*/

	/**
	 * Set object identifier
	 *
	 * This method should return an object identifier string, given a native type.
	 *
	 * @param mixed					$theIdentifier		Native version of the identifier.
	 *
	 * @access public
	 * @return string				Object identifier as a string.
	 */
	abstract public function setObjectId( $theIdentifier );

	 
	/*===================================================================================
	 *	getTimeStamp																	*
	 *==================================================================================*/

	/**
	 * Get time-stamp
	 *
	 * This method should return the current time-stamp in the native database format.
	 *
	 * If the provided parameter is numeric or an integer, the method will assume it is the
	 * number of seconds since the epoch (Jan 1970 00:00:00.000 UTC); if not, it will use
	 * the strtotime() function, if the function was not able to convert the time, the
	 * method will return <tt>FALSE</tt>:
	 *
	 * If you omit the parameter, or pass <tt>now</tt>, the method will return the current
	 * time stamp.
	 *
	 * @param mixed					$theStamp			Unix timestamp or string.
	 *
	 * @access public
	 * @return mixed				Native current time-stamp or <tt>FALSE</tt>.
	 */
	abstract public function getTimeStamp( $theStamp = NULL );

	 
	/*===================================================================================
	 *	parseTimeStamp																	*
	 *==================================================================================*/

	/**
	 * Get time-stamp
	 *
	 * This method should return a formatted time stamp string.
	 *
	 * @param mixed					$theStamp			Time-stamp.
	 *
	 * @access public
	 * @return string				Human readable time-stamp.
	 */
	abstract public function parseTimeStamp( $theStamp );

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DefaultOffsets																	*
	 *==================================================================================*/

	/**
	 * Return default offsets
	 *
	 * In this class we return the {@link kTAG_CONN_BASE} offset.
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_CONN_BASE ) );								// ==>
	
	} // DefaultOffsets;

		

/*=======================================================================================
 *																						*
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newServer																		*
	 *==================================================================================*/

	/**
	 * Return a new server instance
	 *
	 * This method should be implemented by concrete derived classes, it expects a list of
	 * offsets or a data source name containing the necessary elements to instantiate a
	 * {@link ServerObject} instance which will be considered the current object's parent.
	 *
	 * Derived classes must implement this method.
	 *
	 * <em>When implementing this method you should not forget to set the dictionary</em>.
	 *
	 * @param mixed					$theParameter		Server parameters.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access protected
	 * @return ServerObject			Server instance.
	 */
	abstract protected function newServer( $theParameter, $doOpen = TRUE );

	 
	/*===================================================================================
	 *	newCollection																	*
	 *==================================================================================*/

	/**
	 * Return a new collection instance
	 *
	 * This method should be implemented by concrete derived classes, it expects a list of
	 * offsets which include database information and should use them to instantiate a
	 * {@link CollectionObject} instance.
	 *
	 * Derived classes must implement this method.
	 *
	 * <em>When implementing this method you should not forget to set the dictionary</em>.
	 *
	 * @param array					$theOffsets			Full collection offsets.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access protected
	 * @return CollectionObject		Collection instance.
	 */
	abstract protected function newCollection( $theOffsets, $doOpen = TRUE );

	 
	/*===================================================================================
	 *	newFiler																		*
	 *==================================================================================*/

	/**
	 * Return a new filer instance
	 *
	 * This method should be implemented by concrete derived classes, it expects a list of
	 * offsets which include database information and should use them to instantiate a
	 * file collection instance.
	 *
	 * Derived classes must implement this method.
	 *
	 * <em>When implementing this method you should not forget to set the dictionary</em>.
	 *
	 * @param array					$theOffsets			Full collection offsets.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access protected
	 * @return CollectionObject		Collection instance.
	 */
	abstract protected function newFiler( $theOffsets, $doOpen = TRUE );

	 

} // class DatabaseObject.


?>
