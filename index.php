<?php
require_once 'src/utils.php';
require_once 'src/ConvolutionFilter.php';
require_once 'src/Normalization.php';
require_once 'src/MSELoss.php';

define('R', 0);
define('G', 1);
define('B', 2);

$sonic = jpg2rgb( "./resources/sonic.jpg" );
//$bw_sonic = jpg2rgb( "./resources/sonic.jpg", IMG_FILTER_GRAYSCALE );
//echo rgb2str( $sonic );
//rgb2png($bw_sonic, "test.png");

// Test rgb_flatten
//$flat_sonic = rgb_flatten($sonic);
//echo fmap2str($flat_sonic);
//echo fmap2str( fmap_sub( $flat_sonic, 19, 22, 3, 3));

// apply convolution to an image
// Sobel filter (vertical)
$sobel_vertical = [
	[-1, 0, 1],
	[-2, 0, 2],
	[-1, 0, 1]
];
$sobel_horizontal = [
	[-1, -2, -1],
	[0, 0, 0],
	[1, 2, 1]
];
$box_blur = [
	[1/9, 1/9, 1/9],
	[1/9, 1/9, 1/9],
	[1/9, 1/9, 1/9]
];

// for each "feature map"/channel of an image (each color)
foreach($sonic as $color){
	$sonic_f[] = ConvolutionFilter::applyKernel($color, $sobel_vertical);
}
$normalization = Normalization::create(0, 255, true);
$final = $normalization->forward( $sonic_f );
rgb2jpg($final, "test.jpg");

$true = [
	[0, 1, 2],
	[0, 1, 1],
	[2, 1, 2]
];
$pred = [
	[-1, 1, 3],
	[1, 1, 2],
	[2, 1, 1]
];	
echo( matrix2str( (new MESLoss())->rMSE_d1( $pred, $true ), 2) );


function fmap2rgb( array $fmaps ){
	return [
		'r' => $fmaps[0],
		'g' => $fmaps[1],
		'b' => $fmaps[2]
	];
}

function fmap_sub( array $fmap, $x, $y, $width, $height ){
	$sub = [];
	for( $r=$y; $r<$y+$height; $r++ ){
		for( $c=$x; $c<$x+$width; $c++ ){
			$sub[$r-$y][$c-$x] = $fmap[$r][$c];
		}
	}
	return $sub;
}

function int2rgb( $intval ){
	$rgb[R] = ($intval >> 16) & 0xFF;
	$rgb[G] = ($intval >> 8) & 0xFF;
	$rgb[B] = $intval & 0xFF;
	return $rgb;
}

function image2rgb( $image, $width, $height, $pre_filter = null ){
	if( $pre_filter != null ){
		imagefilter( $image, $pre_filter );
	}
	$rgbs = [];
	for( $y=0; $y<$height; $y++ ){
		for( $x=0; $x<$width; $x++ ){
			$rgb = int2rgb(imagecolorat($image, $x, $y));
			$rgbs[R][$y][$x] = $rgb[R];
			$rgbs[G][$y][$x] = $rgb[G];
			$rgbs[B][$y][$x] = $rgb[B];
		}
	}
	return $rgbs;
}

function jpg2rgb( $filepath, $pre_filter = null ){
	$img = imagecreatefromjpeg( $filepath );
	$size = getimagesize( $filepath );
	$W = $size[0];
	$H = $size[1];
	return image2rgb( $img, $W, $H, $pre_filter );
}

function png2rgb( $filepath, $pre_filter = null ){
	$img = imagecreatefrompng( $filepath );
	$size = getimagesize( $filepath );
	$W = $size[0];
	$H = $size[1];
	return image2rgb( $img, $W, $H, $pre_filter );
}

function rgb_flatten( array $rgbs ){
	$fmap = [];
	$H = rgb_height($rgbs);
	$W = rgb_width($rgbs);
	for( $y=0; $y<$H; $y++ ){
		for( $x=0; $x<$W; $x++ ){
			$red = $rgbs[R];
			$grn = $rgbs[G];
			$blu = $rgbs[B];
			$fmap[$y][$x] = ceil(($red[$y][$x] + $grn[$y][$x] + $blu[$y][$x])/3);
		}
	}
	return $fmap;
}

function rgb2str( array $rgbs ){
	$H = rgb_height($rgbs);
	$W = rgb_width($rgbs);
	for( $y=0; $y<$H; $y++ ){
		$row = [];
		for( $x=0; $x<$W; $x++ ){
			$red = $rgbs[R];
			$grn = $rgbs[G];
			$blu = $rgbs[B];
			$row[] = str_pad($red[$y][$x].'|'.$grn[$y][$x].'|'.$blu[$y][$x], 11);
		}
		$rows[] = '['.implode(" ", $row).']';
	}
	return implode( PHP_EOL, $rows ).PHP_EOL;
}

function rgb2jpg( array $rgbs, $filename ){
	imagejpeg( rgb2image( $rgbs ), $filename );
}

function rgb2png( array $rgbs, $filename ){
	imagepng( rgb2image( $rgbs ), $filename );
}

function rgb2image( array $rgbs ){
	$H = rgb_height($rgbs);
	$W = rgb_width($rgbs);
	$image = imagecreatetruecolor($W, $H);
	for( $y=0; $y<$H; $y++ ){
		for( $x=0; $x<$W; $x++ ){
			$red = $rgbs[R];
			$grn = $rgbs[G];
			$blu = $rgbs[B];
			$color = imagecolorallocate($image, $red[$y][$x], $grn[$y][$x], $blu[$y][$x]);
			imagesetpixel($image, $x, $y, $color);
		}
	}
	return $image;
}

function rgb_height( array $rgbs ){
	return count($rgbs[R]);
}

function rgb_width( array $rgbs ){
	return count($rgbs[R][0]);
}

