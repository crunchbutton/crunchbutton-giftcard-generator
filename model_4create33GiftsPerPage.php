<?php

// page 12 x 18

ini_set('max_execution_time', 300);
ini_set('memory_limit','1000M');

/*
	To create the gift cards the first thing we need to do is create a image with this gift card
	and after that we create a pdf file with this image inside.
	I've tried to create the pdf with the text embeded but it did not work because the font could must to be outlined.
	More info: http://us.moo.com/help/faq/using-my-own-artwork.html
*/

$value = 2;
$file = '001';

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
	// $image = imagecreatefrompng( 'assets/model_4/mini_giftcard_' . $value . '.png' );
	$image = imagecreatefrompng( 'assets/model_4/giftcard_back_3.png' );

	// Antialiases
	imagealphablending( $image, true );
	imageantialias( $image, true );

	// Set the colors
	$white = imagecolorallocate( $image, 255, 255, 255 );
	$lightBrown = imagecolorallocate( $image, 172, 169, 168 );

	// Put the texts

	if( strlen( $code ) > 6 ){
		// imagettftext( $image, 25, 0, 345, 235, $white, $fontOmnesBold, 'crunchbutton.com/gift/' . $code );
	} else {
		// imagettftext( $image, 26, 0, 345, 235, $white, $fontOmnesBold, 'crunchbutton.com/gift/' . $code );
	}

	// imagettftext( $image, 12, 0, 345, 275, $white, $fontOmnes, 'Or use our iPhone app and, in the Notes section of your order, enter: ' . $code );
	// imagettftext( $image, 11, 0, 80, 375, $lightBrown, $fontOmnes, 'Valid for $' . $value . ' off one order on crunchbutton.com for new users only. One per user. Has no cash value and is not a jelly donut.' );
	// imagettftext( $image, 11, 0, 80, 395, $lightBrown, $fontOmnes, 'Not valid for past orders. Will not be replaced if lost or stolen. May be canceled any time without notice.' );

	// imagesetthickness ( $image, 5 );

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
$pageWidth = 304;
$pageHeight = 457;

$giftCardWidth = 82;
$giftCardHeight = 40;

$collumns = 3;
$rows = 11;

$marginPageTop = 10;
$marginPageLeft = 30;

$marginTop = 0;
$marginLeft = 0;

$giftCards = [];

// Order in stacks
for ($i = 0; $i < $count; $i++) {
	$giftCards[ $i ] = 'temp/' . ( $i + 1 ) . '.png';
}

$slotsPerPage = ( $collumns * $rows );
$numberOfPages = ceil( $count / $slotsPerPage );

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
$giftCardWidthBleed = 1.5;
$giftCardHeightBleed = 1.5;
$pdf->SetLineWidth( 0.5 );
$pdf->SetDrawColor( 0, 0, 0 );
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

	$collumn++;
	if( $collumn > $collumns ){ $collumn = 1; }
	if( $collumn == 1 ){
		$row++;
	}

	$giftCardsOnThisPage++;

	if( $giftCardsOnThisPage == ( $collumns * $rows ) ){
		continue;
		$pdf->AddPage();
		$giftCardWidthBleed = 1.5;
		$giftCardHeightBleed = 1.5;
		$pdf->SetLineWidth( 0.5 );
		$pdf->SetDrawColor( 0, 0, 0 );
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

$filename =  'GiftCards-Value_' . $value . '-Total_' . $count . '_' . $file . '.pdf';

$pdf->Output( 'pdfs/' . $filename );
// $pdf->Output();

echo 'done: ' . $filename;
?>