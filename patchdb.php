<?php
	$wwwPath=realpath(__DIR__);
	include($wwwPath."/config.php");
	include($wwwPath."/nomoEFW/classes/__NomoPatchDB.class.php");

	__NomoPatchDB::check();
?>
