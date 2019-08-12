<?php
class ReLu implements Layer {
	
	/**
	 * The last processed input: a 3d-array, nb_channels x height x width.
	 * @var array 
	 */
	private $lastInput;
	
	public function backprop( array $d_L_d_out ){
		/*
		 * dL/din = dL/dout * dout/din
		 * dout/din = d/din(ReLu(in)) = in>0 ? 1 : 0
		 */
		$d_L_d_in = [];
		
		$H=count($d_L_d_out[0]);
		$W=count($d_L_d_out[0][0]);
		for( $c=0, $maxc=count($d_L_d_out); $c<$maxc; $c++ ){
			for( $y=0; $y<$H; $y++ ){
				for( $x=0; $x<$W; $x++ ){
					$d_L_d_in[$c][$y][$x] = $this->lastInput[$c][$y][$x] > 0 ? $d_L_d_out[$c][$y][$x] : 0;
				}
			}
		}
		
		return $d_L_d_in;
	}
	
	public function forward( array $input ){
		$this->lastInput = $input;
		foreach($input as $fmap){
			$h = count($fmap);
			$w = count($fmap[0]);
			$r = [];
			for($j=0; $j<$h; $j++){
				for($i=0; $i<$w; $i++){
					$r[$j][$i] = max(0,$fmap[$j][$i]);
				}
			}
			$out[] = $r;
		}
		return $out;
	}

}