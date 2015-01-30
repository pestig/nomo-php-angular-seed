<?php 
require_once(nomo::$frameworkPath."/lib/inputFilter/class.inputfilter.php5");
class __NomoUtils{
	protected static $textfilter=null;
	protected static $htmlfilter=null;

	public static function textFilter($str){
		if(!static::$textfilter)
			static::$textfilter = new InputFilter(null,null,0,0);//kiszedi a html cuccost, plain textet ad vissza
		return static::$textfilter->process($str);
	}

	public static function htmlFilter($str){
		if(!static::$htmlfilter)
			static::$htmlfilter = new InputFilter(null,null,1,1);//kiszedi az xss támadásokat htmlt ad vissza
		return static::$htmlfilter->process($str);
	}

	public static function getJSIncludeHtml($path) {
		$html="";
		$htmlModules="";
		$ite=new RecursiveDirectoryIterator($path);
		$Iterator = new RecursiveIteratorIterator($ite);
		$Regex = new RegexIterator($Iterator, '/^.+js$/i', RecursiveRegexIterator::GET_MATCH);
		foreach ($Regex as $filename=>$cur) {
				//azért kell replace, mert windows-on a könyvtárseparator \, az easyphp apache-nak viszont / kell
				$local="".str_replace(DIRECTORY_SEPARATOR,'/',end(explode('..',$filename)));
			  if(basename($local, ".js")=="module"){
				  $htmlModules.="\t\t<script src=\"".$local."?ver=".VERSION."\"></script>\n";
				}else{
					$html.="\t\t<script src=\"".$local."?ver=".VERSION."\"></script>\n";
				}
		}

		$result="<!-- JS_INCLUDE_HTML -->\n";
		$result.=$htmlModules.$html;
		$result.="\t<!-- JS_INCLUDE_HTML -->\n";

		return $result;
	}

    public static function get_gravatar( $email, $s = 88, $d = 'retro', $r = 'g', $img = false, $atts = array() ) {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&d=$d&r=$r";
        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }

    public static function cutstr($str,$max_length=100){
      $str=($str)?(string)$str:"";
      if (strlen($str) > $max_length){
        $offset = ($max_length - 3) - strlen($str);
        $str = substr($str, 0, strrpos($str, ' ', $offset)) . '...';
      }
      return $str;
    }
  
    public static function rndgen($nod=64,$mode='normal'){ 
      $totalChar = $nod; // number of chars in the password
      $salt = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ123456789";  // salt to select chars from
      if($mode=='numbers'){
        $salt = "0123456789";  // salt to select chars from
      } else if($mode=='characters'){
        $salt = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ";  // salt to select chars from
      }  
      srand((double)microtime()*1000000); // start the random generator
      $password=""; // set the inital variable
      for ($i=0;$i<$totalChar;$i++)  // loop and create password
        $password = $password . substr ($salt, rand() % strlen($salt), 1);
      return $password;
    }
            
    public static function getPagerData($numberOfPages, $currentPageIndex = 1, $rowsPerPage = 10, $pagePerView = 5){
      $mustacheData=array();
      //process
      //legelső oldal
      $firstpage=1;
      if ($currentPageIndex > 1) {
        $mustacheData["firstpage"]["value"] = $firstpage;
        $mustacheData["firstpage"]["visible"] = 1;
      } else {
        $mustacheData["firstpage"]["value"] = $firstpage;
        $mustacheData["firstpage"]["visible"] = 0;
      }
      //előző oldal
      if ($currentPageIndex > 1 && $currentPageIndex <= $numberOfPages) {
        $mustacheData["backpage"]["value"] = $currentPageIndex-1;
        $mustacheData["backpage"]["visible"] = 1;
      } else {
        $mustacheData["backpage"]["value"] = $currentPageIndex-1;
        $mustacheData["backpage"]["visible"] = 0;
      }
      //következő oldal
      if ($currentPageIndex < $numberOfPages && $currentPageIndex >= 1) {
        $mustacheData["nextpage"]["value"] = $currentPageIndex+1;
        $mustacheData["nextpage"]["visible"] = 1;
      } else {
        $mustacheData["nextpage"]["value"] = $currentPageIndex+1;
        $mustacheData["nextpage"]["visible"] = 0;
      }
      //legutolsó oldal
      $lastpage=$numberOfPages;
      if ($numberOfPages != 1 && $currentPageIndex < $lastpage) {
        $mustacheData["lastpage"]["value"] = $lastpage;
        $mustacheData["lastpage"]["visible"] = 1;
      } else {
        $mustacheData["lastpage"]["value"] = $lastpage;
        $mustacheData["lastpage"]["visible"] = 0;
      }

    $pagePerView = 5;  //ennyi oldalszám lesz kint egyszerre 
    $pagenumbers2 = round($pagePerView / 2); //ennyi oldalszám legyen az aktuális előtt és után
    $pageoffset = ($currentPageIndex > $pagenumbers2) ? $currentPageIndex - $pagenumbers2 : 0; //átugrott oldalszámok száma
    $pageend = $pageoffset+$pagePerView; //az utolsó oldalszám
     

    if ($numberOfPages - $pagenumbers2 < $currentPageIndex)
    {
            $pageoffset = $numberOfPages - $pagePerView;
            if ($pageoffset <= 0)
            {
                    $pageoffset = 0;
            }
            $pageend = $numberOfPages;
    }

    $j=0;
    if ($numberOfPages > 1){
    for ($i=1+$pageoffset; $i <= $pageend and $i <= $numberOfPages; $i++)
    {
        $mustacheData["linkek"][$j]["value"] = $i;
        if ($currentPageIndex == $i) {
          $mustacheData["linkek"][$j]["label"] = 0;
        } else {
          $mustacheData["linkek"][$j]["label"] = 1;
        }
        $j++;
    }
    } else {

    }
      return $mustacheData;
    }
        
