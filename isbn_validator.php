<?php
/*
 * Given an ISBN, returns true if it is valid
 * false if not.
 */
function validateISBN($isbn){
	$isbn = trim($isbn);
	$isbn = str_replace("-", "", $isbn);
	if (strlen($isbn) == 10) {
		$sum = $isbn[0]*10 + $isbn[1]*9 + $isbn[2]*8 + $isbn[3]*7 + $isbn[4]*6 + $isbn[5]*5 + $isbn[6]*4 + $isbn[7]*3 + $isbn[8]*2;
		$check_digit = $isbn[9];
		$test_check = 11 - ($sum % 11);
		if ($test_check == 10) $test_check = "X";
		if ($test_check == $check_digit) return true;
		else return false;
	}
	else if (strlen($isbn) == 13) {
		$sum = $isbn[0]*1 + $isbn[1]*3 + $isbn[2]*1 + $isbn[3]*3 + $isbn[4]*1 + $isbn[5]*3 + $isbn[6]*1 + $isbn[7]*3 + $isbn[8]*1 + $isbn[9]*3 + $isbn[10]*1 + $isbn[11]*3;
		$check_digit = $isbn[12];
		$test_check = 10 - ($sum % 10);
		if ($test_check == 10) $test_check = 0;
		if ($test_check == $check_digit) return true;
		else return false;
	}
	else return false;
}

?>
