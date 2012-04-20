<?php
require_once('class/file.class.php');
require_once('class/postgres.class.php');

class update_dukascopy {
    
    private $_sQuoteId;
    private $_iInterval;
    private $_oCachedFile;
    private $_oPostgres;
    
    function update_dukascopy($sQuoteId, $iInterval){
        $this->_sQuoteId = $sQuoteId;
        $this->_iInterval = $iInterval;
    }
    
    function run(){
        $this->_oPostgres = new postgres();
        $this->_oPostgres->connect();
        $this->_checkCachedData();
        $this->_updateData();
        //$this->_viewData();
    }
    
    private function _viewData(){
        $result = $oPostgres->query("query_name", 'SELECT * FROM "RAW_DUKASCOPY";');
        var_dump($result);
    }
    
    private function _updateData(){
        $aContentLines = $this->_oCachedFile->getLines();
        //$oPostgres->beginWork();
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

        $oDateTimeZone = new DateTimeZone('Europe/London');
        $sDateFormat = 'm/d/Y^^^H:i:s';
        
        foreach ($aContentLines as $sContentLine){
            $aContentLine = explode(';', $sContentLine);
            if ((count($aContentLine)==7) && ($oDate = DateTime::createFromFormat($sDateFormat, $aContentLine[0].'^^^'.$aContentLine[1], $oDateTimeZone))){

            
                $result = $this->_oPostgres->execute("insert_dukascopy"
                                                    , $insertQuery
                                                    , array(  $this->_sQuoteId
                                                            , $this->_iInterval
                                                            , $oDate->format('Y-m-d H:i:').'00'
                                                            , $aContentLine[5]
                                                            , $aContentLine[6]
                                                            , $aContentLine[3]
                                                            , $aContentLine[4]
                                                            , $aContentLine[2]));
                if ($this->_oPostgres->getLastError() != 23505){ // Duplicated keys
                    echo $this->_oPostgres->getLastError(PGSQL_DIAG_MESSAGE_DETAIL);
                }
            }
        }

        //$oPostgres->commit();
        //$oPostgres->rollback();
    }
    
    private function _getLastDate(){
        $hour = date('Hi',time());
        return date('m.d.Y', (($hour > 2100) ? time():(time()-28800)));
    }
    
    private function _checkCachedData(){
        $bLocalFileOutDated = true;

        $dukascopyFolder = dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']).'/dukascopy';

        $oLocalFile = new file($dukascopyFolder.'/'.$this->_sQuoteId.'_'.$this->_iInterval.'.dat');
        if ($oLocalFile->exists() && $oLocalFile->hasContent()){
            $stat = stat($oLocalFile->fileName);
            
            $hour = date('Hi',time());
            $bLocalFileOutDated = (($hour>2100) && (date('Ymd',time()) > date('Ymd',$stat["mtime"]))) 
                                || (((time() - $stat["mtime"])/28800) > 1);
        }

        if ($bLocalFileOutDated){
            $oRemoteFile = new file('http://www.dukascopy.com/freeApplets/exp/exp.php'
                    .'?fromD='.$this->_getLastDate()        // mm.dd.YYYY - last date
                    .'&np=2000'                              //number of points
                    .'&interval='.$this->_iInterval         //time interval
                    .'&DF=m%2Fd%2FY'                        // date format
                    .'&Stock='.$this->_sQuoteId             //quote id
                    .'&endSym=win'                          //end of line style
                    .'&split=tz');                          //character separator
            $sRemoteContent = $oRemoteFile->getContent();
            $oLocalFile->write($sRemoteContent);
        }
        
        $this->_oCachedFile = $oLocalFile;
    }
}

?>
    