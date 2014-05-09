<?php

// Show any and all errors
error_reporting(-1);

// Create an array of pages, assigning each page a real/proper name and set a default page
$pages = array('projects' => 'Projects', 'about' => 'About', 'resume' => 'Résumé');
$default = 'about';

// If no page was passed via the URL or the URL passed does not exist, go with the the default page
if ( (!isset($_GET['eoc'])) || (!array_key_exists(strtolower($_GET['eoc']), $pages)) ) {
	$eoc = $default;
	$eocname = $pages["$default"];

// Otherwise, the page was passed via the URL and does exist, go with that
} else {
	$eoc = $_GET['eoc'];
	$eocname = $pages["$eoc"];
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="author" content="Eric O'Callaghan">
<meta name="description" content="Eric O'Callaghan<?php

// Change the meta tag description if we are not on the default page
if ($eoc != $default) {
	echo " - $eocname";
}

?>">
<meta name="keywords" content="Eric O'Callaghan, Eric OCallaghan, EricOC, Eric OC">
<title>Eric O'Callaghan<?php

// Change the title bar to include the pages real name if we are not on the default page
if ($eoc != $default) {
	echo " - $eocname";
}

?></title>
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,700|Roboto:400,700|Permanent+Marker" rel="stylesheet" type="text/css">
<link href="/ericoc.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="logo">
<h1><a href="/"><span id="logoblue">Eric O'C</span>allaghan</a></h1>
</div>
<div id="nav">
<ul id="navlist">
<?php

// Loop through $pages array and show a navigation button/tab for each page
foreach ($pages as $page => $pagename) {

	echo '<li>';

	// Treat current page differently
	if ($page == $eoc) {
		echo "<a id=\"current\">$eocname</a>";

	// Link the default page to "/"
	} elseif ($page == $default) {
		echo '<a href="/">' . $pages["$default"] . '</a>';

	// Link all other pages to "/whatever"
	} else {
		echo "<a href=\"/$page\">$pagename</a>";
	}
	echo "</li>\n";
}

?>
</ul>
</div><br>
<div id="content">
<?php

// Include relevant .html file for current page if it exists
if (@file_exists("$eoc.html")) {
	include("$eoc.html");

// Just print the same error from 404.html if the file does not exist for some reason
} else {
	echo "<center>\n<h2>Sorry!</h2>\nUnfortunately, that page could not be found.\n</center>\n";
}

?>
</div><br>
</body>
</html>
