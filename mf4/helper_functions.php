<?php
if(!function_exists('split')){
    function split($delim =',',$str) 
    {
        return explode($delim, $str);
    }
}

/**
 * @desc Logging function
 */

function logger($message, $level)

{

    if (
        defined(LOGGING_LEVEL) &&

        LOGGING_LEVEL &&

        defined(LOG_FILENAME) &&

        file_exists(LOG_FILENAME) &&

        is_writeable(LOG_FILENAME)
    ) {

        $fh = fopen(LOG_FILENAME, "a");

        fwrite($fh, $message);

        fclose($fh);
    }
}

/**

 * @desc Handles local requestActions

 */

function requestAction($requestURI)

{

    // request uri in form controlller/action/params

    preg_match('/([^\/]+)?\/([^\?]+)?(.*)/', $requestURI, $m);

    //flashDebug($m);

    $controller = $m[1];

    $action = rtrim($m[2], '/'); // remove traling slash if there

    $params = urldecode(ltrim($m[3], '?'));



    // Load and Create the Controller

    $controllerPath = CONTROLLER_DIR . $controller . '_controller.php';

    if (!file_exists($controllerPath)) {

        flashError(" Controller file " . $controllerPath . " does not exist");

        return false;
    }

    include_once $controllerPath;

    $controllerName = $controller . 'Controller';

    $R = new Request(''); // dont pass ROUTES for request actions

    $DB = new DB();

    $R->action = $action; // override the request action

    $R->controller = $controller; // override request controller

    // get any local params

    $data = array();

    if (!empty($params)) {

        parse_str($params, $data);
    }

    $R->getData = $data; // set params for controller to access

    $C = new $controllerName($R, $DB);



    $C->beforeAction();

    $C->page = $action;

    if (method_exists($C, $action)) {

        // AND USER IS AUTH TO ACCESS

        $C->$action();

        return $C->renderAction();
    } else {

        flashError('requestAction Method ' . $action . '() is not valid for ' . $controllerName);
    }
}



/**

 * @desc returns the sql limit string for a named pager

 */

function getPagerLimit($name)

{

    if (isset($_SESSION['pagers'][$name])) {

        $offset = ($_SESSION['pagers'][$name]['page'] - 1) * $_SESSION['pagers'][$name]['rowsPerPage'];

        $offset = $offset < 1 ? 0 : $offset;

        return " limit $offset , " . $_SESSION['pagers'][$name]['rowsPerPage'];
    }
}

function createPager($name, $countQuery, $rowsPerPage = PAGER_DEFAULT_ROWSPERPAGE)

{

    $_SESSION['pagers'][$name]['rowsPerPage'] = $rowsPerPage;

    $_SESSION['pagers'][$name]['countQuery'] = $countQuery;

    $_SESSION['pagers'][$name]['page'] = 0;

    updatePagerCount($name);
}

function updatePagerCount($name)

{

    global $DB;



    if ($query = $DB->query($_SESSION['pagers'][$name]['countQuery'])) {

        $row = mysql_fetch_row($query);

        $numRows = $row[0];
    } else {

        $numRows = 0;
    }

    $_SESSION['pagers'][$name]['numRows'] = $numRows;

    return $numRows;
}

function showPager($name)

{

    return $_SESSION['pagers'][$name]['numRows'] > $_SESSION['pagers'][$name]['rowsPerPage'];
}

function expandSnippet($s)

