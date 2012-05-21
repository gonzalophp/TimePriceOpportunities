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
        $this->_iGraphMin = $oGraphicalChart->getGraphicalY('prices',$this->getMin());
        $this->_iGraphMax = $oGraphicalChart->getGraphicalY('prices',$this->getMax());
        $this->_iGraphOpen = $oGraphicalChart->getGraphicalY('prices',$this->getOpen());
        $this->_iGraphClose = $oGraphicalChart->getGraphicalY('prices',$this->getClose());
    }
    
    public function drawPrice($oImageChart, $x){
        
        $oImageChart->drawCandlestick(($x-((int)($this->getGraphWidth()/2)))
                                    , ($x+((int)($this->getGraphWidth()/2)))
                                    , $this->_iGraphMin
                                    , $this->_iGraphMax
                                    , $this->_iGraphOpen
                                    , $this->_iGraphClose);
        
        if (!is_null($iTrade = $this->getTrade())){
            switch($iTrade){
                case realPrice::TRADE_SELL: 
                    $aColor = array('r'=>200,'g'=>50,'b'=>50);
                break;
                case realPrice::TRADE_CLOSE: 
                    $aColor = array('r'=>70,'g'=>70,'b'=>70);
                break;
                case realPrice::TRADE_BUY: 
                    $aColor = array('r'=>50,'g'=>210,'b'=>50);
                break;
            }
            $oImageChart->drawBalloon(($x-((int)($this->getGraphWidth()/2)))
                                      , $this->_iGraphClose
                                      , $aColor);
        }
    }
}
?>