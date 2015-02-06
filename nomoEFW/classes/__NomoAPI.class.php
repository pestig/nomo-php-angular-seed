<?php 
  class __NomoAPI{

    public static function execute($req){
      $resp=array();
      $resp["ret"]=0;
      $resp["userid"]=0;
      $resp["groupid"]=nomo::$session->groupid;

      if(!isset($req["className"])){
        throw new NomoException("missing input param: className",3);
      }else if(!isset($req["method"])){
        throw new NomoException("missing input param: method",3);
      }else{
        $ClassName=$req["className"];

        if(!method_exists($ClassName, $req["method"]))
          throw new NomoException("Nem létező művelet: ".$ClassName.":".$req["method"]."!",3);;
        
        $vars = get_class_vars($ClassName);
        $ajaxAccessWhitelist=$vars["__ajax__".$req["method"]."_whitelist"];
        $ajaxAccessBlacklist=$vars["__ajax__".$req["method"]."_blacklist"];
        if(!isset($ajaxAccessWhitelist) && !isset($ajaxAccessBlacklist))
          throw new NomoException("Nem engedélyezett művelet: ".$req["className"].":".$req["method"]."!",3);
        
        if(isset($ajaxAccessWhitelist) && $ajaxAccessWhitelist!==true){   
          if($ajaxAccessWhitelist===false)  
            throw new NomoException("Nem rendelkezik a művelet végrehajtásához szükséges  jogosultsággal! [".$req["method"].",".$req["className"].",wl,false]",3);
          //else if(is_array($ajaxAccessWhitelist) && !in_array(nomo::$session->groupid,$ajaxAccessWhitelist)) 
          else if(is_array($ajaxAccessWhitelist) && !nomo::$session->memberOf(array_sum($ajaxAccessWhitelist)))
            throw new NomoException("Nem rendelkezik a művelet végrehajtásához szükséges  jogosultsággal! [".$req["method"].",".$req["className"].",wl,array]",3);
          else if(is_int($ajaxAccessWhitelist) && !nomo::$session->memberOf($ajaxAccessWhitelist))
            throw new NomoException("Nem rendelkezik a művelet végrehajtásához szükséges  jogosultsággal! [".$req["method"].",".$req["className"].",wl,gid]",3);                          
          else if($ajaxAccessWhitelist!==true && !is_array($ajaxAccessWhitelist) && !is_int($ajaxAccessWhitelist) ) 
            throw new NomoException("Nem rendelkezik a művelet végrehajtásához szükséges  jogosultsággal! [".$req["method"].",".$req["className"].",wl,invalid]",3);   
        }
        
        if(isset($ajaxAccessBlacklist) && $ajaxAccessBlacklist!==false){   
          if($ajaxAccessBlacklist===true)  
            throw new NomoException("Nem rendelkezik a művelet végrehajtásához szükséges  jogosultsággal! [".$req["method"].",".$req["className"].",bl,true]",3); 
          else if(is_array($ajaxAccessBlacklist) && in_array(nomo::$session->groupid,$ajaxAccessBlacklist)) 
            throw new NomoException("Nem rendelkezik a művelet végrehajtásához szükséges  jogosultsággal! [".$req["method"].",".$req["className"].",bl,array]",3); 
          else if(is_int($ajaxAccessBlacklist) && nomo::$session->memberOf($ajaxAccessBlacklist))                       
            throw new NomoException("Nem rendelkezik a művelet végrehajtásához szükséges  jogosultsággal! [".$req["method"].",".$req["className"].",bl,gid]",3);    
          else if($ajaxAccessBlacklist!==true && !is_array($ajaxAccessBlacklist) && !is_int($ajaxAccessBlacklist) ) 
            throw new NomoException("Nem rendelkezik a művelet végrehajtásához szükséges  jogosultsággal! [".$req["method"].",".$req["className"].",bl,invalid]",3); 
        }
        
        //OWASP sql injection
        if($req["params"] && $req["params"]["sqlwherepostfix"]) unset($req["params"]["sqlwherepostfix"]);
        if($req["params"] && $req["params"]["sqlfields"]) unset($req["params"]["sqlfields"]);
				if($req["params"] && $req["params"]["sqlgroupby"]) unset($req["params"]["sqlgroupby"]);
				if($req["params"] && $req["params"]["sqlfrom"]) unset($req["params"]["sqlfrom"]);
				if($req["params"] && $req["params"]["orderby"] && $req["params"]["orderby"]["sqlorderby"]) unset($req["params"]["orderby"]["sqlorderby"]);
        
        $resp["data"]=call_user_func(array($ClassName,$req["method"]),$req["params"]);
        $resp["userid"]=nomo::$session->userid;
        $resp["groupid"]=nomo::$session->groupid;
      }       
      return $resp;
    }

  }
?>
