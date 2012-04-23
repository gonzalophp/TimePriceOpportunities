<?php
class postgres {
    private $_dbconn;
    private $_rResult;
    
    function connect() {
        $conn_string = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWORD;
        $this->_dbconn = pg_connect($conn_string);
        
        return !($this->_dbconn == false);
    }
    
    function beginWork(){
        return $this->_db_query("BEGIN WORK");
    }
    
    function query($query, $params=array()){
        if ($rResult=$this->_db_query($query, $params)){
            return pg_fetch_all($rResult);
        }
        else {
            return false;
        }
    }
    
    function execute($query, $params=array()){
        return !($this->_db_query($query, $params)==false);
    }
    
    function commit(){
        return $this->_db_query("COMMIT");
    }
    
    function rollback(){
        return $this->_db_query("ROLLBACK");
    }
    
    private function _db_query($query, $params=array()){
        if ($this->_dbconn) {
            pg_send_query_params ($this->_dbconn, $query, $params);
            return ($this->_rResult = pg_get_result($this->_dbconn));
        }
        else {
            return false;
        }
    }
    
    public function getLastError($field=PGSQL_DIAG_SQLSTATE) {
        /*
         * PGSQL_DIAG_SQLSTATE        - Error code
         * PGSQL_DIAG_MESSAGE_PRIMARY - Simple message
         * PGSQL_DIAG_MESSAGE_DETAIL  - Detailed message
         */
        return pg_result_error_field($this->_rResult, $field);
    }
}