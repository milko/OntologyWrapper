<?php

namespace OntologyWrapper;

/*=======================================================================================
 *																						*
 *									OntologyObject.php									*
 *																						*
 *======================================================================================*/

/**
 * Ontology object
 *
 * The main purpose of this class is to match all offsets to an ontology domain, ensuring
 * that any value held by the embedded object array resolves into an ontology element.
 *
 * The unique identifier of an object is represented by the <i>native identifier offset</i>,
 * <b><tt>{@link kTAG_NID}</tt></b>, which is the only alphabetic offset allowed in this
 * class, all other offsets <i>must</i> be numeric, equivalent to an integer. This is
 * because these numeric constants refer to {@link Tag} instances which define and
 * document the object's data values.
 *
 * It is possible to set an offset by providing a string value: in that case the provided
 * value is interpreted as the tag global identifier which must be resolved into the tag's
 * native identifier.
 *
 * No offset may hold the <tt>NULL</tt> value, setting an offset with this value is
 * equivalent to deleting the offset.
 *
 * Requesting an inexistant offset will not trigger a warning, instead, the <tt>NULL</tt>
 * value will be returned, an indication that the offset doesn't exist.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/01/2014
 */
class OntologyObject extends DocumentObject
{
	/**
	 * Tag cache
	 *
	 * This data member holds the tag cache.
	 *
	 * @var Memcached
	 */
	static $sTagCache		= NULL;

	/**
	 * Native identifier
	 *
	 * This data member holds the tag cache.
	 *
	 * @var string
	 */
	const kTAG_NID = 		'_id';

		

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
	 * We overload the parent constructor to initialise the tag cache by loading all tags.
	 *
	 * @param mixed					$theInput			Object data.
	 * @param int					$theFlags			Object flags.
	 * @param string				$theIterator		Object iterator class name.
	 *
	 * @access public
	 */
	public function __construct( $theInput = [],
								 $theFlags = 0,
								 $theIterator = "ArrayIterator" )
	{
		//
		// Init tag cache.
		//
		if( self::$sTagCache === NULL )
		{
			//
			// Instantiate cache.
			//
			self::$sTagCache = new \Memcached( "Persistent ID" );
			if( ! self::$sTagCache->getServerList() )
			{
				if( self::$sTagCache->addServer('localhost', 11211) === FALSE )
					throw new \Exception(
						"Unable to initialise tag cache",
						self::$sTagCache->getResultCode() );					// !@! ==>
			}
		
		} // Cache not yet initialised.
		
		parent::__construct( $theInput, $theFlags, $theIterator );

	} // Constructor.

	 

} // class OntologyObject.


?>
