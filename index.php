<?php

// Show any and all errors
error_reporting(E_ALL);

// Create an array of pages, assigning each page a real/proper name and set a default page
$pages = array('projects' => 'Projects', 'about' => 'About', 'resume' => 'Résumé');
$default = 'about';

// Skip the page choosing when handling a 404 (from Apache ErrorDocument)
if ( (isset($_SERVER['REDIRECT_STATUS'])) && ($_SERVER['REDIRECT_STATUS'] == '404') ) {
	$eoc = $_SERVER['REDIRECT_STATUS'];
	$eocname = 'Page Not Found';
	$eoctitle = " - $eocname";

// If no page was passed via the URL or the URL passed does not exist, go with the the default page
} elseif ( (!isset($_GET['eoc'])) || (!array_key_exists(strtolower($_GET['eoc']), $pages)) ) {
	$eoc = $default;
	$eocname = $pages["$default"];
	$eoctitle = '';

// Otherwise, the page was passed via the URL and does exist so go with that
} else {
	$eoc = $_GET['eoc'];
	$eocname = $pages["$eoc"];
	$eoctitle = " - $eocname";
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="google-site-verification" content="4yRSlIeY2suoHNV99v092rtlWVP7vLHyQV7Idr8to1g" />
		<meta name="author" content="Eric O'Callaghan">
		<meta name="description" content="Eric O'Callaghan<?=$eoctitle; ?>">
		<meta name="keywords" content="Eric O'Callaghan, Eric OCallaghan, EricOC, Eric OC">
		<title>Eric O'Callaghan<?=$eoctitle; ?></title>
		<link href="/fonts.css" rel="stylesheet" type="text/css">
		<link href="/ericoc.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div id="logo">
			<h1>
				<a href="/"><span id="logoblue">Eric O'C</span>allaghan</a>
			</h1>
		</div>
		<div id="nav">
			<ul id="navlist">
<?php

			// Loop through $pages array and show a navigation button/tab for each page
			foreach ($pages as $page => $pagename) {

				echo '				<li><a ';

				// Do not link the current page, just apply CSS
				if ($page == $eoc) {
					echo 'id="current"';

				// Link the default page to "/"
				} elseif ($page == $default) {
					echo 'href="/"';

				// Link all other pages to "/whatever"
				} else {
					echo 'href="/' . $page . '"';
				}

				// Show the page name
				echo '>' . $pagename . '</a></li>' . "\n";
			}
?>
			</ul>
		</div>
		<br>
		<div id="content">
<?php

		// Include relevant .html file for current page if it exists
		if (@file_exists("$eoc.html")) {
			include("$eoc.html");

		// Just print an error if the file does not exist (i.e. when $eoc is '404')
		} else {
			echo "				<center>\n<h2>Sorry!</h2>\nUnfortunately, that page could not be found.\n</center>\n";
		}

?>
		</div>
		<br>
	</body>
</html>
