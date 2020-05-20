<?php

if (defined('SHOW_ERRORS') && SHOW_ERRORS) {

    if (SHOW_ERRORS === 3) {
        error_reporting(E_ERROR | E_PARSE);
    } elseif (SHOW_ERRORS === 2) {
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
    } else {

        error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    }

} else {
    error_reporting(0);
}

// JSON support
if (!extension_loaded('json')) {
    include CORE_DIR . "JSON.php";
}

// We dont like magic_quotes so nullify if set

if (get_magic_quotes_gpc()) {
    function stripslashes_deep($value)
    {
        $value = is_array($value) ?
        array_map('stripslashes_deep', $value) :
        stripslashes($value);
        return $value;
    }

    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}
// FRamework defines

if (!defined('BASE_URL')) {
    define('BASE_URL', preg_replace('/index\.php/', '', $_SERVER['SCRIPT_NAME'])); //webroot
}

define('APP_DIR', getcwd() . DS);
define('CONTROLLER_DIR', APP_DIR . 'controllers' . DS);
define('MODEL_DIR', APP_DIR . 'models' . DS);
define('VIEW_DIR', APP_DIR . 'views' . DS);
define('VIEW_ELEMENT_DIR', APP_DIR . 'views' . DS . 'elements' . DS);
define('SNIPPET_DIR', APP_DIR . 'snippets' . DS);
define('USER_IMAGES_DIR', 'images' . DS); // for tinymce
define('USER_IMAGES_PATH', APP_DIR . USER_IMAGES_DIR); // Where user stores images and tinymce looks for them
define('SNIPPET_PATTERN', '/\[([\+\&\%\#])([^\]]*)?\]/'); // used by snippet expanders

define('SITE_LIB_DIR', APP_DIR . 'lib' . DS); // where custom files for the site can be stored

//EXPERIMENTAL
define('MODULES_DIR', 'modules' . DS);

if (!defined('DEFAULT_CONTROLLER')) {
    define('DEFAULT_CONTROLLER', 'main');
}
if (!file_exists(CONTROLLER_DIR . DEFAULT_CONTROLLER . '_controller.php')) {
    die('CANT FIND DEFAULT_CONTROLLER FILE ' . CONTROLLER_DIR . DEFAULT_CONTROLLER . '_controller.php ');
}

define('VALID_NOT_EMPTY', '/.+/');
/**
 * Numbers [0-9] only.
 */
define('VALID_NUMBER', '/^[0-9]+$/');

/**
 * Numbers greater NOT ZERO [0-9] only.
 */
define('VALID_NUMBER_NOT_ZERO', '/^[1-9][0-9]*$/');
/**
 * A valid email address.
 */
define('VALID_EMAIL', '/\\A(?:^([a-z0-9][a-z0-9_\\-\\.\\+]*)@([a-z0-9][a-z0-9\\.\\-]{0,63}\\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|[a-z]{2,4}))$)\\z/i');
/**
 * A valid year (1000-2999).
 */
define('VALID_YEAR', '/^[12][0-9]{3}$/');

/**\
 * @desc Valid password
 */
define('MIN_PASSWORD_LENGTH', 4);
define('VALID_PASSWORD', '/^[a-z0-9]{' . MIN_PASSWORD_LENGTH . ',}/');

define('LOCAL_INCLUDE_PATH', 'includes/');
set_include_path(get_include_path() . PATH_SEPARATOR . LOCAL_INCLUDE_PATH);

/**
 * @desc Get a local include libray if one exists
 */
$filepath = LOCAL_INCLUDE_PATH . 'local_app_includes.php';
if (file_exists($filepath)) {
    include_once $filepath;
}

include_once CORE_DIR . 'helper_functions.php';
include_once CORE_DIR . 'request_class.php';
include_once CORE_DIR . 'controller_class.php';
include_once CORE_DIR . 'model_class.php';
include_once CORE_DIR . 'db_class.php';

// added config class July 2010
include_once CORE_DIR . 'config_class.php';
$config = Config::singleton();
$config->__set('debug', false); // code can set to true to cause flashDebug to record debug data

$Routes['/badrequest/'] = '/' . DEFAULT_CONTROLLER . '/badrequest/';
//$Routes['/client/orderhistory/'] = '/' . 'client' . '/orderhistory/';

// Create App Object
$R = new Request($Routes);
$DB = new DB();

// Load and Create the Controller
if (file_exists(CONTROLLER_DIR . $R->controller . '_controller.php')) {
    include_once CONTROLLER_DIR . $R->controller . '_controller.php';
    $controllerName = $R->controller . 'Controller';
    $C = new $controllerName($R, $DB);
} else {
    echo " Controller file " . $R->controller . '_controller.php' . " does not exist";
    exit;
    flashError('Bad controller name:' . $R->controller);
    //header ("Location: ".url('badrequest/',false)."\r\n");
    include_once CONTROLLER_DIR . DEFAULT_CONTROLLER . '_controller.php';
    $controllerName = DEFAULT_CONTROLLER . 'Controller';

    $C = new $controllerName($R, $DB);
    $C->R->controller = DEFAULT_CONTROLLER;
    $C->action = 'badRequest';

}

// Output has been rendered so all that's left is to
// clean up. Close sessions and database connections etc ...
