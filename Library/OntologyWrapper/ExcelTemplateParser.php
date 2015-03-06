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
	 * strucure, it is an array structured as follows:
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
				// Load worksheet fieldss.
				//
				$this->setFields();
				
				return TRUE;														// ==>
			
			} // Matched root.
		
			//
			// Update transaction.
			//
			$theTransaction->offsetSet( kTAG_ERROR_TYPE, kTYPE_ERROR_BAD_TMPL_STRUCT );
			$theTransaction->offsetSet( kTAG_ERROR_CODE, kTYPE_ERROR_CODE_BAD_PID );
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
			$theTransaction->offsetSet( kTAG_TRANSACTION_VALUE, $pid );
			$theTransaction->offsetSet( kTAG_TRANSACTION_MESSAGE,
										'The template type is either '
									   .'incorrect or unsupported.' );
			$theTransaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
			return FALSE;															// ==>
		
		} // Has PID.
		
		//
		// Update transaction.
		//
		$theTransaction->offsetSet( kTAG_ERROR_TYPE, kTYPE_ERROR_BAD_TMPL_STRUCT );
		$theTransaction->offsetSet( kTAG_ERROR_CODE, kTYPE_ERROR_CODE_NO_PID );
		$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
		$theTransaction->offsetSet( kTAG_TRANSACTION_MESSAGE,
									'The template file is missing its "PID" '
								   .'custom property: unable to determine '
								   .'the kind of template.' );
		$theTransaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		return FALSE;																// ==>
	
	} // loadStructure.

	 
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
			$theTransaction->offsetSet( kTAG_ERROR_TYPE, kTYPE_ERROR_BAD_TMPL_STRUCT );
			$theTransaction->offsetSet( kTAG_ERROR_CODE, kTYPE_ERROR_CODE_REQ_WKSHEET );
			$theTransaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
			$theTransaction->offsetSet( kTAG_TRANSACTION_VALUE, $result );
			$theTransaction->offsetSet( kTAG_TRANSACTION_MESSAGE,
										'The template is missing required worksheets.' );
			$theTransaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
			return FALSE;															// ==>
		}
		
		return TRUE;																// ==>
	
	} // checkRequiredWorksheets.

	 
	/*===================================================================================
	 *	checkRequiredColumns															*
	 *==================================================================================*/

	/**
	 * Check required worksheets
	 *
	 * This method will check whether all required columns are included in the template.
	 *
	 * If the operation was successful, the method will return <tt>TRUE</tt>, if not, it
	 * will update the provided transaction and return <tt>FALSE</tt>.
	 *
	 * This method will create a sub-transaction for each parsed worksheet, any missing
	 * required field will be logged in the transaction.
	 *
	 * It is assumed that the {@link loadStructure()} and {@link checkRequiredWorksheets}
	 * methods have been called beforehand.
	 *
	 * @param Transaction			$theTransaction		Current transaction.
	 *
	 * @access public
	 * @return boolean				<tt>TRUE</tt> means OK, <tt>FALSE</tt> means fail.
	 */
	public function checkRequiredColumns( Transaction $theTransaction )
	{
		//
		// Init local storage.
		//
		$ok = TRUE;
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
			
			//
			// Create transaction.
			//
			$transaction
				= $theTransaction
					->newTransaction( kTYPE_TRANS_TMPL_STRUCT_COLUMNS, $name );
			$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 0 );
			
			//
			// Iterate worksheet fields.
			//
			$errors = 0;
			foreach( $wfields as $field )
			{
				//
				// Init local storage.
				//
				$node = $this->mTemplate->getNode( $field );
				$kind = $node->offsetGet( kTAG_DATA_KIND );
				
				//
				// Check if required.
				//
				if( ($kind !== NULL)
				 && in_array( kTYPE_MANDATORY, $kind ) )
				{
					//
					// Locate field.
					//
					if( (! array_key_exists( $name, $fields ))
					 || (! array_key_exists( $node->offsetGet( kTAG_ID_SYMBOL ),
					 						 $fields[ $name ] )) )
					{
						//
						// Add to transaction log.
						//
						$transaction->setLog(
							kTYPE_STATUS_FATAL,
							'Missing required field.',
							$node->offsetGet( kTAG_ID_SYMBOL ),
							$node->offsetGet( kTAG_TAG ),
							NULL,
							NULL,
							kTYPE_ERROR_BAD_TMPL_STRUCT );
						
						//
						// Increment errors count.
						//
						$ok = FALSE;
						$errors++;
					
					} // Missing.
				
				} // Is mandatory field.
			
			} // Iterating worksheet fields.
			
			//
			// Update progress.
			//
			$transaction->offsetSet( kTAG_COUNTER_PROGRESS, 100 );
			$theTransaction->progress( $increment );
			
			//
			// Handle errors.
			//
			if( $errors )
			{
				$transaction->offsetSet( kTAG_ERROR_TYPE, kTYPE_ERROR_BAD_TMPL_STRUCT );
				$transaction->offsetSet( kTAG_ERROR_CODE, kTYPE_ERROR_CODE_REQ_COLUMN );
				$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_FATAL );
				$transaction->offsetSet( kTAG_TRANSACTION_MESSAGE,
											'The template is missing required columns.' );
			}
			
			//
			// Handle successfull.
			//
			else
				$transaction->offsetSet( kTAG_TRANSACTION_STATUS, kTYPE_STATUS_OK );
			
			//
			// Close transaction.
			//
			$transaction->offsetSet( kTAG_TRANSACTION_END, TRUE );
		
		} // Iterating worksheets.
		
		return $ok;																	// ==>
	
	} // checkRequiredColumns.

	

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
		$this->mWorksheets = $dictionary = Array();
		
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
				$this->mWorksheets[ $worksheet[ 'title' ] ] = $worksheet;
				$this->mWorksheets[ $worksheet[ 'title' ] ][ 'symbol_row' ]
					= $this->mTemplate->getNode( $dictionary[ $worksheet[ 'title' ] ] )
						->offsetGet( kTAG_LINE_SYMBOL );
				$this->mWorksheets[ $worksheet[ 'title' ] ][ 'data_row' ]
					= $this->mTemplate->getNode( $dictionary[ $worksheet[ 'title' ] ] )
						->offsetGet( kTAG_LINE_DATA );
				$this->mWorksheets[ $worksheet[ 'title' ] ][ 'node' ]
					= $dictionary[ $worksheet[ 'title' ] ];
			}
		
		} // Iterating template worksheets.
	
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
		$dictionary = $this->mTemplate->getSymbolNodes();
		
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
				$dictionary[ $this->mTemplate->getNode( $id )->offsetGet( kTAG_ID_SYMBOL ) ]
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
				}
			}
		}
	
	} // setFields.

	 

} // class ExcelTemplateParser.


?>
