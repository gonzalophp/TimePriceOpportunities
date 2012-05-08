<?php
class realPrice {
    private $_nMin;
    private $_nMax;
    private $_nOpen;
    private $_nClose;
    private $_iVolume;
    private $_aDateTimes;
    private $_iGraphWidth;
    
    public function realPrice($sDateTime, $nMin, $nMax, $nOpen, $nClose, $iVolume){
        $this->_nMin        = $nMin;
        $this->_nMax        = $nMax;
        $this->_nOpen       = $nOpen;
        $this->_nClose      = $nClose;
        $this->_iVolume     = $iVolume;
        $this->_aDateTimes  = array(strtotime($sDateTime));
    }
    
    public function setGraphWidth($iGraphWidth){
        $this->_iGraphWidth = $iGraphWidth;
    }
    
    public function setZoom($iZoom){
        $this->_iZoom = $iZoom;
    }
    
    public function getGraphWidth(){
        return $this->_iGraphWidth;
    }
    
    public function getDateTimes() {
        return $this->_aDateTimes;
    }
    
    public function addPrice(realPrice $oRealPrice){
        $aDateTimes = $oRealPrice->getDateTimes();
        $iMaxNewDateTimes = max($aDateTimes);
        $iMinNewDateTimes = min($aDateTimes);
        
        $this->_iVolume += $oRealPrice->getVolume();
        $this->_nMin = min($oRealPrice->getMin(),$this->_nMin);
        $this->_nMax = max($oRealPrice->getMax(),$this->_nMax);

        if ($iMaxNewDateTimes > max($this->_aDateTimes)){
            $this->_nClose = $oRealPrice->getClose();
        }
        
        if ($iMinNewDateTimes < min($this->_aDateTimes)){
            $this->_nOpen = $oRealPrice->getOpen();
        }
        
        $this->_aDateTimes = array_merge($this->_aDateTimes,$aDateTimes);
    }
    
    public function getMin(){
        return $this->_nMin;
    }
    
    public function setMin($nMin) {
        $this->_nMin = $nMin;
    }
    
    public function getMax(){
        return $this->_nMax;
    }
    
    public function setMax($nMax) {
        $this->_nMax = $nMax;
    }
    
    public function getOpen(){
        return $this->_nOpen;
    }
    
    public function setOpen($nOpen) {
        $this->_nOpen = $nOpen;
    }
    
    public function getClose(){
        return $this->_nClose;
    }
    
    public function setClose($nClose) {
        $this->_nClose = $nClose;
    }
    
    public function getVolume(){
        return $this->_iVolume;
    }
    
    public function setVolume($iVolume) {
        $this->_iVolume = $iVolume;
    }
    
    public function getDateTime(){
        return $this->_dateTime;
    }
}
?>