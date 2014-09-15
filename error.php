<?php

function error($msg = '', $code = false) {
	if($code == 404) {
		error404($msg);
		return;
	}
	echo 'Error occured:'.$msg.'<br>Please, contact administrator.';
	exit;
}

function error404($msg = '') {
?>
	<h1>Page not found</h1>
	<p>Error 404 - the page you are trying to view doesn't exist on site</p>
	<!--
	Error: <?php echo $msg; ?>

	Non-sef path:<?php echo URL::getNonSEFPath(); ?>

	-->
<?php
}
