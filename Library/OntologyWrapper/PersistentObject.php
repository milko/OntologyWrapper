<?php

/**
 * PersistentObject.php
 *
 * This file contains the definition of the {@link PersistentObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Wrapper;
use OntologyWrapper\OntologyObject;

/*=======================================================================================
 *																						*
 *								PersistentObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Query flags.
 *
 * This file contains the query flag definitions.
 */
require_once( kPATH_DEFINITIONS_ROOT."/Query.inc.php" );

/**
 * Persistent object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing objects that can
 * persist in a container and that are constituted by ontology offsets.
 *
 * The main purpose of this class is to add status and persistence management common to all
 * concrete derived classes.
 *
 * The class makes use of the {@link Status} trait to manage the object's state according to
 * actions:
 *
 * <ul>
 *	<li><tt>{@link isDirty}</tt>: This flag is set whenever any offset is modified, this
 *		status indicates that the contents of the object have changed since the lat time it
 *		was instantiated, loaded from a persistent store or committed to a persistent store.
 *	<li><tt>{@link isCommitted}</tt>: This flag is set whenever the object has been loaded
 *		or stored into a persistent container.
 * </ul>
 *
 * Objects derived from this class <em>must</em> define a constant called <em>kSEQ_NAME</em>
 * which provides a <em<string</em> representing the <em>default collection name</em> for
 * the current object: methods that commit or read objects of a specific class can then
 * resolve the collection given a database; this class does not declare this constant.
 *
 * All objects derived from this class feature the following offsets:
 *
 * <ul>
 *	<li><tt>{@link kTAG_NID}</tt>: This represents the primary key of the object, it should
 *		be of a scalar type and will be unique within the collection in which the object is
 *		stored. This property is required.
 *	<li><tt>{@link kTAG_CLASS}</tt>: This represents the PHP class name of the current
 *		object, this information is used when loading objects from the persistent store.
 *		This property is required.
 *	<li><tt>{@link kTAG_MASTER}</tt>: This property holds a reference to another object
 *		derived from the same class which holds the information regarding the object.
 *		If an object features this property it means that it is an <em>alias</em>, which
 *		indicates that bthere may be a set of other objects pointing to the same
 *		<em>master</em>. This is used to prevent duplicate information from being stored
 *		in the database. This property is optional.
 *	<li><tt>{@link kTAG_TAG_COUNT}</tt>: This property holds an integer indicating the
 *		number of tag objects referencing the current object.
 *	<li><tt>{@link kTAG_TERM_COUNT}</tt>: This property holds an integer indicating the
 *		number of term objects referencing the current object.
 *	<li><tt>{@link kTAG_NODE_COUNT}</tt>: This property holds an integer indicating the
 *		number of node objects referencing the current object.
 *	<li><tt>{@link kTAG_EDGE_COUNT}</tt>: This property holds an integer indicating the
 *		number of edge objects referencing the current object.
 *	<li><tt>{@link kTAG_UNIT_COUNT}</tt>: This property holds an integer indicating the
 *		number of unit objects referencing the current object.
 *	<li><tt>{@link kTAG_ENTITY_COUNT}</tt>: This property holds an integer indicating the
 *		number of entity objects referencing the current object.
 *	<li><tt>{@link kTAG_OBJECT_TAGS}</tt>: This property is an array listing all tags used
 *		as leaf offsets in the current object. This means all tags referenced by object
 *		offsets which hold a value and are not structures. This property is managed
 *		internally.
 *	<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: This property is an array holding the same
 *		number of elements as {@link kTAG_OBJECT_TAGS}, in this case the array key is the
 *		tag sequence number and the value is the list of offset paths in which the tag was
 *		referenced in the current object as a leaf offset.
 *	<li><tt>{@link kTAG_OBJECT_REFERENCES}</tt>: This property is an array holding the list
 *		of object references featured by the object, the array is indexed by collection name
 *		and the values represent the native identifiers of the objects in the collection.
 * </ul>
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 14/02/2014
 */
abstract class PersistentObject extends OntologyObject
{
	/**
	 * Status trait.
	 *
	 * In this class we handle the {@link is_committed()} flag.
	 */
	use	traits\Status;

		

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
	 * Objects derived from this class share the same constructor prototype and should not
	 * overload this method.
	 *
	 * The method accepts three parameters:
	 *
	 * <ul>
	 *	<li><b>$theContainer</b>: This may either be an array containing the object's
	 *		persistent attributes, or a reference to a {@link Wrapper} object. If this
	 *		parameter is <tt>NULL</tt>, the next parameter will be ignored.
	 *	<li><b>$theIdentifier</b>: This parameter represents the object identifier or the
	 *		object persistent attributes: in the first case it will used to select the
	 *		object from the wrapper provided in the previous parameter, in the second case,
	 *		it is assumed that the provided array holds the persistent attributes of an
	 *		object committed in the provided container.
	 *	<li><b>$doAssert</b>: This boolean parameter is relevant only if the first parameter
	 *		is a wrapper and the second is an identifier: if <tt>TRUE</tt>, the method will
	 *		raise an exception if the object was not found.
	 * </ul>
	 *
	 * The workflow is as follows:
	 *
	 * <ul>
	 *	<li><i>Empty object</i>: Both parameters are omitted.
	 *	<li><i>Empty object with wrapper</i>: The first parameter is a {@link Wrapper}
	 *		object and the second parameter is omitted. In this case an empty object is
	 *		instantiated, the committed status will not be set.
	 *	<li><i>Filled non committed object</i>: The first parameter is an array. In this
	 *		case the committed status is not set, but the object will have content.
	 *	<li><i>Filled committed object</i>: The first parameter is {@link Wrapper} object
	 *		and the second parameter is an array holding the object's persistent data. This
	 *		combination can be used when you want to load a persistent object with its
	 *		contents, in this case the object will be set committed.
	 *	<li><i>Load object from container</i>: The first parameter is a {@link Wrapper}
	 *		object and the second parameter is a scalar identifier. Use this combination to
	 *		load an object from the database, to check whether the object was loaded you
	 *		must call the {@link committed()} method or provide <tt>TRUE</tt> in the third
	 *		parameter to raise an exception if the object was not resolved; defaults to
	 *		<tt>TRUE</tt>.
	 * </ul>
	 *
	 * Any other combination will raise an exception.
	 *
	 * This constructor sets the {@link isCommitted()} flag, derived classes should first
	 * call the parent constructor, then they should set the {@link isInited()} flag.
	 *
	 * @param mixed					$theContainer		Data wrapper or properties.
	 * @param mixed					$theIdentifier		Object identifier or properties.
	 * @param boolean				$doAssert			Raise exception if not resolved.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @uses dictionary()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses isCommitted()
	 */
	public function __construct( $theContainer = NULL,
								 $theIdentifier = NULL,
								 $doAssert = TRUE )
	{
		//
		// Instantiate empty object.
		//
		if( $theContainer === NULL )
			parent::__construct();
		
		//
		// Load object attributes from array.
		//
		elseif( is_array( $theContainer ) )
			parent::__construct( $theContainer );
		
		//
		// Load object attributes from object.
		//
		elseif( ($theIdentifier === NULL)
		 && ($theContainer instanceof \ArrayObject)
		 && (! ($theContainer instanceof Wrapper)) )
			parent::__construct( $theContainer->getArrayCopy() );
		
		//
		// Handle wrapper.
		//
		elseif( $theContainer instanceof Wrapper )
		{
			//
			// Set dictionary.
			//
			$this->dictionary( $theContainer );
			
			//
			// Load object data.
			//
			if( is_array( $theIdentifier ) )
			{
				//
				// Set committed status.
				//
				$this->isCommitted( TRUE );
				
				//
				// Call parent constructor.
				//
				parent::__construct( $theIdentifier );
				
			} // Provided data.
			
			//
			// Resolve object.
			//
			elseif( $theIdentifier !== NULL )
			{
				//
				// Resolve collection.
				//
				$collection
					= static::ResolveCollection(
						static::ResolveDatabase( $theContainer ) );
			
				//
				// Find object.
				//
				$found = $collection->matchOne( array( kTAG_NID => $theIdentifier ),
												kQUERY_ARRAY );
				if( $found !== NULL )
				{
					//
					// Set committed status.
					//
					$this->isCommitted( TRUE );
				
					//
					// Call parent constructor.
					//
					parent::__construct( $found );
				
				} // Found.
				
				//
				// Not found.
				//
				elseif( $doAssert )
					throw new \Exception(
						"Cannot instantiate object: "
					   ."unresolved identifier [$theIdentifier]." );			// !@! ==>
				
				//
				// Empty object.
				//
				else
					parent::__construct();
			
			} // Provided identifier.
			
			//
			// Empty object.
			//
			else
				parent::__construct();
		
		} // Container connection.
		
		else
			throw new \Exception(
				"Cannot instantiate object: "
			   ."invalid container parameter type." );							// !@! ==>

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								PUBLIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	commit																			*
	 *==================================================================================*/

	/**
	 * Insert the object
	 *
	 * This method will insert the current object into the provided persistent store, the
	 * method expects the object not to be already committed, if that is the case the method
	 * will raise an exception: use the {@link committed()} method before you call this one.
	 *
	 * The method expects a single parameter representing the wrapper, this can be omitted
	 * if the object was instantiated with a wrapper.
	 *
	 * This method will perform the following steps:
	 *
	 * <ul>
	 *	<li>We resolve the wrapper or raise an exception.
	 *	<li>We call the <tt>{@link preCommit()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link preCommitPrepare()}</tt>: Prepare the object before committing.
	 *		<li><tt>{@link preCommitTraverse()}</tt>: Traverse the object's properties
	 *			validating formats and references.
	 *		<li><tt>{@link preCommitFinalise()}</tt>: Load the dynamic object properties and
	 *			compute the eventual object identifiers.
	 *	 </ul>
	 *	<li>We pass the current object to the collection's
	 *		{@link CollectionObject::commit()} method and recuperate the identifier.
	 *	<li>We call the <tt>{@link postCommit()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link postCommitReferences()}</tt>: Update object references.
	 *		<li><tt>{@link postCommitTags()}</tt>: Update object tags.
	 *	 </ul>
	 *	<li>We set the object {@link isCommitted()} and reset the {@link isDirty()} status.
	 *	<li>We return the object's identifier.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * This method is declared <em>final</em>, to customise the operation you should
	 * overload the methods called in this method.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 *
	 * @access public
	 * @return mixed				The object's native identifier.
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 * @uses resolveWrapper()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses preCommit()
	 * @uses postCommit()
	 * @uses isDirty()
	 */
	public final function commit( $theWrapper = NULL )
	{
		//
		// Do it only if the object is not committed.
		//
		if( ! $this->isCommitted() )
		{
			//
			// Resolve wrapper.
			//
			$this->resolveWrapper( $theWrapper );
			
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $theWrapper ) );
		
			//
			// Prepare object.
			//
			$this->preCommit( $tags, $references );
		
			//
			// Commit.
			//
			$id = $collection->commit( $this );
	
			//
			// Copy identifier if generated.
			//
			if( ! $this->offsetExists( kTAG_NID ) )
				$this->offsetSet( kTAG_NID, $id );
		
			//
			// Cleanup object.
			//
			$this->postCommit( $tags, $references );
	
			//
			// Set object status.
			//
			$this->isDirty( FALSE );
			$this->isCommitted( TRUE );
		
			return $id;																// ==>
		
		} // Dirty or not committed.
		
		throw new \Exception(
			"Cannot commit object: "
		   ."the object is already committed." );								// !@! ==>
	
	} // commit.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MASTER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setAlias																		*
	 *==================================================================================*/

	/**
	 * Signal object as alias
	 *
	 * This method can be used to set or reset the object {@link isAlias()} flag, this
	 * signals that the current object is an alias of the object referenced by the
	 * {@link kTAG_MASTER} offset value: to set the status pass <tt>TRUE</tt> in the
	 * parameter and <tt>FALSE</tt> to reset it.
	 *
	 * This method should only be called on non committed objects, once set, this status is
	 * immutable, so in that case the method will raise an exception.
	 *
	 * When resetting the status, the method will also remove the eventual
	 * {@link kTAG_MASTER} attribute.
	 *
	 * <em>Note that not any object can be set as alias: objects that can take this state
	 * must feature a method that selects their master object, so you should shadow this
	 * method in derived classes that do not implement the concept of master and alias.
	 *
	 * @param boolean				$doSet				<tt>TRUE</tt> to set.
	 *
	 * @access public
	 *
	 * @throws Exception
	 *
	 * @see kTAG_MASTER
	 *
	 * @uses isAlias()
	 * @uses isCommitted()
	 */
	public function setAlias( $doSet = TRUE )
	{
		//
		// Normalise flag.
		//
		$doSet = (boolean) $doSet;
		
		//
		// Set status.
		//
		if( $doSet )
		{
			//
			// Check if needed.
			//
			if( ! $this->isAlias() )
			{
				//
				// Check if committed.
				//
				if( ! $this->isCommitted() )
					$this->isAlias( $doSet );
			
				else
					throw new \Exception(
						"Cannot set alias status: "
					   ."the object is already committed." );					// !@! ==>
		
			} // Not an alias already.
		
		} // Set status
		
		//
		// Reset status.
		//
		else
		{
			//
			// Check if needed.
			//
			if( $this->isAlias() )
			{
				//
				// Check if committed.
				//
				if( ! $this->isCommitted() )
				{
					//
					// Set status.
					//
					$this->isAlias( $doSet );
					
					//
					// Remove master.
					//
					$this->offsetUnset( kTAG_MASTER );
				
				} // Not committed.
			
				else
					throw new \Exception(
						"Cannot reset alias status: "
					   ."the object is already committed." );					// !@! ==>
		
			} // Not an alias already.
		
		} // Reset status.
	
	} // setAlias.

		

