<?php   
setlocale(LC_ALL, 'en_US.UTF8');
date_default_timezone_set('Europe/Budapest'); 

  
require_once(dirname(__FILE__)."/classes/__NomoException.class.php");
class __nomo {
  public static $REQUEST_URI=null; //domain név utáni rész
  public static $requestURIPath=null;
  public static $requestURIPathArray=array();
  public static $_GET=array();
	public static $_POST=array();
	public static $_COOKIE=array();
	public static $defaultDomain=null;
	public static $debug=false;
	public static $frameworkPath=null;
  public static $projectPath=null;
  public static $tempPath=null;
  public static $wwwPath=null;
  public static $autoloadEnabled=true;
  public static $dbConnetctions=array("default"=>array());
  public static $session=null; 
  public static $placeholders;

	public static function init($params=array()) { 
	  static::$REQUEST_URI=$_SERVER["REQUEST_URI"]; 
    $request_uri=explode("?",static::$REQUEST_URI);
	  if(count($request_uri)>1) parse_str($request_uri[1],static::$_GET);
	  static::$_POST=$_POST;
	  static::$_COOKIE=$_COOKIE;
	  $requestPathArray=explode("/",$request_uri[0]);array_splice($requestPathArray,0,1); 
    for($i=0;$i<count($requestPathArray);$i++) $requestPathArray[$i]=rawurldecode($requestPathArray[$i]);
    static::$requestURIPath=$request_uri[0];
	  static::$requestURIPathArray=$requestPathArray;
    static::$defaultDomain=$_SERVER["HTTP_HOST"];
    static::$frameworkPath=dirname(__FILE__);
    
    if(static::$autoloadEnabled){
      spl_autoload_register(get_called_class().'::loadClass');
    }

    static::$placeholders=array(
      "special"=>array(
      
      )
    );    
  }
  
  public static function loadClass($className) { 
    $fileName=$className.".class.php";
    $classNotFound=true;
    if(file_exists(static::$frameworkPath."/classes/".$fileName)){
      require_once(static::$frameworkPath."/classes/".$fileName);
      $classNotFound=false;
    }
    if(file_exists(static::$projectPath."/classes/".$fileName)){
      require_once(static::$projectPath."/classes/".$fileName);
      $classNotFound=false;
    }elseif(file_exists(static::$frameworkPath."/classes/__".$fileName)){
      require_once(static::$frameworkPath."/classes/__".$fileName);
      $classNotFound=false;
      eval("class ".$className." extends __".$className." {}");//Ghost class
    }

    if($classNotFound){
      eval("class ".$className." extends NomoDataSource {}");
    }
  }
    
}
?>
