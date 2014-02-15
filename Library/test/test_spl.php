<?php
	
	//
	// Test iterators.
	//
	function multX10( &$theValue, $theKey )
	{
		if( ((int) $theKey) % 2 )
			$theValue = $theValue * 10;
	}

	$array = array( 1, 2, array( 6, 7, array( 8, 9 ) ) );
	var_dump( $array );
	
	array_walk_recursive( $array, 'multX10' );
	var_dump( $array );

exit;
	
	//
	// Test iterators.
	//
	function print_caps(Iterator $iterator)
	{
		$iterator->offsetSet( $iterator->key(), strtoupper($iterator->current()) );
		return TRUE;
	}

	$array = array( "Apples", "Bananas", "Cherries" );
	var_dump( $array );
	
	$it = new ArrayIterator($array);
	iterator_apply($it, "print_caps", array($it));
	var_dump( $array );

exit;
	
	//
	// Test iterators.
	//
	$array = array( 1, 2, array( 6, 7, array( 8, 9 ) ) );
	var_dump( $array );
	
	$iter = new ArrayObject( $array );
	foreach( $iter as $key => $value )
		$value= 0;;
	var_dump( $array );
exit;
	
	
	/*** a simple xml tree ***/
	 $xmlstring = <<<XML
<?xml version = "1.0" encoding="UTF-8" standalone="yes"?>
<document xmlns:spec="http://example.org/animal-species">
    <animal>
        <category id="26">
            <species>Phascolarctidae</species>
            <spec:name>Speed Hump</spec:name>
            <type>koala</type>
            <name>Bruce</name>
        </category>
    </animal>
    <animal>
        <category id="27">
            <species>macropod</species>
            <spec:name>Boonga</spec:name>
            <type>kangaroo</type>
            <name>Bruce</name>
        </category>
    </animal>
    <animal>
        <category id="28">
            <species>diprotodon</species>
            <spec:name>pot holer</spec:name>
            <type>wombat</type>
            <name>Bruce</name>
        </category>
    </animal>
    <animal>
        <category id="31">
            <species>macropod</species>
            <spec:name>Target</spec:name>
            <type>wallaby</type>
            <name>Bruce</name>
        </category>
    </animal>
    <animal>
        <category id="21">
            <species>dromaius</species>
            <spec:name>Road Runner</spec:name>
            <type>emu</type>
            <name>Bruce</name>
        </category>
    </animal>
    <animal>
        <category id="22">
            <species>Apteryx</species>
            <spec:name>Football</spec:name>
            <type>kiwi</type>
            <name>Troy</name>
        </category>
    </animal>
    <animal>
        <category id="23">
            <species>kingfisher</species>
            <spec:name>snaker</spec:name>
            <type>kookaburra</type>
            <name>Bruce</name>
        </category>
    </animal>
    <animal>
        <category id="48">
            <species>monotremes</species>
            <spec:name>Swamp Rat</spec:name>
            <type>platypus</type>
            <name>Bruce</name>
        </category>
    </animal>
    <animal>
        <category id="4">
            <species>arachnid</species>
            <spec:name>Killer</spec:name>
            <type>funnel web</type>
            <name>Bruce</name>
            <legs>8</legs>
        </category>
    </animal>
</document>
XML;

	/*** a new simpleXML iterator object ***/
	try
	{
		/*** a new simpleXML iterator object ***/
		$sxi =new SimpleXMLIterator($xmlstring);
		
		/*** register namespace ***/
		$sxi-> registerXPathNamespace('spec', 'http://www.exampe.org/species-title');
		
		/*** set the xpath ***/
		$foo = $sxi->xpath('animal/category/species');

		/*** iterate over the xpath ***/
		foreach ($foo as $k=>$v)
		{
			echo $v.'<br />';
		}
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}
	
	echo('<hr>' );
	
function array_flatten_recursive($array) { 
    if($array) { 
        $flat = array(); 
        foreach(new RecursiveIteratorIterator(new RecursiveArrayIterator($array), RecursiveIteratorIterator::SELF_FIRST) as $key=>$value) { 
            if(!is_array($value)) { 
                $flat[] = $value; 
            } 
        } 
        
        return $flat; 
    } else { 
        return false; 
    } 
} 

$array = array( 
    'A' => array('B' => array( 1, 2, 3, 4, 5)), 
    'C' => array( 6,7,8,9) 
);
var_dump($array);

var_dump(array_flatten_recursive($array)); 
	

?>