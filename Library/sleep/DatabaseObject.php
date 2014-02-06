<?php

/**
 * DatabaseObject.php
 *
 * This file contains the definition of the {@link DatabaseObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;

/*=======================================================================================
 *																						*
 *									DatabaseObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Database object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing database server
 * instances.
 *
 * Derived classes can add specialised data members and methods to handle collections.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 24/01/2014
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
	 * We overload the parent constructor to instantiate a {@link ServerObject} instance
	 * before instantiating the current class.
	 *
	 * If the parent was not provided, we use the {@link kTAG_CONN_HOST} and
	 * {@link kTAG_CONN_PORT} offsets to instantiate the parent server, this means that if
	 * you need more server parameters you should first instantiate the server separately
	 * and then pass it to the constructor as the parent.
	 *
	 * If the parent was provided, the method will first instantiate it, them pass it as the
	 * parent to the constructor.
	 *
	 * @param mixed					$theParameter		Data source name or parameters.
	 * @param ConnectionObject		$theParent			Connection parent.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses instantiateParent()
	 */
	public function __construct( $theParameter = NULL, $theParent = NULL )
	{
		//
		// Instantiate parent.
		//
		if( $theParent === NULL )
			$theParent = $this->instantiateParent( $theParameter );
		
		parent::__construct( $theParameter, $theParent );

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Collection																		*
	 *==================================================================================*/

	/**
	 * Return collection connection
	 *
	 * As for the constructor, this method expects a parameter in the same format as the
	 * constructor's first parameter, the parent is assumed to be the current database.
	 *
	 * When providing a data source name, you should only provide the collection name, the
	 * method will place it as the <code>fragment</code> of the URL, so:
	 *
	 * <code>collection</code>
	 *
	 * which represents the collection name to be passed to this method will become
	 *
	 * <code>driver://user:pass@host:port/database?opt1=val1&opt2=val2#collection</code>
	 *
	 * when passed to the collection constructor.
	 *
	 * Note that the <code>user</code> and <code>pass</code> are the credentials that will
	 * be used to access the collection, not the database and the <code>collection</code>
	 * name is placed in the URL <code>fragment</code>.
	 *
	 * The method will set the current object's {@link kTAG_CONN_NAME} in the collection's
	 * {@link kTAG_CONN_BASE} offset, and the collection name will be set in the
	 * {@link kTAG_CONN_NAME} offset.
	 *
	 * Derived classes may define other parameters.
	 *
	 * The method will use the {@link addDatabaseParameters()} method to add the current
	 * database parameters to the list of collection parameters.
	 *
	 * Once the collection parameters are complete, the {@link newCollection()} method
	 * should instantiate and return the collection object.
	 *
	 * @param mixed					$theParameter		Collection name or parameters list.
	 *
	 * @access public
	 * @return CollectionObject		Collection object.
	 *
	 * @uses addDatabaseParameters()
	 * @uses parseCollectionName()
	 * @uses newCollection()
	 */
	public function Collection( $theParameter )
	{
		//
		// Normalise database parameters.
		//
		$theParameter = ( is_array( $theParameter ) )
					  ? $this->addDatabaseParameters(
					  		array( kTAG_CONN_NAME => $theParameter ) )
					  : $this->parseCollectionName( $theParameter );
		
		return $this->newCollection( $theParameter );								// ==>
	
	} // Collection.

		

/*=======================================================================================
 *																						*
 *							PROTECTED SERVER MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	instantiateParent																*
	 *==================================================================================*/

	/**
	 * Instantiate server
	 *
	 * This method will instantiate a server object from the provided parameters.
	 *
	 * It is assumed that the parameters contain at least the server host and that the
	 * protocol of the current database is the same as the server's.
	 *
	 * Concrete derived classes must implement this method.
	 *
	 * @param mixed					$theParameter		Current DSN or parameters.
	 *
	 * @access protected
	 * @return ServerObject			Server object.
	 */
	abstract protected function instantiateParent( $theParameter );

		

/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseCollectionName																*
	 *==================================================================================*/

	/**
	 * Parse collection name
	 *
	 * This method will set the provided name in the resulting offsets
	 * {@link kTAG_CONN_NAME} and  call the {@link addDatabaseParameters()} method to add
	 * the database offsets to the resulting list.
	 *
	 * @param string				$theName			Collection name.
	 *
	 * @access protected
	 * @return array				Full database offsets.
	 *
	 * @uses addDatabaseParameters()
	 */
	protected function parseCollectionName( $theName )
	{
		//
		// Set collection name.
		//
		$params = array( kTAG_CONN_NAME => $theName );
		
		return $this->addDatabaseParameters( $params );								// ==>
	
	} // parseCollectionName.

	 
	/*===================================================================================
	 *	addDatabaseParameters															*
	 *==================================================================================*/

	/**
	 * Parse add database parameters
	 *
	 * This method should add the current database offsets to the provided list of
	 * collection offsets and return the complete list.
	 *
	 * This is done so that the collection may hold also the database parameters which might
	 * be required to instantiate a collection instance.
	 *
	 * The method should return the complete list of collection offsets that can be used to
	 * instantiate a collection object.
	 *
	 * In this class we replace the {@link kTAG_CONN_NAME} offset with the
	 * and {@link kTAG_CONN_BASE} and add all other database offsets.
	 *
	 * @param array					$theOffsets			Collection offsets.
	 *
	 * @access protected
	 * @return array				Full collection offsets.
	 */
	protected function addDatabaseParameters( $theOffsets )
	{
		//
		// Add offsets.
		//
		foreach( $this as $key => $value )
		{
			if( $key == kTAG_CONN_NAME )
				$key = kTAG_CONN_BASE;
			$theOffsets[ $key ] = $value;
		}
		
		return $theOffsets;															// ==>
	
	} // addDatabaseParameters.

		

/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE INSTANTIATION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newCollection																	*
	 *==================================================================================*/

	/**
	 * Return a new collection instance
	 *
	 * This method should implemented by concrete derived classes, it expects a list of
	 * offsets which include database information and should use them to instantiate a
	 * {@link CollectionObject} instance.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param array					$theOffsets			Full collection offsets.
	 *
	 * @access protected
	 * @return CollectionObject		Collection instance.
	 */
	abstract protected function newCollection( $theOffsets );

	 

} // class DatabaseObject.


?>
