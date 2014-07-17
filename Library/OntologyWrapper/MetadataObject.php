<?php

/**
 * MetadataObject.php
 *
 * This file contains the definition of the {@link MetadataObject} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Wrapper;
use OntologyWrapper\PersistentObject;

/*=======================================================================================
 *																						*
 *								MetadataObject.php										*
 *																						*
 *======================================================================================*/

/**
 * Metadata object
 *
 * This <i>abstract</i> class is the ancestor of all classes representing metadata objects
 * that can persist in a container and that are constituted by ontology offsets.
 *
 * The main purpose of this class is to implement abstract methods common to all metadata
 * objects.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 17/07/2014
 */
abstract class MetadataObject extends PersistentObject
{
		

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
	 * In this class we return the <tt>METADATA</tt> root element.
	 *
	 * @static
	 * @return SimpleXMLElement		XML export root element.
	 */
	static function XMLRootElement()
	{
		return new \SimpleXMLElement(
						str_replace( '@@@', 'METADATA', kXML_STANDARDS_BASE ) );	// ==>
	
	} // XMLRootElement.

		

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
	 * In this class we overload the inherited method to enclose the current object in a
	 * transaction element.
	 *
	 * <em>This method will not set and return the unit element, this is the responsibility
	 * of derived concrete classes: the method will return either the transaction element,
	 * or the provided root element if it represents a transaction element.</em>
	 *
	 * @param SimpleXMLElement		$theRoot			Root container.
	 *
	 * @access protected
	 * @return SimpleXMLElement		XML export unit element.
	 */
	protected function xmlUnitElement( \SimpleXMLElement $theRoot )
	{
		//
		// Handle transaction element.
		//
		if( $theRoot->getName() == 'META' )
			return $theRoot;														// ==>
		
		return $theRoot->addChild( 'META' );										// ==>
	
	} // xmlUnitElement.

	 

} // class MetadataObject.


?>
