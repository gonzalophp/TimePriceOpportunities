<?php
require_once('class/postgres.class.php');

class display_TPO {
    
    private $_sQuoteId;
    private $_iInterval;
    private $_iDays;
    private $_aDays;
    
    function display_TPO($sQuoteId, $iInterval, $iDays, $nPriceInterval){
        $this->_sQuoteId    = $sQuoteId;
        $this->_iInterval   = $iInterval;
        $this->_iDays       = $iDays;
        $this->_aDays       = array( 'time_frame_data'  => array()
                                    , 'min_value'       => 100000000000
                                    , 'max_value'       => 0
                                    , 'max_volume'      => 0
                                    , 'price_interval'  => $nPriceInterval);
    }

    function getDayFrameData(){
        $aResultSet = $this->_getData();
        $sDateFormat = 'Y-m-d H:i:se';
        if (!empty($aResultSet)){
            foreach($aResultSet as $aResultSetLine){
                $oDate = DateTime::createFromFormat($sDateFormat, $aResultSetLine['RD_datetime']);

                $sDayKey = $oDate->format('Ymd');

                if (!array_key_exists($sDayKey, $this->_aDays['time_frame_data'])) {
                    $this->_aDays['time_frame_data'][$sDayKey] = array('TPO'    => array()
                                                                    , 'prices' => array());
                }

                $nHalf = ($oDate->format('i') < 30) ? 0:1;
                $sTPOKey = $oDate->format('H').$nHalf;

                $nMin = intval($aResultSetLine['RD_min']/$this->_aDays['price_interval']);
                $nMax = intval($aResultSetLine['RD_max']/$this->_aDays['price_interval']);

                $nTimeVolume = $aResultSetLine['RD_volume'];

                if (!array_key_exists($sTPOKey, $this->_aDays['time_frame_data'][$sDayKey]['TPO'])){ 
                    $this->_aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey] = array('min' => $nMin
                                                                                    , 'max' => $nMax
                                                                                    , 'time_volume' => $nTimeVolume);
                }
                else {
                    $this->_aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey] = array('min' => min(array($nMin, $this->_aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey]['min']))
                                                                                    , 'max' => max(array($nMax, $this->_aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey]['max']))
                                                                                    , 'time_volume' => $this->_aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey]['time_volume']+$nTimeVolume);
                }


                $nPriceVolume = round($nTimeVolume/($nMax-$nMin+1));
                for($nPrice=$nMin; $nPrice<=$nMax; $nPrice++){
                    if (!array_key_exists($nPrice, $this->_aDays['time_frame_data'][$sDayKey]['prices'])){
                        $this->_aDays['time_frame_data'][$sDayKey]['prices'][$nPrice] = array('volume'  => $nPriceVolume
                                                                                            , 'letters' => ''); 
                    }
                    else {
                        $this->_aDays['time_frame_data'][$sDayKey]['prices'][$nPrice]['volume'] += $nPriceVolume; 
                    }
                }
            }

            foreach($this->_aDays['time_frame_data'] as $sDayKey=>$aDayData){
                krsort($this->_aDays['time_frame_data'][$sDayKey]['prices']);
            }

            ksort($this->_aDays['time_frame_data']);

            $aLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            foreach($this->_aDays['time_frame_data'] as $sDayKey => $aDayData){

                $nTPOOfTheDay = 0;
                foreach($aDayData['TPO'] as $sTPOKey => $aTPOData){
                    $nTPOOfTheDay++;
                    for($nPrice=$aTPOData['min']; $nPrice <= $aTPOData['max']; $nPrice++){
                        $this->_aDays['time_frame_data'][$sDayKey]['prices'][$nPrice]['letters'] .= $aLetters[$nTPOOfTheDay-1];
                    }
                }
            }

            foreach($this->_aDays['time_frame_data'] as $sDayKey => $aDayData){
                foreach($aDayData['prices'] as $nPrice => $aPriceData){
                    $this->_aDays['max_volume'] = max($this->_aDays['max_volume'],$aPriceData['volume']);
                }

                foreach($aDayData['TPO'] as $sTPOKey => $aTPOData){
                    $this->_aDays['min_value'] = min($this->_aDays['min_value'], $aTPOData['min']);
                    $this->_aDays['max_value'] = max($this->_aDays['max_value'], $aTPOData['max']);
                }
            }
        }
        
        return $this->_aDays;
    }
    
    private function _getData(){
        $oPostgres = new postgres();
        $oPostgres->connect();

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
        
        

        $aResultSet = $oPostgres->query("query_name4", $sQuery, array($this->_sQuoteId
                                                                     ,$this->_iInterval
                                                                     ,$this->_iDays));
        return $aResultSet;
    }
}

$oDisplayTPO = new display_TPO(  $_POST['quote_id']
                                ,$_POST['interval']
                                ,$_POST['days']
                                ,$_POST['price_interval']); 
//echo $oDisplayTPO->run();
$oPage->day_frame_tpo = $oDisplayTPO->getDayFrameData();
