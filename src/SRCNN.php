<?php
require_once 'src/Layer.php';
require_once 'src/Loss.php';
require_once 'src/MSELoss.php';
require_once 'src/utils.php';

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
	
	private $norm;
	
	function __construct(){
		$this->layers = [];
		$this->loss = new MSELoss(); // TODO: remove !
		$this->norm = Normalization::create(0, 255, true); // TODO: remove !
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
		foreach($this->layers as $id => $layer){
			$out = $layer->forward( $out );
		}
		return $out;
	}
	
	public function train( array $input, array $y_trues ){
		$input = matrices_normalize($input, 0, 1, 0, 255);
		$y_trues = matrices_normalize($y_trues, 0, 1, 0, 255);
		for( $i=1; $i<1000; $i++ ){
			echo "Memory usage: ".number_format(memory_get_usage()/1024/1024, 1)."Mb".PHP_EOL;
			
			// Feed forward
			$out = $this->forward( $input );
			$loss = $this->loss->forward($out, $y_trues);
			echo "Epoch $i: loss $loss".PHP_EOL;
			if( $i==1 || $i%5 == 0 ){
				//rgb2jpg( $out, "test-".str_pad($i, 3, "0", STR_PAD_LEFT).".jpg");
				rgb2jpg( matrices_normalize($out, 0, 255, null, null, true), "test-".str_pad($i, 3, "0", STR_PAD_LEFT).".jpg");
			}
			
			// Initial gradient
			$gradient = $this->loss->backprop( $y_trues );
			
			// Back propagation
			for( $l=count($this->layers)-1; $l>=0; $l-- ){
				$gradient = $this->layers[$l]->backprop( $gradient );
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