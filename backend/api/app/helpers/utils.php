<?php 


	// easy checker for empty strings
	function checkEmptyData ( $arr, $minimumLenght = 0 ) {
		$notEmpty = true;
		foreach ( $arr as $value ) {
			if(strlen(trim($value)) <= $minimumLenght) {
				$notEmpty = false;
			}
		}
		return $notEmpty;
	}