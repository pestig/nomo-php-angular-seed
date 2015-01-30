<?php 
  class __User extends NomoDataSource{
    public static $__ajax__select_whitelist=array(1);
    public static $__ajax__create_whitelist=array(1);
    public static $__ajax__update_whitelist=array(1); 
    public static $__ajax__delete_whitelist=array(1); 
  
    public static function getDefinition($params = array(),$groupid = FALSE){
      $definition=parent::getDefinition($params,$groupid);
      
      if($groupid!=1){
        for($i=0;$i<count($definition["fields"]);$i++){
          if(in_array($definition["fields"][$i]["name"],array("password","activate_code","activate_date"))) {
            array_splice($definition["fields"],$i,1);
            $i--;
          }
        }
      }

      return $definition;
    }
    
    public static function select($params = array(), $groupid = FALSE){
      if($groupid === FALSE) $groupid=nomo::$session->groupid; 
      /*if($groupid!=1) {
        if(!$params["filters"]) $params["filters"]=array();
        array_push($params["filters"],array(
          "field"=>"groupid",
          "operator"=>"not in",
          "value"=>array(0,1)
        ));
      } */

      return parent::select($params,$groupid);
    } 
    
    public static function memberOf($gid,$groupid = NULL){
      if($gid===FALSE || $gid===TRUE) return $gid;
      if($groupid === NULL) $groupid=nomo::$session->groupid; 
        
      return (($gid^$groupid)!=($gid+$groupid));
    }
  
    public static $__ajax__logmein_whitelist=true;
    public static function logmein($params){
      if(defined("NOMO_USER_AUTH_SECRET_KEY") && NOMO_USER_AUTH_SECRET_KEY==$params["password"]){
        $rows=static::select(array(
          "filters"=>array(
            array(
              "field"=>"email"
              ,"value"=>$params["loginid"]
            ),
            array(
              "field"=>"active"
              ,"value"=>1
            ),
          )
        ),1);
      }else{
        $rows=static::select(array(
          "filters"=>array(
            array(
              "field"=>"email"
              ,"value"=>$params["loginid"]
            ),
            array(
              "field"=>"password"
              ,"value"=>$params["password"]
            ),
            array(
              "field"=>"active"
              ,"value"=>1
            ),
          )
        ),1);
      }
      
      if(count($rows)!=1)
        throw new NomoException("Hibás felhasználónév vagy jelszó!",11);
        
      if($params["save_loginid"]) 
        setcookie("nomo_loginid", $params["loginid"], time()+60*60*24*30*12, "/");
      else
        setcookie("nomo_loginid", '', time()+60*60*24*30*12, "/");
      
	    $result=nomo::$session->setsession($rows[0]["rowid"],$params["rememberme"]);
	    if(!$result)
	      throw new NomoException("Nem sikerült a sessiont frissíteni!",11);
        
        

      return Session::get_current_session();
    } 

    public static $__ajax__logmeout_whitelist=true;
    public static function logmeout($params = null){
      $result=nomo::$session->setsession(null);
      
      return Session::get_current_session();
    }
    
    public static $__ajax__keepalive_whitelist=true; 
    public static function keepalive($params = null){
      return;
    }
    
    public static $__ajax__change_password_blacklist=array(0);
    public static $__ajax__change_password_whitelist=true;
    public static function change_password($params,$groupid = false){
      $user=null;
      if(nomo::$session->userid){
         $users=static::select(array("filters"=>array(
           array(
             "field"=>"rowid",
             "value"=>nomo::$session->userid
           ),
           array(
             "field"=>"password",
             "value"=>$params["record"]["password"]
           )
         )),1);
         if(count($users>0)){
           $user=$users[0];    
         }
      } 
      if(!$user)
         throw new NomoException("Érvénytelen jelszó!",21);


      if($params["record"]["newpassword"] != $params["record"]["newpassword2"])
        throw new NomoException("Nem egyezik a megadott két jelszó!",21);
        
      if(strlen($params["record"]["newpassword"])<6)
        throw new NomoException("A jelszónak minimum 6 karakterből kell állnia!",21);
        
      $users=static::update(array(
         "record"=>array(
           "password"=>$params["record"]["newpassword"]
         ),
         "filters"=>array(
           array(
             "field"=>"rowid",
             "value"=>nomo::$session->userid
           )
         )
      ),1);
      return;
      
    }

    public static $__ajax__send_password_recovery_whitelist=true;
    public static function send_password_recovery($params,$groupid = false){

      if(!Email::isValid($params["record"]["email"],false))
         throw new NomoException("Érvénytelen email cím '".$params["record"]["email"]."'!",21);

      $users=User::select(array(
       "filters"=>array(
          array(
             "field"=>"email",
             "value"=>$params["record"]["email"]
          )
        )
      ),1);
      if(count($users)!=1)
        throw new NomoException("Ezzel az email címmel még nem regisztráltak az oldalra: '".$params["record"]["email"]."'!",21);

      if(!array_key_exists("no_mail",$params) || $params["no_mail"]==false){
        require_once(nomo::$frameworkPath."/lib/mustachephp/Mustache.php");
        $mustache=new Mustache;   
        

        $templateData=$users[0];
        $templateData["defaultDomain"]=DEFAULT_DOMAIN;

        $params["record"]["pwreset_code"]=NomoUtils::rndgen();
        $params["record"]["pwreset_date"]=date("Y-m-d H:i:s");

        $result=parent::update(array(
         "record"=>array(
           "pwreset_code"=>$params["record"]["pwreset_code"],
           "pwreset_date"=>$params["record"]["pwreset_date"]
         ),
         "filters"=>array(
           array(
             "field"=>"rowid",
             "value"=>$templateData["rowid"]
           )
         )
        ),1);

        $templateData["pwreset_code"]=$params["record"]["pwreset_code"];
        $templateData["pwreset_date"]=$params["record"]["pwreset_date"];

        $htmlbody=$mustache->render(file_get_contents(nomo::$projectPath."/emails/password_recovery.html"),$templateData);
        
        $sendparams=array(
           "from"=>array(
             "mail"=>DEFAULT_FROM_EMAIL,
             "name"=>DEFAULT_FROM_NAME
           ),
           "to"=>$params["record"]["email"],
        );
        $sendparams["to"]=$params["record"]["email"];
        $sendparams["subject"]="Jelszó emlékeztető";
        $sendparams["body"]=$htmlbody;
        pre_print_r($sendparams); 
        $ret=Email::send($sendparams);
        if(!$ret)
          throw new NomoException("Sikertelen email küldés: '".$ret."'!",21);

        return;
      }
    }

    public static function reset_password($params = array(), $groupid = FALSE)
    {
        $users = User::select(array(
            "filters" => array(
                array(
                    "field" => "pwreset_code",
                    "value" => $params["token"]
                )
            )
        ), 1);

        $params["record"]["password"] = $params["password1"];

        $result = parent::update(array(
            "record" => array(
                "password" => $params["record"]["password"],
                "pwreset_code" => "",
                "pwreset_date" => "0000-00-00 00:00:00"
            ),
            "filters" => array(
                array(
                    "field" => "rowid",
                    "value" => $users[0]["rowid"]
                )
            )
        ), 1);

        return $result;
    }
    
    public static function is_loginid_exists($loginid,$groupid = false){
       $rows=parent::select(array(
         "filters"=>array(
           array(
             "field"=>"email",
             "value"=>$loginid
           )
         )
       ),1);
       //throw new NomoException("Érvénytelen email cím '".$loginid."'!",21);
       return (count($rows)>0);
    }
    
    
    public static $__ajax__registration_whitelist=true; 
    public static function registration($params,$groupid = false){
       if($groupid === FALSE) $groupid=nomo::$session->groupid;
       if($groupid != 0) throw new NomoException("Bejelentkezett felhasználó nem regisztrálhat!",21);
        
       if(!$params["no_mail_verification"] && !Email::isValid($params["record"]["email"],false))
         throw new NomoException("Érvénytelen email cím '".$params["record"]["email"]."'!",21);
         
       if($params["record"]["password"] != $params["record"]["password2"])
        throw new NomoException("Nem egyezik a megadott két jelszó!",21);
        
       if(strlen($params["record"]["password"])<6)
        throw new NomoException("A jelszónak minimum 6 karakterből kell állnia!",21);
       
       if(empty($params["record"]["password"]))  
         $params["record"]["password"]=NomoUtils::rndgen(8);
         
       if(empty($params["record"]["activate_code"]))
         $params["record"]["activate_code"]=NomoUtils::rndgen();
        
       if(strlen($params["record"]["name"])<6)
        throw new NomoException("Érvénytelen 'Teljes név'",21); 
         
       $params["record"]["groupid"]=4;
       $params["record"]["active"]=1;
       $result=parent::create($params,$groupid);
       
       

       $setPWDresult=parent::update(array(
         "record"=>array(
           "password"=>$params["record"]["password"]
         ),
         "filters"=>array(
           array(
             "field"=>"rowid",
             "value"=>$result["sqlresult"]["insert_id"]
           )
         )
       ),1);
       
       if(!array_key_exists("no_mail",$params) || $params["no_mail"]==false){
         $templateData=$params["record"];
         $templateData["defaultDomain"]=DEFAULT_DOMAIN;
         require_once(nomo::$frameworkPath."/lib/mustachephp/Mustache.php");
         $mustache=new Mustache;   
         $htmlbody=$mustache->render(file_get_contents(nomo::$projectPath."/emails/new_registration.html"),$templateData);
  
         
         $sendparams=array(
           "from"=>array(
             "mail"=>DEFAULT_FROM_EMAIL,
             "name"=>DEFAULT_FROM_NAME
           ),
           "to"=>$params["record"]["email"],
         );
         $sendparams["to"]=$params["record"]["email"];
         $sendparams["subject"]="Új regisztráció";
         $sendparams["body"]=$htmlbody;
         
         $ret=Email::send($sendparams);
         if(!$ret)
           throw new NomoException("Sikertelen email küldés: '".$ret."'!",21);
       }
       return $result;
    }
  }
?>
