<?php
//required: Session class, DB class
class __NomoDataSource{
  public static $label=false;

  protected static function getControlType($type){
    $controlType;
    if(preg_match("/^int/i",$type)){
      $controlType="number";
    } else if(preg_match("/^double/i",$type)){
      $controlType="number_double";
    } else if(preg_match("/^varchar/i",$type)){
      $controlType="text";
    } else if(preg_match("/text/i",$type)){
      $controlType="textarea";
    } else if(preg_match("/^enum/i",$type)){
      $controlType="select";
    } else if(preg_match("/^datetime/i",$type)){
      $controlType="datetime";
    } else if(preg_match("/^date/i",$type)){
      $controlType="date";
    }  else if(preg_match("/^time/i",$type)){
      $controlType="time";
    } else if(preg_match("/tinyint\(1\)/i",$type)){
      $controlType="checkbox";
    } else {
      $controlType="text";
    }
    return $controlType;
  }

  protected static function &getFieldByName($fieldName,&$definition){
    for($i=0;$i<count($definition["fields"]);$i++){
      if($definition["fields"][$i]["name"]==$fieldName) {
          return $definition["fields"][$i];
      }
    }
    return null;
  }

  protected static function getPrimaryKey($definition){
    for($i=0;$i<count($definition["fields"]);$i++){
      if($definition["fields"][$i]["primarykey"])
        return $definition["fields"][$i];
    }
    return null;
  }

  public static $__ajax__getDefinition_whitelist=true;
	public static function getDefinition($params = array(),$groupid = FALSE,$definition=array()){
    if(isset(static::$sql_case_sensitive_table_names) && static::$sql_case_sensitive_table_names==true)
      $sqltable=get_called_class();
    else
      $sqltable=strtolower(get_called_class());

    if(empty($definition["table"])) $definition["table"]=$sqltable;
    if(empty($definition["from"])) $definition["from"]=$definition["table"];
    if(empty($definition["fields"])) $definition["fields"]=array();


    $dbhandler=NomoDB::getConnection();
    $sqlresult=$dbhandler->query("SHOW FULL COLUMNS FROM ".$definition["table"]);


    for($i=0;$i<count($sqlresult["rows"]);$i++){
      $row=$sqlresult["rows"][$i];
      $field=array(
        "name"=>$row["Field"],
        "label"=>$row["Comment"]
      );

      if ($row["Default"]!==NULL) {
        $field["default"]=$row["Default"];
      }

      $field["type"]=$row["Type"];
      $field["controltype"]=static::getControlType($row["Type"]);
      if(preg_match("/^enum/i",$row["Type"])){
      //if($field["controltype"]=="select"){
        preg_match_all("/'([^']*)'/",$field["type"],$enumTypes);
        $field["options"]= $enumTypes[1];
      }

      $field["visible"]=array("default"=>true);
      if($i<4)
        $field["visible"]["grid"]=true;
      else
        $field["visible"]["grid"]=false;

      if(!$field["label"]) $field["label"]=$field["name"];
      if($row["Key"]=="PRI") $field["primarykey"]=true;
      array_push($definition["fields"],$field);
    }

    //nomocms mezők kezelése
    $field=&static::getFieldByName("rowid",$definition);
    if($field){
      $field["visible"]=array("default"=>false);
    }
    $field=&static::getFieldByName("nomocms_modified_by",$definition);
    if($field){
      $field["controltype"]="select2ajax";
      $field["visible"]=array("default"=>false);
      $field["params"]=array("type_id"=>"User");
    }
    $field=&static::getFieldByName("nomocms_modified_at",$definition);
    if($field){
      $field["visible"]=array("default"=>false);
    }
    $field=&static::getFieldByName("nomocms_created_by",$definition);
    if($field){
      $field["controltype"]="select2ajax";
      $field["visible"]=array("default"=>false);
      $field["params"]=array("type_id"=>"User");
    }
    $field=&static::getFieldByName("nomocms_created_at",$definition);
    if($field){
      $field["visible"]=array("default"=>false);
    }
    $field=&static::getFieldByName("nomocms_status",$definition);
    if($field){
      $field["visible"]=array("default"=>false);
    }

    return $definition;
  }

	public static $__ajax__getReportDefinition_whitelist=true;
	public static function getReportDefinition($params = array(),$groupid = FALSE,$definition=array()){
		$definition=static::getDefinition();


	  // A(z) [xaxis] időszakra vonatkozó [yaxis] [interval]
		// pl: A(z) [1999-01-02 - 2014-01-01] időszakra vonatkozó [kezelesek szama] [heti] bontásban

		$result=array(
		  "xaxis_options"=>array(
			),
			"yaxis_options"=>array(
				array(
					"name"=>"rowid_count",
					"label"=>get_called_class()." darabszáma",
					"sqlexpr"=>"COALESCE(COUNT(rowid),0)"
				)
			),
			"interval_options"=>array(
				array(
					"name"=>"month",
					"label"=>"havi",
				),
				array(
					"name"=>"week",
					"label"=>"heti",
				),array(
					"name"=>"day",
					"label"=>"napi",
				)
			)
		);

		for($i=0;$i<count($definition["fields"]);$i++){
			$field=$definition["fields"][$i];
			if(in_array($field["controltype"],array("date","datetime"))){
				$xaxis_item=array(
					"name"=>$field["name"],
					"label"=>$field["label"]
				);
				array_push($result["xaxis_options"],$xaxis_item);
			}


			if(in_array($field["controltype"],array("number")) && $field["name"]!="rowid"){
				if($field["sqlexpr"])
					$yaxis_sql=$field["sqlexpr"];
				else
					$yaxis_sql=$field["name"];

				$yaxis_item=array(
					"name"=>$field["name"]."___sum",
					"label"=>$field["label"]." összege",
					"sqlexpr"=>"COALESCE(SUM(".$yaxis_sql."),0)"
				);
				array_push($result["yaxis_options"],$yaxis_item);

				$yaxis_item=array(
					"name"=>$field["name"]."___avarage",
					"label"=>$field["label"]." átlaga",
					"sqlexpr"=>"COALESCE(AVG(".$yaxis_sql."),0)"
				);
				array_push($result["yaxis_options"],$yaxis_item);
			}
		}
		return $result;
	}

