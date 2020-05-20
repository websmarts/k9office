<?php
class Controller
{
    public $error; // general purpose error message populated by methods with error messages
    //public $config; // config class
    public $timeStart;
    public $timeEnd;
    public $action; // action to perform
    public $status;
    public $render = true;
    public $layout = 'default';
    public $moduleLayout = 'layout';
    public $page = '';
    public $R;
    public $viewData = array();
    public $tpl;
    public $data = array(); // Posted data
    public $db; // database object
    public $showEditor = false; // dont show tinymce by default
    public $expandSnippets = true;
    public $moduleName; // name of module being processes if any
    public $renderActionMode = 'normal'; // set to module for modules

    public function __construct($R, $DB)
    {
        $this->R = $R;
        $this->db = $DB;
        //$this->config = Config::singleton();

        //$this->log('controller constructed', 'debug');

        // if Post has JSON data then use that
        if ($this->R->post('jsonData')) {
            $this->data = $this->R->post('jsonData');
        } elseif ($this->R->post()) {
            $this->data = $this->R->post();
        }

        if ($this->R->ajax == true) {
            $this->render = false;
            set_error_handler('ajaxErrorHandler');
        }

        $this->action = $this->R->action ? $this->R->action : 'index'; // default action = index
    }

    public function start()
    {
        $this->timeStart = microtime(true);

        if (method_exists($this, $this->action)) {
            $this->beforeAction();
            $action = $this->action;
            $this->$action();
        } else {
            flashError($this->action . ' method does not exist in ' . __FILE__ . " line number " . __LINE__);
            $this->redirect(DEFAULT_CONTROLLER . '/badrequest/'); // wont work well with cli??
            exit;
        }

        $this->afterAction();

        if ($this->render) {
            $this->render();
        }
        $this->timeEnd = microtime(true);
        flashDebug($this->timeEnd - $this->timeStart . " seconds", 1);
    }

    public function index()
    {
        echo "no index method found in controller";
        exit;
    }

    /**
     * @desc Do this after the called action
     *
     */
    public function beforeAction()
    {}

    /**
     * @desc Do this after the called action
     *
     */
    public function afterAction()
    {}

    /**
     * @desc sets data in the viewdata container
     *
     */
    public function set($key, $value)
    {$this->viewData[$key] = $value;}

    /**
     *@desc redirect to a local url
     *
     */
    public function redirect($path, $host = '', $protocol = '')
    {
        // TODO
        //$protocol = !empty($protocol) ? $protocol."://" : 'http://';
        //$host = empty($host) ? $this->R->host : $host;
        //pr(url($path,false));exit;
        if (1) {
            header("Location: " . url($path, false) . "\r\n");
            exit;
        }
    }

    public function returnToReferer()
    {
        if (isset($_SESSION['lastPathInfo']) && !empty($_SESSION['lastPathInfo'])) {
            $this->redirect($_SESSION['lastPathInfo']);
        } else {
            $this->redirect(''); // home
        }
    }

    public function noPageCacheHeaders()
    {
        header("cache-Control: no-store, no-cache, must-revalidate");
        header("cache-Control: post-check=0, pre-check=0", false);
        // HTTP/1.0
        header("Pragma: no-cache");
        // Date in the past
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        // always modified
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    }

    public function ajaxReply($result = null, $replyCode = "200", $replyText = "okay")
    {
        if ($result == null || !is_array($result)) {
            $result = array();
        }

        // add default replyCode and replyText and Dataif they dont exist
        if (!isset($result['replyCode'])) {
            $result['replyCode'] = $replyCode;
        }

        if (!isset($result['replyText'])) {
            $result['replyText'] = $replyText;
        }

        if (!isset($result['data'])) {
            $result['data'] = array();
        }

        $this->noPageCacheHeaders();

        echo json_encode($result);
        exit;
    }

    public function errorReport($error)
    {
        if ($this->ajax) {
            $this->ajaxReply(array
                (
                    'replyCode' => '500',
                    'replayText' => $error,
                ));
        } else {
            echo "ErrorReport:" . $error;
        }
    }

    public function _loadModel($modelName)
    {
        $model = ucfirst(strtolower($modelName)) . 'Model';

        if (!class_exists($model)) {
            include MODEL_DIR . $modelName . "_model.php";
        }

        $M = new $model($this->db);
        return $M;
    }

    /**
     * @desc validates this-data against validation array
     */
    public function validateInput($validate)
    {
        $errors = array();

        foreach ($this->data as $k => $v) {
            if (isset($validate[$k])) {
                if (!preg_match($validate[$k], $v)) {
                    $errors[$k] = 1;
                }
            }
        }
        return $errors;
    }

    public function email($to, $subject, $data)
    {
        if (preg_match(VALID_EMAIL, $to)) {
            $message = '';

            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    $message .= $k . ": " . $v . "\n";
                }
            } else {
                $message = $data;
            }

