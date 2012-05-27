<?php
require_once('class/postgres.class.php');

class data_postgresql {
    static private $_oPostgres=null;
    
    function data_postgresql() {
        if (is_null(self::$_oPostgres)){
            self::$_oPostgres = new postgres();
            self::$_oPostgres->connect();
        }
    }
    
    public function getLastError($field=PGSQL_DIAG_SQLSTATE){
        return self::$_oPostgres->getLastError($field);
    }
    
    public function get_dukascopy_quotes(){
        $sQuery = 'SELECT "DQI_dukascopy_id"'
                        .' ,"DQI_quote_id"'
                 .' FROM public."DUKASCOPY_QUOTES_ID";';
        
        return self::$_oPostgres->query($sQuery);
    }
    
    public function getDukascopyTPOData($sQuote, $iInterval,$iDays){
        $sQuery = 'SELECT "RD_dukascopy_id" as quote_id'
                        .' ,"RD_interval"   as interval'
                        .' ,to_char("RD_datetime",\'YYYY-MM-DD HH24:MI:\')||\'00\'   as datetime'
                        .' ,"RD_min"        as min'
                        .' ,"RD_max"        as max'
                        .' ,"RD_open"       as open'
                        .' ,"RD_close"      as close'
                        .' ,"RD_volume"     as volume'
                 .' FROM public."RAW_DUKASCOPY"'
                 .' WHERE "RD_dukascopy_id" = $1'
                    .' AND "RD_interval" = $2'
                    .' AND date_trunc(\'day\',"RD_datetime") in ( SELECT distinct date_trunc(\'day\',"RD_datetime")'
                                                                .' FROM public."RAW_DUKASCOPY" '
                                                                .' WHERE "RD_dukascopy_id" = $1'
                                                                .' AND "RD_interval" = $2'
                                                                .' ORDER BY date_trunc(\'day\',"RD_datetime") desc'
                                                                .' LIMIT $3);';
        
        
        return self::$_oPostgres->query($sQuery, array($sQuote
                                                    ,$iInterval
                                                    ,$iDays));
    }
    
    public function getFirstDateDukascopyData($sQuote, $iInterval){
        $sQuery = 'select min("RD_datetime")'
                . ' from "RAW_DUKASCOPY"'
                . ' where "RD_dukascopy_id"=$1'
                . ' and "RD_interval"=$2';
        return self::$_oPostgres->query($sQuery, array($sQuote
                                                      ,$iInterval));
    }
    
    public function insertDukascopyData($RD_dukascopy_id
                                        ,$RD_interval
                                        ,$RD_datetime     
                                        ,$RD_min       
                                        ,$RD_max          
                                        ,$RD_open         
                                        ,$RD_close        
                                        ,$RD_volume) {
                                                    
        $insertQuery = 'INSERT INTO "RAW_DUKASCOPY"( "RD_dukascopy_id"' // 1
                                                . ',"RD_interval"'      // 2
                                                . ',"RD_datetime"'      // 3
                                                . ',"RD_min"'           // 4
                                                . ',"RD_max"'           // 5
                                                . ',"RD_open"'          // 6
                                                . ',"RD_close"'         // 7
                                                . ',"RD_volume")'       // 8
                                        . 'VALUES ($1'
                                                .',$2'
                                                .',$3'
                                                .',$4'
                                                .',$5'
                                                .',$6'
                                                .',$7'
                                                .',$8);';
        
        return self::$_oPostgres->query($insertQuery
                                        , array( $RD_dukascopy_id
                                                ,$RD_interval
                                                ,$RD_datetime     
                                                ,$RD_min       
                                                ,$RD_max          
                                                ,$RD_open         
                                                ,$RD_close        
                                                ,$RD_volume));
    }
    
    
    public function get_telegraph_quotes(){
        $sQuery = 'SELECT "TQI_telegraph_id"'
                        .' ,"TQI_quote_id"'
                 .' FROM public."TELEGRAPH_QUOTES_ID";';
        
        return self::$_oPostgres->query($sQuery);
    }
    
    public function getTelegraphTPOData($sQuote, $iInterval, $iDays){
        $sQuery = 'SELECT "RT_telegraph_id" as quote_id'
                        .' ,"RT_interval"   as interval'
                        .' ,"RT_datetime"   as datetime'
                        .' ,"RT_min"        as min'
                        .' ,"RT_max"        as max'
                        .' ,"RT_open"       as open'
                        .' ,"RT_close"      as close'
                        .' ,"RT_volume"     as volume'
                 .' FROM public."RAW_TELEGRAPH"'
                 .' WHERE "RT_telegraph_id" = $1'
                    .' AND "RT_interval" = $2'
                    .' AND date_trunc(\'day\',"RT_datetime") in ( SELECT distinct date_trunc(\'day\',"RT_datetime")'
                                                                .' FROM public."RAW_TELEGRAPH" '
                                                                .' WHERE "RT_telegraph_id" = $1'
                                                                .' AND "RT_interval" = $2'
                                                                .' ORDER BY date_trunc(\'day\',"RT_datetime") desc'
                                                                .' LIMIT $3);';
        
        
        return self::$_oPostgres->query($sQuery, array($sQuote
                                                    ,$iInterval
                                                    ,$iDays));
    }
    
    public function insertTelegraphData($RT_telegraph_id
                                        ,$RT_interval
                                        ,$RT_datetime     
                                        ,$RT_min       
                                        ,$RT_max          
                                        ,$RT_open         
                                        ,$RT_close        
                                        ,$RT_volume) {
                                                    
        $insertQuery = 'INSERT INTO "RAW_TELEGRAPH"( "RT_telegraph_id"' // 1
                                                . ',"RT_interval"'      // 2
                                                . ',"RT_datetime"'      // 3
                                                . ',"RT_min"'           // 4
                                                . ',"RT_max"'           // 5
                                                . ',"RT_open"'          // 6
                                                . ',"RT_close"'         // 7
                                                . ',"RT_volume")'       // 8
                                        . 'VALUES ($1'
                                                .',$2'
                                                .',$3'
                                                .',$4'
                                                .',$5'
                                                .',$6'
                                                .',$7'
                                                .',$8);';
        
        return self::$_oPostgres->query($insertQuery
                                        , array( $RT_telegraph_id
                                                ,$RT_interval
                                                ,$RT_datetime     
                                                ,$RT_min       
                                                ,$RT_max          
                                                ,$RT_open         
                                                ,$RT_close        
                                                ,$RT_volume));
    }
}
?>
