<?php
require_once('class/chart/realprice.class.php');

class realChart {
    const VERTICAL_MARGIN = 2; // In % on both, top and bottom
    
    private $_aRealPrices;
    private $_iZoom;
    private $_sGraphTimeInterval;
    private $_aIndicatorsSettings;
    private $_sQuote;

    public function realChart($sQuote,$sGraphTimeInterval, $iZoom=1, $aIndicators=array()){
        $this->_sQuote              = $sQuote;
        $this->_aRealPrices         = array();
        $this->_iZoom               = $iZoom;
        $this->_sGraphTimeInterval   = $sGraphTimeInterval;
        $this->_aIndicatorsSettings = $aIndicators;
    }
    
    public function addPrice($sDateTime, realPrice $oRealPrice){
        
        $sDay = substr($sDateTime,0,10);
        $iDay = strtotime($sDay);
        $sFinalDate = date('Y m W d',$iDay);
        if (($this->_sGraphTimeInterval=='1D') || ($this->_sGraphTimeInterval=='1W')){
            $sFinalTime='00:00';
        }
        else {
            $sHour = substr($sDateTime,11,5);

            switch($this->_sGraphTimeInterval) {
                case '5': $aPossibleTimes = array(':00',':05',':10',':15',':20',':25',':30',':35',':40',':45',':50',':55');
                    break;
                case '10': $aPossibleTimes = array(':00',':10',':20',':30',':40',':50');
                    break;
                case '15': $aPossibleTimes = array(':00',':15',':30',':45');
                    break;
                case '30': $aPossibleTimes = array(':00',':30');
                    break;
                case '60': $aPossibleTimes = array(':00');
                    break;
                case '120': $aPossibleTimes = array('00:00','02:00','04:00','06:00','08:00','10:00','12:00','14:00','16:00','18:00','20:00','22:00');
                    break;
                case '240': $aPossibleTimes = array('00:00','04:00','08:00','12:00','16:00','20:00');
                    break;
            }
        
            $aInputTime = explode(':',$sHour);
            rsort($aPossibleTimes);
            if ($this->_sGraphTimeInterval<=60){
                foreach($aPossibleTimes as $sPossibleTime){
                    $aPossibleTime = explode(':',$sPossibleTime);
                    if ($aInputTime[1] >= $aPossibleTime[1]){
                        $sFinalTime = $aInputTime[0].':'.$aPossibleTime[1];
                        break;
                    }
                }
            }
            else {
                foreach($aPossibleTimes as $sPossibleTime){
                    $aPossibleTime = explode(':',$sPossibleTime);
                    if ($aInputTime[0] >= $aPossibleTime[0]){
                        $sFinalTime = $aPossibleTime[0].':00';
                        break;
                    }
                }
            }
        }
        
        $sPriceIndex = "$sFinalDate $sFinalTime";

        if (array_key_exists($sPriceIndex, $this->_aRealPrices)){
            $this->_aRealPrices[$sPriceIndex]->addPrice($oRealPrice);
        }
        else {
            $oRealPrice->setZoom($this->_iZoom);
            $oRealPrice->setIndicators($this->_aIndicatorsSettings);
            $this->_aRealPrices[$sPriceIndex] = $oRealPrice;
        }
    }
    
    public function getGraphTimeInterval(){
        return $this->_sGraphTimeInterval;
    }
    
    public function getQuote(){
        return $this->_sQuote;
    }
    
    public function getPrices(){
        return $this->_aRealPrices;
    }
    
    public function getPriceWidth(){
        return reset($this->_aRealPrices)->getGraphWidth();
    }
    
    
    public function getIndicatorsSettings(){
        return $this->_aIndicatorsSettings;
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
        foreach ($this->_aRealPrices as $oRealPrice){
            $oRealPrice->buildIndicators();
        }
    }
    
