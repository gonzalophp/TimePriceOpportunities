<?php
require_once('class/chart/realprice.class.php');

class realChart {
    const VERTICAL_MARGIN = 5; // In % on both, top and bottom
    
    private $_aRealPrices;
    private $_iZoom;
    private $_iMinutesPerPrice;
    private $_aIndicators;

    public function realChart($iMinutesPerPrice, $iZoom=1, $aIndicators=array()){
        $this->_aRealPrices         = array();
        $this->_iZoom               = $iZoom;
        $this->_iMinutesPerPrice    = $iMinutesPerPrice;
        $this->_aIndicators         = $aIndicators;
    }
    
    public function addPrice($sDateTime, realPrice $oRealPrice){
        $iDateTime = strtotime($sDateTime);
        $iDateTime -= ($iDateTime % ($this->_iMinutesPerPrice*60));
        
        if (array_key_exists($iDateTime, $this->_aRealPrices)){
            $this->_aRealPrices[$iDateTime]->addPrice($oRealPrice);
        }
        else {
            $oRealPrice->setZoom($this->_iZoom);
            $oRealPrice->setIndicators($this->_aIndicators);
            $this->_aRealPrices[$iDateTime] = $oRealPrice;
        }
    }
    
    public function getPrices(){
        return $this->_aRealPrices;
    }
    
    public function getChartParameters($iPlottableSpaceX){
        ksort($this->_aRealPrices);
        $this->_buildIndicators();
        $aXIntervalMarks = $this->_getXIntervalMarks($iPlottableSpaceX);
        
        $aExtremes = $this->_getPriceExtremes($aXIntervalMarks);
        $aYIntervalMarks = $this->_getYIntervalMarks($aExtremes);
        
        $nYMaxIntervalMark = max($aYIntervalMarks);
        $nYMinIntervalMark = min($aYIntervalMarks);
        
        $nRealChartMargin = ($nYMaxIntervalMark-$nYMinIntervalMark)*(self::VERTICAL_MARGIN/100);
        
        $aExtremes['minY'] = $nYMinIntervalMark-$nRealChartMargin;
        $aExtremes['maxY'] = $nYMaxIntervalMark+$nRealChartMargin;
        
        return array('extremes'         => $aExtremes
                    , 'Xinterval_marks' => $aXIntervalMarks
                    , 'Yinterval_marks' => $aYIntervalMarks);
    }
    
    private function _buildIndicators(){
        $oPreviousRealPrice = NULL;
        foreach ($this->_aRealPrices as $oRealPrice){
            $oRealPrice->buildIndicators($oPreviousRealPrice);
            $oPreviousRealPrice = $oRealPrice;
        }
    }
    
    private function _getXIntervalMarks($iPlottableSpaceX) {
        $aDateTimes = array_keys($this->_aRealPrices);
        $iPriceWidth = reset($this->_aRealPrices)->getGraphWidth();
//        var_dump($this->_aRealPrices);exit;
        $aDays = array();
        foreach($aDateTimes as $iDateTime){
            $sDay = date('Y-m-d',$iDateTime);
            if (!array_key_exists($sDay, $aDays)){
                $aDays[$sDay] = array($iDateTime);
            }
            else {
                $aDays[$sDay][] = $iDateTime;
            }
        }
        
        $iSecondsPerPrice = $this->_iMinutesPerPrice*60;
        
        $aPriceTimes = array();
        foreach($aDays as $sDay=>$aTimes){
            $aPriceTimes[$sDay]=array();
            
            $iMaxTime = max($aTimes) - (max($aTimes) % $iSecondsPerPrice);
            $iMinTime = min($aTimes) - (min($aTimes) % $iSecondsPerPrice);
            
            for ($i=$iMaxTime;$i>=$iMinTime; $i-=$iSecondsPerPrice){
                $aPriceTimes[$sDay][]=$i;
            }
        }
        
        $sMinTime = '24:00';
        $sMaxTime = '00:00';
        foreach($aPriceTimes as $sDay => $aTimes){
            foreach($aTimes as $iTime){
                $sMinTime = min($sMinTime, date ('H:i', $iTime));
                $sMaxTime = max($sMaxTime, date ('H:i', $iTime));
            }
        }
        
        $iAvailableGraphPoints = ((int)(string)($iPlottableSpaceX/$iPriceWidth));
        krsort($aPriceTimes);
        $aXIntervalMarks = array();
        foreach($aPriceTimes as $sDay => $aTimes){
            $aXIntervalMarks[$sDay] = array();
            $iLastDayPriceTime = strtotime($sDay.' '.$sMaxTime);
            $iFirstDayPriceTime = strtotime($sDay.' '.$sMinTime);
            for($i=$iLastDayPriceTime;(($iAvailableGraphPoints>0) && ($i>=$iFirstDayPriceTime));$i-=$iSecondsPerPrice){
                $aXIntervalMarks[$sDay][] = $i;
                $iAvailableGraphPoints--;
            }
            if ($iAvailableGraphPoints==0) break;
        }
        return $aXIntervalMarks;
    }
    
