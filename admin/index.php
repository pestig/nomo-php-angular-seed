<?php
    require_once(nomo::$frameworkPath."/lib/mustachephp/Mustache.php");
    $mustache=new Mustache;

    $templateData=array();
    $templateData["VERSION"]=VERSION;
    $templateData["RANDOM"]=rand() . rand() ;
    $templateData["NOMOEFW_JS_INCLUDE_HTML"]=NomoUtils::getJSIncludeHtml(__DIR__."/../nomoEFW/app/modules");
    $templateData["ADMIN_JS_INCLUDE_HTML"]=NomoUtils::getJSIncludeHtml(__DIR__."/../admin/app/modules");

    echo $mustache->render(file_get_contents(__DIR__.'/index.template.html'),$templateData);
?>
