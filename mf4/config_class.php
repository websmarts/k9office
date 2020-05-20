<?php
  class Config {
  
        private static  $instance = NULL;
        protected  $__data = array(); 
    
        public static function singleton() {
            if (self::$instance == NULL) {          
                self::$instance = new Config(); 
            } 
                return self::$instance;   
        }
        
        // make PRIVATE to stop anyone using NEW or CLONE
        private function __construct() {}
        private function __clone(){}
        
        public function __get($property) {
            if (isSet($this->__data[$property])) {
                return $this->__data[$property];
            } else {
                return false;
            }    
        }
        
        public function __set($property,$value) {
            $this->__data[$property] = $value;
        }
        
        public  function __isset($property){
            if (isSet($this->__data[$property])){
            return true;
            } else {
                return false;
            }
        }
 
        public  function __unset($property){
        
            if ( isSet($this->__data[$property]) ) {
                unset( $this->__data[$property] );
                return true;
            } else {
                return false;
            }
        }       
  }
?>
