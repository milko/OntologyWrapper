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
			else
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

	 
	/*===================================================================================
	 *	delete																			*
	 *==================================================================================*/

	/**
	 * Delete the object
	 *
	 * This method will delete the current object from its persistent store, the method
	 * expects the current object to be {@link isCommitted()} and not {@link isDirty()},
	 * if that is not the case, the method will raise an exception.
	 *
	 * In particular, <em>the object must contain all its properties to ensure that all
	 * references are reset</em>. This is fundamental for ensuring referential integrity.
	 * For this reason best practice is to load the object and delete it immediately, to
	 * prevent other clients from modifying it in the meantime.
	 *
	 * This method will perform the following steps:
	 *
	 * <ul>
	 *	<li>We check whether the object has the native identifier, if that is not the case,
	 *		we return <tt>NULL</tt>.
	 *	<li>We call the <tt>{@link preDelete()}</tt> method that is responsible of:
	 *	 <ul>
	 *		<li><tt>{@link preDeletePrepare()}</tt>: Check if the object can be deleted, if
	 *			that is not the case, the method should return <tt>FALSE</tt>.
	 *		<li><tt>{@link preDeleteTraverse()}</tt>: Traverse the object's properties
	 *			retrieveing its tags and references.
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
	 * @access public
	 * @return mixed				Native identifier, <tt>NULL</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 */
	public final function delete()
	{
		//
		// Do it only if the object is committed and clean.
		//
		if( $this->isCommitted()
		 && (! $this->isDirty()) )
		{
			//
			// Check native identifier.
			//
			if( ! $this->offsetExists( kTAG_NID ) )
				return NULL;														// ==>
			
			//
			// Resolve collection.
			//
			$collection
				= static::ResolveCollection(
					static::ResolveDatabase( $this->dictionary() ) );
		
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
	
	} // delete.

		

/*=======================================================================================
 *																						*
 *								STATIC RESOLUTION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
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
	 *	ReferenceTypes																	*
	 *==================================================================================*/

	/**
	 * Get reference types
	 *
	 * This method will return the list of types that represent an object reference.
	 *
	 * @static
	 * @return array				List of reference types.
	 */
	static function ReferenceTypes()
	{
		return array( kTYPE_REF_TAG, kTYPE_REF_TERM, kTYPE_REF_NODE, kTYPE_REF_EDGE,
					  kTYPE_REF_ENTITY, kTYPE_REF_UNIT,
					  kTYPE_REF_SELF,
					  kTYPE_ENUM, kTYPE_SET );										// ==>
	
	} // ReferenceTypes.

		

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
	 *		{@link kTAG_OBJECT_TAGS} property and eventually compute the object's
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
	 				structural element.
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
	 * The method is declared final, so derived classes should only overload the called
	 * methods, if you need to modify the traversal parameters do so overloading the
	 * {@link preCommitPrepare()} method.
	 *
	 * @param reference				$theTags			Object tags.
	 * @param reference				$theRefs			Object references.
	 *
	 * @access protected
	 *
	 * @uses traverseValidate()
	 */
	protected final function preCommitTraverse( &$theTags, &$theRefs )
	{
		//
		// Init current path stack.
		//
		$path = Array();
		
		//
		// Traverse object.
		//
		$iterator = $this->getIterator();
		iterator_apply( $iterator,
						array( $this, 'traverseValidate' ),
						array( $iterator, & $path, & $theTags, & $theRefs ) );
	
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
	 *		the {@link kTAG_OBJECT_TAGS} object property.
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
	 * @uses preCommitObjectIdentifiers()
	 */
	protected function preCommitFinalise( &$theTags, &$theRefs )
	{
		//
		// Load object tags.
		//
		$this->preCommitObjectTags( $theTags );
	
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
	 * Only tags which feature the {@link kTAG_OBJECT_TAGS} element will be passed to the
	 * {@link collectObjectOffset()} method, these correspond to leaf offsets.
	 *
	 * For a description of the parameters to this method, please consult the
	 * {@link preCommit()} method documentation.
	 *
	 * If you need to filter specific tags, overload the {@link collectObjectOffset()}
	 * method, if you do not manage the {@link kTAG_OBJECT_TAGS} and
	 * {@link kTAG_OBJECT_OFFSETS} offsets, shadow this method.
	 *
	 * @param reference				$theTags			Object tags.
	 *
	 * @access protected
	 *
	 * @see kTAG_OBJECT_TAGS
	 *
	 * @uses collectObjectOffset()
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
			if( array_key_exists( kTAG_OBJECT_TAGS, $info ) )
				$this->collectObjectOffset( $tag, $info, $tags, $offsets );
		
		} // Iterating tags.
		
		//
		// Set offsets.
		//
		if( count( $tags ) )
		{
			$this->offsetSet( kTAG_OBJECT_TAGS, $tags );
			$this->offsetSet( kTAG_OBJECT_OFFSETS, $tags );
		}
	
	} // preCommitObjectTags.

	 
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
		// Update object references.
		//
		$this->postCommitReferences( $theRefs );
	
		//
		// Update object tags.
		//
		$this->postCommitTags( $theTags );
	
	} // postCommit.

	 
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
		foreach( $theRefs as $collection => $references )
			$this->updateReferenceCount( $collection, $references );
	
	} // postCommitReferences.

	 
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
	 * @throws Exception
	 *
	 * @uses updateReferenceCount()
	 */
	protected function postCommitTagRefCount( &$theTags )
	{
		//
		// Update tags reference count.
		//
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
	 * @uses dictionary()
	 */
	protected function postCommitTagOffsets( &$theTags )
	{
		//
		// Resolve collection.
		//
		$collection
			= Tag::ResolveCollection(
				Tag::ResolveDatabase( $this->dictionary() ) );
		
		//
		// Resolve set property.
		//
		$offset = static::ResolveRefCountTag( static::kSEQ_NAME );
		
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
	
	} // postCommitTagOffsets.

	

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
	 				structural element.
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
	 * The method will perform the following steps:
	 *
	 * <ul>
	 *	<li><em>Push the current offset to the path</em>: The current offset will be
	 *		appended to the path parameter.
	 *	<li><em>Collect offset tag information</em>: The {@link collectOffsetInformation()}
	 *		method will determine the types and kinds of the current offset value and update
	 *		the tags parameter. If the current offset is an internal offset, all steps
	 *		except the last one will be skipped.
	 *	<li><em>Verify offset structure</em>: The {@link verifyOffsetStructure()} method
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
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt>, or <tt>FALSE</tt> to stop the traversal.
	 *
	 * @uses collectOffsetInformation()
	 * @uses verifyOffsetStructure()
	 * @uses traverseValidateValue()
	 */
	final protected function traverseValidate( \Iterator $theIterator, &$thePath,
																	   &$theTags,
																	   &$theRefs )
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
			// Verify offset structure.
			//
			$this->verifyOffsetStructure( $theIterator, $type, $kind, $offset );
			
			//
			// Handle scalar offset.
			//
			if( (! in_array( kTYPE_LIST, $kind ))
			 && (! in_array( kTYPE_STRUCT, $type )) )
				$this->traverseValidateValue(
					$theIterator, $theRefs, $type, $kind, $offset );
		
			//
			// Handle structure and list offsets.
			//
			else
			{
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
															  & $theRefs ) );
		
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
														  & $theRefs ) );
			
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
													  & $offset ) );
			
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
	 *	<li><em>Cast the offset value</em>: The {@link castValue()} method will cast the
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
	 *
	 * @access protected
	 * @return boolean				<tt>TRUE</tt> continues the traversal.
	 *
	 * @uses verifyOffsetValue()
	 * @uses verifyOffsetReference()
	 * @uses castValue()
	 */
	final protected function traverseValidateValue( \Iterator $theIterator, &$theRefs,
																	&$theType,
																	&$theKind,
																	&$theOffset )
	{
		//
		// Verify value.
		//
		$this->verifyOffsetValue( $theIterator, $theType, $theKind, $theOffset );
		
		//
		// Verify reference.
		//
		if( count( array_intersect( $theType, static::ReferenceTypes() ) ) )
			$this->verifyOffsetReference(
				$theIterator, $theRefs, $theType, $theKind, $theOffset );
		
		//
		// Cast value.
		//
		$this->castValue( $theIterator, $theRefs, $theType, $theKind, $theOffset );
		
		return TRUE;																// ==>
	
	} // traverseValidateValue.

		