    public static function html2text($html){
      $tags = array (
      0 => '~<h[123][^>]+>~si',
      1 => '~<h[456][^>]+>~si',
      2 => '~<table[^>]+>~si',
      3 => '~<tr[^>]+>~si',
      4 => '~<li[^>]+>~si',
      5 => '~<br[^>]+>~si',
      6 => '~<p[^>]+>~si',
      7 => '~<div[^>]+>~si',
      );
      $html = preg_replace($tags,"\n",$html);
      $html = preg_replace('~</t(d|h)>\s*<t(d|h)[^>]+>~si',' - ',$html);
      $html = preg_replace('~<[^>]+>~s','',$html);
      // reducing spaces
      $html = preg_replace('~ +~s',' ',$html);
      $html = preg_replace('~^\s+~m','',$html);
      $html = preg_replace('~\s+$~m','',$html);
      // reducing newlines
      $html = preg_replace('~\n+~s',"\n",$html);
      return $html;
    }
    
    public static function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
    
        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        //$bytes /= (1 << (10 * $pow)); 
    
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 
    
    public static function getMimeTypeByExtension($extension, $default = "application/octet-stream"){
      $ext= strtolower($extension);
      
      if(array_key_exists($ext,static::$mimiTypes))
        $mimeType=NomoUtils::$mimiTypes[$ext];
      else
        $mimeType=$default;
        
      return $mimeType;
    }
    
    public static function getMimeTypeByFilename($filename, $default = "application/octet-stream"){
      $path_info = pathinfo($filename);
      $ext= strtolower($path_info['extension']);
      
      if(array_key_exists($ext,static::$mimiTypes))
        $mimeType=NomoUtils::$mimiTypes[$ext];
      else
        $mimeType=$default;
        
      return $mimeType;
    }
    
