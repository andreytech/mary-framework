<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $this->insert('page_title'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="description" content=""/>

</head>
<body>
<!-- Current page non-sef url: <?php echo URL::getNonSEFPath(); ?> -->

<?php
	$this->load($view);
?>

</body>
</html>