	public static $__ajax__getReport_whitelist=array(1);
	public static function getReport($params = array(),$groupid = FALSE){
		if($groupid === FALSE) $groupid=nomo::$session->groupid;

		$result=array();

		$definition=static::getDefinition();
		$reportDefinition=static::getReportDefinition();

		$dbhandler=NomoDB::getConnection();
		$dbhandler->query("SET lc_time_names = 'hu_HU'");


		$yaxis=null;
		for($i=0;$i<count($reportDefinition["yaxis_options"]);$i++){
			if($reportDefinition["yaxis_options"][$i]["name"]==$params["yaxis"])
				$yaxis=$reportDefinition["yaxis_options"][$i];
		}
		if($yaxis==null )
		  throw new NomoException('Érvénytelen lekérdezés!',5);

		$xaxis=null;
		for($i=0;$i<count($reportDefinition["xaxis_options"]);$i++){
		  if($reportDefinition["xaxis_options"][$i]["name"]==$params["xaxis"])
				$xaxis=$reportDefinition["xaxis_options"][$i];
		}
		if($xaxis==null)
		  throw new NomoException('Érvénytelen lekérdezés!',5);

		$interval=null;
		for($i=0;$i<count($reportDefinition["interval_options"]);$i++){
		  if($reportDefinition["interval_options"][$i]["name"]==$params["interval"])
				$interval=$reportDefinition["interval_options"][$i];
		}
		if($interval==null )
		  throw new NomoException('Érvénytelen lekérdezés!',5);

		$field=static::getFieldByName($xaxis["name"],$definition);
		if($field["sqlexpr"])
			$xaxis_sql=$field["sqlexpr"];
		else
			$xaxis_sql=$field["name"];

		if(!isset($params["filters"])) $params["filters"]=array();
		array_push($params["filters"],array(
			"field"=>$xaxis["name"],
			"operator"=>">",
			"value"=>$params["from"]
		));

		array_push($params["filters"],array(
			"field"=>$xaxis["name"],
			"operator"=>"<",
			"value"=>$params["to"]
		));

		$rowIDs=static::select(array(
		  "filters"=>$params["filters"],
			"numberOfRows"=>"*",
			"resultType"=>"rowids"
		));

		if(count($rowIDs)>0){
			$select_yaxis=$yaxis["sqlexpr"];
			if($interval["name"]=="month"){
				//$select_xaxis="CONCAT(YEAR(".$xaxis_sql.") ,'-',MONTHNAME(".$xaxis_sql."))";
				$select_xaxis="(UNIX_TIMESTAMP(CONCAT(DATE_FORMAT((".$xaxis_sql."),'%Y-%m'),'-01')) +0.0)*1000";
			}else if($interval["name"]=="day"){
				$select_xaxis="(UNIX_TIMESTAMP( DATE_FORMAT((".$xaxis_sql."),'%Y-%m-%d') ) +0.0) *1000";
				//$select_xaxis=" DATE_FORMAT((SELECT technikusmunkalap.datum FROM technikusmunkalap WHERE kezeles.munkalap=technikusmunkalap.rowid),'%Y-%m-%d') ";
				$result["xaxis_mode"]="time";
			}else if($interval["name"]=="week"){
				$select_xaxis="CONCAT(YEAR(".$xaxis_sql.") ,', ',WEEK(".$xaxis_sql."),'. hét')";
				//$select_xaxis=" DATE_FORMAT((SELECT technikusmunkalap.datum FROM technikusmunkalap WHERE kezeles.munkalap=technikusmunkalap.rowid),'%Y-%m-%d') ";
			}else{
				throw new NomoException('Érvénytelen intervallum: '.$interval["name"].'!',5);
			}


			$rows=static::select(array(
				"fields"=>array("xaxis","yaxis"),
				"sqlfields"=>array(
					"xaxis"=>$select_xaxis,
					//"xaxis"=>"CONCAT(YEAR(".$xaxis_sql.") ,'-',MONTHNAME(".$xaxis_sql."))",
					//"xaxis"=>"(SELECT DATE_FORMAT(technikusmunkalap.datum,'%Y-%m') FROM technikusmunkalap WHERE kezeles.munkalap=technikusmunkalap.rowid)",
					//"xaxis"=>"UNIX_TIMESTAMP( CONCAT(DATE_FORMAT((SELECT technikusmunkalap.datum FROM technikusmunkalap WHERE kezeles.munkalap=technikusmunkalap.rowid),'%Y-%m'),'-01')  )*1000",
					"yaxis"=>$select_yaxis //"COALESCE(COUNT(rowid),0)"
				),
				"sqlgroupby"=>$select_xaxis,
				"filters"=>array(
					array(
						"field"=>"rowid",
						"operator"=>"in",
						"value"=>$rowIDs
					)
				),
				"numberOfRows"=>"*",
				"orderby"=>array(
					"sqlorderby"=>$select_xaxis
				)
			));
		}else{
		  $rows= array();
		}

		$result["rows"]=$rows;

		return $result;
	}