    public static function normalizeString($str) {
    	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);			    	
      $clean = preg_replace("/['\"]/",'', $clean);
			$clean = strtolower(trim($clean, '_'));
      $clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '_', $clean);
    	$clean = preg_replace("/[\/_| -]+/", '_', $clean);    
    	return $clean;
    }
    
    public static $mimiTypes=array(
         "ez" => "application/andrew-inset",
         "hqx" => "application/mac-binhex40",
         "cpt" => "application/mac-compactpro",
         "doc" => "application/msword",
         "bin" => "application/octet-stream",
         "dms" => "application/octet-stream",
         "lha" => "application/octet-stream",
         "lzh" => "application/octet-stream",
         "exe" => "application/octet-stream",
         "class" => "application/octet-stream",
         "so" => "application/octet-stream",
         "dll" => "application/octet-stream",
         "oda" => "application/oda",
         "pdf" => "application/pdf",
         "ai" => "application/postscript",
         "eps" => "application/postscript",
         "ps" => "application/postscript",
         "smi" => "application/smil",
         "smil" => "application/smil",
         "wbxml" => "application/vnd.wap.wbxml",
         "wmlc" => "application/vnd.wap.wmlc",
         "wmlsc" => "application/vnd.wap.wmlscriptc",
         "bcpio" => "application/x-bcpio",
         "vcd" => "application/x-cdlink",
         "pgn" => "application/x-chess-pgn",
         "cpio" => "application/x-cpio",
         "csh" => "application/x-csh",
         "dcr" => "application/x-director",
         "dir" => "application/x-director",
         "dxr" => "application/x-director",
         "dvi" => "application/x-dvi",
         "spl" => "application/x-futuresplash",
         "gtar" => "application/x-gtar",
         "hdf" => "application/x-hdf",
         "js" => "application/x-javascript",
         "skp" => "application/x-koan",
         "skd" => "application/x-koan",
         "skt" => "application/x-koan",
         "skm" => "application/x-koan",
         "latex" => "application/x-latex",
         "nc" => "application/x-netcdf",
         "cdf" => "application/x-netcdf",
         "sh" => "application/x-sh",
         "shar" => "application/x-shar",
         "swf" => "application/x-shockwave-flash",
         "sit" => "application/x-stuffit",
         "sv4cpio" => "application/x-sv4cpio",
         "sv4crc" => "application/x-sv4crc",
         "tar" => "application/x-tar",
         "tcl" => "application/x-tcl",
         "tex" => "application/x-tex",
         "texinfo" => "application/x-texinfo",
         "texi" => "application/x-texinfo",
         "t" => "application/x-troff",
         "tr" => "application/x-troff",
         "roff" => "application/x-troff",
         "man" => "application/x-troff-man",
         "me" => "application/x-troff-me",
         "ms" => "application/x-troff-ms",
         "ustar" => "application/x-ustar",
         "src" => "application/x-wais-source",
         "xhtml" => "application/xhtml+xml",
         "xht" => "application/xhtml+xml",
         "zip" => "application/zip",
         "au" => "audio/basic",
         "snd" => "audio/basic",
         "mid" => "audio/midi",
         "midi" => "audio/midi",
         "kar" => "audio/midi",
         "mpga" => "audio/mpeg",
         "mp2" => "audio/mpeg",
         "mp3" => "audio/mpeg",
         "aif" => "audio/x-aiff",
         "aiff" => "audio/x-aiff",
         "aifc" => "audio/x-aiff",
         "m3u" => "audio/x-mpegurl",
         "ram" => "audio/x-pn-realaudio",
         "rm" => "audio/x-pn-realaudio",
         "rpm" => "audio/x-pn-realaudio-plugin",
         "ra" => "audio/x-realaudio",
         "wav" => "audio/x-wav",
         "pdb" => "chemical/x-pdb",
         "xyz" => "chemical/x-xyz",
         "bmp" => "image/bmp",
         "gif" => "image/gif",
         "ief" => "image/ief",
         "jpeg" => "image/jpeg",
         "jpg" => "image/jpeg",
         "jpe" => "image/jpeg",
         "png" => "image/png",
         "tiff" => "image/tiff",
         "tif" => "image/tif",
         "djvu" => "image/vnd.djvu",
         "djv" => "image/vnd.djvu",
         "wbmp" => "image/vnd.wap.wbmp",
         "ras" => "image/x-cmu-raster",
         "pnm" => "image/x-portable-anymap",
         "pbm" => "image/x-portable-bitmap",
         "pgm" => "image/x-portable-graymap",
         "ppm" => "image/x-portable-pixmap",
         "rgb" => "image/x-rgb",
         "xbm" => "image/x-xbitmap",
         "xpm" => "image/x-xpixmap",
         "xwd" => "image/x-windowdump",
         "igs" => "model/iges",
         "iges" => "model/iges",
         "msh" => "model/mesh",
         "mesh" => "model/mesh",
         "silo" => "model/mesh",
         "wrl" => "model/vrml",
         "vrml" => "model/vrml",
         "css" => "text/css",
         "html" => "text/html",
         "htm" => "text/html",
         "asc" => "text/plain",
         "txt" => "text/plain",
         "rtx" => "text/richtext",
         "rtf" => "text/rtf",
         "sgml" => "text/sgml",
         "sgm" => "text/sgml",
         "tsv" => "text/tab-seperated-values",
         "wml" => "text/vnd.wap.wml",
         "wmls" => "text/vnd.wap.wmlscript",
         "etx" => "text/x-setext",
         "xml" => "text/xml",
         "xsl" => "text/xml",
         "mpeg" => "video/mpeg",
         "mpg" => "video/mpeg",
         "mpe" => "video/mpeg",
         "qt" => "video/quicktime",
         "mov" => "video/quicktime",
         "mxu" => "video/vnd.mpegurl",
         "avi" => "video/x-msvideo",
         "movie" => "video/x-sgi-movie",
         "ice" => "x-conference-xcooltalk"
    ); 
  }
?>