/*=======================================================================================
 *																						*
 *								STATIC OFFSET INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	PrivateOffsets																	*
	 *==================================================================================*/

	/**
	 * Return private offsets
	 *
	 * This method will return the current object list of private offsets, these offsets
	 * are managed internally by the class and should not be handled by clients or be part
	 * of the tag and offset paths statistics.
	 *
	 * In this class we return:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_TAG_COUNT}</tt>: Tag property references.
	 *	<li><tt>{@link kTAG_TAG_OFFSETS}</tt>: Tag offset path references.
	 *	<li><tt>{@link kTAG_TERM_COUNT}</tt>: Term property references.
	 *	<li><tt>{@link kTAG_TERM_OFFSETS}</tt>: Term offset path references.
	 *	<li><tt>{@link kTAG_NODE_COUNT}</tt>: Node property references.
	 *	<li><tt>{@link kTAG_NODE_OFFSETS}</tt>: Node offset path references.
	 *	<li><tt>{@link kTAG_EDGE_COUNT}</tt>: Edge property references.
	 *	<li><tt>{@link kTAG_EDGE_OFFSETS}</tt>: Edge offset path references.
	 *	<li><tt>{@link kTAG_UNIT_COUNT}</tt>: Unit property references.
	 *	<li><tt>{@link kTAG_UNIT_OFFSETS}</tt>: Unit offset path references.
	 *	<li><tt>{@link kTAG_ENTITY_COUNT}</tt>: Entities property references.
	 *	<li><tt>{@link kTAG_ENTITY_OFFSETS}</tt>: Entity offset path references.
	 *	<li><tt>{@link kTAG_OBJECT_TAGS}</tt>: Object tags list.
	 *	<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: Object offsets list.
	 *	<li><tt>{@link kTAG_OBJECT_REFERENCES}</tt>: Object object references list.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function PrivateOffsets()
	{
		return array
		(
			kTAG_TAG_COUNT, kTAG_TAG_OFFSETS,
			kTAG_TERM_COUNT, kTAG_TERM_OFFSETS,
			kTAG_NODE_COUNT, kTAG_NODE_OFFSETS,
			kTAG_EDGE_COUNT, kTAG_EDGE_OFFSETS,
			kTAG_UNIT_COUNT, kTAG_UNIT_OFFSETS,
			kTAG_ENTITY_COUNT, kTAG_ENTITY_OFFSETS,
			kTAG_OBJECT_TAGS, kTAG_OBJECT_OFFSETS, kTAG_OBJECT_REFERENCES
		);																			// ==>
	
	} // PrivateOffsets.

		

/*=======================================================================================
 *																						*
 *								STATIC PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	CreateIndexes																	*
	 *==================================================================================*/

	/**
	 * Create indexes
	 *
	 * This method will create the default indexes for the current class.
	 *
	 * In this class we index the following offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_MASTER}</tt>: This will speed the search for the master object.
	 *	<li><tt>{@link kTAG_OBJECT_TAGS}</tt>: This will help the selection of objects
	 *		based on the searched offsets.
	 *	<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: This is necessary to speed referencial
	 *		integrity.
	 * </ul>
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 *
	 * @static
	 * @return CollectionObject		The collection.
	 */
	static function CreateIndexes( DatabaseObject $theDatabase )
	{
		//
		// Get and open collection.
		//
		$collection = $theDatabase->Collection( static::kSEQ_NAME );
		
		//
		// Set master.
		//
		$collection->createIndex( array( kTAG_MASTER => 1 ),
								  array( "name" => "MASTER",
								  		 "sparse" => TRUE ) );
		
		//
		// Set tags.
		//
		$collection->createIndex( array( kTAG_OBJECT_TAGS => 1 ),
								  array( "name" => "TAGS" ) );
		
		//
		// Set offsets.
		//
		$collection->createIndex( array( kTAG_OBJECT_OFFSETS => 1 ),
								  array( "name" => "OFFSETS" ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.


	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * Delete an object
	 *
	 * This method will delete the object corresponding to the provided native identifier.
	 *
	 * Deleting objects is a static matter, since, in order to ensure referential integrity,
	 * it is necessary to traverse an object which is a mirror of the persistent image. The
	 * methods which actually delete the object are declared protected, so that it should
	 * not be easy to delete an incomplete object.
	 *
	 * This method should be called using the base class of the object one wants to delete,
	 * this means that either you know the class, or you may call this method from the
	 * object that you want to delete.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: The data wrapper.
	 *	<li><b>$theIdentifier</b>: The object native identifier.
	 * </ul>
	 *
	 * The method will first load the object from the persistent store and then it will
	 * call its protected {@link deleteObject()} method, returning:
	 *
	 * <ul>
	 *	<li><em>Native identifier</em>: If the object was deleted, the method will return
	 *		the deleted object's native identifier.
	 *	<li><tt>NULL</tt>: This value will be returned if the method was unable to locate
	 *		the object.
	 *	<li><tt>FALSE</tt>: This value will be returned if the object features reference
	 *		counts, this means that other objects reference it and for that reason it cannot
	 *		be deleted.
	 * </ul>
	 *
	 * When loading the object only the {@link kTAG_NID}, {@link kTAG_OBJECT_OFFSETS} and
	 * {@link kTAG_OBJECT_REFERENCES} will be loaded, since these are the offsets that
	 * contain the necessary information for maintaining referential integrity, if you need
	 * to handle other object offsets, you should overload the static
	 * {@link DeleteFieldsSelection()} method.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theIdentifier		Object native identifier.
	 *
	 * @static
	 * @return mixed				Identifier, <tt>NULL</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 */
	static function Delete( Wrapper $theWrapper, $theIdentifier )
	{
		//
		// Check if wrapper is connected.
		//
		if( ! $theWrapper->isConnected() )
			throw new \Exception(
				"Unable to resolve collection: "
			   ."wrapper is not connected." );									// !@! ==>
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) );
		
		//
		// Set criteria.
		//
		$criteria = array( kTAG_NID => $theIdentifier );
		
		//
		// Select fields.
		//
		$fields = static::DeleteFieldsSelection();
		
		//
		// Resolve object.
		//
		$object = $collection->matchOne( $criteria, kQUERY_OBJECT, $fields );
		
		//
		// Delete object.
		//
		if( $object instanceof self )
			return $object->deleteObject();											// ==>
		
		return NULL;																// ==>
	
	} // Delete.


	/*===================================================================================
	 *	Modify																			*
	 *==================================================================================*/

	/**
	 * Modify an object
	 *
	 * This method will modify the object corresponding to the provided identifier by either
	 * adding, replacing or removing the provided offsets.
	 *
	 * Modifying objects is a static matter, since, in order to ensure referential integrity,
	 * it is necessary to traverse an object which is a mirror of the persistent image. The
	 * methods which actually modify the object are declared protected, so that it should
	 * not be easy to modify an incomplete object.
	 *
	 * This method should be called using the base class of the object one wants to modify,
	 * this means that either you know the class, or you may call this method from the
	 * object that you want to modify.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: The data wrapper.
	 *	<li><b>$theIdentifier</b>: The object native identifier.
	 *	<li><b>$theOffsets</b>: The offsets to be added, replaced or deleted. This parameter
	 *		is structured according to the requested operation:
	 *	 <ul>
	 *		<li><em>Add or replace</tt>: This occurs if the last parameter is <tt>TRUE</tt>.
	 *			The parameter should be an array indexed by tag sequence number and value
	 *			representing the offset value. All offsets will be added at the root level.
	 *		<li><em>Remove</tt>: This occurs if the last parameter is <tt>FALSE</tt>.
	 *			The parameter should be an array listing all the offsets to be removed at
	 *			the root level.
	 *	 </ul>
	 *	<li><b>$doSet</b>: <tt>TRUE</tt> means add or replace offsets, <tt>FALSE</tt> means
	 *		remove offsets.
	 * </ul>
	 *
	 * The method will first load the object from the persistent store and then it will
	 * call its protected {@link modifyObject()} method.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theIdentifier		Object native identifier.
	 * @param mixed					$theOffsets			Offsets to be modified.
	 * @param boolean				$doSet				<tt>TRUE</tt> means add or replace.
	 *
	 * @static
	 * return PersistentObject		Modified object.
	 *
	 * @throws Exception
	 */
	static function Modify( Wrapper $theWrapper,
									$theIdentifier,
									$theOffsets,
									$doSet )
	{
		//
		// Check if wrapper is connected.
		//
		if( ! $theWrapper->isConnected() )
			throw new \Exception(
				"Unable to modify object: "
			   ."wrapper is not connected." );									// !@! ==>
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $theWrapper ) );
		
		//
		// Load original object.
		//
		$object = $collection->matchOne( array( kTAG_NID => $theIdentifier ),
										 kQUERY_ASSERT | kQUERY_OBJECT );
		
		//
		// Modify object.
		//
		$object->modifyObject( $theOffsets, $doSet );
		
		return $object;																// ==>
	
	} // Modify.

	 
	/*===================================================================================
	 *	DeleteFieldsSelection															*
	 *==================================================================================*/

	/**
	 * Return delete fields selection
	 *
	 * This method should return the fields selection used to load the object to be deleted.
	 * By default we load the {@link kTAG_NID}, {@link kTAG_OBJECT_OFFSETS} and
	 * {@link kTAG_OBJECT_REFERENCES} properties, derived classes may overload this method
	 * to add other offsets.
	 *
	 * @static
	 * @return array				Delete object fields selection.
	 *
	 * @throws Exception
	 */
	static function DeleteFieldsSelection()
	{
		return array( kTAG_NID => TRUE,
					  kTAG_OBJECT_OFFSETS => TRUE,
					  kTAG_OBJECT_REFERENCES => TRUE );								// ==>
	
	} // DeleteFieldsSelection.

		

/*=======================================================================================
 *																						*
 *								STATIC RESOLUTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	ResolveDatabase																	*
	 *==================================================================================*/

	/**
	 * Resolve the database
	 *
	 * This method should return a {@link DatabaseObject} instance corresponding to the
	 * default database of the current class extracted from the provided {@link Wrapper}
	 * instance.
	 *
	 * Since we cannot declare this method abstract, we raise an exception.
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
		throw new \Exception(
			"Unable to resolve database: "
		   ."this method must be implemented." );								// !@! ==>
	
	} // ResolveDatabase.

	 
	/*===================================================================================
	 *	ResolveCollection																*
	 *==================================================================================*/

	/**
	 * Resolve the collection
	 *
	 * This method should return a {@link CollectionObject} instance corresponding to the
	 * persistent store in which the current object was either read or will be inserted.
	 *
	 * The method expects the object to feature a constant, {@link kSEQ_NAME}, which serves
	 * the double purpose of providing the default collection name and the eventual sequence
	 * number index: the method will use this constant and the provided database reference
	 * to return the default {@link CollectionObject} instance.
	 *
	 * @param DatabaseObject		$theDatabase		Database reference.
	 * @param boolean				$doOpen				<tt>TRUE</tt> open connection.
	 *
	 * @static
	 * @return CollectionObject		Collection or <tt>NULL</tt>.
	 */
	static function ResolveCollection( DatabaseObject $theDatabase, $doOpen = TRUE )
	{
		return $theDatabase->Collection( static::kSEQ_NAME, $doOpen );				// ==>
	
	} // ResolveCollection.

		
	/*===================================================================================
	 *	ResolveCollectionByName															*
	 *==================================================================================*/

	/**
	 * Resolve collection
	 *
	 * Given a wrapper and a collection name, this method will return a collection
	 * reference.
	 *
	 * If the wrapper is not connected, or if the collection could not be resolved, the
	 * method will raise an exception.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param string				$theCollection		Collection name.
	 *
	 * @static
	 * @return CollectionObject		The collection reference.
	 *
	 * @throws Exception
	 */
	static function ResolveCollectionByName( Wrapper $theWrapper, $theCollection )
	{
		//
		// Check if wrapper is connected.
		//
		if( ! $theWrapper->isConnected() )
			throw new \Exception(
				"Unable to resolve collection: "
			   ."wrapper is not connected." );									// !@! ==>
		
		//
		// Resolve collection.
		//
		switch( (string) $theCollection )
		{
			case Tag::kSEQ_NAME:
				return Tag::ResolveCollection(
						Tag::ResolveDatabase( $theWrapper ) );						// ==>
				
			case Term::kSEQ_NAME:
				return Term::ResolveCollection(
						Term::ResolveDatabase( $theWrapper ) );						// ==>
				
			case Node::kSEQ_NAME:
				return Node::ResolveCollection(
						Node::ResolveDatabase( $theWrapper ) );						// ==>
				
			case Edge::kSEQ_NAME:
				return Edge::ResolveCollection(
						Edge::ResolveDatabase( $theWrapper ) );						// ==>
				
			case EntityObject::kSEQ_NAME:
				return EntityObject::ResolveCollection(
						EntityObject::ResolveDatabase( $theWrapper ) );				// ==>
				
			case UnitObject::kSEQ_NAME:
				return UnitObject::ResolveCollection(
						UnitObject::ResolveDatabase( $theWrapper ) );				// ==>
			
			default:
				throw new \Exception(
					"Cannot resolve collection: "
				   ."invalid collection name [$theCollection]." );				// !@! ==>
		
		} // Parsed collection name.
	
	} // ResolveCollectionByName.

	 
	/*===================================================================================
	 *	ResolveRefCountTag																*
	 *==================================================================================*/

	/**
	 * Resolve reference count tag
	 *
	 * Given a collection name, this method will return the tag sequence number
	 * corresponding to the offset holding the number of objects, stored in the provided
	 * collection reference, that reference the current object.
	 *
	 * If the tag could not be resolved, the method will raise an exception.
	 *
	 * @param string				$theCollection		Collection name.
	 *
	 * @static
	 * @return integer				Tag sequence number.
	 *
	 * @throws Exception
	 */
	static function ResolveRefCountTag( $theCollection )
	{
		//
		// Select reference count tag according to provided collection.
		//
		switch( (string) $theCollection )
		{
			case Tag::kSEQ_NAME:
				return kTAG_TAG_COUNT;												// ==>
		
			case Term::kSEQ_NAME:
				return kTAG_TERM_COUNT;												// ==>
		
			case Node::kSEQ_NAME:
				return kTAG_NODE_COUNT;												// ==>
	
			case Edge::kSEQ_NAME:
				return kTAG_EDGE_COUNT;												// ==>
		
			case EntityObject::kSEQ_NAME:
				return kTAG_ENTITY_COUNT;											// ==>
		
			case UnitObject::kSEQ_NAME:
				return kTAG_UNIT_COUNT;												// ==>
		
			default:
				throw new \Exception(
					"Cannot resolve reference count tag: "
				   ."invalid collection name [$theCollection]." );				// !@! ==>
		
		} // Parsed collection name.
	
	} // ResolveRefCountTag.

	 
	/*===================================================================================
	 *	ResolveOffsetsTag																*
	 *==================================================================================*/

	/**
	 * Resolve offsets tag
	 *
	 * Given a collection name, this method will return the tag sequence number
	 * corresponding to the tag object offset holding the set of offsets in which the tag
	 * was used by objects stored in the provided collection.
	 *
	 * If the tag could not be resolved, the method will raise an exception.
	 *
	 * @param string				$theCollection		Collection name.
	 *
	 * @static
	 * @return integer				Tag sequence number.
	 *
	 * @throws Exception
	 */
	static function ResolveOffsetsTag( $theCollection )
	{
		//
		// Select offsets tag according to provided collection.
		//
		switch( (string) $theCollection )
		{
			case Tag::kSEQ_NAME:
				return kTAG_TAG_OFFSETS;											// ==>
		
			case Term::kSEQ_NAME:
				return kTAG_TERM_OFFSETS;											// ==>
		
			case Node::kSEQ_NAME:
				return kTAG_NODE_OFFSETS;											// ==>
	
			case Edge::kSEQ_NAME:
				return kTAG_EDGE_OFFSETS;											// ==>
		
			case EntityObject::kSEQ_NAME:
				return kTAG_ENTITY_OFFSETS;											// ==>
		
			case UnitObject::kSEQ_NAME:
				return kTAG_UNIT_OFFSETS;											// ==>
		
			default:
				throw new \Exception(
					"Cannot resolve offsets tag: "
				   ."invalid collection name [$theCollection]." );				// !@! ==>
		
		} // Parsed collection name.
	
	} // ResolveOffsetsTag.

		

/*=======================================================================================
 *																						*
 *								STATIC DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	GetReferenceKey																	*
	 *==================================================================================*/

	/**
	 * Return reference key
	 *
	 * The reference key is the offset that will be used when storing a set of objects into
	 * a multi class matrix. By default we use the native identifier, but in some cases
	 * other required and unique identifiers may be used for this purpose.
	 *
	 * In this class we use {@link kTAG_NID}.
	 *
	 * @static
	 * @return string				Key offset.
	 */
	static function GetReferenceKey()								{	return kTAG_NID;	}

		
	/*===================================================================================
	 *	GetReferenceTypes																*
	 *==================================================================================*/

	/**
	 * Get reference types
	 *
	 * This method will return the list of types that represent an object reference.
	 *
	 * @static
	 * @return array				List of reference types.
	 */
	static function GetReferenceTypes()
	{
		return array( kTYPE_REF_TAG, kTYPE_REF_TERM, kTYPE_REF_NODE, kTYPE_REF_EDGE,
					  kTYPE_REF_ENTITY, kTYPE_REF_UNIT,
					  kTYPE_REF_SELF,
					  kTYPE_ENUM, kTYPE_SET );										// ==>
	
	} // GetReferenceTypes.

		

