<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="robots" content="noindex,nofollow">
<title>Indego Bikes!</title>
<style type="text/css">
<!--
.header {
	font-weight: bold;
	border: 1px solid #000;
	font-family: Tahoma, sans-serif;
}

table, tr, td {
	border-collapse: collapse;
	border: 1px dotted #000;
}

tr:nth-child(2n) {
	background-color: #eee;
}

.bikes {
//	color: #0b0;
	color: #16216a;
}

.docks {
//	color: #b00;
	color: #777;
}

h1 {
	color: #16216a;
	text-decoration: underline;
	font-family: Tahoma, sans-serif;
}

-->
</style>
</head>
<body>
<h1><a href='<?php echo $_SERVER['PHP_SELF']; ?>'>Indego Bikes</a></h1>
<table>
<tr class='header'>
<td>Kiosk #</td>
<td>Name</td>
<td>Bikes</td>
<td></td>
<td>Docks</td>
</tr>
<?php

// Specify the Indego bikes API URL
$url = 'https://api.phila.gov/bike-share-stations/v1';

// Hit the API to get the JSON response
$c = curl_init();
curl_setopt($c, CURLOPT_URL, $url);
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($c, CURLOPT_USERAGENT, 'EricOC - https://ericoc.com/indego');
curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($c, CURLOPT_TIMEOUT, 2);
$r = curl_exec($c);
curl_close($c);

// Decode the JSON response from the API to an object
$decoded = json_decode($r);

// Totals start at zero
$totalbikesavailable = $totaldocksavailable = $totalstations = 0;

// Loop through each bike-share station
foreach ($decoded->features as $features) {

	// Skip the station if its kiosk is not active?
	if ($features->properties->kioskPublicStatus !== 'Active') {
		continue;
	}

	// Create an array of the current stations dock counts
	$docks = array(
		'used'	=>	$features->properties->bikesAvailable,	// Bikes at the station
		'free'	=>	$features->properties->docksAvailable	// Free docks at the station
	);

	// Get the current stations kiosk ID #, name, and address with zip code
	$id		=	$features->properties->kioskId;
	$name		=	$features->properties->name;
	$address	=	$features->properties->addressStreet . ' (' . $features->properties->addressZipCode . ')';

	// List the current stations information in a unique table row
	echo "<tr>\n";
	echo "<td><a href='#$id' id='$id'>$id</a></td>\n";	// Anchor link to the station/kiosk IDs
	echo "<td><span title='$address'>$name</span></td>\n";	// Hover text on the name shows address+zip code, but doesn't work on mobile :/
	echo "<td>" . $docks['used'] . "</td>\n";		// Number of bikes available at the station

	// Print a pretty graph of stylized blocks for bikes at the current station
	echo "<td><span class='bikes'>";
	for ($bike = 0; $bike < $docks['used']; $bike++) {
		echo "█";
	}
	echo "</span>";

	// And print another pretty graph of stylized blocks for empty docks at the current station
	echo "<span class='docks'>";
	for ($dock = 0; $dock < $docks['free']; $dock++) {
		echo "█";
	}
	echo "</span>";

	// Show the available dock count for current station
	echo "<td>" . $docks['free'] . "</td>\n";

	echo "</tr>\n";

	// Add the current stations counts to the totals
	$totalbikesavailable	+= $docks['used'];
	$totaldocksavailable	+= $docks['free'];
	$totalstations++;

	// Forget the current stations data
	unset($id, $name, $address, $docks);
}

// Show the total counts at the bottom of our table
echo "<tr class='header'>\n";
echo "<td>Totals</td>\n";
echo "<td>$totalstations stations</td>\n";
echo "<td>$totalbikesavailable</td>\n";
echo "<td></td>\n";
echo "<td>$totaldocksavailable</td>\n";
echo "</tr>\n";

// Yay! link to the API
?>
</table>
<br>
<pre>courtesy of <?php echo "<a href='$url' target='_blank'>$url</a>"; ?></pre>
</body>
</html>