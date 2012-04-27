<?php
require_once('class/file.class.php');
require_once('class/data_interface.class.php');

class update_telegraph {
    
    private $_sQuoteId;
    private $_sDate;
    
    public function update_telegraph($sQuoteId, $sDate){
        $this->_sQuoteId = $sQuoteId;
        $this->_sDate = $sDate;
    }
    
    public function run(){
        $oDOMHTML = new DOMDocument();
        $oDateTimeZone = new DateTimeZone('Europe/London');
        $sDateFormat = 'Y-m-d H:i:s';
        $aIntraday = array();
        
        $sToday = date('Y-m-d');

        for($iPage=0,$bDayFinished=false;!$bDayFinished;$iPage++){
            $sContent = $this->_getPageData($iPage);

            @$oDOMHTML->loadHTML($sContent);

            $oDOMXPATH = new DOMXPath($oDOMHTML);
            $oDOMNodeList = $oDOMXPATH->query('//table[@class="full" and not(@id="mam-quote-line")]//tr[@class="odd" or @class="even"]');

            for($i=0;$i<$oDOMNodeList->length;$i++){
                $oDOMNodeListTD = $oDOMXPATH->query('.//td[position()<4]',$oDOMNodeList->item($i));

                $oDate = DateTime::createFromFormat($sDateFormat, $oDOMNodeListTD->item(0)->textContent, $oDateTimeZone);
                $sDateTimeMinute = $oDate->format('Y-m-d H:i:').'00';

                if ($bDayFinished = ($sDateTimeMinute < ($sToday.' 07:40:00'))){
                    break;
                }

                $sDateTime  = $oDate->format('Y-m-d H:i:s');
                $nValue     = str_replace(',', '', $oDOMNodeListTD->item(1)->textContent);
                $iVolume    = str_replace(',', '', $oDOMNodeListTD->item(2)->textContent);
                if (!array_key_exists($sDateTimeMinute, $aIntraday)){
                    $aIntraday[$sDateTimeMinute] = array('min_date_time'    => $sDateTime
                                                        , 'max_date_time'   => $sDateTime
                                                        , 'min'             => $nValue
                                                        , 'max'             => $nValue
                                                        , 'open'            => $nValue
                                                        , 'close'           => $nValue
                                                        , 'volume'          => $iVolume);
                }
                else {
                    if ($sDateTime < $aIntraday[$sDateTimeMinute]['min_date_time']){
                        $aIntraday[$sDateTimeMinute]['min_date_time'] = $sDateTime;
                        $aIntraday[$sDateTimeMinute]['open'] = $nValue;
                    }
                    if ($sDateTime > $aIntraday[$sDateTimeMinute]['max_date_time']){
                        $aIntraday[$sDateTimeMinute]['max_date_time'] = $sDateTime;
                        $aIntraday[$sDateTimeMinute]['close'] = $nValue;
                    }
                    if ($nValue < $aIntraday[$sDateTimeMinute]['min']){
                        $aIntraday[$sDateTimeMinute]['min'] = $nValue;
                    }
                    elseif ($nValue > $aIntraday[$sDateTimeMinute]['max']){
                        $aIntraday[$sDateTimeMinute]['max'] = $nValue;
                    }
                    $aIntraday[$sDateTimeMinute]['volume'] += $iVolume;
                }
            }
        }
        
        $oDataInterface = new data_interface();
        
        foreach($aIntraday as $sDateTime=>$aDayData){
            
            $aResultSet = $oDataInterface->insertTelegraphData($this->_sQuoteId
                                                            , '60'
                                                            , $sDateTime
                                                            , $aDayData['min']
                                                            , $aDayData['max']
                                                            , $aDayData['open']
                                                            , $aDayData['close']
                                                            , $aDayData['volume']);
        }
        
//        var_dump($aIntraday);
//        exit;
    }
    
    private function _getPageData($iPage){
        $oLocalFile = new file('http://shares.telegraph.co.uk/trades/?pagenum='.$iPage.'&epic='.$this->_sQuoteId);
        return $oLocalFile->getContent();
    }
}
set_time_limit(0);
$oUpdateTelegraph = new update_telegraph($_POST['quote_id'],'AAAAAAAAAAA');
$oUpdateTelegraph->run();
?>
