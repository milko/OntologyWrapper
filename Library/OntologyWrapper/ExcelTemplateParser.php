<?php

/**
 * ExcelTemplateParser.php
 *
 * This file contains the definition of the {@link ExcelTemplateParser} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\Session;
use OntologyWrapper\Transaction;
use OntologyWrapper\ExcelParser;
use OntologyWrapper\TemplateStructure;

/*=======================================================================================
 *																						*
 *								ExcelTemplateParser.php									*
 *																						*
 *======================================================================================*/

/**
 * Types.
 *
 * This file contains the default Excel library definitions.
 */
//require_once( kPATH_DEFINITIONS_ROOT."/types.inc.php" );

/**
 * Excel template parser object
 *
 * This class can be used to extract and manipulate data from an excel template file, it
 * makes use of an {@link ExcelParser} instance member to parse and manipulate the Excel
 * file and of a {@link TemplateStructure} instance member to match and verify the Excel
 * file elements to the template structure in the ontology.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 06/03/2015
 */
class ExcelTemplateParser
{
	/**
	 * Data wrapper.
	 *
	 * This data member holds the <i>data wrapper</i> object.
	 *
	 * @var Wrapper
	 */
	protected $mWrapper = NULL;

	/**
	 * Excel file.
	 *
	 * This data member holds the <i>Excel file</i> object.
	 *
	 * @var ExcelParser
	 */
	protected $mFile = NULL;

	/**
	 * Template structure.
	 *
	 * This data member holds the <i>template structure</i> object.
	 *
	 * @var TemplateStructure
	 */
	protected $mTemplate = NULL;

	/**
	 * Worksheets.
	 *
	 * This data member holds the <i>template worksheets</i> matching the template strucure,
	 * it is an array structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet name.
	 *	<li><em>value</em>: An array structured as follows:
	 *	  <ul>
	 *		<li><tt>title</tt>: The worksheet title or name.
	 *		<li><tt>last_row</tt>: The worksheet's last row number.
	 *		<li><tt>last_column</tt>: The worksheet's last column name.
	 *		<li><tt>last_column_index</tt>: The worksheet's last column index.
	 *		<li><tt>symbol_row</tt>: The worksheet's symbol row.
	 *		<li><tt>data_row</tt>: The worksheet's data row.
	 *		<li><tt>node</tt>: The worksheet's node reference.
	 *	  </ul>
	 * </ul>
	 *
	 * @var array
	 */
	protected $mWorksheets = NULL;

	/**
	 * Fields.
	 *
	 * This data member holds the <i>template worksheet fields</i> matching the template
	 * structure, it is an array structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet name.
	 *	<li><em>value</em>: An array structured as follows:
	 *	  <ul>
	 *		<li><em>index</em>: The field symbol.
	 *		<li><em>value</em>: An array structured as follows:
	 *		  <ul>
	 *			<li><tt>column_name</tt>: The column name.
	 *			<li><tt>column_number</tt>: The column number.
	 *			<li><tt>node</tt>: The worksheet's node reference.
	 *		  </ul>
	 *	  </ul>
	 * </ul>
	 *
	 * @var array
	 */
	protected $mFields = NULL;

	/**
	 * Required fields.
	 *
	 * This data member holds the <i>list of required fields</i> by worksheet, it is an
	 * array structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet name.
	 *	<li><em>value</em>: The list of required field symbols.
	 * </ul>
	 *
	 * @var array
	 */
	protected $mRequiredFields = NULL;

		

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
	 * This class is instantiated by providing the Excel template file reference as a path
	 * or SplFileInfo object and the data wrapper.
	 *
	 * @param Wrapper				$theWrapper			Data wrapper.
	 * @param mixed					$theFile			Excel file reference.
	 *
	 * @access public
	 */
	public function __construct( Wrapper $theWrapper, $theFile )
	{
		//
		// Set data wrapper.
		//
		$this->mWrapper = $theWrapper;
		
		//
		// Instantiate Excel file object.
		//
		$this->mFile = new ExcelParser( $theFile );

	} // Constructor.

	

/*=======================================================================================
 *																						*
 *							PUBLIC INITIALISATION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	loadStructure																	*
	 *==================================================================================*/

