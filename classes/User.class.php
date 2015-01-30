<?php

class User extends __User
{
    //public static $__ajax__select_whitelist=true;

    /*User Group Enums*/
    const SUPERADMIN = 1;
    const ADMIN = 2;
    const ACTIVE = 4;
    const TECHNIKUS = 8;
    const SOFOR = 16;
    const UGYKEZELO = 32;
    const VEZETO = 64;
    const KEZELEST_MEGTEKINTHETI = 128;
    const SZERVIZES = 256;
    const MUSZAKI_VEZETO = 512;
    const TULAJDONOS = 1024;

    public static $__ajax__select_whitelist = true;
    public static $__ajax__create_whitelist = array(1, 2, 32, 64);
    public static $__ajax__update_whitelist = array(1, 2, 32, 64);
    public static $__ajax__delete_whitelist = array(1, 2, 32, 64);

    public static $label = "(SELECT IF({{tableprefix}}titulus!='',CONCAT({{tableprefix}}titulus,' ',{{tableprefix}}name),{{tableprefix}}name))";


    public static function getDefinition($params = array(), $groupid = FALSE)
    {
        if ($groupid === FALSE) $groupid = nomo::$session->groupid;

        $definition = parent::getDefinition($params, $groupid, $definition);

        $field =& static::getFieldByName("felettes", $definition);
        $field["controltype"] = "select2ajax";
        $field["visible"] = array("default" => true, "form" => true, "grid" => true);
        $field["params"] = array(
            "type_id" => "User"
        );
        $field =& static::getFieldByName("avatar", $definition);
        $field["controltype"] = "file";
        $field["params"] = array("type_id" => "File", "multi" => false, "attributeAccept" => "image/*", "attributeCapture" => "camera");
        $field =& static::getFieldByName("nev", $definition);
        $field["controltype"] = "name";    //$field["addon"]="<i class='fa fa-user'> </i>";
        $field =& static::getFieldByName("brutto_ber", $definition);
        $field["mask"] = "099999999999999";
        $field["addon"] = "Ft";
        $field =& static::getFieldByName("brutto_premium", $definition);
        $field["mask"] = "099999999999999";
        $field["addon"] = "Ft";
        $field =& static::getFieldByName("eves_szabadsag", $definition);
        $field["addon"] = "nap";
        $field =& static::getFieldByName("superadmin", $definition);
        $field["visible"] = array("default" => false);
        $field =& static::getFieldByName("import_korhaz", $definition);
        $field["visible"] = array("default" => false);

        $field =& static::getFieldByName("kep", $definition);
        $field["controltype"] = "file";
        $field["params"] = array("type_id" => "File", "multi" => false);
        $field["visible"]["grid"] = false;

        if ($groupid != 1) {
            for ($i = 0; $i < count($definition["fields"]); $i++) {
                if (in_array($definition["fields"][$i]["name"], array("activate_code", "activate_date"))) {
                    array_splice($definition["fields"], $i, 1);
                    $i--;
                }
            }
        }


        $definition["orderby"] = array(
            "field" => "name",
            "direction" => 0
        );
        return $definition;
    }

    public static function select($params = array(), $groupid = FALSE)
    {
        if ($groupid === FALSE) $groupid = nomo::$session->groupid;
        if (!$params["filters"]) $params["filters"] = array();

        if ($groupid != 1) {
            array_push($params["filters"], array(
                "field" => "superadmin",
                "operator" => "!=",
                "value" => 1
            ));
        }

        $result = parent::select($params, $groupid);

        return $result;
    }


    public static $__ajax__get_avatar = true;
    public static function get_avatar($params = array(), $groupid = FALSE)
    {
        //TODO paraméterek alapján
        File::get($params);
    }



    private static function send_username_password($userid)
    {
        $rows = static::select(array(
            "filters" => array(
                array(
                    "field" => "rowid"
                , "value" => $userid
                )
            )
        ), 1);

        if (count($rows) != 1)
            throw new NomoException("Nincs ilyen felhasználó!", 11);
        $record = $rows[0];

        $newRecord = array(
            "activate_code" => NomoUtils::rndgen()
        );

        $templateData = $record;
        if (empty($record["password"])) {
            $newRecord["password"] = NomoUtils::rndgen(8);
            $templateData["password"] = $newRecord["password"];
        } else {
            $templateData["password"] = $record["password"];
        }

        $users = static::update(array(
            "record" => $newRecord,
            "filters" => array(
                array(
                    "field" => "rowid",
                    "value" => $record["rowid"]
                )
            )
        ), 1);

        $templateData["defaultDomain"] = DEFAULT_DOMAIN;
        require_once(nomo::$frameworkPath . "/lib/mustachephp/Mustache.php");
        $mustache = new Mustache;
        $htmlbody = $mustache->render(file_get_contents(nomo::$projectPath . "/emails/set_user_active.html"), $templateData);


        $sendparams = array(
            "from" => array(
                "mail" => DEFAULT_FROM_EMAIL,
                "name" => DEFAULT_FROM_NAME
            ),
            "to" => $params["record"]["email"],
        );
        $sendparams["to"] = $record["email"];
        $sendparams["subject"] = "[UVEK|VIR] Új regisztráció";
        $sendparams["body"] = $htmlbody;

        $ret = Email::send($sendparams);
        if (!$ret)
            throw new NomoException("Sikertelen email küldés: '" . $ret . "'!", 21);

        //throw new NomoException('kuldjuk',5,$record);
    }

    public static function create($params = array(), $groupid = FALSE)
    {
        //if(array_key_exists('email',$params["record"]) && $params["record"]['email']==NULL)  unset($params["record"]['email']);
        $result = parent::create($params, $groupid);
        if ($params["record"]["active"]) {
            static::send_username_password($result["sqlresult"]["insert_id"]);
        }
        return $result;
    }

    public static function update($params = array(), $groupid = FALSE)
    {
        //var_dump($params["record"]['email']);
        if (array_key_exists("active", $params["record"])) {
            $rows = static::select(array("filters" => $params["filters"]));
            if (count($rows) != 1) throw new NomoException('Érvénytelen kiválasztás', 5);
            $currentRecord = $rows[0];
        }
        //static::send_username_password($currentRecord["rowid"]);
        $result = parent::update($params, $groupid);
        if (array_key_exists("active", $params["record"])) {
            if ($params["record"]["active"] != $currentRecord["active"]) {
                if ($params["record"]["active"] && empty($currentRecord["activate_code"])) {
                    static::send_username_password($currentRecord["rowid"]);
                }
            }
        }

        return $result;
    }


}

?>
