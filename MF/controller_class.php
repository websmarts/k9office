<?php 

class Controller {
	
	var $status;
	var $render = true;
    var $layout = 'default';
	var $page = '';
	var $R;
	var $viewData = array();
	var $tpl;
    var $data = array(); // Posted data
    var $db; // database object
   
   
	function controller($R,$DB) {
	   $this->__construct($R,$DB);
	}
	
	function __construct($R,$DB) {		
	    $this->R = $R;
        $this->db = $DB;
        
        // if Post has JSON data then use that
        if ($this->R->post('jsonData')) {
            $this->data = $this->R->post('jsonData'); 
        } elseif ( $this->R->post() ) {
            $this->data = $this->R->post();
        }
        if ($this->R->ajax == true) {
            $this->render = false; 
            set_error_handler('ajaxErrorHandler');
        }								
	}
    
    function start() {
        $this->beforeAction(); 
            
        $this->R->action = $this->R->action?$this->R->action:'index'; // default action = index 

        if ( method_exists($this,$this->R->action) ){
            $action = $this->R->action;
            $this->$action();
        } else {
             die('<b>ERROR: '.$this->R->action.'</b> method does not exist in '.__FILE__." line number ". __LINE__);
        }
        
        $this->afterAction();
        if ($this->render){
          	$this->render();
        } 
    
    }
    
    function index() {
        echo "no index method found in controller" ;
        exit;
    }
	
	/**
	* @desc Do this after the called action
	*
	*/
	function beforeAction() {
		
	}
	
	
	/**
	* @desc Do this after the called action
	*
	*/
	function afterAction() {
	
	}
	
	
	/**
	* @desc sets data in the viewdata container
	*
	*/
	function set($key,$value) {
		$this->viewData[$key] = $value;	
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
	
	function returnToReferer(){
	   if(isSet($_SESSION['lastPathInfo']) && !empty($_SESSION['lastPathInfo'])){
	       $this->redirect($_SESSION['lastPathInfo']);
	   } else {
	       $this->redirect(''); // home
	   }
	   
	}
	
	function noPageCacheHeaders() {
        header("cache-Control: no-store, no-cache, must-revalidate");
        header("cache-Control: post-check=0, pre-check=0", false);
        // HTTP/1.0
        header("Pragma: no-cache");
        // Date in the past
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        // always modified
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    
    }
    
    function ajaxReply($result = null,$replyCode ="200",$replyText ="okay") {
    	if ($result == null || !is_array($result)){
    	  $result = array();
    	}
    	
    	// add default replyCode and replyText and Dataif they dont exist
    	if (!isSet($result['replyCode'])){
    	    $result['replyCode'] = $replyCode;
    	}
    	if (!isSet($result['replyText'])){
    	    $result['replyText'] = $replyText;
    	}
    	if (!isSet($result['data'])){
    	    $result['data'] = array();
    	}
    	
    	 $this->noPageCacheHeaders(); 
    	 echo json_encode($result);
    	 exit;  
    }
    
    function errorReport($error) {
      if($this->ajax) {
      	$this->ajaxReply(array( 	
      		'replyCode' => '500',
      		'replayText' =>$error 	
      	));
      
      } else {
           echo "ErrorReport:".$error;
      }
    
    }
    
    function _loadModel($modelName) {
        include(MODEL_DIR.$modelName."_model.php");
        $model =  ucfirst(strtolower($modelName)).'Model';
        $M =  new $model($this->db);
        return $M;  
    }
    
    
    
	/**
	* @desc Renders the page in the layout
	*
	*/
	function render() {
		if (!$this->render) {
            return;
        }
        
        // check for flash message & flash errors
        if( isSet($_SESSION['flash_message'])){
        	$this->set('flash_message',$_SESSION['flash_message']);
        	unSet($_SESSION['flash_message']);
        }
        if( isSet($_SESSION['flash_error'])){
        	$this->set('flash_error',$_SESSION['flash_error']);
        	unSet($_SESSION['flash_error']);
        }
         if( isSet($_SESSION['flash_debug'])){
        	$this->set('flash_debug',$_SESSION['flash_debug']);
        	unSet($_SESSION['flash_debug']);
        }
        
        // remember the last rendered pathinfo
        $_SESSION['lastPathInfo'] = preg_replace('/^\//','',$this->R->pathInfo);
        
		
		
		if ($this->page == '') {
			$this->page = $this->R->action;
		}
        
        // include the template class
        include_once(CORE_DIR.'template_class.php');  
		// Layout Template
		$tpl = new Template(VIEW_DIR.'layouts'.DS);
		$tpl->set('data',$this->viewData);
		
		// Page Template
		$page = new Template(VIEW_DIR.$this->R->controller.DS);
		$page->set('data',$this->viewData); 		
		$tpl->set('content_for_layout',$page->fetch($this->page.'.php'));
		//Render the view
		echo $tpl->fetch($this->layout.'.php');		
	}
	
	/**
	* @desc validates this-data against validation array
	*/
	function validateInput($validate) {
    	$errors = array();
    	foreach($this->data as $k=>$v) {
    		if($validate[$k]){
    		    if(!preg_match($validate[$k],$v)){
    	   		$errors[$k] = 1;
    		   }
    		}   
    	}
       return $errors;
    }
	
	
}

?>