	/**
	 * Load template structure
	 *
	 * This method will load the template structure identified by the PID custom property
	 * of the Excel template file.
	 *
	 * The file PID corresponds to the structure root node persistent identifier.
	 *
	 * If the operation was successful, the method will return <tt>TRUE</tt>, if not, it
	 * will update the provided transaction and return <tt>FALSE</tt>.
	 *
	 * @param Transaction			$theTransaction		Current transaction.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	public function loadStructure( Transaction $theTransaction )
	{
		//
		// Get Excel file PID.
		//
		$pid = $this->mFile->getPID();
		if( $pid !== NULL )
		{
			//
			// Get root node identifier.
			//
			if( Node::GetPidNode( $this->mWrapper, $pid, kQUERY_NID ) !== NULL )
			{
				//
				// Load template structure.
				//
				$this->mTemplate
					= new TemplateStructure(
						$this->mWrapper, $pid, kSTANDARDS_LANGUAGE );
				
				//
				// Load worksheets.
				//
				$this->setWorksheets();
				
				//
				// Load worksheet fields.
				//
				$this->setFields();
				
				//
				// Load required fields.
				//
				$this->setRequiredFields();
				
				return TRUE;														// ==>
			
			} // Matched root.
			
			//
			// Set message.
			//
			$message = 'The template type is incorrect or unsupported.';
		
			//
			// Update transaction status.
			//
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
		
			//
			// Set transaction log.
			//
			$theTransaction->setLog(
				kTYPE_STATUS_FATAL,					// Transaction status.
				NULL,								// Alias.
				NULL,								// Row.
				NULL,								// Value.
				$message,							// Transaction message.
				NULL,								// Tag.
				kTYPE_ERROR_BAD_TMPL_STRUCT,		// Error type.
				kTYPE_ERROR_CODE_BAD_PID,			// Error code.
				NULL );								// Error resource.
	
			//
			// Close transaction.
			//
			$theTransaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
			return FALSE;															// ==>
		
		} // Has PID.
		
		//
		// Set message.
		//
		$message = 'The template file is missing its "PID" custom property: '
				  .'unable to determine the kind of template.';
	
		//
		// Update transaction status.
		//
		$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
	
		//
		// Set transaction log.
		//
		$theTransaction->setLog(
			kTYPE_STATUS_FATAL,					// Transaction status.
			NULL,								// Alias.
			NULL,								// Row.
			NULL,								// Value.
			$message,							// Transaction message.
			NULL,								// Tag.
			kTYPE_ERROR_BAD_TMPL_STRUCT,		// Error type.
			kTYPE_ERROR_CODE_NO_PID,			// Error code.
			NULL );								// Error resource.

		//
		// Close transaction.
		//
		$theTransaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
	
		return FALSE;															// ==>
	
	} // loadStructure.

	

/*=======================================================================================
 *																						*
 *								PUBLIC VALIDATION INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	checkRequiredWorksheets															*
	 *==================================================================================*/

	/**
	 * Check required worksheets
	 *
	 * This method will check whether all required worksheets are included in the template.
	 *
	 * If the operation was successful, the method will return <tt>TRUE</tt>, if not, it
	 * will update the provided transaction and return <tt>FALSE</tt>.
	 *
	 * It is assumed that the {@link loadStructure()} method has been called beforehand.
	 *
	 * @param Transaction			$theTransaction		Current transaction.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	public function checkRequiredWorksheets( Transaction $theTransaction )
	{
		//
		// Init local storage.
		//
		$result = Array();
		
		//
		// Iterate required worksheets.
		//
		foreach( $this->mTemplate->getRequiredWorksheets() as $node )
		{
			//
			// Match worksheet.
			//
			$name = $this->mTemplate->getNodeSymbols()[ $node ];
			if( ! array_key_exists( $name, $this->mWorksheets ) )
				$result[] = $name;
		}
		
		//
		// Handle missing.
		//
		if( count( $result ) )
		{
			//
			// Init local storage.
			//
			$result = implode( ',', $result );
			
			//
			// Set message.
			//
			$message = 'The template is missing required worksheets.';
		
			//
			// Update transaction status.
			//
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
		
			//
			// Set transaction log.
			//
			$theTransaction->setLog(
				kTYPE_STATUS_FATAL,					// Transaction status.
				NULL,								// Alias.
				NULL,								// Field.
				$result,							// Value.
				$message,							// Transaction message.
				NULL,								// Tag.
				kTYPE_ERROR_BAD_TMPL_STRUCT,		// Error type.
				kTYPE_ERROR_CODE_REQ_WKSHEET,		// Error code.
				NULL );								// Error resource.
	
			//
			// Close transaction.
			//
			$theTransaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
			return FALSE;															// ==>
		
		} // Is missing required worksheets.
		
		return TRUE;																// ==>
	
	} // checkRequiredWorksheets.

	 
	/*===================================================================================
	 *	checkRequiredColumns															*
	 *==================================================================================*/

