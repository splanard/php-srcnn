<?php
require_once 'src/utils.php';
require_once 'src/Loss.php';

class MESLoss implements Loss {
	
	/**
	 * The last processed input ($y_preds)
	 * @var array 
	 */
	private $lastInput;
	
	/**
	 * Initiate back propagation by returning the first d_L_d_out.
	 * @param array $y_trues The real values.
	 * @return array d_L_d_out
	 */
	public function backprop( array $y_trues ){
		return $this->rMSE_d1( $this->lastInput, $y_trues );
	}
	
	/**
	 * Evaluate the MSE loss of the given predicted and real values.
	 * 
	 * @param array $y_preds The predicted values
	 * @param array $y_trues The real values
	 * @return number The MSE loss
	 */
	public function forward( array $y_preds, array $y_trues ){
		if( count($y_trues) != count($y_preds) ){
			trigger_error("given arrays must be the same size", E_USER_ERROR);
		}
		$this->lastInput = $y_preds;
		
		return $this->rMSE( $y_preds, $y_trues );
	}
	
	/**
	 * Recursively apply MSE on the given arrays. 
	 * 
	 * @param array $pred A n-dimensional array of predicted values
	 * @param array $true A n-dimensional array of the real values
	 * @return number The MSE
	 */
	private function rMSE( array $pred, array $true ){
		if( count($true) != count($pred) ){
			trigger_error("given arrays must be the same size", E_USER_ERROR);
		}
		if( is_numeric( $pred[0] ) && is_numeric( $true[0] ) ){
			$mse_array = array_map(function($t, $p){ return pow($t - $p, 2); }, $true, $pred );
			return array_sum($mse_array) / count($mse_array);
		}
		
		$size = count($true[0]);
		$sum = $this->rMSE( $pred[0], $true[0] );
		$n = count($true);
		for( $i=1; $i<$n; $i++ ){
			if( count($true[$i]) != $size ){
				trigger_error("if the given arrays contain arrays, these must all be the same size", E_USER_ERROR);
			}
			$sum += $this->rMSE( $pred[$i], $true[$i] );
		}
		return $sum / $n;
	}
	
	/**
	 * Recursively apply the derivative of MSE loss applied to each single element.
	 * 
	 * @param array $pred A n-dimensional array of predicted values
	 * @param array $true A n-dimensional array of the real values
	 * @return array an array of the same structure as $pred and $true
	 */
	private function rMSE_d1( array $pred, array $true ){
		if( count($true) != count($pred) ){
			trigger_error("given arrays must be the same size", E_USER_ERROR);
		}
		
		if( is_numeric( $pred[0] ) && is_numeric( $true[0] ) ){
			return array_map(function($t, $p){ return -2*($t-$p); }, $true, $pred);
		}
		
		for( $i=0, $maxi=count($true); $i<$maxi; $i++ ){
			$result[] = $this->rMSE_d1( $pred[$i], $true[$i] );
		}
		return $result;
	}

}