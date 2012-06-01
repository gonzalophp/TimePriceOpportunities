<?php
require_once('class/file.class.php');
require_once('class/data_interface.class.php');

class update_data {
    
    private $_sQuoteId;
    private $_iInterval;
    private $_sStartDate;
    private $_sEndDate;
    private $_oCachedFile;
    
    function update_data($sQuoteId, $iInterval, $sStartDate, $sEndDate){
        $aQuote = explode(' --- ',$sQuoteId);
        $this->_sSource     = $aQuote[0];
        $this->_sQuoteId    = $aQuote[1];
        $this->_iInterval   = $iInterval;
        $this->_sStartDate  = $sStartDate;
        $this->_sEndDate    = $sEndDate;
    }
    
    function run(){
        switch ($this->_sSource) {
            case 'DUKAS':
                $this->_checkCachedData();
                $this->_updateData();
            break;
            case 'YAHOO':
                $this->_update_from_yahoo();
            break;
        }
        
    }
    
    public function _update_from_yahoo(){
        $aStartDate = explode('.',$this->_sStartDate);
        $aEndDate   = explode('.',$this->_sEndDate);
        
        $sRemoteFileURL = 'http://ichart.finance.yahoo.com/table.csv'
                            .'?s='.$this->_sQuoteId
                            .'&a='.($aStartDate[0]-1)
                            .'&b='.$aStartDate[1]
                            .'&c='.$aStartDate[2]
                            .'&d='.($aEndDate[0]-1)
                            .'&e='.$aEndDate[1]
                            .'&f='.$aEndDate[2]
                            .'&g=d&ignore=.csv';    
        $oRemoteFile = new file($sRemoteFileURL);
        $aContentLines = $oRemoteFile->getLines();
        $oDataInterface = new data_interface();
        foreach($aContentLines as $sLine){
            $aLine=explode(',',$sLine);
            $iDate = strtotime($aLine[0]);
            if ($iDate){
                $aResultSet = $oDataInterface->insertYahooData($this->_sQuoteId
                                                            , '1D'
                                                            , $aLine[0].' 00:00:00'
                                                            , $aLine[3]
                                                            , $aLine[2]
                                                            , $aLine[1]
                                                            , $aLine[4]
                                                            , $aLine[5]);
                                                //
//        Date        ,Open       ,High       ,Low        ,Close      ,Volume     ,Adj Close
//        2012-05-30  ,6360.79    ,6392.21    ,6258.89    ,6280.80    ,29677900   ,6280.80
//        
            }
        }
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

        $sPriceDataFolder = dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']).'/price_data';

        $oLocalFile = new file($sPriceDataFolder.'/'.$this->_sQuoteId.'_'.$this->_iInterval.'.dat');
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

$oUpdateData = new update_data($_POST['quote_id']
                              ,$_POST['interval']
                              ,$_POST['datestart']
                              ,$_POST['dateend']); 
$oUpdateData->run();
?>
    