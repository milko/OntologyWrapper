<?php

/**
 * ExcelParser.php
 *
 * This file contains the definition of the {@link ExcelParser} class.
 */

namespace OntologyWrapper;

/*=======================================================================================
 *																						*
 *										ExcelParser.php									*
 *																						*
 *======================================================================================*/

/**
 * Domains.
 *
 * This file contains the default Excel library definitions.
 */
require_once( kPATH_LIBRARY_EXCEL."/PHPExcel.php" );

/**
 * Excel parser object
 *
 * This class can be used to extract and manipulate data from an excel file.
 *
 *	@author		Alessandro Gubitosi <gubi.ale@iod.io>
 *	@version	1.00 19/02/2014
 */
class ExcelParser
{
	/**
	 * Excel object.
	 *
	 * This data member holds the <i>Excel object</i>.
	 *
	 * @var PHPExcel
	 */
	protected $PE = NULL;

		

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
	 * This class is instantiated by providing an upload session and the upload template
	 * file reference.
	 *
	 * @param mixed					$theFile			Excel file reference.
	 *
	 * @access public
	 */
	public function __construct( $theFile )
	{
		//
		// Get file path.
		//
		if( $theFile instanceof \SplFileInfo )
			$theFile = $theFile->getRealPath();
		
		//
		// Create new PHPExcel object.
		//
		$this->PE = \PHPExcel_IOFactory::load( $theFile );

	} // Constructor.

	

/*=======================================================================================
 *																						*
 *								PUBLIC DOCUMENT INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getPID																			*
	 *==================================================================================*/

	/**
	 * Get document PID
	 *
	 * This method can be used to retrieve the <i>document PID</i>, if not found, the
	 * method will return <tt>NULL</tt>.
	 *
	 * @access public
	 * @return array				Document PID.
	 */
	public function getPID()
	{
		//
		// Get document custom properties.
		//
		$tmp = $this->PE->getProperties()->getCustomProperties();
		if( in_array( 'PID', $tmp ) )
			return $this->PE->getProperties()->getCustomPropertyValue( 'PID' );		// ==>
		
		return NULL;																// ==>
	
	} // getPID.

	 
	/*===================================================================================
	 *	getWorksheets																	*
	 *==================================================================================*/

	/**
	 * Get worksheets
	 *
	 * This method can be used to retrieve the <i>document worksheets</i>, the method
	 * expects a single parameter that determines what the method returns:
	 *
	 * <ul>
	 *	<li><tt>NULL</tt>: Return worksheet titles.
	 *	<li><tt>TRUE</tt>: Return worksheet stats.
	 *	<li><em>other</em>: The value will be converted to a string and interpreted as the
	 *		worksheet title: the method will return the stats for that worksheet.
	 * </ul>
	 *
	 * @param mixed					$theWorksheet		Worksheet name or operation.
	 *
	 * @access public
	 * @return array				Worksheet stats or title(s).
	 */
	public function getWorksheets( $theWorksheet = NULL )
	{
		//
		// Init local storage.
		//
		$sheets = Array();
		
		//
		// Iterate worksheets.
		//
		foreach( $this->PE->getWorksheetIterator() as $sheet )
		{
			//
			// Save title.
			//
			if( $theWorksheet === NULL )
				$sheets[] = $sheet->getTitle();
			
			//
			// Save stats.
			//
			else
			{
				//
				// Get sheet stats.
				//
				$stats = Array();
				$stats[ "title" ] = $sheet->getTitle();
				$stats[ "last_row" ] = $sheet->getHighestRow();
				$stats[ "last_column" ] = $sheet->getHighestColumn();
				$stats[ "columns_count" ] = ord( $sheet->getHighestColumn() ) - 64;
				$stats[ "last_column_index" ]
					= \PHPExcel_Cell::columnIndexFromString( $sheet->getHighestColumn() );
				
				//
				// Handle all worksheets.
				//
				if( $theWorksheet === TRUE )
					$sheets[] = $stats;
				
				//
				// Handle specific worksheet.
				//
				elseif( $sheet->getTitle() == (string) $theWorksheet )
					return $stats;													// ==>
			
			} // Save stats.
		
		} // Iterating worksheets.
		
		return $sheets;																// ==>
	
	} // getWorksheets.

	 
	/*===================================================================================
	 *	getRows																			*
	 *==================================================================================*/

