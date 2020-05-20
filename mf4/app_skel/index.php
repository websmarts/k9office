<?php
// Include local database configs
define("DB_USER","root");
define("DB_PASSWORD","");
define("DB_HOST","localhost");
define("DB_DATABASE","ftpmanager");

// Get the Framework
define('CORE_DIR','c:/apache/MF/');
// URl to App
define('BASE_URL',preg_replace( '/index\.php/','',$_SERVER['SCRIPT_NAME']) );  
define('APPDIR','c:/apache/htdocs/ftpmanager/');
include (CORE_DIR.'start.php');

// if you need to do something before the controller gets underway
// you can put it here.


// kickoff controller to do its stuff
$C->start();

?>