<?php
	require_once(nomo::$frameworkPath."/lib/mustachephp/Mustache.php");
	$mustache=new Mustache;

	$templateData=array();
	$templateData["VERSION"]=VERSION;
	$templateData["RANDOM"]=rand() . rand() ;

	$template=file_get_contents(__DIR__.'/index.template.html');

	echo $mustache->render($template,$templateData);
?>