/*=======================================================================================
 *																						*
 *								PROTECTED OFFSET UTILITIES								*
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
	 *	collectObjectOffset																*
	 *==================================================================================*/

	/**
	 * Load object tags
	 *
	 * This method is responsible of selecting which tags should be included in the object's
	 * {@link kTAG_OBJECT_TAGS} and {@link kTAG_OBJECT_OFFSETS} properties, this method is
	 * called by the {@link preCommitObjectTags()} method which will filter out all tags
	 * which are not leaf offsets.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theTag</b>: Tag sequence number.
	 *	<li><b>$theInfo</b>: Tag information, this array parameter is structured as follows:
	 *	 <ul>
	 *		<li><tt>{@link kTAG_DATA_TYPE}</tt>: The item indexed by this key contains the
	 *			corresponding tag offset.
	 *		<li><tt>{@link kTAG_DATA_TYPE}</tt>: The item indexed by this key contains the
	 *			corresponding tag offset.
	 *		<li><tt>{@link kTAG_UNIT_OFFSETS}</tt>: The item indexed by this key contains the
	 *			list of offsets in which the tag is a leaf offset.
	 *	 </ul>
	 *	<li><b>$theTags</b>: This array parameter represents the set of tags which will be
	 *		set in the object's {@link kTAG_OBJECT_TAGS} offset. This parameter must be an
	 *		array.
	 *	<li><b>$theOffsets</b>: This array parameter represents the set of tag offsets which
	 *		will be set in the object's {@link kTAG_OBJECT_OFFSETS} offset. This parameter
	 *		must be an array.
	 * </ul>
	 *
	 * In this class we add all tags, derived classes can overload this method to filter
	 * specific tag categories.
	 *
	 * @param integer				$theTag				Tag sequence number.
	 * @param reference				$theInfo			Tag information.
	 * @param reference				$theTags			Receives tags list.
	 * @param reference				$theOffsets			Receives offsets list.
	 *
	 * @access protected
	 */
	protected function collectObjectOffset( $theTag, &$theInfo, &$theTags, &$theOffsets )
	{
		//
		// Cast tag.
		//
		$theTag = (int) $theTag;
	
		//
		// Add new tag.
		//
		if( ! in_array( $theTag, $theTags ) )
		{
			//
			// Add to tags list.
			//
			$theTags[] = $theTag;
			
			//
			// Create offsets entry.
			//
			if( array_key_exists( kTAG_OBJECT_OFFSETS, $theInfo ) )
				$theOffsets[ $theTag ]
					= $theInfo[ kTAG_OBJECT_OFFSETS ];
		
		} // New tag.
	
	} // collectObjectOffset.

		

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
		if( ! in_array( $theIterator->key(), static::InternalOffsets() ) )
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
	 *	verifyOffsetStructure															*
	 *==================================================================================*/

	/**
	 * Verify offset structure
	 *
	 * This method should verify the structure of the current offset value.
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
	protected function verifyOffsetStructure( \Iterator $theIterator, &$theType,
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
		
	} // verifyOffsetStructure.

	 
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
	 *
	 * @access protected
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>FALSE</tt>.
	 *
	 * @throws Exception
	 *
	 * @uses dictionary()
	 * @uses addReferenceCount()
	 */
	protected function verifyOffsetReference( \Iterator $theIterator, &$theRefs,
																	  &$theType,
																	  &$theKind,
																	  &$theOffset )
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
			$classes = array( kTYPE_REF_TAG => 'OntologyWrapper\Tag',
							  kTYPE_ENUM => 'OntologyWrapper\Term',
							  kTYPE_SET => 'OntologyWrapper\Term',
							  kTYPE_REF_TERM => 'OntologyWrapper\Term',
							  kTYPE_REF_NODE => 'OntologyWrapper\Node',
							  kTYPE_REF_EDGE => 'OntologyWrapper\Edge',
							  kTYPE_REF_ENTITY => 'OntologyWrapper\EntityObject',
							  kTYPE_REF_UNIT => 'OntologyWrapper\UnitObject' );
			
			//
			// Handle self reference.
			//
			if( $type == kTYPE_REF_SELF )
			{
				//
				// Parse by sequence name.
				//
				switch( static::kSEQ_NAME )
				{
					case Tag::kSEQ_NAME:
						$classes[ kTYPE_REF_SELF ] = $classes[ kTYPE_REF_TAG ];
						break;
						
					case Term::kSEQ_NAME:
						$classes[ kTYPE_REF_SELF ] = $classes[ kTYPE_REF_TERM ];
						break;
						
					case Node::kSEQ_NAME:
						$classes[ kTYPE_REF_SELF ] = $classes[ kTYPE_REF_NODE ];
						break;
						
					case Edge::kSEQ_NAME:
						$classes[ kTYPE_REF_SELF ] = $classes[ kTYPE_REF_EDGE ];
						break;
						
					case EntityObject::kSEQ_NAME:
						$classes[ kTYPE_REF_SELF ] = $classes[ kTYPE_REF_ENTITY ];
						break;
						
					case UnitObject::kSEQ_NAME:
						$classes[ kTYPE_REF_SELF ] = $classes[ kTYPE_REF_UNIT ];
						break;
						
				} // Parsed by sequence number.
			
			} // Self reference.
		
			//
			// Check type.
			//
			if( ! array_key_exists( $type, $classes ) )
				return FALSE;														// ==>
		
			//
			// Handle objects.
			//
			if( is_object( $value ) )
			{
				//
				// Verify class.
				//
				if( ! ($value instanceof $classes[ $type ]) )
					throw new \Exception(
						"Invalid object reference in [$theOffset]: "
					   ."incorrect class object." );							// !@! ==>
			
				//
				// Commit object.
				//
				if( ! $value->isCommitted() )
					$id = $value->commit( $this->dictionary() );
			
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
				$this->addReferenceCount( $theRefs, $type, $id );
			
				return TRUE;														// ==>
		
			} // Property is an object.
		
			//
			// Resolve collection.
			//
			$class = $classes[ $type ];
			$collection
				= $class::ResolveCollection(
					$class::ResolveDatabase( $this->dictionary(), TRUE ) );
				
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
			
				default:
					return FALSE;													// ==>
		
			} // Parsed type.
		
			//
			// Resolve reference.
			//
			if( is_array( $value ) )
			{
				foreach( $value as $val )
				{
					if( ! $collection->matchOne( array( kTAG_NID => $val ), kQUERY_COUNT ) )
						throw new \Exception(
							"Unresolved reference in [$theOffset]: "
						   ."($val)." );										// !@! ==>
				}
			}
			elseif( ! $collection->matchOne( array( kTAG_NID => $value ), kQUERY_COUNT ) )
				throw new \Exception(
					"Unresolved reference in [$theOffset]: "
				   ."($value)." );												// !@! ==>
		
			//
			// Cast value.
			//
			$theIterator->offsetSet( $theIterator->key(), $value );
		
			//
			// Add reference count.
			//
			$this->addReferenceCount( $theRefs, $type, $value );
		
			return TRUE;															// ==>
		
		} // Single data type.
		
		return NULL;																// ==>
	
	} // verifyOffsetReference.

		

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
	 * @throws Exception
	 *
	 * @uses dictionary()
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
				$this->dictionary(), (string) $theCollection );
		
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
	 * </ul>
	 *
	 * The method assumes the current object has its {@link dictionary()} set.
	 *
	 * @param CollectionObject		$theCollection		Tags collection.
	 * @param int					$theTag				Tag sequence number.
	 * @param string				$theOffset			Tag offsets list offset.
	 * @param array					$theOffsets			Offsets list.
	 *
	 * @access protected
	 *
	 * @throws Exception
	 *
	 * @uses dictionary()
	 */
	protected function updateOffsetsSet( CollectionObject $theCollection,
														  $theTag, $theOffset, $theOffsets )
	{
		//
		// Add to set.
		//
		$theCollection->addToSet(
			(int) $theTag,			// Tag sequence number.
			kTAG_ID_SEQUENCE,		// Sequence number offset.
			$theOffset,				// Tag offsets list offset.
			$theOffsets );			// Offsets list.
	
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
	 * @uses dictionary()
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
			$theWrapper = $this->dictionary();
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

	 

} // class PersistentObject.


?>
