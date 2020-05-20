<?php


if( !function_exists('json_encode') ) {
    function json_encode($data) {
        $json = new Services_JSON();
        return( $json->encode($data) );
    }
}

// Future-friendly json_decode
if( !function_exists('json_decode') ) {
    function json_decode($data) {
        $json = new Services_JSON();
        return( $json->decode($data) );
    }
}

/**
* @desc checks if user is allowed to edit site content
*/
function isSiteEditor() {
    if(isSet($_SESSION['user'])){
    	return true;
    } else {
    	return false;
    }
}



function pr($a,$print = true) { 
	
	$html =  "<pre>";
	$html .= print_r($a,true);
	$html .=  "</pre>";
	
	if($print){
		echo $html;
	} else {
		return $html;
	} 
}

function flashMessage($msg){
	if(isSet($_SESSION['flash_message']) && !empty($_SESSION['flash_message'])){
	 	$_SESSION['flash_message']  .= "<br /> ".$msg;
	} else {
	    $_SESSION['flash_message']  = $msg;
	} 
}
function flashError($msg){
	if(isSet($_SESSION['flash_error']) && !empty($_SESSION['flash_error'])){
	 	$_SESSION['flash_error']  .= "<br /> ".$msg;
	} else {
	    $_SESSION['flash_error']  = $msg;
	} 
}

function flashDebug($data) {

	if (!SHOW_DEBUG){
	     return;
	}
	 
   if(isSet($_SESSION['flash_debug']) && !empty($_SESSION['flash_debug'])){ 
	 	$_SESSION['flash_debug']  .= pr($data, false);
	} else { 
		
	    $_SESSION['flash_debug']  = pr($data, false); 
	} 
}

/**
* @desc Error handler for ajax calls
*/
function ajaxErrorHandler($errno, $errstr, $errfile, $errline)	{
	switch ($errno) {
		case E_USER_ERROR:
			echo '{"replyCode":611,"replyText":"User Error: ' , addslashes($errstr) . '","errno":', $errno;
			break;
		case E_USER_WARNING:
			echo '{"replyCode":612,"replyText":"User Warning: ' , addslashes($errstr) . '","errno":', $errno;
			break;
		case E_USER_NOTICE:
		case E_NOTICE:
			return false;
		default:
			echo '{"replyCode":610,"replyText":"' , addslashes($errstr) . '","errno":', $errno;
			break;
	}
	if ($errfile) {
		echo ',"errfile":"' , addslashes($errfile) ,'"';
	}
	if ($errline) {
		echo ',"errline":"', $errline ,'"';
	}
	echo '}';
	die();
}

/**
* @desc  VIEW helpers
*/
function base_url(){
	return BASE_URL;
}
function url($url='',$echo=true){
	if ($echo) {
       echo base_url().$url; 
    } else {
       return base_url().$url;
    } 
}
function include_element($name,$data=null) {
    include(VIEW_ELEMENT_DIR.$name.'.php');    
}



 /**
  * @desc cleans up a string to remove whitespace and commas so its suitable for sql searching
  */
function clean_string($str) {
    $str = trim($str);		
	$search = array( "'[,\.;:]+'");
	$replace = " ";
	$str = preg_replace($search,$replace,$str);  // replace commas, periods semi colons colons etc with space
    $search = array( "'[\s]+'"); 
    $str = preg_replace($search,$replace,$str); // remove multiple white space
    return trim($str);
}


/**
* @desc returns a the data quoted and escaped if it should be	
*/
function quote($data,$type='varchar'){ 
    return in_array($type, array ('varchar','char','date','datetime','blob','mediumblob','text','mediumtext')) ? "'".mysql_real_escape_string($data)."'" : $data; 
}

/**
* @desc Creates a select sting for the table to match the data
*/
function select_string($table,$data) {
    $selectData = _getQueryArray ($table,$data,false);
    if(!empty($selectData) ) {
       
        foreach ($selectData as $i) {
                  $o[] = "`".$i['Field']."` = ". $i['Value'];                                                                        
        }
        return  $sql = "SELECT * FROM ".$table." WHERE " . join(" and ",$o);  
    } else {
          return false;
    }          
}

/**
* @Desc This function prepares an insert statement based on the data and the table properties
* it returns the insert string or false if no data fields match the table fields.
*/
function insert_string ($table,$data) { 	
	$insertData = _getQueryArray ($table,$data );// false means no timestamp data   	
	if(!empty($insertData) ) {
		foreach ($insertData as $i) {
			  	$iFields[] = "`".$i['Field']."`";
                $iValues[] = $i['Value'];		  							  				
		}
		return  "INSERT into ".$table." (".implode(",",$iFields).') VALUES ('.implode(",",$iValues).')';
	} else {
	  	return false;
	}  		
}

/**
* @Desc This function prepares an update statement based on the data and the table properties
* it returns the insert string or false if no data fields match the table fields.
*/
function update_string ($table,$data,$where='') {   
    $updateData = _getQueryArray ($table,$data); 
    // kill created date modification
    unSet($updateData['created']);       
    if(!empty($updateData) ) {
        foreach ($updateData as $i) {
            if (empty($where) && $i['Field'] === 'id') {
                $where = " WHERE `id`=".$data['id'];
                continue;
            }
        
            $items[] = "`".$i['Field']."`"."=".$i['Value'];                                                                
        }
        return  "UPDATE ".$table. " SET " . implode(',',$items)." ".$where;
    } else {
          return false;
    }           
}

/**
* @desc helper function for insert_string and update_string funcs
*  returns a array of filed names and quoted/escaped values
*/
function _getQueryArray ($table,$data,$timestamp=true) {
    $result = mysql_query("SHOW COLUMNS FROM ".$table);
    
    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) { 
            if(isSet($data[$row['Field']])) {   
                preg_match('/^([^\(]*)/',$row['Type'],$type);
                $queryData[] = array('Field'=>$row['Field'] ,'Value'=> quote($data[$row['Field']] , $type[1]) );
            } elseif($timestamp ) {
                // check for modifies and created fields
                if ( $row['Field'] == 'modified' or $row['Field'] == 'created' ) {
                    $queryData[] = array('Field'=>$row['Field'] ,'Value'=> quote(gmdate("Y-m-d H:i:s", time()),'datetime' ) );
                }
            }     
        }
        return $queryData; 
    } else {
       pr("Table ".$table ." has no columns");
    } 
}

function db_query($sql) {
    $result = array();
    $query = mysql_query($sql);
    if ($query && mysql_num_rows($query)) {
        return $query;
    } else {
        return false;
    }
}


?>