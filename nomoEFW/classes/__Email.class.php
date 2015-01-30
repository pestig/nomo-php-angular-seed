<?php 
  
  class __Email{
  
    public static function send($params){
      $ret=true;
      require_once(nomo::$frameworkPath."/lib/phpmailer/class.phpmailer.php");
      $mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
  
      try {
        //pre_print_r("asdasdasd");
        //$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
        //pre_print_r($params);  
        if(array_key_exists("smtp",$params)){
          $mail->IsSMTP(true); // telling the class to use SMTP
                 
          $mail->Host       = $params["smtp"]["host"]; // SMTP server
          $mail->Port       = (int)$params["smtp"]["port"];  // set the SMTP port for server 
          
          if(array_key_exists("secure",$params["smtp"])){
            $mail->SMTPSecure = $params["smtp"]["secure"];  // set the SMTPSecure port for server (ssl,tls) 
          }
          
          $mail->SMTPAuth   = true;                  // enable SMTP authentication 
          if(array_key_exists("auth",$params["smtp"])){
            $mail->SMTPAuth = $params["smtp"]["auth"];
          }
          
          $mail->Username   = $params["smtp"]["user"]; // SMTP account username
          $mail->Password   = $params["smtp"]["pass"];        // SMTP account password
        }
        
        //$mail->SetFrom('press@nomosolutions.com', 'Press - Howislifetoday.com',1,'press@howislifetoday.com','Press - Howislifetoday.com');
        if($params["from"]){
          if(is_string($params["from"])) 
            $params["from"]=array("mail"=>$params["from"],"name"=>"");
          $mail->SetFrom($params["from"]["mail"], $params["from"]["name"],0);
        }
        
        if($params["replayto"]){
          if(is_string($params["replayto"])) 
            $params["replayto"]=array("mail"=>$params["replayto"],"name"=>"");
          $mail->AddReplyTo($params["replayto"]["mail"],$params["replayto"]["name"]);
        } 
        
        if($params["bcc"]){
          if(is_string($params["bcc"])) 
            $params["bcc"]=array("mail"=>$params["bcc"],"name"=>"");
          $mail->AddBCC($params["bcc"]["mail"], $params["bcc"]["name"]);
        }

        $mail->AddAddress($params["to"]);      
        $mail->Subject = '=?UTF-8?B?'.base64_encode($params["subject"]).'?=';
        //echo $mail->Subject;exit;
        //$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
        $mail->CharSet = "UTF-8";
        $mail->MsgHTML($params["body"]);
        
        if($params["MessageID"])
          $mail->MessageID=$params["MessageID"];
        
        //if($params["list-help"]) $mail->AddCustomHeader("List-Help: ".$params["list-help"]);
        //if($params["list-unsubscribe"]) $mail->AddCustomHeader("List-Unsubscribe: ".$params["list-unsubscribe"]);
        //$mail->AddAttachment('images/phpmailer.gif');      // attachment
        //$mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
        if($params["attachments"]){
          for($i=0;$i<count($params["attachments"]);$i++) {
            $attach=$params["attachments"][$i];
            if(!isset($attach["name"])) $attach["name"]=null;
            
            $mail->AddAttachment($attach["path"],$attach["name"]);
            
          }
        }
        //if($params["sender"]) $mail->Sender=$params["sender"];
        if($params["return-path"]) $mail->Sender=$params["return-path"];
        //$mail->Sender="pesti@nomo.hu";
        //pre_print_r($mail);exit;
        $mail->Send(); //Ok return -> true
        //var_dump($ret);exit;
        //echo "Message Sent OK</p>\n";
      } catch (phpmailerException $e) {
        $ret= $e->errorMessage(); //Pretty error messages from PHPMailer
      
      } catch (Exception $e) {
        //echo $e->getMessage(); //Boring error messages from anything else!
        $ret= $e->getMessage();
      }
      
      return $ret;
    }  
    
    
    public static function native_send($params){  
      $params=(array)$params;
        
      if(!isset($params["from"])) $params["from"]="noreply@".$_SERVER[SERVER_NAME];
      //define the receiver of the email
      $to = $params["to"];
      //pre_print_r($_SERVER);exit;
      //define the subject of the email
      $subject = $params["subject"];//'Regisztráció Sikeres Volt';
      //create a boundary string. It must be unique
      //so we use the MD5 algorithm to generate a random hash
      $random_hash = NomoUtils::rndgen(32);
      $mime_boundary="PHP-alt-".$random_hash;
      //define the headers we want passed. Note that they are separated with \r\n
      $headers = "From: ".$params["from"]."\r\n";
      $headers .= "Reply-To: ".$params["from"]."\r\n";
      //add boundary string and mime type specification
      $headers .= "Content-Type: multipart/alternative; boundary=\"".$mime_boundary."\"\r\n";
      //$headers .= "Content-Transfer-Encoding: 7bit\r\n";
  
      $message="";
      $message .="--".$mime_boundary."\r\n"; 
      $message .="Content-Type: text/plain; charset=\"utf-8\"\r\n"; 
      $message .="\r\n";
      
      $message .=NomoUtils::html2text($params["body"]);               
      $message .="\r\n"; 
           
      $message .="--".$mime_boundary."\r\n"; 
      $message .="Content-Type: text/html; charset=\"utf-8\"\r\n";
      $message .="\r\n";
       
      $message .=$params["body"];               
      $message .="\r\n";                 
      
      $message .="--".$mime_boundary."--\r\n";
  
      //send the email
      $mail_sent = @mail( $to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers );
      
      if($mail_sent!=true) $mail_sent="Nem sikerült elküldeni a levelet!";
      return $mail_sent;
    }
    
    
    /**
    Validate an email address.
    Provide email address (raw input)
    Returns true if the email address has the email 
    address format and the domain exists.
    */
    public static function isValid($email,$checkDomain = true){
      $isValid = true;
      $atIndex = strrpos($email, "@");
      if (is_bool($atIndex) && !$atIndex){
        $isValid = false;
      }else{
        $domain = substr($email, $atIndex+1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64){
           // local part length exceeded
           $isValid = false;
        }else if ($domainLen < 1 || $domainLen > 255){
           // domain part length exceeded
           $isValid = false;
        }else if ($local[0] == '.' || $local[$localLen-1] == '.'){
           // local part starts or ends with '.'
           $isValid = false;
        }else if (preg_match('/\\.\\./', $local)){
           // local part has two consecutive dots
           $isValid = false;
        }else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){
           // character not valid in domain part
           $isValid = false;
        }else if (preg_match('/\\.\\./', $domain)){
           // domain part has two consecutive dots
           $isValid = false;
        }else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local))){
           // character not valid in local part unless 
           // local part is quoted
           if (!preg_match('/^"(\\\\"|[^"])+"$/',
               str_replace("\\\\","",$local)))
           {
              $isValid = false;
           }
        }
        if ($isValid && $checkDomain &&  (!(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))){
        
                 pre_print_r("ssss"); exit;
           // domain not found in DNS
           $isValid = false;
        }
      }
      return $isValid;
    }   
    
    
    public static function sendSMS($params){
      $tocell=$params["tocell"];
      $message=urlencode($params["message"]);
      
      //do not edit
      $fromcell='+36705373909';
      $un='nomo.bt';
      $pw='lego158';

      ob_start();      
      $ch=curl_init();
      curl_setopt($ch,CURLOPT_URL,"https://www.voipbuster.com/myaccount/sendsms.php?username=$un&password=$pw&from=$fromcell&to=$tocell&text=$message");
      curl_exec($ch);
      curl_close($ch);
      ob_end_clean();
      return;
    }
  }
  

?>
