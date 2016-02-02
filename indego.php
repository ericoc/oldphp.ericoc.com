<?php

// Create a class to work with the Philadelphia Indego Bike Share API
class Indego {

	private $stations = [];		// Create empty private array to fill in with station data
	private $initialized = false;	// Initialization (retrieval) of station data hasn't happened yet

	// Create a function to hit the API and find all of the stations
	private function findStations() {

		// Specify the Indego bikes API URL
		$url = 'https://www.rideindego.com/stations/json/';

		// Specify a friendly user-agent to hit the API with
		$user_agent = 'Indego PHP API Library - https://github.com/ericoc/indego-php-lib';

		// Hit the API to get the JSON response
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($c, CURLOPT_TIMEOUT, 5);
		$r = curl_exec($c);
		curl_close($c);

		// Decode the JSON response from the API
		$raw = json_decode($r);

		// Add each station to our own array that's easier to work with
		foreach ($raw->features as $station) {
			$this->addStation($station->properties, $station->geometry->coordinates);
		}

		// Initialization complete since stations have been found at this point
		$this->initialized = true;
	}

	// Create a function to add stations to our own array (from large passed in array)
	private function addStation($properties, $coordinates) {
		$id = $properties->kioskId;		//	Get the station kiosk ID
		$this->stations[$id] = new stdClass();	//	Make a new object in our own array for the station

		// Fill in the properties of the station in to our own array
		foreach ($properties as $name => $value) {
			$this->stations[$id]->$name = $value;
		}

		// Fill in the coordinates for the station
		$this->stations[$id]->coordinates = $coordinates;
	}

	// Create a function to search for and return stations
	public function getStations($where = '') {

		// Find all of the stations first, if that hasn't already been done
		if (!$this->initialized) {
			$this->findStations();
		}

		// Create empty array to fill in with station data that will be returned by this function
		$return = [];

		// Just provide all of the stations if no search query was given
		if (empty($where)) {
			$return = $this->stations;

		// If a search query was passed, process it...
		} else {

			// Create a case-insensitive pattern to match station names and addresses with
			$pattern = '/' . trim($where) . '/i';

			// Loop through each station in the primary array
			foreach($this->stations as $station) {

				// If the search query is five digits, only match the stations with that zip code
				if ( (is_numeric($where)) && (strlen($where) == 5) ) {
					if ($station->addressZipCode == $where) {
						$return[$station->kioskId] = $station;
					}

				// Do a regular expression match using the search query on the name and address of each station
				} elseif ( (preg_match($pattern, $station->addressStreet)) || (preg_match($pattern, $station->name)) ) {
					$return[$station->kioskId] = $station;
				}
			}
		}

		// Return the stations!
		return $return;
	}
}

// Create a function to make pretty dock/bike graphs
function make_graph($bikes, $docks) {

	// Make a pretty graph of stylized blocks for bikes at the current station
	$graph = '<span class="bikes">';
	for ($bike = 0; $bike < $bikes; $bike++) {

		// If hitting the bike emoji URL, use bike emojis to represent bikes
		if ($_SERVER['REQUEST_URI'] == '/%F0%9F%9A%B2') {
			$graph .= '🚲 ';
		} else {
			$graph .= '█';
		}
	}
	$graph .= '</span>';

	// And another pretty graph of stylized blocks for empty docks at the current station
	$graph .= '<span class="docks">';
	for ($dock = 0; $dock < $docks; $dock++) {

		// If hitting the bike emoji URL, use hyphens to represent empty docks
		if ($_SERVER['REQUEST_URI'] == '/%F0%9F%9A%B2') {
			$graph .= '-';
		} else {
			$graph .= '█';
		}
	}
	$graph .= '</span>';

	// Return the graph
	return $graph;
}

?>
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

tr:target {
	background-color: orange;
}

.bikes {
	color: #16216a;
}

.docks {
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

// Instantiate the Indego class and get stations
$indego = new Indego;
$stations = $indego->getStations();

// Totals start at zero
$totalbikes = $totaldocks = $totalstations = 0;

// Loop through each bike-share station
foreach ($stations as $station) {

	// Skip the station if its kiosk is not active?
	if ($station->kioskPublicStatus !== 'Active') {
		continue;
	}

	// Get the current stations address with zip code for hover-text
	$address = $station->addressStreet . ' (' . $station->addressZipCode . ')';

	// List the current stations information in a unique table row
	echo "<tr id='$station->kioskId'>\n";
	echo "<td><a href='#$station->kioskId'>$station->kioskId</a></td>\n";				// Anchor link to the station/kiosk IDs
	echo "<td><span title='$address'>$station->name</span></td>\n";					// Hover text on the name shows address+zip code, but doesn't work on mobile :/
	echo "<td>$station->bikesAvailable</td>\n";							// Number of bikes available at the station
	echo "<td>" . make_graph($station->bikesAvailable, $station->docksAvailable) . "</td>\n";	// Generate and show pretty graph of bikes vs. docks at the station
	echo "<td>$station->docksAvailable</td>\n";							// Number of docks available at the station
	echo "</tr>\n";

	// Add the current stations counts to the totals
	$totalbikes += $station->bikesAvailable;
	$totaldocks += $station->docksAvailable;
	$totalstations++;
}

// Show the total counts at the bottom of our table
echo "<tr class='header'>\n";
echo "<td>Totals</td>\n";
echo "<td>$totalstations stations</td>\n";
echo "<td>$totalbikes</td>\n";
echo "<td></td>\n";
echo "<td>$totaldocks</td>\n";
echo "</tr>\n";

// Yay - links below!
?>
</table>
<br>
<pre>
courtesy of <a href='https://www.rideindego.com/stations/json/' target='_blank'>https://www.rideindego.com/stations/json/</a><br>
<a href='https://github.com/ericoc/ericoc.com/blob/master/indego.php' target='_blank'>view source @ github</a> | <a href='https://github.com/ericoc/indego-php-lib' target='_blank'>my Indego PHP library</a>
</pre>
</body>
</html>