/*=======================================================================================
 *																						*
 *							PROTECTED ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before setting it
	 *
	 * We overload this method to prevent modifying the global and native identifiers if the
	 * object is committed, {@link isCommitted()}.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> set offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 * @uses lockedOffsets()
	 */
	protected function preOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Resolve offset.
		//
		$ok = parent::preOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
		{
			//
			// Check if committed.
			//
			if( $this->isCommitted() )
			{
				//
				// Check immutable tags.
				//
				if( in_array( $theOffset, $this->lockedOffsets() ) )
					throw new \Exception(
						"Cannot set the [$theOffset] offset: "
					   ."the object is committed." );							// !@! ==>
		
			} // Object is committed.
			
			//
			// Parse offsets.
			//
			switch( $theOffset )
			{
				//
				// Check container structure.
				//
				case kTAG_TAG_STRUCT:
					$tag = $this->dictionary()->getObject( $theValue );
					if( $tag !== NULL )
					{
						if( array_key_exists( kTAG_DATA_TYPE, $tag ) )
						{
							if( ! in_array( kTYPE_STRUCT, $tag[ kTAG_DATA_TYPE ] ) )
								throw new \Exception(
									"Cannot set the [$theOffset] offset: "
								   ."[$theValue] is not a structure." );		// !@! ==>
						}
						else
							throw new \Exception(
								"Cannot set the [$theOffset] offset: "
							   ."[$theValue] is missing its data type." );		// !@! ==>
					}
					else
						throw new \Exception(
							"Cannot set the [$theOffset] offset: "
						   ."missing data dictionary." );						// !@! ==>
					break;
			
			} // Parsed offsets.
		
		} // Intercepted by preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetSet																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value after setting it
	 *
	 * We overload the parent method to set the {@link isDirty()} status.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @uses isDirty()
	 */
	protected function postOffsetSet( &$theOffset, &$theValue )
	{
		//
		// Resolve offset.
		//
		$ok = parent::postOffsetSet( $theOffset, $theValue );
		if( $ok === NULL )
			$this->isDirty( TRUE );
		
		//
		// Handle master.
		//
		if( $theOffset == kTAG_MASTER )
			$this->setAlias( TRUE );
		
		return $ok;																	// ==>
		
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	preOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset and value before deleting it
	 *
	 * We overload this method to prevent modifying the global and native identifiers if the
	 * object is committed, {@link isCommitted()}.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt> delete offset value, other, return.
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 * @uses lockedOffsets()
	 */
	protected function preOffsetUnset( &$theOffset )
	{
		//
		// Resolve offset.
		//
		$ok = parent::preOffsetUnset( $theOffset );
		if( $ok === NULL )
		{
			//
			// Check if committed.
			//
			if( $this->isCommitted() )
			{
				//
				// Check immutable tags.
				//
				if( in_array( $theOffset, $this->lockedOffsets() ) )
					throw new \Exception(
						"Cannot delete the [$theOffset] offset: "
					   ."the object is committed." );							// !@! ==>
		
			} // Object is committed.
		
		} // Intercepted by preflight.
		
		return $ok;																	// ==>
	
	} // preOffsetUnset.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * This method can be used to manage the object after calling the
	 * {@link ArrayObject::OffsetUnset()} method.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @uses isDirty()
	 */
	protected function postOffsetUnset( &$theOffset )
	{
		//
		// Resolve offset.
		//
		$ok = parent::postOffsetUnset( $theOffset );
		if( $ok === NULL )
			$this->isDirty( TRUE );
		
		//
		// Handle master.
		//
		if( $theOffset == kTAG_MASTER )
			$this->setAlias( FALSE );
		
		return $ok;																	// ==>
		
	} // postOffsetUnset.

		

/*=======================================================================================
 *																						*
 *							PROTECTED PERSISTENCE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	deleteObject																	*
	 *==================================================================================*/

	/**
	 * Delete the object
	 *
	 * This method will delete the current object from its persistent store, the method
	 * expects the current object to be {@link isCommitted()} and not {@link isDirty()},
	 * if that is not the case, the method will raise an exception.
	 *
	 * In particular, <em>the object must contain the {@link kTAG_OBJECT_OFFSETS} and
	 * {@link kTAG_OBJECT_REFERENCES} properties ensure that all references are reset</em>.
	 * This is fundamental for ensuring referential integrity. For this reason the static
	 * method, {@link Delete()}, is the preferred method for deleting objects.
	 *
	 * This method will perform the following steps:
	 *
	 * <ul>
	 *	<li>We call the <tt>{@link preDelete()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link preDeletePrepare()}</tt>: Check if the object can be deleted, if
	 *			that is not the case, the method should return <tt>FALSE</tt>.
	 *		<li><tt>{@link preDeleteFinalise()}</tt>: Perform final operations before the
	 *			object is deleted.
	 *	 </ul>
	 *	<li>We pass the current object to the collection's
	 *		{@link CollectionObject::delete()} method and recuperate the identifier.
	 *	<li>We call the <tt>{@link postDelete()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link postDeleteReferences()}</tt>: Update object references.
	 *		<li><tt>{@link postDeleteTags()}</tt>: Update object tags.
	 *	 </ul>
	 *	<li>We reset both the object's {@link isCommitted()} and {@link isDirty()} status.
	 *	<li>We return the object's identifier.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * The method will return the object's native identifier if it was deleted; if the
	 * object is not committed, or if the object does not feature the native identifier,
	 * the method will return <tt>NULL</tt>; if the object is referenced, or if for any
	 * other reason the object cannot be deleted, the method will return <tt>FALSE</tt>.
	 *
	 * This method is declared <em>final</em>, to customise the operation you should
	 * overload the methods called in this method.
	 *
	 * @access protected
	 * @return mixed				Native identifier, <tt>NULL</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses isCommitted()
	 * @uses isDirty()
	 * @uses ResolveDatabase()
	 * @uses ResolveCollection()
	 * @uses preDelete()
	 * @uses postDelete()
	 */
	protected final function deleteObject()
	{
		//
		// Do it only if the object is committed and clean.
		//
		if( $this->isCommitted()
		 && (! $this->isDirty()) )
		{
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $this->mDictionary ) );
		
			//
			// Prepare object.
			//
			if( ! $this->preDelete( $tags, $references ) )
				return FALSE;														// ==>
		
			//
			// Delete.
			//
			$id = $collection->delete( $this );
		
			//
			// Update references.
			//
			$this->postDelete( $tags, $references );
	
			//
			// Set object status.
			//
			$this->isDirty( FALSE );
			$this->isCommitted( FALSE );
		
			return $id;																// ==>
		
		} // Clean and committed.
		
		throw new \Exception(
			"Cannot delete object: "
		   ."the object is not committed or was modified." );					// !@! ==>
	
	} // deleteObject.

	 
	/*===================================================================================
	 *	modifyObject																	*
	 *==================================================================================*/

	/**
	 * Modify object
	 *
	 * This method will modify the current object by either adding, replacing or deleting
	 * the provided offsets. The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theOffsets</b>: The offsets to be added, replaced or deleted. This parameter
	 *		is structured according to the requested operation:
	 *	 <ul>
	 *		<li><em>Add or replace</tt>: This occurs if the last parameter is <tt>TRUE</tt>.
	 *			The parameter should be an array indexed by tag sequence number and value
	 *			representing the offset value. All offsets will be added at the root level.
	 *		<li><em>Remove</tt>: This occurs if the last parameter is <tt>FALSE</tt>.
	 *			The parameter should be an array listing all the offsets to be removed at
	 *			the root level.
	 *	 </ul>
	 *	<li><b>$doSet</b>: <tt>TRUE</tt> means add or replace offsets, <tt>FALSE</tt> means
	 *		remove offsets.
	 * </ul>
	 *
	 * This method will call the {@link modifyObjectAdd()} method if setting and the
	 * {@link modifyObjectDel()} method if removing.
	 *
	 * The method will return the number of elements affected by the operation (1 or 0).
	 *
	 * @param mixed					$theOffsets			Offsets to be modified.
	 * @param boolean				$doSet				<tt>TRUE</tt> means add or replace.
	 *
	 * @access protected
	 * return integer				Number of objects affected.
	 *
	 * @throws Exception
	 */
	protected function modifyObject( $theOffsets, $doSet )
	{
		//
		// Do it only if the object is committed and clean.
		//
		if( $this->isCommitted()
		 && (! $this->isDirty()) )
		{
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $this->mDictionary ) );
		
			//
			// Set offsets.
			//
			if( $doSet )
				return $this->modifyObjectAdd( $collection, $theOffsets );			// ==>
		
			return $this->modifyObjectDel( $collection, $theOffsets );				// ==>
		
		} // Clean and committed.
		
		throw new \Exception(
			"Cannot modify object: "
		   ."the object is not committed or was modified." );					// !@! ==>
	
	} // modifyObject.

	 
	/*===================================================================================
	 *	modifyObjectAdd																	*
	 *==================================================================================*/

	/**
	 * Add offsets to object
	 *
	 * This method can be used to add the provided properties to the current object, the
	 * provided offsets will be added or will replace the offsets contained in the current
	 * object.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The object's persistent store.
	 *	<li><b>$theOffsets</b>: The offsets to be added or replaced, this parameter should
	 *		be an array of properties in which the element key is the offset and the value
	 *		is the offset value.
	 * </ul>
	 *
	 * In this method we perform the following steps:
	 *
	 * <ul>
	 *	<li>We update the {@link kTAG_OBJECT_TAGS} property.
	 *	<li>We update the {@link kTAG_OBJECT_OFFSETS} property.
	 *	<li>We update the {@link kTAG_OBJECT_REFERENCES} property.
	 *	<li>We update the metadata of new tags and references.
	 *	<li>We update the metadata of removed tags, offsets and references.
	 *	<li>We update the metadata of new offsets of common collections.
	 *	<li>We update the metadata of removed offsets of common collections.
	 *	<li>We replace the offsets in the stored object.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * The method will return the modified object.
	 *
	 * @param CollectionObject		$theCollection		Object collection.
	 * @param mixed					$theOffsets			Modification properties.
	 *
	 * @access protected
	 * return integer				Number of objects affected.
	 */
	protected function modifyObjectAdd( CollectionObject $theCollection, $theOffsets )
	{
		//
		// Init local storage.
		//
		$id = $this->offsetGet( kTAG_NID );
		
		//
		// Instantiate modifications as object.
		//
		if( $theOffsets instanceof self )
			$mods = $theOffsets;
		elseif( is_array( $theOffsets ) )
		{
			$class = get_class( $this );
			$mods = new $class( $this->mDictionary, $theOffsets );
		}
		else
			throw new \Exception(
				"Cannot modify offsets: "
			   ."invalid modifications parameter type." );						// !@! ==>
		
		//
		// Validate, collect tags, offsets and references.
		//
		$mods_tags = $mods_refs = Array();
		$mods->preCommitTraverse( $mods_tags, $mods_refs );
		
		//
		// Save old tags and references.
		//
		$old_tags = $this->offsetGet( kTAG_OBJECT_OFFSETS );
		$old_refs = $this->offsetGet( kTAG_OBJECT_REFERENCES );
		
		//
		// Add and replace properties.
		//
		foreach( $mods as $key => $value )
			$this->offsetSet( $key, $value );
		
		//
		// Collect tags, offsets and references.
		//
		$cur_tags = $cur_refs = Array();
		$this->preCommitTraverse( $cur_tags, $cur_refs, FALSE );
		
		//
		// Replace tags list.
		//
		$this->offsetSet( kTAG_OBJECT_TAGS, array_keys( $cur_tags ) );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_TAGS => array_keys( $cur_tags ) ) );
	
		//
		// Replace tag offsets.
		//
		$tmp = Array();
		foreach( $cur_tags as $key => $value )
			$tmp[ $key ] = $value[ kTAG_OBJECT_OFFSETS ];
		$this->offsetSet( kTAG_OBJECT_OFFSETS, $tmp );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_OFFSETS => $tmp ) );
		
		//
		// Replace object references.
		//
		$this->offsetSet( kTAG_OBJECT_REFERENCES, $cur_refs );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_REFERENCES => $cur_refs ) );
		
		//
		// Select new tags and references.
		//
		$tags = array_diff_key( $mods_tags, $old_tags );
		$refs = $mods->compareObjectOffsets( $mods_refs, $old_refs, TRUE );
			
		//
		// Update metadata.
		//
		$mods->postCommit( $tags, $refs );
		
		//
		// Select removed tags and references.
		//
		$tags = array_diff_key( $old_tags, $cur_tags );
		$refs = $mods->compareObjectOffsets( $old_refs, $cur_refs, TRUE );
			
		//
		// Update metadata.
		//
		$mods->postDelete( $tags, $refs );
		
		//
		// Select common tags.
		//
		$tags = array_intersect_key( $mods_tags, $old_tags );
		
		//
		// Filter new offsets.
		//
		$set = $tags;
		foreach( $tags as $tag => $info )
		{
			$tmp = array_diff( $info[ kTAG_OBJECT_OFFSETS ],
							   $old_tags[ $tag ] );
			if( ! count( $tmp ) )
				unset( $set[ $key ] );
			else
				$set[ $tag ][ kTAG_OBJECT_OFFSETS ] = $tmp;
		}
		
		//
		// Add new offsets.
		//
		if( count( $set ) )
			$mods->postCommitTagOffsets( $set );
		
		//
		// Filter deleted offsets.
		//
		$set = $tags;
		foreach( $tags as $tag => $info )
		{
			$tmp = array_diff( $old_tags[ $tag ],
							   $info[ kTAG_OBJECT_OFFSETS ] );
			if( ! count( $tmp ) )
				unset( $set[ $key ] );
			else
				$set[ $tag ][ kTAG_OBJECT_OFFSETS ] = $tmp;
		}
		
		//
		// Delete old offsets.
		//
		if( count( $set ) )
			$mods->postDeleteTagOffsets( $set );
		
		return $theCollection->replaceOffsets( $id, $mods->getArrayCopy() );		// ==>
	
	} // modifyObjectAdd.

	 
	/*===================================================================================
	 *	modifyObjectDel																	*
	 *==================================================================================*/

	/**
	 * Delete offsets from object
	 *
	 * This method can be used to delete the provided properties from the current object,
	 * the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The object's persistent store.
	 *	<li><b>$theOffsets</b>: The offsets to be added or replaced, this parameter should
	 *		be an array of tag sequence numbers referencing the offsets to be removed from
	 *		the root of the object.
	 * </ul>
	 *
	 * In this method we perform the following steps:
	 *
	 * <ul>
	 *	<li>We update the {@link kTAG_OBJECT_TAGS} property.
	 *	<li>We update the {@link kTAG_OBJECT_OFFSETS} property.
	 *	<li>We update the {@link kTAG_OBJECT_REFERENCES} property.
	 *	<li>We update the metadata of removed tags, offsets and references.
	 *	<li>We update the metadata of removed offsets of common collections.
	 *	<li>We remove the offsets from the stored object.
	 * </ul>
	 *
	 * If any of the above steps fail the method must raise an exception.
	 *
	 * The method will return the number of elements affected by the operation (1 or 0).
	 *
	 * @param CollectionObject		$theCollection		Object collection.
	 * @param array					$theOffsets			Modification properties.
	 *
	 * @access public
	 * return integer				Number of objects affected.
	 *
	 * @throws Exception
	 */
	public function modifyObjectDel( CollectionObject $theCollection, $theOffsets )
	{
		//
		// Init local storage.
		//
		$id = $this->offsetGet( kTAG_NID );
		
		//
		// Collect tags, offsets and references.
		//
		$old_tags = $old_refs = Array();
		$this->preCommitTraverse( $old_tags, $old_refs, FALSE );
		
		//
		// Remove properties.
		//
		foreach( $theOffsets as $offset )
			$this->offsetUnset( $offset );
		
		//
		// Collect tags, offsets and references.
		//
		$cur_tags = $cur_refs = Array();
		$this->preCommitTraverse( $cur_tags, $cur_refs, FALSE );
		
		//
		// Replace tags list.
		//
		$this->offsetSet( kTAG_OBJECT_TAGS, array_keys( $cur_tags ) );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_TAGS => array_keys( $cur_tags ) ) );
	
		//
		// Replace tag offsets.
		//
		$tmp = Array();
		foreach( $cur_tags as $key => $value )
			$tmp[ $key ] = $value[ kTAG_OBJECT_OFFSETS ];
		$this->offsetSet( kTAG_OBJECT_OFFSETS, $tmp );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_OFFSETS => $tmp ) );
		
		//
		// Replace object references.
		//
		$this->offsetSet( kTAG_OBJECT_REFERENCES, $cur_refs );
		$theCollection->replaceOffsets(
			$id,
			array( kTAG_OBJECT_REFERENCES => $cur_refs ) );
		
		//
		// Select removed tags and references.
		//
		$tags = array_diff_key( $old_tags, $cur_tags );
		$refs = $this->compareObjectOffsets( $old_refs, $cur_refs, TRUE );
			
		//
		// Update metadata.
		//
		$this->postDelete( $tags, $refs );
		
		//
		// Select common tags.
		//
		$tags = array_intersect_key( $cur_tags, $old_tags );
		
		//
		// Filter deleted offsets.
		//
		$set = $tags;
		foreach( $tags as $tag => $info )
		{
			$tmp = array_diff( $old_tags[ $tag ],
							   $info[ kTAG_OBJECT_OFFSETS ] );
			if( ! count( $tmp ) )
				unset( $set[ $key ] );
			else
				$set[ $tag ][ kTAG_OBJECT_OFFSETS ] = $tmp;
		}
		
		//
		// Delete old offsets.
		//
		if( count( $set ) )
			$this->postDeleteTagOffsets( $set );
		
		return $theCollection->deleteOffsets( $id, $theOffsets );					// ==>
	
	} // modifyObjectDel.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-COMMIT INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preCommit																		*
	 *==================================================================================*/

	/**
	 * Prepare object for commit
	 *
	 * This method should prepare the object for being committed, the method will perform
	 * the following steps:
	 *
	 * <ul>
	 *	<li><tt>{@link preCommitPrepare()}</tt>: This method will check if the object is
	 *		initialised and will prepare the parameters used by the object traversal method.
	 *	<li><tt>{@link preCommitTraverse()}</tt>: This method will traverse the object's
	 *		structure validating and casting data properties, and collecting tags and
	 *		references that will be used by the post-commit workflow.
	 *	<li><tt>{@link preCommitFinalise()}</tt>: This method will load the object's
	 *		{@link kTAG_OBJECT_TAGS}, {@link kTAG_OBJECT_OFFSETS} and
	 *		{@link kTAG_OBJECT_REFERENCES} properties andeventually compute the object's
	 *		identifiers.
	 *	<li><tt>{@link isReady()}</tt>: The final step of the pre-commit phase is to test
	 *		whether the object is ready to be committed.
	 * </ul>
	 *
	 * The method accepts two array reference parameters which will be initialised in this
	 * method, these will be filled by the {@link preCommitTraverse()} method and are
	 * structured as follows:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This array is the set of all tags referenced by the object's
	 *		offsets, except for the offsets corresponding to the {@link InternalOffsets()}:
	 *		<li><tt>key</tt>: The tag sequence number.
	 *		<li><tt>value</tt>: An array collecting all the relevant information about that
	 *			tag, each element of the array is structured as follows:
	 *		 <ul>
	 *			<li><tt>{@link kTAG_DATA_TYPE}</tt>: The item indexed by this key will
	 *				contain the corresponding tag object offset.
	 *			<li><tt>{@link kTAG_DATA_KIND}</tt>: The item indexed by this key will
	 *				contain the corresponding tag object offset.
	 *			<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: The item indexed by this key will
	 *				collect the list of all the offsets in which the current tag appears as
	 *				a leaf offset. In practice, this element collects all the possible
	 *				offsets at any depth level in which the current tag holds a value. This
	 *				also means that it will only be filled if the current tag is not a
	 *				structural element.
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theRefs</b>: This parameter collects all the object references featured in
	 *		the current object. It is an array of elements structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The referenced object's collection name.
	 *		<li><tt>value</tt>: The set of all native identifiers representing the
	 *			referenced objects.
	 *	 </ul>
	 * </ul>
	 *
	 * These parameter will be initialised in this method.
	 *
	 * This method is declared final, derived classes should overload the called methods.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses preCommitPrepare()
	 * @uses preCommitTraverse()
	 * @uses preCommitFinalise()
	 * @uses isReady()
	 */
	protected final function preCommit( &$theTags, &$theRefs )
	{
		//
		// Init parameters.
		//
		$theTags = $theRefs = Array();
		
		//
		// Prepare object.
		//
		$this->preCommitPrepare( $theTags, $theRefs );
	
		//
		// Traverse object.
		//
		$this->preCommitTraverse( $theTags, $theRefs );
		
		//
		// Finalise object.
		//
		$this->preCommitFinalise( $theTags, $theRefs );
	
		//
		// Check if object is ready.
		//
		if( ! $this->isReady() )
			throw new \Exception(
				"Cannot commit object: "
			   ."the object is not ready." );									// !@! ==>
	
	} // preCommit.

	 
	/*===================================================================================
	 *	preCommitPrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before commit
	 *
	 * This method is called by the {@link preCommit()} method, it should perform
	 * preliminary checks to ensure that the object is fit to be committed.
	 *
	 * In this class we check if the object is {@link isInited()}, in derived classes you
	 * can overload this method to make sure the object is {@link isInited()} and then call
	 * the inherited method.
	 *
	 * The method features the traversal parameters, derived classes can take the
	 * opportunity to modify these parameters. See the {@link preCommit()} method for a
	 * description of those parameters.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses isInited()
	 */
	protected function preCommitPrepare( &$theTags, &$theRefs )
	{
		//
		// Check if initialised.
		//
		if( ! $this->isInited() )
			throw new \Exception(
				"Unable to commit: "
			   ."the object is not initialised." );								// !@! ==>
		
	} // preCommitPrepare.

		
	/*===================================================================================
	 *	preCommitTraverse																*
	 *==================================================================================*/

	/**
	 * Traverse object before commit
	 *
	 * This method is called by the {@link preCommit()} method, it will apply the
	 * {@link traverseValidate()} method to the object's persistent data iterator, the
	 * aforementioned method will be called for each offset of the object and will be
	 * recursed for each sub-structure or list of the object.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * Before traversing the object, the method will delete all private offsets in the
	 * object, this is to ensure these properties are exclusively managed by these methods.
	 *
	 * The last parameter of this method is a flag which if set will activate the
	 * validation, reference check and casting of the offsets.
	 *
	 * The method is declared final, so derived classes should only overload the called
	 * methods, if you need to modify the traversal parameters do so overloading the
	 * {@link preCommitPrepare()} method.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 *
	 * @uses traverseValidate()
	 */
	protected final function preCommitTraverse( &$theTags, &$theRefs, $doValidate = TRUE )
	{
		//
		// Remove private offsets.
		//
		foreach( static::PrivateOffsets() as $offset )
			$this->offsetUnset( $offset );
	
		//
		// Traverse object.
		//
		$path = Array();
		$iterator = $this->getIterator();
		iterator_apply( $iterator,
						array( $this, 'traverseValidate' ),
						array( $iterator, & $path, & $theTags, & $theRefs, $doValidate ) );
	
	} // preCommitTraverse.

	 
	/*===================================================================================
	 *	preCommitFinalise																*
	 *==================================================================================*/

	/**
	 * Finalise object before commit
	 *
	 * This method is called by the {@link preCommit()} method, it will be executed before
	 * checking if the object is ready, {@link isReady()}, its duty is to make the last
	 * preparations before the object is to be committed.
	 *
	 * The method calls two methods:
	 *
	 * <ul>
	 *	<li><tt>{@link preCommitObjectTags()}</tt>: This method is responsible for loading
	 *		the {@link kTAG_OBJECT_TAGS} and {@link kTAG_OBJECT_OFFSETS} object properties.
	 *	<li><tt>{@link preCommitObjectReferences()}</tt>: This method is responsible for
	 *		loading the {@link kTAG_OBJECT_REFERENCES} object property.
	 *	<li><tt>{@link preCommitObjectIdentifiers()}</tt>: This method is responsible of
	 *		computing the object's identifiers.
	 * </ul>
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * Derived classes should overload the called methods, if they need to either change
	 * the default behaviour or prevent loading tags or updating reference counts; if other
	 * actions should be performed, this method can be overloaded.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses preCommitObjectTags()
	 * @uses preCommitObjectReferences()
	 * @uses preCommitObjectIdentifiers()
	 */
	protected function preCommitFinalise( &$theTags, &$theRefs )
	{
		//
		// Load object tags.
		//
		$this->preCommitObjectTags( $theTags );
	
		//
		// Load object references.
		//
		$this->preCommitObjectReferences( $theRefs );
	
		//
		// Compute object identifiers.
		//
		$this->preCommitObjectIdentifiers();
	
	} // preCommitFinalise.

	 
	/*===================================================================================
	 *	preCommitObjectTags																*
	 *==================================================================================*/

	/**
	 * Load object tags
	 *
	 * This method is called by the {@link preCommitFinalise()} method, it will collect the
	 * offset tags set from the tags parameter and populate the {@link kTAG_OBJECT_TAGS}
	 * and the {@link kTAG_OBJECT_OFFSETS} offsets of the current object.
	 *
	 * Only tags which feature the {@link kTAG_OBJECT_OFFSETS} element will be considered.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * @param reference				$theTags			Object tags.
	 *
	 * @access protected
	 *
	 * @see kTAG_OBJECT_TAGS kTAG_OBJECT_OFFSETS
	 */
	protected function preCommitObjectTags( &$theTags )
	{
		//
		// Init local storage.
		//
		$tags = $offsets = Array();
		
		//
		// Iterate tags.
		//
		foreach( $theTags as $tag => $info )
		{
			//
			// Select leaf tags.
			//
			if( array_key_exists( kTAG_OBJECT_OFFSETS, $info ) )
			{
				//
				// Load tags list.
				//
				$tags[] = (int) $tag;
				
				//
				// Load offsets list.
				//
				$offsets[ $tag ] = $info[ kTAG_OBJECT_OFFSETS ];
			
			}
		
		} // Iterating tags.
		
		//
		// Set offsets.
		//
		if( count( $tags ) )
		{
			$this->offsetSet( kTAG_OBJECT_TAGS, $tags );
			$this->offsetSet( kTAG_OBJECT_OFFSETS, $offsets );
		}
	
	} // preCommitObjectTags.

	 
	/*===================================================================================
	 *	preCommitObjectReferences														*
	 *==================================================================================*/

	/**
	 * Load object tags
	 *
	 * This method is called by the {@link preCommitFinalise()} method, it will collect the
	 * object references set from the references parameter and populate the
	 * {@link kTAG_OBJECT_REFERENCES offset of the current object.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * In this class we simply copy the parameter to the offset.
	 *
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @see kTAG_OBJECT_REFERENCES
	 */
	protected function preCommitObjectReferences( &$theRefs )
	{
		//
		// Check references.
		//
		if( count( $theRefs ) )
			$this->offsetSet( kTAG_OBJECT_REFERENCES, $theRefs );
	
	} // preCommitObjectReferences.

	 
	/*===================================================================================
	 *	preCommitObjectIdentifiers														*
	 *==================================================================================*/

	/**
	 * Load object identifiers
	 *
	 * This method is called by the {@link preCommitFinalise()} method, its duty is to
	 * compute the object's identifiers if needed.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * In this class we do not handle identifiers, derived classes should overload this
	 * method if they need to compute identifiers.
	 *
	 * @access protected
	 */
	protected function preCommitObjectIdentifiers()										   {}

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-COMMIT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postCommit																		*
	 *==================================================================================*/

	/**
	 * Handle object after commit
	 *
	 * This method is called immediately after the object was committed, its duty is to
	 * update references and tags, the method will perform the following steps:
	 *
	 * <ul>
	 *	<li><tt>{@link postCommitReferences()}</tt>: This method will process the references
	 *		collected during the pre-commit object traversal phase.
	 *	<li><tt>{@link postCommitTags()}</tt>: This method will process the tags collected
	 *		during the pre-commit object traversal phase.
	 * </ul>
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * If derived classes need to customise the references and tags processing, they should
	 * overload the called methods; if they need additional processing they can overload
	 * this method.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses postCommitReferences()
	 * @uses postCommitTags()
	 */
	protected function postCommit( &$theTags, &$theRefs )
	{
		//
		// Update object tags.
		//
		$this->postCommitTags( $theTags );
	
		//
		// Update object references.
		//
		$this->postCommitReferences( $theRefs );
	
	} // postCommit.

	 
	/*===================================================================================
	 *	postCommitTags																	*
	 *==================================================================================*/

	/**
	 * Handle object tags after commit
	 *
	 * This method is called by the {@link postCommit()} method, it will provide the tags
	 * collected during the pre-commit phase and feed them to the following methods:
	 *
	 * <ul>
	 *	<li><tt>{@link postCommitTagRefCount()}</tt>: It updates the reference counts of the
	 *		provided tags.
	 *	<li><tt>{@link postCommitTagOffsets()}</tt>: It updates the offsets of the provided
	 *		tags.
	 * </ul>
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * If you need to overload this method <em>be aware that you should mirror the changes
	 * in the {@link postDeleteTags()} method</em>, since deleting affects references in
	 * the opposite way commit does.
	 *
	 * @param reference				$theTags			Object tags.
	 *
	 * @access protected
	 *
	 * @uses postCommitTagRefCount()
	 * @uses postCommitTagOffsets()
	 */
	protected function postCommitTags( &$theTags )
	{
		//
		// Update tags reference count.
		//
		$this->postCommitTagRefCount( $theTags );
	
		//
		// Update tag offsets.
		//
		$this->postCommitTagOffsets( $theTags );
	
	} // postCommitTags.

	 
	/*===================================================================================
	 *	postCommitTagRefCount															*
	 *==================================================================================*/

	/**
	 * Update tag reference counts
	 *
	 * This method is called by the {@link postCommitTags()} method, it will update the
	 * reference counts of all tags holding data in the current object.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * If you need to overload this method <em>make sure you mirror the changes in the
	 * {@link postDeleteTagRefCount()} method, in order to maintain referential integrity.
	 *
	 * @param reference				$theTags			Object tags.
	 *
	 * @access protected
	 *
	 * @uses updateReferenceCount()
	 */
	protected function postCommitTagRefCount( &$theTags )
	{
		//
		// Update tags reference count.
		//
		if( count( $theTags ) )
			$this->updateReferenceCount( Tag::kSEQ_NAME,			// Collection.
										 array_keys( $theTags ),	// Identifiers.
										 kTAG_ID_SEQUENCE,			// Identifiers offset.
										 1 );						// Reference count.
		
	} // postCommitTagRefCount.

	 
	/*===================================================================================
	 *	postCommitTagOffsets															*
	 *==================================================================================*/

	/**
	 * Update tag offsets
	 *
	 * This method is called by the {@link postCommitTags()} method, it will update the
	 * offsets list of the tag objects referenced by the leaf offsets of the current object.
	 *
	 * The method will add the offsets contained in the provided parameter to the property
	 * related to the current object. If the current object has no associated offsets set
	 * tag, an exception will be raised.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * If you need to overload this method <em>make sure you mirror the changes in the
	 * {@link postDeleteTagOffsets()} method, in order to maintain referential integrity.
	 *
	 * This method expects the {@link dictionary()} set.
	 *
	 * @param reference				$theTags			Object tags.
	 *
	 * @access protected
	 *
	 * @uses ResolveOffsetsTag()
	 * @uses updateOffsetsSet()
	 */
	protected function postCommitTagOffsets( &$theTags )
	{
		//
		// Check tags.
		//
		if( count( $theTags ) )
		{
			//
			// Resolve collection.
			//
			$collection
				= Tag::ResolveCollection(
					Tag::ResolveDatabase( $this->mDictionary ) );
		
			//
			// Resolve set property.
			//
			$offset = static::ResolveOffsetsTag( static::kSEQ_NAME );
		
			//
			// Get tag identifiers.
			//
			$tags = array_keys( $theTags );
		
			//
			// Iterate tag elements.
			//
			foreach( $tags as $tag )
				$this->updateOffsetsSet(
					$collection, $tag, $offset, $theTags[ $tag ][ kTAG_OBJECT_OFFSETS ] );
		
		} // Has tags.
	
	} // postCommitTagOffsets.

	 
	/*===================================================================================
	 *	postCommitReferences															*
	 *==================================================================================*/

	/**
	 * Update object references
	 *
	 * This method is called by the {@link postCommit()} method, it will iterate all
	 * references collected during the pre-commit phase and feed them to the
	 * {@link updateReferenceCount()} method that will update the referenced object's
	 * reference count.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * By default all referenced objects are tracked, in derived classes you may overload
	 * this method <em>provided you mirror the changes in the {@link postDeleteReferences()}
	 * method</em>, which performs the same function for deleted objects.
	 *
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses updateReferenceCount()
	 */
	protected function postCommitReferences( &$theRefs )
	{
		//
		// Iterate by collection.
		//
		if( count( $theRefs ) )
			foreach( $theRefs as $collection => $references )
				$this->updateReferenceCount( $collection, $references, kTAG_NID, 1 );
	
	} // postCommitReferences.

		