	/**
	 * Get rows
	 *
	 * This method can be used to retrieve a <i>selection of rows</i> corresponding to the
	 * provided worksheet and column.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWorksheet</b>: The desired worksheet.
	 *	<li><b>$theCol</b>: The column name or index.
	 *	<li><b>$theRows</b>: Row number, range or <tt>NULL</tt>:
	 *	  <ul>
	 *		<li><tt>NULL</tt>: The method will return an array indexed by row number for all
	 *			the rows of the provided column.
	 *		<li><tt>array</tt>: The first element of the array is expected to be the first
	 *			row, the second element the number of rows to return; the method will return
	 *			an array indexed by row number.
	 *		<li><tt>int</tt>: The method will return the value at the row and column.
	 *	  </ul>
	 * </ul>
	 *
	 * If the worksheet was not matched, the method will return <tt>NULL</tt>.
	 *
	 * @param mixed					$theWorksheet		Worksheet name.
	 * @param mixed					$theCol				Column name or index.
	 * @param mixed					$theRows			Row or row range.
	 *
	 * @access public
	 * @return array				Row values range or <tt>NULL</tt>.
	 */
	public function getRows( $theWorksheet, $theCol, $theRows = NULL )
	{
		//
		// Get worksheet stats.
		//
		$stats = $this->getWorksheets( $theWorksheet );
		if( $stats )
		{
			//
			// Normalise column.
			//
			if( is_int( $theCol )
			 || ctype_digit( $theCol ) )
				$theCol = \PHPExcel_Cell::stringFromColumnIndex( (int) $theCol - 1 );
			
			//
			// Handle all rows.
			//
			if( $theRows === NULL )
				$theRows = array( 1, (int) $stats[ "last_row" ] );
			
			//
			// Handle row index.
			//
			elseif( ! is_array( $theRows ) )
				$theRows = array( (int) $theRows, 1 );
			
			return $this->rangeRows( $theWorksheet, $theRows, $theCol );			// ==>
		
		} // Matched worksheet.
		
		return NULL;																// ==>
	
	} // getRows.

	 
	/*===================================================================================
	 *	getCols																			*
	 *==================================================================================*/

	/**
	 * Get columns
	 *
	 * This method can be used to retrieve a <i>selection of columns</i> corresponding to
	 * the provided worksheet and row.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWorksheet</b>: The desired worksheet.
	 *	<li><b>$theRow</b>: Row number.
	 *	<li><b>$theCols</b>: The column name, index range or <tt>NULL</tt>.
	 * </ul>
	 *
	 * If the worksheet was not matched, the method will return <tt>NULL</tt>, in all
	 * other cases the method will return an array indexed by column name.
	 *
	 * @param mixed					$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param mixed					$theCols			Column name, index or range.
	 *
	 * @access public
	 * @return array				Column values range or <tt>NULL</tt>.
	 */
	public function getCols( $theWorksheet, $theRow, $theCols = NULL )
	{
		//
		// Get worksheet stats.
		//
		$stats = $this->getWorksheets( $theWorksheet );
		if( $stats )
		{
			//
			// Handle all columns.
			//
			if( $theCols === NULL )
				$theCols = array( 'A', (int) $stats[ "columns_count" ] );
			
			//
			// Handle column index.
			//
			elseif( ! is_array( $theCols ) )
				$theCols = array( $theCols, 1 );
			
			return $this->rangeCols( $theWorksheet, $theRow, $theCols );			// ==>
		
		} // Matched worksheet.
		
		return NULL;																// ==>
	
	} // getCols.

	

/*=======================================================================================
 *																						*
 *								PROTECTED CELL INTERFACE								*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getCell																			*
	 *==================================================================================*/

	/**
	 * Get a cell object
	 *
	 * This method can be used to retrieve the <i>worksheet cell</i> as an object.
	 *
	 * Note that if the cell does not exist, the method will create and return the cell.
	 *
	 * @param mixed					$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param string				$theCol				Column name.
	 *
	 * @access protected
	 * @return PHPExcel_Cell			Cell object or <tt>NULL</tt>.
	 */
	protected function getCell( $theWorksheet, $theRow, $theCol )
	{
		//
		// Get worksheet.
		//
		$sheet = $this->PE->getSheetByName( $theWorksheet );
		if( $sheet !== NULL )
			return $sheet->getCell( "$theCol$theRow" );								// ==>
		
		return NULL;																// ==>
	
	} // getCell.

	 
	/*===================================================================================
	 *	getCellValue																	*
	 *==================================================================================*/

