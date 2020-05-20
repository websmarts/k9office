<?php
 
 // if PHP <5.2
if (!extension_loaded('json')){
	include(CORE_DIR."JSON.php");
}


include_once(CORE_DIR.'helper_functions.php');
include_once(CORE_DIR.'request_class.php');
include_once(CORE_DIR.'controller_class.php');
include_once(CORE_DIR.'model_class.php');
include_once(CORE_DIR.'db_class.php'); 
//include_once(CORE_DIR.'template_class.php');
//include_once(CORE_DIR.'config_class.php');
include_once(CORE_DIR.'config.php');

// Create App Object
$R = New Request();
$DB = new DB();

if ($R->ajax) {
	//Ajax error handler
 	set_error_handler('ajaxErrorHandler');
}




// Load and Create the Controller
if (file_exists(CONTROLLER_DIR.$R->controller.'_controller.php')) {
    include_once(CONTROLLER_DIR.$R->controller.'_controller.php');
    $controllerName = $R->controller.'Controller';
    $C = new $controllerName($R,$DB);
} else {
   echo " Controller file ".$R->controller.'_controller.php'." does not exist" ;
   exit;
}


// Output has been rendered so all that's left is to
// clean up. Close sessions and database connections etc ...

?>