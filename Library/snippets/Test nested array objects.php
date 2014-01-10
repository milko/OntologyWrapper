<?php
	
	//
	// Test nested ArrayObjects.
	//
	
	//
	// Create test data.
	//
	echo( "\nCreate test data\n" );
	echo( '$x = new ArrayObject();'."\n" );
	echo( '$x[2][3][4] = "PIPPO";'."\n" );
	$x = new ArrayObject();
	$x[2][3][4] = 'PIPPO';
	
	var_dump( $x );
	
	//
	// Delete element.
	//
	echo( "\nDelete element\n" );
	echo( 'unset( $x[2][3][4] );'."\n" );
	unset( $x[2][3][4] );
	
	var_dump( $x );
	
	//
	// Set element.
	//
	echo( "\nSet element\n" );
	echo( '$x[2][3][4] = "BABA";'."\n" );
	$x[2][3][4] = 'BABA';
	
	var_dump( $x );
	
	//
	// Modify sub-element in array.
	//
	echo( "\nModify sub-element in array\n" );
	echo( '$z = $x[2][3];'."\n" );
	echo( '$z[] = "cacca";'."\n" );
	$z = $x[2][3];
	$z[] = 'cacca';
	
	var_dump( $x );
	
	echo( "\n===================================================================\n" );
		
	//
	// Test with array objects.
	//
	echo( "\nCreate array object data\n" );
	echo( '$x = new ArrayObject();'."\n" );
	echo( '$x[2] = new ArrayObject( array( 3 => new ArrayObject( array( 4 => "PIPPO" ) ) ) );'."\n" );
	$x = new ArrayObject();
	$x[2] = new ArrayObject( array( 3 => new ArrayObject( array( 4 => 'PIPPO' ) ) ) );
	
	var_dump( $x );
	
	//
	// Delete element.
	//
	echo( "\nDelete element\n" );
	echo( 'unset( $x[2][3][4] );'."\n" );
	unset( $x[2][3][4] );
	
	var_dump( $x );
	
	//
	// Set element.
	//
	echo( "\nSet element\n" );
	echo( '$x[2][3][4] = "BABA";'."\n" );
	$x[2][3][4] = 'BABA';
	
	var_dump( $x );

	//
	// Modify sub-element in array object.
	//
	echo( "\nModify sub-element in array object\n" );
	echo( '$z = $x[2][3];'."\n" );
	echo( '$z[] = "cacca";'."\n" );
	$z = $x[2][3];
	$z[] = 'cacca';
	
	var_dump( $x );

	
?>
