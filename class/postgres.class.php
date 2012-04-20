<?php
class postgres {
    
    const HOST      = 'localhost';
    const PORT      = 5432;
    const DBNAME    = 'postgres';
    const USER      = 'postgres';
    const PASSWORD  = 'postgres';

    private $_host;
    private $_port;
    private $_dbname;
    private $_user;
    private $_password;
    
    private $_dbconn;
    private $_rResult;
    
    function connect($host      = self::HOST
                    ,$port      = self::PORT
                    ,$dbname    = self::DBNAME
                    ,$user      = self::USER
                    ,$password  = self::PASSWORD) {
        $conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
        if ($this->_dbconn = pg_connect($conn_string)) {
            $this->_host     = $host;
            $this->_port     = $port;
            $this->_dbname   = $dbname;
            $this->_user     = $user;
            $this->_password = $password;
        }
        
        return !($this->_dbconn == false);
    }
    
    function beginWork(){
        return $this->_db_query("begin_work", "BEGIN WORK");
    }
    
    function query($query_name, $query, $params=array()){
        if ($rResult=$this->_db_query($query_name, $query, $params)){
            return pg_fetch_all($rResult);
        }
        else {
            return false;
        }
    }
    
    function execute($query_name, $query, $params=array()){
        return !($this->_db_query($query_name, $query, $params)==false);
    }
    
    function commit(){
        return $this->_db_query("commit", "COMMIT");
    }
    
    function rollback(){
        return $this->_db_query("rollback", "ROLLBACK");
    }
    
    private function _db_query($query_name, $query, $params=array()){
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