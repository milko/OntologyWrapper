<?php

/**
 * TemplateWorksheetsIterator.php
 *
 * This file contains the definition of the {@link TemplateWorksheetsIterator} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\ExcelTemplateParser;

/*=======================================================================================
 *																						*
 *							TemplateWorksheetsIterator.php								*
 *																						*
 *======================================================================================*/

/**
 * Worksheet iterator
 *
 * This class represents an iterator that can iterate a template set of worksheets.
 *
 * Data templates are represented by a collection of interconnected tables or worksheets,
 * each structured as a flat table and related to other tables or worksheets through a
 * matching column. This is necessary to represent nested documents with tabular data.
 *
 * The class can be used to traverse this structure in relationship order, from the root
 * node to the leaf nodes, this guarantees that any related field should have been loaded
 * beforehand.
 *
 * The class is instantiated with an {@link ExcelTemplateParser} instance that holds both
 * a data template and the template structure stored in the ontology. The structure is
 * implemented by two object members:
 *
 * <ul>
 *	<li><tt>{@link mList}</tt>: This member is an array holding the list of all worksheet
 *		and field pairs which will be referenced by the other member. it is an array
 *		structured as follows:
 *	  <ul>
 *		<li><em>index</em>: The index represents the node number of the field.
 *		<li><em>value</em>: The value is an array containing the names of the elements:
 *		  <ul>
 *			<li><tt>W</tt>: This item contains the worksheet name.
 *			<li><tt>K</tt>: This item contains the eventual worksheet key field name.
 *			<li><tt>F</tt>: This item contains the eventual name of the field that points
 *				to the parent worksheet.
 *		  </ul>
 *	  </ul>
 *	<li><tt>{@link mStruct}</tt>: This member is an array holding the tree structure that
 *		starts with the root element and ends with the leaf nodes:
 *	  <ul>
 *		<li><tt>N</tt>: The reference to the element, as the index to the <tt>mList</tt>
 *			data member.
 *		<li><tt>P</tt>: The index of the parent element, as the index to the <tt>mList</tt>
 *			data member.
 *		<li><tt>C</tt>: The list of eventual child elements structured as this element.
 *	  </ul>
 * </ul>
 *
 * The structure is populated at construction time and it features the Iterator interface
 * that allows traversing the structure from the root to the leaf nodes.
 *
 * The iterator interface starts with the children of the root node, which is not part of
 * the iteration process, that is because the unit worksheet is the only one that does not
 * reference another worksheet.
 *
 * Iterated elements are structured as the elements of the <tt>mList</tt> data member.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 17/03/2015
 */
