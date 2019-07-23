<?php
require_once 'src/Layer.php';

class ConvolutionLayer implements Layer {
	
	/**
	 * The depth of the layer, i.e. the number of feature maps it will receive
	 * as an input.
	 * @var integer 
	 */
	private $depth;
	
	/**
	 * The filters of the convolution layer.
	 * @var ConvolutionFilter[]
	 */
	private $filters;
	
	public function backprop( array $d_L_d_out, $learn_rate ){
		// TODO
	}

	public function forward( array $input ){
		// TODO
	}
	
	/**
	 * Instanciate a convolution layer.
	 */
	public static function create(){
		$instance = new self();
		// TODO
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