<?php
    class Adminmenu extends NomoDataSource{
        public static $menu_items=array(
             array(
                    "rowid"=>"kezdolap",
                    "name"=>"Felhasználók",
                    "outfilter"=>"/home/User",
                    "parent"=>null,
                    "nomocms_select_access_gid"=>"2047",
                    "device"=>"All",
                    "icon_css_class"=>"fa fa-file-o"
             )
        );


        public static $__ajax__select_whitelist=true;
        public static $__ajax__create_whitelist=false;
        public static $__ajax__update_whitelist=false;
        public static $__ajax__delete_whitelist=false;

        public static function select($params = array(),$groupid=FALSE){
            if($groupid === FALSE) $groupid=nomo::$session->groupid;
            $params["isMobileBrowser"]=!!$params["isMobileBrowser"];

            $menu_items=static::$menu_items;
            $result=array();
            for($i=0;$i<count($menu_items);$i++){
                $item=$menu_items[$i];
                $gid=(int)$item["nomocms_select_access_gid"];
                if(User::memberOf($gid)){
                    if($params["isMobileBrowser"] && ($item["device"]=="Mobile" || $item["device"]=="All" || $item["device"]=="")){
                        array_push($result,$item);
                    }else if(!$params["isMobileBrowser"] && ($item["device"]=="Desktop" || $item["device"]=="All" || $item["device"]=="")){
                        array_push($result,$item);
                    }

                }
            }
            return 	$result;
        }
    }
?>
