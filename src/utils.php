<?php
function error( $message ){
	trigger_error( $message, E_USER_ERROR );
	exit;
}

/**
 * Convert a matrix (2D-array of non-array elements) to a readable string.
 * 
 * @param array $matrix
 * @return string
 */
function matrix2str( array $matrix, $cell_pad = 0 ){
	$H = count($matrix);
	$W = count($matrix[0]);
	for( $y=0; $y<$H; $y++ ){
		$row = [];
		for( $x=0; $x<$W; $x++ ){
			$row[] = str_pad($matrix[$y][$x], $cell_pad);
		}
		$rows[] = '['.implode(" ", $row).']';
	}
	return implode( PHP_EOL, $rows ).PHP_EOL;
}

/**
 * Add a padding on the edges of a 2D-array.
 * 
 * @param array $matrix
 * @param integer $padding_width the width of the padding
 * @param mixed $padding_value The value to insert into the added cells
 * @return array The padded matrix
 */
function matrix_padding( array $matrix, $padding_width = 1, $padding_value = 0 ){
	$H = count($matrix);
	$W = count($matrix[0]);
	for( $y=0; $y<$H; $y++ ){
		for( $n=0; $n<$padding_width; $n++ ){
			array_unshift($matrix[$y], $padding_value );
			$matrix[$y][] = $padding_value;
		}
	}
	$empty_line = array_fill(0, $W + 2*$padding_width, $padding_value);
	for( $n=0; $n<$padding_width; $n++ ){
		array_unshift($matrix, $empty_line);
		$matrix[] = $empty_line;
	}
	return $matrix;
}

/**
 * Revert the matrix given in input: first revert the order of the cells of each 
 * line of the matrix, then revert the ordre of the lines.
 * Like a symetry centered on the center of the matrix.
 * 
 * @param array $matrix the 2d-array to revert
 * @return array the reverted matrix
 */
function matrix_reverse( array $matrix ){
	for( $i=0, $maxi=count($matrix); $i<$maxi; $i++ ){
		$matrix[$i] = array_reverse($matrix[$i]);
	}
	return array_reverse($matrix);
}

/**
 * Extract a sub-matrix from the given matrix (2D-array).
 * 
 * @param array $matrix
 * @param integer $x the x coordinate of the top-left corner of the wanted sub-matrix
 * @param integer $y the y coordinate of the top-left corner of the wanted sub-matrix
 * @param integer $width The width of the wanted sub-matrix
 * @param integer $height The height of the wanted sub-matrix
 * @return array the sub-matrix as a 2-dimensional-array
 */
function matrix_sub( array $matrix, $x, $y, $width, $height ){
	$sub = [];
	for( $r=$y; $r<$y+$height; $r++ ){
		for( $c=$x; $c<$x+$width; $c++ ){
			$sub[$r-$y][$c-$x] = $matrix[$r][$c];
		}
	}
	return $sub;
}

function matrix_sum( array ...$matrixes ){
	if( count($matrixes) == 1 ){
		return $matrixes[0];
	}
	$result = $matrixes[0];
	for( $i=1, $maxi=count($matrixes); $i<$maxi; $i++ ){
		$result = rsum( $result, $matrixes[$i] );
	}
	return $result;
}

/**
 * Recursive dot-product of the given arrays.
 * Works with 1D and 2D arrays. 
 * Not tested with 3D-arrays, but should be working too...
 * 
 * @param array $array1
 * @param array $array2
 * @return number
 */
function rdotp( array $array1, array $array2 ){
	if( count($array1) != count($array2) ){
		trigger_error("the given arrays must have the same size", E_USER_ERROR);
	}
	if( count($array1) == 0 ){
		trigger_error("the given arrays must not be empty", E_USER_ERROR);
	}
	if( is_numeric($array1[0]) && is_numeric($array2[0]) ){
		return array_sum( array_map( function($x, $y){ return $x * $y; }, $array1, $array2 ) );
	}
	else {
		$product = 0;
		for( $i=0, $maxi=count($array1); $i<$maxi; $i++ ){
			$product += rdotp( $array1[$i], $array2[$i] );
		}
		return $product;
	}
}

/**
 * Recursive max function.
 * 
 * @param array $array any n-dimensional array (n>=1)
 * @return number The maximal value contained in the given array.
 */
function rmax( array $array ){
	if( is_numeric( $array[0] ) ){
		return max( $array );
	}
	$max = rmax( $array[0] );
	for( $i=1, $maxi=count($array); $i<$maxi; $i++ ){
		$max = max( $max, rmax( $array[$i] ) );
	}
	return $max;
}

/**
 * Recursive min function.
 * 
 * @param array $array any n-dimensional array (n>=1)
 * @return number The minimal value contained in the given array.
 */
function rmin( array $array ){
	if( is_numeric( $array[0] ) ){
		return min( $array );
	}
	$min = rmin( $array[0] );
	for( $i=1, $maxi=count($array); $i<$maxi; $i++ ){
		$min = min( $min, rmin( $array[$i] ) );
	}
	return $min;
}

function rsum( array $array1, array $array2 ){
	if( count($array1) != count($array2) ){
		trigger_error("Given arrays must be of the same size", E_USER_ERROR);
	}
	if( is_array($array1[0]) ){
		// We deal with an arrays of arrays
		$sum = [];
		for( $i=0, $maxi=count($array1); $i<$maxi; $i++ ){
			$sum[] = rsum( $array1[$i], $array2[$i] );
		}
		return $sum;
	}
	else {
		return array_map( function( $a, $b ){ return $a + $b; }, $array1, $array2 );
	}
}