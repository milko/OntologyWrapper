<?php

/**
 * User.php
 *
 * This file contains the definition of the {@link User} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Individual;

/*=======================================================================================
 *																						*
 *										User.php										*
 *																						*
 *======================================================================================*/

/**
 * User
 *
 * This <em>concrete</em> class is derived from the {@link Individual} class, it implements
 * an <em>individual</em> or <em>person</em> which is a user of the system.
 *
 * The class adds a series of default attributes which characterise users:
 *
 * <ul>
 *	<li><tt>{@link kTAG_CONN_USER}</tt>: <em>User code</em>. This optional attribute can be
 *		set if the individual is also a user of the system, in that case this attribute can
 *		hold the user code credentials.
 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: <em>User password</em>. This optional attribute can
 *		be set if the individual is also a user of the system, in that case this attribute
 *		can hold the user password credentials.
 * </ul>
 *
 * The above two properties are required.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 28/05/2014
 */
class User extends Individual
{
	/**
	 * Sequences selector.
	 *
	 * This constant holds the <i>default collection</i> name for entities.
	 *
	 * @var string
	 */
	const kSEQ_NAME = '_entities';

	/**
	 * Default domain.
	 *
	 * This constant holds the <i>default domain</i> of the object.
	 *
	 * @var string
	 */
	const kDEFAULT_DOMAIN = kDOMAIN_INDIVIDUAL;

		

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
	 * In this class we index the following offsets:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_USER}</tt>: User code.
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: User password.
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
		// Set parent indexes and retrieve collection.
		//
		$collection = parent::CreateIndexes( $theDatabase );
		
		//
		// Set first name index.
		//
		$collection->createIndex( array( kTAG_CONN_USER => 1 ),
								  array( "name" => "USER_CODE" ) );
		
		//
		// Set last name index.
		//
		$collection->createIndex( array( kTAG_CONN_PASS => 1 ),
								  array( "name" => "USER_PASS" ) );
		
		return $collection;															// ==>
	
	} // CreateIndexes.

		

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
	 * In this class we return:
	 *
	 * <ul>
	 *	<li><tt>{@link kTAG_CONN_USER}</tt>: User code.
	 *	<li><tt>{@link kTAG_CONN_PASS}</tt>: User password.
	 * </ul>
	 *
	 * @static
	 * @return array				List of default offsets.
	 */
	static function DefaultOffsets()
	{
		return array_merge( parent::DefaultOffsets(),
							array( kTAG_CONN_USER, kTAG_CONN_PASS ) );				// ==>
	
	} // DefaultOffsets.

		

/*=======================================================================================
 *																						*
 *								STATIC EXPORT INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	XMLRootElement																	*
	 *==================================================================================*/

	/**
	 * Return XML root element
	 *
	 * In this class we return the <tt>USERS</tt> root element.
	 *
	 * @static
	 * @return SimpleXMLElement		XML export root element.
	 */
	static function XMLRootElement()
	{
		return new \SimpleXMLElement(
						str_replace(
							'@@@', kIO_XML_USERS, kXML_STANDARDS_BASE ) );			// ==>
	
	} // XMLRootElement.

		

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
	 * In this class we link the inited status with the presence of the user code and pass.
	 *
	 * @param reference				$theOffset			Offset reference.
	 * @param reference				$theValue			Offset value reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_CONN_USER kTAG_CONN_PASS
	 *
	 * @uses TermCount()
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_CONN_USER ) &&
						 \ArrayObject::offsetExists( kTAG_CONN_PASS ) );
	
	} // postOffsetSet.

	 
	/*===================================================================================
	 *	postOffsetUnset																	*
	 *==================================================================================*/

	/**
	 * Handle offset after deleting it
	 *
	 * In this class we link the inited status with the presence of the user code and pass.
	 *
	 * @param reference				$theOffset			Offset reference.
	 *
	 * @access protected
	 *
	 * @see kTAG_CONN_USER kTAG_CONN_PASS
	 *
	 * @uses TermCount()
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
		$this->isInited( \ArrayObject::offsetExists( kTAG_CONN_USER ) &&
						 \ArrayObject::offsetExists( kTAG_CONN_PASS ) );
	
	} // postOffsetUnset.

		

/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT UTILITIES								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	xmlUnitElement																	*
	 *==================================================================================*/

	/**
	 * Return XML unit element
	 *
	 * In this class we return the <tt>USER</tt> element.
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	protected function xmlUnitElement( \SimpleXMLElement $theRoot )
	{
		return $theRoot->addChild( kIO_XML_TRANS_USERS );							// ==>
	
	} // xmlUnitElement.

	 

} // class User.


?>
