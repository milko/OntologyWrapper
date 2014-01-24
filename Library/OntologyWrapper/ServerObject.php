<?php

/**
 * ServerObject.php
 *
 * This file contains the definition of the {@link ServerObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ConnectionObject;

/*=======================================================================================
 *																						*
 *									ServerObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Server object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing server instances.
 *
 * The main duty of this class is to instantiate {@link DatabaseObject} instances, the
 * class adds a single public method, {@link Database()}, which can be used to create a
 * database connection of the current server.
 *
 * Derived classes can add specialised data members and methods to handle clusters of
 * databases.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 21/01/2014
 */
abstract class ServerObject extends ConnectionObject
{
		

/*=======================================================================================
 *																						*
 *								PUBLIC CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	Database																		*
	 *==================================================================================*/

	/**
	 * Return database connection
	 *
	 * As for the constructor, this method expects a parameter in the same format as the
	 * constructor's first parameter, the parent is assumed to be the current server.
	 *
	 * When providing a data source name, you should only provide the information pertaining
	 * to the database, the method will take care of adding the current server parameters to
	 * the database, so:
	 *
	 * <code>driver://user:pass@database?opt1=val1&opt2=val2</code>
	 *
	 * which represents the connection URL to be passed to this method will become
	 *
	 * <code>driver://user:pass@host:port/database?opt1=val1&opt2=val2</code>
	 *
	 * when passed to the database constructor. Note that the <code>user</code> and
	 * <code>pass</code> are the credentials that will be used to access the database, not
	 * the server and the <code>database</code> name is placed in the URL <code>host</code>.
	 * <br/><br/>
	 * The method supports the following parameters:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_PROTOCOL}</tt>: The URL <code>scheme</code>, or the
	 *		database <code>driver</code>.
	 *	<li><tt>{@link kTAG_CONN_NAME}</tt>: The URL <code>host</code> element which is the
	 *		<code>database</code> name.
	 *	<li><tt>{@link kTAG_CONN_USER}</tt>: The <code>user</code> code.
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: The user <code>pass</code>word.
	 *	<li><tt>{@link kTAG_CONN_OPTS}</tt>: The connection options, <code>query</code>.
	 * </ul>
	 *
	 * Derived classes may define other parameters.
	 *
	 * The method will use the {@link addServerParameters()} method to append the current
	 * server parameters to the list of database parameters. If a data source name for the
	 * database was provided, the {@link parseDatabaseDSN()} method will take care of
	 * parsing the provided data source name and adding the server parameters.
	 *
	 * Once the database parameters are complete, the {@link newDatabase()} method should
	 * instantiate and return the database object.
	 *
	 * @param mixed					$theParameter		DSN or parameters list.
	 *
	 * @access public
	 * @return DatabaseObject		Database object.
	 *
	 * @uses addServerParameters()
	 * @uses parseDatabaseDSN()
	 * @uses newDatabase()
	 */
	public function Database( $theParameter )
	{
		//
		// Normalise database parameters.
		//
		$theParameter = ( is_array( $theParameter ) )
					  ? $this->addServerParameters( $theParameter )
					  : $this->parseDatabaseDSN( $theParameter );
		
		return $this->newDatabase( $theParameter );									// ==>
	
	} // Database.

		

/*=======================================================================================
 *																						*
 *							PROTECTED DATABASE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseDatabaseDSN																*
	 *==================================================================================*/

	/**
	 * Parse database data source name
	 *
	 * This method should parse the provided data source name, extract the database
	 * parameters and add the relevant server parameters to the resulting array that will
	 * be returned by the method.
	 *
	 * In this class we take care of parsing the DSN and we feed each element to the
	 * {@link loadDbDSNParameter()} method which takes care of parsing the URL parameters,
	 * we then pass the resulting offsets list to the {@link addServerParameters()} method
	 * which adds the relevant server offsets and we return the result.
	 *
	 * @param string				$theDSN				Database data source name.
	 *
	 * @access protected
	 * @return array				Full database offsets.
	 *
	 * @uses loadDbDSNParameter()
	 * @uses addServerParameters()
	 */
	protected function parseDatabaseDSN( $theDSN )
	{
		//
		// Parse DSN.
		//
		$encoded = parse_url( $theDSN );
		if( $encoded === FALSE )
			throw new \Exception(
				"Invalid connection string [$dsn]." );							// !@! ==>
		
		//
		// Parse URL parameters.
		//
		$params = Array();
		foreach( $encoded as $key => $value )
			$this->loadDbDSNParameter( $params, $key, $value );
		
		return $this->addServerParameters( $params );								// ==>
	
	} // parseDatabaseDSN.

	 
	/*===================================================================================
	 *	addServerParameters																*
	 *==================================================================================*/

