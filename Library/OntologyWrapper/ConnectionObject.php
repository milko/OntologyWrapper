<?php

/**
 * ConnectionObject.php
 *
 * This file contains the definition of the {@link ConnectionObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\OntologyObject;

/*=======================================================================================
 *																						*
 *								ConnectionObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Connection object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing connection
 * instances, such as caches, servers, databases and collections.
 *
 * The main purpose of this class is to wrap a common interface around concrete instances of
 * specific database or cache engines.
 *
 * The class features three properties:
 *
 * <ul>
 *	<li><tt>{@link $mParent}</tt>: The <i>parent connection</i>.
 *	<li><tt>{@link $mConnection}</tt>: The <i>connection resource</i>.
 *	<li><tt>{@link $mDSN}</tt>: The <i>data source name</i>.
 * </ul>
 *
 * The data source name is an URL that represents the connection string, the connection
 * resource represents the native connection and the parent connection is the eventual
 * object derived from this class that instantiated the current object.
 *
 * The parameters of the connection are stored in the array part of the object, which means that
 * that they must be defined as {@link Tag} objects. The connection may be defined by
 * providing the data source name, or by setting the individual parameters; the first is
 * done by setting the data members, the other by setting the data offsets.
 *
 * The public interface of this class, as well as for many other abstract classes, is
 * implemented as templates in which protected methods do the actual work, so derived
 * concrete classes should only need to implement the protected interface.
 *
 * This class declares three methods for managing the connection:
 *
 * <ul>
 *	<li><tt>{@link isConnected()}</tt>: Returns <tt>TRUE</tt> if the connection is open.
 *	<li><tt>{@link openConnection()}</tt>: Create and open the connection.
 *	<li><tt>{@link closeConnection()}</tt>: Close and reset the connection.
 * </ul>
 *
 * When the object goes out of context it will close the connection, if open, and re-open it
 * once it gets back into context:
 *
 * <ul>
 *	<li><tt>{@link __sleep()}</tt>: This method will close the connection, if open, and
 *		set the {@link $mConnection connection} property to <tt>TRUE</tt> as an indication
 *		that the connection must be opened once the object gets back into scope.
 *	<li><tt>{@link __wakeup()}</tt>: This method will open the connection, if the object
 *		went out of scope while the connection was open.
 * </ul>
 *
 * The class provides accessor methods for the two object properties, {@link DSN()} and
 * {@link Connection()}: derived classes can overload these methods for validation
 * purposes.
 *
 * When setting the connection string, {@link DSN()}, the object's connection parameters
 * will be synchronised, while when setting offsets, the data source name will remain
 * untouched: when the connection is opened, {@link openConnection()}, the data source name
 * will be re-constituted using the object's connection parameters.
 *
 * When the connection is {@link isConnected() open}, any attempt to modify the object
 * properties or offsets will raise an exception: this is to prevent changing the connection
 * parameters while connected.
 *
 * This object represents the building block for all concrete instances that represent
 * servers, databases, data collections and caches.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 16/01/2014
 */
abstract class ConnectionObject extends OntologyObject
{
	/**
	 * Parent connection.
	 *
	 * This data member holds the <i>parent connection object</i>, .
	 *
	 * @var ConnectionObject
	 */
	protected $mParent = NULL;

	/**
	 * Connection resource.
	 *
	 * This data member holds the <i>connection resource</i>, or <i>native connection</i>.
	 * This property represents the actual connection resource.
	 *
	 * @var mixed
	 */
	private $mConnection = NULL;

	/**
	 * Data source name.
	 *
	 * This data member holds the <i>data source name</i>, or <tt>DSN</tt>. It is a string
	 * holding all the connection parameters that is used to instantiate the actual
	 * connection.
	 *
	 * @var string
	 */
	private $mDSN = NULL;

