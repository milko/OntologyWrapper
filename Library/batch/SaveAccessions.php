<?php

/**
 * Save accession records.
 *
 * This file contains the script to save accession records from one database dictionary to
 * another database dictionary, the resulting objects will have the destination dictionary
 * offsets.
 *
 *	@package	OntologyWrapper
 *	@subpackage	Init
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 23/03/2015
 */

/*=======================================================================================
 *																						*
 *									SaveAccessions.php									*
 *																						*
 *======================================================================================*/

//
// Global includes.
//
require_once( 'includes.inc.php' );

//
// Local includes.
//
require_once( 'local.inc.php' );

//
// Tag definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Tags.inc.php" );

//
// Predicate definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Predicates.inc.php" );

//
// Session definitions.
//
require_once( kPATH_DEFINITIONS_ROOT."/Session.inc.php" );


/*=======================================================================================
 *	TEST																				*
 *======================================================================================*/

//
// Parse arguments.
//
if( $argc != 5 )
	exit( "Usage: "
		 ."script.php "
		 ."[source mongo client DSN] "			// mongodb://localhost:27017
		 ."[source mongo database name] "		// PGRDG
		 ."[destination mongo client DSN] "		// mongodb://localhost:27017
		 ."[destination database name.\n" );	// BIOVERSITY
 
//
// Try.
//
try
{
	//
	// Inform.
	//
	echo( "\n==> Copy accessions.\n" );
	echo( "  • Connecting databases.\n" );
	
	//
	// Connect source.
	//
	$src_client = new MongoClient( $argv[ 1 ] );
	$src_db = $src_client->selectDB( $argv[ 2 ] );
	
	//
	// Connect destination.
	//
	$dst_client = new MongoClient( $argv[ 3 ] );
	$dst_db = $src_client->selectDB( $argv[ 4 ] );
	
	//
	// Instantiate data dictionary.
	//
	$wrapper
		= new OntologyWrapper\Wrapper(
			kSESSION_DDICT,
			array( array( 'localhost', 11211 ) ) );
	
	//
	// Inform.
	//
	echo( "  • Connecting wrapper.\n" );
	$mongo
		= new OntologyWrapper\MongoDatabase(
			$argv[ 3 ].'/'.$argv[ 4 ]."?connect=1" );
	$wrapper->metadata( $mongo );
	$wrapper->units( $mongo );
	$wrapper->users( $mongo );
	
	//
	// Load data dictionary.
	//
	if( ! $wrapper->dictionaryFilled() )
		$wrapper->loadTagCache();
	
	//
	// Resolve collection.
	//
	$collection
		= OntologyWrapper\UnitObject::ResolveCollection(
			OntologyWrapper\UnitObject::ResolveDatabase(
				$wrapper ) );
	
	//
	// Iterate source records.
	//
	$rs =
		$src_db->selectCollection( '_units' )
			->find( array( kTAG_DOMAIN => ':domain:accession' ) );
//	$batch = new MongoInsertBatch( $dst_db->selectCollection( 'ACCESSIONS' ) );
//	$counter = 1000;
	foreach( $rs as $record )
	{
		//
		// Save source tags.
		//
		$src_offsets = Array();
		foreach( $record[ kTAG_OBJECT_OFFSETS ] as $offset )
		{
			$tmp = explode( '.', $offset );
			foreach( $tmp as $element )
				$src_offsets[ $element ] = $element;
		}
		
		//
		// Collect remaining tags.
		//
		foreach( array_keys( $record ) as $offset )
		{
			if( substr( $offset, 0, 1 ) == '@' )
				$src_offsets[ $offset ] = $offset;
		}
		
		//
		// Get source tag definitions.
		//
		$rs_tag
			= $src_db->selectCollection( '_tags' )
				->find( array( kTAG_ID_HASH
							=> array( '$in' => array_values( $src_offsets ) ) ),
						array( kTAG_ID_HASH
							=> TRUE ) );
		foreach( $rs_tag as $tag )
			$src_offsets[ $tag[ kTAG_ID_HASH ] ] = $tag[ kTAG_NID ];
		
		//
		// Get destination tag definitions.
		//
		$dst_offsets = Array();
		$rs_tag
			= $dst_db->selectCollection( '_tags' )
				->find( array( kTAG_NID
							=> array( '$in' => array_values( $src_offsets ) ) ),
						array( kTAG_ID_HASH
							=> TRUE ) );
		foreach( $rs_tag as $tag )
			$dst_offsets[ $tag[ kTAG_NID ] ] = $tag[ kTAG_ID_HASH ];
		
		//
		// Check matches.
		//
		if( count( $src_offsets ) != count( $dst_offsets ) )
			throw new exception( "Source and destination tags cout don\'t match." );
		
		//
		// Remove dynamic fields.
		//
		foreach( OntologyWrapper\Accession::DynamicOffsets() as $offset )
			unset( $record[ $offset ] );
		
		//
		// Traverse object.
		//
		$dest_rec = Array();
		foreach( $record as $key => $value )
			SetProperty( $dest_rec, $key, $value, $src_offsets, $dst_offsets );
		
		//
		// Write record.
		//
		$object = new OntologyWrapper\Accession( $dest_rec );
		$object->commit( $wrapper );
		
// 		$batch->add( $dest_rec );
// 		if( ! $counter-- )
// 		{
// 			$batch->execute( array( "w" => 1 ) );
// 			$counter = 1000;
// 		}
	}
	
	//
	// Write record.
	//
//	if( ! $counter )
//		$batch->execute( array( "w" => 1 ) );
}

//
// Catch exceptions.
//
catch( \Exception $error )
{
	echo( $error->xdebug_message );
	print_r( $error->getTrace() );
}

echo( "\nDone!\n" );


/*=======================================================================================
 *	FUNCTIONS																			*
 *======================================================================================*/

	/**
	 * Set property.
	 *
	 * This function will set the property with the destination offset.
	 *
	 * @param array				   &$theContainer		Receives property.
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value.
	 * @param array					$$theSrcOffsets		Source offsets.
	 * @param array					$$theDstOffsets		Destination offsets.
	 */
	function SetProperty( &$theContainer,
						   $theOffset, $theValue,
						   $theSrcOffsets, $theDstOffsets )
	{
		//
		// Translate offset.
		//
		$dst_offset = ( substr( $theOffset, 0, 1 ) != '@' )
					? $theOffset
					: $theDstOffsets[ $theSrcOffsets[ $theOffset ] ];
		
		//
		// Handle arrays.
		//
		if( is_array( $theValue ) )
		{
			$theContainer[ $dst_offset ] = Array();
			foreach( $theValue as $key => $value )
				SetProperty( $theContainer[ $dst_offset ],
							 $key, $value,
							 $theSrcOffsets, $theDstOffsets );
		}
		
		//
		// Handle scalar.
		//
		else
			$theContainer[ $dst_offset ] = $theValue;
		
	} // SetProperty.


?>
