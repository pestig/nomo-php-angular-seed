<?php
    define("DB_HOST", "localhost");
    define("DB_USER", "root");
    define("DB_PASS", "p");
    define("DB_NAME", "dbdev");
    require_once(__DIR__."/nomoEFW/nomo.php");
    include(nomo::$frameworkPath."/api/index.php");
?>
