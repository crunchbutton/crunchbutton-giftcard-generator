<?php

$files = array( 'orders.txt', 'location.txt', 'order.txt', 'restaurant.txt');

$unUsedStyles = [];

foreach( $files as $csslist ){
	$file = @fopen( $csslist, "r" ); 
	while (! feof( $file ) ){
		$styles = [];
		$currentLine = fgets( $file );
		$currentLine = trim( $currentLine );
		$styles = multiexplode( array( ',' ), $currentLine );
		foreach( $styles as $style ){
			$style = trim( $style );
			if( $style == '' ){
				continue;
			}
			// $unUsedStyles[ $style ] = 1;
			if( array_key_exists( $style, $unUsedStyles) ){
				$unUsedStyles[ $style ]++;
			} else {
				$unUsedStyles[ $style ] = 1; 
			}
		}
	}   
	fclose($file) ;
}

arsort( $unUsedStyles );

echo '<pre>';
foreach( $unUsedStyles as $style => $val ){
	if( $val > 1 ){
		echo $val;
		echo ' - ';
		echo $style;
		echo '<br>';
	}
}

function multiexplode ($delimiters,$string) {
	$ready = str_replace($delimiters, $delimiters[0], $string);
	$launch = explode($delimiters[0], $ready);
	return  $launch;
}

?>