<?php

/**
 * MongoDatabase.php
 *
 * This file contains the definition of the {@link MongoDatabase} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\DatabaseObject;
use OntologyWrapper\MongoServer;

/*=======================================================================================
 *																						*
 *									MongoDatabase.php									*
 *																						*
 *======================================================================================*/

/**
 * Mongo database
 *
 * This class is a <i>concrete</i> implementation of the {@link DatabaseObject} wrapping a
 * {@link MongoDB} class.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/02/2014
 */
class MongoDatabase extends DatabaseObject
{
	/**
	 * Sequences collection name.
	 *
	 * This constant holds the <i>sequences</i> collection name.
	 *
	 * @var string
	 */
	const kSEQ_COLLECTION = '_sequence';

	/**
	 * Sequences offset name.
	 *
	 * This constant holds the <i>sequences</i> offset name.
	 *
	 * @var string
	 */
	const kSEQ_OFFSET = '_seq';

		

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
	 * This method will drop the current database.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function drop()
	{
		//
		// Check connection.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to drop database: "
			   ."database is not connected." );									// !@! ==>
		
		$this->mConnection->drop();
	
	} // drop.

		

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
	 * {@link MongoDB}.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		return ( $this->mConnection instanceof \MongoDB );							// ==>
	
	} // isConnected.

	 
	/*===================================================================================
	 *	getCollections																	*
	 *==================================================================================*/

	/**
	 * Return collection names
	 *
	 * In this class we use the {@link \MongoDB::listCollections()} method, we extract only
	 * the name to conform with the method prototype: one should always instantiate an
	 * object derived from {@link CollectionObject} when dealing wit collections.
	 *
	 * This method will return the following retults:
	 *
	 * <ul>
	 *	<li><tt>FALSE</tt>: The database is not connected.
	 *	<li><tt>array</tt>: The database collection names.
	 * </ul>
	 *
	 * @access public
	 * @return array				List of collection names.
	 */
	public function getCollections()
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Extract collection names.
			//
			$names = Array();
			$collections = $this->mConnection->listCollections();
			foreach( $collections as $collection )
				$names[] = $collection->getName();
			
			return $names;															// ==>
		
		} // Connected.
		
		return FALSE;																// ==>
	
	} // getCollections.

	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Return database name
	 *
	 * In this class we return the database name, if the connection is set, or call the
	 * parent method.
	 *
	 * @access public
	 * @return string				Database name.
	 */
	public function getName()
	{
		//
		// Check connection.
		//
		if( $this->mConnection instanceof \MongoDB )
			return (string) $this->mConnection;										// ==>
		
		return parent::getName();													// ==>

	} // getName.

		

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
	 * In this class we match the provided parameter string with an entry in the
	 * {@link kSEQ_COLLECTION} collection in the database, the native identifier of the
	 * record is the sequence selector, while the sequence number will be found at the
	 * {@link kSEQ_OFFSET} offset.
	 *
	 * @param string				$theSequence		Sequence selector.
	 * @param integer				$theNumber			Sequence number.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function setSequenceNumber( $theSequence, $theNumber = 1 )
	{
		//
		// Check connection.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to set sequence: "
			   ."database is not connected." );									// !@! ==>
		
		//
		// Init local storage.
		//
		$collection = $this->mConnection->selectCollection( self::kSEQ_COLLECTION );
		
		//
		// Set sequence.
		//
		$collection->update(
			array( kTAG_NID => (string) $theSequence ),
			array( '$set' => array( self::kSEQ_OFFSET => (int) $theNumber ) ),
			array( 'upsert' => TRUE ) );
	
	} // setSequenceNumber.

	 
	/*===================================================================================
	 *	getSequenceNumber																*
	 *==================================================================================*/

	/**
	 * Return sequence number
	 *
	 * In this class we match the provided parameter string with an entry in the
	 * {@link kSEQ_COLLECTION} collection in the database, the native identifier of the
	 * record is the sequence selector, while the sequence number will be found at the
	 * {@link kSEQ_OFFSET} offset.
	 *
	 * @param string				$theSequence		Sequence selector.
	 *
	 * @access public
	 * @return integer				Sequence number.
	 *
	 * @throws Exception
	 */
	public function getSequenceNumber( $theSequence )
	{
		//
		// Check connection.
		//
		if( ! $this->isConnected() )
			throw new \Exception(
				"Unable to get sequence: "
			   ."database is not connected." );									// !@! ==>
		
		//
		// Init local storage.
		//
		$criteria = array( kTAG_NID => (string) $theSequence );
		$collection = $this->mConnection->selectCollection( self::kSEQ_COLLECTION );
		
		//
		// Locate sequence.
		//
		$seq = $collection->findOne( $criteria );
		
		//
		// Increment sequence.
		//
		if( $seq !== NULL )
			$collection->update( $criteria,
								 array( '$inc' => array( self::kSEQ_OFFSET => 1 ) ),
								 array( 'upsert' => FALSE ) );
		else
			$collection->update( $criteria,
								 array( '$set' => array( self::kSEQ_OFFSET => 2 ) ),
								 array( 'upsert' => TRUE ) );
		
		return ( $seq !== NULL )
			 ? $seq[ self::kSEQ_OFFSET ]											// ==>
			 : 1;																	// ==>
	
	} // getSequenceNumber.

		

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
	 * In this class we return a MongoId; if the provided value is invalid, we return
	 * <tt>NULL</tt>.
	 *
	 * @param string				$theIdentifier		String version of the identifier.
	 *
	 * @access public
	 * @return MongoId				Native cobject identifier or <tt>NULL</tt>.
	 */
	public function getObjectId( $theIdentifier )
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
				return NULL;														// ==>
		
		} // Not a native identifier.
		
		return $theIdentifier;														// ==>
	
	} // getObjectId.

	 
	/*===================================================================================
	 *	setObjectId																		*
	 *==================================================================================*/

	/**
	 * Set object identifier
	 *
	 * In this class we expect a MongoId.
	 *
	 * @param MongoId				$theIdentifier		Native version of the identifier.
	 *
	 * @access public
	 * @return string				Object identifier as a string.
	 */
	public function setObjectId( $theIdentifier )
	{
		//
		// Check identifier.
		//
		if( $theIdentifier instanceof \MongoId )
			return (string) $theIdentifier;											// ==>
		
		$type = ( is_object( $theIdentifier ) )
			  ? get_class( $theIdentifier )
			  : gettype( $theIdentifier );
			
		throw new \Exception(
			"Unable to convert identifier: "
		   ."invalid identifier data type [$type]" );							// !@! ==>
	
	} // getObjectId.

	 
	/*===================================================================================
	 *	getTimeStamp																	*
	 *==================================================================================*/

	/**
	 * Get time-stamp
	 *
	 * In this class we return a MongoDate value.
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
	public function getTimeStamp( $theStamp = NULL )
	{
		//
		// Handle current.
		//
		if( ($theStamp === NULL)
		 || (strtolower( $theStamp ) == 'now') )
			return new \MongoDate();												// ==>
		
		//
		// Handle seconds.
		//
		if( is_int( $theStamp )
		 || ctype_digit( $theStamp ) )
			return new \MongoDate( (int) $theStamp );								// ==>
		
		//
		// Handle string.
		//
		$time = strtotime( (string) $theStamp );
		if( $time !== FALSE )
			return new \MongoDate( $time );											// ==>
		
		return FALSE;																// ==>
	
	} // getTimeStamp.

	 
	/*===================================================================================
	 *	parseTimeStamp																	*
	 *==================================================================================*/

	/**
	 * Get time-stamp
	 *
	 * In this class we convert the time-stamp 
	 *
	 * @param mixed					$theStamp			Time-stamp.
	 *
	 * @access public
	 * @return string				Human readable time-stamp.
	 */
	public function parseTimeStamp( $theStamp )
	{
		//
		// Check type.
		//
		if( $theStamp instanceof \MongoDate )
			return date( "r", $theStamp->sec );										// ==>
		
		return (string) $theStamp;													// ==>
	
	} // parseTimeStamp.

		

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
	 * This method will instantiate a {@link MongoDB} object and set it in the
	 * {@link mConnection()} data member.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * If the operation fails, the method will raise an exception.
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
		if( $this->mParent instanceof MongoServer )
		{
			//
			// Connect server.
			//
			if( ! $this->mParent->isConnected() )
				$this->mParent->openConnection();
			
			//
			// Check database name.
			//
			if( $this->offsetExists( kTAG_CONN_BASE ) )
				$this->mConnection
					= $this->mParent
						->mConnection->selectDB(
							$this->offsetGet( kTAG_CONN_BASE ) );
			
			else
				throw new \Exception(
					"Unable to open connection: "
				   ."Missing database name." );									// !@! ==>
			
			return $this->mConnection;												// ==>
		
		} // Server set.
			
		throw new \Exception(
			"Unable to open connection: "
		   ."Missing server." );												// !@! ==>
	
	} // connectionOpen.

	 
	/*===================================================================================
	 *	connectionClose																	*
	 *==================================================================================*/

	/**
	 * Close connection
	 *
	 * We overload this method to reset the connection resource.
	 *
	 * @access protected
	 */
	protected function connectionClose()					{	$this->mConnection = NULL;	}

		

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
	 * We implement the method to return a {@link MongoServer} instance and set the current
	 * object dictionary in it.
	 *
	 * @param mixed					$theParameter		Server parameters.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access protected
	 * @return MongoServer			Server instance.
	 */
	protected function newServer( $theParameter, $doOpen = TRUE )
	{
		//
		// Instantiate server.
		//
		$server = new MongoServer( $theParameter );
		
		//
		// Set dictionary.
		//
		$server->dictionary( $this->dictionary() );
		
		//
		// Open connection.
		//
		if( $doOpen )
			$server->openConnection();
		
		return $server;																// ==>
	
	} // newServer.

	 
	/*===================================================================================
	 *	newCollection																	*
	 *==================================================================================*/

	/**
	 * Return a new object collection instance
	 *
	 * We implement this method to return a {@link MongoCollection} instance.
	 *
	 * @param array					$theOffsets			Full collection offsets.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access protected
	 * @return MongoCollection		Collection instance.
	 */
	protected function newCollection( $theOffsets, $doOpen = TRUE )
	{
		//
		// Instantiate collection.
		//
		$collection = new MongoCollection( $theOffsets );
		
		//
		// Copy dictionary.
		//
		$collection->dictionary( $this->dictionary() );
		
		//
		// Open connection.
		//
		if( $doOpen )
			$collection->openConnection();
		
		return $collection;															// ==>
	
	} // newCollection.

	 
	/*===================================================================================
	 *	newFiler																		*
	 *==================================================================================*/

	/**
	 * Return a new file collection instance
	 *
	 * We implement this method to return a {@link MongoFileCollection} instance.
	 *
	 * @param array					$theOffsets			Full collection offsets.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @access protected
	 * @return MongoFileCollection	Collection instance.
	 */
	protected function newFiler( $theOffsets, $doOpen = TRUE )
	{
		//
		// Instantiate collection.
		//
		$collection = new MongoFileCollection( $theOffsets );
		
		//
		// Copy dictionary.
		//
		$collection->dictionary( $this->dictionary() );
		
		//
		// Open connection.
		//
		if( $doOpen )
			$collection->openConnection();
		
		return $collection;															// ==>
	
	} // newFiler.

	 

} // class MongoDatabase.


?>
