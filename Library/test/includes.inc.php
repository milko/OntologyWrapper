<?php

/*=======================================================================================
 *																						*
 *									includes.inc.php									*
 *																						*
 *======================================================================================*/
 
/**
 *	Global include file.
 *
 *	This file should be included at the top level of the application or web site as the
 *	first entry, it includes the file paths to the relevant directories and the autoload
 *	function for this library.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Skofic <m.skofic@cgiar.org>
 *	@version	1.00 13/01/2014
 */

/*=======================================================================================
 *	NAMESPACE ROOT																		*
 *======================================================================================*/

/**
 * Library namespace root.
 *
 * This string indicates the root namespace name for this library.
 */
define( "kPATH_NAMESPACE_ROOT",	"OntologyWrapper" );

/*=======================================================================================
 *	LIBRARY PATHS																		*
 *======================================================================================*/

/**
 * Library root.
 *
 * This value defines the <b><i>absolute</i></b> path to the class library root directory.
 */
define( "kPATH_LIBRARY_ROOT",	"/Library/WebServer/Library/OntologyWrapper/Library" );

/**
 * Library definitions root.
 *
 * This value defines the <b><i>absolute</i></b> path to the class library definitions
 * directory.
 */
define( "kPATH_DEFINITIONS_ROOT",	kPATH_LIBRARY_ROOT."/definitions" );

/**
 * Library standards root.
 *
 * This value defines the <b><i>absolute</i></b> path to the library standards directory.
 */
define( "kPATH_STANDARDS_ROOT",	kPATH_LIBRARY_ROOT."/standards" );

/*=======================================================================================
 *	CLASS AUTOLOADER																	*
 *======================================================================================*/

/**
 * This section allows automatic inclusion of the library classes.
 */
function MyAutoload( $theClassName )
{
	//
	// Separate namespace elements.
	//
	$namespaces = explode( '\\', $theClassName );
	
	//
	// Handle our namespace.
	//
	if( $namespaces[ 0 ] == kPATH_NAMESPACE_ROOT )
	{
		//
		// Build path.
		//
		$path = kPATH_LIBRARY_ROOT;
		foreach( $namespaces as $namespace )
			$path .= "/$namespace";
		$path = "$path.php";
		
		//
		// Require class.
		//
		if( file_exists( $path ) )
			require_once( $path );
	
	} // This librarie's namespace.

} spl_autoload_register( 'MyAutoload' );

?>