/*=======================================================================================
 *																						*
 *								PROTECTED PRE-DELETE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	preDelete																		*
	 *==================================================================================*/

	/**
	 * Prepare object for delete
	 *
	 * This method should prepare the object for being deleted, the method will perform
	 * the following steps:
	 *
	 * <ul>
	 *	<li><tt>{@link preDeletePrepare()}</tt>: This method should check whether the object
	 *		can indeed be deleted, it should verify that the object is not referenced.
	 *	<li><tt>{@link preDeleteCollect()}</tt>: This method should collect all tags and
	 *		references which will be used to update reference counts.
	 *	<li><tt>{@link preDeleteFinalise()}</tt>: This method should finalise the pre-delete
	 *		phase ensuring the object is ready to be deleted.
	 * </ul>
	 *
	 * The method accepts two reference parameters which will be filled by the
	 * {@link preDeleteCollect()} method and will be passed to the
	 * {@link preDeleteFinalise()} method:
	 *
	 * <ul>
	 *	<li><b>$theTags</b>: This array is the set of all tags referenced by the object's
	 *		offsets, except for the offsets corresponding to the {@link InternalOffsets()}:
	 *		<li><tt>key</tt>: The tag sequence number.
	 *		<li><tt>value</tt>: An array collecting all the relevant information about that
	 *			tag, each element of the array is structured as follows:
	 *		 <ul>
	 *			<li><tt>{@link kTAG_DATA_TYPE}</tt>: The item indexed by this key will
	 *				contain the corresponding tag object offset.
	 *			<li><tt>{@link kTAG_DATA_KIND}</tt>: The item indexed by this key will
	 *				contain the corresponding tag object offset.
	 *			<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: The item indexed by this key will
	 *				collect the list of all the offsets in which the current tag appears as
	 *				a leaf offset. In practice, this element collects all the possible
	 *				offsets at any depth level in which the current tag holds a value. This
	 *				also means that it will only be filled if the current tag is not a
	 *				structural element.
	 *		 </ul>
	 *	 </ul>
	 *		This parameter will be filled using the object's {@link kTAG_OBJECT_OFFSETS}
	 *		property.
	 *	<li><b>$theRefs</b>: This parameter collects all the object references featured in
	 *		the current object. It is an array of elements structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The referenced object's collection name.
	 *		<li><tt>value</tt>: The set of all native identifiers representing the
	 *			referenced objects.
	 *	 </ul>
	 *		This parameter will be filled using the object's {@link kTAG_OBJECT_REFERENCES}
	 *		property.
	 * </ul>
	 *
	 * These parameter will be initialised by this method.
	 *
	 * This method is declared final, derived classes should overload the called methods.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> the object can be deleted.
	 *
	 * @uses preDeletePrepare()
	 * @uses preDeleteCollect()
	 * @uses preDeleteFinalise()
	 */
	protected final function preDelete( &$theTags, &$theRefs )
	{
		//
		// Init parameters.
		//
		$theTags = $theRefs = Array();
		
		//
		// Prepare object.
		//
		if( ! $this->preDeletePrepare( $theTags, $theRefs ) )
			return FALSE;															// ==>
	
		//
		// Collect references.
		//
		if( ! $this->preDeleteCollect( $theTags, $theRefs ) )
			return FALSE;															// ==>
		
		//
		// Finalise object.
		//
		if( ! $this->preDeleteFinalise( $theTags, $theRefs ) )
			return FALSE;															// ==>
		
		return TRUE;																// ==>
	
	} // preDelete.

	 
	/*===================================================================================
	 *	preDeletePrepare																*
	 *==================================================================================*/

	/**
	 * Prepare object before delete
	 *
	 * This method should perform global preliminary checks to ensure the object is not
	 * referenced and is fit to be deleted.
	 *
	 * The method should return a boolean value indicating whether the object can be deleted
	 * or not.
	 *
	 * In this class we check whether the object is referenced, in that case the method will
	 * return <tt>FALSE</tt>.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> the object can be deleted.
	 *
	 * @see kTAG_UNIT_COUNT kTAG_ENTITY_COUNT
	 * @see kTAG_TAG_COUNT kTAG_TERM_COUNT kTAG_NODE_COUNT kTAG_EDGE_COUNT
	 *
	 * @uses getOffsetTypes()
	 */
	protected function preDeletePrepare( &$theTags, &$theRefs )
	{	
		//
		// Check reference counts.
		//
		if( $this->offsetGet( kTAG_UNIT_COUNT )
		 || $this->offsetGet( kTAG_TAG_COUNT )
		 || $this->offsetGet( kTAG_TERM_COUNT )
		 || $this->offsetGet( kTAG_NODE_COUNT )
		 || $this->offsetGet( kTAG_EDGE_COUNT )
		 || $this->offsetGet( kTAG_ENTITY_COUNT ) )
			return FALSE;															// ==>
		
		return TRUE;																// ==>
	
	} // preDeletePrepare.

		
	/*===================================================================================
	 *	preDeleteCollect																*
	 *==================================================================================*/

	/**
	 * Collect object references
	 *
	 * This method should fill the provided parameters with the necessary information;
	 * please consult the {@link preDelete()} method documentation for a description of
	 * these parameters.
	 *
	 * The method expects all parameters, to have been initialised.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> the object can be deleted.
	 *
	 * @see kTAG_OBJECT_OFFSETS kTAG_OBJECT_REFERENCES
	 */
	protected function preDeleteCollect( &$theTags, &$theRefs )
	{
		//
		// Handle object tags.
		//
		if( $this->offsetExists( kTAG_OBJECT_OFFSETS ) )
		{
			//
			// Load object tags.
			//
			foreach( $this->offsetGet( kTAG_OBJECT_OFFSETS ) as $tag => $offsets )
			{
				//
				// Init tag element.
				//
				$theTags[ $tag ] = array( kTAG_OBJECT_OFFSETS => $offsets );
			
				//
				// Collect data type and kind.
				//
				$ref = & $theTags[ $tag ];
				$this->getOffsetTypes( $tag, $ref[ kTAG_DATA_TYPE ],
											 $ref[ kTAG_DATA_KIND ] );
			
			} // Iterating object tags.
		
		} // Has object offsets.
		
		else
			$theTags = Array();
		
		//
		// Load object references.
		//
		if( $this->offsetExists( kTAG_OBJECT_REFERENCES ) )
			$theRefs = $this->offsetGet( kTAG_OBJECT_REFERENCES );
		
		else
			$theRefs = Array();
		
		return TRUE;																// ==>
	
	} // preDeleteCollect.

	 
	/*===================================================================================
	 *	preDeleteFinalise																*
	 *==================================================================================*/

	/**
	 * Finalise object before delete
	 *
	 * This method will be called before the object is going to be deleted, it is the last
	 * chance of performing actions before the object gets removed from the persistent
	 * store.
	 *
	 * In this class we do nothing.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> the object can be deleted.
	 */
	protected function preDeleteFinalise( &$theTags, &$theRefs )		{	return TRUE;	}

		

