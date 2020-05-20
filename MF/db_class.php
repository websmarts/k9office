<?php
  class DB {
  
    var $instance = NULL ;
    var $dbh;
    var $error;
    
    
    function DB() {
        $this->__construct();
    }
    
    function __construct() { // private  to stop new
         $this->dbh = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD ) or die("Could not connect : " . mysql_error());     
         mysql_select_db(DB_DATABASE,$this->dbh) or die("Could not select database:".$database); 
    
    }
    function __clone() {// private to stop clone
    }
 /*   
      function showColumns($table) {
       $result = array();
       $query = mysql_query("SHOW COLUMNS FROM ".$table,$this->dbh);
        if (mysql_num_rows($query) > 0) {  
            while ($row = mysql_fetch_assoc($query)) { 
                 $result[] = $row;
            }
            
        }
        return $result; 
    }
  */  
    function dbh(){
        return $this->dbh;
    }
    /**
    * @desc make a database query
    *  returns insert_id if INSERT , affected_rows if UPDATE , result ID if SELECT
    * 
    */
     function query($sql) {
    $this->error = false;
    $sql = trim($sql);
    $result = mysql_query($sql); 
    if ($result){ // no query errors
        if ( preg_match("/^\binsert\b\s+/i",$sql) ) {
                return mysql_insert_id($this->dbh);
         
         } elseif (preg_match("/^\b(update|delete)\b\s+/i",$sql)) {
                return mysql_affected_rows($this->dbh); 
         
         } else {
            // if query returns data
            if ( mysql_num_rows($result)) {
                return $result;
            } else {
                return false;
            }
         }
    } else { // query failed
    
         $this->error = 'Database error: ' . mysql_error();
         return false;

    }     
}



function ajaxQuery($sql){
    if(!empty($sql)){
        if ($query = $this->query($sql)) {
		     while ( $row = mysql_fetch_assoc($query) ) {
		        $result[] = $row;
		    }
		    return  array(
        		'replyCode'=>'200',
        		'replyText'=>'Ok',
        		'data'=>$result
    		);           
		} else {
		    return  array(
        		'replyCode'=>'500',
        		'replyText'=> $this->error ." SQL=".$sql,
        		'data'=>array()
				);
		
		}  
    } else {
         return  array(
        		'replyCode'=>'500',
        		'replyText'=>'No Query string supplied to ajaxQuery',
        		'data'=>array()
        		);
    
    }
           
}

// end of class  
}
?>
