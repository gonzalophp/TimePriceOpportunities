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
    
    public function get_dukascopy_quotes(){
        $sQuery = 'SELECT "DQI_dukascopy_id"'
                        .' ,"DQI_quote_id"'
                 .' FROM public."DUKASCOPY_QUOTES_ID";';
        
        return self::$_oPostgres->query($sQuery);
    }
    
    public function getTPOData($sQuote, $iInterval,$iDays){
        $sQuery = 'SELECT "RD_dukascopy_id"'
                        .' ,"RD_interval"'
                        .' ,"RD_datetime"'
                        .' ,"RD_min"'
                        .' ,"RD_max"'
                        .' ,"RD_open"'
                        .' ,"RD_close"'
                        .' ,"RD_volume"'
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
}
?>
