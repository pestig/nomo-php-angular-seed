<?php
  class Email extends __Email{    
    public static function send($params){
    
      if(!array_key_exists("smtp",$params)){
        $params["smtp"]=array(
           "host"=>SMTP_HOST,
           "port"=>SMTP_PORT,
           "user"=>SMTP_USER,
           "pass"=>SMTP_PASS,
           "auth"=>SMTP_AUTH
        );
      }
      /* senad bcc copy */
      $params["bcc"]=SYSTEM_BCC_EMAIL;
      
      $ret=parent::send($params);
      return $ret;
    }  
  }
?>