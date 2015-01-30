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
    

    public static function get_datatables_select_params($type){
        $_GET=nomo::$_POST;
        /*
         * Script:    DataTables server-side script for PHP and MySQL
         * Copyright: 2010 - Allan Jardine
         * License:   GPL v2 or BSD (3-point)
         */

        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Easy set variables
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        //$definition=NomoTypeDefinition::getDefinitionOf($type);
        $json=$_GET["json"];
        if (1 == get_magic_quotes_gpc()){
          $json=stripslashes($json);
        }
        $json=json_decode($json,true);

        $selectParams=array();
        $filterParams=array();
        $orderbyParams=array();



        if($json["resultType"]) $selectParams["resultType"]=$json["resultType"];
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * If you just want to use the basic configuration for DataTables with PHP server-side, there is
         * no need to edit below this line
         */

        /*
         * Paging
         */
        $sLimit = "";
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
          $selectParams["offset"]=$_GET['iDisplayStart'];
          $selectParams["numberOfRows"]=$_GET['iDisplayLength'];
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                  $field=$json["fields"][intval( $_GET['iSortCol_'.$i] )];
                  if(!empty($field)){
                    $orderbyParams=array(
                  "field"=>$field["name"],
                  "direction"=>(strtoupper($_GET['sSortDir_'.$i])=="DESC")?1:0
                );
                  }
                }
            }
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if ( $_GET['sSearch'] != "" )
        {
            /*$sWhere = "WHERE (";
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
            */
            for ( $i=0 ; $i<count($json["fields"]) ; $i++ )
            {
              $field=$json["fields"][$i];
              if(!empty($field)){
               array_push($filterParams,array(
                 "field"=>$field["name"],
                 "operator"=>"like",
                 "value"=>"%". $_GET['sSearch'] ."%"
               ));
            }
            }
        }

        /* Individual column filtering */
        /*for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
            }
        }*/

        for ( $i=0 ; $i<count($json["fields"]) ; $i++ )
        {
          $field=$json["fields"][$i];
            if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if(!empty($field)){
               array_push($filterParams,array(
                 "field"=>$field["name"],
                 "operator"=>"like",
                 "value"=>"%". $_GET['sSearch_'.$i] ."%"
               ));
            }
            }
        }


        for ( $i=0 ; $i<count($json["filters"]) ; $i++ )
        {
          $filter=$json["filters"][$i];
          array_push($filterParams,$filter);
        }



        $iFilteredTotal=$type::select(array("resultType"=>"count","filters"=>$filterParams));
        $iTotal=$type::select(array("resultType"=>"count"));

        $selectParams["filters"]=$filterParams;
        if(!empty($orderbyParams))
          $selectParams["orderby"]=$orderbyParams;
        $selectParams["subseparator"]="_";


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal
          //,"aaData" => array()
        );

        return array(
          "selectParams"=>$selectParams,
          "output"=>$output,
          "fields"=>$json["fields"]
        );

    }
  }
?>