    private function _getPreviousTime($sHour){
        $aHour = explode(':',$sHour);
        
        if ($this->_sGraphTimeInterval>=60){
            $iMinusHours = $this->_sGraphTimeInterval/60;
            if ($aHour[0]>$iMinusHours){
                $sFinalHour=($aHour[0]-$iMinusHours);
                $sFinalMinutes = (int)$aHour[1];
            }
            else {
                echo "time error:";exit;
            }
        }
        else {
            if ($this->_sGraphTimeInterval>$aHour[1]){
                $sFinalHour = ($aHour[0]-1);
                $sFinalMinutes = (60-$this->_sGraphTimeInterval);
            }
            else {
                $sFinalHour = (int)$aHour[0];
                $sFinalMinutes = ($aHour[1]-$this->_sGraphTimeInterval);
            }
        }
        
        if ($sFinalHour<10) $sFinalHour='0'.$sFinalHour;
        if ($sFinalMinutes<10) $sFinalMinutes='0'.$sFinalMinutes;

        $sFinalTime =$sFinalHour.':'.$sFinalMinutes;
        
        return $sFinalTime;
    }
    
    private function _getXIntervalMarks($iPlottableSpaceX) {
        echo "MinutesPerPrice: $this->_sGraphTimeInterval \n";
        $aDateTimes = array_keys($this->_aRealPrices);
        $iPriceWidth = reset($this->_aRealPrices)->getGraphWidth();
        $iAvailableGraphPoints = ((int)(string)($iPlottableSpaceX/$iPriceWidth));
        rsort($aDateTimes);
        //YYYY MM WW DD HH:MM
        //2012 05 20 17 07:30
        $aXIntervalMarks = array();
        if (($this->_sGraphTimeInterval=='1D') || ($this->_sGraphTimeInterval=='1W')){
            foreach($aDateTimes as $sDateTime){
                $aXIntervalMarks[]=$sDateTime;
                $iAvailableGraphPoints--;
                if ($iAvailableGraphPoints==0) break;
            }
        }
        else {
            $sMinTime = '23:59';
            $sMaxTime = '00:00';
            foreach($aDateTimes as $sDateTime){
                $sHour = substr($sDateTime, 14,5);
                $sMinTime = min($sMinTime, $sHour);
                $sMaxTime = max($sMaxTime, $sHour);
            }
            
            $sProcessedDay = NULL;
            foreach($aDateTimes as $sDateTime){
                $sDay = substr($sDateTime,0,13);
                if (is_null($sProcessedDay) || ($sDay!=$sProcessedDay)){
                    for($sHour=$sMaxTime;$sHour>=$sMinTime;$sHour=$this->_getPreviousTime($sHour)){
                        $aXIntervalMarks[]=substr($sDateTime,0,14).$sHour;
                        $iAvailableGraphPoints--;
                        if ($iAvailableGraphPoints==0) break;
                    }
                }
                if ($iAvailableGraphPoints==0) break;
                $sProcessedDay = $sDay;
            }
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
        $bFirstX=true;
        $bFirstY=true;
        
        foreach($aXIntervalMarks as $sDateTime){
            if (array_key_exists($sDateTime, $this->_aRealPrices)){
                if ($bFirstY){
                    $nMinY = $this->_aRealPrices[$sDateTime]->getMin();
                    $nMaxY = $this->_aRealPrices[$sDateTime]->getMax();
                    $bFirstY=false;
                }
                else {
                    $nMinY = min($nMinY,$this->_aRealPrices[$sDateTime]->getMin());
                    $nMaxY = max($nMaxY,$this->_aRealPrices[$sDateTime]->getMax());
                }
                
                $aIndicatorsData = $this->_aRealPrices[$sDateTime]->getIndicators()->getData();
                if (array_key_exists('BOL',$aIndicatorsData) && !is_null($aIndicatorsData['BOL'])){
                    $nMinY = min($nMinY,$aIndicatorsData['BOL']['real']['down']);
                    $nMaxY = max($nMaxY,$aIndicatorsData['BOL']['real']['up']);
                }
            }
            if ($bFirstX){
                $sMinX = $sDateTime;
                $sMaxX = $sDateTime;
                $bFirstX=false;
            }
            else {
                $sMinX = min($sMinX,$sDateTime);
                $sMaxX = max($sMaxX,$sDateTime);
            }
        }
        
        return array( 'minX'    => $sMinX
                    , 'maxX'    => $sMaxX
                    , 'minY'    => $nMinY
                    , 'maxY'    => $nMaxY);
    }
}
?>