/*=======================================================================================
 *																						*
 *							PROTECTED POST-DELETE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	postDelete																		*
	 *==================================================================================*/

	/**
	 * Handle object after delete
	 *
	 * This method is called immediately after the object is deleted, its duty is to handle
	 * the object after it was removed from its persistent store and to handle related
	 * objects.
	 *
	 * In this class we do the following:
	 *
	 * <ul>
	 *	<li><tt>{@link postDeleteTags()}</tt>: This method should handle the tags used by
	 *		the current object, in this class we remove orphan offset paths from the
	 *		relative tag objects.
	 *	<li><tt>{@link postDeleteReferences()}</tt>: This method should handle the current
	 *		object references, in this class we update the reference counts of all objects
	 *		referenced by the current object.
	 * </ul>
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses postDeleteReferences()
	 * @uses postDeleteTags()
	 */
	protected function postDelete( &$theTags, &$theRefs )
	{
		//
		// Update reference counts.
		//
		$this->postDeleteReferences( $theRefs );
	
		//
		// Update object tags.
		//
		$this->postDeleteTags( $theTags );
	
	} // postDelete.

	 
	/*===================================================================================
	 *	postDeleteReferences															*
	 *==================================================================================*/

	/**
	 * Update object references
	 *
	 * This method is called by the {@link postDelete()} method, it will iterate all
	 * references collected during the pre-delete phase and feed them to the
	 * {@link updateReferenceCount()} method that will update the referenced object's
	 * reference count.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preDelete()} method documentation.
	 *
	 * By default all referenced objects are tracked, in derived classes you may overload
	 * this method <em>provided you mirror the changes in the {@link postCommitReferences()}
	 * method</em>, which performs the same function for deleted objects.
	 *
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses updateReferenceCount()
	 */
	protected function postDeleteReferences( &$theRefs )
	{
		//
		// Iterate by collection.
		//
		foreach( $theRefs as $collection => $references )
			$this->updateReferenceCount( $collection, $references, kTAG_NID, -1 );
	
	} // postDeleteReferences.

	 
	/*===================================================================================
	 *	postDeleteTags																	*
	 *==================================================================================*/

	/**
	 * Handle object tags after commit
	 *
	 * This method is called by the {@link postDelete()} method, it will provide the tags
	 * collected during the pre-delete phase and feed them to the following methods:
	 *
	 * <ul>
	 *	<li><tt>{@link postDeleteTagRefCount()}</tt>: It updates the reference counts of the
	 *		provided tags.
	 *	<li><tt>{@link postDeleteTagOffsets()}</tt>: It updates the offsets of the provided
	 *		tags.
	 * </ul>
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preDelete()} method documentation.
	 *
	 * If you need to overload this method <em>be aware that you should mirror the changes
	 * in the {@link postCommitTags()} method</em>, since committing affects references in
	 * the opposite way delete does.
	 *
	 * @param reference				$theTags			Property leaf tags.
	 *
	 * @access protected
	 *
	 * @uses postDeleteTagRefCount()
	 * @uses postDeleteTagOffsets()
	 */
	protected function postDeleteTags( &$theTags )
	{
		//
		// Update tags reference count.
		//
		$this->postDeleteTagRefCount( $theTags );
	
		//
		// Update tag offsets.
		//
		$this->postDeleteTagOffsets( $theTags );
	
	} // postDeleteTags.

	 
	/*===================================================================================
	 *	postDeleteTagRefCount															*
	 *==================================================================================*/

	/**
	 * Update tag reference counts
	 *
	 * This method is called by the {@link postDeleteTags()} method, it will update the
	 * reference counts of all tags holding data in the current object.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * If you need to overload this method <em>make sure you mirror the changes in the
	 * {@link postCommitTagRefCount()} method, in order to maintain referential integrity.
	 *
	 * @param reference				$theTags			Object tags.
	 *
	 * @access protected
	 *
	 * @uses updateReferenceCount()
	 */
	protected function postDeleteTagRefCount( &$theTags )
	{
		//
		// Update tags reference count.
		//
		if( count( $theTags ) )
			$this->updateReferenceCount( Tag::kSEQ_NAME,			// Collection.
										 array_keys( $theTags ),	// Identifiers.
										 kTAG_ID_SEQUENCE,			// Identifiers offset.
										 -1 );						// Reference count.
		
	} // postDeleteTagRefCount.

	 
	/*===================================================================================
	 *	postDeleteTagOffsets															*
	 *==================================================================================*/

	/**
	 * Update tag offsets
	 *
	 * This method is called by the {@link postDeleteTags()} method, it will update the
	 * offsets list of the tag objects referenced by the leaf offsets of the current object.
	 *
	 * This method will first select the set of offsets to be removed, then, for each of
	 * these offsets, it will check if there are other objects featuring them: if that is
	 * the case, the method will do notning, if the offset cannot be found in any other
	 * object, the method will remove it grom the relative tag's set.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * If you need to overload this method <em>make sure you mirror the changes in the
	 * {@link postCommitTagOffsets()} method, in order to maintain referential integrity.
	 *
	 * This method expects the {@link dictionary()} set.
	 *
	 * @param reference				$theTags			Object tags.
	 *
	 * @access protected
	 *
	 * @uses ResolveOffsetsTag()
	 * @uses updateOffsetsSet()
	 */
	protected function postDeleteTagOffsets( &$theTags )
	{
		//
		// Resolve set property.
		//
		$set = static::ResolveOffsetsTag( static::kSEQ_NAME );
	
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollection(
				static::ResolveDatabase( $this->mDictionary ) );
	
		//
		// Remove existing offsets from list.
		//
		$list = new \ArrayObject( $theTags );
		$iterator = $list->getIterator();
		iterator_apply( $iterator,
						array( $this, 'traverseFilterOffsets' ),
						array( $iterator, $collection ) );
	
		//
		// Remove missing offsets from tags.
		//
		if( count( $theTags ) )
		{
			$list = new \ArrayObject( $theTags );
			$iterator = $list->getIterator();
			iterator_apply( $iterator,
							array( $this, 'traverseRemoveOffsets' ),
							array( $iterator, $collection, $set ) );
		}
	
	} // postDeleteTagOffsets.

	