	/**
	 * Add server parameters
	 *
	 * This method should add the current server offsets to the provided list of database
	 * offsets and return the complete list.
	 *
	 * This is done so that the database may hold also the server parameters which might
	 * be required to instantiate a database instance.
	 *
	 * Note that this is also necessary since the database name is expected in the
	 * <code>host</code> part of the URL.
	 *
	 * The method should return the complete list of database offsets that can be used to
	 * instantiate a database object.
	 *
	 * In this class we add the {@link kTAG_CONN_HOST} and {@link kTAG_CONN_PORT}.
	 *
	 * @param array					$theOffsets			Database offsets.
	 *
	 * @access protected
	 * @return array				Full database offsets.
	 */
	protected function addServerParameters( $theOffsets )
	{
		//
		// Init local storage.
		//
		$offsets = array( kTAG_CONN_HOST, kTAG_CONN_PORT );
		
		//
		// Add offsets.
		//
		foreach( $offsets as $offset )
		{
			if( $this->offsetExists( $offset ) )
				$theOffsets[ $offset ]
					= $this->offsetGet( $offset );
		}
		
		return $theOffsets;															// ==>
	
	} // addServerParameters.

		

/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE INSTANTIATION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	newDatabase																		*
	 *==================================================================================*/

	/**
	 * Return a new database instance
	 *
	 * This method should implemented by concrete derived classes, it expects a list of
	 * offsets which include server information and should use them to instantiate a
	 * {@link DatabaseObject} instance.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param array					$theOffsets			Full database offsets.
	 *
	 * @access protected
	 * @return DatabaseObject		Database instance.
	 */
	abstract protected function newDatabase( $theOffsets );

		

/*=======================================================================================
 *																						*
 *							PROTECTED PARAMETER LOADING INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadDbDSNParameter																*
	 *==================================================================================*/

	/**
	 * Load database connection parameters from DSN
	 *
	 * This method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffsets</b>: This is a reference to the array which will receive the
	 *		list of offsets.
	 *	 <ul>
	 *		<li><tt><code>scheme</code></tt>: We set it in {@link kTAG_CONN_PROTOCOL}.
	 *		<li><tt><code>host</code></tt>: We set it in {@link kTAG_CONN_HOST}.
	 *		<li><tt><code>port</code></tt>: We set it in {@link kTAG_CONN_PORT}.
	 *		<li><tt><code>user</code></tt>: We set it in {@link kTAG_CONN_USER}.
	 *		<li><tt><code>pass</code></tt>: We set it in {@link kTAG_CONN_PASS}.
	 *		<li><tt><code>path</code></tt>: This parameter is not handled in this class,
	 *			derived classes should overload this method to handle it.
	 *		<li><tt><code>query</code></tt>: We load the key/value pairs into
	 *			{@link kTAG_CONN_OPTS} array.
	 *		<li><tt><code>fragment</code></tt>: This parameter is not handled in this class,
	 *			derived classes should overload this method to handle it.
	 *	 </ul>
	 *	<li><b>$theKey</b>: This parameter represents the key of the {@link parse_url()}
	 *		function result.
	 *	<li><b>$theValue</b>: This parameter represents the value of the {@link parse_url()}
	 *		function result.
	 * </ul>
	 *
	 * The <tt>{@link $theKey}</tt> parameter is handled as follows:
	 *
	 * <ul>
	 *	<li><tt><code>scheme</code></tt>: We set it in {@link kTAG_CONN_PROTOCOL}.
	 *	<li><tt><code>host</code></tt>: We set it in {@link kTAG_CONN_HOST}.
	 *	<li><tt><code>port</code></tt>: We set it in {@link kTAG_CONN_PORT}.
	 *	<li><tt><code>user</code></tt>: We set it in {@link kTAG_CONN_USER}.
	 *	<li><tt><code>pass</code></tt>: We set it in {@link kTAG_CONN_PASS}.
	 *	<li><tt><code>path</code></tt>: This parameter is not handled in this class,
	 *		derived classes should overload this method to handle it.
	 *	<li><tt><code>query</code></tt>: We load the key/value pairs into
	 *		{@link kTAG_CONN_OPTS} array.
	 *	<li><tt><code>fragment</code></tt>: This parameter is not handled in this class,
	 *		derived classes should overload this method to handle it.
	 *	 </ul>
	 *
	 * @param reference				$theOffsets			Receives offsets list.
	 * @param string				$theKey				Parsed URL key.
	 * @param string				$theValue			Parsed URL value.
	 *
	 * @access protected
	 */
	protected function loadDbDSNParameter( &$theOffsets, $theKey, $theValue = NULL )
	{
		//
		// Parse parameter.
		//
		switch( $theKey )
		{
			case 'scheme':
				$theOffsets[ kTAG_CONN_PROTOCOL ] = $theValue;
				break;
			
			case 'host':
				$theOffsets[ kTAG_CONN_NAME ] = $theValue;
				break;
			
			case 'port':
				$theOffsets[ kTAG_CONN_PORT ] = (int) $theValue;
				break;
			
			case 'user':
				$theOffsets[ kTAG_CONN_USER ] = $theValue;
				break;
			
			case 'pass':
				$theOffsets[ kTAG_CONN_PASS ] = $theValue;
				break;
			
			case 'query':
				$options = Array();
				$opts = explode( '&', $theValue );
				foreach( $opts as $opt )
				{
					$tmp = explode( '=', $opt );
					$key = trim( $tmp[ 0 ] );
					if( count( $tmp ) > 1 )
						$options[ $key ]
							= ( strlen( $tmp[ 1 ] ) )
							? $tmp[ 1 ]
							: NULL;
					else
						$options[ $key ] = NULL;
			
				}
				$theOffsets[ kTAG_CONN_OPTS ] = $options;
				break;
		}
	
	} // loadDbDSNParameter.

	 

} // class ServerObject.


?>