{

    global $DB, $C;

    $content = "";

    if ($s[1] == '%') {

        // & = include  snippett file



        // get filename and any params



        preg_match('/([^\?]+)?(.*)/', $s[2], $m);



        $snippetFileName = $m[1];

        $params = urldecode(ltrim($m[2], '?'));

        $data = array();

        if (!empty($params)) {

            parse_str($params, $data);
        }

        flashDebug($data);

        $snippetFile = SNIPPET_DIR . $snippetFileName . ".php";



        if (file_exists($snippetFile)) {

            ob_start(); // Start output buffering

            // Include the file

            include $snippetFile;

            $content = ob_get_contents(); // Get the contents of the buffer

            ob_end_clean(); // End buffering and discard



        }
    } elseif ($s[1] == '+') {

        // get info from database

        $sql = "select content from snippets where name=" . quote($s[2]);

        if ($row = $DB->fetchRow($sql)) {

            $content = $row['content'];
        } else {

            return '[-- failed atabase lookup --: ' . $s[2] . ']';
        }



        // eval if [% used ]

        if ($s[1] == '%') {

            eval("\$content = $content;");
        }
    } elseif ($s[1] == '#' || $s[1] == '&') {

        // check if function call

        if (preg_match('/^([^\(]+)?\(([^\)]*)?\)/', $s[2], $m)) {

            if (!function_exists($m[1])) {

                $content = '[--function error--: ' . $m[1] . '(' . $m[2] . ')]'; // error so just show snippet code

            } else {

                eval("\$content = " . $s[2] . ";");
            }
        } else {

            eval("\$content = " . $s[2] . ";");
        }
    }

    return preg_replace_callback(SNIPPET_PATTERN, 'expandSnippet', html_entity_decode($content, ENT_QUOTES));
}



if (!function_exists('json_encode')) {

    function json_encode($data)

    {

        $json = new Services_JSON();

        return ($json->encode($data));
    }
}



// Future-friendly json_decode

if (!function_exists('json_decode')) {

    function json_decode($data)

    {

        $json = new Services_JSON();

        return ($json->decode($data));
    }
}



/**

 * @desc checks if user is allowed to edit site content

 */

function isSiteEditor()

{

    if (isset($_SESSION['user'])) {

        return true;
    } else {

        return false;
    }
}



function isUserRole($role = '')

{

    if (!empty($role) && isset($_SESSION['user']) && preg_match("/$role/", $_SESSION['user']['roles'])) {

        return true;
    } else {

        return false;
    }
}

function isUser()

{

    return isset($_SESSION['user']);
}

function getUser($key = '')

{

    if (!empty($key) && isset($_SESSION['user'][$key])) {

        return $_SESSION['user'][$key];
    }
}

function killUser()

{

    unset($_SESSION['user']);
}



function pr($a, $print = true)

{



    $html = "<pre>";

    $html .= print_r($a, true);

    $html .= "</pre>";



    if ($print) {

        echo $html;
    } else {

        return $html;
    }
}



function flashMessage($msg)

{

    if (isset($_SESSION['flash_message']) && !empty($_SESSION['flash_message'])) {

        $_SESSION['flash_message'] .= "<br /> " . $msg;
    } else {

        $_SESSION['flash_message'] = $msg;
    }
}

function flashError($msg)

{

    if (isset($_SESSION['flash_error']) && !empty($_SESSION['flash_error'])) {

        $_SESSION['flash_error'] .= "<br /> " . $msg;
    } else {

        $_SESSION['flash_error'] = $msg;
    }
}



function flashDebug($data)

{



    if (!SHOW_DEBUG) {

        unset($_SESSION['flash_debug']);

        return;
    }



    if (isset($_SESSION['flash_debug']) && !empty($_SESSION['flash_debug'])) {

        $_SESSION['flash_debug'] .= pr($data, false);
    } else {



        $_SESSION['flash_debug'] = pr($data, false);
    }
}



/**

 * @desc Error handler for ajax calls

 */

function ajaxErrorHandler($errno, $errstr, $errfile, $errline)

{

    switch ($errno) {

        case E_USER_ERROR:

            echo '{"replyCode":611,"replyText":"User Error: ', addslashes($errstr) . '","errno":', $errno;

            break;

        case E_USER_WARNING:

            echo '{"replyCode":612,"replyText":"User Warning: ', addslashes($errstr) . '","errno":', $errno;

            break;

        case E_USER_NOTICE:

        case E_NOTICE:

            return false;

        default:

            echo '{"replyCode":610,"replyText":"', addslashes($errstr) . '","errno":', $errno;

            break;
    }

    if ($errfile) {

        echo ',"errfile":"', addslashes($errfile), '"';
    }

    if ($errline) {

        echo ',"errline":"', $errline, '"';
    }

    echo '}';

    die();
}



/**

 * @desc  VIEW helpers

 */

function base_url()

{

    return BASE_URL;
}

function url($url = '', $echo = true)

{

    if ($echo) {

        echo base_url() . $url;
    } else {

        return base_url() . $url;
    }
}

