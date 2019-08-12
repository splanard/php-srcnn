<?php
require_once 'src/Layer.php';
require_once 'src/utils.php';

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
	 * The learn rate of the layer.
	 * @var number
	 */
	private $learnRate;
	
	/**
	 * The filters of the convolution layer.
	 * Each filter is an array of {depth} kernels, one for each channel of the input.
	 * Each kernel is a 2D-array of numbers, {size}x{size}.
	 * @var array
	 */
	private $filters;
	
	/**
	 * The biases of the convolution layer. One bias for each filter.
	 * @var array
	 */
	private $biases;
	
	/**
	 * The last processed input: a 3d-array, nb_channels x height x width.
	 * @var array 
	 */
	private $lastInput;
	
	public function backprop( array $d_L_d_out ){
		echo "back propagation of conv layer: ".$this->id().PHP_EOL;
		
		$outH = count($d_L_d_out[0]);
		$outW = count($d_L_d_out[0][0]);
		
		/*
		 * Calculate d_L_d_in
		 * dL/din = dL/dt * dt/din
		 */
		// for each input channel...
		$d_L_d_in = [];
		for( $c=0; $c<$this->depth; $c++ ){
			$bp_kernels = [];
			for( $f=0, $maxf=count($this->filters); $f<$maxf; $f++ ){
				$bp_kernels[] = matrix_reverse( $this->filters[$f][$c] );
			}
			$d_L_d_in[] = $this->conv($d_L_d_out, $bp_kernels);
		}
		
		// for each filter...
		for( $f=0, $maxf=count($this->filters); $f<$maxf; $f++ ){
			// dL/dbi
			$d_L_d_bi = 0;
			for( $j=0; $j<$outH; $j++ ){
				for( $i=0; $i<$outW; $i++ ){
					$d_L_d_bi += $d_L_d_out[$f][$j][$i];
				}
			}
			// update bi
			$this->biases[$f] -= $this->learnRate * $d_L_d_bi;
			
			// for each kernel...
			for( $k=0; $k<$this->depth; $k++ ){
				// (x,y) the position of wi in the kernel
				for( $y=0; $y<$this->size; $y++ ){
					for( $x=0; $x<$this->size; $x++ ){
						// dL/dwi
						$d_L_d_wi = 0;	
						$pad = ($this->size-1)/2;
						for( $j=max(0,$y-$pad); $j<=min($outH-1,$outH+$pad-$this->size+$y); $j++ ){
							for( $i=max(0,$x-$pad); $i<=min($outW-1,$outW+$pad-$this->size+$x); $i++ ){
								$d_L_d_wi += $d_L_d_out[$f][$j][$i] * $this->lastInput[$k][$j][$i];
							}
						}
						// WARNING: activation function derivative is ignored here...
						
						// update wi
						$this->filters[$f][$k][$y][$x] -= $this->learnRate * $d_L_d_wi;
					}
				}
			}
		}
		
		return $d_L_d_in;
	}

	public function forward( array $input ){
		echo "feed forwaring of conv layer: ".$this->id().PHP_EOL;
		if( count($this->filters) != count($this->biases) ){
			trigger_error("Wrong parametering of the convolution layer: there must be as many filters as biases", E_USER_ERROR);
		}
		$this->lastInput = $input;
		for( $i=0, $maxi=count($this->filters); $i<$maxi; $i++ ){
			$out[] = $this->conv( $input, $this->filters[$i], $this->biases[$i] );
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
			$msg = "Depth of the given input (i.e. number of channels) must be the same as the depth of the filter (i.e. number of kernels)".PHP_EOL
					."\$input: ".count($input).PHP_EOL
					."\$filter: ".count($filter);
			trigger_error($msg, E_USER_ERROR);
		}
		
		// depth
		$d = count($input);
		
		// add padding to each channel (so that the resulting feature map will be the same size)
		for( $i=0; $i<$d; $i++ ){
			$input[$i] = matrix_padding( $input[$i], ($this->size-1)/2 );
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
				$result[$y][$x] = $t;
			}
		}
		
		return $result;
	}
	
	/**
	 * Instanciate a convolution layer.
	 */
	public static function create( $n_filters, $depth, $size, 
			$learn_rate, array $kernel_model = null ){
		$instance = new self();
		if( $size%2 == 0 ){
			trigger_error("filter size must be an odd number", E_USER_ERROR);
		}
		
		$instance->size = $size;
		$instance->depth = $depth;
		$instance->learnRate = $learn_rate;
		
		$instance->filters = [];
		$instance->biases = [];
		for( $f=0; $f<$n_filters; $f++ ){
			$instance->biases[] = nrand(0, 0);
			for( $d=0; $d<$depth; $d++ ){
				if( isset( $kernel_model ) ){
					$instance->filters[$f][] = $kernel_model;
				}
				else {
					$kernel = [];
					for( $y=0; $y<$size; $y++ ){
						for( $x=0; $x<$size; $x++ ){
							//$kernel[$y][$x] = nrand(0,1) / pow($size, 2);
							$kernel[$y][$x] = nrand(0,0.001);
						}
					}
					$instance->filters[$f][] = $kernel;
				}
			}
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
	
	private function id(){
		$f = count($this->filters);
		$S = $this->size;
		$D = $this->depth;
		return "$f filters, ".$S."x$S, depth $D";
	}

}