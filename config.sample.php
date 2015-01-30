<?php
  ini_set('display_errors',1);
  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
  setlocale(LC_ALL, 'en_US.UTF8');
  date_default_timezone_set('Europe/Budapest');
  
  $gitftplog_path=__DIR__."/.git-ftp.log";
  if (file_exists($gitftplog_path)){
    $version=trim(file_get_contents($gitftplog_path));
  }else{
    $version="debug_".date("Ymd");//.rand();
  }
  define("VERSION", $version);

  define("DEFAULT_PROTOCOL", "http");
  define("DEFAULT_DOMAIN", "localhost");
  define("DB_HOST", "localhost");
  define("DB_USER", "root");
  define("DB_PASS", "p");
  define("DB_NAME", "dbdev");

  define("HTTP_AUTH", false);
  define("HTTP_AUTH_USER", "admin");
  define("HTTP_AUTH_PASS", "passs");

  define("SMTP_AUTH", true);
  define("SMTP_HOST", "host");
  define("SMTP_SECURE", "ssl"); //optional
  define("SMTP_PORT", "25");
  define("SMTP_USER", "user");
  define("SMTP_PASS", "pass");

  define("DEFAULT_FROM_EMAIL", "info@nomo.hu");
  define("DEFAULT_FROM_NAME", "NOMO");

  define("NOMO_USER_AUTH_SECRET_KEY", "@");

  define("SYSTEM_BCC_EMAIL", "example@example.com");

  define("AUTO_PATCH_DB", true);

  define("DOMPDF_TEMP_DIR", __DIR__."/tmp");

  define("DOMPDF_ENABLE_REMOTE", true);
  
  define("CRONKEY", "a");
?>
