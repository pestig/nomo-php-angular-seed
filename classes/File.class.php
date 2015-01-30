<?php
  class File extends NomoDataSource{
    //public static $__ajax__select_whitelist=true;
    public static $__ajax__create_whitelist=true;
    public static $__ajax__update_whitelist=array(1,2);
    public static $__ajax__delete_whitelist=array(1,2);
    public static $__ajax__get_whitelist=true;
    public static $__ajax__select_whitelist=true;

	public static function create($params = array(),$groupid = FALSE){
      //var_dump($params);
      //var_dump($_FILES);
      $fileid=$params["definition"]["name"];

      $params["record"]["nev"]=self::sanitize_file_name($_FILES[$fileid]["name"]);
      $params["record"]["meret"]=$_FILES[$fileid]["size"];
      $params["record"]["mime"]=$_FILES[$fileid]["type"];



      $result=parent::create($params,$groupid);
      $resultid=$result["sqlresult"]["insert_id"];
      $path=self::get_path_by_type_and_rowid($params["definition"]["params"]["type_id"],$result["sqlresult"]["insert_id"]);

      if (!@mkdir($path, 0777, true)) {
          throw new NomoException("Nem sikerült elmenteni a file-t, a mappa nem hozható létre",11);
      }

      $move_result=move_uploaded_file($_FILES[$fileid]["tmp_name"],$path.DIRECTORY_SEPARATOR.self::sanitize_file_name($_FILES[$fileid]["name"]));
      if($move_result==false){
        throw new NomoException("Nem sikerült elmenteni a file-t, a fájl-t nem lehet átmozgatni a végleges helyére",11); 
      }
      return $result;

    }
    public static function delete($params = array(),$groupid = FALSE){
      // select by params
      // for loop to delete folders
      // return parent::delete()
      return parent::delete($params);
    }
    public static function update($params = array(),$groupid = FALSE){
      //pre_print_r($params);
      $fileid=$params["definition"]["name"];
      $params["record"]=array(
          "nev"=>self::sanitize_file_name($_FILES[$fileid]["name"]),
          "meret"=>$_FILES[$fileid]["size"],
          "mime"=>$_FILES[$fileid]["type"]
      );

      $result=parent::update($params,$groupid);
      $path=self::get_path_by_type_and_rowid($params["definition"]["params"]["type_id"],$result["rowids"][0]);

      //töröljük a mappát
      self::delete_directory($path);

      if (!@mkdir($path, 0777, true)) {
          throw new NomoException("Nem sikerült elmenteni a file-t, a mappa nem hozható létre",11);
      }
      $move_result=move_uploaded_file($_FILES[$fileid]["tmp_name"],$path.DIRECTORY_SEPARATOR.self::sanitize_file_name($_FILES[$fileid]["name"]));
      return $result;
    }
    
    public static function getServerPath($params,$groupid= FALSE){

      $result = self::select(array(
        "filters"=>array(
          array(
            "field"=>"rowid",
            "value"=>$params["rowid"]
          )
        )
      ),$groupid);

      $result=$result[0];
      $path=self::get_path_by_type_and_rowid('File',$result["rowid"]);
      $file=$path.DIRECTORY_SEPARATOR.$result['nev'];
      return $file;
    }
    
    public static function get($params,$groupid= FALSE){

      $result = self::select(array(
        "filters"=>array(
          array(
            "field"=>"rowid",
            "value"=>$params["rowid"]
          )
        )
      ),$groupid);

      if(!$params["notfound"])
          $params["notfound"]="default";

      $result=$result[0];
      $path=self::get_path_by_type_and_rowid('File',$result["rowid"]);
      $file=$path.DIRECTORY_SEPARATOR.$result['nev'];

      if($params["size"]){
        //thumbnail
        $thumbsize=explode('x',$params["size"]);
        self::flush_thumb($file,(int)$thumbsize[0],(int)$thumbsize[1],$result['nev'],$params["notfound"]);
        exit;
      } else {

        header('Content-Description: File Transfer');
        header('Content-Type: '.$result['mime']);
        header('Content-Disposition: attachment; filename='.$result['nev']);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $result['meret']);

        ob_clean();
        flush();
        readfile($file);
        exit;
      }

    }
    public static function flush_thumb($file, $max_width, $max_height, $filename, $notfound = "default"){
      if(!$file || !file_exists($file)){
        if(!$max_width && $max_height){
          $max_width=$max_height;
        } else if(!$max_height && $max_width){
          $max_height=$max_width;
        } else if(!$max_width && !$max_height){
          $max_height=120;
          $max_width=120;
        }
        if($notfound=="avatar")
          $file=__DIR__.'/../nomoEFW/app/modules/common/img/no-avatar.jpg';
        else
          $file=__DIR__.'/../nomoEFW/app/modules/common/img/notfound.jpg';
      }
       // throw new NomoException("Nem sikerült thumbnail-t készíteni a képből");

      list($width_orig, $height_orig, $img_type) = getimagesize($file);

      if ($img_type==IMAGETYPE_GIF)
        $image = imagecreatefromgif($file);
      else if ($img_type==IMAGETYPE_JPEG)
        $image = imagecreatefromjpeg($file);
      else if ($img_type==IMAGETYPE_PNG)
        $image = imagecreatefrompng($file);
      else
          throw new NomoException("Érvénytelen képformátum.");

      $width  = $max_width;
      $height = $max_height;

      $ratio_orig = $width_orig/$height_orig;
      if ($width==0 || $width/$height > $ratio_orig) {
         $width = floor($height*$ratio_orig);
      } else {
         $height = floor($width/$ratio_orig);
      }

      // Resample
      $tempimg = imagecreatetruecolor($width, $height);
      imagecopyresampled($tempimg, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

      header('Content-Type: image/jpg');
      header('Content-Disposition: inline; filename="'.basename($file).'"');

      imagejpeg($tempimg, $thumbname, 80);
      return;
    }

    public static function delete_directory($dir){
      if(!preg_match('/d\d+/i',$dir))
        throw new NomoException("A könyvtár nem törölhető: ".$dir,11);
      $files = array_diff(scandir($dir), array('.','..'));
      foreach ($files as $file) {
        unlink("$dir/$file");
      }
      return rmdir($dir);
    }
    public static function get_path_by_type_and_rowid($type,$rowid) {
      $storage_path = defined(STORAGE_PATH)?STORAGE_PATH:nomo::$wwwPath.'storage';
      $d=ceil(log10($rowid));
      $decimal_subdir='d'.$d;
      $decimal_dirstructure=implode(DIRECTORY_SEPARATOR ,str_split($rowid));
      return $storage_path.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$decimal_subdir.DIRECTORY_SEPARATOR.$decimal_dirstructure;
    }
    public static function get_filepath_by_type_and_rowid($type,$rowid) {

    }
    public static function sanitize_file_name($string, $force_lowercase = true, $anal = false) {
      $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                     "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                     "â€”", "â€“", ",", "<", ">", "/", "?");
      $clean = trim(str_replace($strip, "", strip_tags($string)));
      $clean = preg_replace('/\s+/', "-", $clean);
      $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
      return ($force_lowercase) ?
          (function_exists('mb_strtolower')) ?
              mb_strtolower($clean, 'UTF-8') :
              strtolower($clean) :
          $clean;
    }



  }
?>