	/**
	 * Check required columns
	 *
	 * This method will check whether all required columns of the provided worksheet are
	 * included in the template.
	 *
	 * If the operation was successful, the method will return <tt>TRUE</tt>, if not, it
	 * will update the provided transaction and return <tt>FALSE</tt>.
	 *
	 * It is assumed that the {@link loadStructure()} and {@link checkRequiredWorksheets}
	 * methods have been called beforehand.
	 *
	 * @param Transaction			$theTransaction		Current transaction.
	 * @param string				$theWorksheet		Worksheet name.
	 *
	 * @access public
	 * @return int					Number of errors.
	 */
	public function checkRequiredColumns( Transaction $theTransaction, $theWorksheet )
	{
		//
		// Init local storage.
		//
		$errors = 0;
		$fields = $this->getFields();
		$wnode = $this->mTemplate->matchSymbolNodes( $theWorksheet )[ 0 ];
		
		//
		// Iterate worksheet fields.
		//
		foreach( $this->mTemplate->getWorksheets()[ $wnode ] as $fnode )
		{
			//
			// Check if worksheet is there.
			// We already checked that the worksheet is there.
			//
			if( array_key_exists( $theWorksheet, $fields ) )
			{
				//
				// Save field information.
				//
				$node = $this->mTemplate->getNode( $fnode );
				$kind = $node->offsetGet( kTAG_DATA_KIND );
				$symbol = $node->offsetGet( kTAG_ID_SYMBOL );
				
				//
				// Check if field was provided.
				//
				if( is_array( $kind )
				 && in_array( kTYPE_MANDATORY, $kind )
				 && (! array_key_exists( $symbol, $fields[ $theWorksheet ] )) )
				{
					//
					// Set transaction log.
					//
					$theTransaction->setLog(
						kTYPE_STATUS_FATAL,					// Status.
						$symbol,							// Alias.
						NULL,								// Field.
						NULL,								// Value.
						'Missing required column.',			// Message.
						$node->offsetGet( kTAG_TAG ),		// Tag.
						kTYPE_ERROR_BAD_TMPL_STRUCT,		// Error type.
						kTYPE_ERROR_CODE_REQ_COLUMN,		// Error code.
						NULL );								// Error resource.
					
					//
					// Increment errors count.
					//
					$errors++;
				
				} // Missing required field.
			
			} // Has worksheet.
		
		} // Iterating worksheet fields.
		
		return $errors;																// ==>
	
	} // checkRequiredColumns.

	

/*=======================================================================================
 *																						*
 *								PUBLIC TEMPLATE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getRoot																			*
	 *==================================================================================*/

	/**
	 * Get root node
	 *
	 * This method will return the root node object.
	 *
	 * @access public
	 * @return Node					Root node object.
	 */
	public function getRoot()						{	return $this->mTemplate->getRoot();	}

	
	/*===================================================================================
	 *	getCellValue																	*
	 *==================================================================================*/

	/**
	 * Get cell data
	 *
	 * This method will return the cell data corresponding to the provided coordinates.
	 *
	 * @param mixed					$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param mixed					$theCols			Column name, index or range.
	 *
	 * @access public
	 * @return mixed				Cell value.
	 */
	public function getCellValue( $theWorksheet, $theRow, $theCols = NULL )
	{
		return $this->mFile->getCols( $theWorksheet, $theRow, $theCols );			// ==>
	
	} // getCellValue.

	

/*=======================================================================================
 *																						*
 *								PUBLIC PARSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getWorksheets																	*
	 *==================================================================================*/

