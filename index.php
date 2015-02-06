<?php
require_once(dirname(__FILE__)."/nomo.php");

if(defined('HTTP_AUTH') && HTTP_AUTH==true) include nomo::$frameworkPath."/lib/httpauth/httpauth.php";

try{    
  //handle AJAX call
  if(nomo::$requestURIPathArray[0]=="api"){
    include(nomo::$frameworkPath."/api/index.php"); 
  }else if(nomo::$requestURIPathArray[0]=="admin"){
    include(nomo::$projectPath."/admin/index.php");
  }else{
    include(nomo::$projectPath."/frontend-php-website/index.php");
  }
}catch(NomoException $e){    
  $resp=new stdClass;
  $resp->ret=$e->getCode();
  $scriptfilepath=$e->getFile();
  $resp->message=$e->getMessage();
  $resp->file=$scriptfilepath;
  $resp->line=$e->getLine();
  $resp->modified=$modified;
  $resp->trace=$e->getTraceAsString();
  //TODO fancy 404 page
  pre_print_r($resp);
}catch(Exception $e){     
  $resp=new stdClass;
  $resp->ret=5;
  $scriptfilepath=$e->getFile();
  $resp->message=$e->getMessage();
  $resp->reason=$e->getMessage();
  $resp->file=$scriptfilepath;
  $resp->modified=$modified;
  $resp->line=$e->getLine();
  $resp->trace=$e->getTraceAsString();
  //TODO fancy 404 page
  pre_print_r($resp);
}
?>
