<?php
require_once('class/data_interface.class.php');

class display_TPO {
    
    private $_sQuoteId;
    private $_iInterval;
    private $_iDays;
    private $_nPriceInterval;
    
    function display_TPO($iInterval, $iDays, $nPriceInterval, $iGraphWidth){
        $this->_iInterval   = $iInterval;
        $this->_iDays       = $iDays;
        $this->_nPriceInterval       = $nPriceInterval;
        $this->_iGraphWidth       = $iGraphWidth;
    }
    
    function getDayFrameDataTelegraph($sQuoteId){
        $this->_sQuoteId    = $sQuoteId;
        return $this->_getDayFrameData('TELEGRAPH');
    }
    
    public function getDayFrameDataDukascopy($sQuoteId) {
        $this->_sQuoteId    = $sQuoteId;
        return $this->_getDayFrameData('DUKASCOPY');
    }

    private function _getDayFrameData($sSource){
        $aDays       = array( 'time_frame_data'  => array()
                            , 'min_value'       => 100000000000
                            , 'max_value'       => 0
                            , 'max_volume'      => 0
                            , 'price_interval'  => $this->_nPriceInterval
                            , 'graph_width'     => $this->_iGraphWidth);
        
        $oDataInterface = new data_interface();
        
        switch ($sSource){
            case 'DUKASCOPY':
                $aResultSet = $oDataInterface->getDukascopyTPOData($this->_sQuoteId,$this->_iInterval,$this->_iDays);
            break;
            case 'TELEGRAPH':
                $aResultSet = $oDataInterface->getTelegraphTPOData($this->_sQuoteId,$this->_iInterval,$this->_iDays);
            break;
        }

        $sDateFormat = 'Y-m-d H:i:se';
        if (!empty($aResultSet)){
            foreach($aResultSet as $aResultSetLine){
                $oDate = DateTime::createFromFormat($sDateFormat, $aResultSetLine['datetime']);

                $sDayKey = $oDate->format('Ymd');

                if (!array_key_exists($sDayKey, $aDays['time_frame_data'])) {
                    $aDays['time_frame_data'][$sDayKey] = array('TPO'           => array()
                                                            , 'prices'          => array()
                                                            , 'rotation_factor' => array()
                                                            , 'total_volume'    => 0);
                }

                $nHalf = ($oDate->format('i') < 30) ? 0:1;
                $sTPOKey = $oDate->format('H').$nHalf;

                $nMin = intval($aResultSetLine['min']/$aDays['price_interval']);
                $nMax = intval($aResultSetLine['max']/$aDays['price_interval']);

                $nTimeVolume = $aResultSetLine['volume'];

                if (!array_key_exists($sTPOKey, $aDays['time_frame_data'][$sDayKey]['TPO'])){ 
                    $aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey] = array('min' => $nMin
                                                                                , 'max' => $nMax
                                                                                , 'time_volume' => $nTimeVolume);
                }
                else {
                    $aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey] = array('min' => min(array($nMin, $aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey]['min']))
                                                                                , 'max' => max(array($nMax, $aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey]['max']))
                                                                                , 'time_volume' => $aDays['time_frame_data'][$sDayKey]['TPO'][$sTPOKey]['time_volume']+$nTimeVolume);
                }


                $nPriceVolume = round($nTimeVolume/($nMax-$nMin+1));
                for($nPrice=$nMin; $nPrice<=$nMax; $nPrice++){
                    if (!array_key_exists($nPrice, $aDays['time_frame_data'][$sDayKey]['prices'])){
                        $aDays['time_frame_data'][$sDayKey]['prices'][$nPrice] = array('volume'     => $nPriceVolume
                                                                                    , 'letters'     => ''
                                                                                    , 'value_area'  => false); 
                    }
                    else {
                        $aDays['time_frame_data'][$sDayKey]['prices'][$nPrice]['volume'] += $nPriceVolume; 
                    }
                }
            }
            
            foreach($aDays['time_frame_data'] as $sDayKey=>$aDayData){
                $iRotationFactorTop = 0;
                $iRotationFactorBottom = 0;
                $nTop=0;
                $nBottom=0;
                $iTotalVolume = 0;
                foreach($aDayData['TPO'] as $sTPOKey => $aTPOData){
                    if (($nTop!=0) && ($nBottom!=0)){
                        if ($aTPOData['max']>$nTop){
                            $iRotationFactorTop++;
                        }
                        elseif ($aTPOData['max']<$nTop){
                            $iRotationFactorTop--;
                        }
                        if ($aTPOData['min']>$nBottom){
                            $iRotationFactorBottom++;
                        }
                        elseif ($aTPOData['min']<$nBottom){
                            $iRotationFactorBottom--;
                        }
                        $nTop = $aTPOData['max'];
                        $nBottom = $aTPOData['min'];
                    }
                    $nTop = $aTPOData['max'];
                    $nBottom = $aTPOData['min'];
                    $iTotalVolume += $aTPOData['time_volume'];
                }
                $aDays['time_frame_data'][$sDayKey]['total_volume']     = $iTotalVolume;
                $aDays['time_frame_data'][$sDayKey]['rotation_factor']  = array('top'     => $iRotationFactorTop
                                                                              , 'bottom'  => $iRotationFactorBottom);
            }

            foreach($aDays['time_frame_data'] as $sDayKey=>$aDayData){
                krsort($aDays['time_frame_data'][$sDayKey]['prices']);
                ksort($aDays['time_frame_data'][$sDayKey]['TPO']);
                $this->_mark_value_area($aDays['time_frame_data'][$sDayKey]);
            }

            ksort($aDays['time_frame_data']);
            
            $aLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            foreach($aDays['time_frame_data'] as $sDayKey => $aDayData){

                $nTPOOfTheDay = 0;
                foreach($aDayData['TPO'] as $sTPOKey => $aTPOData){
                    $nTPOOfTheDay++;
                    for($nPrice=$aTPOData['min']; $nPrice <= $aTPOData['max']; $nPrice++){
                        $aDays['time_frame_data'][$sDayKey]['prices'][$nPrice]['letters'] .= $aLetters[$nTPOOfTheDay-1];
                    }
                }
            }

            foreach($aDays['time_frame_data'] as $sDayKey => $aDayData){
                foreach($aDayData['prices'] as $nPrice => $aPriceData){
                    $aDays['max_volume'] = max($aDays['max_volume'],$aPriceData['volume']);
                }

                foreach($aDayData['TPO'] as $sTPOKey => $aTPOData){
                    $aDays['min_value'] = min($aDays['min_value'], $aTPOData['min']);
                    $aDays['max_value'] = max($aDays['max_value'], $aTPOData['max']);
                }
            }
        }
        
        return $aDays;
    }
    
    private function _mark_value_area(&$aDayData){
        
        $iMaxVolumePrice=0;
        $iMaxVolume = 0;
        foreach($aDayData['prices'] as $nPrice => $aPriceData){
            if($aPriceData['volume'] > $iMaxVolume) {
                $iMaxVolume = $aPriceData['volume'];
                $iMaxVolumePrice = $nPrice;
            }
        }
        
        $aDayData['prices'][$iMaxVolumePrice]['value_area'] = true;
        
        $iVolumeFound = $aDayData['prices'][$iMaxVolumePrice]['volume'];
        $iPriceUp=$iMaxVolumePrice;
        $iPriceDown=$iMaxVolumePrice;
        $nSeventyPercent = ($aDayData['total_volume']*0.7);
        
        while($iVolumeFound < $nSeventyPercent){
            $aVolumesAroundPrice = $this->_getVolumesAroundPrice($aDayData['prices'], $iPriceUp, $iPriceDown);
            if ((($iVolumeFound+$aVolumesAroundPrice['up1']) < $nSeventyPercent)
             && (($iVolumeFound+$aVolumesAroundPrice['down1']) < $nSeventyPercent)){
                if (($aVolumesAroundPrice['up1']+$aVolumesAroundPrice['up2']) > ($aVolumesAroundPrice['down1']+$aVolumesAroundPrice['down2'])){
                    $iVolumeFound += $aVolumesAroundPrice['up1']+$aVolumesAroundPrice['up2'];
                    $aDayData['prices'][$iPriceUp+1]['value_area'] = true;
                    $aDayData['prices'][$iPriceUp+2]['value_area'] = true;
                    $iPriceUp+=2;
                }
                else {
                    $iVolumeFound += $aVolumesAroundPrice['down1']+$aVolumesAroundPrice['down2'];
                    $aDayData['prices'][$iPriceDown-1]['value_area'] = true;
                    $aDayData['prices'][$iPriceDown-2]['value_area'] = true;
                    $iPriceDown-=2;
                }
            }
            else {
                if ($aVolumesAroundPrice['up1'] > $aVolumesAroundPrice['down1']){
                    $iVolumeFound += $aVolumesAroundPrice['up1'];
                    $aDayData['prices'][$iPriceUp+1]['value_area'] = true;
                }
                else {
                    $iVolumeFound += $aVolumesAroundPrice['down1'];
                    $aDayData['prices'][$iPriceDown-1]['value_area'] = true;
                }
            }
        }
    }
    
    private function _getVolumesAroundPrice($aPriceData, $iPriceUp, $iPriceDown){
        $iUp2   = array_key_exists($iPriceUp+2,$aPriceData) ? $aPriceData[$iPriceUp+2]['volume']:0;
        $iUp1   = array_key_exists($iPriceUp+1,$aPriceData) ? $aPriceData[$iPriceUp+1]['volume']:0;
        $iDown1 = array_key_exists($iPriceDown-1,$aPriceData) ? $aPriceData[$iPriceDown-1]['volume']:0;
        $iDown2 = array_key_exists($iPriceDown-2,$aPriceData) ? $aPriceData[$iPriceDown-2]['volume']:0;
       
        return array('up2'=>$iUp2, 'up1'=>$iUp1, 'down1'=>$iDown1, 'down2'=>$iDown2);
    }
}

$oDisplayTPO = new display_TPO($_POST['interval']
                                ,$_POST['days']
                                ,$_POST['price_interval']
                                ,$_POST['graph_width']); 

if (array_key_exists('display_day_frame_tpo_dukascopy', $_POST)){
    $oPage->day_frame_tpo = $oDisplayTPO->getDayFrameDataDukascopy($_POST['quote_dukascopy_id']);
}
elseif (array_key_exists('display_day_frame_tpo_telegraph', $_POST)){
    $oPage->day_frame_tpo = $oDisplayTPO->getDayFrameDataTelegraph($_POST['quote_telegraph_id']);
}