            if (mail($to, $subject, $message)) {
                return true;
            } else {
                $this->error = "Error sending email to" . $to;
                return false;
            }
        }
    }

    /**
     * @desc creates image list for tinyMCE
     */
    public function imagelist()
    {
        if (!defined('DATASTORE_TYPE') || DATASTORE_TYPE == 'file') {
            $dir = USER_IMAGES_PATH;
            $images = array();

            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if ((filetype($dir . $file) == "file") && (eregi(".(jpg|jpeg|png|gif)", $file))) {
                            $images[] = $file;
                        }
                    }
                    closedir($dh);
                }
            }

            if (is_Array($images)) {
                $n = 0;
                $elem = 'var tinyMCEImageList = new Array (' . "\n";

                foreach ($images as $v) {
                    $elem .= $n > 0 ? ",\n" : ''; // add the commas between list elements
                    $elem .= "\t" . '["' . $v . '","' . url(USER_IMAGES_DIR, false) . $v . '"]';

                    $n++;
                }
                $elem .= "\n);\n";

                echo $elem;
                exit;
            }
            exit;
        } elseif (DATASTORE_TYPE == 'db') {
            // get images from datastore
            $images = $this->db->fetchRows(
                "select id,title,name from datastore where type like'image/%' order by title asc, name asc ");

            //pr($images);
            if (is_Array($images)) {
                $n = 0;
                $elem = 'var tinyMCEImageList = new Array (' . "\n";

                foreach ($images as $v) {
                    $elem .= $n > 0 ? ",\n" : ''; // add the commas between list elements
                    $elem .= "\t" . '["' . $v['name'] . '(' . $v['title'] . ')","'
                    . url(DEFAULT_CONTROLLER . '/download/' . $v['id'], false) . '"]';

                    $n++;
                }
                $elem .= "\n);\n";

                //pr($elem);
                echo $elem;
                exit;
            }
            exit;
        }
    }

    /**
     * @desc Renders the controller action using viewdata
     */
    public function renderAction()
    {

        $page = new Template(VIEW_DIR . $this->R->controller . DS);
        $page->set('data', $this->viewData);

        if (file_exists(VIEW_DIR . $this->R->controller . DS . $this->page . '.php')) {
            $content = $page->fetch($this->page . '.php');
        } else {
            $content = 'FILE MISSING ERROR: ' . VIEW_DIR . $this->R->controller . DS . $this->page . '.php';
        }

        // START check if content has any snippet names

        // process snippets
        if ($this->expandSnippets && !$this->showEditor) {
            // dont replace snippets if editor is being used
            $content = preg_replace_callback(SNIPPET_PATTERN, 'expandSnippet', html_entity_decode($content, ENT_QUOTES));
        }

        return $content;
    }

    /**
     * @desc Renders the page in the layout
     *
     */
    public function render()
    {
        if (!$this->render) {
            return;
        }

        // check for flash message & flash errors
        if (isset($_SESSION['flash_message'])) {
            $this->set('flash_message', $_SESSION['flash_message']);
            unset($_SESSION['flash_message']);
        }

        if (isset($_SESSION['flash_error'])) {
            $this->set('flash_error', $_SESSION['flash_error']);
            unset($_SESSION['flash_error']);
        }

        if (isset($_SESSION['flash_debug'])) {
            $this->set('flash_debug', $_SESSION['flash_debug']);
            unset($_SESSION['flash_debug']);
        }

        if (isset($_SESSION['formdata'])) {
            $this->set('formdata', $_SESSION['formdata']);
            // dont unset until after content output as content may call getFormdata() which relies on sess formdata
        }

        // remember the last rendered pathinfo
        $_SESSION['lastPathInfo'] = preg_replace('/^\//', '', $this->R->pathInfo);

        flashDebug($this->showEditor);
        $this->set('showEditor', $this->showEditor);

        if ($this->page == '') {
            $this->page = strtolower($this->action);
        }

        // include the template class
        include_once CORE_DIR . 'template_class.php';

        // ACTION CONTENTS
        $content = $this->renderAction(); // note renderAction expands its own snippets

        // WRAP IN SITE TEMPLATE
        $tpl = new Template(VIEW_DIR . 'layouts' . DS);
        $tpl->set('data', $this->viewData);
        $tpl->set('content_for_layout', $content);
        //Render the view
        $content = $tpl->fetch($this->layout . '.php');

        if ($this->expandSnippets && !$this->showEditor) {
            // dont replace snippets if editor is being used
            $content = preg_replace_callback(SNIPPET_PATTERN, 'expandSnippet', html_entity_decode($content, ENT_QUOTES));
        }

        echo $content;

        if (isset($_SESSION['formdata'])) {
            unset($_SESSION['formdata']);
        }
    }
    /**
     * @desc handles bad request
     */
    public function badRequest()
    {
        pr($_SESSION);
        die('bad request detected');
    }

    public function log($message, $type = 'notice', $email = false, $subject = "web logger entry")
    {
        if (!LOGGER_ENABLE) {
            return;
        }
        $data['message'] = $message;
        $data['type'] = $type;
        $nid = $this->db->query(insert_string(LOGGER_TABLE_NAME, $data));

        if ($email) {
            // send notification to site admin

            $to = DEFAULT_EMAIL_TO_ADDRESS;
            $subject .= '_' . $nid;
            $headers;
            //$headers = 'From: webmaster@example.com' . "\r\n" .
            //'Reply-To: webmaster@notjustchocolate.com' . "\r\n" .
            //'X-Mailer: PHP/' . phpversion();
            mail($to, $subject, $message, $headers);
        }
    }
    /**
     * @desc MODULES SUPPORT - ALPHA !!!!
     */

    /**
     * @desc downloads files from datstore
     */
    public function download()
    {
        $id = $this->R->requestSegment(3);

        if (defined('USE_DATASTORE') && $id) {
            $file = $this->db->fetchRow('select name, type, size, content from datastore where id=' . $id);
            header("Content-length: " . $file['size']);
            header("Content-type: " . $file['type']);
            header("Content-Disposition: attachment; filename=" . $file['name']);

            echo $file['content'];
        }
        exit;
    }

    public function pr($a, $print = true)
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
}