    private function _getYIntervalMarks($aExtremes){
        $nDifference = $aExtremes['maxY'] - $aExtremes['minY'];
        $iIntervals = 3;

        $nIntervalDifference = $nDifference/$iIntervals;

        $aIntervals = array(1,2,5);

        if ($nIntervalDifference<10){
            for ($iExponent=0, $iBase=0;$iBase==0;$iExponent++){
                $iBase = (int)(string)($nIntervalDifference*pow(10,$iExponent));
            }
            $iExponent--;
        }
        else {
            for ($iExponent=0, $iBase=$nIntervalDifference;$iBase>10;$iExponent--){
                $iBase = (int)(string)($nIntervalDifference*pow(10,$iExponent));
            }
            $iExponent++;
        }


        for($i=0;$i<count($aIntervals)-1;$i++){
            if ($aIntervals[$i]>$iBase){
                if ($i>0) $i--;
                break;
            }
        }

        $iBaseInterval = $aIntervals[$i];

        $iMin = (int)(string)($aExtremes['minY']*pow(10,$iExponent));
        $iMax = (int) ceil((string)($aExtremes['maxY']*pow(10,$iExponent)));

        for($i=$iMin; $i>=0; $i--){
            if (($i % $iBaseInterval)==0){
                $iFirstMark = $i;
                break;
            }
        }

        $aIntervalMarksInteger = array();
        for($i=$iFirstMark; $i<$iMax;$i+=$iBaseInterval){
            $aIntervalMarksInteger[] = $i;
        }
        $aIntervalMarksInteger[] = $i;

        foreach($aIntervalMarksInteger as $iIndex=>$iMark){
            $aIntervalMarks[$iIndex] = $iMark/pow(10,$iExponent);
        }
        
        return $aIntervalMarks;
    }
    
    private function _getPriceExtremes($aXIntervalMarks){
        $bFirstX=false;
        $bFirstY=false;
        foreach($aXIntervalMarks as $sDay=>$aTimes){
            foreach($aTimes as $iDateTime){
                if (array_key_exists($iDateTime, $this->_aRealPrices)){
                    if ($bFirstY){
                        $nMinY = min($nMinY,$this->_aRealPrices[$iDateTime]->getMin());
                        $nMaxY = max($nMaxY,$this->_aRealPrices[$iDateTime]->getMax());
                    }
                    else {
                        $nMinY = $this->_aRealPrices[$iDateTime]->getMin();
                        $nMaxY = $this->_aRealPrices[$iDateTime]->getMax();
                        $bFirstY=true;
                    }
                }
                if ($bFirstX){
                    $nMinX = min($nMinX,$iDateTime);
                    $nMaxX = max($nMaxX,$iDateTime);
                }
                else {
                    $nMinX = $iDateTime;
                    $nMaxX = $iDateTime;
                    $bFirstX=true;
                }
                
            }
        }
        
        return array( 'minX'    => $nMinX
                    , 'maxX'    => $nMaxX
                    , 'minY'    => $nMinY
                    , 'maxY'    => $nMaxY);
    }
}
?>
