<?php

class Request

{

    public $request;



    public $uriSegments;

    public $controller;

    public $action;

    public $requestMethod; // console, GET, POST

    public $referer;

    public $pathInfo;

    public $data; // Post data

    public $getData;

    public $ajax;

    public $routes;



    public function __construct($routes)

    {

        if (is_array($routes) && count($routes)) {

            foreach ($routes as $pathIn => $pathOut) {

                $this->routes(strtolower($pathIn), strtolower($pathOut));

            }



        } else {

            $routes = array();

        }

        // pr(strtolower(php_sapi_name()));

        // pr($_SERVER);



        if (strtolower(php_sapi_name()) == 'cli') {

            // CLI request

            $this->requestMethod = 'console';

            $this->pathInfo = $_SERVER['argv'][1];



        } else {

            // webserver request - get URI data

            $this->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

            $this->requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';

            //pr($_SERVER);



            if (isset($_SERVER['ORIG_PATH_INFO'])) {

                // remove the /office/index.php/ bit from start if orig_path_info

                //$this->pathInfo = preg_replace('#(/.*?/.*?)/(.*)#', '/$2', $_SERVER['ORIG_PATH_INFO']);
                $this->pathInfo =  $_SERVER['ORIG_PATH_INFO'];

            } elseif (isset($_SERVER['PATH_INFO'])) {

                $this->pathInfo = $_SERVER['PATH_INFO'];

            } else {

                $this->pathInfo = '';

            }

            // pr($this->pathInfo);



            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {

                $this->ajax = $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;

            } else {

                $this->ajax = false;

            }



            if (defined('SESSION_ID')) {

                $this->sessionStart();

            }

        }

        // Check for aliased ROUTES

        if (count($this->routes) > 0) {

            $this->pathInfo = preg_replace($this->routes['in'], $this->routes['out'], $this->pathInfo);

        }

        // pr($this->routes);

        // pr($this->pathInfo);

        // exit;

        $this->uriSegments = explode('/', $this->pathInfo);

        // pr($this->uriSegments);

        if (isset($this->uriSegments[1])) {

            $this->controller = $this->uriSegments[1];

        } else {

            $this->controller = DEFAULT_CONTROLLER;

            //$this->redirect( defined('DEFAULT_CONTROLLER')?DEFAULT_CONTROLLER:'main'."/" );// no good for cli calls

        }



        $this->action = isset($this->uriSegments[2]) ? $this->uriSegments[2] : 'index';

        $this->setParams();



    }

    public function __clone()

    {}



    public function redirect($path, $host = '', $protocol = '')

    {

        //*** CANT USE REDIRECTs WITH CLI CALLS!!!!

        header("Location: " . url($path, false) . "\r\n");

        exit;

    }



    public function routes($in, $out)

    {



        $this->routes['in'][] = '#^' . $in . '#i';

        $this->routes['out'][] = $out;



    }



    public function sessionStart()

    {

        // start the session

        if (!isset($_SESSION)) {

            ini_set('session.use_cookies', 1);

            ini_set('sesion.use_only_cookies', 1);

            session_name(SESSION_ID);

            session_start();

        }



    }



    public function setParams()

    {



        if ($this->requestMethod == 'GET') {

            $this->getData = $_GET;



        } elseif ($this->requestMethod == 'POST') {

            $data = $_POST;



            if (isset($data['json'])) {

                if (magic_quotes_gpc) {

                    $data['json'] = stripslashes($data['json']);

                }

                $data['jsonData'] = json_decode($data['json'], true);

            }

            $this->data = $data;



        } elseif ($this->requestMethod == 'console') {

            // extract vars here if need be

        }



    }



    /**

     *@desc Retireve POST data

     *

     */

    public function post($key = null)

    {

        if ($key === null) {

            return $this->data;

        }

        if (isset($this->data[$key])) {

            return $this->data[$key];

        } else {

            return false;

        }

    }

    /**

     *@desc Retireve POST data

     *

     */

    public function get($key = null)

    {

        if ($key === null) {

            return $this->getData;

        }

        if (isset($this->getData[$key])) {

            return urldecode($this->getData[$key]);

        } else {

            return false;

        }

    }

    /**

     * @desc call to find out callType

     */

    public function isAjax()

    {

        return $this->ajax;

    }



    public function requestMethod()

    {

        return $this->requestMethod;

    }



    public function requestSegment($n = null)

    {

        if ($n === null) {

            return $this->uriSegments;

        }

        if (isset($this->uriSegments[$n])) {

            return $this->uriSegments[$n];

        } else {

            return false;

        }

    }



}

