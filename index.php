<?php

include_once('../config.php');


define('DS', '/');



if (APP_MODE == 'development') {

    // development

    //define('CORE_DIR','./MF/');

    define('CORE_DIR', './mf4/');

    define("DB_USER", "root");

    define("DB_PASSWORD", "");

    define("DB_HOST", "localhost");

    define("DB_DATABASE", "k9");



    define('SHOW_DEBUG', true);

    define('SHOW_ERRORS', 2); //1 lots 2 medium errors and 3 for serious errors




    // define('SESSION_ID', 'evoipoqt9');



} else if (APP_MODE == 'production') {

    //define('APP_MODE', 'production');//added as  used in catalog session handler

    define('CORE_DIR', './mf4/');

    define("DB_USER", "k9homes_dbuser");

    define("DB_PASSWORD", "Kh56D6en");

    define("DB_HOST", "localhost");

    define("DB_DATABASE", "k9homes_db");

    define('SHOW_DEBUG', false);

    define('SHOW_ERRORS', 4);



    // define('SESSION_ID', 'evor2irae'); /// production



} else {
    die('APP_MODE is not valid');
}



// Get the Framework

define('DEFAULT_CONTROLLER', 'home'); // defaults to main if not set



//define('BASE_URL',preg_replace( '/index\.php/','',$_SERVER['SCRIPT_NAME']) );

//define('APP_DIR',getcwd().DS);



require_once 'State.class.php';



include CORE_DIR . 'start.php';



include_once '../catalog/lib/session.php';



// if you need to do something before the controller gets underway

// you can put it here.



// kickoff controller to do its stuff

$C->start();
