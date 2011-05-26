<?php
/*
 * Formats an ISBN-10 or ISBN-13 with dashes
 * $input_isbn = an ISBN-10 or ISBN-13 without (or with) dashes
 * returns a formatted ISBN-10 or ISBN-13 with dashes
 */
function formatISBN($input_isbn){
	$input_isbn = trim($input_isbn);
	// remove all hyphens
	$input_isbn = str_replace("-", "", $input_isbn);
	$isbn_ten = false;

	if (strlen($input_isbn) == 13){
		// 978
		$prefix = substr($input_isbn, 0, 3);
		// start building our formatted ISBN
		$formatted_isbn = $prefix."-";
		// the rest of the ISBN
		$suffix = substr($input_isbn, 3);
	}
	else if (strlen($input_isbn) == 10){
		$isbn_ten = true;
		$formatted_isbn = "";	
		$suffix = $input_isbn;
	}
	else return "ERROR";
	
	// add country code to formatted isbn and remove it from the
	// rest of the string that still has to be processed
	$country_code = get_country_code($suffix);
	$suffix = substr($suffix, strlen($country_code));
	
	// add publisher
	$publisher = get_publisher($formatted_isbn.$country_code, $suffix, $isbn_ten);
	$formatted_isbn .= $country_code."-".$publisher."-";
	
	// add checksum
	$checksum = substr($suffix, -1);
	$suffix = substr($suffix, 0, -1);
	$remainder = substr($suffix, strlen($publisher));
	$formatted_isbn .= $remainder."-".$checksum;
	return $formatted_isbn;
}

/*
 * Helper for formatISBN()
 * Returns the country code from an ISBN suffix
 */
function get_country_code($isbn_suffix){
	$country_code = substr($isbn_suffix, 0, 1);
	// 0 - 7 = 1 digit code
	if ((int)$country_code < 8) {
		return $country_code;
	}
	else {
		$country_code = substr($isbn_suffix, 0, 2);
		// 80 - 93 = 2-digit code
		if ((int)$country_code <= 93) {
			return $country_code;
		}
		// 95 - 98 = 3-digit code
		else if ((int)$country_code >= 95 && $country_code <= 98) {
			// get the 3-digit code
			$country_code = substr($isbn_suffix, 0, 3);
			return $country_code;
		}
		// 99 = 4-5 digit code
		else if ((int)$country_code == 99) {
			// get the 3rd digit and check it
			$country_code = substr($isbn_suffix, 0, 3);
			if ((int)$country_code >= 995 && $country_code <= 998){
				// get the 4-digit code
				$country_code = substr($isbn_suffix, 0, 4);
				return $country_code;
			}
			else if ((int)$country_code == 999) {
				// get the 5-digit code
				$country_code = substr($isbn_suffix, 0, 5);
				return $country_code;
			}
		}
		else return "ERROR";		
	}
}

/*
 * Helper for formatISBN()
 * Returns the publisher code from an ISBN suffix
 */
function get_publisher($prefix, $suffix, $isbn_ten){
	if ($isbn_ten) $prefix = "978-".$prefix;
	$suffix = substr($suffix, 0, 7);
	$xmlDocument = new DOMDocument();
	$xmlDocument->load("RangeMessage.xml");
	$xpath = new DOMXPath($xmlDocument);
	$xq = "/ISBNRangeMessage/RegistrationGroups/Group[Prefix=\"$prefix\"]/Rules/Rule/Range";
	$ranges = $xpath->query($xq, $xmlDocument);
	foreach ($ranges as $r){
		$range = $r->nodeValue;
		$range_r = explode("-", $range);
		if ($suffix >= $range_r[0] && $suffix <= $range_r[1]){
			// found the correct range
			$sibling = $r->nextSibling;
			while ($sibling != NULL){
				if ($sibling->nodeName == "Length"){
					$digits = $sibling->nodeValue;
				}
				$sibling = $sibling->nextSibling;
			}
			$publisher = substr($suffix, 0, $digits);
			return $publisher;
		}
	}
}

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