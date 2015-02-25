<?php

/**
 * FileObject.php
 *
 * This file contains the definition of the {@link FileObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\CollectionObject;
use OntologyWrapper\PersistentObject;

/*=======================================================================================
 *																						*
 *									FileObject.php										*
 *																						*
 *======================================================================================*/

/**
 * File object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing file objects,
 * these objects are files stored in the database along with searchable metadata.
 *
 * Objects of this class have three main components:
 *
 * <ul>
 *	<li><em>File reference</em>: The reference to the physical file, upload form element, or
 *		file contents.
 *	<li><em>File object</em>: The reference to the persistent file.
 *	<li><em>File metadata</em>: The metadata of the file, which inherits the parent
 *		interface.
 * </ul>
 *
 * This class provides the following specific interface:
 *
 * <ul>
 *	<li><tt>{@link getFileMember()}</tt>: Retrieve the file component.
 *	<li><tt>{@link setFileReference()}</tt>: Set the file component from a file reference.
 *	<li><tt>{@link setFileUpload()}</tt>: Set the file component from an upload form.
 *	<li><tt>{@link setFileContents()}</tt>: Set the file component from the file contents.
 *	<li><tt>{@link getObjectMember()}</tt>: Retrieve the file native object.
 *	<li><tt>{@link setObjectMember()}</tt>: Set the file native object.
 *	<li><tt>{@link getContents()}</tt>: Retrieve the file contents.
 *	<li><tt>{@link getStream()}</tt>: Retrieve the file stream resource.
 *	<li><tt>{@link writeFile()}</tt>: Write the file to disk.
 * </ul>
 *
 * The class overloads the following static interface:
 *
 * <ul>
 *	<li><tt>{@link ResolveDatabase()}</tt>: By default objects of this class are stored in
 *		the users database.
 *	<li><tt>{@link ResolveCollection()}</tt>: It returns a {@link FileCollection} instance
 *		using the {@link DatabaseObject::filer()} method.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 21/02/2015
 */
abstract class FileObject extends PersistentObject
{
	/**
	 * File reference.
	 *
	 * This data member holds the <i>file reference</i> it can take the following types:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: In this case the current object is committed.
	 *	<li><tt>SplFileInfo</tt>: The file reference object.
	 *	<li><tt>array</tt>: The array must have one element at index <tt>name</tt> holding
	 *		the name attribute in the upload HTML form.
	 *	<li><tt>string</tt>: The file contents as a string of bytes.
	 * </ul>
	 *
	 * If the next member is set, this will be reset to <tt>NULL</tt>, this will happen when
	 * the object is committed.
	 *
	 * @var mixed
	 */
	protected $mFile = NULL;

	/**
	 * File object connection.
	 *
	 * This data member holds the persistent <i>native file object</i>, this data member is
	 * filled when the object is committed.
	 *
	 * @var mixed
	 */
	protected $mObject = NULL;

	/**
	 * Default collection name.
	 *
	 * This constant provides the <i>default collection name</i> in which objects of this
	 * class are stored.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_files';

		

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
	 * This class overloads the inherited constructor by handling instantiation by
	 * identifier.
	 *
	 * @param mixed					$theContainer		Data wrapper or properties.
	 * @param mixed					$theIdentifier		Object identifier or properties.
	 * @param boolean				$doAssert			Raise exception if not resolved.
	 *
	 * @access public
	 *
	 * @throws Exception
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
		// Query database.
		//
		if( ($theIdentifier !== NULL)
		 && (! is_array( $theIdentifier ))
		 && ($theContainer instanceof Wrapper) )
		{
			//
			// Set dictionary.
			//
			$this->dictionary( $theContainer );
		
			//
			// Find object.
			//
			$found
				= static::ResolveCollection(
					static::ResolveDatabase( $theContainer ) )
						->matchID( $theIdentifier, TRUE );
			
			//
			// Set committed status.
			//
			$this->isCommitted( TRUE );
			
			//
			// Extract object components.
			//
			$this->mFile = NULL;
			$this->mObject = $found;
			parent::__construct( $found->file );
		
		} // Provided object identifier.
		
		else
			parent::__construct( $theContainer, $theIdentifier, $doAssert );
		
		//
		// Set inited status.
		//
		$this->isInited( ($this->mFile !== NULL)
					  || ($this->mObject !== NULL) );

	} // Constructor.

	 
	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return file name</h4>
	 *
	 * In this class we return the stored object's file path.
	 *
	 * @access public
	 * @return string				The file path.
	 */
	public function __toString()							{	return $this->fileName();	}

	

/*=======================================================================================
 *																						*
 *							PUBLIC NAME MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getName																			*
	 *==================================================================================*/

