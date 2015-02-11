<?php

/**
 * DataTemplate.php
 *
 * This file contains the definition of the {@link DataTemplate} class.
 */

namespace OntologyWrapper;

use OntologyWrapper\TemplateStructure;

/*=======================================================================================
 *																						*
 *									DataTemplate.php									*
 *																						*
 *======================================================================================*/

/**
 * Data template
 *
 * This <em>abstract</em> class implements an object that implements a generic data
 * template. A template is a collection of worksheets which are tabular data, each worksheet
 * is related to the structure or nested structure of a single object, this allows providing
 * complete data for objects featuring complex structures.
 *
 * The class is instantiated by providing a data wrapper, a reference to the template, a
 * reference to the root template structure node and the default language code.
 *
 * The class features a series of methods, abstract and not, which provide the interface
 * for concrete derived classes which will implement objects using specific template types.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 11/02/2015
 */
abstract class DataTemplate
{
	/**
	 * Template data file.
	 *
	 * This data member holds a reference to the template data file.
	 *
	 * @var SplFileObject
	 */
	 protected $mTemplateFile = NULL;

	/**
	 * Template data object.
	 *
	 * This data member holds the template data object.
	 *
	 * @var mixed
	 */
	 protected $mTemplateObject = NULL;

	/**
	 * Template structure.
	 *
	 * This data member holds the related template structure object.
	 *
	 * @var TemplateStructure
	 */
	 protected $mTemplateStruct = NULL;

		

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
	 * The constructor expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theWrapper</b>: Data wrapper.
	 *	<li><b>$theTemplate</b>: Data template reference.
	 *	<li><b>$theStructure</b>: Template structure root node reference, either the native
	 *		or the persistent identifier.
	 * </ul>
	 *
	 * @param Wrapper				$theWrapper			Database wrapper.
	 * @param mixed					$theTemplate		Data template reference.
	 * @param mixed					$theStructure		Template root node reference.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access public
	 */
	public function __construct( Wrapper $theWrapper,
										 $theTemplate,
										 $theStructure = NULL,
										 $theLanguage = kSTANDARDS_LANGUAGE )
	{
		//
		// Set wrapper.
		//
		$this->mWrapper = $theWrapper;
		
		//
		// Set template data file.
		//
		$this->mTemplateFile = $this->initTemplateFile( $theTemplate );
		
		//
		// Set template data object.
		//
		$this->mTemplateObject = $this->initTemplateObject( $this->mTemplateFile );
		
		//
		// Set template structure object.
		//
		$this->mTemplateStruct
			= $this->initTemplateStructure( $theWrapper, $theStructure, $theLanguage );
		
	} // Constructor.

		

/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	getWrapper																		*
	 *==================================================================================*/

	/**
	 * Return data wrapper
	 *
	 * This method will return the data wrapper.
	 *
	 * @access public
	 * @return Wrapper				Data wrapper.
	 */
	public function getWrapper()		{	return $this->mTemplateStruct->getWrapper();	}

	 
	/*===================================================================================
	 *	getTemplateFile																	*
	 *==================================================================================*/

	/**
	 * Get template file
	 *
	 * This method will return the data template file object as a SplFileObject.
	 *
	 * @access public
	 * @return SplFileObject		The template data file reference.
	 */
	public function getTemplateFile()					{	return $this->mTemplateFile;	}

	 
	/*===================================================================================
	 *	getTemplateObject																*
	 *==================================================================================*/

	/**
	 * Get template object
	 *
	 * This method will return the data template object.
	 *
	 * @access public
	 * @return mixed				The template data object.
	 */
	public function getTemplateObject()					{	return $this->mTemplateObject;	}

	 
	/*===================================================================================
	 *	getTemplateStructure															*
	 *==================================================================================*/

	/**
	 * Get template structure
	 *
	 * This method will return the template structure object.
	 *
	 * @access public
	 * @return TemplateStructure	The template structure object.
	 */
	public function getTemplateStructure()				{	return $this->mTemplateStruct;	}

	 
	/*===================================================================================
	 *	getLanguage																		*
	 *==================================================================================*/

	/**
	 * Return default language
	 *
	 * This method will return the default language.
	 *
	 * @access public
	 * @return string				Language code.
	 */
	public function getLanguage()		{	return $this->mTemplateStruct->getLanguage();	}

		

/*=======================================================================================
 *																						*
 *						PROTECTED MEMBER INITIALISATION INTERFACE						*
 *																						*
 *======================================================================================*/


	 
	/*===================================================================================
	 *	initTemplateFile																*
	 *==================================================================================*/

	/**
	 * Initialise template file
	 *
	 * The duty of this method is to initialise the template file object, the method expects
	 * a reference to the template file either as an <tt>SplFileObject</tt>, or as that
	 * object itself.
	 *
	 * The method will return the object.
	 *
	 * In derived classes you may overload this method to check the validity of the
	 * referenced file.
	 *
	 * @param string				$theFile			Path to file or SplFileObject.
	 * @param string				$theMode			File mode.
	 *
	 * @access protected
	 * @return SplFileObject		Template file object.
	 */
	protected function initTemplateFile( $theFile, $theMode = "r" )
	{
		//
		// Instantiate template file object.
		//
		if( ! ($theFile instanceof \SplFileObject) )
			return new \SplFileObject( (string) $theFile, $theMode );				// ==>
		
		return $theFile;															// ==>
		
	} // setTemplateFile.

	 
	/*===================================================================================
	 *	initTemplateObject																*
	 *==================================================================================*/

	/**
	 * Initialise template object
	 *
	 * The duty of this method is to initialise the template object, the method expects
	 * a reference to the template file as an <tt>SplFileObject</tt> and will return the
	 * template data object.
	 *
	 * In this class the method is abstract, derived classes must implement the class to
	 * handle their specific template file type.
	 *
	 * @param SplFileObject			$theFile			File object.
	 *
	 * @access protected
	 * @return SplFileObject		Template file object.
	 */
	abstract protected function initTemplateObject( \SplFileObject $theFile );

	 
	/*===================================================================================
	 *	initTemplateStructure															*
	 *==================================================================================*/

	/**
	 * Initialise template structure
	 *
	 * The duty of this method is to initialise the template structure object, the method
	 * expects the same parameters as the {@link TemplateStructure} class
	 * {@link TemplateStructure::__construct()} constructor.
	 *
	 * The method will return an object of type {@link TemplateStructure}.
	 *
	 * @param Wrapper				$theWrapper			Database wrapper.
	 * @param mixed					$theIdentifier		Root node identifier or object.
	 * @param string				$theLanguage		Default language code.
	 *
	 * @access protected
	 * @return TemplateStructure	Template structure object.
	 */
	protected function initTemplateStructure( Wrapper $theWrapper, $theRoot, $theLanguage )
	{
		return new TemplateStructure( $theWrapper, $theRoot, $theLanguage );		// ==>
		
	} // initTemplateStructure.

	 

} // class DataTemplate.


?>
