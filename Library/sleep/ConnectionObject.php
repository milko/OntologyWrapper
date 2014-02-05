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
 * specific server, database, collection or cache engines.
 *
 * The class features the following properties:
 *
 * <ul>
 *	<li><tt>{@link $mDSN}</tt>: The <i>data source name</i>, it is an URL that represents
 *		the connection string.
 *	<li><tt>{@link $mConnection}</tt>: The <i>connection resource</i>, it represents the
 *		native connection.
 *	<li><tt>{@link $mParent}</tt>: The <i>parent connection</i>, it represents the instance
 *		derived from this class that instantiated the current object.
 * </ul>
 *
 * The object is instantiated by providing a parameter that may either be a connection URL,
 * such as a string that may be parsed by the {@link parse_url()} function, or an array
 * containing the connection parameters.
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
 * The class provides accessor methods for the object properties: the {@link $mDSN} data
 * member can be managed with the {@link DSN()} method, the {@link $mConnection} data
 * member can be retrieved with the {@link Connection()} method and the {@link $mParent}
 * data member can be retrieved with the {@link Parent()} method.
 *
 * When setting the connection string, {@link DSN()}, the object's connection parameters
 * will be synchronised. When setting offsets, the data source name will not be changed.
 * When the connection is opened, {@link openConnection()}, the data source name will be
 * re-constituted using the object's offsets. This means that the object offsets represent
 * the actual connection parameters, although setting the DSN will reset these parameters to
 * match the connection URL.
 *
 * When the connection is {@link isConnected() open}, any attempt to modify the object
 * offsets will raise an exception: this is to prevent changing the connection properties
 * while connected.
 *
 * In this class we make use of the {@link StatusTrait} trait, here we set the
 * {@link isDirty()} flag whenever we modify an object offset, and we reset it whenever we
 * open the connection; we reset the status bitfield data member after calling the parent
 * constructor.
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
	 * Status trait.
	 *
	 * In this class we handle the {@link isDirtyFlag()}
	 */
	use	StatusTrait;

	/**
	 * Data source name.
	 *
	 * This data member holds the <i>data source name</i>, or <tt>DSN</tt>, it is an URL
	 * connection string that should be compatible with the {@link parse_url()} function.
	 * This string should hold all the connection parameters.
	 *
	 * @var string
	 */
	private $mDSN = NULL;

	/**
	 * Parent connection.
	 *
	 * This data member holds the <i>parent connection object</i>, this value should be an
	 * instance derived from this class which is used to instantiate the current object's
	 * connection resource.
	 *
	 * @var ConnectionObject
	 */
	private $mParent = NULL;

	/**
	 * Connection resource.
	 *
	 * This data member holds the <i>connection resource</i>, or <i>native connection</i>,
	 * this property represents the actual connection resource.
	 *
	 * @var mixed
	 */
	protected $mConnection = NULL;

		

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
	 * The object may be instantiated as an empty object, by omitting both parameters; with
	 * a <i>data source name</i> in the form of a connection URL; or by providing an array
	 * of tag/value parameters which will constitute the object's offsets.
	 *
	 * If you provide a data source name, this must be parsable by the {@link parse_url()}
	 * function, if this is not the case, you should use the parameters list.
	 *
	 * If the first parameter was provided, the method will synchronise both the data source
	 * name and the connection parameters.
	 *
	 * The second parameter represents the <i>connection parent</i>, it must be an instance
	 * derived from this class and will only be set by the constructor.
	 *
	 * When overloading the constructor in derived classes you should always first call the
	 * parent method and then perform custom actions.
	 *
	 * @param mixed					$theParameter		Data source name or parameters.
	 * @param ConnectionObject		$theParent			Connection parent.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses DSN()
	 * @uses parseOffsets()
	 */
	public function __construct( $theParameter = NULL, $theParent = NULL )
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
				// Generate and load DSN.
				//
				$this->DSN( $this->parseOffsets(), FALSE, FALSE );
			
			} // Provided individual parameters.
		
			//
			// Handle data source name.
			//
			else
				$this->DSN( (string) $theParameter );
		
		} // Provided parameter.
		
		//
		// Handle parent.
		//
		if( $theParent !== NULL )
		{
			//
			// Check type.
			//
			if( ! ($theParent instanceof self) )
				throw new \Exception(
					"Invalid connection parent type." );						// !@! ==>
			
			//
			// Set parent.
			//
			$this->mParent = $theParent;
		
		} // Provided parent.
		
		//
		// Reset status.
		//
		$this->statusReset();

	} // Constructor.

	 
	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * Destruct instance.
	 *
	 * The destructor will close the connection if open.
	 *
	 * @access public
	 *
	 * @uses closeConnection()
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
		
		//
		// Reset connection.
		//
		else
			$this->mConnection = FALSE;
		
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
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @access public
	 *
	 * @uses isConnected()
	 *
	 * @throws Exception
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
			throw new \Exception(
				"Cannot set value: the connection is open." );					// !@! ==>
		
		//
		// Call parent method.
		//
		parent::offsetSet( $theOffset, $theValue );
	
		//
		// Reset dirty flag.
		//
		$this->isDirty( TRUE );
	
	} // offsetSet.

	 
	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * Reset a value at a given offset
	 *
	 * We overload this method to prevent deleting values while the connection is open.
	 *
	 * @param string				$theOffset			Offset.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses isConnected()
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
			throw new \Exception(
				"Cannot delete value: the connection is open." );				// !@! ==>
		
		//
		// Call parent method.
		//
		parent::offsetUnset( $theOffset );
	
		//
		// Reset dirty flag.
		//
		$this->isDirty( TRUE );
	
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
	 * This method can be used to manage the <i>data source name</i>, it accepts a parameter
	 * which represents either the data source name or the requested operation, depending on
	 * its value:
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
	 * The last parameter is a switch that determines whether the object offsets should be
	 * synchronised: if <tt>TRUE</tt>, the object offsets will be reset and populated with
	 * the elements parsed from the data source name; if <tt>FALSE</tt>, the object offsets
	 * will not be modified. This parameter is set to <tt>FALSE</tt> by the constructor,
	 * since using all offsets may produce an invalid URL and is <tt>TRUE</tt> by default,
	 * since setting a connection URL generally means changing the parameters.
	 *
	 * Whenever a new value is set or the value is deleted, the method will synchronise the
	 * object offsets.
	 *
	 * @param mixed					$theValue			Data source name or operation.
	 * @param boolean				$getOld				<tt>TRUE</tt> get old value.
	 * @param boolean				$doSync				<tt>TRUE</tt> will sync offsets.
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
	public function DSN( $theValue = NULL, $getOld = FALSE, $doSync = TRUE )
	{
		//
		// Handle locked object.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \Exception(
				"Unable to set data source: the connection is open." );			// !@! ==>
		
		//
		// Manage property.
		//
		$save = $this->manageProperty( $this->mDSN, $theValue, $getOld );
		
		//
		// Handle value change.
		//
		if( $theValue !== NULL )
		{
			//
			// Sync offsets.
			//
			if( $doSync )
				$this->parseDSN();
			
			//
			// Handle dirty flag.
			//
			$this->isDirty( ! $doSync );
		}
		
		return $save;																// ==>
	
	} // DSN.

		
	/*===================================================================================
	 *	Connection																		*
	 *==================================================================================*/

	/**
	 * Return connection resource.
	 *
	 * This method can be used to retrieve the <i>connection resource</i>, this method is
	 * read-only, since the connection resource should only be set by the object's
	 * connection methods.
	 *
	 * The connection resource represents the native connection.
	 *
	 * @access public
	 * @return mixed				Connection resource.
	 *
	 * @see $mConnection
	 */
	public function Connection()							{	return $this->mConnection;	}

	 
	/*===================================================================================
	 *	Parent																			*
	 *==================================================================================*/

	/**
	 * Return connection parent.
	 *
	 * This method can be used to retrieve the <i>parent connection</i>, this method is
	 * read-only, since the connection parent can only be set by the constructor and cannot
	 * be changed once the object has been instantiated.
	 *
	 * The connection parent represents the connection creator as the server for a database.
	 *
	 * @access public
	 * @return ConnectionObject		Parent connection.
	 *
	 * @see $mParent
	 */
	public function Parent()									{	return $this->mParent;	}

		

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
	 * and return the connection resource which will be set in the data member,
	 * {@link mConnection}.
	 *
	 * The method will return the connection resource.
	 *
	 * @access public
	 * @return mixed				Depends on implementation.
	 *
	 * @uses isConnected()
	 * @uses DSN()
	 * @uses parseOffsets()
	 * @uses connectionOpen()
	 * @uses Connection()
	 */
	public function openConnection()
	{
		//
		// Check connection.
		//
		if( ! $this->isConnected() )
		{
			//
			// Synchronise DSN.
			//
			if( $this->isDirty() )
				$this->DSN( $this->parseOffsets(), FALSE, FALSE );
		
			//
			// Open and set connection.
			//
			$this->connectionOpen();
		
			//
			// Reset dirty flag.
			//
			$this->isDirty( FALSE );
		
		} // Not connected.
		
		return $this->mConnection;													// ==>
	
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
			//
			$this->mConnection = NULL;
			
			return TRUE;															// ==>
		
		} // Was open.
		
		return FALSE;																// ==>
	
	} // closeConnection.

	 
	/*===================================================================================
	 *	getStatistics																	*
	 *==================================================================================*/

	/**
	 * Return statistics
	 *
	 * This method should return the connection statistics, the result depends on the
	 * specific driver.
	 *
	 * We implement the method in this class to allow drivers that do not return statistics.
	 *
	 * @access public
	 * @return mixed				Depends on driver.
	 */
	public function getStatistics()										{	return NULL;	}

	 

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
	 * This method should open the actual connection and set the {@link mConnection} data
	 * member; in this class the method is virtual.
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
	 * This method should close the actual connection, in this class the method is virtual.
	 *
	 * This method expects the caller to have checked whether the connection is open.
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
	 * This method will parse the provided data source name, extract the connection
	 * parameters and set them in the current object.
	 *
	 * The method will make use of the {@link parse_url()} function and pass each key/value
	 * pair to the protected {@link loadDSNParameter()} method which has the responsibility
	 * of matching the {@link parse_url()} keys to {@link TagObject} instances.
	 *
	 * If the {@link parse_url()} function fails to parse the DSN, the method will raise an
	 * exception.
	 *
	 * Derived classes should overload the {@link loadDSNParameter()} method.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses DSN()
	 * @uses loadDSNParameter()
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
			// Load parameters.
			//
			foreach( $encoded as $key => $value )
				$this->loadDSNParameter( $encoded, $key, $value );
		
		} // Has DSN.
	
	} // parseDSN.

	 
	/*===================================================================================
	 *	parseOffsets																	*
	 *==================================================================================*/

	/**
	 * Parse connection parameters
	 *
	 * This method will parse the current object's offsets and generate a connection URL.
	 *
	 * The method will iterate the object's offsets and feed them to the protected
	 * {@link parseOffset()} method which will populate an array structured as the result of
	 * the {@link parse_url()} function, it will be the duty of this method to generate a
	 * data source name from that array.
	 *
	 * Derived classes should overload the called methods and not this one.
	 *
	 * If the resulting data source name is empty, the method will return <tt>FALSE</tt>.
	 *
	 * @access protected
	 * @return mixed				Data source name or <tt>FALSE</tt> if empty.
	 *
	 * @uses parseOffset()
	 */
	protected function parseOffsets()
	{
		//
		// Init local storage.
		//
		$params = Array();
		
		//
		// Iterate offsets.
		//
		$offsets = $this->getArrayCopy();
		foreach( $offsets as $key => $value )
			$this->parseOffset( $params, $key, $value );
		
		//
		// Handle parameters.
		//
		if( count( $params ) )
		{
			//
			// Init local storage.
			//
			$dsn = '';
			
			//
			// Set protocol.
			//
			if( array_key_exists( 'scheme', $params ) )
				$dsn .= $params[ 'scheme' ].'://';
		
			//
			// Handle credentials.
			//
			if( array_key_exists( 'user', $params ) )
			{
				//
				// Set user.
				//
				$dsn .= $params[ 'user' ];
			
				//
				// Set password.
				//
				if( array_key_exists( 'pass', $params ) )
					$dsn .= (':'.$params[ 'pass' ]);
			
				//
				// Close credentials.
				//
				$dsn .= '@';
		
			} // Has user.
		
			//
			// Add host.
			//
			if( array_key_exists( 'host', $params ) )
				$dsn .= $params[ 'host' ];
		
			//
			// Add port.
			//
			if( array_key_exists( 'port', $params ) )
				$dsn .= (':'.$params[ 'port' ]);
		
			//
			// Handle path.
			// Note that we add a leading slash
			// if the parameter does not start with one.
			//
			if( array_key_exists( 'path', $params ) )
			{
				if( ! substr( $params[ 'path' ], 0, 1 ) == '/' )
					$dsn .= '/';
				$dsn .= $params[ 'path' ];
			}
		
			//
			// Set options.
			//
			if( array_key_exists( 'query', $params ) )
				$dsn .= ('?'.$params[ 'query' ]);
		
			//
			// Set fragments.
			//
			if( array_key_exists( 'fragment', $params ) )
				$dsn .= ('#'.$params[ 'fragment' ]);
		
			return $dsn;															// ==>
		
		} // Has parameters.
		
		return FALSE;																// ==>
		
	} // parseOffsets.

	 
	/*===================================================================================
	 *	parseOffset																		*
	 *==================================================================================*/

	/**
	 * Parse offset
	 *
	 * This method will parse the provided offset and populate the provided parameters.
	 * The main duty is to load the offset values into the provided parameters array so to
	 * create the same result as the {@link parse_url()} function.
	 *
	 * The resulting array can have the following elements:
	 *
	 * <ul>
	 *	<li><tt><code>scheme</code></tt>: The protocol or scheme.
	 *	<li><tt><code>host</code></tt>: The connection host.
	 *	<li><tt><code>port</code></tt>: The connection port.
	 *	<li><tt><code>user</code></tt>: The user code.
	 *	<li><tt><code>pass</code></tt>: The user password.
	 *	<li><tt><code>path</code></tt>: The connection path.
	 *	<li><tt><code>query</code></tt>: The connection options.
	 *	<li><tt><code>fragment</code></tt>: The URL fragment.
	 * </ul>
	 *
	 * In this class we handle the following offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_PROTOCOL}</tt>: The <code>scheme</code>.
	 *	<li><tt>{@link kTAG_CONN_HOST}</tt>: The connection <code>host</code>.
	 *	<li><tt>{@link kTAG_CONN_PORT}</tt>: The connection <code>port</code>.
	 *	<li><tt>{@link kTAG_CONN_USER}</tt>: The <code>user</code> code.
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: The user <code>pass</code>word.
	 *	<li><tt>{@link kTAG_CONN_OPTS}</tt>: The connection options, <code>query</code>.
	 * </ul>
	 *
	 * Derived classes can overload this method to customise the parameters.
	 *
	 * @param reference				$theParameters		Receives parsed offset.
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Offset value.
	 *
	 * @access protected
	 *
	 * @uses parseOption()
	 */
	protected function parseOffset( &$theParameters, $theOffset, $theValue )
	{
		//
		// Parse by tag.
		//
		switch( $theOffset )
		{
			case kTAG_CONN_PROTOCOL:
				$theParameters[ 'scheme' ] = $theValue;
				break;
			
			case kTAG_CONN_HOST:
				$theParameters[ 'host' ] = $theValue;
				break;
			
			case kTAG_CONN_PORT:
				$theParameters[ 'port' ] = $theValue;
				break;
			
			case kTAG_CONN_USER:
				$theParameters[ 'user' ] = $theValue;
				break;
			
			case kTAG_CONN_PASS:
				$theParameters[ 'pass' ] = $theValue;
				break;
			
			case kTAG_CONN_OPTS:
				$options = $this->offsetGet( kTAG_CONN_OPTS );
				foreach( $options as $key => $value )
					$this->parseOption( $theParameters, $key, $value );
				break;
		}
		
	} // parseOffset.

	 
	/*===================================================================================
	 *	parseOption																		*
	 *==================================================================================*/

	/**
	 * Parse option
	 *
	 * This method will parse the provided option and populate the query parameters.
	 * The main duty is to load the option into the <code>query</code> element of the
	 * provided parameters list as the result of the {@link parse_url()} function.
	 *
	 * In this class we load what we find.
	 *
	 * Derived classes can overload this method to customise the options.
	 *
	 * @param reference				$theParameters		Receives parsed offset.
	 * @param string				$theOption			Option.
	 * @param mixed					$theValue			Option value.
	 *
	 * @access protected
	 */
	protected function parseOption( &$theParameters, $theOption, $theValue )
	{
		//
		// Add divider.
		//
		if( array_key_exists( 'query', $theParameters ) )
			$theParameters[ 'query' ] .= '&';
		
		//
		// Create element.
		//
		else
			$theParameters[ 'query' ] = '';
		
		//
		// Set option.
		//
		$theParameters[ 'query' ] .= $theOption;
		if( $theValue !== NULL )
			$theParameters[ 'query' ] .= ('='.$theValue);
		
	} // parseOption.

		

