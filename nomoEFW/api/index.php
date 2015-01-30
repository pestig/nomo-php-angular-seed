<?php
try{

  if(count(nomo::$requestURIPathArray)>=3 && in_array(nomo::$requestURIPathArray[1],array("execute"))){
    $params=nomo::$requestURIPathArray;
    $params["args"]=nomo::$requestURIPathArray;
    foreach(nomo::$_GET as $key => $value)
      $params[$key]=$value;
      
      
    $req=array(
      "cmd" => "execute",
      "className" => nomo::$requestURIPathArray[2],
      "method" => nomo::$requestURIPathArray[3],
      "params" => $params
    );
  // http://nomo.hu/api/datatables/server_processing/
  }else if(count(nomo::$requestURIPathArray)>=3 && in_array(nomo::$requestURIPathArray[1],array("datatables"))){
    //pre_print_r(nomo::$requestURIPathArray[2]);exit;
    //include(__DIR__."/server_processing.php");

    $result=NomoAPI::get_datatables_select_params(nomo::$requestURIPathArray[2]);
    
    $req=array(
      "cmd" => "execute",
      "className" => nomo::$requestURIPathArray[2],
      "method" => "select",
      "params" => $result["selectParams"]
    );
    $resp=call_user_func(array("NomoAPI",$req["cmd"]),$req); 
    $rows=$resp["data"];
    $output=$result["output"];

    $fields=$result["fields"];
    $output['data']=$rows;
    
  	echo json_encode( $output );
    exit;
  }else{
    $params=array_merge(nomo::$_POST,nomo::$_GET);
    if(isset($params["json"])){
      if (1 == get_magic_quotes_gpc()){
        $params["json"]=stripslashes($params["json"]);
      }

      $req=json_decode($params["json"],true);
      if($req==null) 
        throw new Exception("Érvénytelen 'json' fromátum: ".$params["json"]);
    }else{
      throw new Exception("Hiányzó paraméter: 'json'");
    }  
    $req["cmd"]="execute";
    if(!method_exists('NomoAPI',$req["cmd"])) 
      throw new Exception("Hibás 'cmd' paraméter: ".$req["cmd"]);
  
    $notrace=$req["notrace"];
  } 
   
  $resp=call_user_func(array("NomoAPI",$req["cmd"]),$req); 

}catch(NomoException $e){    
  $resp=array();
  $resp["ret"]=$e->getCode();  
  $resp["userid"]=nomo::$session->userid;
  $resp["groupid"]=nomo::$session->groupid;
  $resp["message"]=$e->getMessage();
  $scriptfilepath=$e->getFile();
  $resp["file"]=$scriptfilepath;
  $resp["line"]=$e->getLine();
  $resp["data"]=$e->getData();
  if(!$notrace) $resp["trace"]=$e->getTraceAsString();
}catch(Exception $e){    
  $resp=array();
  $resp["ret"]=12;
  //$resp["userid"]=nomo::$session->userid;
  //$resp["groupid"]=nomo::$session->groupid;
  $resp["message"]=$e->getMessage();
  $scriptfilepath=$e->getFile();
  $resp["file"]=$scriptfilepath;
  $resp["line"]=$e->getLine();
  //if(!$notrace) $resp["trace"]=$e->getTraceAsString();
}


if($resp["ret"]!=0){
    header("HTTP/1.0 422 Unprocessable Entity");
}
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

if($params["responseFormat"]=="jsonInHTMLBody"){ 
  header('Content-type: text/html');
  echo "<HTML><BODY>".json_encode($resp)."</BODY></HTML>";
}elseif($params["responseFormat"]=="javascript"){
  header('Content-type: text/html');
  echo $params["cbFunctionName"]."(".json_encode($resp).")";
}elseif($params["responseFormat"]=="text"){
  header('Content-type: text/plain');
  echo $resp["data"];
}else{
  header('Content-type: application/json');
  header('Content-length: '.strlen(json_encode($resp)).'');
  //echo ")]}',\n".json_encode($resp);
  echo json_encode($resp);
}
?>