	/**
	 * Get relevant worksheets
	 *
	 * This method will return an array containing all template worksheets matching the
	 * structure elements, the array is structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet name.
	 *	<li><em>value</em>: An array structured as follows:
	 *	  <ul>
	 *		<li><tt>title</tt>: The worksheet title or name.
	 *		<li><tt>last_row</tt>: The worksheet's last row number.
	 *		<li><tt>last_column</tt>: The worksheet's last column name.
	 *		<li><tt>last_column_index</tt>: The worksheet's last column index.
	 *		<li><tt>symbol_row</tt>: The worksheet's symbol row.
	 *		<li><tt>data_row</tt>: The worksheet's data row.
	 *		<li><tt>node</tt>: The worksheet's node reference.
	 *	  </ul>
	 * </ul>
	 *
	 * It is assumed that the {@link loadStructure()} method has been called beforehand.
	 *
	 * @access public
	 * @return array				The referenced worksheets list.
	 */
	public function getWorksheets()							{	return $this->mWorksheets;	}

	 
	/*===================================================================================
	 *	getUnitWorksheet																*
	 *==================================================================================*/

	/**
	 * Get unit worksheet
	 *
	 * This method will return the symbol or name of the unit worksheet.
	 *
	 * @access public
	 * @return string				The unit worksheet name.
	 */
	public function getUnitWorksheet()
	{
		//
		// Check worksheets.
		//
		$worksheets = $this->mTemplate->getUnitWorksheets();
		if( count( $worksheets ) )
			return $this->mTemplate->matchNodeSymbol( $worksheets[ 0 ] );			// ==>
		
		return NULL;																// ==>
	
	} // getUnitWorksheet.

	 
	/*===================================================================================
	 *	getFieldWorksheet																*
	 *==================================================================================*/

	/**
	 * Get field worksheet node
	 *
	 * This method will return the worksheet node identifier of the provided field node
	 * identifier.
	 *
	 * @param int					$theNode			Field node reference or object.
	 *
	 * @access public
	 * @return int					Worksheet node identifier.
	 */
	public function getFieldWorksheet( $theNode )
	{
		return $this->mTemplate->matchFieldWorksheet( $theNode );					// ==>
	
	} // getFieldWorksheet.

	 
	/*===================================================================================
	 *	getWorksheetIndexes																*
	 *==================================================================================*/

	/**
	 * Get worksheet indexes
	 *
	 * This method will return the list of worksheet field indexes as an array structured
	 * as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet node identifier.
	 *	<li><em>value</em>: The the index field node identifier.
	 * </ul>
	 *
	 * @access public
	 * @return string				The worksheet indexes.
	 */
	public function getWorksheetIndexes()
	{
		return $this->mTemplate->getWorksheetIndexes();								// ==>
	
	} // getWorksheetIndexes.

	 
	/*===================================================================================
	 *	getWorksheetRelationships														*
	 *==================================================================================*/

	/**
	 * Get worksheet relationships
	 *
	 * This method will return an array containing the list of fields referencing a
	 * template, the array is structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet node identifier.
	 *	<li><em>value</em>: The list of field node identifiers that reference the worksheet
	 *		of the array index.
	 * </ul>
	 *
	 * It is assumed that the {@link loadStructure()} method has been called beforehand.
	 *
	 * @access public
	 * @return array				The worksheet relationships list.
	 */
	public function getWorksheetRelationships()
	{
		return $this->mTemplate->getWorksheetIndexReferences();						// ==>
	
	} // getWorksheetRelationships.

	 
	/*===================================================================================
	 *	getFields																		*
	 *==================================================================================*/

	/**
	 * Get relevant fields
	 *
	 * This method will return an array containing all template worksheet fields matching
	 * the structure elements, the array is structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet name.
	 *	<li><em>value</em>: An array structured as follows:
	 *	  <ul>
	 *		<li><em>index</em>: The field symbol.
	 *		<li><em>value</em>: An array structured as follows:
	 *		  <ul>
	 *			<li><tt>column_name</tt>: The column name.
	 *			<li><tt>column_number</tt>: The column number.
	 *			<li><tt>node</tt>: The worksheet's node reference.
	 *		  </ul>
	 *	  </ul>
	 * </ul>
	 *
	 * It is assumed that the {@link loadStructure()} method has been called beforehand.
	 *
	 * @access public
	 * @return array				The referenced worksheets list.
	 */
	public function getFields()									{	return $this->mFields;	}

	 
	/*===================================================================================
	 *	getRequiredFields																*
	 *==================================================================================*/