  public static $__ajax__select_whitelist=true;//false;
	public static function select($params = array(),$groupid = FALSE){
    if($groupid === FALSE) $groupid=nomo::$session->groupid;
    $definition=static::getDefinition(array("method"=>__FUNCTION__), $groupid);
    $dbhandler=NomoDB::getConnection();

    //DEFAULT resultType HANDLING BEGIN
    if(in_array($params["resultType"],array("grid","form")) /*|| $params["buildCache"]*/){
      if(!$params["sqlfields"]) $params["sqlfields"]=array();

      for($i=0;$i<count($definition["fields"]);$i++){
        $field=$definition["fields"][$i];
        if(!$params["sqlfields"][$field["name"]."_nomoefw_label"]){
          if(in_array($field["controltype"],array("select2ajax","selectajax"))) {
						if($field["sqlexpr"]){
							if($field["sqlfields"])
								foreach($field["sqlfields"] as $key => $value){
									$params["sqlfields"][$key]=$value;
								}
						}else{
							$fieldClassName=$field["params"]["type_id"];
							$fieldDefinition=$fieldClassName::getDefinition();

							if($fieldClassName::$label) $label=str_replace("{{tableprefix}}", "label.", $fieldClassName::$label);
							else $label="label.".$fieldDefinition["fields"][1]["name"]."";

							$params["sqlfields"][$field["name"]."_nomoefw_label"]=
								"(SELECT ".$label." FROM ".$fieldDefinition["table"]." AS label WHERE label.rowid=".$definition["table"].".".$field["name"].")";
						}
          }elseif(in_array($field["controltype"],array("checkbox"))){
            $params["sqlfields"][$field["name"]."_nomoefw_label"]="IF(".$field["name"]."=1,'Igen','Nem')";

          }
        }
      }

      /*if($params["buildCache"]){
        if(static::$label) $label=str_replace("{{tableprefix}}", "", static::$label);
        else $label="".$definition["fields"][1]["name"]."";
        $params["sqlfields"]["label"]=$label;
      } */
    }

    if(!isset($params["status_whitelist"])){
      if($params["resultType"]=="form")
        $params["status_whitelist"]=array("exists","draft");
      else
        $params["status_whitelist"]=array("exists");
    }

    if($params["resultType"]=="select2"){
      if(!$params["sqlfields"]) {
        $params["fields"]=array("rowid","label");
        if(static::$label) $label=str_replace("{{tableprefix}}", "", static::$label);
        else $label="".$definition["fields"][1]["name"]."";
        $params["sqlfields"]=array(
          "label"=>$label
        );
      }
    }
    //DEFAULT resultType HANDLING END

    //PROCESS $params BEGIN
    if($params["sqlfields"]) {
      foreach($params["sqlfields"]  as $name=>$value){
        $field=&static::getFieldByName($name,$definition);
        if($field){
          $field["sqlexpr"]=$value;
        }else{
          array_push($definition["fields"],array(
            "name"=>$name,
            "label"=>$name,
            "type"=>"sqlexpr",
            "controltype" => "text",
            "sqlexpr"=>$value
          ));
        }
        unset($field);
      }
    }



    //set input params
    if(!isset($params["with_filedata"])) $params["with_filedata"]=false;
    if(!isset($params["status_whitelist"])) $params["status_whitelist"]=array("exists");
    if(!isset($params["offset"])) $params["offset"]=0;
    if(!isset($params["numberOfRows"])) $params["numberOfRows"]=30;
    if(!isset($params["resultType"])) $params["resultType"]="rows";
    if(!isset($params["sqlresultonly"])) $params["sqlresultonly"]=false;
    if(!isset($params["filters"])) $params["filters"]=array();
    if(!isset($params["subseparator"])) $params["subseparator"]="_";

		if(!isset($params["cast_sql_result"])) {
		  if(defined('CAST_SQL_RESULT')){
			  $params["cast_sql_result"]=CAST_SQL_RESULT;
	 	  }else{
				$params["cast_sql_result"]=false;
			}
		}

    if(!is_array($params["filters"])) throw new NomoException('a select params["options"]["filters"] csak tömb lehet!',5);
		for($j=0;$j<count($params["filters"]);$j++){
		  $params["filters"][$j]=(array)$params["filters"][$j];
      if(!isset($params["filters"][$j]["operator"])) $params["filters"][$j]["operator"]="=";
      if($params["filters"][$j]["operator"]=="%like"){
        $params["filters"][$j]["operator"]="like";
        $params["filters"][$j]["value"]="%".$params["filters"][$j]["value"];
      }elseif($params["filters"][$j]["operator"]=="like%"){
        $params["filters"][$j]["operator"]="like";
        $params["filters"][$j]["value"]=$params["filters"][$j]["value"]."%";
      }elseif($params["filters"][$j]["operator"]=="%like%"){
        $params["filters"][$j]["operator"]="like";
        $params["filters"][$j]["value"]="%".$params["filters"][$j]["value"]."%";
      }
    }
		if(!isset($params["fields"])){
		  $params["fields"]=array();
		  for($i=0;$i<count($definition["fields"]);$i++){
		    array_push($params["fields"],$definition["fields"][$i]["name"]);
		  }
		}

    if(is_array($params["fields"])){
      $fields=array();
		  for($i=0;$i<count($definition["fields"]);$i++){
        if(in_array($definition["fields"][$i]["name"],$params["fields"]))
		      array_push($fields,$definition["fields"][$i]);
		  }
    }else{
      throw new NomoException('a select params["options"]["fields"] csak tömb lehet!',5);
    }

    //set order by input param
    if(!isset($params["orderby"]) && $definition["orderby"]){
      $params["orderby"]=array(
        "field"=>$definition["orderby"]["field"]
      );
      if($definition["orderby"]["direction"]==1)
        $params["orderby"]["direction"]=1;
    }
    //PROCESS $params END



    //BUILD Query STRING BEGIN
    $result=array();
    $querySELECT="";
    for($i=0;$i<count($fields);$i++){
      $field=$fields[$i];
      if(!$field["filter_only"]){
        if(isset($field["sqlexpr"])){
          $sqlname=$field["sqlexpr"]. " AS `".$field["name"]."`";
        }else{
          $sqlname="`".$field["name"]."`";
          if($field["aes_crypt_hash"])
            $sqlname="AES_DECRYPT(".$sqlname.",'".$field["aes_crypt_hash"]."')";

        }

        if($querySELECT!="") $querySELECT.=", ";
        $querySELECT.=$sqlname;
      }
    }

    //Query összeállítása, ha van mit lekérni
	  if($querySELECT!=""){
      //FROM
			if($params["sqlfrom"])
				$queryFROM=$params["sqlfrom"];
			else
      	$queryFROM=$definition["from"];

      //WHERE
      $queryWHERE=" 1=1";

      $field_nomocms_status=static::getFieldByName("nomocms_status",$definition);
      if($field_nomocms_status){
        $queryWHERE.=" AND nomocms_status IN ('".implode("','",$params["status_whitelist"])."')";
      }

      if($definition["where"]){
        $queryWHERE.=" AND ".$definition["where"];
      }

      $queryFilters="";

      for($i=0;$i<count($params["filters"]);$i++){
        $filter=(array)$params["filters"][$i];
        $field=static::getFieldByName($filter["field"],$definition);
        if(!$field) {
          throw new NomoException("Érvénytelen szűrő mező: ".$filter["field"]."!",5);
        }

        if(isset($field["sqlexpr"])){
          $sqlname=$field["sqlexpr"]. " ";
        }else{
          $sqlname="`".$field["name"]."`";
          if($field["aes_crypt_hash"])
            $sqlname="AES_DECRYPT(".$sqlname.",'".$field["aes_crypt_hash"]."')";
        }


        //tömb value kezelése
        if($filter["operator"]=="in" || $filter["operator"]=="not in"){
          if(!is_array($filter["value"]))
            throw new NomoException("Érvénytelen szűrőmező, az 'in' és 'not in' operátorhoz array típusú mező szükséges!",5);

          if(count($filter["value"])>0){
            $stringValue="(";
            for($j=0;$j<count($filter["value"]);$j++){
              if($j!=0) $stringValue.=",";
              $stringValue.="'".$filter[value][$j]."'";
            }
            $stringValue.=")";
          } /*else {
            // ha "in" az operátor, és üres a lista, akkor AND FALSE kell
            // ha "not in" az operátor és üres a lista, akkor semmi szűrés nem kell --> minden visszaad
            if($filter["operator"]=="in"){
              $filterString.=" AND FALSE";
            }
          } */
          $queryFilters.=" AND ".$sqlname." ".$filter["operator"]." $stringValue";
        } else{
          $queryFilters.=" AND ".$sqlname." ".$filter["operator"]." '".$filter["value"]."'";
        }
      }

      $queryWHERE.=$queryFilters;

      if($params["sqlwherepostfix"]) $queryWHERE.=" ".$params["sqlwherepostfix"];


      $queryGROUPBY="";
      if(isset($params["sqlgroupby"])){
        $queryGROUPBY =" GROUP BY ".$params["sqlgroupby"];
      }


      //ORDER BY
      $queryORDER="";
      if(isset($params["orderby"])){

				if(isset($params["orderby"]["sqlorderby"])){
					$queryORDER.=" ORDER BY ".$params["orderby"]["sqlorderby"];
				}else{
					$field=static::getFieldByName($params["orderby"]["field"],$definition);
					if(!$field) throw new NomoException("Érvénytelen szűrő mező: ".$params["orderby"]["field"]."!",5);

					$sqlname="`".$field["name"]."`";
					if($field["aes_crypt_hash"])
					$sqlname="AES_DECRYPT(".$sqlname.",'".$field["aes_crypt_hash"]."')";
					$queryORDER.=" ORDER BY ".$sqlname;
				}
        if($params["orderby"]["direction"]==1)  $queryORDER.=" DESC";
      }

      //LIMIT;
      $queryLIMIT="";
      if($params["numberOfRows"]!=="*"){
        $queryLIMIT.=" LIMIT ".($params["offset"]).",".(int)$params["numberOfRows"]."";
      }
      //BUILD Query STRING END


      //RUN Query BEGIN
      if($params["resultType"]=="count"){
        if(!empty($queryGROUPBY)){
          $select="SELECT COUNT(*) AS nor FROM (".
                     "SELECT ".$params["sqlgroupby"]." AS nor FROM ".$queryFROM." ".
                     "WHERE ".$queryWHERE." ".$queryGROUPBY.
                  ") MyTable";
        }else{
          $select="SELECT COUNT(*) AS nor FROM ".$queryFROM." ".
                "WHERE ".$queryWHERE." ".$queryGROUPBY;
        }
        $sqlresult=$dbhandler->query($select);
        $result=$sqlresult["rows"][0]["nor"];
      }else{
        $distinct=($params["distinct"])?"DISTINCT":"";

        $select="SELECT ".$distinct." ".$querySELECT." FROM ".$queryFROM." ".
              "WHERE ".$queryWHERE." ".$queryGROUPBY. " " . $queryORDER ." ".$queryLIMIT;

        $sqlResource=($params["resultType"]=="sqlresource" || $params["sqlResource"]);

        if($sqlResource || $params["resultType"]=="rowids") $sqlresultonly=true;
        //if($params["firephplog"]) FB::log($select);
				if($params["firephplog"])  pre_print_r($params);

  	    $sqlresult=$dbhandler->query($select,$sqlresultonly);

  	    if($sqlResource){
  	      $result=$sqlresult;
  	    }elseif($params["resultType"]=="rowids"){
          $rowids=array();
          while ($row = $sqlresult["resource"]->fetch(PDO::FETCH_ASSOC)) {
            $primarykey=static::getPrimaryKey($definition);
            array_push($rowids,$row[$primarykey["name"]]);
          }
          $sqlresult["resource"]->closeCursor();
          $result=$rowids;
        }else{
					if($params["cast_sql_result"]){
						$numberCloumns=array();
						for ($i=0;$i<count($fields);$i++){
							if(
                $fields[$i]["name"]!="label" &&
                (
                  $fields[$i]["type"]=="sqlexpr"
                  ||
                  preg_match("/^int/i",$fields[$i]["type"])
                  ||
                  preg_match("/tinyint\(1\)/i",$fields[$i]["type"])
                )
						  ){
								array_push($numberCloumns,$fields[$i]["name"]);
							}
						}
						//pre_print_r($numberCloumns);exit;
					  for ($i=0;$i<count($sqlresult["rows"]);$i++){
						  for ($j=0;$j<count($numberCloumns);$j++){
								if (ctype_digit((string)$sqlresult["rows"][$i][$numberCloumns[$j]])){
									$sqlresult["rows"][$i][$numberCloumns[$j]]=(int)$sqlresult["rows"][$i][$numberCloumns[$j]];
								  //var_dump($sqlresult["rows"][$i][$numberCloumns[$j]]);
								}
							}
						}
					}

          $result=$sqlresult["rows"];
        }
  	  }
      //RUN Query END
	  }

	  return $result;
  }
  protected static function processMask($value,$definitionField,$unmask=false){
    return $value;
  }
  public static function normalizeRecord($params = array(), $groupid = FALSE, $method ="create"){
    if($groupid === FALSE) $groupid=nomo::$session->groupid;

    $definition=static::getDefinition($params,$groupid);
    $errors=array();
    for($i=0;$i<count($definition["fields"]);$i++){
      $field=$definition["fields"][$i];
      if(
        $field["required"]
        &&
        ($method=="create" || array_key_exists($field["name"],$params["record"]) )
        &&
        empty($params["record"][$field["name"]])
      ){

        array_push($errors,$field["label"]);
      }

      if(array_key_exists($field["name"],$params["record"])){
        if($field["controltype"]=="name")
          $params["record"][$field["name"]]=mb_convert_case($params["record"][$field["name"]], MB_CASE_TITLE, "UTF-8");
        if($field["mask"]){
          $params["record"]=static::processMask($params["record"],$field,true);
        }elseif($field["controltype"]=="number_double"){
          $params["record"][$field["name"]]=str_replace(",",".",$params["record"][$field["name"]]);
        }
      }


    }
    if(count($errors)>0)
       throw new NomoException('A Követező mezőket kitölteni:<br />'.implode("<br />", $errors),19);

    return $params;
  }

