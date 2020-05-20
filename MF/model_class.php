<?php
class Model {
   
   var  $db;
   var $dbh;
   
  function model($db) {
  $this->db = $db;
  
  }
   
  
   
   function __getTableFieldsInfo ($table) {
    $result = mysql_query("SHOW COLUMNS FROM ".$table , $this->dbh);
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) { 
               $queryData[] = $row; 
            }
            return $queryData; 
        }
   } 
   
  
   
}
?>