	/**
	 * URL dictionary.
	 *
	 * This static data member holds an array which features as keys the parameters of the
	 * {@link parse_url()} function and as values the corresponding {@link Tag} native
	 * identifiers that will be used to load the connection parameters into the current
	 * object.
	 *
	 * The <code>path</code> parameter is missing and should be set in derived classes if
	 * used; thew <code>query</code> and <code>fragment</code> elements are handled each by
	 * specific methods.
	 *
	 * In derived classes this array may be customised without the need to overload the
	 * {@link loadMainParameters()} method.
	 *
	 * @var array
	 */
	static $sParseURL = array( 'scheme'	=> kTAG_CONN_PROTOCOL,
							   'host'	=> kTAG_CONN_HOST,
							   'port'	=> kTAG_CONN_PORT,
							   'user'	=> kTAG_CONN_USER,
							   'pass'	=> kTAG_CONN_PASS );

		

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
	 * The object may be instantiated in three ways:
	 *
	 * <ul>
	 *	<li>As an empty object, in this case omit the parameter.
	 *	<li>By providing the data source name as the parameter.
	 *	<li>By providing the connection parameters array.
	 * </ul>
	 *
	 * If the parameter was provided, the method will automatically synchronise the data
	 * source name and the connection parameters.
	 *
	 * @param mixed					$theParameter		Data source name or parameters.
	 *
	 * @access public
	 *
	 * @uses DSN()
	 */
	public function __construct( $theParameter = NULL )
	{
		//
		// Init object.
		//
		parent::__construct();
		
		//
		// Handle parameter.
		//
		if( $theParameter !== NULL )
		{
			//
			// Handle connection parameters.
			//
			if( is_array( $theParameter ) )
			{
				//
				// Load parameters.
				//
				foreach( $theParameter as $key => $value )
					$this->offsetSet( $key, $value );
				
				//
				// Load DSN.
				//
				$this->DSN( $this->parseParameters() );
			
			} // Provided individual parameters.
		
			//
			// Handle data source name.
			//
			else
				$this->DSN( (string) $theParameter );
		
		} // Provided parameter.

	} // Constructor.

	 
	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * Destruct instance.
	 *
	 * The destructor will close the connection if open and reset the data member.
	 *
	 * @access public
	 *
	 * @uses DSN()
	 */
	public function __destruct()							{	$this->closeConnection();	}

		
	/*===================================================================================
	 *	__sleep																			*
	 *==================================================================================*/

	/**
	 * Sleep
	 *
	 * This method will close the connection and replace the connection resource with
	 * <tt>TRUE</tt> if the connection was open.
	 *
	 * @access public
	 *
	 * @uses isConnected()
	 * @uses closeConnection()
	 */
	public function __sleep()
	{
		//
		// Check current connection.
		//
		if( $this->isConnected() )
		{
			//
			// Close connection.
			//
			$this->closeConnection();
			
			//
			// Mark as open.
			//
			$this->mConnection = TRUE;
		
		} // Connection is open.
		
	} // __sleep.

	 
	/*===================================================================================
	 *	__wakeup																		*
	 *==================================================================================*/

	/**
	 * Wake up
	 *
	 * This method will re-open the connection if it was closed by the {@link __sleep()}
	 * method.
	 *
	 * @access public
	 *
	 * @uses openConnection()
	 */
	public function __wakeup()
	{
		//
		// Open closed connection.
		//
		if( $this->mConnection === TRUE )
			$this->openConnection();
		
	} // __wakeup.

		

/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * Set a value at a given offset
	 *
	 * We overload this method to prevent setting values while the connection is open.
	 *
	 * In derived classes yoiu can overload this method if you need to handle offsets while
	 * the connection is open: you should intercept the specific offset and handle it in the
	 * derived class, while all other offsets should be passed to the parent method.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @access public
	 *
	 * @uses isConnected()
	 * @uses DSN()
	 * @uses parseParameters()
	 *
	 * @throws Exception
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Check if connected.
		//
		if( ! $this->isConnected() )
			parent::offsetSet( $theOffset, $theValue );
		