/*=======================================================================================
 *																						*
 *							PROTECTED PARAMETER LOADING INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadDSNParameter																*
	 *==================================================================================*/

	/**
	 * Load connection parameters from DSN
	 *
	 * This method will load the parameters parsed from the data source name into the
	 * current object's offsets, it expects three parameters:
	 *
	 * <ul>
	 *	<li><b>$theParameters</b>: This array is the result of the {@link parse_url()}
	 *		function on the data source name:
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
	 *	<li><b>$theKey</b>: This parameter represents the offset.
	 *	<li><b>$theValue</b>: This parameter represents the offset value.
	 * </ul>
	 *
	 * This is the method that derived classes may overload to customise the parameters.
	 *
	 * @param reference				$theParameters		Original parameters list.
	 * @param string				$theKey				Parameter key.
	 * @param string				$theValue			Parameter value.
	 *
	 * @access protected
	 */
	protected function loadDSNParameter( &$theParameters, $theKey, $theValue = NULL )
	{
		//
		// Parse parameter.
		//
		switch( $theKey )
		{
			case 'scheme':
				$this->offsetSet( kTAG_CONN_PROTOCOL, $theValue );
				break;
			
			case 'host':
				$this->offsetSet( kTAG_CONN_HOST, $theValue );
				break;
			
			case 'port':
				$this->offsetSet( kTAG_CONN_PORT, (int) $theValue );
				break;
			
			case 'user':
				$this->offsetSet( kTAG_CONN_USER, $theValue );
				break;
			
			case 'pass':
				$this->offsetSet( kTAG_CONN_PASS, $theValue );
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
				$this->offsetSet( kTAG_CONN_OPTS, $options );
				break;
		}
	
	} // loadDSNParameter.

	 

} // class ConnectionObject.


?>
