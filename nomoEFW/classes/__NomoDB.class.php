<?php  
  class __NomoDB extends PDO{
    private static $connections = array();

    public static function getConnection($connName = "default") {
      if(!isset(nomo::$dbConnetctions[$connName]))
        throw new NomoException("Nincs ilyen adatbázis kapcsolat definiálva: '$connName'!",11);
         
      if (!isset(self::$connections[$connName])) {
        $connData= nomo::$dbConnetctions[$connName];
        if($connData["provider"]=="mysql"){
          //self::$connections[$connName] = new PDO('odbc:MSSQLServer', $connData["user"], $connData["pass"], array(PDO::ATTR_PERSISTENT => MYSQL_PCONNECT));
          self::$connections[$connName] = new static('mysql:host='.$connData["host"].';dbname='.$connData["db"], $connData["user"], $connData["pass"], array(PDO::ATTR_PERSISTENT => MYSQL_PCONNECT));          
          //self::$connections[$connName] = new static('sqlite:/var/www/_pesti/immogen.nomo.hu/www/tmp/immogen');
          self::$connections[$connName]->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
          self::$connections[$connName]->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);  //hogy az integer valóban integer legyen vissztéréskor
          
          
          
          self::$connections[$connName]->exec("SET NAMES utf8 COLLATE utf8_hungarian_ci");
          self::$connections[$connName]->exec("SET SESSION sql_mode = ''");
        } else if($connData["provider"]=="mssql"){
          self::$connections[$connName] = new static('sqlsrv:Server='.$connData["host"].';Database='.$connData["db"], $connData["user"], $connData["pass"]);
          self::$connections[$connName]->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);          
        }
      }
      return self::$connections[$connName];
    }
    
    public function query($query,$sqlresultonly=false){
      try{
        $result=PDO::query($query);
      }catch(PDOException $e){
        //var_dump ($e) ;exit
        throw new NomoException($e->getMessage()."".$query, 11);
      } 
      
      
      if($result===false){
        $msg=" ".PDO::errorInfo()." >>>>>>>>> query: " . $query;
        //if(strlen($msg)>1024) $msg=" ".PDO::errorInfo()." >>>>>>>>> query: long...";       
        throw new NomoException($msg, 11);
      }elseif($result!==true){
        $records=array();
        if(!$sqlresultonly){
          if($result->columnCount()>0){ 
            $records=$result->fetchAll(PDO::FETCH_ASSOC);
          }
          $result->closeCursor();
        }
      }
      
      $ret=array();
      $ret["resource"]=$result;
      $ret["insert_id"]=static::lastInsertId(); 
      $ret["affected_rows"]=$result->rowCount();
      $ret["rows"]=$records;
      return $ret;
    }
  }
?>