		else
			throw new \Exception(
				"Object is locked: the connection is open." );					// !@! ==>
	
	} // offsetSet.

	 
	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * Reset a value at a given offset
	 *
	 * We overload this method to prevent deleting values while the connection is open.
	 *
	 * In derived classes yoiu can overload this method if you need to handle offsets while
	 * the connection is open: you should intercept the specific offset and handle it in the
	 * derived class, while all other offsets should be passed to the parent method.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses isConnected()
	 * @uses DSN()
	 * @uses parseParameters()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Check if connected.
		//
		if( ! $this->isConnected() )
			parent::offsetUnset( $theOffset );
		
		else
			throw new \Exception(
				"Object is locked: the connection is open." );					// !@! ==>
	
	} // offsetUnset.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	DSN																				*
	 *==================================================================================*/

	/**
	 * Manage data source name
	 *
	 * This method can be used to manage the data source name, it accepts a parameter which
	 * represents either the data source name or the requested operation, depending on its
	 * value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>FALSE</tt>: Delete the current value.
	 *	<li><i>other</i>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * The second parameter is a boolean which if <tt>TRUE</tt> will return the <i>old</i>
	 * value when replacing or resetting; if <tt>FALSE</tt>, it will return the current
	 * value.
	 *
	 * Whenever a new value is set or the value is deleted, the method will synchronise the
	 * connection parameters.
	 *
	 * @param mixed					$theValue			Data source name or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				<i>New</i> or <i>old</i> data source name.
	 *
	 * @throws Exception
	 *
	 * @see $mDSN
	 *
	 * @uses isConnected()
	 * @uses manageProperty()
	 * @uses parseDSN()
	 */
	public function DSN( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Handle locked object.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \Exception(
				"Object is locked: the connection is open." );					// !@! ==>
		
		//
		// Manage property.
		//
		$save = $this->manageProperty( $this->mDSN, $theValue, $getOld );
		
		//
		// Handle value change.
		//
		if( $theValue !== NULL )
			$this->parseDSN();
		
		return $save;																// ==>
	
	} // DSN.

	 
	/*===================================================================================
	 *	Connection																		*
	 *==================================================================================*/

	/**
	 * Manage connection.
	 *
	 * This method can be used to manage the connection resource, it accepts a parameter
	 * which represents either the connection or the requested operation, depending on its
	 * value:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return the current value.
	 *	<li><tt>FALSE</tt>: Delete the current value.
	 *	<li><i>other</i>: Set the value with the provided parameter.
	 * </ul>
	 *
	 * The second parameter is a boolean which if <tt>TRUE</tt> will return the <i>old</i>
	 * value when replacing or resetting; if <tt>FALSE</tt>, it will return the current
	 * value.
	 *
	 * @param mixed					$theValue			Connection or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 *
	 * @access public
	 * @return mixed				<i>New</i> or <i>old</i> connection.
	 *
	 * @see $mConnection
	 *
	 * @uses isConnected()
	 * @uses manageProperty()
	 */
	public function Connection( $theValue = NULL, $getOld = FALSE )
	{
		//
		// Handle locked object.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \Exception(
				"Object is locked: the connection is open." );					// !@! ==>
		
		return $this->manageProperty( $this->mConnection, $theValue, $getOld );		// ==>
	
	} // Connection.

		

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
	 * This method returns a boolean flag indicating whether the connection is open or not.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> is open.
	 */
	public function isConnected()
	{
		return ( ($this->mConnection !== NULL)
			  && ($this->mConnection !== TRUE) );									// ==>
	
	} // isConnected.

		
	/*===================================================================================
	 *	openConnection																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * This method can be used to create and open the connection.
	 *
	 * We first check if the connection is already set: if so we do nothing.
	 *
	 * We call the protected {@link connectionOpen()} method which will open the connection
	 * and return the connection resource which will be set in the {@link Connection} data
	 * member.
	 *
	 * The method will return the connection resource.
	 *
	 * @access public
	 * @return mixed				Depends on implementation.
	 *
	 * @uses isConnected()
	 * @uses Connection()
	 * @uses connectionOpen()
	 */
	public function openConnection()
	{
		//
		// Check connection.
		//
		if( ! $this->isConnected() )
		{
			//
			// Open connection.
			//
			$connection = $this->connectionOpen();
		
			//
			// Refresh DSN.
			//
			$this->DSN( $this->parseParameters() );
			
			//
			// Set connection resource.
			// We do it here or since open it will raise an exception.
			//
			$this->Connection( $connection );
		
		} // Not connected.
		
		return $this->Connection();													// ==>
	
	} // openConnection.

	 
	/*===================================================================================
	 *	closeConnection																	*
	 *==================================================================================*/

	/**
	 * Close connection
	 *
	 * If the connection is open, this method will close the connection and reset the
	 * {@link Connection} data member.
	 *
	 * The method will return <tt>TRUE</tt> if the connection was open and <tt>FALSE</tt> if
	 * not.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> if closed, <tt>FALSE</tt> if was closed.
	 *
	 * @uses isConnected()
	 * @uses connectionClose()
	 * @uses Connection()
	 */
	public function closeConnection()
	{
		//
		// Check connection.
		//
		if( $this->isConnected() )
		{
			//
			// Close connection.
			//
			$this->connectionClose();
			
			//
			// Reset connection.
			// Note that we set the data member directly,
			// this is to prevent an exception from being raised,
			// since the connection is still open.
			//
			$this->mConnection = NULL;
			
			return TRUE;															// ==>
		
		} // Was open.
		
		return FALSE;																// ==>
	
	} // closeConnection.

	 

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
	 * This method should open the actual connection, the method is virtual and must be
	 * implemented by concrete derived instances.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @access protected
	 * @return mixed				The native connection.
	 */
	abstract protected function connectionOpen();

	 
	/*===================================================================================
	 *	connectionClose																	*
	 *==================================================================================*/

	/**
	 * Open connection
	 *
	 * This method should close the actual connection, the method is virtual and must be
	 * implemented by concrete derived instances.
	 *
	 * This method expects the caller to have checked whether the connection is already
	 * open.
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @param mixed					$theConnection		Connection.
	 *
	 * @access protected
	 */
	abstract protected function connectionClose();

		

