<?php
include(__DIR__."/../lib/Clouddueling/Mysqldump/Mysqldump.php");
use Clouddueling\Mysqldump\Mysqldump;

class __NomoPatchDB{
	public static function check($redirect_to_patchdb){
	  set_time_limit ( 300 );

	  if(!isset($wwwPath))  $wwwPath=realpath(__DIR__."/../../");
	  $dbPatchesPath=$wwwPath."/dbscripts/patches";

	  setlocale(LC_ALL, 'en_US.UTF8');
	  date_default_timezone_set('Europe/Budapest');



	  $conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => MYSQL_PCONNECT));
	  $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	  $conn->exec("SET NAMES utf8 COLLATE utf8_hungarian_ci");
	  $conn->exec("SET SESSION sql_mode = ''");
	  //$conn->exec("DROP TABLE _dbpatchlist;");
	  $conn->exec("CREATE TABLE IF NOT EXISTS `_dbpatchlist` (
		`rowid` int(11) NOT NULL AUTO_INCREMENT,
		`filename` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
		`result` longtext COLLATE utf8_hungarian_ci NOT NULL,
		`executed_at` datetime NOT NULL,
		PRIMARY KEY (`rowid`)
	  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci AUTO_INCREMENT=1 ;");


	  //get executed db patches
	  try{
		$result=$conn->query("SELECT * FROM _dbpatchlist");
	  }catch(PDOException $e){
		throw new Exception($e->getMessage());
	  }

	  if($result===false){
		throw new Exception(PDO::errorInfo());
	  }elseif($result!==true){
		$executed=array();
		if($result->columnCount()>0){
		  $executed=$result->fetchAll(PDO::FETCH_ASSOC);
		}
		$result->closeCursor();
		$result=null;
	  }
	  $executed_patch_files=array();
	  for($i=0;$i<count($executed);$i++){
		array_push($executed_patch_files,$executed[$i]["filename"]);
	  }


	  //get list of db patch files
	  $patch_files=array();
	  if ($handle = opendir($dbPatchesPath)) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != ".." && preg_match ('/\.sql$/i',$entry)) {
				array_push($patch_files,$entry);
			}
		}
		closedir($handle);
	  }
	  sort($patch_files);


	  $hasNewPatch=false;
	  //execute patch files
	  for($i=0;$i<count($patch_files);$i++){
		$patch_file=$patch_files[$i];
		if(!in_array($patch_file,$executed_patch_files)){
		  if(!$hasNewPatch){
			$hasNewPatch=true;
			if(isset($redirect_to_patchdb) && $redirect_to_patchdb===true){
			  header('Location: /patchdb.php');exit;
			}
			header('Content-type: text/plain; charset=utf-8');
			if($_GET["skip_dump"]!=1){
			echo "create dump '".$dbPatchesPath.'/dumps/'.$patch_file.'.dump_before_patch.sql'."':\n";
			echo "--------------------------\n";
			$dump = new Mysqldump(DB_NAME, DB_USER, DB_PASS, DB_HOST, 'mysql' );
			$dump->start($dbPatchesPath.'/dumps/'.$patch_file.'.dump_before_patch.sql');
			}
		  }

		  echo "execute '".$patch_file."':\n";
		  echo "--------------------------\n";

		  $result=$conn->query("INSERT INTO _dbpatchlist(filename, result, executed_at) VALUES ('".$patch_file."','el lett inditva de nem futott vegig',NOW())");
		  $insert_id=$conn->lastInsertId();
		  $result->closeCursor();
		  $result=null;

		  $sqlpatch=file_get_contents($dbPatchesPath."/".$patch_file);
		  echo $sqlpatch;

		  try{
			$result=$conn->exec($sqlpatch);
			$result=null;
		  }catch(PDOException $e){
			$result=$conn->exec("UPDATE _dbpatchlist SET result='".addslashes($e->getMessage())."' WHERE rowid='".$insert_id."'");
			throw new Exception($e->getMessage());
		  }

		  $result=$conn->exec("UPDATE _dbpatchlist SET result='OK' WHERE rowid='".$insert_id."'");
		  $result=null;
		  echo "\n\n\n";
		}
	  }
	  $conn = null;

	  if(!isset($redirect_to_patchdb) && !$hasNewPatch){
		header('Content-type: text/html; charset=utf-8');
		echo '<a href="/">Belépés</a>';
	  }
	}

}
?>