/*=======================================================================================
 *																						*
 *							PROTECTED OBJECT TRAVERSAL INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	traverseValidate																*
	 *==================================================================================*/

	/**
	 * Traverse structure
	 *
	 * This method's duty is to validate and normalise offsets of the current object and
	 * collect other information while traversing the structure of the current object.
	 *
	 * This method will be called for each offset at the root or sub-structure level of the
	 * current object. This means that this method will not be called for elements of a list
	 * offset.
	 *
	 * The method is passed a series of reference parameters that will be populated as this
	 * method traverses the object's structure, this data will be then used in the commit
	 * workflow to perform tasks related to referenced objects and statistical information.
	 *
	 * These parameters are:
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter contains the element currently pointed to by
	 *		the iterator. This iterator is not recursive, each time a sub-structure is
	 *		encountered, a new iterator is generated and is handed over to this method.
	 *	<li><b>$thePath</b>: This run-time parameter contains the path to the current
	 *		iterator element represented by a list of offsets, starting from the root
	 *		offset and ending with the offset at the current depth. The current iterator
	 *		element's offset is pushed at entry and popped at exit.
	 *	<li><b>$theTags</b>: This array is the set of all tags referenced by the object's
	 *		offsets, except for the offsets corresponding to the {@link InternalOffsets()}:
	 *		<li><tt>key</tt>: The tag sequence number.
	 *		<li><tt>value</tt>: An array collecting all the relevant information about that
	 *			tag, each element of the array is structured as follows:
	 *		 <ul>
	 *			<li><tt>{@link kTAG_DATA_TYPE}</tt>: The item indexed by this key will
	 *				contain the corresponding tag object offset.
	 *			<li><tt>{@link kTAG_DATA_KIND}</tt>: The item indexed by this key will
	 *				contain the corresponding tag object offset.
	 *			<li><tt>{@link kTAG_OBJECT_OFFSETS}</tt>: The item indexed by this key will
	 *				collect the list of all the offsets in which the current tag appears as
	 *				a leaf offset. In practice, this element collects all the possible
	 *				offsets at any depth level in which the current tag holds a value. This
	 *				also means that it will only be filled if the current tag is not a
	 *				structural element.
	 *		 </ul>
	 *	 </ul>
	 *	<li><b>$theRefs</b>: This parameter collects all the object references featured in
	 *		the current object. It is an array of elements structured as follows:
	 *	 <ul>
	 *		<li><tt>key</tt>: The referenced object's collection name.
	 *		<li><tt>value</tt>: The set of all native identifiers representing the
	 *			referenced objects.
	 *	 </ul>
	 *	<li><b>$doValidate</b>: If this flag is set, the method will activate all the
	 *		validation procedures, if not set, the procedure will only collect information.
	 * </ul>
	 *
	 * The method will perform the following steps:
	 *
	 * <ul>
	 *	<li><em>Push the current offset to the path</em>: The current offset will be
	 *		appended to the path parameter.
	 *	<li><em>Collect offset tag information</em>: The {@link collectOffsetInformation()}
	 *		method will determine the types and kinds of the current offset value and update
	 *		the tags parameter. If the current offset is an internal offset, all steps
	 *		except the last one will be skipped.
	 *	<li><em>Verify offset structure</em>: The {@link verifyStructureOffset()} method
	 *		will check if the current element value has the correct structure.
	 *	<li><em>Verify and cast value</em>: If the current offset type is not a structure,
	 *		{@link kTYPE_STRUCT}, the {@link traverseValidateValue()} method will be used to verify
	 *		the offset value and cast it to the correct data type.
	 *	<li><em>Recurse structures</em>: If the current element is a structure, its elements
	 *		will be iterated and handed to this method. Structure lists will recursively be
	 *		iterated.
	 *	<li><em>Scan lists</em>: If the current element is a list of scalar elements, each
	 *		element of the list will be handed to the {@link traverseValidateValue()} method which
	 *		will take care of validating and casting the value.
	 *	<li><em>Pop offset from path</em>: The current offset will be popped from the path
	 *		parameter.
	 * </ul>
	 *
	 * This method is final, derived classes should only need to overload the methods called
	 * by this one.
	 *
	 * This method is used by the PHP {@link iterator_apply()} method, which means that it
	 * should return <tt>TRUE</tt> to continue the object traversal, or <tt>FALSE</tt> to
	 * stop it: it will return <tt>TRUE</tt> by default.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$thePath			Offsets path.
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theRefs			Object references.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt>, or <tt>FALSE</tt> to stop the traversal.
	 *
	 * @uses collectOffsetInformation()
	 * @uses verifyStructureOffset()
	 * @uses traverseValidateValue()
	 */
	final protected function traverseValidate( \Iterator $theIterator, &$thePath,
																	   &$theTags,
																	   &$theRefs,
																	    $doValidate = TRUE )
	{
		//
		// Push to path.
		//
		$thePath[] = $theIterator->key();
		
		//
		// Collect offset information.
		//
		$offset
			= $this->collectOffsetInformation(
				$theIterator, $thePath, $theTags, $type, $kind );
		
		//
		// Skip internal offsets.
		//
		if( $offset !== NULL )
		{
			//
			// Handle scalar offset.
			//
			if( (! in_array( kTYPE_LIST, $kind ))
			 && (! in_array( kTYPE_STRUCT, $type )) )
				$this->traverseValidateValue(
					$theIterator, $theRefs, $type, $kind, $offset, $doValidate );
		
			//
			// Handle structure and list offsets.
			//
			else
			{
				//
				// Verify structure offset.
				//
				$this->verifyStructureOffset( $theIterator, $type, $kind, $offset );
			
				//
				// Save list or structure.
				//
				$list = new \ArrayObject( $theIterator->current() );
		
				//
				// Handle structure.
				//
				if( in_array( kTYPE_STRUCT, $type ) )
				{
					//
					// Handle structure lists.
					//
					if( in_array( kTYPE_LIST, $kind ) )
					{
						//
						// Iterate list.
						//
						foreach( $list as $idx => $struct )
						{
							//
							// Traverse structure.
							//
							$struct = new \ArrayObject( $struct );
							$iterator = $struct->getIterator();
							iterator_apply( $iterator,
											array( $this, 'traverseValidate' ),
											array( $iterator, & $thePath,
															  & $theTags,
															  & $theRefs,
																$doValidate ) );
		
							//
							// Update structure.
							//
							if( $struct->count() )
								$list[ $idx ] = $struct->getArrayCopy();
				
						} // Iterating list.
			
					} // List of structures.
			
					//
					// Handle scalar structure.
					//
					else
					{
						//
						// Traverse structure.
						//
						$iterator = $list->getIterator();
						iterator_apply( $iterator,
										array( $this, 'traverseValidate' ),
										array( $iterator, & $thePath,
														  & $theTags,
														  & $theRefs,
															$doValidate ) );
			
					} // Scalar structure.
		
				} // Structure.
			
				//
				// Handle list of scalars.
				//
				else
				{
					//
					// Iterate scalar list.
					//
					$iterator = $list->getIterator();
					iterator_apply( $iterator,
									array( $this, 'traverseValidateValue' ),
									array( $iterator, & $theRefs,
													  & $type,
													  & $kind,
													  & $offset,
														$doValidate ) );
			
				} // List of scalars.

				//
				// Update current iterator value.
				//
				$theIterator->offsetSet( $theIterator->key(), $list->getArrayCopy() );
		
			} // Structured offset.
		
		} // Not an internal offset.
		
		//
		// Pop from path.
		//
		array_pop( $thePath );
		
		return TRUE;																// ==>
	
	} // traverseValidate.

	 
	/*===================================================================================
	 *	traverseValidateValue															*
	 *==================================================================================*/

	/**
	 * Traverse value
	 *
	 * This method will be called by iterators that traverse list offset values, or by
	 * methods which are traversing a scalar offset value.
	 *
	 * The main duties of this method are:
	 *
	 * <ul>
	 *	<li><em>Validate the offset value</em>: The {@link verifyOffsetValue()} method will check
	 *		whether the current offset's value is correct.
	 *	<li><em>Validate references</em>: The {@link verifyOffsetReference()} method will
	 *		validate object reference values:
	 *	 <ul>
	 *		<li>If the reference is provided as an uncommitted object, the method will
	 *			commit the object and replace it with its native identifier.
	 *		<li>If the reference is provided as a committed object, the method will replace
	 *			it with its native identifier. <em>We assume here that a committed object
	 *			exists in its collection</em>.
	 *		<li>If the reference is provided as an object reference, the method will check
	 *			whether the reference is correct.
	 *		<li>Once the reference was validated, the method will add the object reference
	 *			to the provided references parameter updating the reference count.
	 *	 </ul>
	 *		<em>Note that an offset having an object reference as its data type is assumed
	 *		to have only that data type</em>.
	 *	<li><em>Cast the offset value</em>: The {@link castOffsetValue()} method will cast the
	 *		offset value to the data type of the tag corresponding to the leaf node of the
	 *		offsets path.
	 * </ul>
	 *
	 * The above methods will check whether the current offset has <em>a single data
	 * type</em>: only in that case will they operate on the value.
	 *
	 * This method is used by the PHP {@link iterator_apply()} method, which means that it
	 * should return <tt>TRUE</tt> to continue the object traversal, or <tt>FALSE</tt> to
	 * stop it: it will return <tt>TRUE</tt> by default.
	 *
	 * This method is final, derived classes should not overload this method, but rather the
	 * methods it calls.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theRefs			Object references.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset string.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> continues the traversal.
	 *
	 * @uses verifyOffsetValue()
	 * @uses GetReferenceTypes()
	 * @uses verifyOffsetReference()
	 * @uses castOffsetValue()
	 */
	final protected function traverseValidateValue( \Iterator $theIterator, &$theRefs,
																			&$theType,
																			&$theKind,
																			&$theOffset,
															  $doValidate = TRUE )
	{
		//
		// Verify value.
		//
		if( $doValidate )
			$this->verifyOffsetValue( $theIterator, $theType, $theKind, $theOffset );
		
		//
		// Verify reference.
		//
		if( count( array_intersect( $theType, static::GetReferenceTypes() ) ) )
			$this->verifyOffsetReference(
				$theIterator, $theRefs, $theType, $theKind, $theOffset, $doValidate );
		
		//
		// Cast value.
		//
		if( $doValidate )
			$this->castOffsetValue(
				$theIterator, $theRefs, $theType, $theKind, $theOffset );
		
		return TRUE;																// ==>
	
	} // traverseValidateValue.

	 
	/*===================================================================================
	 *	traverseFilterOffsets															*
	 *==================================================================================*/

	/**
	 * Remove offsets from tags list
	 *
	 * This method is called for each element of the tags list parameter described in the
	 * {@link preDelete()} method, its duty is to check whether objects stored in the
	 * current object's collection still have the offsets of the current object, if that
	 * is not the case, these offsets will be remoived from the current iterator element:
	 * after this process the iterated list will only feature the offsets to be removed from
	 * the relative tag objects.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter points to the current element of the tags
	 *		list.
	 *	<li><b>$theCollection</b>: The collection of the current object.
	 * </ul>
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param CollectionObject		$theCollection		Current object collection.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> continues the traversal.
	 */
	protected function traverseFilterOffsets( \Iterator		   $theIterator,
											  CollectionObject $theCollection )
	{
		//
		// Init local storage.
		//
		$tag = (int) $theIterator->key();
		$info = $theIterator->current();
		$ref = & $info[ kTAG_OBJECT_OFFSETS ];
		$base_criteria = array( kTAG_ID_SEQUENCE => $tag );

		//
		// Iterate offsets.
		//
		foreach( $ref as $key => $offset )
		{
			//
			// Check offset.
			//
			$criteria = $base_criteria;
			$criteria[ kTAG_OBJECT_OFFSETS.".$tag" ] = $offset;
			if( $theCollection->matchAll( $criteria, kQUERY_ARRAY ) )
				unset( $ref[ $key ] );
		
		} // Iterating offsets.
		
		//
		// Handle empty list.
		//
		if( ! count( $ref ) )
			$theIterator->offsetUnset( $theIterator->key() );
		
		//
		// Update list.
		//
		else
		{
			//
			// Normalise list.
			//
			$ref = array_values( $ref );
			
			//
			// Update list.
			//
			$theIterator->offsetSet( $theIterator->key(), $info );
		
		} // Offsets left.
		
		return TRUE;																// ==>
	
	} // traverseFilterOffsets.

	 
	/*===================================================================================
	 *	traverseRemoveOffsets															*
	 *==================================================================================*/

	/**
	 * Remove offsets from tag objects
	 *
	 * This method is called for each element of the tags list parameter described in the
	 * {@link preDelete()} method, its duty is to remove all offsets featured in the list
	 * from tag objects.
	 *
	 * This method will be invoked <em>after running the {@link traverseFilterOffsets()}
	 * method</em>, which means that the iterated list will only hold those offsets which
	 * are not referenced any more by objects of the current class.
	 *
	 * The method will iterate the list and remove from the relative tag object set all the
	 * offsets featured in the current list element.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theIterator</b>: This parameter points to the current element of the tags
	 *		list.
	 *	<li><b>$theTagCollection</b>: The collection of tag objects.
	 *	<li><b>$theCollection</b>: The collection of the current object.
	 * </ul>
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param CollectionObject		$theCollection		Tag object collection.
	 * @param string				$theOffset			Tag object set offset.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> continues the traversal.
	 */
	protected function traverseRemoveOffsets( \Iterator		   $theIterator,
											  CollectionObject $theCollection,
															   $theOffset )
	{
		//
		// Remove from set.
		//
		$theCollection->updateSet(
			(int) $theIterator->key(),
			kTAG_ID_SEQUENCE,
			array( $theOffset => $theIterator->current()[ kTAG_OBJECT_OFFSETS ] ),
			FALSE );
		
		return TRUE;																// ==>
	
	} // traverseRemoveOffsets.

	

/*=======================================================================================
 *																						*
 *								PROTECTED STATUS INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	isReady																			*
	 *==================================================================================*/

	/**
	 * Check if object is ready
	 *
	 * This method should return <tt>TRUE</tt> if the object is ready to be committed.
	 *
	 * In this class we ensure the object is initialised and that it holds the dictionary.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> means ready.
	 *
	 * @uses isInited()
	 */
	protected function isReady()
	{
		return ( $this->isInited()
			  && ($this->mDictionary !== NULL) );									// ==>
	
	} // isReady.

		

/*=======================================================================================
 *																						*
 *							PROTECTED OFFSET STATUS INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	lockedOffsets																	*
	 *==================================================================================*/

	/**
	 * Return list of locked offsets
	 *
	 * This method should return the list of locked offsets, that is, the offsets which
	 * cannot be modified once the object has been committed.
	 *
	 * In this class we return the list of internal tags plus the {@link kTAG_MASTER}.
	 *
	 * @access protected
	 * @return array				List of locked offsets.
	 *
	 * @see kTAG_MASTER
	 *
	 * @uses InternalOffsets()
	 */
	protected function lockedOffsets()
	{
		return array_merge( $this->InternalOffsets(), (array) kTAG_MASTER );		// ==>
	
	} // lockedOffsets.

		

/*=======================================================================================
 *																						*
 *							PROTECTED RESOLUTION UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getOffsetTypes																	*
	 *==================================================================================*/

	/**
	 * Resolve offset type
	 *
	 * In this class we hard-code the data types and kinds of the default tags, this is to
	 * allow loading the data dictionary on a pristine system.
	 *
	 * If the provided offset is not among the ones handled in this method, it will call
	 * the parent method.
	 *
	 * @param string				$theOffset			Current offset.
	 * @param reference				$theType			Receives data type.
	 * @param reference				$theKind			Receives data kind.
	 *
	 * @access protected
	 * @return mixed				<tt>TRUE</tt> if the tag was resolved.
	 */
	protected function getOffsetTypes( $theOffset, &$theType, &$theKind )
	{
		//
		// Handle default tags.
		//
		switch( $theOffset )
		{
			//
			// Scalar strings.
			//
			case kTAG_AUTHORITY:
			case kTAG_COLLECTION:
			case kTAG_IDENTIFIER:
			case kTAG_ID_LOCAL:
			case kTAG_ID_PERSISTENT:
			case kTAG_ID_VALID:
			case kTAG_VERSION:
			case kTAG_NAME:
			case kTAG_TYPE:
			case kTAG_LANGUAGE:
			case kTAG_TEXT:
			case kTAG_PATTERN:
			case kTAG_ENTITY_FNAME:
			case kTAG_ENTITY_LNAME:
			case kTAG_CONN_PROTOCOL:
			case kTAG_CONN_HOST:
			case kTAG_CONN_USER:
			case kTAG_CONN_PASS:
			case kTAG_CONN_BASE:
			case kTAG_CONN_COLL:
				$theType = array( kTYPE_STRING );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Enumerations.
			//
			case kTAG_DOMAIN:
			case kTAG_ENTITY_COUNTRY:
				$theType = array( kTYPE_ENUM );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Enumerated sets.
			//
			case kTAG_CATEGORY:
			case kTAG_DATA_TYPE:
			case kTAG_DATA_KIND:
			case kTAG_TERM_TYPE:
			case kTAG_NODE_TYPE:
			case kTAG_ENTITY_TYPE:
			case kTAG_ENTITY_KIND:
				$theType = array( kTYPE_SET );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar integers.
			//
			case kTAG_ID_SEQUENCE:
			case kTAG_CONN_PORT:
				$theType = array( kTYPE_INT );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar floats.
			//
			case kTAG_MIN:
			case kTAG_MAX:
				$theType = array( kTYPE_FLOAT );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar URLs.
			//
			case kTAG_URL:
				$theType = array( kTYPE_URL );
				$theKind = Array();
				return TRUE;														// ==>
			
			//
			// Scalar term references.
			//
			case kTAG_NAMESPACE:
			case kTAG_TERM:
			case kTAG_PREDICATE:
				$theType = array( kTYPE_REF_TERM );
				$theKind = Array();
				return TRUE;														// ==>
			
			//
			// Scalar tag references.
			//
			case kTAG_TAG:
				$theType = array( kTYPE_REF_TAG );
				$theKind = Array();
				return TRUE;														// ==>
			
			//
			// Scalar node references.
			//
			case kTAG_SUBJECT:
			case kTAG_OBJECT:
				$theType = array( kTYPE_REF_NODE );
				$theKind = Array();
				return TRUE;														// ==>
			
			//
			// Scalar entity references.
			//
			case kTAG_ENTITY:
			case kTAG_ENTITY_VALID:
				$theType = array( kTYPE_REF_ENTITY );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar array references.
			//
			case kTAG_CONN_OPTS:
				$theType = array( kTYPE_ARRAY );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar self references.
			//
			case kTAG_MASTER:
				$theType = array( kTYPE_REF_SELF );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar typed lists.
			//
			case kTAG_ENTITY_MAIL:
			case kTAG_ENTITY_EMAIL:
			case kTAG_ENTITY_LINK:
			case kTAG_ENTITY_PHONE:
			case kTAG_ENTITY_FAX:
			case kTAG_ENTITY_AFFILIATION:
				$theType = array( kTYPE_TYPED_LIST );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar shapes.
			//
			case kTAG_GEO_LOCATION:
			case kTAG_GEO_PUB_LOCATION:
				$theType = array( kTYPE_SHAPE );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Scalar language string references.
			//
			case kTAG_LABEL:
			case kTAG_DEFINITION:
			case kTAG_DESCRIPTION:
				$theType = array( kTYPE_LANGUAGE_STRINGS );
				$theKind = Array();
				return TRUE;														// ==>
		
			//
			// Term reference lists.
			//
			case kTAG_TERMS:
				$theType = array( kTYPE_REF_TERM );
				$theKind = array( kTYPE_LIST );
				return TRUE;														// ==>
		
			//
			// Tag reference lists.
			//
			case kTAG_TAGS:
				$theType = array( kTYPE_REF_TAG );
				$theKind = array( kTYPE_LIST );
				return TRUE;														// ==>
		
			//
			// String lists.
			//
			case kTAG_NOTE:
			case kTAG_SYNONYM:
			case kTAG_ENTITY_ACRONYM:
				$theType = array( kTYPE_STRING );
				$theKind = array( kTYPE_LIST );
				return TRUE;														// ==>
		
			//
			// Private string lists.
			//
			case kTAG_TAG_OFFSETS:
			case kTAG_TERM_OFFSETS:
			case kTAG_NODE_OFFSETS:
			case kTAG_EDGE_OFFSETS:
			case kTAG_ENTITY_OFFSETS:
			case kTAG_UNIT_OFFSETS:
				$theType = array( kTYPE_STRING );
				$theKind = array( kTYPE_LIST, kTYPE_PRIVATE_IN, kTYPE_PRIVATE_OUT );
				return TRUE;														// ==>
		
			//
			// Private integers.
			//
			case kTAG_UNIT_COUNT:
			case kTAG_ENTITY_COUNT:
			case kTAG_TAG_COUNT:
			case kTAG_TERM_COUNT:
			case kTAG_NODE_COUNT:
			case kTAG_EDGE_COUNT:
				$theType = array( kTYPE_INT );
				$theKind = array( kTYPE_PRIVATE_IN );
				return TRUE;														// ==>
		
			//
			// Private integer lists.
			//
			case kTAG_OBJECT_TAGS:
				$theType = array( kTYPE_INT );
				$theKind = array( kTYPE_LIST, kTYPE_PRIVATE_IN, kTYPE_PRIVATE_OUT );
				return TRUE;														// ==>
		
			//
			// Private scalar arrays.
			//
			case kTAG_OBJECT_OFFSETS:
				$theType = array( kTYPE_ARRAY );
				$theKind = array( kTYPE_PRIVATE_IN, kTYPE_PRIVATE_OUT );
				return TRUE;														// ==>
		
		} // Parsing default tags.
		
		return parent::getOffsetTypes( $theOffset, $theType, $theKind );			// ==>
	
	} // getOffsetTypes.

	 
	/*===================================================================================
	 *	getReferenceTypeClass															*
	 *==================================================================================*/

	/**
	 * Resolve object reference data type class
	 *
	 * Given a data type, this method will return the base class name corresponding to the
	 * referenced object, or <tt>NULL</tt> if the data type is not an object reference.
	 *
	 * If provided the {@link kTYPE_REF_SELF} data type, the method will return the current
	 * object's class name.
	 *
	 * @param string				$theType			Data type.
	 *
	 * @access public
	 * @return string				Base class name.
	 */
	public function getReferenceTypeClass( $theType )
	{
		//
		// Parse by data type.
		//
		switch( (string) $theType )
		{
			case kTYPE_REF_TAG:
				return 'OntologyWrapper\Tag';										// ==>
		
			case kTYPE_SET:
			case kTYPE_ENUM:
			case kTYPE_REF_TERM:
				return 'OntologyWrapper\Term';										// ==>
		
			case kTYPE_REF_NODE:
				return 'OntologyWrapper\Node';										// ==>
		
			case kTYPE_REF_EDGE:
				return 'OntologyWrapper\Edge';										// ==>
		
			case kTYPE_REF_UNIT:
				return 'OntologyWrapper\UnitObject';								// ==>
		
			case kTYPE_REF_ENTITY:
				return 'OntologyWrapper\EntityObject';								// ==>
		
			case kTYPE_REF_SELF:
				return get_class( $this );											// ==>
		
		} // Parsed collection name.
		
		return NULL;																// ==>
	
	} // getReferenceTypeClass.

	 

