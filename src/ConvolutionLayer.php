<?php
require_once 'src/Layer.php';

class ConvolutionLayer implements Layer {
	
	/**
	 * The size (equal height and width) of the filter.
	 * @var integer
	 */
	private $size;
	
	/**
	 * The depth of the layer, i.e. the number of channels its input contain.
	 * @var integer 
	 */
	private $depth;
	
	/**
	 * The filters of the convolution layer.
	 * Each filter is an array of {depth} kernels, one for each channel of the input.
	 * Each kernel is a 2D-array of numbers, {size}x{size}.
	 * @var array
	 */
	private $filters;
	
	public function backprop( array $d_L_d_out, $learn_rate ){
		// TODO
	}

	public function forward( array $input ){
		foreach( $this->filters as $filter ){
			$out[] = $this->conv( $input, $filter ); // TODO: manage biases !
		}
		return $out;
	}
	
	/**
	 * Perform a convolution with the given filter on the given input and return the resulting feature map.
	 * 
	 * @param array $input An array contaning n channels. Each channel is a 2d-array of numbers.
	 * @param array $filter An array containing n kernels. Each kernel is a 2d-array of number.
	 * @param number bias The bias to apply to the convolution
	 * @return array a feature map, the same size as every input channel
	 */
	function conv( array $input, array $filter, $bias = 0 ){
		if( count($input) != count($filter) ){
			trigger_error("Depth of the given input (i.e. number of channels) must be the same as the depth of the filter (i.e. number of kernels)", E_USER_ERROR);
		}
		
		// depth
		$d = count($input);
		
		// add padding to each channel (so that the resulting feature map will be the same size)
		for( $i=0; $i<$d; $i++ ){
			$input[$i] = matrix_padding( $input[$i], 1 );
		}
		
		// height and width of each channel of the input
		$H = count( $input[0] );
		$W = count( $input[0][0] );
		
		// for each region of the channels, apply kernels and bias
		for( $y=0; $y<=$H-$this->size; $y++ ){
			for( $x=0; $x<=$W-$this->size; $x++ ){
				$t = $bias;
				for( $i=0; $i<$d; $i++ ){
					$t += rdotp( $filter[$i], matrix_sub($input[$i], $x, $y, $this->size, $this->size) );
				}
				$fmap[$y][$x] = $t;
			}
		}
		
		return $fmap;
	}
	
	/**
	 * Instanciate a convolution layer.
	 */
	public static function create( $n_filters, $depth, $size, array $kernel_model ){
		$instance = new self();
		
		// TODO: add controls !
		
		$instance->size = $size;
		$instance->depth = $depth;
		
		// TODO: change filter init (currently, only 1 filter is set, $n_filters is ignored)
		$instance->filters = [[]];
		for( $i=0; $i<$depth; $i++ ){
			$instance->filters[0][] = $kernel_model; // TODO: remove kernel_model or make it optional
		}
				
		return $instance;
	}
	
	/**
	 * Instanciate a convolution layer from the given configuration.
	 * 
	 * @param array $conf The layer configuration.
	 * @return ConvolutionLayer The layer
	 */
	public static function fromConf( array $conf ){
		// TODO
	}

}