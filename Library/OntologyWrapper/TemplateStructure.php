<?php

/**
 * TemplateStructure.php
 *
 * This file contains the definition of the {@link TemplateStructure} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\CachedStructure;

/*=======================================================================================
 *																						*
 *									TemplateStructure.php								*
 *																						*
 *======================================================================================*/

/**
 * Template structure
 *
 * This class implements a concrete {@link CachedStructure} instance that is specialised in
 * traversing template structures.
 *
 * This class may only be instantiated with a root template node (type kTYPE_NODE_ROOT and
 * kTYPE_NODE_TEMPLATE).
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 10/02/2015
 */
class TemplateStructure extends CachedStructure
{
	/**
	 * Worksheets.
	 *
	 * This data member holds the list of worksheets structured as follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The worksheet node identifier.
	 *	<li><tt>value</tt>: The list of worksheet properties.
	 * </ul>
	 *
	 * @var array
	 */
	 protected $mWorksheets = Array();

	/**
	 * Unit worksheets.
	 *
	 * This data member holds the list of unit worksheets.
	 *
	 * @var array
	 */
	 protected $mUnitWorksheets = Array();

	/**
	 * Required worksheets.
	 *
	 * This data member holds the list of required worksheets.
	 *
	 * @var array
	 */
	 protected $mRequiredWorksheets = Array();

	/**
	 * Worksheet indexes.
	 *
	 * This data member holds the list of worksheet indexes structured as follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The worksheet node identifier.
	 *	<li><tt>value</tt>: The index property.
	 * </ul>
	 *
	 * All references are node native identifiers.
	 *
	 * @var array
	 */
	 protected $mWorksheetIndexes = Array();

	/**
	 * Index references.
	 *
	 * This data member holds the list of index properties structured as follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The worksheet node identifier.
	 *	<li><tt>value</tt>: The list of properties referencing that worksheet (including
	 *		only properties of other worksheets).
	 * </ul>
	 *
	 * It is assumed that the property is referencing the index property of the worksheet.
	 *
	 * All references are node native identifiers.
	 *
	 * @var array
	 */
	 protected $mIndexReferences = Array();

	/**
	 * Symbol nodes.
	 *
	 * This data member holds an array used to resolve symbol nodes, structured as follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The symbol.
	 *	<li><tt>value</tt>: A list of node references.
	 * </ul>
	 *
	 * All node references are node native identifiers.
	 *
	 * @var array
	 */
	 protected $mSymbolNodes = Array();

