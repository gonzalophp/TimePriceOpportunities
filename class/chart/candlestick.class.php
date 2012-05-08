<?php
require_once('class/chart/realprice.class.php');

class candlestick extends realPrice {
    private $_iGraphMin;
    private $_iGraphMax;
    private $_iGraphOpen;
    private $_iGraphClose;
    
    public function setZoom($iZoom){
        $this->setGraphWidth(3 + (2*$iZoom));
    }
    
    public function calculateGraphParameters($fGetGraphicalY){
        $this->_iGraphMin = call_user_func_array($fGetGraphicalY, array($this->getMin()));
        $this->_iGraphMax = call_user_func_array($fGetGraphicalY, array($this->getMax()));
        $this->_iGraphOpen = call_user_func_array($fGetGraphicalY, array($this->getOpen()));
        $this->_iGraphClose = call_user_func_array($fGetGraphicalY, array($this->getClose()));
    }
    
    public function drawPrice($oImageChart, $x){
        $oImageChart->drawCandlestick(($x-((int)($this->getGraphWidth()/2)))
                                    , ($x+((int)($this->getGraphWidth()/2)))
                                    , $this->_iGraphMin
                                    , $this->_iGraphMax
                                    , $this->_iGraphOpen
                                    , $this->_iGraphClose);
    }
}
?>