<?php
include_once("isbn_tools.php");

/*
 * Shows how you might use formatISBN(); and validateISBN();
 */
?>
<html>
<head><title>ISBN Tools</title></head>
<body>
<h1>ISBN Tools</h1>
<form name="isbn_form" id="isbn_form" action="example.php" method="POST">
<H3>Enter one or more Unformatted ISBN(s), one ISBN per line.</H3>
<b>Sample Input:</b><br/>
9788992589109<br/>
9789868379206<br/> 
<p><textarea name="isbns" id="isbns" rows="10"></textarea></p>
<input type="submit" name="btn_submit" id="btn_submit" value="Submit" />
<input type="button" name="btn_clear" id="btn_clear" value="Clear" onClick="location.href='example.php';"/>
</form>

<?php
if (isset ($_POST['isbns'])){
	$isbns = trim($_POST['isbns']);
	?>
	<h1>Results</h1>
	<table border=1>
	<thead>
		<tr>
		<td><b>Input ISBN</b></td>
		<td><b>Formatted ISBN</b></td>
		<td><b>Valid?</b></td>
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
		<td><? if (validateISBN($formatted_isbn)) print "TRUE"; else print "FALSE";?>&nbsp;</td>
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