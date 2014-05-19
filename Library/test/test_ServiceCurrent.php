<?php

//
// Init local storage.
//
$base_url = 'http://pgrdg.grinfo.private/Service.php';

//
// Autocomplete.
//
echo( "<h3>L'utente sta cercando il campo <em>entity type</em></h3>" );
echo( "URL:" );
$url = "$base_url?op=matchTagLabels&lang=en&param=...";
var_dump( $url );
echo( "Parametri:" );
$param = array( 'limit' => 100,
				'pattern' => 'entity type',
				'operator' => array( '$CX', '$i' ) );
var_dump( $param );
echo( "Risposta:" );
$response = file_get_contents( "$base_url?op=matchTagLabels&lang=en&param=".urlencode( json_encode( $param ) ) );
$response = json_decode( $response, TRUE );
var_dump( $response );

//
// Cerca campo.
//
echo( "<h3>L'utente ha selto il campo <em>Entity type</em></h3>" );
$label = $response[ 'results' ][ 0 ];
echo( 'Label:' );
var_dump( $label );
echo( "URL:" );
$url = "$base_url?op=matchTagByLabel&lang=en&param=...";
var_dump( $url );
echo( "Parametri:" );
$param = array( 'limit' => 100,
				'pattern' => $label,
				'operator' => array( '$EQ' ) );
var_dump( $param );
echo( "Risposta:" );
$response = file_get_contents( "$base_url?op=matchTagByLabel&lang=en&param=".urlencode( json_encode( $param ) ) );
$response = json_decode( $response, TRUE );
var_dump( $response );

//
// Mostra campo.
//
echo( "<h3>L'utente riceve il campo <em>':type:entity'</em></h3>" );
echo( 'Numero di campi:' );
$count = count( $response[ 'dictionary' ][ 'ids' ] );
var_dump( $count );
echo( 'Tipo del campo:' );
$type = $response[ 'results' ]
				 [ $response[ 'dictionary' ][ 'collection' ] ]
				 [ $response[ 'dictionary' ][ 'ids' ][ 0 ] ]	// Questo è il campo da ciclare.
				 [ '25' ];
var_dump( $type );
echo( 'Kind del campo:' );
$type = $response[ 'results' ]
				 [ $response[ 'dictionary' ][ 'collection' ] ]
				 [ $response[ 'dictionary' ][ 'ids' ][ 0 ] ]	// Questo è il campo da ciclare.
				 [ '26' ];
var_dump( $type );

//
// Recupera le enumerazioni.
//
echo( "<h3>Il tipo è enumerazione: bisogna recuperare le enumerazioni</h3>" );
echo( 'Il tag da inviare al servizio recuperi enumerazioni:' );
$id = $response[ 'dictionary' ][ 'ids' ][ 0 ];	// Questo è il primo.
var_dump( $id );
echo( "URL:" );
$url = "$base_url?op=getTagEnumerations&lang=en&param=...";
var_dump( $url );
echo( "Parametri:" );
$param = array( 'tag' => $id );
var_dump( $param );
echo( "Risposta:" );
$response = file_get_contents( "$base_url?op=getTagEnumerations&lang=en&param=".urlencode( json_encode( $param ) ) );
$response = json_decode( $response, TRUE );
var_dump( $response );

//
// Cosa fare con le enumerazioni?
//
echo( "<h3>Hai ricevuto le enumerazioni a livello root: sono 4 elementi:</h3>" );
echo( "<h3>La select va costruita così:<ul><li>Se l'elemento ha 'children' maggiore di zero:"
	 ."<ul><li>Mettere triangolo (accordeon)</ul><li>Se l'elemento ha 'value' TRUE:"
	 ."<ul><li>Mettere la checkbox</ul></ul></h3>" );
echo( "<h3>Se l'utente apre il triangolo:</h3>" );
echo( 'Il nodo da inviare al servizio recuperi enumerazioni:' );
$node = $response[ 'results' ][ 0 ][ 'node' ];
var_dump( $node );
echo( "URL:" );
$url = "$base_url?op=getNodeEnumerations&lang=en&param=...";
var_dump( $url );
echo( "Parametri:" );
$param = array( 'node' => $node );
var_dump( $param );
echo( "Risposta:" );
$response = file_get_contents( "$base_url?op=getNodeEnumerations&lang=en&param=".urlencode( json_encode( $param ) ) );
$response = json_decode( $response, TRUE );
var_dump( $response );

//
// Cosa fare con le enumerazioni delle enumerazioni?
//
echo( "<h3>E così via...</h3>" );

?>
