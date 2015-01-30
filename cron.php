<?php
require_once(dirname(__FILE__)."/nomo.php");
if(nomo::$_GET["key"]==CRONKEY || isset($_SERVER["SHELL"])):
	$resultHTML=Cronjob::run();
  if(nomo::$_GET["autorefresh"]):

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta http-equiv="refresh" content="5" />
  <title>Poor man's cron</title>
  </head>
  <body>
    <?php echo $resultHTML;?>
  </body>
</html>
<?php

else:
		echo $resultHTML;
  endif;
endif;

?>
