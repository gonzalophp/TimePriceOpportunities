<?php
require_once('class/chart/realprice.class.php');

class candlestick extends realPrice {
    
    private $_iGraphMin;
    private $_iGraphMax;
    private $_iGraphOpen;
    private $_iGraphClose;
    private $_iGraphWidth;
    
    public function calculateGraphParameters($fGetGraphicalY){
        $this->_iGraphMin = call_user_func_array($fGetGraphicalY, array($this->getMin()));
        $this->_iGraphMax = call_user_func_array($fGetGraphicalY, array($this->getMax()));
        $this->_iGraphOpen = call_user_func_array($fGetGraphicalY, array($this->getOpen()));
        $this->_iGraphClose = call_user_func_array($fGetGraphicalY, array($this->getClose()));
    }
    
    public function drawPrice($oImageChart, $x){
        $oImageChart->drawCandlestick(($x-((int)($this->_iGraphWidth/2)))
                                    , ($x+((int)($this->_iGraphWidth/2)))
                                    , $this->_iGraphMin
                                    , $this->_iGraphMax
                                    , $this->_iGraphOpen
                                    , $this->_iGraphClose);
    }
    
    public function setGraphWidth($iGraphWidth){
        $this->_iGraphWidth = $iGraphWidth;
    }
    
    public function getColor(){
        return (($this->getClose()-$this->getOpen())>0) ? array('r'=>0, 'g'=>255, 'b'=>0):array('r'=>255, 'g'=>0, 'b'=>0);
    }
}
?>
