<?php
  class ContactController extends Controller {
  
	var $Client;  // ClientModel

	function beforeAction() {
		if (!isSet($_SESSION['PASS'])){
	      header('Location: /');
	      exit;
	    }
	    // load any models we need for this controller
	    $this->Client = $this->_loadModel('client');
	    
	}
	function index() {

	}
     /**
     * @desc List clients for client autocompleter
     */
     function listClients() {
     	$salesRepId = $this->R->get('salesrep_id');
     	$query = $this->R->get('query');
        $this->ajaxReply($this->Client->listClients($salesRepId,$query)); 
     }
     
    function getTravelData(){
    
    	$data = $this->Client->getTravelData($this->data['sales_rep_id'],$this->data['date']);
       $result =  array(
		'data'=>$data,
		'replyCode'=>'200',
		'replyText'=>'okay'
		);
	    $this->ajaxReply($result);
    }
    function saveTravelData(){
    
      $this->Client->saveTravelData($this->data);
       $result =  array(
		'data'=>array(),
		'replyCode'=>'200',
		'replyText'=>'okay'
		);
	    $this->ajaxReply($result);
    
    } 
	function getRunsheet() {
	   $salesRepId = $this->R->get('salesrep_id');
	   $date = $this->R->get('date');
	   $this->ajaxReply($this->Client->getRunsheet($salesRepId,$date));
	   exit;
	
	}
	
	function updateRunsheet() {
	$result =  array(
		'data'=>array(),
		'replyCode'=>'200',
		'replyText'=>'okay'
		);
	    $this->ajaxReply($result);
	}
	/**
	* @desc deletes a record from contact history
	*/
	function deleteRecord(){	
		$contactRecordId =  $this->R->post('contact_record_id');
		if(!empty($contactRecordId)){
		    if ($this->Client->deleteContactRecord($contactRecordId)){
		         $this->ajaxReply(); // all okay
		    } else {
		        $this->ajaxReply('','400','delete record failed');
		    }
		} else {
		   $this->ajaxReply('','500','no record ID supplied');
		}
	}
	/**
	* @desc save a runsheet contact record
	*/
	function saverecord(){
	  
	  // save the client data
	  $data['client_id'] = $this->data['client_id'];
	  $data['contacts'] =  $this->data['contacts'];
	  $this->Client->updateClientContacts($data);
	  
	   
	  
	  // save the history data 
	  if ($this->data['id'] > 0 && $this->data['id'] != 'undefined') {
	      $this->Client->updateContactRecord($this->data); 
	  } else {
	      unSet($this->data['id'] );
	      $this->Client->addContactRecord($this->data);
	  }
	  
	  
	  $this->ajaxReply() ;
	}
	
	/**
	* @desc Get a list of the clients orders to show on runsheet
	* when a client is selected
	*/
	function getClientOrderList(){
	     $clientId = $this->R->get('client_id');
	     if ($clientId != 'undefined' && $clientId > 0){
	         $this->ajaxReply($this->Client->getOrderListSummary($clientId)); 
	     }
	}
  
  }
?>
