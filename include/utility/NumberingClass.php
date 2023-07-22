<?php
class NumberingClass{
	function containsDecimal( $value ) {
	/*
	 | cek ada tidaknya decimal pada angka */	
		if ( strpos( $value, "." ) !== false ) {
		    return true;
		}
		return false;
	}

	// function makeDecimal( $value ) {
	// 	// return number_format($value,$decpoint,',','.');
	// 	return number_format($value,2,'.','');
	// }

	function makeDecimalSeparator( $value,$decpoint ) {
		return number_format($value,$decpoint,',','.');
	}

	function autoSeparator ($value) {
		if ($this->containsDecimal($value)) {
			return $this->makeDecimalSeparator($value,2);
		} else {
			return $this->makeDecimalSeparator($value,0);
		}
	}
}
?>