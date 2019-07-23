<?php
require_once 'src/utils.php';

class ConvolutionFilter {
	
	/**
	 * The height of the filter.
	 * @var integer
	 */
	private $height;
	
	/**
	 * The width of the filter.
	 * @var integer
	 */
	private $width;
	
	/**
	 * The kernels of the filter, stored as an array of {depth} size, 
	 * one for each channel of the input.
	 * Each kernel is a 2D array of numbers.
	 * @var array 
	 */
	private $kernels;
	
	/**
	 * The bias of the filter.
	 * @var number 
	 */
	private $bias;
	
	function __construct( $height, $width, $kernels, $bias ){
		$this->height = $height;
		$this->width = $width;
		$this->kernels = $kernels;
		$this->bias = $bias;
	}
	
	/**
	 * Apply the filter to the given input.
	 * 
	 * @param array $input an array of {nb_channels} size, containing a 2D-arrays of numbers.
	 * @return array a 2D-array containing the result of the convolution.
	 */
	public function forward( array $input ){
		if( count($input) != count($this->kernels) ){
			trigger_error("Depth of the given input (i.e. number of channels) must be the same as the depth of the filter (i.e. number of kernels)", E_USER_ERROR);
		}
		
		// depth
		$d = count($input);
		
		// add padding to each feature map
		for( $i=0; $i<$d; $i++ ){
			$input[$i] = matrix_padding( $input[$i], 1 );
		}
		
		// height and width of each feature map contained into the input
		$H = count( $input[0] );
		$W = count( $input[0][0] );
		
		// for each region of the feature maps, apply kernels and bias
		for( $y=0; $y<=$H-$this->height; $y++ ){
			for( $x=0; $x<=$W-$this->width; $x++ ){
				$t = $this->bias;
				for( $i=0; $i<$d; $i++ ){
					$t += rdotp( $this->kernels[$i], matrix_sub($input[$i], $x, $y, $this->width, $this->height) );
				}
				$fmap[$y][$x] = $t;
			}
		}
		
		return $fmap;
	}
	
	/**
	 * Export the instance configuration as an array.
	 * @return array
	 */
	public function export(){
		// TODO
	}
	
	/**
	 * Instantiate the class with the given values.
	 */
	public static function create( $size, $depth = 1, $kernel_template = null ){
		// TODO
	}
	
	/**
	 * Instantiate the class from the given configuration.
	 */
	public static function fromConf( array $conf ){
		// TODO
	}
	
	public function applyKernel( array $input, array $kernel ){
		// apply 1 px padding
		$input = matrix_padding( $input, 1 );
		
		// apply kernel
		$H = count( $input );
		$W = count( $input[0] );
		$kh = count( $kernel );
		$kw = count( $kernel[0] );
		for( $y=0; $y<=$H-$kh; $y++ ){
			for( $x=0; $x<=$W-$kw; $x++ ){
				$fmap[$y][$x] = rdotp( $kernel, matrix_sub($input, $x, $y, $kw, $kh) );
			}
		}
		
		return $fmap;
	}
	
}