<?php
class Request {
	var $request;
	
	var $uriSegments;
	var $controller;
	var $action;
	var $requestMethod;
	var $referer;
	var $pathInfo;
	var $params;
	var $data; // Post data
    var $getData;
    var $ajax;
    var  $instance;
    
	 function request() {
	    $this->__construct();
	 }
	 function __construct() {
			
			if (strtolower(php_sapi_name()) == 'cli'){
			   // CLI request
			   $this->requestMethod = 'console'; 
			   $this->pathInfo = $_SERVER['argv'][1];
			
			} else {
			    // webserver request - get URI data
				$this->referer = isSet($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
				$this->requestMethod = isSet($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
				
				$this->pathInfo = isSet($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
		
				if( isSet($_SERVER['HTTP_X_REQUESTED_WITH']) ) { 
					$this->ajax = $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest'?true:false;
				}
	            			
				if ( defined('SESSION_ID') ){
					$this->sessionStart(); 
				}
			}
			$this->uriSegments = split('/', $this->pathInfo);
			if(isSet($this->uriSegments[1])) {
			 	$this->controller = $this->uriSegments[1];
			} else {
			    $this->redirect('admin/');
			}
			
			$this->action = isSet($this->uriSegments[2]) ? $this->uriSegments[2] : '';
			$this->setParams();
			
												
	}
     function __clone(){}
	
	
	
	function sessionStart() {
		// start the session
		ini_set('session.use_cookies',1);
		ini_set('sesion.use_only_cookies',1);
		session_name(SESSION_ID);
		session_start();
		
    	
	}
	
	function setParams() {
		
		if ($this->requestMethod == 'GET') {
                $this->getData = $_GET;
			
		} elseif ($this->requestMethod == 'POST') {
				$data = $_POST;
				
                if (isSet($data['json'])) {
                    if(magic_quotes_gpc) {
                        $data['json'] = stripslashes($data['json']);
                    }
                    $data['jsonData'] = json_decode($data['json'],true);
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
	function post($key=null) {
		if ($key === NULL)  {
			return $this->data;
		}
		if (isSet($this->data[$key]) ) {
			return $this->data[$key];
		} else {
		  return false;
		}
	}
    /**
    *@desc Retireve POST data
    *
    */
    function get($key=null) {
        if ($key === NULL)  {
            return $this->getData;
        }
        if (isSet($this->getData[$key]) ) {
            return urldecode($this->getData[$key]);
        }
    }
    function getState() {
    
    }
    function setState() {
    }
    /**
	*@desc redirect to a local url
	*
	*/
	function redirect($path,$host='',$protocol='') {
		// TODO
		//$protocol = !empty($protocol) ? $protocol."://" : 'http://';
		//$host = empty($host) ? $this->R->host : $host;	
		if (1) {
			header ("Location: ".url($path,false)."\r\n");
			exit;	
		}	
	}
	
		
}
?>