/**

 * @desc used for snippet [#linkto('url')]

 */

function linkto($url)

{

    return url($url, false);
}

function include_element($name, $data = null)

{

    include VIEW_ELEMENT_DIR . $name . '.php';
}



/**

 * @desc cleans up a string to remove whitespace and commas so its suitable for sql searching

 */

function clean_string($str)

{

    $str = trim($str);

    $search = array("'[,\.;:]+'");

    $replace = " ";

    $str = preg_replace($search, $replace, $str); // replace commas, periods semi colons colons etc with space

    $search = array("'[\s]+'");

    $str = preg_replace($search, $replace, $str); // remove multiple white space

    return trim($str);
}



/**

 * @desc returns a the data quoted and escaped if it should be

 */

function quote($data, $type = 'varchar')

{

    global $DB;

    return in_array($type, array('varchar', 'char', 'date', 'datetime', 'blob', 'mediumblob', 'text', 'mediumtext')) ? "'" . mysqli_real_escape_string($DB->dbh, $data) . "'" : $data;
}



/**

 * @desc Creates a select sting for the table to match the data

 */

function select_string($table, $data)

{

    $selectData = _getQueryArray($table, $data, false);

    if (!empty($selectData)) {



        foreach ($selectData as $i) {

            $o[] = "`" . $i['Field'] . "` = " . $i['Value'];
        }

        return $sql = "SELECT * FROM " . $table . " WHERE " . join(" and ", $o);
    } else {

        return false;
    }
}



/**

 * @Desc This function prepares an insert statement based on the data and the table properties

 * it returns the insert string or false if no data fields match the table fields.

 */

function insert_string($table, $data)

{

    $insertData = _getQueryArray($table, $data); // false means no timestamp data

    if (!empty($insertData)) {

        foreach ($insertData as $i) {

            $iFields[] = "`" . $i['Field'] . "`";

            $iValues[] = $i['Value'];
        }

        return "INSERT into " . $table . " (" . implode(",", $iFields) . ') VALUES (' . implode(",", $iValues) . ')';
    } else {

        return false;
    }
}



/**

 * @Desc This function prepares an update statement based on the data and the table properties

 * it returns the insert string or false if no data fields match the table fields.

 */

function update_string($table, $data, $where = '')

{

    $updateData = _getQueryArray($table, $data);

    // kill created date modification

    unset($updateData['created']);

    if (!empty($updateData)) {

        foreach ($updateData as $i) {

            if (empty($where) && $i['Field'] === 'id') {

                $where = " WHERE `id`=" . $data['id'];

                continue;
            }

            // remove the created value for UPDATES

            if ($i['Field'] != 'created') {

                $items[] = "`" . $i['Field'] . "`" . "=" . $i['Value'];
            }
        }

        return "UPDATE " . $table . " SET " . implode(',', $items) . " " . $where;
    } else {

        return false;
    }
}



/**

 * @desc helper function for insert_string and update_string funcs

 *  returns a array of filed names and quoted/escaped values

 */

function _getQueryArray($table, $data, $timestamp = true)

{

    global $DB;

    $result = $DB->query("SHOW COLUMNS FROM " . $table);



    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_assoc($result)) {

            if (isset($data[$row['Field']])) {

                preg_match('/^([^\(]*)/', $row['Type'], $type);

                $queryData[] = array('Field' => $row['Field'], 'Value' => quote($data[$row['Field']], $type[1]));
            } elseif ($timestamp) {

                // check for modifies and created fields

                if ($row['Field'] == 'modified' or $row['Field'] == 'created') {

                    $queryData[] = array('Field' => $row['Field'], 'Value' => quote(gmdate("Y-m-d H:i:s", time()), 'datetime'));
                }
            }
        }

        return $queryData;
    } else {

        pr("Table " . $table . " has no columns");
    }
}



function db_query($sql)

{

    global $DB;

    $result = array();

    $query = $DB->query($sql);

    if ($query && mysqli_num_rows($query)) {

        return $query;
    } else {

        return false;
    }
}

/**

 * @desc returns an array value if is set

 */

function keyValue($key, $data)

{

    if (isset($data[$key])) {

        return $data[$key];
    }
}