  public static $__ajax__create_whitelist=false;
	public static function create($params = array(),$groupid = FALSE){
	    if($groupid === FALSE) $groupid=nomo::$session->groupid;

      $definition=static::getDefinition(array("method"=>__FUNCTION__), $groupid);
      $dbhandler=NomoDB::getConnection();

      if(!isset($params["record"]))
        throw new NomoException("Híányzó paraméter a create hívásból: record!",5);

      $params=static::normalizeRecord($params,$groupid);
      $record=$params["record"];

      //insert query készítése a saját táblába
      $names="";$values="";
      for($i=0;$i<count($definition["fields"]);$i++){
        $field=$definition["fields"][$i];
        if($field["name"]=="nomocms_created_at"){
          if($names!="")  {$names.=", ";$values.=", ";}
          $names.="`nomocms_created_at`";
          $values.="NOW()";
        }elseif($field["name"]=="nomocms_created_by"){
          if($names!="")  {$names.=", ";$values.=", ";}
          $names.="`nomocms_created_by`";
          $values.="'".nomo::$session->userid."'";
        }elseif($field["name"]=="nomocms_modified_at"){
          if($names!="")  {$names.=", ";$values.=", ";}
          $names.="`nomocms_modified_at`";
          $values.="NOW()";
        }elseif($field["name"]=="nomocms_modified_by"){
          if($names!="")  {$names.=", ";$values.=", ";}
          $names.="`nomocms_modified_by`";
          $values.="'".nomo::$session->userid."'";
        }elseif(array_key_exists($field["name"],$record) && !$field["sqlexpr"]){
          if($record[$field["name"]]===NULL)
            $value="NULL";
          else if($field["filter"]=="none")
            $value="'".addslashes($record[$field["name"]])."'";
          else if($field["filter"]=="html")
            $value="'".addslashes(NomoUtils::htmlFilter($record[$field["name"]]))."'";
          else
            $value="'".addslashes(NomoUtils::textFilter($record[$field["name"]]))."'";

          if($names!="")  {$names.=", ";$values.=", ";}
          $names.="`".$field["name"]."`";

          if($field["aes_crypt_hash"]) $value="AES_DECRYPT(".$value.",'".$field["aes_crypt_hash"]."')";
          $values.=$value;
        }
      }

      $sqlstr="INSERT INTO `".$definition["table"]."` (".$names.") VALUES(".$values.")";
      $sqlresult=$dbhandler->query($sqlstr);

      return array("record"=>$record,"sqlresult"=>$sqlresult);
  }

