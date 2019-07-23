<?php
interface Layer {
	
	public function forward( array $input );
	public function backprop( array $d_L_d_out, $learn_rate );
	
}