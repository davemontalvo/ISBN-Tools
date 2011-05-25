<?php
/*
 * Takes an unformatted ISBN-10 or ISBN-13 and formats it 
 * with dashes using the RangesMessage.xml file from 
 * www.isbn-international.org
 * 
 * You will have to download the xml file from 
 * http://www.isbn-international.org/agency?rmxml=1
 * and save it as ranges.xml in your working directory for this to work
 * 
 * This uses PHP's DOMDocument() to process the ranges XML file
 */
?>

<html>
<head><title>ISBN Formatter</title></head>
<body>
<h1>ISBN Formatter</h1>
<form name="isbn_form" id="isbn_form" action="isbn_formatter.php?process" method="POST">
<H3>Enter one or more Unformatted ISBN(s), one ISBN per line.</H3>
<p><textarea name="isbns" id="isbns" rows="10"></textarea></p>
<input type="submit" name="btn_submit" id="btn_submit" value="Submit" />
<input type="button" name="btn_clear" id="btn_clear" value="Clear" onClick="location.href='isbn_formatter.php';"/>
</form>

<?php
if (isset ($_GET['process'])){
	$isbns = $_POST['isbns'];
	?>
	<h1>Results</h1>
	<table border=1>
	<thead>
		<tr>
		<td><b>Input ISBN</b></td>
		<td><b>Formatted ISBN</b></td>
		</tr>
	</thead>
	<?
	// assumes 1 ISBN per line
	$isbns = explode("\n", $isbns);
	for ($i = 0; $i < count($isbns); $i++){
		$single_isbn = trim($isbns[$i]);
		$formatted_isbn = formatISBN($single_isbn)
		?>
		<tr>
		<td><?=$single_isbn?>&nbsp;</td>
		<td><?=$formatted_isbn?>&nbsp;</td>
		</tr>
		<? 
	}
	?>
	</table>
	<?php
}
?>
</body>
</html>
<?php

/*
 * Formats an ISBN-10 or ISBN-13 with dashes
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
	$publisher = get_publisher($formatted_isbn.$country_code, $suffix, $isbn_ten);
	$formatted_isbn .= $country_code."-".$publisher."-";
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
	// download xml from http://www.isbn-international.org/agency?rmxml=1 and rename to ranges.xml
	// in the working directory
	$xmlDocument->load("ranges.xml");	
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
?>