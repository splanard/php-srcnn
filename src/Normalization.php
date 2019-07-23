<?php
require_once 'src/Layer.php';

class Normalization implements Layer {
	
	/**
	 * The minimum value wanted after the normalization
	 * @var number 
	 */
	private $min;
	
	/**
	 * The maximum value wanted after the normalization
	 * @var number
	 */
	private $max;
	
	/**
	 * Do we need to round the results as integers ?
	 * @var boolean
	 */
	private $round;
	
	private function __construct( $min, $max, $round ){
		$this->min = $min;
		$this->max = $max;
		$this->round = $round;
	}
	
	/**
	 * Normalize each feature map contained in the given input.
	 * @param array $input An array of one or more feature maps (2-dimensional arrays).
	 * @return array The normalized input
	 */
	public function forward( array $input ){
		foreach( $input as $fmap ){
			$i_min = rmin( $fmap );
			$i_max = rmax( $fmap );
			$H = count($fmap);
			$W = count($fmap[0]);
			for( $y=0; $y<$H; $y++ ){
				for( $x=0; $x<$W; $x++ ){
					$newval = ($fmap[$y][$x]-$i_min)/($i_max-$i_min)*($this->max - $this->min) + $this->min;
					$newfmap[$y][$x] = $this->round ? floor($newval) : $newval;
				}
			}
			$out[] = $newfmap;
		}
		return $out;
	}
	
	public function backprop( array $d_L_d_out, $learn_rate ){
		// TODO
	}
	
	/**
	 * Export the instance configuration as an array.
	 * @return array
	 */
	public function export(){
		return [$this->min, $this->max, $this->round];
	}
	
	/**
	 * Instantiate the class with the given values.
	 */
	public static function create( $min, $max, $round = false ){
		if( !is_numeric( $min ) || !is_numeric( $max ) ){
			trigger_error("min and max arguments values must be numbers", E_USER_ERROR);
		}
		if( $min >= $max ){
			trigger_error("min must be smaller than max", E_USER_ERROR);
		}
		if( !is_bool( $round ) ){
			trigger_error("round argument value must be a boolean", E_USER_ERROR);
		}
		return new self( $min, $max, $round );
	}
	
	/**
	 * Instantiate the class from the given configuration.
	 */
	public static function fromConf( array $conf ){
		return self::create( $conf[0], $conf[1], $conf[2] );
	}
	
}
