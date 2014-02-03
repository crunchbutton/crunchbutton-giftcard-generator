<?php

ini_set('max_execution_time', 300);
ini_set('memory_limit','1000M');

/*
	To create the gift cards the first thing we need to do is create a image with this gift card
	and after that we create a pdf file with this image inside.
	I've tried to create the pdf with the text embeded but it did not work because the font could must to be outlined.
	More info: http://us.moo.com/help/faq/using-my-own-artwork.html
*/

$value = 5;

// Txt file with the codes
$codes = file( 'codes.txt' );

// Fonts that will be used
$fontOmnesBold = 'assets/fonts/OmnesBold.otf';
$fontOmnes = 'assets/fonts/Omnes.otf';

$count = 1;
// Fist create all the images

foreach ( $codes as  $code ){

	$code = str_replace( array( "\r\n", "\r", "\n" ), '', $code );

	// First lets create the image
	$image = imagecreatefrompng( 'assets/model_3/mini_giftcard_' . $value . '.png' );
	// $image = imagecreatefrompng( 'assets/model_3/giftcard_back.png' );

	// Antialiases
	imagealphablending( $image, true );
	imageantialias( $image, true );

	// Set the colors
	$white = imagecolorallocate( $image, 255, 255, 255 );
	$lightBrown = imagecolorallocate( $image, 172, 169, 168 );

	// Put the texts
	imagettftext( $image, 26, 0, 315, 202, $white, $fontOmnesBold, 'crunchbutton.com/gift/' . $code );
	imagettftext( $image, 12, 0, 305, 255, $white, $fontOmnes, 'Or use our iPhone app and, in the Notes section of your order, enter: ' . $code );
	imagettftext( $image, 11, 0, 37, 355, $lightBrown, $fontOmnes, 'Valid for $' . $value . ' off one order on crunchbutton.com for new users only. One per user. Has no cash value and is not a jelly donut.' );
	imagettftext( $image, 11, 0, 37, 373, $lightBrown, $fontOmnes, 'Not valid for past orders. Will not be replaced if lost or stolen. May be canceled any time without notice.' );

	imagesetthickness ( $image, 5 );

	// Path where the image wil be saved
	$imgsrc = 'temp/' . $count . '.png';

	// header('Content-Type: image/png');
	// imagepng( $image );	
	// exit;

	imagepng( $image, $imgsrc );
	imagecolordeallocate( $image, $white );

	// Destroy the image
	imagedestroy( $image );

	$count++;
}

$count--;

//Second create the pdf
// PDF Library
require('lib/fpdf.php');

// Page size in millimetter - (Letter)
$pageWidth = 215;
$pageHeight = 279;

$giftCardWidth = 77; //74
$giftCardHeight = 35; // 31

$collumns = 2;
$rows = 7;

$marginPageTop = 15; //10
$marginPageLeft = 30; //32 

$marginTop = 0;
$marginLeft = 0;

$giftCards = [];

// Order in stacks
for ($i = 0; $i < $count; $i++) { 
	$giftCards[ $i ] = 'temp/' . ( $i + 1 ) . '.png';
}

$slotsPerPage = ( $collumns * $rows );
$numberOfPages = ceil( $count / $slotsPerPage );

$numberOfPages = 16;
$totalGifts = $count;
$giftsPerPosition = ceil( $totalGifts / $numberOfPages );
$left = $totalGifts % $numberOfPages;
$perPosition = array();
$giftCardsOrdered = array();
if( $left != 0 ){
	for( $i = 0; $i < $numberOfPages; $i++ ){
		if( $left > 0 ){
			$perPosition[ $i ] = $giftsPerPosition;
			$left--;
		} else {
			$perPosition[ $i ] = $giftsPerPosition - 1;
		}
	}
} else {
	for( $i = 0; $i <= $numberOfPages; $i++ ){
		$perPosition[ $i ] = $giftsPerPosition ;
	}
}
$startsAt = array();
$sum = 0;
for( $i = 0; $i < sizeof( $perPosition ); $i ++ ){
	$startsAt[ $i ] = $sum;
	$sum = $sum + $perPosition[ $i ];
}
for( $i = 0; $i < $giftsPerPosition; $i++ ){
	for( $j = 1; $j <= $numberOfPages; $j++ ){
		if( sizeof( $giftCardsOrdered ) < sizeof( $giftCards ) ){
			$index = $startsAt[ $j - 1 ] + $i;
			$giftCardsOrdered[] = $giftCards[ $index ];
		}
	}
}