/*=======================================================================================
 *																						*
 *								PROTECTED PARSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	parseDSN																		*
	 *==================================================================================*/

	/**
	 * Parse data source name
	 *
	 * This method will parse the current data source name, extract the connection
	 * parameters and set them in the current object
	 * parameters.
	 *
	 * The method makes use of three methods:
	 *
	 * <ul>
	 *	<li><tt>{@link loadMainParameters()}</tt>: This method will take care of parsing the
	 *		following parameters:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_CONN_PROTOCOL}</tt>: The protocol or <code>scheme</code>.
	 *		<li><tt>{@link kTAG_CONN_HOST}</tt>: The connection <code>host</code>.
	 *		<li><tt>{@link kTAG_CONN_PORT}</tt>: The connection <code>port</code>.
	 *		<li><tt>{@link kTAG_CONN_USER}</tt>: The <code>user</code> code.
	 *		<li><tt>{@link kTAG_CONN_PASS}</tt>: The user <code>pass</code>word.
	 *	 </ul>
	 *		The <code>path</code> parameter should also be parsed in that method, but in
	 *		this class we do not handle it.
	 *	<li><tt>{@link loadQueryParameters()}</tt>: This method should take care of parsing
	 *		the <code>query</code> section of the data source name, in this class we ignore
	 *		these parameters.
	 *	<li><tt>{@link loadFragParameters()}</tt>: This method should take care of parsing
	 *		the <code>fragment</code> section of the data source name, in this class we
	 *		ignore this parameter.
	 * </ul>
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses DSN()
	 * @uses loadMainParameters()
	 * @uses loadQueryParameters()
	 * @uses loadFragParameters()
	 */
	protected function parseDSN()
	{
		//
		// Reset parameters.
		//
		$empty = Array();
		$this->exchangeArray( $empty );
		
		//
		// Check if set.
		//
		$dsn = $this->DSN();
		if( strlen( $dsn ) )
		{
			//
			// Parse DSN.
			//
			$encoded = parse_url( $dsn );
			if( $encoded === FALSE )
				throw new \Exception(
					"Invalid connection string [$dsn]." );						// !@! ==>
			
			//
			// Parse main parameters.
			//
			$this->loadMainParameters( $encoded );
			
			//
			// Parse query parameters.
			//
			$this->loadQueryParameters( $encoded );
			
			//
			// Parse fragment parameters.
			//
			$this->loadFragParameters( $encoded );
		
		} // Has DSN.
	
	} // parseDSN.

	 
	/*===================================================================================
	 *	parseParameters																	*
	 *==================================================================================*/

	/**
	 * Parse connection parameters
	 *
	 * This method will generate and return a data source name by using the object's
	 * connection parameters.
	 *
	 * In this class we use:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_PROTOCOL}</tt>: The protocol or scheme.
	 *	<li><tt>{@link kTAG_CONN_HOST}</tt>: The connection host.
	 *	<li><tt>{@link kTAG_CONN_PORT}</tt>: The connection port.
	 *	<li><tt>{@link kTAG_CONN_USER}</tt>: The user code.
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: The user password.
	 * </ul>
	 *
	 * Note that the host is required, that is, if the host is missing we set a space in
	 * the data source name.
	 *
	 * If the resulting data source name is empty, the method will return <tt>FALSE</tt>.
	 *
	 * Derived classes are responsible of managing any other eventual parameter, they may
	 * call this method and then add custom parameters to the resulting string.
	 *
	 * @access protected
	 * @return mixed				Data source name or <tt>FALSE</tt> if empty.
	 *
	 * @see kTAG_CONN_PROTOCOL, kTAG_CONN_HOST
	 * @see kTAG_CONN_PORT, kTAG_CONN_USER, kTAG_CONN_PASS
	 */
	protected function parseParameters()
	{
		//
		// Init data source name.
		//
		$dsn = '';
		
		//
		// Handle protocol.
		//
		if( $this->offsetGet( kTAG_CONN_PROTOCOL ) )
			$dsn = $this->offsetGet( kTAG_CONN_PROTOCOL ).'://';
		
		//
		// Handle user credentials.
		//
		if( $this->offsetExists( kTAG_CONN_USER ) )
		{
			//
			// Add user.
			//
			$dsn .= $this->offsetGet( kTAG_CONN_USER );
			
			//
			// Add password.
			//
			if( $this->offsetExists( kTAG_CONN_PASS ) )
				$dsn .= (':'.$this->offsetGet( kTAG_CONN_PASS ));
			
			//
			// Close credentials.
			//
			$dsn .= '@';
		
		} // Has credentials.
		
		//
		// Add host.
		//
		$dsn .= (( $this->offsetExists( kTAG_CONN_HOST ) )
				 ? $this->offsetGet( kTAG_CONN_HOST )
				 : ' ');
		
		//
		// Add port.
		//
		if( $this->offsetExists( kTAG_CONN_PORT ) )
			$dsn .= (':'.$this->offsetGet( kTAG_CONN_PORT ));
		
		return ( strlen( $dsn ) ) ? $dsn : FALSE;									// ==>
	
	} // parseParameters.

		

