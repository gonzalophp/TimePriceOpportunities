<?php
require_once('class/file.class.php');
require_once('class/data_interface.class.php');

class update_dukascopy {
    
    private $_sQuoteId;
    private $_iInterval;
    private $_sStartDate;
    private $_oCachedFile;
    
    function update_dukascopy($sQuoteId, $iInterval, $sStartDate){
        $this->_sQuoteId = $sQuoteId;
        $this->_iInterval = $iInterval;
        $this->_sStartDate = $sStartDate;
    }
    
    function run(){
        $this->_checkCachedData();
        $this->_updateData();
    }
    
    private function _updateData(){
        $aContentLines = $this->_oCachedFile->getLines();
        
        $oDateTimeZone = new DateTimeZone('Europe/London');
        $sDateFormat = 'm/d/Y^^^H:i:s';
        
        $oDataInterface = new data_interface();

        foreach ($aContentLines as $sContentLine){
            $aContentLine = explode(';', $sContentLine);
            if ((count($aContentLine)==7) && ($oDate = DateTime::createFromFormat($sDateFormat, $aContentLine[0].'^^^'.$aContentLine[1], $oDateTimeZone))){

                $aResultSet = $oDataInterface->insertDukascopyData($this->_sQuoteId
                                                                , $this->_iInterval
                                                                , $oDate->format('Y-m-d H:i:').'00'
                                                                , $aContentLine[5]
                                                                , $aContentLine[6]
                                                                , $aContentLine[3]
                                                                , $aContentLine[4]
                                                                , $aContentLine[2]);
            }
        }
        
        
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

        
        
        
        
        // HARDCODED
        // HARDCODED
        // HARDCODED
        // HARDCODED
        // HARDCODED
        $bLocalFileOutDated = true;
        
//        $oDataInterface = new data_interface();
//        $aResultSet = $oDataInterface->getFirstDateDukascopyData($this->_sQuoteId,$this->_iInterval);
//        $this->_sStartDate = date('m.d.Y',strtotime($aResultSet['0']['min']));
        var_dump($this->_sStartDate);
        // HARDCODED
        // HARDCODED
        // HARDCODED
        // HARDCODED
        // HARDCODED
        
        
        
        
        
        if ($bLocalFileOutDated){
            $oRemoteFile = new file('http://www.dukascopy.com/freeApplets/exp/exp.php'
//                    .'?fromD='.$this->_getLastDate()        // mm.dd.YYYY - last date
                    .'?fromD='.$this->_sStartDate        // mm.dd.YYYY - last date
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

$oUpdateDukascopy = new update_dukascopy($_POST['quote_id']
                                        ,$_POST['interval']
                                        ,$_POST['datepicker']); 
$oUpdateDukascopy->run();
?>
    