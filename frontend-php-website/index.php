<?php
	require_once(nomo::$frameworkPath."/lib/mustachephp/Mustache.php");
	$mustache=new Mustache;

	$templateData=array();
	$templateData["VERSION"]=VERSION;
	$templateData["RANDOM"]=rand() . rand() ;
	if(!file_exists(__DIR__."/../nomoEFW/app/dist/nomo.min.css")) $templateData["NOMODEV_CSS_HTML"]=NomoUtils::getNomoDevCSS();
    if(!file_exists(__DIR__."/../nomoEFW/app/dist/nomo.min.js")) $templateData["NOMODEV_JS_HTML"]=NomoUtils::getNomoDevJS();

	$template=file_get_contents(__DIR__.'/index.template.html');
	echo $mustache->render($template,$templateData);
?>