/*=======================================================================================
 *																						*
 *							PROTECTED PARAMETER LOADING INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadMainParameters																*
	 *==================================================================================*/

	/**
	 * Load connection main parameters
	 *
	 * This method will load the parameters parsed from the data source name into the
	 * current object, it expects the result of the {@link parse_url()} function and will
	 * handle the following parameters:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_PROTOCOL}</tt>: with the <code>scheme</code>.
	 *	<li><tt>{@link kTAG_CONN_HOST}</tt>: with the <code>host</code>.
	 *	<li><tt>{@link kTAG_CONN_PORT}</tt>: with the <code>port</code>.
	 *	<li><tt>{@link kTAG_CONN_USER}</tt>: with the <code>user</code>.
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: with the <code>pass</code>.
	 * </ul>
	 *
	 * All parameters are trimmed and eny empty parameter is ignored.
	 *
	 * Derived classes are responsible of managing the <code>path</code> part of the URL,
	 * they would typically first call the parent method, then handle the path, or they
	 * could add the path to the static {@link $sParseURL} array.
	 *
	 * The <code>query</code> and <code>fragment</code> elements are handled respectively by
	 * {@link loadQueryParameters()} and {@link loadFragParameters()}.
	 *
	 * @param reference				$theParameters		Array of parsed parameters.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see $sParseURL
	 */
	protected function loadMainParameters( &$theParameters )
	{
		//
		// Check parameter.
		//
		if( is_array( $theParameters ) )
		{
			//
			// Iterate parameters.
			//
			foreach( $theParameters as $key => $value )
			{
				//
				// Load parameter.
				//
				if( strlen( trim( $value ) )
				 && array_key_exists( $key, static::$sParseURL ) )
					$this->offsetSet( static::$sParseURL[ $key ], $value );
			
			} // Iterating parameters.
		
		} // Correct parameter.
		
		else
			throw new \Exception(
				"Unable to parse main parameters: expecting an array." );		// !@! ==>
	
	} // loadMainParameters.

	 
	/*===================================================================================
	 *	loadQueryParameters																*
	 *==================================================================================*/

	/**
	 * Load connection query parameters
	 *
	 * This method will load the parameters parsed from the data source name into the
	 * current object, it expects the result of the {@link parse_url()} function and will
	 * handle the <code>query</code> parameter.
	 *
	 * In this class the method will simply check if the provided parameter is an array,
	 * derived classes may call this method and then handle the parameters.
	 *
	 * @param reference				$theParameters		Array of parsed parameters.
	 *
	 * @access protected
	 */
	protected function loadQueryParameters( &$theParameters )
	{
		//
		// Check parameter.
		//
		if( ! is_array( $theParameters ) )
			throw new Exception(
				"Unable to parse query parameters: expecting an array." );		// !@! ==>
	
	} // loadQueryParameters.

	 
	/*===================================================================================
	 *	loadFragParameters																*
	 *==================================================================================*/

	/**
	 * Load connection fragment parameters
	 *
	 * This method will load the parameters parsed from the data source name into the
	 * current object, it expects the result of the {@link parse_url()} function and will
	 * handle the <code>fragment</code> parameter.
	 *
	 * In this class the method will simply check if the provided parameter is an array,
	 * derived classes may call this method and then handle the parameters.
	 *
	 * @param reference				$theParameters		Array of parsed parameters.
	 *
	 * @access protected
	 */
	protected function loadFragParameters( &$theParameters )
	{
		//
		// Check parameter.
		//
		if( ! is_array( $theParameters ) )
			throw new Exception(
				"Unable to parse fragment parameters: expecting an array." );	// !@! ==>
	
	} // loadFragParameters.

	 

} // class ConnectionObject.


?>
