<?php

class __Session extends NomoDataSource{
	private $data=null;
	private $sessionCookieName=null;
    public $groupid=0;
    public $userid=null;
    public static $sessionTimeout=3600;  //seconds  default 3600
    public static $sessionUpdatePeriod=600;  //seconds    default 600

	function __construct($sessionCookieName) {
	  $this->sessionCookieName=$sessionCookieName;
	}  
  
  public static $__ajax__getDefinition_whitelist=true;
	public static function getDefinition($params = array(),$groupid = FALSE){
    $definition=parent::getDefinition($params,$groupid);
    return $definition;
  }

    public static $__ajax__get_current_session_whitelist=true;
    public static function get_current_session($params = null){
       $user=array();
       if(nomo::$session->userid){
         $users=User::select(array("filters"=>array(
           array(
             "field"=>"rowid",
             "value"=>nomo::$session->userid
           )
         )),1);
         if(count($users>0)){
           $user=$users[0];
         }
       }

       $session=array();
       $session["userid"]=nomo::$session->userid;
       $session["groupid"]=nomo::$session->groupid;
       $session["user"]=$user;


       return $session;
    }
  
  public function initialize($nonUserRequest = false) {
    static::check($nonUserRequest);
    if($this->data===null){
      static::start($nonUserRequest);
    } 
  }
    
  protected function start($nonUserRequest = false){  
    //start session
    $key=NomoUtils::rndgen(64);      
    $result=static::create(array(
      "record"=>array("key"=>$key,"nomocms_modified_at"=>date("Y-m-d H:i:s",time())
    )),1); 
    
    $cookie=$key."_".$result["sqlresult"]["insert_id"];
    if(headers_sent())
      throw new NomoException("A header elküldése után már nem lehet a session cookie-t beállítani!",21);
    
    setcookie ($this->sessionCookieName, $cookie, 0, "/");
    //setcookie ($this->sessionCookieName, $cookie, time()+60*60*24*30*12, "/");
    
    
    nomo::$_COOKIE[$this->sessionCookieName]=$cookie;
    static::check($nonUserRequest);
  }
  
  
  protected function check($nonUserRequest = false){
    $cookie=nomo::$_COOKIE[$this->sessionCookieName];
    if($cookie){ 
      $values=explode("_",$cookie);
      if(count($values)==2){
        $rows=static::select(array(
          "filters"=>array(
            array(
              "field"=>"rowid"
              ,"value"=>$values[1]
            ),
            array(
              "field"=>"key"
              ,"value"=>$values[0]
            )
          ),
          "resultType"=>"withgroupid"
        ),1);     
        if(count($rows)>0){
          $this->data=$rows[0];          
        }
      }
    }
 
    if($this->data){
      $now=time();
      $modifiedAt=strtotime($this->data["nomocms_modified_at"]);
      
      $longsession=(int)$this->data["longsession"];
      if((($now-$modifiedAt)>static::$sessionTimeout) && !$longsession){
        //lejárt a session
        static::delete(array(
          "filters"=>array(
            array(
              "field"=>"rowid",
              "value"=>(int)$this->data["rowid"]
            )
          )
          //"items"=>array((int)$this->data["rowid"])
        ),1);
        $this->data=null; 
        
      }else if(($now-$modifiedAt)>(static::$sessionUpdatePeriod) && !$nonUserRequest){
        //utolsó használat frissítés
        $result=static::update(array(
          "record"=>array(
            "nomocms_modified_at"=>date("Y-m-d H:i:s",time())
          ),
          "filters"=>array(
            array(
              "field"=>"rowid",
              "value"=>(int)$this->data["rowid"]
            )
          )
        ),1);
      }
    }
    static::setProperties($this->data);
  }
   
  protected function setProperties($data){
    if($data && $data["user"]){
      $users=User::select(array(
        "filters"=>array(
          array(
            "field"=>"rowid",
            "value"=>$data["user"]
          )
        )
      ),1);
      
      if(count($users)!=1) throw new NomoException("Érvénytelen userid a sessionben'".$data["user"]."'!",21);
      $user=$users[0];
      
      $this->groupid=(int)$user["groupid"];
      $this->userid=(int)$user["rowid"];
    }else{
      $this->groupid=0;
      $this->userid=null; 
    }
  } 
   
  public function memberOf($gid,$compareValue=null){
    if($gid===FALSE || $gid===TRUE) return $gid;
    if($compareValue===null) $compareValue=$this->groupid;
      
    return (($gid^$compareValue)!=($gid+$compareValue));
  }
  
  public function setsession($userid,$longsession = false){
     if(!$this->data) static::initialize();
     if(!$this->data) throw new NomoException("Nem sikerült incializálnmi a session-t!",11);
     
    
     $lonsessionValue=($longsession)?1:0;
     $result=static::update(array(
       "record"=>array(
         "rowid"=>$this->data["rowid"],
         "user"=>$userid,
         "longsession"=>$lonsessionValue,
         "nomocms_modified_at"=>date("Y-m-d H:i:s",time())
        ),
        "filters"=>array(
          array(
            "field"=>"rowid",
            "value"=>(int)$this->data["rowid"]
          )
        )
     ),1);
     
     //var_dump($result);exit;
     if($result["sqlresult"]["affected_rows"]!==1)
       throw new NomoException("Nem sikerült a sesiont frissíteni affected_rows:".$result["sqlresult"]["affected_rows"]."!",11);

     static::check();

     if($this->data && $this->data["user"]==$userid)
       return true;
    
     return false;
  }
}
?>