	/**
	 * Get object name
	 *
	 * In this class we return by default the filename.
	 *
	 * @param string				$theLanguage		Name language.
	 *
	 * @access public
	 * @return string				Object name.
	 */
	public function getName( $theLanguage )	{	return $this->offsetGet( kTAG_FILE_NAME );	}

		

/*=======================================================================================
 *																						*
 *							PUBLIC FILE MEMBER MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getFileMember																	*
	 *==================================================================================*/

	/**
	 * Retrieve file member
	 *
	 * This method can be used to retrieve the file reference data member.
	 *
	 * @access public
	 * @return mixed				File reference, form name or contents.
	 *
	 * @throws Exception
	 */
	public function getFileMember()								{	return $this->mFile;	}

	 
	/*===================================================================================
	 *	clearFileMember																		*
	 *==================================================================================*/

	/**
	 * Clear file reference
	 *
	 * This method can be used to clear the file reference data member.
	 *
	 * @access public
	 */
	public function clearFileMember()
	{	
		//
		// Clear member.
		//
		$this->mFile = NULL;
		
		//
		// Set initialised status.
		//
		$this->isInited( $this->mObject !== NULL );
	
	} // clearFileMember.

	 
	/*===================================================================================
	 *	setFileReference																*
	 *==================================================================================*/

	/**
	 * Set file reference
	 *
	 * This method can be used to set the file reference data member from a file reference,
	 * the parameter can either be a file path string or an <tt>SplFileInfo</tt> reference.
	 *
	 * If the object is committed, the method will raise an exception.
	 *
	 * @param mixed					$theValue			File path or reference.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function setFileReference( $theValue )
	{
		//
		// Check if committed.
		//
		if( ! $this->committed() )
		{
			//
			// Convert to reference.
			//
			if( ! ($theValue instanceof \SplFileInfo) )
				$theValue = new \SplFileInfo( (string) $theValue );
			
			//
			// Check if readable.
			//
			if( $theValue->isReadable() )
				$this->mFile = $theValue;
		
			else
				throw new \Exception(
					"Unable to set file reference: "
				   ."the file is not readable." );								// !@! ==>
			
			//
			// Set initialised status.
			//
			$this->isInited( TRUE );
		
		} // Not committed.
		
		else
			throw new \Exception(
				"Unable to set file reference: "
			   ."the object is committed." );									// !@! ==>
	
	} // setFileReference.

	 
	/*===================================================================================
	 *	setFileUpload																	*
	 *==================================================================================*/

	/**
	 * Set file reference
	 *
	 * This method can be used to set the file reference data member from a file upload
	 * form, the parameter should be the upload HTML form element name.
	 *
	 * If the object is committed, the method will raise an exception.
	 *
	 * @param string				$theValue			HTML form element name.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function setFileUpload( $theValue )
	{
		//
		// Check if committed.
		//
		if( ! $this->committed() )
			$this->mFile = array( 'name' => (string) $theValue );
		
		else
			throw new \Exception(
				"Unable to set file upload: "
			   ."the object is committed." );									// !@! ==>
		
		//
		// Set initialised status.
		//
		$this->isInited( TRUE );
	
	} // setFileUpload.

	 
	/*===================================================================================
	 *	setFileContents																	*
	 *==================================================================================*/

	/**
	 * Set file contents
	 *
	 * This method can be used to set the file reference data member from the file contents,
	 * the parameter should be the contents expressed as a string of bytes.
	 *
	 * If the object is committed, the method will raise an exception.
	 *
	 * @param string				$theValue			File contents.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function setFileContents( $theValue )
	{
		//
		// Check if committed.
		//
		if( ! $this->committed() )
			$this->mFile = (string) $theValue;
		
		else
			throw new \Exception(
				"Unable to set file contents: "
			   ."the object is committed." );									// !@! ==>
		
		//
		// Set initialised status.
		//
		$this->isInited( TRUE );
	
	} // setFileContents.

		

/*=======================================================================================
 *																						*
 *						PUBLIC OBJECT MEMBER MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getObjectMember																	*
	 *==================================================================================*/