	/**
	 * Get required fields
	 *
	 * This method will return an array containing all required fields as an array
	 * structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet name.
	 *	<li><em>value</em>: The list of required field symbols.
	 * </ul>
	 *
	 * It is assumed that the {@link checkRequiredColumns()} method has been called
	 * beforehand, this is because the list is compiled by that method.
	 *
	 * @access public
	 * @return array				The required fields.
	 */
	public function getRequiredFields()					{	return $this->mRequiredFields;	}

		

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
		return $this->mTemplate->matchSymbolNodes( $theSymbol );					// ==>
	
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
		return $this->mTemplate->matchNodeSymbol( $theNode );						// ==>
	
	} // matchNodeSymbol.

	

/*=======================================================================================
 *																						*
 *									PUBLIC CACHE INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getTag																			*
	 *==================================================================================*/

	/**
	 * Get tag
	 *
	 * This method will return the tag object related to the provided native identifier.
	 *
	 * If the object is not cached, the method will cache it.
	 *
	 * @param string				$theIdentifier		Object native identifier.
	 *
	 * @access public
	 * @return Tag					Tag object.
	 */
	public function getTag( $theIdentifier )
	{
		return $this->mTemplate->getTag( $theIdentifier );							// ==>
		
	} // getTag.

	 
	/*===================================================================================
	 *	getTerm																			*
	 *==================================================================================*/

	/**
	 * Get term
	 *
	 * This method will return the term object related to the provided native identifier.
	 *
	 * If the object is not cached, the method will cache it.
	 *
	 * @param string				$theIdentifier		Object native identifier.
	 *
	 * @access public
	 * @return Term					Term object.
	 */
	public function getTerm( $theIdentifier )
	{
		return $this->mTemplate->getTerm( $theIdentifier );							// ==>
		
	} // getTerm.

	 
	/*===================================================================================
	 *	getNode																			*
	 *==================================================================================*/

	/**
	 * Get node
	 *
	 * This method will return the node object related to the provided native identifier or
	 * persistent identifier.
	 *
	 * If the object is not cached, the method will cache it.
	 *
	 * @param mixed					$theIdentifier		Object native or persistent id.
	 *
	 * @access public
	 * @return Node					Node object.
	 */
	public function getNode( $theIdentifier )
	{
		return $this->mTemplate->getNode( $theIdentifier );							// ==>
		
	} // getNode.

	

