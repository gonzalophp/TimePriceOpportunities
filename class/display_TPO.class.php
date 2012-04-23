<?php
require_once('class/data_interface.class.php');

class display_TPO {
    
    private $_sQuoteId;
    private $_iInterval;
    private $_iDays;
    private $_nPriceInterval;
    
    function display_TPO($sQuoteId, $iInterval, $iDays, $nPriceInterval, $iGraphWidth){
        $this->_sQuoteId    = $sQuoteId;
        $this->_iInterval   = $iInterval;
        $this->_iDays       = $iDays;
        $this->_nPriceInterval       = $nPriceInterval;
        $this->_iGraphWidth       = $iGraphWidth;
    }

    function getDayFrameData(){
        $aDays       = array( 'time_frame_data'  => array()
                            , 'min_value'       => 100000000000
                            , 'max_value'       => 0
                            , 'max_volume'      => 0
                            , 'price_interval'  => $this->_nPriceInterval
                            , 'graph_width'     => $this->_iGraphWidth);
        
        $oDataInterface = new data_interface();
        $aResultSet = $oDataInterface->getTPOData($this->_sQuoteId,$this->_iInterval,$this->_iDays);

        $sDateFormat = 'Y-m-d H:i:se';
        if (!empty($aResultSet)){
            foreach($aResultSet as $aResultSetLine){
                $oDate = DateTime::createFromFormat($sDateFormat, $aResultSetLine['RD_datetime']);

                $sDayKey = $oDate->format('Ymd');

                if (!array_key_exists($sDayKey, $aDays['time_frame_data'])) {
                    $aDays['time_frame_data'][$sDayKey] = array('TPO'           => array()
                                                            , 'prices'          => array()
                                                            , 'rotation_factor' => array()
                                                            , 'total_volume'    => 0);
                }

                $nHalf = ($oDate->format('i') < 30) ? 0:1;
                $sTPOKey = $oDate->format('H').$nHalf;

                $nMin = intval($aResultSetLine['RD_min']/$aDays['price_interval']);
                $nMax = intval($aResultSetLine['RD_max']/$aDays['price_interval']);

                $nTimeVolume = $aResultSetLine['RD_volume'];

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
                        $aDays['time_frame_data'][$sDayKey]['prices'][$nPrice] = array('volume'  => $nPriceVolume
                                                                                        , 'letters' => ''); 
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
}

$oDisplayTPO = new display_TPO(  $_POST['quote_id']
                                ,$_POST['interval']
                                ,$_POST['days']
                                ,$_POST['price_interval']
                                ,$_POST['graph_width']); 

$oPage->day_frame_tpo = $oDisplayTPO->getDayFrameData();