	/**
	 * Retrieve file object
	 *
	 * This method can be used to retrieve the native file object data member.
	 *
	 * @access public
	 * @return mixed				File object.
	 */
	public function getObjectMember()							{	return $this->mObject;	}

	 
	/*===================================================================================
	 *	setObjectMember																	*
	 *==================================================================================*/

	/**
	 * Set file object
	 *
	 * This method can be used to set the file object data member from the provided
	 * parameter, this method will set the data member with whatever it finds, derived
	 * concrete classes should first check the parameter type and then call the parent
	 * method.
	 *
	 * If the object is committed, the method will raise an exception.
	 *
	 * @param mixed					$theValue			File object.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function setObjectMember( $theValue )
	{
		//
		// Check if committed.
		//
		if( ! $this->committed() )
			$this->mObject = $theValue;
		
		else
			throw new \Exception(
				"Unable to set file contents: "
			   ."the object is committed." );									// !@! ==>
		
		//
		// Set initialised status.
		//
		$this->isInited( TRUE );
	
	} // setObjectMember.

		

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
	 * This method should return a string containing the file's contents, it is the duty
	 * of concrete derived classes to implement this feature.
	 *
	 * @access public
	 * @return string				File contents.
	 */
	abstract public function getContents();

	 
	/*===================================================================================
	 *	getStream																		*
	 *==================================================================================*/

	/**
	 * Get the file data
	 *
	 * This method should return a stream resource that can be used to read the file
	 * contents, it is the duty of concrete derived classes to implement this feature.
	 *
	 * @access public
	 * @return resource				File resource.
	 */
	abstract public function getStream();

	 
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
	abstract public function writeFile( $thePath = NULL );

		

/*=======================================================================================
 *																						*
 *								STATIC CONNECTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveDatabase																	*
	 *==================================================================================*/

	/**
	 * Resolve the database
	 *
	 * In this class we return the users database.
	 *
	 * @param Wrapper				$theWrapper			Wrapper.
	 * @param boolean				$doAssert			Raise exception if unable.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return DatabaseObject		Database or <tt>NULL</tt>.
	 *
	 * @throws Exception
	 */
	static function ResolveDatabase( Wrapper $theWrapper, $doAssert = TRUE, $doOpen = TRUE )
	{
		//
		// Get users database.
		//
		$database = $theWrapper->users();
		if( $database instanceof DatabaseObject )
		{
			//
			// Open connection.
			//
			if( $doOpen )
				$database->openConnection();
			
			return $database;														// ==>
		
		} // Retrieved metadata database.
		
		//
		// Raise exception.
		//
		if( $doAssert )
			throw new \Exception(
				"Unable to resolve database: "
			   ."missing users reference in wrapper." );						// !@! ==>
		
		return NULL;																// ==>
	
	} // ResolveDatabase.

	 
	/*===================================================================================
	 *	ResolveCollection																*
	 *==================================================================================*/

	/**
	 * Resolve the collection
	 *
	 * We overload this method to use the database {@link DatabaseObject::filer()} method.
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return CollectionObject		Collection or <tt>NULL</tt>.
	 */
	static function ResolveCollection( DatabaseObject $theDatabase, $doOpen = TRUE )
	{
		return $theDatabase->filer( $doOpen );										// ==>
	
	} // ResolveCollection.

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * In this class we link the inited status with the presence of either the file
	 * or the file object.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @uses isInited()
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Call parent method to resolve offset.
		//
		parent::postOffsetSet( $theOffset, $theValue );
		
		//
		// Set initialised status.
		//
		$this->isInited( ($this->mFile !== NULL)
					  || ($this->mObject !== NULL) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of either the file
	 * or the file object.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @uses isInited()
	 */
	protected function postOffsetUnset( &$theOffset )
	{
		//
		// Call parent method to resolve offset.
		//
		parent::postOffsetUnset( $theOffset );
		
		//
		// Set initialised status.
		//
		$this->isInited( ($this->mFile !== NULL)
					  || ($this->mObject !== NULL) );
	
	} // postOffsetUnset.

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	xmlUnitElement																	*
	 *==================================================================================*/

	/**
	 * Return XML unit element
	 *
	 * In this class we return the <tt>UNIT</tt> element.
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	protected function xmlUnitElement( \SimpleXMLElement $theRoot )
	{
		return $theRoot->addChild( kIO_XML_TRANS_UNITS );							// ==>
	
	} // xmlUnitElement.

	 

} // class FileObject.


?>