class TemplateWorksheetsIterator implements \Iterator,
											\Countable
{
	/**
	 * Parser.
	 *
	 * This data member holds the <i>template parser</i> object.
	 *
	 * @var ExcelTemplateParser
	 */
	protected $mParser = NULL;

	/**
	 * List.
	 *
	 * This data member holds the <i>list of elements</i>.
	 *
	 * @var array
	 */
	protected $mList = Array();

	/**
	 * Structure.
	 *
	 * This data member holds the <i>structure of elements</i>.
	 *
	 * @var array
	 */
	protected $mStruct = Array();

	/**
	 * Cursor.
	 *
	 * This data member holds the <i>iterator cursor</i>.
	 *
	 * @var array
	 */
	protected $mCursor = Array();

		

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
	 * This class is instantiated by providing a template parsewr object,
	 *
	 * @param ExcelTemplateParser	$theParser			Template parser.
	 *
	 * @access public
	 */
	public function __construct( ExcelTemplateParser $theParser )
	{
		//
		// Set member.
		//
		$this->mParser = $theParser;
		
		//
		// Load structure.
		//
		$this->loadStructure();

	} // Constructor.

		

/*=======================================================================================
 *																						*
 *								MEMBER ACCESSOR INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getList																			*
	 *==================================================================================*/

	/**
	 * Return the elements list.
	 *
	 * The method will return the list of elements.
	 *
	 * @access public
	 * @return array				List of elements.
	 */
	public function getList()									{	return $this->mList;	}

	 
	/*===================================================================================
	 *	getStruct																		*
	 *==================================================================================*/

	/**
	 * Return the elements structure.
	 *
	 * The method will return the structure of elements.
	 *
	 * @access public
	 * @return array				Elements structure.
	 */
	public function getStruct()									{	return $this->mStruct;	}

		

/*=======================================================================================
 *																						*
 *									ITERATOR INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	rewind																			*
	 *==================================================================================*/

	/**
	 * Rewind the Iterator to the first element.
	 *
	 * The method will point to the root node's children.
	 *
	 * @param string			   &$theWorksheet		Receives worksheet.
	 * @param string			   &$theField			Receives field.
	 * @param int					$theIndex			Element index.
	 *
	 * @access public
	 */
	public function rewind()
	{
		//
		// Point to the first child.
		//
		if( array_key_exists( 'C', $this->mStruct ) )
		{
			$this->mCursor = Array();
			$this->mCursor[] = $this->mStruct[ 'C' ];
			reset( $this->mCursor[ count( $this->mCursor ) - 1 ] );
		}
		
		//
		// Handle childless root.
		//
		else
			$this->mCursor = NULL;
	
	} // rewind.

	 
	/*===================================================================================
	 *	current																			*
	 *==================================================================================*/

	/**
	 * Return the current element value.
	 *
	 * @access public
	 * @return array				<tt>W</tt> Worksheet, <tt>F</tt> Field.
	 */
	public function current()
	{
		if( is_array( $this->mCursor ) )
		{
			$current = Array();
			$key = $this->key();
			if( $key !== NULL )
			{
				$idxs = array( 'W', 'K', 'F' );
				foreach( $idxs as $idx )
				{
					if( array_key_exists( $idx, $this->mList[ $key ] ) )
						$current[ $idx ] = $this->mList[ $key ][ $idx ];
				}
				
				return $current;													// ==>
			}
		}
		
		return NULL;																// ==>
	
	} // current.

	 
	/*===================================================================================
	 *	parent																			*
	 *==================================================================================*/

	/**
	 * Return the current element value.
	 *
	 * @access public
	 * @return array				<tt>W</tt> Worksheet, <tt>F</tt> Field.
	 */
	public function parent()
	{
		if( $this->valid() )
		{
			if( is_array( $this->mCursor ) )
			{
				$key = $this->key();
				if( $key !== NULL )
				{
					$container = current( $this->mCursor[ count( $this->mCursor ) - 1 ] );
					if( array_key_exists( 'P', $container ) )
					{
						$current = Array();
						$idxs = array( 'W', 'K', 'F' );
						foreach( $idxs as $idx )
						{
							if( array_key_exists( $idx,
												  $this->mList[ $container[ 'P' ] ] ) )
								$current[ $idx ]
									= $this->mList[ $container[ 'P' ] ][ $idx ];
						}
				
						return $current;													// ==>
					}
				}
			}
		}
		
		return NULL;																// ==>
	
	} // parent.

	 
	/*===================================================================================
	 *	key																				*
	 *==================================================================================*/

	/**
	 * Return the key of the current element.
	 *
	 * @access public
	 * @return int					The node identifier of the current field.
	 */
	public function key()
	{
		if( is_array( $this->mCursor ) )
			return
				current( $this->mCursor[ count( $this->mCursor ) - 1 ] )
					[ 'N' ];														// ==>
		
		return NULL;																// ==>
	
	} // key.

	 
	/*===================================================================================
	 *	next																			*
	 *==================================================================================*/

	/**
	 * Move forward to next element.
	 *
	 * @access public
	 */
	public function next()
	{
		if( is_array( $this->mCursor ) )
		{
			$index = count( $this->mCursor ) - 1;
			if( array_key_exists( 'C', current( $this->mCursor[ $index ] ) ) )
			{
				$this->mCursor[] = current( $this->mCursor[ $index ] )[ 'C' ];
				reset( $this->mCursor[ $index + 1 ] );
			}
			elseif( next( $this->mCursor[ $index ] ) === FALSE )
			{
				while( array_pop( $this->mCursor ) !== NULL )
				{
					if( count( $this->mCursor )
					 && (next( $this->mCursor[ --$index ] ) !== FALSE) )
						break;												// =>
				}
			}
		}
	
	} // next.

	 
	/*===================================================================================
	 *	valid																			*
	 *==================================================================================*/

	/**
	 * Check if the current position is valid.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> current position is valid.
	 */
	public function valid()
	{
		return is_array( $this->mCursor ) && count( $this->mCursor );				// ==>
	
	} // valid.

		

/*=======================================================================================
 *																						*
 *									COUNTABLE INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	count																			*
	 *==================================================================================*/

	/**
	 * Return element count.
	 *
	 * @access public
	 * @return int					Number of elements.
	 */
	public function count()							{	return count( $this->mList ) - 1;	}

		

/*=======================================================================================
 *																						*
 *									PUBLIC INTERFACE									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getRoot																			*
	 *==================================================================================*/

	/**
	 * Retrieve root element
	 *
	 * This method can be used to retrieve the root element.
	 *
	 * The result will be an array with <tt>W</tt> holding the worksheet name, <tt>F</tt>
	 * holding the field symbol and <tt>N</tt> holding the node identifier.
	 *
	 * @param int					$theIndex			Element index.
	 *
	 * @access public
	 * @return array				Worksheet, field and parent.
	 */
	public function getRoot()
	{
		$current = Array();
		$idxs = array( 'W', 'K', 'F' );
		foreach( $idxs as $idx )
		{
			if( array_key_exists( $idx, $this->mList[ $this->mStruct[ 'N' ] ] ) )
				$current[ $idx ] = $this->mList[ $this->mStruct[ 'N' ] ][ $idx ];
		}

		return $current;													// ==>
	
	} // getRoot.

		

/*=======================================================================================
 *																						*
 *							PROTECTED INITIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadStructure																	*
	 *==================================================================================*/

	/**
	 * Load structure
	 *
	 * This method will use the parser member to build the relationship structure.
	 *
	 * @access protected
	 */
	protected function loadStructure()
	{
		//
		// Init local storage.
		//
		$this->mList = $this->mStruct = Array();
		$indexes = $this->mParser->getWorksheetIndexes();
		
		//
		// Get field node identifiers.
		//
		$relationships = Array();
		foreach( $this->mParser->getWorksheetRelationships() as $worksheet => $fields )
		{
			//
			// Set list element.
			//
			$this->mList[ $indexes[ $worksheet ] ]
				= array( 'W' => $this->mParser->matchNodeSymbol( $worksheet ),
						 'K' => $this->mParser->matchNodeSymbol( $indexes[ $worksheet ] ) );
			
			//
			// Set worksheet relationships.
			//
			$relationships[ $indexes[ $worksheet ] ] = Array();
			foreach( $fields as $field )
			{
				//
				// Set list element.
				//
				$this->mList[ $field ]
					= array( 'W' => $this->mParser
										->matchNodeSymbol(
											$this->mParser->getFieldWorksheet( $field ) ),
							 'F' => $this->mParser
							 			->matchNodeSymbol(
							 				$field ) );
				
				//
				// Set Worksheet relationship.
				//
				$relationships[ $indexes[ $worksheet ] ][ $field ] = $field;
			}
		}
		
		//
		// Build structure.
		//
		$wkeys = array_keys( $relationships );
		foreach( $wkeys as $wkey )
		{
			if( array_key_exists( $wkey, $relationships ) )
			{
				$fkeys = array_keys( $relationships[ $wkey ] );
				foreach( $fkeys as $fkey )
				{
					if( array_key_exists( $fkey, $relationships ) )
					{
						$relationships[ $wkey ][ $fkey ] = $relationships[ $fkey ];
						unset( $relationships[ $fkey ] );
					}
				}
			}
		}
		
		//
		// Load structure.
		//
		reset( $relationships );
		foreach( $relationships as $worksheet => $fields )
			$this->loadWorksheetRelationship( $this->mStruct, $worksheet, $fields );
	
	} // loadStructure.

		

/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadWorksheetRelationship														*
	 *==================================================================================*/

	/**
	 * Load worksheet relationship
	 *
	 * This method will add to the provided container the provided worksheet, fields and
	 * parent.
	 *
	 * @param array				   &$theContainer		Structure container.
	 * @param int					$theWorksheet		Worksheet node identifier.
	 * @param array					$theFields			Related fields.
	 * @param int					$theParent			Parent field node identifier.
	 *
	 * @access protected
	 */
	protected function loadWorksheetRelationship( &$theContainer,
												   $theWorksheet,
												   $theFields,
												   $theParent = NULL )
	{
		//
		// Set worksheet reference.
		//
		$theContainer[ 'N' ] = $theWorksheet;
		if( $theParent !== NULL )
		{
			$theContainer[ 'P' ] = $theParent;
			$this->mList[ $theWorksheet ][ 'P' ] = $theParent;
		}
		
		//
		// Handle related fields.
		//
		if( is_array( $theFields ) )
		{
			//
			// Allocate related fields container.
			//
			if( ! array_key_exists( 'C', $theContainer ) )
				$theContainer[ 'C' ] = Array();
			
			//
			// Iterate related fields.
			//
			$reference = & $theContainer[ 'C' ];
			foreach( $theFields as $field => $children )
			{
				//
				// Allocate related field container.
				//
				$index = count( $reference );
				$reference[ $index ] = Array();
				
				//
				// Recurse.
				//
				$this->loadWorksheetRelationship(
					$reference[ $index ],
					$field,
					$children,
					$theContainer[ 'N' ] );
			
			} // Iterating related fields.
		
		} // Has related fields.
	
	} // loadWorksheetRelationship.

	 

} // class TemplateWorksheetsIterator.


?>