  public static $__ajax__update_whitelist=false;
	public static function update($params = array(),$groupid = FALSE){
      if($groupid === FALSE) $groupid=nomo::$session->groupid;
      $definition=static::getDefinition(array("method"=>__FUNCTION__), $groupid);
      $dbhandler=NomoDB::getConnection();

      $params=static::normalizeRecord($params,$groupid,"update");
      $record=$params["record"];

      if(array_key_exists("filters",$params)){
        $rowids=static::select(array(
          "filters"=>$params["filters"],
          "numberOfRows"=>"*",
          "resultType"=>"rowids",
          "status_whitelist"=>array("exists","draft","deleted")
        ),$groupid);
        if(count($rowids)<1)
          throw new NomoException("Nincs ilyen elem az adatbázisban!",21);
      } else throw new NomoException("Hiányzó paraméter: filters!",21);



      $querySET="";
      for($i=0;$i<count($definition["fields"]);$i++){
        $field=$definition["fields"][$i];
        if($field["name"]=="nomocms_modified_at"){
          $value="NOW()";
          if($querySET!="") $querySET.=", ";
          $querySET.=" `".$field["name"]."`=".$value."";
        }elseif($field["name"]=="nomocms_modified_by"){
          $value="'".nomo::$session->userid."'";
          if($querySET!="") $querySET.=", ";
          $querySET.=" `".$field["name"]."`=".$value."";
        }elseif(array_key_exists($field["name"],$record) && !$field["sqlexpr"]){
          if($record[$field["name"]]===NULL)
            $value="NULL";
          else if($field["filter"]=="none")
            $value="'".addslashes($record[$field["name"]])."'";
          else if($field["filter"]=="html")
            $value="'".addslashes(NomoUtils::htmlFilter($record[$field["name"]]))."'";
          else
            $value="'".addslashes(NomoUtils::textFilter($record[$field["name"]]))."'";
          if($field["aes_crypt_hash"]) $value="AES_DECRYPT(".$value.",'".$field["aes_crypt_hash"]."')";

          if($querySET!="") $querySET.=", ";
          $querySET.=" `".$field["name"]."`=".$value."";
        }
      }

      //pre_print_r($querySET);
      $sqlresult=false;
      if($querySET!="" && count($rowids)>0){
        $query="UPDATE `".$definition["table"]."` SET ".$querySET." ";
        $primarykey=static::getPrimaryKey($definition);
        $query.="WHERE `".$primarykey["name"]."` in (".implode(",",$rowids).")";
        $sqlresult=$dbhandler->query($query);
      }

      return array("record"=>$record,"sqlresult"=>$sqlresult,"rowids"=>$rowids);
  }