	/**
	 * Get a cell value
	 *
	 * This method can be used to retrieve the <i>worksheet cell value</i>.
	 *
	 * You can specify one of the row or column as an array, but not both.
	 *
	 * If the worksheet is not matched, the method will return <tt>NULL</tt>.
	 *
	 * @param mixed					$theWorksheet		Worksheet name.
	 * @param mixed					$theRow				Row number(s).
	 * @param mixed					$theCol				Column name(s).
	 *
	 * @access protected
	 * @return mixed				Cell value(s).
	 */
	protected function getCellValue( $theWorksheet, $theRow, $theCol )
	{
		//
		// Init local storage.
		//
		$cell = NULL;
		
		//
		// Check coordinates.
		//
		if( is_array( $theRow )
		 && is_array( $theCol ) )
			throw new \Exception(
				"Cannot get cell value: "
			   ."expecting only one array coordinate." );						// !@! ==>
		
		//
		// Get worksheet.
		//
		$sheet = $this->PE->getSheetByName( $theWorksheet );
		if( $sheet !== NULL )
		{
			//
			// Handle single cell.
			//
			if( (! is_array( $theRow ))
			 && (! is_array( $theCol )) )
			{
				$cell = $this->getCell( $theWorksheet, $theRow, $theCol );
				return ( $cell->isFormula() )
					 ? $cell->getCalculatedValue()									// ==>
					 : $cell->getValue();											// ==>
			}
			
			//
			// Init local storage.
			//
			$cell = Array();
			
			//
			// Handle rows.
			//
			if( is_array( $theRow ) )
			{
				//
				// Iterate rows.
				//
				foreach( $theRow as $row )
				{
					$cell = $this->getCell( $theWorksheet, $row, $theCol );
					$cell[ $row ] = ( $cell->isFormula() )
								  ? $cell->getCalculatedValue()
								  : $cell->getValue();
				}
			
			} // Multiple rows.
		
			//
			// Handle columns.
			//
			else
			{
				//
				// Iterate columns.
				//
				foreach( $theCol as $col )
				{
					$cell = $this->getCell( $theWorksheet, $theRow, $col );
					$cell[ $col ] = ( $cell->isFormula() )
								  ? $cell->getCalculatedValue()
								  : $cell->getValue();
				}
			
			} // Multiple rows.
		
		} // Matched worksheet.
		
		return $cell;																// ==>
	
	} // getCellValue.

	

/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	rangeCols																		*
	 *==================================================================================*/

