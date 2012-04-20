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
    
    public function run(){
        $this->_buildDayFrameData();
        return $this->_getHTML();
        //var_dump($this->_aDays);
    }
    
    private function _getHTML() {
        
        $sHTML = '';
        
        foreach($this->_aDays['time_frame_data'] as $sDayKey => $aDayData){
            $sHTML .= '<div class="timeframedays">';
            $sHTML .= '<div style="font-weight:bold;color:blue;">'.$sDayKey.'</div>';
            
            for($nPrice = $this->_aDays['max_value']; $nPrice >= $this->_aDays['min_value']; $nPrice--){
                if (array_key_exists($nPrice, $this->_aDays['time_frame_data'][$sDayKey]['prices'])){
                    $nRelativeVolume = round(300*($this->_aDays['time_frame_data'][$sDayKey]['prices'][$nPrice]['volume']/$this->_aDays['max_volume']));
                    $sLetters = $this->_aDays['time_frame_data'][$sDayKey]['prices'][$nPrice]['letters'];
                    $sHTML .= '<div class="price" style="background-size: '.$nRelativeVolume.'px;">'.$nPrice*$this->_aDays['price_interval'].' '.$sLetters.'</div>';
                }
                else {
                    $sHTML .= '<div class="price" style="background-size: 0px;">'.$nPrice*$this->_aDays['price_interval'].'</div>';
                }
            }
            $sHTML .= '</div>';
        }
        
        return $sHTML;
    }
    
    private function _buildDayFrameData(){
        $aResultSet = $this->_getData();
        $sDateFormat = 'Y-m-d H:i:se';
        
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
                    .' AND "RD_datetime" > now() - interval \''.$this->_iDays.' days\';';
        
        $aResultSet = $oPostgres->query("query_name4", $sQuery, array($this->_sQuoteId
                                                                     ,$this->_iInterval));
        return $aResultSet;
    }
}