  public static $__ajax__delete_whitelist=false;
	public static function delete($params = array(),$groupid = FALSE){
    if($groupid === FALSE) $groupid=nomo::$session->groupid;
    $definition=static::getDefinition(array("method"=>__FUNCTION__), $groupid);
    $dbhandler=NomoDB::getConnection();

    if(array_key_exists("filters",$params)){
      $rowids=static::select(array(
        "filters"=>$params["filters"],
        "numberOfRows"=>"*",
        "resultType"=>"rowids"
      ),$groupid);
    }else throw new NomoException("Hiányzó paraméter: filters!",21);




    $sqlresult=false;
    if(count($rowids)>0){
      $query="DELETE FROM `".$definition["table"]."` ";
      $primarykey=static::getPrimaryKey($definition);
      $query.="WHERE `".$primarykey["name"]."` in (".implode(",",$rowids).")";
      //pre_print_r($query);exit;
      $sqlresult=$dbhandler->query($query);
    }

    return array("rowids"=>$rowids,"sqlresult"=>$sqlresult);
  }

  protected static function arrayToCSVRow($input, $delimiter = ';', $enclosure = '"') {
    $fp = fopen('php://temp', 'r+');
    fputcsv($fp, $input, $delimiter, $enclosure);
    rewind($fp);
    $data = fread($fp, 1048576); // [changed]
    fclose($fp);
    return  $data;
  }

