<?php

// Include local database configuration

/*

define("DB_USER","root");

define("DB_PASSWORD","");

define("DB_HOST","localhost");

define("DB_DATABASE","k92");





// Get the Framework

define('DS','/');

define('CORE_DIR','./MF/');

define('BASE_URL',preg_replace( '/index\.php/','',$_SERVER['SCRIPT_NAME']) );  

define('APP_DIR',getcwd().DS);

define('SESSION_ID','K9SESSION'); 

include (CORE_DIR.'start.php');



// if you need to do something before the controller gets underway

// you can put it here.







// kickoff controller to do its stuff

$C->start();



?>