	/**
	 * Get a range of row columns
	 *
	 * This method can be used to retrieve a <i>range of row cells(s)</i>, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWorksheet</b>: Worksheet name, if not matched, the method will return
	 *		<tt>NULL</tt>.
	 *	<li><b>$theRow</b>: Row number.
	 *	<li><b>$theCols</b>: Columns range, start and limit.
	 * </ul>
	 *
	 * If the worksheet does not exist, the method will return <tt>NULL</tt>, in all other
	 * cases the method returns an array.
	 *
	 * Note that if the cell does not exist, the method will create and return the cell.
	 *
	 * @param mixed					$theWorksheet		Worksheet name.
	 * @param int					$theRow				Row number.
	 * @param array					$theCols			Column range.
	 *
	 * @access protected
	 * @return array				Row cells range or <tt>NULL</tt>.
	 */
	protected function rangeCols( $theWorksheet, $theRow, $theCols )
	{
		//
		// Get worksheet.
		//
		$sheet = $this->PE->getSheetByName( $theWorksheet );
		if( $sheet !== NULL )
		{
			//
			// Check columns.
			//
			if( count( $theCols ) != 2 )
				throw new \Exception(
					"Cannot get row cells range: "
				   ."expecting start and limit columns." );						// !@! ==>
			
			//
			// Save start column.
			//
			$start = ( is_int( $theCols[ 0 ] )
					|| ctype_digit( $theCols[ 0 ] ) )
				   ? (int) $theCols[ 0 ]
				   : (string) $theCols[ 0 ];
			
			//
			// Normalise start.
			//
			$theCols[ 0 ] = ( is_int( $theCols[ 0 ] )
						   || ctype_digit( $theCols[ 0 ] ) )
						  ? (int) $theCols[ 0 ]
						  : \PHPExcel_Cell::columnIndexFromString( $theCols[ 0 ] );
			
			//
			// Check start.
			//
			if( $theCols[ 0 ] <= 0 )
				throw new \Exception(
					"Cannot get row cells range: "
				   ."zero column index." );										// !@! ==>
			
			//
			// Check limit.
			//
			if( $theCols[ 1 ] <= 0 )
				throw new \Exception(
					"Cannot get row cells range: "
				   ."zero limit index." );										// !@! ==>
			
			//
			// Iterate range.
			//
			$result = Array();
			for( $col = ( $theCols[ 0 ] - 1);
					$col < ($theCols[ 0 ] + $theCols[ 1 ] - 1);
						$col++ )
			{
				//
				// Get value.
				//
				$value
					= $this->getCellValue( $theWorksheet,
										   $theRow,
										   \PHPExcel_Cell::stringFromColumnIndex( $col ) );
				
				//
				// Handle cell.
				//
				if( $theCols[ 1 ] == 1 )
					return $value;													// ==>
				
				//
				// Set index.
				//
				$index = ( is_int( $start ) )
					   ? ($col + 1)
					   : \PHPExcel_Cell::stringFromColumnIndex( $col );
				
				//
				// Save value.
				//
				$result[ $index ]
					= $this->getCellValue( $theWorksheet,
										   $theRow,
										   \PHPExcel_Cell::stringFromColumnIndex( $col ) );
			
			} // Iterating range.
			
			return $result;															// ==>
		
		} // Matched worksheet.
		
		return NULL;																// ==>
	
	} // rangeCols.

	 
	/*===================================================================================
	 *	rangeRows																		*
	 *==================================================================================*/

	/**
	 * Get a range of column rows
	 *
	 * This method can be used to retrieve a <i>range of column row cells(s)</i>, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWorksheet</b>: Worksheet name, if not matched, the method will return
	 *		<tt>NULL</tt>.
	 *	<li><b>$theRows</b>: Row range, start and limit.
	 *	<li><b>$theCol</b>: Column name.
	 * </ul>
	 *
	 * If the worksheet does not exist, the method will return <tt>NULL</tt>, in all other
	 * cases the method returns an array.
	 *
	 * Note that if the cell does not exist, the method will create and return the cell.
	 *
	 * @param mixed					$theWorksheet		Worksheet name.
	 * @param array					$theRows			Row range.
	 * @param string				$theCol				Column name.
	 *
	 * @access protected
	 * @return array				Row cells range or <tt>NULL</tt>.
	 */
	protected function rangeRows( $theWorksheet, $theRows, $theCol )
	{
		//
		// Get worksheet.
		//
		$sheet = $this->getWorksheets( $theWorksheet );
		if( $sheet !== NULL )
		{
			//
			// Check range.
			//
			if( count( $theRows ) != 2 )
				throw new \Exception(
					"Cannot get column cells range: "
				   ."expecting start and limit rows." );						// !@! ==>
			
			//
			// Check start.
			//
			if( $theRows[ 0 ] <= 0 )
				throw new \Exception(
					"Cannot get column cells range: "
				   ."zero row index." );										// !@! ==>
			
			//
			// Check limit.
			//
			if( $theRows[ 1 ] <= 0 )
				throw new \Exception(
					"Cannot get column cells range: "
				   ."zero limit index." );										// !@! ==>
			
			//
			// Iterate range.
			//
			$result = Array();
			for( $row = $theRows[ 0 ];
					$row < ($theRows[ 0 ] + $theRows[ 1 ]);
						$row++ )
			{
				//
				// Get value.
				//
				$value = $this->getCellValue( $theWorksheet, $row, $theCol );
				
				//
				// Handle cell.
				//
				if( $theRows[ 1 ] == 1 )
					return $value;													// ==>
				
				//
				// Handle range.
				//
				$result[ $row ] = $value;
			}
			
			return $result;															// ==>
		
		} // Matched worksheet.
		
		return NULL;																// ==>
	
	} // rangeRows.

	 

} // class ExcelParser.


?>
