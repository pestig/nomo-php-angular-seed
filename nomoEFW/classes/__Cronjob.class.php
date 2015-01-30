<?php

  class __Cronjob extends NomoDataSource{
    
    public static function runCronjobs(){ 
      $cronjobs=static::select(array(),1);
      
      for($i=0;$i<count($cronjobs);$i++){
        $cronjob=$cronjobs[$i];
        
        $errorMessage="";
        if(!is_numeric($cronjob["interval"])){
          $errorMessage="Invalid interval in cronjob: ".$cronjob["interval"];
        }else{
          $interval= min(static::timeStringToInterval($cronjob["last_stop_at"]),static::timeStringToInterval($cronjob["last_start_at"]));
          
          if($interval>=$cronjob["interval"]){
            if(!is_callable( $cronjob["functionname"])){
              $errorMessage="Invalid functionname in cronjob: ".$cronjob["functionname"];
            }else{
              $cronjobs_ret=static::update(
                array(
                  "newrecord"=>array(
                    "rowid"=>$cronjob["rowid"],
                    "last_start_at"=>date("Y-m-d H:i:s")
                  )
                ),1
              );
              //TODO: retData into sql
              $retData=call_user_func($cronjob["functionname"]);
    
              $cronjobs_ret=static::update(
                array(
                  "newrecord"=>array(
                    "rowid"=>$cronjob["rowid"],
                    "last_stop_at"=>date("Y-m-d H:i:s"),
                    "last_result"=>$retData
                  )
                ),1
              );
                                  
              //echo $cronjob["functionname"].": \n";
              //echo $retData;          
            }
          }
        }
        
        if(!empty($errorMessage)){
          echo $errorMessage;
          $cronjobs_ret=static::update(
            array(
              "newrecord"=>array(
                "rowid"=>$cronjob["rowid"],
                "last_result"=>"ERROR [".date("Y-m-d H:i:s")."]: ".$errorMessage
              )
            ),1
          );
        }
      }
    }
    
    static function timeStringToInterval($time){
       return (int)(time() - strtotime($time));
    }
  
  }


?>