	/**
	 * Node Symbols.
	 *
	 * This data member holds an array used to resolve node symbols, structured as follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The node.
	 *	<li><tt>value</tt>: The symbol.
	 * </ul>
	 *
	 * All node references are node native identifiers.
	 *
	 * @var array
	 */
	 protected $mNodeSymbols = Array();

		

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
	 * We overload the constructor to ensure the root node is a root template and to load
	 * all worksheets.
	 *
	 * @param Wrapper				$theWrapper			Database wrapper.
	 * @param mixed					$theIdentifier		Root node identifier or object.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access public
	 *
	 * @throws Exception
	 */
	public function __construct( Wrapper $theWrapper, $theRoot, $theLanguage = NULL )
	{
		//
		// Call parent instantiator.
		//
		parent::__construct( $theWrapper, $theRoot, $theLanguage );
		
		//
		// Check root.
		//
		$root = $this->getRoot();
		$type = $root->offsetGet( kTAG_NODE_TYPE );
		if( ($type === NULL)
		 || (! in_array( kTYPE_NODE_ROOT, $type ))
		 || (! in_array( kTYPE_NODE_TEMPLATE, $type )) )
			throw new \Exception(
				"Unable to instantiate object: "
			   ."expecting a root template node." );							// !@! ==>
		
		//
		// Load worksheets.
		//
		$this->loadWorksheets();
		
		//
		// Load unit worksheets.
		//
		$this->loadUnitWorksheets();
		
		//
		// Load property symbols.
		//
		$this->loadPropertySymbols();
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getWorksheets																	*
	 *==================================================================================*/

	/**
	 * Get worksheets
	 *
	 * This method will return the worksheets.
	 *
	 * @access public
	 * @return array				Worksheets.
	 */
	public function getWorksheets()							{	return $this->mWorksheets;	}

	 
	/*===================================================================================
	 *	getUnitWorksheets																*
	 *==================================================================================*/

	/**
	 * Get unit worksheets
	 *
	 * This method will return the unit worksheets.
	 *
	 * @access public
	 * @return array				Unit worksheets.
	 */
	public function getUnitWorksheets()					{	return $this->mUnitWorksheets;	}

	 
	/*===================================================================================
	 *	getRequiredWorksheets															*
	 *==================================================================================*/

	/**
	 * Get required worksheets
	 *
	 * This method will return the required worksheets, unit worksheets are assumed to be
	 * required.
	 *
	 * @access public
	 * @return array				Required worksheets.
	 */
	public function getRequiredWorksheets()			{	return $this->mRequiredWorksheets;	}

	 
	/*===================================================================================
	 *	getWorksheetIndexes																*
	 *==================================================================================*/

	/**
	 * Get worksheet indexes
	 *
	 * This method will return the worksheet indexes as an array structured as follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The worksheet node identifier.
	 *	<li><tt>value</tt>: The index property (as node identifier).
	 * </ul>
	 *
	 * @access public
	 * @return array				Worksheet indexes.
	 */
	public function getWorksheetIndexes()			{	return $this->mWorksheetIndexes;	}

	 
	/*===================================================================================
	 *	getWorksheetIndexReferences														*
	 *==================================================================================*/

	/**
	 * Get worksheet index references
	 *
	 * This method will return the worksheet index references as an array structured as
	 * follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The worksheet node identifier.
	 *	<li><tt>value</tt>: The list of properties referencing the worksheet, excluding the
	 *		worksheet own properties.
	 * </ul>
	 *
	 * @access public
	 * @return array				Worksheet index references.
	 */
	public function getWorksheetIndexReferences()		{	return $this->mIndexReferences;	}

	 
	/*===================================================================================
	 *	getSymbolNodes																	*
	 *==================================================================================*/

	/**
	 * Get symbol node references
	 *
	 * This method will return the symbol nodes references as an array structured as
	 * follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The symbol.
	 *	<li><tt>value</tt>: A list of node references.
	 * </ul>
	 *
	 * @access public
	 * @return array				Symbol nodes references.
	 */
	public function getSymbolNodes()						{	return $this->mSymbolNodes;	}

	 
	/*===================================================================================
	 *	getNodeSymbols																	*
	 *==================================================================================*/

	/**
	 * Get node symbol
	 *
	 * This method will return the node symbols as an array structured as follows:
	 *
	 * <ul>
	 *	<li><tt>index</tt>: The node.
	 *	<li><tt>value</tt>: The symbol.
	 * </ul>
	 *
	 * @access public
	 * @return array				Symbol nodes references.
	 */
	public function getNodeSymbols()						{	return $this->mNodeSymbols;	}

		

/*=======================================================================================
 *																						*
 *							PUBLIC STRUCTURE ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	matchSymbolNodes																*
	 *==================================================================================*/

	/**
	 * Get symbol nodes
	 *
	 * This method will return the list of nodes associated with the provided symbol as an
	 * array, if the symbol is not matched, the method will return an empty array.
	 *
	 * @param string				$theSymbol			Symbol.
	 *
	 * @access public
	 * @return array				Symbol nodes.
	 */
	public function matchSymbolNodes( $theSymbol )
	{
		if( array_key_exists( $theSymbol, $this->mSymbolNodes ) )
			return $this->mSymbolNodes[ $theSymbol ];								// ==>
		
		return Array();																// ==>
	
	} // matchSymbolNodes.

	 
	/*===================================================================================
	 *	matchNodeSymbol																	*
	 *==================================================================================*/

	/**
	 * Get node symbol
	 *
	 * This method will return the symbol associated with the provided node, if the node is
	 * not matched, the method will return <tt>NULL</tt>.
	 *
	 * This mmethod can also be used to extract the symbol from the provided node object,
	 * in this case the local array member will not be checked.
	 *
	 * @param int					$theNode			Node reference or object.
	 *
	 * @access public
	 * @return string				Node symbol.
	 */
	public function matchNodeSymbol( $theNode )
	{
		if( $theNode instanceof Node )
			return $theNode->offsetGet( kTAG_ID_SYMBOL );							// ==>
		
		if( array_key_exists( $theNode, $this->mNodeSymbols ) )
			return $this->mNodeSymbols[ $theNode ];									// ==>
		
		return NULL;																// ==>
	
	} // matchSymbolNodes.

		

/*=======================================================================================
 *																						*
 *								PROTECTED STRUCTURE INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadWorksheets																	*
	 *==================================================================================*/

	/**
	 * Load worksheets
	 *
	 * This method will load all worksheets, including nested worksheets.
	 *
	 * The method will also perform a series of validation checks:
	 *
	 * <ul>
	 *	<li>All worksheets must have a symbol, <tt>{@link kTAG_ID_SYMBOL}</tt>,
	 * </ul>
	 *
	 * @access protected
	 */
	protected function loadWorksheets()
	{
		//
		// Init members.
		//
		$this->mWorksheets =
		$this->mSymbolNodes =
		$this->mNodeSymbols =
		$this->mIndexReferences = 
		$this->mWorksheetIndexes =
		$this->mRequiredWorksheets = Array();
		
		//
		// Load worksheets.
		//
		$worksheets
			= $this->getRelationships(
				$this->mRoot, 'i', kPREDICATE_COLLECTION_OF );
		
		//
		// Select worksheets.
		//
		$worksheets = ( array_key_exists( kPREDICATE_COLLECTION_OF, $worksheets ) )
					? $worksheets[ kPREDICATE_COLLECTION_OF ]
					: Array();
		
		//
		// Recurse worksheets.
		//
		while( ($worksheet = array_shift( $worksheets )) !== NULL )
		{
			//
			// Handle required worksheet.
			//
			$tmp = $this->getNode( $worksheet );
			if( $tmp->offsetExists( kTAG_DATA_KIND ) )
			{
				if( in_array( kTYPE_MANDATORY, $tmp->offsetGet( kTAG_DATA_KIND ) ) )
				{
					if( ! in_array( $worksheet, $this->mRequiredWorksheets ) )
						$this->mRequiredWorksheets[]
							= $worksheet;
				}
			}
			
			//
			// Update symbol dictionaries.
			//
			$symbol = $this->matchNodeSymbol( $tmp );
			if( $tmp !== NULL )
			{
				if( ! array_key_exists( $symbol, $this->mSymbolNodes ) )
					$this->mSymbolNodes[ $symbol ] = Array();
				if( ! in_array( $worksheet, $this->mSymbolNodes[ $symbol ] ) )
					$this->mSymbolNodes[ $symbol ][] = $worksheet;
				
				$this->mNodeSymbols[ $worksheet ] = $symbol;
			}
			else
				throw new \Exception(
					"Invalid template structure: "
				   ."worksheet [$worksheet] is missing its symbol" );			// !@! ==>
			
			//
			// Get properties.
			//
			$result
				= $this->getRelationships(
					$worksheet, 'i', kPREDICATE_PROPERTY_OF );
			
			//
			// Load worksheet.
			//
			$this->mWorksheets[ $worksheet ]
				= ( array_key_exists( kPREDICATE_PROPERTY_OF, $result ) )
				? $result[ kPREDICATE_PROPERTY_OF ]
				: Array();
			
			//
			// Get worksheet index.
			//
			$result
				= $this->getRelationships(
					$worksheet, 'o', kPREDICATE_INDEX );
			
			//
			// Load worksheet index.
			//
			if( array_key_exists( kPREDICATE_INDEX, $result ) )
				$this->mWorksheetIndexes[ $worksheet ]
					= $result[ kPREDICATE_INDEX ][ 0 ];
			
			//
			// Get worksheet index references.
			//
			$result
				= $this->getRelationships(
					$worksheet, 'i', kPREDICATE_INDEX );
			
			//
			// Load worksheet index references.
			//
			if( array_key_exists( kPREDICATE_INDEX, $result ) )
				$this->mIndexReferences[ $worksheet ]
					= $result[ kPREDICATE_INDEX ];
			
			//
			// Load nested worksheets.
			//
			$result
				= $this->getRelationships(
					$worksheet, 'i', kPREDICATE_COLLECTION_OF );
			
			//
			// Add nested worksheets.
			//
			if( array_key_exists( kPREDICATE_COLLECTION_OF, $result )
			 && count( $result[ kPREDICATE_COLLECTION_OF ] ) )
			{
				foreach( $result[ kPREDICATE_COLLECTION_OF ] as $tmp )
				{
					if( ! in_array( $tmp, $worksheets ) )
						$worksheets[] = $tmp;
				}
			}
		
		} // Traversing worksheets.
	
	} // loadWorksheets.

	 
	/*===================================================================================
	 *	loadUnitWorksheets																*
	 *==================================================================================*/

	/**
	 * Load unit worksheets
	 *
	 * This method will load all unit worksheets.
	 *
	 * @access protected
	 */
	protected function loadUnitWorksheets()
	{
		//
		// Init member.
		//
		$this->mUnitWorksheets = Array();
		
		//
		// Load worksheets.
		//
		$worksheets
			= $this->getRelationships(
				$this->mRoot, 'o', kPREDICATE_UNIT );
		
		//
		// Select worksheets.
		//
		$this->mUnitWorksheets
			= ( array_key_exists( kPREDICATE_UNIT, $worksheets ) )
			? $worksheets[ kPREDICATE_UNIT ]
			: Array();
	
	} // loadUnitWorksheets.

	 
	/*===================================================================================
	 *	loadPropertySymbols																*
	 *==================================================================================*/

	/**
	 * Load property symbols
	 *
	 * This method will load all property symbols.
	 *
	 * @access protected
	 */
	protected function loadPropertySymbols()
	{
		//
		// Iterate worksheets.
		//
		foreach( $this->mWorksheets as $worksheet => $properties )
		{
			//
			// Iterate properties.
			//
			foreach( $properties as $property )
			{
				//
				// Init local storage.
				//
				$node = $this->getNode( $property );
				
				//
				// Get symbol.
				//
				if( $node->offsetExists( kTAG_ID_SYMBOL ) )
				{
					$symbol = $node->offsetGet( kTAG_ID_SYMBOL );

					if( ! array_key_exists( $symbol, $this->mSymbolNodes ) )
						$this->mSymbolNodes[ $symbol ] = Array();
					if( ! in_array( $property, $this->mSymbolNodes[ $symbol ] ) )
						$this->mSymbolNodes[ $symbol ][] = $property;
				
					$this->mNodeSymbols[ $property ] = $symbol;
				}
				else
					throw new \Exception(
						"Invalid template structure: "
					   ."property [$property] is missing its symbol" );			// !@! ==>
			}
		}
	
	} // loadPropertySymbols.

	 

} // class TemplateStructure.


?>
