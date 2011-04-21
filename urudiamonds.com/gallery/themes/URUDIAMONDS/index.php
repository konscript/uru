<?php
if(function_exists("checkForPage")) { // check if Zenpage is enabled or not
	if (checkForPage(getOption("zenpage_homepage"))) { // switch to a news page
		$ishomepage = false;
		include ('pages.php');
	} else {
		include ('gallery.php');
	}
} else { 
	include ('gallery.php');
}
?>