// Create the pdf
$pdf = new FPDF( 'P', 'mm', array( $pageWidth, $pageHeight ) );

$pdf->AddPage();

$row = 1;
$collumn = 1;

$page = 1;
$giftCardsOnThisPage = 0;
$giftCardsOnThisRow = 0;

// Draw vertical the cut lines 
$giftCardWidthBleed = 2;
$giftCardHeightBleed = 2;
$pdf->SetLineWidth( 0.01 );
$pdf->SetDrawColor( 175, 175, 175 );
for( $j = 0; $j < $collumns; $j++ ){
	$lineStart = $marginPageLeft + ( $j * $giftCardWidth ) + $giftCardWidthBleed;
	$pdf->Line( $lineStart, 0, $lineStart, $pageHeight);	
	$lineEnd = $marginPageLeft + ( $j * $giftCardWidth ) + $giftCardWidth - $giftCardWidthBleed;
	$pdf->Line( $lineEnd, 0, $lineEnd, $pageHeight);
}
for( $j = 0; $j < $rows; $j++ ){
	$lineStart = $marginPageTop + ( $j * $giftCardHeight ) + $giftCardHeightBleed;
	$pdf->Line( 0, $lineStart, $pageWidth, $lineStart );
	$lineEnd = $marginPageTop + ( $j * $giftCardHeight ) + $giftCardHeight - $giftCardHeightBleed;
	$pdf->Line( 0, $lineEnd, $pageWidth, $lineEnd );
}

for ( $i = 0; $i < $count; $i++ ) { 
	
	$imgsrc = $giftCardsOrdered[ $i ];

	$positionY = ( ( ( $row - 1 ) * ( $giftCardHeight + $marginTop ) ) + $marginPageTop );
	$positionX = ( ( ( $collumn - 1 ) * ( $giftCardWidth + $marginLeft ) ) + $marginPageLeft );
	
	$pdf->Image( $imgsrc, $positionX, $positionY, -300, 'PNG' );

	$collumn = ( $collumn == 1 ) ? 2 : 1;
	if( $collumn == 1 ){
		$row++;	
	}
	
	$giftCardsOnThisPage++;
	
	if( $giftCardsOnThisPage == ( $collumns * $rows ) ){
		$pdf->AddPage();
		$giftCardWidthBleed = 2;
		$giftCardHeightBleed = 2;
		$pdf->SetLineWidth( 0.01 );
		$pdf->SetDrawColor( 175, 175, 175 );
		for( $j = 0; $j < $collumns; $j++ ){
			$lineStart = $marginPageLeft + ( $j * $giftCardWidth ) + $giftCardWidthBleed;
			$pdf->Line( $lineStart, 0, $lineStart, $pageHeight);	
			$lineEnd = $marginPageLeft + ( $j * $giftCardWidth ) + $giftCardWidth - $giftCardWidthBleed;
			$pdf->Line( $lineEnd, 0, $lineEnd, $pageHeight);
		}
		for( $j = 0; $j < $rows; $j++ ){
			$lineStart = $marginPageTop + ( $j * $giftCardHeight ) + $giftCardHeightBleed;
			$pdf->Line( 0, $lineStart, $pageWidth, $lineStart );
			$lineEnd = $marginPageTop + ( $j * $giftCardHeight ) + $giftCardHeight - $giftCardHeightBleed;
			$pdf->Line( 0, $lineEnd, $pageWidth, $lineEnd );
		}
		$giftCardsOnThisPage = 0;
		$row = 1;
		$collumn = 1;
	}
}



// $pdf->Output();
$pdf->Output( 'pdfs/GiftCards.pdf' );

echo 'done!';
?>