/*=======================================================================================
 *																						*
 *							PROTECTED OBJECT TRAVERSAL UTILITIES						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	collectOffsetInformation														*
	 *==================================================================================*/

	/**
	 * Collect offset information
	 *
	 * This method will collect all the necessary information regarding the current offset.
	 * This method expects the provided iterator's current element to be pointing to an
	 * offset, not to an element of a list offset.
	 *
	 * The main duty of this method is to:
	 *
	 * <ul>
	 *	<li><em>Compute the current offset path</em>. The method will return the path of the
	 *		current offset, if the offset is among the internal offsets,
	 *		{@link InternalOffsets()}, the method will exit and return <tt>NULL</tt>.
	 *	<li><em>Resolve the offset data type and kind</em>. The method should return in the
	 *		provided reference parameters the current offset's types and kinds.
	 *	<li><em>Copy data type and kind in tags list</em>. If the offset is not a structure,
	 *		the method will copy the type and kind to the tags list.
	 * </ul>
	 *
	 * For a description of the <tt>$theTags</tt> parameter, please consult the
	 * {@link traverseValidate()} documentation.
	 *
	 * The method will return the current offset string, or <tt>NULL</tt> if the offset is
	 * internal; in the latter case, this method will do nothing.
	 *
	 * Derived classes should not need to overload this method.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$thePath			Offsets path.
	 * @param reference				$theTags			Property tags and offsets.
	 * @param reference				$theType			Receives data type.
	 * @param reference				$theKind			Receives data kind.
	 *
	 * @access protected
	 * @return string				Current offset string.
	 *
	 * @uses InternalOffsets()
	 * @uses getOffsetTypes()
	 */
	protected function collectOffsetInformation( \Iterator $theIterator, &$thePath,
																		 &$theTags,
																		 &$theType,
																		 &$theKind )
	{
		//
		// Skip internal offsets.
		//
		if( ! in_array( $theIterator->key(),
						array_merge( static::InternalOffsets(),
									 static::PrivateOffsets() ) ) )
		{
			//
			// Determine offset string.
			//
			$offset = implode( '.', $thePath );
			
			//
			// Save current tag.
			//
			$tag = $theIterator->key();
		
			//
			// Handle existing tag.
			//
			if( array_key_exists( $tag, $theTags ) )
			{
				//
				// Get types and kinds.
				//
				$theType = $theTags[ $tag ][ kTAG_DATA_TYPE ];
				$theKind = $theTags[ $tag ][ kTAG_DATA_KIND ];
				
				//
				// Check offset.
				//
				if( ! in_array( $offset, $theTags[ $tag ][ kTAG_OBJECT_OFFSETS ] ) )
					$theTags[ $tag ][ kTAG_OBJECT_OFFSETS ][] = $offset;
			
			} // Existing tag.
			
			//
			// Handle new tag.
			//
			else
			{
				//
				// Get types and kinds.
				//
				$this->getOffsetTypes( $tag, $theType, $theKind );
				
				//
				// Add tag if not a structure.
				//
				if( ! in_array( kTYPE_STRUCT, $theType ) )
				{
					//
					// Set types and kinds.
					//
					$theTags[ $tag ][ kTAG_DATA_TYPE ] = $theType;
					$theTags[ $tag ][ kTAG_DATA_KIND ] = $theKind;
					
					//
					// Set offset.
					//
					$theTags[ $tag ][ kTAG_OBJECT_OFFSETS ] = array( $offset );
				
				} // Not a structure.
			
			} // New tag.
			
			return $offset;															// ==>
		
		} // Not an internal offset.
		
		return NULL;																// ==>
	
	} // collectOffsetInformation.

	 
	/*===================================================================================
	 *	collectObjectReference															*
	 *==================================================================================*/

	/**
	 * Collect object reference
	 *
	 * This method will add the provided identifier to the provided references parameter
	 * under the element indexed by the collection name corresponding to the provided class
	 * name.
	 *
	 * @param reference				$theRefs			Object references.
	 * @param string				$theClass			Class name.
	 * @param mixed					$theIdentifier		Referenced object identifier.
	 *
	 * @access protected
	 */
	protected function collectObjectReference( &$theRefs, $theClass, $theIdentifier )
	{
		//
		// Create collection entry.
		//
		if( ! array_key_exists( $theClass::kSEQ_NAME, $theRefs ) )
			$theRefs[ $theClass::kSEQ_NAME ]
				= ( is_array( $theIdentifier ) )
				? $theIdentifier
				: array( $theIdentifier );
		
		//
		// Update collection entry.
		//
		else
		{
			//
			// Normalise value.
			//
			if( ! is_array( $theIdentifier ) )
				$theIdentifier = array( $theIdentifier );
			
			//
			// Update list.
			//
			$theRefs[ $theClass::kSEQ_NAME ]
				= array_merge(
					$theRefs[ $theClass::kSEQ_NAME ],
					array_diff( $theIdentifier, $theRefs[ $theClass::kSEQ_NAME ] ) );
		
		} // Collection exists.
	
	} // collectObjectReference.

		
	/*===================================================================================
	 *	verifyStructureOffset															*
	 *==================================================================================*/

	/**
	 * Verify structure offset
	 *
	 * This method should verify the structure of the current offset value which should
	 * either be a structure or a list.
	 *
	 * In this class we verify whether lists and structures are arrays and raise an
	 * exception if that is not the case.
	 *
	 * When we check if the data type contains the {@link kTYPE_STRUCT} type, we assume that
	 * in that case the property cannot have any other primitive data type, therefore the
	 * value <em>must</em> be an array.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset.
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> if structure or list.
	 *
	 * @throws Exception
	 */
	protected function verifyStructureOffset( \Iterator $theIterator, &$theType,
																	  &$theKind,
																	  &$theOffset )
	{
		//
		// Assert lists.
		//
		if( in_array( kTYPE_LIST, $theKind ) )
		{
			//
			// Verify list.
			//
			if( ! is_array( $theIterator->current() ) )
				throw new \Exception(
					"Invalid offset list value in [$theOffset]: "
				   ."the value is not an array." );								// !@! ==>
		
		} // List.
	
		//
		// Assert structure.
		//
		elseif( in_array( kTYPE_STRUCT, $theType ) )
		{
			//
			// Verify structure.
			//
			if( ! is_array( $theIterator->current() ) )
				throw new \Exception(
					"Invalid offset structure value in [$theOffset]: "
				   ."the value is not an array." );								// !@! ==>
		
		} // Is a structure.
		
	} // verifyStructureOffset.

	 
	/*===================================================================================
	 *	verifyOffsetValue																*
	 *==================================================================================*/

	/**
	 * Verify offset value
	 *
	 * This method should verify if the current offset's value is correct, this method is
	 * called by the {@link traverseValidateValue()} method which is called only if the
	 * current offset is neither a structure nor a list; list elements, however, are passed
	 * to this method with the parent offset data type and kind.
	 *
	 * In this class we assert that structured types are arrays, <em>only if the current
	 * offset data type has a single entry</em>.
	 *
	 * The method will return <tt>NULL</tt> if the offset has more than one data type,
	 * <tt>TRUE</tt> if the value was verified and <tt>FALSE</tt> if it was not verified.
	 *
	 * Derived classes can handle custom cases by handling the value if the parent method
	 * has not returnd <tt>TRUE</tt>.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset string.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 */
	protected function verifyOffsetValue( \Iterator $theIterator, &$theType,
																  &$theKind,
																  &$theOffset )
	{
		//
		// Verify single data types.
		//
		if( count( $theType ) == 1 )
		{
			//
			// Assert array values.
			//
			switch( current( $theType ) )
			{
				case kTYPE_SET:
				case kTYPE_ARRAY:
				case kTYPE_TYPED_LIST:
				case kTYPE_LANGUAGE_STRINGS:
					if( ! is_array( $theIterator->current() ) )
						throw new \Exception(
							"Invalid offset value in [$theOffset]: "
						   ."the value is not an array." );						// !@! ==>
					
					return TRUE;													// ==>
				
				case kTYPE_SHAPE:
					if( ! is_array( $theIterator->current() ) )
						throw new \Exception(
							"Invalid offset value in [$theOffset]: "
						   ."the value is not an array." );						// !@! ==>
					
					if( (! array_key_exists( kTAG_SHAPE_TYPE, $theIterator->current() ))
					 || (! array_key_exists( kTAG_SHAPE_GEOMETRY, $theIterator->current() ))
					 || (! is_array( $theIterator->current()[ kTAG_SHAPE_GEOMETRY ] )) )
						throw new \Exception(
							"Invalid offset value in [$theOffset]: "
						   ."invalid shape geometry." );						// !@! ==>
					
					return TRUE;													// ==>
				
				default:
					if( is_array( $theIterator->current() ) )
						throw new \Exception(
							"Invalid offset value in [$theOffset]: "
						   ."array not expected." );							// !@! ==>
					
					return TRUE;													// ==>
			
			} // Parsed data type.
			
			return FALSE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // verifyOffsetValue.

	 
	/*===================================================================================
	 *	verifyOffsetReference															*
	 *==================================================================================*/

	/**
	 * Verify object reference
	 *
	 * The duty of this method is to resolve and verify offset values which should be object
	 * references.
	 *
	 * The method expects the current offset to have an object reference type, this must
	 * have been checked beforehand; if the data type is not an object reference, the method
	 * will ignore the value.
	 *
	 * The current element may either be an object reference or the object itself, if the
	 * object is not {@link isCommitted()}, the method will commit it; if the object is
	 * {@link isCommitted()}, the method assumes the object exists in its container; in all
	 * other cases the method assumes the value to represent an object native identifier and
	 * it will check if the referenced object exists.
	 *
	 * The method will raise an exception if the provided object is not of the correct class
	 * and if the provided object reference is not found in its default container. It is
	 * assumed that all references belong to the current data dictionary,
	 * {@link dictionary()}.
	 *
	 * In this class we handle all object reference data types, <em>only if the current
	 * offset data type has a single entry</em>.
	 *
	 * The method will return <tt>NULL</tt> if the offset has more than one data type,
	 * <tt>TRUE</tt> if the reference was resolved and <tt>FALSE</tt> if it was not
	 * resolved; this will only happen if the element's data type is not recognised.
	 *
	 * Derived classes can handle custom cases by calling the parent method and checking the
	 * retuned value.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theRefs			Object references.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset string.
	 * @param boolean				$doValidate			<tt>TRUE</tt> validate.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses getReferenceTypeClass()
	 * @uses collectObjectReference()
	 */
	protected function verifyOffsetReference( \Iterator $theIterator, &$theRefs,
																	  &$theType,
																	  &$theKind,
																	  &$theOffset,
																	   $doValidate = TRUE )
	{
		//
		// Verify single data types.
		//
		if( count( $theType ) == 1 )
		{
			//
			// Init local storage.
			//
			$type = current( $theType );
			$value = $theIterator->current();
			$class = $this->getReferenceTypeClass( $type );
		
			//
			// Check type.
			//
			if( $class === NULL )
				return FALSE;														// ==>
		
			//
			// Handle objects.
			//
			if( is_object( $value ) )
			{
				//
				// Verify class.
				//
				if( ! ($value instanceof $class) )
					throw new \Exception(
						"Invalid object reference in [$theOffset]: "
					   ."incorrect class object." );							// !@! ==>
			
				//
				// Commit object.
				//
				if( ! $value->isCommitted() )
					$id = $value->commit( $this->mDictionary );
			
				//
				// Get identifier.
				//
				elseif( ! $value->offsetExists( kTAG_NID ) )
					throw new \Exception(
						"Invalid object in [$theOffset]: "
					   ."missing native identifier." );							// !@! ==>
			
				//
				// Get identifier.
				//
				else
					$id = $value[ kTAG_NID ];
			
				//
				// Set identifier.
				//
				$theIterator->offsetSet( $theIterator->key(), $id );
			
				//
				// Add reference count.
				//
				$this->collectObjectReference( $theRefs, $class, $id );
			
				return TRUE;														// ==>
		
			} // Property is an object.
				
			//
			// Cast identifier.
			//
			switch( $type )
			{
				case kTYPE_REF_TAG:
				case kTYPE_ENUM:
				case kTYPE_REF_TERM:
				case kTYPE_REF_EDGE:
				case kTYPE_REF_ENTITY:
				case kTYPE_REF_UNIT:
					$value = (string) $value;
					break;
		
				case kTYPE_REF_NODE:
					$value = (int) $value;
					break;
		
				case kTYPE_SET:
					foreach( $value as $key => $val )
						$value[ $key ] = (string) $val;
					break;
				
				case kTYPE_REF_SELF:
					switch( $class::kSEQ_NAME )
					{
						case Tag::kSEQ_NAME:
						case Term::kSEQ_NAME:
						case Edge::kSEQ_NAME:
						case UnitObject::kSEQ_NAME:
						case EntityObject::kSEQ_NAME:
							$value = (string) $value;
							break;
						case Node::kSEQ_NAME:
							$value = (int) $value;
							break;
						default:
							return FALSE;											// ==>
					}
					break;
			
				default:
					return FALSE;													// ==>
		
			} // Parsed type.
			
			//
			// Verify reference.
			//
			if( $doValidate )
			{
				//
				// Resolve collection.
				//
				$collection
					= $class::ResolveCollection(
						$class::ResolveDatabase( $this->mDictionary ) );
		
				//
				// Resolve reference.
				//
				if( is_array( $value ) )
				{
					foreach( $value as $val )
					{
						if( ! $collection->matchOne(
								array( kTAG_NID => $val ), kQUERY_COUNT ) )
							throw new \Exception(
								"Unresolved reference in [$theOffset]: "
							   ."($val)." );									// !@! ==>
					}
				}
				elseif( ! $collection->matchOne(
							array( kTAG_NID => $value ), kQUERY_COUNT ) )
					throw new \Exception(
						"Unresolved reference in [$theOffset]: "
					   ."($value)." );											// !@! ==>
			
			} // Requested verification.
		
			//
			// Cast value.
			//
			$theIterator->offsetSet( $theIterator->key(), $value );
		
			//
			// Add reference count.
			//
			$this->collectObjectReference( $theRefs, $class, $value );
		
			return TRUE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // verifyOffsetReference.

	 
	/*===================================================================================
	 *	castOffsetValue																	*
	 *==================================================================================*/

	/**
	 * Cast offset value
	 *
	 * The duty of this method is to cast the iterator's current value to the correct data
	 * type, the method will only be called for scalar values.
	 *
	 * If the property has more than one data type, the method will do nothing; you should
	 * overload this method in derived classes only if you plan to handle offsets that can
	 * have more than one data type.
	 *
	 * The method will return <tt>TRUE</tt> if the value was cast, <tt>FALSE</tt> if not and
	 * <tt>NULL</tt> if the offset has more than one data type.
	 *
	 * @param Iterator				$theIterator		Iterator.
	 * @param reference				$theRefs			Receives object references.
	 * @param reference				$theType			Offset data type.
	 * @param reference				$theKind			Offset data kind.
	 * @param reference				$theOffset			Current offset string.
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses castShapeGeometry()
	 * @uses traverseValidate()
	 */
	protected function castOffsetValue( \Iterator $theIterator, &$theRefs,
																&$theType,
																&$theKind,
																&$theOffset )
	{
		//
		// Cast only single types.
		//
		if( count( $theType ) == 1 )
		{
			//
			// Init local storage.
			//
			$type = current( $theType );
			$key = $theIterator->key();
			$value = $theIterator->current();
			
			//
			// Parse by type.
			//
			switch( $type )
			{
				//
				// Strings.
				//
				case kTYPE_STRING:
				case kTYPE_ENUM:
				case kTYPE_URL:
				case kTYPE_REF_TAG:
				case kTYPE_REF_TERM:
				case kTYPE_REF_EDGE:
				case kTYPE_REF_UNIT:
				case kTYPE_REF_ENTITY:
					$theIterator->offsetSet( $key, (string) $value );
					return TRUE;													// ==>
				
				//
				// Integers.
				//
				case kTYPE_INT:
				case kTYPE_REF_NODE:
					$theIterator->offsetSet( $key, (int) $value );
					return TRUE;													// ==>
		
				//
				// Floats.
				//
				case kTYPE_FLOAT:
					$theIterator->offsetSet( $key, (double) $value );
					return TRUE;													// ==>
		
				//
				// Enumerated sets.
				//
				case kTYPE_SET:
					//
					// Iterate set.
					//
					$idxs = array_keys( $value );
					foreach( $idxs as $idx )
						$value[ $idx ] = (string) $value[ $idx ];
					//
					// Set value.
					//
					$theIterator->offsetSet( $key, $value );
					return TRUE;													// ==>
		
				//
				// Language strings.
				//
				case kTYPE_TYPED_LIST:
				case kTYPE_LANGUAGE_STRINGS:
					//
					// Init loop storage.
					//
					$tags = Array();
					$path = explode( '.', $theOffset );
					//
					// Iterate elements.
					//
					$idxs = array_keys( $value );
					foreach( $idxs as $idx )
					{
						//
						// Check format.
						//
						if( ! is_array( $value[ $idx ] ) )
							throw new \Exception(
								"Invalid offset value element in [$theOffset]: "
							   ."the value is not an array." );					// !@! ==>
						//
						// Traverse element.
						//
						$struct = new \ArrayObject( $value[ $idx ] );
						$iterator = $struct->getIterator();
						iterator_apply( $iterator,
										array( $this, 'traverseValidate' ),
										array( $iterator, & $path,
														  & $tags,
														  & $theRefs ) );
						//
						// Set element.
						//
						$value[ $idx ] = $struct->getArrayCopy();
					}
					//
					// Set value.
					//
					$theIterator->offsetSet( $key, $value );
					return TRUE;													// ==>
		
				//
				// Shapes.
				//
				case kTYPE_SHAPE:
					//
					// Cast geometry.
					//
					$this->castShapeGeometry( $value );
					//
					// Update offset.
					//
					$theIterator->offsetSet( $key, $value );
					return TRUE;													// ==>
		
			} // Parsed type.
			
			return FALSE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // castOffsetValue.

	 
	/*===================================================================================
	 *	castShapeGeometry																*
	 *==================================================================================*/

	/**
	 * Cast shape geometry
	 *
	 * The duty of this method is to verify and cast the geometry of the provided shape.
	 * The method expects the shape to have a correct root structure, that is, it must have
	 * the shape type and geometry array, this method will traverse the geometry and
	 * its structure.
	 *
	 * @param reference				$theShape			Shape.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function castShapeGeometry( &$theShape )
	{
		//
		// Init local storage.
		//
		$type = & $theShape[ kTAG_SHAPE_TYPE ];
		$geom = & $theShape[ kTAG_SHAPE_GEOMETRY ];
		
		//
		// Parse by type.
		//
		switch( $type )
		{
			case 'Point':
				//
				// Check geometry.
				//
				if( is_array( $geom ) )
				{
					//
					// Check if both are there.
					//
					if( count( $geom ) == 2 )
					{
						$geom[ 0 ] = (double) $geom[ 0 ];
						$geom[ 1 ] = (double) $geom[ 1 ];
					}
					else
						throw new \Exception(
							"Invalid point shape structure." );					// !@! ==>
				}
				else
					throw new \Exception(
						"Invalid shape structure." );							// !@! ==>
				break;
			
			case 'LineString':
				//
				// Check geometry.
				//
				if( is_array( $geom ) )
				{
					//
					// Iterate coordinates.
					//
					$idxs = array_keys( $geom );
					foreach( $idxs as $idx )
					{
						//
						// Recurse with points.
						//
						$shape = array( kTAG_SHAPE_TYPE => 'Point',
										kTAG_SHAPE_GEOMETRY => $geom[ $idx ] );
						$this->castShapeGeometry( $shape );
						$geom[ $idx ] = $shape[ kTAG_SHAPE_GEOMETRY ];
					}
				}
				else
					throw new \Exception(
						"Invalid shape structure." );							// !@! ==>
				break;
			
			case 'Polygon':
				//
				// Check geometry.
				//
				if( is_array( $geom ) )
				{
					//
					// Iterate rings.
					//
					$idxs = array_keys( $geom );
					foreach( $idxs as $idx )
					{
						//
						// Recurse with line strings.
						//
						$shape = array( kTAG_SHAPE_TYPE => 'LineString',
										kTAG_SHAPE_GEOMETRY => $geom[ $idx ] );
						$this->castShapeGeometry( $shape );
						$geom[ $idx ] = $shape[ kTAG_SHAPE_GEOMETRY ];
					}
				}
				else
					throw new \Exception(
						"Invalid shape structure." );							// !@! ==>
				break;
			
			default:
				throw new \Exception(
					"Invalid or unsupported shape type [$type]." );				// !@! ==>
		
		} // Parsed type.
	
	} // castShapeGeometry.

		

/*=======================================================================================
 *																						*
 *								PROTECTED REFERENCE UTILITIES							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	updateReferenceCount															*
	 *==================================================================================*/

	/**
	 * Update reference count
	 *
	 * This method will update the references count of the object identified by the provided
	 * parameter. If the provided collection has no associated reference count, an
	 * exception will be raised.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The name of the collection where the provided identifiers
	 *		are stored.
	 *	<li><b>$theIdent</b>: The object identifier or list of identifiers.
	 *	<li><b>$theIdentOffset</b>: The offset matching to the provided identifier.
	 *	<li><b>$theCount</b>: The number of references.
	 * </ul>
	 *
	 * The method will first resolve the reference count tag corresponding to the current
	 * object's collection by using the static {@link ResolveRefCountTag()} method, it will
	 * then select all objects where the <tt>$theIdentOffset</tt> matches the references
	 * provided in <tt>$theIdent</tt> and with each selected object it will increment
	 * the reference count stored in the reference count tag resolved at the beginning by
	 * the value provided in the <tt>$theCount</tt> parameter.
	 *
	 * The method assumes the current object has its {@link dictionary()} set.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theIdent			Object identifier or identifiers.
	 * @param string				$theIdentOffset		Identifier offset.
	 * @param integer				$theCount			Reference count.
	 *
	 * @access protected
	 *
	 * @uses ResolveRefCountTag()
	 * @uses ResolveCollectionByName()
	 */
	protected function updateReferenceCount( $theCollection, $theIdent,
															 $theIdentOffset = kTAG_NID,
															 $theCount = 1 )
	{
		//
		// Resolve reference count tag according to current object.
		//
		$tag = static::ResolveRefCountTag( static::kSEQ_NAME );
		
		//
		// Resolve collection.
		//
		$collection
			= static::ResolveCollectionByName(
				$this->mDictionary, (string) $theCollection );
		
		//
		// Update reference count.
		//
		$collection->updateReferenceCount($theIdent, $theIdentOffset, $tag, $theCount );
	
	} // updateReferenceCount.

	 
	/*===================================================================================
	 *	updateOffsetsSet																*
	 *==================================================================================*/

	/**
	 * Update offsets set
	 *
	 * This method will update the offsets set corresponding to the current object in the
	 * provided tag references.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The tags collection.
	 *	<li><b>$theOffset</b>: The tag object offset receiving the offsets list.
	 *	<li><b>$theOffsets</b>: The list of offsets.
	 *	<li><b>$doAdd</b>: <tt>TRUE</tt> means add to set, <tt>FALSE</tt> means remove from
	 *		set.
	 * </ul>
	 *
	 * The method assumes the current object has its {@link dictionary()} set.
	 *
	 * @param CollectionObject		$theCollection		Tags collection.
	 * @param int					$theTag				Tag sequence number.
	 * @param string				$theOffset			Tag offsets list offset.
	 * @param array					$theOffsets			Offsets list.
	 * @param boolean				$doAdd				Add or remove from set.
	 *
	 * @access protected
	 */
	protected function updateOffsetsSet( CollectionObject $theCollection,
														  $theTag, $theOffset, $theOffsets,
														  $doAdd = TRUE )
	{
		//
		// Update set.
		//
		$theCollection->updateSet(
			(int) $theTag,							// Tag sequence number.
			kTAG_ID_SEQUENCE,						// Sequence number offset.
			array( $theOffset => $theOffsets ),		// Offsets list.
			$doAdd );								// Add or delete.
	
	} // updateOffsetsSet.

		

/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	resolveWrapper																	*
	 *==================================================================================*/

	/**
	 * Resolve wrapper
	 *
	 * This method can be used to resolve the wrapper, it expects a reference to a wrapper
	 * which will either set the current object's {@link dictionary()}, or will be set by
	 * the current object's {@link dictionary()}.
	 *
	 * The method assumes that the wrapper must be resolved, if that is not the case, the
	 * method will raise an exception.
	 *
	 * @param reference				$theWrapper			Data wrapper.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 */
	protected function resolveWrapper( &$theWrapper )
	{
		//
		// Use dictionary.
		//
		if( $theWrapper === NULL )
		{
			//
			// Set wrapper with dictionary.
			//
			$theWrapper = $this->mDictionary;
			if( $theWrapper === NULL )
				throw new \Exception( "Missing wrapper." );						// !@! ==>
		
		} // Used object dictionary.
		
		//
		// Set dictionary.
		//
		elseif( $theWrapper instanceof Wrapper )
			$this->dictionary( $theWrapper );
		
		//
		// Invalid wrapper.
		//
		else
			throw new \Exception( "Invalid wrapper type." );					// !@! ==>
	
	} // resolveWrapper.

	 
	/*===================================================================================
	 *	validateReference																*
	 *==================================================================================*/

	/**
	 * Validate object reference
	 *
	 * This method will validate the provided reference, it will check whether the value is
	 * an object, in which case it will use its native identifier if committed, or check
	 * whether it is of the correct type.
	 *
	 * @param reference				$theValue			Object reference.
	 * @param string				$theClass			Ancestor class name.
	 * @param string				$theType			Reference data type.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @see kTYPE_REF_TERM kTYPE_REF_TAG kTYPE_REF_NODE kTYPE_REF_EDGE
	 * @see kTYPE_REF_ENTITY kTYPE_REF_UNIT kTYPE_REF_SELF
	 */
	protected function validateReference( &$theValue, $theClass, $theType )
	{
		//
		// Handle namespace object.
		//
		if( is_object( $theValue ) )
		{
			//
			// Handle terms.
			//
			if( $theValue instanceof $theClass )
			{
				//
				// Get object reference.
				//
				if( $theValue->isCommitted() )
					$theValue = $theValue->reference();
			
			} // Is a term.
			
			else
				throw new \Exception(
					"Unable to set object reference: "
				   ."provided an object other than $theClass." );				// !@! ==>
		
		} // Namespace object.
		
		//
		// Handle reference.
		//
		else
		{
			//
			// Cast identifier.
			//
			switch( $theType )
			{
				case kTYPE_REF_TAG:
				case kTYPE_ENUM:
				case kTYPE_REF_TERM:
				case kTYPE_REF_EDGE:
				case kTYPE_REF_ENTITY:
				case kTYPE_REF_UNIT:
					$theValue = (string) $theValue;
					break;
		
				case kTYPE_REF_NODE:
					$theValue = (int) $theValue;
					break;
		
				case kTYPE_SET:
					foreach( $theValue as $key => $val )
						$theValue[ $key ] = (string) $val;
					break;
				
				case kTYPE_REF_SELF:
					switch( $class::kSEQ_NAME )
					{
						case Tag::kSEQ_NAME:
						case Term::kSEQ_NAME:
						case Edge::kSEQ_NAME:
						case UnitObject::kSEQ_NAME:
						case EntityObject::kSEQ_NAME:
							$theValue = (string) $theValue;
							break;
						case Node::kSEQ_NAME:
							$theValue = (int) $theValue;
							break;
					}
					break;
		
			} // Parsed type.
		
		} // Object reference.
	
	} // validateReference.

	 
	/*===================================================================================
	 *	compareObjectOffsets															*
	 *==================================================================================*/

	/**
	 * Compare object offsets
	 *
	 * This method expects two parameters structured as the {@link kTAG_OBJECT_OFFSETS}
	 * property, it will return either the differences or the matches between the two
	 * parameters.
	 *
	 * The difference is computed by selecting all collections featured by the first
	 * parameter not existing in the second parameter. If the collection exists in both,
	 * the references featured by the first collection that are not part of the same
	 * collection in the second parameter will be selected.
	 *
	 * The similarity is computed by selecting only those collections belonging to both
	 * parameters and the references in those collections existing in both collections.
	 *
	 * If the third parameter is <tt>TRUE</tt>, the method will compute the difference, if
	 * not, the similarity.
	 *
	 * The method will return the computed array, no collections will be empty.
	 *
	 * <em>The method expects both parameters to be arrays structured as the
	 * {@link kTAG_OBJECT_OFFSETS} property.
	 *
	 * @param reference				$theFirst			Reference parameter.
	 * @param reference				$theSecond			Compare parameter.
	 * @param boolean				$doDiff				<tt>TRUE</tt> compute difference.
	 *
	 * @access protected
	 * @return array				The difference or intersection.
	 *
	 * @throws Exception
	 *
	 * @see kTAG_OBJECT_OFFSETS
	 */
	protected function compareObjectOffsets( &$theFirst, &$theSecond, $doDiff )
	{
		//
		// Init local storage.
		//
		$result = Array();
		
		//
		// Iterate reference parameter.
		//
		foreach( $theFirst as $collection => $references )
		{
			//
			// Handle difference.
			//
			if( $doDiff )
			{
				//
				// New collection.
				//
				if( ! array_key_exists( $collection, $theSecond ) )
					$result[ $collection ] = $references;
				
				//
				// Same collection.
				//
				else
				{
					//
					// Compute references difference.
					//
					$tmp = array_diff( $references, $theSecond[ $collection ] );
					if( count( $tmp ) )
						$result[ $collection ] = $tmp;
				
				} // Same collection.
			
			} // Difference.
		
			//
			// Handle intersection.
			//
			else
			{
				//
				// Same collection.
				//
				if( array_key_exists( $collection, $theSecond ) )
				{
					//
					// Compute references intersection.
					//
					$tmp = array_intersect( $references, $theSecond[ $collection ] );
					if( count( $tmp ) )
						$result[ $collection ] = $tmp;
				
				} // Same collection.
			
			} // Difference.
		
		} // Iterating first parameter.
		
		return $result;																// ==>
	
	} // compareObjectOffsets.

	 

} // class PersistentObject.


?>