/*=======================================================================================
 *																						*
 *								PROTECTED PARSING INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	setWorksheets																	*
	 *==================================================================================*/

	/**
	 * Set relevant worksheets
	 *
	 * This method will set the relevant member with all template worksheets matching the
	 * structure elements, the array is structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet name.
	 *	<li><em>value</em>: An array structured as follows:
	 *	  <ul>
	 *		<li><tt>title</tt>: The worksheet title or name.
	 *		<li><tt>last_row</tt>: The worksheet's last row number.
	 *		<li><tt>last_column</tt>: The worksheet's last column name.
	 *		<li><tt>last_column_index</tt>: The worksheet's last column index.
	 *		<li><tt>symbol_row</tt>: The worksheet's symbol row.
	 *		<li><tt>data_row</tt>: The worksheet's data row.
	 *		<li><tt>node</tt>: The worksheet's node reference.
	 *	  </ul>
	 * </ul>
	 *
	 * It is assumed that the {@link loadStructure()} method has been called beforehand.
	 *
	 * To retrieve the value call {@link getWorksheets()}.
	 *
	 * @access protected
	 */
	protected function setWorksheets()
	{
		//
		// Init local storage.
		//
		$this->mWorksheets = $dictionary = $temp = Array();
		
		//
		// Collect worksheet nodes and symbols.
		//
		foreach( array_keys( $this->mTemplate->getWorksheets() ) as $node )
			$dictionary[ $this->mTemplate->getNodeSymbols()[ $node ] ]
				= $node;
		
		//
		// Load template worksheets.
		//
		$worksheets = $this->mFile->getWorksheets( TRUE );
		foreach( $worksheets as $worksheet )
		{
			//
			// Match template structure.
			//
			if( array_key_exists( $worksheet[ 'title' ], $dictionary ) )
			{
				$temp[ $worksheet[ 'title' ] ] = $worksheet;
				$temp[ $worksheet[ 'title' ] ][ 'symbol_row' ]
					= (int) $this->mTemplate
						->getNode( $dictionary[ $worksheet[ 'title' ] ] )
							->offsetGet( kTAG_LINE_SYMBOL );
				$temp[ $worksheet[ 'title' ] ][ 'data_row' ]
					= (int) $this->mTemplate
						->getNode( $dictionary[ $worksheet[ 'title' ] ] )
							->offsetGet( kTAG_LINE_DATA );
				$temp[ $worksheet[ 'title' ] ][ 'node' ]
					= $dictionary[ $worksheet[ 'title' ] ];
			}
		
		} // Iterating template worksheets.
		
		//
		// Get unit worksheets.
		//
		$this->mWorksheets = Array();
		$w_u = $this->mTemplate->getUnitWorksheets();
		$w_r = $this->mTemplate->getRequiredWorksheets();
		$w_i = $this->mTemplate->getWorksheetIndexReferences();
		if( is_array( $w_u ) )
		{
			foreach( $w_u as $n )
			{
				$sym = $this->mTemplate->matchNodeSymbol( $n );
				if( ! array_key_exists( $sym, $this->mWorksheets ) )
					$this->mWorksheets[ $sym ] = $temp[ $sym ];
			}
		}
		if( is_array( $w_i ) )
		{
			foreach( array_keys( $w_i ) as $n )
			{
				$sym = $this->mTemplate->matchNodeSymbol( $n );
				if( ! array_key_exists( $sym, $this->mWorksheets ) )
					$this->mWorksheets[ $sym ] = $temp[ $sym ];
			}
		}
		if( is_array( $w_r ) )
		{
			foreach( $w_r as $n )
			{
				$sym = $this->mTemplate->matchNodeSymbol( $n );
				if( ! array_key_exists( $sym, $this->mWorksheets ) )
					$this->mWorksheets[ $sym ] = $temp[ $sym ];
			}
		}
		foreach( $temp as $key => $value )
		{
			if( ! array_key_exists( $key, $this->mWorksheets ) )
				$this->mWorksheets[ $key ] = $value;
		}
	
	} // setWorksheets.

	 
	/*===================================================================================
	 *	setFields																		*
	 *==================================================================================*/

	/**
	 * Set relevant fields
	 *
	 * This method will set the relevant member with all template worksheet fields matching
	 * the structure elements, the array is structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet name.
	 *	<li><em>value</em>: An array structured as follows:
	 *	  <ul>
	 *		<li><em>index</em>: The field symbol.
	 *		<li><em>value</em>: An array structured as follows:
	 *		  <ul>
	 *			<li><tt>column_name</tt>: The column name.
	 *			<li><tt>column_number</tt>: The column number.
	 *			<li><tt>node</tt>: The worksheet's node reference.
	 *			<li><tt>indexed</tt>: If it represents an index, <tt>TRUE</tt>.
	 *			<li><tt>unique</tt>: If it represents a unique index, <tt>TRUE</tt>.
	 *			<li><tt>worksheet</tt>: Referenced worksheet.
	 *			<li><tt>field</tt>: Referenced field.
	 *		  </ul>
	 *	  </ul>
	 * </ul>
	 *
	 * It is assumed that the {@link loadStructure()} method has been called beforehand.
	 *
	 * To retrieve the value call {@link getFields()}.
	 *
	 * @access protected
	 */
	protected function setFields()
	{
		//
		// Init local storage.
		//
		$this->mFields = Array();
		$indexes = $this->mTemplate->getWorksheetIndexes();
		$references = $this->mTemplate->getWorksheetIndexReferences();
		
		//
		// Collect referencing fields.
		//
		$referencing = Array();
		foreach( $references as $list )
		{
			foreach( $list as $element )
				$referencing[ $element ] = $element;
		}
		
		//
		// Collect worksheet field nodes and symbols.
		//
		foreach( $this->mWorksheets as $key => $value )
		{
			//
			// Build worksheet fields dictionary.
			//
			$dictionary = Array();
			foreach( $this->mTemplate->getWorksheets()[ $value[ 'node' ] ] as $id )
				$dictionary[ $this->mTemplate->getNode( $id )
					->offsetGet( kTAG_ID_SYMBOL ) ]
						= $id;
			
			//
			// Create worksheet reference.
			//
			$this->mFields[ $key ] = Array();
			$wref = & $this->mFields[ $key ];
			
			//
			// Iterate worksheet symbol row.
			//
			$columns = $this->mFile->getCols( $key, $value[ 'symbol_row' ] );
			foreach( $columns as $name => $symbol )
			{
				//
				// Match dictionary.
				//
				if( array_key_exists( $symbol, $dictionary ) )
				{
					//
					// Create field reference.
					//
					$wref[ $symbol ] = Array();
					$fref = & $wref[ $symbol ];
					
					//
					// Load field.
					//
					$fref[ 'column_name' ] = $name;
					$fref[ 'column_number' ] = $this->mFile->getColumnNumber( $name );
					$fref[ 'node' ] = $dictionary[ $symbol ];
					
					//
					// Handle index.
					//
					if( in_array( $fref[ 'node' ], $indexes ) )
					{
						$fref[ 'indexed' ] = TRUE;
						$fref[ 'unique' ] = TRUE;
					}
					
					//
					// Handle referencing.
					//
					elseif( in_array( $fref[ 'node' ], $referencing ) )
						$fref[ 'indexed' ] = TRUE;
				}
			}
		}
		
		//
		// Set references.
		//
		$structure = $this->mTemplate->getWorksheets();
		$dictionary = $this->mTemplate->getNodeSymbols();
		foreach( $references as $target_worksheet_node => $source_field_nodes )
		{
			//
			// Locate target field.
			//
			$target_worksheet = $dictionary[ $target_worksheet_node ];
			$target_field = $dictionary[ $indexes[ $target_worksheet_node ] ];
			
			//
			// Iterate referencing fields.
			//
			foreach( $source_field_nodes as $source_field_node )
			{
				//
				// Locate field.
				//
				foreach( $structure as $source_worksheet_node => $match_field_nodes )
				{
					$windex = $dictionary[ $source_worksheet_node ];
					$findex = $dictionary[ $source_field_node ];
					if( in_array( $source_field_node, $match_field_nodes )
					 && array_key_exists( $windex, $this->mFields )
					 && array_key_exists( $findex,$this->mFields[ $windex ] ) )
					{
						$this->mFields[ $windex ]
									  [ $findex ]
									  [ 'worksheet' ] = $target_worksheet;
						$this->mFields[ $windex ]
									  [ $findex ]
									  [ 'field' ] = $target_field;
					}
				}
			}
		}
	
	} // setFields.

	 
	/*===================================================================================
	 *	setRequiredFields																*
	 *==================================================================================*/

	/**
	 * Set required fields
	 *
	 * This method will set the relevant member with all required fields, the array is
	 * structured as follows:
	 *
	 * <ul>
	 *	<li><em>index</em>: The worksheet name.
	 *	<li><em>value</em>: The list of required field symbols.
	 * </ul>
	 *
	 * It is assumed that the {@link setFields()} method has been called beforehand.
	 *
	 * To retrieve the value call {@link getRequiredFields()}.
	 *
	 * @access protected
	 */
	protected function setRequiredFields()
	{
		//
		// Init local storage.
		//
		$this->mRequiredFields = Array();
		$fields = $this->getFields();
		$symbols = $this->mTemplate->getNodeSymbols();
		$worksheets = $this->mTemplate->getWorksheets();
		$increment = 100 / count( array_keys( $worksheets ) );
		
		//
		// Iterate worksheets.
		//
		foreach( $worksheets as $worksheet => $wfields )
		{
			//
			// Init local storage.
			//
			$name = $symbols[ $worksheet ];
			$this->mRequiredFields[ $name ] = Array();
			
			//
			// Iterate worksheet fields.
			//
			foreach( $wfields as $field )
			{
				//
				// Init local storage.
				//
				$symbol = $symbols[ $field ];
				$node = $this->mTemplate->getNode( $field );
				$kind = $node->offsetGet( kTAG_DATA_KIND );
				
				//
				// Check if required.
				//
				if( ($kind !== NULL)
				 && in_array( kTYPE_MANDATORY, $kind ) )
					$this->mRequiredFields[ $name ][] = $symbol;
				
				//
				// Check if indexed.
				//
				elseif( array_key_exists( $name, $fields )
					 && array_key_exists( $symbol, $fields[ $name ] ) )
				{
					if( array_key_exists( 'indexed', $fields[ $name ][ $symbol ] )
					 && $fields[ $name ][ $symbol ] )
						$this->mRequiredFields[ $name ][] = $symbol;
				}
			
			} // Iterating worksheet fields.
		
		} // Iterating worksheets.
	
	} // setRequiredFields.

	 

} // class ExcelTemplateParser.


?>
