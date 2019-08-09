<?php
require_once 'src/Layer.php';
require_once 'src/Loss.php';

class SRCNN {
	
	/**
	 * The network layers
	 * @var Layer[] 
	 */
	private $layers;
	
	/**
	 * The network loss function
	 * @var Loss 
	 */
	private $loss;
	
	function __construct(){
		$this->layers = [];
	}
	
	public function addLayer( Layer $layer ){
		$this->layers[] = $layer;
	}
	
	/**
	 * Export the instance configuration as an array.
	 * @return array
	 */
	public function export(){
		// TODO
	}
	
	public function forward( array $input ){
		$out = $input;
		foreach($this->layers as $layer){
			$out = $layer->forward( $out );
		}
		return $out;
	}
	
	public function train( array $input, array $y_trues ){
		for( $i=0; $i<100; $i++ ){
			// Feed forward
			$out = $this->forward( $input );
			$loss = $this->loss->forward($out, $y_trues);
			
			// Initial gradient
			$gradient = $this->loss->backprop( $y_trues );
			
			// Back propagation
			foreach($this->layers as $layer){
				// TODO !
			}
		}
	}
	
	/**
	 * Instantiate the class from the given configuration.
	 */
	public static function fromConf( array $conf ){
		// TODO
	}
	
	
}