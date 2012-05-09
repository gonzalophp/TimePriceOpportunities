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
    
    public function calculateGraphParameters($oGraphicalChart){
        $this->_iGraphMin = $oGraphicalChart->getGraphicalY($this->getMin());
        $this->_iGraphMax = $oGraphicalChart->getGraphicalY($this->getMax());
        $this->_iGraphOpen = $oGraphicalChart->getGraphicalY($this->getOpen());
        $this->_iGraphClose = $oGraphicalChart->getGraphicalY($this->getClose());
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