  protected static function sqlresultToCsv($definition, $sqlresult, $fields, $encode = "UTF-8", $separator = ";", $enclosure = '"') {
      $_BOM_STRINGS=array();
      $_BOM_STRINGS["UTF-32BE"] = chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF);
      $_BOM_STRINGS["UTF-32LE"] = chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00);
      $_BOM_STRINGS["UTF-16BE"] = chr(0xFE) . chr(0xFF);
      $_BOM_STRINGS["UTF-16LE"] = chr(0xFF) . chr(0xFE);
      //$_BOM_STRINGS["UTF-8"]    = chr(0xEF) . chr(0xBB) . chr(0xBF);

      $output="";
      if(array_key_exists($encode,$_BOM_STRINGS)) $output.=$_BOM_STRINGS[$encode];

      //fejléc kiírása
      $header_row=array();
      for($i=0;$i<count($definition["fields"]);$i++){
         $field=$definition["fields"][$i];
				 if(in_array($field["name"],$fields))
         	 array_push($header_row,$field["label"]);
      }
      if($header_row){
        $rowstring=static::arrayToCSVRow($header_row, $separator,$enclosure);
        if($encode!="UTF-8") $rowstring=iconv("UTF-8",$encode,$rowstring);
        $output.=$rowstring;
      }

      // sorok kiírása
      while ($row = $sqlresult->fetch(PDO::FETCH_ASSOC)) {
        $data = array();
        for($i=0;$i<count($definition["fields"]);$i++){
           $field=$definition["fields"][$i];
					 if(in_array($field["name"],$fields)){
						 if(array_key_exists($field["name"]."_nomoefw_label",$row))
							 $record=$row[$field["name"]."_nomoefw_label"];
						 else
							 $record=$row[$field["name"]];

						 array_push($data,strip_tags($record));
					 }
        }

        $rowstring=static::arrayToCSVRow($data, $separator, $enclosure);
        if($encode!="UTF-8") $rowstring=iconv("UTF-8",$encode,$rowstring);
        $output.=$rowstring;
      }

      return $output;
    }

  public static $__ajax__export_whitelist=true;
  public static function export($params, $groupid=FALSE){
    $params=(array)$params;
    $definition=static::getDefinition(__FUNCTION__, $groupid);
    $req=array(
      "cmd" => "callStaticMethod",
      "className" => get_called_class(),
      "method" => "select",
      "params" => array(
        //"fields"=>$params["fields"],
        "filters"=>$params["filters"],
        "orderby"=>$params["orderby"],
        "resultType"=>"grid",
        "sqlResource"=>true,
        "numberOfRows"=>"*"
      )
    );
    $resp=call_user_func(array("NomoAPI",$req["cmd"]),$req);
    $result=$resp["data"];

    $filename = NomoUtils::normalizeString(get_called_class()).'_export_'.date("Y.m.d_H.m.s").'.csv';
    $filename = preg_replace( '/\s+/', '_', $filename );

    $fileContent=static::sqlresultToCsv($definition, $result["resource"], $params["fields"], "UTF-16LE", "\t");
    header("Content-Type: application/vnd.ms-excel");
    //header("Content-Type: text/plain") ;
    header('Content-Disposition: attachment; filename='.$filename);
    header('Content-Length: '.strlen($fileContent));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: private');
    header('Pragma: private');

    echo $fileContent;
    exit;
  }



  // fájl feltöltése nomo::$tempPath mappába
  public static $__ajax__importFileSave_whitelist=false;
  public static function importFileSave($params, $groupid = false){
    //ini_set('max_execution_time', 60);
    global $_FILES;
    $params=(array)$params;
    $tmpname=$_FILES["uploadfile"]['tmp_name'];
    $filename=$_FILES["uploadfile"]['name'];
    if($tmpname=='')
      throw new NomoException("Először válasszon ki egy fájlt!",1);

    $saved_filename=date("Ymdhis")."_".$params["company"]."_".mt_rand(1111111111111111,9999999999999999).".csv";
    $saved_filepath=nomo::$tempPath.$saved_filename;

    try{
      $savefile = fopen($tmpname, 'r');
      file_put_contents($saved_filepath, $savefile);
    } catch (Exception $e){
      throw new NomoException("Hiba a fájl mentésekor!",2);
    }

    $ret["filename"]=$saved_filename;
    $ret["uploadname"]=$filename;
    return $ret;
  }

  protected static function importRecord($params,$cvsrecord){
    $newrecord=array();
    $newrecord["record"]=array();
    if($params["additionalParams"]){
      foreach($params["additionalParams"] as $name=>$value){
        $newrecord["record"][$name]=$value;
      }
    }
    for($j=0;$j<count($cvsrecord);$j++){
      if($params["fields"][$j]!=null && $params["fields"][$j]!=""){
        $newrecord["record"][$params["fields"][$j]]=$cvsrecord[$j];
      }
    }
    // DB-be írás
    return call_user_func(array($params["typeid"],"create"),$newrecord);
  }

  // CSV fájl importálás
  // $params["final"]: végső importálás (DB-be írás)
  // $params["preview"]: előnézeti adatok
  // $params["filename"]: fájl neve (elérési útvonal nélkül)
  // $params["from"]: kezdősor
  // $params["count"]: hány sor legyen beolvasva(null=mind)
  // $params["encoding"]: kódolás (null=automata felismerés)
  // $params["delimiter"]: szeparátor karakter (null=automata felismerés)
  // $params["typeid"]: típus (final)
  // $params["fields"]: rekord mezők DB-be írásho (final)
  // $params["additionalParams"]: plusz rekord paraméterek DB-be íráshoz (final)
  public static $__ajax__importFile_whitelist=false;
  public static function importFile($params, $groupid = false){
    if($groupid === FALSE) $groupid=nomo::$session->groupid;
    $params=(array)$params;
    $ret=array();
    $from=$params["from"];
    if($from<=0 || $from==null || $from=="") $from=1;
    $count=$params["count"];

    // fájl ellenőrzése
    if($params["filepath"])
      $filename=$params["filepath"];
    else
      $filename=nomo::$tempPath.$params["filename"];

    if(dirname($filename).'/'!=nomo::$tempPath && $groupid != 1)
      throw new NomoException("Hibás fájl vagy elérési útvonal.",1);

    if(!file_exists($filename))
      throw new NomoException("Hibás elérési útvonal.",1);

    // ENCODING
    if(!$params["encoding"])
      $coding=self::detectEncoding($filename);
    else
      $coding=$params["encoding"];
    $encodedFileName=self::transcodeFile($filename, $coding);

    // DELIMITER
    if(!$params["delimiter"])
      $delimiter=self::detectDelimiter($encodedFileName);
    else
      $delimiter=$params["delimiter"];

    $encodedFile=fopen($encodedFileName, 'r');

    // végső import, DB-be írás
    if($params["final"]){
      // ellenőrzés, hogy van-e érvényes mezőnév hozzárendelve oszlophoz
      $valid=false;
      for($j=0; $j<count($params["fields"]); $j++){
        if($params["fields"][$j]!="")
          $valid=true;
      }
      if(!$valid)
        throw new NomoException("Hiba: Semelyik oszlophoz nem lett mező hozzárendelve.",2);

      $importcounter=0;

      // delimiter alapján szétbontás és DB-be írás
      $i=1;
      while(($cvsrecord=fgetcsv($encodedFile,0,$delimiter))!==FALSE){
          if($i>=$from){
            $result=static::importRecord($params,$cvsrecord);
            $importcounter++;
          }
          $i++;
      }

      fclose($encodedFile);
      $ret["imported"]=$importcounter;
    }

    // előnézet készítése
    if($params["preview"]){
      $columnnumber=0;
      $ret["data"]=array();
      // delimiter alapján szétbontás
      $i=1;
      while(($cvsrecord=fgetcsv($encodedFile,0,$delimiter))!==FALSE){
          if($count && $i>=$from+$count);
          else if($i>=$from){
            $newrecord=array();
            if($columnnumber<count($cvsrecord))
              $columnnumber=count($cvsrecord);

            for($j=0;$j<count($cvsrecord);$j++){
              array_push($newrecord, $cvsrecord[$j]);
            }
            array_push($ret["data"], $newrecord);
          }
          $i++;
      }

      fclose($encodedFile);
      $ret["coding"]=$coding;
      $ret["columnnumber"]=$columnnumber;
      $ret["delimiter"]=$delimiter;
      $ret["count_more"]=$i-$count-$from;
      if($ret["count_more"]<0)
        $ret["count_more"]=0;
    }

    return $ret;
  }

  // fájl kódolás megállapítása
  static function detectEncoding($_filename){
    $text = file_get_contents($_filename);
    $coding=null;

    define ('UTF32_BIG_ENDIAN_BOM'   , chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
    define ('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
    define ('UTF16_BIG_ENDIAN_BOM'   , chr(0xFE) . chr(0xFF));
    define ('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
    define ('UTF8_BOM'               , chr(0xEF) . chr(0xBB) . chr(0xBF));

    // UTF-16LE detektálása
    $first2 = substr($text, 0, 2);
    $first3 = substr($text, 0, 3);
    $first4 = substr($text, 0, 3);
    if ($first3 == UTF8_BOM) $coding='UTF-8';
    elseif ($first4 == UTF32_BIG_ENDIAN_BOM) $coding='UTF-32';
    elseif ($first4 == UTF32_LITTLE_ENDIAN_BOM) $coding='UTF-32';
    elseif ($first2 == UTF16_BIG_ENDIAN_BOM) $coding='UTF-16';
    elseif ($first2 == UTF16_LITTLE_ENDIAN_BOM) $coding='UTF-16';

    if($coding==null){
      $coding=mb_detect_encoding($text,"UTF-8, ISO-8859-2");
    }

    return $coding;
  }

  // adat szeparátor megállapítása
  static function detectDelimiter($_filename){
    $text = file_get_contents($_filename);
    $delimiter=null;

    $semicolon=substr_count($text,";");
    $comma=substr_count($text,",");
    $tab=substr_count($text,"\t");
    $max=max($semicolon,$comma,$tab);

    if($max==$comma) $delimiter=',';
    else if($max==$semicolon) $delimiter=';';
    else if($max==$tab) $delimiter="\t";

    return $delimiter;
  }

  // átkódolás UTF-8 -ra aktuális kódolás alapján
  static function transcodeFile($_filename, $coding){
    $encodedFileName=$_filename.'.enc';
    $encodedFile=fopen($encodedFileName, 'w+');
    if($encodedFile===FALSE)
      throw new NomoException("Nemsikerült a fájlt megnyitni: '$encodedFileName' !",1);

    $tempfile=fopen($_filename, 'r');

    if(!$coding)
      throw new NomoException("Ismeretlen kódolás!",1);

    while (!feof($tempfile)){
  		$originalContent = fread($tempfile, filesize($_filename));
  		$utf8Contnt = iconv($coding, "UTF-8//IGNORE", $originalContent);
  		fwrite($encodedFile, $utf8Contnt);
  	}
    fclose($tempfile);
    fclose($encodedFile);
    return $encodedFileName;
  }

}
?>
