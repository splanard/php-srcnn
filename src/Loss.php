<?php
interface Loss {
	
	public function backprop( array $y_true );
	public function forward( array $y_pred, array $y_true );
	
}