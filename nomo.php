<?php
  ini_set('display_errors',1);
  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
  setlocale(LC_ALL, 'en_US.UTF8');
  date_default_timezone_set('Europe/Budapest');

  require_once(dirname(__FILE__)."/nomoEFW/__nomo.php");

  class nomo extends __nomo {
  	public static function init($params=array()) { 
  	  parent::init();
      self::$projectPath=dirname(__FILE__);
      self::$wwwPath=self::$projectPath."/";
      self::$tempPath=self::$projectPath."/tmp/";
      self::$dbConnetctions["default"]=array(
        "host"=>DB_HOST
        ,"db"=>DB_NAME
        ,"user"=>DB_USER
        ,"pass"=>DB_PASS
        ,"provider"=>"mysql"
      );
      self::$session=new Session("nomo_framework_sid");
      self::$defaultDomain=DEFAULT_DOMAIN;
    }    
  }  

  nomo::init();

  if(defined("AUTO_PATCH_DB") && AUTO_PATCH_DB) {
  	NomoPatchDB::check(true);
  }

  nomo::$session->initialize();
?>
